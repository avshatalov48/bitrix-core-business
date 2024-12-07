/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_popup,ui_dialogs_messagebox,catalog_storeEnableWizard) {
	'use strict';

	var _templateObject, _templateObject2;
	var DocumentGridManager = /*#__PURE__*/function () {
	  function DocumentGridManager(options) {
	    babelHelpers.classCallCheck(this, DocumentGridManager);
	    this.gridId = options.gridId;
	    this.filterId = options.filterId;
	    this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
	    this.isConductDisabled = options.isConductDisabled;
	    this.masterSliderUrl = options.masterSliderUrl;
	    this.isInventoryManagementDisabled = options.isInventoryManagementDisabled;
	    this.inventoryManagementFeatureCode = options.inventoryManagementFeatureCode;
	    this.inventoryManagementSource = options.inventoryManagementSource;
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
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_CONTENT_2'), function (messageBox, button) {
	        button.setWaiting();
	        main_core.ajax.runAction('catalog.document.deleteList', {
	          data: {
	            documentIds: [documentId]
	          },
	          analyticsLabel: {
	            inventoryManagementSource: _this.inventoryManagementSource
	          }
	        }).then(function () {
	          messageBox.close();
	          _this.grid.reload();
	        })["catch"](function (response) {
	          if (response.errors) {
	            BX.UI.Notification.Center.notify({
	              content: response.errors[0].message
	            });
	          }
	          messageBox.close();
	        });
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_DELETE_BUTTON_CONFIRM'), function (messageBox) {
	        return messageBox.close();
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'));
	    }
	  }, {
	    key: "conductDocument",
	    value: function conductDocument(documentId) {
	      var _this2 = this;
	      var documentType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
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
	      actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;
	      actionConfig.analyticsLabel.mode = 'single';
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_CONTENT_2'), function (messageBox, button) {
	        button.setWaiting();
	        main_core.ajax.runAction('catalog.document.conductList', actionConfig).then(function () {
	          messageBox.close();
	          _this2.grid.reload();
	        })["catch"](function (response) {
	          if (response.errors) {
	            BX.UI.Notification.Center.notify({
	              content: response.errors[0].message
	            });
	          }
	          messageBox.close();
	        });
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CONDUCT_BUTTON_CONFIRM'), function (messageBox) {
	        return messageBox.close();
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'));
	    }
	  }, {
	    key: "cancelDocument",
	    value: function cancelDocument(documentId) {
	      var _this3 = this;
	      var documentType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }
	      var settings = main_core.Extension.getSettings('catalog.document-grid');
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
	      actionConfig.analyticsLabel.mode = 'single';
	      actionConfig.analyticsLabel.inventoryManagementSource = this.inventoryManagementSource;
	      var content = main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_CONTENT_2');
	      if (settings.get('isProductBatchMethodSelected')) {
	        var text = main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_BATCH_SELECTED_CONTENT', {
	          '#HELP_LINK#': '<help-link></help-link>'
	        });
	        content = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div>\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t\t<div>", "</div>\n\t\t\t\t</div>\n\t\t\t"])), content, text);
	        var moreLink = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"#\" class=\"ui-form-link\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t"])), main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_BATCH_SELECTED_CONTENT_LINK'));
	        main_core.Event.bind(moreLink, 'click', function () {
	          var articleId = 17858278;
	          top.BX.Helper.show("redirect=detail&code=".concat(articleId));
	        });
	        main_core.Dom.replace(content.querySelector('help-link'), moreLink);
	      }
	      ui_dialogs_messagebox.MessageBox.confirm(content, function (messageBox, button) {
	        button.setWaiting();
	        main_core.ajax.runAction('catalog.document.cancelList', actionConfig).then(function () {
	          messageBox.close();
	          _this3.grid.reload();
	        })["catch"](function (response) {
	          if (response.errors) {
	            BX.UI.Notification.Center.notify({
	              content: response.errors[0].message
	            });
	          }
	          messageBox.close();
	        });
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_DOCUMENT_CANCEL_BUTTON_CONFIRM'), function (messageBox) {
	        return messageBox.close();
	      }, main_core.Loc.getMessage('DOCUMENT_GRID_BUTTON_BACK'));
	    }
	  }, {
	    key: "deleteSelectedDocuments",
	    value: function deleteSelectedDocuments() {
	      var _this4 = this;
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.deleteList', {
	        data: {
	          documentIds: documentIds
	        },
	        analyticsLabel: {
	          inventoryManagementSource: this.inventoryManagementSource
	        }
	      }).then(function (response) {
	        _this4.grid.reload();
	      })["catch"](function (response) {
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
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }
	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.conductList', {
	        data: {
	          documentIds: documentIds
	        },
	        analyticsLabel: {
	          mode: 'list',
	          inventoryManagementSource: this.inventoryManagementSource
	        }
	      }).then(function (response) {
	        _this5.grid.reload();
	      })["catch"](function (response) {
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
	      if (this.isInventoryManagementDisabled && this.inventoryManagementFeatureCode) {
	        top.BX.UI.InfoHelper.show(this.inventoryManagementFeatureCode);
	        return;
	      }
	      if (this.isConductDisabled) {
	        this.openStoreMasterSlider();
	        return;
	      }
	      var documentIds = this.getSelectedIds();
	      main_core.ajax.runAction('catalog.document.cancelList', {
	        data: {
	          documentIds: documentIds
	        },
	        analyticsLabel: {
	          mode: 'list',
	          inventoryManagementSource: this.inventoryManagementSource
	        }
	      }).then(function (response) {
	        _this6.grid.reload();
	      })["catch"](function (response) {
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
	      var selectedAction = actionValues["action_button_".concat(this.gridId)];
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
	        top.BX.Helper.show('redirect=detail&code=14566618');
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToTransfer",
	    value: function openHowToTransfer() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=14566610');
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToControlGoodsMovement",
	    value: function openHowToControlGoodsMovement() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=14566670');
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openHowToAccountForLosses",
	    value: function openHowToAccountForLosses() {
	      if (top.BX.Helper) {
	        top.BX.Helper.show('redirect=detail&code=14566652');
	        event.preventDefault();
	      }
	    }
	  }, {
	    key: "openStoreMasterSlider",
	    value: function openStoreMasterSlider() {
	      new catalog_storeEnableWizard.EnableWizardOpener().open(this.masterSliderUrl, {
	        urlParams: {
	          analyticsContextSection: catalog_storeEnableWizard.AnalyticsContextList.DOCUMENT_LIST
	        },
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
	  }], [{
	    key: "hideSettingsMenu",
	    value: function hideSettingsMenu() {
	      main_popup.PopupManager.getPopupById('docFieldsSettingsMenu').close();
	    }
	  }, {
	    key: "openUfSlider",
	    value: function openUfSlider(e, item) {
	      e.preventDefault();
	      DocumentGridManager.hideSettingsMenu();
	      BX.SidePanel.Instance.open(item.options.href, {
	        allowChangeHistory: false,
	        cacheable: false
	      });
	    }
	  }]);
	  return DocumentGridManager;
	}();

	exports.DocumentGridManager = DocumentGridManager;

}((this.BX.Catalog = this.BX.Catalog || {}),BX,BX.Main,BX.UI.Dialogs,BX.Catalog.Store));
//# sourceMappingURL=document-grid.bundle.js.map
