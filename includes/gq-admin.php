<?php
/**
 * Here placed all back-end functions
 *
 * @since 1.0
 */

/**
 * Save all default options to DB on activation
 *
 * @since 1.0
 */
function gq_activate() {

	require GQ_DIR . '/includes/gq-init.php';

	$gq_default_config = gq_default_config();

	foreach ( $gq_default_config as $name => $value ) {
		if ( ! get_option( $name ) ) {
			add_option( $name, $value );
		}
	}
}

/**
 * Delete all plugin options on deactivation
 *
 * @since 1.0
 */
function gq_deactivate() {
	if ( 'Yes' === get_option( 'gq_clear' ) ) {
		require GQ_DIR . '/includes/gq-init.php';
		$gq_default_config = array_keys( array_merge( gq_default_config(), gq_deprecated_options() ) );
		foreach ( $gq_default_config as $name ) {
			delete_option( $name );
		}
	}
}

/**
 * Init plugin menu
 *
 * @since 1.0
 * @global string $gq_plugin_hook
 * @return string used internally to track menu page callbacks for outputting the page inside the global $menu array
 */
function gq_init_plugin_menu() {
	global $gq_plugin_hook;
	$gq_plugin_hook = add_submenu_page( 'options-general.php', __( 'Good Question', 'good-question' ), __( 'Good Question', 'good-question' ), 'manage_options', 'good-question-options', 'gq_get_admin_page_html' );

	// Using registered $page handle to hook stylesheet loading.
	add_action( 'admin_print_styles-' . $gq_plugin_hook, 'gq_enqueue_styles' );

	return $gq_plugin_hook;
}

/**
 * Print Good Question options page
 *
 * @since 1.0
 */
function gq_get_admin_page_html() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'good-question' ) );
	}

	if ( isset( $_POST['gq_submit'] ) ) {
		gq_options_update();
		?>
		<div class="updated"><p><strong><?php esc_html_e( 'Settings saved.', 'good-question' ); ?></strong></p></div>
		<?php
	}

	require GQ_DIR . '/includes/gq-init.php';
	$defaults = gq_default_config();

	$question  = get_option( 'gq_question', $defaults['gq_question'] );
	$message   = get_option( 'gq_msg', $defaults['gq_msg'] );
	$styles    = get_option( 'gq_styles', $defaults['gq_styles'] );
	$activated = get_option( 'gq_activated', $defaults['gq_activated'] );
	$comments  = get_option( 'gq_comments', $defaults['gq_comments'] );
	$answers   = get_option( 'gq_answers', $defaults['gq_answers'] );
	$clear     = get_option( 'gq_clear', $defaults['gq_clear'] );

	// notice for admin to activate Question.
	if ( 'Yes' !== $activated && 'Yes' !== $comments ) {
		?>
		<div class="updated">
			<p>
				<strong>
					<?php esc_html_e( 'NOTICE: Question will not appear on form while display option is not set!', 'good-question' ); ?>
				</strong>
			</p>
		</div>
		<?php
	}
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Good Question plugin', 'good-question' ); ?></h2>
		<br/>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">

				<div id="postbox-container-1" class="postbox-container">

					<div id="side-sortables" class="meta-box-sortables ui-sortable">

						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'It will be always free!', 'good-question' ); ?></label></h3>
							<div class="inside">
								<?php esc_attr_e( 'The Good Question plugin was created as a contribution to the development of free open-source software, with a hope that it will be useful to WordPress users. It will always be free, without premium versions or subscriptions.', 'good-question' ); ?>
							</div>
						</div>

						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'Rate Us', 'good-question' ); ?></label></h3>
							<div class="inside">
								<?php echo sprintf( __( 'Like the plugin? Please give us a <a href="%s" target="_blank">rating!</a>', 'good-question' ), 'https://wordpress.org/support/plugin/good-question/reviews/?filter=5' ); ?>
								<div class="gq-stars-container">
									<a href="https://wordpress.org/support/plugin/good-question/reviews/?filter=5" target="_blank">
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
									</a>
								</div>
							</div>
						</div>
						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'Our Other Plugins', 'good-question' ); ?></label></h3>
							<div class="inside">
								<?php echo sprintf( __( 'Check out <a target="_blank" href="%s">our other plugins</a>', 'good-question' ), 'https://arthemes.org/category/plugins/' ); ?>
							</div>
						</div>
						<div class="postbox" style="min-width: inherit;">
							<h3 class="hndle"><label for="title"><?php _e( 'Social', 'good-question' ); ?></label></h3>
							<div class="inside gq-social-container">
								<?php echo sprintf( __( '<a target="_blank" href="%s"><span class="dashicons dashicons-twitter"></span> Twitter</a>', 'good-question' ), 'https://twitter.com/arthemes_org' ); ?>
								<?php echo sprintf( __( '<a target="_blank" href="%s"><span class="dashicons dashicons-github"></span> GitHub</a>', 'good-question' ), 'https://github.com/dikiyforester' ); ?>
							</div>
						</div>

					</div>

				</div>

				<div id="postbox-container-2" class="postbox-container">

					<form id="gq_options_form" name="gq_options_form" method="post" action="" enctype="multipart/form-data">

						<?php wp_nonce_field( 'update', 'gq_submit' ) ;?>

						<table id="gq_question_table" class="widefat">
							<!-- Question Settings Table -->
							<thead>
								<tr>
									<th colspan="2"><?php esc_html_e( 'Question Settings', 'good-question' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="question-settings-left"><label for="gq_question_title"><?php esc_html_e( 'Title:', 'good-question' ); ?></label></td>
									<td>
										<input type="text" name="gq_question_title" class="gq-textbox" value="<?php echo esc_attr( $question['title'] ); ?>"/><br />
										<span class="gq-desc"><?php esc_html_e( 'This is the heading of the question block. (CSS ID: #gq-title)', 'good-question' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_question"><?php esc_html_e( 'Question:', 'good-question' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_question" ><?php echo esc_textarea( $question['question'] ); ?></textarea><br />
										<span class="gq-desc">
											<?php esc_html_e( 'Question to which the user must answer. (CSS ID: #gq-question)', 'good-question' ); ?><br />
											<?php esc_html_e( 'NOTICE: Never use the question and answers by default. Come up with something unique!', 'good-question' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_desc"><?php esc_html_e( 'Description:', 'good-question' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_desc" ><?php echo esc_textarea( $question['desc'] ); ?></textarea><br />
										<span class="gq-desc"><?php esc_html_e( 'Description, which displays after answers and helps user understand what to do. (CSS ID: #gq-description)', 'good-question' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_msg"><?php esc_html_e( 'Error Message:', 'good-question' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_msg" ><?php echo esc_textarea( $message ); ?></textarea><br />
										<span class="gq-desc"><?php esc_html_e( 'Error message, which will appear if user not correct answer to the question and send it to the server.', 'good-question' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_styles"><?php esc_html_e( 'Custom Styles:', 'good-question' ); ?></label></td>
									<td>
										<textarea rows="8" cols="60" class="textarea" name="gq_styles" ><?php echo esc_textarea( $styles ); ?></textarea><br />
										<span class="gq-desc">
											<?php esc_html_e( 'This is default CSS properties for Question block on form.', 'good-question' ); ?><br />
											<?php esc_html_e( 'NOTICE: These properties may be different for different themes and childthemes.', 'good-question' ); ?><br />
											<?php esc_html_e( 'You can set suitable styles and assign different colors or background images for each individual Answer.', 'good-question' ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_activated"><?php esc_html_e( 'Display on registration form:', 'good-question' ); ?></label></td>
									<td>
										<input type="checkbox" name="gq_activated" value="Yes" <?php checked( $activated, 'Yes', true ); ?>/><br />
										<span class="gq-desc">
											<?php esc_html_e( 'Output of the Question is disabled by default.', 'good-question' ); ?><br />
											<?php esc_html_e( 'It allows admin to set unique question and answers, and only after that activate Question functionality for users.', 'good-question' ); ?><br />
											<?php esc_html_e( 'Set this option to display Question block on the form.', 'good-question' ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_comments"><?php esc_html_e( 'Display on comments form:', 'good-question' ); ?></label></td>
									<td>
										<input type="checkbox" name="gq_comments" value="Yes" <?php checked( $comments, 'Yes', true ); ?>/><br />
										<span class="gq-desc">
											<?php esc_html_e( 'Output of the Question is disabled by default.', 'good-question' ); ?><br />
											<?php esc_html_e( 'Set this option to display Question block on the comments form.', 'good-question' ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_clear"><?php esc_html_e( 'Delete Plugin Options after deactivation:', 'good-question' ); ?></label></td>
									<td>
										<input type="checkbox" name="gq_clear" value="Yes" <?php checked( $clear, 'Yes', true ); ?>/><br />
										<span class="gq-desc"><?php esc_html_e( 'Set this option if you want to deactivate plugin and clear database from plugin options.', 'good-question' ); ?></span>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="2">&nbsp;</th>
								</tr>
							</tfoot>
						</table>
						<!-- /Question Settings Table -->
						<br /><br />
						<!-- Answers Settings Table -->
						<table id="gq_answers_table" class="widefat">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Answers Settings', 'good-question' ); ?></th>
									<th><?php esc_html_e( 'Answer Text', 'good-question' ); ?></th>
									<th><?php esc_html_e( 'It\'s True?', 'good-question' ); ?></th>
									<th><?php esc_html_e( 'Display Answer?', 'good-question' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $answers as $name => $value ) { ?>
									<tr>
										<td class="answers-settings-left"><label for="gq_answer_<?php echo esc_attr( $name ); ?>"><?php esc_html_e( 'Answer #', 'good-question' ); ?><?php echo esc_html( $name ); ?>:</label></td>
										<td class="answers-text-col"><input type="text" name="gq_answer_<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value['text'] ); ?>"/></td>
										<td class="answers-true-col"><input type="checkbox" name="gq_true_<?php echo esc_attr( $name ); ?>" value="Yes" <?php checked( $value['true'], 'Yes', true ); ?>/></td>
										<td class="answers-display-col"><input type="checkbox" name="gq_display_<?php echo esc_attr( $name ); ?>" value="Yes" <?php checked( $value['disp'], 'Yes', true ); ?>/></td>
									</tr>
								<?php } ?>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="4">&nbsp;</th>
								</tr>
							</tfoot>
							<!-- /Answers Settings Table -->
						</table>
						<br/>
						<p><input id="acf_submit_bot-btn" class="button-primary" type="submit" name="Save_bot" value="<?php echo esc_attr_e( 'Save Changes', 'good-question' ); ?>" /></p>
					</form>

				</div>

			</div>
		</div>
	</div>
	<?php
}

/**
 * Update all plugin options
 *
 * @since 1.0
 */
function gq_options_update() {

	check_admin_referer( 'update', 'gq_submit' );

	$question  = array();
	$styles    = '';
	$message   = '';
	$activated = '';
	$comments  = '';
	$answers   = array();
	$clear     = '';

	if ( isset( $_POST['gq_clear'] ) && 'Yes' == $_POST['gq_clear'] ) {
		$clear = 'Yes';
	}

	if ( isset( $_POST['gq_activated'] ) && 'Yes' == $_POST['gq_activated'] ) {
		$activated = 'Yes';
	}

	if ( isset( $_POST['gq_comments'] ) && 'Yes' == $_POST['gq_comments'] ) {
		$comments = 'Yes';
	}

	if ( isset( $_POST['gq_question_title'] ) ) {
		$question['title'] = sanitize_text_field( $_POST['gq_question_title'] );
	}

	if ( isset( $_POST['gq_question'] ) ) {
		$question['question'] = stripslashes( $_POST['gq_question'] );
	}

	if ( isset( $_POST['gq_desc'] ) ) {
		$question['desc'] = stripslashes( $_POST['gq_desc'] );
	}

	if ( isset( $_POST['gq_msg'] ) ) {
		$message = stripslashes( $_POST['gq_msg'] );
	}

	if ( isset( $_POST['gq_styles'] ) ) {
		$styles = stripslashes( $_POST['gq_styles'] );
	}

	for ( $i = 1; $i <= 10; $i++ ) {

		if ( isset( $_POST[ 'gq_answer_' . $i ] ) ) {
			$answers[ $i ]['text'] = sanitize_text_field( $_POST[ 'gq_answer_' . $i ] );
		}

		if ( isset( $_POST[ 'gq_true_' . $i ] ) && 'Yes' === $_POST['gq_true_' . $i ] ) {
			$answers[ $i ]['true'] = 'Yes';
		} else {
			$answers[ $i ]['true'] = '';
		}

		if ( isset( $_POST[ 'gq_display_' . $i ] ) && 'Yes' == $_POST[ 'gq_display_' . $i ] ) {
			$answers[ $i ]['disp'] = 'Yes';
		} else {
			$answers[ $i ]['disp'] = '';
		}
	}

	update_option( 'gq_question', $question );
	update_option( 'gq_msg', $message );
	update_option( 'gq_styles', $styles );
	update_option( 'gq_activated', $activated );
	update_option( 'gq_comments', $comments );
	update_option( 'gq_answers', $answers );
	update_option( 'gq_clear', $clear );
}

/**
 * Register back-end styles
 *
 * @since 1.0
 */
function gq_register_styles() {
	wp_register_style( 'gqAdminStyle', GQ_URL . '/css/gq-admin-styles.css' );
}

/**
 * Enqueue back-end styles
 *
 * @since 1.0
 */
function gq_enqueue_styles() {
	wp_enqueue_style( 'gqAdminStyle' );
}
