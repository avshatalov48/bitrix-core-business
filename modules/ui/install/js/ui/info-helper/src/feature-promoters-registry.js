import { FeaturePromoter } from './feature-promoter';
import type { FeaturePromoterConfiguration } from './types/configurations';

export class FeaturePromotersRegistry
{
	static #promoters: Array<FeaturePromoter> = [];

	static register(promoter: FeaturePromoter): void
	{
		if (!FeaturePromotersRegistry.exist(promoter))
		{
			FeaturePromotersRegistry.#promoters.push(promoter);
		}
	}

	static exist(promoter: FeaturePromoter): boolean
	{
		return FeaturePromotersRegistry.#promoters.find((savedPromoter) => savedPromoter === promoter);
	}

	static getPromoter(config: FeaturePromoterConfiguration): FeaturePromoter
	{
		let promoter;

		FeaturePromotersRegistry.#promoters.forEach((savedPromoter) => {
			let isSavedPromoter = true;

			Object.keys(savedPromoter.getOptions()).forEach((key) => {
				if (savedPromoter.getOptions()[key] !== config[key])
				{
					isSavedPromoter = false;
				}
			});

			if (isSavedPromoter)
			{
				promoter = savedPromoter;
			}
		});

		if (promoter instanceof FeaturePromoter)
		{
			return promoter;
		}

		promoter = new FeaturePromoter(config);
		FeaturePromotersRegistry.register(promoter);

		return promoter;
	}
}