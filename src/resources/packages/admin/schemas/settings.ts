import { z } from 'zod';

export const settingsSchema = z.object( {
	erase_phrase: z.string(),
} );

export type SettingsSchema = z.infer< typeof settingsSchema >;
