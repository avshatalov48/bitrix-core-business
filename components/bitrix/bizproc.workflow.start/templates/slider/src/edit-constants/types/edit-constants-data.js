import type { Property } from '../../types/property';

export type EditConstantsData = {
	templateId: number,
	templateName: string,
	constants: Array<Property>,
	documentType: [],
	signedDocumentType: string,
	signedDocumentId: string,
};
