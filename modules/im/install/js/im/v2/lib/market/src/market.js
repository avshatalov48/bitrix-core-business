import { Store } from 'ui.vue3.vuex';
import { Runtime } from 'main.core';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';

import { MarketService } from './classes/market-service';
import { AvailabilityManager } from './classes/availability-manager';

import type { ImModelMarketApplication } from 'im.v2.model';

type MarketApps = {
	items: ImModelMarketApplication[],
	links: {
		load: string
	}
}

export class MarketManager
{
	static #instance: MarketManager;

	#store: Store;
	#marketService: MarketService;
	#availabilityManager: AvailabilityManager;

	static getInstance(): MarketManager
	{
		if (!this.#instance)
		{
			this.#instance = new this();
		}

		return this.#instance;
	}

	static init()
	{
		MarketManager.getInstance();
	}

	constructor()
	{
		this.#store = Core.getStore();
		this.#marketService = new MarketService();
		this.#availabilityManager = new AvailabilityManager();

		const { marketApps } = Core.getApplicationData();
		Logger.warn('MarketManager: marketApps', marketApps);
		this.#init(marketApps);
	}

	getAvailablePlacementsByType(placementType: string, dialogId: string = ''): ImModelMarketApplication[]
	{
		const placements: ImModelMarketApplication[] = this.#store.getters['market/getByPlacement'](placementType);
		const dialog = this.#store.getters['chats/get'](dialogId);
		const dialogType = dialog ? dialog.type : '';

		return this.#availabilityManager.getAvailablePlacements(placements, dialogType);
	}

	loadPlacement(id: number, context: Object = {}): Promise
	{
		const placement = this.#store.getters['market/getById'](Number.parseInt(id, 10));

		return this.#marketService.openPlacement(placement, context);
	}

	unloadPlacement(placementId: string)
	{
		const appLayoutNew = Object.values(BX.rest.layoutList).filter((layout) => {
			return layout.params.placementId === placementId;
		});

		if (appLayoutNew.length > 0)
		{
			appLayoutNew.forEach((layout) => {
				layout.destroy();
			});
		}
	}

	static async openSlider(placement: ImModelMarketApplication, context: Object)
	{
		await Runtime.loadExtension('applayout');
		BX.rest.AppLayout.openApplication(placement.loadConfiguration.ID, context, placement.loadConfiguration);
	}

	static openMarketplace()
	{
		const marketplaceImPlacementCode = 'IM_CHAT';

		BX.SidePanel.Instance.open(`/market/?placement=${marketplaceImPlacementCode}`);
	}

	#init(marketApps: MarketApps)
	{
		if (!marketApps)
		{
			return;
		}

		void this.#store.dispatch('market/set', marketApps);
		this.#marketService.setLoadLink(marketApps.links.load);
	}
}
