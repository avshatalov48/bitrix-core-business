/* eslint-disable */
(function (exports,main_core,ui_alerts,bizproc_debugger) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var DebuggerSessionList = /*#__PURE__*/function () {
	  function DebuggerSessionList(options) {
	    babelHelpers.classCallCheck(this, DebuggerSessionList);
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      this.createDebuggerSessionButton = options.createDebuggerSessionButton;
	      this.errorsContainerDiv = options.errorsContainerDiv;
	      this.documentSigned = options.documentSigned;
	      this.signedParameters = options.signedParameters;
	    }
	  }
	  babelHelpers.createClass(DebuggerSessionList, [{
	    key: "init",
	    value: function init() {
	      var _this = this;
	      if (this.createDebuggerSessionButton) {
	        main_core.Event.bind(this.createDebuggerSessionButton, 'click', function (event) {
	          return _this.createSession();
	        });
	      }
	    }
	  }, {
	    key: "createSession",
	    value: function createSession() {
	      bizproc_debugger.Manager.Instance.openDebuggerStartPage(this.documentSigned, {
	        analyticsStartType: 'session_list'
	      }).then();
	    }
	  }, {
	    key: "showSession",
	    value: function showSession(sessionId) {
	      bizproc_debugger.Manager.Instance.openSessionLog(sessionId).then();
	    }
	  }, {
	    key: "renameSession",
	    value: function renameSession(sessionId) {
	      var _grid$getRows$getById;
	      var grid = this.getGrid();
	      (_grid$getRows$getById = grid.getRows().getById(sessionId)) === null || _grid$getRows$getById === void 0 ? void 0 : _grid$getRows$getById.select();
	      grid.getActionsPanel().getPanel().querySelector('#grid_edit_button > .edit').click();
	      grid.enableActionsPanel();
	      grid.getPinPanel().pinPanel(true);
	    }
	  }, {
	    key: "deleteChosenSessions",
	    value: function deleteChosenSessions() {
	      var grid = this.getGrid();
	      if (grid) {
	        this.deleteSessions(grid.getRows().getSelectedIds());
	      }
	    }
	  }, {
	    key: "deleteSessions",
	    value: function deleteSessions(sessionIds) {
	      var _this2 = this;
	      main_core.ajax.runComponentAction('bitrix:bizproc.debugger.session.list', 'deleteSessions', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          sessionIds: sessionIds
	        }
	      }).then(function (response) {
	        _this2.reloadGrid();
	      })["catch"](function (response) {
	        _this2.showErrors(response.errors);
	      });
	    }
	  }, {
	    key: "showErrors",
	    value: function showErrors(errors) {
	      var _this3 = this;
	      this.errorsContainerDiv.style.margin = '10px';
	      errors.forEach(function (error) {
	        var alert = new ui_alerts.Alert({
	          text: error.message,
	          color: ui_alerts.AlertColor.DANGER,
	          closeBtn: true,
	          animated: true
	        });
	        alert.renderTo(_this3.errorsContainerDiv);
	      });
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var grid = this.getGrid();
	      if (grid) {
	        grid.reload();
	      }
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (this.gridId) {
	        return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
	      }
	      return null;
	    }
	  }]);
	  return DebuggerSessionList;
	}();
	namespace.DebuggerSessionList = DebuggerSessionList;

}((this.window = this.window || {}),BX,BX.UI,BX.Bizproc.Debugger));
//# sourceMappingURL=script.js.map
