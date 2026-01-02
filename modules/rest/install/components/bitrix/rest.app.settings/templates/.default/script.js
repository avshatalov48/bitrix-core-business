/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_loader,main_core_events,rest_formConstructor) {
	'use strict';

	let _ = t => t,
	  _t;
	var _formConstructor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formConstructor");
	var _handler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handler");
	var _redirect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("redirect");
	var _clientId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientId");
	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _overlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("overlay");
	class AppSettings extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _formConstructor, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _handler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _redirect, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _clientId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _overlay, {
	      writable: true,
	      value: void 0
	    });
	    if (!(options.formConstructor instanceof rest_formConstructor.FormConstructor)) {
	      throw new Error('"formConstructor" is required parameters');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _redirect)[_redirect] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = main_core.Type.isElementNode(options.wrapper) ? options.wrapper : null;
	    this.setFormConstructor(options.formConstructor);
	    babelHelpers.classPrivateFieldLooseBase(this, _handler)[_handler] = main_core.Type.isStringFilled(options.handler) ? options.handler : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId] = main_core.Type.isStringFilled(options.clientId) ? options.clientId : null;
	    this.setRedirect(options.redirect);
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	      target: babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _overlay)[_overlay] = main_core.Tag.render(_t || (_t = _`<div class="rest-app-settings-overlay"></div>`));
	  }
	  setRedirect(url) {
	    const reqExp = new RegExp('^(?:/|https?://' + location.host + ')', "g");
	    if (main_core.Type.isStringFilled(url) && !!url.match(reqExp)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _redirect)[_redirect] = url;
	    }
	  }
	  show() {
	    if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper])) {
	      throw new Error('Property "wrapper" is undefined');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    BX.UI.ButtonPanel.show();
	  }
	  subscribeEvents() {
	    if (!(babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor] instanceof rest_formConstructor.FormConstructor)) {
	      return;
	    }
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'button-click', event => {
	      const [clickedBtn] = event.data;
	      if (clickedBtn.TYPE === 'save') {
	        const data = {
	          clientId: babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId],
	          settings: babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].getFormData(),
	          handler: babelHelpers.classPrivateFieldLooseBase(this, _handler)[_handler]
	        };
	        this.save(data);
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].subscribe('onFieldChange', () => {
	      this.reload();
	    });
	  }
	  unsubscribeEvents() {
	    if (!(babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor] instanceof rest_formConstructor.FormConstructor)) {
	      return;
	    }
	    main_core_events.EventEmitter.unsubscribeAll(main_core_events.EventEmitter.GLOBAL_TARGET, 'button-click');
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].unsubscribeAll('onSave');
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].unsubscribeAll('onFieldChange');
	  }
	  setFormConstructor(formConstructor) {
	    this.unsubscribeEvents();
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor] = formConstructor;
	    this.subscribeEvents();
	  }
	  reload() {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _overlay)[_overlay], babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].show();
	    if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId])) {
	      console.log('Property "clientId" is undefined');
	      return;
	    }
	    main_core.ajax.runComponentAction('bitrix:rest.app.settings', 'reload', {
	      mode: 'class',
	      data: {
	        clientId: babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId],
	        settings: babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].getFormData()
	      }
	    }).then(response => {
	      const data = response.data;
	      this.setFormConstructor(new rest_formConstructor.FormConstructor({
	        steps: data.STEPS
	      }));
	      babelHelpers.classPrivateFieldLooseBase(this, _handler)[_handler] = main_core.Type.isStringFilled(data.HANDLER) ? data.HANDLER : babelHelpers.classPrivateFieldLooseBase(this, _handler)[_handler];
	      babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId] = main_core.Type.isStringFilled(data.CLIENT_ID) ? data.CLIENT_ID : babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId];
	      this.setRedirect(data.REDIRECT);
	      this.show();
	      babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader].hide();
	      main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _overlay)[_overlay]);
	    }).catch(response => {
	      console.log(response.errors);
	      babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].showTextInBalloon(main_core.Loc.getMessage('REST_APP_SETTINGS_ERROR'));
	    });
	  }
	  isReadySave() {
	    let isAllFieldReady = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].getFields().forEach(field => {
	      if (!field.isReadySave()) {
	        isAllFieldReady = false;
	      }
	    });
	    return isAllFieldReady;
	  }
	  save(data) {
	    main_core.ajax.runAction('rest.einvoice.save', {
	      mode: 'class',
	      data: data
	    }).then(() => {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _redirect)[_redirect])) {
	        top.BX.SidePanel.Instance.close();
	      } else {
	        top.document.location.href = babelHelpers.classPrivateFieldLooseBase(this, _redirect)[_redirect];
	      }
	      const buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');
	      main_core.Dom.removeClass(buttonWaitState, 'ui-btn-wait');
	    }).catch(response => {
	      const errors = response.errors;
	      let {
	        fieldErrors,
	        otherErrors
	      } = AppSettings.formatErrors(errors);
	      babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].showFieldErrors(fieldErrors);
	      if (main_core.Type.isArrayFilled(otherErrors)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _formConstructor)[_formConstructor].showTextInBalloon(main_core.Loc.getMessage('REST_APP_SETTINGS_ERROR'));
	      }
	      const buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');
	      if (buttonWaitState) {
	        main_core.Dom.removeClass(buttonWaitState, 'ui-btn-wait');
	      }
	    });
	  }
	  static formatErrors(errors) {
	    let fieldErrors = {};
	    let otherErrors = [];
	    errors.forEach(error => {
	      var _error$customData;
	      if (main_core.Type.isStringFilled((_error$customData = error.customData) == null ? void 0 : _error$customData.fieldName)) {
	        var _error$customData2, _error$customData3, _error$customData4;
	        Array.isArray(fieldErrors[(_error$customData2 = error.customData) == null ? void 0 : _error$customData2.fieldName]) ? fieldErrors[(_error$customData3 = error.customData) == null ? void 0 : _error$customData3.fieldName].push(error.message) : fieldErrors[(_error$customData4 = error.customData) == null ? void 0 : _error$customData4.fieldName] = [error.message];
	      } else {
	        otherErrors.push(error.message);
	      }
	    });
	    return {
	      fieldErrors: fieldErrors,
	      otherErrors: otherErrors
	    };
	  }
	}

	exports.AppSettings = AppSettings;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX,BX.Event,BX.Rest));
//# sourceMappingURL=script.js.map
