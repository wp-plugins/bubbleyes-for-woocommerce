<?php

/**
 *
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_API
{
	/**
	 * Available API methods.
	 */
	const CREATE_WEBSHOP      = 'createWebshop';
	const EDIT_WEBSHOP        = 'editWebshop';
	const DELETE_WEBSHOP      = 'deleteWebshop';
	const CREATE_EDIT_PRODUCT = 'createOrEditProduct';
	const CREATE_PRODUCT      = 'createProduct';
	const EDIT_PRODUCT        = 'editProduct';
	const DELETE_PRODUCT      = 'deleteProduct';
	const IMPORT_PRODUCTS     = 'importProducts';
	const SYNC_PRODUCTS       = 'synchronizeProducts';
	const PRODUCT_SCRIPT      = 'getProductScript';

	/**
	 * API key to authorize requests.
	 * 
	 * @var  string
	 */
	private static $key = null;

	/**
	 * Base URL for all API methods.
	 * 
	 * @var  string
	 */
	private $url = 'http://api.bubbleyes.com/client/';

	/**
	 * Set the API key.
	 * 
	 * @param  string  $key
	 */
	public static function set_key( $key )
	{
		self::$key = $key;
	}

	/**
	 * Get the current API key.
	 * 
	 * @return  string
	 */
	public static function get_key()
	{
		return self::$key;
	}

	/**
	 * Check if API key exists.
	 * 
	 * @return  bool
	 */
	public static function has_key()
	{
		return ! empty( self::$key );
	}

	/**
	 * Check if API key is valid.
	 * 
	 * @return  bool
	 */
	public static function test_key( $key )
	{
		$api = new self;
		$old_key = self::get_key();

		self::set_key( $key );

		$is_valid = $api->call( self::IMPORT_PRODUCTS, array(
			'ProductsXML' => '<products />'
		) );

		self::set_key( $is_valid ? $key : $old_key );

		return $is_valid;
	}

	/**
	 * Call an API method and return true if success.
	 * 
	 * @param   string   $method
	 * @param   array    $body
	 * @param   boolean  $auth
	 * 
	 * @return  mixed
	 */
	public function call( $method, $body = array(), $auth = true )
	{
		$response = $this->callWithResponse( $method, $body, $auth, true );
		
		return empty( $response ) ? true : false;
	}

	/**
	 * Call an API method and return its response.
	 * 
	 * @param   string   $method
	 * @param   array    $body
	 * @param   boolean  $auth
	 * 
	 * @return  mixed
	 */
	public function callWithResponse( $method, $body = array(), $auth = true, $decode = true )
	{
		$body = json_encode( $body );

		$headers = array();
		$headers['Content-Type'] = 'application/json';

		if( $auth && WC_Bubbleyes_API::has_key() ) {
			$headers['Authorization'] = 'Basic ' . WC_Bubbleyes_API::get_key();
		}

		$response = wp_remote_post( $this->url . $method, array(
			'body'    => $body,
			'headers' => $headers,
		));

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if( $decode ) {
			return json_decode($response['body'], true);
		}

		return $response['body'];
	}
}