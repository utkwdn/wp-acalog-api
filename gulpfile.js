// Load plugins
var gulp      = require('gulp');


// Create a distribution version
// ===============================================================

// copy the dist directoy
gulp.task('distdist', function () {
      return gulp.src('dist/**')
      .pipe( gulp.dest('packaged/wp-acalog-api/dist') );
});
// copy the plugin-update-checker directoy
gulp.task('distchecker', function () {
      return gulp.src('plugin-update-checker/**')
      .pipe( gulp.dest('packaged/wp-acalog-api/plugin-update-checker') );
});
gulp.task('distsrc', function () {
      return gulp.src('src/**')
      .pipe( gulp.dest('packaged/wp-acalog-api/src') );
});
// copy the plugin.php
gulp.task('distplugin', function () {
      return gulp.src('plugin.php')
      .pipe( gulp.dest('packaged/wp-acalog-api') );
});



gulp.task('package', gulp.series('distdist','distchecker', 'distsrc', 'distplugin'));
