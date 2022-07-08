/**
 * Bitrix Messenger
 * Im rest answers (Rest Answer Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {BaseRestHandler} from "./base.rest";
import { EventType, FileStatus } from "im.const";
import {VuexBuilderModel} from "ui.vue.vuex";
import {Logger} from "im.lib.logger";
import { EventEmitter } from "main.core.events";

class CoreRestHandler extends BaseRestHandler
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
		this.store.dispatch('files/setBefore', this.controller.application.prepareFilesBeforeSave(data.files));
		// this.store.dispatch('messages/setBefore', data.messages);
	}

	handleImDialogMessagesGetInitSuccess(data)
	{
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(data.files));
		//handling messagesSet for empty chat
		if (data.messages.length === 0 && data.chat_id)
		{
			Logger.warn('setting messagesSet for empty chat', data.chat_id);
			setTimeout(() => {
				EventEmitter.emit(EventType.dialog.messagesSet, {chatId: data.chat_id});
			}, 100);
		}
		else
		{
			this.store.dispatch('messages/set', data.messages.reverse());
		}
	}

	handleImDialogMessagesGetUnreadSuccess(data)
	{
		this.store.dispatch('users/set', data.users);
		this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(data.files));
		// this.store.dispatch('messages/setAfter', data.messages);
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
			id: message.id,
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

	handleImRecentListSuccess(result, message)
	{
		Logger.warn('Provider.Rest.handleImRecentGetSuccess', result);
		const users = [];
		const dialogues = [];
		const recent = [];
		result.items.forEach(item => {
			let userId = 0;
			let chatId = 0;

			if (item.user && item.user.id > 0)
			{
				userId = item.user.id;
				users.push(item.user);
			}
			if (item.chat)
			{
				chatId = item.chat.id;
				dialogues.push(Object.assign(item.chat, {dialogId: item.id}));
			}
			else
			{
				dialogues.push(Object.assign({}, {dialogId: item.id}));
			}
			recent.push({
				...item,
				avatar: item.avatar.url,
				color: item.avatar.color,
				userId: userId,
				chatId: chatId
			});
		});

		this.store.dispatch('users/set', users);
		this.store.dispatch('dialogues/set', dialogues);
		this.store.dispatch('recent/set', recent)
	}
}

export {CoreRestHandler};
