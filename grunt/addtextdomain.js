
// add textdomain
module.exports = function(grunt) {

	grunt.config('addtextdomain', {

		options:{
			textdomain: '<%= globals.textdomain %>'
		},
		files: {
			src: [
				'**/*.php',
				'!bower_components/**',
				'!node_modules/**',
				'!tests/**',
				'!tmp/**'
			],
			expand: true
		},

	});


	// load the plugin
	grunt.loadNpmTasks( 'grunt-checktextdomain' );

};
