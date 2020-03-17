/*!
 * Plugin Gruntfile
 * https://www.appthemes.com
 * @author dikiyforester
 */

'use strict';

/**
 * Grunt Module
 */
module.exports = function(grunt) {

	grunt.initConfig({

		pkg: grunt.file.readJSON( 'package.json' ),

		// set global variables
		globals: {
			type: 'wp-plugin',
			textdomain: 'good-question',
			languages: 'languages'
		}

	});



	/**
	 * Grunt Tasks
	 */

	// load plugin configs from grunt folder
	grunt.loadTasks( 'grunt' );


	// default task when you run 'grunt'
	grunt.registerTask( 'default', [
		'build'
	]);

	// custom task when you run 'grunt build'
	grunt.registerTask( 'build', [
		'makepot'
	]);


};
