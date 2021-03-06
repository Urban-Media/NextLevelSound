<?php
/**
 * Lesson Progression actions
 * Mark Complete & Mark Incomplete buttons
 * Take Quiz Button when quiz attached
 * @since    1.0.0
 * @version  3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;

$lesson = new LLMS_Lesson( $post );

if ( ! llms_is_user_enrolled( get_current_user_id(), $lesson->get( 'parent_course' ) ) ) {
	return;
}

$student = new LLMS_Student( get_current_user_id() );
$quiz_id = $lesson->get( 'assigned_quiz' );
?>

<div class="clear"></div>
<div class="llms-lesson-button-wrapper <?php if ( $student->is_complete( $lesson->get( 'id' ), 'lesson' ) ) { ?>alert alert-info<?php } ?>">

	<?php if ( $student->is_complete( $lesson->get( 'id' ), 'lesson' ) ) : ?>

		<?php if ( ! $quiz_id ) : ?>

			<i class="fa fa-check-circle"></i>
			<?php echo apply_filters( 'llms_lesson_complete_text', __( 'Lesson Complete', 'lifterlms' ) ); ?>
			<?php do_action( 'llms_after_lesson_complete_text', $lesson ); ?>

			<?php if ( 'yes' === get_option( 'lifterlms_retake_lessons', 'no' ) || apply_filters( 'lifterlms_retake_lesson_' . $lesson->get( 'parent_course' ), false ) ) : ?>

				<form action="" class="llms-incomplete-lesson-form" method="POST" name="mark_incomplete">

					<?php do_action( 'lifterlms_before_mark_incomplete_lesson' ); ?>

					<input type="hidden" name="mark-incomplete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
					<input type="hidden" name="action" value="mark_incomplete" />
					<?php wp_nonce_field( 'mark_incomplete' ); ?>

					<?php llms_form_field( array(
						'columns' => 12,
						'classes' => 'nls_rounded_button teal_button white_text uppercase text-center sideblock_button',
						'id' => 'llms_mark_incomplete',
						'value' => apply_filters( 'lifterlms_mark_lesson_incomplete_button_text', __( 'Mark Incomplete', 'lifterlms' ), $lesson ),
						'last_column' => true,
						'name' => 'mark_incomplete',
						'required' => false,
						'type'  => 'submit',
					) ); ?>

					<?php do_action( 'lifterlms_after_mark_incomplete_lesson' ); ?>

				</form>

			<?php endif; ?>

		<?php endif; ?>

	<?php else : ?>

		<?php if ( ! $quiz_id ) : ?>

			<form action="" class="llms-complete-lesson-form" method="POST" name="mark_complete">

				<?php do_action( 'lifterlms_before_mark_complete_lesson' ); ?>

				<input type="hidden" name="mark-complete" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>" />
				<input type="hidden" name="action" value="mark_complete" />
				<?php wp_nonce_field( 'mark_complete' ); ?>

				<?php llms_form_field( array(
					'columns' => 12,
					'classes' => 'nls_rounded_button nls_mark_complete auto button',
					'id' => 'llms_mark_complete',
					'value' => apply_filters( 'lifterlms_mark_lesson_complete_button_text', __( 'Mark Complete', 'lifterlms' ), $lesson ),
					'last_column' => true,
					'name' => 'mark_complete',
					'required' => false,
					'type'  => 'submit',
				) ); ?>

				<?php do_action( 'lifterlms_after_mark_complete_lesson' ); ?>

			</form>

		<?php endif; ?>

	<?php endif; ?>

	<?php if ( $quiz_id ) : ?>

		<form action="" class="llms-start-quiz-form" method="POST" name="take_quiz">

			<?php do_action( 'llms_before_start_quiz_button' ); ?>

		 	<input type="hidden" name="associated_lesson" value="<?php echo esc_attr( $lesson->get( 'id' ) ); ?>">
		 	<input type="hidden" name="quiz_id" value="<?php echo esc_attr( $quiz_id ); ?>">
		 	<input type="hidden" name="action" value="take_quiz">

		 	<?php wp_nonce_field( 'take_quiz', '_llms_take_quiz_nonce' ); ?>

			<?php llms_form_field( array(
				'columns' => 12,
				'classes' => 'llms-button-action auto button',
				'id' => 'llms_start_quiz',
				'value' => apply_filters( 'lifterlms_start_quiz_button_text', __( 'Take Quiz', 'lifterlms' ), $quiz_id, $lesson ),
				'last_column' => true,
				'name' => 'take_quiz',
				'required' => false,
				'type'  => 'submit',
			) ); ?>

			<?php do_action( 'llms_after_start_quiz_button' ); ?>

		</form>

	<?php endif; ?>

</div>
