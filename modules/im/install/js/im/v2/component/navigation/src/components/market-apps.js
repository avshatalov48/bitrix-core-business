import 'ui.fontawesome4';

import {Core} from 'im.v2.application.core';
import {Layout, PlacementType} from 'im.v2.const';
import {MarketManager} from 'im.v2.lib.market';

import type {ImModelMarketApplication, ImModelLayout} from 'im.v2.model';

export type MarketMenuItem = {
	id: string,
	text: string,
	counter: number,
	active: boolean,
	iconName: string,
	loadConfiguration?: ImModelMarketApplication['loadConfiguration']
};

// @vue/component
export const MarketApps = {
	name: 'MarketApps',
	emits: ['clickMarketItem'],
	computed:
	{
		marketMenuItems(): MarketMenuItem[]
		{
			const navigationApps = MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.navigation);

			return navigationApps.map((item: ImModelMarketApplication) => {
				return {
					id: item.id,
					text: item.title,
					counter: 0,
					active: true,
					iconName: item.options.iconName ? item.options.iconName : '',
					loadConfiguration: item.loadConfiguration
				};
			});
		},
		layout(): ImModelLayout
		{
			return this.$store.getters['application/getLayout'];
		},
		canShowMarket(): boolean
		{
			return Core.isCloud();
		}
	},
	methods:
	{
		onMarketClick()
		{
			MarketManager.openMarketplace();
		},
		onMarketItemClick(item: MarketMenuItem)
		{
			this.$emit('clickMarketItem', {
				layoutName: Layout.market.name,
				layoutEntityId: item.id
			});
		},
		getMenuItemClasses(item: MarketMenuItem)
		{
			return {
				'--selected': this.isItemSelected(item.id),
				'--active': item.active
			};
		},
		isItemSelected(itemId: string): boolean
		{
			return this.layout.name === Layout.market.name && this.layout.entityId === itemId;
		},
		getIconClassNames(item: MarketMenuItem): string
		{
			return item.iconName.toString();
		}
	},
	template: `
		<div
			v-if="canShowMarket"
			@click="onMarketClick"
			class="bx-im-navigation__item_container"
		>
			<div class="bx-im-navigation__item --active">
				<div class="bx-im-navigation__item_icon --market"></div>
				<div class="bx-im-navigation__item_text" :title="$Bitrix.Loc.getMessage('IM_NAVIGATION_MARKET_TITLE')">
					{{ $Bitrix.Loc.getMessage('IM_NAVIGATION_MARKET_TITLE') }}
				</div>
			</div>
		</div>
		<div
			v-for="item in marketMenuItems"
			@click="onMarketItemClick(item)"
			class="bx-im-navigation__item_container"
		>
			<div :class="getMenuItemClasses(item)" class="bx-im-navigation__item">
				<div class="bx-im-navigation__market-item_icon-container">
					<i 
						class="bx-im-navigation__market-item_icon fa" 
						:class="getIconClassNames(item)" 
						aria-hidden="true"
					></i>
				</div>
				<div class="bx-im-navigation__item_text" :title="item.text">{{item.text}}</div>
			</div>
		</div>
	`
};