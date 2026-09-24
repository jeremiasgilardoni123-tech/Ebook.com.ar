<?php
/**
 * WhatsApp con contexto: cada botón abre el chat con un mensaje que le dice
 * al asesor qué estaba mirando la persona (plan, modelo, simulación, sucursal).
 */
defined( 'ABSPATH' ) || exit;

/**
 * @param array $ctx {
 *   @type string $intent   general|plan|moto|financiacion|sucursal|suscripcion
 *   @type array  $plan     Plan (MotoCred_Data::plan)
 *   @type array  $moto     Moto (MotoCred_Data::moto)
 *   @type array  $sucursal Sucursal (MotoCred_Data::sucursal) — usa su WhatsApp si tiene
 * }
 * @return string URL de wa.me o '' si no hay número configurado/válido.
 */
function motocred_wa_url( array $ctx = array() ) {
	$number = '';
	if ( ! empty( $ctx['sucursal']['whatsapp']['wa'] ) ) {
		$number = $ctx['sucursal']['whatsapp']['wa'];
	} else {
		$central = motocred_phone( MotoCred_Data::setting( 'whatsapp' ) );
		$number  = $central ? $central['wa'] : '';
	}
	if ( ! $number ) {
		return '';
	}
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( motocred_wa_message( $ctx ) );
}

function motocred_wa_message( array $ctx ) {
	$saludo = trim( (string) MotoCred_Data::setting( 'whatsapp_saludo' ) ) ?: 'Hola MotoCred';
	$intent = $ctx['intent'] ?? 'general';
	$plan   = $ctx['plan']['nombre'] ?? '';
	$moto   = $ctx['moto']['nombre'] ?? '';

	switch ( $intent ) {
		case 'moto':
			$msg = sprintf( '%s, quiero consultar por la %s', $saludo, $moto );
			if ( ! empty( $ctx['moto']['plan']['nombre'] ) ) {
				$msg .= sprintf( ' (%s)', $ctx['moto']['plan']['nombre'] );
			}
			$msg .= '. ¿Qué opciones de financiación tengo?';
			break;
		case 'plan':
			$msg = sprintf( '%s, quiero consultar por el %s. ¿Qué motos incluye y cuánto es la cuota?', $saludo, $plan );
			break;
		case 'suscripcion':
			$msg = sprintf( '%s, quiero suscribirme al %s.', $saludo, $plan );
			break;
		case 'financiacion':
			$msg = sprintf( '%s, quiero saber cómo funciona la financiación para comprar una moto 0KM.', $saludo );
			break;
		case 'sucursal':
			$msg = sprintf( '%s, quiero hacer una consulta en la sucursal %s.', $saludo, $ctx['sucursal']['nombre'] ?? '' );
			break;
		default:
			$msg = sprintf( '%s, quiero información para comprar una moto 0KM en cuotas.', $saludo );
	}

	$path = sanitize_text_field( strtok( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ), '?' ) ?: '/' );
	$msg .= "\n\n(Consulta desde la web: " . $path . ')';
	return $msg;
}

/**
 * Botón de WhatsApp listo para usar, con evento de medición.
 * Si no hay número, cae a la página de contacto (nunca un enlace roto).
 */
function motocred_wa_button( array $ctx = array(), $label = 'Consultar por WhatsApp', $class = 'mc-btn mc-btn--wa' ) {
	$url    = motocred_wa_url( $ctx );
	$params = array_filter( array(
		'intent'   => $ctx['intent'] ?? 'general',
		'plan'     => $ctx['plan']['nombre'] ?? ( $ctx['moto']['plan']['nombre'] ?? null ),
		'modelo'   => $ctx['moto']['nombre'] ?? null,
		'marca'    => $ctx['moto']['marca'] ?? null,
		'sucursal' => $ctx['sucursal']['nombre'] ?? null,
		'ubicacion' => $ctx['ubicacion'] ?? null,
	) );

	if ( ! $url ) {
		$contact = get_page_by_path( 'contacto' );
		$url     = $contact ? get_permalink( $contact ) : home_url( '/contacto/' );
		return sprintf( '<a class="%s" href="%s"%s>%s<span>%s</span></a>', esc_attr( $class ), esc_url( $url ), motocred_track_attrs( 'contact_click', $params ), motocred_icon( 'phone' ), esc_html( $label ) );
	}

	return sprintf(
		'<a class="%s" href="%s" target="_blank" rel="noopener"%s>%s<span>%s</span></a>',
		esc_attr( $class ),
		esc_url( $url ),
		motocred_track_attrs( 'whatsapp_click', $params ),
		motocred_icon( 'whatsapp' ),
		esc_html( $label )
	);
}
