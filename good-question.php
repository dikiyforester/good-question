<?php
/*
 * Plugin Name: Good Question
 * Plugin URI: https://arthemes.org/products/good-question-plugin-for-wordpress/
 * Description: Simple but practical plugin to stop spam comments and registrations on your site.
 * Version: 1.3.0
 * Release Date: 02/10/2013
 * Author: Artem Frolov (dikiyforester)
 * Author URI: https://arthemes.org
 * Domain Path: /languages
 * Text Domain: good-question
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	die( 'Direct script access not allowed' );
}

define( 'GQ_FOLDER', 'good-question' );
define( 'GQ_DIR', dirname( __FILE__ ) );
define( 'GQ_URL', plugins_url( '', __FILE__ ) );

load_plugin_textdomain( 'good-question', false, basename( dirname( __FILE__ ) ) . '/languages' );

if ( is_admin() ) {
	require( GQ_DIR . '/includes/gq-admin.php' );
	register_activation_hook( __FILE__, 'gq_activate' );
	register_deactivation_hook( __FILE__, 'gq_deactivate' );
	add_action( 'admin_init', 'gq_register_styles' );
	add_action( 'admin_menu', 'gq_init_plugin_menu', 10 );
}

if ( function_exists( 'bp_init' ) ) {
	add_action( 'bp_init', 'gq_bp_init' );
	add_action( 'bp_signup_validate', 'gq_check_answers_bp' );
} else {
	add_action( 'register_form', 'gq_print_question', 100 );
	add_filter( 'registration_errors', 'gq_check_answers', 20, 1 );
}

add_filter( 'comment_form_default_fields', 'gq_gq_print_comments_form_question', 99999 );
add_filter( 'preprocess_comment', 'gq_check_comments_form_answers' );

/**
 * Works for BuddyPress
 * if this is register page now - will add actions
 *
 * @since 1.1
 */
function gq_bp_init() {
	if ( bp_is_register_page() ) {
		add_action( 'bp_before_registration_submit_buttons', 'gq_print_question', 100 );
	}
}

/**
 * Print custom styles
 *
 * @since 1.0
 * @return type
 */
function gq_print_styles() {

	$styles = get_option( 'gq_styles' );
	if ( empty( $styles ) ) {
		return;
	}

	echo '<style type="text/css">' . $styles . '</style>';
}

/**
 * Print Good Question html on registration page
 *
 * @since 1.0
 * @return type
 */
function gq_print_question() {

	$activated = get_option( 'gq_activated' );
	if ( 'Yes' !== $activated ) {
		return;
	}

	gq_render_html();
}

/**
 * Renders Good Question HTML
 *
 * @since 1.3.0
 */
function gq_render_html() {

	$question = get_option( 'gq_question' );
	$answers  = gq_resort_array( get_option( 'gq_answers' ) );

	if ( empty( $question ) || ! is_array( $answers ) ) {
		return;
	}

	gq_print_styles();
	?>

	<div id="gq-wrapper">
		<h3 id="gq-title"><?php echo esc_html( $question['title'] ); ?></h3>
		<p id="gq-question"><?php echo esc_html( $question['question'] ); ?></p>
			<?php do_action( 'bp_go_home_bot_errors' ); ?>
		<ol id="gq-answers-list">
			<?php
			foreach ( $answers as $key => $answer ) {

				if ( 'Yes' !== $answer['disp'] ) {
					continue;
				}
				?>
				<li id="gq-answer-li_<?php echo esc_attr( $key ); ?>">
					<input type="checkbox" <?php echo esc_attr( gq_checked_answers( $answer['text'] ) ); ?> name="gq_answers[]" value="<?php echo esc_attr( trim( $answer['text'] ) ); ?>" id="gq_answetrim( r_<?php echo esc_attr( $key ); ?>" class="checkboxlist" />
					<span class="gq-answer-placeholder" id="gq-answer-placeholder_<?php echo esc_attr( $key ); ?>"></span>
					<span class="gq-answer-text" id="gq-answer-text_<?php echo esc_attr( $key ); ?>">&nbsp;&nbsp;&nbsp;<?php echo esc_html( trim( $answer['text'] ) ); ?></span>
				</li>
				<?php
			}
			?>
		</ol>
		<span id="gq-description"><?php echo esc_html( $question['desc'] ); ?></span>
	</div>
	<?php
}

/**
 * Print Good Question html on the comments form
 *
 * @since 1.3
 *
 * @param string[] $fields Array of the default comment fields.
 *
 * @return string[]
 */
function gq_gq_print_comments_form_question( $fields ) {
	ob_start();

	$activated = get_option( 'gq_comments' );
	if ( 'Yes' !== $activated ) {
		return;
	}

	gq_render_html();

	$fields['qood_question'] = ob_get_clean();

	return $fields;
}

/**
 * Check answers on comment form before insert comment in db.
 *
 * @since 1.3
 *
 * @param array $commentdata Comment data.
 *
 * @return type
 */
function gq_check_comments_form_answers( $commentdata ) {

	// If isset author and email fields, so Good Questions is also should be posted.
	if ( isset( $_POST['author'] ) && isset( $_POST['email'] ) ) {
		// Nothing to do if GQ is not activated on comments form.
		$activated = get_option( 'gq_comments' );
		if ( 'Yes' !== $activated ) {
			return $commentdata;
		}

		$errors = new WP_Error();
		$err    = gq_validate_answers( $errors );
		if ( ! empty( $err->errors ) ) {
			wp_die( $err->get_error_message() );
		}
	}

	return $commentdata;
}

/**
 * Check the answers and returns an error during user registration
 *
 * @since 1.0
 * @param WP_Errors $errors
 * @return type
 */
function gq_check_answers( $errors ) {

	$activated = get_option( 'gq_activated' );
	if ( 'Yes' !== $activated ) {
		return $errors;
	}

	return gq_validate_answers( $errors );
}

/**
 * Validates submitted answers and returns error object on failure.
 *
 * @since 1.3
 *
 * @param WP_Error $errors
 *
 * @return WP_Error
 */
function gq_validate_answers( $errors ) {
	$answers = get_option( 'gq_answers' );
	if ( $answers ) {

		$msg = get_option( 'gq_msg' );

		foreach ( $answers as $answer ) {

			if ( 'Yes' !== $answer['disp'] ) {
				continue;
			}

			$post_answer   = false;
			$stored_answer = ( 'Yes' === $answer['true'] ) ? true : false;

			if ( isset( $_POST['gq_answers'] ) && is_array( $_POST['gq_answers'] ) && in_array( $answer['text'], $_POST['gq_answers'] ) ) {
				$post_answer = true;
			}

			if ( $stored_answer !== $post_answer ) {
				$errors->add( 'go_home_bot', $msg );
				return $errors;
			}
		}
	}

	return $errors;
}

/**
 * Uses function gq_check_answers($errors) adapted to BuddyPress sign-up errors
 *
 * @since 1.1
 * @global type $bp
 */
function gq_check_answers_bp() {
	global $bp;
	$errors = new WP_Error();
	$err    = gq_check_answers( $errors );

	if ( ! empty( $err->errors ) ) {
		$bp->signup->errors['go_home_bot'] = $err->errors['go_home_bot'][0];
	}

	unset( $errors );
}

/**
 * Return checked boxes after unsuccessful registration attempt
 *
 * @since 1.0
 * @param string $answer
 * @return string Return html attribute checked="checked" if check successfull
 */
function gq_checked_answers( $answer ) {
	if ( isset( $_POST['gq_answers'] ) && is_array( $_POST['gq_answers'] ) ) {
		foreach ( $_POST['gq_answers'] as $chkval ) {
			if ( trim( $chkval ) == trim( $answer ) ) {
				return 'checked="checked"';
			}
		}
	}
}

/**
 * Returns randomly sorted array
 *
 * @since 1.0
 * @param array $arr Accept array for resort.
 * @return array||bool Returns randomly sorted array. If accepted not an array, than returns false
 */
function gq_resort_array( $arr ) {
	if ( ! is_array( $arr ) ) {
		return false;
	}
	$randomize = array_keys( $arr );
	shuffle( $randomize );
	$randsort = array();
	foreach ( $randomize as $k ) {
		$randsort[ $k ] = $arr[ $k ];
	}
	return $randsort;
}

// Only for WP Multisite.
if ( is_multisite() ) {

	/**
	 * Print Good Question html on Multisite Signup form
	 *
	 * @since 1.0
	 * @param WP_Error $errors
	 */
	function gq_print_question_multisite( $errors ) {
		if ( $errmsg = $errors->get_error_message( 'go_home_bot' ) ) {
			?>
			<p class="error" id="gq-error"><?php echo esc_html( $errmsg ); ?></p>
		<?php
		}
		gq_print_question();
	}

	add_action( 'signup_extra_fields', 'gq_print_question_multisite', 100 );

	/**
	 * Check the answers and returns an error during user singup on Multisite
	 *
	 * @since 1.0
	 * @param array $content
	 * @return type
	 */
	function gq_check_answers_multisite( $content ) {
		if ( $_POST['stage'] === 'validate-user-signup' ) {
			$content['errors'] = gq_check_answers( $content['errors'] );
		}
		return $content;
	}

	add_filter( 'wpmu_validate_user_signup', 'gq_check_answers_multisite', 20, 1 );
}
