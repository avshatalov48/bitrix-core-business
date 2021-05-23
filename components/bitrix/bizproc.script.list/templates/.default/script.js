(function (exports,main_core,ui_dialogs_messagebox,bizproc_script) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Bizproc');

	var ScriptListComponent = /*#__PURE__*/function () {
	  function ScriptListComponent(options) {
	    babelHelpers.classCallCheck(this, ScriptListComponent);

	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      this.createScriptButton = options.createScriptButton;
	      this.documentType = options.documentType;
	    }
	  }

	  babelHelpers.createClass(ScriptListComponent, [{
	    key: "init",
	    value: function init() {
	      var _this = this;

	      if (this.createScriptButton) {
	        main_core.Event.bind(this.createScriptButton, 'click', function () {
	          bizproc_script.Script.Manager.Instance.createScript(_this.documentType).then(function () {
	            return _this.reloadGrid();
	          });
	        });
	      }
	    }
	  }, {
	    key: "deleteScript",
	    value: function deleteScript(scriptId) {
	      var _this2 = this;

	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_SCRIPT_LIST_CONFIRM_DELETE'), function () {
	        bizproc_script.Script.Manager.Instance.deleteScript(scriptId).then(function (response) {
	          if (response.data && response.data.error) {
	            ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	          } else {
	            _this2.reloadGrid();
	          }
	        });
	        return true;
	      }, main_core.Loc.getMessage('BIZPROC_SCRIPT_LIST_BTN_DELETE'));
	    }
	  }, {
	    key: "activateScript",
	    value: function activateScript(scriptId) {
	      var _this3 = this;

	      bizproc_script.Script.Manager.Instance.activateScript(scriptId).then(function () {
	        return _this3.reloadGrid();
	      });
	    }
	  }, {
	    key: "deactivateScript",
	    value: function deactivateScript(scriptId) {
	      var _this4 = this;

	      bizproc_script.Script.Manager.Instance.deactivateScript(scriptId).then(function () {
	        return _this4.reloadGrid();
	      });
	    }
	  }, {
	    key: "editScript",
	    value: function editScript(scriptId) {
	      var _this5 = this;

	      bizproc_script.Script.Manager.Instance.editScript(scriptId).then(function () {
	        return _this5.reloadGrid();
	      });
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
	  return ScriptListComponent;
	}();

	namespace.ScriptListComponent = ScriptListComponent;

}((this.window = this.window || {}),BX,BX.UI.Dialogs,BX.Bizproc));
//# sourceMappingURL=script.js.map
