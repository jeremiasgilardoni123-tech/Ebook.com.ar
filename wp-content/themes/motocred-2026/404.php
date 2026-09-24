<?php
/** 404: nunca un callejón sin salida. */
get_header();
get_template_part( 'template-parts/page-hero', null, array(
	'eyebrow' => 'Error 404',
	'title'   => 'No encontramos esta página',
	'lead'    => 'Puede que la dirección haya cambiado. Estas son las secciones más buscadas:',
) );
?>
<section class="mc-section mc-section--tight">
	<div class="mc-container">
		<div class="mc-quick-links">
			<?php if ( mc_ready() ) : ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mc_moto' ) ); ?>"><?php echo motocred_icon( 'moto', 26 ); // phpcs:ignore ?><span>Ver motos</span></a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mc_plan' ) ); ?>"><?php echo motocred_icon( 'plans', 26 ); // phpcs:ignore ?><span>Planes y cuotas</span></a>
				<a href="<?php echo esc_url( motocred_page_url( 'simulador' ) ); ?>"><?php echo motocred_icon( 'calc', 26 ); // phpcs:ignore ?><span>Simulador</span></a>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'mc_sucursal' ) ); ?>"><?php echo motocred_icon( 'pin', 26 ); // phpcs:ignore ?><span>Sucursales</span></a>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php
get_footer();
