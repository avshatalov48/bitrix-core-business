import {Runtime} from 'main.core';

import {Spinner, SpinnerSize} from 'im.v2.component.elements';
import {MarketManager} from 'im.v2.lib.market';

import '../../../../css/smile-selector/tabs/tab-market.css';

// @vue/component
export const TabMarket = {
	name: 'SmilePopupMarketContent',
	components: {Spinner},
	props:
	{
		entityId: {
			type: String,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['close'],
	data() {
		return {
			isLoading: true,
			handleResult: true
		};
	},
	computed:
	{
		SpinnerSize: () => SpinnerSize
	},
	watch:
	{
		entityId(newValue: string)
		{
			this.isLoading = true;
			this.load(newValue);
		}
	},
	created()
	{
		this.marketManager = MarketManager.getInstance();
	},
	mounted()
	{
		this.load(this.entityId);
	},
	beforeUnmount()
	{
		this.handleResult = false;
	},
	methods:
	{
		load(placementId: string)
		{
			const context = {dialogId: this.dialogId};
			this.marketManager.loadPlacement(placementId, context)
				.then((response) => {
					if (!this.handleResult || this.entityId !== placementId)
					{
						return;
					}
					Runtime.html(this.$refs['im-messenger-smile-selector-placement'], response);
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
		onClose()
		{
			this.handleResult = false;
			this.$emit('close');
		}
	},
	template: `
		<div class="bx-im-smile-popup-market-content__container">
			<div v-if="isLoading" class="bx-im-smile-popup-market-content__loader-container">
				<Spinner :size="SpinnerSize.S"/>
			</div>
			<div 
				v-show="!isLoading"
				class="bx-im-smile-popup-market-content__placement-container"
				ref="im-messenger-smile-selector-placement"
			></div>
		</div>
	`
};