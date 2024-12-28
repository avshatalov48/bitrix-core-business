import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { EventEmitter } from 'main.core.events';

import { EventType, GetParameter, Layout } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { LayoutManager } from 'im.v2.lib.layout';
import { Logger } from 'im.v2.lib.logger';
import { PhoneManager } from 'im.v2.lib.phone';
import { Utils } from 'im.v2.lib.utils';
import { MessengerSlider } from 'im.v2.lib.slider';
import { LinesService } from 'im.v2.provider.service';

import type { ForwardedEntityConfig } from 'im.v2.provider.service';
import type { CreatableChatType } from 'im.v2.component.content.chat-forms.forms';

export const Opener = {
	async openChat(dialogId: string | number = '', messageId: number = 0): Promise
	{
		const preparedDialogId = dialogId.toString();
		if (Utils.dialog.isLinesExternalId(preparedDialogId))
		{
			return this.openLines(preparedDialogId);
		}

		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.chat.name,
			entityId: preparedDialogId,
		};
		if (messageId > 0)
		{
			layoutParams.contextId = messageId;
		}
		await LayoutManager.getInstance().setLayout(layoutParams);
		EventEmitter.emit(EventType.layout.onOpenChat, { dialogId: preparedDialogId });

		return Promise.resolve();
	},

	async forwardEntityToChat(dialogId: string, entityConfig: ForwardedEntityConfig): Promise
	{
		const preparedDialogId = dialogId.toString();
		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.chat.name,
			entityId: preparedDialogId,
		};
		await LayoutManager.getInstance().setLayout(layoutParams);
		EventEmitter.emit(EventType.layout.onOpenChat, { dialogId: preparedDialogId });
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

		await MessengerSlider.getInstance().openSlider();

		const optionOpenLinesV2Activated = FeatureManager.isFeatureAvailable(Feature.openLinesV2);

		return LayoutManager.getInstance().setLayout({
			name: optionOpenLinesV2Activated ? Layout.openlinesV2.name : Layout.openlines.name,
			entityId: preparedDialogId,
		});
	},

	async openCopilot(dialogId: string = '', contextId = 0): Promise
	{
		const preparedDialogId = dialogId.toString();

		await MessengerSlider.getInstance().openSlider();

		return LayoutManager.getInstance().setLayout({
			name: Layout.copilot.name,
			entityId: preparedDialogId,
			contextId,
		});
	},

	async openCollab(dialogId: string = ''): Promise
	{
		const preparedDialogId = dialogId.toString();

		await MessengerSlider.getInstance().openSlider();

		return LayoutManager.getInstance().setLayout({
			name: Layout.collab.name,
			entityId: preparedDialogId,
		});
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
			name: Layout.notification.name,
		});

		EventEmitter.emit(EventType.layout.onOpenNotifications);

		return Promise.resolve();
	},

	async openRecentSearch(): Promise
	{
		await MessengerSlider.getInstance().openSlider();
		await LayoutManager.getInstance().setLayout({
			name: Layout.chat.name,
		});

		EventEmitter.emit(EventType.recent.openSearch);

		return Promise.resolve();
	},

	async openSettings(sectionName: string): Promise
	{
		Logger.warn('Slider: openSettings', sectionName);
		await MessengerSlider.getInstance().openSlider();

		await LayoutManager.getInstance().setLayout({
			name: Layout.settings.name,
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

	async openChatCreation(chatType: CreatableChatType): Promise
	{
		Logger.warn('Slider: openChatCreation', chatType);

		await MessengerSlider.getInstance().openSlider();
		const layoutParams = {
			name: Layout.createChat.name,
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
};

const checkHistoryDialogId = (dialogId: string): boolean => {
	return (
		Utils.dialog.isLinesHistoryId(dialogId)
		|| Utils.dialog.isLinesExternalId(dialogId)
	);
};

const prepareHistorySliderLink = (dialogId: string): string => {
	const getParams = new URLSearchParams({
		[GetParameter.openHistory]: dialogId,
		[GetParameter.backgroundType]: 'light',
	});

	return `/desktop_app/history.php?${getParams.toString()}`;
};
