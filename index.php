<?php

	/////////////////////////////////////////////////////////////////////////////////
	//                                                                             //
	//                      Swift Web Development Framework                        //
	//                       Copyright 2012 - James Swift                          //
	//                                                                             //
	//     May only be used with written permission from the copyright holder.     //
	//                                                                             //
	/////////////////////////////////////////////////////////////////////////////////
	//                                                                             //
	//         To allow for future updates please do not modify this file          //
	//                   or files in directory "controller/".                      //
	//                                                                             //
	//           This framework has only two reserved variable names:              //
	//                                                                             //
	//      $_SWDF <- A non-persistent, multi-dimensional associative array		   //
	//                                                                             //
	//		   You are free to read/write to it, but be careful not to		       //
	//       overwrite the entire variable as this will break the framework.       //
	//                                                                             //
	//                                                                             //
	//       $db <- The default database connection - an extended PDO class        //
	//                                                                             //
	//       To alter how the SWDF runs, please do not alter this file. Look       //
	//       in the "settings" directory instead.                                  //
	//                                                                             //
	/////////////////////////////////////////////////////////////////////////////////
	

	/////////////////////////////////////////////////////////////////////////////////
	//Initialize $_SWDF with basic data then execute conroller
	$_SWDF=Array();
	
	//Record Execution Start
	$_SWDF['info']['execution_start']=microtime(true);

	//Define absolute path to the root framework directory
	$_SWDF['paths']['root']=str_replace(Array('\\',"\\","//"),"/",dirname(__FILE__)."/");
	
	//Define path to SWDF folder as the web-browser sees it
	$_SWDF['paths']['web_root']=dirname($_SERVER['PHP_SELF'])."/";
	
	//Run controller which selects the appropriate view
	require("controller/controller.php");

	//Record Controller Execution End
	$_SWDF['info']['controller_completed']=microtime(true);
	

	/////////////////////////////////////////////////////////////////////////////////
	//Load files to generated the selected view (and cache it for later use if requested)

	//Check there's something to load.
	if ($_SWDF['theme']!=NULL && $_SWDF['view']!=NULL){

		//Send any theme-related HTTP-Header Information:
		if (isset($_SWDF['template']['header']) && is_array($_SWDF['template']['header']) && sizeof($_SWDF['template']['header'])>0){
			foreach($_SWDF['template']['header'] as $header){
				header($header);
			}
			unset($header);
		}
	
		//check if caching is enabled
		if ($_SWDF['settings']['enable_view_caching']===false){
			//Caching feature disabled. Just execute the template file
			require $_SWDF['template']['path'];
		} else {
			//Caching feature enabled. Check whether to use cache or regenerate it (or parts of it)
			if ($_SWDF['settings']['regenerate_cache']===false && $_SWDF['settings']['cache_level']==="template"){
				//Use the cached data. 
				require($_SWDF['info']['cache_file']);
			} else {
				//Some cache data needs regenrating. Check whether it's everything or just the template.
				if ($_SWDF['settings']['cache_level']==="template"){
					//Regnerating everything, so start the Output Buffer now
					ob_start();
					//Execute the template file (which outputs the data)
					require $_SWDF['template']['path'];
					//Store the regenrated data to be cached later
					$_SWDF['___ob_buffer']=ob_get_contents();
				} else {
					//Caching the template is disabled, so generate it now, then it will load/regenerate any cached "view data" at the appropriate point. 
					require $_SWDF['template']['path'];
				}
			}

			//Save cached data
			if ($_SWDF['settings']['do_not_cache']!==true && $_SWDF['settings']['regenerate_cache']===true){
				$fp = fopen($_SWDF['info']['cache_file'], 'w');
				fwrite($fp, $_SWDF['___ob_buffer']);
				fclose($fp);
			}

			//Clear the output buffer
			ob_end_flush();
		}
	} else {
		trigger_error("No theme/view selected. Nothing to output.",E_USER_ERROR);
	}
?>
