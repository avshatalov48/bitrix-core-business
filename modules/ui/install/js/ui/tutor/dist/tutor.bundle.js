/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,ui_tour,main_loader) {
	'use strict';

	class Step extends main_core.Event.EventEmitter {
	  constructor(options) {
	    super();
	    this.setEventNamespace('BX.UI.Tutor.Step');
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.id = options.id || null;
	    this.title = options.title || null;
	    this.description = options.description || null;
	    this.url = options.url || '';
	    this.isCompleted = options.isCompleted || false;
	    this.video = options.video || null;
	    this.helpLink = options.helpLink || null;
	    this.highlight = options.highlight || null;
	    this.isActive = options.isActive === true;
	    this.isShownForSlider = options.isShownForSlider || false;
	    this.initOptions = options;
	    this.videoObj = null;
	  }

	  /**
	   * @public
	   * @returns {string}
	   */
	  getTitle() {
	    return this.title;
	  }

	  /**
	   * @public
	   * @returns {Object}
	   */
	  getVideoObj() {
	    return this.videoObj;
	  }

	  /**
	   * @public
	   */
	  getHighlightOptions() {
	    return this.highlight;
	  }

	  /**
	   * @public
	   * @returns {string}
	   */
	  getDescription() {
	    return this.description;
	  }

	  /**
	   * @public
	   * @returns {string}
	   */
	  getUrl() {
	    return this.url;
	  }

	  /**
	   * @public
	   * @returns {Boolean}
	   */
	  getCompleted() {
	    return this.isCompleted;
	  }
	  getVideo() {
	    return this.video;
	  }
	  getHelpLink() {
	    return this.helpLink;
	  }

	  /**
	   * @public
	   * @returns {string}
	   */
	  getId() {
	    return this.id;
	  }

	  /**
	   * @public
	   * @returns {Object}
	   */
	  getInitOptions() {
	    return this.initOptions;
	  }

	  /**
	   * @public
	   */
	  activate() {
	    this.isActive = true;
	  }

	  /**
	   * @public
	   */
	  getShownForSlider() {
	    return this.isShownForSlider;
	  }

	  /**
	   * @public
	   */
	  deactivate() {
	    this.isActive = false;
	  }

	  /**
	   * @private
	   */
	  static getFullEventName(shortName) {
	    return shortName;
	  }
	}

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
	  _t10,
	  _t11;
	class Manager extends main_core.Event.EventEmitter {
	  constructor() {
	    super();
	    this.setEventNamespace('BX.UI.Tutor.Manager');
	  }
	  setOptions(options, domain, feedbackFormId) {
	    options = options || {};
	    this.tutorialData = options.tutorialData || {};
	    this.eventService = options.eventService || {};
	    this.lastCheckTime = options.lastCheckTime || 0;
	    this.domain = options.domain || '';
	    this.feedbackFormId = options.feedbackFormId || '';
	    if (main_core.Type.isString(domain) && domain.length > 0) {
	      this.domain = domain;
	    }
	    if (main_core.Type.isString(feedbackFormId) && feedbackFormId.length > 0) {
	      this.feedbackFormId = feedbackFormId;
	    }
	  }
	  getDomain() {
	    return this.domain;
	  }
	  getCurrentTutorialData() {
	    return this.tutorialData;
	  }
	  getCurrentEventService() {
	    return this.eventService;
	  }
	  getCurrentLastCheckTime() {
	    return this.lastCheckTime;
	  }

	  /**
	   * @return {Manager}
	   */
	  static getInstance() {
	    return this.instance;
	  }

	  /**
	   * @return {Scenario}
	   */
	  static getScenarioInstance() {
	    return this.scenarioInstance;
	  }
	  static init(options, domain, feedbackFormId) {
	    let instance = this.getInstance();
	    if (!(instance instanceof Manager)) {
	      this.instance = new Manager();
	      instance = this.getInstance();
	      this.emit('onInitManager');
	    } else {
	      instance = this.getInstance();
	    }
	    instance.setOptions(options, domain, feedbackFormId);
	    return instance;
	  }
	  static initScenario(options) {
	    let instance = this.getScenarioInstance();
	    if (!(instance instanceof Scenario)) {
	      this.scenarioInstance = new Scenario();
	      instance = this.getScenarioInstance();
	      this.emit('onInitScenario');
	    } else {
	      instance = this.getScenarioInstance();
	    }
	    instance.setOptions(options);
	    return instance;
	  }
	  static showButton(animation) {
	    return this.getImButton(animation);
	  }
	  static getRootImButton() {
	    return document.getElementById('ui-tutor-btn-wrap');
	  }
	  static hasImButton() {
	    return !!this.getRootImButton();
	  }
	  static getImButton(animation) {
	    if (!this.layout.imButton) {
	      let buttonWrapper = this.getRootImButton();
	      if (buttonWrapper) {
	        let buttonInner = main_core.Tag.render(_t || (_t = _`
					<div class="ui-tutor-btn"></div>
				`));
	        if (animation) {
	          main_core.Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-animate');
	        }
	        main_core.Dom.append(buttonInner, buttonWrapper);
	        main_core.Dom.addClass(buttonWrapper, 'ui-tutor-btn-wrap-show');
	        this.layout.imButton = buttonWrapper;
	        main_core.Event.bind(this.layout.imButton, "click", () => {
	          this.emit('clickImButton');
	        });
	        let usersPanel = document.querySelector('.bx-im-users-wrap');
	        if (document.querySelector('#bx-im-btn-call')) {
	          usersPanel.style.bottom = '175px';
	        } else {
	          usersPanel.style.bottom = '120px';
	        }
	      }
	    }
	    return this.layout.imButton;
	  }
	  static showSmallPopup(text) {
	    this.smallPopupText = text;
	    this.getSmallPopup().style.display = 'block';
	    this.smallPopupText = '';
	    if (main_core.Dom.hasClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide')) {
	      main_core.Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');
	    }
	  }
	  static hideSmallPopup(skipAnimation) {
	    skipAnimation = skipAnimation === true;
	    const removeHandler = function () {
	      main_core.Dom.remove(this.getSmallPopup());
	      if (this.hasOwnProperty('smallPopup')) {
	        delete this.smallPopup;
	      }
	      this.emit('onCompleteHideSmallPopup');
	    }.bind(this);
	    main_core.Dom.removeClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-show');
	    main_core.Dom.addClass(this.getSmallPopup(), 'ui-tutor-popup-welcome-hide');
	    if (skipAnimation) {
	      removeHandler();
	    } else {
	      setTimeout(removeHandler, 300);
	    }
	  }
	  static showWelcomePopup(text) {
	    this.emit('onShowWelcomePopup');
	    this.showSmallPopup(text);
	  }
	  static hideWelcomePopup() {
	    this.emit('onBeforeHideWelcomePopup');
	    this.hideSmallPopup();
	    this.emit('onAfterHideWelcomePopup');
	  }
	  static showNoticePopup(text) {
	    this.emit('onShowNoticePopup');
	    this.showSmallPopup(text);
	  }
	  static hideNoticePopup() {
	    this.emit('onBeforeHideNoticePopup');
	    this.hideSmallPopup();
	    this.emit('onAfterHideNoticePopup');
	  }
	  static getSmallPopup() {
	    const clickSmallPopupHandler = () => {
	      this.emit('onClickSmallPopupBtn');
	    };
	    if (!this.smallPopup) {
	      this.smallPopup = main_core.Tag.render(_t2 || (_t2 = _`
					<div class="ui-tutor-popup" onclick="${0}">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title-wrap">
								<span class="ui-tutor-popup-header-title">${0}</span> 
							</span>
						</div>
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-text">${0}</div>
						</div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`), clickSmallPopupHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.smallPopupText);
	      this.emit('onCreateSmallPopupNode');
	      main_core.Dom.addClass(this.smallPopup, 'ui-tutor-popup-welcome-show');
	      this.emit('onBeforeAppendSmallPopupNode');
	      main_core.Dom.append(this.smallPopup, document.body);
	      this.emit('onAfterAppendSmallPopupNode');
	    }
	    return this.smallPopup;
	  }
	  static showStartPopup(title, text) {
	    this.emit('onShowStartPopup');
	    this.startTitle = title;
	    this.startText = text;
	    main_core.Dom.addClass(this.getStartPopup(), 'ui-tutor-popup-show');
	    this.startPopup.style.display = 'flex';
	    this.startTitle = '';
	    this.startText = '';
	  }
	  static closeStartPopup() {
	    main_core.Dom.remove(this.getStartPopup());
	    delete this.startPopup;
	  }
	  static getStartPopup() {
	    if (!this.startPopup) {
	      this.startPopup = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="ui-tutor-popup ui-tutor-popup-start">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title-wrap">
								<span class="ui-tutor-popup-header-title">${0}</span>
							</span>
						</div>
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-title">${0}</div>
							<div class="ui-tutor-popup-text">${0}</div>
						</div>
						<div class="ui-tutor-popup-footer">
							<div class="ui-tutor-popup-btn">
								${0}
								${0}
							</div>
						</div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.startTitle, this.startText, this.getBeginBtn(), this.getDeferBtn());
	      this.emit('onCreateStartPopupNode');
	      main_core.Dom.append(this.startPopup, document.body);
	      this.emit('onAfterAppendStartPopupNode');
	    }
	    return this.startPopup;
	  }
	  static getBeginBtn() {
	    if (!this.beginBtn) {
	      this.beginBtn = main_core.Tag.render(_t4 || (_t4 = _`
					<button class="ui-btn ui-btn-primary ui-btn-round">
						${0}
					</button>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_BEGIN'));
	      main_core.Event.bind(this.beginBtn, "click", () => {
	        this.emit('clickBeginBtn');
	      });
	    }
	    return this.beginBtn;
	  }
	  static getDeferBtn() {
	    if (!this.deferBtn) {
	      this.deferBtn = main_core.Tag.render(_t5 || (_t5 = _`
					<button class="ui-btn ui-btn-link">
						${0}
					</button>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_CLOSE_POPUP_BTN'));
	      main_core.Event.bind(this.deferBtn, "click", () => {
	        this.emit('clickDeferBtn');
	      });
	    }
	    return this.deferBtn;
	  }

	  /**
	   * @private
	   */
	  static getFullEventName(shortName) {
	    return shortName;
	  }

	  /**
	   * @public
	   */
	  static getInformer() {
	    if (!this.informer) {
	      this.informer = main_core.Tag.render(_t6 || (_t6 = _`
					<div class="ui-tutor-informer" id="ui-tutor-informer"></div>
				`));
	      let informerParentNode = this.getImButton();
	      if (this.isCollapsedShow) {
	        informerParentNode = this.getCollapseBlock();
	      }
	      if (informerParentNode) {
	        main_core.Dom.append(this.informer, informerParentNode);
	      }
	    }
	    return this.informer;
	  }
	  static setCount(num) {
	    this.emit('onBeforeSetCount');
	    if (num < 1) {
	      this.removeInformer();
	      delete this.informer;
	      this.isInformerShow = false;
	    } else {
	      this.getInformer().textContent = num;
	      this.isInformerShow = true;
	    }
	    this.emit('onAfterSetCount');
	  }

	  /**
	   * @private
	   */
	  static removeInformer() {
	    if (this.isInformerShow) {
	      main_core.Dom.remove(this.getInformer());
	    }
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static showCollapsedBlock(step, withGuide, showAfterAnimation) {
	    withGuide = withGuide !== false;
	    showAfterAnimation = showAfterAnimation !== false;
	    this.emit('onBeforeShowCollapsedBlock');
	    if (!this.isCollapsedShow) {
	      this.emit('onStartShowCollapsedBlock');
	      if (!(step instanceof Step)) {
	        step = new Step(step);
	      }
	      this.collapsedStep = step;
	      let collapsedBlock = this.getCollapseBlock();
	      let showFunction = function () {
	        collapsedBlock.style.display = 'flex';
	      };
	      if (showAfterAnimation) {
	        setTimeout(showFunction.bind(this), 300);
	      } else {
	        showFunction.call(this);
	      }
	      this.getCollapseTitle().innerHTML = step.getTitle();
	      if (this.isInformerShow) {
	        main_core.Dom.append(this.getInformer(), collapsedBlock);
	      }
	      this.isCollapsedShow = true;
	      this.emit('onShowCollapsedBlock');
	    }
	    if (withGuide) {
	      this.showGuide();
	    } else {
	      this.checkButtonsState();
	    }
	  }
	  static setCollapsedInvisible() {
	    this.hideNode(this.getCollapseBlock());
	  }
	  static setCollapsedVisible() {
	    this.showNode(this.getCollapseBlock());
	  }
	  static checkButtonsState() {
	    this.emit('onCheckButtonsState');
	    let step = this.collapsedStep;
	    if (!step) {
	      return;
	    }
	    if (step.getCompleted()) {
	      if (this.activeGuide) {
	        this.hideNode(this.getRepeatBtn());
	      } else {
	        this.showNode(this.getRepeatBtn());
	      }
	      this.hideNode(this.getCompletedBtn());
	      this.hideNode(this.getStartBtn());
	    } else if (step.isActive) {
	      this.showNode(this.getCompletedBtn());
	      if (this.activeGuide || !this.isShowRepeatWithCompleted) {
	        this.hideNode(this.getRepeatBtn());
	      } else {
	        this.showNode(this.getRepeatBtn());
	      }
	      this.hideNode(this.getStartBtn());
	    } else {
	      this.showNode(this.getStartBtn());
	      this.hideNode(this.getRepeatBtn());
	      this.hideNode(this.getCompletedBtn());
	    }
	  }
	  static showGuide() {
	    this.emit('onBeforeShowGuide');
	    let step = this.collapsedStep;
	    if (!this.activeGuide && step) {
	      this.emit('onStartShowGuide');
	      this.activeGuide = new ui_tour.Guide({
	        simpleMode: true,
	        steps: [step.getHighlightOptions()]
	      });
	      this.activeGuide.subscribe(ui_tour.Guide.getFullEventName("onFinish"), this.finishGuide.bind(this));
	      this.activeGuide.start();
	      main_core.Dom.remove(this.activeGuide.getPopup().closeIcon);
	      this.emit('showCollapseWithGuide');
	      this.checkButtonsState();
	    }
	  }
	  static closeGuide() {
	    if (this.activeGuide instanceof ui_tour.Guide) {
	      this.activeGuide.close();
	      this.emit('onAfterGuide');
	    }
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static getCollapseBlock() {
	    if (!this.layout.collapseBlock) {
	      this.layout.collapseBlock = main_core.Tag.render(_t7 || (_t7 = _`
					<div class="ui-tutor-popup ui-tutor-popup-collapse" onclick="${0}">
						<div class="ui-tutor-popup-content">
							<div class="ui-tutor-popup-step-subject">${0}</div>
							${0}
							<div class="ui-tutor-popup-collapse-btn">
								${0}
								${0}
								${0}
							</div>
						</div>
					</div>
				`), this.clickCollapseBlockHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_TITLE'), this.getCollapseTitle(), this.getStartBtn(), this.getRepeatBtn(), this.getCompletedBtn());
	      this.emit('onCreateCollapsedBlockNode');
	      main_core.Dom.append(this.layout.collapseBlock, document.body);
	      this.emit('onAfterAppendCollapsedBlockNode');
	    }
	    return this.layout.collapseBlock;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static getStartBtn() {
	    if (!this.startBtn) {
	      this.startBtn = main_core.Tag.render(_t8 || (_t8 = _`
					<button class="ui-btn ui-btn-primary ui-btn-round ui-btn-xs">
						${0}
					</button>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_START'));
	      main_core.Event.bind(this.startBtn, "click", event => {
	        event.stopPropagation();
	        this.emit('clickStartBtn');
	      });
	    }
	    return this.startBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static getRepeatBtn() {
	    if (!this.repeatBtn) {
	      this.repeatBtn = main_core.Tag.render(_t9 || (_t9 = _`
					<button class="ui-btn ui-btn-primary ui-btn-round ui-btn-xs">
						${0}
					</button>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT'));
	      main_core.Event.bind(this.repeatBtn, "click", event => {
	        event.stopPropagation();
	        this.emit('clickRepeatBtn');
	      });
	    }
	    return this.repeatBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static getCompletedBtn() {
	    if (!this.completedBtn) {
	      this.completedBtn = main_core.Tag.render(_t10 || (_t10 = _`
					<button class="ui-btn ui-btn-success ui-btn-round ui-btn-xs">
						${0}
					</button>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED_SHORT'));
	      main_core.Event.bind(this.completedBtn, "click", event => {
	        event.stopPropagation();
	        this.emit('clickCompletedBtn');
	      });
	    }
	    return this.completedBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static getCollapseTitle() {
	    if (!this.layout.collapseTitle) {
	      this.layout.collapseTitle = main_core.Tag.render(_t11 || (_t11 = _`
					<div class="ui-tutor-popup-step-title"></div>
				`));
	    }
	    return this.layout.collapseTitle;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  static closeCollapsePopup(event) {
	    this.closeCollapseEntity();
	    this.emit('clickCloseCollapseBlock');
	  }

	  /**
	   * @private
	   */
	  static clickCollapseBlockHandler() {
	    this.emit('clickCollapseBlock');
	  }
	  static finishGuide() {
	    delete this.activeGuide;
	    this.checkButtonsState();
	    this.emit('completeCloseGuide');
	  }
	  static closeCollapseEntity() {
	    this.emit('onBeforeHideCollapsedBlock');
	    this.getCollapseBlock().style.display = 'none';
	    this.getImButton().style.display = 'block';
	    if (this.activeGuide instanceof ui_tour.Guide) {
	      this.activeGuide.close();
	    }
	    if (this.isInformerShow) {
	      main_core.Dom.append(this.getInformer(), this.getImButton());
	    }
	    delete this.collapsedStep;
	    this.isCollapsedShow = false;
	    this.emit('onHideCollapsedBlock');
	  }
	  static showLoader() {
	    this.emit('onBeforeShowLoader');
	    this.startTitle = '';
	    this.startText = '';
	    this.layout.loader = new main_loader.Loader({
	      target: this.getStartPopup(),
	      size: 85
	    });
	    this.layout.loader.show();
	    this.getStartPopup().style.display = 'flex';
	    main_core.Dom.addClass(this.getStartPopup(), "ui-tutor-popup-load");
	    this.emit('onAfterShowLoader');
	  }
	  static hideLoader() {
	    if (this.layout.loader) {
	      this.layout.loader.destroy();
	      this.getStartPopup().style.display = 'none';
	    }
	  }
	  static showCollapsedLoader() {
	    this.emit('onBeforeShowCollapsedLoader');
	    this.layout.collapseLoader = new main_loader.Loader({
	      target: this.getCollapseBlock(),
	      size: 34
	    });
	    this.layout.collapseLoader.show();
	    this.getCollapseBlock().style.display = 'flex';
	    main_core.Dom.addClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
	    this.emit('onAfterShowCollapsedLoader');
	  }
	  static hideCollapsedLoader() {
	    this.emit('onBeforeHideCollapsedLoader');
	    if (this.layout.collapseLoader) {
	      this.layout.collapseLoader.destroy();
	      main_core.Dom.removeClass(this.getCollapseBlock(), "ui-tutor-popup-collapse-load");
	      this.getCollapseBlock().style.display = 'none';
	    }
	    this.emit('onAfterHideCollapsedLoader');
	  }
	  static showNode(node) {
	    node.style.display = 'block';
	  }
	  static hideNode(node) {
	    node.style.display = 'none';
	  }
	  static checkFollowLink(step, scenario) {
	    this.emit('onStartCheckFollowLink');
	    step = step || this.collapsedStep;
	    if (step instanceof Step) {
	      scenario = scenario || {};
	      if (!(window.location.pathname === step.getUrl())) {
	        let beforeEvent = 'onBeforeRedirectToActionPage';
	        if (scenario instanceof Scenario) {
	          main_core.Dom.addClass(scenario.getStartBtn(), 'ui-btn-wait');
	          scenario.fireCurrentStepEvent(beforeEvent);
	        } else {
	          main_core.Dom.addClass(this.getStartBtn(), 'ui-btn-wait');
	          this.emit(beforeEvent, {
	            step
	          });
	        }
	        window.location = step.getUrl();
	      } else {
	        if (scenario instanceof Scenario) {
	          scenario.showCollapseBlock(step);
	        } else {
	          step.activate();
	          this.showCollapsedBlock(step);
	        }
	      }
	    }
	    this.emit('onFinishCheckFollowLink');
	  }
	  static fireEvent(eventName) {
	    this.emit(eventName);
	  }
	}

	/**
	 * @private
	 */
	Manager.instance = null;
	Manager.scenarioInstance = null;
	Manager.activeGuide = null;
	Manager.isShowRepeatWithCompleted = true;
	Manager.layout = {
	  imButton: null,
	  collapseBlock: null,
	  collapseTitle: null
	};

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11$1,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20,
	  _t21,
	  _t22,
	  _t23,
	  _t24,
	  _t25,
	  _t26,
	  _t27,
	  _t28,
	  _t29;
	class Scenario extends main_core.Event.EventEmitter {
	  constructor(options = {}) {
	    super();
	    this.setEventNamespace('BX.UI.Tutor.Scenario');
	    this.stepPopup = null;
	    this.arrowTimer = null;
	    this.guide = null;
	    this.loader = null;
	    this.arrowWrap = null;
	    this.prevArrow = null;
	    this.nextArrow = null;
	    this.currentStepIndex = 0;
	    this.currentStep = null;
	    this.isAddedSteps = false;
	    this.hasArrows = false;
	    this.isLoading = true;
	    this.setOptions(options);
	    this.btn = document.getElementById('ui-tutor-btn-wrap');
	    this.informer = document.getElementById('ui-tutor-informer');
	    this.layout = {
	      stepBlock: null,
	      progress: null,
	      counter: null,
	      counterContainer: null,
	      title: null,
	      description: null,
	      collapseBlock: null,
	      collapseTitle: null,
	      collapseDescription: null,
	      content: null,
	      contentInner: null,
	      contentBlock: null,
	      url: null,
	      target: null,
	      startBtn: null,
	      nextBtn: null,
	      repeatBtn: null,
	      deferBtn: null,
	      help: null,
	      completedBtn: null,
	      completedBlock: null,
	      finishedBlock: null,
	      supportLink: null
	    };
	    this.sections = ['settings', 'scenario', 'work'];
	    this.loadYoutubeApiScript();
	    this.subscribe("onYouTubeReady", () => {
	      this.setVideoItems();
	    });
	  }
	  loadYoutubeApiScript() {
	    const onYouTubeReadyEvent = function () {
	      this.emit("onYouTubeReady", {
	        scenario: this
	      });
	    }.bind(this);
	    if (!window.YT) {
	      let isNeedCheckYT = true;
	      const tag = document.createElement('script');
	      tag.src = "https://www.youtube.com/iframe_api";
	      const firstScriptTag = document.getElementsByTagName('script')[0];
	      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	      let ytCheckerTimer = setInterval(function () {
	        if (isNeedCheckYT) {
	          if (window.YT && main_core.Type.isFunction(window.YT.Player)) {
	            clearInterval(ytCheckerTimer);
	            onYouTubeReadyEvent();
	          }
	        }
	      }, 200);
	      setTimeout(function () {
	        clearInterval(ytCheckerTimer);
	        isNeedCheckYT = false;
	      }, 2000);
	    } else {
	      setTimeout(function () {
	        onYouTubeReadyEvent();
	      }.bind(this), 100);
	    }
	  }
	  setOptions(options) {
	    this.fireCurrentStepEvent('onBeforeSetOptions', false);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    let currentStep = this.getCurrentStep();

	    /** @var {Step[]} */
	    this.steps = [];
	    if (Array.isArray(options.steps)) {
	      options.steps.forEach(step => {
	        this.steps.push(new Step(step));
	      });
	    }
	    if (currentStep instanceof Step) {
	      let stepInList = this.findStepById(currentStep.getId());
	      if (stepInList) {
	        currentStep = stepInList;
	      }
	    } else if (main_core.Type.isString(options.currentStepId) && options.currentStepId.length > 0) {
	      let stepInList = this.findStepById(options.currentStepId);
	      if (stepInList) {
	        currentStep = stepInList;
	        if (options.currentStepIsActive === true) {
	          currentStep.activate();
	        }
	      }
	    }
	    if (!currentStep) {
	      let uncompletedStep = this.getFirstUncompletedStep();
	      if (uncompletedStep) {
	        currentStep = uncompletedStep;
	      }
	    }
	    if (!currentStep && this.steps && this.steps[0]) {
	      currentStep = this.steps[0];
	    }
	    this.setCurrentStep(currentStep);
	    if (options) {
	      this.isLoading = false;
	    }
	    this.title = options.title || '';
	    this.supportLink = options.supportLink || '';
	    this.isFinished = options.isFinished || false;
	    this.fireCurrentStepEvent('onAfterSetOptions', false);
	  }

	  /**
	   * @param {Step} step
	   */
	  setCurrentStep(step) {
	    if (step instanceof Step) {
	      this.currentStep = step;
	      let steps = this.steps;
	      if (main_core.Type.isArray(steps)) {
	        this.currentStepIndex = steps.indexOf(step);
	      }
	      this.fireCurrentStepEvent('onStartStep');
	    }
	  }

	  /**
	   * @public
	   */
	  start(complexAnimation) {
	    this.emit("onStart", {
	      scenario: this
	    });
	    if (complexAnimation)
	      // animate transition from collapsed popup to step popup
	      {
	        this.complexAnimation = true;
	      }
	    this.showPopup(this.getStepPopup());
	    this.toggleCompletedState();
	    this.toggleNavBtn();
	    this.setPopupData();
	    if (this.isAddedSteps) {
	      this.hideFinalState();
	    }
	    if (!this.hasArrows) {
	      this.initArrows();
	    }
	    this.complexAnimation = false;
	    this.fireCurrentStepEvent('onShowComplete');
	  }
	  findStepById(stepId) {
	    for (let i = 0; i < this.steps.length; i++) {
	      const step = this.steps[i];
	      if (step.getId() === stepId) {
	        return step;
	      }
	    }
	    return null;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getStepPopup() {
	    const clickOnCloseIcon = () => {
	      this.emit("onClickOnCloseIcon", {
	        scenario: this
	      });
	    };
	    if (!this.stepPopup) {
	      this.stepPopup = main_core.Tag.render(_t$1 || (_t$1 = _$1`
					<div class="ui-tutor-popup ui-tutor-popup-step">
						<div class="ui-tutor-popup-header">
							<span class="ui-tutor-popup-header-icon"></span>
							<span class="ui-tutor-popup-header-title">
								<span class="ui-tutor-popup-header-counter">
									${0}.
									${0}
								</span>
								<span class="ui-tutor-popup-header-subtitle">${0}</span>
							</span>
							${0}
						</div>
						<div class="ui-tutor-popup-content">
							${0}
						</div>
						<div class="ui-tutor-popup-step-wrap">
							<div class="ui-tutor-popup-step-inner">
								<div class="ui-tutor-popup-arrow-wrap"></div>
								<div class="ui-tutor-popup-step-list-wrap">
									${0}
								</div>
							</div>
						</div>
						<div class="ui-tutor-popup-icon-close" onclick="${0}"></div>
						<div class="ui-tutor-popup-icon-angle"></div>
					</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_TITLE'), this.getCounterContainer(), this.title, this.getDeferLink(), this.getContentBlock(), this.getStepBlock(), clickOnCloseIcon.bind(this));
	      this.fireCurrentStepEvent('onCreateStepPopupNode');
	      main_core.Dom.append(this.stepPopup, document.body);
	      this.fireCurrentStepEvent('onAfterAppendStepPopupNode');
	    }
	    return this.stepPopup;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getContentBlock() {
	    if (!this.layout.contentBlock) {
	      this.layout.contentBlock = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
					<div class="ui-tutor-popup-content-block">
						${0}
						${0}
					</div>
				`), this.getContentInner(), this.getFooter());
	    }
	    return this.layout.contentBlock;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getFooter() {
	    if (!this.layout.footer) {
	      this.layout.footer = main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
					<div class="ui-tutor-popup-footer">
						${0}
						${0}
					</div>
				`), this.getNavigation(), this.getBtnContainer());
	      if (Manager.getInstance() && Manager.getInstance().feedbackFormId) {
	        main_core.Dom.append(this.getSupportLink(), this.layout.footer);
	      }
	    }
	    return this.layout.footer;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getContentInner() {
	    if (!this.layout.contentInner) {
	      this.layout.contentInner = main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
					<div class="ui-tutor-popup-content-inner">
						${0}
						${0}
						${0}
					</div>
				`), this.getTitle(), this.getDescription(), this.getHelpBlock());
	    }
	    return this.layout.contentInner;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getBtnContainer() {
	    if (!this.layout.btnContainer) {
	      this.layout.btnContainer = main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
					<div class="ui-tutor-popup-btn">
						${0}
						${0}
						${0}
					</div>
				`), this.getStartBtn(), this.getRepeatBtn(), this.getCompletedBtn());
	    }
	    return this.layout.btnContainer;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getNavigation() {
	    if (!this.layout.navigation) {
	      this.layout.navigation = main_core.Tag.render(_t6$1 || (_t6$1 = _$1`
					<div class="ui-tutor-popup-nav"></div>
				`));
	      this.layout.backBtn = main_core.Tag.render(_t7$1 || (_t7$1 = _$1`
					<span class="ui-tutor-popup-nav-item ui-tutor-popup-nav-item-prev" onclick="${0}"></span>
				`), this.clickOnBackBtn.bind(this));
	      this.layout.nextBtn = main_core.Tag.render(_t8$1 || (_t8$1 = _$1`
					<span class="ui-tutor-popup-nav-item ui-tutor-popup-nav-item-next" onclick="${0}"></span>
				`), this.clickOnNextBtn.bind(this));
	      main_core.Dom.append(this.layout.backBtn, this.layout.navigation);
	      main_core.Dom.append(this.layout.nextBtn, this.layout.navigation);
	    }
	    return this.layout.navigation;
	  }

	  /**
	   * @private
	   * @param {HTMLElement} node
	   */
	  setInformer(node) {
	    this.setInformerCount(this.steps.length - this.getCompletedSteps());
	  }

	  /**
	   * @public
	   * @param {Number} num
	   */
	  setInformerExternal(num) {
	    this.setInformerCount(num);
	  }

	  /**
	   * @private
	   */
	  setInformerCount(num) {
	    Manager.setCount(num);
	  }

	  /**
	   * @public
	   * @param {Event} event
	   * @param {Boolean} complexAnimation
	   */
	  closeStepPopup(event, complexAnimation) {
	    if (!this.stepPopup) {
	      return;
	    }
	    if (event) {
	      event.stopPropagation();
	    }
	    this.fireCurrentStepEvent('onCloseStepPopup');
	    if (complexAnimation)
	      // animate transition from collapsed popup to step popup
	      {
	        this.complexAnimation = true;
	      }
	    this.fadeAnimation(this.getStepPopup());
	    setTimeout(function () {
	      this.hideNode(this.getStepPopup());
	    }.bind(this), 310);
	    this.complexAnimation = false;
	  }

	  /**
	   * @public
	   * @returns {number}
	   */
	  getCompletedSteps() {
	    let total = 0;
	    for (let i = 0; i < this.steps.length; i += 1) {
	      if (this.steps[i].isCompleted) {
	        total += 1;
	      }
	    }
	    return total;
	  }

	  /**
	   * @private
	   */
	  setStepCounter() {
	    this.getCounter().textContent = main_core.Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER').replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1).replace('#NUMBER_TOTAL#', this.steps.length);
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getCounterContainer() {
	    if (!this.layout.counterContainer) {
	      this.layout.counterContainer = main_core.Tag.render(_t9$1 || (_t9$1 = _$1`
					<span class="ui-tutor-popup-header-counter-step">
						${0}
					</span>
				`), this.getCounter());
	    }
	    return this.layout.counterContainer;
	  }
	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getCounter() {
	    if (!this.layout.counter) {
	      this.layout.counter = main_core.Tag.render(_t10$1 || (_t10$1 = _$1`
					<span class="ui-tutor-popup-header-counter-number"></span>
				`));
	    }
	    return this.layout.counter;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getDeferLink() {
	    if (!this.layout.deferLink) {
	      this.layout.deferLink = main_core.Tag.render(_t11$1 || (_t11$1 = _$1`
					<span class="ui-tutor-popup-defer-link">
						${0}
					</span>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_DEFER'));
	      const deferMenu = new BX.PopupMenuWindow({
	        angle: true,
	        offsetLeft: 15,
	        className: 'ui-tutor-popup-defer-menu',
	        bindElement: this.layout.deferLink,
	        items: [{
	          text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_HOUR'),
	          onclick: function () {
	            this.emit("onDeferOneHour", {
	              scenario: this
	            });
	            deferMenu.close();
	          }.bind(this)
	        }, {
	          text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_TOMORROW'),
	          onclick: function () {
	            this.emit("onDeferTomorrow", {
	              scenario: this
	            });
	            deferMenu.close();
	          }.bind(this)
	        }, {
	          text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_WEEK'),
	          onclick: function () {
	            this.emit("onDeferWeek", {
	              scenario: this
	            });
	            deferMenu.close();
	          }.bind(this)
	        }, {
	          text: main_core.Loc.getMessage('JS_UI_TUTOR_DEFER_MENU_FOREVER'),
	          onclick: function () {
	            this.emit("onDeferForever", {
	              scenario: this
	            });
	            deferMenu.close();
	          }.bind(this)
	        }]
	      });
	      main_core.Event.bind(this.layout.deferLink, "click", () => {
	        deferMenu.show();
	      });
	    }
	    return this.layout.deferLink;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getStartBtn() {
	    if (!this.layout.startBtn) {
	      this.layout.startBtn = main_core.Tag.render(_t12 || (_t12 = _$1`
					<button class="ui-btn ui-btn-primary ui-btn-round" onclick="${0}">
						${0}
					</button>
				`), this.clickStartHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_START'));
	    }
	    return this.layout.startBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getCompletedBtn() {
	    if (!this.layout.completedBtn) {
	      this.layout.completedBtn = main_core.Tag.render(_t13 || (_t13 = _$1`
					<button class="ui-btn ui-btn-success ui-btn-round" onclick="${0}">
						${0}
					</button>
				`), this.showSuccessState.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_COMPLETED'));
	    }
	    return this.layout.completedBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getRepeatBtn() {
	    if (!this.layout.repeatBtn) {
	      this.layout.repeatBtn = main_core.Tag.render(_t14 || (_t14 = _$1`
					<button class="ui-btn ui-btn-primary ui-btn-round" onclick="${0}">
						${0}
					</button>
				`), this.repeatStep.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_REPEAT'));
	    }
	    return this.layout.repeatBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getDeferBtn() {
	    if (!this.layout.deferBtn) {
	      this.layout.deferBtn = main_core.Tag.render(_t15 || (_t15 = _$1`
					<button class="ui-btn ui-btn-link ui-btn-round" onclick="${0}">
						${0}
					</button>
				`), this.closeStepPopup.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_DEFER'));
	    }
	    return this.layout.deferBtn;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getCompletedBLock() {
	    if (!this.layout.completedBlock) {
	      this.layout.completedBlock = main_core.Tag.render(_t16 || (_t16 = _$1`
					<div class="ui-tutor-popup-completed">
						<div class="ui-tutor-popup-completed-icon"></div>
						<div class="ui-tutor-popup-completed-text">${0}</div>
					</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_COMPLETED'));
	    }
	    return this.layout.completedBlock;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getTitle() {
	    if (!this.layout.title) {
	      this.layout.title = main_core.Tag.render(_t17 || (_t17 = _$1`
					<div class="ui-tutor-popup-step-title"></div>
				`));
	    }
	    return this.layout.title;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getDescription() {
	    if (!this.layout.description) {
	      this.layout.description = main_core.Tag.render(_t18 || (_t18 = _$1`
					<div class="ui-tutor-popup-step-decs"></div>
				`));
	    }
	    return this.layout.description;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getSupportLink() {
	    if (!this.layout.supportLink) {
	      this.layout.supportLink = main_core.Tag.render(_t19 || (_t19 = _$1`
					<a class="ui-tutor-popup-support-link" onclick="${0}">
						${0}
					</a>
				`), this.supportLinkHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_BTN_SUPPORT'));
	    }
	    return this.layout.supportLink;
	  }
	  setInvisible() {
	    this.hideNode(this.getStepPopup());
	  }
	  setVisible() {
	    this.showNode(this.getStepPopup());
	  }
	  supportLinkHandler() {
	    this.emit('supportLinkClick');
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getStepBlock() {
	    if (!this.layout.stepBlock) {
	      this.layout.stepBlock = main_core.Tag.render(_t20 || (_t20 = _$1`
					<div class="ui-tutor-popup-step-list"></div>
				`));
	      this.layout.stepItems = [];
	      for (let i = 0; i < this.steps.length; i += 1) {
	        const currentStepIndex = main_core.Tag.render(_t21 || (_t21 = _$1`
						<span class="ui-tutor-popup-step-item" data-step=${0} onclick="${0}">
							<span class="ui-tutor-popup-step-item-number">${0}</span>
						</span>
					`), i, this.switchStep.bind(this), i + 1);
	        this.layout.stepItems.push(currentStepIndex);
	        main_core.Dom.append(currentStepIndex, this.layout.stepBlock);
	      }
	      this.setStepItems();
	    }
	    return this.layout.stepBlock;
	  }

	  /**
	   * @private
	   */
	  setStepItems() {
	    if (this.layout && this.layout.stepItems) {
	      for (let i = 0; i < this.steps.length; i += 1) {
	        if (this.layout.stepItems[i]) {
	          main_core.Dom.removeClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');
	          if (i === this.currentStepIndex) {
	            main_core.Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-current');
	          }
	          if (this.steps[i].isCompleted) {
	            main_core.Dom.addClass(this.layout.stepItems[i], 'ui-tutor-popup-step-item-completed');
	          }
	        }
	      }
	    }
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getHelpBlock() {
	    if (!this.layout.help) {
	      this.layout.help = main_core.Tag.render(_t22 || (_t22 = _$1`
					<div class="ui-tutor-popup-help">
						${0}
					</div>
				`), this.getHelpLink());
	    }
	    return this.layout.help;
	  }
	  setVideoItems() {
	    for (let i = 0; i < this.steps.length; i += 1) {
	      const currentVideo = main_core.Tag.render(_t23 || (_t23 = _$1`
					<div class="ui-tutor-popup-video" data-step=${0}></div>
				`), i);
	      main_core.Dom.prepend(currentVideo, this.getHelpBlock());
	      if (window.YT && main_core.Type.isObject(window.YT) && main_core.Type.isFunction(window.YT.Player) && this.steps[i].video !== null) {
	        const playerData = {
	          videoId: this.steps[i].video,
	          events: {
	            'onReady': function (event) {
	              event.target.mute();
	              event.target.pauseVideo();
	              event.target.setPlaybackQuality('hd720');
	              if (+event.target.getIframe().getAttribute('data-step') === this.currentStepIndex) {
	                main_core.Dom.addClass(event.target.getIframe(), 'ui-tutor-popup-video-show');
	                event.target.playVideo();
	              }
	            }.bind(this)
	          },
	          playerVars: {
	            cc_load_policy: 1,
	            cc_lang_pref: 'ru',
	            rel: 0
	          }
	        };
	        this.fireCurrentStepEvent('onBeforeCreateVideo', true, {
	          playerData
	        });
	        this.steps[i].videoObj = new YT.Player(currentVideo, playerData);
	        this.fireCurrentStepEvent('onAfterCreateVideo');
	      }
	    }
	  }
	  pauseCurrentVideo() {
	    let step = this.getCurrentStep();
	    if (window.YT && step instanceof Step) {
	      let video = step.getVideoObj();
	      if (main_core.Type.isObject(video) && video.pauseVideo) {
	        video.pauseVideo();
	      }
	    }
	  }
	  playCurrentVideo() {
	    let step = this.getCurrentStep();
	    if (window.YT && step instanceof Step) {
	      let video = step.getVideoObj();
	      if (main_core.Type.isObject(video) && video.playVideo) {
	        video.playVideo();
	      }
	    }
	  }
	  getHelpLink() {
	    if (!this.layout.link) {
	      this.layout.link = main_core.Tag.render(_t24 || (_t24 = _$1`
					<span class="ui-tutor-popup-help-link" onclick="${0}">
						<span class="ui-tutor-popup-help-link-text">${0}</span>
					</span>
				`), this.handleClickLinkHandler.bind(this), main_core.Loc.getMessage('JS_UI_TUTOR_ARTICLE_HELP_TOPIC'));
	    }
	    return this.layout.link;
	  }

	  /**
	   * @private
	   */
	  handleClickLinkHandler() {
	    this.emit('helpLinkClick');
	  }

	  /**
	   * @public
	   * @param {HTMLElement} node
	   */
	  showPopup(node) {
	    this.showAnimation(node);
	    node.style.display = 'block';
	  }

	  /**
	   * @public
	   * @param {HTMLElement} node
	   */
	  showNode(node) {
	    node.style.display = 'block';
	  }

	  /**
	   * @public
	   * @param {HTMLElement} node
	   */
	  hideNode(node) {
	    node.style.display = 'none';
	  }

	  /**
	   * @public
	   * @param {HTMLElement} node
	   */
	  removePopup(node) {
	    main_core.Dom.remove(node);
	  }

	  /**
	   * @private
	   */
	  clickOnNextBtn() {
	    this.fireCurrentStepEvent('onBeforeClickNavNextBtn');
	    if (this.getCompletedSteps() === this.steps.length && !this.isFinished) {
	      this.isAddedSteps = false;
	      main_core.Dom.remove(this.getNewStepsSection());
	      main_core.Dom.removeClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
	      this.showFinalState();
	      return;
	    }
	    if (this.getCompletedSteps() === this.steps.length && this.currentStepIndex + 1 === this.steps.length) {
	      this.currentStepIndex = -1;
	    }
	    main_core.Dom.removeClass(this.getStartBtn(), 'ui-btn-wait');
	    this.increaseCurrentIndex();
	    this.showStep();
	    this.toggleNavBtn();
	    this.fireCurrentStepEvent('onAfterClickNavNextBtn');
	  }
	  clickOnBackBtn() {
	    this.fireCurrentStepEvent('onBeforeClickNavBackBtn');
	    this.reduceCurrentIndex();
	    this.toggleNavBtn();
	    this.showStep();
	    this.fireCurrentStepEvent('onAfterClickNavBackBtn');
	  }
	  toggleNavBtn() {
	    main_core.Dom.removeClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
	    main_core.Dom.removeClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');
	    if (this.currentStepIndex === 0) {
	      main_core.Dom.addClass(this.layout.backBtn, 'ui-tutor-popup-nav-item-disabled');
	    }
	    if (this.currentStepIndex + 1 === this.steps.length) {
	      main_core.Dom.addClass(this.layout.nextBtn, 'ui-tutor-popup-nav-item-disabled');
	    }
	  }
	  showStep() {
	    // when last step is completed, but some steps are not
	    if (this.clickOnCompletedBtn && this.currentStepIndex === this.steps.length) {
	      let nextUncompletedStep = this.getFirstUncompletedStep();
	      if (nextUncompletedStep) {
	        this.setCurrentStep(nextUncompletedStep);
	      }
	    }
	    this.scrollToStep();
	    this.toggleCompletedState();
	    this.setPopupData();
	    this.clickOnCompletedBtn = false;
	    this.fireCurrentStepEvent('onAfterShowStep');
	  }

	  /**
	   * @private
	   */
	  switchStep() {
	    this.fireCurrentStepEvent('onBeforeSwitchStep');
	    this.setCurrentStep(this.steps[+window.event.target.getAttribute('data-step')]);
	    this.fireCurrentStepEvent('onAfterSwitchStep');
	    if (this.layout.finishedBlock) {
	      this.hideFinalState();
	    }
	    this.showStep();
	    this.toggleNavBtn();
	    this.fireCurrentStepEvent('onEndSwitchStep');
	  }

	  /**
	   * @private
	   */
	  getFirstUncompletedStep() {
	    for (let i = 0; i < this.steps.length; i += 1) {
	      if (!this.steps[i].isCompleted) {
	        return this.steps[i];
	      }
	    }
	    return null;
	  }

	  /**
	   * @private
	   */
	  toggleCompletedState() {
	    const currentStep = this.getCurrentStep();
	    if (currentStep) {
	      if (currentStep.getCompleted()) {
	        this.showNode(this.getRepeatBtn());
	        this.hideNode(this.getStartBtn());
	        this.hideNode(this.getCompletedBtn());
	      } else if (currentStep.isActive) {
	        this.showNode(this.getCompletedBtn());
	        this.hideNode(this.getStartBtn());
	        this.showNode(this.getRepeatBtn());
	      } else {
	        this.showNode(this.getStartBtn());
	        this.hideNode(this.getCompletedBtn());
	        this.hideNode(this.getRepeatBtn());
	      }
	    }
	  }

	  /**
	   * @private
	   */
	  setPopupData() {
	    this.fireCurrentStepEvent('onBeforeSetPopupData');
	    const currentStep = this.getCurrentStep();
	    if (currentStep) {
	      this.getTitle().innerHTML = currentStep.getTitle();
	      this.getDescription().innerHTML = currentStep.getDescription();
	      Manager.getCollapseTitle().innerHTML = currentStep.getTitle();
	      if (this.getCurrentStep().getVideo() && window.YT) {
	        this.setCurrentVideo();
	      }
	      this.setStepCounter();
	      this.setStepItems();
	    }
	    this.fireCurrentStepEvent('onAfterSetPopupData');
	  }
	  setCurrentVideo() {
	    this.fireCurrentStepEvent('onSetCurrentVideo');
	    for (let i = 0; i < this.steps.length; i += 1) {
	      let video = this.steps[i].getVideoObj();
	      if (window.YT && i === this.currentStepIndex && video && video.playVideo) {
	        main_core.Dom.addClass(video.getIframe(), 'ui-tutor-popup-video-show');
	        video.playVideo();
	      } else {
	        if (video) {
	          main_core.Dom.removeClass(video.getIframe(), 'ui-tutor-popup-video-show');
	          if (video.pauseVideo) {
	            video.pauseVideo();
	          }
	        }
	      }
	    }
	  }

	  /**
	   * @public
	   * @returns {Step}
	   */
	  getCurrentStep() {
	    return this.currentStep;
	  }

	  /**
	   * @private
	   */
	  increaseCurrentIndex() {
	    if (this.currentStepIndex === this.steps.length) {
	      return;
	    }
	    this.currentStepIndex += 1;
	    this.setCurrentStep(this.steps[this.currentStepIndex]);
	  }

	  /**
	   * @private
	   */
	  reduceCurrentIndex() {
	    if (this.currentStepIndex === 0) {
	      return;
	    }
	    this.currentStepIndex -= 1;
	    this.setCurrentStep(this.steps[this.currentStepIndex]);
	  }

	  /**
	   * @private
	   */
	  showCollapseBlock(step, withGuide) {
	    withGuide = withGuide !== false;
	    this.closeStepPopup(null, true);
	    Manager.showCollapsedBlock(step, withGuide);
	  }

	  /**
	   * @private
	   */
	  minimize() {
	    this.pauseCurrentVideo();
	    this.fireCurrentStepEvent('onMinimize');
	    this.showCollapseBlock(this.getCurrentStep(), false);
	  }
	  repeatStep() {
	    this.followLink();
	  }
	  clickStartHandler() {
	    this.followLink();
	  }
	  /**
	   * @private
	   */
	  followLink(step) {
	    let currentStep = this.getCurrentStep();
	    if (step instanceof Step) {
	      currentStep = step;
	    }
	    this.pauseCurrentVideo();
	    this.setActiveStep(currentStep);
	    Manager.checkFollowLink(currentStep, this);
	  }
	  setActiveStep(step) {
	    this.fireCurrentStepEvent('onBeforeSetActiveStep');
	    if (this.currentActiveStep instanceof Step) {
	      this.currentActiveStep.deactivate();
	    }
	    step.activate();
	    this.currentActiveStep = step;
	    this.fireCurrentStepEvent('onAfterSetActiveStep');
	  }

	  /**
	   * @private
	   */
	  showSuccessState() {
	    const currentStep = this.getCurrentStep();
	    this.clickOnCompletedBtn = true;
	    currentStep.isCompleted = true;
	    this.fireCurrentStepEvent('onFinishStep');
	    if (currentStep.getCompleted()) {
	      main_core.Dom.addClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-completed');
	    }
	    main_core.Dom.addClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');
	    setTimeout(function () {
	      main_core.Dom.replace(this.getHelpBlock(), this.getCompletedBLock());
	      this.getFooter().style.display = "none";
	      this.getDescription().style.display = "none";
	      this.getTitle().style.display = "none";
	    }.bind(this), 300);
	    setTimeout(function () {
	      main_core.Dom.addClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate');
	    }.bind(this), 800);
	    setTimeout(function () {
	      main_core.Dom.replace(this.getCompletedBLock(), this.getHelpBlock());
	      this.getTitle().style.display = "block";
	      this.getDescription().style.display = "block";
	      this.getFooter().style.display = "flex";
	      this.clickOnNextBtn();
	    }.bind(this), 1500);
	    setTimeout(function () {
	      main_core.Dom.removeClass(this.getCompletedBLock(), 'ui-tutor-popup-completed-animate');
	      main_core.Dom.removeClass(this.getContentBlock(), 'ui-tutor-popup-content-block-animate');
	      let counter = this.stepPopup.querySelector(".ui-tutor-popup-header-counter-number");
	      counter.innerHTML = main_core.Loc.getMessage('JS_UI_TUTOR_COUNTER_NUMBER').replace('#NUMBER#', this.steps.indexOf(this.getCurrentStep()) + 1).replace('#NUMBER_TOTAL#', this.steps.length);
	      this.fireCurrentStepEvent('onAfterShowSuccessState');
	    }.bind(this), 1700);
	  }
	  fireCurrentStepEvent(eventName, fireStepEvent, extra) {
	    fireStepEvent = fireStepEvent !== false;
	    const currentStep = this.getCurrentStep();
	    const data = {
	      step: currentStep,
	      scenario: this
	    };
	    if (extra) {
	      data.extra = extra;
	    }
	    if (currentStep && fireStepEvent) {
	      currentStep.emit(eventName, data);
	    }
	    this.emit(eventName, data);
	  }

	  /**
	   * @private
	   */
	  showFinalState() {
	    this.fireCurrentStepEvent('onFinalState');
	    if (this.layout.stepItems) {
	      main_core.Dom.removeClass(this.layout.stepItems[this.currentStepIndex], 'ui-tutor-popup-step-item-current');
	    }
	    main_core.Dom.append(this.getFinishedBlock(), this.getContentInner());
	    main_core.Dom.replace(this.getStartBtn(), this.getFinishedNotice());
	    main_core.Dom.remove(this.getCompletedBtn());
	    main_core.Dom.remove(this.getSupportLink());
	    main_core.Dom.remove(this.getNavigation());
	    main_core.Dom.remove(this.getHelpBlock());
	    main_core.Dom.remove(this.getRepeatBtn());
	    main_core.Dom.remove(this.getTitle());
	    main_core.Dom.remove(this.getDescription());
	    main_core.Dom.remove(this.getDeferLink());
	    this.isFinished = true;
	    this.fireCurrentStepEvent('onAfterFinalState');
	  }

	  /**
	   * @private
	   */
	  hideFinalState() {
	    this.fireCurrentStepEvent('onBeforeHideFinalState');
	    if (this.getCurrentStep().getCompleted()) {
	      main_core.Dom.replace(this.getFinishedNotice(), this.getRepeatBtn());
	    } else {
	      main_core.Dom.replace(this.getFinishedNotice(), this.getStartBtn());
	    }
	    main_core.Dom.replace(this.getFinishedBlock(), this.getHelpBlock());
	    if (Manager.getInstance().feedbackFormId) {
	      main_core.Dom.append(this.getSupportLink(), this.getFooter());
	    }
	    main_core.Dom.prepend(this.getNavigation(), this.getFooter());
	    main_core.Dom.prepend(this.getDescription(), this.getContentInner());
	    main_core.Dom.prepend(this.getTitle(), this.getContentInner());
	    if (this.layout.deferBtn) {
	      main_core.Dom.remove(this.getDeferBtn());
	      main_core.Dom.prepend(this.getStartBtn(), this.getBtnContainer());
	    }
	    const header = this.getStepPopup().querySelector('.ui-tutor-popup-header');
	    main_core.Dom.append(this.getDeferLink(), header);
	    this.fireCurrentStepEvent('onAfterHideFinalState');
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getFinishedBlock() {
	    if (!this.layout.finishedBlock) {
	      this.layout.finishedBlock = main_core.Tag.render(_t25 || (_t25 = _$1`
					<div class="ui-tutor-popup-finished">
						<div class="ui-tutor-popup-finished-title">${0}</div>
						<div class="ui-tutor-popup-finished-icon"></div>
						<div class="ui-tutor-popup-finished-text">${0}</div>
					</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_CONGRATULATIONS'), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_TEXT'));
	    }
	    return this.layout.finishedBlock;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getFinishedNotice() {
	    if (!this.layout.finishedNotice) {
	      this.layout.finishedNotice = main_core.Tag.render(_t26 || (_t26 = _$1`
					<div class="ui-tutor-popup-finished-notice">${0}</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_FINAL_NOTICE'));
	    }
	    return this.layout.finishedNotice;
	  }

	  /**
	   * @public
	   * @returns {HTMLElement}
	   */
	  getNewStepsSection() {
	    if (!this.layout.newStepsSection) {
	      this.layout.newStepsSection = main_core.Tag.render(_t27 || (_t27 = _$1`
					<div class="ui-tutor-popup-new-text">${0}</div>
				`), main_core.Loc.getMessage('JS_UI_TUTOR_STEP_NEW'));
	    }
	    return this.layout.newStepsSection;
	  }

	  /**
	   * @public
	   */
	  showNewSteps() {
	    main_core.Dom.addClass(this.getFinishedBlock(), 'ui-tutor-popup-finished-new');
	    this.showPopup(this.getStepPopup());
	    this.showFinalState();
	    main_core.Dom.append(this.getNewStepsSection(), this.getFinishedBlock());
	    main_core.Dom.replace(this.getFinishedNotice(), Manager.getBeginBtn());
	    main_core.Dom.append(this.getDeferBtn(), this.getBtnContainer());
	    this.setStepCounter();
	    this.initArrows();
	    this.scrollToStep();
	    this.isAddedSteps = true;
	    this.isFinished = false;
	  }

	  /**
	   * @private
	   */
	  initArrows() {
	    this.stepListWrap = document.querySelector('.ui-tutor-popup-step-list-wrap');
	    this.arrowWrap = document.querySelector('.ui-tutor-popup-arrow-wrap');
	    if (this.stepListWrap && this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth) {
	      main_core.Dom.append(this.getPrevArrow(), this.arrowWrap);
	      main_core.Dom.append(this.getNextArrow(), this.arrowWrap);
	      this.stepListWrap.addEventListener('scroll', this.toggleArrows.bind(this));
	      this.prevArrow.addEventListener('mouseenter', this.scrollToLeft.bind(this));
	      this.prevArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));
	      this.nextArrow.addEventListener('mouseenter', this.scrollToRight.bind(this));
	      this.nextArrow.addEventListener('mouseleave', this.stopAutoScroll.bind(this));
	      this.toggleNextArrow();
	      this.getStepBlock().classList.add("ui-tutor-popup-step-list-wide");
	      this.hasArrows = true;
	    }
	  }
	  getPrevArrow() {
	    if (!this.prevArrow) {
	      this.prevArrow = main_core.Tag.render(_t28 || (_t28 = _$1`
					<div class="ui-tutor-popup-arrow ui-tutor-popup-arrow-prev"></div>
				`));
	    }
	    return this.prevArrow;
	  }
	  getNextArrow() {
	    if (!this.nextArrow) {
	      this.nextArrow = main_core.Tag.render(_t29 || (_t29 = _$1`
					<div class="ui-tutor-popup-arrow ui-tutor-popup-arrow-next"></div>
				`));
	    }
	    return this.nextArrow;
	  }

	  /**
	   * @private
	   */
	  scrollToLeft() {
	    this.arrowTimer = setInterval(function () {
	      this.stepListWrap.scrollLeft -= 5;
	    }.bind(this), 20);
	  }

	  /**
	   * @private
	   */
	  scrollToRight() {
	    this.arrowTimer = setInterval(function () {
	      this.stepListWrap.scrollLeft += 5;
	    }.bind(this), 20);
	  }

	  /**
	   * @private
	   */
	  stopAutoScroll() {
	    clearInterval(this.arrowTimer);
	  }

	  /**
	   * @private
	   */
	  toggleArrows() {
	    this.togglePrevArrow();
	    this.toggleNextArrow();
	  }

	  /**
	   * @private
	   */
	  toggleNextArrow() {
	    if (this.stepListWrap.scrollWidth > this.stepListWrap.offsetWidth && this.stepListWrap.offsetWidth + this.stepListWrap.scrollLeft < this.stepListWrap.scrollWidth) {
	      main_core.Dom.addClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
	    } else {
	      main_core.Dom.removeClass(this.nextArrow, 'ui-tutor-popup-arrow-show');
	    }
	  }

	  /**
	   * @private
	   */
	  togglePrevArrow() {
	    if (this.stepListWrap.scrollLeft > 0) {
	      main_core.Dom.addClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
	    } else {
	      main_core.Dom.removeClass(this.prevArrow, 'ui-tutor-popup-arrow-show');
	    }
	  }

	  /**
	   * @private
	   */
	  showAnimation(popup) {
	    main_core.Dom.removeClass(popup, 'ui-tutor-popup-hide-complex');
	    main_core.Dom.removeClass(popup, 'ui-tutor-popup-hide');
	    if (this.complexAnimation) {
	      main_core.Dom.addClass(popup, 'ui-tutor-popup-show-complex');
	    } else {
	      main_core.Dom.addClass(popup, 'ui-tutor-popup-show');
	    }
	  }

	  /**
	   * @private
	   */
	  fadeAnimation(popup) {
	    main_core.Dom.removeClass(popup, 'ui-tutor-popup-show-complex');
	    main_core.Dom.removeClass(popup, 'ui-tutor-popup-show');
	    if (this.complexAnimation) {
	      main_core.Dom.addClass(popup, 'ui-tutor-popup-hide-complex');
	    } else {
	      main_core.Dom.addClass(popup, 'ui-tutor-popup-hide');
	    }
	  }

	  /**
	   * @private
	   */
	  scrollToStep() {
	    let posList = null;
	    let posStep = null;
	    if (this.stepListWrap) {
	      posList = main_core.Dom.getPosition(this.stepListWrap);
	      posStep = main_core.Dom.getPosition(this.stepListWrap.querySelector('[data-step="' + this.currentStepIndex + '"]'));
	    }
	    const offset = 7; // padding 2px and margin 5px

	    if (!main_core.Type.isNull(posStep) && posStep.left + posStep.width > posList.left + posList.width) {
	      this.stepListWrap.scrollLeft += posStep.left - (posList.left + posList.width) + posStep.width + offset;
	    }
	    if (!main_core.Type.isNull(posStep) && posStep.left < posList.left) {
	      this.stepListWrap.scrollLeft -= posList.left - posStep.left + offset;
	    }
	  }

	  /**
	   * @private
	   */
	  static getFullEventName(shortName) {
	    return shortName;
	  }
	  static getInstance() {
	    return Manager.getScenarioInstance();
	  }
	  static init(options) {
	    return Manager.initScenario(options);
	  }
	}

	exports.Scenario = Scenario;
	exports.Manager = Manager;
	exports.Step = Step;

}((this.BX.UI.Tutor = this.BX.UI.Tutor || {}),BX,BX.UI.Tour,BX));
//# sourceMappingURL=tutor.bundle.js.map
