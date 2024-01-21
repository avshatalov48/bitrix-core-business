import { LoadServiceInterface } from './load-service-interface';
import { ReloadServiceInterface } from './reload-service-interface';
import { Client } from '../client';
import { LoadRecentSpacesRequestFields } from './recent-service';
import { FilterModeTypes } from '../../const/filter-mode';

import type { SearchSpacesFields } from '../client';

const MINIMUM_QUERY_LENGTH_FOR_LOAD = 3;

export class SearchService implements LoadServiceInterface, ReloadServiceInterface
{
	static instance = null;
	hasMoreSpacesToLoad: boolean = true;
	searchString: string = '';

	static getInstance(): SearchService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	async loadSpaces(data: LoadRecentSpacesRequestFields): Promise
	{
		const fields: SearchSpacesFields = {};
		fields.loadedSpacesCount = data.loadedSpacesCount;
		fields.mode = FilterModeTypes.all;
		fields.searchString = this.searchString;

		const result = await Client.searchSpaces(fields);

		this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;

		return result;
	}

	canLoadSpaces(): boolean
	{
		return this.hasMoreSpacesToLoad && this.searchString.length >= MINIMUM_QUERY_LENGTH_FOR_LOAD;
	}
}
