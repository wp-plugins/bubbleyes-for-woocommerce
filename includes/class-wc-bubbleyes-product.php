<?php

/**
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Product
{
	/**
	 * The post ID.
	 * 
	 * @var  int
	 */
	private $post_id;

	/**
	 * The WooCommerce product instance.
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 * All product meta. Store the old meta data
	 * to compare it with the new meta data.
	 * 
	 * @var  array
	 */
	private $meta_old = array();
	private $meta = array();

	public function __construct( $post_id )
	{
		$this->post_id  = $post_id;
		$this->product  = get_product( $post_id );
		$this->meta_old = get_post_meta( $post_id, '_bubbleyes_meta', true );

		$options = get_option( 'bubbleyes' );

		$this->meta = $this->meta_old;
		$this->meta['Product'] = array();

		$this->set_sku( $this->post_id );
		$this->set_name( $this->product->get_title() );
		$this->set_description( $this->product->post->post_excerpt );
		$this->set_permalink( $this->product->get_permalink() );
		$this->set_is_active( $this->product->post->post_status == 'publish' );
		$this->set_currency( get_woocommerce_currency() );
		
		// Get it from the category or from default if category has no Bubbleyes category.
		if( ! isset( $this->meta['category'] ) || $this->meta['category'] == 'category' ) {
			$product_cats = wp_get_post_terms( $post_id, 'product_cat' );
			if( ! empty($product_cats) ) {
				$meta = get_metadata( 'taxonomy', $product_cats[0]->term_id, '_bubbleyes_meta', true );
				if( isset( $meta['category'] ) && $meta['category'] != '' ) {
					$this->set_category( wc_bubbleyes_category( $meta['category'] ) );
				}
			}
		}

		// This product has a custom Bubbleyes category setting.
		if( isset( $this->meta['category'] ) ) {
			$category = $this->meta['category'] != 'default' ? $this->meta['category'] : $options['category'];
			$this->set_category( wc_bubbleyes_category( $category ) );
		}
		
		// Use Featured Image as thumbnail in Bubbleyes.
		$image = wp_get_attachment_image_src( $this->product->get_image_id() );
		if( ! empty( $image ) ) {
			$this->set_image( $image[0] );
		}

		// Get price.
		$price = $this->product->get_regular_price();
		$variation = null;
		if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2.0') > 0 ) {
			if( ! $price ) {
				$variations = $this->product->get_available_variations();
				if( ! empty( $variations )) {
					$variation = new WC_Product_Variation( $variations[0]['variation_id'] );
					$this->set_price( $variation->regular_price );
				}
			} else {
				$this->set_price( $price );
			}
		} else {
			if( $price != '0' ) {
				$this->set_price( $price );
			}
		}

		// Get sale price.
		if( $this->product->is_on_sale() ) {
			$sale_price = $variation ? $variation->get_sale_price() : $this->product->get_sale_price();
			$this->set_price_discount( $sale_price );
		}

		// Let other plugins modify meta data if necessary.
		$this->meta['Product'] = apply_filters( 'bubbleyes_product_meta', $this->meta['Product'], $this->product );
	}

	/**
	 * Check if this product should be synchronized
	 * or not.
	 * 
	 * @return  bool
	 */
	public function should_sync()
	{
		// Do not sync inactive products.
		if( ! $this->meta['active'] ) {
			return false;
		}

		// Do not sync if currency is not supported.
		// if( ! array_key_exists( $this->meta['Product']['Currency'], wc_bubbleyes_currencies() ) ) {
		//	 return false;
		// }

		$should_sync = false;

		// Synchronize if old meta data did not have
		// the the same as the new meta or if any
		// have changed since last sync.
		foreach ( $this->meta['Product'] as $key => $value ) {
			if( ! isset( $this->meta_old['Product'][$key] ) ) {
				$should_sync = true;
				continue;
			}
			if( $this->meta_old['Product'][$key] != $value ) {
				$should_sync = true;
			}
		}

		// Synchronize if any meta data has been
		// removed since last sync.
		foreach ( $this->meta_old['Product'] as $key => $value ) {
			if( ! isset( $this->meta['Product'][$key] ) ) {
				$should_sync = true;
			}
		}

		return $should_sync;
	}

	/**
	 * Clear the meta data. This will indicate that
	 * the product is not synchronized.
	 *
	 * @since  1.0.0
	 */
	public function clear_meta()
	{
		$this->meta['Product'] = array();
		unset( $this->meta['active'] );
		unset( $this->meta['category'] );
		unset( $this->meta['last_synced'] );
	}

	/**
	 * Create or update the meta data.
	 *
	 * @since  1.0.0
	 */
	public function save_meta()
	{
		update_post_meta( $this->post_id, '_bubbleyes_meta', $this->get_meta() );
	}

	/**
	 * @since    1.0.0
	 */
	private function format_price( $price )
	{
		$formatted = number_format( ! empty( $price ) ? $price : 0, 2, '.', '' );
		return $formatted != '0.00' ? $formatted : null;
	}

	//
	// Setters
	// -------------------------------------------------------------------------

	public function set_active()
	{
		$this->meta['active'] = true;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_sku( $sku )
	{
		$this->meta['Product']['SKU'] = (string) $sku;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_name( $name )
	{
		$this->meta['Product']['Name'] = (string) $name;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_permalink( $permalink )
	{
		$this->meta['Product']['ShopUrl'] = (string) $permalink;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_description( $description )
	{
		$stripped = wp_strip_all_tags( apply_filters( 'woocommerce_short_description', $description ) );
		$this->meta['Product']['Description'] = preg_replace(array('/\r/', '/\n/', '/&nbsp;/'), ' ', $stripped);
	}

	/**
	 * @since    1.0.0
	 */
	public function set_price( $price )
	{
		$this->meta['Product']['Price'] = $this->format_price( $price );
	}

	/**
	 * @since    1.0.0
	 */
	public function set_price_discount( $price )
	{
		if( $price == 0 || $price == '0.00' ) return;
		$this->meta['Product']['DiscountedPrice'] = $this->format_price( $price );
	}

	/**
	 * @since    1.0.0
	 */
	public function set_image( $image )
	{
		$this->meta['Product']['Image'] = (string) $image;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_is_active( $active )
	{
		$this->meta['Product']['IsActive'] = (bool) $active;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_currency( $currency )
	{
		$this->meta['Product']['Currency'] = (string) $currency;
	}

	/**
	 * @since    1.0.0
	 */
	public function set_category( $category )
	{
		$this->meta['Product']['Category'] = (string) $category;
	}

	/**
	 * @since    1.0.0
	 */
	public function update_category( $category )
	{
		$this->meta['category'] = $category;
	}

	/**
	 * @since    1.0.0
	 */
	public function update_synced_time()
	{
		$this->meta['last_synced'] = time();
	}

	//
	// Getters
	// -------------------------------------------------------------------------

	/**
	 * @since    1.0.0
	 */
	private function get_value( $value )
	{
		return isset( $this->meta['Product'][$value] ) ? $this->meta['Product'][$value] : null; 
	}

	/**
	 * @since    1.0.0
	 */
	public function get_active()
	{
		return $this->meta['active'];
	}

	/**
	 * @since    1.0.0
	 */
	public function get_meta()
	{
		return $this->meta;
	}

	/**
	 * @since    1.0.0
	 */
	public function get_product_meta()
	{
		return $this->meta['Product'];
	}

	/**
	 * @since    1.0.0
	 */
	public function get_sku()
	{
		return $this->get_value( 'SKU' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_name()
	{
		return $this->get_value( 'Name' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_permalink()
	{
		return $this->get_value( 'ShopUrl' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_description()
	{
		$description = $this->get_value( 'Description' );
		return $description ? $description : '';
	}

	/**
	 * @since    1.0.0
	 */
	public function get_price()
	{
		return $this->get_value( 'Price' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_price_discount()
	{
		return $this->get_value( 'DiscountedPrice' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_image()
	{
		return $this->get_value( 'Image' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_is_active()
	{
		return $this->get_value( 'IsActive' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_currency()
	{
		return $this->get_value( 'Currency' );
	}

	/**
	 * @since    1.0.0
	 */
	public function get_category()
	{
		return $this->get_value( 'Category' );
	}
}
