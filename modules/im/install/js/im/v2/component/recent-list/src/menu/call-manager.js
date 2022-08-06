import {EventEmitter} from 'main.core.events';
import {RecentCallStatus} from 'im.v2.const';

export class CallManager
{
	static instance = null;
	store: Object = null;

	static getInstance($Bitrix)
	{
		if (!this.instance)
		{
			this.instance = new this($Bitrix);
		}

		return this.instance;
	}

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;

		this.onCallCreatedHandler = this.onCallCreated.bind(this);
		EventEmitter.subscribe('CallEvents::callCreated', this.onCallCreatedHandler);
	}

	onCallCreated(event)
	{
		const {call} = event.getData()[0];
		call.addEventListener(BX.Call.Event.onJoin, this.onCallJoin.bind(this));
		call.addEventListener(BX.Call.Event.onLeave, this.onCallLeave.bind(this));
		call.addEventListener(BX.Call.Event.onDestroy, this.onCallDestroy.bind(this));

		this.store.dispatch('recent/addActiveCall', {
			dialogId: call.associatedEntity.id,
			name: call.associatedEntity.name,
			call: call,
			state: RecentCallStatus.waiting
		});
	}

	onCallJoin(event)
	{
		this.store.dispatch('recent/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.joined
			}
		});
	}

	onCallLeave(event)
	{
		this.store.dispatch('recent/updateActiveCall', {
			dialogId: event.call.associatedEntity.id,
			fields: {
				state: RecentCallStatus.waiting
			}
		});
	}

	onCallDestroy(event)
	{
		this.store.dispatch('recent/deleteActiveCall', {
			dialogId: event.call.associatedEntity.id
		});
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
		// on current tab
		if (BX.MessengerProxy.getCallController().hasActiveCall())
		{
			return true;
		}

		// on different tab
		return this.store.getters['recent/hasActiveCall'];
	}

	getCurrentUserId(): number
	{
		return this.store.state.application.common.userId;
	}

	destroy()
	{
		EventEmitter.unsubscribe(window, 'CallEvents::callCreated', this.onCallCreatedHandler);
	}
}