import {MarketShowMorePopupContentItem} from './market-show-more-popup-content-item';

import '../../css/market-apps-panel/market-show-more-popup-content.css';

// @vue/component
export const MarketShowMorePopupContent = {
	name: 'MarketShowMorePopupContent',
	components: {MarketShowMorePopupContentItem},
	props:
	{
		marketApps: {
			type: Array,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			isLoading: true,
			needTopShadow: false,
			needBottomShadow: true
		};
	},
	methods:
	{
		onListScroll(event: Event)
		{
			this.needBottomShadow = event.target.scrollTop + event.target.clientHeight !== event.target.scrollHeight;

			if (event.target.scrollTop === 0)
			{
				this.needTopShadow = false;
				return;
			}

			this.needTopShadow = true;
		},
	},
	template: `
		<div class="bx-im-market-show-more-popup-content__scope bx-im-market-show-more-popup-content__container">
			<div v-if="needTopShadow" class="bx-im-market-show-more-popup-content__shadow --top">
				<div class="bx-im-market-show-more-popup-content__shadow-inner"></div>
			</div>
			<div @scroll="onListScroll" class="bx-im-market-show-more-popup-content__items-container">
				<MarketShowMorePopupContentItem
					v-for="item in marketApps"
					:item="item"
					:dialogId="dialogId"
					@onAppClick="$emit('close')"
				/>
			</div>
			<div v-if="needBottomShadow" class="bx-im-market-show-more-popup-content__shadow --bottom">
				<div class="bx-im-market-show-more-popup-content__shadow-inner"></div>
			</div>
		</div>
	`
};