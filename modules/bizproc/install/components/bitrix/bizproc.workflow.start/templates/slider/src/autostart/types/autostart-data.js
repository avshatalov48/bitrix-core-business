import type { Property } from '../../types/property';

export type TemplateData = {
	id: number,
	name: string,
	description: string,
	parameters: Array<Property>,
};

export type AutostartData = {
	templates: Array<TemplateData>,
	documentType: [],
	signedDocumentType: string,
	signedDocumentId: ?string,
	autoExecuteType: number,
};
