import { LoadServiceInterface } from './load-service-interface';
import { Client } from '../client';

export class RecentSearchService implements LoadServiceInterface
{
	static instance = null;
	hasMoreSpacesToLoad: boolean = false;

	static getInstance(): RecentSearchService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	async loadSpaces(): Promise
	{
		return Client.loadRecentSearchSpaces();
	}

	canLoadSpaces(): boolean
	{
		return false;
	}
}
