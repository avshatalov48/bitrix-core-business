this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,ui_shortView,pull_client,tasks_kanbanSort,ui_label,ui_entitySelector,tasks_creationMenu,calendar_entry,socialnetwork_postForm,main_popup,ui_popupcomponentsmaker,ui_switcher,main_core_events,main_core) {
	'use strict';

	var _filter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _filterContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterContainer");
	var _fields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fields");
	var _filterApply = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterApply");
	var _filterShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterShow");
	var _filterHide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterHide");
	var _isFilteredByField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFilteredByField");
	var _isFilteredByFieldValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFilteredByFieldValue");
	class Filter extends main_core_events.EventEmitter {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _isFilteredByFieldValue, {
	      value: _isFilteredByFieldValue2
	    });
	    Object.defineProperty(this, _isFilteredByField, {
	      value: _isFilteredByField2
	    });
	    Object.defineProperty(this, _filterHide, {
	      value: _filterHide2
	    });
	    Object.defineProperty(this, _filterShow, {
	      value: _filterShow2
	    });
	    Object.defineProperty(this, _filterApply, {
	      value: _filterApply2
	    });
	    Object.defineProperty(this, _filter, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filterContainer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fields, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.Filter');
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter] = BX.Main.filterManager.getById(params.filterId);
	    babelHelpers.classPrivateFieldLooseBase(this, _filterContainer)[_filterContainer] = params.filterContainer;
	    babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields] = babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFilterFieldsValues();
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', babelHelpers.classPrivateFieldLooseBase(this, _filterApply)[_filterApply].bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:show', babelHelpers.classPrivateFieldLooseBase(this, _filterShow)[_filterShow].bind(this));
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:blur', babelHelpers.classPrivateFieldLooseBase(this, _filterHide)[_filterHide].bind(this));
	  }
	  applyFilter() {
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].applyFilter(false, true);
	  }
	  toggleField(fieldName, value) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isFilteredByFieldValue)[_isFilteredByFieldValue](fieldName, value)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getApi().extendFilter({
	        [fieldName]: value
	      });
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFilterFields().forEach(field => {
	      if (field.getAttribute('data-name') === fieldName) {
	        babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFields().deleteField(field);
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getSearch().apply();
	  }
	}
	function _filterApply2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields] = babelHelpers.classPrivateFieldLooseBase(this, _filter)[_filter].getFilterFieldsValues();
	}
	function _filterShow2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _filterContainer)[_filterContainer], '--active');
	}
	function _filterHide2() {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _filterContainer)[_filterContainer], '--active');
	}
	function _isFilteredByField2(field) {
	  if (!Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields]).includes(field)) {
	    return false;
	  }
	  if (main_core.Type.isArray(babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields][field])) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields][field].length > 0;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields][field] !== '';
	}
	function _isFilteredByFieldValue2(field, value) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isFilteredByField)[_isFilteredByField](field) && babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields][field].toString() === value.toString();
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _filter$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _filterRole = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterRole");
	var _userId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userId");
	var _groupId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _counters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("counters");
	var _isUserSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserSpace");
	var _isScrumSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isScrumSpace");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _initPull = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPull");
	var _processPullEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processPullEvent");
	var _processUserCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processUserCounter");
	var _processProjectCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("processProjectCounter");
	var _renderExpired = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpired");
	var _renderNewComments = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNewComments");
	var _click = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("click");
	var _update = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("update");
	var _updateCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCounter");
	var _updateCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCounters");
	var _readAllGroup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readAllGroup");
	var _readAllUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("readAllUser");
	var _hasCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasCounters");
	var _getActiveClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getActiveClass");
	var _consoleError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("consoleError");
	class TasksCounters {
	  constructor(_params) {
	    Object.defineProperty(this, _consoleError, {
	      value: _consoleError2
	    });
	    Object.defineProperty(this, _getActiveClass, {
	      value: _getActiveClass2
	    });
	    Object.defineProperty(this, _hasCounters, {
	      value: _hasCounters2
	    });
	    Object.defineProperty(this, _readAllUser, {
	      value: _readAllUser2
	    });
	    Object.defineProperty(this, _readAllGroup, {
	      value: _readAllGroup2
	    });
	    Object.defineProperty(this, _updateCounters, {
	      value: _updateCounters2
	    });
	    Object.defineProperty(this, _updateCounter, {
	      value: _updateCounter2
	    });
	    Object.defineProperty(this, _update, {
	      value: _update2
	    });
	    Object.defineProperty(this, _click, {
	      value: _click2
	    });
	    Object.defineProperty(this, _renderNewComments, {
	      value: _renderNewComments2
	    });
	    Object.defineProperty(this, _renderExpired, {
	      value: _renderExpired2
	    });
	    Object.defineProperty(this, _processProjectCounter, {
	      value: _processProjectCounter2
	    });
	    Object.defineProperty(this, _processUserCounter, {
	      value: _processUserCounter2
	    });
	    Object.defineProperty(this, _processPullEvent, {
	      value: _processPullEvent2
	    });
	    Object.defineProperty(this, _initPull, {
	      value: _initPull2
	    });
	    Object.defineProperty(this, _filter$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filterRole, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _groupId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _counters, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isUserSpace, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isScrumSpace, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId] = main_core.Type.isUndefined(_params.userId) ? 0 : parseInt(_params.userId, 10);
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId] = main_core.Type.isUndefined(_params.groupId) ? 0 : parseInt(_params.groupId, 10);
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$1)[_filter$1] = _params.filter;
	    babelHelpers.classPrivateFieldLooseBase(this, _filterRole)[_filterRole] = _params.filterRole;
	    babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters] = _params.counters;
	    babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace)[_isUserSpace] = _params.isUserSpace;
	    babelHelpers.classPrivateFieldLooseBase(this, _isScrumSpace)[_isScrumSpace] = _params.isScrumSpace;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {
	      node: null,
	      listContainerNode: null,
	      listNode: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _initPull)[_initPull]();
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].node = main_core.Tag.render(_t || (_t = _`
			<div class="sn-spaces__toolbar-space_counters">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderExpired)[_renderExpired](), babelHelpers.classPrivateFieldLooseBase(this, _renderNewComments)[_renderNewComments]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].node;
	  }
	  readAll() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace)[_isUserSpace] || babelHelpers.classPrivateFieldLooseBase(this, _filterRole)[_filterRole] !== 'view_all') {
	      babelHelpers.classPrivateFieldLooseBase(this, _readAllUser)[_readAllUser]();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _readAllGroup)[_readAllGroup]();
	    }
	  }
	}
	function _initPull2() {
	  pull_client.PULL.subscribe({
	    moduleId: 'tasks',
	    callback: babelHelpers.classPrivateFieldLooseBase(this, _processPullEvent)[_processPullEvent].bind(this)
	  });
	}
	function _processPullEvent2(data) {
	  const {
	    command,
	    params
	  } = data;
	  const eventHandlers = {
	    user_counter: babelHelpers.classPrivateFieldLooseBase(this, _processUserCounter)[_processUserCounter].bind(this),
	    project_counter: babelHelpers.classPrivateFieldLooseBase(this, _processProjectCounter)[_processProjectCounter].bind(this)
	  };
	  const has = Object.prototype.hasOwnProperty;
	  if (has.call(eventHandlers, command)) {
	    const method = eventHandlers[command];
	    if (method) {
	      method.apply(this, [params]);
	    }
	  }
	}
	function _processUserCounter2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace)[_isUserSpace]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters]();
	}
	function _processProjectCounter2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace)[_isUserSpace]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters]();
	}
	function _renderExpired2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isScrumSpace)[_isScrumSpace]) {
	    return '';
	  }
	  const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';
	  const activeClass = babelHelpers.classPrivateFieldLooseBase(this, _getActiveClass)[_getActiveClass](babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].expired.counter);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].expired = main_core.Tag.render(_t2 || (_t2 = _`
			<button
				data-id="sn-spaces-toolbar-counters-expired"
				class="sn-spaces__toolbar-space_btn-with-counter ${0} ${0}"
			>
				<div class="ui-icon-set --stopwatch"></div>
				<div class="sn-spaces__toolbar-space_btn-counter">
					${0}
				</div>
			</button>
		`), uiClasses, activeClass, parseInt(babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].expired.counter, 10));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].expired, 'click', babelHelpers.classPrivateFieldLooseBase(this, _click)[_click].bind(this, babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].expired));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].expired;
	}
	function _renderNewComments2() {
	  const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';
	  const activeClass = babelHelpers.classPrivateFieldLooseBase(this, _getActiveClass)[_getActiveClass](babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].new_comments.counter);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].newComments = main_core.Tag.render(_t3 || (_t3 = _`
			<button
				data-id="sn-spaces-toolbar-counters-new-comments"
				class="sn-spaces__toolbar-space_btn-with-counter --green ${0} ${0}"
			>
				<div class="ui-icon-set --chats-1"></div>
				<div class="sn-spaces__toolbar-space_btn-counter">
					${0}
				</div>
			</button>
		`), uiClasses, activeClass, parseInt(babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].new_comments.counter, 10));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].newComments, 'click', babelHelpers.classPrivateFieldLooseBase(this, _click)[_click].bind(this, babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].new_comments));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].newComments;
	}
	function _click2(counter) {
	  babelHelpers.classPrivateFieldLooseBase(this, _filter$1)[_filter$1].toggleField(counter.filterField, counter.code);
	}
	function _update2(counters) {
	  babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters] = counters;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isScrumSpace)[_isScrumSpace]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCounter)[_updateCounter](babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].expired, babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].expired.counter);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCounter)[_updateCounter](babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].newComments, babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].new_comments.counter);
	}
	function _updateCounter2(node, value) {
	  const newCommentsActiveClass = babelHelpers.classPrivateFieldLooseBase(this, _getActiveClass)[_getActiveClass](value);
	  if (newCommentsActiveClass) {
	    main_core.Dom.addClass(node, newCommentsActiveClass);
	  } else {
	    main_core.Dom.removeClass(node, '--active');
	  }
	  const counterNode = node.querySelector('.sn-spaces__toolbar-space_btn-counter');
	  counterNode.textContent = parseInt(value, 10);
	}
	function _updateCounters2() {
	  main_core.ajax.runComponentAction('bitrix:socialnetwork.spaces.toolbar', 'getTasksCounters', {
	    mode: 'class',
	    data: {
	      groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId]
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _update)[_update](response.data);
	  }).catch(error => {
	    babelHelpers.classPrivateFieldLooseBase(this, _consoleError)[_consoleError]('changePrivacy', error);
	  });
	}
	function _readAllGroup2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasCounters)[_hasCounters]()) {
	    return;
	  }
	  main_core.ajax.runAction('tasks.viewedGroup.project.markAsRead', {
	    data: {
	      fields: {
	        groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId]
	      }
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters]();
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$1)[_filter$1].applyFilter();
	  }).catch(error => {
	    babelHelpers.classPrivateFieldLooseBase(this, _consoleError)[_consoleError]('readAllScrum', error);
	  });
	}
	function _readAllUser2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasCounters)[_hasCounters]()) {
	    return;
	  }
	  main_core.ajax.runAction('tasks.viewedGroup.user.markAsRead', {
	    data: {
	      fields: {
	        groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId],
	        userId: babelHelpers.classPrivateFieldLooseBase(this, _userId)[_userId],
	        role: babelHelpers.classPrivateFieldLooseBase(this, _filterRole)[_filterRole]
	      }
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters]();
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$1)[_filter$1].applyFilter();
	  }).catch(error => {
	    babelHelpers.classPrivateFieldLooseBase(this, _consoleError)[_consoleError]('readAllScrum', error);
	  });
	}
	function _hasCounters2() {
	  const expiredCounter = parseInt(babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].expired.counter, 10);
	  const newCommentsCounter = parseInt(babelHelpers.classPrivateFieldLooseBase(this, _counters)[_counters].new_comments.counter, 10);
	  return expiredCounter > 0 || newCommentsCounter > 0;
	}
	function _getActiveClass2(value) {
	  return value > 0 ? '--active' : '';
	}
	function _consoleError2(action, error) {
	  // eslint-disable-next-line no-console
	  console.error(`TasksCounters: ${action} error`, error);
	}

	let _$1 = t => t,
	  _t$1;
	var _groupId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _isTaskLimitsExceeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTaskLimitsExceeded");
	var _canUseAutomation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canUseAutomation");
	var _sourceAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sourceAnalytics");
	var _sidePanelManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sidePanelManager");
	var _onClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onClick");
	var _isShowLimitSidePanel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isShowLimitSidePanel");
	class TasksRobots extends main_core_events.EventEmitter {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _isShowLimitSidePanel, {
	      value: _isShowLimitSidePanel2
	    });
	    Object.defineProperty(this, _onClick, {
	      value: _onClick2
	    });
	    Object.defineProperty(this, _groupId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isTaskLimitsExceeded, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canUseAutomation, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sourceAnalytics, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sidePanelManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId$1)[_groupId$1] = params.groupId;
	    babelHelpers.classPrivateFieldLooseBase(this, _isTaskLimitsExceeded)[_isTaskLimitsExceeded] = params.isTaskLimitsExceeded;
	    babelHelpers.classPrivateFieldLooseBase(this, _canUseAutomation)[_canUseAutomation] = params.canUseAutomation;
	    babelHelpers.classPrivateFieldLooseBase(this, _sourceAnalytics)[_sourceAnalytics] = params.sourceAnalytics;
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager)[_sidePanelManager] = BX.SidePanel.Instance;
	    this.setEventNamespace('BX.Socialnetwork.Spaces.TasksRobots');
	  }
	  renderBtn() {
	    let className = 'tasks-scrum-robot-btn ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes ui-btn-no-caps ui-btn-themes ';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isShowLimitSidePanel)[_isShowLimitSidePanel]()) {
	      className += ' ui-btn-icon-lock';
	    }
	    const node = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<button class="${0}">
				${0}
			</button>
		`), className, main_core.Loc.getMessage('SN_SPACES_TASKS_ROBOTS_BUTTON'));
	    main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onClick)[_onClick].bind(this));
	    return node;
	  }
	}
	function _onClick2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isShowLimitSidePanel)[_isShowLimitSidePanel]()) {
	    BX.UI.InfoHelper.show('limit_tasks_robots', {
	      isLimit: true,
	      limitAnalyticsLabels: {
	        module: 'tasks',
	        source: babelHelpers.classPrivateFieldLooseBase(this, _sourceAnalytics)[_sourceAnalytics]
	      }
	    });
	  } else {
	    const url = `/bitrix/components/bitrix/tasks.automation/slider.php?site_id=${main_core.Loc.getMessage('SITE_ID')}&project_id=${babelHelpers.classPrivateFieldLooseBase(this, _groupId$1)[_groupId$1]}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager)[_sidePanelManager].open(url, {
	      customLeftBoundary: 0,
	      cacheable: false,
	      loader: 'bizproc:automation-loader'
	    });
	  }
	}
	function _isShowLimitSidePanel2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isTaskLimitsExceeded)[_isTaskLimitsExceeded] && !babelHelpers.classPrivateFieldLooseBase(this, _canUseAutomation)[_canUseAutomation];
	}

	var _sidePanelManager$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sidePanelManager");
	var _pathToTasks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToTasks");
	var _pathToTasksTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToTasksTask");
	class TasksRouter {
	  constructor(params) {
	    Object.defineProperty(this, _sidePanelManager$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToTasks, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToTasksTask, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks)[_pathToTasks] = params.pathToTasks;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToTasksTask)[_pathToTasksTask] = params.pathToTasksTask;
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager$1)[_sidePanelManager$1] = BX.SidePanel.Instance;
	  }
	  redirectTo(url) {
	    top.BX.Socialnetwork.Spaces.space.reloadPageContent(url);
	  }
	  redirectToTasks(urlParam, urlValue) {
	    const viewUri = new main_core.Uri(babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks)[_pathToTasks]);
	    viewUri.setQueryParam(urlParam, urlValue);
	    top.BX.Socialnetwork.Spaces.space.reloadPageContent(viewUri.toString());
	  }
	  redirectToScrumView(view) {
	    const viewUri = new main_core.Uri(babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks)[_pathToTasks]);
	    viewUri.setQueryParam('tab', view);
	    top.BX.Socialnetwork.Spaces.space.reloadPageContent(viewUri.toString());
	  }
	  showTask(taskId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager$1)[_sidePanelManager$1].open(babelHelpers.classPrivateFieldLooseBase(this, _pathToTasksTask)[_pathToTasksTask].replace('#action#', 'view').replace('#task_id#', taskId));
	  }
	  showSidePanel(url) {
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager$1)[_sidePanelManager$1].open(url);
	  }
	  showByExtension(fullName, shortName, params) {
	    return top.BX.Runtime.loadExtension(fullName).then(exports => {
	      const className = shortName.replaceAll('-', '');
	      if (exports && exports[className]) {
	        const extension = new exports[className](params);
	        extension.show();
	        return extension;
	      }
	      return null;
	    });
	  }
	}

	class KanbanOrder {}
	KanbanOrder.SORT_ASC = 'asc';
	KanbanOrder.SORT_DESC = 'desc';
	KanbanOrder.SORT_ACTUAL = 'actual';

	const ReadAllItem = (view, emptySpace) => new main_popup.MenuItem({
	  dataset: {
	    id: `spaces-tasks-${view.getViewId()}-settings-read-all`
	  },
	  text: main_core.Loc.getMessage('SN_SPACES_TASKS_SETTINGS_READ_ALL'),
	  className: emptySpace ? 'menu-popup-item-none' : '',
	  onclick: () => {
	    view.emit('realAll');
	  }
	});

	var _menu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _bindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");
	var _getMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenu");
	var _createMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	var _getEventNamespace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEventNamespace");
	class TasksSettingsMenu extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _getEventNamespace, {
	      value: _getEventNamespace2
	    });
	    Object.defineProperty(this, _createMenu, {
	      value: _createMenu2
	    });
	    Object.defineProperty(this, _getMenu, {
	      value: _getMenu2
	    });
	    Object.defineProperty(this, _menu, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace(babelHelpers.classPrivateFieldLooseBase(this, _getEventNamespace)[_getEventNamespace]());
	    babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement] = params.bindElement;
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getMenu)[_getMenu]().show();
	  }
	  close() {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) == null ? void 0 : _babelHelpers$classPr.close();
	  }
	  getMenuItems() {
	    return [ReadAllItem(this)];
	  }
	  getViewId() {
	    return 'base';
	  }
	}
	function _getMenu2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu)[_createMenu](babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu];
	}
	function _createMenu2(bindElement) {
	  const menu = new main_popup.Menu({
	    id: `spaces-tasks-${this.getViewId()}-settings`,
	    bindElement,
	    closeByEsc: true
	  });
	  for (const menuItem of this.getMenuItems()) {
	    menu.addMenuItem(menuItem);
	  }
	  return menu;
	}
	function _getEventNamespace2() {
	  const camelCase = this.getViewId().toLowerCase().replace(/(-[a-z])/g, group => group.toUpperCase().replace('-', ''));
	  const pascalCase = camelCase.charAt(0).toUpperCase() + camelCase.slice(1);
	  return `BX.Socialnetwork.Spaces.${pascalCase}Settings`;
	}

	var _isUserSpace$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUserSpace");
	var _isScrumSpace$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isScrumSpace");
	var _viewMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewMode");
	var _setViewMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setViewMode");
	var _getAvailableModes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAvailableModes");
	var _getDefaultMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultMode");
	var _getCurrentSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCurrentSpace");
	class TasksView {
	  constructor(params) {
	    Object.defineProperty(this, _getCurrentSpace, {
	      value: _getCurrentSpace2
	    });
	    Object.defineProperty(this, _getDefaultMode, {
	      value: _getDefaultMode2
	    });
	    Object.defineProperty(this, _getAvailableModes, {
	      value: _getAvailableModes2
	    });
	    Object.defineProperty(this, _setViewMode, {
	      value: _setViewMode2
	    });
	    Object.defineProperty(this, _isUserSpace$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isScrumSpace$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _viewMode, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace$1)[_isUserSpace$1] = params.isUserSpace;
	    babelHelpers.classPrivateFieldLooseBase(this, _isScrumSpace$1)[_isScrumSpace$1] = params.isScrumSpace;
	    babelHelpers.classPrivateFieldLooseBase(this, _setViewMode)[_setViewMode](params.viewMode);
	  }
	  getCurrentViewMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _viewMode)[_viewMode];
	  }
	}
	function _setViewMode2(viewMode) {
	  const availableModes = babelHelpers.classPrivateFieldLooseBase(this, _getAvailableModes)[_getAvailableModes]().get(babelHelpers.classPrivateFieldLooseBase(this, _getCurrentSpace)[_getCurrentSpace]());
	  babelHelpers.classPrivateFieldLooseBase(this, _viewMode)[_viewMode] = availableModes.has(viewMode) ? viewMode : babelHelpers.classPrivateFieldLooseBase(this, _getDefaultMode)[_getDefaultMode]();
	}
	function _getAvailableModes2() {
	  return new Map([['user', new Set(['list', 'plan', 'timeline', 'calendar', 'gantt'])], ['group', new Set(['list', 'kanban', 'plan', 'timeline', 'calendar', 'gantt'])], ['scrum', new Set(['plan', 'active', 'complete'])]]);
	}
	function _getDefaultMode2() {
	  const defaultModes = new Map([['user', 'list'], ['group', 'list'], ['scrum', 'plan']]);
	  return defaultModes.get(babelHelpers.classPrivateFieldLooseBase(this, _getCurrentSpace)[_getCurrentSpace]());
	}
	function _getCurrentSpace2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isUserSpace$1)[_isUserSpace$1]) {
	    return 'user';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isScrumSpace$1)[_isScrumSpace$1]) {
	    return 'scrum';
	  } else {
	    return 'group';
	  }
	}

	const CompleteSprintItem = view => new main_popup.MenuItem({
	  dataset: {
	    id: `spaces-tasks-${view.getViewId()}-settings-complete-sprint`
	  },
	  text: main_core.Loc.getMessage('SN_SPACES_TASKS_SCRUM_SETTINGS_COMPLETE_SPRINT'),
	  disabled: !view.canCompleteSprint(),
	  onclick: () => {
	    view.close();
	    view.emit('completeSprint');
	  }
	});

	var _view = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("view");
	var _order = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	var _getTitleItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitleItem");
	var _getActivitySortItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getActivitySortItem");
	var _getRecommendedLabel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecommendedLabel");
	var _getMySortItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMySortItem");
	var _getOrderTitleItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOrderTitleItem");
	var _getOrderDescItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOrderDescItem");
	var _getOrderAscItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOrderAscItem");
	var _getOrderItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOrderItem");
	var _getSelectedClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSelectedClass");
	class KanbanSortItems {
	  constructor(view, _order2) {
	    Object.defineProperty(this, _getSelectedClass, {
	      value: _getSelectedClass2
	    });
	    Object.defineProperty(this, _getOrderItem, {
	      value: _getOrderItem2
	    });
	    Object.defineProperty(this, _getOrderAscItem, {
	      value: _getOrderAscItem2
	    });
	    Object.defineProperty(this, _getOrderDescItem, {
	      value: _getOrderDescItem2
	    });
	    Object.defineProperty(this, _getOrderTitleItem, {
	      value: _getOrderTitleItem2
	    });
	    Object.defineProperty(this, _getMySortItem, {
	      value: _getMySortItem2
	    });
	    Object.defineProperty(this, _getRecommendedLabel, {
	      value: _getRecommendedLabel2
	    });
	    Object.defineProperty(this, _getActivitySortItem, {
	      value: _getActivitySortItem2
	    });
	    Object.defineProperty(this, _getTitleItem, {
	      value: _getTitleItem2
	    });
	    Object.defineProperty(this, _view, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _order, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _view)[_view] = view;
	    babelHelpers.classPrivateFieldLooseBase(this, _order)[_order] = _order2;
	  }
	  getItems() {
	    const hiddenItems = [babelHelpers.classPrivateFieldLooseBase(this, _getOrderTitleItem)[_getOrderTitleItem](), babelHelpers.classPrivateFieldLooseBase(this, _getOrderDescItem)[_getOrderDescItem](), babelHelpers.classPrivateFieldLooseBase(this, _getOrderAscItem)[_getOrderAscItem]()];
	    const items = [babelHelpers.classPrivateFieldLooseBase(this, _getTitleItem)[_getTitleItem](), babelHelpers.classPrivateFieldLooseBase(this, _getActivitySortItem)[_getActivitySortItem](), babelHelpers.classPrivateFieldLooseBase(this, _getMySortItem)[_getMySortItem](hiddenItems)];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _order)[_order] !== KanbanOrder.SORT_ACTUAL) {
	      items.push(...hiddenItems);
	    }
	    return items;
	  }
	}
	function _getTitleItem2() {
	  return new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].getViewId()}-settings-sort-title-item`
	    },
	    html: `<b>${main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_TITLE_ITEM')}</b>`,
	    className: 'menu-popup-item menu-popup-no-icon'
	  });
	}
	function _getActivitySortItem2() {
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].getViewId()}-settings-activity-sort-item`
	    },
	    html: `
				${main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_ACTIVITY_DATE_MSGVER_1')}
				<span style="margin-left: 5px">${babelHelpers.classPrivateFieldLooseBase(this, _getRecommendedLabel)[_getRecommendedLabel]()}</span>
			`,
	    className: `menu-popup-item-sort-field ${babelHelpers.classPrivateFieldLooseBase(this, _getSelectedClass)[_getSelectedClass]([KanbanOrder.SORT_ACTUAL])}`,
	    onclick: tasks_kanbanSort.KanbanSort.getInstance().disableCustomSort
	  });
	  menuItem.params = {
	    order: KanbanOrder.SORT_ACTUAL
	  };
	  return menuItem;
	}
	function _getRecommendedLabel2() {
	  return new ui_label.Label({
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_RECOMMENDED_LABEL').toUpperCase(),
	    color: ui_label.LabelColor.LIGHT_BLUE,
	    fill: true,
	    size: 'ui-label-xs'
	  }).render().outerHTML;
	}
	function _getMySortItem2(hiddenItems) {
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].getViewId()}-settings-my-sort-item`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_SORTING'),
	    className: `menu-popup-item-sort-field ${babelHelpers.classPrivateFieldLooseBase(this, _getSelectedClass)[_getSelectedClass]([KanbanOrder.SORT_ASC, KanbanOrder.SORT_DESC])}`,
	    onclick: tasks_kanbanSort.KanbanSort.getInstance().enableCustomSort
	  });
	  menuItem.params = hiddenItems;
	  return menuItem;
	}
	function _getOrderTitleItem2() {
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].getViewId()}-settings-sort-order-title-item`
	    },
	    html: `<b>${main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_ORDER_TITLE')}</b>`,
	    className: 'menu-popup-item menu-popup-no-icon'
	  });
	  menuItem.params = {
	    type: 'sub'
	  };
	  return menuItem;
	}
	function _getOrderDescItem2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getOrderItem)[_getOrderItem](KanbanOrder.SORT_DESC);
	}
	function _getOrderAscItem2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _getOrderItem)[_getOrderItem](KanbanOrder.SORT_ASC);
	}
	function _getOrderItem2(order) {
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view)[_view].getViewId()}-settings-order-${order}-item`
	    },
	    text: main_core.Loc.getMessage(`SN_SPACES_TASKS_SORT_${order.toUpperCase()}`),
	    className: `menu-popup-item-sort-field ${babelHelpers.classPrivateFieldLooseBase(this, _getSelectedClass)[_getSelectedClass]([order])}`,
	    onclick: tasks_kanbanSort.KanbanSort.getInstance().selectCustomOrder
	  });
	  menuItem.params = {
	    type: 'sub',
	    order
	  };
	  return menuItem;
	}
	function _getSelectedClass2(order) {
	  const classSelected = 'menu-popup-item-accept';
	  const classDeselected = 'menu-popup-item-none';
	  return order.includes(babelHelpers.classPrivateFieldLooseBase(this, _order)[_order]) ? classSelected : classDeselected;
	}

	var _order$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	var _canCompleteSprint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canCompleteSprint");
	var _activeSprintExists = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("activeSprintExists");
	class ScrumActiveSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _order$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canCompleteSprint, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _activeSprintExists, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _order$1)[_order$1] = params.order;
	    babelHelpers.classPrivateFieldLooseBase(this, _canCompleteSprint)[_canCompleteSprint] = params.canCompleteSprint;
	    babelHelpers.classPrivateFieldLooseBase(this, _activeSprintExists)[_activeSprintExists] = params.activeSprintExists;
	  }
	  getViewId() {
	    return 'scrum-active';
	  }
	  canCompleteSprint() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _canCompleteSprint)[_canCompleteSprint] && babelHelpers.classPrivateFieldLooseBase(this, _activeSprintExists)[_activeSprintExists];
	  }
	  getMenuItems() {
	    return [CompleteSprintItem(this), ...new KanbanSortItems(this, babelHelpers.classPrivateFieldLooseBase(this, _order$1)[_order$1]).getItems()];
	  }
	}

	const BurnDownItem = view => new main_popup.MenuItem({
	  dataset: {
	    id: `spaces-tasks-${view.getViewId()}-settings-burn-down`
	  },
	  text: main_core.Loc.getMessage('SN_SPACES_TASKS_SCRUM_SETTINGS_BURN_DOWN'),
	  onclick: () => {
	    view.close();
	    view.emit('showBurnDown');
	  }
	});

	class ScrumCompleteSettings extends TasksSettingsMenu {
	  getViewId() {
	    return 'scrum-complete';
	  }
	  getMenuItems() {
	    return [BurnDownItem(this)];
	  }
	}

	const PriorityItem = (view, priority, isActive) => {
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-priority-${priority}`
	    },
	    className: isActive ? 'menu-popup-item-accept' : 'menu-popup-item-none',
	    text: main_core.Loc.getMessage(`SN_SPACES_TASKS_SCRUM_SETTINGS_${priority.toUpperCase()}`),
	    onclick: () => BX.Tasks.Scrum.Entry.setDisplayPriority(document.querySelector(`[data-id=${menuItem.dataset.id}`), priority)
	  });
	  return menuItem;
	};
	const PriorityItems = (view, priority) => [PriorityItem(view, 'backlog', priority === 'backlog'), PriorityItem(view, 'sprint', priority === 'sprint')];

	var _displayPriority = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("displayPriority");
	class ScrumPlanSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _displayPriority, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _displayPriority)[_displayPriority] = params.displayPriority;
	  }
	  getViewId() {
	    return 'scrum-plan';
	  }
	  getMenuItems() {
	    return [ReadAllItem(this, true), {
	      delimiter: true
	    }, ...PriorityItems(this, babelHelpers.classPrivateFieldLooseBase(this, _displayPriority)[_displayPriority])];
	  }
	}

	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _tasksView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksView");
	var _createSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSettings");
	class ScrumSettings extends main_core_events.EventEmitter {
	  constructor(_params) {
	    super();
	    Object.defineProperty(this, _createSettings, {
	      value: _createSettings2
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tasksView, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.TasksScrumSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksView)[_tasksView] = _params.tasksView;
	    babelHelpers.classPrivateFieldLooseBase(this, _createSettings)[_createSettings](_params);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].show();
	  }
	}
	function _createSettings2(params) {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _tasksView)[_tasksView].getCurrentViewMode()) {
	    case 'plan':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = new ScrumPlanSettings(params);
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].subscribe('realAll', () => this.emit('realAll'));
	      break;
	    case 'active':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = new ScrumActiveSettings(params);
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].subscribe('completeSprint', () => this.emit('completeSprint'));
	      break;
	    case 'complete':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = new ScrumCompleteSettings(params);
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].subscribe('showBurnDown', () => this.emit('showBurnDown'));
	      break;
	    default:
	      break;
	  }
	}

	class GroupCalendarSettings extends TasksSettingsMenu {
	  getViewId() {
	    return 'group-calendar';
	  }
	}

	var _pathToTasks$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToTasks");
	class TasksExcelManager {
	  constructor(params) {
	    Object.defineProperty(this, _pathToTasks$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks$1)[_pathToTasks$1] = params == null ? void 0 : params.pathToTasks;
	  }

	  /**
	   * @param options {{isAll: boolean}}
	   */
	  getExportHref(options = {}) {
	    let href = `${babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks$1)[_pathToTasks$1]}?F_STATE=sV80&EXPORT_AS=EXCEL&ncc=1`;
	    if (options.isAll) {
	      href += '&COLUMNS=ALL';
	    }
	    return href;
	  }
	  getImportHref() {
	    return `${babelHelpers.classPrivateFieldLooseBase(this, _pathToTasks$1)[_pathToTasks$1]}import/`;
	  }
	}

	const ExportItem = (view, excelManager) => {
	  return {
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-export-list`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_EXPORT_LIST'),
	    className: 'menu-popup-item-none',
	    items: [{
	      dataset: {
	        id: `spaces-tasks-${view.getViewId()}-settings-to-excel`
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_EXPORT_TO_EXCEL'),
	      className: 'sn-spaces-tasks-icon-excel',
	      href: excelManager.getExportHref()
	    }]
	  };
	};

	const GroupSubtasksItem = (view, params) => {
	  let shouldSubtasksBeGrouped = params.shouldSubtasksBeGrouped;
	  return new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-group-subtasks`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_GROUP_SUBTASKS'),
	    className: shouldSubtasksBeGrouped ? 'menu-popup-item-accept' : 'menu-popup-item-none',
	    onclick: (event, item) => {
	      BX.ajax.runComponentAction('bitrix:tasks.interface.filter', 'toggleGroupByTasks', {
	        mode: 'class',
	        data: {
	          userId: params.userId
	        }
	      }).then(response => {
	        if (response.status !== 'success') {
	          return;
	        }
	        main_core.Dom.toggleClass(item.layout.item, ['menu-popup-item-accept', 'menu-popup-item-none']);
	        if (BX.Main.gridManager) {
	          shouldSubtasksBeGrouped = !shouldSubtasksBeGrouped;
	          const gridInstance = BX.Main.gridManager.data[0].instance;
	          gridInstance.reloadTable();
	          main_core_events.EventEmitter.emit('BX.Tasks.Filter.group', [gridInstance, 'groupBySubTasks', shouldSubtasksBeGrouped]);
	        } else {
	          window.location.reload();
	        }
	      }, error => {
	        // eslint-disable-next-line no-console
	        console.log(error);
	      });
	    }
	  });
	};

	const ImportCsvItem = (view, excelManager) => {
	  return {
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-import`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_IMPORT_LIST'),
	    className: 'menu-popup-item-none',
	    items: [{
	      dataset: {
	        id: `spaces-tasks-${view.getViewId()}-settings-import-csv`
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_IMPORT_LIST_CSV'),
	      className: 'sn-spaces-tasks-icon-excel',
	      href: excelManager.getImportHref(),
	      onclick: () => view.close()
	    }]
	  };
	};

	var _gridId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("gridId");
	class TasksSortManager {
	  constructor(params) {
	    Object.defineProperty(this, _gridId, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId] = params.gridId;
	  }
	  setSort(taskSort) {
	    const field = taskSort.field;
	    const dir = taskSort.direction || 'asc';
	    if (BX.Main.gridManager === undefined) {
	      BX.ajax.post(BX.util.add_url_param('/bitrix/components/bitrix/main.ui.grid/settings.ajax.php', {
	        GRID_ID: babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId],
	        action: 'setSort'
	      }), {
	        by: field,
	        order: dir
	      }, res => {
	        try {
	          res = JSON.parse(res);
	          if (!res.error) {
	            window.location.reload();
	          }
	        } catch (err) {
	          console.log(err);
	        }
	      });
	    } else {
	      const grid = BX.Main.gridManager.getById(babelHelpers.classPrivateFieldLooseBase(this, _gridId)[_gridId]).instance;
	      grid.sortByColumn({
	        sort_by: field,
	        sort_order: dir
	      });
	      if (field === 'SORTING') {
	        grid.getRows().enableDragAndDrop();
	      } else {
	        grid.getRows().disableDragAndDrop();
	      }
	    }
	  }
	}

	const classSelected = 'menu-popup-item-accept';
	const classSortField = 'menu-popup-item-sort-field';
	const classSortDir = 'menu-popup-item-sort-dir';
	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _view$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("view");
	var _menuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menuItems");
	var _getMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenuItems");
	var _getFieldItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldItem");
	var _getDirectionItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDirectionItem");
	var _getRecommendedLabel$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecommendedLabel");
	var _onMenuItemClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMenuItemClick");
	var _updateStyles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateStyles");
	var _updateStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateStyle");
	class SortItem {
	  constructor(view, params) {
	    Object.defineProperty(this, _updateStyle, {
	      value: _updateStyle2
	    });
	    Object.defineProperty(this, _updateStyles, {
	      value: _updateStyles2
	    });
	    Object.defineProperty(this, _onMenuItemClick, {
	      value: _onMenuItemClick2
	    });
	    Object.defineProperty(this, _getRecommendedLabel$1, {
	      value: _getRecommendedLabel2$1
	    });
	    Object.defineProperty(this, _getDirectionItem, {
	      value: _getDirectionItem2
	    });
	    Object.defineProperty(this, _getFieldItem, {
	      value: _getFieldItem2
	    });
	    Object.defineProperty(this, _getMenuItems, {
	      value: _getMenuItems2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _view$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _menuItems, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1] = view;
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	  }
	  getItem() {
	    return {
	      dataset: {
	        id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].getViewId()}-settings-sort`
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_SORT'),
	      className: 'menu-popup-item-none menu-popup-sort',
	      events: {
	        onSubMenuShow: event => {
	          babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems] = event.target.getSubMenu().getMenuItems();
	          babelHelpers.classPrivateFieldLooseBase(this, _updateStyles)[_updateStyles](babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems]);
	        }
	      },
	      items: babelHelpers.classPrivateFieldLooseBase(this, _getMenuItems)[_getMenuItems]()
	    };
	  }
	}
	function _getMenuItems2() {
	  return [...babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortFields.flatMap(babelHelpers.classPrivateFieldLooseBase(this, _getFieldItem)[_getFieldItem].bind(this)), new main_popup.MenuItem({
	    id: 'delimiterDir',
	    delimiter: true
	  }), babelHelpers.classPrivateFieldLooseBase(this, _getDirectionItem)[_getDirectionItem]('asc'), babelHelpers.classPrivateFieldLooseBase(this, _getDirectionItem)[_getDirectionItem]('desc')];
	}
	function _getFieldItem2(field) {
	  const isActivityField = field === 'ACTIVITY_DATE';
	  const menuItem = new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].getViewId()}-settings-sort-${field}`
	    },
	    html: `
				${main_core.Loc.getMessage(`SN_SPACES_TASKS_SORT_${field}`)}
				${isActivityField ? `<span style="margin-left: 5px">${babelHelpers.classPrivateFieldLooseBase(this, _getRecommendedLabel$1)[_getRecommendedLabel$1]()}</span>` : ''}
			`,
	    value: field,
	    className: `${classSortField} menu-popup-item-none`,
	    onclick: (event, item) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onMenuItemClick)[_onMenuItemClick]('field', item);
	    }
	  });
	  if (isActivityField) {
	    return [new main_popup.MenuItem({
	      delimiter: true
	    }), menuItem, new main_popup.MenuItem({
	      delimiter: true
	    })];
	  }
	  return menuItem;
	}
	function _getDirectionItem2(direction) {
	  return new main_popup.MenuItem({
	    dataset: {
	      id: `spaces-tasks-${babelHelpers.classPrivateFieldLooseBase(this, _view$1)[_view$1].getViewId()}-settings-sort-${direction}`
	    },
	    text: main_core.Loc.getMessage(`SN_SPACES_TASKS_SORT_BY_${direction.toUpperCase()}`),
	    className: `${classSortDir} menu-popup-item-none`,
	    value: direction,
	    onclick: (event, item) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onMenuItemClick)[_onMenuItemClick]('dir', item);
	    }
	  });
	}
	function _getRecommendedLabel2$1() {
	  return new ui_label.Label({
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_SORT_RECOMMENDED_LABEL').toUpperCase(),
	    color: ui_label.LabelColor.LIGHT_BLUE,
	    fill: true,
	    size: 'ui-label-xs'
	  }).render().outerHTML;
	}
	function _onMenuItemClick2(selectedItemType, selectedItem) {
	  if (selectedItemType === 'field') {
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].taskSort.field = selectedItem.value;
	  } else if (selectedItemType === 'dir') {
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].taskSort.direction = selectedItem.value;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortManager.setSort(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].taskSort);
	  babelHelpers.classPrivateFieldLooseBase(this, _updateStyles)[_updateStyles](babelHelpers.classPrivateFieldLooseBase(this, _menuItems)[_menuItems]);
	}
	function _updateStyles2(menuItems) {
	  menuItems.forEach(item => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateStyle)[_updateStyle](item, babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].taskSort);
	  });
	}
	function _updateStyle2(item, taskSort) {
	  const itemNode = item.getContainer();
	  const isFieldItem = main_core.Dom.hasClass(itemNode, classSortField);
	  const isDirectionItem = main_core.Dom.hasClass(itemNode, classSortDir) || item.getId() === 'delimiterDir';
	  const isFieldSelected = isFieldItem && taskSort.field === item.value;
	  const isDirectionSelected = isDirectionItem && taskSort.direction === item.value;
	  if (isFieldSelected || isDirectionSelected) {
	    main_core.Dom.addClass(itemNode, classSelected);
	  } else {
	    main_core.Dom.removeClass(itemNode, classSelected);
	  }
	  if (taskSort.field === 'SORTING' && isDirectionItem) {
	    main_core.Dom.style(itemNode, 'display', 'none');
	  } else {
	    main_core.Dom.style(itemNode, 'display', '');
	  }
	}

	const SyncItem = (view, syncScript) => {
	  return {
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-sync`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_SYNC_LIST'),
	    className: 'menu-popup-item-none',
	    items: [{
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_SYNC_WITH_OUTLOOK'),
	      className: 'sn-spaces-tasks-icon-outlook',
	      // eslint-disable-next-line no-eval
	      onclick: () => eval(syncScript)
	    }]
	  };
	};

	var _excelManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("excelManager");
	var _sortManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortManager");
	var _params$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	class GroupGanttSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _excelManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sortManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _params$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _excelManager)[_excelManager] = new TasksExcelManager({
	      pathToTasks: params.pathToTasks
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sortManager)[_sortManager] = new TasksSortManager({
	      gridId: params.gridId
	    });
	  }
	  getViewId() {
	    return 'group-gantt';
	  }
	  getMenuItems() {
	    const menuItems = [ReadAllItem(this, true), GroupSubtasksItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1]), {
	      delimiter: true
	    }, new SortItem(this, {
	      sortFields: babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].sortFields,
	      taskSort: babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].taskSort,
	      sortManager: babelHelpers.classPrivateFieldLooseBase(this, _sortManager)[_sortManager]
	    }).getItem(), {
	      delimiter: true
	    }];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].permissions.import) {
	      menuItems.push(ImportCsvItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager)[_excelManager]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].permissions.export) {
	      menuItems.push(ExportItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager)[_excelManager]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].permissions.import && babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].permissions.export) {
	      menuItems.push(SyncItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].syncScript));
	    }
	    return menuItems;
	  }
	}

	const ConfigureViewItem = view => new main_popup.MenuItem({
	  dataset: {
	    id: `spaces-tasks-${view.getViewId()}-settings-configure-view`
	  },
	  text: main_core.Loc.getMessage('SN_SPACES_TASKS_CONFIGURE_VIEW'),
	  onclick: () => {
	    main_core.Runtime.loadExtension('ui.dialogs.checkbox-list').then(() => {
	      view.close();
	      main_core_events.EventEmitter.emit('tasks-kanban-settings-fields-view');
	    });
	  }
	});

	var _order$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	class GroupKanbanSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _order$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _order$2)[_order$2] = params.order;
	  }
	  getViewId() {
	    return 'group-kanban';
	  }
	  getMenuItems() {
	    return [ReadAllItem(this), ...new KanbanSortItems(this, babelHelpers.classPrivateFieldLooseBase(this, _order$2)[_order$2]).getItems(), ConfigureViewItem(this)];
	  }
	}

	const ExportExcelItem = (view, excelManager) => {
	  return {
	    dataset: {
	      id: `spaces-tasks-${view.getViewId()}-settings-export-excel`
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_TASKS_EXPORT_LIST_TO_EXCEL'),
	    className: 'menu-popup-item-none',
	    items: [{
	      dataset: {
	        id: `spaces-tasks-${view.getViewId()}-settings-export-excel-grid-fields`
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_EXPORT_GRID_FIELDS'),
	      className: 'sn-spaces-tasks-icon-excel',
	      href: excelManager.getExportHref()
	    }, {
	      dataset: {
	        id: `spaces-tasks-${view.getViewId()}-settings-export-excel-all-fields`
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_TASKS_EXPORT_ALL_FIELDS'),
	      className: 'sn-spaces-tasks-icon-excel',
	      href: excelManager.getExportHref({
	        isAll: true
	      })
	    }]
	  };
	};

	var _excelManager$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("excelManager");
	var _sortManager$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortManager");
	var _params$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	class GroupListSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _excelManager$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sortManager$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _params$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _excelManager$1)[_excelManager$1] = new TasksExcelManager({
	      pathToTasks: params.pathToTasks
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sortManager$1)[_sortManager$1] = new TasksSortManager({
	      gridId: params.gridId
	    });
	  }
	  getViewId() {
	    return 'group-list';
	  }
	  getMenuItems() {
	    const menuItems = [ReadAllItem(this, true), GroupSubtasksItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2]), {
	      delimiter: true
	    }, new SortItem(this, {
	      sortFields: babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].sortFields,
	      taskSort: babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].taskSort,
	      sortManager: babelHelpers.classPrivateFieldLooseBase(this, _sortManager$1)[_sortManager$1]
	    }).getItem(), {
	      delimiter: true
	    }];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].permissions.import) {
	      menuItems.push(ImportCsvItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$1)[_excelManager$1]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].permissions.export) {
	      menuItems.push(ExportExcelItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$1)[_excelManager$1]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].permissions.import && babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].permissions.export) {
	      menuItems.push(SyncItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].syncScript));
	    }
	    return menuItems;
	  }
	}

	var _tasksView$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksView");
	var _settings$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _createSettings$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSettings");
	class GroupSettings extends main_core_events.EventEmitter {
	  constructor(_params) {
	    super();
	    Object.defineProperty(this, _createSettings$1, {
	      value: _createSettings2$1
	    });
	    Object.defineProperty(this, _tasksView$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$1, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.TasksGroupSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksView$1)[_tasksView$1] = _params.tasksView;
	    babelHelpers.classPrivateFieldLooseBase(this, _createSettings$1)[_createSettings$1](_params);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].show();
	  }
	}
	function _createSettings2$1(params) {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _tasksView$1)[_tasksView$1].getCurrentViewMode()) {
	    case 'list':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1] = new GroupListSettings(params);
	      break;
	    case 'kanban':
	    case 'plan':
	    case 'timeline':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1] = new GroupKanbanSettings(params);
	      break;
	    case 'calendar':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1] = new GroupCalendarSettings(params);
	      break;
	    case 'gantt':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1] = new GroupGanttSettings(params);
	      break;
	    default:
	      return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$1)[_settings$1].subscribe('realAll', () => this.emit('realAll'));
	}

	let _$2 = t => t,
	  _t$2;
	var _groupId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _pathToScrumBurnDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToScrumBurnDown");
	var _router = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("router");
	var _currentCompletedSprint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentCompletedSprint");
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _showSprintSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSprintSelector");
	class TasksScrum {
	  constructor(params) {
	    Object.defineProperty(this, _showSprintSelector, {
	      value: _showSprintSelector2
	    });
	    Object.defineProperty(this, _groupId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToScrumBurnDown, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _router, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentCompletedSprint, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2] = params.groupId;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToScrumBurnDown)[_pathToScrumBurnDown] = params.pathToScrumBurnDown;
	    babelHelpers.classPrivateFieldLooseBase(this, _router)[_router] = params.router;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentCompletedSprint)[_currentCompletedSprint] = params.currentCompletedSprint;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {};
	  }
	  renderSprintSelector() {
	    const {
	      selector,
	      selectorLabel
	    } = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div
				ref="selector"
			
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes sn-spaces__toolbar-space_btn-options"
			>
				<span class="sn-spaces__toolbar-space_btn-text" ref="selectorLabel">
					${0}
				</span>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 19px;"></div>
			</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _currentCompletedSprint)[_currentCompletedSprint].selectorLabel));
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].selector = selector;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].selectorLabel = selectorLabel;
	    main_core.Event.bind(selector, 'click', babelHelpers.classPrivateFieldLooseBase(this, _showSprintSelector)[_showSprintSelector].bind(this, selector));
	    return selector;
	  }
	  showCompletionForm() {
	    const extensionName = 'Sprint-Completion-Form';
	    // eslint-disable-next-line promise/catch-or-return
	    babelHelpers.classPrivateFieldLooseBase(this, _router)[_router].showByExtension(`tasks.scrum.${extensionName.toLowerCase()}`, extensionName, {
	      groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2]
	    }).then(extension => {
	      if (extension) {
	        extension.subscribe('afterComplete', () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _router)[_router].redirectToScrumView('plan');
	        });
	        extension.subscribe('taskClick', baseEvent => {
	          babelHelpers.classPrivateFieldLooseBase(this, _router)[_router].showTask(baseEvent.getData());
	        });
	      }
	    });
	  }
	  showBurnDown() {
	    babelHelpers.classPrivateFieldLooseBase(this, _router)[_router].showSidePanel(babelHelpers.classPrivateFieldLooseBase(this, _pathToScrumBurnDown)[_pathToScrumBurnDown].replace('#sprint_id#', babelHelpers.classPrivateFieldLooseBase(this, _currentCompletedSprint)[_currentCompletedSprint].id));
	  }
	}
	function _showSprintSelector2(selectorNode) {
	  const dialog = new ui_entitySelector.Dialog({
	    targetNode: selectorNode,
	    width: 400,
	    height: 300,
	    multiple: false,
	    dropdownMode: true,
	    enableSearch: true,
	    compactView: true,
	    showAvatars: false,
	    cacheable: false,
	    preselectedItems: [['sprint-selector', babelHelpers.classPrivateFieldLooseBase(this, _currentCompletedSprint)[_currentCompletedSprint].id]],
	    entities: [{
	      id: 'sprint-selector',
	      options: {
	        groupId: babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2],
	        onlyCompleted: true
	      },
	      dynamicLoad: true,
	      dynamicSearch: true
	    }],
	    events: {
	      'Item:onSelect': event => {
	        var selectedItem = event.getData().item;
	        babelHelpers.classPrivateFieldLooseBase(this, _currentCompletedSprint)[_currentCompletedSprint].id = selectedItem.id;

	        // todo change to EventEmitter
	        // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	        BX.onCustomEvent(selectorNode, 'onTasksGroupSelectorChange', [{
	          id: babelHelpers.classPrivateFieldLooseBase(this, _groupId$2)[_groupId$2],
	          sprintId: selectedItem.id,
	          name: selectedItem.customData.get('name')
	        }]);
	        babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].selectorLabel.textContent = selectedItem.customData.get('label');
	      }
	    }
	  });
	  dialog.show();
	}

	class UserCalendarSettings extends TasksSettingsMenu {
	  getViewId() {
	    return 'user-calendar';
	  }
	}

	var _excelManager$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("excelManager");
	var _sortManager$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortManager");
	var _params$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	class UserGanttSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _excelManager$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sortManager$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _params$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _excelManager$2)[_excelManager$2] = new TasksExcelManager({
	      pathToTasks: params.pathToTasks
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sortManager$2)[_sortManager$2] = new TasksSortManager({
	      gridId: params.gridId
	    });
	  }
	  getViewId() {
	    return 'user-gantt';
	  }
	  getMenuItems() {
	    const menuItems = [ReadAllItem(this, true), GroupSubtasksItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3]), {
	      delimiter: true
	    }, new SortItem(this, {
	      sortFields: babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].sortFields,
	      taskSort: babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].taskSort,
	      sortManager: babelHelpers.classPrivateFieldLooseBase(this, _sortManager$2)[_sortManager$2]
	    }).getItem(), {
	      delimiter: true
	    }];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].permissions.import) {
	      menuItems.push(ImportCsvItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$2)[_excelManager$2]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].permissions.export) {
	      menuItems.push(ExportItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$2)[_excelManager$2]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].permissions.import && babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].permissions.export) {
	      menuItems.push(SyncItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].syncScript));
	    }
	    return menuItems;
	  }
	}

	var _order$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	class UserKanbanSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _order$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _order$3)[_order$3] = params.order;
	  }
	  getViewId() {
	    return 'user-kanban';
	  }
	  getMenuItems() {
	    return [ReadAllItem(this), ...new KanbanSortItems(this, babelHelpers.classPrivateFieldLooseBase(this, _order$3)[_order$3]).getItems(), ConfigureViewItem(this)];
	  }
	}

	var _excelManager$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("excelManager");
	var _sortManager$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortManager");
	var _params$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	class UserListSettings extends TasksSettingsMenu {
	  constructor(params) {
	    super(params);
	    Object.defineProperty(this, _excelManager$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sortManager$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _params$4, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _excelManager$3)[_excelManager$3] = new TasksExcelManager({
	      pathToTasks: params.pathToTasks
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sortManager$3)[_sortManager$3] = new TasksSortManager({
	      gridId: params.gridId
	    });
	  }
	  getViewId() {
	    return 'user-list';
	  }
	  getMenuItems() {
	    const menuItems = [ReadAllItem(this, true), GroupSubtasksItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4]), {
	      delimiter: true
	    }, new SortItem(this, {
	      sortFields: babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].sortFields,
	      taskSort: babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].taskSort,
	      sortManager: babelHelpers.classPrivateFieldLooseBase(this, _sortManager$3)[_sortManager$3]
	    }).getItem(), {
	      delimiter: true
	    }];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].permissions.import) {
	      menuItems.push(ImportCsvItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$3)[_excelManager$3]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].permissions.export) {
	      menuItems.push(ExportExcelItem(this, babelHelpers.classPrivateFieldLooseBase(this, _excelManager$3)[_excelManager$3]));
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].permissions.import && babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].permissions.export) {
	      menuItems.push(SyncItem(this, babelHelpers.classPrivateFieldLooseBase(this, _params$4)[_params$4].syncScript));
	    }
	    return menuItems;
	  }
	}

	var _tasksView$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksView");
	var _settings$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _createSettings$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createSettings");
	class UserSettings extends main_core_events.EventEmitter {
	  constructor(_params) {
	    super();
	    Object.defineProperty(this, _createSettings$2, {
	      value: _createSettings2$2
	    });
	    Object.defineProperty(this, _tasksView$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$2, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.TasksUserSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksView$2)[_tasksView$2] = _params.tasksView;
	    babelHelpers.classPrivateFieldLooseBase(this, _createSettings$2)[_createSettings$2](_params);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2].show();
	  }
	}
	function _createSettings2$2(params) {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _tasksView$2)[_tasksView$2].getCurrentViewMode()) {
	    case 'list':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2] = new UserListSettings(params);
	      break;
	    case 'plan':
	    case 'timeline':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2] = new UserKanbanSettings(params);
	      break;
	    case 'calendar':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2] = new UserCalendarSettings(params);
	      break;
	    case 'gantt':
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2] = new UserGanttSettings(params);
	      break;
	    default:
	      return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$2)[_settings$2].subscribe('realAll', () => this.emit('realAll'));
	}

	var _menu$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _createMenu$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	class TasksViewList extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _createMenu$1, {
	      value: _createMenu2$1
	    });
	    Object.defineProperty(this, _menu$1, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.TasksViewList');
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$1)[_menu$1] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$1)[_createMenu$1](params.bindElement, params.viewList);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$1)[_menu$1].toggle();
	  }
	}
	function _createMenu2$1(bindElement, viewList) {
	  const menu = new main_popup.Menu({
	    id: 'spaces-tasks-view-list',
	    className: 'sn-spaces-tasks-view-list-menu',
	    bindElement,
	    closeByEsc: true
	  });
	  viewList.forEach(viewItem => {
	    menu.addMenuItem({
	      dataset: {
	        id: `spaces-tasks-${viewItem.key}`
	      },
	      text: viewItem.title,
	      className: `sn-spaces-tasks-${viewItem.key}-icon ${viewItem.selected ? '--selected' : ''}`,
	      onclick: () => {
	        if (viewItem.selected) {
	          menu.close();
	          return;
	        }
	        this.emit('click', {
	          urlParam: viewItem.urlParam,
	          urlValue: viewItem.urlValue
	        });
	      }
	    });
	  });
	  return menu;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$1,
	  _t3$1,
	  _t4;
	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _filter$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _router$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("router");
	var _counters$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("counters");
	var _tasksView$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksView");
	var _tasksScrum = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksScrum");
	var _tasksViewList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tasksViewList");
	var _settings$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _setParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _renderAddBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAddBtn");
	var _renderScrumAddBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderScrumAddBtn");
	var _renderViewBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderViewBtn");
	var _renderSettingsBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsBtn");
	var _addMenuClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMenuClick");
	var _viewClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewClick");
	var _settingsClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsClick");
	class TasksToolbar {
	  constructor(_params) {
	    Object.defineProperty(this, _settingsClick, {
	      value: _settingsClick2
	    });
	    Object.defineProperty(this, _viewClick, {
	      value: _viewClick2
	    });
	    Object.defineProperty(this, _addMenuClick, {
	      value: _addMenuClick2
	    });
	    Object.defineProperty(this, _renderSettingsBtn, {
	      value: _renderSettingsBtn2
	    });
	    Object.defineProperty(this, _renderViewBtn, {
	      value: _renderViewBtn2
	    });
	    Object.defineProperty(this, _renderScrumAddBtn, {
	      value: _renderScrumAddBtn2
	    });
	    Object.defineProperty(this, _renderAddBtn, {
	      value: _renderAddBtn2
	    });
	    Object.defineProperty(this, _getParam, {
	      value: _getParam2
	    });
	    Object.defineProperty(this, _setParams, {
	      value: _setParams2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _filter$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _router$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _counters$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tasksView$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tasksScrum, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _tasksViewList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams)[_setParams](_params);
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$2)[_filter$2] = new Filter({
	      filterId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('filterId'),
	      filterContainer: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('filterContainer')
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isUserSpace')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _router$1)[_router$1] = new TasksRouter({
	        pathToTasks: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToUserSpaceTasks'),
	        pathToTasksTask: ''
	      });
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _router$1)[_router$1] = new TasksRouter({
	        pathToTasks: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToGroupTasks'),
	        pathToTasksTask: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToGroupTasksTask')
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3] = new TasksView({
	      isUserSpace: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isUserSpace'),
	      isScrumSpace: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isScrumSpace'),
	      viewMode: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('viewMode')
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isScrumSpace')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _tasksScrum)[_tasksScrum] = new TasksScrum({
	        groupId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('groupId'),
	        pathToScrumBurnDown: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToScrumBurnDown'),
	        router: babelHelpers.classPrivateFieldLooseBase(this, _router$1)[_router$1],
	        currentCompletedSprint: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('currentCompletedSprint')
	      });
	    }
	  }
	  renderAddBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAddBtn)[_renderAddBtn](), container);
	  }
	  renderScrumAddBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderScrumAddBtn)[_renderScrumAddBtn](), container);
	  }
	  renderCountersTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
	    }
	    const ignoreList = new Set([]);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isScrumSpace')) {
	      ignoreList.add('active');
	      ignoreList.add('complete');
	    }
	    if (ignoreList.has(babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3].getCurrentViewMode())) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _counters$1)[_counters$1] = new TasksCounters({
	      filter: babelHelpers.classPrivateFieldLooseBase(this, _filter$2)[_filter$2],
	      filterRole: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('filterRole'),
	      userId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('userId'),
	      groupId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('groupId'),
	      counters: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('counters'),
	      isUserSpace: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isUserSpace'),
	      isScrumSpace: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isScrumSpace'),
	      tasksView: babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3]
	    });
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _counters$1)[_counters$1].render(), container);
	  }
	  renderViewBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for view btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderViewBtn)[_renderViewBtn](), container);
	  }
	  renderScrumShortView(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for short view btn not found');
	    }
	    const shortView = new ui_shortView.ShortView({
	      isShortView: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isShortView')
	    });
	    shortView.renderTo(container);
	    shortView.subscribe('change', baseEvent => {
	      BX.Tasks.Scrum.Entry.changeShortView(baseEvent.getData());
	    });
	  }
	  renderScrumSprintSelector(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for sprint selector not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _tasksScrum)[_tasksScrum].renderSprintSelector(), container);
	  }
	  renderScrumRobots(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for robots btn not found');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('activeSprintId') > 0 && babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('canEditSprint') === 'Y') {
	      const tasksRobots = new TasksRobots({
	        groupId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('groupId'),
	        isTaskLimitsExceeded: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isTaskLimitsExceeded'),
	        canUseAutomation: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('canUseAutomation'),
	        sourceAnalytics: 'scrumActiveSprint'
	      });
	      main_core.Dom.append(tasksRobots.renderBtn(), container);
	    }
	  }
	  renderSettingsBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for settings btn not found');
	    }
	    const ignoreList = new Set([]);
	    if (ignoreList.has(babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3].getCurrentViewMode())) {
	      return;
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsBtn)[_renderSettingsBtn](), container);
	  }
	}
	function _setParams2(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].set('params', params);
	}
	function _getParam2(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].get('params')[param];
	}
	function _renderAddBtn2() {
	  const node = main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps">
				<a
					data-id="spaces-tasks-add-main-btn"
					class="ui-btn-main"
					href="${0}"
				>
					${0}
				</a>
				<button class="ui-btn-menu" data-id="spaces-tasks-add-menu-btn"></button>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToAddTask'), main_core.Loc.getMessage('SN_SPACES_TASKS_ADD_TASK'));
	  main_core.Event.bind(node.querySelector('.ui-btn-menu'), 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMenuClick)[_addMenuClick].bind(this));
	  return node;
	}
	function _renderScrumAddBtn2() {
	  const node = main_core.Tag.render(_t2$1 || (_t2$1 = _$3`
			<div class="ui-btn-split ui-btn-round ui-btn-no-caps ui-btn-light-border ui-btn-themes">
				<a
					data-id="spaces-tasks-add-main-btn"
					class="ui-btn-main"
					href="${0}"
				>
					${0}
				</a>
				<button class="ui-btn-menu" data-id="spaces-tasks-add-menu-btn"></button>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToAddTask'), main_core.Loc.getMessage('SN_SPACES_TASKS_ADD_TASK'));
	  main_core.Event.bind(node.querySelector('.ui-btn-menu'), 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMenuClick)[_addMenuClick].bind(this));
	  return node;
	}
	function _renderViewBtn2() {
	  const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes';
	  const node = main_core.Tag.render(_t3$1 || (_t3$1 = _$3`
			<button
				data-id="sn-spaces-tasks-view-mode-btn"
				class="${0} sn-spaces__toolbar-space_btn-options"
			>
				<div
					class="ui-icon-set --customer-cards"
					style="--ui-icon-set__icon-size: 25px;"
				></div>
				<div class="sn-spaces__toolbar-space_btn-text">
					${0}
				</div>
				<div
					class="ui-icon-set --chevron-down"
					style="--ui-icon-set__icon-size: 19px;"
				></div>
			</button>
		`), uiClasses, main_core.Loc.getMessage('SN_SPACES_TASKS_VIEW_BTN'));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _viewClick)[_viewClick].bind(this));
	  return node;
	}
	function _renderSettingsBtn2() {
	  const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';
	  const node = main_core.Tag.render(_t4 || (_t4 = _$3`
			<button
				data-id="spaces-tasks-settings-btn"
				class="${0} sn-spaces__toolbar-space_btn-more"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`), uiClasses);
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _settingsClick)[_settingsClick].bind(this));
	  return node;
	}
	function _addMenuClick2(event) {
	  tasks_creationMenu.CreationMenu.toggle({
	    bindElement: event.srcElement,
	    createTaskLink: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToAddTask'),
	    templatesListLink: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToTemplateList')
	  });
	}
	function _viewClick2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _tasksViewList)[_tasksViewList]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksViewList)[_tasksViewList] = new TasksViewList({
	      bindElement: event.target,
	      viewList: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('viewList')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _tasksViewList)[_tasksViewList].subscribe('click', baseEvent => {
	      const {
	        urlParam,
	        urlValue
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _router$1)[_router$1].redirectToTasks(urlParam, urlValue);
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _tasksViewList)[_tasksViewList].show();
	}
	function _settingsClick2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3]) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isUserSpace')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3] = new UserSettings({
	        bindElement: event.target,
	        tasksView: babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3],
	        pathToTasks: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToTasks'),
	        order: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('order'),
	        shouldSubtasksBeGrouped: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('shouldSubtasksBeGrouped'),
	        userId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('userId'),
	        gridId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('gridId'),
	        sortFields: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('sortFields'),
	        taskSort: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('taskSort'),
	        syncScript: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('syncScript'),
	        permissions: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('permissions')
	      });
	    } else {
	      // eslint-disable-next-line no-lonely-if
	      if (babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('isScrumSpace')) {
	        babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3] = new ScrumSettings({
	          bindElement: event.target,
	          displayPriority: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('displayPriority'),
	          tasksView: babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3],
	          router: babelHelpers.classPrivateFieldLooseBase(this, _router$1)[_router$1],
	          order: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('order'),
	          activeSprintExists: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('activeSprintId') > 0,
	          canCompleteSprint: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('canEditSprint') === 'Y'
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3].subscribe('completeSprint', () => babelHelpers.classPrivateFieldLooseBase(this, _tasksScrum)[_tasksScrum].showCompletionForm());
	        babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3].subscribe('showBurnDown', () => babelHelpers.classPrivateFieldLooseBase(this, _tasksScrum)[_tasksScrum].showBurnDown());
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3] = new GroupSettings({
	          bindElement: event.target,
	          tasksView: babelHelpers.classPrivateFieldLooseBase(this, _tasksView$3)[_tasksView$3],
	          pathToTasks: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('pathToTasks'),
	          order: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('order'),
	          shouldSubtasksBeGrouped: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('shouldSubtasksBeGrouped'),
	          userId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('userId'),
	          gridId: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('gridId'),
	          sortFields: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('sortFields'),
	          taskSort: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('taskSort'),
	          syncScript: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('syncScript'),
	          permissions: babelHelpers.classPrivateFieldLooseBase(this, _getParam)[_getParam]('permissions')
	        });
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3].subscribe('realAll', () => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _counters$1)[_counters$1]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _counters$1)[_counters$1].readAll();
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$3)[_settings$3].show();
	}

	var _menu$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _documentHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentHandlers");
	var _createMenu$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	class FilesAddSettings extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _createMenu$2, {
	      value: _createMenu2$2
	    });
	    Object.defineProperty(this, _menu$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentHandlers, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.FilesAddSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _documentHandlers)[_documentHandlers] = params.documentHandlers;
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$2)[_menu$2] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$2)[_createMenu$2](params.bindElement);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$2)[_menu$2].toggle();
	  }
	}
	function _createMenu2$2(bindElement) {
	  const fileUploadItemId = 'spaces-files-add-file-item';
	  const menu = new main_popup.Menu({
	    id: 'spaces-files-add-settings',
	    bindElement,
	    closeByEsc: true,
	    events: {
	      onShow: event => {
	        this.emit('show', {
	          fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer()
	        });
	      },
	      onClose: () => {
	        this.emit('close', {
	          fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer()
	        });
	      }
	    }
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-files-add-settings-file'
	    },
	    id: fileUploadItemId,
	    text: main_core.Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_FILE')
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-files-add-settings-folder'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_FOLDER'),
	    onclick: () => {
	      this.emit('addFolder');
	    }
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _documentHandlers)[_documentHandlers].forEach(handler => {
	    menu.addMenuItem({
	      dataset: {
	        id: `spaces-files-add-settings-type-${handler.code}`
	      },
	      text: handler.name,
	      items: [{
	        text: main_core.Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_DOC'),
	        onclick: () => {
	          menu.close();
	          this.emit('addDoc', {
	            handlerCode: handler.code
	          });
	        }
	      }, {
	        text: main_core.Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_TABLE'),
	        onclick: () => {
	          menu.close();
	          this.emit('addTable', {
	            handlerCode: handler.code
	          });
	        }
	      }, {
	        text: main_core.Loc.getMessage('SN_SPACES_FILES_ADD_SETTINGS_PRESENTATION'),
	        onclick: () => {
	          menu.close();
	          this.emit('addPresentation', {
	            handlerCode: handler.code
	          });
	        }
	      }]
	    });
	  });
	  return menu;
	}

	var _pathToTrash = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToTrash");
	var _pathToVolume = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToVolume");
	class FilesRouter {
	  constructor(params) {
	    Object.defineProperty(this, _pathToTrash, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToVolume, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToTrash)[_pathToTrash] = params.pathToTrash;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToVolume)[_pathToVolume] = params.pathToUserFilesVolume;
	  }
	  redirectToTrash() {
	    top.BX.Socialnetwork.Spaces.space.reloadPageContent(babelHelpers.classPrivateFieldLooseBase(this, _pathToTrash)[_pathToTrash]);
	  }
	  redirectToVolume() {
	    top.location.href = babelHelpers.classPrivateFieldLooseBase(this, _pathToVolume)[_pathToVolume];
	  }
	}

	var _cache$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _menu$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _setParams$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _createMenu$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	class FilesSettings extends main_core_events.EventEmitter {
	  constructor(_params) {
	    super();
	    Object.defineProperty(this, _createMenu$3, {
	      value: _createMenu2$3
	    });
	    Object.defineProperty(this, _getParam$1, {
	      value: _getParam2$1
	    });
	    Object.defineProperty(this, _setParams$1, {
	      value: _setParams2$1
	    });
	    Object.defineProperty(this, _cache$1, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _menu$3, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.FilesSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams$1)[_setParams$1](_params);
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$3)[_menu$3] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$3)[_createMenu$3](_params.bindElement);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$3)[_menu$3].toggle();
	  }
	}
	function _setParams2$1(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].set('params', params);
	}
	function _getParam2$1(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache$1)[_cache$1].get('params')[param];
	}
	function _createMenu2$3(bindElement) {
	  const menu = new main_popup.Menu({
	    id: 'spaces-files-settings',
	    bindElement,
	    closeByEsc: true
	  });
	  const permissions = babelHelpers.classPrivateFieldLooseBase(this, _getParam$1)[_getParam$1]('permissions');
	  if (permissions.canChangeRights === true) {
	    menu.addMenuItem({
	      dataset: {
	        id: 'spaces-files-settings-rights'
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_RIGHTS'),
	      onclick: () => {
	        this.emit('rights');
	      }
	    });
	  }
	  if (permissions.canChangeBizproc === true) {
	    menu.addMenuItem({
	      dataset: {
	        id: 'spaces-files-settings-bizproc'
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_FILES_BIZPROC'),
	      onclick: () => {
	        this.emit('bizproc');
	      }
	    });
	  }
	  if (permissions.canChangeBizprocSettings === true) {
	    menu.addMenuItem({
	      dataset: {
	        id: 'spaces-files-settings-config-bizproc'
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_BIZPROC'),
	      onclick: () => {
	        this.emit('bizprocSettings');
	      }
	    });
	  }
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-files-settings-network'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_NETWORK'),
	    onclick: () => {
	      this.emit('network');
	    }
	  });
	  menu.addMenuItem({
	    dataset: {
	      id: 'spaces-files-settings-doc'
	    },
	    text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_DOC'),
	    onclick: () => {
	      this.emit('docSettings');
	    }
	  });
	  if (permissions.canCleanFiles === true) {
	    menu.addMenuItem({
	      dataset: {
	        id: 'spaces-files-settings-clean'
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_CLEAN'),
	      onclick: () => {
	        this.emit('clean');
	      }
	    });
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _getParam$1)[_getParam$1]('isTrashMode')) {
	    menu.addMenuItem({
	      dataset: {
	        id: 'spaces-files-settings-trash'
	      },
	      text: main_core.Loc.getMessage('SN_SPACES_FILES_SETTINGS_TRASH'),
	      onclick: () => {
	        this.emit('trash');
	      }
	    });
	  }
	  return menu;
	}

	var _cache$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _setParams$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _getDiskFolderList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDiskFolderList");
	class FilesDisk {
	  constructor(_params) {
	    Object.defineProperty(this, _getDiskFolderList, {
	      value: _getDiskFolderList2
	    });
	    Object.defineProperty(this, _getParam$2, {
	      value: _getParam2$2
	    });
	    Object.defineProperty(this, _setParams$2, {
	      value: _setParams2$2
	    });
	    Object.defineProperty(this, _cache$2, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams$2)[_setParams$2](_params);
	  }
	  createFolder() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().createFolder();
	  }
	  appendUploadInput(container) {
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	    BX.onCustomEvent(window, 'onDiskUploadPopupShow', [container]);
	  }
	  hideUploadInput(container) {
	    // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	    BX.onCustomEvent(window, 'onDiskUploadPopupClose', [container]);
	  }
	  runCreatingDocFile(code) {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().runCreatingFile('docx', code);
	  }
	  runCreatingTableFile(code) {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().runCreatingFile('xlsx', code);
	  }
	  runCreatingPresentationFile(code) {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().runCreatingFile('pptx', code);
	  }
	  showRights() {
	    const listAvailableFeatures = babelHelpers.classPrivateFieldLooseBase(this, _getParam$2)[_getParam$2]('listAvailableFeatures');
	    const featureRestrictionMap = babelHelpers.classPrivateFieldLooseBase(this, _getParam$2)[_getParam$2]('featureRestrictionMap');
	    if (listAvailableFeatures.disk_folder_sharing === true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().showRightsOnStorage();
	    } else {
	      BX.UI.InfoHelper.show(featureRestrictionMap.disk_folder_sharing);
	    }
	  }
	  showBizproc() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().openSlider(babelHelpers.classPrivateFieldLooseBase(this, _getParam$2)[_getParam$2]('pathToFilesBizprocWorkflowAdmin'));
	  }
	  showBizprocSettings() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().showSettingsOnBizproc();
	  }
	  showNetworkDriveConnect() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().showNetworkDriveConnect({
	      link: babelHelpers.classPrivateFieldLooseBase(this, _getParam$2)[_getParam$2]('networkDriveLink')
	    });
	  }
	  openWindowForSelectDocumentService() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().openWindowForSelectDocumentService({});
	  }
	  cleanTrash() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getDiskFolderList)[_getDiskFolderList]().openConfirmEmptyTrash();
	  }
	}
	function _setParams2$2(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache$2)[_cache$2].set('params', params);
	}
	function _getParam2$2(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache$2)[_cache$2].get('params')[param];
	}
	function _getDiskFolderList2() {
	  return BX.Disk[`FolderListClass_${babelHelpers.classPrivateFieldLooseBase(this, _getParam$2)[_getParam$2]('diskComponentId')}`];
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3$2;
	var _cache$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _router$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("router");
	var _disk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disk");
	var _addSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addSettings");
	var _settings$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _filter$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _setParams$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _renderAddBtn$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAddBtn");
	var _renderCleanBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCleanBtn");
	var _renderSettingsBtn$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsBtn");
	var _addMenuClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMenuClick");
	var _cleanClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cleanClick");
	var _settingsClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsClick");
	class FilesToolbar {
	  constructor(_params) {
	    Object.defineProperty(this, _settingsClick$1, {
	      value: _settingsClick2$1
	    });
	    Object.defineProperty(this, _cleanClick, {
	      value: _cleanClick2
	    });
	    Object.defineProperty(this, _addMenuClick$1, {
	      value: _addMenuClick2$1
	    });
	    Object.defineProperty(this, _renderSettingsBtn$1, {
	      value: _renderSettingsBtn2$1
	    });
	    Object.defineProperty(this, _renderCleanBtn, {
	      value: _renderCleanBtn2
	    });
	    Object.defineProperty(this, _renderAddBtn$1, {
	      value: _renderAddBtn2$1
	    });
	    Object.defineProperty(this, _getParam$3, {
	      value: _getParam2$3
	    });
	    Object.defineProperty(this, _setParams$3, {
	      value: _setParams2$3
	    });
	    Object.defineProperty(this, _cache$3, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _router$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _disk, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _addSettings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filter$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams$3)[_setParams$3](_params);
	    babelHelpers.classPrivateFieldLooseBase(this, _router$2)[_router$2] = new FilesRouter({
	      pathToTrash: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('pathToTrash'),
	      pathToUserFilesVolume: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('pathToUserFilesVolume')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk] = new FilesDisk({
	      diskComponentId: _params.diskComponentId,
	      networkDriveLink: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('networkDriveLink'),
	      pathToFilesBizprocWorkflowAdmin: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('pathToFilesBizprocWorkflowAdmin'),
	      listAvailableFeatures: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('listAvailableFeatures'),
	      featureRestrictionMap: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('featureRestrictionMap')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$3)[_filter$3] = new Filter({
	      filterId: _params.filterId,
	      filterContainer: _params.filterContainer
	    });
	  }
	  renderAddBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAddBtn$1)[_renderAddBtn$1](), container);
	  }
	  renderCleanBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderCleanBtn)[_renderCleanBtn](), container);
	  }
	  renderSettingsBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.FilesToolbar: HTMLElement for settings btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsBtn$1)[_renderSettingsBtn$1](), container);
	  }
	}
	function _setParams2$3(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache$3)[_cache$3].set('params', params);
	}
	function _getParam2$3(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache$3)[_cache$3].get('params')[param];
	}
	function _renderAddBtn2$1() {
	  const node = main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps">
				<div data-id="spaces-files-add-main-btn" class="ui-btn-main">
					${0}
				</div>
				<div data-id="spaces-files-add-menu-btn" class="ui-btn-menu"></div>
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_FILES_ADD_FILE'));
	  main_core.Event.bind(node.querySelector('.ui-btn-menu'), 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMenuClick$1)[_addMenuClick$1].bind(this));
	  main_core.Event.bind(node.querySelector('.ui-btn-main'), 'mouseenter', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].appendUploadInput(event.target);
	  });
	  main_core.Event.bind(node.querySelector('.ui-btn-main'), 'mouseleave', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].hideUploadInput(event.target);
	  });
	  return node;
	}
	function _renderCleanBtn2() {
	  const node = main_core.Tag.render(_t2$2 || (_t2$2 = _$4`
			<div class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps">
				${0}
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_FILES_CLEAN_BTN'));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _cleanClick)[_cleanClick].bind(this));
	  return node;
	}
	function _renderSettingsBtn2$1() {
	  const node = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
			<button
				data-id="spaces-files-settings-btn"
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes sn-spaces__toolbar-space_btn-more"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _settingsClick$1)[_settingsClick$1].bind(this));
	  return node;
	}
	function _addMenuClick2$1(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings] = new FilesAddSettings({
	      bindElement: event.target,
	      documentHandlers: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('documentHandlers')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('show', baseEvent => {
	      const {
	        fileUploadContainer
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].appendUploadInput(fileUploadContainer);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('close', baseEvent => {
	      const {
	        fileUploadContainer
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].hideUploadInput(fileUploadContainer);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('addFolder', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].createFolder();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('addDoc', baseEvent => {
	      const {
	        handlerCode
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].runCreatingDocFile(handlerCode);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('addTable', baseEvent => {
	      const {
	        handlerCode
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].runCreatingTableFile(handlerCode);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].subscribe('addPresentation', baseEvent => {
	      const {
	        handlerCode
	      } = baseEvent.getData();
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].runCreatingPresentationFile(handlerCode);
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _addSettings)[_addSettings].show();
	}
	function _cleanClick2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].cleanTrash();
	}
	function _settingsClick2$1(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4] = new FilesSettings({
	      bindElement: event.target,
	      permissions: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('permissions'),
	      featureRestrictionMap: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('featureRestrictionMap'),
	      isTrashMode: babelHelpers.classPrivateFieldLooseBase(this, _getParam$3)[_getParam$3]('isTrashMode')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('rights', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].showRights();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('bizproc', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].showBizproc();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('bizprocSettings', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].showBizprocSettings();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('network', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].showNetworkDriveConnect();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('clean', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _router$2)[_router$2].redirectToVolume();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('docSettings', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _disk)[_disk].openWindowForSelectDocumentService();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].subscribe('trash', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _router$2)[_router$2].redirectToTrash();
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$4)[_settings$4].show();
	}

	var _type = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("type");
	var _locationAccess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("locationAccess");
	var _userId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userId");
	var _ownerId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ownerId");
	var _calendarInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarInstance");
	var _isTypeAllowed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTypeAllowed");
	var _setCalendarInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCalendarInstance");
	var _getUrlForAddTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUrlForAddTask");
	class Calendar {
	  constructor(params) {
	    Object.defineProperty(this, _getUrlForAddTask, {
	      value: _getUrlForAddTask2
	    });
	    Object.defineProperty(this, _setCalendarInstance, {
	      value: _setCalendarInstance2
	    });
	    Object.defineProperty(this, _isTypeAllowed, {
	      value: _isTypeAllowed2
	    });
	    Object.defineProperty(this, _type, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _locationAccess, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _ownerId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendarInstance, {
	      writable: true,
	      value: null
	    });
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isTypeAllowed)[_isTypeAllowed](params.type)) {
	      throw new Error('BX.Socialnetwork.Spaces.Calendar: calendar type is not allowed');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = params.type;
	    babelHelpers.classPrivateFieldLooseBase(this, _userId$1)[_userId$1] = params.userId;
	    babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId] = params.ownerId;
	    babelHelpers.classPrivateFieldLooseBase(this, _locationAccess)[_locationAccess] = params.locationAccess;
	  }
	  getCalendarInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _calendarInstance)[_calendarInstance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setCalendarInstance)[_setCalendarInstance]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _calendarInstance)[_calendarInstance];
	  }
	  addEvent() {
	    calendar_entry.EntryManager.openEditSlider({
	      type: babelHelpers.classPrivateFieldLooseBase(this, _type)[_type],
	      isLocationCalendar: false,
	      locationAccess: babelHelpers.classPrivateFieldLooseBase(this, _locationAccess)[_locationAccess],
	      ownerId: babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId],
	      userId: babelHelpers.classPrivateFieldLooseBase(this, _userId$1)[_userId$1]
	    });
	  }
	  addTask() {
	    BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldLooseBase(this, _getUrlForAddTask)[_getUrlForAddTask](babelHelpers.classPrivateFieldLooseBase(this, _type)[_type]), {
	      loader: 'task-new-loader'
	    });
	  }
	}
	function _isTypeAllowed2(type) {
	  const allowedType = ['user', 'group'];
	  return allowedType.includes(type);
	}
	function _setCalendarInstance2() {
	  if (main_core.Type.isUndefined(window.BXEventCalendar)) {
	    throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: BXEventCalendar is not allowed');
	  }
	  const calendarId = Object.keys(window.BXEventCalendar.instances)[0];
	  babelHelpers.classPrivateFieldLooseBase(this, _calendarInstance)[_calendarInstance] = window.BXEventCalendar.instances[calendarId];
	}
	function _getUrlForAddTask2(type) {
	  let url = '';
	  switch (type) {
	    case 'group':
	      url = `/workgroups/group/${babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId]}`;
	      break;
	    case 'user':
	      url = `/company/personal/user/${babelHelpers.classPrivateFieldLooseBase(this, _ownerId)[_ownerId]}`;
	      break;
	    default:
	      throw new Error('BX.Socialnetwork.Spaces.Calendar: url for add task is empty');
	  }
	  return `${url}/tasks/task/edit/0/`;
	}

	var _menu$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _calendar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendar");
	var _createMenu$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	class CalendarAddButtonMenu extends main_core_events.EventEmitter {
	  constructor(params) {
	    var _params$showMenu;
	    super();
	    Object.defineProperty(this, _createMenu$4, {
	      value: _createMenu2$4
	    });
	    Object.defineProperty(this, _menu$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendar, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.CalendarAddButtonMenu');
	    if ((_params$showMenu = params.showMenu) != null ? _params$showMenu : true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _menu$4)[_menu$4] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$4)[_createMenu$4](params.bindElement);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar] = params.calendar;
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$4)[_menu$4].show();
	  }
	}
	function _createMenu2$4(bindElement) {
	  const menu = new main_popup.Menu({
	    id: 'spaces-calendar-add-button-menu',
	    bindElement,
	    closeByEsc: true
	  });
	  menu.addMenuItem({
	    text: main_core.Loc.getMessage('SN_SPACES_CALENDAR_CREATE_EVENT'),
	    dataset: {
	      id: 'spaces-calendar-add-button-menu-create-event'
	    },
	    onclick: () => {
	      menu.close();
	      babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar].addEvent();
	    }
	  });
	  menu.addMenuItem({
	    text: main_core.Loc.getMessage('SN_SPACES_CALENDAR_CREATE_TASK'),
	    dataset: {
	      id: 'spaces-calendar-add-button-menu-create-task'
	    },
	    onclick: () => {
	      menu.close();
	      babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar].addTask();
	    }
	  });
	  return menu;
	}

	var _menu$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _createMenu$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	var _openCalendars = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openCalendars");
	var _openSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openSettings");
	var _getSectionInterface = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSectionInterface");
	var _getSettingsInterface = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSettingsInterface");
	class CalendarSettings extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _getSettingsInterface, {
	      value: _getSettingsInterface2
	    });
	    Object.defineProperty(this, _getSectionInterface, {
	      value: _getSectionInterface2
	    });
	    Object.defineProperty(this, _openSettings, {
	      value: _openSettings2
	    });
	    Object.defineProperty(this, _openCalendars, {
	      value: _openCalendars2
	    });
	    Object.defineProperty(this, _createMenu$5, {
	      value: _createMenu2$5
	    });
	    Object.defineProperty(this, _menu$5, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.CalendarSettings');
	    if (main_core.Type.isNil(params.calendar)) {
	      throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: calendar is not available');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$5)[_menu$5] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$5)[_createMenu$5](params.bindElement, params.calendar);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$5)[_menu$5].show();
	  }
	}
	function _createMenu2$5(bindElement, calendar) {
	  const menu = new main_popup.Menu({
	    id: 'spaces-calendar-settings',
	    bindElement,
	    closeByEsc: true
	  });
	  if (calendar.getCalendarInstance().util.userIsOwner() || calendar.getCalendarInstance().util.config.TYPE_ACCESS) {
	    menu.addMenuItem({
	      text: main_core.Loc.getMessage('SN_SPACES_CALENDAR_SETTINGS_SETTINGS'),
	      onclick: () => {
	        menu.close();
	        babelHelpers.classPrivateFieldLooseBase(this, _openSettings)[_openSettings](calendar);
	      }
	    });
	  }
	  menu.addMenuItem({
	    text: main_core.Loc.getMessage('SN_SPACES_CALENDAR_SETTINGS_CALENDARS'),
	    onclick: () => {
	      menu.close();
	      babelHelpers.classPrivateFieldLooseBase(this, _openCalendars)[_openCalendars](calendar);
	    }
	  });
	  return menu;
	}
	function _openCalendars2(calendar) {
	  // eslint-disable-next-line promise/catch-or-return
	  babelHelpers.classPrivateFieldLooseBase(this, _getSectionInterface)[_getSectionInterface]().then(SectionInterface => {
	    if (!this.sectionInterface) {
	      const calendarInstance = calendar.getCalendarInstance();
	      this.sectionInterface = new SectionInterface({
	        calendarContext: calendarInstance,
	        readonly: calendarInstance.util.readOnlyMode(),
	        sectionManager: calendarInstance.sectionManager
	      });
	    }
	    this.sectionInterface.show();
	  });
	}
	function _openSettings2(calendar) {
	  // eslint-disable-next-line promise/catch-or-return
	  babelHelpers.classPrivateFieldLooseBase(this, _getSettingsInterface)[_getSettingsInterface]().then(SettingsInterface => {
	    if (!this.settingsInterface) {
	      const calendarInstance = calendar.getCalendarInstance();
	      if (main_core.Type.isNull(calendarInstance)) {
	        throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: calendar instance is not available');
	      }
	      this.settingsInterface = new SettingsInterface({
	        calendarContext: calendarInstance,
	        showPersonalSettings: calendarInstance.util.userIsOwner(),
	        showGeneralSettings: Boolean(calendarInstance.util.config.perm && calendarInstance.util.config.perm.access),
	        settings: calendarInstance.util.config.settings
	      });
	    }
	    this.settingsInterface.show();
	  });
	}
	function _getSectionInterface2() {
	  return new Promise(resolve => {
	    const bx = BX.Calendar.Util.getBX();
	    if (bx.Calendar.SectionInterface) {
	      resolve(bx.Calendar.SectionInterface);
	    } else {
	      const extensionName = 'calendar.sectioninterface';
	      // eslint-disable-next-line promise/catch-or-return
	      bx.Runtime.loadExtension(extensionName).then(() => {
	        if (bx.Calendar.SectionInterface) {
	          resolve(bx.Calendar.SectionInterface);
	        } else {
	          console.error(`Extension ${extensionName} not found`);
	        }
	      });
	    }
	  });
	}
	function _getSettingsInterface2() {
	  return new Promise(resolve => {
	    const bx = BX.Calendar.Util.getBX();
	    if (bx.Calendar.SettingsInterface) {
	      resolve(bx.Calendar.SettingsInterface);
	    } else {
	      const extensionName = 'calendar.settingsinterface';
	      // eslint-disable-next-line promise/catch-or-return
	      bx.Runtime.loadExtension(extensionName).then(() => {
	        if (bx.Calendar.SettingsInterface) {
	          resolve(bx.Calendar.SettingsInterface);
	        } else {
	          console.error(`Extension ${extensionName} not found`);
	        }
	      });
	    }
	  });
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3;
	var _calendar$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendar");
	var _addButtonMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addButtonMenu");
	var _settings$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _filter$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _renderAddBtn$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAddBtn");
	var _renderSettingsBtn$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsBtn");
	var _createAddSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAddSettings");
	var _addMainClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMainClick");
	var _addMenuClick$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMenuClick");
	var _settingsClick$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsClick");
	class CalendarToolbar {
	  constructor(params) {
	    var _ref;
	    Object.defineProperty(this, _settingsClick$2, {
	      value: _settingsClick2$2
	    });
	    Object.defineProperty(this, _addMenuClick$2, {
	      value: _addMenuClick2$2
	    });
	    Object.defineProperty(this, _addMainClick, {
	      value: _addMainClick2
	    });
	    Object.defineProperty(this, _createAddSettings, {
	      value: _createAddSettings2
	    });
	    Object.defineProperty(this, _renderSettingsBtn$2, {
	      value: _renderSettingsBtn2$2
	    });
	    Object.defineProperty(this, _renderAddBtn$2, {
	      value: _renderAddBtn2$2
	    });
	    Object.defineProperty(this, _calendar$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _addButtonMenu, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filter$4, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _calendar$1)[_calendar$1] = new Calendar({
	      type: params.type,
	      // eslint-disable-next-line no-constant-binary-expression
	      locationAccess: (_ref = params.locationAccess === '1') != null ? _ref : false,
	      userId: params.userId,
	      ownerId: params.ownerId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$4)[_filter$4] = new Filter({
	      filterId: params.filterId,
	      filterContainer: params.filterContainer
	    });
	  }
	  renderAddBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.CalendarToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAddBtn$2)[_renderAddBtn$2](), container);
	  }
	  renderSettingsBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.CalendarToolbar: HTMLElement for settings btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsBtn$2)[_renderSettingsBtn$2](), container);
	  }
	}
	function _renderAddBtn2$2() {
	  const {
	    node,
	    mainBtn,
	    menuBtn
	  } = main_core.Tag.render(_t$5 || (_t$5 = _$5`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps" ref="node">
				<button class="ui-btn-main" data-id="spaces-calendar-add-main-btn" ref="mainBtn">
					${0}
				</button>
				<button 
					class="ui-btn-menu" 
					id="spaces-calendar-toolbar-menu" 
					data-id="spaces-calendar-add-menu-btn"
					ref="menuBtn"
				>		
				</button>
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_CALENDAR_CREATE_MEETING'));
	  main_core.Event.bind(mainBtn, 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMainClick)[_addMainClick].bind(this));
	  main_core.Event.bind(menuBtn, 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMenuClick$2)[_addMenuClick$2].bind(this));
	  return node;
	}
	function _renderSettingsBtn2$2() {
	  const node = main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes" 
				data-id="spaces-calendar-settings-btn"
			>
				<div class="ui-icon-set --more" style="--ui-icon-set__icon-color: white;"></div>
			</button>
		`));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _settingsClick$2)[_settingsClick$2].bind(this));
	  return node;
	}
	function _createAddSettings2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu)[_addButtonMenu] = new CalendarAddButtonMenu({
	    bindElement: document.getElementById('spaces-calendar-toolbar-menu'),
	    calendar: babelHelpers.classPrivateFieldLooseBase(this, _calendar$1)[_calendar$1]
	  });
	}
	function _addMainClick2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _calendar$1)[_calendar$1].addEvent();
	}
	function _addMenuClick2$2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu)[_addButtonMenu]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _createAddSettings)[_createAddSettings]();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu)[_addButtonMenu].show();
	}
	function _settingsClick2$2(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _settings$5)[_settings$5]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$5)[_settings$5] = new CalendarSettings({
	      bindElement: event.target,
	      calendar: babelHelpers.classPrivateFieldLooseBase(this, _calendar$1)[_calendar$1]
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$5)[_settings$5].show();
	}

	var _menu$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _createMenu$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	class DiscussionsAddButtonMenu extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _createMenu$6, {
	      value: _createMenu2$6
	    });
	    Object.defineProperty(this, _menu$6, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsAddButtonMenu');
	    if (main_core.Type.isNil(params.calendar)) {
	      throw new TypeError('BX.Socialnetwork.Spaces.DiscussionsAddButtonMenu: calendar is not allowed');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$6)[_menu$6] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$6)[_createMenu$6](params.bindElement, params.calendar, params.isDiskStorageWasObtained);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$6)[_menu$6].show();
	  }
	}
	function _createMenu2$6(bindElement, calendar, isDiskStorageWasObtained) {
	  const fileUploadItemId = 'spaces-discussions-add-button-menu-file-item';
	  const menu = new main_popup.Menu({
	    id: 'spaces-discussions-add-button-menu',
	    bindElement,
	    closeByEsc: true,
	    events: {
	      onShow: event => {
	        if (isDiskStorageWasObtained) {
	          this.emit('showMenu', {
	            fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer()
	          });
	        }
	      },
	      onClose: () => {
	        if (isDiskStorageWasObtained) {
	          this.emit('closeMenu', {
	            fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer()
	          });
	        }
	      }
	    }
	  });
	  menu.addMenuItem({
	    text: main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_CREATE_TASK'),
	    dataset: {
	      id: 'spaces-discussions-add-button-menu-create-task'
	    },
	    onclick: () => {
	      menu.close();
	      calendar.addTask();
	    }
	  });
	  menu.addMenuItem({
	    text: main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_ORGANIZE_EVENT'),
	    dataset: {
	      id: 'spaces-discussions-add-button-menu-organize_event'
	    },
	    onclick: () => {
	      menu.close();
	      calendar.addEvent();
	    }
	  });
	  if (isDiskStorageWasObtained) {
	    menu.addMenuItem({
	      text: main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_UPLOAD_FILE'),
	      dataset: {
	        id: 'spaces-discussions-add-button-menu-file'
	      },
	      id: fileUploadItemId
	    });
	  }
	  return menu;
	}

	var _menu$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _isSmartTrackingMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSmartTrackingMode");
	var _mainFilterId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mainFilterId");
	var _switch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switch");
	var _createMenu$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	var _getStyleFromMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStyleFromMode");
	var _switchMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switchMode");
	var _switchStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switchStyle");
	var _refresh = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refresh");
	class DiscussionsSettings extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _refresh, {
	      value: _refresh2
	    });
	    Object.defineProperty(this, _switchStyle, {
	      value: _switchStyle2
	    });
	    Object.defineProperty(this, _switchMode, {
	      value: _switchMode2
	    });
	    Object.defineProperty(this, _getStyleFromMode, {
	      value: _getStyleFromMode2
	    });
	    Object.defineProperty(this, _createMenu$7, {
	      value: _createMenu2$7
	    });
	    Object.defineProperty(this, _switch, {
	      value: _switch2
	    });
	    Object.defineProperty(this, _menu$7, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isSmartTrackingMode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _mainFilterId, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsSettings');
	    babelHelpers.classPrivateFieldLooseBase(this, _mainFilterId)[_mainFilterId] = params.mainFilterId;
	    babelHelpers.classPrivateFieldLooseBase(this, _isSmartTrackingMode)[_isSmartTrackingMode] = String(params.isSmartTrackingMode) === 'Y';
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$7)[_menu$7] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$7)[_createMenu$7](params.bindElement);
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$7)[_menu$7].show();
	  }
	}
	function _switch2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _switchMode)[_switchMode]().then(result => {
	    babelHelpers.classPrivateFieldLooseBase(this, _switchStyle)[_switchStyle](result.data.mode);
	    babelHelpers.classPrivateFieldLooseBase(this, _refresh)[_refresh]();
	  });
	}
	function _createMenu2$7(bindElement) {
	  const menu = new main_popup.Menu({
	    id: 'spaces-discussions-settings',
	    bindElement,
	    closeByEsc: true
	  });
	  menu.addMenuItem({
	    id: DiscussionsSettings.SWITCHER,
	    text: main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_SETTINGS_SMART_TRACKING'),
	    className: babelHelpers.classPrivateFieldLooseBase(this, _isSmartTrackingMode)[_isSmartTrackingMode] ? DiscussionsSettings.DESELECTED : DiscussionsSettings.SELECTED,
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _switch)[_switch].bind(this)
	  });
	  menu.getMenuItem();
	  return menu;
	}
	function _getStyleFromMode2(mode) {
	  if (mode === 'Y') {
	    return DiscussionsSettings.DESELECTED;
	  }
	  return DiscussionsSettings.SELECTED;
	}
	function _switchMode2() {
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.spaces.switcher.track', {
	    data: {
	      switcher: {
	        type: DiscussionsSettings.SWITCHER,
	        spaceId: 0
	      },
	      space: 0
	    }
	  });
	}
	function _switchStyle2(mode) {
	  const item = babelHelpers.classPrivateFieldLooseBase(this, _menu$7)[_menu$7].getMenuItem(DiscussionsSettings.SWITCHER);
	  main_core.Dom.removeClass(item.layout.item, DiscussionsSettings.SELECTED);
	  main_core.Dom.removeClass(item.layout.item, DiscussionsSettings.DESELECTED);
	  main_core.Dom.addClass(item.layout.item, babelHelpers.classPrivateFieldLooseBase(this, _getStyleFromMode)[_getStyleFromMode](mode));
	}
	function _refresh2() {
	  const filter = BX.Main.filterManager.getById(babelHelpers.classPrivateFieldLooseBase(this, _mainFilterId)[_mainFilterId]);
	  if (filter instanceof BX.Main.Filter) {
	    filter.getApi().apply();
	  }
	}
	DiscussionsSettings.SWITCHER = 'smart_tracking';
	DiscussionsSettings.SELECTED = 'menu-popup-item-accept';
	DiscussionsSettings.DESELECTED = 'menu-popup-item-none';

	let _$6 = t => t,
	  _t$6,
	  _t2$4,
	  _t3$3,
	  _t4$1;
	var _params$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _menu$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");
	var _fields$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fields");
	var _appliedFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appliedFields");
	var _switchers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switchers");
	var _spaceName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("spaceName");
	var _spaceId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("spaceId");
	var _userId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userId");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _createMenu$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");
	var _renderMenuContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMenuContent");
	var _renderHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHeader");
	var _renderFilters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderFilters");
	var _applyComposition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("applyComposition");
	var _setCompositionSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCompositionSettings");
	var _refresh$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refresh");
	class DiscussionsComposition extends main_core_events.EventEmitter {
	  constructor(_params2) {
	    super();
	    Object.defineProperty(this, _refresh$1, {
	      value: _refresh2$1
	    });
	    Object.defineProperty(this, _setCompositionSettings, {
	      value: _setCompositionSettings2
	    });
	    Object.defineProperty(this, _applyComposition, {
	      value: _applyComposition2
	    });
	    Object.defineProperty(this, _renderFilters, {
	      value: _renderFilters2
	    });
	    Object.defineProperty(this, _renderHeader, {
	      value: _renderHeader2
	    });
	    Object.defineProperty(this, _renderMenuContent, {
	      value: _renderMenuContent2
	    });
	    Object.defineProperty(this, _createMenu$8, {
	      value: _createMenu2$8
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _params$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _menu$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fields$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _appliedFields, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _switchers, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _spaceName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _spaceId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userId$2, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsComposition');
	    babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5] = _params2;
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu$8)[_menu$8].show();
	  }
	}
	function _init2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _spaceId)[_spaceId] = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].spaceId;
	  babelHelpers.classPrivateFieldLooseBase(this, _userId$2)[_userId$2] = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].userId;
	  babelHelpers.classPrivateFieldLooseBase(this, _spaceName)[_spaceName] = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].spaceName;
	  babelHelpers.classPrivateFieldLooseBase(this, _fields$1)[_fields$1] = babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].compositionFilters;
	  babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields] = JSON.parse(babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].appliedFields);
	  babelHelpers.classPrivateFieldLooseBase(this, _switchers)[_switchers] = {};
	  babelHelpers.classPrivateFieldLooseBase(this, _menu$8)[_menu$8] = babelHelpers.classPrivateFieldLooseBase(this, _createMenu$8)[_createMenu$8](babelHelpers.classPrivateFieldLooseBase(this, _params$5)[_params$5].bindElement);
	}
	function _createMenu2$8(bindElement) {
	  return new ui_popupcomponentsmaker.PopupComponentsMaker({
	    id: 'spaces-discussions-composition',
	    target: bindElement,
	    padding: 0,
	    contentPadding: 0,
	    offsetTop: 5,
	    width: 300,
	    useAngle: false,
	    content: [{
	      html: [{
	        html: babelHelpers.classPrivateFieldLooseBase(this, _renderMenuContent)[_renderMenuContent]()
	      }]
	    }]
	  });
	}
	function _renderMenuContent2() {
	  return main_core.Tag.render(_t$6 || (_t$6 = _$6`
			<div>
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderHeader)[_renderHeader](), babelHelpers.classPrivateFieldLooseBase(this, _renderFilters)[_renderFilters]());
	}
	function _renderHeader2() {
	  return main_core.Tag.render(_t2$4 || (_t2$4 = _$6`
			<div class="sn-spaces-discussions-composition-header" data-id="spaces-discussions-composition-header">
				<div class="sn-spaces-discussions-composition-header-title">
					${0}
				</div>
				<div class="sn-spaces-discussions-composition-header-icon"></div>
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_DISCUSSION_COMPOSITION_TITLE').replace('%SPACE_NAME%', `<span>${main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _spaceName)[_spaceName])}</span>`));
	}
	function _renderFilters2() {
	  const filtersContainer = main_core.Tag.render(_t3$3 || (_t3$3 = _$6`
			<div data-id="spaces-discussions-composition-filters" class="sn-spaces-discussions-composition-filters"></div>
		`));
	  babelHelpers.classPrivateFieldLooseBase(this, _fields$1)[_fields$1].forEach(field => {
	    const messageId = `SN_SPACES_DISCUSSIONS_COMPOSITION_FILTER_${field.toUpperCase()}`;
	    const {
	      container,
	      switchButton
	    } = main_core.Tag.render(_t4$1 || (_t4$1 = _$6`
				<div
					id="spaces-discussions-composition-filter-${0}" 
					ref="container"
					class="sn-spaces-discussions-composition-filters-item"
				>
					<div ref="switchButton" class="sn-spaces-discussions-composition-item-switcher"></div>
					<div class="sn-spaces__popup-settings_name">
						${0}
					</div>
				</div>
			`), field, main_core.Loc.getMessage(messageId));
	    babelHelpers.classPrivateFieldLooseBase(this, _switchers)[_switchers][field] = new ui_switcher.Switcher({
	      node: switchButton,
	      checked: babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields].includes(field),
	      color: 'green',
	      size: 'small',
	      handlers: {
	        toggled: () => {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _switchers)[_switchers][field].isChecked()) {
	            babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields].push(field);
	          } else {
	            babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields].splice(babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields].indexOf(field), 1);
	          }
	          babelHelpers.classPrivateFieldLooseBase(this, _applyComposition)[_applyComposition]();
	        }
	      }
	    });
	    filtersContainer.append(container);
	  });
	  return filtersContainer;
	}
	function _applyComposition2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _setCompositionSettings)[_setCompositionSettings]().then(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _refresh$1)[_refresh$1]();
	  });
	}
	function _setCompositionSettings2() {
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.spaces.composition.setSettings', {
	    data: {
	      composition: babelHelpers.classPrivateFieldLooseBase(this, _spaceId)[_spaceId],
	      settings: babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields]
	    }
	  });
	}
	function _refresh2$1() {
	  const params = {
	    composition: babelHelpers.classPrivateFieldLooseBase(this, _appliedFields)[_appliedFields],
	    context: 'spaces',
	    useBXMainFilter: 'Y',
	    spaceId: babelHelpers.classPrivateFieldLooseBase(this, _spaceId)[_spaceId],
	    spaceUserId: babelHelpers.classPrivateFieldLooseBase(this, _userId$2)[_userId$2]
	  };
	  BX.Livefeed.PageInstance.refresh(params, null);
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$5,
	  _t3$4;
	const NotificationCenter = main_core.Reflection.namespace('BX.UI.Notification.Center');
	var _cache$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _postForm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("postForm");
	var _addButtonMenu$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addButtonMenu");
	var _composition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("composition");
	var _settings$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _filter$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filter");
	var _setParams$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _renderAddBtn$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAddBtn");
	var _renderCompositionBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCompositionBtn");
	var _renderSettingsBtn$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsBtn");
	var _addMainClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMainClick");
	var _createAddButtonMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAddButtonMenu");
	var _appendUploadInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendUploadInput");
	var _hideUploadInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideUploadInput");
	var _showSuccessUploadNotify = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSuccessUploadNotify");
	var _addMenuClick$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addMenuClick");
	var _settingsClick$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsClick");
	class DiscussionsToolbar {
	  constructor(_params) {
	    Object.defineProperty(this, _settingsClick$3, {
	      value: _settingsClick2$3
	    });
	    Object.defineProperty(this, _addMenuClick$3, {
	      value: _addMenuClick2$3
	    });
	    Object.defineProperty(this, _showSuccessUploadNotify, {
	      value: _showSuccessUploadNotify2
	    });
	    Object.defineProperty(this, _hideUploadInput, {
	      value: _hideUploadInput2
	    });
	    Object.defineProperty(this, _appendUploadInput, {
	      value: _appendUploadInput2
	    });
	    Object.defineProperty(this, _createAddButtonMenu, {
	      value: _createAddButtonMenu2
	    });
	    Object.defineProperty(this, _addMainClick$1, {
	      value: _addMainClick2$1
	    });
	    Object.defineProperty(this, _renderSettingsBtn$3, {
	      value: _renderSettingsBtn2$3
	    });
	    Object.defineProperty(this, _renderCompositionBtn, {
	      value: _renderCompositionBtn2
	    });
	    Object.defineProperty(this, _renderAddBtn$3, {
	      value: _renderAddBtn2$3
	    });
	    Object.defineProperty(this, _getParam$4, {
	      value: _getParam2$4
	    });
	    Object.defineProperty(this, _setParams$4, {
	      value: _setParams2$4
	    });
	    Object.defineProperty(this, _cache$4, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _postForm, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _addButtonMenu$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _composition, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings$6, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _filter$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams$4)[_setParams$4](_params);
	    babelHelpers.classPrivateFieldLooseBase(this, _filter$5)[_filter$5] = new Filter({
	      filterId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('filterId'),
	      filterContainer: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('filterContainer')
	    });
	  }
	  renderAddBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAddBtn$3)[_renderAddBtn$3](), container);
	  }
	  renderCompositionBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for add composition btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderCompositionBtn)[_renderCompositionBtn](), container);
	  }
	  renderSettingsBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.DiscussionsToolbar: HTMLElement for settings btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsBtn$3)[_renderSettingsBtn$3](), container);
	  }
	}
	function _setParams2$4(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache$4)[_cache$4].set('params', params);
	}
	function _getParam2$4(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache$4)[_cache$4].get('params')[param];
	}
	function _renderAddBtn2$3() {
	  const {
	    node,
	    mainBtn,
	    menuBtn
	  } = main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps" ref="node">
				<button class="ui-btn-main" data-id="spaces-discussions-add-main-btn" ref="mainBtn">
					${0}
				</button>
				<button 
					class="ui-btn-menu"  
					id="spaces-discussions-toolbar-menu" 
					data-id="spaces-discussions-add-menu-btn"
					ref="menuBtn"
				>	
				</button>
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_START_DISCUSSIONS'));
	  main_core.Event.bind(mainBtn, 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMainClick$1)[_addMainClick$1].bind(this));
	  main_core.Event.bind(menuBtn, 'click', babelHelpers.classPrivateFieldLooseBase(this, _addMenuClick$3)[_addMenuClick$3].bind(this));
	  return node;
	}
	function _renderCompositionBtn2() {
	  const node = main_core.Tag.render(_t2$5 || (_t2$5 = _$7`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes sn-spaces__toolbar-space_btn-options"
			>
				<div class="ui-icon-set --customer-cards" style="--ui-icon-set__icon-size: 25px;"></div>
				<div class="sn-spaces__toolbar-space_btn-text">
					${0}
				</div>
				<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 19px;"></div>
			</button>
		`), main_core.Loc.getMessage('SN_SPACES_DISCUSSIONS_COMPOSITION'));
	  babelHelpers.classPrivateFieldLooseBase(this, _composition)[_composition] = new DiscussionsComposition({
	    userId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('userId'),
	    spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('spaceId'),
	    bindElement: node,
	    spaceName: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('spaceName'),
	    compositionFilters: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('compositionFilters'),
	    mainFilterId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('mainFilterId'),
	    appliedFields: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('appliedFields')
	  });
	  main_core.Event.bind(node, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _composition)[_composition].show());
	  return node;
	}
	function _renderSettingsBtn2$3() {
	  const node = main_core.Tag.render(_t3$4 || (_t3$4 = _$7`
			<button 
				class="ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes sn-spaces__toolbar-space_btn-more" 
				data-id="spaces-discussions-settings-btn"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _settingsClick$3)[_settingsClick$3].bind(this));
	  return node;
	}
	function _addMainClick2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _postForm)[_postForm]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _postForm)[_postForm] = new socialnetwork_postForm.PostForm({
	      groupId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('type') === 'user' ? 0 : babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('ownerId'),
	      pathToDefaultRedirect: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('pathToUserPage'),
	      pathToGroupRedirect: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('pathToGroupPage')
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _postForm)[_postForm].show();
	}
	function _createAddButtonMenu2() {
	  var _ref;
	  const calendar = new Calendar({
	    type: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('type'),
	    // eslint-disable-next-line no-constant-binary-expression
	    locationAccess: (_ref = babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('locationAccess') === '1') != null ? _ref : false,
	    userId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('userId'),
	    ownerId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('ownerId')
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu$1)[_addButtonMenu$1] = new DiscussionsAddButtonMenu({
	    bindElement: document.getElementById('spaces-discussions-toolbar-menu'),
	    calendar,
	    isDiskStorageWasObtained: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('isDiskStorageWasObtained') === 'Y'
	  });

	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	  BX.addCustomEvent(window, 'onPopupFileUploadClose', () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _showSuccessUploadNotify)[_showSuccessUploadNotify]();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu$1)[_addButtonMenu$1].subscribe('showMenu', baseEvent => {
	    const {
	      fileUploadContainer
	    } = baseEvent.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _appendUploadInput)[_appendUploadInput](fileUploadContainer);
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu$1)[_addButtonMenu$1].subscribe('closeMenu', baseEvent => {
	    const {
	      fileUploadContainer
	    } = baseEvent.getData();
	    babelHelpers.classPrivateFieldLooseBase(this, _hideUploadInput)[_hideUploadInput](fileUploadContainer);
	  });
	}
	function _appendUploadInput2(container) {
	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	  BX.onCustomEvent(window, 'onDiskUploadPopupShow', [container]);
	}
	function _hideUploadInput2(container) {
	  // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	  BX.onCustomEvent(window, 'onDiskUploadPopupClose', [container]);
	}
	function _showSuccessUploadNotify2() {
	  const content = main_core.Loc.getMessage('SN_SPACES_LINE_UPLOAD_FILE_NOTIFY_MESSAGE').replace('#handler#', `top.BX.Socialnetwork.Spaces.space.reloadPageContent('${babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('pathToFilesPage')}');`);
	  NotificationCenter.notify({
	    content
	  });
	}
	function _addMenuClick2$3() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu$1)[_addButtonMenu$1]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _createAddButtonMenu)[_createAddButtonMenu]();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _addButtonMenu$1)[_addButtonMenu$1].show();
	}
	function _settingsClick2$3(event) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _settings$6)[_settings$6]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _settings$6)[_settings$6] = new DiscussionsSettings({
	      bindElement: event.target,
	      isSmartTrackingMode: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('isSmartTrackingMode'),
	      mainFilterId: babelHelpers.classPrivateFieldLooseBase(this, _getParam$4)[_getParam$4]('mainFilterId')
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _settings$6)[_settings$6].show();
	}

	var _sidePanelManager$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sidePanelManager");
	var _pathToInvite = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToInvite");
	class UsersRouter {
	  constructor(params) {
	    Object.defineProperty(this, _sidePanelManager$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToInvite, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager$2)[_sidePanelManager$2] = BX.SidePanel.Instance;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToInvite)[_pathToInvite] = params.pathToInvite;
	  }
	  openInvite() {
	    babelHelpers.classPrivateFieldLooseBase(this, _sidePanelManager$2)[_sidePanelManager$2].open(babelHelpers.classPrivateFieldLooseBase(this, _pathToInvite)[_pathToInvite], {
	      width: 950,
	      loader: 'group-invite-loader'
	    });
	  }
	}

	let _$8 = t => t,
	  _t$8;
	var _cache$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _router$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("router");
	var _setParams$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setParams");
	var _getParam$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParam");
	var _initServices = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initServices");
	var _renderInviteBtn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderInviteBtn");
	var _inviteClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inviteClick");
	class UsersToolbar {
	  constructor(_params) {
	    Object.defineProperty(this, _inviteClick, {
	      value: _inviteClick2
	    });
	    Object.defineProperty(this, _renderInviteBtn, {
	      value: _renderInviteBtn2
	    });
	    Object.defineProperty(this, _initServices, {
	      value: _initServices2
	    });
	    Object.defineProperty(this, _getParam$5, {
	      value: _getParam2$5
	    });
	    Object.defineProperty(this, _setParams$5, {
	      value: _setParams2$5
	    });
	    Object.defineProperty(this, _cache$5, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    Object.defineProperty(this, _router$3, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _setParams$5)[_setParams$5](_params);
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices)[_initServices](_params);
	  }
	  renderInviteBtnTo(container) {
	    if (!main_core.Type.isDomNode(container)) {
	      throw new Error('BX.Socialnetwork.Spaces.UsersToolbar: HTMLElement for add btn not found');
	    }
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderInviteBtn)[_renderInviteBtn](), container);
	  }
	}
	function _setParams2$5(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache$5)[_cache$5].set('params', params);
	}
	function _getParam2$5(param) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache$5)[_cache$5].get('params')[param];
	}
	function _initServices2(params) {
	  babelHelpers.classPrivateFieldLooseBase(this, _router$3)[_router$3] = new UsersRouter({
	    pathToInvite: params.pathToInvite
	  });
	}
	function _renderInviteBtn2() {
	  const node = main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div
				data-id="spaces-users-invite-btn"
				class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps"
			>
				${0}
			</div>
		`), main_core.Loc.getMessage('SN_SPACES_USERS_INVITE_BTN'));
	  main_core.Event.bind(node, 'click', babelHelpers.classPrivateFieldLooseBase(this, _inviteClick)[_inviteClick].bind(this));
	  return node;
	}
	function _inviteClick2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _router$3)[_router$3].openInvite();
	}

	exports.TasksToolbar = TasksToolbar;
	exports.FilesToolbar = FilesToolbar;
	exports.CalendarToolbar = CalendarToolbar;
	exports.DiscussionsToolbar = DiscussionsToolbar;
	exports.UsersToolbar = UsersToolbar;

}((this.BX.Socialnetwork.Spaces = this.BX.Socialnetwork.Spaces || {}),BX.UI.ShortView,BX,BX.Tasks,BX.UI,BX.UI.EntitySelector,BX.Tasks,BX.Calendar,BX.Socialnetwork,BX.Main,BX.UI,BX.UI,BX.Event,BX));
//# sourceMappingURL=script.js.map
