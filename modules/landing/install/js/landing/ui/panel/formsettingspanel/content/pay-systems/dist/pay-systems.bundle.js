this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_loc,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_panel_basepresetpanel,main_core,main_core_events,landing_ui_field_paysystemsselectorfield,landing_ui_form_formsettingsform,landing_ui_panel_formsettingspanel_content_crm_schememanager) {
	'use strict';

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/pay-systems/dist/images/message-icon.svg";

	var _templateObject, _templateObject2;
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _moduleNotIncludedErrorCode = /*#__PURE__*/new WeakMap();
	var _schemeManager = /*#__PURE__*/new WeakMap();
	var _showErrorPlaceholder = /*#__PURE__*/new WeakSet();
	var _getHeaderCard = /*#__PURE__*/new WeakSet();
	var _getMessageCard = /*#__PURE__*/new WeakSet();
	var _getPaySystemsSelectorForm = /*#__PURE__*/new WeakSet();
	var _getPaySystemsSelectorField = /*#__PURE__*/new WeakSet();
	var PaySystems = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(PaySystems, _ContentWrapper);
	  function PaySystems(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, PaySystems);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PaySystems).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getPaySystemsSelectorField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getPaySystemsSelectorForm);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getMessageCard);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getHeaderCard);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _showErrorPlaceholder);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _moduleNotIncludedErrorCode, {
	      writable: true,
	      value: 'MODULE_NOT_INCLUDED'
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _schemeManager, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.PaySystemContent');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _schemeManager, new landing_ui_panel_formsettingspanel_content_crm_schememanager.SchemeManager(babelHelpers.toConsumableArray(options.dictionary.document.schemes)));
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getHeaderCard, _getHeaderCard2).call(babelHelpers.assertThisInitialized(_this)));
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getMessageCard, _getMessageCard2).call(babelHelpers.assertThisInitialized(_this)));
	    _this.addItem(_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getPaySystemsSelectorForm, _getPaySystemsSelectorForm2).call(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(PaySystems, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-content-pay-system\"></div>"])));
	      });
	    }
	  }, {
	    key: "getErrorPlaceholderLayout",
	    value: function getErrorPlaceholderLayout(title) {
	      var subtitle = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      return this.cache.remember("errorTitle", function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-slider-no-access\">\n\t\t\t\t<div class=\"ui-slider-no-access-inner\">\n\t\t\t\t\t<div class=\"ui-slider-no-access-title\">", "</div>\n\t\t\t\t\t<div class=\"ui-slider-no-access-subtitle\">", "</div>\n\t\t\t\t\t<div class=\"ui-slider-no-access-img\">\n\t\t\t\t\t\t<div class=\"ui-slider-no-access-img-inner\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), title, subtitle);
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var schemeId = main_core.Text.toNumber(this.options.formOptions.document.scheme);
	      var usePayment = _classPrivateMethodGet(this, _getPaySystemsSelectorForm, _getPaySystemsSelectorForm2).call(this).isOpened();
	      if (!babelHelpers.classPrivateFieldGet(this, _schemeManager).isInvoice(schemeId) && usePayment) {
	        this.options.formOptions.document.scheme = babelHelpers.classPrivateFieldGet(this, _schemeManager).getSpecularSchemeId(schemeId);
	      }
	      return babelHelpers.get(babelHelpers.getPrototypeOf(PaySystems.prototype), "getValue", this).call(this);
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      var paySystemsSelectorData = value.paySystemsSelector;
	      var payment = this.options.formOptions.payment;
	      var document = this.options.formOptions.document;
	      payment.disabledSystems = paySystemsSelectorData.disabledPaySystems;
	      payment.use = _classPrivateMethodGet(this, _getPaySystemsSelectorForm, _getPaySystemsSelectorForm2).call(this).isOpened();
	      if (document.payment) {
	        document.payment = _objectSpread(_objectSpread({}, document.payment), payment);
	      } else {
	        document.payment = payment;
	      }
	      return {
	        document: document
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "onFetchPaySystemsError",
	    value: function onFetchPaySystemsError(errors) {
	      var firstError = errors[0];
	      if (firstError.code === babelHelpers.classPrivateFieldGet(this, _moduleNotIncludedErrorCode)) {
	        _classPrivateMethodGet(this, _showErrorPlaceholder, _showErrorPlaceholder2).call(this, firstError.message);
	      } else {
	        _classPrivateMethodGet(this, _showErrorPlaceholder, _showErrorPlaceholder2).call(this, 'Network error');
	      }
	    }
	  }]);
	  return PaySystems;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);
	function _showErrorPlaceholder2(title) {
	  var subtitle = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	  main_core.Dom.clean(this.getLayout());
	  main_core.Dom.append(this.getErrorPlaceholderLayout(title, subtitle), this.getLayout());
	}
	function _getHeaderCard2() {
	  return new landing_ui_card_headercard.HeaderCard({
	    title: landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_TITLE')
	  });
	}
	function _getMessageCard2() {
	  return new landing_ui_card_messagecard.MessageCard({
	    id: 'paymentMessage',
	    header: landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_MESSAGE_HEADER'),
	    description: landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_MESSAGE_DESCRIPTION'),
	    icon: messageIcon,
	    restoreState: true
	  });
	}
	function _getPaySystemsSelectorForm2() {
	  var _this2 = this;
	  return this.cache.remember('paySystemsSelectorForm', function () {
	    return new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'paySystemsForm',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_PAY_SYSTEMS_GET_PAYMENT'),
	      toggleable: true,
	      opened: _this2.options.formOptions.payment.use,
	      fields: [_classPrivateMethodGet(_this2, _getPaySystemsSelectorField, _getPaySystemsSelectorField2).call(_this2)]
	    });
	  });
	}
	function _getPaySystemsSelectorField2() {
	  var _this3 = this;
	  return this.cache.remember('paySystemSelectorField', function () {
	    return new landing_ui_field_paysystemsselectorfield.PaySystemsSelectorField({
	      id: 'paySystemsSelector',
	      selector: 'paySystemsSelector',
	      disabledPaySystems: babelHelpers.toConsumableArray(_this3.options.formOptions.payment.disabledSystems),
	      onFetchPaySystemsError: function onFetchPaySystemsError(errors) {
	        return _this3.onFetchPaySystemsError(errors);
	      },
	      showMorePaySystemsBtn: true,
	      morePaySystemsBtnSidePanelPath: _this3.options.dictionary.payment.moreSystemSliderPath
	    });
	  });
	}

	exports.default = PaySystems;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Panel,BX,BX.Event,BX.Landing.Ui.Field,BX.Landing.UI.Form,BX.Landing.Ui.Panel.Formsettingspanel.Content.Crm));
//# sourceMappingURL=pay-systems.bundle.js.map
