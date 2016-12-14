<?php

// =======================
// WORDQUEST HELPER PLUGIN
// =======================

// Requires PHP 5.3 (for anonymous function usage)
// (otherwise helper library loads nothing)

// Usage Note: To Adjust WordQuest Admin Menu Position
// ---------------------------------------------------
// (example user override for use in Child Theme functions.php or /wp-content/mu-plugins/)
// note: this filter is called in wordquest plugins - not in this helper
// if (!has_filter('wordquest_menu_position','custom_wordquest_menu_position')) {
//	add_filter('wordquest_menu_position','custom_wordquest_menu_position');
// }
// if (!function_exists('custom_wordquest_menu_position')) {
//  function custom_wordquest_menu_position() {
//		return '10'; // numeric menu priority, defaults to 3
//	}
// }

// ================
// HELPER CHANGELOG
// ================

// -- 1.6.0 --
// - use variable function names
// - change function prefix to wqhelper
// - text link forms for donations

// -- 1.5.0 --
// - added version checking/loading
// - added global admin page
// - added admin styles/scripts
// - added subscriber levels
// - further wordquest conversions
// - added freemius submenu styling
// - split feed load metaxboxes
// - added feed transient storage
// - added admin notice boxer
// - sidebar options to single array
// - AJAXify some helper actions

// -- 1.4.0 --
// - change to wordquest.org
// - updated donation amounts

// -- 1.3.0 --
// - added recurring donations
// - user email populate bonus form

// -- 1.2.0 --
// - added bonus report

// TODO: collapse/expand buttons for sidebar?
// TODO: replace floating menu with sticky kit?



// START CODE - PHP 5.3 MINIMUM REQUIRED FOR ANONYMOUS FUNCTIONS
// -------------------------------------------------------------
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {

// Set this Wordquest Helper Plugin version
// ----------------------------------------
// 1.6.0: wqv to wqhv for new variable functions
$wordquestversion = '1.6.0';
$wqhv = str_replace('.','',$wordquestversion);

// =================================
// Version Handling Loader Functions
// =================================
// ...future proofing helper update library...

// Add to global array of Wordquest versions
// -----------------------------------------
// 1.6.0: change globals to use new variable functions (as not backcompatible!)
global $wordquesthelpers, $wqfunctions;
if (!is_array($wordquesthelpers)) {$wordquesthelpers = array($wqhv);}
elseif (!in_array($wqhv,$wordquesthelpers)) {$wordquesthelpers[] = $wqhv;}

// Set Latest Wordquest Version on Admin Load
// ------------------------------------------
// 1.5.0: use admin_init not plugins_loaded so usable by themes
if (!has_action('admin_init','wqhelper_admin_loader',1)) {add_action('admin_init','wqhelper_admin_loader',1);}

if (!function_exists('wqhelper_admin_loader')) {
 function wqhelper_admin_loader() {
 	// 1.6.0: maybe remove the pre 1.6.0 load action?
 	// if (has_action('admin_init','wordquest_admin_load')) {remove_action('admin_init','wordquest_admin_load');}

 	// 1.6.0: new globals used for new method
 	global $wordquesthelper, $wordquesthelpers;
 	$wordquesthelper = max($wordquesthelpers);
 	// echo "<!-- WHQV: ".$wordquesthelper." -->"; // debug point

	// 1.6.0: set the function caller helper
	global $wqcaller, $wqfunctions;
	$vfunctionname = 'wqhelper_caller_';
	$vfunc = $vfunctionname.$wordquesthelper;

	if (is_callable($wqfunctions[$vfunc])) {
		$wqfunctions[$vfunc]($vfunctionname); // $wqcaller = $wqfunctions[$vfunctionname];
	} elseif (function_exists($vfunc)) {call_user_func($vfunc,$vfunctionname);}
	// echo "<!-- WQ CALLER: "; print_r($wqcaller); echo " -->";

 	// 1.5.0: set up any admin notices via helper version
 	// 1.6.0: ...use caller function directly for this
 	$vadminnotices = 'wqhelper_admin_notices';
 	if (is_callable($wqcaller)) {$wqcaller($vadminnotices);}
 	elseif (function_exists($vadminnotices)) {call_user_func($vadminnotices);}

 }
}

// Function to Define Function Caller
// ----------------------------------
// 1.6.0: some lovely double abstraction here!
$vfuncname = 'wqhelper_caller_'.$wqhv;
if (!is_callable($wqfunctions[$vfuncname])) {
	$wqfunctions[$vfuncname] = function($vfunc) {
		global $wqfunctions, $wqcaller;
		if (!is_callable($wqcaller)) {
			$wqcaller = function($vfunction,$vargs = null) {
				global $wordquesthelper, $wqfunctions;
				$vfunc = $vfunction.'_'.$wordquesthelper;
				if (is_callable($wqfunctions[$vfunc])) {return $wqfunctions[$vfunc]($vargs);}
				elseif (function_exists($vfunc)) {return call_user_func($vfunc,$vargs);}
			};
		}
	};
}


// Call to Versioned Admin Page Functions
// --------------------------------------
if (!function_exists('wqhelper_admin_page')) {function wqhelper_admin_page($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
// admin notice boxer
if (!function_exists('wqhelper_admin_notice_boxer')) {function wqhelper_admin_notice_boxer($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
// get plugins info
if (!function_exists('wqhelper_get_plugin_info')) {function wqhelper_get_plugin_info($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
// admin page plugins column
if (!function_exists('wqhelper_admin_plugins_column')) {function wqhelper_admin_plugins_column($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
// admin page feeds column
if (!function_exists('wqhelper_admin_feeds_column')) {function wqhelper_admin_feeds_column($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// Sidebar Floatbox Caller Functions
// ---------------------------------
// wqhelper_sidebar_floatbox
// wqhelper_sidebar_paypal_donations
// wqhelper_sidebar_testimonial_box
// wqhelper_sidebar_floatmenuscript

if (!function_exists('wqhelper_sidebar_floatbox')) {function wqhelper_sidebar_floatbox($vargs = null) {
	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_sidebar_paypal_donations')) {function wqhelper_sidebar_paypal_donations($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_sidebar_testimonial_box')) {function wqhelper_sidebar_testimonial_box($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_sidebar_floatmenuscript')) {function wqhelper_sidebar_floatmenuscript($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// Dashboard Feed Caller Functions
// -------------------------------
// wqhelper_add_dashboard_feed_widget
// wqhelper_dashboard_feed_widget
// wqhelper_process_rss_feed
// wqhelper_load_category_feed

if (!function_exists('wqhelper_add_dashboard_feed_widget')) {function wqhelper_add_dashboard_feed_widget($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_dashboard_feed_javascript')) {function wqhelper_dashboard_feed_javascript($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_dashboard_feed_widget')) {function wqhelper_dashboard_feed_widget($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_pluginreview_feed_widget')) {function wqhelper_pluginreview_feed_widget($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
if (!function_exists('wqhelper_process_rss_feed')) {function wqhelper_process_rss_feed($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }
// if (!function_exists('wqhelper_load_category_feed')) {function wqhelper_load_category_feed($vargs = null) {
// 	if (!is_admin()) {return;} global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }


// ------------------
// Styles and Scripts
// ------------------

// Add Wordquest Styles to Admin Footer
// ------------------------------------
if (!has_action('admin_footer','wqhelper_admin_styles')) {add_action('admin_footer','wqhelper_admin_styles');}
if (!function_exists('wqhelper_admin_styles')) {function wqhelper_admin_styles($vargs = null) {
	remove_action('admin_footer','wordquest_admin_styles');
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// Add Wordquest Scripts to Admin Footer
// -------------------------------------
if (!has_action('admin_footer','wqhelper_admin_scripts')) {add_action('admin_footer','wqhelper_admin_scripts');}
if (!function_exists('wqhelper_admin_scripts')) {function wqhelper_admin_scripts($vargs = null) {
	remove_action('admin_footer','wordquest_admin_scripts');
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// --------------
// AJAX Functions
// --------------

// AJAX for WordQuest Plugin Install
if (!has_action('wp_ajax_wqhelper_install_plugin','wqhelper_install_plugin')) {
	add_action('wp_ajax_wqhelper_install_plugin','wqhelper_install_plugin');
}
if (!function_exists('wqhelper_install_plugin')) {function wqhelper_install_plugin($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// AJAX Load Category Feed
if (!has_action('wp_ajax_wqhelper_load_feed_cat','wqhelper_load_feed_category')) {
	add_action('wp_ajax_wqhelper_load_feed_cat','wqhelper_load_feed_category');
}
if (!function_exists('wqhelper_load_feed_category')) {function wqhelper_load_feed_category($vargs = null) {
 	global $wqcaller; return $wqcaller(__FUNCTION__,$vargs);} }

// AJAX Update Sidebar Options
// 1.6.0: ! caller exception ! use matching form version function here just in case...
if (!has_action('wp_ajax_wqhelper_update_sidebar_options','wqhelper_update_sidebar_options')) {
	add_action('wp_ajax_wqhelper_update_sidebar_options','wqhelper_update_sidebar_options');
}
if (!function_exists('wqhelper_update_sidebar_options')) {
 function wqhelper_update_sidebar_options() {
 	if (!isset($_POST['wqhv'])) {return;} else {$wqhv = $_POST['wqhv'];}
 	$vfunc = 'wqhelper_update_sidebar_options_'.$wqhv;
 	if (is_callable($vfunc)) {return $vfunc;}
 	elseif (function_exists($vfunc)) {return call_user_func($vfunc);}
 }
}

// ==========================
// Version Specific Functions
// ==========================
// (functions below this point must be suffixed with _{VERSION} to work
// and update with each plugin helper version regardless of change state)


// Admin Notice Boxer
// ------------------
// (for settings pages)
$vfuncname = 'wqhelper_admin_notice_boxer_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
	$wqfunctions[$vfuncname] = function() {

	// count admin notices
	global $wp_filter; $vadminnotices = 0; // print_r($wp_filter);
	if (isset($wp_filter['admin_notices'])) {$vadminnotices = count($wp_filter['admin_notices']);}
	if (is_network_admin()) {if (isset($wp_filter['network_admin_notices'])) {$vadminnotices = $vadminnotices + count($wp_filter['network_admin_notices']);} }
	if (is_user_admin()) {if (isset($wp_filter['user_admin_notices'])) {$vadminnotices = $vadminnotices + count($wp_filter['user_admin_notices']);} }
	if (isset($wp_filter['all_admin_notices'])) {$vadminnotices = $vadminnotices + count($wp_filter['all_admin_notices']);}
	if ($vadminnotices == 0) {return;}

	echo "<script>function togglenoticebox() {divid = 'adminnoticewrap'; ";
	echo "if (document.getElementById(divid).style.display == '') {document.getElementById(divid).style.display = 'none';} ";
	echo "else {document.getElementById(divid).style.display = '';} } ";
	// straight from /wp-admin/js/common.js... to move the notices if common.js is not loaded...
	echo "jQuery(document).ready(function() {jQuery( 'div.updated, div.error, div.notice' ).not( '.inline, .below-h2' ).insertAfter( jQuery( '.wrap h1, .wrap h2' ).first() ); });";
	echo "</script>";

	echo '<div style="width:75%" id="adminnoticebox" class="postbox">';
	echo '<h3 class="hndle" style="margin-left:20px;" onclick="togglenoticebox();"><span>&#9660; Admin Notices ('.$vadminnotices.')</span></h3>';
	echo '<div id="adminnoticewrap" style="display:none";><h2></h2></div></div>';
 };
}

// Plugin Usage Reminder Notices
// -----------------------------
// 1.5.0: prototype, does nothing yet
$vfuncname = 'wqhelper_admin_notices_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
	$wqfunctions[$vfuncname] = function() {
 	global $wordquestplugins;
 	foreach ($wordquestplugins as $wqplugin) {
 		$vpre = $wqplugin['settings'];
 		$sidebaroptions = get_option($vpre.'_sidebar_options');
 		if (isset($sidebaroptions['installdate'])) {
 			$vinstalltime = @strtotime($sidebaroptions['installdate']);
 			$vtimesince = time() - $vinstalltime;
 			if ($vtimesince > (30*24*60*60)) { // 30 day notice
 				if (!isset($sidebaroptions['30daydismissed'])) {
 					// TODO: add a donation/support/go pro reminder notice?

				}
 			}
 			if ($vtimesince > (90*24*60*60)) { // 90 day notice
 				if (!isset($sidebaroptions['90daydismissed'])) {
 					// TODO: add a donation/support/go pro reminder notice?

				}
 			}
 		}
 	}
 };
}

// Get WordQuest Plugins Info
// --------------------------
$vfuncname = 'wqhelper_get_plugin_info_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
	$wqfunctions[$vfuncname] = function() {
	// 1.5.0: get plugin info (maximum twice daily)
	// $vplugininfo = trim(get_transient('wordquest_plugin_info'));
	if ( (!$vplugininfo) || ($vplugininfo == '') || (!is_array($vplugininfo)) ) {
		$vbaseurl = "http://"."wordquest.org";
		$vpluginsurl = $vbaseurl."/?get_plugins_info=yes";
		$vargs = array('timeout' => 10);
		$vplugininfo = wp_remote_get($vpluginsurl,$vargs);
		if (!is_wp_error($vplugininfo)) {
			$vplugininfo = $vplugininfo['body'];
			// print_r($vplugininfo); // debug point
			$vdataend = "*****END DATA*****";
			if (strstr($vplugininfo,$vdataend)) {
				$vpos = strpos($vplugininfo,$vdataend);
				$vplugininfo = substr($vplugininfo,0,$vpos);
				$vplugininfo = json_decode($vplugininfo,true);
				set_transient('wordquest_plugin_info',$vplugininfo,(12*60*60));
				// print_r($vplugininfo); // debug point
			} else {$vplugininfo = '';}
		} else {$vplugininfo = '';}
	} else {$vplugininfo = '';}
	return $vplugininfo;
 };
}

// Version Specific Admin Page
// ---------------------------
$vfuncname = 'wqhelper_admin_page_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
	$wqfunctions[$vfuncname] = function() {

	global $wordquesthelper, $wordquestplugins;

	echo '<div class="wrap">';

	// Call Admin Notice Boxer
	wqhelper_admin_notice_boxer();

	echo "<script>function togglemetabox(divid) {
		var divid = divid+'-inside';
		if (document.getElementById(divid).style.display == '') {
			document.getElementById(divid).style.display = 'none';
		} else {document.getElementById(divid).style.display = '';}
	}</script>";

	echo '<style>#plugincolumn, #feedcolumn {display: inline-block; float:left; margin: 0 20px;}
	#plugincolumn .postbox {max-width:350px;} #feedcolumn .postbox {max-width:350px;}
	#plugincolumn .postbox h2, #feedcolumn .postbox h2 {font-size: 16px; margin-top: 0; background-color: #E0E0EE; padding: 5px;}
	#page-title a {text-decoration:none;} #page-title h2 {color: #3568A9;}
	</style>';

	// Admin Page Title
	// ----------------
	$vwqurl = 'http://'.'wordquest.org';
	$vwordquesticon = plugin_dir_url(__FILE__).'/images/wordquest.png';
	echo '<table><tr><td width="20"></td><td><img src="'.$vwordquesticon.'"></td><td width="20"></td>';
	echo '<td><div id="page-title"><a href="'.$vwqurl.'" target=_blank><h2>WordQuest Alliance</h2></a></div></td>';
	echo '<td width="80"></td><td><h3>&rarr; <a href="'.$vwqurl.'/register/" target=_blank>Join the Alliance</a></h3></td>';
	echo '<td width="50"></td><td><h3>&rarr; <a href="'.$vwqurl.'/login/" target=_blank>Login</a></h3></td>';
	echo '</tr></table>';

	// Output Plugins Column
	// ---------------------
	wqhelper_admin_plugins_column(null);

	// Output Feeds Column
	// -------------------
	wqhelper_admin_feeds_column(null);

	// Wordquest sidebar 'plugin' box
	// ------------------------------
	function wq_sidebar_plugin_footer() {
		echo '<div id="pluginfooter"><div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>Source Info</h3><div class="inside">';
		echo "<center><table><tr>";
		echo "<td><a href='".$vwqurl."/' target='_blank'><img src='".plugin_dir_url(__FILE__)."images/wordquest.png' border=0></a></td></td>";
		echo "<td width='14'></td>";
		echo "<td><a href='".$vwqurl."/' target='_blank'>WordQuest Alliance</a><br>";
		echo "<a href='".$vwqurl."/plugins/' target='_blank'><b>&rarr; WordQuest Plugins</b></a><br>";
		echo "<a href='http://"."pluginreview.net/directory/' target='_blank'>&rarr; Plugin Directory</a></td>";
		echo "</tr></table></center>";
		echo '</div></div></div>';
	}

	// Floating Sidebar
	// ----------------
	// set 'plugin' values for sidebar
	global $wordquestplugins, $wordquesthelper;
	$wordquestplugins['wordquest']['version'] = $wordquesthelper;
	$wordquestplugins['wordquest']['title'] = 'WordQuest Alliance';
	$wordquestplugins['wordquest']['namespace'] = 'wordquest';
	$wordquestplugins['wordquest']['settings'] = 'wq';
	$wordquestplugins['wordquest']['plan'] = 'free';

	// $vargs = array('patsee','wp-bugbot','free','wp-bugbot','special','WP BugBot',$vpatseeversion);
	$vargs = array('wordquest','special'); wqhelper_sidebar_floatbox($vargs);

	echo wqhelper_sidebar_floatmenuscript();

	echo '<script language="javascript" type="text/javascript">
	floatingMenu.add("floatdiv", {targetRight: 10, targetTop: 20, centerX: false, centerY: false});
	function move_upper_right() {
		floatingArray[0].targetTop=20;
		floatingArray[0].targetBottom=undefined;
		floatingArray[0].targetLeft=undefined;
		floatingArray[0].targetRight=10;
		floatingArray[0].centerX=undefined;
		floatingArray[0].centerY=undefined;
	}
	move_upper_right();
	</script></div>';

	// hidden iframe for plugin actions
	echo '<iframe id="pluginactionframe" src="javascript:void(0);" style="display:none;"></iframe>';

	echo '</div>';
 };
}

// Version Specific Plugins Column
// -------------------------------
$vfuncname = 'wqhelper_admin_plugins_column_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
	$wqfunctions[$vfuncname] = function($vargs) {

	global $wordquesthelper, $wordquestplugins;

	// Plugin Action Select Javascript
	// -------------------------------
	// note: some options unused/untested here...
	$vwqurl = 'http://'.'wordquest.org';
	echo "<script>
	function dopluginaction(pluginslug) {
		var selectelement = document.getElementById(pluginslug+'-action');
		var actionvalue = selectelement.options[selectelement.selectedIndex].value;
		var linkelement = document.getElementById(pluginslug+'-link');

		if (actionvalue == 'settings') {linkelement.target = '_self';
			linkelement.href = '".admin_url('admin.php')."?page='+pluginslug;
		}
		if (actionvalue == 'update') {linkelement.target = '_self';
			alert(document.getElementById(pluginslug+'-updatelink').value);
			linkelement.href = document.getElementById(pluginslug+'-updatelink').value;
		}
		if (actionvalue == 'activate') {linkelement.target = '_self';
			alert(document.getElementById(pluginslug+'-activatelink').value);
			linkelement.href = document.getElementById(pluginslug+'-activatelink').value;
		}
		if (actionvalue == 'install') {linkelement.target = '_self';
			linkelement.href = '".admin_url('admin-ajax.php')."?action=wqhelper_install_plugin&plugin='+pluginslug;
		}
		if (actionvalue == 'support') {linkelement.target = '_blank';
			linkelement.href = '".admin_url('admin.php')."?page='+pluginslug+'-wp-support-forum';
		}
		if (actionvalue == 'donate') {linkelement.target = '_blank';
			linkelement.href = '".$vwqurl."/contribute/?plugin='+pluginslug;
		}
		if (actionvalue == 'rate') {linkelement = '_blank';
			linkelement.href = '".$vwqurl."/support/view/plugin-reviews/'+pluginslug+'?rate=5#postform';
		}
		if (actionvalue == 'contact') {linkelement.target = '_self';
			linkelement.href = '".admin_url('admin.php')."?page='+pluginslug+'-contact';
		}
		if (actionvalue == 'home') {linkelement.target = '_blank';
			linkelement.href = '".$vwqurl."/plugins/'+pluginslug+'/';
		}
		if (actionvalue == 'upgrade') {linkelement.target = '_self';
			linkelement.href = '".admin_url('admin.php')."?page='+pluginslug+'-pricing';
		}
	}</script>";

	echo "<style>.pluginlink {text-decoration:none;} .pluginlink:hover {text-decoration:underline;}</style>";


	// Get Available Plugins from WordQuest.org
	// ----------------------------------------
	$vplugininfo = wqhelper_get_plugin_info();

	// process plugin info
	$vi = 0; $vwqplugins = array(); $vwqpluginslugs = array(); $vreleasedcount = 0;
	if (is_array($vplugininfo)) {
		foreach ($vplugininfo as $vplugin) {
			// print_r($vplugin); // debug point
			if (isset($vplugin['slug'])) {
				$vwqpluginslugs[$vi] = $vpluginslug = $vplugin['slug']; $vi++;
				if (isset($vplugin['title'])) {$vwqplugins[$vpluginslug]['title'] = $vplugin['title'];}
				if (isset($vplugin['home'])) {$vwqplugins[$vpluginslug]['home'] = $vplugin['home'];}
				if (isset($vplugin['description'])) {$vwqplugins[$vpluginslug]['description'] = $vplugin['description'];}
				if (isset($vplugin['icon'])) {$vwqplugins[$vpluginslug]['icon'] = $vplugin['icon'];}
				if (isset($vplugin['cats'])) {$vwqplugins[$vpluginslug]['cats'] = $vplugin['cats'];}
				if (isset($vplugin['tags'])) {$vwqplugins[$vpluginslug]['tags'] = $vplugin['tags'];}
				if (isset($vplugin['paidplans'])) {$vwqplugins[$vpluginslug]['paidplans'] = $vplugin['paidplans'];}
				if (isset($vplugin['package'])) {$vwqplugins[$vpluginslug]['package'] = $vplugin['package'];}
				// 1.6.0: check if released
				if (isset($vplugin['releasedate'])) {
					$vwqplugins[$vpluginslug]['releasedate'] = $vplugin['releasedate'];
					if (strtotime($vplugin['releasedate']) < time()) {$vwqplugins[$vpluginslug]['released'] = 'no';}
					else {$vwqplugins[$vpluginslug]['released'] = 'yes'; $vreleasedcount++;}
				}
				// check for latest release plugin
				if ( (isset($vplugin['latestrelease'])) && ($vplugin['latestrelease'] == 'yes') ) {
					$vplugin['latestrelease'] = 'yes'; $vlatestrelease = $vplugin;
				}
			}
		}
	}
	// print_r($vwqpluginslugs); // debug point
	// print_r($vwqplugins); // debug point

	// Get Installed and Active Plugin Slugs
	// -------------------------------------
	$vi = 0;
	foreach ($wordquestplugins as $vpluginslug => $vvalues) {
		$vpluginslugs[$vi] = $vpluginslug; $vi++;
	}
	// echo "Wordquest Plugins: "; print_r($vpluginslugs); // debug point

	// Get All Installed Plugins Info
	// ------------------------------
	$vi = 0; $vinstalledplugins = get_plugins();
	foreach ($vinstalledplugins as $vpluginfile => $vvalues) {
		// yep this is actually the correct way to generate the slug...
		$vinstalledslugs[$vi] = sanitize_title($vvalues['Name']); $vi++;
	}
	// echo "Installed Plugins: "; print_r($vinstalledplugins); // debug point
	// echo "Installed Plugin Slugs: "; print_r($vinstalledslugs); // debug point

	// Get Plugin Update Info
	// ----------------------
	$vi = 0; $vupdateplugins = get_site_transient('update_plugins');
	foreach ($vupdateplugins->response as $vpluginfile => $vvalues) {
		$vpluginupdates[$vi] = $vvalues->slug; $vi++;
	}
	// echo "Plugin Updates: "; print_r($vupdateplugins); // debug point
	// echo "Plugin Update Slugs: "; print_r($vpluginupdates); // debug point

	$vplugins = array(); $vi = 0; $vj = 0;
	foreach ($vinstalledplugins as $vpluginfile => $vvalues) {
		$vpluginslug = sanitize_title($vvalues['Name']);
		$vpluginfiles[$vpluginslug] = $vpluginfile;
		// echo '***'.$vpluginslug.'***'; // debug point
		if ( (in_array($vpluginslug,$vwqpluginslugs)) || (in_array($vpluginslug,$vpluginslugs)) ) {
			$vplugins[$vi]['slug'] = $vpluginslug;
			$vplugins[$vi]['name'] = $vvalues['Name'];
			$vplugins[$vi]['filename'] = $vpluginfile;
			$vplugins[$vi]['version'] = $vvalues['Version'];
			$vplugins[$vi]['description'] = $vvalues['Description'];

			// check for matching plugin update
			if (in_array($vpluginslug,$vpluginupdates)) {$vplugins[$vi]['update'] = 'yes';}
			else {$vplugins[$vi]['update'] = 'no';}

			// filter out to get inactive plugins
			if (!in_array($vpluginslug,$vpluginslugs)) {
				$vinactiveplugins[$vj] = $vpluginslug; $vj++;
				$vinactiveversions[$vpluginslug] = $vvalues['Version'];
			}
			$vi++;
		}
	}
	// echo "Plugin Info: "; print_r($vplugins); // debug poing
	// echo "Inactive Plugins: "; print_r($vinactiveplugins); // debug point

	// ...also check if the BioShip Theme is installed
	$vthemes = wp_get_themes(); $vbioshipinstalled = false;
	foreach ($vthemes as $vtheme) {if ($vtheme->stylesheet == 'bioship') {$vbioshipinstalled = true;} }

	// ? TODO ? Get Recommended Plugins ?
	// $vrecommended = array();

	echo '<div id="plugincolumn">';

		// Latest Release
		// --------------
		$boxid = 'wordquestlatest'; $boxtitle = 'Latest Release';
		if (is_array($vlatestrelease)) {
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside"><table>';
			echo "<table><tr><td><img src='".$vlatestrelease['icon']."'></td><td width='10'></td>";
			echo "<td>".$vlatestrelease['description']."<br><br>";
			echo "<a href='".$vlatestrelease['home']."' target=_blank>&rarr; ".$vlatestrelease['title']."</a>";
			echo "</td></tr></table>";
			echo '</table></div></div>';
		}

		// Active Plugin Panel
		// -------------------
		$boxid = 'wordquestactive'; $boxtitle = 'Active WordQuest Plugins';
		echo '<div id="'.$boxid.'" class="postbox">';
		echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
		echo '<div class="inside" id="'.$boxid.'-inside"><table>';
		foreach ($wordquestplugins as $vpluginslug => $vplugin) {
			if ($vpluginslug != 'bioship') { // filter out themes
				echo "<form id='".$vpluginslug."-form' target=_self method='get'>";
				if (in_array($vpluginslug,$vpluginupdates)) {
					$vupdatelink = wp_nonce_url(admin_url('update.php')."?action=upgrade-plugin&plugin=".$vpluginfiles[$vpluginslug]);
					echo "<input type='hidden' id='".$vpluginslug."-updatelink' value='".$vupdatelink."'>";
				}
				echo "<tr><td><a href='".$vplugin['home']."' class='pluginlink' target=_blank>";
				echo $vplugin['title']."</a></td><td width='20'></td>";
				echo "<td>".$vplugin['version']."</td><td width='20'></td>";

				echo "<td><select name='".$vpluginslug."-action' id='".$vpluginslug."-action' style='font-size:8pt;'>";
				if (in_array($vpluginslug,$vpluginupdates)) {
					echo "<option value='update' selected='selected'>Update</option><option value='settings'>Settings</option>";
				} else {echo "<option value='settings' selected='selected'>Settings</option>";}

				echo "<option value='donate'>Donate</option>";
				// echo "<option value='contribute'>Contribute</option>";
				echo "<option value='support'>Support</option>";
				if ($vplugin['plan'] == 'premium') {echo "<option value='contact'>Contact</option>";}
				if (isset($vplugin['wporgslug'])) {echo "<option value='Rate'>Rate</option>";}

				// ? TODO ? check for Premium Plans ?
				// if ($vpaidplans) {
				//  // well, not if already premium
				//	if ($vplugin['plan'] != 'premium') {echo "<option value='gopro'>Go PRO!</option>";}
				//	else {echo "<option value='account'>Account</option>";}
				// }

				echo "</select></td><td width='20'></td>";
				echo "<td><a href='javascript:void(0);' target=_blank id='".$vpluginslug."-link' onclick='dopluginaction(\"".$vpluginslug."\");'>";
				echo "<input class='button-secondary' type='button' value='Go'></a></td>";
				echo "</tr></form>";
			}
		}
		echo '</table></div></div>';

		// Inactive Plugin Panel
		// ---------------------
		if (count($vinactiveplugins) > 0) {
			$boxid = 'wordquestinactive'; $boxtitle = 'Inactive WordQuest Plugins';
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside"><table>';
			foreach ($vinactiveplugins as $vinactiveplugin) {
				echo "<form id='".$vinactiveplugin."-form' target=_self method='get'>";
				$vactivatelink = wp_nonce_url(admin_url('plugins.php')."?action=activate&plugin=".$vpluginfiles[$vinactiveplugin]);
				echo "<input type='hidden' id='".$vinactiveplugin."-activatelink' value='".$vactivatelink."'>";
				if (in_array($vinactiveplugin,$vpluginupdates)) {
					$vupdatelink = wp_nonce_url(admin_url('update.php')."?action=upgrade-plugin&plugin=".$vpluginfiles[$vinactiveplugin]);
					echo "<input type='hidden' id='".$vinactiveplugin."-updatelink' value='".$vupdatelink."'>";
				}
				echo "<tr><td><a href='".$vwqplugins[$vinactiveplugin]['home']."' class='pluginlink' target=_blank>";
				echo $vwqplugins[$vinactiveplugin]['title']."</a></td><td width='20'></td>";
				echo "<td>".$vinactiveversions[$vinactiveplugin]."</td><td width='20'></td>";
				echo "<td><select name='".$vinactiveplugin."-action' id='".$vinactiveplugin."-action' style='font-size:8pt;'>";
				if (in_array($vinactiveplugin,$vpluginupdates)) {
					echo "<option value='update' selected='selected'>Update</option><option value='activate'>Activate</option>";
				} else {echo "<option value='activate' selected='selected'>Activate</option>";}
				echo "</select></td><td width='20'></td>";
				echo "<td><a href='javascript:void(0);' target=_blank id='".$vinactiveplugin."-link' onclick='dopluginaction(\"".$vinactiveplugin."\");'>";
				echo "<input class='button-secondary' type='button' value='Go'></a></td>";
				echo "</tr></form>";
			}
			echo '</table></div></div>';
		}

		// Not Installed Plugin Panel
		// --------------------------
		if ( count($vplugins) != count($vwqplugins) ) {
			$boxid = 'wordquestavailable'; $boxtitle = 'Available WordQuest Plugins';
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside"><table>';
			foreach ($vwqplugins as $vpluginslug => $vwqplugin) {
				if (!in_array($vpluginslug,$vinstalledslugs)) {
					echo "<form id='".$vpluginslug."-form' target=_self method='get'>";
					echo "<tr><td><a href='".$vwqplugin['home']."' class='pluginlink' target=_blank>";
					echo $vwqplugin['title']."</a></td><td width='20'></td>";
					echo "<td>".$vwqplugin['version']."</td><td width='20'></td>";
					// 1.6.0: display when released or release date
					if ($vwqplugin['released'] == 'yes') {
						echo "<td><select name='".$vpluginslug."-action' id='".$vpluginslug."-action' style='font-size:8pt;'>";
						if (is_array($vwqplugin['package'])) {
							echo "<option value='install' selected='selected'>Install Now</option>";
							echo "<option value='home'>Read More</option>";
						} else {
							// oops, installation Package unavailable (404)
							echo "<option value='home'>Plugin Home</option>";
						}
						echo "</select></td>";
						echo "<td width='20'></td>";
						echo "<td><a href='javascript:void(0);' target=_blank id='".$vpluginslug."-link' onclick='dopluginaction(\"".$vpluginslug."\");'>";
						echo "<input class='button-secondary' type='button' value='Go'></a></td>";
					} else {
						echo "<td colspan='3'>Available ".date('jS F',strtotime($vwqplugin['releasedate']))."</td>";
					}
					echo "</tr></form>";
				}
			}
			echo '</table></div></div>';
		}

		// BioShip Theme Recommendation
		// ----------------------------
		$boxid = 'bioship'; $boxtitle = 'BioShip Theme Framework';
		echo '<div id="'.$boxid.'" class="postbox">';
		echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
		echo '<div class="inside" id="'.$boxid.'-inside"><table>';
		echo "<tr><td><center>";

		if ($vbioshipinstalled) {
			// check if BioShip Theme is active...
			$vtheme = wp_get_theme();
			if ($vtheme->stylesheet == 'bioship') {
				echo "Sweet! You are using the <b>BioShip Theme</b>.";
			} elseif ( (is_child_theme()) && ($vtheme->template == 'bioship') ) {
				echo "Groovy. You're using <b>BioShip Framework</b>!<br>";
				echo "Your Child Theme is <b>".$vtheme->Name."</b><br><br>";
			} else {
				echo "Looks like you have BioShip installed!<br>";
				echo "...but it is not yet your active theme.<br><br>";

				// BioShip Theme activation link...
				$vwpnonce = wp_create_nonce('switch-theme_bioship');
				$vactivatelink = 'themes.php?action=activate&stylesheet='.$vnewchildslug.'&_wpnonce='.$vwpnonce;
				echo "<a href='".$vactivatelink."'>Click here to activate it now</a>.<br><br>";

				// Check for Theme Test Drive
				echo "<div id='testdriveoptions'>";
				if (function_exists('themedrive_determine_theme')) {
					if (class_exists('TitanFramework')) {
						$vtestdrivelink = admin_url('admin.php').'?page=bioship_options&theme=bioship';
					} elseif (function_exists('OptionsFramework_Init')) {
						$vtestdrivelink = admin_url('themes.php').'?page=options-framework&theme=bioship';
					} else {$vtestdrivelink = admin_url('customize.php').'?theme=bioship';}
					echo "Or, <a href='".$vtestdrivelink."'>take it for a Theme Test Drive</a>.";
				}
				// elseif (in_array('theme-test-drive',$vinstalledplugins)) {
				// 	TODO: Theme Test Drive plugin activation link
				//  $vactivatepluginlink = ""; // ??
				// 	echo "or, <a href='javascript:void(0);'>activate Theme Test Drive plugin</a>.";
				// }
				// else {
				// 	TODO: Theme Test Drive plugin installation link
				//  $vinstallpluginlink = ""; // ??
				// 	echo "or, <a href='javascript:void(0);'>install Theme Test Drive</a>.";
				// }
				echo "</div>";
			}
 		} else {
			echo "Also from <b>WordQuest Alliance</b>, check out the<br>";
			echo "<a href='http://bioship.space' target=_blank><b>BioShip Theme Framework</b></a><br>";
			echo "A highly flexible and responsive starter theme<br>for users, designers and developers.";
			echo "</center></td></tr>";
		}

		if ( ($vtheme->template == 'bioship') || ($vtheme->stylesheet == 'bioship') ) {
			if (function_exists('admin_theme_updates_available')) {
				$vthemeupdates = admin_theme_updates_available();
				if ($vthemeupdates != '') {
					echo '<div class="update-nag" style="padding:3px 10px;margin:0 0 10px 0;text-align:center;">'.$vthemeupdates.'</div></font><br>';
				}
			}

			// TODO: future link for rating on wordpress.org theme repository ?
			// ? $vratelink = 'http://wordpress.org/themes/bioship/ ?
			// echo "<br><a href='".$vratelink."' target=_blank>Rate on WordPress.Org</a><br>";
		}
		echo '</center></td></tr>';
		echo '</table></div></div>';

		// Editor's Picks
		// --------------
		$boxid = 'recommendations'; $boxtitle = 'Editor\'s Picks';

		// ? TODO ? via TGMPA Recommendations ?
		// echo '<div id="'.$boxid.'" class="postbox">';
		// echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
		// echo '<div class="inside" id="'.$boxid.'-inside"><table>';
		// 	echo "Recommended Plugins...";
		//	print_r($vrecommended);
		// echo '</table></div></div>';

	// end column
	echo '</div>';
 };
}

// Version Specific Feed Column
// ----------------------------
$vfuncname = 'wqhelper_admin_feeds_column_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function($vargs) {

	echo '<div id="feedcolumn">';

		// Join WordQuest
		// --------------
		$boxid = 'wordquestjoin'; $boxtitle = 'Latest Release';
		// echo '<div id="'.$boxid.'" class="postbox">';
		// echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
		// echo '<div class="inside" id="'.$boxid.'-inside"><table>';
		// echo 'Join WordQuest today. It is awesome.';
		// echo '</table></div></div>';

		// WordQuest Feed
		// --------------
		$boxid = 'wordquestfeed'; $boxtitle = 'WordQuest News';
		if (function_exists('wqhelper_dashboard_feed_widget')) {
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside">';
				wqhelper_dashboard_feed_widget();
			echo '</div></div>';
		}

		// BioShip Feed
		// ------------
		// (only displays if Bioship theme is active)
		$boxid = 'bioshipfeed'; $boxtitle = 'BioShip News';
		if (function_exists('muscle_bioship_dashboard_feed_widget')) {
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside">';
				muscle_bioship_dashboard_feed_widget(false);
			echo '</div></div>';
		}

		// PluginReview Feed
		// -----------------
		$boxid = 'pluginreviewfeed'; $boxtitle = 'Plugin Reviews';
		if (function_exists('wqhelper_pluginreview_feed_widget')) {
			echo '<div id="'.$boxid.'" class="postbox">';
			echo '<h2 class="hndle" onclick="togglemetabox(\''.$boxid.'\');"><span>'.$boxtitle.'</span></h2>';
			echo '<div class="inside" id="'.$boxid.'-inside">';
				wqhelper_pluginreview_feed_widget();
			echo '</div></div>';
		}

	// end column
	echo "</div>";

	// feed javascript
	if (!has_action('admin_footer','wqhelper_dashboard_feed_javascript')) {
		add_action('admin_footer','wqhelper_dashboard_feed_javascript');
	}

 };
}

// Version Specific Admin Styles
// -----------------------------
$vfuncname = 'wqhelper_admin_styles_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
	// Hide Wordquest plugin freemius submenu items if top level admin menu not open
	echo "<style>#toplevel_page_wordquest a.wp-first-item:after {content: ' Alliance';}
	#toplevel_page_wordquest.wp-not-current-submenu .fs-submenu-item
		{display: none; line-height: 0px; height: 0px;}
    #toplevel_page_wordquest li.wp-first-item {margin-bottom: 5px; margin-left: -10px;}
    span.fs-submenu-item.fs-sub {display: none;}
	.current span.fs-submenu-item.fs-sub {display: block;}
	#wpfooter {display:none !important;}
    </style>";
 };
}

// Version Specific Admin Script
// -----------------------------
$vfuncname = 'wqhelper_admin_scripts_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
 	// wordquest admin submenu icon and styling fix
	echo "<script>function wordquestsubmenufix(slug,iconurl,current) {
	jQuery('li a').each(function() {
		position = this.href.indexOf('admin.php?page='+slug);
		if (position > -1) {
			linkref = this.href.substr(position);
			jQuery(this).css('margin-left','10px');
			if (linkref == 'admin.php?page='+slug) {
				jQuery('<img src=\"'+iconurl+'\" style=\"float:left;\">').insertBefore(this);
				jQuery(this).css('margin-top','-3px');
			} else {if (current == 1) {
				if (linkref == 'admin.php?page='+slug+'-account') {jQuery(this).addClass('current');}
				if (linkref == 'admin.php?page='+slug+'-pricing') {jQuery(this).addClass('current');}
				if (linkref == 'admin.php?page='+slug+'-contact') {jQuery(this).addClass('current');}
				if (linkref == 'admin.php?page='+slug+'-wp-support-forum') {jQuery(this).addClass('current');}
				jQuery(this).css('margin-top','-3px');
			} else {jQuery(this).css('margin-top','-10px');} }
		}
	});
	}</script>";
 };
}

// Install a WordQuest Plugin
// --------------------------
$vfuncname = 'wqhelper_install_plugin_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
	if (current_user_can('manage_options')) {

		// get the package from download/update server
		$vpluginslug = $_REQUEST['plugin'];
		$vwqurl = 'http://'.'wordquest.org';
		$vurl = $vwqurl.'/downloads/?action=get_metadata&slug='.$vpluginslug;
		$vresponse = wp_remote_get($vurl,array('timeout'=>30));
		if (!is_wp_error($vresponse)) {
			if (stristr($vresponse['body'],'404 Not Found')) {
				// try to get package info from transient data
				$vplugininfo = get_transient('wordquestplugininfo');
				if (is_array($vpluginfo)) {
					foreach ($vplugininfo as $vplugin) {
						if (is_object($vplugin)) {
							if ($vplugin->slug == $vpluginslug) {
								$vpluginpackage = $vplugin['package'];
							}
						}
					}
				}
			} else {$vpluginpackage = json_decode($vresponse['body']);}
		}

		if (!isset($vpluginpackage)) {exit;} // failed, add a message?

		// now download the package...
		$vdownload = wp_remote_get($vpluginpackage->download_url);
		if (is_wp_error($vdownload)) { // give it another go...
			sleep(5); $vdownload = wp_remote_get($vpluginpackage->download_url);
		}

		if (!is_wp_error($vdownload)) {
			$vplugin = $vdownload['body'];

		} else {exit;} // failed, add a message?

		// TODO: pass the zip package on to Wordpress to do the rest?

		// INSTALL PLUGIN ZIP

	}
 };
}


// ------------------------
// === Sidebar FloatBox ===
// ------------------------

// Main Floatbox Function
// ----------------------
$vfuncname = 'wqhelper_sidebar_floatbox_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function($vargs) {

	// echo "****"; print_r($vargs); echo "*****"; // debug point

	if (count($vargs) == 7) {
		// the old way, sending all args individually
		$vpre = $vargs[0]; $vpluginslug = $vargs[1]; $vfreepremium = $vargs[2];
		$vwporgslug = $vargs[3]; $vsavebutton = $vargs[4];
		$vplugintitle = $vargs[5]; $vpluginversion = $vargs[6];
	} else {
		// the new way, just sending two args
		$vpluginslug = $vslug = $vargs[0];
		$vsavebutton = $vargs[1];

		// get the args using the slug and global array
		global $wordquestplugins;
		$vpluginversion = $wordquestplugins[$vslug]['version'];
		$vplugintitle = $wordquestplugins[$vslug]['title'];
		$vpre = $wordquestplugins[$vslug]['settings'];
		$vfreepremium = $wordquestplugins[$vslug]['plan'];
		$vwporg = $wordquestplugins[$vslug]['wporg'];

		if (isset($wordquestplugins[$vslug]['wporgslug'])) {
			$vwporgslug = $wordquestplugins[$vslug]['wporgslug'];
		} else {$vwporgslug = '';}

		echo "<!-- Plugin Info: "; print_r($wordquestplugins[$vslug]); echo "-->";
	}

	// 1.5.0: get/convert to single array of plugin sidebar options
	// 1.6.0: fix to sidebar options variable
	$sidebaroptions = get_option($vpre.'_sidebar_options');
	if ( ($sidebaroptions == '') || (!is_array($sidebaroptions)) ) {
		$sidebaroptions['adsboxoff'] = get_option($vpre.'_ads_box_off'); delete_option($vpre.'_ads_box_off');
		$sidebaroptions['donationboxoff'] = get_option($vpre.'_donation_box_off'); delete_option($vpre.'_donation_box_off');
		$sidebaroptions['reportboxoff'] = get_option($vpre.'_report_box_off'); delete_option($vpre.'_report_box_off');
		add_option($vpre.'_sidebar_options',$sidebaroptions);
	}

	echo "<script language='javascript' type='text/javascript'>
	function hidesidebarsaved() {document.getElementById('sidebarsaved').style.display = 'none';}
	function doshowhidediv(divname) {
		if (document.getElementById(divname).style.display == 'none') {document.getElementById(divname).style.display = '';}
		else {document.getElementById(divname).style.display = 'none';}
		/* jQuery(document.body).trigger('sticky_kit:recalc'); */
	}
	</script>";

	// Floatbox Styles
	echo '<style>#floatdiv {margin-top:20px;} .inside {font-size:9pt; line-height:1.6em; padding:0px;}
	#floatdiv a {text-decoration:none;} #floatdiv a:hover {text-decoration:underline;}
	#floatdiv .stuffbox {background-color:#FFFFFF; margin-bottom:10px; padding-bottom:10px; text-align:center; width:25%;}
	#floatdiv .stuffbox .inside {padding:0 3px;}
	.stuffbox h3 {margin:10px 0; background-color:#FAFAFA; font-size:12pt;}
	</style>';

	echo '<div id="floatdiv" class="floatbox">';
	echo '<!-- WQ Helper Loaded From: '.dirname(__FILE__).' -->';

	// Call (optional) Plugin Sidebar Header
	$vfuncname = $vpre.'_sidebar_plugin_header';
	if (function_exists($vfuncname)) {call_user_func($vfuncname);}

	// Donation Box
	// ------------
	// for Free Version? Or Upgrade Link?
	$vargs = array($vpre,$vpluginslug);
	echo '<div id="donate"';
	if ($sidebaroptions['donationboxoff'] == 'checked') {echo " style='display:none;'>";} else {echo ">";}
	if ($vfreepremium == 'free') {
		echo '<div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>Gifts of Appreciation</h3><div class="inside">';
		wqhelper_sidebar_paypal_donations($vargs);
		wqhelper_sidebar_testimonial_box($vargs);
		if ($vwporgslug != '') {
			echo "<a href='http://wordpress.org/support/view/plugin-reviews/'".$vwporgslug."'?rate=5#postform' target='_blank'>&#9733; Rate this Plugin on Wordpress.Org</a></center>";
		}
		// elseif ($vpluginslug == 'bioship') {
			// 1.5.0: add star rating for theme (when in repository)
			// echo "<a href='https://wordpress.org/support/view/theme-reviews/bioship#postform' target='_blank'>&#9733; Rate this Theme on Wordpress.Org</a></center>";
		// }
		echo '</div></div>';
	}
	elseif ($vfreepremium == 'premium') {
		echo '<div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>Testimonials</h3><div class="inside">';
		wqhelper_sidebar_testimonial_box($vargs);
		echo '</div></div>';
	}
	echo '</div>';

	// Bonus Subscription Form
	// -----------------------
	// ...populated for current user...
	global $current_user; $current_user = wp_get_current_user();
	$vuseremail = $current_user->user_email; if (strstr($vuseremail,'@localhost')) {$vuseremail = '';}
	$vuserid = $current_user->ID; $vuserdata = get_userdata($vuserid);
	$vusername = $vuserdata->first_name; $vlastname = $vuserdata->last_name;
	if ($vlastname != '') {$vusername .= ' '.$vlastname;}

	if ($vpluginslug == 'bioship') {$vreportimage = get_template_directory_uri()."/images/rv-report.jpg";}
	else {$vreportimage = plugin_dir_url(__FILE__)."images/rv-report.jpg";}
	echo '<div id="bonusoffer"';
	if (get_option($vpre.'_report_box_off') == 'checked') {echo " style='display:none;'>";} else {echo ">";}
	echo '<div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>Bonus Offer</h3><div class="inside">';
	echo "<center><table cellpadding='0' cellspacing='0'><tr><td align='center'><img src='".$vreportimage."' width='60' height='80'><br>";
	echo "<font style='font-size:6pt;'><a href='http://pluginreview.net/return-visitors-report/' target=_blank>learn more...</a></font></td><td width='7'></td>";
	echo "<td align='center'><b><font style='color:#ee0000;font-size:9pt;'>Maximize Sales Conversions:</font><br><font style='color:#0000ee;font-size:10pt;'>The Return Visitors Report</font></b><br>";
	echo "<form style='margin-top:7px;' action='http://pluginreview.net/?visitorfunnel=join' target='_blank' method='post'>";
	echo "<input type='hidden' name='source' value='".$vpluginslug."-sidebar'>";
	echo "<input placeholder='Your Email...' type='text' style='width:150px;font-size:9pt;' name='subemail' value='".$vuseremail."'><br>";
	echo "<table><tr><td><input placeholder='Your Name...' type='text' style='width:90px;font-size:9pt;' name='subname' value='".$vusername."'></td>";
	echo "<td><input type='submit' class='button-secondary' value='Get it!'></td></tr></table>";
	echo "</td></tr></table></form></center>";
	echo '</div></div></div>';

	// PluginReview.Net Plugin Ad
	// --------------------------
	if ($sidebaroptions['adsboxoff'] != 'checked') {
		echo '<div id="pluginads">';
		echo '<div class="stuffbox" style="width:250px;"><h3>Recommended</h3><div class="inside">';
		echo "<script language='javascript' src='http://pluginreview.net/recommends/?s=yes&a=majick&c=".$vpluginslug."&t=sidebar'></script>";
		echo '</div></div></div>';
	}

	// Call Plugin Footer Function
	// ---------------------------
	$vfuncname = $vpre.'_sidebar_plugin_footer';
	if (function_exists($vfuncname)) {call_user_func($vfuncname);}
	else {
		// Default Sidebar Plugin Footer
		// -----------------------------
		// also allow for theme not plugin...
		$vwqurl = 'http://'.'wordquest.org';
		if ($vpluginslug == 'bioship') {
			$viconurl = get_template_directory_uri().'/images/wordquest.png';
			$vpluginurl = "http://"."bioship.space/";
			$vpluginfootertitle = 'Theme Info';
		} else {
			$viconurl = plugin_dir_url(__FILE__)."images/wordquest.png";
			$vpluginurl = $vwqurl."/plugins/".$vpluginslug."/";
			$vpluginfootertitle = 'Plugin Info';
		}
		echo '<div id="pluginfooter"><div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>'.$vpluginfootertitle.'</h3><div class="inside">';
		echo "<center><table><tr>";
		echo "<td><a href='".$vwqurl."/' target='_blank'><img src='".$viconurl."' border=0></a></td></td>";
		echo "<td width='14'></td>";
		echo "<td><a href='".$vpluginurl."' target='_blank'>".$vplugintitle."</a> <i>v".$vpluginversion."</i><br>";
		echo "by <a href='".$vwqurl."/' target='_blank'>WordQuest Alliance</a><br>";
		echo "<a href='".$vwqurl."/plugins/' target='_blank'><b>&rarr; More Cool Plugins</b></a><br>";
		echo "<a href='http://"."pluginreview.net/directory/' target='_blank'>&rarr; Plugin Directory</a></td>";
		echo "</tr></table></center>";
		echo '</div></div></div>';
	}

	// Save Settings Button
	// --------------------
	if ($vsavebutton != 'replace') {

		echo '<div id="savechanges"><div class="stuffbox" style="width:250px;background-color:#ffffff;"><h3>Update Settings</h3><div class="inside"><center>';

		if ($vsavebutton == 'yes') {
			$vbuttonoutput = "<script>function sidebarsavepluginsettings() {jQuery('#plugin-settings-save').trigger('click');}</script>";
			$vbuttonoutput .= "<table><tr>";
			$vbuttonoutput .= "<td align='center'><input id='sidebarsavebutton' onclick='sidebarsavepluginsettings();' type='button' class='button-primary' value='Save Settings'></td>";
			$vbuttonoutput .= "<td width='30'></td>";
			$vbuttonoutput .= "<td><div style='line-height:1em;'><font style='font-size:8pt;'><a href='javascript:void(0);' style='text-decoration:none;' onclick='doshowhidediv(\"sidebarsettings\");hidesidebarsaved();'>Sidebar<br>Options</a></font></div></td>";
			$vbuttonoutput .= "</tr></table>";
			$vbuttonoutput = apply_filters('wordquest_sidebar_save_button',$vbuttonoutput);
			echo $vbuttonoutput;
		}
		elseif ($vsavebutton == 'no') {echo "";}
		else {echo "<div style='line-height:1em;text-align:center;'><font style='font-size:8pt;'><a href='javascript:void(0);' style='text-decoration:none;' onclick='doshowhidediv(\"sidebarsettings\");hidesidebarsaved();'>Sidebar Options</a></font></div>";}

		echo "<div id='sidebarsettings' style='display:none;'><br>";

			global $wordquesthelper;
			echo "<form action='".admin_url('admin-ajax.php')."' target='savesidebar' method='post'>";
			echo "<input type='hidden' name='action' value='wqhelper_update_sidebar_options'>";
			// 1.6.0: added version matching form field
			echo "<input type='hidden' name='wqhv' value='".$wordquesthelper."'>";
			echo "<input type='hidden' name='sidebarprefix' value='".$vpre."'>";
			echo "<table><tr><td align='center'>";
			echo "<b>I rock! I have made a donation.</b><br>(hides donation box)</td><td width='10'></td>";
			echo "<td align='center'><input type='checkbox' name='".$vpre."_donation_box_off' value='checked'";
			if ($sidebaroptions['donationboxoff'] == 'checked') {echo " checked>";} else {echo ">";}
			echo "</td></tr>";

			echo "<tr><td align='center'>";
			echo "<b>I've got your report, you<br>can stop bugging me now. :-)</b><br>(hides report box)</td><td width='10'></td>";
			echo "<td align='center'><input type='checkbox' name='".$vpre."_report_box_off' value='checked'";
			if ($sidebaroptions['reportboxoff'] == 'checked') {echo " checked>";} else {echo ">";}
			echo "</td></tr>";

			echo "<tr><td align='center'>";
			echo "<b>My site is so awesome it<br>doesn't need any more quality<br>plugins recommendations.</b><br>(hides sidebar ads.)</td><td width='10'></td>";
			echo "<td align='center'><input type='checkbox' name='".$vpre."_ads_box_off' value='checked'";
			if ($sidebaroptions['ads_box_off'] == 'checked') {echo " checked>";} else {echo ">";}
			echo "</td></tr></table><br>";

			echo "<center><input type='submit' class='button-secondary' value='Save Sidebar Options'></center></form><br>";
			echo "<iframe src='javascript:void(0);' name='savesidebar' id='savesidebar' width='250' height'250' style='display:none;'></iframe>";

			echo "<div id='sidebarsaved' style='display:none;'>";
			echo "<table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
			echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
			echo "Sidebar Options Saved.</font></div></td></tr></table></div>";

		echo "</div></center>";

		echo '</div></div></div>';
	}

	echo '</div>';

	// echo '</div>';
 };
}

// ----------------
// Paypal Donations
// ----------------
$vfuncname = 'wqhelper_sidebar_paypal_donations_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function($vargs) {

	$vpre = $vargs[0]; $vpluginslug = $vargs[1];
	if (function_exists($vpre.'_donations_special_top')) {
		$vfuncname = $vpre.'_donations_special_top';
		call_user_func($vfuncname);
	}

	// make display name from the plugin slug
	if (strstr($vpluginslug,'-')) {
		$vparts = explode('-',$vpluginslug);
		$vi = 0;
		foreach ($vparts as $vpart) {
			if ($vpart == 'wp') {$vparts[$vi] = 'WP';}
			else {$vparts[$vi] = strtoupper(substr($vpart,0,1)).substr($vpart,1,(strlen($vpart)-1));}
			$vi++;
		}
		$vpluginname = implode(' ',$vparts);
	}
	else {
		$vpluginname = strtoupper(substr($vpluginslug,0,1)).substr($vpluginslug,1,(strlen($vpluginslug)-1));
	}


	echo "<script language='javascript' type='text/javascript'>
	function showrecurringform() {
		document.getElementById('recurradio').checked = true;
		document.getElementById('onetimedonation').style.display = 'none';
		document.getElementById('recurringdonation').style.display = '';
	}
	function showonetimeform() {
		document.getElementById('onetimeradio').checked = true;
		document.getElementById('recurringdonation').style.display = 'none';
		document.getElementById('onetimedonation').style.display = '';
	}
	function switchperiodoptions() {
		var selectelement = document.getElementById('recurperiod');
		var recurperiod = selectelement.options[selectelement.selectedIndex].value;
		if ( (recurperiod == 'Weekly') || (recurperiod == 'W') ) {
			document.getElementById('periodoptions').innerHTML = document.getElementById('weeklyamounts').innerHTML;
			var monthlyselected = document.getElementById('monthlyselected').value;
			var weeklyselected = monthlyselected++;
			var selectelement = document.getElementById('periodoptions');
			selectelement.selectedIndex = weeklyselected;
		}
		if ( (recurperiod == 'Monthly') || (recurperiod == 'M') ) {
			document.getElementById('periodoptions').innerHTML = document.getElementById('monthlyamounts').innerHTML;
			var weeklyselected = document.getElementById('weeklyselected').value;
			var monthlyselected = weeklyselected--;
			var selectelement = document.getElementById('periodoptions')
			selectelement.selectedIndex = monthlyselected;
		}
	}
	function storeamount() {
		var selectelement = document.getElementById('recurperiod');
		var recurperiod = selectelement.options[selectelement.selectedIndex].value;
		var selectelement = document.getElementById('periodoptions');
		var selected = selectelement.selectedIndex;
		if ( (recurperiod == 'Weekly') || (recurperiod == 'W') ) {
			document.getElementById('weeklyselected').value = selected;
		}
		if ( (recurperiod == 'Monthly') || (recurperiod == 'M') ) {
			document.getElementById('monthlyselected').value = selected;
		}
	}
	</script>";


	$vwqurl = 'http://'.'wordquest.org';
	$vnotifyurl = $vwqurl.'/?estore_pp_ipn=process';
	$vsandbox = '';
	// $vsandbox = 'sandbox.';

	// recurring / one-time switcher
	echo "<center><table cellpadding='0' cellspacing='0'><tr><td>";
	echo "<input name='donatetype' id='recurradio' type='radio' onclick='showrecurringform();' checked> <a href='javascript:void(0);' onclick='showrecurringform();' style='text-decoration:none;'>Supporter</a> ";
	echo "</td><td width='10'></td><td>";
	echo "<input name='donatetype' id='onetimeradio' type='radio' onclick='showonetimeform();'> <a href-'javascript:void(0);' onclick='showonetimeform();' style='text-decoration:none;'>One Time</a>";
	echo "</td></tr></table></center>";

	// 1.5.0: weekly amounts
	echo '<div style="display:none;"><input type="hidden" id="weeklyselected" value="3">
	<select name="wp_eStore_subscribe" id="weeklyamounts" style="font-size:8pt;" size="1">
	<optgroup label="Supporter Amount">
	<option value="1">Copper: $1 </option>
	<option value="3">Bronze: $2</option>
	<option value="5">Silver: $4</option>
	<option value="7">Gold: $5</option>
	<option value="9">Platinum: $7.50</option>
	<option value="11">Titanium: $10</option>
	<option value="13">Star Ruby: $12.50</option>
	<option value="15">Star Topaz: $15</option>
	<option value="17">Star Emerald: $17.50</option>
	<option value="19">Star Sapphire: $20</option>
	<option value="21">Star Diamond: $25</option>
	</select></div>';

	// 1.5.0: monthly amounts
	echo '<div style="display:none;"><input type="hidden" id="monthlyselected" value="3">
	<select name="wp_eStore_subscribe" id="monthlyamounts" style="font-size:8pt;" size="1">
	<optgroup label="Supporter Amount">
	<option value="2">Copper: $5</option>
	<option value="4">Bronze: $10</option>
	<option value="6">Silver: $15</option>
	<option value="9" selected="selected">Gold: $20</option>
	<option value="10">Platinum: $30</option>
	<option value="12">Titanium: $40</option>
	<option value="14">Star Ruby: $50</option>
	<option value="16">Star Topaz: $60</option>
	<option value="18">Star Emerald: $70</option>
	<option value="20">Star Sapphire: $80</option>
	<option value="22">Star Diamond: $100</option>
	</select></div>';

	// recurring form
	// $vwqurl.'/?wp_eStore_subscribe=LEVEL&c_input='.$vpluginslug;

	if ($vpluginslug == 'bioship') {$vdonateimage = get_template_directory_uri()."/images/pp-donate.jpg";}
	else {$vdonateimage = plugin_dir_url(__FILE__)."/images/pp-donate.jpg";}
	// echo '
	//	<center><form id="recurringdonation" method="POST" action="https://www.'.$vsandbox.'paypal.com/cgi-bin/webscr" target="_blank">
	//	<input type="hidden" name="bn" value="WordQuest_Donate_SF_AU">
	//	<input type="hidden" name="business" value="contribute@wordquest.org">
	//	<input type="hidden" id="r_item_name" name="item_name" value="'.$vpluginname.' Supporter">
	//	<input type="hidden" id="r_custom" name="custom" value="'.$vpluginslug.'">
	//	<input type="hidden" name="item_number" value>
	//	<input type="hidden" name="currency_code" value="USD">
	//	<input type="hidden" name="no_shipping" value="1">
	//	<input type="hidden" name="image_url" value="'.$vwurl.'/images/wordquest-paypal-logo.jpg">
	//	<input type="hidden" id="r_return" name="return" value="'.$vwqurl.'/thankyou/?plugin='.$vpluginslug.'">
	//	<input type="hidden" name="cbt" value="Return to Contribute Page">
	//	<input type="hidden" id="r_cancel_return" name="cancel_return" value="'.$vwqurl.'/contribute/?plugin='.$vpluginslug.'">
	//	<input type="hidden" name="no_note" value="0">
	//	<input type="hidden" name="cn" value="Give a Testimonial and/or Log Feature Request">
	//	<input type="hidden" name="notify_url" value="'.$vnotifyurl.'">

	//	<input type="hidden" name="cmd" value="_xclick-subscriptions">
	//	<input type="hidden" name="p3" value="1">
	//	<input type="hidden" name="src" value="1">
	//	<input type="hidden" name="sra" value="0">
	//	<input type="hidden" name="modify" value="1">
	//	<table cellpadding="0" cellspacing="0"><tr><td>
	//	<select name="a3" style="font-size:8pt;" size="1" id="periodoptions" onchange="storeamount();">
	//	<option value="">Supporter Amount</option>
	//	<option value="1">Copper: $1 </option>
	//	<option value="2">Bronze: $2</option>
	//	<option value="3">Silver: $3</option>
	//	<option value="4" selected="selected">Gold: $4</option>
	//	<option value="5">Platinum: $5</option>
	//	<option value="6">Titanium: $6</option>
	//	<option value="7">Ruby: $7</option>
	//	<option value="8">Topaz: $8</option>
	//	<option value="9">Emerald: $9</option>
	//	<option value="10">Sapphire: $10</option>
	//	<option value="12">Diamond: $12</option>
	//	</select>
	//	</td><td width="5"></td><td>
	//	<select name="t3" style="font-size:9pt;" id="recurperiod" onchange="switchperiodoptions()">
	//	<option selected="selected" value="W">Weekly</option>
	//	<option value-"M">Monthly</option>
	//	</select></tr></table>
	//	<input type="image" src="'.$vdonateimage.'" border="0" name="I1">
	//	</center></form>
	// ';

	echo '
		<center><form id="recurringdonation" method="GET" action="'.$vwqurl.'" target="_blank">
		<input type="hidden" name="c_input" value="'.$vpluginslug.'">
		<select name="wp_eStore_subscribe" style="font-size:10pt;" size="1" id="periodoptions" onchange="storeamount();">
		<optgroup label="Supporter Amount">
		<option value="1">Copper: $1 </option>
		<option value="3">Bronze: $2</option>
		<option value="5">Silver: $4</option>
		<option value="7" selected="selected">Gold: $5</option>
		<option value="9">Platinum: $7.50</option>
		<option value="11">Titanium: $10</option>
		<option value="13">Ruby: $12.50</option>
		<option value="15">Topaz: $15</option>
		<option value="17">Emerald: $17.50</option>
		<option value="19">Sapphire: $20</option>
		<option value="21">Diamond: $25</option>
		</select>
		</td><td width="5"></td><td>
		<select name="t3" style="font-size:10pt;" id="recurperiod" onchange="switchperiodoptions()">
		<option selected="selected" value="W">Weekly</option>
		<option value-"M">Monthly</option>
		</select></tr></table>
		<input type="image" src="'.$vdonateimage.'" border="0" name="I1">
		</center></form>';

	// one time form
	// echo '
	//	<center><form id="onetimedonation" style="display:none;" method="POST" action="https://www.paypal.com/cgi-bin/webscr" target="_blank">
	//	<input type="hidden" name="bn" value="WordQuest_Donate_SF_AU">
	//	<input type="hidden" name="business" value="contribute@wordquest.org">
	//	<input type="hidden" id="o_item_name" name="item_name" value="'.$vpluginname.' Donation">
	//	<input type="hidden" id="o_custom" name="custom" value="'.$vpluginslug.'">
	//	<input type="hidden" name="item_number" value>
	//	<input type="hidden" name="currency_code" value="USD">
	//	<input type="hidden" name="no_shipping" value="1">
	//	<input type="hidden" name="image_url" value="'.$vwqurl.'/images/wordquest-paypal-logo.jpg">
	//	<input type="hidden" id="o_return" name="return" value="'.$vwqurl.'/thankyou/?plugin='.$vpluginslug.'">
	//	<input type="hidden" name="cbt" value="Return to Contribute Page">
	//	<input type="hidden" id="o_cancel_return" name="cancel_return" value="'.$vwqurl.'/contribute/?plugin='.$vpluginslug.'">
	//	<input type="hidden" name="no_note" value="0">
	//	<input type="hidden" name="cn" value="Give a Testimonial and/or Log Feature Request">
	//	<input type="hidden" name="notify_url" value="'.$vnotifyurl.'">
	//	<input type="hidden" name="cmd" value="_donations">
	//	<select name="amount" style="font-size:8pt;" size="1">
	//	<option selected value="">Select Gift Amount</option>
	//	<option value="5">$5 - Buy me a Cuppa</option>
	//	<option value="10">$10 - Buy me Lunch</option>
	//	<option value="20">$20 - Support a Minor Bugfix</option>
	//	<option value="50">$50 - Support a Minor Update</option>
	//	<option value="100">$100 - Support a Major Bugfix/Update</option>
	//	<option value="250">$250 - Support a Minor Feature</option>
	//	<option value="500">$500 - Support a Major Feature</option>
	//	<option value="1000">$1000 - Improve my Outsourcing Budget</option>
	//	<option value="">Be Unique: Enter Custom Amount</option>
	//	</select>
	//	<input type="image" src="'.$vdonateimage.'" border="0" name="I1">
	//	</center></form>
	// ';

	// $vwqurl.'/?wp_eStore_donation=23&var1_price=AMOUNT&c_input='.$vpluginslug;
	echo '
	<center><form id="onetimedonation" style="display:none;" method="GET" action="'.$vwqurl.'" target="_blank">
		<input type="hidden" name="wp_eStore_donation" value="23">
		<input type="hidden" name="c_input" value="'.$vpluginslug.'">
		<select name="var1_price" style="font-size:10pt;" size="1">
		<option selected value="">Select Gift Amount</option>
		<option value="5">$5 - Buy me a Cuppa</option>
		<option value="10">$10 - Log a Feature Request</option>
		<option value="20">$20 - Support a Minor Bugfix</option>
		<option value="50">$50 - Support a Minor Update</option>
		<option value="100">$100 - Support a Major Bugfix/Update</option>
		<option value="250">$250 - Support a Minor Feature</option>
		<option value="500">$500 - Support a Major Feature</option>
		<option value="1000">$1000 - Support a New Plugin</option>
		<option value="">Be Unique: Enter Custom Amount</option>
		</select>
		<input type="image" src="'.$vdonateimage.'" border="0" name="I1">
		</center></form>
	';

	if (function_exists($vpre.'_donations_special_bottom')) {
		$vfuncname = $vpre.'_donations_special_bottom';
		call_user_func($vfuncname);
	}
 };
}

// ---------------
// Testimonial Box
// ---------------
$vfuncname = 'wqhelper_sidebar_testimonial_box_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function($vargs) {

	global $current_user; $current_user = wp_get_current_user();
	$vuseremail = $current_user->user_email; $vuserid = $current_user->ID;
	$vuserdata = get_userdata($vuserid);
	$vusername = $vuserdata->first_name;
	$vlastname = $vuserdata->last_name;
	if ($vlastname != '') {$vusername .= ' '.$vlastname;}

	$vpre = $vargs[0]; $vpluginslug = $vargs[1];
	$vpluginslug = str_replace('-','',$vpluginslug);
	echo "<script language='javascript' type='text/javascript'>
	function showhidetestimonialbox() {
		if (document.getElementById('sendtestimonial').style.display == '') {
			document.getElementById('sendtestimonial').style.display = 'none';
		}
		else {
			document.getElementById('sendtestimonial').style.display = '';
			document.getElementById('testimonialbox').style.display = 'none';
		}
	}
	function submittestimonial() {
		document.getElementById('testimonialbox').style.display='';
		document.getElementById('sendtestimonial').style.display='none';
	}</script>";

	$vwqurl = 'http://'.'wordquest.org';
	echo "<center><a href='javascript:void(0);' onclick='showhidetestimonialbox();'>Send me a thank you or testimonial.</a><br>";
	echo "<div id='sendtestimonial' style='display:none;' align='center'>";
	echo "<center><form action='".$vwqurl."' method='post' target='testimonialbox' onsubmit='submittestimonial();'>";
	echo "<b>Your Testimonial:</b><br>";
	echo "<textarea rows='5' cols='25' name='message'></textarea><br>";
	echo "<label for='testimonial_sender'>Your Name:</label> ";
	echo "<input type='text' placeholder='Your Name... (optional)' style='width:200px;' name='testimonial_sender' value='".$vusername."'><br>";
	echo "<input type='text' placeholder='Your Website... (optional)' style='width:200px;' name='testimonial_website' value=''><br>";
	echo "<input type='hidden' name='sending_plugin_testimonial' value='yes'>";
	echo "<input type='hidden' name='for_plugin' value='".$vpluginslug."'>";
	echo "<input type='submit' class='button-secondary' value='Send Testimonial'>";
	echo "</form>";
	echo "</div>";
	echo "<iframe name='testimonialbox' id='testimonialbox' frameborder='0' src='javascript:void(0);' style='display:none;' width='250' height='50' scrolling='no'></iframe>";
 };
}

// ---------------------
// Save Sidebar Settings
// ---------------------
// !! caller exception !! uses form matching version function
$vfuncname = 'wqhelper_update_sidebar_options_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
	$vpre = $_REQUEST['sidebarprefix'];
	if (current_user_can('manage_options')) {

		// 1.5.0: convert to single array of plugin sidebar options
		$sidebaroptions = get_option($vpre.'_sidebar_options');
		if ($sidebaroptions == '') {$sidebaroptions = array('installdate'=>date('Y-m-d'));}
		$sidebaroptions['adsboxoff'] = $_POST[$vpre.'_ads_box_off'];
		$sidebaroptions['donationboxoff'] = $_POST[$vpre.'_donation_box_off'];
		$sidebaroptions['reportboxoff'] = $_POST[$vpre.'_report_box_off'];
		update_option($vpre.'_sidebar_options');

		// Javascript Show/Hide Callbacks
		echo "<script language='javascript' type='text/javascript'>";
		if ($vdonationboxoff == 'checked') {echo "parent.document.getElementById('donate').style.display = 'none'; ";}
		else {echo "parent.document.getElementById('donate').style.display = ''; ";}
		if ($vreportboxoff == 'checked') {echo "parent.document.getElementById('bonusoffer').style.display = 'none'; ";}
		else {echo "parent.document.getElementById('bonusoffer').style.display = ''; ";}
		if ($vadsboxoff == 'checked') {echo "parent.document.getElementById('pluginads').style.display = 'none'; ";}
		else {echo "parent.document.getElementById('pluginads').style.display = ''; ";}
		echo "parent.document.getElementById('sidebarsaved').style.display = ''; ";
		echo "parent.document.getElementById('sidebarsettings').style.display = 'none'; ";
		echo "</script>";

		// maybe call Special Update Options
		$vfuncname = $vpre.'_update_sidebar_options_special';
		if (function_exists($vfuncname)) {call_user_func($vfuncname);}
	}
	exit;
 };
}

// ---------------------
// Float Menu Javascript
// ---------------------
$vfuncname = 'wqhelper_sidebar_floatmenuscript_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {

	return "
	<style>.floatbox {position:absolute;width:250px;top:30px;right:15px;z-index:100;}</style>

	<script language='javascript' type='text/javascript'>
	/* Script by: www.jtricks.com
	 * Version: 1.8 (20111103)
	 * Latest version: www.jtricks.com/javascript/navigation/floating.html
	 *
	 * License:
	 * GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
	 */
	var floatingMenu =
	{
		hasInner: typeof(window.innerWidth) == 'number',
		hasElement: typeof(document.documentElement) == 'object'
		&& typeof(document.documentElement.clientWidth) == 'number'
	};

	var floatingArray =
	[
	];

	floatingMenu.add = function(obj, options)
	{
		var name;  var menu;
		if (typeof(obj) === 'string') name = obj; else menu = obj;
		if (options == undefined) {
		floatingArray.push( {id: name, menu: menu, targetLeft: 0, targetTop: 0, distance: .07, snap: true});
		}
		else  {
		floatingArray.push(
			{id: name, menu: menu, targetLeft: options.targetLeft, targetRight: options.targetRight,
			targetTop: options.targetTop, targetBottom: options.targetBottom, centerX: options.centerX,
			centerY: options.centerY, prohibitXMovement: options.prohibitXMovement,
			prohibitYMovement: options.prohibitYMovement, distance: options.distance != undefined ? options.distance : .07,
			snap: options.snap, ignoreParentDimensions: options.ignoreParentDimensions, scrollContainer: options.scrollContainer,
			scrollContainerId: options.scrollContainerId
			});
		}
	};

	floatingMenu.findSingle = function(item) {
		if (item.id) item.menu = document.getElementById(item.id);
		if (item.scrollContainerId) item.scrollContainer = document.getElementById(item.scrollContainerId);
	};

	floatingMenu.move = function (item) {
		if (!item.prohibitXMovement) {item.menu.style.left = item.nextX + 'px'; item.menu.style.right = '';}
		if (!item.prohibitYMovement) {item.menu.style.top = item.nextY + 'px'; item.menu.style.bottom = '';}
	};

	floatingMenu.scrollLeft = function(item) {
		// If floating within scrollable container use it's scrollLeft
		if (item.scrollContainer) return item.scrollContainer.scrollLeft;
		var w = window.top; return this.hasInner ? w.pageXOffset : this.hasElement
		  ? w.document.documentElement.scrollLeft : w.document.body.scrollLeft;
	};
	floatingMenu.scrollTop = function(item) {
		// If floating within scrollable container use it's scrollTop
		if (item.scrollContainer)
		return item.scrollContainer.scrollTop;
		var w = window.top; return this.hasInner ? w.pageYOffset : this.hasElement
		  ? w.document.documentElement.scrollTop : w.document.body.scrollTop;
	};
	floatingMenu.windowWidth = function() {
		return this.hasElement ? document.documentElement.clientWidth : document.body.clientWidth;
	};
	floatingMenu.windowHeight = function() {
		if (floatingMenu.hasElement && floatingMenu.hasInner) {
		// Handle Opera 8 problems
		return document.documentElement.clientHeight > window.innerHeight
			? window.innerHeight : document.documentElement.clientHeight
		}
		else {
		return floatingMenu.hasElement ? document.documentElement.clientHeight : document.body.clientHeight;
		}
	};
	floatingMenu.documentHeight = function() {
		var innerHeight = this.hasInner ? window.innerHeight : 0;
		var body = document.body, html = document.documentElement;
		return Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight,
		html.scrollHeight, html.offsetHeight, innerHeight);
	};
	floatingMenu.documentWidth = function() {
		var innerWidth = this.hasInner ? window.innerWidth : 0;
		var body = document.body, html = document.documentElement;
		return Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth,
		innerWidth);
	};
	floatingMenu.calculateCornerX = function(item) {
		var offsetWidth = item.menu.offsetWidth;
		if (item.centerX)
		return this.scrollLeft(item) + (this.windowWidth() - offsetWidth)/2;
		var result = this.scrollLeft(item) - item.parentLeft;
		if (item.targetLeft == undefined) {result += this.windowWidth() - item.targetRight - offsetWidth;}
		else {result += item.targetLeft;}
		if (document.body != item.menu.parentNode && result + offsetWidth >= item.confinedWidthReserve)
		{result = item.confinedWidthReserve - offsetWidth;}
		if (result < 0) result = 0;
		return result;
	};
	floatingMenu.calculateCornerY = function(item) {
		var offsetHeight = item.menu.offsetHeight;
		if (item.centerY) return this.scrollTop(item) + (this.windowHeight() - offsetHeight)/2;
		var result = this.scrollTop(item) - item.parentTop;
		if (item.targetTop === undefined) {result += this.windowHeight() - item.targetBottom - offsetHeight;}
		else {result += item.targetTop;}

		if (document.body != item.menu.parentNode && result + offsetHeight >= item.confinedHeightReserve) {
		result = item.confinedHeightReserve - offsetHeight;
		}

		if (result < 0) result = 0;
		return result;
	};
	floatingMenu.computeParent = function(item) {
		if (item.ignoreParentDimensions) {
		item.confinedHeightReserve = this.documentHeight(); item.confinedWidthReserver = this.documentWidth();
		item.parentLeft = 0; item.parentTop = 0; return;
		}
		var parentNode = item.menu.parentNode; var parentOffsets = this.offsets(parentNode, item);
		item.parentLeft = parentOffsets.left; item.parentTop = parentOffsets.top;
		item.confinedWidthReserve = parentNode.clientWidth;

		// We could have absolutely-positioned DIV wrapped
		// inside relatively-positioned. Then parent might not
		// have any height. Try to find parent that has
		// and try to find whats left of its height for us.
		var obj = parentNode; var objOffsets = this.offsets(obj, item);
		while (obj.clientHeight + objOffsets.top < item.menu.offsetHeight + parentOffsets.top) {
		obj = obj.parentNode; objOffsets = this.offsets(obj, item);
		}
		item.confinedHeightReserve = obj.clientHeight - (parentOffsets.top - objOffsets.top);
	};
	floatingMenu.offsets = function(obj, item)
	{
		var result = {left: 0, top: 0};
		if (obj === item.scrollContainer) return;
		while (obj.offsetParent && obj.offsetParent != item.scrollContainer) {
		result.left += obj.offsetLeft; result.top += obj.offsetTop; obj = obj.offsetParent;
		}
		if (window == window.top) return result;

		// we are IFRAMEd
		var iframes = window.top.document.body.getElementsByTagName('IFRAME');
		for (var i = 0; i < iframes.length; i++)
		{
		if (iframes[i].contentWindow != window) continue;
		obj = iframes[i];
		while (obj.offsetParent) {
			result.left += obj.offsetLeft; result.top += obj.offsetTop; obj = obj.offsetParent;
		}
		}
		return result;
	};
	floatingMenu.doFloatSingle = function(item) {
		this.findSingle(item); var stepX, stepY; this.computeParent(item);
		var cornerX = this.calculateCornerX(item); var stepX = (cornerX - item.nextX) * item.distance;
		if (Math.abs(stepX) < .5 && item.snap || Math.abs(cornerX - item.nextX) == 1) {
		stepX = cornerX - item.nextX;
		}
		var cornerY = this.calculateCornerY(item);
		var stepY = (cornerY - item.nextY) * item.distance;
		if (Math.abs(stepY) < .5 && item.snap || Math.abs(cornerY - item.nextY) == 1) {
		stepY = cornerY - item.nextY;
		}
		if (Math.abs(stepX) > 0 || Math.abs(stepY) > 0) {
		item.nextX += stepX; item.nextY += stepY; this.move(item);
		}
	};
	floatingMenu.fixTargets = function() {};
	floatingMenu.fixTarget = function(item) {};
	floatingMenu.doFloat = function() {
		this.fixTargets();
		for (var i=0; i < floatingArray.length; i++) {
		this.fixTarget(floatingArray[i]); this.doFloatSingle(floatingArray[i]);
		}
		setTimeout('floatingMenu.doFloat()', 20);
	};
	floatingMenu.insertEvent = function(element, event, handler) {
		// W3C
		if (element.addEventListener != undefined) {
		element.addEventListener(event, handler, false); return;
		}
		var listener = 'on' + event;
		// MS
		if (element.attachEvent != undefined) {
		element.attachEvent(listener, handler);
		return;
		}
		// Fallback
		var oldHandler = element[listener];
		element[listener] = function (e) {
			e = (e) ? e : window.event;
			var result = handler(e);
			return (oldHandler != undefined)
			&& (oldHandler(e) == true)
			&& (result == true);
		};
	};

	floatingMenu.init = function() {
		floatingMenu.fixTargets();
		for (var i=0; i < floatingArray.length; i++) {
		floatingMenu.initSingleMenu(floatingArray[i]);
		}
		setTimeout('floatingMenu.doFloat()', 100);
	};
	// Some browsers init scrollbars only after
	// full document load.
	floatingMenu.initSingleMenu = function(item) {
		this.findSingle(item); this.computeParent(item); this.fixTarget(item); item.nextX = this.calculateCornerX(item);
		item.nextY = this.calculateCornerY(item); this.move(item);
	};
	floatingMenu.insertEvent(window, 'load', floatingMenu.init);

	// Register ourselves as jQuery plugin if jQuery is present
	if (typeof(jQuery) !== 'undefined') {
		(function ($) {
		$.fn.addFloating = function(options) {
			return this.each(function() {
			floatingMenu.add(this, options);
			});
		};
		}) (jQuery);
	}
	</script>";
 };
}


// =====================
// Dashboard Feed Widget
// =====================

// Add the Dashboard Feed Widget
// -----------------------------
$vrequesturi = $_SERVER['REQUEST_URI'];
if ( (preg_match('|index.php|i', $vrequesturi))
  || (substr($vrequesturi,-(strlen('/wp-admin/'))) == '/wp-admin/')
  || (substr($vrequesturi,-(strlen('/wp-admin/network'))) == '/wp-admin/network/') ) {
	if (!has_action('wp_dashboard_setup','wqhelper_add_dashboard_feed_widget')) {
		add_action('wp_dashboard_setup', 'wqhelper_add_dashboard_feed_widget');
	}
}

// Load the Dashboard Feeds
// ------------------------
$vfuncname = 'wqhelper_add_dashboard_feed_widget_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
	global $wp_meta_boxes, $current_user;
	if ( (current_user_can('manage_options')) || (current_user_can('install_plugins')) ) {
		// 1.6.1: fix to undefined index warning
		$vwordquestloaded = false; $vpluginreviewloaded = false;
		foreach (array_keys($wp_meta_boxes['dashboard']['normal']['core']) as $vname) {
			if ($vname == 'wordquest') {$vwordquestloaded = true;}
			if ($vname == 'pluginreview') {$vpluginreviewloaded = true;}
		}
		if (!$vwordquestloaded) {wp_add_dashboard_widget('wordquest','WordQuest Alliance','wqhelper_dashboard_feed_widget');}
		if (!$vpluginreviewloaded) {wp_add_dashboard_widget('pluginreview','Plugin Review Network','wqhelper_pluginreview_feed_widget');}

		// add the dashboard feed javascript (once only)
		if (!has_action('admin_footer','wqhelper_dashboard_feed_javascript')) {
			add_action('admin_footer','wqhelper_dashboard_feed_javascript');
		}
	}
 };
}

// WordQuest Dashboard Feed Javascript
// -----------------------------------
$vfuncname = 'wqhelper_dashboard_feed_javascript_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {
	echo "<script language='javascript' type='text/javascript'>
	function doloadfeedcat(namespace,siteurl) {
		var selectelement = document.getElementById(namespace+'catselector');
		var catslug = selectelement.options[selectelement.selectedIndex].value;
		var siteurl = encodeURIComponent(siteurl);
		document.getElementById('feedcatloader').src='admin-ajax.php?action=wqhelper_load_feed_category&category='+catslug+'&namespace='+namespace+'&siteurl='+siteurl;
	}
	</script>";
	echo "<iframe src='javascript:void(0);' id='feedcatloader' style='display:none;'></iframe>";
 };
}

// WordQuest Dashboard Feed Widget
// -------------------------------
$vfuncname = 'wqhelper_dashboard_feed_widget_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {

	echo "<style>.feedlink {text-decoration:none;} .feedlink:hover {text-decoration:underline;}</style>";

	// maybe Get Latest Release info
	// -----------------------------
	// $vplugininfo = wqhelper_get_plugin_info();
	// if (is_array($vplugininfo)) {
	//	foreach ($vplugininfo as $vplugin) {
	//		if (isset($vplugin['slug''])) {
	//			if ( (isset($vplugin['latestrelease'])) && ($vplugin['latestrelease'] == 'yes') ) {
	//				$vlatestrelease['slug'] = $vplugin['slug'];
	//				if (isset($vplugin['title'])) {$vlatestrelease['title'] = $vplugin['title'];}
	//				if (isset($vplugin['home'])) {$vlatestrelease['home'] = $vplugin['home'];}
	//				if (isset($vplugin['description'])) {$vlatestrelease['description'] = $vplugin['description'];}
	//				if (isset($vplugin['icon'])) {$vlatestrelease['icon'] = $vplugin['icon'];}
	//				if (isset($vplugin['cats'])) {$vlatestrelease['cats'] = $vplugin['cats'];}
	//				if (isset($vplugin['tags'])) {$vlatestrelease['tags'] = $vplugin['tags'];}
	//				if (isset($vplugin['paidplans'])) {$vlatestrelease['paidplans'] = $vplugin['paidplans'];}
	//				if (isset($vplugin['package'])) {$vlatestrelease['package'] = $vplugin['package'];}
	//			}
	//		}
	//	}
	// }

	// maybe Display Latest Release Info
	// ---------------------------------
	// if (is_array($vlatestrelease)) {
	//  echo "<b>Latest Plugin Release</b><br>";
	//	echo "<table><tr><td><img src='".$vlatestrelease['icon']."'></td><td width='10'></td>";
	//	echo "<td>".$vlatestrelease['description']."<br><br>";
	//	echo "<a href='".$vlatestrelease['home']."' target=_blank>&rarr; ".$vlatestrelease['title']."</a>";
	//	echo "</td></tr></table>";
	// }

	// Load WordQuest Feed
	// -------------------
	$vbaseurl = "http://"."wordquest.org";
	$vrssurl = $vbaseurl."/feed/";
	$vfeed = trim(get_transient('wqhelper_feed'));
	if ( (!$vfeed) || ($vfeed == '') ) {
		$vrssfeed = fetch_feed($vrssurl);
		$vfeeditems = 5;
		$vargs = array($vrssfeed,$vfeeditems);
		$vfeed = wqhelper_process_rss_feed($vargs);
		if ($vfeed != '') {set_transient('wordquest_feed',$vfeed,(24*60*60));}
	}
	echo "<div id='wqnewsdisplay'>";
	if ($vfeed != '') {echo "<b>WordQuest Alliance</b><br>".$vfeed."<div align='right'>&rarr;<a href='".$vbaseurl."/blog/' class='feedlink' target=_blank> More...</a></div>";}
	else {echo "Feed Currently Unavailable.<br>"; delete_transient('wordquest_feed');}
	echo "</div>";

	// Category Feed Selection
	// -----------------------
	$vpluginsurl = $vbaseurl."/?get_post_categories=yes";

	// refresh once a day only to limit downloads
	$vcategorylist = trim(get_transient('wordquest_feed_cats'));
	if ( (!$vcategorylist) || ($vcategorylist == '') ) {
		$vargs = array('timeout' => 10);
		$vgetcategorylist = wp_remote_get($vpluginsurl,$vargs);
		if (!is_wp_error($vgetcategorylist)) {
			$vcategorylist = $vgetcategorylist['body'];
			if ($vcategorylist) {set_transient('wordquest_feed_cats',$vcategorylist,(24*60*60));}
		}
	}

	if (strstr($vcategorylist,"::::")) {
		$vcategories = explode("::::",$vcategorylist);
		if (count($vcategories) > 0) {
			$vi = 0;
			foreach ($vcategories as $vcategory) {
				$vcatinfo = explode("::",$vcategory);
				$vcats[$vi]['name'] = $vcatinfo[0];
				$vcats[$vi]['slug'] = $vcatinfo[1];
				$vcats[$vi]['count'] = $vcatinfo[2];
				$vi++;
			}

			if (count($vcats) > 0) {
				echo "<table><tr><td><b>Category:</b></td>";
				echo "<td width='7'></td>";
				echo "<td><select id='wqcatselector' onchange='doloadfeedcat(\"wq\",\"http://"."wordquest.org\");'>";
				// echo "<option value='news' selected='selected'>WordQuest News</option>";
				foreach ($vcats as $vcat) {
					echo "<option value='".$vcat['slug']."'";
					if ($vcat['slug'] == 'news') {echo " selected='selected'";}
					echo ">".$vcat['name']." (".$vcat['count'].")</option>";
				}
				echo "</select></td></tr></table>";
				echo "<div id='wqfeeddisplay'></div>";
			}
		}
	}
 };
}

// Plugin Review Network Feed Widget
// ---------------------------------
$vfuncname = 'wqhelper_pluginreview_feed_widget_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {

	echo "<style>.feedlink {text-decoration:none;} .feedlink:hover {text-decoration:underline;}</style>";

	// Load PRN Feed
	// -------------
	$vbaseurl = "http://pluginreview.net";
	$vrssurl = $vbaseurl."/feed/";
	$vfeed = trim(get_transient('prn_feed'));
	if ( (!$vfeed) || ($vfeed == '') ) {
		$vrssfeed = fetch_feed($vrssurl);
		$vfeeditems = 5;
		$vargs = array($vrssfeed,$vfeeditems);
		$vfeed = wqhelper_process_rss_feed($vargs);
		if ($vfeed != '') {set_transient('prn_feed',$vfeed,(24*60*60));}
	}
	echo "<div id='prnnewsdisplay'>";
	if ($vfeed != '') {echo "<b>Plugin Review Network</b><br>".$vfeed."<div align='right'>&rarr;<a href='".$vbaseurl."/blog/' class='feedlink' target=_blank> More...</a></div>";}
	else {echo "Feed Currently Unavailable<br>"; delete_transient('prn_feed');}
	echo "</div>";

	// Category Feed Selection
	// -----------------------
	$vbaseurl = "http://pluginreview.net";
	$vcategoryurl = $vbaseurl."/?get_review_categories=yes";

	// refresh once a day only to limit downloads
	$vcategorylist = trim(get_transient('prn_feed_cats'));
	if ( (!$vcategorylist) || ($vcategorylist == '') ) {
		$vargs = array('timeout' => 10);
		$vgetcategorylist = wp_remote_get($vcategoryurl,$vargs);
		if (!is_wp_error($vgetcategorylist)) {
			$vcategorylist = $vgetcategorylist['body'];
			if ($vcategorylist) {set_transient('prn_feed_cats',$vcategorylist,(24*60*60));}
		}
	}

	if (strstr($vcategorylist,"::::")) {
		$vcategories = explode("::::",$vcategorylist);
		if (count($vcategories) > 0) {
			$vi = 0;
			foreach ($vcategories as $vcategory) {
				$vcatinfo = explode("::",$vcategory);
				$vcats[$vi]['name'] = $vcatinfo[0];
				$vcats[$vi]['slug'] = $vcatinfo[1];
				$vcats[$vi]['count'] = $vcatinfo[2];
				$vi++;
			}

			if (count($vcats) > 0) {
				echo "<table><tr><td><b>Review Category:</b></td>";
				echo "<td width='7'></td>";
				echo "<td><select id='prncatselector' onchange='doloadfeedcat(\"prn\",\"http://pluginreview.net\");'>";
				// echo "<option value='reviews' selected='selected'>Plugin Reviews</option>";
				foreach ($vcats as $vcat) {
					echo "<option value='".$vcat['slug']."'";
					if ($vcat['slug'] == 'reviews') {echo " selected='selected'";}
					echo ">".$vcat['name']." (".$vcat['count'].")</option>";
				}
				echo "</select></td></tr></table>";
				echo "<div id='prnfeeddisplay'></div>";
			}
		}
	}
 };
}

// Load Category Feed
// ------------------
$vfuncname = 'wqhelper_load_feed_category_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function() {

	$vnamespace = $_GET['namespace'];
	$vbaseurl = $_GET['siteurl'];
	$vcatslug = $_GET['category'];

	$vcategoryurl = $vbaseurl."/category/".$vcatslug."/feed/";
	echo $vcategoryurl;
	$vmorelink = "<div align='right'>&rarr; <a href='".$vbaseurl."/category/".$vcatslug."/' style='feedlink' target=_blank> More...</a></div>";

	$vcategoryrss = @fetch_feed($vcategoryurl);
	$vfeeditems = 10;

	// Process the Feed
	// ----------------
	$vargs = array($vcategoryrss,$vfeeditems);
	$vcategoryfeed = wqhelper_process_rss_feed($vargs);
	if ($vcategoryfeed != '') {$vcategoryfeed .= $vmorelink;}

	echo '<script language="javascript" type="text/javascript">
	var categoryfeed = "'.$vcategoryfeed.'";
	parent.document.getElementById("'.$vnamespace.'feeddisplay").innerHTML = categoryfeed;
	</script>';

	exit;
 };
}

// Process RSS Feed
// ----------------
$vfuncname = 'wqhelper_process_rss_feed_'.$wqhv;
if ( (!isset($wqfunctions[$vfuncname])) || (!is_callable($wqfunctions[$vfuncname])) ) {
 $wqfunctions[$vfuncname] = function($vargs) {

	$vrss = $vargs[0]; $vfeeditems = $vargs[1]; $vprocessed = '';

	if (is_wp_error($vrss)) {return '';}

	$vmaxitems = $vrss->get_item_quantity($vfeeditems);
	$vrssitems = $vrss->get_items(0,$vmaxitems);

	if ($vmaxitems == 0) {$vprocessed = "";}
	else {
		$vprocessed = "<ul style='list-style:none;'>";
		foreach ($vrssitems as $vitem) {
			$vprocessed .= "<li>&rarr; <a href='".esc_url($vitem->get_permalink())."' class='feedlink' target='_blank' ";
			$vprocessed .= "title='Posted ".$vitem->get_date('j F Y | g:i a')."'>";
			$vprocessed .= esc_html($vitem->get_title())."</a></li>";
		}
		$vprocessed .= "</ul>";
	}

	// echo "***".$vprocessed."***";
	return $vprocessed;
 };
}


// CLOSE VERSION COMPARE WRAPPER FOR PHP 5.3 REQUIRED
// --------------------------------------------------
}

// debug point
// print_r($wqfunctions);

?>