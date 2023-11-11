import { Extension } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { DesktopBxLink, Settings } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { Checker } from './classes/checker';
import { Conference } from './classes/conference';
import { Desktop } from './classes/desktop';
import { Encoder } from './classes/encoder';

const DESKTOP_PROTOCOL_VERSION = 2;
const LOCATION_RESET_TIMEOUT = 1000;

export class DesktopManager
{
	static instance: DesktopManager;

	#desktopIsActive: boolean;
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

		if (DesktopManager.isDesktop() && DesktopApi.isChatWindow())
		{
			Desktop.init();
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

	isLocationChangedToBx(): boolean
	{
		return this.#locationChangedToBx;
	}

	redirectToChat(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToChat', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.chat}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	redirectToLines(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: redirectToLines', dialogId);
		this.openBxLink(`bx://${DesktopBxLink.lines}/dialogId/${dialogId}`);

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

	checkStatusInDifferentContext(): Promise
	{
		if (!this.#desktopIsActive)
		{
			return Promise.resolve(false);
		}

		if (DesktopApi.isChatWindow())
		{
			return Promise.resolve(false);
		}

		return new Promise((resolve) => {
			Checker.testImageUpload(
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
	}
}
