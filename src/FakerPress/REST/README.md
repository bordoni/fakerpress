# FakerPress REST API

This directory contains the REST API implementation for FakerPress, providing modern REST endpoints to replace the existing AJAX-based functionality.

## Structure

```
REST/
├── Controller.php              # Main REST controller
├── Interface_Endpoint.php      # Interface for all endpoints
├── Abstract_Endpoint.php       # Base class for endpoints
├── OpenAPI.php                # OpenAPI documentation utilities
├── Endpoints/                 # Individual endpoint classes
│   ├── Documentation.php      # API documentation endpoint
│   └── .gitkeep               # Placeholder for future endpoints
└── README.md                  # This file
```

## Architecture

### Controller
The `Controller` class is the main entry point for the REST API. It:
- Registers all endpoint routes
- Manages endpoint loading
- Provides OpenAPI documentation generation
- Handles common functionality like permissions

### Interface_Endpoint
Defines the contract that all endpoint classes must implement:
- `register_routes()` - Register REST routes
- `get_base_route()` - Get the base route path
- `get_permission_required()` - Get required capability
- `check_permission()` - Check user permissions
- `validate_request()` - Validate request parameters
- `sanitize_request()` - Sanitize request data
- `get_openapi_schema()` - Generate OpenAPI documentation
- `get_request_schema()` - Define request parameter schema
- `get_response_schema()` - Define response data schema

### Abstract_Endpoint
Provides common functionality for all endpoints:
- Route registration logic
- Parameter validation and sanitization
- Standardized response methods
- OpenAPI schema generation
- Error handling

### OpenAPI
Utility class for generating OpenAPI 3.0 documentation:
- Common schemas for requests/responses
- Parameter definitions
- Security schemes
- Example data generation

## Usage

### Creating a New Endpoint

1. Create a new class in the `Endpoints/` directory
2. Extend `Abstract_Endpoint`
3. Implement required abstract methods
4. Add the class to the endpoint filter in `Controller::load_endpoints()`

Example:
```php
<?php
namespace FakerPress\REST\Endpoints;

use FakerPress\REST\Abstract_Endpoint;
use WP_REST_Server;

class Posts extends Abstract_Endpoint {
    protected $base_route = '/posts';
    protected $permission_required = 'edit_posts';

    protected function get_routes() {
        return [
            '/generate' => [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'generate_posts' ],
                'permission_callback' => [ $this, 'check_permission' ],
                'args'                => $this->get_request_schema(),
            ],
        ];
    }

    public function generate_posts( $request ) {
        // Implementation here
    }

    public function get_request_schema() {
        // Define request parameters
    }

    public function get_response_schema() {
        // Define response structure
    }
}
```

### API Endpoints

The REST API will be available at: `/wp-json/fakerpress/v1/`

#### Documentation Endpoints
- `GET /docs` - Get complete API documentation
- `GET /docs/openapi` - Get OpenAPI specification

#### Module Endpoints (to be created)
- `POST /posts/generate` - Generate fake posts
- `POST /users/generate` - Generate fake users
- `POST /terms/generate` - Generate fake terms
- `POST /comments/generate` - Generate fake comments
- `POST /attachments/generate` - Generate fake attachments
- `POST /meta/generate` - Generate fake meta data

### Authentication

All endpoints require WordPress authentication:
- Cookie authentication for logged-in users
- Nonce verification for CSRF protection
- Capability-based permissions

### Response Format

All endpoints return standardized responses:

Success:
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

Error:
```json
{
  "success": false,
  "code": "error_code",
  "message": "Error description",
  "data": { ... }
}
```

### OpenAPI Documentation

The API includes full OpenAPI 3.0 documentation accessible at:
- `/wp-json/fakerpress/v1/docs` - Wrapped documentation
- `/wp-json/fakerpress/v1/docs/openapi` - Raw OpenAPI spec

This can be used with tools like Swagger UI, Postman, or other API documentation tools.

## Migration from AJAX

When migrating from AJAX endpoints:

1. Identify the AJAX action in `Ajax.php`
2. Create a corresponding REST endpoint
3. Move the logic to the new endpoint class
4. Update the frontend to use REST API calls
5. Maintain backward compatibility during transition

## Security

- All endpoints require user authentication
- Nonce verification for CSRF protection
- Capability-based access control
- Input validation and sanitization
- Rate limiting (can be added via filters)

## Testing

REST endpoints can be tested using:
- WordPress REST API test tools
- Postman with the OpenAPI specification
- cURL commands
- Unit tests with WP_REST_Request objects 
