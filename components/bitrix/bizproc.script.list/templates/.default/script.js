/* eslint-disable */
(function (exports,main_core,ui_dialogs_messagebox,bizproc_script) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc');
	var _getGrid = /*#__PURE__*/new WeakSet();
	var _disableExport = /*#__PURE__*/new WeakSet();
	var _enableExport = /*#__PURE__*/new WeakSet();
	var ScriptListComponent = /*#__PURE__*/function () {
	  function ScriptListComponent(options) {
	    babelHelpers.classCallCheck(this, ScriptListComponent);
	    _classPrivateMethodInitSpec(this, _enableExport);
	    _classPrivateMethodInitSpec(this, _disableExport);
	    _classPrivateMethodInitSpec(this, _getGrid);
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      this.createScriptButton = options.createScriptButton;
	      this.exportScriptButton = options.exportScriptButton;
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
	      if (this.exportScriptButton) {
	        main_core.Event.bind(this.exportScriptButton, 'click', function (event) {
	          if (!main_core.Dom.hasClass(_this.exportScriptButton, 'ui-btn-disabled')) {
	            BX.SidePanel.Instance.open(_this.exportScriptButton.getAttribute('data-url'));
	          }
	        });
	        if (!this.hasRows()) {
	          _classPrivateMethodGet(this, _disableExport, _disableExport2).call(this);
	        }
	      }
	      BX.addCustomEvent('Grid::updated', function () {
	        if (!_this.hasRows()) {
	          _classPrivateMethodGet(_this, _disableExport, _disableExport2).call(_this);
	        } else {
	          _classPrivateMethodGet(_this, _enableExport, _enableExport2).call(_this);
	        }
	      });
	    }
	  }, {
	    key: "deleteScript",
	    value: function deleteScript(scriptId) {
	      var _this2 = this;
	      var messageBox = new ui_dialogs_messagebox.MessageBox({
	        message: main_core.Loc.getMessage('BIZPROC_SCRIPT_LIST_CONFIRM_DELETE'),
	        okCaption: main_core.Loc.getMessage('BIZPROC_SCRIPT_LIST_BTN_DELETE'),
	        onOk: function onOk() {
	          bizproc_script.Script.Manager.Instance.deleteScript(scriptId).then(function (response) {
	            if (response.data && response.data.error) {
	              ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	            } else {
	              _this2.reloadGrid();
	            }
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
	    key: "activateScript",
	    value: function activateScript(scriptId) {
	      var _this3 = this;
	      bizproc_script.Script.Manager.Instance.activateScript(scriptId).then(function (response) {
	        if (response.data.error) {
	          ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	        } else {
	          _this3.reloadGrid();
	        }
	      });
	    }
	  }, {
	    key: "deactivateScript",
	    value: function deactivateScript(scriptId) {
	      var _this4 = this;
	      bizproc_script.Script.Manager.Instance.deactivateScript(scriptId).then(function (response) {
	        if (response.data.error) {
	          ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	        } else {
	          _this4.reloadGrid();
	        }
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
	      var grid = _classPrivateMethodGet(this, _getGrid, _getGrid2).call(this);
	      if (grid) {
	        grid.reload();
	      }
	    }
	  }, {
	    key: "hasRows",
	    value: function hasRows() {
	      var grid = _classPrivateMethodGet(this, _getGrid, _getGrid2).call(this);
	      if (grid) {
	        return grid.getRows().getCountDisplayed() > 0;
	      }
	      return false;
	    }
	  }]);
	  return ScriptListComponent;
	}();
	function _getGrid2() {
	  if (this.gridId) {
	    return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
	  }
	  return null;
	}
	function _disableExport2() {
	  if (this.exportScriptButton) {
	    main_core.Dom.addClass(this.exportScriptButton, 'ui-btn-disabled');
	  }
	}
	function _enableExport2() {
	  if (this.exportScriptButton) {
	    main_core.Dom.removeClass(this.exportScriptButton, 'ui-btn-disabled');
	  }
	}
	namespace.ScriptListComponent = ScriptListComponent;

}((this.window = this.window || {}),BX,BX.UI.Dialogs,BX.Bizproc));
//# sourceMappingURL=script.js.map
