<?php
/** Planes por cilindrada + comparativa de cuotas. */
get_header();

$planes = MotoCred_Data::planes();
get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Planes',
	'title'   => 'Planes de financiación por cilindrada',
	'lead'    => sprintf( 'El número del plan es la cilindrada de la moto: el Plan 125 agrupa motos de 125 cc. Todos se pagan en %s cuotas fijas y en pesos.', MotoCred_Data::plazos_text() ),
	'actions' => motocred_sim_link( 'Simulá tu cuota', 'mc-btn mc-btn--primary', array( 'ubicacion' => 'planes_hero' ) ),
) );
?>

<section class="mc-section mc-section--tight">
	<div class="mc-container">
		<div class="mc-grid mc-grid--plans">
			<?php foreach ( $planes as $p ) : ?>
				<?php echo motocred_plan_card( $p, array( 'ubicacion' => 'planes' ) ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="mc-section mc-section--subtle" id="comparar">
	<div class="mc-container">
		<?php mc_section_head( 'Comparar', 'Todas las cuotas en una tabla', 'Mirá cuánto pagás por mes según la cilindrada y la cantidad de cuotas.' ); ?>
		<?php get_template_part( 'template-parts/plans-table', null, array( 'planes' => $planes ) ); ?>
	</div>
</section>

<section class="mc-section" id="como-funciona">
	<div class="mc-container">
		<?php mc_section_head( 'Cómo funciona', 'De la simulación a tu moto' ); ?>
		<?php echo motocred_como_funciona(); // phpcs:ignore ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta-band', null, array( 'ctx' => array( 'intent' => 'financiacion' ), 'where' => 'planes_cta' ) );
get_footer();
