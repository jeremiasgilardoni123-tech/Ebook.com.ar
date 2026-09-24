<?php
/** Encabezado de página interior: breadcrumbs + H1 + bajada. */
$args = wp_parse_args( $args ?? array(), array( 'eyebrow' => '', 'title' => get_the_title(), 'lead' => '', 'actions' => '' ) );
?>
<section class="mc-page-hero">
	<div class="mc-container">
		<?php echo mc_ready() ? motocred_breadcrumbs() : ''; // phpcs:ignore ?>
		<?php if ( $args['eyebrow'] ) : ?><p class="mc-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p><?php endif; ?>
		<h1 class="mc-page-hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
		<?php if ( $args['lead'] ) : ?><p class="mc-lead mc-page-hero__lead"><?php echo esc_html( $args['lead'] ); ?></p><?php endif; ?>
		<?php if ( $args['actions'] ) : ?><div class="mc-page-hero__actions"><?php echo $args['actions']; // phpcs:ignore ?></div><?php endif; ?>
	</div>
</section>
