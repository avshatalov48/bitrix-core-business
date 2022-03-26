(function (exports,main_core) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Catalog.Store.Document');

	var ControlPanel = /*#__PURE__*/function () {
	  function ControlPanel() {
	    babelHelpers.classCallCheck(this, ControlPanel);
	  }

	  babelHelpers.createClass(ControlPanel, [{
	    key: "openSlider",
	    value: function openSlider(url, options) {
	      var currentSlider = BX.SidePanel.Instance.getTopSlider();

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
	            var slider = event.getSlider();

	            if (!slider) {
	              return;
	            }

	            if (slider.getData().get('isInventoryManagementEnabled') || slider.getData().get('isInventoryManagementDisabled')) {
	              if (currentSlider) {
	                currentSlider.data.set('preventMasterSlider', true);
	              }

	              document.location.reload();
	            }

	            resolve(event.getSlider());
	          };

	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
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

	namespace.ControlPanel = ControlPanel;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
