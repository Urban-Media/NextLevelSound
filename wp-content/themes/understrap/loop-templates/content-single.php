<?php
/**
 * Single post partial template.
 *
 * @package understrap
 */

?>
<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<div class="entry-content blog_content_container">

		<?php the_content(); ?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<div class="container section_bottom_block blog_footer_signups">
      <div class="row section_bottom_block_header white_text uppercase">
        <div class="col-12">
          <h2 class="section_coursework text-center">
            3 Ways To Get More
          </h2>
        </div>
      </div>
      <div class="row section_bottom_block_body">
        <div class="col-lg-4 col-12">
          <div class="row">
            <div class="col-12">
              <h2 class="section_subheader text-center">
                Sign Up To The SoundToys Webinar
              </h2>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
							<a href="#" class="sideblock_link">
			          <div class="nls_rounded_button white_button black_text uppercase text-center sideblock_button">
			            Sign Up
			          </div>
			        </a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-12 section_bottom_block_divider">
          <div class="row">
            <div class="col-12">
              <h2 class="section_subheader text-center">
                Sign Up To Our Newsletter
              </h2>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
							<a href="#" class="sideblock_link">
			          <div class="nls_rounded_button white_button black_text uppercase text-center sideblock_button">
			            Sign Up
			          </div>
			        </a>
            </div>
          </div>
        </div>
				<div class="col-lg-4 col-12 section_bottom_block_divider">
          <div class="row">
            <div class="col-12">
              <h2 class="section_subheader text-center">
                Sign Up To Our Newsletter
              </h2>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
							<a href="#" class="sideblock_link">
			          <div class="nls_rounded_button white_button black_text uppercase text-center sideblock_button">
			            Sign Up
			          </div>
			        </a>
            </div>
          </div>
        </div>
      </div>
    </div>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
