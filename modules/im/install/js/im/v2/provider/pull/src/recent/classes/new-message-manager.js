import { Core } from 'im.v2.application.core';
import { ChatType, RecentType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';

import type { PullExtraParams, RawChat } from '../../types/common';
import type { MessageAddParams } from '../../types/message';

const ActionNameByRecentType = {
	[RecentType.default]: 'recent/setRecent',
	[RecentType.copilot]: 'recent/setCopilot',
	[RecentType.openChannel]: 'recent/setChannel',
	[RecentType.collab]: 'recent/setCollab',
	[RecentType.taskComments]: 'recent/setTask',
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

	getRecentTypes(): $Values<typeof RecentType>[]
	{
		return this.#params.recentConfig.sections;
	}

	isLinesChat(): boolean
	{
		return Boolean(this.#params.lines);
	}

	isCommentChat(): boolean
	{
		return this.getChatType() === ChatType.comment;
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
		const recentTypes = this.getRecentTypes();

		return recentTypes.map((recentType) => {
			return ActionNameByRecentType[recentType];
		});
	}
}
