import { EventType, PlacementType, SidebarDetailBlock } from 'im.v2.const';
import { EventEmitter } from 'main.core.events';

import { MarketItem } from './market-item';

import './css/market.css';

import type { ImModelMarketApplication } from 'im.v2.model';
import { MarketManager } from 'im.v2.lib.market';

// @vue/component
export const MarketPreview = {
	name: 'MarketPreview',
	components: { MarketItem },
	props: {
		isLoading: {
			type: Boolean,
			default: false,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['openDetail'],
	computed:
	{
		marketMenuItems(): ImModelMarketApplication[]
		{
			return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.sidebar, this.dialogId);
		},
	},
	methods:
	{
		onMarketItemClick(entityId)
		{
			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.market,
				dialogId: this.dialogId,
				entityId,
			});
		},
	},
	template: `
		<div v-if="!isLoading" class="bx-im-sidebar-market-preview__scope bx-im-sidebar-market-preview__container">
			<div class="bx-im-sidebar-market-preview__header_container">
				<div class="bx-im-sidebar-market-preview__title">
					<span class="bx-im-sidebar-market-preview__title-text">
						{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_MARKET_DETAIL_TITLE') }}
					</span>
				</div>
			</div>
			<MarketItem 
				v-for="item in marketMenuItems" 
				:key="item.id"
				:item="item"
				@click="onMarketItemClick(item.id)"
			/>
		</div>
	`,
};
