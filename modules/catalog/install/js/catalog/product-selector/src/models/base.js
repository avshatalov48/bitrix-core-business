import {Text, Type} from 'main.core';

export class Base
{
	constructor(id, config = {})
	{
		this.id = id || Text.getRandom();
		this.config = config || {};
		this.errors = {};
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

	getFields()
	{
		return this.fields;
	}

	getField(fieldName, defaultValue = '')
	{
		return BX.prop.get(this.fields, fieldName, defaultValue);
	}

	setFields(fields)
	{
		this.fields = Type.isObject(fields) ? fields : {};
	}

	getErrors()
	{
		return this.errors;
	}

	setError(code, text)
	{
		this.errors[code] = text;
	}

	clearErrors(code, text)
	{
		this.errors = {};
	}

	hasErrors()
	{
		return Object.keys(this.errors).length > 0;
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