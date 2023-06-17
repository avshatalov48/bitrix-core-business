import {MarketAppPopup} from './market-app-popup';

import '../../css/market-apps-panel/market-app-item.css';

import type {ImModelMarketApplication} from 'im.v2.model';

// @vue/component
export const MarketAppItem = {
	name: 'MarketAppItem',
	components: {MarketAppPopup},
	props: {
		item: {
			type: Object,
			required: true
		},
		hideTitle: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data()
	{
		return {
			showApp: false
		};
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
		}
	},
	methods:
	{
		onAppClick()
		{
			this.showApp = !this.showApp;
		}
	},
	template: `
		<div 
			class="bx-im-market-app-item__container" 
			:class="{'--short': hideTitle}" 
			:title="marketItem.title"
			@click="onAppClick"
			ref="market-app"
		>
			<div class="bx-im-market-app-item__icon-container" :style="{backgroundColor: iconColor}">
				<i :class="iconClass" aria-hidden="true"></i>
			</div>
			<div v-if="!hideTitle" class="bx-im-market-app-item__title-container" :title="marketItem.title">
				<div class="bx-im-market-app-item__title-text">
					{{ marketItem.title }}
				</div>
			</div>
			<MarketAppPopup 
				v-if="showApp" 
				:bindElement="$refs['market-app']" 
				:entityId="marketItem.id"
				:width="marketItem.options.width"
				:height="marketItem.options.height"
				:dialogId="dialogId"
				@close="onAppClick"
			/>
		</div>
	`
};