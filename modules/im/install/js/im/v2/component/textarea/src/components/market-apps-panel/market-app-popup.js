import {Runtime} from 'main.core';

import {MessengerPopup, Spinner, SpinnerSize} from 'im.v2.component.elements';
import {MarketManager} from 'im.v2.lib.market';

import '../../css/market-apps-panel/market-app-popup.css';

import type {PopupOptions} from 'main.popup';

// @vue/component
export const MarketAppPopup = {
	name: 'MarketAppPopup',
	components: {MessengerPopup, Spinner},
	props:
	{
		bindElement: {
			type: Object,
			required: true
		},
		entityId: {
			type: String,
			required: true
		},
		width: {
			type: Number,
			required: true
		},
		height: {
			type: Number,
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
		SpinnerSize: () => SpinnerSize,
		popupConfig(): PopupOptions
		{
			return {
				width: this.width,
				height: this.height,
				bindElement: this.bindElement,
				bindOptions: {
					position: 'top'
				},
				offsetTop: 0,
				offsetLeft: 0,
				padding: 0
			};
		}
	},
	created()
	{
		this.marketManager = MarketManager.getInstance();
	},
	mounted()
	{
		const context = {dialogId: this.dialogId};
		this.marketManager.loadPlacement(this.entityId, context).then(response => {
			if (!this.handleResult)
			{
				return;
			}
			this.isLoading = false;
			Runtime.html(this.$refs['im-messenger-textarea-placement'], response);
		});
	},
	methods:
	{
		onClose()
		{
			this.handleResult = false;
			this.$emit('close');
		}
	},
	template: `
		<MessengerPopup
			:config="popupConfig"
			@close="onClose"
			id="im-market-app-popup"
		>
			<div class="bx-im-market-app-popup__container">
				<div v-if="isLoading" class="bx-im-market-app-popup__loader-container">
					<Spinner :size="SpinnerSize.S"/>
				</div>
				<div ref="im-messenger-textarea-placement" class="bx-im-market-app-popup__placement-container"></div>
			</div>
		</MessengerPopup>
	`
};