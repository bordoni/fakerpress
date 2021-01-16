<?php
namespace FakerPress;

Class Readme {
	public function parse_readme( $file, $version = null ) {
		$file_contents = @implode( '', @file( $file ) );
		return $this->parse_readme_contents( $file_contents, $version );
	}

	public function parse_readme_contents( $file_contents, $version = false ) {
		$file_contents = str_replace( [ "\r\n", "\r" ], "\n", $file_contents );
		$file_contents = trim( $file_contents );
		if ( 0 === strpos( $file_contents, "\xEF\xBB\xBF" ) )
			$file_contents = substr( $file_contents, 3 );

		// Markdown transformations
		$file_contents = preg_replace( "|^###([^#]+)#*?\s*?\n|im", '=$1='."\n",     $file_contents );
		$file_contents = preg_replace( "|^##([^#]+)#*?\s*?\n|im",  '==$1=='."\n",   $file_contents );
		$file_contents = preg_replace( "|^#([^#]+)#*?\s*?\n|im",   '===$1==='."\n", $file_contents );

		// === Plugin Name ===
		// Must be the very first thing.
		if ( ! preg_match_all('/^([=]*) ([^=]*) ([=]*)/im', $file_contents, $search_sections_name ) ) {
			return [];
		}

		$valid_section_indexes = array_keys( array_filter(
			$search_sections_name[1],
			static function ( $value ) {
				return '==' === $value;
			}
		) );

		$content = $file_contents;

		$sections['headers'] = [
			'name' => 'headers',
			'content' => null,
		];

		foreach ( $valid_section_indexes as  $index ) {
			$name = $search_sections_name[2][ $index ];
			$sections[ strtolower( $name ) ] = $section = [
				'name' => $name,
				'content' => '',
			];

			$parts = explode( '== ' . $name . ' ==', $content );

			if ( ! $sections['headers']['content'] ) {
				$sections['headers']['content'] = $parts[0];
			} elseif ( isset( $last_name ) ) {
				$sections[ strtolower( $last_name ) ]['content'] = $parts[0];
			}

			if ( ! empty( $parts[1] )  ) {
				$content = $parts[1];
			}

			$last_name = $name;
		}

		$sections[ strtolower( $last_name ) ]['content'] = $content;

		if ( isset( $sections['changelog'] ) ) {
			$sections['changelog']['versions'] = $this->parse_changelog_section( $sections['changelog']['content'], $version );
		}

		return $sections;
	}

	protected static function str_replace_first( $search, $replace, $subject ) {
		$pos = strpos( $subject, $search );
		if ( false !== $pos ) {
			return substr_replace( $subject, $replace, $pos, strlen( $search ) );
		}

		return $subject;
	}

	public function parse_changelog_section( $content, $only_version = null ) {
		$versions = [];

		if ( ! preg_match_all('/^(?:[=]*) ([^=]*) (?:[=]*)/im', $content, $versions_search ) ) {
			return [];
		}

		$versions_titles = $versions_search[1];
		$count = 0;

		foreach ( $versions_titles as $versions_title ) {
			$separator = false;
			if ( false !== strpos( $versions_title, '&mdash;' ) ) {
				$separator = '&mdash;';
			} elseif ( false !== strpos( $versions_title, '-' ) ) {
				$separator = '-';
			}

			if ( ! $separator ) {
				continue;
			}

			$version_title_parts = explode( $separator, $versions_title );
			$version_number = trim( $version_title_parts[0] );
			$version_content_parts = explode( '= ' . $versions_title . ' =', $content );

			$version = [
				'number' => $version_number,
				'date'    => trim( $version_title_parts[1] ),
				'content' => null,
			];

			if ( isset( $last_version ) ) {
				$versions[ $last_version ]['content'] = trim( $version_content_parts[0] );
			}

			if ( ! empty( $version_content_parts[1] )  ) {
				$content = $version_content_parts[1];
			}

			$last_version = $version['number'];

			$versions[ $version['number'] ] = $version;
			$count++;
		}

		$versions[ $last_version ]['content'] = $content;

		// Dont parse headers.
		Utils\Slimdown::remove_rule( '/(#+)(.*)/' );

		foreach ( $versions as &$version ) {
			if ( ! is_null( $only_version ) && ! in_array( $version['number'], (array) $only_version ) ) {
				continue;
			}

			$contents = explode( "\n", $version['content'] );
			$html = [
				'<ul>',
			];
			foreach ( $contents as $change ) {
				$change = trim( $change );
				if ( empty( $change ) ) {
					continue;
				}
				$piece = '<li>' . Utils\Slimdown::render( static::str_replace_first( '*', '', $change ) ) . '</li>';

				$html[] = $piece;
			}

			$html[] = '</ul>';

			$version['html'] = implode( "\n", $html );
		}

		$versions = array_filter( $versions, static function( $version ) {
			return ! empty( $version['html'] );
		} );

		return $versions;
	}

	function user_sanitize( $text, $strict = false ) { // whitelisted chars
		if ( function_exists('user_sanitize') ) // bbPress native
			return user_sanitize( $text, $strict );

		if ( $strict ) {
			$text = preg_replace('/[^a-z0-9-]/i', '', $text);
			$text = preg_replace('|-+|', '-', $text);
		} else {
			$text = preg_replace('/[^a-z0-9_-]/i', '', $text);
		}
		return $text;
	}

	function sanitize_text( $text ) { // not fancy
		$text = strip_tags($text);
		$text = esc_html($text);
		$text = trim($text);
		return $text;
	}

	function filter_text( $text, $markdown = false ) { // fancy, Markdown
		$text = trim($text);
		$text = call_user_func( array( __CLASS__, 'code_trick' ), $text, $markdown ); // A better parser than Markdown's for: backticks -> CODE

		if ( $markdown ) { // Parse markdown.
			// Parse markdown
		}

		$allowed = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
				'rel' => array()),
			'blockquote' => array('cite' => array()),
			'br' => array(),
			'p' => array(),
			'code' => array(),
			'pre' => array(),
			'em' => array(),
			'strong' => array(),
			'ul' => array(),
			'ol' => array(),
			'li' => array(),
			'h3' => array(),
			'h4' => array()
		);

		$text = balanceTags($text);

		$text = wp_kses( $text, $allowed );
		$text = trim($text);
		return $text;
	}

	function code_trick( $text, $markdown ) { // Don't use bbPress native function - it's incompatible with Markdown
		// If doing markdown, first take any user formatted code blocks and turn them into backticks so that
		// markdown will preserve things like underscores in code blocks
		if ( $markdown )
			$text = preg_replace_callback("!(<pre><code>|<code>)(.*?)(</code></pre>|</code>)!s", array( __CLASS__,'decodeit'), $text);

		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		if ( !$markdown ) {
			// This gets the "inline" code blocks, but can't be used with Markdown.
			$text = preg_replace_callback("|(`)(.*?)`|", array( __CLASS__, 'encodeit'), $text);
			// This gets the "block level" code blocks and converts them to PRE CODE
			$text = preg_replace_callback("!(^|\n)`(.*?)`!s", array( __CLASS__, 'encodeit'), $text);
		} else {
			// Markdown can do inline code, we convert bbPress style block level code to Markdown style
			$text = preg_replace_callback("!(^|\n)([ \t]*?)`(.*?)`!s", array( __CLASS__, 'indent'), $text);
		}
		return $text;
	}

	function indent( $matches ) {
		$text = $matches[3];
		$text = preg_replace('|^|m', $matches[2] . '    ', $text);
		return $matches[1] . $text;
	}

	function encodeit( $matches ) {
		if ( function_exists('encodeit') ) // bbPress native
			return encodeit( $matches );

		$text = trim($matches[2]);
		$text = htmlspecialchars($text, ENT_QUOTES);
		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		$text = preg_replace("|\n\n\n+|", "\n\n", $text);
		$text = str_replace('&amp;lt;', '&lt;', $text);
		$text = str_replace('&amp;gt;', '&gt;', $text);
		$text = "<code>$text</code>";
		if ( "`" != $matches[1] )
			$text = "<pre>$text</pre>";
		return $text;
	}

	function decodeit( $matches ) {
		if ( function_exists('decodeit') ) // bbPress native
			return decodeit( $matches );

		$text = $matches[2];
		$trans_table = array_flip(get_html_translation_table(HTML_ENTITIES));
		$text = strtr($text, $trans_table);
		$text = str_replace('<br />', '', $text);
		$text = str_replace('&#38;', '&', $text);
		$text = str_replace('&#39;', "'", $text);
		if ( '<pre><code>' == $matches[1] )
			$text = "\n$text\n";
		return "`$text`";
	}

}
