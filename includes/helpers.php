<?php

function bubbleye_option( $option ) {
	$prefix = 'bubbleyes_';
	if ( isset( $_POST[ $prefix . $option ] ) ) {
		$value = $_POST[ $prefix . $option ];
		if ( ! is_array( $value ) )
			$value = trim( $value );
		return wp_unslash( $value );
	}
	return get_option( $prefix . $option );
}

function bubbleyes_button() {
	$button = new bubbleyes_button();
	$button->show();
}