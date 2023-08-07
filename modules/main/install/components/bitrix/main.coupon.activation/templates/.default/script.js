this.BX = this.BX || {};
(function (exports,ui_buttons,main_popup,ui_alerts,main_loader,main_core_events,main_core) {
	'use strict';

	var BaseContent = /*#__PURE__*/function () {
	  function BaseContent() {
	    babelHelpers.classCallCheck(this, BaseContent);
	  }
	  babelHelpers.createClass(BaseContent, [{
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render('<div></div>');
	    }
	  }, {
	    key: "getButtonCollection",
	    value: function getButtonCollection() {
	      return [];
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {}
	  }]);
	  return BaseContent;
	}();

	var _templateObject, _templateObject2;
	var Loading = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(Loading, _BaseContent);
	  function Loading() {
	    babelHelpers.classCallCheck(this, Loading);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Loading).apply(this, arguments));
	  }
	  babelHelpers.createClass(Loading, [{
	    key: "getContent",
	    value: function getContent() {
	      var loaderNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"license-intranet-popup__loader\"></div>\n\t\t"])));
	      var primaryColor = getComputedStyle(document.body).getPropertyValue('--ui-color-primary');
	      var loader = new main_loader.Loader({
	        target: loaderNode,
	        size: 133,
	        color: primaryColor || '#2fc6f6'
	      });
	      loader.show();
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"license-intranet-popup__content --loader\">\n\t\t\t\t", "\n\t\t\t\t<div class=\"license-intranet-popup__loader-title\">", "</div>\n\t\t\t</div>\n\t\t"])), loaderNode, main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_PLEASE_WAIT'));
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      popup.setButtons(this.getButtonCollection());
	    }
	  }]);
	  return Loading;
	}(BaseContent);

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _componentName = /*#__PURE__*/new WeakMap();
	var _action = /*#__PURE__*/new WeakMap();
	var _method = /*#__PURE__*/new WeakMap();
	var _mode = /*#__PURE__*/new WeakMap();
	var Request = /*#__PURE__*/function () {
	  function Request(action) {
	    var method = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'POST';
	    var mode = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'class';
	    babelHelpers.classCallCheck(this, Request);
	    _classPrivateFieldInitSpec(this, _componentName, {
	      writable: true,
	      value: 'bitrix:main.coupon.activation'
	    });
	    _classPrivateFieldInitSpec(this, _action, {
	      writable: true,
	      value: 'activate'
	    });
	    _classPrivateFieldInitSpec(this, _method, {
	      writable: true,
	      value: 'POST'
	    });
	    _classPrivateFieldInitSpec(this, _mode, {
	      writable: true,
	      value: 'class'
	    });
	    babelHelpers.classPrivateFieldSet(this, _action, action);
	    babelHelpers.classPrivateFieldSet(this, _method, method);
	    babelHelpers.classPrivateFieldSet(this, _mode, mode);
	  }
	  babelHelpers.createClass(Request, [{
	    key: "send",
	    value: function send() {
	      var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return main_core.ajax.runComponentAction(babelHelpers.classPrivateFieldGet(this, _componentName), babelHelpers.classPrivateFieldGet(this, _action), {
	        mode: babelHelpers.classPrivateFieldGet(this, _mode),
	        data: data,
	        method: babelHelpers.classPrivateFieldGet(this, _method)
	      });
	    }
	  }]);
	  return Request;
	}();

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _errors = /*#__PURE__*/new WeakMap();
	var _balloon = /*#__PURE__*/new WeakMap();
	var ErrorCollection = /*#__PURE__*/function () {
	  function ErrorCollection() {
	    var errors = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	    babelHelpers.classCallCheck(this, ErrorCollection);
	    _classPrivateFieldInitSpec$1(this, _errors, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$1(this, _balloon, {
	      writable: true,
	      value: void 0
	    });
	    this.addErrors(errors);
	  }
	  babelHelpers.createClass(ErrorCollection, [{
	    key: "addErrors",
	    value: function addErrors(errors) {
	      babelHelpers.classPrivateFieldSet(this, _errors, [].concat(babelHelpers.toConsumableArray(babelHelpers.classPrivateFieldGet(this, _errors)), babelHelpers.toConsumableArray(errors)));
	    }
	  }, {
	    key: "cleanErrors",
	    value: function cleanErrors() {
	      babelHelpers.classPrivateFieldSet(this, _errors, []);
	    }
	  }, {
	    key: "hideErrors",
	    value: function hideErrors() {
	      if (!main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _balloon))) {
	        babelHelpers.classPrivateFieldGet(this, _balloon).activateAutoHide();
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (babelHelpers.classPrivateFieldGet(this, _errors).length <= 0) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _balloon, BX.UI.Notification.Center.notify({
	        content: ["<strong>".concat(main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_ERROR'), "</strong><br>"), babelHelpers.classPrivateFieldGet(this, _errors).map(function (value) {
	          return value.message;
	        }).join('</br>')].join(''),
	        position: 'top-right',
	        category: 'menu-self-item-popup',
	        autoHideDelay: 300000
	      }));
	    }
	  }]);
	  return ErrorCollection;
	}();

	var _templateObject$1;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _formData = /*#__PURE__*/new WeakMap();
	var _errors$1 = /*#__PURE__*/new WeakMap();
	var _supportLink = /*#__PURE__*/new WeakMap();
	var _docLink = /*#__PURE__*/new WeakMap();
	var _checkRequest = /*#__PURE__*/new WeakSet();
	var Activate = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(Activate, _BaseContent);
	  function Activate(supportLink, docLink) {
	    var _this;
	    var errors = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	    babelHelpers.classCallCheck(this, Activate);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Activate).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _checkRequest);
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _formData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _errors$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _supportLink, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _docLink, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _errors$1, errors.length > 0 ? new ErrorCollection(errors) : new ErrorCollection());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formData, new FormData());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _supportLink, !main_core.Type.isNil(supportLink) ? supportLink : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _docLink, !main_core.Type.isNil(docLink) ? docLink : '');
	    return _this;
	  }
	  babelHelpers.createClass(Activate, [{
	    key: "getContent",
	    value: function getContent() {
	      var _babelHelpers$classPr;
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<form id=\"intranet-license-activate-key-form\">\n\t\t\t<div class=\"license-intranet-popup__content --key-activate\">\n\t\t\t\t<div class=\"license-intranet-popup__block --center\">\n\t\t\t\t\t<div class=\"license-intranet-popup__title\">", "</div>\n\t\t\t\t\t<div class=\"license-intranet-popup__text\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<div class=\"license-intranet-popup__buttons\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\n\t\t\t\t<div class=\"license-intranet-popup__block --input-area\">\n\t\t\t\t\t<div class=\"ui-form licence-key-form\">\n\t\t\t\t\t\t<div class=\"ui-form-row ui-form-row-inline\">\n\t\t\t\t\t\t\t\t<div class=\"ui-form-label\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\t\t\tname=\"key\"\n\t\t\t\t\t\t\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element licence-key-form__input\"\n\t\t\t\t\t\t\t\t\t\t\tplaceholder=\"XXXX-XXXX-XXXX-XXXX-XX\"\n\t\t\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t\n\t\t\t\t<a class=\"license-intranet-popup__help-link\"\n\t\t\t\ttarget=\"_blank\"\n\t\t\t\thref=\"", "\"\n\t\t\t>\n\t\t\t\t", "\n\t\t\t</a>\n\t\t\t</div>\n\t\t\t</form>\n\t\t"])), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_ACTIVATE_KEY'), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_SUBTITLE_ACTIVATE_KEY', {
	        '#SUPPORT_LINK#': babelHelpers.classPrivateFieldGet(this, _supportLink)
	      }), this.renderRefreshPageBtn().render(), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_KEY_FIELD'), (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _formData).get('key')) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : '', this.getSendBtn().render(), babelHelpers.classPrivateFieldGet(this, _docLink), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_NEED_HELP'));
	    }
	  }, {
	    key: "getSendBtn",
	    value: function getSendBtn() {
	      var _this2 = this;
	      return new ui_buttons.Button({
	        text: '',
	        noCaps: false,
	        round: true,
	        className: 'ui-btn-icon-add licence-key-form__submit-btn',
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          var formNode = document.querySelector('#intranet-license-activate-key-form');
	          var formData = new FormData(formNode);
	          babelHelpers.classPrivateFieldGet(_this2, _errors$1).hideErrors();
	          babelHelpers.classPrivateFieldGet(_this2, _errors$1).cleanErrors();
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	            source: _this2,
	            target: new Loading()
	          });
	          babelHelpers.classPrivateFieldSet(_this2, _formData, formData);
	          var request = new Request('activate', 'POST', 'class');
	          request.send(formData).then(_this2.successHandler.bind(_this2), _this2.failureHandler.bind(_this2));
	        }
	      });
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      babelHelpers.classPrivateFieldGet(this, _errors$1).show();
	    }
	  }, {
	    key: "successHandler",
	    value: function successHandler() {
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
	        source: this
	      });
	      document.location.href = '/';
	    }
	  }, {
	    key: "failureHandler",
	    value: function failureHandler(event) {
	      babelHelpers.classPrivateFieldSet(this, _errors$1, new ErrorCollection(event.errors));
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
	        source: this
	      });
	    }
	  }, {
	    key: "renderRefreshPageBtn",
	    value: function renderRefreshPageBtn() {
	      var _this3 = this;
	      return new ui_buttons.Button({
	        text: main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_REFRESH_PAGE'),
	        noCaps: false,
	        round: true,
	        size: BX.UI.Button.Size.LARGE,
	        color: BX.UI.Button.Color.SUCCESS,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          _classPrivateMethodGet(_this3, _checkRequest, _checkRequest2).call(_this3);
	        }
	      });
	    }
	  }]);
	  return Activate;
	}(BaseContent);
	function _checkRequest2() {
	  var request = new Request('check');
	  request.send().then(this.successHandler.bind(this), this.failureHandler.bind(this));
	  main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	    source: this,
	    target: new Loading()
	  });
	}

	var _templateObject$2;
	var Success = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(Success, _BaseContent);
	  function Success() {
	    babelHelpers.classCallCheck(this, Success);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Success).apply(this, arguments));
	  }
	  babelHelpers.createClass(Success, [{
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"intranet-license-partner-form\">\n\t\t\t\t<div class=\"license-intranet-popup__content --partner-success\">\n\t\t\t\t\t<div class=\"intranet-license-partner-form__success-icon\"></div>\n\t\t\t\t\t<div class=\"license-intranet-popup__title\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER_SUCCESS'));
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      popup.setButtons(this.getButtonCollection());
	    }
	  }]);
	  return Success;
	}(BaseContent);

	var _templateObject$3, _templateObject2$1;
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _formData$1 = /*#__PURE__*/new WeakMap();
	var _errors$2 = /*#__PURE__*/new WeakMap();
	var Partner = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(Partner, _BaseContent);
	  function Partner(parameters) {
	    var _this;
	    babelHelpers.classCallCheck(this, Partner);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Partner).call(this));
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _formData$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _errors$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _errors$2, new ErrorCollection());
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _formData$1, new FormData());
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _formData$1).set('name', main_core.Type.isString(parameters === null || parameters === void 0 ? void 0 : parameters.NAME) ? parameters.NAME : '');
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _formData$1).set('phone', main_core.Type.isString(parameters === null || parameters === void 0 ? void 0 : parameters.PHONE) ? parameters.PHONE : '');
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _formData$1).set('email', main_core.Type.isString(parameters === null || parameters === void 0 ? void 0 : parameters.EMAIL) ? parameters.EMAIL : '');
	    return _this;
	  }
	  babelHelpers.createClass(Partner, [{
	    key: "getAlert",
	    value: function getAlert(text) {
	      var alert = new ui_alerts.Alert({
	        color: ui_alerts.AlertColor.DANGER,
	        icon: ui_alerts.AlertIcon.DANGER,
	        size: ui_alerts.AlertSize.SMALL
	      });
	      if (text) {
	        alert.setText(text);
	      }
	      return alert.getContainer();
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3;
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<form id=\"main-coupon-activate-partner-form\">\n\t\t\t<div id=\"intranet-license-partner-form\">\n\t\t\t\t<div class=\"license-intranet-popup__content --partner\">\n\t\t\t\t\t<div class=\"license-intranet-popup__block --center\">\n\t\t\t\t\t\t<div class=\"license-intranet-popup__title\">", "</div>\n\t\t\t\t\t\t<div class=\"license-intranet-popup__text ui-typography-text-lg\">", "</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t\t<div class=\"license-intranet-popup__partner-form\">\n\t\t\t\t\t\t<div class=\"license-intranet-popup__block --input-area\">\n\t\t\t\t\t\t\t<div class=\"license-intranet-popup__input-label\">", "</div>\n\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t\t\t\t<input type=\"text\" name=\"name\" value=\"", "\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t   </div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"license-intranet-popup__block --input-area\">\n\t\t\t\t\t\t\t<div class=\"license-intranet-popup__input-label\">", "</div>\n\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t\t\t\t<input type=\"text\" name=\"phone\" value=\"", "\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"license-intranet-popup__block --input-area\">\n\t\t\t\t\t\t\t<div class=\"license-intranet-popup__input-label\">", "</div>\n\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t\t\t\t<input type=\"text\" name=\"email\" value=\"", "\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t</form>\n\t\t"])), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER'), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_SUBTITLE_PARTNER'), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_NAME_FIELD'), (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _formData$1).get('name')) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : '', main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_PHONE_FIELD'), (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _formData$1).get('phone')) !== null && _babelHelpers$classPr2 !== void 0 ? _babelHelpers$classPr2 : '', main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_EMAIL_FIELD'), (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _formData$1).get('email')) !== null && _babelHelpers$classPr3 !== void 0 ? _babelHelpers$classPr3 : '');
	    }
	  }, {
	    key: "getSuccessContent",
	    value: function getSuccessContent() {
	      return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"intranet-license-partner-form\">\n\t\t\t\t<div class=\"license-intranet-popup__content --partner-success\">\n\t\t\t\t\t<div class=\"intranet-license-partner-form__success-icon\"></div>\n\t\t\t\t\t<div class=\"license-intranet-popup__title\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_PARTNER_SUCCESS'));
	    }
	  }, {
	    key: "getButtonCollection",
	    value: function getButtonCollection() {
	      var _this2 = this;
	      var sendBtn = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_SEND'),
	        noCaps: false,
	        round: true,
	        size: BX.UI.Button.Size.LARGE,
	        color: BX.UI.Button.Color.SUCCESS,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          var formNode = document.querySelector('#main-coupon-activate-partner-form');
	          var formData = new FormData(formNode);
	          babelHelpers.classPrivateFieldGet(_this2, _errors$2).hideErrors();
	          babelHelpers.classPrivateFieldGet(_this2, _errors$2).cleanErrors();
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	            source: _this2,
	            target: new Loading()
	          });
	          babelHelpers.classPrivateFieldSet(_this2, _formData$1, formData);
	          var request = new Request('queryPartner', 'POST', 'class');
	          request.send(formData).then(_this2.successHandler.bind(_this2), _this2.failureHandler.bind(_this2));
	        }
	      });
	      return [sendBtn];
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      popup.setButtons(this.getButtonCollection());
	      babelHelpers.classPrivateFieldGet(this, _errors$2).show();
	    }
	  }, {
	    key: "successHandler",
	    value: function successHandler() {
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	        source: this,
	        target: new Success()
	      });
	    }
	  }, {
	    key: "failureHandler",
	    value: function failureHandler(event) {
	      babelHelpers.classPrivateFieldSet(this, _errors$2, new ErrorCollection(event.errors));
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
	        source: this
	      });
	    }
	  }]);
	  return Partner;
	}(BaseContent);

	var _templateObject$4;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _buyLink = /*#__PURE__*/new WeakMap();
	var _partnerId = /*#__PURE__*/new WeakMap();
	var _state = /*#__PURE__*/new WeakMap();
	var _parameters = /*#__PURE__*/new WeakMap();
	var _createPartnerBtn = /*#__PURE__*/new WeakSet();
	var _createBuyBtn = /*#__PURE__*/new WeakSet();
	var _checkRequest$1 = /*#__PURE__*/new WeakSet();
	var ExpiredLicense = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(ExpiredLicense, _BaseContent);
	  function ExpiredLicense(buyLink) {
	    var _this;
	    var partnerId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	    var parameters = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : [];
	    babelHelpers.classCallCheck(this, ExpiredLicense);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ExpiredLicense).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _checkRequest$1);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _createBuyBtn);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _createPartnerBtn);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _buyLink, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _partnerId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _state, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _parameters, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _partnerId, partnerId);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _buyLink, (buyLink === null || buyLink === void 0 ? void 0 : buyLink.length) > 0 ? buyLink : 'https://www.1c-bitrix.ru/personal/order/basket.php');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _parameters, parameters);
	    return _this;
	  }
	  babelHelpers.createClass(ExpiredLicense, [{
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"license-intranet-popup__content --access-closed\">\n\t\t\t\t<div class=\"license-intranet-popup__block\">\n\t\t\t\t\t<div class=\"license-intranet-popup__title\">", "</div>\n\t\t\t\t\t<div class=\"license-intranet-popup__content-area\">\n\t\t\t\t\t\t<p class=\"license-intranet-popup__text ui-typography-text-lg\">", "</p>\n\t\t\t\t\t\t<div class=\"license-intranet-popup__buttons\">\n\t\t\t\t\t\t\t<div class=\"license-intranet-popup__button --renew-license\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"license-intranet-popup__button\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<a class=\"license-intranet-popup__help-link\"\n\t\t\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\t\t\thref=\"", "\"\n\t\t\t\t\t\t>\n\t\t\t\t", "\n\t\t\t</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_OVER_TITLE'), main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_LICENSE_OVER_DESCRIPTION'), _classPrivateMethodGet$1(this, _createBuyBtn, _createBuyBtn2).call(this).render(), _classPrivateMethodGet$1(this, _createPartnerBtn, _createPartnerBtn2).call(this).render(), babelHelpers.classPrivateFieldGet(this, _parameters).DOC_LINK, main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_NEED_HELP'));
	    }
	  }, {
	    key: "getButtonCollection",
	    value: function getButtonCollection() {
	      return [];
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      if (main_core.Type.isNil(babelHelpers.classPrivateFieldGet(this, _state))) {
	        _classPrivateMethodGet$1(this, _checkRequest$1, _checkRequest2$1).call(this);
	      } else if (babelHelpers.classPrivateFieldGet(this, _state) === ExpiredLicense.stateTypes.LICENSE_ACTIVATED) {
	        document.location.href = '/';
	      } else {
	        popup.setContent(this.getContent());
	      }
	    }
	  }, {
	    key: "successHandler",
	    value: function successHandler(response) {
	      var expireDate = new Date(response.data.DATE_TO_SOURCE);
	      if (!main_core.Type.isNil(response.data.DATE_TO_SOURCE) && expireDate.getTime() > new Date().getTime()) {
	        babelHelpers.classPrivateFieldSet(this, _state, ExpiredLicense.stateTypes.LICENSE_ACTIVATED);
	      } else {
	        babelHelpers.classPrivateFieldSet(this, _state, ExpiredLicense.stateTypes.LICENSE_EXPIRED);
	      }
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
	        source: this
	      });
	    }
	  }, {
	    key: "failureHandler",
	    value: function failureHandler(response) {
	      babelHelpers.classPrivateFieldSet(this, _state, ExpiredLicense.stateTypes.UPDATE_SERVER_IS_UNAVAILABLE);
	      var errors = main_core.Type.isArray(response.errors) ? response.errors : [];
	      // let errors = [];
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	        source: this,
	        target: new Activate(babelHelpers.classPrivateFieldGet(this, _parameters).SUPPORT_LINK, babelHelpers.classPrivateFieldGet(this, _parameters).DOC_LINK, errors)
	      });
	    }
	  }]);
	  return ExpiredLicense;
	}(BaseContent);
	function _createPartnerBtn2() {
	  var _this2 = this;
	  return new ui_buttons.Button({
	    text: main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_PARTNER'),
	    noCaps: false,
	    round: true,
	    size: BX.UI.Button.Size.LARGE,
	    color: BX.UI.Button.Color.LIGHT_BORDER,
	    tag: BX.UI.Button.Tag.BUTTON,
	    onclick: function onclick() {
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	        source: _this2,
	        target: new Partner(babelHelpers.classPrivateFieldGet(_this2, _parameters))
	      });
	    }
	  });
	}
	function _createBuyBtn2() {
	  return new ui_buttons.Button({
	    text: main_core.Loc.getMessage('MAIN_COUPON_ACTIVATION_BUTTON_RENEW'),
	    noCaps: false,
	    round: true,
	    link: babelHelpers.classPrivateFieldGet(this, _buyLink),
	    size: BX.UI.Button.Size.LARGE,
	    color: BX.UI.Button.Color.SUCCESS,
	    tag: BX.UI.Button.Tag.LINK,
	    props: {
	      target: '_blank'
	    }
	  });
	}
	function _checkRequest2$1() {
	  var request = new Request('check');
	  request.send().then(this.successHandler.bind(this), this.failureHandler.bind(this));
	  main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	    source: this,
	    target: new Loading()
	  });
	}
	babelHelpers.defineProperty(ExpiredLicense, "stateTypes", {
	  LICENSE_EXPIRED: 'license_expired',
	  LICENSE_ACTIVATED: 'license_activated',
	  UPDATE_SERVER_IS_UNAVAILABLE: 'update_server_is_unavailable'
	});

	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _popup = /*#__PURE__*/new WeakMap();
	var _currentContent = /*#__PURE__*/new WeakMap();
	var _history = /*#__PURE__*/new WeakMap();
	var LicensePopup = /*#__PURE__*/function () {
	  function LicensePopup(popupContent) {
	    babelHelpers.classCallCheck(this, LicensePopup);
	    _classPrivateFieldInitSpec$5(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(this, _currentContent, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$5(this, _history, {
	      writable: true,
	      value: []
	    });
	    if (popupContent instanceof BaseContent) {
	      babelHelpers.classPrivateFieldSet(this, _currentContent, popupContent);
	    } else {
	      babelHelpers.classPrivateFieldSet(this, _currentContent, new ExpiredLicense());
	    }
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', this.changeHandler.bind(this));
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', this.backHandler.bind(this));
	    new Request('activate', 'POST');
	  }
	  babelHelpers.createClass(LicensePopup, [{
	    key: "getPopup",
	    value: function getPopup() {
	      if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	        return babelHelpers.classPrivateFieldGet(this, _popup);
	      }
	      babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	        className: 'license-intranet-popup',
	        padding: 34,
	        width: 700,
	        closeIcon: false,
	        borderRadius: '20px'
	      }));
	      return babelHelpers.classPrivateFieldGet(this, _popup);
	    }
	  }, {
	    key: "addHistory",
	    value: function addHistory(content) {
	      babelHelpers.classPrivateFieldGet(this, _history).push(content);
	    }
	  }, {
	    key: "back",
	    value: function back() {
	      var content = babelHelpers.classPrivateFieldGet(this, _history).pop();
	      if (content instanceof BaseContent) {
	        babelHelpers.classPrivateFieldSet(this, _currentContent, content);
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.changeContent();
	      this.getPopup().show();
	    }
	  }, {
	    key: "changeContent",
	    value: function changeContent() {
	      babelHelpers.classPrivateFieldGet(this, _currentContent).init(this.getPopup());
	      main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:onAfterChangeContent', {
	        target: babelHelpers.classPrivateFieldGet(this, _currentContent).getContent()
	      });
	    }
	  }, {
	    key: "changeHandler",
	    value: function changeHandler(event) {
	      if (event.data.target instanceof BaseContent) {
	        this.addHistory(babelHelpers.classPrivateFieldGet(this, _currentContent));
	        babelHelpers.classPrivateFieldSet(this, _currentContent, event.data.target);
	      }
	      this.changeContent();
	    }
	  }, {
	    key: "backHandler",
	    value: function backHandler(event) {
	      if (event.data.source instanceof BaseContent) {
	        this.back();
	      }
	      this.changeContent();
	    }
	  }], [{
	    key: "createExpiredLicensePopup",
	    value: function createExpiredLicensePopup(parameters) {
	      var partnerId = main_core.Type.isNil(parameters.PARTNER_ID) ? 0 : parameters.PARTNER_ID;
	      var buyId = main_core.Type.isString(parameters.BUY_LINK) ? parameters.BUY_LINK : '';
	      return new LicensePopup(new ExpiredLicense(buyId, partnerId, parameters));
	    }
	  }]);
	  return LicensePopup;
	}();

	exports.LicensePopup = LicensePopup;

}((this.BX.Main = this.BX.Main || {}),BX.UI,BX.Main,BX.UI,BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
