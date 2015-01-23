<?php

/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Activator
{
	/**
	 * Add a cron job to synchronize all products
	 * every hour.
	 * 
	 * @since    1.0.0
	 */
	public static function activate()
	{
		wp_schedule_event( time(), 'daily', 'bubbleyes_cron_import_all_products' );
	}
}
