this.BX = this.BX || {};
(function (exports,main_core,ui_buttons,main_popup,main_core_events) {
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

	var _templateObject;
	var Activate = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(Activate, _BaseContent);

	  function Activate() {
	    babelHelpers.classCallCheck(this, Activate);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Activate).apply(this, arguments));
	  }

	  babelHelpers.createClass(Activate, [{
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<center>\n\t\t\t\t<h1>\u0410\u043A\u0442\u0438\u0432\u043E\u0440\u043E\u0432\u0430\u0442\u044C \u043A\u043B\u044E\u0447</h1>\n\t\t\t\t<h5>\u042D\u0442\u043E \u043C\u043E\u0436\u0435\u0442 \u0441\u0434\u0435\u043B\u0430\u0442\u044C \u0442\u043E\u043B\u044C\u043A\u043E \u043F\u043E\u043B\u044C\u0437\u043E\u0432\u0430\u0442\u0435\u043B\u044C \u0441 \u043F\u0440\u0430\u0432\u0430\u043C\u0438 \u0430\u0434\u043C\u0438\u043D\u0438\u0441\u0442\u0440\u0430\u0442\u043E\u0440\u0430</h5>\n\t\t\t\t</center>\n\t\t\t\t<form id=\"intranet-license-activate-key-form\" name=\"testForm\">\n\t\t\t\t<div class=\"ui-form\">\n\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t  <div class=\"ui-form-label\">\n\t\t\t\t\t   <div class=\"ui-ctl-label-text\">\u041B\u043E\u0433\u0438\u043D \u0430\u0434\u043C\u0438\u043D\u0438\u0441\u0442\u0440\u0430\u0442\u043E\u0440\u0430</div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t  <div class=\"ui-form-content\">\n\t\t\t\t\t   <div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t    <input type=\"text\" name=\"login\" class=\"ui-ctl-element\" placeholder=\"Admin\">\n\t\t\t\t\t   </div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t </div>\n\t\t\t\t\t <div class=\"ui-form-row\">\n\t\t\t\t\t  <div class=\"ui-form-label\">\n\t\t\t\t\t   <div class=\"ui-ctl-label-text\">\u041F\u0430\u0440\u043E\u043B\u044C</div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t  <div class=\"ui-form-content\">\n\t\t\t\t\t   <div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t    <input type=\"password\" name=\"password\" class=\"ui-ctl-element\" placeholder=\"**********\">\n\t\t\t\t\t   </div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t </div>\n\t\t\t\t\t\n\t\t\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t\t  <div class=\"ui-form-label\">\n\t\t\t\t\t   <div class=\"ui-ctl-label-text\">\u041B\u0438\u0447\u0435\u043D\u0437\u0438\u043E\u043D\u043D\u044B\u0439 \u043A\u043B\u044E\u0447 \u0438\u043B\u0438 \u043A\u0443\u043F\u043E\u043D</div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t  <div class=\"ui-form-content\">\n\t\t\t\t\t   <div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t    <input type=\"text\" name=\"key\" class=\"ui-ctl-element\" placeholder=\"XXXX-XXXX-XXXX-XXXX-XX\">\n\t\t\t\t\t   </div>\n\t\t\t\t\t  </div>\n\t\t\t\t\t </div>\n\t\t\t\t\t</div>\n\t\t\t\t</form>\n\t\t\t</div>\n"])));
	    }
	  }, {
	    key: "getButtonCollection",
	    value: function getButtonCollection() {
	      var _this = this;

	      var backBtn = new ui_buttons.Button({
	        text: '',
	        noCaps: true,
	        round: true,
	        className: 'license-popup-back-btn',
	        icon: BX.UI.Button.Icon.BACK,
	        color: BX.UI.Button.Color.LIGHT_BORDER,
	        size: BX.UI.Button.Size.MEDIUM,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', {
	            source: _this
	          });
	        }
	      });
	      var activateBtn = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('INTRANET_LICENSE_POPUP_BUTTON_ACTIVATE'),
	        noCaps: false,
	        round: true,
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.SUCCESS,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          var _console;

	          console.log('do request');
	          var formNode = document.querySelector('#intranet-license-activate-key-form');
	          var formData = new FormData(formNode); // console.log(formNode);

	          (_console = console).log.apply(_console, babelHelpers.toConsumableArray(formData)); // console.log(new URLSearchParams(formData).toString());
	          // this.request(formData);

	        }
	      });
	      var helpBtn = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('INTRANET_LICENSE_POPUP_BUTTON_NEED_HELP'),
	        noCaps: true,
	        round: true,
	        className: 'license-popup-link-btn',
	        link: 'https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=135&LESSON_ID=25720',
	        size: BX.UI.Button.Size.EXTRA_SMALL,
	        color: BX.UI.Button.Color.LINK,
	        tag: BX.UI.Button.Tag.LINK,
	        onclick: function onclick() {
	          console.log('do request');
	        }
	      });
	      return [backBtn, activateBtn, helpBtn];
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      popup.setButtons(this.getButtonCollection());
	    }
	  }, {
	    key: "request",
	    value: function request(data) {
	      main_core.ajax.runComponentAction('bitrix:intranet.license.popup', 'activate', {
	        mode: 'class',
	        data: data
	      }).then(function (response) {
	        console.log(response);
	      }, function (response) {
	        console.error(response);
	      });
	    }
	  }]);
	  return Activate;
	}(BaseContent);

	var _templateObject$1;
	var ExpiredLicense = /*#__PURE__*/function (_BaseContent) {
	  babelHelpers.inherits(ExpiredLicense, _BaseContent);

	  function ExpiredLicense() {
	    babelHelpers.classCallCheck(this, ExpiredLicense);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ExpiredLicense).apply(this, arguments));
	  }

	  babelHelpers.createClass(ExpiredLicense, [{
	    key: "getContent",
	    value: function getContent() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div style=\"margin: 13px; height: 85%; background-color: #dbf2f9;\">\n\t\t\t\t<center>\n\t\t\t\t\t<h1>\u0414\u043E\u0441\u0442\u0443\u043F \u0432\u0440\u0435\u043C\u0435\u043D\u043D\u043E \u0437\u0430\u043A\u0440\u044B\u0442</h1>\n\t\t\t\t\t<div>\n\t\t\t\t\t\t<p>\n\t\t\t\t\t\t\u0421\u0440\u043E\u043A \u0434\u0435\u0439\u0441\u0442\u0432\u0438\u044F \u043B\u0438\u0446\u0443\u043D\u0437\u0438\u0438 \u0437\u0430\u043A\u0430\u043D\u0447\u0438\u043B\u0441\u044F, \u0434\u043B\u044F \u0434\u0430\u043B\u044C\u043D\u0435\u0439\u0448\u0435\u0439 \u0440\u0430\u0431\u043E\u0442\u044B \u0432\u0430\u043C \u043D\u0435\u0436\u043D\u043E \u043F\u0440\u043E\u0434\u043B\u0438\u0442\u044C \u043F\u043E\u0434\u043F\u0438\u0441\u043A\u0443. \n\t\t\t\t\t\t\u041D\u0435 \u0432\u043E\u043B\u043D\u0443\u0439\u0442\u0435\u0441\u044C, \u0432\u0441\u0435 \u0432\u0430\u0448\u0438 \u0434\u0430\u043D\u043D\u044B\u0435 \u0441\u043E\u0437\u0440\u0430\u043D\u0438\u043B\u0438\u0441\u044C. \n\t\t\t\t\t\t\u041F\u043E\u0441\u043B\u0435 \u043F\u043E\u043A\u0443\u043F\u043A\u0438 \u0430\u043A\u0442\u0438\u0432\u0438\u0440\u043E\u0432\u0430\u0442\u044C \u043A\u043B\u044E\u0447 \u043F\u0440\u043E\u0434\u043B\u0435\u043D\u0438\u044F \u0441\u043C\u043E\u0436\u0435\u0442 \u0430\u0434\u043C\u0438\u043D\u0438\u0441\u0442\u0440\u0430\u0442\u043E\u0440 \u0432\u0430\u0448\u0435\u0433\u043E \u0411\u0438\u0442\u0440\u0438\u043A\u044124\n\t\t\t\t\t\t</p>\n\t\t\t\t\t</div>\n\t\t\t\t</center>\n\t\t\t</div>\n"])));
	    }
	  }, {
	    key: "getButtonCollection",
	    value: function getButtonCollection() {
	      var _this = this;

	      var buyBtn = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('INTRANET_LICENSE_POPUP_BUTTON_RENEW'),
	        noCaps: false,
	        round: true,
	        link: 'https://www.1c-bitrix.ru/personal/order/basket.php',
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.SUCCESS,
	        tag: BX.UI.Button.Tag.LINK
	      });
	      var activateBtn = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('INTRANET_LICENSE_POPUP_BUTTON_ACTIVATE_KEY'),
	        noCaps: false,
	        round: true,
	        size: BX.UI.Button.Size.MEDIUM,
	        color: BX.UI.Button.Color.PRIMARY,
	        tag: BX.UI.Button.Tag.BUTTON,
	        onclick: function onclick() {
	          console.log('event activate');
	          main_core_events.EventEmitter.emit(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', {
	            source: _this,
	            target: new Activate()
	          });
	        }
	      });
	      return [buyBtn, activateBtn];
	    }
	  }, {
	    key: "init",
	    value: function init(popup) {
	      popup.setContent(this.getContent());
	      popup.setButtons(this.getButtonCollection());
	    }
	  }]);
	  return ExpiredLicense;
	}(BaseContent);

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _popup = /*#__PURE__*/new WeakMap();

	var _currentContent = /*#__PURE__*/new WeakMap();

	var _history = /*#__PURE__*/new WeakMap();

	var LicensePopup = /*#__PURE__*/function () {
	  function LicensePopup() {
	    babelHelpers.classCallCheck(this, LicensePopup);

	    _classPrivateFieldInitSpec(this, _popup, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _currentContent, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _history, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _history, []);
	    babelHelpers.classPrivateFieldSet(this, _currentContent, new ExpiredLicense());
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:changeContent', this.changeHandler.bind(this));
	    main_core_events.EventEmitter.subscribe(main_core_events.EventEmitter.GLOBAL_TARGET, 'MainCouponActivation:back', this.backHandler.bind(this));
	  }

	  babelHelpers.createClass(LicensePopup, [{
	    key: "getPopup",
	    value: function getPopup() {
	      if (babelHelpers.classPrivateFieldGet(this, _popup)) {
	        return babelHelpers.classPrivateFieldGet(this, _popup);
	      }

	      babelHelpers.classPrivateFieldSet(this, _popup, new main_popup.Popup({
	        width: 808,
	        height: 535,
	        closeIcon: true
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
	  }]);
	  return LicensePopup;
	}();
	console.log('======== ......... >');

	exports.LicensePopup = LicensePopup;

}((this.BX.Intranet = this.BX.Intranet || {}),BX,BX.UI,BX.Main,BX.Event));
//# sourceMappingURL=script.js.map
