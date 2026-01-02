/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_card_headercard,landing_loc,landing_ui_field_radiobuttonfield,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,main_core,ui_buttons,landing_ui_panel_formsettingspanel,landing_ui_card_messagecard) {
	'use strict';

	var YANDEX_CAPTCHA_SERVICE = 'yandex';
	var GOOGLE_CAPTCHA_SERVICE = 'google';
	var YANDEX_AVAILABLE_ZONES = new Set(['ru', 'by', 'kz', 'uz']);

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

	            // eslint-disable-next-line promise/catch-or-return
	            main_core.Runtime.loadExtension('crm.form.captcha').then(function (_ref) {
	              var Captcha = _ref.Captcha;
	              _this2.getButton().setWaiting(false);
	              return Captcha.open(_this2.options.type);
	            }).then(function (result) {
	              _this2.value = _objectSpread({}, result);
	              var formSettingsPanel = landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance();
	              if (_this2.options.type === YANDEX_CAPTCHA_SERVICE) {
	                formSettingsPanel.getFormDictionary().captcha.yandexCaptcha.hasKeys = main_core.Type.isStringFilled(result.key) && main_core.Type.isStringFilled(result.secret);
	              } else {
	                formSettingsPanel.getFormDictionary().captcha.recaptcha.hasKeys = main_core.Type.isStringFilled(result.key) && main_core.Type.isStringFilled(result.secret);
	              }
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
	    if (YANDEX_AVAILABLE_ZONES.has(options.dictionary.region)) {
	      // yandexCaptcha is chosen for ru region or if chosen explicitly
	      var chosenCaptcha = GOOGLE_CAPTCHA_SERVICE;
	      if (_this.options.formOptions.captcha.service === GOOGLE_CAPTCHA_SERVICE || _this.options.formOptions.captcha.service === '' && _this.options.formOptions.captcha.recaptcha.use === true) {
	        chosenCaptcha = GOOGLE_CAPTCHA_SERVICE;
	      } else if (options.dictionary.region === 'ru' || _this.options.formOptions.captcha.service === YANDEX_CAPTCHA_SERVICE) {
	        chosenCaptcha = YANDEX_CAPTCHA_SERVICE;
	      }
	      var _captchaServiceForm = _this.getServiceForm(false, chosenCaptcha);
	      _this.addItem(header);
	      _this.addItem(message);
	      _this.addItem(_captchaServiceForm);
	      _captchaServiceForm.subscribe('onChange', _this.onCaptchaServiceChange.bind(babelHelpers.assertThisInitialized(_this)));
	      _this.onCaptchaServiceChange();
	      return babelHelpers.possibleConstructorReturn(_this);
	    }
	    var captchaServiceForm = _this.getServiceForm(true, GOOGLE_CAPTCHA_SERVICE);
	    var recaptchaTypeForm = _this.getRecaptchaForm();
	    _this.addItem(captchaServiceForm);
	    _this.addItem(recaptchaTypeForm);
	    recaptchaTypeForm.subscribe('onChange', _this.onRecaptchaTypeChange.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.onRecaptchaTypeChange();
	    return _this;
	  }
	  babelHelpers.createClass(SpamProtection, [{
	    key: "getServiceForm",
	    value: function getServiceForm(isHidden, chosenCaptcha) {
	      return new landing_ui_form_formsettingsform.FormSettingsForm({
	        id: 'service',
	        description: null,
	        hidden: isHidden,
	        fields: [new BX.Landing.UI.Field.Dropdown({
	          selector: 'service',
	          title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_TITLE'),
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_OPTION_YANDEX'),
	            value: YANDEX_CAPTCHA_SERVICE
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_OPTION_GOOGLE'),
	            value: GOOGLE_CAPTCHA_SERVICE
	          }],
	          content: chosenCaptcha
	        })]
	      });
	    }
	  }, {
	    key: "onCaptchaServiceChange",
	    value: function onCaptchaServiceChange() {
	      var recaptchaTypeForm = this.getRecaptchaForm();
	      var yandexCaptchaForm = this.getYandexCaptchaForm();
	      var foreignServiceWarningForm = this.getForeignServiceWarningForm();
	      main_core.Dom.remove(recaptchaTypeForm.getLayout());
	      main_core.Dom.remove(yandexCaptchaForm.getLayout());
	      main_core.Dom.remove(foreignServiceWarningForm.getLayout());
	      if (this.getValue().captcha.service === GOOGLE_CAPTCHA_SERVICE) {
	        yandexCaptchaForm.unsubscribe('onChange', this.onYandexTypeChange.bind(this));
	        this.onYandexTypeChange();
	        this.addForeignServiceWarningForm(foreignServiceWarningForm, GOOGLE_CAPTCHA_SERVICE);
	        this.addItem(recaptchaTypeForm);
	        recaptchaTypeForm.subscribe('onChange', this.onRecaptchaTypeChange.bind(this));
	        this.onRecaptchaTypeChange();
	      } else {
	        recaptchaTypeForm.unsubscribe('onChange', this.onRecaptchaTypeChange.bind(this));
	        this.onRecaptchaTypeChange();
	        this.addForeignServiceWarningForm(foreignServiceWarningForm, YANDEX_CAPTCHA_SERVICE);
	        this.addItem(yandexCaptchaForm);
	        yandexCaptchaForm.subscribe('onChange', this.onYandexTypeChange.bind(this));
	        this.onYandexTypeChange();
	      }
	    }
	  }, {
	    key: "addForeignServiceWarningForm",
	    value: function addForeignServiceWarningForm(form, service) {
	      if (this.options.dictionary.region === 'by' || this.options.dictionary.region === 'ru' && service === GOOGLE_CAPTCHA_SERVICE) {
	        this.addItem(form);
	      }
	    }
	  }, {
	    key: "getForeignServiceWarningForm",
	    value: function getForeignServiceWarningForm() {
	      return this.cache.remember('foreignServiceWarning', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          id: 'foreignServiceWarning',
	          description: null,
	          fields: [new landing_ui_card_messagecard.MessageCard({
	            selector: 'warningForeign',
	            context: 'warning',
	            description: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_MESSAGE_WARNING_FOREIGN'),
	            angle: false,
	            closeable: false
	          })]
	        });
	      });
	    }
	  }, {
	    key: "getRecaptchaForm",
	    value: function getRecaptchaForm() {
	      var _this2 = this;
	      return this.cache.remember('recaptchaForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          id: 'type',
	          description: null,
	          fields: [new landing_ui_card_messagecard.MessageCard({
	            selector: 'warningCaptcha',
	            context: 'warning',
	            description: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_MESSAGE_WARNING_RECAPTCHA').replace('#URL_POLICIES_PRIVACY#', 'https://policies.google.com/privacy').replace('#URL_POLICIES_TERMS#', 'https://policies.google.com/terms'),
	            angle: false,
	            closeable: false
	          }), new landing_ui_field_radiobuttonfield.RadioButtonField({
	            selector: 'recaptchaUse',
	            title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
	            value: main_core.Text.toBoolean(_this2.options.formOptions.captcha.recaptcha.use) ? 'hidden' : 'disabled',
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
	      });
	    }
	  }, {
	    key: "getYandexCaptchaForm",
	    value: function getYandexCaptchaForm() {
	      var _this3 = this;
	      return this.cache.remember('yandexCaptchaForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          id: 'yandexType',
	          description: null,
	          fields: [new landing_ui_field_radiobuttonfield.RadioButtonField({
	            selector: 'yandexUse',
	            title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE_YANDEX'),
	            value: main_core.Text.toBoolean(_this3.options.formOptions.captcha.yandexCaptcha.use) ? 'hidden' : 'disabled',
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
	      });
	    }
	  }, {
	    key: "hasDefaultYandexKeys",
	    value: function hasDefaultYandexKeys() {
	      return main_core.Text.toBoolean(this.options.formOptions.captcha.yandexCaptcha.hasDefaults);
	    }
	  }, {
	    key: "hasCustomYandexKeys",
	    value: function hasCustomYandexKeys() {
	      return main_core.Text.toBoolean(this.options.dictionary.captcha.yandexCaptcha.hasKeys);
	    }
	  }, {
	    key: "onYandexTypeChange",
	    value: function onYandexTypeChange() {
	      main_core.Dom.remove(this.getYandexKeysSettingsForm().getLayout());
	      main_core.Dom.remove(this.getYandexRequiredKeysForm().getLayout());
	      if (this.getValue().captcha.service === YANDEX_CAPTCHA_SERVICE && this.getValue().captcha.yandexCaptcha.use) {
	        if (!this.hasDefaultYandexKeys() && !this.hasCustomYandexKeys()) {
	          this.addItem(this.getYandexRequiredKeysForm());
	        } else if (this.hasCustomYandexKeys()) {
	          this.addItem(this.getYandexKeysSettingsForm());
	        } else if (this.hasDefaultYandexKeys() && !this.hasCustomYandexKeys()) {
	          this.addItem(this.getYandexCustomKeysForm());
	        }
	      }
	    }
	  }, {
	    key: "getYandexCustomKeysForm",
	    value: function getYandexCustomKeysForm() {
	      return this.cache.remember('yandexCustomKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL'),
	          type: YANDEX_CAPTCHA_SERVICE
	        });
	      });
	    }
	  }, {
	    key: "getYandexRequiredKeysForm",
	    value: function getYandexRequiredKeysForm() {
	      return this.cache.remember('yandexRequiredKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_REQUIRED_DESCRIPTION'),
	          type: YANDEX_CAPTCHA_SERVICE
	        });
	      });
	    }
	  }, {
	    key: "getYandexKeysSettingsForm",
	    value: function getYandexKeysSettingsForm() {
	      return this.cache.remember('yandexCustomKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL'),
	          type: YANDEX_CAPTCHA_SERVICE
	        });
	      });
	    }
	  }, {
	    key: "hasDefaultRecaptchaKeys",
	    value: function hasDefaultRecaptchaKeys() {
	      return main_core.Text.toBoolean(this.options.formOptions.captcha.recaptcha.hasDefaults);
	    }
	  }, {
	    key: "hasRecaptchaCustomKeys",
	    value: function hasRecaptchaCustomKeys() {
	      return main_core.Text.toBoolean(this.options.dictionary.captcha.recaptcha.hasKeys);
	    }
	  }, {
	    key: "onRecaptchaTypeChange",
	    value: function onRecaptchaTypeChange() {
	      main_core.Dom.remove(this.getRecaptchaCustomKeysForm().getLayout());
	      main_core.Dom.remove(this.getRecaptchaRequiredKeysForm().getLayout());
	      main_core.Dom.remove(this.getRecaptchaKeysSettingsForm().getLayout());
	      if ((this.getValue().captcha.service === GOOGLE_CAPTCHA_SERVICE || !this.getValue().captcha.service) && this.getValue().captcha.recaptcha.use) {
	        if (!this.hasDefaultRecaptchaKeys() && !this.hasRecaptchaCustomKeys()) {
	          this.addItem(this.getRecaptchaRequiredKeysForm());
	        } else if (this.hasRecaptchaCustomKeys()) {
	          this.addItem(this.getRecaptchaKeysSettingsForm());
	        } else if (this.hasDefaultRecaptchaKeys() && !this.hasRecaptchaCustomKeys()) {
	          this.addItem(this.getRecaptchaCustomKeysForm());
	        }
	      }
	    }
	  }, {
	    key: "getRecaptchaCustomKeysForm",
	    value: function getRecaptchaCustomKeysForm() {
	      return this.cache.remember('customKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL'),
	          type: GOOGLE_CAPTCHA_SERVICE
	        });
	      });
	    }
	  }, {
	    key: "getRecaptchaRequiredKeysForm",
	    value: function getRecaptchaRequiredKeysForm() {
	      return this.cache.remember('requiredKeysForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_REQUIRED_DESCRIPTION'),
	          type: GOOGLE_CAPTCHA_SERVICE
	        });
	      });
	    }
	  }, {
	    key: "getRecaptchaKeysSettingsForm",
	    value: function getRecaptchaKeysSettingsForm() {
	      return this.cache.remember('keysSettingsForm', function () {
	        return new KeysForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
	          buttonLabel: landing_loc.Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL'),
	          type: GOOGLE_CAPTCHA_SERVICE
	        });
	      });
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(sourceValue) {
	      return {
	        captcha: {
	          service: sourceValue.service,
	          recaptcha: _objectSpread$1(_objectSpread$1(_objectSpread$1({
	            use: sourceValue.recaptchaUse ? sourceValue.recaptchaUse === 'hidden' : this.options.formOptions.captcha.recaptcha.use
	          }, this.getRecaptchaKeysSettingsForm().serialize()), this.getRecaptchaCustomKeysForm().serialize()), this.getRecaptchaRequiredKeysForm().serialize()),
	          yandexCaptcha: _objectSpread$1(_objectSpread$1({
	            use: sourceValue.yandexUse ? sourceValue.yandexUse === 'hidden' : this.options.formOptions.captcha.yandexCaptcha.use
	          }, this.getYandexRequiredKeysForm().serialize()), this.getYandexKeysSettingsForm().serialize())
	        }
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
