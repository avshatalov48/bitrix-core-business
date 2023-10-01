/* eslint-disable */
this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,ui_tour,main_core,main_core_events,bizproc_localSettings) {
	'use strict';

	var _IS_SHOWN_SUCCESS_AUTOMATION = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("IS_SHOWN_SUCCESS_AUTOMATION");
	var _SHOW_SUCCESS_AUTOMATION = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("SHOW_SUCCESS_AUTOMATION");
	var _SHOW_CHECK_AUTOMATION = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("SHOW_CHECK_AUTOMATION");
	var _SHOW_HOW_CHECK_TRIIGGER = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("SHOW_HOW_CHECK_TRIIGGER");
	var _SHOW_HOW_CHECK_ROBOT = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("SHOW_HOW_CHECK_ROBOT");
	var _TRIGGER_ADDED = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("TRIGGER_ADDED");
	var _ROBOT_ADDED = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("ROBOT_ADDED");
	var _subscribeOnStartCheckAutomationEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOnStartCheckAutomationEvents");
	var _getStartCheckAutomationHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStartCheckAutomationHandlers");
	var _isSuccessAutomationStepShown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSuccessAutomationStepShown");
	var _isTargetDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTargetDocumentType");
	var _isCorrectDocumentType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCorrectDocumentType");
	var _isNeedShowSuccessAutomationStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNeedShowSuccessAutomationStep");
	var _isNeedShowCheckAutomationStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isNeedShowCheckAutomationStep");
	var _saveUserOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveUserOption");
	var _sendUserOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendUserOption");
	var _deleteUserOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deleteUserOption");
	var _getGuide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getGuide");
	var _isTargetExist = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTargetExist");
	class CheckAutomationCrm {
	  static startCheckAutomationTour(documentType, categoryId) {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isCorrectDocumentType)[_isCorrectDocumentType](documentType) || !main_core.Type.isNumber(categoryId)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOnStartCheckAutomationEvents)[_subscribeOnStartCheckAutomationEvents](documentType, categoryId);
	  }
	  static showHowCheckAutomationGuide(documentType, categoryId, options) {
	    var _document$querySelect;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSuccessAutomationStepShown)[_isSuccessAutomationStepShown](options) || !babelHelpers.classPrivateFieldLooseBase(this, _isTargetDocumentType)[_isTargetDocumentType](documentType, categoryId, options) || babelHelpers.classPrivateFieldLooseBase(this, _isNeedShowSuccessAutomationStep)[_isNeedShowSuccessAutomationStep](options) || babelHelpers.classPrivateFieldLooseBase(this, _isNeedShowCheckAutomationStep)[_isNeedShowCheckAutomationStep](options)) {
	      return;
	    }
	    const showTriggerGuide = options[babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_TRIIGGER)[_SHOW_HOW_CHECK_TRIIGGER]] === true;
	    const showRobotGuide = options[babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_ROBOT)[_SHOW_HOW_CHECK_ROBOT]] === true;
	    if (!showTriggerGuide && !showRobotGuide) {
	      return;
	    }
	    const title = showTriggerGuide ? main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_TRIGGER_TITLE') : main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_ROBOT_TITLE');
	    const text = showTriggerGuide ? main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_TRIGGER_TEXT') : main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_HOW_CHECK_ROBOT_TEXT');

	    // kanban or list
	    const target = (_document$querySelect = document.querySelector('.main-kanban-item')) != null ? _document$querySelect : document.querySelector('.main-grid-row.main-grid-row-body:not(.main-grid-not-count) .main-grid-cell.main-grid-cell-left');
	    const guide = babelHelpers.classPrivateFieldLooseBase(this, _getGuide)[_getGuide]({
	      target,
	      title,
	      text
	    });
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isTargetExist)[_isTargetExist](guide.getCurrentStep().getTarget())) {
	      return;
	    }
	    guide.showNextStep();
	  }
	  static showCheckAutomation(documentType, categoryId, options) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSuccessAutomationStepShown)[_isSuccessAutomationStepShown](options) || !babelHelpers.classPrivateFieldLooseBase(this, _isTargetDocumentType)[_isTargetDocumentType](documentType, categoryId, options) || babelHelpers.classPrivateFieldLooseBase(this, _isNeedShowSuccessAutomationStep)[_isNeedShowSuccessAutomationStep](options) || !babelHelpers.classPrivateFieldLooseBase(this, _isNeedShowCheckAutomationStep)[_isNeedShowCheckAutomationStep](options)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption](babelHelpers.classPrivateFieldLooseBase(this, _SHOW_CHECK_AUTOMATION)[_SHOW_CHECK_AUTOMATION], 'N');
	    babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption](babelHelpers.classPrivateFieldLooseBase(this, _SHOW_SUCCESS_AUTOMATION)[_SHOW_SUCCESS_AUTOMATION], 'Y');
	    babelHelpers.classPrivateFieldLooseBase(this, _sendUserOption)[_sendUserOption]();
	    const guide = babelHelpers.classPrivateFieldLooseBase(this, _getGuide)[_getGuide]({
	      target: '[data-id="tab_automation"]',
	      title: main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_CHECK_AUTOMATION_TITLE'),
	      text: main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_CHECK_AUTOMATION_TEXT'),
	      condition: {
	        top: true,
	        bottom: false,
	        color: 'primary'
	      }
	    });
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isTargetExist)[_isTargetExist](guide.getCurrentStep().getTarget()) || guide.getCurrentStep().getTarget().offsetTop > 0) {
	      guide.getCurrentStep().setTarget('.main-buttons-item.main-buttons-item-more-default.main-buttons-item-more.--has-menu');
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _isTargetExist)[_isTargetExist](guide.getCurrentStep().getTarget())) {
	        return;
	      }
	    }
	    guide.showNextStep();
	  }
	  static showSuccessAutomation(documentType, categoryId, options) {
	    var _document$querySelect2;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSuccessAutomationStepShown)[_isSuccessAutomationStepShown](options) || !babelHelpers.classPrivateFieldLooseBase(this, _isTargetDocumentType)[_isTargetDocumentType](documentType, categoryId, options) || !babelHelpers.classPrivateFieldLooseBase(this, _isNeedShowSuccessAutomationStep)[_isNeedShowSuccessAutomationStep](options)) {
	      return;
	    }

	    // success trigger or robot
	    let target = (_document$querySelect2 = document.querySelector('.bizproc-automation-trigger-item.--complete')) != null ? _document$querySelect2 : document.querySelector('.bizproc-automation-robot-container.--complete');
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isTargetExist)[_isTargetExist](target)) {
	      var _document$querySelect3;
	      // trigger or robot
	      target = (_document$querySelect3 = document.querySelector('.bizproc-automation-trigger-item')) != null ? _document$querySelect3 : document.querySelector('.bizproc-automation-robot-container');
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _isTargetExist)[_isTargetExist](target)) {
	        return;
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _deleteUserOption)[_deleteUserOption]();
	    babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption](babelHelpers.classPrivateFieldLooseBase(this, _IS_SHOWN_SUCCESS_AUTOMATION)[_IS_SHOWN_SUCCESS_AUTOMATION], 'Y');
	    const guide = babelHelpers.classPrivateFieldLooseBase(this, _getGuide)[_getGuide]({
	      target,
	      title: main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_SUCCESS_AUTOMATION_TITLE'),
	      text: main_core.Loc.getMessage('BIZPROC_JS_WOW_MOMENT_CRM_SUCCESS_AUTOMATION_TEXT'),
	      article: '6908975',
	      position: 'top'
	    });
	    guide.showNextStep();
	  }

	  // endregion
	}
	function _subscribeOnStartCheckAutomationEvents2(documentType, categoryId) {
	  const handlers = babelHelpers.classPrivateFieldLooseBase(this, _getStartCheckAutomationHandlers)[_getStartCheckAutomationHandlers]();
	  for (const eventName of Object.keys(handlers)) {
	    main_core_events.EventEmitter.subscribe(eventName, handlers[eventName]);
	  }
	  const slider = BX.SidePanel.Instance.getSliderByWindow(window);
	  let options = {};
	  if (slider) {
	    const localSettings = new bizproc_localSettings.Settings('aut-guide-crm-check-automation');
	    main_core_events.EventEmitter.subscribeOnce(slider, 'SidePanel.Slider:onCloseComplete', () => {
	      for (const eventName of Object.keys(handlers)) {
	        main_core_events.EventEmitter.unsubscribe(eventName, handlers[eventName]);
	      }
	      if (localSettings.get(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_TRIIGGER)[_SHOW_HOW_CHECK_TRIIGGER]) === true || localSettings.get(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_ROBOT)[_SHOW_HOW_CHECK_ROBOT]) === true) {
	        babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption]('document_type', documentType);
	        babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption]('category_id', categoryId);
	        babelHelpers.classPrivateFieldLooseBase(this, _saveUserOption)[_saveUserOption](babelHelpers.classPrivateFieldLooseBase(this, _SHOW_CHECK_AUTOMATION)[_SHOW_CHECK_AUTOMATION], 'Y');
	        options = Object.assign({
	          document_type: documentType,
	          category_id: categoryId
	        }, localSettings.getAll());
	        babelHelpers.classPrivateFieldLooseBase(this, _sendUserOption)[_sendUserOption]();
	      }
	      localSettings.deleteAll();
	    });
	  }
	  const targetWindow = window.top;
	  const showHowCheckAutomationGuide = () => {
	    if (targetWindow.BX.SidePanel.Instance.getOpenSlidersCount() <= 0) {
	      targetWindow.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onCloseComplete', showHowCheckAutomationGuide);
	      targetWindow.BX.Runtime.loadExtension('bizproc.automation.guide').then(exports => {
	        const {
	          CrmCheckAutomationGuide
	        } = exports;
	        CrmCheckAutomationGuide.showHowCheckAutomationGuide(documentType, categoryId, options);
	      });
	      return true;
	    }
	    return false;
	  };
	  targetWindow.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', showHowCheckAutomationGuide);
	}
	function _getStartCheckAutomationHandlers2() {
	  const localSettings = new bizproc_localSettings.Settings('aut-guide-crm-check-automation');
	  localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _TRIGGER_ADDED)[_TRIGGER_ADDED], false);
	  localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_TRIIGGER)[_SHOW_HOW_CHECK_TRIIGGER], false);
	  localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _ROBOT_ADDED)[_ROBOT_ADDED], false);
	  localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_ROBOT)[_SHOW_HOW_CHECK_ROBOT], false);
	  const handlers = {};
	  handlers['BX.Bizproc.Automation:TriggerManager:trigger:add'] = () => {
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _TRIGGER_ADDED)[_TRIGGER_ADDED], true);
	  };
	  handlers['BX.Bizproc.Automation:Template:robot:add'] = () => {
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _ROBOT_ADDED)[_ROBOT_ADDED], true);
	  };
	  handlers['BX.Bizproc.Component.Automation.Component:onSuccessAutomationSave'] = event => {
	    const triggersCount = event.getData()['analyticsLabel']['triggers_count'];
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_TRIIGGER)[_SHOW_HOW_CHECK_TRIIGGER], triggersCount > 0 && localSettings.get(babelHelpers.classPrivateFieldLooseBase(this, _TRIGGER_ADDED)[_TRIGGER_ADDED]) === true);
	    const robotsCount = event.getData()['analyticsLabel']['robots_count'];
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _SHOW_HOW_CHECK_ROBOT)[_SHOW_HOW_CHECK_ROBOT], robotsCount > 0 && localSettings.get(babelHelpers.classPrivateFieldLooseBase(this, _ROBOT_ADDED)[_ROBOT_ADDED]) === true);
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _TRIGGER_ADDED)[_TRIGGER_ADDED], false);
	    localSettings.set(babelHelpers.classPrivateFieldLooseBase(this, _ROBOT_ADDED)[_ROBOT_ADDED], false);
	  };
	  return handlers;
	}
	function _isSuccessAutomationStepShown2(options) {
	  return options[babelHelpers.classPrivateFieldLooseBase(this, _IS_SHOWN_SUCCESS_AUTOMATION)[_IS_SHOWN_SUCCESS_AUTOMATION]] === 'Y';
	}
	function _isTargetDocumentType2(documentType, categoryId, options) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isCorrectDocumentType)[_isCorrectDocumentType](documentType) && main_core.Type.isStringFilled(options['document_type']) && options['document_type'] === documentType && Number(options['category_id']) === Number(categoryId);
	}
	function _isCorrectDocumentType2(documentType) {
	  return main_core.Type.isStringFilled(documentType) && (['LEAD', 'DEAL', 'SMART_INVOICE', 'QUOTE', 'ORDER'].includes(documentType) || documentType.startsWith('DYNAMIC_'));
	}
	function _isNeedShowSuccessAutomationStep2(options) {
	  return options[babelHelpers.classPrivateFieldLooseBase(this, _SHOW_SUCCESS_AUTOMATION)[_SHOW_SUCCESS_AUTOMATION]] === 'Y';
	}
	function _isNeedShowCheckAutomationStep2(options) {
	  return options[babelHelpers.classPrivateFieldLooseBase(this, _SHOW_CHECK_AUTOMATION)[_SHOW_CHECK_AUTOMATION]] === 'Y';
	}
	function _saveUserOption2(key, value) {
	  BX.userOptions.save('bizproc.automation.guide', 'crm_check_automation', key, value, false);
	}
	function _sendUserOption2() {
	  BX.userOptions.send(null);
	}
	function _deleteUserOption2() {
	  BX.userOptions.del('bizproc.automation.guide', 'crm_check_automation');
	}
	function _getGuide2(options) {
	  var _options$article;
	  return new ui_tour.Guide({
	    steps: [{
	      target: options.target,
	      title: options.title,
	      text: options.text,
	      position: options.position | 'bottom',
	      condition: main_core.Type.isPlainObject(options.condition) ? options.condition : null,
	      article: (_options$article = options.article) != null ? _options$article : null
	    }],
	    onEvents: true
	  });
	}
	function _isTargetExist2(target) {
	  return main_core.Type.isElementNode(target);
	}
	Object.defineProperty(CheckAutomationCrm, _isTargetExist, {
	  value: _isTargetExist2
	});
	Object.defineProperty(CheckAutomationCrm, _getGuide, {
	  value: _getGuide2
	});
	Object.defineProperty(CheckAutomationCrm, _deleteUserOption, {
	  value: _deleteUserOption2
	});
	Object.defineProperty(CheckAutomationCrm, _sendUserOption, {
	  value: _sendUserOption2
	});
	Object.defineProperty(CheckAutomationCrm, _saveUserOption, {
	  value: _saveUserOption2
	});
	Object.defineProperty(CheckAutomationCrm, _isNeedShowCheckAutomationStep, {
	  value: _isNeedShowCheckAutomationStep2
	});
	Object.defineProperty(CheckAutomationCrm, _isNeedShowSuccessAutomationStep, {
	  value: _isNeedShowSuccessAutomationStep2
	});
	Object.defineProperty(CheckAutomationCrm, _isCorrectDocumentType, {
	  value: _isCorrectDocumentType2
	});
	Object.defineProperty(CheckAutomationCrm, _isTargetDocumentType, {
	  value: _isTargetDocumentType2
	});
	Object.defineProperty(CheckAutomationCrm, _isSuccessAutomationStepShown, {
	  value: _isSuccessAutomationStepShown2
	});
	Object.defineProperty(CheckAutomationCrm, _getStartCheckAutomationHandlers, {
	  value: _getStartCheckAutomationHandlers2
	});
	Object.defineProperty(CheckAutomationCrm, _subscribeOnStartCheckAutomationEvents, {
	  value: _subscribeOnStartCheckAutomationEvents2
	});
	Object.defineProperty(CheckAutomationCrm, _IS_SHOWN_SUCCESS_AUTOMATION, {
	  writable: true,
	  value: 'success_automation_shown'
	});
	Object.defineProperty(CheckAutomationCrm, _SHOW_SUCCESS_AUTOMATION, {
	  writable: true,
	  value: 'show_success_automation'
	});
	Object.defineProperty(CheckAutomationCrm, _SHOW_CHECK_AUTOMATION, {
	  writable: true,
	  value: 'show_check_automation'
	});
	Object.defineProperty(CheckAutomationCrm, _SHOW_HOW_CHECK_TRIIGGER, {
	  writable: true,
	  value: 'show_how_check_trigger'
	});
	Object.defineProperty(CheckAutomationCrm, _SHOW_HOW_CHECK_ROBOT, {
	  writable: true,
	  value: 'show_how_check_robot'
	});
	Object.defineProperty(CheckAutomationCrm, _TRIGGER_ADDED, {
	  writable: true,
	  value: 'is_trigger_added'
	});
	Object.defineProperty(CheckAutomationCrm, _ROBOT_ADDED, {
	  writable: true,
	  value: 'is_robot_added'
	});

	exports.CrmCheckAutomationGuide = CheckAutomationCrm;

}((this.BX.Bizproc.Automation = this.BX.Bizproc.Automation || {}),BX.UI.Tour,BX,BX.Event,BX.Bizproc.LocalSettings));
//# sourceMappingURL=guide.bundle.js.map
