'use strict';
module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		core: {
			css: 'assets/css/core',
			js: 'assets/js/core'
		},
		vendor: {
			js: 'assets/js/vendor',
			css: 'assets/css/vendor'
		},
		build: {
			js: 'assets/js/build',
			css: 'assets/css/build'
		},
		min: {
			css: 'assets/css',
			js: 'assets/js'
		},
		watch: {
			options: {

			},
			js: {
				files: [ '<%= core.js %>/*.js', '<%= vendor.js %>/*.js' ],
				tasks: ['concat', 'cssmin', 'uglify']
			},
			css: {
				files: [ '<%= core.css %>/*.css', '<%= vendor.css %>/*.css' ],
				tasks: ['concat', 'cssmin', 'uglify']
			},
			less: {
				files: [ '<%= core.css %>/*.less' ],
				tasks: ['less']
			}
		},
		less: {
			core: {
				options: {
					paths: ["<%= core.css %>"],
					cleancss: true
				},
				files: {
					"<%= core.css %>/bug-report.css": "<%= core.css %>/bug-report.less"
				}
			}
		},
		concat: {
			corecss: {
				options: {
					stripBanners: true,
					banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("yyyy-mm-dd") %> */'
				},
				src: [
					'<%= core.css %>/*.css'
				],
				dest: '<%= build.css %>/core.css'
			},
			corejs: {
				src: [
					'<%= core.js %>/*.js'
				],
				dest: '<%= build.js %>/core.js'
			},
			vendorcss: {
				options: {
					stripBanners: true,
					banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' +
					'<%= grunt.template.today("yyyy-mm-dd") %> */'
				},
				src: [
					'<%= vendor.css %>/*.css'
				],
				dest: '<%= build.css %>/vendor.css'
			},
			vendorjs: {
				src: [
					'<%= vendor.js %>/*.js'
				],
				dest: '<%= build.js %>/vendor.js'
			}
		},
		cssmin: {
			core: {
				src: '<%= concat.corecss.dest %>',
				dest: '<%= min.css %>/core.min.css'
			},
			vendor: {
				src: '<%= concat.vendorcss.dest %>',
				dest: '<%= min.css %>/vendor.min.css'
			}
		},
		uglify: {
			vendor: {
				options: {
					preserveComments: 'none',
					compress: {
						drop_console: true,
						global_defs: {
							"DEBUG": false
						}
					}
				},
				files: {
					'<%= min.js %>/vendor.min.js': ['<%= concat.vendorjs.dest %>']
				}
			},
			core: {
				options: {
					preserveComments: 'none',
					compress: {
						drop_console: true,
						global_defs: {
							"DEBUG": false
						}
					}
				},
				files: {
					'<%= min.js %>/core.min.js': ['<%= concat.corejs.dest %>']
				}
			}
		}
	});
	grunt.loadNpmTasks('grunt-shell');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-less');

	grunt.registerTask('default', ['less']);
	grunt.registerTask('all', ['less', 'concat', 'cssmin', 'uglify']);

	grunt.registerTask('vendor', ['concat:vendorcss', 'concat:vendorjs', 'cssmin:vendor', 'uglify:vendor']);
	grunt.registerTask('core', ['less:core','concat:corecss', 'concat:corejs', 'cssmin:core', 'uglify:core']);

};