var gulp = require('gulp'),
    postcss = require('gulp-postcss'),
    presetEnv = require('postcss-preset-env'), 
    precss = require('precss'),
    concat = require('gulp-concat'), 
    uglify = require('gulp-uglify-es').default, 
    cssnano = require('gulp-cssnano'), 
    include = require('gulp-file-include'),
    ftp = require('vinyl-ftp');

var root = '_new-codebase/front',
    templatePath = root + '/templates/stock',
    buildFolder = 'build',
    buildName = 'build.min',
    remotePath = '_new-codebase/front/templates/stock/' + buildFolder;
var conn = ftp.create({
    host: '185.4.67.251',
    user: 'crm.r97.ru',
    password: 'vqE(0Hi&7$A^NFOd',
    parallel: 10
});
var css = [
        root + '/vendor/normalize.css',
        root + '/vendor/air-datepicker/css/datepicker.min.css',
        root + '/vendor/fancybox/jquery.fancybox.min.css',
        templatePath + '/.src/scss/main/vars.scss',
        templatePath + '/.src/scss/main/mixins.scss',
        templatePath + '/.src/scss/**/*.scss'
    ],
    js = [
        root + '/vendor/jquery.min.js',
        root + '/vendor/air-datepicker/js/datepicker.min.js',
        root + '/vendor/fancybox/jquery.fancybox.min.js',
        templatePath + '/.src/js/**/*.js'
    ];

///////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////// Tasks ////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////

gulp.task('css', () => {
    return gulp.src(css)
        .pipe(concat(buildName + '.css'))
        .pipe(postcss([precss, presetEnv]))
        .pipe(cssnano())
        .pipe(gulp.dest(templatePath + '/' + buildFolder))
        .pipe(conn.dest(remotePath));
});


gulp.task('js', () => {
    return gulp.src(js)
        .pipe(concat(buildName + '.js'))
        .pipe(include({
            prefix: '@@',
            basepath: root
        }))
        .pipe(uglify())
        .pipe(gulp.dest(templatePath + '/' + buildFolder))
        .pipe(conn.dest(remotePath));
});


gulp.watch(css, gulp.parallel('css'));
gulp.watch(js, gulp.parallel('js'));


gulp.task('default',
    gulp.parallel(
        'css',
        'js'));