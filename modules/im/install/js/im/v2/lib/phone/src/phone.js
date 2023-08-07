import 'voximplant';
import { PhoneCallsController } from 'voximplant.phone-calls';
import 'applayout'; // should not be here

import { Core } from 'im.v2.application.core';

import type { ImModelUser } from 'im.v2.model';

type PhoneSettings = {
	phoneEnabled: boolean,
	deviceActive: boolean,
	canInterceptCall: boolean,
	canPerformCalls: boolean,
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

export class PhoneManager
{
	static instance: PhoneManager;

	#controller: PhoneCallsController;

	static getInstance(): PhoneManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	static init(phoneSettings: PhoneSettings)
	{
		PhoneManager.getInstance().#init(phoneSettings);
	}

	openKeyPad(params: KeyPadParams)
	{
		if (!this.#controller)
		{
			return;
		}

		this.#controller.openKeyPad(params);
	}

	closeKeyPad()
	{
		if (!this.#controller)
		{
			return;
		}

		this.#controller.closeKeyPad();
	}

	#init(phoneSettings: PhoneSettings)
	{
		return;
		this.#controller = this.#getController(phoneSettings);
	}

	#getController(phoneSettings: PhoneSettings): PhoneCallsController
	{
		return new PhoneCallsController({
			phoneEnabled: phoneSettings.phoneEnabled,

			userId: Core.getUserId(),
			isAdmin: this.#getCurrentUser().isAdmin,

			restApps: phoneSettings.restApps,
			canInterceptCall: phoneSettings.canInterceptCall,
			deviceActive: phoneSettings.deviceActive,
			defaultLineId: phoneSettings.defaultLineId,
			availableLines: phoneSettings.availableLines,
		});
	}

	#getCurrentUser(): ImModelUser
	{
		const userId = Core.getUserId();

		return Core.getStore().getters['users/get'](userId);
	}
}
