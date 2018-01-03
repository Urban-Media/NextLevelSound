<?php
/**
 * Template Name: Educational Discounts
 *
 * Largely the same as a regular page but with a custom check to make sure
 * students are enrolled on a course (any course)
 */
 global $post;
 get_header();
?>

	<div class="container" id="content" tabindex="-1">

		<div class="row">

			<div class="col-md-8 col-12">

				<main class="site-main" id="main">

          <?php
          /*
           * Check if they're a student enrolled on any course - if not,
           * disallow access
           */
           $allowAccess = false;

           $student = llms_get_student();
           // $student will be false if there's no logged in user

           // see if the student is enrolled in at least one course
           if ($student) {
             $courses = $student->get_courses(
              array(
             	   'status' => 'enrolled',
             	   'limit' => 1,
              )
             );
             if ( $courses['results'] ) {
             	// user is logged in and enrolled on a course
              $allowAccess = true;
             }
           }
           ?>

          <?php if ($allowAccess) { ?>
  					<?php while ( have_posts() ) : the_post(); ?>

  						<?php get_template_part( 'loop-templates/content', 'page' ); ?>

  					<?php endwhile; // end of the loop. ?>
          <?php } else { // Access denied
              the_field('access_denied');
          } ?>

				</main><!-- #main -->

			</div>

			<div class="col-md-4 col-12">

				<?php
				get_sidebar( 'right' );
				?>

			</div>

		</div><!-- .row -->

	</div><!-- Container end -->

<?php get_footer(); ?>
