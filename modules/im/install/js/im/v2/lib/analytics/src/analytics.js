import { sendData } from 'ui.analytics';

import { ChatType, Layout, UserRole } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import { AnalyticsEvent, AnalyticsTool, AnalyticsCategory } from './const';

import { getCollabId } from './helpers/get-collab-id';
import { getUserType } from './helpers/get-user-type';
import { getCategoryByChatType } from './helpers/get-category-by-chat-type';
import { getChatType } from './helpers/get-chat-type';

import { MessageFiles } from './classes/message-files';
import { CollabEntities } from './classes/collab-entities';
import { ChatEntities } from './classes/chat-entities';
import { ChatDelete } from './classes/chat-delete';
import { MessageDelete } from './classes/message-delete';
import { HistoryLimit } from './classes/history-limit';
import { UserAdd } from './classes/user-add';
import { ChatEdit } from './classes/chat-edit';
import { ChatCreate } from './classes/chat-create';
import { Supervisor } from './classes/supervisor';
import { CheckIn } from './classes/check-in';
import { Copilot } from './classes/copilot';
import { AttachMenu } from './classes/attach-menu';

import type { ImModelChat } from 'im.v2.model';

type DialogId = string;

export { CreateChatContext } from './const';
export { getCollabId } from './helpers/get-collab-id';
export { getUserType } from './helpers/get-user-type';

export class Analytics
{
	#excludedChats: Set<DialogId> = new Set();
	#currentTab: string = Layout.chat.name;

	chatCreate: ChatCreate = new ChatCreate();
	chatEdit: ChatEdit = new ChatEdit();
	chatDelete: ChatDelete = new ChatDelete();
	messageDelete: MessageDelete = new MessageDelete();
	historyLimit: HistoryLimit = new HistoryLimit();
	userAdd: UserAdd = new UserAdd();
	collabEntities: CollabEntities = new CollabEntities();
	chatEntities: ChatEntities = new ChatEntities();
	supervisor: Supervisor = new Supervisor();
	checkIn: CheckIn = new CheckIn();
	copilot: Copilot = new Copilot();
	attachMenu: AttachMenu = new AttachMenu();
	messageFiles: MessageFiles = new MessageFiles();

	static #instance: Analytics;

	static getInstance(): Analytics
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	ignoreNextChatOpen(dialogId: string): void
	{
		this.#excludedChats.add(dialogId);
	}

	onOpenMessenger()
	{
		sendData({
			event: AnalyticsEvent.openMessenger,
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.messenger,
		});
	}

	onOpenTab(tabName: string)
	{
		const existingTabs = [
			Layout.chat.name,
			Layout.copilot.name,
			Layout.collab.name,
			Layout.channel.name,
			Layout.notification.name,
			Layout.settings.name,
			Layout.openlines.name,
		];

		if (!existingTabs.includes(tabName))
		{
			return;
		}

		if (this.#currentTab === tabName)
		{
			return;
		}

		this.#currentTab = tabName;

		sendData({
			event: AnalyticsEvent.openTab,
			tool: AnalyticsTool.im,
			category: AnalyticsCategory.messenger,
			type: tabName,
			p2: getUserType(),
		});
	}

	onOpenChat(dialog: ImModelChat)
	{
		if (this.#excludedChats.has(dialog.dialogId))
		{
			this.#excludedChats.delete(dialog.dialogId);

			return;
		}

		const chatType = getChatType(dialog);

		if (chatType === ChatType.copilot)
		{
			this.copilot.onOpenChat(dialog.dialogId);
		}

		const currentLayout = Core.getStore().getters['application/getLayout'].name;
		const isMember = dialog.role === UserRole.guest ? 'N' : 'Y';

		const params = {
			tool: AnalyticsTool.im,
			category: getCategoryByChatType(chatType),
			event: AnalyticsEvent.openExisting,
			type: chatType,
			c_section: `${currentLayout}_tab`,
			p2: getUserType(),
			p3: `isMember_${isMember}`,
			p5: `chatId_${dialog.chatId}`,
		};

		if (chatType === ChatType.comment)
		{
			const parentChat = Core.getStore().getters['chats/getByChatId'](dialog.parentChatId);
			params.p1 = `chatType_${parentChat.type}`;
			params.p4 = `parentChatId_${dialog.parentChatId}`;
		}

		if (chatType === ChatType.collab)
		{
			params.p4 = getCollabId(dialog.chatId);
		}

		sendData(params);
	}
}
