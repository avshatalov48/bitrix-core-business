import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Loc, Type } from 'main.core';
import { Store } from 'ui.vue3.vuex';

import { AccessErrorCode } from 'im.v2.lib.access';
import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { RestMethod, Layout } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { MessageService } from 'im.v2.provider.service';
import { UserManager } from 'im.v2.lib.user';
import { LayoutManager } from 'im.v2.lib.layout';
import { Utils } from 'im.v2.lib.utils';
import { CopilotManager } from 'im.v2.lib.copilot';
import { OpenLinesManager } from 'imopenlines.v2.lib.openlines';

import { ChatDataExtractor } from './chat-data-extractor';

import type { ImModelChat, ImModelMessage } from 'im.v2.model';
import type { ChatLoadRestResult, CommentInfoRestResult } from '../../types/rest';

type UpdateModelsResult = {
	dialogId: string,
	chatId: number,
};

type RequestChatError = {
	code: $Values<typeof AccessErrorCode> | number,
	customData: Array | null,
	message: string,
};

export class LoadService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	loadChat(dialogId: string): Promise
	{
		const params = { dialogId };

		return this.#requestChat(RestMethod.imV2ChatShallowLoad, params);
	}

	loadChatWithMessages(dialogId: string): Promise
	{
		const params = {
			dialogId,
			messageLimit: MessageService.getMessageRequestLimit(),
		};

		return this.#requestChat(RestMethod.imV2ChatLoad, params);
	}

	loadChatWithContext(dialogId: string, messageId: number): Promise
	{
		const params = {
			dialogId,
			messageId,
			messageLimit: MessageService.getMessageRequestLimit(),
		};

		return this.#requestChat(RestMethod.imV2ChatLoadInContext, params);
	}

	prepareDialogId(dialogId: string): Promise<string>
	{
		if (!Utils.dialog.isExternalId(dialogId))
		{
			return Promise.resolve(dialogId);
		}

		return runAction(RestMethod.imV2ChatGetDialogId, {
			data: { externalId: dialogId },
		})
			.then((result: {dialogId: string}) => {
				return result.dialogId;
			})
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('ChatService: Load: error preparing external id', error);
			});
	}

	async loadComments(postId: number): Promise
	{
		const params = {
			postId,
			messageLimit: MessageService.getMessageRequestLimit(),
			autoJoin: true,
			createIfNotExists: true,
		};

		const { chatId } = await this.#requestChat(RestMethod.imV2ChatLoad, params);

		return this.#store.dispatch('messages/comments/set', {
			messageId: postId,
			chatId,
		});
	}

	async loadCommentInfo(channelDialogId: string): Promise
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](channelDialogId, true);
		const messages = this.#store.getters['messages/getByChatId'](dialog.chatId);
		const messageIds = messages.map((message: ImModelMessage) => message.id);
		const { commentInfo, usersShort }: CommentInfoRestResult = await runAction(
			RestMethod.imV2ChatMessageCommentInfoList,
			{ data: { messageIds } },
		)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('ChatService: Load: error loading comment info', error);
			});

		const userManager = new UserManager();

		void this.#store.dispatch('messages/comments/set', commentInfo);
		void userManager.addUsersToModel(usersShort);
	}

	resetChat(dialogId: string): Promise
	{
		const dialog: ImModelChat = this.#store.getters['chats/get'](dialogId, true);
		this.#store.dispatch('messages/clearChatCollection', { chatId: dialog.chatId });
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: { inited: false },
		});
	}

	async #requestChat(actionName: string, params: Object<string, any>): Promise<{ dialogId: string, chatId: number }>
	{
		const { dialogId, messageId } = params;
		this.#markDialogAsLoading(dialogId);

		const actionResult = await runAction(actionName, { data: params })
			.catch((errors: RequestChatError[]) => {
				// eslint-disable-next-line no-console
				console.error('ChatService: Load: error loading chat', errors);
				this.#handleChatLoadError(errors);
				this.#markDialogAsNotLoaded(dialogId);
				throw errors;
			});

		if (this.#needLayoutRedirect(actionResult))
		{
			return this.#redirectToLayout(actionResult, messageId);
		}

		const {
			dialogId: loadedDialogId,
			chatId,
		} = await this.#updateModels(actionResult);

		if (this.#isDialogLoadedMarkNeeded(actionName))
		{
			await this.#markDialogAsLoaded(loadedDialogId);
		}

		return { dialogId: loadedDialogId, chatId };
	}

	#markDialogAsLoading(dialogId: string)
	{
		void this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				loading: true,
			},
		});
	}

	#markDialogAsLoaded(dialogId: string): Promise
	{
		return this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				inited: true,
				loading: false,
			},
		});
	}

	#markDialogAsNotLoaded(dialogId: string): Promise
	{
		return this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				loading: false,
			},
		});
	}

	#isDialogLoadedMarkNeeded(actionName: string): boolean
	{
		return actionName !== RestMethod.imV2ChatShallowLoad;
	}

	async #updateModels(restResult: ChatLoadRestResult): Promise<UpdateModelsResult>
	{
		const extractor = new ChatDataExtractor(restResult);

		const chatsPromise = this.#store.dispatch('chats/set', extractor.getChats());
		const filesPromise = this.#store.dispatch('files/set', extractor.getFiles());

		const userManager = new UserManager();
		const usersPromise = Promise.all([
			this.#store.dispatch('users/set', extractor.getUsers()),
			userManager.addUsersToModel(extractor.getAdditionalUsers()),
		]);
		const messagesPromise = Promise.all([
			this.#store.dispatch('messages/setChatCollection', {
				messages: extractor.getMessages(),
				clearCollection: true,
			}),
			this.#store.dispatch('messages/store', extractor.getMessagesToStore()),
			this.#store.dispatch('messages/pin/setPinned', {
				chatId: extractor.getChatId(),
				pinnedMessages: extractor.getPinnedMessageIds(),
			}),
			this.#store.dispatch('messages/reactions/set', extractor.getReactions()),
			this.#store.dispatch('messages/comments/set', extractor.getCommentInfo()),
		]);

		const copilotManager = new CopilotManager();
		const copilotPromise = copilotManager.handleChatLoadResponse(extractor.getCopilot());

		const openLinesPromise = OpenLinesManager.handleChatLoadResponse(extractor.getSession());

		const collabPromise = this.#store.dispatch('chats/collabs/set', {
			chatId: extractor.getChatId(),
			collabInfo: extractor.getCollabInfo(),
		});

		await Promise.all([
			chatsPromise,
			filesPromise,
			usersPromise,
			messagesPromise,
			copilotPromise,
			openLinesPromise,
			collabPromise,
		]);

		return { dialogId: extractor.getDialogId(), chatId: extractor.getChatId() };
	}

	#needLayoutRedirect(actionResult: ChatLoadRestResult): boolean
	{
		return this.#needRedirectToCopilotLayout(actionResult) || this.#needRedirectToOpenLinesLayout(actionResult);
	}

	#redirectToLayout(actionResult: ChatLoadRestResult, contextId: number = 0): Promise
	{
		const extractor = new ChatDataExtractor(actionResult);
		LayoutManager.getInstance().setLastOpenedElement(Layout.chat.name, '');

		if (this.#needRedirectToCopilotLayout(actionResult))
		{
			return Messenger.openCopilot(extractor.getDialogId(), contextId);
		}

		if (this.#needRedirectToOpenLinesLayout(actionResult))
		{
			return Messenger.openLines(extractor.getDialogId());
		}

		return Promise.resolve();
	}

	#needRedirectToCopilotLayout(actionResult: ChatLoadRestResult): boolean
	{
		const extractor = new ChatDataExtractor(actionResult);
		const currentLayoutName = LayoutManager.getInstance().getLayout().name;

		return extractor.isCopilotChat() && currentLayoutName !== Layout.copilot.name;
	}

	#needRedirectToOpenLinesLayout(actionResult: ChatLoadRestResult): boolean
	{
		const optionOpenLinesV2Activated = FeatureManager.isFeatureAvailable(Feature.openLinesV2);

		if (optionOpenLinesV2Activated)
		{
			return false;
		}

		const extractor = new ChatDataExtractor(actionResult);

		return extractor.isOpenlinesChat() && Type.isStringFilled(extractor.getDialogId());
	}

	#handleChatLoadError(errors: RequestChatError[]): void
	{
		const [firstError] = errors;
		if (firstError.code === AccessErrorCode.chatNotFound)
		{
			this.#showNotification(Loc.getMessage('IM_CONTENT_CHAT_ACCESS_ERROR_MSGVER_1'));
		}
	}

	#showNotification(text: string)
	{
		BX.UI.Notification.Center.notify({ content: text });
	}
}
