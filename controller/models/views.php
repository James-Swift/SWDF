<?php

	function SWDF_load_theme($_theme){
		global $_SWDF,$db;
		
		//clear out any old theme data
		unset($_SWDF['theme'],$_SWDF['view'],$_SWDF['template'],$_SWDF['themes'],$_SWDF['templates'],$_SWDF['views']);
		
		// Load pre-defined views used by SWDF for handling resources (js, css etc.)
		require($_SWDF['paths']['root']."controller/predefined_views.php");
		
		/////////////////////////////////
		//Load theme and associated views
		if (is_file($_SWDF['paths']['themes'].$_theme."/_settings.php")){
			require($_SWDF['paths']['themes'].$_theme."/_settings.php");
		} else {
			die("Cannot load theme.");
		}
		
		if ($_SWDF['themes'][$_theme]!=null){

			//Load into $_SWDF['theme']
			$_SWDF['theme']=&$_SWDF['themes'][$_theme];
		
			//Before proceeding check all SWDF required views exist
			if (is_array($_SWDF['settings']['required_views'])){
				foreach($_SWDF['settings']['required_views'] as $view){
					if ($_SWDF['views'][$view]==NULL){
						print "Sorry, the selected theme is missing one or more of the required views: ";
						print implode (", ",$_SWDF['settings']['required_views']);
						exit;
					}
				}
			}	
			return true;
		} else {
			die("Invalid theme file.");
		}
	}
	
	function SWDF_load_view($_view=NULL){ 
		global $_SWDF;
		
		if ($_view!=NULL){
			$_SWDF['info']['current_view']=$_view;
		}

		//check if view is set, if not set default view
		if ($_SWDF['info']['current_view']==NULL){
			$_SWDF['info']['current_view']=$_SWDF['theme']['default_view'];
		}

		// If view doesn't exist, set to 404
		if (!isset($_SWDF['views'][$_SWDF['info']['current_view']])){
			$_SWDF['info']['current_view']="404";
		}
		
		//Set an easy link to selected view
		$_SWDF['view']=&$_SWDF['views'][$_SWDF['info']['current_view']];
		
		///////////
		//check view security settings
		$view_authorized=true;
		
		//user variables
		if ($_SWDF['info']['user_logged_in']==true){
			//required
			if (isset($_SWDF['view']['require_user_settings']) && is_array($_SWDF['view']['require_user_settings'])){
				foreach($_SWDF['view']['require_user_settings'] as $item){
					if ($_SWDF['info']['user'][$item]!=true){
						$view_authorized=false;
					}
				}
			}
			//denied
			if (isset($_SWDF['view']['deny_user_settings']) && is_array($_SWDF['view']['deny_user_settings'])){
				foreach($_SWDF['view']['deny_user_settings'] as $item){
					if ($_SWDF['info']['user'][$item]!=false){
						$view_authorized=false;
					}
				}
			}		
		}
		
		//Settings vairables
			//Requires
			if (isset($_SWDF['view']['require_settings']) && is_array($_SWDF['view']['require_settings'])){
				foreach($_SWDF['view']['require_settings'] as $item){
					if ($_SWDF[$item]!=true){
						$view_authorized=false;
					}
				}
			}

			//Denied
			if (isset($_SWDF['view']['deny_settings']) && is_array($_SWDF['view']['deny_settings'])){
				foreach($_SWDF['view']['deny_settings'] as $item){
					if ($_SWDF[$item]!=false){
						$view_authorized=false;
					}
				}
			}
		
		//If user not allowed to access this view, redirect to $_SWDF['settings']['on_auth_failure']
		if ($view_authorized===false){
			$_SWDF['info']['current_view']=&$_SWDF['settings']['on_auth_failure'];
			$_SWDF['view']=&$_SWDF['views'][$_SWDF['settings']['on_auth_failure']];
		}
		if (!isset($_SWDF['view']['template'])){
			$_SWDF['view']['template']=$_SWDF['theme']['default_template'];
		}
		$_SWDF['template']=&$_SWDF['templates'][$_SWDF['view']['template']];
		
		//if caching not enabled for this page, disable it throughout script
		if (!isset($_SWDF['view']['cache']) || (isset($_SWDF['view']['cache']) && $_SWDF['view']['cache']===false)){
			$_SWDF['settings']['enable_view_caching']=false;
		}
		
		//////////
		//Create "template_data" array
		
		//use view as template
		$_SWDF['template_data']=$_SWDF['view'];
		
		//Update resource links (css/js)
		if (isset($_SWDF['view']['css']) && is_array($_SWDF['view']['css'])){
			foreach($_SWDF['view']['css'] as $id=>$url){			
				$_SWDF['template_data']['css'][$id]=SWDF_make_resource_link("css",$url,$_SWDF['info']['current_view']);
			}
		}
		if (isset($_SWDF['view']['js']) && is_array($_SWDF['view']['js'])){
			foreach($_SWDF['view']['js'] as $id=>$url){			
				$_SWDF['template_data']['js'][$id]=SWDF_make_resource_link("js",$url,$_SWDF['info']['current_view']);
			}
		}	
		
		
		//Include and update resource links from selected template
		if (isset($_SWDF['template']['css']) && is_array($_SWDF['template']['css'])){
			foreach($_SWDF['template']['css'] as $id=>$url){			
				$_SWDF['template_data']['css'][]=SWDF_make_resource_link("css",$url,$_SWDF['info']['current_view']);
			}
		}
		if (isset($_SWDF['template']['js']) && is_array($_SWDF['template']['js'])){
			foreach($_SWDF['template']['js'] as $is=>$url){			
				$_SWDF['template_data']['js'][]=SWDF_make_resource_link("js",$url,$_SWDF['info']['current_view']);
			}
		}	
		
		
		//Finally Include any model files specified for this view
		if (isset($_SWDF['view']['model_includes']) && is_array($_SWDF['view']['model_includes'])){
			foreach($_SWDF['view']['model_includes']as $dir){			
				require($dir);
			}
		}
		
		return $view_authorized;
	}
	
//	function add_theme($theme){
//		global $_SWDF;
//		if ($theme['name']!=NULL  &&  is_dir($theme['path'])  &&  $theme['default_view']!=NULL){
//			$_SWDF['themes'][$theme['name']]=$theme;
//			return true;
//		}
//		die("Invalid theme file.");
//		return false;
//	}
//	
//	function add_template($template){
//		global $_SWDF;
//		if ($template['name']!=NULL  &&  $template['path']!=NULL){
//			$_SWDF['templates'][$template['name']]=$template;
//			return true;
//		}
//		die("Tried to add invalid template: ".$template['name']);
//		return false;	
//	}
//	
//	function add_view($view){
//		global $_SWDF;
//		if (sizeof($view['body_includes'])>0  &&  $view['name']!=NULL  &&  $view['template']!=NULL){
//			$_SWDF['views'][$view['name']]=$view;
//			return true;
//		}
//		die("Tried to add invalid view: ".$view['name']);
//		return false;	
//	}
	
	
	function SWDF_head_includes(){
		global $_SWDF,$db;
		if (isset($_SWDF['template_data']['head_includes']) && is_array($_SWDF['template_data']['head_includes'])){
			foreach($_SWDF['template_data']['head_includes'] as $dir){			
				require($dir);
			}
		}	
	}

	function SWDF_body_includes(){
		global $_SWDF,$db;	
		if (isset($_SWDF['template_data']['body_includes']) && is_array($_SWDF['template_data']['body_includes'])){
			foreach($_SWDF['template_data']['body_includes'] as $dir){			
				require($dir);
			}
		}
	}
	
	function SWDF_print_meta_tags(){
		global $_SWDF,$db;
		if (isset($_SWDF['template_data']['meta_tags']) && is_array($_SWDF['template_data']['meta_tags'])){
			foreach($_SWDF['template_data']['meta_tags'] as $name => $content){
				print "\t\t<meta name=\"".htmlentities($name)."\" content=\"".htmlentities($content)."\" />\n";
			} 
		}
	}
	
	function SWDF_print_css_tags(){
		global $_SWDF,$db;
		if (isset($_SWDF['template_data']['css']) && is_array($_SWDF['template_data']['css'])){
			foreach($_SWDF['template_data']['css'] as $href){
				print "\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"".htmlentities($href)."\" />\n";
			} 
		}
	}
	
	function SWDF_print_js_tags(){
		global $_SWDF,$db;	
		if (isset($_SWDF['template_data']['js']) && is_array($_SWDF['template_data']['js'])){
			foreach($_SWDF['template_data']['js'] as $src){
				print "\t\t<script type=\"text/javascript\" src=\"".htmlentities($src)."\"></script>\n";
			} 
		}	
	}