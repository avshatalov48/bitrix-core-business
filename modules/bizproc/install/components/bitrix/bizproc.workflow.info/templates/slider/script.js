/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core_events,ui_buttons,bizproc_task,ui_dialogs_messagebox,main_core) {
	'use strict';

	var _NEED_PATTERN = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("NEED_PATTERN");
	var _MULTIPLE_WITH_NO_BRACKETS = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("MULTIPLE_WITH_NO_BRACKETS");
	var _getFieldId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldId");
	var _getFieldValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFieldValues");
	var _isFieldEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFieldEmpty");
	var _isValueEmpty = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isValueEmpty");
	var _getPattern = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPattern");
	var _getPatternValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPatternValues");
	class ValidateHelper {
	  static checkRequiredFieldsFilled(formData, requiredFields) {
	    const errors = [];
	    for (const requiredField of requiredFields) {
	      const fieldId = babelHelpers.classPrivateFieldLooseBase(this, _getFieldId)[_getFieldId](requiredField);
	      const values = babelHelpers.classPrivateFieldLooseBase(this, _getFieldValues)[_getFieldValues](formData, requiredField, fieldId);
	      if (!main_core.Type.isArrayFilled(values)) {
	        var _requiredField$FieldI;
	        const originalFieldId = (_requiredField$FieldI = requiredField.FieldId) != null ? _requiredField$FieldI : requiredField.Id;
	        if (!formData.keys().every(key => !key.includes(originalFieldId))) {
	          continue;
	        }
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isFieldEmpty)[_isFieldEmpty](requiredField, values)) {
	        errors.push({
	          message: main_core.Loc.getMessage('BPWFI_SLIDER_ARGUMENT_NULL', {
	            '#PARAM#': requiredField.Name
	          }),
	          fieldId: requiredField.Id
	        });
	      }
	    }
	    return errors;
	  }
	}
	function _getFieldId2(field) {
	  let fieldId = main_core.Type.isNil(field.FieldId) ? field.Id : field.FieldId;
	  if (field.Multiple || field.Type === 'S:DiskFile') {
	    fieldId = babelHelpers.classPrivateFieldLooseBase(ValidateHelper, _MULTIPLE_WITH_NO_BRACKETS)[_MULTIPLE_WITH_NO_BRACKETS].has(field.Type) ? fieldId : `${fieldId}[]`;
	  }
	  return fieldId;
	}
	function _getFieldValues2(formData, field, fieldId) {
	  return babelHelpers.classPrivateFieldLooseBase(ValidateHelper, _NEED_PATTERN)[_NEED_PATTERN].has(field.Type) ? babelHelpers.classPrivateFieldLooseBase(this, _getPatternValues)[_getPatternValues](formData, babelHelpers.classPrivateFieldLooseBase(this, _getPattern)[_getPattern](field, fieldId)) : formData.getAll(fieldId);
	}
	function _isFieldEmpty2(field, values) {
	  if (field.Multiple || field.Type === 'S:DiskFile' || babelHelpers.classPrivateFieldLooseBase(ValidateHelper, _NEED_PATTERN)[_NEED_PATTERN].has(field.Type)) {
	    return !main_core.Type.isArrayFilled(values) || values.every(value => babelHelpers.classPrivateFieldLooseBase(this, _isValueEmpty)[_isValueEmpty](field, value));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _isValueEmpty)[_isValueEmpty](field, values[0]);
	}
	function _isValueEmpty2(field, value) {
	  if (field.Type === 'file') {
	    return main_core.Type.isFile(value) && value.name === '';
	  }
	  return !main_core.Type.isStringFilled(value);
	}
	function _getPattern2(field, fieldId) {
	  if (field.Type === 'S:HTML') {
	    return field.Multiple ? `${fieldId}\\[n\\d+\\]\\[TEXT\\]` : `${fieldId}\\[TEXT\\]`;
	  }
	  if (field.Type === 'E:EList') {
	    return field.Multiple ? `${fieldId}\\[[n]?\\d+\\]\\[VALUE\\]` : `^${fieldId}$`;
	  }
	  if (field.Type === 'email' || field.Type === 'phone' || field.Type === 'web' || field.Type === 'im') {
	    return `${fieldId}\\[${field.Type.toUpperCase()}\\]\\[n\\d+\\]\\[VALUE\\]`;
	  }
	  return '';
	}
	function _getPatternValues2(formData, pattern) {
	  const values = [];
	  for (const [key, value] of formData.entries()) {
	    if (new RegExp(pattern).test(key)) {
	      values.push(value);
	    }
	  }
	  return values;
	}
	Object.defineProperty(ValidateHelper, _getPatternValues, {
	  value: _getPatternValues2
	});
	Object.defineProperty(ValidateHelper, _getPattern, {
	  value: _getPattern2
	});
	Object.defineProperty(ValidateHelper, _isValueEmpty, {
	  value: _isValueEmpty2
	});
	Object.defineProperty(ValidateHelper, _isFieldEmpty, {
	  value: _isFieldEmpty2
	});
	Object.defineProperty(ValidateHelper, _getFieldValues, {
	  value: _getFieldValues2
	});
	Object.defineProperty(ValidateHelper, _getFieldId, {
	  value: _getFieldId2
	});
	Object.defineProperty(ValidateHelper, _NEED_PATTERN, {
	  writable: true,
	  value: new Set(['S:HTML', 'email', 'phone', 'web', 'im', 'E:EList'])
	});
	Object.defineProperty(ValidateHelper, _MULTIPLE_WITH_NO_BRACKETS, {
	  writable: true,
	  value: new Set([...babelHelpers.classPrivateFieldLooseBase(ValidateHelper, _NEED_PATTERN)[_NEED_PATTERN], 'user', 'S:employee', 'sms_sender', 'mail_sender'])
	});

	function doTaskAction(data, slider, isLast) {
	  BX.SidePanel.Instance.postMessage(window, 'try-do-bp-task-event', {
	    workflowId: data.get('workflowId')
	  });
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runAction('bizproc.task.do', {
	      data
	    }).then(response => {
	      if (isLast) {
	        BX.SidePanel.Instance.postMessage(slider, 'success-do-bp-task-event', {
	          taskName: data.get('taskName')
	        });
	      }
	      resolve(response);
	    }).catch(response => {
	      BX.SidePanel.Instance.postMessage(slider, 'error-do-bp-task-event', {
	        workflowId: data.get('workflowId')
	      });
	      reject(response);
	    });
	  });
	}

	let _ = t => t,
	  _t;
	var _isChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChanged");
	var _messageBox = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageBox");
	var _canClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canClose");
	var _workflowResult = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workflowResult");
	var _canUseHumanResources = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canUseHumanResources");
	var _renderButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtons");
	var _handleTaskButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTaskButtonClick");
	var _isNeedValidate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNeedValidate");
	var _getRequiredFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRequiredFields");
	var _getNextTaskOrClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNextTaskOrClose");
	var _prepareErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareErrors");
	var _handleDelegateButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDelegateButtonClick");
	var _delegateTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delegateTask");
	var _sendMarkAsRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMarkAsRead");
	var _clearError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearError");
	var _showErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showErrors");
	var _showError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showError");
	var _renderNextTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNextTask");
	var _renderTaskFields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTaskFields");
	var _showConfirmDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showConfirmDialog");
	class WorkflowInfo {
	  constructor(options) {
	    Object.defineProperty(this, _showConfirmDialog, {
	      value: _showConfirmDialog2
	    });
	    Object.defineProperty(this, _renderTaskFields, {
	      value: _renderTaskFields2
	    });
	    Object.defineProperty(this, _renderNextTask, {
	      value: _renderNextTask2
	    });
	    Object.defineProperty(this, _showError, {
	      value: _showError2
	    });
	    Object.defineProperty(this, _showErrors, {
	      value: _showErrors2
	    });
	    Object.defineProperty(this, _clearError, {
	      value: _clearError2
	    });
	    Object.defineProperty(this, _sendMarkAsRead, {
	      value: _sendMarkAsRead2
	    });
	    Object.defineProperty(this, _delegateTask, {
	      value: _delegateTask2
	    });
	    Object.defineProperty(this, _handleDelegateButtonClick, {
	      value: _handleDelegateButtonClick2
	    });
	    Object.defineProperty(this, _prepareErrors, {
	      value: _prepareErrors2
	    });
	    Object.defineProperty(this, _getNextTaskOrClose, {
	      value: _getNextTaskOrClose2
	    });
	    Object.defineProperty(this, _getRequiredFields, {
	      value: _getRequiredFields2
	    });
	    Object.defineProperty(this, _isNeedValidate, {
	      value: _isNeedValidate2
	    });
	    Object.defineProperty(this, _handleTaskButtonClick, {
	      value: _handleTaskButtonClick2
	    });
	    Object.defineProperty(this, _renderButtons, {
	      value: _renderButtons2
	    });
	    Object.defineProperty(this, _isChanged, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _messageBox, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canClose, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _workflowResult, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _canUseHumanResources, {
	      writable: true,
	      value: void 0
	    });
	    this.currentUserId = options.currentUserId;
	    this.workflowId = options.workflowId;
	    this.taskId = options.taskId;
	    this.taskUserId = options.taskUserId;
	    this.taskButtons = options.taskButtons;
	    this.taskForm = options.taskForm;
	    this.taskFields = options.taskFields;
	    this.taskName = options.taskName;
	    this.buttonsPanel = options.buttonsPanel;
	    this.workflowContent = options.workflowContent;
	    this.canDelegateTask = options.canDelegateTask;
	    this.fastClose = options.fastClose;
	    this.saveVariables = options.saveVariables;
	    babelHelpers.classPrivateFieldLooseBase(this, _canUseHumanResources)[_canUseHumanResources] = main_core.Text.toBoolean(options.canUseHumanResources);
	    this.handleMarkAsRead = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _sendMarkAsRead)[_sendMarkAsRead], 100, this);
	    babelHelpers.classPrivateFieldLooseBase(this, _workflowResult)[_workflowResult] = main_core.Type.isNil(options.workflowResult) ? null : options.workflowResult;
	  }
	  init() {
	    if (this.buttonsPanel) {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderButtons)[_renderButtons]();
	    }
	    this.handleMarkAsRead();
	    main_core_events.EventEmitter.subscribe('OnUCCommentWasRead', event => {
	      const [xmlId] = event.getData();
	      if (xmlId === `WF_${this.workflowId}`) {
	        this.handleMarkAsRead();
	      }
	    });
	    if (this.taskForm) {
	      main_core.Event.bind(this.taskForm, 'change', () => {
	        babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	      });
	      main_core.Event.bind(this.taskForm, 'input', event => {
	        const target = event.target;
	        if (target.matches('input, textarea, select')) {
	          const formRow = target.closest('.ui-form-content');
	          if (formRow) {
	            babelHelpers.classPrivateFieldLooseBase(this, _clearError)[_clearError](formRow);
	          }
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	      });
	      this.taskForm.querySelectorAll('.ui-form-content').forEach(row => {
	        main_core.Event.bind(row, 'click', event => {
	          const target = event.currentTarget;
	          babelHelpers.classPrivateFieldLooseBase(this, _clearError)[_clearError](target);
	        });
	      });
	      main_core_events.EventEmitter.subscribe('BX.UI.Selector:onChange', event => {
	        const box = BX(`crm-${event.data[0].selectorId}-box`);
	        const formRow = box.closest('.ui-form-content');
	        if (formRow) {
	          babelHelpers.classPrivateFieldLooseBase(this, _clearError)[_clearError](formRow);
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.UI.EntitySelector.Dialog:Item:onSelect', event => {
	        if (event.target.context === 'BIZPROC') {
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.UI.EntitySelector.Dialog:Item:onDeselect', event => {
	        if (event.target.context === 'BIZPROC') {
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('OnIframeKeyup', event => {
	        const box = event.target.dom.cont;
	        const formRow = box.closest('.ui-form-content');
	        if (formRow) {
	          babelHelpers.classPrivateFieldLooseBase(this, _clearError)[_clearError](formRow);
	        }
	      });
	      main_core_events.EventEmitter.subscribe('OnContentChanged', event => {
	        if (event.target.dom.cont.closest('.ui-form-content')) {
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.Disk.Uploader.Integration:Item:onAdd', event => {
	        if (event.target.getUploader().getHiddenFieldsContainer().closest('.ui-form-content')) {
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.Disk.Uploader.Integration:Item:onRemove', event => {
	        if (event.target.getUploader().getHiddenFieldsContainer().closest('.ui-form-content')) {
	          babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = true;
	        }
	      });
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', event => {
	        if (event.getTarget().getWindow() === window && babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] && !babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose]) {
	          var _babelHelpers$classPr;
	          event.getCompatData()[0].denyAction();
	          if (!((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox]) != null && _babelHelpers$classPr.getPopupWindow().isShown())) {
	            babelHelpers.classPrivateFieldLooseBase(this, _showConfirmDialog)[_showConfirmDialog]();
	          }
	        }
	      });
	    }
	    const desc = this.workflowContent.querySelector('.bp-workflow-info__desc-inner');
	    if (desc) {
	      BX.UI.Hint.init(desc);
	    }
	    const resultNode = this.workflowContent.querySelector('[data-role="bp-workflow-result"]');
	    if (resultNode && babelHelpers.classPrivateFieldLooseBase(this, _workflowResult)[_workflowResult]) {
	      main_core.Runtime.loadExtension('bizproc.workflow.result').then(exports => {
	        if (exports != null && exports.WorkflowResult) {
	          new exports.WorkflowResult(babelHelpers.classPrivateFieldLooseBase(this, _workflowResult)[_workflowResult]).renderTo(resultNode);
	        }
	      }).catch(() => {});
	    }
	  }
	}
	function _renderButtons2() {
	  if (this.taskButtons) {
	    main_core.Dom.clean(this.buttonsPanel);
	    this.taskButtons.forEach(taskButton => {
	      const targetStatus = new bizproc_task.UserStatus(taskButton.TARGET_USER_STATUS);
	      const isDecline = targetStatus.isNo() || targetStatus.isCancel();
	      const button = new ui_buttons.Button({
	        color: isDecline ? ui_buttons.ButtonColor.LIGHT_BORDER : ui_buttons.ButtonColor.SUCCESS,
	        // icon: isDecline ? ButtonIcon.CANCEL : ButtonIcon.DONE,
	        round: true,
	        size: ui_buttons.ButtonSize.MEDIUM,
	        // noCaps: true,
	        text: taskButton.TEXT,
	        onclick: btn => babelHelpers.classPrivateFieldLooseBase(this, _handleTaskButtonClick)[_handleTaskButtonClick](taskButton, btn)
	      });
	      main_core.Dom.style(button.getContainer(), 'minWidth', '160px');
	      main_core.Dom.style(button.getContainer(), 'maxWidth', '200px');
	      main_core.Dom.attr(button.getContainer(), 'title', taskButton.TEXT);
	      main_core.Dom.append(button.getContainer(), this.buttonsPanel);
	    });
	  }
	  if (this.canDelegateTask) {
	    const button = new ui_buttons.Button({
	      color: ui_buttons.ButtonColor.LINK,
	      size: ui_buttons.ButtonSize.MEDIUM,
	      // noCaps: true,
	      text: main_core.Loc.getMessage('BPWFI_SLIDER_BUTTON_DELEGATE'),
	      onclick: btn => babelHelpers.classPrivateFieldLooseBase(this, _handleDelegateButtonClick)[_handleDelegateButtonClick](btn)
	    });
	    main_core.Dom.style(button.getContainer(), 'minWidth', '160px');
	    main_core.Dom.style(button.getContainer(), 'maxWidth', '200px');
	    main_core.Dom.append(button.getContainer(), this.buttonsPanel);
	  }
	}
	function _handleTaskButtonClick2(taskButton, uiButton) {
	  const formData = new FormData(this.taskForm);
	  const errors = babelHelpers.classPrivateFieldLooseBase(this, _isNeedValidate)[_isNeedValidate](taskButton.NAME) ? ValidateHelper.checkRequiredFieldsFilled(formData, babelHelpers.classPrivateFieldLooseBase(this, _getRequiredFields)[_getRequiredFields]()) : [];
	  if (main_core.Type.isArrayFilled(errors)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](errors);
	    return;
	  }
	  formData.append('taskId', this.taskId);
	  formData.append('workflowId', this.workflowId);
	  formData.append('taskName', this.taskName);
	  formData.append(taskButton.NAME, taskButton.VALUE);
	  const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	  uiButton.setDisabled(true);
	  if (this.fastClose) {
	    babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	    slider == null ? void 0 : slider.close();
	    slider.setCacheable(true);
	  }
	  doTaskAction(formData, slider, this.fastClose).then(() => {
	    slider == null ? void 0 : slider.setCacheable(false);
	    main_core.Dom.addClass(this.workflowContent, 'fade-out');
	    babelHelpers.classPrivateFieldLooseBase(this, _getNextTaskOrClose)[_getNextTaskOrClose](formData);
	  }).catch(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](babelHelpers.classPrivateFieldLooseBase(this, _prepareErrors)[_prepareErrors](response.errors));
	  }).finally(() => uiButton.setDisabled(false));
	}
	function _isNeedValidate2(buttonName) {
	  return !(buttonName === 'cancel' && !this.saveVariables);
	}
	function _getRequiredFields2() {
	  if (main_core.Type.isNil(this.taskFields)) {
	    return [];
	  }
	  return this.taskFields.filter(field => field.Required);
	}
	function _getNextTaskOrClose2(formData) {
	  main_core.ajax.runAction('bizproc.task.getUserTaskByWorkflowId', {
	    data: formData
	  }).then(res => {
	    if (BX.type.isArray(res.data.additionalParams) && res.data.additionalParams.length === 0) {
	      var _BX$SidePanel$Instanc;
	      babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	      (_BX$SidePanel$Instanc = BX.SidePanel.Instance.getSliderByWindow(window)) == null ? void 0 : _BX$SidePanel$Instanc.close();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderNextTask)[_renderNextTask](res.data);
	    }
	  }).catch(response => {
	    main_core.Dom.toggleClass(this.workflowContent, 'fade-out fade-in');
	    ui_dialogs_messagebox.MessageBox.alert(response.errors.pop().message);
	  });
	}
	function _prepareErrors2(responseErrors) {
	  const errors = [];
	  for (const error of responseErrors) {
	    var _error$customData;
	    errors.push({
	      fieldId: (_error$customData = error.customData) != null ? _error$customData : null,
	      message: error.message
	    });
	  }
	  return errors;
	}
	function _handleDelegateButtonClick2(uiButton) {
	  uiButton.setDisabled(true);
	  main_core.Runtime.loadExtension('ui.entity-selector').then(exports => {
	    const {
	      Dialog
	    } = exports;
	    uiButton.setDisabled(false);
	    const dialog = new Dialog({
	      targetNode: uiButton.getContainer(),
	      context: 'bp-task-delegation',
	      entities: [{
	        id: 'user',
	        options: {
	          intranetUsersOnly: true,
	          emailUsers: false,
	          inviteEmployeeLink: false,
	          inviteGuestLink: false
	        }
	      }, {
	        id: babelHelpers.classPrivateFieldLooseBase(this, _canUseHumanResources)[_canUseHumanResources] ? 'structure-node' : 'department',
	        options: {
	          selectMode: 'usersOnly'
	        }
	      }],
	      popupOptions: {
	        bindOptions: {
	          forceBindPosition: true
	        }
	      },
	      enableSearch: true,
	      events: {
	        'Item:onSelect': event => {
	          const item = event.getData().item;
	          babelHelpers.classPrivateFieldLooseBase(this, _delegateTask)[_delegateTask](item.getId());
	        },
	        onHide: event => {
	          event.getTarget().destroy();
	        }
	      },
	      hideOnSelect: true,
	      offsetTop: 3,
	      clearUnavailableItems: true,
	      multiple: false
	    });
	    dialog.show();
	  }).catch(e => {
	    console.error(e);
	    uiButton.setDisabled(false);
	  });
	}
	function _delegateTask2(toUserId) {
	  const actionData = {
	    taskIds: [this.taskId],
	    fromUserId: this.taskUserId || this.currentUserId,
	    toUserId
	  };
	  main_core.ajax.runAction('bizproc.task.delegate', {
	    data: actionData
	  }).then(response => {
	    var _BX$SidePanel$Instanc2;
	    babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	    (_BX$SidePanel$Instanc2 = BX.SidePanel.Instance.getSliderByWindow(window)) == null ? void 0 : _BX$SidePanel$Instanc2.close();
	  }).catch(response => {
	    ui_dialogs_messagebox.MessageBox.alert(response.errors.pop().message);
	  });
	}
	function _sendMarkAsRead2() {
	  main_core.ajax.runAction('bizproc.workflow.comment.markAsRead', {
	    data: {
	      workflowId: this.workflowId,
	      userId: this.currentUserId
	    }
	  });
	}
	function _clearError2(target) {
	  const errorContainer = target.querySelector('.ui-form-notice');
	  if (errorContainer) {
	    BX.Dom.remove(errorContainer);
	  }
	}
	function _showErrors2(errors) {
	  if (BX.type.isArray(errors)) {
	    const popupErrors = [];
	    errors.forEach(error => {
	      const fieldName = error.fieldId;
	      if (this.taskForm && fieldName) {
	        babelHelpers.classPrivateFieldLooseBase(this, _showError)[_showError](error.message, fieldName);
	      } else {
	        popupErrors.push(error.message);
	      }
	    });
	    if (popupErrors.length > 0) {
	      ui_dialogs_messagebox.MessageBox.alert(popupErrors.join(', '));
	    }
	  }
	}
	function _showError2(message, id) {
	  const field = this.taskForm.querySelector(`[data-cid="${id}"]`);
	  if (!field) {
	    return;
	  }
	  const parentContainer = field.querySelector('.ui-form-content');
	  let errorContainer = field.querySelector('.ui-form-notice');
	  if (!errorContainer) {
	    errorContainer = BX.Dom.create('div', {
	      attrs: {
	        className: 'ui-form-notice'
	      }
	    });
	    errorContainer.innerText = message;
	    if (parentContainer) {
	      BX.Dom.append(errorContainer, parentContainer);
	    }
	  }
	}
	function _renderNextTask2(data) {
	  babelHelpers.classPrivateFieldLooseBase(this, _isChanged)[_isChanged] = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _renderTaskFields)[_renderTaskFields](data);
	  if (data.additionalParams) {
	    this.taskId = data.additionalParams.ID;
	    this.fastClose = data.additionalParams.IS_LAST_TASK_FOR_USER;
	    this.saveVariables = data.additionalParams.saveVariables;
	    this.taskFields = data.additionalParams.FIELDS;
	    const subject = this.workflowContent.querySelector('.bp-workflow-info__subject');
	    if (subject) {
	      subject.innerText = data.additionalParams.NAME;
	    }
	    const desc = this.workflowContent.querySelector('.bp-workflow-info__desc-inner');
	    if (desc) {
	      const descWrap = desc.closest('.bp-workflow-info__tabs-block');
	      if (data.additionalParams.DESCRIPTION.length > 0) {
	        main_core.Dom.removeClass(descWrap, 'block-hidden');
	      } else {
	        main_core.Dom.addClass(descWrap, 'block-hidden');
	      }
	      desc.innerHTML = data.additionalParams.DESCRIPTION;
	      BX.UI.Hint.init(desc);
	    }
	    const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	    if (slider) {
	      const currentUrl = slider.getUrl();
	      const newUrl = currentUrl.replace(/\/bizproc\/\d+\//, `/bizproc/${this.taskId}/`);
	      slider.setUrl(newUrl);
	      top.history.replaceState({}, '', newUrl);
	    }
	  }
	  if (data.additionalParams && data.additionalParams.BUTTONS) {
	    this.taskButtons = data.additionalParams.BUTTONS;
	  }
	  this.init();
	  main_core.Dom.removeClass(this.workflowContent, 'fade-out');
	  main_core.Dom.addClass(this.workflowContent, 'fade-in');
	  main_core.Event.bindOnce(this.workflowContent, 'animationend', () => {
	    main_core.Dom.removeClass(this.workflowContent, 'fade-in');
	  });
	}
	function _renderTaskFields2(data) {
	  const taskFields = this.workflowContent.querySelector('.bp-workflow-info__editor');
	  if (BX.type.isArray(data.html) && data.html.length > 0) {
	    main_core.Dom.removeClass(taskFields, 'block-hidden');
	    main_core.Dom.clean(this.taskForm);
	    data.html.forEach((renderedControl, controlId) => {
	      var _data$additionalParam, _data$additionalParam2;
	      const fieldData = (_data$additionalParam = data.additionalParams) == null ? void 0 : (_data$additionalParam2 = _data$additionalParam.FIELDS) == null ? void 0 : _data$additionalParam2[controlId];
	      if (fieldData) {
	        const labelClass = fieldData.Required ? 'ui-form-label --required' : 'ui-form-label';
	        const node = main_core.Tag.render(_t || (_t = _`
						<div class="ui-form-row" data-cid="${0}">
							<div class="${0}">
								<div class="ui-ctl-label-text">${0}</div>
							</div>
							<div class="ui-form-content"></div>
						</div>
					`), main_core.Text.encode(fieldData.Id), labelClass, main_core.Text.encode(fieldData.Name));
	        BX.Runtime.html(node.querySelector('.ui-form-content'), renderedControl);
	        this.taskForm.append(node);
	      }
	    });
	  } else {
	    main_core.Dom.addClass(taskFields, 'block-hidden');
	  }
	}
	function _showConfirmDialog2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox] = ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BPWFI_SLIDER_CONFIRM_DESCRIPTION'), main_core.Loc.getMessage('BPWFI_SLIDER_CONFIRM_TITLE'), () => {
	    var _BX$SidePanel$Instanc3;
	    babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	    (_BX$SidePanel$Instanc3 = BX.SidePanel.Instance.getSliderByWindow(window)) == null ? void 0 : _BX$SidePanel$Instanc3.close();
	  }, main_core.Loc.getMessage('BPWFI_SLIDER_CONFIRM_ACCEPT'), () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox].close();
	    babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox] = null;
	  }, main_core.Loc.getMessage('BPWFI_SLIDER_CONFIRM_CANCEL'));
	}

	exports.WorkflowInfo = WorkflowInfo;

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX.Event,BX.UI,BX.Bizproc,BX.UI.Dialogs,BX));
//# sourceMappingURL=script.js.map
