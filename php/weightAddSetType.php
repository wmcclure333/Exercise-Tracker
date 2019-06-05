<?php
	require(dirname(__FILE__).'/utility/AA_conf.php');
	
	$set_name = $_POST['set_name'];
	$body_group = $_POST['body_group'];
	
	$sql = "INSERT INTO zz_Exercise_Weight_Sets (set_name, body_group) VALUES ('".$set_name."','".$body_group."')";
	$result = mysql_query($sql);
	mysql_close($sql);
	echo $result;

?>