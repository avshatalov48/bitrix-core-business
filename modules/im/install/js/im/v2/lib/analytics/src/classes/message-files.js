import { sendData } from 'ui.analytics';

import { ChatType, FileType } from 'im.v2.const';
import { Core } from 'im.v2.application.core';
import { getCollabId } from 'im.v2.lib.analytics';

import {
	AnalyticsEvent,
	AnalyticsSection,
	AnalyticsSubSection,
	AnalyticsTool,
} from '../const';

import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { getUserType } from '../helpers/get-user-type';

import type { ImModelMessage, ImModelChat } from 'im.v2.model';

const AnalyticsAmountFilesType = {
	single: 'files_single',
	many: 'files_all',
};

const AnalyticsFileType = {
	...FileType,
	media: 'media',
	any: 'any',
};

export class MessageFiles
{
	onClickDownload({ messageId, dialogId }: {messageId: string | number, dialogId: string})
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.downloadFile,
			type: getAnalyticsFileType(messageId),
			c_section: AnalyticsSection.chatWindow,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
			p3: getFilesAmountParam(messageId),
			p5: `chatId_${chat.chatId}`,
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}

	onClickSaveOnDisk({ messageId, dialogId }: {messageId: string | number, dialogId: string})
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.saveToDisk,
			type: getAnalyticsFileType(messageId),
			c_section: AnalyticsSection.chatWindow,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
			p2: getUserType(),
			p3: getFilesAmountParam(messageId),
			p5: `chatId_${chat.chatId}`,
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}
}

function getFilesAmountParam(messageId: string | number): string
{
	const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
	if (message.files.length === 1)
	{
		return AnalyticsAmountFilesType.single;
	}

	return AnalyticsAmountFilesType.many;
}

function getAnalyticsFileType(messageId: string | number): $Values<typeof AnalyticsFileType>
{
	const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
	const fileTypes = message.files.map((fileId) => {
		return Core.getStore().getters['files/get'](fileId).type;
	});

	const uniqueTypes = [...new Set(fileTypes)];

	if (uniqueTypes.length === 1)
	{
		return uniqueTypes[0];
	}

	if (uniqueTypes.length === 2 && uniqueTypes.includes(FileType.image) && uniqueTypes.includes(FileType.video))
	{
		return AnalyticsFileType.media;
	}

	return AnalyticsFileType.any;
}
