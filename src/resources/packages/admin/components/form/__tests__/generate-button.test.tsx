import { render, screen } from '@testing-library/react';
import '@testing-library/jest-dom';
import { GenerateButton } from '../generate-button';

describe( 'GenerateButton', () => {
	const defaultProps = {
		onClick: jest.fn(),
		isGenerating: false,
		progress: null,
		results: null,
		error: null,
	};

	beforeEach( () => {
		jest.clearAllMocks();
	} );

	it( 'renders Generate button', () => {
		render( <GenerateButton { ...defaultProps } /> );

		expect( screen.getByRole( 'button', { name: /generate/i } ) ).toBeInTheDocument();
		expect( screen.getByRole( 'button' ) ).toHaveTextContent( 'Generate' );
	} );

	it( 'shows progress bar during generation', () => {
		render(
			<GenerateButton
				{ ...defaultProps }
				isGenerating={ true }
				progress={ { current: 5, total: 10 } }
			/>
		);

		expect( screen.getByRole( 'button' ) ).toHaveTextContent( 'Generating...' );
		expect( screen.getByText( /5 \/ 10 generated/ ) ).toBeInTheDocument();
	} );

	it( 'shows results after generation', () => {
		render(
			<GenerateButton
				{ ...defaultProps }
				results={ {
					generated: 5,
					ids: [ 1, 2, 3, 4, 5 ],
					links: {},
					time: 1.23,
				} }
			/>
		);

		const successText = screen.getByText( /successfully generated/i );
		expect( successText ).toHaveTextContent( '5' );
		expect( successText ).toHaveTextContent( '1.23' );
	} );
} );
