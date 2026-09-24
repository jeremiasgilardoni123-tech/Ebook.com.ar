<?php
/**
 * WP-CLI: `wp motocred seed` carga la estructura inicial con los datos
 * relevados del sitio actual (todos marcados "sin validar").
 * `--demo` agrega valores de EJEMPLO sólo para revisar el diseño en local.
 */
defined( 'ABSPATH' ) || exit;

WP_CLI::add_command( 'motocred', 'MotoCred_CLI' );

class MotoCred_CLI {

	/**
	 * Carga planes, motos, sucursales y páginas base (idempotente).
	 *
	 * ## OPTIONS
	 *
	 * [--demo]
	 * : Agrega cuotas y WhatsApp de EJEMPLO (nunca usar en producción).
	 */
	public function seed( $args, $assoc ) {
		$demo = ! empty( $assoc['demo'] );

		// Planes relevados en el sitio actual. Cuotas vacías: se cargan desde el tarifario vigente.
		$planes = array(
			'110' => array( 'Plan 110', 'Ideal para la ciudad', '' ),
			'125' => array( 'Plan 125', 'Ciudad y uso diario', '/suscribete-plan125/' ),
			'150' => array( 'Plan 150', 'Más potencia para todos los días', '' ),
			'160' => array( 'Plan 160', 'Ciudad y ruta', '' ),
			'200' => array( 'Plan 200', 'Rendimiento para ruta', '' ),
		);
		$demo_base = array( '110' => 95000, '125' => 110000, '150' => 128000, '160' => 145000, '200' => 170000 );
		$plan_ids  = array();
		$order     = 0;
		foreach ( $planes as $cc => $p ) {
			$id = $this->upsert( 'mc_plan', $p[0], array( 'menu_order' => $order++ ) );
			update_post_meta( $id, '_mc_cc', $cc );
			update_post_meta( $id, '_mc_uso', $p[1] );
			if ( $p[2] ) {
				update_post_meta( $id, '_mc_suscripcion_url', home_url( $p[2] ) );
			}
			$cuotas = array();
			foreach ( MotoCred_Data::plazo_numbers() as $n ) {
				// Valores de EJEMPLO: cuota base decreciente con el plazo. No son precios reales.
				$cuotas[ $n ] = $demo ? (int) round( $demo_base[ $cc ] * 12 / $n * ( 1 + $n / 60 ) / 1000 ) * 1000 : null;
			}
			update_post_meta( $id, '_mc_cuotas', $cuotas );
			update_post_meta( $id, '_mc_validado', '' );
			$plan_ids[ $cc ] = $id;
		}

		foreach ( array( 'Honda', 'Zanella', 'Gilera' ) as $marca ) {
			if ( ! term_exists( $marca, 'mc_marca' ) ) {
				wp_insert_term( $marca, 'mc_marca' );
			}
		}
		foreach ( array( 'Calle', 'On/Off' ) as $tipo ) {
			if ( ! term_exists( $tipo, 'mc_tipo' ) ) {
				wp_insert_term( $tipo, 'mc_tipo' );
			}
		}

		// Modelos con ficha publicada hoy en motocred.com.ar.
		$motos = array(
			array(
				'title'  => 'Honda Wave 110',
				'marca'  => 'Honda',
				'tipo'   => 'Calle',
				'plan'   => '110',
				'old'    => '/tienda-2/honda/honda-wave-110-cc',
				'specs'  => "Motor: 110 cc, monocilíndrico, 4 tiempos\nRefrigeración: por aire\nCaja: 4 velocidades semiautomática",
				'resumen' => 'La moto urbana por excelencia: liviana, económica y fácil de manejar.',
			),
			array(
				'title'  => 'Zanella ZT 150',
				'marca'  => 'Zanella',
				'tipo'   => 'On/Off',
				'plan'   => '150',
				'old'    => '/tienda-2/zanella/zanella-zt-150cc',
				'specs'  => "Motor: 150 cc, monocilíndrico, 4 tiempos\nRefrigeración: por aire\nArranque: eléctrico y a patada\nCaja: 5 velocidades\nFreno delantero: a disco",
				'resumen' => 'On/off para ciudad y calles de tierra, con arranque eléctrico y caja de 5 marchas.',
			),
		);
		foreach ( $motos as $i => $m ) {
			$id = $this->upsert( 'mc_moto', $m['title'], array( 'menu_order' => $i, 'post_excerpt' => $m['resumen'] ) );
			wp_set_object_terms( $id, $m['marca'], 'mc_marca' );
			wp_set_object_terms( $id, $m['tipo'], 'mc_tipo' );
			update_post_meta( $id, '_mc_plan', $plan_ids[ $m['plan'] ] );
			update_post_meta( $id, '_mc_cc', $m['plan'] );
			update_post_meta( $id, '_mc_specs', $m['specs'] );
			update_post_meta( $id, '_mc_specs_validado', '' );
			update_post_meta( $id, '_mc_url_anterior', $m['old'] );
			update_post_meta( $id, '_mc_destacada', '1' );
		}

		// Sucursales publicadas hoy. Estado y localidad: REQUIEREN VALIDACIÓN (ver docs/01-auditoria.md).
		$sucursales = array(
			array( 'Espejo 216', 'Espejo 216', '261 631-4304', 'operativa' ),
			array( 'Cabildo Abierto 411', 'Cabildo Abierto 411', '261 589-51544', 'operativa' ),
			array( 'Maza 2567', 'Maza 2567', '261 570-9828', 'proximamente' ),
			array( 'Ejército de los Andes y 25 de Mayo', 'Ejército de los Andes y 25 de Mayo', '261 680-0448', 'proximamente' ),
		);
		foreach ( $sucursales as $i => $s ) {
			$id = $this->upsert( 'mc_sucursal', $s[0], array( 'menu_order' => $i ) );
			update_post_meta( $id, '_mc_direccion', $s[1] );
			update_post_meta( $id, '_mc_telefono', $s[2] );
			update_post_meta( $id, '_mc_estado', $s[3] );
			update_post_meta( $id, '_mc_validado', '' );
		}

		// Páginas base (sólo si no existen: nunca se pisan páginas reales).
		$pages = array(
			'simulador'              => array( 'Simulador de financiación', '[motocred_simulador]' ),
			'financiacion'           => array( 'Financiación', '' ),
			'nosotros'               => array( 'Nosotros', '<p>[Contenido actual de /nosotros/ a revisar: ver docs/01-auditoria.md]</p>' ),
			'contacto'               => array( 'Contacto', '<p>[Formulario actual de /contacto/]</p>' ),
			'terminos-y-condiciones' => array( 'Términos y Condiciones', '<p>[Texto legal vigente de /terminos-y-condiciones/]</p>' ),
			'suscribete-plan125'     => array( 'Suscripción Plan 125', '<p>[Página de suscripción/pago existente. No se modifica.]</p><p>Primera cuota (12 cuotas): [motocred_cuota plan="125" plazo="12"] · Gastos: [motocred_cuota plan="125" campo="gastos"]</p>' ),
			'cotizador-motos-usadas' => array( 'Cotizador de motos usadas', '<p>[Formulario actual del cotizador]</p>' ),
		);
		foreach ( $pages as $slug => $p ) {
			if ( ! get_page_by_path( $slug ) ) {
				wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $p[0], 'post_content' => $p[1] ) );
			}
		}

		$settings = get_option( MotoCred_Data::OPT_SETTINGS, array() );
		$settings = is_array( $settings ) ? $settings : array();
		$settings['facebook'] = $settings['facebook'] ?? 'https://www.facebook.com/motocred.mza/';
		// Sólo actúan si la URL vieja da 404 (es decir, después de despublicar la página vieja).
		$settings['redirects'] = $settings['redirects'] ?? "/gi/ -> /motos/marca/gilera/\n/tienda-2/ -> /motos/\n/tienda/ -> /motos/";
		if ( $demo ) {
			$settings['whatsapp'] = '261 000-0000'; // EJEMPLO.
		}
		update_option( MotoCred_Data::OPT_SETTINGS, $settings );
		if ( ! get_option( MotoCred_Data::OPT_PLAZOS ) ) {
			update_option( MotoCred_Data::OPT_PLAZOS, MotoCred_Data::plazos_defaults() );
		}

		flush_rewrite_rules();
		WP_CLI::success( $demo ? 'Estructura cargada con valores de EJEMPLO (demo).' : 'Estructura cargada. Revisá MotoCred → Datos a validar.' );
	}

	private function upsert( $type, $title, array $extra = array() ) {
		$existing = get_posts( array( 'post_type' => $type, 'title' => $title, 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) );
		$data     = array_merge( array( 'post_type' => $type, 'post_title' => $title, 'post_status' => 'publish' ), $extra );
		if ( $existing ) {
			$data['ID'] = $existing[0];
			return wp_update_post( $data );
		}
		return wp_insert_post( $data );
	}
}
