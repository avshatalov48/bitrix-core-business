/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_loader) {
	'use strict';

	class Provider {
	  fetch() {
	    return new Promise();
	  }
	}

	var _clientId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clientId");
	var _type = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("type");
	class ConfigProvider extends Provider {
	  constructor(clientId, eventType) {
	    super();
	    Object.defineProperty(this, _clientId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _type, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId] = clientId;
	    babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = eventType;
	  }
	  fetch() {
	    return main_core.ajax.runAction('rest.controller.appform.getConfig', {
	      data: {
	        clientId: babelHelpers.classPrivateFieldLooseBase(this, _clientId)[_clientId],
	        type: babelHelpers.classPrivateFieldLooseBase(this, _type)[_type]
	      }
	    });
	  }
	}

	const EventType = {
	  INSTALL: 'OnAppSettingsInstall',
	  CHANGE: 'OnAppSettingsChange',
	  DISPLAY: 'OnAppSettingsDisplay'
	};

	var _url = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("url");
	var _width = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("width");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	class AppForm {
	  constructor(options) {
	    Object.defineProperty(this, _url, {
	      writable: true,
	      value: '/marketplace/app/settings/'
	    });
	    Object.defineProperty(this, _width, {
	      writable: true,
	      value: 575
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	  }
	  show() {
	    top.BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldLooseBase(this, _url)[_url], {
	      width: babelHelpers.classPrivateFieldLooseBase(this, _width)[_width],
	      requestMethod: 'post',
	      requestParams: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options],
	      allowChangeHistory: false
	    });
	  }
	  static sliderLoader() {
	    top.BX.SidePanel.Instance.open('rest:app-form.loader', {
	      width: 575,
	      contentCallback: slider => {
	        const loader = new main_loader.Loader({
	          target: slider.getFrameWindow()
	        });
	        return loader.show();
	      },
	      requestMethod: 'post',
	      allowChangeHistory: false
	    });
	  }
	  static buildByApp(clientId, eventType) {
	    const provider = new ConfigProvider(clientId, eventType);
	    return provider.fetch().then(response => {
	      return new AppForm(response.data);
	    });
	  }
	  static buildByAppWithLoader(clientId, eventType) {
	    const provider = new ConfigProvider(clientId, eventType);
	    AppForm.sliderLoader();
	    return provider.fetch().then(response => {
	      top.BX.SidePanel.Instance.close(true);
	      top.BX.SidePanel.Instance.destroy('loader');
	      return new AppForm(response.data);
	    });
	  }
	}

	exports.AppForm = AppForm;
	exports.EventType = EventType;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX));
//# sourceMappingURL=app-form.bundle.js.map
