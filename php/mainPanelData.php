<?php 
	require(dirname(__FILE__).'/utility/AA_conf.php');
	
	$list_type = $_POST['list_type'];
	
	switch($list_type){
		case "get_exercise_types":
			getExerciseTypes();
			break;
		case "insert_exercise_types":
			insertExerciseTypes();
			break;
	}

	function getExerciseTypes(){
		//Grab all exercise types from the database
		$aExerciseIds = array();
		$aExerciseTypes = array();
		$aExerciseIcons = array();
		$aExerciseColors = array();
		$aExerciseLastWorkout = array();
		
		$sql = "SELECT * FROM zz_Exercise_Types ORDER BY ex_id ASC";
		$result = mysql_query($sql);
		$count_ex = 0;
		while($row = mysql_fetch_array($result)){
			$aExerciseIds[$count_ex] = $row['ex_id'];
			$aExerciseTypes[$count_ex] = $row['ex_name'];
			$aExerciseCodename[$count_ex] = str_replace(" ", "_", strtolower($row['ex_name']));		
			$aExerciseIcons[$count_ex] = $row['ex_icon'];
			$aExerciseColors[$count_ex] = $row['ex_color'];
			
			$sql2 = "SELECT ex_timestamp FROM zz_Exercise_Log WHERE ex_timestamp = (SELECT MAX(ex_timestamp) FROM zz_Exercise_Log WHERE ex_type = '".$aExerciseCodename[$count_ex]."')";
			$result2 = mysql_query($sql2);
			while($row2 = mysql_fetch_array($result2)){
				$aExerciseLastWorkout[$count_ex] = $row2['ex_timestamp'];
			}
			mysql_close($sql2);
			$today = strtotime(date("Ymd"));
     		$lastWorkoutDatestamp = strtotime($aExerciseLastWorkout[$count_ex]);
			$dateDiff = $today - $lastWorkoutDatestamp;
			$aExerciseLastWorkout[$count_ex] = floor($dateDiff/60/60/24);	//# of days since last workout stamp
			
			$count_ex++;
		}
		mysql_close($sql);
				
		//Output HTML panels for each exercise type
		echo $count_ex."|XXXCUTXXX|";
		for($c = 0; $c < count($aExerciseTypes); $c++){
			echo "<div id='panel".$aExerciseIds[$c]."' class='main_panel' panelCode='".$aExerciseCodename[$c]."'><div id='image".$aExerciseIds[$c]."' class='panel_overlay_btn' imgName='".$aExerciseTypes[$c]."'><img src='images/spacer.png'/></div><div class='panel_overlay_fader' age='".$aExerciseLastWorkout[$c]."'></div><div class='panel_highlight_line'></div><div class='panel_title'>".$aExerciseTypes[$c]."</div><img src='".$aExerciseIcons[$c]."' alt='".$aExerciseTypes[$c]."' border='0' /></div>";
		}
	}
	
	function insertExerciseTypes(){
		//Insert new exercise type to database
		//$default_workout_icon = "";
		//$default_workout_color = "";
		$workout_name = $_POST['workout_name'];
		$workout_icon_filename = $_POST['workout_icon_filename'];
		$sql = "INSERT INTO zz_Exercise_Types (ex_name, ex_icon) VALUES ('".$workout_name."', '".$workout_icon_filename."')";
		$result = mysql_query($sql);
		mysql_close($sql);
		echo $result;
	}
	
?>