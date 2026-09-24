<?php
/**
 * Plugin Name:       MotoCred Core
 * Description:       Fuente única de datos comerciales de MotoCred (planes, cuotas, motos, sucursales, entregas), simulador, WhatsApp con contexto, SEO estructurado y medición de conversiones. Funciona con cualquier theme.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            MotoCred
 * Text Domain:       motocred
 * License:           GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'MOTOCRED_VERSION', '1.0.0' );
define( 'MOTOCRED_FILE', __FILE__ );
define( 'MOTOCRED_DIR', plugin_dir_path( __FILE__ ) );
define( 'MOTOCRED_URL', plugin_dir_url( __FILE__ ) );

/*
 * MOTOCRED_DEMO: sólo para entornos locales de diseño/QA.
 * Permite mostrar valores NO validados al público y muestra un aviso visible.
 * Nunca definirlo en producción.
 */
if ( ! defined( 'MOTOCRED_DEMO' ) ) {
	define( 'MOTOCRED_DEMO', false );
}

require_once MOTOCRED_DIR . 'includes/helpers.php';
require_once MOTOCRED_DIR . 'includes/class-data.php';
require_once MOTOCRED_DIR . 'includes/cpt.php';
require_once MOTOCRED_DIR . 'includes/whatsapp.php';
require_once MOTOCRED_DIR . 'includes/components.php';
require_once MOTOCRED_DIR . 'includes/shortcodes.php';
require_once MOTOCRED_DIR . 'includes/seo.php';
require_once MOTOCRED_DIR . 'includes/tracking.php';
require_once MOTOCRED_DIR . 'includes/performance.php';

if ( is_admin() ) {
	require_once MOTOCRED_DIR . 'includes/admin.php';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once MOTOCRED_DIR . 'includes/cli.php';
}

register_activation_hook( __FILE__, function () {
	motocred_register_content_types();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
