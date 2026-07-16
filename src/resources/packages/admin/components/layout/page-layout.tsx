import type { ReactNode } from 'react';

interface PageLayoutProps {
	title: string;
	children: ReactNode;
}

export function PageLayout( { title, children }: PageLayoutProps ) {
	return (
		<div className="fp:max-w-4xl">
			<h2 className="fp:text-2xl fp:font-semibold fp:mb-4">{ title }</h2>
			{ children }
		</div>
	);
}
