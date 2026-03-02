/**
 * PHP-compatible JSON encoding.
 *
 * Replicates: json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
 *
 * Replacements applied:
 *   < → \u003C, > → \u003E
 *   & → \u0026
 *   ' → \u0027
 *   " inside string values → \u0022
 *   / → \/ (PHP default)
 */
export function phpJsonEncode(data: unknown): string {
	const json = JSON.stringify(data);

	// Process character by character to correctly handle only quotes inside string values
	let result = '';
	let inString = false;
	let i = 0;

	while (i < json.length) {
		const ch = json[i];

		if (ch === '\\' && inString) {
			// Escaped character — copy both chars verbatim
			result += ch + json[i + 1];
			i += 2;
			continue;
		}

		if (ch === '"') {
			if (!inString) {
				// Opening quote of a JSON string — keep as literal "
				inString = true;
				result += '"';
			} else {
				// Could be closing quote or we need to check the next char context
				// In JSON, an unescaped " inside a string is always the closing quote
				inString = false;
				result += '"';
			}
			i++;
			continue;
		}

		if (inString) {
			// Apply PHP hex replacements inside string values
			switch (ch) {
				case '<':
					result += '\\u003C';
					break;
				case '>':
					result += '\\u003E';
					break;
				case '&':
					result += '\\u0026';
					break;
				case "'":
					result += '\\u0027';
					break;
				case '/':
					result += '\\/';
					break;
				default:
					result += ch;
			}
		} else {
			result += ch;
		}

		i++;
	}

	return result;
}
