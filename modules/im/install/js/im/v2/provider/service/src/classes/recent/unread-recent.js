import {RecentService} from '../../recent';

import type {ImModelRecentItem} from 'im.v2.model';

export class UnreadRecentService extends RecentService
{
	static instance = null;

	static getInstance(): UnreadRecentService
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	getCollection(): ImModelRecentItem[]
	{
		return this.store.getters['recent/getUnreadCollection'];
	}

	loadFirstPage({ignorePreloadedItems = false} = {}): Promise
	{
		this.isLoading = true;

		return this.requestItems({firstPage: true});
	}

	updateModels(rawData): Promise
	{
		const {users, dialogues, recent} = this.prepareDataForModels(rawData);

		const usersPromise = this.store.dispatch('users/set', users);
		const dialoguesPromise = this.store.dispatch('dialogues/set', dialogues);

		const fakeRecent = this.getFakeData(recent);
		const recentPromise = this.store.dispatch('recent/setUnread', fakeRecent);

		return Promise.all([usersPromise, dialoguesPromise, recentPromise]);
	}

	getFakeData(itemsForModel: Object[]): Object[]
	{
		itemsForModel = itemsForModel.slice(-4);
		itemsForModel.forEach(item => {
			this.store.dispatch('dialogues/update', {
				dialogId: item.id,
				fields: {
					counter: 7
				}
			});
		});

		return itemsForModel;
	}

	onUpdateState({data})
	{
		//
	}
}