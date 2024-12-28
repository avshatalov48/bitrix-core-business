import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';

import type { PullExtraParams, RawChat } from '../../types/common';
import type { MessageAddParams } from '../../types/message';

const ActionNameByChatType = {
	[ChatType.copilot]: 'recent/setCopilot',
	[ChatType.channel]: 'recent/setChannel',
	[ChatType.openChannel]: 'recent/setChannel',
	[ChatType.generalChannel]: 'recent/setChannel',
	[ChatType.collab]: 'recent/setCollab',
	default: 'recent/setRecent',
};

export class NewMessageManager
{
	#params: MessageAddParams;
	#extra: PullExtraParams;

	constructor(params: MessageAddParams, extra: PullExtraParams = {})
	{
		this.#params = params;
		this.#extra = extra;
	}

	getChatId(): number
	{
		return this.#params.chatId;
	}

	getParentChatId(): number
	{
		return this.getChat()?.parent_chat_id || 0;
	}

	getChat(): ?RawChat
	{
		const chatId = this.getChatId();

		return this.#params.chat?.[chatId];
	}

	getChatType(): string
	{
		const chat = this.getChat();

		return chat?.type ?? '';
	}

	isLinesChat(): boolean
	{
		return Boolean(this.#params.lines);
	}

	isCommentChat(): boolean
	{
		return this.getChatType() === ChatType.comment;
	}

	isCollabChat(): boolean
	{
		return this.getChatType() === ChatType.collab;
	}

	isChannelChat(): boolean
	{
		return ChannelManager.channelTypes.has(this.getChatType());
	}

	isUserInChat(): boolean
	{
		const chatUsers = this.#params.userInChat[this.getChatId()];
		if (!chatUsers || this.isChannelListEvent())
		{
			return true;
		}

		return chatUsers.includes(Core.getUserId());
	}

	isChannelListEvent(): boolean
	{
		return this.isChannelChat() && this.#extra.is_shared_event;
	}

	needToSkipMessageEvent(): boolean
	{
		return this.isLinesChat() || this.isCommentChat() || !this.isUserInChat();
	}

	getAddActions(): string[]
	{
		// for open channels there are two similar P&P events
		// one adds data to default recent, another adds data to channel recent
		// close channels are added only to default recent
		if (this.isChannelChat() && !this.isChannelListEvent())
		{
			return [ActionNameByChatType.default];
		}

		if (this.isCollabChat())
		{
			return [ActionNameByChatType.default, ActionNameByChatType[ChatType.collab]];
		}

		const newMessageChatType = this.getChatType();
		const actionName = ActionNameByChatType[newMessageChatType] ?? ActionNameByChatType.default;

		return [actionName];
	}
}
