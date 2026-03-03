import { StrictMode, Suspense, lazy } from 'react';
import { createRoot } from 'react-dom/client';
import './styles/globals.css';

const PAGE_COMPONENTS: Record< string, React.LazyExoticComponent< React.ComponentType > > = {
	settings: lazy( () => import( './pages/settings' ) ),
	terms: lazy( () => import( './pages/terms' ) ),
	users: lazy( () => import( './pages/users' ) ),
	comments: lazy( () => import( './pages/comments' ) ),
	attachments: lazy( () => import( './pages/attachments' ) ),
	posts: lazy( () => import( './pages/posts' ) ),
};

function App( { page }: { page: string } ) {
	const PageComponent = PAGE_COMPONENTS[ page ];

	if ( ! PageComponent ) {
		return <div>Unknown page: { page }</div>;
	}

	return (
		<Suspense fallback={ <div className="fp-p-4 fp-text-muted-foreground">Loading...</div> }>
			<PageComponent />
		</Suspense>
	);
}

const rootElement = document.getElementById( 'fakerpress-react-root' );

if ( rootElement ) {
	const page = rootElement.getAttribute( 'data-page' ) || '';
	const root = createRoot( rootElement );

	root.render(
		<StrictMode>
			<App page={ page } />
		</StrictMode>
	);
}
