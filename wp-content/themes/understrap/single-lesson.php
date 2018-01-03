<?php
/**
 * The template for displaying all single posts.
 *
 * @package understrap
 */

get_header();
?>
<div class="container-fluid no_hor_padding nls_page_content">

	<div class="container" id="content" tabindex="-1">

		<div class="row">

				<div class="col-lg-8 col-12">

					<main class="site-main" id="main">

						<?php while ( have_posts() ) : the_post(); ?>

							<?php get_template_part( 'loop-templates/content', 'single-lesson' ); ?>

						<?php endwhile; // end of the loop. ?>

					</main><!-- #main -->

				</div>

				<div class="col-lg-4 col-12">
					<ul class="course_sidebar_syllabus">
						<?php //dynamic_sidebar( 'llms_lesson_widgets_side' ); ?>
						<?php dynamic_sidebar( 'llms_section_widgets_side' ); ?>
					</ul>

				</div>

		</div><!-- .row -->

	</div><!-- Container end -->

</div>

<?php get_footer(); ?>
