import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button } from 'ui.buttons';
import { BaseSettingsPage, SettingsSection, SettingsRow, SettingsField } from 'ui.form-elements.field';
import { Checker, Selector } from 'ui.form-elements.view';

export default class Products
{
	#parentPage: BaseSettingsPage;
	#values: Object;

	constructor(params: Object)
	{
		this.#parentPage = params.parentPage;
		this.#values = params.values;
	}

	buildSection(): SettingsSection
	{
		const productsSection = new SettingsSection({
			parent: this.#parentPage,
			section: {
				title: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCTS_SECTION_TITLE'),
				titleIconClasses: 'ui-icon-set --cubes-3',
				isOpen: true,
			},
		});

		new SettingsRow({
			parent: productsSection,
			child: new SettingsField({
				fieldView: (new Checker({
					inputName: 'defaultSubscribe',
					title: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_SUBSCRIBE'),
					checked: this.#values.defaultSubscribe === 'Y',
				})),
			}),
		});

		const isInventoryManagementEnabled = this.#values.isEnabledInventoryManagement;
		const isEmptyCostPriceCalculationMethod = this.#values.costPriceCalculationMethod.current === '';

		const isCanBuyZeroInDocsVisible = isInventoryManagementEnabled && isEmptyCostPriceCalculationMethod;

		if (isCanBuyZeroInDocsVisible)
		{
			new SettingsRow({
				parent: productsSection,
				child: new SettingsField({
					fieldView: (new Checker({
						inputName: 'checkRightsOnDecreaseStoreAmount',
						title: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_IN_DOCS'),
						checked: this.#values.checkRightsOnDecreaseStoreAmount === 'Y',
						hintOn:
							Loc
								.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_IN_DOCS_HINT')
								.replace('[link]', '<a class="ui-section__link" onclick="top.BX.Helper.show(\'redirect=detail&code=15706692&anchor=products\')">')
								.replace('[/link]', '</a>')
						,
					})),
				}),
			});
		}

		new SettingsRow({
			parent: productsSection,
			child: new SettingsField({
				fieldView: (new Checker({
					inputName: 'defaultProductVatIncluded',
					title: Loc.getMessage('CAT_CONFIG_SETTINGS_SET_VAT_IN_PRICE_FOR_NEW_PRODUCTS'),
					checked: this.#values.defaultProductVatIncluded === 'Y',
				})),
			}),
		});

		const isDefaultCanBuyZeroVisible = this.#values.isReservationUsed && this.#values.hasAccessToChangeCanBuyZero;
		if (isDefaultCanBuyZeroVisible)
		{
			new SettingsRow({
				parent: productsSection,
				child: new SettingsField({
					fieldView: (new Checker({
						inputName: 'defaultCanBuyZero',
						title: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO'),
						checked: this.#values.defaultCanBuyZero === 'Y',
						hintOn: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_CAN_BUY_ZERO_HINT'),
					})),
				}),
			});
		}

		const initDefaultQuantityTrace = this.#values.defaultQuantityTrace;
		const isDefaultQuantityTraceVisible = initDefaultQuantityTrace === 'Y' && !isInventoryManagementEnabled;
		if (isDefaultQuantityTraceVisible)
		{
			const defaultQuantityTraceChecker = (new Checker({
				inputName: 'defaultQuantityTrace',
				title: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_QUANTITY_TRACE'),
				checked: this.#values.defaultQuantityTrace === 'Y',
			}));

			new SettingsRow({
				parent: productsSection,
				child: new SettingsField({
					fieldView: defaultQuantityTraceChecker,
				}),
			});

			EventEmitter.subscribe(
				defaultQuantityTraceChecker.switcher,
				'toggled',
				() => {
					if (defaultQuantityTraceChecker.isChecked())
					{
						return;
					}

					this.#showQuantityTracePopup();
				},
			);
		}

		if (this.#values.canEnableProductCardSlider)
		{
			const canEnableProductCardSliderChecker = (new Checker({
				inputName: 'productCardSliderEnabled',
				title: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD'),
				checked: this.#values.productCardSliderEnabled === 'Y',
			}));

			new SettingsRow({
				parent: productsSection,
				child: new SettingsField({
					fieldView: canEnableProductCardSliderChecker,
				}),
			});

			EventEmitter.subscribe(
				canEnableProductCardSliderChecker.switcher,
				'toggled',
				() => {
					if (!canEnableProductCardSliderChecker.isChecked())
					{
						return;
					}

					this.#showNewCardPopup(canEnableProductCardSliderChecker);
				},
			);
		}

		Object.keys(this.#values.vats.hints).forEach((hint) => {
			this.#values.vats.hints[hint] = this.#values.vats.hints[hint].replace('#MORE_DETAILS#', `
				<a class="ui-section__link"
					onclick="top.BX.Helper.show('redirect=detail&code=15706692&anchor=products')">${Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_HELP')}</a>
			`);
		});

		const vatSelector = new Selector({
			label: Loc.getMessage('CAT_CONFIG_SETTINGS_DEFAULT_VAT'),
			name: 'defaultProductVatId',
			items: this.#values.vats.items,
			hints: this.#values.vats.hints,
		});

		new SettingsRow({
			parent: productsSection,
			child: new SettingsField({
				fieldView: vatSelector,
			}),
		});

		return productsSection;
	}

	#showQuantityTracePopup()
	{
		const warnPopup = new Popup(null, null, {
			events: {
				onPopupClose: () => warnPopup.destroy(),
			},
			content: Tag.render`
				<div class="catalog-settings-popup-content">
					<h3>
						${Loc.getMessage('CAT_CONFIG_SETTINGS_TURN_OFF_QUANTITY_TRACE_TITLE')}
					</h3>
					<div class="catalog-settings-popup-text">
						${Loc.getMessage('CAT_CONFIG_SETTINGS_TURN_OFF_QUANTITY_TRACE_TEXT')}
					</div>
				</div>
			`,
			maxWidth: 500,
			overlay: true,
			buttons: [
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_CLOSE'),
					color: Button.Color.PRIMARY,
					onclick: () => warnPopup.close(),
				}),
			],
		});
		warnPopup.show();
	}

	#showNewCardPopup(checker: Checker)
	{
		const askPopup = this.#values.isBitrix24 === 'Y'
			? this.#createWarningProductCardPopupForBitrix24(checker)
			: this.#createWarningProductCardPopupForBUS(checker);

		askPopup.show();
	}

	#createWarningProductCardPopupForBitrix24(checker: Checker): Popup
	{
		const askPopup = this.#createWarningProductCardPopup(
			Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_TEXT'),
			[
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_DISAGREE'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						checker.switcher.toggle();
						askPopup.close();
					},
				}),
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_AGREE'),
					onclick: () => askPopup.close(),
				}),
			],
			{
				onPopupShow: () => {
					const helpdeskLink = document.getElementById('catalog-settings-new-productcard-popup-helpdesk');
					if (helpdeskLink)
					{
						Event.bind(helpdeskLink, 'click', () => top.BX.Helper.show('redirect=detail&code=11657084'));
					}
				},
			},
		);

		return askPopup;
	}

	#createWarningProductCardPopupForBUS(checker: Checker): Popup
	{
		const askPopup = this.#createWarningProductCardPopup(
			Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_BUS_TEXT').replace('#HELP_LINK#', this.#values.busProductCardHelpLink),
			[
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_AGREE'),
					color: Button.Color.SUCCESS,
					onclick: () => askPopup.close(),
				}),
				new Button({
					text: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_BUS_DISAGREE'),
					color: Button.Color.LINK,
					onclick: () => {
						checker.switcher.toggle();
						askPopup.close();
					},
				}),
			],
		);

		return askPopup;
	}

	#createWarningProductCardPopup(contentText: string, buttons: Array, events = {}): Popup
	{
		const popupParams = {
			events: {
				onPopupClose: () => askPopup.destroy(),
				...events,
			},
			content: Tag.render`
				<div class="catalog-settings-new-productcard-popup-content">
					${contentText}
				</div>
			`,
			className: 'catalog-settings-new-productcard-popup',
			titleBar: Loc.getMessage('CAT_CONFIG_SETTINGS_PRODUCT_CARD_ENABLE_NEW_CARD_ASK_TITLE'),
			maxWidth: 800,
			overlay: true,
			buttons,
		};

		const askPopup = new Popup(null, null, popupParams);

		return askPopup;
	}

	updateValues(newValues: Object): void
	{
		Object.assign(this.#values, newValues);
	}
}
