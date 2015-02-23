<?php

/**
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Cron
{
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if( ! WC_Bubbleyes_API::has_key() ) return;
		
		WC_Bubbleyes()->loader()->add_action( 'bubbleyes_cron_import_all_products', $this, 'import_all_products' );
		WC_Bubbleyes()->loader()->add_action( 'set_transient_wc_products_onsale', $this, 'sync_products_on_sale', 10, 2 );
	}

	/**
	 * Synchronize all products when the Bubbleyes
	 * cron is running.
	 * 
	 * @since    1.0.0
	 * @param    object  $transient
	 */
	public function import_all_products()
	{
		$sync = new WC_Bubbleyes_Products_Synchronizer();
		$sync->import_all_products( false );
	}

	/**
	 * Synchronize products that will be on sale and
	 * remove those which are not.
	 * 
	 * @since    1.0.0
	 * @param    object  $transient
	 */
	public function sync_products_on_sale( $transient, $expiration )
	{
		//$synchronizer = new WC_Bubbleyes_Products_Synchronizer();
		//$synchronizer->sync_products( $posts );
	}
}
