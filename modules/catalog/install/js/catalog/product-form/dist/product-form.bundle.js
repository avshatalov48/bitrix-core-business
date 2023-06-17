this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_fonts_opensans,currency,ui_layoutForm,ui_forms,ui_buttons,ui_common,ui_alerts,catalog_productSelector,ui_entitySelector,catalog_productModel,ui_vue_vuex,main_popup,main_loader,ui_label,ui_messagecard,ui_vue_components_hint,ui_notification,ui_infoHelper,main_qrcode,clipboard,helper,catalog_storeUse,ui_hint,ui_vue,main_core,main_core_events,currency_currencyCore,catalog_productCalculator) {
	'use strict';

	class FormElementPosition {}
	FormElementPosition.TOP = 'TOP';
	FormElementPosition.BOTTOM = 'BOTTOM';

	class ProductList extends ui_vue_vuex.VuexBuilderModel {
	  /**
	   * @inheritDoc
	   */
	  getName() {
	    return 'productList';
	  }
	  getState() {
	    return {
	      currency: '',
	      taxIncluded: 'N',
	      basket: [],
	      total: {
	        sum: 0,
	        discount: 0,
	        taxSum: 0,
	        result: 0
	      }
	    };
	  }
	  static getBaseProduct() {
	    const random = main_core.Text.getRandom();
	    return {
	      offerId: null,
	      selectorId: random,
	      fields: {
	        innerId: random,
	        productId: null,
	        skuId: null,
	        code: null,
	        type: null,
	        module: null,
	        sort: 0,
	        price: null,
	        basePrice: null,
	        priceExclusive: null,
	        quantity: 1,
	        name: '',
	        discount: 0,
	        discountRate: 0,
	        discountInfos: [],
	        discountType: catalog_productCalculator.DiscountType.PERCENTAGE,
	        tax: 0,
	        taxSum: 0,
	        taxIncluded: 'N',
	        measureCode: 0,
	        measureName: '',
	        measureRatio: 1,
	        isCustomPrice: 'N',
	        additionalFields: [],
	        properties: [],
	        brands: [],
	        weight: 0,
	        dimensions: {}
	      },
	      calculatedFields: [],
	      catalogFields: {},
	      showDiscount: 'N',
	      showTax: 'N',
	      skuTree: [],
	      image: null,
	      sum: 0,
	      catalogPrice: null,
	      discountSum: 0,
	      detailUrl: '',
	      encodedFields: null,
	      errors: []
	    };
	  }
	  getActions() {
	    return {
	      resetBasket({
	        commit
	      }) {
	        commit('clearBasket');
	        commit('addItem', {});
	      },
	      removeItem({
	        dispatch,
	        commit,
	        state
	      }, payload) {
	        commit('deleteItem', payload);
	        if (state.basket.length === 0) {
	          commit('addItem', {});
	        } else {
	          state.basket.forEach((item, i) => {
	            commit('updateItem', {
	              index: i,
	              fields: {
	                sort: i
	              }
	            });
	          });
	        }
	        dispatch('calculateTotal');
	      },
	      changeItem: ({
	        dispatch,
	        commit
	      }, payload) => {
	        commit('updateItem', payload);
	        dispatch('calculateTotal');
	      },
	      setCurrency: ({
	        commit
	      }, payload) => {
	        const currency$$1 = payload || '';
	        commit('setCurrency', currency$$1);
	      },
	      addItem: ({
	        dispatch,
	        commit
	      }, payload) => {
	        const item = payload.item || {
	          fields: {}
	        };
	        commit('addItem', {
	          item,
	          position: payload.position || FormElementPosition.TOP
	        });
	        dispatch('calculateTotal');
	      },
	      calculateTotal: ({
	        commit,
	        state
	      }) => {
	        const total = {
	          sum: 0,
	          taxSum: 0,
	          discount: 0,
	          result: 0
	        };
	        state.basket.forEach(item => {
	          const basePrice = main_core.Text.toNumber(item.fields.basePrice || 0);
	          const quantity = main_core.Text.toNumber(item.fields.quantity || 0);
	          const discount = main_core.Text.toNumber(item.fields.discount || 0);
	          const taxSum = main_core.Text.toNumber(item.fields.taxSum || 0);
	          total.sum += basePrice * quantity;
	          total.result += main_core.Text.toNumber(item.sum);
	          total.discount += discount * quantity;
	          total.taxSum += taxSum * quantity;
	        });
	        total.discount = total.discount > total.sum ? total.sum : total.discount;
	        commit('setTotal', total);
	      }
	    };
	  }
	  getGetters() {
	    return {
	      getBasket: state => () => {
	        return state.basket;
	      },
	      getBaseProduct: () => () => {
	        return ProductList.getBaseProduct();
	      }
	    };
	  }
	  getMutations() {
	    return {
	      addItem: (state, payload) => {
	        let item = ProductList.getBaseProduct();
	        item = Object.assign(item, payload.item);
	        if (payload.position === FormElementPosition.BOTTOM) {
	          state.basket.push(item);
	        } else {
	          state.basket.unshift(item);
	        }
	        state.basket.forEach((item, index) => {
	          item.fields.sort = index;
	        });
	      },
	      updateItem: (state, payload) => {
	        if (main_core.Type.isNil(state.basket[payload.index])) {
	          ui_vue.Vue.set(state.basket, payload.index, ProductList.getBaseProduct());
	        }
	        state.basket[payload.index] = Object.assign(state.basket[payload.index], payload.product);
	      },
	      clearBasket: state => {
	        state.basket = [];
	      },
	      deleteItem: (state, payload) => {
	        state.basket.splice(payload.index, 1);
	        state.basket.forEach((item, index) => {
	          item.fields.sort = index;
	        });
	      },
	      setErrors: (state, payload) => {
	        state.errors = payload;
	      },
	      clearErrors: state => {
	        state.errors = [];
	      },
	      setCurrency: (state, payload) => {
	        state.currency = payload;
	      },
	      setTotal: (state, payload) => {
	        const formattedTotal = payload;
	        if (main_core.Type.isStringFilled(state.currency)) {
	          for (const key in payload) {
	            if (payload.hasOwnProperty(key)) {
	              formattedTotal[key] = currency_currencyCore.CurrencyCore.currencyFormat(payload[key], state.currency);
	            }
	          }
	        }
	        state.total = Object.assign(state.total, formattedTotal);
	      }
	    };
	  }
	}

	const config = Object.freeze({
	  databaseConfig: {
	    name: 'catalog.product-form'
	  },
	  templateName: 'bx-form',
	  templatePanelButtons: 'bx-panel-buttons',
	  templatePanelCompilation: 'bx-panel-compilation',
	  templateRowName: 'bx-form-row',
	  templateFieldInlineSelector: 'bx-field-inline-selector',
	  templateFieldPrice: 'bx-field-price',
	  templateFieldResultSum: 'bx-field-result-sum',
	  templateFieldQuantity: 'bx-field-quantity',
	  templateFieldDiscount: 'bx-field-discount',
	  templateFieldTax: 'bx-field-tax',
	  templateFieldBrand: 'bx-field-brand',
	  templateSummaryTotal: 'bx-summary-total',
	  moduleId: 'catalog'
	});

	class FormInputCode {}
	FormInputCode.PRODUCT_SELECTOR = 'product-selector';
	FormInputCode.IMAGE_EDITOR = 'image-editor';
	FormInputCode.QUANTITY = 'quantity';
	FormInputCode.PRICE = 'price';
	FormInputCode.RESULT = 'result';
	FormInputCode.DISCOUNT = 'discount';
	FormInputCode.TAX = 'tax';
	FormInputCode.BRAND = 'brand';
	FormInputCode.MEASURE = 'measure';

	class FormErrorCode {}
	FormErrorCode.EMPTY_PRODUCT_SELECTOR = 0;
	FormErrorCode.EMPTY_IMAGE = 1;
	FormErrorCode.EMPTY_QUANTITY = 2;
	FormErrorCode.EMPTY_PRICE = 3;
	FormErrorCode.EMPTY_BRAND = 4;
	FormErrorCode.IS_NULLABLE_PRICE = 5;

	class FormMode {}
	FormMode.REGULAR = 'REGULAR';
	FormMode.READ_ONLY = 'READ_ONLY';
	FormMode.COMPILATION = 'COMPILATION';
	FormMode.COMPILATION_READ_ONLY = 'COMPILATION_READ_ONLY';

	ui_vue.Vue.component(config.templateFieldQuantity, {
	  /**
	   * @emits 'onChangeQuantity' {quantity: number}
	   * @emits 'onSelectMeasure' {quantity: number, }
	   */

	  props: {
	    measureCode: Number,
	    measureRatio: Number,
	    measureName: String,
	    quantity: Number,
	    editable: Boolean,
	    saveableMeasure: Boolean,
	    hasError: Boolean,
	    options: Object
	  },
	  created() {
	    this.onInputQuantityHandler = main_core.Runtime.debounce(this.onInputQuantity, 500, this);
	  },
	  methods: {
	    onInputQuantity(event) {
	      if (!this.editable) {
	        return;
	      }
	      event.target.value = event.target.value.replace(/[^.\d]/g, '.');
	      const newQuantity = main_core.Text.toNumber(event.target.value);
	      const lastSymbol = event.target.value.substr(-1);
	      if (lastSymbol === '.') {
	        return;
	      }
	      this.changeQuantity(newQuantity);
	    },
	    calculateCorrectionFactor(quantity, measureRatio) {
	      let factoredQuantity = quantity;
	      let factoredRatio = measureRatio;
	      let correctionFactor = 1;
	      while (!(Number.isInteger(factoredQuantity) && Number.isInteger(factoredRatio))) {
	        correctionFactor *= 10;
	        factoredQuantity = quantity * correctionFactor;
	        factoredRatio = measureRatio * correctionFactor;
	      }
	      return correctionFactor;
	    },
	    incrementValue() {
	      if (!this.editable) {
	        return;
	      }
	      const correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
	      const quantity = (this.quantity * correctionFactor + this.measureRatio * correctionFactor) / correctionFactor;
	      this.changeQuantity(quantity);
	    },
	    decrementValue() {
	      if (this.quantity > this.measureRatio && this.editable) {
	        const correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
	        const quantity = (this.quantity * correctionFactor - this.measureRatio * correctionFactor) / correctionFactor;
	        this.changeQuantity(quantity);
	      }
	    },
	    changeQuantity(value) {
	      this.$emit('onChangeQuantity', value);
	    },
	    showPopupMenu(target) {
	      if (!this.editable || !main_core.Type.isArray(this.options.measures)) {
	        return;
	      }
	      const menuItems = [];
	      this.options.measures.forEach(item => {
	        menuItems.push({
	          text: item.SYMBOL,
	          item: item,
	          onclick: this.selectMeasure
	        });
	      });
	      if (menuItems.length > 0) {
	        this.popupMenu = new main_popup.Menu({
	          bindElement: target,
	          items: menuItems
	        });
	        this.popupMenu.show();
	      }
	    },
	    selectMeasure(event, params) {
	      var _params$options, _params$options2;
	      this.$emit('onSelectMeasure', {
	        code: (_params$options = params.options) == null ? void 0 : _params$options.item.CODE,
	        name: (_params$options2 = params.options) == null ? void 0 : _params$options2.item.SYMBOL
	      });
	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div class="catalog-pf-product-input-wrapper" v-bind:class="{ 'ui-ctl-danger': hasError }">
			<input 	
				type="text" class="catalog-pf-product-input"
				v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
				:value="quantity"
				@input="onInputQuantityHandler"
				:disabled="!editable"
				data-name="quantity"
				:data-value="quantity"
			>
			<div 
				class="catalog-pf-product-input-info catalog-pf-product-input-info--action" 
				@click="showPopupMenu($event.target)"
			>
				<span :title="measureName">{{ measureName }}</span>
			</div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateFieldPrice, {
	  /**
	   * @emits 'onChangePrice' {price: number}
	   * @emits 'saveCatalogField' {}
	   */

	  props: {
	    selectorId: String,
	    price: Number,
	    editable: Boolean,
	    hasError: Boolean,
	    options: Object
	  },
	  created() {
	    this.onInputPriceHandler = main_core.Runtime.debounce(this.onInputPrice, 500, this);
	  },
	  mounted() {
	    BX.UI.Hint.init();
	  },
	  methods: {
	    onInputPrice(event) {
	      if (!this.editable) {
	        return;
	      }
	      event.target.value = event.target.value.replace(/[^.,\d]/g, '');
	      if (event.target.value === '') {
	        event.target.value = 0;
	      }
	      const lastSymbol = event.target.value.substr(-1);
	      if (lastSymbol === ',') {
	        event.target.value = event.target.value.replace(',', ".");
	      }
	      let newPrice = main_core.Text.toNumber(event.target.value);
	      if (lastSymbol === '.' || lastSymbol === ',') {
	        return;
	      }
	      if (newPrice < 0) {
	        newPrice *= -1;
	      }
	      this.$emit('onChangePrice', newPrice);
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    currencySymbol() {
	      return this.options.currencySymbol || '';
	    },
	    hintText() {
	      var _this$options;
	      if (!this.editable && !((_this$options = this.options) != null && _this$options.isCatalogPriceEditEnabled)) {
	        return main_core.Loc.getMessage('CATALOG_FORM_PRICE_ACCESS_DENIED_HINT');
	      }
	      return null;
	    }
	  },
	  // language=Vue
	  template: `
		<div 
			class="catalog-pf-product-input-wrapper" 
			:class="{ 'ui-ctl-danger': hasError, '.catalog-pf-product-input-wrapper--disabled': !editable }"
			:data-hint="hintText"
			data-hint-no-icon
		>
			<input 	type="text" class="catalog-pf-product-input catalog-pf-product-input--align-right"
					v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
					v-model.lazy="price"
					@input="onInputPriceHandler"
					:disabled="!editable"
					data-name="price"
					:data-value="price"
			>
			<div class="catalog-pf-product-input-info" v-html="currencySymbol"></div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateFieldDiscount, {
	  /**
	   * @emits 'changeDiscountType' {type: Y|N}
	   * @emits 'changeDiscount' {discountValue: number}
	   */

	  props: {
	    editable: Boolean,
	    options: Object,
	    discount: Number,
	    discountType: Number,
	    discountRate: Number
	  },
	  created() {
	    this.onInputDiscount = main_core.Runtime.debounce(this.onChangeDiscount, 500, this);
	    this.currencySymbol = this.options.currencySymbol;
	  },
	  mounted() {
	    BX.UI.Hint.init();
	  },
	  methods: {
	    onChangeType(event, params) {
	      var _params$options;
	      if (!this.editable) {
	        return;
	      }
	      const type = main_core.Text.toNumber(params == null ? void 0 : (_params$options = params.options) == null ? void 0 : _params$options.type) === catalog_productCalculator.DiscountType.MONETARY ? catalog_productCalculator.DiscountType.MONETARY : catalog_productCalculator.DiscountType.PERCENTAGE;
	      this.$emit('changeDiscountType', type);
	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    },
	    onChangeDiscount(event) {
	      const discountValue = main_core.Text.toNumber(event.target.value) || 0;
	      if (discountValue === main_core.Text.toNumber(this.discount) || !this.editable) {
	        return;
	      }
	      this.$emit('changeDiscount', discountValue);
	    },
	    showPopupMenu(target) {
	      if (!this.editable || !main_core.Type.isArray(this.options.allowedDiscountTypes)) {
	        return;
	      }
	      const menuItems = [];
	      if (this.options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.PERCENTAGE)) {
	        menuItems.push({
	          text: '%',
	          onclick: this.onChangeType,
	          type: catalog_productCalculator.DiscountType.PERCENTAGE
	        });
	      }
	      if (this.options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.MONETARY)) {
	        menuItems.push({
	          text: this.currencySymbol,
	          onclick: this.onChangeType,
	          type: catalog_productCalculator.DiscountType.MONETARY
	        });
	      }
	      if (menuItems.length > 0) {
	        this.popupMenu = new main_popup.Menu({
	          bindElement: target,
	          items: menuItems
	        });
	        this.popupMenu.show();
	      }
	    }
	  },
	  computed: {
	    getDiscountInputValue() {
	      if (main_core.Text.toNumber(this.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE) {
	        return main_core.Text.toNumber(this.discountRate);
	      }
	      return main_core.Text.toNumber(this.discount);
	    },
	    getDiscountSymbol() {
	      return main_core.Text.toNumber(this.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE ? '%' : this.currencySymbol;
	    },
	    wrapperClasses() {
	      return {
	        'catalog-pf-product-input-wrapper--disabled': !this.editable
	      };
	    },
	    hintText() {
	      var _this$options;
	      if (!this.editable && !((_this$options = this.options) != null && _this$options.isCatalogDiscountSetEnabled)) {
	        return main_core.Loc.getMessage('CATALOG_FORM_DISCOUNT_ACCESS_DENIED_HINT');
	      }
	      return null;
	    }
	  },
	  // language=Vue
	  template: `
		<div
			class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--left"
			:class="wrapperClasses"
			:data-hint="hintText"
			data-hint-no-icon
		>
			<input class="catalog-pf-product-input catalog-pf-product-input--align-right catalog-pf-product-input--right"
				ref="discountInput"
				v-bind:class="{ 'catalog-pf-product-input--disabled': !editable }"
				:value="getDiscountInputValue"
				:v-model="discountRate"
				@input="onInputDiscount"
				placeholder="0"
				:disabled="!editable"
				data-name="discount"
				:data-value="getDiscountInputValue"
			/>
			<div class="catalog-pf-product-input-info catalog-pf-product-input-info--action"
				@click="showPopupMenu">
				<span v-html="getDiscountSymbol"></span>
			</div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateFieldTax, {
	  /**
	   * @emits 'changeTax' {taxValue: number}
	   */

	  props: {
	    taxId: Number,
	    editable: Boolean,
	    options: Object
	  },
	  data() {
	    return {
	      taxValue: this.getTaxList()[this.taxId] || 0
	    };
	  },
	  methods: {
	    onChangeValue(event, params) {
	      var _params$options, _params$options2;
	      const taxValue = main_core.Text.toNumber(params == null ? void 0 : (_params$options = params.options) == null ? void 0 : _params$options.item);
	      if (taxValue === main_core.Text.toNumber(this.taxValue) || !this.editable) {
	        return;
	      }
	      this.$emit('changeTax', {
	        taxValue,
	        taxId: params == null ? void 0 : (_params$options2 = params.options) == null ? void 0 : _params$options2.id
	      });
	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    },
	    getTaxList() {
	      return main_core.Type.isArray(this.options.taxList) ? this.options.taxList : [];
	    },
	    showPopupMenu(target) {
	      if (!this.editable || !main_core.Type.isArray(this.options.taxList)) {
	        return;
	      }
	      const menuItems = [];
	      this.options.taxList.forEach((item, id) => {
	        menuItems.push({
	          id,
	          text: item + '%',
	          item: item,
	          onclick: this.onChangeValue
	        });
	      });
	      if (menuItems.length > 0) {
	        this.popupMenu = new main_popup.Menu({
	          bindElement: target,
	          items: menuItems
	        });
	        this.popupMenu.show();
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div class="catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--right" @click="showPopupMenu">
			<div class="catalog-pf-product-input">{{this.taxValue}}%</div>
			<div class="catalog-pf-product-input-info catalog-pf-product-input-info--dropdown"></div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateFieldInlineSelector, {
	  /**
	   * @emits 'onProductChange' {fields: object}
	   */

	  props: {
	    editable: Boolean,
	    basketLength: Number,
	    options: Object,
	    basketItem: Object,
	    model: Object
	  },
	  data() {
	    return {
	      currencySymbol: null,
	      productSelector: null,
	      imageControlId: null,
	      selectorId: this.basketItem.selectorId
	    };
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onProductSelect', this.onProductSelect.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChange.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClear.bind(this));
	    main_core_events.EventEmitter.subscribe(this.$root.$app, 'onChangeCompilationMode', this.changeProductSelectorImageRequire.bind(this));
	  },
	  mounted() {
	    this.productSelector = new catalog_productSelector.ProductSelector(this.selectorId, this.prepareSelectorParams());
	    this.productSelector.renderTo(this.$refs.selectorWrapper);
	  },
	  methods: {
	    changeProductSelectorImageRequire(event) {
	      var _event$getData, _event$getData2;
	      const isCompilationMode = (_event$getData = event.getData()) == null ? void 0 : _event$getData.isCompilationMode;
	      const isFacebookForm = (_event$getData2 = event.getData()) == null ? void 0 : _event$getData2.isFacebookForm;
	      this.productSelector.setConfig('ENABLE_EMPTY_IMAGES_ERROR', isCompilationMode && isFacebookForm);
	      this.productSelector.checkEmptyImageError();
	      this.productSelector.layoutErrors();
	    },
	    prepareSelectorParams() {
	      const fields = {
	        NAME: this.getField('name') || ''
	      };
	      if (!main_core.Type.isNil(this.getField('basePrice'))) {
	        fields.PRICE = this.getField('basePrice');
	        fields.CURRENCY = this.options.currency;
	      }
	      const basketItemOfferId = this.basketItem.offerId;
	      const facebookFailProducts = this.options.facebookFailProducts;
	      const hasFacebookError = main_core.Type.isObject(facebookFailProducts) && facebookFailProducts.hasOwnProperty(basketItemOfferId);
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
	          ENABLE_EMPTY_PRODUCT_ERROR: this.options.enableEmptyProductError || this.isRequiredField(FormInputCode.PRODUCT_SELECTOR),
	          ENABLE_EMPTY_IMAGES_ERROR: this.isRequiredField(FormInputCode.IMAGE_EDITOR),
	          ROW_ID: this.selectorId,
	          ENABLE_SKU_SELECTION: this.editable,
	          HIDE_UNSELECTED_ITEMS: this.options.hideUnselectedProperties,
	          URL_BUILDER_CONTEXT: this.options.urlBuilderContext,
	          VIEW_FORMAT: this.options.isShortProductViewFormat ? catalog_productSelector.ProductSelector.SHORT_VIEW_FORMAT : catalog_productSelector.ProductSelector.FULL_VIEW_FORMAT
	        },
	        failedProduct: hasFacebookError,
	        mode: this.editable ? catalog_productSelector.ProductSelector.MODE_EDIT : catalog_productSelector.ProductSelector.MODE_VIEW,
	        fields
	      };
	      const formImage = this.basketItem.image;
	      if (main_core.Type.isObject(formImage)) {
	        selectorOptions.fileView = formImage.preview;
	        selectorOptions.fileInput = formImage.input;
	        selectorOptions.fileInputId = formImage.id;
	        selectorOptions.morePhotoValues = formImage.values;
	      }
	      return selectorOptions;
	    },
	    isEnabledSaving() {
	      return this.options.enableCatalogSaving && this.basketItem.hasEditRights;
	    },
	    isRequiredField(code) {
	      return main_core.Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
	    },
	    getDefaultSkuTree() {
	      let skuTree = this.basketItem.skuTree || {};
	      if (main_core.Type.isStringFilled(skuTree)) {
	        skuTree = JSON.parse(skuTree);
	      }
	      return skuTree;
	    },
	    getField(name, defaultValue = null) {
	      return this.basketItem.fields[name] || defaultValue;
	    },
	    onProductSelect(event) {
	      const data = event.getData();
	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
	        this.$emit('onProductSelect');
	      }
	    },
	    onProductChange(event) {
	      const data = event.getData();
	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
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
	          CUSTOMIZED: main_core.Type.isNil(data.fields.PRICE) || data.fields.CUSTOMIZED === 'Y' ? 'Y' : 'N',
	          MEASURE_CODE: data.fields.MEASURE_CODE,
	          MEASURE_NAME: data.fields.MEASURE_NAME,
	          MORE_PHOTO: data.morePhoto,
	          BRANDS: data.fields.BRANDS,
	          IS_NEW: data.isNew
	        };
	        this.$emit('onProductChange', fields);
	      }
	    },
	    onProductClear(event) {
	      const data = event.getData();
	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
	        this.$emit('onProductClear');
	      }
	    }
	  },
	  // language=Vue
	  template: `
		<div class="catalog-pf-product-item-section" :id="selectorId" ref="selectorWrapper"></div>
	`
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	ui_vue.Vue.component(config.templateFieldBrand, {
	  /**
	   * @emits 'changeBrand' {values: Array<any>}
	   */

	  props: {
	    brands: [Array, String],
	    options: Object,
	    editable: Boolean,
	    hasError: Boolean,
	    selectorId: String
	  },
	  data() {
	    return {
	      cache: new main_core.Cache.MemoryCache()
	    };
	  },
	  created() {
	    if (this.editable) {
	      this.selector = new ui_entitySelector.TagSelector({
	        id: this.selectorId,
	        dialogOptions: {
	          id: this.selectorId,
	          context: 'CATALOG_BRANDS',
	          // enableSearch: true,
	          preselectedItems: this.getPreselectedBrands(),
	          events: {
	            'Item:onSelect': this.onBrandChange.bind(this),
	            'Item:onDeselect': this.onBrandChange.bind(this),
	            'Search:onItemCreateAsync': this.createBrand.bind(this)
	          },
	          searchTabOptions: {
	            stub: true,
	            stubOptions: {
	              title: main_core.Tag.message(_t || (_t = _`${0}`), 'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_TITLE'),
	              subtitle: main_core.Tag.message(_t2 || (_t2 = _`${0}`), 'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_SUBTITLE'),
	              arrow: true
	            }
	          },
	          searchOptions: {
	            allowCreateItem: true
	          },
	          entities: [{
	            id: 'brand',
	            options: {
	              iblockId: this.options.iblockId
	            },
	            dynamicSearch: true,
	            dynamicLoad: true
	          }]
	        }
	      });
	      this.isSelectedByProductChange = false;
	      this.$parent.$on('onInlineSelectorProductChange', this.selectCurrentBrands.bind(this));
	    }
	  },
	  mounted() {
	    if (this.editable) {
	      this.selector.renderTo(this.$refs.brandSelectorWrapper);
	    } else {
	      this.brands.forEach((brand, brandIndex, brands) => {
	        const separator = brandIndex < brands.length - 1 ? ',&nbsp;' : '';
	        this.$refs.brandSelectorWrapper.appendChild(main_core.Tag.render(_t3 || (_t3 = _`
					<span>
						<span
							class="catalog-pf-product-input-brand-read-only-item"
							style="background-image:url('${0}');"
						></span>
						${0}
					</span>
				`), brand['IMAGE_SRC'], brand['NAME'] + separator));
	      });
	    }
	  },
	  methods: {
	    selectCurrentBrands(brands) {
	      this.isSelectedByProductChange = true;
	      this.brands = brands;
	      if (this.selector.getDialog().isLoaded()) {
	        this.selector.getDialog().deselectAll();
	        this.selectDialogItems();
	      } else {
	        this.selector.getDialog().load();
	        main_core_events.EventEmitter.subscribe(this.selector.getDialog(), 'onLoad', this.selectDialogItems.bind(this));
	      }
	    },
	    selectDialogItems() {
	      this.brands.forEach(brand => {
	        const item = this.selector.getDialog().getItem({
	          id: brand['VALUE'],
	          entityId: 'brand'
	        });
	        item.select();
	      });
	      this.isSelectedByProductChange = false;
	    },
	    getPreselectedBrands() {
	      if (!main_core.Type.isArray(this.brands) || this.brands.length === 0) {
	        return [];
	      }
	      return this.brands.map(item => {
	        return ['brand', item['VALUE']];
	      });
	    },
	    onBrandChange(event) {
	      const items = event.getTarget().getSelectedItems();
	      const resultValues = [];
	      if (main_core.Type.isArray(items)) {
	        items.forEach(item => {
	          resultValues.push({
	            'VALUE': item.getId(),
	            'NAME': item.getTitle(),
	            'IMAGE_SRC': item.getAvatar()
	          });
	        });
	      }
	      const eventData = {
	        resultValues: resultValues,
	        isSelectedByProductChange: this.isSelectedByProductChange
	      };
	      this.$emit('changeBrand', eventData);
	    },
	    createBrand(event) {
	      const {
	        searchQuery
	      } = event.getData();
	      const iblockId = this.options.iblockId;
	      return new Promise((resolve, reject) => {
	        const dialog = event.getTarget();
	        const fields = {
	          name: searchQuery.getQuery(),
	          iblockId
	        };
	        dialog.showLoader();
	        main_core.ajax.runAction('catalog.productForm.createBrand', {
	          data: {
	            fields
	          }
	        }).then(response => {
	          dialog.hideLoader();
	          const item = dialog.addItem({
	            id: response.data.id,
	            entityId: 'brand',
	            title: searchQuery.getQuery(),
	            tabs: dialog.getRecentTab().getId()
	          });
	          if (item) {
	            item.select();
	          }
	          dialog.hide();
	          resolve();
	        }).catch(() => reject());
	      });
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    }
	  },
	  // language=Vue
	  template: `
		<div class="catalog-pf-product-control ui-ctl-w100" v-bind:class="{ 'ui-ctl-danger': hasError }">
			<div class="catalog-pf-product-input-wrapper" ref="brandSelectorWrapper" :id="selectorId"></div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateFieldResultSum, {
	  /**
	   * @emits 'onChangeSum' {sum: number}
	   */

	  props: {
	    sum: Number,
	    editable: Boolean,
	    options: Object
	  },
	  created() {
	    this.onInputSumHandler = main_core.Runtime.debounce(this.onInputSum, 500, this);
	  },
	  methods: {
	    onInputSum(event) {
	      if (!this.editable) {
	        return;
	      }
	      event.target.value = event.target.value.replace(/[^.,\d]/g, '');
	      if (event.target.value === '') {
	        event.target.value = 0;
	      }
	      const lastSymbol = event.target.value.substr(-1);
	      if (lastSymbol === ',') {
	        event.target.value = event.target.value.replace(',', ".");
	      }
	      let newSum = main_core.Text.toNumber(event.target.value);
	      if (lastSymbol === '.' || lastSymbol === ',') {
	        return;
	      }
	      if (newSum < 0) {
	        newSum *= -1;
	      }
	      this.$emit('onChangeSum', newSum);
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    currencySymbol() {
	      return this.options.currencySymbol || '';
	    }
	  },
	  // language=Vue
	  template: `
		<div class="catalog-pf-product-input-wrapper">
			<input 	type="text" 
					class="catalog-pf-product-input catalog-pf-product-input--align-right"
					:class="{ 'catalog-pf-product-input--disabled': !editable }"
					:value="sum"
					@input="onInputSumHandler"
					:disabled="!editable"
					data-name="sum"
					:data-value="sum"
			>
			<div class="catalog-pf-product-input-info"
				 :class="{ 'catalog-pf-product-input--disabled': !editable }"
				 v-html="currencySymbol"
			></div>
		</div>
	`
	});

	ui_vue.Vue.component(config.templateRowName, {
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
	    mode: String
	  },
	  data() {
	    return {
	      model: null,
	      currencySymbol: null,
	      productSelector: null,
	      imageControlId: null,
	      selectorId: this.basketItem.selectorId,
	      defaultMeasure: {
	        name: '',
	        id: null
	      },
	      blocks: {
	        productSelector: FormInputCode.PRODUCT_SELECTOR,
	        quantity: FormInputCode.QUANTITY,
	        price: FormInputCode.PRICE,
	        result: FormInputCode.RESULT,
	        discount: FormInputCode.DISCOUNT,
	        tax: FormInputCode.TAX,
	        brand: FormInputCode.BRAND,
	        measure: FormInputCode.MEASURE
	      },
	      errorCodes: {
	        emptyProductSelector: FormErrorCode.EMPTY_PRODUCT_SELECTOR,
	        emptyImage: FormErrorCode.EMPTY_IMAGE,
	        emptyQuantity: FormErrorCode.EMPTY_QUANTITY,
	        emptyPrice: FormErrorCode.EMPTY_PRICE,
	        emptyBrand: FormErrorCode.EMPTY_BRAND
	      }
	    };
	  },
	  created() {
	    this.currencySymbol = this.options.currencySymbol;
	    this.model = this.initModel();
	    if (main_core.Type.isArray(this.options.measures)) {
	      this.options.measures.map(measure => {
	        if (measure['IS_DEFAULT'] === 'Y') {
	          this.defaultMeasure.name = measure.SYMBOL;
	          this.defaultMeasure.code = measure.CODE;
	          if (!this.basketItem.fields.measureName && !this.basketItem.fields.measureCode) {
	            this.changeProductFields({
	              measureCode: this.defaultMeasure.code,
	              measureName: this.defaultMeasure.name
	            });
	          }
	        }
	      });
	    }
	  },
	  methods: {
	    prepareModelFields() {
	      var _this$basketItem$fiel, _this$basketItem$fiel2, _this$basketItem$fiel3, _this$basketItem$fiel4, _this$basketItem$fiel5, _this$basketItem$fiel6, _this$basketItem$fiel7, _this$basketItem$fiel8;
	      const defaultFields = this.basketItem.fields;
	      const defaultPrice = main_core.Text.toNumber(defaultFields.price);
	      let basePrice = defaultFields.basePrice ? defaultFields.basePrice : defaultFields.price;
	      if (!main_core.Type.isNil(basePrice)) {
	        basePrice = main_core.Text.toNumber(basePrice);
	      }
	      return {
	        NAME: ((_this$basketItem$fiel = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel.name) || '',
	        MODULE: ((_this$basketItem$fiel2 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel2.module) || '',
	        PROPERTIES: ((_this$basketItem$fiel3 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel3.properties) || {},
	        BRAND: ((_this$basketItem$fiel4 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel4.brand) || {},
	        PRODUCT_ID: (_this$basketItem$fiel5 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel5.productId,
	        ID: ((_this$basketItem$fiel6 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel6.skuId) || ((_this$basketItem$fiel7 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel7.productId),
	        SKU_ID: (_this$basketItem$fiel8 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel8.skuId,
	        QUANTITY: main_core.Text.toNumber(defaultFields.quantity),
	        BASE_PRICE: basePrice,
	        PRICE: defaultPrice,
	        PRICE_NETTO: basePrice,
	        PRICE_BRUTTO: defaultPrice,
	        PRICE_EXCLUSIVE: this.basketItem.fields.priceExclusive || defaultPrice,
	        DISCOUNT_TYPE_ID: main_core.Text.toNumber(defaultFields.discountType) || catalog_productCalculator.DiscountType.PERCENTAGE,
	        DISCOUNT_RATE: main_core.Text.toNumber(defaultFields.discountRate),
	        DISCOUNT_SUM: main_core.Text.toNumber(defaultFields.discount),
	        TAX_INCLUDED: defaultFields.taxIncluded || this.options.taxIncluded,
	        TAX_RATE: defaultFields.tax || 0,
	        CUSTOMIZED: defaultFields.isCustomPrice || 'N',
	        MEASURE_CODE: defaultFields.measureCode || this.defaultMeasure.code,
	        MEASURE_NAME: defaultFields.measureName || this.defaultMeasure.name
	      };
	    },
	    initModel() {
	      var _this$basketItem$fiel9, _this$basketItem$fiel10, _this$basketItem$fiel11;
	      const productId = main_core.Text.toNumber((_this$basketItem$fiel9 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel9.productId);
	      const skuId = main_core.Text.toNumber((_this$basketItem$fiel10 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel10.skuId);
	      const model = new catalog_productModel.ProductModel({
	        iblockId: main_core.Text.toNumber(this.options.iblockId),
	        basePriceId: main_core.Text.toNumber(this.options.basePriceId),
	        currency: this.options.currency,
	        isStoreCollectable: false,
	        isSimpleModel: main_core.Type.isStringFilled((_this$basketItem$fiel11 = this.basketItem.fields) == null ? void 0 : _this$basketItem$fiel11.name) && productId <= 0 && skuId <= 0,
	        fields: this.prepareModelFields()
	      });
	      main_core_events.EventEmitter.subscribe(model, 'onErrorsChange', this.onErrorsChange);
	      return model;
	    },
	    onErrorsChange() {
	      const errors = Object.values(this.model.getErrorCollection().getErrors());
	      this.changeRowData({
	        errors
	      });
	      this.$emit('emitErrorsChange', {
	        index: this.basketItemIndex,
	        errors
	      });
	    },
	    setCalculatedFields(fields) {
	      this.model.getCalculator().setFields(fields);
	      const map = {
	        calculatedFields: fields
	      };
	      if (main_core.Text.toNumber(fields.SUM) >= 0) {
	        map.sum = main_core.Text.toNumber(fields.SUM);
	      }
	      if (!main_core.Type.isNil(fields.ID)) {
	        map.offerId = main_core.Text.toNumber(fields.ID);
	      }
	      this.changeRowData(map);
	    },
	    getProductFieldsFromModel() {
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
	        brands: modelFields.BRANDS || [],
	        taxId: modelFields.TAX_ID,
	        type: modelFields.TYPE,
	        morePhoto: modelFields.MORE_PHOTO
	      };
	    },
	    changeRowData(product) {
	      this.$emit('changeRowData', {
	        index: this.basketItemIndex,
	        product
	      });
	    },
	    changeProductFields(fields) {
	      fields = Object.assign(this.basketItem.fields, fields);
	      this.$emit('changeProduct', {
	        index: this.basketItemIndex,
	        product: {
	          fields
	        },
	        skipFieldChecking: this.model.isSimple() && this.basketLength === 1
	      });
	    },
	    saveCatalogField(changedFields) {
	      return this.model.save(changedFields);
	    },
	    onProductChange(fields) {
	      fields = Object.assign(this.model.getCalculator().calculateBasePrice(fields.BASE_PRICE), fields);
	      this.changeRowData({
	        catalogPrice: fields.BASE_PRICE
	      });
	      this.processFields(fields);
	      this.setCalculatedFields(fields);
	      this.$emit('onInlineSelectorProductChange', this.basketItem.fields.brands);
	    },
	    onProductSelect() {
	      this.changeProductFields({
	        additionalFields: {
	          originBasketId: '',
	          originProductId: ''
	        }
	      });
	    },
	    onProductClear() {
	      if (main_core.Type.isPlainObject(this.options.facebookFailProducts)) {
	        delete this.options.facebookFailProducts[this.basketItem.offerId];
	      }
	      /*const fields = this.model.getCalculator().calculatePrice(0);
	      	fields.BASE_PRICE = 0;
	      fields.NAME = '';
	      fields.ID = 0;
	      fields.PRODUCT_ID = 0;
	      fields.SKU_ID = 0;
	      fields.MODULE = '';
	      	this.setCalculatedFields(fields);*/
	    },

	    onChangeSum(sum) {
	      const priceItem = sum / main_core.Text.toNumber(this.basketItem.fields.quantity);
	      if (this.isEditablePrice()) {
	        const price = priceItem + main_core.Text.toNumber(this.basketItem.fields.discount);
	        this.onChangePrice(price);
	      } else if (this.isEditableDiscount()) {
	        const discount = this.basketItem.fields.basePrice - priceItem;
	        this.toggleDiscount('Y');
	        this.changeDiscountType(catalog_productCalculator.DiscountType.MONETARY);
	        this.changeDiscount(discount);
	      }
	    },
	    onChangePrice(newPrice) {
	      this.changeBasePrice(newPrice);
	      if (this.isSaveablePrice()) {
	        this.saveCatalogField(['BASE_PRICE']).then(() => {
	          this.changeRowData({
	            catalogPrice: newPrice
	          });
	        });
	      }
	    },
	    onSelectMeasure(measure) {
	      this.changeMeasure(measure);
	      this.model.showSaveNotifier('measureChanger_' + this.selectorId, {
	        title: main_core.Loc.getMessage('CATALOG_PRODUCT_MODEL_SAVING_NOTIFICATION_MEASURE_CHANGED_QUERY'),
	        events: {
	          onSave: () => {
	            this.saveCatalogField(['MEASURE_CODE', 'MEASURE_NAME']);
	          }
	        }
	      });
	    },
	    toggleDiscount(value) {
	      if (this.isReadOnly) {
	        return;
	      }
	      this.changeRowData({
	        showDiscount: value
	      });
	      if (value === 'Y') {
	        setTimeout(() => {
	          var _this$$refs, _this$$refs$discountW, _this$$refs$discountW2, _this$$refs$discountW3;
	          return (_this$$refs = this.$refs) == null ? void 0 : (_this$$refs$discountW = _this$$refs.discountWrapper) == null ? void 0 : (_this$$refs$discountW2 = _this$$refs$discountW.$refs) == null ? void 0 : (_this$$refs$discountW3 = _this$$refs$discountW2.discountInput) == null ? void 0 : _this$$refs$discountW3.focus();
	        });
	      }
	    },
	    toggleTax(value) {
	      this.changeRowData({
	        showTax: value
	      });
	    },
	    processFields(fields) {
	      this.model.getCalculator().setFields(fields);
	      this.model.setFields(fields);
	      this.changeProductFields({
	        ...this.basketItem.fields,
	        ...this.getProductFieldsFromModel()
	      });
	      if (!main_core.Type.isNil(fields.SUM)) {
	        this.changeRowData({
	          sum: fields.SUM
	        });
	      }
	    },
	    changeBrand(eventData) {
	      const brands = main_core.Type.isArray(eventData.resultValues) ? eventData.resultValues : [];
	      const isSelectedByProductChange = eventData.isSelectedByProductChange;
	      this.processFields({
	        BRANDS: brands
	      });
	      if (!isSelectedByProductChange) {
	        this.saveCatalogField(['BRANDS']);
	      }
	    },
	    onChangeQuantity(quantity) {
	      this.model.getCalculator().setFields();
	      this.processFields(this.model.getCalculator().calculateQuantity(quantity));
	    },
	    changeMeasure(measure) {
	      const productFields = this.basketItem.fields;
	      productFields['measureCode'] = measure.code;
	      productFields['measureName'] = measure.name;
	      this.processFields({
	        MEASURE_CODE: measure.code,
	        MEASURE_NAME: measure.name
	      });
	    },
	    changeBasePrice(price) {
	      this.model.setField('BASE_PRICE', price);
	      this.processFields(this.model.getCalculator().calculateBasePrice(price));
	    },
	    changePrice(price) {
	      this.model.getCalculator().setFields(this.model.getCalculator().calculateBasePrice(this.basketItem.catalogPrice));
	      const calculatedFields = this.model.getCalculator().calculatePrice(price);
	      this.processFields(calculatedFields);
	      return calculatedFields;
	    },
	    changeDiscountType(discountType) {
	      const type = main_core.Text.toNumber(discountType) === catalog_productCalculator.DiscountType.MONETARY ? catalog_productCalculator.DiscountType.MONETARY : catalog_productCalculator.DiscountType.PERCENTAGE;
	      const calculatedFields = this.model.getCalculator().calculateDiscountType(type);
	      this.processFields(calculatedFields);
	      return calculatedFields;
	    },
	    changeDiscount(discount) {
	      const calculatedFields = this.model.getCalculator().calculateDiscount(discount);
	      this.processFields(calculatedFields);
	      return calculatedFields;
	    },
	    changeTax(fields) {
	      const calculatedFields = this.model.getCalculator().calculateTax(fields.taxValue);
	      calculatedFields.TAX_ID = fields.taxId;
	      this.processFields(calculatedFields);
	      return calculatedFields;
	    },
	    changeTaxIncluded(taxIncluded) {
	      if (taxIncluded === this.basketItem.taxIncluded || !this.isEditableField(this.blocks.tax)) {
	        return;
	      }
	      const calculatedFields = this.model.getCalculator().calculateTaxIncluded(taxIncluded);
	      this.processFields(calculatedFields);
	      return calculatedFields;
	    },
	    removeItem() {
	      if (main_core.Type.isPlainObject(this.options.facebookFailProducts)) {
	        delete this.options.facebookFailProducts[this.basketItem.offerId];
	      }
	      this.$emit('removeItem', {
	        index: this.basketItemIndex
	      });
	    },
	    isRequiredField(code) {
	      return main_core.Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
	    },
	    isVisibleBlock(code) {
	      return main_core.Type.isArray(this.options.visibleBlocks) && this.options.visibleBlocks.includes(code);
	    },
	    isCompilationMode() {
	      return this.mode === FormMode.COMPILATION_READ_ONLY || this.mode === FormMode.COMPILATION;
	    },
	    getPriceValue() {
	      if (this.isCompilationMode()) {
	        return this.isEditableField(this.blocks.price) ? this.basketItem.fields.basePrice : this.basketItem.catalogPrice;
	      }
	      return this.basketItem.fields.basePrice;
	    },
	    getQuantityValue() {
	      if (this.isCompilationMode()) {
	        return this.isEditableField(this.blocks.quantity) ? this.basketItem.fields.quantity : 1;
	      }
	      return this.basketItem.fields.quantity;
	    },
	    getSumValue() {
	      if (this.isCompilationMode()) {
	        return this.isEditableField(this.blocks.result) ? this.basketItem.sum : this.basketItem.catalogPrice;
	      }
	      return this.basketItem.sum;
	    },
	    getDiscountValue() {
	      if (this.isCompilationMode()) {
	        return this.isEditableField(this.blocks.discount) ? this.basketItem.fields.discount : 0;
	      }
	      return this.basketItem.fields.discount;
	    },
	    getDiscountRateValue() {
	      if (this.isCompilationMode()) {
	        return this.isEditableField(this.blocks.discount) ? this.basketItem.fields.discountRate : 0;
	      }
	      return this.basketItem.fields.discountRate;
	    },
	    hasError(code) {
	      if (this.basketItem.errors.length === 0 || this.model.isEmpty() && !this.model.isChanged()) {
	        return false;
	      }
	      const filteredErrors = this.basketItem.errors.filter(error => {
	        return error.code === code;
	      });
	      return filteredErrors.length > 0;
	    },
	    isEditablePrice() {
	      var _this$options, _this$options2;
	      return ((_this$options = this.options) == null ? void 0 : _this$options.editableFields.includes(FormInputCode.PRICE)) && (this.model.isNew() || !this.model.isCatalogExisted() || ((_this$options2 = this.options) == null ? void 0 : _this$options2.isCatalogPriceEditEnabled));
	    },
	    isEditableDiscount() {
	      var _this$options3;
	      return (_this$options3 = this.options) == null ? void 0 : _this$options3.isCatalogDiscountSetEnabled;
	    },
	    isSaveablePrice() {
	      return this.options.isCatalogPriceEditEnabled && this.options.isCatalogPriceSaveEnabled && this.model.isNew();
	    },
	    isEditableField(code) {
	      var _this$options4, _this$options5;
	      if (code === FormInputCode.PRICE && !this.isEditablePrice()) {
	        return false;
	      } else if (code === FormInputCode.DISCOUNT && !this.isEditableDiscount()) {
	        return false;
	      } else if (code === FormInputCode.RESULT && !((_this$options4 = this.options) != null && _this$options4.isCatalogDiscountSetEnabled) && !this.isEditablePrice()) {
	        return false;
	      }
	      return (_this$options5 = this.options) == null ? void 0 : _this$options5.editableFields.includes(code);
	    },
	    getHint(code) {
	      var _this$options6;
	      return (_this$options6 = this.options) == null ? void 0 : _this$options6.fieldHints[code];
	    },
	    hasHint(code) {
	      var _this$options7;
	      if (code === FormInputCode.PRICE && !((_this$options7 = this.options) != null && _this$options7.isCatalogPriceEditEnabled)) {
	        return !this.isEditablePrice();
	      }
	      return false;
	    }
	  },
	  watch: {
	    taxIncluded(value, oldValue) {
	      if (value !== oldValue) {
	        this.changeTaxIncluded(value);
	      }
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_FORM_');
	    },
	    showDiscount() {
	      return this.showDiscountBlock && this.basketItem.showDiscount === 'Y';
	    },
	    getBrandsSelectorId() {
	      return this.basketItem.selectorId + '_brands';
	    },
	    getPriceExclusive() {
	      return this.basketItem.fields.priceExclusive || this.basketItem.fields.price;
	    },
	    showDiscountBlock() {
	      return this.options.showDiscountBlock === 'Y' && this.isVisibleBlock(this.blocks.discount) && !this.isReadOnly;
	    },
	    showTaxBlock() {
	      return this.options.showTaxBlock === 'Y' && this.getTaxList.length > 0 && this.isVisibleBlock(this.blocks.tax) && !this.isReadOnly;
	    },
	    showRemoveIcon() {
	      if (this.isReadOnly) {
	        return false;
	      }
	      if (this.countItems > 1) {
	        return true;
	      }
	      return !main_core.Type.isNil(this.basketItem.offerId);
	    },
	    showTaxSelector() {
	      return this.basketItem.showTax === 'Y';
	    },
	    showBasePrice() {
	      return this.basketItem.fields.discount > 0 || main_core.Text.toNumber(this.basketItem.fields.price) !== main_core.Text.toNumber(this.basketItem.fields.basePrice);
	    },
	    getMeasureName() {
	      return this.basketItem.fields.measureName || this.defaultMeasure.name;
	    },
	    getMeasureCode() {
	      return this.basketItem.fields.measureCode || this.defaultMeasure.code;
	    },
	    getTaxList() {
	      return main_core.Type.isArray(this.options.taxList) ? this.options.taxList : [];
	    },
	    taxIncluded() {
	      return this.basketItem.fields.taxIncluded;
	    },
	    isTaxIncluded() {
	      return this.taxIncluded === 'Y';
	    },
	    isReadOnly() {
	      return this.mode === FormMode.READ_ONLY || this.mode === FormMode.COMPILATION_READ_ONLY;
	    },
	    getErrorsText() {
	      let errorText = this.basketItem.errors.length !== 0 && !this.model.isEmpty() && this.model.isChanged() ? main_core.Loc.getMessage('CATALOG_PRODUCT_MODEL_ERROR_NOTIFICATION') : '';
	      const basketItemOfferId = this.basketItem.offerId;
	      const facebookFailProducts = this.options.facebookFailProducts;
	      const facebookFailProductErrorText = main_core.Type.isObject(facebookFailProducts) ? facebookFailProducts[basketItemOfferId] : null;
	      if (facebookFailProductErrorText) {
	        if (errorText) {
	          errorText += '<br>';
	        }
	        errorText += main_core.Loc.getMessage('CATALOG_FORM_FACEBOOK_ERROR') + ':<br>' + facebookFailProductErrorText;
	      }
	      return errorText;
	    },
	    hasSku() {
	      return this.basketItem.skuTree !== '';
	    }
	  },
	  // language=Vue
	  template: `
		<div>
			<div class="catalog-pf-product-item" v-bind:class="{ 'catalog-pf-product-item--borderless': !isReadOnly && basketItemIndex === 0 }">
				<div class="catalog-pf-product-item--remove" @click="removeItem" v-if="showRemoveIcon"></div>
				<div class="catalog-pf-product-item--num">
					<div class="catalog-pf-product-index">{{basketItemIndex + 1}}</div>
				</div>
				<div class="catalog-pf-product-item--left">
					<div v-if="isVisibleBlock(blocks.productSelector)" class="catalog-pf-product-item-inline-selector">
						<div v-if="!this.isReadOnly" class="catalog-pf-product-item-section">
							<div class="catalog-pf-product-label">{{localize.CATALOG_FORM_NAME}}</div>
						</div>
						<${config.templateFieldInlineSelector}
							:basketItem="basketItem"
							:basketLength="basketLength"
							:options="options"
							:model="model"
							:editable="isEditableField(blocks.productSelector)"
							@onProductChange="onProductChange"
							@onProductSelect="onProductSelect"
							@onProductClear="onProductClear"
							@saveCatalogField="saveCatalogField"
						/>
					</div>
					<div
						v-if="isVisibleBlock(blocks.brand)"
						class="catalog-pf-product-input-brand-wrapper"
						v-bind:class="[
							{ 'catalog-pf-product-input-brand-wrapper-readonly': this.isReadOnly},
							{ 'catalog-pf-product-input-brand-wrapper-readonly-no-sku': this.isReadOnly && !this.hasSku}
						]"
					>
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
						<div v-if="hasError(errorCodes.emptyBrand)" class="catalog-pf-product-item-section">
							<div class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_BRAND_1}}</div>
						</div>
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
								:selectorId="basketItem.selectorId"
								:price="getPriceValue()"
								:options="options"
								:editable="isEditableField(blocks.price)"
								:hasError="hasError(errorCodes.emptyPrice)"
								@onChangePrice="onChangePrice"
								@saveCatalogField="saveCatalogField"
							/>
						</div>
	
						<div v-if="isVisibleBlock(blocks.quantity)" class="catalog-pf-product-control" style="width: 72px">
							<${config.templateFieldQuantity}
								:quantity="getQuantityValue()"
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
									:sum="getSumValue()"
									:options="options"
									:editable="isEditableField(blocks.result)"
									@onChangeSum="onChangeSum"
							/>
						</div>
					</div>
					<div v-if="hasError(errorCodes.emptyQuantity)" class="catalog-pf-product-item-section">
						<div class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_QUANTITY_1}}</div>
					</div>
					<div v-if="hasError(errorCodes.emptyPrice)" class="catalog-pf-product-item-section">
						<div v-if="isEditableField(blocks.price)" class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_PRICE_1}}</div>
						<div v-else class="catalog-product-error">{{localize.CATALOG_FORM_ERROR_EMPTY_PRICE_FILL_IN_CARD}}</div>
					</div>
					<div v-if="showDiscountBlock" class="catalog-pf-product-item-section">
						<div v-if="showDiscount" class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide" @click="toggleDiscount('N')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
						<div v-else class="catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show" @click="toggleDiscount('Y')">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>
					</div>
	
					<div v-if="showDiscount" class="catalog-pf-product-item-section">
						<${config.templateFieldDiscount}
							:discount="getDiscountValue()"
							:discountType="basketItem.fields.discountType"
							:discountRate="getDiscountRateValue()"
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
				<div class="catalog-pf-product-item">
				</div>
			</div>
			<div>
				<div class="catalog-product-error" v-html="getErrorsText"></div>
			</div>
		</div>
	`
	});

	class FormCompilationType {}
	FormCompilationType.REGULAR = 'REGULAR';
	FormCompilationType.FACEBOOK = 'FACEBOOK';

	class FormHelpdeskCode {}
	FormHelpdeskCode.COMPILATION_FACEBOOK = 13856526;
	FormHelpdeskCode.COMMON_COMPILATION = 13841876;

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9;
	ui_vue.Vue.component(config.templatePanelCompilation, {
	  props: {
	    compilationOptions: Object,
	    mode: String
	  },
	  created() {
	    this.newLabel = new ui_label.Label({
	      text: this.localize.CATALOG_FORM_COMPILATION_PRODUCT_NEW_LABEL,
	      color: ui_label.LabelColor.PRIMARY,
	      fill: true
	    });
	    this.popup = null;
	    this.compilationLink = null;
	    const moreMessageButton = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<a class="ui-btn ui-btn-primary">${0}</a>
		`), this.localize.CATALOG_FORM_COMPILATION_INFO_BUTTON_MORE);
	    main_core.Event.bind(moreMessageButton, 'click', this.openHelpDesk);
	    let header = '';
	    let description = '';
	    if (this.isFacebookForm()) {
	      header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE_FACEBOOK;
	      description = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<p>${0}</p>
				<p>${0}</p>
			`), this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_FIRST_BLOCK, this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_SECOND_BLOCK);
	    } else {
	      header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE;
	      description = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_MARKETING_2;
	    }
	    this.message = new ui_messagecard.MessageCard({
	      id: 'compilationInfo',
	      header,
	      description,
	      angle: false,
	      hidden: true,
	      actionElements: [moreMessageButton]
	    });
	    main_core_events.EventEmitter.subscribe(this.message, 'onClose', this.hideMessage);
	  },
	  mounted() {
	    this.$refs.label.appendChild(this.newLabel.render());
	    this.$refs.message.appendChild(this.message.getLayout());
	    if (!this.compilationOptions.hiddenInfoMessage) {
	      this.showMessage();
	    }
	  },
	  data() {
	    return {
	      compilationLink: null
	    };
	  },
	  methods: {
	    isFacebookForm() {
	      return this.compilationOptions.type === FormCompilationType.FACEBOOK;
	    },
	    openHelpDesk() {
	      this.helpdeskCode = this.isFacebookForm() ? FormHelpdeskCode.COMPILATION_FACEBOOK : FormHelpdeskCode.COMMON_COMPILATION;
	      top.BX.Helper.show('redirect=detail&code=' + this.helpdeskCode);
	    },
	    showPopup(event) {
	      if (this.compilationOptions.disabledSwitcher) {
	        return;
	      }
	      if (this.isFacebookForm()) {
	        this.openHelpDesk();
	        return;
	      }
	      if (this.popup instanceof main_popup.Popup) {
	        this.popup.setBindElement(this.$refs.qrLink);
	        this.popup.show();
	        return;
	      }
	      const basket = this.$store.getters['productList/getBasket']();
	      const productIds = basket.map(basketItem => {
	        var _basketItem$fields;
	        return basketItem == null ? void 0 : (_basketItem$fields = basketItem.fields) == null ? void 0 : _basketItem$fields.skuId;
	      });
	      return new Promise((resolve, reject) => {
	        main_core.ajax.runAction('salescenter.compilation.createCompilation', {
	          data: {
	            productIds,
	            options: {
	              ownerId: this.$root.$app.options.ownerId,
	              ownerTypeId: this.$root.$app.options.ownerTypeId,
	              dialogId: this.$root.$app.options.dialogId,
	              sessionId: this.$root.$app.options.sessionId
	            }
	          }
	        }).then(response => {
	          var _response$data$link, _response$data$compil, _response$data$ownerI;
	          this.compilationLink = (_response$data$link = response.data.link) != null ? _response$data$link : null;
	          main_core_events.EventEmitter.emit(this.$root.$app, 'ProductForm:onCompilationCreated', {
	            compilationId: (_response$data$compil = response.data.compilationId) != null ? _response$data$compil : null,
	            ownerId: (_response$data$ownerI = response.data.ownerId) != null ? _response$data$ownerI : null
	          });
	          this.popup = new main_popup.Popup({
	            bindElement: event.target,
	            content: this.getQRPopupContent(),
	            width: 375,
	            closeIcon: {
	              top: '5px',
	              right: '5px'
	            },
	            padding: 0,
	            closeByEsc: true,
	            autoHide: true,
	            cacheable: true,
	            animation: 'fading-slide',
	            angle: {
	              offset: 30
	            }
	          });
	          this.popup.show();
	          resolve();
	        }).catch(() => reject());
	      });
	    },
	    getQRPopupContent() {
	      if (!this.compilationLink) {
	        return '';
	      }
	      const buttonCopy = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<div class="catalog-pf-product-qr-popup-copy">${0}</div>
			`), this.localize.CATALOG_FORM_COMPILATION_QR_COPY);
	      main_core.Event.bind(buttonCopy, 'click', () => {
	        BX.clipboard.copy(this.compilationLink);
	        BX.UI.Notification.Center.notify({
	          content: this.localize.CATALOG_FORM_COMPILATION_QR_COPY_NOTIFY_MESSAGE,
	          autoHideDelay: 2000
	        });
	      });
	      const qrWrapper = main_core.Tag.render(_t4 || (_t4 = _$1`<div class="catalog-pf-product-qr-popup-image"></div>`));
	      const content = main_core.Tag.render(_t5 || (_t5 = _$1`
					<div class="catalog-pf-product-qr-popup">
						<div class="catalog-pf-product-qr-popup-content">
							<div class="catalog-pf-product-qr-popup-text">${0}</div>
							${0}
							<div class="catalog-pf-product-qr-popup-buttons">
								<a href="${0}" target="_blank" class="ui-btn ui-btn-light-border ui-btn-round">${0}</a>
							</div>
						</div>
						<div class="catalog-pf-product-qr-popup-bottom">
							<a href="${0}" target="_blank" class="catalog-pf-product-qr-popup--url">${0}</a>
							${0}
						</div>
					</div>
				`), this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_TITLE, qrWrapper, this.compilationLink, this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_INPUT_TITLE, this.compilationLink, this.compilationLink, buttonCopy);
	      new QRCode(qrWrapper, {
	        text: this.compilationLink,
	        width: 250,
	        height: 250
	      });
	      return content;
	    },
	    setSetting(event) {
	      const value = event.target.checked ? 'Y' : 'N';
	      this.$root.$app.changeFormOption('isCompilationMode', value);
	    },
	    getOnBeforeCreationStorePopupContent() {
	      const loaderContent = main_core.Tag.render(_t6 || (_t6 = _$1`
				<div class="catalog-product-form-popup--loader-block"></div>
			`));
	      const node = main_core.Tag.render(_t7 || (_t7 = _$1`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${0}</div>
					${0}
					<div class="catalog-product-form-popup--text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING1'), loaderContent, main_core.Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING_INFO1'));
	      const loader = new main_loader.Loader({
	        color: "#2fc6f6",
	        target: loaderContent,
	        size: 40
	      });
	      loader.show();
	      return node;
	    },
	    getOnAfterCreationStorePopupContent(creationStorePopup) {
	      const continueButton = main_core.Tag.render(_t8 || (_t8 = _$1`
				<button class="ui-btn ui-btn-md ui-btn-primary">
					${0}
				</button>
			`), main_core.Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_CONTINUE'));
	      main_core.Event.bind(continueButton, 'click', this.closeCreationStorePopup.bind(this, creationStorePopup));
	      return main_core.Tag.render(_t9 || (_t9 = _$1`
				<div class="catalog-product-form-popup--container">
					<div class="catalog-product-form-popup--title">${0}</div>
					<div class="catalog-product-form-popup--loader-block catalog-product-form-popup--done"></div>
					<div class="catalog-product-form-popup--text">${0}</div>
					<div class="catalog-product-form-popup--button-container">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING1'), main_core.Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_INFO1'), continueButton);
	    },
	    closeCreationStorePopup(creationStorePopup) {
	      creationStorePopup.close();
	    },
	    onLabelClick() {
	      if (this.compilationOptions.isLimitedStore) {
	        BX.UI.InfoHelper.show('limit_sites_number');
	      }
	    },
	    onClickHint(event) {
	      event.preventDefault();
	      event.stopImmediatePropagation();
	      if (!this.message) {
	        return;
	      }
	      if (this.message.isShown()) {
	        this.hideMessage();
	      } else {
	        this.showMessage();
	      }
	    },
	    showMessage() {
	      if (this.message) {
	        main_core.Dom.addClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
	        this.message.show();
	      }
	    },
	    hideMessage() {
	      if (this.message) {
	        main_core.Dom.removeClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
	      }
	      this.message.hide();
	      this.$root.$app.changeFormOption('hiddenCompilationInfoMessage', 'Y');
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    showQrLink() {
	      return this.mode === FormMode.COMPILATION;
	    },
	    ...ui_vue_vuex.Vuex.mapState({
	      productList: state => state.productList
	    })
	  },
	  // language=Vue
	  template: `
		<div>
			<div class="catalog-pf-product-panel-compilation">
				<div class="catalog-pf-product-panel-compilation-wrapper">
					<label class="ui-ctl ui-ctl-checkbox catalog-pf-product-panel-compilation-checkbox-container" @click="onLabelClick">
						<input
							type="checkbox"
							:disabled="compilationOptions.disabledSwitcher"
							class="ui-ctl-element"
							@change="setSetting"
							data-setting-id="isCompilationMode"
						>
						<div class="ui-ctl-label-text">{{localize.CATALOG_FORM_COMPILATION_PRODUCT_SWITCHER_2}}</div>
						<div ref="hintIcon">
							<div data-hint-init="vue" class="ui-hint" @click="onClickHint">
								<span class="ui-hint-icon"></span>
							</div>
						</div>
						<div ref="label"></div>
						<div class="tariff-lock" v-if="compilationOptions.isLimitedStore"></div>
					</label>
				</div>
				<div
					v-if="showQrLink"
					class="catalog-pf-product-panel-compilation-link --icon-qr"
					@click="showPopup"
					ref="qrLink"
				>
					{{localize.CATALOG_FORM_COMPILATION_QR_LINK}}
				</div>
			</div>
			<div class="catalog-pf-product-panel-compilation-price-info">{{localize.CATALOG_FORM_COMPILATION_PRICE_NOTIFICATION}}</div>
			<div class="catalog-pf-product-panel-compilation-message" ref="message"></div>
		</div>
	`
	});

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$1;
	ui_vue.Vue.component(config.templatePanelButtons, {
	  /**
	   * @emits 'changeRowData' {index: number, fields: object}
	   * @emits 'refreshBasket'
	   * @emits 'addItem'
	   */

	  props: {
	    options: Object,
	    mode: String
	  },
	  data() {
	    return {
	      settings: []
	    };
	  },
	  methods: {
	    refreshBasket() {
	      this.$emit('refreshBasket');
	    },
	    changeBasketItem(item) {
	      this.$emit('changeRowData', item);
	    },
	    addBasketItemForm() {
	      this.$emit('addItem');
	    },
	    getInternalIndexByProductId(skuId) {
	      const basket = this.$store.getters['productList/getBasket']();
	      return Object.keys(basket).findIndex(inx => {
	        return parseInt(basket[inx].skuId) === parseInt(skuId);
	      });
	    },
	    handleAddItem(id, params) {
	      const skuType = 4;
	      if (main_core.Text.toNumber(params.type) === skuType) {
	        main_core.ajax.runAction('catalog.productSelector.getSelectedSku', {
	          json: {
	            variationId: id,
	            options: {
	              priceId: this.options.basePriceId,
	              urlBuilder: this.options.urlBuilder,
	              currency: this.options.currency,
	              resetSku: true
	            }
	          }
	        }).then(response => this.processResponse(response, params.isAddAnyway));
	      } else {
	        main_core.ajax.runAction('catalog.productSelector.getProduct', {
	          json: {
	            productId: id,
	            options: {
	              priceId: this.options.basePriceId,
	              urlBuilder: this.options.urlBuilder,
	              currency: this.options.currency
	            }
	          }
	        }).then(response => this.processResponse(response, params.isAddAnyway));
	      }
	    },
	    processResponse(response, isAddAnyway) {
	      const index = isAddAnyway ? -1 : this.getInternalIndexByProductId(response.data.skuId);
	      if (index < 0) {
	        const productData = response.data;
	        const basePrice = main_core.Text.toNumber(productData.fields.BASE_PRICE);
	        productData.fields = productData.fields || {};
	        let newItem = this.$store.getters['productList/getBaseProduct']();
	        newItem.fields = Object.assign(newItem.fields, {
	          price: basePrice,
	          priceExclusive: basePrice,
	          basePrice,
	          name: productData.fields.NAME || '',
	          productId: productData.productId,
	          skuId: productData.skuId,
	          measureCode: productData.fields.MEASURE_CODE,
	          measureName: productData.fields.MEASURE_NAME,
	          measureRatio: productData.fields.MEASURE_RATIO,
	          properties: productData.fields.PROPERTIES,
	          offerId: productData.skuId > 0 ? productData.skuId : productData.productId,
	          module: 'catalog',
	          isCustomPrice: main_core.Type.isNil(productData.fields.PRICE) ? 'Y' : 'N',
	          discountType: this.options.defaultDiscountType
	        });
	        delete productData.fields;
	        newItem = Object.assign(newItem, productData);
	        newItem.sum = basePrice;
	        this.$root.$app.addProduct(newItem);
	      }
	    },
	    onUpdateBasketItem(inx, item) {
	      this.$store.dispatch('productList/changeRowData', {
	        index: inx,
	        fields: item
	      });
	      this.$store.dispatch('productList/changeProduct', {
	        index: inx,
	        fields: item.fields
	      });
	    },
	    /*
	    * By default, basket collection contains a fake|empty item,
	    *  that is deleted when you select items from the catalog.
	    * Also, products can be added to the form and become an empty string,
	    *  while stay a item of basket collection
	    * */
	    removeEmptyItems() {
	      const basket = this.$store.getters['productList/getBasket']();
	      basket.forEach((item, i) => {
	        if (basket[i].name === '' && basket[i].price < 1e-10) {
	          this.$store.commit('productList/deleteItem', {
	            index: i
	          });
	        }
	      });
	    },
	    modifyBasketItem(params) {
	      const skuId = parseInt(params.id);
	      if (skuId > 0) {
	        const index = this.getInternalIndexByProductId(skuId);
	        if (index >= 0) {
	          this.showDialogProductExists(params);
	        } else {
	          this.removeEmptyItems();
	          this.handleAddItem(skuId, params);
	        }
	      }
	    },
	    showDialogProductExists(params) {
	      this.popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: () => {
	            this.popup.destroy();
	          }
	        },
	        zIndex: 4000,
	        autoHide: true,
	        closeByEsc: true,
	        closeIcon: true,
	        titleBar: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TITLE'),
	        draggable: true,
	        resizable: false,
	        lightShadow: true,
	        cacheable: false,
	        overlay: true,
	        content: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TEXT_FOR_DOUBLE').replace('#NAME#', params.name),
	        buttons: this.getButtons(params)
	      });
	      this.popup.show();
	    },
	    getButtons(product) {
	      const buttons = [];
	      const params = product;
	      buttons.push(new BX.UI.SaveButton({
	        text: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_OK'),
	        onclick: () => {
	          const productId = parseInt(params.id);
	          const index = this.getInternalIndexByProductId(productId);
	          if (index >= 0) {
	            this.handleAddItem(productId, {
	              ...params,
	              isAddAnyway: true
	            });
	          }
	          this.popup.destroy();
	        }
	      }));
	      buttons.push(new BX.UI.CancelButton({
	        text: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_NO'),
	        onclick: () => {
	          this.popup.destroy();
	        }
	      }));
	      return buttons;
	    },
	    showDialogProductSearch() {
	      const funcName = 'addBasketItemFromDialogProductSearch';
	      window[funcName] = params => this.modifyBasketItem(params);
	      const popup = new BX.CDialog({
	        content_url: '/bitrix/tools/sale/product_search_dialog.php?' +
	        //todo: 'lang='+this._settings.languageId+
	        //todo: '&LID='+this._settings.siteId+
	        '&caller=order_edit' + '&func_name=' + funcName + '&STORE_FROM_ID=0' + '&public_mode=Y',
	        height: Math.max(500, window.innerHeight - 400),
	        width: Math.max(800, window.innerWidth - 400),
	        draggable: true,
	        resizable: true,
	        min_height: 500,
	        min_width: 800,
	        zIndex: 3100
	      });
	      popup.Show();
	    },
	    setSetting(event) {
	      if (event.target.dataset.settingId === 'taxIncludedOption') {
	        const value = event.target.checked ? 'Y' : 'N';
	        this.$root.$app.changeFormOption('taxIncluded', value);
	      } else if (event.target.dataset.settingId === 'showDiscountInputOption') {
	        const value = event.target.checked ? 'Y' : 'N';
	        this.$root.$app.changeFormOption('showDiscountBlock', value);
	      } else if (event.target.dataset.settingId === 'showTaxInputOption') {
	        const value = event.target.checked ? 'Y' : 'N';
	        this.$root.$app.changeFormOption('showTaxBlock', value);
	      } else if (event.target.dataset.settingId === 'warehouseOption') {
	        const value = event.target.checked ? 'Y' : 'N';
	        if (value === 'Y') {
	          this.popupMenu.close();
	          new catalog_storeUse.Slider().open('/bitrix/components/bitrix/catalog.warehouse.master.clear/slider.php', {}).then(() => {
	            main_core.ajax.runAction('catalog.config.isUsedInventoryManagement', {}).then(response => {
	              const index = this.getSettingItems().findIndex(item => {
	                return item.id === event.target.dataset.settingId;
	              });
	              this.options.warehouseOption = response.data === true;
	              this.settings = this.getSettingItems();
	            });
	          });
	        }
	      }
	    },
	    getSettingItem(item) {
	      var _item$disabled;
	      const input = main_core.Tag.render(_t$2 || (_t$2 = _$2`
					<input type="checkbox"  class="ui-ctl-element">
				`));
	      input.checked = item.checked;
	      input.disabled = (_item$disabled = item.disabled) != null ? _item$disabled : false;
	      input.dataset.settingId = item.id;
	      const hintNode = main_core.Type.isStringFilled(item.hint) ? main_core.Tag.render(_t2$2 || (_t2$2 = _$2`<span class="catalog-product-form-setting-hint" data-hint="${0}"></span>`), item.hint) : '';
	      const setting = main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
				<label class="ui-ctl ui-ctl-checkbox ui-ctl-w100">
					${0}
					<div class="ui-ctl-label-text ${0}">${0}${0}</div>
				</label>
			`), input, item.disabled ? 'catalog-product-form-disabled-setting' : '', item.title, hintNode);
	      BX.UI.Hint.init(setting);
	      main_core.Event.bind(setting, 'change', this.setSetting.bind(this));
	      return setting;
	    },
	    getSettingItems() {
	      const items = [
	      // {
	      // 	id: 'taxIncludedOption',
	      // 	checked: (this.options.taxIncluded === 'Y'),
	      // 	title: this.localize.CATALOG_FORM_ADD_TAX_INCLUDED,
	      // },
	      {
	        id: 'showDiscountInputOption',
	        checked: this.options.showDiscountBlock !== 'N',
	        title: this.localize.CATALOG_FORM_ADD_SHOW_DISCOUNTS_OPTION
	      }
	      // {
	      // 	id: 'showTaxInputOption',
	      // 	checked: (this.options.showTaxBlock !== 'N'),
	      // 	title: this.localize.CATALOG_FORM_ADD_SHOW_TAXES_OPTION,
	      // },
	      ];

	      if (this.options.isCatalogSettingAccess) {
	        items.push({
	          id: 'warehouseOption',
	          checked: this.options.warehouseOption,
	          disabled: this.options.warehouseOption,
	          title: this.localize.CATALOG_FORM_ADD_SHOW_WAREHOUSE_OPTION,
	          hint: this.options.warehouseOption ? this.localize.CATALOG_FORM_ADD_SHOW_WAREHOUSE_HINT : ''
	        });
	      }
	      return items;
	    },
	    prepareSettingsContent() {
	      const content = main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
					<div class='catalog-pf-product-config-popup'></div>
				`));
	      this.settings.forEach(item => {
	        content.append(this.getSettingItem(item));
	      });
	      return content;
	    },
	    showConfigPopup(event) {
	      // if (!this.popupMenu)
	      // {
	      this.popupMenu = new main_popup.Popup(null, event.target, {
	        autoHide: true,
	        draggable: false,
	        offsetLeft: 0,
	        offsetTop: 0,
	        noAllPaddings: true,
	        bindOptions: {
	          forceBindPosition: true
	        },
	        closeByEsc: true,
	        content: this.prepareSettingsContent()
	      });
	      // }

	      this.popupMenu.show();
	    },
	    openSlider(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }
	      options = {
	        ...{
	          cacheable: false,
	          allowChangeHistory: false,
	          events: {}
	        },
	        ...options
	      };
	      return new Promise(resolve => {
	        if (main_core.Type.isString(url) && url.length > 1) {
	          options.events.onClose = function (event) {
	            resolve(event.getSlider());
	          };
	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
	    }
	  },
	  computed: {
	    hasAccessToCatalog() {
	      return this.options.isCatalogAccess;
	    },
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    countItems() {
	      return this.order.basket.length;
	    },
	    ...ui_vue_vuex.Vuex.mapState({
	      productList: state => state.productList
	    })
	  },
	  mounted() {
	    this.settings = this.getSettingItems();
	    BX.UI.Hint.init();
	  },
	  // language=Vue
	  template: `
		<div>
			<div class="catalog-pf-product-add">
				<div class="catalog-pf-product-add-wrapper">
					<span class="catalog-pf-product-add-link" @click="addBasketItemForm">{{localize.CATALOG_FORM_ADD_PRODUCT}}</span>
					<span
						v-if="hasAccessToCatalog"
						class="catalog-pf-product-add-link catalog-pf-product-add-link--gray"
						@click="showDialogProductSearch"
					>{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>
					<span
						v-else
						class="catalog-pf-product-add-link catalog-pf-product-add-link--gray catalog-pf-product-add-link--disabled"
						:data-hint="localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG_DENIED_HINT"
						data-hint-no-icon
					>{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>
				</div>
				<div class="catalog-pf-product-configure-link" @click="showConfigPopup">{{localize.CATALOG_FORM_DISCOUNT_EDIT_PAGE_URL_TITLE}}</div>
			</div>
		</div>
	`
	});

	let _$3 = t => t,
	  _t$3;
	ui_vue.Vue.component(config.templateSummaryTotal, {
	  props: {
	    currency: {
	      type: String,
	      required: true
	    },
	    sum: {
	      required: true
	    },
	    sumAdditionalClass: String,
	    currencyAdditionalClass: String
	  },
	  computed: {
	    formattedSum() {
	      var _this$sumAdditionalCl;
	      const element = main_core.Tag.render(_t$3 || (_t$3 = _$3`<span class="catalog-pf-text ${0}">${0}</span>`), (_this$sumAdditionalCl = this.sumAdditionalClass) != null ? _this$sumAdditionalCl : '', this.sum);
	      return currency_currencyCore.CurrencyCore.getPriceControl(element, this.currency);
	    }
	  },
	  // language=Vue
	  template: `
	<span class="catalog-pf-symbol" :class="currencyAdditionalClass" v-html="formattedSum"></span>
	`
	});

	ui_vue.Vue.component(config.templateName, {
	  props: {
	    options: Object,
	    mode: String
	  },
	  created() {
	    BX.ajax.runAction("catalog.productSelector.getFileInput", {
	      json: {
	        iblockId: this.options.iblockId
	      }
	    });
	  },
	  methods: {
	    refreshBasket() {
	      this.$store.dispatch('productList/refreshBasket');
	    },
	    changeProduct(item) {
	      this.$root.$app.changeProduct(item);
	    },
	    emitErrorsChange() {
	      this.$root.$app.emitErrorsChange();
	    },
	    changeRowData(item) {
	      delete item.product.fields;
	      this.$store.commit('productList/updateItem', item);
	    },
	    removeItem(item) {
	      this.$root.$app.removeProduct(item);
	    },
	    addItem() {
	      this.$root.$app.addProduct();
	    }
	  },
	  computed: {
	    localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    showTaxResult() {
	      return this.options.showTaxBlock !== 'N';
	    },
	    showResults() {
	      return this.options.showResults !== false;
	    },
	    showButtonsTop() {
	      return this.options.singleProductMode !== true && this.mode !== FormMode.READ_ONLY && this.mode !== FormMode.COMPILATION_READ_ONLY && this.options.buttonsPosition !== FormElementPosition.BOTTOM;
	    },
	    showButtonsBottom() {
	      return this.options.singleProductMode !== true && this.mode !== FormMode.READ_ONLY && this.mode !== FormMode.COMPILATION_READ_ONLY && this.options.buttonsPosition === FormElementPosition.BOTTOM;
	    },
	    showResultBlock() {
	      return this.showResults || this.enableAddButtons;
	    },
	    countItems() {
	      return this.productList.basket.length;
	    },
	    totalResultLabel() {
	      return this.options.hasOwnProperty('totalResultLabel') && this.options.totalResultLabel ? this.options.totalResultLabel : this.localize.CATALOG_FORM_TOTAL_RESULT;
	    },
	    ...ui_vue_vuex.Vuex.mapState({
	      productList: state => state.productList
	    })
	  },
	  // language=Vue
	  template: `
	<div class="catalog-product-form-container">
		<${config.templatePanelButtons}
			:options="options"
			:mode="mode"
			@refreshBasket="refreshBasket"
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct"
			v-if="showButtonsTop"
		/>
		<div v-for="(item, index) in productList.basket" :key="item.selectorId">
			<${config.templateRowName}
				:basketItem="item"
				:basketItemIndex="index"
				:basketLength="productList.basket.length"
				:countItems="countItems"
				:options="options"
				:mode="mode"
				@changeProduct="changeProduct"
				@changeRowData="changeRowData"
				@removeItem="removeItem"
				@refreshBasket="refreshBasket"
				@emitErrorsChange="emitErrorsChange"
			/>
		</div>
		<${config.templatePanelButtons}
			:options="options"
			:mode="mode"
			@refreshBasket="refreshBasket"
			@addItem="addItem"
			@changeRowData="changeRowData"
			@changeProduct="changeProduct"
			v-if="showButtonsBottom"
		/>
		<${config.templatePanelCompilation}
			v-if="options.showCompilationModeSwitcher"
			:compilationOptions="options.compilationFormOption"
			:mode="mode"
		/>
		<div class="catalog-pf-result-line"></div>
		<div class="catalog-pf-result-wrapper" v-if="showResultBlock">
			<table class="catalog-pf-result">
				<tr>
					<td>
						<span class="catalog-pf-text">{{localize.CATALOG_FORM_PRODUCTS_PRICE}}:</span>
					</td>
					<td>
						<${config.templateSummaryTotal}
							:sum="productList.total.sum"
							:currency="options.currency"
							:sumAdditionalClass="productList.total.result !== productList.total.sum ? 'catalog-pf-text--line-through' : ''"
						/>
					</td>
				</tr>
				<tr>
					<td class="catalog-pf-result-padding-bottom">
						<span class="catalog-pf-text catalog-pf-text--discount">{{localize.CATALOG_FORM_TOTAL_DISCOUNT}}:</span>
					</td>
					<td class="catalog-pf-result-padding-bottom">
						<${config.templateSummaryTotal}
							:sum="productList.total.discount"
							:currency="options.currency"
							:sumAdditionalClass="'catalog-pf-text--discount'"
						/>
					</td>
				</tr>
				<tr v-if="showTaxResult">
					<td class="catalog-pf-tax">
						<span class="catalog-pf-text catalog-pf-text--tax">{{localize.CATALOG_FORM_TAX_TITLE}}:</span>
					</td>
					<td class="catalog-pf-tax">
						<${config.templateSummaryTotal}
							:sum="productList.total.taxSum"
							:currency="options.currency"
							:sumAdditionalClass="'catalog-pf-text--tax'"
						/>
					</td>
				</tr>
				<tr>
					<td class="catalog-pf-result-padding">
						<span class="catalog-pf-text catalog-pf-text--total catalog-pf-text--border">{{totalResultLabel}}:</span>
					</td>
					<td class="catalog-pf-result-padding">
						<${config.templateSummaryTotal}
							:sum="productList.total.result"
							:currency="options.currency"
							:sumAdditionalClass="'catalog-pf-text--total'"
							:currencyAdditionalClass="'catalog-pf-symbol--total'"
						/>
					</td>
				</tr>
			</table>
		</div>
	</div>
`
	});

	let _$4 = t => t,
	  _t$4;
	var _onBasketChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onBasketChange");
	var _checkRequiredFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkRequiredFields");
	var _changeCompilationModeSetting = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changeCompilationModeSetting");
	var _setMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMode");
	class ProductForm {
	  constructor(options = {}) {
	    Object.defineProperty(this, _setMode, {
	      value: _setMode2
	    });
	    Object.defineProperty(this, _changeCompilationModeSetting, {
	      value: _changeCompilationModeSetting2
	    });
	    Object.defineProperty(this, _checkRequiredFields, {
	      value: _checkRequiredFields2
	    });
	    Object.defineProperty(this, _onBasketChange, {
	      value: _onBasketChange2
	    });
	    this.options = this.prepareOptions(options);
	    this.defaultOptions = Object.assign({}, this.options);
	    this.editable = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](FormMode.REGULAR);
	    this.wrapper = main_core.Tag.render(_t$4 || (_t$4 = _$4`<div class=""></div>`));
	    if (main_core.Text.toNumber(options.iblockId) <= 0) {
	      return;
	    }
	    ProductForm.initStore().then(result => this.initTemplate(result)).catch(error => ProductForm.showError(error));
	  }
	  static initStore() {
	    const builder = new ui_vue_vuex.VuexBuilder();
	    return builder.addModel(ProductList.create()).build();
	  }
	  prepareOptions(options = {}) {
	    const settingsCollection = main_core.Extension.getSettings('catalog.product-form');
	    const defaultOptions = {
	      basket: [],
	      measures: [],
	      iblockId: null,
	      basePriceId: settingsCollection.get('basePriceId'),
	      taxList: [],
	      singleProductMode: false,
	      showResults: true,
	      showCompilationModeSwitcher: false,
	      enableEmptyProductError: true,
	      isShortProductViewFormat: false,
	      pricePrecision: 2,
	      currency: settingsCollection.get('currency'),
	      currencySymbol: settingsCollection.get('currencySymbol'),
	      taxIncluded: settingsCollection.get('taxIncluded'),
	      warehouseOption: settingsCollection.get('warehouseOption'),
	      showDiscountBlock: settingsCollection.get('showDiscountBlock'),
	      showTaxBlock: settingsCollection.get('showTaxBlock'),
	      allowedDiscountTypes: [catalog_productCalculator.DiscountType.PERCENTAGE, catalog_productCalculator.DiscountType.MONETARY],
	      visibleBlocks: [FormInputCode.PRODUCT_SELECTOR, FormInputCode.IMAGE_EDITOR, FormInputCode.PRICE, FormInputCode.QUANTITY, FormInputCode.RESULT, FormInputCode.DISCOUNT],
	      requiredFields: [],
	      editableFields: [],
	      newItemPosition: FormElementPosition.TOP,
	      buttonsPosition: FormElementPosition.TOP,
	      urlBuilderContext: 'SHOP',
	      hideUnselectedProperties: false,
	      isCatalogDiscountSetEnabled: settingsCollection.get('isCatalogDiscountSetEnabled'),
	      isCatalogPriceEditEnabled: settingsCollection.get('isCatalogPriceEditEnabled'),
	      isCatalogPriceSaveEnabled: settingsCollection.get('isCatalogPriceSaveEnabled'),
	      isCatalogSettingAccess: settingsCollection.get('isCatalogSettingAccess'),
	      isCatalogAccess: settingsCollection.get('isCatalogAccess'),
	      fieldHints: settingsCollection.get('fieldHints'),
	      compilationFormType: FormCompilationType.REGULAR,
	      compilationFormOption: {},
	      facebookFailProducts: null,
	      ownerId: null,
	      ownerTypeId: null,
	      dialogId: null,
	      sessionId: null
	    };
	    if (options.visibleBlocks && !main_core.Type.isArray(options.visibleBlocks)) {
	      delete options.visibleBlocks;
	    }
	    if (options.requiredFields && !main_core.Type.isArray(options.requiredFields)) {
	      delete options.requiredFields;
	    }
	    options = {
	      ...defaultOptions,
	      ...options
	    };
	    options.showTaxBlock = 'N';
	    if (settingsCollection.get('isEnabledLanding')) {
	      options.compilationFormOption = {
	        type: options.compilationFormType,
	        hasStore: settingsCollection.get('hasLandingStore'),
	        isLimitedStore: settingsCollection.get('isLimitedLandingStore'),
	        disabledSwitcher: settingsCollection.get('isLimitedLandingStore'),
	        hiddenInfoMessage: settingsCollection.get('hiddenCompilationInfoMessage')
	      };
	    } else {
	      options.showCompilationModeSwitcher = false;
	    }
	    options.defaultDiscountType = '';
	    if (main_core.Type.isArray(options.allowedDiscountTypes)) {
	      if (options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.PERCENTAGE)) {
	        options.defaultDiscountType = catalog_productCalculator.DiscountType.PERCENTAGE;
	      } else if (options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.MONETARY)) {
	        options.defaultDiscountType = catalog_productCalculator.DiscountType.MONETARY;
	      }
	    }
	    return options;
	  }
	  layout() {
	    return this.wrapper;
	  }
	  initTemplate(result) {
	    return new Promise(resolve => {
	      const context = this;
	      this.store = result.store;
	      this.templateEngine = ui_vue.BitrixVue.createApp({
	        el: this.wrapper,
	        store: this.store,
	        data: {
	          options: this.options,
	          mode: this.mode
	        },
	        created() {
	          this.$app = context;
	        },
	        mounted() {
	          resolve();
	        },
	        template: `<${config.templateName} :options="options" :mode="mode"/>`
	      });
	      if (main_core.Type.isStringFilled(this.options.currency)) {
	        this.setData({
	          currency: this.options.currency
	        });
	        currency_currencyCore.CurrencyCore.loadCurrencyFormat(this.options.currency);
	      }
	      if (this.options.basket.length > 0) {
	        this.setData({
	          basket: this.options.basket
	        }, {
	          newItemPosition: FormElementPosition.BOTTOM
	        });
	        if (main_core.Type.isObject(this.options.totals)) {
	          this.store.commit('productList/setTotal', this.options.totals);
	        } else {
	          this.store.dispatch('productList/calculateTotal');
	        }
	      } else {
	        const newItem = this.store.getters['productList/getBaseProduct']();
	        newItem.fields.discountType = this.options.defaultDiscountType;
	        this.addProduct(newItem);
	      }
	      main_core_events.EventEmitter.emit(this, 'onAfterInit');
	    });
	  }
	  addProduct(item = {}) {
	    this.store.dispatch('productList/addItem', {
	      item,
	      position: this.options.newItemPosition
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onBasketChange)[_onBasketChange]();
	    });
	  }
	  emitErrorsChange() {
	    main_core_events.EventEmitter.emit(this, 'ProductForm:onErrorsChange');
	  }
	  changeProduct(item) {
	    const product = item.product;
	    product.errors = [];
	    if (item.skipFieldChecking !== true) {
	      const result = babelHelpers.classPrivateFieldLooseBase(this, _checkRequiredFields)[_checkRequiredFields](product);
	      product.errors = (result == null ? void 0 : result.errors) || [];
	    }
	    this.store.dispatch('productList/changeItem', {
	      index: item.index,
	      product
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onBasketChange)[_onBasketChange]();
	    });
	  }
	  removeProduct(product) {
	    this.store.dispatch('productList/removeItem', {
	      index: product.index
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onBasketChange)[_onBasketChange]();
	    });
	  }
	  setData(data, option = {}) {
	    if (main_core.Type.isObject(data.basket)) {
	      const formBasket = this.store.getters['productList/getBasket']();
	      data.basket.forEach(fields => {
	        if (!main_core.Type.isObject(fields)) {
	          return;
	        }
	        const itemPosition = option.newItemPosition || this.options.newItemPosition;
	        const innerId = fields.selectorId;
	        if (main_core.Type.isNil(innerId)) {
	          this.store.dispatch('productList/addItem', {
	            item: fields,
	            position: itemPosition
	          });
	          return;
	        }
	        const basketIndex = formBasket.findIndex(item => item.selectorId === innerId);
	        if (basketIndex === -1) {
	          this.store.dispatch('productList/addItem', {
	            item: fields,
	            position: itemPosition
	          });
	        } else {
	          this.store.dispatch('productList/changeItem', {
	            basketIndex,
	            fields
	          });
	        }
	      });
	    }
	    if (main_core.Type.isStringFilled(data.currency)) {
	      this.store.dispatch('productList/setCurrency', data.currency);
	    }
	    if (main_core.Type.isObject(data.total)) {
	      this.store.commit('productList/setTotal', {
	        sum: data.total.sum,
	        taxSum: data.total.taxSum,
	        discount: data.total.discount,
	        result: data.total.result
	      });
	    }
	    if (main_core.Type.isObject(data.errors)) {
	      this.store.commit('productList/setErrors', data.errors);
	    }
	  }
	  changeFormOption(optionName, value) {
	    value = value === 'Y' ? 'Y' : 'N';
	    if (optionName === 'isCompilationMode') {
	      if (!this.options.showCompilationModeSwitcher) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(this, 'onChangeCompilationMode', {
	        isCompilationMode: value === 'Y',
	        isFacebookForm: this.options.compilationFormType === FormCompilationType.FACEBOOK
	      });
	      const mode = value === 'Y' ? FormMode.COMPILATION : FormMode.REGULAR;
	      babelHelpers.classPrivateFieldLooseBase(this, _changeCompilationModeSetting)[_changeCompilationModeSetting](mode);
	      return;
	    }
	    this.options[optionName] = value;
	    if (optionName !== 'hiddenCompilationInfoMessage') {
	      const basket = this.store.getters['productList/getBasket']();
	      basket.forEach((item, index) => {
	        if (optionName === 'showDiscountBlock') {
	          item.showDiscountBlock = value;
	        } else if (optionName === 'showTaxBlock') {
	          item.showTaxBlock = value;
	        } else if (optionName === 'taxIncluded') {
	          item.fields.taxIncluded = value;
	        }
	        this.store.dispatch('productList/changeItem', {
	          index,
	          fields: item
	        });
	      });
	    }
	    main_core.ajax.runAction('catalog.productForm.setConfig', {
	      data: {
	        configName: optionName,
	        value: value
	      }
	    });
	  }
	  getTotal() {
	    this.store.dispatch('productList/getTotal');
	  }
	  setEditable(editable, isCompilationMode) {
	    this.editable = editable;
	    if (!editable && !isCompilationMode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](FormMode.READ_ONLY);
	    } else if (!editable && isCompilationMode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](FormMode.COMPILATION_READ_ONLY);
	    } else if (editable && isCompilationMode) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](FormMode.COMPILATION);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](FormMode.REGULAR);
	    }
	  }
	  hasErrors() {
	    if (!this.store) {
	      return false;
	    }
	    const basket = this.store.getters['productList/getBasket']();
	    const errorItems = basket.filter(item => item.errors.length > 0);
	    return errorItems.length > 0;
	  }
	  static showError(error) {
	    console.error(error);
	  }
	}
	function _onBasketChange2() {
	  main_core_events.EventEmitter.emit(this, 'ProductForm:onBasketChange', {
	    basket: this.store.getters['productList/getBasket']()
	  });
	}
	function _checkRequiredFields2(product) {
	  const result = {};
	  if (!main_core.Type.isArray(this.options.requiredFields) || this.options.requiredFields.length === 0) {
	    return result;
	  }
	  result.errors = [];
	  this.options.requiredFields.forEach(code => {
	    switch (code) {
	      case FormInputCode.PRICE:
	        if (!this.options.isCatalogPriceSaveEnabled && product.catalogPrice <= 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_PRICE,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_PRICE_FILL_IN_CARD')
	          });
	        } else if (product.fields.basePrice <= 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_PRICE,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_PRICE_1')
	          });
	        }
	        break;
	      case FormInputCode.QUANTITY:
	        if (product.fields.quantity <= 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_QUANTITY,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_QUANTITY_1')
	          });
	        }
	        break;
	      case FormInputCode.BRAND:
	        if (!main_core.Type.isArray(product.fields.brands) || product.fields.brands.length === 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_BRAND,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_BRAND_1')
	          });
	        }
	        break;
	      case FormInputCode.IMAGE_EDITOR:
	        if (!main_core.Type.isObject(product.fields.morePhoto) || Object.keys(product.fields.morePhoto).length === 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_IMAGE,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_PICTURE_1')
	          });
	        }
	        break;
	    }
	  });
	  return result;
	}
	function _changeCompilationModeSetting2(mode) {
	  babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](mode);
	  const basket = this.store.getters['productList/getBasket']();
	  basket.forEach((item, index) => this.changeProduct({
	    index,
	    product: item,
	    skipFieldChecking: basket.length === 1 && index === 0 && item.offerId === null
	  }));
	}
	function _setMode2(mode) {
	  this.mode = mode;
	  if (mode === FormMode.READ_ONLY) {
	    this.options.editableFields = [];
	  } else if (mode === FormMode.COMPILATION_READ_ONLY) {
	    this.options.editableFields = [];
	    this.options.visibleBlocks = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.IMAGE_EDITOR, FormInputCode.PRICE, FormInputCode.BRAND];
	    this.options.showResults = false;
	  } else if (mode === FormMode.COMPILATION) {
	    this.options.editableFields = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.BRAND];
	    this.options.visibleBlocks = this.defaultOptions.visibleBlocks;
	    if (this.options.compilationFormType === FormCompilationType.FACEBOOK) {
	      this.options.visibleBlocks = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.IMAGE_EDITOR, FormInputCode.PRICE, FormInputCode.BRAND];
	    } else {
	      this.options.visibleBlocks = this.defaultOptions.visibleBlocks;
	    }
	    this.options.showResults = false;
	  } else {
	    mode = FormMode.REGULAR;
	    this.options.visibleBlocks = this.defaultOptions.visibleBlocks;
	    this.options.showResults = this.defaultOptions.showResults;
	    this.options.editableFields = this.defaultOptions.visibleBlocks;
	  }
	  if (this.templateEngine) {
	    this.templateEngine.mode = mode;
	  }
	  this.options.requiredFields = [];
	  if (mode === FormMode.COMPILATION) {
	    let compilationRequiredFields = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE];
	    if (this.options.compilationFormType === FormCompilationType.FACEBOOK) {
	      compilationRequiredFields.push(FormInputCode.IMAGE_EDITOR);
	      compilationRequiredFields.push(FormInputCode.BRAND);
	    }
	    this.options.requiredFields = this.options.visibleBlocks.filter(item => compilationRequiredFields.includes(item));
	  }
	  main_core_events.EventEmitter.emit(this, 'ProductForm:onModeChange', {
	    mode
	  });
	}

	exports.ProductForm = ProductForm;
	exports.FormMode = FormMode;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX,BX,BX.UI,BX,BX.UI,BX,BX.UI,BX.Catalog,BX.UI.EntitySelector,BX.Catalog,BX,BX.Main,BX,BX.UI,BX.UI,window,BX,BX,BX,BX,BX,BX.Catalog.StoreUse,BX,BX,BX,BX.Event,BX.Currency,BX.Catalog));
//# sourceMappingURL=product-form.bundle.js.map
