this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,landing_ui_field_basefield,main_core,landing_loc) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;
	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var PresetField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(PresetField, _BaseField);

	  function PresetField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PresetField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PresetField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.PresetField');

	    _this.subscribeFromOptions(options.events);

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-preset');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_PRESET_FIELD_TITLE'));

	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onLinkClick = _this.onLinkClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.replace(_this.layout, _this.getLayout());
	    _this.layout = _this.getLayout();
	    return _this;
	  }

	  babelHelpers.createClass(PresetField, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-preset-layout\">\n\t\t\t\t\t<div class=\"landing-ui-field-preset-left\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-field-preset-right\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this2.getIcon(), _this2.header, _this2.getLink());
	      });
	    }
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      return this.cache.remember('icon', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-field-preset-icon landing-ui-field-preset-icon-default\"></span>"])));
	      });
	    }
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      main_core.Dom.style(this.getIcon(), 'background-image', "url(".concat(icon, ")"));
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      var _this3 = this;

	      return this.cache.remember('link', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-preset-link\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.onLinkClick, landing_loc.Loc.getMessage('LANDING_PRESET_DEFAULT_CASE_TITLE'));
	      });
	    }
	  }, {
	    key: "setLinkText",
	    value: function setLinkText(text) {
	      this.getLink().textContent = text;
	    }
	  }, {
	    key: "onLinkClick",
	    value: function onLinkClick(event) {
	      event.preventDefault();
	      this.emit('onClick');
	    }
	  }]);
	  return PresetField;
	}(landing_ui_field_basefield.BaseField);

	exports.PresetField = PresetField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Field,BX,BX.Landing));
//# sourceMappingURL=presetfield.bundle.js.map
