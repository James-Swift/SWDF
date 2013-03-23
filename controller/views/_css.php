<?php
	if (!isset($_SWDF)){
		exit;	
	}
	
	$for_view=whitelist($_GET['for'],"id");
	$path=whitelist($_GET['path'],"id");
	$link=SWDF_get_resource_link("css",$path,$for_view);
	if ($link!==false){
		header("Content-type: text/css");	
		if (startsWith("http://",$link) || startsWith("https://",$link)){
			print file_get_contents($link);
		} else {
			if (is_file($link)){
				require($link);
			} else {
				header("HTTP/1.1 404 Not Found");
			}
		}
	
	} else {
		header("HTTP/1.1 403 Forbidden");
	}
	
?>