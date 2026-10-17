import { clsx, type ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

/**
 * tailwind-merge instance aware of our Tailwind v4 `fp:` prefix.
 *
 * Without the prefix configured, tailwind-merge cannot recognize that
 * prefixed utilities (e.g. `fp:rounded-full` vs `fp:rounded-sm`) belong to
 * the same conflict group, so className overrides are silently dropped.
 */
const twMerge = extendTailwindMerge( { prefix: 'fp' } );

/**
 * Merge Tailwind CSS classes with proper precedence handling.
 *
 * Uses clsx for conditional class joining and tailwind-merge
 * for resolving Tailwind class conflicts.
 */
export function cn( ...inputs: ClassValue[] ) {
	return twMerge( clsx( inputs ) );
}
