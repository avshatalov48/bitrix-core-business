import { Event } from 'main.core';
import { FeaturePromotersRegistry } from './export';

export class FeaturePromoterAutoBinder
{
	static #elements: ?NodeList;
	static #isLaunch: boolean;
	static #attributeName: string = 'data-feature-promoter';

	static launch(): void
	{
		if (!FeaturePromoterAutoBinder.#isLaunch)
		{
			FeaturePromoterAutoBinder.getElements().forEach((element) => {
				Event.bind(element, 'click', (event) => {
					const code = event.target?.attributes?.getNamedItem(FeaturePromoterAutoBinder.#attributeName)?.value;

					if (code)
					{
						FeaturePromotersRegistry.getPromoter({
							code: code,
							bindElement: element,
						}).show();
					}
				});
			});
			FeaturePromoterAutoBinder.#isLaunch = true;
		}
	}

	static getElements(): NodeList
	{
		if (!FeaturePromoterAutoBinder.#elements)
		{
			FeaturePromoterAutoBinder.#elements = document.querySelectorAll(`[${FeaturePromoterAutoBinder.#attributeName}]`);
		}

		return FeaturePromoterAutoBinder.#elements;
	}
}
