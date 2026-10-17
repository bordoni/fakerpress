import { z } from 'zod';
import { metaRuleSchema } from './meta-rules';

export const usersSchema = z.object( {
	qty: z.object( {
		min: z.number().min( 1 ).default( 3 ),
		max: z.number().min( 1 ).default( 12 ),
	} ),
	roles: z.array( z.string() ).default( [] ),
	description_size: z.object( {
		min: z.number().min( 0 ).default( 1 ),
		max: z.number().min( 0 ).default( 5 ),
	} ),
	use_html: z.boolean().default( true ),
	html_tags: z.array( z.string() ).default( [] ),
	meta: z.array( metaRuleSchema ).default( [] ),
} );

export type UsersSchema = z.infer< typeof usersSchema >;
