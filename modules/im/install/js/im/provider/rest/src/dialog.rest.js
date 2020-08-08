/**
 * Bitrix Im
 * Dialog Rest answers (Rest Answer Handler)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2023 Bitrix
 */

import {BaseRestHandler} from "./base.rest";
import {EventType} from "im.const";

export class DialogRestHandler extends BaseRestHandler
{
	constructor(params)
	{
		super(params);

		this.application = params.application;
	}

	handleImChatGetSuccess(data)
	{
		this.store.commit('application/set', {dialog: {
			chatId: data.id,
			dialogId: data.dialog_id,
			diskFolderId: data.disk_folder_id,
		}});
	}
	handleImChatGetError(error)
	{
		if (error.ex.error === 'ACCESS_ERROR')
		{
			Logger.error('MobileRestAnswerHandler.handleImChatGetError: ACCESS_ERROR')
		//	app.closeController();
		}
	}

	handleImDialogMessagesGetInitSuccess()
	{
		this.controller.application.emit(EventType.dialog.sendReadMessages);
	}

	handleImMessageAddSuccess(messageId, message)
	{
		this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	}

	handleImMessageAddError(error, message)
	{
		this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	}

	handleImDiskFileCommitSuccess(result, message)
	{
		this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	}
}