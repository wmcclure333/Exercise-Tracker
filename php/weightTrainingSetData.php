<?php

class WeightTrainingSetData{
	public $log_id;
	public $set_id;
	public $set_name;
	public $time_stamp;
	public $set1_w;
	public $set1_r;
	public $set1_note;
	public $set2_w;
	public $set2_r;
	public $set2_note;
	public $set3_w;
	public $set3_r;
	public $set3_note;
	public $set4_w;
	public $set4_r;
	public $set4_note;
	
	public function WeightTrainingSetData($set){
		$this->log_id = $set['log_id'];
		$this->set_id = $set['set_id'];
		$this->set_name = $set['set_name'];
		$this->time_stamp = $set['time_stamp'];
		$this->set1_w = $set['set1_w'];
		$this->set1_r = $set['set1_r'];
		$this->set1_note = $set['set1_note'];
		$this->set2_w = $set['set2_w'];
		$this->set2_r = $set['set2_r'];
		$this->set2_note = $set['set2_note'];
		$this->set3_w = $set['set3_w'];
		$this->set3_r = $set['set3_r'];
		$this->set3_note = $set['set3_note'];
		$this->set4_w = $set['set4_w'];
		$this->set4_r = $set['set4_r'];
		$this->set4_note = $set['set4_note'];
	}
}

?>