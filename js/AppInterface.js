/*
AppInterface is the first class to run in the app.  It sets up the persistant status bar, initializes some global details and creates the main panel (MainPanelGrid) instance.  It also makes a function available to all classes that animated the score meter inside the persistant status bar.
*/
(function(window){
	AppInterface.mainPanelGrid = '';
	
	function AppInterface(){
		AppInterface.mainPanelGrid = new MainPanelGrid(this);
		showPersistentStatusBar();
		//disable whole page drag
		window.addEventListener('touchmove', function(e){
			e.preventDefault();
		}, false);
		
		/*$( ".draggable" ).draggable({		//keep in case I want to add a draggable function to the scroll list plug-in
		  connectToSortable: "#sortable",
		  helper: "clone",
		  revert: "invalid"
		});*/
		$( "ul, li" ).disableSelection();
		//$("#two_split_grid #left_box").mCustomScrollbar();	//attach plugin div scroller to workout list box
		
		
		//show workout history on button click in menu bar
		$("#progress_numbers_btn").mouseover(function(){
			
		}).mouseout(function(){
			
		}).click(function(){
			var showWorkoutHistory = new PopupWindow(AppInterface.mainPanelGrid, "#show_history", "includes/show_workout_history.php", "show_history");
			showWorkoutHistory.openPopup();	
		});
	};
	
	function showPersistentStatusBar(){
		theScore = $("#score_text_number").html();
		window.setTimeout(function() {  
			TweenLite.to("#status_bar", .5, {bottom:"-10px", ease:Back.easeOut, onComplete:function(){AppInterface.prototype.animateScoreMeter();}});
		}, 500); 
	}
	
	AppInterface.prototype.animateScoreMeter = function(){	//public function animates the score meter in the persistant toolbar.
		var newScore = parseInt(theScore) / 3000 * 195;
		TweenLite.to('#score_meter_bar', .4, {width:newScore, ease:Back.easeOut, delay:.4});
		//TweenLite.to('#score_txt', .4, {left:newScore, ease:Back.easeOut, delay:.4});
	}
	
	AppInterface.prototype.updateWorkoutSessionScore = function(mins, ahr, bonus){	//updates the text field on the workout popup window that displays the total session score
		document.getElementById("result").innerHTML = "+" + (Math.round(mins * ahr / 100) + parseInt(bonus));	
	}
	
	AppInterface.prototype.updateInterfaceData = function(newScore, newLevel, weeklyMinutes, weeklyExercises, lastExerciseDate, lastExerciseType){	//updates all data on interface after a score or other info is submitted.
		$("#score_text_number").html(newScore);	//update score meter with new score
		$("#level_badge_value").html(newLevel);	//update current level
		$("#total_weekly_mins").html(weeklyMinutes);
		$("#total_weekly_exercises").html(weeklyExercises);
		$("#last_workout_date").html(lastExerciseDate);
		$("#last_workout_type").html(lastExerciseType);
	}
	AppInterface.prototype.closeAllQuickAlerts = function(){	//removes all quick alert boxes from the screen
		$("#ALERTS").html("");
	}
	window.AppInterface = AppInterface;	//make class available to global scope
	
}(window));
