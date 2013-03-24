<?php
	function SWDF_add_img_path($data){
		global $_SWDF;
		if ($data['path']!=""){
			//Normalize path
			$data['path']==str_replace(Array("\\","//"),"/",$data['path']."/");
			//check mime_type
			if ($data['output']==""){
				$data['output']="image/jpeg";
			}
			$_SWDF['settings']['images']['paths'][$data['path']]=$data;
		} else {
			die("Invalid Image Path.");
		}
	}
	
	function SWDF_add_user_img_path($path){
		$_SESSION['_SWDF']['images']['paths'][$path['path']]=$path;
	}
	
	function SWDF_load_user_img_paths(){
		global $_SWDF;
		foreach ($_SESSION['_SWDF']['images']['paths'] as $path){
			SWDF_add_img_path($path);
		}
	}
	
	
	function SWDF_get_img_path_info($image){
		global $_SWDF;
		
		//Check image settings are loaded
		if ($_SWDF['settings']['images']['settings_loaded']!==true){
			require($_SWDF['paths']['root']."settings/images.php");
		}
		
		//First, check image exists
		if (is_file($_SWDF['paths']['root'].$image)){
			//Get folder
			$image_path=str_replace(Array("\\","//"),"/",dirname($image)."/");
			

			//Find closest matching path
			$image_path_parts=explode("/",$image_path);
			if (is_array($image_path_parts)){
				foreach ($image_path_parts as $part){
					if (sizeof($image_path_parts)>0){
						$new_image_path=implode("/",$image_path_parts)."/";
						if (is_array($_SWDF['settings']['images']['paths'][$new_image_path])){
							$image_path_data=$_SWDF['settings']['images']['paths'][$new_image_path];
							break;
						} else {
							array_pop($image_path_parts);
						}
					} else {
						return false;
					}
				}
				
				//return data
				return $image_path_data;
				
			} else {
				return false;
			}
			
		} else {
			return false;
		}
	}
	
	function SWDF_get_allowed_sizes($path_id){
		global $_SWDF;

		//Check image settings are loaded
		if ($_SWDF['settings']['images']['settings_loaded']!==true){
			require($_SWDF['paths']['root']."settings/images.php");
		}
		
		$path=$_SWDF['settings']['images']['paths'][$path_id];
		if (is_array($path)){
			$allowed_sizes=Array();
			//Populate with all allowed sizes
			if ($path['allow_sizes']==="all" || $path['allow_sizes']==NULL){
				foreach($_SWDF['settings']['images']['sizes'] as $id=>$size){
					$allowed_sizes[$id]=$id;
				}
			} else if ($path['allow_sizes']!=NULL && is_array($path['allow_sizes'])){
				$allowed_sizes=$path['allow_sizes'];
			}
			//Now remove any denied sizes
			if ($path['deny_sizes']==="all"){
				$allowed_sizes=Array();
			} else if (is_array($path['deny_sizes'])){
				foreach ($path['deny_sizes'] as $id ){
					unset($allowed_sizes[$id]);
				}
			}
			return $allowed_sizes;
		} else {
			return false;
		}
	
	}
	
	function SWDF_validate_resize_request($image,$size){
		global $_SWDF;

		//Check image settings are loaded
		if ($_SWDF['settings']['images']['settings_loaded']!==true){
			require($_SWDF['paths']['root']."settings/images.php");
		}		
		
		$image=str_replace(Array("\\","//"),"/",$image);
		$image=str_replace(Array("../","./"),"",$image);

		//First, check file exists before other checks.
		if (is_file($_SWDF['paths']['root'].$image)){
			//Get security settings for the path the image is in
			$path=SWDF_get_img_path_info($image);
			//Check path is allowed
			if ($path!=false){
				//Find the allowed sizes for the path the image is in
				$sizes=SWDF_get_allowed_sizes($path['path']);
				//Check whether requested size is allowed
				if (is_array($sizes)){
					if (in_array($size,$sizes)===true && is_array($_SWDF['settings']['images']['sizes'][$size])){
						return $_SWDF['settings']['images']['sizes'][$size];
					}
				}
			}
			return false;
		} else {
			return false;
		}
	}
	
	function SWDF_clean_image_cache(){
		global $_SWDF;

		//Check image settings are loaded
		if ($_SWDF['settings']['images']['settings_loaded']!==true){
			require($_SWDF['paths']['root']."settings/images.php");
		}		
		
		$dir=scandir($_SWDF['paths']['images_cache']);
		foreach($dir as $file){
			if (is_file($_SWDF['paths']['images_cache'].$file) && filemtime($_SWDF['paths']['images_cache'].$file)<time()-$_SWDF['settings']['images']['cache_expiry'] && substr($file,-5,5)=="cache"){
				unlink($_SWDF['paths']['images_cache'].$file);
			}
		}
	}

	
	class SWDF_image_resizer {
		
		private $source;
		private $stream;
		private $type;
		private $width;
		private $height;
		private $img=array();
		
		public $compatible_mime_types=Array("image/jpeg","image/jp2","image/png","image/gif");
		public $quality;
		
		
		public function __construct(){

		}
		
		public function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){ 
		   // creating a cut resource 
		   $cut = imagecreatetruecolor($src_w, $src_h); 

		   // copying relevant section from background to the cut resource 
		   imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h); 

		   // copying relevant section from watermark to the cut resource 
		   imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h); 

		   // insert cut resource to destination image 
		   imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct); 
		} 		
		
		public function load_image($source,$id="main"){
			if (is_file($source)){
				$properties=getimagesize($source);
				if ($properties!=false){
					if (in_array($properties['mime'],$this->compatible_mime_types)===true){
						if ($properties['mime']=="image/jpeg" || $properties['mime']=="image/jp2"){
							if (!$this->img[$id]['stream']=imagecreatefromjpeg($source)){
								return false;
							}
						}
						if ($properties['mime']=="image/png"){
							if (!$this->img[$id]['stream']=imagecreatefrompng($source)){
								return false;
							}
						}
						if ($properties['mime']=="image/gif"){
							if (!$this->img[$id]['stream']=imagecreatefromgif($source)){
								return false;
							}
						}
						
						imagealphablending($this->img[$id]['stream'], false);
						imagesavealpha($this->img[$id]['stream'], true);
						
						$this->img[$id]['source']=$source;
						$this->img[$id]['type']=$properties['mime'];
						$this->img[$id]['width']=$properties[0];
						$this->img[$id]['height']=$properties[1];
						return true;
					}
					
				}
				
			}
			return false;
		}
		
		public function resize($method,$n_width=null,$n_height=null,$scale=null,$img_id="main"){
			if ($method==="original"){
				return true;
			} else if ($method==="fit"){
				
				if ($this->img[$img_id]['width']>=$this->img[$img_id]['height']){
					$scale=$n_width/$this->img[$img_id]['width'];
					if ($scale*$this->img[$img_id]['height']>$n_height){
						$scale=$n_height/$this->img[$img_id]['height'];
					}
				} else {
					$scale=$n_height/$this->img[$img_id]['height'];
					if ($scale*$this->img[$img_id]['width']>$n_width){
						$scale=$n_width/$this->img[$img_id]['width'];
					}					
				}
			
				$n_width=$this->img[$img_id]['width']*$scale;
				$n_height=$this->img[$img_id]['height']*$scale;
				
				//Create blank image
				$this->img['temp']['stream']=imagecreatetruecolor($n_width,$n_height);
				imagealphablending($this->img['temp']['stream'], false);
				imagesavealpha($this->img['temp']['stream'], true);
				
				//resize image
				if (imagecopyresampled($this->img['temp']['stream'], $this->img[$img_id]['stream'], 0, 0, 0, 0, $n_width, $n_height, $this->img[$img_id]['width'], $this->img[$img_id]['height'])){
				
					//place in stream
					$this->img[$img_id]['stream']=$this->img['temp']['stream'];
					$this->img[$img_id]['width']=$n_width;
					$this->img[$img_id]['height']=$n_height;
					return true;
				}
				
			} else if ($method==="fill"){
				//Create blank image
				$this->img['temp']['stream']=imagecreatetruecolor($n_width,$n_height);
				imagealphablending($this->img['temp']['stream'], false);
				imagesavealpha($this->img['temp']['stream'], true);
				
				//Determine scale
				if ($n_width<=$n_height){
					$scale=$this->img[$img_id]['width']/$n_width;
					if ($scale*$n_height>$this->img[$img_id]['height']){
						$scale = $this->img[$img_id]['height']/$n_height;
					}
				} else {
					$scale=$this->img[$img_id]['height']/$n_height;
					if ($scale*$n_width>$this->img[$img_id]['width']){
						$scale = $this->img[$img_id]['width']/$n_width;
					}
				}				
			
				$s_width=$n_width*$scale;
				$s_height=$n_height*$scale;
				
				$left=($this->img[$img_id]['width']-$s_width)/2;
				$top=($this->img[$img_id]['height']-$s_height)/2;
				
				//resize image
				if (imagecopyresampled($this->img['temp']['stream'], $this->img[$img_id]['stream'], 0, 0, $left, $top, $n_width, $n_height, $s_width, $s_height)){
				
					//place in stream
					$this->img[$img_id]['stream']=$this->img['temp']['stream'];
					$this->img[$img_id]['width']=$n_width;
					$this->img[$img_id]['height']=$n_height;
					return true;
				}
				
			} else if ($method==="stretch"){
				$this->img['temp']['stream']=imagecreatetruecolor($n_width,$n_height);
				imagealphablending($this->img['temp']['stream'], false);
				imagesavealpha($this->img['temp']['stream'], true);
				
				if (imagecopyresampled($this->img['temp']['stream'], $this->img[$img_id]['stream'], 0, 0, 0, 0, $n_width, $n_height, $this->img[$img_id]['width'], $this->img[$img_id]['height'])){
					$this->img[$img_id]['stream']=$this->img['temp']['stream'];
					$this->img[$img_id]['width']=$n_width;
					$this->img[$img_id]['height']=$n_height;					
					return true;
				}
			} else if ($method==="scale"){
				$this->img['temp']['stream']=imagecreatetruecolor($this->img[$img_id]['width']*$scale, $this->img[$img_id]['height']*$scale);
				imagealphablending($this->img['temp']['stream'], false);
				imagesavealpha($this->img['temp']['stream'], true);
				
				if (imagecopyresampled($this->img['temp']['stream'], $this->img[$img_id]['stream'], 0, 0, 0, 0, $this->img[$img_id]['width']*$scale, $this->img[$img_id]['height']*$scale, $this->img[$img_id]['width'], $this->img[$img_id]['height'])){				
					$this->img[$img_id]['stream']=$this->img['temp']['stream'];
					$this->img[$img_id]['width']=$this->img[$img_id]['width']*$scale;
					$this->img[$img_id]['height']=$this->img[$img_id]['height']*$scale;					
					return true;
				}
			}
			return false;
		}
		
		public function add_watermark($path,$v="center",$h="center",$opacity=85,$scale=1,$repeat=false){
			if ($this->load_image($path,"wm")){
				
				if ($scale!="" && $scale!=1){
					$this->resize("scale",null,null,$scale,"wm");
				}
				
				//Repeat the watermark in a pattern?
				if ($repeat==false){
					if ($h=="left"){ $h_pos=0; }
					if ($h=="center"){ $h_pos=($this->img['main']['width']/2)-($this->img['wm']['width']/2); }
					if ($h=="right"){ $h_pos=$this->img['main']['width']-$this->img['wm']['width']; }

					if ($v=="top"){ $v_pos=0; }
					if ($v=="center"){ $v_pos=($this->img['main']['height']/2)-($this->img['wm']['height']/2); }
					if ($v=="bottom"){ $v_pos=$this->img['main']['height']-$this->img['wm']['height']; }


					if ($this->imagecopymerge_alpha(	$this->img['main']['stream'], $this->img['wm']['stream'],
												$h_pos,$v_pos,
												0, 0, 
												$this->img['wm']['width'], $this->img['wm']['height'],
												$opacity
					)){
						return true;
					}
				} else {
					$x=0;$y=0;
					while ($x<$this->img['main']['width'] || $y<$this->img['main']['height']){
						$this->imagecopymerge_alpha(	$this->img['main']['stream'], $this->img['wm']['stream'],
													$x,$y,
													0, 0, 
													$this->img['wm']['width'], $this->img['wm']['height'],
													$opacity
						);
						if ($x<$this->img['main']['width']){
							$x=$x+$this->img['wm']['width'];
						} else {
							$y=$y+$this->img['wm']['height'];
							$x=0;
						}
					}
				}
			}
			return false;
		}
		
		public function output_image($output_type=null,$filename=null){
			if ($output_type==""){
				$output_type=$this->img['main']['type'];
			}
			if ($filename==null){
				header("Content-type: ".$output_type);
			}
			$output=false;
			if ($output_type=="image/jpeg" || $output_type=="image/jp2"){
				if (!$output=imagejpeg($this->img['main']['stream'], $filename, $this->quality)){
					return false;
				}
			}
			if ($output_type=="image/png"){
				if (!$output=imagepng($this->img['main']['stream'], $filename)){
					return false;
				}
			}
			if ($output_type=="image/gif"){
				if (!$output=imagegif($this->img['main']['stream'], $filename)){
					return false;
				}
			}
			return $output;
		}

		public function destory(){
			foreach($this->img as $img){
				if ($img->stream!=NULL){
					imagedestroy($img->stream);
				}
			}
		}
		
		public function __destruct(){
			$this->destory();
		}
	}
?>