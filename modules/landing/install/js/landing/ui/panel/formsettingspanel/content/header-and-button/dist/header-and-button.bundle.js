this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_card_headercard,main_core,landing_ui_card_messagecard,landing_ui_form_formsettingsform,landing_ui_field_textfield,landing_ui_field_variablesfield,landing_ui_panel_basepresetpanel) {
	'use strict';

	var headerAndButtonsIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/header-and-button/dist/images/header-and-buttons-message-icon.svg";

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var HeaderAndButtonContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(HeaderAndButtonContent, _ContentWrapper);
	  function HeaderAndButtonContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, HeaderAndButtonContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(HeaderAndButtonContent).call(this, options));
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.HeaderAndButtonContent');
	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'headerAndButtonMessage',
	      icon: headerAndButtonsIcon,
	      header: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_MESSAGE_HEADER'),
	      description: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_MESSAGE_DESCRIPTION_2'),
	      restoreState: true,
	      more: function more() {
	        var helper$$1 = main_core.Reflection.getClass('top.BX.Helper');
	        if (helper$$1) {
	          BX.Helper.show('redirect=detail&code=12802786');
	        }
	      }
	    });
	    var headersForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'headers',
	      title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_TITLE'),
	      fields: [new landing_ui_field_variablesfield.VariablesField({
	        selector: 'title',
	        title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HEADER_FIELD_TITLE'),
	        textOnly: true,
	        content: _this.options.formOptions.data.title,
	        variables: _this.getPersonalizationVariables()
	      }), new landing_ui_field_variablesfield.VariablesField({
	        selector: 'desc',
	        title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_SUBHEADER_FIELD_TITLE'),
	        textOnly: true,
	        content: _this.options.formOptions.data.desc,
	        variables: _this.getPersonalizationVariables()
	      }), new BX.Landing.UI.Field.Checkbox({
	        selector: 'hideDesc',
	        items: [{
	          value: 'hideDesc',
	          name: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HIDE_SUBHEADER_FIELD_TITLE')
	        }
	        // {
	        // 	value: 'hideSeparator',
	        // 	name: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HIDE_SEPARATOR_FIELD_TITLE'),
	        // },
	        ],

	        compact: true
	      })]
	    });
	    var buttonsForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'buttons',
	      title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_FORM_TITLE'),
	      fields: [new landing_ui_field_textfield.TextField({
	        selector: 'buttonCaption',
	        title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_FORM_SEND_BUTTON_TITLE'),
	        textOnly: true,
	        content: _this.options.formOptions.data.buttonCaption
	      })]
	    });
	    _this.addItem(header);
	    _this.addItem(message);
	    _this.addItem(headersForm);
	    _this.addItem(buttonsForm);
	    return _this;
	  }
	  babelHelpers.createClass(HeaderAndButtonContent, [{
	    key: "getPersonalizationVariables",
	    value: function getPersonalizationVariables() {
	      var _this2 = this;
	      return this.cache.remember('personalizationVariables', function () {
	        return _this2.options.dictionary.personalization.list.map(function (item) {
	          return {
	            name: item.name,
	            value: item.id
	          };
	        });
	      });
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(sourceValue) {
	      var value = Object.entries(sourceValue).reduce(function (acc, _ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          key = _ref2[0],
	          value = _ref2[1];
	        if (key === 'hideDesc') {
	          if (value.includes(key)) {
	            acc.desc = '';
	          }
	          delete acc.hideDesc;
	        }
	        if (key === 'useSign') {
	          acc.useSign = value.includes('useSign');
	        }
	        return acc;
	      }, _objectSpread({}, sourceValue));
	      if (!this.items[2].getSwitch().getValue()) {
	        value.title = '';
	        value.desc = '';
	      }
	      return value;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return HeaderAndButtonContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = HeaderAndButtonContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Card,BX,BX.Landing.UI.Card,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.Landing.UI.Panel));
//# sourceMappingURL=header-and-button.bundle.js.map
