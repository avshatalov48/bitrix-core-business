this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports, main_core, landing_ui_form_baseform) {
	'use strict';

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-right\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-form-header-remove-button\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-left\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-title\">", "</div>\n\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-form-header-drag-button landing-ui-drag\"></div>\n\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var depthKey = Symbol('depth');
	var onHeaderClick = Symbol('onHeaderClick');
	var onTextChange = Symbol('onTextChange');
	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var MenuItemForm =
	/*#__PURE__*/
	function (_BaseForm) {
	  babelHelpers.inherits(MenuItemForm, _BaseForm);

	  function MenuItemForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MenuItemForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuItemForm).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Form.MenuItemForm');

	    _this.cache = new main_core.Cache.MemoryCache();
	    _this[onHeaderClick] = _this[onHeaderClick].bind(babelHelpers.assertThisInitialized(_this));
	    _this[onTextChange] = _this[onTextChange].bind(babelHelpers.assertThisInitialized(_this));
	    _this.onRemoveButtonClick = _this.onRemoveButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-menuitem');
	    main_core.Dom.append(_this.getHeaderLeftLayout(), _this.header);
	    main_core.Dom.append(_this.getHeaderRightLayout(), _this.header);

	    _this.setDepth(options.depth);

	    var _this$fields = babelHelpers.slicedToArray(_this.fields, 1),
	        firstField = _this$fields[0];

	    if (firstField) {
	      var _firstField$getValue = firstField.getValue(),
	          text = _firstField$getValue.text;

	      _this.setTitle(text);

	      main_core.Event.bind(firstField.input.input, 'input', _this[onTextChange]);
	    }

	    main_core.Event.bind(_this.getHeader(), 'click', _this[onHeaderClick]);
	    return _this;
	  }

	  babelHelpers.createClass(MenuItemForm, [{
	    key: onHeaderClick,
	    value: function value(event) {
	      event.preventDefault();

	      if (this.isFormShown()) {
	        this.hideForm();
	      } else {
	        this.showForm();
	      }
	    }
	  }, {
	    key: onTextChange,
	    value: function value() {
	      var _this$fields2 = babelHelpers.slicedToArray(this.fields, 1),
	          firstField = _this$fields2[0];

	      if (firstField) {
	        var _firstField$getValue2 = firstField.getValue(),
	            text = _firstField$getValue2.text;

	        this.setTitle(text);
	      }
	    }
	  }, {
	    key: "onRemoveButtonClick",
	    value: function onRemoveButtonClick() {
	      this.emit('remove', {
	        form: this
	      });
	      main_core.Dom.remove(this.layout);
	    }
	  }, {
	    key: "showForm",
	    value: function showForm() {
	      main_core.Dom.addClass(this.layout, 'landing-ui-form-menuitem-open');
	      main_core.Dom.style(this.body, 'display', 'block');
	    }
	  }, {
	    key: "hideForm",
	    value: function hideForm() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-form-menuitem-open');
	      main_core.Dom.style(this.body, 'display', null);
	    }
	  }, {
	    key: "isFormShown",
	    value: function isFormShown() {
	      return main_core.Dom.style(this.body, 'display') !== 'none';
	    }
	  }, {
	    key: "getDragButton",
	    value: function getDragButton() {
	      return this.cache.remember('dragButton', function () {
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      var _this2 = this;

	      return this.cache.remember('titleLayout', function () {
	        return main_core.Tag.render(_templateObject2(), main_core.Text.encode(_this2.title));
	      });
	    }
	  }, {
	    key: "getHeaderLeftLayout",
	    value: function getHeaderLeftLayout() {
	      var _this3 = this;

	      return this.cache.remember('headerLeftLayout', function () {
	        return main_core.Tag.render(_templateObject3(), _this3.getDragButton(), _this3.getTitleLayout());
	      });
	    }
	  }, {
	    key: "getRemoveButton",
	    value: function getRemoveButton() {
	      var _this4 = this;

	      return this.cache.remember('removeButton', function () {
	        var button = main_core.Tag.render(_templateObject4());
	        main_core.Event.bind(button, 'click', _this4.onRemoveButtonClick);
	        return button;
	      });
	    }
	  }, {
	    key: "getHeaderRightLayout",
	    value: function getHeaderRightLayout() {
	      var _this5 = this;

	      return this.cache.remember('headerRightLayout', function () {
	        return main_core.Tag.render(_templateObject5(), _this5.getRemoveButton());
	      });
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isString(title) || main_core.Type.isNumber(title)) {
	        this.title = title;
	        this.getTitleLayout().innerText = main_core.Text.decode(title);
	      }
	    }
	  }, {
	    key: "setDepth",
	    value: function setDepth(depth) {
	      var offset = 20;
	      this[depthKey] = main_core.Text.toNumber(depth);
	      main_core.Dom.style(this.layout, 'margin-left', "".concat(depth * offset, "px"));
	      main_core.Dom.attr(this.layout, 'data-depth', depth);
	    }
	  }, {
	    key: "getDepth",
	    value: function getDepth() {
	      return main_core.Text.toNumber(main_core.Dom.attr(this.layout, 'data-depth'));
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      var _this$fields3 = babelHelpers.slicedToArray(this.fields, 1),
	          firstField = _this$fields3[0];

	      return firstField.getValue();
	    }
	  }]);
	  return MenuItemForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.MenuItemForm = MenuItemForm;

}(this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}, BX, BX.Landing.UI.Form));
//# sourceMappingURL=menuitemform.bundle.js.map
