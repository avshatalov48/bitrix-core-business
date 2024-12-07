import { EnableTrait } from './enable-trait';
import { ModeList } from './mode-list';
import { Type } from 'main.core';
import { ReplaceLangPhraseTrait } from './replace-lang-phrase-tait';

export const EnableB24 = {
	data() {
		return {
			costPriceMethod: '',
		};
	},
	mixins: [
		EnableTrait,
		ReplaceLangPhraseTrait,
	],
	computed: {
		popupTitle(): ?String
		{
			if (this.options.hasConductedDocumentsOrQuantities)
			{
				return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DATA_WILL_BE_DELETED_MSGVER_1');
			}

			return null;
		},
		popupTexts(): Array
		{
			const result = [];

			if (this.options.hasConductedDocumentsOrQuantities)
			{
				result.push({
					text: this.$Bitrix.Loc.getMessage(
						'CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_ENABLE_B24_MSGVER_1',
					),
					hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2'),
				});
			}

			return result;
		},
		startEnablingButtonClass(): Object
		{
			return {
				'ui-btn-clock': this.isEnabling && !this.isShownPopup,
				'ui-btn-disabled': !this.isFormValid,
			};
		},
		startEnablingButtonTitle(): ?string
		{
			if (this.isFormValid)
			{
				return null;
			}

			return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_B24_ENABLE_COST_PRICE_METHOD_REQUIRED');
		},
		isFormValid(): boolean
		{
			return Type.isStringFilled(this.costPriceMethod);
		},
	},
	methods: {
		startEnabling(): void
		{
			if (!this.isFormValid)
			{
				return;
			}

			if (this.options.isPlanRestricted)
			{
				top.BX.UI.InfoHelper.show('limit_store_inventory_management');

				return;
			}

			// enabling right away because we have nothing to warn about
			if (this.popupTexts.length === 0)
			{
				this.enable();

				return;
			}

			this.isShownPopup = true;
		},
		getMode(): string
		{
			return ModeList.MODE_B24;
		},
		getEnableOptions(): Object
		{
			return {
				costPriceCalculationMethod: this.costPriceMethod,
			};
		},
		getHelpLink(): string
		{
			return 'redirect=detail&code=17858278';
		},
	},
	template: `
		<div class="inventory-management__card-item --active --inner-field">
			<div class="inventory-management__card-item-inner">
				<div class="inventory-management__card-logo"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_TITLE')}}
				</div>
				<div 
					class="inventory-management__card-desc" 
					v-html="this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_1')"
				>
				</div>
				<div
					class="inventory-management__card-desc"
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_2')"
				>
				</div>
				<a
					@click="openHelp"
					href="#" class="inventory-management__card-link"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
				</a>
				<div class="inventory-management__card-select-field">
					<div class="inventory-management__card-select-title">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_SELECT_TITLE')}}
					</div>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select
							v-model="costPriceMethod"
							class="ui-ctl-element"
						>
							<option value="">
								{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_COST_PRICE_METHOD_NOT_SELECTED')}}
							</option>
							<option
								v-for="(name, value) in options.costPriceMethodList"
								:value="value"
								:key="value"
							>
								{{name}}
							</option>
						</select>
					</div>
				</div>
			</div>
			<div class="ui-btn-container inventory-management__card-footer">
				<button
					v-if="!options.isBlocked"
					@click="onBack"
					class="ui-btn ui-btn-light-border ui-btn-round ui-btn-lg"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_BACK')}}
				</button>
				<button
					@click="startEnabling"
					class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
					:class="startEnablingButtonClass"
					:title="startEnablingButtonTitle"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_START')}}
				</button>
			</div>
		</div>
		<popup-field
			@enable="enable"
			@cancel="isShownPopup = false"
			:isShown="isShownPopup"
			:isLoading="isEnabling"
			:title="popupTitle"
			:texts="popupTexts"
			:primaryButtonText="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT')"
		/>
	`,
};
