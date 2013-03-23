<?php
ob_start();
if (is_array($_SWDF['info']['cache_passthrough_files'])){
	foreach($_SWDF['info']['cache_passthrough_files'] as $file){			
		require($file);
	}
}
$_SWDF['___ob_buffer']=ob_get_flush();
?>
