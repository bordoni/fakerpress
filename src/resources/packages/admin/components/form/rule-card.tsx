import type { ReactNode } from 'react';
import { cn } from '../../lib/utils';

interface RuleSectionProps {
	/** Section heading, e.g. "Meta Field Rules". */
	title: string;
	/** Italic helper line under the heading. */
	description?: ReactNode;
	children: ReactNode;
}

/**
 * A rules section: the grey heading band and italic description used by the
 * stable generator's Taxonomy and Meta Field Rules, followed by its rule cards.
 * @param root0
 * @param root0.title
 * @param root0.description
 * @param root0.children
 */
export function RuleSection( {
	title,
	description,
	children,
}: RuleSectionProps ) {
	return (
		<div className="fp:py-[15px]">
			<h3 className="fp:-ml-[22px] fp:mb-2.5 fp:bg-[#f9f9f9] fp:py-1.5 fp:pl-[22px] fp:text-[1.3em] fp:font-semibold fp:leading-7 fp:text-[#1d2327]">
				{ title }
			</h3>
			{ description && (
				<p className="fp:mx-px fp:mt-1 fp:mb-[5px] fp:text-sm fp:italic fp:leading-7 fp:text-[#666]">
					{ description }
				</p>
			) }
			<div>{ children }</div>
		</div>
	);
}

interface RuleCardProps {
	/** Zero-based index of the rule; displayed as a 1-based badge. */
	index: number;
	/** Remove this rule (or clear it, when it is the only one). */
	onRemove: () => void;
	/** Append a new, empty rule. */
	onAdd: () => void;
	/** Disable the remove control (the only rule, and it is already empty). */
	removeDisabled?: boolean;
	children: ReactNode;
}

/**
 * A single rule, laid out like the stable generator's rule tables: a grey
 * gutter with the rule number on the left, label-left field rows in the
 * middle, and stacked remove/add buttons in a grey gutter on the right.
 * Consecutive cards share a border so a list of rules reads as one block.
 * @param root0
 * @param root0.index
 * @param root0.onRemove
 * @param root0.onAdd
 * @param root0.removeDisabled
 * @param root0.children
 */
export function RuleCard( {
	index,
	onRemove,
	onAdd,
	removeDisabled = false,
	children,
}: RuleCardProps ) {
	return (
		<div
			className={ cn(
				'fp:flex fp:flex-col fp:border fp:border-[#dfdfdf] fp:bg-[#f9f9f9]',
				index > 0 && 'fp:border-t-0'
			) }
			data-fp-rule={ index + 1 }
		>
			<div className="fp:h-[5px] fp:shrink-0 fp:border-b fp:border-[#f4f4f4] fp:bg-white" />
			<div className="fp:flex fp:flex-1">
				<div className="fp:w-[37px] fp:shrink-0 fp:bg-[#f4f4f4] fp:pt-1">
					<button
						type="button"
						className="button fp-rule-action"
						disabled
						aria-label={ `Rule ${ index + 1 }` }
					>
						{ index + 1 }
					</button>
				</div>
				<div className="fp:min-w-0 fp:flex-1 fp:bg-white fp:py-3">
					{ children }
				</div>
				<div className="fp:flex fp:w-[37px] fp:shrink-0 fp:flex-col fp:gap-0.5 fp:bg-[#f4f4f4] fp:pt-1">
					<button
						type="button"
						className="button fp-rule-action"
						onClick={ onRemove }
						disabled={ removeDisabled }
						title="Remove rule"
						aria-label="Remove rule"
					>
						&minus;
					</button>
					<button
						type="button"
						className="button fp-rule-action"
						onClick={ onAdd }
						title="Add rule"
						aria-label="Add rule"
					>
						+
					</button>
				</div>
			</div>
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
 * A single labelled row inside a rule: label on the left, control on the
 * right, with an optional italic description beneath the control.
 * @param root0
 * @param root0.label
 * @param root0.description
 * @param root0.children
 */
export function RuleField( { label, description, children }: RuleFieldProps ) {
	return (
		<div className="fp:flex fp:flex-col fp:py-2 fp:sm:flex-row">
			<div className="fp:shrink-0 fp:pr-2 fp:pl-5 fp:sm:w-[139px]">
				<span className="fp:mt-[5px] fp:inline-block fp:text-sm fp:leading-[18.2px] fp:text-[#3c434a]">
					{ label }
				</span>
			</div>
			<div className="fp:min-w-0 fp:flex-1 fp:px-2 fp:max-sm:pl-5">
				{ children }
				{ description && (
					<RuleDescription>{ description }</RuleDescription>
				) }
			</div>
		</div>
	);
}

/**
 * Small italic helper text, as used under rule controls.
 * @param root0
 * @param root0.children
 */
export function RuleDescription( { children }: { children: ReactNode } ) {
	return (
		<p className="fp:mx-px fp:mt-1 fp:mb-[5px] fp:text-[12.6px] fp:italic fp:leading-[18.9px] fp:text-[#666]">
			{ children }
		</p>
	);
}

interface RuleConfigItemProps {
	/** Small grey label next to (or, for wide controls, below) the control. */
	label?: ReactNode;
	/** Put the label under the control instead of beside it. */
	labelBelow?: boolean;
	children: ReactNode;
}

/**
 * One control inside a meta rule's "Configuration" row, with the small grey
 * inline label the stable generator uses (e.g. "Weight", "Stored Data").
 * @param root0
 * @param root0.label
 * @param root0.labelBelow
 * @param root0.children
 */
export function RuleConfigItem( {
	label,
	labelBelow = false,
	children,
}: RuleConfigItemProps ) {
	return (
		<div
			className={ cn(
				'fp:flex fp:gap-x-2 fp:not-first:mt-2',
				labelBelow
					? 'fp:flex-col fp:items-start'
					: 'fp:flex-wrap fp:items-center'
			) }
		>
			{ children }
			{ label && (
				<span className="fp:text-xs fp:leading-[16.8px] fp:text-[#888] fp:[&_b]:px-[3px] fp:[&_b]:text-[15px]">
					{ label }
				</span>
			) }
		</div>
	);
}
