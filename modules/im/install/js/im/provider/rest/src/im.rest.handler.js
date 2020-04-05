/**
 * Bitrix Messenger
 * Im rest answers (Rest Answer Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {BaseRestAnswerHandler} from "./base.answer";
import {FileStatus} from "im.const";
import {VuexBuilderModel} from "ui.vue.vuex";

class ImRestAnswerHandler extends BaseRestAnswerHandler
{
	handleImUserListGetSuccess(data)
	{
		this.store.dispatch('users/set', VuexBuilderModel.convertToArray(data));
	}

	handleImUserGetSuccess(data)
	{
		this.store.dispatch('users/set', [data]);
	}

	handleImChatGetSuccess(data)
	{
		this.store.dispatch('dialogues/set', data);
	}

	handleImDialogMessagesGetSuccess(data)
	{
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/setBefore', this.controller.prepareFilesBeforeSave(data.files));
		this.store.dispatch('messages/setBefore', data.messages);
	}

	handleImDialogMessagesGetInitSuccess(data)
	{
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
		this.store.dispatch('messages/set', data.messages.reverse());
	}

	handleImDialogMessagesGetUnreadSuccess(data)
	{
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
		this.store.dispatch('messages/setAfter', data.messages);
	}

	handleImDiskFolderGetSuccess(data)
	{
		this.store.commit('application/set', {dialog: {
			diskFolderId: data.ID,
		}});
	}

	handleImMessageAddSuccess(messageId, message)
	{
		this.store.dispatch('messages/update', {
			id:  message.id,
			chatId: message.chatId,
			fields: {
				id: messageId,
				sending: false,
				error: false,
			}
		}).then(() => {
			this.store.dispatch('messages/actionFinish', {
				id: messageId,
				chatId: message.chatId
			});
		});
	}

	handleImMessageAddError(error, message)
	{
		this.store.dispatch('messages/actionError', {
			id: message.id,
			chatId: message.chatId
		});
	}

	handleImDiskFileCommitSuccess(result, message)
	{
		this.store.dispatch('messages/update', {
			id:  message.id,
			chatId: message.chatId,
			fields: {
				id: result['MESSAGE_ID'],
				sending: false,
				error: false,
			}
		}).then(() => {
			this.store.dispatch('messages/actionFinish', {
				id: result['MESSAGE_ID'],
				chatId: message.chatId
			});
		});
	}

	handleImDiskFileCommitError(error, message)
	{
		this.store.dispatch('files/update', {
			chatId: message.chatId,
			id: message.file.id,
			fields: {
				status: FileStatus.error,
				progress: 0
			}
		});
		this.store.dispatch('messages/actionError', {
			id: message.id,
			chatId: message.chatId,
			retry: false
		});
	}
}

export {ImRestAnswerHandler};