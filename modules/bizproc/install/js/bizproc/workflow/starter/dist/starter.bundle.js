/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,ui_entitySelector,ui_notification,sidepanel,main_core,ui_dialogs_messagebox,main_core_events) {
	'use strict';

	var _moduleId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moduleId");
	var _entity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("entity");
	var _documentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentId");
	class ComplexDocumentId {
	  constructor(moduleId, entity, documentId) {
	    Object.defineProperty(this, _moduleId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _entity, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentId, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isStringFilled(moduleId) || !main_core.Type.isStringFilled(entity) || !(main_core.Type.isStringFilled(documentId) || main_core.Type.isNumber(documentId))) {
	      throw new TypeError('incorrect complex document id');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _moduleId)[_moduleId] = moduleId;
	    babelHelpers.classPrivateFieldLooseBase(this, _entity)[_entity] = entity;
	    babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId] = documentId;
	  }
	  get moduleId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _moduleId)[_moduleId];
	  }
	  get entity() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _entity)[_entity];
	  }
	  get documentId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentId)[_documentId];
	  }
	}

	var _moduleId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moduleId");
	var _entity$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("entity");
	var _documentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentType");
	class ComplexDocumentType {
	  constructor(moduleId, entity, documentType) {
	    Object.defineProperty(this, _moduleId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _entity$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _documentType, {
	      writable: true,
	      value: void 0
	    });
	    if (!main_core.Type.isStringFilled(moduleId) || !main_core.Type.isStringFilled(entity) || !main_core.Type.isStringFilled(documentType)) {
	      throw new TypeError('incorrect complex document type');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _moduleId$1)[_moduleId$1] = moduleId;
	    babelHelpers.classPrivateFieldLooseBase(this, _entity$1)[_entity$1] = entity;
	    babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType] = documentType;
	  }
	  get moduleId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _moduleId$1)[_moduleId$1];
	  }
	  get entity() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _entity$1)[_entity$1];
	  }
	  get documentType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _documentType)[_documentType];
	  }
	  isEqual(targetDocumentType) {
	    if (main_core.Type.isString(targetDocumentType)) {
	      return targetDocumentType.includes(this.moduleId) && targetDocumentType.includes(this.entity) && targetDocumentType.includes(this.documentType);
	    }
	    if (main_core.Type.isObjectLike(targetDocumentType)) {
	      return this.moduleId === targetDocumentType.moduleId && this.entity === targetDocumentType.entity && this.documentType === targetDocumentType.documentType;
	    }
	    return false;
	  }
	}

	const ACTION_AJAX_MAP = Object.freeze({
	  load: 'get_templates',
	  start: 'start_workflow',
	  check_parameters: 'check_parameters'
	});
	const ACTION_CONTROLLER_MAP = Object.freeze({
	  load: 'getTemplates',
	  start: 'startWorkflow',
	  check_parameters: 'checkParameters'
	});
	var _defaultData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultData");
	var _ajaxUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ajaxUrl");
	var _controller = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("controller");
	var _fillDefaultData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fillDefaultData");
	var _hasAjaxUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasAjaxUrl");
	var _callAjax = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callAjax");
	var _callController = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callController");
	class CallActionHelper {
	  constructor(_data) {
	    Object.defineProperty(this, _callController, {
	      value: _callController2
	    });
	    Object.defineProperty(this, _callAjax, {
	      value: _callAjax2
	    });
	    Object.defineProperty(this, _hasAjaxUrl, {
	      get: _get_hasAjaxUrl,
	      set: void 0
	    });
	    Object.defineProperty(this, _fillDefaultData, {
	      value: _fillDefaultData2
	    });
	    Object.defineProperty(this, _defaultData, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _ajaxUrl, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _controller, {
	      writable: true,
	      value: 'bizproc.workflow.starter'
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _fillDefaultData)[_fillDefaultData](_data);
	    if (main_core.Type.isStringFilled(_data.customAjaxUrl)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _ajaxUrl)[_ajaxUrl] = _data.customAjaxUrl;
	    } else if (!main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].signed_document_type)) {
	      console.warn(`
				Bizproc.Workflow.Starter: 
				Using the document type in parts has been deprecated and will soon cease to be supported. 
				Please use a signed document type
			`);
	      babelHelpers.classPrivateFieldLooseBase(this, _ajaxUrl)[_ajaxUrl] = '/bitrix/components/bitrix/bizproc.workflow.start/ajax.php';
	    }
	  }
	  callAction(action, actionData = {}) {
	    const actionName = babelHelpers.classPrivateFieldLooseBase(this, _hasAjaxUrl)[_hasAjaxUrl] ? ACTION_AJAX_MAP[action] : ACTION_CONTROLLER_MAP[action];
	    if (!main_core.Type.isStringFilled(actionName)) {
	      return Promise.reject(new Error('incorrect action')); // todo: Loc
	    }

	    const data = this.addData(babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData], actionData);
	    return babelHelpers.classPrivateFieldLooseBase(this, _hasAjaxUrl)[_hasAjaxUrl] ? babelHelpers.classPrivateFieldLooseBase(this, _callAjax)[_callAjax](actionName, data) : babelHelpers.classPrivateFieldLooseBase(this, _callController)[_callController](actionName, data);
	  }
	  addData(targetData, actionData = {}) {
	    const data = actionData;
	    const isPlainObject = main_core.Type.isPlainObject(data);
	    Object.entries(targetData).forEach(([key, value]) => {
	      const modifiedKey = babelHelpers.classPrivateFieldLooseBase(this, _hasAjaxUrl)[_hasAjaxUrl] ? key : main_core.Text.toCamelCase(key);
	      if (isPlainObject) {
	        data[modifiedKey] = value;
	        return;
	      }
	      data.set(modifiedKey, value);
	    });
	    return data;
	  }
	}
	function _fillDefaultData2(data) {
	  var _data$complexDocument, _data$complexDocument2, _data$complexDocument3, _data$complexDocument4;
	  if (!main_core.Type.isNil(data.signedDocumentType)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].signed_document_type = data.signedDocumentType;
	  }
	  if (!main_core.Type.isNil(data.signedDocumentId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].signed_document_id = data.signedDocumentId;
	  }
	  if (!main_core.Type.isNil((_data$complexDocument = data.complexDocumentType) == null ? void 0 : _data$complexDocument.moduleId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].module_id = data.complexDocumentType.moduleId;
	  }
	  if (!main_core.Type.isNil((_data$complexDocument2 = data.complexDocumentType) == null ? void 0 : _data$complexDocument2.entity)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].entity = data.complexDocumentType.entity;
	  }
	  if (!main_core.Type.isNil((_data$complexDocument3 = data.complexDocumentType) == null ? void 0 : _data$complexDocument3.documentType)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].document_type = data.complexDocumentType.documentType;
	  }
	  if (!main_core.Type.isNil((_data$complexDocument4 = data.complexDocumentId) == null ? void 0 : _data$complexDocument4.documentId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultData)[_defaultData].document_id = data.complexDocumentId.documentId;
	  }
	}
	function _get_hasAjaxUrl() {
	  return main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _ajaxUrl)[_ajaxUrl]);
	}
	function _callAjax2(actionName, actionData = {}) {
	  const data = this.addData({
	    sessid: main_core.Loc.getMessage('bitrix_sessid'),
	    site: main_core.Loc.getMessage('SITE_ID'),
	    ajax_action: actionName
	  }, actionData);
	  return new Promise((resolve, reject) => {
	    const ajaxConfig = {
	      method: 'POST',
	      dataType: 'json',
	      url: babelHelpers.classPrivateFieldLooseBase(this, _ajaxUrl)[_ajaxUrl],
	      data,
	      onsuccess: response => {
	        if (response.success) {
	          resolve(response);
	        } else {
	          reject(response);
	        }
	      },
	      onfailure: () => {
	        reject();
	      }
	    };
	    if (!main_core.Type.isPlainObject(data)) {
	      ajaxConfig.preparePost = false;
	    }
	    main_core.ajax(ajaxConfig);
	  });
	}
	function _callController2(actionName, data = {}) {
	  return new Promise((resolve, reject) => {
	    main_core.ajax.runAction(`${babelHelpers.classPrivateFieldLooseBase(this, _controller)[_controller]}.${actionName}`, {
	      data
	    }).then(resolve).catch(reject);
	  });
	}

	var _messages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messages");
	var _setMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setMessages");
	var _showMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showMessages");
	var _defaultErrorMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultErrorMessage");
	class ErrorNotifier {
	  constructor(_errors) {
	    Object.defineProperty(this, _defaultErrorMessage, {
	      get: _get_defaultErrorMessage,
	      set: void 0
	    });
	    Object.defineProperty(this, _showMessages, {
	      value: _showMessages2
	    });
	    Object.defineProperty(this, _setMessages, {
	      value: _setMessages2
	    });
	    Object.defineProperty(this, _messages, {
	      writable: true,
	      value: []
	    });
	    if (main_core.Type.isArrayFilled(_errors)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setMessages)[_setMessages](_errors);
	    }
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _showMessages)[_showMessages](ui_dialogs_messagebox.MessageBox);
	  }
	  showToWindow(targetWindow) {
	    targetWindow.BX.Runtime.loadExtension('ui.dialogs.messagebox').then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _showMessages)[_showMessages](targetWindow.BX.UI.Dialogs.MessageBox);
	    }).catch(() => {});
	  }
	}
	function _setMessages2(errors) {
	  errors.forEach(error => {
	    if (main_core.Type.isStringFilled(error)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].push(main_core.Text.encode(error));
	    } else if (main_core.Type.isPlainObject(error) && main_core.Type.isStringFilled(error.message)) {
	      if (main_core.Type.isStringFilled(error.code) && error.code === 'NETWORK_ERROR') {
	        babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].push(main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _defaultErrorMessage)[_defaultErrorMessage]));
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].push(main_core.Text.encode(error.message));
	      }
	    }
	  });
	}
	function _showMessages2(messageBox) {
	  if (!messageBox) {
	    return;
	  }
	  if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages])) {
	    messageBox.alert(babelHelpers.classPrivateFieldLooseBase(this, _messages)[_messages].join('<br>'));
	    return;
	  }
	  messageBox.alert(main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _defaultErrorMessage)[_defaultErrorMessage]));
	}
	function _get_defaultErrorMessage() {
	  return main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_STARTER_REQUEST_FAILED');
	}

	var _templates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templates");
	var _signedDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentType");
	var _signedDocumentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedDocumentId");
	var _complexDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("complexDocumentType");
	var _complexDocumentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("complexDocumentId");
	var _templatesSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templatesSelector");
	var _callActionHelper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callActionHelper");
	var _hasCustomAjaxUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasCustomAjaxUrl");
	var _setDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDocumentType");
	var _setDocumentId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setDocumentId");
	var _showTemplatesSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showTemplatesSlider");
	var _loadTemplates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadTemplates");
	var _initTemplateSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initTemplateSelector");
	var _onTemplateSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTemplateSelect");
	var _showStepByStepSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showStepByStepSlider");
	var _createStepByStepSliderUrl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStepByStepSliderUrl");
	var _callAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callAction");
	var _showErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showErrors");
	class Starter$$1 extends main_core_events.EventEmitter {
	  constructor(_data) {
	    super();
	    Object.defineProperty(this, _showErrors, {
	      value: _showErrors2
	    });
	    Object.defineProperty(this, _callAction, {
	      value: _callAction2
	    });
	    Object.defineProperty(this, _createStepByStepSliderUrl, {
	      value: _createStepByStepSliderUrl2
	    });
	    Object.defineProperty(this, _showStepByStepSlider, {
	      value: _showStepByStepSlider2
	    });
	    Object.defineProperty(this, _onTemplateSelect, {
	      value: _onTemplateSelect2
	    });
	    Object.defineProperty(this, _initTemplateSelector, {
	      value: _initTemplateSelector2
	    });
	    Object.defineProperty(this, _loadTemplates, {
	      value: _loadTemplates2
	    });
	    Object.defineProperty(this, _showTemplatesSlider, {
	      value: _showTemplatesSlider2
	    });
	    Object.defineProperty(this, _setDocumentId, {
	      value: _setDocumentId2
	    });
	    Object.defineProperty(this, _setDocumentType, {
	      value: _setDocumentType2
	    });
	    Object.defineProperty(this, _templates, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _signedDocumentType, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _signedDocumentId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _complexDocumentType, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _complexDocumentId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _templatesSelector, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _callActionHelper, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasCustomAjaxUrl, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.Bizproc.Workflow.Starter');
	    babelHelpers.classPrivateFieldLooseBase(this, _setDocumentType)[_setDocumentType](_data);
	    if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType]) && main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType])) {
	      throw new TypeError('document type is empty');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setDocumentId)[_setDocumentId](_data);
	    if (main_core.Type.isArray(_data.templates)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates] = _data.templates;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _hasCustomAjaxUrl)[_hasCustomAjaxUrl] = main_core.Type.isStringFilled(_data.ajaxUrl);
	    babelHelpers.classPrivateFieldLooseBase(this, _callActionHelper)[_callActionHelper] = new CallActionHelper({
	      complexDocumentType: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType],
	      signedDocumentType: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType],
	      complexDocumentId: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentId)[_complexDocumentId],
	      signedDocumentId: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId],
	      customAjaxUrl: babelHelpers.classPrivateFieldLooseBase(this, _hasCustomAjaxUrl)[_hasCustomAjaxUrl] ? _data.ajaxUrl : null
	    });
	    managerInstance.put(this);
	  }
	  static singleStart(config, callback) {
	    const templateId = main_core.Text.toInteger(config == null ? void 0 : config.templateId);
	    if (templateId <= 0) {
	      return;
	    }
	    let starter = null;
	    try {
	      starter = new Starter$$1({
	        moduleId: config.moduleId,
	        entity: config.entity,
	        documentType: config.documentType,
	        documentId: config.documentId,
	        signedDocumentType: config.signedDocumentType,
	        signedDocumentId: config.signedDocumentId,
	        templates: config.templates || null,
	        ajaxUrl: config.ajaxUrl || ''
	      });
	    } catch (e) {
	      console.error(e);
	      return;
	    }
	    if (main_core.Type.isFunction(callback)) {
	      main_core_events.EventEmitter.subscribe(starter, 'onAfterStartWorkflow', callback);
	    }
	    starter.beginStartWorkflow(templateId).then(() => {
	      managerInstance.remove(starter);
	    }).catch(() => {});
	  }
	  static showTemplates(starterData, config) {
	    let starter = null;
	    try {
	      starter = new Starter$$1({
	        signedDocumentType: starterData.signedDocumentType,
	        signedDocumentId: starterData.signedDocumentId
	      });
	    } catch (e) {
	      console.error(e);
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(starter, _showTemplatesSlider)[_showTemplatesSlider](() => {
	      if (main_core.Type.isFunction(config.callback)) {
	        config.callback();
	      }
	      managerInstance.remove(starter);
	    });
	  }
	  get signedDocumentType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType];
	  }
	  get complexDocumentType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType];
	  }
	  // compatibility
	  showTemplatesMenu(targetNode) {
	    if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType]) && !babelHelpers.classPrivateFieldLooseBase(this, _hasCustomAjaxUrl)[_hasCustomAjaxUrl]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showTemplatesSlider)[_showTemplatesSlider]();
	      return;
	    }
	    if (!main_core.Type.isElementNode(targetNode) && !main_core.Type.isNull(targetNode)) {
	      return;
	    }
	    if (main_core.Type.isArray(babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates])) {
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _templatesSelector)[_templatesSelector]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _initTemplateSelector)[_initTemplateSelector](targetNode);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _templatesSelector)[_templatesSelector].show();
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _loadTemplates)[_loadTemplates]().then(() => {
	      this.showTemplatesMenu(targetNode);
	    }).catch(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](response == null ? void 0 : response.errors);
	    });
	  }
	  // compatibility
	  showParametersPopup(templateId) {
	    this.beginStartWorkflow(templateId).then(() => {}).catch(() => {});
	  }
	  beginStartWorkflow(templateId) {
	    if (main_core.Text.toInteger(templateId) <= 0) {
	      return Promise.resolve();
	    }
	    return new Promise((resolve, reject) => {
	      babelHelpers.classPrivateFieldLooseBase(this, _showStepByStepSlider)[_showStepByStepSlider]({
	        templateId,
	        autoExecuteType: null
	      }).then(data => {
	        if (main_core.Type.isStringFilled(data.workflowId)) {
	          managerInstance.fireEvent(this, 'onAfterStartWorkflow', {
	            workflowId: data.workflowId
	          });
	        }
	        resolve();
	      }).catch(reject);
	    });
	  }

	  // compatibility
	  showAutoStartParametersPopup(autoExecuteType, config = {}) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showStepByStepSlider)[_showStepByStepSlider]({
	      templateId: null,
	      autoExecuteType
	    }).then(data => {
	      if (main_core.Type.isFunction(config == null ? void 0 : config.callback)) {
	        if (main_core.Type.isString(data.signedParameters)) {
	          config.callback({
	            parameters: data.signedParameters
	          });
	          return;
	        }
	        config.callback({
	          parameters: null
	        });
	      }
	    }).catch(() => {});
	  }
	}
	function _setDocumentType2(data) {
	  if (main_core.Type.isStringFilled(data.moduleId) && main_core.Type.isStringFilled(data.entity) && main_core.Type.isStringFilled(data.documentType)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType] = new ComplexDocumentType(data.moduleId, data.entity, data.documentType);
	  }
	  if (main_core.Type.isStringFilled(data.signedDocumentType)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType] = data.signedDocumentType;
	  }
	}
	function _setDocumentId2(data) {
	  if (main_core.Type.isStringFilled(data.moduleId) && main_core.Type.isStringFilled(data.entity) && (main_core.Type.isStringFilled(data.documentId) || main_core.Type.isNumber(data.documentId))) {
	    babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentId)[_complexDocumentId] = new ComplexDocumentId(data.moduleId, data.entity, data.documentId);
	  }
	  if (main_core.Type.isStringFilled(data.signedDocumentId)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId] = data.signedDocumentId;
	  }
	}
	function _showTemplatesSlider2(callback = null) {
	  const sliderOptions = {
	    width: 970,
	    cacheable: false,
	    events: {
	      onCloseComplete: main_core.Type.isFunction(callback) ? callback : () => {}
	    }
	  };
	  const componentParams = {
	    signedDocumentType: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType],
	    signedDocumentId: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId]
	  };
	  const url = BX.Uri.addParam('/bitrix/components/bitrix/bizproc.workflow.start.list/', componentParams);
	  BX.SidePanel.Instance.open(url, sliderOptions);
	}
	function _loadTemplates2() {
	  return new Promise((resolve, reject) => {
	    babelHelpers.classPrivateFieldLooseBase(this, _callAction)[_callAction]('load').then(response => {
	      babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates] = main_core.Type.isArray(response.data.templates) ? response.data.templates : [];
	      resolve(response);
	    }).catch(reject);
	  });
	}
	function _initTemplateSelector2(targetNode) {
	  const items = [];
	  if (main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _templates)[_templates].forEach(template => {
	      if (main_core.Text.toInteger(template.id) > 0 && main_core.Type.isStringFilled(template.name)) {
	        items.push({
	          id: template.id,
	          title: template.name,
	          subtitle: template.description || '',
	          entityId: 'template',
	          tabs: 'recents',
	          customData: template
	        });
	      }
	    });
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _templatesSelector)[_templatesSelector] = new ui_entitySelector.Dialog({
	    targetNode,
	    context: 'bp_workflow_starter',
	    items,
	    multiple: false,
	    dropdownMode: true,
	    enableSearch: true,
	    hideOnSelect: true,
	    clearSearchOnSelect: true,
	    hideByEsc: true,
	    cacheable: true,
	    focusOnFirst: true,
	    showAvatars: false,
	    compactView: false,
	    events: {
	      'Item:onSelect': event => {
	        var _event$getData$item;
	        babelHelpers.classPrivateFieldLooseBase(this, _templatesSelector)[_templatesSelector].deselectAll();
	        const customData = (_event$getData$item = event.getData().item) == null ? void 0 : _event$getData$item.getCustomData();
	        if (customData) {
	          babelHelpers.classPrivateFieldLooseBase(this, _onTemplateSelect)[_onTemplateSelect](customData);
	        }
	      }
	    },
	    recentTabOptions: {
	      stub: true,
	      stubOptions: {
	        title: main_core.Loc.getMessage('BIZPROC_JS_WORKFLOW_STARTER_EMPTY_TEMPLATES')
	      }
	    }
	  });
	}
	function _onTemplateSelect2(template) {
	  this.beginStartWorkflow(template.get('id')).then(() => {}).catch(() => {});
	}
	function _showStepByStepSlider2(componentParams) {
	  return new Promise(resolve => {
	    BX.SidePanel.Instance.open(babelHelpers.classPrivateFieldLooseBase(this, _createStepByStepSliderUrl)[_createStepByStepSliderUrl](componentParams), {
	      width: 900,
	      cacheable: false,
	      allowChangeHistory: false,
	      // loader: '', // todo: loader
	      events: {
	        onCloseComplete: event => {
	          const slider = event.getSlider();
	          const dictionary = slider ? slider.getData() : null;
	          let data = {};
	          if (dictionary && dictionary.has('data')) {
	            data = {
	              workflowId: dictionary.get('data').workflowId || null,
	              signedParameters: dictionary.get('data').signedParameters || null
	            };
	          }
	          resolve(data);
	        }
	      }
	    });
	  });
	}
	function _createStepByStepSliderUrl2(componentParams) {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  let url = main_core.Uri.addParam('/bitrix/components/bitrix/bizproc.workflow.start/', {
	    sessid: main_core.Loc.getMessage('bitrix_sessid')
	  } // todo: remove?
	  );

	  const templateId = main_core.Text.toInteger(componentParams.templateId);
	  if (templateId > 0) {
	    url = main_core.Uri.addParam(url, {
	      templateId
	    });
	  }
	  const autoExecuteType = main_core.Text.toInteger(componentParams.autoExecuteType);
	  if (!main_core.Type.isNil(componentParams.autoExecuteType) && autoExecuteType >= 0) {
	    url = main_core.Uri.addParam(url, {
	      autoExecuteType
	    });
	  }
	  if ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType]) != null && _babelHelpers$classPr.moduleId) {
	    url = main_core.Uri.addParam(url, {
	      moduleId: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType].moduleId,
	      entity: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType].entity,
	      documentType: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentType)[_complexDocumentType].documentType
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType]) {
	    url = main_core.Uri.addParam(url, {
	      signedDocumentType: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentType)[_signedDocumentType]
	    });
	  }
	  if ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentId)[_complexDocumentId]) != null && _babelHelpers$classPr2.documentId) {
	    url = main_core.Uri.addParam(url, {
	      documentId: babelHelpers.classPrivateFieldLooseBase(this, _complexDocumentId)[_complexDocumentId].documentId
	    });
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId]) {
	    url = main_core.Uri.addParam(url, {
	      signedDocumentId: babelHelpers.classPrivateFieldLooseBase(this, _signedDocumentId)[_signedDocumentId]
	    });
	  }
	  return url;
	}
	function _callAction2(action, formData = {}, addData = {}) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _callActionHelper)[_callActionHelper].callAction(action, babelHelpers.classPrivateFieldLooseBase(this, _callActionHelper)[_callActionHelper].addData(addData, formData));
	}
	function _showErrors2(errors, targetWindow) {
	  const notifier = new ErrorNotifier(errors);
	  const method = main_core.Type.isNil(targetWindow) ? 'show' : 'showToWindow';
	  notifier[method](targetWindow);
	}

	var _instances = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instances");
	var _findSimilar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findSimilar");
	var _isEqual = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEqual");
	class Manager {
	  constructor() {
	    Object.defineProperty(this, _isEqual, {
	      value: _isEqual2
	    });
	    Object.defineProperty(this, _findSimilar, {
	      value: _findSimilar2
	    });
	    Object.defineProperty(this, _instances, {
	      writable: true,
	      value: new Set()
	    });
	  }
	  put(starter) {
	    babelHelpers.classPrivateFieldLooseBase(this, _instances)[_instances].add(starter);
	    return this;
	  }
	  remove(starter) {
	    babelHelpers.classPrivateFieldLooseBase(this, _instances)[_instances].delete(starter);
	  }
	  fireEvent(starter, eventName, parameters) {
	    const instances = babelHelpers.classPrivateFieldLooseBase(this, _findSimilar)[_findSimilar](starter);
	    instances.forEach(target => {
	      target.emit(eventName, parameters);
	      main_core_events.EventEmitter.emit(target, eventName, parameters, {
	        useGlobalNaming: true
	      }); // compatibility
	    });
	  }
	}
	function _findSimilar2(target) {
	  const result = [target];
	  babelHelpers.classPrivateFieldLooseBase(this, _instances)[_instances].forEach(starter => {
	    if (starter !== target && babelHelpers.classPrivateFieldLooseBase(this, _isEqual)[_isEqual](target, starter)) {
	      result.push(starter);
	    }
	  });
	  return result;
	}
	function _isEqual2(target, starter) {
	  if (target.signedDocumentType && starter.signedDocumentType) {
	    return target.signedDocumentType === starter.signedDocumentType;
	  }
	  if (target.complexDocumentType) {
	    return target.complexDocumentType.isEqual(starter.complexDocumentType || starter.signedDocumentType);
	  }
	  return starter.complexDocumentType.isEqual(target.complexDocumentType || target.signedDocumentType);
	}

	const managerInstance = new Manager();

	exports.Starter = Starter$$1;
	exports.managerInstance = managerInstance;

}((this.BX.Bizproc.Workflow = this.BX.Bizproc.Workflow || {}),BX.UI.EntitySelector,BX,BX,BX,BX.UI.Dialogs,BX.Event));
//# sourceMappingURL=starter.bundle.js.map
