/* eslint-disable */
(function (exports,main_core,bizproc_mixedSelector,bizproc_condition) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _documentType = /*#__PURE__*/new WeakMap();
	var _createAddConditionNode = /*#__PURE__*/new WeakSet();
	var _createJoiner = /*#__PURE__*/new WeakSet();
	var _createSource = /*#__PURE__*/new WeakSet();
	var _findActivityTitle = /*#__PURE__*/new WeakSet();
	var _deleteCondition = /*#__PURE__*/new WeakSet();
	var MixedCondition = /*#__PURE__*/function () {
	  function MixedCondition(options) {
	    babelHelpers.classCallCheck(this, MixedCondition);
	    _classPrivateMethodInitSpec(this, _deleteCondition);
	    _classPrivateMethodInitSpec(this, _findActivityTitle);
	    _classPrivateMethodInitSpec(this, _createSource);
	    _classPrivateMethodInitSpec(this, _createJoiner);
	    _classPrivateMethodInitSpec(this, _createAddConditionNode);
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.defineProperty(this, "index", 0);
	    if (main_core.Type.isPlainObject(options)) {
	      this.conditions = options.conditions;
	      this.table = options.table;
	      this.objectTabs = options.objectTabs;
	      this.template = options.template;
	      babelHelpers.classPrivateFieldSet(this, _documentType, options.documentType);
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
	      var _this$getProperty, _condition$object, _condition$field;
	      var condition = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	        object: null,
	        field: null,
	        value: null,
	        joiner: '0',
	        operator: bizproc_condition.Operator.NOT_EMPTY
	      };
	      condition.object = condition.object === 'Template' ? 'Parameter' : condition.object;
	      var bpCondition = new bizproc_condition.BpCondition({
	        operator: condition.operator,
	        value: condition.value,
	        selectName: 'mixed_condition[' + main_core.Text.toInteger(this.index) + '][operator]',
	        inputName: 'mixed_condition_value_' + main_core.Text.toInteger(this.index),
	        useOperatorModified: false,
	        documentType: babelHelpers.classPrivateFieldGet(this, _documentType)
	      });
	      var property = (_this$getProperty = this.getProperty(condition.object, condition.field)) !== null && _this$getProperty !== void 0 ? _this$getProperty : {
	        Type: 'string'
	      };
	      var joiner = this.index > 0 ? _classPrivateMethodGet(this, _createJoiner, _createJoiner2).call(this, condition.joiner) : '';
	      var tbody = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tbody \n\t\t\t\tdata-index=\"", "\"\n\t\t\t\tdata-object=\"", "\"\n\t\t\t\tdata-field=\"", "\"\n\t\t\t>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</tbody>\n\t\t"])), main_core.Text.toInteger(this.index), main_core.Text.encode((_condition$object = condition.object) !== null && _condition$object !== void 0 ? _condition$object : ''), main_core.Text.encode((_condition$field = condition.field) !== null && _condition$field !== void 0 ? _condition$field : ''), joiner, _classPrivateMethodGet(this, _createSource, _createSource2).call(this, condition.object, condition.field), bpCondition.renderOperator(property.Type), bpCondition.renderValue(property));
	      if (this.selector) {
	        this.selector.subscribe('onSelect', function (event) {
	          var _this$getProperty2;
	          var object = event.data.item.object;
	          var field = event.data.item.field;
	          var property = (_this$getProperty2 = this.getProperty(object, field)) !== null && _this$getProperty2 !== void 0 ? _this$getProperty2 : {
	            Type: 'string'
	          };
	          tbody.setAttribute('data-object', object);
	          tbody.setAttribute('data-field', field);
	          bpCondition.rerenderOperator(property.Type);
	          bpCondition.rerenderValue(property);
	        }.bind(this));
	      }
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
	  var addButton = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPMC_PD_ADD'));
	  main_core.Event.bind(addButton, 'click', function (event) {
	    event.preventDefault();
	    me.addCondition();
	  });
	  return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tbody>\n\t\t\t\t<tr>\n\t\t\t\t\t<td class=\"adm-detail-content-cell-l\"></td>\n\t\t\t\t\t<td class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</td>\n\t\t\t\t</tr>\n\t\t\t</tbody>\n\t\t"])), addButton);
	}
	function _createJoiner2(joiner) {
	  var deleteNode = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\">", "</a>"])), main_core.Loc.getMessage('BPMC_PD_DELETE'));
	  main_core.Event.bind(deleteNode, 'click', _classPrivateMethodGet(this, _deleteCondition, _deleteCondition2).bind(this));
	  return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t<select name=\"mixed_condition[", "][joiner]\">\n\t\t\t\t\t\t<option value=\"0\">", "</option>\n\t\t\t\t\t\t<option value=\"1\"", ">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</option>\n\t\t\t\t\t</select>\n\t\t\t\t</td>\n\t\t\t\t<td align=\"right\" width=\"60%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), main_core.Text.toInteger(this.index), main_core.Loc.getMessage('BPMC_PD_AND'), main_core.Text.toInteger(joiner) === 1 ? ' selected' : '', main_core.Loc.getMessage('BPMC_PD_OR'), deleteNode);
	}
	function _createSource2(object, field) {
	  var source = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<td width=\"60%\" class=\"adm-detail-content-cell-r\"></td>"])));
	  this.selector = new bizproc_mixedSelector.BpMixedSelector({
	    targetNode: source,
	    template: this.template,
	    objectTabs: this.objectTabs,
	    inputNames: {
	      object: 'mixed_condition[' + String(main_core.Text.toInteger(this.index)) + '][object]',
	      field: 'mixed_condition[' + String(main_core.Text.toInteger(this.index)) + '][field]'
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
	  return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td align=\"right\" width=\"40%\" class=\"adm-detail-content-cell-l\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t\t", "\n\t\t\t</tr>\n\t\t"])), main_core.Loc.getMessage('BPMC_PD_FIELD') + ':', source);
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
	function _deleteCondition2(event) {
	  var target = event.target.closest('tbody');
	  if (target) {
	    main_core.Dom.remove(target);
	  }
	  event.preventDefault();
	}
	namespace.MixedCondition = MixedCondition;

}((this.window = this.window || {}),BX,BX.Bizproc,BX.Bizproc));
//# sourceMappingURL=script.js.map
