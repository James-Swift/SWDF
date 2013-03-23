<?php
	//Predefined views used for handling resources
	

	//Images
	$_SWDF['views']['_img']=Array(
		"name"=>"_img",
		"template"=>"empty",
		"model_includes"=>Array(
						$_SWDF['paths']['root']."settings/images.php", 
						$_SWDF['paths']['root']."controller/required_models/image.php"
		),
		"body_includes"=>Array($_SWDF['paths']['root']."controller/required_views/_img.php"),
	);

	//CSS
	$_SWDF['views']['_css']=Array(
		"name"=>"_css",
		"template"=>"empty",
		"body_includes"=>Array($_SWDF['paths']['root']."controller/required_views/_css.php"),
	);
	
	//Javascript
	$_SWDF['views']['_js']=Array(
		"name"=>"_js",
		"template"=>"empty",
		"body_includes"=>Array($_SWDF['paths']['root']."controller/required_views/_js.php"),
	);	

	//Process Login
	$_SWDF['views']['_process_login']=Array(
		"name"=>"_process_login",
		"template"=>"empty",
		"body_includes"=>Array($_SWDF['paths']['root']."controller/required_views/_process_login.php"),
	    );	
		
	//Keep record of required views (used to avoid saving them as a login redirect)
	foreach($_SWDF['views'] as $rv){
		$_SWDF['info']['controller_views'][]=$rv['name'];
	}
	unset($rv);
?>