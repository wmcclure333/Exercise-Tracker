<?php 
	require(dirname(__FILE__).'/utility/AA_conf.php');
	require(dirname(__FILE__).'/dataLogWeightTraining.php');
	
	$list_type = $_POST['list_type'];
	$body_groups = array();
	$body_groups = $_POST['body_groups'];
	$dataWeightTraining = new DataLogWeightTraining;	//holds all weight training data
	
	switch($list_type){
		case "body_groups":
			getBodyGroups($dataWeightTraining);
			break;
		case "body_group_array":
			getBodyGroupOptionList($dataWeightTraining);
			break;
		case "sets":
			getSets($dataWeightTraining, $body_groups);
			break;
	}

	function getBodyGroups($dataWeightTraining){
		//Grab most recent timestamp for all Body Groups (in alphabetical order by body group name)
		$aBodyGroupsMostRecent = $dataWeightTraining->grabHistoricDataFromBodyPartList();
		
		//Output HTML list items for each Body Group
		for($c = 0; $c < count($dataWeightTraining->a_body_groups); $c++){
			echo "<li id='option".($c + 1)."'><div class='label'><span class='label_value'>".$dataWeightTraining->a_body_groups[$c]."</span><div class='sublabel'>Last Exercise: ".$aBodyGroupsMostRecent[$c]."</div></div><div class='add_btn'><img src='/images/spacer.png' width='30' height='30' /></div></li>";
		}
	}
	
	function getBodyGroupOptionList($dataWeightTraining){
		//Return all body groups in an options list for dropdown menu
		for($c = 0; $c < count($dataWeightTraining->a_body_groups); $c++){
			echo "<option value='".$dataWeightTraining->a_body_groups[$c]."'>".$dataWeightTraining->a_body_groups[$c]."</option>";
		}
	}
	
	function getSets($dataWeightTraining, $body_groups){
		$aColorList = array("blue", "red", "green", "orange", "purple");	//values to change the font color for each body group
		$colorListCount = -1;	//counter for color change array index
		$curGroup = "";	//used to compare against new body group for color change
		$aFilteredData = $dataWeightTraining->grabFilteredEntries($body_groups);	//to pull body part specific array 
		for($c = 0; $c < count($aFilteredData); $c++){
			if($curGroup != $aFilteredData[$c]->body_group){
				$curGroup = $aFilteredData[$c]->body_group;
				$colorListCount++;
			}
			echo "<li id='option".($c + 1)."'><div class='label'><span class='label_value'>".$aFilteredData[$c]->set_name."</span><div style='color:".$aColorList[$colorListCount]."' class='sublabel'>".$aFilteredData[$c]->body_group."&nbsp;&nbsp;-&nbsp;&nbsp;Last workout: ".$aFilteredData[$c]->getLastSetDone()."</div></div><div class='add_btn'><img src='/images/spacer.png' width='30' height='30' /></div></li>";
		}
	}
?>