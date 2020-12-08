const webpackPubConfig = require('./webpack.config'),
	pubCss = './public/css/',
	pubJs = './public/js/',
	adminCss = './admin/css/',
	adminJs = './admin/js/',
	buildComposer = './build/libs/composer/',
	buildCountryList = buildComposer + 'umpirsky/country-list/data/';

module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		copy: {
			adminAssets: {
				files: [{
					expand: true,
					cwd: 'node_modules/chart.js/dist',
					src: '*.css',
					dest: adminCss,
					filter: 'isFile'
				}],
			},
			buildDir: {
				files: [{
					expand: true,
					src: [
						'*.php', '*.txt',
						'public/**/**.php', 'public/**/**.min.js', 'public/**/**.min.css',
						'admin/**/**.php', 'admin/**/**.min.css', 'admin/**/**.min.js',
						'includes/**', 'languages/*.mo', 'libs/**',
					],
					dest: 'build/'
				}],
			},
			buildWpOrg: {
				files: [
					{
						expand: true,
						cwd: 'build/',
						src: '**',
						dest: 'buildWpOrg/trunk/'
					}, {
						expand: true,
						cwd: 'assets/',
						src: '**',
						dest: 'buildWpOrg/assets/'
					}
				],
			},
		},
		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true,
			},
		},
		mkdir: {
			build: {
				options: {
					create: ['build']
				},
			},
			buildWpOrg: {
				options: {
					create: ['buildWpOrg']
				},
			},
		},
		checkDependencies: {
			this: {},
		},
		webpack: {
			options: {
				stats: !process.env.NODE_ENV || process.env.NODE_ENV === 'development'
			},
			prod: webpackPubConfig,
			dev: Object.assign({watch: true}, webpackPubConfig),
		},
		sass: {
			dist: {
				options: {
					style: 'expanded'
				},
				files: {
					[pubCss + 'demovox-public.css']: pubCss + 'demovox-public.scss',
					[adminCss + 'demovox-admin.css']: adminCss + 'demovox-admin.scss',
				},
			}
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
				roundingPrecision: -1
			},
			target: {
				files: {
					[pubCss + 'demovox-public.min.css']: pubCss + 'demovox-public.css',
					[adminCss + 'demovox-admin.min.css']: adminCss + 'demovox-admin.css',
				}
			}
		},
		compress: {
			main: {
				options: {
					archive: './demovox.zip'
					/*
					archive: function () {
						// The global value git.tag is set by another task
						return 'demovox-' + git.tag + '.zip'
					}
					*/
				},
				expand: true,
				cwd: 'build/',
				src: ['**'],
				dest: '',
			}
		},
		clean: {
			build: ['build/',],
			buildComposer: [
				buildCountryList + '*_*',
				buildCountryList + '*/*.csv', buildCountryList + '*/*.html',
				buildCountryList + '*/*.sql', buildCountryList + '*/*.txt',
				buildCountryList + '*/*.xliff', buildCountryList + '*/*.xml',
				buildCountryList + '*/*.yaml',
				buildComposer + 'defuse/php-encryption/docs',
			],
			buildWpOrg: ['buildWpOrg/',],
			public: [pubCss + '*.css', pubCss + '*.map', pubJs + '*.min.js', pubJs + '*.map',],
			admin: [adminCss + '*.css', adminCss + '*.map', adminJs + 'demovox-admin.min.js',],
		},
		phpunit: {
			classes: {
				dir: 'tests/php/'
			},
			options: {
				bin: 'libs/composer/phpunit/phpunit/phpunit',
				bootstrap: 'tests/phpunit/bootstrap.php',
				colors: true
			}
		},
		checkwpversion: {
			options: {
				//Options specifying location your plug-in's header and readme.txt
				readme: 'README.txt',
				plugin: 'demovox.php',
			},
			check: { //Check plug-in version and stable tag match
				version1: 'plugin',
				version2: 'readme',
				compare: '==',
			},
			check2: { //Check plug-in version and package.json match
				version1: 'plugin',
				version2: '<%= pkg.version %>',
				compare: '==',
			},
		},
		availabletasks: {
			tasks: {}
		}
	});

	// Load NPM modules
	grunt.loadNpmTasks('grunt-available-tasks');
	grunt.loadNpmTasks('grunt-check-dependencies');

	grunt.loadNpmTasks('grunt-webpack');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	grunt.loadNpmTasks('grunt-checkwpversion');
	grunt.loadNpmTasks('grunt-composer');
	grunt.loadNpmTasks('grunt-phpunit');

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-po2mo');

	// define Tasks
	grunt.registerTask('default', 'availabletasks');
	grunt.registerTask('test', [
		'composer:install', 'phpunit',
	]);
	grunt.registerTask('buildAssets', [
		'checkDependencies', 'clean', 'webpack:prod', 'copy:adminAssets', 'sass', 'cssmin', 'po2mo',
	]);
	grunt.registerTask('build', [
		'buildAssets', 'composer:install:no-dev', 'mkdir:build', 'copy:buildDir', 'clean:buildComposer',
	]);
	grunt.registerTask('buildZip', [
		'build', 'compress', 'clean:build',
	]);
	grunt.registerTask('buildWpOrg', [
		'build', 'copy:buildWpOrg', 'clean:build',
	]);
};
