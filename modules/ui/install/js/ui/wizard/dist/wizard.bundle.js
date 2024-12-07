/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _metadata = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("metadata");
	var _order = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("order");
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _stepIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepIndex");
	var _stepNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stepNode");
	var _stages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("stages");
	var _navigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("navigationButtons");
	var _createNavigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createNavigationButtons");
	var _createStages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createStages");
	var _onPrevStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPrevStep");
	var _tryCompleteStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryCompleteStep");
	var _onNextStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onNextStep");
	var _getButtonsTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtonsTitle");
	var _renderNavigationButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderNavigationButtons");
	var _renderActiveStage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderActiveStage");
	var _renderStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderStep");
	class Wizard {
	  constructor(metadata = {}, options = {}) {
	    Object.defineProperty(this, _renderStep, {
	      value: _renderStep2
	    });
	    Object.defineProperty(this, _renderActiveStage, {
	      value: _renderActiveStage2
	    });
	    Object.defineProperty(this, _renderNavigationButtons, {
	      value: _renderNavigationButtons2
	    });
	    Object.defineProperty(this, _getButtonsTitle, {
	      value: _getButtonsTitle2
	    });
	    Object.defineProperty(this, _onNextStep, {
	      value: _onNextStep2
	    });
	    Object.defineProperty(this, _tryCompleteStep, {
	      value: _tryCompleteStep2
	    });
	    Object.defineProperty(this, _onPrevStep, {
	      value: _onPrevStep2
	    });
	    Object.defineProperty(this, _createStages, {
	      value: _createStages2
	    });
	    Object.defineProperty(this, _createNavigationButtons, {
	      value: _createNavigationButtons2
	    });
	    Object.defineProperty(this, _metadata, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _order, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepIndex, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stepNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _stages, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _navigationButtons, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _metadata)[_metadata] = metadata;
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _order)[_order] = Object.keys(metadata);
	    babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode] = main_core.Tag.render(_t || (_t = _`<div class="sign-wizard__step"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages] = new Map();
	    babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons] = babelHelpers.classPrivateFieldLooseBase(this, _createNavigationButtons)[_createNavigationButtons]();
	  }
	  getLayout() {
	    babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages] = babelHelpers.classPrivateFieldLooseBase(this, _createStages)[_createStages]();
	    const content = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="sign-wizard__content">
				<div class="sign-wizard__stages">
					${0}
				</div>
				${0}
			</div>
		`), [...babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].values()], babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	    const footer = main_core.Tag.render(_t3 || (_t3 = _`
			<div class="sign-wizard__footer">
				${0}
			</div>
		`), Object.values(babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons]));
	    return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="sign-wizard__scope sign-wizard">
				${0}
				${0}
			</div>
		`), content, footer);
	  }
	  moveOnStep(step) {
	    babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] = step;
	    babelHelpers.classPrivateFieldLooseBase(this, _renderActiveStage)[_renderActiveStage]();
	    babelHelpers.classPrivateFieldLooseBase(this, _renderNavigationButtons)[_renderNavigationButtons]();
	    babelHelpers.classPrivateFieldLooseBase(this, _renderStep)[_renderStep]();
	  }
	  toggleBtnLoadingState(buttonId, loading) {
	    const button = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons][buttonId];
	    if (loading) {
	      main_core.Dom.addClass(button, 'ui-btn-wait');
	    } else {
	      main_core.Dom.removeClass(button, 'ui-btn-wait');
	    }
	  }
	  toggleBtnActiveState(buttonId, shouldDisable) {
	    const button = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons][buttonId];
	    if (shouldDisable) {
	      main_core.Dom.addClass(button, 'ui-btn-disabled');
	    } else {
	      main_core.Dom.removeClass(button, 'ui-btn-disabled');
	    }
	  }
	}
	function _createNavigationButtons2() {
	  var _babelHelpers$classPr, _back$className, _next$className;
	  const classList = ['ui-btn', 'ui-btn-lg', 'ui-btn-round', 'sign-wizard__footer_button'];
	  const {
	    back = {},
	    next = {},
	    complete = {},
	    swapButtons = false
	  } = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options]) != null ? _babelHelpers$classPr : {};
	  const {
	    title: completeTitle,
	    onComplete,
	    className: completeClassName
	  } = complete;
	  const backClassList = ((_back$className = back.className) != null ? _back$className : '').split(' ');
	  const nextClassList = ((_next$className = next.className) != null ? _next$className : '').split(' ');
	  const completeClassList = (completeClassName != null ? completeClassName : '').split(' ');
	  const backButton = {
	    id: 'back',
	    title: main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_BACK'),
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _onPrevStep)[_onPrevStep](),
	    buttonClassList: [...classList, ...backClassList]
	  };
	  const buttons = [{
	    id: 'next',
	    title: main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_NEXT'),
	    method: () => babelHelpers.classPrivateFieldLooseBase(this, _onNextStep)[_onNextStep](),
	    buttonClassList: [...classList, ...nextClassList]
	  }, {
	    id: 'complete',
	    title: completeTitle != null ? completeTitle : main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_COMPLETE'),
	    method: async () => {
	      const completed = await babelHelpers.classPrivateFieldLooseBase(this, _tryCompleteStep)[_tryCompleteStep]('complete');
	      if (completed && onComplete) {
	        onComplete();
	      }
	    },
	    buttonClassList: [...classList, ...completeClassList]
	  }];
	  if (swapButtons) {
	    buttons.push(backButton);
	  } else {
	    buttons.unshift(backButton);
	  }
	  return buttons.reduce((acc, button) => {
	    const {
	      title,
	      method,
	      buttonClassList = classList,
	      id
	    } = button;
	    const node = main_core.Tag.render(_t5 || (_t5 = _`
				<button
					class="${0}"
					title="${0}"
					onclick="${0}"
				>
					${0}
				</button>
			`), buttonClassList.join(' '), title, method, title);
	    acc[id] = node;
	    return acc;
	  }, {});
	}
	function _createStages2() {
	  const entries = Object.entries(babelHelpers.classPrivateFieldLooseBase(this, _metadata)[_metadata]);
	  const stages = new Map();
	  entries.forEach(([stepName, step]) => {
	    const stage = main_core.Tag.render(_t6 || (_t6 = _`
				<span class="sign-wizard__stages_item">
					${0}
				</span>
			`), step.title);
	    stages.set(stepName, stage);
	  });
	  return stages;
	}
	function _onPrevStep2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] -= 1;
	  this.moveOnStep(babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]);
	}
	async function _tryCompleteStep2(buttonId = 'next') {
	  var _babelHelpers$classPr2, _await$beforeCompleti;
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const {
	    beforeCompletion
	  } = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _metadata)[_metadata][stepName]) != null ? _babelHelpers$classPr2 : {};
	  this.toggleBtnLoadingState(buttonId, true);
	  const shouldComplete = (_await$beforeCompleti = await (beforeCompletion == null ? void 0 : beforeCompletion())) != null ? _await$beforeCompleti : true;
	  this.toggleBtnLoadingState(buttonId, false);
	  return shouldComplete;
	}
	async function _onNextStep2() {
	  const completed = await babelHelpers.classPrivateFieldLooseBase(this, _tryCompleteStep)[_tryCompleteStep]();
	  if (completed) {
	    babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] += 1;
	    this.moveOnStep(babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]);
	  }
	}
	function _getButtonsTitle2() {
	  var _babelHelpers$classPr3, _back$titles$stepName, _back$titles, _next$titles$stepName, _next$titles;
	  const {
	    back = {},
	    next = {}
	  } = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _options)[_options]) != null ? _babelHelpers$classPr3 : {};
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const backTitle = (_back$titles$stepName = (_back$titles = back.titles) == null ? void 0 : _back$titles[stepName]) != null ? _back$titles$stepName : main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_BACK');
	  const nextTitle = (_next$titles$stepName = (_next$titles = next.titles) == null ? void 0 : _next$titles[stepName]) != null ? _next$titles$stepName : main_core.Loc.getMessage('SIGN_WIZARD_FOOTER_BUTTON_NEXT');
	  return {
	    backTitle,
	    nextTitle
	  };
	}
	function _renderNavigationButtons2() {
	  const {
	    back: backButton,
	    next: nextButton,
	    complete: completeButton
	  } = babelHelpers.classPrivateFieldLooseBase(this, _navigationButtons)[_navigationButtons];
	  const isFirstStep = babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] === 0;
	  const isLastStep = babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex] + 1 === babelHelpers.classPrivateFieldLooseBase(this, _order)[_order].length;
	  main_core.Dom.removeClass(backButton, '--hide');
	  main_core.Dom.removeClass(nextButton, '--hide');
	  main_core.Dom.addClass(completeButton, '--hide');
	  const {
	    nextTitle,
	    backTitle
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getButtonsTitle)[_getButtonsTitle](backButton, nextButton);
	  backButton.textContent = backTitle;
	  nextButton.textContent = nextTitle;
	  if (isFirstStep) {
	    main_core.Dom.addClass(backButton, '--hide');
	  }
	  if (isLastStep) {
	    main_core.Dom.addClass(nextButton, '--hide');
	    main_core.Dom.removeClass(completeButton, '--hide');
	  }
	}
	function _renderActiveStage2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].forEach(stageNode => {
	    main_core.Dom.removeClass(stageNode, '--active');
	  });
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const stageNode = babelHelpers.classPrivateFieldLooseBase(this, _stages)[_stages].get(stepName);
	  main_core.Dom.addClass(stageNode, '--active');
	}
	function _renderStep2() {
	  var _babelHelpers$classPr4;
	  const stepName = babelHelpers.classPrivateFieldLooseBase(this, _order)[_order][babelHelpers.classPrivateFieldLooseBase(this, _stepIndex)[_stepIndex]];
	  const {
	    content
	  } = (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _metadata)[_metadata][stepName]) != null ? _babelHelpers$classPr4 : {};
	  if (!content) {
	    return;
	  }
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	  if (main_core.Type.isArrayFilled(content)) {
	    content.forEach(node => main_core.Dom.append(node, babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]));
	  } else {
	    main_core.Dom.append(content, babelHelpers.classPrivateFieldLooseBase(this, _stepNode)[_stepNode]);
	  }
	}

	exports.Wizard = Wizard;

}((this.BX.Ui = this.BX.Ui || {}),BX));
//# sourceMappingURL=wizard.bundle.js.map
