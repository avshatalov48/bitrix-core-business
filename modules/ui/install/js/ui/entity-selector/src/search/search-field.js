import { Type } from 'main.core';
import type { SearchFieldOptions } from './search-field-options';

export default class SearchField
{
	name: string = null;
	type: string = 'string';
	searchable: boolean = true;
	system: boolean = false;
	sort: ?number = null;

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
		this.setSort(options.sort);
		this.setSearchable(options.searchable);
	}

	getName(): string
	{
		return this.name;
	}

	getType(): string
	{
		return this.type;
	}

	setType(type: string): void
	{
		if (Type.isStringFilled(type))
		{
			this.type = type;
		}
	}

	getSort(): ?number
	{
		return this.sort;
	}

	setSort(sort: ?number): void
	{
		if (Type.isNumber(sort) || sort === null)
		{
			this.sort = sort;
		}
	}

	setSearchable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.searchable = flag;
		}
	}

	isSearchable(): boolean
	{
		return this.searchable;
	}

	setSystem(flag: boolean)
	{
		if (Type.isBoolean(flag))
		{
			this.system = flag;
		}
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