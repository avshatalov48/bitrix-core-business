import { Cache } from 'main.core';
import { PopupComponentsMaker } from 'ui.popupcomponentsmaker';

type Params = {
	bindElement: HTMLElement,
	userId: number,
}

export class UserSettings
{
	#cache = new Cache.MemoryCache();

	#menu: PopupComponentsMaker;

	constructor(params: Params)
	{
		this.#setParams(params);
	}

	show()
	{

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
