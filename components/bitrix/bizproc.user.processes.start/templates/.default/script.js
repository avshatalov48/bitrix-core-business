/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,main_core_events,ui_alerts,bizproc_router) {
	'use strict';

	let _ = t => t,
	  _t;
	var _counters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("counters");
	var _action = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("action");
	var _onAfterGridUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterGridUpdated");
	var _renderStartedByMeNow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStartedByMeNow");
	class UserProcessesStart {
	  constructor(options) {
	    Object.defineProperty(this, _renderStartedByMeNow, {
	      value: _renderStartedByMeNow2
	    });
	    Object.defineProperty(this, _onAfterGridUpdated, {
	      value: _onAfterGridUpdated2
	    });
	    Object.defineProperty(this, _counters, {
	      writable: true,
	      value: new Map()
	    });
	    this.onElementCreatedHandler = null;
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      if (main_core.Type.isArray(options.errors)) {
	        this.showErrors(options.errors);
	      }
	    }
	    this.init();
	  }
	  init() {
	    BX.UI.Hint.init(document);
	    if (this.getGrid()) {
	      BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(this.getGrid());
	    }
	    main_core_events.EventEmitter.subscribe('Grid::updated', babelHelpers.classPrivateFieldLooseBase(this, _onAfterGridUpdated)[_onAfterGridUpdated].bind(this));
	    this.subscribeCustomEvents();
	  }
	  subscribeCustomEvents() {
	    if (this.onElementCreatedHandler === null) {
	      this.onElementCreatedHandler = this.onElementCreated.bind(this);
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', this.onElementCreatedHandler);
	    }
	  }
	  unsubscribeCustomEvents() {
	    if (this.onElementCreatedHandler) {
	      main_core_events.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', this.onElementCreatedHandler);
	      this.onElementCreatedHandler = null;
	    }
	  }
	  destroy() {
	    this.unsubscribeCustomEvents();
	  }
	  static changePin(iblockId, gridId, event) {
	    const eventData = event.getData();
	    const button = eventData.button;
	    if (main_core.Dom.hasClass(button, BX.Grid.CellActionState.ACTIVE)) {
	      babelHelpers.classPrivateFieldLooseBase(BX.Bizproc.Component.UserProcessesStart, _action)[_action]('unpin', iblockId, gridId);
	      main_core.Dom.removeClass(button, BX.Grid.CellActionState.ACTIVE);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(BX.Bizproc.Component.UserProcessesStart, _action)[_action]('pin', iblockId, gridId);
	      main_core.Dom.addClass(button, BX.Grid.CellActionState.ACTIVE);
	    }
	    const grid = BX.Main.gridManager.getInstanceById(gridId);
	    if (grid) {
	      BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(grid);
	    }
	  }
	  static colorPinnedRows(grid) {
	    grid.getRows().getRows().forEach(row => {
	      const node = row.getNode();
	      if (main_core.Type.isElementNode(node.querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active'))) {
	        main_core.Dom.addClass(node, 'bizproc-user-processes-start-item-pinned');
	      } else {
	        main_core.Dom.removeClass(node, 'bizproc-user-processes-start-item-pinned');
	      }
	    });
	  }
	  startWorkflow(event, iBlockTypeId, iblockId, iBlockName) {
	    event.preventDefault();
	    main_core.Runtime.loadExtension('lists.element.creation-guide').then(({
	      CreationGuide
	    }) => {
	      CreationGuide == null ? void 0 : CreationGuide.open({
	        iBlockTypeId,
	        iBlockId: iblockId,
	        analyticsP1: iBlockName
	      });
	    }).catch(() => {}).finally(() => {});
	  }
	  onElementCreated(event) {
	    const [sliderEvent] = event.getCompatData();
	    if (sliderEvent.getEventId() === 'BX.Lists.Element.CreationGuide:onElementCreated') {
	      const eventArgs = sliderEvent.getData();
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].has(eventArgs.iBlockId)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].set(eventArgs.iBlockId, 0);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].set(eventArgs.iBlockId, babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(eventArgs.iBlockId) + 1);
	      this.reloadGrid();
	      BX.SidePanel.Instance.getSliderByWindow(window).close();
	    } else {
	      this.reloadGrid();
	    }
	  }
	  showErrors(errors) {
	    if (!main_core.Type.isArrayFilled(errors)) {
	      if (!main_core.Type.isArray(errors)) {
	        console.error(errors);
	      }
	      return;
	    }
	    const errorsContainer = document.getElementById('bp-user-processes-errors-container');
	    if (errorsContainer) {
	      let errorCounter = 0;
	      const fixStyles = () => {
	        if (errorCounter > 0) {
	          main_core.Dom.style(errorsContainer, {
	            margin: '10px'
	          });
	        } else {
	          main_core.Dom.style(errorsContainer, {
	            margin: '0px'
	          });
	        }
	      };
	      for (const error of errors) {
	        errorCounter += 1;
	        const alert = new ui_alerts.Alert({
	          text: main_core.Text.encode(error.message),
	          color: ui_alerts.AlertColor.DANGER,
	          closeBtn: true,
	          animated: true
	        });
	        alert.renderTo(errorsContainer);
	        if (alert.getCloseBtn()) {
	          // eslint-disable-next-line no-loop-func
	          alert.getCloseBtn().onclick = () => {
	            errorCounter -= 1;
	            fixStyles();
	          };
	        }
	      }
	      fixStyles();
	    }
	  }
	  reloadGrid() {
	    var _this$getGrid;
	    (_this$getGrid = this.getGrid()) == null ? void 0 : _this$getGrid.reload();
	  }
	  getGrid() {
	    if (this.gridId) {
	      var _BX$Main$gridManager;
	      return (_BX$Main$gridManager = BX.Main.gridManager) == null ? void 0 : _BX$Main$gridManager.getInstanceById(this.gridId);
	    }
	    return null;
	  }
	  editTemplate(event, bizprocEditorUrl, canEdit) {
	    if (!canEdit) {
	      this.showNoPermissionsHint(event.target);
	      return;
	    }
	    if (bizprocEditorUrl.length === 0) {
	      this.showNoEditorHint(event.target);
	      return;
	    }
	    top.window.location.href = bizprocEditorUrl;
	  }
	  editTemplateConstants(templateId, signedDocumentType) {
	    bizproc_router.Router.openWorkflowChangeConstants({
	      templateId,
	      signedDocumentType
	    });
	  }
	  showAngleHint(node, text) {
	    if (this.hintTimeout) {
	      clearTimeout(this.hintTimeout);
	    }
	    this.popupHint = BX.UI.Hint.createInstance({
	      popupParameters: {
	        width: 334,
	        height: 104,
	        closeByEsc: true,
	        autoHide: true,
	        angle: {
	          offset: main_core.Dom.getPosition(node).width / 2
	        },
	        bindOptions: {
	          position: 'top'
	        }
	      }
	    });
	    this.popupHint.close = function () {
	      this.hide();
	    };
	    this.popupHint.show(node, text);
	    this.timeout = setTimeout(this.hideHint.bind(this), 5000);
	  }
	  hideHint() {
	    if (this.popupHint) {
	      this.popupHint.close();
	    }
	    this.popupHint = null;
	  }
	  showNoPermissionsHint(node) {
	    this.showAngleHint(node, main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_START_TEMPLATE_RIGHTS_ERROR'));
	  }
	  showNoEditorHint(node) {
	    this.showAngleHint(node, main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_START_TEMPLATE_MODULE_ERROR'));
	  }
	}
	function _action2(action, iblockId, gridId) {
	  const component = 'bitrix:bizproc.user.processes.start';
	  main_core.ajax.runComponentAction(component, action, {
	    mode: 'class',
	    data: {
	      iblockId
	    }
	  }).then(response => {
	    const grid = BX.Main.gridManager.getInstanceById(gridId);
	    if (grid) {
	      grid.reload();
	    }
	  }).catch(() => {});
	}
	function _onAfterGridUpdated2() {
	  if (this.getGrid()) {
	    BX.UI.Hint.init(this.getGrid().getContainer());
	    BX.Bizproc.Component.UserProcessesStart.colorPinnedRows(this.getGrid());
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].forEach((value, key) => {
	    const counter = document.querySelector(`[data-role="iblock-${key}-counter"]`);
	    if (main_core.Type.isElementNode(counter)) {
	      main_core.Dom.clean(counter);
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderStartedByMeNow)[_renderStartedByMeNow](key), counter);
	    }
	  });
	}
	function _renderStartedByMeNow2(iblockId) {
	  let message = main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_START_COUNTER', {
	    '#COUNTER#': babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].get(iblockId)
	  }));
	  message = message.replace('[bold]', '<span class="bizproc-user-processes-start-column-start-counter">');
	  message = message.replace('[/bold]', '</span>');
	  return main_core.Tag.render(_t || (_t = _`<div class="ui-typography-text-xs">${0}</div>`), message);
	}
	Object.defineProperty(UserProcessesStart, _action, {
	  value: _action2
	});

	exports.UserProcessesStart = UserProcessesStart;

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX,BX.Event,BX.UI,BX.Bizproc));
//# sourceMappingURL=script.js.map
