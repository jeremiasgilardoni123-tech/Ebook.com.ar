<?php
/**
 * SEO: títulos y descripciones para contenidos MotoCred, OpenGraph,
 * datos estructurados (MotorcycleDealer, Product, FAQPage, BreadcrumbList),
 * breadcrumbs y redirecciones 301 desde URLs antiguas.
 *
 * Si hay un plugin SEO activo (Yoast, Rank Math, AIOSEO, SEOPress, TSF),
 * no se duplican title/description/OG/Organization: sólo se agregan los
 * schemas propios de MotoCred (Product, FAQ, sucursales, breadcrumbs).
 */
defined( 'ABSPATH' ) || exit;

function motocred_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/* -------------------------------------------------------------------------
 * Títulos y descripciones
 * ----------------------------------------------------------------------- */

function motocred_seo_meta() {
	$site = get_bloginfo( 'name' ) ?: 'MotoCred';
	if ( is_front_page() ) {
		return array(
			'title' => 'Motos 0KM en cuotas fijas en Mendoza | ' . $site,
			'desc'  => sprintf( 'Motos 0KM financiadas en Mendoza: planes de %s cuotas fijas en pesos. Simulá tu cuota online y consultá por WhatsApp.', MotoCred_Data::plazos_text() ),
		);
	}
	if ( is_post_type_archive( 'mc_moto' ) ) {
		return array(
			'title' => 'Motos 0KM en cuotas en Mendoza: modelos y financiación | ' . $site,
			'desc'  => 'Catálogo de motos 0KM con financiación en cuotas fijas en pesos. Filtrá por cilindrada y marca, y simulá tu cuota.',
		);
	}
	if ( is_post_type_archive( 'mc_plan' ) ) {
		return array(
			'title' => 'Planes de financiación de motos por cilindrada | ' . $site,
			'desc'  => sprintf( 'Planes de motos 0KM de %s cuotas fijas en pesos en Mendoza. Compará cilindradas, mirá la cuota y simulá tu plan.', MotoCred_Data::plazos_text() ),
		);
	}
	if ( is_post_type_archive( 'mc_sucursal' ) ) {
		return array(
			'title' => 'Sucursales en Mendoza: direcciones y teléfonos | ' . $site,
			'desc'  => 'Encontrá la sucursal MotoCred más cercana: dirección, teléfono, WhatsApp y cómo llegar.',
		);
	}
	if ( is_tax( 'mc_marca' ) ) {
		$t = get_queried_object();
		return array(
			'title' => sprintf( 'Motos %s 0KM en cuotas en Mendoza | %s', $t->name, $site ),
			'desc'  => sprintf( 'Motos %s 0KM con financiación en cuotas fijas en pesos en Mendoza. Mirá los modelos y simulá tu cuota.', $t->name ),
		);
	}
	if ( is_singular( 'mc_moto' ) ) {
		$m = MotoCred_Data::moto( get_queried_object_id() );
		return array(
			'title' => sprintf( '%s en cuotas en Mendoza | %s', $m['nombre'], $site ),
			'desc'  => $m['resumen'] ?: sprintf( '%s 0KM financiada en cuotas fijas en pesos%s. Simulá tu cuota y consultá por WhatsApp.', $m['nombre'], $m['plan'] ? ' con el ' . $m['plan']['nombre'] : '' ),
		);
	}
	if ( is_singular( 'mc_plan' ) ) {
		$p = MotoCred_Data::plan( get_queried_object_id() );
		return array(
			'title' => sprintf( '%s: motos %s cc en cuotas | %s', $p['nombre'], $p['cc'], $site ),
			'desc'  => $p['resumen'] ?: sprintf( 'Motos de %s cc en %s cuotas fijas en pesos. Mirá los modelos del %s y simulá tu cuota.', $p['cc'], MotoCred_Data::plazos_text(), $p['nombre'] ),
		);
	}
	if ( is_singular( 'mc_sucursal' ) ) {
		$s = MotoCred_Data::sucursal( get_queried_object_id() );
		return array(
			'title' => sprintf( 'Sucursal %s | %s', $s['nombre'], $site ),
			'desc'  => sprintf( 'MotoCred %s: %s. Teléfono, WhatsApp y cómo llegar.', $s['nombre'], trim( $s['direccion'] . ', ' . $s['localidad'], ', ' ) ),
		);
	}
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_mc_seo_desc', true );
		return array( 'title' => null, 'desc' => $custom ?: ( has_excerpt() ? get_the_excerpt() : '' ) );
	}
	return array( 'title' => null, 'desc' => '' );
}

add_filter( 'pre_get_document_title', function ( $title ) {
	if ( motocred_seo_plugin_active() ) {
		return $title;
	}
	$m = motocred_seo_meta();
	return $m['title'] ?: $title;
}, 20 );

add_action( 'wp_head', function () {
	if ( motocred_seo_plugin_active() ) {
		return;
	}
	$m    = motocred_seo_meta();
	$desc = wp_strip_all_tags( (string) $m['desc'] );
	$url  = is_singular() ? get_permalink() : ( is_front_page() ? home_url( '/' ) : '' );
	if ( ! $url && ( is_post_type_archive() ) ) {
		$url = get_post_type_archive_link( get_query_var( 'post_type' ) );
	} elseif ( ! $url && ( is_tax() || is_category() ) ) {
		$url = get_term_link( get_queried_object() );
	}

	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_html_excerpt( $desc, 160, '…' ) ) );
	}
	// Canonical para archivos (WP ya lo imprime en singulares).
	if ( $url && ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
	}

	$img = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$img = get_the_post_thumbnail_url( null, 'large' );
	} elseif ( get_theme_mod( 'mc_og_image' ) ) {
		$img = get_theme_mod( 'mc_og_image' );
	} elseif ( has_custom_logo() ) {
		$img = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	}

	$og = array(
		'og:locale'      => 'es_AR',
		'og:type'        => is_singular( 'mc_moto' ) ? 'product' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => wp_get_document_title(),
		'og:description' => $desc,
		'og:url'         => $url,
		'og:image'       => $img,
		'twitter:card'   => $img ? 'summary_large_image' : 'summary',
	);
	foreach ( array_filter( $og ) as $k => $v ) {
		$attr = str_starts_with( $k, 'twitter:' ) ? 'name' : 'property';
		printf( '<meta %s="%s" content="%s">' . "\n", $attr, esc_attr( $k ), esc_attr( $v ) );
	}
}, 2 );

/* -------------------------------------------------------------------------
 * Datos estructurados (JSON-LD)
 * ----------------------------------------------------------------------- */

$GLOBALS['motocred_seo_faq'] = array();

function motocred_seo_collect_faq( array $faqs ) {
	foreach ( $faqs as $f ) {
		$GLOBALS['motocred_seo_faq'][ md5( $f['q'] ) ] = $f;
	}
}

function motocred_schema_org() {
	$logo = has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '';
	$s    = MotoCred_Data::settings();
	$org  = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ) ?: 'MotoCred',
		'url'   => home_url( '/' ),
	);
	if ( $logo ) {
		$org['logo'] = $logo;
	}
	if ( $s['razon_social'] ) {
		$org['legalName'] = $s['razon_social'];
	}
	if ( $s['cuit'] ) {
		$org['taxID'] = $s['cuit'];
	}
	$same = array_values( array_filter( array( $s['facebook'], $s['instagram'] ) ) );
	if ( $same ) {
		$org['sameAs'] = $same;
	}
	return $org;
}

function motocred_schema_branch( array $s ) {
	$b = array(
		'@type'              => 'MotorcycleDealer',
		'@id'                => $s['url'] . '#dealer',
		'name'               => ( get_bloginfo( 'name' ) ?: 'MotoCred' ) . ' ' . $s['nombre'],
		'url'                => $s['url'],
		'parentOrganization' => array( '@id' => home_url( '/#organization' ) ),
		'address'            => array_filter( array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $s['direccion'],
			'addressLocality' => $s['localidad'],
			'addressRegion'   => 'Mendoza',
			'addressCountry'  => 'AR',
		) ),
	);
	if ( $s['telefono'] ) {
		$b['telephone'] = $s['telefono']['tel'];
	}
	if ( $s['lat'] && $s['lng'] ) {
		$b['geo'] = array( '@type' => 'GeoCoordinates', 'latitude' => (float) $s['lat'], 'longitude' => (float) $s['lng'] );
	}
	if ( has_custom_logo() ) {
		$b['image'] = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
	}
	return $b;
}

add_action( 'wp_footer', function () {
	$graph = array();

	if ( ! motocred_seo_plugin_active() ) {
		$graph[] = motocred_schema_org();
	}

	// Sucursales operativas y validadas → MotorcycleDealer (SEO local).
	if ( is_front_page() || is_post_type_archive( 'mc_sucursal' ) ) {
		foreach ( MotoCred_Data::sucursales( 'operativa' ) as $s ) {
			if ( $s['validado'] ) {
				$graph[] = motocred_schema_branch( $s );
			}
		}
	}
	if ( is_singular( 'mc_sucursal' ) ) {
		$s = MotoCred_Data::sucursal( get_queried_object_id() );
		if ( $s && $s['validado'] && 'operativa' === $s['estado'] ) {
			$graph[] = motocred_schema_branch( $s );
		}
	}

	if ( is_singular( 'mc_moto' ) ) {
		$m       = MotoCred_Data::moto( get_queried_object_id() );
		$product = array(
			'@type'       => 'Product',
			'@id'         => $m['url'] . '#product',
			'name'        => $m['nombre'],
			'url'         => $m['url'],
			'category'    => 'Motocicletas',
			'description' => $m['resumen'] ?: wp_strip_all_tags( get_the_excerpt() ),
		);
		if ( $m['marca'] ) {
			$product['brand'] = array( '@type' => 'Brand', 'name' => $m['marca'] );
		}
		if ( $m['imagen_id'] ) {
			$product['image'] = wp_get_attachment_image_url( $m['imagen_id'], 'large' );
		}
		// Precio sólo si fue validado (nunca con valores de prueba).
		if ( $m['precio_ok'] && $m['precio'] ) {
			$product['offers'] = array(
				'@type'         => 'Offer',
				'price'         => (string) $m['precio'],
				'priceCurrency' => 'ARS',
				'availability'  => 'https://schema.org/InStock',
				'url'           => $m['url'],
				'seller'        => array( '@id' => home_url( '/#organization' ) ),
			);
		}
		$graph[] = $product;
	}

	$crumbs = motocred_breadcrumb_items();
	if ( count( $crumbs ) > 1 ) {
		$list = array();
		foreach ( $crumbs as $i => $c ) {
			$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['url'] );
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
	}

	if ( $GLOBALS['motocred_seo_faq'] ) {
		$graph[] = array(
			'@type'      => 'FAQPage',
			'mainEntity' => array_values( array_map( fn( $f ) => array(
				'@type'          => 'Question',
				'name'           => $f['q'],
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f['a'] ),
			), $GLOBALS['motocred_seo_faq'] ) ),
		);
	}

	if ( $graph ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}
}, 20 );

/* -------------------------------------------------------------------------
 * Breadcrumbs
 * ----------------------------------------------------------------------- */

function motocred_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}
	$items = array( array( 'name' => 'Inicio', 'url' => home_url( '/' ) ) );
	$arch  = fn( $pt, $name ) => array( 'name' => $name, 'url' => get_post_type_archive_link( $pt ) );

	if ( is_singular( 'mc_moto' ) ) {
		$items[] = $arch( 'mc_moto', 'Motos' );
		$m       = MotoCred_Data::moto( get_queried_object_id() );
		if ( $m['marca_slug'] ) {
			$t = get_term_by( 'slug', $m['marca_slug'], 'mc_marca' );
			if ( $t ) {
				$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
			}
		}
		$items[] = array( 'name' => $m['nombre'], 'url' => $m['url'] );
	} elseif ( is_singular( 'mc_plan' ) ) {
		$items[] = $arch( 'mc_plan', 'Planes' );
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular( 'mc_sucursal' ) ) {
		$items[] = $arch( 'mc_sucursal', 'Sucursales' );
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_tax( array( 'mc_marca', 'mc_tipo' ) ) ) {
		$items[] = $arch( 'mc_moto', 'Motos' );
		$t       = get_queried_object();
		$items[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
	} elseif ( is_post_type_archive( 'mc_moto' ) ) {
		$items[] = $arch( 'mc_moto', 'Motos' );
	} elseif ( is_post_type_archive( 'mc_plan' ) ) {
		$items[] = $arch( 'mc_plan', 'Planes' );
	} elseif ( is_post_type_archive( 'mc_sucursal' ) ) {
		$items[] = $arch( 'mc_sucursal', 'Sucursales' );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $a ) {
			$items[] = array( 'name' => get_the_title( $a ), 'url' => get_permalink( $a ) );
		}
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular() ) {
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	}
	return $items;
}

function motocred_breadcrumbs() {
	$items = motocred_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return '';
	}
	$out  = '<nav class="mc-breadcrumbs" aria-label="Ruta de navegación"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $c ) {
		$out .= $i === $last
			? '<li aria-current="page">' . esc_html( $c['name'] ) . '</li>'
			: '<li><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['name'] ) . '</a></li>';
	}
	return $out . '</ol></nav>';
}

/* -------------------------------------------------------------------------
 * Redirecciones 301 (URLs viejas → nuevas). Sólo actúan sobre 404,
 * así nunca pisan una página existente.
 * ----------------------------------------------------------------------- */

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = untrailingslashit( strtolower( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ) ) );
	if ( '' === $path ) {
		return;
	}

	// 1) Mapa manual de Ajustes: una por línea "/vieja/ -> /nueva/".
	foreach ( preg_split( '/\r\n|\n/', (string) MotoCred_Data::setting( 'redirects' ) ) as $line ) {
		if ( ! str_contains( $line, '->' ) ) {
			continue;
		}
		list( $from, $to ) = array_map( 'trim', explode( '->', $line, 2 ) );
		if ( $from && $to && untrailingslashit( strtolower( $from ) ) === $path ) {
			wp_safe_redirect( str_starts_with( $to, 'http' ) ? $to : home_url( $to ), 301, 'MotoCred' );
			exit;
		}
	}

	// 2) URL anterior cargada en cada moto (p. ej. /tienda-2/honda/honda-wave-110-cc/).
	$found = get_posts( array(
		'post_type'      => 'mc_moto',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_mc_url_anterior', 'value' => $path ),
			array( 'key' => '_mc_url_anterior', 'value' => $path . '/' ),
		),
	) );
	if ( $found ) {
		wp_safe_redirect( get_permalink( $found[0] ), 301, 'MotoCred' );
		exit;
	}
}, 1 );
