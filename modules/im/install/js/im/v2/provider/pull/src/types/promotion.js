import { PromoId } from 'im.v2.const';

export type PromotionUpdatedParams = {
	addedPromotions: RawPromoData[],
	deletedPromotions: RawPromoData[],
};

export type RawPromoData = {
	id: $Values<typeof PromoId>,
	params: PromoParams,
};

export type PromoParams = {
	chatId?: number,
};
