<?php

//scan for old cache files to delete
function SWDF_clean_view_cache(){
	$dir=scandir($_SWDF['paths']['views_cache']);
	$match=$_SWDF['info']['current_view']."[";
	$len=strlen($match);
	$files=array();
	if (isset($dir) && is_array($dir)){
		foreach($dir as $file){
			if (substr($file,0,$len)===$match && is_file($file)){
				if (filemtime($_SWDF['paths']['views_cache'].$file)<time()-$cache_expiry) {
					unlink($_SWDF['paths']['views_cache'].$file);
				} else {
					$files[]=$_SWDF['paths']['views_cache'].$file;
				}
			}
		}
	}

	//Find maximum number of cache files for this size
	$file_limit=$_SWDF['settings']['default_cache_file_limit'];
	if ($_SWDF['view']['cache_file_limit']>0){
		$file_limit=$_SWDF['view']['cache_file_limit'];
	}

	//Check number of cache files hasn't been exceeded
	$size=sizeof($files);
	if ($size>=$file_limit && $file_limit>0){
		while ($size>=$file_limit){
			$to_unlink=array_pop($files);
			unlink($to_unlink);
			$size=$size-1;
		}
	}
}
?>
