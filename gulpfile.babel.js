import yargs from 'yargs';

const isProduction = yargs.argv.prod ? true : false;

import gulp from 'gulp';
import header from 'gulp-header';

import postcss from 'gulp-postcss';
import postcssImport from 'postcss-import';
import postcssMixins from 'postcss-mixins';
import postcssHexrgba from 'postcss-hexrgba';
import postcssNested from 'postcss-nested';
import postcssInlineSvg from 'postcss-inline-svg';
import postcssCalc from 'postcss-calc';
import cssMqpacker from 'css-mqpacker';
import autoprefixer from 'autoprefixer';

import concat from 'gulp-concat';
import uglify from 'gulp-uglify';
import rename from 'gulp-rename';
import cleanCSS from 'gulp-clean-css';
import del from 'del';

const paths = {
	styles: {
		src: [
			'src/resources/pcss/**/*.pcss',
			'!src/resources/pcss/**/_*.pcss'
		],
		dest: 'src/resources/css/'
	},
	scripts: {
		src: [
			'src/resources/js/**/*.js',
			'!src/resources/js/**/*.min.js',
		],
		dest: 'src/resources/js/'
	}
};

const banner = [
	'/**',
	' * This CSS file was auto-generated via PostCSS',
	' *',
	' * Contributors should avoid editing this file, but instead edit the associated',
	' * src/resources/pcss/ file. For more information, check out the repository.',
	' *',
	' * @see: http://github.com/bordoni/fakerpress',
	' */',
	'',
	'',
].join( '\n' );

export const clean = () => del( [ 'src/resources/js/**/*.min.js', 'src/resources/css/**/*.css' ] );

/*
 * You can also declare named functions and export them as tasks
 */
export function styles() {
	const plugins = [
		autoprefixer,
		postcssImport,
		postcssMixins,
		postcssNested,
		postcssInlineSvg,
		postcssCalc,
		postcssHexrgba,
		cssMqpacker,
	];

	return gulp.src( paths.styles.src )
		.pipe( postcss( plugins ) )
		.pipe( cleanCSS() )
		// pass in options to the stream
		.pipe( rename( {
			suffix: '.min',
			extname: '.css'
		} ) )
		.pipe( gulp.dest( paths.styles.dest ) );
}

export function scripts() {
	return gulp.src( paths.scripts.src, { sourcemaps: true } )
		.pipe( uglify() )
		.pipe( rename( {
			suffix: '.min'
		} ) )
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