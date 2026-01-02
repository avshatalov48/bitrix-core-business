import { ChatType } from 'im.v2.const';
import { Parser } from 'im.v2.lib.parser';

import { NotificationId, NotificationIdPrefix } from './notification-id';

import type { ImModelUser, ImModelChat, ImModelMessage } from 'im.v2.model';

type MessageContext = {
	message: ImModelMessage,
	chat: ImModelChat,
	user: ImModelUser,
	isLines: boolean,
};

type MessageNotificationOptions = {
	id: string,
	title: string,
	icon: string,
	text: string,
};

type PrefixMapItem = {
	condition: () => boolean,
	prefix: $Values<typeof NotificationIdPrefix>,
};

export class MessageOptionsBuilder
{
	#message: ImModelMessage;
	#chat: ImModelChat;
	#user: ?ImModelUser;
	#isLines: boolean;

	constructor(context: MessageContext)
	{
		const { message, chat, user, isLines } = context;

		this.#message = message;
		this.#chat = chat;
		this.#user = user;
		this.#isLines = isLines;
	}

	build(): MessageNotificationOptions
	{
		return {
			id: this.#getId(),
			title: this.#chat.name,
			icon: this.#getAvatarUrl(),
			text: this.#getText(),
		};
	}

	#getId(): string
	{
		const prefixMap: PrefixMapItem[] = [
			{
				condition: () => this.#isLines,
				prefix: NotificationIdPrefix.lines,
			},
			{
				condition: () => this.#chat.type === ChatType.taskComments,
				prefix: NotificationIdPrefix.taskComments,
			},
		];

		let prefix = NotificationIdPrefix.chat;
		const foundItem = prefixMap.find((record: PrefixMapItem) => record.condition() === true);
		if (foundItem)
		{
			prefix = foundItem.prefix;
		}

		return NotificationId.build({
			prefix,
			dialogId: this.#chat.dialogId,
			messageId: this.#message.id,
		});
	}

	#getAvatarUrl(): string
	{
		return this.#chat.avatar || this.#user?.avatar;
	}

	#getText(): string
	{
		let text = '';
		if (this.#chat.type !== ChatType.user && this.#user)
		{
			text += `${this.#user.name}: `;
		}

		text += Parser.purifyMessage(this.#message);

		return text;
	}
}
