<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package understrap
 */

$the_theme = wp_get_theme();
$container = get_theme_mod( 'understrap_container_type' );
?>

<?php get_sidebar( 'footerfull' ); ?>

<div class="container-fluid nls_footer_container no-hor-padding">

	<div class="row">

		<div class="col-12">

			<footer class="site-footer" id="colophon">

				<div class="container">
					<div class="row">
						<div class="col-md-3 col-12">
							<a href="<?php echo home_url(); ?>">
								<img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="<?php echo get_bloginfo('name'); ?>" title="<?php echo get_bloginfo('name'); ?>">
							</a>

							<div class="social_links_menu">
								<?php wp_nav_menu(
									array(
										'theme_location'  => 'social-links-menu',
										//'container_class' => 'collapse navbar-collapse',
										'container_id'    => '',
										'container' 			=> false,
										'menu_class'      => 'nav',
										'fallback_cb'     => '',
										'menu_id'         => 'social-links-menu',
										'walker'          => new Social_Links_Navwalker(),
									)
								); ?>
							</div>
						</div>

						<div class="col-md-5 hidden-xs-down">

						</div>

						<div class="col-md-2 col-12">
							<h2 class="footer_menu_header">Links</h2>
							<?php wp_nav_menu(
								array(
									'theme_location'  => 'links-menu',
									//'container_class' => 'collapse navbar-collapse',
									'container_id'    => '',
									'container' 			=> false,
									'menu_class'      => 'nav block_menu',
									'fallback_cb'     => '',
									'menu_id'         => 'links-menu',
									'walker'          => new Links_Menu_Navwalker(),
								)
							); ?>
						</div>

						<div class="col-md-2 col-12">
							<h2 class="footer_menu_header">Courses</h2>
							<?php wp_nav_menu(
								array(
									'theme_location'  => 'courses-menu',
									//'container_class' => 'collapse navbar-collapse',
									'container_id'    => '',
									'container' 			=> false,
									'menu_class'      => 'nav block_menu',
									'fallback_cb'     => '',
									'menu_id'         => 'courses-menu',
									//'walker'          => new Header_Menu_Navwalker(),
								)
							); ?>
						</div>
					</div>

					<div class="row">
						<div class="col-12">
							<div class="footer_copyright">
								&copy; <?php echo date('Y'); ?> All rights reserved - Next Level Sound
							</div>
						</div>
					</div>
				</div>

			</footer><!-- #colophon -->

		</div><!--col end -->

	</div><!-- row end -->

</div><!-- container end -->

</div><!-- #page we need this extra closing tag here -->

<?php wp_footer(); ?>

</body>

</html>
