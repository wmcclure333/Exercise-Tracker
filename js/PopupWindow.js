/*
PopupWindow is a class for all 'workout submit' popups and some utility popups such as 'adding workout types'.  It handles opening, closing and basic functions of the popup.  It also handles the form submissions that happen inside the popup.
*/
(function(window){
		
	function PopupWindow(mainGrid, handle, url, type, optionalCode){
		this.mainGrid = mainGrid;	//passed in parent should always be the MainPanelGrid object
		var context = this;

		//private vars
		var _tagHandle = handle;
		var _contentURL = url;
		var _popupType = type;
		var _panelName = optionalCode;
		if(_panelName) var _panelCode = optionalCode.toLowerCase().replace(" ", "_");  //this code is used for database insertion, not for display
		
		//getters and setters
		this.getTagHandle = function(){	return _tagHandle; }
		this.getContentURL = function(){	return _contentURL; }
		this.getPopupType = function(){	return _popupType; }
		this.getPanelName = function(){	return _panelName; }
		if(_panelName) this.getPanelCode = function(){	return _panelCode; }
	}
	
	PopupWindow.prototype.openPopup = function(){		//public method opens the popup
		AppInterface.prototype.closeAllQuickAlerts();	//remove all quick alert boxes from screen before opening popup
		var context = this;
		activePopup = context;
		$(this.getTagHandle()).load(this.getContentURL()+"?seed="+Math.random(), function(){
			//when loaded...
			positionPopup(context);
			if(context.getPanelName() == "Weight Training") $("#bonus").val(activeSplitListGrid.getWeightTrainingBonusPoints());
			TweenLite.to('#full_screen_underlayer', .4, {autoAlpha:.8, ease:Back.easeOut});
			TweenLite.to(this, .4, {autoAlpha:1, ease:Back.easeOut});
						$("#show_history #workout_history_ctr").mCustomScrollbar();	//attach plugin div scroller to workout history list

		});
	};
	
	PopupWindow.prototype.closePopup = function(context){		//closes the popup and deletes its content
		TweenLite.to('#full_screen_underlayer', .05, {autoAlpha:0, ease:Back.easeOut, delay:0});
		MainPanelGrid.prototype.clearExercises();
		context.mainGrid.currentExerciseOpen = 0;	
		TweenLite.to(context.getTagHandle(), .05, {autoAlpha:0, ease:Back.easeOut, delay:0, onComplete:function(){
			$(context.getTagHandle()).html("");
			activePopup = "";
		}});
	};

	function positionPopup(context){      //sets the position of the popup based on its size and window dimensions.  also sets workout code for workout entry popups
		if(context.getPanelName() != undefined){	//if the optional code parameter was passed to the constructor...
			if(context.getPanelCode() != "weight_training")
				$(context.getTagHandle() + " #bonus_block").css("display", "none");	//hide bonus field for all exercises that don't use it	
			$('#db_workout_type').attr('value', context.getPanelCode()); //set workout code for workout entry popups
			$('#workout_type_text').html(context.getPanelName());
		}
		
		var formWidAdjustment = $(context.getTagHandle()).width() / 2;
		var formHeiAdjustment = $(context.getTagHandle()).height() / 2;
		var topPos = (window.innerHeight / 2) - formHeiAdjustment + "px";
		var leftPos = (window.innerWidth / 2) - formWidAdjustment + "px";
		$(context.getTagHandle()).css({"top":topPos, "left":leftPos});
	}
	

	PopupWindow.prototype.submitForm = function(context){		//submits form based on popup type
		switch(context.getPopupType()){
			case "add_workout":
				var d=new Date(); var timeStamp=d.getTime(); // grab timestamp to tack onto icon filename.  This is to avoid file naming conflicts.
				var workout_name = $("#w_workout_name").val();
				var workout_icon_filename = "images/workoutIcon_" + timeStamp + "_" + $("#w_icon_file").val();
				closePopup(context);
				$.ajax({
					type: "POST",
					url: "php/mainPanelData.php",
					data: { workout_name: workout_name, workout_icon_filename: workout_icon_filename, list_type: "insert_exercise_types" }
				}).done(function(msg){
					if(msg == 1) alert("Workout added successfully!");
					else alert("There was an error adding the workout.");
					mainPanelGrid = new MainPanelGrid(); //reset main panel grid
				});
				break;
			case "workout_entry":
				var xtype = document.getElementById("db_workout_type").value;
				var mins = document.getElementById("mins").value;
				var ahr = document.getElementById("ahr").value;
				var bonus = document.getElementById("bonus").value;
				context.mainGrid.appInterface.updateWorkoutSessionScore(mins, ahr, bonus);
				$.ajax({
					type: "POST",
					url: "php/dataLogSubmit.php",
					data: { user_id: $("body").attr("userId"), w_type: xtype, w_minutes: mins, w_ahr: ahr, w_bonus: bonus }
				}).done(function(msg){
					var aReturnedValues = msg.split(',');
					AppInterface.prototype.updateInterfaceData(aReturnedValues[0], aReturnedValues[2], aReturnedValues[3], aReturnedValues[4], aReturnedValues[5], aReturnedValues[6]);
					var theScore = aReturnedValues[0];
					var caloriesBurned = aReturnedValues[1];
					$(".main_panel").each(function(){	//update "age" indicators on main panel buttons.  reset 'aged' workouts that were just performed.
						if($(this).attr("panelcode") == xtype){
							$(this).find(".panel_overlay_fader").attr("age",0);
							MainPanelGrid.prototype.updatePanelFadeIndicators();	
						}
					});
					aReturnedValues[0] = (Math.round((parseInt(mins) * parseInt(ahr)) / 100) + parseInt(bonus)) + " points scored.  <br/>"+caloriesBurned+" calories burned.";	//reset this index in the array to hold alert text for the current workout score and the calories burned.  Will be passed to the PopupQuickAlert class below.
					context.mainGrid.appInterface.animateScoreMeter();
					
					//show quick alerts for achievements and final workout score
					var quickAlert;
					
					for(var q = 0; q <= aReturnedValues.length - 1; q++){
						if(q < 1 || q > 6){	//skip indices that hold user data, not alert codes.  Index 0 holds the score and calories burned data - this is passed as an alert to show that the exercise was completed.  
							quickAlert = new PopupQuickAlert(aReturnedValues[q], q);
						}
					}
					closePopup(context);
				});
				break;
			default:
				break;
		}
	}
	
	window.PopupWindow = PopupWindow;	//make class available to global scope
	

/*************BEGIN GLOBAL - Define these outside the class definition so they only run once.*************/
	//Global Variables
	var activePopup;
	
	//Global Listeners
	$(".close_btn").live("click", function(){ closePopup();	});
	$(".submit_btn").live("click", function(){ submitForm(); });
	
	//Global Functions
	function closePopup(){ PopupWindow.prototype.closePopup(activePopup); }
	function submitForm(){ PopupWindow.prototype.submitForm(activePopup); }
/*************END GLOBAL ***********************************************************************************/

	
}(window));
