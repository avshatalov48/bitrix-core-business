import {BitrixVue} from 'ui.vue';
import {VuexBuilder} from 'ui.vue.vuex';
import {Loc, Type, Text, Tag, ajax, Extension} from 'main.core';
import 'ui.notification';
import {ProductList} from './models/product-list';
import {config} from "./config";
import './templates/form';
import './component.css';
import {EventEmitter} from "main.core.events";
import {CurrencyCore} from "currency.currency-core";
import type {FormOption} from "./types/form-option";
import {FormElementPosition} from "./types/form-element-position";
import {DiscountType} from "catalog.product-calculator";
import {FormInputCode} from "./types/form-input-code";
import type {BasketItemScheme} from "./types/basket-item-scheme";
import {FormErrorCode} from "./types/form-error-code";
import {FormMode} from "./types/form-mode";
import {FormCompilationType} from "./types/form-compilation-type";

export class ProductForm
{
	constructor(options: FormOption = {})
	{
		this.options = this.prepareOptions(options);
		this.defaultOptions = Object.assign({}, this.options);

		this.editable = true;
		this.#setMode(FormMode.REGULAR);

		this.wrapper = Tag.render`<div class=""></div>`;

		if (Text.toNumber(options.iblockId) <= 0)
		{
			return;
		}

		ProductForm.initStore()
			.then((result) => this.initTemplate(result))
			.catch((error) => ProductForm.showError(error))
		;
	}

	static initStore(): VuexBuilder
	{
		const builder = new VuexBuilder();

		return builder
			.addModel(ProductList.create())
			.build();
	}

	prepareOptions(options: FormOption = {}): FormOption
	{
		const settingsCollection = Extension.getSettings('catalog.product-form');
		const defaultOptions: FormOption = {
			basket: [],
			measures: [],
			iblockId: null,
			basePriceId: settingsCollection.get('basePriceId'),
			taxList: [],
			singleProductMode: false,
			showResults: true,
			showCompilationModeSwitcher: false,
			enableEmptyProductError: true,
			pricePrecision: 2,
			currency: settingsCollection.get('currency'),
			currencySymbol: settingsCollection.get('currencySymbol'),
			taxIncluded: settingsCollection.get('taxIncluded'),
			warehouseOption: settingsCollection.get('warehouseOption'),
			showDiscountBlock: settingsCollection.get('showDiscountBlock'),
			showTaxBlock: settingsCollection.get('showTaxBlock'),
			allowedDiscountTypes: [DiscountType.PERCENTAGE, DiscountType.MONETARY],
			visibleBlocks: [
				FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE,
				FormInputCode.QUANTITY, FormInputCode.RESULT,
				FormInputCode.DISCOUNT,
			],
			requiredFields: [],
			editableFields: [],
			newItemPosition: FormElementPosition.TOP,
			buttonsPosition: FormElementPosition.TOP,
			urlBuilderContext: 'SHOP',
			hideUnselectedProperties: false,
			isCatalogPriceEditEnabled: settingsCollection.get('isCatalogPriceEditEnabled'),
			isCatalogPriceSaveEnabled: settingsCollection.get('isCatalogPriceSaveEnabled'),
			fieldHints: settingsCollection.get('fieldHints'),
			compilationFormType: FormCompilationType.REGULAR,
			compilationFormOption: {},
		};

		if (options.visibleBlocks && !Type.isArray(options.visibleBlocks))
		{
			delete(options.visibleBlocks);
		}

		if (options.requiredFields && !Type.isArray(options.requiredFields))
		{
			delete(options.requiredFields);
		}

		options = {...defaultOptions, ...options};
		options.showTaxBlock = 'N';

		if (settingsCollection.get('isEnabledLanding'))
		{
			options.compilationFormOption = {
				type: options.compilationFormType,
				hasStore: settingsCollection.get('hasLandingStore'),
				isLimitedStore: settingsCollection.get('isLimitedLandingStore'),
				disabledSwitcher: settingsCollection.get('isLimitedLandingStore'),
				hiddenInfoMessage: settingsCollection.get('hiddenCompilationInfoMessage'),
			};
		}
		else
		{
			options.showCompilationModeSwitcher = false;
		}

		options.defaultDiscountType = '';
		if (Type.isArray(options.allowedDiscountTypes))
		{
			if (options.allowedDiscountTypes.includes(DiscountType.PERCENTAGE))
			{
				options.defaultDiscountType = DiscountType.PERCENTAGE;
			}
			else if (options.allowedDiscountTypes.includes(DiscountType.MONETARY))
			{
				options.defaultDiscountType = DiscountType.MONETARY;
			}
		}

		return options;
	}

	layout(): HTMLElement
	{
		return this.wrapper;
	}

	initTemplate(result): Promise
	{
		return new Promise((resolve) =>
		{
			const context = this;
			this.store = result.store;

			this.templateEngine = BitrixVue.createApp({
				el: this.wrapper,
				store: this.store,
				data: {
					options: this.options,
					mode: this.mode,
				},
				created()
				{
					this.$app = context;
				},
				mounted()
				{
					resolve();
				},
				template: `<${config.templateName} :options="options" :mode="mode"/>`,
			});

			if (Type.isStringFilled(this.options.currency))
			{
				this.setData({
					currency: this.options.currency
				});
				CurrencyCore.loadCurrencyFormat(this.options.currency);
			}

			if (this.options.basket.length > 0)
			{
				this.setData(
					{basket: this.options.basket,},
					{newItemPosition: FormElementPosition.BOTTOM}
				);

				if (Type.isObject(this.options.totals))
				{
					this.store.commit('productList/setTotal', this.options.totals);
				}
				else
				{
					this.store.dispatch('productList/calculateTotal');
				}
			}
			else
			{
				const newItem = this.store.getters['productList/getBaseProduct']();
				newItem.fields.discountType = this.options.defaultDiscountType;
				this.addProduct(newItem);
			}

			EventEmitter.emit(this, 'onAfterInit');
		});
	}

	addProduct(item = {}): void
	{
		this.store.dispatch('productList/addItem', {
			item,
			position: this.options.newItemPosition
		})
			.then(() => {
				this.#onBasketChange();
			});
	}

	#onBasketChange(): void
	{
		EventEmitter.emit(this, 'ProductForm:onBasketChange', {
			basket: this.store.getters['productList/getBasket']()
		});
	}

	emitErrorsChange(): void
	{
		EventEmitter.emit(this, 'ProductForm:onErrorsChange');
	}

	changeProduct(item): void
	{
		const product = item.product;
		product.errors = [];
		if (item.skipFieldChecking !== true)
		{
			const result = this.#checkRequiredFields(product.fields);
			product.errors = result?.errors || [];
		}

		this.store.dispatch('productList/changeItem', {
			index: item.index,
			product
		}).then(() => {
			this.#onBasketChange();
		});
	}

	#checkRequiredFields(fields: BasketItemScheme): {}
	{
		const result = {};
		if (!Type.isArray(this.options.requiredFields) || this.options.requiredFields.length === 0)
		{
			return result;
		}

		result.errors = [];
		this.options.requiredFields.forEach((code) => {
			switch (code)
			{
				case FormInputCode.PRICE:
					if (fields.price <= 0)
					{
						result.errors.push({
							code: FormErrorCode.EMPTY_PRICE,
							message: Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_PRICE'),
						});
					}
					break;
				case FormInputCode.QUANTITY:
					if (fields.quantity <= 0)
					{
						result.errors.push({
							code: FormErrorCode.EMPTY_QUANTITY,
							message: Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_QUANTITY'),
						});
					}
					break;
				case FormInputCode.BRAND:
					if (!Type.isArray(fields.brands) || fields.brands.length === 0)
					{
						result.errors.push({
							code: FormErrorCode.EMPTY_BRAND,
							message: Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_BRAND'),
						});
					}
					break;
			}
		});

		return result;
	}

	removeProduct(product): void
	{
		this.store.dispatch('productList/removeItem', {
			index: product.index
		}).then(() => {
			this.#onBasketChange();
		});
	}

	setData(data, option = {}): void
	{
		if (Type.isObject(data.basket))
		{
			const formBasket = this.store.getters['productList/getBasket']();
			data.basket.forEach((fields) => {
				if (!Type.isObject(fields))
				{
					return;
				}
				const itemPosition = option.newItemPosition || this.options.newItemPosition;

				const innerId = fields.selectorId;
				if (Type.isNil(innerId))
				{
					this.store.dispatch('productList/addItem', {
						item: fields,
						position: itemPosition
					});

					return;
				}

				const basketIndex = formBasket.findIndex(item => item.selectorId === innerId);
				if (basketIndex === -1)
				{
					this.store.dispatch('productList/addItem', {
						item: fields,
						position: itemPosition
					});
				}
				else
				{
					this.store.dispatch('productList/changeItem', {basketIndex, fields});
				}
			});
		}

		if (Type.isStringFilled(data.currency))
		{
			this.store.dispatch('productList/setCurrency', data.currency);
		}

		if (Type.isObject(data.total))
		{
			this.store.commit('productList/setTotal', {
				sum: data.total.sum,
				taxSum: data.total.taxSum,
				discount: data.total.discount,
				result: data.total.result,
			})
		}

		if (Type.isObject(data.errors))
		{
			this.store.commit('productList/setErrors', data.errors);
		}
	}

	changeFormOption(optionName, value): void
	{
		value = (value === 'Y') ? 'Y' : 'N';

		if (optionName === 'isCompilationMode')
		{
			if (!this.options.showCompilationModeSwitcher)
			{
				return;
			}

			const mode = (value === 'Y') ? FormMode.COMPILATION : FormMode.REGULAR;
			this.#changeCompilationModeSetting(mode);

			return;
		}

		this.options[optionName] = value;
		if (optionName !== 'hiddenCompilationInfoMessage')
		{
			const basket = this.store.getters['productList/getBasket']();
			basket.forEach((item, index) => {
				if (optionName === 'showDiscountBlock')
				{
					item.showDiscountBlock = value;
				}
				else if (optionName === 'showTaxBlock')
				{
					item.showTaxBlock = value;
				}
				else if (optionName === 'taxIncluded')
				{
					item.fields.taxIncluded = value;
				}

				this.store.dispatch('productList/changeItem', {
					index,
					fields: item
				});
			});
		}

		ajax.runAction(
			'catalog.productForm.setConfig',
			{
				data: {
					configName: optionName,
					value: value
				}
			}
		);
	}

	#changeCompilationModeSetting(mode: FormMode)
	{
		this.options.requiredFields = [];
		if (mode === FormMode.COMPILATION)
		{
			const compilationRequiredFields = [
				FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE, FormInputCode.BRAND,
			];
			this.options.requiredFields = this.options.visibleBlocks.filter(
				item => compilationRequiredFields.includes(item)
			);
		}

		this.#setMode(mode)

		const basket = this.store.getters['productList/getBasket']();

		basket.forEach((item, index) => this.changeProduct({
				index,
				product: item	,
				skipFieldChecking: (basket.length === 1 && index === 0 && item.offerId === null)
			})
		);
	}

	getTotal(): void
	{
		this.store.dispatch('productList/getTotal');
	}

	setEditable(value): void
	{
		this.editable = value;
		if (!value)
		{
			this.#setMode(FormMode.READ_ONLY);
		}
		else
		{
			this.#setMode(FormMode.REGULAR);
		}
	}

	#setMode(mode: FormMode): void
	{
		this.mode = mode;
		if (mode === FormMode.READ_ONLY)
		{
			this.options.editableFields = [];
		}
		else if (mode === FormMode.COMPILATION)
		{
			this.options.editableFields = [
				FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE, FormInputCode.BRAND,
			];

			this.options.visibleBlocks = this.defaultOptions.visibleBlocks;

			if (this.options.compilationFormType === FormCompilationType.FACEBOOK)
			{
				this.options.visibleBlocks.push(FormInputCode.BRAND);
			}

			this.options.showResults = false;
		}
		else
		{
			mode = FormMode.REGULAR;
			this.options.visibleBlocks = this.defaultOptions.visibleBlocks;
			this.options.showResults = this.defaultOptions.showResults;
			this.options.editableFields = this.defaultOptions.visibleBlocks;
		}

		if (this.templateEngine)
		{
			this.templateEngine.mode = mode;
		}

		EventEmitter.emit(this, 'ProductForm:onModeChange', { mode });
	}

	hasErrors()
	{
		if (!this.store)
		{
			return false;
		}

		const basket = this.store.getters['productList/getBasket']();
		const errorItems = basket.filter(
			item => item.errors.length > 0
		);

		return errorItems.length > 0;
	}

	static showError(error): void
	{
		console.error(error);
	}
}
