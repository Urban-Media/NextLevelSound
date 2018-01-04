<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package understrap
 */

$container = get_theme_mod( 'understrap_container_type' );

/*
 * The height of the homepage header banner can change depending on
 * which page it's displayed on
 */
/*$heroBackgroundImageClass = (is_page(79)) ? "hero_header_home" : "hero_header";*/
if (is_page(79)) {
	$heroBackgroundImageClass = "hero_header_home";
} elseif (is_page_template('page-templates/course-overview.php') || (get_post_type() == 'post')) {
	$heroBackgroundImageClass = "hero_header_course_overview";
} else {
	$heroBackgroundImageClass = "hero_header";
}


/*
 * Get hero header background image - for the most part this is an ACF field
 * that falls back to a default if not specified.
 *
 * However, individual blog images may use the 'featured image' field instead
 */
$heroBackgroundImage = get_field('background_image');

if (strlen($heroBackgroundImage['url']) < 1) {
	// No ACF image specified - is it an individual blog post?
	if (get_post_type() == 'post' && (get_the_post_thumbnail())) {
		$heroBackgroundImage['url'] = get_the_post_thumbnail_url();
	} else {
		$heroBackgroundImage['url'] = WP_CONTENT_URL . '/uploads/2017/12/header_home.jpg';
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<link href="https://fonts.googleapis.com/css?family=Alegreya|Raleway:400,600,700" rel="stylesheet">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div class="hfeed site" id="page">

	<div class="container-fluid no-hor-padding site_header_block">
		<?php
		if (!is_lesson()) { ?>
		<div class="row">
			<div class="col-12">
				<div class="header-strap">
					<span class="header-strap">
						Next Level Sound, the new home of <img src="<?php echo get_template_directory_uri(); ?>/img/mixmasterwyatt_logo.png" alt="Mix Master Wyatt Online Next Level Music School" title="Mix Master Wyatt Online Next Level Music School">
					</span>
				</div>
			</div>
		</div>
		<?php
		}
		?>

		<div class="row">
			<div class="col-12">
				<div class="hero_background <?php echo $heroBackgroundImageClass; ?>" style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1)), url('<?php echo $heroBackgroundImage['url']; ?>');">
					<div class="hero_background_overlay">

					<div class="container menu_container">
						<div class="row">
							<div class="col-lg-3 col-12">
								<a href="<?php echo home_url(); ?>">
									<img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" class="nls_logo" alt="<?php echo get_bloginfo('name'); ?>" title="<?php echo get_bloginfo('name'); ?>">
								</a>
							</div>

							<!--<div class="col-lg-3 hidden-xs-down"></div>-->

							<div class="col-lg-8 offset-lg-1 col-12 header_menu_container">
								<div class="header_menu_container">
									<?php wp_nav_menu(
										array(
											'theme_location'  => 'header-menu',
											//'container_class' => 'collapse navbar-collapse',
											'container_id'    => '',
											'container' 			=> false,
											'menu_class'      => 'nav header_menu text-right',
											'fallback_cb'     => '',
											'menu_id'         => 'header-menu',
											'walker'          => new Header_Menu_Navwalker(),
										)
									); ?>
								</div>
							</div>
						</div>
					</div>

					<div class="container">
						<div class="row">
							<div class="col-lg-7 col-12">

								<?php
								$subTitleClass = "subpage_header_title";
								if (is_page(79)) {
									$subTitleClass = "homepage_header_title";
								} elseif (get_post_type() == 'post') {
									$subTitleClass = "blog_header_title";
								}
								/*
								 * This section only shows if you are within a lesson
								 */
								if (is_lesson()) {
									// All this to get the section title...there must be a better way
									$subTitleClass = "lesson_header_title";
									$lesson = llms_get_post( $post->ID );
									$course_id = $lesson->get( 'parent_course' );
									$course = new LLMS_Course( $course_id );
									$student = new LLMS_Student();
									$next_lesson = llms_get_post( $student->get_next_lesson( $course->get( 'id' ) ) );
									$section = llms_get_post( $next_lesson->get( 'parent_section' ) );
								?>
									<div class="hero_header_course_titles">
										<a href="<?php echo get_permalink($course->get( 'id' )); ?>">
											<?php echo do_shortcode('[lifterlms_course_title]'); ?>
										</a>
										 :
										<a href="<?php echo get_permalink($section->get( 'id' )); ?>">
											<?php echo $section->title; ?>
										</a>
									</div>
								<?php
							} else if (get_post_type() == "section") {
								// All this to get the section title...there must be a better way
								$lesson = llms_get_post( $post->ID );
								$course_id = $lesson->get( 'parent_course' );
								$course = new LLMS_Course( $course_id );
								$student = new LLMS_Student();
								$next_lesson = llms_get_post( $student->get_next_lesson( $course->get( 'id' ) ) );
								$section = llms_get_post( $next_lesson->get( 'parent_section' ) );
							?>
								<div class="hero_header_course_titles">
									<a href="<?php echo get_permalink($course->get( 'id' )); ?>">
										<?php echo get_the_title($course->get( 'id' )); ?>
									</a>
								</div>
							<?php
							}
								?>

								<div class="header_title <?php echo $subTitleClass; ?>">
									<?php
									/*
									 * By default use the ACF custom 'title' field but fall back
									 * to the post_title() if not present
									 */
									 $customTitle = get_field('title');

									 if (is_post_type_archive('course')) {
										 echo "Course Library";
									 } else if (is_page_template('page-templates/course-overview.php')) {
										 // There should be an ACF field on course overview pages
										 the_field('overview_title');
									 } else if (is_archive()) {
										 the_archive_title();
									 } else {
										 echo (strlen($customTitle) > 0) ? $customTitle : the_title();
									 }
									 ?>
								</div>
							</div>

							<div class="col-lg-5 col-12 <?php if (is_page_template('page-templates/course-overview.php')) echo 'mentor_banner_image_container'; ?>">
								<?php
								/*
								 * Course overview pages have an image of the course mentor here
								 */
								if (is_page_template('page-templates/course-overview.php')) {
									$mentorBannerImage = get_field('banner_image');
									//var_dump($mentorBannerImage);
									echo "<img src='" . $mentorBannerImage['sizes']['mentor_header_banner'] . "' class='mentor_banner_image' alt='" . $mentorBannerImage['alt'] . "' title='" . $mentorBannerImage['title'] . "'>";
								}
								?>
							</div>
						</div>
					</div>

					<?php
					/*
					 * This section only appears on the home page
					 */
					if (is_front_page() && is_plugin_active('lifterlms/lifterlms.php')) {
						?>
						<div class="container reasons_container">
							<div class="row">
								<?php
								if (have_rows('reasons')) {
									while (have_rows('reasons')) { the_row();
									?>
									<div class="col-md-4 col-12">
										<div class="reasons_block">
											<div class="reasons_icon">
												<?php
												$reasonIcon = get_sub_field('reasons_icon')
												?>
												<img src="<?php echo $reasonIcon['url']; ?>" alt="<?php echo $reasonIcon['alt']; ?>" title="<?php $reasonIcon['title']; ?>">
											</div>
											<div class="reasons_text white_text">
												<?php the_sub_field('reasons_text'); ?>
											</div>
										</div>
									</div>
									<?php
									}
								}
								?>
							</div>
						</div>
						<?php
					}
					/*
					 * End frontpage only section
					 */
					?>
				</div>
				</div>
			</div>
		</div>
	</div>
</div>
