import { ChatType, MessageComponent } from 'im.v2.const';
import { Core } from 'im.v2.application.core';

import type { JsonObject } from 'main.core';
import type { Store } from 'ui.vue3.vuex';
import type { ImModelMessage, ImModelUser } from 'im.v2.model';

export class CopilotManager
{
	store: Store;

	constructor()
	{
		this.store = Core.getStore();
	}

	async handleRecentListResponse(copilotData: JsonObject): Promise
	{
		if (!copilotData)
		{
			return Promise.resolve();
		}

		const { recommendedRoles, roles, chats, messages } = copilotData;
		if (!roles)
		{
			return Promise.resolve();
		}

		return Promise.all([
			this.store.dispatch('copilot/chats/add', chats),
			this.store.dispatch('copilot/roles/add', roles),
			this.store.dispatch('copilot/setRecommendedRoles', recommendedRoles),
			this.store.dispatch('copilot/messages/add', messages),
		]);
	}

	async handleChatLoadResponse(copilotData: JsonObject): Promise
	{
		if (!copilotData)
		{
			return Promise.resolve();
		}

		const { aiProvider, chats, roles, messages } = copilotData;
		if (!roles)
		{
			return Promise.resolve();
		}

		return Promise.all([
			this.store.dispatch('copilot/setProvider', aiProvider),
			this.store.dispatch('copilot/roles/add', roles),
			this.store.dispatch('copilot/chats/add', chats),
			this.store.dispatch('copilot/messages/add', messages),
		]);
	}

	async handleRoleUpdate(copilotData: JsonObject): Promise
	{
		const { chats, roles } = copilotData;
		if (!roles)
		{
			return Promise.resolve();
		}

		return Promise.all([
			this.store.dispatch('copilot/roles/add', roles),
			this.store.dispatch('copilot/chats/add', chats),
		]);
	}

	async handleMessageAdd(copilotData): Promise
	{
		const { chats, roles, messages } = copilotData;
		if (!roles)
		{
			return Promise.resolve();
		}

		return Promise.all([
			this.store.dispatch('copilot/roles/add', roles),
			this.store.dispatch('copilot/chats/add', chats),
			this.store.dispatch('copilot/messages/add', messages),
		]);
	}

	getRoleAvatarUrl({ avatarDialogId, contextDialogId }: { avatarDialogId: string, contextDialogId: string }): ?string
	{
		if (!this.isCopilotChatOrBot(avatarDialogId))
		{
			return '';
		}

		return this.store.getters['copilot/chats/getRoleAvatar'](contextDialogId);
	}

	isCopilotBot(userId: string | number): boolean
	{
		return this.store.getters['users/bots/isCopilot'](userId);
	}

	isCopilotChat(dialogId: string): boolean
	{
		return this.store.getters['chats/get'](dialogId)?.type === ChatType.copilot;
	}

	isCopilotChatOrBot(dialogId: string): boolean
	{
		return this.isCopilotChat(dialogId) || this.isCopilotBot(dialogId);
	}

	getMessageRoleAvatar(messageId: number): ?string
	{
		return this.store.getters['copilot/messages/getRole'](messageId)?.avatar?.medium;
	}

	getNameWithRole({ dialogId, messageId }): string
	{
		const user: ImModelUser = this.store.getters['users/get'](dialogId);
		const roleName = this.store.getters['copilot/messages/getRole'](messageId).name;

		return `${user.name} (${roleName})`;
	}

	isCopilotMessage(messageId: number): boolean
	{
		const message: ImModelMessage = this.store.getters['messages/getById'](messageId);
		if (!message)
		{
			return false;
		}

		if (this.isCopilotBot(message.authorId))
		{
			return true;
		}

		return message.componentId === MessageComponent.copilotCreation;
	}
}
