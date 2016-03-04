<?php

	//Logs events in the system db or filesystem
	function log_event($type,$title,$description=null,$importance=0,$data1=null,$data2=null,$data3=null,$data4=null,$data5=null){
		global $_SWDF,$db;
		
		//Check whether to bother logging these events
		if ($importance<$_SWDF['settings']['log_only_from_importance']){
			return true;
		}
		
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
				"ip"=>\JamesSwift\SWDF\get_ip(),
				"browser"=>\JamesSwift\SWDF\get_browser(),
				"psid"=>$_SESSION['_SWDF']['info']['persistant_session_id'],
				"uid"=>$_SESSION['_SWDF']['info']['user_id']
			));
			return $db->lastInsertId();
		} else {
			//TODO: make log filesystem sytem
		}
		return false;
	}
	
	//returns a log
	function fetch_log_event($id, $decode_JSON=false){
		global $_SWDF,$db;
		
		//Check which handler to use
		if ($_SWDF['settings']['use_db_for_log'] && $_SWDF['settings']['use_db']){
			
			$log = $db->select("*",$_SWDF['settings']['db']['tables']['log'],array("id"=>$id));
			if ($decode_JSON===true){
				$log['data_1']=json_decode($log['data_1'], true);
				$log['data_2']=json_decode($log['data_2'], true);
				$log['data_3']=json_decode($log['data_3'], true);
				$log['data_4']=json_decode($log['data_4'], true);
				$log['data_5']=json_decode($log['data_5'], true);
			}
			return $log;
		} else {
			//TODO: make log filesystem sytem
		}
		return false;
	}
	
	
	//Cleans the log according to settings
	function clean_log(){
		global $_SWDF;
		
		//Check which handler to use
		if ($_SWDF['settings']['use_db_for_log'] && $_SWDF['settings']['use_db']){
			try {
				$stat=$this->prepare("DELETE FROM `".whitelist($_SWDF['settings']['db']['tables']['log'], "id")."` WHERE importance < ? AND timestamp < ?");
				$stat->execute([$_SWDF['settings']['log_unimportant_level'],time()-$_SWDF['settings']['log_unimportant_expiry']]);
				return true;
				
				$stat=$this->prepare("DELETE FROM `".whitelist($_SWDF['settings']['db']['tables']['log'], "id")."`AND timestamp < ?");
				$stat->execute([time()-$_SWDF['settings']['log_expiry']]);
				return true;
				
			} catch (\PDOException $e){
				trigger_error("Error cleaning event log.",E_USER_ERROR);
			}
			
		} else {
			//TODO: make log filesystem sytem
		}
		return false;
	}
