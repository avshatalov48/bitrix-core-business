this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_backend,main_loader,landing_env,landing_ui_panel_stylepanel,ui_dialogs_messagebox,helper,landing_ui_field_agreementslist,landing_ui_field_accordionfield,landing_ui_field_fieldslistfield,landing_ui_field_rulefield,landing_ui_component_actionpanel,landing_ui_component_internal,landing_ui_field_presetfield,landing_ui_field_variablesfield,landing_ui_component_link,landing_ui_field_radiobuttonfield,landing_ui_field_defaultvaluefield,ui_buttons,landing_ui_card_basecard,crm_form_client,landing_ui_card_messagecard,landing_ui_card_headercard,landing_ui_form_formsettingsform,landing_ui_field_textfield,main_core_events,landing_ui_field_basefield,ui_entitySelector,landing_pageobject,main_core,landing_ui_button_sidebarbutton,landing_loc,landing_ui_panel_basepresetpanel) {
	'use strict';

	var headerAndButtonsIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/header-and-buttons/images/header-and-buttons-message-icon.svg";

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
	      toggleable: true,
	      fields: [new landing_ui_field_variablesfield.VariablesField({
	        selector: 'title',
	        title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HEADER_FIELD_TITLE'),
	        textOnly: true,
	        content: _this.options.values.title,
	        variables: _this.options.personalizationVariables
	      }), new landing_ui_field_variablesfield.VariablesField({
	        selector: 'desc',
	        title: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_SUBHEADER_FIELD_TITLE'),
	        textOnly: true,
	        content: _this.options.values.desc,
	        variables: _this.options.personalizationVariables
	      }), new BX.Landing.UI.Field.Checkbox({
	        selector: 'hideDesc',
	        items: [{
	          value: 'hideDesc',
	          name: main_core.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HIDE_SUBHEADER_FIELD_TITLE')
	        } // {
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
	        content: _this.options.values.buttonCaption
	      })]
	    });

	    _this.addItem(header);

	    _this.addItem(message);

	    _this.addItem(headersForm);

	    _this.addItem(buttonsForm);

	    return _this;
	  } // eslint-disable-next-line class-methods-use-this


	  babelHelpers.createClass(HeaderAndButtonContent, [{
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
	      }, babelHelpers.objectSpread({}, sourceValue));

	      if (!this.items[2].getSwitch().getValue()) {
	        value.title = '';
	        value.desc = '';
	      }

	      return value;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return HeaderAndButtonContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/agreements/images/message-icon.svg";

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
	        agreementsList: _this.options.agreementsList,
	        value: _this.options.values.agreements
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
	    var captchaTypeForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'type',
	      description: null,
	      fields: [new landing_ui_field_radiobuttonfield.RadioButtonField({
	        selector: 'use',
	        title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
	        value: _this.options.values.use ? 'hidden' : 'disabled',
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

	    _this.addItem(captchaTypeForm);

	    _this.addItem(_this.getKeysCheckbox());

	    _this.addItem(_this.getKeysForm());

	    var hasKeys = _this.options.values.key && _this.options.values.secret;

	    if (captchaTypeForm.fields[0].getValue() === 'disabled') {
	      _this.hideKeysCheckbox();

	      _this.hideKeysForm();
	    } else {
	      _this.showKeysCheckbox();

	      _this.getKeysCheckbox().fields[0].setValue([hasKeys ? 'useCustomKeys' : '']);

	      if (hasKeys) {
	        _this.showKeysForm();
	      } else {
	        _this.hideKeysForm();
	      }
	    }

	    captchaTypeForm.subscribe('onChange', function () {
	      if (captchaTypeForm.fields[0].getValue() === 'disabled') {
	        _this.hideKeysCheckbox();

	        _this.hideKeysForm();
	      } else {
	        _this.showKeysCheckbox();

	        _this.getKeysCheckbox().fields[0].setValue([hasKeys ? 'useCustomKeys' : '']);

	        if (hasKeys) {
	          _this.showKeysForm();
	        } else {
	          _this.hideKeysForm();
	        }
	      }
	    });

	    _this.getKeysCheckbox().subscribe('onChange', function () {
	      if (_this.getKeysCheckbox().fields[0].getValue().includes('useCustomKeys')) {
	        _this.showKeysForm();
	      } else {
	        _this.hideKeysForm();
	      }
	    });

	    return _this;
	  }

	  babelHelpers.createClass(SpamProtection, [{
	    key: "getKeysCheckbox",
	    value: function getKeysCheckbox() {
	      return this.cache.remember('keysCheckbox', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          id: 'customKeys',
	          description: null,
	          fields: [new BX.Landing.UI.Field.Checkbox({
	            selector: 'useCustomKeys',
	            items: [{
	              name: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_CUSTOM_KEYS_CHECKBOX_LABEL'),
	              value: 'useCustomKeys'
	            }]
	          })]
	        });
	      });
	    }
	  }, {
	    key: "showKeysCheckbox",
	    value: function showKeysCheckbox() {
	      this.getKeysCheckbox().getLayout().hidden = false;
	    }
	  }, {
	    key: "hideKeysCheckbox",
	    value: function hideKeysCheckbox() {
	      this.getKeysCheckbox().getLayout().hidden = true;
	    }
	  }, {
	    key: "getKeysForm",
	    value: function getKeysForm() {
	      var _this2 = this;

	      return this.cache.remember('keysForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          id: 'keys',
	          title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_TITLE'),
	          help: {
	            text: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_HELP_TEXT'),
	            href: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_HELP_HREF')
	          },
	          fields: [new landing_ui_field_textfield.TextField({
	            selector: 'key',
	            title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_RECAPTCHA_KEY_FIELD_TITLE'),
	            textOnly: true,
	            content: _this2.options.values.key
	          }), new landing_ui_field_textfield.TextField({
	            selector: 'secret',
	            title: landing_loc.Loc.getMessage('LANDING_SPAM_PROTECTION_RECAPTCHA_SECRET_KEY_FIELD_TITLE'),
	            textOnly: true,
	            content: _this2.options.values.secret
	          })]
	        });
	      });
	    }
	  }, {
	    key: "showKeysForm",
	    value: function showKeysForm() {
	      this.getKeysForm().getLayout().hidden = false;
	    }
	  }, {
	    key: "hideKeysForm",
	    value: function hideKeysForm() {
	      this.getKeysForm().getLayout().hidden = true;
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "valueReducer",
	    value: function valueReducer(sourceValue) {
	      var useCustomKeys = sourceValue.useCustomKeys.length > 0;
	      return {
	        recaptcha: {
	          use: sourceValue.use === 'hidden',
	          key: useCustomKeys ? sourceValue.key : '',
	          secret: useCustomKeys ? sourceValue.secret : ''
	        }
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return SpamProtection;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var yandexIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/analytics/images/yandex.svg";

	var googleIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/analytics/images/google.svg";

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-cell\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ContentTableCell = /*#__PURE__*/function () {
	  function ContentTableCell() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ContentTableCell);
	    this.options = babelHelpers.objectSpread({}, options);
	  }

	  babelHelpers.createClass(ContentTableCell, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject(), this.options.content);
	    }
	  }]);
	  return ContentTableCell;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-row", "\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ContentTableRow = /*#__PURE__*/function () {
	  function ContentTableRow(options) {
	    babelHelpers.classCallCheck(this, ContentTableRow);
	    this.options = babelHelpers.objectSpread({}, options);
	  }

	  babelHelpers.createClass(ContentTableRow, [{
	    key: "render",
	    value: function render() {
	      var headClass = this.options.head ? ' landing-ui-content-table-row-head' : '';
	      return main_core.Tag.render(_templateObject$1(), headClass, this.options.columns.map(function (cell) {
	        return cell.render();
	      }));
	    }
	  }]);
	  return ContentTableRow;
	}();

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-content-table-wrapper\">\n\t\t\t\t\n\t\t\t\t<div class=\"landing-ui-content-table\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-content-table-title\">", "</div>\n\t\t\t"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ContentTable = /*#__PURE__*/function () {
	  function ContentTable(options) {
	    babelHelpers.classCallCheck(this, ContentTable);
	    this.options = babelHelpers.objectSpread({}, options);
	    this.headRow = new ContentTableRow({
	      columns: this.options.columns.map(function (columnOptions) {
	        return new ContentTableCell(columnOptions);
	      }),
	      head: true
	    });
	    this.rows = this.options.rows.map(function (rowOptions) {
	      return new ContentTableRow({
	        columns: rowOptions.columns.map(function (cellOptions) {
	          return new ContentTableCell(cellOptions);
	        })
	      });
	    });
	  }

	  babelHelpers.createClass(ContentTable, [{
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      if (Type.isStringFilled(this.options.title)) {
	        return main_core.Tag.render(_templateObject$2(), this.options.title);
	      }

	      return '';
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject2(), this.headRow.render(), this.rows.map(function (row) {
	        return row.render();
	      }));
	    }
	  }]);
	  return ContentTable;
	}();

	var AnalyticsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(AnalyticsContent, _ContentWrapper);

	  function AnalyticsContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, AnalyticsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AnalyticsContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.AgreementsContent');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TITLE')
	    });
	    var items = [];

	    if (landing_loc.Loc.getMessage('LANGUAGE_ID') === 'ru') {
	      items.push({
	        id: 'yandex',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_YANDEX_METRIKA'),
	        icon: yandexIcon,
	        checked: true,
	        switcher: false,
	        content: _this.getYandexTable()
	      });
	    }

	    items.push({
	      id: 'google',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_GOOGLE_ANALYTICS'),
	      icon: googleIcon,
	      checked: true,
	      switcher: false,
	      content: _this.getGoogleTable()
	    });
	    var accordionField = new landing_ui_field_accordionfield.AccordionField({
	      selector: 'analytics',
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEMS_FIELD_TITLE'),
	      items: items
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'analyticsMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_DESCRIPTION'),
	      angle: false,
	      restoreState: true
	    });

	    _this.addItem(header);

	    _this.addItem(message);

	    _this.addItem(accordionField);

	    return _this;
	  }

	  babelHelpers.createClass(AnalyticsContent, [{
	    key: "getYandexTable",
	    value: function getYandexTable() {
	      var table = new ContentTable({
	        columns: [{
	          id: 'title',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE')
	        }, {
	          id: 'id',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE')
	        }],
	        rows: this.options.events.map(function (row) {
	          return {
	            columns: [{
	              content: row.name
	            }, {
	              content: row.event
	            }]
	          };
	        })
	      });
	      return table.render();
	    }
	  }, {
	    key: "getGoogleTable",
	    value: function getGoogleTable() {
	      var table = new ContentTable({
	        columns: [{
	          id: 'title',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE')
	        }, {
	          id: 'id',
	          content: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE')
	        }],
	        rows: this.options.events.map(function (row) {
	          return {
	            columns: [{
	              content: row.name
	            }, {
	              content: row.code
	            }]
	          };
	        })
	      });
	      return table.render();
	    }
	  }]);
	  return AnalyticsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var messageIcon$1 = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/fields/images/message-icon.svg";

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
	      icon: messageIcon$1,
	      restoreState: true
	    });
	    var fieldsForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      fields: [new landing_ui_field_fieldslistfield.FieldsListField({
	        selector: 'fields',
	        isLeadEnabled: _this.options.isLeadEnabled,
	        dictionary: _this.options.dictionary,
	        formOptions: babelHelpers.objectSpread({}, _this.options.formOptions),
	        crmFields: babelHelpers.objectSpread({}, _this.options.crmFields),
	        items: babelHelpers.toConsumableArray(_this.options.values.fields)
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

	var FieldsRulesContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(FieldsRulesContent, _ContentWrapper);

	  function FieldsRulesContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldsRulesContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsRulesContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsRulesContent');

	    _this.addItem(_this.getHeader());

	    if (!Object.keys(_this.options.values).length > 0) {
	      _this.addItem(_this.getRuleTypeField());
	    } else {
	      _this.addItem(_this.getRulesForm());

	      _this.addItem(_this.getActionPanel());

	      var values = _this.options.values.reduce(function (acc, group) {
	        group.list.forEach(function (item) {
	          var accEntry = acc.find(function (accItem) {
	            return (accItem.condition.field === item.condition.target || accItem.condition.field.id === item.condition.target) && accItem.condition.value === item.condition.value && accItem.condition.operator === item.condition.operation;
	          });

	          if (accEntry) {
	            accEntry.expression.push({
	              field: _this.options.fields.find(function (field) {
	                return field.id === item.action.target;
	              }),
	              action: item.action.type
	            });
	          } else {
	            acc.push({
	              condition: {
	                field: _this.options.fields.find(function (field) {
	                  return field.name === item.condition.target;
	                }),
	                value: item.condition.value,
	                operator: item.condition.operation
	              },
	              expression: [{
	                field: _this.options.fields.find(function (field) {
	                  return field.name === item.action.target;
	                }),
	                action: item.action.type
	              }]
	            });
	          }
	        });
	        return acc;
	      }, []);

	      _this.getRulesForm().addField(new landing_ui_field_rulefield.RuleField({
	        fields: _this.getFormFields(),
	        rules: values,
	        onRemove: _this.onFieldRemove.bind(babelHelpers.assertThisInitialized(_this)),
	        dictionary: _this.options.dictionary
	      }));
	    }

	    return _this;
	  }

	  babelHelpers.createClass(FieldsRulesContent, [{
	    key: "getFormFields",
	    value: function getFormFields() {
	      var _this2 = this;

	      var disallowedTypes = function () {
	        if (!main_core.Type.isPlainObject(_this2.options.dictionary.deps.field) || !main_core.Type.isArrayFilled(_this2.options.dictionary.deps.field.disallowed)) {
	          return null;
	        }

	        return _this2.options.dictionary.deps.field.disallowed;
	      }();

	      return this.options.fields.filter(function (field) {
	        return !main_core.Type.isArrayFilled(disallowedTypes) || !disallowedTypes.includes(field.type) && (!main_core.Type.isPlainObject(field.content) || disallowedTypes.includes(field.content.type));
	      });
	    }
	  }, {
	    key: "onFieldRemove",
	    value: function onFieldRemove(event) {
	      this.getRulesForm().removeField(event.getTarget());
	      this.clear();
	      var header = this.getHeader();
	      header.setBottomMargin(true);
	      this.addItem(header);
	      this.addItem(this.getRuleTypeField());
	    }
	  }, {
	    key: "getRulesForm",
	    value: function getRulesForm() {
	      return this.cache.remember('rulesForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          selector: 'dependencies',
	          description: null
	        });
	      });
	    }
	  }, {
	    key: "getActionPanel",
	    value: function getActionPanel() {
	      return this.cache.remember('actionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [// {
	            // 	text: Loc.getMessage('LANDING_FIELDS_ADD_NEW_RULE_LINK_LABEL'),
	            // 	onClick: this.onAddRuleClick.bind(this),
	            // },
	          ]
	        });
	      });
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('headerCard', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "onCreateRule",
	    value: function onCreateRule() {
	      this.clear();
	      var header = this.getHeader();
	      header.setBottomMargin(false);
	      this.addItem(header);
	      var ruleForm = this.getRulesForm();
	      ruleForm.addField(new landing_ui_field_rulefield.RuleField({
	        fields: this.getFormFields(),
	        rules: [],
	        onRemove: this.onFieldRemove.bind(this),
	        dictionary: this.options.dictionary
	      }));
	      this.addItem(ruleForm);
	      this.addItem(this.getActionPanel());
	    }
	  }, {
	    key: "getRuleTypeField",
	    value: function getRuleTypeField() {
	      var _this3 = this;

	      return this.cache.remember('ruleTypeField', function () {
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selector: 'rules-type',
	          items: [{
	            id: 'type1',
	            icon: 'landing-ui-rules-type1-icon',
	            title: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_1'),
	            button: {
	              text: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
	              onClick: _this3.onCreateRule.bind(_this3)
	            }
	          }, {
	            id: 'type2',
	            icon: 'landing-ui-rules-type2-icon',
	            title: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_2'),
	            button: {
	              text: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
	              onClick: _this3.onCreateRule.bind(_this3)
	            },
	            disabled: true,
	            soon: true
	          }, {
	            id: 'type3',
	            icon: 'landing-ui-rules-type3-icon',
	            title: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_3'),
	            button: {
	              text: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
	              onClick: _this3.onCreateRule.bind(_this3)
	            },
	            disabled: true,
	            soon: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onAddRuleClick",
	    value: function onAddRuleClick() {
	      var radioField = this.getRuleTypeField();
	      this.insertBefore(radioField, this.items[this.items.length - 1]);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        dependencies: Object.values(value).map(function (item) {
	          return {
	            typeId: 0,
	            list: item.reduce(function (acc, listItems) {
	              listItems.forEach(function (listItem) {
	                listItem.expression.forEach(function (expItem) {
	                  acc.push({
	                    condition: {
	                      target: listItem.condition.field,
	                      event: 'change',
	                      value: listItem.condition.value,
	                      operation: listItem.condition.operator
	                    },
	                    action: {
	                      target: expItem.field,
	                      type: expItem.action,
	                      value: ''
	                    }
	                  });
	                });
	              });
	              return acc;
	            }, [])
	          };
	        })
	      };
	    }
	  }]);
	  return FieldsRulesContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"", " ", "-", "\">\n\t\t\t\t<div class=\"", "-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"", "-inner\">\n\t\t\t\t\t<div class=\"", "-header\">\n\t\t\t\t\t\t<span class=\"", "-header-view\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "-icon\"></div>\n\t\t\t\t\t<div class=\"", "-text\" onclick=\"", "\" oninput=\"", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"", "-footer\">\n\t\t\t\t\t\t<span class=\"", "-footer-edit\" onclick=\"", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ActionPagesField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ActionPagesField, _BaseField);

	  function ActionPagesField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ActionPagesField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionPagesField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.ActionPagesField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.setLayoutClass('landing-ui-field-action-pages');

	    main_core.Dom.append(_this.getSuccess(), _this.input);
	    main_core.Dom.append(_this.getFailure(), _this.input);
	    main_core.Event.bind(document, 'click', _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(window.top.document, 'click', _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(ActionPagesField, [{
	    key: "onDocumentClick",
	    value: function onDocumentClick() {
	      var successInput = this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text');
	      var failureInput = this.getFailure().querySelector('.landing-ui-field-action-pages-page-text');
	      main_core.Dom.attr(successInput, 'contenteditable', null);
	      main_core.Dom.attr(failureInput, 'contenteditable', null);
	    }
	  }, {
	    key: "getSuccess",
	    value: function getSuccess() {
	      var _this2 = this;

	      return this.cache.remember('success', function () {
	        return ActionPagesField.createPageBlock({
	          type: 'success',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_SUCCESS_PAGE_TITLE'),
	          text: _this2.options.successText,
	          onInput: function onInput() {
	            _this2.emit('onChange');
	          },
	          onShowClick: function onShowClick() {
	            _this2.emit('onShowSuccess');
	          }
	        });
	      });
	    }
	  }, {
	    key: "getFailure",
	    value: function getFailure() {
	      var _this3 = this;

	      return this.cache.remember('failure', function () {
	        return ActionPagesField.createPageBlock({
	          type: 'failure',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_FAILURE_PAGE_TITLE'),
	          text: _this3.options.failureText,
	          onInput: function onInput() {
	            _this3.emit('onChange');
	          },
	          onShowClick: function onShowClick() {
	            _this3.emit('onShowFailure');
	          }
	        });
	      });
	    }
	  }, {
	    key: "getSuccessText",
	    value: function getSuccessText() {
	      return this.getSuccess().querySelector('.landing-ui-field-action-pages-page-text').innerText;
	    }
	  }, {
	    key: "getFailureText",
	    value: function getFailureText() {
	      return this.getFailure().querySelector('.landing-ui-field-action-pages-page-text').innerText;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        success: this.getSuccessText(),
	        failure: this.getFailureText()
	      };
	    }
	  }], [{
	    key: "createPageBlock",
	    value: function createPageBlock(options) {
	      var classPrefix = 'landing-ui-field-action-pages-page';

	      var onEditClick = function onEditClick(event) {
	        event.preventDefault();
	        event.stopPropagation();
	        var inner = event.currentTarget.closest(".".concat(classPrefix, "-inner"));
	        var textContainer = inner.querySelector(".".concat(classPrefix, "-text"));
	        main_core.Dom.attr(textContainer, 'contenteditable', !textContainer.isContentEditable);

	        if (main_core.Type.isFunction(options.onEditClick)) {
	          options.onEditClick(event);
	        }
	      };

	      var onEditorClick = function onEditorClick(event) {
	        event.stopPropagation();
	      };

	      var onViewClick = function onViewClick(event) {
	        event.preventDefault();

	        if (main_core.Type.isFunction(options.onShowClick)) {
	          options.onShowClick(event);
	        }
	      };

	      return main_core.Tag.render(_templateObject$3(), classPrefix, classPrefix, options.type, classPrefix, options.title, classPrefix, classPrefix, classPrefix, onViewClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_SHOW'), classPrefix, classPrefix, onEditorClick, options.onInput, main_core.Text.encode(options.text), classPrefix, classPrefix, onEditClick, landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_EDIT_PAGE_EDIT'));
	    }
	  }]);
	  return ActionPagesField;
	}(landing_ui_field_basefield.BaseField);

	var type1Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/actions/images/type1.svg";

	var type2Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/actions/images/type2.svg";

	var ActionsContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(ActionsContent, _ContentWrapper);

	  function ActionsContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ActionsContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ActionsContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ActionsContent');

	    main_core.Dom.addClass(_this.getLayout(), 'landing-ui-actions-content-wrapper');

	    _this.addItem(_this.getHeader());

	    _this.addItem(_this.getTypeButtons());

	    _this.getActionPages().subscribe('onShowSuccess', function () {
	      _this.emit('onShowSuccess');
	    }).subscribe('onShowFailure', function () {
	      _this.emit('onShowFailure');
	    });

	    return _this;
	  }

	  babelHelpers.createClass(ActionsContent, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getTypeButtons",
	    value: function getTypeButtons() {
	      var _this2 = this;

	      return this.cache.remember('typeButtons', function () {
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selectable: true,
	          value: function () {
	            if (main_core.Type.isStringFilled(_this2.options.values.success.url) && main_core.Type.isStringFilled(_this2.options.values.failure.url)) {
	              return 'type2';
	            }

	            return 'type1';
	          }(),
	          items: [{
	            id: 'type1',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_1'),
	            icon: 'landing-ui-form-actions-type1'
	          }, {
	            id: 'type2',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_2'),
	            icon: 'landing-ui-form-actions-type2'
	          }],
	          onChange: _this2.onTypeChange.bind(_this2)
	        });
	      });
	    }
	  }, {
	    key: "getCheckbox",
	    value: function getCheckbox() {
	      return this.cache.remember('checkbox', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_CHECKBOX_TITLE'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getTypeDropdown",
	    value: function getTypeDropdown() {
	      var _this3 = this;

	      return this.cache.remember('typeDropdown', function () {
	        var field = new landing_ui_field_presetfield.PresetField({
	          events: {
	            onClick: function onClick() {
	              _this3.clear();

	              _this3.addItem(_this3.getHeader());

	              _this3.addItem(_this3.getTypeButtons());
	            }
	          }
	        });
	        field.setTitle(landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_DROPDOWN_TITLE'));
	        field.setIcon(type1Icon);
	        return field;
	      });
	    }
	  }, {
	    key: "getSuccessLinkField",
	    value: function getSuccessLinkField() {
	      var _this4 = this;

	      return this.cache.remember('successLinkField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_SUCCESS_FIELD_TITLE'),
	          placeholder: 'http://',
	          textOnly: true,
	          content: _this4.options.values.success.url,
	          onInput: _this4.onChange.bind(_this4)
	        });
	      });
	    }
	  }, {
	    key: "getFailureLinkField",
	    value: function getFailureLinkField() {
	      var _this5 = this;

	      return this.cache.remember('failureLinkField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_FAILURE_FIELD_TITLE'),
	          placeholder: 'http://',
	          textOnly: true,
	          content: _this5.options.values.failure.url,
	          onInput: _this5.onChange.bind(_this5)
	        });
	      });
	    }
	  }, {
	    key: "getDelayField",
	    value: function getDelayField() {
	      var _this6 = this;

	      return this.cache.remember('delayField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'redirectDelay',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_DELAY_TITLE'),
	          content: _this6.options.values.redirectDelay,
	          items: Array.from({
	            length: 11
	          }, function (item, index) {
	            return {
	              name: "".concat(index, " ").concat(landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_DELAY_ITEM')),
	              value: index
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getActionPages",
	    value: function getActionPages() {
	      var _this7 = this;

	      return this.cache.remember('actionPages', function () {
	        return new ActionPagesField({
	          successText: _this7.options.values.success.text,
	          failureText: _this7.options.values.failure.text,
	          onChange: _this7.onChange.bind(_this7)
	        });
	      });
	    }
	  }, {
	    key: "onTypeChange",
	    value: function onTypeChange(event) {
	      var data = event.getData();
	      var typeDropdown = this.getTypeDropdown();
	      this.clear();
	      this.addItem(this.getHeader());
	      this.addItem(typeDropdown);
	      typeDropdown.setLinkText(data.item.title.replace(/&nbsp;/, ' '));

	      if (data.item.id === 'type1') {
	        typeDropdown.setIcon(type1Icon);
	        this.addItem(this.getActionPages());
	      }

	      if (data.item.id === 'type2') {
	        typeDropdown.setIcon(type2Icon);
	        this.addItem(this.getSuccessLinkField());
	        this.addItem(this.getFailureLinkField());
	        this.addItem(this.getDelayField());
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var actionPagesValue = this.getActionPages().getValue();
	      var useRedirect = this.getTypeButtons().getValue() === 'type2';
	      return {
	        result: {
	          success: {
	            text: actionPagesValue.success,
	            url: useRedirect ? main_core.Text.decode(this.getSuccessLinkField().getValue()) : ''
	          },
	          failure: {
	            text: actionPagesValue.failure,
	            url: useRedirect ? main_core.Text.decode(this.getFailureLinkField().getValue()) : ''
	          },
	          redirectDelay: this.getDelayField().getValue()
	        }
	      };
	    }
	  }]);
	  return ActionsContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-embed-header\">\n\t\t\t\t\t<div class=\"landing-ui-field-embed-header-button\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EmbedField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(EmbedField, _BaseField);

	  function EmbedField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, EmbedField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EmbedField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.EmbedField');

	    _this.setLayoutClass('landing-ui-field-embed');

	    main_core.Dom.clean(_this.layout);
	    main_core.Dom.append(_this.getHeader(), _this.layout);
	    main_core.Dom.append(_this.input, _this.layout);
	    main_core.Event.bind(_this.input, 'click', _this.onInputClick.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(EmbedField, [{
	    key: "getCopyButton",
	    value: function getCopyButton() {
	      var _this2 = this;

	      return this.cache.remember('copyButton', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_COPY_BUTTON_LABEL'),
	          size: ui_buttons.Button.Size.SMALL,
	          color: ui_buttons.Button.Color.PRIMARY,
	          onclick: _this2.onCopyButtonClick.bind(_this2)
	        });
	      });
	    }
	  }, {
	    key: "onCopyButtonClick",
	    value: function onCopyButtonClick() {
	      this.selectCode();
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      rootWindow.document.execCommand('copy');
	      rootWindow.getSelection().removeAllRanges();
	      var button = this.getCopyButton();
	      button.setColor(ui_buttons.Button.Color.LINK);
	      button.setIcon(ui_buttons.Button.Icon.DONE);
	      button.setText(landing_loc.Loc.getMessage('LANDING_FORM_EMBED_COPIED_BUTTON_LABEL'));
	      main_core.Dom.addClass(this.getHeader(), 'landing-ui-field-embed-header-copied');
	    }
	  }, {
	    key: "onInputClick",
	    value: function onInputClick(event) {
	      event.preventDefault();
	      this.selectCode();
	    }
	  }, {
	    key: "selectCode",
	    value: function selectCode() {
	      landing_pageobject.PageObject.getRootWindow().getSelection().selectAllChildren(this.input);
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      var _this3 = this;

	      return this.cache.remember('header', function () {
	        return main_core.Tag.render(_templateObject$4(), _this3.getCopyButton().render());
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue() {
	      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      this.input.innerHTML = main_core.Text.encode(value);
	    }
	  }]);
	  return EmbedField;
	}(landing_ui_field_basefield.BaseField);

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-filed-widget-wrapper\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var WidgetField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(WidgetField, _BaseField);

	  function WidgetField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, WidgetField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(WidgetField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Filed.WidgetField');

	    _this.setLayoutClass('landing-ui-filed-widget');

	    main_core.Dom.append(_this.getInputWrapper(), _this.layout);
	    return _this;
	  }

	  babelHelpers.createClass(WidgetField, [{
	    key: "getInputWrapper",
	    value: function getInputWrapper() {
	      var _this2 = this;

	      return this.cache.remember('inputWrapper', function () {
	        return main_core.Tag.render(_templateObject$5(), _this2.input, _this2.getSettingsButton().render());
	      });
	    }
	  }, {
	    key: "getSettingsButton",
	    value: function getSettingsButton() {
	      return this.cache.remember('settingsButton', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_WIDGET_BUTTON_LABEL'),
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          icon: ui_buttons.Button.Icon.SETTING
	        });
	      });
	    }
	  }]);
	  return WidgetField;
	}(landing_ui_field_basefield.BaseField);

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-copy-link-preview\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var CopyLinkField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(CopyLinkField, _BaseField);

	  function CopyLinkField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CopyLinkField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CopyLinkField).call(this, options));

	    _this.setLayoutClass('landing-ui-field-copy-link');

	    main_core.Dom.clean(_this.layout);
	    main_core.Dom.append(_this.getLink(), _this.layout);
	    main_core.Dom.append(_this.getCopyButton().render(), _this.layout);
	    return _this;
	  }

	  babelHelpers.createClass(CopyLinkField, [{
	    key: "getLink",
	    value: function getLink() {
	      var _this2 = this;

	      return this.cache.remember('link', function () {
	        var link = new landing_ui_component_link.Link({
	          text: _this2.options.link,
	          href: _this2.options.link,
	          target: '_blank'
	        });
	        return main_core.Tag.render(_templateObject$6(), link.getLayout());
	      });
	    }
	  }, {
	    key: "getCopyButton",
	    value: function getCopyButton() {
	      var _this3 = this;

	      return this.cache.remember('copyButton', function () {
	        return new ui_buttons.Button({
	          color: ui_buttons.Button.Color.PRIMARY,
	          size: ui_buttons.Button.Size.SMALL,
	          text: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_PUB_COPY_LINK_BUTTON_LABEL'),
	          onclick: _this3.onCopyButtonClick.bind(_this3)
	        });
	      });
	    }
	  }, {
	    key: "selectCode",
	    value: function selectCode() {
	      landing_pageobject.PageObject.getRootWindow().getSelection().selectAllChildren(this.getLink());
	    }
	  }, {
	    key: "onCopyButtonClick",
	    value: function onCopyButtonClick() {
	      this.selectCode();
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      rootWindow.document.execCommand('copy');
	      rootWindow.getSelection().removeAllRanges();
	      var button = this.getCopyButton();
	      button.setColor(ui_buttons.Button.Color.LINK);
	      button.setIcon(ui_buttons.Button.Icon.DONE);
	      button.setText(landing_loc.Loc.getMessage('LANDING_FORM_EMBED_PUB_COPIED_LINK_BUTTON_LABEL'));
	    }
	  }]);
	  return CopyLinkField;
	}(landing_ui_field_basefield.BaseField);

	function _templateObject$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-position-fields-inner\"></div>\n\t\t\t"]);

	  _templateObject$7 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var PositionField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(PositionField, _BaseField);

	  function PositionField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PositionField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PositionField).call(this, options));
	    _this.horizontal = new BX.Landing.UI.Field.Dropdown({
	      selector: 'horizontal',
	      content: options.value.horizontal,
	      items: [{
	        name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_LEFT'),
	        value: 'left'
	      }, {
	        name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_CENTER'),
	        value: 'center'
	      }, {
	        name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_RIGHT'),
	        value: 'right'
	      }]
	    });
	    _this.vertical = new BX.Landing.UI.Field.Dropdown({
	      selector: 'vertical',
	      content: options.value.vertical,
	      items: [{
	        name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_TOP'),
	        value: 'top'
	      }, {
	        name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_BOTTOM'),
	        value: 'bottom'
	      }]
	    });

	    _this.horizontal.subscribe('onChange', function () {
	      _this.emit('onChange');
	    });

	    _this.vertical.subscribe('onChange', function () {
	      _this.emit('onChange');
	    });

	    var fieldsInner = _this.getFieldsInner();

	    main_core.Dom.append(_this.horizontal.getLayout(), fieldsInner);
	    main_core.Dom.append(_this.vertical.getLayout(), fieldsInner);
	    main_core.Dom.replace(_this.input, fieldsInner);
	    return _this;
	  }

	  babelHelpers.createClass(PositionField, [{
	    key: "getFieldsInner",
	    value: function getFieldsInner() {
	      return this.cache.remember('fieldsInner', function () {
	        return main_core.Tag.render(_templateObject$7());
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        vertical: this.vertical.getValue(),
	        horizontal: this.horizontal.getValue()
	      };
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.vertical.setValue(value.vertical);
	      this.horizontal.setValue(value.horizontal);
	    }
	  }]);
	  return PositionField;
	}(landing_ui_field_basefield.BaseField);

	var type1icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type1.svg";

	var type2icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type2.svg";

	var type3icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type3.svg";

	var type4icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type4.svg";

	var type5icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type5.svg";

	var type6icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/embed/images/type6.svg";

	var EmbedContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(EmbedContent, _ContentWrapper);

	  function EmbedContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, EmbedContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EmbedContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.EmbedContent');

	    main_core.Dom.addClass(_this.getLayout(), 'landing-ui-embed-content-wrapper');

	    _this.addItem(_this.getHeader());

	    _this.addItem(_this.getTypeButtons());

	    return _this;
	  }

	  babelHelpers.createClass(EmbedContent, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getTypeButtons",
	    value: function getTypeButtons() {
	      var _this2 = this;

	      return this.cache.remember('typeButtons', function () {
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_FIELD_TITLE'),
	          items: [1, 3, 4, 2, 5, 6].map(function (item) {
	            return {
	              id: "type".concat(item),
	              title: landing_loc.Loc.getMessage("LANDING_FORM_EMBED_TYPE_".concat(item)),
	              icon: "landing-ui-form-embed-type".concat(item),
	              soon: [2, 5, 6].includes(item),
	              disabled: [2, 5, 6].includes(item)
	            };
	          }),
	          onChange: _this2.onTypeChange.bind(_this2),
	          selectable: false
	        });
	      });
	    }
	  }, {
	    key: "getTypeDropdown",
	    value: function getTypeDropdown() {
	      var _this3 = this;

	      return this.cache.remember('typeDropdown', function () {
	        var field = new landing_ui_field_presetfield.PresetField({
	          events: {
	            onClick: function onClick() {
	              _this3.clear();

	              _this3.addItem(_this3.getHeader());

	              _this3.addItem(_this3.getTypeButtons());
	            }
	          }
	        });
	        field.setTitle(landing_loc.Loc.getMessage('LANDING_FORM_ACTIONS_TYPE_DROPDOWN_TITLE'));
	        return field;
	      });
	    }
	  }, {
	    key: "getType1Message",
	    value: function getType1Message() {
	      return this.cache.remember('type1Message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_1_MESSAGE_TITLE'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_1_MESSAGE_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "getType3Message",
	    value: function getType3Message() {
	      return this.cache.remember('type3Message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_3_MESSAGE_TITLE'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_3_MESSAGE_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "getType4Message",
	    value: function getType4Message() {
	      return this.cache.remember('type4Message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TITLE'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "getType5Message",
	    value: function getType5Message() {
	      return this.cache.remember('type5Message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TITLE'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_4_MESSAGE_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "getType8Message",
	    value: function getType8Message() {
	      return this.cache.remember('type8Message', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_8_MESSAGE_TITLE'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_TYPE_8_MESSAGE_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "getEmbedField",
	    value: function getEmbedField() {
	      return this.cache.remember('embedField', function () {
	        return new EmbedField({});
	      });
	    }
	  }, {
	    key: "getType2Header",
	    value: function getType2Header() {
	      return this.cache.remember('type2header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_LINK_SETTINGS_HEADER'),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getType3Header",
	    value: function getType3Header() {
	      return this.cache.remember('type3header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_CLICK_SETTINGS_HEADER'),
	          level: 2,
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_SETTINGS_DESCRIPTION')
	        });
	      });
	    }
	  }, {
	    key: "getType4Header",
	    value: function getType4Header() {
	      return this.cache.remember('type4header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_AUTO_SHOW_SETTINGS_HEADER'),
	          level: 2,
	          description: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_SETTINGS_DESCRIPTION')
	        });
	      });
	    }
	  }, {
	    key: "getLinkTextField",
	    value: function getLinkTextField() {
	      return this.cache.remember('linkTextField', function () {
	        return new landing_ui_field_variablesfield.VariablesField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_LINK_TEXT_SETTINGS_FIELD_TITLE'),
	          variables: [{
	            name: 'Test',
	            value: 'test'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getType4Checkbox",
	    value: function getType4Checkbox() {
	      var _this4 = this;

	      return this.cache.remember('type4checkbox', function () {
	        var field = new BX.Landing.UI.Field.Radio({
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_PAGE_LOADED'),
	            value: 'pageLoaded'
	          }, {
	            html: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_TIME'),
	            value: 'afterTime'
	          } // {
	          // 	name: Loc.getMessage('LANDING_FORM_EMBED_SHOW_AFTER_SCROLL_TO_ANCHOR'),
	          // 	value: 'scrollToAnchor',
	          // },
	          ],
	          value: function () {
	            if (_this4.options.values.views.auto.delay > 0) {
	              return ['afterTime'];
	            }

	            return ['pageLoaded'];
	          }()
	        });
	        main_core.Dom.replace(field.layout.querySelector('.delay_time'), _this4.getDelayDropdown().getLayout());
	        return field;
	      });
	    }
	  }, {
	    key: "getWidgetField",
	    value: function getWidgetField() {
	      return this.cache.remember('widgetField', function () {
	        return new WidgetField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_WIDGET_FIELD_TITLE'),
	          placeholder: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_WIDGET_FIELD_PLACEHOLDER')
	        });
	      });
	    }
	  }, {
	    key: "getCopyLinkField",
	    value: function getCopyLinkField() {
	      return this.cache.remember('copyLinkField', function () {
	        return new CopyLinkField({
	          link: 'https://bitrix24.io/pub/my/form/link'
	        });
	      });
	    }
	  }, {
	    key: "getDelayDropdown",
	    value: function getDelayDropdown() {
	      var _this5 = this;

	      return this.cache.remember('delayDropdown', function () {
	        return new BX.Landing.UI.Field.DropdownInline({
	          selector: 'showDelay',
	          content: _this5.options.values.views.auto.delay,
	          items: [{
	            name: '5c',
	            value: '5'
	          }, {
	            name: '10c',
	            value: '10'
	          }, {
	            name: '15c',
	            value: '15'
	          }],
	          onChange: _this5.onChange.bind(_this5),
	          skipInitialEvent: true
	        });
	      });
	    }
	  }, {
	    key: "getType3PositionField",
	    value: function getType3PositionField() {
	      var _this6 = this;

	      return this.cache.remember('type3PositionField', function () {
	        return new PositionField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_FIELD_TITLE'),
	          value: {
	            vertical: _this6.options.values.views.click.vertical,
	            horizontal: _this6.options.values.views.click.position
	          }
	        });
	      });
	    }
	  }, {
	    key: "getType4PositionField",
	    value: function getType4PositionField() {
	      var _this7 = this;

	      return this.cache.remember('type4PositionField', function () {
	        return new PositionField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_POSITION_FIELD_TITLE'),
	          value: {
	            vertical: _this7.options.values.views.auto.vertical,
	            horizontal: _this7.options.values.views.auto.position
	          }
	        });
	      });
	    }
	  }, {
	    key: "getType3ShowTypeField",
	    value: function getType3ShowTypeField() {
	      var _this8 = this;

	      return this.cache.remember('type3ShowTypeField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_TYPE'),
	          selector: 'type',
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_POPUP'),
	            value: 'popup'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_SLIDER'),
	            value: 'panel'
	          }],
	          content: _this8.options.values.views.click.type
	        });
	      });
	    }
	  }, {
	    key: "getType4ShowTypeField",
	    value: function getType4ShowTypeField() {
	      var _this9 = this;

	      return this.cache.remember('type4ShowTypeField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_TYPE'),
	          selector: 'type',
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_POPUP'),
	            value: 'popup'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_EMBED_SHOW_SLIDER'),
	            value: 'panel'
	          }],
	          content: _this9.options.values.views.auto.type
	        });
	      });
	    }
	  }, {
	    key: "onTypeChange",
	    value: function onTypeChange(event) {
	      var data = event.getData();
	      var typeDropdown = this.getTypeDropdown();

	      if (/type[1-6]$/.test(data.item.id)) {
	        this.clear();
	        this.addItem(this.getHeader());
	        this.addItem(typeDropdown);
	      }

	      typeDropdown.setLinkText(data.item.title.replace(/&nbsp;/, ' '));
	      var embedField = this.getEmbedField();

	      if (data.item.id === 'type1') {
	        typeDropdown.setIcon(type1icon);
	        this.addItem(this.getType1Message());
	        embedField.setValue(this.options.values.scripts.inline.text);
	        this.addItem(embedField);
	      }

	      if (data.item.id === 'type2') {
	        typeDropdown.setIcon(type2icon); // this.addItem(this.getType2Header());
	        // this.addItem(this.getLinkTextField());

	        embedField.setValue(this.options.values.scripts.click.text);
	        this.addItem(embedField);
	      }

	      if (data.item.id === 'type3') {
	        typeDropdown.setIcon(type3icon); // this.addItem(this.getLinkTextField());

	        embedField.setValue(this.options.values.scripts.click.text);
	        var positionField = this.getType3PositionField();
	        positionField.setValue({
	          vertical: this.options.values.views.click.vertical,
	          horizontal: this.options.values.views.click.position
	        });
	        this.addItem(this.getType3Message());
	        this.addItem(this.getType3Header());
	        this.addItem(positionField);
	        this.addItem(this.getType3ShowTypeField());
	        this.addItem(embedField);
	      }

	      if (data.item.id === 'type4') {
	        typeDropdown.setIcon(type4icon);
	        this.addItem(this.getType4Message());
	        this.addItem(this.getType4Header());
	        this.addItem(this.getType4Checkbox());

	        var _positionField = this.getType4PositionField();

	        _positionField.setValue({
	          vertical: this.options.values.views.auto.vertical,
	          horizontal: this.options.values.views.auto.position
	        });

	        this.addItem(_positionField);
	        this.addItem(this.getType4ShowTypeField());
	        embedField.setValue(this.options.values.scripts.auto.text);
	        this.addItem(embedField);
	      }

	      if (data.item.id === 'type5') {
	        typeDropdown.setIcon(type5icon);
	        this.addItem(this.getType5Message());
	        this.addItem(this.getWidgetField());
	        embedField.setValue(this.options.values.scripts.auto.text);
	        this.addItem(embedField);
	      }

	      if (data.item.id === 'type6') {
	        typeDropdown.setIcon(type6icon);
	        this.addItem(this.getCopyLinkField());
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this10 = this;

	      var type3positionValue = this.getType3PositionField().getValue();
	      var type4positionValue = this.getType4PositionField().getValue();
	      return {
	        embedding: {
	          views: {
	            auto: {
	              delay: function () {
	                if (_this10.getType4Checkbox().getValue().includes('pageLoaded')) {
	                  return 0;
	                }

	                return _this10.getDelayDropdown().getValue();
	              }(),
	              position: type4positionValue.horizontal,
	              vertical: type4positionValue.vertical,
	              type: this.getType4ShowTypeField().getValue()
	            },
	            click: {
	              position: type3positionValue.horizontal,
	              vertical: type3positionValue.vertical,
	              type: this.getType3ShowTypeField().getValue()
	            }
	          }
	        }
	      };
	    }
	  }]);
	  return EmbedContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var messageIcon$2 = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/identify/images/icon.svg";

	var Identify = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Identify, _ContentWrapper);

	  function Identify(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Identify);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Identify).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Identify');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_IDENTIFY_HEADER')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      header: landing_loc.Loc.getMessage('LANDING_IDENTIFY_MESSAGE_HEADER'),
	      description: landing_loc.Loc.getMessage('LANDING_IDENTIFY_MESSAGE_DESCRIPTION'),
	      icon: messageIcon$2,
	      angle: false,
	      closeable: false,
	      more: function more() {
	        var helper$$1 = main_core.Reflection.getClass('top.BX.Helper');

	        if (helper$$1) {
	          BX.Helper.show('redirect=detail&code=12802786');
	        }
	      }
	    });

	    _this.addItem(header);

	    _this.addItem(message);

	    return _this;
	  }

	  return Identify;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	function _templateObject$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-stages\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$8 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var StageField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(StageField, _BaseField);

	  function StageField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, StageField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StageField).call(this, options));
	    main_core.Dom.replace(_this.input, _this.getInner());
	    return _this;
	  }

	  babelHelpers.createClass(StageField, [{
	    key: "getInner",
	    value: function getInner() {
	      var _this2 = this;

	      return this.cache.remember('inner', function () {
	        return main_core.Tag.render(_templateObject$8(), _this2.getCategoriesDropdown().getLayout());
	      });
	    }
	  }, {
	    key: "getCategoriesDropdown",
	    value: function getCategoriesDropdown() {
	      var _this3 = this;

	      return this.cache.remember('categoriesDropdown', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          title: _this3.options.listTitle || landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CATEGORIES_FIELD_TITLE'),
	          content: _this3.options.value.category,
	          items: _this3.options.categories.map(function (category) {
	            return {
	              name: category.NAME || category.name,
	              value: category.ID || category.id
	            };
	          }),
	          onChange: _this3.onCategoryChange.bind(_this3)
	        });
	      });
	    }
	  }, {
	    key: "getCurrentCategory",
	    value: function getCurrentCategory() {
	      var currentCategoryId = this.getCategoriesDropdown().getValue();
	      return this.options.categories.find(function (category) {
	        return String(category.ID || category.id) === String(currentCategoryId);
	      });
	    }
	  }, {
	    key: "onCategoryChange",
	    value: function onCategoryChange() {
	      var oldStagesDropdown = this.getStagesDropdown();
	      this.cache.delete('stagesDropdown');

	      if (oldStagesDropdown.popup) {
	        oldStagesDropdown.popup.destroy();
	      }

	      var newStagesDropdown = this.getStagesDropdown();
	      main_core.Dom.replace(oldStagesDropdown.getLayout(), newStagesDropdown.getLayout());
	      this.emit('onChange');
	    }
	  }, {
	    key: "getStagesDropdown",
	    value: function getStagesDropdown() {
	      var _this4 = this;

	      return this.cache.remember('stagesDropdown', function () {
	        var stages = _this4.getCurrentCategory().STAGES || _this4.getCurrentCategory().stages;

	        return new BX.Landing.UI.Field.Dropdown({
	          title: _this4.options.listTitle || landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_STAGES_FIELD_TITLE'),
	          items: stages.map(function (stage) {
	            return {
	              name: stage.NAME || stage.name,
	              value: stage.ID || stage.id
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        category: this.getCategoriesDropdown().getValue(),
	        stage: this.getStagesDropdown().getValue()
	      };
	    }
	  }]);
	  return StageField;
	}(landing_ui_field_basefield.BaseField);

	var messageIcon$3 = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/internal/content/crm/images/message-icon.svg";

	var CrmContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(CrmContent, _ContentWrapper);

	  function CrmContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, CrmContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CrmContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.CrmContent');

	    _this.addItem(_this.getHeader());

	    _this.addItem(_this.getTypesField());

	    if (_this.isDynamicAvailable()) {
	      _this.addItem(_this.getDynamicEntitySettingsForm());
	    }

	    _this.addItem(_this.getExpertSettingsForm());

	    _this.addItem(_this.getOrderSettingsForm());

	    return _this;
	  }

	  babelHelpers.createClass(CrmContent, [{
	    key: "isDynamicAvailable",
	    value: function isDynamicAvailable() {
	      return main_core.Type.isArrayFilled(this.options.formDictionary.document.dynamic);
	    }
	  }, {
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getDuplicatesField",
	    value: function getDuplicatesField() {
	      var _this2 = this;

	      return this.cache.remember('duplicatesField', function () {
	        return new BX.Landing.UI.Field.Radio({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_FIELD_TITLE'),
	          selector: 'duplicateMode',
	          value: [_this2.options.values.duplicateMode ? _this2.options.values.duplicateMode : 'ALLOW'],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_ALLOW'),
	            value: 'ALLOW'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_REPLACE'),
	            value: 'REPLACE'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DUPLICATES_MERGE'),
	            value: 'MERGE'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getPaymentField",
	    value: function getPaymentField() {
	      var _this3 = this;

	      return this.cache.remember('paymentField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'payment',
	          value: [_this3.options.values.payment],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_SHOW_PAYMENT'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getOrderSettingsForm",
	    value: function getOrderSettingsForm() {
	      var _this4 = this;

	      return this.cache.remember('formSettingsForm', function () {
	        var scheme = _this4.getSchemeById(_this4.options.values.scheme);

	        var isOpened = function () {
	          if (scheme && scheme.dynamic === true) {
	            return String(scheme.id).endsWith('1');
	          }

	          return main_core.Text.toNumber(_this4.options.values.scheme) > 4;
	        }();

	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_HEADER'),
	          toggleable: true,
	          opened: isOpened,
	          fields: [_this4.getPaymentField(), new landing_ui_card_messagecard.MessageCard({
	            id: 'orderMessage',
	            header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_MESSAGE_HEADER'),
	            description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_MESSAGE_DESCRIPTION'),
	            angle: false,
	            icon: messageIcon$3,
	            restoreState: true
	          })]
	        });
	      });
	    }
	  }, {
	    key: "getType1Header",
	    value: function getType1Header() {
	      return this.cache.remember('type1header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1').replace('&nbsp;', ' '),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getType2Header",
	    value: function getType2Header() {
	      return this.cache.remember('type2header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_2').replace('&nbsp;', ' '),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getType3Header",
	    value: function getType3Header() {
	      return this.cache.remember('type3header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_3').replace('&nbsp;', ' '),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getType4Header",
	    value: function getType4Header() {
	      return this.cache.remember('type4header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4').replace('&nbsp;', ' '),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getDynamicHeader",
	    value: function getDynamicHeader(headerText) {
	      var header = this.cache.remember('dynamicHeader', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: '',
	          level: 2
	        });
	      });

	      if (main_core.Type.isString(headerText)) {
	        header.setTitle(headerText);
	      }

	      return header;
	    }
	  }, {
	    key: "getDynamicEntitiesField",
	    value: function getDynamicEntitiesField() {
	      var _this5 = this;

	      return this.cache.remember('dynamicEntitiesField', function () {
	        var currentScheme = _this5.getSchemeById(_this5.options.values.scheme);

	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'dynamicScheme',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_SMART_ENTITY_LIST'),
	          items: _this5.options.formDictionary.document.dynamic.map(function (scheme) {
	            return {
	              name: scheme.name,
	              value: scheme.id
	            };
	          }),
	          content: currentScheme.mainEntity,
	          onChange: function onChange() {
	            _this5.onTypeChange(new main_core_events.BaseEvent({
	              data: {
	                item: {
	                  id: _this5.getSelectedSchemeId()
	                }
	              }
	            }));
	          }
	        });
	      });
	    }
	  }, {
	    key: "getDynamicEntitySettingsForm",
	    value: function getDynamicEntitySettingsForm() {
	      var _this6 = this;

	      return this.cache.remember('dynamicEntitySettingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          opened: true,
	          hidden: true,
	          fields: [_this6.getDynamicEntitiesField()]
	        });
	      });
	    }
	  }, {
	    key: "getExpertSettingsForm",
	    value: function getExpertSettingsForm() {
	      var _this7 = this;

	      return this.cache.remember('expertSettingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_EXPERT_MODE'),
	          toggleable: true,
	          toggleableType: landing_ui_form_formsettingsform.FormSettingsForm.ToggleableType.Link,
	          opened: false,
	          fields: [new landing_ui_card_headercard.HeaderCard({
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1').replace('&nbsp;', ' '),
	            level: 2
	          }), _this7.getDuplicatesField()]
	        });
	      });
	    }
	  }, {
	    key: "getTypesField",
	    value: function getTypesField() {
	      var _this8 = this;

	      return this.cache.remember('typesField', function () {
	        setTimeout(function () {
	          _this8.onTypeChange(new main_core_events.BaseEvent({
	            data: {
	              item: {
	                id: _this8.options.values.scheme
	              }
	            }
	          }));
	        });
	        var items = [{
	          id: '2',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_2'),
	          icon: 'landing-ui-crm-entity-type2'
	        }, {
	          id: '3',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_3'),
	          icon: 'landing-ui-crm-entity-type3'
	        }, {
	          id: '4',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4'),
	          icon: 'landing-ui-crm-entity-type4'
	        }];

	        if (_this8.isDynamicAvailable()) {
	          items.push({
	            id: 'smart',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_5'),
	            icon: 'landing-ui-crm-entity-type5'
	          });
	        }

	        if (_this8.options.isLeadEnabled) {
	          items.unshift({
	            id: '1',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1'),
	            icon: 'landing-ui-crm-entity-type1'
	          });
	        }

	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selector: 'scheme',
	          value: function () {
	            if (String(_this8.options.values.scheme) === '8') {
	              return 1;
	            }

	            if (String(_this8.options.values.scheme) === '5') {
	              return 2;
	            }

	            if (String(_this8.options.values.scheme) === '6') {
	              return 3;
	            }

	            if (String(_this8.options.values.scheme) === '7') {
	              return 4;
	            }

	            var scheme = _this8.getSchemeById(_this8.options.values.scheme);

	            if (main_core.Type.isPlainObject(scheme) && scheme.dynamic === true) {
	              return 'smart';
	            }

	            return _this8.options.values.scheme;
	          }(),
	          items: items,
	          onChange: _this8.onTypeChange.bind(_this8)
	        });
	      });
	    }
	  }, {
	    key: "getStagesField",
	    value: function getStagesField() {
	      var _this9 = this;

	      return this.cache.remember('stagesField', function () {
	        return new StageField({
	          categories: _this9.options.categories,
	          value: {
	            category: _this9.options.values.category
	          }
	        });
	      });
	    }
	  }, {
	    key: "getDynamicCategoriesField",
	    value: function getDynamicCategoriesField(schemeId) {
	      var _this10 = this;

	      return this.cache.remember("dynamicCategories#".concat(schemeId), function () {
	        var scheme = _this10.getDynamicSchemeById(schemeId);

	        return new StageField({
	          listTitle: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_SMART_STAGES_FIELD_TITLE'),
	          categories: scheme.categories,
	          value: {
	            category: _this10.options.values.dynamicCategory
	          }
	        });
	      });
	    }
	  }, {
	    key: "getDuplicatesEnabledField",
	    value: function getDuplicatesEnabledField() {
	      var _this11 = this;

	      return this.cache.remember('duplicatesEnabledField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'duplicatesEnabled',
	          compact: true,
	          value: [_this11.options.values.duplicatesEnabled],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_DUPLICATES_ENABLED'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getSchemeById",
	    value: function getSchemeById(id) {
	      return this.options.formDictionary.document.schemes.find(function (scheme) {
	        return String(scheme.id) === String(id) || id === 'smart' && scheme.dynamic;
	      });
	    }
	  }, {
	    key: "getDynamicSchemeById",
	    value: function getDynamicSchemeById(id) {
	      var _this$getSchemeById = this.getSchemeById(id),
	          mainEntity = _this$getSchemeById.mainEntity;

	      return this.options.formDictionary.document.dynamic.find(function (scheme) {
	        return String(scheme.id) === String(mainEntity);
	      });
	    }
	  }, {
	    key: "onTypeChange",
	    value: function onTypeChange(event) {
	      var _event$getData = event.getData(),
	          item = _event$getData.item;

	      var scheme = this.getSchemeById(item.id);
	      this.clear();
	      this.addItem(this.getHeader());
	      this.addItem(this.getTypesField());

	      if (this.isDynamicAvailable()) {
	        this.addItem(this.getDynamicEntitySettingsForm());
	        this.getDynamicEntitySettingsForm().hide();
	      }

	      var expertSettingsForm = this.getExpertSettingsForm();
	      expertSettingsForm.clear();

	      if (String(item.id) === '1' || String(item.id) === '8') {
	        expertSettingsForm.addField(this.getType1Header());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }

	      if (String(item.id) === '2' || String(item.id) === '5') {
	        expertSettingsForm.addField(this.getType2Header());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }

	      if (String(item.id) === '3' || String(item.id) === '6') {
	        expertSettingsForm.addField(this.getType3Header());
	        expertSettingsForm.addField(this.getStagesField());
	        expertSettingsForm.addField(this.getDuplicatesEnabledField());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }

	      if (String(item.id) === '4' || String(item.id) === '7') {
	        expertSettingsForm.addField(this.getType4Header());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }

	      if (main_core.Text.toNumber(item.id) > 4 && main_core.Type.isPlainObject(scheme) && scheme.dynamic !== true || this.getOrderSettingsForm().isOpened()) {
	        this.getOrderSettingsForm().onSwitchChange(true);
	      }

	      if (main_core.Type.isPlainObject(scheme) && (String(item.id) === 'smart' || scheme.dynamic === true) && this.isDynamicAvailable()) {
	        expertSettingsForm.addField(this.getDynamicHeader(scheme.name));
	        expertSettingsForm.addField(this.getDynamicCategoriesField(scheme.id));
	        expertSettingsForm.addField(this.getDuplicatesField());

	        if (String(scheme.id).endsWith('1')) {
	          this.getOrderSettingsForm().onSwitchChange(true);
	        }

	        this.getDynamicEntitySettingsForm().show();
	      }

	      this.addItem(expertSettingsForm);
	      this.addItem(this.getOrderSettingsForm());
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }, {
	    key: "getSelectedSchemeId",
	    value: function getSelectedSchemeId() {
	      var typeId = this.getTypesField().getValue();

	      if (String(typeId) === 'smart') {
	        var entityId = this.getDynamicEntitiesField().getValue();

	        if (this.getOrderSettingsForm().isOpened()) {
	          return "".concat(entityId, "1");
	        }

	        return "".concat(entityId, "0");
	      }

	      return typeId;
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      var duplicateMode = this.getDuplicatesField().getValue()[0];
	      var reducedValue = {
	        duplicateMode: duplicateMode === 'ALLOW' ? '' : duplicateMode,
	        scheme: this.getSelectedSchemeId(),
	        deal: {
	          duplicatesEnabled: main_core.Text.toBoolean(this.getDuplicatesEnabledField().getValue()[0])
	        },
	        payment: {
	          use: this.getPaymentField().getValue().length > 0
	        },
	        dynamic: {
	          category: null
	        }
	      };

	      if (this.getOrderSettingsForm().isOpened()) {
	        if (String(reducedValue.scheme) === '1') {
	          reducedValue.scheme = '8';
	        }

	        if (String(reducedValue.scheme) === '2') {
	          reducedValue.scheme = '5';
	        }

	        if (String(reducedValue.scheme) === '3') {
	          reducedValue.scheme = '6';
	        }

	        if (String(reducedValue.scheme) === '4') {
	          reducedValue.scheme = '7';
	        }
	      }

	      if (String(reducedValue.scheme) === '3' || String(reducedValue.scheme) === '6') {
	        reducedValue.deal.category = this.getStagesField().getValue().category;
	      }

	      var scheme = this.getSchemeById(reducedValue.scheme);

	      if (main_core.Type.isPlainObject(scheme) && scheme.dynamic) {
	        reducedValue.dynamic.category = this.getDynamicCategoriesField(scheme.id).getValue().category;
	      }

	      return {
	        document: reducedValue
	      };
	    }
	  }]);
	  return CrmContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var DefaultValues = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(DefaultValues, _ContentWrapper);

	  function DefaultValues(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DefaultValues);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultValues).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.DefaultValues');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_TITLE')
	    });
	    var message = new landing_ui_card_messagecard.MessageCard({
	      id: 'defaultValueMessage',
	      header: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_TITLE'),
	      description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_DESCRIPTION'),
	      restoreState: true
	    });
	    var fieldsForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      fields: [new landing_ui_field_defaultvaluefield.DefaultValueField({
	        selector: 'presetFields',
	        isLeadEnabled: _this.options.isLeadEnabled,
	        personalizationVariables: _this.options.personalizationVariables,
	        formOptions: babelHelpers.objectSpread({}, _this.options.formOptions),
	        crmFields: babelHelpers.objectSpread({}, _this.options.crmFields),
	        dictionary: babelHelpers.objectSpread({}, _this.options.dictionary),
	        items: babelHelpers.toConsumableArray(_this.options.values.fields)
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

	  babelHelpers.createClass(DefaultValues, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var layout = babelHelpers.get(babelHelpers.getPrototypeOf(DefaultValues.prototype), "getLayout", this).call(this);
	      main_core.Dom.addClass(layout, 'landing-ui-default-fields-values');
	      return layout;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return DefaultValues;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var FacebookContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(FacebookContent, _ContentWrapper);

	  function FacebookContent(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FacebookContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FacebookContent).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FacebookContent');

	    _this.addItem(new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_FACEBOOK')
	    }));

	    if (_this.options.formOptions.integration.canUse) {
	      var buttonCard = new landing_ui_card_basecard.BaseCard();
	      var button = new ui_buttons.Button({
	        text: _this.prepareButtonText(_this.options.formOptions),
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        onclick: function onclick() {
	          BX.SidePanel.Instance.open("/crm/webform/ads/".concat(_this.options.formOptions.id, "/?type=facebook"), {
	            cacheable: false,
	            events: {
	              onClose: function onClose() {
	                var client = crm_form_client.FormClient.getInstance();
	                client.resetCache(_this.options.formOptions.id);
	                client.getOptions(_this.options.formOptions.id).then(function (result) {
	                  button.setText(_this.prepareButtonText(result));
	                });
	              }
	            }
	          });
	        }
	      });
	      main_core.Dom.style(buttonCard.getLayout(), {
	        padding: 0,
	        margin: 0
	      });
	      main_core.Dom.append(button.render(), buttonCard.getBody());

	      _this.addItem(buttonCard);
	    } else {
	      _this.addItem(new landing_ui_card_messagecard.MessageCard({
	        header: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_HEADER'),
	        description: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_FB_TEXT'),
	        angle: false,
	        closeable: false
	      }));
	    }

	    return _this;
	  }

	  babelHelpers.createClass(FacebookContent, [{
	    key: "prepareButtonText",
	    value: function prepareButtonText(formOptions) {
	      var enabled = formOptions.integration.cases.some(function (item) {
	        return item.providerCode === 'facebook';
	      });

	      if (enabled) {
	        return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_FACEBOOK_BUTTON_ENABLED');
	      }

	      return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_FACEBOOK_BUTTON');
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return FacebookContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

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

	    if (_this.options.formOptions.integration.canUse) {
	      var buttonCard = new landing_ui_card_basecard.BaseCard();
	      var button = new ui_buttons.Button({
	        text: _this.prepareButtonText(_this.options.formOptions),
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        onclick: function onclick() {
	          BX.SidePanel.Instance.open("/crm/webform/ads/".concat(_this.options.formOptions.id, "/?type=vkontakte"), {
	            cacheable: false,
	            events: {
	              onClose: function onClose() {
	                var client = crm_form_client.FormClient.getInstance();
	                client.resetCache(_this.options.formOptions.id);
	                client.getOptions(_this.options.formOptions.id).then(function (result) {
	                  button.setText(_this.prepareButtonText(result));
	                });
	              }
	            }
	          });
	        }
	      });
	      main_core.Dom.style(buttonCard.getLayout(), {
	        padding: 0,
	        margin: 0
	      });
	      main_core.Dom.append(button.render(), buttonCard.getBody());

	      _this.addItem(buttonCard);
	    } else {
	      _this.addItem(new landing_ui_card_messagecard.MessageCard({
	        header: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_HEADER'),
	        description: landing_loc.Loc.getMessage('LANDING_CRM_FORM_INTEGRATION_SEO_NOT_INSTALLED_VK_TEXT'),
	        angle: false,
	        closeable: false
	      }));
	    }

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
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return VkContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var Callback = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Callback, _ContentWrapper);

	  function Callback(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Callback);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Callback).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Callback');

	    _this.addItem(_this.getHeader());

	    if (!_this.isAvailable()) {
	      _this.addItem(_this.getWarningMessage());

	      _this.getSettingsForm().disable();
	    }

	    _this.addItem(_this.getSettingsForm());

	    return _this;
	  }

	  babelHelpers.createClass(Callback, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getWarningMessage",
	    value: function getWarningMessage() {
	      return this.cache.remember('warningMessage', function () {
	        return new landing_ui_card_messagecard.MessageCard({
	          header: landing_loc.Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_HEADER'),
	          description: landing_loc.Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_TEXT'),
	          angle: false,
	          closeable: false,
	          hideActions: true
	        });
	      });
	    }
	  }, {
	    key: "isAvailable",
	    value: function isAvailable() {
	      var _this2 = this;

	      return this.cache.remember('isAvailable', function () {
	        return _this2.options.dictionary.callback.from.length > 0;
	      });
	    }
	  }, {
	    key: "getSettingsForm",
	    value: function getSettingsForm() {
	      var _this3 = this;

	      return this.cache.remember('settingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
	          toggleable: true,
	          toggleableType: landing_ui_form_formsettingsform.FormSettingsForm.ToggleableType.Switch,
	          opened: _this3.isAvailable() && main_core.Text.toBoolean(_this3.options.values.use),
	          fields: [_this3.getPhoneListField(), _this3.getTextField()]
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
	          value: [main_core.Text.toBoolean(_this4.options.values.use)],
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
	            value: true
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getPhoneListField",
	    value: function getPhoneListField() {
	      var _this5 = this;

	      return this.cache.remember('phoneListField', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'from',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_PHONE_TITLE'),
	          content: _this5.options.values.from,
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_DEFAULT_PHONE_NOT_SELECTED'),
	            value: ''
	          }].concat(babelHelpers.toConsumableArray(_this5.options.dictionary.callback.from.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          })))
	        });
	      });
	    }
	  }, {
	    key: "getTextField",
	    value: function getTextField() {
	      var _this6 = this;

	      return this.cache.remember('textField', function () {
	        return new landing_ui_field_textfield.TextField({
	          selector: 'text',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TEXT_TITLE'),
	          content: _this6.options.values.text,
	          textOnly: true
	        });
	      });
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        callback: babelHelpers.objectSpread({}, value, {
	          use: this.getSettingsForm().isOpened()
	        })
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return Callback;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var UserSelectorField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(UserSelectorField, _BaseField);

	  function UserSelectorField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, UserSelectorField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelectorField).call(this, options));
	    main_core.Dom.removeClass(_this.input, 'landing-ui-field-input');

	    _this.getTagSelector().renderTo(_this.input);

	    return _this;
	  }

	  babelHelpers.createClass(UserSelectorField, [{
	    key: "getTagSelector",
	    value: function getTagSelector() {
	      var _this2 = this;

	      return this.cache.remember('tagSelector', function () {
	        var root = landing_pageobject.PageObject.getRootWindow();
	        return new root.BX.UI.EntitySelector.TagSelector({
	          id: 'user-selector',
	          dialogOptions: {
	            id: 'user-selector',
	            entities: [{
	              id: 'user',
	              options: {
	                activeUsers: true
	              }
	            }],
	            preselectedItems: _this2.options.value,
	            events: {
	              'Item:onSelect': function ItemOnSelect() {
	                _this2.emit('onChange', {
	                  skipPrepare: true
	                });
	              },
	              'Item:onDeselect': function ItemOnDeselect() {
	                _this2.emit('onChange', {
	                  skipPrepare: true
	                });
	              }
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getTagSelector().getDialog().getSelectedItems().map(function (item) {
	        return item.id;
	      });
	    }
	  }]);
	  return UserSelectorField;
	}(landing_ui_field_basefield.BaseField);

	var Other = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(Other, _ContentWrapper);

	  function Other(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Other);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Other).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.SpamProtection');

	    var header = new landing_ui_card_headercard.HeaderCard({
	      title: landing_loc.Loc.getMessage('LANDING_FORM_OTHER_TITLE')
	    });
	    var otherForm = new landing_ui_form_formsettingsform.FormSettingsForm({
	      id: 'other',
	      description: null,
	      fields: [_this.getNameField(), _this.getUserSelectorField(), _this.getLanguageField(), _this.getUseSignField()]
	    });

	    _this.addItem(header);

	    _this.addItem(otherForm);

	    return _this;
	  }

	  babelHelpers.createClass(Other, [{
	    key: "canRemoveCopyrights",
	    value: function canRemoveCopyrights() {
	      return this.options.dictionary.sign.canRemove;
	    }
	  }, {
	    key: "getNameField",
	    value: function getNameField() {
	      var _this2 = this;

	      return this.cache.remember('nameField', function () {
	        return new landing_ui_field_textfield.TextField({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_OTHER_TITLE_NAME_TITLE'),
	          selector: 'name',
	          textOnly: true,
	          content: _this2.options.values.name
	        });
	      });
	    }
	  }, {
	    key: "getUseSignField",
	    value: function getUseSignField() {
	      var _this3 = this;

	      return this.cache.remember('useSignField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'useSign',
	          value: _this3.options.values.useSign ? ['useSign'] : [],
	          items: [{
	            value: 'useSign',
	            html: "".concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_SHOW_SIGN')).concat(_this3.createCopyRight())
	          }],
	          compact: true
	        });
	      });
	    }
	  }, {
	    key: "getUserSelectorField",
	    value: function getUserSelectorField() {
	      var _this4 = this;

	      return this.cache.remember('userSelectorField', function () {
	        return new UserSelectorField({
	          selector: 'users',
	          title: landing_loc.Loc.getMessage('LANDING_CRM_FORM_USER'),
	          value: _this4.options.values.users.reduce(function (acc, item) {
	            if (main_core.Type.isStringFilled(item) || main_core.Type.isNumber(item)) {
	              acc.push(['user', item]);
	            }

	            return acc;
	          }, [])
	        });
	      });
	    }
	  }, {
	    key: "getLanguageField",
	    value: function getLanguageField() {
	      var _this5 = this;

	      return this.cache.remember('language', function () {
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'language',
	          title: landing_loc.Loc.getMessage('LANDING_CRM_FORM_LANGUAGE'),
	          items: _this5.options.dictionary.languages.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          }),
	          content: _this5.options.values.language
	        });
	      });
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "createCopyRight",
	    value: function createCopyRight() {
	      return "\n\t\t\t<span class=\"landing-ui-signin\">\n\t\t\t\t<span class=\"landing-ui-sign\">".concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN'), "</span>\n\t\t\t\t<span class=\"landing-ui-sign-in\">").concat(landing_loc.Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_SIGN_BY'), "</span>\n\t\t\t\t<span class=\"landing-ui-sign-24\">24</span>\n\t\t\t</span>\n\t\t");
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        name: value.name,
	        data: {
	          language: this.getLanguageField().getValue(),
	          useSign: value.useSign.includes('useSign')
	        },
	        responsible: {
	          users: value.users
	        }
	      };
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      if (!this.canRemoveCopyrights()) {
	        var checkbox = this.getUseSignField();

	        if (!checkbox.getValue().includes('useSign')) {
	          checkbox.setValue(['useSign']);

	          if (main_core.Type.isStringFilled(this.options.dictionary.restriction.helper)) {
	            var evalGlobal = main_core.Reflection.getClass('BX.evalGlobal');

	            if (main_core.Type.isFunction(evalGlobal)) {
	              evalGlobal(this.options.dictionary.restriction.helper);
	            }
	          }
	        }
	      }

	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }]);
	  return Other;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	var sidebarButtons = [new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'fields',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_FIELDS'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'agreements',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_AGREEMENT'),
	  child: true,
	  important: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'crm',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_ENTITY'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'identify',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_CLIENT_IDENTIFY'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'button_and_header',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_HEADER_AND_BUTTON'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'spam_protection',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_SPAM_PROTECTION'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'fields_rules',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_FIELDS_RULES'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'actions',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_ACTIONS'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'default_values',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_DEFAULT_VALUES'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'analytics',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_DEFAULT_ANALYTICS'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'facebook',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_FACEBOOK'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'vk',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_VK'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'callback',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CALLBACK'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'embed',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_CRM_EMBED'),
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'more',
	  text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_MORE_BUTTON_TEXT'),
	  className: 'landing-ui-button-sidebar-more',
	  child: true
	}), new landing_ui_button_sidebarbutton.SidebarButton({
	  id: 'other',
	  text: landing_loc.Loc.getMessage('LANDING_SIDEBAR_BUTTON_OTHER'),
	  child: true
	})];

	var presetCategories = [new landing_ui_panel_basepresetpanel.PresetCategory({
	  id: 'crm',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_CRM')
	}), new landing_ui_panel_basepresetpanel.PresetCategory({
	  id: 'products',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_PRODUCTS_2')
	}), new landing_ui_panel_basepresetpanel.PresetCategory({
	  id: 'social',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_SOCIAL')
	}), new landing_ui_panel_basepresetpanel.PresetCategory({
	  id: 'crm_automation',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CATEGORY_CRM_AUTOMATION')
	})];

	var siteIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/integration.svg";

	var widgetAutoShowIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/autoshow.svg";

	var callbackIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/revertcall.svg";

	var vkIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/vk.svg";

	var facebookIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/facebook.svg";

	var crmFormIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/crm.svg";

	var serviceIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/service.svg";

	var product1Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/products1.svg";

	var product2Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/products2.svg";

	var product3Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/products3.svg";

	var product4Icon = "/bitrix/js/landing/ui/panel/formsettingspanel/dist/images/icons/products4.svg";

	var presets = [new landing_ui_panel_basepresetpanel.Preset({
	  id: 'contacts',
	  category: 'crm',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_DESCRIPTION'),
	  icon: siteIcon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'contacts',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      buttonCaption: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_BUTTON'),
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_LAST_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    result: {
	      success: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_SUCCESS_TEXT')
	      },
	      failure: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CONTACTS_FAILURE_TEXT')
	      }
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'feedback',
	  category: 'crm',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_DESCRIPTION'),
	  icon: widgetAutoShowIcon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'feedback',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      buttonCaption: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_BUTTON'),
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }, {
	        name: 'LEAD_COMMENTS'
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    result: {
	      success: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_SUCCESS_TEXT')
	      },
	      failure: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_FAILURE_TEXT')
	      }
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'callback',
	  category: 'crm',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_DESCRIPTION'),
	  icon: callbackIcon,
	  defaultSection: 'callback',
	  items: ['fields', 'agreements', 'crm', 'embed', 'callback', 'other'],
	  options: {
	    templateId: 'callback',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      buttonCaption: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_BUTTON'),
	      fields: [{
	        name: 'LEAD_PHONE'
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    result: {
	      success: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_SUCCESS_TEXT')
	      },
	      failure: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_CALLBACK_FAILURE_TEXT')
	      }
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'expert',
	  category: 'crm',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT_DESCRIPTION'),
	  icon: serviceIcon,
	  items: ['fields', 'agreements', 'crm', 'identify', 'button_and_header', 'spam_protection', 'fields_rules', 'actions', 'default_values', 'analytics', 'facebook', 'vk', 'callback', 'embed', 'other'],
	  options: {
	    templateId: 'expert',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_EXPERT'),
	    agreements: {
	      use: true
	    },
	    result: {
	      success: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_SUCCESS_TEXT')
	      },
	      failure: {
	        text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FEEDBACK_FAILURE_TEXT')
	      }
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'products1',
	  category: 'products',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1_DESCRIPTION'),
	  icon: product1Icon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'products1',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_1'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }, {
	        type: 'product',
	        bigPic: false
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'products2',
	  category: 'products',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2_DESCRIPTION'),
	  icon: product2Icon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'products2',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_2'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }, {
	        type: 'product',
	        bigPic: false
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    document: {
	      scheme: 8
	    },
	    payment: {
	      use: true
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'products3',
	  category: 'products',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3_DESCRIPTION'),
	  icon: product3Icon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'products3',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_3'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }, {
	        type: 'product',
	        bigPic: false
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'products4',
	  category: 'products',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4_DESCRIPTION'),
	  icon: product4Icon,
	  items: ['fields', 'agreements', 'crm', 'embed', 'other'],
	  options: {
	    templateId: 'products4',
	    name: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PRODUCT_4'),
	    agreements: {
	      use: true
	    },
	    data: {
	      title: '',
	      desc: '',
	      fields: [{
	        name: 'LEAD_NAME'
	      }, {
	        name: 'LEAD_PHONE'
	      }, {
	        name: 'LEAD_EMAIL'
	      }, {
	        type: 'product',
	        bigPic: true
	      }],
	      agreements: [{
	        checked: true
	      }],
	      dependencies: [],
	      recaptcha: {
	        use: false
	      }
	    },
	    captcha: {
	      key: '',
	      secret: ''
	    },
	    document: {
	      scheme: 1
	    }
	  }
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'vk',
	  category: 'social',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_VK'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_VK_DESCRIPTION'),
	  icon: vkIcon,
	  items: ['fields', 'embed', 'other'],
	  disabled: true,
	  soon: true
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'facebook',
	  category: 'social',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FB'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_FB_DESCRIPTION'),
	  icon: facebookIcon,
	  items: ['embed', 'other'],
	  disabled: true,
	  soon: true
	}), new landing_ui_panel_basepresetpanel.Preset({
	  id: 'personalisation',
	  category: 'crm_automation',
	  title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PERSONALIZATION'),
	  description: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PRESET_PERSONALIZATION_DESCRIPTION'),
	  icon: crmFormIcon,
	  items: ['embed', 'other'],
	  disabled: true,
	  soon: true
	})];

	function _templateObject$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-access-error-message\">\n\t\t\t\t\t<div class=\"landing-ui-access-error-message-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"]);

	  _templateObject$9 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	/**
	 * @memberOf BX.Landing.UI.Panel
	 */
	var FormSettingsPanel = /*#__PURE__*/function (_BasePresetPanel) {
	  babelHelpers.inherits(FormSettingsPanel, _BasePresetPanel);
	  babelHelpers.createClass(FormSettingsPanel, null, [{
	    key: "getInstance",
	    value: function getInstance() {
	      var rootWindow = landing_pageobject.PageObject.getRootWindow();
	      var rootWindowPanel = rootWindow.BX.Landing.UI.Panel.FormSettingsPanel;

	      if (!rootWindowPanel.instance && !FormSettingsPanel.instance) {
	        rootWindowPanel.instance = new FormSettingsPanel();
	      }

	      return rootWindowPanel.instance || FormSettingsPanel.instance;
	    }
	  }]);

	  function FormSettingsPanel() {
	    var _this;

	    babelHelpers.classCallCheck(this, FormSettingsPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormSettingsPanel).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_TITLE'));

	    _this.disableOverlay();

	    var preparedSidebarButtons = sidebarButtons.filter(function (button) {
	      return _this.isCrmFormPage() || button.id !== 'embed';
	    });

	    _this.setSidebarButtons(preparedSidebarButtons);

	    _this.setCategories(presetCategories);

	    var filteredPresets = presets.filter(function (preset) {
	      return landing_loc.Loc.getMessage('LANGUAGE_ID') === 'ru' || landing_loc.Loc.getMessage('LANGUAGE_ID') !== 'ru' && preset.options.id !== 'vk';
	    });

	    _this.setPresets(filteredPresets);

	    if (!_this.isCrmFormPage()) {
	      main_core.Dom.append(_this.getBlockSettingsButton().render(), _this.getRightHeaderControls());
	    }

	    _this.subscribe('onCancel', _this.onCancelClick.bind(babelHelpers.assertThisInitialized(_this)));

	    return _this;
	  } // eslint-disable-next-line class-methods-use-this


	  babelHelpers.createClass(FormSettingsPanel, [{
	    key: "isCrmFormPage",
	    value: function isCrmFormPage() {
	      return landing_env.Env.getInstance().getOptions().specialType === 'crm_forms';
	    }
	  }, {
	    key: "getBlockSettingsButton",
	    value: function getBlockSettingsButton() {
	      var _this2 = this;

	      return this.cache.remember('blockSettingsButton', function () {
	        return new ui_buttons.Button({
	          text: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_BLOCK_SETTINGS_BUTTON_TEXT'),
	          color: ui_buttons.Button.Color.LIGHT_BORDER,
	          onclick: _this2.onBlockSettingsButtonClick.bind(_this2),
	          size: ui_buttons.Button.Size.SMALL
	        });
	      });
	    }
	  }, {
	    key: "onBlockSettingsButtonClick",
	    value: function onBlockSettingsButtonClick() {
	      var _this3 = this;

	      if (this.getCurrentBlock()) {
	        this.hide().then(function () {
	          _this3.getCurrentBlock().showContentPanel();
	        });
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      var _this4 = this;

	      return this.cache.remember('loader', function () {
	        return new main_loader.Loader({
	          target: _this4.body
	        });
	      });
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      void this.getLoader().show();
	      main_core.Dom.hide(this.sidebar);
	      main_core.Dom.hide(this.content);
	      main_core.Dom.hide(this.getPresetField().getLayout());
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.getLoader().hide();
	      main_core.Dom.show(this.sidebar);
	      main_core.Dom.show(this.content);
	      main_core.Dom.show(this.getPresetField().getLayout());
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this5 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (options.showWithOptions) {
	        var editorData = landing_env.Env.getInstance().getOptions().formEditorData;
	        this.setCrmFields(editorData.crmFields);
	        this.setCrmCompanies(editorData.crmCompanies);
	        this.setCrmCategories(editorData.crmCategories);
	        this.setAgreements(editorData.agreements);
	        var currentOptions = main_core.Runtime.clone(editorData.formOptions);

	        if (currentOptions.agreements.use !== true) {
	          currentOptions.agreements.use = true;
	          currentOptions.data.agreements = [];
	        }

	        this.setFormOptions(currentOptions);
	        this.setFormDictionary(editorData.dictionary);
	        return Promise.resolve();
	      }

	      var crmData = landing_backend.Backend.getInstance().batch('Form::getCrmFields', {
	        crmFields: {
	          action: 'Form::getCrmFields',
	          data: null
	        },
	        crmCompanies: {
	          action: 'Form::getCrmCompanies',
	          data: null
	        },
	        crmCategories: {
	          action: 'Form::getCrmCategories',
	          data: null
	        },
	        agreements: {
	          action: 'Form::getAgreements',
	          data: null
	        }
	      }).then(function (result) {
	        _this5.setCrmFields(result.crmFields.result);

	        _this5.setCrmCompanies(result.crmCompanies.result);

	        _this5.setCrmCategories(result.crmCategories.result);

	        _this5.setAgreements(result.agreements.result);
	      });
	      var formOptions = crm_form_client.FormClient.getInstance().getOptions(this.getCurrentFormId()).then(function (options) {
	        var currentOptions = main_core.Runtime.clone(options);

	        if (currentOptions.agreements.use !== true) {
	          currentOptions.agreements.use = true;
	          currentOptions.data.agreements = [];
	        }

	        _this5.setFormOptions(currentOptions);
	      });
	      var formDictionary = crm_form_client.FormClient.getInstance().getDictionary().then(function (dictionary) {
	        _this5.setFormDictionary(dictionary);
	      });
	      return Promise.all([crmData, formOptions, formDictionary]);
	    }
	  }, {
	    key: "setAgreements",
	    value: function setAgreements(agreements) {
	      this.cache.set('agreements', main_core.Runtime.orderBy(agreements, ['id'], ['asc']));
	    }
	  }, {
	    key: "getAgreements",
	    value: function getAgreements() {
	      return this.cache.get('agreements');
	    }
	  }, {
	    key: "isLeadEnabled",
	    value: function isLeadEnabled() {
	      return this.getFormDictionary().document.lead.enabled;
	    }
	  }, {
	    key: "setCurrentBlock",
	    value: function setCurrentBlock(block) {
	      this.cache.set('currentBlock', block);
	    }
	  }, {
	    key: "getCurrentBlock",
	    value: function getCurrentBlock() {
	      return this.cache.get('currentBlock');
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this6 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        formOptions: {}
	      };

	      if (!this.layout.parentNode) {
	        this.enableToggleMode();
	      }

	      if (!this.isFormCreated()) {
	        this.disableTransparentMode();
	      }

	      this.setCurrentBlock(options.block);
	      this.setCurrentFormId(options.formId);
	      this.setCurrentFormInstanceId(options.instanceId);
	      this.showLoader();
	      this.load(options).then(function () {
	        _this6.hideLoader();

	        var formOptions = _this6.getFormOptions();

	        if (!_this6.isLeadEnabled()) {
	          var _presets = _this6.getPresets().map(function (preset) {
	            if (main_core.Type.isPlainObject(preset.options.options) && main_core.Type.isPlainObject(preset.options.options.data)) {
	              if (main_core.Type.isArrayFilled(preset.options.options.data.fields)) {
	                preset.options.options.data.fields = preset.options.options.data.fields.map(function (field) {
	                  var preparedField = babelHelpers.objectSpread({}, field);

	                  if (main_core.Type.isStringFilled(field.name)) {
	                    preparedField.name = field.name.replace(/^LEAD_/, 'CONTACT_');
	                  }

	                  return preparedField;
	                });
	              }

	              if (main_core.Type.isPlainObject(preset.options.options.document)) {
	                preset.options.options.document.scheme = 3;
	              }
	            }

	            return preset;
	          });

	          _this6.setPresets(_presets);
	        }

	        if (main_core.Type.isPlainObject(options.formOptions)) {
	          var _formOptions = main_core.Runtime.merge(_this6.getFormOptions(), options.formOptions);

	          _this6.setFormOptions(_formOptions);
	        }

	        if (options.state === 'presets' && formOptions.templateId !== 'callback') {
	          _this6.onPresetFieldClick();

	          _this6.activatePreset(formOptions.templateId);
	        } else {
	          var preset = _this6.getPresets().find(function (item) {
	            return item.options.id === formOptions.templateId;
	          });

	          if (!preset) {
	            preset = _this6.getPresets().find(function (item) {
	              return item.options.id === 'expert';
	            });
	          }

	          if (_this6.isFormCreated() && formOptions.templateId !== 'callback') {
	            _this6.applyPreset(preset);
	          } else {
	            _this6.applyPreset(preset, true);
	          }
	        }

	        _this6.setInitialFormOptions(main_core.Runtime.clone(_this6.getFormOptions()));
	      }).catch(function (error) {
	        if (main_core.Type.isArrayFilled(error)) {
	          var accessDeniedCode = 510;
	          var isAccessDenied = error.some(function (errorItem) {
	            return String(errorItem.code) === String(accessDeniedCode);
	          });

	          if (isAccessDenied) {
	            _this6.getLoader().hide();

	            main_core.Dom.show(_this6.sidebar);
	            main_core.Dom.show(_this6.content);
	            main_core.Dom.hide(_this6.footer);
	            main_core.Dom.append(_this6.getAccessError(), _this6.content);
	          }
	        }

	        console.error(error);
	      });
	      void landing_ui_panel_stylepanel.StylePanel.getInstance().hide();
	      return babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "show", this).call(this, options);
	    }
	  }, {
	    key: "getAccessError",
	    value: function getAccessError() {
	      return this.cache.remember('accessErrorMessage', function () {
	        return main_core.Tag.render(_templateObject$9(), landing_loc.Loc.getMessage('LANDING_CRM_ACCESS_ERROR_MESSAGE'));
	      });
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "isFormCreated",
	    value: function isFormCreated() {
	      var uri = new main_core.Uri(window.location.origin);
	      return main_core.Text.toBoolean(uri.getQueryParam('formCreated'));
	    }
	  }, {
	    key: "setCurrentFormId",
	    value: function setCurrentFormId(formId) {
	      this.cache.set('currentFormId', main_core.Text.toNumber(formId));
	    }
	  }, {
	    key: "getCurrentFormId",
	    value: function getCurrentFormId() {
	      return this.cache.get('currentFormId');
	    }
	  }, {
	    key: "setCurrentFormInstanceId",
	    value: function setCurrentFormInstanceId(formId) {
	      this.cache.set('currentFormInstanceId', formId);
	    }
	  }, {
	    key: "getCurrentFormInstanceId",
	    value: function getCurrentFormInstanceId() {
	      return this.cache.get('currentFormInstanceId');
	    }
	  }, {
	    key: "setCrmFields",
	    value: function setCrmFields(fields) {
	      this.cache.set('fields', fields);
	    }
	  }, {
	    key: "getCrmFields",
	    value: function getCrmFields() {
	      return this.cache.get('fields') || {};
	    }
	  }, {
	    key: "setCrmCompanies",
	    value: function setCrmCompanies(companies) {
	      this.cache.set('companies', companies);
	    }
	  }, {
	    key: "getCrmCompanies",
	    value: function getCrmCompanies() {
	      return this.cache.get('companies') || [];
	    }
	  }, {
	    key: "setCrmCategories",
	    value: function setCrmCategories(categories) {
	      this.cache.set('crmCategories', categories);
	    }
	  }, {
	    key: "getCrmCategories",
	    value: function getCrmCategories() {
	      return this.cache.get('crmCategories') || [];
	    }
	  }, {
	    key: "setFormOptions",
	    value: function setFormOptions(options) {
	      this.cache.set('formOptions', options);
	    }
	  }, {
	    key: "getFormOptions",
	    value: function getFormOptions() {
	      return main_core.Runtime.clone(this.cache.get('formOptions') || {});
	    }
	  }, {
	    key: "setFormDictionary",
	    value: function setFormDictionary(dictionary) {
	      this.cache.set('formDictionary', dictionary);
	    }
	  }, {
	    key: "getFormDictionary",
	    value: function getFormDictionary() {
	      return this.cache.get('formDictionary') || {};
	    }
	  }, {
	    key: "setInitialFormOptions",
	    value: function setInitialFormOptions(options) {
	      this.cache.set('initialFormOptions', main_core.Runtime.clone(options));
	    }
	  }, {
	    key: "getInitialFormOptions",
	    value: function getInitialFormOptions() {
	      return this.cache.get('initialFormOptions');
	    } // eslint-disable-next-line

	  }, {
	    key: "getCrmForm",
	    value: function getCrmForm() {
	      var formApp = main_core.Reflection.getClass('b24form.App');

	      if (formApp) {
	        if (this.getCurrentFormInstanceId()) {
	          return formApp.get(this.getCurrentFormInstanceId());
	        }

	        return formApp.list()[0];
	      }

	      return null;
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      var _this7 = this;

	      var eventData = event.getData();
	      var eventTargetValue = event.getTarget().getValue();
	      Promise.resolve(eventTargetValue).then(function (value) {
	        if (eventData.skipPrepare) {
	          var formOptions = _this7.getFormOptions();

	          if (main_core.Type.isArrayFilled(formOptions.data.dependencies) && main_core.Type.isArrayFilled(value.fields)) {
	            var dependencies = formOptions.data.dependencies;
	            formOptions.data.dependencies = dependencies.reduce(function (acc, item) {
	              var preparedItem = babelHelpers.objectSpread({}, item);
	              preparedItem.list = preparedItem.list.filter(function (rule) {
	                return value.fields.some(function (field) {
	                  return field.id === rule.condition.target;
	                }) && value.fields.some(function (field) {
	                  return field.id === rule.action.target;
	                });
	              });

	              if (preparedItem.list.length > 0) {
	                acc.push(preparedItem);
	              }

	              return acc;
	            }, []);
	          }

	          if (Reflect.has(value, 'presetFields') || Reflect.has(value, 'document') || Reflect.has(value, 'result')) {
	            var additionalValue = {};

	            if (Reflect.has(value, 'document')) {
	              additionalValue.payment = value.document.payment;
	              delete value.document.payment;
	            }

	            return babelHelpers.objectSpread({}, formOptions, value, additionalValue);
	          }

	          if (Reflect.has(value, 'embedding') || Reflect.has(value, 'callback') || Reflect.has(value, 'name') && Reflect.has(value, 'data') && Reflect.has(value.data, 'useSign')) {
	            var mergedOptions = main_core.Runtime.merge(formOptions, value);

	            if (Reflect.has(value, 'responsible')) {
	              mergedOptions.responsible.users = value.responsible.users;
	            }

	            return mergedOptions;
	          }

	          if (Reflect.has(value, 'recaptcha')) {
	            var _value$recaptcha = value.recaptcha,
	                _key = _value$recaptcha.key,
	                secret = _value$recaptcha.secret;
	            delete value.recaptcha.key;
	            delete value.recaptcha.secret;
	            var captcha = {
	              key: _key,
	              secret: secret
	            };
	            return babelHelpers.objectSpread({}, formOptions, {
	              captcha: captcha,
	              data: babelHelpers.objectSpread({}, formOptions.data, value)
	            });
	          }

	          return babelHelpers.objectSpread({}, formOptions, {
	            data: babelHelpers.objectSpread({}, formOptions.data, value)
	          });
	        }

	        return crm_form_client.FormClient.getInstance().prepareOptions(_this7.getFormOptions(), value).then(function (result) {
	          if (value.agreements) {
	            result.data = main_core.Runtime.merge(result.data, value);
	          }

	          if (value.fields) {
	            result.data.fields = result.data.fields.map(function (field, index) {
	              return main_core.Runtime.merge(field, value.fields[index]);
	            });
	          }

	          return result;
	        });
	      }).then(function (result) {
	        _this7.setFormOptions(result);

	        _this7.getCrmForm().adjust(main_core.Runtime.clone(result.data));
	      });
	    }
	  }, {
	    key: "getPersonalizationVariables",
	    value: function getPersonalizationVariables() {
	      var _this8 = this;

	      return this.cache.remember('personalizationVariables', function () {
	        return _this8.getFormDictionary().personalization.list.map(function (item) {
	          return {
	            name: item.name,
	            value: item.id
	          };
	        });
	      });
	    }
	  }, {
	    key: "getDefaultValuesVariables",
	    value: function getDefaultValuesVariables() {
	      var _this9 = this;

	      return this.cache.remember('personalizationVariables', function () {
	        var _this9$getFormDiction = _this9.getFormDictionary(),
	            properties = _this9$getFormDiction.properties;

	        if (main_core.Type.isPlainObject(properties) && main_core.Type.isArrayFilled(properties.list)) {
	          return properties.list.map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          });
	        }

	        return [];
	      });
	    }
	  }, {
	    key: "getContent",
	    value: function getContent(id) {
	      var _this10 = this;

	      var crmForm = this.getCrmForm();

	      if (crmForm) {
	        crmForm.sent = false;
	        crmForm.error = false;
	      }

	      if (id === 'button_and_header') {
	        return new HeaderAndButtonContent({
	          personalizationVariables: this.getPersonalizationVariables(),
	          values: {
	            title: FormSettingsPanel.sanitize(this.getFormOptions().data.title),
	            desc: FormSettingsPanel.sanitize(this.getFormOptions().data.desc),
	            buttonCaption: this.getFormOptions().data.buttonCaption
	          }
	        });
	      }

	      if (id === 'spam_protection') {
	        return new SpamProtection({
	          values: {
	            key: this.getFormOptions().captcha.key,
	            secret: this.getFormOptions().captcha.secret,
	            use: main_core.Text.toBoolean(this.getFormOptions().data.recaptcha.use)
	          }
	        });
	      }

	      if (id === 'agreements') {
	        return new AgreementsContent({
	          formOptions: this.getFormOptions(),
	          agreementsList: this.getAgreements(),
	          values: {
	            agreements: this.getFormOptions().data.agreements
	          }
	        });
	      }

	      if (id === 'analytics') {
	        return new AnalyticsContent({
	          events: this.getFormOptions().analytics.steps,
	          values: {}
	        });
	      }

	      if (id === 'fields') {
	        return new FieldsContent({
	          crmFields: this.getCrmFields(),
	          formOptions: this.getFormOptions(),
	          dictionary: this.getFormDictionary(),
	          isLeadEnabled: this.isLeadEnabled(),
	          values: {
	            fields: this.getFormOptions().data.fields
	          }
	        });
	      }

	      if (id === 'fields_rules') {
	        return new FieldsRulesContent({
	          fields: this.getFormOptions().data.fields,
	          values: this.getFormOptions().data.dependencies,
	          dictionary: this.getFormDictionary()
	        });
	      }

	      if (id === 'actions') {
	        var actionsContent = new ActionsContent({
	          fields: this.getFormOptions().data.fields,
	          values: this.getFormOptions().result
	        });
	        actionsContent.subscribe('onShowSuccess', function () {
	          crmForm.stateText = _this10.getFormOptions().result.success.text;
	          crmForm.sent = !crmForm.sent;
	          crmForm.error = false;
	        }).subscribe('onShowFailure', function () {
	          crmForm.stateText = _this10.getFormOptions().result.failure.text;
	          crmForm.error = !crmForm.error;
	          crmForm.sent = false;
	        });
	        return actionsContent;
	      }

	      if (id === 'embed') {
	        return new EmbedContent({
	          fields: this.getFormOptions().data.fields,
	          values: this.getFormOptions().embedding
	        });
	      }

	      if (id === 'identify') {
	        return new Identify({
	          fields: this.getFormOptions().data.fields,
	          values: {}
	        });
	      }

	      if (id === 'crm') {
	        return new CrmContent({
	          fields: this.getFormOptions().data.fields,
	          companies: this.getCrmCompanies(),
	          categories: this.getCrmCategories(),
	          isLeadEnabled: this.isLeadEnabled(),
	          formDictionary: this.getFormDictionary(),
	          values: {
	            scheme: this.getFormOptions().document.scheme,
	            duplicatesEnabled: this.getFormOptions().document.deal.duplicatesEnabled || 'Y',
	            category: this.getFormOptions().document.deal.category,
	            dynamicCategory: this.getFormOptions().document.dynamic.category,
	            payment: this.getFormOptions().payment.use,
	            duplicateMode: this.getFormOptions().document.duplicateMode
	          }
	        });
	      }

	      if (id === 'default_values') {
	        return new DefaultValues({
	          crmFields: this.getCrmFields(),
	          formOptions: this.getFormOptions(),
	          dictionary: this.getFormDictionary(),
	          isLeadEnabled: this.isLeadEnabled(),
	          personalizationVariables: this.getDefaultValuesVariables(),
	          values: {
	            fields: this.getFormOptions().presetFields
	          }
	        });
	      }

	      if (id === 'facebook') {
	        return new FacebookContent({
	          formOptions: this.getFormOptions()
	        });
	      }

	      if (id === 'vk') {
	        return new VkContent({
	          formOptions: this.getFormOptions()
	        });
	      }

	      if (id === 'callback') {
	        return new Callback({
	          dictionary: this.getFormDictionary(),
	          values: this.getFormOptions().callback
	        });
	      }

	      if (id === 'other') {
	        return new Other({
	          formOptions: this.getFormOptions(),
	          dictionary: this.getFormDictionary(),
	          values: {
	            name: this.getFormOptions().name,
	            useSign: this.getFormOptions().data.useSign,
	            users: this.getFormOptions().responsible.users,
	            language: this.getFormOptions().data.language
	          }
	        });
	      }

	      return null;
	    }
	  }, {
	    key: "applyPreset",
	    value: function applyPreset(preset) {
	      var _this11 = this;

	      var skipOptions = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      this.getPresets().forEach(function (currentPreset) {
	        currentPreset.deactivate();
	      });
	      preset.activate();

	      if (main_core.Type.isPlainObject(preset.options.options) && !skipOptions) {
	        this.showLoader();
	        var clonedPresetOptions = babelHelpers.objectSpread({
	          data: {}
	        }, main_core.Runtime.clone(preset.options.options));
	        var agreementsOptions = [];

	        if (Reflect.has(clonedPresetOptions.data, 'agreements')) {
	          var sourceAgreements = this.getAgreements();
	          agreementsOptions = babelHelpers.toConsumableArray(clonedPresetOptions.data.agreements);
	          clonedPresetOptions.data.agreements = clonedPresetOptions.data.agreements.map(function (item, index) {
	            return sourceAgreements[index].id;
	          });
	        }

	        crm_form_client.FormClient.getInstance().prepareOptions(this.getFormOptions(), clonedPresetOptions.data).then(function (result) {
	          if (Reflect.has(clonedPresetOptions.data, 'fields')) {
	            delete clonedPresetOptions.data.fields;
	          }

	          if (Reflect.has(clonedPresetOptions.data, 'agreements')) {
	            delete clonedPresetOptions.data.agreements;
	          }

	          var preparedOptions = main_core.Runtime.merge({}, result, clonedPresetOptions);
	          preparedOptions.data.agreements = preparedOptions.data.agreements.map(function (agreement, index) {
	            return babelHelpers.objectSpread({}, agreement, agreementsOptions[index]);
	          });

	          _this11.setFormOptions(preparedOptions);

	          _this11.getCrmForm().adjust(main_core.Runtime.clone(preparedOptions.data));

	          babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "applyPreset", _this11).call(_this11, preset);

	          _this11.hideLoader();
	        });
	      } else {
	        babelHelpers.get(babelHelpers.getPrototypeOf(FormSettingsPanel.prototype), "applyPreset", this).call(this, preset);
	      }
	    }
	  }, {
	    key: "getFormNode",
	    value: function getFormNode() {
	      var _this12 = this;

	      return this.cache.remember('formNode', function () {
	        return _this12.getCurrentBlock().node.querySelector('[data-b24form-use-style]');
	      });
	    }
	  }, {
	    key: "useBlockDesign",
	    value: function useBlockDesign() {
	      var _this13 = this;

	      return this.cache.remember('useBlockDesign', function () {
	        return main_core.Text.toBoolean(main_core.Dom.attr(_this13.getFormNode(), 'data-b24form-use-style'));
	      });
	    }
	  }, {
	    key: "getCurrentCrmEntityName",
	    value: function getCurrentCrmEntityName() {
	      var scheme = this.getFormOptions().document.scheme;
	      var schemeItem = this.getFormDictionary().document.schemes.find(function (item) {
	        return String(scheme) === String(item.id);
	      });
	      return schemeItem.name;
	    }
	  }, {
	    key: "getNotSynchronizedFields",
	    value: function getNotSynchronizedFields() {
	      return crm_form_client.FormClient.getInstance().checkFields(this.getFormOptions()).then(function (result) {
	        return result;
	      });
	    }
	  }, {
	    key: "showSynchronizationPopup",
	    value: function showSynchronizationPopup(notSynchronizedFields) {
	      var _this14 = this;

	      return new Promise(function (resolve) {
	        var onOk = function onOk(messageBox) {
	          messageBox.close();
	          resolve(true);
	        };

	        var onCancel = function onCancel(messageBox) {
	          messageBox.close();
	          resolve(false);
	        };

	        var messageDescription = function () {
	          var entityName = landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_ENTITY_TEMPLATE').replace('{entityName}', _this14.getCurrentCrmEntityName());
	          return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_DESCRIPTION').replace('{entityName}', entityName);
	        }();

	        var messageText = function () {
	          var fields = babelHelpers.toConsumableArray(notSynchronizedFields).map(function (field) {
	            return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_FIELD_TEMPLATE').replace('{fieldName}', field);
	          });

	          if (notSynchronizedFields.length > 1) {
	            var lastField = fields.pop();
	            return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT').replace('{fieldsList}', fields.join(', ')).replace('{lastField}', lastField);
	          }

	          return landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TEXT_1').replace('{field}', fields.join(', '));
	        }();

	        window.top.BX.UI.Dialogs.MessageBox.confirm("".concat(messageDescription, "<br><br>").concat(messageText), landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_TITLE'), onOk, landing_loc.Loc.getMessage('LANDING_SYNCHRONIZATION_POPUP_OK_BUTTON_LABEL'), onCancel);
	      });
	    }
	  }, {
	    key: "showSynchronizationErrorPopup",
	    value: function showSynchronizationErrorPopup(errors) {
	      var message = errors.reduce(function (acc, item) {
	        return "".concat(acc, "\n\n").concat(item);
	      }, '');
	      window.top.BX.UI.Dialogs.MessageBox.alert(message);
	    }
	  }, {
	    key: "onSaveClick",
	    value: function onSaveClick() {
	      var _this15 = this;

	      main_core.Dom.addClass(this.getSaveButton().layout, 'ui-btn-wait');
	      this.getNotSynchronizedFields().then(function (result) {
	        if (main_core.Type.isPlainObject(result.sync)) {
	          if (main_core.Type.isArrayFilled(result.sync.errors)) {
	            _this15.showSynchronizationErrorPopup(result.sync.errors);

	            return false;
	          }

	          if (main_core.Type.isArrayFilled(result.sync.fields)) {
	            var fieldLabels = result.sync.fields.map(function (field) {
	              return field.label;
	            });
	            return _this15.showSynchronizationPopup(fieldLabels);
	          }
	        }

	        return true;
	      }).then(function (isConfirmed) {
	        if (isConfirmed) {
	          var uri = new main_core.Uri(window.top.location.toString());
	          uri.removeQueryParam('formCreated');
	          window.top.history.replaceState(null, document.title, uri.toString());

	          var initialOptions = _this15.getInitialFormOptions();

	          var currentOptions = _this15.getFormOptions();

	          var options = function () {
	            if (!_this15.isCrmFormPage()) {
	              var clonedOptions = main_core.Runtime.clone(currentOptions);
	              clonedOptions.data.design = main_core.Runtime.clone(initialOptions.data.design);
	              return clonedOptions;
	            }

	            return currentOptions;
	          }();

	          void crm_form_client.FormClient.getInstance().saveOptions(options).then(function (result) {
	            _this15.setFormOptions(result);

	            crm_form_client.FormClient.getInstance().resetCache(result.id);
	            main_core.Dom.removeClass(_this15.getSaveButton().layout, 'ui-btn-wait');
	            void _this15.hide();
	          });

	          if (_this15.useBlockDesign() && _this15.isCrmFormPage()) {
	            _this15.disableUseBlockDesign();
	          }
	        } else {
	          main_core.Dom.removeClass(_this15.getSaveButton().layout, 'ui-btn-wait');
	        }
	      });
	    }
	  }, {
	    key: "disableUseBlockDesign",
	    value: function disableUseBlockDesign() {
	      main_core.Dom.attr(this.getFormNode(), 'data-b24form-use-style', 'N');
	      this.cache.set('useBlockDesign', false);
	      landing_backend.Backend.getInstance().action('Landing\\Block::updateNodes', {
	        block: this.getCurrentBlock().id,
	        data: {
	          '.bitrix24forms': {
	            attrs: {
	              'data-b24form-use-style': 'N'
	            }
	          }
	        },
	        lid: this.getCurrentBlock().lid,
	        siteId: this.getCurrentBlock().siteId
	      }, {
	        code: this.getCurrentBlock().manifest.code
	      });
	    }
	  }, {
	    key: "onCancelClick",
	    value: function onCancelClick() {
	      this.getCrmForm().adjust(this.getInitialFormOptions().data);
	      void this.hide();
	    }
	  }], [{
	    key: "sanitize",
	    value: function sanitize(value) {
	      if (main_core.Type.isStringFilled(value)) {
	        return main_core.Text.decode(value).replace(/<style[^>]*>.*<\/style>/gm, '').replace(/<script[^>]*>.*<\/script>/gm, '').replace(/<[^>]+>/gm, '');
	      }

	      return value;
	    }
	  }]);
	  return FormSettingsPanel;
	}(landing_ui_panel_basepresetpanel.BasePresetPanel);

	exports.FormSettingsPanel = FormSettingsPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing,BX,BX.Landing,BX.Landing.UI.Panel,BX.UI.Dialogs,BX,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.Landing.UI.Component,BX.Landing.UI.Component,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.Landing.UI.Component,BX.Landing.UI.Field,BX.Landing.UI.Field,BX.UI,BX.Landing.UI.Card,BX.Crm.Form,BX.Landing.UI.Card,BX.Landing.UI.Card,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Event,BX.Landing.UI.Field,BX.UI.EntitySelector,BX.Landing,BX,BX.Landing.UI.Button,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=formsettingspanel.bundle.js.map
