import {EventType, RestMethod} from 'im.v2.const';
import {runAction} from 'im.v2.lib.rest';

import {DesktopApi} from 'im.v2.lib.desktop-api';

export class AuthHandler
{
	static init(): AuthHandler
	{
		return new AuthHandler();
	}

	constructor()
	{
		this.#subscribeToLogoutEvent();
	}

	#subscribeToLogoutEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onExit, this.#onExit.bind(this));
	}

	#onExit()
	{
		runAction(RestMethod.imV2DesktopLogout)
			.finally(() => {
				DesktopApi.shutdown();
			});
	}
}