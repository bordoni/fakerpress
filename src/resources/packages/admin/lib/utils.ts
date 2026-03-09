import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/**
 * Merge Tailwind CSS classes with proper precedence handling.
 *
 * Uses clsx for conditional class joining and tailwind-merge
 * for resolving Tailwind class conflicts.
 */
export function cn( ...inputs: ClassValue[] ) {
	return twMerge( clsx( inputs ) );
}
