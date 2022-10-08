this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,ui_fonts_opensans,main_core,landing_ui_field_basefield,ui_buttons,landing_ui_component_internal,landing_loc) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	/**
	 * @memberOf BX.Landing.UI.Field
	 */
	var RadioButtonField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(RadioButtonField, _BaseField);

	  function RadioButtonField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RadioButtonField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RadioButtonField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.RadioButtonField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-radio-button');
	    main_core.Dom.replace(_this.input, _this.getLayout());

	    if (main_core.Type.isBoolean(_this.options.selectable)) {
	      _this.setSelectable(_this.options.selectable);
	    } else {
	      _this.setSelectable(true);
	    }

	    _this.options.items.forEach(function (item) {
	      _this.appendItem(item);
	    });

	    if (_this.options.value) {
	      _this.setValue(_this.options.value, true);
	    } else {
	      _this.setValue(_this.options.items[0].id, true);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RadioButtonField, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('remember', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-radio-button-layout\" data-selector=\"", "\"></div>\n\t\t\t"])), _this2.selector);
	      });
	    }
	  }, {
	    key: "appendItem",
	    value: function appendItem(options) {
	      var element = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass=\"landing-ui-field-radio-button-item", "\" \n\t\t\t\tdata-value=\"", "\"\n\t\t\t\tonclick=\"", "\"\n\t\t\t>\n\t\t\t\t<div class=\"landing-ui-field-radio-button-item-icon ", "\"></div>\n\t\t\t\t<div class=\"landing-ui-field-radio-button-item-text\">\n\t\t\t\t\t<span>", "</span>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), options.disabled ? ' landing-ui-disabled' : '', options.id, this.onItemClick.bind(this, options), options.icon, options.title, options.soon ? this.createSoonLabel() : '');

	      if (main_core.Type.isPlainObject(options.button)) {
	        var button = new ui_buttons.Button({
	          color: ui_buttons.Button.Color.PRIMARY,
	          size: ui_buttons.Button.Size.EXTRA_SMALL,
	          text: options.button.text,
	          round: true,
	          events: {
	            click: options.button.onClick
	          }
	        });
	        button.renderTo(element);
	      }

	      main_core.Dom.append(element, this.getLayout());
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(item, event) {
	      event.preventDefault();

	      if (this.options.selectable !== false) {
	        babelHelpers.toConsumableArray(this.getLayout().children).forEach(function (element) {
	          main_core.Dom.removeClass(element, 'landing-ui-field-radio-button-item-active');
	        });
	        main_core.Dom.addClass(event.currentTarget, 'landing-ui-field-radio-button-item-active');
	      }

	      this.emit('onChange', {
	        item: item
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var activeElement = babelHelpers.toConsumableArray(this.getLayout().children).find(function (item) {
	        return main_core.Dom.hasClass(item, 'landing-ui-field-radio-button-item-active');
	      });

	      if (activeElement) {
	        return main_core.Dom.attr(activeElement, 'data-value');
	      }

	      return '';
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value, preventEvent) {
	      var items = babelHelpers.toConsumableArray(this.getLayout().children);
	      items.forEach(function (element) {
	        main_core.Dom.removeClass(element, 'landing-ui-field-radio-button-item-active');
	      });
	      var item = items.find(function (currentItem) {
	        return String(main_core.Dom.attr(currentItem, 'data-value')) === String(value);
	      });

	      if (item) {
	        if (this.options.selectable !== false) {
	          main_core.Dom.addClass(item, 'landing-ui-field-radio-button-item-active');
	        }

	        if (!preventEvent) {
	          this.emit('onChange', {
	            item: item
	          });
	        }
	      }
	    }
	  }, {
	    key: "getSelectable",
	    value: function getSelectable() {
	      return main_core.Text.toBoolean(this.cache.get('selectable'));
	    }
	  }, {
	    key: "setSelectable",
	    value: function setSelectable(value) {
	      this.cache.set('selectable', main_core.Text.toBoolean(value));
	    }
	  }, {
	    key: "isSelectable",
	    value: function isSelectable() {
	      return main_core.Text.toBoolean(this.cache.get('selectable'));
	    }
	  }, {
	    key: "createSoonLabel",
	    value: function createSoonLabel() {
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-radio-button-item-soon-label\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), landing_loc.Loc.getMessage('LANDING_UI_BASE_PRESET_PANEL_SOON_LABEL'));
	    }
	  }]);
	  return RadioButtonField;
	}(landing_ui_field_basefield.BaseField);

	exports.RadioButtonField = RadioButtonField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX,BX,BX.Landing.UI.Field,BX.UI,BX.Landing.UI.Component,BX.Landing));
//# sourceMappingURL=radiobuttonfield.bundle.js.map
