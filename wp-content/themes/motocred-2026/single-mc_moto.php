<?php
/**
 * Ficha de moto: foto, datos clave, cuota desde, CTAs con contexto,
 * simulador precargado con el modelo y motos del mismo plan.
 */
get_header();
the_post();

$m    = MotoCred_Data::moto( get_the_ID() );
$plan = $m['plan'];

// Galería: imagen destacada + imágenes adjuntas.
$gallery = array_filter( array_unique( array_merge(
	$m['imagen_id'] ? array( $m['imagen_id'] ) : array(),
	get_posts( array( 'post_type' => 'attachment', 'post_parent' => get_the_ID(), 'post_mime_type' => 'image', 'fields' => 'ids', 'posts_per_page' => 8, 'orderby' => 'menu_order', 'order' => 'ASC' ) )
) ) );
?>

<section class="mc-product">
	<div class="mc-container">
		<?php echo motocred_breadcrumbs(); // phpcs:ignore ?>
		<div class="mc-product__grid">
			<div class="mc-product__media">
				<?php if ( $gallery ) : ?>
					<div class="mc-gallery" data-mc-gallery>
						<?php foreach ( array_values( $gallery ) as $i => $img ) : ?>
							<figure class="mc-gallery__slide">
								<?php echo wp_get_attachment_image( $img, 'mc-hero', false, array( 'loading' => 0 === $i ? 'eager' : 'lazy', 'fetchpriority' => 0 === $i ? 'high' : 'auto', 'alt' => 0 === $i ? $m['nombre'] : $m['nombre'] . ' – foto ' . ( $i + 1 ), 'sizes' => '(min-width: 1024px) 55vw, 100vw' ) ); ?>
							</figure>
						<?php endforeach; ?>
					</div>
					<?php if ( count( $gallery ) > 1 ) : ?>
						<div class="mc-gallery__dots" aria-hidden="true"><?php foreach ( $gallery as $g ) : ?><span></span><?php endforeach; ?></div>
					<?php endif; ?>
				<?php else : ?>
					<div class="mc-gallery mc-gallery--empty"><span class="mc-placeholder"><?php echo motocred_icon( 'moto', 120 ); // phpcs:ignore ?></span></div>
				<?php endif; ?>
			</div>

			<div class="mc-product__info">
				<p class="mc-eyebrow"><?php echo esc_html( trim( $m['marca'] . ( $m['tipo'] ? ' · ' . $m['tipo'] : '' ), ' ·' ) ); ?></p>
				<h1 class="mc-product__title"><?php echo esc_html( $m['nombre'] ); ?></h1>
				<?php if ( $m['resumen'] ) : ?><p class="mc-lead"><?php echo esc_html( $m['resumen'] ); ?></p><?php endif; ?>

				<div class="mc-product__tags">
					<?php if ( $m['cc'] ) : ?><span class="mc-tag"><?php echo esc_html( $m['cc'] ); ?> cc</span><?php endif; ?>
					<?php if ( $plan ) : ?><a class="mc-tag mc-tag--link" href="<?php echo esc_url( $plan['url'] ); ?>"><?php echo esc_html( $plan['nombre'] ); ?></a><?php endif; ?>
					<span class="mc-tag">0KM</span>
				</div>

				<div class="mc-product__price">
					<?php if ( $plan && null !== $plan['desde'] ) : ?>
						<div class="mc-price">
							<span class="mc-price__label">Cuotas desde <?php echo motocred_badge( $plan['validado'] ); // phpcs:ignore ?></span>
							<strong class="mc-price__value"><?php echo esc_html( motocred_money( $plan['desde'] ) ); ?></strong>
							<span class="mc-price__note"><?php echo esc_html( $plan['desde_plazo'] ); ?> cuotas fijas en pesos · <?php echo esc_html( $plan['nombre'] ); ?></span>
						</div>
					<?php else : ?>
						<div class="mc-price">
							<span class="mc-price__label">Cuota</span>
							<strong class="mc-price__value mc-price__value--ask">Consultá el valor actualizado</strong>
							<span class="mc-price__note"><?php echo esc_html( MotoCred_Data::plazos_text() ); ?> cuotas fijas en pesos</span>
						</div>
					<?php endif; ?>
					<?php if ( null !== $m['precio'] ) : ?>
						<p class="mc-product__cash">Precio de referencia contado: <strong><?php echo esc_html( motocred_money( $m['precio'] ) ); ?></strong> <?php echo motocred_badge( $m['precio_ok'] ); // phpcs:ignore ?></p>
					<?php endif; ?>
				</div>

				<div class="mc-product__cta">
					<?php echo motocred_sim_link( 'Simular esta moto', 'mc-btn mc-btn--primary', array( 'moto' => $m['id'], 'ubicacion' => 'ficha_moto' ) ); // phpcs:ignore ?>
					<?php echo motocred_wa_button( array( 'intent' => 'moto', 'moto' => $m, 'ubicacion' => 'ficha_moto' ), 'Consultar por WhatsApp' ); // phpcs:ignore ?>
				</div>

				<?php if ( $m['specs'] ) : ?>
					<div class="mc-specs">
						<h2 class="mc-h3">Características principales <?php echo motocred_badge( $m['specs_ok'] ); // phpcs:ignore ?></h2>
						<dl>
							<?php foreach ( $m['specs'] as $sp ) : ?>
								<div><dt><?php echo esc_html( $sp['k'] ); ?></dt><dd><?php echo esc_html( $sp['v'] ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
						<p class="mc-note">Datos de referencia. Pueden variar según versión y año del modelo.</p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php if ( get_the_content() ) : ?>
<section class="mc-section mc-section--tight">
	<div class="mc-container mc-prose"><?php the_content(); ?></div>
</section>
<?php endif; ?>

<section class="mc-section mc-section--subtle" id="simulador">
	<div class="mc-container mc-split mc-split--sim">
		<div>
			<?php mc_section_head( 'Financiación', 'Calculá tu cuota para la ' . $m['nombre'], 'Elegí la cantidad de cuotas. Si querés, mandale la simulación a un asesor y seguí la compra por WhatsApp.' ); ?>
			<?php echo motocred_plazos_table(); // phpcs:ignore ?>
		</div>
		<?php echo motocred_simulador( array( 'mode' => 'compact', 'moto' => $m['id'], 'plan' => $plan['id'] ?? 0, 'ubicacion' => 'ficha_moto' ) ); // phpcs:ignore ?>
	</div>
</section>

<?php
$related = $plan ? MotoCred_Data::motos( array( 'plan' => $plan['id'], 'exclude' => array( $m['id'] ), 'limit' => 3 ) ) : array();
if ( $related ) :
	?>
<section class="mc-section">
	<div class="mc-container">
		<?php mc_section_head( $plan['nombre'], 'Otras motos del mismo plan', '', 'h2', array( 'Ver el ' . $plan['nombre'], $plan['url'] ) ); ?>
		<div class="mc-grid mc-grid--motos">
			<?php foreach ( $related as $r ) : ?>
				<?php echo motocred_moto_card( $r, array( 'ubicacion' => 'ficha_relacionadas' ) ); // phpcs:ignore ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="mc-section" id="como-funciona">
	<div class="mc-container">
		<?php mc_section_head( 'Cómo comprar', 'Así te llevás la ' . $m['nombre'] ); ?>
		<?php echo motocred_como_funciona(); // phpcs:ignore ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta-band', null, array(
	'title' => '¿Te gusta la ' . $m['nombre'] . '?',
	'lead'  => 'Consultá disponibilidad y la cuota vigente con un asesor.',
	'ctx'   => array( 'intent' => 'moto', 'moto' => $m ),
	'where' => 'ficha_moto_cta',
) );
get_footer();
