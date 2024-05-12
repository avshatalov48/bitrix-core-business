import { Extension, Type } from 'main.core';
import { BaseProvider } from './providers/base-provider';
import { PopupProvider } from './providers/popup-provider';
import { SliderProvider } from './providers/slider-provider';
import type { FeaturePromoterConfiguration } from './types/configurations';

export class FeaturePromoter
{
	#code: string;
	#provider: BaseProvider;
	#options: FeaturePromoterConfiguration;

	constructor(options: FeaturePromoterConfiguration)
	{
		this.#options = options;
		const settings = Extension.getSettings('ui.info-helper');
		this.#options.bindElement = options.bindElement ?? null;
		this.#code = options.code;

		if (!options.code)
		{
			throw new Error("'code' parameter is required.");
		}

		if (Type.isObject(settings) && settings.popupProviderEnabled && Type.isDomNode(options.bindElement))
		{
			this.#provider = new PopupProvider({
				bindElement: options.bindElement,
				code: this.#code,
			});
		}
		else
		{
			this.#provider = new SliderProvider();
		}
	}

	getOptions(): FeaturePromoterConfiguration
	{
		return this.#options;
	}

	getProvider(): BaseProvider
	{
		return this.#provider;
	}

	show(): void
	{
		return this.#provider.show(this.#code, {});
	}
}