const {src, dest, parallel, task, watch} = require('gulp'),
  browserify = require('browserify'),
  buffer = require('vinyl-buffer'),
  sass = require('gulp-sass'),
  source = require('vinyl-source-stream'),
  uglify = require('gulp-uglify');

function postmetaPluginJs() {
  return browserify({entries: ['assets/scripts/postmeta/index.js']})
    .transform('babelify', {presets: ['@babel/preset-env', '@babel/preset-react']})
    .bundle()
    .pipe(source('simple-events-postmeta.min.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(dest('assets'));
}

async function build() {
  parallel(postmetaPluginJs);
}

function watcher() {
  postmetaPluginJs();
  watch(['assets/scripts/postmeta/*.js'], postmetaPluginJs());
}

task('postmeta', postmetaPluginJs);
task('default', build);
task('watch', watcher);
task('build', build);
