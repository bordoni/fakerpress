import type { ReactNode } from 'react';
import { Button } from '../ui/button';

interface RuleCardProps {
	/** Zero-based index of the rule; displayed as a 1-based badge. */
	index: number;
	/** Remove this rule. */
	onRemove: () => void;
	/** Append a new rule. */
	onAdd: () => void;
	children: ReactNode;
}

/**
 * A single rule card: a white bordered panel with a numbered badge and
 * remove/add controls in the header, followed by label-left field rows.
 *
 * Mirrors the layout of the legacy (stable) generator so the React UI reads
 * like a native WordPress form rather than a stack of disconnected inputs.
 */
export function RuleCard( { index, onRemove, onAdd, children }: RuleCardProps ) {
	return (
		<div className="fp:bg-white fp:border fp:border-[#c3c4c7] fp:rounded-md fp:p-4 fp:mb-3">
			<div className="fp:flex fp:items-center fp:justify-between fp:mb-3">
				<span className="fp:flex fp:items-center fp:justify-center fp:size-6 fp:rounded fp:bg-[#f0f0f1] fp:text-xs fp:font-semibold fp:text-[#646970]">
					{ index + 1 }
				</span>
				<div className="fp:flex fp:gap-1">
					<Button
						type="button"
						variant="outline"
						size="icon-sm"
						onClick={ onRemove }
						title="Remove rule"
					>
						−
					</Button>
					<Button
						type="button"
						variant="outline"
						size="icon-sm"
						onClick={ onAdd }
						title="Add rule"
					>
						+
					</Button>
				</div>
			</div>
			<div className="fp:space-y-3">{ children }</div>
		</div>
	);
}

interface RuleFieldProps {
	/** Label shown on the left (stacks above on small screens). */
	label: ReactNode;
	/** Optional helper text shown beneath the control. */
	description?: ReactNode;
	children: ReactNode;
}

/**
 * A single labelled field row: label on the left, control on the right,
 * with an optional italic description beneath the control.
 */
export function RuleField( { label, description, children }: RuleFieldProps ) {
	return (
		<div className="fp:flex fp:flex-col fp:gap-1.5 fp:sm:flex-row fp:sm:gap-4">
			<div className="fp:sm:w-40 fp:sm:shrink-0 fp:sm:pt-2">
				<label className="fp:text-sm fp:font-medium fp:text-[#1d2327]">{ label }</label>
			</div>
			<div className="fp:flex-1 fp:space-y-1">
				{ children }
				{ description && (
					<p className="fp:text-xs fp:italic fp:text-[#646970]">{ description }</p>
				) }
			</div>
		</div>
	);
}
