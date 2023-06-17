import {Runtime} from 'main.core';

import {MarketManager} from 'im.v2.lib.market';
import {Spinner, SpinnerSize} from 'im.v2.component.elements';

import '../../css/market/detail.css';

// @vue/component
export const MarketDetail = {
	name: 'MarketDetail',
	components: {Spinner},
	props: {
		detailBlockEntityId: {
			type: String,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	data() {
		return {
			isLoading: true
		};
	},
	computed:
	{
		SpinnerSize: () => SpinnerSize
	},
	created()
	{
		this.marketManager = MarketManager.getInstance();
	},
	mounted()
	{
		const context = {dialogId: this.dialogId};
		this.marketManager.loadPlacement(this.detailBlockEntityId, context).then(response => {
			this.isLoading = false;
			Runtime.html(this.$refs['im-messenger-sidebar-placement'], response);
		});
	},
	template: `
		<div class="bx-im-sidebar-market-detail__container">
			<div v-if="isLoading" class="bx-im-sidebar-market-detail__loader-container">
				<Spinner :size="SpinnerSize.S" />
			</div>
			<div ref="im-messenger-sidebar-placement"></div>
		</div>
		
	`
};