this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_card_headercard,landing_loc,landing_ui_field_radiobuttonfield,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,main_core,ui_buttons,landing_ui_panel_formsettingspanel,landing_ui_card_messagecard) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var KeysForm = /*#__PURE__*/function (_FormSettingsForm) {
	  babelHelpers.inherits(KeysForm, _FormSettingsForm);
	  function KeysForm(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, KeysForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(KeysForm).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Content.SpamProtection.KeysForm');
	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-form-keys-settings');
	    _this.getButton().renderTo(_this.layout);
	    _this.value = {};
	    return _this;
	  }
	  babelHelpers.createClass(KeysForm, [{
	    key: "getButton",
	    value: function getButton() {
	      var _this2 = this;
	      return this.cache.remember('button', function () {
	        return new ui_buttons.Button({
	          text: _this2.options.buttonLabel,
	          color: ui_buttons.ButtonColor.LIGHT_BORDER,
	          onclick: function onclick() {
	            _this2.getButton().setWaiting(true);
	            main_core.Runtime.loadExtension('crm.form.captcha').then(function (_ref) {
	              var Captcha = _ref.Captcha;
	              _this2.getButton().setWaiting(false);
	              return Captcha.open();
	            }).then(function (result) {
	              _this2.value = _objectSpread({}, result);
	              var formSettingsPanel = landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance();
	              formSettingsPanel.getFormDictionary().captcha.hasKeys = main_core.Type.isStringFilled(result.key) && main_core.Type.isStringFilled(result.secret);
	              var activeButton = formSettingsPanel.getSidebarButtons().find(function (button) {
	                return button.isActive();
	              });
	              if (activeButton) {
	                activeButton.getLayout().click();
	              }
	              _this2.emit('onChange');
	            });
	          }
	        });
	      });
	    }
	  }, {
	    key: "serialize",
	    value: function serialize() {
	      return this.value;
	    }
	  }]);
	  return KeysForm;
	}(landing_ui_form_formsettingsform.FormSettingsForm);

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var SpamProtection = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(SpamProtection, _ContentWrapper);
	  function SpamProtection(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SpamProtection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SpamProtection).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.SpamProtection');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      header: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_MESSAGE_TEXT'),
	      angle: false
	    });
	    var captchaTypeForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'type',
	      description: null,
	      fields: [new landing_ui_card_messagecard.MessageCard({
	        selector: 'warning-captcha',
	        context: 'warning',
	        description: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_MESSAGE_WARNING_RECAPTCHA').replace('#URL_POLICIES_PRIVACY#', "https://policies.google.com/privacy").replace('#URL_POLICIES_TERMS#', 'https://policies.google.com/terms'),
	        angle: false,
	        closeable: false
	      }), new landing_ui_field_radiobuttonfield.RadioButtonField({
	        selector: 'use',
	        title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
	        value: main_core.Text.toBoolean(_this.options.formOptions.data.recaptcha.use) ? 'hidden' : 'disabled',
	        items: [{
	          id: 'disabled',
	          title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TAB_DISABLED'),
	          icon: 'landing-ui-spam-protection-icon-disabled'
	        }, {
	          id: 'hidden',
	          title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TAB_HIDDEN'),
	          icon: 'landing-ui-spam-protection-icon-hidden'
	        }]
	      })]
	    });
	    _this.addItem(header);
	    _this.addItem(message);
	    _this.addItem(captchaTypeForm);
	    captchaTypeForm.subscribe('onChange', _this.onTypeChange.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.onTypeChange();
	    return _this;
	  }
	  babelHelpers.createClass(SpamProtection, [{
	    key: "hasDefaultsCaptchaKeys",
	    value: function hasDefaultsCaptchaKeys() {
	      return main_core.Text.toBoolean(this.options.formOptions.captcha.hasDefaults);
	    }
	  }, {
	    key: "hasCustomKeys",
	    value: function hasCustomKeys() {
	      return main_core.Text.toBoolean(this.options.dictionary.captcha.hasKeys);
	    }
	  }, {
	    key: "onTypeChange",
	    value: function onTypeChange() {
	      main_core.Dom.remove(this.getCustomKeysForm().getLayout());
	      main_core.Dom.remove(this.getRequiredKeysForm().getLayout());
	      main_core.Dom.remove(this.getKeysSettingsForm().getLayout());
	      if (this.getValue().recaptcha.use) {
	        if (!this.hasDefaultsCaptchaKeys() && !this.hasCustomKeys()) {
	          this.addItem(this.getRequiredKeysForm());
	        }
	        if (!this.hasDefaultsCaptchaKeys() && this.hasCustomKeys() || this.hasDefaultsCaptchaKeys() && this.hasCustomKeys()) {
	          this.addItem(this.getKeysSettingsForm());
	        }
	        if (this.hasDefaultsCaptchaKeys() && !this.hasCustomKeys()) {
	          this.addItem(this.getCustomKeysForm());
	        }
	      }
	    }
	  }, {
	    key: "getCustomKeysForm",
	    value: function getCustomKeysForm() {
	      return this.cache.remember('customKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL')
	        });
	      });
	    }
	  }, {
	    key: "getRequiredKeysForm",
	    value: function getRequiredKeysForm() {
	      return this.cache.remember('requiredKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_REQUIRED_DESCRIPTION')
	        });
	      });
	    }
	  }, {
	    key: "getKeysSettingsForm",
	    value: function getKeysSettingsForm() {
	      return this.cache.remember('keysSettingsForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL')
	        });
	      });
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(sourceValue) {
	      return {
	        recaptcha: _objectSpread$1(_objectSpread$1(_objectSpread$1({
	          use: sourceValue.use === 'hidden'
	        }, this.getKeysSettingsForm().serialize()), this.getCustomKeysForm().serialize()), this.getRequiredKeysForm().serialize())
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread$1(_objectSpread$1({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return SpamProtection;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = SpamProtection;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Card,BX.Landing,BX.Landing.UI.Field,BX.Landing.UI.Panel,BX.Landing.UI.Form,BX,BX.UI,BX.Landing.UI.Panel,BX.Landing.UI.Card));
//# sourceMappingURL=spam-protection.bundle.js.map
