const gulp = require('gulp'),
  browserify = require('browserify'),
  buffer = require('vinyl-buffer'),
  source = require('vinyl-source-stream'),
  uglify = require('gulp-uglify'),
  touch = require('gulp-touch-fd');

function jsTask() {
  return browserify({entries: ['assets/scripts/app.js']})
    .transform('babelify', {presets: ['@babel/preset-env', '@babel/preset-react']})
    .bundle()
    .pipe(source('simple-events.min.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(gulp.dest('assets'))
    .pipe(touch());
}

gulp.task('default', () => {
  jsTask();
});

gulp.task('build', () => {
  jsTask();
});

gulp.task('watch', () => {
  gulp.watch('assets/scripts/*.js', () => {
    jsTask();
  });
});


