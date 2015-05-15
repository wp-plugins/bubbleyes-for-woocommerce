<?php

/**
 * Get an array with all allowed Bubbleyes Categories.
 * 
 * @return  array  An array with category names.
 */
function wc_bubbleyes_categories() {

	return array(
		'1'  => 'Fashion',
		'2'  => 'Women\'s',
		'3'  => 'Men\'s',
		'4'  => 'Kid\'s',
		'5'  => 'Shoes', 
		'6'  => 'Sporting Goods',
		'7'  => 'Travel',
		'8'  => 'Electronics',
		'9'  => 'Health & Beauty',
		'10' => 'Home & Garden',
		'11' => 'Bags & Accessories',
		'12' => 'Pets',
	);

}

/**
 * Get category name based on given ID.
 * 
 * @param   int $id  Category ID.
 * 
 * @return  string
 */
function wc_bubbleyes_category( $id ) {

	$categories = wc_bubbleyes_categories();
	return $categories[$id];
	
}


/**
 * Get an array with all allowed Bubbleyes currencies.
 * 
 * @return  array  An array with currencies.
 */
function wc_bubbleyes_currencies() {

	$currencies = get_woocommerce_currencies();
	$allowed    = array( 'USD', 'EUR', 'NOK', 'GBP', 'DKK', 'SEK' );

	return array_intersect_key( $currencies, array_flip( $allowed ) );

}

/**
 * Button layouts.
 * 
 * @return  array
 */
function wc_bubbleyes_button_layouts() {

	return array(
		'long' => __( 'Extended', 'bubbleyes' ),
		'short' => __( 'Compact', 'bubbleyes' )
	);
	
}