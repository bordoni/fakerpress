# FakerPress — Agent & Developer Guide

## Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| PHP | 8.1+ | MAMP, Homebrew, or system PHP |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org) |
| Node | 18.17 | Matches `.nvmrc` — use `nvm use` |
| npm | 9.6.7 | Ships with Node 18.17 |
| WordPress | 6.4+ | Local install (MAMP, Local, Valet, etc.) |

## Quick Start

```bash
# 1. Install PHP dependencies + build vendor-prefixed packages (Strauss)
composer install

# 2. Install Node dependencies
nvm use && npm install

# 3. Build JS and CSS assets
npm run build
```

After these three steps the plugin is ready to activate in WordPress.

## Build Commands

### PHP (Composer)

| Command | What it does |
|---------|-------------|
| `composer install` | Install deps + run Strauss (auto via post-install hook) |
| `composer strauss` | Re-run Strauss manually (namespace-prefix third-party packages into `vendor-prefixed/`) |
| `composer dump-autoload` | Regenerate autoloader without reinstalling |

Strauss vendor-prefixes all third-party packages under `FakerPress\ThirdParty\` into `vendor-prefixed/`. This runs automatically on `composer install` and `composer update`.

### JS / CSS (npm + @wordpress/scripts)

| Command | What it does |
|---------|-------------|
| `npm run build` | Production build — compiles JS and PostCSS into `build/` and `src/resources/css/` |
| `npm run start` | Watch mode with source maps for development |
| `npm run lint` | Lint JS and CSS |
| `npm run format:js` | Auto-fix JS lint issues |
| `npm run format:css` | Auto-fix CSS lint issues |

**Source files:**
- JS: `src/resources/js/*.js` — compiled to `build/js/` and exposed on `window.fakerpress`
- CSS: `src/resources/pcss/*.pcss` — PostCSS compiled to `src/resources/css/`
- Packages: `src/resources/packages/` — modern module entry points compiled to `build/packages/`

**Build tool:** `@wordpress/scripts` with custom webpack config (`webpack.config.js`) using `@stellarwp/tyson` helpers.

## Gitignored Build Artifacts

These directories are generated and must NOT be committed:

- `vendor/` — Composer dependencies
- `vendor-prefixed/` — Strauss-prefixed dependencies
- `build/` — Compiled JS/CSS bundles
- `src/resources/css/` — Compiled CSS output
- `node_modules/` — Node dependencies
- `bin/` — Downloaded tool binaries (strauss.phar, etc.)

## Project Structure

```
fakerpress.php                  # Plugin entry point, defines __FP_FILE__
src/
├── FakerPress/
│   ├── Plugin.php              # Boot, container bindings, autoload
│   ├── Module/                 # Data generation modules (Post, User, Term, Comment, Attachment)
│   │   ├── Abstract_Module.php # set() → generate() → save() chain
│   │   └── Factory.php         # Module registry
│   ├── Provider/               # Faker providers (WP_Post, WP_User, HTML, Image/*)
│   ├── REST/                   # REST API (v1) — endpoints, controller, OpenAPI docs
│   │   ├── Controller.php      # Service provider, registers routes on rest_api_init
│   │   ├── Abstract_Endpoint.php
│   │   └── Endpoints/          # Posts, Users, Terms, Comments, Attachments, Documentation
│   ├── Admin/                  # Admin pages and views
│   ├── Ajax.php                # Legacy AJAX handlers (Select2 only, generate moved to REST)
│   ├── Assets.php              # Script/style registration
│   └── Field.php               # Legacy form field renderer (deprecated, do not extend)
├── functions/                  # Helper functions (container.php, load.php, etc.)
├── resources/
│   ├── js/                     # Source JS files
│   ├── pcss/                   # Source PostCSS files
│   └── img/                    # Static images (SVG icon)
└── templates/                  # PHP view templates
```

## Architecture Notes

- **Container:** DI52 (vendor-prefixed). Use helpers: `singleton()`, `make()`, `bind()`, `register()`
- **Module lifecycle:** `$module->set(params)->generate()->save()` — `parse_request()` orchestrates this
- **REST namespace:** `fakerpress/v1` — endpoints at `/fakerpress/v1/{module}/generate`
- **Service Providers:** extend `FakerPress\Contracts\Service_Provider`, registered via `Plugin::register()`
- **Version:** `Plugin::VERSION` in `src/FakerPress/Plugin.php`

## Coding Standards

- **PHP:** WordPress Coding Standards. Short array syntax (`[]`). Early returns to reduce nesting.
- **JavaScript:** ES6 with `@wordpress/scripts` linting. Modules exposed on `window.fakerpress`.
- **CSS:** PostCSS with `postcss-nested` (not native CSS nesting). `&` means "this element".
- **Docblocks:** `@since 0.9.0` for new code. Aligned params. Period-terminated descriptions.
- **Namespacing:** PSR-4 autoloading under `FakerPress\`. Third-party code under `FakerPress\ThirdParty\`.
- **Security:** Nonce verification on all requests. Capability checks via `get_permission_required()`. Sanitize all input.
