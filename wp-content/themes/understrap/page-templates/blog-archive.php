<?php
/**
 * Template Name: Blog Archive
 *
 * Template for displaying an archive of all blog posts
 *
 */
global $post;
get_header();
?>

<div class="container" style="padding-top: 30px;">

  <div class="row">

    <div class="col-md-9 col-12">

      <div class="blog_categories">
        <div class="blog_subtitle section_subtitle uppercase">
          Categories
        </div>

        <div class="container">
          <div class="row">

            <?php
            /*
             * First display all categories here - there should be a fixed
             * amount of 4
             */
            $categories = get_categories();
            //var_dump($categories);
            $catTotal = count($categories);
            $i = 1;
            foreach($categories as $category) {
              $thumbnailProperties = json_decode($category->term_thumbnail);
              //var_dump($thumbnailProperties);
              ?>
              <div class="col-md-2 <?php if ($i != $catTotal) echo 'mr-md-auto'; ?> col-12 nls_course_nav no-hor-padding blog_archive_block">
                <a href="<?php echo get_category_link($category->cat_ID); ?>">
                  <img src="<?php echo $thumbnailProperties->sizes->thumbnail->url; ?>" alt="<?php echo $thumbnailProperties->alt; ?>" title="<?php echo $thumbnailProperties->title; ?>" style="border-top-left-radius: 10px;
        border-top-right-radius: 10px; width:100% !important;" data-mh="category_thumbnails">
                  <span class="list_category_name uppercase text-center">
                    <?php echo $category->name; ?>
                  </span>
                </a>
              </div>
              <?php
              $i++;
            }
            ?>

          </div>
        </div>
      </div>

      <div class="blog_posts">

        <div class="blog_subtitle section_subtitle uppercase">
          Latest Posts
        </div>

        <?php
        /*
         * Get the very most recent post
         */
        $mostRecentPostArgs = array(
          'numberposts'   => 1,
          'post_type'     => 'post'
        );

        $mostRecentPost = wp_get_recent_posts( $mostRecentPostArgs, ARRAY_A );
        ?>
        <div class="blog_primary_featured_post nls_course_nav blog_archive_block">
          <a href="<?php echo get_permalink($mostRecentPost[0]['ID']); ?>">
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-7 col-12" style="padding-left: 0px !important; padding-right: 0px !important;">
                  <?php
                  echo get_the_post_thumbnail($mostRecentPost[0]['ID'], 'primary_blog_post_thumbnail', array('class' => 'blog_primary_image'));
                  ?>
                </div>
                <div class="col-md-5 col-12" style="padding-left: 0px !important; padding-right: 0px !important;">
                  <div class="blog_primary_content">
                    <div class="blog_title text-center">
                      <?php
                      echo $mostRecentPost[0]['post_title'];
                      ?>
                    </div>

                    <div class="blog_excerpt text-center">
                      <?php
                      echo $mostRecentPost[0]['post_excerpt'];
                      ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>

        <?php
        /*
         * Get the next two most recent posts
         */
        $nextMostRecentPostArgs = array(
          'numberposts'   => 2,
          'offset'        => 1,
          'post_type'     => 'post'
        );

        $nextMostRecentPosts = wp_get_recent_posts( $nextMostRecentPostArgs, ARRAY_A );
        $j = 1;
        ?>
        <div class="container-fluid blog_secondary_posts">
          <div class="row justify-content-between">
            <?php
            foreach($nextMostRecentPosts as $recentPost) {
              ?>
              <div class="col-md-5 col-12 nls_course_nav no-hor-padding blog_archive_block">
                <a href="<?php echo get_permalink($recentPost['ID']); ?>">
                  <div class="blog_secondary_container">
                    <div class="blog_secondary_image">
                      <?php
                      echo get_the_post_thumbnail($recentPost['ID'], 'full', array('class' => 'blog_secondary_image'));
                      ?>
                    </div>
                    <div class="blog_secondary_content">
                      <div class="blog_title text-center">
                        <?php
                        echo $recentPost['post_title'];
                        ?>
                      </div>

                      <div class="blog_excerpt text-center">
                        <?php
                        echo $recentPost['post_excerpt'];
                        ?>
                      </div>
                    </div>
                  </div>
                </a>
              </div>
              <?php
              $j++;
            }
            ?>
          </div>
        </div>

        <?php
        /*
         * Get the rest of the most recent posts
         */
        $otherRecentPostsArgs = array(
          /*'numberposts'   => 2,*/
          'offset'        => 3,
          'post_type'     => 'post'
        );

        $otherRecentPosts = wp_get_recent_posts( $otherRecentPostsArgs, ARRAY_A );
        ?>

        <div class="container">
          <div class="row">
            <?php
            foreach($otherRecentPosts as $recentPost) {
            ?>
              <div class="col-md-3 col-12 nls_course_nav no-hor-padding blog_tertiary_container blog_archive_block">
                <div class="">
                  <a href="<?php echo get_permalink($recentPost['ID']); ?>">
                    <div class="blog_tertiary_image">
                      <?php
                      echo get_the_post_thumbnail($recentPost['ID'], 'full', array('class' => 'blog_secondary_image'));
                      ?>
                    </div>
                    <div class="blog_title text-center blog_tertiary_content">
                      <?php echo $recentPost['post_title']; ?>
                    </div>
                  </a>
                </div>
              </div>
            <?php
            }
            ?>
          </div>
        </div>
        <?php /*while ( have_posts() ) : the_post(); ?>

          <?php get_template_part( 'loop-templates/content', 'page' ); ?>

        <?php endwhile;*/ // end of the loop. ?>
      </div>

    </div>

    <div class="col-md-3 col-12">

      <?php
      get_sidebar( 'right' );
      ?>

    </div>

  </div><!-- .row -->

</div><!-- Container end -->

 <?php get_footer(); ?>
