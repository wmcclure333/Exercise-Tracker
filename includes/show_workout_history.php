<div id="workout_history_title">Last 50 workouts</div>
<div id="workout_history_ctr">
<?php
	$thisDir = dirname(__FILE__);
	$dir = str_replace("/includes", "", $thisDir)."/php/utility/AA_conf.php";
	require($dir);
	
	$sql = "SELECT * FROM zz_Exercise_Log ORDER BY seed DESC LIMIT 50";
	$result = mysql_query($sql);
	$count_ex = 0;
	echo "	<div class='history_entry'>
				<div class='history_entry_field history_entry_field_date history_entry_header'>Date</div>
				<div class='history_entry_field history_entry_field_type history_entry_header'>Workout</div>
				<div class='history_entry_field history_entry_field_mins history_entry_header'>Mins</div>
				<div class='history_entry_field history_entry_field_ahr history_entry_header'>AHR</div>
				<div class='history_entry_field history_entry_field_bonus history_entry_header'>Bonus</div>
				<div class='history_entry_field history_entry_field_score history_entry_header'>Score</div>
				<div class='history_entry_field history_entry_field_cals history_entry_header'>Burned</div>
			</div>";
	while($row = mysql_fetch_array($result)){
		$day = substr($row['ex_timestamp'],6, 2);
		$mon = substr($row['ex_timestamp'],4, 2);
		if($mon[0] == "0") $mon = substr($mon, 1);
		$year = substr($row['ex_timestamp'],2, 2);
		$type = str_replace("_", " ", $row['ex_type']);
		$type = ucwords($type);
		$score = round($row['ex_ahr'] * $row['ex_minutes'] / 100 + $row['ex_bonus_pts']);
		echo "<div class='history_entry";
		if($count_ex % 2 == 0) echo " history_entry_gray";
		echo"'>";
		echo "<div class='history_entry_field history_entry_field_date'>".$mon."/".$day."/".$year."</div>";
		echo "<div class='history_entry_field history_entry_field_type'>".$type."</div>";
		echo "<div class='history_entry_field history_entry_field_mins'>".$row['ex_minutes']."</div>";
		echo "<div class='history_entry_field history_entry_field_ahr'>".$row['ex_ahr']."</div>";
		echo "<div class='history_entry_field history_entry_field_bonus'>".$row['ex_bonus_pts']."</div>";
		echo "<div class='history_entry_field history_entry_field_score'>".$score."</div>";
		echo "<div class='history_entry_field history_entry_field_cals'>".$row['ex_cals_burned']." cal</div>";
		echo "</div><!-- END history_entry -->";
		$count_ex++;
	}
	
?>
</div>
<div id="submit_btn">
    <div class="pop_btn_right close_btn" id="ahr_form_close_btn"></div>
</div> 
