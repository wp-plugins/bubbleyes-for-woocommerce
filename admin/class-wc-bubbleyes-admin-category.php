<?php

/**
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/includes
 * @author     WC_Bubbleyes <email@bubbleyes.com>
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WC_Bubbleyes_Admin_Category
{
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if( ! WC_Bubbleyes_API::has_key() ) return;

		WC_Bubbleyes()->loader()->add_action( 'product_cat_add_form_fields', $this, 'category_add_form' );
		WC_Bubbleyes()->loader()->add_action( 'product_cat_edit_form_fields', $this, 'category_edit_form' );
		WC_Bubbleyes()->loader()->add_action( 'created_product_cat', $this, 'category_save' );
		WC_Bubbleyes()->loader()->add_action( 'edited_product_cat', $this, 'category_save' );
		WC_Bubbleyes()->loader()->add_action( 'edited_product_cat', $this, 'category_save' );
	}

	/**
	 * Add fields to new category form.
	 *
	 * @param   object  $taxonomy
	 */
	public function category_add_form( $taxonomy )
	{
		include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/category-add-form.php';
	}

	/**
	 * Add fields to category edit form.
	 *
	 * @param   object  $taxonomy
	 */
	public function category_edit_form( $taxonomy )
	{
		$meta = get_metadata( 'taxonomy', $taxonomy->term_id, '_bubbleyes_meta', true );
		$category = $meta && isset( $meta['category'] ) ? $meta['category'] : '';
		include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/category-edit-form.php';
	}

	/**
	 * Save category settings.
	 *
	 * @param   int  $term_id
	 */
	public function category_save( $term_id )
	{
		$old_meta = get_metadata( 'taxonomy', $term_id, '_bubbleyes_meta', true );
		$new_meta = array( 'category' => $_POST['bubbleyes_category'] );

		if( $old_meta && isset( $old_meta['category'] ) && $old_meta['category'] == $new_meta['category'] ) {
			return;
		}

		if( $_POST['bubbleyes_category'] == 'default' ) {
			delete_metadata( 'taxonomy', $term_id, '_bubbleyes_meta' );
		} else {
			update_metadata( 'taxonomy', $term_id, '_bubbleyes_meta', $new_meta );
		}
		
		$data = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'order' => 'asc',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term_id
				),
			),
		);

		$posts = get_posts( $data );

		$synchronizer = new WC_Bubbleyes_Products_Synchronizer();
		$synchronizer->import_products( $posts );
	}
}
