import { useMemo, useState } from 'react';
import { __, _n, sprintf } from '@wordpress/i18n';
import {
	AlertTriangle,
	Check,
	Copy,
	ExternalLink,
	List,
	Pencil,
} from 'lucide-react';
import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogHeader,
	DialogTitle,
} from '../ui/dialog';
import {
	Accordion,
	AccordionContent,
	AccordionItem,
	AccordionTrigger,
} from '../ui/accordion';
import { Button } from '../ui/button';
import { cn } from '../../lib/utils';
import {
	formatDuration,
	generatedHeadline,
	type GenerateModule,
} from '../../lib/generate-labels';
import type {
	GenerateError,
	GenerateResult,
	GeneratedItem,
} from '../../lib/types';

interface GenerateDetailsDialogProps {
	open: boolean;
	onOpenChange: ( open: boolean ) => void;
	module: GenerateModule;
	results: GenerateResult | null;
	errorDetails: GenerateError | null;
}

/**
 * Copies text to the clipboard and reports success for a moment.
 *
 * @return The copy function and whether the last copy just happened.
 */
function useCopy(): [ ( text: string ) => void, boolean ] {
	const [ copied, setCopied ] = useState( false );

	const copy = ( text: string ) => {
		const done = () => {
			setCopied( true );
			window.setTimeout( () => setCopied( false ), 1500 );
		};
		if ( navigator.clipboard?.writeText ) {
			navigator.clipboard.writeText( text ).then( done, () => {} );
			return;
		}
		// Clipboard API needs a secure context; fall back for plain-HTTP admin screens.
		const area = document.createElement( 'textarea' );
		area.value = text;
		document.body.appendChild( area );
		area.select();
		document.execCommand( 'copy' );
		area.remove();
		done();
	};

	return [ copy, copied ];
}

/**
 * A small copy button with a "Copied" state.
 *
 * @param props       Component props.
 * @param props.text  Text to copy.
 * @param props.label Button label.
 */
function CopyButton( { text, label }: { text: string; label: string } ) {
	const [ copy, copied ] = useCopy();

	return (
		<Button
			type="button"
			variant="outline"
			size="sm"
			onClick={ () => copy( text ) }
		>
			{ copied ? <Check /> : <Copy /> }
			{ copied ? __( 'Copied', 'fakerpress' ) : label }
		</Button>
	);
}

/**
 * One figure in the stats strip.
 *
 * @param props       Component props.
 * @param props.label What the figure is.
 * @param props.value The figure.
 * @param props.code
 */
function Stat( {
	label,
	value,
	code = false,
}: {
	label: string;
	value: string;
	code?: boolean;
} ) {
	return (
		<div className="fp:min-w-0 fp:py-3 fp:pr-4 fp:sm:px-4 fp:sm:first:pl-0">
			<div className="fp:text-[11px] fp:font-medium fp:tracking-wide fp:text-muted-foreground fp:uppercase">
				{ label }
			</div>
			<div
				className={ cn(
					'fp:mt-0.5 fp:font-semibold',
					code
						? 'fp:font-mono fp:text-xs fp:leading-7 fp:break-words'
						: 'fp:truncate fp:text-lg fp:tabular-nums'
				) }
			>
				{ value }
			</div>
		</div>
	);
}

/**
 * Thumbnail for an item, or a tile with its ID when it has none.
 *
 * @param props      Component props.
 * @param props.item The generated item.
 */
function ItemThumb( { item }: { item: GeneratedItem } ) {
	if ( item.thumbnail ) {
		return (
			<img
				src={ item.thumbnail }
				alt=""
				className="fp:size-10 fp:shrink-0 fp:rounded-md fp:border fp:border-border/30 fp:object-cover"
			/>
		);
	}

	return (
		<span className="fp:flex fp:size-10 fp:shrink-0 fp:items-center fp:justify-center fp:rounded-md fp:bg-muted fp:font-mono fp:text-[11px] fp:text-muted-foreground">
			{ item.id }
		</span>
	);
}

/**
 * One generated item: title linking to its edit screen, its summary, and edit/view actions.
 *
 * @param props      Component props.
 * @param props.item The generated item.
 */
function ItemRow( { item }: { item: GeneratedItem } ) {
	return (
		<li className="fp:flex fp:items-center fp:gap-3 fp:border-b fp:border-border/20 fp:py-2.5 fp:last:border-b-0">
			<ItemThumb item={ item } />
			<div className="fp:min-w-0 fp:flex-1">
				<div className="fp:flex fp:items-baseline fp:gap-2">
					{ item.edit_url ? (
						<a
							href={ item.edit_url }
							className="fp:truncate fp:font-medium fp:text-foreground fp:hover:text-primary fp:hover:underline"
						>
							{ item.title }
						</a>
					) : (
						<span className="fp:truncate fp:font-medium">
							{ item.title }
						</span>
					) }
					<span className="fp:shrink-0 fp:font-mono fp:text-xs fp:text-muted-foreground">
						#{ item.id }
					</span>
				</div>
				{ item.summary.length > 0 && (
					<dl className="fp:mt-0.5 fp:flex fp:flex-wrap fp:gap-x-3 fp:gap-y-0.5 fp:text-xs">
						{ item.summary.map( ( fact ) => (
							<div
								key={ fact.label }
								className="fp:flex fp:min-w-0 fp:gap-1"
							>
								<dt className="fp:text-muted-foreground">
									{ fact.label }
								</dt>
								<dd className="fp:m-0 fp:truncate fp:text-foreground/85">
									{ fact.value }
								</dd>
							</div>
						) ) }
					</dl>
				) }
			</div>
			<div className="fp:flex fp:shrink-0 fp:items-center fp:gap-1">
				{ item.edit_url && (
					<Button
						asChild
						variant="ghost"
						size="icon-sm"
						title={ __( 'Edit', 'fakerpress' ) }
					>
						<a href={ item.edit_url }>
							<Pencil />
							<span className="fp:sr-only">
								{ sprintf(
									/* translators: %s: Title of the generated item. */
									__( 'Edit “%s”', 'fakerpress' ),
									item.title
								) }
							</span>
						</a>
					</Button>
				) }
				{ item.view_url && (
					<Button
						asChild
						variant="ghost"
						size="icon-sm"
						title={ __( 'View', 'fakerpress' ) }
					>
						<a
							href={ item.view_url }
							target="_blank"
							rel="noreferrer"
						>
							<ExternalLink />
							<span className="fp:sr-only">
								{ sprintf(
									/* translators: %s: Title of the generated item. */
									__(
										'View “%s” (opens in a new tab)',
										'fakerpress'
									),
									item.title
								) }
							</span>
						</a>
					</Button>
				) }
			</div>
		</li>
	);
}

/**
 * Pretty-printed JSON with a copy button.
 *
 * @param props       Component props.
 * @param props.value The value to show.
 */
function JsonBlock( { value }: { value: unknown } ) {
	const json = useMemo( () => JSON.stringify( value, null, 2 ), [ value ] );

	return (
		<div className="fp:relative">
			<div className="fp:absolute fp:top-2 fp:right-2">
				<CopyButton
					text={ json }
					label={ __( 'Copy JSON', 'fakerpress' ) }
				/>
			</div>
			<pre className="fp:m-0 fp:max-h-80 fp:overflow-auto fp:rounded-md fp:bg-slate-950 fp:p-3 fp:pr-28 fp:font-mono fp:text-xs fp:leading-relaxed fp:text-slate-100">
				{ json }
			</pre>
		</div>
	);
}

type AdvancedTab = 'items' | 'request' | 'response';

/**
 * The full data of a run: every item's record, what was sent, and what came back.
 *
 * @param props         Component props.
 * @param props.items   Generated items.
 * @param props.batches Each request/response round trip.
 * @param props.extra   Extra raw data, e.g. the error body.
 */
function AdvancedData( {
	items,
	batches,
	extra,
}: {
	items: GeneratedItem[];
	batches: GenerateResult[ 'batches' ];
	extra?: { request: unknown; response: unknown } | null;
} ) {
	const [ tab, setTab ] = useState< AdvancedTab >(
		items.length ? 'items' : 'response'
	);

	const requests = batches.map( ( batch ) => batch.request );
	const responses = batches.map( ( batch ) => batch.response );
	if ( extra ) {
		requests.push( extra.request as Record< string, unknown > );
		responses.push( extra.response );
	}

	const tabs: { id: AdvancedTab; label: string; value: unknown }[] = [
		{
			id: 'items',
			label: __( 'Items', 'fakerpress' ),
			value: items.map( ( item ) => ( {
				id: item.id,
				title: item.title,
				type: item.type,
				status: item.status,
				date: item.date,
				edit_url: item.edit_url,
				view_url: item.view_url,
				...item.details,
			} ) ),
		},
		{
			id: 'request',
			label: __( 'Request', 'fakerpress' ),
			value: requests.length === 1 ? requests[ 0 ] : requests,
		},
		{
			id: 'response',
			label: __( 'Response', 'fakerpress' ),
			value: responses.length === 1 ? responses[ 0 ] : responses,
		},
	];
	const current = tabs.find( ( t ) => t.id === tab ) ?? tabs[ 0 ];

	return (
		<div className="fp:space-y-3">
			<div
				role="tablist"
				aria-label={ __( 'Full data', 'fakerpress' ) }
				className="fp:inline-flex fp:rounded-md fp:bg-muted fp:p-0.5"
			>
				{ tabs.map( ( t ) => (
					<button
						key={ t.id }
						type="button"
						role="tab"
						aria-selected={ t.id === tab }
						onClick={ () => setTab( t.id ) }
						className={ cn(
							'fp:cursor-pointer fp:rounded-[5px] fp:bg-transparent fp:px-3 fp:py-1 fp:text-xs fp:font-medium fp:text-muted-foreground fp:transition-colors',
							t.id === tab &&
								'fp:bg-background fp:text-foreground fp:shadow-sm'
						) }
					>
						{ t.label }
					</button>
				) ) }
			</div>
			<JsonBlock value={ current.value } />
			{ batches.length > 1 && (
				<p className="fp:text-xs fp:text-muted-foreground">
					{ sprintf(
						/* translators: %d: Number of requests the run was split into. */
						_n(
							'The run was sent as %d request; each is listed in order.',
							'The run was split into %d requests; each is listed in order.',
							batches.length,
							'fakerpress'
						),
						batches.length
					) }
				</p>
			) }
		</div>
	);
}

/**
 * Details of a generate run: figures, every item with its links, and the full raw data.
 *
 * @param props              Component props.
 * @param props.open
 * @param props.onOpenChange
 * @param props.module
 * @param props.results
 * @param props.errorDetails
 */
export function GenerateDetailsDialog( {
	open,
	onOpenChange,
	module,
	results,
	errorDetails,
}: GenerateDetailsDialogProps ) {
	const failed = ! results && !! errorDetails;
	const batches = results?.batches ?? errorDetails?.batches ?? [];
	const items =
		results?.items ??
		batches.flatMap(
			( batch ) =>
				( ( batch.response as { data?: { items?: GeneratedItem[] } } )
					?.data?.items ?? [] ) as GeneratedItem[]
		);
	const generated =
		results?.generated ??
		batches.reduce( ( sum, batch ) => sum + batch.generated, 0 );
	const time =
		results?.time ??
		batches.reduce( ( sum, batch ) => sum + batch.time, 0 );
	const finishedAt = results?.finishedAt ?? errorDetails?.finishedAt;
	const ids = results?.ids ?? items.map( ( item ) => item.id );

	return (
		<Dialog open={ open } onOpenChange={ onOpenChange }>
			<DialogContent className="fp:flex fp:max-h-[min(88vh,52rem)] fp:flex-col fp:gap-0 fp:overflow-hidden fp:p-0 fp:sm:max-w-3xl fp:data-[state=open]:animate-dialog-in fp:motion-reduce:animate-none">
				<DialogHeader className="fp:flex-row fp:items-center fp:gap-3 fp:border-b fp:border-border/25 fp:px-6 fp:py-4 fp:text-left">
					<span
						className={ cn(
							'fp:flex fp:size-9 fp:shrink-0 fp:items-center fp:justify-center fp:rounded-full',
							failed
								? 'fp:bg-red-100 fp:text-red-700'
								: 'fp:bg-emerald-100 fp:text-emerald-700'
						) }
					>
						{ failed ? (
							<AlertTriangle className="fp:size-4.5" />
						) : (
							<Check className="fp:size-4.5" strokeWidth={ 3 } />
						) }
					</span>
					<div className="fp:min-w-0 fp:space-y-1">
						<DialogTitle>
							{ failed
								? __( 'Generation failed', 'fakerpress' )
								: generatedHeadline( module, generated ) }
						</DialogTitle>
						<DialogDescription>
							{ failed
								? errorDetails?.message
								: sprintf(
										/* translators: %s: Time of day the run finished. */
										__( 'Finished at %s', 'fakerpress' ),
										finishedAt
											? new Date(
													finishedAt
											  ).toLocaleTimeString()
											: ''
								  ) }
						</DialogDescription>
					</div>
				</DialogHeader>

				<div className="fp:min-h-0 fp:flex-1 fp:overflow-y-auto fp:px-6 fp:pb-6">
					<div className="fp:grid fp:grid-cols-2 fp:divide-border/25 fp:border-b fp:border-border/25 fp:sm:grid-cols-4 fp:sm:divide-x">
						<Stat
							label={ __( 'Items', 'fakerpress' ) }
							value={ String( generated ) }
						/>
						<Stat
							label={ __( 'Time', 'fakerpress' ) }
							value={ formatDuration( time ) }
						/>
						{ failed ? (
							<Stat
								label={ __( 'Error code', 'fakerpress' ) }
								value={ errorDetails?.code ?? '—' }
								code
							/>
						) : (
							<Stat
								label={ __( 'Per item', 'fakerpress' ) }
								value={
									generated
										? formatDuration( time / generated )
										: '—'
								}
							/>
						) }
						<Stat
							label={
								failed && errorDetails?.status
									? __( 'HTTP status', 'fakerpress' )
									: __( 'Requests', 'fakerpress' )
							}
							value={ String(
								failed && errorDetails?.status
									? errorDetails.status
									: batches.length
							) }
						/>
					</div>

					{ ids.length > 0 && (
						<section className="fp:pt-4">
							<div className="fp:flex fp:flex-wrap fp:items-center fp:justify-between fp:gap-2">
								<h3 className="fp:text-sm fp:font-semibold">
									{ failed
										? __(
												'Generated before the error',
												'fakerpress'
										  )
										: __(
												'Generated items',
												'fakerpress'
										  ) }
								</h3>
								<div className="fp:flex fp:gap-2">
									<CopyButton
										text={ ids.join( ', ' ) }
										label={ __( 'Copy IDs', 'fakerpress' ) }
									/>
									{ results?.listUrl && (
										<Button
											asChild
											variant="outline"
											size="sm"
										>
											<a href={ results.listUrl }>
												<List />
												{ __(
													'Open list',
													'fakerpress'
												) }
											</a>
										</Button>
									) }
								</div>
							</div>

							{ items.length > 0 ? (
								<ul className="fp:mt-2">
									{ items.map( ( item ) => (
										<ItemRow
											key={ item.id }
											item={ item }
										/>
									) ) }
								</ul>
							) : (
								<p className="fp:mt-2 fp:font-mono fp:text-xs fp:text-muted-foreground">
									{ ids.join( ', ' ) }
								</p>
							) }
						</section>
					) }

					<Accordion type="single" collapsible className="fp:mt-4">
						<AccordionItem
							value="advanced"
							className="fp:rounded-md fp:border fp:border-border/30 fp:px-4"
						>
							<AccordionTrigger className="fp:cursor-pointer fp:bg-transparent fp:py-3 fp:hover:no-underline">
								<span>
									<span className="fp:block">
										{ __(
											'Advanced: full data',
											'fakerpress'
										) }
									</span>
									<span className="fp:block fp:text-xs fp:font-normal fp:text-muted-foreground">
										{ __(
											'Every field and meta value of each item, plus the raw request and response.',
											'fakerpress'
										) }
									</span>
								</span>
							</AccordionTrigger>
							<AccordionContent>
								<AdvancedData
									items={ items }
									batches={ batches }
									extra={
										failed && errorDetails
											? {
													request:
														errorDetails.request,
													response:
														errorDetails.response,
											  }
											: null
									}
								/>
							</AccordionContent>
						</AccordionItem>
					</Accordion>
				</div>
			</DialogContent>
		</Dialog>
	);
}
