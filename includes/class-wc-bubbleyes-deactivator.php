<?php

/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Deactivator
{
	/**
	 * Remove cron job.
	 * 
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		wp_clear_scheduled_hook( 'bubbleyes_cron_import_all_products' );
	}
}
