import { Type } from 'main.core';
import type { SignedDocumentType } from '../starter';

export class ComplexDocumentType
{
	#moduleId: string;
	#entity: string;
	#documentType: string;

	constructor(moduleId: string, entity: string, documentType: string)
	{
		if (
			!Type.isStringFilled(moduleId)
			|| !Type.isStringFilled(entity)
			|| !Type.isStringFilled(documentType)
		)
		{
			throw new TypeError('incorrect complex document type');
		}

		this.#moduleId = moduleId;
		this.#entity = entity;
		this.#documentType = documentType;
	}

	get moduleId(): string
	{
		return this.#moduleId;
	}

	get entity(): string
	{
		return this.#entity;
	}

	get documentType(): string
	{
		return this.#documentType;
	}

	isEqual(targetDocumentType: SignedDocumentType | ComplexDocumentType): boolean
	{
		if (Type.isString(targetDocumentType))
		{
			return (
				targetDocumentType.includes(this.moduleId)
				&& targetDocumentType.includes(this.entity)
				&& targetDocumentType.includes(this.documentType)
			);
		}

		if (Type.isObjectLike(targetDocumentType))
		{
			return (
				this.moduleId === targetDocumentType.moduleId
				&& this.entity === targetDocumentType.entity
				&& this.documentType === targetDocumentType.documentType
			);
		}

		return false;
	}
}
