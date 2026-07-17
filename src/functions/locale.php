<?php
namespace FakerPress;

/**
 * Locale helper functions for FakerPress.
 *
 * @since 0.10.0
 *
 * @package FakerPress
 */

/**
 * Returns the curated list of available Faker locales for FakerPress.
 *
 * Each locale code corresponds to a directory under
 * vendor/prefixed/fakerphp/faker/src/Faker/Provider/.
 *
 * @since 0.10.0
 *
 * @return array Associative array of locale code => human-readable label.
 */
function fakerpress_get_available_locales(): array {
	return [
		'en_US' => 'English (United States)',
		'fr_FR' => 'French (France)',
		'de_DE' => 'German (Germany)',
		'es_ES' => 'Spanish (Spain)',
		'pt_BR' => 'Portuguese (Brazil)',
		'it_IT' => 'Italian (Italy)',
		'nl_NL' => 'Dutch (Netherlands)',
		'ja_JP' => 'Japanese (Japan)',
		'zh_CN' => 'Chinese (Simplified, China)',
		'ru_RU' => 'Russian (Russia)',
	];
}
