this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,landing_ui_field_basefield,catalog_productForm,catalog_productCalculator,landing_pageobject,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	var ProductField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ProductField, _BaseField);

	  function ProductField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ProductField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.ProductField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.setLayoutClass('landing-ui-field-product');

	    _this.onBasketChange = _this.onBasketChange.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.append(_this.getProductSelector().wrapper, _this.input);

	    _this.setProducts(_this.options.items);

	    var root = landing_pageobject.PageObject.getRootWindow();
	    root.BX.Event.EventEmitter.subscribe(_this.getProductSelector(), 'ProductForm:onBasketChange', _this.onBasketChange);
	    return _this;
	  }

	  babelHelpers.createClass(ProductField, [{
	    key: "setProducts",
	    value: function setProducts(products) {
	      this.cache.set('products', main_core.Runtime.clone(products));
	    }
	  }, {
	    key: "getProducts",
	    value: function getProducts() {
	      return this.cache.get('products') || [];
	    }
	  }, {
	    key: "onBasketChange",
	    value: function onBasketChange(event) {
	      var data = event.getData();
	      this.setProducts(data.basket);
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getProducts().reduce(function (acc, item) {
	        if (!main_core.Type.isNil(item.offerId) || !main_core.Type.isNil(item.fields.productId)) {
	          var pics = [];

	          if (item.image && item.image.path) {
	            pics.push(item.image.path);
	          } else if (item.image && item.image.preview) {
	            var ic = document.createElement('div');
	            ic.innerHTML = item.image.preview;
	            ic = ic.querySelector('img');

	            if (ic && ic.src) {
	              pics.push(ic.src);
	            }
	          }

	          var value = item.offerId || item.fields.productId;

	          if (acc.some(function (item) {
	            return item.value === value;
	          })) {
	            return acc;
	          }

	          acc.push({
	            label: item.fields.name,
	            changeablePrice: false,
	            discount: item.fields.discount,
	            pics: pics,
	            price: item.fields.price,
	            quantity: [],
	            selected: false,
	            value: value
	          });
	        }

	        return acc;
	      }, []);
	    }
	  }, {
	    key: "getProductSelector",
	    value: function getProductSelector() {
	      var _this2 = this;

	      return this.cache.remember('productSelector', function () {
	        var root = landing_pageobject.PageObject.getRootWindow();
	        return new root.BX.Catalog.ProductForm({
	          iblockId: _this2.options.iblockId,
	          showResults: false,
	          allowedDiscountTypes: [catalog_productCalculator.DiscountType.MONETARY],
	          buttonsPosition: 'BOTTOM',
	          newItemPosition: 'BOTTOM',
	          basket: _this2.options.items
	        });
	      });
	    }
	  }]);
	  return ProductField;
	}(landing_ui_field_basefield.BaseField);

	exports.ProductField = ProductField;

}((this.BX.Landing.Ui.Field = this.BX.Landing.Ui.Field || {}),BX.Landing.UI.Field,BX.Catalog,BX.Catalog,BX.Landing,BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=productfield.bundle.js.map
