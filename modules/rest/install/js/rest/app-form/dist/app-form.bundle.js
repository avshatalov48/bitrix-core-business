/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_loader) {
	'use strict';

	var Provider = /*#__PURE__*/function () {
	  function Provider() {
	    babelHelpers.classCallCheck(this, Provider);
	  }
	  babelHelpers.createClass(Provider, [{
	    key: "fetch",
	    value: function fetch() {
	      return new Promise();
	    }
	  }]);
	  return Provider;
	}();

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _clientId = /*#__PURE__*/new WeakMap();
	var _type = /*#__PURE__*/new WeakMap();
	var ConfigProvider = /*#__PURE__*/function (_Provider) {
	  babelHelpers.inherits(ConfigProvider, _Provider);
	  function ConfigProvider(clientId, eventType) {
	    var _this;
	    babelHelpers.classCallCheck(this, ConfigProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfigProvider).call(this));
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _clientId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _type, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _clientId, clientId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _type, eventType);
	    return _this;
	  }
	  babelHelpers.createClass(ConfigProvider, [{
	    key: "fetch",
	    value: function fetch() {
	      return main_core.ajax.runAction('rest.controller.appform.getConfig', {
	        data: {
	          clientId: babelHelpers.classPrivateFieldGet(this, _clientId),
	          type: babelHelpers.classPrivateFieldGet(this, _type)
	        }
	      });
	    }
	  }]);
	  return ConfigProvider;
	}(Provider);

	var EventType = {
	  INSTALL: 'OnAppSettingsInstall',
	  CHANGE: 'OnAppSettingsChange',
	  DISPLAY: 'OnAppSettingsDisplay'
	};

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _url = /*#__PURE__*/new WeakMap();
	var _width = /*#__PURE__*/new WeakMap();
	var _options = /*#__PURE__*/new WeakMap();
	var AppForm = /*#__PURE__*/function () {
	  function AppForm(options) {
	    babelHelpers.classCallCheck(this, AppForm);
	    _classPrivateFieldInitSpec$1(this, _url, {
	      writable: true,
	      value: '/marketplace/app/settings/'
	    });
	    _classPrivateFieldInitSpec$1(this, _width, {
	      writable: true,
	      value: 575
	    });
	    _classPrivateFieldInitSpec$1(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _options, options);
	  }
	  babelHelpers.createClass(AppForm, [{
	    key: "show",
	    value: function show() {
	      top.BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldGet(this, _url), {
	        width: babelHelpers.classPrivateFieldGet(this, _width),
	        requestMethod: 'post',
	        requestParams: babelHelpers.classPrivateFieldGet(this, _options),
	        allowChangeHistory: false
	      });
	    }
	  }], [{
	    key: "sliderLoader",
	    value: function sliderLoader() {
	      top.BX.SidePanel.Instance.open('rest:app-form.loader', {
	        width: 575,
	        contentCallback: function contentCallback(slider) {
	          var loader = new main_loader.Loader({
	            target: slider.getFrameWindow()
	          });
	          return loader.show();
	        },
	        requestMethod: 'post',
	        allowChangeHistory: false
	      });
	    }
	  }, {
	    key: "buildByApp",
	    value: function buildByApp(clientId, eventType) {
	      var provider = new ConfigProvider(clientId, eventType);
	      return provider.fetch().then(function (response) {
	        return new AppForm(response.data);
	      });
	    }
	  }, {
	    key: "buildByAppWithLoader",
	    value: function buildByAppWithLoader(clientId, eventType) {
	      var provider = new ConfigProvider(clientId, eventType);
	      AppForm.sliderLoader();
	      return provider.fetch().then(function (response) {
	        top.BX.SidePanel.Instance.close(true);
	        top.BX.SidePanel.Instance.destroy('loader');
	        return new AppForm(response.data);
	      });
	    }
	  }]);
	  return AppForm;
	}();

	exports.AppForm = AppForm;
	exports.EventType = EventType;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX));
//# sourceMappingURL=app-form.bundle.js.map
