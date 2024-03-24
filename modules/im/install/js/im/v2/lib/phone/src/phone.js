import 'voximplant';
import { PhoneCallsController } from 'voximplant.phone-calls';
import { Type, Runtime, Reflection } from 'main.core';
import { BaseEvent } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { CallManager } from 'im.v2.lib.call';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';

import type { ImModelUser } from 'im.v2.model';

type PhoneSettings = {
	phoneEnabled: boolean,
	deviceActive: boolean,
	canInterceptCall: boolean,
	canPerformCallsByUser: boolean,
	canPerformCallsByLimits: boolean,
	canCallUserNumber: boolean,
	defaultLineId: string,
	availableLines: string[],
	restApps: Array<{id: number, name: string}>
};

type KeyPadParams = {
	bindElement: HTMLElement,
	offsetTop: number,
	offsetLeft: number,
	anglePosition: 'left' | 'top',
	angleOffset: number
};

type CallConnectedEvent = {
	call: Object<string, any>,
	isIncoming: boolean,
	isDeviceCall: boolean,
};

type StartCallParams = {
	LINE_ID?: string,
};

export class PhoneManager
{
	static #instance: PhoneManager;

	#controller: PhoneCallsController;
	#settings: PhoneSettings;

	static getInstance(): PhoneManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		PhoneManager.getInstance();
	}

	constructor()
	{
		const { phoneSettings } = Core.getApplicationData();
		Logger.warn('PhoneManager: phoneSettings', phoneSettings);
		this.#init(phoneSettings);
	}

	canCall(): boolean
	{
		return this.#settings.phoneEnabled && this.#settings.canPerformCallsByUser;
	}

	openKeyPad(params: KeyPadParams)
	{
		this.#controller?.openKeyPad(params);
	}

	closeKeyPad()
	{
		this.#controller?.closeKeyPad();
	}

	async startCall(number: string, rawParams: StartCallParams = {})
	{
		if (!this.#settings.canPerformCallsByLimits)
		{
			void this.#showCallLimitSlider();

			return;
		}

		if (!this.#controller)
		{
			return;
		}

		let params = rawParams;
		if (Type.isStringFilled(params))
		{
			params = this.#parseStartCallParams(params);
		}

		// await this.#controller.loadPhoneLines();
		//
		// const lineId = params.LINE_ID ?? this.#controller.defaultLineId;
		// if (this.#controller.isRestLine(lineId))
		// {
		// 	this.#controller.startCallViaRestApp(number, lineId, params);
		// }

		this.closeKeyPad();
		this.#controller.phoneCall(number, params);
	}

	startCallList(rawCallListId: string | number, params: Object<string, any>)
	{
		if (!this.#controller)
		{
			return;
		}

		const callListId = Number.parseInt(rawCallListId, 10);
		if (callListId === 0 || Number.isNaN(callListId))
		{
			return;
		}

		this.#controller.startCallList(callListId, params);
	}

	toggleDebugFlag(debug)
	{
		if (!this.#controller)
		{
			return;
		}

		this.#controller.debug = debug;
	}

	#init(phoneSettings: PhoneSettings)
	{
		this.#settings = phoneSettings;
		if (!Reflection.getClass('BX.Voximplant.PhoneCallsController'))
		{
			return;
		}

		this.#controller = this.#getController(phoneSettings);
	}

	#getController(phoneSettings: PhoneSettings): PhoneCallsController
	{
		const soundManager = SoundNotificationManager.getInstance();

		return new PhoneCallsController({
			phoneEnabled: phoneSettings.phoneEnabled,

			userId: Core.getUserId(),
			isAdmin: this.#getCurrentUser().isAdmin,

			restApps: phoneSettings.restApps,
			canInterceptCall: phoneSettings.canInterceptCall,
			deviceActive: phoneSettings.deviceActive,
			defaultLineId: phoneSettings.defaultLineId,
			availableLines: phoneSettings.availableLines,

			messengerFacade: {
				isThemeDark: () => false,
				isDesktop: () => DesktopApi.isDesktop(),
				hasActiveCall: () => CallManager.getInstance().hasCurrentCall(),
				repeatSound: (melodyName, time, force) => soundManager.playLoop(melodyName, time, force),
				stopRepeatSound: (melodyName) => soundManager.stop(melodyName),
				playSound: (melodyName, force) => {
					if (force)
					{
						soundManager.forcePlayOnce(melodyName);

						return;
					}

					soundManager.playOnce(melodyName);
				},

				setLocalConfig: () => {},
				getLocalConfig: () => {},

				getAvatar: (userId) => this.#getUserAvatar(userId),
			},
			events: {
				[PhoneCallsController.Events.onCallCreated]: () => this.#onCallCreated(),
				[PhoneCallsController.Events.onCallConnected]: (event) => this.#onCallConnected(event),
				[PhoneCallsController.Events.onCallDestroyed]: () => this.#onCallDestroyed(),
				[PhoneCallsController.Events.onDeviceCallStarted]: () => this.#onDeviceCallStarted(),
			},
		});
	}

	#onCallCreated()
	{
		if (!DesktopApi.isDesktop())
		{
			return;
		}

		DesktopApi.stopDiskSync();
	}

	#onCallDestroyed()
	{
		if (!DesktopApi.isDesktop())
		{
			return;
		}

		DesktopApi.startDiskSync();
	}

	#onDeviceCallStarted()
	{
		if (!DesktopApi.isDesktop())
		{
			return;
		}

		const target = DesktopApi.findWindow('callWindow') ?? window;
		DesktopApi.activateWindow(target);
		// close desktop topmost window?
	}

	#onCallConnected(event: BaseEvent<CallConnectedEvent>)
	{
		const { isIncoming, isDeviceCall } = event.getData();
		if (!DesktopApi.isDesktop() || isIncoming || isDeviceCall)
		{
			return;
		}

		const target = DesktopApi.findWindow('callWindow') ?? window;
		DesktopApi.activateWindow(target);
	}

	#getCurrentUser(): ImModelUser
	{
		const userId = Core.getUserId();

		return Core.getStore().getters['users/get'](userId);
	}

	#getUserAvatar(userId: number): string
	{
		const user: ImModelUser = Core.getStore().getters['users/get'](userId, true);

		return user.avatar;
	}

	#parseStartCallParams(jsonParams: string): StartCallParams
	{
		let params = jsonParams;
		try
		{
			params = JSON.parse(params);
		}
		catch
		{
			params = {};
		}

		return params;
	}

	async #showCallLimitSlider()
	{
		const SLIDER_EXTENSION = 'voximplant.common';

		await Runtime.loadExtension(SLIDER_EXTENSION);
		BX.Voximplant.openLimitSlider();
	}
}
