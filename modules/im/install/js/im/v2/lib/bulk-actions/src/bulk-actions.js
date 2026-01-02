import { BaseEvent, EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { EscEventAction } from 'im.v2.lib.esc-manager';

export class BulkActionsManager
{
	static #instance: BulkActionsManager;

	static getInstance(): BulkActionsManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		BulkActionsManager.getInstance();
	}

	constructor()
	{
		EventEmitter.subscribe(EventType.dialog.openBulkActionsMode, this.enableBulkMode.bind(this));
		EventEmitter.subscribe(EventType.dialog.closeBulkActionsMode, this.disableBulkMode.bind(this));

		this.keyPressHandler = this.#onKeyPressCloseBulkActions.bind(this);
	}

	enableBulkMode(event: BaseEvent<{messageId: number, dialogId: string}>)
	{
		const { messageId, dialogId } = event.getData();

		void Core.getStore().dispatch('messages/select/enableBulkMode', {
			messageId,
			dialogId,
		});

		this.#bindEscHandler();
	}

	disableBulkMode(event: BaseEvent<{dialogId: string}>)
	{
		const { dialogId } = event.getData();

		void Core.getStore().dispatch('messages/select/disableBulkMode', {
			dialogId,
		});

		this.#unbindEscHandler();
	}

	clearCollection()
	{
		void Core.getStore().dispatch('messages/select/clearCollection');

		this.#unbindEscHandler();
	}

	#bindEscHandler()
	{
		EventEmitter.subscribe(EventType.key.onBeforeEscape, this.keyPressHandler);
	}

	#unbindEscHandler()
	{
		EventEmitter.unsubscribe(EventType.key.onBeforeEscape, this.keyPressHandler);
	}

	#onKeyPressCloseBulkActions(): $Values<typeof EscEventAction>
	{
		this.clearCollection();

		return EscEventAction.handled;
	}
}
