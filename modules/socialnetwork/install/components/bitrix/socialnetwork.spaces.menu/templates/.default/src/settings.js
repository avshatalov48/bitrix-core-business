import { Cache } from 'main.core';
import { Chat } from './chat';
import { GroupSettings } from './group-settings';
import { UserSettings } from './user-settings';
import type { LogoData } from 'socialnetwork.logo';

type Params = {
	bindElement: HTMLElement,
	type: 'group' | 'user',
	entityId: number,
	logo?: LogoData,
	chat: Chat,
	availableFeatures: { [option: 'discussions' | 'tasks' | 'calendar' | 'files']: boolean },
	isMember?: boolean,
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
					availableFeatures: this.#getParam('availableFeatures'),
					isMember: this.#getParam('isMember'),
					groupId: this.#getParam('entityId'),
					logo: this.#getParam('logo'),
					chat: this.#getParam('chat'),
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
