<?php


	//Useful variables for use throughout this script. Do not alter.
	$theme_name=basename(dirname(__FILE__)); //<- automatically taken from folder name
	$theme_path=str_replace("\\","/",dirname(__FILE__))."/";
	$views_path=str_replace("\\","/",dirname(__FILE__))."/views/";
	
	##################################################################################################################
	/*
	 This is the example theme for SWDF. It gives an example of the bare-minimum code that should be written
	 for future themes. Create a copy of this folder rather than modifying files within it when creating a
	 new theme so as to have a refference.
	
	 The following files must be in pace for a theme to work:
	
	 ./_settings.php			<- Contains all settings for theme
	 ./main.template.php 		<- the default page template
	 ./empty.template.php		<- A template that just passes through data and adds no additional output
	 ./views/					<- this folder should contain all the actual theme views/pages
	
	*/

	###### Theme settings:
	
	$_SWDF['themes'][$theme_name]=Array(
		"name"=>$theme_name,					// <- Must match array key and must be the same name as the direcotry which contains this theme
		"path"=>$theme_path,					// <- An absolute path to this directory
		"default_view"=>"home",					// If the user doesn't request a view, the view specified here will be displayed.
		"default_template"=>"main"				// The default template for views. Saves having to specify for each settings below.
	);
	
	
	###### Theme Templates:
	
	/* Possible Theme Template Settings: ( * indicates required )
		"name"		string	*	The name of this tempalte, Must match array key.
		"path"		string	*	The absolute path to the template file
		"css"		array		An array of CSS files to be referenced inside the html head section (absolute system path or url)
		"js"		array		An array of JavaScript files to be referenced inside the html head section (absolute system path or url) 
		"header"	array		An array of data to be sent in the HTTP-HEADERS (such as 'Content-Type: text/html; charset=utf-8')
	 */

	$_SWDF['templates']['main']=[
		"name"=>"main",
		"path"=>$theme_path."main.template.php",
		"css"=>Array($theme_path."main.template.css"),
		"js"=>Array($theme_path."main.template.js"),
		"header"=>Array('Content-Type: text/html; charset=utf-8'),
	];
	
	$_SWDF['templates']['empty']=[
		"name"=>"empty",
		"path"=>$theme_path."empty.template.php"
	];
	
	$_SWDF['templates']['blank_xhtml']=[
		"name"=>"blank_xhtml",
		"path"=>$theme_path."blank_xhtml.template.php",
		"header"=>Array('Content-Type: text/html; charset=utf-8'),
	];
	

	
	
	###### Views/Pages within this theme:	
	
	/* Views/pages must be explicitly defined in this file.
	Possible settings: ( * denotes required )
	"name"					string	*	Name your view here (this name will be used in urls such as "example.com/?p=home"). Must match array key.
	"template"				string		The template (defined above) to be used. Default "main".
	"head_title"			string		The string to be displayed in the html <title></title> tags. 
	"gui_name"				string		The view name as it should be displayed to users (such as in the menu)
	"menu_item"				boolean	  	Whether this view should be included in the main menu
	"menu_parent"			string	  	If this item is a child of a "menu_item", specify it's parents id here. (Can be used to automatically arrange pages into menus/sub-menus).
	"model_includes"		array 		An array of model files to include (included prior to template) (absolute paths)
	"head_includes"			array		An array of php files to include inside the html head section (absolute paths)
	"body_includes"			array	*	An array of php files to include inside the html body section - THE MAIN FILE (absolute paths)
	"js"					array		An array of javascript files to be called inside the html head section (absolute system path or url) 
	"css"					array		An array of CSS files to be referenced inside the html head section (absolute system path or url)
	"meta_tags"				array		An array of meta tags to be outputted. E.G. Array("keywords"=>"some,example,keywords", "description"=>"example description")
	"require_user_settings"	array		An array of fields which must match boolean true against user's DB row for view to be displayed
	"deny_user_settings"	array		An array of fields which must match boolean false against user's DB row for view to be displayed
	"require_settings"		array		An array of variables which must match boolean true against $_SWDF for view to be displayed. E.G. Array("user_logged_in")
	"deny_settings"			array		An array of variables which must match boolean false against $_SWDF for view to be displayed. E.G. Array("cookies_enabled")
	"cache"					mixed		Which chaching method to use. Possible value: false,"loose","exact". Default is false.
											false			Disables caching for this page.
											"loose"			Effected by "require_user_settings"/"require_settings" etc. checks, but will display the same content regardless of passed variables or user settings.	Example use: index.php?p=aboutus
											"exact"			Warning, may generate a large number of files. It creates a new cache file for each combination of the variables set in "cache_variables". Slower than "loose". Example use: index.php?p=blog&id=123&blog=jamie
	"cache_level"			string		The level at which to cache. Can be "template" or "view". Default is $_SWDF['settings']['default_cache_level']. If set to view, the template will be regenerated for each page load (useful when the template is dynamic and displayes info like a shopping basket) but the actual content won't be, can be significantly slower as model/header files for the current view will be execute.
	"cache_variables"		array		If cache type "exact" is used, a cache file will be generated for each combination of the variables in this array. "p", is automatically handled, do not include.
	"cache_expiry"			int			Number of seconds to keep cache files for. Overides default setting.
	"cache_file_limit"		int			Limit the number of cache files that can be generated for this page (with combinations of cache_variables). Overides $_SWDF['settings']['default_cache_file_limit'];.
	*/
	
	/*
	$_SWDF['settings']['do_not_cache'] 
	A quick note on caching. Caching is not suitable for pages modified for each user (like a "my account" page).
	Also, to prevent users generating large numbers of cache files by passing infinate combinations of the cache_variables, 
	use $_SWDF['settings']['do_not_cache']=true; on pages you don't want to be cached (such as a "blog post not found" page).
	Simply set that variable true anywhere on any of your pages and a cache file won't be generated. It is advisable
	to do this on all "not found" pages, as they are the most likely to generate useless cache files.
	*/
	
	

	//home (default view)
	$_SWDF['views']['home']=Array(
		"name"=>"home",
		"gui_name"=>get_text('home','gui_name'),
		"head_title"=>get_text('home','gui_name')." | ".get_text('_none','website_title'),
		"menu_item"=>true,
		"body_includes"=>Array($views_path."home/home.php"),	
		"css"=>Array($views_path."home/home.css"),
		"js"=>Array($views_path."home/home.js"),
	);
	
	//Login
	$_SWDF['views']['login']=Array(
		"name"=>"login",
		"gui_name"=>get_text('login','gui_name'),
		"head_title"=>get_text('login','gui_name')." | ".get_text('_none','website_title'),
		"menu_item"=>true,
		"body_includes"=>Array($views_path."login/login.php"),	
		"css"=>Array($views_path."login/login.css")
	    );	
	
	//403 - Access Denied
	$_SWDF['views']['403']=Array(
		"name"=>"403",
		"head_title"=>"Access Denied | ".get_text('_none','website_title'),
		"body_includes"=>Array($views_path."error/403.php")	
	);

	//404 - File Not Found
	$_SWDF['views']['404']=Array(
		"name"=>"404",
		"head_title"=>"File Not Found | ".get_text('_none','website_title'),
		"body_includes"=>Array($views_path."error/404.php")	
	);
	
	
	
	
	
	/////////////////////////////////////////////////////////
	//clean up variabels
	unset($theme_name,$theme_path,$views_path);