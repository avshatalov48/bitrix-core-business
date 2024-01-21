import { Cache } from 'main.core';
import { Chat } from './chat';
import { GroupSettings } from './group-settings';
import { MenuRouter } from './menu-router';
import { UserSettings } from './user-settings';

import type { LogoData } from './logo';

type Params = {
	bindElement: HTMLElement,
	type: 'group' | 'user',
	entityId: number,
	logo?: LogoData,
	chat: Chat,
	router: MenuRouter,
}

import './css/settings.css';

export class Settings
{
	#cache = new Cache.MemoryCache();

	#settings: UserSettings | GroupSettings;

	constructor(params: Params)
	{
		this.#setParams(params);
	}

	show(): void
	{
		if (!this.#settings)
		{
			if (this.#getParam('type') === 'user')
			{
				this.#settings = new UserSettings({
					bindElement: this.#getParam('bindElement'),
					userId: this.#getParam('entityId'),
				});
			}
			else
			{
				this.#settings = new GroupSettings({
					bindElement: this.#getParam('bindElement'),
					groupId: this.#getParam('entityId'),
					logo: this.#getParam('logo'),
					chat: this.#getParam('chat'),
					router: this.#getParam('router'),
				});
			}
		}

		this.#settings.show();
	}

	update(groupDataPromise: Promise)
	{
		this.#settings?.update(groupDataPromise);
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}
}
