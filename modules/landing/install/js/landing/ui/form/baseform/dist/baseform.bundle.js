this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports, main_core, landing_env) {
	'use strict';

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-footer\"></div>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-body\"></div>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-description\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-header\"></div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form\"></div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a href=\"", "\" class=\"landing-ui-form-header-checkbox-help\" target=\"_blank\"> </a>\n\t\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-checkbox\">", "", "</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label for=\"", "\" class=\"landing-ui-form-header-checkbox-label\">", "</label>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"checkbox\" id=\"", "\" class=\"landing-ui-form-header-checkbox-input\">\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var BaseForm =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(BaseForm, _Event$EventEmitter);

	  function BaseForm() {
	    var _this;

	    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseForm).call(this, data));

	    _this.setEventNamespace('BX.Landing.UI.Form.BaseForm');

	    _this.data = babelHelpers.objectSpread({}, data);
	    _this.id = Reflect.has(_this.data, 'id') ? _this.data.id : main_core.Text.getRandom();
	    _this.selector = Reflect.has(_this.data, 'selector') ? _this.data.selector : '';
	    _this.title = Reflect.has(_this.data, 'title') ? _this.data.title : '';
	    _this.label = Reflect.has(_this.data, 'label') ? _this.data.label : '';
	    _this.type = Reflect.has(_this.data, 'type') ? _this.data.type : 'content';
	    _this.code = Reflect.has(_this.data, 'code') ? _this.data.code : '';
	    _this.descriptionText = Reflect.has(_this.data, 'description') ? _this.data.description : '';
	    _this.headerCheckbox = _this.data.headerCheckbox;
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.fields = new BX.Landing.Collection.BaseCollection();
	    _this.cards = new BX.Landing.Collection.BaseCollection();
	    _this.layout = BaseForm.createLayout();
	    _this.description = BaseForm.createDescription();
	    _this.header = BaseForm.createHeader();
	    _this.body = BaseForm.createBody();
	    _this.footer = BaseForm.createFooter();
	    main_core.Dom.append(_this.header, _this.layout);
	    main_core.Dom.append(_this.description, _this.layout);
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

	    return _this;
	  }

	  babelHelpers.createClass(BaseForm, [{
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
	        var input = main_core.Tag.render(_templateObject(), checkboxId);
	        var label = main_core.Tag.render(_templateObject2(), checkboxId, text);
	        var layout = main_core.Tag.render(_templateObject3(), input, label);

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
	          var helpButton = main_core.Tag.render(_templateObject4(), help);
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
	      return this.fields.reduce(function (acc, field) {
	        acc[field.selector] = field.getValue();
	        return acc;
	      }, {});
	    }
	  }, {
	    key: "removeField",
	    value: function removeField(field) {
	      this.fields.remove(field);
	      main_core.Dom.remove(field.layout);
	    }
	  }], [{
	    key: "createLayout",
	    value: function createLayout() {
	      return main_core.Tag.render(_templateObject5());
	    }
	  }, {
	    key: "createHeader",
	    value: function createHeader() {
	      return main_core.Tag.render(_templateObject6());
	    }
	  }, {
	    key: "createDescription",
	    value: function createDescription() {
	      return main_core.Tag.render(_templateObject7());
	    }
	  }, {
	    key: "createBody",
	    value: function createBody() {
	      return main_core.Tag.render(_templateObject8());
	    }
	  }, {
	    key: "createFooter",
	    value: function createFooter() {
	      return main_core.Tag.render(_templateObject9());
	    }
	  }]);
	  return BaseForm;
	}(main_core.Event.EventEmitter);

	exports.BaseForm = BaseForm;

}(this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}, BX, BX.Landing));
//# sourceMappingURL=baseform.bundle.js.map
