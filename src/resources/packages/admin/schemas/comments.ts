import { z } from 'zod';
import { metaRuleSchema } from './meta-rules';

export const commentsSchema = z.object( {
	type: z.array( z.string() ).default( [ 'default' ] ),
	post_type: z.array( z.string() ).default( [ 'post' ] ),
	qty: z.object( {
		min: z.number().min( 1 ).default( 3 ),
		max: z.number().min( 1 ).default( 12 ),
	} ),
	date: z.object( {
		preset: z.string().default( '' ),
		start: z.string().default( '' ),
		end: z.string().default( '' ),
	} ),
	content_size: z.object( {
		min: z.number().min( 0 ).default( 1 ),
		max: z.number().min( 0 ).default( 5 ),
	} ),
	use_html: z.boolean().default( true ),
	html_tags: z.array( z.string() ).default( [] ),
	meta: z.array( metaRuleSchema ).default( [] ),
} );

export type CommentsSchema = z.infer< typeof commentsSchema >;
