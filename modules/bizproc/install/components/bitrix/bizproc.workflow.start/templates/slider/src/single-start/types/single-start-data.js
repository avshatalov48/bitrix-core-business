import type { Property } from '../../types/property';

export type StepId = string;

export type SingleStartData = {
	documentType: [],
	signedDocumentType: string,
	signedDocumentId: string,

	// flags
	hasParameters: boolean,
	isConstantsTuned: boolean,

	// template
	id: number,
	name: string,
	description: string,
	duration: ?number,
	constants: ?Array<Property>,
	parameters: ?Array<Property>,
};
