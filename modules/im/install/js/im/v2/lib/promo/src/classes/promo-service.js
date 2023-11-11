import { Core } from 'im.v2.application.core';
import { PromoId, RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

export class PromoService
{
	static markAsWatched(promoId: $Values<typeof PromoId>): Promise
	{
		Logger.warn('PromoService: markAsWatched:', promoId);

		return Core.getRestClient().callMethod(RestMethod.imPromotionRead, {
			id: promoId,
		})
			.catch((error) => {
				console.error('PromoService: markAsWatched error:', error);
			});
	}
}
