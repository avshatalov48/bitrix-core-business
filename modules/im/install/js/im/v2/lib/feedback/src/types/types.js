import type { JsonObject } from 'main.core';

export type FormConfigType = {
	id: string;
	forms: FormEntryType[];
	presets: JsonObject;
};

type FormEntryType = {
	zones: string[],
	id: number,
	sec: string,
	lang: string,
};

export type CopilotFormParams = {
	userCounter: number,
	text: string,
};
