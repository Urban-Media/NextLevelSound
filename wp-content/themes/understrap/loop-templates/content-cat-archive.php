<?php
/**
 * Partial template for blog category content
 *
 * @package understrap
 */

?>
<div class="col-md-3 col-12">
  <div class="nls_course_nav no-hor-padding blog_tertiary_container blog_archive_block" data-mh="blogPost">
    <a href="<?php echo get_permalink(get_the_ID()); ?>">
      <div class="blog_tertiary_image">
        <?php
        echo get_the_post_thumbnail(get_the_ID(), 'blog_secondary_image', array('class' => 'blog_secondary_image', 'data-mh' => 'blogThumbnail')); //echo get_the_post_thumbnail($recentPost['ID'], 'full', array('class' => 'blog_secondary_image'));
        ?>
      </div>
      <div class="blog_title_tertiary text-center blog_tertiary_content">
        <?php the_title(); //echo $recentPost['post_title']; ?>
      </div>
    </a>
  </div>
</div>
