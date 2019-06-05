<?php

class WeightTrainingSetType{
	public $set_id;
	public $set_name;
	public $body_group;
	public $classParent;
	
	public function WeightTrainingSetType($set, $parent){
		$this->set_id = $set['set_id'];
		$this->set_name = $set['set_name'];
		$this->body_group = $set['body_group'];
		$this->classParent = $parent;
	}
	
	public function getLastSetDone($type){   //get datestamp of last set of $type
		$aListOfWorkoutForThisSet = array();
		foreach ($this->classParent->a_set_type_data as $obj){	//pull out every set performed that matched this set type
			if($obj->set_name == $this->set_name) 
				array_push($aListOfWorkoutForThisSet, $obj);
		}
		$lastSetDone = 0;
		foreach ($aListOfWorkoutForThisSet as $set){	//go through each set performed and find the most recent time stamp
			if($set->time_stamp > $lastSetDone)
				$lastSetDone = $set->time_stamp;
		}
		if($lastSetDone == 0)
			return "None";
		else
			return date("m/d/y", strtotime($lastSetDone));
	}
	
	public function getTotalSetsDone($type){
		//get total sets done of $type	
	}
}

?>