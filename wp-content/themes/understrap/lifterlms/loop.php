<?php
/**
 * Generic loop template
 *
 * utilized by both courses and memberships
 *
 * @author 		LifterLMS
 * @package 	LifterLMS/Templates
 * @since       1.0.0
 * @version     3.14.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<?php get_header( 'llms_loop' ); ?>

  <?php //do_action( 'lifterlms_before_main_content' ); ?>

  <div class="container">
    <div class="row">
      <div class="col-lg-8 col-12">

      <?php /*if ( apply_filters( 'lifterlms_show_page_title', true ) ) : ?>

      	<h1 class="page-title"><?php lifterlms_page_title(); ?></h1>

      <?php endif;*/ ?>

      <?php //do_action( 'lifterlms_archive_description' ); ?>

      <?php
      	/**
      	 * lifterlms_loop
      	 * @hooked lifterlms_loop - 10
      	 */
      	do_action( 'lifterlms_loop' );
      ?>

      <?php //do_action( 'lifterlms_after_main_content' ); ?>
    </div>

    <div class="col-lg-4 col-12">

      <?php do_action( 'lifterlms_sidebar' ); ?>

      <?php get_sidebar('right'); ?>

    </div>

  </div>
</div>

<?php get_footer(); ?>
