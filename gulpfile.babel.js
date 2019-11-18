import yargs from 'yargs';

const isProduction = yargs.argv.prod ? true : false;

import gulp from 'gulp';
import postcss from 'gulp-postcss';
import atImport from 'gulp-import';
import autoprefixer from 'autoprefixer';
import concat from 'gulp-concat';
import uglify from 'gulp-uglify';
import rename from 'gulp-rename';
import cleanCSS from 'gulp-clean-css';

const paths = {
	styles: {
		src: 'src/resources/pcss/**/*.pcss',
		dest: 'src/resources/css/'
	},
	scripts: {
		src: 'src/resources/js/**/*.js',
		dest: 'src/resources/js/'
	}
};

/*
 * You can also declare named functions and export them as tasks
 */
export function styles() {
	const plugins = [
		atImport,
		autoprefixer,
	];

	return gulp.src( paths.styles.src )
		.pipe( postcss( plugins ) )
		.pipe( cleanCSS() )
		.pipe( uglify() )
		// pass in options to the stream
		.pipe( rename( {
			basename: 'main',
			suffix: '.min'
		} ) )
		.pipe( gulp.dest( paths.styles.dest ) );
}

export function scripts() {
	return gulp.src( paths.scripts.src, { sourcemaps: true } )
		.pipe( uglify() )
		.pipe( concat( 'main.min.js' ) )
		.pipe( gulp.dest( paths.scripts.dest ) );
}

 /*
	* You could even use `export as` to rename exported tasks
	*/
function watchFiles() {
	gulp.watch( paths.scripts.src, scripts );
	gulp.watch( paths.styles.src, styles );
}
export { watchFiles as watch };

const build = gulp.series( clean, gulp.parallel( styles, scripts ) );
/*
 * Export a default task
 */
export default build;