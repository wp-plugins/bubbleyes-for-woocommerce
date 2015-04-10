<?php

/**
 * Admin functionality of the plugin.
 *
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/admin
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Admin_Options
{
	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	private $plugin_options_page = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		WC_Bubbleyes()->loader()->add_action( 'admin_menu', $this, 'add_options_page' );
		WC_Bubbleyes()->loader()->add_action( 'admin_init', $this, 'register_options' );

		WC_Bubbleyes()->loader()->add_filter( 'pre_update_option_bubbleyes', $this, 'pre_options_update', 10, 2 );
		WC_Bubbleyes()->loader()->add_action( 'added_option', $this, 'options_added', 10, 2 );
		WC_Bubbleyes()->loader()->add_action( 'updated_option', $this, 'options_updated', 10, 3 );

		WC_Bubbleyes()->loader()->add_action( 'admin_post_bubbleyes_sync', $this, 'sync_all_products' );
		WC_Bubbleyes()->loader()->add_action( 'admin_notices', $this, 'admin_notices' );
		WC_Bubbleyes()->loader()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_styles' );
		WC_Bubbleyes()->loader()->add_action( 'admin_enqueue_scripts', $this, 'enqueue_scripts' );

		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_bubbleyes_batch_start', $this, 'batch_start' );
		WC_Bubbleyes()->loader()->add_action( 'wp_ajax_bubbleyes_batch_sync', $this, 'batch_sync' );
	}

	/**
	 * Register the administration menu for this
	 * plugin into the WordPress Admin menu.
	 * 
	 * @since    1.0.0
	 */
	public function add_options_page()
	{
		$identifier = WC_Bubbleyes()->identifier();

		$this->plugin_options_page  = add_submenu_page(
			'woocommerce',
			__( 'Bubbleyes', $identifier ), 
			__( 'Bubbleyes', $identifier ),
			'manage_woocommerce',
			$identifier,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Render the settings page for Bubbleyes.
	 *
	 * @since    1.0.0
	 */
	public function settings_page()
	{
		$identifier = WC_Bubbleyes()->identifier();
		$defaults   = WC_Bubbleyes()->options();
		$options    = wp_parse_args( get_option( 'bubbleyes', array() ), $defaults );

		$apikey_message  = __( 'API key providing access to the Bubbleyes API.' );

		if( empty( $options['apikey'] ) ) {
			$apikey_message = sprintf( __( 'Create a free account at %s to get a key.', $identifier ), '<a href="http://bubbleyes.com" target="_blank">Bubbleyes.com</a>' );
		}

		include_once( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/options.php' );
	}

	/**
	 * Register options for Bubbleyes.
	 *
	 * @since    1.0.0
	 */
	public function register_options()
	{
		register_setting( 'bubbleyes', 'bubbleyes', array( $this, 'validate_options' ) );
	}

	/**
	 * Validate options and register or update
	 * Webshop in Bubbleyes Platform.
	 * 
	 * @since    1.0.0
	 * @param    array  $options
	 *
	 * @return   array
	 */
	public function validate_options( $options )
	{
		if( empty( $options['apikey'] ) ) {
			add_settings_error(
				'bubbleyes_message',
				esc_attr( 'settings_updated' ),
				__( 'The API key is required.' ),
				'error'
			);
		}

		return $options;
	}

	/**
	 * Bubbleyes options was updated. Check if the
	 * API key is valid if the key has changed..
	 * 
	 * @param   array  $options
	 * @param   array  $options_old
	 */
	public function pre_options_update( $options, $options_old )
	{
		if( isset( $_POST['bubbleyes-resync'] ) ) {
			// $this->sync_all_products();
			return $options;
		}

		if( ! empty( $options_old ) && $options['apikey'] == $options_old['apikey'] ) {
			return $options;
		}

		if( ! WC_Bubbleyes_API::test_key( $options['apikey'] ) ) {
			add_settings_error(
				'bubbleyes_message',
				esc_attr( 'settings_updated' ),
				__( 'Please enter a valid API key.' ),
				'error'
			);
			$options['apikey'] = $options_old['apikey'];

			if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' ) {
				echo 'error';
				die;
			}

			return $options;
		}

		add_settings_error(
			'bubbleyes_message',
			esc_attr( 'settings_updated' ),
			__( 'Settings saved.' ),
			'updated'
		);

		return $options;
	}

	public function options_added( $option, $options )
	{
		if( $option != 'bubbleyes' ) return;

		// $this->sync_all_products();
	}

	public function options_updated( $option, $options_old, $options )
	{
		if( $option != 'bubbleyes' ) return;

		if( ! empty( $options_old ) && $options['apikey'] == $options_old['apikey'] ) {
			return;
		}

		// $this->sync_all_products();
	}

	/**
	 * @since 1.0.4
	 */
	public function batch_start()
	{
		// Create a transient to collect failed products.
		set_transient( 'bubbleyes_failed_products', array(), 3600 );

		$sync = new WC_Bubbleyes_Products_Synchronizer();
		$sync->clear_all_products();
		$this->batch_sync();
	}

	/**
	 * @since 1.0.4
	 */
	public function batch_sync()
	{
		$sync = new WC_Bubbleyes_Products_Synchronizer();

		$chunk_size = (int) $_GET['chunk_size'];
		$start_at   = (int) $_GET['start_at'];

		$query = new WP_Query( array(
			'posts_per_page' => $chunk_size,
			'offset' => $start_at,
			'post_type' => 'product',
		) );

		$posts = $query->get_posts();
		$total = $query->found_posts;

		if( empty( $posts ) ) {
			$failed = get_transient( 'bubbleyes_failed_products' );
			echo json_encode( array( 'done' => true, 'failed' => $failed ), JSON_NUMERIC_CHECK );
			delete_transient( 'bubbleyes_failed_products' );
			die;
		}

		$response = $sync->import_products( $posts, true );

		$data = array(
			'done'     => false,
			'startAt'  => $start_at + $chunk_size,
			'total'    => $query->found_posts,
			'response' => $response,
		);

		if( ! empty( $response ) ) {
			$data['error'] = true;
			$data['errorMessage'] = $response['Message'];
		}

		echo json_encode( $data, JSON_NUMERIC_CHECK );
		die;
	}


	protected function sync_all_products()
	{
		$sync = new WC_Bubbleyes_Products_Synchronizer();
		$sync->sync_all_products();
	}

	/**
	 * Show a message if the webshop is not
	 * registered yet.
	 */
	public function admin_notices()
	{
		if ( WC_Bubbleyes_API::has_key() ) return;

		$identifier = WC_Bubbleyes()->identifier();
		$plugin_data = get_plugin_data( plugin_dir_path( dirname( __FILE__ ) ) . 'woocommerce-bubbleyes.php' );
		$screen = get_current_screen();
		$url = get_admin_url( null, 'admin.php?page=' . $identifier );

		if ( $this->plugin_options_page == $screen->id ) return;

    	?><div class="update-nag">
	        <p>
	        	<?php echo sprintf( __( 'Visit %s to setup <strong>%s</strong>.', $identifier ), '<a href="' . $url . '">' . __( 'Bubbleyes Options', $identifier ) . '</a>', $plugin_data['Name'] ); ?>
	        </p>
	    </div><?php
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		$identifier = WC_Bubbleyes()->identifier();
		$version = WC_Bubbleyes()->version();
		$screen = get_current_screen();

		if ( $this->plugin_options_page == $screen->id ) {
			wp_enqueue_style( $identifier, plugin_dir_url( __FILE__ ) . 'css/bubbleyes.css', array(), $version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		$identifier = WC_Bubbleyes()->identifier();
		$version = WC_Bubbleyes()->version();
		$screen = get_current_screen();

		if ( $this->plugin_options_page == $screen->id ) {
			wp_enqueue_script( $identifier, plugin_dir_url( __FILE__ ) . 'js/bubbleyes.js', array( 'jquery', 'jquery-form' ), $version, false );
		}
	}
}
