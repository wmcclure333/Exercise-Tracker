<?php
/*
DataLogEntry holds all information about a single workout entry.  This is the default "catch-all" workout type.  Some workout types are controlled with their own class type, implemented as extensions of this one.
*/
class DataLogEntry{
	private $seed;
	public $type = "";
	public $minutes = 0;
	public $ahr = 0;
	public $daily_score = 0;
	public $timestamp = "";
	
	public function DataLogEntry($e){
		$this->seed = $e['seed'];
		$this->type = $e['ex_type'];
		$this->minutes = $e['ex_minutes'];
		$this->ahr = $e['ex_ahr'];
		$this->timestamp = $e['ex_timestamp'];
		$this->daily_score = $this->calcScore();
	}
	
	private function calcScore(){
		return(round((int)$this->minutes * (int)$this->ahr / 100));
	}
}

?>