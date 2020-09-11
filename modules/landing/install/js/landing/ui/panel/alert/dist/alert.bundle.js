this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_loc,landing_ui_panel_base) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"", "\" target=\"_blank\" class=\"landing-ui-panel-alert-support-link\">", "</a>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-alert-action\">", "</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button class=\"ui-btn ui-btn-link\" onclick=\"", "\">", "</button>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-alert-text\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * Implements interface for works with alert panel
	 * use this panel for show error and info messages
	 *
	 * Implements singleton design pattern. Don't use it as constructor
	 * use BX.Landing.UI.Panel.Alert.getInstance() for get instance of module
	 * @memberOf BX.Landing.UI.Panel
	 */

	var Alert = /*#__PURE__*/function (_BasePanel) {
	  babelHelpers.inherits(Alert, _BasePanel);
	  babelHelpers.createClass(Alert, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      return this.staticCache.remember('instance', function () {
	        return new Alert();
	      });
	    }
	  }]);

	  function Alert() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Alert);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Alert).call(this, options));
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onCloseClick = _this.onCloseClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.text = _this.getText();
	    _this.closeButton = _this.getCloseButton();
	    _this.action = _this.getAction();
	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-alert');
	    main_core.Dom.append(_this.text, _this.layout);
	    main_core.Dom.append(_this.action, _this.layout);
	    main_core.Dom.append(_this.layout, document.body);
	    return _this;
	  }

	  babelHelpers.createClass(Alert, [{
	    key: "getText",
	    value: function getText() {
	      return this.cache.remember('text', function () {
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "getCloseButton",
	    value: function getCloseButton() {
	      var _this2 = this;

	      return this.cache.remember('closeButton', function () {
	        var text = landing_loc.Loc.getMessage('LANDING_ALERT_ACTION_CLOSE');
	        return main_core.Tag.render(_templateObject2(), _this2.onCloseClick, text);
	      });
	    }
	  }, {
	    key: "getAction",
	    value: function getAction() {
	      var _this3 = this;

	      return this.cache.remember('action', function () {
	        return main_core.Tag.render(_templateObject3(), _this3.getCloseButton());
	      });
	    }
	  }, {
	    key: "show",
	    value: function show(type, text) {
	      var _this4 = this;

	      var hideSupportLink = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var promise = Promise.resolve(this);

	      if (this.isShown()) {
	        promise = this.hide();
	      }

	      return promise.then(function () {
	        void babelHelpers.get(babelHelpers.getPrototypeOf(Alert.prototype), "show", _this4).call(_this4, _this4);

	        if (type === 'error') {
	          main_core.Dom.removeClass(_this4.layout, 'landing-ui-alert');
	          main_core.Dom.addClass(_this4.layout, 'landing-ui-error');
	        } else {
	          main_core.Dom.removeClass(_this4.layout, 'landing-ui-error');
	          main_core.Dom.addClass(_this4.layout, 'landing-ui-alert');
	        }

	        _this4.text.innerHTML = "".concat(text || type, " ");

	        if (!hideSupportLink) {
	          main_core.Dom.append(_this4.getSupportLink(), _this4.text);
	        }

	        return _this4;
	      });
	    }
	  }, {
	    key: "getSupportLink",
	    value: function getSupportLink() {
	      var _this5 = this;

	      return this.cache.remember('supportLink', function () {
	        var url = 'https://helpdesk.bitrix24.com/ticket.php';

	        switch (landing_loc.Loc.getMessage('LANGUAGE_ID')) {
	          case 'ru':
	          case 'by':
	          case 'kz':
	            url = 'https://helpdesk.bitrix24.ru/ticket.php';
	            break;

	          case 'de':
	            url = 'https://helpdesk.bitrix24.de/ticket.php';
	            break;

	          case 'br':
	            url = 'https://helpdesk.bitrix24.com.br/ticket.php';
	            break;

	          case 'es':
	            url = 'https://helpdesk.bitrix24.es/ticket.php';
	            break;

	          default:
	        }

	        _this5.supportLink = BX.create('a', {
	          props: {
	            className: 'landing-ui-panel-alert-support-link'
	          },
	          html: BX.Landing.Loc.getMessage('LANDING_ALERT_ACTION_SUPPORT_LINK'),
	          attrs: {
	            href: url,
	            target: '_blank'
	          }
	        });
	        var text = landing_loc.Loc.getMessage('LANDING_ALERT_ACTION_SUPPORT_LINK');
	        return main_core.Tag.render(_templateObject4(), url, text);
	      });
	    }
	  }, {
	    key: "onCloseClick",
	    value: function onCloseClick() {
	      void this.hide();
	    }
	  }]);
	  return Alert;
	}(landing_ui_panel_base.BasePanel);
	babelHelpers.defineProperty(Alert, "staticCache", new main_core.Cache.MemoryCache());

	exports.Alert = Alert;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=alert.bundle.js.map
