import { Extension, Type } from 'main.core';
import { InfoHelper } from 'ui.info-helper';
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
		if (!options.code && !options.featureId)
		{
			throw new Error("Either the 'code' parameter or the 'featureId' parameter is required");
		}

		this.#options = options;
		this.#options.bindElement = options.bindElement ?? null;
		this.#code = options.code ?? '';

		const settings = Extension.getSettings('ui.info-helper');
		this.#provider = this.#createProvider(settings);
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

	close(): void
	{
		return this.#provider.close();
	}

	getBindElement(): ?HTMLElement
	{
		return this.getOptions().bindElement;
	}

	#createProvider(settings: Object): BaseProvider
	{
		if (
			Type.isObject(settings)
			&& settings.popupProviderEnabled
			&& Type.isDomNode(this.getOptions().bindElement)
			&& (!this.getOptions().featureId || !settings.isUpgradeTariffAvailable)
		)
		{
			return new PopupProvider({
				bindElement: this.getOptions().bindElement,
				code: this.#code,
				featureId: this.getOptions().featureId ?? null,
			});
		}
		else
		{
			const provider = new SliderProvider({ featureId: this.getOptions().featureId ?? null });
			InfoHelper.sliderProviderForOldFormat = provider;

			return provider;
		}
	}
}