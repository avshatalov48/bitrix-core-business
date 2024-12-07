import { Loc } from 'main.core';
import { Util } from "calendar.util";
import { QrAuthorization } from 'ui.qrauthorization';

export default class MobileSyncBanner
{
	zIndex = 3100;
	DOM = {};
	QRC = null;

	constructor(options = {})
	{
		this.type = options.type;
		this.helpDeskCode = options.helpDeskCode || '11828176';
		this.alreadyConnectedToNew = this.type === 'android'
			? Util.isGoogleConnected()
			: Util.isIcloudConnected()
		;

		this.qrAuth = null;
	}

	show()
	{
		this.qrAuth ??= new QrAuthorization({
			title: Loc.getMessage('SYNC_BANNER_MOBILE_TITLE'),
			content: Loc.getMessage('SYNC_MOBILE_NOTICE'),
			intent: this.type ? 'calendar_sync_slider' : 'calendar_sync_banner',
			showFishingWarning: true,
			showBottom: false,
		});

		this.qrAuth.show();
	}
}
