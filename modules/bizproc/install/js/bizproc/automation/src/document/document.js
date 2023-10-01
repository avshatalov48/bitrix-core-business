import { Type, Runtime } from 'main.core';

export class Document
{
	#rawType: Array<string>;
	#id: number;
	#title: ?string;
	#categoryId: ?number;
	#statusList: ?Array<Object>;
	#currentStatusIndex: number;

	#fields: ?Array<Object>;

	constructor(options: {
		rawDocumentType: Array<string>,
		documentId: number,
		categoryId?: number,
		statusId?: string,

		statusList: Array<Object>,

		documentFields?: Array<Object>;
		title?: string,
	})
	{
		this.#rawType = options.rawDocumentType;
		this.#id = options.documentId;
		this.#title = options.title;
		this.#categoryId = options.categoryId;

		this.#statusList = [];
		this.#currentStatusIndex = 0;
		if (Type.isArray(options.statusList))
		{
			this.#statusList = options.statusList.map((status) => {
				status.STATUS_ID = String(status.STATUS_ID);

				return status;
			})
			this.#currentStatusIndex = this.#statusList.findIndex(status => status.STATUS_ID === options.statusId)
		}
		else if (Type.isStringFilled(options.statusId))
		{
			this.#statusList.push(options.statusId);
		}

		if (this.#currentStatusIndex < 0)
		{
			this.#currentStatusIndex = 0;
		}

		this.#fields = Type.isArray(options.documentFields) ? options.documentFields : [];
	}

	clone(): this
	{
		return new Document({
			rawDocumentType: Runtime.clone(this.#rawType),
			documentId: this.#id,
			categoryId: this.#categoryId,
			statusId: this.getCurrentStatusId(),

			statusList: Runtime.clone(this.#statusList),

			documentFields: Runtime.clone(this.#fields),
			title: this.#title,
		});
	}

	get title(): ?string
	{
		return this.#title;
	}

	getId(): number
	{
		return this.#id;
	}

	getRawType(): Array<string>
	{
		return this.#rawType;
	}

	getCategoryId(): ?number
	{
		return this.#categoryId;
	}

	getCurrentStatusId(): ?string
	{
		const documentStatus = this.#statusList[this.#currentStatusIndex]?.STATUS_ID;

		return !Type.isNil(documentStatus) ? String(documentStatus) : documentStatus;
	}

	getSortedStatusId(index: number): ?string
	{
		if (index >= 0 && index < this.#statusList.length)
		{
			return this.#statusList[index].STATUS_ID;
		}

		return null;
	}

	getNextStatusIdList(): Array<string>
	{
		return this.#statusList.slice(this.#currentStatusIndex + 1).map(status => status.STATUS_ID);
	}

	getPreviousStatusIdList(): Array<string>
	{
		return this.#statusList.slice(0, this.#currentStatusIndex).map(status => status.STATUS_ID);
	}

	setStatus(statusId: string): Document
	{
		const newStatusId = this.#statusList.findIndex(status => status.STATUS_ID === statusId);
		if (newStatusId >= 0)
		{
			this.#currentStatusIndex = newStatusId;
		}

		return this;
	}

	getFields(): Array<Object>
	{
		return this.#fields;
	}

	setFields(documentFields: Array<object>): this
	{
		this.#fields = documentFields;

		return this;
	}

	setStatusList(statusList: Array<Object>): this
	{
		if (Type.isArrayFilled(statusList))
		{
			this.#statusList = statusList;
		}

		return this;
	}

	get statusList(): ?Array<Object>
	{
		return this.#statusList;
	}
}