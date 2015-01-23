<?php

/**
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Products_Repository
{
	public function __construct()
	{
		WC_Bubbleyes()->loader()->add_filter( 'posts_join', $this, 'posts_join');
		WC_Bubbleyes()->loader()->add_filter( 'posts_where', $this, 'posts_where');
		WC_Bubbleyes()->loader()->add_filter( 'posts_orderby', $this, 'posts_orderby');
		WC_Bubbleyes()->loader()->add_filter( 'posts_clauses', $this, 'posts_clauses');
	}

	/**
	 * Get a single product.
	 * 
	 * @param   int  $post_id
	 * @return  Bubbleyes_Product
	 */
	public function get( $post_id )
	{

	}

	/**
	 * Get all products.
	 * 
	 * @return  array
	 */
	public function all()
	{

	}

	public function all_in_category( $term_id )
	{

	}

	public function posts_join( $join )
	{
		return $join;
	}

	public function posts_where( $where )
	{
		return $where;
	}

	public function posts_orderby( $orderby )
	{
		return $orderby;
	}

	public function posts_clauses( $pieces )
	{
		return $pieces;
	}
}
