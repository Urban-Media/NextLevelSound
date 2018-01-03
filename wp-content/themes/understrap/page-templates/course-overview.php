<?php
/**
 * Template Name: Course Overview
 *
 * Template for displaying course overview pages
 *
 */

get_header();
?>

<!-- Block 1 Start -->

<div class="container-fluid" >
	<div class="row teal_block">
		<div class="col-12" style="padding-top: 30px; padding-bottom: 30px;">

						<div class="white_text uppercase title text-center ready_next_level">
							I'm ready to go to the next level! &nbsp; &nbsp; &nbsp;
							<a href="<?php echo get_post_type_archive_link('course'); ?>" class="sideblock_link">
								<span class="nls_rounded_button white_button teal_text uppercase text-center sideblock_button study_options" style="padding-left: 20px; padding-right: 20px;">
									View Study Options
								</span>
							</a>
						</div>

		</div>
	</div>
</div>

<!-- Block 1 End -->

<!-- Block 2 Start -->

<div class="container-fluid no_hor_padding nls_page_content">

  <div class="container">
    <div class="row">
      <div class="col-12 text-center">
        <div class="section_subtitle uppercase">
          Course Overview
        </div>
        <div class="section_title course_module_title">
          <?php the_field('overview_title'); ?>
        </div>
      </div>
    </div>

    <div class="row" style="padding-top: 30px;">
      <div class="col-md-6 col-12">
        <?php the_field('overview_content'); ?>
      </div>

      <div class="col-md-6 col-12">
        <div class="section_subtitle uppercase">
          The course is for you if:
        </div>

        <ul class="success_reasons">
          <?php
          if (have_rows('overview_reasons')) {
            while (have_rows('overview_reasons')) { the_row();
              ?>
              <li class="success_reason">
                <?php the_sub_field('overview_reason'); ?>
              </li>
              <?php
            }
          }
          ?>
        </ul>
      </div>
    </div>
  </div>

</div>

<!-- Block 2 End -->

<!-- Block 3 Start -->

<div class="container-fluid">
  <div class="row course_contents_banner">
    <div class="col-12">
      <div class="uppercase white_text text-center">
        Course Contents
      </div>
      <h2 class="section_title white_text text-center">
        <?php
        // Check how many modules there are
        $moduleCount = count( get_field( 'modules' ) );
        ?>
        The Course is Divided Into <?php echo $moduleCount; ?> Modules
      </h2>
    </div>
  </div>
</div>

<!-- Block 3 End -->

<!-- Block 4 Start -->

<?php
/*
 * This is a repeating block for each module in the course
 */
$i = 1;
if (have_rows('modules')) {
  while (have_rows('modules')) { the_row();
    $moduleNumber = $i;
    ?>

    <div class="container-fluid course_module">
      <div class="row">
        <div class="col-12">
          <div class="course_contents_number_circle course_contents_circle_position">
            <h3 class="course_contents_number white_text">
              <?php echo $moduleNumber; ?>
            </h3>
          </div>
        </div>
      </div>
      <div class="row text-center course_module_title_container">
        <div class="col-md-6 offset-md-3 col-12">
          <div class="section_title course_module_title">
            <?php the_sub_field('module_title'); ?>
          </div>
          <div class="section_content grey_section_content course_module_content">
            <?php the_sub_field('module_brief'); ?>
          </div>
        </div>
      </div>
      <div class="row text-center">
        <div class="col-12">
          <div class="section_subtitle uppercase course_success_subtitle">
            At the end of <?php the_sub_field('module_title'); ?> you'll be able to:
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-10 offset-md-1 col-12">
          <ul class="course_module_goals success_reasons d-flex flex-row flex-wrap">
            <?php
            if (have_rows('end_goals')) {
              while (have_rows('end_goals')) { the_row();
                ?>
                <li class="course_module_goal success_reason  w-50">
                  <?php the_sub_field('goal'); ?>
                </li>
                <?php
              }
            }
            ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-12 course_module_bottom">
        </div>
      </div>
    </div>

    <?php
    $i++;
  }
}
?>

<!-- Block 4 End -->

<!-- Block 5 Start -->

<?php
$mentorImage = get_field('mentor_image');
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12 course_your_mentor_background" style="background-color: <?php the_field('mentor_background_colour'); ?>; background-image: url('<?php echo $mentorImage['url']; ?>');">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-12 col-lg-5 course_your_mentor_text_container"> <!--offset-lg-1-->
            <div class="section_title course_module_title course_your_mentor_title">
              Your Mentor
            </div>
            <div class="sectgion_content course_your_mentor_text">
              <?php the_field('mentor_text'); ?>
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
										<div class="col-md-4 col-12 text-left">
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

<div class="container-fluid" id="studyOptions">
	<div class="row grey_block">
		<div class="col-12">
      <div class="container frontpage_block study_options_block">

        <div class="row frontpage_courses bio_row">
          <div class="col-md-6 offset-md-3 col-12 text-center">
						<div class="section_subtitle uppercase grey_text">
							Your Study Options
						</div>
						<div class="section_title course_module_title">
							Two Ways To Study
						</div>
            <div class="section_content grey_section_content course_module_content">
              <?php the_field('study_blurb'); ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="container-fluid">
  <div class="row frontpage" style="padding-bottom: 0px; background-size: contain; background-image: url('<?php echo get_template_directory_uri(); ?>/img/study_options_bg.png');">

		<div class="container">
			<div class="row">
				<div class="col-12 col-md-10 mr-auto ml-auto">
					<div class="row">
					<!-- Study options block -->
					<div class="col-12 col-md-4 col-lg-4 mr-auto ml-auto text-center" data-mh="studyOptions">
						<div class="buy_courses_options nls_course_nav ways_to_study_block" data-mh="courses">

			        <div class="section_subtitle uppercase course_success_subtitle course_study_options_title white_text">
			          Course Features
			        </div>

			        <div class="course_study_list">
			          <ul class="course_study_options">
			            <?php
			            if (have_rows('live_lessons_points')) {
			              while (have_rows('live_lessons_points')) { the_row();
			                ?>
			                <li class="course_study_option">
			                  <?php the_sub_field('live_lesson_point'); ?>
			                </li>
			                <?php
			              }
			            }
			            ?>
			          </ul>
			        </div>

							<div class="course_study_options_footer uppercase">
								Bonus - Now with lifetime access to course updates
							</div>

			      </div>
					</div>

					<div class="col-12 col-md-8 col-lg-8 mr-auto ml-auto text-center" data-mh="studyOptions">
						<div class="buy_courses_options nls_course_nav nls_course_catalogue_loop" data-mh="courses">

							<div class="section_subtitle uppercase ways_to_pay">
								Different Ways To Pay
							</div>

							<div class="nls_payment_options">
								<div class="container">
									<div class="row">
										<div class="col-5">
											<div class="one_payment" data-mh="paymentOptions">
												$1,950
												<span class="one_payment_subtitle uppercase">
													One Payment
												</span>
											</div>
										</div>
										<div class="col-2">
											<div class="ways_to_pay_or text-center">
												OR
											</div>
										</div>
										<div class="col-5">
											<div class="multi_payment" data-mh="paymentOptions">
												<div class="multi_payment_subtitle uppercase">
													Pay in installments
												</div>

												<div class="multi_payment_options">
													<select class="payment_options_select">
														<option>3 Months - $650</option>
														<option>6 Months - $325</option>
													</select>
												</div>
											</div>
										</div>
									</div>

									<div class="row">
										<?php $atts = array(); ?>
										<?php echo do_shortcode('[lifterlms_checkout cols="2"]'); ?>
									</div>

								</div>
							</div>

						</div>
					</div>
					<!-- End study options block -->
				</div>

				<div class="row" style="padding-top: 50px;">
					<div class="col-12">
			      <div class="container">
			        <div class="row study_block">
			          <div class="col-12">
			            <div class="study_block_title">
			              *Graduates Club
			            </div>
			            <div class="graduates_content study_block_content">
			              <?php the_field('graduates_club_content'); ?>
			            </div>
									<div class="study_block_title">
			              **Additional Masterclasses
			            </div>
			            <div class="masterclasses_content study_block_content">
			              <?php the_field('additional_masterclasses_content'); ?>
			            </div>
			          </div>
			        </div>
			      </div>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Block 7 End -->

<!-- Block 8 Start -->

<div class="container-fluid frontpage_image_faded_bg" style="background-position: bottom; background-image: url('<?php echo get_template_directory_uri(); ?>/img/questions_bg.jpg');">

	<div class="row">

		<div class="col-12">

      <div class="container frontpage_block">

        <div class="row frontpage_courses study_block not_ready_yet_block">
          <div class="col-md-7 col-12">
            <div class="section_title course_module_title">
              <?php the_field('sign_up_title');?>
            </div>
            <div class="sign_up_content">
              <?php the_field('sign_up_content'); ?>
            </div>
          </div>
          <div class="col-md-5 col-12">
            <a href="#" class="sideblock_link">
              <div class="nls_rounded_button teal_button white_text uppercase text-center bottom_shadow not_ready_yet_button ">
                Sign Me Up
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Block 8 End -->

<?php get_footer(); ?>
