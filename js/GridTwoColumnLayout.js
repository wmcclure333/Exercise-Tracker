/*
GridTwoColumnLayout sets up the two-column layout (currently for weight training only) and controls the flow of the workout until the layout moves to another format (i.e. GridSplitListLayout). The two-column layout consists of two <ul>s, each in their own column and side-by-side.  The <ul>s are set up to interact with one another (dragging, sorting, etc).
*/
(function(window){	
	function GridTwoColumnLayout(parent){
		//public var
		this.parent = parent;
		
		//private var
		var _stageWid = window.innerWidth;	//grab stage dimensions to determine layout
		var _stageHei = window.innerHeight;	
		
		//getter/setter methods to grab private vars
		this.getStageWid = function(){return _stageWid;}
		this.getStageHei = function(){return _stageHei;}

		activeTwoColumnGrid = this; 	//set global var to this instance for use in global listeners/functions
		makeGridSortable(this);
		showGrid(this);
	}
	
	function makeGridSortable(context){		//private method makes any ".sortable" column in the two column grid sortable by click/dragging a list item
		$( ".sortable" ).sortable({
			serialize: { key: "sort" },
			revert: true,
			update: function(){
				var thisArray = $( this ).sortable( "toArray" );
				GridTwoColumnLayout.prototype.updateColoredRows(context, thisArray);
			}
		});
	}
	
	function showGrid(context){		//re-initializes two column grid CSS and fades it in
		$("#two_column_grid").css({ left: "15%", top: "15%", width: (context.getStageWid() * .7)+"px", height: (context.getStageHei() * .7)+"px" });
		TweenLite.to('#two_column_grid', .4, {autoAlpha:1, ease:Back.easeOut});	
		TweenLite.to('#full_screen_underlayer', .4, {autoAlpha:.8, ease:Back.easeOut});






		GridTwoColumnLayout.prototype.updateContinueButton(context);
		loadDataIntoGrid(context);
	}
	
	GridTwoColumnLayout.prototype.updateContinueButton = function(context){		//public method to update the copy on the continue button to match current stage of workout
		switch(context.parent.getCurStageOfWorkout()){
			case 1:
				$("#two_column_grid .add_btn").attr("id", "body_group_add_btn");	//update add body group button
				break;
			case 2:
				$("#two_column_grid #body_group_add_btn").attr("id", "set_type_add_btn");	//update add body group button to the add new set button
				$("#two_column_grid #body_group_continue_btn").attr("id", "sets_continue_btn");	//update continue button to allow a continue to the next stage
				break;
		}
	};
	
	GridTwoColumnLayout.prototype.closePopup = function(){		//closes the popup and deletes its content
		TweenLite.to('#full_screen_underlayer', .05, {autoAlpha:0, ease:Back.easeOut, delay:0});
		TweenLite.to('#two_column_grid', .05, {autoAlpha:0, ease:Back.easeOut});
	};

	function loadDataIntoGrid(context){	//pull in database data using a call to a php file
		$.ajax({	//get body group data and place it in the grid, then update row format
			type: "POST",
			url: "php/getTwoColumnListData.php",
			data: { list_type: "body_groups" }
		}).done(function(msg){
			$("#two_column_grid #left_column ul").html(msg);
			$("#two_column_grid #left_column").mCustomScrollbar("update");	//attach plugin div scroller to list box
			GridTwoColumnLayout.prototype.updateColoredRows(context);
		});
	}
	
	GridTwoColumnLayout.prototype.loadSetListOptions = function (context){	//load and format the set list after the body groups have been chosen and the continue button is clicked
		$("#two_column_grid #set_type_add_btn").live("click", function(){
			openAddSetTypePopup(context);	
		});
		context.parent.aSelectedBodyGroups = $(".sortable").sortable("toArray");	//send final list to global var
					
		var aFilterList = []; //create list of body group names to pass in the PHP DB grab
		for(var a = 0; a < context.parent.aSelectedBodyGroups.length; a++){
			aFilterList[a] = ($("#"+context.parent.aSelectedBodyGroups[a]+" .label_value").html());
			context.parent.aSelectedBodyGroups[a] = aFilterList[a];	//rewrite global var to hold body group name for access later
		}
		//close current pop up
		$("#two_column_grid #left_column ul").html('');
		$("#two_column_grid #right_column ul").html('');
		
		GridTwoColumnLayout.prototype.getSetTypesForScrollList(aFilterList);
	};
	
	function openAddSetTypePopup(context){	//Open popup box with fields to add a workout set type.  Available body groups are loaded into a drop down menu from an external php file.
		$('#two_column_grid #add_set_type_popup').load('includes/add_set_type_popup.html?seed='+Math.random(), function(){
			$("#s_body_group").html("");
			$.ajax({	//get body group data in an option list format for drop down menu
				type: "POST",
				url: "php/getTwoColumnListData.php",
				data: { list_type: "body_group_array" }
			}).done(function(msg){
				$("#s_body_group").append(msg);
			});
		});
	}

	GridTwoColumnLayout.prototype.getSetTypesForScrollList = function(aFilterList){  //Grab set list options from database via php and display in left side of grid
	console.log(aFilterList[0]);
		$.ajax({
			type: "POST",
			url: "php/getTwoColumnListData.php",
			data: { list_type: "sets", body_groups: aFilterList }
		}).done(function(msg){
			$("#two_column_grid #left_column ul").html(msg);
			$("#two_column_grid #right_column ul").html("");	//reset right column
			$("#two_column_grid #left_column").mCustomScrollbar("update");	//attach plugin div scroller to list box
		});
	}
	
	GridTwoColumnLayout.prototype.updateColoredRows = function(context, order){	//Public function reformats the <li> items in the column so that the background coloring alternates - making it easier to read.  The parameter 'order' is passed in after the user reorders the list.  'order' is an array containing the new order of <li> elements.
		if(order){		
			$("#two_column_grid #right_column li").css("background-color", "#dddddd");
			for(c = 1; c <= order.length; c++){
				if(c % 2 == 0){
					$("#two_column_grid #right_column li#"+order[c-1]).css("background-color", "#cccccc");
				}
			}
		}else{
			$("#two_column_grid #left_column li").css("background-color", "#dddddd");
			$("#two_column_grid #right_column li").css("background-color", "#dddddd");
			$("#two_column_grid #left_column li:even").css("background-color", "#cccccc");
			$("#two_column_grid #right_column li:odd").css("background-color", "#cccccc");
		}
	}
	
	window.GridTwoColumnLayout = GridTwoColumnLayout;	//make class available to global scope

////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////*************BEGIN GLOBAL - Define these outside the class definition so they only run once.*************/
/////////////////////////////////////////////Global Variables
	var activeTwoColumnGrid;	//current instance of the two column grid being used

/////////////////////////////////////////////Global Listeners
	$(".close_btn1").live("click", function(){ closeGridTwoColumnLayout();	});

	//add items from left column to right column when 'add btn' is clicked
	$("#two_column_grid li div.add_btn").live("touchstart mousedown", function(){
		thisOptionHtml = ($(this).parent().html());
		thisIdNum = $(this).parent().attr('id').substr(6);	//correct id for new element
		if($("#two_column_grid #right_column ul li#item"+thisIdNum ).length == 0){	//add new element, change btn class and restyle list
			//play click audio
			audio = $("#au_click_add")[0];
			//audio.play();
			
			$("#two_column_grid #right_column ul").append("<li id='item"+thisIdNum+"'>"+thisOptionHtml+"</li>");
			$("#two_column_grid #right_column ul li#item"+thisIdNum+" .add_btn").addClass("delete_btn").removeClass("add_btn");
			GridTwoColumnLayout.prototype.updateColoredRows(activeTwoColumnGrid);
		}
	});
		
	//code to remove items from left column on 'delete btn' click
	$("#two_column_grid li div.delete_btn").live('click', function(){
		//play click audio
		//audio = $("#au_click_delete")[0];
		//audio.play();
		
		$(this).parent().remove();
		GridTwoColumnLayout.prototype.updateColoredRows(activeTwoColumnGrid);
	});
	
	//after continue button is clicked after 'body group' selection, load up double column form with set list data, based on body groups chosen
	$("#two_column_grid #body_group_continue_btn").live("click", function(){
		activeTwoColumnGrid.parent.setCurStageOfWorkout(2);
		GridTwoColumnLayout.prototype.updateContinueButton(activeTwoColumnGrid);
		GridTwoColumnLayout.prototype.loadSetListOptions(activeTwoColumnGrid);
	});
	
	
	//after continue button is clicked, load up double split form with workout data
	$("#two_column_grid #sets_continue_btn").live("click", function(){
		////////////////need to rename the tag on this button to dynamically determine the grid layout type
		activeTwoColumnGrid.parent.openPopup("split_list");
	});
//////////////////////////////////////////////Global Functions
	$("#two_column_grid #left_column").mCustomScrollbar();	//attach plugin div scroller to list box
	
	function closeGridTwoColumnLayout(){ GridTwoColumnLayout.prototype.closePopup(); }

///////////////////////////////////////////////////////////////
/*************END GLOBAL ***********************************************************************************/
///////////////////////////////////////////////////////////////

}(window));
