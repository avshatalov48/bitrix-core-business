import {Event} from 'main.core';

import {Utils} from 'im.v2.lib.utils';

import {DesktopApi} from 'im.v2.lib.desktop-api';

export class HotkeyHandler
{
	static init(): HotkeyHandler
	{
		return new HotkeyHandler();
	}

	constructor()
	{
		this.#bindHotkeys();
	}

	#bindHotkeys()
	{
		Event.bind(window, 'keydown', (event) => {
			const reloadCombination = Utils.key.isCombination(event, 'Ctrl+R');
			if (reloadCombination)
			{
				DesktopApi.reloadWindow();
			}
		});
	}
}