<?php
/*
DataLog holds all workout data entries and current point/level totals for a particular user.
*/
require(dirname(dirname(__FILE__)).'/utility/AA_conf.php');
require(dirname(dirname(__FILE__)).'/dataHolders/dataLogEntry.php');
require(dirname(dirname(__FILE__)).'/dataHolders/dataLogEntryWeightTraining.php');

class DataLog{
	public $user_id = "";
	public $current_level = 0;
	public $current_score = 0;
	public $current_week = "";
	public $current_max_hr = 0;
	public $current_day_in_week = 0;
	public $total_weekly_minutes = 0;
	public $total_weekly_exercises = 0;
	public $a_current_alerts = array();
	public $a_last_workout = array();
	//public $num_entries = 0;
	//public $a_data_log_entries = array();
	
	public function DataLog($userId, $initUserData){
		$this->user_id = $userId;
		$this->grabCurrentUserData($this->user_id);
		$this->grabLastWorkoutData($this->user_id);
		if($initUserData){	//if this is a first run, check and update past data.
			//$this->grabDataEntries();
			$this->checkCurrentWeek();
		}
	}
	
	
	public function updateTotalScore($adjustment, $addWeeklyMinutes, $addWeeklyExercise){	//function takes $adjustment attribute and adds it to the user's total score.  The $addWeeklyMinutes attribute is optional and takes a boolean value.  If 1, it adds $adjustment to the weekly total score.  The $addWeeklyExercise is optional and takes a boolean 1 or 0 - if 1, the weekly exercise total increases by 1.
		//calculate current milestone from score.  milestones are at 500, 1000, and 2000
		if($this->current_score >= 2000)
			$milestone = 2000;
		else if($this->current_score >= 1000){
			$milestone = 1000;
			if($this->current_score + $adjustment >= 2000)	//if new score passes 2000, log a milestone achievement
				$this->insertAchievementAlertIntoDB(92, date("Ymd"));
		}else if($this->current_score >= 500){
			$milestone = 500;
			if($this->current_score + $adjustment >= 1000)	//if new score passes 1000, log a milestone achievement
				$this->insertAchievementAlertIntoDB(91, date("Ymd"));
		}else{ 
			$milestone = 0;
			if($this->current_score + $adjustment >= 500)	//if new score passes 500, log a milestone achievement
				$this->insertAchievementAlertIntoDB(90, date("Ymd"));
		}
		
		$newLevel = 0;
		if($this->current_score + $adjustment >= 3000){	//if a new level has been passed
			$this->current_score = ($this->current_score + $adjustment) - 3000;
			$newLevel = 1;
			$this->current_level++;
			$this->insertAchievementAlertIntoDB(9999, date("Ymd"));
		}else if($this->current_score + $adjustment < $milestone)  //if current score plus penalties is below current milestone
			$this->current_score = $milestone;	
		else
			$this->current_score += $adjustment;  //apply achievements/penalties from attrib
			
		//init variables for weekly adjustments
		if($addWeeklyMinutes){
			$oldMinutesTotal = $this->total_weekly_minutes;
			$this->total_weekly_minutes += $adjustment;
			if($oldMinutesTotal < 125 && $this->total_weekly_minutes >= 125){
				$this->insertAchievementAlertIntoDB(6, date("Ymd"));
				$this->current_score += 50;
			}else if($oldMinutesTotal < 75 && $this->total_weekly_minutes >= 75){
				$this->insertAchievementAlertIntoDB(5, date("Ymd"));
				$this->current_score += 25;
			}
		}
		if($addWeeklyExercise){
			$this->total_weekly_exercises++;
			if($this->total_weekly_exercises == 3){
				$this->insertAchievementAlertIntoDB(1, date("Ymd"));
				$this->current_score += 25;
			}else if($this->total_weekly_exercises == 4){
				$this->insertAchievementAlertIntoDB(2, date("Ymd"));
				$this->current_score += 50;
			}else if($this->total_weekly_exercises == 5){
				$this->insertAchievementAlertIntoDB(3, date("Ymd"));
				$this->current_score += 75;
			}else if($this->total_weekly_exercises == 6){
				$this->insertAchievementAlertIntoDB(4, date("Ymd"));
				$this->current_score += 100;
			}
		}
		//Insert new score into DB
		$sql = "UPDATE zz_Exercise_User_Data SET ex_current_level = '".$this->current_level."',ex_current_score = '".$this->current_score."', ex_total_weekly_minutes = '".$this->total_weekly_minutes."', ex_total_weekly_exercises = '".$this->total_weekly_exercises."' WHERE ex_user_id = '".$this->user_id."'";
		$result = mysql_query($sql);
		mysql_close($sql);
	}
	
	
	private function grabCurrentUserData($userId){
		$sql = "SELECT * FROM zz_Exercise_User_Data WHERE ex_user_id = '".$userId."'";
		$result = mysql_query($sql);
	
		while($row = mysql_fetch_array($result)){
			$this->current_level = $row['ex_current_level'];
			$this->current_score = $row['ex_current_score'];
			$this->current_week = $row['ex_current_week'];
			$this->current_max_hr = $row['ex_max_hr'];
			$this->total_weekly_minutes = $row['ex_total_weekly_minutes'];
			$this->total_weekly_exercises = $row['ex_total_weekly_exercises'];
		}
		mysql_close($sql);
	}
	private function grabLastWorkoutData($userId){	//grab data from the last workout for this user and store in public variables that the interface can access
		$sql = "SELECT ex_type, ex_timestamp FROM zz_Exercise_Log WHERE ex_timestamp = (SELECT MAX(ex_timestamp) FROM zz_Exercise_Log)";
		$result = mysql_query($sql);
		while($row = mysql_fetch_array($result)){
			$this->a_last_workout['date'] = strtotime($row['ex_timestamp']);
			$this->a_last_workout['type'] = ucwords(str_replace("_", " ", $row['ex_type']));
		}
		mysql_close($sql);
	}
	private function checkCurrentWeek(){	//'current week' is the most recent week that there was a workout.  Coming in to this function, there may have been multiple weeks since the 'current week' where no workout were logged. 
		$today = strtotime(date("Ymd"));
     	$curWeek = strtotime($this->current_week);
     	$dateDiff = $today - $curWeek;
   		$dateDiff = floor($dateDiff/60/60/24);	//# of days since 'current week' stamp

		if($dateDiff >= 7){  //if today is not within the 'current week', calculate penalties from missed workouts and resets the current week
			$total_penalty_to_apply = 0;
			//check 'current week' to see how many exercises were performed, apply penalty if necessary
			if($this->total_weekly_exercises == 0){
				$this->insertPenaltyAlertIntoDB(1, date('Ymd', $curWeek));	//Insert new alert into DB for this penalty		
				$total_penalty_to_apply -= 50;
			}else if($this->total_weekly_exercises <= 2){
				$this->insertPenaltyAlertIntoDB(2, date('Ymd', $curWeek));	//Insert new alert into DB for this penalty		
				$total_penalty_to_apply -= 25;
			}
			
			//calculate penalties for missed (unlogged) weeks
			$numWeeksDiff = floor($dateDiff / 7) - 1;	//# of unlogged weeks since last workout, not including this week
			$tempPreviousWeek = $curWeek;
			for($c = 1; $c <= $numWeeksDiff; $c++){	//loop through all weeks passed, except for 'current week'
				$alertThisWeek = date('Ymd', strtotime('+7 days', $tempPreviousWeek));	//move 1 week ahead
				$this->insertPenaltyAlertIntoDB(1, $alertThisWeek);	
				$tempPreviousWeek = strtotime($alertThisWeek);		
				
				//apply penalty for this missed week
				$total_penalty_to_apply -= 50;
			}
			
			$this->updateTotalScore($total_penalty_to_apply);
			
			//find start day of the new current week.  Must be the same weekday that the 1st week started on (Mon, Tues...)
			$newStartDateForCurrentWeek = date('Ymd', $curWeek);
			$tempPreviousWeek = $curWeek;
			while($stopLoop == 0){	//increase week by week until you find the date of the correct weekday in the current week
				$tryThisWeek = date('Ymd', strtotime('+7 days', $tempPreviousWeek));
				if($tryThisWeek > date("Ymd")){	//this week is past today's date.  End loop and keep the last week that was tried.
					$stopLoop = 1;	
				}else{
					$tempPreviousWeek = strtotime($tryThisWeek);	
					$newStartDateForCurrentWeek = $tryThisWeek;
				}
			}
			$this->updateCurrentWeekInDbTo($newStartDateForCurrentWeek);	
		}
		$this->current_day_in_week = $dateDiff % 7 + 1;	//assign value to public var.  It holds the current day # in the current week (1-7).
	}
	private function insertAchievementAlertIntoDB($achievementType, $alertDateStamp){
		switch($achievementType){
			case 1:
				$alertMessage = "ACHIEVEMENT: +25   3 workouts logged this week: ".$alertDateStamp;
				break;	
			case 2:
				$alertMessage = "ACHIEVEMENT: +50   4 workouts logged this week: ".$alertDateStamp;
				break;	
			case 3:
				$alertMessage = "ACHIEVEMENT: +75   5 workouts logged this week: ".$alertDateStamp;
				break;	
			case 4:
				$alertMessage = "ACHIEVEMENT: +100   6 workouts logged this week: ".$alertDateStamp;
				break;	
			case 5:
				$alertMessage = "ACHIEVEMENT: +25   75+ minutes logged this week: ".$alertDateStamp;
				break;	
			case 6:
				$alertMessage = "ACHIEVEMENT: +50   125+ minutes logged this week: ".$alertDateStamp;
				break;	
			case 90:
				$alertMessage = "MILESTONE: You reached 500 points! ".$alertDateStamp;
				break;	
			case 91:
				$alertMessage = "MILESTONE: You reached 1000 points! ".$alertDateStamp;
				break;	
			case 92:
				$alertMessage = "MILESTONE: You reached 2000 points! ".$alertDateStamp;
				break;	
			case 9999:
				$alertMessage = "ACHIEVEMENT: You reached a new level! ".$alertDateStamp;
				break;	
		}
		
		array_push($this->a_current_alerts, $achievementType);	//add this achievement type to array.  This will eventually be passed to the front end to trigger alerts
		
		//Insert new alert into DB for this achievement
		$sql = "INSERT INTO zz_Exercise_User_Alerts (user_id, alert_type, alert_message, alert_datestamp) VALUES ('".$this->user_id."','achievement','".$alertMessage."','".$alertDateStamp."')";
		$result = mysql_query($sql);
		mysql_close($sql);
	}
	
	private function insertPenaltyAlertIntoDB($penaltyType, $alertThisWeek){
		switch($penaltyType){
			case 1:
				$alertMessage = "PENALTY: -50   0 workouts logged for the week of ".$alertThisWeek;
				break;	
			case 2:
				$alertMessage = "PENALTY: -25   Less than 3 workouts logged for the week of ".$alertThisWeek;
				break;	
		}
		$dateStamp = date('Ymd', strtotime('+6 days', strtotime($alertThisWeek)));	//this is the 'recorded' date stamp of the penalty.  It's the last day of the week in which the penalty happened.
		
		//Insert new alert into DB for this missed week penalty
		$sql = "INSERT INTO zz_Exercise_User_Alerts (user_id, alert_type, alert_message, alert_datestamp) VALUES ('".$this->user_id."','penalty','".$alertMessage."','".$dateStamp."')";
		$result = mysql_query($sql);
		mysql_close($sql);
	}
	
	private function updateCurrentWeekInDbTo($thisWeek){
		//Update current week info in DB
		$sql = "UPDATE zz_Exercise_User_Data SET ex_current_week = '".$thisWeek."', ex_total_weekly_minutes = '0', ex_total_weekly_exercises = '0' WHERE ex_user_id = '".$this->user_id."'";
		$result = mysql_query($sql);
		mysql_close($sql);
	}
	
	/*private function grabDataEntries(){	//this is currently phased out.  I'll keep it in case there's a use for it later on.
		$sql = "SELECT * FROM zz_Exercise_Log";
		$result = mysql_query($sql);
	
		while($row = mysql_fetch_array($result)){
			$this_type = $row['ex_type'];
			switch($this_type){
				case "weight_training":
					$this_entry = new DataLogEntryWeightTraining($row);
					break;
				default:	//all other workout types not specified above
					$this_entry = new DataLogEntry($row);
					break;
			}
			$this_pts = $this_entry->daily_score;
			$this->a_data_log_entries[$this->num_entries] = $this_entry;
			$this->current_score += (int)$this_pts;
			$this->num_entries++;
		}
		mysql_close($sql);
	}*/
}
?>