<?php

/*
Plugin Name: AutoSave Net
Plugin URI: http://wordquest.org/plugins/autosave-net/
Description: Auto-save safety net! Timed Backup of your Post Content while writing, with instant compare and restore content metabox.
Version: 1.3.2
Author: Tony Hayes
Author URI: http://dreamjester.net
GitHub Plugin URI: majick777/autosave-net
@fs_premium_only pro-functions.php
*/

if (!function_exists('add_action')) {exit;}

/*
// TODO: better handling of visual/text editor view content?
// TODO: ability to compare to copy and pasted textarea Content?
// TODO: ability to compare to latest Wordpress AutoSave Content?
// TODO: ability to compare to earlier AutoSave Revisions Content?
*/

// --------------------
// === Setup Plugin ===
// --------------------

// -----------------
// Set Plugin Values
// -----------------
global $wordquestplugins;
$vslug = $vasnslug = 'autosave-net';
$wordquestplugins[$vslug]['version'] = $vautosavenetversion = '1.3.2';
$wordquestplugins[$vslug]['title'] = 'AutoSave Net';
$wordquestplugins[$vslug]['namespace'] = 'autosave_net';
$wordquestplugins[$vslug]['settings'] = 'asn';
$wordquestplugins[$vslug]['hasplans'] = false;
$wordquestplugins[$vslug]['wporgslug'] = 'autosave-net';
define('AUTOSAVENET_VERSION',$vautosavenetversion); // for theme
define('QUICKSAVE_VERSION',$vautosavenetversion); // back compat

// ------------------------
// Check for Update Checker
// ------------------------
// note: lack of updatechecker.php file indicates WordPress.Org SVN version
// presence of updatechecker.php indicates site download or GitHub version
$vfile = __FILE__; $vupdatechecker = dirname($vfile).'/updatechecker.php';
if (!file_exists($vupdatechecker)) {$wordquestplugins[$vslug]['wporg'] = true;}
else {include($vupdatechecker); $wordquestplugins[$vslug]['wporg'] = false;}

// -----------------------------------
// Load WordQuest Helper/Pro Functions
// -----------------------------------
if (is_admin()) {$wordquest = dirname(__FILE__).'/wordquest.php'; if (file_exists($wordquest)) {include($wordquest);} }
$vprofunctions = dirname(__FILE__).'/pro-functions.php';
if (file_exists($vprofunctions)) {include($vprofunctions); $wordquestplugins[$vslug]['plan'] = 'premium';}
else {$wordquestplugins[$vslug]['plan'] = 'free';}

// -----------------
// Load Freemius SDK
// -----------------
function asn_freemius($vslug) {
    global $wordquestplugins, $asn_freemius;
    $vwporg = $wordquestplugins[$vslug]['wporg'];
    if ($wordquestplugins[$vslug]['plan'] == 'premium') {$vpremium = true;} else {$vpremium = false;}
    $vhasplans = $wordquestplugins[$vslug]['hasplans'];

	// redirect for support forum
	if ( (is_admin()) && (isset($_REQUEST['page'])) ) {
		if ($_REQUEST['page'] == $vslug.'-wp-support-forum') {
			if(!function_exists('wp_redirect')) {include(ABSPATH.WPINC.'/pluggable.php');}
			wp_redirect('http://wordquest.org/quest/quest-category/plugin-support/'.$vslug.'/'); exit;
		}
	}

    if (!isset($asn_freemius)) {
        if (!class_exists('Freemius')) {require_once(dirname(__FILE__).'/freemius/start.php');}

		$asn_settings = array(
            'id'                => '146',
            'slug'              => $vslug,
            'public_key'        => 'pk_4c378ea656ccc7fb19bb6227eecca',
            'is_premium'        => $vpremium,
            'has_addons'        => false,
            'has_paid_plans'    => $vhasplans,
            'is_org_compliant'  => $vwporg,
            'menu'              => array(
                'slug'       	=> $vslug,
                'first-path' 	=> 'admin.php?page='.$vslug.'&welcome=true',
                'parent'		=> array('slug'=>'wordquest'),
                'contact'		=> $vpremium,
                // 'support'    => false,
                // 'account'    => false,
            )
        );
    	$asn_freemius = fs_dynamic_init($asn_settings);
    }
    return $asn_freemius;
}
// Initialize Freemius
$asn_freemius = asn_freemius($vslug);

// Custom Freemius Connect Message
function asn_freemius_connect($message, $user_first_name, $plugin_title, $user_login, $site_link, $freemius_link) {
	return sprintf(
		__fs('hey-x').'<br>'.
		__('If you want to more easily provide feedback for this plugins features and functionality, %s can connect your user, %s at %s, to %s', 'autosave-net'),
		$user_first_name, '<b>'.$plugin_title.'</b>', '<b>'.$user_login.'</b>', $site_link, $freemius_link
	);
}
$asn_freemius->add_filter('connect_message', 'asn_freemius_connect', WP_FS__DEFAULT_PRIORITY, 6);

// ---------------
// Add Admin Menus
// ---------------
if (is_admin()) {add_action('admin_menu','asn_settings_menu',1);}
function asn_settings_menu() {
	if (empty($GLOBALS['admin_page_hooks']['wordquest'])) {
		$vicon = plugins_url('images/wordquest-icon.png',__FILE__); $vposition = apply_filters('wordquest_menu_position','3');
		add_menu_page('WordQuest Alliance', 'WordQuest', 'manage_options', 'wordquest', 'wqhelper_admin_page', $vicon, $vposition);
	}
	add_submenu_page('wordquest', 'AutoSave Net', 'AutoSave Net', 'manage_options', 'autosave-net', 'autosave_net_options_page');
	// add_options_page('AutoSave Net', 'AutoSave Net', 'manage_options', 'autosave-net', 'autosave_net_options_page');

	// Add icons and styling to the plugin submenu :-)
	add_action('admin_footer','asn_admin_javascript');
	function asn_admin_javascript() {
		global $vasnslug; $vslug = $vasnslug; $vcurrent = '0';
		$vicon = plugins_url('images/icon.png',__FILE__);
		if (isset($_REQUEST['page'])) {if ($_REQUEST['page'] == $vslug) {$vcurrent = '1';} }
		echo "<script>jQuery(document).ready(function() {if (typeof wordquestsubmenufix == 'function') {
		wordquestsubmenufix('".$vslug."','".$vicon."','".$vcurrent."');} });</script>";
	}

	// Plugin Page Settings Link
	add_filter('plugin_action_links', 'asn_plugin_action_links', 10, 2);
	function asn_plugin_action_links($vlinks, $vfile) {
		global $vasnslug;
		$vthisplugin = plugin_basename(__FILE__);
		if ($vfile == $vthisplugin) {
			$vsettingslink = "<a href='".admin_url()."admin.php?page=".$vasnslug."'>Settings</a>";
			array_unshift($vlinks, $vsettingslink);
		}
		return $vlinks;
	}
}


// -----------------
// Options / Filters
// -----------------

// use these filters to adjust autosave/revision limit on a conditional basis
// 'autosave_disable' (return 0 or 1)
// 'limit_post_revisions' (return empty or number)
// note: since AUTOSAVE_INTERVAL is a constant no filter has been made for it

// The default QuickSave Timer cycle is 60 seconds. To adjust use the filter:
// 'quicksave_timer' (return a value in seconds)

// By default Quicksave is active for all post types (including Custom Post Types.)
// To adjust the post types for which QuickSave is active use the filter:
// 'quicksave_post_types' (return an array of post types)

// By default Quicksave 'backup found' top admin notice is for post.php and edit.php
// For other post types which have their own edit screen, the notice is displayed
// in the Quicksave metabox, but you may want to move it to the top of screen.
// To adjust the screens for which Quicksave top admin notice is active use the filter:
// 'quicksave_notice_screens' (return an array of .php admin values)

// Quicksave metabox can be conditionally disabled by filter.
// 'quicksave_disabler' (return 1 to disable)


// Load Plugin Options Global
// --------------------------
// 1.2.5: use global plugin option
global $vasnoptions; $vasnoptions = get_option('autosave_net');

// Get Plugin Option Helper
// ------------------------
function autosave_net_get_option($vkey,$vfilter=false) {
	global $vasnoptions;
	if (isset($vasnoptions[$vkey])) {
		if ($vfilter) {return apply_filters($vkey,$vasnoptions[$vkey]);}
		else {return $vasnoptions[$vkey];}
	} else {
		// 1.3.1: fallback to default options
		$vdefaults = autosave_net_default_options();
		if (isset($vdefaults[$vkey])) {return $vdefaults[$vkey];}
		else {return '';}
	}
}

// Set Defaults on Activation
// --------------------------
register_activation_hook(__FILE__,'autosave_net_add_options');
function autosave_net_add_options() {

	// 1.2.5: use global options array
	global $vasnoptions;
	// 1.3.1: use default options function
	// 1.3.2: fix to default function typo
	$vasnoptions = autosave_net_default_options();
	add_option('autosave_net',$vasnoptions);

	if (file_exists(dirname(__FILE__).'/updatechecker.php')) {$vadsboxoff = '';} else {$vadsboxoff = 'checked';}
	$sidebaroptions = array('adsboxoff'=>$vadsboxoff,'donationboxoff'=>'','reportboxoff'=>'','installdate'=>date('Y-m-d'));
	add_option('asn_sidebar_options',$sidebaroptions);
}

// get Default Options
// -------------------
// 1.3.1: separate defaults function
function autosave_net_default_options() {
	$vasnoptions['quicksave_timer'] = '60';
	$vasnoptions['quicksave_post_types'] = array('post','page');
	$vasnoptions['quicksave_notice_screens'] = array('post.php','edit.php');
	$vasnoptions['quicksave_icons'] = 'colour';

	$vasnoptions['autosave_disable'] = '0';
	$vasnoptions['autosave_revisions'] = '';
	$vasnoptions['autosave_time'] = '300';

	return $vasnoptions;
}

// Update Options
// --------------
if ( (isset($_POST['autosave_net_save_options'])) && ($_POST['autosave_net_save_options'] == 'yes') ) {
	add_action('init','autosave_net_update_options');
}

// 1.3.0: added option validation
function autosave_net_update_options() {

	if (!current_user_can('manage_options')) {return;}

	// 1.2.5: use global options array
	global $vasnoptions;

	// 1.2.5: verify nonce field
	check_admin_referer('autosave_net');

	// QuickSave Timer
	$vquicksavetimer = $_POST['quicksave_timer'];
	if ( (!is_numeric($vquicksavetimer)) || ($vquicksavetimer < 1) ) {$vquicksavetimer = 60;}
	$vasnoptions['quicksave_timer'] = $vquicksavetimer;

	// Quicksave Post Types
	$vcpts[0] = 'page'; $vcpts[1] = 'post';
	$vargs = array('public' => false, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vdefaultcpts = array_merge($vcpts,$vcptlist);
	$vposttypearray = array();
	foreach ($vdefaultcpts as $vcpt) {
		if (isset($_POST['quicksave_type_'.$vcpt])) {
			if ($_POST['quicksave_type_'.$vcpt] == '1') {
				$vposttypearray[] = $vcpt;
			}
		}
	}
	$vasnoptions['quicksave_post_types'] = $vposttypearray;

	// QuickSave Notice Screens
	// 1.2.5: fix this save option
	$vnoticescreens = $_POST['quicksave_notice_screens'];
	if (strstr($vnoticescreens,',')) {$vscreens = explode(',',$vnoticescreens);}
	else {$vscreens[0] = trim($vnoticescreens);}
	$vasnoptions['quicksave_notice_screens'] = $vscreens;

	// QuickSave Icons
	$vquicksaveicons = $_POST['quicksave_icons'];
	if ( ($vquicksaveicons != 'dash') && ($vquicksaveicons != 'colour') ) {$vquicksaveicons = 'colour';}
	$vasnoptions['quicksave_icons'] = $vquicksaveicons;

	// WordPress AutoSave options
	if ( (isset($_POST['autosave_disable'])) && ($_POST['autosave_disable'] == '1') ) {$vasnoptions['autosave_disable'] = '1';}
	else {$vasnoptions['autosave_disable'] = '';}
	$vautosaverevisions = $_POST['autosave_revisions'];
	if (!is_numeric($vautosaverevisions)) {$vautosaverevisions = '';}
	$vasnoptions['autosave_revisions'] = $vautosaverevisions;
	$vautosavetime = $_POST['autosave_time'];
	if (!is_numeric($vautosavetime)) {$vautosavetime = '';}
	$vasnoptions['autosave_time'] = $vautosavetime;

	update_option('autosave_net',$vasnoptions);
}


// Options Page
// ------------
function autosave_net_options_page() {

	// 1.2.5: use global option array
	global $vautosavenetversion, $vasnoptions, $vasnslug;

	echo "<div id='pagewrap' class='wrap' style='width:100%;margin-right:0px !important;'>";

	// Admin Notices Boxer
	// -------------------
	if (function_exists('wqhelper_admin_notice_boxer')) {wqhelper_admin_notice_boxer();} else {echo "<h2> </h2>";}

	// Sidebar Floatbox
	// ----------------
	global $vautosavenetversion;
	// $vargs = array('asn','autosave-net','free','autosave-net','yes','AutoSave Net',$vautosavenetversion);
	$vargs = array('autosave-net','yes'); // trimmed settings
	if (function_exists('wqhelper_sidebar_floatbox')) {
		wqhelper_sidebar_floatbox($vargs);

		// 1.3.0: replace floatbox with stickykit
		echo wqhelper_sidebar_stickykitscript();
		echo "<style>#floatdiv {float:right;}</style>";
		echo '<script>jQuery("#floatdiv").stick_in_parent();
		wrapwidth = jQuery("#pagewrap").width(); sidebarwidth = jQuery("#floatdiv").width();
		newwidth = wrapwidth - sidebarwidth;
		jQuery("#wrapbox").css("width",newwidth+"px");
		jQuery("#adminnoticebox").css("width",newwidth+"px");
		</script>';

		// echo wqhelper_sidebar_floatmenuscript();
		// echo '<script language="javascript" type="text/javascript">
		// floatingMenu.add("floatdiv", {targetRight: 10, targetTop: 20, centerX: false, centerY: false});
		// function move_upper_right() {
		//	floatingArray[0].targetTop=20;
		//	floatingArray[0].targetBottom=undefined;
		//	floatingArray[0].targetLeft=undefined;
		//	floatingArray[0].targetRight=10;
		//	floatingArray[0].centerX=undefined;
		//	floatingArray[0].centerY=undefined;
		// }
		// move_upper_right();</script>';
	}

	// Plugin Page Title
	// -----------------
	$viconurl = plugins_url("images/autosave-net.png",__FILE__);
	echo "<table><tr><td><img src='".$viconurl."'></td>";
	echo "<td width='20'></td><td>";
		echo "<table><tr><td><h2>AutoSave Net</h2></td>";
		echo "<td width='20'></td>";
		echo "<td><h3><i>v".$vautosavenetversion."</i></h3></td></tr>";
		echo "<tr><td colspan='3' align='center'>".__('by','autosave-net');
		echo " <a href='http://wordquest.org/' style='text-decoration:none;' target=_blank><b>WordQuest Alliance</b></a>";
		echo "</td></tr></table>";
	echo "</td><td width='50'></td>";
	// 1.3.1: added welcome message
	if ( (isset($_REQUEST['welcome'])) && ($_REQUEST['welcome'] == 'true') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo __('Welcome! For usage see','autosave-net')." <i>readme.txt</i> FAQ</font></div></td></tr></table></td>";
	}
	if ( (isset($_REQUEST['updated'])) && ($_REQUEST['updated'] == 'yes') ) {
		echo "<td><table style='background-color: lightYellow; border-style:solid; border-width:1px; border-color: #E6DB55; text-align:center;'>";
		echo "<tr><td><div class='message' style='margin:0.25em;'><font style='font-weight:bold;'>";
		echo __('Settings Updated.','autosave-net')."</font></div></td></tr></table></td>";
	}
	echo "</tr></table><br>";

	echo "<div id='wrapbox' class='postbox' style='width:680px;line-height:2em;'><div class='inner' style='padding-left:20px;'>";

	echo "<form method='post' action='admin.php?page=".$vasnslug."&updated=yes'>";
	// 1.2.5: added nonce field
	wp_nonce_field('autosave_net');
	echo "<input type='hidden' name='autosave_net_save_options' value='yes'>";
	echo "<table cellpadding='0' cellspacing='0'>";

	// QuickSave Options
	// -----------------
	echo "<tr><td><b>QuickSave</td></tr><tr height='10'><td> </td></tr>";

	// Quicksave Interval Time
	echo "<tr height='40'><td>".__('QuickSave Interval Time','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='text' name='quicksave_timer' value='".$vasnoptions['quicksave_timer']."' size='5'></td>";
	echo "<td width='10'></td><td>(".__('global default for QuickSave Backups.','autosave-net').")</td></tr>";

	// Quicksave Post Types
	echo "<tr height='40'><td style='vertical-align:top;'>".__('QuickSave Post Types','autosave-net')."</td>";
	echo "<td width='10'></td><td>";
	$vcpts[0] = 'page'; $vcpts[1] = 'post';
	$vargs = array('public'=>false, '_builtin' => false);
	$vcptlist = get_post_types($vargs,'names','and');
	$vdefaultcpts = array_merge($vcpts,$vcptlist);
	$vquicksavetypes = $vasnoptions['quicksave_post_types'];
	foreach ($vdefaultcpts as $vcpt) {
		echo "<input type='checkbox' name='quicksave_type_".$vcpt."' value='1'";
		// 1.3.1: fix to warning when option not set
		if ( (is_array($vquicksavetypes)) && (in_array($vcpt,$vquicksavetypes)) ) {echo " checked";}
		echo "> ".strtoupper(substr($vcpt,0,1)).substr($vcpt,1,strlen($vcpt))."<br>";
	}
	echo "</td><td width='10'></td>";
	echo "<td style='vertical-align:top;'>(".__('post types to activate QuickSave for.','autosave-net').")</td></tr>";

	// Quicksave Post Screens
	$vnoticescreens = implode(',',$vasnoptions['quicksave_notice_screens']);
	echo "<tr height='40'><td>".__('QuickSave Notice Screens','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='text' name='quicksave_notice_screens' value='".$vnoticescreens."' size='20'></td>";
	echo "<td width='10'></td><td>(".__('admin screens to show QuickSave notices on.','autosave-net').")</tr>";

	echo "<tr height='40'><td>".__('QuickSave Icons','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='radio' name='quicksave_icons' value='dash'";
	if ($vasnoptions['quicksave_icons'] == 'dash') {echo " checked";}
	echo ">".__('Dashicons','autosave-net');
	echo " &nbsp; <input type='radio' name='quicksave_icons' value='colour'";
	if ($vasnoptions['quicksave_icons'] == 'colour') {echo " checked";}
	echo ">".__('Colour Icons','autosave-net');
	echo "</td><td width='10'></td>";
	echo "<td>(".__('which icons to use for QuickSave controls.','autosave-net').")</td></tr>";

	echo "<tr height='40'><td> </td></tr>";

	// WordPress AutoSave Options
	// --------------------------
	echo "<tr height='40'><td><b>".__('WordPress AutoSave','autosave-net')."</td></tr><tr height='10'><td> </td></tr>";
	echo "<tr height='40'><td>".__('Disable WordPress AutoSave','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='checkbox' name='autosave_disable' value='1'";
	if ($vasnoptions['autosave_disable'] == '1') {echo " checked";}
	echo "></td>";
	echo "<td width='10'><td>(".__('turns off WordPress AutoSave completely.','autosave-net').")</td></tr>";

	echo "<tr height='40'><td>".__('Limit AutoSave Post Revisions','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='text' name='autosave_revisions' value='".$vasnoptions['autosave_revisions']."' size='5'></td>";
	echo "<td width='10'></td><td>(".__('number of AutoSave Revisions per post.','autosave-net').")</td></tr>";

	echo "<tr height='40'><td>".__('AutoSave Interval Time','autosave-net')."</td><td width='10'></td>";
	echo "<td><input type='text' name='autosave_time' value='".$vasnoptions['autosave_time']."' size='5'></td>";
	echo "<td width='10'></td><td>(".__('time in seconds between WordPress AutoSaves','autosave-net').")</td></tr>";

	echo "<tr height='20'><td> </td></tr>";
	echo "<tr><td colspan='2'></td><td align='center'>";
	echo "<input type='submit' class='button-primary' id='plugin-settings-save' value='".__('Save Settings','autosave-net')."'>";
	echo "</td></tr></table><br><br>";
	echo "</form>";

	echo '</div></div>'; // close wrapbox
	echo '</div>'; // close wrap
}

// ==================
// WORDPRESS AUTOSAVE
// ==================

// Handle Wordpress AutoSave Options
// ---------------------------------
add_action('plugins_loaded', 'autosave_net_wordpress_autosave');
function autosave_net_wordpress_autosave() {

	// 1.2.5: get filtered plugin options for wordpress autosave
	$vautosavedisable = autosave_net_get_option('autosave_net_autosave_disable',true);
	$vautosaverevisions = autosave_net_get_option('autosave_revisions',true);
	$vautosavetime = (int)autosave_net_get_option('autosave_time',true);

	if ( ($vautosavetime > 0) && (!defined('AUTOSAVE_INTERVAL')) ) {define('AUTOSAVE_INTERVAL', $vautosavetime);}

	if ( ($vautosavedisable != '1') && (!empty($vautosaverevisions)) && ($vautosaverevisions > 0) ) {
		// 1.2.5: fix to incorrect function name call
        add_filter('wp_revisions_to_keep', 'autosave_net_limit_post_revisions');
	} else {
        add_filter('wp_revisions_to_keep', 'autosave_net_no_post_revisions');
		add_action('admin_enqueue_scripts', 'autosave_net_dequeue_autosave');
	}
}

// Limit Wordpress AutoSave Post Revisions
// ---------------------------------------
function autosave_net_limit_post_revisions() {return autosave_net_get_option('autosave_revisions');}
function autosave_net_no_post_revisions() {return 0;}
function autosave_net_dequeue_autosave() {wp_dequeue_script('autosave');}


// ================
// QUICKSAVE BACKUP
// ================

// Get Just Post Content Helper
// ----------------------------
function autosave_net_get_just_post_content($vpostid) {
	global $wpdb;
	$vselect = "SELECT post_content FROM ".$wpdb->prefix."posts WHERE ID = '".$vpostid."'";
	$vpostcontent = $wpdb->get_var($vselect);
	return $vpostcontent;
}

// Add Quicksave Metabox
// ---------------------
add_action('admin_init','autosave_net_quicksave_add_metabox');
function autosave_net_quicksave_add_metabox() {
	$vcpts = autosave_net_get_option('quicksave_post_types');
	// 1.3.1: fix to default array fallback
	if (!is_array($vcpts)) {$vcpts = array('post','page');}
	foreach ($vcpts as $vcpt) {
		add_meta_box('autosave_net_metabox', __('QuickSave Content Backup Timer','autosave-net'), 'autosave_net_quicksave_metabox', $vcpt, 'normal', 'low');
	}
	add_action('admin_notices','autosave_net_quicksave_notice');
}

// possible top of post screen Admin notice
// ----------------------------------------
function autosave_net_quicksave_notice() {
	global $pagenow; $vchecknotice = '';
	// post.php and edit.php screens by default
	$vpostscreens = autosave_net_get_option('quicksave_notice_screens',true);
	if (!is_array($vpostscreens)) {$vpostscreens = $vdefaultpostscreens;}
	if (count($vpostscreens) > 0) {
		foreach ($vpostscreens as $vpostscreen) {
			if ($pagenow == $vpostscreen) {$vchecknotice = 'yes';}
		}
		if ($vchecknotice == 'yes') {autosave_net_quicksave_check_notice('adminnotice');}
	}
}

// Display Message if newer QuickSave found
// ----------------------------------------
function autosave_net_quicksave_check_notice($vcontext) {

	global $post; if (!isset($post)) {return;}
	$vpostid = $post->ID; $vposttype = get_post_type($vpostid);

	$vcpts = autosave_net_get_option('quicksave_post_types',true);
	if (!is_array($vcpts)) {return;}
	if (!in_array($vposttype,$vcpts)) {return;}

	// Compare QuickSave Timestamp with
	//   Saved Post Content Timestamp
	// --------------------------------
	$vbackuptimestamp = get_post_meta($vpostid,'_quicksavetime',true);
	$vmodifiedtimestamp = get_post_modified_time('U',false,$vpostid,false);

	if ($vbackuptimestamp > $vmodifiedtimestamp) {

		// check for a content difference
		$vbackupcontent = get_post_meta($vpostid,'_quicksavecontent',true);
		$vsavedcontent = autosave_net_get_just_post_content($vpostid);

		if ($vbackupcontent != $vsavedcontent) {

			$vbackuptime = date('H:i:s',$vbackuptimestamp);
			$vmodifiedtime = date('H:i:s',$vmodifiedtimestamp);

			// bold emphasis on the day/month/year timestamp differences
			$vbackupyear = date('Y',$vbackuptimestamp);
			$vmodifiedyear = date('Y',$vmodifiedtimestamp);
			if ($vbackupyear != $vmodifiedyear) {$vbackupyear = '<b>'.$vbackupyear.'</b>'; $vmodifiedyear = '<b>'.$vmodifiedyear.'</b>';}
			$vbackupmonth = date('F',$vbackuptimestamp);
			$vmodifiedmonth = date('F',$vmodifiedtimestamp);
			if ($vbackupmonth != $vmodifiedmonth) {$vbackupmonth = '<b>'.$vbackupmonth.'</b>'; $vmodifiedmonth = '<b>'.$vmodifiedmonth.'</b>';}
			$vbackupday = date('jS',$vbackuptimestamp);
			$vmodifiedday = date('jS',$vmodifiedtimestamp);
			if ($vbackupday != $vmodifiedday) {$vbackupday = '<b>'.$vbackupday.'</b>'; $vmodifiedday = '<b>'.$vmodifiedday.'</b>';}

			$vbackupdate = $vbackupday.' '.__('of','autosave-net').' '.$vbackupmonth.' '.$vbackupyear;
			$vbackupdisplay = '<b>'.$vbackuptime.'</b> '.__('on','autosave-net').' '.$vbackupdate;
			$vmodifieddate = $vmodifiedday.' '.__('of','autosave-net').' '.$vmodifiedmonth.' '.$vmodifiedyear;
			$vmodifieddisplay = '<b>'.$vmodifiedtime.'</b> '.__('on','autosave-net').' '.$vmodifieddate;

			$vmessage =	__('A more recent version of this post has been QuickSaved by AutoSave Net.','autosave-net');
			if ($vcontext == 'adminnotice') {
				$vmessage .= " <a id='scrolltoquicksave' href='#quicksavemetabox'>";
				$vmessage .= __('Scroll down to the QuickSave metabox to compare versions.','autosave-net')."</a><br>";
			} else {$vmessage .= "<br>";}
			$vmessage .= __('The QuickSave version was saved at','autosave-net').": ".$vbackupdisplay." ";
			$vmessage .= __('and the actual post was last saved at','autosave-net').": ".$vmodifieddisplay."<br>";
			echo "<div class='error' style='padding:5px;line-height:20px;'>".$vmessage."</div>";

			// 1.2.5: moved smooth scroll jump script to footer
			add_action('admin_footer','autosave_net_smooth_scroll_jump');

		}
	}
}

// Smooth Scroll Jump Script
// -------------------------
function autosave_net_smooth_scroll_jump() {
	// jQuery smooth scrolling onclick adjustment
	// http://css-tricks.com/snippets/jquery/smooth-scrolling/
	echo "<script>jQuery(function($) {
		$('#scrolltoquicksave').click(function() {
			if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
				var target = $(this.hash);
				target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
				if (target.length) {
					var scrollto = target.offset().top - 90;
					$('html,body').animate({scrollTop: scrollto}, 1000);
					return false;
				}
			}
		});
	});</script>";
}

// Quicksave Metabox
// -----------------
function autosave_net_quicksave_metabox() {

	global $pagenow, $post; $vpostid = $post->ID;

	// disable for post switch
	$vquicksavedisabled = get_post_meta($vpostid,'_quicksavedisabled',true);
	$vquicksavedisabled = apply_filters('quicksave_disabler',$vquicksavedisabled);
	if (!$vquicksavedisabled) {$vquicksavedisabled = '';}

	// if this is a new post, do nothing and tell user
	if ($pagenow == 'post-new.php') {
		echo __('QuickSave does not launch until you Publish - or Save a Draft and refresh.','autosave-net');
		return;
	}

	// Timer Variables
	// ---------------
	$vtimestamp = time();
	// delete_post_meta($vpostid,'_quicksavetime');
	$vbackuptime = get_post_meta($vpostid,'_quicksavetime',true);
	$vlatestcontent = '';

	// Filterable Timer Value
	// ----------------------
	$vquicksavetimer = absint(autosave_net_get_option('quicksave_timer',true));

	// a name hash scroll link
	echo '<a name="quicksavemetabox"></a>';

	// Resize Helper Wrapper
	echo '<div id="quicksavewrapper">';

	// display check message if NOT in the filtered quicksave post screens
	$vpostscreens = autosave_net_get_option('quicksave_notice_screens',true);
	if (count($vpostscreens) > 0) {
		foreach ($vpostscreens as $vpostscreen) {
			if ($pagenow == $vpostscreen) {$vchecknotice = 'yes';}
		}
		if ($vchecknotice != 'yes') {autosave_net_quicksave_check_notice('adminnotice');}
	}

	// QuickSave Info Box
	// ------------------
	// 1.2.0: fix to icon logic here
	if (file_exists(get_stylesheet_directory().'/images/quicksave-icons.png')) {$viconsurl = get_stylesheet_directory_uri().'/images/quicksave-icons.png';}
	elseif (file_exists(get_template_directory().'/images/quicksave-icons.png')) {$viconsurl = get_template_directory_uri().'/images/quicksave-icons.png';}
	else {$viconsurl = plugins_url('images/quicksave-icons.png',__FILE__);}
	// else {$viconurl = admin_url('/images/menu.png'); $vmenuicon = 'yes';}
	$vmenuicon = 'no';

	echo '<div id="quicksaveinfobox">';
	echo '<center><table><tr><td><div style="background-image:url(\''.$viconsurl.'\');';
	if ($vmenuicon == 'yes') {echo 'width:28px;height:28px;background-position:-149px -1px;';}
	else {echo 'width:32px;height:32px;background-position:0px 0px;';}
	echo '" title="Hide QuickSave Info" onclick="quicksave_show_hide(\'quicksaveinfobox\');quicksave_info(\'off\');">';
	echo '</td><td width="10"></td><td>';
	echo '<ul style="padding:0;list-style:none;display:inline-block;">';
	echo '<li style="display:inline-block;"><a href="http://wordquest.org/plugins/autosave-net/" title="AutoSave Net Content Backup Timer" target=_blank style="text-decoration:none;"><b>AutoSave Net</b></a>';
	echo ' v'.AUTOSAVENET_VERSION;
	echo ' '.__('by','autosave-net').' <a href="http://wordquest.org" target=_blank style="text-decoration:none;">WordQuest</a></li>';
	echo '<li style="display:inline-block;width:30px;"></li>';
	echo '<li style="display:inline-block;font-size:9pt;"><a href="http://wordquest.org/contribute/?plugin=autosave-net" target=_blank style="text-decoration:none;">&rarr; '.__('Contribute','autosave-net').'</a></li>';
	echo '<li style="display:inline-block;width:10px;"></li>';
	echo '<li style="display:inline-block;font-size:9pt;"><a href="http://pluginreview.net/return-visitors-report/" target=_blank style="text-decoration:none;">&rarr; '.__('Bonus Report','autosave-net').'</a></li>';
	echo '<li style="display:inline-block;width:10px;"></li>';
	echo '<li style="display:inline-block;font-size:9pt;"><a href="http://wordquest.org/plugins/" target=_blank style="text-decoration:none;">&rarr; '.__('More Free Plugins','autosave-net').'</a></li>';
	echo '</ul></td></tr></table></center></div>';

	// QuickSave Global Settings
	// -------------------------
	$vsettingsurl = admin_url('options-general.php').'?page=autosave-net';
	echo '<div id="quicksavesettings" style="display:none;"><center>';
	if (current_user_can('manage_options')) {
		echo '<a href="'.$vsettingsurl.'" target=_blank>'.__('Click here for Global QuickSave Settings on the AutoSave Net admin page.','autosave-net').'</a>.';
	} else {echo __('Contact your Site Administrator to adjust QuickSave Global Settings for AutoSave Net.','autosave-net');}
	echo '</center></div>';

	// User Controls
	// -------------
	// start table
	echo '<center><table><tr>';
	echo '<td><div id="infobutton" style="display:none;margin-right:10px;background-image:url(\''.$viconsurl.'\');';
	if ($vmenuicon == 'yes') {echo 'width:28px;height:28px; background-position:-149px -1px;';}
	else {echo 'width:32px;height:32px;background-position:0px 0px;';}
	echo '" title="Show QuickSave Info" onclick="quicksave_show_hide(\'quicksaveinfobox\');quicksave_info(\'on\');"></td>';

	// Plus/Minus Timer Adjustment
	echo '<td><table cellpadding="0" cellspacing="0"><tr>';
	echo '<td><input type="button" class="button-secondary" title="'.__('Decrement Timer Length','autosave-net').'" value="-" style="font-weight:bold;" onclick="quicksave_decrement_timer();"></td>';
	echo '<td><input type="text" id="quicksavetime" value="'.$vquicksavetimer.'" style="width:50px;text-align:center;"></td>';
	echo '<td><input type="button" class="button-secondary" title="'.__('Increment Timer Length','autosave-net').'" value="+" style="font-weight:bold;" onclick="quicksave_increment_timer();"></td>';
	echo '<td width="5"></td><td>'.__('second save cycle','autosave-net').'.</td>';
	echo '</tr></table></td>';

	// Readonly Timer Countdown
	echo '<td width="20"></td>';
	echo '<td>QuickSaving in:</td>';
	echo '<td width="5"></td>';
	echo '<td><input id="quicksavecountdown" type="text" style="width:30px;text-align:center;" value="'.$vquicksavetimer.'" readonly></td>';

	// QuickSave Tool Buttons
	// $vcolouricons = autosave_net_get_option('quicksave_icons',true);
	$vcolouricons = false; // temp
	if ($vcolouricons) {$viconstyle = "background-image:url(\''.$viconsurl.'\');"; $vdashicons = "";}
	else {$viconstyle = ""; $vdashicons = "dashicons ";}
	if ($vquicksavedisabled == 'yes') {$venable = ''; $vdisable = 'display:none;'; $vpauser = 'display:none;';}
	else {$vdisable = ''; $venable = 'display:none;'; $vpauser = '';}

	echo '<td width="20"></td>';
	// dashicon play f522 "dashicons-controls-play", pause f523 "dashicons-controls-pause"
	echo '<td><div id="quicksavepauser" title="'.__('Pause QuickSave Timer','autosave-net').'" onclick="quicksave_timer_pause();"';
	echo ' style="'.$vpauser.'width:32px;height:32px;background-position: -32px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-controls-pause"';
	echo '></div><div id="quicksaveresumer" title="'.__('Resume QuickSave Timer','autosave-net').'" onclick="quicksave_timer_pause();"';
	echo ' style="display:none;width:32px;height:32px;background-position: -64px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-controls-play"';
	echo '></div></td>';
	// dashicon reset f463 "dashicons dashicons-update"
	echo '<td><div id="quicksavereset" title="'.__('Reset QuickSave Timer','autosave-net').'" onclick="quicksave_timer_reset();"';
	echo ' style="width:32px;height:32px;background-position: -96px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-update"';
	echo '></div></td>';
	// dashicon enable f147 "dashicons-yes", disable f335 "dashicons-no"
	echo '<td><div id="quicksaveenabler" title="'.__('Enable QuickSave Timer for this Post','autosave-net').'" onclick="quicksave_enable_disable();"';
	echo ' style="'.$venable.'width:32px;height:32px;background-position: -160px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-yes"';
	echo '></div><div id="quicksavedisabler" title="'.__('Disable QuickSave Timer for this Post','autosave-net').'" onclick="quicksave_enable_disable();"';
	echo ' style="'.$vdisable.'width:32px;height:32px;background-position: -128px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-no"';
	echo '></div></td>';
	// dashicon save ? f119 "dashicons dashicons-welcome-write-blog"
	echo '<td><div id="quicksavesavenow" title="'.__('QuickSave Now','autosave-net').'" onclick="domanualquicksave();"';
	echo ' style="width:32px;height:32px;background-position: -192px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-welcome-write-blog"';
	echo '></div></td>';
	// dashicon settings f111 "dashicons dashicons-admin-generic"
	echo '<td><div id="quicksaveoptions" title="'.__('QuickSave Settings','autosave-net').'" onclick="quicksave_show_hide(\'quicksavesettings\');"';
	echo ' style="width:32px;height:32px;background-position: -224px 0px;'.$viconstyle.'" class="'.$vdashicons.'dashicons-admin-generic"';
	echo '></div></td>';

	// end table
	echo '</tr></table></center>';

	// QuickSave Message Box
	// ---------------------
	echo '<div id="quicksavebox" style="display:none;text-align:center;border-radius:3px;padding:3px;">';
	echo '<span id="quicksavemessage">&nbsp;</span></div><br>';

	// Display Pageload Backup Content
	// -------------------------------
	$vtimedisplay = ''; $vdatedisplay = '';
	if ($vbackuptime != '') {
		if ($vtimestamp > $vbackuptime) {
			$vtimedisplay = date('H:i:s',$vbackuptime);
			$vdatedisplay = date('jS \o\f F Y',$vbackuptime);
			$vbackupcontent = $vlatestcontent = get_post_meta($vpostid,'_quicksavecontent',true);
			if ($vbackupcontent != '') {
				echo "<input type='button' class='button-secondary' style='font-weight:bold;' onclick='quicksave_show_hide(\"pageloadbackup\");' value='Pageload QuickSaved Content'>";
				echo "<span style='margin-left:30px;'>QuickSave Time:</span>";
				echo "<span style='margin-left:30px;' id='pageloadtime'><b>".$vtimedisplay."</b> on ".$vdatedisplay."</span><br>";
				echo "<div id='pageloadbackup' style='display:none;'>";
				echo "<table style='width:100%;'><tr><td colspan='7'>";
				echo "<textarea rows='10' cols='80' style='width:100%;' id='pageloadbackupcontent' readonly>".$vbackupcontent."</textarea>";
				echo "</td></tr><tr><td width='30'></td>";
				echo "<td align='center'><input type='button' class='button-secondary' title='Compares QuickSave Backup on Pageload with Current Edited Content' value='Compare with Current' onclick='quicksave_compare_versions(\"pageload\",\"current\");'></td>";
				echo "<td width='30'></td>";
				echo "<td align='center'><input type='button' class='button-secondary' title='Compares Quicksave Backup on Pageload with Saved Database Content' value='Compare with Database' onclick='quicksave_compare_versions(\"pageload\",\"saved\");'></td>";
				echo "<td width='30'></td>";
				echo "<td align='center'><input type='button' class='button-secondary' title='Restores Quicksave Backup on Pageload to Content Editing Area' value='Restore this QuickSave' onclick='quicksave_restore_content(\"pageload\");'></td>";
				echo "</tr></table></div><br>";
			}
		}
	}

	// Display Latest QuickSave Content
	// --------------------------------
	echo "<input type='button' class='button-secondary' style='font-weight:bold;margin-left:15px;' onclick='quicksave_show_hide(\"latestquicksave\");' value='Latest QuickSaved Content'>";
	echo "<span style='margin-left:30px;'>QuickSave Time:</span>";
	echo "<span style='margin-left:30px;' id='quicksavelatesttime'>";
	if ( ($vtimedisplay != '') && ($vdatedisplay != '') ) {echo "<b>".$vtimedisplay."</b> ".__('on','autosave-net')." ".$vdatedisplay;}
	else {echo __('No QuickSave has happened yet...','autosave-net');}
	echo "</span><br>";
	echo "<div id='latestquicksave' style='display:none;'>";
	echo "<table style='width:100%;'><tr><td colspan='7'>";
	echo "<textarea rows='10' cols='80' style='width:100%;' id='latestquicksavecontent' readonly>".$vlatestcontent."</textarea>";
	echo "</td></tr><tr><td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Compares Latest QuickSave Backup with Current Edited Content','autosave-net')."' value='".__('Compare with Current','autosave-net')."' onclick='quicksave_compare_versions(\"latest\",\"current\");'></td>";
	echo "<td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Compares Latest Quicksave Backup with Saved Database Content','autosave-net')."' value='".__('Compare with Database','autosave-net')."' onclick='quicksave_compare_versions(\"latest\",\"saved\");'></td>";
	echo "<td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Restores Latest Quicksave Backup to Content Editing Area','autosave-net')."' value='".__('Restore this QuickSave','autosave-net')."' onclick='quicksave_restore_content(\"latest\");'></td>";
	echo "</tr></table></div><br>";

	// Hidden Backup Content Display
	// -----------------------------
	echo "<div id='contentbackupwrapper' style='display:none;'>";
	echo "<input type='button' class='button-secondary' style='font-weight:bold;margin-left:40px;' onclick='quicksave_show_hide(\"contentbackup\");' value='Content before Restore'>";
	echo "<span style='margin-left:50px;'>".__('Restore Time','autosave-net').":</span>";
	echo "<span style='margin-left:30px;' id='contentbackuptime'></span><br>";
	echo "<div id='contentbackup' style='display:none;'>";
	echo "<table style='width:100%;'><tr><td colspan='7'>";
	echo "<textarea rows='10' cols='80' style='width:100%;' id='contentbackupcontent' readonly>".$vlatestcontent."</textarea>";
	echo "</td></tr>";
	echo "<tr><td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Compares Content Backup with Current Edited Content','autosave-net')."' value='".__('Compare with Current','autosave-net')."' onclick='quicksave_compare_versions(\"backup\",\"current\");'></td>";
	echo "<td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Compares Content Backup with Saved Database Content','autosave-net')."' value='".__('Compare with Database','autosave-net')."' onclick='quicksave_compare_versions(\"backup\",\"saved\");'></td>";
	echo "<td width='30'></td>";
	echo "<td align='center'><input type='button' class='button-secondary' title='".__('Restores Content Backup to Content Editing Area','autosave-net')."' value='".__('Restore this Backup','autosave-net')."' onclick='quicksave_restore_content(\"backup\");'></td>";
	echo "</tr></table></div><br></div>";

	// hidden comparison iframe form target
	echo '<div id="compareframewrapper" style="display:none;">';
	echo '<br><center><b>'.__('Content Comparison','autosave-net').'</b></center><br>';
	echo '<iframe name="compareframe" id="compareframe" src="javascript:void(0);" width="95%" height="200"></iframe>';
	echo '</div>';

	// hidden restored message
	echo '<div id="restoredwrapper" style="display:none;background-color:#F0F000;text-align:center;">';
	echo '<span id="restoredmessage"></span></div>';

	// end quicksave wrapper
	echo '</div>';

	// Quicksave Forms
	// ---------------
	// put form in footer to not conflict with other forms
	add_action('admin_footer','autosave_net_quicksave_save_forms');
	function autosave_net_quicksave_save_forms() {

		global $post; $vpostid = $post->ID;
		$vquicksavetimer = absint(apply_filters('quicksave_timer',get_option('quicksave_timer')));
		$vuserid = get_current_user_id();

		// quicksave form
		echo '<form id="quicksaveform" name="quicksaveform" target="quicksaveframe" action="admin-ajax.php?action=do_quick_save" method="post">';
		echo '<input type="hidden" id="quicksaveid" name="quicksaveid" value="'.$vpostid.'">';
		echo '<input type="hidden" id="quicksaveuserid" name="quicksaveuserid" value="'.$vuserid.'">';
		echo '<textarea style="display:none;" id="quicksavecontent" name="quicksavecontent"></textarea>';
		echo '<input type="hidden" id="quicksavetimer" name="quicksavetimer" value="'.$vquicksavetimer.'">';
		echo '<input type="hidden" id="quicksaveusertimer" name="quicksaveusertimer" value="'.$vquicksavetimer.'">';
		echo '<input type="hidden" id="quicksavemanual" name="quicksavemanual" value="">';
		echo '<input type="hidden" id="quicksavepause" name="quicksavepause" value="">';
		echo '</form>';
		// hidden iframe form target
		echo '<iframe style="display:none;" name="quicksaveframe" id="quicksaveframe" src="javascript:void(0);"></iframe>';

		// compare form
		echo '<form id="compareform" name="compareform" target="compareframe" action="admin-ajax.php?action=compare_quick_saves" method="post">';
		echo '<input type="hidden" id="comparepostid" name="comparepostid" value="'.$vpostid.'">';
		echo '<input type="hidden" id="compareuserid" name="compareuserid" value="'.$vuserid.'">';
		echo '<textarea style="display:none;" id="savedcontent" name="savedcontent"></textarea>';
		echo '<textarea style="display:none;" id="currentcontent" name="currentcontent"></textarea>';
		echo '<input type="hidden" id="savedtype" name="savedtype" value="">';
		echo '<input type="hidden" id="contenttype" name="contenttype" value="">';
		echo '</form>';

		// backuptimeform
		echo '<form id="backuptimeform" name="backuptimeform" target="backuptimeframe" action="admin-ajax.php?action=quicksave_update_backup_time" method="post">';
		echo '<input type="hidden" id="backupsource" name="backupsource" value="'.$vpostid.'">';
		echo '</form>';
		// hidden iframe form target
		echo '<iframe style="display:none;" name="backuptimeframe" id="backuptimeframe" src="javascript:void(0);"></iframe>';

		// 1.2.5: fix to missing meta value for post disable
		$vquicksavedisabled = get_post_meta($vpostid,'_quicksavedisabled',true);
		$vquicksavedisabled = apply_filters('quicksave_disabler',$vquicksavedisabled);
		if (!$vquicksavedisabled) {$vquicksavedisabled = '';}

		// disableform
		echo '<form id="quicksavedisableform" name="quicksavedisableform" target="quicksavedisableframe" action="admin-ajax.php?action=quicksave_enable_disable" method="post">';
		echo '<input type="hidden" id="quicksavedisablepostid" name="quicksavedisablepostid" value="'.$vpostid.'">';
		echo '<input type="hidden" id="quicksavedisableuserid" name="quicksavedisableuserid" value="'.$vuserid.'">';
		echo '<input type="hidden" id="quicksavedisable" name="quicksavedisable" value="'.$vquicksavedisabled.'">';
		echo '</form>';
		// hidden iframe form target
		echo '<iframe style="display:none;" name="quicksavedisableframe" id="quicksavedisableframe" src="javascript:void(0);"></iframe>';
	}

	// QuickSave Javascript
	// --------------------
	// put javascript in footer too
	add_action('admin_footer','autosave_net_quicksave_save_javascript');

	function autosave_net_quicksave_save_javascript() {

		// DocReady Javascript
		// -------------------
		echo "<script language='javascript' type='text/javascript'>";
		echo autosave_net_docready_javascript();
		echo "</script>";

		// QuickSave Javascript
		// -------------------
		echo "<script language='javascript' type='text/javascript'>

		var thepostcontent;	var quicksavetimer; var quicksavecycle;
		var quicksavedisabled = document.getElementById('quicksavedisable').value;

		function quicksave_wrapper_resize() {
			newheight = document.getElementById('quicksavewrapper').scrollHeight;
			document.getElementById('quicksave_metabox').height = newheight + 50 + 'px';
		}

		function quicksave_info(onoff) {
			if (onoff == 'on') {document.getElementById('infobutton').style.display = 'none';}
			if (onoff == 'off') {document.getElementById('infobutton').style.display = '';}
		}

		function quicksave_show_hide(divid) {
			if (document.getElementById(divid).style.display == 'none') {
				document.getElementById(divid).style.display = '';
			}
			else {document.getElementById(divid).style.display = 'none';}
			quicksave_wrapper_resize();
		}

		function quicksave_increment_timer() {
			var timerinput = document.getElementById('quicksavetime');
			var quicksavetimer = document.getElementById('quicksavetimer').value;
			var textint = parseInt(timerinput.value);
			if (isNaN(textint)) {timerinput.value = quicksavetimer;}
			else {timerinput.value++;}
		}

		function quicksave_decrement_timer() {
			var timerinput = document.getElementById('quicksavetime');
			var quicksavetimer = document.getElementById('quicksavetimer').value;
			var textint = parseInt(timerinput.value);
			if (isNaN(textint)) {timerinput.value = quicksavetimer;}
			else {
				/* five second maximum */
				if (timerinput.value > 5) {timerinput.value--;}
				if (timerinput.value < 6) {timerinput.value = 5;}
			}
		}

		function quicksave_timer_pause() {
			if (document.getElementById('quicksavepause').value == 'yes') {
				document.getElementById('quicksavepause').value = '';
				document.getElementById('quicksaveresumer').style.display = 'none';
				document.getElementById('quicksavepauser').style.display = '';
			} else {
				document.getElementById('quicksavepause').value = 'yes';
				document.getElementById('quicksavepauser').style.display = 'none';
				document.getElementById('quicksaveresumer').style.display = '';
			}
		}

		function quicksave_enable_disable() {
			var quicksavedisabled = document.getElementById('quicksavedisable').value;
			if (quicksavedisabled == 'yes') {
				document.getElementById('quicksaveenabler').style.display = 'none';
				document.getElementById('quicksavedisabler').style.display = '';
				document.getElementById('quicksavedisable').value = '';
				document.getElementById('quicksavepause').value = '';
				document.getElementById('quicksaveresumer').style.display = 'none';
				document.getElementById('quicksavepauser').style.display = '';
			} else {
				document.getElementById('quicksavedisabler').style.display = 'none';
				document.getElementById('quicksaveenabler').style.display = '';
				document.getElementById('quicksavedisable').value = 'yes';
				document.getElementById('quicksavepause').value = 'yes';
				document.getElementById('quicksavepauser').style.display = 'none';
				document.getElementById('quicksaveresumer').style.display = 'none';
			}
			quicksavedisableform = document.getElementById('quicksavedisableform');
			quicksavedisableform.submit();
		}

		function quicksave_timer_reset() {
			var quicksaveusertimer = parseInt(document.getElementById('quicksavetime').value);
			var quicksavetimer = parseInt(document.getElementById('quicksavetimer').value);
			if (!isNaN(quicksaveusertimer)) {
				document.getElementById('quicksavecountdown').value = quicksaveusertimer;
			} else {
				document.getElementById('quicksavecountdown').value = quicksavetimer;
			}
		}

		function domanualquicksave() {
			document.getElementById('quicksavemanual').value = 'yes';
			doquicksave();
			document.getElementById('quicksavemanual').value = '';
		}

		function doquicksavetimer() {
			var quicksavedisabled = document.getElementById('quicksavedisable').value;
			if (quicksavedisabled == 'yes') {return;}
			var quicksavepause = document.getElementById('quicksavepause').value;
			if (quicksavepause == 'yes') {return;}

			var quicksaveusertimer = parseInt(document.getElementById('quicksavetime').value);
			var quicksavetimer = parseInt(document.getElementById('quicksavetimer').value);
			var countdowntimer = parseInt(document.getElementById('quicksavecountdown').value);

			if ( (!isNaN(quicksaveusertimer)) && (quicksaveusertimer !== 0) ) {
				if (quicksaveusertimer < countdowntimer) {
					document.getElementById('quicksavecountdown').value = quicksaveusertimer;
					countdowntimer = quicksaveusertimer;
				}
			}

			if (countdowntimer == 1) {
				doquicksave();
				if (!isNaN(quicksaveusertimer)) {
					document.getElementById('quicksavecountdown').value = quicksaveusertimer;
				} else {
					document.getElementById('quicksavecountdown').value = quicksavetimer;
				}
				countdowntimer = parseInt(document.getElementById('quicksavecountdown').value);
				/* alert('timer:'+quicksaveusertimer+':'+quicksavetimer+':'+countdowntimer); */
			}
			else {
				countdowntimer--;
				document.getElementById('quicksavecountdown').value = countdowntimer;
			}
		}

		function doquicksave() {
			var usertimer = parseInt(document.getElementById('quicksavetime').value);
			if (!isNaN(usertimer)) {document.getElementById('quicksavetime').value = usertimer;}
			thepostcontent = document.getElementById('content').value;
			document.getElementById('quicksavecontent').innerHTML = thepostcontent;
			savingform = document.getElementById('quicksaveform');
			savingform.submit();
		}

		function doquicksavecycle() {quicksavecycle = setInterval(doquicksavetimer, 1000);}
		/* Load quicksave cycle when document is ready */
		window.docReady(doquicksavecycle);

		function quicksave_compare_versions(savedtype,contenttype) {
			document.getElementById('savedtype').value = savedtype;
			if (savedtype == 'pageload') {
				document.getElementById('savedcontent').value = document.getElementById('pageloadbackupcontent').value;
				document.getElementById('latestquicksave').style.display = 'none';
				document.getElementById('contentbackup').style.display = 'none';
			} else if (savedtype == 'latest') {
				document.getElementById('pageloadbackup').style.display = 'none';
				document.getElementById('contentbackup').style.display = 'none';
			} else if (savedtype == 'backup') {
				document.getElementById('savedcontent').value = document.getElementById('contentbackupcontent').value;
				document.getElementById('latestquicksave').style.display = 'none';
				document.getElementById('pageloadbackup').style.display = 'none';
			}

			document.getElementById('contenttype').value = contenttype;
			if (contenttype == 'current') {
				thepostcontent = document.getElementById('content').value;
				document.getElementById('currentcontent').value = thepostcontent;
			}

			compareform = document.getElementById('compareform');
			compareform.submit();
			quicksave_wrapper_resize();
			setTimeout(quicksave_wrapper_resize,5000);
		}

		function quicksave_restore_content(source) {

			if (source == 'latest') {var displaysource = '".__('the Latest QuickSave Content','autosave-net')."';}
			if (source == 'pageload') {var displaysource = '".__('the Pageload QuickSave Content','autosave-net')."';}
			if (source == 'backup') {var displaysource = '".__('the Content before Restore','autosave-net')."';}

			var agree = confirm('".__('Are you sure you want to Restore','autosave-net')."\\n'+displaysource+'?');
			if (!agree) {return false;}

			backuptimeform = document.getElementById('backuptimeform');
			document.getElementById('backupsource').value = source;
			backuptimeform.submit();

			if (source == 'latest') {
				document.getElementById('contentbackupcontent').value = document.getElementById('content').value;
				document.getElementById('content').value = document.getElementById('latestquicksavecontent').value;
				restoredmessage = '".__('Latest QuickSave Content has been Restored.','autosave-net')."';
			}
			if (source == 'pageload') {
				document.getElementById('contentbackupcontent').value = document.getElementById('content').value;
				document.getElementById('content').value = document.getElementById('pageloadbackupcontent').value;
				restoredmessage = '".__('QuickSave Pageload Content has been Restored.','autosave-net')."';
			}
			if (source == 'backup') {
				var currentcontent = document.getElementById('content').value;
				document.getElementById('content').value = document.getElementById('contentbackupcontent').value;
				document.getElementById('contentbackupcontent').value = currentcontent;
				restoredmessage = '".__('Content before Restore - has now been Restored.','autosave-net')."';
			}
			alert(restoredmessage);
		}


		</script>";

	}
}

// Quicksave_via AJAX
// ------------------
add_action('wp_ajax_do_quick_save', 'autosave_net_quicksave_do_quicksave');

function autosave_net_quicksave_do_quicksave() {

	// Check conditions and maybe exit
	if ( (!isset($_POST['quicksaveid'])) || (!isset($_POST['quicksavecontent'])) ) {
		echo "Error 1."; return false;
	}
	$vpostid = $_POST['quicksaveid'];
	$vcontent = $_POST['quicksavecontent'];
	$vtimer = $_POST['quicksavetimer'];
	$vusertimer = $_POST['quicksaveusertimer'];
	$vuserid = $_POST['quicksaveuserid'];
	$vsavetime = $_POST['quicksaveposttime'];
	$vmanual = $_POST['quicksavemanual'];

	if ( ($vpostid == '') || (!is_numeric($vpostid)) ) {echo "Error 2."; return false;}
	if ($vcontent == '') {return false;}
	if (!current_user_can('edit_post',$vpostid)) {echo "Error 3."; return false;}
	$vcurrentuserid = get_current_user_id();
	if ($vuserid != $vcurrentuserid) {echo "Error 4."; return false;}

	// Compare Timestamp (pointless here?)
	$vtimestamp = time();
	$vbackuptime = get_post_meta($vpostid,'_quicksavetime',true);
	if ($vbackuptime != '') {
		if ($vbackuptime > $vtimestamp) {echo "Error 5."; return false;}
	}

	// check for disabled switch
	// as another user may have disabled quicksave for this post
	$vdisabled = get_post_meta($vpostid,'_quicksavedisabled',true);
	if ( ($vdisabled == 'yes') && ($vmanual != 'yes') ) {echo "Error 6."; return false;}

	// Update the _quicksavecontent meta value
	$vcontent = stripslashes($vcontent);
	$vbackupcontent = get_post_meta($vpostid,'_quicksavecontent',true);
	delete_post_meta($vpostid,'_quicksavecontent');
	$vquicksave = add_post_meta($vpostid,'_quicksavecontent',$vcontent);
	$vquicksavetime = update_post_meta($vpostid,'_quicksavetime',$vtimestamp);

	// If the user timer has been adjusted, save that too
	if ( ($vtimer != $vusertimer) && (is_numeric($vusertimer)) ) {
		update_post_meta($vpostid,'_quicksavetimer',$vusertimer);
	}

	// Javascript Callbacks
	// --------------------
	echo "<head><script language='javascript' type='text/javascript'>";
	$vtimestamp = time();
	$vdisplaytime = date('H:i:s',$vtimestamp);
	$vdisplaydate = date('jS \o\f F Y',$vtimestamp);
	$vmessage = __('QuickSaved','autosave-net').": <b>".$vdisplaytime."</b> ".__('on','autosave-net')." ".$vdisplaydate." (".__('server time.','autosave-net').")";
	$vfailed = __('QuickSave Failed.','autosave-net');
	if ($vquicksave) {
		echo "parent.document.getElementById('quicksavebox').style.display = '';";
		echo "parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F000';";
		echo "parent.document.getElementById('quicksavemessage').innerHTML = '".$vmessage."';";
		echo "parent.document.getElementById('quicksavelatesttime').innerHTML = '<b>".$vdisplaytime."</b> ".__('on','autosave-net')." ".$vdisplaydate."';";
		echo "parent.document.getElementById('latestquicksavecontent').value = parent.document.getElementById('content').value;";
		if ($vmanual == 'yes') {
			if ($vdisabled == 'yes') {echo "alert('".__('QuickSaved! Warning: Timer is disabled for this post.','autosave-net')."');";}
			else {
				echo "parent.quicksave_timer_reset();";
				echo "alert('".__('QuickSaved! Timer has been reset.','autosave-net')."');";
			}
		}
	} else {
		echo "parent.document.getElementById('quicksavebox').style.display = '';";
		echo "parent.document.getElementById('quicksavemessage').innerHTML = '".$vfailed."';";
	}

	// fade away the save message background color from #F0F000
	// ...yes i know jquery fade is available, this is for fun: F0F00n!
	echo "
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F022';}, 1000);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F044';}, 1500);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F066';}, 2000);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F088';}, 2500);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F0AA';}, 3000);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F0BB';}, 3500);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F0CC';}, 4000);
		setTimeout(function() {parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F0EE';}, 4500);
		setTimeout(function() {
			parent.document.getElementById('quicksavebox').style.backgroundColor = 'transparent';
			// clear message and stop element resizing affecting any lower metaboxes
			parent.document.getElementById('quicksavemessage').innerHTML = '&nbsp;';
		}, 5000);";

	echo "</script></head>";

	echo "<body>".$vcontent."</body>";
	wp_die(false); // exit;
}

// Compare Saved Content via AJAX
// ------------------------------
add_action('wp_ajax_compare_quick_saves', 'autosave_net_quicksave_compare_saved_versions');
function autosave_net_quicksave_compare_saved_versions() {

	$vpostid = $_POST['comparepostid'];
	if ( ($vpostid == '') || (!is_numeric($vpostid)) ) {echo 'Error 1. No Post ID.'; return false;}

	if (!current_user_can('edit_post',$vpostid)) {echo 'Error 2. Cannot Edit.'; return false;}
	$vuserid = $_POST['compareuserid'];	$vcurrentuserid = get_current_user_id();
	if ($vcurrentuserid != $vuserid) {echo 'Error 3. User Mismatch.'; return false;}

	$vsavedtype = $_POST['savedtype'];
	if ($vsavedtype == 'latest') {
		$vquicksavecontent = get_post_meta($vpostid,'_quicksavecontent',true);
		$vargs['title_left'] = __('Latest QuickSave Content','autosave-net');
	}
	elseif ($vsavedtype == 'pageload') {
		$vquicksavecontent = stripslashes($_POST['savedcontent']);
		$vargs['title_left'] = __('Pageload QuickSave Content','autosave-net');
	}
	elseif ($vsavedtype == 'backup') {
		$vquicksavecontent = stripslashes($_POST['savedcontent']);
		$vargs['title_left'] = __('Content before Restore','autosave-net');
	}
	else {return false;}

	$vcontenttype = $_POST['contenttype'];
	if ($vcontenttype == 'current') {
		$vpostcontent = stripslashes($_POST['currentcontent']);
		$vargs['title_right'] = __('Currently Edited Post Content','autosave-net');
		$vtimestamp = time();
	}
	elseif ($vcontenttype == 'saved') {
		$vpostcontent = autosave_net_get_just_post_content($vpostid);
		$vargs['title_right'] = __('Wordpress Saved Post Content','autosave-net');
		$vtimestamp = get_post_modified_time('U',false,$vpostid,false);
	}
	else {return false;}

	$vdisplaytime = date('H:i:s',$vtimestamp);
	$vdisplaydate = date('jS \o\f F Y',$vtimestamp);
	$vsavedtime = "<b>".$vdisplaytime."</b> ".__('on','autosave-net')." ".$vdisplaydate;

	// Here is where the magic happens...
	// Do the comparison using in-built function wp_text_diff
	$vargs['title'] = get_the_title($vpostid);
	$vdifference = wp_text_diff($vquicksavecontent,$vpostcontent,$vargs);

	if (!$vdifference) {
		echo "<head><script language='javascript' type='text/javascript'>
			alert('".__('No difference was found between','autosave-net').":\\n".$vargs['title_left']." ".__('and','autosave-net')."\\n ".$vargs['title_right'].".');
			parent.quicksave_wrapper_resize();
		</script></head>";
	}
	else {
		wp_enqueue_style('colors'); wp_enqueue_style('ie');
		wp_enqueue_script('utils'); wp_enqueue_script('buttons');
		echo "<html><head>";

		// DocReady
		echo "<script language='javascript' type='text/javascript'>";
		echo autosave_net_docready_javascript(); echo "</script>";

		// Comparison Javacript
		echo "<script language='javascript' type='text/javascript'>
		parent.document.getElementById('compareframewrapper').style.display = '';

		function resizeIframe(newHeight) {
			parent.document.getElementById('compareframe').style.height = parseInt(newHeight) + 30 + 'px';
		}
		function closecomparison() {
			parent.document.getElementById('compareframewrapper').style.display = 'none';
			parent.document.getElementById('compareframe').src = 'javascript:void(0);';
			parent.quicksave_wrapper_resize();
		}
		function restorecontent() {
			restoreform = document.getElementById('restoreform');
			restoreform.submit();
		}
		function displaytimestamps() {
			var savedtime = '".$vsavedtime."';";
			if ($vsavedtype == 'pageload') {echo "var quicksavetime = parent.document.getElementById('pageloadtime').innerHTML;";}
			if ($vsavedtype == 'latest') {echo "var quicksavetime = parent.document.getElementById('quicksavelatesttime').innerHTML;";}
			if ($vsavedtype == 'backup') {echo "var quicksavetime = parent.document.getElementById('contentbackuptime').innerHTML;";}
			echo "
			document.getElementById('savedtimedisplay').innerHTML = savedtime;
			document.getElementById('quicksavedtimedisplay').innerHTML = quicksavetime;
			document.getElementById('timedisplay').style.display = '';
		}
		window.docReady(displaytimestamps);
		</script>";
		do_action('admin_print_styles');
		echo "</head>";
		echo "<body onload='resizeIframe(document.body.scrollHeight)'>";

		// restore form
		echo '<form id="restoreform" name="restoreform" action="admin-ajax.php?action=restore_quick_save" method="post">';
		echo '<input type="hidden" id="restorepostid" name="restorepostid" value="'.$vpostid.'">';
		echo '<input type="hidden" id="restoreuserid" name="restoreuserid" value="'.$vuserid.'">';
		echo '<input type="hidden" id="savedtype" name="savedtype" value="'.$vsavedtype.'">';
		echo '<input type="hidden" id="contenttype" name="contenttype" value="'.$vcontenttype.'">';
		echo '<textarea id="quicksavedcontent" name="quicksavedcontent" style="display:none;" readonly>'.$vquicksavecontent.'</textarea>';
		echo '<textarea id="savedcontent" name="savedcontent" style="display:none;" readonly>'.$vpostcontent.'</textarea>';
		echo '</form>';

		echo "<input style='float:left;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='restorecontent(\"".$vsavedtype."\",\"".$vcontenttype."\");' value='".__('Restore Left-Hand Content','autosave-net')."'>";
		echo "<input style='float:right;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='closecomparison();' value='".__('Close Comparison','autosave-net')."'><br><br>";
		echo "<div id='timedisplay' style='display:none;'>";
		echo "<span id='quicksavedtimedisplay' style='float:left;margin:10px;'></span>";
		echo "<span id='savedtimedisplay' style='float:right;margin:10px;'></span>";
		echo "</div>";

		// 1.2.5: fixes to difference table output
		$vdifference = str_replace('<td></td><th>'.$vargs['title_left'],'<th>'.$vargs['title_left'],$vdifference);
		$vdifference = str_replace('<td></td><th>'.$vargs['title_right'],'<th>'.$vargs['title_right'],$vdifference);
		$vdifference = str_replace('<td>&nbsp;</td>','',$vdifference);
		$vdifference .= "<style>table.diff {table-layout:auto !important;}
		td.diff-context, td.diff-deletedline, td.diff-addedline {width:50% !important;}</style>";

		echo "<br>".$vdifference."<br>";
		echo "<input style='float:left;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='restorecontent(\"".$vsavedtype."\",\"".$vcontenttype."\");' value='".__('Restore Left-Hand Content','autosave-net')."'>";
		echo "<input style='float:right;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='closecomparison();' value='".__('Close Comparison','autosave-net')."'>";
		echo "</body>";
		do_action('admin_print_footer_scripts');
		echo "</html>";
	}

	wp_die(false); // exit;

}

// Restore Saved Content via AJAX
// ------------------------------
add_action('wp_ajax_restore_quick_save', 'autosave_net_quicksave_restore_saved_version');
function autosave_net_quicksave_restore_saved_version() {

	$vpostid = $_POST['restorepostid'];
	if ( ($vpostid == '') || (!is_numeric($vpostid)) ) {return false;}

	if (!current_user_can('edit_post',$vpostid)) {return false;}
	$vuserid = $_POST['restoreuserid'];	$vcurrentuserid = get_current_user_id();
	if ($vcurrentuserid != $vuserid) {return false;}

	$vquicksavecontent = stripslashes($_POST['quicksavedcontent']);
	$vsavedtype = $_POST['savedtype'];
	if ($vsavedtype == 'latest') {$vrestoreddisplay = __('Latest QuickSave Content','autosave-net');}
	elseif ($vsavedtype == 'pageload') {$vrestoreddisplay = __('Pageload QuickSave Content','autosave-net');}
	elseif ($vsavedtype == 'backup') {$vrestoreddisplay = __('Content before Restore','autosave-net');}
	else {return false;}

	$vpostcontent = stripslashes($_POST['savedcontent']);
	$vcontenttype = $_POST['contenttype'];
	if ($vcontenttype == 'current') {$volddisplay = __('Currently Edited Post Content','autosave-net');}
	elseif ($vcontenttype == 'saved') {$volddisplay = __('Wordpress Saved Post Content','autosave-net');}
	else {return false;}

	// dummy textareas
	echo '<textarea id="quicksavedcontent" name="quicksavedcontent" readonly>'.$vquicksavecontent.'</textarea>';
	echo '<textarea id="savedcontent" name="savedcontent" readonly>'.$vpostcontent.'</textarea>';

	$vtimestamp = time();
	$vdisplaytime = date('H:i:s',$vtimestamp);
	$vdisplaydate = date('jS \o\f F Y',$vtimestamp);
	$vtimedisplay = '<b>'.$vdisplaytime.'</b> on '.$vdisplaydate;
	echo "<script language='javascript' type='text/javascript'>
		oldcontent = document.getElementById('savedcontent').value;
		parent.document.getElementById('contentbackupcontent').value = oldcontent;
		parent.document.getElementById('contentbackuptime').innerHTML = '".$vtimedisplay."';
		parent.document.getElementById('contentbackupwrapper').style.display = '';

		restoredcontent = document.getElementById('quicksavedcontent').value;
		parent.document.getElementById('content').value = restoredcontent;

		parent.document.getElementById('compareframewrapper').style.display = 'none';
		parent.document.getElementById('contentbackup').style.display = 'none';

		checkcontent = parent.document.getElementById('content').value;
		if (checkcontent == restoredcontent) {
			var restoredmessage = '".$vtimedisplay."';
			restoredmessage += ': <b>".$vrestoreddisplay." ".__('has been Restored.','autosave-net')."</b><br>".__('Content before Restore is now available above.','autosave-net')."';
			parent.document.getElementById('restoredmessage').innerHTML = restoredmessage;
			parent.document.getElementById('restoredwrapper').style.display = '';
		}
		else {alert('".$vrestoreddisplay." : ".__('Restore Failed. Use Copy and Paste.','autosave-net')."');}
		parent.quicksave_wrapper_resize();
	</script>";

	wp_die(false); // exit;
}

// Adjust Backup Timer via AJAX
// ----------------------------
add_action('wp_ajax_quicksave_update_backup_time', 'autosave_net_quicksave_update_backup_time');
function autosave_net_quicksave_update_backup_time() {

	$vsource = $_POST['backupsource'];
	if ($vsource == 'latest') {$vrestoredmessage = __('Latest QuickSave Content has been Restored.','autosave-net');}
	if ($vsource == 'pageload') {$vrestoredmessage = __('QuickSave Pageload Content has been Restored.','autosave-net');}
	if ($vsource == 'backup') {$vrestoredmessage = __('Content before Restore - has now been Restored.','autosave-net');}

	$vtimestamp = time();
	$vdisplaytime = date('H:i:s',$vtimestamp);
	$vdisplaydate = date('jS \o\f F Y',$vtimestamp);
	$vtimedisplay = '<b>'.$vdisplaytime.'</b> on '.$vdisplaydate;
	echo "<script language='javascript' type='text/javascript'>
	parent.document.getElementById('contentbackuptime').innerHTML = '".$vtimedisplay."';
	var restoredmessage = '".$vrestoredmessage."';
	if (restoredmessage != '') {
		document.getElementById('restoredmessage').innerHTML = $vtimedisplay.': <b>'+restoredmessage+'</b>';
		document.getElementById('restoredwrapper').style.display = '';
	}
	</script>";
	wp_die(false); // exit;
}

// Disable QuickSave for this Post
// -------------------------------
add_action('wp_ajax_quicksave_enable_disable', 'autosave_net_quicksave_enable_disable');
function autosave_net_quicksave_enable_disable() {

	$vpostid = $_POST['quicksavedisablepostid'];
	if ( ($vpostid == '') || (!is_numeric($vpostid)) ) {return false;}
	if (!current_user_can('edit_post',$vpostid)) {return false;}
	$vuserid = $_POST['quicksavedisableuserid'];
	$vcurrentuserid = get_current_user_id();
	if ($vcurrentuserid != $vuserid) {return false;}

	if ($_POST['quicksavedisable'] == 'yes') {
		update_post_meta($vpostid,'_quicksavedisabled','yes'); $vdisabled = 'yes';
	} else {delete_post_meta($vpostid,'_quicksavedisabled');}

	echo "<script language='javascript' type='text/javascript'>";
	if ($vdisabled == 'yes') {
		echo "var quicksavetimer = parent.document.getElementById('quicksavetimer').value;";
		echo "alert('".__('QuickSave Timer has been disabled for this post.','autosave-net')."');";
	} else {echo "alert('".__('QuickSave Timer has been enabled for this post.','autosave-net')."');";}
	echo "</script>";
	wp_die(false); // exit;
}

// DocReady Javascript
// -------------------
// https://github.com/jfriend00/docReady
function autosave_net_docready_javascript() {

	$docready = '
	// DocReady - Javacript-only cross-browser document ready function
	// [ substitute for jQuery .ready() ]
	// https://github.com/jfriend00/docReady

	(function(funcName, baseObj) {
		"use strict";
		// The public function name defaults to window.docReady
		// but you can modify the last line of this function to pass in a different object or method name
		// if you want to put them in a different namespace and those will be used instead of
		// window.docReady(...)
		funcName = funcName || "docReady";
		baseObj = baseObj || window;
		var readyList = [];
		var readyFired = false;
		var readyEventHandlersInstalled = false;

		// call this when the document is ready
		// this function protects itself against being called more than once
		function ready() {
			if (!readyFired) {
				// this must be set to true before we start calling callbacks
				readyFired = true;
				for (var i = 0; i < readyList.length; i++) {
					// if a callback here happens to add new ready handlers,
					// the docReady() function will see that it already fired
					// and will schedule the callback to run right after
					// this event loop finishes so all handlers will still execute
					// in order and no new ones will be added to the readyList
					// while we are processing the list
					readyList[i].fn.call(window, readyList[i].ctx);
				}
				// allow any closures held by these functions to free
				readyList = [];
			}
		}

		function readyStateChange() {
			if ( document.readyState === "complete" ) {ready();}
		}

		// This is the one public interface
		// docReady(fn, context);
		// the context argument is optional - if present, it will be passed
		// as an argument to the callback
		baseObj[funcName] = function(callback, context) {
			// if ready has already fired, then just schedule the callback
			// to fire asynchronously, but right away
			if (readyFired) {
				setTimeout(function() {callback(context);}, 1);
				return;
			} else {
				// add the function and context to the list
				readyList.push({fn: callback, ctx: context});
			}
			// if document already ready to go, schedule the ready function to run
			// IE only safe when readyState is "complete", others safe when readyState is "interactive"
			if (document.readyState === "complete" || (!document.attachEvent && document.readyState === "interactive")) {
				setTimeout(ready, 1);
			} else if (!readyEventHandlersInstalled) {
				// otherwise if we do not have event handlers installed, install them
				if (document.addEventListener) {
					// first choice is DOMContentLoaded event
					document.addEventListener("DOMContentLoaded", ready, false);
					// backup is window load event
					window.addEventListener("load", ready, false);
				} else {
					// must be IE
					document.attachEvent("onreadystatechange", readyStateChange);
					window.attachEvent("onload", ready);
				}
				readyEventHandlersInstalled = true;
			}
		}
	})("docReady", window);
	// modify this previous line to pass in your own method name
	// and object for the method to be attached to

	';
	return $docready;
}

?>