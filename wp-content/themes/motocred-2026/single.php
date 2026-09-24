<?php
/** Entrada de blog. */
get_header();
the_post();
get_template_part( 'template-parts/page-hero', null, array( 'eyebrow' => get_the_date() ) );
?>
<section class="mc-section mc-section--tight">
	<div class="mc-container mc-prose">
		<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large' ); } ?>
		<?php the_content(); ?>
	</div>
</section>
<?php
get_template_part( 'template-parts/cta-band' );
get_footer();
