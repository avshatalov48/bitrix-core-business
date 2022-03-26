import {Type} from 'main.core';
import {BaseProvider} from './base_provider';
import {GoogleMap} from './google_map';
import {YandexMap} from './yandex_map';

import './css/style.css'


export class Map
{
	static +PROVIDERS = {
		google: GoogleMap,
		yandex: YandexMap,
	};
	static +DEFAULT_PROVIDER = 'google';
	static +DATA_ATTRIBUTE = 'mapProvider';

	/**
	 * If API not loaded already - create schedule
	 * @type {{}}
	 */
	static scheduled = {};

	constructor()
	{
	}

	/**
	 * Create map provider for current node
	 * @param node
	 * @param options
	 * @return {*}
	 */
	static create(node: HTMLElement, options: {}): BaseProvider
	{
		// handler for load api
		options.onApiLoaded = Map.onApiLoaded;

		// get provider code
		let providerCode = node.dataset[Map.DATA_ATTRIBUTE];
		if (
			!providerCode
			|| Object.keys(Map.PROVIDERS).indexOf(providerCode) === -1
		)
		{
			providerCode = Map.DEFAULT_PROVIDER;
		}

		// init or set to schedule
		const provider = new (Map.PROVIDERS[providerCode])(options);
		if (provider.isApiLoaded())
		{
			provider.onInitHandler();
		}
		else
		{
			if (!Type.isArray(Map.scheduled[provider.getCode()]))
			{
				Map.scheduled[provider.getCode()] = [];
			}

			Map.scheduled[provider.getCode()].push(provider);
		}

		return provider;
	}

	static onApiLoaded(providerCode: string)
	{
		if (Type.isArray(Map.scheduled[providerCode]))
		{
			Map.scheduled[providerCode].forEach(provider =>
			{
				provider.onInitHandler();
			});
		}
	}
}