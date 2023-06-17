import {TabsColorScheme, MessengerPopup, MessengerTabs} from 'im.v2.component.elements';
import {PlacementType} from 'im.v2.const';
import {MarketManager} from 'im.v2.lib.market';
import {SmilePopupContent} from './smile-popup-content';
import {SmilePopupMarketContent} from './smile-popup-market-content';

import '../../css/smile-selector/smile-popup.css';

import type {PopupOptions} from 'main.popup';
import type {ImModelMarketApplication} from 'im.v2.model';

const TabType = Object.freeze({
	default: 'default',
	market: 'market'
});

type Tab = {
	id: number,
	title: string,
	type: $Values<typeof TabType>;
}

// @vue/component
export const SmilePopup = {
	name: 'SmilePopup',
	components:
	{
		MessengerPopup, SmilePopupContent, SmilePopupMarketContent, MessengerTabs
	},
	props:
	{
		bindElement: {
			type: Object,
			required: true
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['close'],
	data()
	{
		return {
			currentTab: TabType.default,
			currentEntityId: ''
		};
	},
	computed:
	{
		TabsColorScheme: () => TabsColorScheme,
		TabType: () => TabType,
		popupConfig(): PopupOptions
		{
			return {
				width: 320,
				bindElement: this.bindElement,
				bindOptions: {
					position: 'top'
				},
				offsetTop: 25,
				offsetLeft: -110,
				padding: 0
			};
		},
		marketMenuItems(): ImModelMarketApplication[]
		{
			return MarketManager.getInstance().getAvailablePlacementsByType(PlacementType.smilesSelector, this.dialogId);
		},
		tabs(): Tab[]
		{
			return [
				this.smilesTab,
				...this.marketTabs
			];
		},
		smilesTab(): Tab
		{
			return {
				id: 1,
				title: this.$Bitrix.Loc.getMessage('IM_TEXTAREA_SMILE_SELECTOR_SMILES_TAB'),
				type: TabType.default
			};
		},
		marketTabs(): Tab[]
		{
			return this.marketMenuItems.map(marketItem => {
				return {
					id: marketItem.id,
					title: marketItem.title,
					type: TabType.market
				};
			});
		}
	},
	methods:
	{
		tabSelect(tab: Tab)
		{
			this.currentTab = tab.type;
			this.currentEntityId = tab.id;
		}
	},
	template: `
		<MessengerPopup
			:config="popupConfig"
			@close="$emit('close')"
			id="im-smiles-popup"
		>
			<div class="bx-im-smile-popup__container bx-im-smile-popup__scope">
				<div class="bx-im-smile-popup__tabs-container">
					<MessengerTabs :colorScheme="TabsColorScheme.gray" :tabs="tabs" @tabSelect="tabSelect"  />
				</div>
				<SmilePopupContent v-if="currentTab === TabType.default" />
				<SmilePopupMarketContent v-else :entityId="currentEntityId" :dialogId="dialogId" />
			</div>
		</MessengerPopup>
	`
};