this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_panel_basepresetpanel,landing_ui_card_headercard,landing_loc,landing_ui_card_basecard,main_core,main_core_events,landing_ui_card_messagecard,crm_form_integration) {
	'use strict';

	var VkContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(VkContent, _ContentWrapper);
	  function VkContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, VkContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VkContent).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.VkContent');
	    _this.addItem(new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_VK')
	    }));
	    if (!_this.options.dictionary.integration.canUse) {
	      _this.addItem(new landing_ui_card_messagecard.MessageCard({
	        header: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_HEADER'),
	        description: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_VK_TEXT'),
	        angle: false,
	        closeable: false
	      }));
	      return babelHelpers.possibleConstructorReturn(_this);
	    }
	    var buttonCard = new landing_ui_card_basecard.BaseCard();
	    main_core.Dom.style(buttonCard.getLayout(), {
	      padding: 0,
	      margin: 0
	    });
	    var integration = new crm_form_integration.Integration({
	      type: 'vkontakte',
	      form: _this.options.formOptions,
	      fields: _this.options.crmFields,
	      dictionary: _this.options.dictionary
	    });
	    integration.subscribe('change', _this.onChange.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Dom.append(integration.render(), buttonCard.getBody());
	    _this.addItem(buttonCard);
	    return _this;
	  }
	  babelHelpers.createClass(VkContent, [{
	    key: "prepareButtonText",
	    value: function prepareButtonText(formOptions) {
	      var enabled = formOptions.integration.cases.some(function (item) {
	        return item.providerCode === 'vkontakte';
	      });
	      if (enabled) {
	        return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_VK_BUTTON_ENABLED');
	      }
	      return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_VK_BUTTON');
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        integration: this.options.formOptions.integration
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', {
	        skipPrepare: false
	      });
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.options.formOptions.integration.cases.filter(function (data) {
	        return data.providerCode === 'vkontakte';
	      })[0] || null;
	    }
	  }]);
	  return VkContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = VkContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Panel,BX.Landing.UI.Card,BX.Landing,BX.Landing.UI.Card,BX,BX.Event,BX.Landing.UI.Card,BX.Crm.Form));
//# sourceMappingURL=vk.bundle.js.map
