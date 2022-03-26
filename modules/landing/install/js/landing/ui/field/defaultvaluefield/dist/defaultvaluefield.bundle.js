this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_field_basefield,landing_ui_field_datetimefield,landing_ui_component_internal,ui_draganddrop_draggable,landing_loc,landing_ui_component_listitem,main_core_events,landing_ui_panel_fieldspanel,landing_ui_form_formsettingsform,landing_ui_component_actionpanel,landing_ui_field_variablesfield) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-defaultvalue-list-container\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DefaultValueField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(DefaultValueField, _BaseField);
	  babelHelpers.createClass(DefaultValueField, null, [{
	    key: "isListField",
	    value: function isListField(field) {
	      return main_core.Type.isArray(field.items);
	    }
	  }]);

	  function DefaultValueField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, DefaultValueField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DefaultValueField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.DefaultValueField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.onSelectFieldButtonClick = _this.onSelectFieldButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onItemRemove = _this.onItemRemove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragEnd = _this.onDragEnd.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onFormChange = _this.onFormChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.items = [];
	    _this.actionPanel = new landing_ui_component_actionpanel.ActionPanel({
	      renderTo: _this.layout,
	      left: [{
	        id: 'selectField',
	        text: landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_ADD_FIELD'),
	        onClick: _this.onSelectFieldButtonClick
	      }]
	    });
	    _this.draggable = new ui_draganddrop_draggable.Draggable({
	      context: window.parent,
	      container: _this.getListContainer(),
	      draggable: '.landing-ui-component-list-item',
	      dragElement: '.landing-ui-button-icon-drag',
	      type: ui_draganddrop_draggable.Draggable.MOVE,
	      offset: {
	        y: -62
	      }
	    });

	    _this.draggable.subscribe('end', _this.onDragEnd);

	    _this.options.items.forEach(function (item) {
	      var itemOptions = _this.prepareItemOptions({
	        id: "".concat(item.entityName, "_").concat(item.fieldName),
	        value: item.value
	      });

	      if (itemOptions) {
	        _this.addItem(itemOptions);
	      }
	    });

	    return _this;
	  }

	  babelHelpers.createClass(DefaultValueField, [{
	    key: "prepareItemOptions",
	    value: function prepareItemOptions(options) {
	      var _this2 = this;

	      var crmField = this.getCrmFieldById(options.id);

	      if (crmField) {
	        var displayedValue = function () {
	          if (DefaultValueField.isListField(crmField)) {
	            var fieldItems = _this2.getFieldItems(crmField);

	            var item = fieldItems.find(function (currentItem) {
	              return currentItem.ID === options.value;
	            });

	            if (item) {
	              return item.VALUE;
	            }

	            if (main_core.Type.isArrayFilled(fieldItems)) {
	              return fieldItems[0].VALUE;
	            }

	            return landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_DEFAULT_VALUE');
	          }

	          if (crmField.type === 'checkbox') {
	            if (main_core.Text.toBoolean(options.value)) {
	              return landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_YES');
	            }

	            return landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_NO');
	          }

	          if (main_core.Type.isStringFilled(options.value)) {
	            return options.value;
	          }

	          return landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_DEFAULT_VALUE');
	        }();

	        var displayedLabel = function () {
	          var fieldCategory = _this2.getCrmFieldCategoryById(crmField.entity_name);

	          return "".concat(crmField.caption, " \xB7 ").concat(fieldCategory.CAPTION);
	        }();

	        return {
	          field: crmField,
	          value: options.value,
	          displayedValue: displayedValue,
	          displayedLabel: displayedLabel
	        };
	      }

	      return null;
	    }
	  }, {
	    key: "getListContainer",
	    value: function getListContainer() {
	      return this.cache.remember('listContainer', function () {
	        return main_core.Tag.render(_templateObject());
	      });
	    }
	  }, {
	    key: "createInput",
	    value: function createInput() {
	      return this.getListContainer();
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
	    key: "getCrmFieldCategoryById",
	    value: function getCrmFieldCategoryById(id) {
	      return this.options.crmFields[id];
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(options) {
	      this.items.push(new landing_ui_component_listitem.ListItem({
	        id: options.field.name,
	        title: options.displayedLabel,
	        description: options.displayedValue,
	        draggable: true,
	        editable: true,
	        removable: true,
	        appendTo: this.getListContainer(),
	        onRemove: this.onItemRemove,
	        onFormChange: this.onFormChange,
	        form: this.createItemForm(options)
	      }));
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(id) {
	      return this.items.find(function (currentItem) {
	        return currentItem.options.id === id;
	      });
	    }
	  }, {
	    key: "onItemRemove",
	    value: function onItemRemove(event) {
	      this.items = this.items.filter(function (item) {
	        return item !== event.getTarget();
	      });
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "onFormChange",
	    value: function onFormChange(event) {
	      var value = event.getTarget().getValue();
	      var item = this.getItemById(value.name);
	      var options = this.prepareItemOptions({
	        id: value.name,
	        value: value.label
	      });

	      if (item) {
	        item.setDescription(options.displayedValue);
	      }

	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "onDragEnd",
	    value: function onDragEnd() {
	      var _this3 = this;

	      setTimeout(function () {
	        _this3.items = babelHelpers.toConsumableArray(_this3.getListContainer().children).map(function (itemNode) {
	          var itemNodeId = main_core.Dom.attr(itemNode, 'data-id');
	          return _this3.items.find(function (item) {
	            return item.options.id === itemNodeId;
	          });
	        });

	        _this3.emit('onChange', {
	          skipPrepare: true
	        });
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this4 = this;

	      return this.items.map(function (item) {
	        var sourceValue = item.getValue();

	        var crmField = _this4.getCrmFieldById(sourceValue.name);

	        return {
	          entityName: crmField.entity_name,
	          fieldName: crmField.entity_field_name,
	          value: sourceValue.value
	        };
	      });
	    }
	  }, {
	    key: "onFieldsSelect",
	    value: function onFieldsSelect(selectedFields) {
	      var _this5 = this;

	      selectedFields.forEach(function (fieldId) {
	        _this5.addItem(_this5.prepareItemOptions({
	          id: fieldId
	        }));
	      });
	      this.emit('onChange', {
	        skipPrepare: true
	      });
	    }
	  }, {
	    key: "getAllowedCategories",
	    value: function getAllowedCategories() {
	      var schemeId = this.options.formOptions.document.scheme;
	      var scheme = this.options.dictionary.document.schemes.find(function (item) {
	        return String(schemeId) === String(item.id);
	      });

	      if (main_core.Type.isPlainObject(scheme)) {
	        return main_core.Runtime.clone(scheme.entities);
	      }

	      return [];
	    }
	  }, {
	    key: "onSelectFieldButtonClick",
	    value: function onSelectFieldButtonClick(event) {
	      var _this6 = this;

	      event.preventDefault();
	      landing_ui_panel_fieldspanel.FieldsPanel.getInstance({
	        isLeadEnabled: this.options.isLeadEnabled
	      }).show({
	        isLeadEnabled: this.options.isLeadEnabled,
	        allowedCategories: this.getAllowedCategories(),
	        allowedTypes: ['string', 'list', 'checkbox', 'radio', 'text', 'integer', 'double', 'date', 'datetime', 'typed_string']
	      }).then(function (selectedFields) {
	        _this6.options.crmFields = landing_ui_panel_fieldspanel.FieldsPanel.getInstance().getOriginalCrmFields();

	        _this6.onFieldsSelect(selectedFields);
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getFieldItems",
	    value: function getFieldItems(field) {
	      if (field.entity_field_name === 'STAGE_ID') {
	        if (main_core.Type.isPlainObject(this.options.formOptions.document) && main_core.Type.isPlainObject(this.options.formOptions.document.deal)) {
	          var categoryId = main_core.Text.toNumber(this.options.formOptions.document.deal.category);

	          if (categoryId > 0) {
	            return field.itemsByCategory[categoryId];
	          }
	        }
	      }

	      return field.items;
	    }
	  }, {
	    key: "createItemForm",
	    value: function createItemForm() {
	      var _this7 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var form = new landing_ui_form_formsettingsform.FormSettingsForm({
	        serializeModifier: function serializeModifier(value) {
	          if (options.field.type === 'list' || options.field.type === 'checkbox' || options.field.type === 'bool') {
	            var valueItem = _this7.getFieldItems(form.fields[0]).find(function (item) {
	              return item.value === value.value;
	            });

	            if (valueItem) {
	              value.label = valueItem.name;
	            }
	          } else {
	            value.label = value.value;
	          }

	          return value;
	        }
	      });

	      if (DefaultValueField.isListField(options.field)) {
	        form.addField(new BX.Landing.UI.Field.Dropdown({
	          selector: 'value',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
	          content: options.value,
	          items: this.getFieldItems(options.field).map(function (item) {
	            return {
	              name: item.VALUE,
	              value: item.ID
	            };
	          })
	        }));
	        return form;
	      }

	      if (options.field.type === 'bool' || options.field.type === 'checkbox') {
	        form.addField(new BX.Landing.UI.Field.Dropdown({
	          selector: 'value',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
	          content: options.value,
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_NO'),
	            value: 'N'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_DEFAULT_VALUE_FIELD_CHECKBOX_YES'),
	            value: 'Y'
	          }]
	        }));
	        return form;
	      }

	      if (options.field.type === 'date' || options.field.type === 'datetime') {
	        form.addField(new landing_ui_field_datetimefield.DateTimeField({
	          selector: 'value',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
	          time: options.field.type === 'datetime',
	          content: options.value || ''
	        }));
	        return form;
	      }

	      form.addField(new landing_ui_field_variablesfield.VariablesField({
	        selector: 'value',
	        title: landing_loc.Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_VALUE_FIELD_TITLE'),
	        variables: this.options.personalizationVariables,
	        content: options.value || ''
	      }));
	      return form;
	    }
	  }]);
	  return DefaultValueField;
	}(landing_ui_field_basefield.BaseField);

	exports.DefaultValueField = DefaultValueField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Field,BX.Landing.Ui.Field,BX.Landing.UI.Component,BX.UI.DragAndDrop,BX.Landing,BX.Landing.UI.Component,BX.Event,BX.Landing.UI.Panel,BX.Landing.UI.Form,BX.Landing.UI.Component,BX.Landing.UI.Field));
//# sourceMappingURL=defaultvaluefield.bundle.js.map
