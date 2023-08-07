import {Extension} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';

import {DesktopBxLink} from 'im.v2.const';
import {Logger} from 'im.v2.lib.logger';
import {DesktopApi} from 'im.v2.lib.desktop-api';

import {Checker} from './classes/checker';
import {Desktop} from './classes/desktop';

const DESKTOP_PROTOCOL_VERSION = 2;
const LOCATION_RESET_TIMEOUT = 1000;

export class DesktopManager
{
	static instance: DesktopManager;

	#desktopIsActive: boolean;
	#locationChangedToBx = false;

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

	openChat(dialogId: string = ''): Promise
	{
		Logger.warn('Desktop: openChat', dialogId);
		this.#goToBx(`bx://${DesktopBxLink.chat}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	openNotifications(): Promise
	{
		Logger.warn('Desktop: openNotifications');
		this.#goToBx(`bx://${DesktopBxLink.notifications}`);

		return Promise.resolve();
	}

	openRecentSearch()
	{
		Logger.warn('Desktop: openRecentSearch');
		this.#goToBx(`bx://${DesktopBxLink.recentSearch}`);

		return Promise.resolve();
	}

	startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		Logger.warn('Desktop: startVideoCall', dialogId, withVideo);
		this.#goToBx(`bx://${DesktopBxLink.call}/dialogId/${dialogId}`);

		return Promise.resolve();
	}

	checkStatusInDifferentContext(): Promise
	{
		if (!this.#desktopIsActive)
		{
			return Promise.resolve(false);
		}

		if (DesktopManager.isDesktop() && DesktopApi.isChatWindow())
		{
			return Promise.resolve(false);
		}

		return new Promise((resolve) => {
			Checker.testImageUpload(
				() => { resolve(true); },
				() => { resolve(false); }
			);
		});
	}

	#goToBx(rawUrl: string)
	{
		const preparedUrl = this.#prepareBxUrl(rawUrl);

		this.#locationChangedToBx = true;
		setTimeout(() => {
			const event = new BaseEvent({compatData: []});
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