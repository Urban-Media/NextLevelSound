<?php
/**
 * Template Name: Compare Courses
 *
 * Template for displaying course comparison page
 *
 */

get_header();
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="course_comparison_wrapper">
        <table class="course_comparison nls_course_nav">
          <thead>
            <tr>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border"></th>
              <th class="comparison_no_border comparison_best_value_header text-center white_text uppercase">
                Best Value
              </th>
            </tr>
            <tr>
              <th></th>
              <th>
                Music Production
              </th>
              <th>
                Music Composition
              </th>
              <th>
                Mixing & Mastering
              </th>
              <th>
                Music Promotion
              </th>
              <th>
                The Essential Bundle
              </th>
              <th class="comparison_best_value">
                The Ultimate Bundle
              </th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (have_rows('section_row')) {
              while (have_rows('section_row')) { the_row();
                ?>
                <tr>
                  <td>
                    <?php the_sub_field('row_name'); ?>
                  </td>
                  <td class="text-center">
                    <?php if (get_sub_field('music_production')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                  <td class="text-center">
                    <?php if (get_sub_field('music_composition')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                  <td class="text-center">
                    <?php if (get_sub_field('mixing_mastering')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                  <td class="text-center">
                    <?php if (get_sub_field('music_promotion')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                  <td class="text-center">
                    <?php if (get_sub_field('the_essential_bundle')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                  <td class="text-center comparison_best_value">
                    <?php if (get_sub_field('the_ultimate_bundle')) { ?>
                      <img class="comparison_check_mark" src="<?php echo get_stylesheet_directory_uri(); ?>/img/check.png">
                    <?php } ?>
                  </td>
                </tr>
                <?php
              }
            }
            ?>
          </tbody>
          <tfoot>
            <tr>
              <td></td>
              <td>
                <div class="comparison_sign_up comparison_sign_up_regular text-center">
                  <a href="#" class="white_text uppercase">
                    Sign Up
                  </a>
                </div>
              </td>
              <td>
                <div class="comparison_sign_up comparison_sign_up_regular text-center">
                  <a href="#" class="white_text uppercase">
                    Sign Up
                  </a>
                </div>
              </td>
              <td>
                <div class="comparison_sign_up comparison_sign_up_regular text-center">
                  <a href="#" class="white_text uppercase">
                    Sign Up
                  </a>
                </div>
              </td>
              <td>
                <div class="comparison_sign_up comparison_sign_up_regular text-center">
                  <a href="#" class="white_text uppercase">
                    Sign Up
                  </a>
                </div>
              </td>
              <td>
                <div class="comparison_sign_up comparison_sign_up_regular text-center">
                  <a href="#" class="white_text uppercase">
                    Sign Up
                  </a>
                </div>
              </td>
              <td class="comparison_best_value">
                <div class="comparison_sign_up comparison_sign_up_best_value text-center">
                  <a href="#" class="white_text uppercase text-center">
                    Sign Up
                  </a>
                </div>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
