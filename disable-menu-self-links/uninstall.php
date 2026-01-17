<?php
/**
 * Uninstall script for Disable Menu Self Links.
 *
 * Fired when the plugin is uninstalled.
 *
 * @package Disable_Menu_Self_Links
 * @since   1.0.0
 */

// Exit if accessed directly or not uninstalling
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all plugin meta data from nav menu items.
 */
function dmsl_delete_menu_meta() {
	global $wpdb;
	
	// Delete all meta entries for this plugin
	$wpdb->delete(
		$wpdb->postmeta,
		array( 'meta_key' => '_dmsl_enable_self_link' ),
		array( '%s' )
	);
}

// Run cleanup
dmsl_delete_menu_meta();
