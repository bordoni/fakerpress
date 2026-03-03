import type { ReactNode } from 'react';
import { Label } from '../ui/label';

interface FormFieldProps {
	label: string;
	htmlFor?: string;
	description?: string;
	children: ReactNode;
}

export function FormField( { label, htmlFor, description, children }: FormFieldProps ) {
	return (
		<div className="fp-grid fp-grid-cols-[200px_1fr] fp-gap-4 fp-items-start fp-py-3 fp-border-b fp-border-border last:fp-border-b-0">
			<Label htmlFor={ htmlFor } className="fp-pt-2 fp-text-sm fp-font-medium">
				{ label }
			</Label>
			<div className="fp-space-y-1">
				{ children }
				{ description && (
					<p className="fp-text-xs fp-text-muted-foreground">{ description }</p>
				) }
			</div>
		</div>
	);
}
