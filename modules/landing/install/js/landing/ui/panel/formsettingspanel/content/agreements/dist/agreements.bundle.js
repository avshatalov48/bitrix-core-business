this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,main_core,landing_loc,landing_ui_card_headercard,landing_ui_card_messagecard,landing_ui_form_formsettingsform,landing_ui_field_agreementslist,landing_ui_panel_basepresetpanel) {
	'use strict';

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/agreements/dist/images/message-icon.svg";

	var AgreementsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(AgreementsContent, _ContentWrapper);

	  function AgreementsContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, AgreementsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AgreementsContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.AgreementsContent');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_AGREEMENTS_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'agreementsMessage',
	      header: landing_loc.Loc.getMessage('LANDING_AGREEMENTS_MESSAGE_HEADER'),
	      description: landing_loc.Loc.getMessage('LANDING_AGREEMENTS_MESSAGE_DESCRIPTION'),
	      icon: messageIcon,
	      restoreState: true
	    });
	    var listForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'agreementsList',
	      fields: [new landing_ui_field_agreementslist.AgreementsList({
	        selector: 'agreements',
	        formOptions: _this.options.formOptions,
	        agreementsList: _this.options.agreements,
	        value: _this.options.formOptions.data.agreements
	      })]
	    });

	    if (!message.isShown()) {
	      main_core.Dom.style(header.getLayout(), 'margin-bottom', '0');
	      main_core.Dom.style(listForm.getLayout(), 'margin-top', '-19px');
	    }

	    message.subscribe('onClose', function () {
	      main_core.Dom.style(header.getLayout(), 'margin-bottom', '0');
	      main_core.Dom.style(listForm.getLayout(), 'margin-top', '-19px');
	    });

	    _this.addItem(header);

	    _this.addItem(message);

	    _this.addItem(listForm);

	    return _this;
	  }

	  return AgreementsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = AgreementsContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX,BX.Landing,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Landing.UI.Panel));
//# sourceMappingURL=agreements.bundle.js.map
