/* eslint-disable */
(function (exports,main_core,catalog_agentContract) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Catalog.Component');
	var AgentContractList = /*#__PURE__*/function () {
	  function AgentContractList() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, AgentContractList);
	    babelHelpers.defineProperty(this, "grid", null);
	    babelHelpers.defineProperty(this, "gridActions", null);
	    babelHelpers.defineProperty(this, "createUrl", null);
	    this.gridId = options.gridId;
	    this.createUrl = options.createUrl;
	    if (BX.Main.gridManager) {
	      this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	    }
	    this.gridActions = new catalog_agentContract.GridActions({
	      grid: this.grid
	    });
	    this.sliderOptions = {
	      allowChangeHistory: false,
	      cacheable: false,
	      width: 650
	    };
	  }
	  babelHelpers.createClass(AgentContractList, [{
	    key: "open",
	    value: function open(url) {
	      BX.SidePanel.Instance.open(url, this.sliderOptions);
	    }
	  }, {
	    key: "create",
	    value: function create() {
	      BX.SidePanel.Instance.open(this.createUrl, this.sliderOptions);
	    }
	  }, {
	    key: "delete",
	    value: function _delete(id) {
	      this.gridActions["delete"](id);
	    }
	  }, {
	    key: "deleteList",
	    value: function deleteList() {
	      var ids = this.grid.getRows().getSelectedIds();
	      if (ids && ids.length > 0) {
	        this.gridActions.deleteList(ids);
	      }
	    }
	  }], [{
	    key: "openHelpDesk",
	    value: function openHelpDesk() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=17917894');
	        event.preventDefault();
	      }
	    }
	  }]);
	  return AgentContractList;
	}();
	namespace.AgentContractList = AgentContractList;

}((this.window = this.window || {}),BX,BX.Catalog));
//# sourceMappingURL=script.js.map
