/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,bizproc_mixedSelector,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _renderResultProperty = /*#__PURE__*/new WeakSet();
	var _renderAccessProperty = /*#__PURE__*/new WeakSet();
	var _createSource = /*#__PURE__*/new WeakSet();
	var _findActivityTitle = /*#__PURE__*/new WeakSet();
	var FixResultActivity = /*#__PURE__*/function () {
	  function FixResultActivity(options) {
	    babelHelpers.classCallCheck(this, FixResultActivity);
	    _classPrivateMethodInitSpec(this, _findActivityTitle);
	    _classPrivateMethodInitSpec(this, _createSource);
	    _classPrivateMethodInitSpec(this, _renderAccessProperty);
	    _classPrivateMethodInitSpec(this, _renderResultProperty);
	    babelHelpers.defineProperty(this, "resultFieldsContainer", undefined);
	    babelHelpers.defineProperty(this, "accessFieldsContainer", undefined);
	    babelHelpers.defineProperty(this, "accessTypeSelect", undefined);
	    babelHelpers.defineProperty(this, "resultTypeSelect", undefined);
	    babelHelpers.defineProperty(this, "currentResultValues", {});
	    babelHelpers.defineProperty(this, "currentAccessValues", {});
	    babelHelpers.defineProperty(this, "renderedResultProperties", {});
	    babelHelpers.defineProperty(this, "renderedAccessProperties", {});
	    this.accessFieldsContainer = document.getElementById('access-fields-container');
	    this.resultFieldsContainer = document.getElementById('result-fields-container');
	    if (main_core.Type.isPlainObject(options)) {
	      var form = document.forms[options.formName];
	      if (!main_core.Type.isNil(form)) {
	        this.accessTypeSelect = form.access_type;
	        this.resultTypeSelect = form.result_type;
	      }
	      this.resultFieldsMap = options.resultFieldsMap;
	      this.accessFieldsMap = options.accessFieldsMap;
	      this.objectTabs = options.objectTabs;
	      this.template = options.template;
	      this.activityFilter = options.activityFilter;
	      if (main_core.Type.isPlainObject(options.currentResultValues)) {
	        this.currentResultValues = options.currentResultValues;
	      }
	      if (main_core.Type.isPlainObject(options.currentAccessValues)) {
	        this.currentAccessValues = options.currentAccessValues;
	      }
	    }
	  }
	  babelHelpers.createClass(FixResultActivity, [{
	    key: "getBindFieldId",
	    value: function getBindFieldId() {
	      return "".concat(this.currentResultType, "_BindToCurrentElement");
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (this.resultTypeSelect) {
	        this.renderResultFields();
	        main_core.Event.bind(this.resultTypeSelect, 'change', this.onResultTypeChange.bind(this));
	      }
	      if (this.accessTypeSelect) {
	        this.renderAccessFields();
	        main_core.Event.bind(this.accessTypeSelect, 'change', this.onAccessTypeChange.bind(this));
	      }
	    }
	  }, {
	    key: "onResultTypeChange",
	    value: function onResultTypeChange() {
	      main_core.Dom.clean(this.resultFieldsContainer);
	      this.currentResultValues = {};
	      this.renderResultFields();
	    }
	  }, {
	    key: "onAccessTypeChange",
	    value: function onAccessTypeChange() {
	      main_core.Dom.clean(this.accessFieldsContainer);
	      this.currentAccessValues = {};
	      this.renderAccessFields();
	    }
	  }, {
	    key: "renderResultFields",
	    value: function renderResultFields() {
	      if (Object.hasOwn(this.resultFieldsMap, this.currentResultType)) {
	        var _this$resultFieldsMap = this.resultFieldsMap[this.currentResultType],
	          documentType = _this$resultFieldsMap.documentType,
	          fieldsMap = _this$resultFieldsMap.fieldsMap;
	        this.loadRenderedResultFields();
	        for (var _i = 0, _Object$keys = Object.keys(fieldsMap); _i < _Object$keys.length; _i++) {
	          var fieldId = _Object$keys[_i];
	          main_core.Dom.append(_classPrivateMethodGet(this, _renderResultProperty, _renderResultProperty2).call(this, fieldId), this.resultFieldsContainer);
	        }
	      }
	    }
	  }, {
	    key: "renderAccessFields",
	    value: function renderAccessFields() {
	      if (Object.hasOwn(this.accessFieldsMap, this.currentAccessType)) {
	        var _this$accessFieldsMap = this.accessFieldsMap[this.currentAccessType],
	          documentType = _this$accessFieldsMap.documentType,
	          fieldsMap = _this$accessFieldsMap.fieldsMap;
	        this.loadRenderedAccessFields();
	        for (var _i2 = 0, _Object$keys2 = Object.keys(fieldsMap); _i2 < _Object$keys2.length; _i2++) {
	          var fieldId = _Object$keys2[_i2];
	          main_core.Dom.append(_classPrivateMethodGet(this, _renderAccessProperty, _renderAccessProperty2).call(this, fieldId), this.accessFieldsContainer);
	        }
	      }
	    }
	  }, {
	    key: "loadRenderedResultFields",
	    value: function loadRenderedResultFields() {
	      var _this = this;
	      var _this$resultFieldsMap2 = this.resultFieldsMap[this.currentResultType],
	        documentType = _this$resultFieldsMap2.documentType,
	        fieldsMap = _this$resultFieldsMap2.fieldsMap;
	      if (main_core.Type.isFunction(BX.Bizproc.FieldType.renderControlCollection)) {
	        this.renderedResultProperties = BX.Bizproc.FieldType.renderControlCollection(documentType, Object.entries(fieldsMap).map(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	            fieldId = _ref2[0],
	            field = _ref2[1];
	          return {
	            property: field,
	            fieldName: field.FieldName,
	            value: _this.currentResultValues[fieldId],
	            controlId: fieldId
	          };
	        }), 'designer');
	      }
	    }
	  }, {
	    key: "loadRenderedAccessFields",
	    value: function loadRenderedAccessFields() {
	      var _this2 = this;
	      var _this$accessFieldsMap2 = this.accessFieldsMap[this.currentAccessType],
	        documentType = _this$accessFieldsMap2.documentType,
	        fieldsMap = _this$accessFieldsMap2.fieldsMap;
	      if (main_core.Type.isFunction(BX.Bizproc.FieldType.renderControlCollection)) {
	        this.renderedAccessProperties = BX.Bizproc.FieldType.renderControlCollection(documentType, Object.entries(fieldsMap).map(function (_ref3) {
	          var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	            fieldId = _ref4[0],
	            field = _ref4[1];
	          return {
	            property: field,
	            fieldName: field.FieldName,
	            value: _this2.currentAccessValues[fieldId],
	            controlId: fieldId
	          };
	        }), 'designer');
	      }
	    }
	  }, {
	    key: "currentResultType",
	    get: function get() {
	      if (!this.resultTypeSelect) {
	        return 0;
	      }
	      return main_core.Text.toNumber(this.resultTypeSelect.value);
	    }
	  }, {
	    key: "currentAccessType",
	    get: function get() {
	      if (!this.accessTypeSelect) {
	        return 0;
	      }
	      return main_core.Text.toNumber(this.accessTypeSelect.value);
	    }
	  }]);
	  return FixResultActivity;
	}();
	function _renderResultProperty2(fieldId) {
	  var _this3 = this;
	  var _this$resultFieldsMap3 = this.resultFieldsMap[this.currentResultType],
	    documentType = _this$resultFieldsMap3.documentType,
	    fieldsMap = _this$resultFieldsMap3.fieldsMap;
	  var property = fieldsMap[fieldId];
	  if (property.Type === 'mixed') {
	    return _classPrivateMethodGet(this, _createSource, _createSource2).call(this, property);
	  }
	  var fallback = function fallback() {
	    return BX.Bizproc.FieldType.renderControlDesigner(documentType, property, property.FieldName, _this3.currentResultValues[fieldId]);
	  };
	  return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td class=\"adm-detail-content-cell-l\" style=\"text-align: right; vertical-align: middle\" align=\"right\" width=\"25%\">", "</td>\n\t\t\t\t<td width=\"75%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), main_core.Text.encode(property.Name), main_core.Type.isDomNode(this.renderedResultProperties[fieldId]) ? this.renderedResultProperties[fieldId] : fallback());
	}
	function _renderAccessProperty2(fieldId) {
	  var _this4 = this;
	  var _this$accessFieldsMap3 = this.accessFieldsMap[this.currentAccessType],
	    documentType = _this$accessFieldsMap3.documentType,
	    fieldsMap = _this$accessFieldsMap3.fieldsMap;
	  var property = fieldsMap[fieldId];
	  var fallback = function fallback() {
	    return BX.Bizproc.FieldType.renderControlDesigner(documentType, property, property.FieldName, _this4.currentAccessValues[fieldId]);
	  };
	  return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr>\n\t\t\t\t<td class=\"adm-detail-content-cell-l\" style=\"text-align: right; vertical-align: middle\" align=\"right\" width=\"25%\">", "</td>\n\t\t\t\t<td width=\"75%\" class=\"adm-detail-content-cell-r\">\n\t\t\t\t\t", "\n\t\t\t\t</td>\n\t\t\t</tr>\n\t\t"])), main_core.Text.encode(property.Name), main_core.Type.isDomNode(this.renderedAccessProperties[fieldId]) ? this.renderedAccessProperties[fieldId] : fallback());
	}
	function _createSource2(property) {
	  var _this$currentResultVa, _this$currentResultVa2;
	  var source = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<td class=\"adm-detail-content-cell-r\" width=\"75%\"></td>"])));
	  var object = (_this$currentResultVa = this.currentResultValues.ResultItem) === null || _this$currentResultVa === void 0 ? void 0 : _this$currentResultVa.object;
	  var field = (_this$currentResultVa2 = this.currentResultValues.ResultItem) === null || _this$currentResultVa2 === void 0 ? void 0 : _this$currentResultVa2.field;
	  this.selector = new bizproc_mixedSelector.BpMixedSelector({
	    targetNode: source,
	    template: this.template,
	    exceptErrorMessages: true,
	    objectTabs: this.objectTabs,
	    activityFilter: this.activityFilter,
	    inputNames: {
	      object: 'result_item_object',
	      field: 'result_item_field'
	    }
	  });
	  this.selector.renderMixedSelector();
	  if (object && field && this.objectTabs[object] && this.objectTabs[object][field]) {
	    this.selector.setSelectedObjectAndField(object, field, this.objectTabs[object][field].Name);
	  } else {
	    var sourceName = _classPrivateMethodGet(this, _findActivityTitle, _findActivityTitle2).call(this, object, field);
	    if (sourceName) {
	      this.selector.setSelectedObjectAndField(object, field, sourceName);
	    }
	  }
	  var tr = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<tr \n\t\t\t\tdata-object=\"", "\"\n\t\t\t\tdata-field=\"", "\">\n\t\t\t\t<td class=\"adm-detail-content-cell-l\" style=\"text-align: right; vertical-align: middle\" align=\"right\" width=\"25%\">", ":</td>\n\t\t\t\t", "\n\t\t\t</tr>\n\t\t"])), main_core.Text.encode(object !== null && object !== void 0 ? object : ''), main_core.Text.encode(field !== null && field !== void 0 ? field : ''), main_core.Text.encode(property.Name), source);
	  if (this.selector) {
	    this.selector.subscribe('onSelect', function (event) {
	      tr.setAttribute('data-object', event.data.item.object);
	      tr.setAttribute('data-field', event.data.item.field);
	    });
	  }
	  return tr;
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
	namespace.FixResultActivity = FixResultActivity;

}((this.BX.Bizproc.Activity = this.BX.Bizproc.Activity || {}),BX.Bizproc,BX));
//# sourceMappingURL=script.js.map
