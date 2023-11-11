import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { PromoId } from 'im.v2.const';

import { PromoService } from './classes/promo-service';

export class PromoManager
{
	static #instance: PromoManager;

	#promoList: Set<string>;

	static getInstance(): PromoManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		PromoManager.getInstance();
	}

	constructor()
	{
		const { promoList } = Core.getApplicationData();
		Logger.warn('PromoManager: promoList', promoList);
		this.#init(promoList);
	}

	needToShow(promoId: $Values<typeof PromoId>): boolean
	{
		return this.#promoList.has(promoId);
	}

	async markAsWatched(promoId: $Values<typeof PromoId>)
	{
		if (!this.#promoList.has(promoId))
		{
			return;
		}

		await PromoService.markAsWatched(promoId);
		this.#promoList.delete(promoId);
	}

	#init(rawPromoList: string[])
	{
		this.#promoList = new Set(rawPromoList);
	}
}
