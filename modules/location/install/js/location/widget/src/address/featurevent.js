import BaseFeature from "./features/basefeature";

export type FeatureEvent = {
	feature: BaseFeature,
	eventCode: string,
	payload: Object,
};
