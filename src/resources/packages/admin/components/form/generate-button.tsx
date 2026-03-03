import { Button } from '../ui/button';
import { Progress } from '../ui/progress';
import { Loader2 } from 'lucide-react';
import type { GenerateResult } from '../../lib/types';

interface GenerateButtonProps {
	onClick: () => void;
	isGenerating: boolean;
	progress: { current: number; total: number } | null;
	results: GenerateResult | null;
	error: string | null;
}

export function GenerateButton( {
	onClick,
	isGenerating,
	progress,
	results,
	error,
}: GenerateButtonProps ) {
	const progressPercent = progress
		? Math.round( ( progress.current / progress.total ) * 100 )
		: 0;

	return (
		<div className="fp-space-y-3 fp-pt-4">
			<Button
				onClick={ onClick }
				disabled={ isGenerating }
				className="fp-w-full sm:fp-w-auto"
			>
				{ isGenerating && <Loader2 className="fp-animate-spin" /> }
				{ isGenerating ? 'Generating...' : 'Generate' }
			</Button>

			{ isGenerating && progress && (
				<div className="fp-space-y-1">
					<Progress value={ progressPercent } className="fp-w-full" />
					<p className="fp-text-xs fp-text-muted-foreground">
						{ progress.current } / { progress.total } generated
					</p>
				</div>
			) }

			{ results && (
				<p className="fp-text-sm fp-text-green-600">
					Successfully generated { results.generated } items in { results.time.toFixed( 2 ) }s.
				</p>
			) }

			{ error && (
				<p className="fp-text-sm fp-text-destructive">
					Error: { error }
				</p>
			) }
		</div>
	);
}
