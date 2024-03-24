/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_loader,main_core_events,rest_formConstructor) {
	'use strict';

	var _templateObject;
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _formConstructor = /*#__PURE__*/new WeakMap();
	var _handler = /*#__PURE__*/new WeakMap();
	var _redirect = /*#__PURE__*/new WeakMap();
	var _clientId = /*#__PURE__*/new WeakMap();
	var _wrapper = /*#__PURE__*/new WeakMap();
	var _loader = /*#__PURE__*/new WeakMap();
	var _overlay = /*#__PURE__*/new WeakMap();
	var AppSettings = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(AppSettings, _EventEmitter);
	  function AppSettings(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, AppSettings);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AppSettings).call(this));
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _formConstructor, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _handler, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _redirect, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _clientId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _wrapper, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _loader, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _overlay, {
	      writable: true,
	      value: void 0
	    });
	    if (!(options.formConstructor instanceof rest_formConstructor.FormConstructor)) {
	      throw new Error('"formConstructor" is required parameters');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _redirect, null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _wrapper, main_core.Type.isElementNode(options.wrapper) ? options.wrapper : null);
	    _this.setFormConstructor(options.formConstructor);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _handler, main_core.Type.isStringFilled(options.handler) ? options.handler : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _clientId, main_core.Type.isStringFilled(options.clientId) ? options.clientId : null);
	    _this.setRedirect(options.redirect);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _loader, new main_loader.Loader({
	      target: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _wrapper)
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _overlay, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"rest-app-settings-overlay\"></div>"]))));
	    return _this;
	  }
	  babelHelpers.createClass(AppSettings, [{
	    key: "setRedirect",
	    value: function setRedirect(url) {
	      var reqExp = new RegExp('^(?:/|https?://' + location.host + ')', "g");
	      if (main_core.Type.isStringFilled(url) && !!url.match(reqExp)) {
	        babelHelpers.classPrivateFieldSet(this, _redirect, url);
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _wrapper))) {
	        throw new Error('Property "wrapper" is undefined');
	      }
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).renderTo(babelHelpers.classPrivateFieldGet(this, _wrapper));
	    }
	  }, {
	    key: "subscribeEvents",
	    value: function subscribeEvents() {
	      var _this2 = this;
	      if (!(babelHelpers.classPrivateFieldGet(this, _formConstructor) instanceof rest_formConstructor.FormConstructor)) {
	        return;
	      }
	      main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'button-click', function (event) {
	        var _event$data = babelHelpers.slicedToArray(event.data, 1),
	          clickedBtn = _event$data[0];
	        if (clickedBtn.TYPE === 'save') {
	          var data = {
	            clientId: babelHelpers.classPrivateFieldGet(_this2, _clientId),
	            settings: babelHelpers.classPrivateFieldGet(_this2, _formConstructor).getFormData(),
	            handler: babelHelpers.classPrivateFieldGet(_this2, _handler)
	          };
	          _this2.save(data);
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).subscribe('onReadySave', function () {
	        if (_this2.isReadySave()) {
	          BX.UI.ButtonPanel.show();
	        } else {
	          BX.UI.ButtonPanel.hide();
	        }
	      });
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).subscribe('onUnreadySave', function () {
	        BX.UI.ButtonPanel.hide();
	      });
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).subscribe('onFieldChange', function () {
	        _this2.reload();
	      });
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {
	      if (!(babelHelpers.classPrivateFieldGet(this, _formConstructor) instanceof rest_formConstructor.FormConstructor)) {
	        return;
	      }
	      main_core_events.EventEmitter.unsubscribeAll(main_core_events.EventEmitter.GLOBAL_TARGET, 'button-click');
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).unsubscribeAll('onSave');
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).unsubscribeAll('onReadySave');
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).unsubscribeAll('onUnreadySave');
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).unsubscribeAll('onFieldChange');
	    }
	  }, {
	    key: "setFormConstructor",
	    value: function setFormConstructor(formConstructor) {
	      this.unsubscribeEvents();
	      babelHelpers.classPrivateFieldSet(this, _formConstructor, formConstructor);
	      this.subscribeEvents();
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      var _this3 = this;
	      main_core.Dom.append(babelHelpers.classPrivateFieldGet(this, _overlay), babelHelpers.classPrivateFieldGet(this, _wrapper));
	      babelHelpers.classPrivateFieldGet(this, _loader).show();
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _clientId))) {
	        console.log('Property "clientId" is undefined');
	        return;
	      }
	      main_core.ajax.runComponentAction('bitrix:rest.app.settings', 'reload', {
	        mode: 'class',
	        data: {
	          clientId: babelHelpers.classPrivateFieldGet(this, _clientId),
	          settings: babelHelpers.classPrivateFieldGet(this, _formConstructor).getFormData()
	        }
	      }).then(function (response) {
	        var data = response.data;
	        _this3.setFormConstructor(new rest_formConstructor.FormConstructor({
	          steps: data.STEPS
	        }));
	        babelHelpers.classPrivateFieldSet(_this3, _handler, main_core.Type.isStringFilled(data.HANDLER) ? data.HANDLER : babelHelpers.classPrivateFieldGet(_this3, _handler));
	        babelHelpers.classPrivateFieldSet(_this3, _clientId, main_core.Type.isStringFilled(data.CLIENT_ID) ? data.CLIENT_ID : babelHelpers.classPrivateFieldGet(_this3, _clientId));
	        _this3.setRedirect(data.REDIRECT);
	        _this3.show();
	        babelHelpers.classPrivateFieldGet(_this3, _loader).hide();
	        main_core.Dom.remove(babelHelpers.classPrivateFieldGet(_this3, _overlay));
	      })["catch"](function (response) {
	        console.log(response.errors);
	        babelHelpers.classPrivateFieldGet(_this3, _formConstructor).showTextInBalloon(main_core.Loc.getMessage('REST_APP_SETTINGS_ERROR'));
	      });
	    }
	  }, {
	    key: "isReadySave",
	    value: function isReadySave() {
	      var isAllFieldReady = true;
	      babelHelpers.classPrivateFieldGet(this, _formConstructor).getFields().forEach(function (field) {
	        if (!field.isReadySave()) {
	          isAllFieldReady = false;
	        }
	      });
	      return isAllFieldReady;
	    }
	  }, {
	    key: "save",
	    value: function save(data) {
	      var _this4 = this;
	      main_core.ajax.runAction('rest.einvoice.save', {
	        mode: 'class',
	        data: data
	      }).then(function () {
	        if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(_this4, _redirect))) {
	          top.BX.SidePanel.Instance.close();
	        } else {
	          top.document.location.href = babelHelpers.classPrivateFieldGet(_this4, _redirect);
	        }
	        var buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');
	        main_core.Dom.removeClass(buttonWaitState, 'ui-btn-wait');
	      })["catch"](function (response) {
	        var errors = response.errors;
	        var _AppSettings$formatEr = AppSettings.formatErrors(errors),
	          fieldErrors = _AppSettings$formatEr.fieldErrors,
	          otherErrors = _AppSettings$formatEr.otherErrors;
	        babelHelpers.classPrivateFieldGet(_this4, _formConstructor).showFieldErrors(fieldErrors);
	        if (main_core.Type.isArrayFilled(otherErrors)) {
	          babelHelpers.classPrivateFieldGet(_this4, _formConstructor).showTextInBalloon(main_core.Loc.getMessage('REST_APP_SETTINGS_ERROR'));
	        }
	        var buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');
	        if (buttonWaitState) {
	          main_core.Dom.removeClass(buttonWaitState, 'ui-btn-wait');
	        }
	        BX.UI.ButtonPanel.hide();
	      });
	    }
	  }], [{
	    key: "formatErrors",
	    value: function formatErrors(errors) {
	      var fieldErrors = {};
	      var otherErrors = [];
	      errors.forEach(function (error) {
	        var _error$customData;
	        if (main_core.Type.isStringFilled((_error$customData = error.customData) === null || _error$customData === void 0 ? void 0 : _error$customData.fieldName)) {
	          var _error$customData2, _error$customData3, _error$customData4;
	          Array.isArray(fieldErrors[(_error$customData2 = error.customData) === null || _error$customData2 === void 0 ? void 0 : _error$customData2.fieldName]) ? fieldErrors[(_error$customData3 = error.customData) === null || _error$customData3 === void 0 ? void 0 : _error$customData3.fieldName].push(error.message) : fieldErrors[(_error$customData4 = error.customData) === null || _error$customData4 === void 0 ? void 0 : _error$customData4.fieldName] = [error.message];
	        } else {
	          otherErrors.push(error.message);
	        }
	      });
	      return {
	        fieldErrors: fieldErrors,
	        otherErrors: otherErrors
	      };
	    }
	  }]);
	  return AppSettings;
	}(main_core_events.EventEmitter);

	exports.AppSettings = AppSettings;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX,BX.Event,BX.Rest));
//# sourceMappingURL=script.js.map
