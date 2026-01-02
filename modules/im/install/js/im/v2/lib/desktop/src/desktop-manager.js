import { Extension, Type } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { BitrixVue } from 'ui.vue3';

import { Core } from 'im.v2.application.core';
import { DesktopBxLink, Settings } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { DesktopUpdateBanner } from 'im.v2.component.desktop.update-banner';
import { LayoutManager } from 'im.v2.lib.layout';
import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';

import { CheckUtils } from './classes/check-utils';
import { Conference } from './classes/conference';
import { DesktopChatWindow } from './classes/chat-window';
import { DesktopBrowserWindow } from './classes/browser-window';
import { Encoder } from './classes/encoder';
import { DesktopBroadcastManager } from './classes/broadcast-manager';

import type { CreatableChatType } from 'im.v2.component.content.chat-forms.forms';

export { DesktopBroadcastManager } from './classes/broadcast-manager';

const DESKTOP_PROTOCOL_VERSION = 2;
const LOCATION_RESET_TIMEOUT = 1000;
const DESKTOP_VERSION_WITH_AIR_DESIGN_SUPPORT = 17;
const BANNER_COMPONENT_NAME = 'update-banner';

export class DesktopManager
{
	static instance: DesktopManager;

	#desktopIsActive: boolean;
	#desktopActiveVersion: number;
	#locationChangedToBx = false;
	#enableRedirectCounter = 1;

	static getInstance(): DesktopManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	static init()
	{
		DesktopManager.getInstance();
	}

	static isDesktop(): boolean
	{
		return DesktopApi.isDesktop();
	}

	static isChatWindow(): boolean
	{
		return DesktopApi.isChatWindow();
	}

	constructor()
	{
		this.#initDesktopStatus();

		if (!DesktopManager.isDesktop())
		{
			return;
		}

		if (this.#shouldShowDesktopUpdateBanner())
		{
			this.#showDesktopUpdateBanner();

			return;
		}

		if (DesktopApi.isAirDesignEnabledInDesktop())
		{
			DesktopBroadcastManager.init();
		}

		if (DesktopApi.isChatWindow())
		{
			DesktopChatWindow.init();
		}
		else
		{
			DesktopBrowserWindow.init();
		}
	}

	isDesktopActive(): boolean
	{
		if (DesktopManager.isDesktop())
		{
			return true;
		}

		return this.#desktopIsActive;
	}

	setDesktopActive(flag: boolean)
	{
		this.#desktopIsActive = flag;
	}

	setDesktopVersion(version: number)
	{
		this.#desktopActiveVersion = version;
	}

	getDesktopVersion(): number
	{
		return this.#desktopActiveVersion;
	}

	isLocationChangedToBx(): boolean
	{
		return this.#locationChangedToBx;
	}

	canReloadWindow(): boolean
	{
		return !DesktopApi.isAirDesignEnabledInDesktop() || LayoutManager.getInstance().isEmbeddedMode();
	}

	redirectToChat(dialogId: string = '', messageId: number = 0): Promise
	{
		Logger.warn('Desktop: redirectToChat', dialogId);
		let link = `bx://${DesktopBxLink.chat}/dialogId/${dialogId}`;
		if (messageId > 0)
		{
			link += `/messageId/${messageId}`;
		}
		this.openBxLink(link);

		return Promise.resolve();
	}

	redirectToChatWithBotContext(dialogId: string = '', context: Object = {}): Promise
	{
		Logger.warn('Desktop: redirectToChatWithBotContext', dialogId);
		let link = `bx://${DesktopBxLink.botContext}/dialogId/${dialogId}`;
		if (!Type.isPlainObject(context))
		{
			return Promise.reject();
		}

		const preparedContext = Encoder.encodeParamsJson(context);
		link += `/context/${preparedContext}`;

		this.openBxLink(link);

		return Promise.resolve();
	}

	redirectToLines(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToLines', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.lines}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	redirectToCopilot(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToCopilot', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.copilot}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	redirectToCollab(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToCollab', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.collab}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	redirectToChannel(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToChannel', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.channel}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	redirectToTaskComments(dialogId: string = '', messageId: number = 0): Promise
	{
		Logger.warn('Desktop: redirectToTaskComments', dialogId);
		let link = `bx://${DesktopBxLink.taskComments}/dialogId/${dialogId}`;
		if (messageId > 0)
		{
			link += `/messageId/${messageId}`;
		}
		this.openBxLink(link);

		return Promise.resolve();
	}

	redirectToNotifications(): Promise
	{
		Logger.warn('Desktop: redirectToNotifications');
		this.openBxLink(`bx://${DesktopBxLink.notifications}`);

		return Promise.resolve();
	}

	redirectToRecentSearch(): Promise
	{
		Logger.warn('Desktop: redirectToRecentSearch');
		this.openBxLink(`bx://${DesktopBxLink.recentSearch}`);

		return Promise.resolve();
	}

	redirectToConference(code: string): Promise
	{
		Logger.warn('Desktop: redirectToConference', code);

		this.openBxLink(`bx://${DesktopBxLink.conference}/code/${code}`);

		return Promise.resolve();
	}

	redirectToSettings(sectionName: string): Promise
	{
		Logger.warn('Desktop: redirectToSettings', sectionName);

		this.openBxLink(`bx://${DesktopBxLink.settings}/section/${sectionName}`);

		return Promise.resolve();
	}

	openConference(code: string): Promise
	{
		Logger.warn('Desktop: openConference', code);

		const result = Conference.openConference(code);
		if (!result)
		{
			return Promise.resolve(false);
		}

		return Promise.resolve(true);
	}

	toggleConference()
	{
		Logger.warn('Desktop: toggleConference');

		Conference.toggleConference();
	}

	redirectToChatCreation(chatType: CreatableChatType): Promise
	{
		Logger.warn('Desktop: redirectToChatCreation', chatType);
		this.openBxLink(`bx://${DesktopBxLink.chatCreation}/chatType/${chatType}/`);

		return Promise.resolve();
	}

	redirectToVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		Logger.warn('Desktop: redirectToVideoCall', dialogId, withVideo);
		const withVideoParam = withVideo ? 'Y' : 'N';
		this.openBxLink(`bx://${DesktopBxLink.call}/dialogId/${dialogId}/withVideo/${withVideoParam}`);

		return Promise.resolve();
	}

	redirectToPhoneCall(number: string, params: Object<any, string>): Promise
	{
		Logger.warn('Desktop: redirectToPhoneCall', number, params);
		const encodedParams = Encoder.encodeParamsJson(params);
		this.openBxLink(`bx://${DesktopBxLink.phone}/number/${number}/phoneParams/${encodedParams}`);

		return Promise.resolve();
	}

	redirectToCallList(callListId: number, params: Object<string, any>): Promise
	{
		Logger.warn('Desktop: redirectToCallList', callListId, params);
		const encodedParams = Encoder.encodeParamsJson(params);
		this.openBxLink(`bx://${DesktopBxLink.callList}/callListId/${callListId}/callListParams/${encodedParams}`);

		return Promise.resolve();
	}

	openAccountTab(domainName: string)
	{
		this.openBxLink(`bx://v2/${domainName}/${DesktopBxLink.openTab}`);
	}

	openPage(url: string, options: { skipNativeBrowser?: boolean } = {})
	{
		const encodedParams = Encoder.encodeParamsJson({ url, options });

		this.openBxLink(`bx://${DesktopBxLink.openPage}/options/${encodedParams}`);
	}

	redirectToLayout({ id, entityId }): Promise
	{
		Logger.warn('Desktop: redirectToLayout', id, entityId);
		const preparedEntityId = entityId ?? '';
		this.openBxLink(`bx://${DesktopBxLink.openLayout}/id/${id}/entityId/${preparedEntityId}`);

		return Promise.resolve();
	}

	async checkStatusInDifferentContext(): Promise<boolean>
	{
		if (!this.isDesktopActive())
		{
			return false;
		}

		if (DesktopApi.isChatWindow())
		{
			return false;
		}

		if (DesktopApi.isDesktop() && !DesktopApi.isChatWindow())
		{
			return true;
		}

		return CheckUtils.testImageLoad();
	}

	checkForRedirect(): Promise<boolean>
	{
		if (!this.isRedirectEnabled() || !this.isRedirectOptionEnabled())
		{
			return Promise.resolve(false);
		}

		if (DesktopApi.isAirDesignEnabledInDesktop())
		{
			return Promise.resolve(false);
		}

		return this.checkStatusInDifferentContext();
	}

	async checkForOpenBrowserPage(): Promise<boolean>
	{
		await Core.ready();

		if (!this.isDesktopActive() || !this.isRedirectOptionEnabled())
		{
			return false;
		}

		const desktopVersion = this.getDesktopVersion();
		if (!DesktopApi.isFeatureSupportedInVersion(desktopVersion, DesktopFeature.openPage.id))
		{
			return false;
		}

		return CheckUtils.testImageLoad(CheckUtils.IMAGE_DESKTOP_TWO_WINDOW_MODE);
	}

	isRedirectEnabled(): boolean
	{
		return this.#enableRedirectCounter > 0;
	}

	enableRedirect()
	{
		this.#enableRedirectCounter++;
	}

	disableRedirect()
	{
		this.#enableRedirectCounter--;
	}

	isRedirectOptionEnabled(): boolean
	{
		if (!FeatureManager.isFeatureAvailable(Feature.isDesktopRedirectAvailable))
		{
			return false;
		}

		if (DesktopApi.isDesktop() && !DesktopApi.isChatWindow())
		{
			return true;
		}

		return Core.getStore().getters['application/settings/get'](Settings.desktop.enableRedirect);
	}

	openBxLink(rawUrl: string)
	{
		const preparedUrl = this.#prepareBxUrl(rawUrl);

		this.#locationChangedToBx = true;
		setTimeout(() => {
			const event = new BaseEvent({ compatData: [] });
			EventEmitter.emit(window, 'BXLinkOpened', event);
			this.#locationChangedToBx = false;
		}, LOCATION_RESET_TIMEOUT);

		location.href = preparedUrl;
	}

	#prepareBxUrl(url: string): string
	{
		if (/^bx:\/\/v(\d)\//.test(url))
		{
			return url;
		}

		return url.replace('bx://', `bx://v${DESKTOP_PROTOCOL_VERSION}/${location.hostname}/`);
	}

	#shouldShowDesktopUpdateBanner(): boolean
	{
		const isOldDesktopVersion = DesktopApi.getMajorVersion() < DESKTOP_VERSION_WITH_AIR_DESIGN_SUPPORT;

		return isOldDesktopVersion && DesktopApi.isAirDesignEnabledInDesktop();
	}

	#showDesktopUpdateBanner()
	{
		const desktopUpdateBanner = BitrixVue.createApp({
			name: BANNER_COMPONENT_NAME,
			components: { DesktopUpdateBanner },
			template: '<DesktopUpdateBanner />',
		});

		desktopUpdateBanner.mount(document.body);

		Analytics.getInstance().desktopUpdateBanner.onShow();
	}

	#initDesktopStatus()
	{
		const settings = Extension.getSettings('im.v2.lib.desktop');
		this.setDesktopActive(settings.get('desktopIsActive'));
		this.setDesktopVersion(settings.get('desktopActiveVersion'));
	}
}
