/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,bizproc_condition) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _table = /*#__PURE__*/new WeakMap();
	var _fields = /*#__PURE__*/new WeakMap();
	var _documentType = /*#__PURE__*/new WeakMap();
	var _index = /*#__PURE__*/new WeakMap();
	var _conditions = /*#__PURE__*/new WeakMap();
	var _conditionCountNode = /*#__PURE__*/new WeakMap();
	var _renderConditionTo = /*#__PURE__*/new WeakSet();
	var _renderField = /*#__PURE__*/new WeakSet();
	var _onFieldChange = /*#__PURE__*/new WeakSet();
	var _renderJoinerAndDeleteButton = /*#__PURE__*/new WeakSet();
	var _onDeleteClick = /*#__PURE__*/new WeakSet();
	var _renderAddButton = /*#__PURE__*/new WeakSet();
	var _onAddClick = /*#__PURE__*/new WeakSet();
	var PropertyVariableCondition = /*#__PURE__*/function () {
	  function PropertyVariableCondition(options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, PropertyVariableCondition);
	    _classPrivateMethodInitSpec(this, _onAddClick);
	    _classPrivateMethodInitSpec(this, _renderAddButton);
	    _classPrivateMethodInitSpec(this, _onDeleteClick);
	    _classPrivateMethodInitSpec(this, _renderJoinerAndDeleteButton);
	    _classPrivateMethodInitSpec(this, _onFieldChange);
	    _classPrivateMethodInitSpec(this, _renderField);
	    _classPrivateMethodInitSpec(this, _renderConditionTo);
	    _classPrivateFieldInitSpec(this, _table, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _fields, {
	      writable: true,
	      value: {
	        variables: {},
	        properties: {}
	      }
	    });
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _index, {
	      writable: true,
	      value: 0
	    });
	    _classPrivateFieldInitSpec(this, _conditions, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec(this, _conditionCountNode, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isPlainObject(options)) {
	      return;
	    }
	    if (main_core.Type.isElementNode(options.table)) {
	      babelHelpers.classPrivateFieldSet(this, _table, options.table);
	    }
	    if (main_core.Type.isPlainObject(options.conditions)) {
	      var indexes = String(options.conditions.variable_condition_count).split(',').map(main_core.Text.toInteger);
	      indexes.forEach(function (index) {
	        babelHelpers.classPrivateFieldGet(_this, _conditions).push({
	          operator: options.conditions['variable_condition_condition_' + index] || bizproc_condition.Operator.EQUAL,
	          fieldId: options.conditions['variable_condition_field_' + index] || '',
	          joiner: options.conditions['variable_condition_joiner_' + index] || 0,
	          value: options.conditions['variable_condition_value_' + index] || ''
	        });
	      });
	    }
	    if (main_core.Type.isPlainObject(options.variables)) {
	      babelHelpers.classPrivateFieldGet(this, _fields).variables = options.variables;
	    }
	    if (main_core.Type.isPlainObject(options.properties)) {
	      babelHelpers.classPrivateFieldGet(this, _fields).properties = options.properties;
	    }
	    babelHelpers.classPrivateFieldSet(this, _documentType, options.documentType);
	  }
	  babelHelpers.createClass(PropertyVariableCondition, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      var wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<tbody></tbody>"])));
	      babelHelpers.classPrivateFieldGet(this, _conditions).forEach(function (condition) {
	        _classPrivateMethodGet(_this2, _renderConditionTo, _renderConditionTo2).call(_this2, condition, wrapper);
	      });
	      main_core.Dom.append(_classPrivateMethodGet(this, _renderAddButton, _renderAddButton2).call(this), wrapper);
	      main_core.Dom.append(wrapper, babelHelpers.classPrivateFieldGet(this, _table));
	    }
	  }]);
	  return PropertyVariableCondition;
	}();
	function _renderConditionTo2(condition, wrapper) {
	  var _babelHelpers$classPr;
	  var _this$index, _this$index2;
	  var bpCondition = new bizproc_condition.BpCondition({
	    operator: condition.operator || bizproc_condition.Operator.EQUAL,
	    value: condition.value || '',
	    selectName: 'variable_condition_condition_' + String(babelHelpers.classPrivateFieldGet(this, _index)),
	    inputName: 'variable_condition_value_' + String(babelHelpers.classPrivateFieldGet(this, _index)),
	    documentType: babelHelpers.classPrivateFieldGet(this, _documentType),
	    useOperatorModified: false
	  });
	  if (babelHelpers.classPrivateFieldGet(this, _index) !== 0) {
	    main_core.Dom.append(_classPrivateMethodGet(this, _renderJoinerAndDeleteButton, _renderJoinerAndDeleteButton2).call(this, main_core.Text.toInteger(condition.joiner || 0), bpCondition), wrapper);
	  }
	  var fieldNode = _classPrivateMethodGet(this, _renderField, _renderField2).call(this, condition.fieldId || '', babelHelpers.classPrivateFieldGet(this, _index), bpCondition);
	  main_core.Dom.append(fieldNode, wrapper);
	  var fieldId = condition.fieldId || '';
	  var field = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _fields).variables[fieldId]) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : babelHelpers.classPrivateFieldGet(this, _fields).properties[fieldId];
	  if (main_core.Type.isNil(field)) {
	    var _babelHelpers$classPr2;
	    fieldId = fieldNode.getElementsByTagName('SELECT')[0].options[0].value;
	    field = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _fields).variables[fieldId]) !== null && _babelHelpers$classPr2 !== void 0 ? _babelHelpers$classPr2 : babelHelpers.classPrivateFieldGet(this, _fields).properties[fieldId];
	  }
	  bpCondition.renderOperatorTo(field.Type, wrapper);
	  bpCondition.renderValueTo(field, wrapper);
	  babelHelpers.classPrivateFieldSet(this, _index, (_this$index = babelHelpers.classPrivateFieldGet(this, _index), _this$index2 = _this$index++, _this$index)), _this$index2;
	}
	function _renderField2(fieldId, index, condition) {
	  var _this3 = this;
	  var select = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<select name=\"variable_condition_field_", "\"></select>\n\t\t"])), String(index));
	  var propertyKeys = Object.keys(babelHelpers.classPrivateFieldGet(this, _fields).properties);
	  if (propertyKeys.length > 0) {
	    var optgroup = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<optgroup label=\"", "\"></optgroup>"])), main_core.Loc.getMessage('BPFC_PD_PARAMS'));
	    propertyKeys.forEach(function (key) {
	      main_core.Dom.append(main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<option value=\"", "\"", ">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</option>\n\t\t\t\t\t"])), main_core.Text.encode(key), fieldId === key ? ' selected' : '', main_core.Text.encode(babelHelpers.classPrivateFieldGet(_this3, _fields).properties[key].Name)), optgroup);
	    });
	    main_core.Dom.append(optgroup, select);
	  }
	  var variableKeys = Object.keys(babelHelpers.classPrivateFieldGet(this, _fields).variables);
	  if (variableKeys.length > 0) {
	    var _optgroup = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<optgroup label=\"", "\"></optgroup>"])), main_core.Loc.getMessage('BPFC_PD_VARS'));
	    variableKeys.forEach(function (key) {
	      main_core.Dom.append(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<option value=\"", "\"", ">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</option>\n\t\t\t\t\t"])), main_core.Text.encode(key), fieldId === key ? ' selected' : '', main_core.Text.encode(babelHelpers.classPrivateFieldGet(_this3, _fields).variables[key].Name)), _optgroup);
	    });
	    main_core.Dom.append(_optgroup, select);
	  }
	  main_core.Event.bind(select, 'change', _classPrivateMethodGet(this, _onFieldChange, _onFieldChange2).bind(this, condition));
	  return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t\t<td width=\"60%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), main_core.Loc.getMessage('BPFC_PD_FIELD') + ':', select);
	}
	function _onFieldChange2(condition, event) {
	  var _babelHelpers$classPr3, _field$Type, _field;
	  var select = event.target;
	  var fieldId = select.selectedOptions[0].value;
	  var field = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _fields).properties[fieldId]) !== null && _babelHelpers$classPr3 !== void 0 ? _babelHelpers$classPr3 : babelHelpers.classPrivateFieldGet(this, _fields).variables[fieldId];
	  if (!field) {
	    select.selectedIndex = 0;
	    field = babelHelpers.classPrivateFieldGet(this, _fields)[select.selectedOptions[0]];
	  }
	  condition.rerenderOperator((_field$Type = field.Type) !== null && _field$Type !== void 0 ? _field$Type : 'string');
	  condition.rerenderValue((_field = field) !== null && _field !== void 0 ? _field : {
	    Type: 'string'
	  });
	}
	function _renderJoinerAndDeleteButton2(joiner, condition) {
	  var index = babelHelpers.classPrivateFieldGet(this, _index);
	  var deleteButton = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPFC_PD_DELETE'));
	  main_core.Event.bindOnce(deleteButton, 'click', _classPrivateMethodGet(this, _onDeleteClick, _onDeleteClick2).bind(this, condition, index));
	  return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t<select name=\"", "\">\n\t\t\t\t\t\t<option value=\"0\">", "</option>\n\t\t\t\t\t\t<option value=\"1\"", ">", "</option>\n\t\t\t\t\t</select>\n\t\t\t\t</td>\n\t\t\t\t<td align=\"right\" width=\"60%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), 'variable_condition_joiner_' + String(babelHelpers.classPrivateFieldGet(this, _index)), main_core.Loc.getMessage('BPFC_PD_AND'), joiner === 1 ? ' selected' : '', main_core.Loc.getMessage('BPFC_PD_OR'), deleteButton);
	}
	function _onDeleteClick2(condition, index, event) {
	  event.preventDefault();
	  var eventTarget = event.target;
	  var wrapper = eventTarget.closest('tbody');
	  var td = eventTarget.parentElement;
	  var tr = td.parentElement;
	  var rowIndex = tr.rowIndex;
	  wrapper.deleteRow(rowIndex + 1); // field
	  wrapper.deleteRow(rowIndex); // joiner
	  condition.destroy();
	  var currentIndexes = babelHelpers.classPrivateFieldGet(this, _conditionCountNode).value.split(',');
	  var deletedIndex = currentIndexes.indexOf(String(index));
	  if (deletedIndex !== -1) {
	    currentIndexes.splice(deletedIndex, 1);
	  }
	  babelHelpers.classPrivateFieldGet(this, _conditionCountNode).value = currentIndexes.join(',');
	}
	function _renderAddButton2() {
	  babelHelpers.classPrivateFieldSet(this, _conditionCountNode, main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\ttype=\"hidden\"\n\t\t\t\tname=\"variable_condition_count\"\n\t\t\t\tvalue=\"", "\"\n\t\t\t>\n\t\t"])), babelHelpers.toConsumableArray(Array(babelHelpers.classPrivateFieldGet(this, _index))).map(function (value, index) {
	    return index;
	  }).join(',')));
	  var addButton = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPFC_PD_ADD'));
	  main_core.Event.bind(addButton, 'click', _classPrivateMethodGet(this, _onAddClick, _onAddClick2).bind(this));
	  return main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td class=\"adm-detail-content-cell-l\"></td>\n\t\t\t\t<td class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _conditionCountNode), addButton);
	}
	function _onAddClick2(event) {
	  event.preventDefault();
	  var eventTarget = event.target;
	  var wrapper = eventTarget.closest('tbody');
	  var addRow = eventTarget.closest('tr');
	  main_core.Dom.remove(addRow);
	  babelHelpers.classPrivateFieldGet(this, _conditionCountNode).value += ',' + String(babelHelpers.classPrivateFieldGet(this, _index));
	  _classPrivateMethodGet(this, _renderConditionTo, _renderConditionTo2).call(this, {}, wrapper);
	  main_core.Dom.append(addRow, wrapper);
	}
	namespace.PropertyVariableCondition = PropertyVariableCondition;

}((this.BX.Bizproc.Activity = this.BX.Bizproc.Activity || {}),BX,BX.Bizproc));
//# sourceMappingURL=script.js.map
