<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package understrap
 */

get_header();

$container   = get_theme_mod( 'understrap_container_type' );
$sidebar_pos = get_theme_mod( 'understrap_sidebar_position' );

?>

<!--<div class="wrapper" id="page-wrapper">-->

	<div class="container" id="content" tabindex="-1">

		<div class="row">

			<div class="col-md-8 col-12">

				<main class="site-main" id="main">

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'loop-templates/content', 'page' ); ?>

					<?php endwhile; // end of the loop. ?>

				</main><!-- #main -->

			</div>

			<div class="col-md-4 col-12">

				<?php
				if (is_lesson()) {
					dynamic_sidebar( 'lifter_course_catalogue' );
				} else if (is_page(6)) { // Course registration page
					dynamic_sidebar( 'upsell' );
				} else {
					get_sidebar( 'right' );
				}
				?>

			</div>

		</div><!-- .row -->

	</div><!-- Container end -->

<!--</div>--><!-- Wrapper end -->

<?php get_footer(); ?>
