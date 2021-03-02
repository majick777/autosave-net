<?php

/*
Plugin Name: AutoSave Net
Plugin URI: http://wordquest.org/plugins/autosave-net/
Description: Auto-save safety net! Timed Backup of your Post Content while writing, with instant compare and restore content metabox.
Version: 1.3.4
Author: Tony Hayes
Author URI: http://dreamjester.net
GitHub Plugin URI: majick777/autosave-net
@fs_premium_only pro-functions.php
*/

if ( !function_exists( 'add_action' ) ) {
	exit;
}

// === Development TODOs ===
// - better handling of visual/text editor view content ?
// - ability to compare to copy and pasted textarea Content ?
// - ability to compare to latest Wordpress AutoSave Content ?
// - ability to compare to earlier AutoSave Revisions Content ?
// - special option to set number of Revisions per post type ?


// === WordQuest Menus ===
// - Add WordQuest Submenu
// - WordQuest Submenu Icon Fix
// - Add WordQuest Sidebar Settings
// - Load WordQuest Admin Helper
// - Add Sidebar Floatbox
// === Plugin Setup ===
// - Plugin Options
// - Plugin Settings
// - Start Plugin Loader Instance
// - Define Plugin Constants
// - Define Version Constant
// === WordPress Autosave ===
// - Handle Wordpress AutoSave Options
// - Override AutoSave Time in Javascript
// - Dequeueue AutoSave Script
// - Limit Wordpress AutoSave Post Revisions
// === QuickSave Timer ===
// - Get Just Post Content Helper
// - Edit Screen Admin Notice
// - Display QuickSave Content Notice
// - Add Quicksave Metabox
// - Quicksave Metabox
// - QuickSave Save Forms
// - QuickSave Javascript
// === AJAX Actions ===
// - Quicksave via AJAX
// - Compare Saved Content via AJAX
// - Restore Saved Content via AJAX
// - Adjust Backup Timer via AJAX
// - Disable QuickSave for Post
// === Extra Scripts ===
// - DocReady Javascript
// - Smooth Scroll Jump Script


// ----------------------------
// Options / Filter Usage Notes
// ----------------------------

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




// -----------------------
// === WordQuest Menus ===
// -----------------------
// note: these actions must be added before loader is initiated

// ---------------------
// Add WordQuest Submenu
// ---------------------
add_filter( 'autosave_net_admin_menu_added', 'autosave_net_add_admin_menu', 10, 2 );
function autosave_net_add_admin_menu( $added, $args ) {

	// --- filter menu capability early ---
	$capability = apply_filters( 'wordquest_menu_capability', 'manage_options' );

	// --- maybe add Wordquest top level menu ---
	global $admin_page_hooks;
	if ( empty( $admin_page_hooks['wordquest'] ) ) {
		$icon = plugins_url( 'images/wordquest-icon.png', $args['file'] );
		$position = apply_filters( 'wordquest_menu_position', '3' );
		add_menu_page( 'WordQuest Alliance', 'WordQuest', $capability, 'wordquest', 'wqhelper_admin_page', $icon, $position );
	}

	// --- check if using parent menu ---
	// (and parent menu capability)
	if ( isset( $args['parentmenu']) && ( $args['parentmenu'] == 'wordquest' ) && current_user_can( $capability ) ) {

		// --- add WordQuest Plugin Submenu ---
		add_submenu_page( 'wordquest', $args['pagetitle'], $args['menutitle'], $args['capability'], $args['slug'], $args['namespace'] . '_settings_page' );

		// --- add icons and styling fix to the plugin submenu :-) ---
		add_action( 'admin_footer', 'autosave_net_wordquest_submenu_fix' );

		return true;
	}

	return false;
}

// --------------------------
// WordQuest Submenu Icon Fix
// --------------------------
function autosave_net_wordquest_submenu_fix() {
	$args = autosave_net_loader_instance()->args;
	$icon_url = plugins_url( 'images/icon.png', $args['file'] );
	if ( isset( $_REQUEST['page'] ) && ( $_REQUEST['page'] == $args['slug'] ) ) {$current = '1';} else {$current = '0';}
	echo "<script>jQuery(document).ready(function() {if (typeof wordquestsubmenufix == 'function') {
	wordquestsubmenufix('" . esc_js( $args['slug'] ) . "', '" . esc_url( $icon_url ) . "', '" . esc_js( $current ) . "');} });</script>";
}

// ------------------------------
// Add WordQuest Sidebar Settings
// ------------------------------
add_action( 'autosave_net_add_settings', 'autosave_net_add_settings' , 10, 1 );
function autosave_net_add_settings( $args ) {
	if ( isset( $args['settings'] ) ) {
		$adsboxoff = 'checked';
		if ( file_exists($args['dir'] . '/updatechecker.php' ) ) {
			$adsboxoff = '';
		}
		$sidebaroptions = array(
			'installdate'		=> date( 'Y-m-d' ),
			'donationboxoff'	=> '',
			'subscribeboxoff'	=> '',
			'reportboxoff' 		=> '',
			'adsboxoff'		=> $adsboxoff,
		);
		add_option( $args['settings'] . '_sidebar_options', $sidebaroptions );
	}
}

// ---------------------------
// Load WordQuest Admin Helper
// ---------------------------
add_action( 'autosave_net_loader_helpers', 'autosave_net_load_wordquest_helper', 10, 1 );
function autosave_net_load_wordquest_helper( $args ) {
	if ( is_admin() && ( version_compare( PHP_VERSION, '5.3.0') >= 0 ) ) {
		$wqhelper = dirname( __FILE__ ) . '/wordquest.php';
		if ( file_exists( $wqhelper ) ) {
			include( $wqhelper );
			global $wordquestplugins; $slug = $args['slug'];
			$wordquestplugins[$slug] = $args;
		}
	}
}

// --------------------
// Add Sidebar Floatbox
// --------------------
add_action( 'autosave_net_admin_page_top', 'autosave_net_sidebar_floatbox' );
function autosave_net_sidebar_floatbox() {
	$settings = autosave_net_loader_instance()->args;
	$args = array( $settings['slug'], 'yes' );
	$args = array( 'autosave-net', 'yes' );
	if ( function_exists( 'wqhelper_sidebar_floatbox' ) ) {
		wqhelper_sidebar_floatbox( $args );
		echo wqhelper_sidebar_stickykitscript();
		echo '<style>#floatdiv {float:right;}</style>';
		echo '<script>jQuery("#floatdiv").stick_in_parent();
		jQuery(document).ready(function() {
			wrapwidth = jQuery("#pagewrap").width();
			sidebarwidth = jQuery("#floatdiv").width();
			newwidth = wrapwidth - sidebarwidth - 20;
			jQuery("#wrapbox").css("width",newwidth+"px");
			jQuery("#adminnoticebox").css("width",newwidth+"px");
		});
		</script>';
	}
}		


// ====================
// --- Plugin Setup ---
// ====================

// --------------
// Plugin Options
// --------------
// 1.3.4: updated options to use plugin loader
$options = array(

	// --- Autosave Net Quicksave options ---
	'quicksave_timer'		=> array(
		'type'		=> 'numeric',
		'default'	=> 60,
		'min'		=> 10,
		'max'		=> 10000,
		'step'		=> 10,
		'label'		=> __( 'QuickSave Interval Time', 'autosave-net' ),
		'helper'	=> __( 'Global default for QuickSave Backups.', 'autosave-net'),
		'section'	=> 'quicksave',
	),
	'quicksave_post_types'		=> array(
		'type'		=> 'multicheck',
		'default'	=> array( 'post', 'page' ),
		'options'	=> 'POSTTYPES',
		'label'		=> __( 'QuickSave Post Types', 'autosave-net' ),
		'helper'	=> __( 'Post types to activate QuickSave for.', 'autosave-net' ),
		'section'	=> 'quicksave',
	),
	// 1.3.4: removed plugin setting and made a filter
	// 'quicksave_notice_screens'	=> array(
	//	'type'		=> 'csv',
	//	'default'	=> 'post.php, edit.php',
	//	'label'		=> __( 'QuickSave Notice Screens','autosave-net' ),
	//	'helper'	=> __( 'Admin screens to show QuickSave notices on.', 'autosave-net' ),
	//	'section'	=> 'quicksave',
	// ),
	'quicksave_icons'	=> array(
		'type'		=> 'radio',
		'options'	=> array(
			'dash'		=> __( 'Dashicons', 'autosave-net' ),
			'colour'	=> __( 'Colour Icons', 'autosave-net' ),
		),
		'default'	=> 'dash',
		'label'		=> __( 'QuickSave Icons', 'autosave-net' ),
		'helper'	=> __( 'Icons to use for QuickSave controls.', 'autosave-net' ),
		'section'	=> 'quicksave',
	),

	// --- WordPress Autosave options ---
	'autosave_disable'		=> array(
		'type'		=> 'checkbox',
		'value'		=> '1',
		'default'	=> '',
		'label'		=> __( 'Disable WordPress AutoSave', 'autosave-net' ),
		'helper'	=> __( 'Turns off WordPress AutoSave completely.', 'autosave-net' ),
		'section'	=> 'autosave',
	),
	'autosave_time'			=> array(
		'type'		=> 'numeric',
		'default'	=> 60,
		'min'		=> 10,
		'max'		=> 3600,
		'step'		=> 10,
		'label'		=> __( 'AutoSave Interval Time', 'autosave-net' ),
		'helper'	=> __( 'Time in seconds between WordPress AutoSaves', 'autosave-net' ),
		'section'	=> 'autosave',
	),
	
	// --- WordPress Post Revisions ---
	'autosave_revisions'		=> array(
		'type'		=> 'numeric',
		'default'	=> -1,
		'min'		=> -1,
		'max'		=> 999,
		'step'		=> 1,
		'label'		=> __( 'Limit AutoSave Post Revisions', 'autosave-net' ),
		'helper'	=> __( 'AutoSave Revisions per post. -1 for no limit.', 'autosave-net' ),
		'section'	=> 'revisions',
	),

	// TODO: special: number of revisions per post type ?

	// --- section labels ---
	'sections'                => array(
		'quicksave'	=> __( 'Content QuickSave Settings', 'autosave-net' ),
		'autosave'	=> __( 'WordPress Autosave Settings', 'autosave-net' ),
		'revisions'	=> __( 'WordPress Post Revisions', 'autosave-net' ),
	),
);

// ---------------
// Plugin Settings
// ---------------
// 1.3.4: updated settings to use plugin loader
$slug = 'autosave-net';
$args = array(
	// --- Plugin Info ---
	'slug'			=> $slug,
	'file'			=> __FILE__,
	'version'		=> '0.0.1',

	// --- Menus and Links ---
	'title'			=> 'AutoSave Net',
	'parentmenu'		=> 'wordquest',
	'home'			=> 'http://wordquest.org/plugins/' . $slug . '/',
	'support'		=> 'http://wordquest.org/quest-category/' . $slug . '/',
	// 'share'		=> 'http://wordquest.org/plugins/' . $slug . '/#share',
	'donate'		=> 'https://wordquest.org/contribute/?plugin=' . $slug,
	'donatetext'		=> __( 'Support AutoSave Net' ),
	'welcome'		=> '',	// TODO

	// --- Options ---
	'namespace'		=> 'autosave_net',
	'option'		=> 'autosave_net',
	'options'		=> $options,
	'settings'		=> 'fcs',

	// --- WordPress.Org ---
	'wporgslug'		=> 'autosave-net',
	'textdomain'		=> 'autosave-net',
	'wporg'			=> false,

	// --- Freemius ---
	'freemius_id'		=> '146',
	'freemius_key'		=> 'pk_4c378ea656ccc7fb19bb6227eecca',
	'hasplans'		=> false,
	'hasaddons'		=> false,
	'plan'			=> 'free',
);

// ----------------------------
// Start Plugin Loader Instance
// ----------------------------
require dirname( __FILE__ ) . '/loader.php';
new autosave_net_loader( $args );

// -----------------------
// Define Plugin Constants
// -----------------------
define( 'AUTOSAVENET_FILE', __FILE__ );
define( 'AUTOSAVENET_DIR', dirname( __FILE__ ) );

// -----------------------
// Define Version Constant
// -----------------------
add_action( 'plugins_loaded', 'autosave_net_set_version_constant', 11 );
function autosave_net_set_version_constant() {
	$version = autosave_net_plugin_version();
	if ( !defined( 'AUTOSAVENET_VERSION' ) ) {
		define( 'AUTOSAVENET_VERSION', $version );
	}
}


// --------------------------
// === WordPress AutoSave ===
// --------------------------

// ---------------------------------
// Handle Wordpress AutoSave Options
// ---------------------------------
add_action( 'plugins_loaded', 'autosave_net_wordpress_autosave' );
function autosave_net_wordpress_autosave() {

	// 1.2.5: get filtered plugin options for wordpress autosave
	$autosavedisable = autosave_net_get_setting( 'autosave_net_autosave_disable', true );
	$autosavetime = (int)autosave_net_get_setting( 'autosave_time', true );
	$autosaverevisions = autosave_net_get_setting( 'autosave_revisions', true );

	if ( '1' == $autosavedisable ) {
		// --- disable autosave by dequeueing script ---
		add_action( 'admin_enqueue_scripts', 'autosave_net_dequeue_autosave', 11 );
	} elseif ( ( $autosavetime ) && ( AUTOSAVE_INTERVAL != $autosavetime ) ) {
		// --- override autosave interval time ---
		// 1.3.4: override javascript variable instead as AUTOSAVE_INTERVAL is auto-defined
		// define('AUTOSAVE_INTERVAL', $autosavetime);
		add_action( 'admin_footer', 'autosave_net_override_autosave_interval', 999 );
	}

	// --- limit post revisions ---
	// 1.3.4: simplify check to revision number filtering
	if ( $autosaverevisions > -1 ) {
		// 1.2.5: fix to incorrect function name call
		add_filter( 'wp_revisions_to_keep', 'autosave_net_limit_post_revisions', 10, 2 );
	}
}

// ------------------------------------
// Override AutoSave Time in Javascript
// ------------------------------------
function autosave_net_override_autosave_interval() {
	$autosavetime = (int)autosave_net_get_setting( 'autosave_time', true );
	echo "<script>jQuery(document).ready(function() {";
	echo "if (typeof autosaveL10n == 'object') {";
	echo "autosaveL10n.autosaveInterval = " . $autosavetime . ";";
	echo "console.log('AutoSave Interval changed to " . $autosavetime . "'); console.log(autosaveL10n);";
	echo "} });</script>";	
}

// -------------------------
// Dequeueue AutoSave Script
// -------------------------
function autosave_net_dequeue_autosave() {
	wp_dequeue_script( 'autosave' );
}

// ---------------------------------------
// Limit Wordpress AutoSave Post Revisions
// ---------------------------------------
function autosave_net_limit_post_revisions( $num, $post ) {
	// 1.3.4: added recheck if post type supports revisions
	if ( !post_type_supports( $post->post_type, 'revisions' ) ) {
		$num = 0;
	} else {
		$num = autosave_net_get_setting( 'autosave_revisions' );
	}
	return $num;
}


// -----------------------
// === QuickSave Timer ===
// -----------------------

// ----------------------------
// Get Just Post Content Helper
// ----------------------------
function autosave_net_get_just_post_content( $postid ) {
	global $wpdb;
	// 1.3.4: use wpdb->prepare on query
	$query = "SELECT post_content FROM " . $wpdb->prefix . "posts WHERE ID = %s";
	$query = $wpdb->prepare( $query, $postid );
	$postcontent = $wpdb->get_var( $query );
	return $postcontent;
}

// -----------------------
// Translate Month Display
// -----------------------
function autosave_net_translate_month( $month ) {
	
	// --- set months ---
	$months = array(
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December',
	);

	// --- translate month ---
	global $wp_locale;
	foreach ( $months as $i => $fullmonth ) {
		if ( $month == $fullmonth )  {
			return $wp_locale->get_month( ( $i + 1 ) );
		}
	}

	return $month;
}

// ------------------------
// Edit Screen Admin Notice
// ------------------------
function autosave_net_quicksave_notice() {
	global $pagenow;
	$checknotice = '';
	
	// 1.3.4: remove plugin setting and make this a filter
	// post.php and edit.php screens by default
	// $postscreens = autosave_net_get_setting( 'quicksave_notice_screens', true );
	$postscreens = array( 'post.php', 'edit.php' );
	$postscreens = apply_filters( 'quicksave_notice_screens', $postscreens );

	if ( is_array( $postscreens ) && ( count( $postscreens ) > 0 ) ) {
		foreach ( $postscreens as $postscreen ) {
			if ( $pagenow == $postscreen ) {
				$checknotice = 'yes';
			}
		}
		if ( 'yes' == $checknotice ) {
			autosave_net_quicksave_check_notice( 'adminnotice' );
		}
	}
}


// --------------------------------
// Display QuickSave Content Notice
// --------------------------------
function autosave_net_quicksave_check_notice( $context ) {

	global $post;
	if ( !isset( $post ) || !is_object( $post ) ) {
		return;
	}
	$postid = $post->ID;
	$posttype = get_post_type( $postid );

	$cpts = autosave_net_get_setting( 'quicksave_post_types', true );
	if ( !is_array( $cpts ) ) {
		return;
	}
	if ( !in_array( $posttype, $cpts ) ) {
		return;
	}

	// --- Compare QuickSave Timestamp with Saved Post Content Timestamp ---
	$backuptimestamp = get_post_meta( $postid, '_quicksavetime', true );
	$modifiedtimestamp = get_post_modified_time( 'U', false, $postid, false );

	if ( $backuptimestamp > $modifiedtimestamp ) {

		// --- check for a content difference ---
		$backupcontent = get_post_meta( $postid, '_quicksavecontent', true );
		$savedcontent = autosave_net_get_just_post_content( $postid );

		if ( $backupcontent != $savedcontent ) {

			$backuptime = date( 'H:i:s', $backuptimestamp );
			$modifiedtime = date( 'H:i:s', $modifiedtimestamp );

			// --- bold emphasis on day/month/year differences ---
			$backupyear = date( 'Y', $backuptimestamp );
			$modifiedyear = date( 'Y', $modifiedtimestamp );
			if ( $backupyear != $modifiedyear ) {
				$backupyear = '<b>' . $backupyear . '</b>';
				$modifiedyear = '<b>' . $modifiedyear . '</b>';
			}
			
			// 1.3.4: translate months using WP Locale
			$backupmonth = autosave_net_translate_month( date( 'F', $backuptimestamp ) );
			$modifiedmonth = autosave_net_translate_month( date( 'F', $modifiedtimestamp ) );
			if ( $backupmonth != $modifiedmonth ) {
				$backupmonth = '<b>' . $backupmonth . '</b>';
				$modifiedmonth = '<b>' . $modifiedmonth . '</b>';
				
			}
			$backupday = date( 'jS', $backuptimestamp );
			$modifiedday = date( 'jS', $modifiedtimestamp );
			if ($backupday != $modifiedday) {
				$backupday = '<b>' . $backupday . '</b>';
				$modifiedday = '<b>' . $modifiedday . '</b>';
			}

			$backupdate = $backupday . ' ' . esc_html( __( 'of', 'autosave-net' ) ) . ' ' . $backupmonth . ' ' . $backupyear;
			$backupdisplay = '<b>' . $backuptime . '</b> ' . __( 'on', 'autosave-net' ) . ' ' . $backupdate;
			$modifieddate = $modifiedday . ' ' . esc_html( __( 'of', 'autosave-net' ) ) . ' ' . $modifiedmonth . ' ' . $modifiedyear;
			$modifieddisplay = '<b>' . $modifiedtime . '</b> ' . esc_html( __( 'on', 'autosave-net' ) ) . ' ' . $modifieddate;

			echo '<div class="error" style="padding:5px;line-height:20px;">';
				echo esc_html( __( 'A more recent version of this post has been QuickSaved by AutoSave Net.', 'autosave-net' ) );
				if ( 'adminnotice' == $context ) {
					echo ' <a id="scrolltoquicksave" href="#quicksavemetabox">';
						echo esc_html( __( 'Scroll down to the QuickSave metabox to compare versions.', 'autosave-net' ) );
					echo '</a><br>';
				} else {
					$message .= "<br>";
				}
				echo esc_html( __( 'The QuickSave version was saved at', 'autosave-net' ) ) . ': ' . $backupdisplay . '<br>';
				echo esc_html( __( 'and the actual post was last saved at', 'autosave-net' ) ) . ': ' . $modifieddisplay . '<br>';
			echo '</div>';

			// 1.2.5: moved smooth scroll jump script to footer
			add_action( 'admin_footer', 'autosave_net_smooth_scroll_jump' );
		}
	}
}

// ---------------------
// Add Quicksave Metabox
// ---------------------
add_action( 'admin_init', 'autosave_net_quicksave_add_metabox' );
function autosave_net_quicksave_add_metabox() {
	$cpts = autosave_net_get_setting( 'quicksave_post_types' );
	// 1.3.1: fix to default array fallback
	if ( !is_array( $cpts ) ) {
		$cpts = array( 'post','page' );
	}
	foreach ( $cpts as $cpt ) {
		add_meta_box( 'autosave_net_metabox', __( 'QuickSave Content Backup Timer', 'autosave-net' ), 'autosave_net_quicksave_metabox', $cpt, 'normal', 'low' );
	}
	// TODO: maybe use add_settings_error instead ?
	add_action( 'admin_notices', 'autosave_net_quicksave_notice' );
}

// -----------------
// Quicksave Metabox
// -----------------
function autosave_net_quicksave_metabox() {

	global $pagenow, $post;
	$postid = $post->ID;

	// --- check disable for post switch ---
	$quicksavedisabled = get_post_meta( $postid, '_quicksavedisabled', true );
	$quicksavedisabled = apply_filters( 'quicksave_disabler', $quicksavedisabled );
	if ( !$quicksavedisabled ) {
		$quicksavedisabled = '';
	}

	// --- if this is a new post, do nothing and tell user ---
	if ( 'post-new.php' == $pagenow ) {
		echo esc_html( __( 'QuickSave does not launch until you Publish - or Save a Draft and refresh.', 'autosave-net' ) );
		return;
	}

	// Timer Variables
	// ---------------
	$timestamp = time();
	// delete_post_meta( $postid, '_quicksavetime' );
	$backuptime = get_post_meta( $postid, '_quicksavetime', true );
	$latestcontent = '';

	// Filterable Timer Value
	// ----------------------
	$quicksavetimer = absint( autosave_net_get_setting( 'quicksave_timer', true ) );

	// --- a name hash scroll link ---
	echo '<a name="quicksavemetabox"></a>';

	// Resize Helper Wrapper
	echo '<div id="quicksavewrapper">';

	// display check message if NOT in the filtered quicksave post screens
	// 1.3.4: remove plugin setting and make a filter
	// $postscreens = autosave_net_get_setting( 'quicksave_notice_screens', true );
	$postscreens = array( 'post.php', 'edit.php' );
	$postscreens = apply_filters( 'quicksave_notice_screens', $postscreens );
	$checknotice = '';
	if ( is_array( $postscreens ) && ( count( $postscreens ) > 0 ) ) {
		foreach ( $postscreens as $postscreen ) {
			if ( $pagenow == $postscreen ) {
				$checknotice = 'yes';
			}
		}
		if ( 'yes' != $checknotice ) {
			autosave_net_quicksave_check_notice( 'adminnotice' );
		}
	}

	// QuickSave Info Box
	// ------------------
	// 1.2.0: fix to icon logic here
	if ( file_exists( get_stylesheet_directory() . '/images/quicksave-icons.png' ) ) {
		$iconsurl = get_stylesheet_directory_uri() . '/images/quicksave-icons.png';
	} elseif ( file_exists( get_template_directory() . '/images/quicksave-icons.png' ) ) {
		$iconsurl = get_template_directory_uri() . '/images/quicksave-icons.png';
	} else {
		$iconsurl = plugins_url( 'images/quicksave-icons.png', __FILE__ );
	}
	// else {$iconurl = admin_url('/images/menu.png'); $menuicon = 'yes';}
	$menuicon = 'no';

	echo '<div id="quicksaveinfobox">';
		echo '<center><table><tr><td>';
			echo '<div style="background-image:url(\'' . esc_url( $iconsurl ) . '\');';
			if ( 'yes' == $menuicon ) {
				echo 'width:28px;height:28px;background-position:-149px -1px;';
			} else {
				echo 'width:32px;height:32px;background-position:0px 0px;';
			}
			echo '" title="' . esc_attr( __( 'Hide QuickSave Info' ) ) . '" onclick="quicksave_show_hide(\'quicksaveinfobox\');quicksave_info(\'off\');">';
		echo '</td><td width="10"></td><td>';
			echo '<ul style="padding:0;list-style:none;display:inline-block;">';
				echo '<li style="display:inline-block;">';
					echo '<a href="https://wordquest.org/plugins/autosave-net/" title="AutoSave Net Content Backup Timer" target=_blank style="text-decoration:none;"><b>AutoSave Net</b></a>';
					echo ' v' . AUTOSAVENET_VERSION;
					echo ' ' . esc_html( __( 'by', 'autosave-net' ) );
					echo ' <a href="https://wordquest.org" target=_blank style="text-decoration:none;">WordQuest</a></li>';
				echo '<li style="display:inline-block;width:30px;"></li>';
				echo '<li style="display:inline-block;font-size:9pt;"><a href="https://wordquest.org/contribute/?plugin=autosave-net" target=_blank style="text-decoration:none;">&rarr; ' . esc_html( __('Contribute','autosave-net' ) ) . '</a></li>';
				echo '<li style="display:inline-block;width:10px;"></li>';
				echo '<li style="display:inline-block;font-size:9pt;"><a href="https://wordquest.org/plugins/" target=_blank style="text-decoration:none;">&rarr; ' . esc_html( __( 'More Great Plugins', 'autosave-net' ) ) . '</a></li>';
			echo '</ul>';
		echo '</td></tr></table></center>';
	echo '</div>';

	// QuickSave Global Settings
	// -------------------------
	$settingsurl = admin_url( 'options-general.php' ) . '?page=autosave-net';
	echo '<div id="quicksavesettings" style="display:none;"><center>';
	if ( current_user_can( 'manage_options' ) ) {
		echo '<a href="' . esc_url( $settingsurl ) . '" target="_blank">';
			echo esc_html( __( 'Click here for Global QuickSave Settings on the AutoSave Net admin page.', 'autosave-net' ) );
		echo '</a>.<br>';
	} else {
		echo esc_html( __( 'Contact your Site Administrator to adjust QuickSave Global Settings for AutoSave Net.', 'autosave-net' ) ) . '<br>';
	}
	echo '</center></div>';

	// User Controls
	// -------------
	echo '<center><table><tr>';
		echo '<td><div id="infobutton" style="display:none;margin-right:10px;background-image:url(\'' . esc_url( $iconsurl ) . '\');';
		if ( 'yes' == $menuicon ) {
			echo 'width:28px;height:28px; background-position:-149px -1px;';
		} else {
			echo 'width:32px;height:32px;background-position:0px 0px;';
		}
		echo '" title="' . esc_attr( __( 'Show QuickSave Info', 'autosave-net' ) ) . '" onclick="quicksave_show_hide(\'quicksaveinfobox\');quicksave_info(\'on\');"></td>';

		// --- Plus/Minus Timer Adjustment ---
		echo '<td><table cellpadding="0" cellspacing="0"><tr>';
			echo '<td><input type="button" class="button-secondary" title="' . esc_attr( __( 'Decrement Timer Length', 'autosave-net' ) ) . '" value="-" style="font-weight:bold;" onclick="quicksave_decrement_timer();"></td>';
			echo '<td><input type="text" id="quicksavetime" value="' . esc_attr( $quicksavetimer ) . '" style="width:50px;text-align:center;"></td>';
			echo '<td><input type="button" class="button-secondary" title="' . esc_attr( __( 'Increment Timer Length', 'autosave-net' ) ) . '" value="+" style="font-weight:bold;" onclick="quicksave_increment_timer();"></td>';
			echo '<td width="5"></td><td>' . esc_html( __( 'second save cycle', 'autosave-net' ) ) . '.</td>';
		echo '</tr></table></td>';

		// ---  Readonly Timer Countdown ---
		echo '<td width="20"></td>';
		echo '<td>' . esc_html( __( 'QuickSaving in', 'autosave-net' ) ) . ':</td>';
		echo '<td width="5"></td>';
		echo '<td><input id="quicksavecountdown" type="text" style="width:30px;text-align:center;" value="' . esc_attr( $quicksavetimer ) . '" readonly></td>';

		// --- QuickSave Tool Buttons ---
		$colouricons = autosave_net_get_setting( 'quicksave_icons', true );
		if ( $colouricons ) {
			// 1.3.4: fix to incorrect quoting
			$iconstyle = "background-image:url('" . esc_url( $iconsurl ) . "');"; 
			$dashicons = '';
		} else {
			$iconstyle = '';
			$dashicons = 'dashicons ';
		}
		if ( 'yes' == $quicksavedisabled ) {
			$enable = '';
			$disable = 'display:none;';
			$pauser = 'display:none;';
		} else {
			$disable = '';
			$enable = 'display:none;';
			$pauser = '';
		}

		echo '<td width="20"></td>';
		// dashicon play f522 "dashicons-controls-play", pause f523 "dashicons-controls-pause"
		echo '<td><div id="quicksavepauser" title="' . esc_attr( __( 'Pause QuickSave Timer', 'autosave-net' ) ) . '" onclick="quicksave_timer_pause();"';
		echo ' style="' . esc_attr( $pauser ) . 'width:32px;height:32px;background-position: -32px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons ) . 'dashicons-controls-pause"';
		echo '></div><div id="quicksaveresumer" title="'.__('Resume QuickSave Timer','autosave-net').'" onclick="quicksave_timer_pause();"';
		echo ' style="display:none;width:32px;height:32px;background-position: -64px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons );
		if ( '' != $dashicons ) {echo ' dashicons-controls-play';}
		echo '"></div></td>';
		// dashicon reset f463 "dashicons dashicons-update"
		echo '<td><div id="quicksavereset" title="' . esc_attr( __( 'Reset QuickSave Timer', 'autosave-net' ) ) . '" onclick="quicksave_timer_reset();"';
		echo ' style="width:32px;height:32px;background-position: -96px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons );
		if ( '' != $dashicons ) {echo ' dashicons-update';}
		echo '"></div></td>';
		// dashicon enable f147 "dashicons-yes", disable f335 "dashicons-no"
		echo '<td><div id="quicksaveenabler" title="' . esc_attr( __( 'Enable QuickSave Timer for this Post', 'autosave-net' ) ) . '" onclick="quicksave_enable_disable();"';
		echo ' style="' . $enable . 'width:32px;height:32px;background-position: -160px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons ) . 'dashicons-yes"';
		echo '></div><div id="quicksavedisabler" title="'.__('Disable QuickSave Timer for this Post','autosave-net').'" onclick="quicksave_enable_disable();"';
		echo ' style="' . $disable . 'width:32px;height:32px;background-position: -128px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons );
		if ( '' != $dashicons ) {echo ' dashicons-no';}
		echo '"></div></td>';
		// dashicon save ? f119 "dashicons dashicons-welcome-write-blog"
		echo '<td><div id="quicksavesavenow" title="' . esc_attr( __( 'QuickSave Now', 'autosave-net' ) ) . '" onclick="quicksave_manual_quicksave();"';
		echo ' style="width:32px;height:32px;background-position: -192px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons );
		if ( '' != $dashicons ) {echo ' dashicons-welcome-write-blog';}
		echo '"></div></td>';
		// dashicon settings f111 "dashicons dashicons-admin-generic"
		echo '<td><div id="quicksaveoptions" title="' . esc_attr( __( 'QuickSave Settings', 'autosave-net' ) ) . '" onclick="quicksave_show_hide(\'quicksavesettings\');"';
		echo ' style="width:32px;height:32px;background-position: -224px 0px;' . $iconstyle . '" class="' . esc_attr( $dashicons );
		if ( '' != $dashicons ) {echo ' dashicons-admin-generic';}
		echo '"></div></td>';

	// end table
	echo '</tr></table></center>';

	// QuickSave Message Box
	// ---------------------
	echo '<div id="quicksavebox" style="display:none;text-align:center;border-radius:3px;padding:3px;">';
	echo '<span id="quicksavemessage">&nbsp;</span></div><br>';

	// Display Pageload Backup Content
	// -------------------------------
	$timedisplay = '';
	$datedisplay = '';
	if ( '' != $backuptime ) {
		if ( $timestamp > $backuptime ) {
			$timedisplay = date( 'H:i:s', $backuptime );
			$datedisplay = date( 'jS \o\f F Y', $backuptime );
			$backupcontent = $latestcontent = get_post_meta( $postid, '_quicksavecontent', true );
			if ( $backupcontent && ( '' != $backupcontent ) ) {
				echo '<input type="button" class="button-secondary" style="font-weight:bold;" onclick="quicksave_show_hide(\'pageloadbackup\');" value="' . esc_attr( __( 'Pageload QuickSaved Content', 'autosave-net' ) ) . '">';
				echo '<span style="margin-left:30px;">' . esc_html( __( 'QuickSave Time', 'autosave-net' ) ) . ':</span>';
				echo '<span style="margin-left:30px;" id="pageloadtime"><b>' . $timedisplay . '</b> ' . esc_html( __( 'on', 'autosave-net' ) ) . $datedisplay . '</span><br>';
				echo '<div id="pageloadbackup" style="display:none;">';
				echo '<table style="width:100%;">';
					echo '<tr><td colspan="7">';
						echo '<textarea rows="10" cols="80" style="width:100%;" id="pageloadbackupcontent" readonly>' . $backupcontent . '</textarea>';
					echo '</td></tr><tr><td width="30"></td><td align="center">';
						echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares QuickSave Backup on Pageload with Current Edited Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Current', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'pageload\',\'current\');">';
					echo '</td><td width="30"></td><td align="center">';
						echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares Quicksave Backup on Pageload with Saved Database Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Database', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'pageload\',\'saved\');">';
					echo '</td><td width="30"></td><td align="center">';
						echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Restores Quicksave Backup on Pageload to Content Editing Area', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Restore this QuickSave', 'autosave-net' ) ) . '" onclick="quicksave_restore_content(\'pageload\');">';
					echo '</td></tr>';
				echo '</table></div><br>';
			}
		}
	}

	// Display Latest QuickSave Content
	// --------------------------------
	echo '<input type="button" class="button-secondary" style="font-weight:bold;margin-left:15px;" onclick="quicksave_show_hide(\'latestquicksave\');" value="' . esc_attr( __( 'Latest QuickSaved Content', 'autosave-net' ) ) . '">';
	echo '<span style="margin-left:30px;">' . esc_html( __( 'QuickSave Time', 'autosave-net' ) ) . ':</span>';
	echo '<span style="margin-left:30px;" id="quicksavelatesttime">';
	if ( ( '' != $timedisplay ) && ( '' != $datedisplay ) ) {
		echo '<b>' . $timedisplay . '</b> ' . esc_html( __('on', 'autosave-net' ) ) . ' ' . $datedisplay;
	} else {
		echo esc_html( __( 'No QuickSave has happened yet...', 'autosave-net' ) );
	}
	echo '</span><br>';
	echo '<div id="latestquicksave" style="display:none;">';
		echo '<table style="width:100%;">';
			echo '<tr><td colspan="7">';
				echo '<textarea rows="10" cols="80" style="width:100%;" id="latestquicksavecontent" readonly>' . $latestcontent . '</textarea>';
			echo '</td></tr><tr><td width="30"></td><td align="center">';
				echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares Latest QuickSave Backup with Current Edited Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Current', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'latest\',\'current\');">';
			echo '</td><td width="30"></td><td align="center">';
				echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares Latest Quicksave Backup with Saved Database Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Database', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'latest\',\'saved\');">';
			echo '</td><td width="30"></td><td align="center">';
				echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Restores Latest Quicksave Backup to Content Editing Area', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Restore this QuickSave', 'autosave-net' ) ) . '" onclick="quicksave_restore_content(\'latest\');">';
			echo '</td></tr>';
		echo '</table></div><br>';

	// Hidden Backup Content Display
	// -----------------------------
	echo '<div id="contentbackupwrapper" style="display:none;">';
	echo '<input type="button" class="button-secondary" style="font-weight:bold;margin-left:40px;" onclick="quicksave_show_hide(\'contentbackup\');" value="' . esc_attr( __( 'Content before Restore', 'autosave-net' ) ) . '">';
	echo '<span style="margin-left:50px;">' . esc_html( __( 'Restore Time', 'autosave-net' ) ) . ':</span>';
	echo '<span style="margin-left:30px;" id="contentbackuptime"></span><br>';
	echo '<div id="contentbackup" style="display:none;">';
	echo '<table style="width:100%;">';
		echo '<tr><td colspan="7">';
			echo '<textarea rows="10" cols="80" style="width:100%;" id="contentbackupcontent" readonly>' . $latestcontent . '</textarea>';
		echo '</td></tr><tr><td width="30"></td><td align="center">';
			echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares Content Backup with Current Edited Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Current', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'backup\',\'current\');">';
		echo '</td><td width="30"></td><td align="center">';
			echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Compares Content Backup with Saved Database Content', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Compare with Database', 'autosave-net' ) ) . '" onclick="quicksave_compare_versions(\'backup\',\'saved\');">';
		echo '</td><td width="30"></td><td align="center">';
			echo '<input type="button" class="button-secondary" title="' . esc_attr( __( 'Restores Content Backup to Content Editing Area', 'autosave-net' ) ) . '" value="' . esc_attr( __( 'Restore this Backup', 'autosave-net' ) ) . '" onclick="quicksave_restore_content(\'backup\');">';
		echo '</td></tr>';
	echo '</table></div><br></div>';

	// --- hidden comparison iframe form target ---
	echo '<div id="compareframewrapper" style="display:none;">';
		echo '<br><center><b>' . esc_html( __( 'Content Comparison', 'autosave-net' ) ) . '</b></center><br>';
		echo '<iframe name="compareframe" id="compareframe" src="javascript:void(0);" width="95%" height="200"></iframe>';
	echo '</div>';

	// --- hidden restored message ---
	echo '<div id="restoredwrapper" style="display:none;background-color:#F0F000;text-align:center;">';
	echo '<span id="restoredmessage"></span></div>';

	// --- end quicksave wrapper ---
	echo '</div>';

	// --- enqueue Quicksave Forms ---
	// put form in footer to not conflict with other forms
	add_action( 'admin_footer','autosave_net_quicksave_save_forms' );

	// --- enqueue QuickSave script ---
	add_action( 'admin_footer','autosave_net_quicksave_save_javascript' );
	
}

// --------------------
// QuickSave Save Forms
// --------------------
function autosave_net_quicksave_save_forms() {

	global $post;
	$postid = $post->ID;
	$quicksavetimer = absint( apply_filters( 'quicksave_timer', get_option( 'quicksave_timer' ) ) );
	$userid = get_current_user_id();

	// --- quicksave form ---
	echo '<form id="quicksaveform" name="quicksaveform" target="quicksaveframe" action="admin-ajax.php?action=do_quick_save" method="post">';
	echo '<input type="hidden" id="quicksaveid" name="quicksaveid" value="' . esc_attr( $postid ) . '">';
	echo '<input type="hidden" id="quicksaveuserid" name="quicksaveuserid" value="' . esc_attr( $userid ) . '">';
	echo '<textarea style="display:none;" id="quicksavecontent" name="quicksavecontent"></textarea>';
	echo '<input type="hidden" id="quicksavetimer" name="quicksavetimer" value="' . esc_attr( $quicksavetimer ) . '">';
	echo '<input type="hidden" id="quicksaveusertimer" name="quicksaveusertimer" value="' . esc_attr( $quicksavetimer ) . '">';
	echo '<input type="hidden" id="quicksavemanual" name="quicksavemanual" value="">';
	echo '<input type="hidden" id="quicksavepause" name="quicksavepause" value="">';
	echo '</form>';
	// - hidden iframe form target -
	echo '<iframe style="display:none;" name="quicksaveframe" id="quicksaveframe" src="javascript:void(0);"></iframe>';

	// --- compare form ---
	echo '<form id="compareform" name="compareform" target="compareframe" action="admin-ajax.php?action=compare_quick_saves" method="post">';
	echo '<input type="hidden" id="comparepostid" name="comparepostid" value="' . esc_attr( $postid ) . '">';
	echo '<input type="hidden" id="compareuserid" name="compareuserid" value="' . esc_attr( $userid ) . '">';
	echo '<textarea style="display:none;" id="savedcontent" name="savedcontent"></textarea>';
	echo '<textarea style="display:none;" id="currentcontent" name="currentcontent"></textarea>';
	echo '<input type="hidden" id="savedtype" name="savedtype" value="">';
	echo '<input type="hidden" id="contenttype" name="contenttype" value="">';
	echo '</form>';

	// --- backup time form ---
	echo '<form id="backuptimeform" name="backuptimeform" target="backuptimeframe" action="admin-ajax.php?action=quicksave_update_backup_time" method="post">';
	echo '<input type="hidden" id="backupsource" name="backupsource" value="' . esc_attr( $postid ) . '">';
	echo '</form>';
	echo '<iframe style="display:none;" name="backuptimeframe" id="backuptimeframe" src="javascript:void(0);"></iframe>';

	// 1.2.5: fix to missing meta value for post disable
	$quicksavedisabled = get_post_meta( $postid, '_quicksavedisabled', true );
	$quicksavedisabled = apply_filters( 'quicksave_disabler', $quicksavedisabled, $postid );
	if ( !$quicksavedisabled ) {
		$quicksavedisabled = '';
	}

	// --- disable form ---
	echo '<form id="quicksavedisableform" name="quicksavedisableform" target="quicksavedisableframe" action="admin-ajax.php?action=quicksave_enable_disable" method="post">';
	echo '<input type="hidden" id="quicksavedisablepostid" name="quicksavedisablepostid" value="' . esc_attr( $postid ) . '">';
	echo '<input type="hidden" id="quicksavedisableuserid" name="quicksavedisableuserid" value="' . esc_attr( $userid ) . '">';
	echo '<input type="hidden" id="quicksavedisable" name="quicksavedisable" value="' . esc_attr( $quicksavedisabled ) . '">';
	echo '</form>';
	// - hidden iframe form target -
	echo '<iframe style="display:none;" name="quicksavedisableframe" id="quicksavedisableframe" src="javascript:void(0);"></iframe>';
}
	
// --------------------
// QuickSave Javascript
// --------------------
function autosave_net_quicksave_save_javascript() {

	// DocReady Javascript
	// -------------------
	echo "<script>";
	echo autosave_net_docready_javascript();
	echo "</script>";

	// QuickSave Javascript
	// -------------------
	// 1.3.4: fix to metabox ID and style height in quicksave_wrapper_resize
	echo "<script>

	var thepostcontent; var quicksavetimer; var quicksavecycle;
	var quicksavedisabled = document.getElementById('quicksavedisable').value;

	function quicksave_wrapper_resize() {
		newheight = document.getElementById('quicksavewrapper').scrollHeight;
		document.getElementById('autosave_net_metabox').style.height = newheight + 50 + 'px';
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

	function quicksave_manual_quicksave() {
		document.getElementById('quicksavemanual').value = 'yes';
		doquicksave();
		document.getElementById('quicksavemanual').value = '';
	}

	function quicksave_timer() {
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

	function quicksave_cycle() {quicksavecycle = setInterval(quicksave_timer, 1000);}
	/* Load quicksave cycle when document is ready */
	window.docReady(quicksave_cycle);

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

		if (source == 'latest') {var displaysource = '". esc_js( __( 'the Latest QuickSave Content', 'autosave-net' ) ) . "';}
		if (source == 'pageload') {var displaysource = '" . esc_js( __( 'the Pageload QuickSave Content', 'autosave-net' ) ) . "';}
		if (source == 'backup') {var displaysource = '" . esc_js( __( 'the Content before Restore', 'autosave-net' ) ) . "';}

		var agree = confirm('". esc_js( __( 'Are you sure you want to Restore', 'autosave-net' ) ) . "\\n'+displaysource+'?');
		if (!agree) {return false;}

		backuptimeform = document.getElementById('backuptimeform');
		document.getElementById('backupsource').value = source;
		backuptimeform.submit();

		if (source == 'latest') {
			document.getElementById('contentbackupcontent').value = document.getElementById('content').value;
			document.getElementById('content').value = document.getElementById('latestquicksavecontent').value;
			restoredmessage = '" . esc_js( __( 'Latest QuickSave Content has been Restored.', 'autosave-net' ) ) . "';
		}
		if (source == 'pageload') {
			document.getElementById('contentbackupcontent').value = document.getElementById('content').value;
			document.getElementById('content').value = document.getElementById('pageloadbackupcontent').value;
			restoredmessage = '" . esc_js( __( 'QuickSave Pageload Content has been Restored.', 'autosave-net' ) ) . "';
		}
		if (source == 'backup') {
			var currentcontent = document.getElementById('content').value;
			document.getElementById('content').value = document.getElementById('contentbackupcontent').value;
			document.getElementById('contentbackupcontent').value = currentcontent;
			restoredmessage = '" . esc_js( __( 'Content before Restore - has now been Restored.', 'autosave-net' ) ) . "';
		}
		alert(restoredmessage);
	}
	</script>";

}


// --------------------
// === AJAX Actions ===
// --------------------

// ------------------
// Quicksave via AJAX
// ------------------
add_action( 'wp_ajax_do_quick_save', 'autosave_net_quicksave_do_quicksave' );
function autosave_net_quicksave_do_quicksave() {

	// --- check conditions and maybe exit ---
	if ( !isset( $_POST['quicksaveid'] ) || !isset( $_POST['quicksavecontent'] ) ) {
		echo "Error 1.";
		exit;
	}
	$postid = $_POST['quicksaveid'];
	$content = $_POST['quicksavecontent'];
	$timer = $_POST['quicksavetimer'];
	$usertimer = $_POST['quicksaveusertimer'];
	$userid = $_POST['quicksaveuserid'];
	$savetime = $_POST['quicksaveposttime'];
	$manual = $_POST['quicksavemanual'];

	if ( ( '' == $postid ) || !is_numeric( $postid ) ) {
		echo "Error 2.";
		exit;
	}
	if ( '' == $content ) {
		exit;
	}
	if ( !current_user_can( 'edit_post', $postid ) ) {
		exit;
	}
	$currentuserid = get_current_user_id();
	if ( $userid != $currentuserid ) {
		echo "Error 4.";
		exit;
	}

	// Compare Timestamp (pointless here?)
	$timestamp = time();
	$backuptime = get_post_meta( $postid, '_quicksavetime', true );
	if ( '' != $backuptime ) {
		if ( $backuptime > $timestamp ) {
			echo "Error 5.";
			exit;
		}
	}

	// check for disabled switch
	// as another user may have disabled quicksave for this post
	$disabled = get_post_meta( $postid, '_quicksavedisabled', true );
	if ( ( 'yes' == $disabled ) && ( 'yes' != $manual ) ) {
		echo "Error 6.";
		exit;
	}

	// Update the _quicksavecontent meta value
	$content = stripslashes( $content );
	$backupcontent = get_post_meta( $postid, '_quicksavecontent', true );
	delete_post_meta( $postid, '_quicksavecontent' );
	$quicksave = add_post_meta( $postid, '_quicksavecontent', $content );
	$quicksavetime = update_post_meta( $postid, '_quicksavetime', $timestamp );

	// If the user timer has been adjusted, save that too
	if ( ( $timer != $usertimer) && is_numeric( $usertimer ) ) {
		update_post_meta( $postid, '_quicksavetimer', $usertimer );
	}

	// Javascript Callbacks
	// --------------------
	echo "<head><script>";
	$timestamp = time();
	$displaytime = date( 'H:i:s', $timestamp );
	$displaydate = date( 'jS \o\f F Y', $timestamp );
	$message = __( 'QuickSaved', 'autosave-net' ) . ': <b>' . $displaytime . '</b> ' . esc_html( __( 'on', 'autosave-net' ) ) . ' ' . $displaydate;
	$message .= ' (' . esc_html( __( 'server time.', 'autosave-net' ) ) . ')';
	$failed = __( 'QuickSave Failed.', 'autosave-net' );
	if ( $quicksave ) {
		echo "parent.document.getElementById('quicksavebox').style.display = '';";
		echo "parent.document.getElementById('quicksavebox').style.backgroundColor = '#F0F000';";
		echo "parent.document.getElementById('quicksavemessage').innerHTML = '" . $message . "';";
		echo "parent.document.getElementById('quicksavelatesttime').innerHTML = '<b>" . $displaytime . "</b> " . __('on', 'autosave-net' ) . ' ' . $displaydate . "';";
		echo "parent.document.getElementById('latestquicksavecontent').value = parent.document.getElementById('content').value;";
		if ( 'yes' == $manual ) {
			if ( 'yes' == $disabled ) {
				echo "alert('" . __( 'QuickSaved! Warning: Timer is disabled for this post.', 'autosave-net' ) . "');";
			} else {
				echo "parent.quicksave_timer_reset();";
				echo "alert('" . __( 'QuickSaved! Timer has been reset.', 'autosave-net' ) . "');";
			}
		}
	} else {
		echo "parent.document.getElementById('quicksavebox').style.display = '';";
		echo "parent.document.getElementById('quicksavemessage').innerHTML = '" . $failed . "';";
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

	echo '<body>' . $content . '</body>';

	exit;
}

// ------------------------------
// Compare Saved Content via AJAX
// ------------------------------
add_action( 'wp_ajax_compare_quick_saves', 'autosave_net_quicksave_compare_saved_versions' );
function autosave_net_quicksave_compare_saved_versions() {

	$postid = $_POST['comparepostid'];
	if ( ( '' == $postid ) || !is_numeric( $postid ) ) {
		echo 'Error 1. No Post ID.'; return false;
	}

	if ( !current_user_can( 'edit_post', $postid ) ) {
		echo 'Error 2. Cannot Edit.'; return false;
	}
	$userid = $_POST['compareuserid'];
	$currentuserid = get_current_user_id();
	if ( $currentuserid != $userid) {
		echo 'Error 3. User Mismatch.'; return false;
	}

	$savedtype = $_POST['savedtype'];
	if ( 'latest' == $savedtype ) {
		$quicksavecontent = get_post_meta( $postid, '_quicksavecontent', true );
		$args['title_left'] = __( 'Latest QuickSave Content', 'autosave-net' );
	} elseif ( 'pageload' == $savedtype ) {
		$quicksavecontent = stripslashes( $_POST['savedcontent'] );
		$args['title_left'] = __( 'Pageload QuickSave Content', 'autosave-net' );
	} elseif ( 'backup' == $savedtype ) {
		$quicksavecontent = stripslashes( $_POST['savedcontent'] );
		$args['title_left'] = __( 'Content before Restore', 'autosave-net' );
	} else {
		return false;
	}

	$contenttype = $_POST['contenttype'];
	if ( 'current' == $contenttype ) {
		$postcontent = stripslashes( $_POST['currentcontent'] );
		$args['title_right'] = __( 'Currently Edited Post Content','autosave-net' );
		$timestamp = time();
	} elseif ( 'saved' == $contenttype ) {
		$postcontent = autosave_net_get_just_post_content( $postid );
		$args['title_right'] = __( 'Wordpress Saved Post Content', 'autosave-net' );
		$timestamp = get_post_modified_time( 'U', false, $postid, false );
	} else {
		return false;
	}

	$displaytime = date( 'H:i:s', $timestamp );
	$displaydate = date( 'jS \o\f F Y', $timestamp );
	$savedtime = '<b>' . $displaytime . '</b> ' . esc_html( __( 'on', 'autosave-net' ) ) . ' ' . $displaydate;

	// Here is where the magic happens...
	// Do the comparison using in-built function wp_text_diff
	$args['title'] = get_the_title( $postid );
	$difference = wp_text_diff( $quicksavecontent, $postcontent, $args );

	if ( !$difference ) {
		echo "<head><script language='javascript' type='text/javascript'>
			alert('" . __( 'No difference was found between', 'autosave-net' ) . ":\\n" . $args['title_left'] . ' ' . __( 'and', 'autosave-net' ) . "\\n " . $args['title_right'] . ".');
			parent.quicksave_wrapper_resize();
		</script></head>";
	} else {
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );
		wp_enqueue_script( 'utils' );
		wp_enqueue_script( 'buttons' );
		echo '<html><head>';

		// DocReady
		echo "<script>";
		echo autosave_net_docready_javascript();
		echo "</script>";

		// Comparison Javacript
		echo "<script language='javascript' type='text/javascript'>
		parent.document.getElementById('compareframewrapper').style.display = '';

		function quicksave_resize_iframe(newHeight) {
			parent.document.getElementById('compareframe').style.height = parseInt(newHeight) + 30 + 'px';
		}
		function quicksave_closecomparison() {
			parent.document.getElementById('compareframewrapper').style.display = 'none';
			parent.document.getElementById('compareframe').src = 'javascript:void(0);';
			parent.quicksave_wrapper_resize();
		}
		function quicksave_restorecontent() {
			restoreform = document.getElementById('restoreform');
			restoreform.submit();
		}
		function quicksave_displaytimestamps() {
			var savedtime = '" . $savedtime . "';";
			if ( 'pageload' == $savedtype ) {
				echo "var quicksavetime = parent.document.getElementById('pageloadtime').innerHTML;";
			}
			if ( 'latest' == $savedtype ) {
				echo "var quicksavetime = parent.document.getElementById('quicksavelatesttime').innerHTML;";
			}
			if ( 'backup' == $savedtype ) {
				echo "var quicksavetime = parent.document.getElementById('contentbackuptime').innerHTML;";
			}
			echo "
			document.getElementById('savedtimedisplay').innerHTML = savedtime;
			document.getElementById('quicksavedtimedisplay').innerHTML = quicksavetime;
			document.getElementById('timedisplay').style.display = '';
		}
		window.docReady(quicksave_displaytimestamps);
		</script>";

		do_action( 'admin_print_styles' );
		echo "</head>";
		echo "<body onload='quicksave_resize_iframe(document.body.scrollHeight)'>";

		// restore form
		echo '<form id="restoreform" name="restoreform" action="admin-ajax.php?action=restore_quick_save" method="post">';
		echo '<input type="hidden" id="restorepostid" name="restorepostid" value="' . esc_attr( $postid ) . '">';
		echo '<input type="hidden" id="restoreuserid" name="restoreuserid" value="' . esc_attr( $userid ) . '">';
		echo '<input type="hidden" id="savedtype" name="savedtype" value="' . esc_attr( $savedtype ) . '">';
		echo '<input type="hidden" id="contenttype" name="contenttype" value="' . esc_attr( $contenttype ) . '">';
		echo '<textarea id="quicksavedcontent" name="quicksavedcontent" style="display:none;" readonly="readonly">' . $quicksavecontent . '</textarea>';
		echo '<textarea id="savedcontent" name="savedcontent" style="display:none;" readonly="readonly">' . $postcontent . '</textarea>';
		echo '</form>';

		echo "<input style='float:left;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='quicksave_restorecontent(\"" . esc_attr( $savedtype ) . "\",\"" . esc_attr( $contenttype ) . "\");' value='" . esc_attr( __( 'Restore Left-Hand Content', 'autosave-net' ) ) . "'>";
		echo "<input style='float:right;background:#EEE;border-radius:3px;' type='button' class='button-secondary' onclick='quicksave_closecomparison();' value='" . esc_attr( __( 'Close Comparison', 'autosave-net' ) ) . "'><br><br>";
		echo "<div id='timedisplay' style='display:none;'>";
		echo "<span id='quicksavedtimedisplay' style='float:left;margin:10px;'></span>";
		echo "<span id='savedtimedisplay' style='float:right;margin:10px;'></span>";
		echo "</div>";

		// 1.2.5: fixes to difference table output
		$difference = str_replace( '<td></td><th>' . $args['title_left'], '<th>' . $args['title_left'], $difference );
		$difference = str_replace( '<td></td><th>' . $args['title_right'], '<th>' . $args['title_right'], $difference );
		$difference = str_replace( '<td>&nbsp;</td>', '', $difference );
		$difference .= "<style>table.diff {table-layout:auto !important;}
		td.diff-context, td.diff-deletedline, td.diff-addedline {width:50% !important;}</style>";

		// phpcs:ignore WordPress.Security.OutputNotEscaped
		echo '<br>' . $difference . '<br>';
		echo '<input style="float:left;background:#EEE;border-radius:3px;" type="button" class="button-secondary" onclick="quicksave_restorecontent(\'' . esc_attr( $savedtype ) . '\',\'' . esc_attr( $contenttype ) . '\');" value="' . esc_attr( __( 'Restore Left-Hand Content', 'autosave-net' ) ) . '">';
		echo '<input style="float:right;background:#EEE;border-radius:3px;" type="button" class="button-secondary" onclick="quicksave_closecomparison();" value="' . esc_attr( __( 'Close Comparison', 'autosave-net' ) ) . '">';
		echo '</body>';

		do_action( 'admin_print_footer_scripts' );
		echo '</html>';
	}

	exit;
}

// ------------------------------
// Restore Saved Content via AJAX
// ------------------------------
add_action( 'wp_ajax_restore_quick_save', 'autosave_net_quicksave_restore_saved_version' );
function autosave_net_quicksave_restore_saved_version() {

	$postid = $_POST['restorepostid'];
	if ( ( '' == $postid ) || !is_numeric( $postid ) ) {
		exit;
	}

	if ( !current_user_can( 'edit_post', $postid ) ) {
		exit;
	}
	$userid = $_POST['restoreuserid'];
	$currentuserid = get_current_user_id();
	if ( $currentuserid != $userid ) {
		exit;
	}

	$quicksavecontent = stripslashes( $_POST['quicksavedcontent'] );
	$savedtype = $_POST['savedtype'];
	if ( 'latest' == $savedtype ) {
		$restoreddisplay = __( 'Latest QuickSave Content', 'autosave-net' );
	} elseif ( 'pageload' == $savedtype ) {
		$restoreddisplay = __( 'Pageload QuickSave Content', 'autosave-net' );
	} elseif ( 'backup' == $savedtype ) {
		$restoreddisplay = __( 'Content before Restore', 'autosave-net' );
	} else {
		exit;
	}

	$postcontent = stripslashes( $_POST['savedcontent'] );
	$contenttype = $_POST['contenttype'];
	if ( 'current' == $contenttype ) {
		$olddisplay = __( 'Currently Edited Post Content', 'autosave-net' );
	} elseif ( 'saved' == $contenttype ) {
		$olddisplay = __( 'Wordpress Saved Post Content', 'autosave-net' );
	} else {
		exit;
	}

	// dummy textareas
	echo '<textarea id="quicksavedcontent" name="quicksavedcontent" readonly="readonly">' . $quicksavecontent . '</textarea>';
	echo '<textarea id="savedcontent" name="savedcontent" readonly="readonly">' . $postcontent . '</textarea>';

	$timestamp = time();
	$displaytime = date( 'H:i:s', $timestamp );
	$displaydate = date( 'jS \o\f F Y', $timestamp );
	$timedisplay = '<b>' . esc_js( $displaytime ) . '</b> ' . __( 'on', 'autosave-net' ) . ' ' . esc_js( $displaydate );
	
	// 1.3.4: use esc_js on translated string outputs
	echo "<script>
	oldcontent = document.getElementById('savedcontent').value;
	parent.document.getElementById('contentbackupcontent').value = oldcontent;
	parent.document.getElementById('contentbackuptime').innerHTML = '" . $timedisplay . "';
	parent.document.getElementById('contentbackupwrapper').style.display = '';

	restoredcontent = document.getElementById('quicksavedcontent').value;
	parent.document.getElementById('content').value = restoredcontent;

	parent.document.getElementById('compareframewrapper').style.display = 'none';
	parent.document.getElementById('contentbackup').style.display = 'none';

	checkcontent = parent.document.getElementById('content').value;
	if (checkcontent == restoredcontent) {
		var restoredmessage = '" . $timedisplay . "';
		restoredmessage += ': <b>" . esc_js( $restoreddisplay ) . " " . esc_js( __( 'has been Restored.', 'autosave-net' ) ) . "</b><br>" . esc_js( __( 'Content before Restore is now available above.', 'autosave-net' ) ) . "';
		parent.document.getElementById('restoredmessage').innerHTML = restoredmessage;
		parent.document.getElementById('restoredwrapper').style.display = '';
	} else {
		alert('" . esc_js( $restoreddisplay ) . " : " . esc_js( __( 'Restore Failed. Use Copy and Paste.', 'autosave-net' ) ) . "');
	}
	parent.quicksave_wrapper_resize();
	</script>";

	exit;
}

// ----------------------------
// Adjust Backup Timer via AJAX
// ----------------------------
add_action( 'wp_ajax_quicksave_update_backup_time', 'autosave_net_quicksave_update_backup_time' );
function autosave_net_quicksave_update_backup_time() {

	$source = $_POST['backupsource'];
	if ( 'latest' == $source ) {
		$restoredmessage = __( 'Latest QuickSave Content has been Restored.','autosave-net' );
	} elseif ( 'pagelaod' == $source ) {
		$restoredmessage = __( 'QuickSave Pageload Content has been Restored.','autosave-net' );
	} elseif ( 'backup' == $source ) {
		$restoredmessage = __( 'Content before Restore - has now been Restored.','autosave-net' );
	} else {
		exit;
	}

	$timestamp = time();
	$displaytime = date( 'H:i:s', $timestamp );
	$displaydate = date( 'jS \o\f F Y', $timestamp );
	$timedisplay = '<b>' . $displaytime.'</b> ' . __( 'on', 'autosave-net' ). ' ' . $displaydate;
	
	// 1.3.4: use esc_js on translated strings
	echo "<script language='javascript' type='text/javascript'>
	parent.document.getElementById('contentbackuptime').innerHTML = '" . esc_js( $timedisplay ) . "';
	var restoredmessage = '" . esc_js( $restoredmessage ) . "';
	if (restoredmessage != '') {
		document.getElementById('restoredmessage').innerHTML = " . esc_js( $timedisplay ) . ": <b>'+restoredmessage+'</b>';
		document.getElementById('restoredwrapper').style.display = '';
	}
	</script>";

	exit;
}

// --------------------------
// Disable QuickSave for Post
// --------------------------
add_action( 'wp_ajax_quicksave_enable_disable', 'autosave_net_quicksave_enable_disable' );
function autosave_net_quicksave_enable_disable() {

	$postid = $_POST['quicksavedisablepostid'];
	if ( ( '' == $postid ) || !is_numeric( $postid ) ) {
		exit;
	}
	if ( !current_user_can( 'edit_post', $postid ) ) {
		exit;
	}
	$userid = $_POST['quicksavedisableuserid'];
	$currentuserid = get_current_user_id();
	if ( $currentuserid != $userid ) {
		exit;
	}

	if ( 'yes' == $_POST['quicksavedisable'] ) {
		update_post_meta( $postid, '_quicksavedisabled', 'yes' );
		$disabled = 'yes';
	} else {
		delete_post_meta( $postid, '_quicksavedisabled' );
	}

	echo "<script>";
	if ( 'yes' == $disabled ) {
		echo "var quicksavetimer = parent.document.getElementById('quicksavetimer').value;";
		echo "alert('" . esc_js( __( 'QuickSave Timer has been disabled for this post.', 'autosave-net' ) ) ."');";
	} else {
		echo "alert('" . esc_js( __('QuickSave Timer has been enabled for this post.', 'autosave-net') ) . "');";
	}
	echo "</script>";

	exit;
}


// ---------------------
// === Extra Scripts ===
// ---------------------

// -------------------
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

// -------------------------
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
