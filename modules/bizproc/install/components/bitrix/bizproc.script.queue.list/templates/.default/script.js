/* eslint-disable */
(function (exports,main_core,ui_dialogs_messagebox,bizproc_script) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Bizproc');
	var ScriptQueueListComponent = /*#__PURE__*/function () {
	  function ScriptQueueListComponent(options) {
	    babelHelpers.classCallCheck(this, ScriptQueueListComponent);
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	    }
	  }
	  babelHelpers.createClass(ScriptQueueListComponent, [{
	    key: "deleteQueue",
	    value: function deleteQueue(queueId) {
	      var _this = this;
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_CONFIRM_DELETE'), function () {
	        bizproc_script.Script.Manager.Instance.deleteScriptQueue(queueId);
	        _this.reloadGrid();
	        return true;
	      }, main_core.Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_BTN_DELETE'));
	    }
	  }, {
	    key: "terminateQueue",
	    value: function terminateQueue(queueId) {
	      var _this2 = this;
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_CONFIRM_TERMINATE'), function () {
	        bizproc_script.Script.Manager.Instance.terminateScriptQueue(queueId);
	        _this2.reloadGrid();
	        return true;
	      }, main_core.Loc.getMessage('BIZPROC_SCRIPT_QUEUE_LIST_BTN_TERMINATE'));
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      if (this.gridId) {
	        var grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
	        if (grid) {
	          grid.reload();
	        }
	      }
	    }
	  }]);
	  return ScriptQueueListComponent;
	}();
	namespace.ScriptQueueListComponent = ScriptQueueListComponent;

}((this.window = this.window || {}),BX,BX.UI.Dialogs,BX.Bizproc));
//# sourceMappingURL=script.js.map
