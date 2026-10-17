import { z } from 'zod';

export const taxonomyRuleSchema = z.object( {
	taxonomies: z.array( z.string() ).min( 1 ),
	terms: z.array( z.union( [ z.string(), z.number() ] ) ).default( [] ),
	rate: z.number().min( 0 ).max( 100 ).default( 50 ),
	qty: z.object( {
		min: z.number().min( 0 ).default( 1 ),
		max: z.number().min( 0 ).default( 3 ),
	} ),
} );

export type TaxonomyRuleSchema = z.infer< typeof taxonomyRuleSchema >;
