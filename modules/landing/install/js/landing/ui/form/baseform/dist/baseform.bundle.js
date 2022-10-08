this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,ui_fonts_opensans,main_core,main_core_events,landing_env) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @memberOf BX.Landing.UI.Form
	 */
	var BaseForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseForm, _EventEmitter);

	  function BaseForm() {
	    var _this;

	    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseForm).call(this, data));

	    _this.setEventNamespace('BX.Landing.UI.Form.BaseForm');

	    _this.data = _objectSpread({}, data);
	    _this.options = _this.data;
	    _this.id = Reflect.has(_this.data, 'id') ? _this.data.id : main_core.Text.getRandom();
	    _this.selector = Reflect.has(_this.data, 'selector') ? _this.data.selector : '';
	    _this.title = Reflect.has(_this.data, 'title') ? _this.data.title : '';
	    _this.label = Reflect.has(_this.data, 'label') ? _this.data.label : '';
	    _this.type = Reflect.has(_this.data, 'type') ? _this.data.type : 'content';
	    _this.code = Reflect.has(_this.data, 'code') ? _this.data.code : '';
	    _this.descriptionText = Reflect.has(_this.data, 'description') ? _this.data.description : '';

	    _this.serializeModifier = _this.options.serializeModifier || function (value) {
	      return value;
	    };

	    _this.headerCheckbox = _this.data.headerCheckbox;
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.fields = new BX.Landing.Collection.BaseCollection();
	    _this.cards = new BX.Landing.Collection.BaseCollection();
	    _this.layout = BaseForm.createLayout();
	    _this.header = BaseForm.createHeader();
	    _this.body = BaseForm.createBody();
	    _this.footer = BaseForm.createFooter();
	    main_core.Dom.append(_this.header, _this.layout);

	    if (_this.descriptionText !== null) {
	      _this.description = BaseForm.createDescription();
	      main_core.Dom.append(_this.description, _this.layout);
	    }

	    main_core.Dom.append(_this.body, _this.layout);
	    main_core.Dom.append(_this.footer, _this.layout);

	    if (main_core.Type.isString(_this.title) && _this.title !== '') {
	      main_core.Dom.append(document.createTextNode(_this.title), _this.header);
	    }

	    if (main_core.Type.isString(_this.descriptionText) && _this.descriptionText !== '') {
	      _this.description.innerHTML = _this.descriptionText;
	    }

	    if (main_core.Type.isArray(_this.data.fields) && _this.data.fields.length > 0) {
	      _this.data.fields.forEach(function (field) {
	        _this.addField(field);
	      });
	    }

	    var _Env$getInstance$getO = landing_env.Env.getInstance().getOptions(),
	        sources = _Env$getInstance$getO.sources;

	    if (main_core.Type.isPlainObject(_this.headerCheckbox) && main_core.Type.isArray(sources) && sources.length > 0) {
	      main_core.Dom.append(_this.getHeaderCheckbox(), _this.header);
	    }

	    if (_this.options.hidden === true) {
	      _this.hide();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(BaseForm, [{
	    key: "show",
	    value: function show() {
	      main_core.Dom.attr(this.getLayout(), 'hidden', null);
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.attr(this.getLayout(), 'hidden', true);
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      return this.layout;
	    }
	  }, {
	    key: "getHeaderCheckbox",
	    value: function getHeaderCheckbox() {
	      var _this2 = this;

	      return this.cache.remember('headerCheckbox', function () {
	        var checkboxId = main_core.Text.getRandom();
	        var _this2$headerCheckbox = _this2.headerCheckbox,
	            text = _this2$headerCheckbox.text,
	            help = _this2$headerCheckbox.help,
	            state = _this2$headerCheckbox.state,
	            onChange = _this2$headerCheckbox.onChange;
	        var input = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"checkbox\" id=\"", "\" class=\"landing-ui-form-header-checkbox-input\">\n\t\t\t"])), checkboxId);
	        var label = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label for=\"", "\" class=\"landing-ui-form-header-checkbox-label\">", "</label>\n\t\t\t"])), checkboxId, text);
	        var layout = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-checkbox\">", "", "</div>\n\t\t\t"])), input, label);

	        if (main_core.Text.toBoolean(state)) {
	          input.setAttribute('checked', true);
	        }

	        if (main_core.Type.isFunction(onChange)) {
	          main_core.Event.bind(input, 'change', function () {
	            onChange({
	              state: input.checked === true,
	              form: _this2
	            });
	          });
	        }

	        if (main_core.Type.isString(help) && help !== '') {
	          var helpButton = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a href=\"", "\" class=\"landing-ui-form-header-checkbox-help\" target=\"_blank\"> </a>\n\t\t\t\t"])), help);
	          main_core.Dom.append(helpButton, layout);
	        }

	        return layout;
	      });
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.header;
	    }
	  }, {
	    key: "getBody",
	    value: function getBody() {
	      return this.body;
	    }
	  }, {
	    key: "getFooter",
	    value: function getFooter() {
	      return this.footer;
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.layout;
	    }
	  }, {
	    key: "addField",
	    value: function addField(field) {
	      if (main_core.Type.isObject(field)) {
	        this.fields.add(field);
	        main_core.Dom.append(field.getNode(), this.getBody());
	      }
	    }
	  }, {
	    key: "addCard",
	    value: function addCard(card) {
	      var _this3 = this;

	      if (main_core.Type.isObject(card)) {
	        this.cards.add(card);
	        card.fields.forEach(function (field) {
	          _this3.fields.add(field);
	        });
	        main_core.Dom.append(card.getNode(), this.getBody());
	      }
	    }
	  }, {
	    key: "removeCard",
	    value: function removeCard(card) {
	      var _this4 = this;

	      if (main_core.Type.isObject(card)) {
	        card.fields.forEach(function (field) {
	          _this4.fields.remove(field);
	        });
	        this.cards.remove(card);
	        main_core.Dom.remove(card.layout);
	      }
	    }
	  }, {
	    key: "replaceCard",
	    value: function replaceCard(oldCard, newCard) {
	      this.removeCard(oldCard);
	      this.addCard(newCard);
	    }
	  }, {
	    key: "replaceField",
	    value: function replaceField(oldField, newField) {
	      if (main_core.Type.isObject(oldField) && main_core.Type.isObject(newField)) {
	        main_core.Dom.replace(oldField.getNode(), newField.getNode());
	        this.fields.remove(oldField);
	        this.fields.add(newField);
	      }
	    }
	  }, {
	    key: "isCheckboxChecked",
	    value: function isCheckboxChecked() {
	      var checkbox = this.header.querySelector('input');
	      return main_core.Type.isDomNode(checkbox) && checkbox.checked;
	    }
	  }, {
	    key: "clone",
	    value: function clone(options) {
	      var instance = new this.constructor(main_core.Runtime.clone(options || this.data));
	      this.fields.forEach(function (field) {
	        if (field instanceof BX.Landing.UI.Field.Date) {
	          var newFieldData = main_core.Runtime.clone(field.data);
	          newFieldData.selector = instance.selector;
	          instance.addField(field.clone(newFieldData));
	          return;
	        }

	        instance.addField(field.clone());
	      });
	      return instance;
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return this.serializeModifier(this.fields.reduce(function (acc, field) {
	        if (main_core.Type.isFunction(field.getValue)) {
	          acc[field.selector] = field.getValue();
	        }

	        return acc;
	      }, {}));
	    }
	  }, {
	    key: "removeField",
	    value: function removeField(field) {
	      this.fields.remove(field);
	      main_core.Dom.remove(field.layout);
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      main_core.Dom.addClass(this.getLayout(), 'landing-ui-disabled');
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      main_core.Dom.removeClass(this.getLayout(), 'landing-ui-disabled');
	    }
	  }], [{
	    key: "createLayout",
	    value: function createLayout() {
	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form\"></div>"])));
	    }
	  }, {
	    key: "createHeader",
	    value: function createHeader() {
	      return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-header\"></div>"])));
	    }
	  }, {
	    key: "createDescription",
	    value: function createDescription() {
	      return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-description\"></div>"])));
	    }
	  }, {
	    key: "createBody",
	    value: function createBody() {
	      return main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-body\"></div>"])));
	    }
	  }, {
	    key: "createFooter",
	    value: function createFooter() {
	      return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-footer\"></div>"])));
	    }
	  }]);
	  return BaseForm;
	}(main_core_events.EventEmitter);

	exports.BaseForm = BaseForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX,BX,BX.Event,BX.Landing));
//# sourceMappingURL=baseform.bundle.js.map
