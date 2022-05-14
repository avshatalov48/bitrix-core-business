import {config} from "../config";
import {Vue} from "ui.vue";
import {Text, Type, Tag, Loc, Event} from "main.core";
import {EventEmitter} from "main.core.events";

import "catalog.product-selector";
import "ui.common";
import "ui.alerts";
import "ui.notification";
import {ProductCalculator, DiscountType} from "catalog.product-calculator";
import {FormInputCode} from "../types/form-input-code";
import {FormErrorCode} from "../types/form-error-code";
import {FormMode} from "../types/form-mode";
import "./fields/quantity";
import "./fields/price";
import "./fields/discount";
import "./fields/tax";
import "./fields/inline-selector";
import "./fields/brand";
import "./fields/result-sum";
import {ProductModel} from "catalog.product-model";
import type {FieldScheme} from "catalog.product-calculator";


Vue.component(config.templateRowName,
	{
		/**
		 * @emits 'changeProduct' {index: number, fields: object}
		 * @emits 'changeRowData' {index: number, fields: object}
		 * @emits 'emitErrorsChange' {index: number, errors: object}
		 * @emits 'refreshBasket'
		 * @emits 'removeItem' {index: number}
		 */

		props: {
			basketItem: Object,
			basketItemIndex: Number,
			basketLength: Number,
			countItems: Number,
			options: Object,
			mode: String,
		},

		data()
		{
			return {
				model: null,
				currencySymbol: null,
				productSelector: null,
				imageControlId: null,
				selectorId: this.basketItem.selectorId,
				defaultMeasure: {
					name: '',
					id: null,
				},
				blocks: {
					productSelector: FormInputCode.PRODUCT_SELECTOR,
					quantity: FormInputCode.QUANTITY,
					price: FormInputCode.PRICE,
					result: FormInputCode.RESULT,
					discount: FormInputCode.DISCOUNT,
					tax: FormInputCode.TAX,
					brand: FormInputCode.BRAND,
					measure: FormInputCode.MEASURE,
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
			this.currencySymbol = this.options.currencySymbol;

			this.model = this.initModel();
			if (Type.isArray(this.options.measures))
			{
				this.options.measures.map((measure) => {
					if (measure['IS_DEFAULT'] === 'Y')
					{
						this.defaultMeasure.name = measure.SYMBOL;
						this.defaultMeasure.code = measure.CODE;

						if (!this.basketItem.fields.measureName && !this.basketItem.fields.measureCode)
						{
							this.changeProductFields({
								measureCode: this.defaultMeasure.code,
								measureName: this.defaultMeasure.name
							});
						}
					}
				});
			}
		},
		methods:
			{
				prepareModelFields(): {}
				{
					const defaultFields = this.basketItem.fields;
					const defaultPrice = Text.toNumber(defaultFields.price);
					let basePrice = defaultFields.basePrice ? defaultFields.basePrice : defaultFields.price;
					if (!Type.isNil(basePrice))
					{
						basePrice = Text.toNumber(basePrice);
					}
					return {
						NAME: this.basketItem.fields?.name || '',
						MODULE: this.basketItem.fields?.module || '',
						PROPERTIES: this.basketItem.fields?.properties || {},
						BRAND: this.basketItem.fields?.brand || {},
						PRODUCT_ID: this.basketItem.fields?.productId,
						ID: this.basketItem.fields?.skuId || this.basketItem.fields?.productId,
						SKU_ID: this.basketItem.fields?.skuId,
						QUANTITY: Text.toNumber(defaultFields.quantity),
						BASE_PRICE: basePrice,
						PRICE: defaultPrice,
						PRICE_NETTO: basePrice,
						PRICE_BRUTTO: defaultPrice,
						PRICE_EXCLUSIVE: this.basketItem.fields.priceExclusive || defaultPrice,
						DISCOUNT_TYPE_ID: Text.toNumber(defaultFields.discountType) || DiscountType.PERCENTAGE,
						DISCOUNT_RATE: Text.toNumber(defaultFields.discountRate),
						DISCOUNT_SUM: Text.toNumber(defaultFields.discount),
						TAX_INCLUDED: defaultFields.taxIncluded || this.options.taxIncluded,
						TAX_RATE: defaultFields.tax || 0,
						CUSTOMIZED: defaultFields.isCustomPrice || 'N',
						MEASURE_CODE: defaultFields.measureCode || this.defaultMeasure.code,
						MEASURE_NAME: defaultFields.measureName || this.defaultMeasure.name,
					}
				},
				initModel(): ProductModel
				{
					const productId = Text.toNumber(this.basketItem.fields?.productId);
					const skuId = Text.toNumber(this.basketItem.fields?.skuId);
					const model = new ProductModel(
						{
							iblockId: Text.toNumber(this.options.iblockId),
							basePriceId: Text.toNumber(this.options.basePriceId),
							currency: this.options.currency,
							isSimpleModel: (
								Type.isStringFilled(this.basketItem.fields?.name)
								&& productId <= 0
								&& skuId <= 0
							),
							fields: this.prepareModelFields(),
						}
					);

					EventEmitter.subscribe(model, 'onErrorsChange', this.onErrorsChange);

					return model;
				},
				onErrorsChange()
				{
					const errors = Object.values(this.model.getErrorCollection().getErrors());
					this.changeRowData({errors});
					this.$emit('emitErrorsChange', {
						index: this.basketItemIndex,
						errors,
					});
				},
				setCalculatedFields(fields: {}): void
				{
					this.model.getCalculator().setFields(fields);
					const map = {calculatedFields: fields};
					if (Text.toNumber(fields.SUM) >= 0)
					{
						map.sum = Text.toNumber(fields.SUM);
					}

					if (!Type.isNil(fields.ID))
					{
						map.offerId = Text.toNumber(fields.ID);
					}

					this.changeRowData(map);
				},
				getProductFieldsFromModel()
				{
					const modelFields = this.model.getFields();
					return {
						productId: modelFields.PRODUCT_ID,
						skuId: modelFields.SKU_ID,
						name: modelFields.NAME,
						module: modelFields.MODULE,
						basePrice: modelFields.BASE_PRICE,
						price: modelFields.PRICE,
						priceExclusive: modelFields.PRICE_EXCLUSIVE,
						quantity: modelFields.QUANTITY,
						discountRate: modelFields.DISCOUNT_RATE,
						discount: modelFields.DISCOUNT_SUM,
						discountType: modelFields.DISCOUNT_TYPE_ID,
						isCustomPrice: modelFields.CUSTOMIZED || 'N',
						measureCode: modelFields.MEASURE_CODE || '',
						measureName: modelFields.MEASURE_NAME || '',
						properties: modelFields.PROPERTIES || {},
						brands: modelFields.BRANDS || {},
						taxId: modelFields.TAX_ID,
					};
				},
				changeRowData(product: {}): void
				{
					this.$emit('changeRowData', {
						index: this.basketItemIndex,
						product
					});
				},
				changeProductFields(fields: {}): void
				{
					fields = Object.assign(this.basketItem.fields, fields);
					this.$emit('changeProduct', {
						index: this.basketItemIndex,
						product: {fields},
						skipFieldChecking: this.model.isSimple() && this.basketLength === 1,
					});
				},
				saveCatalogField(changedFields: []): ?Promise
				{
					return this.model.save(changedFields);
				},
				onProductChange(fields: {})
				{
					fields = Object.assign(
						this.model.getCalculator().calculateBasePrice(fields.BASE_PRICE),
						fields
					);

					this.changeRowData(
						{catalogPrice: fields.BASE_PRICE}
					);
					this.processFields(fields);
					this.setCalculatedFields(fields);
				},
				onProductSelect()
				{
					this.changeProductFields({
						additionalFields: {
							originBasketId: '',
							originProductId: '',
						},
					});
				},
				onProductClear()
				{
					const fields = this.model.getCalculator().calculatePrice(0);

					fields.BASE_PRICE = 0;
					fields.NAME = '';
					fields.ID = 0;
					fields.PRODUCT_ID = 0;
					fields.SKU_ID = 0;
					fields.MODULE = '';

					this.setCalculatedFields(fields);
				},
				onChangeSum(sum: number)
				{
					const price = (sum / Text.toNumber(this.basketItem.fields.quantity)) + Text.toNumber(this.basketItem.fields.discount);
					this.onChangePrice(price);
				},
				onChangePrice(newPrice)
				{
					if (!this.options.isCatalogPriceSaveEnabled)
					{
						this.changeBasePrice(newPrice);

						return;
					}

					this.model.showSaveNotifier(
						'priceChanger_' + this.selectorId,
						{
							title: Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_PRICE_CHANGED_QUERY'),
							events: {
								onCancel: () => {
									const calculatorFields = this.changePrice(newPrice);
									if (calculatorFields.DISCOUNT_SUM > 0)
									{
										this.toggleDiscount('Y');
										this.$root.$app.changeFormOption('showDiscountBlock', 'Y');
									}
								},
								onSave: () => {
									this.changeBasePrice(newPrice);
									this.saveCatalogField(['BASE_PRICE']).then(()=>{
										this.changeRowData(
											{catalogPrice: newPrice}
										);
									});
								}
							},
						}
					);
				},
				onSelectMeasure(measure: {})
				{
					this.changeMeasure(measure);
					this.model.showSaveNotifier(
						'measureChanger_' + this.selectorId,
						{
							title: Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_MEASURE_CHANGED_QUERY'),
							events: {
								onSave: () => {
									this.saveCatalogField(['MEASURE_CODE', 'MEASURE_NAME']);
								}
							},
						}
					);
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
				processFields(fields: {}): void
				{
					this.model.getCalculator().setFields(fields);
					this.model.setFields(fields);
					this.changeProductFields({...this.basketItem.fields, ...this.getProductFieldsFromModel()});

					if (!Type.isNil(fields.SUM))
					{
						this.changeRowData({sum: fields.SUM});
					}
				},
				changeBrand(values): void
				{
					const brands = Type.isArray(values) ? values : [];
					this.processFields({ BRANDS: brands });
				},
				onChangeQuantity(quantity: number): void
				{
					this.model.getCalculator().setFields();
					this.processFields(
						this.model.getCalculator().calculateQuantity(quantity)
					);
				},
				changeMeasure(measure: {}): void
				{
					const productFields = this.basketItem.fields;
					productFields['measureCode'] = measure.code;
					productFields['measureName'] = measure.name;
					this.processFields({
						MEASURE_CODE: measure.code,
						MEASURE_NAME: measure.name,
					});
				},
				changeBasePrice(price: number): void
				{
					this.model.setField('BASE_PRICE', price);
					this.processFields(
						this.model.getCalculator().calculateBasePrice(price)
					);
				},
				changePrice(price: number): FieldScheme
				{
					this.model.getCalculator().setFields(
						this.model.getCalculator().calculateBasePrice(this.basketItem.catalogPrice)
					);
					const calculatedFields = this.model.getCalculator().calculatePrice(price);
					this.processFields(calculatedFields);
					return calculatedFields;
				},
				changeDiscountType(discountType: string): FieldScheme
				{
					const type = (Text.toNumber(discountType) === DiscountType.MONETARY) ?  DiscountType.MONETARY : DiscountType.PERCENTAGE;
					const calculatedFields = this.model.getCalculator().calculateDiscountType(type);
					this.processFields(calculatedFields);
					return calculatedFields;
				},
				changeDiscount(discount: number): FieldScheme
				{
					const calculatedFields = this.model.getCalculator().calculateDiscount(discount);
					this.processFields(calculatedFields);
					return  calculatedFields;
				},
				changeTax(fields)
				{
					const calculatedFields = this.model.getCalculator().calculateTax(fields.taxValue);
					calculatedFields.TAX_ID = fields.taxId;
					this.processFields(calculatedFields)
					return  calculatedFields;
				},
				changeTaxIncluded(taxIncluded)
				{
					if (taxIncluded === this.basketItem.taxIncluded || !this.isEditableField(this.blocks.tax))
					{
						return;
					}

					const calculatedFields = this.model.getCalculator().calculateTaxIncluded(taxIncluded);
					this.processFields(calculatedFields)
					return  calculatedFields;
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
				hasError(code): boolean
				{
					if (this.basketItem.errors.length === 0 || (this.model.isEmpty() && !this.model.isChanged()))
					{
						return false;
					}

					const filteredErrors = this.basketItem.errors.filter((error) => {
						return error.code === code
					});

					return filteredErrors.length > 0;
				},
				isEditablePrice(): boolean
				{
					return this.options?.editableFields.includes(FormInputCode.PRICE)
						&& (
							this.model.isNew()
							|| !this.model.isCatalogExisted()
							|| this.options?.isCatalogPriceEditEnabled
						)
					;
				},
				isEditableField(code): boolean
				{
					if (code === FormInputCode.PRICE && !this.options?.isCatalogPriceEditEnabled)
					{
						return this.isEditablePrice();
					}

					return this.options?.editableFields.includes(code);
				},
				getHint(code): ?{}
				{
					return this.options?.fieldHints[code];
				},
				hasHint(code): boolean
				{
					if (code === FormInputCode.PRICE && !this.options?.isCatalogPriceEditEnabled)
					{
						return !this.isEditablePrice();
					}

					return false;
				},
				showPriceNotify()
				{
					const hint = this.getHint(this.blocks.price);
					if (Text.toNumber(hint?.ARTICLE_CODE) > 0)
					{
						top.BX.Helper.show("redirect=detail&code=" + Text.toNumber(hint?.ARTICLE_CODE));
					}
				}
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

					return !Type.isNil(this.basketItem.offerId);
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
						:basketLength="basketLength"
						:options="options"
						:model="model"
						:editable="isEditableField(blocks.productSelector)"
						@onProductSelect="onProductSelect"
						@onProductChange="onProductChange"
						@saveCatalogField="saveCatalogField"
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
						@saveCatalogField="saveCatalogField"
					/>
				</div>

			</div>
			<div class="catalog-pf-product-item--right">
				<div class="catalog-pf-product-item-section">
					<div v-if="isVisibleBlock(blocks.price)" class="catalog-pf-product-label" style="width: 94px">
						{{localize.CATALOG_FORM_PRICE}}
						<span v-if="hasHint(blocks.price)" class="ui-hint-icon" @click="showPriceNotify"></span>
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
							:selectorId="basketItem.selectorId"
							:price="basketItem.fields.basePrice"
							:options="options"
							:editable="isEditableField(blocks.price)"
							:hasError="hasError(errorCodes.emptyPrice)"
							@onChangePrice="onChangePrice"
							@saveCatalogField="saveCatalogField"
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
							@onChangeQuantity="onChangeQuantity"
							@onSelectMeasure="onSelectMeasure"
						/>
					</div>

					<div v-if="isVisibleBlock(blocks.result)" class="catalog-pf-product-control" style="width: 94px">
						<${config.templateFieldResultSum}
								:sum="basketItem.sum"
								:options="options"
								:editable="isEditableField(blocks.result)"
								@onChangeSum="onChangeSum"
						/>
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
