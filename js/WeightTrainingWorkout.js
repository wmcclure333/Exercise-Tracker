/*
WeightTrainingWorkout is an extension of the Workout class.  It controls the group of panels used in the 'Weight Training' workout before the final AHR/minutes popup (which is a PopupWindow object).
*/
(function(window){
	// Extend the Workout class.
	WeightTrainingWorkout.prototype = new Workout();
	
	function WeightTrainingWorkout(parent){
		Workout.apply(this);	// Call super constructor
		//this.testit();
		
		//public var
		this.parent = parent;
		var aSelectedBodyGroups = new Array();	//array of body groups to exercise (for weight training)
		activeWeightTrainingWorkout = this;
		this.parent.currentExerciseOpen = 3;	//set the current exercise to ('weight training') 
		
		context = this;
		$("#submit_weight_workout_btn").click(function(){
			openWeightTrainingSubmitWorkoutPopup(context);
		});
		
		//set the increment up/down buttons for the weight training weight/reps data
		$("#weight .incremental_button_down").live("mousedown", function(){
			var tempVal = $("#fweight").val();
			if(tempVal > 0) $("#fweight").val(parseInt(tempVal) - 5);
		});
		$("#weight .incremental_button_up").live("mousedown", function(){
			var tempVal = $("#fweight").val();
			$("#fweight").val(parseInt(tempVal) + 5);
		});
		$("#reps .incremental_button_down").live("mousedown", function(){
			var tempVal = $("#freps").val();
			if(tempVal > 0) $("#freps").val(parseInt(tempVal) - 1);
		});
		$("#reps .incremental_button_up").live("mousedown", function(){
			var tempVal = $("#freps").val();
			$("#freps").val(parseInt(tempVal) + 1);
		});
		
		//set the 'skip' button to skip sets
		$("#rep_skip_btn").live("mousedown", function(){
			$("#fweight").val('0');
			$("#freps").val('0');
			$("#weight_rep_log_form").submit();
		});
	}

	WeightTrainingWorkout.prototype.openPopup = function(gridLayout){		//public method opens the 'Weight Training' popup.  The layout type depends on the stage of the workout and is passed in as an argument.
		switch(gridLayout){
			case "two_column":
				var twoColumnGridWeightTraining = new GridTwoColumnLayout(this);
				this.parent.currentLayoutOpen = 1;	//set the current layout to 1 ('column grid') 
				TweenLite.to('#two_column_grid', .05, {autoAlpha:1, ease:Back.easeOut});
				break;
			case "split_list":
				//create new split grid object for the next stage of workout
				var splitListGridWeightTraining = new GridSplitListLayout();
				this.parent.currentLayoutOpen = 2;	//set the current layout to 2 ('split grid') 
				//close existing layout
				TweenLite.to('#two_column_grid', .05, {autoAlpha:0, ease:Back.easeOut});
				TweenLite.to('#two_split_grid', .05, {autoAlpha:1, ease:Back.easeOut});
				
				$("#two_split_grid #left_box ul li").live("mousedown touchstart", function(){	//code to trigger edits for a completed set
					var thisSet = $(this).children(":first").attr("id");
					if(thisSet.substr(0, 1) == "_"){
						editCompletedSet(thisSet.substr(3), $(this).find(".it_set_data").html(), $(this).attr("id").substr(6, 1), $(this).attr("id").substr(8));	
					}
				});
				break;	
		}
	};
		
	function openWeightTrainingSubmitWorkoutPopup(context){	//open the final workout popup (AHR/minutes/etc) that is specific to weight training
		//clear set data popup
		TweenLite.to('#two_split_grid', .5, {autoAlpha:0, ease:Back.easeOut});
		context.parent.currentLayoutOpen = 10;	//set the current layout to 10 ('stats submit')
		//show final AHR and minute popup form
		var weightWorkoutEntryPop = new PopupWindow(AppInterface.mainPanelGrid, "#workout_entry_popup", "includes/workout_log_form.html", "workout_entry", "Weight Training");
		weightWorkoutEntryPop.openPopup();
	}
	
	/*function showWeightTrainingPopup(){	
	}*/
	function editCompletedSet(setID, setDataToChange, exerciseNum, setNumToChange){	//open popup to make edits to a completed set
		var dataToChange = setDataToChange.split("|");
		var weightDataToChange = dataToChange[0];
		var repsDataToChange = dataToChange[1];
		$('#two_split_grid #edit_set_popup').load('includes/weight_edit_set_data.html?seed='+Math.random(), function(){
			$("#edit_weight").val(weightDataToChange);
			$("#edit_reps").val(repsDataToChange);
			$("#edit_set_num").val(setNumToChange);
			$("#edit_set_id").val(setID);
			$("#exercise_num").val(exerciseNum);
		});
	}
	
	WeightTrainingWorkout.prototype.submitEditsToWeightTrainingSetData = function(){	//send weight training set edits to database and update the display with the new data.
		var newWeight = $("#edit_weight").val();
		var newReps = $("#edit_reps").val();
		var setNum = $("#edit_set_num").val();
		var setID = $("#edit_set_id").val();
		var exerciseNum = $("#exercise_num").val();
		$('#two_split_grid #edit_set_popup').html("");
		$.ajax({
			type: "POST",
			url: "php/weightEditSetData.php",
			data: { setID: setID, setToEdit: setNum, newWeight: newWeight, newReps: newReps }
		}).done(function(msg){
			if(msg == 1) alert("Set edited successfully!");
			else alert("There was an error editing set.");
			$("#two_split_grid #left_box #option"+exerciseNum+"_"+setNum+" .it_set_data").html(newWeight+"|"+newReps);
		});
	}
	
	WeightTrainingWorkout.prototype.submitNewSetType = function(context){	//send new weight training set type to the database and update the list to include the new set type.
		var set_name = $("#s_set_name").val();
		var body_group = $("#s_body_group").val();
		$('#two_column_grid #add_set_type_popup').html("");
		$.ajax({
			type: "POST",
			url: "php/weightAddSetType.php",
			data: { set_name: set_name, body_group: body_group }
		}).done(function(msg){
			if(msg == 1) alert("Set added successfully!");
			else alert("There was an error adding the set.");
			GridTwoColumnLayout.prototype.getSetTypesForScrollList(context.aSelectedBodyGroups);	//reset scroll list
		});
	}
	
	window.WeightTrainingWorkout = WeightTrainingWorkout;	//make class available to global scope
	
}(window));

////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////*************BEGIN GLOBAL - Define these outside the class definition so they only run once.*************/
/////////////////////////////////////////////Global Variables
var activeWeightTrainingWorkout;	//current instance of a weight training workout
/////////////////////////////////////////////Global Listeners

//////////////////////////////////////////////Global Functions
function _relaySubmitEditsToWeightTrainingSetData(){  //this function receives a call from a submitted set edit (weight training) form and relays it to the 'active' object's class function
	WeightTrainingWorkout.prototype.submitEditsToWeightTrainingSetData();	
}
function _relaySubmitNewSetType(){  //this function receives a call from a submitted set edit (weight training) form and relays it to the 'active' object's class function
	WeightTrainingWorkout.prototype.submitNewSetType(activeWeightTrainingWorkout);	
}
///////////////////////////////////////////////////////////////
/*************END GLOBAL ***********************************************************************************/
///////////////////////////////////////////////////////////////

