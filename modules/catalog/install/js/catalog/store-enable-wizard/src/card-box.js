import { CardBoxB24 } from './card-box-b24';
import { CardBoxB24Solo } from './card-box-b24-solo';
import { CardBoxOnec } from './card-box-onec';
import { EnableB24 } from './enable-b24';
import { EnableOnec } from './enable-onec';
import { TitleBox } from './title-box';
import { ModeList } from './mode-list';

import 'ui.icon-set.main';
import 'ui.icon-set.crm';
import 'ui.icon-set.actions';

import { Type } from 'main.core';

export const InventoryCardBox = {
	created()
	{
		if (this.initEnableMode)
		{
			this.startEnable(this.initEnableMode);
		}
	},
	mounted()
	{
		if (this.enableMode === null)
		{
			this.$Bitrix.Application.instance.sendOpenedEvent();
		}
	},
	props: {
		initEnableMode: {
			type: String,
			required: false,
			default: null,
		},
		availableModes: {
			type: Object,
			required: true,
		},
		currentMode: {
			type: String,
			required: true,
		},
		hasConductedDocumentsOrQuantities: {
			type: Boolean,
			required: true,
		},
		areTherePublishedShops: {
			type: Boolean,
			required: true,
		},
		areThereActiveProducts: {
			type: Boolean,
			required: true,
		},
		inventoryManagementSource: {
			type: String,
			required: false,
			default: '',
		},
	},
	data() {
		return {
			enableMode: null,
			hoveredMode: null,
		};
	},
	components: {
		CardBoxB24,
		CardBoxB24Solo,
		CardBoxOnec,
		EnableB24,
		EnableOnec,
		TitleBox,
	},
	computed: {
		b24Mode(): string
		{
			return ModeList.MODE_B24;
		},
		onecMode(): string
		{
			return ModeList.MODE_1C;
		},
		titleBoxOptions(): ?Object
		{
			if (this.enableMode)
			{
				if (this.enableMode === ModeList.MODE_1C)
				{
					return {
						title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE_ENABLE_1C'),
					};
				}

				return {
					title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE_ENABLE_B24'),
					subTitle: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_SUBTITLE_ENABLE_B24'),
				};
			}

			if (!this.isAvailable(ModeList.MODE_1C))
			{
				return null;
			}

			return {
				title: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_TITLE'),
				subTitle: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_SLIDER_SUBTITLE'),
			};
		},
	},
	methods: {
		isHovered(mode: string): boolean
		{
			if (mode === this.b24Mode)
			{
				return (
					this.hoveredMode === mode
					|| (
						!this.hoveredMode
						&& (
							this.currentMode === mode
							|| !this.currentMode
						)
					)
				);
			}

			return (
				this.hoveredMode === mode
				|| (
					!this.hoveredMode
					&& this.currentMode === mode
				)
			);
		},
		isAvailable(mode: string): boolean
		{
			return Boolean(this.availableModes[mode]);
		},
		isActive(mode: string): boolean
		{
			return this.currentMode === mode;
		},
		isEnabling(mode: string): boolean
		{
			return this.enableMode === mode;
		},
		getEnableOptions(mode: string): Object
		{
			const result = this.availableModes[mode] ?? {};

			result.currentMode = this.currentMode;
			result.isBlocked = Type.isStringFilled(this.initEnableMode);
			result.inventoryManagementSource = this.inventoryManagementSource;
			result.hasConductedDocumentsOrQuantities = this.hasConductedDocumentsOrQuantities;
			result.areTherePublishedShops = this.areTherePublishedShops;
			result.areThereActiveProducts = this.areThereActiveProducts;

			return result;
		},
		discardEnable(): void
		{
			if (this.initEnableMode)
			{
				return;
			}

			this.enableMode = null;
		},
		enableB24(): void
		{
			this.startEnable(ModeList.MODE_B24);
		},
		enableOnec()
		{
			this.startEnable(ModeList.MODE_1C);
		},
		startEnable(mode: string): void
		{
			if (this.currentMode === mode)
			{
				return;
			}

			this.$Bitrix.Application.instance.sendStep2ProceededEvent(mode);

			this.enableMode = mode;
		},
		onCardBoxEnter(mode: string)
		{
			this.hoveredMode = mode;
		},
		onCardBoxLeave()
		{
			this.hoveredMode = null;
		},
	},
	template: `
		<title-box :options="titleBoxOptions"></title-box>
		<div v-if="enableMode" class="inventory-management__inner">
			<enable-b24
				v-if="isEnabling(b24Mode)"
				:options="getEnableOptions(b24Mode)"
				@back="discardEnable"
			>
			</enable-b24>
			<enable-onec
				v-if="isEnabling(onecMode)"
				:options="getEnableOptions(onecMode)"
				@back="discardEnable"
			>
			</enable-onec>
		</div>
		<template v-else>
			<div
				v-if="isAvailable(onecMode)"
				class="inventory-management__card-box"
			>
				<card-box-b24
					:isActive="isActive(b24Mode)"
					:isHovered="isHovered(b24Mode)"
					@pick="enableB24"
					@enter="onCardBoxEnter(b24Mode)"
					@leave="onCardBoxLeave()"
				>
				</card-box-b24>
				<card-box-onec
					:isActive="isActive(onecMode)"
					:isHovered="isHovered(onecMode)"
					@pick="enableOnec"
					@enter="onCardBoxEnter(onecMode)"
					@leave="onCardBoxLeave()"
				>
				</card-box-onec>
			</div>
			<card-box-b24-solo
				v-else
				:isActive="isActive(b24Mode)"
				:isHovered="isHovered(b24Mode)"
				@pick="enableB24"
			>
			</card-box-b24-solo>
		</template>
	`,
};
