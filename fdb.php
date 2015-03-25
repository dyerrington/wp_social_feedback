<?php
/*
Plugin Name: WP Feedback
Plugin URI: http://www.vervetype.com/wp-feedback/
Description: Instantly integrate social user feedback features into wordpress.
Version: 0.1
Author: David Yerrington
Author URI: http://www.vervetype.com
License: GPL2
*/

/*  Copyright 2012  David Yerrington  

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?><?php

// some definition we will use
define( 'FDB_PUGIN_NAME', 'WP_Social_Feedback');
define( 'FDB_PLUGIN_DIRECTORY', 'wp_social_feedback');
define( 'FDB_PLUGIN_URL', plugins_url().'/'.FDB_PLUGIN_DIRECTORY);
define( 'FDB_CURRENT_VERSION', '0.1' );
define( 'FDB_CURRENT_BUILD', '1' );
define( 'FDB_LOGPATH', str_replace('\\', '/', WP_CONTENT_DIR).'/fdb-logs/');
define( 'FDB_DEBUG', false);		# never use debug mode on productive systems

// i18n plugin domain for language files
define( 'EMU2_I18N_DOMAIN', 'fdb' );

require_once(dirname(__FILE__).'/lib/wp_feedback.class.php');

// initialize and set class
$options        =   array('blog_url'    =>  get_bloginfo('wpurl'));
$wp_feedback    =   new wp_feedback($options);

// register installation / uninstall / etc
register_activation_hook(__FILE__, array($wp_feedback, 'activate_plugin'));
register_deactivation_hook(__FILE__, array($wp_feedback, 'deactivate_plugin'));
register_uninstall_hook(__FILE__, array($wp_feedback, 'uninstall_plugin'));

// admin methods
add_action('admin_menu', array($wp_feedback, 'admin_create_menu'));
add_action('admin_init', array($wp_feedback, 'fdb_register_settings'));
add_action('admin_enqueue_scripts', array($wp_feedback, 'admin_enqueue_scripts'));
add_action('admin_head', array($wp_feedback, 'admin_icons'));

// scripts / styles
add_action('wp_enqueue_scripts', array($wp_feedback, 'fdb_register_scripts'));

// AJAX handling
add_action('wp_ajax_insert_request', array($wp_feedback, 'fdb_insert_request'));
add_action('wp_ajax_nopriv_insert_request', array($wp_feedback, 'fdb_insert_request'));

add_action('wp_ajax_insert_vote', array($wp_feedback, 'fdb_insert_vote'));
add_action('wp_ajax_nopriv_insert_vote', array($wp_feedback, 'fdb_insert_vote'));

// template for feedback container / template
add_action('wp_footer', array($wp_feedback, 'fdb_display_frontend'));

// template for displaying posts / requests
add_shortcode( 'display_feedback', array($wp_feedback, 'fdb_display_posts'));

// the voting interface on details pages
add_filter('the_content', array($wp_feedback, 'insert_voting_frontend'));

// template for display front-end voting interface


// check if debug is activated
function fdb_debug() {
	# only run debug on localhost
	if ($_SERVER["HTTP_HOST"]=="localhost" && defined('FDB_DEBUG') && FDB_DEBUG==true) return true;
}
?>
