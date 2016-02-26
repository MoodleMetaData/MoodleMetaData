var path = require('path'),
    gulp = require('gulp'),
    less = require('gulp-less'),
    rename = require("gulp-rename"),
    minifyCSS = require('gulp-minify-css'),
    browserify = require('gulp-browserify'),
    uglify = require('gulp-uglify'),
    stringify = require('stringify');  // This is needed to avoid using paths for templates.

var paths = {
    js: ['./main.js',
        './controllers/**/*.js',
        './models/**/*.js',
        './views/**/*.js',
        './views/**/*.html'],  // Since .html are converted to js strings, we watch them too for changes.
    images: './img/**/*.*',
    less: ['./less/**/*.less']
};

// LESS/CSS section.
gulp.task('css', function () {
    gulp.src('./less/main.less')
        .pipe(less({
            paths: [path.join(__dirname, 'less', 'includes')]
        }))
        .pipe(gulp.dest('./public/css'))
        .pipe(minifyCSS({keepBreaks: false}))
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('./public/css'));
});

// JS section.
gulp.task('js', function() {
    gulp.src('./main.js')
        .pipe(browserify({
            transform: stringify({ extensions: ['.html'], minify: true })
        }))
        .pipe(gulp.dest('./public/js'))
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('./public/js'));
});

// Images
gulp.task('images', function() {
    gulp.src(paths.images)
        .pipe(gulp.dest('./public/img'));
});

// Watcher section.
gulp.task('watchers', function () {
    gulp.watch(paths.less, ['css']);
    gulp.watch(paths.js, ['js']);
    gulp.watch(paths.images, ['images']);
});

gulp.task('default', ['css', 'js', 'images', 'watchers']);