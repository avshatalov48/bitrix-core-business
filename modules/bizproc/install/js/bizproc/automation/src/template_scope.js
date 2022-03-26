import { Type } from 'main.core';
import { DocumentType, DocumentCategory, DocumentStatus } from './document/types';

export class TemplateScope
{
	#documentType: DocumentType;
	#category: DocumentCategory | null;
	#status: DocumentStatus;

	constructor(rawTemplateScope: {
		DocumentType: {
			Type: string,
			Name: string,
		},
		Category: {
			Id: string | null,
			Name: string | null,
		},
		Status: {
			Id: string,
			Name: string,
			Color: string,
		}
	})
	{
		this.#documentType = rawTemplateScope.DocumentType;
		this.#category = !Type.isNil(rawTemplateScope.Category.Id) ? rawTemplateScope.Category : null;
		this.#status = rawTemplateScope.Status;
	}

	getId()
	{
		if (this.hasCategory())
		{
			return `${this.#documentType.Type}_${this.#category.Id}_${this.#status.Id}`;
		}

		return `${this.#documentType.Type}_${this.#status.Id}`;
	}

	getDocumentType(): DocumentType
	{
		return this.#documentType;
	}

	getDocumentCategory(): DocumentCategory
	{
		return this.#category;
	}

	getDocumentStatus(): DocumentStatus
	{
		return this.#status;
	}

	hasCategory(): boolean
	{
		return !Type.isNull(this.#category);
	}
}