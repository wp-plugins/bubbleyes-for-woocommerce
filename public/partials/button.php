<?php

/**
 * Variables:
 * $post       Current post/product
 *
 * @link       http://bubbleyes.com
 * @since      1.0.0
 *
 * @package    WC_Bubbleyes
 * @subpackage WC_Bubbleyes/public/partials
 */
?>
<script id="bubbleyes-script">
	(function( $ ) {

		$.getJSON("<?php echo admin_url( 'admin-ajax.php' ); ?>", {
			action:'bubbleyes_button',
			sku:<?php echo $post->ID; ?>
		}, function(data) {
			$('#bubbleyes-button').html(data.Script);
		}).fail(function(response) {
			console.log(response);
		});

	})( jQuery );
</script>
<div id="bubbleyes-button"></div>