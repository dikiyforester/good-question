<?php
/*
Plugin Name: Good Question
Plugin URI:
Description: Simple plugin to create an unique question and prevent spam-bots registration on your site.
Version: 1.1
Release Date: 02/10/2013
Author: Artem Frolov (dikiyforester)
Author URI: http://forums.appthemes.com/members/dikiyforester/
License: GPLv2 or later
*/

/*  Copyright 2012  Artem Frolov (dikiyforester)  (email : info@codedforest.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	die( 'Direct script access not allowed' );
}

define('GQ_FOLDER', 'good-question');
define('GQ_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . GQ_FOLDER);
define('GQ_URL', WP_PLUGIN_URL . '/' . GQ_FOLDER);
define('GQ_TITLE', 'Good Question');
define('GQ_MENU', 'Good Question');

if ( is_admin() ) {
	require( GQ_DIR . '/includes/gq-admin.php' );
	register_activation_hook( __FILE__, 'gq_activate' );
	register_deactivation_hook( __FILE__, 'gq_deactivate' );
	add_action( 'admin_init', 'gq_register_styles' );
	add_action( 'admin_menu',  'gq_init_plugin_menu', 10 );
}

if ( function_exists( 'bp_init' ) ) {
	add_action( 'bp_init', 'gq_bp_init'  );
	add_action( 'bp_signup_validate', 'gq_check_answers_bp'  );
} else {
	add_action( 'template_redirect', 'gq_where_to_run' );
	add_action( 'login_head', 'gq_print_styles' );
	add_action( 'register_form', 'gq_print_question', 100 );
	add_filter( 'registration_errors', 'gq_check_answers', 20, 1 );
}

/**
 * Works for BuddyPress
 * if is register gage now - will add actions
 *
 * @since 1.1
 */
function gq_bp_init(){
	if ( bp_is_register_page() ) {
		add_action( 'bp_head', 'gq_print_styles' );
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

	$activated = get_option( 'gq_activated' );
	$styles = get_option( 'gq_styles' );
	if ( empty( $styles ) || 'Yes' != $activated ) return;

	echo '<style type="text/css">' . $styles . '</style>';

}


/**
 * If registration form placed on custom specified page
 * plugin styles will be printed on this page,
 *
 * @since 1.0
 * @return type
 */
function gq_where_to_run() {
	if ( is_page( get_option( 'gq_page' ) ) )
		add_action( 'wp_head', 'gq_print_styles' );
}


/**
 * Print Good Question html on registration page
 *
 * @since 1.0
 * @return type
 */
function gq_print_question() {

	$activated = get_option( 'gq_activated' );
		if ( 'Yes' != $activated ) return;

			$question = get_option( 'gq_question' );
			$answers = gq_resort_array( get_option( 'gq_answers' ) );

			if ( $question == '' || !is_array( $answers ) ) return; ?>

					<div id="gq-wrapper">
						<h3 id="gq-title"><?php esc_html_e( $question['title'] ); ?></h3>
						<p id="gq-question"><?php esc_html_e( $question['question'] ); ?></p>
						<?php do_action( 'bp_go_home_bot_errors' ); ?>
						<ol id="gq-answers-list">
							<?php
							foreach ( $answers as $key => $answer ) {

								if ( 'Yes' != $answer['disp']) continue;
								?>
									<li id="gq-answer-li_<?php esc_attr_e( $key ); ?>">
										<input type="checkbox" <?php esc_attr_e( gq_checked_answers( $answer['text'] ) ); ?> name="gq_answers[]" value="<?php trim( esc_attr_e( $answer['text'] ) ); ?>" id="gq_answer_<?php esc_attr_e( $key ); ?>" class="checkboxlist" />
										<span class="gq-answer-placeholder" id="gq-answer-placeholder_<?php esc_attr_e( $key ); ?>"></span>
										<span class="gq-answer-text" id="gq-answer-text_<?php esc_attr_e( $key ); ?>">&nbsp;&nbsp;&nbsp;<?php trim( esc_html_e( $answer['text'] ) ); ?></span>
									</li>
								<?php
							} ?>

						</ol>
						<span id="gq-description"><?php esc_html_e( $question['desc'] ); ?></span>
					</div>
				<?php

}


/**
 * Check the answers and returns an error during user registration
 *
 * @since 1.0
 * @param type $errors
 * @return type
 */
function gq_check_answers($errors){

	$activated = get_option( 'gq_activated' );
	if ( 'Yes' != $activated ) return $errors;

	$answers = get_option( 'gq_answers' );
	if ( $answers ){

		$msg = get_option( 'gq_msg' );

		foreach ( $answers as $answer ) {

			if ( $answer['disp'] != 'Yes'  ) continue;

			$post_answer = false;
			$stored_answer = ( $answer['true'] == 'Yes' ) ? true : false;

			if ( isset( $_POST['gq_answers'] ) && is_array( $_POST['gq_answers'] ) && in_array( $answer['text'], $_POST['gq_answers'] ) )
				$post_answer =  true;

			if ( $stored_answer != $post_answer ) {
				$errors->add('go_home_bot', $msg );
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
function gq_check_answers_bp(){
	global $bp;
	$errors = new WP_Error();
	$err = gq_check_answers($errors);

	if ( ! empty( $err->errors ) )
		$bp->signup->errors['go_home_bot'] = $err->errors['go_home_bot'][0];

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
	if ( isset( $_POST['gq_answers'] ) && is_array( $_POST['gq_answers'] ) )
		foreach ( $_POST['gq_answers'] as $chkval ) {
			if ( trim( $chkval ) == trim( $answer ) )
				return 'checked="checked"';
		}
}


/**
 * Returns randomly sorted array
 *
 * @since 1.0
 * @param array $arr Accept array for resort
 * @return array||bool Returns randomly sorted array. If accepted not an array, than returns false
 */
function gq_resort_array( $arr ) {
	if ( !is_array( $arr ) )
		return false;
	$randomize = array_keys( $arr );
	shuffle( $randomize );
	$randsort = array( );
	foreach ( $randomize as $k ) {
		$randsort[ $k ] = $arr[ $k ];
	}
	return $randsort;
}


// Only for WP Multisite
if( is_multisite() ){
	/**
	 * Print Good Question html on Multisite Signup form
	 *
	 * @since 1.0
	 * @param type $errors
	 */
	function gq_print_question_multisite( $errors ){
		if ( $errmsg = $errors->get_error_message( 'go_home_bot' ) ) { ?>
			<p class="error" id="gq-error"><?php echo esc_html( $errmsg ); ?></p>
		<?php }
		gq_print_question();
	}
	add_action( 'signup_extra_fields', 'gq_print_question_multisite', 100 );


	/**
	 * Check the answers and returns an error during user singup on Multisite
	 *
	 * @since 1.0
	 * @param type $content
	 * @return type
	 */
	function gq_check_answers_multisite( $content ){
		if( $_POST['stage'] == 'validate-user-signup' )
			$content['errors'] = gq_check_answers($content['errors']);
		return $content;
	}
	add_filter( 'wpmu_validate_user_signup', 'gq_check_answers_multisite', 20, 1 );
}
?>