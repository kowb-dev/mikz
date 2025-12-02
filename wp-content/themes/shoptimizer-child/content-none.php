<?php
/**
 * The template part for displaying a message that posts cannot be found.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package shoptimizer
 */

?>

<div class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Ничего не найдено', 'shoptimizer' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">

		<?php if ( is_search() ) : ?>

			<p><?php esc_html_e( 'К сожалению, по вашему запросу ничего не найдено. Попробуйте другие ключевые слова.', 'shoptimizer' ); ?></p>
			<?php get_search_form(); ?>

			<?php
			if ( function_exists( 'shoptimizer_child_display_no_results_brands' ) ) {
				shoptimizer_child_display_no_results_brands();
			}
			?>

		<?php else : ?>

			<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'shoptimizer' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>

		<?php

			$noSearchResults = '';
			$query = new WP_Query(
			    array(
			        'post_type'              => 'wp_block',
			        'title'                  => 'No Search Results',
			        'post_status'            => 'publish',
			        'posts_per_page'         => 1
			    )
			);

			if ( $query->have_posts() == NULL ) {
				$noSearchResults = false;
			} else {

			$object = $query->post;
			echo apply_filters('the_content', $object->post_content);
			wp_reset_postdata();	

			}
		?>

	</div><!-- .page-content -->
</div><!-- .no-results -->
