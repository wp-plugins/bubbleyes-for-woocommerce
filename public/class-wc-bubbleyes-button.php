<?php

/**
 * This class defines all code necessary to run during the plugin's activation.
 * 
 * @since      1.0.0
 * @package    Bubbleyes
 * @subpackage Bubbleyes/includes
 * @author     Bubbleyes <email@bubbleyes.com>
 */
class WC_Bubbleyes_Button {

	private $api;

	public function __construct()
	{
		$this->api = new WC_Bubbleyes_API();
	}

	/**
	 * Show local button script.
	 *
	 * @since  1.0.0
	 */
	public function show()
	{
		global $post;

		if( ! isset( $post ) ) return;

		// $meta = get_post_meta( $post->ID, '_bubbleyes_meta', true );
		// if( empty( $meta ) ) {
		// 	return;
		// }

		include plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/button.php';
	}

	/**
	 * Get the button script from Bubbleyes API.
	 * 
	 * @since  1.0.0
	 */
	public function get()
	{
		$sku = $_GET[ 'sku' ];
		$options = WC_Bubbleyes()->options();

		$data = array(
			'Product'  => array( 'SKU'  => $sku ),
			'Settings' => array( 'Type' => $options['button_layout'] ),
		);

		$response = $this->api->callWithResponse( WC_Bubbleyes_API::PRODUCT_SCRIPT, $data, true, false );

		echo $response;

		die;
	}

}
