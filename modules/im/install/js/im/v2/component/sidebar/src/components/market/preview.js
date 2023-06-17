import {PlacementType, SidebarBlock, SidebarDetailBlock} from 'im.v2.const';

import {MarketPreviewItem} from './market-preview-item';

import '../../css/market/market-preview.css';

import type {ImModelMarketApplication} from 'im.v2.model';
import {MarketManager} from 'im.v2.lib.market';

// @vue/component
export const MarketPreview = {
	name: 'MarketPreview',
	components: {MarketPreviewItem},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
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
			this.$emit('openDetail', {
				block: SidebarBlock.meeting,
				detailBlock: SidebarDetailBlock.market,
				entityId: entityId,
			});
		}
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
			<MarketPreviewItem 
				v-for="item in marketMenuItems" 
				:key="item.id"
				:item="item"
				@click="onMarketItemClick(item.id)"
			/>
		</div>
	`
};