'use strict';
module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        replace: {
            deploy: {
                options: {
                    patterns: [{
                        match: 'version',
                        replacement: '<%= pkg.version %>'
                    }, {
                        match: 'timestamp',
                        replacement: '<%= grunt.template.today() %>'
                    }]
                },
                files: [{
                    expand: true,
                    flatten: true,
                    src: ['smyles-bug-report.php'],
                    dest: 'dist/<%= pkg.version %>/<%= pkg.name %>/'
                }]
            }
        },

        copy: {
            deploy: {
                src: [
                    '**', '!Gruntfile.js',
                    '!dist/**',
                    '!package.json',
                    '!node_modules/**'
                ],
                dest: 'dist/<%= pkg.version %>/<%= pkg.name %>/',
                expand: true
            }
        },
    });

    grunt.loadNpmTasks('grunt-replace');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('deploy', ['copy:deploy', 'replace:deploy']);

};