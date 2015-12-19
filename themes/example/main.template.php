<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php
			//Include any php header files
			SWDF_head_includes();
			
			//Output Page Title
			print "\n\t\t<title>".$_SWDF['template_data']['head_title']."</title>\n";

			//Print Meta tags
			SWDF_print_meta_tags();

			//Include Style Sheets
			SWDF_print_css_tags();	
			
			//Include Javascript Scripts
			SWDF_print_js_tags();	
			
			//Print timestamp onto page for refference
			print "\t\t<!--- Generated: ".date("c")." (".date_default_timezone_get().") -->\n";
			
		?> 
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Content-Language" content="en" />
		<base href="<?php print $_SWDF['info']['website_full_url']; ?>" />
	</head>
	<body>
		<div id="header">
			<h1><?php print get_text("_none","website_h1"); ?></h1>
			<ul>
				<?php
					//Auto-generate a list of views
					if (is_array($_SWDF['views'])){
						foreach ($_SWDF['views'] as $view => $options){
							if (isset($options['menu_item']) && $options['menu_item']===true){
								print "<li>".make_link($view,NULL,NULL,true)."</li>";
							}
						}
					}
				?> 
			</ul>
		</div>
		<hr/>
		<div id="content">
			<?php
				//Include main content file(s)
				SWDF_body_includes();
			?> 
		</div>
		<hr/>
		<div id="footer">
			<ul>
				<li>Copyright James Swift - 2015</li>
				<li><a target="_new" href="http://validator.w3.org/check?uri=referer">Valid xHTML</a></li>
				<li><?php print "This page took ".round((microtime(true)-$_SWDF['info']['execution_start'])*1000)." milliseconds to generate."; ?></li>
			</ul>

		</div>
		<?php 
			//Print code execution time for refference
			print "\n\t\t<!--- This page took ".round((microtime(true)-$_SWDF['info']['execution_start'])*1000)." ms to generate. ".round(($_SWDF['info']['controller_completed']-$_SWDF['info']['execution_start'])*1000)." ms running controller, ".round((microtime(true)-$_SWDF['info']['controller_completed'])*1000)." ms generating page. -->\n";
		?> 
	</body>
</html>