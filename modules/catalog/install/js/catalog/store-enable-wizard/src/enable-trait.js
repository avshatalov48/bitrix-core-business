import { Text } from 'main.core';
import { EnableWarning } from './enable-warning';
import { IconHint } from './icon-hint';
import { PopupField } from './popup-field';
import { Service } from './service';

import 'ui.forms';
import 'ui.buttons';

export const EnableTrait = {
	data() {
		return {
			isShownPopup: false,
			isEnabling: false,
		};
	},
	props: {
		options: {
			type: Object,
			required: true,
		},
	},
	components: {
		IconHint,
		EnableWarning,
		PopupField,
	},
	methods: {
		onBack()
		{
			this.$emit('back');
		},
		/**
		 * @abstract
		 */
		getMode(): string
		{
			throw new Error('Abstract method "getMode" must be implemented');
		},
		getEnableOptions(): Object
		{
			return {};
		},
		onEnableSuccess(): void
		{
			this.$Bitrix.Application.instance.sendEnableDoneEvent(this.getMode(), 'success');

			const slider = BX.SidePanel.Instance.getTopSlider();
			if (slider)
			{
				slider.getData().set('isInventoryManagementEnabled', true);
				slider.getData().set('inventoryManagementMode', this.getMode());
				slider.close();
			}
		},
		onEnableError(error): void
		{
			this.$Bitrix.Application.instance.sendEnableDoneEvent(
				this.getMode(),
				`error_${error?.customData?.analyticsCode ?? 'unknown'}`,
			);

			top.BX.UI.Notification.Center.notify({ content: Text.encode(error.message) });
		},
		enable(): void
		{
			if (this.isEnabling)
			{
				return;
			}

			this.isEnabling = true;

			this.$Bitrix.Application.instance.sendEnableProceededEvent(this.getMode());

			Service.enable({
				analyticsLabel: this.makeAnalyticsData(),
				data: {
					mode: this.getMode(),
					options: this.getEnableOptions(),
				},
			})
				.then(() => this.onEnableSuccess())
				.catch((error) => this.onEnableError(error))
				.finally(() => {
					this.isEnabling = false;
					this.isShownPopup = false;
				});
		},
		makeAnalyticsData(): Object
		{
			const result = {
				iME: 'inventoryManagementEnabled',
			};
			if (this.options.inventoryManagementSource)
			{
				result.inventoryManagementSource = this.options.inventoryManagementSource;
			}

			return result;
		},
		openHelp(): void
		{
			if (top.BX && top.BX.Helper)
			{
				top.BX.Helper.show(this.getHelpLink());
			}
		},
		/**
		 * @abstract
		 */
		getHelpLink(): string
		{
			throw new Error('Abstract method "getHelpLink" must be implemented');
		},
	},
};
