/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,bizproc_workflow_faces_summary,ui_imageStackSteps,main_core) {
	'use strict';

	function workflowFacesDataValidator(data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    return false;
	  }
	  if (!main_core.Type.isStringFilled(data.workflowId)) {
	    return false;
	  }
	  if (!main_core.Type.isDomNode(data.target)) {
	    return false;
	  }
	  if (!main_core.Type.isInteger(data.targetUserId) || data.targetUserId <= 0) {
	    return false;
	  }
	  return validateFacesData(data.data);
	}
	function validateFacesData(data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    return false;
	  }

	  // avatars
	  const avatars = data.avatars;
	  if (!main_core.Type.isPlainObject(avatars) || !main_core.Type.isArrayFilled(avatars.author) || !main_core.Type.isArray(avatars.running) || !main_core.Type.isArray(avatars.completed) || !main_core.Type.isArray(avatars.done)) {
	    return false;
	  }

	  // statuses
	  const statuses = data.statuses;
	  if (!main_core.Type.isPlainObject(statuses)) {
	    return false;
	  }

	  // time
	  const time = data.time;
	  if (!main_core.Type.isPlainObject(time) || !timeValidator(time.author) || !timeValidator(time.running) || !timeValidator(time.completed) || !timeValidator(time.done) || !timeValidator(time.total)) {
	    return false;
	  }
	  return true;
	}
	const timeValidator = time => {
	  return main_core.Type.isNull(time) || time === 0 || main_core.Text.toInteger(time) > 0;
	};

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _target = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("target");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _showArrow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showArrow");
	var _showTimeline = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showTimeline");
	var _workflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _targetUserId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetUserId");
	var _stack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stack");
	var _unsubscribePushCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribePushCallback");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _timelineNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timelineNode");
	var _errorNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorNode");
	var _initStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initStack");
	var _getStackSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackSteps");
	var _getAuthorStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAuthorStep");
	var _getHiddenTaskCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHiddenTaskCount");
	var _getRunningStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRunningStep");
	var _getCompletedStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCompletedStep");
	var _getDoneStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDoneStep");
	var _getStubStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStubStep");
	var _getStackUserImages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackUserImages");
	var _getFooterDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFooterDuration");
	var _subscribeToPushes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPushes");
	var _onWorkflowPush = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWorkflowPush");
	var _loadWorkflowFaces = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadWorkflowFaces");
	var _unsubscribeToPushes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribeToPushes");
	var _renderTimeline = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTimeline");
	var _renderError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderError");
	class WorkflowFaces {
	  constructor(_data2) {
	    Object.defineProperty(this, _renderError, {
	      value: _renderError2
	    });
	    Object.defineProperty(this, _renderTimeline, {
	      value: _renderTimeline2
	    });
	    Object.defineProperty(this, _unsubscribeToPushes, {
	      value: _unsubscribeToPushes2
	    });
	    Object.defineProperty(this, _loadWorkflowFaces, {
	      value: _loadWorkflowFaces2
	    });
	    Object.defineProperty(this, _onWorkflowPush, {
	      value: _onWorkflowPush2
	    });
	    Object.defineProperty(this, _subscribeToPushes, {
	      value: _subscribeToPushes2
	    });
	    Object.defineProperty(this, _getFooterDuration, {
	      value: _getFooterDuration2
	    });
	    Object.defineProperty(this, _getStackUserImages, {
	      value: _getStackUserImages2
	    });
	    Object.defineProperty(this, _getStubStep, {
	      value: _getStubStep2
	    });
	    Object.defineProperty(this, _getDoneStep, {
	      value: _getDoneStep2
	    });
	    Object.defineProperty(this, _getCompletedStep, {
	      value: _getCompletedStep2
	    });
	    Object.defineProperty(this, _getRunningStep, {
	      value: _getRunningStep2
	    });
	    Object.defineProperty(this, _getHiddenTaskCount, {
	      value: _getHiddenTaskCount2
	    });
	    Object.defineProperty(this, _getAuthorStep, {
	      value: _getAuthorStep2
	    });
	    Object.defineProperty(this, _getStackSteps, {
	      value: _getStackSteps2
	    });
	    Object.defineProperty(this, _initStack, {
	      value: _initStack2
	    });
	    Object.defineProperty(this, _target, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _showArrow, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showTimeline, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _workflowId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _targetUserId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stack, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _unsubscribePushCallback, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _timelineNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errorNode, {
	      writable: true,
	      value: void 0
	    });
	    if (!workflowFacesDataValidator(_data2)) {
	      throw new TypeError('Bizproc.Workflow.Faces: data must be correct plain object', _data2);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = _data2.workflowId;
	    babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] = _data2.target;
	    babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId] = _data2.targetUserId;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = _data2.data;
	    if (main_core.Type.isBoolean(_data2.showArrow)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showArrow)[_showArrow] = _data2.showArrow;
	    }
	    if (main_core.Type.isBoolean(_data2.showTimeline)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showTimeline)[_showTimeline] = _data2.showTimeline;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initStack)[_initStack]();
	    if (_data2.subscribeToPushes) {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribeToPushes)[_subscribeToPushes]();
	    }
	  }
	  render() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _node)[_node] = main_core.Tag.render(_t || (_t = _`<div class="bp-workflow-faces"></div>`));
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node], babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	    babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].renderTo(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _showArrow)[_showArrow]) {
	      main_core.Dom.append(main_core.Tag.render(_t2 || (_t2 = _`<div class="bp-workflow-faces-arrow"></div>`)), babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _showTimeline)[_showTimeline]) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderTimeline)[_renderTimeline](), babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    }
	  }
	  updateData(data) {
	    if (!validateFacesData(data)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = data;
	    babelHelpers.classPrivateFieldLooseBase(this, _getStackSteps)[_getStackSteps]().forEach(step => {
	      babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].updateStep(step, step.id);
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowIsCompleted) {
	      babelHelpers.classPrivateFieldLooseBase(this, _unsubscribeToPushes)[_unsubscribeToPushes]();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _showTimeline)[_showTimeline]) {
	        main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _timelineNode)[_timelineNode], babelHelpers.classPrivateFieldLooseBase(this, _renderTimeline)[_renderTimeline]());
	      }
	    }
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribeToPushes)[_unsubscribeToPushes]();
	    babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].destroy();
	    babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = null;
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _timelineNode)[_timelineNode]);
	    babelHelpers.classPrivateFieldLooseBase(this, _timelineNode)[_timelineNode] = null;
	  }
	}
	function _initStack2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack] = new ui_imageStackSteps.ImageStackSteps({
	    steps: babelHelpers.classPrivateFieldLooseBase(this, _getStackSteps)[_getStackSteps]()
	  });
	}
	function _getStackSteps2() {
	  const steps = [babelHelpers.classPrivateFieldLooseBase(this, _getAuthorStep)[_getAuthorStep]()];
	  if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].avatars.completed)) {
	    steps.push(babelHelpers.classPrivateFieldLooseBase(this, _getCompletedStep)[_getCompletedStep]());
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowIsCompleted) {
	    steps.push(babelHelpers.classPrivateFieldLooseBase(this, _getDoneStep)[_getDoneStep]());
	  } else {
	    steps.push(babelHelpers.classPrivateFieldLooseBase(this, _getRunningStep)[_getRunningStep]());
	  }
	  if (steps.length === 2) {
	    steps.push(babelHelpers.classPrivateFieldLooseBase(this, _getStubStep)[_getStubStep]());
	  }
	  return steps.map((step, index) => ({
	    ...step,
	    id: `step-${index}`
	  }));
	}
	function _getAuthorStep2() {
	  const stack = {
	    images: [{
	      type: ui_imageStackSteps.imageTypeEnum.ICON,
	      data: {
	        icon: 'bp',
	        color: 'var(--ui-color-palette-gray-20)'
	      }
	    }]
	  };
	  const avatar = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].avatars.author[0];
	  const authorId = main_core.Text.toInteger(avatar.id);
	  if (authorId > 0) {
	    stack.images = [{
	      type: ui_imageStackSteps.imageTypeEnum.USER,
	      data: {
	        src: String(avatar.avatarUrl || ''),
	        userId: authorId
	      }
	    }];
	  }
	  const step = {
	    id: 'author',
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.TEXT,
	      data: {
	        text: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_AUTHOR')
	      }
	    },
	    stack,
	    footer: babelHelpers.classPrivateFieldLooseBase(this, _getFooterDuration)[_getFooterDuration](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].time.author)
	  };
	  const hiddenTaskCount = babelHelpers.classPrivateFieldLooseBase(this, _getHiddenTaskCount)[_getHiddenTaskCount]();
	  if (hiddenTaskCount > 0) {
	    step.progressBox = {
	      title: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_COMPLETED_TASK_COUNT', {
	        '#COUNT#': hiddenTaskCount
	      })
	    };
	  }
	  return step;
	}
	function _getHiddenTaskCount2() {
	  const completedTaskCount = main_core.Text.toInteger(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].completedTaskCount);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowIsCompleted) {
	    return completedTaskCount > 2 ? completedTaskCount - 2 : 0;
	  }
	  return completedTaskCount > 1 ? completedTaskCount - 1 : 0;
	}
	function _getRunningStep2() {
	  const stack = {
	    images: [{
	      type: ui_imageStackSteps.imageTypeEnum.ICON,
	      data: {
	        icon: 'black-clock',
	        color: 'var(--ui-color-palette-blue-60)'
	      }
	    }]
	  };
	  const images = babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].avatars.running);
	  if (main_core.Type.isArrayFilled(images)) {
	    stack.images = images;
	    stack.status = {
	      type: ui_imageStackSteps.stackStatusEnum.WAIT
	    };
	  }
	  return {
	    id: 'running',
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.TEXT,
	      data: {
	        text: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_RUNNING')
	      }
	    },
	    stack,
	    footer: {
	      type: ui_imageStackSteps.footerTypeEnum.DURATION,
	      data: {
	        duration: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].time.running,
	        realtime: true
	      }
	    }
	  };
	}
	function _getCompletedStep2() {
	  const isSuccess = main_core.Text.toBoolean(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].statuses.completedSuccess);
	  const stack = {
	    images: [{
	      type: ui_imageStackSteps.imageTypeEnum.ICON,
	      data: {
	        icon: isSuccess ? 'circle-check' : 'cross-circle-60',
	        color: isSuccess ? 'var(--ui-color-primary-alt)' : 'var(--ui-color-base-35)'
	      }
	    }]
	  };
	  const images = babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].avatars.completed);
	  if (main_core.Type.isArrayFilled(images)) {
	    stack.images = images;
	    stack.status = {
	      type: isSuccess ? ui_imageStackSteps.stackStatusEnum.OK : ui_imageStackSteps.stackStatusEnum.CANCEL
	    };
	  }
	  return {
	    id: 'completed',
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.TEXT,
	      data: {
	        text: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_COMPLETED')
	      }
	    },
	    stack,
	    footer: babelHelpers.classPrivateFieldLooseBase(this, _getFooterDuration)[_getFooterDuration](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].time.completed)
	  };
	}
	function _getDoneStep2() {
	  const stack = {
	    images: [{
	      type: ui_imageStackSteps.imageTypeEnum.ICON,
	      data: {
	        icon: 'circle-check',
	        color: 'var(--ui-color-primary-alt)'
	      }
	    }]
	  };
	  const images = babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].avatars.done);
	  if (main_core.Type.isArrayFilled(images)) {
	    const isSuccess = main_core.Text.toBoolean(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].statuses.doneSuccess);
	    stack.images = images;
	    stack.status = {
	      type: isSuccess ? ui_imageStackSteps.stackStatusEnum.OK : ui_imageStackSteps.stackStatusEnum.CANCEL
	    };
	  }
	  return {
	    id: 'done',
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.TEXT,
	      data: {
	        text: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_COLUMN_DONE')
	      }
	    },
	    stack,
	    footer: babelHelpers.classPrivateFieldLooseBase(this, _getFooterDuration)[_getFooterDuration](babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].time.done)
	  };
	}
	function _getStubStep2() {
	  return {
	    id: 'stub',
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.STUB
	    },
	    stack: {
	      images: [{
	        type: ui_imageStackSteps.imageTypeEnum.USER_STUB
	      }]
	    },
	    footer: {
	      type: ui_imageStackSteps.footerTypeEnum.STUB
	    }
	  };
	}
	function _getStackUserImages2(avatars) {
	  const images = [];
	  if (main_core.Type.isArrayFilled(avatars)) {
	    avatars.forEach(avatar => {
	      const userId = main_core.Text.toInteger(avatar.id);
	      if (userId > 0) {
	        images.push({
	          type: ui_imageStackSteps.imageTypeEnum.USER,
	          data: {
	            userId,
	            src: String(avatar.avatarUrl || '')
	          }
	        });
	      }
	    });
	  }
	  return images;
	}
	function _getFooterDuration2(time) {
	  if (main_core.Type.isNumber(time) && time > 0) {
	    return {
	      type: ui_imageStackSteps.footerTypeEnum.DURATION,
	      data: {
	        duration: time,
	        realtime: false
	      }
	    };
	  }
	  return {
	    type: ui_imageStackSteps.footerTypeEnum.TEXT,
	    data: {
	      text: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_FACES_EMPTY_TIME')
	    }
	  };
	}
	function _subscribeToPushes2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowIsCompleted && BX.PULL) {
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribePushCallback)[_unsubscribePushCallback] = BX.PULL.subscribe({
	      moduleId: 'bizproc',
	      command: 'workflow',
	      callback: babelHelpers.classPrivateFieldLooseBase(this, _onWorkflowPush)[_onWorkflowPush].bind(this)
	    });
	  }
	}
	function _onWorkflowPush2(params) {
	  if (params && params.eventName === 'UPDATED' && main_core.Type.isArrayFilled(params.items)) {
	    for (const item of params.items) {
	      if (String(item.id) === babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _loadWorkflowFaces)[_loadWorkflowFaces]();
	        return;
	      }
	    }
	  }
	}
	function _loadWorkflowFaces2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] && main_core.Type.isDomNode(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]) && babelHelpers.classPrivateFieldLooseBase(this, _target)[_target].clientHeight > 0) {
	    main_core.ajax.runAction('bizproc.workflow.faces.load', {
	      data: {
	        workflowId: babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId],
	        runningTaskId: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].runningTaskId || 0,
	        userId: babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId]
	      }
	    }).then(({
	      data
	    }) => {
	      if (main_core.Type.isDomNode(babelHelpers.classPrivateFieldLooseBase(this, _errorNode)[_errorNode])) {
	        main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _errorNode)[_errorNode], babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	        babelHelpers.classPrivateFieldLooseBase(this, _errorNode)[_errorNode] = null;
	      }
	      this.updateData(data);
	    }).catch(({
	      errors
	    }) => {
	      if (main_core.Type.isArrayFilled(errors)) {
	        const firstError = errors.pop();
	        if (firstError.code === 'ACCESS_DENIED') {
	          main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node], babelHelpers.classPrivateFieldLooseBase(this, _renderError)[_renderError](firstError.message));
	          this.errorMessage = firstError.message;
	        }
	      }
	    });
	  }
	}
	function _unsubscribeToPushes2() {
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _unsubscribePushCallback)[_unsubscribePushCallback])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribePushCallback)[_unsubscribePushCallback]();
	    babelHelpers.classPrivateFieldLooseBase(this, _unsubscribePushCallback)[_unsubscribePushCallback] = null;
	  }
	}
	function _renderTimeline2() {
	  const timeline = new bizproc_workflow_faces_summary.Summary({
	    workflowId: babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId],
	    time: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].time.total,
	    workflowIsCompleted: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].workflowIsCompleted,
	    showArrow: false
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _timelineNode)[_timelineNode] = timeline.render();
	  return babelHelpers.classPrivateFieldLooseBase(this, _timelineNode)[_timelineNode];
	}
	function _renderError2(message) {
	  babelHelpers.classPrivateFieldLooseBase(this, _errorNode)[_errorNode] = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="bp-workflow-faces">
				<span class="bp-workflow-faces-error-message">
					${0}
				</span>
			</div>
		`), main_core.Text.encode(message));
	  return babelHelpers.classPrivateFieldLooseBase(this, _errorNode)[_errorNode];
	}

	exports.WorkflowFaces = WorkflowFaces;

}((this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {}),BX.Bizproc.Workflow.Faces,BX.UI,BX));
//# sourceMappingURL=faces.bundle.js.map
