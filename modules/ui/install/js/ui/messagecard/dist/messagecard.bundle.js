this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-body\"></div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-header\"></div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var BaseCard = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseCard, _EventEmitter);

	  function BaseCard() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseCard).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.data = babelHelpers.objectSpread({}, options);
	    _this.options = _this.data;
	    _this.id = main_core.Type.isStringFilled(_this.options.id) ? _this.options.id : main_core.Text.getRandom();
	    _this.hidden = main_core.Text.toBoolean(_this.options.hidden);
	    _this.onClickHandler = main_core.Type.isFunction(_this.options.onClick) ? _this.options.onClick : function () {};
	    _this.onClick = _this.onClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.layout = _this.getLayout();
	    _this.header = _this.getHeader();
	    _this.body = _this.getBody();

	    _this.setTitle(_this.options.title || '');

	    _this.setHidden(_this.options.hidden);

	    if (main_core.Type.isStringFilled(_this.options.className)) {
	      main_core.Dom.addClass(_this.layout, _this.options.className);
	    }

	    if (main_core.Type.isObject(_this.options.attrs)) {
	      main_core.Dom.adjust(_this.layout, {
	        attrs: _this.options.attrs
	      });
	    }

	    main_core.Event.bind(_this.layout, 'click', _this.onClick);
	    return _this;
	  }

	  babelHelpers.createClass(BaseCard, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject(), _this2.getHeader(), _this2.getBody());
	      });
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return main_core.Tag.render(_templateObject2());
	      });
	    }
	  }, {
	    key: "getBody",
	    value: function getBody() {
	      return this.cache.remember('body', function () {
	        return main_core.Tag.render(_templateObject3());
	      });
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      this.getHeader().textContent = title;
	    }
	  }, {
	    key: "setHidden",
	    value: function setHidden(hidden) {
	      main_core.Dom.attr(this.getLayout(), 'hidden', hidden || null);
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      this.onClickHandler(this);
	      this.emit('onClick');
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.setHidden(false);
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return main_core.Dom.attr(this.getLayout(), 'hidden') === null;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.setHidden(true);
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.getLayout();
	    }
	  }]);
	  return BaseCard;
	}(main_core_events.EventEmitter);

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-message-actions\"></div>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"ui-card-message-close-button\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></div>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-message-description\">", "</div>\n\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-message-title\">", "</div>\n\t\t\t"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-card-message-icon\" style=\"background-image: url(", ")\"></div>\n\t\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var MessageCard = /*#__PURE__*/function (_BaseCard) {
	  babelHelpers.inherits(MessageCard, _BaseCard);

	  function MessageCard(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, MessageCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MessageCard).call(this, options));
	    main_core.Dom.addClass(_this.getLayout(), 'ui-card-message');
	    _this.onCloseClick = _this.onCloseClick.bind(babelHelpers.assertThisInitialized(_this));

	    if (_this.options.angle === false) {
	      main_core.Dom.addClass(_this.getLayout(), 'ui-card-message-without-angle');
	    }

	    if (main_core.Type.isStringFilled(_this.options.icon)) {
	      main_core.Dom.append(_this.getIcon(), _this.getHeader());
	    }

	    if (!main_core.Type.isArray(_this.options.actionElements)) {
	      _this.options.actionElements = [];
	    }

	    main_core.Dom.append(_this.getTitle(), _this.getHeader());
	    main_core.Dom.append(_this.getDescription(), _this.getBody());

	    if (_this.options.closeable !== false) {
	      main_core.Dom.append(_this.getCloseButton(), _this.getLayout());
	    }

	    if (_this.options.hideActions !== true || _this.options.more) {
	      main_core.Dom.append(_this.getActionsContainer(), _this.getLayout());
	    }

	    if (_this.isAllowRestoreState()) {
	      var state = MessageCard.cache.get(_this.options.id, {
	        shown: true
	      });

	      if (state.shown) {
	        _this.show();
	      } else {
	        _this.hide();
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(MessageCard, [{
	    key: "isAllowRestoreState",
	    value: function isAllowRestoreState() {
	      return this.options.restoreState && this.options.id;
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      var _this2 = this;

	      return this.cache.remember('icon', function () {
	        return main_core.Tag.render(_templateObject$1(), _this2.options.icon);
	      });
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      var _this3 = this;

	      return this.cache.remember('title', function () {
	        return main_core.Tag.render(_templateObject2$1(), _this3.options.header);
	      });
	    }
	  }, {
	    key: "getDescription",
	    value: function getDescription() {
	      var _this4 = this;

	      return this.cache.remember('description', function () {
	        return main_core.Tag.render(_templateObject3$1(), _this4.options.description);
	      });
	    }
	  }, {
	    key: "getCloseButton",
	    value: function getCloseButton() {
	      var _this5 = this;

	      return this.cache.remember('closeButton', function () {
	        return main_core.Tag.render(_templateObject4(), _this5.onCloseClick);
	      });
	    }
	  }, {
	    key: "onCloseClick",
	    value: function onCloseClick(event) {
	      event.preventDefault();
	      this.hide();
	      this.emit('onClose');
	      MessageCard.cache.set(this.options.id, {
	        shown: false
	      });
	    }
	  }, {
	    key: "getActionsContainer",
	    value: function getActionsContainer() {
	      var _this6 = this;

	      return this.cache.remember('actionsContainer', function () {
	        var actionWrapper = main_core.Tag.render(_templateObject5());

	        _this6.options.actionElements.forEach(function (element) {
	          actionWrapper.appendChild(element);
	        });

	        return actionWrapper;
	      });
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      this.onClickHandler(this);
	      this.emit('onClick');
	    }
	  }]);
	  return MessageCard;
	}(BaseCard);
	babelHelpers.defineProperty(MessageCard, "cache", new main_core.Cache.MemoryCache());

	exports.MessageCard = MessageCard;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=messagecard.bundle.js.map
