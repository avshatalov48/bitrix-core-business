/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,main_core) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _featureId = /*#__PURE__*/new WeakMap();
	var _code = /*#__PURE__*/new WeakMap();
	var _bindElement = /*#__PURE__*/new WeakMap();
	var _limitAnalyticsLabels = /*#__PURE__*/new WeakMap();
	var Limit = /*#__PURE__*/function () {
	  function Limit(params) {
	    babelHelpers.classCallCheck(this, Limit);
	    _classPrivateFieldInitSpec(this, _featureId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _code, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _limitAnalyticsLabels, {
	      writable: true,
	      value: {}
	    });
	    babelHelpers.classPrivateFieldSet(this, _featureId, params.featureId);
	    babelHelpers.classPrivateFieldSet(this, _code, main_core.Type.isStringFilled(params.code) ? params.code : "limit_".concat(babelHelpers.classPrivateFieldGet(this, _featureId)));
	    babelHelpers.classPrivateFieldSet(this, _bindElement, main_core.Type.isElementNode(params.bindElement) ? params.bindElement : null);
	    if (main_core.Type.isPlainObject(params.limitAnalyticsLabels)) {
	      babelHelpers.classPrivateFieldSet(this, _limitAnalyticsLabels, _objectSpread({
	        module: 'socialnetwork'
	      }, params.limitAnalyticsLabels));
	    }
	  }
	  babelHelpers.createClass(Limit, [{
	    key: "show",
	    value: function show() {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        main_core.Runtime.loadExtension('ui.info-helper').then(function (_ref) {
	          var FeaturePromotersRegistry = _ref.FeaturePromotersRegistry;
	          if (FeaturePromotersRegistry) {
	            FeaturePromotersRegistry.getPromoter({
	              featureId: babelHelpers.classPrivateFieldGet(_this, _featureId),
	              code: babelHelpers.classPrivateFieldGet(_this, _code),
	              bindElement: babelHelpers.classPrivateFieldGet(_this, _bindElement)
	            }).show();
	          } else {
	            BX.UI.InfoHelper.show(babelHelpers.classPrivateFieldGet(_this, _code), {
	              isLimit: true,
	              limitAnalyticsLabels: babelHelpers.classPrivateFieldGet(_this, _limitAnalyticsLabels)
	            });
	          }
	          resolve();
	        })["catch"](function (error) {
	          reject(error);
	        });
	      });
	    }
	  }], [{
	    key: "showInstance",
	    value: function showInstance(params) {
	      return this.getInstance(params).show();
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance(params) {
	      var _this$instances, _params$featureId, _this$instances$_para;
	      if (!main_core.Type.isStringFilled(params.featureId)) {
	        throw new Error('BX.Socialnetwork.Limit: featureId is required');
	      }
	      (_this$instances$_para = (_this$instances = this.instances)[_params$featureId = params.featureId]) !== null && _this$instances$_para !== void 0 ? _this$instances$_para : _this$instances[_params$featureId] = new this(params);
	      return this.instances[params.featureId];
	    }
	  }]);
	  return Limit;
	}();
	babelHelpers.defineProperty(Limit, "instances", {});

	exports.Limit = Limit;

}((this.BX.Socialnetwork.Limit = this.BX.Socialnetwork.Limit || {}),BX));
//# sourceMappingURL=limit.bundle.js.map
