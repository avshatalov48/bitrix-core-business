(function (exports,main_core) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Catalog.Master');

	var CatalogWarehouseMasterClear = /*#__PURE__*/function () {
	  function CatalogWarehouseMasterClear() {
	    babelHelpers.classCallCheck(this, CatalogWarehouseMasterClear);
	  }

	  babelHelpers.createClass(CatalogWarehouseMasterClear, [{
	    key: "inventoryManagementEnabled",
	    value: function inventoryManagementEnabled() {
	      return main_core.ajax.runAction('catalog.config.inventoryManagementYAndResetQuantity', {});
	    }
	  }, {
	    key: "inventoryManagementDisabled",
	    value: function inventoryManagementDisabled() {
	      return main_core.ajax.runAction('catalog.config.inventoryManagementN', {});
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }

	      options = babelHelpers.objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: false,
	        events: {}
	      }, options);
	      return new Promise(function (resolve) {
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
	  }]);
	  return CatalogWarehouseMasterClear;
	}();

	namespace.CatalogWarehouseMasterClear = CatalogWarehouseMasterClear;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
