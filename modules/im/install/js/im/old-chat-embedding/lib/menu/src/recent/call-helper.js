export class CallHelper
{
	store: Object = null;

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
	}

	checkCallSupport(dialogId: string): boolean
	{
		if (!BX.MessengerProxy.getPushServerStatus() || !BX.Call.Util.isWebRTCSupported())
		{
			return false;
		}

		const userId = Number.parseInt(dialogId, 10);

		return userId > 0 ? this.checkUserCallSupport(userId) : this.checkChatCallSupport(dialogId);
	}

	checkUserCallSupport(userId: number): boolean
	{
		const user = this.store.getters['users/get'](userId);
		return (
			user
			&& user.status !== 'guest'
			&& !user.bot
			&& !user.network
			&& user.id !== this.getCurrentUserId()
			&& !!user.lastActivityDate
		);
	}

	checkChatCallSupport(dialogId: string): boolean
	{
		const dialog = this.store.getters['dialogues/get'](dialogId);
		if (!dialog)
		{
			return false;
		}

		const {userCounter} = dialog;

		return userCounter > 1 && userCounter <= BX.Call.Util.getUserLimit();
	}

	hasActiveCall(): boolean
	{
		return BX.MessengerProxy.getCallController().hasActiveCall();
	}

	getCurrentUserId(): number
	{
		return this.store.state.application.common.userId;
	}
}