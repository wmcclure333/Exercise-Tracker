<?php
/* DataLogWeightTraining Class
This is a data class that holds all weight training data and allows filtering and retrieving of that data.  It holds all Body Group types, Set (workout) types and data from each logged set.
*/
require(dirname(__FILE__).'/weightTrainingSetType.php');
require(dirname(__FILE__).'/weightTrainingSetData.php');

class DataLogWeightTraining{
	public $num_entries = 0;
	public $a_body_groups = array();
	public $a_set_types = array();
	public $a_set_type_data = array();
	
	public function DataLogWeightTraining(){
		//Grab and calculate all important weight training data | store local vars
		$this->grabDataEntries();
	}
	private function grabDataEntries(){
		//Grab all body group names and store in object array
		$sql = "SELECT * FROM zz_Exercise_Weight_Body_Groups ORDER BY body_name ASC";
		$result = mysql_query($sql);
		$count_body = 0;
		while($row = mysql_fetch_array($result)){
			$this->a_body_groups[$count_body] = $row['body_name'];
			$count_body++;
		}
		mysql_close($sql);
		//Grab all set types (exercises), create objects and store in object array
		$sql = "SELECT * FROM zz_Exercise_Weight_Sets";
		$result = mysql_query($sql);
		$count_sets = 0;
		while($row = mysql_fetch_array($result)){
			$this->a_set_types[$count_sets] = new WeightTrainingSetType($row);
			$count_sets++;
		}
		mysql_close($sql);
		//Grab all set logs (actual rep data), create objects and store in object array
		$sql = "SELECT * FROM zz_Exercise_Weight_Sets_Log";
		$result = mysql_query($sql);
		$count_set_data = 0;
		while($row = mysql_fetch_array($result)){
			$this->a_set_type_data[$count_set_data] = new WeightTrainingSetData($row);
			$count_set_data++;
		}
		mysql_close($sql);
	}
	
	public function grabFilteredEntries($aFilters){
		//Grab all set logs (actual rep data), within filtered array
		$aEntries = array();
		$filterString = "";
		foreach ($aFilters as $value) {
			$filterString .= "'".$value."',";
		}		
		$filterString = substr($filterString, 0, strlen($filterString) - 1);
		$sql = "SELECT * FROM zz_Exercise_Weight_Sets WHERE body_group IN (".$filterString.") ORDER BY body_group ASC";
		$result = mysql_query($sql);
		$count_sets = 0;
		while($row = mysql_fetch_array($result)){
			$aEntries[$count_sets] = new WeightTrainingSetType($row, $this);
			$count_sets++;
		}
		mysql_close($sql);
		return $aEntries;
	}	
	
	
	public function grabHistoricDataFromBodyPartList(){	//Runs through class-level array of Body Parts and grabs most recent time stamp for each and returns an array of those time stamps.

		$aFinalData = array();
		foreach ($this->a_body_groups as $thisBodyGroup){
			//Grab all Set names that fall under the current Body Part and store them in a local array
			$aBpSets = array();
			$sql = "SELECT * FROM zz_Exercise_Weight_Sets WHERE body_group = '".$thisBodyGroup."'";
			$result = mysql_query($sql);
			$count_sets = 0;
			while($row = mysql_fetch_array($result)){
				$aBpSets[$count_sets] = $row['set_name'];
				$count_sets++;
			}
			mysql_close($sql);
			
			//Get historical data of the current Body Part's sets and parse out all timestamps, then find the most recent timestamp in that group and add it to the final array.  
			$aThisBpHistoricalData = $this->grabHistoricDataFromSetList($aBpSets);
			$aThisBpTimestamps = array();
			$numberOfFieldsPassedBackPerSet = 21;  //this is how many data fields will be passed back per set.  This helps seperate multiple set types.
			for($c = 0; $c < count($aThisBpHistoricalData); $c += $numberOfFieldsPassedBackPerSet){
				array_push($aThisBpTimestamps, $aThisBpHistoricalData[$c]);
			}
			$thisMostRecentTimestamp = "190001011200";
			for($d = 0; $d < count($aThisBpTimestamps); $d++){
				if($aThisBpTimestamps[$d] > $thisMostRecentTimestamp)
					$thisMostRecentTimestamp = $aThisBpTimestamps[$d];
			}
			if($thisMostRecentTimestamp == "190001011200")	//if there's no data for this Body Group set to N/A, else format timestamp
				$thisMostRecentTimestamp = "Not Exercised Yet";
			else
				$thisMostRecentTimestamp = substr($thisMostRecentTimestamp, 4, 2)."/".substr($thisMostRecentTimestamp, 6, 2)."/".substr($thisMostRecentTimestamp, 2, 2);	//format stamp as MM/DD/YYYY
			array_push($aFinalData, $thisMostRecentTimestamp);
		}	//END foreach
		return $aFinalData;	
	} //END grabHistoricDataFromBodyPartList()
	
	public function grabHistoricDataFromSetList($setList){	//Takes in an array of Set types (workout moves) and returns a complex array of data for each set in a specific order that the function call will then parse
	
		//Grab last rep data (1-4) and store in local array
		$aMostRecentRecs = array();
		$aBestRecs = array();
		$aSetNotes = array();
		foreach ($setList as $set_value){	//iterate local Set list 
			$tempSetRecord = array();	
			foreach ($this->a_set_type_data as $obj){	//iterate class-level array of Set (workout) data logs.  Grab all data logs where the Set type matches the current Set type in the local Set list 
				if($obj->set_name == $set_value)
					array_push($tempSetRecord, $obj);
			}
			
			//Code block below finds the most recent data for the current set type 
			$mostRecent = $tempSetRecord[0];	//temp var to find most recent record below
			foreach($tempSetRecord as $thisRec){	//iterate temporary array of records to find the most recent
				if($mostRecent->time_stamp < $thisRec->time_stamp)
					$mostRecent = $thisRec;
			}
			array_push($aMostRecentRecs, $mostRecent);	//Store the most recent in a local array
			
			//Grab notes from the most recent exercise sets and place in array
			array_push($aSetNotes, $mostRecent->set1_note);
			array_push($aSetNotes, $mostRecent->set2_note);
			array_push($aSetNotes, $mostRecent->set3_note);
			array_push($aSetNotes, $mostRecent->set4_note);			

			//Code block below finds the all-time best data for the current set type (Highest weight/rep combo)
			
			//temp var $bestSet is initialized to find the best record below
			$bestSet = new stdClass();
			$bestSet->best_set1_w = $tempSetRecord[0]->set1_w;	
			$bestSet->best_set1_r = $tempSetRecord[0]->set1_r;	
			$bestSet->best_set2_w = $tempSetRecord[0]->set2_w;	
			$bestSet->best_set2_r = $tempSetRecord[0]->set2_r;	
			$bestSet->best_set3_w = $tempSetRecord[0]->set3_w;	
			$bestSet->best_set3_r = $tempSetRecord[0]->set3_r;	
			$bestSet->best_set4_w = $tempSetRecord[0]->set4_w;	
			$bestSet->best_set4_r = $tempSetRecord[0]->set4_r;	
			
			foreach($tempSetRecord as $thisRec){	//iterate temporary array of records to find the best
				//Find best Set #1
				if($bestSet->best_set1_w < $thisRec->set1_w){	//if weight amount is higher...
					$bestSet->best_set1_w = $thisRec->set1_w;
					$bestSet->best_set1_r = $thisRec->set1_r;
				}else if(($bestSet->best_set1_w == $thisRec->set1_w) && ($bestSet->best_set1_r < $thisRec->set1_r)){	//if weight amount is equal but reps are higher...
					$bestSet->best_set1_w = $thisRec->set1_w;
					$bestSet->best_set1_r = $thisRec->set1_r;
				}
				//Find best Set #2
				if($bestSet->best_set2_w < $thisRec->set2_w){	
					$bestSet->best_set2_w = $thisRec->set2_w;
					$bestSet->best_set2_r = $thisRec->set2_r;
				}else if(($bestSet->best_set2_w == $thisRec->set2_w) && ($bestSet->best_set2_r < $thisRec->set2_r)){	
					$bestSet->best_set2_w = $thisRec->set2_w;
					$bestSet->best_set2_r = $thisRec->set2_r;
				}
				//Find best Set #3
				if($bestSet->best_set3_w < $thisRec->set3_w){	
					$bestSet->best_set3_w = $thisRec->set3_w;
					$bestSet->best_set3_r = $thisRec->set3_r;
				}else if(($bestSet->best_set3_w == $thisRec->set3_w) && ($bestSet->best_set3_r < $thisRec->set3_r)){	
					$bestSet->best_set3_w = $thisRec->set3_w;
					$bestSet->best_set3_r = $thisRec->set3_r;
				}
				//Find best Set#4
				if($bestSet->best_set4_w < $thisRec->set4_w){	
					$bestSet->best_set4_w = $thisRec->set4_w;
					$bestSet->best_set4_r = $thisRec->set4_r;
				}else if(($bestSet->best_set4_w == $thisRec->set4_w) && ($bestSet->best_set4_r < $thisRec->set4_r)){	
					$bestSet->best_set4_w = $thisRec->set4_w;
					$bestSet->best_set4_r = $thisRec->set4_r;
				}
			}
			array_push($aBestRecs, $bestSet);	//Store the most recent in a local array
		}

		//grab record rep data (1-4) and store in temp array  
			//(v3 -to be added) 
		
		//Splice local arrays together in a specific order and return to function call
		$aFinalData = array();
		for($c = 0; $c < count($setList); $c++){
			//add date and set info to array
			array_push($aFinalData, $aMostRecentRecs[$c]->time_stamp); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set1_w); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set1_r); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set2_w); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set2_r); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set3_w); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set3_r); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set4_w); 
			array_push($aFinalData, $aMostRecentRecs[$c]->set4_r); 
			array_push($aFinalData, $aBestRecs[$c]->best_set1_w); 
			array_push($aFinalData, $aBestRecs[$c]->best_set1_r); 
			array_push($aFinalData, $aBestRecs[$c]->best_set2_w); 
			array_push($aFinalData, $aBestRecs[$c]->best_set2_r); 
			array_push($aFinalData, $aBestRecs[$c]->best_set3_w); 
			array_push($aFinalData, $aBestRecs[$c]->best_set3_r); 
			array_push($aFinalData, $aBestRecs[$c]->best_set4_w); 
			array_push($aFinalData, $aBestRecs[$c]->best_set4_r);
			array_push($aFinalData, $aSetNotes[0]);
			array_push($aFinalData, $aSetNotes[1]);
			array_push($aFinalData, $aSetNotes[2]);
			array_push($aFinalData, $aSetNotes[3]);
		}
		
		return $aFinalData;
	} //END grabHistoricDataFromSetList()
	
		
	public function grabBodyTypeFromSetList($setList){  //Takes in an array of Set types (workout moves) and returns an array of ordered Body Part types corresponding to the Set list order
	
		//Grab body parts and put them in a temporary array, then create an SQL-friendly string
		$aBodyParts = array();
		$filterString = "";
		foreach ($setList as $value) {
			$filterString .= "'".$value."',";
		}		
		$filterString = substr($filterString, 0, strlen($filterString) - 1);
		
		//Grab data with SQL and store Body Part data in a local array
		$sql = "SELECT * FROM zz_Exercise_Weight_Sets WHERE set_name IN (".$filterString.") ORDER BY FIELD(set_name, ".$filterString.")";
		$result = mysql_query($sql);
		$count_bp = 0;
		while($row = mysql_fetch_array($result)){
			$aBodyParts[$count_bp] = $row['body_group'];
			$count_bp++;
		}
		mysql_close($sql);
		
		return $aBodyParts;
	} //END grabBodyTypeFromSetList()

}

?>