this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {};
(function (exports) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _schemes = /*#__PURE__*/new WeakMap();
	var _defaultSchemes = /*#__PURE__*/new WeakMap();
	var SchemeManager = /*#__PURE__*/function () {
	  function SchemeManager(schemes) {
	    babelHelpers.classCallCheck(this, SchemeManager);
	    _classPrivateFieldInitSpec(this, _schemes, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _defaultSchemes, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _schemes, schemes);
	    babelHelpers.classPrivateFieldSet(this, _defaultSchemes, babelHelpers.classPrivateFieldGet(this, _schemes).filter(function (scheme) {
	      return !scheme.dynamic;
	    }));
	  }
	  babelHelpers.createClass(SchemeManager, [{
	    key: "isInvoice",
	    value: function isInvoice(schemeId) {
	      return this.findSchemeById(schemeId).hasInvoice;
	    }
	  }, {
	    key: "findSchemeById",
	    value: function findSchemeById(schemeId) {
	      return babelHelpers.classPrivateFieldGet(this, _schemes).find(function (scheme) {
	        return scheme.id === schemeId;
	      });
	    }
	  }, {
	    key: "getSpecularSchemeId",
	    value: function getSpecularSchemeId(schemeId) {
	      return this.findSchemeById(schemeId).specularId;
	    }
	  }, {
	    key: "isDefaultScheme",
	    value: function isDefaultScheme(schemeId) {
	      return babelHelpers.classPrivateFieldGet(this, _defaultSchemes).findIndex(function (scheme) {
	        return scheme.id === schemeId;
	      }) !== -1;
	    }
	  }]);
	  return SchemeManager;
	}();

	exports.SchemeManager = SchemeManager;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content.Crm = this.BX.Landing.Ui.Panel.Formsettingspanel.Content.Crm || {})));
//# sourceMappingURL=schememanager.bundle.js.map
