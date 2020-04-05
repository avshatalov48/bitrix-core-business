/**
 * Bitrix Messenger
 * Im rest answers (Rest Answer Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {BaseRestAnswerHandler} from "./base.answer";

class ImRestAnswerHandler extends BaseRestAnswerHandler
{
	handleImChatGetSuccess(data)
	{
		this.store.dispatch('dialogues/set', data);
	}

	handleImDialogMessagesGetSuccess(data)
	{
		this.store.dispatch('messages/setBefore', data.messages);
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/setBefore', this.controller.prepareFilesBeforeSave(data.files));
	}

	handleImDialogMessagesUnreadSuccess(data)
	{
		this.store.dispatch('messages/set', data.messages);
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
	}

	handleImDiskFolderGetSuccess(data)
	{
		this.store.commit('application/set', {dialog: {
			diskFolderId: data.ID,
		}});
	}

	handleImMessageAddSuccess(messageId, message)
	{
		if (typeof messageId === "number")
		{
			this.store.dispatch('messages/update', {
				id:  message.id,
				chatId: this.controller.getChatId(),
				fields: {
					id: messageId,
					sending: false,
					error: false,
				}
			});

			this.store.dispatch('messages/actionFinish', {
				id: messageId,
				chatId: this.controller.getChatId()
			});
		}
		else
		{
			this.store.dispatch('messages/actionError', {
				id: message.id,
				chatId: this.controller.getChatId()
			});
		}
	}

	handleImMessageAddError(error, message)
	{
		this.store.dispatch('messages/actionError', {
			id: message.id,
			chatId: this.controller.getChatId()
		});
	}
}

export {ImRestAnswerHandler};