import { ModeList } from 'catalog.store-enable-wizard';
import { ajax as Ajax, Dom, Event, Loc, Tag, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button, ButtonColor } from 'ui.buttons';
import { BaseSettingsPage, SettingsSection } from 'ui.form-elements.field';
import ProductUpdater from './product-updater/template';
import CostPriceCalculation from './sections/cost-price-calculation';
import Mode from './sections/mode';
import Products from './sections/products';
import ReservationSection from './sections/reservation';
import './style.css';

class CatalogPage extends BaseSettingsPage
{
	#productUpdaterPopup = null;
	#initialData: Object = {};
	#slider = null;

	constructor()
	{
		super();
		this.titlePage = Loc.getMessage('CAT_CONFIG_SETTINGS_TITLE');
		this.descriptionPage = Loc.getMessage('CAT_CONFIG_SETTINGS_DESCRIPTION');

		EventEmitter.subscribe(
			EventEmitter.GLOBAL_TARGET,
			'button-click',
			(event) => {
				this.#save();
			},
		);

		this.#slider = BX.SidePanel.Instance.getTopSlider();
	}

	// temporary methods, to be removed after the page is integrated with the intranet settings
	static init(settings: Object): CatalogPage
	{
		const page = new CatalogPage();
		page.setData(settings);
		page.#initialData = settings;

		const permission = Boolean(settings.hasAccessToCatalogSettings) || Boolean(settings.hasAccessToReservationSettings);
		page.setPermission({
			canRead: () => permission,
			canEdit: () => permission,
		});

		return page;
	}

	#getDataForSaving(): Object
	{
		return BX.ajax.prepareForm(this.getFormNode()).data;
	}

	// this method will probably need to be overridden to include the saveProductSettings part
	#save()
	{
		const isNegativeBalancePopupShown = this.#showNegativeBalancePopupIfNeeded();
		if (isNegativeBalancePopupShown)
		{
			return;
		}

		this.#saveProductSettings().then(() => {
			const data = this.#getDataForSaving();

			return Ajax.runComponentAction(
				'bitrix:catalog.config.settings',
				'save',
				{
					mode: 'class',
					data: {
						data,
					},
				},
			);
		}).then(this.#onSaveSuccess.bind(this));
	}

	#resetSaveButton()
	{
		const saveButton = document.getElementById('ui-button-panel-save');
		Dom.removeClass(saveButton, 'ui-btn-wait');
	}

	onChange()
	{
		BX.UI.ButtonPanel.show();
	}

	#onSaveSuccess()
	{
		BX.UI.ButtonPanel.hide();
		this.#resetSaveButton();
		this.updateDataAfterSave();
		BX.SidePanel.Instance.postMessage(window, 'BX.Crm.Config.Catalog:onAfterSaveSettings');
	}
	// end temporary methods

	#saveProductSettings(): Promise
	{
		if (!this.#didProductSettingsChange())
		{
			return Promise.resolve();
		}

		const newData = this.#getDataForSaving();

		const productUpdaterOptions = {
			settings: {
				default_quantity_trace: newData.defaultQuantityTrace,
				default_can_buy_zero: newData.defaultCanBuyZero,
				default_subscribe: newData.defaultSubscribe,
			},
		};

		return new Promise((resolve) => {
			productUpdaterOptions.onComplete = () => {
				resolve();
				if (this.#needProgressBarOnProductsUpdating())
				{
					this.#productUpdaterPopup.destroy();
				}
			};

			const productUpdater = (new ProductUpdater(productUpdaterOptions)).render();

			if (this.#needProgressBarOnProductsUpdating())
			{
				this.#productUpdaterPopup = new Popup({
					content: productUpdater,
					width: 310,
					overlay: true,
					padding: 17,
					animation: 'fading-slide',
					angle: false,
				});
				this.#productUpdaterPopup.show();
			}
		});
	}

	#didProductSettingsChange(): boolean
	{
		const newData = this.#getDataForSaving();

		const affectedSettings = [
			'defaultQuantityTrace',
			'defaultCanBuyZero',
			'defaultSubscribe',
			'checkRightsOnDecreaseStoreAmount',
		];

		const productSettingsResult = affectedSettings.find((code) => {
			return newData[code] !== undefined && newData[code] !== this.getValue(code);
		});
		const costPriceCalculationMethodResult = newData.costPriceCalculationMethod !== undefined
			&& newData.costPriceCalculationMethod !== this.getValue('costPriceCalculationMethod').current
		;

		return Boolean(productSettingsResult) || costPriceCalculationMethodResult;
	}

	#needProgressBarOnProductsUpdating(): boolean
	{
		return this.getValue('productsCount') > 500;
	}

	getType(): string
	{
		return 'catalog';
	}

	appendSections(contentNode: HTMLElement)
	{
		if (this.#isReservationUsed() && this.getValue('hasAccessToReservationSettings'))
		{
			const reservationSection = this.#buildReservationSection();
			reservationSection.renderTo(contentNode);
		}

		if (this.#isStoreBatchUsed() && this.getValue('hasAccessToCatalogSettings'))
		{
			const costPriceCalculationSection = this.#buildCostPriceCalculationSection();
			costPriceCalculationSection.renderTo(contentNode);
		}

		if (this.getValue('hasAccessToCatalogSettings'))
		{
			const productsSection = this.#buildProductsSection();
			productsSection.renderTo(contentNode);

			const modeSection = this.#buildModeSection();
			modeSection.renderTo(contentNode);
		}
	}

	#buildReservationSection(): SettingsSection
	{
		const storeControlMode = this.getValue('storeControlMode');
		const reservationEntities = this.getValue('reservationEntities');

		for (const reservationEntity of reservationEntities)
		{
			for (const schemeItem of reservationEntity.settings.scheme)
			{
				if (['mode', 'period'].includes(schemeItem.code))
				{
					schemeItem.disabled = storeControlMode === ModeList.MODE_1C;
				}
			}

			if (storeControlMode === ModeList.MODE_1C)
			{
				reservationEntity.settings.values.mode = 'onAddToDocument';
			}
		}

		const reservationSection = new ReservationSection({
			parentPage: this,
			reservationEntities,
		});

		return reservationSection.buildSection();
	}

	#buildCostPriceCalculationSection(): SettingsSection
	{
		const costPriceCalculationSection = new CostPriceCalculation({
			parentPage: this,
			costPriceCalculationParams: this.getValue('costPriceCalculationMethod'),
		});

		return costPriceCalculationSection.buildSection();
	}

	#buildProductsSection(): SettingsSection
	{
		const values = {};
		[
			'defaultSubscribe',
			'isEnabledInventoryManagement',
			'costPriceCalculationMethod',
			'checkRightsOnDecreaseStoreAmount',
			'defaultProductVatIncluded',
			'defaultCanBuyZero',
			'defaultQuantityTrace',
			'canEnableProductCardSlider',
			'isBitrix24',
			'productCardSliderEnabled',
			'showNegativeStoreAmountPopup',
			'storeBalancePopupLink',
			'hasAccessToChangeCanBuyZero',
			'busProductCardHelpLink',
			'vats',
		].forEach((code) => {
			values[code] = this.getValue(code);
		});

		values.isReservationUsed = this.#isReservationUsed();

		const productsSection = new Products({
			parentPage: this,
			values,
		});

		return productsSection.buildSection();
	}

	#buildModeSection(): SettingsSection
	{
		const modeSection = new Mode({
			parentPage: this,
			inventoryManagementParams: {
				isEnabled: this.getValue('isEnabledInventoryManagement'),
				currentMode: this.getValue('storeControlMode'),
				availableModes: this.getValue('storeControlAvailableModes'),
				onecStatusUrl: this.getValue('onecStatusUrl'),
				is1cRestricted: this.getValue('is1cRestricted'),
				hasConductedDocumentsOrQuantities: this.getValue('hasConductedDocumentsOrQuantities'),
			},
			configCatalogSource: this.getValue('configCatalogSource'),
		});

		return modeSection.buildSection();
	}

	onInventoryManagementModeChanged({ isEnabled, mode }: { isEnabled: boolean, mode?: string }): void
	{
		if (this.#slider)
		{
			this.#slider.getData().set('isInventoryManagementChanged', true);
			if (mode)
			{
				this.#slider.getData().set('inventoryManagementMode', mode);
				if (mode === ModeList.MODE_1C)
				{
					this.#initialData.is1cRestricted = false;
				}
			}
		}
		this.#initialData.isEnabledInventoryManagement = isEnabled;
		if (mode && this.getValue('storeControlAvailableModes')?.includes(mode))
		{
			this.#initialData.storeControlMode = mode;
		}

		this.#initialData.defaultQuantityTrace = isEnabled ? 'Y' : 'N';

		this.setData(this.#initialData);
	}

	#showNegativeBalancePopupIfNeeded(): boolean
	{
		if (!this.#getDataForSaving().costPriceCalculationMethod || !this.getValue('showNegativeStoreAmountPopup'))
		{
			return false;
		}

		const text = Loc.getMessage(
			'CAT_CONFIG_SETTINGS_NEGATIVE_STORE_BALANCE_POPUP_TEXT',
			{
				'#STORE_BALANCE_LIST_LINK#': '<help-link></help-link>',
			},
		);

		const content = Tag.render`
			<div class="catalog-settings-popup-content">
				<div class="catalog-settings-popup-text">
					${text}
				</div>
			</div>
		`;

		if (!Type.isUndefined(top.BX.SidePanel.Instance) && Type.isStringFilled(this.getValue('storeBalancePopupLink')))
		{
			const balanceInfoLink = Tag.render`
				<a href="#" class="ui-form-link">
					${Loc.getMessage('CAT_CONFIG_SETTINGS_NEGATIVE_STORE_BALANCE_POPUP_LINK')}
				</a>
			`;

			Event.bind(balanceInfoLink, 'click', () => {
				top.BX.SidePanel.Instance.open(
					String(this.getValue('storeBalancePopupLink')),
					{
						requestMethod: 'post',
						cacheable: false,
					},
				);
			});

			Dom.replace(content.querySelector('help-link'), balanceInfoLink);
		}

		const popup = new Popup({
			id: 'catalog_settings_document_negative_balance_popup',
			content,
			overlay: true,
			buttons: [
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_RETURN'),
					color: ButtonColor.DANGER,
					onclick: (button, event) => {
						this.#resetSaveButton();
						popup.destroy();
					},
				}),
			],
		});
		popup.show();

		return true;
	}

	#isReservationUsed(): boolean
	{
		return this.getValue('isEnabledInventoryManagement')
			|| this.getValue('defaultQuantityTrace') === 'Y'
		;
	}

	#isStoreBatchUsed(): boolean
	{
		return this.getValue('isStoreBatchUsed')
			|| this.getValue('hasAccessToCatalogSettings')
		;
	}

	// reads the data from the form element and updates the page object's #data
	updateDataAfterSave()
	{
		this.setData(this.#convertFormDataToObjectData());
	}

	#convertFormDataToObjectData(): Object
	{
		const formData = this.#getDataForSaving();
		const objectData = this.#initialData;

		// reservation
		if (formData.reservationSettings)
		{
			formData.reservationSettings.deal.autoWriteOffOnFinalize = formData.reservationSettings.deal.autoWriteOffOnFinalize === 'Y';
			Object.assign(objectData.reservationEntities[0].settings.values, formData.reservationSettings.deal);
		}

		// cost price calculation
		if (formData.costPriceCalculationMethod)
		{
			objectData.costPriceCalculationMethod.items.forEach((item) => {
				item.selected = item.value === formData.costPriceCalculationMethod;
			});
		}

		// product settings
		if (formData.defaultProductVatId)
		{
			objectData.vats.items.forEach((item) => {
				item.selected = Number(item.value) === Number(formData.defaultProductVatId);
			});
		}

		const options = [
			'defaultSubscribe',
			'checkRightsOnDecreaseStoreAmount',
			'defaultProductVatIncluded',
			'defaultCanBuyZero',
			'defaultQuantityTrace',
			'productCardSliderEnabled',
		];
		options.forEach((option) => {
			if (formData[option])
			{
				objectData[option] = formData[option];
			}
		});

		return objectData;
	}
}

export default CatalogPage;
