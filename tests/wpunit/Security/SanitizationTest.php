<?php
/**
 * Tests for security sanitization across modules and REST endpoints.
 *
 * @since 0.9.0
 *
 * @package FakerPress\Tests\Security
 */

namespace FakerPress\Tests\Security;

use FakerPress\REST\Endpoints\Posts;

/**
 * Class SanitizationTest
 *
 * Verifies that array and object parameters are properly sanitized
 * in the REST Abstract_Endpoint.
 *
 * @since 0.9.0
 */
class SanitizationTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Get a concrete endpoint instance for testing protected methods.
	 *
	 * @return Posts
	 */
	private function get_endpoint(): Posts {
		return new Posts();
	}

	/**
	 * Verify that sanitize_array_parameter strips HTML tags from strings.
	 *
	 * @test
	 */
	public function it_should_sanitize_strings_in_arrays(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_array_parameter' );
		$method->setAccessible( true );

		$input  = [ '<script>alert("xss")</script>', 'clean value', '<b>bold</b>' ];
		$result = $method->invoke( $endpoint, $input, 'test_param' );

		// sanitize_text_field() strips script tags and their content entirely.
		$this->assertSame( '', $result[0] );
		$this->assertSame( 'clean value', $result[1] );
		$this->assertSame( 'bold', $result[2] );
	}

	/**
	 * Verify that sanitize_array_parameter preserves numeric types.
	 *
	 * @test
	 */
	public function it_should_preserve_numeric_types_in_arrays(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_array_parameter' );
		$method->setAccessible( true );

		$input  = [ 42, 3.14, '100', 0 ];
		$result = $method->invoke( $endpoint, $input, 'test_param' );

		$this->assertSame( 42, $result[0] );
		$this->assertSame( 3.14, $result[1] );
		$this->assertSame( 100, $result[2] );
		$this->assertSame( 0, $result[3] );
	}

	/**
	 * Verify that sanitize_array_parameter preserves boolean values.
	 *
	 * @test
	 */
	public function it_should_preserve_booleans_in_arrays(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_array_parameter' );
		$method->setAccessible( true );

		$input  = [ true, false ];
		$result = $method->invoke( $endpoint, $input, 'test_param' );

		$this->assertTrue( $result[0] );
		$this->assertFalse( $result[1] );
	}

	/**
	 * Verify that sanitize_array_parameter handles nested arrays recursively.
	 *
	 * @test
	 */
	public function it_should_sanitize_nested_arrays(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_array_parameter' );
		$method->setAccessible( true );

		$input  = [ [ '<em>emphasis</em>', 42 ], 'clean' ];
		$result = $method->invoke( $endpoint, $input, 'test_param' );

		$this->assertSame( 'emphasis', $result[0][0] );
		$this->assertSame( 42, $result[0][1] );
		$this->assertSame( 'clean', $result[1] );
	}

	/**
	 * Verify that sanitize_array_parameter returns empty array for non-array input.
	 *
	 * @test
	 */
	public function it_should_return_empty_array_for_non_array_input(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_array_parameter' );
		$method->setAccessible( true );

		$this->assertSame( [], $method->invoke( $endpoint, 'string', 'test_param' ) );
		$this->assertSame( [], $method->invoke( $endpoint, 42, 'test_param' ) );
		$this->assertSame( [], $method->invoke( $endpoint, null, 'test_param' ) );
	}

	/**
	 * Verify that sanitize_object_parameter sanitizes string values.
	 *
	 * @test
	 */
	public function it_should_sanitize_strings_in_objects(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_object_parameter' );
		$method->setAccessible( true );

		$input  = [ 'name' => '<b>bold</b> text', 'value' => 'clean' ];
		$result = $method->invoke( $endpoint, $input, 'test_param' );

		$this->assertIsObject( $result );
		$this->assertSame( 'bold text', $result->name );
		$this->assertSame( 'clean', $result->value );
	}

	/**
	 * Verify that sanitize_object_parameter returns empty object for invalid input.
	 *
	 * @test
	 */
	public function it_should_return_empty_object_for_invalid_input(): void {
		$endpoint = $this->get_endpoint();
		$method   = new \ReflectionMethod( $endpoint, 'sanitize_object_parameter' );
		$method->setAccessible( true );

		$result = $method->invoke( $endpoint, 'string', 'test_param' );
		$this->assertIsObject( $result );
		$this->assertEmpty( (array) $result );
	}
}
