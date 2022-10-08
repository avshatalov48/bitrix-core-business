this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Landing.UI.Card
	 */

	var BaseCard = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseCard, _EventEmitter);

	  function BaseCard() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseCard).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Card.BaseCard');

	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.data = _objectSpread({}, options);
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
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card\">\n\t\t\t\t\t<div class=\"landing-ui-card-header-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.getHeader(), _this2.getBody());
	      });
	    }
	  }, {
	    key: "getRemoveButton",
	    value: function getRemoveButton() {
	      return this.cache.remember('remove', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-block-remove\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-header\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getBody",
	    value: function getBody() {
	      return this.cache.remember('body', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-body\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "addWarning",
	    value: function addWarning(warning) {
	      main_core.Dom.append(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-card-body-warning\">", "</div>\n\t\t\t"])), warning), this.getBody());
	      main_core.Dom.addClass(this.getBody(), '--warning');
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

	exports.BaseCard = BaseCard;

}((this.BX.Landing.UI.Card = this.BX.Landing.UI.Card || {}),BX,BX.Event));
//# sourceMappingURL=basecard.bundle.js.map
