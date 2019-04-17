const webpackPubConfig = require('./webpack.config'),
	pubCss = './public/css/',
	pubJs = './public/js/',
	adminCss = './admin/css/',
	adminJs = './admin/js/';

module.exports = function (grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		copy: {
			adminAssets: {
				files: [{
					expand: true,
					cwd: 'node_modules/chart.js/dist',
					src: '*.js', dest: adminJs, filter: 'isFile'
				}, {
					expand: true,
					cwd: 'node_modules/chart.js/dist',
					src: '*.css', dest: adminCss, filter: 'isFile'
				}],
			},
			buildDir: {
				files: [{
					expand: true,
					src: [
						'*.php', '*.txt',
						'public/**/**.php', 'public/**/**.min.js', 'public/**/**.min.css',
						'admin/**/**.php', 'admin/**/**.min.css', 'admin/**/**.min.js',
						'assets/**', 'includes/**',
						'languages/*.mo', 'libs/**',
					],
					dest: 'build/'
				}],
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
		uglify: {
			admin: {
				files: {
					[adminJs + 'demovox-admin.min.js']: [adminJs + 'demovox-admin.js']
				}
			}
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
					archive: '../demovox.zip'
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
			build: ['build/'],
			public: [pubCss + '*.css', pubCss + '*.map', pubJs + '*.min.js', pubJs + '*.map',],
			admin: [adminCss + '*.css', adminCss + '*.map', adminJs + 'demovox-admin.min.js', adminJs + 'Chart.*',],
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
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-checkwpversion');

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-mkdir');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-po2mo');

	// define Tasks
	grunt.registerTask('default', 'availabletasks');
	grunt.registerTask('buildAssets', [
		'checkDependencies', 'clean', 'webpack:prod', 'copy:adminAssets', 'uglify', 'sass', 'cssmin', 'po2mo',
	]);
	grunt.registerTask('buildZip', [
		'checkDependencies', 'clean', 'translations', 'webpack:prod', 'copy:adminAssets', 'uglify', 'sass', 'cssmin', 'po2mo',
		'mkdir', 'copy:buildDir', 'compress', 'clean',
	]);
};
