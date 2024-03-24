import { Cache, Loc, Type } from 'main.core';
import { InitData, PostFormData } from './post-form';

export class PostData
{
	#cache = new Cache.MemoryCache();

	constructor(data: InitData)
	{
		this.setData(data);
	}

	setData(data: InitData)
	{
		this.#cache.set('data', data);
	}

	setFormData(formData: PostFormData)
	{
		const currentData = this.#cache.get('data');

		this.setData({
			...currentData,
			...formData,
		});
	}

	prepareRequestData(): Object
	{
		return {
			POST_TITLE: this.#getData('title'),
			POST_MESSAGE: this.#getData('message'),
			DEST_DATA: this.#getData('recipients'),
			UF_BLOG_POST_FILE: this.#getData('fileIds'),
			TAGS: this.#getData('tags'),
		};
	}

	validateRequestData(): string
	{
		if (!this.getMessage())
		{
			return Loc.getMessage('SN_PF_REQUEST_TEXT_VALIDATION_ERROR');
		}

		if (!this.getRecipients())
		{
			return Loc.getMessage('SN_PF_REQUEST_RECIPIENTS_VALIDATION_ERROR');
		}

		return '';
	}

	getTitle(): string
	{
		return Type.isStringFilled(this.#getData('title')) ? this.#getData('title') : '';
	}

	getMessage(): string
	{
		return Type.isStringFilled(this.#getData('message')) ? this.#getData('message') : '';
	}

	getRecipients(): string
	{
		return Type.isStringFilled(this.#getData('recipients')) ? this.#getData('recipients') : '';
	}

	setRecipients(recipients: string): void
	{
		const currentData = this.#cache.get('data');

		const newData = { recipients };

		this.setData({
			...currentData,
			...newData,
		});
	}

	getAllUsersTitle(): string
	{
		return this.#getData('allUsersTitle');
	}

	isAllowEmailInvitation(): boolean
	{
		return this.#getData('allowEmailInvitation') === true;
	}

	isAllowToAll(): boolean
	{
		return this.#getData('allowToAll') === true;
	}

	#getData(param: string): any
	{
		return this.#cache.get('data')[param];
	}
}
