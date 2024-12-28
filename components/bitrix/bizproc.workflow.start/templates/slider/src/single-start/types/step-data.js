import type { Property } from '../../types/property';

export type StepData = {
	name: string,
};

export type RecommendationStepData = StepData & {
	recommendation: ?string,
	duration: ?number,
};

export type ConstantsStepData = StepData & {
	templateId: number,
	constants: ?Array<Property>,
	documentType: [],
	signedDocumentType: string,
	signedDocumentId: string,
};

export type ParametersStepData = StepData & {
	templateId: number,
	parameters: ?Array<Property>,
	documentType: [],
	signedDocumentType: string,
	signedDocumentId: string,
};
