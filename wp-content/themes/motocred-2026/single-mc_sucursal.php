<?php
/** Página de una sucursal (SEO local). */
get_header();
the_post();
$s = MotoCred_Data::sucursal( get_the_ID() );
get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'proximamente' === $s['estado'] ? 'Próximamente' : 'Sucursal',
	'title'   => 'MotoCred ' . $s['nombre'],
	'lead'    => trim( $s['direccion'] . ', ' . ( $s['localidad'] ?: 'Mendoza' ), ', ' ),
) );
?>
<section class="mc-section mc-section--tight">
	<div class="mc-container mc-branch-single">
		<?php echo motocred_sucursal_card( $s, array( 'map' => 1 ) ); // phpcs:ignore ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band', null, array( 'ctx' => array( 'intent' => 'sucursal', 'sucursal' => $s ), 'where' => 'sucursal_cta' ) );
get_footer();
