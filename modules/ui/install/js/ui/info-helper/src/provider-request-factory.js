import { ajax } from 'main.core';
import { ProvidersType } from './types/providers-type';

export class ProviderRequestFactory
{
	#type: ProvidersType.SLIDER | ProvidersType.POPUP;
	#code: string;
	static controller: string = 'ui.infoHelper.getInitParams';

	constructor(type: string, code: string = null)
	{
		if ((type === ProvidersType.SLIDER) || (type === ProvidersType.POPUP))
		{
			this.#type = type;
			this.#code = code;
		}
		else
		{
			throw new Error("Invalid parameter 'type'");
		}
	}

	getRequest(): Promise
	{
		return ajax.runAction(ProviderRequestFactory.controller, {
			data: {
				type: this.#type,
				code: this.#code,
				currentUrl: window.location.href,
			},
		});
	}
}