import {SearchEntityIdTypes} from 'im.v2.const';
import {SearchItem} from './search-item';
import {Type} from 'main.core';

export const SearchUtils = {
	getWordsFromString(string: string): Array<string>
	{
		const clearedString = string
			.replaceAll('(', ' ')
			.replaceAll(')', ' ')
			.replaceAll('[', ' ')
			.replaceAll(']', ' ')
			.replaceAll('{', ' ')
			.replaceAll('}', ' ')
			.replaceAll('<', ' ')
			.replaceAll('>', ' ')
			.replaceAll('-', ' ')
			.replaceAll('#', ' ')
			.replaceAll('"', ' ')
			.replaceAll('\'', ' ')
			.replace(/\s\s+/g, ' ')
		;

		return clearedString.split(' ').filter(word => word !== '');
	},

	getTypeByEntityId(entityId: string): string
	{
		switch (entityId)
		{
			case SearchEntityIdTypes.user:
			case SearchEntityIdTypes.bot:
				return 'user';
			case SearchEntityIdTypes.chat:
			case SearchEntityIdTypes.chatUser:
				return 'chat';
			case SearchEntityIdTypes.department:
				return 'department';
			case SearchEntityIdTypes.network:
				return 'network';
			default:
				throw new Error(`Unknown entity id: ${entityId}`);
		}
	},

	createItemMap(items: Array): Map<string, SearchItem>
	{
		const map = new Map();

		items.forEach(item => {
			const mapItem = new SearchItem(item);
			map.set(mapItem.getEntityFullId(), mapItem);
		});

		return map;
	},

	getFirstItemFromMap(map: Map<string, SearchItem>): SearchItem
	{
		const iterator = map.entries();
		const firstIteration = iterator.next();
		const firstItem = firstIteration.value;
		const [, content] = firstItem;

		return content;
	},

	convertKeysToLowerCase(object: Object): Object
	{
		const result = {};
		Object.keys(object).forEach(key => {
			if (Type.isObject(object[key]) && !Type.isArray(object[key]))
			{
				result[key.toLowerCase()] = this.convertKeysToLowerCase(object[key]);
			}
			else
			{
				result[key.toLowerCase()] = object[key];
			}
		});

		return result;
	},

	convertKeysToUpperCase(object: Object): Object
	{
		const result = {};
		Object.keys(object).forEach(key => {
			if (Type.isObject(object[key]) && !Type.isArray(object[key]))
			{
				result[key.toUpperCase()] = this.convertKeysToUpperCase(object[key]);
			}
			else
			{
				result[key.toUpperCase()] = object[key];
			}
		});

		return result;
	},

	prepareRecentItems(recentItems: Array<string, number>): Array<Object>
	{
		if (!recentItems)
		{
			return [];
		}

		return recentItems.map(item => {
			const [entityId, id] = item;
			const type = SearchUtils.getTypeByEntityId(entityId);

			return {
				cacheId: `${type}|${id}`,
				date: new Date(),
			};
		});
	}
};