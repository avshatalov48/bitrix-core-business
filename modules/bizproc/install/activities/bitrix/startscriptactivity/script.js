/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,ui_entitySelector) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Activity');
	var _templateNode = /*#__PURE__*/new WeakMap();
	var _templateInput = /*#__PURE__*/new WeakMap();
	var _templateId = /*#__PURE__*/new WeakMap();
	var _parametersNode = /*#__PURE__*/new WeakMap();
	var _documentType = /*#__PURE__*/new WeakMap();
	var _formName = /*#__PURE__*/new WeakMap();
	var _isRobot = /*#__PURE__*/new WeakMap();
	var _initTemplateSelector = /*#__PURE__*/new WeakSet();
	var _renderTemplateParameters = /*#__PURE__*/new WeakSet();
	var StartScriptActivity = /*#__PURE__*/function () {
	  function StartScriptActivity(options) {
	    babelHelpers.classCallCheck(this, StartScriptActivity);
	    _classPrivateMethodInitSpec(this, _renderTemplateParameters);
	    _classPrivateMethodInitSpec(this, _initTemplateSelector);
	    _classPrivateFieldInitSpec(this, _templateNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateInput, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateId, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(this, _parametersNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _formName, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isRobot, {
	      writable: true,
	      value: false
	    });
	    if (!main_core.Type.isElementNode(options.templateNode)) {
	      throw 'templateNode must be HTML Element';
	    }
	    babelHelpers.classPrivateFieldSet(this, _templateNode, options.templateNode);
	    if (!main_core.Type.isElementNode(options.templateInput)) {
	      throw 'templateInput must be HTML Input Element';
	    }
	    babelHelpers.classPrivateFieldSet(this, _templateInput, options.templateInput);
	    if (!main_core.Type.isElementNode(options.parametersNode)) {
	      throw 'parametersNode must be HTML Element';
	    }
	    babelHelpers.classPrivateFieldSet(this, _parametersNode, options.parametersNode);
	    var _templateId2 = main_core.Text.toInteger(options.templateId);
	    if (_templateId2 > 0) {
	      babelHelpers.classPrivateFieldSet(this, _templateId, _templateId2);
	    }
	    babelHelpers.classPrivateFieldSet(this, _documentType, main_core.Type.isArrayFilled(options.documentType) ? options.documentType : []);
	    babelHelpers.classPrivateFieldSet(this, _formName, main_core.Type.isStringFilled(options.formName) ? options.formName : '');
	    babelHelpers.classPrivateFieldSet(this, _isRobot, main_core.Type.isBoolean(options.isRobot) ? options.isRobot : false);
	  }
	  babelHelpers.createClass(StartScriptActivity, [{
	    key: "init",
	    value: function init() {
	      _classPrivateMethodGet(this, _initTemplateSelector, _initTemplateSelector2).call(this);
	    }
	  }]);
	  return StartScriptActivity;
	}();
	function _initTemplateSelector2() {
	  var _this = this;
	  var preselectedItems = [];
	  if (babelHelpers.classPrivateFieldGet(this, _templateId)) {
	    preselectedItems.push(['bizproc-script-template', babelHelpers.classPrivateFieldGet(this, _templateId)]);
	  }
	  var selector = new ui_entitySelector.TagSelector({
	    dialogOptions: {
	      entities: [{
	        id: 'bizproc-script-template'
	      }],
	      multiple: false,
	      dropdownMode: true,
	      enableSearch: true,
	      hideOnSelect: true,
	      hideOnDeselect: false,
	      clearSearchOnSelect: true,
	      showAvatars: false,
	      compactView: true,
	      height: 300,
	      preselectedItems: preselectedItems,
	      events: {
	        'Item:onSelect': function ItemOnSelect(event) {
	          var _event$getData = event.getData(),
	            selectedItem = _event$getData.item;
	          _classPrivateMethodGet(_this, _renderTemplateParameters, _renderTemplateParameters2).call(_this, selectedItem.getId());
	          babelHelpers.classPrivateFieldGet(_this, _templateInput).value = selectedItem.getId();
	        },
	        'Item:onDeselect': function ItemOnDeselect() {
	          _classPrivateMethodGet(_this, _renderTemplateParameters, _renderTemplateParameters2).call(_this, -1);
	          babelHelpers.classPrivateFieldGet(_this, _templateInput).value = '';
	        }
	      }
	    },
	    multiple: false,
	    tagMaxWidth: 500
	  });
	  selector.renderTo(babelHelpers.classPrivateFieldGet(this, _templateNode));
	}
	function _renderTemplateParameters2(templateId) {
	  var _this2 = this;
	  babelHelpers.classPrivateFieldGet(this, _parametersNode).innerHTML = '';
	  templateId = main_core.Text.toInteger(templateId);
	  if (templateId <= 0) {
	    return;
	  }
	  main_core.ajax.runAction('bizproc.activity.request', {
	    data: {
	      documentType: babelHelpers.classPrivateFieldGet(this, _documentType),
	      activity: 'StartScriptActivity',
	      params: {
	        template_id: templateId,
	        form_name: babelHelpers.classPrivateFieldGet(this, _formName),
	        document_type: babelHelpers.classPrivateFieldGet(this, _documentType)
	      }
	    }
	  }).then(function (response) {
	    babelHelpers.classPrivateFieldGet(_this2, _parametersNode).innerHTML = response.data;
	  });
	}
	namespace.StartScriptActivity = StartScriptActivity;

}((this.BX.Bizproc.Activity = this.BX.Bizproc.Activity || {}),BX,BX.UI.EntitySelector));
//# sourceMappingURL=script.js.map
