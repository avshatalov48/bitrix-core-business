import { Type } from 'main.core';

export class ComplexDocumentId
{
	#moduleId: string;
	#entity: string;
	#documentId: string | number;

	constructor(moduleId: string, entity: string, documentId: string | number)
	{
		if (
			!Type.isStringFilled(moduleId)
			|| !Type.isStringFilled(entity)
			|| !(Type.isStringFilled(documentId) || Type.isNumber(documentId))
		)
		{
			throw new TypeError('incorrect complex document id');
		}

		this.#moduleId = moduleId;
		this.#entity = entity;
		this.#documentId = documentId;
	}

	get moduleId(): string
	{
		return this.#moduleId;
	}

	get entity(): string
	{
		return this.#entity;
	}

	get documentId(): string | number
	{
		return this.#documentId;
	}
}
