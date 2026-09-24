<?php
defined( 'ABSPATH' ) || exit;

/**
 * Normaliza un teléfono argentino (área + número, 10 dígitos) y devuelve
 * los formatos útiles. Si el número no tiene 10 dígitos devuelve null:
 * preferimos no mostrar un botón a publicar un enlace roto.
 *
 * Acepta "261 631-4304", "0261 15 631-4304", "+54 9 261 6314304", etc.
 */
function motocred_phone( $raw ) {
	$digits = preg_replace( '/\D+/', '', (string) $raw );
	if ( '' === $digits ) {
		return null;
	}
	// Quitar prefijo internacional y el 9 de celulares.
	if ( str_starts_with( $digits, '549' ) ) {
		$digits = substr( $digits, 3 );
	} elseif ( str_starts_with( $digits, '54' ) ) {
		$digits = substr( $digits, 2 );
	}
	$digits = ltrim( $digits, '0' );
	// Formato local con "15" después del código de área (Mendoza: 261 15 xxx-xxxx).
	if ( 12 === strlen( $digits ) && '15' === substr( $digits, 3, 2 ) ) {
		$digits = substr( $digits, 0, 3 ) . substr( $digits, 5 );
	}
	if ( 10 !== strlen( $digits ) ) {
		return null;
	}
	return array(
		'digits'  => $digits,
		'display' => substr( $digits, 0, 3 ) . ' ' . substr( $digits, 3, 3 ) . '-' . substr( $digits, 6 ),
		'tel'     => '+54' . $digits,
		'wa'      => '549' . $digits,
	);
}

/** Formato de moneda argentino sin decimales: $ 123.456 */
function motocred_money( $value ) {
	if ( null === $value || '' === $value ) {
		return '';
	}
	return '$ ' . number_format( (float) $value, 0, ',', '.' );
}

/**
 * ¿Se pueden mostrar datos no validados? Sólo a editores logueados
 * (con aviso) o en entorno de demo local.
 */
function motocred_can_preview_unvalidated() {
	return MOTOCRED_DEMO || current_user_can( 'edit_posts' );
}

/** ¿Debe mostrarse un dato comercial dado su estado de validación? */
function motocred_visible( $validado ) {
	return (bool) $validado || motocred_can_preview_unvalidated();
}

/** Etiqueta visible sólo para el equipo: marca los datos pendientes de validación. */
function motocred_badge( $validado ) {
	if ( $validado || ! motocred_can_preview_unvalidated() ) {
		return '';
	}
	return '<span class="mc-validate" title="Este dato no fue validado por MotoCred. El público no lo ve.">A validar</span>';
}

function motocred_int_or_null( $value ) {
	$value = is_string( $value ) ? preg_replace( '/[^\d]/', '', $value ) : $value;
	return ( '' === $value || null === $value ) ? null : absint( $value );
}

/** Ícono SVG inline minimalista (stroke), para no depender de fuentes de íconos. */
function motocred_icon( $name, $size = 20 ) {
	$paths = array(
		'whatsapp' => '<path d="M3.5 20.5l1.3-4.2A8.5 8.5 0 1 1 8 19.4z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.5-2-1-1 .8a4.6 4.6 0 0 1-2.8-2.8l.8-1-1-2z"/>',
		'calc'     => '<rect x="5" y="3" width="14" height="18" rx="2.5"/><path d="M8.5 7h7M8.5 11h.01M12 11h.01M15.5 11h.01M8.5 14.5h.01M12 14.5h.01M15.5 14.5v3M8.5 18h.01M12 18h.01"/>',
		'moto'     => '<circle cx="5.5" cy="16.5" r="3.5"/><circle cx="18.5" cy="16.5" r="3.5"/><path d="M5.5 16.5l4-6h5l4 6M14.5 10.5L13 6h3M9.5 10.5L8 8H5"/>',
		'plans'    => '<rect x="3" y="4" width="18" height="16" rx="2.5"/><path d="M3 9h18M8 14h3M8 17h6"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'phone'    => '<path d="M5 4h3.5l1.5 4-2 1.5a11 11 0 0 0 5.5 5.5L15 13l4 1.5V18a2 2 0 0 1-2 2A15 15 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
		'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'shield'   => '<path d="M12 3l7 3v5.5c0 4.5-3 8-7 9.5-4-1.5-7-5-7-9.5V6z"/><path d="M9 12l2 2 4-4"/>',
		'id'       => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2"/><path d="M6 16c.6-1.5 1.7-2 3-2s2.4.5 3 2M14.5 10h4M14.5 13.5h3"/>',
		'pesos'    => '<circle cx="12" cy="12" r="9"/><path d="M14.5 9.2c-.5-.8-1.4-1.2-2.5-1.2-1.4 0-2.5.8-2.5 2s1.1 1.6 2.5 2 2.5.8 2.5 2-1.1 2-2.5 2c-1.1 0-2-.4-2.5-1.2M12 6.5V8M12 16v1.5"/>',
		'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'filter'   => '<path d="M4 6h16M7 12h10M10 18h4"/>',
		'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.2-4 4.2-6 8-6s6.8 2 8 6"/>',
		'key'      => '<circle cx="8" cy="15" r="4"/><path d="M11 12l8-8M16 7l2 2M14 9l2 2"/>',
		'chevron'  => '<path d="M9 6l6 6-6 6"/>',
		'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="mc-icon mc-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/** Atributos data-* para medición (ver assets/js/motocred.js). */
function motocred_track_attrs( $event, array $params = array() ) {
	$out = ' data-mc-event="' . esc_attr( $event ) . '"';
	if ( $params ) {
		$out .= ' data-mc-params="' . esc_attr( wp_json_encode( $params ) ) . '"';
	}
	return $out;
}
