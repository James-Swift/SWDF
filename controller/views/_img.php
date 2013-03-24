<?php
	//This page resizes the passed image acording to the passed size. Sizes are defined in settings/images.php
	//Images may be cached if allowed in $_SWDF['settings']['images']['cache_resized'];
	//Call this page like this: index.php?p=_img&size=SIZE&img=RELATIVE_PATH_TO_IMAGE
	//or use the make_img_link() function.
	
	$size=$_GET['size'];
	$img=$_GET['img'];
	
	//Load paths specific to this user
	SWDF_load_user_img_paths();
	

	//Get details of requested size
	$size=SWDF_validate_resize_request($img,$size);

	//Check whether to proceed with resize request
	if ($size!=false && is_array($size)){

		//Get absolute path to image
		$img_path=$_SWDF['paths']['root'].$img;
		$img_path=str_replace(Array("\\","//"),"/",$img_path);
		$img_path=str_replace(Array("../","./"),"",$img_path);
		
		//Create cache filename
		if ($_SWDF['settings']['images']['cache_resized']===true && $size['disable_caching']!==true){
			$cache_file=$_SWDF['paths']['images_cache'].basename($img_path)."[".md5($img_path.$size['id'])."].cache";
			//check if it exists
			if (is_file($cache_file) && filemtime($cache_file)>time()-$_SWDF['settings']['images']['cache_expiry']){
				//Change method to "original" so it will just be passed straight through
				$size=Array(
					"method"=>"original"
				);
				//Change img_path to cache file path
				$img_path=$cache_file;
			}
		}	
		
		//get properties of actual image
		$properties=getimagesize($img_path);
		
		//Determine resizing method
		if ($size['method']==="original" && $size['output']==""){
			//Check file is an image
			if ($properties!=false){
				//just pass image through script
				if ($fp = fopen($img_path, "rb")){
					header('Expires: '.gmdate('D, d M Y H:i:s', time()+$_SWDF['settings']['images']['cache_expiry']).'GMT');
					header("Content-type: {$properties['mime']}");
					fpassthru($fp);
				}
			} else {
				//Invalid image
				header("HTTP/1.1 404 Not Found");
			}
			
		} else if(in_array($size['method'], Array("original","fit","fill","stretch","scale"))===true) {
			
			//Load resizer class
			$resizer=new SWDF_image_resizer();
			
			//Set JPEG quality
			$resizer->quality=$_SWDF['settings']['images']['default_jpeg_quality'];
			if ($size['quality']!=null){
				$resizer->quality=$size['quality'];
			}
			
			//load source
			$resizer->load_image($img_path);
			
			//resize image
			$resizer->resize($size['method'],$size['width'],$size['height'],$size['scale']);
			
			//add watermark
			if ($size['watermark']!="" && is_array($size['watermark'])){
				if ($size['watermark']['opacity']==""){
					$size['watermark']['opacity']=$_SWDF['settings']['images']['default_watermark_opacity'];
				}
				$resizer->add_watermark($size['watermark']['path'],$size['watermark']['v'],$size['watermark']['h'],$size['watermark']['opacity'],$size['watermark']['scale'],$size['watermark']['repeat']);
			}
			
			//Save image to cache
			if ($_SWDF['settings']['images']['cache_resized']===true && $size['disable_caching']!==true){
				$resizer->output_image($size['output'],$cache_file);
			}
			
			//output image to screen
			$resizer->output_image($size['output']);
			
			//Request that the browser cache this page
			header('Expires: '.gmdate('D, d M Y H:i:s', time()+$_SWDF['settings']['images']['cache_expiry']).'GMT');
			
			//Clean the cache directory
			SWDF_clean_image_cache();
		} else {
			die("Invalid method specified for this size.");
		}
	} else {
		//Not allowed to resize this image/image not found
		header("HTTP/1.1 404 Not Found");
	}
	
	
		

	
	
?>