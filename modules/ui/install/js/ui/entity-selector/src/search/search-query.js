import { Type } from 'main.core';

export default class SearchQuery
{
	queryWords: string[] = [];
	query: string = '';
	cacheable: boolean = true;
	dynamicSearchEntities: string[] = [];

	constructor(query: string)
	{
		this.query = query.trim().replace(/\s\s+/g, ' ');
		this.queryWords = Type.isStringFilled(this.query) ? this.query.split(' ') : [];
	}

	getQueryWords(): string[]
	{
		return this.queryWords;
	}

	getQuery(): string
	{
		return this.query;
	}

	isEmpty(): boolean
	{
		return this.getQueryWords().length === 0;
	}

	setCacheable(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.cacheable = flag;
		}
	}

	isCacheable(): boolean
	{
		return this.cacheable;
	}

	hasDynamicSearch(): boolean
	{
		return this.getDynamicSearchEntities().length > 0;
	}

	hasDynamicSearchEntity(entityId: string): boolean
	{
		return this.getDynamicSearchEntities().includes(entityId);
	}

	setDynamicSearchEntities(entities: string[]): void
	{
		if (Type.isArrayFilled(entities))
		{
			entities.forEach((entityId: string) => {
				if (Type.isStringFilled(entityId) && !this.hasDynamicSearchEntity(entityId))
				{
					this.dynamicSearchEntities.push(entityId);
				}
			});
		}

		return this.dynamicSearchEntities;
	}

	getDynamicSearchEntities(): string[]
	{
		return this.dynamicSearchEntities;
	}

	toJSON()
	{
		return {
			queryWords: this.getQueryWords(),
			query: this.getQuery(),
			dynamicSearchEntities: this.getDynamicSearchEntities(),
		};
	}
}