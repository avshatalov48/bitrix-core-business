import {Runtime} from 'main.core';
import {MarketManager} from 'im.v2.lib.market';
import {Spinner, SpinnerSize} from 'im.v2.component.elements';

import './market-content.css';

// @vue/component
export const MarketContent = {
	name: 'MarketContent',
	components: {Spinner},
	props:
	{
		entityId: {
			type: String,
			required: true
		}
	},
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
	beforeUnmount()
	{
		this.marketManager.unloadPlacement(this.entityId);
		this.handleResult = false;
	},
	created()
	{
		this.marketManager = MarketManager.getInstance();
	},
	mounted()
	{
		this.load(this.entityId);
	},
	methods:
	{
		load(placementId: string)
		{
			this.marketManager.loadPlacement(placementId)
				.then((response) => {
					if (!this.handleResult || this.entityId !== placementId)
					{
						return;
					}
					Runtime.html(this.$refs['im-messenger-placement'], response);
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
	},
	template: `
		<div class="bx-content-market__container">
			<div v-if="isLoading" class="bx-content-market__loader-container">
				<Spinner :size="SpinnerSize.L" />
			</div>
			<div 
				v-show="!isLoading" 
				class="bx-content-market__placement-container" 
				ref="im-messenger-placement"
			></div>
		</div>
	`
};