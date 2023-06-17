this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,landing_ui_field_defaultvaluefield,landing_ui_card_headercard,landing_loc,main_core,landing_ui_card_messagecard) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var DefaultValues = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(DefaultValues, _ContentWrapper);
	  function DefaultValues(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, DefaultValues);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultValues).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.DefaultValues');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'defaultValueMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_DESCRIPTION'),
	      restoreState: true
	    });
	    var fieldsForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      fields: [new landing_ui_field_defaultvaluefield.DefaultValueField({
	        selector: 'presetFields',
	        isLeadEnabled: _this.options.isLeadEnabled,
	        personalizationVariables: _this.getPersonalizationVariables(),
	        formOptions: _objectSpread({}, _this.options.formOptions),
	        crmFields: _objectSpread({}, _this.options.crmFields),
	        dictionary: _objectSpread({}, _this.options.dictionary),
	        items: babelHelpers.toConsumableArray(_this.options.formOptions.presetFields)
	      })]
	    });
	    if (!message.isShown()) {
	      fieldsForm.setOffsetTop(-36);
	    }
	    message.subscribe('onClose', function () {
	      fieldsForm.setOffsetTop(-36);
	    });
	    _this.addItem(header);
	    _this.addItem(message);
	    _this.addItem(fieldsForm);
	    return _this;
	  }
	  babelHelpers.createClass(DefaultValues, [{
	    key: "getPersonalizationVariables",
	    value: function getPersonalizationVariables() {
	      var _this2 = this;
	      return this.cache.remember('personalizationVariables', function () {
	        var properties = _this2.options.dictionary.properties;
	        if (main_core.Type.isPlainObject(properties) && main_core.Type.isArrayFilled(properties.list)) {
	          return properties.list.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          }).concat([{
	            delimiter: true
	          }]).concat(_this2.options.formOptions.data.fields.filter(function (field) {
	            return ['phone', 'email', 'date', 'datetime', 'double', 'integer', 'lastname', 'name', 'secondname', 'string', 'text', 'money'].includes(field.type);
	          }).map(function (field) {
	            return {
	              name: field.label,
	              value: "%".concat(field.name.toLowerCase(), "%")
	            };
	          }));
	        }
	        return [];
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var layout = babelHelpers.get(babelHelpers.getPrototypeOf(DefaultValues.prototype), "getLayout", this).call(this);
	      main_core.Dom.addClass(layout, 'landing-ui-default-fields-values');
	      return layout;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return DefaultValues;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = DefaultValues;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Panel,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Landing.UI.Card,BX.Landing,BX,BX.Landing.UI.Card));
//# sourceMappingURL=default-values.bundle.js.map
