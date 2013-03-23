<?php

	function whitelist($variable,$preset){
		global $_SWDF;
	
		if (isset($_SWDF['filters']['whitelist'][$preset])){
			$this_filter=$_SWDF['filters']['whitelist'][$preset];

			//apply regex
			$variable=preg_replace("/[^".$this_filter['filter']."]/", "", $variable);
			
			//check min length
			if (isset($this_filter['min_length']) && $this_filter['min_length']>0){
				if (strlen($variable)<$this_filter['min_length']){
					return NULL;
				}
			}
			
			//check max length
			if (isset($this_filter['max_length']) && $this_filter['max_length']!==NULL){
				if (strlen($variable)>$this_filter['max_length']){
					$variable=substr($variable,0,$this_filter['max_length']);
				}
			}
			
			return $variable;
		} else {
			return preg_replace("/[^".$preset."]/", "", $variable);
		}
	}

	//Pre-defined filters for whitelist()
	$_SWDF['filters']['whitelist']['id']=Array(
		"filter"=>"a-zA-Z0-9\_\-\.",
	);
	$_SWDF['filters']['whitelist']['email']=Array(
		"filter"=>"a-zA-Z0-9@\_\-\.",
	);
	$_SWDF['filters']['whitelist']['file']=Array(
		"filter"=>"\w\.\-\s\(\)",
	);
	$_SWDF['filters']['whitelist']['directory']=Array(
		"filter"=>"\w\.\-\s\(\)",
	);
	$_SWDF['filters']['whitelist']['name']=Array(
		"filter"=>"a-zA-Z0-9\s\'\-",
	);
	$_SWDF['filters']['whitelist']['place']=Array(
		"filter"=>"a-zA-Z0-9\s\'\-",
	);
	$_SWDF['filters']['whitelist']['number']=Array(
		"filter"=>"0-9",
	);
	$_SWDF['filters']['whitelist']['letter']=Array(
		"filter"=>"a-zA-Z",
	);	
?>