import {Extension} from 'main.core';
import {DesktopFeature} from 'im.v2.const';
import {DesktopApi} from './desktop-api';
import {Logger} from 'im.v2.lib.logger';
import {DesktopUtils} from './desktop-utils';

export class DesktopManager
{
	static instance: DesktopManager;

	#desktopIsActive: boolean;
	#desktopVersion: number = 0;

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
		return DesktopApi.isApiAvailable();
	}

	constructor()
	{
		this.#initDesktopStatus();
	}

	isDesktopActive(): boolean
	{
		if (DesktopApi.isApiAvailable())
		{
			return true;
		}

		return this.#desktopIsActive;
	}

	setDesktopActive(flag: boolean)
	{
		this.#desktopIsActive = flag;
	}

	getDesktopVersion(): number
	{
		if (DesktopApi.isApiAvailable())
		{
			return DesktopApi.getApiVersion();
		}

		return this.#desktopVersion;
	}

	setDesktopVersion(version: number)
	{
		if (DesktopApi.isApiAvailable())
		{
			return DesktopApi.getApiVersion();
		}

		this.#desktopVersion = version;
	}

	isDesktopFeatureEnabled(code: $Values<typeof DesktopFeature>): boolean
	{
		if (!DesktopApi.isApiAvailable())
		{
			return false;
		}

		return DesktopApi.isFeatureEnabled(code);
	}

	#initDesktopStatus()
	{
		const settings = Extension.getSettings('im.v2.lib.desktop');
		this.setDesktopActive(settings.get('desktopIsActive'));
		this.setDesktopVersion(settings.get('desktopVersion'));
	}

	startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		Logger.warn('Desktop: onStartVideoCall', dialogId, withVideo);

		const callType = withVideo? 'video': 'audio';
		DesktopUtils.goToBx(`bx://callto/${callType}/${dialogId}`);

		return new Promise((resolve) => {
			resolve();
		});
	}

	checkRunStatus()
	{
		return new Promise((resolve, failure) => {
			DesktopUtils.checkRunStatus(resolve, failure);
		});
	}
}