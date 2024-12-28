/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_popup,ui_designTokens,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class GuideConditionColor {}
	GuideConditionColor.WARNING = '--condition-warning';
	GuideConditionColor.ALERT = '--condition-alert';
	GuideConditionColor.PRIMARY = '--condition-primary';
	GuideConditionColor.COPILOT = '--condition-copilot';

	class Step extends main_core.Event.EventEmitter {
	  constructor(options) {
	    super(options);
	    this.target = null;
	    if (main_core.Type.isString(options.target) && options.target !== '' || main_core.Type.isFunction(options.target) || main_core.Type.isDomNode(options.target)) {
	      this.target = options.target;
	    }
	    this.id = options.id || null;
	    this.text = options.text;
	    this.areaPadding = options.areaPadding;
	    this.link = options.link || '';
	    this.linkTitle = options.linkTitle || null;
	    this.rounded = options.rounded || false;
	    this.title = options.title || null;
	    this.iconSrc = options.iconSrc || null;
	    this.article = options.article || null;
	    this.articleAnchor = options.articleAnchor || null;
	    this.infoHelperCode = options.infoHelperCode || null;
	    this.position = options.position || null;
	    this.cursorMode = options.cursorMode || false;
	    this.targetEvent = options.targetEvent || null;
	    this.buttons = options.buttons || [];
	    this.condition = options.condition || null;
	    const events = main_core.Type.isPlainObject(options.events) ? options.events : {};
	    for (const eventName in events) {
	      const callback = main_core.Type.isFunction(events[eventName]) ? events[eventName] : main_core.Reflection.getClass(events[eventName]);
	      if (callback) {
	        this.subscribe(this.constructor.getFullEventName(eventName), () => {
	          callback();
	        });
	      }
	    }
	  }
	  getCondition() {
	    return this.condition;
	  }
	  getTarget() {
	    if (main_core.Type.isString(this.target) && this.target !== '') {
	      return document.querySelector(this.target);
	    }
	    if (main_core.Type.isFunction(this.target)) {
	      return this.target();
	    }
	    return this.target;
	  }
	  getTargetPos() {
	    if (main_core.Type.isDomNode(this.target)) {
	      return main_core.Dom.getPosition(this.target);
	    }
	  }
	  getId() {
	    return this.id;
	  }
	  getButtons() {
	    return this.buttons;
	  }
	  getAreaPadding() {
	    return this.areaPadding;
	  }
	  getRounded() {
	    return this.rounded;
	  }
	  getText() {
	    return this.text;
	  }
	  getLink() {
	    return this.link;
	  }
	  getLinkTitle() {
	    return this.linkTitle;
	  }
	  getTitle() {
	    return this.title;
	  }
	  getIconSrc() {
	    return this.iconSrc;
	  }
	  getPosition() {
	    return this.position;
	  }
	  getArticle() {
	    return this.article;
	  }
	  getArticleAnchor() {
	    return this.articleAnchor;
	  }
	  getInfoHelperCode() {
	    return this.infoHelperCode;
	  }
	  getCursorMode() {
	    return this.cursorMode;
	  }
	  getTargetEvent() {
	    return this.targetEvent;
	  }
	  static getFullEventName(shortName) {
	    return `Step:${shortName}`;
	  }
	  setTarget(target) {
	    this.target = target;
	  }
	  initTargetEvent() {
	    if (main_core.Type.isFunction(this.targetEvent)) {
	      this.targetEvent();
	      return;
	    }
	    this.getTarget().dispatchEvent(new MouseEvent(this.targetEvent));
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
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18;
	class Guide extends main_core.Event.EventEmitter {
	  constructor(options = {}) {
	    super(options);
	    options = main_core.Type.isPlainObject(options) ? options : {};

	    /** @var {Step[]}*/
	    this.steps = [];
	    if (Array.isArray(options.steps)) {
	      options.steps.forEach(step => {
	        this.steps.push(new Step(step));
	      });
	    }
	    if (this.steps.length < 1) {
	      throw new Error("BX.UI.Tour.Guide: 'steps' argument is required.");
	    }
	    this.id = "ui-tour-guide-" + main_core.Text.getRandom();
	    this.setId(options.id);
	    this.autoSave = false;
	    this.popup = null;
	    this.layout = {
	      overlay: null,
	      element: null,
	      title: null,
	      text: null,
	      link: null,
	      closeIcon: {
	        right: '0',
	        top: '0'
	      },
	      btnContainer: null,
	      nextBtn: null,
	      backBtn: null,
	      content: null,
	      finalContent: null,
	      counter: null,
	      currentCounter: null,
	      counterItems: []
	    };
	    this.buttons = options.buttons || "";
	    this.onEvents = options.onEvents || false;
	    this.currentStepIndex = 0;
	    this.targetPos = null;
	    this.clickOnBackBtn = false;
	    this.helper = top.BX.Helper;
	    this.targetContainer = main_core.Type.isDomNode(options.targetContainer) ? options.targetContainer : null;
	    this.overlay = main_core.Type.isBoolean(options.overlay) ? options.overlay : true;
	    this.finalStep = options.finalStep || false;
	    this.finalText = options.finalText || "";
	    this.finalTitle = options.finalTitle || "";
	    this.simpleMode = options.simpleMode || false;
	    this.setAutoSave(options.autoSave);
	    const events = main_core.Type.isPlainObject(options.events) ? options.events : {};
	    for (let eventName in events) {
	      let cb = main_core.Type.isFunction(events[eventName]) ? events[eventName] : main_core.Reflection.getClass(events[eventName]);
	      if (cb) {
	        this.subscribe(this.constructor.getFullEventName(eventName), () => {
	          cb();
	        });
	      }
	    }
	    main_core.Event.bind(window, "resize", this.handleResizeWindow.bind(this));
	  }

	  /**
	   * @public
	   * @returns {string}
	   */
	  getId() {
	    return this.id;
	  }
	  setId(id) {
	    if (main_core.Type.isString(id) && id !== '') {
	      this.id = id;
	    }
	  }

	  /**
	   * @public
	   * @returns {Boolean}
	   */
	  getAutoSave() {
	    return this.autoSave;
	  }
	  setAutoSave(mode) {
	    if (main_core.Type.isBoolean(mode)) {
	      this.autoSave = mode;
	    }
	  }
	  save() {
	    const optionName = "view_date_" + this.getId();
	    main_core.userOptions.save("ui-tour", optionName, null, Math.floor(Date.now() / 1000));
	    main_core.userOptions.send(null);
	  }

	  /**
	   * @public
	   */
	  start() {
	    this.emit(this.constructor.getFullEventName("onStart"), {
	      guide: this
	    });
	    if (this.getAutoSave()) {
	      this.save();
	    }
	    if (this.overlay) {
	      this.setOverlay();
	    }
	    const popup = this.getPopup();
	    popup.show();
	    if (this.popup.getPopupContainer()) {
	      main_core.Dom.removeClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	    }
	    this.showStep();
	    main_core.Dom.addClass(this.layout.backBtn, "ui-tour-popup-btn-hidden");
	    if (this.getCurrentStep().getTarget()) {
	      main_core.Dom.addClass(this.getCurrentStep().getTarget(), "ui-tour-selector");
	    }
	  }

	  /**
	   * @public
	   */
	  close() {
	    if (this.currentStepIndex === this.steps.length && this.onEvents) return;
	    this.closeStep();
	    this.emit(this.constructor.getFullEventName("onFinish"), {
	      guide: this
	    });
	    if (this.popup) {
	      this.popup.destroy();
	    }
	    if (this.layout.cursor) {
	      main_core.Dom.remove(this.layout.cursor);
	      this.layout.cursor = null;
	    }
	    if (this.onEvents) {
	      this.increaseCurrentStepIndex();
	    }
	    main_core.Dom.remove(this.layout.overlay);
	    main_core.Dom.removeClass(document.body, "ui-tour-body-overflow");
	    if (this.getCurrentStep() && this.getCurrentStep().getTarget()) {
	      this.getCurrentStep().getTarget().classList.remove("ui-tour-selector");
	    }
	    this.layout.overlay = null;
	    this.layout.element = null;
	    this.layout.title = null;
	    this.layout.text = null;
	    this.layout.link = null;
	    this.layout.btnContainer = null;
	    this.layout.nextBtn = null;
	    this.layout.backBtn = null;
	    this.layout.content = null;
	    this.layout.finalContent = null;
	    this.layout.counter = null;
	    this.layout.currentCounter = null;
	    this.layout.counterItems = [];
	    this.popup = null;
	  }

	  /**
	   * @private
	   */
	  showStep() {
	    this.adjustEvents();
	    main_core.Dom.removeClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	    if (this.layout.element) {
	      main_core.Dom.removeClass(this.layout.element, "ui-tour-overlay-element-opacity");
	    }
	    if (this.layout.backBtn) {
	      setTimeout(() => {
	        this.layout.backBtn.style.display = "block";
	      }, 200);
	    }
	    if (this.overlay) {
	      this.setOverlayElementForm();
	    }
	    if (this.getCurrentStep()) {
	      this.setCoords(this.getCurrentStep().getTarget());
	    }
	    this.setPopupData();
	  }

	  /**
	   * @public
	   */
	  showNextStep() {
	    if (this.currentStepIndex === this.steps.length) {
	      return;
	    }
	    if (this.getCurrentStep().getCursorMode()) {
	      this.showCursor();
	    } else {
	      const popup = this.getPopup();
	      popup.show();
	      if (popup.getPopupContainer()) {
	        main_core.Dom.removeClass(popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	      }
	      if (this.getCurrentStep()) {
	        this.setCoords(this.getCurrentStep().getTarget());
	      }
	      this.setPopupData();
	    }
	    this.adjustEvents();
	    if (this.getCurrentStep() && this.getCurrentStep().getTarget()) {
	      main_core.Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
	    }
	  }
	  /**
	   * @private
	   */
	  adjustEvents() {
	    let currentStep = this.getCurrentStep();
	    currentStep.emit(currentStep.constructor.getFullEventName("onShow"), {
	      step: currentStep,
	      guide: this
	    });
	    if (currentStep.getTarget()) {
	      let close = this.close.bind(this);
	      const clickEvent = e => {
	        if (e.isTrusted) {
	          close();
	        }
	        main_core_events.EventEmitter.emit('UI.Tour.Guide:clickTarget', this);
	        main_core.Event.unbind(currentStep.getTarget(), 'click', clickEvent);
	      };
	      main_core.Event.bind(currentStep.getTarget(), 'click', clickEvent);
	      this.subscribe('UI.Tour.Guide:onFinish', () => {
	        main_core.Event.unbind(currentStep.getTarget(), 'click', close);
	      });
	      const targetPosWindow = main_core.Dom.getPosition(currentStep.getTarget());
	      if (!this.isTargetVisible(targetPosWindow)) {
	        this.scrollToTarget(targetPosWindow);
	      }
	    }
	  }
	  /**
	   * @private
	   */
	  closeStep() {
	    const currentStep = this.getCurrentStep();
	    if (currentStep) {
	      currentStep.emit(currentStep.constructor.getFullEventName("onClose"), {
	        step: currentStep,
	        guide: this
	      });
	      const target = currentStep.getTarget();
	      if (target) {
	        main_core.Dom.removeClass(target, "ui-tour-selector");
	      }
	    }
	  }
	  setPopupPosition() {
	    if (!this.getCurrentStep().getTarget() || this.targetPos === null || this.getCurrentStep().getPosition() === 'center') {
	      this.getPopup().setBindElement(null);
	      this.getPopup().setOffset({
	        offsetLeft: 0,
	        offsetTop: 0
	      });
	      this.getPopup().setAngle(false);
	      this.getPopup().adjustPosition();
	      return;
	    }
	    let offsetLeft = 0;
	    let offsetTop = -15;
	    let angleOffset = 0;
	    let anglePosition = "top";
	    const bindOptions = {
	      forceTop: true,
	      forceLeft: true,
	      forceBindPosition: true
	    };
	    const popupWidth = this.getPopup().getPopupContainer().offsetWidth;
	    const clientWidth = document.documentElement.clientWidth;
	    if (this.getCurrentStep().getPosition() === "right") {
	      anglePosition = "left";
	      offsetLeft = this.targetPos.width + 30;
	      offsetTop = this.targetPos.height + this.getAreaPadding();
	      if (this.targetPos.left + offsetLeft + popupWidth > clientWidth) {
	        let left = this.targetPos.left - popupWidth;
	        if (left > 0) {
	          offsetLeft = -popupWidth - 30;
	          anglePosition = "right";
	        }
	      }
	    } else if (this.getCurrentStep().getPosition() === "left") {
	      anglePosition = "right";
	      offsetLeft = -this.targetPos.width - (popupWidth - this.targetPos.width) - 40;
	      offsetTop = this.targetPos.height + this.getAreaPadding();
	      if (this.targetPos.right + offsetLeft + popupWidth < clientWidth) {
	        let left = this.targetPos.left - popupWidth;
	        if (left < 0) {
	          offsetLeft = this.targetPos.width + 40;
	          anglePosition = "left";
	        }
	      }
	    } else
	      // top || bottom
	      {
	        bindOptions.forceLeft = false;
	        bindOptions.forceTop = false;
	        if (this.getCurrentStep().getRounded()) {
	          if (!this.onEvents) {
	            offsetTop = -(this.layout.element.getAttribute("r") - this.targetPos.height / 2 + 10);
	          }
	          angleOffset = 0;
	          offsetLeft = this.targetPos.width / 2;
	        } else if (this.targetPos.width < 30) {
	          offsetLeft = this.targetPos.width / 2;
	          offsetTop = -15;
	          angleOffset = 0;
	        } else {
	          offsetLeft = 25;
	          if (!this.onEvents) {
	            offsetTop = -(this.layout.element.getAttribute("height") / 2 - this.targetPos.height / 2 + 10);
	          } else {
	            offsetTop = 0;
	          }
	          angleOffset = 0;
	        }
	      }
	    let bindElement = this.getCurrentStep().getTarget();
	    if (this.getCurrentStep().getPosition() === 'center') bindElement = window;
	    this.getPopup().setBindElement(bindElement);
	    this.getPopup().setOffset({
	      offsetLeft: offsetLeft,
	      offsetTop: -offsetTop
	    });
	    this.getPopup().setAngle({
	      position: anglePosition,
	      offset: angleOffset
	    });
	    this.getPopup().adjustPosition(bindOptions);
	  }

	  /**
	   * @private
	   */
	  setOverlay() {
	    this.layout.overlay = main_core.Tag.render(_t || (_t = _`
			<svg class="ui-tour-overlay" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" preserveAspectRatio="none">
				<mask id="hole">
					<defs>
						<filter id="ui-tour-filter">
							<feGaussianBlur stdDeviation="0"/>
						</filter>
					</defs>
					<rect x="0" y="0" width="100%" height="100%" fill="white"></rect>
					<rect id="rect" class="ui-tour-overlay-element ui-tour-overlay-element-rect" x="1035.5" y="338" width="422" rx="2" ry="2" height="58" filter="url(#ui-tour-filter)"></rect>
					<circle id="circle" class="ui-tour-overlay-element ui-tour-overlay-element-circle" cx="10" cy="10" r="10" filter="url(#ui-tour-filter)"></circle>
				</mask>
				<rect x="0" y="0" width="100%" height="100%" fill="#000" mask="url(#hole)"></rect>
			</svg>
		`));
	    main_core.Dom.addClass(document.body, 'ui-tour-body-overflow');
	    if (this.targetContainer) {
	      main_core.Dom.append(this.layout.overlay, this.targetContainer);
	    } else {
	      main_core.Dom.append(this.layout.overlay, document.body);
	    }
	    this.setOverlayElementForm();
	  }
	  setOverlayElementForm() {
	    if (this.getCurrentStep().getRounded()) {
	      this.layout.overlay.querySelector(".ui-tour-overlay-element-rect").style.display = "none";
	      this.layout.overlay.querySelector(".ui-tour-overlay-element-circle").style.display = "block";
	      this.layout.element = this.layout.overlay.querySelector(".ui-tour-overlay-element-circle");
	    } else {
	      this.layout.overlay.querySelector(".ui-tour-overlay-element-circle").style.display = "none";
	      this.layout.overlay.querySelector(".ui-tour-overlay-element-rect").style.display = "block";
	      this.layout.element = this.layout.overlay.querySelector(".ui-tour-overlay-element-rect");
	    }
	    return this.layout.element;
	  }
	  handleResizeWindow() {
	    if (this.layout.element && this.getCurrentStep()) {
	      this.setCoords(this.getCurrentStep().getTarget());
	    }
	  }

	  /**
	   * @private
	   * @param {Element} node
	   */
	  setCoords(node) {
	    if (!node) {
	      if (this.layout.element) {
	        this.layout.element.style.display = "none";
	      }
	      return;
	    }
	    this.targetPos = node.getBoundingClientRect();
	    if (this.layout.element) {
	      this.layout.element.style.display = "block";
	      if (this.getCurrentStep().getRounded()) {
	        this.layout.element.setAttribute('cx', this.targetPos.left + this.targetPos.width / 2);
	        this.layout.element.setAttribute('cy', this.targetPos.top + this.targetPos.height / 2);
	        this.layout.element.setAttribute('r', this.targetPos.width / 2 + this.getAreaPadding());
	      } else {
	        this.layout.element.setAttribute('x', this.targetPos.left - this.getAreaPadding());
	        this.layout.element.setAttribute('y', this.targetPos.top - this.getAreaPadding());
	        this.layout.element.setAttribute('width', this.targetPos.width + this.getAreaPadding() * 2);
	        this.layout.element.setAttribute('height', this.targetPos.height + this.getAreaPadding() * 2);
	      }
	    }
	  }
	  getAreaPadding() {
	    let padding = 15;
	    if (this.getCurrentStep().getAreaPadding() >= 0) {
	      padding = this.getCurrentStep().getAreaPadding();
	    }
	    return padding;
	  }

	  /**
	   * @private
	   */
	  increaseCurrentStepIndex() {
	    this.currentStepIndex++;
	    if (this.currentStepIndex + 1 === this.steps.length && !this.finalStep && !this.onEvents) {
	      setTimeout(() => {
	        this.layout.nextBtn.textContent = main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE");
	      }, 200);
	    }
	  }

	  /**
	   * @private
	   */
	  reduceCurrentStepIndex() {
	    if (this.currentStepIndex === 0) {
	      return;
	    }
	    if (this.currentStepIndex < this.steps.length && !this.finalStep) {
	      setTimeout(() => {
	        this.layout.nextBtn.textContent = main_core.Loc.getMessage("JS_UI_TOUR_BUTTON");
	      }, 200);
	    }
	    this.currentStepIndex--;
	  }

	  /**
	   * @public
	   */
	  getPopup() {
	    if (!this.popup) {
	      var _this$getCurrentStep$4;
	      let bindElement = this.getCurrentStep() ? this.getCurrentStep().getTarget() : window;
	      let className = 'popup-window-ui-tour popup-window-ui-tour-opacity';
	      if (this.getCurrentStep().getCondition()) {
	        var _this$getCurrentStep$2;
	        if (main_core.Type.isString(this.getCurrentStep().getCondition())) {
	          className = className + ' --condition-' + this.getCurrentStep().getCondition().toLowerCase();
	        }
	        if (main_core.Type.isObject(this.getCurrentStep().getCondition())) {
	          var _this$getCurrentStep$;
	          className = className + ' --condition-' + ((_this$getCurrentStep$ = this.getCurrentStep().getCondition()) == null ? void 0 : _this$getCurrentStep$.color.toLowerCase());
	        }
	        if (((_this$getCurrentStep$2 = this.getCurrentStep().getCondition()) == null ? void 0 : _this$getCurrentStep$2.top) !== false) {
	          className = className + ' --condition';
	        }
	      }
	      this.onEvents ? className = className + ' popup-window-ui-tour-animate' : null;
	      let buttons = [];
	      if (this.getCurrentStep() && this.getCurrentStep().getButtons().length > 0) {
	        this.getCurrentStep().getButtons().forEach(item => {
	          buttons.push(new main_popup.PopupWindowButton({
	            text: item.text,
	            className: 'ui-btn ui-btn-sm ui-btn-primary ui-btn-round',
	            events: {
	              click: main_core.Type.isFunction(item.event) ? item.event : null
	            }
	          }));
	        });
	      }
	      const popupWidth = this.onEvents ? 280 : 420;
	      this.popup = new main_popup.Popup({
	        targetContainer: this.targetContainer,
	        content: this.getContent(),
	        bindElement: bindElement,
	        className: className,
	        autoHide: this.onEvents ? false : true,
	        offsetTop: 15,
	        width: popupWidth,
	        closeIcon: true,
	        noAllPaddings: true,
	        bindOptions: {
	          forceTop: true,
	          forceLeft: true,
	          forceBindPosition: true
	        },
	        events: {
	          onPopupClose: popup => {
	            if (popup.destroyed === false && this.onEvents) main_core_events.EventEmitter.emit('UI.Tour.Guide:onPopupClose', this);
	            this.close();
	          }
	        },
	        buttons
	      });
	      const conditionNodeTop = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-tour-popup-condition-top">
					<div class="ui-tour-popup-condition-angle"></div>
				</div>
			`));
	      const conditionNodeBottom = main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-tour-popup-condition-bottom"></div>
			`));
	      if (main_core.Type.isString(this.getCurrentStep().getCondition())) {
	        main_core.Dom.append(conditionNodeTop, this.popup.getContentContainer());
	      }
	      if (main_core.Type.isObject(this.getCurrentStep().getCondition())) {
	        var _this$getCurrentStep$3;
	        if (((_this$getCurrentStep$3 = this.getCurrentStep().getCondition()) == null ? void 0 : _this$getCurrentStep$3.top) !== false) {
	          main_core.Dom.append(conditionNodeTop, this.popup.getContentContainer());
	        }
	      }
	      if (((_this$getCurrentStep$4 = this.getCurrentStep().getCondition()) == null ? void 0 : _this$getCurrentStep$4.bottom) !== false) {
	        main_core.Dom.append(conditionNodeBottom, this.popup.getContentContainer());
	      }
	    }
	    return this.popup;
	  }

	  /**
	   * @private
	   */
	  getContent() {
	    if (!this.layout.content) {
	      let iconNode = '';
	      if (this.getCurrentStep().getIconSrc()) {
	        iconNode = main_core.Tag.render(_t4 || (_t4 = _`
					<div
						class="ui-tour-popup-icon"
						style="background-image: url(${0});"
					></div>
				`), encodeURI(this.getCurrentStep().getIconSrc()));
	      }
	      let linkNode = '';
	      if (this.steps.some(step => step.getLink()) || this.steps.some(step => step.getArticle()) || this.steps.some(step => step.getInfoHelperCode())) {
	        linkNode = this.getLink();
	      }
	      this.layout.content = main_core.Tag.render(_t5 || (_t5 = _`
				<div
					class="ui-tour-popup ${0} ${0}"
					style="${0};"
				>
					${0}
					<div>
						${0}
						<div class="ui-tour-popup-content">
							${0}
							${0}
						</div>
						${0}
						<div class="ui-tour-popup-footer">
							<div class="ui-tour-popup-index">
								${0}
								${0}
							</div>
								${0}
						</div>
					</div>
				</div>
			`), this.simpleMode ? 'ui-tour-popup-simple' : '', this.onEvents ? 'ui-tour-popup-events' : '', iconNode ? 'padding-left: 13px;' : '', iconNode, this.getTitle(), this.getText(), linkNode, linkNode, this.onEvents ? '' : this.getCounterItems(), this.onEvents ? '' : this.getCurrentCounter(), this.onEvents ? '' : this.getBtnContainer());
	    }
	    return this.layout.content;
	  }

	  /**
	   * @private
	   */
	  setPopupData() {
	    main_core.Event.unbindAll(this.layout.link, 'click');
	    this.getTitle().innerHTML = this.getCurrentStep().getTitle();
	    this.getText().innerHTML = this.getCurrentStep().getText();
	    if (this.getCurrentStep().getArticle() || this.getCurrentStep().getLink() || this.getCurrentStep().getInfoHelperCode()) {
	      main_core.Dom.removeClass(this.layout.link, 'ui-tour-popup-link-hide');
	      if (this.getCurrentStep().getArticle()) {
	        main_core.Event.bind(this.layout.link, 'click', this.handleClickLink.bind(this));
	      } else if (this.getCurrentStep().getInfoHelperCode()) {
	        main_core.Event.bind(this.layout.link, 'click', this.handleInfoHelperCodeClickLink.bind(this));
	      }
	      if (this.getCurrentStep().getLink()) {
	        this.getLink().setAttribute('href', this.getCurrentStep().getLink());
	      }
	    } else {
	      main_core.Dom.addClass(this.layout.link, "ui-tour-popup-link-hide");
	    }
	    this.getCurrentCounter().textContent = main_core.Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT").replace('#NUMBER#', this.currentStepIndex + 1).replace('#NUMBER_TOTAL#', this.steps.length);
	    for (let i = 0; i < this.steps.length; i++) {
	      if (this.layout.counterItems[i]) {
	        main_core.Dom.removeClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-current');
	        main_core.Dom.removeClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-passed');
	      }
	      if (i === this.currentStepIndex) {
	        main_core.Dom.addClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-current');
	      } else if (i < this.currentStepIndex) {
	        main_core.Dom.addClass(this.layout.counterItems[i], 'ui-tour-popup-index-item-passed');
	      }
	    }
	    this.setPopupPosition();
	  }

	  /**
	   * @public
	   */
	  handleClickLink() {
	    event.preventDefault();
	    if (!this.helper) {
	      this.helper = top.BX.Helper;
	    }
	    const article = this.getCurrentStep().getArticle();
	    const anchor = this.getCurrentStep().getArticleAnchor();

	    // eslint-disable-next-line sonarjs/no-nested-template-literals
	    const url = `redirect=detail&code=${article}${anchor ? `&anchor=${anchor}` : ''}`;
	    this.helper.show(url);
	    if (this.helper.isOpen()) {
	      this.getPopup().setAutoHide(false);
	    }
	    main_core_events.EventEmitter.subscribe(this.helper.getSlider(), 'SidePanel.Slider:onCloseComplete', () => {
	      this.getPopup().setAutoHide(true);
	    });
	  }
	  handleInfoHelperCodeClickLink() {
	    event.preventDefault();
	    if (main_core.Reflection.getClass('BX.UI.InfoHelper.show')) {
	      const helper = top.BX.UI.InfoHelper;
	      helper.show(this.getCurrentStep().getInfoHelperCode());
	      if (helper.isOpen()) {
	        this.getPopup().setAutoHide(false);
	      }
	      main_core_events.EventEmitter.subscribe(helper.getSlider(), 'SidePanel.Slider:onCloseComplete', () => {
	        this.getPopup().setAutoHide(true);
	      });
	    }
	  }

	  /**
	   * @public
	   */
	  getTitle() {
	    if (this.layout.title === null) {
	      this.layout.title = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-tour-popup-title"></div>
			`));
	    }
	    return this.layout.title;
	  }

	  /**
	   * @public
	   */
	  getText() {
	    if (this.layout.text === null) {
	      this.layout.text = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="ui-tour-popup-text"></div>
			`));
	    }
	    return this.layout.text;
	  }

	  /**
	   * @public
	   */
	  getLink() {
	    if (!this.layout.link) {
	      var _this$steps$this$curr;
	      const title = (_this$steps$this$curr = this.steps[this.currentStepIndex].getLinkTitle()) != null ? _this$steps$this$curr : main_core.Loc.getMessage('JS_UI_TOUR_LINK');
	      this.layout.link = main_core.Tag.render(_t8 || (_t8 = _`
				<a target="_blank" href="" class="ui-tour-popup-link">
					${0}
				</a>
			`), title);
	    }
	    return this.layout.link;
	  }

	  /**
	   * @public
	   */
	  getCurrentCounter() {
	    if (this.layout.currentCounter === null) {
	      this.layout.currentCounter = main_core.Tag.render(_t9 || (_t9 = _`
				<span class="ui-tour-popup-counter">
					${0}
				</span>
			`), main_core.Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT").replace('#NUMBER#', this.currentStepIndex + 1).replace('#NUMBER_TOTAL#', this.steps.length));
	    }
	    return this.layout.currentCounter;
	  }

	  /**
	   * @private
	   */
	  getBtnContainer() {
	    if (this.layout.btnContainer === null) {
	      this.layout.btnContainer = main_core.Tag.render(_t10 || (_t10 = _`
				<div class="ui-tour-popup-btn-block"></div>
			`));
	      this.layout.nextBtn = main_core.Tag.render(_t11 || (_t11 = _`
				<button id="next" class="ui-tour-popup-btn-next">
					${0}
				</button>
			`), this.simpleMode ? main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_SIMPLE") : main_core.Loc.getMessage("JS_UI_TOUR_BUTTON"));
	      this.layout.backBtn = main_core.Tag.render(_t12 || (_t12 = _`
				<button id="back" class="ui-tour-popup-btn-back">
				</button>
			`));
	      main_core.Dom.append(this.layout.backBtn, this.layout.btnContainer);
	      main_core.Dom.append(this.layout.nextBtn, this.layout.btnContainer);
	      main_core.Event.bind(this.layout.nextBtn, "click", this.handleClickOnNextBtn.bind(this));
	      main_core.Event.bind(this.layout.backBtn, "click", this.handleClickOnBackBtn.bind(this));
	    }
	    return this.layout.btnContainer;
	  }
	  getCounterItems() {
	    if (this.layout.counter === null) {
	      this.layout.counter = main_core.Tag.render(_t13 || (_t13 = _`
				<span class="ui-tour-popup-index-items">
				</span>
			`));
	    }
	    this.layout.counterItems = [];
	    for (let i = 0; i < this.steps.length; i++) {
	      const currentStepIndex = main_core.Tag.render(_t14 || (_t14 = _`
				<span class="ui-tour-popup-index-item">
				</span>
			`));
	      this.layout.counterItems.push(currentStepIndex);
	      main_core.Dom.append(currentStepIndex, this.layout.counter);
	    }
	    return this.layout.counter;
	  }

	  /**
	   * @returns {Step}
	   */
	  getCurrentStep() {
	    return this.steps[this.currentStepIndex];
	  }

	  /**
	   * @returns {Step}
	   */
	  getPreviousStep() {
	    if (this.steps[this.currentStepIndex - 1]) {
	      return this.steps[this.currentStepIndex - 1];
	    }
	  }
	  handleClickOnNextBtn() {
	    main_core.Dom.addClass(this.layout.element, "ui-tour-overlay-element-opacity");
	    main_core.Dom.addClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	    this.clickOnBackBtn = false;
	    if (this.getCurrentStep()) {
	      this.closeStep();
	    }
	    this.increaseCurrentStepIndex();
	    if (this.getCurrentStep() && this.getCurrentStep().getTarget()) {
	      main_core.Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
	    }
	    if (this.currentStepIndex === this.steps.length) {
	      if (this.finalStep) {
	        this.setFinalStep();
	      } else {
	        this.close();
	      }
	    } else {
	      setTimeout(() => {
	        this.showStep();
	      }, 200);
	      if (main_core.Dom.hasClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden')) {
	        main_core.Dom.removeClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden');
	      }
	    }
	  }
	  handleClickOnBackBtn() {
	    main_core.Dom.addClass(this.layout.element, "ui-tour-overlay-element-opacity");
	    main_core.Dom.addClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	    this.closeStep();
	    this.reduceCurrentStepIndex();
	    if (this.currentStepIndex === 0) {
	      main_core.Dom.addClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden');
	    }
	    this.clickOnBackBtn = true;
	    setTimeout(() => {
	      this.layout.backBtn.style.display = "none";
	      this.showStep();
	    }, 200);
	    if (this.getCurrentStep().getTarget()) {
	      main_core.Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
	    }
	  }
	  setFinalStep() {
	    this.layout.element.style.display = "none";
	    this.getPopup().destroy();
	    const finalPopup = this.getFinalPopup();
	    finalPopup.show();
	    main_core.Dom.addClass(finalPopup.getPopupContainer(), "popup-window-ui-tour-final-show");
	  }

	  /**
	   * @public
	   */
	  getFinalPopup() {
	    this.popup = new main_popup.Popup({
	      content: this.getFinalContent(),
	      className: 'popup-window-ui-tour-final',
	      offsetTop: this.onEvents ? 0 : 15,
	      offsetLeft: 35,
	      maxWidth: 430,
	      minWidth: 300
	    });
	    return this.popup;
	  }
	  getFinalContent() {
	    if (!this.layout.finalContent) {
	      this.layout.finalContent = main_core.Tag.render(_t15 || (_t15 = _`
				<div class="ui-tour-popup --final">
					<div class="ui-tour-popup-title">
						${0}
					</div>
					<div class="ui-tour-popup-content">
						<div class="ui-tour-popup-text">
							${0}
						</div>
					</div>
					<div class="ui-tour-popup-footer-btn">
						${0}
					</div>
				</div>
			`), this.finalTitle, this.finalText, this.getFinalBtn());
	    }
	    return this.layout.finalContent;
	  }
	  getFinalBtn() {
	    const buttons = [];
	    if (this.buttons !== "") {
	      for (let i = 0; i < this.buttons.length; i++) {
	        var _this$buttons$i$event;
	        let btn = main_core.Tag.render(_t16 || (_t16 = _`
					<button class="${0}" onclick="${0}">
					${0}
					</button>
				`), this.buttons[i].class, (_this$buttons$i$event = this.buttons[i].events) == null ? void 0 : _this$buttons$i$event.click, this.buttons[i].text);
	        buttons.push(btn);
	      }
	    } else {
	      let btn = main_core.Tag.render(_t17 || (_t17 = _`
				<button class="ui-btn ui-btn-sm ui-btn-primary ui-btn-round" onclick="${0}">
				${0}
				</button>
			`), this.close.bind(this), main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE"));
	      buttons.push(btn);
	    }
	    return buttons;
	  }

	  /**
	   * @private
	   */
	  isTargetVisible(node) {
	    return node.top >= 0 && node.left >= 0 && node.bottom <= (window.innerHeight || document.documentElement.clientHeight) && node.right <= (window.innerWidth || document.documentElement.clientWidth);
	  }

	  /**
	   * @private
	   */
	  scrollToTarget(target) {
	    window.scrollTo(0, target.y - this.getAreaPadding());
	  }

	  /**
	   * @private
	   */
	  static getFullEventName(shortName) {
	    return "UI.Tour.Guide:" + shortName;
	  }
	  showCursor() {
	    this.setCursorPos();
	    setTimeout(() => {
	      this.animateCursor();
	    }, 1000);
	  }
	  getCursor() {
	    if (!this.layout.cursor) {
	      this.layout.cursor = main_core.Tag.render(_t18 || (_t18 = _`
				<div class="ui-tour-cursor"></div>
			`));
	      main_core.Event.bind(this.layout.cursor, 'transitionend', () => {
	        this.getCurrentStep().initTargetEvent();
	      });
	      main_core.Dom.append(this.layout.cursor, document.body);
	    }
	    return this.layout.cursor;
	  }
	  setCursorPos() {
	    const targetPos = this.getCurrentStep().getTargetPos();
	    let left = targetPos.left + targetPos.width / 2;
	    if (left < 0) {
	      left = 0;
	    }
	    this.cursorPaddingTop = 30;
	    let top = targetPos.bottom + this.cursorPaddingTop;
	    if (top < 0) {
	      top = 0;
	    }
	    main_core.Dom.adjust(this.getCursor(), {
	      style: {
	        top: top + 'px',
	        left: left + 'px'
	      }
	    });
	  }
	  animateCursor() {
	    const adjustment = this.cursorPaddingTop + this.getCurrentStep().getTargetPos().height / 2;
	    this.layout.cursor.style.transform = 'translateY(-' + adjustment + 'px)';
	  }
	}
	Guide.ConditionColor = GuideConditionColor;

	class Manager {
	  constructor() {
	    this.guides = new Map();
	    this.autoStartQueue = [];
	    this.currentGuide = null;
	  }
	  create(options) {
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    const id = options.id;
	    if (!main_core.Type.isString(id) && id !== '') {
	      throw new Error("'id' parameter is required.");
	    }
	    if (this.get(id)) {
	      throw new Error("The tour instance with the same 'id' already exists.");
	    }
	    const guide = new Guide(options);
	    this.guides.set(guide, true);
	    return guide;
	  }
	  add(options) {
	    const guide = this.create(options);
	    guide.subscribe('UI.Tour.Guide:onFinish', () => {
	      this.handleTourFinish(guide);
	    });
	    if (this.currentGuide) {
	      this.autoStartQueue.push(guide);
	    } else {
	      this.currentGuide = guide;
	      guide.start();
	    }
	  }

	  /**
	   * @public
	   * @param {string} id
	   * @returns {Guide|null}
	   */
	  get(id) {
	    return this.guides.get(id);
	  }

	  /**
	   * @public
	   * @param {string} id
	   */
	  remove(id) {
	    this.guides.delete(id);
	  }

	  /**
	   * @public
	   * @returns {Guide|null}
	   */
	  getCurrentGuide() {
	    return this.currentGuide;
	  }

	  /**
	   * @private
	   * @param {Guide} guide
	   */
	  handleTourFinish(guide) {
	    this.currentGuide = null;
	    this.remove(guide.getId());
	    const autoStartGuide = this.autoStartQueue.shift();
	    if (autoStartGuide) {
	      this.currentGuide = autoStartGuide;
	      autoStartGuide.start();
	    }
	  }
	}
	var manager = new Manager();

	exports.Guide = Guide;
	exports.Step = Step;
	exports.Manager = manager;

}((this.BX.UI.Tour = this.BX.UI.Tour || {}),BX.Event,BX.Main,BX,BX));
//# sourceMappingURL=tour.bundle.js.map
