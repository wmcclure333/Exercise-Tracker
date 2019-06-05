/*
Workout is a super class of other specific workout classes.
*/
(function(window){
	function Workout(){
		//private var
		var _curStageOfWorkout = 1;	
		
		//getter/setter methods to grab private vars
		this.getCurStageOfWorkout = function(){return _curStageOfWorkout;}
		this.setCurStageOfWorkout = function(e){_curStageOfWorkout = e;}
	}
	
	/*Workout.prototype.testit = function(){		
		alert("holy shite");
	}*/


	window.Workout = Workout;	//make class available to global scope
	
}(window));


