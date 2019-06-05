<?php
/*
DataLogEntryWeightTraining is an extension of the generic DataLogEntry class type.  This class holds all information about a single 'Weight Training' workout entry.
*/

class DataLogEntryWeightTraining extends DataLogEntry{
	public $bonus = "";
	
	public function DataLogEntryWeightTraining($e){
		parent::DataLogEntry($e);	//call base class constructor
		$this->bonus = $e['ex_bonus_pts'];
		$this->daily_score = $this->calcScore();
	}
	private function calcScore(){
		return(round((int)$this->minutes * (int)$this->ahr / 100) + (int)$this->bonus);
	}

}

?>