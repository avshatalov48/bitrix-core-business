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
	  if (!main_core.Type.isNil(data.targetUserId) && (!main_core.Type.isInteger(data.targetUserId) || data.targetUserId <= 0)) {
	    return false;
	  }
	  return validateFacesData(data.data);
	}
	function validateFacesData(data) {
	  if (!main_core.Type.isPlainObject(data)) {
	    return false;
	  }
	  if (!main_core.Type.isArrayFilled(data.steps)) {
	    return false;
	  }
	  for (const step of data.steps) {
	    if (!main_core.Type.isStringFilled(step.id) || !main_core.Type.isString(step.name) || !main_core.Type.isArray(step.avatars)) {
	      return false;
	    }
	    const duration = step.duration;
	    if (!main_core.Type.isString(duration) && (!main_core.Type.isNumber(duration) || duration < 0)) {
	      return false;
	    }
	  }
	  return true;
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _target = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("target");
	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	var _showArrow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showArrow");
	var _showTimeStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showTimeStep");
	var _workflowId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowId");
	var _targetUserId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetUserId");
	var _stack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stack");
	var _unsubscribePushCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unsubscribePushCallback");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _timelineNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timelineNode");
	var _errorNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorNode");
	var _initStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initStack");
	var _getStackSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackSteps");
	var _createStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStep");
	var _getStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStack");
	var _getUserStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getUserStack");
	var _getStackUserImages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStackUserImages");
	var _getIconStack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconStack");
	var _getFooter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFooter");
	var _getStubStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStubStep");
	var _subscribeToPushes = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToPushes");
	var _onWorkflowPush = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWorkflowPush");
	var _loadWorkflowFaces = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadWorkflowFaces");
	var _getRunningTaskId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRunningTaskId");
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
	    Object.defineProperty(this, _getRunningTaskId, {
	      value: _getRunningTaskId2
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
	    Object.defineProperty(this, _getStubStep, {
	      value: _getStubStep2
	    });
	    Object.defineProperty(this, _getFooter, {
	      value: _getFooter2
	    });
	    Object.defineProperty(this, _getIconStack, {
	      value: _getIconStack2
	    });
	    Object.defineProperty(this, _getStackUserImages, {
	      value: _getStackUserImages2
	    });
	    Object.defineProperty(this, _getUserStack, {
	      value: _getUserStack2
	    });
	    Object.defineProperty(this, _getStack, {
	      value: _getStack2
	    });
	    Object.defineProperty(this, _createStep, {
	      value: _createStep2
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
	    Object.defineProperty(this, _showTimeStep, {
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
	      throw new TypeError('Bizproc.Workflow.Faces: data must be correct plain object');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowId)[_workflowId] = _data2.workflowId;
	    babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] = _data2.target;
	    babelHelpers.classPrivateFieldLooseBase(this, _targetUserId)[_targetUserId] = _data2.targetUserId || 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = _data2.data;
	    if (main_core.Type.isBoolean(_data2.showArrow)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showArrow)[_showArrow] = _data2.showArrow;
	    }
	    if (main_core.Type.isBoolean(_data2.showTimeStep)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showTimeStep)[_showTimeStep] = _data2.showTimeStep;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _initStack)[_initStack]();
	    if (_data2.subscribeToPushes && !_data2.isWorkflowFinished) {
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _showTimeStep)[_showTimeStep] && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].timeStep) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderTimeline)[_renderTimeline](), babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    }
	  }
	  updateData(data) {
	    const facesData = {
	      steps: data.steps,
	      progressBox: data.progressBox,
	      timeStep: data.timeStep
	    };
	    if (!validateFacesData(facesData)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = facesData;
	    babelHelpers.classPrivateFieldLooseBase(this, _getStackSteps)[_getStackSteps]().forEach(step => {
	      babelHelpers.classPrivateFieldLooseBase(this, _stack)[_stack].updateStep(step, step.id);
	    });
	    if (data.isWorkflowFinished) {
	      babelHelpers.classPrivateFieldLooseBase(this, _unsubscribeToPushes)[_unsubscribeToPushes]();
	      if (babelHelpers.classPrivateFieldLooseBase(this, _showTimeStep)[_showTimeStep]) {
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
	  const steps = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].steps.forEach(stepData => {
	    steps.push(babelHelpers.classPrivateFieldLooseBase(this, _createStep)[_createStep](stepData));
	  });
	  if (steps.length < 3) {
	    for (let i = steps.length; i < 3; i++) {
	      steps.push(babelHelpers.classPrivateFieldLooseBase(this, _getStubStep)[_getStubStep]());
	    }
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].progressBox && babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].progressBox.progressTasksCount > 0) {
	    steps[0].progressBox = {
	      title: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].progressBox.text
	    };
	  }
	  return steps.map((step, index) => ({
	    ...step,
	    id: `step-${index}`
	  }));
	}
	function _createStep2(data) {
	  return {
	    id: data.id,
	    header: {
	      type: ui_imageStackSteps.headerTypeEnum.TEXT,
	      data: {
	        text: data.name
	      }
	    },
	    stack: babelHelpers.classPrivateFieldLooseBase(this, _getStack)[_getStack](data),
	    footer: babelHelpers.classPrivateFieldLooseBase(this, _getFooter)[_getFooter](data),
	    styles: {
	      minWidth: 75
	    }
	  };
	}
	function _getStack2(data) {
	  const userStack = babelHelpers.classPrivateFieldLooseBase(this, _getUserStack)[_getUserStack](data);
	  if (userStack) {
	    return userStack;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _getIconStack)[_getIconStack](data);
	}
	function _getUserStack2(data) {
	  const images = babelHelpers.classPrivateFieldLooseBase(this, _getStackUserImages)[_getStackUserImages](data.avatarsData);
	  if (main_core.Type.isArrayFilled(images)) {
	    const stack = {
	      images
	    };
	    let status = null;
	    switch (data.status) {
	      case 'wait':
	        status = ui_imageStackSteps.stackStatusEnum.WAIT;
	        break;
	      case 'success':
	        status = ui_imageStackSteps.stackStatusEnum.OK;
	        break;
	      case 'not-success':
	        status = ui_imageStackSteps.stackStatusEnum.CANCEL;
	        break;
	      default:
	        status = null;
	    }
	    if (status) {
	      stack.status = {
	        type: status
	      };
	    }
	    return stack;
	  }
	  return null;
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
	function _getIconStack2(data) {
	  let icon = null;
	  let color = null;
	  switch (data.id) {
	    case 'completed':
	      icon = data.success ? 'circle-check' : 'cross-circle-60';
	      color = data.success ? 'var(--ui-color-primary-alt)' : 'var(--ui-color-base-35)';
	      break;
	    case 'running':
	      icon = 'black-clock';
	      color = 'var(--ui-color-palette-blue-60)';
	      break;
	    case 'done':
	      icon = 'circle-check';
	      color = 'var(--ui-color-primary-alt)';
	      break;
	    default:
	      icon = 'bp';
	      color = 'var(--ui-color-palette-gray-20)';
	  }
	  return {
	    images: [{
	      type: ui_imageStackSteps.imageTypeEnum.ICON,
	      data: {
	        icon,
	        color
	      }
	    }]
	  };
	}
	function _getFooter2(data) {
	  if (main_core.Type.isNumber(data.duration) && data.duration > 0 || data.id === 'running') {
	    return {
	      type: ui_imageStackSteps.footerTypeEnum.DURATION,
	      data: {
	        duration: main_core.Text.toInteger(data.duration),
	        realtime: data.id === 'running'
	      }
	    };
	  }
	  return {
	    type: ui_imageStackSteps.footerTypeEnum.TEXT,
	    data: {
	      text: String(data.duration)
	    }
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
	    },
	    styles: {
	      minWidth: 75
	    }
	  };
	}
	function _subscribeToPushes2() {
	  if (BX.PULL) {
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
	        runningTaskId: babelHelpers.classPrivateFieldLooseBase(this, _getRunningTaskId)[_getRunningTaskId](),
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
	function _getRunningTaskId2() {
	  const runningStep = babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].steps.find(step => step.id === 'running');
	  if (runningStep) {
	    return runningStep.taskId;
	  }
	  return 0;
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
	    data: babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].timeStep
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
