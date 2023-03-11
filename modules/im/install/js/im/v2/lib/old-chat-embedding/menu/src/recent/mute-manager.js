import {RestMethod} from 'im.v2.const';

export class MuteManager
{
	store: Object = null;
	restClient: Object = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
	}

	muteDialog(dialogId: string)
	{
		this.store.dispatch('dialogues/mute', {dialogId});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'Y'};
		this.restClient.callMethod(RestMethod.imChatMute, queryParams).catch(error => {
			console.error('Im.RecentList: error muting chat', error);
			this.store.dispatch('dialogues/unmute', {dialogId});
		});
	}

	unmuteDialog(dialogId: string)
	{
		this.store.dispatch('dialogues/unmute', {dialogId});
		const queryParams = {'DIALOG_ID': dialogId, 'ACTION': 'N'};
		this.restClient.callMethod(RestMethod.imChatMute, queryParams).catch(error => {
			console.error('Im.RecentList: error unmuting chat', error);
			this.store.dispatch('dialogues/mute', {dialogId});
		});
	}
}