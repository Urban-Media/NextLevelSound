<?php
/**
 * The upsell sidebar containing the main widget area.
 *
 * @package understrap
 */

if ( ! is_active_sidebar( 'upsell-sidebar' ) ) {
	return;
}

?>
<ul style="list-style-image: none;">
  <?php dynamic_sidebar( 'upsell-sidebar' ); ?>
</ul>
