<?php

/**
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Batch_Process
{
	/**
	 * Products that will be processed in each batch.
	 * 
	 * @var  integer
	 */
	private $chunk_size = 25;

	public static function import( $term_id = null )
	{
		$token  = uniqid();
		$action = 'bubbleyes_batch_import';
		$data   = array( 'token' => $token, 'batch' => 1, 'progress' => 0 );
		$url    = admin_url( 'admin-ajax.php' ) . "?action=$action" . ( $term_id ? "&term_id=$term_id" : '' );

		set_transient( $action, $data, 3600 );

		if( function_exists( 'curl_init' ) ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, true );
			curl_setopt($ch, CURLOPT_TIMEOUT, 1 );
			curl_setopt($ch, CURLOPT_POST, 1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS, "token=$token" );
			curl_exec($ch);
			curl_close($ch);
		}
	}

	public function process()
	{
		$token   = $_POST['token'];
		$action  = $_GET['action'];
		$term_id = isset( $_GET['term_id'] ) ? $_GET['term_id'] : null;
		$data    = get_transient( $action );
		$sync    = new WC_Bubbleyes_Products_Synchronizer();

		if( ! $data ) die;

		if( $data == 'error' ) die;

		if( $data['token'] !== $token ) die;

		if( $products = $this->get_products_portion( $data['progress'], $term_id ) ) {
			if( $response = $sync->import_products( $products ) ) {
				$data['batch']    = $data['batch'] + 1;
				$data['progress'] = $data['progress'] + $this->chunk_size;
				set_transient( $action, $data, 3600 );
				wp_remote_post( admin_url( 'admin-ajax.php' ) . "?action=$action"  . ( $term_id ? "&term_id=$term_id" : '' ), array(
					'body' => array( 'token' => $token ),
				) );
			} else {
				set_transient( $action, 'error' );
			}
		} else {
			delete_transient( $action );
		}

		die;
	}

	private function get_products_portion( $offset, $term_id = null )
	{
		$data = array(
			'post_type' => 'product',
			'posts_per_page' => $this->chunk_size,
			'offset' => $offset,
			'order' => 'asc',
		);

		if( $term_id ) {
			$data['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term_id
				),
			);
		}

		return get_posts( $data );
	}
}
