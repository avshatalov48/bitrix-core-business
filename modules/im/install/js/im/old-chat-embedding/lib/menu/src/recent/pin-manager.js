import {RestMethod} from 'im.old-chat-embedding.const';

export class PinManager
{
	store: Object = null;
	restClient: Object = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
	}

	pinDialog(dialogId: string)
	{
		this.store.dispatch('recent/pin', {id: dialogId, action: true});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'Y'};
		this.restClient.callMethod(RestMethod.imRecentPin, queryParams).catch(error => {
			console.error('Im.RecentList: error pinning chat', error);
			this.store.dispatch('recent/pin', {id: dialogId, action: false});
		});
	}

	unpinDialog(dialogId: string)
	{
		this.store.dispatch('recent/pin', {id: dialogId, action: false});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'N'};
		this.restClient.callMethod(RestMethod.imRecentPin, queryParams).catch(error => {
			console.error('Im.RecentList: error unpinning chat', error);
			this.store.dispatch('recent/pin', {id: dialogId, action: true});
		});
	}
}