this.BX = this.BX || {};
(function (exports) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _url = /*#__PURE__*/new WeakMap();
	var _width = /*#__PURE__*/new WeakMap();
	var _options = /*#__PURE__*/new WeakMap();
	var AppForm = /*#__PURE__*/function () {
	  function AppForm(options) {
	    babelHelpers.classCallCheck(this, AppForm);
	    _classPrivateFieldInitSpec(this, _url, {
	      writable: true,
	      value: '/app/settings/'
	    });
	    _classPrivateFieldInitSpec(this, _width, {
	      writable: true,
	      value: 575
	    });
	    _classPrivateFieldInitSpec(this, _options, {
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
	        requestParams: babelHelpers.classPrivateFieldGet(this, _options)
	      });
	    }
	  }]);
	  return AppForm;
	}();

	exports.AppForm = AppForm;

}((this.BX.Rest = this.BX.Rest || {})));
//# sourceMappingURL=app-form.bundle.js.map
