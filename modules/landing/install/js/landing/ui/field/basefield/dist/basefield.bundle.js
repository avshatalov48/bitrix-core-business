this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-input\">", "</div>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-description\">\n\t\t\t\t<span class=\"fa fa-info-circle\"> </span> ", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-header\"></div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var BaseField = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseField, _EventEmitter);
	  babelHelpers.createClass(BaseField, null, [{
	    key: "createLayout",
	    value: function createLayout() {
	      return main_core.Tag.render(_templateObject());
	    }
	  }, {
	    key: "createHeader",
	    value: function createHeader() {
	      return main_core.Tag.render(_templateObject2());
	    }
	  }, {
	    key: "createDescription",
	    value: function createDescription(text) {
	      return main_core.Tag.render(_templateObject3(), text);
	    }
	  }]);

	  function BaseField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.data = babelHelpers.objectSpread({}, options);
	    _this.options = _this.data;
	    _this.id = Reflect.has(_this.data, 'id') ? _this.data.id : main_core.Text.getRandom();
	    _this.selector = Reflect.has(_this.data, 'selector') ? _this.data.selector : main_core.Text.getRandom();
	    _this.content = Reflect.has(_this.data, 'content') ? _this.data.content : '';
	    _this.title = main_core.Type.isString(_this.data.title) ? _this.data.title : '';
	    _this.placeholder = main_core.Type.isString(_this.data.placeholder) ? _this.data.placeholder : '';
	    _this.className = main_core.Type.isString(_this.data.className) ? _this.data.className : '';
	    _this.descriptionText = main_core.Type.isString(_this.data.description) ? _this.data.description : '';
	    _this.description = null;
	    _this.attribute = main_core.Type.isString(_this.data.attribute) ? _this.data.attribute : '';
	    _this.hidden = main_core.Text.toBoolean(_this.data.hidden);
	    _this.property = main_core.Type.isString(_this.data.property) ? _this.data.property : '';
	    _this.style = Reflect.has(_this.data, 'style') ? _this.data.style : '';
	    _this.cache = new main_core.Cache.MemoryCache();
	    var onValueChange = _this.data.onValueChange;
	    _this.onValueChangeHandler = main_core.Type.isFunction(onValueChange) ? onValueChange : function () {};
	    _this.onPaste = _this.onPaste.bind(babelHelpers.assertThisInitialized(_this));
	    _this.layout = BaseField.createLayout();
	    _this.header = BaseField.createHeader();
	    _this.input = _this.createInput();

	    _this.setTitle(_this.title);

	    main_core.Dom.append(_this.header, _this.layout);
	    main_core.Dom.append(_this.input, _this.layout);
	    main_core.Dom.attr(_this.layout, 'data-selector', _this.selector);
	    main_core.Dom.attr(_this.input, 'data-placeholder', _this.placeholder);

	    if (main_core.Type.isArrayLike(_this.className)) {
	      main_core.Dom.addClass(_this.layout, _this.className);
	    }

	    if (main_core.Type.isString(_this.descriptionText) && _this.descriptionText !== '') {
	      _this.description = BaseField.createDescription(_this.descriptionText);
	      main_core.Dom.append(_this.description, _this.layout);
	    }

	    if (_this.data.disabled === true) {
	      _this.disable();
	    }

	    main_core.Event.bind(_this.input, 'paste', _this.onPaste);

	    _this.init();

	    return _this;
	  }

	  babelHelpers.createClass(BaseField, [{
	    key: "setTitle",
	    value: function setTitle(title) {
	      this.header.innerHTML = main_core.Text.encode(title);
	    }
	  }, {
	    key: "createInput",
	    value: function createInput() {
	      return main_core.Tag.render(_templateObject4(), this.content);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "init",
	    value: function init() {}
	  }, {
	    key: "getContext",
	    value: function getContext() {
	      if (this.input.ownerDocument) {
	        return this.input.ownerDocument.defaultView;
	      }

	      return window;
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onPaste",
	    value: function onPaste(event) {
	      event.preventDefault();
	      event.stopPropagation();

	      if (event.clipboardData && event.clipboardData.getData) {
	        var sourceText = event.clipboardData.getData('text/plain');
	        var encodedText = BX.Text.encode(sourceText);
	        var formattedHtml = encodedText.replace(new RegExp('\n', 'g'), '<br>');
	        this.getContext().document.execCommand('insertHTML', false, formattedHtml);
	      } else {
	        // ie11
	        var text = window.clipboardData.getData('text');
	        this.getContext().document.execCommand('paste', true, BX.Text.encode(text));
	      }
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.layout;
	    }
	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      var _this2 = this;

	      var content = function () {
	        if (main_core.Type.isNil(_this2.content)) {
	          return '';
	        }

	        if (main_core.Type.isString(_this2.content)) {
	          return _this2.content.trim();
	        }

	        return _this2.content;
	      }();

	      return content !== this.getValue();
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.input.innerHTML.trim();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue() {
	      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var preparedValue = this.textOnly ? main_core.Text.encode(value) : value;
	      this.input.innerHTML = preparedValue.toString().trim();
	      this.onValueChangeHandler(this);
	      var event = new main_core.Event.BaseEvent({
	        data: {
	          value: this.getValue()
	        },
	        compatData: [this.getValue()]
	      });
	      this.emit('change', event);
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      main_core.Dom.attr(this.layout, 'disabled', null);
	      main_core.Dom.removeClass(this.layout, 'landing-ui-disabled');
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      main_core.Dom.attr(this.layout, 'disabled', true);
	      main_core.Dom.addClass(this.layout, 'landing-ui-disabled');
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "reset",
	    value: function reset() {}
	  }, {
	    key: "clone",
	    value: function clone(data) {
	      return new this.constructor(main_core.Runtime.clone(data || this.data));
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      return this.layout;
	    }
	  }, {
	    key: "setLayoutClass",
	    value: function setLayoutClass(className) {
	      main_core.Dom.addClass(this.layout, className);
	    }
	  }]);
	  return BaseField;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(BaseField, "currentField", null);

	exports.BaseField = BaseField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=basefield.bundle.js.map
