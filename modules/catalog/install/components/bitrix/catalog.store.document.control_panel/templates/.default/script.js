(function (exports,main_core,catalog_storeUse) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Catalog.Store.Document');

	var ControlPanel = /*#__PURE__*/function () {
	  function ControlPanel() {
	    babelHelpers.classCallCheck(this, ControlPanel);
	  }

	  babelHelpers.createClass(ControlPanel, [{
	    key: "openSlider",
	    value: function openSlider(url) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var currentSlider = BX.SidePanel.Instance.getTopSlider();
	      options = main_core.Type.isPlainObject(options) ? options : {};
	      var params = {
	        events: options.hasOwnProperty("events") ? options.events : {},
	        data: options.hasOwnProperty("data") ? options.data : {}
	      };

	      params.events.onClose = function (event) {
	        var slider = event.getSlider();

	        if (!slider) {
	          return;
	        }

	        if (slider.getData().get('isInventoryManagementEnabled')) {
	          if (currentSlider) {
	            currentSlider.data.set('preventMasterSlider', true);
	          }

	          document.location.reload();
	        }
	      };

	      return new catalog_storeUse.Slider().open(url, params);
	    }
	  }, {
	    key: "storeMasterOpenSlider",
	    value: function storeMasterOpenSlider(url) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.openSlider(url, options);
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      document.location.reload();
	    }
	  }]);
	  return ControlPanel;
	}();

	if (window === window.top && BX.SidePanel.Instance) {
	  BX.SidePanel.Instance.bindAnchors({
	    rules: [{
	      condition: ['/crm/configs/catalog/'],
	      options: {
	        width: 1000,
	        allowChangeHistory: false,
	        cacheable: false
	      }
	    }]
	  });
	}

	namespace.ControlPanel = ControlPanel;

}((this.window = this.window || {}),BX,BX.Catalog.StoreUse));
//# sourceMappingURL=script.js.map
