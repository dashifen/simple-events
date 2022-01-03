const {src, dest, parallel, task, watch} = require('gulp'),
  browserify = require('browserify'),
  buffer = require('vinyl-buffer'),
  sass = require('gulp-sass'),
  source = require('vinyl-source-stream'),
  uglify = require('gulp-uglify');

function postmetaPluginJs() {
  return browserify({entries: ['assets/scripts/postmeta/postmeta.js']})
    .transform('babelify', {presets: ['@babel/preset-env', '@babel/preset-react']})
    .bundle()
    .pipe(source('simple-events-postmeta.min.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(dest('assets'));
}

function blockJs() {
  return browserify({entries: ['assets/scripts/blocks/blocks.js']})
    .transform('babelify', {presets: ['@babel/preset-env', '@babel/preset-react']})
    .bundle()
    .pipe(source('simple-events-blocks.min.js'))
    .pipe(buffer())
    .pipe(uglify())
    .pipe(dest('assets'));
}

async function build() {
  parallel(postmetaPluginJs, blockJs);
}

function watcher() {
  blockJs();
  postmetaPluginJs();
  watch(['assets/scripts/postmeta/*.js'], postmetaPluginJs);
  watch(['assets/scripts/blocks/*.js'], blockJs);
}

task('postmeta', postmetaPluginJs);
task('blocks', blockJs);
task('default', build);
task('watch', watcher);
task('build', build);
