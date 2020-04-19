<?php

/**
 * Here placed all default settings
 * @since 1.0
 */

/**
 * An array of plugin default settings.
 *
 * Array param definitions are as follows:
 * key1                     = option name.
 *    |-key2                = field name.
 *         |-key3 => value  = property name => value.
 *
 * @since 1.0
 * @return array Return array of default settings
 */
function gq_default_config() {
	$gq_default_config = array(
		'gq_question'  => array(
			'title'    => __( 'Please answer the following anti-spam test', 'good-question' ),
			'desc'     => __( 'Just select the correct answers among the proposed', 'good-question' ),
			'question' => __( '1 + 1 = ?', 'good-question' ),
		),
		'gq_msg'       => __( 'ERROR: Failed Spam test - Please try again...', 'good-question' ),
		'gq_styles'    => gq_custom_styles(),
		'gq_activated' => '',
		'gq_comments'  => '',
		'gq_clear'     => '',
		'gq_answers'   => array(
			'1'  => array(
				'text' => '1',
				'true' => '',
				'disp' => 'Yes',
			),
			'2'  => array(
				'text' => '2',
				'true' => 'Yes',
				'disp' => 'Yes',
			),
			'3'  => array(
				'text' => '3',
				'true' => '',
				'disp' => 'Yes',
			),
			'4'  => array(
				'text' => '4',
				'true' => '',
				'disp' => '',
			),
			'5'  => array(
				'text' => '5',
				'true' => '',
				'disp' => '',
			),
			'6'  => array(
				'text' => '6',
				'true' => '',
				'disp' => '',
			),
			'7'  => array(
				'text' => '7',
				'true' => '',
				'disp' => '',
			),
			'8'  => array(
				'text' => '8',
				'true' => '',
				'disp' => '',
			),
			'9'  => array(
				'text' => '9',
				'true' => '',
				'disp' => '',
			),
			'10' => array(
				'text' => '10',
				'true' => '',
				'disp' => '',
			),
		),
	);
	return $gq_default_config;
}

/**
 * Options to be deleted.
 *
 * @since 1.3.0
 *
 * @return array
 */
function gq_deprecated_options() {
	return array(
		'gq_page' => '',
	);
}

/**
 * Custom styles for different themes
 *
 * @since 1.0
 * @return string
 */
function gq_custom_styles() {

	$styles = '#gq-wrapper{float: left; clear: both; width: 100%;}
#gq-question{font-style: italic; width: 100%;}
#gq-description{color: #777; font-style: italic;}
#gq-answers-list{list-style-type: none !important; padding-left: 2em;}';

	return $styles;
}
