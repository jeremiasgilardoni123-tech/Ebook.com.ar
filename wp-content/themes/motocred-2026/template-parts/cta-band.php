<?php
/** Banda de cierre: una sola acción principal + WhatsApp. */
if ( ! mc_ready() ) {
	return;
}
$args = wp_parse_args( $args ?? array(), array(
	'title' => '¿Cuánto pagarías por tu moto?',
	'lead'  => 'Simulalo en segundos. Sin registrarte y sin compromiso.',
	'ctx'   => array( 'intent' => 'general' ),
	'where' => 'cta_final',
) );
?>
<section class="mc-cta-band">
	<div class="mc-container mc-cta-band__inner">
		<div>
			<h2 class="mc-cta-band__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<p class="mc-cta-band__lead"><?php echo esc_html( $args['lead'] ); ?></p>
		</div>
		<div class="mc-cta-band__actions">
			<?php echo motocred_sim_link( 'Simulá tu cuota', 'mc-btn mc-btn--primary', array( 'ubicacion' => $args['where'] ) ); // phpcs:ignore ?>
			<?php echo motocred_wa_button( $args['ctx'] + array( 'ubicacion' => $args['where'] ), 'Hablar con un asesor', 'mc-btn mc-btn--wa' ); // phpcs:ignore ?>
		</div>
	</div>
</section>
