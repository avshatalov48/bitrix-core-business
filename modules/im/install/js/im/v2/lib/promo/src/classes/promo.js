import { PromoId } from 'im.v2.const';

import type { PromoParams, RawPromoData } from 'im.v2.provider.pull';

export class Promo
{
	id: $Values<typeof PromoId>;
	params: PromoParams;

	constructor(id: $Values<typeof PromoId>, params: PromoParams)
	{
		this.id = id;
		this.params = params;
	}

	static createFromRawPromoData(data: RawPromoData): Promo
	{
		return new Promo(data.id, data.params);
	}

	isEmptyParams(): boolean
	{
		return Object.keys(this.params).length === 0;
	}

	isEqual(promo: Promo): boolean
	{
		return (this.id === promo.id) && this.#isParamsEqual(promo.params);
	}

	#isParamsEqual(params: PromoParams): boolean
	{
		return Number(this.params.chatId ?? null) === Number(params.chatId ?? null);
	}
}
