<?php

/**
 * Fired when the plugin is uninstalled.
 * 
 * @link       http://bubbleyes.com
 * @since      1.0.0
 *
 * @package    WC_Bubbleyes
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
delete_option( 'bubbleyes' );

// Delete all post meta data.
delete_metadata( 'post', null, '_bubbleyes_meta', null, true);

// Delete category meta data.
delete_metadata( 'taxonomy', null, '_bubbleyes_meta', null, true);