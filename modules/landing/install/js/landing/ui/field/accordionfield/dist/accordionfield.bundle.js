this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,landing_ui_field_basefield,main_core,landing_ui_field_smallswitch) {
	'use strict';

	var _templateObject, _templateObject2;
	var AccordionField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(AccordionField, _BaseField);

	  function AccordionField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, AccordionField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AccordionField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.AccordionField');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-accordion');
	    main_core.Dom.replace(_this.input, _this.getItemsContainer());
	    _this.items = [];
	    options.items.forEach(function (itemOptions) {
	      _this.addItem(itemOptions);
	    });
	    return _this;
	  }

	  babelHelpers.createClass(AccordionField, [{
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      return this.cache.remember('itemsContainer', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-accordion-items-container\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "onTitleClick",
	    value: function onTitleClick(event) {
	      event.preventDefault();
	      var item = event.currentTarget.closest('.landing-ui-field-accordion-item');

	      if (main_core.Dom.hasClass(item, 'landing-ui-field-accordion-item-active')) {
	        main_core.Dom.toggleClass(item, 'landing-ui-field-accordion-item-opened');
	      }
	    }
	  }, {
	    key: "createItem",
	    value: function createItem(options) {
	      var switcher = new landing_ui_field_smallswitch.SmallSwitch({
	        value: main_core.Text.toBoolean(options.checked),
	        onValueChange: function onValueChange() {
	          var item = switcher.layout.closest('.landing-ui-field-accordion-item');

	          if (switcher.getValue()) {
	            main_core.Dom.addClass(item, 'landing-ui-field-accordion-item-active');
	          } else {
	            main_core.Dom.removeClass(item, 'landing-ui-field-accordion-item-active');
	            main_core.Dom.removeClass(item, 'landing-ui-field-accordion-item-opened');
	          }
	        }
	      });
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-accordion-item landing-ui-field-accordion-item-active\" data-id=\"", "\">\n\t\t\t\t<div class=\"landing-ui-field-accordion-item-header\">\n\t\t\t\t\t<div class=\"landing-ui-field-accordion-item-header-icon\" style=\"background-image: url(", ")\"></div>\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass=\"landing-ui-field-accordion-item-header-title\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>", "</div>\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass=\"landing-ui-field-accordion-item-header-switch\"\n\t\t\t\t\t\tstyle=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"landing-ui-field-accordion-item-header-switch-link\"></div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-field-accordion-item-body\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), options.id, options.icon, this.onTitleClick.bind(this), options.title, options.switcher === false ? 'display: none;' : '', switcher.layout, options.content);
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(options) {
	      var renderedItem = this.createItem(options);
	      main_core.Dom.append(renderedItem, this.getItemsContainer());
	    }
	  }]);
	  return AccordionField;
	}(landing_ui_field_basefield.BaseField);

	exports.AccordionField = AccordionField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Field,BX,BX.Landing.UI.Field));
//# sourceMappingURL=accordionfield.bundle.js.map
