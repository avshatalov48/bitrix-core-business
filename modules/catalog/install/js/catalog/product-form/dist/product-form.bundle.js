this.BX = this.BX || {};
(function (exports,currency,ui_layoutForm,ui_forms,ui_buttons,ui_common,ui_alerts,catalog_productSelector,ui_entitySelector,ui_vue_vuex,ui_vue,main_popup,main_core,main_loader,ui_label,ui_messagecard,ui_vue_components_hint,ui_notification,ui_infoHelper,main_qrcode,clipboard,helper,main_core_events,currency_currencyCore,catalog_productCalculator) {
	'use strict';

	var FormElementPosition = function FormElementPosition() {
	  babelHelpers.classCallCheck(this, FormElementPosition);
	};
	babelHelpers.defineProperty(FormElementPosition, "TOP", 'TOP');
	babelHelpers.defineProperty(FormElementPosition, "BOTTOM", 'BOTTOM');

	var ProductList = /*#__PURE__*/function (_VuexBuilderModel) {
	  babelHelpers.inherits(ProductList, _VuexBuilderModel);

	  function ProductList() {
	    babelHelpers.classCallCheck(this, ProductList);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductList).apply(this, arguments));
	  }

	  babelHelpers.createClass(ProductList, [{
	    key: "getName",

	    /**
	     * @inheritDoc
	     */
	    value: function getName() {
	      return 'productList';
	    }
	  }, {
	    key: "getState",
	    value: function getState() {
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
	  }, {
	    key: "getActions",
	    value: function getActions() {
	      return {
	        resetBasket: function resetBasket(_ref) {
	          var commit = _ref.commit;
	          commit('clearBasket');
	          commit('addItem', {});
	        },
	        removeItem: function removeItem(_ref2, payload) {
	          var dispatch = _ref2.dispatch,
	              commit = _ref2.commit,
	              state = _ref2.state;
	          commit('deleteItem', payload);

	          if (state.basket.length === 0) {
	            commit('addItem', {});
	          } else {
	            state.basket.forEach(function (item, i) {
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
	        changeItem: function changeItem(_ref3, payload) {
	          var dispatch = _ref3.dispatch,
	              commit = _ref3.commit;
	          commit('updateItem', payload);
	          dispatch('calculateTotal');
	        },
	        setCurrency: function setCurrency(_ref4, payload) {
	          var commit = _ref4.commit;
	          var currency$$1 = payload || '';
	          commit('setCurrency', currency$$1);
	        },
	        addItem: function addItem(_ref5, payload) {
	          var dispatch = _ref5.dispatch,
	              commit = _ref5.commit;
	          var item = payload.item || {
	            fields: {}
	          };
	          commit('addItem', {
	            item: item,
	            position: payload.position || FormElementPosition.TOP
	          });
	          dispatch('calculateTotal');
	        },
	        calculateTotal: function calculateTotal(_ref6) {
	          var commit = _ref6.commit,
	              state = _ref6.state;
	          var total = {
	            sum: 0,
	            taxSum: 0,
	            discount: 0,
	            result: 0
	          };
	          state.basket.forEach(function (item) {
	            var basePrice = main_core.Text.toNumber(item.fields.basePrice || 0);
	            var quantity = main_core.Text.toNumber(item.fields.quantity || 0);
	            var discount = main_core.Text.toNumber(item.fields.discount || 0);
	            var taxSum = main_core.Text.toNumber(item.fields.taxSum || 0);
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
	  }, {
	    key: "getGetters",
	    value: function getGetters() {
	      return {
	        getBasket: function getBasket(state) {
	          return function () {
	            return state.basket;
	          };
	        },
	        getBaseProduct: function getBaseProduct() {
	          return function () {
	            return ProductList.getBaseProduct();
	          };
	        }
	      };
	    }
	  }, {
	    key: "getMutations",
	    value: function getMutations() {
	      return {
	        addItem: function addItem(state, payload) {
	          var item = ProductList.getBaseProduct();
	          item = Object.assign(item, payload.item);

	          if (payload.position === FormElementPosition.BOTTOM) {
	            state.basket.push(item);
	          } else {
	            state.basket.unshift(item);
	          }

	          state.basket.forEach(function (item, index) {
	            item.fields.sort = index;
	          });
	        },
	        updateItem: function updateItem(state, payload) {
	          if (typeof state.basket[payload.index] === 'undefined') {
	            ui_vue.Vue.set(state.basket, payload.index, ProductList.getBaseProduct());
	          }

	          state.basket[payload.index] = Object.assign(state.basket[payload.index], payload.fields);
	        },
	        clearBasket: function clearBasket(state) {
	          state.basket = [];
	        },
	        deleteItem: function deleteItem(state, payload) {
	          state.basket.splice(payload.index, 1);
	          state.basket.forEach(function (item, index) {
	            item.fields.sort = index;
	          });
	        },
	        setErrors: function setErrors(state, payload) {
	          state.errors = payload;
	        },
	        clearErrors: function clearErrors(state) {
	          state.errors = [];
	        },
	        setCurrency: function setCurrency(state, payload) {
	          state.currency = payload;
	        },
	        setTotal: function setTotal(state, payload) {
	          var formattedTotal = payload;

	          if (main_core.Type.isStringFilled(state.currency)) {
	            for (var key in payload) {
	              if (payload.hasOwnProperty(key)) {
	                formattedTotal[key] = currency_currencyCore.CurrencyCore.currencyFormat(payload[key], state.currency);
	              }
	            }
	          }

	          state.total = Object.assign(state.total, formattedTotal);
	        }
	      };
	    }
	  }], [{
	    key: "getBaseProduct",
	    value: function getBaseProduct() {
	      var random = main_core.Text.getRandom();
	      return {
	        offerId: null,
	        selectorId: random,
	        fields: {
	          innerId: random,
	          productId: null,
	          skuId: null,
	          code: null,
	          module: null,
	          sort: 0,
	          price: 0,
	          basePrice: 0,
	          priceExclusive: 0,
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
	          brands: []
	        },
	        calculatedFields: [],
	        showDiscount: 'N',
	        showTax: 'N',
	        skuTree: [],
	        image: null,
	        sum: 0,
	        discountSum: 0,
	        detailUrl: '',
	        encodedFields: null,
	        errors: []
	      };
	    }
	  }]);
	  return ProductList;
	}(ui_vue_vuex.VuexBuilderModel);

	var config = Object.freeze({
	  databaseConfig: {
	    name: 'catalog.product-form'
	  },
	  templateName: 'bx-form',
	  templatePanelButtons: 'bx-panel-buttons',
	  templatePanelCompilation: 'bx-panel-compilation',
	  templateRowName: 'bx-form-row',
	  templateFieldInlineSelector: 'bx-field-inline-selector',
	  templateFieldPrice: 'bx-field-price',
	  templateFieldQuantity: 'bx-field-quantity',
	  templateFieldDiscount: 'bx-field-discount',
	  templateFieldTax: 'bx-field-tax',
	  templateFieldBrand: 'bx-field-brand',
	  moduleId: 'catalog'
	});

	var FormInputCode = function FormInputCode() {
	  babelHelpers.classCallCheck(this, FormInputCode);
	};
	babelHelpers.defineProperty(FormInputCode, "PRODUCT_SELECTOR", 'product-selector');
	babelHelpers.defineProperty(FormInputCode, "IMAGE_EDITOR", 'image-editor');
	babelHelpers.defineProperty(FormInputCode, "QUANTITY", 'quantity');
	babelHelpers.defineProperty(FormInputCode, "PRICE", 'price');
	babelHelpers.defineProperty(FormInputCode, "RESULT", 'result');
	babelHelpers.defineProperty(FormInputCode, "DISCOUNT", 'discount');
	babelHelpers.defineProperty(FormInputCode, "TAX", 'tax');
	babelHelpers.defineProperty(FormInputCode, "BRAND", 'brand');

	var FormErrorCode = function FormErrorCode() {
	  babelHelpers.classCallCheck(this, FormErrorCode);
	};
	babelHelpers.defineProperty(FormErrorCode, "EMPTY_PRODUCT_SELECTOR", 0);
	babelHelpers.defineProperty(FormErrorCode, "EMPTY_IMAGE", 1);
	babelHelpers.defineProperty(FormErrorCode, "EMPTY_QUANTITY", 2);
	babelHelpers.defineProperty(FormErrorCode, "EMPTY_PRICE", 3);
	babelHelpers.defineProperty(FormErrorCode, "EMPTY_BRAND", 4);

	var FormMode = function FormMode() {
	  babelHelpers.classCallCheck(this, FormMode);
	};
	babelHelpers.defineProperty(FormMode, "REGULAR", 'REGULAR');
	babelHelpers.defineProperty(FormMode, "READ_ONLY", 'READ_ONLY');
	babelHelpers.defineProperty(FormMode, "COMPILATION", 'COMPILATION');

	ui_vue.Vue.component(config.templateFieldQuantity, {
	  /**
	   * @emits 'changeQuantity' {quantity: number}
	   * @emits 'changeMeasure' {quantity: number, }
	   */
	  props: {
	    measureCode: Number,
	    measureRatio: Number,
	    measureName: String,
	    quantity: Number,
	    editable: Boolean,
	    hasError: Boolean,
	    options: Object
	  },
	  created: function created() {
	    this.onInputQuantityHandler = main_core.Runtime.debounce(this.onInputQuantity, 500, this);
	  },
	  methods: {
	    onInputQuantity: function onInputQuantity(event) {
	      if (!this.editable) {
	        return;
	      }

	      event.target.value = event.target.value.replace(/[^.\d]/g, '.');
	      var newQuantity = parseFloat(event.target.value);
	      var lastSymbol = event.target.value.substr(-1);

	      if (lastSymbol === '.') {
	        return;
	      }

	      this.changeQuantity(newQuantity);
	    },
	    calculateCorrectionFactor: function calculateCorrectionFactor(quantity, measureRatio) {
	      var factoredQuantity = quantity;
	      var factoredRatio = measureRatio;
	      var correctionFactor = 1;

	      while (!(Number.isInteger(factoredQuantity) && Number.isInteger(factoredRatio))) {
	        correctionFactor *= 10;
	        factoredQuantity = quantity * correctionFactor;
	        factoredRatio = measureRatio * correctionFactor;
	      }

	      return correctionFactor;
	    },
	    incrementValue: function incrementValue() {
	      if (!this.editable) {
	        return;
	      }

	      var correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
	      var quantity = (this.quantity * correctionFactor + this.measureRatio * correctionFactor) / correctionFactor;
	      this.changeQuantity(quantity);
	    },
	    decrementValue: function decrementValue() {
	      if (this.quantity > this.measureRatio && this.editable) {
	        var correctionFactor = this.calculateCorrectionFactor(this.quantity, this.measureRatio);
	        var quantity = (this.quantity * correctionFactor - this.measureRatio * correctionFactor) / correctionFactor;
	        this.changeQuantity(quantity);
	      }
	    },
	    changeQuantity: function changeQuantity(value) {
	      this.$emit('changeQuantity', value);
	    },
	    showPopupMenu: function showPopupMenu(target) {
	      var _this = this;

	      if (!this.editable || !main_core.Type.isArray(this.options.measures)) {
	        return;
	      }

	      var menuItems = [];
	      this.options.measures.forEach(function (item) {
	        menuItems.push({
	          text: item.SYMBOL,
	          item: item,
	          onclick: _this.changeMeasure
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
	    changeMeasure: function changeMeasure(event, params) {
	      this.$emit('changeMeasure', {
	        code: param.options.item.CODE,
	        name: param.options.item.SYMBOL
	      });

	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-input-wrapper\" v-bind:class=\"{ 'ui-ctl-danger': hasError }\">\n\t\t\t<input \t\n\t\t\t\ttype=\"text\" class=\"catalog-pf-product-input\"\n\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t:value=\"quantity\"\n\t\t\t\t@input=\"onInputQuantity\"\n\t\t\t\t:disabled=\"!editable\"\n\t\t\t>\n\t\t\t<div \n\t\t\t\tclass=\"catalog-pf-product-input-info catalog-pf-product-input-info--action\" \n\t\t\t\t@click=\"showPopupMenu($event.target)\"\n\t\t\t>\n\t\t\t\t<span>{{ measureName }}</span>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	ui_vue.Vue.component(config.templateFieldPrice, {
	  /**
	   * @emits 'changePrice' {price: number}
	   */
	  props: {
	    basePrice: Number,
	    editable: Boolean,
	    hasError: Boolean,
	    options: Object
	  },
	  created: function created() {
	    this.onInputPriceHandler = main_core.Runtime.debounce(this.onInputPrice, 500, this);
	  },
	  methods: {
	    onInputPrice: function onInputPrice(event) {
	      if (!this.editable) {
	        return;
	      }

	      event.target.value = event.target.value.replace(/[^.,\d]/g, '');

	      if (event.target.value === '') {
	        event.target.value = 0;
	      }

	      var lastSymbol = event.target.value.substr(-1);

	      if (lastSymbol === ',') {
	        event.target.value = event.target.value.replace(',', ".");
	      }

	      var newPrice = parseFloat(event.target.value);

	      if (newPrice < 0 || lastSymbol === '.' || lastSymbol === ',') {
	        return;
	      }

	      this.$emit('changePrice', newPrice);
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    currencySymbol: function currencySymbol() {
	      return this.options.currencySymbol || '';
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-input-wrapper\" v-bind:class=\"{ 'ui-ctl-danger': hasError }\">\n\t\t\t<input \ttype=\"text\" class=\"catalog-pf-product-input catalog-pf-product-input--align-right\"\n\t\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t\t:value=\"basePrice\"\n\t\t\t\t\t@input=\"onInputPriceHandler\"\n\t\t\t\t\t:disabled=\"!editable\">\n\t\t\t<div class=\"catalog-pf-product-input-info\" v-html=\"currencySymbol\"></div>\n\t\t</div>\n\t"
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
	  created: function created() {
	    this.onInputDiscount = main_core.Runtime.debounce(this.onChangeDiscount, 500, this);
	    this.currencySymbol = this.options.currencySymbol;
	  },
	  methods: {
	    onChangeType: function onChangeType(event, params) {
	      var _params$options;

	      if (!this.editable) {
	        return;
	      }

	      var type = main_core.Text.toNumber(params === null || params === void 0 ? void 0 : (_params$options = params.options) === null || _params$options === void 0 ? void 0 : _params$options.type) === catalog_productCalculator.DiscountType.MONETARY ? catalog_productCalculator.DiscountType.MONETARY : catalog_productCalculator.DiscountType.PERCENTAGE;
	      this.$emit('changeDiscountType', type);

	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    },
	    onChangeDiscount: function onChangeDiscount(event) {
	      var discountValue = main_core.Text.toNumber(event.target.value) || 0;

	      if (discountValue === main_core.Text.toNumber(this.discount) || !this.editable) {
	        return;
	      }

	      this.$emit('changeDiscount', discountValue);
	    },
	    showPopupMenu: function showPopupMenu(target) {
	      if (!this.editable || !main_core.Type.isArray(this.options.allowedDiscountTypes)) {
	        return;
	      }

	      var menuItems = [];

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
	    getDiscountInputValue: function getDiscountInputValue() {
	      if (main_core.Text.toNumber(this.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE) {
	        return main_core.Text.toNumber(this.discountRate);
	      }

	      return main_core.Text.toNumber(this.discount);
	    },
	    getDiscountSymbol: function getDiscountSymbol() {
	      return main_core.Text.toNumber(this.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE ? '%' : this.currencySymbol;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--left\">\n\t\t\t<input class=\"catalog-pf-product-input catalog-pf-product-input--align-right catalog-pf-product-input--right\"\n\t\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t\tref=\"discountInput\" \n\t\t\t\t\t:value=\"getDiscountInputValue\"\n\t\t\t\t\t@input=\"onInputDiscount\"\n\t\t\t\t\tplaceholder=\"0\"\n\t\t\t\t\t:disabled=\"!editable\">\n\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input-info--action\" \n\t\t\t\t@click=\"showPopupMenu\">\n\t\t\t\t<span v-html=\"getDiscountSymbol\"></span>\n\t\t\t</div>\n\t\t</div>\n\t"
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
	  data: function data() {
	    return {
	      taxValue: this.getTaxList()[this.taxId] || 0
	    };
	  },
	  methods: {
	    onChangeValue: function onChangeValue(event, params) {
	      var _params$options, _params$options2;

	      var taxValue = main_core.Text.toNumber(params === null || params === void 0 ? void 0 : (_params$options = params.options) === null || _params$options === void 0 ? void 0 : _params$options.item);

	      if (taxValue === main_core.Text.toNumber(this.taxValue) || !this.editable) {
	        return;
	      }

	      this.$emit('changeTax', {
	        taxValue: taxValue,
	        taxId: params === null || params === void 0 ? void 0 : (_params$options2 = params.options) === null || _params$options2 === void 0 ? void 0 : _params$options2.id
	      });

	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }
	    },
	    getTaxList: function getTaxList() {
	      return main_core.Type.isArray(this.options.taxList) ? this.options.taxList : [];
	    },
	    showPopupMenu: function showPopupMenu(target) {
	      var _this = this;

	      if (!this.editable || !main_core.Type.isArray(this.options.taxList)) {
	        return;
	      }

	      var menuItems = [];
	      this.options.taxList.forEach(function (item, id) {
	        menuItems.push({
	          id: id,
	          text: item + '%',
	          item: item,
	          onclick: _this.onChangeValue
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
	  template: "\n\t\t<div class=\"catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--right\" @click=\"showPopupMenu\">\n\t\t\t<div class=\"catalog-pf-product-input\">{{this.taxValue}}%</div>\n\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input-info--dropdown\"></div>\n\t\t</div>\n\t"
	});

	ui_vue.Vue.component(config.templateFieldInlineSelector, {
	  /**
	   * @emits 'onProductChange' {fields: object}
	   */
	  props: {
	    editable: Boolean,
	    options: Object,
	    basketItem: Object
	  },
	  data: function data() {
	    return {
	      currencySymbol: null,
	      productSelector: null,
	      imageControlId: null,
	      selectorId: this.basketItem.selectorId
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChange.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClear.bind(this));
	  },
	  mounted: function mounted() {
	    this.productSelector = new catalog_productSelector.ProductSelector(this.selectorId, this.prepareSelectorParams());
	    this.productSelector.renderTo(this.$refs.selectorWrapper);
	  },
	  methods: {
	    prepareSelectorParams: function prepareSelectorParams() {
	      var selectorOptions = {
	        iblockId: this.options.iblockId,
	        basePriceId: this.options.basePriceId,
	        productId: this.getField('productId'),
	        skuId: this.getField('skuId'),
	        skuTree: this.getDefaultSkuTree(),
	        fileInputId: '',
	        morePhotoValues: [],
	        fileInput: '',
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
	          URL_BUILDER_CONTEXT: this.options.urlBuilderContext
	        },
	        mode: this.editable ? catalog_productSelector.ProductSelector.MODE_EDIT : catalog_productSelector.ProductSelector.MODE_VIEW,
	        isSimpleModel: this.getField('name', '') !== '' && this.getField('productId') <= 0 && this.getField('skuId') <= 0,
	        fields: {
	          NAME: this.getField('name') || '',
	          PRICE: this.getField('basePrice') || 0,
	          CURRENCY: this.options.currency
	        }
	      };
	      var formImage = this.basketItem.image;

	      if (main_core.Type.isObject(formImage)) {
	        selectorOptions.fileView = formImage.preview;
	        selectorOptions.fileInput = formImage.input;
	        selectorOptions.fileInputId = formImage.id;
	        selectorOptions.morePhotoValues = formImage.values;
	      }

	      return selectorOptions;
	    },
	    isRequiredField: function isRequiredField(code) {
	      return main_core.Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
	    },
	    getDefaultSkuTree: function getDefaultSkuTree() {
	      var skuTree = this.basketItem.skuTree || {};

	      if (main_core.Type.isStringFilled(skuTree)) {
	        skuTree = JSON.parse(skuTree);
	      }

	      return skuTree;
	    },
	    getField: function getField(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.basketItem.fields[name] || defaultValue;
	    },
	    onProductChange: function onProductChange(event) {
	      var data = event.getData();

	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
	        var basePrice = main_core.Text.toNumber(data.fields.PRICE);
	        var fields = {
	          BASE_PRICE: basePrice,
	          MODULE: 'catalog',
	          NAME: data.fields.NAME,
	          ID: data.fields.ID,
	          PRODUCT_ID: data.fields.PRODUCT_ID,
	          SKU_ID: data.fields.SKU_ID,
	          PROPERTIES: data.fields.PROPERTIES,
	          URL_BUILDER_CONTEXT: this.options.urlBuilderContext,
	          CUSTOMIZED: main_core.Type.isNil(data.fields.PRICE) ? 'Y' : 'N',
	          MEASURE_CODE: data.fields.MEASURE_CODE,
	          MEASURE_NAME: data.fields.MEASURE_NAME
	        };
	        this.$emit('onProductChange', fields);
	      }
	    },
	    onProductClear: function onProductClear(event) {
	      var data = event.getData();

	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
	        this.$emit('onProductClear');
	      }
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-item-section\" :id=\"selectorId\" ref=\"selectorWrapper\"></div>\n\t"
	});

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	  data: function data() {
	    return {
	      cache: new main_core.Cache.MemoryCache()
	    };
	  },
	  created: function created() {
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
	            title: main_core.Tag.message(_templateObject(), 'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_TITLE'),
	            subtitle: main_core.Tag.message(_templateObject2(), 'CATALOG_FORM_BRAND_SELECTOR_IS_EMPTY_SUBTITLE'),
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
	  },
	  mounted: function mounted() {
	    this.selector.renderTo(this.$refs.brandSelectorWrapper);
	  },
	  methods: {
	    getPreselectedBrands: function getPreselectedBrands() {
	      if (!main_core.Type.isArray(this.brands) || this.brands.length === 0) {
	        return [];
	      }

	      return this.brands.map(function (item) {
	        return ['brand', item];
	      });
	    },
	    onBrandChange: function onBrandChange(event) {
	      var items = event.getTarget().getSelectedItems();
	      var resultValues = [];

	      if (main_core.Type.isArray(items)) {
	        items.forEach(function (item) {
	          resultValues.push(item.getId());
	        });
	      }

	      this.$emit('changeBrand', resultValues);
	    },
	    createBrand: function createBrand(event) {
	      var _event$getData = event.getData(),
	          searchQuery = _event$getData.searchQuery;

	      var iblockId = this.options.iblockId;
	      return new Promise(function (resolve, reject) {
	        var dialog = event.getTarget();
	        var fields = {
	          name: searchQuery.getQuery(),
	          iblockId: iblockId
	        };
	        dialog.showLoader();
	        main_core.ajax.runAction('catalog.productForm.createBrand', {
	          data: {
	            fields: fields
	          }
	        }).then(function (response) {
	          dialog.hideLoader();
	          var item = dialog.addItem({
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
	        }).catch(function () {
	          return reject();
	        });
	      });
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-control ui-ctl-w100\" v-bind:class=\"{ 'ui-ctl-danger': hasError }\">\n\t\t\t<div class=\"catalog-pf-product-input-wrapper\" ref=\"brandSelectorWrapper\" :id=\"selectorId\"></div>\n\t\t</div>\n\t"
	});

	var FormCompilationType = function FormCompilationType() {
	  babelHelpers.classCallCheck(this, FormCompilationType);
	};
	babelHelpers.defineProperty(FormCompilationType, "REGULAR", 'REGULAR');
	babelHelpers.defineProperty(FormCompilationType, "FACEBOOK", 'FACEBOOK');

	ui_vue.Vue.component(config.templateRowName, {
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
	    mode: String
	  },
	  data: function data() {
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
	        brand: FormInputCode.BRAND
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
	  created: function created() {
	    var _this = this;

	    var defaultFields = this.basketItem.fields;
	    var defaultPrice = main_core.Text.toNumber(defaultFields.price);
	    var basePrice = defaultFields.basePrice || defaultPrice;
	    var calculatorFields = {
	      'QUANTITY': main_core.Text.toNumber(defaultFields.quantity),
	      'BASE_PRICE': basePrice,
	      'PRICE': defaultPrice,
	      'PRICE_NETTO': basePrice,
	      'PRICE_BRUTTO': defaultPrice,
	      'PRICE_EXCLUSIVE': this.basketItem.fields.priceExclusive || defaultPrice,
	      'DISCOUNT_TYPE_ID': main_core.Text.toNumber(defaultFields.discountType) || catalog_productCalculator.DiscountType.PERCENTAGE,
	      'DISCOUNT_RATE': main_core.Text.toNumber(defaultFields.discountRate),
	      'DISCOUNT_SUM': main_core.Text.toNumber(defaultFields.discount),
	      'TAX_INCLUDED': defaultFields.taxIncluded || this.options.taxIncluded,
	      'TAX_RATE': defaultFields.tax || 0,
	      'CUSTOMIZED': defaultFields.isCustomPrice || 'N'
	    };
	    var pricePrecision = this.options.pricePrecision || 2;
	    this.calculator = new catalog_productCalculator.ProductCalculator(calculatorFields, {
	      currencyId: this.options.currency,
	      pricePrecision: pricePrecision,
	      commonPrecision: pricePrecision
	    });
	    this.calculator.setCalculationStrategy(new catalog_productCalculator.TaxForPriceStrategy(this.calculator));
	    this.currencySymbol = this.options.currencySymbol;
	    this.defaultMeasure = {
	      name: '',
	      id: null
	    };

	    if (main_core.Type.isArray(this.options.measures)) {
	      this.options.measures.map(function (measure) {
	        if (measure['IS_DEFAULT'] === 'Y') {
	          _this.defaultMeasure.name = measure.SYMBOL;
	          _this.defaultMeasure.code = measure.CODE;

	          if (!defaultFields.measureName && !defaultFields.measureCode) {
	            _this.changeProduct({
	              measureCode: _this.defaultMeasure.code,
	              measureName: _this.defaultMeasure.name
	            });
	          }
	        }
	      });
	    }

	    this.onInputDiscount = main_core.Runtime.debounce(this.changeDiscount, 500, this);
	  },
	  updated: function updated() {
	    if (main_core.Type.isObject(this.basketItem.calculatedFields)) {
	      var changedFields = this.basketItem.calculatedFields;
	      changedFields['PRICES'] = {};
	      changedFields['PRICES'][this.options.basePriceId] = {
	        PRICE: changedFields.BASE_PRICE || changedFields.PRICE,
	        CURRENCY: this.options.currency
	      };
	      changedFields['MEASURE_CODE'] = this.basketItem.fields.measureCode;
	      main_core_events.EventEmitter.emit(this, 'ProductList::onChangeFields', {
	        rowId: this.selectorId,
	        fields: changedFields
	      });
	    }
	  },
	  methods: {
	    getField: function getField(name) {
	      var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      return this.basketItem.fields[name] || defaultValue;
	    },
	    getCalculator: function getCalculator() {
	      return this.calculator;
	    },
	    setCalculatedFields: function setCalculatedFields(fields) {
	      var map = {
	        calculatedFields: fields
	      };
	      var productFields = this.basketItem.fields;

	      if (!main_core.Type.isNil(fields.ID)) {
	        map.offerId = main_core.Text.toNumber(fields.ID);
	        productFields.productId = main_core.Text.toNumber(fields.PRODUCT_ID);
	        productFields.skuId = main_core.Text.toNumber(fields.SKU_ID);
	      }

	      if (!main_core.Type.isNil(fields.NAME)) {
	        productFields.name = fields.NAME;
	      }

	      if (!main_core.Type.isNil(fields.MODULE)) {
	        productFields.module = fields.MODULE;
	      }

	      if (main_core.Text.toNumber(fields.BASE_PRICE) >= 0) {
	        productFields.basePrice = main_core.Text.toNumber(fields.BASE_PRICE);
	      }

	      if (main_core.Text.toNumber(fields.PRICE) >= 0) {
	        productFields.price = main_core.Text.toNumber(fields.PRICE);
	        productFields.priceExclusive = main_core.Text.toNumber(fields.PRICE_EXCLUSIVE);
	      }

	      if (main_core.Text.toNumber(fields.PRICE_EXCLUSIVE) >= 0 && fields.TAX_INCLUDED === 'Y') {
	        productFields.priceExclusive = main_core.Text.toNumber(fields.PRICE);
	      }

	      if (main_core.Text.toNumber(fields.QUANTITY) >= 0) {
	        productFields.quantity = main_core.Text.toNumber(fields.QUANTITY);
	      }

	      if (!main_core.Type.isNil(fields.DISCOUNT_RATE)) {
	        productFields.discountRate = main_core.Text.toNumber(fields.DISCOUNT_RATE);
	      }

	      if (!main_core.Type.isNil(fields.DISCOUNT_SUM)) {
	        productFields.discount = main_core.Text.toNumber(fields.DISCOUNT_SUM);
	      }

	      if (!main_core.Type.isNil(fields.DISCOUNT_TYPE_ID)) {
	        productFields.discountType = fields.DISCOUNT_TYPE_ID;
	      }

	      if (main_core.Text.toNumber(fields.SUM) >= 0) {
	        map.sum = main_core.Text.toNumber(fields.SUM);
	      }

	      if (!main_core.Type.isNil(fields.CUSTOMIZED)) {
	        productFields.isCustomPrice = fields.CUSTOMIZED;
	      }

	      if (!main_core.Type.isNil(fields.MEASURE_CODE)) {
	        productFields.measureCode = fields.MEASURE_CODE;
	      }

	      if (!main_core.Type.isNil(fields.MEASURE_NAME)) {
	        productFields.measureName = fields.MEASURE_NAME;
	      }

	      if (!main_core.Type.isNil(fields.PROPERTIES)) {
	        productFields.properties = fields.PROPERTIES;
	      }

	      if (!main_core.Type.isNil(fields.BRANDS)) {
	        productFields.brands = fields.BRANDS;
	      }

	      if (!main_core.Type.isNil(fields.TAX_ID)) {
	        productFields.taxId = fields.TAX_ID;
	      }

	      this.changeRowData(map);
	      this.changeProduct(productFields);
	    },
	    changeRowData: function changeRowData(fields) {
	      this.$emit('changeRowData', {
	        index: this.basketItemIndex,
	        fields: fields
	      });
	    },
	    changeProduct: function changeProduct(fields) {
	      fields = Object.assign(this.basketItem.fields, fields);
	      this.$emit('changeProduct', {
	        index: this.basketItemIndex,
	        fields: fields
	      });
	    },
	    onProductChange: function onProductChange(fields) {
	      fields = Object.assign(this.getCalculator().calculatePrice(fields.BASE_PRICE), fields);
	      this.getCalculator().setFields(fields);
	      this.setCalculatedFields(fields);
	    },
	    onProductClear: function onProductClear() {
	      var fields = this.getCalculator().calculatePrice(0);
	      fields.BASE_PRICE = 0;
	      fields.NAME = '';
	      fields.ID = 0;
	      fields.PRODUCT_ID = 0;
	      fields.SKU_ID = 0;
	      fields.MODULE = '';
	      this.getCalculator().setFields(fields);
	      this.setCalculatedFields(fields);
	    },
	    toggleDiscount: function toggleDiscount(value) {
	      var _this2 = this;

	      if (this.isReadOnly) {
	        return;
	      }

	      this.changeRowData({
	        showDiscount: value
	      });

	      if (value === 'Y') {
	        setTimeout(function () {
	          var _this2$$refs, _this2$$refs$discount, _this2$$refs$discount2, _this2$$refs$discount3;

	          return (_this2$$refs = _this2.$refs) === null || _this2$$refs === void 0 ? void 0 : (_this2$$refs$discount = _this2$$refs.discountWrapper) === null || _this2$$refs$discount === void 0 ? void 0 : (_this2$$refs$discount2 = _this2$$refs$discount.$refs) === null || _this2$$refs$discount2 === void 0 ? void 0 : (_this2$$refs$discount3 = _this2$$refs$discount2.discountInput) === null || _this2$$refs$discount3 === void 0 ? void 0 : _this2$$refs$discount3.focus();
	        });
	      }
	    },
	    toggleTax: function toggleTax(value) {
	      this.changeRowData({
	        showTax: value
	      });
	    },
	    changeBrand: function changeBrand(values) {
	      var fields = this.getCalculator().getFields();
	      fields.BRANDS = main_core.Type.isArray(values) ? values : [];
	      this.setCalculatedFields(fields);
	    },
	    processFields: function processFields(fields) {
	      this.setCalculatedFields(fields);
	      this.getCalculator().setFields(fields);
	    },
	    changeQuantity: function changeQuantity(quantity) {
	      this.processFields(this.getCalculator().calculateQuantity(quantity));
	    },
	    changeMeasure: function changeMeasure(measure) {
	      var productFields = this.basketItem.fields;
	      productFields['measureCode'] = measure.code;
	      productFields['measureName'] = measure.name;
	      this.changeProduct(productFields);
	    },
	    changePrice: function changePrice(price) {
	      var calculatedFields = this.getCalculator().calculatePrice(price);
	      calculatedFields.BASE_PRICE = price;
	      this.processFields(calculatedFields);
	    },
	    changeDiscountType: function changeDiscountType(discountType) {
	      var type = main_core.Text.toNumber(discountType) === catalog_productCalculator.DiscountType.MONETARY ? catalog_productCalculator.DiscountType.MONETARY : catalog_productCalculator.DiscountType.PERCENTAGE;
	      this.processFields(this.getCalculator().calculateDiscountType(type));
	    },
	    changeDiscount: function changeDiscount(discount) {
	      this.processFields(this.getCalculator().calculateDiscount(discount));
	    },
	    changeTax: function changeTax(fields) {
	      var calculatedFields = this.getCalculator().calculateTax(fields.taxValue);
	      calculatedFields.TAX_ID = fields.taxId;
	      this.processFields(calculatedFields);
	    },
	    changeTaxIncluded: function changeTaxIncluded(taxIncluded) {
	      if (taxIncluded === this.basketItem.taxIncluded || !this.isEditableField(this.blocks.tax)) {
	        return;
	      }

	      var calculatedFields = this.getCalculator().calculateTaxIncluded(taxIncluded);
	      this.getCalculator().setFields(calculatedFields);
	      this.setCalculatedFields(calculatedFields);
	    },
	    removeItem: function removeItem() {
	      this.$emit('removeItem', {
	        index: this.basketItemIndex
	      });
	    },
	    isRequiredField: function isRequiredField(code) {
	      return main_core.Type.isArray(this.options.requiredFields) && this.options.requiredFields.includes(code);
	    },
	    isVisibleBlock: function isVisibleBlock(code) {
	      return main_core.Type.isArray(this.options.visibleBlocks) && this.options.visibleBlocks.includes(code);
	    },
	    hasError: function hasError(code) {
	      if (this.basketItem.errors.length === 0) {
	        return false;
	      }

	      var filteredErrors = this.basketItem.errors.filter(function (error) {
	        return error.code === code;
	      });
	      return filteredErrors.length > 0;
	    },
	    isEditableField: function isEditableField(code) {
	      var _this$options;

	      return (_this$options = this.options) === null || _this$options === void 0 ? void 0 : _this$options.editableFields.includes(code);
	    }
	  },
	  watch: {
	    taxIncluded: function taxIncluded(value, oldValue) {
	      if (value !== oldValue) {
	        this.changeTaxIncluded(value);
	      }
	    }
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_FORM_');
	    },
	    showDiscount: function showDiscount() {
	      return this.showDiscountBlock && this.basketItem.showDiscount === 'Y';
	    },
	    getBrandsSelectorId: function getBrandsSelectorId() {
	      return this.basketItem.selectorId + '_brands';
	    },
	    getPriceExclusive: function getPriceExclusive() {
	      return this.basketItem.fields.priceExclusive || this.basketItem.fields.price;
	    },
	    showDiscountBlock: function showDiscountBlock() {
	      return this.options.showDiscountBlock === 'Y' && this.isVisibleBlock(this.blocks.discount) && !this.isReadOnly;
	    },
	    showTaxBlock: function showTaxBlock() {
	      return this.options.showTaxBlock === 'Y' && this.getTaxList.length > 0 && this.isVisibleBlock(this.blocks.tax) && !this.isReadOnly;
	    },
	    showRemoveIcon: function showRemoveIcon() {
	      if (this.isReadOnly) {
	        return false;
	      }

	      if (this.countItems > 1) {
	        return true;
	      }

	      return this.basketItem.offerId !== null;
	    },
	    showTaxSelector: function showTaxSelector() {
	      return this.basketItem.showTax === 'Y';
	    },
	    showBasePrice: function showBasePrice() {
	      return this.basketItem.fields.discount > 0 || main_core.Text.toNumber(this.basketItem.fields.price) !== main_core.Text.toNumber(this.basketItem.fields.basePrice);
	    },
	    getMeasureName: function getMeasureName() {
	      return this.basketItem.fields.measureName || this.defaultMeasure.name;
	    },
	    getMeasureCode: function getMeasureCode() {
	      return this.basketItem.fields.measureCode || this.defaultMeasure.code;
	    },
	    getTaxList: function getTaxList() {
	      return main_core.Type.isArray(this.options.taxList) ? this.options.taxList : [];
	    },
	    taxIncluded: function taxIncluded() {
	      return this.basketItem.fields.taxIncluded;
	    },
	    isTaxIncluded: function isTaxIncluded() {
	      return this.taxIncluded === 'Y';
	    },
	    isReadOnly: function isReadOnly() {
	      return this.mode === FormMode.READ_ONLY;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"catalog-pf-product-item\" v-bind:class=\"{ 'catalog-pf-product-item--borderless': !isReadOnly && basketItemIndex === 0 }\">\n\t\t\t<div class=\"catalog-pf-product-item--remove\" @click=\"removeItem\" v-if=\"showRemoveIcon\"></div>\n\t\t\t<div class=\"catalog-pf-product-item--num\">\n\t\t\t\t<div class=\"catalog-pf-product-index\">{{basketItemIndex + 1}}</div>\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-item--left\">\n\t\t\t\t<div v-if=\"isVisibleBlock(blocks.productSelector)\">\n\t\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-label\">{{localize.CATALOG_FORM_NAME}}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<".concat(config.templateFieldInlineSelector, " \n\t\t\t\t\t\t:basketItem=\"basketItem\" \n\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t:editable=\"isEditableField(blocks.productSelector)\"\n\t\t\t\t\t\t@onProductChange=\"onProductChange\" \n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"isVisibleBlock(blocks.brand)\" class=\"catalog-pf-product-input-brand-wrapper\">\n\t\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-label\">{{localize.CATALOG_FORM_BRAND_TITLE}}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<").concat(config.templateFieldBrand, " \n\t\t\t\t\t\t:brands=\"basketItem.fields.brands\"\n\t\t\t\t\t\t:selectorId=\"getBrandsSelectorId\"\n\t\t\t\t\t\t:hasError=\"hasError(errorCodes.emptyBrand)\"\n\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t:editable=\"isEditableField(blocks.brand)\"\n\t\t\t\t\t\t@changeBrand=\"changeBrand\" \n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-item--right\">\n\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.price)\" class=\"catalog-pf-product-label\" style=\"width: 94px\">\n\t\t\t\t\t\t{{localize.CATALOG_FORM_PRICE}}\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.quantity)\" class=\"catalog-pf-product-label\" style=\"width: 72px\">\n\t\t\t\t\t\t{{localize.CATALOG_FORM_QUANTITY}}\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.result)\" class=\"catalog-pf-product-label\" style=\"width: 94px\">\n\t\t\t\t\t\t{{localize.CATALOG_FORM_RESULT}}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.price)\" class=\"catalog-pf-product-control\" style=\"width: 94px\">\n\t\t\t\t\t\t<").concat(config.templateFieldPrice, " \n\t\t\t\t\t\t\t:basePrice=\"basketItem.fields.basePrice\"\n\t\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t\t:editable=\"isEditableField(blocks.price)\"\n\t\t\t\t\t\t\t:hasError=\"hasError(errorCodes.emptyPrice)\"\n\t\t\t\t\t\t\t@changePrice=\"changePrice\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.quantity)\" class=\"catalog-pf-product-control\" style=\"width: 72px\">\n\t\t\t\t\t\t<").concat(config.templateFieldQuantity, " \n\t\t\t\t\t\t\t:quantity=\"basketItem.fields.quantity\"\n\t\t\t\t\t\t\t:measureCode=\"getMeasureCode\"\n\t\t\t\t\t\t\t:measureRatio=\"basketItem.fields.measureRatio\"\n\t\t\t\t\t\t\t:measureName=\"getMeasureName\"\n\t\t\t\t\t\t\t:hasError=\"hasError(errorCodes.emptyQuantity)\"\n\t\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t\t:editable=\"isEditableField(blocks.quantity)\"\n\t\t\t\t\t\t\t@changeQuantity=\"changeQuantity\" \n\t\t\t\t\t\t\t@changeMeasure=\"changeMeasure\" \n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t\t<div v-if=\"isVisibleBlock(blocks.result)\" class=\"catalog-pf-product-control\" style=\"width: 94px\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-wrapper\">\n\t\t\t\t\t\t\t<input disabled type=\"text\" class=\"catalog-pf-product-input catalog-pf-product-input--disabled catalog-pf-product-input--gray catalog-pf-product-input--align-right\" :value=\"basketItem.sum\">\n\t\t\t\t\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input--disabled catalog-pf-product-input--gray\" v-html=\"currencySymbol\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"hasError(errorCodes.emptyQuantity)\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-product-error\">{{localize.CATALOG_FORM_ERROR_EMPTY_QUANTITY}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"hasError(errorCodes.emptyPrice)\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-product-error\">{{localize.CATALOG_FORM_ERROR_EMPTY_PRICE}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showDiscountBlock\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div v-if=\"showDiscount\" class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide\" @click=\"toggleDiscount('N')\">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>\n\t\t\t\t\t<div v-else class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show\" @click=\"toggleDiscount('Y')\">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div v-if=\"showDiscount\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<").concat(config.templateFieldDiscount, " \n\t\t\t\t\t\t:discount=\"basketItem.fields.discount\"\n\t\t\t\t\t\t:discountType=\"basketItem.fields.discountType\"\n\t\t\t\t\t\t:discountRate=\"basketItem.fields.discountRate\"\n\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t:editable=\"isEditableField(blocks.discount)\"\n\t\t\t\t\t\tref=\"discountWrapper\"\n\t\t\t\t\t\t@changeDiscount=\"changeDiscount\" \n\t\t\t\t\t\t@changeDiscountType=\"changeDiscountType\" \n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div v-if=\"showTaxBlock\" class=\"catalog-pf-product-item-section catalog-pf-product-item-section--dashed\">\n\t\t\t\t\t<div v-if=\"showTaxSelector\" class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide\" @click=\"toggleTax('N')\">{{localize.CATALOG_FORM_TAX_TITLE}}</div>\n\t\t\t\t\t<div v-else class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show\" @click=\"toggleTax('Y')\">{{localize.CATALOG_FORM_TAX_TITLE}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showTaxSelector && showTaxBlock\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<").concat(config.templateFieldTax, " \n\t\t\t\t\t\t:taxId=\"basketItem.fields.taxId\"\n\t\t\t\t\t\t:options=\"options\"\n\t\t\t\t\t\t:editable=\"isEditableField(blocks.tax)\"\n\t\t\t\t\t\t@changeProduct=\"changeProduct\" \n\t\t\t\t\t/>\n\t\t\t\t</div>\t\t\t\t\n\t\t\t\t<div class=\"catalog-pf-product-item-section catalog-pf-product-item-section--dashed\"></div>\n\t\t\t</div>\n\t\t</div>\n\t")
	});

	var FormHelpdeskCode = function FormHelpdeskCode() {
	  babelHelpers.classCallCheck(this, FormHelpdeskCode);
	};
	babelHelpers.defineProperty(FormHelpdeskCode, "COMPILATION_FACEBOOK", 13856526);
	babelHelpers.defineProperty(FormHelpdeskCode, "COMMON_COMPILATION", 13841876);

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"catalog-product-form-popup--container\">\n\t\t\t\t\t<div class=\"catalog-product-form-popup--title\">", "</div>\n\t\t\t\t\t<div class=\"catalog-product-form-popup--loader-block catalog-product-form-popup--done\"></div>\n\t\t\t\t\t<div class=\"catalog-product-form-popup--text\">", "</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"catalog-product-form-popup--container\">\n\t\t\t\t\t<div class=\"catalog-product-form-popup--title\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"catalog-product-form-popup--text\">", "</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"catalog-product-form-popup--loader-block\"></div>\n\t\t\t"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t\t<span>", "</span>\n\t\t\t\t\t\t\t\t\t\t<a href=\"/shop/stores/\" target=\"_blank\">\n\t\t\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"catalog-pf-product-qr-popup\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-qr-popup-content\">\n\t\t\t\t\t\t\t<div class=\"catalog-pf-product-qr-popup-text\">", "</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"catalog-pf-product-qr-popup-buttons\">\n\t\t\t\t\t\t\t\t<a href=\"", "\" target=\"_blank\" class=\"ui-btn ui-btn-light-border ui-btn-round\">", "</a>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"catalog-pf-product-qr-popup-bottom\">\n\t\t\t\t\t\t\t<a href=\"", "\" target=\"_blank\" class=\"catalog-pf-product-qr-popup--url\">", "</a>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\t\t\t\t\t\n\t\t\t\t\t</div>\n\t\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-pf-product-qr-popup-image\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"catalog-pf-product-qr-popup-copy\">", "</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<p>", "</p>\n\t\t\t\t<p>", "</p>\n\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a class=\"ui-btn ui-btn-primary\">", "</a>\n\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	ui_vue.Vue.component(config.templatePanelCompilation, {
	  props: {
	    compilationOptions: Object,
	    mode: String
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.destroyPopup.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.destroyPopup.bind(this));
	    this.newLabel = new ui_label.Label({
	      text: this.localize.CATALOG_FORM_COMPILATION_PRODUCT_NEW_LABEL,
	      color: ui_label.LabelColor.PRIMARY,
	      fill: true
	    });
	    var moreMessageButton = main_core.Tag.render(_templateObject$1(), this.localize.CATALOG_FORM_COMPILATION_INFO_BUTTON_MORE);
	    main_core.Event.bind(moreMessageButton, 'click', this.openHelpDesk);
	    var header = '';
	    var description = '';

	    if (this.isFacebookForm()) {
	      header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE_FACEBOOK;
	      description = main_core.Tag.render(_templateObject2$1(), this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_FIRST_BLOCK, this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_FACEBOOK_SECOND_BLOCK);
	    } else {
	      header = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_TITLE;
	      description = this.localize.CATALOG_FORM_COMPILATION_INFO_MESSAGE_BODY_MARKETING;
	    }

	    this.message = new ui_messagecard.MessageCard({
	      id: 'compilationInfo',
	      header: header,
	      description: description,
	      angle: false,
	      hidden: true,
	      actionElements: [moreMessageButton]
	    });
	    main_core_events.EventEmitter.subscribe(this.message, 'onClose', this.hideMessage);
	  },
	  mounted: function mounted() {
	    this.$refs.label.appendChild(this.newLabel.render());
	    this.$refs.message.appendChild(this.message.getLayout());

	    if (!this.compilationOptions.hiddenInfoMessage) {
	      this.showMessage();
	    }
	  },
	  data: function data() {
	    return {
	      compilationLink: null
	    };
	  },
	  methods: {
	    isFacebookForm: function isFacebookForm() {
	      return this.compilationOptions.type === FormCompilationType.FACEBOOK;
	    },
	    openHelpDesk: function openHelpDesk() {
	      this.helpdeskCode = this.isFacebookForm() ? FormHelpdeskCode.COMPILATION_FACEBOOK : FormHelpdeskCode.COMMON_COMPILATION;
	      top.BX.Helper.show('redirect=detail&code=' + this.helpdeskCode);
	    },
	    showPopup: function showPopup(event) {
	      var _this = this;

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

	      var basket = this.$store.getters['productList/getBasket']();
	      var productIds = basket.map(function (basketItem) {
	        var _basketItem$fields;

	        return basketItem === null || basketItem === void 0 ? void 0 : (_basketItem$fields = basketItem.fields) === null || _basketItem$fields === void 0 ? void 0 : _basketItem$fields.skuId;
	      });
	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runAction('salescenter.api.store.getLinkToProductCollection', {
	          json: {
	            productIds: productIds
	          }
	        }).then(function (response) {
	          _this.compilationLink = response.data.link;
	          _this.popup = new main_popup.Popup({
	            bindElement: event.target,
	            content: _this.getQRPopupContent(),
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

	          _this.popup.show();

	          resolve();
	        }).catch(function () {
	          return reject();
	        });
	      });
	    },
	    destroyPopup: function destroyPopup() {
	      if (this.popup instanceof main_popup.Popup) {
	        this.popup.destroy();
	        this.popup = null;
	      }
	    },
	    getQRPopupContent: function getQRPopupContent() {
	      var _this2 = this;

	      if (!this.compilationLink) {
	        return '';
	      }

	      var buttonCopy = main_core.Tag.render(_templateObject3(), this.localize.CATALOG_FORM_COMPILATION_QR_COPY);
	      main_core.Event.bind(buttonCopy, 'click', function () {
	        BX.clipboard.copy(_this2.compilationLink);
	        BX.UI.Notification.Center.notify({
	          content: _this2.localize.CATALOG_FORM_COMPILATION_QR_COPY_NOTIFY_MESSAGE,
	          autoHideDelay: 2000
	        });
	      });
	      var qrWrapper = main_core.Tag.render(_templateObject4());
	      var content = main_core.Tag.render(_templateObject5(), this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_TITLE, qrWrapper, this.compilationLink, this.localize.CATALOG_FORM_COMPILATION_QR_POPUP_INPUT_TITLE, this.compilationLink, this.compilationLink, buttonCopy);
	      new QRCode(qrWrapper, {
	        text: this.compilationLink,
	        width: 250,
	        height: 250
	      });
	      return content;
	    },
	    setSetting: function setSetting(event) {
	      var _this3 = this;

	      var value = event.target.checked ? 'Y' : 'N';

	      if (!this.compilationOptions.hasStore) {
	        this.compilationOptions.disabledSwitcher = true;
	        var creationStorePopup = new main_popup.Popup({
	          bindElement: event.target,
	          className: 'catalog-product-form-popup--creating-shop',
	          content: this.getOnBeforeCreationStorePopupContent(),
	          width: 310,
	          overlay: true,
	          padding: 17,
	          animation: 'fading-slide',
	          angle: false
	        });
	        creationStorePopup.show();
	        main_core.ajax.runAction('salescenter.api.store.getStoreInfo', {
	          json: {}
	        }).then(function (response) {
	          var _response$data, _response$data$deacti;

	          if (main_core.Type.isStringFilled((_response$data = response.data) === null || _response$data === void 0 ? void 0 : (_response$data$deacti = _response$data.deactivatedStore) === null || _response$data$deacti === void 0 ? void 0 : _response$data$deacti.TITLE)) {
	            var _response$data2, _response$data2$deact;

	            var title = main_core.Loc.getMessage('CATALOG_FORM_COMPILATION_UNPUBLISHED_STORE', {
	              '#STORE_TITLE#': main_core.Tag.safe(_templateObject6(), (_response$data2 = response.data) === null || _response$data2 === void 0 ? void 0 : (_response$data2$deact = _response$data2.deactivatedStore) === null || _response$data2$deact === void 0 ? void 0 : _response$data2$deact.TITLE)
	            });
	            BX.UI.Notification.Center.notify({
	              content: main_core.Tag.render(_templateObject7(), title, main_core.Loc.getMessage('CATALOG_FORM_COMPILATION_UNPUBLISHED_STORE_LINK'))
	            });
	          }

	          creationStorePopup.setContent(_this3.getOnAfterCreationStorePopupContent());
	          creationStorePopup.setClosingByEsc(true);
	          creationStorePopup.setAutoHide(true);
	          creationStorePopup.show();

	          _this3.$root.$app.changeFormOption('isCompilationMode', value);

	          _this3.compilationOptions.disabledSwitcher = _this3.compilationOptions.isLimitedStore;
	          _this3.compilationOptions.hasStore = true;
	        });
	      } else {
	        this.$root.$app.changeFormOption('isCompilationMode', value);
	      }
	    },
	    getOnBeforeCreationStorePopupContent: function getOnBeforeCreationStorePopupContent() {
	      var loaderContent = main_core.Tag.render(_templateObject8());
	      var node = main_core.Tag.render(_templateObject9(), main_core.Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING'), loaderContent, main_core.Loc.getMessage('CATALOG_FORM_POPUP_BEFORE_MARKET_CREATING_INFO'));
	      var loader = new main_loader.Loader({
	        color: "#2fc6f6",
	        target: loaderContent,
	        size: 40
	      });
	      loader.show();
	      return node;
	    },
	    getOnAfterCreationStorePopupContent: function getOnAfterCreationStorePopupContent() {
	      return main_core.Tag.render(_templateObject10(), main_core.Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING'), main_core.Loc.getMessage('CATALOG_FORM_POPUP_AFTER_MARKET_CREATING_INFO'));
	    },
	    onLabelClick: function onLabelClick() {
	      if (this.compilationOptions.isLimitedStore) {
	        BX.UI.InfoHelper.show('limit_sites_number');
	      }
	    },
	    onClickHint: function onClickHint(event) {
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
	    showMessage: function showMessage() {
	      if (this.message) {
	        main_core.Dom.addClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
	        this.message.show();
	      }
	    },
	    hideMessage: function hideMessage() {
	      if (this.message) {
	        main_core.Dom.removeClass(this.$refs.hintIcon, 'catalog-pf-product-panel-message-arrow-target');
	      }

	      this.message.hide();
	      this.$root.$app.changeFormOption('hiddenCompilationInfoMessage', 'Y');
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    showQrLink: function showQrLink() {
	      return this.mode === FormMode.COMPILATION;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    productList: function productList(state) {
	      return state.productList;
	    }
	  })),
	  // language=Vue
	  template: "\n\t\t<div>\n\t\t\t<div class=\"catalog-pf-product-panel-compilation\">\n\t\t\t\t<div class=\"catalog-pf-product-panel-compilation-wrapper\">\n\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\" @click=\"onLabelClick\">\n\t\t\t\t\t\t<input \n\t\t\t\t\t\t\ttype=\"checkbox\" \n\t\t\t\t\t\t\t:disabled=\"compilationOptions.disabledSwitcher\"\n\t\t\t\t\t\t\tclass=\"ui-ctl-element\" \n\t\t\t\t\t\t\t@change=\"setSetting\" \n\t\t\t\t\t\t\tdata-setting-id=\"isCompilationMode\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">{{localize.CATALOG_FORM_COMPILATION_PRODUCT_SWITCHER}}</div>\n\t\t\t\t\t\t<div ref=\"hintIcon\">\n\t\t\t\t\t\t\t<div data-hint-init=\"vue\" class=\"ui-hint\" @click=\"onClickHint\">\n\t\t\t\t\t\t\t\t<span class=\"ui-hint-icon\"></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div ref=\"label\"></div>\n\t\t\t\t\t\t<div class=\"tariff-lock\" v-if=\"compilationOptions.isLimitedStore\"></div>\n\t\t\t\t\t</label>\n\t\t\t\t</div>\n\t\t\t\t<div \t\t\t\t\n\t\t\t\t\tv-if=\"showQrLink\"\n\t\t\t\t\tclass=\"catalog-pf-product-panel-compilation-link --icon-qr\"\n\t\t\t\t\t@click=\"showPopup\"\n\t\t\t\t\tref=\"qrLink\"\n\t\t\t\t>\n\t\t\t\t\t{{localize.CATALOG_FORM_COMPILATION_QR_LINK}}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-panel-compilation-message\" ref=\"message\"></div>\n\t\t</div>\n\t"
	});

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-pf-product-config-popup'></div>\n\t\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<input type=\"checkbox\"  class=\"ui-ctl-element\">\n\t\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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
	  methods: {
	    refreshBasket: function refreshBasket() {
	      this.$emit('refreshBasket');
	    },
	    changeBasketItem: function changeBasketItem(item) {
	      this.$emit('changeRowData', item);
	    },
	    addBasketItemForm: function addBasketItemForm() {
	      this.$emit('addItem');
	    },
	    getInternalIndexByProductId: function getInternalIndexByProductId(skuId) {
	      var basket = this.$store.getters['productList/getBasket']();
	      return Object.keys(basket).findIndex(function (inx) {
	        return parseInt(basket[inx].skuId) === parseInt(skuId);
	      });
	    },
	    handleAddItem: function handleAddItem(id, params) {
	      var _this = this;

	      var skuType = 4;

	      if (main_core.Text.toNumber(params.type) === skuType) {
	        main_core.ajax.runAction('catalog.productSelector.getSelectedSku', {
	          json: {
	            variationId: id,
	            options: {
	              priceId: this.options.basePriceId,
	              urlBuilder: this.options.urlBuilder,
	              resetSku: true
	            }
	          }
	        }).then(function (response) {
	          return _this.processResponse(response);
	        });
	      } else {
	        main_core.ajax.runAction('catalog.productSelector.getProduct', {
	          json: {
	            productId: id,
	            options: {
	              priceId: this.options.basePriceId,
	              urlBuilder: this.options.urlBuilder
	            }
	          }
	        }).then(function (response) {
	          return _this.processResponse(response);
	        });
	      }
	    },
	    processResponse: function processResponse(response) {
	      var index = this.getInternalIndexByProductId(response.data.skuId);

	      if (index < 0) {
	        var productData = response.data;
	        var price = main_core.Text.toNumber(productData.fields.PRICE);
	        productData.fields = productData.fields || {};
	        var newItem = this.$store.getters['productList/getBaseProduct']();
	        newItem.fields = Object.assign(newItem.fields, {
	          price: price,
	          priceExclusive: price,
	          basePrice: price,
	          name: productData.fields.NAME || '',
	          productId: productData.productId,
	          skuId: productData.skuId,
	          offerId: productData.skuId > 0 ? productData.skuId : productData.productId,
	          module: 'catalog',
	          isCustomPrice: main_core.Type.isNil(productData.fields.PRICE) ? 'Y' : 'N',
	          discountType: this.options.defaultDiscountType
	        });
	        delete productData.fields;
	        newItem = Object.assign(newItem, productData);
	        newItem.sum = price;
	        this.$root.$app.addProduct(newItem);
	      }
	    },
	    onUpdateBasketItem: function onUpdateBasketItem(inx, item) {
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
	    removeEmptyItems: function removeEmptyItems() {
	      var _this2 = this;

	      var basket = this.$store.getters['productList/getBasket']();
	      basket.forEach(function (item, i) {
	        if (basket[i].name === '' && basket[i].price < 1e-10) {
	          _this2.$store.commit('productList/deleteItem', {
	            index: i
	          });
	        }
	      });
	    },
	    modifyBasketItem: function modifyBasketItem(params) {
	      var skuId = parseInt(params.id);

	      if (skuId > 0) {
	        var index = this.getInternalIndexByProductId(skuId);

	        if (index >= 0) {
	          this.showDialogProductExists(params);
	        } else {
	          this.removeEmptyItems();
	          this.handleAddItem(skuId, params);
	        }
	      }
	    },
	    showDialogProductExists: function showDialogProductExists(params) {
	      var _this3 = this;

	      this.popup = new main_popup.Popup(null, null, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this3.popup.destroy();
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
	        content: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_TEXT').replace('#NAME#', params.name),
	        buttons: this.getButtons(params)
	      });
	      this.popup.show();
	    },
	    getButtons: function getButtons(product) {
	      var _this4 = this;

	      var buttons = [];
	      var params = product;
	      buttons.push(new BX.UI.SaveButton({
	        text: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_OK'),
	        onclick: function onclick() {
	          var productId = parseInt(params.id);

	          var inx = _this4.getInternalIndexByProductId(productId);

	          if (inx >= 0) {
	            var item = _this4.$store.getters['productList/getBasket']()[inx];

	            item.fields.quantity++;
	            item.calculatedFields.QUANTITY++;

	            _this4.onUpdateBasketItem(inx, item);
	          }

	          _this4.popup.destroy();
	        }
	      }));
	      buttons.push(new BX.UI.CancelButton({
	        text: main_core.Loc.getMessage('CATALOG_FORM_BLOCK_PROD_EXIST_DLG_NO'),
	        onclick: function onclick() {
	          _this4.popup.destroy();
	        }
	      }));
	      return buttons;
	    },
	    showDialogProductSearch: function showDialogProductSearch() {
	      var _this5 = this;

	      var funcName = 'addBasketItemFromDialogProductSearch';

	      window[funcName] = function (params) {
	        return _this5.modifyBasketItem(params);
	      };

	      var popup = new BX.CDialog({
	        content_url: '/bitrix/tools/sale/product_search_dialog.php?' + //todo: 'lang='+this._settings.languageId+
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
	    setSetting: function setSetting(event) {
	      if (event.target.dataset.settingId === 'taxIncludedOption') {
	        var value = event.target.checked ? 'Y' : 'N';
	        this.$root.$app.changeFormOption('taxIncluded', value);
	      } else if (event.target.dataset.settingId === 'showDiscountInputOption') {
	        var _value = event.target.checked ? 'Y' : 'N';

	        this.$root.$app.changeFormOption('showDiscountBlock', _value);
	      } else if (event.target.dataset.settingId === 'showTaxInputOption') {
	        var _value2 = event.target.checked ? 'Y' : 'N';

	        this.$root.$app.changeFormOption('showTaxBlock', _value2);
	      }
	    },
	    getSettingItem: function getSettingItem(item) {
	      var input = main_core.Tag.render(_templateObject$2());
	      input.checked = item.checked;
	      input.dataset.settingId = item.id;
	      var setting = main_core.Tag.render(_templateObject2$2(), input, item.title);
	      main_core.Event.bind(setting, 'change', this.setSetting.bind(this));
	      return setting;
	    },
	    prepareSettingsContent: function prepareSettingsContent() {
	      var _this6 = this;

	      var settings = [// {
	      // 	id: 'taxIncludedOption',
	      // 	checked: (this.options.taxIncluded === 'Y'),
	      // 	title: this.localize.CATALOG_FORM_ADD_TAX_INCLUDED,
	      // },
	      {
	        id: 'showDiscountInputOption',
	        checked: this.options.showDiscountBlock !== 'N',
	        title: this.localize.CATALOG_FORM_ADD_SHOW_DISCOUNTS_OPTION
	      } // {
	      // 	id: 'showTaxInputOption',
	      // 	checked: (this.options.showTaxBlock !== 'N'),
	      // 	title: this.localize.CATALOG_FORM_ADD_SHOW_TAXES_OPTION,
	      // },
	      ];
	      var content = main_core.Tag.render(_templateObject3$1());
	      settings.forEach(function (item) {
	        content.append(_this6.getSettingItem(item));
	      });
	      return content;
	    },
	    showConfigPopup: function showConfigPopup(event) {
	      if (!this.popupMenu) {
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
	      }

	      this.popupMenu.show();
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    countItems: function countItems() {
	      return this.order.basket.length;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    productList: function productList(state) {
	      return state.productList;
	    }
	  })),
	  // language=Vue
	  template: "\n\t\t<div>\n\t\t\t<div class=\"catalog-pf-product-add\">\n\t\t\t\t<div class=\"catalog-pf-product-add-wrapper\">\n\t\t\t\t\t<span class=\"catalog-pf-product-add-link\" @click=\"addBasketItemForm\">{{localize.CATALOG_FORM_ADD_PRODUCT}}</span>\n\t\t\t\t\t<span class=\"catalog-pf-product-add-link catalog-pf-product-add-link--gray\" @click=\"showDialogProductSearch\">{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-pf-product-configure-link\" @click=\"showConfigPopup\">{{localize.CATALOG_FORM_DISCOUNT_EDIT_PAGE_URL_TITLE}}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	ui_vue.Vue.component(config.templateName, {
	  props: {
	    options: Object,
	    mode: String
	  },
	  created: function created() {
	    BX.ajax.runAction("catalog.productSelector.getFileInput", {
	      json: {
	        iblockId: this.options.iblockId
	      }
	    });
	  },
	  methods: {
	    refreshBasket: function refreshBasket() {
	      this.$store.dispatch('productList/refreshBasket');
	    },
	    changeProduct: function changeProduct(item) {
	      this.$root.$app.changeProduct(item);
	    },
	    changeRowData: function changeRowData(item) {
	      delete item.fields.fields;
	      this.$store.dispatch('productList/changeItem', item);
	    },
	    removeItem: function removeItem(item) {
	      this.$root.$app.removeProduct(item);
	    },
	    addItem: function addItem() {
	      this.$root.$app.addProduct();
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('CATALOG_');
	    },
	    showTaxResult: function showTaxResult() {
	      return this.options.showTaxBlock !== 'N';
	    },
	    showResults: function showResults() {
	      return this.options.showResults !== false;
	    },
	    showButtonsTop: function showButtonsTop() {
	      return this.options.singleProductMode !== true && this.mode !== FormMode.READ_ONLY && this.options.buttonsPosition !== FormElementPosition.BOTTOM;
	    },
	    showButtonsBottom: function showButtonsBottom() {
	      return this.options.singleProductMode !== true && this.mode !== FormMode.READ_ONLY && this.options.buttonsPosition === FormElementPosition.BOTTOM;
	    },
	    showResultBlock: function showResultBlock() {
	      return this.showResults || this.enableAddButtons;
	    },
	    countItems: function countItems() {
	      return this.productList.basket.length;
	    },
	    totalResultLabel: function totalResultLabel() {
	      return this.options.hasOwnProperty('totalResultLabel') && this.options.totalResultLabel ? this.options.totalResultLabel : this.localize.CATALOG_FORM_TOTAL_RESULT;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    productList: function productList(state) {
	      return state.productList;
	    }
	  })),
	  // language=Vue
	  template: "\n\t<div class=\"catalog-product-form-container\">\n\t\t<".concat(config.templatePanelButtons, "\n\t\t\t:options=\"options\" \n\t\t\t:mode=\"mode\" \n\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t@addItem=\"addItem\"\n\t\t\t@changeRowData=\"changeRowData\"\n\t\t\t@changeProduct=\"changeProduct\" \n\t\t\tv-if=\"showButtonsTop\"\n\t\t/>\n\t\t<div v-for=\"(item, index) in productList.basket\" :key=\"item.selectorId\">\n\t\t\t<").concat(config.templateRowName, " \n\t\t\t\t:basketItem=\"item\" \n\t\t\t\t:basketItemIndex=\"index\"  \n\t\t\t\t:countItems=\"countItems\"\n\t\t\t\t:options=\"options\"\n\t\t\t\t:mode=\"mode\"\n\t\t\t\t@changeProduct=\"changeProduct\" \n\t\t\t\t@changeRowData=\"changeRowData\" \n\t\t\t\t@removeItem=\"removeItem\" \n\t\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t/>\n\t\t</div>\n\t\t<").concat(config.templatePanelButtons, "\n\t\t\t:options=\"options\" \n\t\t\t:mode=\"mode\"\n\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t@addItem=\"addItem\"\n\t\t\t@changeRowData=\"changeRowData\"\n\t\t\t@changeProduct=\"changeProduct\" \n\t\t\tv-if=\"showButtonsBottom\"\n\t\t/>\n\t\t<").concat(config.templatePanelCompilation, "  \n\t\t\tv-if=\"options.showCompilationModeSwitcher\"\n\t\t\t:compilationOptions=\"options.compilationFormOption\" \n\t\t\t:mode=\"mode\" \n\t\t/>\n\t\t<div class=\"catalog-pf-result-line\"></div>\n\t\t<div class=\"catalog-pf-result-wrapper\" v-if=\"showResultBlock\">\n\t\t\t<table class=\"catalog-pf-result\" v-if=\"showResultBlock\">\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<span class=\"catalog-pf-text\">{{localize.CATALOG_FORM_PRODUCTS_PRICE}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<span v-html=\"productList.total.sum\"\n\t\t\t\t\t\t\t:class=\"productList.total.result !== productList.total.sum ? 'catalog-pf-text catalog-pf-text--line-through' : 'catalog-pf-text'\"\n\t\t\t\t\t\t></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td class=\"catalog-pf-result-padding-bottom\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--discount\">{{localize.CATALOG_FORM_TOTAL_DISCOUNT}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-result-padding-bottom\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--discount\" v-html=\"productList.total.discount\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults && showTaxResult\">\n\t\t\t\t\t<td class=\"catalog-pf-tax\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--tax\">{{localize.CATALOG_FORM_TAX_TITLE}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-tax\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--tax\" v-html=\"productList.total.taxSum\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td class=\"catalog-pf-result-padding\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--total catalog-pf-text--border\">{{totalResultLabel}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-result-padding\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--total\" v-html=\"productList.total.result\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol catalog-pf-symbol--total\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t</div>\n\t</div>\n")
	});

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"\"></div>"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _onBasketChange = new WeakSet();

	var _checkRequiredFields = new WeakSet();

	var _changeCompilationModeSetting = new WeakSet();

	var _setMode = new WeakSet();

	var ProductForm = /*#__PURE__*/function () {
	  function ProductForm() {
	    var _this = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ProductForm);

	    _setMode.add(this);

	    _changeCompilationModeSetting.add(this);

	    _checkRequiredFields.add(this);

	    _onBasketChange.add(this);

	    this.options = this.prepareOptions(options);
	    this.defaultOptions = Object.assign({}, this.options);
	    this.editable = true;

	    _classPrivateMethodGet(this, _setMode, _setMode2).call(this, FormMode.REGULAR);

	    this.wrapper = main_core.Tag.render(_templateObject$3());

	    if (main_core.Text.toNumber(options.iblockId) <= 0) {
	      return;
	    }

	    ProductForm.initStore().then(function (result) {
	      return _this.initTemplate(result);
	    }).catch(function (error) {
	      return ProductForm.showError(error);
	    });
	  }

	  babelHelpers.createClass(ProductForm, [{
	    key: "prepareOptions",
	    value: function prepareOptions() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var settingsCollection = main_core.Extension.getSettings('catalog.product-form');
	      var defaultOptions = {
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
	        showDiscountBlock: settingsCollection.get('showDiscountBlock'),
	        showTaxBlock: settingsCollection.get('showTaxBlock'),
	        allowedDiscountTypes: [catalog_productCalculator.DiscountType.PERCENTAGE, catalog_productCalculator.DiscountType.MONETARY],
	        visibleBlocks: [FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE, FormInputCode.QUANTITY, FormInputCode.RESULT, FormInputCode.DISCOUNT],
	        requiredFields: [],
	        editableFields: [],
	        newItemPosition: FormElementPosition.TOP,
	        buttonsPosition: FormElementPosition.TOP,
	        urlBuilderContext: 'SHOP',
	        hideUnselectedProperties: false,
	        compilationFormType: FormCompilationType.REGULAR,
	        compilationFormOption: {}
	      };

	      if (options.visibleBlocks && !main_core.Type.isArray(options.visibleBlocks)) {
	        delete options.visibleBlocks;
	      }

	      if (options.requiredFields && !main_core.Type.isArray(options.requiredFields)) {
	        delete options.requiredFields;
	      }

	      options = babelHelpers.objectSpread({}, defaultOptions, options);
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
	  }, {
	    key: "layout",
	    value: function layout() {
	      return this.wrapper;
	    }
	  }, {
	    key: "initTemplate",
	    value: function initTemplate(result) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        var context = _this2;
	        _this2.store = result.store;
	        _this2.templateEngine = ui_vue.BitrixVue.createApp({
	          el: _this2.wrapper,
	          store: _this2.store,
	          data: {
	            options: _this2.options,
	            mode: _this2.mode
	          },
	          created: function created() {
	            this.$app = context;
	          },
	          mounted: function mounted() {
	            resolve();
	          },
	          template: "<".concat(config.templateName, " :options=\"options\" :mode=\"mode\"/>")
	        });

	        if (main_core.Type.isStringFilled(_this2.options.currency)) {
	          _this2.setData({
	            currency: _this2.options.currency
	          });

	          currency_currencyCore.CurrencyCore.loadCurrencyFormat(_this2.options.currency);
	        }

	        if (_this2.options.basket.length > 0) {
	          _this2.setData({
	            basket: _this2.options.basket
	          }, {
	            newItemPosition: FormElementPosition.BOTTOM
	          });

	          if (main_core.Type.isObject(_this2.options.totals)) {
	            _this2.store.commit('productList/setTotal', _this2.options.totals);
	          } else {
	            _this2.store.dispatch('productList/calculateTotal');
	          }
	        } else {
	          var newItem = _this2.store.getters['productList/getBaseProduct']();

	          newItem.fields.discountType = _this2.options.defaultDiscountType;

	          _this2.addProduct(newItem);
	        }
	      });
	    }
	  }, {
	    key: "addProduct",
	    value: function addProduct() {
	      var _this3 = this;

	      var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.store.dispatch('productList/addItem', {
	        item: item,
	        position: this.options.newItemPosition
	      }).then(function () {
	        _classPrivateMethodGet(_this3, _onBasketChange, _onBasketChange2).call(_this3);
	      });
	    }
	  }, {
	    key: "changeProduct",
	    value: function changeProduct(product) {
	      var _this4 = this;

	      var fields = product.fields;

	      var result = _classPrivateMethodGet(this, _checkRequiredFields, _checkRequiredFields2).call(this, fields);

	      fields.errors = (result === null || result === void 0 ? void 0 : result.errors) || [];
	      this.store.dispatch('productList/changeItem', {
	        index: product.index,
	        fields: fields
	      }).then(function () {
	        _classPrivateMethodGet(_this4, _onBasketChange, _onBasketChange2).call(_this4);
	      });
	    }
	  }, {
	    key: "removeProduct",
	    value: function removeProduct(product) {
	      var _this5 = this;

	      this.store.dispatch('productList/removeItem', {
	        index: product.index
	      }).then(function () {
	        _classPrivateMethodGet(_this5, _onBasketChange, _onBasketChange2).call(_this5);
	      });
	    }
	  }, {
	    key: "setData",
	    value: function setData(data) {
	      var _this6 = this;

	      var option = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (main_core.Type.isObject(data.basket)) {
	        var formBasket = this.store.getters['productList/getBasket']();
	        data.basket.forEach(function (fields) {
	          if (!main_core.Type.isObject(fields)) {
	            return;
	          }

	          var itemPosition = option.newItemPosition || _this6.options.newItemPosition;
	          var innerId = fields.selectorId;

	          if (main_core.Type.isNil(innerId)) {
	            _this6.store.dispatch('productList/addItem', {
	              item: fields,
	              position: itemPosition
	            });

	            return;
	          }

	          var basketIndex = formBasket.findIndex(function (item) {
	            return item.selectorId === innerId;
	          });

	          if (basketIndex === -1) {
	            _this6.store.dispatch('productList/addItem', {
	              item: fields,
	              position: itemPosition
	            });
	          } else {
	            _this6.store.dispatch('productList/changeItem', {
	              basketIndex: basketIndex,
	              fields: fields
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
	  }, {
	    key: "changeFormOption",
	    value: function changeFormOption(optionName, value) {
	      var _this7 = this;

	      value = value === 'Y' ? 'Y' : 'N';

	      if (optionName === 'isCompilationMode') {
	        if (!this.options.showCompilationModeSwitcher) {
	          return;
	        }

	        var mode = value === 'Y' ? FormMode.COMPILATION : FormMode.REGULAR;

	        _classPrivateMethodGet(this, _changeCompilationModeSetting, _changeCompilationModeSetting2).call(this, mode);

	        return;
	      }

	      this.options[optionName] = value;

	      if (optionName !== 'hiddenCompilationInfoMessage') {
	        var basket = this.store.getters['productList/getBasket']();
	        basket.forEach(function (item, index) {
	          if (optionName === 'showDiscountBlock') {
	            item.showDiscountBlock = value;
	          } else if (optionName === 'showTaxBlock') {
	            item.showTaxBlock = value;
	          } else if (optionName === 'taxIncluded') {
	            item.fields.taxIncluded = value;
	          }

	          _this7.store.dispatch('productList/changeItem', {
	            index: index,
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
	  }, {
	    key: "getTotal",
	    value: function getTotal() {
	      this.store.dispatch('productList/getTotal');
	    }
	  }, {
	    key: "setEditable",
	    value: function setEditable(value) {
	      this.editable = value;

	      if (!value) {
	        _classPrivateMethodGet(this, _setMode, _setMode2).call(this, FormMode.READ_ONLY);
	      } else {
	        _classPrivateMethodGet(this, _setMode, _setMode2).call(this, FormMode.REGULAR);
	      }
	    }
	  }, {
	    key: "hasErrors",
	    value: function hasErrors() {
	      if (!this.store) {
	        return false;
	      }

	      var basket = this.store.getters['productList/getBasket']();
	      var errorItems = basket.filter(function (item) {
	        return item.errors.length > 0;
	      });
	      return errorItems.length > 0;
	    }
	  }], [{
	    key: "initStore",
	    value: function initStore() {
	      var builder = new ui_vue_vuex.VuexBuilder();
	      return builder.addModel(ProductList.create()).build();
	    }
	  }, {
	    key: "showError",
	    value: function showError(error) {
	      console.error(error);
	    }
	  }]);
	  return ProductForm;
	}();

	var _onBasketChange2 = function _onBasketChange2() {
	  main_core_events.EventEmitter.emit(this, 'ProductForm:onBasketChange', {
	    basket: this.store.getters['productList/getBasket']()
	  });
	};

	var _checkRequiredFields2 = function _checkRequiredFields2(fields) {
	  var result = {};

	  if (this.options.requiredFields.length === 0) {
	    return result;
	  }

	  result.errors = [];
	  this.options.requiredFields.forEach(function (code) {
	    switch (code) {
	      case FormInputCode.PRICE:
	        if (fields.price <= 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_PRICE,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_PRICE')
	          });
	        }

	        break;

	      case FormInputCode.QUANTITY:
	        if (fields.quantity <= 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_QUANTITY,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_QUANTITY')
	          });
	        }

	        break;

	      case FormInputCode.BRAND:
	        if (!main_core.Type.isArray(fields.brands) || fields.brands.length === 0) {
	          result.errors.push({
	            code: FormErrorCode.EMPTY_BRAND,
	            message: main_core.Loc.getMessage('CATALOG_FORM_ERROR_EMPTY_BRAND')
	          });
	        }

	        break;
	    }
	  });
	  return result;
	};

	var _changeCompilationModeSetting2 = function _changeCompilationModeSetting2(mode) {
	  var _this8 = this;

	  this.options.requiredFields = [];

	  if (mode === FormMode.COMPILATION) {
	    var compilationRequiredFields = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE, FormInputCode.BRAND];
	    this.options.requiredFields = this.options.visibleBlocks.filter(function (item) {
	      return compilationRequiredFields.includes(item);
	    });
	  }

	  _classPrivateMethodGet(this, _setMode, _setMode2).call(this, mode);

	  var basket = this.store.getters['productList/getBasket']();
	  basket.forEach(function (item, index) {
	    return _this8.changeProduct({
	      index: index,
	      fields: item.fields
	    });
	  });
	};

	var _setMode2 = function _setMode2(mode) {
	  this.mode = mode;

	  if (mode === FormMode.READ_ONLY) {
	    this.options.editableFields = [];
	  } else if (mode === FormMode.COMPILATION) {
	    this.options.editableFields = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE, FormInputCode.BRAND];
	    this.options.visibleBlocks = [FormInputCode.PRODUCT_SELECTOR, FormInputCode.PRICE];

	    if (this.options.compilationFormType === FormCompilationType.FACEBOOK) {
	      this.options.visibleBlocks.push(FormInputCode.BRAND);
	    }

	    this.options.showResults = false;
	  } else {
	    mode = FormMode.REGULAR;
	    this.options.visibleBlocks = this.defaultOptions.visibleBlocks;
	    this.options.showResults = this.defaultOptions.showResults;
	    var visibleBlocks = this.options.visibleBlocks.slice(0);

	    if (visibleBlocks.includes(FormInputCode.RESULT)) {
	      visibleBlocks.splice(visibleBlocks.indexOf(FormInputCode.RESULT), 1);
	    }

	    this.options.editableFields = visibleBlocks;
	  }

	  if (this.templateEngine) {
	    this.templateEngine.mode = mode;
	  }

	  main_core_events.EventEmitter.emit(this, 'ProductForm:onModeChange', {
	    mode: mode
	  });
	};

	exports.ProductForm = ProductForm;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.UI,BX,BX.UI,BX,BX.UI,BX.Catalog,BX.UI.EntitySelector,BX,BX,BX.Main,BX,BX,BX.UI,BX.UI,window,BX,BX,BX,BX,BX,BX.Event,BX.Currency,BX.Catalog));
//# sourceMappingURL=product-form.bundle.js.map
