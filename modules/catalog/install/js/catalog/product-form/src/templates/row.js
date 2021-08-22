import {config} from "../config";
import {Vue} from "ui.vue";
import {Text, Type, Runtime} from "main.core";

import "catalog.product-selector";
import "ui.common";
import "ui.alerts";
import {EventEmitter} from 'main.core.events';
import {ProductCalculator, DiscountType, TaxForPriceStrategy} from "catalog.product-calculator";
import {FormInputCode} from "../types/form-input-code";
import {FormErrorCode} from "../types/form-error-code";
import {FormMode} from "../types/form-mode";
import "./fields/quantity";
import "./fields/price";
import "./fields/discount";
import "./fields/tax";
import "./fields/inline-selector";
import "./fields/brand";
import type {BasketItemScheme} from "../types/basket-item-scheme";
import {FormOption} from "../types/form-option";

Vue.component(config.templateRowName,
	{
		/**
		 * @emits 'changeProduct' {index: number, fields: object}
		 * @emits 'changeRowData' {index: number, fields: object}
		 * @emits 'refreshBasket'
		 * @emits 'removeItem' {index: number}
		 */

		props: {
			basketItem: Object,
			basketItemIndex: Number,
			countItems: Number,
			options: Object,
			mode: String,
		},
		data()
		{
			return {
				currencySymbol: null,
				productSelector: null,
				imageControlId: null,
				selectorId: this.basketItem.selectorId,
				blocks: {
					productSelector: FormInputCode.PRODUCT_SELECTOR,
					quantity: FormInputCode.QUANTITY,
					price: FormInputCode.PRICE,
					result: FormInputCode.RESULT,
					discount: FormInputCode.DISCOUNT,
					tax: FormInputCode.TAX,
					brand: FormInputCode.BRAND,
				},
				errorCodes: {
					emptyProductSelector: FormErrorCode.EMPTY_PRODUCT_SELECTOR,
					emptyImage: FormErrorCode.EMPTY_IMAGE,
					emptyQuantity: FormErrorCode.EMPTY_QUANTITY,
					emptyPrice: FormErrorCode.EMPTY_PRICE,
					emptyBrand: FormErrorCode.EMPTY_BRAND,
				}
			};
		},
		created()
		{
			const defaultFields = this.basketItem.fields;
			const defaultPrice = Text.toNumber(defaultFields.price);
			const basePrice = defaultFields.basePrice || defaultPrice;
			const calculatorFields = {
				'QUANTITY': Text.toNumber(defaultFields.quantity),
				'BASE_PRICE': basePrice,
				'PRICE': defaultPrice,
				'PRICE_NETTO': basePrice,
				'PRICE_BRUTTO': defaultPrice,
				'PRICE_EXCLUSIVE': this.basketItem.fields.priceExclusive || defaultPrice,
				'DISCOUNT_TYPE_ID': Text.toNumber(defaultFields.discountType) || DiscountType.PERCENTAGE,
				'DISCOUNT_RATE': Text.toNumber(defaultFields.discountRate),
				'DISCOUNT_SUM': Text.toNumber(defaultFields.discount),
				'TAX_INCLUDED': defaultFields.taxIncluded || this.options.taxIncluded,
				'TAX_RATE': defaultFields.tax || 0,
				'CUSTOMIZED': defaultFields.isCustomPrice || 'N',
			};

			const pricePrecision = this.options.pricePrecision || 2;
			this.calculator = new ProductCalculator(calculatorFields, {
				currencyId: this.options.currency,
				pricePrecision: pricePrecision,
				commonPrecision: pricePrecision,
			});

			this.calculator.setCalculationStrategy(new TaxForPriceStrategy(this.calculator));

			this.currencySymbol = this.options.currencySymbol;
			this.defaultMeasure = {
				name: '',
				id: null,
			};

			if (Type.isArray(this.options.measures))
			{
				this.options.measures.map((measure) => {
					if (measure['IS_DEFAULT'] === 'Y')
					{
						this.defaultMeasure.name = measure.SYMBOL;
						this.defaultMeasure.code = measure.CODE;

						if (!defaultFields.measureName && !defaultFields.measureCode)
						{
							this.changeProduct({
								measureCode: this.defaultMeasure.code,
								measureName: this.defaultMeasure.name
							});
						}
					}
				});
			}

			this.onInputDiscount = Runtime.debounce(this.changeDiscount, 500, this);
		},
		updated()
		{
			if (Type.isObject(this.basketItem.calculatedFields))
			{
				const changedFields = this.basketItem.calculatedFields;
				changedFields['PRICES'] = {};
				changedFields['PRICES'][this.options.basePriceId] = {
					PRICE: changedFields.BASE_PRICE || changedFields.PRICE,
					CURRENCY: this.options.currency,
				};

				changedFields['MEASURE_CODE'] = this.basketItem.fields.measureCode;
				EventEmitter.emit(this, 'ProductList::onChangeFields', {
					rowId: this.selectorId,
					fields: changedFields
				});
			}
		},
		methods:
			{
				getField(name, defaultValue = null)
				{
					return this.basketItem.fields[name] || defaultValue;
				},
				getCalculator(): ProductCalculator
				{
					return this.calculator;
				},
				setCalculatedFields(fields: {}): void
				{
					const map = {
						calculatedFields: fields,
					};
					const productFields = this.basketItem.fields;

					if (!Type.isNil(fields.ID))
					{
						map.offerId = Text.toNumber(fields.ID);
						productFields.productId = Text.toNumber(fields.PRODUCT_ID);
						productFields.skuId = Text.toNumber(fields.SKU_ID);
					}
					if (!Type.isNil(fields.NAME))
					{
						productFields.name = fields.NAME;
					}
					if (!Type.isNil(fields.MODULE))
					{
						productFields.module = fields.MODULE;
					}
					if (Text.toNumber(fields.BASE_PRICE) >= 0)
					{
						productFields.basePrice = Text.toNumber(fields.BASE_PRICE);
					}
					if (Text.toNumber(fields.PRICE) >= 0)
					{
						productFields.price = Text.toNumber(fields.PRICE);
						productFields.priceExclusive = Text.toNumber(fields.PRICE_EXCLUSIVE);
					}
					if (Text.toNumber(fields.PRICE_EXCLUSIVE) >= 0 && fields.TAX_INCLUDED === 'Y')
					{
						productFields.priceExclusive = Text.toNumber(fields.PRICE);
					}
					if (Text.toNumber(fields.QUANTITY) >= 0)
					{
						productFields.quantity = Text.toNumber(fields.QUANTITY);
					}
					if (!Type.isNil(fields.DISCOUNT_RATE))
					{
						productFields.discountRate = Text.toNumber(fields.DISCOUNT_RATE);
					}
					if (!Type.isNil(fields.DISCOUNT_SUM))
					{
						productFields.discount = Text.toNumber(fields.DISCOUNT_SUM);
					}
					if (!Type.isNil(fields.DISCOUNT_TYPE_ID))
					{
						productFields.discountType = fields.DISCOUNT_TYPE_ID;
					}
					if (Text.toNumber(fields.SUM) >= 0)
					{
						map.sum = Text.toNumber(fields.SUM);
					}

					if (!Type.isNil(fields.CUSTOMIZED))
					{
						productFields.isCustomPrice = fields.CUSTOMIZED;
					}

					if (!Type.isNil(fields.MEASURE_CODE))
					{
						productFields.measureCode = fields.MEASURE_CODE;
					}

					if (!Type.isNil(fields.MEASURE_NAME))
					{
						productFields.measureName = fields.MEASURE_NAME;
					}

					if (!Type.isNil(fields.PROPERTIES))
					{
						productFields.properties = fields.PROPERTIES;
					}

					if (!Type.isNil(fields.BRANDS))
					{
						productFields.brands = fields.BRANDS;
					}

					if (!Type.isNil(fields.TAX_ID))
					{
						productFields.taxId = fields.TAX_ID;
					}

					this.changeRowData(map);
					this.changeProduct(productFields);
				},
				changeRowData(fields: {}): void
				{
					this.$emit('changeRowData', {
						index: this.basketItemIndex,
						fields
					});
				},
				changeProduct(fields: {}): void
				{
					fields = Object.assign(this.basketItem.fields, fields);
					this.$emit('changeProduct', {
						index: this.basketItemIndex,
						fields
					});
				},
				onProductChange(fields: {})
				{
					fields = Object.assign(
						this.getCalculator().calculatePrice(fields.BASE_PRICE),
						fields
					);

					this.getCalculator().setFields(fields);
					this.setCalculatedFields(fields);
				},
				onProductClear()
				{
					const fields = this.getCalculator().calculatePrice(0);

					fields.BASE_PRICE = 0;
					fields.NAME = '';
					fields.ID = 0;
					fields.PRODUCT_ID = 0;
					fields.SKU_ID = 0;
					fields.MODULE = '';

					this.getCalculator().setFields(fields);
					this.setCalculatedFields(fields);
				},
				toggleDiscount(value: string): void
				{
					if (this.isReadOnly)
					{
						return;
					}

					this.changeRowData(
						{showDiscount: value}
					);

					if (value === 'Y')
					{
						setTimeout(
							() => this.$refs?.discountWrapper?.$refs?.discountInput?.focus()
						);
					}
				},
				toggleTax(value: string): void
				{
					this.changeRowData(
						{showTax: value}
					);
				},
				changeBrand(values): void
				{
					const fields = this.getCalculator().getFields();
					fields.BRANDS = Type.isArray(values) ? values : [];
					this.setCalculatedFields(fields);
				},
				processFields(fields: {}): void
				{
					this.setCalculatedFields(fields);
					this.getCalculator().setFields(fields);
				},
				changeQuantity(quantity: number): void
				{
					this.processFields(
						this.getCalculator().calculateQuantity(quantity)
					);
				},
				changeMeasure(measure: {}): void
				{
					const productFields = this.basketItem.fields;
					productFields['measureCode'] = measure.code;
					productFields['measureName'] = measure.name;
					this.changeProduct(productFields);
				},
				changePrice(price: number): void
				{
					const calculatedFields = this.getCalculator().calculatePrice(price);
					calculatedFields.BASE_PRICE = price;
					this.processFields(calculatedFields);
				},
				changeDiscountType(discountType: string)
				{
					const type = (Text.toNumber(discountType) === DiscountType.MONETARY) ?  DiscountType.MONETARY : DiscountType.PERCENTAGE;
					this.processFields(
						this.getCalculator().calculateDiscountType(type)
					);
				},
				changeDiscount(discount: number)
				{
					this.processFields(
						this.getCalculator().calculateDiscount(discount)
					);
				},
				changeTax(fields)
				{
					const calculatedFields = this.getCalculator().calculateTax(fields.taxValue);
					calculatedFields.TAX_ID = fields.taxId;
					this.processFields(calculatedFields)
				},
				changeTaxIncluded(taxIncluded)
				{
					if (taxIncluded === this.basketItem.taxIncluded || !this.isEditableField(this.blocks.tax))
					{
						return;
					}

					const calculatedFields = this.getCalculator().calculateTaxIncluded(taxIncluded);
					this.getCalculator().setFields(calculatedFields);
					this.setCalculatedFields(calculatedFields);
				},
				removeItem()
				{
					this.$emit('removeItem', {
						index: this.basketItemIndex
					});
				},
				isRequiredField(code: string): boolean
				{
					return Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
				},
				isVisibleBlock(code): boolean
				{
					return Type.isArray(this.options.visibleBlocks) && this.options.visibleBlocks.includes(code)
				},
				hasError(code)
				{
					if (this.basketItem.errors.length === 0)
					{
						return false;
					}

					const filteredErrors = this.basketItem.errors.filter((error) => {
						return error.code === code
					});

					return filteredErrors.length > 0;
				},
				isEditableField(code)
				{
					return this.options?.editableFields.includes(code);
				},
			},
		watch:
			{
				taxIncluded(value, oldValue){
					if (value !== oldValue)
					{
						this.changeTaxIncluded(value);
					}
				}
			},
		computed:
			{
				localize()
				{
					return Vue.getFilteredPhrases('CATALOG_FORM_');
				},
				showDiscount(): boolean
				{
					return this.showDiscountBlock && this.basketItem.showDiscount === 'Y';
				},
				getBrandsSelectorId(): string
				{
					return this.basketItem.selectorId + '_brands';
				},
				getPriceExclusive(): ?number
				{
					return this.basketItem.fields.priceExclusive || this.basketItem.fields.price
				},
				showDiscountBlock(): boolean
				{
					return this.options.showDiscountBlock === 'Y'
						&& this.isVisibleBlock(this.blocks.discount)
						&& !this.isReadOnly
					;
				},
				showTaxBlock(): boolean
				{
					return this.options.showTaxBlock === 'Y'
						&& this.getTaxList.length > 0
						&& this.isVisibleBlock(this.blocks.tax)
						&& !this.isReadOnly
					;
				},
				showRemoveIcon(): boolean
				{
					if (this.isReadOnly)
					{
						return false;
					}

					if (this.countItems > 1)
					{
						return true;
					}

					return this.basketItem.offerId !== null;
				},
				showTaxSelector(): boolean
				{
					return this.basketItem.showTax === 'Y';
				},
				showBasePrice(): boolean
				{
					return this.basketItem.fields.discount > 0
						|| (Text.toNumber(this.basketItem.fields.price) !== Text.toNumber(this.basketItem.fields.basePrice))
					;
				},
				getMeasureName(): string
				{
					return this.basketItem.fields.measureName || this.defaultMeasure.name;
				},
				getMeasureCode(): string
				{
					return this.basketItem.fields.measureCode || this.defaultMeasure.code;
				},
				getTaxList(): []
				{
					return Type.isArray(this.options.taxList) ? this.options.taxList : [];
				},
				taxIncluded(): string
				{
					return this.basketItem.fields.taxIncluded;
				},
				isTaxIncluded(): boolean
				{
					return this.taxIncluded === 'Y';
				},
				isReadOnly(): boolean
				{
					return this.mode === FormMode.READ_ONLY
				},
			},
		// language=Vue
		template: `
		<div class="catalog-pf-product-item" v-bind:class="{ 'catalog-pf-product-item--borderless': !isReadOnly && basketItemIndex === 0 }">
			<div class="catalog-pf-product-item--remove" @click="removeItem" v-if="showRemoveIcon"></div>
			<div class="catalog-pf-product-item--num">
				<div class="catalog-pf-product-index">{{basketItemIndex + 1}}</div>
			</div>
			<div class="catalog-pf-product-item--left">
				<div v-if="isVisibleBlock(blocks.productSelector)">
					<div class="catalog-pf-product-item-section">
						<div class="catalog-pf-product-label">{{localize.CATALOG_FORM_NAME}}</div>
					</div>
					<${config.templateFieldInlineSelector} 
						:basketItem="basketItem" 
						:options="options"
						:editable="isEditableField(blocks.productSelector)"
						@onProductChange="onProductChange" 
					/>
				</div>
				<div v-if="isVisibleBlock(blocks.brand)" class="catalog-pf-product-input-brand-wrapper">
					<div class="catalog-pf-product-item-section">
						<div class="catalog-pf-product-label">{{localize.CATALOG_FORM_BRAND_TITLE}}</div>
					</div>
					<${config.templateFieldBrand} 
						:brands="basketItem.fields.brands"
						:selectorId="getBrandsSelectorId"
						:hasError="hasError(errorCodes.emptyBrand)"
						:options="options"
						:editable="isEditableField(blocks.brand)"
						@changeBrand="changeBrand" 
					/>
				</div>
				
			</div>
			<div class="catalog-pf-product-item--right">
				<div class="catalog-pf-product-item-section">
					<div v-if="isVisibleBlock(blocks.price)" class="catalog-pf-product-label" style="width: 94px">
						{{localize.CATALOG_FORM_PRICE}}
					</div>
					<div v-if="isVisibleBlock(blocks.quantity)" class="catalog-pf-product-label" style="width: 72px">
						{{localize.CATALOG_FORM_QUANTITY}}
					</div>
					<div v-if="isVisibleBlock(blocks.result)" class="catalog-pf-product-label" style="width: 94px">
						{{localize.CATALOG_FORM_RESULT}}
					</div>
				</div>
				<div class="catalog-pf-product-item-section">
				
					<div v-if="isVisibleBlock(blocks.price)" class="catalog-pf-product-control" style="width: 94px">
						<${config.templateFieldPrice} 
							:basePrice="basketItem.fields.basePrice"
							:options="options"
							:editable="isEditableField(blocks.price)"
							:hasError="hasError(errorCodes.emptyPrice)"
							@changePrice="changePrice"
						/>
					</div>
					
					<div v-if="isVisibleBlock(blocks.quantity)" class="catalog-pf-product-control" style="width: 72px">
						<${config.templateFieldQuantity} 
							:quantity="basketItem.fields.quantity"
							:measureCode="getMeasureCode"
							:measureRatio="basketItem.fields.measureRatio"
							:measureName="getMeasureName"
							:hasError="hasError(errorCodes.emptyQuantity)"
							:options="options"
							:editable="isEditableField(blocks.quantity)"
							@changeQuantity="changeQuantity" 
							@changeMeasure="changeMeasure" 
						/>
					</div>
					
					<div v-if="isVisibleBlock(blocks.result)" class="catalog-pf-product-control" style="width: 94px">
						<div class="catalog-pf-product-input-wrapper">
							<input disabled type="text" class="catalog-pf-product-input catalog-pf-product-input--disabled catalog-pf-product-input--gray catalog-pf-product-input--align-right" :value="basketItem.sum">
							<div class="catalog-pf-product-input-info catalog-pf-product-input--disabled catalog-pf-product-input--gray" v-html="currencySymbol"></div>
						</div>
					</div>
				</div>
				<div v-if="hasError(errorCodes.emptyQuantity)" class="catalog-pf-product-item-section">
					<div class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_QUANTITY}}</div>
				</div>
				<div v-if="hasError(errorCodes.emptyPrice)" class="catalog-pf-product-item-section">
					<div class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_PRICE}}</div>
				</div>
				<div v-if="showDiscountBlock" class="catalog-pf-product-item-section">
					<div v-if="showDiscount" class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide" @click="toggleDiscount('N')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
					<div v-else class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show" @click="toggleDiscount('Y')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
				</div>
				
				<div v-if="showDiscount" class="catalog-pf-product-item-section">
					<${config.templateFieldDiscount} 
						:discount="basketItem.fields.discount"
						:discountType="basketItem.fields.discountType"
						:discountRate="basketItem.fields.discountRate"
						:options="options"
						:editable="isEditableField(blocks.discount)"
						ref="discountWrapper"
						@changeDiscount="changeDiscount" 
						@changeDiscountType="changeDiscountType" 
					/>
				</div>
				
				<div v-if="showTaxBlock" class="catalog-pf-product-item-section catalog-pf-product-item-section--dashed">
					<div v-if="showTaxSelector" class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide" @click="toggleTax('N')">{{localize.CATALOG_FORM_TAX_TITLE}}</div>
					<div v-else class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show" @click="toggleTax('Y')">{{localize.CATALOG_FORM_TAX_TITLE}}</div>
				</div>
				<div v-if="showTaxSelector && showTaxBlock" class="catalog-pf-product-item-section">
					<${config.templateFieldTax} 
						:taxId="basketItem.fields.taxId"
						:options="options"
						:editable="isEditableField(blocks.tax)"
						@changeProduct="changeProduct" 
					/>
				</div>				
				<div class="catalog-pf-product-item-section catalog-pf-product-item-section--dashed"></div>
			</div>
		</div>
	`
	});