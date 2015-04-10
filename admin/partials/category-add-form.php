<div class="form-field">
	<label for="bubbleyes-category"><?php _e( 'Bubbleyes Category' ) ?></label>
	<select id="bubbleyes-category" name="bubbleyes_category" class="postform">
		<option value="default"><?php _e( '— Use default —' ) ?></option>
		<?php foreach ( wc_bubbleyes_categories() as $id => $name ) : ?>
		<option value="<?php echo $id; ?>"><?php _e( $name ); ?></option>
		<?php endforeach; ?>
	</select>
</div>