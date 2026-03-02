export interface ReadmeSubsection {
	heading: string;
	body: string;
}

export interface ReadmeSection {
	heading: string;
	body: string;
	subsections: ReadmeSubsection[];
}

export interface ParsedReadme {
	title: string;
	shortDescription: string;
	metadata: Record<string, string | string[]>;
	sections: ReadmeSection[];
}

export interface ChangelogVersion {
	number: string;
	date: string;
	content: string | null;
	html?: string;
}

export interface ReadmeDataSection {
	name: string;
	content: string | null;
	versions?: Record<string, ChangelogVersion>;
}

export type ReadmeData = Record<string, ReadmeDataSection>;
