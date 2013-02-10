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
function gq_default_config(){
	$gq_default_config = array(
		'gq_question'	=> array(
					'title'		=> __('Please answer the following anti-spam test', 'appthemes'),
					'desc'		=> __('Just select the correct answers among the proposed', 'appthemes'),
					'question'	=> __('Select all objects with corners from the list below:', 'appthemes')
		),
		'gq_msg'		=> 'ERROR: Failed Spam test - Please try again...',
		'gq_styles'		=> gq_custom_styles(),
		'gq_activated'	=> '',
		'gq_clear'		=> '',
		'gq_page'		=> 'register',
		'gq_answers'	=> array(
						'1'	=> array(
							'text'	=> 'Melon',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'2' => array(
							'text'	=> 'Box',
							'true'	=> 'Yes',
							'disp'	=> 'Yes'
						),
						'3' => array(
							'text'	=> 'Book',
							'true'	=> 'Yes',
							'disp'	=> 'Yes'
						),
						'4' => array(
							'text'	=> 'Ball',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'5' => array(
							'text'	=> 'Drop',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'6' => array(
							'text'	=> 'TV',
							'true'	=> 'Yes',
							'disp'	=> 'Yes'
						),
						'7' => array(
							'text'	=> 'Air',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'8' => array(
							'text'	=> 'Sphere',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'9' => array(
							'text'	=> 'Tomato',
							'true'	=> '',
							'disp'	=> 'Yes'
						),
						'10' => array(
							'text'	=> 'Door',
							'true'	=> 'Yes',
							'disp'	=> 'Yes'
						)
		)
	);
	return $gq_default_config;
}

/**
 * Custom styles for different themes
 *
 * @since 1.0
 * @return string
 */
function gq_custom_styles(){

	$theme = wp_get_theme();
	$themename = $theme->name;

	switch ( $themename ) {
		case 'ClassiPress':
		case 'Flannel':
		case 'CLASSIECO':
			$styles = '#gq-wrapper{float: left; clear: both; width: 100%; margin-left: 140px;}
#gq-question{font-style: italic; width: 100%;}
#gq-description{color: #777; font-style: italic; font-size: 10px;}
#gq-answers-list{list-style-type: none; padding-left: 2em;}';
			break;

		case 'Clipper':
			$styles = '#gq-wrapper{float: left; clear: both; width: 100%; margin-left: 166px;}
#gq-question{font-style: italic; width: 100%;}
#gq-description{color: #777; font-style: italic; font-size: 10px;}
#gq-answers-list{list-style-type: none; padding-left: 2em;}
#gq-answers-list li{margin: 0;}';
			break;

		case 'Vantage':
		case 'Quality Control':
			$styles = '#gq-wrapper{margin: 20px 0; width: 75%; position: relative;}
#gq-question{font-style: italic; width: 100%;}
#gq-description{color: #777; font-style: italic; font-size: 10px;}
#gq-answers-list{list-style-type: none; padding-left: 2em;}
#gq-answers-list li{margin: 0;}';
			break;

		default:
			$styles = '#gq-wrapper{float: left; clear: both; width: 100%;}
#gq-question{font-style: italic; width: 100%;}
#gq-description{color: #777; font-style: italic; font-size: 10px;}
#gq-answers-list{list-style-type: none !important; padding-left: 2em;}';
			break;
	}

	return $styles;
}
?>