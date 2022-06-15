this.BX = this.BX || {};
(function (exports,main_core,main_popup,ui_buttons,catalog_storeUse) {
	'use strict';

	var DocumentGridManager = /*#__PURE__*/function () {
	  function DocumentGridManager(options) {
	    babelHelpers.classCallCheck(this, DocumentGridManager);
	    this.gridId = options.gridId;
	    this.filterId = options.filterId;
	    this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	    this.isConductDisabled = options.isConductDisabled;
	    this.masterSliderUrl = options.masterSliderUrl;
	  }

	  babelHelpers.createClass(DocumentGridManager, [{
	    key: "getSelectedIds",
	    value: function getSelectedIds() {
	      return this.grid.getRows().getSelectedIds();
	    }
	  }, {
	    key: "deleteDocument",
	    value: function deleteDocument(documentId) {
	      var _this = this;

	      var popup = new main_popup.Popup({
	        id: 'catalog_delete_document_popup',
	        titleBar: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_TITLE'),
	        content: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_CONTENT'),
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
	          color: ui_buttons.ButtonColor.SUCCESS,
	          onclick: function onclick(button, event) {
	            main_core.ajax.runAction('catalog.document.deleteList', {
	              data: {
	                documentIds: [documentId]
	              }
	            }).then(function (response) {
	              popup.destroy();

	              _this.grid.reload();
	            }).catch(function (response) {
	              if (response.errors) {
	                BX.UI.Notification.Center.notify({
	                  content: response.errors[0].message
	                });
	              }

	              popup.destroy();
	            });
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CANCEL'),
	          color: ui_buttons.ButtonColor.DANGER,
	          onclick: function onclick(button, event) {
	            popup.destroy();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "conductDocument",
	    value: function conductDocument(documentId) {
	      var _this2 = this;

	      var documentType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }

	      var actionConfig = {
	        data: {
	          documentIds: [documentId]
	        }
	      };

	      if (documentType !== '') {
	        actionConfig.analyticsLabel = {
	          documentType: documentType
	        };
	      }

	      var popup = new main_popup.Popup({
	        id: 'catalog_delete_document_popup',
	        titleBar: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_TITLE'),
	        content: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_CONTENT'),
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
	          color: ui_buttons.ButtonColor.SUCCESS,
	          onclick: function onclick(button, event) {
	            main_core.ajax.runAction('catalog.document.conductList', actionConfig).then(function (response) {
	              popup.destroy();

	              _this2.grid.reload();
	            }).catch(function (response) {
	              if (response.errors) {
	                BX.UI.Notification.Center.notify({
	                  content: response.errors[0].message
	                });
	              }

	              popup.destroy();
	            });
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CANCEL'),
	          color: ui_buttons.ButtonColor.DANGER,
	          onclick: function onclick(button, event) {
	            popup.destroy();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "cancelDocument",
	    value: function cancelDocument(documentId) {
	      var _this3 = this;

	      var documentType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }

	      var actionConfig = {
	        data: {
	          documentIds: [documentId]
	        }
	      };

	      if (documentType !== '') {
	        actionConfig.analyticsLabel = {
	          documentType: documentType
	        };
	      }

	      var popup = new main_popup.Popup({
	        id: 'catalog_delete_document_popup',
	        titleBar: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_TITLE'),
	        content: main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_CONTENT'),
	        buttons: [new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CONTINUE'),
	          color: ui_buttons.ButtonColor.SUCCESS,
	          onclick: function onclick(button, event) {
	            main_core.ajax.runAction('catalog.document.cancelList', actionConfig).then(function (response) {
	              popup.destroy();

	              _this3.grid.reload();
	            }).catch(function (response) {
	              if (response.errors) {
	                BX.UI.Notification.Center.notify({
	                  content: response.errors[0].message
	                });
	              }

	              popup.destroy();
	            });
	          }
	        }), new ui_buttons.Button({
	          text: main_core.Loc.getMessage('DOCUMENT_GRID_CANCEL'),
	          color: ui_buttons.ButtonColor.DANGER,
	          onclick: function onclick(button, event) {
	            popup.destroy();
	          }
	        })]
	      });
	      popup.show();
	    }
	  }, {
	    key: "deleteSelectedDocuments",
	    value: function deleteSelectedDocuments() {
	      var _this4 = this;

	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.deleteList', {
	        data: {
	          documentIds: documentIds
	        }
	      }).then(function (response) {
	        _this4.grid.reload();
	      }).catch(function (response) {
	        if (response.errors) {
	          response.errors.forEach(function (error) {
	            if (error.message) {
	              BX.UI.Notification.Center.notify({
	                content: error.message
	              });
	            }
	          });
	        }

	        _this4.grid.reload();
	      });
	    }
	  }, {
	    key: "conductSelectedDocuments",
	    value: function conductSelectedDocuments() {
	      var _this5 = this;

	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }

	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.conductList', {
	        data: {
	          documentIds: documentIds
	        }
	      }).then(function (response) {
	        _this5.grid.reload();
	      }).catch(function (response) {
	        if (response.errors) {
	          response.errors.forEach(function (error) {
	            if (error.message) {
	              BX.UI.Notification.Center.notify({
	                content: error.message
	              });
	            }
	          });
	        }

	        _this5.grid.reload();
	      });
	    }
	  }, {
	    key: "cancelSelectedDocuments",
	    value: function cancelSelectedDocuments() {
	      var _this6 = this;

	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }

	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.cancelList', {
	        data: {
	          documentIds: documentIds
	        }
	      }).then(function (response) {
	        _this6.grid.reload();
	      }).catch(function (response) {
	        if (response.errors) {
	          response.errors.forEach(function (error) {
	            if (error.message) {
	              BX.UI.Notification.Center.notify({
	                content: error.message
	              });
	            }
	          });
	        }

	        _this6.grid.reload();
	      });
	    }
	  }, {
	    key: "processApplyButtonClick",
	    value: function processApplyButtonClick() {
	      var actionValues = this.grid.getActionsPanel().getValues();
	      var selectedAction = actionValues['action_button_' + this.gridId];

	      if (selectedAction === 'conduct') {
	        this.conductSelectedDocuments();
	      }

	      if (selectedAction === 'cancel') {
	        this.cancelSelectedDocuments();
	      }
	    }
	  }, {
	    key: "applyFilter",
	    value: function applyFilter(options) {
	      var filterManager = BX.Main.filterManager.getById(this.filterId);

	      if (!filterManager) {
	        return;
	      }

	      filterManager.getApi().extendFilter(options);
	    }
	  }, {
	    key: "openHowToStart",
	    value: function openHowToStart() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show("redirect=detail&code=14566618");
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToTransfer",
	    value: function openHowToTransfer() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show("redirect=detail&code=14566610");
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToControlGoodsMovement",
	    value: function openHowToControlGoodsMovement() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show("redirect=detail&code=14566670");
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToAccountForLosses",
	    value: function openHowToAccountForLosses() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show("redirect=detail&code=14566652");
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openStoreMasterSlider",
	    value: function openStoreMasterSlider() {
	      new catalog_storeUse.Slider().open(this.masterSliderUrl, {
	        data: {
	          openGridOnDone: false
	        },
	        events: {
	          onCloseComplete: function onCloseComplete(event) {
	            var slider = event.getSlider();

	            if (!slider) {
	              return;
	            }

	            if (slider.getData().get('isInventoryManagementEnabled')) {
	              document.location.reload();
	            }
	          }
	        }
	      });
	    }
	  }]);
	  return DocumentGridManager;
	}();

	exports.DocumentGridManager = DocumentGridManager;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Main,BX.UI,BX.Catalog.StoreUse));
//# sourceMappingURL=document-grid.bundle.js.map
