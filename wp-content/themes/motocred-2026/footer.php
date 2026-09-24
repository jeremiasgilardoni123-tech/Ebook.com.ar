</main>

<?php
$mc_s = mc_ready() ? MotoCred_Data::settings() : array();
?>
<footer class="mc-footer">
	<div class="mc-container">
		<div class="mc-footer__top">
			<div class="mc-footer__brand">
				<div class="mc-footer__logo"><?php mc_logo(); ?></div>
				<p>Motos 0KM en cuotas fijas y en pesos, con atención en sucursales de Mendoza.</p>
				<?php if ( mc_ready() ) : ?>
					<div class="mc-footer__cta">
						<?php echo motocred_sim_link( 'Simulá tu cuota', 'mc-btn mc-btn--primary mc-btn--sm', array( 'ubicacion' => 'footer' ) ); // phpcs:ignore ?>
						<?php echo motocred_wa_button( array( 'intent' => 'general', 'ubicacion' => 'footer' ), 'WhatsApp', 'mc-btn mc-btn--wa mc-btn--sm' ); // phpcs:ignore ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( mc_ready() ) : ?>
				<div class="mc-footer__col">
					<p class="mc-footer__title">Motos</p>
					<ul>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'mc_moto' ) ); ?>">Todas las motos</a></li>
						<?php
						$mc_marcas = get_terms( array( 'taxonomy' => 'mc_marca', 'hide_empty' => true ) );
						foreach ( is_array( $mc_marcas ) ? $mc_marcas : array() as $t ) {
							printf( '<li><a href="%s">Motos %s</a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
						}
						?>
					</ul>
				</div>
				<div class="mc-footer__col">
					<p class="mc-footer__title">Financiación</p>
					<ul>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'mc_plan' ) ); ?>">Planes por cilindrada</a></li>
						<li><a href="<?php echo esc_url( motocred_page_url( 'simulador' ) ); ?>">Simulador</a></li>
						<li><a href="<?php echo esc_url( motocred_page_url( 'financiacion' ) ); ?>">Cómo funciona</a></li>
						<li><a href="<?php echo esc_url( motocred_page_url( 'cotizador' ) ); ?>">Cotizá tu usada</a></li>
					</ul>
				</div>
				<div class="mc-footer__col">
					<p class="mc-footer__title">MotoCred</p>
					<ul>
						<li><a href="<?php echo esc_url( motocred_page_url( 'nosotros' ) ); ?>">Nosotros</a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'mc_sucursal' ) ); ?>">Sucursales</a></li>
						<li><a href="<?php echo esc_url( motocred_page_url( 'contacto' ) ); ?>">Contacto</a></li>
						<li><a href="<?php echo esc_url( motocred_page_url( 'terminos' ) ); ?>">Términos y condiciones</a></li>
						<?php
						$mc_arrep = get_page_by_path( 'boton-de-arrepentimiento' );
						if ( $mc_arrep && 'publish' === $mc_arrep->post_status ) {
							printf( '<li><a href="%s">Botón de arrepentimiento</a></li>', esc_url( get_permalink( $mc_arrep ) ) );
						}
						?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<div class="mc-footer__bottom">
			<p>
				© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( ! empty( $mc_s['razon_social'] ) ? $mc_s['razon_social'] : get_bloginfo( 'name' ) ); ?>
				<?php if ( ! empty( $mc_s['cuit'] ) ) : ?> · CUIT <?php echo esc_html( $mc_s['cuit'] ); ?><?php endif; ?>
			</p>
			<p class="mc-footer__social">
				<?php if ( ! empty( $mc_s['instagram'] ) ) : ?><a href="<?php echo esc_url( $mc_s['instagram'] ); ?>" target="_blank" rel="noopener">Instagram</a><?php endif; ?>
				<?php if ( ! empty( $mc_s['facebook'] ) ) : ?><a href="<?php echo esc_url( $mc_s['facebook'] ); ?>" target="_blank" rel="noopener">Facebook</a><?php endif; ?>
			</p>
		</div>
	</div>
</footer>

<?php if ( mc_ready() ) : ?>
<nav class="mc-tabbar" aria-label="Accesos rápidos">
	<a class="mc-tabbar__item<?php echo ( is_post_type_archive( 'mc_moto' ) || is_singular( 'mc_moto' ) ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'mc_moto' ) ); ?>"><?php echo motocred_icon( 'moto', 22 ); // phpcs:ignore ?><span>Motos</span></a>
	<a class="mc-tabbar__item<?php echo ( is_post_type_archive( 'mc_plan' ) || is_singular( 'mc_plan' ) ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'mc_plan' ) ); ?>"><?php echo motocred_icon( 'plans', 22 ); // phpcs:ignore ?><span>Planes</span></a>
	<a class="mc-tabbar__item mc-tabbar__item--main" href="<?php echo esc_url( motocred_page_url( 'simulador' ) ); ?>#simulador"<?php echo motocred_track_attrs( 'cta_simular_click', array( 'ubicacion' => 'tabbar' ) ); // phpcs:ignore ?>><?php echo motocred_icon( 'calc', 22 ); // phpcs:ignore ?><span>Simular</span></a>
	<?php
	$mc_wa = motocred_wa_url( array( 'intent' => 'general' ) );
	if ( $mc_wa ) {
		printf( '<a class="mc-tabbar__item mc-tabbar__item--wa" href="%s" target="_blank" rel="noopener"%s>%s<span>WhatsApp</span></a>', esc_url( $mc_wa ), motocred_track_attrs( 'whatsapp_click', array( 'intent' => 'general', 'ubicacion' => 'tabbar' ) ), motocred_icon( 'whatsapp', 22 ) ); // phpcs:ignore
	} else {
		printf( '<a class="mc-tabbar__item" href="%s">%s<span>Sucursales</span></a>', esc_url( get_post_type_archive_link( 'mc_sucursal' ) ), motocred_icon( 'pin', 22 ) ); // phpcs:ignore
	}
	?>
</nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
