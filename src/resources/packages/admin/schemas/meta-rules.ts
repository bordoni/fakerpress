import { z } from 'zod';

export const metaRuleSchema = z.object( {
	type: z.string().min( 1 ),
	name: z.string().min( 1 ),
	config: z.record( z.unknown() ).default( {} ),
} );

export type MetaRuleSchema = z.infer< typeof metaRuleSchema >;
