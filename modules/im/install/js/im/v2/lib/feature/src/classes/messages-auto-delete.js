import { FeaturePromoter } from 'ui.info-helper';

import { SliderCode } from 'im.v2.const';

export const MessagesAutoDelete = {
	openFeatureSlider(): void
	{
		const promoter = new FeaturePromoter({ code: SliderCode.autoDeleteDisabled });
		promoter.show();
	},
};
