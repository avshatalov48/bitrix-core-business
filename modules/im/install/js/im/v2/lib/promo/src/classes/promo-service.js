import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import { Promo } from './promo';

export class PromoService
{
	static markAsWatched(promo: Promo): Promise
	{
		Logger.warn('PromoService: markAsWatched:', promo);

		const payload = {
			data: {
				id: promo.id,
				params: promo.params,
			},
		};

		runAction(RestMethod.imV2PromotionRead, payload)
			.catch(([error]) => {
				console.error('PromoService: markAsWatched error:', error);
			});
	}
}
