this.BX = this.BX || {};
(function (exports,ui_notification,currency,ui_layoutForm,ui_forms,ui_buttons,catalog_productSelector,ui_common,ui_alerts,ui_vue_vuex,main_popup,main_core,ui_vue,main_core_events,currency_currencyCore,catalog_productCalculator) {
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
	          properties: []
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
	  templateName: 'bx-product-form',
	  templateProductAddName: 'bx-product-add',
	  templateProductRowName: 'bx-product-form-row',
	  moduleId: 'catalog'
	});

	ui_vue.Vue.component(config.templateProductRowName, {
	  /**
	   * @emits 'changeProduct' {index: number, fields: object}
	   * @emits 'changeRowData' {index: number, fields: object}
	   * @emits 'refreshBasket'
	   * @emits 'removeItem' {index: number}
	   */
	  props: ['basketItem', 'basketItemIndex', 'countItems', 'options', 'editable'],
	  data: function data() {
	    return {
	      currencySymbol: null,
	      productSelector: null,
	      imageControlId: null,
	      selectorId: this.basketItem.selectorId
	    };
	  },
	  created: function created() {
	    var _this = this;

	    var defaultFields = this.basketItem.fields;
	    var defaultPrice = main_core.Text.toNumber(defaultFields.price);
	    var basePrice = this.basketItem.fields.basePrice || defaultPrice;
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

	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onChange', this.onProductChange.bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Catalog.ProductSelector:onClear', this.onProductClear.bind(this));
	    this.onInputPrice = main_core.Runtime.debounce(this.changePrice, 500, this);
	    this.onInputQuantity = main_core.Runtime.debounce(this.changeQuantity, 500, this);
	    this.onInputDiscount = main_core.Runtime.debounce(this.changeDiscount, 500, this);
	  },
	  mounted: function mounted() {
	    this.productSelector = new catalog_productSelector.ProductSelector(this.selectorId, this.prepareSelectorParams());
	    this.productSelector.renderTo(this.$refs.selectorWrapper);
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
	        imageValues: [],
	        config: {
	          DETAIL_PATH: this.basketItem.detailUrl || '',
	          ENABLE_SEARCH: true,
	          ENABLE_INPUT_DETAIL_LINK: true,
	          ENABLE_IMAGE_CHANGE_SAVING: true,
	          ENABLE_EMPTY_PRODUCT_ERROR: this.options.enableEmptyProductError,
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

	      if (main_core.Text.toNumber(fields.QUANTITY) > 0) {
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
	        fields = Object.assign(this.getCalculator().calculatePrice(basePrice), fields);
	        this.getCalculator().setFields(fields);
	        this.setCalculatedFields(fields);
	      }
	    },
	    onProductClear: function onProductClear(event) {
	      var data = event.getData();

	      if (main_core.Type.isStringFilled(data.selectorId) && data.selectorId === this.productSelector.getId()) {
	        var fields = this.getCalculator().calculatePrice(0);
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
	    toggleDiscount: function toggleDiscount(value) {
	      var _this2 = this;

	      if (!this.editable) {
	        return;
	      }

	      this.changeRowData({
	        showDiscount: value
	      });
	      value === 'Y' ? setTimeout(function () {
	        return _this2.$refs.discountInput.focus();
	      }) : null;
	    },
	    toggleTax: function toggleTax(value) {
	      this.changeRowData({
	        showTax: value
	      });
	    },
	    changeQuantity: function changeQuantity(event) {
	      if (!this.editable) {
	        return;
	      }

	      event.target.value = event.target.value.replace(/[^.\d]/g, '.');
	      var newQuantity = parseFloat(event.target.value);
	      var lastSymbol = event.target.value.substr(-1);

	      if (!newQuantity || lastSymbol === '.') {
	        return;
	      }

	      var calculatedFields = this.getCalculator().calculateQuantity(newQuantity);
	      this.setCalculatedFields(calculatedFields);
	      this.getCalculator().setFields(calculatedFields);
	    },
	    changePrice: function changePrice(event) {
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

	      var calculatedFields = this.getCalculator().calculatePrice(newPrice);
	      calculatedFields.BASE_PRICE = newPrice;
	      this.getCalculator().setFields(calculatedFields);
	      this.setCalculatedFields(calculatedFields);
	    },

	    /**
	     *
	     * @param discountType {string}
	     */
	    changeDiscountType: function changeDiscountType(discountType) {
	      if (!this.editable) {
	        return;
	      }

	      var type = main_core.Text.toNumber(discountType) === catalog_productCalculator.DiscountType.MONETARY ? catalog_productCalculator.DiscountType.MONETARY : catalog_productCalculator.DiscountType.PERCENTAGE;
	      var calculatedFields = this.getCalculator().calculateDiscountType(type);
	      this.getCalculator().setFields(calculatedFields);
	      this.setCalculatedFields(calculatedFields);
	    },
	    changeDiscount: function changeDiscount(event) {
	      var discountValue = main_core.Text.toNumber(event.target.value) || 0;

	      if (discountValue === main_core.Text.toNumber(this.basketItem.discount) || !this.editable) {
	        return;
	      }

	      var calculatedFields = this.getCalculator().calculateDiscount(discountValue);
	      this.getCalculator().setFields(calculatedFields);
	      this.setCalculatedFields(calculatedFields);
	    },
	    changeTax: function changeTax(taxValue) {
	      if (taxValue === main_core.Text.toNumber(this.basketItem.tax) || !this.editable) {
	        return;
	      }

	      var calculatedFields = this.getCalculator().calculateTax(taxValue);
	      this.getCalculator().setFields(calculatedFields);
	      this.setCalculatedFields(calculatedFields);
	    },
	    changeTaxIncluded: function changeTaxIncluded(taxIncluded) {
	      if (taxIncluded === this.basketItem.taxIncluded || !this.editable) {
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
	    openDiscountEditor: function openDiscountEditor(e, url) {
	      if (!(window.top.BX.SidePanel && window.top.BX.SidePanel.Instance)) {
	        return;
	      }

	      window.top.BX.SidePanel.Instance.open(BX.util.add_url_param(url, {
	        "IFRAME": "Y",
	        "IFRAME_TYPE": "SIDE_SLIDER",
	        "publicSidePanel": "Y"
	      }), {
	        allowChangeHistory: false
	      });
	      e.preventDefault ? e.preventDefault() : e.returnValue = false;
	    },
	    isEmptyProductName: function isEmptyProductName() {
	      return this.basketItem.name.length === 0;
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
	    incrementQuantity: function incrementQuantity() {
	      if (!this.editable) {
	        return;
	      }

	      var correctionFactor = this.calculateCorrectionFactor(this.basketItem.quantity, this.basketItem.measureRatio);
	      var quantity = (this.basketItem.quantity * correctionFactor + this.basketItem.measureRatio * correctionFactor) / correctionFactor;
	      this.changeQuantity(quantity);
	    },
	    decrementQuantity: function decrementQuantity() {
	      if (this.basketItem.quantity > this.basketItem.measureRatio && this.editable) {
	        var correctionFactor = this.calculateCorrectionFactor(this.basketItem.quantity, this.basketItem.measureRatio);
	        var quantity = (this.basketItem.quantity * correctionFactor - this.basketItem.measureRatio * correctionFactor) / correctionFactor;
	        this.changeQuantity(quantity);
	      }
	    },
	    showPopupMenu: function showPopupMenu(target, array, type) {
	      var _this3 = this;

	      if (!this.editable) {
	        return;
	      }

	      var menuItems = [];

	      var setItem = function setItem(ev, param) {
	        if (type === 'tax') {
	          _this3.changeTax(main_core.Text.toNumber(param.options.item));
	        } else if (type === 'measures') {
	          var productFields = _this3.basketItem.fields;
	          productFields['measureCode'] = param.options.item.CODE;
	          productFields['measureName'] = param.options.item.SYMBOL;

	          _this3.changeProduct(productFields);
	        } else {
	          target.innerHTML = ev.target.innerHTML;

	          if (type === 'discount') {
	            _this3.changeDiscountType(param.options.type);
	          }
	        }

	        _this3.popupMenu.close();
	      };

	      if (type === 'discount') {
	        array = [];

	        if (main_core.Type.isArray(this.options.allowedDiscountTypes)) {
	          if (this.options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.PERCENTAGE)) {
	            array[catalog_productCalculator.DiscountType.PERCENTAGE] = '%';
	          }

	          if (this.options.allowedDiscountTypes.includes(catalog_productCalculator.DiscountType.MONETARY)) {
	            array[catalog_productCalculator.DiscountType.MONETARY] = this.currencySymbol;
	          }
	        }
	      }

	      if (array) {
	        for (var item in array) {
	          var text = array[item];

	          if (type === 'measures') {
	            text = array[item].SYMBOL;
	          } else if (type === 'tax') {
	            text = text + '%';
	          }

	          menuItems.push({
	            text: text,
	            item: array[item],
	            onclick: setItem.bind({
	              value: 'settswguy'
	            }),
	            type: type === 'discount' ? item : null
	          });
	        }
	      }

	      if (menuItems.length > 0) {
	        this.popupMenu = new main_popup.Menu({
	          bindElement: target,
	          items: menuItems
	        });
	        this.popupMenu.show();
	      }
	    },
	    showProductTooltip: function showProductTooltip(e) {
	      if (!this.productTooltip) {
	        this.productTooltip = new main_popup.Popup({
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
	    hideProductTooltip: function hideProductTooltip() {
	      this.productTooltip ? this.productTooltip.close() : null;
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
	    getDiscountSymbol: function getDiscountSymbol() {
	      return main_core.Text.toNumber(this.basketItem.fields.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE ? '%' : this.currencySymbol;
	    },
	    getDiscountInputValue: function getDiscountInputValue() {
	      if (main_core.Text.toNumber(this.basketItem.fields.discountType) === catalog_productCalculator.DiscountType.PERCENTAGE) {
	        return main_core.Text.toNumber(this.basketItem.fields.discountRate);
	      }

	      return main_core.Text.toNumber(this.basketItem.fields.discount);
	    },
	    getPriceExclusive: function getPriceExclusive() {
	      return this.basketItem.fields.priceExclusive || this.basketItem.fields.price;
	    },
	    showDiscountBlock: function showDiscountBlock() {
	      return this.options.showDiscountBlock === 'Y' && (this.editable || !this.editable && this.basketItem.fields.discount > 0);
	    },
	    showTaxBlock: function showTaxBlock() {
	      return this.options.showTaxBlock === 'Y' && this.getTaxList.length > 0 && (this.editable || !this.editable && this.showBasePrice);
	    },
	    showRemoveIcon: function showRemoveIcon() {
	      if (!this.editable) {
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
	    isNotEnoughQuantity: function isNotEnoughQuantity() {
	      return this.basketItem.errors.includes('SALE_BASKET_AVAILABLE_QUANTITY');
	    },
	    hasPriceError: function hasPriceError() {
	      return this.basketItem.errors.includes('SALE_BASKET_ITEM_WRONG_PRICE');
	    }
	  },
	  template: "\n\t\t<div class=\"catalog-pf-product-item\" v-bind:class=\"{ 'catalog-pf-product-item--borderless': !editable && basketItemIndex === 0 }\">\n\t\t\t<div class=\"catalog-pf-product-item--remove\" @click=\"removeItem\" v-if=\"showRemoveIcon\"></div>\n\t\t\t<div class=\"catalog-pf-product-item--num\">\n\t\t\t\t<div class=\"catalog-pf-product-index\">{{basketItemIndex + 1}}</div>\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-item--left\">\n\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-pf-product-label\">{{localize.CATALOG_FORM_NAME}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-pf-product-item-section\" :id=\"selectorId\" ref=\"selectorWrapper\"></div>\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-item--right\">\n\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-pf-product-label\" style=\"width: 94px\">{{localize.CATALOG_FORM_PRICE}}</div>\n\t\t\t\t\t<div class=\"catalog-pf-product-label\" style=\"width: 72px\">{{localize.CATALOG_FORM_QUANTITY}}</div>\n\t\t\t\t\t<div class=\"catalog-pf-product-label\" style=\"width: 94px\">{{localize.CATALOG_FORM_RESULT}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-pf-product-control\" style=\"width: 94px\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-wrapper\">\n\t\t\t\t\t\t\t<input \ttype=\"text\" class=\"catalog-pf-product-input catalog-pf-product-input--align-right\"\n\t\t\t\t\t\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t\t\t\t\t\t:value=\"basketItem.fields.basePrice\"\n\t\t\t\t\t\t\t\t\t@input=\"onInputPrice\"\n\t\t\t\t\t\t\t\t\t:disabled=\"!editable\">\n\t\t\t\t\t\t\t<div class=\"catalog-pf-product-input-info\" v-html=\"currencySymbol\"></div>\n\t\t\t\t\t\t</div>\t\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"catalog-pf-product-control\" style=\"width: 72px\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-wrapper\">\n\t\t\t\t\t\t\t<input \ttype=\"text\" class=\"catalog-pf-product-input\"\n\t\t\t\t\t\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t\t\t\t\t\t:value=\"basketItem.fields.quantity\"\n\t\t\t\t\t\t\t\t\t@input=\"onInputQuantity\"\n\t\t\t\t\t\t\t\t\t:disabled=\"!editable\">\n\t\t\t\t\t\t\t<div \tclass=\"catalog-pf-product-input-info catalog-pf-product-input-info--action\" \n\t\t\t\t\t\t\t\t\t@click=\"showPopupMenu($event.target, options.measures, 'measures')\"><span>{{ getMeasureName }}</span></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"catalog-pf-product-control\" style=\"width: 94px\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-wrapper\">\n\t\t\t\t\t\t\t<input disabled type=\"text\" class=\"catalog-pf-product-input catalog-pf-product-input--disabled catalog-pf-product-input--gray catalog-pf-product-input--align-right\" :value=\"basketItem.sum\">\n\t\t\t\t\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input--disabled catalog-pf-product-input--gray\" v-html=\"currencySymbol\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showDiscountBlock\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div v-if=\"showDiscount\" class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide\" @click=\"toggleDiscount('N')\">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>\n\t\t\t\t\t<div v-else class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show\" @click=\"toggleDiscount('Y')\">{{localize.CATALOG_FORM_DISCOUNT_TITLE}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showDiscount\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div class=\"catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--left\">\n\t\t\t\t\t\t<input class=\"catalog-pf-product-input catalog-pf-product-input--align-right catalog-pf-product-input--right\"\n\t\t\t\t\t\t\t\tv-bind:class=\"{ 'catalog-pf-product-input--disabled': !editable }\"\n\t\t\t\t\t\t\t\tref=\"discountInput\" \n\t\t\t\t\t\t\t\t:value=\"getDiscountInputValue\"\n\t\t\t\t\t\t\t\t@input=\"onInputDiscount\"\n\t\t\t\t\t\t\t\tplaceholder=\"0\"\n\t\t\t\t\t\t\t\t:disabled=\"!editable\">\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input-info--action\" \n\t\t\t\t\t\t\t@click=\"showPopupMenu($event.target, null, 'discount')\">\n\t\t\t\t\t\t\t<span v-html=\"getDiscountSymbol\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div v-if=\"showTaxBlock\" class=\"catalog-pf-product-item-section catalog-pf-product-item-section--dashed\">\n\t\t\t\t\t<div v-if=\"showTaxSelector\" class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--hide\" @click=\"toggleTax('N')\">{{localize.CATALOG_FORM_TAX_TITLE}}</div>\n\t\t\t\t\t<div v-else class=\"catalog-pf-product-link-toggler catalog-pf-product-link-toggler--show\" @click=\"toggleTax('Y')\">{{localize.CATALOG_FORM_TAX_TITLE}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showTaxSelector && showTaxBlock\" class=\"catalog-pf-product-item-section\">\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass=\"catalog-pf-product-input-wrapper catalog-pf-product-input-wrapper--right\"\n\t\t\t\t\t\t@click=\"showPopupMenu($event.target, getTaxList, 'tax')\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input\">{{basketItem.fields.tax}}%</div>\n\t\t\t\t\t\t<div class=\"catalog-pf-product-input-info catalog-pf-product-input-info--dropdown\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"catalog-pf-product-item-section catalog-pf-product-item-section--dashed\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class='catalog-pf-product-config-popup'></div>\n\t\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<input type=\"checkbox\"  class=\"ui-ctl-element\">\n\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	ui_vue.Vue.component(config.templateProductAddName, {
	  /**
	   * @emits 'changeRowData' {index: number, fields: object}
	   * @emits 'refreshBasket'
	   * @emits 'addItem'
	   */
	  props: ['options'],
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
	      var input = main_core.Tag.render(_templateObject());
	      input.checked = item.checked;
	      input.dataset.settingId = item.id;
	      var setting = main_core.Tag.render(_templateObject2(), input, item.title);
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
	      var content = main_core.Tag.render(_templateObject3());
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
	  template: "\n\t\t<div class=\"catalog-pf-product-add\">\n\t\t\t<div class=\"catalog-pf-product-add-wrapper\">\n\t\t\t\t<span class=\"catalog-pf-product-add-link\" @click=\"addBasketItemForm\">{{localize.CATALOG_FORM_ADD_PRODUCT}}</span>\n\t\t\t\t<span class=\"catalog-pf-product-add-link catalog-pf-product-add-link--gray\" @click=\"showDialogProductSearch\">{{localize.CATALOG_FORM_ADD_PRODUCT_FROM_CATALOG}}</span>\n\t\t\t</div>\n\t\t\t<div class=\"catalog-pf-product-configure-link\" @click=\"showConfigPopup\">{{localize.CATALOG_FORM_DISCOUNT_EDIT_PAGE_URL_TITLE}}</div>\n\t\t</div>\n\t"
	});

	ui_vue.Vue.component(config.templateName, {
	  props: ['options'],
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
	    editable: function editable() {
	      return this.$root.$app.editable;
	    },
	    showTaxResult: function showTaxResult() {
	      return this.options.showTaxBlock !== 'N';
	    },
	    showResults: function showResults() {
	      return this.options.showResults !== false;
	    },
	    showButtonsTop: function showButtonsTop() {
	      return this.options.singleProductMode !== true && this.editable && this.options.buttonsPosition !== FormElementPosition.BOTTOM;
	    },
	    showButtonsBottom: function showButtonsBottom() {
	      return this.options.singleProductMode !== true && this.editable && this.options.buttonsPosition === FormElementPosition.BOTTOM;
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
	  template: "\n\t<div class=\"catalog-product-form-container\">\n\t\t<".concat(config.templateProductAddName, "\n\t\t\t:options=\"options\" \n\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t@addItem=\"addItem\"\n\t\t\t@changeRowData=\"changeRowData\"\n\t\t\t@changeProduct=\"changeProduct\" \n\t\t\tv-if=\"showButtonsTop\"\n\t\t/>\n\t\t<div v-for=\"(item, index) in productList.basket\" :key=\"item.selectorId\">\n\t\t\t<").concat(config.templateProductRowName, " \n\t\t\t\t:basketItem=\"item\" \n\t\t\t\t:basketItemIndex=\"index\"  \n\t\t\t\t:countItems=\"countItems\"\n\t\t\t\t:options=\"options\"\n\t\t\t\t:editable=\"editable\"\n\t\t\t\t@changeProduct=\"changeProduct\" \n\t\t\t\t@changeRowData=\"changeRowData\" \n\t\t\t\t@removeItem=\"removeItem\" \n\t\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t/>\n\t\t</div>\n\t\t<").concat(config.templateProductAddName, "\n\t\t\t:options=\"options\" \n\t\t\t@refreshBasket=\"refreshBasket\" \n\t\t\t@addItem=\"addItem\"\n\t\t\t@changeRowData=\"changeRowData\"\n\t\t\t@changeProduct=\"changeProduct\" \n\t\t\tv-if=\"showButtonsBottom\"\n\t\t/>\n\t\t<div class=\"catalog-pf-result-line\"></div>\n\t\t<div class=\"catalog-pf-result-wrapper\" v-if=\"showResultBlock\">\n\t\t\t<table class=\"catalog-pf-result\" v-if=\"showResultBlock\">\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<span class=\"catalog-pf-text\">{{localize.CATALOG_FORM_PRODUCTS_PRICE}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td>\n\t\t\t\t\t\t<span v-html=\"productList.total.sum\"\n\t\t\t\t\t\t\t:class=\"productList.total.result !== productList.total.sum ? 'catalog-pf-text catalog-pf-text--line-through' : 'catalog-pf-text'\"\n\t\t\t\t\t\t></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td class=\"catalog-pf-result-padding-bottom\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--discount\">{{localize.CATALOG_FORM_TOTAL_DISCOUNT}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-result-padding-bottom\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--discount\" v-html=\"productList.total.discount\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults && showTaxResult\">\n\t\t\t\t\t<td class=\"catalog-pf-tax\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--tax\">{{localize.CATALOG_FORM_TAX_TITLE}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-tax\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--tax\" v-html=\"productList.total.taxSum\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t\t<tr v-if=\"showResults\">\n\t\t\t\t\t<td class=\"catalog-pf-result-padding\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--total catalog-pf-text--border\">{{totalResultLabel}}:</span>\n\t\t\t\t\t</td>\n\t\t\t\t\t<td class=\"catalog-pf-result-padding\">\n\t\t\t\t\t\t<span class=\"catalog-pf-text catalog-pf-text--total\" v-html=\"productList.total.result\"></span>\n\t\t\t\t\t\t<span class=\"catalog-pf-symbol catalog-pf-symbol--total\" v-html=\"options.currencySymbol\"></span>\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</table>\n\t\t</div>\n\t</div>\n")
	});

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"\"></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _onBasketChange = new WeakSet();

	var ProductForm = /*#__PURE__*/function () {
	  function ProductForm() {
	    var _this = this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ProductForm);

	    _onBasketChange.add(this);

	    this.options = this.prepareOptions(options);
	    this.editable = true;
	    this.wrapper = main_core.Tag.render(_templateObject$1());

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
	        enableEmptyProductError: true,
	        pricePrecision: 2,
	        currency: settingsCollection.get('currency'),
	        currencySymbol: settingsCollection.get('currencySymbol'),
	        taxIncluded: settingsCollection.get('taxIncluded'),
	        showDiscountBlock: settingsCollection.get('showDiscountBlock'),
	        showTaxBlock: settingsCollection.get('showTaxBlock'),
	        allowedDiscountTypes: [catalog_productCalculator.DiscountType.PERCENTAGE, catalog_productCalculator.DiscountType.MONETARY],
	        newItemPosition: FormElementPosition.TOP,
	        buttonsPosition: FormElementPosition.TOP,
	        urlBuilderContext: 'SHOP',
	        hideUnselectedProperties: false
	      };
	      options = babelHelpers.objectSpread({}, defaultOptions, options);
	      options.showTaxBlock = 'N';
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
	        _this2.templateEngine = ui_vue.Vue.create({
	          el: _this2.wrapper,
	          store: _this2.store,
	          data: {
	            options: _this2.options
	          },
	          created: function created() {
	            this.$app = context;
	          },
	          mounted: function mounted() {
	            resolve();
	          },
	          template: "<".concat(config.templateName, " :options=\"options\"/>")
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

	      this.store.dispatch('productList/changeItem', {
	        index: product.index,
	        fields: product.fields
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
	      this.options[optionName] = value;
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
	    }
	  }], [{
	    key: "initStore",
	    value: function initStore() {
	      var builder = new ui_vue_vuex.VuexBuilder();
	      return builder.addModel(ProductList.create()).useNamespace(true).build();
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

	exports.ProductForm = ProductForm;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX,BX.UI,BX,BX.UI,BX.Catalog,BX,BX.UI,BX,BX.Main,BX,BX,BX.Event,BX.Currency,BX.Catalog));
//# sourceMappingURL=product-form.bundle.js.map
