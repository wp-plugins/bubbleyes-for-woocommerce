<?php

/**
 * Provide a options view for the plugin.
 *
 * @link       http://bubbleyes.com
 * @since      1.0.0
 *
 * @package    Bubbleyes
 * @subpackage Bubbleyes/admin/partials
 */
?>
<div class="wrap">

	<h2><?php _e( 'Bubbleyes Settings', 'bubbleyes' ); ?></h2>

	<?php settings_errors(); ?>

	<div id="bubbleyes-options">

		<form id="bubbleyes-import" method="post" action="options.php">
			<?php settings_fields( 'bubbleyes' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="bubbleyes_apikey"><?php _e('API key') ?></label></th>
					<td>
						<input type="text" name="bubbleyes[apikey]" id="bubbleyes_apikey" value="<?php echo esc_attr( $options['apikey'] ); ?>" class="regular-text"><br>
						<span class="description"><?php echo $apikey_message ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="bubbleyes_button_layout"><?php _e('Bubbl button layout') ?></label></th>
					<td>
						<select name="bubbleyes[button_layout]" id="bubbleyes_button_style">
							<?php foreach ( wc_bubbleyes_button_layouts() as $key => $name) : ?>
							<option value="<?php echo $key; ?>" <?php selected( $options['button_layout'], $key ); ?>><?php _e( $name, 'bubbleyes' ); ?></option>
							<?php endforeach; ?>
						</select><br>
						<span class="description"><?php _e( 'Choose the layout for your taste - extended or compact.' ); ?></span>
					</td>
				</tr>
				<!--<tr>
					<th scope="row"><label for="bubbleyes_button_style"><?php _e('Bubbl button position') ?></label></th>
					<td>
						<span class="description"><?php _e( 'Choose where you want the button to appear.' ); ?></span>
					</td>
				</tr>-->
			</table>
			<p class="submit">
				<?php submit_button( 'Save', 'primary', 'submit', false ) ?>
				<?php if( $options['apikey'] ) : ?>
					<?php submit_button( 'Re-synchronize products', 'secondary', 'resync', false ) ?>
				<?php endif; ?>
			</p>
		</form>

	</div>

</div>