this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,ui_buttons,landing_ui_card_headercard,landing_ui_panel_basepresetpanel,landing_ui_field_radiobuttonfield,main_core_events,landing_ui_form_formsettingsform,ui_dialogs_messagebox,landing_ui_field_basefield,landing_loc,main_core,landing_ui_panel_formsettingspanel_content_crm_schememanager) {
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

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _schemeManager = /*#__PURE__*/new WeakMap();
	var _setDuplicatesEnabledFieldDependency = /*#__PURE__*/new WeakSet();
	var CrmContent = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(CrmContent, _ContentWrapper);
	  function CrmContent(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, CrmContent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CrmContent).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _setDuplicatesEnabledFieldDependency);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _schemeManager, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.CrmContent');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _schemeManager, new landing_ui_panel_formsettingspanel_content_crm_schememanager.SchemeManager(babelHelpers.toConsumableArray(options.dictionary.document.schemes)));
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
	    key: "getOrderSettingsForm",
	    value: function getOrderSettingsForm() {
	      var _this3 = this;
	      return this.cache.remember('formSettingsForm', function () {
	        var scheme = _this3.getSchemeById(_this3.options.formOptions.document.scheme);
	        var isOpened = function () {
	          if (scheme && scheme.dynamic === true) {
	            return String(scheme.id).endsWith('1');
	          }
	          return main_core.Text.toNumber(_this3.options.formOptions.document.scheme) > 4;
	        }();
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_HEADER'),
	          toggleable: true,
	          opened: isOpened,
	          fields: []
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
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4_MSGVER_1').replace('&nbsp;', ' '),
	          level: 2
	        });
	      });
	    }
	  }, {
	    key: "getType6Header",
	    value: function getType6Header() {
	      return this.cache.remember('type6header', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_6').replace('&nbsp;', ' '),
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
	      var _this4 = this;
	      return this.cache.remember('dynamicEntitiesField', function () {
	        var currentScheme = _this4.getSchemeById(_this4.options.formOptions.document.scheme);
	        return new BX.Landing.UI.Field.Dropdown({
	          selector: 'dynamicScheme',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_SMART_ENTITY_LIST'),
	          items: _this4.options.dictionary.document.dynamic.map(function (scheme) {
	            return {
	              name: scheme.name,
	              value: scheme.id
	            };
	          }),
	          content: currentScheme.mainEntity,
	          onChange: function onChange() {
	            _this4.onTypeChange(new main_core_events.BaseEvent({
	              data: {
	                item: {
	                  id: _this4.getSelectedSchemeId()
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
	      var _this5 = this;
	      return this.cache.remember('dynamicEntitySettingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          opened: true,
	          hidden: true,
	          fields: [_this5.getDynamicEntitiesField()]
	        });
	      });
	    }
	  }, {
	    key: "getExpertSettingsForm",
	    value: function getExpertSettingsForm() {
	      var _this6 = this;
	      return this.cache.remember('expertSettingsForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_EXPERT_MODE'),
	          toggleable: true,
	          toggleableType: landing_ui_form_formsettingsform.FormSettingsForm.ToggleableType.Link,
	          opened: false,
	          fields: [new landing_ui_card_headercard.HeaderCard({
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1').replace('&nbsp;', ' '),
	            level: 2
	          }), _this6.getDuplicatesField()]
	        });
	      });
	    }
	  }, {
	    key: "getTypesField",
	    value: function getTypesField() {
	      var _this7 = this;
	      return this.cache.remember('typesField', function () {
	        setTimeout(function () {
	          _this7.onTypeChange(new main_core_events.BaseEvent({
	            data: {
	              item: {
	                id: _this7.options.formOptions.document.scheme
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
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_4_MSGVER_1'),
	          icon: 'landing-ui-crm-entity-type4'
	        }, {
	          id: '310',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_310'),
	          icon: 'landing-ui-crm-entity-type310'
	        }];
	        if (_this7.isDynamicAvailable()) {
	          items.push({
	            id: 'smart',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_5'),
	            icon: 'landing-ui-crm-entity-type5'
	          });
	        }
	        if (_this7.options.isLeadEnabled) {
	          items.unshift({
	            id: '1',
	            title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_CRM_TYPE_1'),
	            icon: 'landing-ui-crm-entity-type1'
	          });
	        }
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selector: 'scheme',
	          value: function () {
	            var schemeId = main_core.Text.toNumber(_this7.options.formOptions.document.scheme);
	            if (babelHelpers.classPrivateFieldGet(_this7, _schemeManager).isDefaultScheme(schemeId) && babelHelpers.classPrivateFieldGet(_this7, _schemeManager).isInvoice(schemeId)) {
	              return babelHelpers.classPrivateFieldGet(_this7, _schemeManager).getSpecularSchemeId(schemeId);
	            }
	            if (String(_this7.options.formOptions.document.scheme) === '310') {
	              return 310;
	            }
	            var scheme = _this7.getSchemeById(_this7.options.formOptions.document.scheme);
	            if (main_core.Type.isPlainObject(scheme) && scheme.dynamic === true) {
	              return 'smart';
	            }
	            return String(schemeId);
	          }(),
	          items: items,
	          onChange: _this7.onTypeChange.bind(_this7)
	        });
	      });
	    }
	  }, {
	    key: "getDealCategoryField",
	    value: function getDealCategoryField() {
	      var _this8 = this;
	      return this.cache.remember('dealCategoryField', function () {
	        return new StageField({
	          categories: _this8.options.categories,
	          value: {
	            category: _this8.options.formOptions.document.deal.category
	          }
	        });
	      });
	    }
	  }, {
	    key: "getDynamicCategoriesField",
	    value: function getDynamicCategoriesField(schemeId) {
	      var _this9 = this;
	      return this.cache.remember("dynamicCategories#".concat(schemeId), function () {
	        var scheme = _this9.getDynamicSchemeById(schemeId);
	        return new StageField({
	          listTitle: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_SMART_STAGES_FIELD_TITLE'),
	          categories: scheme.categories,
	          value: {
	            category: _this9.options.formOptions.document.dynamic.category
	          }
	        });
	      });
	    }
	  }, {
	    key: "getDuplicatesEnabledField",
	    value: function getDuplicatesEnabledField() {
	      var _this10 = this;
	      return this.cache.remember('duplicatesEnabledField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'duplicatesEnabled',
	          compact: true,
	          value: [_this10.options.formOptions.document.deal.duplicatesEnabled || 'Y'],
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
	      var _this11 = this;
	      return this.options.dictionary.document.schemes.find(function (scheme) {
	        return String(scheme.id) === String(id) || id === 'smart' && scheme.dynamic && String(scheme.id) === String(_this11.getSelectedSchemeId());
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
	        expertSettingsForm.addField(this.getDuplicatesField());
	        expertSettingsForm.addField(this.getDuplicatesEnabledField());
	        _classPrivateMethodGet(this, _setDuplicatesEnabledFieldDependency, _setDuplicatesEnabledFieldDependency2).call(this);
	      }
	      if (String(item.id) === '4' || String(item.id) === '7') {
	        expertSettingsForm.addField(this.getType4Header());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }
	      if (String(item.id) === '310') {
	        expertSettingsForm.addField(this.getType6Header());
	        expertSettingsForm.addField(this.getDuplicatesField());
	      }
	      if (main_core.Text.toNumber(item.id) > 4 && main_core.Type.isPlainObject(scheme) && scheme.dynamic !== true && String(item.id) !== '9' || this.getOrderSettingsForm().isOpened()) {
	        this.getOrderSettingsForm().onSwitchChange(true);
	      }
	      if (main_core.Type.isPlainObject(scheme) && (String(item.id) === 'smart' || scheme.dynamic === true) && this.isDynamicAvailable()) {
	        expertSettingsForm.addField(this.getDynamicHeader(scheme.name));
	        var dynamicScheme = this.getDynamicSchemeById(scheme.id);
	        if (dynamicScheme && dynamicScheme.categories) {
	          expertSettingsForm.addField(this.getDynamicCategoriesField(scheme.id));
	        }
	        expertSettingsForm.addField(this.getDuplicatesField());
	        if (String(scheme.id).endsWith('1')) {
	          this.getOrderSettingsForm().onSwitchChange(true);
	        }
	        this.getDynamicEntitySettingsForm().show();
	      }
	      this.addItem(expertSettingsForm);
	      if (String(item.id) !== '310') {
	        this.addItem(this.getOrderSettingsForm());
	      }
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
	    key: "getCreateOrderChangeConfirm",
	    value: function getCreateOrderChangeConfirm() {
	      return this.cache.remember('createOrderChangeConfirm', function () {
	        return new ui_dialogs_messagebox.MessageBox({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_SCHEME_CREATE_ORDER_CHANGE_CONFIRM_TITLE'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          message: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_TITLE_1')
	        });
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      var _this12 = this;
	      var value = this.getValue();
	      var scheme = this.getSchemeById(value.document.scheme);
	      if (main_core.Type.isPlainObject(scheme)) {
	        var allowedEntities = scheme.entities;
	        var removedFields = this.options.formOptions.presetFields.filter(function (presetField) {
	          return !allowedEntities.includes(presetField.entityName);
	        }).map(function (presetField) {
	          return _this12.getCrmFieldById("".concat(presetField.entityName, "_").concat(presetField.fieldName));
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
	            var filteredFields = _this12.options.formOptions.presetFields.filter(function (presetField) {
	              return allowedEntities.includes(presetField.entityName);
	            });
	            _this12.setLastScheme(scheme.id);
	            _this12.setAdditionalValue({
	              presetFields: filteredFields
	            });
	            _this12.options.formOptions.presetFields = filteredFields;
	            _this12.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	              skipPrepare: true
	            }));
	            _this12.setAdditionalValue({});
	          });
	          entityChangeConfirm.setCancelCallback(function () {
	            entityChangeConfirm.close();
	            entityChangeConfirm.getOkButton().setDisabled(false);
	            entityChangeConfirm.getCancelButton().setDisabled(false);
	            var lastScheme = _this12.getSchemeById(_this12.getLastScheme());
	            if (lastScheme.dynamic) {
	              _this12.getTypesField().setValue('smart', true);
	              _this12.getDynamicEntitiesField().setValue(lastScheme.mainEntity, true);
	            } else {
	              _this12.getTypesField().setValue(lastScheme.id);
	            }
	            _this12.onTypeChange(new main_core_events.BaseEvent({
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
	                var filteredFields = _this12.options.formOptions.presetFields.filter(function (presetField) {
	                  return presetField !== dealStageField;
	                });
	                _this12.options.formOptions.presetFields = filteredFields;
	                _this12.setLastDealCategory(value.document.deal.category);
	                _this12.setAdditionalValue({
	                  presetFields: filteredFields
	                });
	                _this12.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
	                  skipPrepare: true
	                }));
	                _this12.setAdditionalValue({});
	              });
	              dealCategoryChangeConfirm.setCancelCallback(function () {
	                dealCategoryChangeConfirm.close();
	                dealCategoryChangeConfirm.getOkButton().setDisabled(false);
	                dealCategoryChangeConfirm.getCancelButton().setDisabled(false);
	                _this12.getDealCategoryField().setValue({
	                  category: _this12.getLastDealCategory()
	                });
	                _this12.setAdditionalValue({});
	              });
	              dealCategoryChangeConfirm.show();
	              return;
	            }
	          }
	        }
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _schemeManager).isInvoice(scheme.id) && value.document.payment.use) {
	        var createOrderMessageBox = this.getCreateOrderChangeConfirm();
	        createOrderMessageBox.setButtons([new ui_buttons.Button().setColor(ui_buttons.ButtonColor.PRIMARY).setText(landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_CANCEL')).setNoCaps(true).bindEvent('click', function (button) {
	          createOrderMessageBox.close();
	          button.setDisabled(false);
	          var orderSettingsSwitch = _this12.getOrderSettingsForm().getSwitch();
	          orderSettingsSwitch.setValue(true);
	          orderSettingsSwitch.onChange();
	          _this12.onChange(event);
	        }), new ui_buttons.Button().setColor(ui_buttons.ButtonColor.LIGHT).setText(landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_PANEL_CRM_CREATE_ORDER_MESSAGE_BOX_OK')).setNoCaps(true).bindEvent('click', function (button) {
	          createOrderMessageBox.close();
	          button.setDisabled(false);
	          _this12.options.formOptions.payment.use = false;
	          _this12.onChange(event);
	        })]);
	        createOrderMessageBox.show();
	      }
	      this.emit('onChange', _objectSpread(_objectSpread({}, event.getData()), {}, {
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
	          use: this.options.formOptions.payment.use,
	          payer: this.options.formOptions.payment.payer,
	          disabledSystems: this.options.formOptions.payment.disabledSystems
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
	      var dynamicScheme = this.getDynamicSchemeById(reducedValue.scheme);
	      if (main_core.Type.isPlainObject(scheme) && scheme.dynamic && dynamicScheme && dynamicScheme.categories) {
	        reducedValue.dynamic.category = this.getDynamicCategoriesField(scheme.id).getValue().category;
	      }
	      return _objectSpread({
	        document: reducedValue
	      }, this.getAdditionalValue());
	    }
	  }]);
	  return CrmContent;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);
	function _setDuplicatesEnabledFieldDependency2() {
	  var _this13 = this;
	  var allowType = 'ALLOW';
	  var duplicatesField = this.getDuplicatesField();
	  var duplicatesEnabledField = this.getDuplicatesEnabledField();
	  var isDuplicatesFieldAllowed = function isDuplicatesFieldAllowed() {
	    return _this13.getDuplicatesField().getValue()[0] === allowType;
	  };
	  if (isDuplicatesFieldAllowed()) {
	    main_core.Dom.hide(duplicatesEnabledField.layout);
	  }
	  duplicatesField.subscribe('onchange', function () {
	    if (isDuplicatesFieldAllowed()) {
	      main_core.Dom.hide(duplicatesEnabledField.layout);
	      return;
	    }
	    main_core.Dom.show(duplicatesEnabledField.layout);
	  });
	}

	exports.default = CrmContent;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.UI,BX.Landing.UI.Card,BX.Landing.UI.Panel,BX.Landing.UI.Field,BX.Event,BX.Landing.UI.Form,BX.UI.Dialogs,BX.Landing.UI.Field,BX.Landing,BX,BX.Landing.Ui.Panel.Formsettingspanel.Content.Crm));
//# sourceMappingURL=crm.bundle.js.map
