<?php
/**
 * Home: Hero con simulador → cilindradas → motos → cómo funciona →
 * respaldo → entregas reales → sucursales → preguntas → CTA final.
 */
get_header();

if ( ! mc_ready() ) {
	echo '<div class="mc-container mc-page"><p>Activá el plugin MotoCred Core.</p></div>';
	get_footer();
	return;
}

$planes      = MotoCred_Data::planes();
$destacadas  = MotoCred_Data::motos( array( 'destacadas' => 1, 'limit' => 6 ) );
$operativas  = MotoCred_Data::sucursales( 'operativa' );
$proximas    = MotoCred_Data::sucursales( 'proximamente' );
$stats       = MotoCred_Data::stats();
$entregas    = motocred_entregas_grid( 8 );
$hero_id     = mc_hero_image_id();
$plazos      = MotoCred_Data::plazo_numbers();
?>

<section class="mc-hero<?php echo $hero_id ? ' mc-hero--photo' : ''; ?>">
	<div class="mc-container mc-hero__grid">
		<div class="mc-hero__copy">
			<p class="mc-pill"><span class="mc-pill__dot" aria-hidden="true"></span><?php echo esc_html( mc_mod( 'mc_hero_eyebrow', 'Motos 0KM en Mendoza' ) ); ?></p>
			<h1 class="mc-hero__title"><?php echo esc_html( mc_mod( 'mc_hero_title', 'Tu moto 0KM, en cuotas fijas y en pesos.' ) ); ?></h1>
			<p class="mc-hero__lead"><?php echo esc_html( mc_mod( 'mc_hero_lead', 'Elegí tu moto, simulá la cuota en segundos y seguí la compra con un asesor por WhatsApp.' ) ); ?></p>
			<div class="mc-hero__cta">
				<a class="mc-btn mc-btn--primary" href="#simular"<?php echo motocred_track_attrs( 'cta_simular_click', array( 'ubicacion' => 'hero' ) ); // phpcs:ignore ?>><?php echo motocred_icon( 'calc' ); // phpcs:ignore ?><span>Simulá tu cuota</span></a>
				<a class="mc-btn mc-btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'mc_moto' ) ); ?>"><span>Ver motos</span><?php echo motocred_icon( 'arrow', 18 ); // phpcs:ignore ?></a>
			</div>
			<ul class="mc-trust-chips">
				<li><?php echo motocred_icon( 'pesos', 18 ); // phpcs:ignore ?>Cuotas fijas en pesos</li>
				<?php if ( $plazos ) : ?>
					<li><?php echo motocred_icon( 'calendar', 18 ); // phpcs:ignore ?>De <?php echo esc_html( min( $plazos ) ); ?> a <?php echo esc_html( max( $plazos ) ); ?> cuotas</li>
				<?php endif; ?>
				<li><?php echo motocred_icon( 'id', 18 ); // phpcs:ignore ?>Iniciás con tu DNI</li>
				<?php if ( $operativas ) : ?>
					<li><?php echo motocred_icon( 'pin', 18 ); // phpcs:ignore ?><?php echo esc_html( count( $operativas ) ); ?> <?php echo 1 === count( $operativas ) ? 'sucursal' : 'sucursales'; ?> en Mendoza</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php if ( $hero_id ) : ?>
			<div class="mc-hero__visual">
				<?php echo wp_get_attachment_image( $hero_id, 'mc-hero', false, array( 'class' => 'mc-hero__img', 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(min-width: 1024px) 50vw, 100vw' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="mc-hero__sim" id="simular">
			<?php echo motocred_simulador( array( 'mode' => 'compact', 'ubicacion' => 'home_hero' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php if ( $planes ) : ?>
<section class="mc-section" id="planes">
	<div class="mc-container">
		<?php mc_section_head( 'Planes', 'Elegí por cilindrada', 'Cada plan agrupa motos de una misma cilindrada. Elegí la que va con tu uso y mirá la cuota.', 'h2', array( 'Comparar planes', get_post_type_archive_link( 'mc_plan' ) ) ); ?>
		<div class="mc-rail" data-mc-rail>
			<?php foreach ( $planes as $p ) : ?>
				<?php echo motocred_plan_card( $p, array( 'ubicacion' => 'home_planes' ) ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( $destacadas ) : ?>
<section class="mc-section mc-section--subtle" id="motos">
	<div class="mc-container">
		<?php mc_section_head( 'Catálogo', 'Motos para empezar a mirar', '', 'h2', array( 'Ver todas las motos', get_post_type_archive_link( 'mc_moto' ) ) ); ?>
		<div class="mc-grid mc-grid--motos">
			<?php foreach ( $destacadas as $m ) : ?>
				<?php echo motocred_moto_card( $m, array( 'ubicacion' => 'home_destacadas' ) ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="mc-section" id="como-funciona">
	<div class="mc-container">
		<?php mc_section_head( 'Cómo funciona', 'De la simulación a tu moto, en 5 pasos', 'Sin vueltas: sabés cuánto pagás antes de empezar.' ); ?>
		<?php echo motocred_como_funciona(); // phpcs:ignore ?>
		<div class="mc-center-cta">
			<?php echo motocred_sim_link( 'Empezá simulando tu cuota', 'mc-btn mc-btn--primary', array( 'ubicacion' => 'home_pasos' ) ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<section class="mc-section mc-section--dark" id="respaldo">
	<div class="mc-container">
		<?php mc_section_head( 'Respaldo', 'Comprá con reglas claras', 'Antes de pagar sabés cuánto es la cuota, en qué cuota se adjudica tu moto y qué dice el contrato.' ); ?>

		<?php if ( $stats ) : ?>
			<dl class="mc-stats">
				<?php foreach ( $stats as $st ) : ?>
					<div class="mc-stat"><dt><?php echo esc_html( $st['label'] ); ?> <?php echo motocred_badge( ! empty( $st['validado'] ) ); // phpcs:ignore ?></dt><dd><?php echo esc_html( $st['value'] ); ?></dd></div>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>

		<ul class="mc-pillars">
			<li>
				<span class="mc-pillars__icon"><?php echo motocred_icon( 'pesos', 24 ); // phpcs:ignore ?></span>
				<h3>Cuotas fijas en pesos</h3>
				<p>El valor de la cuota se mantiene igual durante todo el plan.</p>
			</li>
			<li>
				<span class="mc-pillars__icon"><?php echo motocred_icon( 'shield', 24 ); // phpcs:ignore ?></span>
				<h3>Todo por escrito</h3>
				<p>Plan, plazo y adjudicación quedan en la Nota de Pedido. <a href="<?php echo esc_url( motocred_page_url( 'terminos' ) ); ?>">Leé los términos</a>.</p>
			</li>
			<li>
				<span class="mc-pillars__icon"><?php echo motocred_icon( 'pin', 24 ); // phpcs:ignore ?></span>
				<h3>Atención en persona</h3>
				<p><?php echo $operativas ? esc_html( sprintf( '%d %s en Mendoza para ver las motos y firmar.', count( $operativas ), 1 === count( $operativas ) ? 'sucursal' : 'sucursales' ) ) : 'Sucursales en Mendoza para ver las motos y firmar.'; ?></p>
			</li>
			<li>
				<span class="mc-pillars__icon"><?php echo motocred_icon( 'whatsapp', 24 ); // phpcs:ignore ?></span>
				<h3>Un asesor te acompaña</h3>
				<p>Desde la simulación hasta la entrega, por WhatsApp o en sucursal.</p>
			</li>
		</ul>
	</div>
</section>

<?php if ( $entregas ) : ?>
<section class="mc-section" id="entregas">
	<div class="mc-container">
		<?php mc_section_head( 'Entregas reales', 'Motos que ya están en la calle', 'Clientes de MotoCred retirando su 0KM. Fotos publicadas con su autorización.' ); ?>
		<?php echo $entregas; // phpcs:ignore ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $operativas ) : ?>
<section class="mc-section mc-section--subtle" id="sucursales">
	<div class="mc-container">
		<?php mc_section_head( 'Sucursales', 'Vení a conocer tu próxima moto', '', 'h2', array( 'Ver todas las sucursales', get_post_type_archive_link( 'mc_sucursal' ) ) ); ?>
		<div class="mc-grid mc-grid--branches">
			<?php foreach ( $operativas as $s ) : ?>
				<?php echo motocred_sucursal_card( $s ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $proximas ) : ?>
			<p class="mc-soon-line"><strong>Próximamente:</strong> <?php echo esc_html( implode( ' · ', wp_list_pluck( $proximas, 'nombre' ) ) ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<section class="mc-section" id="preguntas">
	<div class="mc-container mc-split">
		<div>
			<?php mc_section_head( 'Preguntas frecuentes', 'Lo que todos preguntan antes de empezar' ); ?>
			<div class="mc-split__aside-cta">
				<?php echo motocred_wa_button( array( 'intent' => 'financiacion', 'ubicacion' => 'home_faq' ), 'Tengo otra pregunta' ); // phpcs:ignore ?>
			</div>
		</div>
		<?php echo motocred_faq(); // phpcs:ignore ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta-band' ); ?>

<?php
get_footer();
