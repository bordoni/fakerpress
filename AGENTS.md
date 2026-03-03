# FakerPress — Agent & Developer Guide

## Prerequisites

| Tool | Version | Notes |
|------|---------|-------|
| PHP | 8.1+ | MAMP, Homebrew, or system PHP |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org) |
| Node | 18+ | Required by Bun / @wordpress/scripts |
| Bun | 1.x | [bun.sh](https://bun.sh) |
| WordPress | 6.4+ | Local install (MAMP, Local, Valet, etc.) |

## Quick Start

```bash
# 1. Install PHP dependencies + build vendor/prefixed packages (Strauss)
composer install

# 2. Install Node dependencies
bun install

# 3. Build JS and CSS assets
bun run build
```

After these three steps the plugin is ready to activate in WordPress.

## Build Commands

### PHP (Composer)

| Command | What it does |
|---------|-------------|
| `composer install` | Install deps + run Strauss (auto via post-install hook) |
| `composer strauss` | Re-run Strauss manually (namespace-prefix third-party packages into `vendor/prefixed/`) |
| `composer dump-autoload` | Regenerate autoloader without reinstalling |

Strauss vendor-prefixes all third-party packages under `FakerPress\ThirdParty\` into `vendor/prefixed/`. This runs automatically on `composer install` and `composer update`.

### JS / CSS (bun + @wordpress/scripts)

| Command | What it does |
|---------|-------------|
| `bun run build` | Production build — compiles JS and PostCSS into `build/` and `src/resources/css/` |
| `bun run start` | Watch mode with source maps for development |
| `bun run lint` | Lint JS and CSS |
| `bun run format:js` | Auto-fix JS lint issues |
| `bun run format:css` | Auto-fix CSS lint issues |

**Source files:**
- JS: `src/resources/js/*.js` — compiled to `build/js/` and exposed on `window.fakerpress`
- CSS: `src/resources/pcss/*.pcss` — PostCSS compiled to `src/resources/css/`
- Packages: `src/resources/packages/` — modern module entry points compiled to `build/packages/`

**Build tool:** `@wordpress/scripts` with custom webpack config (`webpack.config.js`) using `@stellarwp/tyson` helpers.

## Gitignored Build Artifacts

These directories are generated and must NOT be committed:

- `vendor/` — Composer dependencies (includes `vendor/prefixed/` — Strauss-prefixed packages)
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

- **Container:** DI52 (vendor/prefixed). Use helpers: `singleton()`, `make()`, `bind()`, `register()`
- **Module lifecycle:** `$module->set(params)->generate()->save()` — `parse_request()` orchestrates this
- **REST namespace:** `fakerpress/v1` — endpoints at `/fakerpress/v1/{module}/generate`
- **Service Providers:** extend `FakerPress\Contracts\Service_Provider`, registered via `Plugin::register()`
- **Version:** `Plugin::VERSION` in `src/FakerPress/Plugin.php`

## Testing

### Test Commands

| Command | What it does |
|---------|-------------|
| `composer test:wpunit` | Run WPUnit integration tests via Codeception |
| `vendor/bin/codecept run wpunit` | Run WPUnit tests directly |
| `composer lint:php` | Run PHPCS code standards checks |
| `composer lint:php:fix` | Auto-fix PHPCS violations with PHPCBF |

### Test Directory Structure

```
tests/
├── _bootstrap.php              # Global test bootstrap
├── _data/                      # Test fixtures and data
├── _output/                    # Test output (gitignored)
├── _support/
│   ├── WpunitTester.php        # Actor class
│   ├── _generated/             # Auto-generated actor methods (gitignored)
│   └── Helper/
│       └── Wpunit.php          # Custom helper module
├── wpunit.suite.dist.yml       # Suite config (WPLoader + Asserts)
└── wpunit/
    ├── _bootstrap.php          # Suite bootstrap (WPLoader handles WP)
    └── PluginTest.php          # Plugin smoke tests
```

### Running Tests Locally

1. Create a `.env.testing` file (gitignored) with your local WordPress paths:
   ```
   WP_ROOT_FOLDER=/path/to/wordpress
   WP_URL=http://fakerpress.test
   WP_DOMAIN=fakerpress.test
   WP_DB_URL=mysql://root:root@127.0.0.1:3306/fakerpress_test
   WP_TABLE_PREFIX=wp_
   ```
2. Run `composer test:wpunit`

### Running Tests with SLIC

SLIC containers use `codeception.slic.yml` and `.env.testing.slic` automatically.

## Tailwind CSS in WordPress Admin

Tailwind is used in `src/resources/packages/admin/` and compiled into `build/admin.css`. Two rules must always hold:

### Never use `@import "tailwindcss"` — use partial imports

`@import "tailwindcss" prefix(fp)` silently injects Tailwind's global preflight reset (`*`, `html`, `h1–h6`, `a`, `img`, `button`, etc.) into the page. The `prefix(fp)` modifier only renames utility classes — it does **not** scope resets. In the WordPress admin this breaks heading sizes, link underlines, form elements, and global layout.

Always use the two partial imports instead:

```css
@import "tailwindcss/theme" prefix(fp);
@import "tailwindcss/utilities" prefix(fp);
```

Then add a minimal scoped reset targeting only `#fakerpress-react-root *` in `@layer base` (already present in `globals.css`).

### Never try to strip `:not(#\#)` shims with a PostCSS plugin

Tailwind v4 injects `:not(#\#)` cascade-compatibility shims via `@tailwindcss/node`'s LightningCSS optimizer inside the `@tailwindcss/postcss` `Once` hook. These shims land in `result.root` **after** PostCSS `OnceExit` fires, making them invisible to any PostCSS plugin you add after `@tailwindcss/postcss`.

The correct fix is the `StripTailwindLayerHacksPlugin` webpack plugin in `webpack.config.js`, which strips them from compiled `.css` assets at `PROCESS_ASSETS_STAGE_DERIVED` (before `RtlCssPlugin`).

## Coding Standards

- **PHP:** WordPress Coding Standards. Short array syntax (`[]`). Early returns to reduce nesting.
- **JavaScript:** ES6 with `@wordpress/scripts` linting. Modules exposed on `window.fakerpress`.
- **CSS:** PostCSS with `postcss-nested` (not native CSS nesting). `&` means "this element".
- **Docblocks:** `@since <current_version_in_dev>` for new code (check `Plugin::VERSION`). Aligned params. Period-terminated descriptions.
- **Namespacing:** PSR-4 autoloading under `FakerPress\`. Third-party code under `FakerPress\ThirdParty\`.
- **Security:** Nonce verification on all requests. Capability checks via `get_permission_required()`. Sanitize all input.

### PHPCS — Validating PHP Code

**Before writing or modifying PHP code**, internalize these rules so you write compliant code on the first pass — do NOT write code and then fix lint errors in a second pass.

Key rules enforced by `phpcs.xml` (WordPress + StellarWP + VIP-Go):
- **Tabs for indentation**, never spaces. All PHP files use tabs.
- **Yoda conditions disabled** — write `$var === true`, not `true === $var`.
- **Spaces inside parentheses** — `if ( $condition )`, not `if ($condition)`.
- **Spaces after commas** — `func( $a, $b )`, not `func($a,$b)`.
- **Opening brace on same line** — `function foo() {`, `if ( $x ) {`.
- **`snake_case`** for functions and variables, `UPPER_SNAKE` for constants.
- **Short array syntax** — `[]` not `array()` (`Generic.Arrays.DisallowLongArraySyntax`).
- **No `else`** — prefer early returns over `else` blocks.
- **Strict comparisons** — `===` / `!==`, never `==` / `!=`.
- **Escaping output** — `esc_html()`, `esc_attr()`, `wp_kses()`, etc. on all echoed values.
- **Nonce verification** — `wp_verify_nonce()` / `check_ajax_referer()` on all form/AJAX handlers.
- **Direct DB queries** — use `$wpdb->prepare()`, never interpolate. Excluded in `src/Test.php`.
- **No `extract()`**, no `eval()`, no `compact()` in new code.

Run `composer lint:php -- <file>` to check a single file. Run `composer lint:php:fix -- <file>` to auto-fix.

When making changes to PHP files, run `composer lint:php -- <changed-files>` against only the files you touched before considering the task done. Do NOT run PHPCS against the entire codebase.
