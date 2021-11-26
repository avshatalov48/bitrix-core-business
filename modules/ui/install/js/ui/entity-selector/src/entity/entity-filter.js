import { Type } from 'main.core';
import type { EntityFilterOptions } from './entity-filter-options';

export default class EntityFilter
{
	id: string = null;
	options: { [key: string]: any } = {};

	constructor(filterOptions: EntityFilterOptions)
	{
		const options = Type.isPlainObject(filterOptions) ? filterOptions : {};

		this.id = options.id;
		this.options = options.options;
	}

	getId(): string
	{
		return this.id;
	}

	getOptions(): { [key: string]: any }
	{
		return this.options;
	}

	toJSON()
	{
		return {
			id: this.getId(),
			options: this.getOptions(),
		}
	}
}