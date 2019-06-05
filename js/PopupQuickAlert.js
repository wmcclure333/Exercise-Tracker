/*
PopupQuickAlert 
*/
(function(window){
	//static var
	PopupQuickAlert.aAlertBoxPositionX = new Array(50, 210, 50, 210, 370, 50, 370, 210, 370);	//holds position values for displaying up to 9 quick alerts
	PopupQuickAlert.aAlertBoxPositionY = new Array(50, 50, 210, 210, 50, 370, 210, 370, 370);

	function PopupQuickAlert(alertCode, numInSequence){
		//private var
		var _alertCode = alertCode;
		var _numInSequence = numInSequence;	
		//getter/setter methods 
		this.getAlertCode = function(){return _alertCode;}
		this.getNumInSequence = function(){return _numInSequence;}

		showAlert(this);
	}
	
	function showAlert(context){
		var thisSequenceNum = context.getNumInSequence();
		var alertMessage = "";
		
		if(thisSequenceNum == 0){	//for the actual score of the submitted workout... 
			alertMessage = "WORKOUT COMPLETE! <br/>"+context.getAlertCode();
		}else{	//for the alerts triggered by the workout
			thisSequenceNum -= 6;
			switch(parseInt(context.getAlertCode())){
				case 1:
					alertMessage = "ACHIEVEMENT: +25   3 workouts logged this week";
					break;
				case 2:
					alertMessage = "ACHIEVEMENT: +50   4 workouts logged this week";
					break;	
				case 3:
					alertMessage = "ACHIEVEMENT: +75   5 workouts logged this week";
					break;	
				case 4:
					alertMessage = "ACHIEVEMENT: +100   6 workouts logged this week";
					break;	
				case 5:
					alertMessage = "ACHIEVEMENT: +25   75+ minutes logged this week";
					break;	
				case 6:
					alertMessage = "ACHIEVEMENT: +50   125+ minutes logged this week";
					break;	
				case 90:
					alertMessage = "MILESTONE: You reached 500 points!";
					break;	
				case 91:
					alertMessage = "MILESTONE: You reached 1000 points!";
					break;	
				case 92:
					alertMessage = "MILESTONE: You reached 2000 points!";
					break;	
				case 9999:
					alertMessage = "ACHIEVEMENT: You reached a new level!";
					break;	
			}
		}
		
		TweenLite.to('#_delayForShowingAlert', (.1 * (thisSequenceNum)), {onComplete:function(){
			$("#ALERTS").append('<div id="alert_box'+thisSequenceNum+'" class="alert_box">'+alertMessage+'</div>');
			$("#ALERTS #alert_box"+thisSequenceNum).css({"top":PopupQuickAlert.aAlertBoxPositionY[thisSequenceNum]+"px", "left":PopupQuickAlert.aAlertBoxPositionX[thisSequenceNum]+"px"});
			
			$("#ALERTS #alert_box"+thisSequenceNum).click(function(){
				hideAlert(thisSequenceNum);
			});
		}});
	}
	
	function hideAlert(thisSequenceNum){
		$("#ALERTS #alert_box"+thisSequenceNum).remove();
	}
	
	window.PopupQuickAlert = PopupQuickAlert;	//make class available to global scope
	
}(window));

