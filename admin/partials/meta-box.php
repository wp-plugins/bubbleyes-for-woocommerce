<p>
	<strong>Category</strong><br>
	<select name="bubbleyes_product_category" id="bubbleyes-product-category">
		<option value="category" <?php selected( $category, 'category' ); ?>><?php _e( '— Same as category —' ); ?></option>
		<option value="default" <?php selected( $category, 'default' ); ?>><?php _e( '— Use default —' ); ?></option>
		<?php foreach ( wc_bubbleyes_categories() as $id => $name ) : ?>
		<option value="<?php echo $id; ?>" <?php selected( intval( $category ), $id ); ?>><?php _e( $name ); ?></option>
		<?php endforeach; ?>
	</select>
</p>