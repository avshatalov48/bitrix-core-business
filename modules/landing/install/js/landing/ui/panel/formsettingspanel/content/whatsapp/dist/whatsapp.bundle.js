this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,main_core,main_core_events,landing_loc,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,landing_ui_card_headercard,landing_ui_field_textfield,landing_ui_card_messagecard,crm_form_client,landing_ui_panel_formsettingspanel) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Whatsapp = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Whatsapp, _ContentWrapper);
	  function Whatsapp(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Whatsapp);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Whatsapp).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Whatsapp');
	    _this.addItem(_this.getHeader());
	    if (_this.options.dictionary.whatsapp.setup.completed) {
	      _this.addItem(_this.getSettingsForm());
	    } else {
	      _this.addItem(_this.getWarningMessage());
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Whatsapp, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getWarningMessage",
	    value: function getWarningMessage() {
	      var _this2 = this;
	      return this.cache.remember('warningMessage', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_WHATSAPP_WARNING_HEADER'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_WHATSAPP_WARNING_TEXT'),
	          angle: false,
	          closeable: false,
	          more: function more() {
	            BX.SidePanel.Instance.open(_this2.options.dictionary.whatsapp.setup.link, {
	              events: {
	                onClose: function onClose() {
	                  crm_form_client.FormClient.getInstance().getDictionary().then(function (dictionary) {
	                    _this2.options.dictionary = dictionary;
	                    landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance().setFormDictionary(dictionary);
	                    _this2.clear();
	                    _this2.addItem(_this2.getHeader());
	                    if (_this2.options.dictionary.whatsapp.setup.completed) {
	                      _this2.addItem(_this2.getSettingsForm());
	                    } else {
	                      _this2.addItem(_this2.getWarningMessage());
	                    }
	                  });
	                }
	              }
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "getSettingsForm",
	    value: function getSettingsForm() {
	      var _this3 = this;
	      return this.cache.remember('settingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_USE_CHECKBOX_LABEL'),
	          fields: [_this3.getTextField()]
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
	          value: [main_core.Text.toBoolean(_this4.options.formOptions.whatsapp.use)],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getTextField",
	    value: function getTextField() {
	      var _this5 = this;
	      return this.cache.remember('textField', function () {
	        var textItem = _this5.options.dictionary.whatsapp.messages.find(function (item) {
	          return String(item.langId) === String(_this5.options.data.language);
	        });
	        var text = main_core.Type.isPlainObject(textItem) ? textItem.text : '';
	        var field = new landing_ui_field_textfield.TextField({
	          selector: 'text',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_TEXT_TITLE'),
	          content: text,
	          textOnly: true
	        });
	        main_core.Dom.addClass(field.input, 'landing-ui-disabled');
	        return field;
	      });
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        whatsapp: {
	          use: this.getSettingsForm().isOpened()
	        }
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return Whatsapp;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = Whatsapp;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX,BX.Event,BX.Landing,BX.Landing.UI.Panel,BX.Landing.UI.Form,BX.Landing.UI.Card,BX.Landing.UI.Field,BX.Landing.UI.Card,BX.Crm.Form,BX.Landing.UI.Panel));
//# sourceMappingURL=whatsapp.bundle.js.map
