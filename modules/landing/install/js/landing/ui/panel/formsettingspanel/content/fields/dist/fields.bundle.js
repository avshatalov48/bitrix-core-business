this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_loc,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,landing_ui_field_fieldslistfield) {
	'use strict';

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/fields/dist/images/message-icon.svg";

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var FieldsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(FieldsContent, _ContentWrapper);
	  function FieldsContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, FieldsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsContent).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsContent');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'fieldsMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_MESSAGE_DESCRIPTION'),
	      icon: messageIcon,
	      restoreState: true
	    });
	    var fieldsForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      fields: [new landing_ui_field_fieldslistfield.FieldsListField({
	        selector: 'fields',
	        isLeadEnabled: _this.options.isLeadEnabled,
	        dictionary: _this.options.dictionary,
	        formOptions: _objectSpread({}, _this.options.formOptions),
	        crmFields: _objectSpread({}, _this.options.crmFields),
	        items: babelHelpers.toConsumableArray(_this.options.formOptions.data.fields)
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
	  return FieldsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = FieldsContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Form,BX.Landing.UI.Field));
//# sourceMappingURL=fields.bundle.js.map
