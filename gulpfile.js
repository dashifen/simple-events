const gulp = require('gulp'),
  browserify = require('browserify'),
  buffer = require('vinyl-buffer'),
  source = require('vinyl-source-stream'),
  uglify = require('gulp-uglify'),
  touch = require('gulp-touch-fd');

/*
 * [TASK FUNCTION] Build JS files.
 */
function jsTask() {
  return browserify({entries: ['assets/scripts/simple-events.js']})
    .transform('babelify', {presets: ['@babel/preset-env', '@babel/preset-react']})
    .bundle()
    .pipe(source('simple-events.min.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(gulp.dest('assets'))
    .pipe(touch());
}

function buildTask(done) {
  jsTask();
  done();
}

gulp.task('default', buildTask);
