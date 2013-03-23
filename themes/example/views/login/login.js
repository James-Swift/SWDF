var presubmit = function(){
	//Check all fields are filled in
	if (document.getElementById('LOGIN_token').value==""){
		//TODO: Let the login form request a token via the API
	}
	if (document.getElementById('LOGIN_username').value==""){
		alert("Please eneter a username");
		document.getElementById('LOGIN_username').focus()
		return false;
	}
	if (document.getElementById('LOGIN_password').value==""){
		alert("Please eneter a password");
		document.getElementById('LOGIN_password').focus();
		return false;
	}
	
	//Generate login hash
	var hash = SWDF.login.generate_hash(
		document.getElementById('LOGIN_username').value,
		document.getElementById('LOGIN_password').value,
		document.getElementById('LOGIN_token').value,
		document.getElementById('LOGIN_limit_to_one_session').checked,						
		document.getElementById('LOGIN_limit_to_ip').checked,
		document.getElementById('LOGIN_limit_to_browser').checked
	);
	//Check a hash was generated
	if (hash===false){
		return false;
	} else {
		//Store hash in form to be submitted
		document.getElementById('LOGIN_hash').value=hash
		//Clear password box to avoid it being sent plain-text
		document.getElementById('LOGIN_password').value="";
		//Submit
		return true;
	}
};