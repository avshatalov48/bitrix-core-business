(function (exports,main_core,main_core_events,catalog_accessDeniedInput) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getGrid = /*#__PURE__*/new WeakSet();

	var _initPurchasingPrice = /*#__PURE__*/new WeakSet();

	var StoreAmountDetails = /*#__PURE__*/function () {
	  function StoreAmountDetails(settings) {
	    babelHelpers.classCallCheck(this, StoreAmountDetails);

	    _classPrivateMethodInitSpec(this, _initPurchasingPrice);

	    _classPrivateMethodInitSpec(this, _getGrid);

	    this.gridId = settings.gridId;
	    this.productId = settings.productId;
	    this.allowPurchasingPrice = settings.allowPurchasingPrice;
	    this.onFilterApplyHandler = this.onFilterApply.bind(this);
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);

	    if (!this.allowPurchasingPrice) {
	      _classPrivateMethodGet(this, _initPurchasingPrice, _initPurchasingPrice2).call(this);

	      main_core_events.EventEmitter.subscribe('Grid::updated', _classPrivateMethodGet(this, _initPurchasingPrice, _initPurchasingPrice2).bind(this));
	    }
	  }

	  babelHelpers.createClass(StoreAmountDetails, [{
	    key: "getGridId",
	    value: function getGridId() {
	      return this.gridId;
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this.productId;
	    }
	  }, {
	    key: "onFilterApply",
	    value: function onFilterApply(event) {
	      var _this = this;

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          gridId = _event$getCompatData2[0];

	      if (gridId !== this.getGridId()) {
	        return;
	      }

	      BX.ajax.runComponentAction('bitrix:catalog.productcard.store.amount.details', 'updateTotalData', {
	        mode: 'class',
	        data: {
	          productId: this.getProductId()
	        }
	      }).then(function (response) {
	        var _response$data;

	        var totalData = response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.TOTAL_DATA;

	        if (!totalData) {
	          return;
	        }

	        var quantityAvailableNode = document.getElementById(_this.getGridId() + '_total_quantity_available');

	        if (quantityAvailableNode) {
	          quantityAvailableNode.innerHTML = totalData.QUANTITY_AVAILABLE;
	        }

	        var quantityReservedNode = document.getElementById(_this.getGridId() + '_total_quantity_reserved');

	        if (quantityReservedNode) {
	          quantityReservedNode.innerHTML = totalData.QUANTITY_RESERVED;
	        }

	        var quantityCommonNode = document.getElementById(_this.getGridId() + '_total_quantity_common');

	        if (quantityCommonNode) {
	          quantityCommonNode.innerHTML = totalData.QUANTITY_COMMON;
	        }

	        var totalPriceNode = document.getElementById(_this.getGridId() + '_total_price');

	        if (totalPriceNode) {
	          totalPriceNode.innerHTML = totalData.PRICE;
	        }
	      });
	    }
	  }]);
	  return StoreAmountDetails;
	}();

	function _getGrid2() {
	  if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	    throw Error("Cannot find grid");
	  }

	  return BX.Main.gridManager.getInstanceById(this.getGridId());
	}

	function _initPurchasingPrice2() {
	  _classPrivateMethodGet(this, _getGrid, _getGrid2).call(this).getContainer().querySelectorAll('purchasing-price').forEach(function (element) {
	    var input = new catalog_accessDeniedInput.AccessDeniedInput({
	      hint: main_core.Loc.getMessage('CATALOG_PRODUCTCARD_STORE_AMOUNT_DETAILS_PURCHASING_PRICE_HINT'),
	      isReadOnly: true
	    });
	    input.renderTo(element);
	  });
	}

	main_core.Reflection.namespace('BX.Catalog').StoreAmountDetails = StoreAmountDetails;

}((this.window = this.window || {}),BX,BX.Event,BX.Catalog));
//# sourceMappingURL=script.js.map
