<?php

	namespace JamesSwift\SWDF;

	//remember url to return to after login
	function save_post_login_redirect_request(){
		global $_SWDF;
		if ( !in_array($_SWDF['info']['requested_view'],$_SWDF['settings']['dont_save_login_path_on']) && !in_array($_SWDF['info']['requested_view'],$_SWDF['info']['controller_views'])){
			
			$_SESSION['_SWDF']['settings']['after_login_redirect_to']=$_SERVER['REQUEST_URI'];
			return true;
		
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
				unset($_SWDF['info']['user']['password']);
				
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
	
	function log_failed_login_and_die($username=null, $message=null){

	    global $_SWDF;
	
	    //Log attempt
		log_event("failed-login","Failed login attempt",null,7,$message,$username);
	
		//Redirect to login page
		header('Location: '.make_link($_SWDF['settings']['on_auth_failure'], ["error"=>"bad_login"], true));
		
		//Die
		die();
	}
	
	function lookup_auth_error_message($id,$type="user"){
		$errors=[
			"login_disabled"=>[
				"Sorry, we couldn't attempt to log you in as the website login system has been disabled by the administrator.",
				"User log-in failed because the login system has been dsiabled in the SWDF settings."
			],
			"insufficient_session_requests_before_login"=>[
				"Sorry, your login attempt failed cookies are required to log in. Please ensure your browser is set to allow cookies, then try again.",
				"User log-in failed because they hadn't visited a log-in page first. Either their browser has disabled cookies or someone is trying to brute force the login system."
			],
			"remote_logout"=>[
				"Sorry, this user session has been logged out remotely. This could be because this account has been logged in somwhere else recently and the system only allows one active log in at a time, or because you chose to log out of this session.",
				"The user's session has been logged out remotely. This could be because the account has been logged in somwhere else recently and the system only allows one active log in at a time, or because they chose to log out of this session."
			],
			"wrong_browser"=>[
				"Sorry, your user session has been cleared because your browser version suddenly changed. This occasionally happens due to flash or proxy settings, but it can be a sign that someone is trying to remotely take over this session. As a precaution, your session has been closed. This means that any temporary data you have entered may have been lost (such as items in a shopping cart, or cookie preferences). Data associated with a user account however will not have been removed.",
				"The user's session has been cleared because their browser version suddenly changed. This occasionally happens due to flash or proxy settings, but it can be a sign that someone is trying to remotely take over their session. As a precaution, their session data has been deleted. This means that any temporary data they entered may have been lost (such as items in a shopping cart, or cookie preferences). Data associated with their user account however will not have been removed."
			]
		];
		if ($type==="user"){
			return $errors[$id][0];
		} else {
			return $errors[$id][1];
		}
		
	}
