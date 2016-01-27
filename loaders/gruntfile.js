module.exports = function(grunt) {

    // 1. All configuration goes here 
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concurrent: {
            options: {
                logConcurrentOutput: true,
            },
            dev: {
                tasks: [
                    'watch:scriptsDev',
                    'watch:cssDev',
                ],
            },
        },

        concat: {
            options: {
                separator: ';',
            },
            dist: {
                src: ['js/libs/*.js','js/module.js'],
                dest: 'js/main.prod.js',
            }
        },

        uglify: {
            build: {
                src: 'js/main.prod.js',
                dest: 'js/main.prod.min.js',
            }
        },

        imagemin: {
            dynamic: {
                files: [{
                    expand: true,
                    cwd: 'images/',
                    src: ['*.{png,jpg,gif}'],
                    dest: 'images/build/',
                }]
            }
        },

        sass: {
            dist: {
                options: {
                    style: 'compressed',
                },
                files: {
                    'css/main.css': 'sass/main.scss',
                    'css/styleguide.css': 'sass/styleguide.scss',
                }
            },
            dev: {
                options: {
                    style: 'expanded',
                },
                files: {
                    'css/main.css': 'sass/main.scss',
                    'css/styleguide.css': 'sass/styleguide.scss',
                }
            }
        },

        autoprefixer: {
            dist: {
                files: {
                    'css/main.css': 'css/main.css',
                    'css/styleguide.css': 'css/styleguide.css',
                }
            }
        },

        kss: {
            options: {
                //includeType: 'css',
                //includePath: 'css/main.css',
                template: 'styleguide-template',
                mask: 'styleguide.css',
            },
            dist: {
                files: {
                    'kss-styleguide': ['css'],
                }
            }
        },

        watch: {
            scriptsProd: {
                files: ['js/*.js'],
                tasks: ['concat','uglify'],
                options: {
                    spawn: false
                }
            },
            scriptsDev: {
                files: ['js/*.js'],
                tasks: ['concat'],
                options: {
                    spawn: false
                }
            },
            cssProd: {
                files: ['sass/*.scss'],
                tasks: ['sass','autoprefixer'],
                options: {
                    spawn: false
                }
            },
            cssDev: {
                files: ['sass/*.scss', 'sass/**/*.scss'],
                tasks: ['sass:dev','autoprefixer'],
                options: {
                    spawn: false
                }
            }
        },

    });

    // 3. Where we tell Grunt we plan to use this plug-in.
    grunt.loadNpmTasks('grunt-concurrent');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-kss');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default',[
        'concat',
        'sass:dev',
        'autoprefixer'
    ]);

    grunt.registerTask('prod',[
        'concat',
        'uglify',
        'imagemin',
        'sass',
        'autoprefixer',
    ]);

    grunt.registerTask('dev',[
        'concat',
        'sass:dev',
        'autoprefixer'
    ]);

    grunt.registerTask('styleguide', [
        'kss'
    ]);

    /*grunt.registerTask('watch',[
        'concurrent:dev'
    ]);*/

};