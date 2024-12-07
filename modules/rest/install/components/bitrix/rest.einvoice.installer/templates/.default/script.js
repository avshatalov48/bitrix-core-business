/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_loader,main_core,ui_popupcomponentsmaker,ui_buttons,main_core_events,rest_listener,rest_appForm) {
	'use strict';

	var _templateObject;
	var BasePage = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BasePage, _EventEmitter);
	  function BasePage() {
	    var _this;
	    babelHelpers.classCallCheck(this, BasePage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BasePage).call(this));
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller.Page');
	    return _this;
	  }
	  babelHelpers.createClass(BasePage, [{
	    key: "getContent",
	    value: function getContent() {
	      throw new Error('Must be implemented in a child class');
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bitrix-einvoice-installer-main-icon-wrapper\">\n\t\t\t\t<div class=\"bitrix-einvoice-installer-main-icon\"></div>\n\t\t\t</div>\n\t\t"])));
	    }
	  }], [{
	    key: "getType",
	    value: function getType() {
	      throw new Error('Must be implemented in a child class');
	    }
	  }]);
	  return BasePage;
	}(main_core_events.EventEmitter);

	var _templateObject$1;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _content = /*#__PURE__*/new WeakMap();
	var _button = /*#__PURE__*/new WeakMap();
	var _getButton = /*#__PURE__*/new WeakSet();
	var ErrorPage = /*#__PURE__*/function (_BasePage) {
	  babelHelpers.inherits(ErrorPage, _BasePage);
	  function ErrorPage() {
	    var _this;
	    babelHelpers.classCallCheck(this, ErrorPage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ErrorPage).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getButton);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _content, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _button, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller.ErrorPage');
	    return _this;
	  }
	  babelHelpers.createClass(ErrorPage, [{
	    key: "getContent",
	    value: function getContent() {
	      if (!babelHelpers.classPrivateFieldGet(this, _content)) {
	        babelHelpers.classPrivateFieldSet(this, _content, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-content --error-content\">\n\t\t\t\t\t<div class=\"bitrix-einvoice-installer-error-icon\"></div>\n\t\t\t\t\t<div class=\"bitrix-einvoice-installer-title-install\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_TITLE'), _classPrivateMethodGet(this, _getButton, _getButton2).call(this)));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _content);
	    }
	  }], [{
	    key: "getType",
	    value: function getType() {
	      return 'error';
	    }
	  }]);
	  return ErrorPage;
	}(BasePage);
	function _getButton2() {
	  var _this2 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _button)) {
	    babelHelpers.classPrivateFieldSet(this, _button, new ui_buttons.Button({
	      text: main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_ERROR_BUTTON'),
	      size: ui_buttons.Button.Size.LARGE,
	      color: ui_buttons.Button.Color.SUCCESS,
	      className: 'bitrix-einvoice-installer-button-try-again',
	      onclick: function onclick() {
	        _this2.emit('go-back');
	      }
	    }).getContainer());
	  }
	  return babelHelpers.classPrivateFieldGet(this, _button);
	}

	var _templateObject$2, _templateObject2;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _content$1 = /*#__PURE__*/new WeakMap();
	var _title = /*#__PURE__*/new WeakMap();
	var _loader = /*#__PURE__*/new WeakMap();
	var _onAfterUnsuccessfulInstallApplication = /*#__PURE__*/new WeakSet();
	var _getTitle = /*#__PURE__*/new WeakSet();
	var _getLoader = /*#__PURE__*/new WeakSet();
	var InstallPage = /*#__PURE__*/function (_BasePage) {
	  babelHelpers.inherits(InstallPage, _BasePage);
	  babelHelpers.createClass(InstallPage, null, [{
	    key: "getType",
	    value: function getType() {
	      return 'install';
	    }
	  }]);
	  function InstallPage() {
	    var _this;
	    babelHelpers.classCallCheck(this, InstallPage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(InstallPage).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getLoader);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _getTitle);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onAfterUnsuccessfulInstallApplication);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _content$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _title, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _loader, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller.InstallPage');
	    _this.subscribe('install-app', function (event) {
	      _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getLoader, _getLoader2).call(babelHelpers.assertThisInitialized(_this)).show();
	      var installer = event.data.source;
	      event.data.install.then(function (response) {
	        if (!response.status) {
	          return Promise.reject();
	        }
	        return rest_appForm.AppForm.buildByApp(response.data.id);
	      }).then(function (appForm) {
	        _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getLoader, _getLoader2).call(babelHelpers.assertThisInitialized(_this)).hide();
	        appForm.show();
	        installer.render('selection');
	      })["catch"](function () {
	        _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _getLoader, _getLoader2).call(babelHelpers.assertThisInitialized(_this)).hide();
	        _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _onAfterUnsuccessfulInstallApplication, _onAfterUnsuccessfulInstallApplication2).call(babelHelpers.assertThisInitialized(_this));
	      });
	    });
	    return _this;
	  }
	  babelHelpers.createClass(InstallPage, [{
	    key: "getContent",
	    value: function getContent() {
	      if (!babelHelpers.classPrivateFieldGet(this, _content$1)) {
	        babelHelpers.classPrivateFieldSet(this, _content$1, main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-content\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"bitrix-einvoice-installer-loader-wrapper-install\"/>\n\t\t\t\t</div>\n\t\t\t"])), _classPrivateMethodGet$1(this, _getTitle, _getTitle2).call(this)));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _content$1);
	    }
	  }]);
	  return InstallPage;
	}(BasePage);
	function _onAfterUnsuccessfulInstallApplication2() {
	  this.emit('install-error');
	}
	function _getTitle2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _title)) {
	    babelHelpers.classPrivateFieldSet(this, _title, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-title-install\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_INSTALL_TITLE')));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _title);
	}
	function _getLoader2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _loader)) {
	    babelHelpers.classPrivateFieldSet(this, _loader, new main_loader.Loader({
	      target: this.getContent().querySelector('.bitrix-einvoice-installer-loader-wrapper-install'),
	      size: 90,
	      color: '#2FC6F6',
	      mode: 'inline'
	    }));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _loader);
	}

	var _templateObject$3, _templateObject2$1;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _popup = /*#__PURE__*/new WeakMap();
	var _apps = /*#__PURE__*/new WeakMap();
	var _popupContent = /*#__PURE__*/new WeakMap();
	var _formConfiguration = /*#__PURE__*/new WeakMap();
	var _getPopup = /*#__PURE__*/new WeakSet();
	var _getPopupContent = /*#__PURE__*/new WeakSet();
	var _showFormForOffer = /*#__PURE__*/new WeakSet();
	var EInvoiceAppButton = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(EInvoiceAppButton, _EventEmitter);
	  function EInvoiceAppButton(apps, formConfiguration) {
	    var _this;
	    babelHelpers.classCallCheck(this, EInvoiceAppButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EInvoiceAppButton).call(this));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _showFormForOffer);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getPopupContent);
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _getPopup);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _apps, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _popupContent, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _formConfiguration, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Rest.EInvoiceAppButton');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _apps, apps);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formConfiguration, formConfiguration);
	    return _this;
	  }
	  babelHelpers.createClass(EInvoiceAppButton, [{
	    key: "getContent",
	    value: function getContent() {
	      var _this2 = this;
	      var button = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON'),
	        size: ui_buttons.Button.Size.LARGE,
	        color: ui_buttons.Button.Color.SUCCESS,
	        dropdown: true,
	        className: 'bitrix-einvoice-installer-button-select',
	        onclick: function onclick(event) {
	          var popup = _classPrivateMethodGet$2(_this2, _getPopup, _getPopup2).call(_this2, event.button);
	          popup.getPopup().setMaxWidth(event.button.offsetWidth);
	          popup.getPopup().toggle();
	        }
	      });
	      return button.getContainer();
	    }
	  }]);
	  return EInvoiceAppButton;
	}(main_core_events.EventEmitter);
	function _getPopup2(bindElement) {
	  var _this3 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	    return babelHelpers.classPrivateFieldGet(this, _popup);
	  }
	  babelHelpers.classPrivateFieldSet(this, _popup, new ui_popupcomponentsmaker.PopupComponentsMaker({
	    target: bindElement,
	    content: _classPrivateMethodGet$2(this, _getPopupContent, _getPopupContent2).call(this),
	    useAngle: false
	  }));
	  babelHelpers.classPrivateFieldGet(this, _popup).getPopup().setOffset({
	    offsetLeft: 0,
	    offsetTop: 0
	  });
	  this.subscribe('popup-close', function () {
	    babelHelpers.classPrivateFieldGet(_this3, _popup).close();
	  });
	  return babelHelpers.classPrivateFieldGet(this, _popup);
	}
	function _getPopupContent2() {
	  var _this4 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _popupContent)) {
	    return babelHelpers.classPrivateFieldGet(this, _popupContent);
	  }
	  babelHelpers.classPrivateFieldSet(this, _popupContent, []);
	  babelHelpers.classPrivateFieldGet(this, _apps).forEach(function (app) {
	    var onclick = function onclick() {
	      _this4.emit('popup-close');
	      _this4.emit('click-app', new main_core_events.BaseEvent({
	        data: {
	          code: app.code,
	          name: app.name
	        }
	      }));
	    };
	    babelHelpers.classPrivateFieldGet(_this4, _popupContent).push({
	      html: main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div onclick=\"", "\" class=\"bitrix-einvoice-installer-app-wrapper\">\n\t\t\t\t\t\t<div class=\"bitrix-einvoice-installer-app-name\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t"])), onclick, main_core.Text.encode(app.name))
	    });
	  });
	  var showForm = function showForm() {
	    _classPrivateMethodGet$2(_this4, _showFormForOffer, _showFormForOffer2).call(_this4);
	    _this4.emit('popup-close');
	  };
	  babelHelpers.classPrivateFieldGet(this, _popupContent).push({
	    html: main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div onclick=\"", "\" class=\"bitrix-einvoice-installer-app-wrapper --form\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), showForm, main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_BUTTON_OFFER'))
	  });
	  return babelHelpers.classPrivateFieldGet(this, _popupContent);
	}
	function _showFormForOffer2() {
	  BX.UI.Feedback.Form.open({
	    id: 'b5309667',
	    forms: [{
	      zones: ['es'],
	      id: 676,
	      lang: 'es',
	      sec: 'uthphh'
	    }, {
	      zones: ['de'],
	      id: 670,
	      lang: 'de',
	      sec: 'gk89kt'
	    }, {
	      zones: ['com.br'],
	      id: 668,
	      lang: 'br',
	      sec: 'kuelnm'
	    }],
	    defaultForm: {
	      id: 674,
	      lang: 'en',
	      sec: '5iorws'
	    },
	    presets: _objectSpread(_objectSpread({}, babelHelpers.classPrivateFieldGet(this, _formConfiguration)), {}, {
	      sender_page: document.location.href
	    })
	  });
	}

	var _templateObject$4, _templateObject2$2, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _content$2 = /*#__PURE__*/new WeakMap();
	var _title$1 = /*#__PURE__*/new WeakMap();
	var _description = /*#__PURE__*/new WeakMap();
	var _button$1 = /*#__PURE__*/new WeakMap();
	var _apps$1 = /*#__PURE__*/new WeakMap();
	var _formConfiguration$1 = /*#__PURE__*/new WeakMap();
	var _moreInformation = /*#__PURE__*/new WeakMap();
	var _getTitle$1 = /*#__PURE__*/new WeakSet();
	var _getDescription = /*#__PURE__*/new WeakSet();
	var _getButton$1 = /*#__PURE__*/new WeakSet();
	var _getMoreInformation = /*#__PURE__*/new WeakSet();
	var SelectionPage = /*#__PURE__*/function (_BasePage) {
	  babelHelpers.inherits(SelectionPage, _BasePage);
	  function SelectionPage(apps, formConfiguration) {
	    var _this;
	    babelHelpers.classCallCheck(this, SelectionPage);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectionPage).call(this));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _getMoreInformation);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _getButton$1);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _getDescription);
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _getTitle$1);
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _content$2, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _title$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _description, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _button$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _apps$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _formConfiguration$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _moreInformation, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _apps$1, apps);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formConfiguration$1, formConfiguration);
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller.SelectionPage');
	    return _this;
	  }
	  babelHelpers.createClass(SelectionPage, [{
	    key: "getContent",
	    value: function getContent() {
	      if (!babelHelpers.classPrivateFieldGet(this, _content$2)) {
	        babelHelpers.classPrivateFieldSet(this, _content$2, main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-content\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _classPrivateMethodGet$3(this, _getTitle$1, _getTitle2$1).call(this), _classPrivateMethodGet$3(this, _getDescription, _getDescription2).call(this), _classPrivateMethodGet$3(this, _getButton$1, _getButton2$1).call(this), _classPrivateMethodGet$3(this, _getMoreInformation, _getMoreInformation2).call(this)));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _content$2);
	    }
	  }], [{
	    key: "getType",
	    value: function getType() {
	      return 'selection';
	    }
	  }]);
	  return SelectionPage;
	}(BasePage);
	function _getTitle2$1() {
	  if (!babelHelpers.classPrivateFieldGet(this, _title$1)) {
	    babelHelpers.classPrivateFieldSet(this, _title$1, main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-title__wrapper\">\n\t\t\t\t\t<div class=\"bitrix-einvoice-installer-title__main-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_TITLE')));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _title$1);
	}
	function _getDescription2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _description)) {
	    babelHelpers.classPrivateFieldSet(this, _description, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"bitrix-einvoice-installer-description\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_SELECTION_DESCRIPTION')));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _description);
	}
	function _getButton2$1() {
	  var _this2 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _button$1)) {
	    return babelHelpers.classPrivateFieldGet(this, _button$1);
	  }
	  var buttonConstructor = new EInvoiceAppButton(babelHelpers.classPrivateFieldGet(this, _apps$1), babelHelpers.classPrivateFieldGet(this, _formConfiguration$1));
	  babelHelpers.classPrivateFieldSet(this, _button$1, buttonConstructor.getContent());
	  buttonConstructor.subscribe('click-app', function (event) {
	    _this2.emit('start-install-app', event);
	  });
	  return babelHelpers.classPrivateFieldGet(this, _button$1);
	}
	function _getMoreInformation2() {
	  if (babelHelpers.classPrivateFieldGet(this, _moreInformation)) {
	    return babelHelpers.classPrivateFieldGet(this, _moreInformation);
	  }
	  var onclick = function onclick() {
	    top.BX.Helper.show('redirect=detail&code=19312840');
	  };
	  babelHelpers.classPrivateFieldSet(this, _moreInformation, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"bitrix-einvoice-installer-more-information-wrapper\">\n\t\t\t\t<div onclick=\"", "\" class=\"bitrix-einvoice-installer-more-information-link\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), onclick, main_core.Loc.getMessage('REST_EINVOICE_INSTALLER_MORE')));
	  return babelHelpers.classPrivateFieldGet(this, _moreInformation);
	}

	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _selectionPage = /*#__PURE__*/new WeakMap();
	var _installPage = /*#__PURE__*/new WeakMap();
	var _errorPage = /*#__PURE__*/new WeakMap();
	var _options = /*#__PURE__*/new WeakMap();
	var _types = /*#__PURE__*/new WeakMap();
	var _getSelectionPage = /*#__PURE__*/new WeakSet();
	var _getInstallPage = /*#__PURE__*/new WeakSet();
	var _getErrorPage = /*#__PURE__*/new WeakSet();
	var _registerPageHandlers = /*#__PURE__*/new WeakSet();
	var PageProvider = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PageProvider, _EventEmitter);
	  function PageProvider(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, PageProvider);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PageProvider).call(this));
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _registerPageHandlers);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getErrorPage);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getInstallPage);
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _getSelectionPage);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _selectionPage, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _installPage, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _errorPage, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _options, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _types, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller.PageProvider');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _options, options);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _types, [SelectionPage.getType(), InstallPage.getType(), ErrorPage.getType()]);
	    return _this;
	  }
	  babelHelpers.createClass(PageProvider, [{
	    key: "exist",
	    value: function exist(type) {
	      return babelHelpers.classPrivateFieldGet(this, _types).includes(type);
	    }
	  }, {
	    key: "getPageByType",
	    value: function getPageByType(type) {
	      switch (type) {
	        case SelectionPage.getType():
	          return _classPrivateMethodGet$4(this, _getSelectionPage, _getSelectionPage2).call(this);
	        case InstallPage.getType():
	          return _classPrivateMethodGet$4(this, _getInstallPage, _getInstallPage2).call(this);
	        case ErrorPage.getType():
	          return _classPrivateMethodGet$4(this, _getErrorPage, _getErrorPage2).call(this);
	        default:
	          throw new Error('Incorrect page type');
	      }
	    }
	  }]);
	  return PageProvider;
	}(main_core_events.EventEmitter);
	function _getSelectionPage2() {
	  var _this2 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _selectionPage)) {
	    babelHelpers.classPrivateFieldSet(this, _selectionPage, new SelectionPage(babelHelpers.classPrivateFieldGet(this, _options).apps, babelHelpers.classPrivateFieldGet(this, _options).formConfiguration));
	    _classPrivateMethodGet$4(this, _registerPageHandlers, _registerPageHandlers2).call(this, babelHelpers.classPrivateFieldGet(this, _selectionPage));
	    babelHelpers.classPrivateFieldGet(this, _selectionPage).subscribe('start-install-app', function () {
	      _this2.emit('render', new main_core_events.BaseEvent({
	        data: {
	          type: InstallPage.getType()
	        }
	      }));
	    });
	  }
	  return babelHelpers.classPrivateFieldGet(this, _selectionPage);
	}
	function _getInstallPage2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _installPage)) {
	    babelHelpers.classPrivateFieldSet(this, _installPage, new InstallPage());
	    _classPrivateMethodGet$4(this, _registerPageHandlers, _registerPageHandlers2).call(this, babelHelpers.classPrivateFieldGet(this, _installPage));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _installPage);
	}
	function _getErrorPage2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _errorPage)) {
	    babelHelpers.classPrivateFieldSet(this, _errorPage, new ErrorPage());
	    _classPrivateMethodGet$4(this, _registerPageHandlers, _registerPageHandlers2).call(this, babelHelpers.classPrivateFieldGet(this, _errorPage));
	  }
	  return babelHelpers.classPrivateFieldGet(this, _errorPage);
	}
	function _registerPageHandlers2(page) {
	  var _this3 = this;
	  page.subscribe('go-back', function () {
	    _this3.emit('render', new main_core_events.BaseEvent({
	      data: {
	        type: SelectionPage.getType()
	      }
	    }));
	  });
	  page.subscribe('install-error', function () {
	    _this3.emit('render', new main_core_events.BaseEvent({
	      data: {
	        type: ErrorPage.getType()
	      }
	    }));
	  });
	}

	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _options$1 = /*#__PURE__*/new WeakMap();
	var _pageProvider = /*#__PURE__*/new WeakMap();
	var _formListener = /*#__PURE__*/new WeakMap();
	var _installApplicationByCode = /*#__PURE__*/new WeakSet();
	var _onStartInstall = /*#__PURE__*/new WeakSet();
	var _getPageProvider = /*#__PURE__*/new WeakSet();
	var EInvoiceInstaller = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(EInvoiceInstaller, _EventEmitter);
	  function EInvoiceInstaller(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, EInvoiceInstaller);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EInvoiceInstaller).call(this));
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _getPageProvider);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _onStartInstall);
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _installApplicationByCode);
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _options$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _pageProvider, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _formListener, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Rest.EInvoiceInstaller');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _options$1, options);
	    _this.render('selection');
	    _classPrivateMethodGet$5(babelHelpers.assertThisInitialized(_this), _getPageProvider, _getPageProvider2).call(babelHelpers.assertThisInitialized(_this)).getPageByType('selection').subscribe('start-install-app', _classPrivateMethodGet$5(babelHelpers.assertThisInitialized(_this), _onStartInstall, _onStartInstall2).bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(EInvoiceInstaller, [{
	    key: "render",
	    value: function render(pageType) {
	      if (_classPrivateMethodGet$5(this, _getPageProvider, _getPageProvider2).call(this).exist(pageType)) {
	        main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _options$1).wrapper);
	        var page = _classPrivateMethodGet$5(this, _getPageProvider, _getPageProvider2).call(this).getPageByType(pageType);
	        main_core.Dom.append(page.getContent(), babelHelpers.classPrivateFieldGet(this, _options$1).wrapper);
	        if (pageType !== 'error') {
	          main_core.Dom.append(page.getIcon(), babelHelpers.classPrivateFieldGet(this, _options$1).wrapper);
	        }
	      }
	    }
	  }]);
	  return EInvoiceInstaller;
	}(main_core_events.EventEmitter);
	function _installApplicationByCode2(code) {
	  return main_core.ajax.runComponentAction('bitrix:rest.einvoice.installer', 'installApplicationByCode', {
	    mode: 'class',
	    data: {
	      code: code
	    }
	  });
	}
	function _onStartInstall2(event) {
	  _classPrivateMethodGet$5(this, _getPageProvider, _getPageProvider2).call(this).getPageByType('install').emit('install-app', new main_core_events.BaseEvent({
	    data: {
	      source: this,
	      code: event.data.code,
	      name: event.data.name,
	      install: _classPrivateMethodGet$5(this, _installApplicationByCode, _installApplicationByCode2).call(this, event.data.code)
	    }
	  }));
	}
	function _getPageProvider2() {
	  var _this2 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _pageProvider)) {
	    babelHelpers.classPrivateFieldSet(this, _pageProvider, new PageProvider(babelHelpers.classPrivateFieldGet(this, _options$1)));
	    babelHelpers.classPrivateFieldGet(this, _pageProvider).subscribe('render', function (event) {
	      _this2.render(event.data.type);
	    });
	  }
	  return babelHelpers.classPrivateFieldGet(this, _pageProvider);
	}

	exports.EInvoiceInstaller = EInvoiceInstaller;

}((this.BX.Rest = this.BX.Rest || {}),BX,BX,BX.UI,BX.UI,BX.Event,BX.Rest,BX.Rest));
//# sourceMappingURL=script.js.map
