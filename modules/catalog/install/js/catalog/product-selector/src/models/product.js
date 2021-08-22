import {Loc, Text, Type} from "main.core";
import {Base} from './base';

export class Product extends Base
{
	isSaveable(): boolean
	{
		return this.getConfig('saveProductFields', false);
	}

	isEnableFileSaving(): boolean
	{
		return true;
	}

	getDetailPath(): string
	{
		return this.getConfig('DETAIL_PATH', '');
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

	setMorePhotoValues(values)
	{
		super.setMorePhotoValues(values);

		if (this.isEnabledEmptyImageError() && this.morePhoto.length === 0)
		{
			this.setError(
				'EMPTY_IMAGE',
				Loc.getMessage('CATALOG_SELECTOR_EMPTY_IMAGE_ERROR')
			);
		}
	}

	isEnabledEmptyImageError(): boolean
	{
		return this.getConfig('ENABLE_EMPTY_IMAGES_ERROR', false);
	}
}