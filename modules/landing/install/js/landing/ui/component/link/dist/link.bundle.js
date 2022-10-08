this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	var _templateObject;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Colors = function Colors() {
	  babelHelpers.classCallCheck(this, Colors);
	};

	babelHelpers.defineProperty(Colors, "Primary", 'primary');
	babelHelpers.defineProperty(Colors, "Grey", 'grey');
	var defaultOptions = {
	  text: '',
	  color: Colors.Primary,
	  attrs: {},
	  style: {}
	};
	var Link = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Link, _EventEmitter);

	  function Link(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Link);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Link).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Component.Link');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.options = _objectSpread(_objectSpread({}, defaultOptions), options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(Link, [{
	    key: "getTag",
	    value: function getTag() {
	      var _this2 = this;

	      return this.cache.remember('tag', function () {
	        return main_core.Type.isStringFilled(_this2.options.href) ? 'a' : 'span';
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this3 = this;

	      return this.cache.remember('layout', function () {
	        var tag = _this3.getTag();

	        var element = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<", "\n\t\t\t\t\tclass=\"landing-ui-component-link landing-ui-component-link-color-", "\"\n\t\t\t\t\tonclick=\"", "\">", "</", ">\n\t\t\t"])), tag, _this3.options.color, _this3.onClick.bind(_this3), _this3.options.text, tag);

	        if (tag === 'a') {
	          main_core.Dom.attr(element, 'href', _this3.options.href);
	        }

	        if (tag === 'a' && main_core.Type.isStringFilled(_this3.options.target)) {
	          main_core.Dom.attr(element, 'target', _this3.options.target);
	        }

	        main_core.Dom.attr(element, _this3.options.attrs);
	        main_core.Dom.style(element, _this3.options.style);
	        return element;
	      });
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(event) {
	      if (this.getTag() === 'span') {
	        event.preventDefault();
	      }

	      this.emit('onClick');
	    }
	  }]);
	  return Link;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Link, "Colors", Colors);

	exports.Link = Link;

}((this.BX.Landing.UI.Component = this.BX.Landing.UI.Component || {}),BX,BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=link.bundle.js.map
