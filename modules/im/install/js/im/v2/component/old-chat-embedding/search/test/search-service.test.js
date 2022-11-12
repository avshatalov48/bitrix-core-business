import {SearchService} from '../src/search-service';
import {Type} from 'main.core';
import {SearchItem} from '../src/search-item';
import {DumbData} from './dumb-data';
import {SearchUtils} from '../src/search-utils';

describe('SearchService', () => {
	const store = {};
	const controller = null;
	let restClient = null;
	let $Bitrix = null;
	let searchService = null;

	before(async () => {
		restClient = {
			callMethod: () => {}
		};
		$Bitrix = {
			Data: {
				get()
				{
					return {store, controller};
				}
			},
			RestClient: {
				get()
				{
					return restClient;
				}
			}
		};
		searchService = SearchService.getInstance($Bitrix, {}, {});
	});

	it('should be a Function', () => {
		assert.equal(Type.isFunction(SearchService), true);
	});

	describe('sortItemsByEntityIdAndContextSort', () => {
		it('should return the same map for one element', () => {
			const item = new SearchItem(DumbData.providerData[0]);
			const mapBeforeSort = new Map().set(item.getDialogId(), item);
			const firstElementBeforeSort = [...mapBeforeSort.values()][0];

			const sortedMap = searchService.sortItemsByEntityIdAndContextSort(mapBeforeSort);
			const firstElementAfterSort = [...sortedMap.values()][0];
			assert.equal(firstElementBeforeSort, firstElementAfterSort);
		});

		it('should return a user chat before a group chat', () => {
			const groupChat = new SearchItem(DumbData.providerData[0]);
			const userChat = new SearchItem(DumbData.providerData[3]);
			const mapBeforeSort = new Map();
			mapBeforeSort.set(groupChat.getDialogId(), groupChat);
			mapBeforeSort.set(userChat.getDialogId(), userChat);

			const sortedMap = searchService.sortItemsByEntityIdAndContextSort(mapBeforeSort);
			const sortedMapElements = [...sortedMap.values()];
			assert.equal(userChat, sortedMapElements[0]);
			assert.equal(groupChat, sortedMapElements[1]);
		});

		it('should consider a contextSort value', () => {
			const firstChat = new SearchItem(DumbData.providerData[0]);
			const secondChat = new SearchItem(DumbData.providerData[1]);
			const userChat = new SearchItem(DumbData.providerData[3]);
			const mapBeforeSort = new Map();
			mapBeforeSort.set(firstChat.getDialogId(), firstChat);
			mapBeforeSort.set(secondChat.getDialogId(), secondChat);
			mapBeforeSort.set(userChat.getDialogId(), userChat);

			const sortedMap = searchService.sortItemsByEntityIdAndContextSort(mapBeforeSort);
			const sortedMapElements = [...sortedMap.values()];
			assert.equal(userChat, sortedMapElements[0]);
			assert.equal(secondChat, sortedMapElements[1]);
			assert.equal(firstChat, sortedMapElements[2]);
		});

		it('should return a user on the first place and an extranet user on the last place', () => {
			const mapBeforeSort = SearchUtils.createItemMap(DumbData.providerData);

			const sortedMap = searchService.sortItemsByEntityIdAndContextSort(mapBeforeSort);
			const sortedMapElements = [...sortedMap.values()];
			assert.equal(DumbData.providerData[3].id, sortedMapElements[0].id);
			assert.equal(DumbData.providerData[6].id, sortedMapElements[6].id);
		});
	});
});