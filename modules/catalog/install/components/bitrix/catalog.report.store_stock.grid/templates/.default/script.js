(function (exports,main_core) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _productListSliderUrl = /*#__PURE__*/new WeakMap();

	var _productListSliderFilter = /*#__PURE__*/new WeakMap();

	var StoreStockGrid = /*#__PURE__*/function () {
	  function StoreStockGrid(options) {
	    babelHelpers.classCallCheck(this, StoreStockGrid);

	    _classPrivateFieldInitSpec(this, _productListSliderUrl, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _productListSliderFilter, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _productListSliderUrl, options.productListSliderUrl);
	    babelHelpers.classPrivateFieldSet(this, _productListSliderFilter, options.productListSliderFilter);
	  }

	  babelHelpers.createClass(StoreStockGrid, [{
	    key: "openStoreProductListGrid",
	    value: function openStoreProductListGrid(storeId) {
	      BX.SidePanel.Instance.open("".concat(babelHelpers.classPrivateFieldGet(this, _productListSliderUrl), "?storeId=").concat(storeId), {
	        requestMethod: "post",
	        requestParams: {
	          filter: babelHelpers.classPrivateFieldGet(this, _productListSliderFilter),
	          openedFromReport: true
	        },
	        cacheable: false
	      });
	    }
	  }]);
	  return StoreStockGrid;
	}();

	main_core.Reflection.namespace('BX.Catalog.Report.StoreStock').StoreGrid = StoreStockGrid;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
