<?php
/*
	This utility is called when a workout log form is submitted (workout_log_form.html).  The workout data is logged to the DB, the workout score is calculated and then logged to the user's record in the DB.  The user's total score is then sent back to the front end for updating the display.
*/
	require(dirname(__FILE__).'/utility/AA_conf.php');
	require(dirname(__FILE__).'/dataHolders/dataLog.php');
		
	$thisTime = date('Ymd');
	$userId = $_POST['user_id'];
	$thisType = $_POST['w_type'];
	$thisMinutes = $_POST['w_minutes'];
	$thisAhr = $_POST['w_ahr'];
	$thisBonus = $_POST['w_bonus'];
	$calsBurned = round(((-55.0969 + (0.6309 * $thisAhr) + (0.09017 * 191) + (0.2017 * 33))/4.184) * 60 * ($thisMinutes / 60));  //calculate calories burned based on bio data and workout data.  NEED TO UPDATE THIS AT SOME POINT WITH DYNAMIC BIO INFO.  HARD CODED GENDER, AGE & WEIGHT RIGHT NOW.
	/* Formulas are based on this
	Male: ((-55.0969 + (0.6309 x AHR) + (0.09017 x WEIGHT) + (0.2017 x AGE))/4.184) x 60 x (TIME / 60)
	Female: ((-20.4022 + (0.4472 x AHR) - (0.057289 x WEIGHT) + (0.074 x AGE))/4.184) x 60 x (TIME / 60)
*/

	//Insert new log into DB
	$sql = "INSERT INTO zz_Exercise_Log (ex_type, ex_minutes, ex_ahr, ex_bonus_pts, ex_cals_burned, ex_timestamp) VALUES ('".$thisType."','".$thisMinutes."','".$thisAhr."','".$thisBonus."','".$calsBurned."','".$thisTime."')";
	$result = mysql_query($sql);
	mysql_close($sql);
	
	//Re-calculate total score with new data and send to the DB with a temporary object
	$newScore = round($thisMinutes * $thisAhr / 100) + $thisBonus;
	$tempData = new DataLog(1);		//temp object var to hold and calculate data for this particular workout
	$weeklyExerciseAdjustment = '';  if($thisMinutes >= 15) $weeklyExerciseAdjustment = 1;	//make sure exercise was 15 or more minutes before counting it
	$tempData->updateTotalScore($newScore, 1, $weeklyExerciseAdjustment);	//update score and set quick alerts for achievements
	$curScore += $tempData->current_score;
	
	$aReturnJavascriptValues = $tempData->a_current_alerts;  //this array holds the score at position 0 and then alert codes at the remaining positions to trigger popups in the front end.
	
	//add updated user data to the front of the array before the alert codes
	array_unshift($aReturnJavascriptValues, $tempData->a_last_workout['type']);	//index[6]
	array_unshift($aReturnJavascriptValues, date("m/d/Y", $tempData->a_last_workout['date']));	//index[5]
	array_unshift($aReturnJavascriptValues, $tempData->total_weekly_exercises);	//index[4]
	array_unshift($aReturnJavascriptValues, $tempData->total_weekly_minutes);	//index[3]
	array_unshift($aReturnJavascriptValues, $tempData->current_level);	//index[2]
	array_unshift($aReturnJavascriptValues, $calsBurned);	//index[1]
	array_unshift($aReturnJavascriptValues, $curScore);	//add the score to the front of the array - index[0]
	
	$returnJavascriptValuesString = implode(',', $aReturnJavascriptValues);	//format the array into a passable string
	echo $returnJavascriptValuesString;	//return score to update the score meter and other data to front end 
	 
?>