this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
this.BX.Landing.Ui.Panel = this.BX.Landing.Ui.Panel || {};
this.BX.Landing.Ui.Panel.Formsettingspanel = this.BX.Landing.Ui.Panel.Formsettingspanel || {};
(function (exports,landing_ui_panel_basepresetpanel,landing_ui_card_headercard,landing_ui_field_radiobuttonfield,landing_ui_form_formsettingsform,landing_ui_field_basefield,landing_ui_component_iconbutton,main_popup,landing_pageobject,landing_ui_field_textfield,main_core_events,main_core,landing_ui_component_internal,landing_ui_component_actionpanel,landing_loc) {
	'use strict';

	var RuleType = function RuleType() {
	  babelHelpers.classCallCheck(this, RuleType);
	};

	babelHelpers.defineProperty(RuleType, "TYPE_0", 0);
	babelHelpers.defineProperty(RuleType, "TYPE_1", 1);
	babelHelpers.defineProperty(RuleType, "TYPE_2", 2);

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
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-element-text-title\">", "</div>"])), main_core.Text.encode(_this4.options.title));
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
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-field-element-", "\"\n\t\t\t\t\tdata-field-id=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-field-element-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this6.options.color, main_core.Text.encode(_this6.options.id), _this6.options.draggable ? _this6.getDragButtonLayout() : '', _this6.options.actionsLabel ? _this6.getActionsLayout() : '', _this6.getTitleLayout(), _this6.options.removable ? _this6.getRemoveButtonLayout() : '');
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

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4, _templateObject5;

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var FieldValueElement = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FieldValueElement, _EventEmitter);

	  function FieldValueElement(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldValueElement);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldValueElement).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.ValueElement');

	    _this.options = _objectSpread$1({}, options);
	    _this.state = _objectSpread$1({}, _this.options.data);
	    return _this;
	  }

	  babelHelpers.createClass(FieldValueElement, [{
	    key: "getOperatorLabelLayout",
	    value: function getOperatorLabelLayout() {
	      var _this2 = this;

	      return this.cache.remember('operatorLabelLayout', function () {
	        var text = _this2.getOperatorLabelText(_this2.options.data.operation);

	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-rule-value-operator-label\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>", "</div>\n\t\t\t"])), _this2.onOperatorLabelClick.bind(_this2), text);
	      });
	    }
	  }, {
	    key: "onOperatorLabelClick",
	    value: function onOperatorLabelClick(event) {
	      event.preventDefault();
	      this.getOperatorSettingsPopup().show();
	    }
	  }, {
	    key: "getTargetContainer",
	    value: function getTargetContainer() {
	      var _this3 = this;

	      return this.cache.remember('targetContainer', function () {
	        return _this3.getLayout().closest('.landing-ui-panel-content-body-content') || _this3.getLayout();
	      });
	    }
	  }, {
	    key: "getOperatorSettingsPopup",
	    value: function getOperatorSettingsPopup() {
	      var _this4 = this;

	      return this.cache.remember('operatorSettingsPopup', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.Main.Popup({
	          bindElement: _this4.getLayout(),
	          targetContainer: _this4.getTargetContainer(),
	          content: _this4.getOperatorField().getLayout(),
	          autoHide: true,
	          minWidth: 160,
	          offsetLeft: 20,
	          offsetTop: 3,
	          bindOptions: {
	            position: 'bottom'
	          }
	        });
	      });
	    }
	  }, {
	    key: "getValueLabelLayout",
	    value: function getValueLabelLayout() {
	      var _this5 = this;

	      return this.cache.remember('valueLabelLayout', function () {
	        var text = _this5.getValueLabelText(_this5.options.data.value);

	        var layout = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-rule-value-value-label\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t<span class=\"landing-ui-rule-value-value-label-inner\">", "</span>\n\t\t\t\t</div>\n\t\t\t"])), _this5.onValueLabelClick.bind(_this5), main_core.Text.encode(text));

	        if (_this5.options.data.operation === 'any' || _this5.options.data.operation === 'empty') {
	          main_core.Dom.hide(layout);
	        }

	        return layout;
	      });
	    }
	  }, {
	    key: "setValueLabelText",
	    value: function setValueLabelText(text) {
	      this.getValueLabelLayout().firstElementChild.textContent = text;
	    }
	  }, {
	    key: "onValueLabelClick",
	    value: function onValueLabelClick(event) {
	      event.preventDefault();
	      this.getValueSettingsPopup().show();
	    }
	  }, {
	    key: "getValueSettingsPopup",
	    value: function getValueSettingsPopup() {
	      var _this6 = this;

	      return this.cache.remember('valueSettingsPopup', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var popupContent = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"value-settings-popup\"></div>"])));
	        var random = main_core.Text.getRandom();

	        var targetField = _this6.getTargetField();

	        if (targetField.type === 'list' || targetField.type === 'product' || targetField.type === 'checkbox' || targetField.type === 'radio' || targetField.type === 'bool') {
	          var valueItems = function () {
	            if (targetField.type === 'bool') {
	              return [{
	                label: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_YES'),
	                value: 'Y'
	              }, {
	                label: landing_loc.Loc.getMessage('LANDING_RULE_FIELD_CONDITION_VALUE_NO'),
	                value: 'N'
	              }];
	            }

	            return targetField.items;
	          }();

	          valueItems.forEach(function (item) {
	            var checked = String(targetField.value) === String(item.value);
	            main_core.Dom.append(main_core.Dom.append(_this6.renderValueRadioButton(_objectSpread$1(_objectSpread$1({}, item), {}, {
	              id: random,
	              checked: checked
	            })), popupContent), popupContent);
	          });
	        } else {
	          var value = function () {
	            if (main_core.Type.isStringFilled(_this6.options.data.value)) {
	              return _this6.getValueLabelText(_this6.options.data.value);
	            }

	            return '';
	          }();

	          var inputField = new landing_ui_field_textfield.TextField({
	            textOnly: true,
	            onValueChange: function onValueChange() {
	              var conditionValue = inputField.getValue() || landing_loc.Loc.getMessage('LANDING_RULE_CONDITION_VALUE_EMPTY');

	              _this6.setValueLabelText(conditionValue);

	              _this6.state.value = inputField.getValue();

	              _this6.emit('onChange');
	            },
	            content: value
	          });
	          main_core.Dom.append(inputField.getLayout(), popupContent);
	        }

	        return new rootWindow.BX.Main.Popup({
	          bindElement: _this6.getLayout(),
	          targetContainer: _this6.getTargetContainer(),
	          content: popupContent,
	          width: 228,
	          autoHide: true,
	          maxHeight: 200,
	          offsetLeft: 20,
	          offsetTop: 3,
	          events: {
	            onShow: function onShow() {
	              main_core.Dom.addClass(_this6.getLayout(), 'landing-ui-rule-value-active');
	            },
	            onClose: function onClose() {
	              main_core.Dom.removeClass(_this6.getLayout(), 'landing-ui-rule-value-active');
	            }
	          }
	        });
	      });
	    }
	  }, {
	    key: "renderValueRadioButton",
	    value: function renderValueRadioButton(_ref) {
	      var _this7 = this;

	      var label = _ref.label,
	          value = _ref.value,
	          id = _ref.id,
	          checked = _ref.checked;

	      var onChange = function onChange() {
	        _this7.setValueLabelText(label);

	        _this7.state.value = value;

	        _this7.emit('onChange');
	      };

	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"value-settings-item value-settings-item-value\">\n\t\t\t\t<input\n\t\t\t\t\ttype=\"radio\"\n\t\t\t\t\tid=\"value_", "_", "\"\n\t\t\t\t\tname=\"value_", "_", "\"\n\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t\t", "\n\t\t\t\t>\n\t\t\t\t<label for=\"value_", "_", "\">", "</label>\n\t\t\t</div>\n\t\t"])), id, value, id, this.options.data.target, onChange, checked ? 'checked' : '', id, value, main_core.Text.encode(label));
	    }
	  }, {
	    key: "getOperatorField",
	    value: function getOperatorField() {
	      var _this8 = this;

	      return this.cache.remember('operatorField', function () {
	        var condition = _this8.options.dictionary.deps.condition;

	        var targetField = _this8.getTargetField();

	        return new BX.Landing.UI.Field.Radio({
	          selector: 'operation',
	          value: [_this8.state.operation],
	          items: condition.operations.filter(function (item) {
	            return (!main_core.Type.isArrayFilled(item.fieldTypes) || item.fieldTypes.includes(targetField.type)) && (!main_core.Type.isArrayFilled(item.excludeFieldTypes) || main_core.Type.isArrayFilled(item.excludeFieldTypes) && !item.excludeFieldTypes.includes(targetField.type));
	          }).map(function (item) {
	            return {
	              name: item.name,
	              value: item.id
	            };
	          }),
	          onChange: _this8.onOperationChange.bind(_this8)
	        });
	      });
	    }
	  }, {
	    key: "setOperationLabelText",
	    value: function setOperationLabelText(text) {
	      this.getOperatorLabelLayout().textContent = text;
	    }
	  }, {
	    key: "onOperationChange",
	    value: function onOperationChange() {
	      var operatorField = this.getOperatorField();

	      var _operatorField$getVal = operatorField.getValue(),
	          _operatorField$getVal2 = babelHelpers.slicedToArray(_operatorField$getVal, 1),
	          value = _operatorField$getVal2[0];

	      if (value === 'empty' || value === 'any') {
	        main_core.Dom.hide(this.getValueLabelLayout());
	      } else {
	        main_core.Dom.show(this.getValueLabelLayout());
	      }

	      this.setOperationLabelText(this.getOperatorLabelText(value));
	      this.state.operation = value;
	      this.emit('onChange');
	    }
	  }, {
	    key: "getRemoveButton",
	    value: function getRemoveButton() {
	      var _this9 = this;

	      return this.cache.remember('removeButton', function () {
	        return new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.remove,
	          iconSize: '9px',
	          style: {
	            width: '19px',
	            marginLeft: 'auto'
	          },
	          onClick: function onClick() {
	            _this9.emit('onRemove');

	            _this9.emit('onChange');
	          }
	        });
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this10 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-rule-value\"\n\t\t\t\t\tdata-target=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t<div class=\"landing-ui-rule-value-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-rule-value-actions\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-rule-decoration\">\n\t\t\t\t\t\t<div class=\"landing-ui-rule-decoration-v-line\"></div>\n\t\t\t\t\t\t<div class=\"landing-ui-rule-decoration-h-line\"></div>\n\t\t\t\t\t\t<div class=\"landing-ui-rule-decoration-arrow\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Text.encode(_this10.options.data.target), _this10.getOperatorLabelLayout(), _this10.getValueLabelLayout(), _this10.options.removable ? _this10.getRemoveButton().getLayout() : '');
	      });
	    }
	  }, {
	    key: "getOperatorLabelText",
	    value: function getOperatorLabelText(operatorValue) {
	      return this.options.dictionary.deps.condition.operations.reduce(function (acc, item) {
	        if (item.id === operatorValue) {
	          return item.name;
	        }

	        return acc;
	      }, this.options.dictionary.deps.condition.operations[0].name);
	    }
	  }, {
	    key: "getTargetField",
	    value: function getTargetField() {
	      var _this11 = this;

	      return this.cache.remember('targetField', function () {
	        return _this11.options.fields.find(function (field) {
	          return String(field.id) === String(_this11.options.data.target);
	        });
	      });
	    }
	  }, {
	    key: "getValueLabelText",
	    value: function getValueLabelText(value) {
	      var targetField = this.getTargetField();

	      if (main_core.Type.isPlainObject(targetField)) {
	        if (main_core.Type.isArrayFilled(targetField.items)) {
	          var item = targetField.items.find(function (currentItem) {
	            return String(currentItem.value) === String(value);
	          });

	          if (main_core.Type.isPlainObject(item)) {
	            return item.label;
	          }
	        }

	        if (main_core.Type.isStringFilled(value)) {
	          if (value === 'Y') {
	            return landing_loc.Loc.getMessage('LANDING_RULE_CONDITION_VALUE_YES');
	          }

	          if (value === 'N') {
	            return landing_loc.Loc.getMessage('LANDING_RULE_CONDITION_VALUE_NO');
	          }

	          return value;
	        }
	      }

	      return landing_loc.Loc.getMessage('LANDING_RULE_CONDITION_VALUE_EMPTY');
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return _objectSpread$1({}, this.state);
	    }
	  }]);
	  return FieldValueElement;
	}(main_core_events.EventEmitter);

	var _templateObject$2;

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var TypeSeparator = /*#__PURE__*/function () {
	  function TypeSeparator(options) {
	    babelHelpers.classCallCheck(this, TypeSeparator);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.options = _objectSpread$2({}, options);
	  }

	  babelHelpers.createClass(TypeSeparator, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-type-separator\">\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-type-separator-inner\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this.getSeparatorLabel());
	      });
	    }
	  }, {
	    key: "getSeparatorLabel",
	    value: function getSeparatorLabel() {
	      if (String(this.options.typeId) === String(2)) {
	        return landing_loc.Loc.getMessage('LANDING_RULE_TYPE_SEPARATOR_TYPE_2');
	      }

	      return landing_loc.Loc.getMessage('LANDING_RULE_TYPE_SEPARATOR_TYPE_1');
	    }
	  }]);
	  return TypeSeparator;
	}();

	var _templateObject$3, _templateObject2$2, _templateObject3$2, _templateObject4$1, _templateObject5$1;

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var RuleEntry = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(RuleEntry, _EventEmitter);

	  function RuleEntry(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RuleEntry);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RuleEntry).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "conditions", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "expressions", []);

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.RuleEntry');

	    _this.options = _objectSpread$3({
	      enableHeader: true,
	      expressions: []
	    }, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onConditionFieldValueRemove = _this.onConditionFieldValueRemove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onConditionFieldRemove = _this.onConditionFieldRemove.bind(babelHelpers.assertThisInitialized(_this));

	    if (main_core.Type.isArrayFilled(_this.options.conditions)) {
	      _this.options.conditions.forEach(function (item) {
	        _this.addCondition(item);
	      });

	      _this.options.expressions.forEach(function (item) {
	        _this.addExpression(item);
	      });
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RuleEntry, [{
	    key: "getConditionsLayout",
	    value: function getConditionsLayout() {
	      return this.cache.remember('conditionsLayout', function () {
	        return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-conditions\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getExpressionsLayout",
	    value: function getExpressionsLayout() {
	      var _this2 = this;

	      return this.cache.remember('expressionsLayout', function () {
	        return main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-expressions\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.getAddExpresionFieldLinkLayout());
	      });
	    }
	  }, {
	    key: "getHeaderLayout",
	    value: function getHeaderLayout() {
	      return this.cache.remember('headerLayout', function () {
	        return main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-header\">", "</div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_RULE_ENTRY_HEADER'));
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this3 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-body\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this3.options.enableHeader ? _this3.getHeaderLayout() : '', _this3.getConditionsLayout(), _this3.getExpressionsLayout());
	      });
	    }
	  }, {
	    key: "onConditionFieldRemove",
	    value: function onConditionFieldRemove(event) {
	      var target = event.getTarget();
	      var targetLayout = target.getLayout();
	      this.conditions = this.conditions.filter(function (item) {
	        return item !== target;
	      });
	      var nextNode = targetLayout.nextElementSibling;

	      while (main_core.Type.isDomNode(nextNode) && !nextNode.matches('[class*="landing-ui-field-element"]')) {
	        this.conditions = this.conditions.filter(function (item) {
	          return item.getLayout() !== nextNode;
	        });
	        main_core.Dom.remove(nextNode);
	        nextNode = targetLayout.nextElementSibling;
	      }

	      if (!main_core.Type.isDomNode(nextNode)) {
	        var prevNode = targetLayout.previousElementSibling;

	        if (main_core.Type.isDomNode(prevNode) && main_core.Dom.hasClass(prevNode, 'landing-ui-rule-entry-type-separator')) {
	          main_core.Dom.remove(prevNode);
	        }
	      }

	      main_core.Dom.remove(targetLayout);
	      this.emit('onChange');
	    }
	  }, {
	    key: "onConditionFieldValueRemove",
	    value: function onConditionFieldValueRemove(event) {
	      var target = event.getTarget();
	      var targetLayout = target.getLayout();
	      this.conditions = this.conditions.filter(function (item) {
	        return item !== target;
	      });

	      if (main_core.Dom.hasClass(targetLayout.nextElementSibling, 'landing-ui-rule-entry-type-separator')) {
	        main_core.Dom.remove(targetLayout.nextElementSibling);
	      } else if (main_core.Dom.hasClass(targetLayout.previousElementSibling, 'landing-ui-rule-entry-type-separator')) {
	        main_core.Dom.remove(targetLayout.previousElementSibling);
	      }

	      main_core.Dom.remove(targetLayout);
	    }
	  }, {
	    key: "addCondition",
	    value: function addCondition(element) {
	      var _this4 = this;

	      if (!this.conditions.includes(element)) {
	        this.conditions.push(element);

	        if (element instanceof FieldValueElement) {
	          element.subscribe('onRemove', this.onConditionFieldValueRemove);
	          element.subscribe('onChange', function () {
	            return _this4.emit('onChange');
	          });
	          var conditionsNodes = babelHelpers.toConsumableArray(this.getConditionsLayout().childNodes);
	          var lastElement = conditionsNodes.reduce(function (acc, node) {
	            if (main_core.Dom.hasClass(node, 'landing-ui-rule-value') && String(main_core.Dom.attr(node, 'data-target')) === String(element.options.data.target) || node.matches('[class*="landing-ui-field-element"]') && String(main_core.Dom.attr(node, 'data-field-id')) === String(element.options.data.target)) {
	              return node;
	            }

	            return acc;
	          }, null);

	          if (main_core.Type.isDomNode(lastElement)) {
	            main_core.Dom.insertAfter(element.getLayout(), lastElement);

	            if (main_core.Dom.hasClass(lastElement, 'landing-ui-rule-value')) {
	              var separator = new TypeSeparator({
	                typeId: this.options.typeId
	              });
	              main_core.Dom.insertBefore(separator.getLayout(), element.getLayout());
	            }

	            return;
	          }
	        }

	        if (element instanceof FieldElement) {
	          element.subscribe('onRemove', this.onConditionFieldRemove);
	          element.subscribe('onChange', function () {
	            return _this4.emit('onChange');
	          });

	          if (babelHelpers.toConsumableArray(this.getConditionsLayout().childNodes).length > 0) {
	            var _separator = new TypeSeparator({
	              typeId: this.options.typeId
	            });

	            main_core.Dom.append(_separator.getLayout(), this.getConditionsLayout());
	          }
	        }

	        main_core.Dom.append(element.getLayout(), this.getConditionsLayout());
	        this.emit('onChange');
	      }
	    }
	  }, {
	    key: "getExpressionActionPanel",
	    value: function getExpressionActionPanel() {
	      var _this5 = this;

	      return this.cache.remember('expressionActionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            id: 'addField',
	            text: landing_loc.Loc.getMessage('LANDING_RULE_ENTRY_ADD_FIELD_LABEL'),
	            onClick: _this5.onAddExpressionFieldClick.bind(_this5)
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onAddExpressionFieldClick",
	    value: function onAddExpressionFieldClick(event) {
	      var _this6 = this;

	      event.preventDefault();
	      var menu = this.getFieldsListMenu();
	      menu.getMenuItems().forEach(function (item) {
	        var isUsed = _this6.expressions.some(function (expressionItem) {
	          return String(expressionItem.options.id) === String(item.getId());
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
	    key: "getExpressionAllowedFieldsList",
	    value: function getExpressionAllowedFieldsList() {
	      var _this7 = this;

	      var disallowedTypes = ['page', 'layout'];
	      return this.options.fields.filter(function (field) {
	        if (!disallowedTypes.includes(field.type)) {
	          return !_this7.conditions.find(function (condition) {
	            return main_core.Type.isPlainObject(condition.options) && (main_core.Type.isPlainObject(condition.options.data) && String(condition.options.data.target) === String(field.id) || String(condition.options.id) === String(field.id));
	          });
	        }

	        return true;
	      });
	    }
	  }, {
	    key: "getFieldsListMenu",
	    value: function getFieldsListMenu() {
	      var _this8 = this;

	      return this.cache.remember('fieldsListMenu', function () {
	        return new window.top.BX.Main.Menu({
	          bindElement: _this8.getExpressionActionPanel().getLayout(),
	          maxHeight: 205,
	          items: _this8.getExpressionAllowedFieldsList().map(function (item) {
	            return {
	              id: item.id,
	              text: item.label,
	              onclick: _this8.onAddExpressionField.bind(_this8, item)
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "getAddExpresionFieldLinkLayout",
	    value: function getAddExpresionFieldLinkLayout() {
	      var _this9 = this;

	      return this.cache.remember('addExpressionFieldLinkLayout', function () {
	        return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-entry-add-expression-field-link\">\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-add-expression-field-link-action-panel\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-rule-entry-add-expression-field-link-separator\"></div>\n\t\t\t\t</div>\n\t\t\t"])), _this9.getExpressionActionPanel().getLayout());
	      });
	    }
	  }, {
	    key: "onAddExpressionField",
	    value: function onAddExpressionField(field) {
	      var element = new FieldElement({
	        id: field.id,
	        title: field.label,
	        removable: true,
	        color: FieldElement.Colors.green,
	        actionsLabel: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
	        actionsList: [{
	          name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'),
	          value: 'show'
	        }, {
	          name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'),
	          value: 'hide'
	        }],
	        actionsValue: 'show'
	      });
	      this.addExpression(element);
	      this.getFieldsListMenu().close();
	      this.emit('onChange');
	    }
	  }, {
	    key: "onExpressionFieldRemove",
	    value: function onExpressionFieldRemove(event) {
	      var target = event.getTarget();
	      main_core.Dom.remove(target.getLayout());
	      this.expressions = this.expressions.filter(function (field) {
	        return String(field.options.id) !== String(target.options.id);
	      });
	      this.adjustExpressionFieldsZIndexes();
	      this.emit('onChange');
	    }
	  }, {
	    key: "onExpressionFieldChange",
	    value: function onExpressionFieldChange() {
	      this.emit('onChange');
	    }
	  }, {
	    key: "adjustExpressionFieldsZIndexes",
	    value: function adjustExpressionFieldsZIndexes() {
	      babelHelpers.toConsumableArray(this.getExpressionsLayout().children).reverse().forEach(function (node, index) {
	        if (node.matches('[class*="landing-ui-field-element"]')) {
	          main_core.Dom.style(node, 'z-index', index + 2);
	        }
	      });
	    }
	  }, {
	    key: "addExpression",
	    value: function addExpression(element) {
	      if (!this.expressions.includes(element)) {
	        this.expressions.push(element);
	        element.subscribe('onRemove', this.onExpressionFieldRemove.bind(this));
	        element.subscribe('onChange', this.onExpressionFieldChange.bind(this)); // @todo: refactoring

	        void this.getLayout();
	        main_core.Dom.insertBefore(element.getLayout(), this.getAddExpresionFieldLinkLayout());
	        this.adjustExpressionFieldsZIndexes();
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this10 = this;

	      return this.conditions.filter(function (item) {
	        return item instanceof FieldValueElement;
	      }).reduce(function (acc, conditionsItem) {
	        return [].concat(babelHelpers.toConsumableArray(acc), babelHelpers.toConsumableArray(_this10.expressions.map(function (expressionItem) {
	          return {
	            condition: _objectSpread$3(_objectSpread$3({}, conditionsItem.getValue()), {}, {
	              event: 'change'
	            }),
	            action: {
	              target: expressionItem.options.id,
	              type: expressionItem.getActionsDropdown().getValue()
	            }
	          };
	        })));
	      }, []);
	    }
	  }]);
	  return RuleEntry;
	}(main_core_events.EventEmitter);

	var _templateObject$4;

	var FieldActionPanel = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FieldActionPanel, _EventEmitter);

	  function FieldActionPanel(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldActionPanel);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldActionPanel).call(this, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());

	    _this.setEventNamespace('BX.Landing.UI.FormSettingsPanel.FieldRules.FieldActionPanel');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    if (main_core.Type.isPlainObject(options.style)) {
	      main_core.Dom.style(_this.getLayout(), options.style);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(FieldActionPanel, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-field-action-panel\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-rule-field-action-panel-decoration\">\n\t\t\t\t\t\t<div class=\"landing-ui-rule-field-action-panel-decoration-v-line\"></div>\n\t\t\t\t\t\t<div class=\"landing-ui-rule-field-action-panel-decoration-h-line\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), _this2.getActionPanel().getLayout());
	      });
	    }
	  }, {
	    key: "getActionPanel",
	    value: function getActionPanel() {
	      var _this3 = this;

	      return this.cache.remember('actionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            id: 'addCondition',
	            text: landing_loc.Loc.getMessage('LANDING_RULE_GROUP_ADD_FIELD_CONDITION'),
	            onClick: function onClick() {
	              _this3.emit('onAddCondition');
	            }
	          }]
	        });
	      });
	    }
	  }]);
	  return FieldActionPanel;
	}(main_core_events.EventEmitter);

	var _templateObject$5, _templateObject2$3, _templateObject3$3, _templateObject4$2;

	var RuleGroup = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(RuleGroup, _BaseField);

	  function RuleGroup(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, RuleGroup);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RuleGroup).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Content.FieldRules.RuleGroup');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.setLayoutClass('landing-ui-rule-group');

	    var layout = _this.getLayout();

	    main_core.Dom.clean(layout);
	    main_core.Dom.append(_this.getHeaderLayout(), layout);
	    main_core.Dom.append(_this.getBodyLayout(), layout);
	    main_core.Dom.append(_this.getFooterLayout(), layout);

	    if (main_core.Type.isArrayFilled(_this.options.data.list)) {
	      var filteredDataList = _this.options.data.list.filter(function (item) {
	        var conditionTarget = _this.getField(item.condition.target);

	        var actionTarget = _this.getField(item.action.target);

	        return conditionTarget && actionTarget;
	      });

	      if (_this.getTypeId() === RuleType.TYPE_0) {
	        var groupedList = filteredDataList.reduce(function (acc, item) {
	          var _item$condition = item.condition,
	              target = _item$condition.target,
	              operation = _item$condition.operation,
	              value = _item$condition.value;

	          if (!main_core.Type.isArray(acc["".concat(target).concat(operation).concat(value)])) {
	            acc["".concat(target).concat(operation).concat(value)] = [];
	          }

	          acc["".concat(target).concat(operation).concat(value)].push(item);
	          return acc;
	        }, {});
	        Object.values(groupedList).forEach(function (group, index) {
	          var _group = babelHelpers.slicedToArray(group, 1),
	              firstItem = _group[0];

	          if (main_core.Type.isPlainObject(firstItem)) {
	            var targetField = _this.getField(firstItem.condition.target);

	            var entry = new RuleEntry({
	              enableHeader: index === 0,
	              typeId: _this.getTypeId(),
	              fields: _this.options.fields,
	              onChange: function onChange() {
	                return _this.emit('onChange');
	              },
	              conditions: [new FieldElement({
	                dictionary: _this.options.dictionary,
	                fields: _this.options.fields,
	                id: targetField.id,
	                title: targetField.label,
	                color: FieldElement.Colors.blue,
	                onRemove: function onRemove() {
	                  _this.onConditionFieldRemove(entry);
	                }
	              }), new FieldValueElement({
	                dictionary: _this.options.dictionary,
	                fields: _this.options.fields,
	                removable: false,
	                data: group[0].condition
	              })],
	              expressions: group.map(function (groupItem) {
	                var targetField = _this.getField(groupItem.action.target);

	                return new FieldElement({
	                  id: targetField.id,
	                  title: targetField.label,
	                  removable: true,
	                  color: FieldElement.Colors.green,
	                  actionsLabel: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
	                  actionsList: [{
	                    name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'),
	                    value: 'show'
	                  }, {
	                    name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'),
	                    value: 'hide'
	                  }],
	                  actionsValue: groupItem.action.type
	                });
	              })
	            });

	            _this.addEntry(entry);
	          }
	        });
	      }

	      if (_this.getTypeId() === RuleType.TYPE_1 || _this.getTypeId() === RuleType.TYPE_2) {
	        var entry = new RuleEntry({
	          enableHeader: true,
	          typeId: _this.getTypeId(),
	          fields: _this.options.fields,
	          onChange: function onChange() {
	            return _this.emit('onChange');
	          }
	        });

	        var _groupedList = filteredDataList.reduce(function (acc, item) {
	          var target = item.condition.target;

	          if (!main_core.Type.isArray(acc[target])) {
	            acc[target] = [];
	          }

	          acc[target].push(item);
	          return acc;
	        }, {});

	        Object.values(_groupedList).forEach(function (group) {
	          var _group2 = babelHelpers.slicedToArray(group, 1),
	              firstItem = _group2[0];

	          if (main_core.Type.isPlainObject(firstItem)) {
	            var targetField = _this.getField(firstItem.condition.target);

	            var allowedMultipleConditions = _this.getTypeId() === RuleType.TYPE_2 && targetField.multiple || _this.getTypeId() === RuleType.TYPE_1;
	            entry.addCondition(new FieldElement({
	              dictionary: _this.options.dictionary,
	              fields: _this.options.fields,
	              id: targetField.id,
	              title: targetField.label,
	              color: FieldElement.Colors.blue,
	              onRemove: function onRemove() {
	                _this.onConditionFieldRemove(entry);
	              }
	            }));
	            var groupedConditions = group.reduce(function (acc, item) {
	              acc["".concat(item.condition.operation).concat(item.condition.value)] = item;
	              return acc;
	            }, {});
	            Object.values(groupedConditions).forEach(function (item) {
	              entry.addCondition(new FieldValueElement({
	                dictionary: _this.options.dictionary,
	                fields: _this.options.fields,
	                removable: allowedMultipleConditions,
	                data: item.condition
	              }));
	            });
	            entry.addCondition(new FieldActionPanel({
	              style: {
	                display: allowedMultipleConditions ? null : 'none'
	              },
	              onAddCondition: function onAddCondition() {
	                _this.onAddFieldCondition(new main_core_events.BaseEvent({
	                  data: {
	                    entry: entry,
	                    target: targetField.id
	                  }
	                }));
	              }
	            }));
	          }
	        });
	        var groupedExpressions = Object.values(filteredDataList).reduce(function (acc, item) {
	          var _item$action = item.action,
	              target = _item$action.target,
	              type = _item$action.type;
	          acc["".concat(target).concat(type)] = item;
	          return acc;
	        }, {});
	        Object.values(groupedExpressions).forEach(function (item) {
	          var targetField = _this.getField(item.action.target);

	          var element = new FieldElement({
	            id: targetField.id,
	            title: targetField.label,
	            removable: true,
	            color: FieldElement.Colors.green,
	            actionsLabel: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_LABEL'),
	            actionsList: [{
	              name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_SHOW_LABEL'),
	              value: 'show'
	            }, {
	              name: landing_loc.Loc.getMessage('LANDING_RULE_EXPRESSION_FIELD_ACTION_HIDE_LABEL'),
	              value: 'hide'
	            }],
	            actionsValue: item.action.type
	          });
	          entry.addExpression(element);
	        });

	        _this.addEntry(entry);
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(RuleGroup, [{
	    key: "getEntries",
	    value: function getEntries() {
	      return this.cache.remember('entries', function () {
	        return [];
	      });
	    }
	  }, {
	    key: "setEntries",
	    value: function setEntries(entries) {
	      this.cache.set('entries', entries);
	    }
	  }, {
	    key: "addEntry",
	    value: function addEntry(entry) {
	      var _this2 = this;

	      if (entry) {
	        var entries = this.getEntries();

	        if (!entries.includes(entry)) {
	          entry.subscribe('onChange', function () {
	            return _this2.emit('onChange');
	          });
	          entries.push(entry);
	          main_core.Dom.append(entry.getLayout(), this.getBodyLayout());
	          this.emit('onChange');
	        }
	      }
	    }
	  }, {
	    key: "getHeaderLayout",
	    value: function getHeaderLayout() {
	      var _this3 = this;

	      return this.cache.remember('headerLayout', function () {
	        return main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-group-header\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.getHeaderTitleLayout(), _this3.getRemoveButtonLayout());
	      });
	    }
	  }, {
	    key: "getHeaderTitleLayout",
	    value: function getHeaderTitleLayout() {
	      var _this4 = this;

	      return this.cache.remember('headerTitleLayout', function () {
	        var titleOfRuleType = landing_loc.Loc.getMessage("LANDING_FIELDS_RULES_TYPE_".concat(_this4.getTypeId() + 1));
	        return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-group-header-title\">", "</div>\n\t\t\t"])), titleOfRuleType);
	      });
	    }
	  }, {
	    key: "getRemoveButtonLayout",
	    value: function getRemoveButtonLayout() {
	      var _this5 = this;

	      return this.cache.remember('removeButtonLayout', function () {
	        var button = new landing_ui_component_iconbutton.IconButton({
	          type: landing_ui_component_iconbutton.IconButton.Types.remove,
	          onClick: _this5.onRemoveClick.bind(_this5),
	          title: landing_loc.Loc.getMessage('LANDING_RULE_GROUP_REMOVE_BUTTON_TITLE'),
	          style: {
	            marginLeft: 'auto'
	          }
	        });
	        return button.getLayout();
	      });
	    }
	  }, {
	    key: "onRemoveClick",
	    value: function onRemoveClick() {
	      main_core.Dom.remove(this.getLayout());
	      this.emit('onRemove');
	      this.emit('onChange');
	    }
	  }, {
	    key: "getBodyLayout",
	    value: function getBodyLayout() {
	      return this.cache.remember('bodyLayout', function () {
	        return main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-group-body\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getFooterLayout",
	    value: function getFooterLayout() {
	      var _this6 = this;

	      return this.cache.remember('footerLayout', function () {
	        return main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-rule-group-footer\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this6.getFooterActionPanel().getLayout());
	      });
	    }
	  }, {
	    key: "getFooterActionPanel",
	    value: function getFooterActionPanel() {
	      var _this7 = this;

	      return this.cache.remember('footerActionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            id: 'selectField',
	            text: landing_loc.Loc.getMessage('LANDING_RULE_ENTRY_ADD_FIELD_LABEL'),
	            onClick: _this7.onAddFieldClick.bind(_this7)
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onAddFieldClick",
	    value: function onAddFieldClick(event) {
	      var menu = this.getFieldsListMenu();
	      menu.getPopupWindow().setBindElement(event.currentTarget);
	      menu.show();
	    }
	  }, {
	    key: "getFieldsListMenu",
	    value: function getFieldsListMenu() {
	      var _this8 = this;

	      return this.cache.remember('fieldsMenu', function () {
	        return new window.top.BX.Main.Menu({
	          maxHeight: 205,
	          items: _this8.options.fields.map(function (field) {
	            return {
	              id: field.id,
	              text: field.label,
	              onclick: function onclick() {
	                _this8.onFieldsListMenuItemClick(field);

	                _this8.getFieldsListMenu().close();
	              }
	            };
	          }),
	          autoHide: true
	        });
	      });
	    }
	  }, {
	    key: "getDefaultValueState",
	    value: function getDefaultValueState(fieldId) {
	      var targetField = this.options.fields.find(function (field) {
	        return String(field.id) === String(fieldId);
	      });

	      if (targetField) {
	        var filteredOperations = this.options.dictionary.deps.condition.operations.filter(function (operation) {
	          return (!main_core.Type.isArrayFilled(operation.fieldTypes) || operation.fieldTypes.includes(targetField.type)) && (!main_core.Type.isArrayFilled(operation.excludeFieldTypes) || main_core.Type.isArrayFilled(operation.excludeFieldTypes) && !operation.excludeFieldTypes.includes(targetField.type));
	        });

	        if (main_core.Type.isArrayFilled(filteredOperations)) {
	          return filteredOperations[0].id;
	        }
	      }

	      return '=';
	    }
	  }, {
	    key: "onAddFieldCondition",
	    value: function onAddFieldCondition(event) {
	      var _event$getData = event.getData(),
	          target = _event$getData.target,
	          entry = _event$getData.entry;

	      entry.addCondition(new FieldValueElement({
	        dictionary: this.options.dictionary,
	        fields: this.options.fields,
	        removable: true,
	        data: {
	          target: target,
	          operation: this.getDefaultValueState(target),
	          value: null
	        }
	      }));
	    }
	  }, {
	    key: "onConditionFieldRemove",
	    value: function onConditionFieldRemove(entry) {
	      var fieldElements = entry.conditions.filter(function (item) {
	        return item instanceof FieldElement;
	      });

	      if (fieldElements.length === 1) {
	        var entries = this.getEntries().filter(function (item) {
	          return entry !== item;
	        });
	        this.setEntries(entries);
	        main_core.Dom.remove(entry.getLayout());
	      }
	    }
	  }, {
	    key: "onFieldsListMenuItemClick",
	    value: function onFieldsListMenuItemClick(field) {
	      var _this9 = this;

	      if (this.getTypeId() === RuleType.TYPE_0) {
	        var enableHeader = this.getEntries().length === 0;
	        var entry = new RuleEntry({
	          enableHeader: enableHeader,
	          typeId: this.getTypeId(),
	          fields: this.options.fields,
	          conditions: [new FieldElement({
	            dictionary: this.options.dictionary,
	            fields: this.options.fields,
	            id: field.id,
	            title: field.label,
	            color: FieldElement.Colors.blue,
	            onRemove: function onRemove() {
	              _this9.onConditionFieldRemove(entry);
	            }
	          }), new FieldValueElement({
	            dictionary: this.options.dictionary,
	            fields: this.options.fields,
	            removable: false,
	            data: {
	              target: field.id,
	              operation: this.getDefaultValueState(field.id),
	              value: null
	            }
	          })],
	          onChange: function onChange() {
	            return _this9.emit('onChange');
	          }
	        });
	        this.addEntry(entry);
	      }

	      if (this.getTypeId() === RuleType.TYPE_1 || this.getTypeId() === RuleType.TYPE_2) {
	        var allowedMultipleConditions = this.getTypeId() === RuleType.TYPE_2 && field.multiple || this.getTypeId() === RuleType.TYPE_1;
	        var items = [new FieldElement({
	          dictionary: this.options.dictionary,
	          fields: this.options.fields,
	          id: field.id,
	          title: field.label,
	          color: FieldElement.Colors.blue,
	          onRemove: function onRemove() {
	            _this9.onConditionFieldRemove(_this9.getEntries()[0]);
	          }
	        }), new FieldValueElement({
	          dictionary: this.options.dictionary,
	          fields: this.options.fields,
	          removable: allowedMultipleConditions,
	          data: {
	            target: field.id,
	            operation: this.getDefaultValueState(field.id),
	            value: null
	          }
	        })];

	        if (this.getTypeId() === RuleType.TYPE_1 || this.getTypeId() === RuleType.TYPE_2) {
	          items.push(new FieldActionPanel({
	            style: {
	              display: allowedMultipleConditions ? null : 'none'
	            },
	            onAddCondition: function onAddCondition() {
	              _this9.onAddFieldCondition(new main_core_events.BaseEvent({
	                data: {
	                  entry: _this9.getEntries()[0],
	                  target: field.id
	                }
	              }));
	            }
	          }));
	        }

	        var _this$getEntries = this.getEntries(),
	            _this$getEntries2 = babelHelpers.slicedToArray(_this$getEntries, 1),
	            _entry = _this$getEntries2[0];

	        if (_entry) {
	          items.forEach(function (item) {
	            _entry.addCondition(item);
	          });
	        } else {
	          var newEntry = new RuleEntry({
	            enableHeader: true,
	            typeId: this.getTypeId(),
	            fields: this.options.fields,
	            conditions: items,
	            onChange: function onChange() {
	              return _this9.emit('onChange');
	            }
	          });
	          this.addEntry(newEntry);
	        }
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      if (!main_core.Type.isNil(this.options.data.id)) {
	        return this.options.data.id;
	      }

	      return 0;
	    }
	  }, {
	    key: "getTypeId",
	    value: function getTypeId() {
	      return main_core.Text.toNumber(this.options.data.typeId);
	    }
	  }, {
	    key: "getLogic",
	    value: function getLogic() {
	      return this.getTypeId() === RuleType.TYPE_2 ? 'and' : 'or';
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var list = this.getEntries().reduce(function (acc, entry) {
	        return [].concat(babelHelpers.toConsumableArray(acc), babelHelpers.toConsumableArray(entry.getValue()));
	      }, []);
	      return {
	        id: this.getId(),
	        typeId: this.getTypeId(),
	        logic: this.getLogic(),
	        list: list
	      };
	    }
	  }, {
	    key: "getField",
	    value: function getField(fieldId) {
	      return this.options.fields.find(function (item) {
	        return String(item.id) === String(fieldId);
	      });
	    }
	  }]);
	  return RuleGroup;
	}(landing_ui_field_basefield.BaseField);

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var FieldsRules = /*#__PURE__*/function (_ContentWrapper) {
	  babelHelpers.inherits(FieldsRules, _ContentWrapper);

	  function FieldsRules(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldsRules);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldsRules).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsRulesContent');

	    _this.addItem(_this.getHeader());

	    if (!main_core.Type.isArrayFilled(_this.options.formOptions.data.dependencies)) {
	      _this.addItem(_this.getRuleTypeField());
	    } else {
	      _this.addItem(_this.getRulesForm());

	      _this.addItem(_this.getActionPanel());
	    }

	    return _this;
	  }

	  babelHelpers.createClass(FieldsRules, [{
	    key: "getHeader",
	    value: function getHeader() {
	      return this.cache.remember('headerCard', function () {
	        return new landing_ui_card_headercard.HeaderCard({
	          title: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TITLE')
	        });
	      });
	    }
	  }, {
	    key: "getRulesForm",
	    value: function getRulesForm() {
	      var _this2 = this;

	      return this.cache.remember('rulesForm', function () {
	        return new landing_ui_form_formsettingsform.FormSettingsForm({
	          selector: 'dependencies',
	          description: null,
	          fields: _this2.options.formOptions.data.dependencies.map(function (groupData) {
	            return new RuleGroup({
	              dictionary: _this2.options.dictionary,
	              fields: _this2.getFormFields(),
	              data: groupData,
	              onRemove: _this2.onRuleGroupRemove.bind(_this2)
	            });
	          })
	        });
	      });
	    }
	  }, {
	    key: "getActionPanel",
	    value: function getActionPanel() {
	      var _this3 = this;

	      return this.cache.remember('actionPanel', function () {
	        return new landing_ui_component_actionpanel.ActionPanel({
	          left: [{
	            text: main_core.Loc.getMessage('LANDING_FIELDS_ADD_NEW_RULE_LINK_LABEL'),
	            onClick: _this3.onAddRuleClick.bind(_this3)
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onAddRuleClick",
	    value: function onAddRuleClick() {
	      this.insertBefore(this.getRuleTypeField(), this.getActionPanel());
	      this.items.remove(this.getActionPanel());
	      main_core.Dom.remove(this.getActionPanel().getLayout());
	      this.getActionPanel().unsubscribe('onChange', this.onChange);
	    }
	  }, {
	    key: "getRuleTypeField",
	    value: function getRuleTypeField() {
	      var _this4 = this;

	      return this.cache.remember('ruleTypeField', function () {
	        return new landing_ui_field_radiobuttonfield.RadioButtonField({
	          selector: 'rules-type',
	          items: Object.entries(RuleType).map(function (_ref) {
	            var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	                value = _ref2[1];

	            return {
	              id: "ruleType".concat(value),
	              icon: "landing-ui-rules-type".concat(value + 1, "-icon"),
	              title: main_core.Loc.getMessage("LANDING_FIELDS_RULES_TYPE_".concat(value + 1)),
	              button: {
	                text: main_core.Loc.getMessage('LANDING_FIELDS_RULES_TYPE_BUTTON'),
	                onClick: _this4.onCreateRuleButtonClick.bind(_this4, {
	                  type: value
	                })
	              }
	            };
	          })
	        });
	      });
	    }
	  }, {
	    key: "getFormFields",
	    value: function getFormFields() {
	      var _this5 = this;

	      var disallowedTypes = function () {
	        if (!main_core.Type.isPlainObject(_this5.options.dictionary.deps.field) || !main_core.Type.isArrayFilled(_this5.options.dictionary.deps.field.disallowed)) {
	          return null;
	        }

	        return _this5.options.dictionary.deps.field.disallowed;
	      }();

	      return this.options.formOptions.data.fields.filter(function (field) {
	        return !main_core.Type.isArrayFilled(disallowedTypes) || !disallowedTypes.includes(field.type) && (!main_core.Type.isPlainObject(field.content) || disallowedTypes.includes(field.content.type));
	      });
	    }
	  }, {
	    key: "onCreateRuleButtonClick",
	    value: function onCreateRuleButtonClick(_ref3) {
	      var type = _ref3.type;
	      this.clear();
	      var header = this.getHeader();
	      header.setBottomMargin(false);
	      this.addItem(header);
	      var ruleForm = this.getRulesForm();
	      ruleForm.addField(new RuleGroup({
	        dictionary: this.options.dictionary,
	        fields: this.getFormFields(),
	        data: {
	          id: 0,
	          typeId: type,
	          list: [],
	          logic: type === RuleType.TYPE_2 ? 'and' : 'or'
	        },
	        onRemove: this.onRuleGroupRemove.bind(this)
	      }));
	      this.addItem(ruleForm);
	      this.addItem(this.getActionPanel());
	    }
	  }, {
	    key: "onRuleGroupRemove",
	    value: function onRuleGroupRemove(event) {
	      this.onChange(event);
	      this.getRulesForm().removeField(event.getTarget());
	      event.getTarget().unsubscribe('onChange', this.onChange);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', _objectSpread$4(_objectSpread$4({}, event.getData()), {}, {
	        skipPrepare: true
	      }));
	    }
	  }, {
	    key: "valueReducer",
	    value: function valueReducer(value) {
	      return {
	        dependencies: Object.values(value).filter(function (group) {
	          return main_core.Type.isArrayFilled(group.list);
	        })
	      };
	    }
	  }]);
	  return FieldsRules;
	}(landing_ui_panel_basepresetpanel.ContentWrapper);

	exports.default = FieldsRules;

}((this.BX.Landing.Ui.Panel.Formsettingspanel.Content = this.BX.Landing.Ui.Panel.Formsettingspanel.Content || {}),BX.Landing.UI.Panel,BX.Landing.UI.Card,BX.Landing.UI.Field,BX.Landing.UI.Form,BX.Landing.UI.Field,BX.Landing.UI.Component,BX.Main,BX.Landing,BX.Landing.UI.Field,BX.Event,BX,BX.Landing.UI.Component,BX.Landing.UI.Component,BX.Landing));
//# sourceMappingURL=fields-rules.bundle.js.map
