this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_loc,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_panel_basepresetpanel,landing_ui_form_formsettingsform,landing_ui_field_fieldslistfield) {
	'use strict';

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/fields/dist/images/message-icon.svg";

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
	        formOptions: babelHelpers.objectSpread({}, _this.options.formOptions),
	        crmFields: babelHelpers.objectSpread({}, _this.options.crmFields),
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
