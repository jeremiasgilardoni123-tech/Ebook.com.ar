<?php
/** Detalle de un plan: cuotas por plazo, motos incluidas, suscripción. */
get_header();
the_post();

$p     = MotoCred_Data::plan( get_the_ID() );
$motos = MotoCred_Data::motos( array( 'plan' => $p['id'] ) );

$actions  = motocred_sim_link( 'Simular este plan', 'mc-btn mc-btn--primary', array( 'plan' => $p['id'], 'ubicacion' => 'plan_hero' ) );
$actions .= motocred_wa_button( array( 'intent' => 'plan', 'plan' => $p, 'ubicacion' => 'plan_hero' ), 'Consultar por WhatsApp' );

get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => $p['cc'] ? $p['cc'] . ' cc · ' . $p['uso'] : $p['uso'],
	'title'   => $p['cc'] ? sprintf( '%s: motos de %s cc en cuotas', $p['nombre'], $p['cc'] ) : $p['nombre'],
	'lead'    => $p['resumen'] ?: sprintf( 'Elegí tu moto de %s cc y pagala en %s cuotas fijas en pesos.', $p['cc'], MotoCred_Data::plazos_text() ),
	'actions' => $actions,
) );
?>

<section class="mc-section mc-section--tight">
	<div class="mc-container mc-split mc-split--sim">
		<div>
			<h2 class="mc-h3">Cuotas del <?php echo esc_html( $p['nombre'] ); ?> <?php echo motocred_badge( $p['validado'] ); // phpcs:ignore ?></h2>
			<ul class="mc-quota-list">
				<?php foreach ( MotoCred_Data::plazos() as $pz ) : ?>
					<li>
						<span><strong><?php echo esc_html( $pz['n'] ); ?> cuotas</strong><small>Adjudicación en la cuota <?php echo esc_html( $pz['adj'] ); ?></small></span>
						<b><?php echo null !== $p['cuotas'][ $pz['n'] ] ? esc_html( motocred_money( $p['cuotas'][ $pz['n'] ] ) ) : 'Consultar'; ?></b>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( null !== $p['gastos'] ) : ?>
				<p class="mc-note">Gastos de suscripción: <?php echo esc_html( motocred_money( $p['gastos'] ) ); ?>.</p>
			<?php endif; ?>
			<?php if ( $p['suscripcion_url'] ) : ?>
				<p><a class="mc-btn mc-btn--ghost" href="<?php echo esc_url( $p['suscripcion_url'] ); ?>"<?php echo motocred_track_attrs( 'suscripcion_click', array( 'plan' => $p['nombre'], 'ubicacion' => 'plan_detalle' ) ); // phpcs:ignore ?>><span>Suscribirme al <?php echo esc_html( $p['nombre'] ); ?></span><?php echo motocred_icon( 'arrow', 18 ); // phpcs:ignore ?></a></p>
			<?php endif; ?>
		</div>
		<?php echo motocred_simulador( array( 'mode' => 'compact', 'plan' => $p['id'], 'ubicacion' => 'plan_detalle' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php if ( $motos ) : ?>
<section class="mc-section mc-section--subtle">
	<div class="mc-container">
		<?php mc_section_head( 'Motos del plan', 'Qué motos podés elegir con el ' . $p['nombre'] ); ?>
		<div class="mc-grid mc-grid--motos">
			<?php foreach ( $motos as $m ) : ?>
				<?php echo motocred_moto_card( $m, array( 'ubicacion' => 'plan_detalle' ) ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( get_the_content() ) : ?>
<section class="mc-section"><div class="mc-container mc-prose"><?php the_content(); ?></div></section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/cta-band', null, array(
	'title' => '¿Querés empezar con el ' . $p['nombre'] . '?',
	'lead'  => 'Un asesor te confirma la cuota vigente y los pasos para suscribirte.',
	'ctx'   => array( 'intent' => 'plan', 'plan' => $p ),
	'where' => 'plan_cta',
) );
get_footer();
