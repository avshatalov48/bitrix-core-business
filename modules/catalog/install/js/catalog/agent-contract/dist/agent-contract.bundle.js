/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,main_core,ui_dialogs_messagebox) {
	'use strict';

	class AgentContractController extends BX.UI.EntityEditorController {
	  constructor(id, settings) {
	    super();
	    this.initialize(id, settings);
	  }
	  onAfterSave() {
	    super.onAfterSave();
	    window.top.BX.onCustomEvent('AgentContract:onDocumentSave');
	    let sliders = BX.SidePanel.Instance.getOpenSliders();
	    sliders.forEach(slider => {
	      slider.getWindow().BX.onCustomEvent('AgentContract:onDocumentSave');
	    });
	  }
	}

	class ControllersFactory {
	  constructor(eventName) {
	    main_core_events.EventEmitter.subscribe(eventName + ':onInitialize', event => {
	      const [, eventArgs] = event.getCompatData();
	      eventArgs.methods['agent_contract'] = this.factory.bind(this);
	    });
	  }
	  factory(type, controlId, settings) {
	    if (type === 'agent_contract') {
	      return new AgentContractController(controlId, settings);
	    }
	    return null;
	  }
	}

	class AgentContractModel extends BX.UI.EntityModel {
	  constructor(id, settings) {
	    super();
	    this.initialize(id, settings);
	  }
	  isCaptionEditable() {
	    return true;
	  }
	  getCaption() {
	    var title = this.getField("TITLE");
	    return BX.type.isString(title) ? title : "";
	  }
	  setCaption(caption) {
	    this.setField("TITLE", caption);
	  }
	  prepareCaptionData(data) {
	    data["TITLE"] = this.getField("TITLE", "");
	  }
	}

	class ModelFactory {
	  constructor() {
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorModelFactory:onInitialize', event => {
	      const [, eventArgs] = event.getCompatData();
	      eventArgs.methods['agent_contract'] = this.factory.bind(this);
	    });
	  }
	  factory(type, controlId, settings) {
	    if (type === 'agent_contract') {
	      return new AgentContractModel(controlId, settings);
	    }
	    return null;
	  }
	}

	class GridActions {
	  constructor(options = {}) {
	    this.grid = options.grid || null;
	    BX.addCustomEvent('AgentContract:onDocumentSave', () => {
	      var _this$grid;
	      (_this$grid = this.grid) == null ? void 0 : _this$grid.reload();
	    });
	    BX.SidePanel.Instance.bindAnchors({
	      rules: [{
	        condition: [new RegExp("/agent_contract/details/[0-9]+/"), new RegExp("/bitrix/admin/cat_agent_contract.php\\?ID=([0-9]+)")],
	        options: {
	          allowChangeHistory: false,
	          cacheable: false,
	          width: 650
	        }
	      }]
	    });
	  }
	  delete(id) {
	    ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('CATALOG_AGENT_CONTRACT_TITLE_DELETE_CONTENT'), (messageBox, button) => {
	      button.setWaiting();
	      main_core.ajax.runAction('catalog.agentcontract.entity.delete', {
	        data: {
	          id
	        }
	      }).then(() => {
	        var _this$grid2;
	        messageBox.close();
	        (_this$grid2 = this.grid) == null ? void 0 : _this$grid2.reload();
	      }).catch(response => {
	        if (response.errors) {
	          BX.UI.Notification.Center.notify({
	            content: BX.util.htmlspecialchars(response.errors[0].message)
	          });
	        }
	        messageBox.close();
	      });
	    }, main_core.Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_CONFIRM'), messageBox => messageBox.close(), main_core.Loc.getMessage('CATALOG_AGENT_CONTRACT_BUTTON_BACK'));
	  }
	  deleteList() {
	    let ids = this.grid.getRows().getSelectedIds();
	    main_core.ajax.runAction('catalog.agentcontract.entity.deleteList', {
	      data: {
	        ids: ids
	      }
	    }).then(response => {
	      var _this$grid3;
	      (_this$grid3 = this.grid) == null ? void 0 : _this$grid3.reload();
	    }).catch(response => {
	      var _this$grid4;
	      if (response.errors) {
	        response.errors.forEach(error => {
	          if (error.message) {
	            BX.UI.Notification.Center.notify({
	              content: BX.util.htmlspecialchars(error.message)
	            });
	          }
	        });
	      }
	      (_this$grid4 = this.grid) == null ? void 0 : _this$grid4.reload();
	    });
	  }
	}

	exports.ControllersFactory = ControllersFactory;
	exports.ModelFactory = ModelFactory;
	exports.GridActions = GridActions;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Event,BX,BX.UI.Dialogs));
//# sourceMappingURL=agent-contract.bundle.js.map
