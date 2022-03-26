(function (exports,main_core,main_core_events) {
	'use strict';

	var StoreAmountDetails = /*#__PURE__*/function () {
	  function StoreAmountDetails(settings) {
	    babelHelpers.classCallCheck(this, StoreAmountDetails);
	    this.gridId = settings.gridId;
	    this.productId = settings.productId;
	    this.onFilterApplyHandler = this.onFilterApply.bind(this);
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);
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

	main_core.Reflection.namespace('BX.Catalog').StoreAmountDetails = StoreAmountDetails;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
