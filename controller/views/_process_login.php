<?php
//TODO: Convert the follwing into a function (API) which can be called

//Suppress errors that could prevent a redirect
error_reporting( E_ERROR );

//Check dependancies are in place
if ( !isset( $_SWDF ) ) {
	exit;
}

//Check if people are allowed to log in
if ( $_SWDF['settings']['allow_login'] === false ) {
	header( "Location: " . make_link($_SWDF['settings']['on_auth_failure'], array("reason"=>"login_disabled"), true)); exit;
}

//Make sure enough requests have been completed on this session to allow login
if ($_SESSION['_SWDF']['info']['requests_this_session']<$_SWDF['settings']['require_session_requests_before_login']){
	header( "Location: " . make_link($_SWDF['settings']['on_auth_failure'], array("reason"=>"insufficient_session_requests_before_login"), true)); exit;
}

////////////////////////////////////////////////////////////////
//Sanitize variables
$failure_reason="";
$username = whitelist( $_POST['username'], "email" );
$hash = whitelist( $_POST['hash'], "id" );
$password = whitelist( $_POST['password'], "id" );
$token = whitelist( $_POST['token'], "id" );

if ( ($_POST['limit_to_ip'] === "on" && $_SWDF['settings']['multiple_ip_login_sessions']['state'] == "allow") || $_SWDF['settings']['multiple_ip_login_sessions']['state'] == "force" ) {
	$limit_to_ip = "true";
} else {
	$limit_to_ip = "false";
}

if ( ($_POST['limit_to_browser'] === "on" && $_SWDF['settings']['multiple_browser_login_sessions']['state'] == "allow") || $_SWDF['settings']['multiple_browser_login_sessions']['state'] == "force" ) {
	$limit_to_browser = "true";
} else {
	$limit_to_browser = "false";
}

if ( ($_POST['limit_to_one_session'] === "on" && $_SWDF['settings']['concurrent_login_sessions']['state'] == "allow") || $_SWDF['settings']['concurrent_login_sessions']['state'] == "force" ) {
	$limit_to_one_session = "true";
} else {
	$limit_to_one_session = "false";
}

////////////////////////////////////////////////////////////////


//Validate token (and delete)
$token_is_valid = SWDF_validate_token( "login", $token );

if ( $token_is_valid === false ) {
	//Fail on bad token
	$failure_reason="token";
} else {
	
	
	//Fetch user row from DB
	$user_info = \JamesSwift\SWDF\user_info($username);
	
	//Check user is recorgnized
	if ($user_info==""){
		//Fail on bad username
		$failure_reason="user";
	} else {

		//Validate password by regenerating the sent hash
		// Notes: The only info the user typed which we don't have in plain text is their password. That was encrypted into a hash along with all the other
		// data the user entered client-side. To check they entered the correct password (and thus validate thier login request), we need to recreate the
		// hash using all the data they supplied in plain text, plus the correct password (which we know have previously stored in the server-side db), then 
		// compare our hash to the one they sent. If the two match, they entered the right password.
		//
		// However, we don't know whether they used their user_id, email or username to log-in. So we need to generate a hash for each scenario and check all
		// three. Also, as the password is salted (combined then hashed) with the username they entered before itself being hashed, we need to store three versions
		// of their password server-side. One where it is salted with their user_id, one with their username, and one with their password. This doesn't do much to
		// improve security of the password during transmition (it's already pretty uncrackable), but it does mean that if ever a hacker accessed our store of user
		// data, it will be much harder for them to brute-force a user's password back to plain-text to then use on other services where the user has used the same
		// password for more than one website. Using this process adds a little complexity on our end, but is worth the effort to improve each user's security should
		// our service ever suffer a breach.
		
		//Make last part of hash string for use later
		$to_hash= $token;
		$to_hash.= $limit_to_one_session;
		$to_hash.= $limit_to_ip;
		$to_hash.= $limit_to_browser;

		//When a user is logging in with the plain-text form
		// compare password directly with the db, then
		// generate the hash we should have been sent
		if ($hash==="" && $password!==""){
			if ( hash("sha256", hash("sha256", $password).hash("sha256", strtolower($user_info['id']))) === $user_info['password_id'] ){
				$hash=hash( "sha256", strtolower($user_info['id']).strtolower($user_info['password_id']).$to_hash );
			}
		}
		
		//For each allowed user identifier, hash it with the above string then add to array of allowed hashes.
		if ($_SWDF['settings']['allow_login_with_id']===true){
			$allowed_hashes[]=hash( "sha256", strtolower($user_info['id'].$user_info['password_id'].$to_hash) );
		}
		if ($_SWDF['settings']['allow_login_with_username']===true){
			$allowed_hashes[]=hash( "sha256", strtolower($user_info['username'].$user_info['password_username'].$to_hash) );
		}
		if ($_SWDF['settings']['allow_login_with_email']===true){
			$allowed_hashes[]=hash( "sha256", strtolower($user_info['email'].$user_info['password_email'].$to_hash) );
		}

		//Compare hashes
		if ( !in_array($hash,$allowed_hashes)===true) {
			$failure_reason="credentials";
		} else {
			//Check it User is banned.
			if ($user_info['banned_until']>date("Y-m-d H:i:s")){
				$failure_reason="banned";
			} else{
				//Store session security setings
				$_SESSION['_SWDF']['info']['limit_to_ip']=false;
				if ($limit_to_ip==="true"){
					$_SESSION['_SWDF']['info']['limit_to_ip']=\JamesSwift\SWDF\get_ip();
				}
				
				$_SESSION['_SWDF']['info']['limit_to_browser']=$limit_to_ip;
				
				//Add to or reset the valid_sessions variable in the user db row
				if ($limit_to_one_session){
					//Log out all other sessions
					$valid_sessions=array();
					$valid_sessions[]=$_SESSION['_SWDF']['info']['persistant_session_id'];

					$db->update($_SWDF['settings']['db']['tables']['users'], array("valid_sessions"=>json_encode($valid_sessions)), array("id"=>$user_info['id']));
				} else {
					//Allow other sessions to stay logged in
					$valid_sessions=json_decode($user_info['valid_sessions'],true);
					$valid_sessions[]=$_SESSION['_SWDF']['info']['persistant_session_id'];
					$db->update($_SWDF['settings']['db']['tables']['users'], array("valid_sessions"=>json_encode($valid_sessions)), array("id"=>$user_info['id']));
				}
				
				//Looks like the user passed all checks, log event and setup/secure session
				$_SESSION['_SWDF']['info']['last_login']=time();
				$_SESSION['_SWDF']['info']['previous_login']=strtotime($user_info['last_login']);
				$_SESSION['_SWDF']['info']['user_id']=$user_info['id'];
				unset($_SESSION['_SWDF']['settings']['after_login_redirect_to']);
				
				header( "Location: " . $_SESSION['_SWDF']['settings']['after_login_redirect_to'] );	

				exit;
			}
		}
	}
}

//TODO: log login attempts, make brute force protection
header("Location: ".make_link("login",Array("failure_reason"=>$failure_reason,"username"=>$username),true));
?>
