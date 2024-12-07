/* eslint-disable */
(function (exports,main_core,main_core_events,catalog_storeEnableWizard) {
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
	    this.productId = settings.productId;
	    this.reservedDealsSliderLink = settings.reservedDealsSliderLink;
	    if (this.totalWrapperId) {
	      this.totalWrapper = BX(this.totalWrapperId);
	      this.refreshTotalWrapper();
	    }
	    this.subscribeEvents();
	    this.bindSliderToReservedQuantityNodes();
	  }
	  babelHelpers.createClass(ProductStoreGridManager, [{
	    key: "subscribeEvents",
	    value: function subscribeEvents() {
	      this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
	      main_core_events.EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	    }
	  }, {
	    key: "bindSliderToReservedQuantityNodes",
	    value: function bindSliderToReservedQuantityNodes() {
	      var _this = this;
	      var rows = this.grid.getRows().getRows();
	      rows.forEach(function (row) {
	        if (row.isBodyChild() && !row.isTemplate()) {
	          var reservedQuantityNode = row.getNode().querySelector('.main-grid-cell-content-store-amount-reserved-quantity');
	          if (main_core.Type.isDomNode(reservedQuantityNode)) {
	            main_core.Event.bind(reservedQuantityNode, 'click', _this.openDealsWithReservedProductSlider.bind(_this, _this.productId, row.getId()));
	          }
	        }
	      });
	    }
	  }, {
	    key: "openDealsWithReservedProductSlider",
	    value: function openDealsWithReservedProductSlider(rowId) {
	      var storeId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	      if (!this.reservedDealsSliderLink) {
	        return;
	      }
	      var sliderLink = new main_core.Uri(this.reservedDealsSliderLink);
	      sliderLink.setQueryParam('productId', rowId);
	      if (storeId > 0) {
	        sliderLink.setQueryParam('storeId', storeId);
	      }
	      BX.SidePanel.Instance.open(sliderLink.toString(), {
	        allowChangeHistory: false,
	        cacheable: false
	      });
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
	      this.bindSliderToReservedQuantityNodes();
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
	      var _this2 = this;
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
	            _this2.setTotalData(totalData);
	            if (BX.isNodeHidden(_this2.totalWrapper)) {
	              _this2.showTotalData();
	            }
	          } else {
	            _this2.hideTotalData();
	          }
	          //this.grid.tableUnfade();
	        });
	      }
	    }
	  }, {
	    key: "openInventoryManagementSlider",
	    value: function openInventoryManagementSlider() {
	      if (this.inventoryManagementLink) {
	        new catalog_storeEnableWizard.EnableWizardOpener().open(this.inventoryManagementLink, {
	          urlParams: {
	            analyticsContextSection: catalog_storeEnableWizard.AnalyticsContextList.PRODUCT_CARD
	          },
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

}((this.window = this.window || {}),BX,BX.Event,BX.Catalog.Store));
//# sourceMappingURL=script.js.map
