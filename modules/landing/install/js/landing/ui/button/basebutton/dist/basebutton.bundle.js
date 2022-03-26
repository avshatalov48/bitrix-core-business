this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	var defaultOptions = {
	  id: main_core.Text.getRandom(),
	  text: '',
	  html: '',
	  onClick: function onClick() {},
	  attrs: {},
	  disabled: false,
	  className: null
	};

	var _templateObject, _templateObject2;
	/**
	 * @memberOf BX.Landing.UI.Button
	 */

	var BaseButton = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseButton, _EventEmitter);

	  function BaseButton(id, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BaseButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseButton).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Button.BaseButton');

	    var compatOptions = function () {
	      if (main_core.Type.isPlainObject(options)) {
	        return options;
	      }

	      if (main_core.Type.isPlainObject(id)) {
	        return id;
	      }

	      return {};
	    }();

	    var compatId = function () {
	      if (main_core.Type.isStringFilled(id)) {
	        return id;
	      }

	      if (main_core.Type.isStringFilled(compatOptions.id)) {
	        return compatOptions.id;
	      }

	      return main_core.Text.getRandom();
	    }();

	    _this.options = babelHelpers.objectSpread({}, defaultOptions, compatOptions);
	    _this.id = compatId;
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.layout = _this.getLayout();

	    if (main_core.Type.isStringFilled(_this.options.html)) {
	      _this.setHtml(_this.options.html);
	    } else {
	      _this.setText(_this.options.text);
	    }

	    if (main_core.Type.isFunction(_this.options.onClick)) {
	      main_core.Event.bind(_this.getLayout(), 'click', _this.options.onClick);
	    }

	    if (main_core.Type.isPlainObject(_this.options.attrs)) {
	      main_core.Dom.attr(_this.getLayout(), _this.options.attrs);
	    }

	    if (main_core.Type.isArray(_this.options.className) || main_core.Type.isStringFilled(_this.options.className)) {
	      main_core.Dom.addClass(_this.layout, _this.options.className);
	    }

	    if (_this.options.active) {
	      _this.activate();
	    }

	    if (_this.options.disabled) {
	      _this.disable();
	    }

	    main_core.Event.bind(_this.getLayout(), 'click', function (event) {
	      event.preventDefault();

	      _this.emit('onClick');
	    });
	    return _this;
	  }

	  babelHelpers.createClass(BaseButton, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button \n\t\t\t\t\tclass=\"landing-ui-button\" \n\t\t\t\t\ttype=\"button\"\n\t\t\t\t\tdata-id=\"", "\"\n\t\t\t\t>", "</button>\n\t\t\t"])), _this2.id, _this2.getTextLayout());
	      });
	    }
	  }, {
	    key: "getTextLayout",
	    value: function getTextLayout() {
	      return this.cache.remember('textLayout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-button-text\"></span>"])));
	      });
	    }
	  }, {
	    key: "setHtml",
	    value: function setHtml(html) {
	      this.getTextLayout().innerHTML = html;
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      this.getTextLayout().innerHTML = main_core.Text.encode(text);
	    }
	    /**
	     * @deprecated
	     */

	  }, {
	    key: "on",
	    value: function on(event, handler, context) {
	      if (main_core.Type.isString(event) && main_core.Type.isFunction(handler)) {
	        main_core.Event.bind(this.layout, event, BX.proxy(handler, context));
	      }
	    }
	  }, {
	    key: "setAttributes",
	    value: function setAttributes(attrs) {
	      main_core.Dom.attr(this.layout, attrs);
	    }
	  }, {
	    key: "setAttribute",
	    value: function setAttribute(key, value) {
	      main_core.Dom.attr(this.layout, key, value);
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-disabled');
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-disabled');
	      main_core.Dom.attr(this.layout, 'disabled', null);
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled() {
	      return !main_core.Dom.hasClass(this.layout, 'landing-ui-disabled');
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      return BX.Landing.Utils.show(this.layout);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      return BX.Landing.Utils.hide(this.layout);
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-active');
	    }
	  }, {
	    key: "deactivate",
	    value: function deactivate() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-active');
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return main_core.Dom.hasClass(this.layout, 'landing-ui-active');
	    }
	  }]);
	  return BaseButton;
	}(main_core_events.EventEmitter);

	exports.BaseButton = BaseButton;

}((this.BX.Landing.UI.Button = this.BX.Landing.UI.Button || {}),BX.Event,BX));
//# sourceMappingURL=basebutton.bundle.js.map
