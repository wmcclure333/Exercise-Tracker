/*
GridSplitListLayout sets up the split-list layout (currently for weight training only) and controls the flow of the workout until the layout moves to another format or closes completely.  The split-list layout consists of one <ul> in it's own column and an open block in another column, both side-by-side.  Data is passed in real-time from one side to the other but there is no direct interaction between sides.
*/
(function(window){

	function GridSplitListLayout(){
		//private var
		var _stageWid = window.innerWidth;	//grab stage dimensions to determine layout
		var _stageHei = window.innerHeight;	
		var _aSelectedSets;	//array of set exercises selected (for weight training)
		var _weightTrainingBonusPoints = 0;	//bonus points added for hitting rep/weight goals
		var _sessionVal = 0; //weight training session id # for updating database records
		var _currentSetNum = 1;	//weight training current set of current exercise
		var _currentExerciseNum = 1; //weight training current exercise
		
		//getter/setter methods to grab private vars
		this.getStageWid = function(){return _stageWid;}
		this.getStageHei = function(){return _stageHei;}
		this.getASelectedSets = function(){return _aSelectedSets;}
		this.setASelectedSets = function(e){_aSelectedSets = e;}
		this.getWeightTrainingBonusPoints = function(){return _weightTrainingBonusPoints;}
		this.setWeightTrainingBonusPoints = function(e){_weightTrainingBonusPoints = _weightTrainingBonusPoints + e;}
		this.getSessionVal = function(){return _sessionVal;}
		this.setSessionVal = function(e){_sessionVal = e;}
		this.getCurrentSetNum = function(){return _currentSetNum;}
		this.setCurrentSetNum = function(e){_currentSetNum = e;}
		this.getCurrentExerciseNum = function(){return _currentExerciseNum;}
		this.setCurrentExerciseNum = function(e){_currentExerciseNum = e;}
		
		activeSplitListGrid = this; 	//set global var to this instance for use in global listeners/functions
		showGrid(this);
	}

	function showGrid(context){		//re-initializes split grid CSS and fades it in
		$("#two_split_grid").css({ left: "15%", top: "15%", width: (context.getStageWid() * .7)+"px", height: (context.getStageHei() * .7)+"px" }); //reformat grid CSS
		TweenLite.to('#two_split_grid', .5, {autoAlpha:1, ease:Back.easeOut});
		//pull in data to both sides of grid
		initBlockSideOfLayout(context);
		initListSideOfLayout(context);
	}
	
	GridSplitListLayout.prototype.closePopup = function(){		//closes the popup and deletes its content
		TweenLite.to('#full_screen_underlayer', .05, {autoAlpha:0, ease:Back.easeOut, delay:0});
		TweenLite.to('#two_split_grid', .05, {autoAlpha:0, ease:Back.easeOut});
	};
	
	function initBlockSideOfLayout(context){	//private method loads in content to the block (non-list) side of the split layout
		//load html for weight set log form
		$('#right_box').html("");
		$('#right_box').load('includes/weight_set_log_form.html?seed='+Math.random());
	}
	
	function initListSideOfLayout(context){	//private method loads in content to the list side of the split layout
		context.setASelectedSets($(".sortable").sortable("toArray"));	//send final list to global var
		var aSetList = []; //create list of workouts in set
		var aSetsChosen = context.getASelectedSets();
		for(var b = 0; b < aSetsChosen.length; b++){
			aSetList[b] = ($("#"+aSetsChosen[b]+" .label .label_value").html());
		}

		//get workout list data and place in grid
		$.ajax({
			type: "POST",
			url: "php/getTwoSplitListData.php",
			data: { list_type: "workout_set", set_list: aSetList }
		}).done(function(msg){
			$("#two_split_grid #left_box ul").html(msg);
			$("#two_split_grid #left_box").mCustomScrollbar("update");	//attach plugin div scroller to workout list box
			$("#option1_1").addClass("active_set");
			//update last set data
			updateMilestoneSetData($("#option1_1 .dt_last_date").html(), $("#option1_1 .s1w").html(), $("#option1_1 .s1r").html(), $("#option1_1 .b1w").html(), $("#option1_1 .b1r").html(), $("#option1_1 .note1").html(), $("#option1_1 .set_name").html());
		});
	}

	function updateMilestoneSetData(lastdate, lastweight, lastreps, bestweight, bestreps, setnote, setname){	//This function updates the historical data for the current set of the current exercise (including last weight/rep data and all-time best weight/rep data)
		$("#set_note #note").val(setnote);	
		
		if(lastdate == ""){
			$(".last_set_data_ctr").html("This is the first time doing this set type.");
			$(".best_set_data_ctr").html("");
		}else{
			$(".last_set_data").html(" <b><font color='blue'>" + lastweight + " | " + lastreps + "</font></b> set - " + lastdate.substr(4, 2)+"/"+lastdate.substr(6, 2)+"/"+lastdate.substr(0, 4));
			$(".best_set_data").html("<b><font color='blue'>" + bestweight + " | " + bestreps + "</font></b>");
		}
		//populate form fields with last set data or record data if no last set completed
		if(lastweight == '' && lastreps == ''){
			$("#fweight").val('0');
			$("#freps").val('0');
		}else if(lastweight == '0' && lastreps == '0'){
			$("#fweight").val(bestweight);
			$("#freps").val(bestreps);
		}else{
			$("#fweight").val(lastweight);
			$("#freps").val(lastreps);
		}
		$("#rep_num_label #this_set_name").html(setname);
	}
	
	
	GridSplitListLayout.prototype.recordWeightTrainingSet = function(context){		//public method that submits single set data from a weight training workout after the user hits the submit button.  This posts the information to the DB, updates the workout menu and the data for the next set.
		var weight_val = parseInt($("#weight_rep_log_form #fweight").val());
		var reps_val = parseInt($("#weight_rep_log_form #freps").val());
		var set_val = $(".active_set .set_name").html();
		var last_weight_val = parseInt($("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .s1w").html());
		var last_reps_val = parseInt($("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .s1r").html());
		var best_weight_val = parseInt($("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .b1w").html());
		var best_reps_val = parseInt($("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .b1r").html());
		var note_val = $("#weight_rep_log_form #note").val();
		
		var goal_stars_achieved = 0;
			
		//check for goals hit
		if((weight_val == last_weight_val) && (reps_val == last_reps_val)){
			goal_stars_achieved++;	//equaled last workout numbers
			context.setWeightTrainingBonusPoints(1);
		}else{
			if(weight_val > last_weight_val){
				goal_stars_achieved += 2;	//beat last workout numbers
				context.setWeightTrainingBonusPoints(2);
			}else if((weight_val == last_weight_val) && (reps_val > last_reps_val)){
				goal_stars_achieved += 2;
				context.setWeightTrainingBonusPoints(2);
			}
		}
		if(weight_val > best_weight_val){
			goal_stars_achieved++;	//beat all-time best workout numbers
			context.setWeightTrainingBonusPoints(1);
		}else if((weight_val == best_weight_val) && (reps_val > best_reps_val)){
			goal_stars_achieved++;	
			context.setWeightTrainingBonusPoints(1);
		}
		
		//insert into DB
		$.ajax({
			type: "POST",
			url: "php/weightDataSetSubmit.php",
			data: { w_weight: weight_val, w_reps: reps_val, w_note: note_val, w_session: context.getSessionVal(), w_set_type: set_val, w_current_set: context.getCurrentSetNum() }
		}).done(function(msg){
			//update left column block
			$("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .checkbox").addClass("checked");		
			$("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .database_set_insert_id").attr("id", "_id"+msg);
			//display completed set info in scrollbar list.  highlight goal icons if needed.
			$("#two_split_grid #left_box #option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .it_set_data").html(weight_val+"|"+reps_val);
			if(goal_stars_achieved > 0){
				$("#two_split_grid #left_box #option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .it_goal_stars").removeClass("stars0");
				$("#two_split_grid #left_box #option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .it_goal_stars").addClass("stars"+goal_stars_achieved);
			}
	
			//open next set
			if(context.getCurrentSetNum() != 4){
				context.setCurrentSetNum(context.getCurrentSetNum() + 1);
				context.setSessionVal(msg) //db id to update correct entry next time
			}else{	//reset values for next exercise
				context.setCurrentSetNum(1);
				context.setSessionVal(0) 
				context.setCurrentExerciseNum(context.getCurrentExerciseNum() + 1);
			}
			
			//move set scrollbar list up one notch
			var thisHei = $("#two_split_grid li").height() * (((context.getCurrentExerciseNum() - 1) * 4) + context.getCurrentSetNum() - 1);
			$("#two_split_grid #left_box").mCustomScrollbar("scrollTo",thisHei);
			
			//update last set data
			updateMilestoneSetData($("#option"+context.getCurrentExerciseNum()+"_1 .dt_last_date").html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .s1w").html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .s1r").html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .b1w").html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .b1r").html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .note"+context.getCurrentSetNum()).html(), $("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()+" .set_name").html());
			//update active_set class
			$(".active_set").removeClass("active_set");
			$("#option"+context.getCurrentExerciseNum()+"_"+context.getCurrentSetNum()).addClass("active_set");
			
			$("#this_set_num").html(context.getCurrentSetNum()); //update set # display
			
			
		});	//END AJAX 
	}
	
	window.GridSplitListLayout = GridSplitListLayout;	//make class available to global scope


}(window));

////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////*************BEGIN GLOBAL - Define these outside the class definition so they only run once.*************/
/////////////////////////////////////////////Global Variables
var activeSplitListGrid;	//current instance of the split list grid being used
/////////////////////////////////////////////Global Listeners
$("#two_split_grid #left_box").mCustomScrollbar();	//attach plugin div scroller to workout list box

$(".close_btn2").live("click", function(){ closeGridSplitListLayout();	});

//////////////////////////////////////////////Global Functions
function _relayRecordWeightTrainingSet(){  //this function receives a call from a submitted workout form and relays it to the 'active' object's class function
	GridSplitListLayout.prototype.recordWeightTrainingSet(activeSplitListGrid);	
}
function closeGridSplitListLayout(){ GridSplitListLayout.prototype.closePopup(); }

///////////////////////////////////////////////////////////////
/*************END GLOBAL ***********************************************************************************/
///////////////////////////////////////////////////////////////
