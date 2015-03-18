<?php

/**
 * Admin functionality of the plugin.
 *
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/admin
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Admin_Product
{
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if( ! WC_Bubbleyes_API::has_key() ) return;
		
		WC_Bubbleyes()->loader()->add_action( 'add_meta_boxes', $this, 'add_metabox' );
		WC_Bubbleyes()->loader()->add_action( 'save_post', $this, 'save_metabox', 10, 2 );
		WC_Bubbleyes()->loader()->add_action( 'save_post', $this, 'save_product', 1000, 2 );
		WC_Bubbleyes()->loader()->add_action( 'deleted_post', $this, 'delete_product', 10 );
	}

	public function add_metabox()
	{
		add_meta_box( 'bubbleyes_product_data',
			__( 'Bubbleyes Options' ),
			array( $this, 'show_metabox' ),
			'product',
			'side',
			'core'
		);
	}

	public function show_metabox( $post )
	{
		wp_nonce_field( 'bubbleyes_meta_box', 'bubbleyes_meta_box_nonce' );

		$meta = get_post_meta( $post->ID, '_bubbleyes_meta', true );
		$category = isset( $meta['category'] ) ? $meta['category'] : 'category';

		include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/meta-box.php';
	}

	public function save_metabox( $post_id, $post )
	{
		if ( $post->post_type != 'product' ) {
			return;
		}

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( ! isset( $_POST['bubbleyes_meta_box_nonce'] ) ) {
			return;
		}

		if( ! wp_verify_nonce( $_POST['bubbleyes_meta_box_nonce'], 'bubbleyes_meta_box' ) ) {
			return;
		}

		if( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$meta = get_post_meta( $post_id, '_bubbleyes_meta', true );
		$meta['category'] = $_POST['bubbleyes_product_category'];

		if( $meta['category'] == 'category' ) {
			unset( $meta['category'] );
		}

		update_post_meta( $post_id, '_bubbleyes_meta', $meta );
	}

	public function save_product( $post_id, $post )
	{
		if ( $post->post_type != 'product' ) {
			return;
		}

		if ( $post->post_status == 'auto-draft' ) {
			return;
		}

		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$synchronizer = new WC_Bubbleyes_Products_Synchronizer();
		$synchronizer->sync_product( $post_id );
	}

	public function delete_product( $post_id )
	{
		global $post;

		if ( $post->post_type != 'product' ) {
			return;
		}

		$body = array( 'Product' => array( 'SKU' => $post_id ) );
		$api = new WC_Bubbleyes_API();
		$api->call( WC_Bubbleyes_API::DELETE_PRODUCT, $body );
	}
}
