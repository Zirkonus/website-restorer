var gulp = require('gulp');
var addsrc = require('gulp-add-src');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var tinypng = require('gulp-tinypng-compress');
var autoprefixer = require('gulp-autoprefixer');
var uglify = require('gulp-uglify');
var ngmin = require('gulp-ngmin');
var templateCache = require('gulp-angular-templatecache');

//
//
//

// Main dashboard application

var appSassSources = [
    './node_modules/normalize.css/normalize.css',
    './node_modules/ng-dialog/css/ngDialog.min.css',
    './node_modules/ng-dialog/css/ngDialog-theme-default.min.css',
    './app/**/*.scss',
    './scss/style.scss'
];

var appJsSources = [
    './node_modules/angular/angular.min.js',
    './node_modules/angular-ui-router/release/angular-ui-router.min.js',
    './node_modules/angular-animate/angular-animate.min.js',
    './node_modules/angular-sanitize/angular-sanitize.min.js',
    './node_modules/angular-clipboard/angular-clipboard.js',
    './node_modules/ng-file-upload/dist/ng-file-upload.min.js',
    './node_modules/angular-scroll/angular-scroll.min.js',
    './node_modules/ng-disable-scroll/disable-scroll.min.js',
    './node_modules/ng-infinite-scroll/build/ng-infinite-scroll.min.js',
    './node_modules/ng-dialog/js/ngDialog.min.js',
    './app/app.module.js',
    './app/**/*js'
];

// Admin dashboard application

var adminSassSources = [
    './node_modules/normalize.css/normalize.css',
    './appAdmin/**/*.scss',
    './scss/styleAdmin.scss'
];

var adminJsSources = [
    './node_modules/angular/angular.min.js',
    './node_modules/angular-ui-router/release/angular-ui-router.min.js',
    './node_modules/angular-animate/angular-animate.min.js',
    './node_modules/angular-sanitize/angular-sanitize.min.js',
    './node_modules/angular-clipboard/angular-clipboard.js',
    './appAdmin/app.module.js',
    './appAdmin/**/*js'
];


// Auth module apllication

var authSassSources = [
    './node_modules/normalize.css/normalize.css',
    './auth/**/*.scss',
    './scss/auth.scss'
];

var authJsSources = [
    './node_modules/angular/angular.min.js',
    './node_modules/angular-ui-router/release/angular-ui-router.min.js',
    './node_modules/angular-animate/angular-animate.min.js',
    './node_modules/angular-sanitize/angular-sanitize.min.js',
    './node_modules/angular-clipboard/angular-clipboard.js',
    './auth/app.module.js',
    './auth/**/*js'
];

//
//
//

gulp.task('app-sass', function () {
  return gulp.src(appSassSources)
      .pipe(sass().on('error', sass.logError))
      .pipe(autoprefixer({
          browsers: ['last 60 versions'],
          cascade: false
        }))
      .pipe(concat("style.css"))
      .pipe(gulp.dest('./../public/assets/css/'));
});


gulp.task('admin-sass', function () {
    return gulp.src(adminSassSources)
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['last 60 versions'],
            cascade: false
        }))
        .pipe(concat("styleAdmin.css"))
        .pipe(gulp.dest('./../public/assets/css/'));
});


gulp.task('auth-sass', function () {
    return gulp.src(authSassSources)
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer({
            browsers: ['last 60 versions'],
            cascade: false
        }))
        .pipe(concat("auth.css"))
        .pipe(gulp.dest('./../public/assets/css/'));
});

// gulp.task('sass-min', function () {
//     return gulp.src(['./scss/variables.scss', './scss/mixin.scss',  './scss/style.scss', './app/**/*.scss', '!./node_modules/**/*.scss'])
//         .pipe(concat("style.min.css"))
//         .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
//         .pipe(autoprefixer({
//             browsers: ['last 16 versions'],
//             cascade: false
//         }))
//         .pipe(gulp.dest('./../public/assets/css/'));
// });

gulp.task('app-build', function () {
    return gulp.src('./app/**/*html')
        .pipe(templateCache('template.js', {module: 'App', root: '/app'}))
        .pipe(addsrc(appJsSources))
        .pipe(concat('build.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./../public/app/'));
});

gulp.task('admin-build', function () {
    return gulp.src(adminJsSources)
        .pipe(concat('build.js'))
        .pipe(gulp.dest('./../public/appAdmin/'));
});

gulp.task('auth-build', function () {
    return gulp.src(authJsSources)
        .pipe(concat('build.js'))
        .pipe(gulp.dest('./../public/auth/'));
});

// gulp.task('compress', function () {
//     return gulp.src(['./app/**/*.module.js', './app/**/*.js'])
//         .pipe(concat('build.min.js'))
//         .pipe(uglify())
//         .pipe(gulp.dest('./../public/app/'));
// });


gulp.task('admin-template', function () {
    return gulp.src(['./appAdmin/**/*.html'], {base:"./appAdmin"})
        .pipe(gulp.dest('./../public/appAdmin/'));
});

gulp.task('auth-template', function () {
    return gulp.src(['./auth/**/*.html', './auth/**/*.php'], {base:"./auth"})
        .pipe(gulp.dest('./../public/auth/'));
});

gulp.task('app-watch', function () {
    gulp.watch(appSassSources, ['app-sass']);
    gulp.watch([appJsSources, './app/**/*html'], ['app-build']);
});

gulp.task('admin-watch', function () {
    gulp.watch(adminSassSources, ['admin-sass']);
    gulp.watch(adminJsSources, ['admin-build']);
    gulp.watch(['./appAdmin/**/*.html'], ['admin-template']);
});

gulp.task('auth-watch', function () {
    gulp.watch(authSassSources, ['auth-sass']);
    gulp.watch(authJsSources, ['auth-build']);
    gulp.watch(['./auth/**/*.html'], ['auth-template']);
});

gulp.task('build', ['app-sass', 'app-build', 'admin-sass', 'admin-build', 'admin-template']);

gulp.task('app', ['app-sass', 'app-build', 'app-watch']);
gulp.task('admin', ['admin-sass', 'admin-build', 'admin-template', 'admin-watch']);
gulp.task('auth', ['auth-sass', 'auth-build', 'auth-template', 'auth-watch']);

gulp.task('default', ['app', 'admin', 'auth']);