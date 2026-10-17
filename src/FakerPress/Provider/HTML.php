<?php
/**
 * Faker provider for random HTML content.
 *
 * @package FakerPress
 */

namespace FakerPress\Provider;

use FakerPress\Module\Attachment;
use FakerPress\Provider\Image\LoremPicsum;
use FakerPress\Provider\Image\Placeholder;
use FakerPress\Utils;
use FakerPress\ThirdParty\Faker\Provider\Base;
use FakerPress\ThirdParty\Faker\Provider\Lorem;
use FakerPress\ThirdParty\Faker\Provider\Internet;
use function FakerPress\make;

/**
 * Generates random HTML markup (headings, paragraphs, lists, links, images, more tags) for post content.
 */
class HTML extends Base {
	/**
	 * Sets up the provider and registers the Internet and image providers it relies on.
	 *
	 * @param \FakerPress\ThirdParty\Faker\Generator $generator Faker generator this provider is added to.
	 */
	public function __construct( \FakerPress\ThirdParty\Faker\Generator $generator ) {
		parent::__construct( $generator );

		$provider = new Internet( $this->generator );
		$this->generator->addProvider( $provider );

		$provider = new Image\Placeholder( $this->generator );
		$this->generator->addProvider( $provider );

		$provider = new Image\LoremPicsum( $this->generator );
		$this->generator->addProvider( $provider );
	}

	/**
	 * Set of elements based on a grouping.
	 *
	 * @since 0.1.0
	 *
	 * @var string[][]
	 */
	public static $sets = [
		'self_close' => [ 'img', 'hr', '!--more--' ],
		'header'     => [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
		'list'       => [ 'ul', 'ol' ],
		'block'      => [ 'div', 'p', 'blockquote' ],
		'item'       => [ 'li' ],
		'inline'     => [
			'b',
			'big',
			'i',
			'small',
			'tt',
			'abbr',
			'cite',
			'code',
			'em',
			'strong',
			'a',
			'bdo',
			'br',
			'img',
			'q',
			'span',
			'sub',
			'sup',
			'hr',
		],
		'wp'         => [ '!--more--' ],
	];

	/**
	 * Tells whether an element name is not an HTML comment (such as `!--more--`), for use with array_filter().
	 *
	 * @since 0.1.0
	 *
	 * @param string $element Element name or markup to test.
	 *
	 * @return bool True when the element is not an HTML comment.
	 */
	private function filter_html_comments( $element = '' ) {
		return ! preg_match( '/<?!--(.*?)-->?/i', $element );
	}

	/**
	 * Checks if a haystack of elements contains the needle provided.
	 *
	 * @since 0.1.0
	 *
	 * @param string   $needle   Element name to look for, matched as a regular expression fragment.
	 * @param string[] $haystack Element names to search.
	 *
	 * @return bool True when at least one element matches.
	 */
	private function has_element( $needle = '', $haystack = [] ) {
		$needle   = trim( $needle );
		$filtered = array_filter(
			$haystack,
			static function ( $element ) use ( $needle ) {
				return preg_match( "/<?(!--)? ?({$needle})+ ?(--)?>?/i", $element ) !== 0;
			} 
		);

		return count( $filtered ) > 0;
	}

	/**
	 * Generates a list of random HTML elements.
	 *
	 * Headings never follow each other or close the content, lists are not repeated within two
	 * elements, and a `<!--more-->` tag is added once around the middle when `elements` allows it.
	 *
	 * @param array $args {
	 *     Optional. Generation arguments.
	 *
	 *     @type int[]|int $qty                 Number of elements, or a [ min, max ] range. Default [ 5, 25 ].
	 *     @type string[]  $elements            Element names to pick from. Default headings, lists and blocks.
	 *     @type array     $attr                Attributes added to every element. Default empty.
	 *     @type string[]  $exclude             Element names never picked. Default [ 'div' ].
	 *     @type bool      $allow_html_comments Whether comment elements can be picked. Default false.
	 * }
	 *
	 * @return array<int, string|false> The HTML of each element (false when an element failed, such as an image).
	 */
	public function html_elements( $args = [] ) {
		$html = [];

		$defaults = [
			'qty'                 => [ 5, 25 ],
			'elements'            => array_merge( self::$sets['header'], self::$sets['list'], self::$sets['block'] ),
			'attr'                => [],
			'exclude'             => [ 'div' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- HTML element names to skip, not a query argument.
			'allow_html_comments' => false,
		];

		$args                   = (object) wp_parse_args( $args, $defaults );
		$args->did_more_element = false;

		// Randomize the quantity based on range.
		$args->qty = make( Utils::class )->get_qty_from_range( $args->qty );

		$max_to_more = ( $args->qty / 2 ) + $this->generator->numberBetween( 0, max( floor( $args->qty / 2 ), 1 ) );
		$min_to_more = ( $args->qty / 2 ) - $this->generator->numberBetween( 0, max( floor( $args->qty / 2 ), 1 ) );

		for ( $i = 0; $i < $args->qty; $i++ ) {
			$exclude = $args->exclude;
			if ( isset( $element ) ) {
				// Here we check if we need to exclude some elements from the next
				// This one is to exclude header elements from apearing one after the other, or in the end of the string.
				if ( in_array( $element, self::$sets['header'] ) || $args->qty - 1 === $i ) {
					$exclude = array_merge( (array) $exclude, self::$sets['header'] );
				} elseif ( $i > 1 && ( in_array( $els[ $i - 1 ], self::$sets['list'] ) || in_array( $els[ $i - 2 ], self::$sets['list'] ) ) ) {
					$exclude = array_merge( (array) $exclude, self::$sets['list'] );
				}
			}

			$elements = array_diff( $args->elements, $exclude );

			if ( ! $args->allow_html_comments ) {
				$elements = array_filter( $elements, [ $this, 'filter_html_comments' ] );
			}

			$element = Base::randomElement( $elements );
			$els[]   = $element;

			$html[] = $this->element( $element, $args->attr, null, $args );

			if (
				$this->generator->numberBetween( 0, 100 ) <= 80
				&& ! $args->did_more_element
				&& $args->qty > 2
				&& $this->has_element( '!--more--', $args->elements )
				&& $i < $max_to_more
				&& $i > $min_to_more
			) {
				$html[]                 = $this->element( '!--more--' );
				$args->did_more_element = true;
			}
		}

		return (array) $html;
	}

	/**
	 * Fills in the attributes of an <img> element: an optional alignment class, alt text and the image source.
	 *
	 * @since 0.2.0
	 *
	 * @param object|array $element Element with `name` and `attr` keys.
	 * @param string[]     $sources Image provider IDs to pick from when a new image has to be created.
	 *
	 * @return object|\WP_Error The element with its attributes set, or the error from creating the image.
	 */
	private function html_element_img( $element, $sources = [ Placeholder::ID, LoremPicsum::ID ] ) {
		if ( is_array( $element ) ) {
			$element = (object) $element;
		}

		if ( ! isset( $element->attr['class'] ) ) {
			$element->attr['class'][] = $this->generator->optional( 0.4, null )->randomElement( [ 'aligncenter', 'alignleft', 'alignright' ] );
			$element->attr['class']   = array_filter( $element->attr['class'] );
			$element->attr['class']   = implode( ' ', $element->attr['class'] );
		}

		if ( ! isset( $element->attr['alt'] ) ) {
			$element->attr['alt'] = rtrim( $this->generator->optional( 0.7, '' )->sentence( Base::randomDigitNotNull() ), '.' );
		}

		if ( ! isset( $element->attr['src'] ) ) {
			$image_src = $this->get_img_src( $sources );
			if ( is_wp_error( $image_src ) ) {
				return $image_src;
			}

			$element->attr['src'] = $image_src;

		}

		$element->attr = array_filter( $element->attr );

		return $element;
	}

	/**
	 * Generates image URLs for the <img> elements, will fetch attachments as a basis for the inline images.
	 *
	 * @since 0.4.0
	 *
	 * Reuses an attachment generated by FakerPress 20% of the time when there are any, otherwise
	 * creates a new attachment from one of the image providers.
	 *
	 * @param string[] $sources Image provider IDs to pick from when a new attachment is created.
	 *
	 * @return false|string|\WP_Error The attachment URL, false when it has none, or the error from creating it.
	 */
	public function get_img_src( $sources = [ Placeholder::ID, LoremPicsum::ID ] ) {
		$images       = \FakerPress\Module\Post::fetch( [ 'post_type' => 'attachment' ] );
		$image        = false;
		$count_images = count( $images );

		if ( $count_images > 0 ) {
			$image = $this->generator->optional( 0.2, $image )->randomElement( $images );
		}

		if ( false === $image ) {
			$image = make( Attachment::class )
				->set( 'attachment_url', $this->generator->randomElement( $sources ) )
				->generate()->save();
		}

		if ( is_wp_error( $image ) ) {
			return $image;
		}

		return wp_get_attachment_url( $image );
	}

	/**
	 * Randomly apply a given element to an existing text.
	 *
	 * @since 0.1.0
	 *
	 * The text is split into word groups and up to `$max` of them are wrapped in the element.
	 *
	 * @param string      $element Element name to wrap the word groups in.
	 * @param int         $max     Maximum number of word groups to wrap.
	 * @param string|null $text    Text to apply the element to.
	 *
	 * @return string|null The text with the elements applied, or the text unchanged when none were picked.
	 */
	public function random_apply_element( $element = 'a', $max = 5, $text = null ) {
		$words       = explode( ' ', $text );
		$total_words = count( $words );
		$sentences   = [];

		for ( $i = 0; $i < $total_words; $i++ ) {
			$group    = Base::numberBetween( 1, Base::numberBetween( 3, 9 ) );
			$sentence = [];

			for ( $k = 0; $k < $group; $k++ ) {
				$index = $i + $k;

				if ( ! isset( $words[ $index ] ) ) {
					break;
				}

				$sentence[] = $words[ $index ];

			}
			$i += $k;

			$sentences[] = implode( ' ', $sentence );
		}

		$qty = $max - Base::numberBetween( 0, $max );

		if ( 0 === $qty ) {
			return $text;
		}

		$indexes = floor( count( $sentences ) / $qty );

		for ( $i = 0; $i < $qty; $i++ ) {
			$index = ( $indexes * $i ) + Base::numberBetween( 0, $indexes );

			if ( isset( $sentences[ $index ] ) ) {
				$sentences[ $index ] = $this->element( $element, [], $sentences[ $index ] );
			}
		}

		return implode( ' ', $sentences );
	}

	/**
	 * Generates a given Element by name with a set of attrs and text based on a set of arguments.
	 *
	 * @since 0.1.0
	 *
	 * Links get a random title and URL, images get their source from get_img_src(), and elements
	 * without `$text` get random content suited to their type (lists get `<li>` children).
	 *
	 * @param string      $name Element name, e.g. `p`, `h2`, `img` or `!--more--`.
	 * @param array       $attr Attributes for the element.
	 * @param string|null $text Inner content; null generates it.
	 * @param object|null $args Arguments from html_elements(); `sources` sets the image providers.
	 *
	 * @return false|string The element HTML, or false when the name is empty or the image failed.
	 */
	public function element( $name = 'div', $attr = [], $text = null, $args = null ) {
		$element = (object) [
			'name' => $name,
			'attr' => $attr,
		];

		if ( empty( $element->name ) ) {
			return false;
		}

		$element->one_liner = in_array( $element->name, self::$sets['self_close'] );

		$html = [];

		if ( 'a' === $element->name ) {
			if ( ! isset( $element->attr['title'] ) ) {
				$element->attr['title'] = Lorem::sentence( Base::numberBetween( 1, Base::numberBetween( 3, 9 ) ) );
			}
			if ( ! isset( $element->attr['href'] ) ) {
				$element->attr['href'] = $this->generator->url();
			}
		}

		if ( 'img' === $element->name ) {
			$sources = [ LoremPicsum::ID, Placeholder::ID ];
			if ( is_object( $args ) && $args->sources ) {
				$sources = $args->sources;
			}
			$element = $this->html_element_img( $element, $sources );

			// When we fail to create the image we just bail.
			if ( is_wp_error( $element ) ) {
				return false;
			}
		}

		$attributes = [];
		foreach ( $element->attr as $key => $value ) {
			$attributes[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}

		$html[] = sprintf( '<%s%s>', $element->name, ( ! empty( $attributes ) ? ' ' : '' ) . implode( ' ', $attributes ) );

		if ( ! $element->one_liner ) {
			if ( ! is_null( $text ) ) {
				$html[] = $text;
			} elseif ( in_array( $element->name, self::$sets['inline'] ) ) {
				$text   = Lorem::text( Base::numberBetween( 5, 25 ) );
				$html[] = substr( $text, 0, strlen( $text ) - 1 );
			} elseif ( in_array( $element->name, self::$sets['item'] ) ) {
				$text   = Lorem::text( Base::numberBetween( 10, 60 ) );
				$html[] = substr( $text, 0, strlen( $text ) - 1 );
			} elseif ( in_array( $element->name, self::$sets['list'] ) ) {
				for ( $i = 0; $i < Base::numberBetween( 1, 15 ); $i++ ) { // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed -- Kept as is, hoisting the random bound would change the generated list-length distribution.
					$html[] = $this->element( 'li' );
				}
			} elseif ( in_array( $element->name, self::$sets['header'] ) ) {
				$text   = Lorem::text( Base::numberBetween( 60, 200 ) );
				$html[] = substr( $text, 0, - 1 );
			} else {
				$html[] = $this->random_apply_element( 'a', Base::numberBetween( 0, 10 ), Lorem::paragraph( Base::numberBetween( 2, 40 ) ) );
			}

			$html[] = sprintf( '</%s>', $element->name );
		}

		return implode( '', $html );
	}
}
