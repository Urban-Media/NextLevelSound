<?php
/**
 * The Template for displaying all single courses.
 *
 * @author 		codeBOX
 * @package 	LifterLMS/Templates
 * @since       1.0.0
 * @version     3.14.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<li <?php post_class( 'llms-loop-item' ); ?>>
	<div class="llms-loop-item-content nls_course_nav nls_course_catalogue_loop">

	<?php
		/**
		 * lifterlms_before_loop_item
		 * @hooked lifterlms_loop_featured_video - 8
		 * @hooked lifterlms_loop_link_start - 10
		 */
		do_action( 'lifterlms_before_loop_item' );
	?>

	<?php
		/**
		 * lifterlms_before_loop_item_title
		 * @hooked lifterlms_template_loop_thumbnail - 10
		 * @hooked lifterlms_template_loop_progress - 15
		 */
		//do_action( 'lifterlms_before_loop_item_title' );
	?>

  <?php
  $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
  ?>
  <div class="nls_course_catalogue_image_spacer">
    <div class="nls_course_catalogue_image" style="background-image: url('<?php echo $featuredImage[0]; ?>');"></div>
  </div>

	<h4 class="nls_course_catalogue_title uppercase text-center"><?php the_title(); ?></h4>

  <?php
  /*
   * Add a 'Buy Now' or 'Continue' button depending on whether
   * the user has access to the course or not
   */
   //if (!is_user_logged_in()) {
     // Do something if user is not logged in
   //} else {
     $courseID = get_the_ID();

     /*
      * LifterLMS doesn't restrict courses, rather it restricts
      * lessons. Therefore we need to check if a lesson within the course
      * is restricted.
      */
     /*$course = new LLMS_Course( $courseID );
     $restrictions = llms_page_restricted( $courseID, get_current_user_id() );
     $coursePT = get_post_type($courseID); //var_dump($coursePT);
     //var_dump($restrictions);
     $student = new LLMS_Student(); //var_dump($student);
     $nextLesson = llms_get_post( $student->get_next_lesson( $courseID ) ); //var_dump($nextLesson);
     $nextLevelRestrictions = llms_page_restricted( $nextLesson->post->ID, get_current_user_id() );
     $levelPT = get_post_type($nextLesson->post->ID); //var_dump($levelPT);
     $isRestricted = $nextLevelRestrictions['is_restricted'];
     //var_dump($nextLevelRestrictions);
     //var_dump($isRestricted);
     //var_dump($nextLesson->post->ID);

     $re = llms_is_page_restricted($nextLesson->post->ID, get_current_user_id());
     $single = is_singular();*/
     //var_dump($single);
     //var_dump($re);

   //}
  ?>

  <?php
  if (get_field('course_brief')) {
  ?>
    <div class="nls_course_catalogue_brief text-center">
      <?php the_field('course_brief'); ?>
    </div>
  <?php
  }
  ?>

  <a href="<?php echo get_permalink(); ?>" class="sideblock_link">
    <div class="nls_rounded_button teal_button white_text uppercase text-center sideblock_button">
      View course
    </div>
  </a>

	<footer class="llms-loop-item-footer">
		<?php
			/**
			 * lifterlms_after_loop_item_title
			 * @hooked lifterlms_template_loop_author - 10
			 * @hooked lifterlms_template_loop_length - 15
			 * @hooked lifterlms_template_loop_difficulty - 20
			 *
			 * On Student Dashboard & "Mine" Courses Shortcode
			 * @hooked lifterlms_template_loop_enroll_status - 25
			 * @hooked lifterlms_template_loop_enroll_date - 30
			 */
			do_action( 'lifterlms_after_loop_item_title' );
		?>
	</footer>

	<?php
		/**
		 * lifterlms_after_loop_item
		 * @hooked lifterlms_loop_link_end - 5
		 */
		do_action( 'lifterlms_after_loop_item' );
	?>

	</div><!-- .llms-loop-item-content -->
</li><!-- .llms-loop-item -->
