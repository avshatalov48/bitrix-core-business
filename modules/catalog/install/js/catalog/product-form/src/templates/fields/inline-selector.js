import { Type } from 'main.core';
import { Vue } from 'ui.vue';
import { config } from '../../config';
import { FormInputCode } from '../../types/form-input-code';
import { ProductSelector } from 'catalog.product-selector';
import { EventEmitter } from 'main.core.events';
import type { BaseEvent } from 'main.core.events';

Vue.component(
	config.templateFieldInlineSelector,
	{
		/**
		 * @emits 'onProductChange' {fields: object}
		 */
		props: {
			editable: Boolean,
			basketLength: Number,
			options: Object,
			basketItem: Object,
			model: Object,
		},
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
			EventEmitter.subscribe('BX.Catalog.ProductSelector:onProductSelect', this.onProductSelect.bind(this));
			EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChange.bind(this));
			EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClear.bind(this));
			EventEmitter.subscribe(
				this.$root.$app,
				'onChangeCompilationMode',
				this.changeProductSelectorImageRequire.bind(this),
			);
		},
		mounted()
		{
			this.productSelector = new ProductSelector(this.selectorId, this.prepareSelectorParams());
			this.productSelector.renderTo(this.$refs.selectorWrapper);
		},
		methods:
		{
			changeProductSelectorImageRequire(event: BaseEvent)
			{
				const isCompilationMode = event.getData()?.isCompilationMode;
				const isFacebookForm = event.getData()?.isFacebookForm;

				this.productSelector.setConfig(
					'ENABLE_EMPTY_IMAGES_ERROR',
					isCompilationMode && isFacebookForm,
				);

				this.productSelector.checkEmptyImageError();
				this.productSelector.layoutErrors();
			},
			prepareSelectorParams(): Object
			{
				const fields = {
					NAME: this.getField('name') || '',
				};

				if (!Type.isNil(this.getField('basePrice')))
				{
					fields.PRICE = this.getField('basePrice');
					fields.CURRENCY = this.options.currency;
				}

				const basketItemOfferId = this.basketItem.offerId;
				const facebookFailProducts = this.options.facebookFailProducts;
				const hasFacebookError = (
					Type.isObject(facebookFailProducts)
					&& facebookFailProducts.hasOwnProperty(basketItemOfferId)
				);

				const selectorOptions = {
					iblockId: this.options.iblockId,
					basePriceId: this.options.basePriceId,
					currency: this.options.currency,
					skuTree: this.getDefaultSkuTree(),
					fileInputId: '',
					morePhotoValues: [],
					fileInput: '',
					model: this.model,
					config: {
						DETAIL_PATH: this.basketItem.detailUrl || '',
						ENABLE_SEARCH: true,
						ENABLE_INPUT_DETAIL_LINK: true,
						ENABLE_IMAGE_CHANGE_SAVING: true,
						ENABLE_EMPTY_PRODUCT_ERROR: (
							this.options.enableEmptyProductError
							|| this.isRequiredField(FormInputCode.PRODUCT_SELECTOR)
						),
						ENABLE_EMPTY_IMAGES_ERROR: this.isRequiredField(FormInputCode.IMAGE_EDITOR),
						ROW_ID: this.selectorId,
						ENABLE_SKU_SELECTION: this.editable,
						HIDE_UNSELECTED_ITEMS: this.options.hideUnselectedProperties,
						URL_BUILDER_CONTEXT: this.options.urlBuilderContext,
						VIEW_FORMAT: this.options.isShortProductViewFormat
							? ProductSelector.SHORT_VIEW_FORMAT
							: ProductSelector.FULL_VIEW_FORMAT
						,
					},
					failedProduct: hasFacebookError,
					mode: this.editable ? ProductSelector.MODE_EDIT : ProductSelector.MODE_VIEW,
					fields,
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
			isEnabledSaving(): boolean
			{
				return this.options.enableCatalogSaving && this.basketItem.hasEditRights;
			},
			isRequiredField(code: string): boolean
			{
				return Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
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
			getField(name, defaultValue = null): any
			{
				return this.basketItem.fields[name] || defaultValue;
			},
			onProductSelect(event: BaseEvent)
			{
				const data = event.getData();
				if (Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId())
				{
					this.$emit('onProductSelect');
				}
			},
			onProductChange(event: BaseEvent): void
			{
				const data = event.getData();
				if (Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId())
				{
					const basePrice = data.fields.BASE_PRICE;

					const fields = {
						BASE_PRICE: basePrice,
						MODULE: 'catalog',
						NAME: data.fields.NAME,
						ID: data.fields.ID,
						PRODUCT_ID: data.fields.PRODUCT_ID,
						TYPE: data.fields.TYPE,
						SKU_ID: data.fields.SKU_ID,
						PROPERTIES: data.fields.PROPERTIES,
						URL_BUILDER_CONTEXT: this.options.urlBuilderContext,
						CUSTOMIZED: (Type.isNil(data.fields.PRICE) || data.fields.CUSTOMIZED === 'Y') ? 'Y' : 'N',
						MEASURE_CODE: data.fields.MEASURE_CODE,
						MEASURE_NAME: data.fields.MEASURE_NAME,
						MORE_PHOTO: data.morePhoto,
						BRANDS: data.fields.BRANDS,
						IS_NEW: data.isNew,
					};

					this.$emit('onProductChange', fields);
				}
			},
			onProductClear(event: BaseEvent)
			{
				const data = event.getData();

				if (Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId())
				{
					this.$emit('onProductClear');
				}
			},
		},
		// language=Vue
		template: `
			<div class='catalog-pf-product-item-section' :id='selectorId' ref='selectorWrapper'></div>
		`,
	},
);
