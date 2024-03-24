import { Client } from '../client';
import { LoadServiceInterface } from './load-service-interface';
import { ReloadServiceInterface } from './reload-service-interface';
import type { LoadSpacesFields, ReloadSpacesFields } from '../client';

export type LoadRecentSpacesRequestFields = {
	loadedSpacesCount: number,
	filterMode: string,
};

export type ReloadRecentSpacesRequestFields = {
	filterMode: string,
};

export class RecentService implements LoadServiceInterface, ReloadServiceInterface
{
	static instance = null;
	#selectedSpaceId: number;
	hasMoreSpacesToLoad: boolean = true;

	static getInstance(): RecentService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	setSelectedSpaceId(selectedSpaceId: number)
	{
		this.#selectedSpaceId = selectedSpaceId;
	}

	getSelectedSpaceId(): number
	{
		return this.#selectedSpaceId;
	}

	async loadSpaces(data: LoadRecentSpacesRequestFields): Promise
	{
		const fields: LoadSpacesFields = {};
		fields.loadedSpacesCount = data.loadedSpacesCount;
		fields.mode = data.filterMode;
		fields.searchString = '';

		const result = await Client.loadSpaces(fields);

		this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;

		return result;
	}

	canLoadSpaces(): boolean
	{
		return this.hasMoreSpacesToLoad;
	}

	async reloadSpaces(data: ReloadRecentSpacesRequestFields): Promise
	{
		const fields: ReloadSpacesFields = {};
		fields.mode = data.filterMode;

		const result = await Client.reloadSpaces(fields);
		this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;

		return result;
	}
}
