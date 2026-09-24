<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', 'motocred_register_content_types' );

function motocred_register_content_types() {
	$common = array(
		'public'       => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-performance',
	);

	// PLANES: la unidad comercial (Plan 110, Plan 125...). Aquí se cargan las cuotas.
	register_post_type( 'mc_plan', $common + array(
		'labels'       => motocred_labels( 'Plan', 'Planes', false ),
		'menu_icon'    => 'dashicons-money-alt',
		'menu_position' => 25,
		'has_archive'  => 'planes',
		'rewrite'      => array( 'slug' => 'planes', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );

	// Taxonomías antes que el CPT: sus reglas (/motos/marca/...) deben ganar a las de la ficha.
	register_taxonomy( 'mc_marca', 'mc_moto', array(
		'labels'            => array( 'name' => 'Marcas', 'singular_name' => 'Marca', 'add_new_item' => 'Agregar marca' ),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'motos/marca', 'with_front' => false ),
	) );

	register_taxonomy( 'mc_tipo', 'mc_moto', array(
		'labels'            => array( 'name' => 'Tipos de moto', 'singular_name' => 'Tipo', 'add_new_item' => 'Agregar tipo' ),
		'public'            => true,
		'hierarchical'      => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'motos/tipo', 'with_front' => false ),
	) );

	// MOTOS: catálogo. Cada moto pertenece a un plan (su financiación sale del plan).
	register_post_type( 'mc_moto', $common + array(
		'labels'       => motocred_labels( 'Moto', 'Motos', true ),
		'menu_icon'    => 'dashicons-car',
		'menu_position' => 26,
		'has_archive'  => 'motos',
		'rewrite'      => array( 'slug' => 'motos', 'with_front' => false ),
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'mc_sucursal', $common + array(
		'labels'       => motocred_labels( 'Sucursal', 'Sucursales', true ),
		'menu_icon'    => 'dashicons-location',
		'menu_position' => 27,
		'has_archive'  => 'sucursales',
		'rewrite'      => array( 'slug' => 'sucursales', 'with_front' => false ),
		'supports'     => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	// ENTREGAS: fotos reales de clientes. Sólo se publican con consentimiento registrado.
	register_post_type( 'mc_entrega', array(
		'labels'              => motocred_labels( 'Entrega', 'Entregas', true ),
		'public'              => false,
		'show_ui'             => true,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-camera',
		'menu_position'       => 28,
		'supports'            => array( 'title', 'thumbnail', 'excerpt' ),
	) );
}

function motocred_labels( $singular, $plural, $fem ) {
	$s = strtolower( $singular );
	$p = strtolower( $plural );
	return array(
		'name'          => $plural,
		'singular_name' => $singular,
		'add_new'       => 'Agregar',
		'add_new_item'  => ( $fem ? 'Agregar nueva ' : 'Agregar nuevo ' ) . $s,
		'edit_item'     => 'Editar ' . $s,
		'new_item'      => ( $fem ? 'Nueva ' : 'Nuevo ' ) . $s,
		'view_item'     => 'Ver ' . $s,
		'search_items'  => 'Buscar ' . $p,
		'not_found'     => 'No hay ' . $p,
		'all_items'     => ( $fem ? 'Todas las ' : 'Todos los ' ) . $p,
		'menu_name'     => $plural,
	);
}

/*
 * Planes, Motos, Sucursales y Entregas son fichas de datos: se editan en la
 * pantalla clásica, con todos los campos visibles de entrada.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
	return in_array( $post_type, array( 'mc_plan', 'mc_moto', 'mc_sucursal', 'mc_entrega' ), true ) ? false : $use;
}, 10, 2 );
