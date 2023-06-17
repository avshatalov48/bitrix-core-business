import {RestMethod} from 'im.old-chat-embedding.const';

export class UnreadManager
{
	store: Object = null;
	restClient: Object = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
	}

	readDialog(dialogId: string)
	{
		let queryParams;

		const dialog = this.store.getters['dialogues/get'](dialogId, true);
		if (dialog.counter > 0)
		{
			queryParams = {'DIALOG_ID': dialogId};
			this.restClient.callMethod(RestMethod.imDialogRead, queryParams).catch(error => {
				console.error('Im.RecentList: error reading chat', error);
			});

			return;
		}

		this.store.dispatch('recent/unread', {id: dialogId, action: false});
		queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'N'};
		this.restClient.callMethod(RestMethod.imRecentUnread, queryParams).catch(error => {
			console.error('Im.RecentList: error reading chat', error);
			this.store.dispatch('recent/unread', {id: dialogId, action: true});
		});
	}

	unreadDialog(dialogId: string)
	{
		this.store.dispatch('recent/unread', {id: dialogId, action: true});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'Y'};
		this.restClient.callMethod(RestMethod.imRecentUnread, queryParams).catch(error => {
			console.error('Im.RecentList: error unreading chat', error);
			this.store.dispatch('recent/unread', {id: dialogId, action: false});
		});
	}
}