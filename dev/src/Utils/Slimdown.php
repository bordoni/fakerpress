<?php
namespace FakerPress\Dev\Utils;

/**
 * Slimdown - A very basic regex-based Markdown parser. Supports the
 * following elements (and can be extended via Slimdown::add_rule()):
 *
 * - Headers
 * - Links
 * - Bold
 * - Emphasis
 * - Deletions
 * - Quotes
 * - Inline code
 * - Blockquotes
 * - Ordered/unordered lists
 * - Horizontal rules
 *
 * Author: Johnny Broadway <johnny@johnnybroadway.com>
 * Website: https://gist.github.com/jbroadway/2836900
 * License: MIT
 */
class Slimdown {
	/**
	 * All of the rules for replacement.
	 *
	 * @since 0.5.2
	 *
	 * @var array
	 */
	protected static $rules = [
		'/`(.*?)`/' => 'self::code',                              // inline code
		'/(#+)(.*)/' => 'self::header',                           // headers
		'/\[([^\[]+)\]\(([^\)]+)\)/' => '<a href=\'\2\'>\1</a>',  // links
		'/(\*\*|__)(.*?)\1/' => '<strong>\2</strong>',            // bold
		'/(\*|_)(.*?)\1/' => '<em>\2</em>',                       // emphasis
		'/\~\~(.*?)\~\~/' => '<del>\1</del>',                     // del
		'/\:\"(.*?)\"\:/' => '<q>\1</q>',                         // quote
		'/\n\*(.*)/' => 'self::ul_list',                          // ul lists
		'/\n[0-9]+\.(.*)/' => 'self::ol_list',                    // ol lists
		'/\n(&gt;|\>)(.*)/' => 'self::blockquote ',               // blockquotes
		'/\n-{5,}/' => "\n<hr />",                                // horizontal rule
		'/\n([^\n]+)\n/' => 'self::para',                         // add paragraphs
		'/<\/ul>\s?<ul>/' => '',                                  // fix extra ul
		'/<\/ol>\s?<ol>/' => '',                                  // fix extra ol
		'/<\/blockquote><blockquote>/' => "\n",                   // fix extra blockquote
		'/<\/code><code>/' => "",                                 // fix extra code
		'/<code>(.*?)<\/code>/' => 'self::code_contents',         // put the contents back into code
	];

	/**
	 * Replacement for the codeblocks, which prevent problems with inline elements inside of the codeblocks.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Which elements
	 *
	 * @return string
	 */
	protected static $code_values = [];

	/**
	 * Renders the inline code from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function code( $regs ) {
		$item = $regs[1];
		$key = md5( $item );
		static::$code_values[ $key ] = $item;

		return sprintf( "<code>%s</code>", $key );
	}

	/**
	 * Renders the code contents replacements from the values code blocks from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function code_contents( $regs ) {
		$key = $regs[1];
		return sprintf( "<code>%s</code>", static::$code_values[ $key ] );
	}

	/**
	 * Renders the paragraph from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function para( $regs ) {
		$line = $regs[1];
		$trimmed = trim( $line );
		if ( preg_match( '/^<\/?(ul|ol|li|h|p|bl)/', $trimmed ) ) {
			return "\n" . $line . "\n";
		}
		return sprintf( "\n<p>%s</p>\n", $trimmed );
	}

	/**
	 * Renders the UL list from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function ul_list( $regs ) {
		$item = $regs[1];
		return sprintf( "\n<ul>\n\t<li>%s</li>\n</ul>", trim( $item ) );
	}

	/**
	 * Renders the OL list from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function ol_list( $regs ) {
		$item = $regs[1];
		return sprintf( "\n<ol>\n\t<li>%s</li>\n</ol>", trim( $item ) );
	}

	/**
	 * Renders the Blockquotes from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function blockquote( $regs ) {
		$item = $regs[2];
		return sprintf( "\n<blockquote>%s</blockquote>", trim( $item ) );
	}

	/**
	 * Renders the Headers from Markdown.
	 *
	 * @since 0.5.2
	 *
	 * @param array $regs Regular EXP results.
	 *
	 * @return string
	 */
	protected static function header( $regs ) {
		list( $tmp, $chars, $header ) = $regs;
		$level = strlen( $chars );
		return sprintf( '<h%d>%s</h%d>', $level, trim( $header ), $level );
	}

	/**
	 * Add a rule.
	 *
	 * @since 0.5.2
	 *
	 * @param string          $regex       Which regex we are adding to the list.
	 * @param string|callable $replacement Replacement for the regex passed.
	 *
	 * @return void
	 */
	public static function add_rule( $regex, $replacement ) {
		static::$rules[ $regex ] = $replacement;
	}

	/**
	 * Remove a rule.
	 *
	 * @since 0.5.2
	 *
	 * @param string $regex Which regex we are deleting from the list.
	 *
	 * @return bool
	 */
	public static function remove_rule( $regex ) {
		if ( ! isset( static::$rules[ $regex ] ) ) {
			return false;
		}

		unset( static::$rules[ $regex ] );
		return true;
	}

	/**
	 * Render some Markdown into HTML.
	 *
	 * @since 0.5.2
	 *
	 * @param string $text Text to convert form Markdown to HTML.
	 *
	 * @return string
	 */
	public static function render( $text ) {
		$text = "\n" . $text . "\n";
		foreach ( static::$rules as $regex => $replacement ) {
			if ( is_callable( $replacement ) ) {
				$text = preg_replace_callback( $regex, $replacement, $text );
			} else {
				$text = preg_replace( $regex, $replacement, $text );
			}
		}
		return trim( $text );
	}
}