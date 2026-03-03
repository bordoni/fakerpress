import type { ReactNode } from 'react';
import { Card, CardContent } from '../ui/card';

interface PageLayoutProps {
	title: string;
	children: ReactNode;
}

export function PageLayout( { title, children }: PageLayoutProps ) {
	return (
		<div className="fp-max-w-4xl">
			<h2 className="fp-text-2xl fp-font-semibold fp-mb-4">{ title }</h2>
			<Card>
				<CardContent className="fp-p-6">
					{ children }
				</CardContent>
			</Card>
		</div>
	);
}
