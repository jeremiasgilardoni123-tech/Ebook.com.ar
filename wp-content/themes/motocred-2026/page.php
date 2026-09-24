<?php
/**
 * Página genérica. Compatible con Elementor/Gutenberg: si la página se armó
 * con un page builder, el contenido ocupa todo el ancho.
 */
get_header();
the_post();

$builder = ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor() );

if ( $builder ) {
	the_content();
} else {
	get_template_part( 'template-parts/page-hero', null, array( 'lead' => has_excerpt() ? get_the_excerpt() : '' ) );
	echo '<section class="mc-section mc-section--tight"><div class="mc-container mc-prose">';
	the_content();
	echo '</div></section>';
}
get_template_part( 'template-parts/cta-band' );
get_footer();
