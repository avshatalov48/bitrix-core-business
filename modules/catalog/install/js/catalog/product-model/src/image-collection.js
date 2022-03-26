import {Text, Type} from 'main.core';
import {ProductModel} from "catalog.product-model";

export class ImageCollection
{
	#isEnabledSaving = false;
	#preview = '';
	#editInput = '';

	constructor(model: ProductModel = {})
	{
		this.model = model;
	}

	isEnableFileSaving(): boolean
	{
		return this.#isEnabledSaving;
	}

	enableFileSaving(): void
	{
		this.#isEnabledSaving = true;
	}

	getMorePhotoValues(): {}
	{
		return this.morePhoto;
	}

	setMorePhotoValues(values): void
	{
		this.morePhoto = Type.isPlainObject(values) ? values : {};
	}

	removeMorePhotoItem(fileId): boolean
	{
		for (const index in this.morePhoto)
		{
			let value = this.morePhoto[index];

			if (!Type.isObject(value))
			{
				value = Text.toInteger(value);
			}

			if (
				(Type.isNumber(value) && value === Text.toInteger(fileId))
				|| (Type.isObject(value) && value.fileId === fileId)
			)
			{
				delete this.morePhoto[index];
				return true;
			}
		}

		return false;
	}

	setPreview(html: string): ImageCollection
	{
		this.#preview = Type.isStringFilled(html) ? html : '';

		return this;
	}

	setEditInput(html: string): ImageCollection
	{
		this.#editInput = Type.isStringFilled(html) ? html : '';

		return this;
	}

	getPreview(): string
	{
		return this.#preview || '';
	}

	getEditInput(): string
	{
		return this.#editInput || '';
	}

	addMorePhotoItem(fileId, value): void
	{
		this.morePhoto[fileId] = value;
	}
}
