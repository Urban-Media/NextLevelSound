<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
?>

<div class="container-fluid no_hor_padding nls_page_content">

	<div class="container">

		<div class="row">

				<div class="col-lg-8 col-12">

					<main class="site-main" id="main">

						<?php while ( have_posts() ) : the_post(); ?>

							<?php get_template_part( 'loop-templates/content', 'single' ); ?>

						<?php endwhile; // end of the loop. ?>

					</main><!-- #main -->

				</div>

				<div class="col-lg-4 col-12">
					<?php get_sidebar( 'right' ); ?>
				</div>

		</div><!-- .row -->

	</div><!-- Container end -->

</div>

<?php get_footer(); ?>
