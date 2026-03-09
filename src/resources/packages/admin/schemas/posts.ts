import { z } from 'zod';
import { metaRuleSchema } from './meta-rules';
import { taxonomyRuleSchema } from './taxonomy-rules';

export const postsSchema = z.object( {
	qty: z.object( {
		min: z.number().min( 1 ).default( 3 ),
		max: z.number().min( 1 ).default( 12 ),
	} ),
	date: z.object( {
		preset: z.string().default( '' ),
		start: z.string().default( '' ),
		end: z.string().default( '' ),
	} ),
	post_type: z.array( z.string() ).min( 1 ),
	parents: z.array( z.number() ).default( [] ),
	comment_status: z.array( z.string() ).default( [] ),
	authors: z.array( z.number() ).default( [] ),
	use_html: z.boolean().default( true ),
	content_size: z.object( {
		min: z.number().min( 0 ).default( 5 ),
		max: z.number().min( 0 ).default( 15 ),
	} ),
	html_tags: z.array( z.string() ).default( [] ),
	image_providers: z.array( z.string() ).default( [] ),
	excerpt_size: z.object( {
		min: z.number().min( 0 ).default( 1 ),
		max: z.number().min( 0 ).default( 3 ),
	} ),
	taxonomy_rules: z.array( taxonomyRuleSchema ).default( [] ),
	meta: z.array( metaRuleSchema ).default( [] ),
} );

export type PostsSchema = z.infer< typeof postsSchema >;
