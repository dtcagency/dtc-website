"use strict";
module.exports = grunt=>{
	grunt.initConfig({
		makepot: {
			target: {
				options: {
					exclude: ['apps/.*', 'node_modules/*', 'assets/*'],
					mainFile: 'diviflash.php',
					domainPath: '/languages/',
					potFilename: 'divi_flash.pot',
					type: 'wp-plugin',
					updateTimestamp: true,
					potHeaders: {
						'report-msgid-bugs-to': 'https://diviflash.com/contact/',
						poedit: true,
						'x-poedit-keywordslist': true
					}
				}
			}
		},
	})

	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	grunt.registerTask( 'default', [ 'makepot' ] );

}