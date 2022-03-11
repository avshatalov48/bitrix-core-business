this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject;
	var Manual = /*#__PURE__*/function () {
	  function Manual(params) {
	    babelHelpers.classCallCheck(this, Manual);
	    this.manualCode = main_core.Type.isString(params.manualCode) ? params.manualCode : '';
	    this.width = main_core.Type.isNumber(params.width) ? params.width : 1000;
	    this.urlParams = main_core.Type.isPlainObject(params.urlParams) ? params.urlParams : {};
	    this.analyticsLabel = main_core.Type.isPlainObject(params.analyticsLabel) ? params.analyticsLabel : {};
	    this.sidePanelId = 'manual-side-panel-' + this.manualCode;
	  }

	  babelHelpers.createClass(Manual, [{
	    key: "open",
	    value: function open() {
	      var _this = this;

	      if (this.isOpen()) {
	        return;
	      }

	      BX.SidePanel.Instance.open(this.sidePanelId, {
	        contentCallback: function contentCallback() {
	          return _this.createFrame();
	        },
	        width: this.width
	      });
	    }
	  }, {
	    key: "createFrame",
	    value: function createFrame() {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        main_core.ajax.runAction('ui.manual.getInitParams', {
	          data: {
	            manualCode: _this2.manualCode,
	            urlParams: _this2.urlParams
	          },
	          analyticsLabel: _this2.analyticsLabel
	        }).then(function (response) {
	          resolve(_this2.renderFrame(response.data.url));
	        });
	      });
	    }
	  }, {
	    key: "renderFrame",
	    value: function renderFrame(url) {
	      var frameStyles = 'position: absolute; left: 0; top: 0; padding: 0;' + ' border: none; margin: 0; width: 100%; height: 100%;';
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<iframe style=\"", "\" src=\"", "\"></iframe>"])), frameStyles, url);
	    }
	  }, {
	    key: "getSidePanel",
	    value: function getSidePanel() {
	      return BX.SidePanel.Instance.getSlider(this.sidePanelId);
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.getSidePanel() && this.getSidePanel().isOpen();
	    }
	  }], [{
	    key: "show",
	    value: function show(manualCode) {
	      var urlParams = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var analyticsLabel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      var manual = new Manual({
	        manualCode: manualCode,
	        urlParams: urlParams,
	        analyticsLabel: analyticsLabel
	      });
	      manual.open();
	    }
	  }]);
	  return Manual;
	}();

	exports.Manual = Manual;

}((this.BX.UI.Manual = this.BX.UI.Manual || {}),BX));
//# sourceMappingURL=manual.bundle.js.map
