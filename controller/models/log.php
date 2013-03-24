<?php

	//Logs events in the system db or filesystem
	function log_event($title,$description,$type,$importance,$data1=null,$data2=null,$data3=null,$data4=null,$data5=null){
		global $_SWDF,$db;
		
		//Check which handler to use
		if ($_SWDF['settings']['use_db_for_log'] && $_SWDF['settings']['use_db']){
			$db->insert($_SWDF['settings']['db']['tables']['log'],Array(
				"importance"=>$importance,
				"type"=>$type,
				"title"=>$title,
				"description"=>$description,
				"data_1"=>$data1,
				"data_2"=>$data2,
				"data_3"=>$data3,
				"data_4"=>$data4,
				"data_5"=>$data5,
			));
			return $db->lastInsertId();
		} else {
			//TODO: make log filesystem sytem
		}
		return false;
	}
	
	//returns a log
	function fetch_log_event($id){
		global $_SWDF,$db;
		return $db->select("*",$_SWDF['settings']['db']['tables']['log'],array("id"=>$id));
	}
	
	
	//Cleans the log
	function clean_log(){
		//TODO: make the log cleaning function
	}
?>
