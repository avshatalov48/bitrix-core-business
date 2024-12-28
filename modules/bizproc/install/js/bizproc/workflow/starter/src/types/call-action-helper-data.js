import type { ComplexDocumentId } from '../data/complex-document-id';
import type { ComplexDocumentType } from '../data/complex-document-type';
import type { SignedDocumentId, SignedDocumentType } from '../starter';

export type CallActionHelperData = {
	complexDocumentType?: ComplexDocumentType,
	signedDocumentType?: SignedDocumentType,
	complexDocumentId?: ComplexDocumentId,
	signedDocumentId?: SignedDocumentId,
	customAjaxUrl: ?string,
};
