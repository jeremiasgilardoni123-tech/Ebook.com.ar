<?php
/**
 * Catálogo de motos (también se usa para /motos/marca/{marca}/ y /motos/tipo/{tipo}/).
 * Filtros en el cliente: cilindrada, marca, tipo y orden por cuota.
 * Sin JavaScript se ven todas las motos (y los links de marca siguen funcionando).
 */
get_header();

$term  = is_tax() ? get_queried_object() : null;
$motos = array();
while ( have_posts() ) {
	the_post();
	$motos[] = MotoCred_Data::moto( get_the_ID() );
}
$motos  = array_filter( $motos );
$ccs    = array_unique( array_filter( wp_list_pluck( $motos, 'cc' ) ) );
sort( $ccs, SORT_NUMERIC );
$marcas = get_terms( array( 'taxonomy' => 'mc_marca', 'hide_empty' => true ) );
$tipos  = get_terms( array( 'taxonomy' => 'mc_tipo', 'hide_empty' => true ) );

$title = $term
	? ( 'mc_marca' === $term->taxonomy ? sprintf( 'Motos %s 0KM en cuotas', $term->name ) : sprintf( 'Motos %s 0KM en cuotas', $term->name ) )
	: 'Motos 0KM en cuotas';

get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Catálogo',
	'title'   => $title,
	'lead'    => $term && $term->description ? $term->description : 'Todas las motos se financian en cuotas fijas y en pesos. Filtrá por cilindrada o marca y simulá tu cuota.',
) );
?>

<section class="mc-section mc-section--tight">
	<div class="mc-container">
		<?php if ( ! $term ) : ?>
		<div class="mc-filters" data-mc-filters>
			<?php if ( count( $ccs ) > 1 ) : ?>
				<div class="mc-filters__group" role="group" aria-label="Cilindrada">
					<button type="button" class="mc-filter-chip is-active" data-filter="cc" data-value="" aria-pressed="true">Todas</button>
					<?php foreach ( $ccs as $cc ) : ?>
						<button type="button" class="mc-filter-chip" data-filter="cc" data-value="<?php echo esc_attr( $cc ); ?>" aria-pressed="false"><?php echo esc_html( $cc ); ?> cc</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="mc-filters__selects">
				<?php if ( is_array( $marcas ) && count( $marcas ) > 1 ) : ?>
					<label class="mc-select"><span class="screen-reader-text">Marca</span>
						<select data-filter="marca">
							<option value="">Todas las marcas</option>
							<?php foreach ( $marcas as $t ) : ?><option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				<?php if ( is_array( $tipos ) && count( $tipos ) > 1 ) : ?>
					<label class="mc-select"><span class="screen-reader-text">Tipo</span>
						<select data-filter="tipo">
							<option value="">Todos los tipos</option>
							<?php foreach ( $tipos as $t ) : ?><option value="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></option><?php endforeach; ?>
						</select>
					</label>
				<?php endif; ?>
				<label class="mc-select"><span class="screen-reader-text">Ordenar</span>
					<select data-sort>
						<option value="">Orden recomendado</option>
						<option value="desde-asc">Cuota más baja</option>
						<option value="cc-asc">Menor cilindrada</option>
						<option value="cc-desc">Mayor cilindrada</option>
					</select>
				</label>
			</div>
			<p class="mc-filters__count" aria-live="polite" data-mc-count><?php echo esc_html( sprintf( _n( '%d moto', '%d motos', count( $motos ), 'motocred' ), count( $motos ) ) ); ?></p>
		</div>
		<?php endif; ?>

		<?php if ( $motos ) : ?>
			<div class="mc-grid mc-grid--motos" data-mc-list>
				<?php foreach ( $motos as $m ) : ?>
					<?php echo motocred_moto_card( $m, array( 'ubicacion' => $term ? 'catalogo_' . $term->slug : 'catalogo' ) ); // phpcs:ignore ?>
				<?php endforeach; ?>
			</div>
			<div class="mc-empty" data-mc-empty hidden>
				<p>No hay motos con esos filtros.</p>
				<button type="button" class="mc-btn mc-btn--ghost mc-btn--sm" data-mc-reset>Ver todas</button>
			</div>
		<?php else : ?>
			<div class="mc-empty"><p>Estamos actualizando el catálogo. Consultá los modelos disponibles por WhatsApp.</p><?php echo motocred_wa_button( array( 'intent' => 'general', 'ubicacion' => 'catalogo_vacio' ) ); // phpcs:ignore ?></div>
		<?php endif; ?>

		<?php if ( is_array( $marcas ) && $marcas ) : ?>
			<nav class="mc-brand-links" aria-label="Motos por marca">
				<p class="mc-eyebrow">Buscar por marca</p>
				<ul>
					<?php foreach ( $marcas as $t ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $t ) ); ?>"<?php echo ( $term && $term->term_id === $t->term_id ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $t->name ); ?> <span><?php echo esc_html( $t->count ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		<?php endif; ?>
	</div>
</section>

<?php
get_template_part( 'template-parts/cta-band', null, array(
	'title' => '¿No encontrás el modelo que buscás?',
	'lead'  => 'Preguntanos: te decimos qué motos hay disponibles y en qué plan entran.',
	'where' => 'catalogo_cta',
) );
get_footer();
