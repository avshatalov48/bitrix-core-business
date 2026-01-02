import type PopupType from './popup-type';
import type PopupCategory from './popup-category';

export type PopupConfig = {
	type: PopupType,
	category: PopupCategory,
	expireDate: string,
	expireDays: string,
	marketSubscriptionUrl: string,
	withDemo: boolean,
	olWidgetCode: ?string,
	isRenamedMarket: boolean,
	discount: {
		isAvailable: boolean,
		percentage: ?number,
		termsUrl: ?string,
	},
}
