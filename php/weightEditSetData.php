<?php
	require(dirname(__FILE__).'/utility/AA_conf.php');
	
	$e_weight = $_POST['newWeight'];
	$e_reps = $_POST['newReps'];
	$e_set_num = $_POST['setToEdit'];
	$e_set_id = $_POST['setID'];
	
	$sql = "UPDATE zz_Exercise_Weight_Sets_Log SET set".$e_set_num."_w = '".$e_weight."', set".$e_set_num."_r = '".$e_reps."' WHERE log_id = '".$e_set_id."'";
	$result = mysql_query($sql);
	mysql_close($sql);
	echo $result;

?>