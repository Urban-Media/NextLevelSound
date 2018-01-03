<?php
/**
 * My Account Navigation Links
 * @since    2.?.?
 * @version 3.10.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$sep = apply_filters( 'lifterlms_my_account_navigation_link_separator', '&bull;' );
$current = LLMS_Student_Dashboard::get_current_tab( 'slug' );

$current_user = wp_get_current_user();
if ( !($current_user instanceof WP_User) ) return;

$ignoreArray = array('view-achievements', 'redeem-voucher');
?>
<!-- Dashboard User Overview -->

<div class="container">
	<div class="row">
		<div class="col-12 nls_dashboard">
			<div class="row">
				<div class="col-3">
					<h2 class="nls_dashboard_user">
						<?php echo $current_user->user_login; ?>
					</h2>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- End dashboard user overview -->

<nav class="llms-sd-nav">

	<?php do_action( 'lifterlms_before_my_account_navigation' ); ?>

	<ul class="llms-sd-items nls_dashboard_nav uppercase">
		<?php foreach ( LLMS_Student_Dashboard::get_tabs() as $var => $data ) : ?>
			<?php if (!in_array($var, $ignoreArray)) { ?>
				<li class="llms-sd-item nls_dashboard_nav_option <?php printf( '%1$s %2$s', $var, ( $var === $current ) ? ' current nls_dashboard_current' : '' ); ?>">
					<a class="llms-sd-link" href="<?php echo isset( $data['url'] ) ? $data['url'] : llms_get_endpoint_url( $var, '', llms_get_page_url( 'myaccount' ) ); ?>"><?php echo $data['title']; ?></a>
				</li>
			<?php } ?>
		<?php endforeach; ?>
	</ul>

	<?php do_action( 'lifterlms_after_my_account_navigation' ); ?>

</nav>
