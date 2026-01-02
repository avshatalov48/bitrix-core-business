/* eslint-disable */
(function (exports,main_core,catalog_entityEditor_field_productset,catalog_entityEditor_field_sectionset,catalog_entityEditor_field_contractor,catalog_agentContract) {
	'use strict';

	var _templateObject;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Catalog.Agent.ContractorComponent');
	var _appendEditButton = /*#__PURE__*/new WeakSet();
	var Detail = /*#__PURE__*/function () {
	  function Detail() {
	    babelHelpers.classCallCheck(this, Detail);
	    _classPrivateMethodInitSpec(this, _appendEditButton);
	    _classPrivateMethodGet(this, _appendEditButton, _appendEditButton2).call(this);
	  }
	  babelHelpers.createClass(Detail, null, [{
	    key: "registerFieldFactory",
	    value: function registerFieldFactory(entityEditorControlFactory) {
	      new catalog_entityEditor_field_productset.ProductSetFieldFactory(entityEditorControlFactory);
	      new catalog_entityEditor_field_sectionset.SectionSetFieldFactory(entityEditorControlFactory);
	      new catalog_entityEditor_field_contractor.ContractorFieldFactory(entityEditorControlFactory);
	    }
	  }, {
	    key: "registerControllerFactory",
	    value: function registerControllerFactory(entityEditorControllerFactory) {
	      new catalog_agentContract.ControllersFactory(entityEditorControllerFactory);
	    }
	  }, {
	    key: "registerModelFactory",
	    value: function registerModelFactory() {
	      new catalog_agentContract.ModelFactory();
	    }
	  }]);
	  return Detail;
	}();
	function _appendEditButton2() {
	  var _BX$UI, _BX$UI$ToolbarManager;
	  var toolbar = (_BX$UI = BX.UI) === null || _BX$UI === void 0 ? void 0 : (_BX$UI$ToolbarManager = _BX$UI.ToolbarManager) === null || _BX$UI$ToolbarManager === void 0 ? void 0 : _BX$UI$ToolbarManager.getDefaultToolbar();
	  var titleContainer = toolbar === null || toolbar === void 0 ? void 0 : toolbar.titleContainer.querySelector('.ui-toolbar-title-item-box');
	  if (!titleContainer) {
	    return;
	  }
	  var editButton = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span id=\"pagetitle_btn_wrapper\" class=\"pagetitile-button-container\">\n\t\t\t\t<span id=\"pagetitle_edit\" class=\"pagetitle-edit-button\"></span>\n\t\t\t</span>\n\t\t"])));
	  main_core.Dom.append(editButton, titleContainer);
	}
	namespace.Detail = Detail;

}((this.window = this.window || {}),BX,BX.Catalog.EntityEditor.Field,BX.Catalog.EntityEditor.Field,BX.Catalog.EntityEditor.Field,BX.Catalog));
//# sourceMappingURL=script.js.map
