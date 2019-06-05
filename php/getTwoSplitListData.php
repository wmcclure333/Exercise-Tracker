<?php 
	require(dirname(__FILE__).'/utility/AA_conf.php');
	require(dirname(__FILE__).'/dataLogWeightTraining.php');
	
	$list_type = $_POST['list_type'];
	$set_list = array();
	$set_list = $_POST['set_list'];
	$dataWeightTraining = new DataLogWeightTraining;	//holds all weight training data
	
	switch($list_type){
		case "workout_set":
			getWorkoutSet($dataWeightTraining, $set_list);
			break;
	}
	
	function getWorkoutSet($dataWeightTraining, $set_list){
		$aColorList = array("blue", "red", "green", "orange", "purple");	//values to change the font color for each body group
		$colorListCount = 0;	//counter for color change array index
		$numberOfFieldsPassedBackPerSet = 21;  //this is how many data fields will be passed back per set within $aHistoricWorkoutData.  This helps seperate multiple set types.
		$aHistoricWorkoutData = $dataWeightTraining->grabHistoricDataFromSetList($set_list);	//pull historic workout data
		$aBodyType = $dataWeightTraining->grabBodyTypeFromSetList($set_list);	//pull body type based on set type 
		for($c = 0; $c < count($set_list); $c++){
			echo "
			<li id='option".($c + 1)."_1'>
				<span id='notset_id1' class='hide database_set_insert_id'></span>
				<span class='set_btn_overlay'><img src='images/spacer.png' width='200' height='72' /></span>
				<div class='label'>
					<span class='label_value'><span class='set_name'>".$set_list[$c]."</span> - s1</span>
					<div style='color:".$aColorList[$colorListCount]."' class='sublabel'>".$aBodyType[$c]."</div>
				</div>
				<div class='icon_tray'>
					<div class='it_set_data'></div>
					<div class='it_goal_stars stars0'></div>
				</div>
				<div class='checkbox'></div>
				<div class='hide dt_last_date' id='last_date_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 0]."</div>
				<div class='hide s1w' id='s1w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 1]."</div>
				<div class='hide s1r' id='s1r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 2]."</div>
				<div class='hide b1w' id='b1w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 9]."</div>
				<div class='hide b1r' id='b1r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 10]."</div>
				<div class='hide note1' id='note1_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 17]."</div>
			</li>
			<li id='option".($c + 1)."_2'>
				<span class='set_btn_overlay'><img src='images/spacer.png' width='100%' height='72' /></span>
				<span id='notset_id2' class='hide database_set_insert_id'></span>
				<div class='label'>
					<span class='label_value'><span class='set_name'>".$set_list[$c]."</span> - s2</span>
					<div style='color:".$aColorList[$colorListCount]."' class='sublabel'>".$aBodyType[$c]."</div>
				</div>
				<div class='icon_tray'>
					<div class='it_set_data'></div>
					<div class='it_goal_stars stars0'></div>
				</div>
				<div class='checkbox'></div>
				<div class='hide s1w' id='s2w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 3]."</div>
				<div class='hide s1r' id='s2r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 4]."</div>
				<div class='hide b1w' id='b2w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 11]."</div>
				<div class='hide b1r' id='b2r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 12]."</div>
				<div class='hide note2' id='note2_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 18]."</div>
			</li>
			<li id='option".($c + 1)."_3'>
				<span class='set_btn_overlay'><img src='images/spacer.png' width='100%' height='72' /></span>
				<span id='notset_id3' class='hide database_set_insert_id'></span>
				<div class='label'>
					<span class='label_value'><span class='set_name'>".$set_list[$c]."</span> - s3</span>
					<div style='color:".$aColorList[$colorListCount]."' class='sublabel'>".$aBodyType[$c]."</div>
				</div>
				<div class='icon_tray'>
					<div class='it_set_data'></div>
					<div class='it_goal_stars stars0'></div>
				</div>
				<div class='checkbox'></div>
				<div class='hide s1w' id='s3w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 5]."</div>
				<div class='hide s1r' id='s3r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 6]."</div>
				<div class='hide b1w' id='b3w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 13]."</div>
				<div class='hide b1r' id='b3r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 14]."</div>
				<div class='hide note3' id='note3_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 19]."</div>
			</li>
			<li id='option".($c + 1)."_4'>
				<span class='set_btn_overlay'><img src='images/spacer.png' width='100%' height='72' /></span>
				<span id='notset_id4' class='hide database_set_insert_id'></span>
				<div class='label'>
					<span class='label_value'><span class='set_name'>".$set_list[$c]."</span> - s4</span>
					<div style='color:".$aColorList[$colorListCount]."' class='sublabel'>".$aBodyType[$c]."</div>
				</div>
				<div class='icon_tray'>
					<div class='it_set_data'></div>
					<div class='it_goal_stars stars0'></div>
				</div>
				<div class='checkbox'></div>
				<div class='hide s1w' id='s4w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 7]."</div>
				<div class='hide s1r' id='s4r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 8]."</div>
				<div class='hide b1w' id='b4w_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 15]."</div>
				<div class='hide b1r' id='b4r_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 16]."</div>
				<div class='hide note4' id='note4_".$c."'>".$aHistoricWorkoutData[($c*$numberOfFieldsPassedBackPerSet) + 20]."</div>
			</li>
			";
		}
	}
?>