import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { PromoId } from 'im.v2.const';

import { PromoService } from './classes/promo-service';
import { Promo } from './classes/promo';

import type { PromotionUpdatedParams, PromoParams, RawPromoData } from 'im.v2.provider.pull';

export class PromoManager
{
	static #instance: PromoManager;

	#promoList: Promo[];

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

	needToShow(promoId: $Values<typeof PromoId>, promoParams: PromoParams = {}): boolean
	{
		const promo: Promo = new Promo(promoId, promoParams);

		return Boolean(this.#get(promo));
	}

	async markAsWatched(promoId: $Values<typeof PromoId>, promoParams: PromoParams = {}): Promise<void>
	{
		const promo: Promo = new Promo(promoId, promoParams);

		if (this.#get(promo))
		{
			await PromoService.markAsWatched(promo);
			this.#remove(promo);
		}
	}

	onPromotionUpdated(params: PromotionUpdatedParams): void
	{
		const deletedPromotions: Promo[] = params.deletedPromotions.map(
			(promoData: RawPromoData) => Promo.createFromRawPromoData(promoData),
		);

		this.#removeByPromotionList(deletedPromotions);

		const addedPromotions: Promo[] = params.addedPromotions.map(
			(promoData: RawPromoData) => Promo.createFromRawPromoData(promoData),
		);

		this.#promoList = [...this.#promoList, ...addedPromotions];
	}

	#init(promoList: RawPromoData[]): void
	{
		this.#promoList = promoList.map((promoData: RawPromoData) => Promo.createFromRawPromoData(promoData));
	}

	#get(promo: Promo): ?Promo
	{
		return this.#promoList.find((item: Promo) => item.isEqual(promo));
	}

	#remove(promo: Promo): void
	{
		this.#promoList = this.#promoList.filter((item: Promo) => !item.isEqual(promo));
	}

	#removeByPromotionList(promoList: Promo[]): void
	{
		this.#promoList = this.#promoList.filter((promo: Promo) => {
			const deletedPromo = promoList.find((deleted: Promo) => deleted.id === promo.id);
			if (!deletedPromo)
			{
				return true;
			}

			if (deletedPromo.isEmptyParams())
			{
				return false;
			}

			return !promo.isEqual(deletedPromo);
		});
	}
}
