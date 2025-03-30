/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,ui_alerts,ui_entitySelector,main_popup,bizproc_types,bizproc_task,ui_hint,bizproc_workflow_faces,bizproc_workflow_faces_summary,ui_cnt,main_core,ui_notification) {
	'use strict';

	var _runComponentAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("runComponentAction");
	class WorkflowLoader {
	  constructor() {
	    Object.defineProperty(this, _runComponentAction, {
	      value: _runComponentAction2
	    });
	  }
	  loadWorkflows(ids) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _runComponentAction)[_runComponentAction]('loadWorkflows', {
	      ids
	    });
	  }
	}
	function _runComponentAction2(action, data = {}) {
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runComponentAction('bitrix:bizproc.user.processes', action, {
	      mode: 'class',
	      data
	    }).catch(response => {
	      reject(response);
	    }).then(response => {
	      resolve(response);
	    });
	  });
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _currentUserId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentUserId");
	var _targetUserId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetUserId");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _task = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("task");
	var _inlineTaskView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inlineTaskView");
	var _faces = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("faces");
	var _getWorkflowInfoUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWorkflowInfoUrl");
	class WorkflowRenderer {
	  constructor(data) {
	    Object.defineProperty(this, _getWorkflowInfoUrl, {
	      value: _getWorkflowInfoUrl2
	    });
	    Object.defineProperty(this, _currentUserId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _targetUserId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _task, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inlineTaskView, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _faces, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = data.workflow;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId] = main_core.Type.isNumber(data.currentUserId) ? data.currentUserId : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId] = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].userId;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task) {
	      babelHelpers.classPrivateFieldLooseBase(this, _task)[_task] = new bizproc_task.Task(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task);
	      if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].controls.buttons)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].setButtons(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].buttons.map(button => ({
	          onclick: () => data.userProcesses.doTask({
	            taskId: babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].id,
	            workflowId: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId,
	            taskName: babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].name,
	            taskRequest: {
	              [button.NAME]: button.VALUE
	            }
	          }),
	          ...button
	        })));
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _inlineTaskView)[_inlineTaskView] = new bizproc_task.InlineTaskView({
	        task: babelHelpers.classPrivateFieldLooseBase(this, _task)[_task],
	        responsibleUser: babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId]
	      });
	    }
	  }
	  renderProcess() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4;
	    const itemName = main_core.Type.isString((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]) == null ? void 0 : _babelHelpers$classPr.name) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].name : '';
	    const typeName = main_core.Type.isString((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]) == null ? void 0 : _babelHelpers$classPr2.typeName) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].typeName : '';
	    const documentUrl = ((_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task) == null ? void 0 : _babelHelpers$classPr3.url) || babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowUrl || babelHelpers.classPrivateFieldLooseBase(this, _getWorkflowInfoUrl)[_getWorkflowInfoUrl]();
	    const description = main_core.Type.isString((_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]) == null ? void 0 : _babelHelpers$classPr4.description) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].description : '';
	    const lengthLimit = 80;
	    const collapsedDescription = main_core.Dom.create('span', {
	      html: description == null ? void 0 : description.replace(/(<br \/>)+/gm, ' ')
	    }).textContent.replace(/\n+/, ' ').slice(0, lengthLimit);
	    const collapsed = (description == null ? void 0 : description.length) > lengthLimit;
	    const descriptionNode = main_core.Tag.render(_t || (_t = _`
			<span class="bp-user-processes__description">
				${0}
			</span>
		`), description);
	    BX.UI.Hint.init(descriptionNode);
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="bp-user-processes">
					<a class="bp-user-processes__title-link ui-typography-text-lg"
						href="${0}">${0}
					</a>
					<div class="bp-user-processes__appointment">${0}</div>
					<div class="bp-user-processes__description-box ${0}">
						<span class="bp-user-processes__short_description">
							${0}
							...<a href="#" onclick="this.closest('div').classList.add('--expanded'); return false;" class="bp-user-processes__description-link">${0}</a>
						</span>
						${0}
					</div>
			</div>
		`), main_core.Text.encode(documentUrl), main_core.Text.encode(itemName), main_core.Text.encode(typeName.toUpperCase()), collapsed ? '' : '--expanded', main_core.Text.encode(collapsedDescription), main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_DESCRIPTION_MORE'), descriptionNode);
	  }
	  renderTaskName() {
	    var _babelHelpers$classPr5;
	    return (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _inlineTaskView)[_inlineTaskView]) == null ? void 0 : _babelHelpers$classPr5.render();
	  }

	  // eslint-disable-next-line sonarjs/cognitive-complexity
	  renderTask() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task || babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].userId !== babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId]) {
	      const completedClassName = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].isCompleted ? '--success' : '';
	      let result = '';
	      let noRightsClass = '';
	      if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].isCompleted && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult !== null) {
	        if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.status === bizproc_types.WorkflowResultStatus.BB_CODE_RESULT) {
	          var _babelHelpers$classPr6;
	          result = `${main_core.Loc.getMessage('BIZPROC_RENDERED_RESULT_VALUE')}<br>${(_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.text) != null ? _babelHelpers$classPr6 : ''}`;
	        }
	        if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.status === bizproc_types.WorkflowResultStatus.USER_RESULT) {
	          var _babelHelpers$classPr7;
	          result = main_core.Loc.getMessage('BIZPROC_RENDERED_RESULT_POSITIVE_RESULT_FOR', {
	            '#USER#': (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.text) != null ? _babelHelpers$classPr7 : ''
	          });
	        }
	        if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.status === bizproc_types.WorkflowResultStatus.NO_RIGHTS_RESULT) {
	          noRightsClass = 'no-rights';
	          result = `${main_core.Loc.getMessage('BIZPROC_RENDERED_RESULT_NO_RIGHTS_VIEW')} <span data-hint="${main_core.Loc.getMessage('BIZPROC_RENDERED_RESULT_NO_RIGHTS_TOOLTIP')}"></span>`;
	        }
	      }
	      const panel = main_core.Tag.render(_t3 || (_t3 = _`
				<div class="bp-status-panel ${0}">
						<div class="bp-status-item">
							<div class="bp-status-name">${0}</div>
							<div class="bp-workflow-result ${0}">${0}</div>
						</div>
				</div>
			`), completedClassName, main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].statusText.toUpperCase()), noRightsClass, result);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult !== null && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowResult.status === bizproc_types.WorkflowResultStatus.NO_RIGHTS_RESULT) {
	        BX.UI.Hint.init(panel);
	      }
	      return panel;
	    }
	    return this.renderTaskName();
	  }
	  renderDocumentName() {
	    var _babelHelpers$classPr8, _babelHelpers$classPr9, _babelHelpers$classPr10, _babelHelpers$classPr11;
	    const documentName = main_core.Type.isString((_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]) == null ? void 0 : (_babelHelpers$classPr9 = _babelHelpers$classPr8.document) == null ? void 0 : _babelHelpers$classPr9.name) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].document.name : '';
	    if (main_core.Type.isString((_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data]) == null ? void 0 : (_babelHelpers$classPr11 = _babelHelpers$classPr10.document) == null ? void 0 : _babelHelpers$classPr11.url)) {
	      const url = new main_core.Uri(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].document.url);
	      return main_core.Tag.render(_t4 || (_t4 = _`
				<a href="${0}">
					${0}
				</a>
			`), main_core.Text.encode(url.toString()), main_core.Text.encode(documentName));
	    }
	    return main_core.Text.encode(documentName);
	  }
	  renderWorkflowFaces() {
	    const target = main_core.Tag.render(_t5 || (_t5 = _`<div></div>`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskProgress) {
	      try {
	        babelHelpers.classPrivateFieldLooseBase(this, _faces)[_faces] = new bizproc_workflow_faces.WorkflowFaces({
	          workflowId: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId,
	          targetUserId: babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId],
	          target,
	          data: {
	            steps: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskProgress.steps,
	            progressBox: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskProgress.progressBox
	          },
	          showArrow: true
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _faces)[_faces].render();
	      } catch (e) {
	        console.error(e);
	      }
	    }
	    return target;
	  }
	  renderSummary() {
	    var _babelHelpers$classPr12;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId || !((_babelHelpers$classPr12 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskProgress) != null && _babelHelpers$classPr12.timeStep)) {
	      return null;
	    }
	    return new bizproc_workflow_faces_summary.Summary({
	      workflowId: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId,
	      data: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskProgress.timeStep
	    }).render();
	  }
	  renderModified() {
	    var _counter;
	    let counter = null;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].userId === babelHelpers.classPrivateFieldLooseBase(this, _currentUserId)[_currentUserId] && (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskCnt > 0 || babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].commentCnt > 0)) {
	      const primaryColor = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskCnt === 0 && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].commentCnt > 0 ? ui_cnt.CounterColor.SUCCESS : ui_cnt.CounterColor.DANGER;
	      counter = new ui_cnt.Counter({
	        value: (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskCnt || 0) + (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].commentCnt || 0),
	        color: primaryColor,
	        secondaryColor: ui_cnt.CounterColor.SUCCESS,
	        isDouble: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].taskCnt > 0 && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].commentCnt > 0
	      });
	    }
	    return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="bp-modified-cell">
				<span class="bp-row-counters">${0}</span>
				<span>${0}</span>
			</div>
		`), (_counter = counter) == null ? void 0 : _counter.getContainer(), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].modified));
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _task)[_task] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _inlineTaskView)[_inlineTaskView] = null;
	    if (!main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _faces)[_faces])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _faces)[_faces].destroy();
	      babelHelpers.classPrivateFieldLooseBase(this, _faces)[_faces] = null;
	    }
	  }
	}
	function _getWorkflowInfoUrl2() {
	  var _babelHelpers$classPr13;
	  const idParam = main_core.Type.isNil((_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task) == null ? void 0 : _babelHelpers$classPr13.id) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowId : babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].task.id;
	  const uri = new main_core.Uri(`/company/personal/bizproc/${idParam}/`);
	  return uri.toString();
	}

	var _subscribeToPulls = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPulls");
	var _getTaskColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTaskColor");
	var _getCommentColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCommentColor");
	var _setFilterPreset = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setFilterPreset");
	var _getFilterManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFilterManager");
	class CounterPanel {
	  constructor(options) {
	    Object.defineProperty(this, _getFilterManager, {
	      value: _getFilterManager2
	    });
	    Object.defineProperty(this, _setFilterPreset, {
	      value: _setFilterPreset2
	    });
	    Object.defineProperty(this, _getCommentColor, {
	      value: _getCommentColor2
	    });
	    Object.defineProperty(this, _getTaskColor, {
	      value: _getTaskColor2
	    });
	    Object.defineProperty(this, _subscribeToPulls, {
	      value: _subscribeToPulls2
	    });
	    this.filterId = options.filterId;
	    this.counters = options.counters;
	  }
	  renderTo(target) {
	    var _this$counters, _this$counters2, _this$counters3, _this$counters4;
	    this.uiPanel = new BX.UI.CounterPanel({
	      target,
	      multiselect: false,
	      title: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_LABEL'),
	      items: [{
	        id: 'task',
	        value: {
	          value: ((_this$counters = this.counters) == null ? void 0 : _this$counters.task) || 0,
	          order: 1
	        },
	        title: {
	          value: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_TASK'),
	          order: 2
	        },
	        color: babelHelpers.classPrivateFieldLooseBase(this, _getTaskColor)[_getTaskColor]((_this$counters2 = this.counters) == null ? void 0 : _this$counters2.task),
	        separator: false,
	        eventsForActive: {
	          click: babelHelpers.classPrivateFieldLooseBase(this, _setFilterPreset)[_setFilterPreset].bind(this)
	        },
	        eventsForUnActive: {
	          click: babelHelpers.classPrivateFieldLooseBase(this, _setFilterPreset)[_setFilterPreset].bind(this, 'active_task')
	        }
	      }, {
	        id: 'comment',
	        value: ((_this$counters3 = this.counters) == null ? void 0 : _this$counters3.comment) || 0,
	        title: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_COMMENT'),
	        color: babelHelpers.classPrivateFieldLooseBase(this, _getCommentColor)[_getCommentColor]((_this$counters4 = this.counters) == null ? void 0 : _this$counters4.comment),
	        eventsForActive: {
	          click: babelHelpers.classPrivateFieldLooseBase(this, _setFilterPreset)[_setFilterPreset].bind(this)
	        },
	        eventsForUnActive: {
	          click: babelHelpers.classPrivateFieldLooseBase(this, _setFilterPreset)[_setFilterPreset].bind(this, 'comment')
	        }
	      }]
	    });
	    this.uiPanel.init();
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPulls)[_subscribeToPulls]();
	  }
	}
	function _subscribeToPulls2() {
	  BX.PULL.subscribe({
	    moduleId: 'main',
	    command: 'user_counter',
	    callback: params => {
	      var _params$BX$message$bp, _params$BX$message;
	      const taskCounterValue = (_params$BX$message$bp = (_params$BX$message = params[BX.message('SITE_ID')]) == null ? void 0 : _params$BX$message.bp_tasks) != null ? _params$BX$message$bp : null;
	      if (taskCounterValue !== null) {
	        this.uiPanel.getItemById('task').updateValue(taskCounterValue);
	        this.uiPanel.getItemById('task').updateColor(babelHelpers.classPrivateFieldLooseBase(this, _getTaskColor)[_getTaskColor](taskCounterValue));
	      }
	    }
	  });
	  BX.PULL.subscribe({
	    moduleId: 'bizproc',
	    command: 'comment',
	    callback: params => {
	      var _params$counter$allUn, _params$counter;
	      const allUnreadValue = (_params$counter$allUn = (_params$counter = params.counter) == null ? void 0 : _params$counter.allUnread) != null ? _params$counter$allUn : null;
	      if (allUnreadValue !== null) {
	        this.uiPanel.getItemById('comment').updateValue(allUnreadValue);
	        this.uiPanel.getItemById('comment').updateColor(babelHelpers.classPrivateFieldLooseBase(this, _getCommentColor)[_getCommentColor](allUnreadValue));
	      }
	    }
	  });
	}
	function _getTaskColor2(value) {
	  return value > 0 ? 'DANGER' : 'THEME';
	}
	function _getCommentColor2(value) {
	  return value > 0 ? 'SUCCESS' : 'THEME';
	}
	function _setFilterPreset2(presetId) {
	  const filterManager = babelHelpers.classPrivateFieldLooseBase(this, _getFilterManager)[_getFilterManager]();
	  if (!filterManager) {
	    return;
	  }
	  const api = filterManager.getApi();
	  const fields = {
	    SYSTEM_PRESET: presetId != null ? presetId : 'in_work'
	  };
	  api.setFields(fields);
	  api.apply();
	}
	function _getFilterManager2() {
	  if (this.filterId) {
	    var _BX$Main$filterManage;
	    return (_BX$Main$filterManage = BX.Main.filterManager) == null ? void 0 : _BX$Main$filterManage.getById(this.filterId);
	  }

	  // eslint-disable-next-line no-console
	  console.warn('Filter not found');
	  return null;
	}

	let _$1 = t => t,
	  _t$1;
	var _workflowTasks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowTasks");
	var _workflowRenderer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowRenderer");
	var _subscribeToPushes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPushes");
	var _updateWorkflows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateWorkflows");
	var _appendWorkflow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendWorkflow");
	var _createWorkflowRenderer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWorkflowRenderer");
	var _getWorkflowRendererById = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWorkflowRendererById");
	var _deleteWorkflowRendererById = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteWorkflowRendererById");
	var _hideRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideRow");
	var _showRow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showRow");
	class UserProcesses {
	  constructor(options) {
	    Object.defineProperty(this, _showRow, {
	      value: _showRow2
	    });
	    Object.defineProperty(this, _hideRow, {
	      value: _hideRow2
	    });
	    Object.defineProperty(this, _deleteWorkflowRendererById, {
	      value: _deleteWorkflowRendererById2
	    });
	    Object.defineProperty(this, _getWorkflowRendererById, {
	      value: _getWorkflowRendererById2
	    });
	    Object.defineProperty(this, _createWorkflowRenderer, {
	      value: _createWorkflowRenderer2
	    });
	    Object.defineProperty(this, _appendWorkflow, {
	      value: _appendWorkflow2
	    });
	    Object.defineProperty(this, _updateWorkflows, {
	      value: _updateWorkflows2
	    });
	    Object.defineProperty(this, _subscribeToPushes, {
	      value: _subscribeToPushes2
	    });
	    this.delegateToUserId = 0;
	    Object.defineProperty(this, _workflowTasks, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _workflowRenderer, {
	      writable: true,
	      value: {}
	    });
	    let mustSubscribeToPushes = false;
	    if (main_core.Type.isPlainObject(options)) {
	      this.gridId = options.gridId;
	      if (main_core.Type.isArray(options.errors)) {
	        this.showErrors(options.errors);
	      }
	      this.actionPanel = {
	        wrapperElementId: options.actionPanelUserWrapperId,
	        actionButtonName: `${this.gridId}_action_button`
	      };
	      this.currentUserId = options.currentUserId;
	      mustSubscribeToPushes = options.mustSubscribeToPushes === true;
	    }
	    this.loader = new WorkflowLoader();
	    if (mustSubscribeToPushes) {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPushes)[_subscribeToPushes]();
	    }
	    this.init();
	    this.initCounterPanel(options.counters, options.filterId);
	  }
	  getDefaultAddRowOptions(workflow, renderer) {
	    const actions = [{
	      text: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_DOCUMENT'),
	      href: workflow.document.url || '#'
	    }];
	    if (workflow.task) {
	      actions.push({
	        text: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_TASK'),
	        href: workflow.task.url
	      });
	    }
	    return {
	      id: workflow.workflowId,
	      animation: false,
	      columns: {
	        ID: workflow.workflowId,
	        PROCESS: renderer.renderProcess(),
	        TASK_PROGRESS: renderer.renderWorkflowFaces(),
	        TASK: renderer.renderTask(),
	        WORKFLOW_STATE: main_core.Text.encode(workflow.statusText),
	        DOCUMENT_NAME: renderer.renderDocumentName(),
	        WORKFLOW_TEMPLATE_NAME: main_core.Text.encode(workflow.templateName),
	        TASK_DESCRIPTION: main_core.Dom.create('span', {
	          html: workflow.description || ''
	        }),
	        MODIFIED: renderer.renderModified(),
	        WORKFLOW_STARTED: main_core.Text.encode(workflow.workflowStarted),
	        WORKFLOW_STARTED_BY: main_core.Text.encode(workflow.startedBy),
	        OVERDUE_DATE: main_core.Text.encode(workflow.overdueDate),
	        SUMMARY: renderer.renderSummary()
	      },
	      actions,
	      columnClasses: {
	        TASK_PROGRESS: 'bp-task-progress-cell',
	        SUMMARY: 'bp-summary-cell',
	        TASK: workflow.isCompleted ? 'bp-status-completed-cell' : ''
	      },
	      editable: Boolean(workflow.task)
	    };
	  }
	  init() {
	    this.actionPanel.userWrapperElement = document.getElementById(this.actionPanel.wrapperElementId);
	    this.initUserSelector();
	    this.renderCells();
	    this.onActionPanelChanged();
	  }
	  initCounterPanel(counters, filterId) {
	    const panelWrapperNode = document.querySelector('[data-role="bizproc-counterpanel"]');
	    if (!panelWrapperNode) {
	      return;
	    }
	    new CounterPanel({
	      counters,
	      filterId
	    }).renderTo(panelWrapperNode);
	  }
	  renderCells() {
	    const updated = new Map();
	    document.querySelectorAll('[data-role="bp-render-cell"]').forEach(target => {
	      const workflow = main_core.Dom.attr(target, 'data-workflow');
	      const columnId = main_core.Dom.attr(target, 'data-column');
	      if (workflow) {
	        if (!updated.has(workflow.workflowId)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _deleteWorkflowRendererById)[_deleteWorkflowRendererById](workflow.workflowId);
	          updated.set(workflow.workflowId);
	        }
	        if (workflow.task) {
	          // set workflow task map
	          babelHelpers.classPrivateFieldLooseBase(this, _workflowTasks)[_workflowTasks].set(workflow.workflowId, workflow.task.id);
	        }
	        this.renderColumnCell(target, columnId, workflow);
	      }
	    });
	  }
	  renderColumnCell(target, columnId, workflow) {
	    var _babelHelpers$classPr;
	    const renderer = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _getWorkflowRendererById)[_getWorkflowRendererById](String(workflow.workflowId))) != null ? _babelHelpers$classPr : babelHelpers.classPrivateFieldLooseBase(this, _createWorkflowRenderer)[_createWorkflowRenderer](String(workflow.workflowId), workflow);
	    let childNode = null;
	    switch (columnId) {
	      case 'DOCUMENT_NAME':
	        childNode = renderer.renderDocumentName();
	        break;
	      case 'PROCESS':
	        childNode = renderer.renderProcess();
	        break;
	      case 'TASK_PROGRESS':
	        childNode = renderer.renderWorkflowFaces();
	        break;
	      case 'TASK':
	        childNode = renderer.renderTask();
	        break;
	      case 'SUMMARY':
	        childNode = renderer.renderSummary();
	        break;
	      case 'MODIFIED':
	        childNode = renderer.renderModified();
	        break;
	      default:
	      // do nothing
	    }

	    if (childNode) {
	      main_core.Dom.replace(target, childNode);
	    }
	  }
	  async initStartWorkflowButton(buttonId) {
	    const button = main_core.Type.isStringFilled(buttonId) && document.getElementById(buttonId);
	    const lists = main_core.Type.isStringFilled(button == null ? void 0 : button.dataset.lists) && JSON.parse(button.dataset.lists);
	    let selectedIBlockSliderParams = null;
	    if (lists) {
	      const popupMenu = new main_popup.Menu({
	        angle: true,
	        offsetLeft: main_core.Dom.getPosition(button).width / 2,
	        autoHide: true,
	        bindElement: button,
	        closeByEsc: true,
	        items: Object.values(lists).map(list => {
	          const item = {
	            text: list.name,
	            className: 'feed-add-post-form-link-lists',
	            dataset: {
	              iconUrl: list.icon
	            }
	          };
	          if (main_core.Type.isNil(list.url)) {
	            const params = {
	              iBlockTypeId: list.iBlockTypeId,
	              iBlockId: list.iBlockId,
	              analyticsP1: list.name
	            };
	            if (list.selected === true) {
	              selectedIBlockSliderParams = params;
	            }
	            item.onclick = () => {
	              popupMenu.close();
	              main_core.Runtime.loadExtension('lists.element.creation-guide').then(({
	                CreationGuide
	              }) => {
	                CreationGuide == null ? void 0 : CreationGuide.open(params);
	              }).catch(() => {});
	            };
	          } else {
	            item.href = list.url;
	          }
	          return item;
	        })
	      });
	      const popupElement = popupMenu.getMenuContainer();
	      for (const iconElement of popupElement.querySelectorAll('.menu-popup-item-icon')) {
	        main_core.Dom.append(main_core.Tag.render(_t$1 || (_t$1 = _$1`
						<img src = "${0}" alt="" width = "19" height = "16"/>
					`), iconElement.parentElement.dataset.iconUrl), iconElement);
	      }
	      button.onclick = event => {
	        event.preventDefault();
	        if (!popupMenu.getPopupWindow().isShown()) {
	          main_core.Runtime.loadExtension('ui.analytics').then(({
	            sendData
	          }) => {
	            sendData({
	              tool: 'automation',
	              category: 'bizproc_operations',
	              event: 'drawer_open',
	              c_section: 'bizproc',
	              c_element: 'button'
	            });
	          }).catch(() => {});
	        }
	        popupMenu.toggle();
	      };
	    }
	    if (selectedIBlockSliderParams) {
	      main_core.Runtime.loadExtension('lists.element.creation-guide').then(({
	        CreationGuide
	      }) => {
	        CreationGuide == null ? void 0 : CreationGuide.open(selectedIBlockSliderParams);
	      }).catch(() => {});
	    }
	  }
	  initUserSelector() {
	    if (!this.delegateToSelector) {
	      this.delegateToSelector = new ui_entitySelector.TagSelector({
	        multiple: false,
	        tagMaxWidth: 180,
	        events: {
	          onTagAdd: event => {
	            this.delegateToUserId = parseInt(event.getData().tag.getId(), 10);
	            if (!main_core.Type.isInteger(this.delegateToUserId)) {
	              this.delegateToUserId = 0;
	            }
	          },
	          onTagRemove: () => {
	            this.delegateToUserId = 0;
	          }
	        },
	        dialogOptions: {
	          entities: [{
	            id: 'user',
	            options: {
	              intranetUsersOnly: true,
	              inviteEmployeeLink: false
	            }
	          }]
	        }
	      });
	    }
	    if (main_core.Type.isDomNode(this.actionPanel.userWrapperElement)) {
	      main_core.Dom.clean(this.actionPanel.userWrapperElement);
	      this.delegateToSelector.renderTo(this.actionPanel.userWrapperElement);
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
	  onActionPanelChanged() {
	    const grid = this.getGrid();
	    const actionPanel = grid == null ? void 0 : grid.getActionsPanel();
	    if (actionPanel) {
	      const action = actionPanel.getValues()[this.actionPanel.actionButtonName];
	      if (!main_core.Type.isString(action) || action.includes('set_status')) {
	        main_core.Dom.hide(this.actionPanel.userWrapperElement);
	      } else {
	        main_core.Dom.show(this.actionPanel.userWrapperElement);
	      }
	    }
	  }
	  applyActionPanelValues() {
	    const grid = this.getGrid();
	    const actionsPanel = grid == null ? void 0 : grid.getActionsPanel();
	    if (grid && actionsPanel) {
	      var _actionsPanel$getForA;
	      const isApplyingForAll = ((_actionsPanel$getForA = actionsPanel.getForAllCheckbox()) == null ? void 0 : _actionsPanel$getForA.checked) === true;
	      // TODO - implement doing all tasks
	      if (isApplyingForAll) {
	        this.showErrors([{
	          message: 'Not implemented currently'
	        }]);
	      }
	      const action = actionsPanel.getValues()[this.actionPanel.actionButtonName];
	      if (main_core.Type.isString(action)) {
	        const selectedTasks = this.getSelectedTaskIds(grid.getRows().getSelectedIds());
	        if (selectedTasks.length === 0) {
	          // todo: show error?

	          return;
	        }
	        if (action.includes('set_status_')) {
	          const status = parseInt(action.split('_').pop(), 10);
	          if (main_core.Type.isNumber(status)) {
	            this.setTasksStatuses(selectedTasks, status);
	          }
	        } else if (action.startsWith('delegate_to')) {
	          this.delegateTasks(selectedTasks, this.delegateToUserId);
	        }
	      }
	    }
	  }
	  getSelectedTaskIds(selectedWorkflowIds) {
	    return selectedWorkflowIds.map(workflowId => babelHelpers.classPrivateFieldLooseBase(this, _workflowTasks)[_workflowTasks].get(workflowId)).filter(taskId => main_core.Type.isNumber(taskId));
	  }
	  setTasksStatuses(taskIds, newStatus) {
	    // eslint-disable-next-line promise/catch-or-return
	    main_core.ajax.runAction('bizproc.task.doInlineTasks', {
	      data: {
	        taskIds,
	        newStatus
	      }
	    }).catch(response => {
	      this.showErrors(response.errors);
	      this.reloadGrid();
	    })
	    // .then(() => this.reloadGrid())
	    ;
	  }

	  delegateTasks(taskIds, toUserId) {
	    // eslint-disable-next-line promise/catch-or-return
	    main_core.ajax.runComponentAction('bitrix:bizproc.user.processes', 'delegateTasks', {
	      mode: 'class',
	      data: {
	        taskIds,
	        toUserId
	      }
	    }).catch(response => {
	      this.showErrors(response.errors);
	      this.reloadGrid();
	    })
	    // .then(() => this.reloadGrid())
	    ;
	  }

	  reloadGrid() {
	    var _this$getGrid;
	    (_this$getGrid = this.getGrid()) == null ? void 0 : _this$getGrid.reload();
	  }
	  doTask(props) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hideRow)[_hideRow](props.workflowId);
	    main_core.ajax.runAction('bizproc.task.do', {
	      data: props
	    }).then(() => {
	      if (props.taskName) {
	        ui_notification.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_TASK_TOUCHED', {
	            '#TASK_NAME#': main_core.Text.encode(props.taskName)
	          })
	        });
	      }
	    }).catch(response => {
	      this.showErrors(response.errors);
	      babelHelpers.classPrivateFieldLooseBase(this, _showRow)[_showRow](props.workflowId);
	    });
	  }
	  removeWorkflow(workflowId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hideRow)[_hideRow](workflowId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _deleteWorkflowRendererById)[_deleteWorkflowRendererById](workflowId);
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowTasks)[_workflowTasks].delete(workflowId);
	  }
	  getGrid() {
	    if (this.gridId) {
	      var _BX$Main$gridManager;
	      return (_BX$Main$gridManager = BX.Main.gridManager) == null ? void 0 : _BX$Main$gridManager.getInstanceById(this.gridId);
	    }

	    // eslint-disable-next-line no-console
	    console.warn('Grid not found');
	    return null;
	  }
	}
	function _subscribeToPushes2() {
	  BX.PULL.subscribe({
	    moduleId: 'bizproc',
	    command: 'workflow',
	    callback: params => {
	      if (params.eventName === 'DELETED' || params.eventName === 'UPDATED') {
	        params.items.forEach(workflow => this.removeWorkflow(workflow.id));
	      }
	      if (params.eventName === 'ADDED' || params.eventName === 'UPDATED') {
	        const rowsCollectionWrapper = this.getGrid().getRows();
	        let ids = params.items.map(workflow => workflow.id);
	        if (params.eventName === 'ADDED') {
	          ids = ids.filter(id => !rowsCollectionWrapper.getById(id));
	        }
	        if (ids.length > 0) {
	          this.loader.loadWorkflows(ids).then(babelHelpers.classPrivateFieldLooseBase(this, _updateWorkflows)[_updateWorkflows].bind(this)).catch(response => this.showErrors(response));
	        }
	      }
	    }
	  });
	}
	function _updateWorkflows2(response) {
	  var _this$getGrid2;
	  const {
	    workflows
	  } = response.data;
	  if (!main_core.Type.isArray(workflows)) {
	    // eslint-disable-next-line no-console
	    console.warn('Unexpected response from server. Expected workflow.data to be an array');
	    return;
	  }
	  const gridRealtime = (_this$getGrid2 = this.getGrid()) == null ? void 0 : _this$getGrid2.getRealtime();
	  if (gridRealtime) {
	    let lastWorkflowId = null;
	    workflows.forEach(workflow => {
	      const isActual = Boolean(workflow.taskCnt > 0 || workflow.commentCnt > 0 || workflow.startedById === this.currentUserId && workflow.isCompleted === false);
	      if (isActual) {
	        babelHelpers.classPrivateFieldLooseBase(this, _appendWorkflow)[_appendWorkflow]({
	          workflow,
	          renderer: babelHelpers.classPrivateFieldLooseBase(this, _createWorkflowRenderer)[_createWorkflowRenderer](workflow.workflowId, workflow),
	          insertAfter: lastWorkflowId
	        });
	        lastWorkflowId = workflow.workflowId;
	      }
	    });
	  }
	}
	function _appendWorkflow2({
	  workflow,
	  renderer,
	  insertAfter
	}) {
	  var _this$getGrid3, _this$getGrid4;
	  if (workflow.task) {
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowTasks)[_workflowTasks].set(workflow.workflowId, workflow.task.id);
	  }
	  const gridRealtime = (_this$getGrid3 = this.getGrid()) == null ? void 0 : _this$getGrid3.getRealtime();
	  if (!gridRealtime) {
	    return;
	  }
	  const addRowOptions = this.getDefaultAddRowOptions(workflow, renderer);
	  if (main_core.Type.isStringFilled(insertAfter)) {
	    addRowOptions.insertAfter = insertAfter;
	  } else {
	    addRowOptions.prepend = true;
	  }
	  gridRealtime.addRow(addRowOptions);

	  // temporary crutches for the GRID :-)
	  const row = (_this$getGrid4 = this.getGrid()) == null ? void 0 : _this$getGrid4.getRows().getById(workflow.workflowId);
	  if (row) {
	    if (addRowOptions.columnClasses) {
	      for (const [columnId, columnClass] of Object.entries(addRowOptions.columnClasses)) {
	        if (columnClass) {
	          main_core.Dom.addClass(row.getCellById(columnId), columnClass);
	        }
	      }
	    }
	    main_core.Dom.addClass(row.getNode(), 'main-ui-grid-show-new-row');
	    main_core.Event.bind(row.getNode(), 'animationend', event => {
	      if (event.animationName === 'showNewRow') {
	        main_core.Dom.removeClass(row.getNode(), 'main-ui-grid-show-new-row');
	      }
	    });
	  }
	}
	function _createWorkflowRenderer2(workflowId, workflow) {
	  babelHelpers.classPrivateFieldLooseBase(this, _workflowRenderer)[_workflowRenderer][workflowId] = new WorkflowRenderer({
	    userProcesses: this,
	    currentUserId: this.currentUserId,
	    workflow
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _workflowRenderer)[_workflowRenderer][workflowId];
	}
	function _getWorkflowRendererById2(workflowId) {
	  return main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _workflowRenderer)[_workflowRenderer][workflowId]) ? null : babelHelpers.classPrivateFieldLooseBase(this, _workflowRenderer)[_workflowRenderer][workflowId];
	}
	function _deleteWorkflowRendererById2(workflowId) {
	  const renderer = babelHelpers.classPrivateFieldLooseBase(this, _getWorkflowRendererById)[_getWorkflowRendererById](workflowId);
	  if (renderer) {
	    renderer.destroy();
	    delete babelHelpers.classPrivateFieldLooseBase(this, _workflowRenderer)[_workflowRenderer][workflowId];
	  }
	}
	function _hideRow2(id, remove = false) {
	  const grid = this.getGrid();
	  const row = grid == null ? void 0 : grid.getRows().getById(id);
	  if (row) {
	    row.hide();
	    if (remove) {
	      main_core.Dom.remove(row.getNode());
	    }
	    if (grid.getRows().getCountDisplayed() === 0) {
	      grid.getRealtime().showStub();
	    }
	  }
	}
	function _showRow2(id) {
	  var _this$getGrid5, _this$getGrid5$getRow;
	  (_this$getGrid5 = this.getGrid()) == null ? void 0 : (_this$getGrid5$getRow = _this$getGrid5.getRows().getById(id)) == null ? void 0 : _this$getGrid5$getRow.show();
	}

	exports.UserProcesses = UserProcesses;

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX.UI,BX.UI.EntitySelector,BX.Main,BX.Bizproc,BX.Bizproc,BX,BX.Bizproc.Workflow,BX.Bizproc.Workflow.Faces,BX.UI,BX,BX));
//# sourceMappingURL=script.js.map
