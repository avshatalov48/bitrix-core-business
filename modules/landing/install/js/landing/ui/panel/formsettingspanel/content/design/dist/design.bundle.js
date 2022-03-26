this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {};
this.BX.Landing.UI.Panel.FormSettingsPanel = this.BX.Landing.UI.Panel.FormSettingsPanel || {};
(function (exports,landing_ui_panel_basepresetpanel,landing_ui_card_headercard,landing_loc,landing_ui_card_messagecard,ui_buttons,main_core,landing_ui_panel_formsettingspanel) {
	'use strict';

	var Design = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Design, _ContentWrapper);

	  function Design(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Design);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Design).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Design');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'designMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_MESSAGE_TEXT'),
	      restoreState: true,
	      angle: false
	    });

	    _this.addItem(header);

	    _this.addItem(message);

	    main_core.Dom.append(_this.getButton().render(), _this.getLayout());
	    return _this;
	  }

	  babelHelpers.createClass(Design, [{
	    key: "getButton",
	    value: function getButton() {
	      return this.cache.remember('button', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DESIGN_BUTTON_LABEL'),
	          color: ui_buttons.ButtonColor.LIGHT_BORDER,
	          onclick: function onclick() {
	            landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance().onFormDesignButtonClick();
	          }
	        });
	      });
	    }
	  }]);
	  return Design;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = Design;

}((this.BX.Landing.UI.Panel.FormSettingsPanel.Content = this.BX.Landing.UI.Panel.FormSettingsPanel.Content || {}),BX.Landing.UI.Panel,BX.Landing.UI.Card,BX.Landing,BX.Landing.UI.Card,BX.UI,BX,BX.Landing.UI.Panel));
//# sourceMappingURL=design.bundle.js.map
