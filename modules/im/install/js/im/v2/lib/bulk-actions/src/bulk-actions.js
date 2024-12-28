import { BaseEvent, EventEmitter } from 'main.core.events';
import { Event } from 'main.core';

import { EventType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';

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

	init()
	{
		this.enableBulkModeHandler = this.enableBulkMode.bind(this);
		this.disableBulkModeHandler = this.disableBulkMode.bind(this);
		this.keyPressHandler = this.#onKeyPressCloseBulkActions.bind(this);

		EventEmitter.subscribe(EventType.dialog.openBulkActionsMode, this.enableBulkModeHandler);
		EventEmitter.subscribe(EventType.dialog.closeBulkActionsMode, this.disableBulkModeHandler);
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.openBulkActionsMode, this.enableBulkModeHandler);
		EventEmitter.unsubscribe(EventType.dialog.closeBulkActionsMode, this.disableBulkModeHandler);

		Event.unbind(document, 'keydown', this.keyPressHandler);
	}

	enableBulkMode(event: BaseEvent<{messageId: number}>)
	{
		const { messageId } = event.getData();

		void Core.getStore().dispatch('messages/select/toggle', messageId);
		this.#toggleBulkActionsMode(true);

		Event.bind(document, 'keydown', this.keyPressHandler);
	}

	disableBulkMode()
	{
		void Core.getStore().dispatch('messages/select/clear');
		this.#toggleBulkActionsMode(false);
	}

	#toggleBulkActionsMode(active: boolean)
	{
		void Core.getStore().dispatch('messages/select/toggleBulkActionsMode', active);
	}

	#onKeyPressCloseBulkActions(event: KeyboardEvent)
	{
		if (Utils.key.isCombination(event, 'Escape'))
		{
			this.disableBulkMode();
		}
	}
}
