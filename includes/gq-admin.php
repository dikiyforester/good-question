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

	require( GQ_DIR . '/includes/gq-init.php' );

	$gq_default_config = gq_default_config();

		foreach ( $gq_default_config as $name => $value ) {
			if ( !get_option( $name ) )
				add_option( $name, $value );
		}
}


/**
 * Delete all plugin options on deactivation
 *
 * @since 1.0
 */
function gq_deactivate() {
	if( 'Yes' == get_option( 'gq_clear' ) ){
		require( GQ_DIR . '/includes/gq-init.php' );
		$gq_default_config = array_keys( gq_default_config() );
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
	$gq_plugin_hook = add_submenu_page( 'options-general.php', GQ_TITLE, GQ_MENU, 'manage_options', 'good-question-options', 'gq_get_admin_page_html' );

	// Using registered $page handle to hook stylesheet loading
	add_action( 'admin_print_styles-' . $gq_plugin_hook, 'gq_enqueue_styles' );

	return $gq_plugin_hook;
}

/**
 * Print Good Question options page
 *
 * @since 1.0
 */
function gq_get_admin_page_html() {

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	if ( isset( $_POST['gq_submit_hidden'] ) && $_POST['gq_submit_hidden'] == 'Y' ) {
		gq_options_update();
		?>
		<div class="updated"><p><strong><?php _e( 'Settings saved.' ); ?></strong></p></div>
		<?php
	}

	$question = get_option( 'gq_question' );
	$message = get_option( 'gq_msg' );
	$styles = get_option( 'gq_styles' );
	$activated = get_option( 'gq_activated' );
	$answers = get_option( 'gq_answers' );
	$clear = get_option( 'gq_clear' );
	$page = get_option( 'gq_page' );

	// notice for admin to activate Question
	if ( 'Yes' != $activated ){
	?>
		<div class="updated">
			<p>
				<strong>
					<?php _e( 'NOTICE: Question will not appear on form while not set property "Activate Now"!' ); ?>
				</strong>
			</p>
		</div>
	<?php
	}
	?>
		<div class="wrap">
				<div id="icon-options-general" class="gq_options_img icon32">
					<br />
				</div>
				<h2><?php echo GQ_TITLE . ' plugin'; ?></h2>
				<br/>
				<div>
					<form id="gq_options_form" name="gq_options_form" method="post" action="" enctype="multipart/form-data">
						<input type="hidden" name="gq_submit_hidden" value="Y">
						<p><input id="gq_submit_top-btn" class="button-primary" type="submit" name="Save_top" value="<?php _e( 'Save Changes' ); ?>" /></p>
						<table id="gq_question_table" class="widefat">
							<!-- Question Settings Table -->
							<thead>
								<tr>
									<th colspan="2"><?php _e( 'Question Settings' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="question-settings-left"><label for="gq_register_page"><?php _e( 'Registration Page Title | Slug | ID :' ); ?></label></td>
									<td>
										<input type="text" name="gq_register_page" class="gq-textbox" value="<?php echo $page; ?>"/><br />
										<span class="gq-desc"><?php _e( 'If your theme uses special registration page - enter here page Slug or Title or ID.<br />
											If your theme uses standard WordPress login/register page - forget this option and go next.' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_question_title"><?php _e( 'Title:' ); ?></label></td>
									<td>
										<input type="text" name="gq_question_title" class="gq-textbox" value="<?php echo $question['title']; ?>"/><br />
										<span class="gq-desc"><?php _e( 'This is the heading of the question block. (CSS ID: #gq-title)' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_question"><?php _e( 'Question:' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_question" ><?php echo esc_textarea( $question['question'] ); ?></textarea><br />
										<span class="gq-desc"><?php _e( 'Question to which the user must answer. (CSS ID: #gq-question)<br />
																		NOTICE: Never use the question and answers by default. Come up with something unique!' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_desc"><?php _e( 'Description:' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_desc" ><?php echo esc_textarea( $question['desc'] ); ?></textarea><br />
										<span class="gq-desc"><?php _e( 'Description, which displays after answers and helps user understand what to do. (CSS ID: #gq-description)' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_msg"><?php _e( 'Error Message:' ); ?></label></td>
									<td>
										<textarea rows="4" cols="60" class="textarea" name="gq_msg" ><?php echo esc_textarea( $message ); ?></textarea><br />
										<span class="gq-desc"><?php _e( 'Error message, which will appear if user not correct answer to the question and send it to the server.' ); ?></span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_styles"><?php _e( 'Custom Styles:' ); ?></label></td>
									<td>
										<textarea rows="8" cols="60" class="textarea" name="gq_styles" ><?php echo esc_textarea( $styles ); ?></textarea><br />
										<span class="gq-desc"><?php _e( 'This is default CSS properties for Question block on form.<br />
																		NOTICE: These properties may be different for different themes and childthemes,<br />
																		so I gave you the ability to change them here.<br />
																		You can set suitable styles and assign different colors or<br />
																		background images for each individual Answer. ' ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_activated"><?php _e( 'Activate Now:' ); ?></label></td>
									<td>
										<input type="checkbox" name="gq_activated" value="Yes" <?php checked( $activated, "Yes", true ); ?>/><br />
										<span class="gq-desc"><?php _e( 'Output of the Question is disabled by default. It will allow admin to set unique question and answers,<br />
																		and only after that activate Question functionality for users.
																		<br />Set this option to display Question block on the form.' ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<td class="question-settings-left"><label for="gq_clear"><?php _e( 'Delete Plugin Options after deactivation:' ); ?></label></td>
									<td>
										<input type="checkbox" name="gq_clear" value="Yes" <?php checked( $clear, "Yes", true ); ?>/><br />
										<span class="gq-desc"><?php _e( 'Set this option if you want to deactivate plugin and clear database from plugin options.' ); ?></span>
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
									<th><?php _e( 'Answers Settings' ); ?></th>
									<th><?php _e( 'Answer Text' ); ?></th>
									<th><?php _e( 'It\'s True?' ); ?></th>
									<th><?php _e( 'Display Answer?' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $answers as $name => $value ) { ?>
									<tr>
										<td class="answers-settings-left"><label for="gq_answer_<?php esc_attr_e( $name );?>"><?php _e( 'Answer #' ); ?><?php esc_html_e( $name );?>:</label></td>
										<td class="answers-text-col"><input type="text" name="gq_answer_<?php esc_attr_e( $name );?>" value="<?php esc_attr_e( $value['text'] );?>"/></td>
										<td class="answers-true-col"><input type="checkbox" name="gq_true_<?php esc_attr_e( $name );?>" value="Yes" <?php checked( $value['true'], "Yes", true ); ?>/></td>
										<td class="answers-display-col"><input type="checkbox" name="gq_display_<?php esc_attr_e( $name );?>" value="Yes" <?php checked( $value['disp'], "Yes", true ); ?>/></td>
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
						<p><input id="acf_submit_bot-btn" class="button-primary" type="submit" name="Save_bot" value="<?php _e( 'Save Changes' ); ?>" /></p>
					</form>
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

	$question = array();
	$styles = '';
	$message = '';
	$activated = '';
	$answers = array();
	$clear = '';
	$page = '';

	if ( isset( $_POST['gq_register_page'] ) )
		$page = sanitize_text_field( $_POST['gq_register_page'] );

	if ( isset( $_POST['gq_clear'] ) && 'Yes' == $_POST['gq_clear'] )
		$clear = 'Yes';

	if ( isset( $_POST['gq_activated'] ) && 'Yes' == $_POST['gq_activated'] )
		$activated = 'Yes';

	if ( isset( $_POST['gq_question_title'] ) )
		$question['title'] = sanitize_text_field( $_POST['gq_question_title'] );

	if ( isset( $_POST['gq_question'] ) )
		$question['question'] = stripslashes( $_POST['gq_question'] );

	if ( isset( $_POST['gq_desc'] ) )
		$question['desc'] = stripslashes( $_POST['gq_desc'] );

	if ( isset( $_POST['gq_msg'] ) )
		$message = stripslashes( $_POST['gq_msg'] );

	if ( isset( $_POST['gq_styles'] ) )
		$styles = stripslashes( $_POST['gq_styles'] );

	for ( $i = 1; $i <= 10; $i++ ) {

		if ( isset( $_POST['gq_answer_' . $i] ) )
			$answers[ $i ]['text'] = sanitize_text_field( $_POST['gq_answer_' . $i] );

		if ( isset( $_POST['gq_true_' . $i] ) && 'Yes' == $_POST['gq_true_' . $i] )
			$answers[ $i ]['true'] = 'Yes';
		else
			$answers[ $i ]['true'] = '';

		if ( isset( $_POST['gq_display_' . $i] ) && 'Yes' == $_POST['gq_display_' . $i] )
			$answers[ $i ]['disp'] = 'Yes';
		else
			$answers[ $i ]['disp'] = '';
	}

	 update_option( 'gq_question', $question );
	 update_option( 'gq_msg', $message );
	 update_option( 'gq_styles', $styles );
	 update_option( 'gq_activated', $activated );
	 update_option( 'gq_answers', $answers );
	 update_option( 'gq_clear', $clear );
	 update_option( 'gq_page', $page );

}

/**
 * Register back-end styles
 *
 * @since 1.0
 */
function gq_register_styles() {
   wp_register_style( 'gqAdminStyle',GQ_URL . '/css/gq-admin-styles.css' );
}

/**
 * Enqueue back-end styles
 *
 * @since 1.0
 */
function gq_enqueue_styles() {
	wp_enqueue_style( 'gqAdminStyle' );
}
?>