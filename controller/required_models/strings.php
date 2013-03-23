<?php
	//Used all over SWDF and websites made with it to do i18n or even just one language text retrieval
	function get_text($parent_view,$text_id,$language=null,$force_refresh=false){
		global $_SWDF,$db;
		static $text_store;

		//Hnadle selecting language
		if ($language==null){
			$language=$_SESSION['_SWDF']['settings']['language'];
		}
		if ($language==null || in_array($language,$_SWDF['info']['available_languages'])===false){
			return false;
		}

		//Check if this string has been requested before on this execution. If so, just send the cached version.
		if (isset($text_store[$parent_view][$text_id][$language]) && $force_refresh!==false){
			return $text_store[$parent_view][$text_id][$language];
		}

		//Check which handler to use
		if ($_SWDF['settings']['use_db_for_text'] && $_SWDF['settings']['use_db']){
			try {
				$text_row=$db->select1("text,requires_update,use_instead",$_SWDF['settings']['db']['tables']['text'],Array("parent_view"=>$parent_view,"text_id"=>$text_id,"language"=>$language),"last_modified desc");
				if ($text_row!=""){
					//Check if row needs update and we should be using another language
					if ($text_row['requires_update']==1 && $text_row['use_instead']!=null){
						$text_row=$db->select1("text,requires_update,use_instead",$_SWDF['settings']['db']['tables']['text'],Array("parent_view"=>$parent_view,"text_id"=>$text_id,"id"=>$text_row['use_instead']));
					}
					if ($text_row['text']!=""){
						$text_store[$parent_view][$text_id][$language]=$text_row['text'];
						return $text_row['text'];
					}
				}
			} catch (PDOException $e) {
				//return "Database Error";
			}
		} else {
			$text_file=$_SWDF['paths']['text'].$parent_view."/".$text_id.".".$language.".txt";
			$text_settings=$_SWDF['paths']['text'].$parent_view."/".$text_id.".".$language.".settings";
			if (is_file($text_settings)){
				$t_settings=file($text_settings);
			}
			
			//Check if requires update
			if (isset($t_settings) && $t_settings[1]==1){
				//Check if we should use another language file
				if ($t_settings[2]!=null){
					$text_file=$_SWDF['paths']['text'].$parent_view."/".$text_id.".".$t_settings[2].".txt";
				}
			}
			
			//Check for file and return text data
			if (is_file($text_file)){
				$the_text=file_get_contents($text_file);
				$text_store[$parent_view][$text_id][$language]=$the_text;
				return $the_text;
			}

		}
		
		//If all else fails, print $textid
		return $parent_view."/".$text_id;
	}
	
	function SWDF_make_resource_link($type,$path,$for_view=null){
		global $_SWDF;
		$ext=substr($path,strrpos($path,".")+1);
		$handler_view="_".$type;
		if ($_SWDF['views'][$handler_view]!=""){
			if ($for_view!=null){
				if ((substr($path,0,strlen($_SWDF['paths']['root']."themes/"))===$_SWDF['paths']['root']."themes/" || substr($path,0,strlen($_SWDF['paths']['root']."assets/"))===$_SWDF['paths']['root']."assets/") && in_array($ext,$_SWDF['settings']['allow_direct_resource_linking_for'])===true){
					return substr($path,strlen($_SWDF['paths']['root']));
				} else {
					return make_link($handler_view, Array("for"=>$for_view,"path"=>md5($path)));
				}
			} else {
				return make_link($handler_view, Array("path"=>$path));
			}
		} else {
			return false;
		}
	}

	function SWDF_get_resource_link($type,$path,$for_view=null){
		global $_SWDF;
		if ($for_view!=NULL){
			//try view's files first
			if (is_array($_SWDF['views'][$for_view][$type])){
				foreach ($_SWDF['views'][$for_view][$type] as $link){
					if (md5($link)===$path){
						return $link;
					}
				}
			}
			//then try template's files next
			if (!isset($_SWDF['views'][$for_view]['template'])){
				$_SWDF['views'][$for_view]['template']=$_SWDF['theme']['default_template'];
			}
			if (is_array($_SWDF['templates'][$_SWDF['views'][$for_view]['template']][$type])){
				foreach ($_SWDF['templates'][$_SWDF['views'][$for_view]['template']][$type] as $link){
					if (md5($link)==$path){
						return $link;
					}
				}
			}
			return false;
		}
		return false;
	}


	function make_link($view, $variables="",$full_url=false,$html_output=false){
		global $_SWDF;
		$link="";
		if ($full_url==true){
			$link=$_SWDF['info']['website_full_url'];
		}	
		$link.="?".$_SWDF['settings']['vvn']."=".urlencode($view);
		if ($variables!=""){
			if (is_array($variables)){
				foreach($variables as $key=>$val){
					$link.="&$key=".urlencode($val);
				}
			} else {
				$link.="&$variables";
			}
		}
		if ($html_output==false){
			return $link;
		} else {
			return "<a href=\"".htmlentities($link)."\">".htmlentities(( $_SWDF['views'][$view]['gui_name']!="" ? $_SWDF['views'][$view]['gui_name'] : $view ))."</a>";
		}
	}
	
	function make_img_link($img,$size=null){
		global $_SWDF;
		
		//Check image settings are loaded
		if ($_SWDF['settings']['images']['settings_loaded']!==true){
			require($_SWDF['paths']['root']."settings/images.php");
		}
		
		if ($size==NULL){
		    $size=$_SWDF['settings']['images']['default_size'];
		}
		return make_link("_img",Array("img"=>$img,"size"=>$size));
	}
	

	function starts_with($needle, $haystack){
		return (substr($haystack,0,strlen($needle))===$needle);
	}

	function starts_withi($needle, $haystack){
		return (strtolower(substr($haystack,0,strlen($needle)))===strtolower($needle));
	}
	
	function ends_with($needle, $haystack){
		return (substr($haystack,strlen($needle)*-1)===$needle);
	}

	function ends_withi($needle, $haystack){
		return (strtolower(substr($haystack,strlen($needle)*-1))===strtolower($needle));
	}	
?>