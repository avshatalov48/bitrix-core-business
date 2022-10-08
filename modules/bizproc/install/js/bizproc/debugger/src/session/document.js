import {Type, Text} from 'main.core';
import {DocumentOptions} from './document-options';
import {Helper} from '../helper';

export class Document
{
	#id: number;
	#sessionId: string;
	#documentId: string;
	#categoryId: number;
	#dateExpire: Date;

	#documentSigned: string = '';

	constructor(options: DocumentOptions)
	{
		this.#id = parseInt(options.Id) >= 0 ? parseInt(options.Id) : 0;
		this.#sessionId = Type.isStringFilled(options.SessionId) ? options.SessionId : '';
		this.#documentId = Type.isStringFilled(options.DocumentId) ? options.DocumentId : '';
		this.#categoryId = Text.toInteger(options.DocumentCategoryId);
		this.#dateExpire = Helper.toDate(options.DateExpire);
		if (options.DocumentSigned)
		{
			this.documentSigned = options.DocumentSigned;
		}
	}

	get documentId(): string
	{
		return this.#documentId;
	}

	get categoryId(): number
	{
		return this.#categoryId;
	}

	set categoryId(categoryId: number)
	{
		this.#categoryId = Type.isNumber(categoryId) ? categoryId : 0;
	}

	get documentSigned(): string
	{
		return this.#documentSigned;
	}

	set documentSigned(documentSigned: string)
	{
		this.#documentSigned = Type.isStringFilled(documentSigned) ? documentSigned : '';
	}
}