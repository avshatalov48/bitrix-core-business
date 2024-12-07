import { ajax } from 'main.core';
import { ProviderRequestFactoryConfiguration } from './types/configurations';
import { ProvidersType } from './types/providers-type';

export class ProviderRequestFactory
{
	#type: ProvidersType.SLIDER | ProvidersType.POPUP ;
	#code: ?string;
	#featureId: ?string;
	static controller: string = 'ui.infoHelper.getInitParams';

	constructor(configuration: ProviderRequestFactoryConfiguration)
	{
		if ((configuration.type === ProvidersType.SLIDER) || (configuration.type === ProvidersType.POPUP))
		{
			this.#type = configuration.type;
			this.#code = configuration.code ?? null;
			this.#featureId = configuration.featureId ?? null;
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
				featureId: this.#featureId,
			},
		});
	}
}