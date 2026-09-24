<?php
/**
 * Medición de conversiones.
 *
 * Todos los eventos se envían a window.dataLayer (sirve para GTM y GA4)
 * y, si existen, a gtag() y fbq(). No se carga ninguna librería de terceros
 * salvo que se active explícitamente en Ajustes, para no duplicar etiquetas
 * que ya cargue otro plugin (Site Kit, PixelYourSite, GTM4WP, etc.).
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'motocred', MOTOCRED_URL . 'assets/css/motocred.css', array(), MOTOCRED_VERSION );
	wp_register_script( 'motocred', MOTOCRED_URL . 'assets/js/motocred.js', array(), MOTOCRED_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_add_inline_script( 'motocred', 'window.MOTOCRED=' . wp_json_encode( MotoCred_Data::public_payload() ) . ';', 'before' );
	// El tracking de clics y formularios se usa en todas las páginas.
	wp_enqueue_script( 'motocred' );
	wp_enqueue_style( 'motocred' );
}, 5 );

add_action( 'wp_head', function () {
	$s = MotoCred_Data::settings();

	echo "<script>window.dataLayer=window.dataLayer||[];</script>\n";

	$gtm = preg_replace( '/[^A-Z0-9\-]/', '', strtoupper( (string) $s['gtm_id'] ) );
	if ( $gtm ) {
		printf(
			"<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','%s');</script>\n",
			esc_js( $gtm )
		);
	}

	$ga4 = preg_replace( '/[^A-Z0-9\-]/', '', strtoupper( (string) $s['ga4_id'] ) );
	if ( $ga4 && ! empty( $s['load_gtag'] ) && ! $gtm ) {
		printf(
			"<script async src=\"https://www.googletagmanager.com/gtag/js?id=%1\$s\"></script>\n<script>function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','%1\$s');</script>\n",
			esc_attr( $ga4 )
		);
	}

	$pixel = preg_replace( '/\D/', '', (string) $s['meta_pixel_id'] );
	if ( $pixel && ! empty( $s['load_pixel'] ) ) {
		printf(
			"<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','%s');fbq('track','PageView');</script>\n",
			esc_js( $pixel )
		);
	}
}, 1 );

add_action( 'wp_body_open', function () {
	$gtm = preg_replace( '/[^A-Z0-9\-]/', '', strtoupper( (string) MotoCred_Data::setting( 'gtm_id' ) ) );
	if ( $gtm ) {
		printf( '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>', esc_attr( $gtm ) );
	}
	if ( MOTOCRED_DEMO ) {
		echo '<div class="mc-demo-bar" role="status">Entorno de demostración: los valores marcados «A validar» son de ejemplo y no deben publicarse.</div>';
	}
} );
