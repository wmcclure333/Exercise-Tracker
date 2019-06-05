/*
MainPanelGrid is a class for the base navigation of the app.  The panels represent workouts and they start each workout process on click.
*/
(function(window){
	
	//static vars
	MainPanelGrid.maxPanelsPerRow = 5;	
	MainPanelGrid.outerGutterSize = 40;		//left & right gutter of main panel
	MainPanelGrid.containerWidthBuffer = 12;	//buffer added to allow panels to fit in container *bug fix*
	MainPanelGrid.panelDefaultSize = 465;	//default maximum width and height of main exercise panels

	function MainPanelGrid(appInterface){
		//public var
		this.appInterface = appInterface;
		this.currentExerciseOpen = 0;	//code for the currently running exercise. 0=no exercise, 1=, 2=, 3=weight training, 4=, 5=, 6=, 7=
		this.currentLayoutOpen = 0;	//code for the currently open layout. 0=no layout, 1=two column, 2=split grid, 10=stat submit
		this.screenWid = 1024;	//based on ipad dimensions;
		this.screenHei = 768;

		//private var
		var _numExercises = 0;	//# of exercises pulled from DB
		this.getNumExercises = function(){	
			return _numExercises;	
		}
		this.setNumExercises = function(num){	
			_numExercises = num;	
		}

		getGridData(this);
	}
	
	function getGridData(context){	
		//import panel data from DB and display panels in a row
		$.ajax({
			type: "POST",
			url: "php/mainPanelData.php",
			data: { list_type: "get_exercise_types" }
		}).done(function(msg){
			aTempData = msg.split("|XXXCUTXXX|");
			context.setNumExercises(parseInt(aTempData[0]));
			$("#panel_row1").html(aTempData[1]);
			MainPanelGrid.prototype.updatePanelFadeIndicators();
			calcPanelDimensions(context);
		});
	}
	
	MainPanelGrid.prototype.updatePanelFadeIndicators = function(){	//adjust the fade on each panel to indicate it's 'age' - how long ago was the last workout
		$(".main_panel").each(function(){
			var thisPanelsAge = $(this).find(".panel_overlay_fader").attr("age");
			var fadeAmount = 0;
			if(thisPanelsAge > 56) fadeAmount = .6; 
			else if(thisPanelsAge > 49) fadeAmount = .5; 
			else if(thisPanelsAge > 42) fadeAmount = .4; 
			else if(thisPanelsAge > 35) fadeAmount = .3; 
			else if(thisPanelsAge > 28) fadeAmount = .2; 
			else if(thisPanelsAge > 21) fadeAmount = .15; 
			else if(thisPanelsAge > 14) fadeAmount = .1; 
			else if(thisPanelsAge > 7) fadeAmount = .05; 

			TweenLite.to($(this).find(".panel_overlay_fader"), .5, {alpha:fadeAmount, ease:Back.easeOut, delay:0});
		});
	}
	function calcPanelDimensions(context){
		//calculate dimensions of panels based on # of panels and width of window
		var newPanelSize;
		usableWidth = context.screenWid - (MainPanelGrid.outerGutterSize * 2);  //screen width minus left and ride side gutters
		$("#main_workout_panels").width(usableWidth + MainPanelGrid.containerWidthBuffer);
		if(context.getNumExercises() <= MainPanelGrid.maxPanelsPerRow){		//if only one row of panels...
			newPanelSize = Math.floor(usableWidth / context.getNumExercises());
			if(newPanelSize > MainPanelGrid.panelDefaultSize) newPanelSize = MainPanelGrid.panelDefaultSize;
		}else{
			newPanelSize = Math.floor(usableWidth / MainPanelGrid.maxPanelsPerRow);
		}
		resetLayoutDimensionsTo(newPanelSize, context);
	}
	
	function resetLayoutDimensionsTo(newPanelSize, context){  //resize panels based on calculated dimensions (newPanelSize) and adjust some CSS positioning of the interface
		$(".main_panel img").height(84);
		$(".main_panel img").width(newPanelSize);
		$(".main_panel .panel_title").each(function(){	//adjust font size depending on length of title
			var lengthOfTitle = $(this).html().length;
			var fontSize = 0;
			if(lengthOfTitle < 7) fontSize = 34;
			else if(lengthOfTitle < 12) fontSize = 30;
			else if(lengthOfTitle < 18) fontSize = 24;
			else fontSize = 20;
			$(this).css("fontSize", fontSize+"px");
		});
		$("#status_bar_top img").attr("width", $(window).width());	//adjust top of status bar to full browser width
		var badgeXPos = $("#status_bar_middle_column").width() / 2 - 116;
		$("#level_badge").css("left", badgeXPos+"px");
		
		activatePanelButtons(context);
	}
	
	function showLiveExercise(context){	//show active popup that's been closed but is still in mid-workout
		TweenLite.to('#full_screen_underlayer', .05, {autoAlpha:.8, ease:Back.easeOut});
		if(context.currentLayoutOpen == 1){	//show the correct layout based on where the workout was left
			TweenLite.to('#two_column_grid', .05, {autoAlpha:1, ease:Back.easeOut});
		}else if(context.currentLayoutOpen == 2){
			TweenLite.to('#two_split_grid', .05, {autoAlpha:1, ease:Back.easeOut});	
		}
	}

	MainPanelGrid.prototype.clearExercises = function(){	//removes all data from layouts to reset the workout
		$("#two_column_grid #right_column ul").html("");
		$("#two_split_grid #left_box ul").html("");
		$("#two_split_grid #right_box").html("");
		//reset all scrollbars
		$("#two_column_grid #left_column").mCustomScrollbar("destroy");	
		$("#two_column_grid #left_column").mCustomScrollbar();	
		$("#two_split_grid #left_box").mCustomScrollbar("destroy");	
		$("#two_split_grid #left_box").mCustomScrollbar();	
		$("#sets_continue_btn").attr("id", "body_group_continue_btn"); //reset continue button name in case of a workout reset
		//unbind all events attached to continue and submit buttons
		$("#body_group_continue_btn").die();
		$("#sets_continue_btn").die();
		$("#submit_weight_workout_btn").die();
	}
	
	function activatePanelButtons(context){  //set up listeners for panel buttons and other related buttons
		$("#exercise_type_add_btn").live("mousedown touchstart", function(){
			var addWorkoutPop = new PopupWindow(AppInterface.mainPanelGrid, "#add_workout_type_popup", "includes/add_workout_type_popup.html", "add_workout");
			addWorkoutPop.openPopup();	
		});
		
		$('.main_panel .panel_overlay_btn').live('mousedown touchstart', function(){
			var this_panel_id = $(this).attr('id').substr(5);
			var this_panel_name = $(this).attr('imgName');
			
			switch(this_panel_id){	//trigger special case entry forms, otherwise trigger default entry form
				case '7':  //trigger weight training log form
					if(context.currentLayoutOpen != 0){
						showLiveExercise(context);
					}else{
						MainPanelGrid.prototype.clearExercises();
						var weightTrainingWorkoutEntry = new WeightTrainingWorkout(context);
						weightTrainingWorkoutEntry.openPopup("two_column");
					}
					break;
				default://trigger default log form
					var workoutEntryPop = new PopupWindow(AppInterface.mainPanelGrid, "#workout_entry_popup", "includes/workout_log_form.html", "workout_entry", this_panel_name);
					workoutEntryPop.openPopup();
					break;
			}
		});
	}
	
	window.MainPanelGrid = MainPanelGrid;	//make class available to global scope
	
}(window));
