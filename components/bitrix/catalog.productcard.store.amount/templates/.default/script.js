(function (exports,main_core,main_core_events) {
	'use strict';

	var ProductStoreGridManager = /*#__PURE__*/function () {
	  function ProductStoreGridManager() {
	    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ProductStoreGridManager);
	    babelHelpers.defineProperty(this, "grid", null);
	    babelHelpers.defineProperty(this, "totalWrapper", null);
	    this.gridId = settings.gridId;
	    this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	    this.signedParameters = settings.signedParameters;
	    this.totalWrapperId = settings.totalWrapperId || null;
	    this.inventoryManagementLink = settings.inventoryManagementLink || null;

	    if (this.totalWrapperId) {
	      this.totalWrapper = BX(this.totalWrapperId);
	      this.refreshTotalWrapper();
	    }

	    this.subscribeEvents();
	  }

	  babelHelpers.createClass(ProductStoreGridManager, [{
	    key: "subscribeEvents",
	    value: function subscribeEvents() {
	      this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	    }
	  }, {
	    key: "onGridUpdated",
	    value: function onGridUpdated(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          grid = _event$getCompatData2[0],
	          eventArgs = _event$getCompatData2[1];

	      if (!grid || grid.getId() !== this.getGridId()) {
	        return;
	      }

	      this.refreshTotalWrapper();
	    }
	  }, {
	    key: "getGridId",
	    value: function getGridId() {
	      return this.gridId;
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      if (this.grid) {
	        this.grid.reload();
	      }
	    }
	  }, {
	    key: "setTotalData",
	    value: function setTotalData(totalData) {
	      for (var propertyId in totalData) {
	        if (totalData.hasOwnProperty(propertyId)) {
	          this.setTotalDataBySelector("#".concat(propertyId), totalData[propertyId]);
	        }
	      }
	    }
	  }, {
	    key: "setTotalDataBySelector",
	    value: function setTotalDataBySelector(selector, data) {
	      if (this.totalWrapper) {
	        var totalWrapperItem = this.totalWrapper.querySelector(selector);

	        if (totalWrapperItem) {
	          totalWrapperItem.innerHTML = data;
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "hideTotalData",
	    value: function hideTotalData() {
	      BX.hide(this.totalWrapper);
	    }
	  }, {
	    key: "showTotalData",
	    value: function showTotalData() {
	      BX.show(this.totalWrapper);
	    }
	  }, {
	    key: "refreshTotalWrapper",
	    value: function refreshTotalWrapper() {
	      var _this = this;

	      if (this.totalWrapper) {
	        //this.grid.tableFade();
	        BX.ajax.runComponentAction('bitrix:catalog.productcard.store.amount', 'getStoreAmountTotal', {
	          mode: 'ajax',
	          data: {
	            signedParameters: this.signedParameters
	          }
	        }).then(function (response) {
	          var amount = response.data.AMOUNT || '';
	          var quantity = response.data.QUANTITY || '';
	          var quantityReserved = response.data.QUANTITY_RESERVED || '';
	          var quantityCommon = response.data.QUANTITY_COMMON || '';

	          if (amount || quantity || quantityCommon || quantityReserved) {
	            var totalData = {
	              'total_amount': amount,
	              'total_quantity': quantity,
	              'total_quantity_common': quantityCommon,
	              'total_quantity_reserved': quantityReserved
	            };

	            _this.setTotalData(totalData);

	            if (BX.isNodeHidden(_this.totalWrapper)) {
	              _this.showTotalData();
	            }
	          } else {
	            _this.hideTotalData();
	          } //this.grid.tableUnfade();

	        });
	      }
	    }
	  }, {
	    key: "openInventoryManagementSlider",
	    value: function openInventoryManagementSlider() {
	      if (this.inventoryManagementLink) {
	        BX.SidePanel.Instance.open(this.inventoryManagementLink, {
	          cacheable: false,
	          data: {
	            openGridOnDone: false
	          },
	          events: {
	            onCloseComplete: function onCloseComplete(event) {
	              var slider = event.getSlider();

	              if (!slider) {
	                return;
	              }

	              if (slider.getData().get('isInventoryManagementEnabled')) {
	                window.top.location.reload();
	              }
	            }
	          }
	        });
	      }
	    }
	  }]);
	  return ProductStoreGridManager;
	}();

	main_core.Reflection.namespace('BX.Catalog').ProductStoreGridManager = ProductStoreGridManager;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
