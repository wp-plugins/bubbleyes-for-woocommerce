<?php

/**
 * Admin functionality of the plugin.
 *
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/admin
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Admin
{
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		$batch_process = new WC_Bubbleyes_Batch_Process();
		$admin_options = new WC_Bubbleyes_Admin_Options();
		$admin_product = new WC_Bubbleyes_Admin_Product();
		$admin_metabox = new WC_Bubbleyes_Admin_Category();

		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_bubbleyes_batch_import', $batch_process, 'process' );
		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_nopriv_bubbleyes_batch_import', $batch_process, 'process' );
	}
}
