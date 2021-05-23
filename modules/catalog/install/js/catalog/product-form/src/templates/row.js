import {config} from "../config";
import {Vue} from "ui.vue";
import {Text, Type, Runtime} from "main.core";
import {Menu} from 'main.popup';
import {Popup} from 'main.popup';

import "catalog.product-selector";
import "ui.common";
import "ui.alerts";
import {type BaseEvent, EventEmitter} from 'main.core.events';
import {ProductSelector} from "catalog.product-selector";
import {ProductCalculator, DiscountType, TaxForPriceStrategy} from "catalog.product-calculator";

Vue.component(config.templateProductRowName,
	{
		/**
		 * @emits 'changeProduct' {index: number, fields: object}
		 * @emits 'changeRowData' {index: number, fields: object}
		 * @emits 'refreshBasket'
		 * @emits 'removeItem' {index: number}
		 */

		props: ['basketItem', 'basketItemIndex', 'countItems', 'options', 'editable'],
		data()
		{
			return {
				currencySymbol: null,
				productSelector: null,
				imageControlId: null,
				selectorId: this.basketItem.selectorId,
			};
		},
		created()
		{
			const defaultFields = this.basketItem.fields;
			const defaultPrice = Text.toNumber(defaultFields.price);
			const basePrice = this.basketItem.fields.basePrice || defaultPrice;
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
				currencyId: this.options.currencyId,
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

			EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChange.bind(this));
			EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClear.bind(this));

			this.onInputPrice = Runtime.debounce(this.changePrice, 500, this);
			this.onInputQuantity = Runtime.debounce(this.changeQuantity, 500, this);
			this.onInputDiscount = Runtime.debounce(this.changeDiscount, 500, this);
		},
		mounted()
		{
			this.productSelector = new ProductSelector(this.selectorId, this.prepareSelectorParams());

			if (!Type.isObject(this.basketItem.image))
			{
				this.initEmptyImageInputScripts();
			}
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
				prepareSelectorParams(): Object
				{
					const selectorOptions = {
						iblockId: this.options.iblockId,
						basePriceId: this.options.basePriceId,
						productId: this.getField('productId'),
						skuId: this.getField('skuId'),
						skuTree: this.getDefaultSkuTree(),
						fileInputId: '',
						morePhotoValues: [],
						fileInput: "<div class='ui-image-input-container ui-image-input-img--disabled'>" +
							"<div class='adm-fileinput-wrapper '>" +
								"<div class='adm-fileinput-area mode-pict adm-fileinput-drag-area'></div>" +
							"</div>" +
						"</div>",
						imageValues: [],
						config: {
							DETAIL_PATH: this.basketItem.detailUrl || '',
							ENABLE_SEARCH: true,
							ENABLE_INPUT_DETAIL_LINK: true,
							ENABLE_IMAGE_CHANGE_SAVING: true,
							ROW_ID: this.selectorId,
							ENABLE_SKU_SELECTION: this.editable,
							URL_BUILDER_CONTEXT: this.options.urlBuilderContext
						},
						mode: this.editable ? ProductSelector.MODE_EDIT : ProductSelector.MODE_VIEW,
						fields: {
							NAME: this.getField('name') || ''
						},
					};

					const formImage = this.basketItem.image;
					if (Type.isObject(formImage))
					{
						selectorOptions.fileView = formImage.preview;
						selectorOptions.fileInput = formImage.input;
						selectorOptions.fileInputId = formImage.id;
						selectorOptions.morePhotoValues = formImage.values;
					}

					return selectorOptions;
				},
				getDefaultSkuTree(): Object
				{
					let skuTree = this.basketItem.skuTree || {};
					if (Type.isStringFilled(skuTree))
					{
						skuTree = JSON.parse(skuTree);
					}

					return skuTree;
				},
				getField(name, defaultValue = null)
				{
					return this.basketItem.fields[name] || defaultValue;
				},
				initEmptyImageInputScripts(): void
				{
					if (!this.productSelector)
					{
						return;
					}

					BX.ajax.runAction(
						"catalog.productSelector.getEmptyInputImage",
						{ json: { iblockId: this.options.iblockId } }
					).then((response) => {
						const imageData = response.data;
						this.productSelector.getFileInput().setId(imageData.id);
						this.productSelector.getFileInput().setInputHtml(imageData.input);

						this.productSelector.layoutImage();
					});
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
					if (Text.toNumber(fields.QUANTITY) > 0)
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
				onProductChange(event: BaseEvent)
				{
					const data = event.getData();
					if (Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId())
					{
						const basePrice = Text.toNumber(data.fields.PRICE);

						let fields = {
							BASE_PRICE: basePrice,
							MODULE: 'catalog',
							NAME: data.fields.NAME,
							ID: data.fields.ID,
							PRODUCT_ID: data.fields.PRODUCT_ID,
							SKU_ID: data.fields.SKU_ID,
						};

						fields = Object.assign(
							this.getCalculator().calculatePrice(basePrice),
							fields
						);

						this.getCalculator().setFields(fields);
						this.setCalculatedFields(fields);
					}
				},
				onProductClear(event: BaseEvent)
				{
					const data = event.getData();

					if (Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId())
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
					}
				},
				toggleDiscount(value: string): void
				{
					if (!this.editable)
					{
						return;
					}

					this.changeRowData(
						{showDiscount: value}
					);

					value === 'Y' ? setTimeout(() => this.$refs.discountInput.focus()) : null;
				},
				toggleTax(value: string): void
				{
					this.changeRowData(
						{showTax: value}
					);
				},
				changeQuantity(event: BaseEvent): void
				{
					if (!this.editable)
					{
						return;
					}

					event.target.value = event.target.value.replace(/[^.\d]/g,'.');
					let newQuantity = parseFloat(event.target.value);
					let lastSymbol = event.target.value.substr(-1);

					if (!newQuantity || lastSymbol === '.')
					{
						return;
					}

					const calculatedFields = this.getCalculator().calculateQuantity(newQuantity);
					this.setCalculatedFields(calculatedFields);
					this.getCalculator().setFields(calculatedFields);
				},
				changePrice(event: BaseEvent): void
				{
					if (!this.editable)
					{
						return;
					}

					event.target.value = event.target.value.replace(/[^.,\d]/g,'');
					if (event.target.value === '')
					{
						event.target.value = 0;
					}
					let lastSymbol = event.target.value.substr(-1);
					if (lastSymbol === ',')
					{
						event.target.value = event.target.value.replace(',', ".");
					}
					let newPrice = parseFloat(event.target.value);
					if (newPrice < 0|| lastSymbol === '.' || lastSymbol === ',')
					{
						return;
					}

					const calculatedFields = this.getCalculator().calculatePrice(newPrice);
					calculatedFields.BASE_PRICE = newPrice;
					this.getCalculator().setFields(calculatedFields);
					this.setCalculatedFields(calculatedFields);
				},
				/**
				 *
				 * @param discountType {string}
				 */
				changeDiscountType(discountType)
				{
					if (!this.editable)
					{
						return;
					}

					let type = (Text.toNumber(discountType) === DiscountType.MONETARY) ?  DiscountType.MONETARY : DiscountType.PERCENTAGE;
					const calculatedFields = this.getCalculator().calculateDiscountType(type);
					this.getCalculator().setFields(calculatedFields);
					this.setCalculatedFields(calculatedFields);
				},
				changeDiscount(event)
				{
					let discountValue = Text.toNumber(event.target.value) || 0;
					if (discountValue === Text.toNumber(this.basketItem.discount) || !this.editable)
					{
						return;
					}

					const calculatedFields = this.getCalculator().calculateDiscount(discountValue);
					this.getCalculator().setFields(calculatedFields);
					this.setCalculatedFields(calculatedFields);
				},
				changeTax(taxValue)
				{
					if (taxValue === Text.toNumber(this.basketItem.tax) || !this.editable)
					{
						return;
					}

					const calculatedFields = this.getCalculator().calculateTax(taxValue);
					this.getCalculator().setFields(calculatedFields);
					this.setCalculatedFields(calculatedFields);
				},
				changeTaxIncluded(taxIncluded)
				{
					if (taxIncluded === this.basketItem.taxIncluded || !this.editable)
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
				openDiscountEditor(e, url)
				{
					if(!(window.top.BX.SidePanel && window.top.BX.SidePanel.Instance))
					{
						return;
					}

					window.top.BX.SidePanel.Instance.open (
						BX.util.add_url_param( url, { "IFRAME": "Y", "IFRAME_TYPE": "SIDE_SLIDER", "publicSidePanel": "Y" } ),
						{ allowChangeHistory: false }
					);

					e.preventDefault ? e.preventDefault() : (e.returnValue = false);
				},
				isEmptyProductName()
				{
					return (this.basketItem.name.length === 0);
				},
				calculateCorrectionFactor(quantity, measureRatio)
				{
					let factoredQuantity = quantity;
					let factoredRatio = measureRatio;
					let correctionFactor = 1;

					while (!(Number.isInteger(factoredQuantity) && Number.isInteger(factoredRatio)))
					{
						correctionFactor *= 10;
						factoredQuantity = quantity * correctionFactor;
						factoredRatio = measureRatio * correctionFactor;
					}

					return correctionFactor;
				},
				incrementQuantity()
				{
					if (!this.editable)
					{
						return;
					}

					let correctionFactor = this.calculateCorrectionFactor(this.basketItem.quantity, this.basketItem.measureRatio);
					const quantity = (this.basketItem.quantity * correctionFactor + this.basketItem.measureRatio * correctionFactor) / correctionFactor;
					this.changeQuantity(quantity);
				},
				decrementQuantity()
				{
					if (this.basketItem.quantity > this.basketItem.measureRatio && this.editable)
					{
						let correctionFactor = this.calculateCorrectionFactor(this.basketItem.quantity, this.basketItem.measureRatio);
						const quantity = (this.basketItem.quantity * correctionFactor - this.basketItem.measureRatio * correctionFactor) / correctionFactor;
						this.changeQuantity(quantity);
					}
				},
				showPopupMenu(target, array, type)
				{
					if (!this.editable)
					{
						return;
					}

					const menuItems = [];
					const setItem = (ev, param) => {
						if (type === 'tax')
						{
							this.changeTax(Text.toNumber(param.options.item));
						}
						else if (type === 'measures')
						{
							const productFields = this.basketItem.fields;
							productFields['measureCode'] = param.options.item.CODE;
							productFields['measureName'] = param.options.item.SYMBOL;
							this.changeProduct(productFields);
						}
						else
						{
							target.innerHTML = ev.target.innerHTML;

							if(type === 'discount')
							{
								this.changeDiscountType(param.options.type);
							}
						}

						this.popupMenu.close();
					};

					if(type === 'discount')
					{
						array = [];
						array[DiscountType.PERCENTAGE] = '%';
						array[DiscountType.MONETARY] = this.currencySymbol;
					}

					if(array)
					{
						for(let item in array)
						{
							let text = array[item];

							if (type === 'measures')
							{
								text = array[item].SYMBOL;
							}
							else if (type === 'tax')
							{
								text = text + '%';
							}

							menuItems.push({
								text: text,
								item: array[item],
								onclick: setItem.bind({ value: 'settswguy' }),
								type: type === 'discount' ? item : null
							})
						}
					}

					this.popupMenu = new Menu({
						bindElement: target,
						items: menuItems
					});

					this.popupMenu.show();
				},
				showProductTooltip(e)
				{
					if(!this.productTooltip)
					{
						this.productTooltip = new Popup({
							bindElement: e.target,
							maxWidth: 400,
							darkMode: true,
							innerHTML: e.target.value,
							animation: 'fading-slide'
						});
					}

					this.productTooltip.setContent(e.target.value);
					e.target.value.length > 0 ? this.productTooltip.show() : null;
				},
				hideProductTooltip()
				{
					this.productTooltip ? this.productTooltip.close() : null;
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
				showDiscount()
				{
					return this.showDiscountBlock && this.basketItem.showDiscount === 'Y';
				},
				getDiscountSymbol()
				{
					return Text.toNumber(this.basketItem.fields.discountType) === DiscountType.PERCENTAGE ? '%' : this.currencySymbol;
				},
				getDiscountInputValue()
				{
					if (Text.toNumber(this.basketItem.fields.discountType) === DiscountType.PERCENTAGE)
					{
						return Text.toNumber(this.basketItem.fields.discountRate);
					}

					return Text.toNumber(this.basketItem.fields.discount);
				},
				getPriceExclusive()
				{
					return this.basketItem.fields.priceExclusive || this.basketItem.fields.price
				},
				showDiscountBlock()
				{
					return this.options.showDiscountBlock === 'Y'
						&& (
							this.editable
							|| (!this.editable && this.basketItem.fields.discount > 0)
						);
				},
				showTaxBlock()
				{
					return this.options.showTaxBlock === 'Y'
						&& this.getTaxList.length > 0
						&& (
							this.editable
							|| (!this.editable && this.showBasePrice)
						);
				},
				showTaxSelector()
				{
					return this.basketItem.showTax === 'Y';
				},
				showBasePrice()
				{
					return this.basketItem.fields.discount > 0
						|| (Text.toNumber(this.basketItem.fields.price) !== Text.toNumber(this.basketItem.fields.basePrice))
					;
				},
				getMeasureName()
				{
					return this.basketItem.fields.measureName || this.defaultMeasure.name;
				},
				getMeasureCode()
				{
					return this.basketItem.fields.measureCode || this.defaultMeasure.code;
				},
				getTaxList()
				{
					return Type.isArray(this.options.taxList) ? this.options.taxList : [];
				},
				taxIncluded()
				{
					return this.basketItem.fields.taxIncluded;
				},
				isTaxIncluded()
				{
					return this.taxIncluded === 'Y';
				},
				isNotEnoughQuantity()
				{
					return this.basketItem.errors.includes('SALE_BASKET_AVAILABLE_QUANTITY');
				},
				hasPriceError()
				{
					return this.basketItem.errors.includes('SALE_BASKET_ITEM_WRONG_PRICE');
				}
			},
		template: `
		<div class="catalog-pf-product-item" v-bind:class="{ 'catalog-pf-product-item--borderless': !editable && basketItemIndex === 0 }">
			<div class="catalog-pf-product-item--remove" @click="removeItem" v-if="countItems > 1 && editable"></div>
			<div class="catalog-pf-product-item--num">
				<div class="catalog-pf-product-index">{{basketItemIndex + 1}}</div>
			</div>
			<div class="catalog-pf-product-item--left">
				<div class="catalog-pf-product-item-section">
					<div class="catalog-pf-product-label">{{localize.CATALOG_FORM_NAME}}</div>
				</div>
				<div class="catalog-pf-product-item-section" :id="selectorId"></div>
			</div>
			<div class="catalog-pf-product-item--right">
				<div class="catalog-pf-product-item-section">
					<div class="catalog-pf-product-label" style="width: 94px">{{localize.CATALOG_FORM_PRICE}}</div>
					<div class="catalog-pf-product-label" style="width: 72px">{{localize.CATALOG_FORM_QUANTITY}}</div>
					<div class="catalog-pf-product-label" style="width: 94px">{{localize.CATALOG_FORM_RESULT}}</div>
				</div>
				<div class="catalog-pf-product-item-section">
					<div class="catalog-pf-product-control" style="width: 94px">
						<div class="catalog-pf-product-input-wrapper">
							<input 	type="text" class="catalog-pf-product-input catalog-pf-product-input--align-right"
									v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
									:value="basketItem.fields.basePrice"
									@input="onInputPrice"
									:disabled="!editable">
							<div class="catalog-pf-product-input-info" v-html="currencySymbol"></div>
						</div>	
					</div>
					<div class="catalog-pf-product-control" style="width: 72px">
						<div class="catalog-pf-product-input-wrapper">
							<input 	type="text" class="catalog-pf-product-input"
									v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
									:value="basketItem.fields.quantity"
									@input="onInputQuantity"
									:disabled="!editable">
							<div 	class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
									@click="showPopupMenu($event.target, options.measures, 'measures')"><span>{{ getMeasureName }}</span></div>
						</div>
					</div>
					<div class="catalog-pf-product-control" style="width: 94px">
						<div class="catalog-pf-product-input-wrapper">
							<input disabled type="text" class="catalog-pf-product-input catalog-pf-product-input--disabled catalog-pf-product-input--gray catalog-pf-product-input--align-right" :value="basketItem.sum">
							<div class="catalog-pf-product-input-info catalog-pf-product-input--disabled catalog-pf-product-input--gray" v-html="currencySymbol"></div>
						</div>
					</div>
				</div>
				<div v-if="showDiscountBlock" class="catalog-pf-product-item-section">
					<div v-if="showDiscount" class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide" @click="toggleDiscount('N')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
					<div v-else class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show" @click="toggleDiscount('Y')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
				</div>
				<div v-if="showDiscount" class="catalog-pf-product-item-section">
					<div class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--left">
						<input class="catalog-pf-product-input catalog-pf-product-input--align-right catalog-pf-product-input--right"
								v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
								ref="discountInput" 
								:value="getDiscountInputValue"
								@input="onInputDiscount"
								placeholder="0"
								:disabled="!editable">
						<div class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
							@click="showPopupMenu($event.target, null, 'discount')">
							<span v-html="getDiscountSymbol"></span>
						</div>
					</div>
				</div>
				
				<div v-if="showTaxBlock" class="catalog-pf-product-item-section catalog-pf-product-item-section--dashed">
					<div v-if="showTaxSelector" class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide" @click="toggleTax('N')">{{localize.CATALOG_FORM_TAX_TITLE}}</div>
					<div v-else class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show" @click="toggleTax('Y')">{{localize.CATALOG_FORM_TAX_TITLE}}</div>
				</div>
				<div v-if="showTaxSelector && showTaxBlock" class="catalog-pf-product-item-section">
					<div 
						class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--right"
						@click="showPopupMenu($event.target, getTaxList, 'tax')"
					>
						<div class="catalog-pf-product-input">{{basketItem.fields.tax}}%</div>
						<div class="catalog-pf-product-input-info catalog-pf-product-input-info--dropdown"></div>
					</div>
				</div>
				<div class="catalog-pf-product-item-section catalog-pf-product-item-section--dashed"></div>
			</div>
		</div>
	`
	});