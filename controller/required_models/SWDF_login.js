//Check main SWDF object already exists. If not, make it.
if (typeof SWDF !== 'object') {
    SWDF = {};
}

//Check if SWDF.login object exists. If not, create it.
if (typeof SWDF.login !== 'object') {
    SWDF.login = {};
}

(function () {
	"use strict";
	
	
	// Generate a hash of all login variables (including a unique token) which will be verified server side to check for
	// tampering of login variables and to prevent request replays (as each hash can only be used once due to the unique
	// token included in each).
	SWDF.login.generate_hash = function (user, pass, token, limit_to_one_session, limit_to_ip, limit_to_browser) {
		
		//Attempt to generate login query hash
		try {
			return CryptoJS.SHA256(
				(user + 
				CryptoJS.SHA256( (CryptoJS.SHA256(pass).toString() + CryptoJS.SHA256(user.toLowerCase()).toString()).toLowerCase() ).toString() +
				token + 
				limit_to_one_session +
				limit_to_ip +				
				limit_to_browser).toLowerCase()
			).toString();
		} catch (e) {
			return false;
		}
	};



}());