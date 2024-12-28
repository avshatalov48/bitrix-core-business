import { Extension } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { DesktopBxLink, Settings } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';

import { CheckUtils } from './classes/check-utils';
import { Conference } from './classes/conference';
import { Desktop } from './classes/desktop';
import { Browser } from './classes/browser';
import { Encoder } from './classes/encoder';

import type { CreatableChatType } from 'im.v2.component.content.chat-forms.forms';

const DESKTOP_PROTOCOL_VERSION = 2;
const LOCATION_RESET_TIMEOUT = 1000;

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

		if (DesktopManager.isDesktop())
		{
			if (DesktopApi.isChatWindow())
			{
				Desktop.init();
			}
			else
			{
				Browser.init();
			}
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

	checkStatusInDifferentContext(): Promise
	{
		if (!this.isDesktopActive())
		{
			return Promise.resolve(false);
		}

		if (DesktopApi.isChatWindow())
		{
			return Promise.resolve(false);
		}

		return new Promise((resolve) => {
			CheckUtils.testImageLoad(
				() => {
					resolve(true);
				},
				() => {
					resolve(false);
				},
			);
		});
	}

	checkForRedirect(): Promise
	{
		if (!this.isRedirectEnabled() || !this.isRedirectOptionEnabled())
		{
			return Promise.resolve(false);
		}

		return this.checkStatusInDifferentContext();
	}

	checkForOpenBrowserPage(): Promise
	{
		if (!this.isDesktopActive() || !this.isRedirectOptionEnabled())
		{
			return Promise.resolve(false);
		}

		const desktopVersion = this.getDesktopVersion();
		if (!DesktopApi.isFeatureSupportedInVersion(desktopVersion, DesktopFeature.openPage.id))
		{
			return Promise.resolve(false);
		}

		return new Promise((resolve) => {
			CheckUtils.testImageLoad(
				() => {
					CheckUtils.testImageLoad(
						() => {
							resolve(true);
						},
						() => {
							resolve(false);
						},
						CheckUtils.IMAGE_DESKTOP_TWO_WINDOW_MODE,
					);
				},
				() => {
					resolve(false);
				},
				CheckUtils.IMAGE_DESKTOP_RUN,
			);
		});
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

	#initDesktopStatus()
	{
		const settings = Extension.getSettings('im.v2.lib.desktop');
		this.setDesktopActive(settings.get('desktopIsActive'));
		this.setDesktopVersion(settings.get('desktopActiveVersion'));
	}
}
