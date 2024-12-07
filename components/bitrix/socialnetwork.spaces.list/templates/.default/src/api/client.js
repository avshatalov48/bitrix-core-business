import { ajax } from 'main.core';

export type LoadSpacesFields = {
	loadedSpacesCount: number,
	mode: string,
	searchString: string,
};

export type ReloadSpacesFields = {
	mode: string,
};

export type SearchSpacesFields = {
	loadedSpacesCount: number,
	searchString: string,
};

export class Client
{
	static async loadSpaces(data: LoadSpacesFields): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'loadSpaces';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data,
		});

		return response.data;
	}

	static async reloadSpaces(data: ReloadSpacesFields): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'reloadSpaces';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data,
		});

		return response.data;
	}

	static async searchSpaces(data: SearchSpacesFields): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'searchSpaces';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data,
		});

		return response.data;
	}

	static async loadRecentSearchSpaces(): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'loadRecentSearchSpaces';

		const response = await ajax.runComponentAction(componentName, actionName, { mode: 'class' });

		return response.data;
	}

	static async addSpaceToRecentSearch(spaceId: number): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'addSpaceToRecentSearch';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data: {
				spaceId,
			},
		});

		return response.data;
	}

	static async loadSpaceData(spaceId: number): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'loadSpaceData';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data: {
				spaceId,
			},
		});

		return response.data;
	}

	static async loadSpacesData(spaceIds: Array<number>): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'loadSpacesData';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data: {
				spaceIds,
			},
		});

		return response.data;
	}

	static async loadSpaceTheme(spaceId: number): Promise
	{
		const componentName = 'bitrix:socialnetwork.spaces.list';
		const actionName = 'loadSpaceTheme';

		const response = await ajax.runComponentAction(componentName, actionName, {
			mode: 'class',
			data: {
				spaceId,
			},
		});

		return response.data;
	}
}
