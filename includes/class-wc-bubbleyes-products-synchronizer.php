<?php

/**
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Products_Synchronizer
{
	private $api;

	public function __construct()
	{
		$this->api = new WC_Bubbleyes_API();
	}

	/**
	 * The method edits an existing product in the
	 * Bubbleyes Platform or creates it if it does
	 * not exist.
	 * 
	 * @since    1.0.0
	 */
	public function sync_product( $post_id )
	{
		$product = new WC_Bubbleyes_Product( $post_id );

		if( ! $product->should_sync() ) {
			return;
		}

		$body = array( 'Product' => $product->get_product_meta() );
		$response = $this->api->call( WC_Bubbleyes_API::CREATE_EDIT_PRODUCT, $body );

		if( $response ) {
			$product->update_synced_time();
			$product->save_meta();
		}

		return $response;
	}

	/**
	 * The method deletes an existing product in the
	 * Bubbleyes Platform.
	 * 
	 * @since    1.0.0
	 */
	public function delete_product( $post_id )
	{
		$product = new WC_Bubbleyes_Product( $post_id );
		$meta    = $product->get_meta();
		
		if( ! isset( $meta['last_synced'] ) ) return;

		$body = array( 'Product' => array( 'SKU' => $post_id ) );
		$response = $this->api->call( WC_Bubbleyes_API::DELETE_PRODUCT, $body );

		if( $response ) {
			$product->clear_meta();
			$product->save_meta();
		}

		return $response;
	}

	/**
	 * Clear all products in the Bubbleyes Platform.
	 */
	public function clear_all_products( $clear_meta = false )
	{
		$cleared = $this->api->call( WC_Bubbleyes_API::SYNC_PRODUCTS, array(
			'ProductsXML' => '<products />'
		));

		if( $cleared && $clear_meta ) {
			delete_metadata( 'post', null, '_bubbleyes_meta', null, true);
		}

		return $cleared;
	}

	/**
	 * Synchronize provided products. Products not
	 * existing in Bubbleyes Platform will be
	 * created.
	 * 
	 * @since   1.0.0
	 * @param   array  $posts  All posts thats going to be synchronized.
	 * @return  bool
	 */
	public function import_products( $posts )
	{
		$products = array();

		foreach ($posts as $post) {
			$products[] = new WC_Bubbleyes_Product( $post->ID );
		}

		$response = $this->api->call( WC_Bubbleyes_API::IMPORT_PRODUCTS, array(
			'ProductsXML' => $this->to_xml( $products )
		));

		if( $response ) {
			foreach ($products as $product) {
				$product->update_synced_time();
				$product->save_meta();
			}
		}

		return $response;
	}

	/**
	 * Synchronize all products in a category. Update
	 * if a product get its Bubbleyes category from
	 * the WooCommerce category.
	 * 
	 * @param   mixed  $term_id
	 */
	public function import_products_in_category( $term_id )
	{
		WC_Bubbleyes_Batch_Process::import( $term_id );
	}

	/**
	 * Synchronize all products. Created if missing
	 * in the Bubbleyes platform, updated if existing
	 * in both platforms, deteled if missing.
	 * 
	 * @since   1.0.0
	 * @param   bool   $posts  Clear existing products.
	 */
	public function sync_all_products( $clear_existing = true )
	{
		if( $clear_existing && ! $this->clear_all_products() ) return;
		WC_Bubbleyes_Batch_Process::import();
	}

	/**
	 * 
	 * 
	 * @param   array  $posts
	 * @return  string
	 */
	private function to_xml( $products )
	{
		$products_xml = new SimpleXMLElement('<products/>');

		foreach ($products as $product) {
			$product_xml = $products_xml->addChild('product');
			$product_xml->addAttribute('sku', esc_attr( $product->get_sku()) );
			$product_xml->addChild('name', esc_attr( $product->get_name()) );
			$product_xml->addChild('shopurl', esc_attr( $product->get_permalink()) );
			$product_xml->addChild('price', esc_attr( $product->get_price()) );
			$product_xml->addChild('currency', esc_attr( $product->get_currency()) );
			$product_xml->addChild('description', esc_attr( $product->get_description()) );
			$product_xml->addChild('image', esc_attr( $product->get_image()));
			$product_xml->addChild('active', $product->get_is_active() ? 'true' : 'false');

			if( $product->get_category() ) {
				$product_xml->addChild('category', esc_attr( $product->get_category() ));
			}

			if( $product->get_price_discount() ) {
				$product_xml->addChild('discountedprice', esc_attr( $product->get_price_discount() ));
			}
		}

		return $products_xml->asXML();
	}
}
