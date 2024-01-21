/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_designTokens,ui_progressbar,main_core_events,main_popup,ui_alerts,ui_buttons,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */
	const ProcessResultStatus = {
	  progress: 'PROGRESS',
	  completed: 'COMPLETED'
	};
	const ProcessState = {
	  intermediate: 'INTERMEDIATE',
	  running: 'RUNNING',
	  completed: 'COMPLETED',
	  stopped: 'STOPPED',
	  error: 'ERROR',
	  canceling: 'CANCELING'
	};

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	class BaseField {
	  constructor(options) {
	    this.obligatory = false;
	    this.emptyMessage = '';
	    this.className = '';
	    this.disabled = false;
	    this.value = null;
	    this.id = 'id' in options ? options.id : 'ProcessDialogField_' + Math.random().toString().substring(2);
	    this.name = options.name;
	    this.type = options.type;
	    this.title = options.title;
	    this.obligatory = !!options.obligatory;
	    if ('value' in options) {
	      this.setValue(options.value);
	    }
	    if ('emptyMessage' in options) {
	      this.emptyMessage = options.emptyMessage;
	    } else {
	      this.emptyMessage = main_core.Loc.getMessage('UI_STEP_PROCESSING_EMPTY_ERROR') || '';
	    }
	  }
	  setValue(value) {
	    throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
	    //this.value = value;
	    //return this;
	  }

	  getValue() {
	    throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
	    //return this.value;
	  }

	  render() {
	    throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
	    //return this.field;
	  }

	  lock(flag = true) {
	    throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
	    //this.disabled = flag;
	    //this.field.disabled = !!flag;
	    //return this;
	  }

	  isFilled() {
	    throw new Error('BX.UI.StepProcessing: Must be implemented by a subclass');
	    //return this.field;
	  }

	  getInput() {
	    return this.field ? this.field : null;
	  }
	  getContainer() {
	    if (!this.container) {
	      this.container = main_core.Tag.render(_t || (_t = _`<div class="${0} ${0}"></div>`), DialogStyle.ProcessOptionContainer, this.className);
	      this.container.appendChild(main_core.Tag.render(_t2 || (_t2 = _`<div class="${0}"></div>`), DialogStyle.ProcessOptionsTitle)).appendChild(main_core.Tag.render(_t3 || (_t3 = _`<label for="${0}_inp">${0}</label>`), this.id, this.title));
	      this.container.appendChild(main_core.Tag.render(_t4 || (_t4 = _`<div class="${0}"></div>`), DialogStyle.ProcessOptionsInput)).appendChild(this.render());
	      if (this.obligatory) {
	        const alertId = this.id + '_alert';
	        this.container.appendChild(main_core.Tag.render(_t5 || (_t5 = _`<div id="${0}" class="${0}" style="display:none"></div>`), alertId, DialogStyle.ProcessOptionsObligatory)).appendChild(main_core.Tag.render(_t6 || (_t6 = _`<span class="ui-alert-message">${0}</span>`), this.emptyMessage));
	      }
	    }
	    return this.container;
	  }
	  showWarning(message) {
	    const alertId = this.id + '_alert';
	    const optionElement = this.container.querySelector('#' + alertId);
	    if (optionElement) {
	      if (main_core.Type.isStringFilled(message)) {
	        const messageElement = optionElement.querySelector('.ui-alert-message');
	        messageElement.innerHTML = message;
	      }
	      optionElement.style.display = 'block';
	    } else {
	      const message = message ? message : this.emptyMessage;
	      if (main_core.Type.isStringFilled(message)) {
	        this.container.appendChild(main_core.Tag.render(_t7 || (_t7 = _`<div id="${0}" class="${0}"></div>`), alertId, DialogStyle.ProcessOptionsObligatory)).appendChild(main_core.Tag.render(_t8 || (_t8 = _`<span class="ui-alert-message">${0}</span>`), message));
	      }
	    }
	    return this;
	  }
	  hideWarning() {
	    const alertId = this.id + '_alert';
	    const optionElement = this.container.querySelector('#' + alertId);
	    if (optionElement) {
	      optionElement.style.display = 'none';
	    }
	    return this;
	  }
	}

	let _$1 = t => t,
	  _t$1;
	class TextField extends BaseField {
	  constructor(options) {
	    super(options);
	    this.type = 'text';
	    this.className = DialogStyle.ProcessOptionText;
	    this.rows = 10;
	    this.cols = 50;
	    if (options.textSize) {
	      this.cols = options.textSize;
	    }
	    if (options.textLine) {
	      this.rows = options.textLine;
	    }
	  }
	  setValue(value) {
	    this.value = value;
	    if (this.field) {
	      this.field.value = this.value;
	    }
	    return this;
	  }
	  getValue() {
	    if (this.field && this.disabled !== true) {
	      if (typeof this.field.value !== 'undefined') {
	        this.value = this.field.value;
	      }
	    }
	    return this.value;
	  }
	  isFilled() {
	    if (this.field) {
	      if (typeof this.field.value !== 'undefined') {
	        return main_core.Type.isStringFilled(this.field.value);
	      }
	    }
	    return false;
	  }
	  render() {
	    if (!this.field) {
	      this.field = main_core.Tag.render(_t$1 || (_t$1 = _$1`<textarea id="${0}" name="${0}" cols="${0}" rows="${0}"></textarea>`), this.id, this.name, this.cols, this.rows);
	    }
	    if (this.value) {
	      this.field.value = this.value;
	    }
	    return this.field;
	  }
	  lock(flag = true) {
	    this.disabled = flag;
	    this.field.disabled = !!flag;
	    return this;
	  }
	}

	let _$2 = t => t,
	  _t$2;
	class FileField extends BaseField {
	  constructor(options) {
	    if (!('emptyMessage' in options)) {
	      options.emptyMessage = main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_EMPTY_ERROR');
	    }
	    super(options);
	    this.type = 'file';
	    this.className = DialogStyle.ProcessOptionFile;
	  }
	  setValue(value) {
	    this.value = value;
	    if (this.field) {
	      if (value instanceof FileList) {
	        this.field.files = value;
	      } else if (value instanceof File) {
	        this.field.files[0] = value;
	      }
	    }
	    return this;
	  }
	  getValue() {
	    if (this.field && this.disabled !== true) {
	      if (typeof this.field.files[0] != "undefined") {
	        this.value = this.field.files[0];
	      }
	    }
	    return this.value;
	  }
	  isFilled() {
	    if (this.field) {
	      if (typeof this.field.files[0] != "undefined") {
	        return true;
	      }
	    }
	    return false;
	  }
	  render() {
	    if (!this.field) {
	      this.field = main_core.Tag.render(_t$2 || (_t$2 = _$2`<input type="file" id="${0}" name="${0}">`), this.id, this.name);
	    }
	    return this.field;
	  }
	  lock(flag = true) {
	    this.disabled = flag;
	    this.field.disabled = !!flag;
	    return this;
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1;
	class CheckboxField extends BaseField {
	  constructor(options) {
	    super(options);
	    this.type = 'checkbox';
	    this.list = [];
	    this.multiple = false;
	    this.className = DialogStyle.ProcessOptionCheckbox;
	    if ('list' in options) {
	      this.list = options.list;
	    }
	    this.multiple = this.list.length > 1;
	    if (this.multiple) {
	      this.class = DialogStyle.ProcessOptionMultiple;
	    }
	  }
	  setValue(value) {
	    if (this.multiple) {
	      this.value = main_core.Type.isArray(value) ? value : [value];
	    } else {
	      if (value === 'Y' || value === 'N' || value === null || value === undefined) {
	        value = value === 'Y'; //Boolean
	      }

	      this.value = value;
	    }
	    if (this.field) {
	      if (this.multiple) {
	        const optionElements = this.field.querySelectorAll("input[type=checkbox]");
	        if (optionElements) {
	          for (let k = 0; k < optionElements.length; k++) {
	            optionElements[k].checked = this.value.indexOf(optionElements[k].value) !== -1;
	          }
	        }
	      } else {
	        const optionElement = this.field.querySelector("input[type=checkbox]");
	        if (optionElement) {
	          optionElement.checked = main_core.Type.isBoolean(this.value) ? this.value : optionElement.value === this.value;
	        }
	      }
	    }
	    return this;
	  }
	  getValue() {
	    if (this.field && this.disabled !== true) {
	      if (this.multiple) {
	        this.value = [];
	        const optionElements = this.field.querySelectorAll("input[type=checkbox]");
	        if (optionElements) {
	          for (let k = 0; k < optionElements.length; k++) {
	            if (optionElements[k].checked) {
	              this.value.push(optionElements[k].value);
	            }
	          }
	        }
	      } else {
	        const optionElement = this.field.querySelector("input[type=checkbox]");
	        if (optionElement) {
	          if (optionElement.value && optionElement.value !== 'Y') {
	            this.value = optionElement.checked ? optionElement.value : '';
	          } else {
	            this.value = optionElement.checked;
	          }
	        }
	      }
	    }
	    return this.value;
	  }
	  isFilled() {
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=checkbox]");
	      if (optionElements) {
	        return true;
	      }
	    }
	    return false;
	  }
	  getInput() {
	    if (this.field) {
	      if (this.multiple) {
	        const optionElements = this.field.querySelectorAll("input[type=checkbox]");
	        if (optionElements) {
	          return optionElements;
	        }
	      } else {
	        const optionElement = this.field.querySelector("input[type=checkbox]");
	        if (optionElement) {
	          return optionElement;
	        }
	      }
	    }
	    return null;
	  }
	  render() {
	    if (!this.field) {
	      this.field = main_core.Tag.render(_t$3 || (_t$3 = _$3`<div id="${0}"></div>`), this.id);
	    }
	    if (this.multiple) {
	      Object.keys(this.list).forEach(itemId => {
	        if (this.value.indexOf(itemId) !== -1) {
	          this.field.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$3`<label><input type="checkbox" name="${0}[]" value="${0}" checked>${0}</label>`), this.name, itemId, this.list[itemId]));
	        } else {
	          this.field.appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$3`<label><input type="checkbox" name="${0}[]" value="${0}">${0}</label>`), this.name, itemId, this.list[itemId]));
	        }
	      });
	    } else {
	      if (main_core.Type.isBoolean(this.value)) {
	        if (this.value) {
	          this.field.appendChild(main_core.Tag.render(_t4$1 || (_t4$1 = _$3`<input type="checkbox" id="${0}_inp" name="${0}" value="Y" checked>`), this.id, this.name));
	        } else {
	          this.field.appendChild(main_core.Tag.render(_t5$1 || (_t5$1 = _$3`<input type="checkbox" id="${0}_inp" name="${0}" value="Y">`), this.id, this.name));
	        }
	      } else {
	        if (this.value !== '') {
	          this.field.appendChild(main_core.Tag.render(_t6$1 || (_t6$1 = _$3`<input type="checkbox" id="${0}_inp" name="${0}" value="${0}" checked>`), this.id, this.name, this.value));
	        } else {
	          this.field.appendChild(main_core.Tag.render(_t7$1 || (_t7$1 = _$3`<input type="checkbox" id="${0}_inp" name="${0}" value="${0}>"`), this.id, this.name, this.value));
	        }
	      }
	    }
	    return this.field;
	  }
	  lock(flag = true) {
	    this.disabled = flag;
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=checkbox]");
	      if (optionElements) {
	        for (let k = 0; k < optionElements.length; k++) {
	          optionElements[k].disabled = !!flag;
	        }
	      }
	    }
	    return this;
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2;
	class SelectField extends BaseField {
	  constructor(options) {
	    super(options);
	    this.type = 'select';
	    this.multiple = false;
	    this.list = [];
	    this.className = DialogStyle.ProcessOptionSelect;
	    if ('multiple' in options) {
	      this.multiple = main_core.Type.isBoolean(options.multiple) ? options.multiple === true : options.multiple === 'Y';
	    }
	    if (this.multiple) {
	      if ('size' in options) {
	        this.size = options.size;
	      }
	    }
	    if ('list' in options) {
	      this.list = options.list;
	    }
	  }
	  setValue(value) {
	    if (this.multiple) {
	      this.value = main_core.Type.isArray(value) ? value : [value];
	    } else {
	      this.value = value;
	    }
	    if (this.field) {
	      if (this.multiple) {
	        for (let k = 0; k < this.field.options.length; k++) {
	          this.field.options[k].selected = this.value.indexOf(this.field.options[k].value) !== -1;
	        }
	      } else {
	        this.field.value = this.value;
	      }
	    }
	    return this;
	  }
	  getValue() {
	    if (this.field && this.disabled !== true) {
	      if (this.multiple) {
	        this.value = [];
	        for (let k = 0; k < this.field.options.length; k++) {
	          if (this.field.options[k].selected) {
	            this.value.push(this.field.options[k].value);
	          }
	        }
	      } else {
	        this.value = this.field.value;
	      }
	    }
	    return this.value;
	  }
	  isFilled() {
	    if (this.field) {
	      for (let k = 0; k < this.field.options.length; k++) {
	        if (this.field.options[k].selected) {
	          return true;
	        }
	      }
	    }
	    return false;
	  }
	  render() {
	    if (!this.field) {
	      this.field = main_core.Tag.render(_t$4 || (_t$4 = _$4`<select id="${0}" name="${0}"></select>`), this.id, this.name);
	    }
	    if (this.multiple) {
	      this.field.multiple = 'multiple';
	      if (this.size) {
	        this.field.size = this.size;
	      }
	    }
	    Object.keys(this.list).forEach(itemId => {
	      let selected;
	      if (this.multiple === true) {
	        selected = this.value.indexOf(itemId) !== -1;
	      } else {
	        selected = itemId === this.value;
	      }
	      let option = this.field.appendChild(main_core.Tag.render(_t2$2 || (_t2$2 = _$4`<option value="${0}">${0}</option>`), itemId, this.list[itemId]));
	      if (selected) {
	        option.selected = 'selected';
	      }
	    });
	    return this.field;
	  }
	  lock(flag = true) {
	    this.disabled = flag;
	    this.field.disabled = !!flag;
	    return this;
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$2;
	class RadioField extends BaseField {
	  constructor(options) {
	    super(options);
	    this.type = 'radio';
	    this.list = [];
	    this.className = DialogStyle.ProcessOptionMultiple;
	    if ('list' in options) {
	      this.list = options.list;
	    }
	  }
	  setValue(value) {
	    this.value = value;
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=radio]");
	      if (optionElements) {
	        for (let k = 0; k < optionElements.length; k++) {
	          optionElements[k].checked = optionElements[k].value === this.value;
	        }
	      }
	    }
	    return this;
	  }
	  getValue() {
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=radio]");
	      if (optionElements) {
	        for (let k = 0; k < optionElements.length; k++) {
	          if (optionElements[k].checked) {
	            this.value = optionElements[k].value;
	            break;
	          }
	        }
	      }
	    }
	    return this.value;
	  }
	  isFilled() {
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=radio]");
	      if (optionElements) {
	        for (let k = 0; k < optionElements.length; k++) {
	          if (optionElements[k].checked) {
	            return true;
	          }
	        }
	      }
	    }
	    return false;
	  }
	  getInput() {
	    if (this.field && this.disabled !== true) {
	      const optionElement = this.field.querySelector("input[type=radio]");
	      if (optionElement) {
	        return optionElement;
	      }
	    }
	    return null;
	  }
	  render() {
	    if (!this.field) {
	      this.field = main_core.Tag.render(_t$5 || (_t$5 = _$5`<div id="${0}"></div>`), this.id);
	    }
	    Object.keys(this.list).forEach(itemId => {
	      if (itemId === this.value) {
	        this.field.appendChild(main_core.Tag.render(_t2$3 || (_t2$3 = _$5`<label><input type="radio" name="${0}" value="${0}" checked>${0}</label>`), this.name, itemId, this.list[itemId]));
	      } else {
	        this.field.appendChild(main_core.Tag.render(_t3$2 || (_t3$2 = _$5`<label><input type="radio" name="${0}" value="${0}">${0}</label>`), this.name, itemId, this.list[itemId]));
	      }
	    });
	    return this.field;
	  }
	  lock(flag = true) {
	    this.disabled = flag;
	    if (this.field) {
	      const optionElements = this.field.querySelectorAll("input[type=radio]");
	      if (optionElements) {
	        for (let k = 0; k < optionElements.length; k++) {
	          optionElements[k].disabled = !!flag;
	        }
	      }
	    }
	    return this;
	  }
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$4,
	  _t3$3,
	  _t4$2;

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */

	const DialogStyle = {
	  ProcessWindow: 'bx-stepprocessing-dialog-process',
	  ProcessPopup: 'bx-stepprocessing-dialog-process-popup',
	  ProcessSummary: 'bx-stepprocessing-dialog-process-summary',
	  ProcessProgressbar: 'bx-stepprocessing-dialog-process-progressbar',
	  ProcessOptions: 'bx-stepprocessing-dialog-process-options',
	  ProcessOptionContainer: 'bx-stepprocessing-dialog-process-option-container',
	  ProcessOptionsTitle: 'bx-stepprocessing-dialog-process-options-title',
	  ProcessOptionsInput: 'bx-stepprocessing-dialog-process-options-input',
	  ProcessOptionsObligatory: 'ui-alert ui-alert-xs ui-alert-warning',
	  ProcessOptionText: 'bx-stepprocessing-dialog-process-option-text',
	  ProcessOptionCheckbox: 'bx-stepprocessing-dialog-process-option-checkbox',
	  ProcessOptionMultiple: 'bx-stepprocessing-dialog-process-option-multiple',
	  ProcessOptionFile: 'bx-stepprocessing-dialog-process-option-file',
	  ProcessOptionSelect: 'bx-stepprocessing-dialog-process-option-select',
	  ButtonStart: 'popup-window-button-accept',
	  ButtonStop: 'popup-window-button-disable',
	  ButtonCancel: 'popup-window-button-link-cancel',
	  ButtonDownload: 'popup-window-button-link-download',
	  ButtonRemove: 'popup-window-button-link-remove'
	};
	const DialogEvent = {
	  Shown: 'BX.UI.StepProcessing.Dialog.Shown',
	  Closed: 'BX.UI.StepProcessing.Dialog.Closed',
	  Start: 'BX.UI.StepProcessing.Dialog.Start',
	  Stop: 'BX.UI.StepProcessing.Dialog.Stop'
	};

	/**
	 * UI of process dialog
	 *
	 * @namespace {BX.UI.StepProcessing}
	 * @event BX.UI.StepProcessing.Dialog.Shown
	 * @event BX.UI.StepProcessing.Dialog.Closed
	 * @event BX.UI.StepProcessing.Dialog.Start
	 * @event BX.UI.StepProcessing.Dialog.Stop
	 */
	class Dialog {
	  /**
	   * @type {DialogOptions}
	   * @private
	   */

	  /**
	   * @private
	   */

	  /**
	   * @private
	   */

	  /**
	   * @private
	   */

	  constructor(settings = {}) {
	    this.id = '';
	    this._settings = {};
	    this.isShown = false;
	    this.buttons = {};
	    this.fields = {};
	    this._messages = {};
	    this._handlers = {};
	    this.isAdminPanel = false;
	    this._settings = settings;
	    this.id = this.getSetting('id', 'ProcessDialog_' + Math.random().toString().substring(2));
	    this._messages = this.getSetting('messages', {});
	    let optionsFields = {};
	    const fields = this.getSetting('optionsFields');
	    if (main_core.Type.isArray(fields)) {
	      fields.forEach(option => {
	        if (main_core.Type.isPlainObject(option) && option.hasOwnProperty('name') && option.hasOwnProperty('type') && option.hasOwnProperty('title')) {
	          optionsFields[option.name] = option;
	        }
	      });
	    } else if (main_core.Type.isPlainObject(fields)) {
	      Object.keys(fields).forEach(optionName => {
	        let option = fields[optionName];
	        if (main_core.Type.isPlainObject(option) && option.hasOwnProperty('name') && option.hasOwnProperty('type') && option.hasOwnProperty('title')) {
	          optionsFields[option.name] = option;
	        }
	      });
	    }
	    this.setSetting('optionsFields', optionsFields);
	    const optionsFieldsValue = this.getSetting('optionsFieldsValue');
	    if (!optionsFieldsValue) {
	      this.setSetting('optionsFieldsValue', {});
	    }
	    const showButtons = this.getSetting('showButtons');
	    if (!showButtons) {
	      this.setSetting('showButtons', {
	        'start': true,
	        'stop': true,
	        'close': true
	      });
	    }
	    this._handlers = this.getSetting('handlers', {});
	  }
	  destroy() {
	    if (this.popupWindow) {
	      this.popupWindow.destroy();
	      this.popupWindow = null;
	    }
	  }
	  getId() {
	    return this.id;
	  }
	  getSetting(name, defaultVal = null) {
	    return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
	  }
	  setSetting(name, value) {
	    this._settings[name] = value;
	    return this;
	  }
	  getMessage(name) {
	    return this._messages && this._messages.hasOwnProperty(name) ? this._messages[name] : "";
	  }
	  setMessage(name, text) {
	    this._messages[name] = text;
	    return this;
	  }

	  //region Event handlers

	  setHandler(type, handler) {
	    if (typeof handler == 'function') {
	      this._handlers[type] = handler;
	    }
	    return this;
	  }
	  callHandler(type, args) {
	    if (typeof this._handlers[type] == 'function') {
	      this._handlers[type].apply(this, args);
	    }
	  }

	  //endregion

	  //region Run

	  start() {
	    this.callHandler('start');
	    main_core_events.EventEmitter.emit(DialogEvent.Start, new main_core_events.BaseEvent({
	      dialog: this
	    }));
	  }
	  stop() {
	    this.callHandler('stop');
	    main_core_events.EventEmitter.emit(DialogEvent.Stop, new main_core_events.BaseEvent({
	      dialog: this
	    }));
	  }
	  show() {
	    if (this.isShown) {
	      return;
	    }
	    const optionElement = document.querySelector('#bx-admin-prefix');
	    if (optionElement) {
	      this.isAdminPanel = true;
	    }
	    this.progressBar = new BX.UI.ProgressBar({
	      statusType: BX.UI.ProgressBar.Status.COUNTER,
	      size: this.isAdminPanel ? BX.UI.ProgressBar.Size.LARGE : BX.UI.ProgressBar.Size.MEDIUM,
	      fill: this.isAdminPanel,
	      column: !this.isAdminPanel
	    });
	    this.error = new ui_alerts.Alert({
	      color: ui_alerts.AlertColor.DANGER,
	      icon: ui_alerts.AlertIcon.DANGER,
	      size: ui_alerts.AlertSize.SMALL
	    });
	    this.warning = new ui_alerts.Alert({
	      color: ui_alerts.AlertColor.WARNING,
	      icon: ui_alerts.AlertIcon.WARNING,
	      size: ui_alerts.AlertSize.SMALL
	    });
	    this.popupWindow = main_popup.PopupManager.create({
	      id: this.getId(),
	      cacheable: false,
	      titleBar: this.getMessage("title"),
	      autoHide: false,
	      closeByEsc: false,
	      closeIcon: true,
	      content: this._prepareDialogContent(),
	      draggable: true,
	      buttons: this._prepareDialogButtons(),
	      className: DialogStyle.ProcessWindow,
	      bindOptions: {
	        forceBindPosition: false
	      },
	      events: {
	        onClose: BX.delegate(this.onDialogClose, this)
	      },
	      overlay: true,
	      resizable: true,
	      minWidth: Number.parseInt(this.getSetting('minWidth', 500)),
	      maxWidth: Number.parseInt(this.getSetting('maxWidth', 1000))
	    });
	    if (!this.popupWindow.isShown()) {
	      this.popupWindow.show();
	    }
	    this.isShown = this.popupWindow.isShown();
	    if (this.isShown) {
	      this.callHandler('dialogShown');
	      main_core_events.EventEmitter.emit(DialogEvent.Shown, new main_core_events.BaseEvent({
	        dialog: this
	      }));
	    }
	    return this;
	  }
	  close() {
	    if (!this.isShown) {
	      return;
	    }
	    if (this.popupWindow) {
	      this.popupWindow.close();
	    }
	    this.isShown = false;
	    this.callHandler('dialogClosed');
	    main_core_events.EventEmitter.emit(DialogEvent.Closed, new main_core_events.BaseEvent({
	      dialog: this
	    }));
	    return this;
	  }

	  // endregion

	  //region Dialog

	  /**
	   * @private
	   */
	  _prepareDialogContent() {
	    this.summaryBlock = main_core.Tag.render(_t$6 || (_t$6 = _$6`<div class="${0}">${0}</div>`), DialogStyle.ProcessSummary, this.getMessage('summary'));
	    this.errorBlock = this.error.getContainer();
	    this.warningBlock = this.warning.getContainer();
	    this.errorBlock.style.display = 'none';
	    this.warningBlock.style.display = 'none';
	    if (this.progressBar) {
	      this.progressBarBlock = main_core.Tag.render(_t2$4 || (_t2$4 = _$6`<div class="${0}" style="display:none"></div>`), DialogStyle.ProcessProgressbar);
	      this.progressBarBlock.appendChild(this.progressBar.getContainer());
	    }
	    if (!this.optionsFieldsBlock) {
	      this.optionsFieldsBlock = main_core.Tag.render(_t3$3 || (_t3$3 = _$6`<div class="${0}" style="display:none"></div>`), DialogStyle.ProcessOptions);
	    } else {
	      main_core.Dom.clean(this.optionsFieldsBlock);
	    }
	    let optionsFields = this.getSetting('optionsFields', {});
	    let optionsFieldsValue = this.getSetting('optionsFieldsValue', {});
	    Object.keys(optionsFields).forEach(optionName => {
	      let optionValue = optionsFieldsValue[optionName] ? optionsFieldsValue[optionName] : null;
	      let optionBlock = this._renderOption(optionsFields[optionName], optionValue);
	      if (optionBlock instanceof HTMLElement) {
	        this.optionsFieldsBlock.appendChild(optionBlock);
	        this.optionsFieldsBlock.style.display = 'block';
	      }
	    });
	    let dialogContent = main_core.Tag.render(_t4$2 || (_t4$2 = _$6`<div class="${0}"></div>`), DialogStyle.ProcessPopup);
	    dialogContent.appendChild(this.summaryBlock);
	    dialogContent.appendChild(this.warningBlock);
	    dialogContent.appendChild(this.errorBlock);
	    if (this.progressBarBlock) {
	      dialogContent.appendChild(this.progressBarBlock);
	    }
	    if (this.optionsFieldsBlock) {
	      dialogContent.appendChild(this.optionsFieldsBlock);
	    }
	    return dialogContent;
	  }

	  /**
	   * @private
	   */
	  _renderOption(option, optionValue = null) {
	    option.id = this.id + '_opt_' + option.name;
	    switch (option.type) {
	      case 'text':
	        this.fields[option.name] = new TextField(option);
	        break;
	      case 'file':
	        this.fields[option.name] = new FileField(option);
	        break;
	      case 'checkbox':
	        this.fields[option.name] = new CheckboxField(option);
	        break;
	      case 'select':
	        this.fields[option.name] = new SelectField(option);
	        break;
	      case 'radio':
	        this.fields[option.name] = new RadioField(option);
	        break;
	    }
	    if (optionValue !== null) {
	      this.fields[option.name].setValue(optionValue);
	    }
	    const optionBlock = this.fields[option.name].getContainer();
	    return optionBlock;
	  }

	  //endregion

	  //region Events

	  onDialogClose() {
	    if (this.popupWindow) {
	      this.popupWindow.destroy();
	      this.popupWindow = null;
	    }
	    this.buttons = {};
	    this.fields = {};
	    this.summaryBlock = null;
	    this.isShown = false;
	    this.callHandler('dialogClosed');
	    main_core_events.EventEmitter.emit(DialogEvent.Closed, new main_core_events.BaseEvent({
	      dialog: this
	    }));
	  }
	  handleStartButtonClick() {
	    const btn = this.getButton('start');
	    if (btn && btn.isDisabled()) {
	      return;
	    }
	    this.start();
	  }
	  handleStopButtonClick() {
	    const btn = this.getButton('stop');
	    if (btn && btn.isDisabled()) {
	      return;
	    }
	    this.stop();
	  }
	  handleCloseButtonClick() {
	    this.popupWindow.close();
	  }

	  //endregion

	  //region Buttons

	  /**
	   * @private
	   */
	  _prepareDialogButtons() {
	    const showButtons = this.getSetting('showButtons');
	    let ret = [];
	    this.buttons = {};
	    if (showButtons.start) {
	      const startButtonText = this.getMessage('startButton');
	      this.buttons.start = new ui_buttons.Button({
	        text: startButtonText || 'Start',
	        color: ui_buttons.Button.Color.SUCCESS,
	        icon: ui_buttons.Button.Icon.START,
	        //className: DialogStyle.ButtonStart,
	        events: {
	          click: BX.delegate(this.handleStartButtonClick, this)
	        }
	      });
	      ret.push(this.buttons.start);
	    }
	    if (showButtons.stop) {
	      const stopButtonText = this.getMessage('stopButton');
	      this.buttons.stop = new ui_buttons.Button({
	        text: stopButtonText || 'Stop',
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        icon: ui_buttons.Button.Icon.STOP,
	        //className: DialogStyle.ButtonStop,
	        events: {
	          click: BX.delegate(this.handleStopButtonClick, this)
	        }
	      });
	      this.buttons.stop.setDisabled();
	      ret.push(this.buttons.stop);
	    }
	    if (showButtons.close) {
	      const closeButtonText = this.getMessage('closeButton');
	      this.buttons.close = new ui_buttons.CancelButton({
	        text: closeButtonText || 'Close',
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        tag: ui_buttons.Button.Tag.SPAN,
	        events: {
	          click: BX.delegate(this.handleCloseButtonClick, this)
	        }
	      });
	      ret.push(this.buttons.close);
	    }
	    return ret;
	  }

	  /**
	   * @param {String} downloadLink
	   * @param {String} fileName
	   * @param {function} purgeHandler
	   * @return self
	   */
	  setDownloadButtons(downloadLink, fileName, purgeHandler) {
	    let ret = [];
	    if (downloadLink) {
	      let downloadButtonText = this.getMessage("downloadButton");
	      downloadButtonText = downloadButtonText !== "" ? downloadButtonText : "Download file";
	      const downloadButton = new ui_buttons.Button({
	        text: downloadButtonText,
	        color: ui_buttons.Button.Color.SUCCESS,
	        icon: ui_buttons.Button.Icon.DOWNLOAD,
	        className: DialogStyle.ButtonDownload,
	        tag: ui_buttons.Button.Tag.LINK,
	        link: downloadLink,
	        props: {
	          //href: downloadLink,
	          download: fileName
	        }
	      });
	      ret.push(downloadButton);
	    }
	    if (typeof purgeHandler == 'function') {
	      let clearButtonText = this.getMessage("clearButton");
	      clearButtonText = clearButtonText !== "" ? clearButtonText : "Delete file";
	      const clearButton = new ui_buttons.Button({
	        text: clearButtonText,
	        color: ui_buttons.Button.Color.LIGHT_BORDER,
	        icon: ui_buttons.Button.Icon.REMOVE,
	        className: DialogStyle.ButtonRemove,
	        events: {
	          click: purgeHandler
	        }
	      });
	      ret.push(clearButton);
	    }
	    if (this.buttons.close) {
	      ret.push(this.buttons.close);
	    }
	    if (ret.length > 0 && this.popupWindow) {
	      this.popupWindow.setButtons(ret);
	    }
	    return this;
	  }
	  resetButtons(showButtons = {
	    'start': true,
	    'stop': true,
	    'close': true
	  }) {
	    this._prepareDialogButtons();
	    showButtons = showButtons || this.getSetting('showButtons');
	    let ret = [];
	    if (showButtons.start) {
	      ret.push(this.buttons.start);
	    }
	    if (showButtons.stop) {
	      ret.push(this.buttons.stop);
	    }
	    if (showButtons.close) {
	      ret.push(this.buttons.close);
	    }
	    if (ret.length > 0 && this.popupWindow) {
	      this.popupWindow.setButtons(ret);
	    }
	    return this;
	  }
	  getButton(bid) {
	    var _this$buttons$bid;
	    return (_this$buttons$bid = this.buttons[bid]) != null ? _this$buttons$bid : null;
	  }
	  lockButton(bid, lock, wait) {
	    const btn = this.getButton(bid);
	    if (btn) {
	      btn.setDisabled(lock);
	      if (main_core.Type.isBoolean(wait)) {
	        btn.setWaiting(wait);
	      }
	    }
	    return this;
	  }
	  showButton(bid, show) {
	    const btn = this.getButton(bid);
	    if (btn) {
	      btn.getContainer().style.display = !!show ? '' : 'none';
	    }
	    if (bid === 'close') {
	      if (this.popupWindow && this.popupWindow.closeIcon) {
	        this.popupWindow.closeIcon.style.display = !!show ? '' : 'none';
	      }
	    }
	    return this;
	  }

	  // endregion

	  //region Summary

	  setSummary(content, isHtml = false) {
	    if (this.optionsFieldsBlock) {
	      BX.clean(this.optionsFieldsBlock);
	      this.optionsFieldsBlock.style.display = 'none';
	    }
	    if (main_core.Type.isStringFilled(content)) {
	      if (this.summaryBlock) {
	        if (!!isHtml) this.summaryBlock.innerHTML = content;else this.summaryBlock.innerHTML = BX.util.htmlspecialchars(content);
	        this.summaryBlock.style.display = "block";
	      }
	    } else {
	      this.summaryBlock.innerHTML = "";
	      this.summaryBlock.style.display = "none";
	    }
	    return this;
	  }

	  //endregion

	  //region Errors

	  setErrors(errors, isHtml = false) {
	    errors.forEach(err => this.setError(err, isHtml));
	    return this;
	  }
	  setError(content, isHtml) {
	    if (main_core.Type.isStringFilled(content)) {
	      this.setSummary('');
	      if (this.progressBar) {
	        this.progressBar.setColor(BX.UI.ProgressBar.Color.DANGER);
	      }
	      if (!!isHtml) {
	        this.error.setText(content);
	      } else {
	        this.error.setText(BX.util.htmlspecialchars(content));
	      }
	      this.errorBlock.style.display = "flex";
	    }
	    return this;
	  }
	  clearErrors() {
	    if (this.error) {
	      this.error.setText('');
	    }
	    if (this.errorBlock) {
	      this.errorBlock.style.display = 'none';
	    }
	    return this;
	  }
	  setWarning(err, isHtml = false) {
	    if (main_core.Type.isStringFilled(err)) {
	      if (!!isHtml) {
	        this.warning.setText(err);
	      } else {
	        this.warning.setText(BX.util.htmlspecialchars(err));
	      }
	      this.warningBlock.style.display = "flex";
	    }
	    return this;
	  }
	  clearWarnings() {
	    if (this.warning) {
	      this.warning.setText("");
	    }
	    if (this.warningBlock) {
	      this.warningBlock.style.display = 'none';
	    }
	    return this;
	  }

	  //endregion

	  //region Progressbar

	  setProgressBar(totalItems, processedItems, textBefore) {
	    if (this.progressBar) {
	      if (main_core.Type.isNumber(processedItems) && main_core.Type.isNumber(totalItems) && totalItems > 0) {
	        BX.show(this.progressBarBlock);
	        this.progressBar.setColor(BX.UI.ProgressBar.Color.PRIMARY);
	        this.progressBar.setMaxValue(totalItems);
	        textBefore = textBefore || "";
	        this.progressBar.setTextBefore(textBefore);
	        this.progressBar.update(processedItems);
	      } else {
	        this.hideProgressBar();
	      }
	    }
	    return this;
	  }
	  hideProgressBar() {
	    if (this.progressBar) {
	      BX.hide(this.progressBarBlock);
	    }
	    return this;
	  }

	  //endregion

	  //region Initial options

	  getOptionField(name) {
	    if (main_core.Type.isString(name)) {
	      if (this.fields[name] && this.fields[name] instanceof BaseField) {
	        return this.fields[name];
	      }
	    }
	    return null;
	  }
	  getOptionFieldValues() {
	    let initialOptions = {};
	    if (this.optionsFieldsBlock) {
	      Object.keys(this.fields).forEach(optionName => {
	        let field = this.getOptionField(optionName);
	        let val = field.getValue();
	        if (field.type === 'checkbox' && main_core.Type.isBoolean(val)) {
	          initialOptions[optionName] = val ? 'Y' : 'N';
	        } else if (main_core.Type.isArray(val)) {
	          if (main_core.Type.isArrayFilled(val)) {
	            initialOptions[optionName] = val;
	          }
	        } else if (val) {
	          initialOptions[optionName] = val;
	        }
	      });
	    }
	    return initialOptions;
	  }
	  checkOptionFields() {
	    let checked = true;
	    if (this.optionsFieldsBlock) {
	      Object.keys(this.fields).forEach(optionName => {
	        let field = this.getOptionField(optionName);
	        if (field.obligatory) {
	          if (!field.isFilled()) {
	            field.showWarning();
	            checked = false;
	          } else {
	            field.hideWarning();
	          }
	        }
	      });
	    }
	    return checked;
	  }
	  lockOptionFields(flag = true) {
	    if (this.optionsFieldsBlock) {
	      Object.keys(this.fields).forEach(optionName => {
	        let field = this.getOptionField(optionName);
	        if (field) {
	          field.lock(flag);
	        }
	      });
	    }
	    return this;
	  }
	  //endregion
	}

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */
	const ProcessEvent = {
	  StateChanged: 'BX.UI.StepProcessing.StateChanged',
	  BeforeRequest: 'BX.UI.StepProcessing.BeforeRequest'
	};

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */
	const ProcessCallback = {
	  StateChanged: 'StateChanged',
	  RequestStart: 'RequestStart',
	  RequestStop: 'RequestStop',
	  RequestFinalize: 'RequestFinalize',
	  StepCompleted: 'StepCompleted'
	};
	const ProcessDefaultLabels = {
	  AuthError: main_core.Loc.getMessage('UI_STEP_PROCESSING_AUTH_ERROR'),
	  RequestError: main_core.Loc.getMessage('UI_STEP_PROCESSING_REQUEST_ERR'),
	  DialogStartButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_START'),
	  DialogStopButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_STOP'),
	  DialogCloseButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_BTN_CLOSE'),
	  RequestCanceling: main_core.Loc.getMessage('UI_STEP_PROCESSING_CANCELING'),
	  RequestCanceled: main_core.Loc.getMessage('UI_STEP_PROCESSING_CANCELED'),
	  RequestCompleted: main_core.Loc.getMessage('UI_STEP_PROCESSING_COMPLETED'),
	  DialogExportDownloadButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_DOWNLOAD'),
	  DialogExportClearButton: main_core.Loc.getMessage('UI_STEP_PROCESSING_FILE_DELETE'),
	  WaitingResponse: main_core.Loc.getMessage('UI_STEP_PROCESSING_WAITING')
	};
	const EndpointType = {
	  Controller: 'controller',
	  Component: 'component'
	};

	/**
	 * Long running process.
	 *
	 * @namespace {BX.UI.StepProcessing}
	 * @event BX.UI.StepProcessing.StateChanged
	 * @event BX.UI.StepProcessing.BeforeRequest
	 */
	class Process {
	  // Ajax endpoint

	  // Queue

	  // Events

	  // Messages

	  constructor(options) {
	    this.action = '';
	    this.method = 'POST';
	    this.params = {};
	    this.isRequestRunning = false;
	    this.queue = [];
	    this.currentStep = -1;
	    this.state = ProcessState.intermediate;
	    this.initialOptionValues = {};
	    this.optionsFields = {};
	    this.handlers = {};
	    this.messages = new Map();
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.id = this.getOption('id', '');
	    if (!main_core.Type.isStringFilled(this.id)) {
	      this.id = 'Process_' + main_core.Text.getRandom().toLowerCase();
	    }
	    const controller = this.getOption('controller', '');
	    const component = this.getOption('component', '');
	    if (main_core.Type.isStringFilled(controller)) {
	      this.controller = controller;
	      this.controllerDefault = controller;
	      this.endpointType = EndpointType.Controller;
	    } else if (main_core.Type.isStringFilled(component)) {
	      this.component = component;
	      this.endpointType = EndpointType.Component;
	      this.componentMode = this.getOption('componentMode', 'class');
	    }
	    if (!main_core.Type.isStringFilled(this.controller)) {
	      if (!main_core.Type.isStringFilled(this.component)) {
	        throw new TypeError("BX.UI.StepProcessing: There no any ajax endpoint was defined.");
	      }
	    }
	    this.setQueue(this.getOption('queue', [])).setParams(this.getOption('params', {})).setOptionsFields(this.getOption('optionsFields', {})).setHandlers(this.getOption('handlers', {})).setMessages(ProcessDefaultLabels).setMessages(this.getOption('messages', {}));
	  }
	  destroy() {
	    if (this.dialog instanceof Dialog) {
	      this.dialog.close().destroy();
	      this.dialog = null;
	    }
	    this._closeConnection();
	  }

	  //region Run

	  start(startStep = 1) {
	    this._refreshHash();
	    startStep = startStep || 1;
	    if (this.state === ProcessState.intermediate || this.state === ProcessState.stopped || this.state === ProcessState.completed) {
	      if (!this.getDialog().checkOptionFields()) {
	        return;
	      }
	      this.getDialog().clearErrors().clearWarnings();
	      this.networkErrorCount = 0;
	      if (this.getQueueLength() > 0) {
	        this.currentStep = 0;
	        if (startStep > 1) {
	          this.currentStep = startStep - 1;
	        }
	        if (this.endpointType === EndpointType.Controller) {
	          if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	            this.setController(this.queue[this.currentStep].controller);
	          }
	        }
	        if (!main_core.Type.isStringFilled(this.queue[this.currentStep].action)) {
	          throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
	        }
	        this.setAction(this.queue[this.currentStep].action);
	        this.startRequest();
	        if (this.queue[this.currentStep].title) {
	          this.getDialog().setSummary(this.queue[this.currentStep].title);
	        } else {
	          this.getDialog().setSummary(this.getMessage('WaitingResponse'));
	        }
	      } else {
	        this.startRequest();
	      }
	    }
	    return this;
	  }
	  stop() {
	    if (this.state === ProcessState.running) {
	      this.stopRequest();
	      this.currentStep = -1;
	    }
	    return this;
	  }

	  //endregion

	  //region Request

	  startRequest() {
	    if (this.isRequestRunning || this.state === ProcessState.canceling) {
	      return this.ajaxPromise;
	    }
	    this.isRequestRunning = true;
	    this.ajaxPromise = null;
	    let actionData = new FormData();
	    let appendData = (data, prefix) => {
	      if (main_core.Type.isPlainObject(data)) {
	        Object.keys(data).forEach(name => {
	          let id = name;
	          if (prefix) {
	            id = prefix + '[' + name + ']';
	          }
	          if (main_core.Type.isArray(data[name]) || main_core.Type.isPlainObject(data[name])) {
	            appendData(data[name], id);
	          } else {
	            actionData.append(id, data[name]);
	          }
	        });
	      } else if (main_core.Type.isArray(data)) {
	        data.forEach(element => actionData.append(prefix + '[]', element));
	      }
	    };
	    appendData(this.params);
	    if (this.queue[this.currentStep].params) {
	      appendData(this.queue[this.currentStep].params);
	    }
	    let initialOptions = this.getDialog().getOptionFieldValues();
	    if (BX.type.isNotEmptyObject(initialOptions)) {
	      appendData(initialOptions);
	      this.initialOptionValues = initialOptions;
	      this.storeOptionFieldValues(initialOptions);
	    } else {
	      Object.keys(this.initialOptionValues).forEach(name => {
	        // don't repeat file uploading
	        if (this.initialOptionValues[name] instanceof File) {
	          delete this.initialOptionValues[name];
	        }
	      });
	      appendData(this.initialOptionValues);
	    }
	    this.setState(ProcessState.running);
	    if (this.hasActionHandler(ProcessCallback.RequestStart)) {
	      this.callActionHandler(ProcessCallback.RequestStart, [actionData]);
	    } else if (this.hasHandler(ProcessCallback.RequestStart)) {
	      this.callHandler(ProcessCallback.RequestStart, [actionData]);
	    }
	    main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	      data: {
	        process: this,
	        actionData: actionData
	      }
	    }));
	    let params = {
	      data: actionData,
	      method: this.method,
	      onrequeststart: this._onRequestStart.bind(this)
	    };
	    if (this.endpointType === EndpointType.Controller) {
	      this.ajaxPromise = BX.ajax.runAction(this.controller + '.' + this.getAction(), params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	    } else if (this.endpointType === EndpointType.Component) {
	      params.data.mode = this.componentMode;
	      if ('signedParameters' in params.data) {
	        params.signedParameters = params.data.signedParameters;
	        delete params.data.signedParameters;
	      }
	      this.ajaxPromise = BX.ajax.runComponentAction(this.component, this.getAction(), params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	    }
	    return this.ajaxPromise;
	  }
	  stopRequest() {
	    if (this.state === ProcessState.canceling) {
	      return this.ajaxPromise;
	    }
	    this.setState(ProcessState.canceling);
	    this._closeConnection();
	    let actionData = BX.clone(this.params);
	    actionData.cancelingAction = this.getAction();
	    this.getDialog().setSummary(this.getMessage("RequestCanceling"));
	    let proceedAction = true;
	    if (this.hasActionHandler(ProcessCallback.RequestStop)) {
	      proceedAction = false;
	      this.callActionHandler(ProcessCallback.RequestStop, [actionData]);
	    } else if (this.hasHandler(ProcessCallback.RequestStop)) {
	      proceedAction = false;
	      this.callHandler(ProcessCallback.RequestStop, [actionData]);
	    }
	    main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	      data: {
	        process: this,
	        actionData: actionData
	      }
	    }));
	    this.ajaxPromise = null;
	    if (proceedAction) {
	      let params = {
	        data: actionData,
	        method: this.method,
	        onrequeststart: this._onRequestStart.bind(this)
	      };
	      if (this.endpointType === EndpointType.Controller) {
	        this.setController(this.controllerDefault);
	        this.ajaxPromise = BX.ajax.runAction(this.controller + '.cancel', params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	      } else if (this.endpointType === EndpointType.Component) {
	        params.data.mode = this.componentMode;
	        if ('signedParameters' in params.data) {
	          params.signedParameters = params.data.signedParameters;
	          delete params.data.signedParameters;
	        }
	        this.ajaxPromise = BX.ajax.runComponentAction(this.component, 'cancel', params).then(this._onRequestSuccess.bind(this), this._onRequestFailure.bind(this));
	      }
	    }
	    return this.ajaxPromise;
	  }
	  finalizeRequest() {
	    if (this.state === ProcessState.canceling) {
	      return this.ajaxPromise;
	    }
	    let actionData = BX.clone(this.params);
	    let proceedAction = true;
	    if (this.hasActionHandler(ProcessCallback.RequestFinalize)) {
	      proceedAction = false;
	      this.callActionHandler(ProcessCallback.RequestFinalize, [actionData]);
	    } else if (this.hasHandler(ProcessCallback.RequestFinalize)) {
	      proceedAction = false;
	      this.callHandler(ProcessCallback.RequestFinalize, [actionData]);
	    }
	    main_core_events.EventEmitter.emit(ProcessEvent.BeforeRequest, new main_core_events.BaseEvent({
	      data: {
	        process: this,
	        actionData: actionData
	      }
	    }));
	    this.ajaxPromise = null;
	    if (proceedAction) {
	      let params = {
	        data: actionData,
	        method: this.method,
	        onrequeststart: this._onRequestStart.bind(this)
	      };
	      if (this.endpointType === EndpointType.Controller) {
	        this.setController(this.controllerDefault);
	        this.ajaxPromise = BX.ajax.runAction(this.controller + '.finalize', params);
	      } else if (this.endpointType === EndpointType.Component) {
	        params.data.mode = this.componentMode;
	        if ('signedParameters' in params.data) {
	          params.signedParameters = params.data.signedParameters;
	          delete params.data.signedParameters;
	        }
	        this.ajaxPromise = BX.ajax.runComponentAction(this.component, 'finalize', params);
	      }
	    }
	    return this.ajaxPromise;
	  }

	  /**
	   * @private
	   */
	  _refreshHash() {
	    this.hash = this.id + Date.now();
	    this.setParam("PROCESS_TOKEN", this.hash);
	    return this;
	  }

	  /**
	   * @private
	   */
	  _onRequestSuccess(response) {
	    this.isRequestRunning = false;
	    this.xhr = null;
	    this.ajaxPromise = null;
	    if (!response) {
	      this.getDialog().setError(this.getMessage('RequestError'));
	      this.setState(ProcessState.error);
	      return;
	    }
	    if (main_core.Type.isArrayFilled(response.errors)) {
	      const errors = response.errors.slice(-10);
	      let errMessages = [];
	      errors.forEach(err => errMessages.push(err.message));
	      this.getDialog().setErrors(errMessages, true);
	      this.setState(ProcessState.error);
	      return;
	    }
	    this.networkErrorCount = 0;
	    const result = response.data;
	    const status = main_core.Type.isStringFilled(result.STATUS) ? result.STATUS : "";
	    let summary = "";
	    if (main_core.Type.isStringFilled(result.SUMMARY)) {
	      summary = result.SUMMARY;
	    } else if (main_core.Type.isStringFilled(result.SUMMARY_HTML)) {
	      summary = result.SUMMARY_HTML;
	    }
	    const processedItems = main_core.Type.isNumber(result.PROCESSED_ITEMS) ? result.PROCESSED_ITEMS : 0;
	    const totalItems = main_core.Type.isNumber(result.TOTAL_ITEMS) ? result.TOTAL_ITEMS : 0;
	    let finalize = !!result.FINALIZE;
	    if (this.hasActionHandler(ProcessCallback.StepCompleted)) {
	      this.callActionHandler(ProcessCallback.StepCompleted, [status, result]);
	    }
	    if (main_core.Type.isStringFilled(result.WARNING)) {
	      this.getDialog().setWarning(result.WARNING);
	    }
	    if (status === ProcessResultStatus.progress || status === ProcessResultStatus.completed) {
	      if (totalItems > 0) {
	        if (this.queue[this.currentStep].progressBarTitle) {
	          this.getDialog().setProgressBar(totalItems, processedItems, this.queue[this.currentStep].progressBarTitle);
	        } else {
	          this.getDialog().setProgressBar(totalItems, processedItems);
	        }
	      } else {
	        this.getDialog().hideProgressBar();
	      }
	    }
	    if (status === ProcessResultStatus.progress) {
	      if (summary !== "") {
	        this.getDialog().setSummary(summary, true);
	      }
	      if (this.state === ProcessState.canceling) {
	        this.setState(ProcessState.stopped);
	      } else {
	        if (this.endpointType === EndpointType.Controller) {
	          const nextController = main_core.Type.isStringFilled(result.NEXT_CONTROLLER) ? result.NEXT_CONTROLLER : "";
	          if (nextController !== "") {
	            this.setController(nextController);
	          } else if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	            this.setController(this.queue[this.currentStep].controller);
	          } else {
	            this.setController(this.controllerDefault);
	          }
	        }
	        const nextAction = main_core.Type.isStringFilled(result.NEXT_ACTION) ? result.NEXT_ACTION : "";
	        if (nextAction !== "") {
	          this.setAction(nextAction);
	        }
	        setTimeout(BX.delegate(this.startRequest, this), 100);
	      }
	      return;
	    }
	    if (this.state === ProcessState.canceling) {
	      this.getDialog().setSummary(this.getMessage("RequestCanceled"));
	      this.setState(ProcessState.completed);
	    } else if (status === ProcessResultStatus.completed) {
	      if (this.getQueueLength() > 0 && this.currentStep + 1 < this.getQueueLength()) {
	        // next
	        this.currentStep++;
	        if (this.endpointType === EndpointType.Controller) {
	          if (main_core.Type.isStringFilled(this.queue[this.currentStep].controller)) {
	            this.setController(this.queue[this.currentStep].controller);
	          } else {
	            this.setController(this.controllerDefault);
	          }
	        }
	        if (!main_core.Type.isStringFilled(this.queue[this.currentStep].action)) {
	          throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
	        }
	        if ('finalize' in this.queue[this.currentStep]) {
	          finalize = true;
	          this.setAction(this.queue[this.currentStep].action);
	        } else {
	          this.setAction(this.queue[this.currentStep].action);
	          this.getDialog().setSummary(this.queue[this.currentStep].title);
	          setTimeout(BX.delegate(this.startRequest, this), 100);
	          return;
	        }
	      }
	      if (summary !== "") {
	        this.getDialog().setSummary(summary, true);
	      } else {
	        this.getDialog().setSummary(this.getMessage("RequestCompleted"));
	      }
	      if (main_core.Type.isStringFilled(result.DOWNLOAD_LINK)) {
	        if (main_core.Type.isStringFilled(result.DOWNLOAD_LINK_NAME)) {
	          this.getDialog().setMessage('downloadButton', result.DOWNLOAD_LINK_NAME);
	        }
	        if (main_core.Type.isStringFilled(result.CLEAR_LINK_NAME)) {
	          this.getDialog().setMessage('clearButton', result.CLEAR_LINK_NAME);
	        }
	        this.getDialog().setDownloadButtons(result.DOWNLOAD_LINK, result.FILE_NAME, BX.delegate(function () {
	          this.getDialog().resetButtons({
	            stop: true,
	            close: true
	          });
	          this.callAction('clear'); //.then
	          setTimeout(BX.delegate(function () {
	            this.getDialog().resetButtons({
	              close: true
	            });
	          }, this), 1000);
	        }, this));
	      }
	      this.setState(ProcessState.completed, result);
	      if (finalize) {
	        setTimeout(BX.delegate(this.finalizeRequest, this), 100);
	      }
	    } else {
	      this.getDialog().setSummary("").setError(this.getMessage("RequestError"));
	      this.setState(ProcessState.error);
	    }
	  }

	  /**
	   * @private
	   */
	  _onRequestFailure(response) {
	    /*
	    // check if it's manual aborting
	    if (this.state === ProcessState.canceling)
	    {
	    	return;
	    }
	    */
	    this.isRequestRunning = false;
	    this.ajaxPromise = null;

	    // check non auth
	    if (main_core.Type.isPlainObject(response) && 'data' in response && main_core.Type.isPlainObject(response.data) && 'ajaxRejectData' in response.data && main_core.Type.isPlainObject(response.data.ajaxRejectData) && 'reason' in response.data.ajaxRejectData && response.data.ajaxRejectData.reason === 'status' && 'data' in response.data.ajaxRejectData && response.data.ajaxRejectData.data === 401) {
	      this.getDialog().setError(this.getMessage('AuthError'));
	    }
	    // check errors
	    else if (main_core.Type.isPlainObject(response) && 'errors' in response && main_core.Type.isArrayFilled(response.errors)) {
	      let abortingState = false;
	      let networkError = false;
	      response.errors.forEach(err => {
	        if (err.code === 'NETWORK_ERROR') {
	          if (this.state === ProcessState.canceling) {
	            abortingState = true;
	          } else {
	            networkError = true;
	          }
	        }
	      });

	      // ignoring error of manual aborting
	      if (abortingState) {
	        return;
	      }
	      if (networkError) {
	        this.networkErrorCount++;
	        // Let's give it more chance to complete
	        if (this.networkErrorCount <= 2) {
	          setTimeout(BX.delegate(this.startRequest, this), 15000);
	          return;
	        }
	      }
	      const errors = response.errors.slice(-10);
	      let errMessages = [];
	      errors.forEach(err => {
	        if (err.code === 'NETWORK_ERROR') {
	          errMessages.push(this.getMessage('RequestError'));
	        } else {
	          errMessages.push(err.message);
	        }
	      });
	      this.getDialog().setErrors(errMessages, true);
	    } else {
	      this.getDialog().setError(this.getMessage('RequestError'));
	    }
	    this.xhr = null;
	    this.currentStep = -1;
	    this.setState(ProcessState.error);
	  }

	  //endregion

	  //region Connection

	  /**
	   * @private
	   */
	  _closeConnection() {
	    if (this.xhr instanceof XMLHttpRequest) {
	      try {
	        this.xhr.abort();
	        this.xhr = null;
	      } catch (e) {}
	    }
	  }
	  /**
	   * @private
	   */
	  _onRequestStart(xhr) {
	    this.xhr = xhr;
	  }

	  //endregion

	  //region Set & Get

	  setId(id) {
	    this.id = id;
	    return this;
	  }
	  getId() {
	    return this.id;
	  }

	  //region Queue actions

	  setQueue(queue) {
	    queue.forEach(action => this.addQueueAction(action));
	    return this;
	  }
	  addQueueAction(action) {
	    this.queue.push(action);
	    return this;
	  }
	  getQueueLength() {
	    return this.queue.length;
	  }

	  //endregion

	  //region Process options

	  setOption(name, value) {
	    this.options[name] = value;
	    return this;
	  }
	  getOption(name, defaultValue = null) {
	    return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	  }

	  //endregion

	  //region Initial fields

	  setOptionsFields(optionsFields) {
	    Object.keys(optionsFields).forEach(id => this.addOptionsField(id, optionsFields[id]));
	    return this;
	  }
	  addOptionsField(id, field) {
	    this.optionsFields[id] = field;
	    return this;
	  }
	  storeOptionFieldValues(values) {
	    if ('sessionStorage' in window) {
	      let valuesToStore = {};
	      Object.keys(this.optionsFields).forEach(name => {
	        let field = this.optionsFields[name];
	        switch (field.type) {
	          case 'checkbox':
	          case 'select':
	          case 'radio':
	            if (field.name in values) {
	              valuesToStore[field.name] = values[field.name];
	            }
	            break;
	        }
	      });
	      window.sessionStorage.setItem('bx.' + this.getId(), JSON.stringify(valuesToStore));
	    }
	    return this;
	  }
	  restoreOptionFieldValues() {
	    let values = {};
	    if ('sessionStorage' in window) {
	      values = JSON.parse(window.sessionStorage.getItem('bx.' + this.getId()));
	      if (!main_core.Type.isPlainObject(values)) {
	        values = {};
	      }
	    }
	    return values;
	  }

	  //endregion

	  //region Request parameters

	  setParams(params) {
	    this.params = {};
	    Object.keys(params).forEach(name => this.setParam(name, params[name]));
	    return this;
	  }
	  getParams() {
	    return this.params;
	  }
	  setParam(key, value) {
	    this.params[key] = value;
	    return this;
	  }
	  getParam(key) {
	    return this.params[key] ? this.params[key] : null;
	  }

	  //endregion

	  //region Process state

	  setState(state, result = {}) {
	    if (this.state === state) {
	      return this;
	    }
	    this.state = state;
	    if (state === ProcessState.intermediate || state === ProcessState.stopped) {
	      this.getDialog().lockButton('start', false).lockButton('stop', true).showButton('close', true);
	    } else if (state === ProcessState.running) {
	      this.getDialog().lockButton('start', true, true).lockButton('stop', false).showButton('close', false);
	    } else if (state === ProcessState.canceling) {
	      this.getDialog().lockButton('start', true).lockButton('stop', true, true).showButton('close', false).hideProgressBar();
	    } else if (state === ProcessState.error) {
	      this.getDialog().lockButton('start', true).lockButton('stop', true).showButton('close', true);
	    } else if (state === ProcessState.completed) {
	      this.getDialog().lockButton('start', true).lockButton('stop', true).showButton('close', true).hideProgressBar();
	    }
	    if (this.hasActionHandler(ProcessCallback.StateChanged)) {
	      this.callActionHandler(ProcessCallback.StateChanged, [state, result]);
	    } else if (this.hasHandler(ProcessCallback.StateChanged)) {
	      this.callHandler(ProcessCallback.StateChanged, [state, result]);
	    }
	    main_core_events.EventEmitter.emit(ProcessEvent.StateChanged, new main_core_events.BaseEvent({
	      data: {
	        state: state,
	        result: result
	      }
	    }));
	    return this;
	  }
	  getState() {
	    return this.state;
	  }

	  //endregion

	  //region Controller

	  setController(controller) {
	    this.controller = controller;
	    return this;
	  }
	  getController() {
	    return this.controller;
	  }
	  setComponent(component, componentMode = 'class') {
	    this.component = component;
	    this.componentMode = componentMode;
	    return this;
	  }
	  getComponent() {
	    return this.component;
	  }
	  setAction(action) {
	    this.action = action;
	    return this;
	  }
	  getAction() {
	    return this.action;
	  }
	  callAction(action) {
	    this.setAction(action)._refreshHash();
	    return this.startRequest();
	  }

	  //endregion

	  //region Event handlers

	  setHandlers(handlers) {
	    Object.keys(handlers).forEach(type => this.setHandler(type, handlers[type]));
	    return this;
	  }
	  setHandler(type, handler) {
	    if (main_core.Type.isFunction(handler)) {
	      this.handlers[type] = handler;
	    }
	    return this;
	  }
	  hasHandler(type) {
	    return main_core.Type.isFunction(this.handlers[type]);
	  }
	  callHandler(type, args) {
	    if (this.hasHandler(type)) {
	      this.handlers[type].apply(this, args);
	    }
	  }
	  hasActionHandler(type) {
	    if (this.queue[this.currentStep]) {
	      if ('handlers' in this.queue[this.currentStep]) {
	        return main_core.Type.isFunction(this.queue[this.currentStep].handlers[type]);
	      }
	    }
	    return false;
	  }
	  callActionHandler(type, args) {
	    if (this.hasActionHandler(type)) {
	      this.queue[this.currentStep].handlers[type].apply(this, args);
	    }
	  }

	  //endregion

	  //region lang messages
	  setMessages(messages) {
	    Object.keys(messages).forEach(id => this.setMessage(id, messages[id]));
	    return this;
	  }
	  setMessage(id, text) {
	    this.messages.set(id, text);
	    return this;
	  }
	  getMessage(id, placeholders = null) {
	    let phrase = this.messages.has(id) ? this.messages.get(id) : '';
	    if (main_core.Type.isStringFilled(phrase) && main_core.Type.isPlainObject(placeholders)) {
	      Object.keys(placeholders).forEach(placeholder => {
	        phrase = phrase.replace('#' + placeholder + '#', placeholders[placeholder]);
	      });
	    }
	    return phrase;
	  }

	  //endregion
	  //endregion

	  //region Dialog

	  getDialog() {
	    if (!this.dialog) {
	      this.dialog = new Dialog({
	        id: this.id,
	        optionsFields: this.getOption('optionsFields', {}),
	        minWidth: Number.parseInt(this.getOption('dialogMinWidth', 500)),
	        maxWidth: Number.parseInt(this.getOption('dialogMaxWidth', 1000)),
	        optionsFieldsValue: this.restoreOptionFieldValues(),
	        messages: {
	          title: this.getMessage('DialogTitle'),
	          summary: this.getMessage('DialogSummary'),
	          startButton: this.getMessage('DialogStartButton'),
	          stopButton: this.getMessage('DialogStopButton'),
	          closeButton: this.getMessage('DialogCloseButton'),
	          downloadButton: this.getMessage('DialogExportDownloadButton'),
	          clearButton: this.getMessage('DialogExportClearButton')
	        },
	        showButtons: this.getOption('showButtons'),
	        handlers: {
	          start: BX.delegate(this.start, this),
	          stop: BX.delegate(this.stop, this),
	          dialogShown: typeof this.handlers.dialogShown == 'function' ? this.handlers.dialogShown : null,
	          dialogClosed: typeof this.handlers.dialogClosed == 'function' ? this.handlers.dialogClosed : null
	        }
	      });
	    }
	    return this.dialog;
	  }
	  showDialog() {
	    this.getDialog().setSetting('optionsFieldsValue', this.restoreOptionFieldValues()).resetButtons(this.getOption('optionsFields')).show();
	    if (!this.isRequestRunning) {
	      this.setState(ProcessState.intermediate);
	    }
	    return this;
	  }
	  closeDialog() {
	    if (this.isRequestRunning) {
	      this.stop();
	    }
	    this.getDialog().close();
	    return this;
	  }

	  //endregion
	}

	/**
	 * @namespace {BX.UI.StepProcessing}
	 */
	class ProcessManager {
	  static create(props) {
	    if (!this.instances) {
	      this.instances = new Map();
	    }
	    let process = new Process(props);
	    this.instances.set(process.getId(), process);
	    return process;
	  }
	  static get(id) {
	    if (this.instances) {
	      if (this.instances.has(id)) {
	        return this.instances.get(id);
	      }
	    }
	    return null;
	  }
	  static has(id) {
	    if (this.instances) {
	      return this.instances.has(id);
	    }
	    return false;
	  }
	  static delete(id) {
	    if (this.instances) {
	      if (this.instances.has(id)) {
	        this.instances.get(id).destroy();
	        this.instances.delete(id);
	      }
	    }
	  }
	}

	exports.ProcessManager = ProcessManager;
	exports.Process = Process;
	exports.ProcessState = ProcessState;
	exports.ProcessEvent = ProcessEvent;
	exports.ProcessCallback = ProcessCallback;
	exports.ProcessResultStatus = ProcessResultStatus;
	exports.Dialog = Dialog;
	exports.DialogEvent = DialogEvent;

}((this.BX.UI.StepProcessing = this.BX.UI.StepProcessing || {}),BX,BX.UI,BX.Event,BX.Main,BX.UI,BX.UI,BX));
//# sourceMappingURL=stepprocessing.bundle.js.map
