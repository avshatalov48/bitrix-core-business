/* eslint-disable */
(function (exports,main_core,bizproc_router,ui_dialogs_messagebox,ui_notification) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc');
	var _gridId = /*#__PURE__*/new WeakMap();
	var _terminateWorkflowRunAction = /*#__PURE__*/new WeakSet();
	var _reloadGrid = /*#__PURE__*/new WeakSet();
	var ScriptQueueDocumentListComponent = /*#__PURE__*/function () {
	  function ScriptQueueDocumentListComponent(options) {
	    babelHelpers.classCallCheck(this, ScriptQueueDocumentListComponent);
	    _classPrivateMethodInitSpec(this, _reloadGrid);
	    _classPrivateMethodInitSpec(this, _terminateWorkflowRunAction);
	    _classPrivateFieldInitSpec(this, _gridId, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isPlainObject(options)) {
	      babelHelpers.classPrivateFieldSet(this, _gridId, options.gridId);
	    }
	  }
	  babelHelpers.createClass(ScriptQueueDocumentListComponent, [{
	    key: "openWorkflowLog",
	    value: function openWorkflowLog(workflowId) {
	      bizproc_router.Router.openWorkflowLog(workflowId);
	    }
	  }, {
	    key: "terminateWorkflow",
	    value: function terminateWorkflow(workflowId) {
	      var _this = this;
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_SCRIPT_QDL_CONFIRM_TERMINATE'), function () {
	        _classPrivateMethodGet(_this, _terminateWorkflowRunAction, _terminateWorkflowRunAction2).call(_this, workflowId);
	        return true;
	      }, main_core.Loc.getMessage('BIZPROC_SCRIPT_QDL_BTN_TERMINATE'));
	    }
	  }]);
	  return ScriptQueueDocumentListComponent;
	}();
	function _terminateWorkflowRunAction2(workflowId) {
	  var _this2 = this;
	  main_core.ajax.runAction('bizproc.workflow.terminate', {
	    data: {
	      workflowId: workflowId
	    }
	  }).then(function () {
	    _classPrivateMethodGet(_this2, _reloadGrid, _reloadGrid2).call(_this2);
	    ui_notification.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('BIZPROC_SCRIPT_QDL_TERMINATE_SUCCESS'),
	      autoHideDelay: 5000
	    });
	  })["catch"](function (response) {
	    response.errors.forEach(function (error) {
	      ui_notification.UI.Notification.Center.notify({
	        content: error.message,
	        autoHideDelay: 5000
	      });
	    });
	  });
	}
	function _reloadGrid2() {
	  if (babelHelpers.classPrivateFieldGet(this, _gridId)) {
	    var grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById(babelHelpers.classPrivateFieldGet(this, _gridId));
	    if (grid) {
	      grid.reload();
	    }
	  }
	}
	namespace.ScriptQueueDocumentListComponent = ScriptQueueDocumentListComponent;

}((this.window = this.window || {}),BX,BX.Bizproc,BX.UI.Dialogs,BX));
//# sourceMappingURL=script.js.map
