<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="mc-skip" href="#contenido">Saltar al contenido</a>

<header class="mc-header" data-mc-header>
	<div class="mc-container mc-header__inner">
		<div class="mc-header__logo"><?php mc_logo(); ?></div>

		<nav class="mc-nav" aria-label="Principal">
			<?php mc_nav( 'primary', 'mc-nav__list' ); ?>
		</nav>

		<div class="mc-header__actions">
			<?php
			if ( mc_ready() ) {
				echo motocred_wa_button( array( 'intent' => 'general', 'ubicacion' => 'header' ), 'WhatsApp', 'mc-btn mc-btn--wa-ghost mc-btn--sm mc-header__wa' ); // phpcs:ignore
				echo motocred_sim_link( 'Simulá tu cuota', 'mc-btn mc-btn--primary mc-btn--sm mc-header__sim', array( 'ubicacion' => 'header' ) ); // phpcs:ignore
			}
			?>
			<button class="mc-menu-toggle" type="button" aria-expanded="false" aria-controls="mc-drawer" data-mc-menu>
				<span class="mc-menu-toggle__open"><?php echo motocred_icon( 'menu', 24 ); // phpcs:ignore ?></span>
				<span class="mc-menu-toggle__close"><?php echo motocred_icon( 'close', 24 ); // phpcs:ignore ?></span>
				<span class="screen-reader-text">Menú</span>
			</button>
		</div>
	</div>

	<div class="mc-drawer" id="mc-drawer" hidden>
		<nav class="mc-container" aria-label="Menú móvil">
			<?php mc_nav( 'primary', 'mc-drawer__list' ); ?>
			<div class="mc-drawer__cta">
				<?php
				if ( mc_ready() ) {
					echo motocred_sim_link( 'Simulá tu cuota', 'mc-btn mc-btn--primary mc-btn--block', array( 'ubicacion' => 'menu_movil' ) ); // phpcs:ignore
					echo motocred_wa_button( array( 'intent' => 'general', 'ubicacion' => 'menu_movil' ), 'Hablar por WhatsApp', 'mc-btn mc-btn--wa mc-btn--block' ); // phpcs:ignore
				}
				?>
			</div>
		</nav>
	</div>
</header>

<main id="contenido" class="mc-main">
