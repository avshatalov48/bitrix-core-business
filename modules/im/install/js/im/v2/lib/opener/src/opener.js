import { EventEmitter } from 'main.core.events';

import { EventType, GetParameter, Layout, NavigationMenuItem } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { LayoutManager } from 'im.v2.lib.layout';
import { Logger } from 'im.v2.lib.logger';
import { PhoneManager } from 'im.v2.lib.phone';
import { Utils } from 'im.v2.lib.utils';
import { MessengerSlider } from 'im.v2.lib.slider';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { BotContextService } from 'im.v2.provider.service.bot';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { type NavigationMenuItemParams, NavigationManager } from 'im.v2.lib.navigation';

import { LinesService } from './classes/lines-service';
import {
	checkHistoryDialogId,
	prepareHistorySliderLink,
	normalizeEntityId,
	isEmbeddedModeWithActiveSlider,
	openChatInNewTab,
} from './functions/helpers';

import type { JsonObject } from 'main.core';
import type { ForwardedEntityConfig } from 'im.v2.provider.service.sending';
import type { CreatableChatType, OpenChatCreationParams } from 'im.v2.component.content.chat-forms.forms';

export const Opener = {
	async openChat(dialogId: string | number = '', messageId: number = 0): Promise
	{
		const preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			return this.openLines(preparedDialogId);
		}

		if (isEmbeddedModeWithActiveSlider())
		{
			openChatInNewTab({
				navigationItem: NavigationMenuItem.chat,
				dialogId: preparedDialogId,
				messageId,
			});

			return Promise.resolve();
		}

		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.chat,
			entityId: preparedDialogId,
		};
		if (messageId > 0)
		{
			layoutParams.contextId = messageId;
		}
		await LayoutManager.getInstance().setLayout(layoutParams);

		return Promise.resolve();
	},

	async openChatWithBotContext(dialogId: string | number, context: JsonObject): Promise
	{
		const preparedDialogId = dialogId.toString();

		const botContextService = new BotContextService();
		botContextService.scheduleContextRequest(preparedDialogId, context);

		return this.openChat(preparedDialogId);
	},

	async forwardEntityToChat(dialogId: string, entityConfig: ForwardedEntityConfig): Promise
	{
		const preparedDialogId = dialogId.toString();
		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.chat,
			entityId: preparedDialogId,
		};
		await LayoutManager.getInstance().setLayout(layoutParams);
		EventEmitter.emit(EventType.textarea.forwardEntity, { dialogId, entityConfig });

		return Promise.resolve();
	},

	async openLines(dialogId: string = ''): Promise
	{
		let preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			const linesService = new LinesService();
			preparedDialogId = await linesService.getDialogIdByUserCode(preparedDialogId);
		}

		if (isEmbeddedModeWithActiveSlider())
		{
			openChatInNewTab({
				navigationItem: NavigationMenuItem.openlines,
				dialogId: preparedDialogId,
			});

			return Promise.resolve();
		}

		await MessengerSlider.getInstance().openSlider();

		const optionOpenLinesV2Activated = FeatureManager.isFeatureAvailable(Feature.openLinesV2);

		return LayoutManager.getInstance().setLayout({
			name: optionOpenLinesV2Activated ? Layout.openlinesV2 : Layout.openlines,
			entityId: preparedDialogId,
		});
	},

	async openCopilot(dialogId: string = '', contextId = 0): Promise
	{
		const preparedDialogId = dialogId.toString();

		await MessengerSlider.getInstance().openSlider();

		return LayoutManager.getInstance().setLayout({
			name: Layout.copilot,
			entityId: preparedDialogId,
			contextId,
		});
	},

	async openCollab(dialogId: string = ''): Promise
	{
		const preparedDialogId = dialogId.toString();

		if (!FeatureManager.collab.isAvailable())
		{
			FeatureManager.collab.openFeatureSlider();

			return null;
		}

		await MessengerSlider.getInstance().openSlider();

		return LayoutManager.getInstance().setLayout({
			name: Layout.collab,
			entityId: preparedDialogId,
		});
	},

	async openChannel(dialogId: string = ''): Promise
	{
		const preparedDialogId = dialogId.toString();

		await MessengerSlider.getInstance().openSlider();

		return LayoutManager.getInstance().setLayout({
			name: Layout.channel,
			entityId: preparedDialogId,
		});
	},

	async openTaskComments(dialogId: string = '', messageId: number = 0): Promise
	{
		const preparedDialogId = dialogId.toString();

		await MessengerSlider.getInstance().openSlider();

		const layoutParams = {
			name: Layout.taskComments,
			entityId: preparedDialogId,
		};
		if (messageId > 0)
		{
			layoutParams.contextId = messageId;
		}

		return LayoutManager.getInstance().setLayout(layoutParams);
	},

	openHistory(dialogId: string | number = ''): Promise
	{
		if (Utils.dialog.isDialogId(dialogId))
		{
			return this.openChat(dialogId);
		}

		if (!checkHistoryDialogId(dialogId))
		{
			return Promise.reject();
		}

		const sliderLink = prepareHistorySliderLink(dialogId);
		BX.SidePanel.Instance.open(sliderLink, {
			width: Utils.dialog.isLinesExternalId(dialogId) ? 700 : 1000,
			allowChangeHistory: false,
			allowChangeTitle: false,
			cacheable: false,
		});

		return Promise.resolve();
	},

	async openNotifications(): Promise
	{
		await MessengerSlider.getInstance().openSlider();
		await LayoutManager.getInstance().setLayout({
			name: Layout.notification,
		});

		EventEmitter.emit(EventType.layout.onOpenNotifications);

		return Promise.resolve();
	},

	async openRecentSearch(): Promise
	{
		await MessengerSlider.getInstance().openSlider();
		await LayoutManager.getInstance().setLayout({
			name: Layout.chat,
		});

		EventEmitter.emit(EventType.recent.openSearch);

		return Promise.resolve();
	},

	async openSettings(sectionName: string): Promise
	{
		Logger.warn('Slider: openSettings', sectionName);
		await MessengerSlider.getInstance().openSlider();

		await LayoutManager.getInstance().setLayout({
			name: Layout.settings,
			entityId: sectionName,
		});

		return Promise.resolve();
	},

	openConference(code: string = ''): Promise
	{
		Logger.warn('Slider: openConference', code);

		if (!Utils.conference.isValidCode(code))
		{
			return Promise.reject();
		}

		const url = Utils.conference.getUrlByCode(code);
		Utils.browser.openLink(url, Utils.conference.getWindowNameByCode(code));

		return Promise.resolve();
	},

	async openChatCreation(
		chatType: CreatableChatType,
		params: OpenChatCreationParams = {},
	): Promise
	{
		Logger.warn('Slider: openChatCreation', chatType);

		CreateChatManager.getInstance().setPreselectedMembers(params.preselectedMembers ?? []);
		CreateChatManager.getInstance().setIncludeCurrentUser(params.includeCurrentUser ?? true);
		CreateChatManager.getInstance().setOwnerId(params.ownerId ?? null);

		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.createChat,
			entityId: chatType,
		};

		return LayoutManager.getInstance().setLayout(layoutParams);
	},

	startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		Logger.warn('Slider: onStartVideoCall', dialogId, withVideo);
		if (!Utils.dialog.isDialogId(dialogId))
		{
			Logger.error('Slider: onStartVideoCall - dialogId is not correct', dialogId);

			return false;
		}

		CallManager.getInstance().startCall(dialogId, withVideo);

		return Promise.resolve();
	},

	startPhoneCall(number: string, params: Object<any, string>): Promise
	{
		Logger.warn('Slider: startPhoneCall', number, params);
		void PhoneManager.getInstance().startCall(number, params);

		return Promise.resolve();
	},

	startCallList(callListId: number, params: Object<string, any>): Promise
	{
		Logger.warn('Slider: startCallList', callListId, params);
		PhoneManager.getInstance().startCallList(callListId, params);

		return Promise.resolve();
	},

	openNewTab(path)
	{
		if (DesktopApi.isChatTab() && DesktopApi.isFeatureSupported(DesktopFeature.openNewTab.id))
		{
			DesktopApi.createImTab(`${path}&${GetParameter.desktopChatTabMode}=Y`);
		}
		else
		{
			Utils.browser.openLink(path);
		}
	},

	async openNavigationItem(payload: NavigationMenuItemParams): void
	{
		await MessengerSlider.getInstance().openSlider();

		NavigationManager.open({
			id: payload.id.toString(),
			entityId: normalizeEntityId(payload.entityId),
			target: payload.target,
		});
	},

	isChatOpened(dialogId: string): boolean
	{
		const currentLayout = LayoutManager.getInstance().getLayout();
		if (!LayoutManager.getInstance().isChatLayout(currentLayout.name))
		{
			return false;
		}

		return currentLayout.entityId === dialogId;
	},
};
