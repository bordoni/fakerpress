import { useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '../ui/alert';
import { Button } from '../ui/button';
import { X, CheckCircle, AlertCircle } from 'lucide-react';

interface AdminNoticeProps {
	type: 'success' | 'error';
	title: string;
	message: string;
	dismissible?: boolean;
	onDismiss?: () => void;
}

export function AdminNotice( { type, title, message, dismissible = true, onDismiss }: AdminNoticeProps ) {
	const [ visible, setVisible ] = useState( true );

	if ( ! visible ) {
		return null;
	}

	const handleDismiss = () => {
		setVisible( false );
		onDismiss?.();
	};

	const Icon = type === 'success' ? CheckCircle : AlertCircle;
	const variant = type === 'error' ? 'destructive' : 'default';

	return (
		<Alert variant={ variant } className="fp:mb-4 fp:relative">
			<Icon className="fp:h-4 fp:w-4" />
			<AlertTitle>{ title }</AlertTitle>
			<AlertDescription>{ message }</AlertDescription>
			{ dismissible && (
				<Button
					variant="ghost"
					size="icon-xs"
					className="fp:absolute fp:top-2 fp:right-2"
					onClick={ handleDismiss }
				>
					<X className="fp:h-3 fp:w-3" />
				</Button>
			) }
		</Alert>
	);
}
