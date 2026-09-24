<?php
/** Simulador completo (la página existente sigue funcionando con su slug). */
if ( ! mc_ready() ) {
	require __DIR__ . '/page.php';
	return;
}

get_header();
the_post();

get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Simulador',
	'title'   => 'Simulá tu financiación',
	'lead'    => 'Elegí cilindrada y cantidad de cuotas. Si te sirve, mandale la simulación a un asesor y seguí la compra por WhatsApp.',
) );

// Si la página tiene el shortcode del simulador, se respeta su contenido tal cual.
$content_has_sim = has_shortcode( get_the_content(), 'motocred_simulador' );
?>
<section class="mc-section mc-section--tight" id="simulador">
	<div class="mc-container mc-split mc-split--sim mc-split--reverse">
		<aside class="mc-sim-aside">
			<h2 class="mc-h3">¿Qué pasa después?</h2>
			<ol class="mc-mini-steps">
				<li><strong>Enviás tu simulación</strong> por WhatsApp con un toque.</li>
				<li><strong>Un asesor te confirma</strong> el valor vigente y la disponibilidad.</li>
				<li><strong>Te suscribís</strong> con tu DNI, la primera cuota y los gastos.</li>
				<li><strong>Retirás tu moto</strong> según la adjudicación de tu plan.</li>
			</ol>
			<?php echo motocred_plazos_table(); // phpcs:ignore ?>
		</aside>
		<div>
			<?php
			if ( $content_has_sim ) {
				the_content();
			} else {
				echo motocred_simulador( array( 'mode' => 'full', 'ubicacion' => 'simulador' ) ); // phpcs:ignore
			}
			?>
		</div>
	</div>
</section>

<?php if ( ! $content_has_sim && trim( get_the_content() ) ) : ?>
<section class="mc-section mc-section--subtle"><div class="mc-container mc-prose"><?php the_content(); ?></div></section>
<?php endif; ?>

<section class="mc-section" id="preguntas">
	<div class="mc-container mc-split">
		<div><?php mc_section_head( 'Preguntas frecuentes', 'Antes de avanzar' ); ?></div>
		<?php echo motocred_faq(); // phpcs:ignore ?>
	</div>
</section>
<?php
get_footer();
