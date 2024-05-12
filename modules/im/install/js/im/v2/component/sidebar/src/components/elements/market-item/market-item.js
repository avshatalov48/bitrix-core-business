import './market-item.css';

import type { ImModelMarketApplication } from 'im.v2.model';

// @vue/component
export const MarketItem = {
	name: 'MarketItem',
	props: {
		item: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		marketItem(): ImModelMarketApplication
		{
			return this.item;
		},
		iconClass(): string
		{
			return `fa ${this.marketItem.options.iconName}`;
		},
		iconColor(): string
		{
			return this.marketItem.options.color;
		},
	},
	template: `
		<div class="bx-im-sidebar-market-preview-item__container bx-im-sidebar-market-preview-item__scope">
			<div class="bx-im-sidebar-market-preview-item__icon-container" :style="{backgroundColor: iconColor}">
				<i :class="iconClass" aria-hidden="true"></i>
			</div>
			<div class="bx-im-sidebar-market-preview-item__title-container" :title="marketItem.title">
				{{ marketItem.title }}
			</div>
		</div>
	`,
};
