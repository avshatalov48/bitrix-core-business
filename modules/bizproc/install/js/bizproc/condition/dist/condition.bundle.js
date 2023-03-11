this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	class Operator {
	  static getAll() {
	    return [this.NOT_EMPTY, this.EMPTY, this.EQUAL, this.NOT_EQUAL, this.CONTAIN, this.NOT_CONTAIN, this.IN, this.NOT_IN, this.GREATER_THEN, this.GREATER_THEN_OR_EQUAL, this.LESS_THEN, this.LESS_THEN_OR_EQUAL, this.MODIFIED
	    //this.BETWEEN,
	    ];
	  }

	  static getAllLabels() {
	    return Object.fromEntries([[this.EMPTY, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_EMPTY')], [this.NOT_EMPTY, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_NOT_EMPTY')], [this.EQUAL, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_EQ')], [this.NOT_EQUAL, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_NE')], [this.CONTAIN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_CONTAIN')], [this.NOT_CONTAIN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_NOT_CONTAIN')], [this.IN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_IN')], [this.NOT_IN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_NOT_IN')], [this.GREATER_THEN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_GT')], [this.GREATER_THEN_OR_EQUAL, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_GTE')], [this.LESS_THEN, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_LT')], [this.LESS_THEN_OR_EQUAL, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_LTE')],
	    //[this.BETWEEN, Loc.getMessage('BIZPROC_JS_CONDITION_BETWEEN')],
	    [this.MODIFIED, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_MODIFIED')]]);
	  }
	  static getOperatorLabel(operator) {
	    var _this$getAllLabels$op;
	    return (_this$getAllLabels$op = this.getAllLabels()[operator]) != null ? _this$getAllLabels$op : '';
	  }
	  static getOperatorFieldTypeFilter(operator, isRobot = false) {
	    if (!this.getAll().includes(operator)) {
	      return [];
	    }

	    // if (operator === this.BETWEEN)
	    // {
	    // 	return ['int', 'double', 'date', 'datetime', 'time'];
	    // }

	    return [];
	  }
	  static getAllSortedForBp() {
	    return [this.EQUAL, this.NOT_EQUAL, this.GREATER_THEN, this.GREATER_THEN_OR_EQUAL, this.LESS_THEN, this.LESS_THEN_OR_EQUAL, this.IN, this.NOT_IN, this.CONTAIN, this.NOT_CONTAIN, this.NOT_EMPTY, this.EMPTY, this.MODIFIED
	    //this.BETWEEN,
	    ];
	  }

	  static getOperatorsWithoutRenderValue() {
	    return [this.EMPTY, this.NOT_EMPTY, this.MODIFIED];
	  }
	}
	Operator.EMPTY = 'empty';
	Operator.NOT_EMPTY = '!empty';
	Operator.EQUAL = '=';
	Operator.NOT_EQUAL = '!=';
	Operator.CONTAIN = 'contain';
	Operator.NOT_CONTAIN = '!contain';
	Operator.IN = 'in';
	Operator.NOT_IN = '!in';
	Operator.GREATER_THEN = '>';
	Operator.GREATER_THEN_OR_EQUAL = '>=';
	Operator.LESS_THEN = '<';
	Operator.LESS_THEN_OR_EQUAL = '<=';
	Operator.MODIFIED = 'modified';
	Operator.BETWEEN = 'between';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _operator = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("operator");
	var _operatorName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("operatorName");
	var _valueName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("valueName");
	var _value = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _documentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentType");
	var _useModified = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useModified");
	var _operatorElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("operatorElement");
	var _valueElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("valueElement");
	var _lastFieldProperty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastFieldProperty");
	var _onChangeOperator = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeOperator");
	var _getFilteredOperatorsByFieldType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilteredOperatorsByFieldType");
	var _renderBetweenValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderBetweenValue");
	var _rerenderBetweenValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rerenderBetweenValue");
	var _getFieldControl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldControl");
	class BpCondition$$1 {
	  constructor(parameters) {
	    Object.defineProperty(this, _getFieldControl, {
	      value: _getFieldControl2
	    });
	    Object.defineProperty(this, _rerenderBetweenValue, {
	      value: _rerenderBetweenValue2
	    });
	    Object.defineProperty(this, _renderBetweenValue, {
	      value: _renderBetweenValue2
	    });
	    Object.defineProperty(this, _getFilteredOperatorsByFieldType, {
	      value: _getFilteredOperatorsByFieldType2
	    });
	    Object.defineProperty(this, _onChangeOperator, {
	      value: _onChangeOperator2
	    });
	    Object.defineProperty(this, _operator, {
	      writable: true,
	      value: Operator.EQUAL
	    });
	    Object.defineProperty(this, _operatorName, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _valueName, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _value, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _useModified, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _operatorElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _valueElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lastFieldProperty, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isPlainObject(parameters)) {
	      return;
	    }
	    if (Operator.getAll().includes(parameters.operator)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] = parameters.operator;
	    }
	    if (main_core.Type.isStringFilled(parameters.selectName)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _operatorName)[_operatorName] = parameters.selectName;
	    }
	    if (main_core.Type.isStringFilled(parameters.inputName)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _valueName)[_valueName] = parameters.inputName;
	    }
	    if (main_core.Type.isBoolean(parameters.useOperatorModified)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _useModified)[_useModified] = parameters.useOperatorModified;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = parameters.value;
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType] = parameters.documentType;
	  }
	  renderOperator(fieldType) {
	    const select = main_core.Tag.render(_t || (_t = _`<select name="${0}"></select>`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _operatorName)[_operatorName]));
	    main_core.Event.bind(select, 'change', babelHelpers.classPrivateFieldLooseBase(this, _onChangeOperator)[_onChangeOperator].bind(this));
	    babelHelpers.classPrivateFieldLooseBase(this, _getFilteredOperatorsByFieldType)[_getFilteredOperatorsByFieldType](fieldType).forEach(operator => {
	      main_core.Dom.append(main_core.Tag.render(_t2 || (_t2 = _`
					<option value="${0}"${0}>
						${0}
					</option>
				`), main_core.Text.encode(operator), babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] === operator ? ' selected' : '', main_core.Text.encode(Operator.getOperatorLabel(operator))), select);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement] = select;
	    return main_core.Tag.render(_t3 || (_t3 = _`
			<tr>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${0}
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					${0}
				</td>
			</tr>
		`), main_core.Loc.getMessage('BIZPROC_JS_CONDITION'), select);
	  }
	  rerenderOperator(fieldType) {
	    const filterOperators = babelHelpers.classPrivateFieldLooseBase(this, _getFilteredOperatorsByFieldType)[_getFilteredOperatorsByFieldType](fieldType);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement].options.length === filterOperators.length) {
	      return;
	    }
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement]);
	    filterOperators.forEach(operator => {
	      main_core.Dom.append(main_core.Tag.render(_t4 || (_t4 = _`
					<option value="${0}"${0}>
						${0}
					</option>
				`), main_core.Text.encode(operator), babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] === operator ? ' selected' : '', main_core.Text.encode(Operator.getOperatorLabel(operator))), babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement]);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] = babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement].selectedOptions[0].value;
	  }
	  renderOperatorTo(fieldType, to) {
	    main_core.Dom.append(this.renderOperator(fieldType), to);
	  }
	  renderValue(fieldProperty) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFieldProperty)[_lastFieldProperty] = fieldProperty;
	    babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement] = babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] === Operator.BETWEEN ? babelHelpers.classPrivateFieldLooseBase(this, _renderBetweenValue)[_renderBetweenValue](fieldProperty, babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]) : babelHelpers.classPrivateFieldLooseBase(this, _getFieldControl)[_getFieldControl](fieldProperty, babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    return main_core.Tag.render(_t5 || (_t5 = _`
			<tr${0}>
				<td align="right" width="40%" class="adm-detail-content-cell-l">
					${0}
				</td>
				<td width="60%" class="adm-detail-content-cell-r">
					${0}
				</td>
			</tr>
		`), Operator.getOperatorsWithoutRenderValue().includes(babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator]) ? ' hidden' : '', main_core.Loc.getMessage('BIZPROC_JS_CONDITION_VALUE'), babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement]);
	  }
	  rerenderValue(fieldProperty) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFieldProperty)[_lastFieldProperty] = fieldProperty;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] === Operator.BETWEEN) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _rerenderBetweenValue)[_rerenderBetweenValue](fieldProperty);
	    }
	    const valueElement = babelHelpers.classPrivateFieldLooseBase(this, _getFieldControl)[_getFieldControl](fieldProperty, '');
	    main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement], valueElement);
	    babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement] = valueElement;
	  }
	  renderValueTo(fieldType, to) {
	    main_core.Dom.append(this.renderValue(fieldType), to);
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _operatorName)[_operatorName] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _valueName)[_valueName] = null;
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement].parentElement.parentElement);
	    babelHelpers.classPrivateFieldLooseBase(this, _operatorElement)[_operatorElement] = null;
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement].parentElement.parentElement);
	    babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFieldProperty)[_lastFieldProperty] = null;
	  }
	}
	function _onChangeOperator2(event) {
	  const select = event.target;
	  const previousOperator = String(babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator]);
	  babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] = select.selectedOptions[0].value;
	  const valueRow = babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement].closest('tr');
	  if (Operator.getOperatorsWithoutRenderValue().includes(babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator])) {
	    if (main_core.Dom.isShown(valueRow)) {
	      main_core.Dom.hide(valueRow);
	    }
	    return;
	  }
	  if (!main_core.Dom.isShown(valueRow)) {
	    main_core.Dom.show(valueRow);
	  }
	  const needRerender = previousOperator === Operator.BETWEEN || babelHelpers.classPrivateFieldLooseBase(this, _operator)[_operator] === Operator.BETWEEN || Operator.getOperatorsWithoutRenderValue().includes(previousOperator);
	  if (needRerender) {
	    this.rerenderValue(babelHelpers.classPrivateFieldLooseBase(this, _lastFieldProperty)[_lastFieldProperty]);
	  }
	}
	function _getFilteredOperatorsByFieldType2(fieldType) {
	  return Operator.getAllSortedForBp().filter(operator => {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _useModified)[_useModified] && operator === Operator.MODIFIED) {
	      return false;
	    }
	    const filterFields = Operator.getOperatorFieldTypeFilter(operator);

	    // todo: white list
	    return filterFields.length === 0 || filterFields.includes(fieldType);
	  });
	}
	function _renderBetweenValue2(fieldProperty, value) {
	  fieldProperty = Object.assign(fieldProperty, {
	    Multiple: false
	  });
	  const valueElement1 = babelHelpers.classPrivateFieldLooseBase(this, _getFieldControl)[_getFieldControl](fieldProperty, value[0] || '', babelHelpers.classPrivateFieldLooseBase(this, _valueName)[_valueName] + '_greater_then');
	  const valueElement2 = babelHelpers.classPrivateFieldLooseBase(this, _getFieldControl)[_getFieldControl](fieldProperty, value[1] || '', babelHelpers.classPrivateFieldLooseBase(this, _valueName)[_valueName] + '_less_then');
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<table>
				<tbody>
					<tr><td>${0}</td></tr>
					<tr><td>${0}</td></tr>
					<tr><td>${0}</td></tr>
				</tbody>
			</table>
		`), valueElement1, main_core.Loc.getMessage('BIZPROC_JS_CONDITION_BETWEEN_JOINER'), valueElement2);
	}
	function _rerenderBetweenValue2(fieldProperty) {
	  const valueElement = babelHelpers.classPrivateFieldLooseBase(this, _renderBetweenValue)[_renderBetweenValue](fieldProperty, ['', '']);
	  main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement], valueElement);
	  babelHelpers.classPrivateFieldLooseBase(this, _valueElement)[_valueElement] = valueElement;
	}
	function _getFieldControl2(fieldProperty, value, valueName) {
	  if (main_core.Type.isNil(valueName)) {
	    valueName = babelHelpers.classPrivateFieldLooseBase(this, _valueName)[_valueName];
	  }
	  return BX.Bizproc.FieldType.renderControl(babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType], fieldProperty, valueName, value, 'designer');
	}

	exports.Operator = Operator;
	exports.BpCondition = BpCondition$$1;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX));
//# sourceMappingURL=condition.bundle.js.map
