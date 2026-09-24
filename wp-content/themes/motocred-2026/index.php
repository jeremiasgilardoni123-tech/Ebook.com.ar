<?php
/** Fallback: blog, búsquedas y archivos genéricos. */
get_header();
get_template_part( 'template-parts/page-hero', null, array(
	'title' => is_search() ? sprintf( 'Resultados para «%s»', get_search_query() ) : ( is_home() ? 'Novedades' : wp_strip_all_tags( get_the_archive_title() ) ),
) );
?>
<section class="mc-section mc-section--tight">
	<div class="mc-container">
		<?php if ( have_posts() ) : ?>
			<div class="mc-post-list">
				<?php while ( have_posts() ) : the_post(); ?>
					<article class="mc-post-item">
						<h2 class="mc-h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<div class="mc-empty"><p>No encontramos resultados.</p></div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
