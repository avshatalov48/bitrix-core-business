import { Extension, Runtime } from 'main.core';
import { EnableTrait } from './enable-trait';
import { ModeList } from './mode-list';
import { Service } from './service';
import { ReplaceLangPhraseTrait } from './replace-lang-phrase-tait';
import { OneCPlanRestrictionSlider } from 'catalog.tool-availability-manager';

const OTHER_VERSION = 'OTHER';

export const EnableOnec = {
	mixins: [
		EnableTrait,
		ReplaceLangPhraseTrait,
	],
	data() {
		return {
			isAppInstalled: false,
			isAppStatusChecking: false,
			version: Object.keys(this.options.versionList)[0],
			isDemoEnabledFromSlider: false,
		};
	},
	computed: {
		isLoading(): boolean
		{
			return this.isEnabling || this.isAppStatusChecking;
		},
		popupPrimaryButtonText(): string
		{
			return this.$Bitrix.Loc.getMessage(
				this.popupTexts > 0
					? 'CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT'
					: 'CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT_2',
			);
		},
		popupTitle(): string
		{
			if (this.options.hasConductedDocumentsOrQuantities)
			{
				if (this.options.areTherePublishedShops)
				{
					return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_1');
				}

				return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_2');
			}

			if (this.options.areTherePublishedShops)
			{
				return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_3');
			}

			if (this.options.areThereActiveProducts)
			{
				return this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TITLE_4');
			}

			return '';
		},
		popupTexts(): Array
		{
			const result = [];

			if (this.options.hasConductedDocumentsOrQuantities)
			{
				if (this.options.currentMode === ModeList.MODE_B24)
				{
					if (
						this.options.areTherePublishedShops
						&& this.options.areThereActiveProducts
					)
					{
						result.push(
							{
								text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_1'),
								hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2'),
							},
						);
					}
					else
					{
						result.push(
							{
								text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_1'),
							},
							{
								text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_11'),
								hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2'),
							},
						);
					}
				}
				else
				{
					result.push({
						text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_2'),
						hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT_2'),
					});
				}
			}

			if (this.options.areTherePublishedShops)
			{
				result.push({
					text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_3'),
				});
			}

			if (this.options.areThereActiveProducts)
			{
				result.push({
					text: this.replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_4'),
					hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_1C_POPUP_WARNING_TEXT_4_HINT'),
				});
			}

			return result;
		},
		startEnablingButtonClass(): Object
		{
			return {
				'ui-btn-clock': (this.isLoading && !this.isShownPopup),
				'ui-btn-disabled': !this.isFormValid,
			};
		},
		isFormValid(): boolean
		{
			return true;
		},
	},
	methods: {
		startEnabling(): void
		{
			if (!this.isFormValid)
			{
				return;
			}

			if (this.options.isPlanRestricted && !this.isDemoEnabledFromSlider)
			{
				OneCPlanRestrictionSlider.show({
					onActivateSuccessHandler: () => {
						this.isDemoEnabledFromSlider = true;
						this.startEnabling();
					},
				});

				return;
			}

			// enabling right away because we have nothing to warn about
			if (this.popupTexts.length === 0)
			{
				this.onecEnable();

				return;
			}

			this.isShownPopup = true;
		},
		onecEnable(): void
		{
			this.checkIfOnecAppInstalled()
				.then(() => this.enableOrInstall())
				.catch((error) => console.error(error));
		},
		checkIfOnecAppInstalled(): Promise
		{
			this.isAppStatusChecking = true;

			return new Promise((resolve) => {
				Service.isOnecAppInstalled()
					.then((isInstalled) => {
						this.isAppInstalled = isInstalled;
					})
					.catch((error) => console.error(error))
					.finally(() => {
						this.isAppStatusChecking = false;
						resolve();
					});
			});
		},
		enableOrInstall(): void
		{
			if (this.isAppInstalled)
			{
				this.enable();
			}
			else
			{
				this.isShownPopup = false;

				BX.SidePanel.Instance.open(this.options.installUrl, {
					cacheable: false,
					allowChangeHistory: false,
					width: 1000,
					events: {
						onCloseComplete: () => {
							if (!this.isAppInstalled)
							{
								return;
							}

							this.enable();
						},
					},
				});

				top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', (installed, eventResult) => {
					this.isAppInstalled = Boolean(installed);
				});
			}
		},
		getModeLimitationTexts(): Array
		{
			return [
				{
					text: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LANDING_SHOP_PUBLICATION_WARNING'),
					hint: this.$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LANDING_SHOP_PUBLICATION_WARNING_HINT'),
				},
			];
		},
		getMode(): string
		{
			return ModeList.MODE_1C;
		},
		getEnableOptions(): Object
		{
			return {
				version: this.version,
			};
		},
		getHelpLink(): string
		{
			return 'redirect=detail&code=20233716';
		},
		openHelp(): void
		{
			if (top.BX && top.BX.Helper)
			{
				top.BX.Helper.show(this.getHelpLink());
			}
		},
	},
	watch: {
		version(newValue)
		{
			if (newValue === OTHER_VERSION)
			{
				Runtime.loadExtension(['ui.feedback.form'])
					.then(() => {
						BX.UI.Feedback.Form.open(
							{
								id: 'catalog-enable-wizard-1c-other-version',
								forms: [
									{ zones: ['ru', 'by', 'kz'], title: '', id: 704, lang: 'ru', sec: 'phfehj' },
								],
								presets: Extension.getSettings('catalog.store-enable-wizard')
									.get('feedbackFormOtherVersion1CPresets')
								,
							},
						);
					})
					.catch((error) => console.error(error));
			}
		},
	},
	template: `
		<div class="inventory-management__card-item --1c --active --inner-field">
			<div class="inventory-management__card-item-inner">
				<div class="inventory-management__card-logo"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_TITLE_1C')}}
				</div>
				<div class="inventory-management__card-desc">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_DESC_1C')}}
				</div>
				<a
					@click="openHelp"
					href="#" class="inventory-management__card-link"
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DETAILS')}}
				</a>
				<div class="inventory-management__card-select-field">
					<div class="inventory-management__card-select-title-wo-star">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_INNER_SELECT_TITLE_1C')}}
					</div>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select
							v-model="version"
							class="ui-ctl-element"
						>
							<option
								v-for="(name, value) in options.versionList"
								:value="value"
								:key="value"
							>
								{{name}}
							</option>
						</select>
					</div>
				</div>
				<enable-warning
					v-for="warning in getModeLimitationTexts()"
					:text="warning.text"
					:hint="warning.hint"
					:help-link="getHelpLink()"
				>
				</enable-warning>
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
				>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_NEXT')}}
				</button>
			</div>
		</div>
		<popup-field
			@enable="onecEnable"
			@cancel="isShownPopup = false"
			:isShown="isShownPopup"
			:isLoading="isLoading"
			:title="popupTitle"
			:texts="popupTexts"
			:primaryButtonText="popupPrimaryButtonText"
		/>
	`,
};
