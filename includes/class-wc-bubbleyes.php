<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization,
 * dashboard-specific hooks, and public-facing site
 * hooks.
 *
 * Also maintains the unique identifier of this
 * plugin as well as the current version of the
 * plugin.
 *
 * @since      1.0.0
 * @package    Bubbleyes
 * @subpackage Bubbleyes/includes
 * @author     Bubbleyes AS <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

final class WC_Bubbleyes
{
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 * @var      Bubbleyes
	 */
	private static $instance = null;

	/**
	 * Bubbleyes options.
	 * 
	 * @since    1.0.0
	 * @var      array
	 */
	protected $options;

	/**
	 * The loader that's responsible for maintaining
	 * and registering all hooks that power the
	 * plugin.
	 *
	 * @since    1.0.0
	 * @var      WC_Bubbleyes_Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 */
	protected $identifier;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		$this->identifier = 'woocommerce-bubbleyes';
		$this->version    = '1.0.5';

		$default_options = array(
			'apikey' => null,
			'version' => '1.0.0',
			'button_layout' => 'long',
			'button_position' => 'share',
		);

		// Load options and ensure there are
		// some options available.
		$this->options = wp_parse_args( get_option( 'bubbleyes', array() ), $default_options );

		$this->load_dependencies();

		if( $this->needs_migration() ) {
			$this->load_migration();
		}
	}

	//
	// Migration
	//

	private function needs_migration()
	{
		return version_compare( $this->options['version'], $this->version, '<' );
	}

	private function load_migration()
	{
		$name = str_replace( '.', '-', $this->version ) . '.php';
		$file = plugin_dir_path( dirname( __FILE__ ) ) . 'migrations/' . $name;

		if( file_exists( $file ) ) {
			include $file;
		}

		$this->options['version'] = $this->version;
		update_option( 'bubbleyes', $this->options );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies()
	{
		/**
		 * The class responsible for orchestrating
		 * the actions and filters of the core
		 * plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-bubbleyes-loader.php';

		/**
		 * The class responsible for defining
		 * internationalization functionality of the
		 * plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-bubbleyes-i18n.php';

		/**
		 * The class responsible for cron jobs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-bubbleyes-cron.php';

		/**
		 * The class responsible for defining all
		 * actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-bubbleyes-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-bubbleyes-admin-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-bubbleyes-admin-product.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-bubbleyes-admin-category.php';

		/**
		 * The class responsible for defining all
		 * actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-bubbleyes-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-bubbleyes-button.php';

		/**
		 * Classes for Bubbleyes API intergration.
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-bubbleyes-batch-process.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-bubbleyes-products-synchronizer.php';
	}

	/**
	 * Initialize the plugin and run the loader to
	 * execute all hooks.
	 * 
	 * @since    1.0.0
	 */
	public function initialize()
	{
		static $initialized;

		if( $initialized ) return;

		if( ! empty($this->options['apikey']) ) {
			WC_Bubbleyes_API::set_key( $this->options['apikey'] );
		}

		$plugin_i18n = new WC_Bubbleyes_i18n();
		$plugin_i18n->set_domain( $this->identifier() );

		$this->loader = new WC_Bubbleyes_Loader();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

		$plugin_cron   = new WC_Bubbleyes_Cron();
		$plugin_admin  = new WC_Bubbleyes_Admin();
		$plugin_public = new WC_Bubbleyes_Public();

		$this->loader->run();

		$initialized = true;
	}

	/**
	 * The name of the plugin used to uniquely
	 * identify it within the context of WordPress
	 * and to define internationalization
	 * functionality.
	 *
	 * @since     1.0.0
	 *
	 * @return    string
	 */
	public function identifier()
	{
		return $this->identifier;
	}

	/**
	 * Reference to the class that orchestrates
	 * the hooks with the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @return    Bubbleyes_Loader
	 */
	public function loader()
	{
		return $this->loader;
	}

	/**
	 * Reference to the Bubbleyes options.
	 * 
	 * @since     1.0.0
	 * @return    array
	 */
	public function options()
	{
		return $this->options;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 *
	 * @return    string
	 */
	public function version()
	{
		return $this->version;
	}

	/**
	 * Return an instance of this class.
	 * 
	 * @since     1.0.0
	 * @return    Bubbleyes
	 */
	public static function instance()
	{
		if ( self::$instance == null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
}
