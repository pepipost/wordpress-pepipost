<?php
/*
Plugin Name: Wp-Pepipost
Version: 2.1.1
Description: Reconfigures the wp_mail() function to use SMTP instead of mail() and creates an options page to manage the settings.
Author: Pepipost
*/

/**
 * @author Pepipost
 * @copyright Pepipost, 2015, All Rights Reserved
 * This code is released under the GPL licence version 3 or later, available here
 * http://www.gnu.org/licenses/gpl.txt
 */

/**
 * Setting options in wp-config.php
 */
 
if ( ! class_exists( 'class-logs.php' ) ) {
	require_once( 'class-logs.php' );
}
//load common functions file
require_once( 'common_functions.php' );

// Array of options and their default values
global $wpp_options;
$wpp_options = array (
	'wpp_api_key' 		 => '',
	'wpp_mail_from' 	 => '',
	'wpp_mail_from_name'     => '',
	'wpp_mailer' 		 => 'api',
);
global $is_test_mail;
$is_test_mail = false;

/**
 * Activation function. This function creates the required options and defaults.
 */
if (!function_exists('wp_pepipost_activate')) :
function wp_pepipost_activate() {
	
	global $wpp_options;
	
	// Create the required options...
	foreach ($wpp_options as $name => $val) {
		add_option($name,$val);
	}
	
}
endif;

// Whitelist the plugin options in wp
if (!function_exists('wp_pepipost_whitelist_options')) :
function wp_pepipost_whitelist_options($wpp_whitelist_options) {
	
	global $wpp_options;
	
	// Add our options to the array
	$wpp_whitelist_options['email'] = array_keys($wpp_options);
	
	return $wpp_whitelist_options;
	
}
endif;


/**
 * This function used to enqueue the required script files
 */
function wp_pepipost_scripts_method() {
	if ( is_admin() ) {
		
		wp_register_style( 'wp-pepipost', plugin_dir_url( __FILE__ ). 'css/wp-pepipost-ui.css', false, '1.11.4' );
		wp_enqueue_style( 'wp-pepipost' );
		wp_deregister_script('jquery-ui');
		wp_register_script('jquery-ui',"https://code.jquery.com/ui/1.11.4/jquery-ui.js", false, '1.11.4');
		wp_enqueue_script('jquery-ui');
   	
		wp_enqueue_script(
			'custom-script',
			plugin_dir_url( __FILE__ ) . 'js/jquery.canvasjs.min.js',
			array('jquery')
		);
		wp_enqueue_script(
			'custom-js',
			plugin_dir_url( __FILE__ ) . 'js/tabs.js',
			array('jquery')
		);
		
		wp_register_style(
			'custom-style',
			plugin_dir_url( __FILE__ ) . 'css/wp-pepipost-style.css', 
			false, '1.00'
		);
		wp_enqueue_style( 'custom-style' );
	}
}
//add_action('wp_enqueue_scripts', 'wp_pepipost_scripts_method');
add_action('admin_enqueue_scripts', 'wp_pepipost_scripts_method');


/**
 * This function outputs the plugin options page.
 */


/**
 * This function adds the required page (only 1 at the moment).
 */
if (!function_exists('wp_pepipost_menus')) :
  function wp_pepipost_menus() {
	
	if (function_exists('add_submenu_page')) {
		//add_options_page( 'Pepipost Options', 'Wp Pepipost', 'manage_options', 'wp_pepipost', 'wp_pepipost_options_page' );
		add_menu_page('Pepipost Settings', 'Pepipost Settings', 'manage_options', 'wp_pepipost', 'wp_pepipost_options_page');
			
		add_submenu_page( 'wp_pepipost', 'Pepipost Logs Report', 'Logs Report', 'manage_options', 'wp_pepipost_logs', 'wp_pepipost_logs');
		add_submenu_page( 'wp_pepipost', 'Pepipost Statistics Report', 'Statistics Report', 'manage_options', 'wp_pepipost_stats', 'wp_pepipost_stats');
	}
	
  } // End of wp_pepipost_menus() function definition
endif;


/**
 * This function sets the from email value
 */
if (!function_exists('wp_pepipost_mail_from')) :
function wp_pepipost_mail_from ($orig='') {
	
	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}

	$default_from = 'wordpress@' . $sitename;
	// End of copied code
	
	// If the from email is not the default, return it unchanged
	if ( !empty($orig) && $orig != $default_from ) {
		return $orig;
	}
	
	if (defined('WPMS_ON') && WPMS_ON) {
		if (defined('WPMS_MAIL_FROM') && WPMS_MAIL_FROM != false)
			return WPMS_MAIL_FROM;
	}
	elseif (is_email(get_option('wpp_mail_from'), false))
		return get_option('wpp_mail_from');
	
	// If in doubt, return the original value
	return $orig;
	
} // End of wp_pepipost_mail_from() function definition
endif;


/**
 * This function sets the from name value
 */
if (!function_exists('wp_pepipost_mail_from_name')) :
function wp_pepipost_mail_from_name ($orig='') {
	
	// Only filter if the from name is the default
	if (!empty($orig) && $orig == 'WordPress') {
		if ( get_option('wpp_mail_from_name') != "" && is_string(get_option('wpp_mail_from_name')) ){
			return get_option('wpp_mail_from_name');
                }
	} else if(empty($orig)) {
            if ( get_option('wpp_mail_from_name') != "" && is_string(get_option('wpp_mail_from_name')) ){
			return get_option('wpp_mail_from_name');
                }
        }

	// If in doubt, return the original value
	return $orig;
	
} // End of wp_pepipost_mail_from_name() function definition
endif;

if (!function_exists('wp_pepipost_plugin_action_links')) :
function wp_pepipost_plugin_action_links( $links, $file ) {
	if ( $file != plugin_basename( __FILE__ ))
		return $links;

	$settings_link = '<a href="admin.php?page=wp_pepipost">' . __( 'Settings', 'wp_pepipost' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}
endif;


if (!defined('WPMS_ON') || !WPMS_ON) {
	// Whitelist our options
	add_filter('whitelist_options', 'wp_pepipost_whitelist_options');
	// Add the create pages options
	add_action('admin_menu','wp_pepipost_menus');
	// Add an activation hook for this plugin
	register_activation_hook(__FILE__,'wp_pepipost_activate');
	// Adds "Settings" link to the plugin action page
	add_filter( 'plugin_action_links', 'wp_pepipost_plugin_action_links',10,2);
}

// Add filters to replace the mail from name and emailaddress
add_filter('wp_mail_from','wp_pepipost_mail_from');
add_filter('wp_mail_from_name','wp_pepipost_mail_from_name');
load_plugin_textdomain('wp_pepipost', false, dirname(plugin_basename(__FILE__)) . '/langs');


/**
 * Send Emails Using Pepipost API
 *
 * @param int $start date
 * @param int $end date
 *
 * @return mixed
 */
 require_once __DIR__ .'/pepipost-sdk/vendor/autoload.php';
 use PepipostAPIV10Lib\Controllers\Email;
if (!function_exists('wpp_send_email')) :
 function wpp_send_email( $to, $subject, $message, $headers = '', $attachments = array() ) {
	$email = new Email();
	global $is_test_mail;
	$to = rtrim(trim($to), ",");
	$to = ltrim($to, ",");
	$data = array(
		'api_key'        =>  get_option('wpp_api_key'),
		'recipients'    =>  array($to),
		'email_details' => array(
			'content'       =>  trim($message),
			'from'          =>  wp_pepipost_mail_from(), //'info@seasonsms.com',
			'subject'       =>  trim($subject),
			'fromname'      =>  wp_pepipost_mail_from_name(),    
			//'replytoid'     =>  'replyto@example.com',
		),
		
	);
	if(!empty($attachments)) {
		$data['files'] = array($attachments);
	}

	try {
		$response = $email->sendJson( $data );
		//echo "<pre>";print_r($response);die;
		if(empty($response->errorcode)){
			return true;
		}
		else {
			if(!$is_test_mail) {
				return false;
			} else {
				return array( 'is_error' => true, 'error' => $response->errormessage);
			}
		}
	}
	catch(Exception $e){
		//print 'Call failed due to unhandled exception/error('. $e->getMessage().')'."\n";
		if(!$is_test_mail) {
				return false;
		} else {
			return array( 'is_error' => true, 'error' => $e->getMessage());
		}
	}
	return false;

}
endif;

// replace standard WordPress wp_mail() if nobody else has already done it
//if (!function_exists('wp_mail') && get_option('wpp_mailer') == 'api') {
if (!function_exists('wp_mail') ) {

	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		// send mails using Pepipost API
		return wpp_send_email( $to, $subject, $message, $headers = '', $attachments = array() );
		
	}

}

