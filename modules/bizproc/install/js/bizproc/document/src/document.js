import { Type } from 'main.core';

export type ModuleId = string;
export type Entity = string;
export type ItemId = string;

export type RawDocumentId = [ModuleId, Entity, ItemId];

export type ExtendedDocumentIdOptions = {
	documentId: RawDocumentId,
	documentName?: string,
	documentUrl?: ?string,
	entityName?: string,
	moduleName?: string,
}

export class DocumentId
{
	#documentId: RawDocumentId;
	#name: ?string;
	#url: ?string;
	#entityName: ?string;
	#moduleName: ?string;

	constructor(options: RawDocumentId | ExtendedDocumentIdOptions)
	{
		if (Type.isArray(options))
		{
			this.#documentId = options;
		}
		else if (Type.isPlainObject(options))
		{
			this.#documentId = options.documentId;
			this.#name = options.documentName;
			this.#url = options.documentUrl;
			this.#entityName = options.entityName;
			this.#moduleName = options.moduleName;
		}
	}

	hasName(): boolean
	{
		return Type.isStringFilled(this.#name);
	}

	hasUrl(): boolean
	{
		return Type.isStringFilled(this.#url);
	}

	get name(): string
	{
		return this.hasName() ? this.#name : '';
	}

	get url(): ?string
	{
		return this.#url;
	}

	hasEntityName(): boolean
	{
		return Type.isStringFilled(this.#entityName);
	}

	get entityName(): string
	{
		return this.hasEntityName() ? this.#entityName : '';
	}

	hasModuleName(): boolean
	{
		return Type.isStringFilled(this.#name);
	}

	get moduleName(): string
	{
		return this.hasModuleName() ? this.#moduleName : '';
	}

	get moduleId(): ModuleId
	{
		return this.#documentId[0];
	}

	get entity(): Entity
	{
		return this.#documentId[1];
	}

	get id(): ItemId
	{
		return this.#documentId[2];
	}

	toJSON(): string
	{
		return JSON.stringify(this.#documentId);
	}
}
