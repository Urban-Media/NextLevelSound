<?php
/**
 * Partial template for content in page.php
 *
 * @package understrap
 */

global $post;

//$course = new LLMS_Course( $post );

//$sections = $course->get_sections( 'posts' );

//var_dump($post);

$section = new LLMS_Section( $post->ID );
//var_dump($section);
?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="live_lesson_container">
          <div class="row">
            <div class="col-sm-10 col-12 text-center">
              <h2 class="sideblock_title live_lesson_title">
                Launch Live Lesson
              </h2>
            </div>
            <div class="col-sm-2 hidden-xs-down">
              <span class="live_lesson_circle text-right">
                <i class="fa fa-play red_text circle_icon play_icon" aria-hidden="true"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="lms_course_header">
    <h4 class="section_subtitle grey_text uppercase text-center">
      Section Overview
    </h4>
    <h3 class="llms-h3 llms-section-title section_title text-center">
      <?php echo the_title(); ?>
    </h3>
  </div>

	<div class="llms-syllabus-wrapper">

    <?php $lessons = $section->get_children_lessons();
    if ( $lessons ) : ?>

      <?php foreach ( $lessons as $l ) : ?>

        <?php llms_get_template( 'course/lesson-preview.php', array(
          'lesson' => new LLMS_Lesson( $l->ID ),
          'total_lessons' => count( $lessons ),
          'type'  => 'list'
        ) ); ?>

      <?php endforeach; ?>

    <?php else : ?>

      <?php _e( 'This section does not have any lessons.', 'lifterlms' ); ?>

    <?php endif; ?>


    <!-- Add Coursework Section -->

    <div class="container section_bottom_block">
      <div class="row section_bottom_block_header white_text uppercase">
        <div class="col-12">
          <h2 class="section_coursework">
            Coursework
          </h2>
        </div>
      </div>
      <div class="row section_bottom_block_body">
        <div class="col-lg-6 col-12">
          <div class="row">
            <div class="col-12">
              <h2 class="section_subheader text-center">
                <?php
                // This will be different for different sections
                $section = "Practice Stems";
                echo $section;
                ?>
              </h2>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <ul class="discourse_topic_list" style="padding-left:0px !important">
                <li class="discourse_topic">
                  Test
                </li>
                <li class="discourse_topic">
                  Test
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-12 section_bottom_block_divider">
          <div class="row">
            <div class="col-12">
              <h2 class="section_subheader">
                Upload Your Coursework
              </h2>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              TODO
            </div>
          </div>
        </div>
      </div>
    </div>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php edit_post_link( __( 'Edit', 'understrap' ), '<span class="edit-link">', '</span>' ); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
