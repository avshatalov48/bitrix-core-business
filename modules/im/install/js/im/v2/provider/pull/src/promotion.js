import { PromoManager } from 'im.v2.lib.promo';

import type { PromotionUpdatedParams } from 'im.v2.provider.pull';

export class PromotionPullHandler
{
	getModuleId(): string
	{
		return 'im';
	}

	handlePromotionUpdated(params: PromotionUpdatedParams): void
	{
		PromoManager.getInstance().onPromotionUpdated(params);
	}
}
