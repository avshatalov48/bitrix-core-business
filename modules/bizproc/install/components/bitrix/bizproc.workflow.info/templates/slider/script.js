/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,main_core_events,ui_buttons,bizproc_task,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t;
	var _isChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChanged");
	var _messageBox = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageBox");
	var _canClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canClose");
	var _renderButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderButtons");
	var _handleTaskButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTaskButtonClick");
	var _handleDelegateButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDelegateButtonClick");
	var _delegateTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delegateTask");
	var _sendMarkAsRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMarkAsRead");
	var _clearError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearError");
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
	    this.currentUserId = options.currentUserId;
	    this.workflowId = options.workflowId;
	    this.taskId = options.taskId;
	    this.taskUserId = options.taskUserId;
	    this.taskButtons = options.taskButtons;
	    this.taskForm = options.taskForm;
	    this.buttonsPanel = options.buttonsPanel;
	    this.workflowContent = options.workflowContent;
	    this.canDelegateTask = options.canDelegateTask;
	    this.handleMarkAsRead = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _sendMarkAsRead)[_sendMarkAsRead], 100, this);
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
	  formData.append('taskId', this.taskId);
	  formData.append('workflowId', this.workflowId);
	  formData.append(taskButton.NAME, taskButton.VALUE);
	  uiButton.setDisabled(true);
	  main_core.ajax.runAction('bizproc.task.do', {
	    data: formData
	  }).then(() => {
	    uiButton.setDisabled(false);
	    main_core.Dom.addClass(this.workflowContent, 'fade-out');
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
	  }).catch(response => {
	    if (BX.type.isArray(response.errors)) {
	      const popupErrors = [];
	      response.errors.forEach(error => {
	        const fieldName = error.customData;
	        if (this.taskForm && fieldName) {
	          const field = this.taskForm.querySelector(`[data-cid="${fieldName}"]`);
	          if (field) {
	            babelHelpers.classPrivateFieldLooseBase(this, _showError)[_showError](error, field);
	          }
	        } else {
	          popupErrors.push(error.message);
	        }
	      });
	      if (popupErrors.length > 0) {
	        ui_dialogs_messagebox.MessageBox.alert(popupErrors.join(', '));
	      }
	    }
	    uiButton.setDisabled(false);
	  });
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
	        id: 'department',
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
	function _showError2(error, field) {
	  const parentContainer = field.querySelector('.ui-form-content');
	  let errorContainer = field.querySelector('.ui-form-notice');
	  if (!errorContainer) {
	    errorContainer = BX.Dom.create('div', {
	      attrs: {
	        className: 'ui-form-notice'
	      }
	    });
	    errorContainer.innerText = error.message;
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

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX,BX.Event,BX.UI,BX.Bizproc,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
