/* eslint-disable */
(function (exports,main_core,ui_notification,ui_dialogs_messagebox) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var _gridId = /*#__PURE__*/new WeakMap();
	var _getGrid = /*#__PURE__*/new WeakSet();
	var _removeGridRow = /*#__PURE__*/new WeakSet();
	var _showNotification = /*#__PURE__*/new WeakSet();
	var WorkflowInstances = /*#__PURE__*/function () {
	  function WorkflowInstances(options) {
	    babelHelpers.classCallCheck(this, WorkflowInstances);
	    _classPrivateMethodInitSpec(this, _showNotification);
	    _classPrivateMethodInitSpec(this, _removeGridRow);
	    _classPrivateMethodInitSpec(this, _getGrid);
	    _classPrivateFieldInitSpec(this, _gridId, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isPlainObject(options)) {
	      babelHelpers.classPrivateFieldSet(this, _gridId, options.gridId);
	    }
	  }
	  babelHelpers.createClass(WorkflowInstances, [{
	    key: "deleteItem",
	    value: function deleteItem(workflowId) {
	      var _this = this;
	      var messageBox = new ui_dialogs_messagebox.MessageBox({
	        message: main_core.Loc.getMessage('BPWI_DELETE_MESS_CONFIRM'),
	        okCaption: main_core.Loc.getMessage('BPWI_DELETE_BTN_LABEL'),
	        onOk: function onOk() {
	          _classPrivateMethodGet(_this, _removeGridRow, _removeGridRow2).call(_this, workflowId);
	          _classPrivateMethodGet(_this, _showNotification, _showNotification2).call(_this, {
	            content: main_core.Loc.getMessage('BPWIT_DELETE_NOTIFICATION')
	          });
	          return true;
	        },
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	        popupOptions: {
	          events: {
	            onAfterShow: function onAfterShow(event) {
	              var okBtn = event.getTarget().getButton('ok');
	              if (okBtn) {
	                okBtn.getContainer().focus();
	              }
	            }
	          }
	        }
	      });
	      messageBox.show();
	    }
	  }, {
	    key: "terminateItem",
	    value: function terminateItem(workflowId) {
	      var _this2 = this;
	      main_core.ajax.runAction('bizproc.workflow.terminate', {
	        data: {
	          workflowId: workflowId
	        }
	      }).then(function () {
	        _classPrivateMethodGet(_this2, _removeGridRow, _removeGridRow2).call(_this2, workflowId);
	        _classPrivateMethodGet(_this2, _showNotification, _showNotification2).call(_this2, {
	          content: main_core.Loc.getMessage('BPWIT_TERMINATE_NOTIFICATION')
	        });
	      })["catch"](function (response) {
	        response.errors.forEach(function (error) {
	          _classPrivateMethodGet(_this2, _showNotification, _showNotification2).call(_this2, {
	            content: error.message
	          });
	        });
	      });
	    }
	  }, {
	    key: "logItem",
	    value: function logItem(workflowId) {
	      main_core.Runtime.loadExtension('bizproc.router').then(function (_ref) {
	        var Router = _ref.Router;
	        Router.openWorkflowLog(workflowId);
	      })["catch"](function (e) {
	        return console.error(e);
	      });
	    }
	  }]);
	  return WorkflowInstances;
	}();
	function _getGrid2() {
	  if (babelHelpers.classPrivateFieldGet(this, _gridId)) {
	    return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(babelHelpers.classPrivateFieldGet(this, _gridId));
	  }
	  return null;
	}
	function _removeGridRow2(workflowId) {
	  var grid = _classPrivateMethodGet(this, _getGrid, _getGrid2).call(this);
	  if (grid) {
	    grid.removeRow(workflowId);
	  }
	}
	function _showNotification2(notificationOptions) {
	  var defaultSettings = {
	    autoHideDelay: 5000
	  };
	  ui_notification.UI.Notification.Center.notify(Object.assign(defaultSettings, notificationOptions));
	}
	namespace.WorkflowInstances = WorkflowInstances;

}((this.window = this.window || {}),BX,BX,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
