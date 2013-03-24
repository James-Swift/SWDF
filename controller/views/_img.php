<?php
	//This page resizes the passed image acording to the passed size. Sizes are defined in settings/images.php
	//Images may be cached if allowed in $_SWDF['settings']['images']['cache_resized'];
	//Call this page like this: index.php?p=_img&size=SIZE&img=RELATIVE_PATH_TO_IMAGE
	//or use the make_img_link() function.
	
	$size=@$_GET['size'];
	$img=@$_GET['img'];

	//Load paths specific to this user
	SWDF_load_user_img_paths();
	
	//We don't actually want to write to the session file so close it straight away to speed up concurrent requests
	session_write_close();

	///////////////////////////////////////////////////////////////////////
	//Resize the Image

	//Make resize request
	$result=SWDF_image_resizer_request($size,$img,false);
	

	///////////////////////////////////////////////////////////////////////
	//Handle returned data, mapping headers etc. and output image
	if ($result['status']!=""){
		//Set status Code
		header(":",null,$result['status']);

		//Set headers
		if (isset($result['headers']) && is_array($result['headers'])){
			foreach($result['headers'] as $header){
				header($header);
			}
		}

		//Output image data
		if (isset($result['data'])){
			print $result['data'];
		}
	} else {
		header(":",null,500);
	}
	
?>