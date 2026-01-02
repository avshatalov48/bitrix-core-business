/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_stepbystep,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _errorBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("errorBlock");
	class BaseField extends main_core_events.EventEmitter {
	  constructor(options) {
	    var _this$options$value;
	    super();
	    Object.defineProperty(this, _errorBlock, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoice.Field');
	    this.options = options;
	    this.value = (_this$options$value = this.options.value) != null ? _this$options$value : null;
	    this.readySave = !(main_core.Type.isNil(this.value) || this.value === '');
	    this.options.id = main_core.Type.isStringFilled(this.options.id) ? this.options.id : main_core.Text.getRandom(8);
	  }
	  getId() {
	    return this.options.id;
	  }
	  getName() {
	    return this.options.name;
	  }
	  getContent() {
	    const wrapper = main_core.Tag.render(_t || (_t = _`
			<div class="container"></div>
		`));
	    main_core.Dom.append(this.renderFieldContainer(), wrapper);
	    main_core.Dom.append(this.renderErrorsContainer(), wrapper);
	    main_core.Dom.hide(this.renderErrorsContainer());
	    this.subscribe('error', event => {
	      const messages = event.data.messages;
	      this.renderErrorsContainer().innerHTML = main_core.Type.isArray(messages) ? messages.join('<br>') : messages;
	      main_core.Dom.show(this.renderErrorsContainer());
	      if (!main_core.Dom.hasClass(wrapper, 'ui-ctl-warning')) {
	        main_core.Dom.addClass(wrapper, 'ui-ctl-warning');
	      }
	    });
	    return wrapper;
	  }
	  renderFieldContainer() {
	    throw new Error('Must be implemented in a child class');
	  }
	  isReadySave() {
	    return this.readySave;
	  }
	  renderErrorsContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _errorBlock)[_errorBlock]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _errorBlock)[_errorBlock] = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-ctl-bottom bitrix-einvoice-error-block"></div>
			`));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _errorBlock)[_errorBlock];
	  }
	  getValue() {
	    return this.value;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3;
	class DropdownList extends BaseField {
	  constructor(options) {
	    super(options);
	    this.readySave = true;
	  }
	  renderFieldContainer() {
	    const wrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
			</div>
		`));
	    if (main_core.Type.isArray(this.options.items)) {
	      const itemsWrapper = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<select class="ui-ctl-element" id="${0}" />
			`), this.getId());
	      this.options.items.forEach(item => {
	        const itemElement = main_core.Tag.render(_t3 || (_t3 = _$1`
					<option value="${0}">${0}</option>
				`), item.value, item.name);
	        if (this.options.value === item.value) {
	          main_core.Dom.attr(itemElement, {
	            selected: true
	          });
	        }
	        main_core.Dom.append(itemElement, itemsWrapper);
	      });
	      main_core.Dom.append(itemsWrapper, wrapper);
	      main_core.Event.bind(itemsWrapper, 'change', event => {
	        this.value = event.target.value;
	        if (Object.prototype.hasOwnProperty.call(this.options, 'updateForm') && this.options.updateForm) {
	          this.emit('onFieldChange', {
	            target: event.target,
	            field: this
	          });
	        }
	      });
	    }
	    return wrapper;
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1;
	var _onInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onInput");
	class Input extends BaseField {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _onInput, {
	      value: _onInput2
	    });
	  }
	  renderFieldContainer() {
	    const wrapper = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="ui-ctl-container"/>
		`));
	    if (this.options.label) {
	      const inputTitle = main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
				<div class="ui-ctl-top">
					<div class="ui-ctl-title">${0}</div>
				</div>
			`), this.options.label);
	      main_core.Dom.append(inputTitle, wrapper);
	    }
	    const input = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="ui-ctl ui-ctl-textbox">
				<input type="text" id="${0}" class="ui-ctl-element">
			</div>
		`), this.getId());
	    const inputElement = input.querySelector('input');
	    if (this.options.placeholder) {
	      main_core.Dom.attr(inputElement, {
	        placeholder: this.options.placeholder
	      });
	    }
	    if (this.options.value) {
	      main_core.Dom.attr(inputElement, {
	        value: this.options.value
	      });
	    }
	    main_core.Event.bind(inputElement, 'paste', event => {
	      setTimeout(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _onInput)[_onInput](wrapper, event);
	      }, 0);
	    });
	    main_core.Event.bind(inputElement, 'input', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _onInput)[_onInput](wrapper, event);
	    });
	    main_core.Dom.append(input, wrapper);
	    return wrapper;
	  }
	}
	function _onInput2(wrapper, event) {
	  main_core.Dom.hide(this.renderErrorsContainer());
	  if (main_core.Dom.hasClass(wrapper, 'ui-ctl-warning')) {
	    main_core.Dom.removeClass(wrapper, 'ui-ctl-warning');
	  }
	  if (main_core.Type.isNil(event.target.value) || event.target.value === '') {
	    this.emit('onUnreadySave');
	    this.readySave = false;
	  } else {
	    this.emit('onReadySave');
	    this.readySave = true;
	  }
	  this.value = event.target.value;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$2,
	  _t4;
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _fields = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fields");
	var _stepByStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepByStep");
	var _getContentConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContentConfig");
	var _getStepContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStepContent");
	class FormConstructor extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    Object.defineProperty(this, _getStepContent, {
	      value: _getStepContent2
	    });
	    Object.defineProperty(this, _getContentConfig, {
	      value: _getContentConfig2
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _fields, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepByStep, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('BX.Rest.EInvoice');
	    if (!main_core.Type.isArray(options.steps)) {
	      throw new Error('Unexpected property type  "steps", expected type array');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _stepByStep)[_stepByStep] = new ui_stepbystep.StepByStep({
	      content: babelHelpers.classPrivateFieldLooseBase(this, _getContentConfig)[_getContentConfig]()
	    });
	  }
	  getFields() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields];
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _stepByStep)[_stepByStep].getContentWrapper();
	  }
	  renderTo(target) {
	    babelHelpers.classPrivateFieldLooseBase(this, _stepByStep)[_stepByStep].target = target;
	    babelHelpers.classPrivateFieldLooseBase(this, _stepByStep)[_stepByStep].init();
	  }
	  getFormData() {
	    let result = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields].forEach(field => {
	      if (field.isReadySave()) {
	        result[field.getName()] = field.getValue();
	      }
	    });
	    return result;
	  }

	  /*
	  errors = {
	  	fieldName: ['error message']
	  }
	   */
	  showFieldErrors(errors) {
	    for (const [fieldName, messages] of Object.entries(errors)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields].forEach(field => {
	        if (field.getName() === fieldName) {
	          field.emit('error', new main_core_events.BaseEvent({
	            data: {
	              messages: messages
	            }
	          }));
	        }
	      });
	    }
	  }
	  showTextInBalloon(text) {
	    BX.UI.Notification.Center.notify({
	      id: 'einvoice-error-save-settings',
	      content: main_core.Tag.render(_t$3 || (_t$3 = _$3`
						<div class="bitrix-einvoice-settings-notification-wrapper">
							<span class="ui-icon-set --warning"></span>
							${0}
						</div>
					`), text),
	      autoHideDelay: 5000
	    });
	  }
	}
	function _getContentConfig2() {
	  const contentConfig = [];
	  babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].steps.forEach(item => {
	    const stepConfig = {
	      html: [{
	        backgroundColor: '#ffffff'
	      }]
	    };
	    if (item.title) {
	      stepConfig.html[0].header = {
	        title: item.title
	      };
	    }
	    stepConfig.html[0].node = babelHelpers.classPrivateFieldLooseBase(this, _getStepContent)[_getStepContent](item);
	    contentConfig.push(stepConfig);
	  });
	  return contentConfig;
	}
	function _getStepContent2(stepConfig) {
	  const wrapper = main_core.Tag.render(_t2$3 || (_t2$3 = _$3`
			<div class="bitrix-einvoice-settings-step__wrapper"></div>
		`));
	  if (stepConfig.description) {
	    const description = main_core.Tag.render(_t3$2 || (_t3$2 = _$3`
				<div class="bitrix-einvoice-settings-step__description">${0}</div>
			`), stepConfig.description);
	    main_core.Dom.append(description, wrapper);
	  }
	  if (stepConfig.fields) {
	    stepConfig.fields.forEach((fieldConfig, index) => {
	      let field;
	      switch (fieldConfig.type) {
	        case 'input':
	          field = new Input(fieldConfig);
	          break;
	        case 'dropdown-list':
	          field = new DropdownList(fieldConfig);
	          break;
	        default:
	          throw new Error('Incorrect field type');
	      }
	      if (field instanceof BaseField) {
	        field.subscribe('onReadySave', () => {
	          this.emit('onReadySave');
	        });
	        field.subscribe('onUnreadySave', () => {
	          this.emit('onUnreadySave');
	        });
	        field.subscribe('onFieldChange', event => {
	          this.emit('onFieldChange', event);
	        });
	        babelHelpers.classPrivateFieldLooseBase(this, _fields)[_fields].push(field);
	        const fieldContent = field.getContent();
	        main_core.Dom.append(fieldContent, wrapper);
	        if (index > 0) {
	          main_core.Dom.style(fieldContent, 'margin-top', '12px');
	        }
	      }
	    });
	  }
	  if (stepConfig.link && stepConfig.link.url.startsWith('https://')) {
	    const linkArticle = main_core.Tag.render(_t4 || (_t4 = _$3`
				<div class="bitrix-einvoice-settings-step-wrapper-link">
					<a href="${0}" class="bitrix-einvoice-settings-step__link">${0}</a>
				</div>
			`), stepConfig.link.url, main_core.Text.encode(stepConfig.link.name));
	    main_core.Dom.append(linkArticle, wrapper);
	  }
	  return wrapper;
	}

	exports.FormConstructor = FormConstructor;

}((this.BX.Rest = this.BX.Rest || {}),BX.UI,BX,BX.Event));
//# sourceMappingURL=form-constructor.bundle.js.map
