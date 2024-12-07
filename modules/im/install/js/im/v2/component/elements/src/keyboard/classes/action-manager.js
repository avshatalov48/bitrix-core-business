import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import 'ui.notification';

import { Messenger } from 'im.public';
import { EventType, KeyboardButtonAction } from 'im.v2.const';
import { SendingService } from 'im.v2.provider.service';
import { PhoneManager } from 'im.v2.lib.phone';

import type { ActionEvent } from '../types/events';

export class ActionManager
{
	#dialogId: string;
	#actionHandlers: ActionConfig = {
		[KeyboardButtonAction.send]: this.#sendMessage.bind(this),
		[KeyboardButtonAction.put]: this.#insertText.bind(this),
		[KeyboardButtonAction.call]: this.#startCall.bind(this),
		[KeyboardButtonAction.copy]: this.#copyText.bind(this),
		[KeyboardButtonAction.dialog]: this.#openChat.bind(this),
	};

	constructor(dialogId)
	{
		this.#dialogId = dialogId;
	}

	handleAction(event: ActionEvent): void
	{
		const { action, payload } = event;
		if (!this.#actionHandlers[action])
		{
			// eslint-disable-next-line no-console
			console.error('Keyboard: action not found');
		}

		this.#actionHandlers[action](payload);
	}

	#sendMessage(payload: string): void
	{
		SendingService.getInstance().sendMessage({
			text: payload,
			dialogId: this.#dialogId,
		});
	}

	#insertText(payload: string): void
	{
		EventEmitter.emit(EventType.textarea.insertText, {
			text: payload,
			dialogId: this.#dialogId,
		});
	}

	#startCall(payload: string): void
	{
		PhoneManager.getInstance().startCall(payload);
	}

	#copyText(payload: string): void
	{
		if (BX.clipboard?.copy(payload))
		{
			BX.UI.Notification.Center.notify({
				content: Loc.getMessage('IM_ELEMENTS_KEYBOARD_BUTTON_ACTION_COPY_SUCCESS'),
			});
		}
	}

	#openChat(payload: string): void
	{
		Messenger.openChat(payload);
	}
}
