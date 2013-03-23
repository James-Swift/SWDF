<?php

//remember url to return to after login
	function SWDF_save_post_login_redirect_request($overwite=false){
		global $_SWDF;
		if ( !in_array($_SWDF['info']['requested_view'],$_SWDF['settings']['dont_save_login_path_on']) && !in_array($_SWDF['info']['requested_view'],$_SWDF['info']['controller_views'])){
			if (!isset($_SESSION['_SWDF']['settings']['after_login_redirect_to']) || $_SESSION['_SWDF']['settings']['after_login_redirect_to']=="" || $overwite==true){
				$_SESSION['_SWDF']['settings']['after_login_redirect_to']=$_SERVER['REQUEST_URI'];
				return true;
			}
		} 
		return false;
	}



	//return specific or all info about a particular user
	function SWDF_user_info($id,$info="*"){
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
	
	function SWDF_logout(){
		global $_SWDF;
		unset($_SESSION['_SWDF']['info']['user_id']);
		unset($_SWDF['info']['user']);
		
		//TODO: make logout function
		
	}

?>