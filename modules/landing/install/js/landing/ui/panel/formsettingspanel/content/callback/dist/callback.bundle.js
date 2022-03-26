this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,main_core,main_core_events,landing_loc,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,landing_ui_card_headercard,landing_ui_field_textfield,landing_ui_card_messagecard) {
	'use strict';

	var Callback = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Callback, _ContentWrapper);

	  function Callback(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Callback);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Callback).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Callback');

	    _this.addItem(_this.getHeader());

	    if (!_this.isAvailable()) {
	      _this.addItem(_this.getWarningMessage());

	      _this.getSettingsForm().disable();
	    }

	    _this.addItem(_this.getSettingsForm());

	    return _this;
	  }

	  babelHelpers.createClass(Callback, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getWarningMessage",
	    value: function getWarningMessage() {
	      return this.cache.remember('warningMessage', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_HEADER'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true,
	          context: 'warning'
	        });
	      });
	    }
	  }, {
	    key: "isAvailable",
	    value: function isAvailable() {
	      var _this2 = this;

	      return this.cache.remember('isAvailable', function () {
	        return _this2.options.dictionary.callback.from.length > 0;
	      });
	    }
	  }, {
	    key: "getSettingsForm",
	    value: function getSettingsForm() {
	      var _this3 = this;

	      return this.cache.remember('settingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
	          toggleable: true,
	          toggleableType: landing_ui_form_formsettingsform.FormSettingsForm.ToggleableType.Switch,
	          opened: _this3.isAvailable() && main_core.Text.toBoolean(_this3.options.formOptions.callback.use),
	          fields: [_this3.getPhoneListField(), _this3.getTextField()]
	        });
	      });
	    }
	  }, {
	    key: "getUseCheckboxField",
	    value: function getUseCheckboxField() {
	      var _this4 = this;

	      return this.cache.remember('useCheckboxField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'use',
	          compact: true,
	          value: [main_core.Text.toBoolean(_this4.options.formOptions.callback.use)],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getPhoneListField",
	    value: function getPhoneListField() {
	      var _this5 = this;

	      return this.cache.remember('phoneListField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'from',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_PHONE_TITLE'),
	          content: _this5.options.formOptions.callback.from,
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_DEFAULT_PHONE_NOT_SELECTED'),
	            value: ''
	          }].concat(babelHelpers.toConsumableArray(_this5.options.dictionary.callback.from.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          })))
	        });
	      });
	    }
	  }, {
	    key: "getTextField",
	    value: function getTextField() {
	      var _this6 = this;

	      return this.cache.remember('textField', function () {
	        return new landing_ui_field_textfield.TextField({
	          selector: 'text',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TEXT_TITLE'),
	          content: _this6.options.formOptions.callback.text,
	          textOnly: true
	        });
	      });
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        callback: babelHelpers.objectSpread({}, value, {
	          use: this.getSettingsForm().isOpened()
	        })
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return Callback;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = Callback;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX,BX.Event,BX.Landing,BX.Landing.UI.Panel,BX.Landing.UI.Form,BX.Landing.UI.Card,BX.Landing.UI.Field,BX.Landing.UI.Card));
//# sourceMappingURL=callback.bundle.js.map
