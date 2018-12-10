<?php
/*
Plugin Name: Buddypress Required Field Reminder
Description: This Plugin remind Buddypress user when logged in to fill all required fields
Author: nitin247
License: GPLv2 or later
Version: 1.0
Text Domain: buddypress-required-field-reminder
Network: true
*/

// Constant defined
defined( 'ABSPATH' ) || exit;
define( 'BUDDYPRESS_REQUIRED_FIELD_VERSION', '1.0.0' );
define( 'BUDDYPRESS_REQUIRED_FIELD_TEXTDOMAIN', 'buddypress-required-field-reminder' );
define('PLUGIN_URI', ''.plugin_dir_path(__FILE__).'');
define('PLUGIN_URL', ''.plugin_dir_url(__FILE__).'');
//Admin Menu Settings


function buddypress_required_field_launch_av() 
{
	if (is_user_logged_in()) 
	{
		$user_id 	= wp_get_current_user()->ID;
		$current_url  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$redirect_url = buddypress_required_field_redirect_av($user_id);

		if (strpos($current_url, $redirect_url) === false)
		{
			global $wpdb;

			$bp_prefix  	 = $wpdb->prefix;
			$xprofile_fields = $wpdb->get_results("SELECT count(*) AS empty_fields_count FROM {$bp_prefix}bp_xprofile_fields WHERE parent_id = 0 AND is_required = 1 AND id NOT IN (SELECT field_id FROM {$bp_prefix}bp_xprofile_data WHERE user_id = {$user_id} AND `value` IS NOT NULL AND `value` != '')");

			foreach ($xprofile_fields as $field) 
			{
				if ($field->empty_fields_count > 0)	
				{
					wp_redirect($redirect_url);
					exit;
				}
			}
		}		
	}
}

/**
 * Plugin styles
 */
function buddypress_required_field_style_av()
{	
	wp_enqueue_style('bp-required-field-css',PLUGIN_URL . 'assets/style.css');
}

/**
 * Plugin notice
 */
function buddypress_required_field_notification_av() 
{
	if (is_user_logged_in()) 
	{
		$user_id 	= wp_get_current_user()->ID;
		$current_url  = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$redirect_url = buddypress_required_field_redirect_av($user_id);

		if (strpos($current_url, $redirect_url) !== false)
		{
			global $wpdb;

			$bp_prefix  	 = $wpdb->prefix;
			$xprofile_fields = $wpdb->get_results("SELECT `name` FROM {$bp_prefix}bp_xprofile_fields WHERE parent_id = 0 AND is_required = 1 AND id NOT IN (SELECT field_id FROM {$bp_prefix}bp_xprofile_data WHERE user_id = {$user_id} AND `value` IS NOT NULL AND `value` != '')");
	
			$xprofile_fields_count = count($xprofile_fields);
			if ($xprofile_fields_count > 0)
			{
				$message = '<div class="bp-message-av">' . __('Please complete your profile to continue', 'bp-required-field-notification') . ' (' . $xprofile_fields_count . __(' fields are required', 'bp-required-field-notification') . ')</div>';
				//$message .= '<ul class="bp-fields-av">';
				//$cn=1;
				$message .= '<span class="err-field">';
				$str_ma = '';
				foreach ($xprofile_fields as $field) 
				{
					//$message .= '<li><span class="point">('.$cn.')</span>' . $field->name . '</li>';
					$str_ma .= $field->name.',&nbsp;';
					//$cn++;
				}
				$str_ma = substr($str_ma,0,-7);
				$message .= $str_ma.'</span>';

				echo '<div id="bp-required-field-av"><div  class="bp-container-av">' . $message . '</div></div>';
			}	
		}	
	}
}

function buddypress_required_field_redirect_av($user_id)
{
	return bp_loggedin_user_domain() . 'profile/edit/'; 
	
}
add_action('template_redirect'		, 'buddypress_required_field_launch_av');
add_action('wp_head'			, 'buddypress_required_field_style_av');
add_action('wp_footer'			,'buddypress_required_field_notification_av');
?>
