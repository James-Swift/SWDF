<?php

	if ($_SWDF['settings']['allow_login']===false){
		
		print "Sorry. The user system has been disabled.";
		
	} else {
		
		//Sanitize variables
		$username="";
		if (isset($_GET['username'])){
			$username=whitelist($_GET['username'],"id");
		}
		$token = SWDF_generate_token("login",120);
		
		//TODO: parse $failure_reason and give feedback
		?>
		<form action="<?php print make_link("_process_login"); ?>" method="post" id="LOGIN_form">
			<input type="hidden" name="token" value="<?php print $token ?>" id="LOGIN_token" />
			<table>
				<tr>
					<td>Username: </td>
					<td><input type="text" name="username" value="<?php print $username; ?>" id="LOGIN_username" /></td>
				</tr>
				<tr>
					<td>Password:</td>
					<td><input type="password" name="password" value="" id="LOGIN_password" /></td>
				</tr>
			</table>

			<input type="hidden" name="noscript" value="true" />
			<input type="submit" value="Log In" />

			<?php
			
			//Check whether to bother outputting session options box at all
			if (	$_SWDF['settings']['concurrent_login_sessions']['state']==="allow" || 
					$_SWDF['settings']['multiple_ip_login_sessions']['state']==="allow" || 
					$_SWDF['settings']['multiple_browser_login_sessions']['state']==="allow"
				) {
			
				print "<p>";
					print "<b>Session Security Options:</b><br/>";

					//Concurrent Sessions
					if ($_SWDF['settings']['concurrent_login_sessions']['state']==="allow") {
						print '<input type="checkbox" id="LOGIN_limit_to_one_session" name="limit_to_one_session" ';
						if ($_SWDF['settings']['concurrent_login_sessions']['default']===false){
							print 'checked="checked" ';
						}
						print '/> Log-out all my other sessions on this website<br/>';
					}

					//Multiple IPs per user session
					if ($_SWDF['settings']['multiple_ip_login_sessions']['state']==="allow") {
						print '<input type="checkbox" id="LOGIN_limit_to_ip" name="limit_to_ip" ';
						if ($_SWDF['settings']['multiple_ip_login_sessions']['default']===false){
							print 'checked="checked" ';
						}							
						print ' /> Limit this session to connections using my IP Address<br/>';				
					}

					//Multiple Browsers Per Session
					if ($_SWDF['settings']['multiple_browser_login_sessions']['state']==="allow") {
						print '<input type="checkbox" id="LOGIN_limit_to_browser" name="limit_to_browser" ';
						if ($_SWDF['settings']['multiple_browser_login_sessions']['default']===false){
							print 'checked="checked" ';
						}					
						print ' /> Limit this session to connections using my exact Browser Version<br/>';
					}

				print "</p>";
			}
			
		print "</form>";
		
	}
