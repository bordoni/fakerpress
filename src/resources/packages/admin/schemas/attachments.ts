import { z } from 'zod';
import { metaRuleSchema } from './meta-rules';

export const attachmentsSchema = z.object( {
	qty: z.object( {
		min: z.number().min( 1 ).default( 3 ),
		max: z.number().min( 1 ).default( 12 ),
	} ),
	date: z.object( {
		preset: z.string().default( '' ),
		start: z.string().default( '' ),
		end: z.string().default( '' ),
	} ),
	provider: z.string().default( 'placehold_co' ),
	width: z.object( {
		min: z.number().min( 1 ).default( 50 ),
		max: z.number().min( 1 ).default( 3000 ),
	} ),
	height: z.object( {
		min: z.number().min( 1 ).default( 50 ),
		max: z.number().min( 1 ).default( 3000 ),
	} ),
	aspect_ratio: z.number().min( 0 ).default( 0 ),
	parents: z.array( z.number() ).default( [] ),
	author: z.array( z.number() ).default( [] ),
	alt_text: z.boolean().default( true ),
	caption: z.boolean().default( true ),
	description: z.boolean().default( true ),
	meta: z.array( metaRuleSchema ).default( [] ),
} );

export type AttachmentsSchema = z.infer< typeof attachmentsSchema >;
