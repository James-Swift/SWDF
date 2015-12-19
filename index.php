<?php

	/////////////////////////////////////////////////////////////////////////////////
	//                                                                             //
	//                      Swift Web Development Framework                        //
	//                       Copyright 2015 - James Swift                          //
	//																		       //
	//								     v0.2.1								       //
	//                                                                             //
	//						See LICENSE for copyright terms					       //
	//                                                                             //
	/////////////////////////////////////////////////////////////////////////////////
	//                                                                             //
	//         To allow for future updates please do not modify this file          //
	//                   or files in directory "controller/".                      //
	//                                                                             //
	//           This framework has only two reserved variable names:              //
	//                                                                             //
	//      $_SWDF <- A non-persistent, multi-dimensional associative array	       //
	//                                                                             //
	//		   You are free to read/write to it, but be careful not to			   //
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
	$_SWDF=[];
	
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

		//Send any template-related HTTP-Header Information:
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
			
		//Caching feature enabled. 
		} else {
			
			//Check whether to use or regenerate cache (or parts of it)
			if ($_SWDF['settings']['regenerate_cache']===false && $_SWDF['settings']['cache_level']==="template"){
				
				//Use the cached data. 
				require($_SWDF['info']['cache_file']);
				
			//Some cache data needs regenrating.
			} else {
				
				//Are we planning to cache the template as well as the content?
				if ($_SWDF['settings']['cache_level']==="template"){
					
					//Regnerating everything, so start the Output Buffer now
					ob_start();
					
					//Execute the template file (which outputs the data)
					require $_SWDF['template']['path'];
					
					//Store the regenrated data to be cached later
					$_SWDF['___ob_buffer']=ob_get_contents();
					
					
				//No. Don't cache the template
				} else {
					
					//Generate the template now. It will load/regenerate any cached "view data" at the appropriate point. 
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