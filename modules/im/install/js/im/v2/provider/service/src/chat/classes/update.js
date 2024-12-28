import { Type } from 'main.core';
import { isResizableImage, resizeImage } from 'ui.uploader.core';

import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { Utils } from 'im.v2.lib.utils';
import { Core } from 'im.v2.application.core';
import { getChatRoleForUser } from 'im.v2.lib.role-manager';

import type { Store } from 'ui.vue3.vuex';
import type {
	RestUpdateChatConfig,
	UpdateChatConfig,
	UpdateCollabConfig,
	GetMemberEntitiesConfig,
} from '../types/chat';

export class UpdateService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	async prepareAvatar(avatarFile: File): Promise<File>
	{
		if (!isResizableImage(avatarFile))
		{
			// eslint-disable-next-line no-console
			return Promise.reject(new Error('UpdateService: prepareAvatar: incorrect image'));
		}

		const MAX_AVATAR_SIZE = 180;
		const { preview: resizedAvatar } = await resizeImage(avatarFile, {
			width: MAX_AVATAR_SIZE,
			height: MAX_AVATAR_SIZE,
		});

		return resizedAvatar;
	}

	async changeAvatar(chatId: number, avatarFile: File): Promise
	{
		Logger.warn('ChatService: changeAvatar', chatId, avatarFile);
		const avatarInBase64 = await Utils.file.getBase64(avatarFile);

		return runAction(RestMethod.imV2ChatUpdateAvatar, {
			data: {
				id: chatId,
				avatar: avatarInBase64,
			},
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: changeAvatar error:', error);
			throw new Error(error);
		});
	}

	async updateChat(chatId: number, chatConfig: UpdateChatConfig): Promise<boolean>
	{
		Logger.warn(`ChatService: updateChat, chatId: ${chatId}`, chatConfig);

		const preparedFields = await this.#prepareFields(chatConfig);

		const updateResult: RestResult = await runAction(RestMethod.imV2ChatUpdate, {
			data: {
				id: chatId,
				fields: preparedFields,
			},
			id: chatId,
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: updateChat error:', error);
			throw new Error(error);
		});

		Logger.warn('ChatService: updateChat result', updateResult);

		const dialogId = `chat${chatId}`;
		await this.#updateChatInModel(dialogId, chatConfig);

		return updateResult;
	}

	async updateCollab(dialogId: string, collabConfig: UpdateCollabConfig): Promise<boolean>
	{
		Logger.warn(`ChatService: updateCollab, dialogId: ${dialogId}`, collabConfig);

		const preparedFields = await this.#prepareFields(collabConfig);

		let payload = {
			dialogId,
			name: preparedFields.title,
			description: preparedFields.description,
			avatarId: preparedFields.avatar,
		};

		if (collabConfig.groupSettings)
		{
			const groupSettings = collabConfig.groupSettings;
			payload = {
				...payload,
				ownerId: groupSettings.ownerId,
				addModeratorMembers: Utils.user.prepareSelectorIds(groupSettings.addModeratorMembers),
				deleteModeratorMembers: Utils.user.prepareSelectorIds(groupSettings.deleteModeratorMembers),
				permissions: groupSettings.permissions,
				options: groupSettings.options,
			};
		}

		const updateResult: RestResult = await runAction(RestMethod.socialnetworkCollabUpdate, {
			data: payload,
		}).catch(([error]) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: updateCollab error:', error);
			throw error;
		});

		Logger.warn('ChatService: updateCollab result', updateResult);

		return updateResult;
	}

	async getMemberEntities(chatId: number): Promise<GetMemberEntitiesConfig>
	{
		return runAction(RestMethod.imV2ChatMemberEntitiesList, {
			data: { chatId },
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: getMemberEntities error:', error);
			throw new Error(error);
		});
	}

	async #prepareFields(chatConfig: UpdateChatConfig): RestUpdateChatConfig
	{
		const result = {
			title: chatConfig.title,
			description: chatConfig.description,
			ownerId: chatConfig.ownerId,
			searchable: chatConfig.isAvailableInSearch ? 'Y' : 'N',
			manageUi: chatConfig.manageUi,
			manageUsersAdd: chatConfig.manageUsersAdd,
			manageUsersDelete: chatConfig.manageUsersDelete,
			manageMessages: chatConfig.manageMessages,
			addedMemberEntities: chatConfig.addedMemberEntities,
			deletedMemberEntities: chatConfig.deletedMemberEntities,
			addedManagers: chatConfig.addedManagers,
			deletedManagers: chatConfig.deletedManagers,
		};

		if (chatConfig.avatar)
		{
			result.avatar = await Utils.file.getBase64(chatConfig.avatar);
		}

		Object.entries(result).forEach(([key, value]) => {
			if (Type.isUndefined(value))
			{
				delete result[key];
			}
		});

		return result;
	}

	#updateChatInModel(dialogId: string, chatConfig: UpdateChatConfig): Promise
	{
		return this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				name: chatConfig.title,
				description: chatConfig.description,
				ownerId: chatConfig.ownerId,
				managerList: chatConfig.managers,
				type: chatConfig.type,
				role: getChatRoleForUser(chatConfig),
				permissions: {
					manageUi: chatConfig.manageUi,
					manageUsersAdd: chatConfig.manageUsersAdd,
					manageUsersDelete: chatConfig.manageUsersDelete,
					manageMessages: chatConfig.manageMessages,
				},
			},
		});
	}
}
