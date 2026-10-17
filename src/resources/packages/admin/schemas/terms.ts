import { z } from 'zod';
import { metaRuleSchema } from './meta-rules';

export const termsSchema = z.object( {
	qty: z.object( {
		min: z.number().min( 1 ).default( 3 ),
		max: z.number().min( 1 ).default( 12 ),
	} ),
	size: z.object( {
		min: z.number().min( 1 ).default( 2 ),
		max: z.number().min( 1 ).default( 5 ),
	} ),
	taxonomies: z.array( z.string() ).min( 1 ),
	meta: z.array( metaRuleSchema ).default( [] ),
} );

export type TermsSchema = z.infer< typeof termsSchema >;
