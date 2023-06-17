this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_popup,ui_buttons,ui_buttons_icons,ui_entitySelector,bp_field_type,ui_layoutForm,ui_hint,main_date,main_loader,bizproc_condition,ui_fonts_robotomono,bizproc_automation,bizproc_debugger,pull_client,bizproc_localSettings,main_core_events,main_core,ui_tour,ui_dialogs_messagebox) {
	'use strict';

	class DebuggerState {}
	DebuggerState.Run = 0;
	DebuggerState.NextStep = 1;
	DebuggerState.Stop = 2;
	DebuggerState.Pause = 3;
	DebuggerState.Undefined = -1;

	class Helper {
	  /** Finds whether a variable is a number or a numeric string */
	  static isNumeric(num) {
	    if (main_core.Type.isNumber(num)) {
	      return true;
	    }
	    if (!main_core.Type.isStringFilled(num)) {
	      return false;
	    }
	    return Number(num).toString() === num.trim();
	  }

	  /** Checks whether the variable is a date or a timestamp */
	  static isDate(date) {
	    if (main_core.Type.isDate(date)) {
	      return true;
	    }
	    if (!Helper.isNumeric(date)) {
	      return false;
	    }
	    return new Date(Number(date)).getTime() === Number(date);
	  }

	  /** Convert date from DataBase to date in JS */
	  static convertDateFromDB(date) {
	    if (!Helper.isNumeric(date)) {
	      return null;
	    }
	    return new Date(date * 1000);
	  }

	  /** if the variable is a date or a timestamp return Date, else null  */
	  static toDate(date) {
	    if (main_date.DateTimeFormat.parse(date)) {
	      return main_date.DateTimeFormat.parse(date, false);
	    }
	    if (Date.parse(date)) {
	      return new Date(date);
	    }
	    if (!Helper.isDate(date)) {
	      return null;
	    }
	    if (main_core.Type.isDate(date)) {
	      return date;
	    }
	    return Helper.convertDateFromDB(date);
	  }

	  /** formats the date */
	  static formatDate(format, date) {
	    if (!main_core.Type.isStringFilled(format)) {
	      format = 'j F Y H:i:s';
	    }
	    return main_date.DateTimeFormat.format(format, date);
	  }

	  /** return condition operators label */
	  static getOperatorsLabel() {
	    return bizproc_condition.Operator.getAllLabels();
	  }

	  /** return condition operator label */
	  static getOperatorLabel(operator) {
	    return bizproc_condition.Operator.getOperatorLabel([operator]);
	  }

	  /** return joiner label */
	  static getJoinerLabel(joiner) {
	    const joiners = {
	      'AND': main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_AND'),
	      'OR': main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_OR')
	    };
	    return joiners[joiner];
	  }
	  static getColorBrightness(bgColor) {
	    if (bgColor[0] === '#') {
	      bgColor = bgColor.replace('#', '');
	    }
	    const bigint = parseInt(bgColor, 16);
	    const r = bigint >> 16 & 255;
	    const g = bigint >> 8 & 255;
	    const b = bigint & 255;
	    return 0.21 * r + 0.72 * g + 0.07 * b;
	  }
	  static getBgColorAdditionalClass(bgColor) {
	    const brightness = Helper.getColorBrightness(bgColor);
	    if (brightness > 224) {
	      return '--with-border --light-color';
	    }
	    if (brightness > 145) {
	      return '--light-color';
	    }
	    return '';
	  }
	  static toHtml(text) {
	    return main_core.Text.encode(text || '').replace(/\[(\/)?b\]/ig, '<$1b>');
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12;
	var _debuggerInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("debuggerInstance");
	var _popupInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupInstance");
	var _loaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loaded");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _triggerManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("triggerManager");
	var _template = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	var _tracker = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tracker");
	var _tabs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tabs");
	var _expandedMinWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expandedMinWidth");
	var _expandedMinHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expandedMinHeight");
	var _collapsedMinWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collapsedMinWidth");
	var _collapsedMinHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collapsedMinHeight");
	var _changingViewTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("changingViewTimeout");
	var _buttonPlay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttonPlay");
	var _getPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopup");
	var _getPopupWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupWidth");
	var _getPopupHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupHeight");
	var _getPopupTitleBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTitleBar");
	var _handleCollapse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleCollapse");
	var _handleClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClose");
	var _getNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNode");
	var _renderExpandedMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpandedMode");
	var _renderCollapsedMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCollapsedMode");
	var _getAddFieldNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAddFieldNode");
	var _getFieldListNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldListNode");
	var _handleChangeTab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleChangeTab");
	var _handleAddDocFieldMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddDocFieldMenu");
	var _handleAddField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAddField");
	var _handleRemoveField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleRemoveField");
	var _handleFieldListChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFieldListChange");
	var _getFieldNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldNode");
	var _createTriggersHeaderNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTriggersHeaderNode");
	var _createTriggersNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTriggersNode");
	var _createTemplateNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTemplateNode");
	var _updateTemplate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateTemplate");
	var _createTemplateToolbar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTemplateToolbar");
	var _handleStartTemplate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleStartTemplate");
	var _handleEmulateExternalEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleEmulateExternalEvent");
	var _updateTracker = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateTracker");
	var _renderPausedRobots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPausedRobots");
	var _createStageNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStageNode");
	var _handleShowStages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleShowStages");
	var _handleChangeStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleChangeStatus");
	var _getDocumentStatusTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDocumentStatusTitle");
	var _getDocumentStatusColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDocumentStatusColor");
	var _onDocumentStatusChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDocumentStatusChanged");
	var _onWorkflowEventsChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWorkflowEventsChanged");
	var _onWorkflowTrackAdded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWorkflowTrackAdded");
	var _onDocumentValuesUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDocumentValuesUpdated");
	var _onWorkflowStatusChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWorkflowStatusChange");
	var _onAfterDocumentFixed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterDocumentFixed");
	var _setDebuggerState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDebuggerState");
	class AutomationMainView extends main_core_events.EventEmitter {
	  constructor(debuggerInstance) {
	    super();
	    Object.defineProperty(this, _setDebuggerState, {
	      value: _setDebuggerState2
	    });
	    Object.defineProperty(this, _onAfterDocumentFixed, {
	      value: _onAfterDocumentFixed2
	    });
	    Object.defineProperty(this, _onWorkflowStatusChange, {
	      value: _onWorkflowStatusChange2
	    });
	    Object.defineProperty(this, _onDocumentValuesUpdated, {
	      value: _onDocumentValuesUpdated2
	    });
	    Object.defineProperty(this, _onWorkflowTrackAdded, {
	      value: _onWorkflowTrackAdded2
	    });
	    Object.defineProperty(this, _onWorkflowEventsChanged, {
	      value: _onWorkflowEventsChanged2
	    });
	    Object.defineProperty(this, _onDocumentStatusChanged, {
	      value: _onDocumentStatusChanged2
	    });
	    Object.defineProperty(this, _getDocumentStatusColor, {
	      value: _getDocumentStatusColor2
	    });
	    Object.defineProperty(this, _getDocumentStatusTitle, {
	      value: _getDocumentStatusTitle2
	    });
	    Object.defineProperty(this, _handleChangeStatus, {
	      value: _handleChangeStatus2
	    });
	    Object.defineProperty(this, _handleShowStages, {
	      value: _handleShowStages2
	    });
	    Object.defineProperty(this, _createStageNode, {
	      value: _createStageNode2
	    });
	    Object.defineProperty(this, _renderPausedRobots, {
	      value: _renderPausedRobots2
	    });
	    Object.defineProperty(this, _updateTracker, {
	      value: _updateTracker2
	    });
	    Object.defineProperty(this, _handleEmulateExternalEvent, {
	      value: _handleEmulateExternalEvent2
	    });
	    Object.defineProperty(this, _handleStartTemplate, {
	      value: _handleStartTemplate2
	    });
	    Object.defineProperty(this, _createTemplateToolbar, {
	      value: _createTemplateToolbar2
	    });
	    Object.defineProperty(this, _updateTemplate, {
	      value: _updateTemplate2
	    });
	    Object.defineProperty(this, _createTemplateNode, {
	      value: _createTemplateNode2
	    });
	    Object.defineProperty(this, _createTriggersNode, {
	      value: _createTriggersNode2
	    });
	    Object.defineProperty(this, _createTriggersHeaderNode, {
	      value: _createTriggersHeaderNode2
	    });
	    Object.defineProperty(this, _getFieldNode, {
	      value: _getFieldNode2
	    });
	    Object.defineProperty(this, _handleFieldListChange, {
	      value: _handleFieldListChange2
	    });
	    Object.defineProperty(this, _handleRemoveField, {
	      value: _handleRemoveField2
	    });
	    Object.defineProperty(this, _handleAddField, {
	      value: _handleAddField2
	    });
	    Object.defineProperty(this, _handleAddDocFieldMenu, {
	      value: _handleAddDocFieldMenu2
	    });
	    Object.defineProperty(this, _handleChangeTab, {
	      value: _handleChangeTab2
	    });
	    Object.defineProperty(this, _getFieldListNode, {
	      value: _getFieldListNode2
	    });
	    Object.defineProperty(this, _getAddFieldNode, {
	      value: _getAddFieldNode2
	    });
	    Object.defineProperty(this, _renderCollapsedMode, {
	      value: _renderCollapsedMode2
	    });
	    Object.defineProperty(this, _renderExpandedMode, {
	      value: _renderExpandedMode2
	    });
	    Object.defineProperty(this, _getNode, {
	      value: _getNode2
	    });
	    Object.defineProperty(this, _handleClose, {
	      value: _handleClose2
	    });
	    Object.defineProperty(this, _handleCollapse, {
	      value: _handleCollapse2
	    });
	    Object.defineProperty(this, _getPopupTitleBar, {
	      value: _getPopupTitleBar2
	    });
	    Object.defineProperty(this, _getPopupHeight, {
	      value: _getPopupHeight2
	    });
	    Object.defineProperty(this, _getPopupWidth, {
	      value: _getPopupWidth2
	    });
	    Object.defineProperty(this, _getPopup, {
	      value: _getPopup2
	    });
	    Object.defineProperty(this, _debuggerInstance, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupInstance, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loaded, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _triggerManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _template, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tracker, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tabs, {
	      writable: true,
	      value: ['doc', 'log']
	    });
	    Object.defineProperty(this, _expandedMinWidth, {
	      writable: true,
	      value: 781
	    });
	    Object.defineProperty(this, _expandedMinHeight, {
	      writable: true,
	      value: 612
	    });
	    Object.defineProperty(this, _collapsedMinWidth, {
	      writable: true,
	      value: 465
	    });
	    Object.defineProperty(this, _collapsedMinHeight, {
	      writable: true,
	      value: 187
	    });
	    Object.defineProperty(this, _changingViewTimeout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _buttonPlay, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Bizproc.Debugger.AutomationMainView');
	    babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance)[_debuggerInstance] = debuggerInstance;
	    debuggerInstance.subscribe('onDocumentStatusChanged', babelHelpers.classPrivateFieldLooseBase(this, _onDocumentStatusChanged)[_onDocumentStatusChanged].bind(this));
	    debuggerInstance.subscribe('onWorkflowEventsChanged', babelHelpers.classPrivateFieldLooseBase(this, _onWorkflowEventsChanged)[_onWorkflowEventsChanged].bind(this));
	    debuggerInstance.subscribe('onWorkflowTrackAdded', babelHelpers.classPrivateFieldLooseBase(this, _onWorkflowTrackAdded)[_onWorkflowTrackAdded].bind(this));
	    debuggerInstance.subscribe('onDocumentValuesUpdated', babelHelpers.classPrivateFieldLooseBase(this, _onDocumentValuesUpdated)[_onDocumentValuesUpdated].bind(this));
	    debuggerInstance.subscribe('onWorkflowStatusChanged', babelHelpers.classPrivateFieldLooseBase(this, _onWorkflowStatusChange)[_onWorkflowStatusChange].bind(this));
	    debuggerInstance.subscribe('onAfterDocumentFixed', babelHelpers.classPrivateFieldLooseBase(this, _onAfterDocumentFixed)[_onAfterDocumentFixed].bind(this));
	  }
	  get debugger() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance)[_debuggerInstance];
	  }
	  show() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loaded)[_loaded]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	      return;
	    }
	    this.debugger.loadMainViewInfo().then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _loaded)[_loaded] = true;
	      babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().setContent(babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]());
	      babelHelpers.classPrivateFieldLooseBase(this, _setDebuggerState)[_setDebuggerState](this.debugger.getState());
	      babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().show();
	    });
	  }
	  showExpanded() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().isShown()) {
	      this.debugger.settings.set('popup-collapsed', false);
	      this.show();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _handleCollapse)[_handleCollapse]();
	  }
	  showCollapsed() {
	    this.debugger.settings.set('popup-collapsed', true);
	    this.show();
	  }
	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().close();
	  }
	  destroy() {
	    this.close();
	    //TODO - cleanup
	  }
	}
	function _getPopup2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance]) {
	    const collapsed = this.debugger.settings.get('popup-collapsed');
	    const className = 'bizproc-debugger-automation__main-popup bizproc-debugger-automation__scope';
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance] = new main_popup.Popup({
	      className: className + (collapsed ? ' --collapse' : ''),
	      titleBar: babelHelpers.classPrivateFieldLooseBase(this, _getPopupTitleBar)[_getPopupTitleBar](),
	      noAllPaddings: true,
	      contentBackground: 'white',
	      draggable: true,
	      zIndexOptions: {
	        alwaysOnTop: collapsed
	      },
	      width: babelHelpers.classPrivateFieldLooseBase(this, _getPopupWidth)[_getPopupWidth](collapsed),
	      height: babelHelpers.classPrivateFieldLooseBase(this, _getPopupHeight)[_getPopupHeight](collapsed),
	      events: {
	        onResizeStart: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].setMinWidth(babelHelpers.classPrivateFieldLooseBase(this, _expandedMinWidth)[_expandedMinWidth]);
	          babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].setMinHeight(babelHelpers.classPrivateFieldLooseBase(this, _expandedMinHeight)[_expandedMinHeight]);
	        },
	        onResizeEnd: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].setMinWidth(null);
	          babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].setMinHeight(null);
	          this.debugger.settings.set('popup-width', babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].getWidth());
	          this.debugger.settings.set('popup-height', babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].getHeight());
	        }
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance].setResizeMode(!collapsed);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _popupInstance)[_popupInstance];
	}
	function _getPopupWidth2(collapsed) {
	  if (collapsed) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collapsedMinWidth)[_collapsedMinWidth];
	  }
	  return Math.max(babelHelpers.classPrivateFieldLooseBase(this, _expandedMinWidth)[_expandedMinWidth], this.debugger.settings.get('popup-width') || 0);
	}
	function _getPopupHeight2(collapsed) {
	  if (collapsed) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collapsedMinHeight)[_collapsedMinHeight];
	  }
	  return Math.max(babelHelpers.classPrivateFieldLooseBase(this, _expandedMinHeight)[_expandedMinHeight], this.debugger.settings.get('popup-height') || 0);
	}
	function _getPopupTitleBar2() {
	  return {
	    content: main_core.Tag.render(_t || (_t = _`
				<div class="popup-window-titlebar-text bizproc-debugger-automation__titlebar">
					<div class="bizproc-debugger-automation__titlebar--move-icon"></div>
					${0}
					<div 
						class="bizproc-debugger-automation__titlebar--button-collapse" 
						onclick="${0}"
					></div>
					<span 
						class=" popup-window-close-icon 
								popup-window-titlebar-close-icon 
								bizproc-debugger-automation__titlebar--button-close"
						onclick="${0}"
					></span>
				</div>
			`), document.createTextNode(main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_POPUP_TITLE')), babelHelpers.classPrivateFieldLooseBase(this, _handleCollapse)[_handleCollapse].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleClose)[_handleClose].bind(this))
	  };
	}
	function _handleCollapse2() {
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().getPopupContainer();
	  const collapsed = main_core.Dom.hasClass(node, '--collapse');
	  this.debugger.settings.set('popup-collapsed', !collapsed);
	  babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().getZIndexComponent().setAlwaysOnTop(!collapsed);
	  babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().setResizeMode(collapsed);
	  main_core.Dom.toggleClass(node, '--collapse');
	  clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _changingViewTimeout)[_changingViewTimeout]);
	  main_core.Dom.addClass(node, '--changing-view');
	  babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().setWidth(babelHelpers.classPrivateFieldLooseBase(this, _getPopupWidth)[_getPopupWidth](!collapsed));
	  babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().setHeight(babelHelpers.classPrivateFieldLooseBase(this, _getPopupHeight)[_getPopupHeight](!collapsed));
	  babelHelpers.classPrivateFieldLooseBase(this, _changingViewTimeout)[_changingViewTimeout] = setTimeout(() => main_core.Dom.removeClass(node, '--changing-view'), 500);
	}
	function _handleClose2() {
	  bizproc_debugger.Manager.Instance.askFinishSession(this.debugger.session).catch(() => {/*do nth*/});
	}
	function _getNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _node)[_node] = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="bizproc-debugger-automation__content">
					<div class="bizproc-debugger-automation-content-collapsed">
						${0}
					</div>
					<div class="bizproc-debugger-automation__content-expanded">
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _renderCollapsedMode)[_renderCollapsedMode](), babelHelpers.classPrivateFieldLooseBase(this, _renderExpandedMode)[_renderExpandedMode]());
	    bizproc_automation.HelpHint.bindAll(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _node)[_node];
	}
	function _renderExpandedMode2() {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  const hasRobots = !this.debugger.isTemplateEmpty();
	  const activeTab = this.debugger.settings.get('tab') === 'log' ? 'log' : 'doc';
	  const tabDocClass = activeTab === 'doc' ? '--active' : '';
	  const tabLogClass = activeTab === 'log' ? '--active' : '';
	  const hasActiveDocument = this.debugger.session.isFixed();
	  const tabNoDocumentClass = hasActiveDocument ? '' : '--empty --active';
	  const fieldListNode = babelHelpers.classPrivateFieldLooseBase(this, _getFieldListNode)[_getFieldListNode]();
	  const hasFields = fieldListNode.querySelector('[data-field-id]') !== null;
	  return main_core.Tag.render(_t3 || (_t3 = _`
				<div class="bizproc-debugger-automation__main">
					<div class="bizproc-debugger-automation__main-robots">
						<div class="bizproc-debugger-automation__main-robots--head">
							${0}
						</div>
						<div data-role="automation-content" class="bizproc-debugger-automation__main-robots--main-content">
							${0}
							${0}
							<div class="bizproc-debugger-automation__head">
								<div class="bizproc-debugger-automation__main--title">
									<div class="bizproc-debugger-automation__main--name">${0} </div>
									<div class="ui-hint">
										<span class="ui-hint-icon" data-text="${0}"></span>
									</div>
								</div>
								<div data-role="no-template" class="bizproc-debugger-automation__main-hint ${0}">
									<div class="bizproc-debugger-automation__main-hint--title">
										${0}
									</div>
									<div class="bizproc-debugger-automation__main-hint--text">
										${0}
									</div>
									<a href="${0}" class="bizproc-debugger-automation__link">${0}</a>
								</div>
							</div>
							${0}
						</div>
						${0}
					</div>
					<div class="bizproc-debugger-automation__main-fields ${0}">
						<div data-role="tabs-container" class="bizproc-debugger-automation__main-navigation --active-${0}">
							<div class="bizproc-debugger-automation__tab-block">
								<span class="bizproc-debugger-automation__tab ${0}" data-tab-item="doc" onclick="${0}">
									${0}
								</span>
								<div class="ui-hint">
									<span class="ui-hint-icon" data-text="${0}"></span>
								</div>
							</div>
							<div class="bizproc-debugger-automation__tab-block">
								<span class="bizproc-debugger-automation__tab ${0}" data-tab-item="log" onclick="${0}">
									${0}
								</span>
								<div class="ui-hint">
									<span class="ui-hint-icon" data-text="${0}"></span>
								</div>
							</div>
							
							<div data-tab-item="doc" class="bizproc-debugger-automation__tab-action ${0}">
								${0}
							</div>
							
							<div data-tab-item="log" class="bizproc-debugger-automation__tab-action ${0}">
								<div class="bizproc-debugger-automation__action-btn --icon-search" style="display: none"></div>
								<div class="bizproc-debugger-automation__action-btn --icon-log" onclick="${0}"></div>
								<div class="bizproc-debugger-automation__action-btn --icon-note" style="display: none"></div>
							</div>
						</div>
						
						<div data-tab-item="doc" data-role="tab-content-doc" class="bizproc-debugger-tab__content ${0} ${0}">
							<div class="bizproc-debugger-tab__content--empty">
								${0}
							</div>
							<div class="bizproc-debugger-tab__content--not-empty">
								<div class="bizproc-debugger-tab__content-title">${0}</div>							
								${0}
							</div>
						</div>
						<div data-tab-item="log" class="bizproc-debugger-tab__content ${0} bizproc-debugger-automation-main-section-log">
							${0}
						</div>
						<div data-tab-item="no-document" class="bizproc-debugger-tab__content ${0} bizproc-debugger-automation-main-section-disabled">
							<div class="bizproc-debugger-tab__content--empty">
								${0}
							</div>
						</div>
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _createStageNode)[_createStageNode](), (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _createTriggersHeaderNode)[_createTriggersHeaderNode]()) != null ? _babelHelpers$classPr : '', (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _createTriggersNode)[_createTriggersNode]()) != null ? _babelHelpers$classPr2 : '', main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ROBOTS_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_ROBOT')), hasRobots ? '' : '--active', main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_ROBOTS_TITLE')), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_ROBOTS_SUBTITLE')), this.debugger.getSettingsUrl(), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_AUTOMATION_SETTINGS')), babelHelpers.classPrivateFieldLooseBase(this, _createTemplateNode)[_createTemplateNode](), babelHelpers.classPrivateFieldLooseBase(this, _createTemplateToolbar)[_createTemplateToolbar](), hasActiveDocument ? '' : '--disabled', activeTab, tabDocClass, babelHelpers.classPrivateFieldLooseBase(this, _handleChangeTab)[_handleChangeTab].bind(this), main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_DOCUMENT_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_FIELD')), tabLogClass, babelHelpers.classPrivateFieldLooseBase(this, _handleChangeTab)[_handleChangeTab].bind(this), main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_LOG_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_LOG')), tabDocClass, babelHelpers.classPrivateFieldLooseBase(this, _getAddFieldNode)[_getAddFieldNode](), tabLogClass, () => {
	    bizproc_debugger.Manager.Instance.openSessionLog(this.debugger.sessionId);
	  }, hasActiveDocument ? tabDocClass : '', hasFields ? '' : '--empty', main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_FIELD_TITLE')), main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_DOCUMENT_TITLE'), fieldListNode, hasActiveDocument ? tabLogClass : '', this.debugger.getLogView().shouldScrollToBottom(true).shouldLoadPreviousLog(true).render(), tabNoDocumentClass, main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_FIXED_DOCUMENT')));
	}
	function _renderCollapsedMode2() {
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="bizproc-debugger-automation-menu__content-body">
				<div class="bizproc-debugger-automation-menu__content-body--logo"></div>
				<div class="bizproc-debugger-automation-menu__content-body--text">${0}</div>
			</div>
		`), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_MENU_SUBTITLE'));
	}
	function _getAddFieldNode2() {
	  return new ui_buttons.Button({
	    size: ui_buttons.ButtonSize.EXTRA_SMALL,
	    color: ui_buttons.ButtonColor.PRIMARY,
	    round: true,
	    noCaps: true,
	    text: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_ADD_FIELD'),
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _handleAddDocFieldMenu)[_handleAddDocFieldMenu].bind(this)
	  }).render();
	}
	function _getFieldListNode2() {
	  const form = main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-form" data-role="doc-field-list">
			</div>`));
	  const fields = this.debugger.settings.getSet('watch-fields');
	  fields.forEach(value => {
	    const node = babelHelpers.classPrivateFieldLooseBase(this, _getFieldNode)[_getFieldNode](value);
	    if (node) {
	      main_core.Dom.append(node, form);
	    }
	  });
	  return form;
	}
	function _handleChangeTab2(event) {
	  const activeTabName = event.target.dataset.tabItem;
	  const hiddenTabName = babelHelpers.classPrivateFieldLooseBase(this, _tabs)[_tabs].filter(tabName => tabName !== activeTabName)[0];
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]();
	  const navigationNode = node.querySelector('[data-role="tabs-container"]');
	  node.querySelectorAll([`[data-tab-item="${activeTabName}"]`]).forEach(tab => main_core.Dom.addClass(tab, '--active'));
	  node.querySelectorAll([`[data-tab-item="${hiddenTabName}"]`]).forEach(tab => main_core.Dom.removeClass(tab, '--active'));
	  main_core.Dom.addClass(navigationNode, `--active-${activeTabName}`);
	  main_core.Dom.removeClass(navigationNode, `--active-${hiddenTabName}`);
	  this.debugger.settings.set('tab', activeTabName);
	  this.emit('onChangeTab', {
	    tab: activeTabName
	  });
	}
	function _handleAddDocFieldMenu2(button, event) {
	  const documentFields = this.debugger.getDocumentFields();
	  const selectedFields = this.debugger.settings.getSet('watch-fields');
	  const fieldsDialog = new ui_entitySelector.EntitySelector.Dialog({
	    targetNode: event.target,
	    width: 500,
	    height: 300,
	    multiple: true,
	    dropdownMode: true,
	    enableSearch: true,
	    cacheable: false,
	    items: documentFields.filter(field => field.Watchable === true).map(field => {
	      return {
	        title: field.Name,
	        id: field.Id,
	        customData: {
	          field
	        },
	        entityId: 'bp',
	        tabs: 'recents',
	        selected: selectedFields.has(field.Id)
	      };
	    }),
	    showAvatars: false,
	    events: {
	      'Item:onSelect': event => babelHelpers.classPrivateFieldLooseBase(this, _handleAddField)[_handleAddField](event.getData().item),
	      'Item:onDeselect': event => babelHelpers.classPrivateFieldLooseBase(this, _handleRemoveField)[_handleRemoveField](event.getData().item.getId())
	    },
	    compactView: true
	  });
	  fieldsDialog.show();
	}
	function _handleAddField2(item) {
	  const fields = this.debugger.settings.getSet('watch-fields');
	  const field = item.getCustomData().get('field');
	  if (fields.has(field.Id)) {
	    return;
	  }
	  const fieldNode = babelHelpers.classPrivateFieldLooseBase(this, _getFieldNode)[_getFieldNode](field);
	  main_core.Dom.append(fieldNode, babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="doc-field-list"]'));
	  fields.add(field.Id);
	  this.debugger.settings.set('watch-fields', fields);
	  babelHelpers.classPrivateFieldLooseBase(this, _handleFieldListChange)[_handleFieldListChange](fields);
	}
	function _handleRemoveField2(fieldId) {
	  const fields = this.debugger.settings.getSet('watch-fields');
	  if (!fields.has(fieldId)) {
	    return;
	  }
	  fields.delete(fieldId);
	  this.debugger.settings.set('watch-fields', fields);
	  main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector(`[data-field-id="${fieldId}"]`));
	  babelHelpers.classPrivateFieldLooseBase(this, _handleFieldListChange)[_handleFieldListChange](fields);
	}
	function _handleFieldListChange2(fields) {
	  const contentNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="tab-content-doc"]');
	  const hasFields = contentNode.querySelector('[data-field-id]') !== null;
	  main_core.Dom[hasFields ? 'removeClass' : 'addClass'](contentNode, '--empty');
	}
	function _getFieldNode2(field) {
	  if (main_core.Type.isString(field)) {
	    field = this.debugger.getDocumentField(field);
	  }
	  if (!field || !field.Id) {
	    return null;
	  }
	  const value = this.debugger.getDocumentValue(field.Id) || '';
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="ui-form-row" data-role="field-row" data-field-id="${0}">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">${0}</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-ext-after-icon">
						<input type="text" readonly class="ui-ctl-element"
						 placeholder="${0}"
						 data-role="field-value-${0}"
						 value="${0}"
						 >
						 <a class="ui-ctl-after ui-ctl-icon-clear" onclick="${0}"></a>
					</div>
				</div>
			</div>
		`), main_core.Text.encode(field.Id), main_core.Text.encode(field.Name), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_EMPTY_VALUE'), main_core.Text.encode(field.Id), main_core.Text.encode(value), babelHelpers.classPrivateFieldLooseBase(this, _handleRemoveField)[_handleRemoveField].bind(this, field.Id));
	}
	function _createTriggersHeaderNode2() {
	  const hasTriggers = this.debugger.templateTriggers.length > 0;
	  const hasRobots = !this.debugger.isTemplateEmpty();
	  if (!hasTriggers && hasRobots) {
	    return null;
	  }
	  return main_core.Tag.render(_t7 || (_t7 = _`
			<div data-role="triggers-header" class="bizproc-debugger-automation__head">
				<div class="bizproc-debugger-automation__main--title">
					<div class="bizproc-debugger-automation__main--name">${0} </div>
					<div class="ui-hint">
						<span class="ui-hint-icon" data-text="${0}"></span>
					</div>
				</div>
				<div data-role="no-triggers" class="bizproc-debugger-automation__main-hint ${0}">
					<div class="bizproc-debugger-automation__main-hint--title">
						${0}
					</div>
					<div class="bizproc-debugger-automation__main-hint--text">
						${0}
					</div>
					<a href="${0}" class="bizproc-debugger-automation__link">${0}</a>
				</div>
			</div>
		`), main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_TRIGGERS_TITLE'), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_TRIGGER')), hasTriggers || hasRobots ? '' : '--active', main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_TRIGGERS_TITLE')), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_TRIGGERS_SUBTITLE')), this.debugger.getSettingsUrl(), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_AUTOMATION_SETTINGS')));
	}
	function _createTriggersNode2() {
	  const documentStatus = this.debugger.getTemplate().DOCUMENT_STATUS;
	  babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker] = new bizproc_automation.Tracker(bizproc_automation.getGlobalContext().document);
	  const logs = {};
	  logs[documentStatus] = this.debugger.track;
	  babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker].init(logs);
	  bizproc_automation.getGlobalContext().tracker = babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker];
	  const triggers = this.debugger.templateTriggers;
	  if (triggers.length === 0) {
	    return null;
	  }
	  const node = main_core.Tag.render(_t8 || (_t8 = _`
			<div data-role="triggers" class="bizproc-debugger__template">
				<div class="bizproc-automation-status-list-item" data-type="column-trigger">
					<div data-role="trigger-list" class="bizproc-automation-trigger-list" data-status-id="${0}"></div>
				</div>
			</div>
		`), documentStatus);
	  babelHelpers.classPrivateFieldLooseBase(this, _triggerManager)[_triggerManager] = new bizproc_automation.TriggerManager(node);
	  babelHelpers.classPrivateFieldLooseBase(this, _triggerManager)[_triggerManager].init({
	    TRIGGERS: triggers
	  }, bizproc_automation.ViewMode.view());
	  return node;
	}
	function _createTemplateNode2() {
	  const templateData = this.debugger.getTemplate();
	  babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker] = new bizproc_automation.Tracker(bizproc_automation.getGlobalContext().document);
	  const logs = {};
	  logs[templateData.DOCUMENT_STATUS] = this.debugger.track;
	  babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker].init(logs);
	  bizproc_automation.getGlobalContext().tracker = babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker];
	  const node = main_core.Tag.render(_t9 || (_t9 = _`
			<div data-role="template" class="bizproc-debugger__template">
				<div data-role="automation-template" data-status-id="${0}">
					<div data-role="robot-list" class="bizproc-automation-robot-list"></div>
				</div>
			</div>
		`), templateData.DOCUMENT_STATUS);
	  const template = new bizproc_automation.Template({
	    constants: {},
	    variables: {},
	    templateContainerNode: node,
	    delayMinLimitM: 0
	    // userOptions: this.userOptions,
	  });

	  template.init(templateData, bizproc_automation.ViewMode.view().intoRaw());
	  babelHelpers.classPrivateFieldLooseBase(this, _updateTemplate)[_updateTemplate](template);
	  babelHelpers.classPrivateFieldLooseBase(this, _renderPausedRobots)[_renderPausedRobots]();
	  return node;
	}
	function _updateTemplate2(newTemplate) {
	  if (!main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _template)[_template])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _template)[_template].destroy();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _template)[_template] = newTemplate;
	  return babelHelpers.classPrivateFieldLooseBase(this, _template)[_template];
	}
	function _createTemplateToolbar2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay] = new ui_buttons.Button({
	    size: ui_buttons.ButtonSize.EXTRA_SMALL,
	    color: ui_buttons.ButtonColor.PRIMARY,
	    round: true,
	    icon: ui_buttons.ButtonIcon.START,
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _handleStartTemplate)[_handleStartTemplate].bind(this)
	  });
	  const hasEvents = this.debugger.hasWorkflowEvents();
	  const hasActiveDocument = this.debugger.session.isFixed();
	  return main_core.Tag.render(_t10 || (_t10 = _`
			<div class="bizproc-debugger-automation__toolbar ${0}">
			<div data-role="external-event-info" class="bizproc-debugger-automation__toolbar--info-waiting ${0}">
				<div>
					${0}
				</div>
				<a onclick="${0}" class="bizproc-debugger-automation__link">
					${0}
				</a>
			</div>
			<div class="bizproc-debugger-automation__toolbar--btn-block">
				${0}
				<div class="bizproc-debugger-automation__toolbar--btn-text">
					${0}
				</div>
			</div>
			</div>
		`), hasActiveDocument ? '' : '--disabled', hasEvents ? '--active' : '', main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_SUBTITLE')), babelHelpers.classPrivateFieldLooseBase(this, _handleEmulateExternalEvent)[_handleEmulateExternalEvent].bind(this), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_TITLE')), babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay].render(), main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_START'));
	}
	function _handleStartTemplate2(button) {
	  button.setWaiting(true);
	  this.debugger.startDebugTemplate().then(() => {
	    button.setWaiting(false);
	    babelHelpers.classPrivateFieldLooseBase(this, _setDebuggerState)[_setDebuggerState](this.debugger.getState());
	  });
	}
	function _handleEmulateExternalEvent2(event) {
	  const infoNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="external-event-info"]');
	  main_core.Dom.removeClass(infoNode, '--active');
	  this.debugger.emulateExternalEvent(event.target.dataset.sourceId);
	}
	function _updateTracker2(data) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker]) {
	    const logs = {};
	    logs[this.debugger.getDocumentStatus()] = this.debugger.track;
	    babelHelpers.classPrivateFieldLooseBase(this, _tracker)[_tracker].reInit(logs);
	    babelHelpers.classPrivateFieldLooseBase(this, _template)[_template].reInit(null, bizproc_automation.ViewMode.view().intoRaw());
	    babelHelpers.classPrivateFieldLooseBase(this, _renderPausedRobots)[_renderPausedRobots]();
	  }
	}
	function _renderPausedRobots2() {
	  const pausedRobots = babelHelpers.classPrivateFieldLooseBase(this, _template)[_template].robots.filter(robot => robot.getLogStatus() === bizproc_automation.TrackingStatus.RUNNING);
	  pausedRobots.forEach(robot => {
	    const loader = robot.node.lastChild.lastChild;
	    const clonedLoader = main_core.Runtime.clone(loader);
	    bizproc_automation.HelpHint.bindToNode(clonedLoader);
	    main_core.Dom.replace(loader, main_core.Tag.render(_t11 || (_t11 = _`
					<div class="bizproc-debugger-automation-robot-info-container">
						${0}
						<a 
							onclick="${0}"
							data-source-id="${0}"
							class="bizproc-debugger-automation__link --inside-robot"
						>
							${0}
						</a>
					</div>
				`), clonedLoader, babelHelpers.classPrivateFieldLooseBase(this, _handleEmulateExternalEvent)[_handleEmulateExternalEvent].bind(this), robot.getId(), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_TITLE'))));
	  });
	}
	function _createStageNode2() {
	  const color = babelHelpers.classPrivateFieldLooseBase(this, _getDocumentStatusColor)[_getDocumentStatusColor]();
	  const title = main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _getDocumentStatusTitle)[_getDocumentStatusTitle]());
	  return main_core.Tag.render(_t12 || (_t12 = _`
			<div 
				class="bizproc-debugger-automation__status --robot-change ${0}"
				data-role="document-status"
				title="${0}"
				onclick="${0}"
			>
				<div class="bizproc-debugger-automation__status--title" data-role="document-status-title">
					${0}
				</div>
				<div class="bizproc-debugger-automation__status--bg" data-role="document-status-bg" style="background-color: ${0}; border-color: ${0};">
					<span class="bizproc-debugger-automation__status--bg-arrow"></span>
				</div>
			</div>
		`), Helper.getBgColorAdditionalClass(color), title, babelHelpers.classPrivateFieldLooseBase(this, _handleShowStages)[_handleShowStages].bind(this), title, color, color);
	}
	function _handleShowStages2(event) {
	  event.preventDefault();
	  const statusList = this.debugger.getStatusList();
	  const menu = new main_popup.Menu({
	    bindElement: event.target,
	    items: statusList.map(stage => {
	      return {
	        text: stage.NAME,
	        statusId: stage['STATUS_ID'],
	        onclick: babelHelpers.classPrivateFieldLooseBase(this, _handleChangeStatus)[_handleChangeStatus].bind(this)
	      };
	    })
	  });
	  menu.show();
	}
	function _handleChangeStatus2(event, item) {
	  item.getMenuWindow().destroy();
	  this.debugger.setDocumentStatus(item.statusId);
	}
	function _getDocumentStatusTitle2() {
	  const statusId = this.debugger.getDocumentStatus();
	  const statusList = this.debugger.getStatusList();
	  const status = statusList.find(stage => stage['STATUS_ID'] === statusId);
	  return status ? status.NAME || status.TITLE : '?';
	}
	function _getDocumentStatusColor2() {
	  const statusId = this.debugger.getDocumentStatus();
	  const statusList = this.debugger.getStatusList();
	  const status = statusList.find(stage => stage['STATUS_ID'] === statusId);
	  return status ? status.COLOR : '#9DCF00';
	}
	function _onDocumentStatusChanged2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().isShown()) {
	    return;
	  }
	  const automationContentNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="automation-content"]');
	  const loader = new main_loader.Loader({
	    target: automationContentNode
	  });
	  main_core.Dom.addClass(automationContentNode, '--loading');
	  loader.show();
	  this.debugger.loadMainViewInfo().then(() => {
	    const statusTitleNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="document-status-title"]');
	    const statusTitle = babelHelpers.classPrivateFieldLooseBase(this, _getDocumentStatusTitle)[_getDocumentStatusTitle]();
	    statusTitleNode.textContent = statusTitle;
	    statusTitleNode.parentNode.setAttribute('title', statusTitle);
	    const statusBgNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="document-status-bg"]');
	    const color = babelHelpers.classPrivateFieldLooseBase(this, _getDocumentStatusColor)[_getDocumentStatusColor]();
	    main_core.Dom.style(statusBgNode, {
	      backgroundColor: color,
	      borderColor: color
	    });
	    const documentStatusNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="document-status"]');
	    main_core.Dom.removeClass(documentStatusNode, ['--with-border', '--light-color']);
	    main_core.Dom.addClass(documentStatusNode, Helper.getBgColorAdditionalClass(color));
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="triggers-header"]'));
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="triggers"]'));
	    main_core.Dom.prepend(babelHelpers.classPrivateFieldLooseBase(this, _createTriggersNode)[_createTriggersNode](), automationContentNode);
	    const triggersHeaderNode = babelHelpers.classPrivateFieldLooseBase(this, _createTriggersHeaderNode)[_createTriggersHeaderNode]();
	    if (triggersHeaderNode) {
	      bizproc_automation.HelpHint.bindAll(triggersHeaderNode);
	      main_core.Dom.prepend(triggersHeaderNode, automationContentNode);
	    }
	    const tplNode = babelHelpers.classPrivateFieldLooseBase(this, _createTemplateNode)[_createTemplateNode]();
	    main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node].querySelector('[data-role="template"]'), tplNode);
	    const hasTriggers = this.debugger.templateTriggers.length > 0;
	    const hasRobots = !this.debugger.isTemplateEmpty();
	    main_core.Dom[hasTriggers || hasRobots ? 'removeClass' : 'addClass'](babelHelpers.classPrivateFieldLooseBase(this, _node)[_node].querySelector('[data-role="no-triggers"]'), '--active');
	    main_core.Dom[hasRobots ? 'removeClass' : 'addClass'](babelHelpers.classPrivateFieldLooseBase(this, _node)[_node].querySelector('[data-role="no-template"]'), '--active');
	    main_core.Dom.removeClass(automationContentNode, '--loading');
	    loader.destroy();
	  });
	}
	function _onWorkflowEventsChanged2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay]) {
	    return;
	  }
	  const events = event.getData().events;
	  const infoNode = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]().querySelector('[data-role="external-event-info"]');
	  main_core.Dom[events.length ? 'addClass' : 'removeClass'](infoNode, '--active');
	}
	function _onWorkflowTrackAdded2(event) {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateTracker)[_updateTracker](this.debugger.track);
	}
	function _onDocumentValuesUpdated2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().isShown()) {
	    return;
	  }
	  const values = event.getData().values;
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getNode)[_getNode]();
	  Object.keys(values).forEach(key => {
	    const valueNode = node.querySelector(`[data-role="field-value-${key}"]`);
	    if (valueNode) {
	      valueNode.value = values[key] || '';
	    }
	  });
	}
	function _onWorkflowStatusChange2(event) {
	  const status = event.getData().status;
	  const workflowId = event.getData().workflowId;
	  if ([bizproc_automation.WorkflowStatus.COMPLETED, bizproc_automation.WorkflowStatus.TERMINATED].includes(status)) {
	    this.debugger.track.forEach(track => {
	      if (track['WORKFLOW_ID'] === workflowId) {
	        track['WORKFLOW_STATUS'] = bizproc_automation.WorkflowStatus.COMPLETED;
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateTracker)[_updateTracker](this.debugger.track);
	  }
	}
	function _onAfterDocumentFixed2() {
	  const popupContainer = babelHelpers.classPrivateFieldLooseBase(this, _getPopup)[_getPopup]().getPopupContainer();
	  main_core.Dom.removeClass(popupContainer.getElementsByClassName('bizproc-debugger-automation__main-fields')[0], '--disabled');
	  main_core.Dom.removeClass(popupContainer.getElementsByClassName('bizproc-debugger-automation__toolbar')[0], '--disabled');
	  const activeTab = this.debugger.settings.get('tab') === 'log' ? 'log' : 'doc';
	  popupContainer.querySelectorAll([`[data-tab-item="no-document"]`]).forEach(tab => main_core.Dom.removeClass(tab, ['--empty', '--active']));
	  popupContainer.querySelectorAll([`[data-tab-item="${activeTab}"]`]).forEach(tab => main_core.Dom.addClass(tab, '--active'));
	  babelHelpers.classPrivateFieldLooseBase(this, _onDocumentStatusChanged)[_onDocumentStatusChanged]();
	}
	function _setDebuggerState2(state) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay]) {
	    return;
	  }
	  switch (state) {
	    case DebuggerState.Run:
	      babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay].setIcon(ui_buttons.ButtonIcon.PAUSE);
	      babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay].getContainer().nextElementSibling.textContent = main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_PAUSE');
	      break;
	    default:
	      babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay].setIcon(ui_buttons.ButtonIcon.START);
	      babelHelpers.classPrivateFieldLooseBase(this, _buttonPlay)[_buttonPlay].getContainer().nextElementSibling.textContent = main_core.Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_START');
	  }
	}

	class Mode {
	  static isMode(modeId) {
	    return [0, 1].includes(modeId);
	  }
	  static getMode(modeId) {
	    if (modeId === 0) {
	      return Mode.experimental;
	    } else if (modeId === 1) {
	      return Mode.interception;
	    }
	    return null;
	  }
	  static getAllModes() {
	    return {
	      0: Mode.experimental,
	      1: Mode.interception
	    };
	  }
	}
	Mode.experimental = {
	  id: 0,
	  code: 'experimental'
	};
	Mode.interception = {
	  id: 1,
	  code: 'interception'
	};

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _sessionId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sessionId");
	var _documentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentId");
	var _categoryId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryId");
	var _dateExpire = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateExpire");
	var _documentSigned = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentSigned");
	class Document {
	  constructor(options) {
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sessionId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _categoryId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dateExpire, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentSigned, {
	      writable: true,
	      value: ''
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = parseInt(options.Id) >= 0 ? parseInt(options.Id) : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _sessionId)[_sessionId] = main_core.Type.isStringFilled(options.SessionId) ? options.SessionId : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId] = main_core.Type.isStringFilled(options.DocumentId) ? options.DocumentId : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _categoryId)[_categoryId] = main_core.Text.toInteger(options.DocumentCategoryId);
	    babelHelpers.classPrivateFieldLooseBase(this, _dateExpire)[_dateExpire] = Helper.toDate(options.DateExpire);
	    if (options.DocumentSigned) {
	      this.documentSigned = options.DocumentSigned;
	    }
	  }
	  get documentId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId];
	  }
	  get categoryId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _categoryId)[_categoryId];
	  }
	  set categoryId(categoryId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _categoryId)[_categoryId] = main_core.Type.isNumber(categoryId) ? categoryId : 0;
	  }
	  get documentSigned() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentSigned)[_documentSigned];
	  }
	  set documentSigned(documentSigned) {
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSigned)[_documentSigned] = main_core.Type.isStringFilled(documentSigned) ? documentSigned : '';
	  }
	}

	var _id$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _mode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mode");
	var _startedBy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startedBy");
	var _active = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("active");
	var _fixed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fixed");
	var _documents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documents");
	var _shortDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shortDescription");
	var _categoryId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryId");
	var _documentSigned$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentSigned");
	var _finished = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("finished");
	var _pullFinishHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullFinishHandler");
	var _pullDocumentValuesHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullDocumentValuesHandler");
	var _setMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMode");
	var _setDocuments = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDocuments");
	var _innerFinish = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("innerFinish");
	var _handleExternalFinished = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleExternalFinished");
	var _handleExternalDocumentValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleExternalDocumentValues");
	var _handleFinish = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFinish");
	var _updateSession = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSession");
	class Session extends main_core_events.EventEmitter {
	  constructor(_options) {
	    super();
	    Object.defineProperty(this, _updateSession, {
	      value: _updateSession2
	    });
	    Object.defineProperty(this, _handleFinish, {
	      value: _handleFinish2
	    });
	    Object.defineProperty(this, _handleExternalDocumentValues, {
	      value: _handleExternalDocumentValues2
	    });
	    Object.defineProperty(this, _handleExternalFinished, {
	      value: _handleExternalFinished2
	    });
	    Object.defineProperty(this, _innerFinish, {
	      value: _innerFinish2
	    });
	    Object.defineProperty(this, _setDocuments, {
	      value: _setDocuments2
	    });
	    Object.defineProperty(this, _setMode, {
	      value: _setMode2
	    });
	    Object.defineProperty(this, _id$1, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _mode, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _startedBy, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _active, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fixed, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documents, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _shortDescription, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _categoryId$1, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _documentSigned$1, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _finished, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _pullFinishHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pullDocumentValuesHandler, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Bizproc.Debugger.Session');
	    _options = main_core.Type.isPlainObject(_options) ? _options : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1] = _options.Id;
	    babelHelpers.classPrivateFieldLooseBase(this, _setMode)[_setMode](_options.Mode);
	    babelHelpers.classPrivateFieldLooseBase(this, _startedBy)[_startedBy] = parseInt(_options.StartedBy) >= 0 ? parseInt(_options.StartedBy) : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _shortDescription)[_shortDescription] = String(_options.ShortDescription);
	    babelHelpers.classPrivateFieldLooseBase(this, _active)[_active] = Boolean(_options.Active);
	    babelHelpers.classPrivateFieldLooseBase(this, _fixed)[_fixed] = Boolean(_options.Fixed);
	    babelHelpers.classPrivateFieldLooseBase(this, _categoryId$1)[_categoryId$1] = main_core.Text.toInteger(_options.CategoryId);
	    babelHelpers.classPrivateFieldLooseBase(this, _setDocuments)[_setDocuments](_options.Documents);
	    if (this.isActive()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _pullFinishHandler)[_pullFinishHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleExternalFinished)[_handleExternalFinished].bind(this);
	      bizproc_debugger.Manager.Instance.pullHandler.subscribe('sessionFinish', babelHelpers.classPrivateFieldLooseBase(this, _pullFinishHandler)[_pullFinishHandler]);
	      babelHelpers.classPrivateFieldLooseBase(this, _pullDocumentValuesHandler)[_pullDocumentValuesHandler] = babelHelpers.classPrivateFieldLooseBase(this, _handleExternalDocumentValues)[_handleExternalDocumentValues].bind(this);
	      bizproc_debugger.Manager.Instance.pullHandler.subscribe('documentValues', babelHelpers.classPrivateFieldLooseBase(this, _pullDocumentValuesHandler)[_pullDocumentValuesHandler]);
	    }
	  }
	  set documentSigned(documentSigned) {
	    if (this.isFixed() && this.activeDocument) {
	      this.activeDocument.documentSigned = documentSigned;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _documentSigned$1)[_documentSigned$1] = documentSigned;
	  }
	  get documentSigned() {
	    if (this.activeDocument) {
	      const documentSigned = this.activeDocument.documentSigned;
	      if (main_core.Type.isStringFilled(documentSigned)) {
	        return documentSigned;
	      }
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentSigned$1)[_documentSigned$1];
	  }
	  get id() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id$1)[_id$1];
	  }
	  get startedBy() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _startedBy)[_startedBy];
	  }
	  get activeDocument() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _documents)[_documents].length === 1) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _documents)[_documents][0];
	    }
	    return null;
	  }
	  get modeId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode].id;
	  }
	  get shortDescription() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _shortDescription)[_shortDescription];
	  }
	  get initialCategoryId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _categoryId$1)[_categoryId$1];
	  }
	  isActive() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _active)[_active];
	  }
	  isFixed() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fixed)[_fixed];
	  }
	  isSessionStartedByUser(userId) {
	    return this.startedBy === userId;
	  }
	  isAutomation() {
	    return true;
	  }
	  isInterceptionMode() {
	    return this.modeId === Mode.interception.id;
	  }
	  isExperimentalMode() {
	    return this.modeId === Mode.experimental.id;
	  }
	  static start(documentSigned, modeId) {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('bizproc.debugger.startSession', {
	        data: {
	          documentSigned,
	          mode: modeId
	        },
	        analyticsLabel: {
	          automation_select_debug_mode: 'Y',
	          mode_type: Mode.getMode(modeId).code
	        }
	      }).then(response => {
	        const session = new Session(response.data.session);
	        session.documentSigned = response.data.documentSigned;
	        resolve(session);
	      }, reject);
	    });
	  }
	  finish(options = {}) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _innerFinish)[_innerFinish](options).then(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleFinish)[_handleFinish]();
	      return response;
	    });
	  }
	  fixateDocument(id) {
	    return main_core.ajax.runAction('bizproc.debugger.fixateSessionDocument', {
	      data: {
	        documentId: id
	      }
	    }).then(response => {
	      this.documentSigned = response.data.documentSigned;
	      babelHelpers.classPrivateFieldLooseBase(this, _updateSession)[_updateSession](response.data.session);
	      this.emit('onAfterDocumentFixed');
	      return response;
	    });
	  }
	  removeDocuments(ids = []) {
	    return main_core.ajax.runAction('bizproc.debugger.removeSessionDocument', {
	      data: {
	        documentIds: ids
	      }
	    }).then(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateSession)[_updateSession](response.data.session);
	      return response;
	    });
	  }
	}
	function _setMode2(modeId) {
	  modeId = Helper.isNumeric(modeId) ? Number(modeId) : null;
	  if (Mode.isMode(modeId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] = Mode.getMode(modeId);
	  }
	}
	function _setDocuments2(documents) {
	  if (main_core.Type.isArrayFilled(documents)) {
	    documents.forEach(document => {
	      babelHelpers.classPrivateFieldLooseBase(this, _documents)[_documents].push(new Document(document));
	    });
	  }
	  return this;
	}
	function _innerFinish2(options = {}) {
	  return main_core.ajax.runAction('bizproc.debugger.finishDebugSession', {
	    json: {
	      sessionId: this.id,
	      ...options
	    }
	  });
	}
	function _handleExternalFinished2(event) {
	  const sessionId = event.getData().sessionId;
	  if (sessionId === this.id) {
	    babelHelpers.classPrivateFieldLooseBase(this, _handleFinish)[_handleFinish]();
	  }
	}
	function _handleExternalDocumentValues2(event) {
	  if (!this.activeDocument) {
	    return;
	  }
	  const values = event.getData().rawValues;
	  const categoryId = values['CATEGORY_ID'];
	  if (categoryId) {
	    this.activeDocument.categoryId = main_core.Text.toInteger(categoryId);

	    //TODO: refactoring candidate
	    bizproc_debugger.Manager.Instance.requireSetFilter(this);
	  }
	}
	function _handleFinish2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _finished)[_finished]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _finished)[_finished] = true;
	    this.emit('onFinished');
	    this.unsubscribeAll();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _pullFinishHandler)[_pullFinishHandler]) {
	      bizproc_debugger.Manager.Instance.pullHandler.unsubscribe('sessionFinish', babelHelpers.classPrivateFieldLooseBase(this, _pullFinishHandler)[_pullFinishHandler]);
	      babelHelpers.classPrivateFieldLooseBase(this, _pullFinishHandler)[_pullFinishHandler] = null;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _pullDocumentValuesHandler)[_pullDocumentValuesHandler]) {
	      bizproc_debugger.Manager.Instance.pullHandler.unsubscribe('documentValues', babelHelpers.classPrivateFieldLooseBase(this, _pullDocumentValuesHandler)[_pullDocumentValuesHandler]);
	      babelHelpers.classPrivateFieldLooseBase(this, _pullDocumentValuesHandler)[_pullDocumentValuesHandler] = null;
	    }
	  }
	}
	function _updateSession2(options = {}) {
	  if (Object.keys(options).length <= 0) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _active)[_active] = Boolean(options.Active);
	  babelHelpers.classPrivateFieldLooseBase(this, _fixed)[_fixed] = Boolean(options.Fixed);
	  babelHelpers.classPrivateFieldLooseBase(this, _categoryId$1)[_categoryId$1] = main_core.Text.toInteger(options.CategoryId);
	  babelHelpers.classPrivateFieldLooseBase(this, _setDocuments)[_setDocuments](options.Documents);
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11$1,
	  _t12$1,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17;
	var _robot = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("robot");
	var _view = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("view");
	var _currentNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentNode");
	var _currentIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentIndex");
	var _isAfterPreviousRendered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAfterPreviousRendered");
	var _isPauseRendered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPauseRendered");
	var _isActivityBodyRendered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isActivityBodyRendered");
	var _prevRobotTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prevRobotTitle");
	var _getCurrentRobotNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentRobotNode");
	var _renderRobotTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRobotTitle");
	var _renderAfterPrevious = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAfterPrevious");
	var _renderPause = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderPause");
	var _renderDelayInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDelayInterval");
	var _renderCondition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCondition");
	var _renderConditions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderConditions");
	var _renderActivity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActivity");
	var _renderActivityFinish = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActivityFinish");
	var _renderNote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNote");
	var _renderDebugNote = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDebugNote");
	var _renderDebugLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDebugLink");
	class RobotLog {
	  constructor(view, robotInfo) {
	    Object.defineProperty(this, _renderDebugLink, {
	      value: _renderDebugLink2
	    });
	    Object.defineProperty(this, _renderDebugNote, {
	      value: _renderDebugNote2
	    });
	    Object.defineProperty(this, _renderNote, {
	      value: _renderNote2
	    });
	    Object.defineProperty(this, _renderActivityFinish, {
	      value: _renderActivityFinish2
	    });
	    Object.defineProperty(this, _renderActivity, {
	      value: _renderActivity2
	    });
	    Object.defineProperty(this, _renderConditions, {
	      value: _renderConditions2
	    });
	    Object.defineProperty(this, _renderCondition, {
	      value: _renderCondition2
	    });
	    Object.defineProperty(this, _renderDelayInterval, {
	      value: _renderDelayInterval2
	    });
	    Object.defineProperty(this, _renderPause, {
	      value: _renderPause2
	    });
	    Object.defineProperty(this, _renderAfterPrevious, {
	      value: _renderAfterPrevious2
	    });
	    Object.defineProperty(this, _renderRobotTitle, {
	      value: _renderRobotTitle2
	    });
	    Object.defineProperty(this, _getCurrentRobotNode, {
	      value: _getCurrentRobotNode2
	    });
	    Object.defineProperty(this, _robot, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _view, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _currentIndex, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isAfterPreviousRendered, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isPauseRendered, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isActivityBodyRendered, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _prevRobotTitle, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _view)[_view] = view;
	    babelHelpers.classPrivateFieldLooseBase(this, _robot)[_robot] = robotInfo;
	  }
	  get name() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _robot)[_robot].name;
	  }
	  get title() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _robot)[_robot].title;
	  }
	  get delayName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _robot)[_robot].delayName;
	  }
	  get conditionNames() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _robot)[_robot].conditionNames;
	  }
	  getActivitiesName() {
	    let names = [];
	    if (this.name) {
	      names.push(this.name);
	    }
	    if (this.delayName) {
	      names.push(this.delayName);
	    }
	    names = names.concat(this.conditionNames);
	    return names;
	  }
	  set previousRobotTitle(title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _prevRobotTitle)[_prevRobotTitle] = title;
	  }
	  renderTrack(track) {
	    const excludedTypes = [bizproc_automation.TrackingEntry.EXECUTE_ACTIVITY_TYPE, bizproc_automation.TrackingEntry.ATTACHED_ENTITY_TYPE];
	    if (excludedTypes.includes(track.type)) {
	      return;
	    }
	    if (track.name === this.delayName) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderPause)[_renderPause](track);
	    }
	    if (this.conditionNames.includes(track.name)) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isPauseRendered)[_isPauseRendered] === false) {
	        const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderDelayInterval)[_renderDelayInterval](new bizproc_automation.DelayInterval()), node);
	        babelHelpers.classPrivateFieldLooseBase(this, _isPauseRendered)[_isPauseRendered] = true;
	      }
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderCondition)[_renderCondition](track);
	    }
	    if (track.name === this.name) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isPauseRendered)[_isPauseRendered] === false) {
	        const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderDelayInterval)[_renderDelayInterval](new bizproc_automation.DelayInterval()), node);
	        babelHelpers.classPrivateFieldLooseBase(this, _isPauseRendered)[_isPauseRendered] = true;
	      }
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderActivity)[_renderActivity](track);
	    }
	  }
	}
	function _getCurrentRobotNode2(track) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index !== babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex]) {
	    const node = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="bizproc-debugger-automation__log-section">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _renderRobotTitle)[_renderRobotTitle](track.datetime));
	    if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _prevRobotTitle)[_prevRobotTitle]) && babelHelpers.classPrivateFieldLooseBase(this, _isAfterPreviousRendered)[_isAfterPreviousRendered] === false) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAfterPrevious)[_renderAfterPrevious](), node);
	    }
	    main_core.Dom.append(node, babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].logNode);
	    babelHelpers.classPrivateFieldLooseBase(this, _currentNode)[_currentNode] = node;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _currentNode)[_currentNode];
	}
	function _renderRobotTitle2(time) {
	  const message = main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_TITLE', {
	    '#TITLE#': this.title
	  });
	  const node = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
				<div class="bizproc-debugger-automation__log-section--title">${0}</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), AutomationLogView.renderTime(time), main_core.Text.encode(message));
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderAfterPrevious2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _isAfterPreviousRendered)[_isAfterPreviousRendered] = true;
	  const node = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				<div class="bizproc-debugger-automation__log-info">
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text">
							${0}
						</span>
						<span>:</span>
					</div>
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="bizproc-debugger-automation__log-color-box --blue">
							"${0}"
						</span>
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_AFTER_PREVIOUS'), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _prevRobotTitle)[_prevRobotTitle]));
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderPause2(track) {
	  babelHelpers.classPrivateFieldLooseBase(this, _isPauseRendered)[_isPauseRendered] = true;
	  const excludedTypes = [bizproc_automation.TrackingEntry.CLOSE_ACTIVITY_TYPE];

	  // ignore
	  if (excludedTypes.includes(track.type)) {
	    return;
	  }

	  // delay Interval
	  if (track.type === bizproc_automation.TrackingEntry.DEBUG_AUTOMATION_TYPE) {
	    const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	    const note = JSON.parse(track.note);
	    return main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderDelayInterval)[_renderDelayInterval](note), node);
	  }
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	  return main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderNote)[_renderNote](track), node);
	}
	function _renderDelayInterval2(note = {}) {
	  var _note$fieldName;
	  const delayInterval = new bizproc_automation.DelayInterval(note);
	  let name = (_note$fieldName = note.fieldName) != null ? _note$fieldName : new bizproc_automation.DelayIntervalSelector().getBasisField(delayInterval.basis, true).Name;
	  name = name + ' [' + note.fieldValue + ']';
	  const delay = delayInterval.format(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_DELAY_INTERVAL_AT_ONCE'), [{
	    SystemExpression: delayInterval.basis,
	    Name: name
	  }]);
	  const node = main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				<div class="bizproc-debugger-automation__log-info">
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text">
							${0}
						</span>
						<span>:</span>
					</div>
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="bizproc-debugger-automation__log-color-box --dark-blue">
							${0}
						</span>
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_DELAY_INTERVAL_RUN'), main_core.Text.encode(delay));
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderCondition2(track) {
	  const excludedTypes = [bizproc_automation.TrackingEntry.CLOSE_ACTIVITY_TYPE];

	  // ignore
	  if (excludedTypes.includes(track.type)) {
	    return;
	  }
	  if (track.type === bizproc_automation.TrackingEntry.DEBUG_AUTOMATION_TYPE) {
	    const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	    return main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderConditions)[_renderConditions](track), node);
	  }
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	  return main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderNote)[_renderNote](track), node);
	}
	function _renderConditions2(track) {
	  const note = JSON.parse(track.note);
	  const conditionNode = main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
			<div class="bizproc-debugger-automation__log-info">
				<div class="bizproc-debugger-automation__log-info--name">
					<span class="bizproc-debugger-automation__log-info--name-text">
						${0}
					</span>
					<span>:</span> 
				</div> 
			</div>
		`), note.result === 'Y' ? main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION') : main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_FALSE'));
	  Object.keys(note).forEach(key => {
	    if (key === 'result') {
	      return;
	    }
	    let colorCondition = '';
	    if (note.result === note[key]['result']) {
	      colorCondition = note.result === 'Y' ? 'bizproc-debugger-automation__log-color-box --green' : 'bizproc-debugger-automation__log-color-box --orange';
	    }
	    const condition = note[key]['condition'];
	    const field = condition['field'];
	    const fieldValue = note[key]['fieldValue'] ? String(note[key]['fieldValue']) : '';
	    const operator = bizproc_condition.Operator.getOperatorLabel(condition['operator']);
	    const value = condition['value'];
	    const joiner = Helper.getJoinerLabel(note[key]['joiner']);
	    main_core.Dom.append(main_core.Tag.render(_t6$1 || (_t6$1 = _$1`
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="${0}" >
							${0}
							${0}
							${0} 
							${0} 
							${0}
						</span>
					</div>
				`), colorCondition, key === '0' ? '' : main_core.Text.encode(joiner) + ' ', main_core.Text.encode(field) + ' ', '[' + main_core.Text.encode(fieldValue) + '] ', main_core.Text.encode(operator) + ' ', main_core.Text.encode(value)), conditionNode);
	  });
	  const node = main_core.Tag.render(_t7$1 || (_t7$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), conditionNode);
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderActivity2(track) {
	  if (track.type === bizproc_automation.TrackingEntry.CLOSE_ACTIVITY_TYPE) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isActivityBodyRendered)[_isActivityBodyRendered] === false) {
	      const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	      return main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderActivityFinish)[_renderActivityFinish](), node);
	    }
	    return;
	  }

	  // fields
	  babelHelpers.classPrivateFieldLooseBase(this, _isActivityBodyRendered)[_isActivityBodyRendered] = true;
	  const node = babelHelpers.classPrivateFieldLooseBase(this, _getCurrentRobotNode)[_getCurrentRobotNode](track);
	  const renderedNote = babelHelpers.classPrivateFieldLooseBase(this, _renderNote)[_renderNote](track);
	  main_core.Dom.append(renderedNote, node);
	  babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].collapseInfoResults(renderedNote);
	}
	function _renderActivityFinish2() {
	  // tracking-track-2
	  return main_core.Tag.render(_t8$1 || (_t8$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				<div class="bizproc-debugger-automation-log-section-robot-activity">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_FINISH_WITHOUT_SETTINGS'));
	}
	function _renderNote2(track) {
	  if ([bizproc_automation.TrackingEntry.DEBUG_AUTOMATION_TYPE, bizproc_automation.TrackingEntry.DEBUG_ACTIVITY_TYPE].includes(track.type)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderDebugNote)[_renderDebugNote](track);
	  }
	  if ([bizproc_automation.TrackingEntry.DEBUG_LINK_TYPE].includes(track.type)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _renderDebugLink)[_renderDebugLink](track);
	  }
	  const colorBox = [bizproc_automation.TrackingEntry.CANCEL_ACTIVITY_TYPE, bizproc_automation.TrackingEntry.FAULT_ACTIVITY_TYPE, bizproc_automation.TrackingEntry.ERROR_ACTIVITY_TYPE].includes(track.type) ? 'bizproc-debugger-automation__log-color-box --red' : '';
	  const node = main_core.Tag.render(_t9$1 || (_t9$1 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				<div class="bizproc-debugger-automation__log-info--value --first">
					<span class="${0}">
						${0}
					</span>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), colorBox, main_core.Text.encode(track.note).replace(/([^>])\n/g, '$1<br>'));
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderDebugNote2(track) {
	  const note = JSON.parse(track.note);
	  const infoNode = main_core.Tag.render(_t10$1 || (_t10$1 = _$1`<div class="bizproc-debugger-automation__log-info"></div>`));
	  if (note['propertyName']) {
	    main_core.Dom.append(main_core.Tag.render(_t11$1 || (_t11$1 = _$1`
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text" title="${0}">
							${0}
						</span>
						<span>:</span>
					</div>
				`), main_core.Text.encode(note['propertyName']), main_core.Text.encode(note['propertyName'])), infoNode);
	  }
	  main_core.Dom.append(main_core.Tag.render(_t12$1 || (_t12$1 = _$1`
				<div class="bizproc-debugger-automation__log-info--value ${0}">
					<div class="bizproc-debugger-automation__log--variable-height" data-role="info-result">
						<div>
							${0}
						</div>
					</div>
					<div data-role="more-info-result" style="display:none;">
						<span class="bizproc-debugger-automation__log-info--more">
							${0}
						</span>
					</div>
				</div>
			`), note['propertyName'] ? '' : '--first', note['propertyValue'] ? main_core.Text.encode(note['propertyValue']).replace(/([^>])\n/g, '$1<br>') : '', main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_MORE_INFORMATION'))), infoNode);
	  const node = main_core.Tag.render(_t13 || (_t13 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), infoNode);
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}
	function _renderDebugLink2(track) {
	  const note = JSON.parse(track.note);
	  const infoNode = main_core.Tag.render(_t14 || (_t14 = _$1`<div class="bizproc-debugger-automation__log-info"></div>`));
	  const label = note['propertyLinkName'] || note['propertyValue'];
	  const link = note['propertyValue'];
	  if (note['propertyName']) {
	    main_core.Dom.append(main_core.Tag.render(_t15 || (_t15 = _$1`
					<div class="bizproc-debugger-automation__log-info--name">
						<span class="bizproc-debugger-automation__log-info--name-text" title="${0}">
							${0}
						</span>
						<span>:</span>
					</div>
				`), main_core.Text.encode(note['propertyName']), main_core.Text.encode(note['propertyName'])), infoNode);
	  }
	  main_core.Dom.append(main_core.Tag.render(_t16 || (_t16 = _$1`
				<div class="bizproc-debugger-automation__log-info--value ${0}">
					<div class="bizproc-debugger-automation__log--variable-height" data-role="info-result">
						<a href="${0}" target="_blank">
							${0}
						</a>
					</div>
				</div>
			`), note['propertyName'] ? '' : '--first', main_core.Text.encode(link), label), infoNode);
	  const node = main_core.Tag.render(_t17 || (_t17 = _$1`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].renderIndex(), infoNode);
	  babelHelpers.classPrivateFieldLooseBase(this, _currentIndex)[_currentIndex] = babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].index;
	  return node;
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$2;
	var _view$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("view");
	var _condition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("condition");
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _track = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("track");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	var _renderCondition$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCondition");
	class TriggerLog {
	  constructor(view) {
	    Object.defineProperty(this, _renderCondition$1, {
	      value: _renderCondition2$1
	    });
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _view$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _condition, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _track, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1] = view;
	  }
	  addTrack(track) {
	    if (track.type === bizproc_automation.TrackingEntry.DEBUG_AUTOMATION_TYPE && track.name === 'TRIGGER_LOG') {
	      babelHelpers.classPrivateFieldLooseBase(this, _condition)[_condition] = JSON.parse(track.note);
	      babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = track.title;
	      babelHelpers.classPrivateFieldLooseBase(this, _track)[_track] = track;
	    }
	    return this;
	  }
	  render() {
	    const node = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="bizproc-debugger-automation__log-section">
				${0}
				${0}
				<div class="bizproc-debugger-automation__log-section--row">
					${0}
					<div class="bizproc-debugger-automation__log-info--value --first">
						<span>
							${0}
						</span>
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle](), babelHelpers.classPrivateFieldLooseBase(this, _renderCondition$1)[_renderCondition$1](), babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].renderIndex(), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_TRIGGER_FINISH')));
	    main_core.Dom.append(node, babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].logNode);
	  }
	}
	function _renderTitle2() {
	  const message = main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_TRIGGER_TITLE', {
	    '#TITLE#': babelHelpers.classPrivateFieldLooseBase(this, _title)[_title]
	  });
	  return main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
				<div class="bizproc-debugger-automation__log-section--title">${0}</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].renderIndex(), AutomationLogView.renderTime(babelHelpers.classPrivateFieldLooseBase(this, _track)[_track].datetime), main_core.Text.encode(message));
	}
	function _renderCondition2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _condition)[_condition] || Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _condition)[_condition]).length <= 0) {
	    return '';
	  }
	  const note = babelHelpers.classPrivateFieldLooseBase(this, _condition)[_condition];
	  const conditionNode = main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
			<div class="bizproc-debugger-automation__log-info">
				<div class="bizproc-debugger-automation__log-info--name">
					<span class="bizproc-debugger-automation__log-info--name-text">
						${0}
					</span>
					<span>:</span> 
				</div> 
			</div>
		`), main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION'));
	  Object.keys(note).forEach(key => {
	    const colorCondition = note[key]['result'] === 'Y' ? 'bizproc-debugger-automation__log-color-box --green' : '';
	    const condition = note[key]['condition'];
	    //const object = Helper.getFieldObjectLabel(condition['object']);
	    const field = condition['field'];
	    const fieldValue = note[key]['fieldValue'] ? String(note[key]['fieldValue']) : '';
	    const operator = bizproc_condition.Operator.getOperatorLabel(condition['operator']);
	    const value = condition['value'];
	    const joiner = Helper.getJoinerLabel(note[key]['joiner']);
	    main_core.Dom.append(main_core.Tag.render(_t4$2 || (_t4$2 = _$2`
					<div class="bizproc-debugger-automation__log-info--value">
						<span class="${0}" >
							${0}
							${0}
							${0}
							${0}
							${0}
						</span>
					</div>
				`), colorCondition, key === '0' ? '' : main_core.Text.encode(joiner) + ' ', main_core.Text.encode(field) + ' ', '[' + main_core.Text.encode(fieldValue) + '] ', main_core.Text.encode(operator) + ' ', main_core.Text.encode(value)), conditionNode);
	  });
	  return main_core.Tag.render(_t5$2 || (_t5$2 = _$2`
			<div class="bizproc-debugger-automation__log-section--row">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].renderIndex(), conditionNode);
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$2,
	  _t7$2;
	var _debuggerInstance$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("debuggerInstance");
	var _workflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _activityRenderer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("activityRenderer");
	var _documentStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentStatus");
	var _categoryName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("categoryName");
	var _statusSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("statusSettings");
	var _node$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _index = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("index");
	var _trackId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("trackId");
	var _poolTrack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("poolTrack");
	var _poolWorkflowRobots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("poolWorkflowRobots");
	var _isRendering = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRendering");
	var _NUMBER_OF_LINES_TO_SHOW_IN_PIXELS = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("NUMBER_OF_LINES_TO_SHOW_IN_PIXELS");
	var _shouldScrollToBottom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldScrollToBottom");
	var _shouldLoadPreviousLog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldLoadPreviousLog");
	var _onTrackAddedHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTrackAddedHandler");
	var _onChangeTabHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeTabHandler");
	var _onSessionFinishedHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onSessionFinishedHandler");
	var _autoScrollHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoScrollHandler");
	var _scrollAnimationId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scrollAnimationId");
	var _loadPreviousLog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadPreviousLog");
	var _loadWorkflowRobotsByWorkflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadWorkflowRobotsByWorkflowId");
	var _onAfterGetLog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterGetLog");
	var _renderStartDebugLog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStartDebugLog");
	var _renderStartedDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStartedDate");
	var _renderLegend = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLegend");
	var _renderCategoryChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCategoryChange");
	var _onChangeTab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeTab");
	var _bindAutoScroll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindAutoScroll");
	var _scrollToBottom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("scrollToBottom");
	var _animateScroll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("animateScroll");
	var _cancelAnimateScroll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancelAnimateScroll");
	var _askScrollToBottom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("askScrollToBottom");
	var _clearWorkflowRobots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearWorkflowRobots");
	var _onSessionFinished = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onSessionFinished");
	class AutomationLogView {
	  // 3 lines

	  constructor(debuggerInstance) {
	    Object.defineProperty(this, _onSessionFinished, {
	      value: _onSessionFinished2
	    });
	    Object.defineProperty(this, _clearWorkflowRobots, {
	      value: _clearWorkflowRobots2
	    });
	    Object.defineProperty(this, _askScrollToBottom, {
	      value: _askScrollToBottom2
	    });
	    Object.defineProperty(this, _cancelAnimateScroll, {
	      value: _cancelAnimateScroll2
	    });
	    Object.defineProperty(this, _animateScroll, {
	      value: _animateScroll2
	    });
	    Object.defineProperty(this, _scrollToBottom, {
	      value: _scrollToBottom2
	    });
	    Object.defineProperty(this, _bindAutoScroll, {
	      value: _bindAutoScroll2
	    });
	    Object.defineProperty(this, _onChangeTab, {
	      value: _onChangeTab2
	    });
	    Object.defineProperty(this, _renderCategoryChange, {
	      value: _renderCategoryChange2
	    });
	    Object.defineProperty(this, _renderLegend, {
	      value: _renderLegend2
	    });
	    Object.defineProperty(this, _renderStartedDate, {
	      value: _renderStartedDate2
	    });
	    Object.defineProperty(this, _renderStartDebugLog, {
	      value: _renderStartDebugLog2
	    });
	    Object.defineProperty(this, _onAfterGetLog, {
	      value: _onAfterGetLog2
	    });
	    Object.defineProperty(this, _loadWorkflowRobotsByWorkflowId, {
	      value: _loadWorkflowRobotsByWorkflowId2
	    });
	    Object.defineProperty(this, _loadPreviousLog, {
	      value: _loadPreviousLog2
	    });
	    Object.defineProperty(this, _debuggerInstance$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _workflowId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _activityRenderer, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _documentStatus, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _categoryName, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _statusSettings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _node$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _index, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _trackId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _poolTrack, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _poolWorkflowRobots, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _isRendering, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _shouldScrollToBottom, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _shouldLoadPreviousLog, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _onTrackAddedHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onChangeTabHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onSessionFinishedHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _autoScrollHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _scrollAnimationId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$1)[_debuggerInstance$1] = debuggerInstance;
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = this.debugger.workflowId;
	    const template = this.debugger.getTemplate();
	    this.initializeWorkflowRobotsRenderer(template ? template['ROBOTS'] : []);
	    if (this.debugger.session.isActive()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _onTrackAddedHandler)[_onTrackAddedHandler] = this.onTrackAdded.bind(this);
	      this.debugger.subscribe('onWorkflowTrackAdded', babelHelpers.classPrivateFieldLooseBase(this, _onTrackAddedHandler)[_onTrackAddedHandler]);
	      babelHelpers.classPrivateFieldLooseBase(this, _onChangeTabHandler)[_onChangeTabHandler] = babelHelpers.classPrivateFieldLooseBase(this, _onChangeTab)[_onChangeTab].bind(this);
	      this.debugger.getMainView().subscribe('onChangeTab', babelHelpers.classPrivateFieldLooseBase(this, _onChangeTabHandler)[_onChangeTabHandler]);
	      babelHelpers.classPrivateFieldLooseBase(this, _onSessionFinishedHandler)[_onSessionFinishedHandler] = babelHelpers.classPrivateFieldLooseBase(this, _onSessionFinished)[_onSessionFinished].bind(this);
	      this.debugger.session.subscribeOnce('onFinished', babelHelpers.classPrivateFieldLooseBase(this, _onSessionFinishedHandler)[_onSessionFinishedHandler]);
	    }
	  }
	  get debugger() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$1)[_debuggerInstance$1];
	  }
	  get index() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _index)[_index];
	  }
	  get logNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1] = main_core.Tag.render(_t$3 || (_t$3 = _$3`<div data-role="log" class="bizproc-debugger-tab__log"></div>`));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1];
	  }
	  initializeWorkflowRobotsRenderer(workflowRobots = [], workflowId = null) {
	    if (!workflowId && !babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]) {
	      return;
	    }
	    if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][workflowId != null ? workflowId : babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][workflowId != null ? workflowId : babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]] = {};
	    }
	    if (main_core.Type.isArrayFilled(workflowRobots)) {
	      let prevRobotTitle = null;
	      for (let i = 0; i < workflowRobots.length; ++i) {
	        var _robot$data$DelayName;
	        const robot = new bizproc_automation.Robot({});
	        robot.init(workflowRobots[i], bizproc_automation.ViewMode.none());
	        const robotLogger = new RobotLog(this, {
	          name: robot.getId(),
	          title: robot.getTitle(),
	          delayName: (_robot$data$DelayName = robot.data.DelayName) != null ? _robot$data$DelayName : null,
	          conditionNames: robot.getCondition().conditionNamesList
	        });
	        if (robot.isExecuteAfterPrevious() && prevRobotTitle) {
	          robotLogger.previousRobotTitle = prevRobotTitle;
	        }
	        prevRobotTitle = robot.getTitle();
	        robotLogger.getActivitiesName().forEach(activityName => {
	          babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][workflowId != null ? workflowId : babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]][activityName] = robotLogger;
	        });
	      }
	    }
	  }

	  // region LOAD LOG

	  shouldLoadPreviousLog(should) {
	    babelHelpers.classPrivateFieldLooseBase(this, _shouldLoadPreviousLog)[_shouldLoadPreviousLog] = should;
	    return this;
	  }
	  setPreviousLog(data = {
	    logs: [],
	    workflowRobots: {}
	  }) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onAfterGetLog)[_onAfterGetLog](data);
	    return this;
	  }
	  // endregion

	  // region RENDER LOG

	  render() {
	    if (this.logNode.children.length <= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering] = true;
	      if (babelHelpers.classPrivateFieldLooseBase(this, _shouldLoadPreviousLog)[_shouldLoadPreviousLog]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _loadPreviousLog)[_loadPreviousLog]().then(() => {
	          this.startRendering();
	        });
	      } else {
	        this.startRendering();
	      }
	    }
	    return this.logNode;
	  }
	  renderTo(element) {
	    main_core.Dom.append(this.logNode, element);
	    babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering] = true;
	    this.startRendering();
	    return this;
	  }
	  startRendering() {
	    const track = babelHelpers.classPrivateFieldLooseBase(this, _poolTrack)[_poolTrack].shift();
	    if (main_core.Type.isUndefined(track)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _askScrollToBottom)[_askScrollToBottom]();
	      babelHelpers.classPrivateFieldLooseBase(this, _bindAutoScroll)[_bindAutoScroll](babelHelpers.classPrivateFieldLooseBase(this, _shouldScrollToBottom)[_shouldScrollToBottom]);
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] !== track.workflowId) {
	      if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][track.workflowId])) {
	        if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId])) {
	          babelHelpers.classPrivateFieldLooseBase(this, _loadWorkflowRobotsByWorkflowId)[_loadWorkflowRobotsByWorkflowId](track);
	          this.renderTrack(track);
	          babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = track.workflowId;
	          return;
	        }
	        this.initializeWorkflowRobotsRenderer(babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId], track.workflowId);
	      }
	    }
	    this.renderTrack(track);
	    this.startRendering();
	  }
	  renderIndex() {
	    babelHelpers.classPrivateFieldLooseBase(this, _index)[_index]++;
	    return main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="bizproc-debugger-automation__log--index" data-role="index">${0}</div>
		`), String(babelHelpers.classPrivateFieldLooseBase(this, _index)[_index]).padStart(3, '0'));
	  }
	  static renderTime(datetime) {
	    datetime = Helper.toDate(datetime);
	    return main_core.Tag.render(_t3$3 || (_t3$3 = _$3`
			<div class="bizproc-debugger-automation__log--time">
				[${0}]
			</div>
		`), main_core.Text.encode(Helper.formatDate('H:i:s', datetime)));
	  }

	  // endregion

	  // region status log

	  renderStatusChange(track) {
	    const parsedTrackNote = JSON.parse(track.note);
	    if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _documentStatus)[_documentStatus])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _documentStatus)[_documentStatus] = parsedTrackNote['STATUS_ID'];
	      babelHelpers.classPrivateFieldLooseBase(this, _statusSettings)[_statusSettings] = parsedTrackNote;
	      return;
	    }
	    const sourceStage = this.getStatusSettings(babelHelpers.classPrivateFieldLooseBase(this, _documentStatus)[_documentStatus]);
	    const destinationStage = parsedTrackNote;
	    const node = main_core.Tag.render(_t4$3 || (_t4$3 = _$3`
			<div class="bizproc-debugger-automation__log-section">
				<div class="bizproc-debugger-automation__log-section--row">
					${0}
					${0}
					<div class="bizproc-debugger-automation__status--change-info">
						<div class="bizproc-debugger-automation__status --log-status ${0}" title="${0}"> 
							<div class="bizproc-debugger-automation__status--title">${0}</div>
							<div class="bizproc-debugger-automation__status--bg" style="background-color: ${0}; border-color: ${0};">
								<span class="bizproc-debugger-automation__status--bg-arrow"></span>
							</div>
						</div>
						<div class="bizproc-debugger-automation__status--robot-change-arrow"></div>
						<div class="bizproc-debugger-automation__status --log-status ${0}" title="${0}"> 
							<div class="bizproc-debugger-automation__status--title">${0}</div>
							<div class="bizproc-debugger-automation__status--bg" style="background-color: ${0}; border-color: ${0};">
								<span class="bizproc-debugger-automation__status--bg-arrow"></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		`), this.renderIndex(), AutomationLogView.renderTime(track.datetime), Helper.getBgColorAdditionalClass(sourceStage['COLOR']), main_core.Text.encode(sourceStage['NAME']), main_core.Text.encode(sourceStage['NAME']), sourceStage['COLOR'], sourceStage['COLOR'], Helper.getBgColorAdditionalClass(destinationStage['COLOR']), main_core.Text.encode(destinationStage['NAME']), main_core.Text.encode(destinationStage['NAME']), destinationStage.COLOR, destinationStage.COLOR);
	    main_core.Dom.append(node, this.logNode);
	    babelHelpers.classPrivateFieldLooseBase(this, _documentStatus)[_documentStatus] = parsedTrackNote['STATUS_ID'];
	    babelHelpers.classPrivateFieldLooseBase(this, _statusSettings)[_statusSettings] = parsedTrackNote;
	  }
	  getStatusSettings() {
	    if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _statusSettings)[_statusSettings])) {
	      return {
	        NAME: '',
	        COLOR: 'AEF2F9'
	      };
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _statusSettings)[_statusSettings];
	  }
	  // endregion

	  // region TRACK
	  onTrackAdded(event) {
	    const entryBuilder = new bizproc_automation.TrackingEntryBuilder();
	    entryBuilder.setLogEntry(event.getData().row);
	    this.addTrack(entryBuilder.build());
	  }
	  addTrack(track) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering]) {
	      this.renderTrack(track);
	      babelHelpers.classPrivateFieldLooseBase(this, _askScrollToBottom)[_askScrollToBottom]();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _poolTrack)[_poolTrack].push(track);
	  }
	  renderTrack(track) {
	    var _babelHelpers$classPr;
	    if (track.id <= babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId]) {
	      return;
	    }
	    if (!Object.keys((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][track.workflowId]) != null ? _babelHelpers$classPr : {}).includes(track.name)) {
	      if (track.name === 'SESSION_LEGEND') {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderStartDebugLog)[_renderStartDebugLog](track);
	        babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId] = track.id;
	      } else if (track.name === 'STATUS_CHANGED') {
	        this.renderStatusChange(track);
	        babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId] = track.id;
	      } else if (track.name === 'CATEGORY_CHANGED') {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderCategoryChange)[_renderCategoryChange](track);
	        babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId] = track.id;
	      } else if (track.name === 'TRIGGER_LOG') {
	        new TriggerLog(this).addTrack(track).render();
	        babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId] = track.id;
	      } else if (track.name === 'Template' && track.type === bizproc_automation.TrackingEntry.EXECUTE_ACTIVITY_TYPE) {
	        if (main_core.Type.isUndefined(babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId]) && babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering] === false) {
	          babelHelpers.classPrivateFieldLooseBase(this, _isRendering)[_isRendering] = true;
	          babelHelpers.classPrivateFieldLooseBase(this, _loadWorkflowRobotsByWorkflowId)[_loadWorkflowRobotsByWorkflowId](track);
	        }
	      } else if (track.name === 'Template' && track.type === bizproc_automation.TrackingEntry.CLOSE_ACTIVITY_TYPE) {
	        babelHelpers.classPrivateFieldLooseBase(this, _clearWorkflowRobots)[_clearWorkflowRobots](track.workflowId);
	      }
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][track.workflowId][track.name].renderTrack(track);
	    babelHelpers.classPrivateFieldLooseBase(this, _trackId)[_trackId] = track.id;
	  }
	  //endregion

	  // region ON CHANGE TAB: scrollToBottom, collapseInfoResults

	  collapseInfoResults(node) {
	    if (!node) {
	      node = this.logNode;
	    }
	    const infoResults = node.querySelectorAll('[data-role="info-result"]');
	    infoResults.forEach(infoNode => {
	      if (infoNode.firstElementChild.clientHeight > babelHelpers.classPrivateFieldLooseBase(this.constructor, _NUMBER_OF_LINES_TO_SHOW_IN_PIXELS)[_NUMBER_OF_LINES_TO_SHOW_IN_PIXELS]) {
	        const moreInfoNode = infoNode.parentNode.querySelector('[data-role="more-info-result"]');
	        main_core.Event.bind(moreInfoNode, 'click', () => {
	          main_core.Dom.style(infoNode, 'height', infoNode.firstElementChild.clientHeight + 'px');
	          main_core.Dom.style(moreInfoNode, 'display', 'none');
	        });
	        main_core.Event.bind(infoNode, 'transitionend', () => {
	          main_core.Dom.style(infoNode, 'height', null);
	        });
	        main_core.Dom.style(infoNode, 'height', babelHelpers.classPrivateFieldLooseBase(this.constructor, _NUMBER_OF_LINES_TO_SHOW_IN_PIXELS)[_NUMBER_OF_LINES_TO_SHOW_IN_PIXELS] + 'px');
	        main_core.Dom.style(moreInfoNode, 'display', 'block');
	      }
	    });
	    return this;
	  }
	  shouldScrollToBottom(should) {
	    babelHelpers.classPrivateFieldLooseBase(this, _shouldScrollToBottom)[_shouldScrollToBottom] = should;
	    return this;
	  }
	}
	function _loadPreviousLog2() {
	  return new Promise(resolve => {
	    this.debugger.loadAllLog().then(data => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onAfterGetLog)[_onAfterGetLog](data);
	      resolve(this);
	    }, () => {
	      resolve(this);
	    });
	  });
	}
	function _loadWorkflowRobotsByWorkflowId2(track) {
	  this.debugger.loadRobotsByWorkflowId(track.workflowId).then(data => {
	    babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId] = data.workflowRobots;
	    this.initializeWorkflowRobotsRenderer(babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId], track.workflowId);
	    this.startRendering();
	  }, () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][track.workflowId] = [];
	    console.info('session has no workflowId from track:', track);
	    this.startRendering();
	  });
	}
	function _onAfterGetLog2(data) {
	  const logFromDB = [];
	  const builder = new bizproc_automation.TrackingEntryBuilder();
	  if (main_core.Type.isArrayFilled(data['logs'])) {
	    data['logs'].forEach(item => {
	      logFromDB.push(builder.setLogEntry(item).build());
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _poolTrack)[_poolTrack] = logFromDB.concat(babelHelpers.classPrivateFieldLooseBase(this, _poolTrack)[_poolTrack]);
	  babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots] = Object.assign(data['workflowRobots'], babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots]);
	}
	function _renderStartDebugLog2(track) {
	  babelHelpers.classPrivateFieldLooseBase(this, _renderStartedDate)[_renderStartedDate](track);
	  babelHelpers.classPrivateFieldLooseBase(this, _renderLegend)[_renderLegend](track);
	}
	function _renderStartedDate2(track) {
	  const startedDate = Helper.toDate(track.datetime);
	  const dateNode = main_core.Tag.render(_t5$3 || (_t5$3 = _$3`
			<div class="bizproc-debugger-automation__log--date">
				<div class="bizproc-debugger-automation__log--date-text">${0}</div>
			</div>
		`), main_core.Text.encode(Helper.formatDate('j F Y', startedDate)));
	  main_core.Dom.append(dateNode, this.logNode);
	}
	function _renderLegend2(track) {
	  const description = JSON.parse(track.note)['propertyValue'];

	  // separator <div class="bizproc-debugger-automation__log-separator"></div>

	  const descriptionNode = main_core.Tag.render(_t6$2 || (_t6$2 = _$3`
			<div class="bizproc-debugger-automation__log-section">
				<div class="bizproc-debugger-automation__log-section--row">
					${0}
					${0}
					<div>${0}</div>
				</div>
			</div>
		`), this.renderIndex(), AutomationLogView.renderTime(track.datetime), main_core.Text.encode(description));
	  main_core.Dom.append(descriptionNode, this.logNode);
	}
	function _renderCategoryChange2(track) {
	  const categoryName = JSON.parse(track.note)['propertyValue'];
	  if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName] = categoryName;
	    return;
	  }
	  const descriptionNode = main_core.Tag.render(_t7$2 || (_t7$2 = _$3`
			<div>
				<div class="bizproc-debugger-automation__log-separator"></div>
				<div class="bizproc-debugger-automation__log-section">
					<div class="bizproc-debugger-automation__log-section--row">
						${0}
						${0}
						<div>
							${0}
						</div>
					</div>
				</div>
			</div>
		`), this.renderIndex(), AutomationLogView.renderTime(track.datetime), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_CATEGORY_CHANGE_MSGVER_1', {
	    '#SOURCE_CATEGORY#': babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName],
	    '#DESTINATION_CATEGORY#': categoryName
	  })));
	  main_core.Dom.append(descriptionNode, this.logNode);
	  babelHelpers.classPrivateFieldLooseBase(this, _categoryName)[_categoryName] = categoryName;
	}
	function _onChangeTab2(event) {
	  if (event.getData().tab === 'log') {
	    this.collapseInfoResults();
	    babelHelpers.classPrivateFieldLooseBase(this, _askScrollToBottom)[_askScrollToBottom]();
	  }
	}
	function _bindAutoScroll2(state) {
	  if (!this.logNode.parentNode) {
	    return;
	  }
	  const scrollNode = this.logNode.parentNode;
	  if (state) {
	    babelHelpers.classPrivateFieldLooseBase(this, _autoScrollHandler)[_autoScrollHandler] = () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId]) {
	        return;
	      }
	      const scrollMax = scrollNode.scrollHeight - scrollNode.clientHeight;
	      babelHelpers.classPrivateFieldLooseBase(this, _shouldScrollToBottom)[_shouldScrollToBottom] = scrollNode.scrollTop >= scrollMax - babelHelpers.classPrivateFieldLooseBase(this.constructor, _NUMBER_OF_LINES_TO_SHOW_IN_PIXELS)[_NUMBER_OF_LINES_TO_SHOW_IN_PIXELS];
	    };
	    main_core.Event.bind(scrollNode, 'scroll', babelHelpers.classPrivateFieldLooseBase(this, _autoScrollHandler)[_autoScrollHandler]);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _autoScrollHandler)[_autoScrollHandler]) {
	    main_core.Event.unbind(scrollNode, 'scroll', babelHelpers.classPrivateFieldLooseBase(this, _autoScrollHandler)[_autoScrollHandler]);
	  }
	}
	function _scrollToBottom2() {
	  if (!this.logNode.parentNode) {
	    return;
	  }
	  const scrollNode = this.logNode.parentNode;
	  const from = scrollNode.scrollTop;
	  const to = scrollNode.scrollHeight - scrollNode.clientHeight;
	  babelHelpers.classPrivateFieldLooseBase(this, _animateScroll)[_animateScroll](scrollNode, from, to);
	}
	function _animateScroll2(element, start, end) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cancelAnimateScroll)[_cancelAnimateScroll]();
	  const increment = 20;
	  const duration = 500;
	  const diff = end - start;
	  let currentPosition = 0;
	  const requestFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
	    return window.setTimeout(callback, 1000 / 60);
	  };
	  const animateScroll = () => {
	    currentPosition += increment;
	    element.scrollTop = easeInOutQuad(currentPosition, start, diff, duration);
	    if (currentPosition < duration) {
	      babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId] = requestFrame(animateScroll);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId] = null;
	    }
	  };
	  return animateScroll();
	}
	function _cancelAnimateScroll2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId]) {
	    const cancelFrame = window.cancelAnimationFrame || window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame || function (id) {
	      clearTimeout(id);
	    };
	    cancelFrame(babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId]);
	    babelHelpers.classPrivateFieldLooseBase(this, _scrollAnimationId)[_scrollAnimationId] = null;
	  }
	}
	function _askScrollToBottom2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _shouldScrollToBottom)[_shouldScrollToBottom]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _scrollToBottom)[_scrollToBottom]();
	  }
	}
	function _clearWorkflowRobots2(workflowId) {
	  delete babelHelpers.classPrivateFieldLooseBase(this, _poolWorkflowRobots)[_poolWorkflowRobots][workflowId];
	  delete babelHelpers.classPrivateFieldLooseBase(this, _activityRenderer)[_activityRenderer][workflowId];
	}
	function _onSessionFinished2() {
	  this.debugger.unsubscribe('onWorkflowTrackAdded', babelHelpers.classPrivateFieldLooseBase(this, _onTrackAddedHandler)[_onTrackAddedHandler]);
	  this.debugger.getMainView().unsubscribe('onChangeTab', babelHelpers.classPrivateFieldLooseBase(this, _onChangeTabHandler)[_onChangeTabHandler]);
	}
	Object.defineProperty(AutomationLogView, _NUMBER_OF_LINES_TO_SHOW_IN_PIXELS, {
	  writable: true,
	  value: 50
	});
	const easeInOutQuad = function (current, start, diff, duration) {
	  current /= duration / 2;
	  if (current < 1) {
	    return diff / 2 * current * current + start;
	  }
	  current--;
	  return -diff / 2 * (current * (current - 2) - 1) + start;
	};

	var _guide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("guide");
	var _getHtmlTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHtmlTitle");
	class ActionPanelGuide {
	  constructor(options) {
	    Object.defineProperty(this, _guide, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _guide)[_guide] = new ui_tour.Guide({
	      steps: [{
	        target: options.target,
	        title: babelHelpers.classPrivateFieldLooseBase(ActionPanelGuide, _getHtmlTitle)[_getHtmlTitle](options.title) || '',
	        text: options.text || '',
	        //article: options.article,
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'warning'
	        }
	      }],
	      onEvents: true
	    });
	  }
	  start() {
	    babelHelpers.classPrivateFieldLooseBase(this, _guide)[_guide].getPopup().setWidth(370); //some magic ^_^
	    babelHelpers.classPrivateFieldLooseBase(this, _guide)[_guide].showNextStep();
	  }
	  finish() {
	    babelHelpers.classPrivateFieldLooseBase(this, _guide)[_guide].close();
	  }
	}
	function _getHtmlTitle2(title) {
	  if (title) {
	    return `
				<div class="bizproc__action-panel-guide">
					<div class="bizproc__action-panel-guide--title --warning-icon">${main_core.Text.encode(title)}</div>
				</div>
			`;
	  }
	  return null;
	}
	Object.defineProperty(ActionPanelGuide, _getHtmlTitle, {
	  value: _getHtmlTitle2
	});

	var _actionPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionPanel");
	var _grid = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("grid");
	var _guides = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("guides");
	var _debuggerInstance$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("debuggerInstance");
	var _appendItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendItems");
	var _onHideActionPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onHideActionPanel");
	var _getRemoveEntityActionText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRemoveEntityActionText");
	var _getCheckedIdsInBpStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCheckedIdsInBpStyle");
	var _handleRejectResponse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleRejectResponse");
	class CustomCrmActionPanel {
	  constructor(grid, debuggerInstance) {
	    Object.defineProperty(this, _handleRejectResponse, {
	      value: _handleRejectResponse2
	    });
	    Object.defineProperty(this, _getCheckedIdsInBpStyle, {
	      value: _getCheckedIdsInBpStyle2
	    });
	    Object.defineProperty(this, _getRemoveEntityActionText, {
	      value: _getRemoveEntityActionText2
	    });
	    Object.defineProperty(this, _onHideActionPanel, {
	      value: _onHideActionPanel2
	    });
	    Object.defineProperty(this, _appendItems, {
	      value: _appendItems2
	    });
	    Object.defineProperty(this, _actionPanel, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _grid, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _guides, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _debuggerInstance$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid] = grid;
	    babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2] = debuggerInstance;
	    babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel] = new BX.UI.ActionPanel({
	      removeLeftPosition: true,
	      maxHeight: 58,
	      parentPosition: 'bottom',
	      autoHide: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _appendItems)[_appendItems]();
	    const onHideActionPanelHandler = babelHelpers.classPrivateFieldLooseBase(this, _onHideActionPanel)[_onHideActionPanel].bind(this);
	    main_core_events.EventEmitter.subscribe(babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel], 'BX.UI.ActionPanel:hidePanel', onHideActionPanelHandler);
	  }
	  get actionPanel() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel];
	  }
	  fixEntityAction() {
	    const checkedIds = babelHelpers.classPrivateFieldLooseBase(this, _getCheckedIdsInBpStyle)[_getCheckedIdsInBpStyle]();
	    if (checkedIds.length !== 1) {
	      const guide = new ActionPanelGuide({
	        target: this.actionPanel.getItemById('fix_entity').layout.container,
	        title: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FIX_DEAL_COUNT_ERROR_TITLE'),
	        article: 'limit_office_bp_designer' // todo: replace,
	      });

	      babelHelpers.classPrivateFieldLooseBase(this, _guides)[_guides].push(guide);
	      guide.start();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].session.fixateDocument(checkedIds[0]).then(() => {
	      this.stopActionPanel();
	      bizproc_debugger.Manager.Instance.requireSetFilter(babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].session, true);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].settings.get('popup-collapsed')) {
	        babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].getMainView().showExpanded();
	      }
	    }, response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse)[_handleRejectResponse](response, 'fix_entity');
	    });
	  }
	  removeEntityAction() {
	    const checkedIds = babelHelpers.classPrivateFieldLooseBase(this, _getCheckedIdsInBpStyle)[_getCheckedIdsInBpStyle]();
	    babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].session.removeDocuments(checkedIds).then(() => {
	      this.actionPanel.hidePanel();
	      babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid].reload();
	    }, response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse)[_handleRejectResponse](response, 'remove_entity');
	    });
	  }
	  stopActionPanel() {
	    this.actionPanel.hidePanel();
	    babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid].resetActionPanel();
	    babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid].stopActionPanel();
	  }
	}
	function _appendItems2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel].appendItem({
	    id: 'fix_entity',
	    text: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FIX_DEAL_ACTION_1'),
	    onclick: this.fixEntityAction.bind(this)
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel].appendItem({
	    id: 'remove_entity',
	    text: babelHelpers.classPrivateFieldLooseBase(this, _getRemoveEntityActionText)[_getRemoveEntityActionText](),
	    onclick: this.removeEntityAction.bind(this)
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _actionPanel)[_actionPanel].appendItem({
	    id: 'finish_debug',
	    text: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_FINISH_DEBUG_ACTION'),
	    onclick: function () {
	      bizproc_debugger.Manager.Instance.askFinishSession(babelHelpers.classPrivateFieldLooseBase(this, _debuggerInstance$2)[_debuggerInstance$2].session).then(() => {
	        this.stopActionPanel();
	      }, response => {
	        babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse)[_handleRejectResponse](response, 'finish_debug');
	      });
	    }.bind(this)
	  });
	}
	function _onHideActionPanel2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _guides)[_guides].forEach(guide => {
	    guide.finish();
	  });
	}
	function _getRemoveEntityActionText2() {
	  return `
			<span>${main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_ACTION_PANEL_CRM_REMOVE_DEAL_ACTION_1'))}</span>
		`;
	}
	function _getCheckedIdsInBpStyle2() {
	  const checkedIds = babelHelpers.classPrivateFieldLooseBase(this, _grid)[_grid].getCheckedId();

	  // todo: get EntityType from another place
	  return checkedIds.map(id => 'DEAL_' + id);
	}
	function _handleRejectResponse2(response, actionId) {
	  if (!response.errors) {
	    return;
	  }
	  let message = '';
	  response.errors.forEach(error => {
	    message = message + '\n' + error.message;
	  });
	  const guide = new ActionPanelGuide({
	    target: this.actionPanel.getItemById(actionId).layout.container,
	    title: message,
	    article: 'limit_office_bp_designer' // todo: replace,
	  });

	  babelHelpers.classPrivateFieldLooseBase(this, _guides)[_guides].push(guide);
	  guide.start();
	}

	var _pullHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullHandlers");
	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _mainView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mainView");
	var _triggers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("triggers");
	var _template$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("template");
	var _documentStatus$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentStatus");
	var _statusList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("statusList");
	var _documentCategoryId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentCategoryId");
	var _documentFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentFields");
	var _documentValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentValues");
	var _workflowId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _workflowStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowStatus");
	var _workflowEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowEvents");
	var _workflowTrack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowTrack");
	var _debuggerState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("debuggerState");
	var _customActionPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customActionPanel");
	var _resumeShowActionPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resumeShowActionPanel");
	var _shouldSetCustomActionPanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shouldSetCustomActionPanel");
	var _initAutomationContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initAutomationContext");
	var _subscribePull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribePull");
	var _unsubscribePull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribePull");
	var _handleRejectResponse$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleRejectResponse");
	var _onAfterDocumentFixed$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterDocumentFixed");
	class Automation extends main_core_events.EventEmitter {
	  constructor(parameters = {}) {
	    super();
	    Object.defineProperty(this, _onAfterDocumentFixed$1, {
	      value: _onAfterDocumentFixed2$1
	    });
	    Object.defineProperty(this, _handleRejectResponse$1, {
	      value: _handleRejectResponse2$1
	    });
	    Object.defineProperty(this, _unsubscribePull, {
	      value: _unsubscribePull2
	    });
	    Object.defineProperty(this, _subscribePull, {
	      value: _subscribePull2
	    });
	    Object.defineProperty(this, _initAutomationContext, {
	      value: _initAutomationContext2
	    });
	    Object.defineProperty(this, _shouldSetCustomActionPanel, {
	      value: _shouldSetCustomActionPanel2
	    });
	    Object.defineProperty(this, _resumeShowActionPanel, {
	      value: _resumeShowActionPanel2
	    });
	    this.session = null;
	    Object.defineProperty(this, _pullHandlers, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mainView, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _triggers, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _template$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentStatus$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _statusList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentCategoryId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _documentFields, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentValues, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _workflowId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _workflowStatus, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _workflowEvents, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _workflowTrack, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _debuggerState, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _customActionPanel, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('BX.Bizproc.Debugger.Automation');
	    this.session = parameters.session;
	    if (this.session.isActive()) {
	      this.session.subscribeOnce('onAfterDocumentFixed', babelHelpers.classPrivateFieldLooseBase(this, _onAfterDocumentFixed$1)[_onAfterDocumentFixed$1].bind(this));
	      this.session.subscribe('onFinished', this.destroy.bind(this));
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribePull)[_subscribePull]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = new bizproc_localSettings.Settings('atm-dbg');
	    babelHelpers.classPrivateFieldLooseBase(this, _initAutomationContext)[_initAutomationContext]();
	    babelHelpers.classPrivateFieldLooseBase(this, _resumeShowActionPanel)[_resumeShowActionPanel]();
	  }
	  destroy() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2;
	    this.unsubscribeAll();
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribePull)[_unsubscribePull]();
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _mainView)[_mainView]) == null ? void 0 : _babelHelpers$classPr.destroy();
	    this.session = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _mainView)[_mainView] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _documentStatus$1)[_documentStatus$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _statusList)[_statusList] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _documentFields)[_documentFields] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _documentValues)[_documentValues] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowStatus)[_workflowStatus] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowTrack)[_workflowTrack] = [];
	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _customActionPanel)[_customActionPanel]) == null ? void 0 : _babelHelpers$classPr2.stopActionPanel();
	  }
	  get track() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _workflowTrack)[_workflowTrack];
	  }
	  get settings() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings];
	  }
	  get documentSigned() {
	    return this.session.documentSigned;
	  }
	  get sessionId() {
	    return this.session.id;
	  }
	  get workflowId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1];
	  }
	  get pullHandlers() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _pullHandlers)[_pullHandlers] === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _pullHandlers)[_pullHandlers] = [{
	        name: 'documentStatus',
	        func: this.handleExternalDocumentStatus.bind(this)
	      }, {
	        name: 'documentValues',
	        func: this.handleExternalDocumentValues.bind(this)
	      }, {
	        name: 'documentDelete',
	        func: this.handleExternalDocumentDelete.bind(this)
	      }, {
	        name: 'workflowStatus',
	        func: this.handleExternalWorkflowStatus.bind(this)
	      }, {
	        name: 'workflowEventAdd',
	        func: this.handleExternalWorkflowEventAdd.bind(this)
	      }, {
	        name: 'workflowEventRemove',
	        func: this.handleExternalWorkflowEventRemove.bind(this)
	      }, {
	        name: 'trackRow',
	        func: this.handleExternalTrackRow.bind(this)
	      }];
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _pullHandlers)[_pullHandlers];
	  }
	  getMainView() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _mainView)[_mainView]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _mainView)[_mainView] = new AutomationMainView(this);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _mainView)[_mainView];
	  }
	  getLogView() {
	    return new AutomationLogView(this);
	  }
	  getStatusList() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _statusList)[_statusList];
	  }
	  getDocumentFields() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentFields)[_documentFields];
	  }
	  getDocumentField(fieldId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentFields)[_documentFields].find(field => field.Id === fieldId);
	  }
	  getDocumentValue(fieldId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentValues)[_documentValues][fieldId] || null;
	  }
	  getDocumentStatus() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentStatus$1)[_documentStatus$1]; //getActiveDocument().getStatus();
	  }

	  getWorkflowStatus() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _workflowStatus)[_workflowStatus];
	  }
	  getState() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _debuggerState)[_debuggerState];
	  }
	  hasWorkflowEvents() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].length > 0;
	  }
	  setDocumentStatus(statusId) {
	    return new Promise(resolve => {
	      main_core.ajax.runAction('bizproc.debugger.setDocumentStatus', {
	        data: {
	          statusId: statusId
	        }
	      }).then(response => {
	        if (response.data && response.data.newStatus) {
	          babelHelpers.classPrivateFieldLooseBase(this, _documentStatus$1)[_documentStatus$1] = response.data.newStatus;
	          babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1] = response.data.template;
	          babelHelpers.classPrivateFieldLooseBase(this, _workflowTrack)[_workflowTrack] = [];
	          this.emit('onDocumentStatusChanged');
	        }
	        resolve(response);
	      }, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	    });
	  }
	  get templateTriggers() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _triggers)[_triggers].filter(trigger => trigger['DOCUMENT_STATUS'] === babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1]['DOCUMENT_STATUS']);
	  }
	  getTemplate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1];
	  }
	  isTemplateEmpty() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1].IS_EXTERNAL_MODIFIED === false && !main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1].ROBOTS);
	  }
	  startDebugTemplate() {
	    return new Promise(resolve => {
	      main_core.ajax.runAction('bizproc.debugger.resumeAutomationTemplate', {
	        data: {
	          sessionId: this.sessionId
	        },
	        analyticsLabel: 'automation_start_debug'
	      }).then(response => {
	        babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1] = response.data.workflowId;
	        babelHelpers.classPrivateFieldLooseBase(this, _debuggerState)[_debuggerState] = response.data.debuggerState;
	        resolve(response.data);
	      }, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	    });
	  }
	  emulateExternalEvent(robotId) {
	    return new Promise(resolve => {
	      var _babelHelpers$classPr3;
	      let eventId = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents][0]) == null ? void 0 : _babelHelpers$classPr3.name;
	      if (main_core.Type.isStringFilled(robotId)) {
	        var _babelHelpers$classPr4;
	        eventId = (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].find(({
	          sourceId: eventRobotId
	        }) => eventRobotId === robotId)) == null ? void 0 : _babelHelpers$classPr4.name;
	      }
	      if (!eventId) {
	        return;
	      }
	      main_core.ajax.runAction('bizproc.debugger.emulateExternalEvent', {
	        data: {
	          workflowId: babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1],
	          eventId
	        }
	      }).then(response => {
	        resolve(response.data);
	      }, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	    });
	  }
	  loadMainViewInfo() {
	    return new Promise(resolve => {
	      main_core.ajax.runAction('bizproc.debugger.fillAutomationView', {
	        data: {
	          sessionId: this.sessionId
	        }
	      }).then(response => {
	        babelHelpers.classPrivateFieldLooseBase(this, _triggers)[_triggers] = response.data.triggers;
	        babelHelpers.classPrivateFieldLooseBase(this, _template$1)[_template$1] = response.data.template;
	        babelHelpers.classPrivateFieldLooseBase(this, _documentStatus$1)[_documentStatus$1] = response.data.documentStatus;
	        babelHelpers.classPrivateFieldLooseBase(this, _statusList)[_statusList] = response.data.statusList;
	        babelHelpers.classPrivateFieldLooseBase(this, _documentCategoryId)[_documentCategoryId] = response.data.documentCategoryId;
	        babelHelpers.classPrivateFieldLooseBase(this, _documentFields)[_documentFields] = response.data.documentFields;
	        babelHelpers.classPrivateFieldLooseBase(this, _documentValues)[_documentValues] = response.data.documentValues;
	        babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1] = response.data.workflowId;
	        babelHelpers.classPrivateFieldLooseBase(this, _workflowStatus)[_workflowStatus] = response.data.workflowStatus;
	        babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents] = response.data.workflowEvents;
	        babelHelpers.classPrivateFieldLooseBase(this, _workflowTrack)[_workflowTrack] = response.data.track;
	        babelHelpers.classPrivateFieldLooseBase(this, _debuggerState)[_debuggerState] = response.data.debuggerState;
	        bizproc_automation.getGlobalContext().document.setFields(this.getDocumentFields()).setStatusList(this.getStatusList()).setStatus(this.getDocumentStatus());
	        bizproc_automation.getGlobalContext().automationGlobals.globalConstants = main_core.Type.isArrayFilled(response.data.globalConstants) ? response.data.globalConstants : [];
	        bizproc_automation.getGlobalContext().automationGlobals.globalVariables = main_core.Type.isArrayFilled(response.data.globalVariables) ? response.data.globalVariables : [];
	        resolve();
	      }, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	    });
	  }
	  get globalConstants() {
	    const context = bizproc_automation.getGlobalContext();
	    return context && context.automationGlobals ? context.automationGlobals.globalConstants : [];
	  }
	  get globalVariables() {
	    const context = bizproc_automation.getGlobalContext();
	    return context && context.automationGlobals ? context.automationGlobals.globalVariables : [];
	  }
	  loadAllLog() {
	    return new Promise(resolve => {
	      main_core.ajax.runAction('bizproc.debugger.loadAllLog', {
	        data: {
	          sessionId: this.session.id
	        }
	      }).then(response => {
	        resolve(response.data);
	      }, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	    });
	  }
	  loadRobotsByWorkflowId(workflowId) {
	    return new Promise((resolve, reject) => {
	      main_core.ajax.runAction('bizproc.debugger.loadRobotsByWorkflowId', {
	        data: {
	          sessionId: this.sessionId,
	          workflowId
	        }
	      }).then(response => {
	        resolve(response.data);
	      }, response => {
	        reject(response.data);
	        //this.#handleRejectResponse.bind(this);
	      });
	    });
	  }

	  handleExternalDocumentStatus(event) {
	    const status = event.getData().status;
	    if (this.getDocumentStatus() === status) {
	      return;
	    }
	    console.info('document status: ' + status);
	    babelHelpers.classPrivateFieldLooseBase(this, _documentStatus$1)[_documentStatus$1] = status;
	    this.emit('onDocumentStatusChanged');
	  }
	  handleExternalDocumentValues(event) {
	    const values = event.getData().values;
	    Object.keys(values).forEach(key => babelHelpers.classPrivateFieldLooseBase(this, _documentValues)[_documentValues][key] = values[key]);
	    console.info('document values: ' + Object.keys(values));
	    this.emit('onDocumentValuesUpdated', {
	      values
	    });
	  }
	  handleExternalDocumentDelete() {
	    ui_dialogs_messagebox.MessageBox.show({
	      message: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_CONFIRM_ON_DOCUMENT_DELETE'),
	      okCaption: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_MENU_FINISH_SESSION'),
	      onOk: () => {
	        return Manager.Instance.finishSession(this.session).then(null, babelHelpers.classPrivateFieldLooseBase(this, _handleRejectResponse$1)[_handleRejectResponse$1].bind(this));
	      },
	      buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL
	    });
	  }
	  handleExternalTrackRow(event) {
	    const row = event.getData().row;
	    row['WORKFLOW_STATUS'] = babelHelpers.classPrivateFieldLooseBase(this, _workflowStatus)[_workflowStatus];
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowTrack)[_workflowTrack].push(row);
	    this.emit('onWorkflowTrackAdded', {
	      row
	    });
	  }
	  handleExternalWorkflowStatus(event) {
	    const status = event.getData().status;
	    const workflowId = event.getData().workflowId;
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowStatus)[_workflowStatus] = status;
	    if (status === bizproc_automation.WorkflowStatus.RUNNING) {
	      babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1] = workflowId;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _workflowId$1)[_workflowId$1] !== workflowId) {
	      return;
	    }
	    console.info('workflow status: ' + status);
	    this.emit('onWorkflowStatusChanged', {
	      status,
	      workflowId
	    });
	  }
	  handleExternalWorkflowEventAdd(event) {
	    const eventName = event.getData().eventName;
	    const robotId = event.getData().sourceId;
	    console.info('workflow event added: ' + eventName);
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].push({
	      name: eventName,
	      sourceId: robotId
	    });
	    console.info('workflow events: ' + babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].map(event => event.name).join(', '));
	    this.emit('onWorkflowEventsChanged', {
	      events: babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents]
	    });
	  }
	  handleExternalWorkflowEventRemove(event) {
	    const eventName = event.getData().eventName;
	    console.info('workflow event removed: ' + eventName);
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents] = babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].filter(({
	      name: value
	    }) => value !== eventName);
	    console.info('workflow events: ' + babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents].map(({
	      name
	    }) => name).join(', '));
	    this.emit('onWorkflowEventsChanged', {
	      events: babelHelpers.classPrivateFieldLooseBase(this, _workflowEvents)[_workflowEvents]
	    });
	  }
	  getField(object, id) {
	    let field;
	    switch (object) {
	      case 'Document':
	        field = babelHelpers.classPrivateFieldLooseBase(this, _documentFields)[_documentFields].find(field => field.Id === id);
	        break;
	      case 'Template':
	      case 'Parameter':
	      case 'Constant':
	      case 'GlobalConst':
	      case 'GlobalVar':
	        // todo: parameter, variable, constant, GlobalConst, GlobalVar, Activity
	        break;
	    }
	    return field || {
	      Id: id,
	      ObjectId: object,
	      Name: id,
	      Type: 'string',
	      Expression: id,
	      SystemExpression: '{=' + object + ':' + id + '}'
	    };
	  }
	  getSettingsUrl() {
	    //TODO: get actual url
	    return `/crm/deal/automation/${babelHelpers.classPrivateFieldLooseBase(this, _documentCategoryId)[_documentCategoryId]}/`;
	  }
	}
	function _resumeShowActionPanel2() {
	  if (this.session.isInterceptionMode() && !this.session.isFixed()) {
	    if (main_core.Reflection.getClass('BX.CRM.Kanban.Grid')) {
	      const gridInstance = BX.CRM.Kanban.Grid.getInstance();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _shouldSetCustomActionPanel)[_shouldSetCustomActionPanel](gridInstance)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _customActionPanel)[_customActionPanel] = new CustomCrmActionPanel(gridInstance, this);
	        gridInstance.stopActionPanel(true);
	        gridInstance.resetActionPanel();
	        gridInstance.setCustomActionPanel(babelHelpers.classPrivateFieldLooseBase(this, _customActionPanel)[_customActionPanel].actionPanel);
	      }
	    }
	  }
	}
	function _shouldSetCustomActionPanel2(gridInstance) {
	  const gridData = gridInstance.getData();
	  const entityType = gridData.entityType;

	  //todo: modify
	  if (entityType !== 'DEAL') {
	    return false;
	  }
	  const categoryId = gridData.params.hasOwnProperty('CATEGORY_ID') ? main_core.Text.toInteger(gridData.params.CATEGORY_ID) : 0;
	  return this.session.initialCategoryId === categoryId;
	}
	function _initAutomationContext2() {
	  const context = new bizproc_automation.Context({
	    document: new bizproc_automation.Document({
	      rawDocumentType: [],
	      documentId: null,
	      categoryId: 0,
	      statusList: this.getStatusList(),
	      statusId: this.getDocumentStatus(),
	      documentFields: this.getDocumentFields()
	    }),
	    documentSigned: this.documentSigned,
	    canEdit: false,
	    canManage: false,
	    automationGlobals: new bizproc_automation.AutomationGlobals({
	      variables: [],
	      constants: []
	    })
	  });
	  bizproc_automation.setGlobalContext(context);
	}
	function _subscribePull2() {
	  const pull = Manager.Instance.pullHandler;
	  this.pullHandlers.forEach(({
	    name,
	    func
	  }) => {
	    pull.subscribe(name, func);
	  });
	}
	function _unsubscribePull2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _pullHandlers)[_pullHandlers] === null) {
	    return;
	  }
	  const pull = Manager.Instance.pullHandler;
	  this.pullHandlers.forEach(({
	    name,
	    func
	  }) => {
	    pull.unsubscribe(name, func);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _pullHandlers)[_pullHandlers] = null;
	}
	function _handleRejectResponse2$1(response) {
	  if (main_core.Type.isArrayFilled(response.errors)) {
	    const noDocumentError = response.errors.find(error => error.code === 404);
	    if (noDocumentError) {
	      this.handleExternalDocumentDelete();
	    } else {
	      const message = response.errors.map(error => error.message).join('\n');
	      ui_dialogs_messagebox.MessageBox.alert(message);
	    }
	  }
	}
	function _onAfterDocumentFixed2$1() {
	  this.loadMainViewInfo().then(() => {
	    this.emit('onAfterDocumentFixed');
	  });
	}

	var _unsubscribe = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribe");
	var _commands = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("commands");
	var _handleCommand = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleCommand");
	class CommandHandler extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _handleCommand, {
	      value: _handleCommand2
	    });
	    Object.defineProperty(this, _unsubscribe, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _commands, {
	      writable: true,
	      value: ['documentStatus', 'documentValues', 'documentDelete',
	      //workflow
	      'workflowStatus', 'workflowEventAdd', 'workflowEventRemove',
	      //track
	      'trackRow',
	      //session
	      'sessionFinish']
	    });
	    this.setEventNamespace('BX.Bizproc.Debugger.Pull');
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribe)[_unsubscribe] = pull_client.PULL.subscribe(this);
	  }
	  destroy() {
	    if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _unsubscribe)[_unsubscribe])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _unsubscribe)[_unsubscribe]();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribe)[_unsubscribe] = null;
	  }
	  getModuleId() {
	    return 'bizproc';
	  }
	  getSubscriptionType() {
	    return BX.PullClient.SubscriptionType.Server;
	  }
	  getMap() {
	    const map = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _commands)[_commands].forEach(command => {
	      map[command] = babelHelpers.classPrivateFieldLooseBase(this, _handleCommand)[_handleCommand].bind(this);
	    });
	    return map;
	  }
	}
	function _handleCommand2(params, extra, command) {
	  this.emit(command, params);
	}

	var _getText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getText");
	class FilterGuide {
	  constructor(options) {
	    var _options$events;
	    this.guide = new ui_tour.Guide({
	      steps: [{
	        target: options.target,
	        title: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TITLE'),
	        text: babelHelpers.classPrivateFieldLooseBase(FilterGuide, _getText)[_getText](),
	        article: '16087180',
	        events: (_options$events = options.events) != null ? _options$events : {},
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'primary'
	        }
	      }],
	      onEvents: true
	    });
	    this.bindEvents();
	  }
	  bindEvents() {
	    // EventEmitter.subscribe('UI.Tour.Guide:onFinish'....
	  }
	  start() {
	    this.guide.getPopup().setWidth(365);
	    this.guide.showNextStep();
	  }
	}
	function _getText2() {
	  return `
			<ul class="bizproc-debugger-filter-guide-list">
				<li class="bizproc-debugger-filter-guide-list-item">
					${Helper.toHtml(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TEXT_LINE_1'))}
				</li>
				<li class="bizproc-debugger-filter-guide-list-item">
					${Helper.toHtml(main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_FILTER_TOUR_TEXT_LINE_2'))}
				</li>
			</ul>
		`;
	}
	Object.defineProperty(FilterGuide, _getText, {
	  value: _getText2
	});

	var _getText$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getText");
	class StageGuide {
	  constructor(options) {
	    var _options$events;
	    this.guide = new ui_tour.Guide({
	      steps: [{
	        target: options.target,
	        title: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TITLE'),
	        text: babelHelpers.classPrivateFieldLooseBase(StageGuide, _getText$1)[_getText$1](),
	        article: '16483018',
	        events: (_options$events = options.events) != null ? _options$events : {},
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'primary'
	        }
	      }],
	      onEvents: true
	    });
	  }
	  start() {
	    this.guide.getPopup().setWidth(330);
	    this.guide.showNextStep();
	  }
	}
	function _getText2$1() {
	  return `
			<ul class="bizproc-debugger-filter-guide-list">
				<li class="bizproc-debugger-filter-guide-list-item">
					${main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TEXT_LINE_1')}
				</li>
				<li class="bizproc-debugger-filter-guide-list-item">
					${main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_STAGE_TOUR_TEXT_LINE_2')}
				</li>
			</ul>
		`;
	}
	Object.defineProperty(StageGuide, _getText$1, {
	  value: _getText2$1
	});

	var _grid$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("grid");
	var _guides$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("guides");
	var _reserveFilterIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reserveFilterIds");
	var _getStageGuide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStageGuide");
	var _getFilterGuide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilterGuide");
	class CrmDebuggerGuide extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _getFilterGuide, {
	      value: _getFilterGuide2
	    });
	    Object.defineProperty(this, _getStageGuide, {
	      value: _getStageGuide2
	    });
	    Object.defineProperty(this, _grid$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _guides$1, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _reserveFilterIds, {
	      writable: true,
	      value: []
	    });
	    this.setEventNamespace('BX.Bizproc.Debugger.CrmDebuggerGuide');
	    babelHelpers.classPrivateFieldLooseBase(this, _grid$1)[_grid$1] = options.grid;
	    babelHelpers.classPrivateFieldLooseBase(this, _reserveFilterIds)[_reserveFilterIds] = options.reserveFilterIds;

	    // reverse order
	    if (options.showStageStep) {
	      const stageStep = babelHelpers.classPrivateFieldLooseBase(this, _getStageGuide)[_getStageGuide]();
	      if (stageStep) {
	        babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].push(stageStep);
	      }
	    }
	    if (options.showFilterStep) {
	      const filterGuide = babelHelpers.classPrivateFieldLooseBase(this, _getFilterGuide)[_getFilterGuide]();
	      if (filterGuide) {
	        babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].push(filterGuide);
	      }
	    }
	  }
	  start() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].length <= 0) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1][babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].length - 1].start();
	  }
	}
	function _getStageGuide2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _grid$1)[_grid$1]) {
	    return;
	  }
	  const firstColumn = babelHelpers.classPrivateFieldLooseBase(this, _grid$1)[_grid$1].getColumns()[0];
	  if (!firstColumn) {
	    return null;
	  }
	  const guideTarget = firstColumn.getTitleContainer();
	  if (!guideTarget) {
	    return null;
	  }
	  const nextGuide = babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1][babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].length - 1];
	  return new StageGuide({
	    target: '.' + guideTarget.classList[0],
	    events: {
	      'onShow': function () {
	        this.emit('onStageGuideStepShow');
	      }.bind(this),
	      'onClose': function () {
	        if (nextGuide) {
	          nextGuide.start();
	        }
	        this.emit('onStageGuideStepClose');
	      }.bind(this)
	    }
	  });
	}
	function _getFilterGuide2() {
	  var _filterApi$parent, _filterApi$parent$get;
	  const filterId = babelHelpers.classPrivateFieldLooseBase(this, _grid$1)[_grid$1] ? [babelHelpers.classPrivateFieldLooseBase(this, _grid$1)[_grid$1].getData().gridId] : babelHelpers.classPrivateFieldLooseBase(this, _reserveFilterIds)[_reserveFilterIds];
	  if (filterId.length <= 0) {
	    return null;
	  }
	  let filter;
	  for (const key in filterId) {
	    filter = BX.Main.filterManager.getById(filterId[key]);
	    if (filter) {
	      break;
	    }
	  }
	  if (!filter) {
	    return null;
	  }
	  const filterApi = filter.getApi();
	  const guideTarget = filterApi == null ? void 0 : (_filterApi$parent = filterApi.parent) == null ? void 0 : (_filterApi$parent$get = _filterApi$parent.getPopupBindElement()) == null ? void 0 : _filterApi$parent$get.firstElementChild;
	  if (!guideTarget) {
	    return null;
	  }
	  const nextGuide = babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1][babelHelpers.classPrivateFieldLooseBase(this, _guides$1)[_guides$1].length - 1];
	  return new FilterGuide({
	    target: guideTarget,
	    events: {
	      'onShow': function () {
	        this.emit('onFilterGuideStepShow');
	      }.bind(this),
	      'onClose': function () {
	        if (nextGuide) {
	          nextGuide.start();
	        }
	        this.emit('onFilterGuideStepClose');
	      }.bind(this)
	    }
	  });
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$4;
	let instance = null;
	var _settings$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _showDebugger = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showDebugger");
	var _showGuide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showGuide");
	var _setDebugFilter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDebugFilter");
	var _removeDebugFilter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeDebugFilter");
	var _lastFilterId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastFilterId");
	var _getFilterApis = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilterApis");
	var _getFilterIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilterIds");
	var _isFilterGuideShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFilterGuideShown");
	var _isStageGuideShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isStageGuideShown");
	var _setFilterGuideShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFilterGuideShown");
	var _setStageGuideShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setStageGuideShown");
	class Manager {
	  static get Instance() {
	    if (instance === null) {
	      instance = new Manager();
	    }
	    return instance;
	  }
	  constructor() {
	    Object.defineProperty(this, _setStageGuideShown, {
	      value: _setStageGuideShown2
	    });
	    Object.defineProperty(this, _setFilterGuideShown, {
	      value: _setFilterGuideShown2
	    });
	    Object.defineProperty(this, _isStageGuideShown, {
	      value: _isStageGuideShown2
	    });
	    Object.defineProperty(this, _isFilterGuideShown, {
	      value: _isFilterGuideShown2
	    });
	    Object.defineProperty(this, _getFilterIds, {
	      value: _getFilterIds2
	    });
	    Object.defineProperty(this, _getFilterApis, {
	      value: _getFilterApis2
	    });
	    Object.defineProperty(this, _lastFilterId, {
	      get: _get_lastFilterId,
	      set: _set_lastFilterId
	    });
	    Object.defineProperty(this, _removeDebugFilter, {
	      value: _removeDebugFilter2
	    });
	    Object.defineProperty(this, _setDebugFilter, {
	      value: _setDebugFilter2
	    });
	    Object.defineProperty(this, _showGuide, {
	      value: _showGuide2
	    });
	    Object.defineProperty(this, _showDebugger, {
	      value: _showDebugger2
	    });
	    Object.defineProperty(this, _settings$1, {
	      writable: true,
	      value: void 0
	    });
	    this.pullHandler = new CommandHandler();
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1] = new bizproc_localSettings.Settings('manager');
	  }
	  initializeDebugger(parameters = {
	    session: {},
	    documentSigned: ''
	  }) {
	    const session = main_core.Type.isPlainObject(parameters.session) ? new Session(parameters.session) : null;
	    if (!session) {
	      return;
	    }
	    session.documentSigned = parameters.documentSigned;
	    this.requireSetFilter(session);
	    babelHelpers.classPrivateFieldLooseBase(this, _showDebugger)[_showDebugger](session);
	  }
	  startSession(documentSigned, modeId) {
	    return new Promise((resolve, reject) => {
	      Session.start(documentSigned, modeId).then(session => {
	        babelHelpers.classPrivateFieldLooseBase(this, _lastFilterId)[_lastFilterId] = null;
	        babelHelpers.classPrivateFieldLooseBase(this, _setDebugFilter)[_setDebugFilter](session);
	        const debuggerInstance = babelHelpers.classPrivateFieldLooseBase(this, _showDebugger)[_showDebugger](session, true);
	        babelHelpers.classPrivateFieldLooseBase(this, _showGuide)[_showGuide](debuggerInstance);
	        resolve();
	      }, reject);
	    });
	  }
	  finishSession(session, deleteDocument = false) {
	    return new Promise((resolve, reject) => {
	      session.finish({
	        deleteDocument
	      }).then(response => {
	        babelHelpers.classPrivateFieldLooseBase(this, _removeDebugFilter)[_removeDebugFilter](session);
	        resolve(response);
	      }, reject);
	    });
	  }
	  askFinishSession(session) {
	    const checkboxElement = main_core.Tag.render(_t$4 || (_t$4 = _$4`<input type="checkbox" class="ui-ctl-element">`));
	    const boxOptions = {
	      message: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_CONFIRM_FINISH_SESSION'),
	      okCaption: main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_MENU_FINISH_SESSION'),
	      buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	      popupOptions: {
	        zIndexOptions: {
	          alwaysOnTop: true
	        }
	      }
	    };
	    if (session.isExperimentalMode()) {
	      boxOptions.title = main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_CONFIRM_FINISH_SESSION');
	      boxOptions.message = main_core.Tag.render(_t2$4 || (_t2$4 = _$4`
				<label class="ui-ctl ui-ctl-checkbox">
					${0}
					<div class="ui-ctl-label-text">${0}</div>
				</label>
			`), checkboxElement, main_core.Loc.getMessage('BIZPROC_JS_DEBUGGER_DELETE_SESSION_DOCUMENT'));
	    }
	    return new Promise((resolve, reject) => {
	      boxOptions.onOk = () => Manager.Instance.finishSession(session, checkboxElement == null ? void 0 : checkboxElement.checked).then(resolve, reject);
	      boxOptions.onCancel = () => {
	        reject({
	          cancel: true
	        });
	        return true;
	      };
	      ui_dialogs_messagebox.MessageBox.show(boxOptions);
	    });
	  }
	  requireSetFilter(session, force = false) {
	    const lastId = babelHelpers.classPrivateFieldLooseBase(this, _getFilterIds)[_getFilterIds](session).pop();
	    if (lastId !== babelHelpers.classPrivateFieldLooseBase(this, _lastFilterId)[_lastFilterId] || force) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setDebugFilter)[_setDebugFilter](session);
	    }
	  }
	  createAutomationDebugger(parameters = {}) {
	    return new Automation(parameters);
	  }
	  openDebuggerStartPage(documentSigned, parameters = {}) {
	    const url = BX.Uri.addParam('/bitrix/components/bitrix/bizproc.debugger.start/', {
	      documentSigned: documentSigned,
	      analyticsLabel: {
	        automation_enter_debug: 'Y',
	        start_type: parameters.analyticsStartType || 'default'
	      }
	    });
	    const options = {
	      width: 745,
	      cacheable: false,
	      allowChangeHistory: true,
	      events: {}
	    };
	    return Manager.openSlider(url, options);
	  }
	  openSessionLog(sessionId) {
	    const url = BX.Uri.addParam('/bitrix/components/bitrix/bizproc.debugger.log/', {
	      'setTitle': 'Y',
	      'sessionId': sessionId
	    });
	    const options = {
	      width: 720,
	      cacheable: false,
	      allowChangeHistory: true,
	      events: {},
	      newWindowLabel: true
	    };
	    return Manager.openSlider(url, options);
	  }
	  static openSlider(url, options) {
	    if (!main_core.Type.isPlainObject(options)) {
	      options = {};
	    }
	    options = {
	      ...{
	        cacheable: false,
	        allowChangeHistory: true,
	        events: {}
	      },
	      ...options
	    };
	    return new Promise((resolve, reject) => {
	      if (main_core.Type.isStringFilled(url)) {
	        if (BX.SidePanel.Instance.open(url, options)) {
	          return resolve();
	        }
	        return reject();
	      }
	      return reject();
	    });
	  }
	}
	function _showDebugger2(session, isFirstShow = false) {
	  let debuggerInstance = null;
	  if (session.isAutomation()) {
	    debuggerInstance = this.createAutomationDebugger({
	      session: session
	    });
	  }
	  if (debuggerInstance) {
	    let initialShowState = debuggerInstance.session.isExperimentalMode() ? 'showExpanded' : 'showCollapsed';
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isFilterGuideShown)[_isFilterGuideShown]()) {
	      initialShowState = 'showCollapsed';
	    }
	    debuggerInstance.getMainView()[isFirstShow ? initialShowState : 'show']();
	    return debuggerInstance;
	  }
	  return debuggerInstance;
	}
	function _showGuide2(debuggerInstance) {
	  const guide = new CrmDebuggerGuide({
	    grid: main_core.Reflection.getClass('BX.CRM.Kanban.Grid') ? BX.CRM.Kanban.Grid.getInstance() : null,
	    showFilterStep: !babelHelpers.classPrivateFieldLooseBase(this, _isFilterGuideShown)[_isFilterGuideShown](),
	    showStageStep: !babelHelpers.classPrivateFieldLooseBase(this, _isStageGuideShown)[_isStageGuideShown]() && debuggerInstance.session.isInterceptionMode(),
	    reserveFilterIds: babelHelpers.classPrivateFieldLooseBase(this, _getFilterIds)[_getFilterIds](debuggerInstance.session)
	  });
	  guide.subscribe('onFilterGuideStepShow', babelHelpers.classPrivateFieldLooseBase(this, _setFilterGuideShown)[_setFilterGuideShown].bind(this, true));
	  guide.subscribe('onStageGuideStepShow', babelHelpers.classPrivateFieldLooseBase(this, _setStageGuideShown)[_setStageGuideShown].bind(this, true));
	  guide.subscribe('onFilterGuideStepClose', () => {
	    if (debuggerInstance.session && debuggerInstance.session.isExperimentalMode() && debuggerInstance.settings.get('popup-collapsed') === true) {
	      debuggerInstance.getMainView().showExpanded();
	    }
	  });
	  guide.start();
	}
	function _setDebugFilter2(session) {
	  const ids = babelHelpers.classPrivateFieldLooseBase(this, _getFilterIds)[_getFilterIds](session);
	  babelHelpers.classPrivateFieldLooseBase(this, _getFilterApis)[_getFilterApis](ids).forEach(({
	    id,
	    api
	  }) => {
	    api.setFilter({
	      preset_id: 'filter_robot_debugger'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFilterId)[_lastFilterId] = id;
	  });
	}
	function _removeDebugFilter2(session) {
	  const ids = babelHelpers.classPrivateFieldLooseBase(this, _getFilterIds)[_getFilterIds](session);
	  babelHelpers.classPrivateFieldLooseBase(this, _getFilterApis)[_getFilterApis](ids).forEach(({
	    api
	  }) => {
	    api.setFilter({
	      preset_id: 'default_filter'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFilterId)[_lastFilterId] = null;
	  });
	}
	function _get_lastFilterId() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].get('last-filter-id');
	}
	function _set_lastFilterId(value) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].set('last-filter-id', value);
	}
	function _getFilterApis2(ids) {
	  const apis = [];
	  ids.forEach(id => {
	    var _BX$Main$filterManage;
	    const filter = (_BX$Main$filterManage = BX.Main.filterManager) == null ? void 0 : _BX$Main$filterManage.getById(id);
	    if (filter) {
	      apis.push({
	        id,
	        api: filter.getApi()
	      });
	    }
	  });
	  return apis;
	}
	function _getFilterIds2(session) {
	  let categoryId;
	  if (session && session.modeId === Mode.interception.id && !session.isFixed()) {
	    categoryId = session.initialCategoryId;
	  } else {
	    var _session$activeDocume;
	    categoryId = session == null ? void 0 : (_session$activeDocume = session.activeDocument) == null ? void 0 : _session$activeDocume.categoryId;
	  }
	  const filterId = 'CRM_DEAL_LIST_V12';
	  if (!categoryId) {
	    return [filterId, `${filterId}_C_0`];
	  }
	  return [`${filterId}_C_${categoryId}`];
	}
	function _isFilterGuideShown2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].get('filter-guide-shown') === true;
	}
	function _isStageGuideShown2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].get('stage-guide-shown') === true;
	}
	function _setFilterGuideShown2(shown = true) {
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].set('filter-guide-shown', shown);
	}
	function _setStageGuideShown2(shown = true) {
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].set('stage-guide-shown', shown);
	}

	const Debugger = {
	  Manager,
	  Session,
	  Mode
	};

	exports.Debugger = Debugger;
	exports.Manager = Manager;
	exports.Session = Session;
	exports.Mode = Mode;

}((this.BX.Bizproc.Debugger = this.BX.Bizproc.Debugger || {}),BX.Main,BX.UI,BX,BX.UI.EntitySelector,BX,BX.UI,BX,BX.Main,BX,BX.Bizproc,BX,BX.Bizproc.Automation,BX.Bizproc.Debugger,BX,BX.Bizproc.LocalSettings,BX.Event,BX,BX.UI.Tour,BX.UI.Dialogs));
//# sourceMappingURL=debugger.bundle.js.map
