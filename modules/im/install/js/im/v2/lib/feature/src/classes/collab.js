import { SliderCode } from 'im.v2.const';
import { FeatureManager, Feature } from 'im.v2.lib.feature';
import { FeaturePromoter } from 'ui.info-helper';

export const CollabManager = {
	isAvailable(): boolean
	{
		return FeatureManager.isFeatureAvailable(Feature.collabAvailable);
	},
	openFeatureSlider(): void
	{
		const promoter = new FeaturePromoter({ featureId: SliderCode.collabDisabled });
		promoter.show();
	},
};
