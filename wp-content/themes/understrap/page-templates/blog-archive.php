<?php
/**
 * Template Name: Blog Archive
 *
 * Template for displaying an archive of all blog posts
 *
 */
global $post;
get_header();

//$the_query = new WP_Query( 'paged=' . $paged );
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
             * First display all categories here - this is a fixed list
             */
            $catArgs = array(
              'include' => array(17,18,19,20)
            );
            $categories = get_categories($catArgs);
            $catTotal = count($categories);

            // Categories are not arranged in the desired order by themselves
            // and Wordpress orderby support is limited, so rearrange order here
            $keysDesiredOrder = array(2,1,0,3);
            $catsRearranged = array_combine( $keysDesiredOrder, $categories );
            ksort($catsRearranged);

            $i = 1;
            foreach($catsRearranged as $category) {
              $thumbnailProperties = new stdClass();
              $thumbnailProperties = json_decode($category->term_thumbnail);
              //var_dump($thumbnailProperties);
              ?>
              <div class="col-md-3 col-12">
                <div class="nls_course_nav no-hor-padding blog_archive_block">
                  <a href="<?php echo get_category_link($category->cat_ID); ?>" class="no_hover_underline">
                    <?php
                    if (is_object($thumbnailProperties)) { ?>
                    <img src="<?php echo $thumbnailProperties->sizes->thumbnail->url; ?>" alt="<?php echo $thumbnailProperties->alt; ?>" title="<?php echo $thumbnailProperties->title; ?>" style="border-top-left-radius: 10px;
          border-top-right-radius: 10px; width:100% !important;" data-mh="category_thumbnails">
                    <?php } ?>
                    <span class="list_category_name uppercase text-center">
                      <?php echo $category->name; ?>
                    </span>
                  </a>
                </div>
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
          'posts_per_page'           => 1,
          'posts_per_archive_page'   => 1,
          'post_type'                => 'post',
          'post_status'              => 'publish',
          'paged'                    => get_query_var('page')
        );

        //$mostRecentPost = wp_get_recent_posts( $mostRecentPostArgs, ARRAY_A );
        $mostRecentPost = new WP_Query($mostRecentPostArgs);

        if ($mostRecentPost->have_posts()) {
          while ($mostRecentPost->have_posts()) {
            $mostRecentPost->the_post();
            ?>
            <div class="blog_primary_featured_post nls_course_nav blog_archive_block">
              <a href="<?php echo get_permalink(get_the_ID()); //echo get_permalink($mostRecentPost[0]['ID']); ?>" class="no_hover_underline">
                <div class="container-fluid">
                  <div class="row">
                    <div class="col-md-7 col-12" style="padding-left: 0px !important; padding-right: 0px !important;">
                      <?php
                      echo get_the_post_thumbnail(get_the_ID(), 'primary_blog_post_thumbnail', array('class' => 'blog_primary_image')); //echo get_the_post_thumbnail($mostRecentPost[0]['ID'], 'primary_blog_post_thumbnail', array('class' => 'blog_primary_image'));
                      ?>
                    </div>
                    <div class="col-md-5 col-12" style="padding-left: 0px !important; padding-right: 0px !important;">
                      <div class="blog_primary_content">
                        <div class="blog_title text-center">
                          <?php
                          the_title(); //echo $mostRecentPost[0]['post_title'];
                          ?>
                        </div>

                        <div class="blog_excerpt text-center">
                          <?php
                          //the_excerpt(); //echo $mostRecentPost[0]['post_excerpt'];
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div>
        <?php
          }
        }


        /*
         * Get the next two most recent posts
         */
        /*$nextMostRecentPostArgs = array(
          'numberposts'   => 2,
          'offset'        => 1,
          'post_type'     => 'post',
          'paged'         => $paged
        );*/
        $nextMostRecentPostArgs = array(
          'offset'                   => 1,
          'posts_per_page'           => 2,
          'posts_per_archive_page'   => 2,
          'post_type'                => 'post',
          'post_status'              => 'publish',
          'paged'                    => get_query_var('page')
        );

        //$nextMostRecentPosts = wp_get_recent_posts( $nextMostRecentPostArgs, ARRAY_A );
        $nextMostRecentPosts = new WP_Query($nextMostRecentPostArgs);
        ?>

        <div class="container-fluid blog_secondary_posts no-hor-padding">
          <div class="row">
            <?php
            if ($nextMostRecentPosts->have_posts()) {
              while ($nextMostRecentPosts->have_posts()) {
                $nextMostRecentPosts->the_post();
                ?>
                <div class="col-md-6 col-12">
                  <div class="nls_course_nav blog_archive_block" data-mh="blogSecondaryContainer">
                    <a href="<?php echo get_permalink(get_the_ID()); ?>" class="no_hover_underline">
                      <div class="blog_secondary_container">
                        <div class="blog_secondary_image">
                          <?php
                          echo get_the_post_thumbnail(get_the_ID(), 'full', array('class' => 'blog_secondary_image', 'data-mh' => 'blogSecondaryThumbnail')); //echo get_the_post_thumbnail($recentPost['ID'], 'full', array('class' => 'blog_secondary_image'));
                          ?>
                        </div>
                        <div class="blog_secondary_content">
                          <div class="blog_title text-center">
                            <?php
                            the_title(); //echo $recentPost['post_title'];
                            ?>
                          </div>

                          <div class="blog_excerpt text-center">
                            <?php
                            //the_excerpt(); //echo $recentPost['post_excerpt'];
                            ?>
                          </div>
                        </div>
                      </div>
                    </a>
                  </div>
                </div>
                <?php
              }
            }
            ?>
          </div>
        </div>

        <?php
        /*
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
        */
        ?>

        <?php
        /*
         * Get the rest of the most recent posts
         */
        /*$otherRecentPostsArgs = array(
          //'numberposts'   => 2,
          'offset'        => 3,
          'post_type'     => 'post',
          'paged'         => $paged
        );*/
        $otherRecentPostsArgs = array(
          'offset'                   => 3,
          'posts_per_page'           => 6,
          'posts_per_archive_page'   => 6,
          'post_type'                => 'post',
          'post_status'              => 'publish',
          'paged'                    => get_query_var('page')
        );


        $otherRecentPosts = new WP_Query($otherRecentPostsArgs); //$otherRecentPosts = wp_get_recent_posts( $otherRecentPostsArgs, ARRAY_A );
        ?>

        <div class="container  no-hor-padding">
          <div class="row">
            <?php
            //foreach($otherRecentPosts as $recentPost) {
            $i = 0;
            if ($otherRecentPosts->have_posts()) {
              while ($otherRecentPosts->have_posts()) {
                $otherRecentPosts->the_post();
                $offset = "";
                /*if ($i % 3 != 0) {
                  $offset = "offset-md-1";
                } else if ($i % 2 == 0) {
                  //$offset = "offset-md-2";
                }*/


                ?>
                <div class="col-md-4 col-12">
                  <div class="nls_course_nav no-hor-padding blog_tertiary_container blog_archive_block" data-mh="blogTertiaryContainer">
                    <a href="<?php echo get_permalink(get_the_ID()); ?>" class="no_hover_underline">
                      <div class="blog_tertiary_image">
                        <?php
                        echo get_the_post_thumbnail(get_the_ID(), 'full', array('class' => 'blog_secondary_image', 'data-mh' => "blogTertiaryThumbnail")); //echo get_the_post_thumbnail($recentPost['ID'], 'full', array('class' => 'blog_secondary_image'));
                        ?>
                      </div>
                      <div class="blog_title_tertiary text-center blog_tertiary_content">
                        <?php the_title(); //echo $recentPost['post_title']; ?>
                      </div>
                    </a>
                  </div>
                </div>
                <?php
                $i++;
              }
            }
            ?>
          </div>
        </div>

        <?php /*
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
        */ ?>


      </div>

      <div class="get_more_posts">
        <?php
        //next_posts_link( 'More' , $otherRecentPosts->max_num_pages);
        ?>
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
