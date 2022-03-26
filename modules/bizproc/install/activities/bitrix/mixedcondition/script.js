(function (exports,main_core,bizproc_mixedSelector) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');

	var _createAddConditionNode = /*#__PURE__*/new WeakSet();

	var _createJoiner = /*#__PURE__*/new WeakSet();

	var _createSource = /*#__PURE__*/new WeakSet();

	var _findActivityTitle = /*#__PURE__*/new WeakSet();

	var _createCondition = /*#__PURE__*/new WeakSet();

	var _createField = /*#__PURE__*/new WeakSet();

	var _deleteCondition = /*#__PURE__*/new WeakSet();

	var _changeCondition = /*#__PURE__*/new WeakSet();

	var _renderValue = /*#__PURE__*/new WeakSet();

	var _renderField = /*#__PURE__*/new WeakSet();

	var MixedCondition = /*#__PURE__*/function () {
	  function MixedCondition(options) {
	    babelHelpers.classCallCheck(this, MixedCondition);

	    _classPrivateMethodInitSpec(this, _renderField);

	    _classPrivateMethodInitSpec(this, _renderValue);

	    _classPrivateMethodInitSpec(this, _changeCondition);

	    _classPrivateMethodInitSpec(this, _deleteCondition);

	    _classPrivateMethodInitSpec(this, _createField);

	    _classPrivateMethodInitSpec(this, _createCondition);

	    _classPrivateMethodInitSpec(this, _findActivityTitle);

	    _classPrivateMethodInitSpec(this, _createSource);

	    _classPrivateMethodInitSpec(this, _createJoiner);

	    _classPrivateMethodInitSpec(this, _createAddConditionNode);

	    babelHelpers.defineProperty(this, "index", 0);

	    if (main_core.Type.isPlainObject(options)) {
	      this.operatorList = options.operatorList;
	      this.conditions = options.conditions;
	      this.table = options.table;
	      this.objectTabs = options.objectTabs;
	      this.template = options.template;
	      this.formName = options.formName;
	    }
	  }

	  babelHelpers.createClass(MixedCondition, [{
	    key: "init",
	    value: function init() {
	      this.addConditionNode = _classPrivateMethodGet(this, _createAddConditionNode, _createAddConditionNode2).call(this);
	      main_core.Dom.append(this.addConditionNode, this.table);

	      for (var i in this.conditions) {
	        this.addCondition(this.conditions[i]);
	      }
	    }
	  }, {
	    key: "addCondition",
	    value: function addCondition() {
	      var _BX$util$htmlspecialc, _BX$util$htmlspecialc2;

	      var condition = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        object: null,
	        field: null,
	        value: null,
	        joiner: '0',
	        operator: '!empty'
	      };
	      var me = this;

	      if (condition.object === 'Template') {
	        condition.object = 'Parameter';
	      } // Tag.render can't render <tbody>, <td>, <tr>;


	      var tbody = main_core.Dom.create('tbody', {
	        attrs: {
	          'data-index': String(this.index),
	          'data-object': (_BX$util$htmlspecialc = BX.util.htmlspecialchars(condition.object)) !== null && _BX$util$htmlspecialc !== void 0 ? _BX$util$htmlspecialc : '',
	          'data-field': (_BX$util$htmlspecialc2 = BX.util.htmlspecialchars(condition.field)) !== null && _BX$util$htmlspecialc2 !== void 0 ? _BX$util$htmlspecialc2 : ''
	        }
	      });

	      var joinerNode = _classPrivateMethodGet(this, _createJoiner, _createJoiner2).call(this, condition.joiner);

	      if (this.index > 0) {
	        main_core.Dom.append(joinerNode, tbody);
	      }

	      var sourceNode = _classPrivateMethodGet(this, _createSource, _createSource2).call(this, condition.object, condition.field);

	      main_core.Dom.append(sourceNode, tbody);

	      if (this.selector) {
	        this.selector.subscribe('onSelect', function (event) {
	          tbody.setAttribute('data-object', event.data.item.object);
	          tbody.setAttribute('data-field', event.data.item.field);

	          _classPrivateMethodGet(me, _renderValue, _renderValue2).call(me, tbody);
	        });
	      }

	      var conditionNode = _classPrivateMethodGet(this, _createCondition, _createCondition2).call(this, condition.operator);

	      main_core.Dom.append(conditionNode, tbody);

	      var fieldNode = _classPrivateMethodGet(this, _createField, _createField2).call(this, condition.operator);

	      main_core.Dom.append(fieldNode, tbody);

	      _classPrivateMethodGet(this, _renderValue, _renderValue2).call(this, tbody, condition.operator, condition.value);

	      main_core.Dom.insertBefore(tbody, this.addConditionNode);
	      this.index++;
	    }
	  }, {
	    key: "getProperty",
	    value: function getProperty(object, field) {
	      if (object && this.objectTabs[object]) {
	        return this.objectTabs[object][field];
	      }

	      var results = BX.Bizproc.Selector.getActivitiesItems();

	      for (var i = 0; i < results.length; ++i) {
	        if (results[i].propertyObject === object && results[i].propertyField === field) {
	          return results[i].property;
	        }
	      }

	      return null;
	    }
	  }]);
	  return MixedCondition;
	}();

	function _createAddConditionNode2() {
	  var me = this;
	  return main_core.Dom.create('tbody', {
	    children: [main_core.Dom.create('tr', {
	      children: [main_core.Dom.create('td', {
	        attrs: {
	          className: 'adm-detail-content-cell-l'
	        }
	      }), main_core.Dom.create('td', {
	        attrs: {
	          className: 'adm-detail-content-cell-r'
	        },
	        children: [main_core.Dom.create('a', {
	          attrs: {
	            href: '#'
	          },
	          text: main_core.Loc.getMessage('BPMC_PD_ADD'),
	          events: {
	            click: function click(event) {
	              me.addCondition();
	              event.preventDefault();
	            }
	          }
	        })]
	      })]
	    })]
	  });
	}

	function _createJoiner2(joiner) {
	  var wrapJoiner = main_core.Dom.create('td', {
	    attrs: {
	      className: 'adm-detail-content-cell-l',
	      align: 'right',
	      width: '40%'
	    }
	  });
	  var joinerNode = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<select name=\"mixed_condition[", "][joiner]\">\n\t\t\t\t<option value=\"0\">", "</option>\n\t\t\t\t<option value=\"1\">", "</option>\n\t\t\t</select>\n\t\t"])), this.index, main_core.Loc.getMessage('BPMC_PD_AND'), main_core.Loc.getMessage('BPMC_PD_OR'));

	  if (String(joiner) === '1') {
	    joinerNode.value = '1';
	  }

	  main_core.Dom.append(joinerNode, wrapJoiner);
	  var wrapDelete = main_core.Dom.create('td', {
	    attrs: {
	      className: "adm-detail-content-cell-r",
	      align: 'right',
	      width: '60%'
	    }
	  });
	  var deleteNode = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPMC_PD_DELETE'));
	  main_core.Event.bind(deleteNode, 'click', _classPrivateMethodGet(this, _deleteCondition, _deleteCondition2).bind(this));
	  main_core.Dom.append(deleteNode, wrapDelete);
	  return main_core.Dom.create('tr', {
	    children: [wrapJoiner, wrapDelete]
	  });
	}

	function _createSource2(object, field) {
	  var label = main_core.Dom.create('td', {
	    attrs: {
	      className: 'adm-detail-content-cell-l',
	      align: 'right',
	      width: '40%'
	    },
	    text: main_core.Loc.getMessage('BPMC_PD_FIELD') + ':'
	  });
	  var source = main_core.Dom.create('td', {
	    attrs: {
	      className: 'adm-detail-content-cell-r',
	      width: "60%"
	    }
	  });
	  this.selector = new bizproc_mixedSelector.BpMixedSelector({
	    targetNode: source,
	    template: this.template,
	    objectTabs: this.objectTabs,
	    inputNames: {
	      object: 'mixed_condition[' + String(this.index) + '][object]',
	      field: 'mixed_condition[' + String(this.index) + '][field]'
	    }
	  });
	  this.selector.renderMixedSelector();

	  if (object && field && this.objectTabs[object] && this.objectTabs[object][field]) {
	    this.selector.setSelectedObjectAndField(object, field, this.objectTabs[object][field]['Name']);
	  } else {
	    var sourceName = _classPrivateMethodGet(this, _findActivityTitle, _findActivityTitle2).call(this, object, field);

	    if (sourceName) {
	      this.selector.setSelectedObjectAndField(object, field, sourceName);
	    }
	  }

	  return main_core.Dom.create('tr', {
	    children: [label, source]
	  });
	}

	function _findActivityTitle2(object, field) {
	  var activityTabItems = this.selector.getMenuItemsByTabName('Activity');

	  for (var i in activityTabItems) {
	    var activityInfo = activityTabItems[i];

	    if (activityInfo.object === object) {
	      var activityItems = activityInfo.items;

	      for (var j in activityItems) {
	        var itemInfo = activityItems[j];

	        if (itemInfo.field === field) {
	          return itemInfo.text;
	        }
	      }
	    }
	  }

	  return null;
	}

	function _createCondition2(operator) {
	  var label = main_core.Dom.create('td', {
	    attrs: {
	      className: 'adm-detail-content-cell-l',
	      align: 'right',
	      width: "40%"
	    },
	    text: main_core.Loc.getMessage('BPMC_PD_CONDITION') + ':'
	  });
	  var select = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<select name=\"mixed_condition[", "][operator]\" data-role=\"operator-selector\"></select>\n\t\t"])), this.index);
	  main_core.Event.bind(select, 'change', _classPrivateMethodGet(this, _changeCondition, _changeCondition2).bind(this));

	  for (var operation in this.operatorList) {
	    var option = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<option value=\"", "\">", "</option>\n\t\t\t"])), operation, BX.util.htmlspecialchars(this.operatorList[operation]));
	    main_core.Dom.append(option, select);
	  }

	  select.value = operator;

	  if (select.selectedIndex === -1) {
	    select.value = '!empty';
	  }

	  return main_core.Dom.create('tr', {
	    children: [label, main_core.Dom.create('td', {
	      attrs: {
	        className: 'adm-detail-content-cell-r',
	        width: "60%"
	      },
	      children: [select]
	    })]
	  });
	}

	function _createField2(operator) {
	  var wrapper = main_core.Dom.create('tr', {
	    attrs: {
	      'data-role': 'value-row'
	    },
	    children: [main_core.Dom.create('td', {
	      attrs: {
	        className: 'adm-detail-content-cell-l',
	        align: 'right',
	        width: '40%'
	      },
	      text: main_core.Loc.getMessage('BPMC_PD_VALUE') + ':'
	    }), main_core.Dom.create('td', {
	      attrs: {
	        className: 'adm-detail-content-cell-r',
	        'data-role': 'value-cell',
	        width: '60%'
	      },
	      text: '...'
	    })]
	  });

	  if (['empty', '!empty'].includes(operator)) {
	    main_core.Dom.style(wrapper, 'display', 'none');
	  }

	  return wrapper;
	}

	function _deleteCondition2(event) {
	  var target = event.target.closest('tbody');

	  if (target) {
	    main_core.Dom.remove(target);
	  }

	  event.preventDefault();
	}

	function _changeCondition2(event) {
	  var target = event.target;

	  _classPrivateMethodGet(this, _renderValue, _renderValue2).call(this, target.closest('tbody'), target.value);
	}

	function _renderValue2(conditionNode, operator) {
	  var value = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	  operator = operator || conditionNode.querySelector('[data-role="operator-selector"]').value;
	  var valueRow = conditionNode.querySelector('[data-role="value-row"]');

	  if (['empty', '!empty'].includes(operator)) {
	    main_core.Dom.style(valueRow, 'display', 'none');
	  } else {
	    main_core.Dom.style(valueRow, 'display', '');

	    _classPrivateMethodGet(this, _renderField, _renderField2).call(this, conditionNode, value);
	  }
	}

	function _renderField2(conditionNode, value) {
	  var cell = conditionNode.querySelector('[data-role="value-cell"]');
	  var index = conditionNode.getAttribute('data-index');
	  var property = this.getProperty(conditionNode.getAttribute('data-object'), conditionNode.getAttribute('data-field'));

	  if (!property) {
	    return;
	  }

	  objFieldsPVC.GetFieldInputControl(property, value !== null && value !== void 0 ? value : '', {
	    Field: 'mixed_condition_value_' + index,
	    Form: this.formName
	  }, function (value) {
	    if (value) {
	      cell.innerHTML = value;
	    }

	    if (!main_core.Type.isUndefined(BX.Bizproc.Selector)) {
	      BX.Bizproc.Selector.initSelectors(cell);
	    }
	  }, true);
	}

	namespace.MixedCondition = MixedCondition;

}((this.window = this.window || {}),BX,BX.Bizproc));
//# sourceMappingURL=script.js.map
