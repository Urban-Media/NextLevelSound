<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

 // Used to check Vimeography plugin is activated
 include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

 $course = llms_get_post( $post->ID );
 $instructors = $course->get_course()->get_instructors( true );
?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">

    <div class="entry-meta">

  		<span class="lms_lesson_author nimbus_sans">
        <?php
        /*if (!$instructors) {
          echo "Anonymous";
        } else {
          $instructorArray = array();
          foreach($instructors as $instructor) {
             $instructorArray[] = get_the_author_meta('display_name', $instructor['id']);
          }
        }

        echo _n('Author: ', 'Authors: ', count($instructorArray));
        echo implode(', ', $instructorArray);*/
        ?>
      </span>

		</div><!-- .entry-meta -->

	</header><!-- .entry-header -->

	<?php echo get_the_post_thumbnail( $post->ID, 'large' ); ?>

	<div class="entry-content">

    <?php
    /*
     * Most lessons will have a Vimeography Gallery ID associated with them
     */
    if (get_field('video_gallery_id') && is_plugin_active( 'vimeography/vimeography.php' )) {
      $galleryID = get_field('video_gallery_id');

      echo do_shortcode('[vimeography id="'.$galleryID.'"]');
    }
    ?>

		<?php the_content(); ?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
