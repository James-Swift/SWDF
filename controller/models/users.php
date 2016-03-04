<?php

	namespace JamesSwift\SWDF;

	//remember url to return to after login
	function save_post_login_redirect_request($overwite=false){
		global $_SWDF;
		if ( !in_array($_SWDF['info']['requested_view'],$_SWDF['settings']['dont_save_login_path_on']) && !in_array($_SWDF['info']['requested_view'],$_SWDF['info']['controller_views'])){
			if (!isset($_SESSION['_SWDF']['settings']['after_login_redirect_to']) || $_SESSION['_SWDF']['settings']['after_login_redirect_to']=="" || $overwite==true){
				$_SESSION['_SWDF']['settings']['after_login_redirect_to']=$_SERVER['REQUEST_URI'];
				return true;
			}
		} 
		return false;
	}

	
	function validate_user_session(){
		global $_SWDF;
		//Assume the user isn't logged in
		$_SWDF['info']['user_logged_in']=false;
		
		//Determine if the user thinks they are logged in.
		if ($_SWDF['settings']['use_db']===true){
			if (isset($_SESSION['_SWDF']['info']['user_id']) && $_SESSION['_SWDF']['info']['user_id']!=""){
				//For now, assume user is logged in while we load and validate their session
				
				//Load user data into info array for handy reference
				$_SWDF['info']['user']=\JamesSwift\SWDF\user_info($_SESSION['_SWDF']['info']['user_id']);
				if ($_SWDF['info']['user']==false){
					trigger_error("Unable to load user settings from Database.",E_USER_ERROR);
				}
				
				//Remove sensitive data to avoid accidental security breach
				unset($_SWDF['info']['user']['password_id'],$_SWDF['info']['user']['password_username'],$_SWDF['info']['user']['password_email']);
				
				//Convert JSON data into php associative array
				$_SWDF['info']['user']['valid_sessions']=json_decode($_SWDF['info']['user']['valid_sessions'], true);
				
				//Check this session hasn't been logged out
				if (in_array($_SESSION['_SWDF']['info']['persistant_session_id'], $_SWDF['info']['user']['valid_sessions'])!==true){
					//This session has been logged out. Tell the user.
					\JamesSwift\SWDF\logout();
					header( "Location: " . make_link($_SWDF['settings']['on_auth_failure'], array("reason"=>"remote_logout"), true)); exit;
				} else {
					//Check this user hasn't been banned
					if ($_SWDF['info']['user']['banned_until']>date("Y-m-d H:i:s")){
						//This session has been logged out. Tell the user.
						\JamesSwift\SWDF\logout();
						header( "Location: " . make_link($_SWDF['settings']['on_auth_failure'], array("reason"=>"banned"), true)); exit;
					} else {
						//Everything checks out. They are logged in.
						$_SWDF['info']['user_logged_in']=true;
					}
				}
			} else {
				$_SWDF['info']['user_logged_in']=false;
			}

		} else {
				$_SWDF['info']['user_logged_in']=false;
		}
	}


	//return specific or all info about a particular user
	function user_info($id,$info="*"){
		global $_SWDF,$db;
		$id=strtolower($id);
		//TODO: Make sure when registering a username can't just be a number (otherwise it could be wrongly identified below)
		//Try to match the user by id, username, email or name
		if ($array=$db->select1($info,$_SWDF['settings']['db']['tables']['users'],Array("LOWER(id)"=>$id)) ){
			if (sizeof($array)>0){
				return $array;
			}
		} 
		if ($array=$db->select1($info,$_SWDF['settings']['db']['tables']['users'],Array("LOWER(username)"=>$id)) ){
			if (sizeof($array)>0){
				return $array;
			}
		} 		
		if ($array=$db->select1($info,$_SWDF['settings']['db']['tables']['users'],Array("LOWER(email)"=>$id)) ){
			if (sizeof($array)>0){
				return $array;
			}
		}
		return false;
	}
	
	function logout(){
		global $_SWDF;
		unset($_SESSION['_SWDF']['info']['user_id']);
		unset($_SWDF['info']['user']);
		$_SWDF['info']['user_logged_in']=false;
		
		//TODO: make logout function
		
	}
