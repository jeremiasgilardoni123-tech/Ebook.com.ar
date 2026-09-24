<?php
/** Sucursales: las operativas primero y con todo el peso; las próximas, aparte. */
get_header();

$operativas = MotoCred_Data::sucursales( 'operativa' );
$proximas   = MotoCred_Data::sucursales( 'proximamente' );

get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Sucursales',
	'title'   => 'Sucursales MotoCred en Mendoza',
	'lead'    => 'Vení a ver las motos, resolvé tus dudas con un asesor y firmá tu plan.',
) );
?>
<section class="mc-section mc-section--tight">
	<div class="mc-container">
		<?php if ( $operativas ) : ?>
			<div class="mc-grid mc-grid--branches">
				<?php foreach ( $operativas as $s ) : ?>
					<?php echo motocred_sucursal_card( $s, array( 'map' => 1 ) ); // phpcs:ignore ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="mc-empty"><p>Consultá la sucursal más cercana por WhatsApp.</p></div>
		<?php endif; ?>

		<?php if ( $proximas ) : ?>
			<div class="mc-soon">
				<h2 class="mc-h3">Próximas aperturas</h2>
				<div class="mc-grid mc-grid--branches">
					<?php foreach ( $proximas as $s ) : ?>
						<?php echo motocred_sucursal_card( $s ); // phpcs:ignore ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band', null, array( 'title' => '¿Preferís resolverlo sin moverte?', 'lead' => 'Simulá tu cuota y hablá con un asesor por WhatsApp.', 'where' => 'sucursales_cta' ) );
get_footer();
