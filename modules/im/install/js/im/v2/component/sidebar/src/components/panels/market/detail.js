import { Type, Runtime } from 'main.core';

import { MarketManager } from 'im.v2.lib.market';
import { Spinner, SpinnerSize } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { EventEmitter } from 'main.core.events';

import { DetailHeader } from '../../elements/detail-header/detail-header';

import './detail.css';

import type { JsonObject } from 'main.core';
import type { ImModelMarketApplication } from 'im.v2.model';

// @vue/component
export const MarketPanel = {
	name: 'MarketPanel',
	components: { Spinner, DetailHeader },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		entityId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: true,
		};
	},
	computed:
	{
		SpinnerSize: () => SpinnerSize,
		SidebarDetailBlock: () => SidebarDetailBlock,
		placement(): ?ImModelMarketApplication
		{
			const placementId = Number.parseInt(this.entityId, 10);

			return this.$store.getters['market/getById'](placementId);
		},
		title(): string
		{
			if (this.placement && Type.isStringFilled(this.placement.title))
			{
				return this.placement.title;
			}

			return this.$Bitrix.Loc.getMessage('IM_SIDEBAR_MARKET_DETAIL_TITLE');
		},
	},
	created()
	{
		this.marketManager = MarketManager.getInstance();
	},
	async mounted()
	{
		const context = { dialogId: this.dialogId };
		const response = await this.marketManager.loadPlacement(this.entityId, context);
		this.isLoading = false;
		Runtime.html(this.$refs['im-messenger-sidebar-placement'], response);
	},
	methods:
	{
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.market });
		},
	},
	template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="title"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-market-detail__container">
				<div v-if="isLoading" class="bx-im-sidebar-market-detail__loader-container">
					<Spinner :size="SpinnerSize.S" />
				</div>
				<div 
					class="bx-im-sidebar-market-detail__placement-container" 
					ref="im-messenger-sidebar-placement"
				></div>
			</div>
		</div>
	`,
};
