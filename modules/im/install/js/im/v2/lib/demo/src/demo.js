import { Core } from 'im.v2.application.core';
import { ChatType, UserRole } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Utils } from 'im.v2.lib.utils';

import { IdGenerator } from './classes/id-generator';

import type { ImModelChat, ImModelMessage, ImModelUser, ImModelFile, ImModelTranscription } from 'im.v2.model';

export class DemoChatBuilder
{
	#chat: Partial<ImModelChat> = {};
	#messages: Partial<ImModelMessage>[] = [];
	#users: Partial<ImModelUser>[] = [];
	#files: Partial<ImModelFile>[] = [];

	static isDemoDialogId(dialogId: string): boolean
	{
		return Utils.text.isUuidV4(dialogId);
	}

	addChat(fields: Partial<ImModelChat>): Partial<ImModelChat>
	{
		this.#chat = {
			...this.#getDefaultChatFields(),
			...fields,
			dialogId: IdGenerator.getDialogId(),
			chatId: IdGenerator.getNextChatId(),
		};

		return this.#chat;
	}

	addMessage(fields: Partial<ImModelMessage>): Partial<ImModelMessage>
	{
		const newMessage = {
			id: fields.id ?? IdGenerator.getNextMessageId(),
			chatId: this.#chat.chatId ?? 0,
			...fields,
		};
		this.#messages.push(newMessage);

		return newMessage;
	}

	getNextMessageId(): number
	{
		return IdGenerator.getNextMessageId();
	}

	addUser(fields: Partial<ImModelUser>): Partial<ImModelUser>
	{
		const newUser = {
			id: IdGenerator.getNextUserId(),
			...fields,
		};
		this.#users.push(newUser);

		return newUser;
	}

	addFile(fields: Partial<ImModelFile>): Partial<ImModelUser>
	{
		const newFile = {
			id: IdGenerator.getNextFileId(),
			chatId: this.#chat.chatId ?? 0,
			...fields,
		};
		this.#files.push(newFile);

		return newFile;
	}

	addFileTranscription(transcription: ImModelTranscription)
	{
		Core.getStore().dispatch('files/setTranscription', transcription);
	}

	save(): void
	{
		if (this.#messages.length > 0)
		{
			const [newestMessage] = this.#messages.slice(-1);
			this.#chat.lastMessageId = newestMessage.id;
		}

		void Core.getStore().dispatch('chats/set', this.#chat);

		const userManager = new UserManager();
		void userManager.addUsersToModel(this.#users);

		void Core.getStore().dispatch('files/set', this.#files);
		void Core.getStore().dispatch('messages/setChatCollection', { messages: this.#messages });
	}

	getChat(): Partial<ImModelChat>
	{
		return this.#chat;
	}

	getUsers(): Partial<ImModelUser>[]
	{
		return this.#users;
	}

	getMessages(): Partial<ImModelMessage>[]
	{
		return this.#messages;
	}

	getFiles(): Partial<ImModelFile>[]
	{
		return this.#files;
	}

	#getDefaultChatFields(): Partial<ImModelChat>
	{
		return {
			type: ChatType.chat,
			inited: true,
			role: UserRole.member,
			permissions: {
				manageUi: UserRole.member,
				manageSettings: UserRole.member,
				manageUsersAdd: UserRole.member,
				manageUsersDelete: UserRole.member,
				manageMessages: UserRole.member,
			},
		};
	}
}
