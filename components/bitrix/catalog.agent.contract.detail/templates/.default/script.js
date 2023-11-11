/* eslint-disable */
(function (exports,main_core,catalog_entityEditor_field_productset,catalog_entityEditor_field_sectionset,catalog_entityEditor_field_contractor,catalog_agentContract) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Catalog.Agent.ContractorComponent');
	var Detail = /*#__PURE__*/function () {
	  function Detail() {
	    babelHelpers.classCallCheck(this, Detail);
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
	namespace.Detail = Detail;

}((this.window = this.window || {}),BX,BX.Catalog.EntityEditor.Field,BX.Catalog.EntityEditor.Field,BX.Catalog.EntityEditor.Field,BX.Catalog));
//# sourceMappingURL=script.js.map
