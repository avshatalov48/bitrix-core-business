/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var instance = null;
	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);
	    babelHelpers.defineProperty(this, "mode", {
	      variable: 'variable',
	      constant: 'constant'
	    });
	    babelHelpers.defineProperty(this, "listUrl", '/bitrix/components/bitrix/bizproc.globalfield.list/');
	    babelHelpers.defineProperty(this, "editUrl", '/bitrix/components/bitrix/bizproc.globalfield.edit/');
	    babelHelpers.defineProperty(this, "listSliderOptions", {
	      width: 1150,
	      cacheable: false,
	      allowChangeHistory: false
	    });
	    babelHelpers.defineProperty(this, "editSliderOptions", {
	      width: 500,
	      cacheable: false,
	      allowChangeHistory: false
	    });
	  }
	  babelHelpers.createClass(Manager, [{
	    key: "createGlobals",
	    value: function createGlobals(mode, documentType, name, additionContext) {
	      var customName = main_core.Type.isStringFilled(name) ? name : '';
	      var visibility = null;
	      var availableTypes = [];
	      if (main_core.Type.isPlainObject(additionContext)) {
	        visibility = main_core.Type.isStringFilled(additionContext.visibility) ? additionContext.visibility : null;
	        availableTypes = main_core.Type.isArrayFilled(additionContext.availableTypes) ? additionContext.availableTypes : [];
	      }
	      return this.constructor.openSlider(main_core.Uri.addParam(this.editUrl, {
	        documentType: documentType,
	        mode: this.mode[mode],
	        name: customName,
	        visibility: visibility,
	        availableTypes: availableTypes
	      }), this.editSliderOptions);
	    }
	  }, {
	    key: "editGlobals",
	    value: function editGlobals(id, mode, documentType) {
	      id = main_core.Type.isStringFilled(id) ? main_core.Text.decode(id) : '';
	      return this.constructor.openSlider(main_core.Uri.addParam(this.editUrl, {
	        fieldId: id,
	        mode: this.mode[mode],
	        documentType: documentType
	      }), this.editSliderOptions);
	    }
	  }, {
	    key: "showGlobals",
	    value: function showGlobals(mode, documentType) {
	      return this.constructor.openSlider(main_core.Uri.addParam(this.listUrl, {
	        documentType: documentType,
	        mode: this.mode[mode]
	      }), this.listSliderOptions);
	    }
	  }, {
	    key: "deleteGlobalsAction",
	    value: function deleteGlobalsAction(id, mode, documentType) {
	      return main_core.ajax.runAction('bizproc.globalfield.delete', {
	        analyticsLabel: 'bizprocGlobalFieldDelete',
	        data: {
	          fieldId: id,
	          mode: mode,
	          documentType: documentType
	        }
	      });
	    }
	  }, {
	    key: "upsertGlobalsAction",
	    value: function upsertGlobalsAction(id, property, documentType, mode) {
	      return main_core.ajax.runAction('bizproc.globalfield.upsert', {
	        analyticsLabel: 'bizprocGlobalFieldUpsert',
	        data: {
	          fieldId: id,
	          property: property,
	          documentType: documentType,
	          mode: mode
	        }
	      });
	    }
	  }], [{
	    key: "openSlider",
	    value: function openSlider(url, options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        options = {};
	      }
	      options = _objectSpread(_objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: true,
	        events: {}
	      }), options);
	      return new Promise(function (resolve) {
	        if (main_core.Type.isStringFilled(url)) {
	          options.events.onClose = function (event) {
	            resolve(event.getSlider());
	          };
	          BX.SidePanel.Instance.open(url, options);
	        } else {
	          resolve();
	        }
	      });
	    }
	  }, {
	    key: "Instance",
	    get: function get() {
	      if (instance === null) {
	        instance = new Manager();
	      }
	      return instance;
	    }
	  }]);
	  return Manager;
	}();

	var Globals = {
	  Manager: Manager
	};

	exports.Globals = Globals;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX));
//# sourceMappingURL=globals.bundle.js.map
