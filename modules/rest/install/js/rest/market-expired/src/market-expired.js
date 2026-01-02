import { MarketExpiredPopup } from './popup/market-expired-popup';
import { Extension } from 'main.core';
import type { PopupConfig } from './type/popup-config';
import { PopupFactory } from './popup/popup-factory';
import './style.css';
import type { MarketExpiredCurtain } from './curtain/market-expired-curtain';
import { CurtainFactory } from './curtain/curtain-factory';
import CurtainPage from './type/curtain-page';

export {
	CurtainPage,
};

export class MarketExpired
{
	config: PopupConfig;

	constructor(config: PopupConfig)
	{
		this.config = config;
	}

	static async getPopup(config: PopupConfig = null): ?MarketExpiredPopup
	{
		const popupConfig = config ?? Extension.getSettings('rest.market-expired');
		const manager = new PopupFactory(popupConfig);

		return manager.createPopup();
	}

	static getCurtain(curtainPage: CurtainPage, config: PopupConfig = null): ?MarketExpiredCurtain
	{
		const curtainConfig = config ?? Extension.getSettings('rest.market-expired');
		const manager = new CurtainFactory(curtainConfig);

		return manager.createCurtain(curtainPage);
	}
}
