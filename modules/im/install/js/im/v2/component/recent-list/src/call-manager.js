import {EventEmitter} from 'main.core.events';
import {RecentCallStatus} from 'im.v2.const';

export class CallManager
{
	static instance = null;
	store: Object = null;

	static init($Bitrix): void
	{
		if (this.instance)
		{
			return;
		}

		this.instance = new this($Bitrix);
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

	destroy()
	{
		EventEmitter.unsubscribe(window, 'CallEvents::callCreated', this.onCallCreatedHandler);
	}
}