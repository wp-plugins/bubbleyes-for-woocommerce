<tr class="form-field">
	<th scope="row"><label for="bubbleyes-category"><?php _e( 'Bubbleyes Category' ); ?></label></th>
	<td>
		<select name="bubbleyes_category" id="bubbleyes-category" class="postform">
			<option value="default"><?php _e( '— Use default —' ); ?></option>
			<?php foreach ( wc_bubbleyes_categories() as $id => $name ) : ?>
			<option value="<?php echo $id; ?>" <?php selected( $category, $id ); ?>><?php _e( $name ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
</tr>