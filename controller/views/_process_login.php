<?php

namespace JamesSwift\SWDF;
//TODO: Convert the follwing into a function (API) which can be called

//Suppress errors that could prevent a redirect
error_reporting( E_ERROR );

//Check dependancies are in place
if ( !isset( $_SWDF ) ) {
	exit;
}

/////////////////////////////////////////////////////////////

//Sanitize variables
$failure_reason="";
$username = whitelist( $_POST['username'], "email" );
$password = $_POST['password'];
$token = whitelist( $_POST['token'], "id" );

//Fetch user row from DB
$user_info = user_info($username);

//Set security settings
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

/////////////////////////////////////////////////////////////

//Check if people are allowed to log in
if ( $_SWDF['settings']['allow_login'] === false ) {
	log_failed_login_and_die($username, "login_disabled");
}

//Make sure enough requests have been completed on this session to allow login
if ($_SESSION['_SWDF']['info']['requests_this_session']<$_SWDF['settings']['require_session_requests_before_login']){
	log_failed_login_and_die($username, "insufficient_session_requests_before_login");
}





////////////////////////////////////////////////////////////////


//Validate token (and delete)
if ( SWDF_validate_token( "login", $token ) === false ) {
	log_failed_login_and_die($username, "token");
}


//Check user is recognized
if ($user_info==""){
	log_failed_login_and_die($username, "invalid_user");
} 

//User exists, now check password is right
if (!password_verify($password, $user_info['password'])){
	log_failed_login_and_die($username, "credentials");
}

//User credentials confirmed
//Check if the password hash needs updating?
if (password_needs_rehash($user_info['password'], PASSWORD_DEFAULT, ["cost"=>12])) {
	
    //create a new hash, and replace the old one
    $newhash = password_hash($password, PASSWORD_DEFAULT, ["cost"=>12]);
    $db->update($_SWDF['settings']['db']['tables']['users'], ["password"=>$newhash], ["id"=>$user_info['id']]);
}

//Check if User is banned.
if ($user_info['banned_until']>date("Y-m-d H:i:s")){
	log_failed_login_and_die($username, "banned");
} 

////////////////////////////////////////////////////
// Security checks complete
////////////////////////////////////////////////////


//Store session security setings
$_SESSION['_SWDF']['info']['limit_to_ip']=false;
if ($limit_to_ip==="true"){
	$_SESSION['_SWDF']['info']['limit_to_ip']=\JamesSwift\SWDF\get_ip();
}
$_SESSION['_SWDF']['info']['limit_to_browser']=$limit_to_browser;

//Add to or reset the valid_sessions variable in the user db row
if ($limit_to_one_session){
	//Log out all other sessions
	$valid_sessions=[];
	$valid_sessions[]=$_SESSION['_SWDF']['info']['persistant_session_id'];

	$db->update($_SWDF['settings']['db']['tables']['users'], ["valid_sessions"=>json_encode($valid_sessions)], ["id"=>$user_info['id']] );
} else {
	//Allow other sessions to stay logged in
	$valid_sessions=json_decode($user_info['valid_sessions'],true);
	$valid_sessions[]=$_SESSION['_SWDF']['info']['persistant_session_id'];
	$db->update($_SWDF['settings']['db']['tables']['users'], ["valid_sessions"=>json_encode($valid_sessions)], ["id"=>$user_info['id']] );
}

//setup/secure session
$_SESSION['_SWDF']['info']['last_login']=time();
$_SESSION['_SWDF']['info']['previous_login']=strtotime($user_info['last_login']);
$_SESSION['_SWDF']['info']['user_id']=$user_info['id'];
$db->update($_SWDF['settings']['db']['tables']['users'], ["last_login"=>date("Y-m-d H:i:s")], ["id"=>$user_info['id']] );

//Log event
log_event(	"login",
			"User: ".$user_info['username']." Logged In",
			"User ".$user_info['username']." (UID ".$user_info['id'].") logged in at ".date("Y-m-d H:i:s")." from IP: ".\JamesSwift\SWDF\get_ip(),
			10
);

//Redirect to login page
if ($_SESSION['_SWDF']['settings']['after_login_redirect_to']==""){
	$_SESSION['_SWDF']['settings']['after_login_redirect_to'] = make_link($_SWDF['theme']['default_view'],null,true);
}
header( "Location: " . $_SESSION['_SWDF']['settings']['after_login_redirect_to'] );	

unset($_SESSION['_SWDF']['settings']['after_login_redirect_to']);



