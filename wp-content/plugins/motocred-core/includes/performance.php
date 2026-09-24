<?php
/**
 * Performance: tamaños de imagen justos, WebP automático al subir y
 * limpieza de scripts innecesarios. Nada que pueda romper funcionalidades.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_image_size( 'mc-card', 640, 440, true );
	add_image_size( 'mc-square', 600, 600, true );
	add_image_size( 'mc-hero', 1400, 1000, false );
	add_theme_support( 'post-thumbnails' );
} );

/*
 * Las subidas JPEG/PNG generan sus tamaños intermedios en WebP
 * (30-50 % más livianos). El original se conserva.
 */
add_filter( 'image_editor_output_format', function ( $formats ) {
	if ( ! MotoCred_Data::setting( 'webp_uploads' ) ) {
		return $formats;
	}
	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		return $formats;
	}
	$formats['image/jpeg'] = 'image/webp';
	$formats['image/png']  = 'image/webp';
	return $formats;
} );

// Emojis: ~15 KB de JS/CSS en cada página que no aportan nada.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
