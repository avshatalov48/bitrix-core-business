import { Text } from 'main.core';
import { sendData } from 'ui.analytics';

import { Core } from 'im.v2.application.core';
import { ChatType, FileType } from 'im.v2.const';
import { getCollabId, getUserType } from 'im.v2.lib.analytics';
import { MessageComponentManager } from 'im.v2.lib.message-component';

import {
	AnalyticsCategory,
	AnalyticsElement,
	AnalyticsEvent,
	AnalyticsSection,
	AnalyticsSubSection,
	AnalyticsTool,
} from '../const';
import { getCategoryByChatType } from '../helpers/get-category-by-chat-type';
import { MessageForward } from './message-forward';
import { MessagePins } from './message-pins';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelFile, ImModelMessage } from 'im.v2.model';

const AnalyticsAmountFilesType = {
	single: 'files_single',
	many: 'files_all',
};

const AnalyticsFileType = {
	...FileType,
	media: 'media',
	any: 'any',
};

export class MessageContextMenu
{
	messageForward: MessageForward = new MessageForward();
	messagePins: MessagePins = new MessagePins();

	onSendFeedback(dialogId: string): void
	{
		const currentLayout = Core.getStore().getters['application/getLayout'].name;
		const role = Core.getStore().getters['copilot/chats/getRole'](dialogId);
		const aiModel = Core.getStore().getters['copilot/chats/getAIModel'](dialogId);

		const aiModelName = aiModel.name ?? aiModel;

		sendData({
			category: AnalyticsCategory.copilot,
			event: AnalyticsEvent.addFeedback,
			c_section: `${currentLayout}_tab`,
			p2: `provider_${aiModelName}`,
			p4: `role_${Text.toCamelCase(role.code)}`,
			...this.#getBaseParams(dialogId),
		});
	}

	onDelete({ messageId, dialogId }: {messageId: string | number, dialogId: string}): void
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();

		sendData({
			category: AnalyticsCategory.message,
			event: AnalyticsEvent.clickDelete,
			type,
			c_section: AnalyticsSection.chatWindow,
			...this.#getBaseParams(dialogId),
		});
	}

	onCancelDelete({ messageId, dialogId }: {messageId: string | number, dialogId: string}): void
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		sendData({
			category: AnalyticsCategory.message,
			event: AnalyticsEvent.cancelDelete,
			type,
			c_section: AnalyticsSection.popup,
			p5: `chatId_${chat.chatId}`,
			...this.#getBaseParams(dialogId),
		});
	}

	onFileDownload({ messageId, dialogId }: {messageId: string | number, dialogId: string}): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.downloadFile,
			type: this.#getAnalyticsFileType(messageId),
			c_section: AnalyticsSection.chatWindow,
			p2: getUserType(),
			p3: this.#getFilesAmountParam(messageId),
			p5: `chatId_${chat.chatId}`,
			...this.#getBaseParams(dialogId),
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}

	onSaveOnDisk({ messageId, dialogId }: {messageId: string | number, dialogId: string}): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId);

		const params = {
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.saveToDisk,
			type: this.#getAnalyticsFileType(messageId),
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			p2: getUserType(),
			p3: this.#getFilesAmountParam(messageId),
			p5: `chatId_${chat.chatId}`,
			...this.#getBaseParams(dialogId),
		};

		if (chat.type === ChatType.collab)
		{
			params.p4 = getCollabId(chat.chatId);
		}

		sendData(params);
	}

	onCopyLink(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.copyLink,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onCopyFile({ dialogId, fileId }: {dialogId: string, fileId: string | number}): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const file: ImModelFile = Core.getStore().getters['files/get'](fileId);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.copyFile,
			type: file.type,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onEdit(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickEdit,
			c_section: AnalyticsSection.chatWindow,
			...this.#getBaseParams(dialogId),
		});
	}

	onCreateTask(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickCreateTask,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onSelect(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.select,
			c_section: AnalyticsSection.chatWindow,
			...this.#getBaseParams(dialogId),
		});
	}

	onMark(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.seeLater,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onReply(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickReply,
			c_section: AnalyticsSection.chatWindow,
			...this.#getBaseParams(dialogId),
		});
	}

	onAddFavorite({ dialogId, messageId }: {dialogId: string, messageId: string | number}): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.addToFav,
			type,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onCreateEvent(dialogId: string): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.clickCreateEvent,
			c_section: AnalyticsSection.chatWindow,
			c_element: AnalyticsElement.more,
			...this.#getBaseParams(dialogId),
		});
	}

	onCopyText({ dialogId, messageId }: {dialogId: string, messageId: string | number}): void
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const type = new MessageComponentManager(message).getName();

		if (chat.type === ChatType.copilot)
		{
			this.#onCopyTextCopilot({
				dialogId,
				messageId,
			});

			return;
		}

		sendData({
			category: getCategoryByChatType(chat.type),
			event: AnalyticsEvent.copyMessage,
			type,
			c_section: AnalyticsSection.chatWindow,
			...this.#getBaseParams(dialogId),
		});
	}

	onPin(dialogId: string): void
	{
		this.messagePins.onSendData({
			dialogId,
			eventParams: {
				event: AnalyticsEvent.pinMessage,
				c_section: AnalyticsSection.chatWindow,
			},
		});
	}

	onReachingPinsLimit(dialogId: string): void
	{
		this.messagePins.onSendData({
			dialogId,
			eventParams: {
				event: AnalyticsEvent.pinnedMessageLimitException,
				c_section: AnalyticsSection.chatWindow,
			},
		});
	}

	onUnpin(dialogId: string): void
	{
		this.messagePins.onUnpin({
			dialogId,
			eventParams: {
				c_section: AnalyticsSection.chatWindow,
			},
		});
	}

	onForward(dialogId: string): void
	{
		this.messageForward.onClickForward(dialogId);
	}

	#onCopyTextCopilot({ dialogId, messageId }: {dialogId: string, messageId: string | number}): void
	{
		const aiModel = Core.getStore().getters['copilot/chats/getAIModel'](dialogId);
		const currentLayout = Core.getStore().getters['application/getLayout'].name;
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		const role = Core.getStore().getters['copilot/chats/getRole'](dialogId);

		const type = new MessageComponentManager(message).getName();

		const aiModelName = aiModel.name ?? aiModel;

		sendData({
			category: AnalyticsCategory.copilot,
			event: AnalyticsEvent.copyMessage,
			type,
			c_section: `${currentLayout}_tab`,
			p2: `provider_${aiModelName}`,
			p4: `role_${Text.toCamelCase(role.code)}`,
			p5: `chatId_${chat.chatId}`,
			...this.#getBaseParams(dialogId),
		});
	}

	#getFilesAmountParam(messageId: string | number): string
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);
		if (message.files.length === 1)
		{
			return AnalyticsAmountFilesType.single;
		}

		return AnalyticsAmountFilesType.many;
	}

	#getAnalyticsFileType(messageId: string | number): $Values<typeof AnalyticsFileType>
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

	#getBaseParams(dialogId: string): JsonObject
	{
		const chat: ImModelChat = Core.getStore().getters['chats/get'](dialogId, true);

		return {
			tool: AnalyticsTool.im,
			c_sub_section: AnalyticsSubSection.contextMenu,
			p1: `chatType_${chat.type}`,
		};
	}
}
