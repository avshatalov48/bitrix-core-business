this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

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
	      var customName = name !== null && name !== void 0 ? name : '';
	      var visibility = null;
	      var availableTypes = [];

	      if (additionContext !== undefined) {
	        var _additionContext$visi, _additionContext$avai;

	        visibility = (_additionContext$visi = additionContext['visibility']) !== null && _additionContext$visi !== void 0 ? _additionContext$visi : null;
	        availableTypes = (_additionContext$avai = additionContext['availableTypes']) !== null && _additionContext$avai !== void 0 ? _additionContext$avai : [];
	      }

	      return Manager.openSlider(main_core.Uri.addParam(this.editUrl, {
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
	      id = BX.util.htmlspecialcharsback(id);
	      return Manager.openSlider(main_core.Uri.addParam(this.editUrl, {
	        fieldId: id,
	        mode: mode,
	        documentType: documentType
	      }), this.editSliderOptions);
	    }
	  }, {
	    key: "showGlobals",
	    value: function showGlobals(mode, documentType) {
	      return Manager.openSlider(main_core.Uri.addParam(this.listUrl, {
	        documentType: documentType,
	        mode: mode
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

	      options = babelHelpers.objectSpread({}, {
	        cacheable: false,
	        allowChangeHistory: true,
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
