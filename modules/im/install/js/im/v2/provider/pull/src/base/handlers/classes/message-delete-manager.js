import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { InputActionListener } from 'im.v2.lib.input-action';
import { Notifier } from 'im.v2.lib.notifier';

import type {
	MessageDeletePreparedParams,
	MessageDeleteCompletePreparedParams,
	DialogUpdateFieldsParams,
} from '../../../types/message';

export class MessageDeleteManager
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	deleteMessage(params: MessageDeletePreparedParams)
	{
		this.#stopWriting(params.dialogId, params.senderId);

		void this.#store.dispatch('messages/update', {
			id: params.id,
			fields: {
				text: '',
				isDeleted: true,
				files: [],
				attach: [],
				replyId: 0,
			},
		});
	}

	deleteMessageComplete(params: MessageDeleteCompletePreparedParams)
	{
		this.#stopWriting(params.dialogId, params.senderId);

		const areChannelCommentsOpened = this.#store.getters['messages/comments/areOpenedForChannelPost'](params.id);
		if (areChannelCommentsOpened)
		{
			this.#closeChannelComments(params);
		}

		void this.#store.dispatch('messages/delete', {
			id: params.id,
		});

		const dialogUpdateFields = this.#prepareDialogUpdateFields(params);

		void this.#store.dispatch('chats/update', {
			dialogId: params.dialogId,
			fields: dialogUpdateFields,
		});
	}

	#stopWriting(dialogId: number, userId: number)
	{
		InputActionListener.getInstance().stopAction({ dialogId, userId });
	}

	#closeChannelComments(params: MessageDeleteCompletePreparedParams)
	{
		EventEmitter.emit(EventType.dialog.closeComments);
		Analytics.getInstance().messageDelete.onDeletedPostNotification({
			dialogId: params.dialogId,
			messageId: params.id,
		});

		Notifier.message.onNotFoundError();
	}

	#prepareDialogUpdateFields(params: MessageDeleteCompletePreparedParams): DialogUpdateFieldsParams
	{
		const dialogUpdateFields = {
			counter: params.counter,
		};

		const lastMessageWasDeleted = Boolean(params.newLastMessage);
		if (lastMessageWasDeleted)
		{
			dialogUpdateFields.lastMessageId = params.newLastMessage.id;
			dialogUpdateFields.lastMessageViews = params.lastMessageViews;

			void this.#store.dispatch('messages/store', params.newLastMessage);
		}

		return dialogUpdateFields;
	}
}
