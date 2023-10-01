/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,bizproc_condition) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _table = /*#__PURE__*/new WeakMap();
	var _conditions = /*#__PURE__*/new WeakMap();
	var _documentField = /*#__PURE__*/new WeakMap();
	var _documentType = /*#__PURE__*/new WeakMap();
	var _index = /*#__PURE__*/new WeakMap();
	var _fieldConditionCountNode = /*#__PURE__*/new WeakMap();
	var _useOperatorModified = /*#__PURE__*/new WeakMap();
	var _renderConditionTo = /*#__PURE__*/new WeakSet();
	var _renderField = /*#__PURE__*/new WeakSet();
	var _onFieldChange = /*#__PURE__*/new WeakSet();
	var _renderJoinerAndDeleteButton = /*#__PURE__*/new WeakSet();
	var _onDeleteClick = /*#__PURE__*/new WeakSet();
	var _renderAddButton = /*#__PURE__*/new WeakSet();
	var _onAddClick = /*#__PURE__*/new WeakSet();
	var FieldCondition = /*#__PURE__*/function () {
	  function FieldCondition(options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, FieldCondition);
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
	    _classPrivateFieldInitSpec(this, _conditions, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec(this, _documentField, {
	      writable: true,
	      value: {}
	    });
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _index, {
	      writable: true,
	      value: 0
	    });
	    _classPrivateFieldInitSpec(this, _fieldConditionCountNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _useOperatorModified, {
	      writable: true,
	      value: false
	    });
	    if (!main_core.Type.isPlainObject(options)) {
	      return;
	    }
	    if (main_core.Type.isElementNode(options.table)) {
	      babelHelpers.classPrivateFieldSet(this, _table, options.table);
	    }
	    if (main_core.Type.isPlainObject(options.conditions)) {
	      var indexes = String(options.conditions.field_condition_count).split(',').map(main_core.Text.toInteger);
	      indexes.forEach(function (index) {
	        babelHelpers.classPrivateFieldGet(_this, _conditions).push({
	          operator: options.conditions['field_condition_condition_' + index] || bizproc_condition.Operator.EQUAL,
	          fieldId: options.conditions['field_condition_field_' + index] || '',
	          joiner: options.conditions['field_condition_joiner_' + index] || 0,
	          value: options.conditions['field_condition_value_' + index] || ''
	        });
	      });
	    }
	    if (main_core.Type.isPlainObject(options.documentFields)) {
	      babelHelpers.classPrivateFieldSet(this, _documentField, options.documentFields);
	    }
	    babelHelpers.classPrivateFieldSet(this, _documentType, options.documentType);
	    babelHelpers.classPrivateFieldSet(this, _useOperatorModified, options.useOperatorModified === 'Y');
	  }
	  babelHelpers.createClass(FieldCondition, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;
	      var wrapper = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<tbody></tbody>"])));
	      babelHelpers.classPrivateFieldGet(this, _conditions).forEach(function (condition) {
	        return _classPrivateMethodGet(_this2, _renderConditionTo, _renderConditionTo2).call(_this2, condition, wrapper);
	      });
	      main_core.Dom.append(_classPrivateMethodGet(this, _renderAddButton, _renderAddButton2).call(this), wrapper);
	      main_core.Dom.append(wrapper, babelHelpers.classPrivateFieldGet(this, _table));
	    }
	  }]);
	  return FieldCondition;
	}();
	function _renderConditionTo2(condition, wrapper) {
	  var _babelHelpers$classPr;
	  var _this$index, _this$index2;
	  var bpCondition = new bizproc_condition.BpCondition({
	    operator: condition.operator || bizproc_condition.Operator.EQUAL,
	    value: condition.value || '',
	    selectName: 'field_condition_condition_' + String(babelHelpers.classPrivateFieldGet(this, _index)),
	    inputName: 'field_condition_value_' + String(babelHelpers.classPrivateFieldGet(this, _index)),
	    documentType: babelHelpers.classPrivateFieldGet(this, _documentType),
	    useOperatorModified: babelHelpers.classPrivateFieldGet(this, _useOperatorModified)
	  });
	  if (babelHelpers.classPrivateFieldGet(this, _index) !== 0) {
	    main_core.Dom.append(_classPrivateMethodGet(this, _renderJoinerAndDeleteButton, _renderJoinerAndDeleteButton2).call(this, main_core.Text.toInteger(condition.joiner || 0), bpCondition), wrapper);
	  }
	  var fieldNode = _classPrivateMethodGet(this, _renderField, _renderField2).call(this, condition.fieldId || '', babelHelpers.classPrivateFieldGet(this, _index), bpCondition);
	  main_core.Dom.append(fieldNode, wrapper);
	  var field = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _documentField)[condition.fieldId || '']) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : babelHelpers.classPrivateFieldGet(this, _documentField)[fieldNode.getElementsByTagName('SELECT')[0].options[0].value];
	  bpCondition.renderOperatorTo(field.BaseType, wrapper);
	  bpCondition.renderValueTo(field, wrapper);
	  babelHelpers.classPrivateFieldSet(this, _index, (_this$index = babelHelpers.classPrivateFieldGet(this, _index), _this$index2 = _this$index++, _this$index)), _this$index2;
	}
	function _renderField2() {
	  var _this3 = this;
	  var fieldId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	  var index = arguments.length > 1 ? arguments[1] : undefined;
	  var condition = arguments.length > 2 ? arguments[2] : undefined;
	  var select = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<select name=\"field_condition_field_", "\"></select>\n\t\t"])), String(index));
	  Object.keys(babelHelpers.classPrivateFieldGet(this, _documentField)).forEach(function (key) {
	    main_core.Dom.append(main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<option value=\"", "\"", ">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</option>\n\t\t\t\t"])), main_core.Text.encode(key), fieldId === key ? ' selected' : '', main_core.Text.encode(babelHelpers.classPrivateFieldGet(_this3, _documentField)[key].Name)), select);
	  });
	  main_core.Event.bind(select, 'change', _classPrivateMethodGet(this, _onFieldChange, _onFieldChange2).bind(this, condition));
	  return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t\t<td width=\"60%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), main_core.Loc.getMessage('BPFC_PD_FIELD') + ':', select);
	}
	function _onFieldChange2(condition, event) {
	  var _field$BaseType, _field;
	  var select = event.target;
	  var fieldId = select.selectedOptions[0].value;
	  var field = babelHelpers.classPrivateFieldGet(this, _documentField)[fieldId];
	  if (!field) {
	    select.selectedIndex = 0;
	    field = babelHelpers.classPrivateFieldGet(this, _documentField)[select.selectedOptions[0]];
	  }
	  condition.rerenderOperator((_field$BaseType = field.BaseType) !== null && _field$BaseType !== void 0 ? _field$BaseType : 'string');
	  condition.rerenderValue((_field = field) !== null && _field !== void 0 ? _field : {
	    Type: 'string'
	  });
	}
	function _renderJoinerAndDeleteButton2(joiner, condition) {
	  var index = babelHelpers.classPrivateFieldGet(this, _index);
	  var deleteButton = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPFC_PD_DELETE'));
	  main_core.Event.bindOnce(deleteButton, 'click', _classPrivateMethodGet(this, _onDeleteClick, _onDeleteClick2).bind(this, condition, index));
	  return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t<select name=\"", "\">\n\t\t\t\t\t\t<option value=\"0\">", "</option>\n\t\t\t\t\t\t<option value=\"1\"", ">", "</option>\n\t\t\t\t\t</select>\n\t\t\t\t</td>\n\t\t\t\t<td align=\"right\" width=\"60%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), 'field_condition_joiner_' + String(babelHelpers.classPrivateFieldGet(this, _index)), main_core.Loc.getMessage('BPFC_PD_AND'), joiner === 1 ? ' selected' : '', main_core.Loc.getMessage('BPFC_PD_OR'), deleteButton);
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
	  var currentIndexes = babelHelpers.classPrivateFieldGet(this, _fieldConditionCountNode).value.split(',');
	  var deletedIndex = currentIndexes.indexOf(String(index));
	  if (deletedIndex !== -1) {
	    currentIndexes.splice(deletedIndex, 1);
	  }
	  babelHelpers.classPrivateFieldGet(this, _fieldConditionCountNode).value = currentIndexes.join(',');
	}
	function _renderAddButton2() {
	  babelHelpers.classPrivateFieldSet(this, _fieldConditionCountNode, main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input \n\t\t\t\ttype=\"hidden\"\n\t\t\t\tname=\"field_condition_count\"\n\t\t\t\tvalue=\"", "\"\n\t\t\t>\n\t\t"])), babelHelpers.toConsumableArray(Array(babelHelpers.classPrivateFieldGet(this, _index))).map(function (value, index) {
	    return index;
	  }).join(',')));
	  var addButton = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPFC_PD_ADD'));
	  main_core.Event.bind(addButton, 'click', _classPrivateMethodGet(this, _onAddClick, _onAddClick2).bind(this));
	  return main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td class=\"adm-detail-content-cell-l\"></td>\n\t\t\t\t<td class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _fieldConditionCountNode), addButton);
	}
	function _onAddClick2(event) {
	  event.preventDefault();
	  var eventTarget = event.target;
	  var wrapper = eventTarget.closest('tbody');
	  var addRow = eventTarget.closest('tr');
	  main_core.Dom.remove(addRow);
	  babelHelpers.classPrivateFieldGet(this, _fieldConditionCountNode).value += ',' + String(babelHelpers.classPrivateFieldGet(this, _index));
	  _classPrivateMethodGet(this, _renderConditionTo, _renderConditionTo2).call(this, {}, wrapper);
	  main_core.Dom.append(addRow, wrapper);
	}
	namespace.FieldCondition = FieldCondition;

}((this.BX.Bizproc.Activity = this.BX.Bizproc.Activity || {}),BX,BX.Bizproc));
//# sourceMappingURL=script.js.map
