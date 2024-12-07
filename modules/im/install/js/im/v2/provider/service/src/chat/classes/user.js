import 'ui.notification';
import { Loc } from 'main.core';
import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { ChatType, RestMethod, UserRole } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelChat } from 'im.v2.model';

type RestError = {
	answer: {
		error: string,
		error_description: string,
	},
	status: number,
};

const DeleteUserErrorCode = {
	userInvitedFromStructure: 'USER_INVITED_FROM_STRUCTURE',
	userNotFound: 'USER_NOT_FOUND',
};

export class UserService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	addToChat(addConfig: {chatId: number, members: string[], showHistory: boolean}): Promise
	{
		const queryParams = {
			chat_id: addConfig.chatId,
			users: addConfig.members,
			hide_history: !addConfig.showHistory,
		};

		return this.#restClient.callMethod(RestMethod.imChatUserAdd, queryParams);
	}

	async kickUserFromChat(dialogId: string, userId: number)
	{
		const queryParams = { dialogId, userId };
		try
		{
			await this.#restClient.callMethod(RestMethod.imV2ChatDeleteUser, queryParams);
		}
		catch (error)
		{
			this.#onChatKickError(error);
		}
	}

	async leaveChat(dialogId: string)
	{
		const queryParams = { dialogId, userId: Core.getUserId() };
		try
		{
			await this.#restClient.callMethod(RestMethod.imV2ChatDeleteUser, queryParams);
			this.#onChatLeave(dialogId);
		}
		catch (error)
		{
			this.#onChatLeaveError(error);
		}
	}

	joinChat(dialogId: string)
	{
		Logger.warn(`UserService: join chat ${dialogId}`);
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				role: UserRole.member,
			},
		});

		this.#restClient.callMethod(RestMethod.imV2ChatJoin, {
			dialogId,
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('UserService: error joining chat', error);
		});
	}

	addManager(dialogId: string, userId: number)
	{
		Logger.warn(`UserService: add manager ${userId} to ${dialogId}`);
		const { managerList }: ImModelChat = this.#store.getters['chats/get'](dialogId);
		if (managerList.includes(userId))
		{
			return;
		}
		const newManagerList = [...managerList, userId];
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: { managerList: newManagerList },
		});

		const payload = {
			data: {
				dialogId,
				userIds: [userId],
			},
		};

		runAction(RestMethod.imV2ChatAddManagers, payload)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('UserService: add manager error', error);
			});
	}

	removeManager(dialogId: string, userId: number): void
	{
		Logger.warn(`UserService: remove manager ${userId} from ${dialogId}`);
		const { managerList }: ImModelChat = this.#store.getters['chats/get'](dialogId);
		if (!managerList.includes(userId))
		{
			return;
		}
		const newManagerList = managerList.filter((managerId) => managerId !== userId);
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: { managerList: newManagerList },
		});

		const payload = {
			data: {
				dialogId,
				userIds: [userId],
			},
		};

		runAction(RestMethod.imV2ChatDeleteManagers, payload)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('UserService: remove manager error', error);
			});
	}

	#onChatLeave(dialogId: string): void
	{
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: { inited: false },
		});
		void this.#store.dispatch('recent/delete', { id: dialogId });

		const chatIsOpened = this.#store.getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			void Messenger.openChat();
		}
	}

	#onChatKickError(error: RestError)
	{
		// eslint-disable-next-line no-console
		console.error('UserService: error kicking from chat', error);

		const NotificationTextByErrorCode = {
			[DeleteUserErrorCode.userInvitedFromStructure]: Loc.getMessage('IM_MESSAGE_SERVICE_KICK_CHAT_STRUCTURE_ERROR_MSGVER_1'),
			default: Loc.getMessage('IM_MESSAGE_SERVICE_KICK_CHAT_DEFAULT_ERROR'),
		};

		const errorCode = this.#getErrorCode(error);
		const notificationText = NotificationTextByErrorCode[errorCode] ?? NotificationTextByErrorCode.default;
		this.#showNotification(notificationText);
	}

	#onChatLeaveError(error: RestError)
	{
		// eslint-disable-next-line no-console
		console.error('UserService: error leaving chat', error);

		const NotificationTextByErrorCode = {
			[DeleteUserErrorCode.userInvitedFromStructure]: Loc.getMessage('IM_MESSAGE_SERVICE_LEAVE_CHAT_STRUCTURE_ERROR'),
			default: Loc.getMessage('IM_MESSAGE_SERVICE_LEAVE_CHAT_DEFAULT_ERROR'),
		};

		const errorCode = this.#getErrorCode(error);
		const notificationText = NotificationTextByErrorCode[errorCode] ?? NotificationTextByErrorCode.default;
		this.#showNotification(notificationText);
	}

	#showNotification(text: string): void
	{
		BX.UI.Notification.Center.notify({
			content: text,
			autoHideDelay: 5000,
		});
	}

	#getErrorCode(error: RestError): string
	{
		const { answer: { error: errorCode } } = error;

		return errorCode;
	}
}
