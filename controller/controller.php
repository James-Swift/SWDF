<?php
	if (!isset($_SWDF)){
		exit;	
	}
	
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// Controller Init

	//Include models required for controller operation (functions, classes etc.)
	require("models/database.php");
	require("models/security.php");	
	require("models/ip.php");
	require("models/log.php");	
	require("models/sanitization.php");
	require("models/strings.php");
	require("models/users.php");
	require("models/views.php");
	require("models/files.php");

	//Load applicable settings
	require("settings/_settings.php");	
	
	//Store current view for reference throughout SWDF
	if (isset($_POST[$_SWDF['settings']['vvn']])){
		$_SWDF['info']['current_view']=whitelist($_POST[$_SWDF['settings']['vvn']], "id");
	} else if (isset($_GET[$_SWDF['settings']['vvn']])){
		$_SWDF['info']['current_view']=whitelist($_GET[$_SWDF['settings']['vvn']], "id");
	} else {
		$_SWDF['info']['current_view']="";
	}
	
	//Store requested view for reference (in case a different view is later forced, e.g. 404, login etc.)
	$_SWDF['info']['requested_view']=$_SWDF['info']['current_view'];
	
	//Open Main $db Database connection
	if ($_SWDF['settings']['use_db']===true){
		try {
			$db=new SWDF_DB(
				$_SWDF['settings']['db']['driver'].":dbname=" . $_SWDF['settings']['db']['database'] . ";host=" . $_SWDF['settings']['db']['host'],
				$_SWDF['settings']['db']['username'],
				$_SWDF['settings']['db']['password']		
			);
		} catch (PDOException $e) {
			trigger_error("Couldn't establish main database connection.",E_USER_ERROR);
		}
		//Unset the variable storing the DB password
		if ($_SWDF['settings']['unset_db_password_after_use']===true){
			unset($_SWDF['settings']['db']['password']);
		}
	}

	
	
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// Session
	
	//Load the custom session storage handler if requested
	if ($_SWDF['settings']['use_db_for_session']===true && $_SWDF['settings']['use_db']===true){
		new SWDF_session_db_handler($db);
	}
	
	//Prevent PHP from forcing specific session-related cache-handling on us. We'll sort that ourselves later.
	session_cache_limiter("");
	
	//Start the session
	session_start();
	
	//Try to determine if user is able to send/receive cookies and therefore use the session mechanism (result is: boolean $_SWDF['info']['cookies_enabled'] )
	SWDF_check_cookies();
	
	//Check the security of $_SESSION for both registered and anonymous users. Note: This may regenerate/kick/wipe the current session if it thinks a hijacking attempt is being attempted.
	SWDF_check_session_security();
	
	//Check to see if a user is logged in (sets $_SWDF['info']['user_logged_in']) and validate thier user session. Also loads user data to $_SWDF['info']['user'].
	SWDF_validate_user_session();

	//Setup default $_SESSION state, if this is the user's first visit.
	if (!isset($_SESSION['_SWDF']['language'])){
		$_SESSION['_SWDF']['settings']['language']=$_SWDF['settings']['default_language'];
	}	   

	
	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// Themes/Views <-Determine what view (page) to display
	
	if ($_SWDF['settings']['use_theme']!=NULL ){
		//Load theme and associated views
		SWDF_load_theme($_SWDF['settings']['use_theme']);
		
		//If the user hasn't logged in yet, save the current request (except in a few circumstances) so they can be redirect to it after login.
		if ($_SWDF['info']['user_logged_in']===false){
			SWDF_save_post_login_redirect_request(true);
		}

		//Select the view to be used and update $_SWDF
		SWDF_load_view();
		
		//Count the number of loaded views this session
		$_SESSION['_SWDF']['info']['requests_this_session']+=1;
		
	} else {
		trigger_error("No theme specified.",E_USER_ERROR);
	}

	//////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////
	// Caching
	
	//Compress output
	if ($_SWDF['settings']['compress_output']===true){
		ini_set("zlib.output_compression", "On");
	}
	
	//Find/load cached files
	if ($_SWDF['settings']['enable_view_caching']===true && in_array($_SWDF['view']['cache'],array("loose","exact"))===true){
		
		//by default, always regenerate (i.e. run the code, don't load an old cached version)
		$_SWDF['settings']['regenerate_cache']=true;

		//Find how old a cache copy of this view can be before it must be regenerated (in seconds)
		$cache_expiry=$_SWDF['settings']['default_cache_expiry'];
		if ($_SWDF['view']['cache_expiry']>0){
			$cache_expiry=$_SWDF['view']['cache_expiry'];
		} 

		//Find at what level/depth caching for this view begins ("template" or "view"). If "template", then the cached file includes the page template. If "view", it only includes the content to be loaded into the template.  
		$_SWDF['settings']['cache_level']=$_SWDF['settings']['default_cache_level'];
		if ($_SWDF['view']['cache_level']!=false){
			$_SWDF['settings']['cache_level']=$_SWDF['view']['cache_level'];
		}

		//Work the path and name of the desired cache file and store it in $_SWDF['info']['cache_file']. (At this point the file might not exist/have been created yet.)
		if ($_SWDF['view']['cache']==="loose"){
			//If in "loose" mode, the same file will be used regardless of what variables are sent to the page. (For example, a "contact us" page will always look the same so ca be loosely cached).
			$_SWDF['info']['cache_file']=$_SWDF['paths']['views_cache'].$_SWDF['info']['current_view']."[,".$_SWDF['settings']['cache_level']."].cache";
		} else {
			//If in "exact" mode, a different cached version can exist when any combination of $_SWDF['view']['cache_variables'] are sent to the view.
			
			//Work out what the file name should be
			$var_string="";
			if (isset($_SWDF['view']['cache_variables']) && is_array($_SWDF['view']['cache_variables'])){
				foreach($_SWDF['view']['cache_variables'] as $var){
					$val=($_POST[$var]!=NULL ? $_POST[$var] : $_GET[$var]);
					$var_string.="&".$var."=".$val;
				}
			}
			$_SWDF['info']['cache_file']=$_SWDF['paths']['views_cache'].$_SWDF['info']['current_view']."[".md5($var_string).",".$_SWDF['settings']['cache_level']."].cache";
		}

		//Send header to request browser caching to match server-side caching
		header('Expires: '.gmdate('D, d M Y H:i:s', time()+$cache_expiry).'GMT'); 

		//Check if the cache file exists and is usable (not older than the expiry date)
		if (is_file($_SWDF['info']['cache_file']) && filemtime($_SWDF['info']['cache_file'])>time()-$cache_expiry){
			//The file exists and is usable.
			
			//So, don't regenerate cache
			$_SWDF['settings']['regenerate_cache']=false;

			//Point "body_includes" to the cached file. This has no effect in "template" cache level, but in "view" level it does the subtituting of the script for the cached version for us.
			$_SWDF['template_data']['body_includes']=Array($_SWDF['info']['cache_file']);

		} else {
			//The Cache file needs regenerating/creating.

			//If we're operating at "view" cache level, set the body include to be the special cache passthrough file (which still loads the normal files, but starts and stops the output buffer at the right place before and efter them).
			if ($_SWDF['settings']['cache_level']==="view"){
				//Store the body_includes
				$_SWDF['info']['cache_passthrough_files']=$_SWDF['template_data']['body_includes'];
				$_SWDF['template_data']['body_includes']=Array($_SWDF['paths']['root']."controller/views/cache_passthrough.php");
			}
			
			//If we're operating at the "template" cache level, the starting/stoping of the output buffer is handled by the main index.php file. 
			//We've told it the name and location to store the created cache file in for future use, so basically nothing left to do here.
			
			//Clean out old cached files:
			SWDF_clean_view_cache();
		}

	
	} else {
		//If caching disabled for this view, set cache expiry for sometime in the past
		header('Expires: '.gmdate('D, d M Y H:i:s', 0).'GMT');
	}
	
	//////////////////////////////////////////////////////////////
	//Unset custom variables and mark completed
	unset($version,$themes_dir,$views_dir,$cache_expiry,$dir,$match,$len,$files,$file_limit,$size,$to_unlink);
	$_SWDF['info']['controller_completed']=true;
	
	//Handing back to the mian index.php file.... 
?>