import {MessengerPopup} from 'im.v2.component.elements';
import {MarketShowMorePopupContent} from './market-show-more-popup-content';

import '../../css/market-apps-panel/market-show-more-popup.css';

import type {PopupOptions} from 'main.popup';

// @vue/component
export const MarketShowMorePopup = {
	name: 'MarketShowMorePopup',
	components: {MessengerPopup, MarketShowMorePopupContent},
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
	emits: ['close'],
	data() {
		return {
			showPopup: false
		};
	},
	computed:
	{
		popupConfig(): PopupOptions
		{
			return {
				titleBar: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MARKET_OTHER_APPS'),
				closeIcon: true,
				width: 302,
				height: 422,
				bindElement: this.$refs['textarea-show-more-market-apps'],
				bindOptions: {
					position: 'top'
				},
				offsetTop: 0,
				offsetLeft: 0,
				padding: 0,
				contentPadding: 0,
				contentBackground: '#fff',
				className: 'bx-im-market-show-more-popup__scope',
			};
		},
		showMoreButtonText(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_TEXTAREA_MARKET_APPS_SHOW_MORE_BUTTON')
				.replace('#NUMBER#', this.marketApps.length);
		},
	},
	template: `
		<div
			@click="showPopup = true"
			class="bx-im-market-apps-panel__more-items-button"
			:class="{'--active': showPopup}"
			ref="textarea-show-more-market-apps"
		>
			{{ showMoreButtonText }}
		</div>
		<MessengerPopup
			v-if="showPopup"
			:config="popupConfig"
			@close="showPopup = false"
			id="im-market-apps-more-popup"
		>
			<MarketShowMorePopupContent :marketApps='marketApps' :dialogId="dialogId" @close="showPopup = false" />
		</MessengerPopup>
	`
};