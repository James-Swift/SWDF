<?php

class SWDF_encryption {
    const CYPHER = 'blowfish';
    const MODE   = 'cfb';

    public function encrypt($plaintext, $key)
    {
        $td = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $crypttext = mcrypt_generic($td, $plaintext);
        mcrypt_generic_deinit($td);
        return $iv.$crypttext;
    }

    public function decrypt($crypttext, $key)
    {
        $plaintext = '';
        $td        = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $ivsize    = mcrypt_enc_get_iv_size($td);
        $iv        = substr($crypttext, 0, $ivsize);
        $crypttext = substr($crypttext, $ivsize);
        if ($iv)
        {
            mcrypt_generic_init($td, $key, $iv);
            $plaintext = mdecrypt_generic($td, $crypttext);
        }
        return $plaintext;
    }
}


function SWDF_clean_tokens(){
	//clean up old tokens
	if (isset($_SESSION['_SWDF']['tokens']) && is_array($_SESSION['_SWDF']['tokens'])){
		foreach ($_SESSION['_SWDF']['tokens'] as $toks_id=>$toks){
			if (is_array($toks) && sizeof($toks)>0){
				foreach ($toks as $tok_id=>$tok_opts){
					if ($tok_opts['expiry']<=time()){
						unset($_SESSION['_SWDF']['tokens'][$toks_id][$tok_id]);
					}
				}
			} else {
				unset($_SESSION['_SWDF']['tokens'][$toks_id]);
			}
		}
	}	
}
function SWDF_generate_token($id, $expiry, $data=NULL){
	//clean up expired tokens
	SWDF_clean_tokens();

	//return new token
	$token=whitelist( strtolower( hash("sha256", $id . mt_rand() . uniqid() ) ), "id");
	$_SESSION['_SWDF']['tokens'][$id][$token]['data']=$data;
	$_SESSION['_SWDF']['tokens'][$id][$token]['expiry']=time()+$expiry;
	return $token;
}

function SWDF_validate_token($id, $token, $auto_delete=true){
	//clean up expired tokens
	SWDF_clean_tokens();
	
	//check if token exists
	if (isset($_SESSION['_SWDF']['tokens'][$id][$token])){
		if ($auto_delete===true){
			unset($_SESSION['_SWDF']['tokens'][$id][$token]);
		}
		return true;
	} else {
		return false;
	}
}

?>