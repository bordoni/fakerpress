<?php
/**
 * Interface for FakerPress REST API endpoints.
 *
 * Defines the contract that all REST endpoint classes must implement.
 *
 * @since TBD
 * @package FakerPress
 */

namespace FakerPress\REST;

use WP_REST_Request;

/**
 * Interface Interface_Endpoint
 *
 * Contract for all REST API endpoint implementations.
 *
 * @since 0.9.0
 */
interface Interface_Endpoint {

	/**
	 * Register the routes for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return void
	 */
	public function register_routes();

	/**
	 * Get the base route for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return string
	 */
	public function get_base_route();

	/**
	 * Get the permission required to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return string
	 */
	public function get_permission_required();

	/**
	 * Check if the current user has permission to access this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return bool|\WP_Error
	 */
	public function check_permission( WP_REST_Request $request );

	/**
	 * Validate the request parameters.
	 *
	 * @since 0.9.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return bool|\WP_Error
	 */
	public function validate_request( $request );

	/**
	 * Sanitize the request parameters.
	 *
	 * @since 0.9.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return array
	 */
	public function sanitize_request( $request );

	/**
	 * Get the OpenAPI schema for this endpoint.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_openapi_schema();

	/**
	 * Get the schema for request parameters.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_request_schema();

	/**
	 * Get the schema for response data.
	 *
	 * @since 0.9.0
	 *
	 * @return array
	 */
	public function get_response_schema();
} 
