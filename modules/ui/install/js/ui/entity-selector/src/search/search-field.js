import { Type } from 'main.core';
import type { SearchFieldOptions } from './search-field-options';

export default class SearchField
{
	name: string = null;
	type: string = 'string';
	searchable: boolean = true;
	system: boolean = false;

	constructor(fieldOptions: SearchFieldOptions)
	{
		const options = Type.isPlainObject(fieldOptions) ? fieldOptions : {};

		if (!Type.isStringFilled(options.name))
		{
			throw new Error('EntitySelector.SearchField: "name" parameter is required.');
		}

		this.name = options.name;
		this.setType(options.type);
		this.setSystem(options.system);
		this.setSeachable(options.searchable);
	}

	getName(): string
	{
		return this.name;
	}

	getType(): string
	{
		return this.type;
	}

	setType(type: string)
	{
		if (Type.isStringFilled(type))
		{
			this.type = type;
		}
	}

	setSeachable(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.searchable = flag;
		}

		return this;
	}

	isSeachable(): boolean
	{
		return this.searchable;
	}

	setSystem(flag: boolean): this
	{
		if (Type.isBoolean(flag))
		{
			this.system = flag;
		}

		return this;
	}

	isCustom(): boolean
	{
		return !this.isSystem();
	}

	isSystem(): boolean
	{
		return this.system;
	}
}