<?php
/**
 * MotoCred 2026 — theme.
 * La lógica comercial vive en el plugin MotoCred Core; el theme sólo presenta.
 */
defined( 'ABSPATH' ) || exit;

define( 'MC_THEME_VERSION', '1.0.0' );

/** ¿Está activo el plugin con la fuente única de datos? */
function mc_ready() {
	return class_exists( 'MotoCred_Data' );
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	// El logo actual de MotoCred se reutiliza tal cual (Personalizar → Identidad del sitio).
	add_theme_support( 'custom-logo', array( 'height' => 96, 'width' => 320, 'flex-height' => true, 'flex-width' => true, 'unlink-homepage-logo' => false ) );
	register_nav_menus( array(
		'primary' => 'Menú principal',
		'footer'  => 'Menú del pie',
	) );
} );

add_action( 'admin_notices', function () {
	if ( ! mc_ready() && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-error"><p><strong>MotoCred 2026:</strong> activá el plugin <em>MotoCred Core</em>. Sin él no se muestran planes, motos, simulador ni sucursales.</p></div>';
	}
} );

/* -------------------------------------------------------------------------
 * Assets
 * ----------------------------------------------------------------------- */

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory_uri();
	wp_enqueue_style( 'mc-theme', $dir . '/assets/css/theme.css', mc_ready() ? array( 'motocred' ) : array(), MC_THEME_VERSION );
	wp_add_inline_style( 'mc-theme', mc_brand_css() );
	wp_enqueue_script( 'mc-theme', $dir . '/assets/js/theme.js', array(), MC_THEME_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	// Bloques de Gutenberg: sólo si la página los usa.
	if ( ! is_singular() || ! has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 20 );

add_action( 'wp_head', function () {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( get_template_directory_uri() . '/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2' )
	);
	$hero = mc_hero_image_id();
	if ( is_front_page() && $hero ) {
		$src = wp_get_attachment_image_src( $hero, 'mc-hero' );
		if ( $src ) {
			printf( '<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n", esc_url( $src[0] ) );
		}
	}
	echo '<meta name="theme-color" content="#ffffff">' . "\n";
}, 1 );

/** Color de marca (tomado del logo) → tokens CSS. */
function mc_brand_css() {
	$brand = sanitize_hex_color( get_theme_mod( 'mc_brand', '#d7182a' ) ) ?: '#d7182a';
	$hex   = ltrim( $brand, '#' );
	list( $r, $g, $b ) = array_map( fn( $c ) => hexdec( $c ) / 255, str_split( strlen( $hex ) === 3 ? preg_replace( '/(.)/', '$1$1', $hex ) : $hex, 2 ) );
	$lin = fn( $c ) => $c <= 0.03928 ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
	$lum = 0.2126 * $lin( $r ) + 0.7152 * $lin( $g ) + 0.0722 * $lin( $b );
	$ink = $lum > 0.4 ? '#0f1217' : '#ffffff';
	$font = esc_url( get_template_directory_uri() . '/assets/fonts/plus-jakarta-sans-latin-wght-normal.woff2' );
	return "@font-face{font-family:'Plus Jakarta Sans';font-style:normal;font-display:swap;font-weight:200 800;src:url('{$font}') format('woff2');}"
		. ":root{--mc-brand:{$brand};--mc-brand-ink:{$ink};}";
}

/* -------------------------------------------------------------------------
 * Personalizador: color de marca, foto del hero y textos del hero
 * ----------------------------------------------------------------------- */

add_action( 'customize_register', function ( WP_Customize_Manager $c ) {
	$c->add_section( 'mc_brand', array( 'title' => 'MotoCred · Identidad y portada', 'priority' => 25 ) );

	$c->add_setting( 'mc_brand', array( 'default' => '#d7182a', 'sanitize_callback' => 'sanitize_hex_color' ) );
	$c->add_control( new WP_Customize_Color_Control( $c, 'mc_brand', array(
		'label'       => 'Color principal (tomarlo del logo)',
		'description' => 'Usá el color exacto del logo actual de MotoCred. El logo no se modifica.',
		'section'     => 'mc_brand',
	) ) );

	$c->add_setting( 'mc_hero_image', array( 'sanitize_callback' => 'absint' ) );
	$c->add_control( new WP_Customize_Media_Control( $c, 'mc_hero_image', array(
		'label'       => 'Foto principal de portada',
		'description' => 'Foto profesional de una moto real (horizontal, mínimo 1600 px). Sin foto, se muestra una composición gráfica.',
		'section'     => 'mc_brand',
		'mime_type'   => 'image',
	) ) );

	foreach ( array(
		'mc_hero_eyebrow' => array( 'Etiqueta superior', 'Motos 0KM en Mendoza' ),
		'mc_hero_title'   => array( 'Título', 'Tu moto 0KM, en cuotas fijas y en pesos.' ),
		'mc_hero_lead'    => array( 'Bajada', 'Elegí tu moto, simulá la cuota en segundos y seguí la compra con un asesor por WhatsApp.' ),
	) as $id => $cfg ) {
		$c->add_setting( $id, array( 'default' => $cfg[1], 'sanitize_callback' => 'sanitize_text_field' ) );
		$c->add_control( $id, array( 'label' => $cfg[0], 'section' => 'mc_brand', 'type' => 'mc_hero_lead' === $id ? 'textarea' : 'text' ) );
	}

	$c->add_setting( 'mc_og_image', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$c->add_control( new WP_Customize_Image_Control( $c, 'mc_og_image', array(
		'label'   => 'Imagen para compartir (redes / WhatsApp)',
		'section' => 'mc_brand',
	) ) );
} );

function mc_hero_image_id() {
	return (int) get_theme_mod( 'mc_hero_image', 0 );
}

function mc_mod( $id, $default ) {
	$v = get_theme_mod( $id, $default );
	return '' === trim( (string) $v ) ? $default : $v;
}

/* -------------------------------------------------------------------------
 * Navegación
 * ----------------------------------------------------------------------- */

/** Menú por defecto si no se asignó uno en Apariencia → Menús. */
function mc_default_nav_items() {
	$items = array();
	if ( mc_ready() ) {
		$items[] = array( 'Motos', get_post_type_archive_link( 'mc_moto' ), is_post_type_archive( 'mc_moto' ) || is_singular( 'mc_moto' ) || is_tax( array( 'mc_marca', 'mc_tipo' ) ) );
		$items[] = array( 'Planes', get_post_type_archive_link( 'mc_plan' ), is_post_type_archive( 'mc_plan' ) || is_singular( 'mc_plan' ) );
		$items[] = array( 'Financiación', motocred_page_url( 'financiacion' ), is_page( 'financiacion' ) );
		$items[] = array( 'Sucursales', get_post_type_archive_link( 'mc_sucursal' ), is_post_type_archive( 'mc_sucursal' ) || is_singular( 'mc_sucursal' ) );
		$items[] = array( 'Nosotros', motocred_page_url( 'nosotros' ), is_page( 'nosotros' ) );
	}
	return $items;
}

function mc_nav( $location, $class ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array( 'theme_location' => $location, 'container' => false, 'menu_class' => $class, 'depth' => 1 ) );
		return;
	}
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( mc_default_nav_items() as $i ) {
		printf( '<li%s><a href="%s"%s>%s</a></li>', $i[2] ? ' class="current-menu-item"' : '', esc_url( $i[1] ), $i[2] ? ' aria-current="page"' : '', esc_html( $i[0] ) );
	}
	echo '</ul>';
}

function mc_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	// Sin logo cargado: nombre del sitio. Cargar el logo ORIGINAL en Personalizar → Identidad del sitio.
	printf( '<a class="mc-logo-text" href="%s" rel="home">%s</a>', esc_url( home_url( '/' ) ), esc_html( get_bloginfo( 'name' ) ?: 'MotoCred' ) );
}

/** Encabezado de sección reutilizable. */
function mc_section_head( $eyebrow, $title, $lead = '', $tag = 'h2', $link = null ) {
	echo '<header class="mc-section-head">';
	echo '<div>';
	if ( $eyebrow ) {
		echo '<p class="mc-eyebrow">' . esc_html( $eyebrow ) . '</p>';
	}
	printf( '<%1$s class="mc-h2">%2$s</%1$s>', tag_escape( $tag ), esc_html( $title ) );
	if ( $lead ) {
		echo '<p class="mc-lead">' . esc_html( $lead ) . '</p>';
	}
	echo '</div>';
	if ( $link ) {
		printf( '<a class="mc-link-arrow" href="%s">%s%s</a>', esc_url( $link[1] ), esc_html( $link[0] ), motocred_icon( 'arrow', 18 ) ); // phpcs:ignore
	}
	echo '</header>';
}

/* El logo sale del custom logo sin lazy-load (está arriba de todo). */
add_filter( 'get_custom_logo_image_attributes', function ( $attr ) {
	$attr['loading']       = 'eager';
	$attr['fetchpriority'] = 'high';
	$attr['class']         = 'custom-logo';
	return $attr;
} );

/* Archivo de motos: todas en una página (el filtrado es en el cliente). */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( array( 'mc_moto', 'mc_plan', 'mc_sucursal' ) ) || $q->is_tax( array( 'mc_marca', 'mc_tipo' ) ) ) {
		$q->set( 'posts_per_page', 100 );
		$q->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
	}
} );

add_filter( 'body_class', function ( $c ) {
	$c[] = 'mc';
	return $c;
} );

/* Todo el contenido del theme está en español argentino: el atributo lang no depende del paquete de idioma instalado. */
add_filter( 'language_attributes', fn() => 'lang="es-AR"' );
