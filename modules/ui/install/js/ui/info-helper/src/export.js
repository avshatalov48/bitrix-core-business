import { InfoHelper } from './info-helper';
import { FeaturePromoter } from './feature-promoter';
import { FeaturePromotersRegistry } from './feature-promoters-registry';
import { FeaturePromoterAutoBinder } from './feature-promoter-auto-binder';
import './info-helper.css';

export {
	InfoHelper,
	FeaturePromoter,
	FeaturePromotersRegistry,
};

BX.ready(() => {
	FeaturePromoterAutoBinder.launch();
});
