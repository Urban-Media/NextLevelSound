<?php
/**
 * The template for displaying the front page
 *
 *
 * @package understrap
 */

get_header();
?>
<!-- Block 1 Start -->
<div class="container-fluid" id="our_courses">

	<div class="row frontpage_image_faded_bg" style="margin-top: 50px; margin-bottom: 50px;background-image: url('<?php echo get_template_directory_uri(); ?>/img/our_courses_bg.png');">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses">
          <div class="col-md-6 offset-md-3 col-12 text-center">
            <div class="section_title uppercase">
              <?php the_field('our_courses_title'); ?>
            </div>
            <div class="section_content grey_section_content">
              <?php the_field('our_courses_content'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <div class="row frontpage white_block">
		<div class="col-12">
			<ul class="list-inline text-center">
		    <?php
				/*
				 * Get list of courses to display on the front page
				 *
				 */
				if (is_plugin_active('lifterlms/lifterlms.php')) {
					if(have_rows('course_select')) {
						while(have_rows('course_select')) { the_row();
							$course = get_sub_field('course');
							?>
							<li class="list-inline-item text-center course_list_item" data-mh="ourCourses">
					        <?php
					        $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( $course ), 'thumbnail' );
					        ?>
					        <div class="nls_course_catalogue_image_spacer">
					          <div class="nls_course_catalogue_image" style="background-image: url('<?php echo $featuredImage[0]; ?>');"></div>
					        </div>

					      	<h4 class="nls_course_catalogue_title uppercase text-center" data-mh="course_title"><?php echo get_the_title($course); ?></h4>

					        <a href="<?php the_sub_field('overview_page'); ?>" class="sideblock_link">
					          <div class="nls_rounded_button teal_button white_text uppercase text-center sideblock_button" style="padding-left: 20px; padding-right: 20px;">
					            Find out more
					          </div>
					        </a>
					      </li>
							<?php
						}
					}
				}
				?>
			</ul>
		</div>
  </div>
</div>
<!-- Block 1 End -->


<!-- Block 2 Start -->

<div class="container-fluid" id="course_icons">
<div class="row white_block">
	<div class="col-12">
    <div class="container frontpage_block course_icons_section">
      <div class="row frontpage_courses">
        <div class="col-12 text-center">
					<div class="section_subtitle uppercase grey_text">
						<?php the_field('icons_tagline'); ?>
					</div>
				</div>
			</div>

			<div class="row">
          <div class="col-12 section_content">
						<ul class="icon_images list-inline">
              <?php
							if (is_plugin_active('lifterlms/lifterlms.php')) {
								if(have_rows('icons')) {
									while(have_rows('icons')) { the_row();
										$icon = get_sub_field('icon_image');
										echo '<li class="icons_image list-inline-item"><img src="'. $icon['url'] .'" alt="'. $icon['alt'] .'" title="'. $icon['title'] .'"></li>';
									}
								}
							}
							?>
						</ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Block 2 End -->

<!-- Block 3 Start -->

<div class="container-fluid" id="available_courses">
	<div class="row greyer_block">
		<div class="col-12">

      <div class="container frontpage_block">
        <div class="row frontpage_courses course_success_bg">
          <div class="col-md-6 col-12">

						<div class="section_title">
            	<?php the_field('ss_title'); ?>
						</div>

						<div class="section_content">
							<ul class="success_reasons">
								<?php
								if (have_rows('success_list')) {
									while(have_rows('success_list')) { the_row();
										?>
										<li class="success_reason">
											<?php the_sub_field('reason'); ?>
										</li>
										<?php
									}
								}
								?>
							</ul>
						</div>

          </div>

					<div class="col-md-6 col-12 text-center">
						<?php
						$successImage = get_field('ss_image');
						echo '<img class="success_image" src="' . $successImage['url'] . '" alt="' . $successImage['alt'] . '" title="' . $successImage['title'] . '">'
						?>
					</div>

        </div>

				<div class="row course_icons_section">
					<div class="col-12 course_success_hr">
						<!--<hr class="course_success_hr">-->
					</div>
					<?php
					for($i = 1; $i < 4; $i++) {
						?>
						<div class="col-md-4 col-12 course_success_block">
							<div class="section_subtitle uppercase course_success_subtitle">
								<?php the_field('footer_block_'.$i.'_title'); ?>
							</div>
							<div class="section_content course_success_content">
								<?php the_field('footer_block_'.$i.'_content'); ?>
							</div>
						</div>
						<?php
					}
					?>
				</div>

      </div>

    </div>
  </div>
</div>

<!-- Block 3 End -->

<!-- Block 4 Start -->

<div class="container-fluid" >
	<div class="row teal_block">
		<div class="col-12" style="padding-top: 30px; padding-bottom: 30px;">

			<div class="white_text uppercase title text-center ready_next_level">
				I'm ready to go to the next level! &nbsp; &nbsp; &nbsp;
				<a href="<?php echo get_post_type_archive_link('course'); ?>" class="sideblock_link">
					<span class="nls_rounded_button white_button teal_text uppercase text-center sideblock_button" style="padding-left: 20px; padding-right: 20px;">
						View Courses
					</span>
				</a>
			</div>

		</div>
	</div>
</div>

<!-- Block 4 End -->

<!-- Block 5 Start -->

<?php
$mentorsBackgroundImage = get_field('bio_background_image');
?>

<div class="container-fluid" id="biographies" style="background-image: url('<?php echo $mentorsBackgroundImage['url']; ?>');">

	<div class="row">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses">
          <div class="col-12">
						<div class="row bio_row">
							<div class="col-12 text-center">
		            <div class="section_subtitle uppercase white_text">
		              Biographies
		            </div>
		            <div class="section_title white_text">
		              <?php the_field('bio_title'); ?>
		            </div>
							</div>
						</div>
						<div class="row">
							<?php
							if (is_plugin_active('lifterlms/lifterlms.php')) {
								if (have_rows('biographies')) {
									while(have_rows('biographies')) { the_row();
										$bioImage = get_sub_field('bio_image');
										?>
										<div class="col-md-6 col-12 text-left" data-mh="bios">
											<div class="bio_mentor_data">
												<span class="bio_image">
													<img src="<?php echo $bioImage['sizes']['mentors_thumbnail']; ?>" class="mentor_circle" alt="<?php echo $bioImage['alt']; ?>" title="<?php echo $bioImage['title']; ?>">
												</span>
												<span class="bio_data">
													<span class="bio_name white_text uppercase">
														<?php the_sub_field('bio_name'); ?>
													</span>
													<br />
													<span class="bio_speciality grey_bio_text uppercase">
														<?php the_sub_field('bio_speciality'); ?>
													</span>
												</span>
											</div>
											<div class="bio_mentor_biography grey_bio_text">
												<?php the_sub_field('bio_bio'); ?>
											</div>
										</div>
										<?php
									}
								}
							}
							?>
						</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Block 5 End -->

<!-- Block 6 Start -->

<div class="container-fluid" id="testimonials">

	<div class="row testimonials_bg">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses bio_row">
          <div class="col-md-6 offset-md-3 col-12 text-center">
						<div class="section_subtitle uppercase grey_text">
							Testimonials
						</div>
						<div class="section_title">
							<?php the_field('testimonial_title'); ?>
						</div>
          </div>
        </div>

				<div class="row">

					<div class="container">
						<div class="row frontpage testimonials_bg testimonials_content">
							<?php
							if (is_plugin_active('lifterlms/lifterlms.php')) {
								if(have_rows('testimonials')) {
									while(have_rows('testimonials')) { the_row();
										$student = array(
											'image'				=> get_sub_field('testimonial_image'),
											'name'				=> get_sub_field('testimonial_name'),
											'testimonial'	=> get_sub_field('testimonial_testimonial')
										);
										?>
										<div class="col-md-4 col-12 text-left" data-mh="testimonials">
											<div class="bio_mentor_data">
												<span class="bio_image">
													<img src="<?php echo $student['image']['sizes']['mentors_thumbnail']; ?>" class="mentor_circle" alt="<?php echo $student['image']['alt']; ?>" title="<?php echo $student['image']['title']; ?>">
												</span>
												<span class="bio_data">
													<span class="bio_name uppercase">
														<?php echo $student['name']; ?>
													</span>
												</span>
											</div>
											<div class="bio_mentor_biography grey_bio_text">
												<?php echo $student['testimonial']; ?>
											</div>
										</div>
										<?php
									}
								}
							}
							?>
							</div>
					</div>

				</div>

      </div>
    </div>
  </div>
</div>

<!-- Block 6 End -->

<!-- Block 7 Start -->
<div class="container-fluid" id="available_courses">

	<div class="row grey_block">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses">
          <div class="col-md-6 offset-md-3 col-12 text-center">
            <div class="nls_course_catalogue_title uppercase ">
              Take a closer look
            </div>
            <div class="section_title uppercase">
              <?php the_field('available_courses_title'); ?>
            </div>
            <div class="section_content grey_section_content">
              <?php the_field('available_courses_content'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="row frontpage" style="background-size: contain; background-image: url('<?php echo get_template_directory_uri(); ?>/img/wave_bg.png');">
    <?php
		/*
		 * Get list of courses to display on the front page
		 *
		 */
		$coursesLoop = array();
		if (is_plugin_active('lifterlms/lifterlms.php')) {
			if(have_rows('available_courses')) {
				while(have_rows('available_courses')) { the_row();
					$course = get_sub_field('course');
					$coursesLoop[] = $course->ID;
				}
			}
		}

		// Add some fallback in case ACF is ever uninstalled for some reason
		if (count($coursesLoop) < 1) {
			$coursesLoop = array(186,111);
		}

		$i = 0;

    foreach ($coursesLoop as $course) {
    ?>

    <!--<div class="col-md-3 <?php if ($i % 2 == 0) { ?>offset-md-2<?php } ?> text-center">-->
		<div class="col-md-3 offset-md-2 text-center">

      <div class="llms-loop-item-content nls_course_nav nls_course_catalogue_loop" style="width: fit-content;" data-mh="courses">
        <?php
        $featuredImage = wp_get_attachment_image_src( get_post_thumbnail_id( $course ), 'thumbnail' );
        ?>
        <div class="nls_course_catalogue_image_spacer">
          <div class="nls_course_catalogue_image" style="background-image: url('<?php echo $featuredImage[0]; ?>');"></div>
        </div>

      	<h4 class="nls_course_catalogue_title uppercase text-center"><?php echo get_the_title($course); ?></h4>

        <?php
        if (get_field('course_brief', $course)) {
        ?>
          <div class="nls_course_catalogue_brief text-center" data-mh="course-briefs">
            <?php the_field('course_brief', $course); ?>
          </div>
        <?php
				$i++;
        }
        ?>

        <?php
        $product = new LLMS_Product( $course );
        $accessPlans = $product->get_access_plans(true);
        //var_dump($accessPlans);
        $accessPlan = new LLMS_Access_Plan($accessPlans[0]);
        $checkoutURL = $accessPlan->get_checkout_url();
        ?>

        <a href="<?php echo $checkoutURL; ?>" class="sideblock_link">
          <div class="nls_rounded_button teal_button white_text uppercase text-center sideblock_button">
            Find out more
          </div>
        </a>

        <?php /*echo llms_get_template( 'product/pricing-table.php', array(
    			'product' => new LLMS_Product( $course ),
    		) );*/ ?>

      	<footer class="llms-loop-item-footer">
          <div class="nls_course_catalogue_author">
            Mentor: <?php echo lifterlms_template_loop_author($course); ?>
          </div>
        </footer>
      </div>

      </div>
      <?php
      }
      ?>

    </div>
</div>
<!-- Block 7 End -->

<!-- Block 8 Start -->
<?php
$questionsBackgroundImage = get_field('questions_background_image');
?>

<div class="container-fluid frontpage_image_faded_bg" id="got_questions" style="background-image: url('<?php echo $questionsBackgroundImage['url']; ?>');">

	<div class="row">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses">
          <div class="col-12 text-center white_block nls_course_nav" style="padding: 50px;">
						<div class="row">
							<div class="col-12">
		            <div class="section_title">
		              <?php the_field('questions_title'); ?>
		            </div>
		            <div class="section_content grey_section_content">
		              <?php the_field('questions_tagline'); ?>
		            </div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 col-12 text-center">
								<a href="#" class="sideblock_link">
				          <div class="nls_rounded_button teal_button white_text uppercase text-center sideblock_button" style="padding-left: 20px; padding-right: 20px;">
				            Chat Now
				          </div>
				        </a>
							</div>
							<div class="col-md-6 col-12 text-center">
								<a href="#" class="sideblock_link">
									<div class="nls_rounded_button white_button teal_text teal_border uppercase text-center sideblock_button" style="padding-left: 20px; padding-right: 20px;">
										Send An Enquiry
									</div>
								</a>
							</div>
						</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Block 8 End -->

<?php get_footer(); ?>
