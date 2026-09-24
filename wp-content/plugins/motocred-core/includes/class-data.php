<?php
/**
 * Fuente única de datos comerciales.
 *
 * Todo importe, plazo o condición que aparece en Home, Planes, Financiación,
 * fichas de motos y Simulador sale de aquí. Ningún template debe escribir
 * valores a mano.
 *
 *   Admin (Planes / Ajustes MotoCred) → MotoCred_Data → templates + simulador (JSON)
 */
defined( 'ABSPATH' ) || exit;

final class MotoCred_Data {

	const OPT_SETTINGS = 'motocred_settings';
	const OPT_PLAZOS   = 'motocred_plazos';

	private static $cache = array();

	/* ---------------------------------------------------------------------
	 * Ajustes generales
	 * ------------------------------------------------------------------- */

	public static function settings_defaults() {
		return array(
			'whatsapp'        => '',
			'whatsapp_saludo' => 'Hola MotoCred',
			'ga4_id'          => '',
			'load_gtag'       => 0,
			'gtm_id'          => '',
			'meta_pixel_id'   => '',
			'load_pixel'      => 0,
			'razon_social'    => '',
			'cuit'            => '',
			'facebook'        => '',
			'instagram'       => '',
			'email'           => '',
			'aviso_legal'     => 'Valores de referencia sujetos a cambios. La operación queda sujeta a la firma de la Nota de Pedido y a los Términos y Condiciones vigentes.',
			'stats'           => array(),
			'faqs'            => self::default_faqs(),
			'redirects'       => '',
			'webp_uploads'    => 1,
		);
	}

	/**
	 * FAQ iniciales redactadas SÓLO a partir de lo publicado hoy en
	 * motocred.com.ar (Home, Financiación, Suscripción y T&C).
	 * {adjudicacion} se reemplaza por la tabla de plazos cargada en Ajustes.
	 */
	public static function default_faqs() {
		return array(
			array(
				'q' => '¿Qué necesito para empezar?',
				'a' => 'Tu DNI. Con tu documento iniciás el plan y un asesor te confirma si tu caso necesita algo más.',
			),
			array(
				'q' => '¿Las cuotas aumentan?',
				'a' => 'No. Las cuotas son fijas, en pesos, y se mantienen iguales durante todo el plan.',
			),
			array(
				'q' => '¿En cuántas cuotas puedo pagar?',
				'a' => 'En {plazos} cuotas iguales y consecutivas. El plan se elige en la Nota de Pedido y no se puede cambiar después, salvo acuerdo entre las partes.',
			),
			array(
				'q' => '¿Cuándo retiro mi moto?',
				'a' => 'Depende del plan que elijas: {adjudicacion}. Tu asesor te confirma la fecha estimada de entrega.',
			),
			array(
				'q' => '¿Cómo me suscribo a un plan?',
				'a' => 'Pagás la primera cuota y los gastos. Una vez hecha la suscripción, un asesor te contacta para seguir con la entrega.',
			),
		);
	}

	public static function settings() {
		if ( ! isset( self::$cache['settings'] ) ) {
			$saved = get_option( self::OPT_SETTINGS, array() );
			self::$cache['settings'] = wp_parse_args( is_array( $saved ) ? $saved : array(), self::settings_defaults() );
		}
		return self::$cache['settings'];
	}

	public static function setting( $key ) {
		$s = self::settings();
		return $s[ $key ] ?? null;
	}

	public static function flush() {
		self::$cache = array();
	}

	/* ---------------------------------------------------------------------
	 * Plazos (cantidad de cuotas + cuota de adjudicación)
	 * Fuente: Términos y Condiciones publicados en motocred.com.ar.
	 * ------------------------------------------------------------------- */

	public static function plazos_defaults() {
		return array(
			array( 'n' => 12, 'adj' => 2 ),
			array( 'n' => 18, 'adj' => 4 ),
			array( 'n' => 24, 'adj' => 6 ),
			array( 'n' => 30, 'adj' => 7 ),
			array( 'n' => 36, 'adj' => 8 ),
		);
	}

	public static function plazos() {
		$saved = get_option( self::OPT_PLAZOS );
		$list  = is_array( $saved ) && $saved ? $saved : self::plazos_defaults();
		usort( $list, fn( $a, $b ) => $a['n'] <=> $b['n'] );
		return $list;
	}

	public static function plazo_numbers() {
		return wp_list_pluck( self::plazos(), 'n' );
	}

	/** "12, 18, 24, 30 o 36" */
	public static function plazos_text() {
		$n = self::plazo_numbers();
		if ( count( $n ) < 2 ) {
			return implode( '', $n );
		}
		$last = array_pop( $n );
		return implode( ', ', $n ) . ' o ' . $last;
	}

	/** "en la cuota 2 con el plan de 12 cuotas, en la 4 con el de 18..." */
	public static function adjudicacion_text() {
		$parts = array();
		foreach ( self::plazos() as $i => $p ) {
			$parts[] = 0 === $i
				? sprintf( 'la adjudicación es en la cuota %d con el plan de %d cuotas', $p['adj'], $p['n'] )
				: sprintf( 'en la %d con el de %d', $p['adj'], $p['n'] );
		}
		return implode( ', ', $parts );
	}

	public static function faqs() {
		$out = array();
		foreach ( (array) self::setting( 'faqs' ) as $f ) {
			if ( empty( $f['q'] ) || empty( $f['a'] ) ) {
				continue;
			}
			$out[] = array(
				'q' => $f['q'],
				'a' => strtr( $f['a'], array(
					'{plazos}'       => self::plazos_text(),
					'{adjudicacion}' => self::adjudicacion_text(),
				) ),
			);
		}
		return $out;
	}

	/** Cifras de confianza: sólo las validadas se muestran al público. */
	public static function stats() {
		$out = array();
		foreach ( (array) self::setting( 'stats' ) as $s ) {
			if ( empty( $s['value'] ) || empty( $s['label'] ) ) {
				continue;
			}
			if ( motocred_visible( ! empty( $s['validado'] ) ) ) {
				$out[] = $s;
			}
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Planes
	 * ------------------------------------------------------------------- */

	public static function plan( $post ) {
		$post = get_post( $post );
		if ( ! $post || 'mc_plan' !== $post->post_type ) {
			return null;
		}
		$key = 'plan_' . $post->ID;
		if ( isset( self::$cache[ $key ] ) ) {
			return self::$cache[ $key ];
		}

		$validado = (bool) get_post_meta( $post->ID, '_mc_validado', true );
		$raw      = (array) get_post_meta( $post->ID, '_mc_cuotas', true );
		$show     = motocred_visible( $validado );
		$cuotas   = array();
		foreach ( self::plazo_numbers() as $n ) {
			$v            = isset( $raw[ $n ] ) ? motocred_int_or_null( $raw[ $n ] ) : null;
			$cuotas[ $n ] = $show ? $v : null;
		}
		$defined = array_filter( $cuotas, fn( $v ) => null !== $v );
		$gastos  = motocred_int_or_null( get_post_meta( $post->ID, '_mc_gastos', true ) );

		$plan = array(
			'id'              => $post->ID,
			'slug'            => $post->post_name,
			'nombre'          => get_the_title( $post ),
			'cc'              => (string) get_post_meta( $post->ID, '_mc_cc', true ),
			'uso'             => (string) get_post_meta( $post->ID, '_mc_uso', true ),
			'resumen'         => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
			'cuotas'          => $cuotas,
			'desde'           => $defined ? min( $defined ) : null,
			'desde_plazo'     => $defined ? array_search( min( $defined ), $defined, true ) : null,
			'gastos'          => $show ? $gastos : null,
			'suscripcion_url' => (string) get_post_meta( $post->ID, '_mc_suscripcion_url', true ),
			'url'             => get_permalink( $post ),
			'validado'        => $validado,
			'actualizado'     => (string) get_post_meta( $post->ID, '_mc_actualizado', true ),
			'imagen'          => get_the_post_thumbnail_url( $post, 'mc-card' ) ?: '',
		);
		return self::$cache[ $key ] = $plan;
	}

	public static function planes() {
		if ( isset( self::$cache['planes'] ) ) {
			return self::$cache['planes'];
		}
		$posts = get_posts( array(
			'post_type'      => 'mc_plan',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
		) );
		$planes = array_values( array_filter( array_map( array( __CLASS__, 'plan' ), $posts ) ) );
		usort( $planes, fn( $a, $b ) => (int) $a['cc'] <=> (int) $b['cc'] );
		return self::$cache['planes'] = $planes;
	}

	/* ---------------------------------------------------------------------
	 * Motos
	 * ------------------------------------------------------------------- */

	public static function moto( $post ) {
		$post = get_post( $post );
		if ( ! $post || 'mc_moto' !== $post->post_type ) {
			return null;
		}
		$plan_id = (int) get_post_meta( $post->ID, '_mc_plan', true );
		$plan    = $plan_id ? self::plan( $plan_id ) : null;
		$marcas  = get_the_terms( $post, 'mc_marca' );
		$tipos   = get_the_terms( $post, 'mc_tipo' );

		$precio_ok = (bool) get_post_meta( $post->ID, '_mc_precio_validado', true );
		$precio    = motocred_int_or_null( get_post_meta( $post->ID, '_mc_precio_ref', true ) );

		$specs = array();
		foreach ( preg_split( '/\r\n|\n/', (string) get_post_meta( $post->ID, '_mc_specs', true ) ) as $line ) {
			if ( str_contains( $line, ':' ) ) {
				list( $k, $v ) = array_map( 'trim', explode( ':', $line, 2 ) );
				if ( $k && $v ) {
					$specs[] = array( 'k' => $k, 'v' => $v );
				}
			}
		}

		return array(
			'id'         => $post->ID,
			'slug'       => $post->post_name,
			'nombre'     => get_the_title( $post ),
			'marca'      => $marcas && ! is_wp_error( $marcas ) ? $marcas[0]->name : '',
			'marca_slug' => $marcas && ! is_wp_error( $marcas ) ? $marcas[0]->slug : '',
			'tipo'       => $tipos && ! is_wp_error( $tipos ) ? $tipos[0]->name : '',
			'tipo_slug'  => $tipos && ! is_wp_error( $tipos ) ? $tipos[0]->slug : '',
			'cc'         => (string) get_post_meta( $post->ID, '_mc_cc', true ) ?: ( $plan['cc'] ?? '' ),
			'plan'       => $plan,
			'precio'     => motocred_visible( $precio_ok ) ? $precio : null,
			'precio_ok'  => $precio_ok,
			'specs'      => $specs,
			'specs_ok'   => (bool) get_post_meta( $post->ID, '_mc_specs_validado', true ),
			'destacada'  => (bool) get_post_meta( $post->ID, '_mc_destacada', true ),
			'url'        => get_permalink( $post ),
			'imagen_id'  => get_post_thumbnail_id( $post ),
			'resumen'    => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
		);
	}

	public static function motos( array $args = array() ) {
		$q = array(
			'post_type'      => 'mc_moto',
			'post_status'    => 'publish',
			'posts_per_page' => $args['limit'] ?? 100,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
		);
		if ( ! empty( $args['destacadas'] ) ) {
			$q['meta_key']   = '_mc_destacada';
			$q['meta_value'] = '1';
		}
		if ( ! empty( $args['plan'] ) ) {
			$q['meta_query'] = array( array( 'key' => '_mc_plan', 'value' => (int) $args['plan'] ) );
		}
		if ( ! empty( $args['exclude'] ) ) {
			$q['post__not_in'] = (array) $args['exclude'];
		}
		return array_values( array_filter( array_map( array( __CLASS__, 'moto' ), get_posts( $q ) ) ) );
	}

	/* ---------------------------------------------------------------------
	 * Sucursales
	 * ------------------------------------------------------------------- */

	public static function sucursal( $post ) {
		$post = get_post( $post );
		if ( ! $post || 'mc_sucursal' !== $post->post_type ) {
			return null;
		}
		$m = fn( $k ) => (string) get_post_meta( $post->ID, '_mc_' . $k, true );

		$direccion = $m( 'direccion' );
		$localidad = $m( 'localidad' );
		$query     = trim( $direccion . ', ' . ( $localidad ?: 'Mendoza' ) . ', Argentina', ', ' );
		$lat       = $m( 'lat' );
		$lng       = $m( 'lng' );
		$dest      = ( $lat && $lng ) ? $lat . ',' . $lng : $query;

		return array(
			'id'        => $post->ID,
			'nombre'    => get_the_title( $post ),
			'direccion' => $direccion,
			'localidad' => $localidad,
			'telefono'  => motocred_phone( $m( 'telefono' ) ),
			'tel_raw'   => $m( 'telefono' ),
			'whatsapp'  => motocred_phone( $m( 'whatsapp' ) ),
			'horario'   => $m( 'horario' ),
			'estado'    => $m( 'estado' ) ?: 'operativa',
			'lat'       => $lat,
			'lng'       => $lng,
			'mapa_url'  => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $query ),
			'llegar'    => 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $dest ),
			'embed'     => 'https://www.google.com/maps?q=' . rawurlencode( $dest ) . '&output=embed',
			'validado'  => (bool) get_post_meta( $post->ID, '_mc_validado', true ),
			'url'       => get_permalink( $post ),
		);
	}

	/** @param string $estado 'operativa' | 'proximamente' | '' (todas) */
	public static function sucursales( $estado = '' ) {
		$posts = get_posts( array(
			'post_type'      => 'mc_sucursal',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
		) );
		$list = array_values( array_filter( array_map( array( __CLASS__, 'sucursal' ), $posts ) ) );
		if ( $estado ) {
			$list = array_values( array_filter( $list, fn( $s ) => $s['estado'] === $estado ) );
		}
		return $list;
	}

	/* ---------------------------------------------------------------------
	 * Entregas reales (con consentimiento)
	 * ------------------------------------------------------------------- */

	public static function entregas( $limit = 12 ) {
		$posts = get_posts( array(
			'post_type'      => 'mc_entrega',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_mc_consentimiento',
			'meta_value'     => '1',
			'no_found_rows'  => true,
		) );
		$out = array();
		foreach ( $posts as $p ) {
			if ( ! has_post_thumbnail( $p ) ) {
				continue;
			}
			$out[] = array(
				'id'       => $p->ID,
				'titulo'   => get_the_title( $p ),
				'texto'    => get_the_excerpt( $p ),
				'imagen'   => get_post_thumbnail_id( $p ),
				'sucursal' => get_post_meta( $p->ID, '_mc_sucursal', true ) ? get_the_title( (int) get_post_meta( $p->ID, '_mc_sucursal', true ) ) : '',
			);
		}
		return $out;
	}

	/* ---------------------------------------------------------------------
	 * Payload para el simulador (JS). Nunca incluye valores no visibles.
	 * ------------------------------------------------------------------- */

	public static function public_payload() {
		$planes = array_map( fn( $p ) => array(
			'id'          => $p['id'],
			'slug'        => $p['slug'],
			'nombre'      => $p['nombre'],
			'cc'          => $p['cc'],
			'uso'         => $p['uso'],
			'cuotas'      => (object) $p['cuotas'],
			'gastos'      => $p['gastos'],
			'suscripcion' => $p['suscripcion_url'],
			'url'         => $p['url'],
			'validado'    => $p['validado'],
		), self::planes() );

		$motos = array_map( fn( $m ) => array(
			'id'     => $m['id'],
			'nombre' => $m['nombre'],
			'marca'  => $m['marca'],
			'plan'   => $m['plan']['id'] ?? 0,
			'url'    => $m['url'],
		), self::motos() );

		$wa = motocred_phone( self::setting( 'whatsapp' ) );

		return array(
			'planes'  => $planes,
			'motos'   => $motos,
			'plazos'  => self::plazos(),
			'wa'      => $wa ? $wa['wa'] : '',
			'saludo'  => (string) self::setting( 'whatsapp_saludo' ),
			'preview' => motocred_can_preview_unvalidated(),
			'aviso'   => (string) self::setting( 'aviso_legal' ),
		);
	}
}
