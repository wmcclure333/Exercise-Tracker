<?php
	require(dirname(__FILE__).'/utility/AA_conf.php');
		
	$w_weight = $_POST['w_weight'];
	$w_reps = $_POST['w_reps'];
	$w_note = $_POST['w_note'];
	$w_session = $_POST['w_session'];
	$w_set_type = $_POST['w_set_type'];
	$w_current_set = $_POST['w_current_set'];
	$w_timestamp = date('YmdHi');
	
	//grab set id from set list in DB
	$sql = "SELECT * FROM zz_Exercise_Weight_Sets WHERE set_name = '".$w_set_type."'";
	$result = mysql_query($sql);
	while($row = mysql_fetch_array($result)){
		$w_set_id = $row['set_id'];
	}
	mysql_close($sql);
	
	
	if($w_session == 0){	//if sessions isn't already established
		//Insert new log into DB
		$sql = "INSERT INTO zz_Exercise_Weight_Sets_Log (set_id, set_name, time_stamp, set1_w, set1_r, set1_note) VALUES ('".$w_set_id."','".$w_set_type."','".$w_timestamp."','".$w_weight."','".$w_reps."','".$w_note."')";
		$result = mysql_query($sql);
		$new_session = mysql_insert_id();
		echo $new_session;
		mysql_close($sql);
	}else{ //if updating existing session
		switch($w_current_set){
			case '2':
				$sql = "UPDATE zz_Exercise_Weight_Sets_Log SET set2_w = '".$w_weight."', set2_r = '".$w_reps."', set2_note = '".$w_note."' WHERE log_id = '".$w_session."'";
				break;	
			case '3':
				$sql = "UPDATE zz_Exercise_Weight_Sets_Log SET set3_w = '".$w_weight."', set3_r = '".$w_reps."', set3_note = '".$w_note."' WHERE log_id = '".$w_session."'";
				break;	
			case '4':
				$sql = "UPDATE zz_Exercise_Weight_Sets_Log SET set4_w = '".$w_weight."', set4_r = '".$w_reps."', set4_note = '".$w_note."' WHERE log_id = '".$w_session."'";
				break;	
		}
		$result = mysql_query($sql);
		mysql_close($sql);
		echo $w_session;
	}

	
	
?>