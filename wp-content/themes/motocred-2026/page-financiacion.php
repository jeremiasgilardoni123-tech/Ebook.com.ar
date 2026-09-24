<?php
/**
 * Financiación: cómo funciona, requisitos, plazos y adjudicación, condiciones
 * relevantes y preguntas. El contenido propio de la página (editor) se muestra
 * al final, así no se pierde nada de lo que ya estaba cargado.
 */
if ( ! mc_ready() ) {
	require __DIR__ . '/page.php';
	return;
}

get_header();
the_post();

get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Financiación',
	'title'   => 'Financiación de motos 0KM, clara desde el principio',
	'lead'    => sprintf( 'Planes de %s cuotas fijas en pesos. Antes de empezar sabés cuánto pagás por mes y en qué cuota se adjudica tu moto.', MotoCred_Data::plazos_text() ),
	'actions' => motocred_sim_link( 'Simulá tu financiación', 'mc-btn mc-btn--primary', array( 'ubicacion' => 'financiacion_hero' ) ) . motocred_wa_button( array( 'intent' => 'financiacion', 'ubicacion' => 'financiacion_hero' ), 'Consultar por WhatsApp' ),
) );
?>

<nav class="mc-subnav" aria-label="En esta página">
	<div class="mc-container">
		<a href="#como-funciona">Cómo funciona</a>
		<a href="#requisitos">Requisitos</a>
		<a href="#plazos">Plazos</a>
		<a href="#condiciones">Condiciones</a>
		<a href="#preguntas">Preguntas</a>
	</div>
</nav>

<section class="mc-section" id="como-funciona">
	<div class="mc-container">
		<?php mc_section_head( 'Paso a paso', 'Cómo funciona' ); ?>
		<?php echo motocred_como_funciona(); // phpcs:ignore ?>
	</div>
</section>

<section class="mc-section mc-section--subtle" id="requisitos">
	<div class="mc-container mc-split">
		<div>
			<?php mc_section_head( 'Requisitos', 'Qué necesitás para empezar' ); ?>
		</div>
		<ul class="mc-checklist">
			<li><?php echo motocred_icon( 'id', 22 ); // phpcs:ignore ?><div><strong>Tu DNI</strong><span>Con tu documento iniciás el plan.</span></div></li>
			<li><?php echo motocred_icon( 'pesos', 22 ); // phpcs:ignore ?><div><strong>La primera cuota y los gastos</strong><span>Con ese pago queda hecha la suscripción al plan.</span></div></li>
			<li><?php echo motocred_icon( 'whatsapp', 22 ); // phpcs:ignore ?><div><strong>Hablar con un asesor</strong><span>Te confirma si tu caso necesita alguna documentación adicional.</span></div></li>
		</ul>
	</div>
</section>

<section class="mc-section" id="plazos">
	<div class="mc-container mc-split mc-split--sim">
		<div>
			<?php mc_section_head( 'Plazos', 'Cuotas y adjudicación', 'La adjudicación es el momento en que la moto queda asignada a tu plan. Depende de la cantidad de cuotas que elijas.' ); ?>
			<?php echo motocred_plazos_table(); // phpcs:ignore ?>
		</div>
		<?php echo motocred_simulador( array( 'mode' => 'compact', 'ubicacion' => 'financiacion' ) ); // phpcs:ignore ?>
	</div>
</section>

<section class="mc-section mc-section--subtle" id="comparar">
	<div class="mc-container">
		<?php mc_section_head( 'Cuotas', 'Todas las cuotas por plan', '', 'h2', array( 'Ver planes', get_post_type_archive_link( 'mc_plan' ) ) ); ?>
		<?php get_template_part( 'template-parts/plans-table' ); ?>
	</div>
</section>

<section class="mc-section" id="condiciones">
	<div class="mc-container mc-split">
		<div>
			<?php mc_section_head( 'Transparencia', 'Lo que tenés que saber antes de firmar' ); ?>
			<p><a class="mc-link-arrow" href="<?php echo esc_url( motocred_page_url( 'terminos' ) ); ?>">Leer Términos y Condiciones completos <?php echo motocred_icon( 'arrow', 18 ); // phpcs:ignore ?></a></p>
		</div>
		<ul class="mc-checklist">
			<li><?php echo motocred_icon( 'check', 22 ); // phpcs:ignore ?><div><strong>Las cuotas son fijas y en pesos</strong><span>El valor no cambia durante el plan.</span></div></li>
			<li><?php echo motocred_icon( 'check', 22 ); // phpcs:ignore ?><div><strong>Son cuotas iguales y consecutivas</strong><span>Planes de <?php echo esc_html( MotoCred_Data::plazos_text() ); ?> cuotas.</span></div></li>
			<li><?php echo motocred_icon( 'check', 22 ); // phpcs:ignore ?><div><strong>El plan se elige en la Nota de Pedido</strong><span>Después no se puede cambiar, salvo acuerdo entre las partes.</span></div></li>
			<li><?php echo motocred_icon( 'check', 22 ); // phpcs:ignore ?><div><strong>La entrega depende de la adjudicación</strong><span>Tu asesor te confirma la fecha estimada según tu plan.</span></div></li>
		</ul>
	</div>
</section>

<?php if ( trim( get_the_content() ) ) : ?>
<section class="mc-section mc-section--subtle">
	<div class="mc-container mc-prose"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<section class="mc-section" id="preguntas">
	<div class="mc-container mc-split">
		<div><?php mc_section_head( 'Preguntas frecuentes', 'Dudas sobre la financiación' ); ?></div>
		<?php echo motocred_faq(); // phpcs:ignore ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta-band', null, array( 'ctx' => array( 'intent' => 'financiacion' ), 'where' => 'financiacion_cta' ) );
get_footer();
