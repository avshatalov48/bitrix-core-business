this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,landing_ui_component_actionpanel,main_core,main_core_events,landing_ui_component_internal,landing_ui_component_iconbutton,landing_loc,ui_draganddrop_draggable,landing_pageobject,landing_ui_field_textfield,main_popup) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var defaultOptions = {
	  removable: true,
	  draggable: false,
	  // eslint-disable-next-line no-use-before-define
	  color: 'blue'
	};
	var FieldElement = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FieldElement, _EventEmitter);

	  function FieldElement(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldElement);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldElement).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.RuleField.FieldElement');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.options = _objectSpread(_objectSpread({}, defaultOptions), options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(FieldElement, [{
	    key: "getDragButtonLayout",
	    value: function getDragButtonLayout() {
	      return this.cache.remember('dragButton', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.drag,
	          style: {
	            width: '20px'
	          }
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "getActionsDropdown",
	    value: function getActionsDropdown() {
	      var _this2 = this;

	      return this.cache.remember('actionsDropdown', function () {
	        var field = new window.top.BX.Landing.UI.Field.DropdownInline({
	          title: _this2.options.actionsLabel,
	          items: _this2.options.actionsList,
	          content: _this2.options.actionsValue
	        });
	        field.subscribe('onChange', function () {
	          _this2.emit('onChange');
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getActionsLayout",
	    value: function getActionsLayout() {
	      var _this3 = this;

	      return this.cache.remember('actionsLayout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-element-text-action\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.getActionsDropdown().getLayout());
	      });
	    }
	  }, {
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      var _this4 = this;

	      return this.cache.remember('titleLayout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-element-text-title\">", "</div>"])), _this4.options.title);
	      });
	    }
	  }, {
	    key: "getRemoveButtonLayout",
	    value: function getRemoveButtonLayout() {
	      var _this5 = this;

	      return this.cache.remember('removeButton', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.remove,
	          onClick: function onClick() {
	            return _this5.emit('onRemove');
	          },
	          iconSize: '9px',
	          style: {
	            width: '20px',
	            marginLeft: 'auto'
	          }
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this6 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-element-", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-field-element-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this6.options.color, _this6.options.draggable ? _this6.getDragButtonLayout() : '', _this6.options.actionsLabel ? _this6.getActionsLayout() : '', _this6.getTitleLayout(), _this6.options.removable ? _this6.getRemoveButtonLayout() : '');
	      });
	    }
	  }]);
	  return FieldElement;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(FieldElement, "Colors", {
	  blue: 'blue',
	  green: 'green',
	  red: 'red'
	});

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10;

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var RuleEntry = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(RuleEntry, _EventEmitter);

	  function RuleEntry(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RuleEntry);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RuleEntry).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.RuleField.RuleEntry');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.options = _objectSpread$1({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.draggable = new ui_draganddrop_draggable.Draggable({
	      container: _this.getExpressionContainer(),
	      dragElement: '.landing-ui-button-icon-drag',
	      draggable: '.landing-ui-field-element-green',
	      type: ui_draganddrop_draggable.Draggable.HEADLESS,
	      context: window.parent,
	      offset: {
	        y: -62
	      }
	    });

	    _this.draggable.subscribe('end', _this.onDragEnd.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.state = {
	      condition: {
	        field: _this.options.condition.field.id,
	        value: _this.options.condition.value,
	        operator: _this.options.condition.operator
	      },
	      expression: _this.options.expression.map(function (item) {
	        return {
	          field: item.field.id,
	          action: item.action
	        };
	      })
	    };

	    _this.options.expression.forEach(function (item) {
	      _this.addExpressionItem({
	        id: item.field.id,
	        label: item.field.label,
	        action: item.action,
	        preventEvent: true
	      });
	    });

	    return _this;
	  }

	  babelHelpers.createClass(RuleEntry, [{
	    key: "getOperatorField",
	    value: function getOperatorField() {
	      var _this2 = this;

	      return this.cache.remember('operatorField', function () {
	        var condition = _this2.options.dictionary.deps.condition;
	        return new BX.Landing.UI.Field.Radio({
	          selector: 'operator',
	          value: [_this2.state.condition.operator],
	          items: condition.operations.filter(function (item) {
	            return !main_core.Type.isArrayFilled(item.fieldTypes) || item.fieldTypes.includes(_this2.options.condition.field.type);
	          }).map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          }),
	          onChange: _this2.onOperatorChange.bind(_this2)
	        });
	      });
	    }
	  }, {
	    key: "getOperatorLabel",
	    value: function getOperatorLabel(operator) {
	      var operatorField = this.getOperatorField();
	      return operatorField.items.reduce(function (acc, item) {
	        return String(item.value) === String(operator) ? item.name : acc;
	      }, '');
	    }
	  }, {
	    key: "onOperatorChange",
	    value: function onOperatorChange() {
	      var operatorField = this.getOperatorField();

	      var _operatorField$getVal = operatorField.getValue(),
	          _operatorField$getVal2 = babelHelpers.slicedToArray(_operatorField$getVal, 1),
	          value = _operatorField$getVal2[0];

	      this.getOperatorLabelLayout().textContent = this.getOperatorLabel(value);
	      this.state.condition.operator = value;
	      this.emit('onChange');
	    }
	  }, {
	    key: "getSeparator",
	    value: function getSeparator() {
	      return this.cache.remember('separator', function () {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"value-settings-item-separator\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "renderValueRadioButton",
	    value: function renderValueRadioButton(_ref) {
	      var _this3 = this;

	      var label = _ref.label,
	          value = _ref.value,
	          id = _ref.id,
	          checked = _ref.checked;

	      var onChange = function onChange() {
	        _this3.setValueLabelText(label);

	        _this3.state.condition.value = value;

	        _this3.emit('onChange');
	      };

	      return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"value-settings-item value-settings-item-value\">\n\t\t\t\t<input \n\t\t\t\t\ttype=\"radio\" \n\t\t\t\t\tid=\"value_", "_", "\" \n\t\t\t\t\tname=\"value_", "_", "\"\n\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t\t", "\n\t\t\t\t>\n\t\t\t\t<label for=\"value_", "_", "\">", "</label>\n\t\t\t</div>\n\t\t"])), id, value, id, this.options.condition.field.id, onChange, checked ? 'checked' : '', id, value, label);
	    }
	  }, {
	    key: "getValueSettingsPopup",
	    value: function getValueSettingsPopup() {
	      var _this4 = this;

	      return this.cache.remember('valueSettingsPopup', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var popupContent = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"value-settings-popup\"></div>"])));
	        var random = main_core.Text.getRandom();

	        if (_this4.options.condition.field.type === 'list' || _this4.options.condition.field.type === 'checkbox' || _this4.options.condition.field.type === 'radio' || _this4.options.condition.field.type === 'bool') {
	          var operatorField = _this4.getOperatorField();

	          operatorField.setValue(_this4.options.condition.operator);
	          main_core.Dom.append(operatorField.getLayout(), popupContent);
	          main_core.Dom.append(_this4.getSeparator(), popupContent);

	          var valueItems = function () {
	            if (_this4.options.condition.field.type === 'bool') {
	              return [{
	                label: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES'),
	                value: 'Y'
	              }, {
	                label: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO'),
	                value: 'N'
	              }];
	            }

	            return _this4.options.condition.field.items;
	          }();

	          valueItems.forEach(function (item) {
	            var checked = String(_this4.options.condition.value) === String(item.value);
	            main_core.Dom.append(main_core.Dom.append(_this4.renderValueRadioButton(_objectSpread$1(_objectSpread$1({}, item), {}, {
	              id: random,
	              checked: checked
	            })), popupContent), popupContent);
	          });
	        } else {
	          var _operatorField = _this4.getOperatorField();

	          _operatorField.setValue(_this4.options.condition.operator);

	          main_core.Dom.append(_operatorField.getLayout(), popupContent);
	          main_core.Dom.append(_this4.getSeparator(), popupContent);
	          var inputField = new landing_ui_field_textfield.TextField({
	            textOnly: true,
	            onValueChange: function onValueChange() {
	              var conditionValue = inputField.getValue() || landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_EMPTY');

	              _this4.setValueLabelText(conditionValue);

	              _this4.state.condition.value = inputField.getValue();

	              _this4.emit('onChange');
	            },
	            content: _this4.options.condition.value
	          });
	          main_core.Dom.append(inputField.getLayout(), popupContent);
	        }

	        return new rootWindow.BX.Main.Popup({
	          bindElement: _this4.getConditionValueLayout(),
	          content: popupContent,
	          width: 228,
	          autoHide: true,
	          offsetLeft: 8,
	          offsetTop: 1,
	          maxHeight: 200,
	          events: {
	            onShow: function onShow() {
	              main_core.Dom.addClass(_this4.getConditionValueLayout(), 'landing-ui-rule-entry-condition-value-active');

	              _this4.getValueSettingsPopup().adjustPosition({
	                forceBindPosition: true
	              });
	            },
	            onClose: function onClose() {
	              main_core.Dom.removeClass(_this4.getConditionValueLayout(), 'landing-ui-rule-entry-condition-value-active');
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "getOperatorLabelLayout",
	    value: function getOperatorLabelLayout() {
	      var _this5 = this;

	      return this.cache.remember('operatorLayout', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-condition-value-operator\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this5.getOperatorLabel(_this5.options.condition.operator));
	      });
	    }
	  }, {
	    key: "getValueLabel",
	    value: function getValueLabel() {
	      var _this6 = this;

	      if (main_core.Type.isArray(this.options.condition.field.items)) {
	        var valueItem = this.options.condition.field.items.find(function (item) {
	          return String(item.value) === String(_this6.options.condition.value);
	        });

	        if (valueItem && main_core.Type.isString(valueItem.label)) {
	          return valueItem.label;
	        }
	      }

	      if (main_core.Type.isStringFilled(this.options.condition.value)) {
	        if (this.options.condition.value === 'Y') {
	          return landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES');
	        }

	        if (this.options.condition.value === 'N') {
	          return landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO');
	        }

	        return this.options.condition.value;
	      }

	      return landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_EMPTY');
	    }
	  }, {
	    key: "getValueLabelLayout",
	    value: function getValueLabelLayout() {
	      var _this7 = this;

	      return this.cache.remember('valueLabelLayout', function () {
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-rule-entry-condition-value-value-label\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</div>\t\t\t\n\t\t\t"])), _this7.onValueLabelClick.bind(_this7), _this7.getValueLabel());
	      });
	    }
	  }, {
	    key: "setValueLabelText",
	    value: function setValueLabelText(text) {
	      this.getValueLabelLayout().textContent = text;
	    }
	  }, {
	    key: "onValueLabelClick",
	    value: function onValueLabelClick(event) {
	      event.preventDefault();
	      var valueSettingsPopup = this.getValueSettingsPopup();

	      if (!valueSettingsPopup.isShown()) {
	        valueSettingsPopup.show();
	      } else {
	        valueSettingsPopup.close();
	      }
	    }
	  }, {
	    key: "getConditionValueLayout",
	    value: function getConditionValueLayout() {
	      var _this8 = this;

	      return this.cache.remember('conditionValueLayout', function () {
	        // const removeButton = new IconButton({
	        // 	type: IconButton.Types.remove,
	        // 	iconSize: '9px',
	        // 	style: {
	        // 		width: '19px',
	        // 		marginLeft: 'auto',
	        // 	},
	        // 	onClick: () => {
	        // 		Dom.remove(this.getLayout());
	        // 		this.emit('onRemove');
	        // 	},
	        // });
	        return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-condition-value\">\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-condition-value-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this8.getOperatorLabelLayout(), _this8.getValueLabelLayout());
	      });
	    }
	  }, {
	    key: "getConditionContainer",
	    value: function getConditionContainer() {
	      var _this9 = this;

	      return this.cache.remember('conditionContainer', function () {
	        return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-condition\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this9.getConditionValueLayout());
	      });
	    }
	  }, {
	    key: "getFieldsListMenu",
	    value: function getFieldsListMenu() {
	      var _this10 = this;

	      return this.cache.remember('fieldsListMenu', function () {
	        return new window.top.BX.Main.Menu({
	          bindElement: _this10.getAddFieldLink(),
	          maxHeight: 205,
	          items: _this10.options.fieldsList.filter(function (field) {
	            return field.type !== 'page' && field.type !== 'layout' && field.id !== _this10.options.condition.field.id;
	          }).map(function (item) {
	            return {
	              id: item.id,
	              text: item.label,
	              onclick: _this10.onAddExpressionField.bind(_this10, item)
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "onExpressionItemRemove",
	    value: function onExpressionItemRemove(event) {
	      var target = event.getTarget();
	      main_core.Dom.remove(target.getLayout());

	      if (this.getExpressionContainer().children.length === 1) {
	        main_core.Dom.removeClass(this.getLayout(), 'landing-ui-rule-entry-with-expression');
	      }

	      this.state.expression = this.state.expression.filter(function (entry) {
	        return entry.field !== target.options.id;
	      });
	      this.emit('onChange');
	    }
	  }, {
	    key: "onExpressionFieldChange",
	    value: function onExpressionFieldChange(event) {
	      var target = event.getTarget();
	      var expressionEntry = this.state.expression.find(function (currentEntry) {
	        return currentEntry.field === target.options.id;
	      });

	      if (expressionEntry) {
	        expressionEntry.action = target.getActionsDropdown().getValue();
	      }

	      this.emit('onChange');
	    }
	  }, {
	    key: "addExpressionItem",
	    value: function addExpressionItem(options) {
	      var preparedOptions = _objectSpread$1({
	        preventEvent: false,
	        action: 'show'
	      }, options);

	      var fieldElement = new FieldElement({
	        id: preparedOptions.id,
	        title: preparedOptions.label,
	        removable: true,
	        color: FieldElement.Colors.green,
	        actionsLabel: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_LABEL'),
	        actionsList: [{
	          name: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_SHOW_LABEL'),
	          value: 'show'
	        }, {
	          name: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_ACTION_HIDE_LABEL'),
	          value: 'hide'
	        }],
	        actionsValue: preparedOptions.action,
	        onRemove: this.onExpressionItemRemove.bind(this),
	        onChange: this.onExpressionFieldChange.bind(this)
	      });
	      main_core.Dom.insertBefore(fieldElement.getLayout(), this.getAddFieldLink());
	      this.state.expression.push({
	        field: preparedOptions.id,
	        action: 'show'
	      });
	      this.state.expression = this.state.expression.reduce(function (acc, entry) {
	        if (!acc.find(function (accEntry) {
	          return accEntry.field === entry.field;
	        })) {
	          acc.push(entry);
	        }

	        return acc;
	      }, []);
	      this.adjustExpressionItems();
	      main_core.Dom.addClass(this.getLayout(), 'landing-ui-rule-entry-with-expression');

	      if (!preparedOptions.preventEvent) {
	        this.emit('onChange');
	      }
	    }
	  }, {
	    key: "onAddExpressionField",
	    value: function onAddExpressionField(item) {
	      this.addExpressionItem(item);
	      this.getFieldsListMenu().close();
	    }
	  }, {
	    key: "adjustExpressionItems",
	    value: function adjustExpressionItems() {
	      babelHelpers.toConsumableArray(this.getExpressionContainer().children).reverse().forEach(function (element, index) {
	        if (!main_core.Dom.hasClass(element, 'landing-ui-rule-entry-expression-link')) {
	          main_core.Dom.style(element, 'z-index', index + 2);
	        }
	      });
	    }
	  }, {
	    key: "onAddFieldLinkClick",
	    value: function onAddFieldLinkClick(event) {
	      event.preventDefault();
	      var menu = this.getFieldsListMenu();
	      var expressionItems = this.state.expression;
	      menu.getMenuItems().forEach(function (item) {
	        var isUsed = expressionItems.some(function (expressionItem) {
	          return expressionItem.field === item.getId();
	        });

	        if (isUsed) {
	          main_core.Dom.addClass(item.getLayout().item, 'landing-ui-disabled');
	        } else {
	          main_core.Dom.removeClass(item.getLayout().item, 'landing-ui-disabled');
	        }
	      });
	      this.getFieldsListMenu().show();
	    }
	  }, {
	    key: "getAddFieldLink",
	    value: function getAddFieldLink() {
	      var _this11 = this;

	      return this.cache.remember('addFieldLink', function () {
	        return main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-expression-link\">\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass=\"landing-ui-rule-entry-expression-link-text\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-expression-link-sep\"></div>\n\t\t\t\t</div>\n\t\t\t"])), _this11.onAddFieldLinkClick.bind(_this11), landing_loc.Loc.getMessage('LANDING_RULE_FIELD_EXPRESSION_ADD_FIELD_LINK_LABEL'));
	      });
	    }
	  }, {
	    key: "getExpressionContainer",
	    value: function getExpressionContainer() {
	      var _this12 = this;

	      return this.cache.remember('expressionContainer', function () {
	        return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-expression\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this12.getAddFieldLink());
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this13 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this13.getConditionContainer(), _this13.getExpressionContainer());
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.state;
	    }
	  }, {
	    key: "onDragEnd",
	    value: function onDragEnd() {
	      this.adjustExpressionItems();
	      this.emit('onChange');
	    }
	  }]);
	  return RuleEntry;
	}(main_core_events.EventEmitter);

	var _templateObject$2, _templateObject2$2, _templateObject3$2;

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var FieldRules = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FieldRules, _EventEmitter);

	  function FieldRules() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, FieldRules);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldRules).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.RuleField.FieldRow');

	    _this.options = _objectSpread$2({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.entries = [];
	    _this.onEntryChange = _this.onEntryChange.bind(babelHelpers.assertThisInitialized(_this));

	    _this.addRule(_this.options);

	    return _this;
	  }

	  babelHelpers.createClass(FieldRules, [{
	    key: "addRule",
	    value: function addRule(ruleOptions) {
	      var entry = new RuleEntry(_objectSpread$2(_objectSpread$2({}, ruleOptions), {}, {
	        fieldsList: this.options.fields,
	        dictionary: this.options.dictionary
	      }));
	      this.entries.push(entry);
	      entry.subscribe('onChange', this.onEntryChange);
	      main_core.Dom.append(entry.getLayout(), this.getListContainer());
	      this.emit('onChange');
	    }
	  }, {
	    key: "onEntryChange",
	    value: function onEntryChange() {
	      this.emit('onChange');
	    }
	  }, {
	    key: "getConditionFieldLayout",
	    value: function getConditionFieldLayout() {
	      var _this2 = this;

	      return this.cache.remember('conditionField', function () {
	        var fieldElement = new FieldElement({
	          title: _this2.options.condition.field.label,
	          removable: true,
	          onRemove: _this2.onConditionFieldRemoveClick.bind(_this2)
	        });
	        return fieldElement.getLayout();
	      });
	    }
	  }, {
	    key: "onConditionFieldRemoveClick",
	    value: function onConditionFieldRemoveClick(event) {
	      event.preventDefault();
	      main_core.Dom.remove(this.getLayout());
	      this.entries = [];
	      this.emit('onChange');
	    }
	  }, {
	    key: "getFieldContainer",
	    value: function getFieldContainer() {
	      var _this3 = this;

	      return this.cache.remember('fieldContainer', function () {
	        return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-field-row-field-container\">\n\t\t\t\t\t<div class=\"landing-ui-field-rule-field-row-field-container-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-field-rule-field-row-field-container-action-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_TITLE'), _this3.getConditionFieldLayout(), landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_FIELD_SHOW_ACTION_TITLE'));
	      });
	    }
	  }, {
	    key: "getListContainer",
	    value: function getListContainer() {
	      return this.cache.remember('listContainer', function () {
	        return main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-field-row-list\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this4 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-field-row\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this4.getFieldContainer(), _this4.getListContainer());
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.entries.map(function (entry) {
	        return entry.getValue();
	      });
	    }
	  }]);
	  return FieldRules;
	}(main_core_events.EventEmitter);

	var _templateObject$3, _templateObject2$3, _templateObject3$3, _templateObject4$1;

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var RuleField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(RuleField, _BaseField);

	  function RuleField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RuleField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RuleField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.RuleField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.setLayoutClass('landing-ui-field-rule');

	    _this.onRemoveClick = _this.onRemoveClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onRowChange = _this.onRowChange.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Dom.clean(_this.layout);
	    main_core.Dom.append(_this.getHeaderLayout(), _this.getLayout());
	    main_core.Dom.append(_this.getBodyLayout(), _this.getLayout());
	    main_core.Dom.append(_this.getFooterLayout(), _this.getLayout());

	    _this.setRuleType(_this.options.type);

	    _this.rows = [];

	    _this.options.rules.forEach(function (rule) {
	      _this.addRule(rule);
	    });

	    if (_this.hasExpression()) {
	      _this.hideExpressionsLabel();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RuleField, [{
	    key: "hideExpressionsLabel",
	    value: function hideExpressionsLabel() {
	      main_core.Dom.hide(this.rows[0].getFieldContainer().querySelector('.landing-ui-field-rule-field-row-field-container-action-title'));
	    }
	  }, {
	    key: "hasExpression",
	    value: function hasExpression() {
	      return this.options.rules.some(function (rule) {
	        return rule.expression.length > 0;
	      });
	    }
	  }, {
	    key: "setRuleType",
	    value: function setRuleType(type) {
	      this.cache.set('ruleType', type);
	    }
	  }, {
	    key: "getRuleType",
	    value: function getRuleType() {
	      return this.cache.get('ruleType');
	    }
	  }, {
	    key: "addRule",
	    value: function addRule(fieldRules) {
	      var row = new FieldRules(_objectSpread$3(_objectSpread$3({}, fieldRules), {}, {
	        fields: this.options.fields,
	        dictionary: this.options.dictionary
	      }));
	      this.rows.push(row);
	      row.subscribe('onChange', this.onRowChange);
	      main_core.Dom.append(row.getLayout(), this.getBodyLayout());
	      this.emit('onChange');
	    }
	  }, {
	    key: "onRowChange",
	    value: function onRowChange(event) {
	      this.emit('onChange');
	      var hideLabel = event.getTarget().getValue().some(function (rule) {
	        return rule.expression.length > 0;
	      });
	      var label = this.rows[0].getFieldContainer().querySelector('.landing-ui-field-rule-field-row-field-container-action-title');

	      if (hideLabel) {
	        main_core.Dom.hide(label);
	      } else {
	        main_core.Dom.show(label);
	      }
	    }
	  }, {
	    key: "getHeaderTitleLayout",
	    value: function getHeaderTitleLayout() {
	      return this.cache.remember('headerTitleLayout', function () {
	        return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-header-title\">", "</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_1'));
	      });
	    }
	  }, {
	    key: "getRemoveButtonLayout",
	    value: function getRemoveButtonLayout() {
	      var _this2 = this;

	      return this.cache.remember('removeButtonLayout', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.remove,
	          onClick: _this2.onRemoveClick,
	          title: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_REMOVE_BUTTON_TITLE'),
	          style: {
	            marginLeft: 'auto'
	          }
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "getHeaderLayout",
	    value: function getHeaderLayout() {
	      var _this3 = this;

	      return this.cache.remember('headerLayout', function () {
	        return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-header\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.getHeaderTitleLayout(), _this3.getRemoveButtonLayout());
	      });
	    }
	  }, {
	    key: "getBodyLayout",
	    value: function getBodyLayout() {
	      return this.cache.remember('bodyLayout', function () {
	        return main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-body\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getFooterLayout",
	    value: function getFooterLayout() {
	      var _this4 = this;

	      return this.cache.remember('footerLayout', function () {
	        return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-rule-footer\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this4.getFooterActionPanel().getLayout());
	      });
	    }
	  }, {
	    key: "onRemoveClick",
	    value: function onRemoveClick(event) {
	      event.preventDefault();
	      main_core.Dom.remove(this.getLayout());
	      this.emit('onRemove');
	      this.emit('onChange');
	    }
	  }, {
	    key: "getFooterActionPanel",
	    value: function getFooterActionPanel() {
	      var _this5 = this;

	      return this.cache.remember('footerActionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            id: 'selectField',
	            text: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_EXPRESSION_ADD_FIELD_LINK_LABEL'),
	            onClick: _this5.onAddFieldRulesLinkClick.bind(_this5)
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getFieldsListMenu",
	    value: function getFieldsListMenu() {
	      var _this6 = this;

	      return this.cache.remember('fieldsMenu', function () {
	        return new window.top.BX.Main.Menu({
	          bindElement: _this6.getFooterActionPanel().getLeftContainer().firstElementChild,
	          maxHeight: 205,
	          items: _this6.options.fields.map(function (field) {
	            return {
	              id: field.id,
	              text: field.label,
	              onclick: function onclick() {
	                _this6.onAddFieldRulesMenuItemClick(field);
	              }
	            };
	          }),
	          autoHide: true
	        });
	      });
	    }
	  }, {
	    key: "onAddFieldRulesLinkClick",
	    value: function onAddFieldRulesLinkClick() {
	      this.getFieldsListMenu().show();
	    }
	  }, {
	    key: "onAddFieldRulesMenuItemClick",
	    value: function onAddFieldRulesMenuItemClick(field) {
	      this.addRule({
	        condition: {
	          field: field,
	          value: '',
	          operator: '='
	        },
	        expression: []
	      });
	      this.getFieldsListMenu().close();
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.rows.map(function (row) {
	        return row.getValue();
	      });
	    }
	  }]);
	  return RuleField;
	}(landing_ui_field_basefield.BaseField);

	exports.RuleField = RuleField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX.Landing.UI.Component,BX,BX.Event,BX.Landing.UI.Component,BX.Landing.UI.Component,BX.Landing,BX.UI.DragAndDrop,BX.Landing,BX.Landing.UI.Field,BX.Main));
//# sourceMappingURL=rulefield.bundle.js.map
