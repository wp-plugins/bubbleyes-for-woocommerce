<?php

/**
 * Bubbleyes for WooCommerce
 *
 * @link              http://bubbleyes.com
 * @since             1.0.0
 * @package           Bubbleyes
 *
 * @wordpress-plugin
 * Plugin Name:       Bubbleyes for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/bubbleyes-for-woocommerce/
 * Description:       Connect customers and web stores in a unique and dynamic way: Bubbl!
 * Version:           1.0.3
 * Author:            Terje Lindstad
 * Author URI:        http://bubbleyes.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bubbleyes
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Do not do anything if WooComerce is not activated.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}

/**
 * Config.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/config.php';

/**
 * The helper functions.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/helpers.php';

/**
 * The code that runs during plugin activation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bubbleyes-activator.php';

/**
 * The code that runs during plugin deactivation.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bubbleyes-deactivator.php';

/** This action is documented in includes/class-plugin-name-activator.php */
register_activation_hook( __FILE__, array( 'WC_Bubbleyes_Activator', 'activate' ) );

/**
 * This action is documented in
 * includes/class-plugin-name-deactivator.php
 */
register_deactivation_hook( __FILE__, array( 'WC_Bubbleyes_Deactivator', 'deactivate' ) );

/**
 * 
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bubbleyes-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bubbleyes-product.php';

/**
 * The core plugin class that is used to define
 * internationalization, dashboard-specific hooks,
 * and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-bubbleyes.php';

/**
 * Initialize the plugin if WooCommerce is active.
 * 
 * @since    1.0.0
 */
function WC_Bubbleyes() {
	return WC_Bubbleyes::instance();
}

WC_Bubbleyes()->initialize();