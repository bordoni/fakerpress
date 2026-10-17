import type { ReactNode } from 'react';
import { Info } from 'lucide-react';
import { Label } from '../ui/label';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '../ui/tooltip';

interface FormFieldProps {
	label: string;
	htmlFor?: string;
	description?: string;
	tooltip?: string;
	children: ReactNode;
}

export function FormField( { label, htmlFor, description, tooltip, children }: FormFieldProps ) {
	return (
		<div className="fp:grid fp:grid-cols-[200px_1fr] fp:gap-4 fp:items-start fp:py-3">
			<div className="fp:flex fp:items-center fp:gap-1 fp:pt-2">
				<Label htmlFor={ htmlFor } className="fp:text-sm fp:font-medium">
					{ label }
				</Label>
				{ tooltip && (
					<TooltipProvider>
						<Tooltip>
							<TooltipTrigger asChild>
								<span className="fp:inline-flex fp:cursor-help fp:text-muted-foreground fp:shrink-0">
									<Info className="fp:size-3.5" />
								</span>
							</TooltipTrigger>
							<TooltipContent side="right" className="fp:max-w-[300px]">{ tooltip }</TooltipContent>
						</Tooltip>
					</TooltipProvider>
				) }
			</div>
			<div className="fp:space-y-1">
				{ children }
				{ description && (
					<p className="fp:text-xs fp:italic fp:text-muted-foreground">{ description }</p>
				) }
			</div>
		</div>
	);
}
