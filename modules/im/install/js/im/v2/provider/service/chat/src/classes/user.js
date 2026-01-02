import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod, UserRole } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { Utils } from 'im.v2.lib.utils';
import { Notifier } from 'im.v2.lib.notifier';
import { LayoutManager } from 'im.v2.lib.layout';

import type { ImModelChat } from 'im.v2.model';

export class UserService
{
	#store: Store;
	#restClient: RestClient;

	constructor()
	{
		this.#store = Core.getStore();
		this.#restClient = Core.getRestClient();
	}

	async leaveChat(dialogId: string): void
	{
		const queryParams = { dialogId, userId: Core.getUserId() };
		try
		{
			await this.#restClient.callMethod(RestMethod.imV2ChatDeleteUser, queryParams);
			this.#onChatLeave(dialogId);
		}
		catch (result)
		{
			console.error('UserService: leave chat error', result.error());
			Notifier.chat.handleLeaveError(result.error());
		}
	}

	async leaveCollab(dialogId: string): void
	{
		const payload = {
			data: { dialogId },
		};

		try
		{
			await runAction(RestMethod.socialnetworkMemberLeave, payload);
			this.#onChatLeave(dialogId);
		}
		catch (errors)
		{
			console.error('UserService: leave collab error', errors[0]);
			Notifier.collab.onLeaveError();
		}
	}

	async kickUserFromChat(dialogId: string, userId: number): void
	{
		const queryParams = { dialogId, userId };
		await this.#restClient.callMethod(RestMethod.imV2ChatDeleteUser, queryParams)
			.catch((result: RestResult) => {
				console.error('UserService: error kicking from chat', result.error());
				Notifier.chat.handleUserKickError(result.error());
			});
	}

	async kickUserFromCollab(dialogId: string, userId: number): void
	{
		const members = Utils.user.prepareSelectorIds(userId);

		const payload = {
			data: { dialogId, members },
		};

		await runAction(RestMethod.socialnetworkMemberDelete, payload)
			.catch(([error]) => {
				console.error('UserService: error kicking from collab', error);
				Notifier.collab.onKickUserError();
			});
	}

	addToChat(addConfig: {chatId: number, members: string[], showHistory: boolean}): Promise
	{
		const queryParams = {
			chat_id: addConfig.chatId,
			users: addConfig.members,
			hide_history: !addConfig.showHistory,
		};

		return this.#restClient.callMethod(RestMethod.imChatUserAdd, queryParams)
			.catch((result: RestResult) => {
				console.error('UserService: error adding to chat', result.error());
				throw result.error();
			});
	}

	joinChat(dialogId: string): void
	{
		Logger.warn(`UserService: join chat ${dialogId}`);
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: { role: UserRole.member },
		});

		this.#restClient.callMethod(RestMethod.imV2ChatJoin, {
			dialogId,
		}).catch((result: RestResult) => {
			console.error('UserService: error joining chat', result.error());
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
		void this.#store.dispatch('chats/update', {
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
			.catch(([error]) => {
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
		void this.#store.dispatch('chats/update', {
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
			.catch(([error]) => {
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
			LayoutManager.getInstance().clearCurrentLayoutEntityId();
			void LayoutManager.getInstance().deleteLastOpenedElementById(dialogId);
		}
	}
}
