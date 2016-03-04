<?php
	$_SWDF['settings']['on_auth_failure']="403";					//If a visitor doesn't have authorization to view a page redirect to:

	//Uploads
	$_SWDF['settings']['max_upload_size']=5*1024*1024;				//Max upload size in bytes
	$_SWDF['settings']['max_upload_size_gui']="5MB";				//A human readable max upload size
	$_SWDF['settings']['max_uploads_per_session']=400;				//The maximum number of uploads one user can perform in one sesssion
	$_SWDF['settings']['max_uploads_per_day']=25000;				//The maximum number of uploads the website will accept in one day
	
	
	//Login/Users
	$_SWDF['settings']['allow_login']=true;							//Whether to allow users to log in. Can be handy to disable when performing upgrades.
	$_SWDF['settings']['require_session_requests_before_login']=1;	//How many requests must have been made on a session before the user is allowed to login. (setting this to at least one helps mitagate brute force attacks. The higher you can set this the better).
	$_SWDF['settings']['allow_login_with_id']=true;					//Whether to allow users to log in with their user id (the number asigned tho their account when registering).
	$_SWDF['settings']['allow_login_with_username']=true;			//Whether to allow users to log in with their username. (You might disable this for a sites where username's are not used, but real-names are)
	$_SWDF['settings']['allow_login_with_email']=true;				//Whether to allow users to log in with their email address.
	
	//Sessions
	$_SWDF['settings']['delete_single_use_sessions_after']=3600*5;	//How long to wait (in seconds) before destroying sessions that only requested a single page (Search-bots create thousands of junk sessions because they don't send cookie data)

	/*
	 * For the following, the options are:
	 * 
	 * "state":
	 *		"allow"  -  The user can decide
	 *		"force"  -  The user has no choice, the option is forced true.
	 *		"deny"   -  The user has no choice. The option is forced false. (denied)
	 * 
	 * "default"	  -  True/False. If state=allow, specify the default state selected for the user.
	 */
	
	$_SWDF['settings']['concurrent_login_sessions']['state']="allow";		//Whether to allow users to be logged in from several different locations/sessions at once (have concurrent login sessions).
	$_SWDF['settings']['concurrent_login_sessions']['default']=false;		//If the user is allowed to choose, what should the default should be.
	
	$_SWDF['settings']['multiple_ip_login_sessions']['state']="allow";		//Whether to allow one login session to span multiple IP addresses. Normally a session is only active on one IP (apart from AOL dial up). Activity on multiple IPs can indicate a hijacked session. Disable for tighter security, but doing so may cause users to be wrongly kicked from their sessions.
	$_SWDF['settings']['multiple_ip_login_sessions']['default']=true;		//If the user is allowed to choose, what should the default should be.
	
	$_SWDF['settings']['multiple_browser_login_sessions']['state']="allow";	//Whether to allow one login session to span multiple browsers. This almost never happens in the real world (apart from with buggy browsers reporting the wrong browser version), and if detected normally indicates a hijacked session. Disable for tighter security, but doing so may cause users to be wrongly kicked from their sessions.
	$_SWDF['settings']['multiple_browser_login_sessions']['default']=false;	//If the user is allowed to choose, what should the default should be.
	
	
	
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//SWDF related Settings. You don't normally need to modify these
	$_SWDF['settings']['required_views']=Array("404","403");	//Views that any theme must have. Theme loading will fail if they are not present.	
	$_SWDF['settings']['dont_save_login_path_on']=				//Views which a user shouldn't be redirected to after login
		Array("login","_process_login","logout","register","register2");
	$_SWDF['settings']['allow_direct_resource_linking_for'] =	//Used by SWDF_make_resource_link() to determine whether to pass through php script or link directly to file
		Array("ico","pdf","flv","jpg","jpeg","png","gif","swf","css","js");		
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
