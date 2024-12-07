/* eslint-disable */
(function (exports,main_core,catalog_storeEnableWizard) {
	'use strict';

	/* eslint-disable no-param-reassign */
	var namespace = main_core.Reflection.namespace('BX.Catalog.Store.Document');
	var ControlPanel = /*#__PURE__*/function () {
	  function ControlPanel() {
	    babelHelpers.classCallCheck(this, ControlPanel);
	  }
	  babelHelpers.createClass(ControlPanel, [{
	    key: "openSlider",
	    value: function openSlider(url) {
	      var _options$events, _options$data;
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var currentSlider = BX.SidePanel.Instance.getTopSlider();
	      options = main_core.Type.isPlainObject(options) ? options : {};
	      var params = {
	        urlParams: {
	          analyticsContextSection: catalog_storeEnableWizard.AnalyticsContextList.ANALYTICS_MENU_ITEM
	        },
	        events: (_options$events = options.events) !== null && _options$events !== void 0 ? _options$events : {},
	        data: (_options$data = options.data) !== null && _options$data !== void 0 ? _options$data : {}
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
	      return new catalog_storeEnableWizard.EnableWizardOpener().open(url, params);
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

}((this.window = this.window || {}),BX,BX.Catalog.Store));
//# sourceMappingURL=script.js.map
