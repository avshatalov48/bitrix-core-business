import {SearchItem} from './search-item';
import {SearchEntityIdTypes} from 'im.v2.const';
import {SearchUtils} from './search-utils';
import {Core} from 'im.v2.application.core';
import {LayoutManager} from './layout-manager';

export class SortingResult
{
	constructor()
	{
		this.store = Core.getStore();
		this.layoutManager = new LayoutManager();
	}

	sortItemsByEntityIdAndContextSort(items: Map<string, SearchItem>): Map<string, SearchItem>
	{
		const entityWeight = {
			'user': 100,
			'im-chat': 80,
			'im-chat-user': 80,
			'im-bot': 70,
			'department': 60,
			'extranet': 10,
		};

		return new Map([...items.entries()].sort((firstItem, secondItem) => {
			const [, firstItemValue] = firstItem;
			const [, secondItemValue] = secondItem;

			const secondItemEntityId = secondItemValue.isExtranet() ? 'extranet' : secondItemValue.getEntityId();
			const firstItemEntityId = firstItemValue.isExtranet() ? 'extranet' : firstItemValue.getEntityId();

			if (entityWeight[secondItemEntityId] < entityWeight[firstItemEntityId])
			{
				return -1;
			}
			else if (entityWeight[secondItemEntityId] > entityWeight[firstItemEntityId])
			{
				return 1;
			}
			else
			{
				return secondItemValue.getContextSort() - firstItemValue.getContextSort();
			}
		}));
	}

	allocateSearchResults(items: Map<string, SearchItem>, originalLayoutQuery: string): Object
	{
		const usersAndChats = new Map();
		const chatUsers = new Map();
		const departments = new Map();
		const openLines = new Map();
		const network = new Map();

		items.forEach(item => {
			switch (item.getEntityId())
			{
				case SearchEntityIdTypes.chatUser:
				{
					chatUsers.set(item.getEntityFullId(), item);
					break;
				}
				case SearchEntityIdTypes.department:
				{
					departments.set(item.getEntityFullId(), item);
					break;
				}
				case SearchEntityIdTypes.network:
				{
					network.set(item.getEntityFullId(), item);
					break;
				}
				default:
				{
					if (item.isOpeLinesType())
					{
						openLines.set(item.getEntityFullId(), item);
					}
					else
					{
						usersAndChats.set(item.getEntityFullId(), item);
					}
				}
			}
		});

		return {
			usersAndChats: this.getSortedItems(usersAndChats, originalLayoutQuery),
			chatUsers: chatUsers,
			departments: departments,
			openLines: openLines,
			network: network
		};
	}

	sortItemsBySearchField(items: Map<string, SearchItem>, originalLayoutQuery: string): Map<string, SearchItem>
	{
		let queryWords = SearchUtils.getWordsFromString(originalLayoutQuery);
		if (this.layoutManager.needLayoutChange(originalLayoutQuery))
		{
			const wrongLayoutQueryWords = SearchUtils.getWordsFromString(this.layoutManager.changeLayout(originalLayoutQuery));
			queryWords = [...queryWords, ...wrongLayoutQueryWords];
		}
		const uniqueWords = [...new Set(queryWords)];

		const searchFieldsWeight = {
			title: 10_000,
			name: 1000,
			lastName: 100,
			position: 1,
		};

		items.forEach(item => {
			uniqueWords.forEach(word => {
				if (item.getTitle().toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.title);
				}
				else if (item.getName()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.name);
				}
				else if (item.getLastName()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.lastName);
				}
				else if (item.getPosition()?.toLowerCase().startsWith(word))
				{
					item.addCustomSort(searchFieldsWeight.position);
				}
			});
		});

		return new Map([...items.entries()].sort((firstItem, secondItem) => {
			const [, firstItemValue] = firstItem;
			const [, secondItemValue] = secondItem;

			return secondItemValue.getCustomSort() - firstItemValue.getCustomSort();
		}));
	}

	getSortedItems(items: Map<string, SearchItem>, originalLayoutQuery: string): Map<string, SearchItem>
	{
		let sortedItems = this.sortItemsBySearchField(items, originalLayoutQuery);
		sortedItems = this.sortItemsByEntityIdAndContextSort(sortedItems);

		return sortedItems;
	}
}