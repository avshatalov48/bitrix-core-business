this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_card_headercard,landing_ui_panel_basepresetpanel,landing_ui_field_radiobuttonfield,main_core_events,landing_ui_form_formsettingsform,landing_ui_card_messagecard,ui_dialogs_messagebox,landing_ui_field_basefield,landing_loc,main_core) {
	'use strict';

	var _templateObject;

	var fetchId = function fetchId(item) {
	  return !main_core.Type.isNil(item.ID) ? item.ID : item.id;
	};

	var fetchName = function fetchName(item) {
	  return !main_core.Type.isNil(item.NAME) ? item.NAME : item.name;
	};

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
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-stages\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.getCategoriesDropdown().getLayout());
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
	              name: fetchName(category),
	              value: fetchId(category)
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
	        return String(fetchId(category)) === String(currentCategoryId);
	      });
	    }
	  }, {
	    key: "onCategoryChange",
	    value: function onCategoryChange() {
	      this.emit('onChange');
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        category: this.getCategoriesDropdown().getValue(),
	        stage: ''
	      };
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var preventEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      this.getCategoriesDropdown().setValue(value.category);

	      if (!preventEvent) {
	        this.emit('onChange');
	      }
	    }
	  }]);
	  return StageField;
	}(landing_ui_field_basefield.BaseField);

	var messageIcon = "/bitrix/js/landing/ui/panel/formsettingspanel/content/crm/dist/images/message-icon.svg";

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

	    _this.setLastScheme(_this.options.formOptions.document.scheme);

	    _this.setLastDealCategory(_this.options.formOptions.document.deal.category);

	    return _this;
	  }

	  babelHelpers.createClass(CrmContent, [{
	    key: "isDynamicAvailable",
	    value: function isDynamicAvailable() {
	      return main_core.Type.isArrayFilled(this.options.dictionary.document.dynamic);
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
	          value: [_this2.options.formOptions.document.duplicateMode ? _this2.options.formOptions.document.duplicateMode : 'ALLOW'],
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
	          value: [_this3.options.formOptions.payment.use],
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
	        var scheme = _this4.getSchemeById(_this4.options.formOptions.document.scheme);

	        var isOpened = function () {
	          if (scheme && scheme.dynamic === true) {
	            return String(scheme.id).endsWith('1');
	          }

	          return main_core.Text.toNumber(_this4.options.formOptions.document.scheme) > 4;
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
	            icon: messageIcon,
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
	        var currentScheme = _this5.getSchemeById(_this5.options.formOptions.document.scheme);

	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'dynamicScheme',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_SMART_ENTITY_LIST'),
	          items: _this5.options.dictionary.document.dynamic.map(function (scheme) {
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
	                id: _this8.options.formOptions.document.scheme
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
	            if (String(_this8.options.formOptions.document.scheme) === '8') {
	              return 1;
	            }

	            if (String(_this8.options.formOptions.document.scheme) === '5') {
	              return 2;
	            }

	            if (String(_this8.options.formOptions.document.scheme) === '6') {
	              return 3;
	            }

	            if (String(_this8.options.formOptions.document.scheme) === '7') {
	              return 4;
	            }

	            var scheme = _this8.getSchemeById(_this8.options.formOptions.document.scheme);

	            if (main_core.Type.isPlainObject(scheme) && scheme.dynamic === true) {
	              return 'smart';
	            }

	            return _this8.options.formOptions.document.scheme;
	          }(),
	          items: items,
	          onChange: _this8.onTypeChange.bind(_this8)
	        });
	      });
	    }
	  }, {
	    key: "getDealCategoryField",
	    value: function getDealCategoryField() {
	      var _this9 = this;

	      return this.cache.remember('dealCategoryField', function () {
	        return new StageField({
	          categories: _this9.options.categories,
	          value: {
	            category: _this9.options.formOptions.document.deal.category
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
	            category: _this10.options.formOptions.document.dynamic.category
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
	          value: [_this11.options.formOptions.document.deal.duplicatesEnabled || 'Y'],
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
	      var _this12 = this;

	      return this.options.dictionary.document.schemes.find(function (scheme) {
	        return String(scheme.id) === String(id) || id === 'smart' && scheme.dynamic && String(scheme.id) === String(_this12.getSelectedSchemeId());
	      });
	    }
	  }, {
	    key: "getDynamicSchemeById",
	    value: function getDynamicSchemeById(id) {
	      var _this$getSchemeById = this.getSchemeById(id),
	          mainEntity = _this$getSchemeById.mainEntity;

	      return this.options.dictionary.document.dynamic.find(function (scheme) {
	        return String(scheme.id) === String(mainEntity);
	      });
	    }
	  }, {
	    key: "setLastScheme",
	    value: function setLastScheme(schemeId) {
	      this.cache.set('lastScheme', schemeId);
	    }
	  }, {
	    key: "getLastScheme",
	    value: function getLastScheme() {
	      return this.cache.get('lastScheme');
	    }
	  }, {
	    key: "setLastDealCategory",
	    value: function setLastDealCategory(categoryId) {
	      this.cache.set('lastDealCategory', categoryId);
	    }
	  }, {
	    key: "getLastDealCategory",
	    value: function getLastDealCategory() {
	      return this.cache.get('lastDealCategory', null);
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
	        expertSettingsForm.addField(this.getDealCategoryField());
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
	    key: "setAdditionalValue",
	    value: function setAdditionalValue(value) {
	      this.cache.set('additionalValue', value);
	    }
	  }, {
	    key: "getAdditionalValue",
	    value: function getAdditionalValue() {
	      return this.cache.get('additionalValue', {});
	    }
	  }, {
	    key: "getEntityChangeConfirm",
	    value: function getEntityChangeConfirm() {
	      return this.cache.remember('entityChangeConfirm', function () {
	        return new ui_dialogs_messagebox.MessageBox({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TITLE'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL
	        });
	      });
	    }
	  }, {
	    key: "getDealCategoryChangeConfirm",
	    value: function getDealCategoryChangeConfirm() {
	      return this.cache.remember('dealCategoryChangeConfirm', function () {
	        return new ui_dialogs_messagebox.MessageBox({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TITLE'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL
	        });
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      var _this13 = this;

	      var value = this.getValue();
	      var scheme = this.getSchemeById(value.document.scheme);

	      if (main_core.Type.isPlainObject(scheme)) {
	        var allowedEntities = scheme.entities;
	        var removedFields = this.options.formOptions.presetFields.filter(function (presetField) {
	          return !allowedEntities.includes(presetField.entityName);
	        }).map(function (presetField) {
	          return _this13.getCrmFieldById("".concat(presetField.entityName, "_").concat(presetField.fieldName));
	        });

	        if (main_core.Type.isArrayFilled(removedFields)) {
	          var itemTemplate = landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_ITEM_TEMPLATE');
	          var entityName = main_core.Text.encode(itemTemplate.replace('{text}', scheme.name));

	          var messageText = function () {
	            var fields = removedFields.map(function (field) {
	              return itemTemplate.replace('{text}', main_core.Text.encode(field.caption));
	            });

	            if (removedFields.length > 1) {
	              var lastField = fields.pop();
	              return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TEXT').replace('{fieldsList}', fields.join(', ')).replace('{lastField}', main_core.Text.encode(lastField)).replaceAll('{entityName}', entityName);
	            }

	            return landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CHANGE_CONFIRM_TEXT_1').replace('{fieldName}', fields.join(', ')).replaceAll('{entityName}', entityName);
	          }();

	          var entityChangeConfirm = this.getEntityChangeConfirm();
	          entityChangeConfirm.setOkCallback(function () {
	            entityChangeConfirm.close();
	            entityChangeConfirm.getOkButton().setDisabled(false);
	            entityChangeConfirm.getCancelButton().setDisabled(false);

	            var filteredFields = _this13.options.formOptions.presetFields.filter(function (presetField) {
	              return allowedEntities.includes(presetField.entityName);
	            });

	            _this13.setLastScheme(scheme.id);

	            _this13.setAdditionalValue({
	              presetFields: filteredFields
	            });

	            _this13.options.formOptions.presetFields = filteredFields;

	            _this13.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	              skipPrepare: true
	            }));

	            _this13.setAdditionalValue({});
	          });
	          entityChangeConfirm.setCancelCallback(function () {
	            entityChangeConfirm.close();
	            entityChangeConfirm.getOkButton().setDisabled(false);
	            entityChangeConfirm.getCancelButton().setDisabled(false);

	            var lastScheme = _this13.getSchemeById(_this13.getLastScheme());

	            if (lastScheme.dynamic) {
	              _this13.getTypesField().setValue('smart', true);

	              _this13.getDynamicEntitiesField().setValue(lastScheme.mainEntity, true);
	            } else {
	              _this13.getTypesField().setValue(lastScheme.id);
	            }

	            _this13.onTypeChange(new main_core_events.BaseEvent({
	              data: {
	                item: {
	                  id: lastScheme.id
	                }
	              }
	            }));
	          });
	          entityChangeConfirm.setMessage(messageText);
	          entityChangeConfirm.show();
	          return;
	        }

	        if (String(scheme.id) === '3' || String(scheme.id) === '6') {
	          var lastDealCategory = this.getLastDealCategory();

	          if (main_core.Text.toNumber(value.document.deal.category) !== main_core.Text.toNumber(lastDealCategory)) {
	            var dealStageField = this.options.formOptions.presetFields.find(function (presetField) {
	              return presetField.entityName === 'DEAL' && presetField.fieldName === 'STAGE_ID';
	            });

	            if (dealStageField) {
	              var crmField = this.getCrmFieldById('DEAL_STAGE_ID');
	              var dealCategoryChangeConfirm = this.getDealCategoryChangeConfirm();
	              var fieldName = landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_ITEM_TEMPLATE').replace('{text}', main_core.Text.encode(crmField.caption));

	              var _messageText = landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CATEGORY_CHANGE_CONFIRM_TEXT').replace('{fieldName}', fieldName);

	              dealCategoryChangeConfirm.setMessage(_messageText);
	              dealCategoryChangeConfirm.setOkCallback(function () {
	                dealCategoryChangeConfirm.close();
	                dealCategoryChangeConfirm.getOkButton().setDisabled(false);
	                dealCategoryChangeConfirm.getCancelButton().setDisabled(false);

	                var filteredFields = _this13.options.formOptions.presetFields.filter(function (presetField) {
	                  return presetField !== dealStageField;
	                });

	                _this13.options.formOptions.presetFields = filteredFields;

	                _this13.setLastDealCategory(value.document.deal.category);

	                _this13.setAdditionalValue({
	                  presetFields: filteredFields
	                });

	                _this13.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	                  skipPrepare: true
	                }));

	                _this13.setAdditionalValue({});
	              });
	              dealCategoryChangeConfirm.setCancelCallback(function () {
	                dealCategoryChangeConfirm.close();
	                dealCategoryChangeConfirm.getOkButton().setDisabled(false);
	                dealCategoryChangeConfirm.getCancelButton().setDisabled(false);

	                _this13.getDealCategoryField().setValue({
	                  category: _this13.getLastDealCategory()
	                });

	                _this13.setAdditionalValue({});
	              });
	              dealCategoryChangeConfirm.show();
	              return;
	            }
	          }
	        }
	      }

	      this.emit('onChange', babelHelpers.objectSpread({}, event.getData(), {
	        skipPrepare: true
	      }));
	    }
	  }, {
	    key: "getCrmFieldById",
	    value: function getCrmFieldById(id) {
	      return Object.values(this.options.crmFields).reduce(function (acc, category) {
	        return [].concat(babelHelpers.toConsumableArray(acc), babelHelpers.toConsumableArray(category.FIELDS));
	      }, []).find(function (currentField) {
	        return currentField.name === id;
	      });
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
	        reducedValue.deal.category = this.getDealCategoryField().getValue().category;
	      }

	      var scheme = this.getSchemeById(reducedValue.scheme);

	      if (main_core.Type.isPlainObject(scheme) && scheme.dynamic) {
	        reducedValue.dynamic.category = this.getDynamicCategoriesField(scheme.id).getValue().category;
	      }

	      return babelHelpers.objectSpread({
	        document: reducedValue
	      }, this.getAdditionalValue());
	    }
	  }]);
	  return CrmContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = CrmContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Event,BX.Landing.UI.Form,BX.Landing.UI.Card,BX.UI.Dialogs,BX.Landing.UI.Field,BX.Landing,BX));
//# sourceMappingURL=crm.bundle.js.map
