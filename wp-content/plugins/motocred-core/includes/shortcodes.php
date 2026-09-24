<?php
/**
 * Shortcodes: permiten usar la fuente única de datos dentro de páginas
 * existentes (Elementor, Gutenberg, editor clásico) sin reescribir importes.
 *
 * Ejemplo en la página de suscripción del Plan 125:
 *   "Pagás hoy [motocred_cuota plan="125" plazo="12"] + gastos [motocred_cuota plan="125" campo="gastos"]"
 */
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	add_shortcode( 'motocred_simulador', fn( $a ) => motocred_simulador( shortcode_atts( array( 'mode' => 'full', 'plan' => 0, 'moto' => 0, 'ubicacion' => 'shortcode' ), $a ) ) );
	add_shortcode( 'motocred_planes', 'motocred_sc_planes' );
	add_shortcode( 'motocred_motos', 'motocred_sc_motos' );
	add_shortcode( 'motocred_sucursales', 'motocred_sc_sucursales' );
	add_shortcode( 'motocred_whatsapp', 'motocred_sc_whatsapp' );
	add_shortcode( 'motocred_como_funciona', function () {
		motocred_enqueue_front();
		return motocred_como_funciona();
	} );
	add_shortcode( 'motocred_faq', function () {
		motocred_enqueue_front();
		return motocred_faq();
	} );
	add_shortcode( 'motocred_plazos', function () {
		motocred_enqueue_front();
		return motocred_plazos_table();
	} );
	add_shortcode( 'motocred_entregas', function ( $a ) {
		motocred_enqueue_front();
		return motocred_entregas_grid( (int) ( $a['limit'] ?? 8 ) );
	} );
	add_shortcode( 'motocred_cuota', 'motocred_sc_cuota' );
	add_shortcode( 'motocred_dato', 'motocred_sc_dato' );
} );

/** Busca un plan por ID, slug o cilindrada ("125"). */
function motocred_find_plan( $ref ) {
	$ref = trim( (string) $ref );
	foreach ( MotoCred_Data::planes() as $p ) {
		if ( (string) $p['id'] === $ref || $p['slug'] === $ref || $p['cc'] === $ref ) {
			return $p;
		}
	}
	return null;
}

function motocred_sc_planes( $a ) {
	motocred_enqueue_front();
	$html = '';
	foreach ( MotoCred_Data::planes() as $p ) {
		$html .= motocred_plan_card( $p, array( 'ubicacion' => 'shortcode' ) );
	}
	return $html ? '<div class="mc-grid mc-grid--plans">' . $html . '</div>' : '';
}

function motocred_sc_motos( $a ) {
	motocred_enqueue_front();
	$a    = shortcode_atts( array( 'destacadas' => 0, 'limit' => 12, 'plan' => '' ), $a );
	$plan = $a['plan'] ? motocred_find_plan( $a['plan'] ) : null;
	$html = '';
	foreach ( MotoCred_Data::motos( array( 'destacadas' => (int) $a['destacadas'], 'limit' => (int) $a['limit'], 'plan' => $plan['id'] ?? 0 ) ) as $m ) {
		$html .= motocred_moto_card( $m, array( 'ubicacion' => 'shortcode' ) );
	}
	return $html ? '<div class="mc-grid mc-grid--motos">' . $html . '</div>' : '';
}

function motocred_sc_sucursales( $a ) {
	motocred_enqueue_front();
	$a    = shortcode_atts( array( 'estado' => 'operativa', 'mapa' => 1 ), $a );
	$html = '';
	foreach ( MotoCred_Data::sucursales( $a['estado'] ) as $s ) {
		$html .= motocred_sucursal_card( $s, array( 'map' => (int) $a['mapa'] ) );
	}
	return $html ? '<div class="mc-grid mc-grid--branches">' . $html . '</div>' : '';
}

function motocred_sc_whatsapp( $a ) {
	motocred_enqueue_front();
	$a   = shortcode_atts( array( 'intent' => 'general', 'plan' => '', 'moto' => 0, 'texto' => 'Consultar por WhatsApp' ), $a );
	$ctx = array( 'intent' => sanitize_key( $a['intent'] ), 'ubicacion' => 'shortcode' );
	if ( $a['plan'] ) {
		$ctx['plan'] = motocred_find_plan( $a['plan'] );
	}
	if ( $a['moto'] ) {
		$ctx['moto'] = MotoCred_Data::moto( (int) $a['moto'] );
	}
	return motocred_wa_button( array_filter( $ctx ), $a['texto'] );
}

/** [motocred_cuota plan="125" plazo="12"] · [motocred_cuota plan="125" campo="gastos|desde"] */
function motocred_sc_cuota( $a ) {
	$a    = shortcode_atts( array( 'plan' => '', 'plazo' => '', 'campo' => 'cuota' ), $a );
	$plan = motocred_find_plan( $a['plan'] );
	if ( ! $plan ) {
		return current_user_can( 'edit_posts' ) ? '[plan no encontrado]' : '';
	}
	$v = match ( $a['campo'] ) {
		'gastos' => $plan['gastos'],
		'desde'  => $plan['desde'],
		default  => $plan['cuotas'][ (int) $a['plazo'] ] ?? null,
	};
	return null === $v ? 'a confirmar' : esc_html( motocred_money( $v ) ) . motocred_badge( $plan['validado'] );
}

/** [motocred_dato campo="plazos|adjudicacion|whatsapp|sucursales"] */
function motocred_sc_dato( $a ) {
	$a = shortcode_atts( array( 'campo' => '' ), $a );
	switch ( $a['campo'] ) {
		case 'plazos':
			return esc_html( MotoCred_Data::plazos_text() );
		case 'adjudicacion':
			return esc_html( MotoCred_Data::adjudicacion_text() );
		case 'whatsapp':
			$p = motocred_phone( MotoCred_Data::setting( 'whatsapp' ) );
			return $p ? esc_html( $p['display'] ) : '';
		case 'sucursales':
			return (string) count( MotoCred_Data::sucursales( 'operativa' ) );
	}
	return '';
}
