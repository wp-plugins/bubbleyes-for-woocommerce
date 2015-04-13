<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Bubbleyes
 * @subpackage Bubbleyes/public
 * @author     Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Public {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      Bubbleyes    $plugin
	 */
	public function __construct()
	{
		if( ! WC_Bubbleyes_API::has_key() ) return;

		$button = new WC_Bubbleyes_Button();
		
		WC_Bubbleyes()->loader()->add_action( 'wp_enqueue_scripts',  $this, 'enqueue_styles' );
		WC_Bubbleyes()->loader()->add_action( 'woocommerce_share',  $button, 'show' );
		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_bubbleyes_button', $button, 'get' );
		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_nopriv_bubbleyes_button', $button, 'get' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style( 'bubbleyes', plugin_dir_url( __FILE__ ) . 'css/bubbleyes.css', array(), WC_Bubbleyes()->version(), 'all' );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// wp_enqueue_script( 'bubbleyes', plugin_dir_url( __FILE__ ) . 'js/bubbleyes.js', array( 'jquery' ), $this->plugin->get_version(), false );
	}

}
