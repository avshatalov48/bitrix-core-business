import {Text, Type} from 'main.core';

export class Base
{
	TYPE = '';

	constructor(id, config = {})
	{
		this.id = id || Text.getRandom();
		this.config = config || {};
		this.setMorePhotoValues(config.morePhoto);
		this.setFields(config.fields);
	}

	getId()
	{
		return this.id;
	}

	getProductId()
	{
		return this.id;
	}

	isSaveable(): boolean
	{
		return false;
	}

	setSaveable(value)
	{
		this.config.saveProductFields = value;
	}

	isNew(): boolean
	{
		return this.getConfig('isNew', false);
	}

	getConfig(name, defaultValue)
	{
		return BX.prop.get(this.config, name, defaultValue);
	}

	getType(): string
	{
		return this.TYPE;
	}

	getFields()
	{
		return this.fields;
	}

	getField(fieldName)
	{
		return BX.prop.get(this.fields, fieldName, '');
	}

	setFields(fields)
	{
		this.fields = Type.isObject(fields) ? fields : {};
	}

	isEnableFileSaving(): boolean
	{
		return false;
	}

	getMorePhotoValues()
	{
		return this.morePhoto;
	}

	setMorePhotoValues(values)
	{
		this.morePhoto = Type.isPlainObject(values) ? values : {};
	}

	removeMorePhotoItem(fileId): boolean
	{
		return false;
	}

	addMorePhotoItem(fileId, value)
	{
		this.morePhoto[fileId] = value;
	}

	getFileType(): string
	{
		return this.getType();
	}

	setFileType(value): string
	{
		this.config.fileType = value || '';
	}

	getDetailPath(): string
	{
		return '';
	}

	setDetailPath(value): string
	{
		this.config.DETAIL_PATH = value || '';
	}
}