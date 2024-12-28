/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,ui_iconSet_api_core,ui_alerts,bp_field_type,ui_forms,main_date,sidepanel,main_core_events,ui_buttons,main_core,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	var _title = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("title");
	var _description = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("description");
	var _renderIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderIcon");
	var _renderContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	var _renderInfo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderInfo");
	class Header {
	  constructor(config) {
	    Object.defineProperty(this, _renderInfo, {
	      value: _renderInfo2
	    });
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _renderContent, {
	      value: _renderContent2
	    });
	    Object.defineProperty(this, _renderIcon, {
	      value: _renderIcon2
	    });
	    Object.defineProperty(this, _title, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _description, {
	      writable: true,
	      value: ''
	    });
	    if (main_core.Type.isStringFilled(config.title)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _title)[_title] = config.title;
	    }
	    if (main_core.Type.isStringFilled(config.description)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _description)[_description] = config.description;
	    }
	  }
	  render() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="bizproc__ws_start__header">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderIcon)[_renderIcon](), babelHelpers.classPrivateFieldLooseBase(this, _renderContent)[_renderContent]());
	  }
	}
	function _renderIcon2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Main.BUSINESS_PROCESS_1,
	    size: 48,
	    color: 'var(--ui-color-palette-white-base)'
	  });
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="bizproc__ws_start__header-icon">
				${0}
			</div>
		`), icon.render());
	}
	function _renderContent2() {
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="bizproc__ws_start__header-content">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle](), babelHelpers.classPrivateFieldLooseBase(this, _renderInfo)[_renderInfo]());
	}
	function _renderTitle2() {
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="bizproc__ws_start__header__title">
				${0}
			</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _title)[_title]));
	}
	function _renderInfo2() {
	  return main_core.Tag.render(_t5 || (_t5 = _`
			<div class="bizproc__ws_start__header__info">
				${0}
			</div>
		`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _description)[_description]));
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1;
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _itemsNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("itemsNode");
	var _sequenceSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceSteps");
	var _currentStepId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentStepId");
	var _renderItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderItem");
	var _markNotActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markNotActive");
	var _markActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markActive");
	var _markComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markComplete");
	var _markNotComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markNotComplete");
	class Breadcrumbs {
	  constructor(config = {}) {
	    Object.defineProperty(this, _markNotComplete, {
	      value: _markNotComplete2
	    });
	    Object.defineProperty(this, _markComplete, {
	      value: _markComplete2
	    });
	    Object.defineProperty(this, _markActive, {
	      value: _markActive2
	    });
	    Object.defineProperty(this, _markNotActive, {
	      value: _markNotActive2
	    });
	    Object.defineProperty(this, _renderItem, {
	      value: _renderItem2
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _itemsNode, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _sequenceSteps, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _currentStepId, {
	      writable: true,
	      value: null
	    });
	    if (!main_core.Type.isArrayFilled(config.items)) {
	      throw new TypeError('BX.Bizproc.Workflow.SingleStart.Breadcrumbs: items must be filled array');
	    }
	    config.items.forEach(item => {
	      babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].set(item.id, item);
	      babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].push(item.id);
	      if (item.active) {
	        babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId] = item.id;
	      }
	    });
	    if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]) && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(0))) {
	      babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(0);
	    }
	  }
	  render() {
	    return main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="bizproc__ws_start__breadcrumbs">
				${0}
			</div>
		`), [...babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].entries()].map(([key, item]) => babelHelpers.classPrivateFieldLooseBase(this, _renderItem)[_renderItem](item, key)));
	  }
	  next() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]) {
	      const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	      if (index !== -1 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(index + 1))) {
	        babelHelpers.classPrivateFieldLooseBase(this, _markNotActive)[_markNotActive](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	        babelHelpers.classPrivateFieldLooseBase(this, _markComplete)[_markComplete](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	        babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(index + 1);
	        babelHelpers.classPrivateFieldLooseBase(this, _markActive)[_markActive](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	      }
	    }
	  }
	  back() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]) {
	      const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	      if (index !== -1 && index - 1 >= 0 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(index - 1))) {
	        babelHelpers.classPrivateFieldLooseBase(this, _markNotActive)[_markNotActive](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	        babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps)[_sequenceSteps].at(index - 1);
	        babelHelpers.classPrivateFieldLooseBase(this, _markNotComplete)[_markNotComplete](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	        babelHelpers.classPrivateFieldLooseBase(this, _markActive)[_markActive](babelHelpers.classPrivateFieldLooseBase(this, _currentStepId)[_currentStepId]);
	      }
	    }
	  }
	}
	function _renderItem2(item, stepId) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].has(stepId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].set(stepId, main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
					<div class="bizproc__ws_start__breadcrumbs-item${0}">
						<span>${0}</span>
						<span class="ui-icon-set --chevron-right"></span>
					</div>
				`), item.active ? ' --active' : '', main_core.Text.encode(item.text)));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].get(stepId);
	}
	function _markNotActive2(stepId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].has(stepId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].get(stepId).active = false;
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].get(stepId), '--active');
	  }
	}
	function _markActive2(stepId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].has(stepId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].get(stepId).active = true;
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].get(stepId), '--active');
	  }
	}
	function _markComplete2(stepId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].has(stepId)) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].get(stepId), '--complete');
	  }
	}
	function _markNotComplete2(stepId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].has(stepId)) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _itemsNode)[_itemsNode].get(stepId), '--complete');
	  }
	}

	var _buttons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttons");
	var _sequenceSteps$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceSteps");
	var _currentStepId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentStepId");
	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _currentStepButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentStepButtons");
	class Buttons {
	  static createNextButton(action) {
	    return new ui_buttons.Button({
	      id: 'next',
	      text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_NEXT_BUTTON')),
	      onclick: action,
	      color: ui_buttons.ButtonColor.PRIMARY
	    });
	  }
	  static createBackButton(action) {
	    return new ui_buttons.Button({
	      id: 'back',
	      text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BACK_BUTTON')),
	      onclick: action,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER
	    });
	  }
	  static createStartButton(action) {
	    return new ui_buttons.Button({
	      id: 'start',
	      text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_START_BUTTON')),
	      onclick: action,
	      color: ui_buttons.ButtonColor.PRIMARY
	    });
	  }
	  constructor(config) {
	    Object.defineProperty(this, _currentStepButtons, {
	      get: _get_currentStepButtons,
	      set: void 0
	    });
	    Object.defineProperty(this, _buttons, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _sequenceSteps$1, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _currentStepId$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: void 0
	    });
	    if (main_core.Type.isPlainObject(config.buttons)) {
	      Object.entries(config.buttons).forEach(([stepId, buttons]) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].set(stepId, buttons);
	        babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].push(stepId);
	      });
	      if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1])) {
	        babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].at(0);
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = config.wrapper;
	  }
	  next() {
	    const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1]);
	    if (index !== -1 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].at(index + 1))) {
	      babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].at(index + 1);
	      this.show();
	    }
	  }
	  back() {
	    const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1]);
	    if (index !== -1 && index - 1 >= 0 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].at(index - 1))) {
	      babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$1)[_sequenceSteps$1].at(index - 1);
	      this.show();
	    }
	  }
	  show() {
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    const buttons = babelHelpers.classPrivateFieldLooseBase(this, _currentStepButtons)[_currentStepButtons];
	    if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _currentStepButtons)[_currentStepButtons])) {
	      main_core.Dom.show(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	      buttons.forEach(button => {
	        button.renderTo(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	      });
	    } else {
	      main_core.Dom.hide(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    }
	  }
	  resolveEnableState(enable) {
	    babelHelpers.classPrivateFieldLooseBase(this, _currentStepButtons)[_currentStepButtons].forEach(button => {
	      if (main_core.Type.isBoolean(enable[button.getId()])) {
	        button.setDisabled(!enable[button.getId()]);
	      }
	    });
	  }
	  resolveWaitingState(waiting) {
	    babelHelpers.classPrivateFieldLooseBase(this, _currentStepButtons)[_currentStepButtons].forEach(button => {
	      if (main_core.Type.isBoolean(waiting[button.getId()])) {
	        button.setWaiting(waiting[button.getId()]);
	      }
	    });
	  }
	}
	function _get_currentStepButtons() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].has(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1]) ? babelHelpers.classPrivateFieldLooseBase(this, _buttons)[_buttons].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$1)[_currentStepId$1]) : [];
	}

	let _$2 = t => t,
	  _t$2;
	var _errors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errors");
	var _element = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("element");
	var _renderErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderErrors");
	class ErrorNotifier {
	  constructor(props) {
	    Object.defineProperty(this, _renderErrors, {
	      value: _renderErrors2
	    });
	    Object.defineProperty(this, _errors, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _element, {
	      writable: true,
	      value: void 0
	    });
	    this.errors = props.errors;
	  }
	  set errors(errors) {
	    if (main_core.Type.isArray(errors)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors] = errors;
	    }
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _element)[_element] = main_core.Tag.render(_t$2 || (_t$2 = _$2`<div>${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _renderErrors)[_renderErrors]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _element)[_element];
	  }
	  show(scrollToElement = true) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]) {
	      this.clean();
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderErrors)[_renderErrors](), babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]);
	      if (scrollToElement) {
	        // eslint-disable-next-line @bitrix24/bitrix24-rules/no-bx
	        BX.scrollToNode(babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]);
	      }
	    }
	  }
	  clean() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _element)[_element]);
	    }
	  }
	}
	function _renderErrors2() {
	  if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors])) {
	    const message = babelHelpers.classPrivateFieldLooseBase(this, _errors)[_errors].map(error => main_core.Text.encode(error.message || '')).join('<br/>');
	    return new ui_alerts.Alert({
	      text: message,
	      color: ui_alerts.AlertColor.DANGER
	    }).render();
	  }
	  return null;
	}

	function showExitDialog(onConfirm, onCancel) {
	  const messageBox = ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EXIT_DIALOG_DESCRIPTION'), main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EXIT_DIALOG_TITLE'), onConfirm, main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EXIT_DIALOG_CONFIRM'), main_core.Type.isFunction(onCancel) ? onCancel : () => true, main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EXIT_DIALOG_CANCEL'));
	  if (main_core.Type.isFunction(onCancel)) {
	    const popup = messageBox.getPopupWindow();
	    popup.subscribe('onClose', onCancel);
	  }
	}

	function addMissingFormDataValues(target, source) {
	  const addedKeys = new Set();
	  for (const [key, value] of source.entries()) {
	    if (!target.has(key) || addedKeys.has(key)) {
	      addedKeys.add(key);
	      target.append(key, value);
	    }
	  }
	}

	function isEqualsFormData(form1, form2) {
	  for (const key of form1.keys()) {
	    if (!form2.has(key)) {
	      return false;
	    }
	    const values1 = form1.getAll(key);
	    const values2 = form2.getAll(key);
	    if (values1.length !== values2.length) {
	      return false;
	    }
	    for (const singleKey of values1.keys()) {
	      let value1 = values1.at(singleKey);
	      let value2 = values2.at(singleKey);
	      if (main_core.Type.isFile(value1)) {
	        value1 = value1.name;
	        value2 = value2.name;
	      }
	      if (value1 !== value2) {
	        return false;
	      }
	    }
	  }
	  return true;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$2;
	function renderBpForm(formName, title, fields, documentType, description, signedDocumentId) {
	  let context = {};
	  if (main_core.Type.isStringFilled(signedDocumentId)) {
	    context = {
	      isStartWorkflow: true,
	      signedDocumentId
	    };
	  }
	  const controls = BX.Bizproc.FieldType.renderControlCollection(documentType, fields.map(field => ({
	    property: field,
	    fieldName: field.Id,
	    value: field.Default,
	    controlId: field.Id
	  })), 'public', context);
	  return main_core.Tag.render(_t$3 || (_t$3 = _$3`
		<form name="${0}">
			<div class="bizproc__ws_start__content-form-title-block">
				<div class="bizproc__ws_start__content-form-title">${0}</div>
				<div class="bizproc__ws_start__content-form-description">${0}</div>
			</div>
				${0}
		</form>
	`), formName, main_core.Text.encode(title), main_core.Text.encode(description), fields.map(property => {
	    const control = main_core.Type.isElementNode(controls[property.Id]) ? controls[property.Id] : BX.Bizproc.FieldType.renderControlPublic(documentType, property, property.Id, property.Default, false);
	    return renderBpFieldForForm(property, control);
	  }));
	}
	function renderBpFieldForForm(property, control) {
	  return main_core.Tag.render(_t2$2 || (_t2$2 = _$3`
		<div class="bizproc__ws_start__content-form-block">
			<div class="ui-ctl-title${0}">
				${0}
			</div>
			${0}
		</div>
	`), main_core.Text.toBoolean(property.Required) ? ' --required' : '', main_core.Text.encode(property.Name), control);
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$3;
	class Step extends main_core_events.EventEmitter {
	  constructor(config) {
	    super();
	    this.setEventNamespace('BX.Bizproc.Component.WorkflowSingleStart.Step');
	    if (this.constructor === Step) {
	      throw new Error('Object of Abstract Class cannot be created');
	    }
	    this.name = config.name;
	  }
	  render() {
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
			<div class="bizproc__ws_start__content">
				${0}
				${0}
				${0}
			</div>
		`), this.renderHead(), this.renderBody(), this.renderFooter());
	  }
	  renderHead() {
	    return main_core.Tag.render(_t2$3 || (_t2$3 = _$4`
			<div class="bizproc__ws_start__content-head">
				<div class="bizproc__ws_start__content-title">
					${0}
				</div>
			</div>
		`), main_core.Text.encode(this.name));
	  }
	  renderBody() {
	    throw new Error('Abstract Method has no implementation');
	  }
	  renderFooter() {
	    return null;
	  }
	  isNextEnabled() {
	    return true;
	  }
	  onBeforeNextStep() {
	    return Promise.resolve();
	  }
	  isBackEnabled() {
	    return true;
	  }
	  onChangeStepAvailability() {
	    this.emit('onChangeStepAvailability');
	  }
	  onAfterRender() {}
	  canExit() {
	    return true;
	  }
	}

	class StepWithErrors extends Step {
	  constructor(config) {
	    super(config);
	    this.errorNotifier = new ErrorNotifier({});
	  }
	  renderErrors() {
	    return this.errorNotifier.render();
	  }
	  showErrors(errors) {
	    if (main_core.Type.isArrayFilled(errors)) {
	      this.errorNotifier.errors = errors;
	      this.errorNotifier.show();
	    }
	  }
	  cleanErrors() {
	    this.errorNotifier.errors = [];
	    this.errorNotifier.clean();
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$4;
	const FORM_NAME = 'bizproc-ws-single-start-constants';
	var _constants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("constants");
	var _documentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentType");
	var _signedDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentType");
	var _signedDocumentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentId");
	var _templateId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templateId");
	var _body = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("body");
	var _form = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("form");
	var _isConstantsTuned = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isConstantsTuned");
	var _originalFormData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("originalFormData");
	var _hasConstants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasConstants");
	var _renderStub = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStub");
	var _renderConstants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderConstants");
	var _subscribeOnRenderEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnRenderEvents");
	var _onAfterFieldCollectionRenderer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterFieldCollectionRenderer");
	var _renderSaveButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSaveButton");
	var _handleSaveClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSaveClick");
	class ConstantsStep extends StepWithErrors {
	  constructor(config) {
	    super(config);
	    Object.defineProperty(this, _handleSaveClick, {
	      value: _handleSaveClick2
	    });
	    Object.defineProperty(this, _renderSaveButton, {
	      value: _renderSaveButton2
	    });
	    Object.defineProperty(this, _onAfterFieldCollectionRenderer, {
	      value: _onAfterFieldCollectionRenderer2
	    });
	    Object.defineProperty(this, _subscribeOnRenderEvents, {
	      value: _subscribeOnRenderEvents2
	    });
	    Object.defineProperty(this, _renderConstants, {
	      value: _renderConstants2
	    });
	    Object.defineProperty(this, _renderStub, {
	      value: _renderStub2
	    });
	    Object.defineProperty(this, _hasConstants, {
	      get: _get_hasConstants,
	      set: void 0
	    });
	    Object.defineProperty(this, _constants, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _documentType, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _signedDocumentType, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _templateId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _body, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _form, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isConstantsTuned, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _originalFormData, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType] = config.documentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType] = config.signedDocumentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId] = config.signedDocumentId;
	    babelHelpers.classPrivateFieldLooseBase(this, _templateId)[_templateId] = main_core.Text.toInteger(config.templateId);
	    if (main_core.Type.isArrayFilled(config.constants)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _constants)[_constants] = config.constants;
	    }
	  }
	  renderBody() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _body)[_body]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _body)[_body] = main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="bizproc__ws_start__content-body">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _hasConstants)[_hasConstants] ? babelHelpers.classPrivateFieldLooseBase(this, _renderConstants)[_renderConstants]() : babelHelpers.classPrivateFieldLooseBase(this, _renderStub)[_renderStub]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _body)[_body];
	  }
	  isNextEnabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _isConstantsTuned)[_isConstantsTuned];
	  }
	  canExit() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _hasConstants)[_hasConstants] || !babelHelpers.classPrivateFieldLooseBase(this, _originalFormData)[_originalFormData] || babelHelpers.classPrivateFieldLooseBase(this, _isConstantsTuned)[_isConstantsTuned]) {
	      return true;
	    }
	    return isEqualsFormData(new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]), babelHelpers.classPrivateFieldLooseBase(this, _originalFormData)[_originalFormData]);
	  }
	}
	function _get_hasConstants() {
	  return main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _constants)[_constants]);
	}
	function _renderStub2() {
	  return new ui_alerts.Alert({
	    text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_NOT_TUNING_CONSTANTS')),
	    color: ui_alerts.AlertColor.WARNING,
	    icon: ui_alerts.AlertIcon.INFO
	  }).render();
	}
	function _renderConstants2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _form)[_form] = renderBpForm(FORM_NAME, this.name, babelHelpers.classPrivateFieldLooseBase(this, _constants)[_constants], babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType], null, babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId]);
	  main_core.Dom.append(this.renderErrors(), babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]);
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderSaveButton)[_renderSaveButton](), babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]);
	  babelHelpers.classPrivateFieldLooseBase(this, _originalFormData)[_originalFormData] = new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]);
	  babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnRenderEvents)[_subscribeOnRenderEvents]();
	  return main_core.Tag.render(_t2$4 || (_t2$4 = _$5`<div class="bizproc__ws_start__content-form">${0}</div>`), babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]);
	}
	function _subscribeOnRenderEvents2() {
	  main_core_events.EventEmitter.subscribe('BX.Bizproc.FieldType.onCustomRenderControlFinished', babelHelpers.classPrivateFieldLooseBase(this, _onAfterFieldCollectionRenderer)[_onAfterFieldCollectionRenderer].bind(this));
	  main_core_events.EventEmitter.subscribe('BX.Bizproc.FieldType.onCollectionRenderControlFinished', babelHelpers.classPrivateFieldLooseBase(this, _onAfterFieldCollectionRenderer)[_onAfterFieldCollectionRenderer].bind(this));
	}
	function _onAfterFieldCollectionRenderer2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _originalFormData)[_originalFormData] && document.forms.namedItem(FORM_NAME)) {
	    addMissingFormDataValues(babelHelpers.classPrivateFieldLooseBase(this, _originalFormData)[_originalFormData], new FormData(document.forms.namedItem(FORM_NAME)));
	  }
	}
	function _renderSaveButton2() {
	  return new ui_buttons.Button({
	    text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BUTTON_SAVE')),
	    size: ui_buttons.ButtonSize.EXTRA_SMALL,
	    color: ui_buttons.ButtonColor.SECONDARY,
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _handleSaveClick)[_handleSaveClick].bind(this)
	  }).render();
	}
	function _handleSaveClick2(button) {
	  button.setWaiting(true);
	  this.cleanErrors();
	  const data = new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form)[_form]);
	  data.set('templateId', babelHelpers.classPrivateFieldLooseBase(this, _templateId)[_templateId]);
	  data.set('signedDocumentType', babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType]);
	  main_core.ajax.runAction('bizproc.workflow.starter.setConstants', {
	    data
	  }).then(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _isConstantsTuned)[_isConstantsTuned] = true;
	    this.onChangeStepAvailability();
	    button.setWaiting(false);
	  }).catch(response => {
	    this.showErrors(response.errors);
	    button.setWaiting(false);
	  });
	}

	function startWorkflowAction(data) {
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runAction('bizproc.workflow.starter.startWorkflow', {
	      data
	    }).then(response => {
	      const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	      if (slider) {
	        const dictionary = slider.getData();
	        dictionary.set('data', {
	          workflowId: response.data.workflowId
	        });
	      }
	      resolve(response);
	    }).catch(reject);
	  });
	}

	let _$6 = t => t,
	  _t$6;
	const FORM_NAME$1 = 'bizproc-ws-single-start-parameters';
	var _parameters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parameters");
	var _documentType$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentType");
	var _signedDocumentId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentId");
	var _signedDocumentType$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentType");
	var _templateId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templateId");
	var _body$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("body");
	var _form$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("form");
	var _originalFormData$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("originalFormData");
	var _isSent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSent");
	var _startTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startTime");
	var _renderParametersForm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderParametersForm");
	var _subscribeOnRenderEvents$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnRenderEvents");
	var _onAfterFieldCollectionRenderer$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAfterFieldCollectionRenderer");
	class ParametersStep extends StepWithErrors {
	  constructor(config) {
	    super(config);
	    Object.defineProperty(this, _onAfterFieldCollectionRenderer$1, {
	      value: _onAfterFieldCollectionRenderer2$1
	    });
	    Object.defineProperty(this, _subscribeOnRenderEvents$1, {
	      value: _subscribeOnRenderEvents2$1
	    });
	    Object.defineProperty(this, _renderParametersForm, {
	      value: _renderParametersForm2
	    });
	    Object.defineProperty(this, _parameters, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _documentType$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _signedDocumentId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentType$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _templateId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _body$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _form$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _originalFormData$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isSent, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _startTime, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType$1)[_documentType$1] = config.documentType;
	    if (main_core.Type.isArrayFilled(config.parameters)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _parameters)[_parameters] = config.parameters;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _templateId$1)[_templateId$1] = main_core.Text.toInteger(config.templateId);
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$1)[_signedDocumentType$1] = config.signedDocumentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$1)[_signedDocumentId$1] = config.signedDocumentId;
	    babelHelpers.classPrivateFieldLooseBase(this, _startTime)[_startTime] = Math.round(Date.now() / 1000);
	  }
	  renderBody() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _body$1)[_body$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _body$1)[_body$1] = main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="bizproc__ws_start__content-body">
					${0}
					<div class="bizproc__ws_start__content-form">
						${0}
					</div>
				</div>
			`), this.renderErrors(), babelHelpers.classPrivateFieldLooseBase(this, _renderParametersForm)[_renderParametersForm]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _body$1)[_body$1];
	  }
	  canExit() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _originalFormData$1)[_originalFormData$1] || babelHelpers.classPrivateFieldLooseBase(this, _isSent)[_isSent]) {
	      return true;
	    }
	    return isEqualsFormData(new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form$1)[_form$1]), babelHelpers.classPrivateFieldLooseBase(this, _originalFormData$1)[_originalFormData$1]);
	  }
	  onBeforeNextStep() {
	    this.cleanErrors();
	    const data = new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form$1)[_form$1]);
	    data.set('templateId', babelHelpers.classPrivateFieldLooseBase(this, _templateId$1)[_templateId$1]);
	    data.set('signedDocumentType', babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$1)[_signedDocumentType$1]);
	    data.set('signedDocumentId', babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$1)[_signedDocumentId$1]);
	    data.set('startDuration', Math.round(Date.now() / 1000) - babelHelpers.classPrivateFieldLooseBase(this, _startTime)[_startTime]);
	    return new Promise((resolve, reject) => {
	      startWorkflowAction(data).then(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _isSent)[_isSent] = true;
	        resolve();
	      }).catch(response => {
	        this.showErrors(response.errors);
	        reject();
	      });
	    });
	  }
	}
	function _renderParametersForm2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _form$1)[_form$1] = renderBpForm(FORM_NAME$1, main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_PARAMETERS_TITLE'), babelHelpers.classPrivateFieldLooseBase(this, _parameters)[_parameters], babelHelpers.classPrivateFieldLooseBase(this, _documentType$1)[_documentType$1], null, babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$1)[_signedDocumentId$1]);
	  babelHelpers.classPrivateFieldLooseBase(this, _originalFormData$1)[_originalFormData$1] = new FormData(babelHelpers.classPrivateFieldLooseBase(this, _form$1)[_form$1]);
	  babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnRenderEvents$1)[_subscribeOnRenderEvents$1]();
	  return babelHelpers.classPrivateFieldLooseBase(this, _form$1)[_form$1];
	}
	function _subscribeOnRenderEvents2$1() {
	  main_core_events.EventEmitter.subscribe('BX.Bizproc.FieldType.onCustomRenderControlFinished', babelHelpers.classPrivateFieldLooseBase(this, _onAfterFieldCollectionRenderer$1)[_onAfterFieldCollectionRenderer$1].bind(this));
	  main_core_events.EventEmitter.subscribe('BX.Bizproc.FieldType.onCollectionRenderControlFinished', babelHelpers.classPrivateFieldLooseBase(this, _onAfterFieldCollectionRenderer$1)[_onAfterFieldCollectionRenderer$1].bind(this));
	}
	function _onAfterFieldCollectionRenderer2$1() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _originalFormData$1)[_originalFormData$1] && document.forms.namedItem(FORM_NAME$1)) {
	    addMissingFormDataValues(babelHelpers.classPrivateFieldLooseBase(this, _originalFormData$1)[_originalFormData$1], new FormData(document.forms.namedItem(FORM_NAME$1)));
	  }
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$5,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6,
	  _t7,
	  _t8;
	var _body$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("body");
	var _recommendation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recommendation");
	var _recommendationElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("recommendationElement");
	var _expandElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("expandElement");
	var _freeHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("freeHeight");
	var _duration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("duration");
	var _isHeightFixed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isHeightFixed");
	var _hasRecommendation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasRecommendation");
	var _hasDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasDuration");
	var _getFreeHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFreeHeight");
	var _fixRecommendationHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fixRecommendationHeight");
	var _renderRecommendation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRecommendation");
	var _renderEmptyRecommendation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderEmptyRecommendation");
	var _renderExpandElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpandElement");
	var _toggleRecommendation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleRecommendation");
	var _renderDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDuration");
	var _renderLinkToArticle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLinkToArticle");
	class RecommendationStep extends Step {
	  constructor(config) {
	    super(config);
	    Object.defineProperty(this, _renderLinkToArticle, {
	      value: _renderLinkToArticle2
	    });
	    Object.defineProperty(this, _renderDuration, {
	      value: _renderDuration2
	    });
	    Object.defineProperty(this, _toggleRecommendation, {
	      value: _toggleRecommendation2
	    });
	    Object.defineProperty(this, _renderExpandElement, {
	      value: _renderExpandElement2
	    });
	    Object.defineProperty(this, _renderEmptyRecommendation, {
	      value: _renderEmptyRecommendation2
	    });
	    Object.defineProperty(this, _renderRecommendation, {
	      value: _renderRecommendation2
	    });
	    Object.defineProperty(this, _fixRecommendationHeight, {
	      value: _fixRecommendationHeight2
	    });
	    Object.defineProperty(this, _getFreeHeight, {
	      value: _getFreeHeight2
	    });
	    Object.defineProperty(this, _hasDuration, {
	      get: _get_hasDuration,
	      set: void 0
	    });
	    Object.defineProperty(this, _hasRecommendation, {
	      get: _get_hasRecommendation,
	      set: void 0
	    });
	    Object.defineProperty(this, _body$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _recommendation, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _recommendationElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _expandElement, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _freeHeight, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _duration, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _isHeightFixed, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _recommendation)[_recommendation] = String(config.recommendation).trim();
	    if (!main_core.Type.isNil(config.duration)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration] = main_core.Text.toInteger(config.duration);
	    }
	  }
	  onAfterRender() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isHeightFixed)[_isHeightFixed]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _fixRecommendationHeight)[_fixRecommendationHeight]();
	      babelHelpers.classPrivateFieldLooseBase(this, _isHeightFixed)[_isHeightFixed] = true;
	    }
	  }
	  renderBody() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _body$2)[_body$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _body$2)[_body$2] = main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="bizproc__ws_start__content-body">
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _renderRecommendation)[_renderRecommendation](), babelHelpers.classPrivateFieldLooseBase(this, _renderExpandElement)[_renderExpandElement]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _body$2)[_body$2];
	  }
	  renderFooter() {
	    return main_core.Tag.render(_t2$5 || (_t2$5 = _$7`
			<div class="bizproc__ws_single-start__informer">
				<div class="bizproc__ws_single-start__informer-header">
					<div class="bizproc__ws_single-start__informer-title">
						${0}
					</div>
					${0}
				</div>
				<div class="bizproc__ws_single-start__informer-message">
					${0}
				</div>
				<div class="bizproc__ws_single-start__informer-bottom">
					${0}
				</div>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_AVERAGE_DURATION_TITLE')), babelHelpers.classPrivateFieldLooseBase(this, _renderDuration)[_renderDuration](), main_core.Text.encode(main_core.Loc.getMessage(babelHelpers.classPrivateFieldLooseBase(this, _hasDuration)[_hasDuration] ? 'BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_DURATION_DESCRIPTION' : 'BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_DURATION_UNDEFINED_DESCRIPTION')), babelHelpers.classPrivateFieldLooseBase(this, _hasDuration)[_hasDuration] ? babelHelpers.classPrivateFieldLooseBase(this, _renderLinkToArticle)[_renderLinkToArticle]() : null);
	  }
	}
	function _get_hasRecommendation() {
	  return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _recommendation)[_recommendation]);
	}
	function _get_hasDuration() {
	  return !main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration]);
	}
	function _getFreeHeight2() {
	  if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _freeHeight)[_freeHeight])) {
	    const slider = document.querySelector('.ui-page-slider-workarea-content-padding');
	    babelHelpers.classPrivateFieldLooseBase(this, _freeHeight)[_freeHeight] = slider ? slider.offsetHeight - window.innerHeight : 0;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _freeHeight)[_freeHeight];
	}
	function _fixRecommendationHeight2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement] && babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement]) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getFreeHeight)[_getFreeHeight]() <= 0) {
	      main_core.Event.unbindAll(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement], 'click');
	      main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement]);
	      babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement] = null;
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _toggleRecommendation)[_toggleRecommendation]();
	    }
	  }
	}
	function _renderRecommendation2() {
	  const recommendation = babelHelpers.classPrivateFieldLooseBase(this, _hasRecommendation)[_hasRecommendation] ? BX.util.nl2br(main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _recommendation)[_recommendation])) : babelHelpers.classPrivateFieldLooseBase(this, _renderEmptyRecommendation)[_renderEmptyRecommendation]();
	  babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement] = main_core.Tag.render(_t3$1 || (_t3$1 = _$7`
			<div class="bizproc__ws_single-start__content-wrapper">
				${0}
			</div>
		`), recommendation);
	  return babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement];
	}
	function _renderEmptyRecommendation2() {
	  return main_core.Tag.render(_t4$1 || (_t4$1 = _$7`
			<div class="bizproc__ws_single-start__empty-recommendation">
				<svg width="172" height="172" viewBox="0 0 172 172" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path opacity="0.5" d="M137.617 121.056C137.617 123.661 135.505 125.773 132.899 125.773C130.294 125.773 128.182 123.661 128.182 121.056C128.182 118.45 130.294 116.338 132.899 116.338C135.505 116.338 137.617 118.45 137.617 121.056Z" fill="#2FC6F6"/>
					<path opacity="0.2" fill-rule="evenodd" clip-rule="evenodd" d="M152.713 121.056C152.713 132 143.842 140.871 132.899 140.871C123.946 140.871 116.38 134.933 113.924 126.78H117.91C120.215 132.812 126.057 137.096 132.899 137.096C141.758 137.096 148.939 129.915 148.939 121.056C148.939 112.198 141.758 105.016 132.899 105.016C126.057 105.016 120.215 109.3 117.91 115.333H113.924C116.38 107.18 123.946 101.242 132.899 101.242C143.842 101.242 152.713 110.113 152.713 121.056Z" fill="#2FC6F6"/>
					<path opacity="0.3" fill-rule="evenodd" clip-rule="evenodd" d="M145.164 121.057C145.164 127.831 139.673 133.323 132.898 133.323C128.191 133.323 124.103 130.672 122.047 126.781H126.626C128.178 128.482 130.414 129.549 132.898 129.549C137.588 129.549 141.39 125.747 141.39 121.057C141.39 116.367 137.588 112.565 132.898 112.565C130.414 112.565 128.178 113.632 126.625 115.333H122.047C124.104 111.442 128.191 108.791 132.898 108.791C139.673 108.791 145.164 114.283 145.164 121.057Z" fill="#2FC6F6"/>
					<g opacity="0.3">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M135.652 51.1387L133.678 51.1387V49.6387L135.652 49.6387C136.431 49.6387 137.175 49.7937 137.854 50.0753L137.279 51.4609C136.779 51.2535 136.23 51.1387 135.652 51.1387ZM129.73 51.1387L125.781 51.1387V49.6387L129.73 49.6387V51.1387ZM121.833 51.1387L117.884 51.1387V49.6387L121.833 49.6387V51.1387ZM113.936 51.1387L109.988 51.1387V49.6387L113.936 49.6387V51.1387ZM106.039 51.1387L102.091 51.1387L102.091 49.6387L106.039 49.6387L106.039 51.1387ZM98.1422 51.1387L96.168 51.1387C95.7538 51.1387 95.418 50.8029 95.418 50.3887C95.418 49.9745 95.7538 49.6387 96.168 49.6387L98.1422 49.6387L98.1422 51.1387ZM139.902 55.3887C139.902 54.811 139.788 54.2621 139.58 53.762L140.966 53.1874C141.247 53.8665 141.402 54.6104 141.402 55.3887V57.2499H139.902V55.3887ZM139.902 64.6948V60.9724H141.402V64.6948H139.902ZM139.902 72.1397V68.4173H141.402V72.1397H139.902ZM139.902 77.7234V75.8622H141.402V77.7234C141.402 78.3068 141.345 78.8776 141.236 79.4303L139.764 79.1392C139.855 78.6819 139.902 78.2086 139.902 77.7234ZM136.68 83.7527C137.471 83.2232 138.152 82.542 138.682 81.7511L139.928 82.5856C139.29 83.5395 138.469 84.3606 137.515 84.9992L136.68 83.7527ZM132.652 84.9734C133.138 84.9734 133.611 84.9259 134.068 84.8354L134.359 86.3069C133.807 86.4162 133.236 86.4734 132.652 86.4734H131.026V84.9734H132.652ZM119.64 84.9734H121.267V86.4734H119.64V84.9734ZM124.52 84.9734H127.773V86.4734H124.52V84.9734Z" fill="#2FC6F6"/>
						<path d="M98.1719 50.3926C98.1719 51.4971 97.2764 52.3926 96.1719 52.3926C95.0673 52.3926 94.1719 51.4971 94.1719 50.3926C94.1719 49.288 95.0673 48.3926 96.1719 48.3926C97.2764 48.3926 98.1719 49.288 98.1719 50.3926Z" fill="#2FC6F6"/>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M24.7566 108.84V106.95H26.2566V108.84H24.7566ZM24.7566 103.171V99.3921H26.2566V103.171H24.7566ZM24.7566 95.613V93.7235C24.7566 93.14 24.8138 92.5692 24.9232 92.0166L26.3947 92.3077C26.3042 92.765 26.2566 93.2383 26.2566 93.7235V95.613H24.7566ZM26.2309 88.8613C26.8695 87.9074 27.6906 87.0863 28.6445 86.4477L29.479 87.6942C28.688 88.2237 28.0069 88.9048 27.4773 89.6958L26.2309 88.8613ZM31.7998 85.14C32.3524 85.0307 32.9232 84.9735 33.5066 84.9735H36.0597V86.4735H33.5066C33.0215 86.4735 32.5482 86.521 32.0909 86.6115L31.7998 85.14ZM41.1657 84.9735H43.7188V86.4735H41.1657V84.9735Z" fill="#2FC6F6"/>
						<path d="M41.8867 85.7227C41.8867 86.8272 40.9913 87.7227 39.8867 87.7227C38.7821 87.7227 37.8867 86.8272 37.8867 85.7227C37.8867 84.6181 38.7821 83.7227 39.8867 83.7227C40.9913 83.7227 41.8867 84.6181 41.8867 85.7227Z" fill="#2FC6F6"/>
						<path d="M126.154 83.1855C126.154 82.347 125.184 81.8808 124.53 82.4046L121.357 84.9425C120.857 85.3428 120.857 86.1039 121.357 86.5042L124.53 89.0421C125.184 89.566 126.154 89.0998 126.154 88.2613V83.1855Z" fill="#2FC6F6"/>
						<path d="M28.0841 104.461C28.9226 104.461 29.3887 105.431 28.8649 106.086L26.327 109.258C25.9267 109.758 25.1656 109.758 24.7653 109.258L22.2274 106.086C21.7036 105.431 22.1697 104.461 23.0083 104.461L28.0841 104.461Z" fill="#2FC6F6"/>
					</g>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M121.136 123.595C121.136 124.434 122.105 124.9 122.76 124.376L125.933 121.838C126.433 121.438 126.433 120.677 125.933 120.276L122.76 117.739C122.105 117.215 121.136 117.681 121.136 118.519V120.307L119.401 120.307L119.401 121.807L121.136 121.807V123.595ZM115.499 121.807L111.596 121.807L111.596 120.307L115.499 120.307V121.807ZM107.694 121.807L103.792 121.807L103.792 120.307L107.694 120.307L107.694 121.807ZM98.0226 120.307C97.726 119.574 97.0073 119.057 96.168 119.057C95.0634 119.057 94.168 119.953 94.168 121.057C94.168 122.162 95.0634 123.057 96.168 123.057C97.0073 123.057 97.7258 122.54 98.0226 121.807L99.8894 121.807L99.8894 120.307L98.0226 120.307Z" fill="url(#paint0_linear_5779_78783)"/>
					<g filter="url(#filter0_d_5779_78783)">
						<path d="M18.8066 44.6914C18.8066 41.3777 21.4929 38.6914 24.8066 38.6914H90.167C93.4807 38.6914 96.167 41.3777 96.167 44.6914V56.7393C96.167 60.053 93.4807 62.7393 90.167 62.7393H24.8066C21.4929 62.7393 18.8066 60.053 18.8066 56.7393V44.6914Z" fill="white"/>
					</g>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M90.167 39.6914H24.8066C22.0452 39.6914 19.8066 41.93 19.8066 44.6914V56.7393C19.8066 59.5007 22.0452 61.7393 24.8066 61.7393H90.167C92.9284 61.7393 95.167 59.5007 95.167 56.7393V44.6914C95.167 41.93 92.9284 39.6914 90.167 39.6914ZM24.8066 38.6914C21.4929 38.6914 18.8066 41.3777 18.8066 44.6914V56.7393C18.8066 60.053 21.4929 62.7393 24.8066 62.7393H90.167C93.4807 62.7393 96.167 60.053 96.167 56.7393V44.6914C96.167 41.3777 93.4807 38.6914 90.167 38.6914H24.8066Z" fill="#1EC6FA"/>
					<path opacity="0.3" d="M44.293 50.8101C44.293 49.8535 45.0684 49.0781 46.0249 49.0781H76.0454C77.0019 49.0781 77.7773 49.8535 77.7773 50.8101C77.7773 51.7666 77.0019 52.542 76.0454 52.542H46.0249C45.0684 52.542 44.293 51.7666 44.293 50.8101Z" fill="#2FC6F6"/>
					<path opacity="0.56" fill-rule="evenodd" clip-rule="evenodd" d="M33.1615 56.9988C36.5795 56.9988 39.3503 54.2279 39.3503 50.8099C39.3503 47.3919 36.5795 44.6211 33.1615 44.6211C29.7435 44.6211 26.9727 47.3919 26.9727 50.8099C26.9727 54.2279 29.7435 56.9988 33.1615 56.9988ZM36.2499 48.4132C35.9788 48.1421 35.5392 48.1421 35.2681 48.4132L32.2547 51.4267L31.0536 50.2256C30.7827 49.9547 30.3435 49.9547 30.0726 50.2256C29.8017 50.4965 29.8017 50.9357 30.0726 51.2066L31.7648 52.8987C32.0357 53.1696 32.4749 53.1696 32.7458 52.8987L36.2499 49.395C36.521 49.1239 36.521 48.6843 36.2499 48.4132Z" fill="#2FC6F6"/>
					<g filter="url(#filter1_d_5779_78783)">
						<path d="M45.3302 74.8923C46.2308 73.3741 47.8652 72.4434 49.6304 72.4434H111.547C113.328 72.4434 114.975 73.3907 115.87 74.9304L120.39 82.7061C121.474 84.5704 121.474 86.8729 120.39 88.7372L115.87 96.5129C114.975 98.0526 113.328 98.9999 111.547 98.9999H49.6542C47.8762 98.9999 46.232 98.0557 45.3358 96.5202L40.1566 87.6458C39.4244 86.3912 39.43 84.8382 40.1711 83.5888L45.3302 74.8923Z" fill="white"/>
					</g>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M111.547 73.4434H49.6304C48.2183 73.4434 46.9107 74.188 46.1902 75.4025L41.0312 84.099C40.4753 85.036 40.4711 86.2008 41.0203 87.1418L46.1995 96.0161C46.9164 97.2446 48.2318 97.9999 49.6542 97.9999H111.547C112.972 97.9999 114.289 97.242 115.005 96.0103L119.526 88.2346C120.429 86.681 120.429 84.7622 119.526 83.2086L115.005 75.433C114.289 74.2012 112.972 73.4434 111.547 73.4434ZM49.6304 72.4434C47.8652 72.4434 46.2308 73.3741 45.3302 74.8923L40.1711 83.5888C39.43 84.8382 39.4244 86.3912 40.1566 87.6458L45.3358 96.5202C46.232 98.0557 47.8762 98.9999 49.6542 98.9999H111.547C113.328 98.9999 114.975 98.0526 115.87 96.5129L120.39 88.7372C121.474 86.8729 121.474 84.5704 120.39 82.7061L115.87 74.9304C114.975 73.3907 113.328 72.4434 111.547 72.4434H49.6304Z" fill="#1EC6FA"/>
					<path opacity="0.3" d="M69.0293 85.7222C69.0293 84.7657 69.8047 83.9902 70.7612 83.9902H100.782C101.738 83.9902 102.514 84.7657 102.514 85.7222C102.514 86.6787 101.738 87.4541 100.782 87.4541H70.7612C69.8047 87.4541 69.0293 86.6787 69.0293 85.7222Z" fill="#2FC6F6"/>
					<path opacity="0.56" fill-rule="evenodd" clip-rule="evenodd" d="M57.8998 91.9109C61.3178 91.9109 64.0886 89.14 64.0886 85.722C64.0886 82.304 61.3178 79.5332 57.8998 79.5332C54.4818 79.5332 51.7109 82.304 51.7109 85.722C51.7109 89.14 54.4818 91.9109 57.8998 91.9109ZM60.9882 83.3253C60.7171 83.0542 60.2775 83.0542 60.0064 83.3253L56.993 86.3388L55.7919 85.1377C55.521 84.8668 55.0818 84.8668 54.8109 85.1377C54.54 85.4086 54.54 85.8478 54.8109 86.1187L56.5031 87.8109C56.774 88.0817 57.2132 88.0817 57.4841 87.8109L60.9882 84.3071C61.2593 84.036 61.2593 83.5965 60.9882 83.3253Z" fill="#2FC6F6"/>
					<g filter="url(#filter2_d_5779_78783)">
						<path d="M18.8066 114.807C18.8066 111.493 21.4929 108.807 24.8066 108.807H90.167C93.4807 108.807 96.167 111.493 96.167 114.807V127.306C96.167 130.62 93.4807 133.306 90.167 133.306H24.8066C21.4929 133.306 18.8066 130.62 18.8066 127.306V114.807Z" fill="white"/>
					</g>
					<path fill-rule="evenodd" clip-rule="evenodd" d="M90.167 109.807H24.8066C22.0452 109.807 19.8066 112.045 19.8066 114.807V127.306C19.8066 130.067 22.0452 132.306 24.8066 132.306H90.167C92.9284 132.306 95.167 130.067 95.167 127.306V114.807C95.167 112.045 92.9284 109.807 90.167 109.807ZM24.8066 108.807C21.4929 108.807 18.8066 111.493 18.8066 114.807V127.306C18.8066 130.62 21.4929 133.306 24.8066 133.306H90.167C93.4807 133.306 96.167 130.62 96.167 127.306V114.807C96.167 111.493 93.4807 108.807 90.167 108.807H24.8066Z" fill="#1EC6FA"/>
					<path opacity="0.3" d="M44.209 121.056C44.209 120.1 44.9844 119.324 45.9409 119.324H75.9614C76.9179 119.324 77.6933 120.1 77.6933 121.056C77.6933 122.013 76.9179 122.788 75.9614 122.788H45.9409C44.9844 122.788 44.209 122.013 44.209 121.056Z" fill="#2FC6F6"/>
					<path opacity="0.56" fill-rule="evenodd" clip-rule="evenodd" d="M33.0775 127.245C36.4955 127.245 39.2663 124.474 39.2663 121.056C39.2663 117.638 36.4955 114.867 33.0775 114.867C29.6595 114.867 26.8887 117.638 26.8887 121.056C26.8887 124.474 29.6595 127.245 33.0775 127.245ZM36.1659 118.659C35.8948 118.388 35.4553 118.388 35.1841 118.659L32.1707 121.673L30.9696 120.472C30.6987 120.201 30.2595 120.201 29.9886 120.472C29.7178 120.743 29.7178 121.182 29.9886 121.453L31.6808 123.145C31.9517 123.416 32.3909 123.416 32.6618 123.145L36.1659 119.641C36.4371 119.37 36.4371 118.93 36.1659 118.659Z" fill="#2FC6F6"/>
					<path d="M114.498 51.8009C113.717 51.0199 113.717 49.7536 114.498 48.9725L120.728 42.7429C121.509 41.9619 122.775 41.9619 123.556 42.7429L129.786 48.9725C130.567 49.7536 130.567 51.0199 129.786 51.8009L123.556 58.0305C122.775 58.8115 121.509 58.8115 120.728 58.0305L114.498 51.8009Z" fill="#2FC6F6"/>
					<defs>
						<filter id="filter0_d_5779_78783" x="15.8066" y="36.6914" width="83.3613" height="30.0469" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
							<feFlood flood-opacity="0" result="BackgroundImageFix"/>
							<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
							<feOffset dy="1"/>
							<feGaussianBlur stdDeviation="1.5"/>
							<feComposite in2="hardAlpha" operator="out"/>
							<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.294033 0 0 0 0 0.3875 0 0 0 0.09 0"/>
							<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5779_78783"/>
							<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5779_78783" result="shape"/>
						</filter>
						<filter id="filter1_d_5779_78783" x="36.6113" y="70.4434" width="87.5918" height="32.5566" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
							<feFlood flood-opacity="0" result="BackgroundImageFix"/>
							<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
							<feOffset dy="1"/>
							<feGaussianBlur stdDeviation="1.5"/>
							<feComposite in2="hardAlpha" operator="out"/>
							<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.294033 0 0 0 0 0.3875 0 0 0 0.09 0"/>
							<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5779_78783"/>
							<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5779_78783" result="shape"/>
						</filter>
						<filter id="filter2_d_5779_78783" x="15.8066" y="106.807" width="83.3613" height="30.5" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
							<feFlood flood-opacity="0" result="BackgroundImageFix"/>
							<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
							<feOffset dy="1"/>
							<feGaussianBlur stdDeviation="1.5"/>
							<feComposite in2="hardAlpha" operator="out"/>
							<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0.294033 0 0 0 0 0.3875 0 0 0 0.09 0"/>
							<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_5779_78783"/>
							<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_5779_78783" result="shape"/>
						</filter>
						<linearGradient id="paint0_linear_5779_78783" x1="93.418" y1="121.057" x2="129.388" y2="121.057" gradientUnits="userSpaceOnUse">
							<stop stop-color="#2FC6F6" stop-opacity="0.3"/>
							<stop offset="1" stop-color="#2FC6F6"/>
						</linearGradient>
					</defs>
				</svg>
				<span class="bizproc__ws_single-start__text-empty">
					${0}
				</span>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EMPTY_RECOMMENDATION')));
	}
	function _renderExpandElement2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasRecommendation)[_hasRecommendation]) {
	    return null;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement] = main_core.Tag.render(_t5$1 || (_t5$1 = _$7`
			<div class="bizproc__ws_single-start__content-open --expanded">
				${0}
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_COLLAPSE_RECOMMENDATION')));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement], 'click', babelHelpers.classPrivateFieldLooseBase(this, _toggleRecommendation)[_toggleRecommendation].bind(this));
	  return babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement];
	}
	function _toggleRecommendation2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement] && babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement]) {
	    main_core.Dom.toggleClass(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement], ['--expanded', '--collapsed']);
	    babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement].innerText = main_core.Loc.getMessage(main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement], '--expanded') ? 'BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_COLLAPSE_RECOMMENDATION' : 'BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EXPAND_RECOMMENDATION');
	    main_core.Dom.toggleClass(babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement], ['--hide']);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getFreeHeight)[_getFreeHeight]() > 0) {
	      const height = main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _expandElement)[_expandElement], '--expanded') ? `${babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement].scrollHeight}px` : `${babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement].offsetHeight - babelHelpers.classPrivateFieldLooseBase(this, _getFreeHeight)[_getFreeHeight]()}px`;
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _recommendationElement)[_recommendationElement], 'height', height);
	    }
	  }
	}
	function _renderDuration2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasDuration)[_hasDuration]) {
	    let formattedDuration = main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_ZERO_DURATION');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration] > 0) {
	      formattedDuration = main_date.DateTimeFormat.format([['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']], 0, babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration]);
	    }
	    return main_core.Tag.render(_t6 || (_t6 = _$7`
				<div class="bizproc__ws_single-start__informer-time">
					<span>${0}</span>
					<div class="ui-icon-set --time-picker"></div>
				</div>
			`), main_core.Text.encode(formattedDuration));
	  }
	  return main_core.Tag.render(_t7 || (_t7 = _$7`
			<div class="bizproc__ws_single-start__informer-time">
				<span class="bizproc__ws_single-start__text-empty">
					${0}
				</span>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_EMPTY_DURATION')));
	}
	function _renderLinkToArticle2() {
	  return main_core.Tag.render(_t8 || (_t8 = _$7`
			<a class="bizproc__ws_single-start__link" href="#" onclick="top.BX.Helper.show('redirect=detail&code=18783714')">
				${0}
			</a>
		`), main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_AVERAGE_DURATION_HINT')));
	}

	let _$8 = t => t,
	  _t$8;
	const CLOSE_SLIDER_AFTER_SECONDS = 1;
	class SuccessStartStep extends Step {
	  renderHead() {
	    return null;
	  }
	  renderBody() {
	    return main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div>
				<div class="bizproc-workflow-start__slider">
					<div class="bizproc-workflow-start__slider-logo">
						<div class="bizproc-workflow-start__slider-logo-animated"></div>
					</div>
					<div class="bizproc-workflow-start__slider-content">
						<div class="bizproc-workflow-start__slider-text">
							${0}
						</div>
					</div>
				</div>
			</div>
		`), main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_FINAL_TEXT_STARTED'));
	  }
	  onAfterRender() {
	    setTimeout(() => {
	      if (BX.SidePanel.Instance.getSliderByWindow(window)) {
	        BX.SidePanel.Instance.getSliderByWindow(window).close();
	      }
	    }, CLOSE_SLIDER_AFTER_SECONDS * 1000);
	  }
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$6;
	const HTML_ELEMENT_ID = 'bizproc-workflow-start-single-start';
	var _header = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("header");
	var _breadcrumbs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("breadcrumbs");
	var _errorNotifier = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorNotifier");
	var _steps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("steps");
	var _buttons$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttons");
	var _sequenceSteps$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sequenceSteps");
	var _currentStepId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentStepId");
	var _content = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("content");
	var _canExit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canExit");
	var _isExitInProcess = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExitInProcess");
	var _templateId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templateId");
	var _signedDocumentType$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentType");
	var _signedDocumentId$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentId");
	var _startTime$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startTime");
	var _resolveButtonsEnableState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveButtonsEnableState");
	var _renderContent$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderContent");
	var _updateContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateContent");
	var _next = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("next");
	var _back = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("back");
	var _fastStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fastStart");
	var _markButtonsOnBeforeNextStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markButtonsOnBeforeNextStep");
	var _cleanErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cleanErrors");
	var _isNextStepEnable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNextStepEnable");
	var _isPreviousStepEnable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPreviousStepEnable");
	var _exit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exit");
	var _composeData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("composeData");
	var _getRecommendationData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRecommendationData");
	var _getConstantsData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getConstantsData");
	var _getParametersData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getParametersData");
	var _getStartData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStartData");
	var _subscribeOnSliderClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnSliderClose");
	class SingleStart {
	  constructor(_config) {
	    Object.defineProperty(this, _subscribeOnSliderClose, {
	      value: _subscribeOnSliderClose2
	    });
	    Object.defineProperty(this, _getStartData, {
	      value: _getStartData2
	    });
	    Object.defineProperty(this, _getParametersData, {
	      value: _getParametersData2
	    });
	    Object.defineProperty(this, _getConstantsData, {
	      value: _getConstantsData2
	    });
	    Object.defineProperty(this, _getRecommendationData, {
	      value: _getRecommendationData2
	    });
	    Object.defineProperty(this, _composeData, {
	      value: _composeData2
	    });
	    Object.defineProperty(this, _exit, {
	      value: _exit2
	    });
	    Object.defineProperty(this, _isPreviousStepEnable, {
	      value: _isPreviousStepEnable2
	    });
	    Object.defineProperty(this, _isNextStepEnable, {
	      value: _isNextStepEnable2
	    });
	    Object.defineProperty(this, _cleanErrors, {
	      value: _cleanErrors2
	    });
	    Object.defineProperty(this, _markButtonsOnBeforeNextStep, {
	      value: _markButtonsOnBeforeNextStep2
	    });
	    Object.defineProperty(this, _fastStart, {
	      value: _fastStart2
	    });
	    Object.defineProperty(this, _back, {
	      value: _back2
	    });
	    Object.defineProperty(this, _next, {
	      value: _next2
	    });
	    Object.defineProperty(this, _updateContent, {
	      value: _updateContent2
	    });
	    Object.defineProperty(this, _renderContent$1, {
	      value: _renderContent2$1
	    });
	    Object.defineProperty(this, _resolveButtonsEnableState, {
	      value: _resolveButtonsEnableState2
	    });
	    Object.defineProperty(this, _header, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _breadcrumbs, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errorNotifier, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _steps, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _buttons$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sequenceSteps$2, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _currentStepId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _content, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canExit, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isExitInProcess, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _templateId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentType$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentId$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _startTime$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _startTime$1)[_startTime$1] = Math.round(Date.now() / 1000);
	    const composedData = babelHelpers.classPrivateFieldLooseBase(this, _composeData)[_composeData](_config);
	    babelHelpers.classPrivateFieldLooseBase(this, _header)[_header] = new Header({
	      title: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_TITLE'),
	      description: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_DESCRIPTION')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs)[_breadcrumbs] = new Breadcrumbs({
	      items: Object.values(composedData).map(data => data.breadcrumbs)
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier] = new ErrorNotifier({});
	    Object.entries(composedData).forEach(([key, data]) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].set(key, data.step);
	      data.step.subscribe('onChangeStepAvailability', babelHelpers.classPrivateFieldLooseBase(this, _resolveButtonsEnableState)[_resolveButtonsEnableState].bind(this));
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2] = Object.keys(composedData);
	    babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].at(0);
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1] = new Buttons({
	      buttons: Object.fromEntries(Object.entries(composedData).map(([key, data]) => [key, data.buttons])),
	      wrapper: document.getElementById(`${HTML_ELEMENT_ID}-buttons`).querySelector('.ui-button-panel')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$2)[_signedDocumentType$2] = _config.signedDocumentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$2)[_signedDocumentId$2] = _config.signedDocumentId;
	    babelHelpers.classPrivateFieldLooseBase(this, _templateId$2)[_templateId$2] = main_core.Text.toInteger(_config.id);
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnSliderClose)[_subscribeOnSliderClose]();
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = babelHelpers.classPrivateFieldLooseBase(this, _renderContent$1)[_renderContent$1]();
	    return main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div class="bizproc__ws_start">
				${0}
				<div class="bizproc__ws_start__body">
					${0}
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _header)[_header].render(), babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs)[_breadcrumbs].render(), babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]);
	  }
	  onAfterRender() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].has('recommendation')) {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get('recommendation').onAfterRender();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].show();
	  }
	}
	function _resolveButtonsEnableState2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].resolveEnableState({
	    next: babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).isNextEnabled(),
	    back: babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).isBackEnabled(),
	    start: babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).isNextEnabled()
	  });
	}
	function _renderContent2$1() {
	  return main_core.Tag.render(_t2$6 || (_t2$6 = _$9`
			<div class="bizproc__ws_start__container">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier].render(), babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].has(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]) ? babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).render() : null);
	}
	function _updateContent2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _content)[_content]) {
	    const content = babelHelpers.classPrivateFieldLooseBase(this, _renderContent$1)[_renderContent$1]();
	    main_core.Dom.replace(babelHelpers.classPrivateFieldLooseBase(this, _content)[_content], content);
	    babelHelpers.classPrivateFieldLooseBase(this, _content)[_content] = content;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].has(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2])) {
	      babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).onAfterRender();
	    }
	  }
	}
	function _next2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors]();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isNextStepEnable)[_isNextStepEnable]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _markButtonsOnBeforeNextStep)[_markButtonsOnBeforeNextStep]();
	    babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).onBeforeNextStep().then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs)[_breadcrumbs].next();
	      babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].at(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]) + 1);
	      babelHelpers.classPrivateFieldLooseBase(this, _updateContent)[_updateContent]();
	      babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].next();
	      babelHelpers.classPrivateFieldLooseBase(this, _resolveButtonsEnableState)[_resolveButtonsEnableState]();
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _resolveButtonsEnableState)[_resolveButtonsEnableState]();
	      if (error) {
	        console.error(error);
	      }
	    });
	  }
	}
	function _back2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors]();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isPreviousStepEnable)[_isPreviousStepEnable]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs)[_breadcrumbs].back();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2] = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].at(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]) - 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _updateContent)[_updateContent]();
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].back();
	    babelHelpers.classPrivateFieldLooseBase(this, _resolveButtonsEnableState)[_resolveButtonsEnableState]();
	  }
	}
	function _fastStart2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors]();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isNextStepEnable)[_isNextStepEnable]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _markButtonsOnBeforeNextStep)[_markButtonsOnBeforeNextStep]();
	    const data = {
	      templateId: babelHelpers.classPrivateFieldLooseBase(this, _templateId$2)[_templateId$2],
	      signedDocumentType: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$2)[_signedDocumentType$2],
	      signedDocumentId: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$2)[_signedDocumentId$2],
	      startDuration: Math.round(Date.now() / 1000) - babelHelpers.classPrivateFieldLooseBase(this, _startTime$1)[_startTime$1]
	    };
	    startWorkflowAction(data).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _canExit)[_canExit] = true;
	      babelHelpers.classPrivateFieldLooseBase(this, _next)[_next]();
	    }).catch(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier].errors = response.errors;
	      babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier].show();
	      babelHelpers.classPrivateFieldLooseBase(this, _resolveButtonsEnableState)[_resolveButtonsEnableState]();
	    });
	  }
	}
	function _markButtonsOnBeforeNextStep2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].resolveWaitingState({
	    start: true,
	    next: true
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _buttons$1)[_buttons$1].resolveEnableState({
	    back: false
	  });
	}
	function _cleanErrors2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier].errors = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier)[_errorNotifier].clean();
	}
	function _isNextStepEnable2() {
	  const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]);
	  return index !== -1 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].at(index + 1)) && babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).isNextEnabled();
	}
	function _isPreviousStepEnable2() {
	  const index = babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].indexOf(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]);
	  return index !== -1 && index - 1 >= 0 && main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _sequenceSteps$2)[_sequenceSteps$2].at(index - 1)) && babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].get(babelHelpers.classPrivateFieldLooseBase(this, _currentStepId$2)[_currentStepId$2]).isBackEnabled();
	}
	function _exit2() {
	  if (BX.SidePanel.Instance.getSliderByWindow(window)) {
	    BX.SidePanel.Instance.getSliderByWindow(window).close();
	  }
	}
	function _composeData2(config) {
	  const data = {
	    recommendation: babelHelpers.classPrivateFieldLooseBase(this, _getRecommendationData)[_getRecommendationData](config)
	  };
	  if (!config.isConstantsTuned) {
	    data.constants = babelHelpers.classPrivateFieldLooseBase(this, _getConstantsData)[_getConstantsData](config);
	  }
	  if (config.hasParameters) {
	    data.parameters = babelHelpers.classPrivateFieldLooseBase(this, _getParametersData)[_getParametersData](config);
	  }
	  data.start = babelHelpers.classPrivateFieldLooseBase(this, _getStartData)[_getStartData](config);
	  return data;
	}
	function _getRecommendationData2(config) {
	  const isFastStart = config.isConstantsTuned && !config.hasParameters;
	  return {
	    breadcrumbs: {
	      id: 'recommendation',
	      text: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_RECOMMENDATION'),
	      active: true
	    },
	    step: new RecommendationStep({
	      name: config.name,
	      recommendation: config.description,
	      duration: config.duration
	    }),
	    buttons: [Buttons.createBackButton(babelHelpers.classPrivateFieldLooseBase(this, _exit)[_exit].bind(this)), isFastStart ? Buttons.createStartButton(babelHelpers.classPrivateFieldLooseBase(this, _fastStart)[_fastStart].bind(this)) : Buttons.createNextButton(babelHelpers.classPrivateFieldLooseBase(this, _next)[_next].bind(this))]
	  };
	}
	function _getConstantsData2(config) {
	  return {
	    breadcrumbs: {
	      id: 'constants',
	      text: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_CONSTANTS'),
	      active: false
	    },
	    step: new ConstantsStep({
	      name: config.name,
	      templateId: config.id,
	      constants: config.constants,
	      documentType: config.documentType,
	      signedDocumentType: config.signedDocumentType,
	      signedDocumentId: config.signedDocumentId
	    }),
	    buttons: [Buttons.createBackButton(babelHelpers.classPrivateFieldLooseBase(this, _back)[_back].bind(this)), config.hasParameters ? Buttons.createNextButton(babelHelpers.classPrivateFieldLooseBase(this, _next)[_next].bind(this)) : Buttons.createStartButton(babelHelpers.classPrivateFieldLooseBase(this, _fastStart)[_fastStart].bind(this))]
	  };
	}
	function _getParametersData2(config) {
	  return {
	    breadcrumbs: {
	      id: 'parameters',
	      text: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_PARAMETERS'),
	      active: false
	    },
	    step: new ParametersStep({
	      name: config.name,
	      templateId: config.id,
	      parameters: config.parameters,
	      documentType: config.documentType,
	      signedDocumentId: config.signedDocumentId,
	      signedDocumentType: config.signedDocumentType
	    }),
	    buttons: [Buttons.createBackButton(babelHelpers.classPrivateFieldLooseBase(this, _back)[_back].bind(this)), Buttons.createStartButton(babelHelpers.classPrivateFieldLooseBase(this, _next)[_next].bind(this)) // slow start
	    ]
	  };
	}
	function _getStartData2(config) {
	  return {
	    breadcrumbs: {
	      id: 'start',
	      text: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_STEP_START'),
	      active: false
	    },
	    step: new SuccessStartStep({
	      name: config.name
	    }),
	    buttons: []
	  };
	}
	function _subscribeOnSliderClose2() {
	  const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	  if (slider) {
	    main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onClose', event => {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _canExit)[_canExit]) {
	        const canExit = [...babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].values()].every(step => step ? step.canExit() : true);
	        if (!canExit) {
	          event.getCompatData()[0].denyAction();
	          if (!babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess)[_isExitInProcess]) {
	            babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess)[_isExitInProcess] = true;
	            showExitDialog(() => {
	              babelHelpers.classPrivateFieldLooseBase(this, _canExit)[_canExit] = true;
	              slider.close();
	              return true;
	            }, () => {
	              babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess)[_isExitInProcess] = false;
	              return true;
	            });
	          }
	        }
	      }
	    });
	  }
	}

	function showCancelDialog(onConfirm, onCancel) {
	  const messageBox = ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_DESCRIPTION'), main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_TITLE'), onConfirm, main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_CONFIRM'), main_core.Type.isFunction(onCancel) ? onCancel : () => true, main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_EXIT_DIALOG_CANCEL'));
	  if (main_core.Type.isFunction(onCancel)) {
	    const popup = messageBox.getPopupWindow();
	    popup.subscribe('onClose', onCancel);
	  }
	}

	let _$a = t => t,
	  _t$a,
	  _t2$7;
	const FORM_NAME$2 = 'bizproc-ws-autostart';
	const HTML_ELEMENT_ID$1 = 'bizproc-workflow-start-autostart';
	var _header$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("header");
	var _breadcrumbs$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("breadcrumbs");
	var _buttons$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("buttons");
	var _errorNotifier$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorNotifier");
	var _templates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templates");
	var _documentType$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentType");
	var _signedDocumentType$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentType");
	var _signedDocumentId$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentId");
	var _autoExecute = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoExecute");
	var _forms = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("forms");
	var _canExit$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canExit");
	var _isExitInProcess$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExitInProcess");
	var _renderForm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderForm");
	var _exit$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("exit");
	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");
	var _subscribeOnSliderClose$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnSliderClose");
	class Autostart {
	  constructor(config) {
	    Object.defineProperty(this, _subscribeOnSliderClose$1, {
	      value: _subscribeOnSliderClose2$1
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _exit$1, {
	      value: _exit2$1
	    });
	    Object.defineProperty(this, _renderForm, {
	      value: _renderForm2
	    });
	    Object.defineProperty(this, _header$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _breadcrumbs$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _buttons$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _errorNotifier$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _templates, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentType$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentType$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _signedDocumentId$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _autoExecute, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _forms, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _canExit$1, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isExitInProcess$1, {
	      writable: true,
	      value: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _header$1)[_header$1] = new Header({
	      title: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_TITLE'),
	      description: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_DESCRIPTION')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs$1)[_breadcrumbs$1] = new Breadcrumbs({
	      items: [{
	        id: 'autostart',
	        text: main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_AUTOSTART_STEP_AUTOSTART_TITLE'),
	        active: true
	      }]
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$2)[_buttons$2] = new Buttons({
	      buttons: {
	        autostart: [Buttons.createBackButton(babelHelpers.classPrivateFieldLooseBase(this, _exit$1)[_exit$1].bind(this)), new ui_buttons.Button({
	          id: 'save',
	          text: main_core.Text.encode(main_core.Loc.getMessage('BIZPROC_CMP_WORKFLOW_START_TMP_SINGLE_START_BUTTON_SAVE')),
	          onclick: babelHelpers.classPrivateFieldLooseBase(this, _save)[_save].bind(this),
	          color: ui_buttons.ButtonColor.PRIMARY
	        })]
	      },
	      wrapper: document.getElementById(`${HTML_ELEMENT_ID$1}-buttons`).querySelector('.ui-button-panel')
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier$1)[_errorNotifier$1] = new ErrorNotifier({});
	    if (main_core.Type.isArrayFilled(config.templates)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates] = config.templates;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType$2)[_documentType$2] = config.documentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$3)[_signedDocumentType$3] = config.signedDocumentType;
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$3)[_signedDocumentId$3] = config.signedDocumentId || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _autoExecute)[_autoExecute] = main_core.Text.toInteger(config.autoExecuteType);
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnSliderClose$1)[_subscribeOnSliderClose$1]();
	  }
	  render() {
	    return main_core.Tag.render(_t$a || (_t$a = _$a`
			<div class="bizproc__ws_start">
				${0}
				<div class="bizproc__ws_start__body">
					${0}
					<div class="bizproc__ws_start__container">
						${0}
						<div class="bizproc__ws_start__content">
							<div class="bizproc__ws_start__content-body">
								${0}
							</div>
						</div>
					</div>
				<div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _header$1)[_header$1].render(), babelHelpers.classPrivateFieldLooseBase(this, _breadcrumbs$1)[_breadcrumbs$1].render(), babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier$1)[_errorNotifier$1].render(), babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates].map(template => babelHelpers.classPrivateFieldLooseBase(this, _renderForm)[_renderForm](template)));
	  }
	  onAfterRender() {
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$2)[_buttons$2].show();
	  }
	}
	function _renderForm2(template) {
	  const form = renderBpForm(`${FORM_NAME$2}_${template.id}`, template.name, template.parameters, babelHelpers.classPrivateFieldLooseBase(this, _documentType$2)[_documentType$2], template.description);
	  babelHelpers.classPrivateFieldLooseBase(this, _forms)[_forms].push(form);
	  return main_core.Tag.render(_t2$7 || (_t2$7 = _$a`<div class="bizproc__ws_start__content-form">${0}</div>`), form);
	}
	function _exit2$1() {
	  if (BX.SidePanel.Instance.getSliderByWindow(window)) {
	    BX.SidePanel.Instance.getSliderByWindow(window).close();
	  }
	}
	function _save2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _buttons$2)[_buttons$2].resolveWaitingState({
	    save: true
	  });
	  const data = new FormData();
	  babelHelpers.classPrivateFieldLooseBase(this, _forms)[_forms].forEach(form => {
	    addMissingFormDataValues(data, new FormData(form));
	  });
	  data.set('signedDocumentType', babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType$3)[_signedDocumentType$3]);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$3)[_signedDocumentId$3]) {
	    data.set('signedDocumentId', babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId$3)[_signedDocumentId$3]);
	  }
	  data.set('autoExecuteType', babelHelpers.classPrivateFieldLooseBase(this, _autoExecute)[_autoExecute]);
	  main_core.ajax.runAction('bizproc.workflow.starter.checkParameters', {
	    data
	  }).then(response => {
	    const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	    if (slider) {
	      const dictionary = slider.getData();
	      dictionary.set('data', {
	        signedParameters: response.data.parameters
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _canExit$1)[_canExit$1] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _exit$1)[_exit$1]();
	  }).catch(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier$1)[_errorNotifier$1].errors = response.errors;
	    babelHelpers.classPrivateFieldLooseBase(this, _errorNotifier$1)[_errorNotifier$1].show();
	    babelHelpers.classPrivateFieldLooseBase(this, _buttons$2)[_buttons$2].resolveWaitingState({
	      save: false
	    });
	  });
	}
	function _subscribeOnSliderClose2$1() {
	  const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	  if (slider) {
	    main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onClose', event => {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _canExit$1)[_canExit$1]) {
	        event.getCompatData()[0].denyAction();
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess$1)[_isExitInProcess$1]) {
	          babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess$1)[_isExitInProcess$1] = true;
	          showCancelDialog(() => {
	            babelHelpers.classPrivateFieldLooseBase(this, _canExit$1)[_canExit$1] = true;
	            slider.close();
	            return true;
	          }, () => {
	            babelHelpers.classPrivateFieldLooseBase(this, _isExitInProcess$1)[_isExitInProcess$1] = false;
	            return true;
	          });
	        }
	      }
	    });
	  }
	}

	exports.WorkflowSingleStart = SingleStart;
	exports.WorkflowAutoStart = Autostart;

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX.UI.IconSet,BX.UI,BX,BX,BX.Main,BX,BX.Event,BX.UI,BX,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
