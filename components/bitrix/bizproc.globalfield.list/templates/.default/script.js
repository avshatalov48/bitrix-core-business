(function (exports,main_core,ui_dialogs_messagebox,bizproc_globals) {
	'use strict';

	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');

	var GlobalFieldListComponent = /*#__PURE__*/function () {
	  function GlobalFieldListComponent(options) {
	    babelHelpers.classCallCheck(this, GlobalFieldListComponent);

	    if (main_core.Type.isPlainObject(options)) {
	      this.componentName = options.componentName;
	      this.signedParameters = options.signedParameters;
	      this.gridId = options.gridId;
	      this.signedDocumentType = options.signedDocumentType;
	      this.mode = options.mode;
	      this.slider = options.slider;
	    }
	  }

	  babelHelpers.createClass(GlobalFieldListComponent, [{
	    key: "init",
	    value: function init() {
	      this.sliderDict = this.slider ? this.slider.getData() : null;
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (this.gridId) {
	        return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(this.gridId);
	      }

	      return null;
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
	    key: "onCreateButtonClick",
	    value: function onCreateButtonClick() {
	      var me = this;
	      bizproc_globals.Globals.Manager.Instance.createGlobals(this.mode, this.signedDocumentType).then(function (slider) {
	        return me.onAfterUpsert(slider);
	      });
	    }
	  }, {
	    key: "editGlobalFieldAction",
	    value: function editGlobalFieldAction(id, mode) {
	      var me = this;
	      bizproc_globals.Globals.Manager.Instance.editGlobals(id, mode, this.signedDocumentType).then(function (slider) {
	        return me.onAfterUpsert(slider);
	      });
	    }
	  }, {
	    key: "onAfterUpsert",
	    value: function onAfterUpsert(slider) {
	      var info = slider.getData().entries();
	      var keys = Object.keys(info);

	      if (keys.length <= 0) {
	        return;
	      }

	      if (this.sliderDict) {
	        var _this$sliderDict$get;

	        var items = (_this$sliderDict$get = this.sliderDict.get('upsert')) !== null && _this$sliderDict$get !== void 0 ? _this$sliderDict$get : {};
	        items[keys[0]] = info[keys[0]];
	        this.sliderDict.set('upsert', items);
	      }

	      this.reloadGrid();
	    }
	  }, {
	    key: "getDeletePhrase",
	    value: function getDeletePhrase(mode) {
	      if (mode === bizproc_globals.Globals.Manager.Instance.mode.variable) {
	        return main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_VARIABLE_DELETE');
	      } else if (mode === bizproc_globals.Globals.Manager.Instance.mode.constant) {
	        return main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_CONSTANT_DELETE');
	      } else {
	        return '';
	      }
	    }
	  }, {
	    key: "getPluralDeletePhrase",
	    value: function getPluralDeletePhrase(mode) {
	      if (mode === bizproc_globals.Globals.Manager.Instance.mode.variable) {
	        return main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_VARIABLES_DELETE');
	      } else if (mode === bizproc_globals.Globals.Manager.Instance.mode.constant) {
	        return main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_CONFIRM_CONSTANTS_DELETE');
	      } else {
	        return '';
	      }
	    }
	  }, {
	    key: "deleteGlobalFieldAction",
	    value: function deleteGlobalFieldAction(id, mode) {
	      var me = this;
	      var message = this.getDeletePhrase(mode);
	      new ui_dialogs_messagebox.MessageBox({
	        message: message,
	        okCaption: main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_BTN_DELETE'),
	        onOk: function onOk() {
	          bizproc_globals.Globals.Manager.Instance.deleteGlobalsAction(id, mode, me.signedDocumentType).then(function (response) {
	            if (response.data && response.data.error) {
	              ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	            } else {
	              if (me.sliderDict) {
	                var _me$sliderDict$get;

	                var items = (_me$sliderDict$get = me.sliderDict.get('delete')) !== null && _me$sliderDict$get !== void 0 ? _me$sliderDict$get : [];
	                items.push(id);
	                me.sliderDict.set('delete', items);
	              }

	              me.reloadGrid();
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
	      }).show();
	    }
	  }, {
	    key: "deleteFieldsAction",
	    value: function deleteFieldsAction(mode) {
	      var _this = this;

	      var me = this;
	      var message = this.getPluralDeletePhrase(mode);
	      new ui_dialogs_messagebox.MessageBox({
	        message: message,
	        okCaption: main_core.Loc.getMessage('BIZPROC_GLOBALFIELDS_LIST_BTN_DELETE'),
	        onOk: function onOk() {
	          BX.ajax.runComponentAction(me.componentName, 'processGridDelete', {
	            mode: 'class',
	            data: {
	              signedParameters: _this.signedParameters,
	              documentType: _this.signedDocumentType,
	              mode: mode,
	              ids: _this.getGrid().getRows().getSelectedIds()
	            }
	          }).then(function (response) {
	            if (response.data && response.data.error) {
	              ui_dialogs_messagebox.MessageBox.alert(response.data.error);
	            } else {
	              if (me.sliderDict) {
	                var _me$sliderDict$get2;

	                var items = (_me$sliderDict$get2 = me.sliderDict.get('delete')) !== null && _me$sliderDict$get2 !== void 0 ? _me$sliderDict$get2 : [];

	                _this.getGrid().getRows().getSelectedIds().forEach(function (id) {
	                  items.push(id);
	                });

	                me.sliderDict.set('delete', items);
	              }

	              me.reloadGrid();
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
	      }).show();
	    }
	  }]);
	  return GlobalFieldListComponent;
	}();

	namespace.GlobalFieldListComponent = GlobalFieldListComponent;

}((this.window = this.window || {}),BX,BX.UI.Dialogs,BX.Bizproc));
//# sourceMappingURL=script.js.map
