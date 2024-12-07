/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,bizproc_types) {
	'use strict';

	var _status = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("status");
	class TaskStatus {
	  constructor(rawStatus) {
	    Object.defineProperty(this, _status, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] = rawStatus;
	  }
	  isWaiting() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === 0;
	  }
	  isYes() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === 1;
	  }
	  isNo() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === 2;
	  }
	  isOk() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === 3;
	  }
	  isCancel() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _status)[_status] === 4;
	  }
	  isCustom() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _status)[_status]);
	  }
	  get name() {
	    if (this.isCustom()) {
	      return main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _status)[_status]);
	    }
	    if (this.isYes()) {
	      return main_core.Loc.getMessage('BIZPROC_TASK_STATUS_YES');
	    }
	    if (this.isNo() || this.isCancel()) {
	      return main_core.Loc.getMessage('BIZPROC_TASK_STATUS_NO');
	    }
	    return main_core.Loc.getMessage('BIZPROC_TASK_STATUS_OK');
	  }
	}

	class UserStatus extends TaskStatus {}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _task = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("task");
	var _responsibleUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("responsibleUser");
	var _renderTaskButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTaskButtons");
	var _renderTaskButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTaskButton");
	var _renderDefaultTaskButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDefaultTaskButton");
	class InlineTaskView$$1 {
	  constructor(options) {
	    Object.defineProperty(this, _renderDefaultTaskButton, {
	      value: _renderDefaultTaskButton2
	    });
	    Object.defineProperty(this, _renderTaskButton, {
	      value: _renderTaskButton2
	    });
	    Object.defineProperty(this, _renderTaskButtons, {
	      value: _renderTaskButtons2
	    });
	    Object.defineProperty(this, _task, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _responsibleUser, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _task)[_task] = options.task;
	    this.setResponsibleUser(options.responsibleUser);
	  }
	  setResponsibleUser(userId) {
	    if (main_core.Type.isNumber(userId)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _responsibleUser)[_responsibleUser] = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].users.find(user => user.id === userId);
	    }
	    return this;
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].isInline()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderDefaultTaskButton)[_renderDefaultTaskButton]();
	    }
	    if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].controls.buttons)) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _renderTaskButtons)[_renderTaskButtons]();
	    }
	    return null;
	  }
	  renderTaskAnchor() {
	    return main_core.Tag.render(_t || (_t = _`
			<a href="${0}"></a>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].url || '#'));
	  }
	}
	function _renderTaskButtons2() {
	  const buttonsPanel = main_core.Tag.render(_t2 || (_t2 = _`<div class="bp-btn-panel-block"></div>`));
	  const taskButtons = babelHelpers.classPrivateFieldLooseBase(this, _task)[_task].controls.buttons;
	  if (!main_core.Type.isArray(taskButtons)) {
	    return buttonsPanel;
	  }
	  for (const button of taskButtons) {
	    let renderedButton = null;
	    if (!Object.hasOwn(button, 'default')) {
	      renderedButton = babelHelpers.classPrivateFieldLooseBase(this, _renderTaskButton)[_renderTaskButton](button);
	    } else if (button.default === true) {
	      renderedButton = babelHelpers.classPrivateFieldLooseBase(this, _renderDefaultTaskButton)[_renderDefaultTaskButton]();
	    }
	    if (main_core.Type.isDomNode(renderedButton)) {
	      main_core.Dom.append(renderedButton, buttonsPanel);
	    }
	  }
	  return buttonsPanel;
	}
	function _renderTaskButton2(button) {
	  const targetStatus = new UserStatus(button.TARGET_USER_STATUS);
	  const isDecline = targetStatus.isNo() || targetStatus.isCancel();
	  const className = isDecline ? 'light-border' : 'success';
	  const encodedText = main_core.Text.encode(button.TEXT);
	  const renderedButton = main_core.Tag.render(_t3 || (_t3 = _`
			<div
				class="ui-btn ui-btn-round ui-btn-xs ui-btn-no-caps ui-btn-${0}"
				title="${0}"
			>
				<div class="ui-btn-text">${0}</div>
			</div>
		`), className, encodedText, encodedText);
	  if (main_core.Type.isFunction(button.onclick)) {
	    main_core.Event.bind(renderedButton, 'click', button.onclick.bind(renderedButton));
	  }
	  return renderedButton;
	}
	function _renderDefaultTaskButton2() {
	  const anchor = this.renderTaskAnchor();
	  if (main_core.Type.isDomNode(anchor)) {
	    main_core.Dom.addClass(anchor, ['ui-btn', 'ui-btn-primary', 'ui-btn-round', 'ui-btn-xs', 'ui-btn-no-caps']);
	    const buttonText = main_core.Loc.getMessage('BIZPROC_TASK_DEFAULT_TASK_BUTTON');
	    anchor.innerText = buttonText;
	    return main_core.Tag.render(_t4 || (_t4 = _`
				<div class="bp-btn-panel-block" title="${0}">
					${0}
				</div>
			`), main_core.Text.encode(buttonText), anchor);
	  }
	  return null;
	}

	var _data = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("data");
	class Task {
	  constructor(task) {
	    Object.defineProperty(this, _data, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data] = task;
	  }
	  get id() {
	    return main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].id : 0;
	  }
	  get name() {
	    return main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].name) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].name : '';
	  }
	  hasDescription() {
	    return main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].description);
	  }
	  get description() {
	    return this.hasDescription() ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].description : '';
	  }
	  hasUrl() {
	    return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].url);
	  }
	  get url() {
	    return this.hasUrl() ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].url : '';
	  }
	  canShowInPopup() {
	    return main_core.Type.isBoolean(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].canShowInPopup) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].canShowInPopup : false;
	  }
	  isResponsibleForTask(userId) {
	    const responsibleUser = this.users.find(user => user.id === userId);
	    return !main_core.Type.isNil(responsibleUser);
	  }
	  get users() {
	    return main_core.Type.isArray(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].users) ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].users.map(user => ({
	      ...user,
	      status: new UserStatus(user.status)
	    })) : [];
	  }
	  hasStatus() {
	    return main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].status) || main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].status);
	  }
	  getStatus() {
	    return new TaskStatus(this.hasStatus() ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].status : 0);
	  }
	  get modified() {
	    return main_core.Type.isNumber(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].modified) ? Math.max(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].modified, 0) : 0;
	  }
	  hasControls() {
	    return main_core.Type.isPlainObject(babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].controls);
	  }
	  get controls() {
	    return this.hasControls() ? babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].controls : {};
	  }
	  get buttons() {
	    if (this.hasControls() && main_core.Type.isArray(this.controls.buttons)) {
	      return this.controls.buttons;
	    }
	    return [];
	  }
	  setControls(controls) {
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].controls = controls;
	    return this;
	  }
	  setButtons(buttons) {
	    if (!this.hasControls()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].controls = {};
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].controls.buttons = buttons;
	    return this;
	  }
	  isCompleted() {
	    return this.hasStatus() ? !this.getStatus().isWaiting() : false;
	  }
	  isInline() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _data)[_data].isInline;
	  }
	}

	exports.TaskStatus = TaskStatus;
	exports.UserStatus = UserStatus;
	exports.Task = Task;
	exports.InlineTaskView = InlineTaskView$$1;

}((this.BX.Bizproc = this.BX.Bizproc || {}),BX,BX.Bizproc));
//# sourceMappingURL=task.bundle.js.map
