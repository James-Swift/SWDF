<?php
/*
 * Main Website Settings File
 * 
 * Use this file to configure your website. It comes with many pre-defined settings, but as with all
 * SWDF files, it's designed to be fully extensible - so feel free to add in your own settings which
 * you can referrence in your own code.
 * 
 * The reason the settings are stored in a php file instead of an ini or database table is to allow 
 * settings to be altered on the fly. For example, you could use conditional statements to alter 
 * settings depeding on whether $_SWDF['info']['user_logged_in'] is true, or you could alter the website's 
 * theme dependant on a setting stored in $_SESSION['_SWDF'] or what $_SWDF['info']['current_view'] is. You'll find 
 * it gives you great freedom. Experiment!
 * 
 * Most of the SWDF settings have been placed in this one file, but to make organizing the settings easier
 * some have been placed in other files within this directory. When making settings files to control
 * your own functions, I reccommend you place them in this directory as well to make things simpler.
 * If your settings files will be required by every view just include them from this script (see the end 
 * of this file). If your settings files will only be needed by a few views, it is best to only include 
 * them only on those specific files (you can automate that through each theme's main "_settings.php" file).
 */
	
///////////////////////////////////////////////////////////////////////////
// Start of Settings:
///////////////////////////////////////////////////////////////////////////
	
/* View Variable Name	-	 (might as well start with the most complicated)
 * 
 * IMPORTANT!! Changing the VVN setting will radically alter (and/or break) the way your website works!
 * 
 * What you define will control how the SWDF detects what view has been requested. For example:
 * 
 * If you set it to $_SWDF['settings']['vvn']="p" and you wanted to request the "home" page. You might have a 
 * link like this:  http://www.mysite.com/?p=home
 * 
 * If you set it to $_SWDF['settings']['vvn']="view" and you wanted to request the "home" page. You might have a 
 * link like this:  http://www.mysite.com/?view=home
 * 
 * If you set it to $_SWDF['settings']['vvn']="obscure_name" and you wanted to request the "links" page. You 
 * might have a link like this:  http://www.mysite.com/?obscure_name=links
 * 
 * As a rule of thumb, this should be the first things you decide on when you make your website to
 * avoid confusion later on. If your website will be integrating with other libraries/frameworks decide
 * whether it should have the same Variable Name as them, or a different one so as not to conflict. If 
 * you are importing your current website into the SWDF, you will want to set it to whatever you used
 * to use to pass the requested view id (that way, people's links to your site will still work properly).
 * 
 * When you come to write code for your site, you have two choices. You can either decide on a VVN now
 * and hard code it into all of your links, or you can use the actual variable in your code and give 
 * yourself the ability to change it later if you wish. For most sites, I reccommend the first, as it is 
 * the simplest method. Plus the chances of you ever wanting to change it are so slim it's not worth all
 * the extra effort it would take to use the actual variable.
 * 
 * But if you think there is a possibility you might have to integrate your site into another later in
 * it's life, you'll thank yourself for doing integrating it from the word go, as it will avoid major 
 * headaches further down the line, trust me!
 * 
 * If you choose to use the actual variable, to make the process a little easier there is a function
 * for crating links, (it can actually be easier than hand-writing a link sometimes):
 *   make_link(VIEW, [VARIABLES, USE_FULL_URL?, OUTPUT_AS_HTML_A_TAG?])
 * It's quite inteligant when handling variables (properly escaping etc.) You can call it like this:
 *	make_link("blog","id=21&project=picture bute");							//outputs something like: "?p=blog&id=21&project=picture bute" (assuming your VVN is "p")
 * 	make_link("blog",array("id"=>21, "project"=>$my_project_var));			//outputs something like: "?p=blog&id=21&project=picture%20bute"
 * 	make_link("blog",array("id"=>$id, "project"=>$my_project_var), true);	//outputs something like: "http://mysite.com/?p=blog&id=21&project=picture%20bute"
 * 	make_link("blog",$variables_array, true, true);							//outputs something like: "<a href="http://mysite.com/?p=blog&id=21&project=picture%20bute">Blog</a>"
 * 
 * If you prefer, you can also write links by hand like this: 
 *	<?php print "index.php?".$_SWDF['settings']['vvn']."=blog&id=21&project=picture%20"; ?>
 * 
 * If you ever need to refference the currently selected view, it is stored in:
 * $_SWDF['info']['current_view']
 * 
 * My advice is, think carefully before making any changes to $_SWDF['settings']['vvn'] and you'll be fine.
 * If you're not sure what to do, just leave it as is. It will work fine!
 * 
 * The urls used by the SWDF when generating links are also specified below. Generally you can leave them as
 * is. But sometimes you might want to hard-code them.
 */
	$_SWDF['settings']['vvn']="p";
	
	$_SWDF['info']['website_url']=$_SERVER['HTTP_HOST'].$_SWDF['paths']['web_root'];
	$_SWDF['info']['website_full_url']="http://".$_SERVER['HTTP_HOST'].$_SWDF['paths']['web_root'];		
	
	
/*
 * The SWDF supports internationalization (i18n for short) out-of-the-can. It does so in a way that
 * doesn't make your website any more difficult to configure or code. Most sites store their text
 * (also known as copy) seperate from the html markup to allow the site owners / webmasters to edit 
 * it easily without trawling through html code. The SWDF has a simple function to semi-automate
 * this, (which also combines i18n): get_text(VIEW_ID,TEXT_ID,[LANGUAGE])
 * 
 * Anytime you come to insert text into your markup, simply use the get_text() function. You 
 * generaly don't need to specify what language you want as the function detects it automatically
 * based on the setting in $_SESSION['_SWDF']['settings']['language']. If you only use one language on your site, just
 * forget the option is even there.
 * 
 * Example: let's say you want to insert a brief description onto an "about us" page:
 * 
 * <?php print get_text("about_us","brief_intro"); ?>
 * 
 * Then you go to the admin page Text Editor and write your copy into there. Other admins can easily
 * edit it too without having to know anything about writing code, simply by visiting the admin page.
 * 
 * If your website has multiple languages, or if you didn't in the past but want to now, you don't
 * have to change your code at all. Al you have to do is ask your translators to log into the admin
 * page and translate the copy that is already there into another language for you. Then, when a user
 * visits a page, the appropriate translation of the copy is displayed depending on their language 
 * selection without any extra work from you. The system can even automatically mark other language 
 * versions of copy when one translation is altered, so your translation team can self manage.
 * 
 * Following are the i18n settings:
 */
	$_SWDF['settings']['default_language']="en";									//The default language for the users visiting the site. Normally "en".
	$_SWDF['info']['available_languages']=array("en");								//An array of the languages supported by the website
	$_SWDF['paths']['text']=$_SWDF['paths']['root']."data/text/";					//Where to store i18n text data (if using file-system storage method).

	
/* Data-Base Settings 
 * 
 * You can tell the SWDF whether or not to initialize a database connection. In all but the most 
 * basic of websites, you probably will want to though, as functions like the user system rely on a 
 * database. But if you can get by without one, your website will gain a few ms extra speed per page
 * load.
 * 
 * Set up the primary database connection below. All SWDF tables must be inside this database, however
 * you can of course manually connect to other databases if you need to when writing your site using the
 * SWDF_DB class, or even just the plain old mysql functions. The SWDF will only initialize this one
 * connection by itself. It will be initialized with the name: $db 
 * 
 * The SWDF_DB class is an extension of the PDO class, which adds some useful functions like:
 *	select(WHAT,FROM_WHERE,WHERE) and set(TABLE,VARIABLES,WHERE)
 * among many others, which make writing code very quick and easy. To initialize a new connection
 * in your own code to a database other than the main one, do this:
 * 
 		try {
			$my_db_connection=new SWDF_DB(
				"mysql:dbname=my_database;host=mysite.com",
				"username",
				"password"		
			);
		} catch (PDOException $e) {
			die("Error. Couldn't establish database connection.");
		} 
  * 
 * If for some reason you need to rename one of the SWDF DB tables (perhaps a conflict with your own 
 * setup), you can easily do so by altering the $_SWDF['settings']['db']['tables'] array as all tables are
 * called by refference to that array, specifically to allow you to change their name to whatever
 * you wish.
 */	
	$_SWDF['settings']['use_db']=true;								// Whether to initiate a PDO db connection (required if site has a user system)
	$_SWDF['settings']['use_db_for_session']=true;					// Whether to store session data in the db (as opposed to file-system as default). Requires $_SWDF['settings']['use_db'] be set to true.
	$_SWDF['settings']['use_db_for_text']=false;					// Whether to use the DB for storing data with get_text(), set_text() etc. instead of the file system. Both are equally supported. Requires $_SWDF['settings']['use_db'] be set to true.
	$_SWDF['settings']['use_db_for_log']=true;						// Whether to write the results of log() to the DB. Requires $_SWDF['settings']['use_db'] be set to true. If either is false, the log will be stored in the filesytem under $_SWDF['paths']['logs']

	//Setup the primary $db connection here:
	$_SWDF['settings']['db']['driver']="mysql";						//The driver to load for your database. See the PDO docs for more information.
	$_SWDF['settings']['db']['host']="localhost";					//The address/IP of your database server
	$_SWDF['settings']['db']['username']="root";					//Your database username
	$_SWDF['settings']['db']['password']="";						//Your database password
	$_SWDF['settings']['db']['database']="swdf";					//Which database to use
	
	$_SWDF['settings']['unset_db_password_after_use']=true;			//If set to true, once the controller has initiated the main $db connection, it will then unset $_SWDF['settings']['db']['password'] to avoid it being accidentatly revealed later in the script
	
	$_SWDF['settings']['db']['tables']['users']="users";			//The name of the table used to store user info.
	$_SWDF['settings']['db']['tables']['text']="text";				//The name of the table used to store i18n text strings (for functions get_text() and set_text() )
	$_SWDF['settings']['db']['tables']['sessions']="sessions";		//The name of the table to store session data in (if $_SWDF['settings']['use_db_for_session'] is true)
	$_SWDF['settings']['db']['tables']['log']="log";				//The name of the table used to store log entries (if $_SWDF['settings']['use_db_for_log'] is true)
	
	
/* Themes, Templates and views
 * 
 * Simple settings for themes are below. For more advanced options see the "_settings.php" file inside
 * each theme's directory which handles settings for templates and individual views. If you wish to 
 * add a new view, you must specifiy it in the theme's _settings.php file, not this file.
 * e.g. themes/main/_settings.php
 */
	$_SWDF['paths']['themes']=$_SWDF['paths']['root']."themes/";			//Absolute path to the directory the website's themes are stored in. Default: $_SWDF['paths']['root']."themes/"
	$_SWDF['settings']['use_theme']="example";								//The theme to be used. (See themes/THEME_NAME/_settings.php for more theme related settings)
	
	
/* Logging
 * 
 * The SWDF has an inbuilt logging system which you may find useful to use in your own code. You simply
 * call it with: log_event(TITLE, DESCRIPTION, TYPE, IMPORTANCE, [DATA1, DATA2, DATA3, DATA4, DATA5])
 * Type can specify what kind of event you are logging. Importance is a value between 0 and 10 (10 being
 * the most important/urgent). Tha DATA1,2,3,4,5 variables are for you to dump JSON data into for referrence
 * when reading the log back later, particularly when this is an automated proccess.
 */
	$_SWDF['paths']['logs']=$_SWDF['paths']['root']."data/logs/";		//Absolute path to the directory the log files should be stored in, if $_SWDF['settings']['use_db_for_log'] is false.
	$_SWDF['settings']['log_only_from_importance']=0;					//Tells the logging system to only store results at or above a certain significance/importance. 0=keep all. It can be useful while developing a website to place log points all over the place with low importances, then when the website is in production, hide them from the log by changing this one settings
	$_SWDF['settings']['log_unimportant_expiry']=60*60*24*1;			//How long before items deemed unimportant should be removed from the log (in seconds). 0=never remove.
	$_SWDF['settings']['log_unimportant_level']=5;						//The importance level at and below which log entries are deemed unimportant (and removed after $_SWDF['settings']['log_unimportant_expiry'] seconds). Normally higher than $_SWDF['settings']['log_only_from_importance'], otherwise will have no effect.
	$_SWDF['settings']['log_expiry']=60*60*24*7;						//How long (in seconds) before items should be removed from the log regardless of importance. 0=never remove.
	
		
/* Caching/Compressing
 * 
 * The SWDF can handle caching of pages and images for you, and is quite configurable in how it
 * does this. Settings for caching views are below. Settings for caching/resizing images are in
 * settings/images.php
 */
	$_SWDF['settings']['compress_output']=true;									//Whether to use zlib compression for php output

	$_SWDF['paths']['views_cache']=$_SWDF['paths']['root']."cache/views/";		//Location to cached view files.	
	$_SWDF['settings']['enable_view_caching']=false;							//Enables/disables the view caching system. By default, no views are cached even when this is set to true. Views must be explicitly opted-in to be cached. See themes/theme_name/_settings.php to opt views in.
	$_SWDF['settings']['default_cache_level']="template";						//The default point in your code at which to cache output. Can be "template" or "view". If set to template, all output will be cached. If set to "view", the page template will be regenerated on each page load (useful when the template is dynamic and displayes info like a shopping basket) but the actual content won't be, can be significantly slower as model/header files for the current view will be executed.
	$_SWDF['settings']['default_cache_expiry']=1800;							//How long (in seconds) to use a cache file for before regenerating. This is the default value, can be overwritten on a view-by-view basis.
	$_SWDF['settings']['default_cache_file_limit']=20;							//Maximum number of cache file versions each view can have. (Used when cache type is set to "exact"). See themes/theme_name/_settings.php for explainations


/* Include additional setttings files
 * 
 * I try to write code so that the way it works can easily be altered by changing one setting,
 * (hence all the above settings). If you write your own settings files, you can include them below.
 * If the settings files are only required for one or two particular pages though, I recommend you
 * don't include them here (as they will interpreted with every page load), but rather include
 * them only when needed.
 */
	require("security.php");							//Settings related to website security
	require("sanitization.php");						//Settings related to data Sanitization

?>