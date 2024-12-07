/* eslint-disable */
this.BX = this.BX || {};
this.BX.Lists = this.BX.Lists || {};
(function (exports,main_core,main_date,ui_buttons,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10;
	const namespace = main_core.Reflection.namespace('BX.Lists.Component');
	const HTML_ELEMENT_ID = 'lists-element-creation-guide';
	const BP_STATE_FORM_NAME = 'lists_element_creation_guide_bp';
	const BP_STATE_CONSTANTS_FORM_NAME = 'lists_element_creation_guide_bp_constants';
	const AJAX_COMPONENT = 'bitrix:lists.element.creation_guide';
	const STEPS = Object.freeze({
	  DESCRIPTION: 'description',
	  CONSTANTS: 'constants',
	  FIELDS: 'fields'
	});
	var _steps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("steps");
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _description = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("description");
	var _duration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("duration");
	var _signedParameters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("signedParameters");
	var _templateIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("templateIds");
	var _currentStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentStep");
	var _startTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startTime");
	var _descriptionNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("descriptionNode");
	var _durationNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("durationNode");
	var _difference = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("difference");
	var _canUserTuningStates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canUserTuningStates");
	var _isAdminLoaded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAdminLoaded");
	var _isLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLoading");
	var _stepsEnterTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepsEnterTime");
	var _formData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formData");
	var _messageBox = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageBox");
	var _canClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canClose");
	var _setCurrentStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCurrentStep");
	var _fillSteps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fillSteps");
	var _toggleButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleButtons");
	var _isFirstStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFirstStep");
	var _isLastStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isLastStep");
	var _hideButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideButton");
	var _disableButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disableButton");
	var _removeWaitFromButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeWaitFromButton");
	var _setWaitToButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setWaitToButton");
	var _showButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showButton");
	var _enableButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableButton");
	var _renderProgressBar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderProgressBar");
	var _renderFirstStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderFirstStep");
	var _renderDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDescription");
	var _renderExpandDescriptionNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpandDescriptionNode");
	var _toggleDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleDescription");
	var _renderDuration = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDuration");
	var _handleDurationHintClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDurationHintClick");
	var _loadAdminList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadAdminList");
	var _renderAdminList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderAdminList");
	var _notifyAdmin = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notifyAdmin");
	var _setAllConstants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setAllConstants");
	var _setConstants = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setConstants");
	var _showSuccessNotification = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSuccessNotification");
	var _createElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createElement");
	var _appendSectionFormData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendSectionFormData");
	var _appendBPFormData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendBPFormData");
	var _appendStateFormData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("appendStateFormData");
	var _showErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showErrors");
	var _cleanErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cleanErrors");
	var _startLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startLoading");
	var _disableAllButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disableAllButtons");
	var _finishLoading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("finishLoading");
	var _enableAllButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableAllButtons");
	var _addNotTunedConstantsHint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addNotTunedConstantsHint");
	var _removeNotTunedConstantsHint = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeNotTunedConstantsHint");
	var _sendCreationAnalytics = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendCreationAnalytics");
	var _getAnalyticsSection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAnalyticsSection");
	var _isChangedFormData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChangedFormData");
	var _showConfirmDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showConfirmDialog");
	class ElementCreationGuide {
	  constructor(_props) {
	    Object.defineProperty(this, _showConfirmDialog, {
	      value: _showConfirmDialog2
	    });
	    Object.defineProperty(this, _isChangedFormData, {
	      value: _isChangedFormData2
	    });
	    Object.defineProperty(this, _getAnalyticsSection, {
	      value: _getAnalyticsSection2
	    });
	    Object.defineProperty(this, _sendCreationAnalytics, {
	      value: _sendCreationAnalytics2
	    });
	    Object.defineProperty(this, _removeNotTunedConstantsHint, {
	      value: _removeNotTunedConstantsHint2
	    });
	    Object.defineProperty(this, _addNotTunedConstantsHint, {
	      value: _addNotTunedConstantsHint2
	    });
	    Object.defineProperty(this, _enableAllButtons, {
	      value: _enableAllButtons2
	    });
	    Object.defineProperty(this, _finishLoading, {
	      value: _finishLoading2
	    });
	    Object.defineProperty(this, _disableAllButtons, {
	      value: _disableAllButtons2
	    });
	    Object.defineProperty(this, _startLoading, {
	      value: _startLoading2
	    });
	    Object.defineProperty(this, _cleanErrors, {
	      value: _cleanErrors2
	    });
	    Object.defineProperty(this, _showErrors, {
	      value: _showErrors2
	    });
	    Object.defineProperty(this, _appendStateFormData, {
	      value: _appendStateFormData2
	    });
	    Object.defineProperty(this, _appendBPFormData, {
	      value: _appendBPFormData2
	    });
	    Object.defineProperty(this, _appendSectionFormData, {
	      value: _appendSectionFormData2
	    });
	    Object.defineProperty(this, _createElement, {
	      value: _createElement2
	    });
	    Object.defineProperty(this, _showSuccessNotification, {
	      value: _showSuccessNotification2
	    });
	    Object.defineProperty(this, _setConstants, {
	      value: _setConstants2
	    });
	    Object.defineProperty(this, _setAllConstants, {
	      value: _setAllConstants2
	    });
	    Object.defineProperty(this, _notifyAdmin, {
	      value: _notifyAdmin2
	    });
	    Object.defineProperty(this, _renderAdminList, {
	      value: _renderAdminList2
	    });
	    Object.defineProperty(this, _loadAdminList, {
	      value: _loadAdminList2
	    });
	    Object.defineProperty(this, _handleDurationHintClick, {
	      value: _handleDurationHintClick2
	    });
	    Object.defineProperty(this, _renderDuration, {
	      value: _renderDuration2
	    });
	    Object.defineProperty(this, _toggleDescription, {
	      value: _toggleDescription2
	    });
	    Object.defineProperty(this, _renderExpandDescriptionNode, {
	      value: _renderExpandDescriptionNode2
	    });
	    Object.defineProperty(this, _renderDescription, {
	      value: _renderDescription2
	    });
	    Object.defineProperty(this, _renderFirstStep, {
	      value: _renderFirstStep2
	    });
	    Object.defineProperty(this, _renderProgressBar, {
	      value: _renderProgressBar2
	    });
	    Object.defineProperty(this, _enableButton, {
	      value: _enableButton2
	    });
	    Object.defineProperty(this, _showButton, {
	      value: _showButton2
	    });
	    Object.defineProperty(this, _setWaitToButton, {
	      value: _setWaitToButton2
	    });
	    Object.defineProperty(this, _removeWaitFromButton, {
	      value: _removeWaitFromButton2
	    });
	    Object.defineProperty(this, _disableButton, {
	      value: _disableButton2
	    });
	    Object.defineProperty(this, _hideButton, {
	      value: _hideButton2
	    });
	    Object.defineProperty(this, _isLastStep, {
	      value: _isLastStep2
	    });
	    Object.defineProperty(this, _isFirstStep, {
	      value: _isFirstStep2
	    });
	    Object.defineProperty(this, _toggleButtons, {
	      value: _toggleButtons2
	    });
	    Object.defineProperty(this, _fillSteps, {
	      value: _fillSteps2
	    });
	    Object.defineProperty(this, _setCurrentStep, {
	      value: _setCurrentStep2
	    });
	    Object.defineProperty(this, _steps, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _description, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _duration, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _signedParameters, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _templateIds, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _currentStep, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _startTime, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _descriptionNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _durationNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _difference, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canUserTuningStates, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isAdminLoaded, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _isLoading, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _stepsEnterTime, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _formData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _messageBox, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _canClose, {
	      writable: true,
	      value: false
	    });
	    if (!main_core.Type.isStringFilled(_props.signedParameters)) {
	      throw new TypeError('signedParameters must be filled string');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _signedParameters)[_signedParameters] = _props.signedParameters;
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = main_core.Type.isString(_props.name) ? _props.name : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _description)[_description] = main_core.Type.isString(_props.description) ? _props.description : '';
	    if (main_core.Type.isInteger(_props.duration) && _props.duration >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration] = _props.duration;
	    }
	    if (main_core.Type.isArrayFilled(_props.bpTemplateIds)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _templateIds)[_templateIds] = _props.bpTemplateIds;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _canUserTuningStates)[_canUserTuningStates] = main_core.Type.isBoolean(_props.canUserTuningStates) ? _props.canUserTuningStates : false;
	    babelHelpers.classPrivateFieldLooseBase(this, _startTime)[_startTime] = Math.round(Date.now() / 1000);
	    babelHelpers.classPrivateFieldLooseBase(this, _setCurrentStep)[_setCurrentStep](STEPS.DESCRIPTION);
	    babelHelpers.classPrivateFieldLooseBase(this, _fillSteps)[_fillSteps](_props);
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	    babelHelpers.classPrivateFieldLooseBase(this, _renderProgressBar)[_renderProgressBar]();
	    babelHelpers.classPrivateFieldLooseBase(this, _renderFirstStep)[_renderFirstStep]();
	    main_core.Event.EventEmitter.subscribe('SidePanel.Slider:onClose', event => {
	      if (event.target.getWindow() === window && babelHelpers.classPrivateFieldLooseBase(this, _isChangedFormData)[_isChangedFormData]() && !babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose]) {
	        var _babelHelpers$classPr;
	        event.getCompatData()[0].denyAction();
	        if (!((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox]) != null && _babelHelpers$classPr.getPopupWindow().isShown())) {
	          babelHelpers.classPrivateFieldLooseBase(this, _showConfirmDialog)[_showConfirmDialog](event.target);
	        }
	      }
	    });
	  }
	  next() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || babelHelpers.classPrivateFieldLooseBase(this, _isLastStep)[_isLastStep]()) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData]) {
	      const form = document.forms.form_lists_element_creation_guide_element;
	      babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData] = form ? new FormData(form) : new FormData();
	      babelHelpers.classPrivateFieldLooseBase(this, _appendSectionFormData)[_appendSectionFormData](babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData]);
	      babelHelpers.classPrivateFieldLooseBase(this, _appendBPFormData)[_appendBPFormData](babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData]);
	    }
	    const currentStepIndex = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].findIndex(step => step.step === babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep]);
	    const currentStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][currentStepIndex];
	    const nextStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][currentStepIndex + 1];
	    const changeStep = () => {
	      main_core.Dom.toggleClass(currentStep.progressBarNode, ['--active', '--complete']);
	      main_core.Dom.addClass(nextStep.progressBarNode, '--active');
	      babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors]();
	      main_core.Dom.addClass(currentStep.contentNode, '--hidden');
	      main_core.Dom.removeClass(nextStep.contentNode, '--hidden');
	      if (babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.DESCRIPTION) {
	        main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _durationNode)[_durationNode], '--hidden');
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _setCurrentStep)[_setCurrentStep](nextStep.step);
	      babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	    };
	    if (currentStep.step === STEPS.CONSTANTS && babelHelpers.classPrivateFieldLooseBase(this, _canUserTuningStates)[_canUserTuningStates]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _startLoading)[_startLoading]();
	      babelHelpers.classPrivateFieldLooseBase(this, _setAllConstants)[_setAllConstants]().then(() => {
	        changeStep();
	      }).catch(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	      }).finally(babelHelpers.classPrivateFieldLooseBase(this, _finishLoading)[_finishLoading].bind(this));
	      return;
	    }
	    if (nextStep.step === STEPS.CONSTANTS && !babelHelpers.classPrivateFieldLooseBase(this, _canUserTuningStates)[_canUserTuningStates] && !babelHelpers.classPrivateFieldLooseBase(this, _isAdminLoaded)[_isAdminLoaded]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _startLoading)[_startLoading]();
	      babelHelpers.classPrivateFieldLooseBase(this, _loadAdminList)[_loadAdminList]().then(() => {}).catch(() => {}).finally(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _isAdminLoaded)[_isAdminLoaded] = true;
	        babelHelpers.classPrivateFieldLooseBase(this, _finishLoading)[_finishLoading]();
	        changeStep();
	      });
	      return;
	    }
	    changeStep();
	  }
	  back() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFirstStep)[_isFirstStep]()) {
	      if (main_core.Reflection.getClass('BX.SidePanel') && BX.SidePanel.Instance.getSliderByWindow(window)) {
	        BX.SidePanel.Instance.getSliderByWindow(window).close(false);
	        return;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _setCurrentStep)[_setCurrentStep]();
	      return;
	    }
	    const currentStepIndex = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].findIndex(step => step.step === babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep]);
	    const currentStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][currentStepIndex];
	    const previousStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][currentStepIndex - 1];
	    main_core.Dom.removeClass(currentStep.progressBarNode, '--active');
	    main_core.Dom.toggleClass(previousStep.progressBarNode, ['--active', '--complete']);
	    babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors]();
	    main_core.Dom.addClass(currentStep.contentNode, '--hidden');
	    main_core.Dom.removeClass(previousStep.contentNode, '--hidden');
	    if (previousStep.step === STEPS.DESCRIPTION) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _durationNode)[_durationNode], '--hidden');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setCurrentStep)[_setCurrentStep](previousStep.step);
	    babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	  }
	  async create() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] || !babelHelpers.classPrivateFieldLooseBase(this, _isLastStep)[_isLastStep]() || babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.CONSTANTS && !babelHelpers.classPrivateFieldLooseBase(this, _canUserTuningStates)[_canUserTuningStates]) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.CONSTANTS) {
	      babelHelpers.classPrivateFieldLooseBase(this, _startLoading)[_startLoading]();
	      let hasErrors = false;
	      await babelHelpers.classPrivateFieldLooseBase(this, _setAllConstants)[_setAllConstants]().catch(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	        hasErrors = true;
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _finishLoading)[_finishLoading]();
	      if (hasErrors) {
	        return;
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _startLoading)[_startLoading]();
	    babelHelpers.classPrivateFieldLooseBase(this, _createElement)[_createElement]().then(({
	      data
	    }) => {
	      if (main_core.Reflection.getClass('BX.SidePanel') && BX.SidePanel.Instance.getSliderByWindow(window)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	        BX.SidePanel.Instance.getSliderByWindow(window).close(false);
	        babelHelpers.classPrivateFieldLooseBase(this, _showSuccessNotification)[_showSuccessNotification](data.elementUrl);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _sendCreationAnalytics)[_sendCreationAnalytics]();
	    }).catch(error => {
	      babelHelpers.classPrivateFieldLooseBase(this, _toggleButtons)[_toggleButtons]();
	      babelHelpers.classPrivateFieldLooseBase(this, _sendCreationAnalytics)[_sendCreationAnalytics](error);
	    }).finally(babelHelpers.classPrivateFieldLooseBase(this, _finishLoading)[_finishLoading].bind(this));
	  }
	  saveConstants(templateId, button) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _templateIds)[_templateIds].includes(templateId) || !main_core.Type.isDomNode(button) || babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _setWaitToButton)[_setWaitToButton](button);
	    const formData = new FormData();
	    babelHelpers.classPrivateFieldLooseBase(this, _appendStateFormData)[_appendStateFormData](formData, `form_${BP_STATE_CONSTANTS_FORM_NAME}_${templateId}`);
	    formData.append('templateIds[]', templateId);
	    const errorsNode = document.getElementById(`${HTML_ELEMENT_ID}-constants-${templateId}-errors`);
	    babelHelpers.classPrivateFieldLooseBase(this, _startLoading)[_startLoading]();
	    babelHelpers.classPrivateFieldLooseBase(this, _setConstants)[_setConstants](formData).then(() => {
	      if (errorsNode) {
	        babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors](errorsNode);
	      }
	    }).catch(({
	      errors
	    }) => {
	      if (main_core.Type.isArrayFilled(errors) && errorsNode) {
	        babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](errors, errorsNode);
	      }
	    }).finally(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _finishLoading)[_finishLoading]();
	      babelHelpers.classPrivateFieldLooseBase(this, _removeWaitFromButton)[_removeWaitFromButton](button);
	    });
	  }
	  checkEqualFileField(fileFieldA, fileFieldB) {
	    if (!fileFieldB) {
	      return false;
	    }
	    return fileFieldA.name === fileFieldB.name;
	  }
	}
	function _setCurrentStep2(step) {
	  babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] = step;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.DESCRIPTION) {
	    babelHelpers.classPrivateFieldLooseBase(this, _stepsEnterTime)[_stepsEnterTime].set(STEPS.DESCRIPTION, Date.now());
	  } else {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _stepsEnterTime)[_stepsEnterTime].has(STEPS.DESCRIPTION)) {
	      const diffTime = Date.now() - babelHelpers.classPrivateFieldLooseBase(this, _stepsEnterTime)[_stepsEnterTime].get(STEPS.DESCRIPTION);
	      main_core.Runtime.loadExtension('ui.analytics').then(({
	        sendData
	      }) => {
	        sendData({
	          tool: 'automation',
	          category: 'bizproc_operations',
	          event: 'process_instructions_read',
	          p1: babelHelpers.classPrivateFieldLooseBase(this, _name)[_name],
	          p4: Math.round(diffTime / 1000)
	        });
	      }).catch(() => {});
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _stepsEnterTime)[_stepsEnterTime].delete(STEPS.DESCRIPTION);
	  }
	}
	function _fillSteps2(props) {
	  const contentNode = document.querySelectorAll('.list-el-cg__content >.list-el-cg__content-body');
	  const showBPConstantsStep = main_core.Type.isBoolean(props.hasStatesToTuning) ? props.hasStatesToTuning : false;
	  const showFieldsStep = main_core.Type.isBoolean(props.hasFieldsToShow) ? props.hasFieldsToShow : false;
	  babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].push({
	    step: STEPS.DESCRIPTION,
	    contentNode: contentNode.item(0),
	    progressBarNode: null
	  });
	  if (showBPConstantsStep) {
	    babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].push({
	      step: STEPS.CONSTANTS,
	      contentNode: contentNode.item(1),
	      progressBarNode: null
	    });
	  }
	  if (showFieldsStep) {
	    babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].push({
	      step: STEPS.FIELDS,
	      contentNode: contentNode.item(2),
	      progressBarNode: null
	    });
	  }
	}
	function _toggleButtons2() {
	  const backButton = document.getElementById(`${HTML_ELEMENT_ID}-back-button`);
	  const nextButton = document.getElementById(`${HTML_ELEMENT_ID}-next-button`);
	  const createButton = document.getElementById(`${HTML_ELEMENT_ID}-create-button`);
	  babelHelpers.classPrivateFieldLooseBase(this, _removeNotTunedConstantsHint)[_removeNotTunedConstantsHint](createButton);
	  babelHelpers.classPrivateFieldLooseBase(this, _removeNotTunedConstantsHint)[_removeNotTunedConstantsHint](nextButton);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isFirstStep)[_isFirstStep]()) {
	    const showNextStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].length > 1;
	    babelHelpers.classPrivateFieldLooseBase(this, _hideButton)[_hideButton](showNextStep ? createButton : nextButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _showButton)[_showButton](showNextStep ? nextButton : createButton);
	  } else if (babelHelpers.classPrivateFieldLooseBase(this, _isLastStep)[_isLastStep]()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _hideButton)[_hideButton](nextButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _showButton)[_showButton](createButton);
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _hideButton)[_hideButton](createButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _showButton)[_showButton](nextButton);
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.CONSTANTS && !babelHelpers.classPrivateFieldLooseBase(this, _canUserTuningStates)[_canUserTuningStates]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _disableButton)[_disableButton](createButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _disableButton)[_disableButton](nextButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _addNotTunedConstantsHint)[_addNotTunedConstantsHint](createButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _addNotTunedConstantsHint)[_addNotTunedConstantsHint](nextButton);
	  }
	  setTimeout(() => {
	    babelHelpers.classPrivateFieldLooseBase(this, _removeWaitFromButton)[_removeWaitFromButton](backButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _removeWaitFromButton)[_removeWaitFromButton](nextButton);
	    babelHelpers.classPrivateFieldLooseBase(this, _removeWaitFromButton)[_removeWaitFromButton](createButton);
	  }, 100);
	}
	function _isFirstStep2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.DESCRIPTION;
	}
	function _isLastStep2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.DESCRIPTION && babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].length === 1 || babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.CONSTANTS && babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].length === 2 || babelHelpers.classPrivateFieldLooseBase(this, _currentStep)[_currentStep] === STEPS.FIELDS;
	}
	function _hideButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.addClass(button, ['--hidden']);
	    babelHelpers.classPrivateFieldLooseBase(this, _disableButton)[_disableButton](button);
	  }
	}
	function _disableButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.addClass(button, ['ui-btn-disabled']);
	    main_core.Dom.attr(button, 'disabled', 'disabled');
	  }
	}
	function _removeWaitFromButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.removeClass(button, 'ui-btn-wait');
	  }
	}
	function _setWaitToButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.addClass(button, 'ui-btn-wait');
	  }
	}
	function _showButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.removeClass(button, ['--hidden']);
	    babelHelpers.classPrivateFieldLooseBase(this, _enableButton)[_enableButton](button);
	  }
	}
	function _enableButton2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.removeClass(button, ['ui-btn-disabled']);
	    main_core.Dom.attr(button, 'disabled', null);
	  }
	}
	function _renderProgressBar2() {
	  const container = document.getElementById(`${HTML_ELEMENT_ID}-breadcrumbs`);
	  if (!container) {
	    return;
	  }
	  const {
	    step0,
	    step1,
	    step2
	  } = main_core.Tag.render(_t || (_t = _`
			<div>
				<div class="list-el-cg__breadcrumbs-item --active" ref="step0">
					<span>${0}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
				<div class="list-el-cg__breadcrumbs-item" ref="step1">
					<span>${0}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
				<div class="list-el-cg__breadcrumbs-item" ref="step2">
					<span>${0}</span>
					<span class="ui-icon-set --chevron-right"></span>
				</div>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_RECOMMENDATION')), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_CONSTANTS')), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_STEP_FIELDS')));
	  babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][0].progressBarNode = step0;
	  main_core.Dom.append(step0, container);
	  const constantsStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].find(step => step.step === STEPS.CONSTANTS);
	  if (constantsStep) {
	    constantsStep.progressBarNode = step1;
	    main_core.Dom.append(step1, container);
	  }
	  const fieldsStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].find(step => step.step === STEPS.FIELDS);
	  if (fieldsStep) {
	    fieldsStep.progressBarNode = step2;
	    main_core.Dom.append(step2, container);
	  }
	}
	function _renderFirstStep2() {
	  const container = document.getElementById(`${HTML_ELEMENT_ID}-container`);
	  const contentNode = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps][0].contentNode;
	  if (container && contentNode) {
	    const description = babelHelpers.classPrivateFieldLooseBase(this, _renderDescription)[_renderDescription]();
	    babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode] = description;
	    main_core.Dom.append(description, contentNode);
	    const expandNode = babelHelpers.classPrivateFieldLooseBase(this, _renderExpandDescriptionNode)[_renderExpandDescriptionNode]();
	    main_core.Dom.append(expandNode, contentNode);
	    babelHelpers.classPrivateFieldLooseBase(this, _durationNode)[_durationNode] = babelHelpers.classPrivateFieldLooseBase(this, _renderDuration)[_renderDuration]();
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _durationNode)[_durationNode], container);
	    const slider = document.querySelector('.ui-page-slider-workarea-content-padding');
	    const difference = slider ? slider.offsetHeight - window.innerHeight : 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _difference)[_difference] = difference;
	    if (difference > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _toggleDescription)[_toggleDescription]({
	        target: expandNode
	      });
	    } else {
	      main_core.Dom.remove(expandNode);
	    }
	  }
	}
	function _renderDescription2() {
	  if (main_core.Type.isStringFilled(babelHelpers.classPrivateFieldLooseBase(this, _description)[_description])) {
	    return main_core.Tag.render(_t2 || (_t2 = _`
				<div class="list-el-cg__content-wrapper">
					${0}
				</div>
			`), BX.util.nl2br(babelHelpers.classPrivateFieldLooseBase(this, _description)[_description]));
	  }
	  return main_core.Tag.render(_t3 || (_t3 = _`
			<div class="list-el-cg__content-wrapper">
				<span class="list-el-cg__text-empty">${0}</span>
			</div>
		`), main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EMPTY_DESCRIPTION'));
	}
	function _renderExpandDescriptionNode2() {
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="list-el-cg__content-open" onclick="${0}">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _toggleDescription)[_toggleDescription].bind(this), main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXPAND_DESCRIPTION'));
	}
	function _toggleDescription2(event) {
	  const target = event.target;
	  if (target && babelHelpers.classPrivateFieldLooseBase(this, _difference)[_difference] > 0) {
	    main_core.Dom.clean(target);
	    if (main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode], '--hide')) {
	      target.innerText = main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_COLLAPSE_DESCRIPTION');
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode], 'height', `${babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode].scrollHeight}px`);
	    } else {
	      target.innerText = main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXPAND_DESCRIPTION');
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode], 'height', `${babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode].offsetHeight - babelHelpers.classPrivateFieldLooseBase(this, _difference)[_difference]}px`);
	    }
	    main_core.Dom.toggleClass(babelHelpers.classPrivateFieldLooseBase(this, _descriptionNode)[_descriptionNode], ['--hide']);
	  }
	}
	function _renderDuration2() {
	  if (main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration])) {
	    return main_core.Tag.render(_t5 || (_t5 = _`
				<div class="list-el-cg__informer">
					<div class="list-el-cg__informer-header">
						<div class="list-el-cg__informer-title">
							${0}
						</div>
						<div class="list-el-cg__informer-time">
							<span class="list-el-cg__text-empty">${0}</span>
						</div>
					</div>
					<div class="list-el-cg__informer-message">${0}</div>
					<div class="list-el-cg__informer-bottom"></div>
				</div>
			`), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_TITLE')), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EMPTY_DURATION')), main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_UNDEFINED_DESCRIPTION'));
	  }
	  let formattedDuration = main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_ZERO_DURATION');
	  if (babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration] > 0) {
	    formattedDuration = main_date.DateTimeFormat.format([['s', 'sdiff'], ['i', 'idiff'], ['H', 'Hdiff'], ['d', 'ddiff'], ['m', 'mdiff'], ['Y', 'Ydiff']], 0, babelHelpers.classPrivateFieldLooseBase(this, _duration)[_duration]);
	  }
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="list-el-cg__informer">
				<div class="list-el-cg__informer-header">
					<div class="list-el-cg__informer-title">
						${0}
					</div>
					<div class="list-el-cg__informer-time">
						<span>${0}</span>
						<div class="ui-icon-set --time-picker"></div>
					</div>
				</div>
				<div class="list-el-cg__informer-message">${0}</div>
				<div class="list-el-cg__informer-bottom">
					<a
						class="list-el-cg__link" href="#"
						onclick="${0}"
					>${0}
					</a>
				</div>
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_TITLE')), main_core.Text.encode(formattedDuration), main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_DESCRIPTION'), babelHelpers.classPrivateFieldLooseBase(this, _handleDurationHintClick)[_handleDurationHintClick], main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_AVERAGE_DURATION_HINT'));
	}
	function _handleDurationHintClick2(event) {
	  event.preventDefault();
	  const ARTICLE_ID = '18783714';
	  const helper = main_core.Reflection.getClass('top.BX.Helper');
	  if (helper) {
	    helper.show(`redirect=detail&code=${ARTICLE_ID}`);
	  }
	}
	function _loadAdminList2() {
	  return new Promise((resolve, reject) => {
	    const constantStep = babelHelpers.classPrivateFieldLooseBase(this, _steps)[_steps].find(step => step.step === STEPS.CONSTANTS);
	    main_core.ajax.runComponentAction(AJAX_COMPONENT, 'getListAdmin', {
	      json: {
	        signedParameters: babelHelpers.classPrivateFieldLooseBase(this, _signedParameters)[_signedParameters]
	      }
	    }).then(({
	      data
	    }) => {
	      if (main_core.Type.isArrayFilled(data == null ? void 0 : data.admins)) {
	        main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _renderAdminList)[_renderAdminList](data.admins, data.canNotify), constantStep.contentNode);
	      }
	      resolve();
	    }).catch(reject);
	  });
	}
	function _renderAdminList2(admins, canNotify = false) {
	  return main_core.Tag.render(_t7 || (_t7 = _`
			<div>
				<div class="list-el-cg__const-desc">
					${0}
				</div>
				<div class="list-el-cg__const-title">
					${0}
				</div>
				${0}
			</div>
		`), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_ADMIN')), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY')), admins.map(admin => {
	    var _button;
	    let button = null;
	    if (canNotify) {
	      button = new ui_buttons.Button({
	        text: main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_BUTTON'),
	        size: ui_buttons.ButtonSize.MEDIUM,
	        color: ui_buttons.ButtonColor.PRIMARY,
	        onclick: babelHelpers.classPrivateFieldLooseBase(this, _notifyAdmin)[_notifyAdmin].bind(this, admin)
	      });
	    }
	    return main_core.Tag.render(_t8 || (_t8 = _`
						<div class="list-el-cg__const-box">
							<div class="list-el-cg__const-user">
								<div
									class="ui-icon ui-icon-common-user list-el-cg__const-icon"
									bx-tooltip-user-id="${0}"								
								>
									<i style="background-image: url('${0}');"></i>
								</div>
								<span class="list-el-cg__const-name">${0}</span>
							</div>						
							<div>
								${0}
							</div>
						</div>
					`), admin.id, admin.img ? encodeURI(main_core.Text.encode(admin.img)) : '/bitrix/js/ui/icons/b24/images/ui-user.svg?v2', main_core.Text.encode(admin.name), (_button = button) == null ? void 0 : _button.render());
	  }));
	}
	function _notifyAdmin2(admin, button) {
	  button.setWaiting(true);
	  main_core.ajax.runComponentAction(AJAX_COMPONENT, 'notifyAdmin', {
	    json: {
	      signedParameters: babelHelpers.classPrivateFieldLooseBase(this, _signedParameters)[_signedParameters],
	      adminId: admin.id
	    }
	  }).then(({
	    data
	  }) => {
	    if (data.success === true) {
	      main_core.Dom.replace(button.getContainer(), main_core.Tag.render(_t9 || (_t9 = _`
							<span class="list-el-cg__const-success-text">
								${0}
							</span>
						`), main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_NOTIFY_SUCCESS'))));
	    }
	    button.setWaiting(false);
	  }).catch(() => {
	    button.setWaiting(false);
	  });
	}
	function _setAllConstants2() {
	  return new Promise((resolve, reject) => {
	    const formData = new FormData();
	    babelHelpers.classPrivateFieldLooseBase(this, _appendBPFormData)[_appendBPFormData](formData, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _setConstants)[_setConstants](formData).then(resolve).catch(({
	      errors
	    }) => {
	      if (Array.isArray(errors)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](errors);
	      }
	      reject();
	    });
	  });
	}
	function _setConstants2(formData) {
	  return new Promise((resolve, reject) => {
	    formData.set('signedParameters', babelHelpers.classPrivateFieldLooseBase(this, _signedParameters)[_signedParameters]);
	    main_core.ajax.runComponentAction(AJAX_COMPONENT, 'setConstants', {
	      data: formData
	    }).then(resolve).catch(reject);
	  });
	}
	function _showSuccessNotification2(href) {
	  const topRuntime = main_core.Reflection.getClass('top.BX.Runtime');
	  if (topRuntime) {
	    topRuntime.loadExtension('ui.notification').then(() => {
	      if (main_core.Reflection.getClass('top.BX.UI.Notification.Center')) {
	        const actions = [];
	        if (main_core.Type.isStringFilled(href)) {
	          actions.push({
	            href,
	            title: main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SUCCESS_CREATE_SEE')
	          });
	        }
	        top.BX.UI.Notification.Center.notify({
	          content: main_core.Text.encode(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_SUCCESS_CREATE')),
	          actions
	        });
	      }
	    }).catch(() => {});
	  }
	}
	function _createElement2() {
	  return new Promise((resolve, reject) => {
	    const form = document.forms.form_lists_element_creation_guide_element;
	    const formData = form ? new FormData(form) : new FormData();
	    babelHelpers.classPrivateFieldLooseBase(this, _appendSectionFormData)[_appendSectionFormData](formData);
	    babelHelpers.classPrivateFieldLooseBase(this, _appendBPFormData)[_appendBPFormData](formData);
	    formData.set('signedParameters', babelHelpers.classPrivateFieldLooseBase(this, _signedParameters)[_signedParameters]);
	    formData.set('time', Math.round(Date.now() / 1000) - babelHelpers.classPrivateFieldLooseBase(this, _startTime)[_startTime]);
	    main_core.ajax.runComponentAction(AJAX_COMPONENT, 'create', {
	      data: formData
	    }).then(resolve).catch(({
	      errors
	    }) => {
	      if (Array.isArray(errors)) {
	        babelHelpers.classPrivateFieldLooseBase(this, _showErrors)[_showErrors](errors);
	      }
	      reject(new Error(errors[0].message));
	    });
	  });
	}
	function _appendSectionFormData2(formData) {
	  const form = document.forms.form_lists_element_creation_guide_section;
	  if (form) {
	    formData.set('IBLOCK_SECTION_ID', new FormData(form).get('IBLOCK_SECTION_ID'));
	  }
	}
	function _appendBPFormData2(formData, isConstantsForms = false) {
	  babelHelpers.classPrivateFieldLooseBase(this, _templateIds)[_templateIds].forEach(id => {
	    const formId = `form_${isConstantsForms ? BP_STATE_CONSTANTS_FORM_NAME : BP_STATE_FORM_NAME}_${id}`;
	    if (document.forms[formId]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _appendStateFormData)[_appendStateFormData](formData, formId);
	      formData.append('templateIds[]', id);
	    }
	  });
	}
	function _appendStateFormData2(formData, formId) {
	  const form = document.forms[formId];
	  if (form) {
	    for (const [key, value] of new FormData(form).entries()) {
	      if (key !== 'sessid') {
	        formData.append(key, value);
	      }
	    }
	  }
	}
	function _showErrors2(errors, toNode = null) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cleanErrors)[_cleanErrors](toNode);
	  const errorsNode = main_core.Type.isDomNode(toNode) ? toNode : document.getElementById(`${HTML_ELEMENT_ID}-errors`);
	  if (errorsNode) {
	    let message = '';
	    errors.forEach(error => {
	      if (error.message) {
	        message += main_core.Text.encode(error.message);
	        message += '<br/>';
	      }
	    });
	    main_core.Dom.append(main_core.Tag.render(_t10 || (_t10 = _`
					<div class="ui-alert ui-alert-danger">
						<span class="ui-alert-message">${0}</span>
					</div>
				`), message), errorsNode);
	    BX.scrollToNode(errorsNode);
	  }
	}
	function _cleanErrors2(fromNode = null) {
	  if (main_core.Type.isDomNode(fromNode)) {
	    main_core.Dom.clean(fromNode);
	    return;
	  }
	  const errorsNode = document.getElementById(`${HTML_ELEMENT_ID}-errors`);
	  if (errorsNode) {
	    main_core.Dom.clean(errorsNode);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _templateIds)[_templateIds].forEach(templateId => {
	    const node = document.getElementById(`${HTML_ELEMENT_ID}-constants-${templateId}-errors`);
	    if (node) {
	      main_core.Dom.clean(node);
	    }
	  });
	}
	function _startLoading2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = true;
	  babelHelpers.classPrivateFieldLooseBase(this, _disableAllButtons)[_disableAllButtons]();
	}
	function _disableAllButtons2() {
	  for (const button of document.getElementsByClassName('ui-btn')) {
	    babelHelpers.classPrivateFieldLooseBase(this, _disableButton)[_disableButton](button);
	  }
	}
	function _finishLoading2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _isLoading)[_isLoading] = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _enableAllButtons)[_enableAllButtons]();
	}
	function _enableAllButtons2() {
	  for (const button of document.getElementsByClassName('ui-btn')) {
	    babelHelpers.classPrivateFieldLooseBase(this, _enableButton)[_enableButton](button);
	  }
	}
	function _addNotTunedConstantsHint2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.attr(button, 'title', main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_NOT_TUNING_CONSTANTS_HINT'));
	  }
	}
	function _removeNotTunedConstantsHint2(button) {
	  if (main_core.Type.isDomNode(button)) {
	    main_core.Dom.attr(button, 'title', null);
	  }
	}
	function _sendCreationAnalytics2(error) {
	  main_core.Runtime.loadExtension('ui.analytics').then(({
	    sendData
	  }) => {
	    sendData({
	      tool: 'automation',
	      category: 'bizproc_operations',
	      event: 'process_run',
	      type: 'run',
	      c_section: babelHelpers.classPrivateFieldLooseBase(this, _getAnalyticsSection)[_getAnalyticsSection](),
	      p1: babelHelpers.classPrivateFieldLooseBase(this, _name)[_name],
	      status: error ? 'error' : 'success'
	    });
	  }).catch(() => {});
	}
	function _getAnalyticsSection2() {
	  return new main_core.Uri(window.location.href).getQueryParam('analyticsSection') || 'bizproc';
	}
	function _isChangedFormData2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData]) {
	    return false;
	  }
	  const form = document.forms.form_lists_element_creation_guide_element;
	  const formData = form ? new FormData(form) : new FormData();
	  babelHelpers.classPrivateFieldLooseBase(this, _appendSectionFormData)[_appendSectionFormData](formData);
	  babelHelpers.classPrivateFieldLooseBase(this, _appendBPFormData)[_appendBPFormData](formData);
	  const originFormData = Object.fromEntries(babelHelpers.classPrivateFieldLooseBase(this, _formData)[_formData].entries());
	  for (const [key, value] of formData.entries()) {
	    if (main_core.Type.isFile(value)) {
	      if (!this.checkEqualFileField(value, originFormData[key])) {
	        return true;
	      }
	    } else if (value !== originFormData[key]) {
	      return true;
	    }
	  }
	  return false;
	}
	function _showConfirmDialog2(slider) {
	  babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox] = ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_DESCRIPTION'), main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_TITLE'), () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _canClose)[_canClose] = true;
	    slider.close();
	  }, main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_CONFIRM'), () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox].close();
	    babelHelpers.classPrivateFieldLooseBase(this, _messageBox)[_messageBox] = null;
	  }, main_core.Loc.getMessage('LISTS_ELEMENT_CREATION_GUIDE_CMP_EXIT_DIALOG_CANCEL'));
	}
	namespace.ElementCreationGuide = ElementCreationGuide;

}((this.BX.Lists.Component = this.BX.Lists.Component || {}),BX,BX.Main,BX.UI,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
