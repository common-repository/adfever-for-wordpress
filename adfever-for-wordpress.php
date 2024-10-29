<?php
/*
Plugin Name: AdFever for Wordpress
Plugin URI: http://www.adfever.com
Description: Ajoute la comparaison de prix à Wordpress
Version: 1.4
Author: Inteliscent SAS
*/
/*******************************************************************************
 *  Copyright (c) 2009 Inteliscent SAS.
 *  All rights reserved. This program and the accompanying materials
 *  are made available under the terms of the GNU Public License v2.0
 *  which accompanies this distribution, and is available at
 *  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *  
 *  Contributors:
 *      Be-API - initial API and implementation
 *      Inteliscent SAS - Stabilisation, refactoring, optimization, debug
 ******************************************************************************/

/** For compatibility **/
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' ); // no trailing slash, full paths only - WP_CONTENT_URL is defined further down

if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content'); // full url - WP_CONTENT_DIR is defined further up

if ( !defined('WP_PLUGIN_DIR') )
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' ); // full path, no trailing slash

if ( !defined('WP_PLUGIN_URL') )
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' ); // full url, no trailing slash
	
/** end **/

define('ADFEVER_UUID', 1);
define('ADFEVER_FOLDER', basename(dirname(__FILE__)));
define('ADFEVER_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . ADFEVER_FOLDER );
define('ADFEVER_URL', WP_PLUGIN_URL . '/' . ADFEVER_FOLDER);
define('THIS_PLUGIN_NAME', plugin_basename(__FILE__));
define('ADFEVER_THEME_FILE', 'adfever.php' ); 

// Load translations
load_plugin_textdomain ( 'adfever', str_replace( ABSPATH, '', ADFEVER_DIR ) . '/languages', false );

require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/base.php';
require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/version.php';
require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/lib/adfever/Shopping.php';
require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/functions.php';

/** Compatibility **/
if ( !class_exists('WP_Http') ) {
	require( ADFEVER_DIR . '/lib/wp-compat/http.php' );
}
if ( !class_exists('WP_Dependencies') ) {
	require( ADFEVER_DIR . '/lib/wp-compat/class.wp-dependencies.php' );
}
if ( !class_exists('WP_Styles') ) {
	require( ADFEVER_DIR . '/lib/wp-compat/class.wp-styles.php' );
}
if ( !function_exists('wp_print_styles') ) {
	require( ADFEVER_DIR . '/lib/wp-compat/functions.wp-styles.php' );
	add_action('wp_head', 'wp_print_styles', 8);
	add_action('admin_print_scripts', 'wp_print_styles', 2);
}
/** end **/

if(is_admin()) {
	require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/admin.php';
	require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/admin_functions.php';
}
else {
	require_once  WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/common.php';
	require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/client.shortcode.php';
	$short = new AdFeverClient_Shortcode();
	
	$options = get_option('adfever');
	
	if(isset($options['comparator-active']) && $options['comparator-active']==1) {
		require_once WP_PLUGIN_DIR.'/'.ADFEVER_FOLDER.'/inc/client.comparator.php';
		$comparator = & AdfeverClient_Comparator::getInstance();
	}
}

