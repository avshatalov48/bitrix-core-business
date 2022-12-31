this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,main_core_events,ui_designTokens,main_core) {
	'use strict';

	var Step = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(Step, _Event$EventEmitter);

	  function Step(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Step);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Step).call(this, options));
	    _this.target = null;

	    if (main_core.Type.isString(options.target) && options.target !== '' || main_core.Type.isFunction(options.target) || main_core.Type.isDomNode(options.target)) {
	      _this.target = options.target;
	    }

	    _this.id = options.id || null;
	    _this.text = options.text;
	    _this.areaPadding = options.areaPadding;
	    _this.link = options.link || "";
	    _this.rounded = options.rounded || false;
	    _this.title = options.title || null;
	    _this.article = options.article || null;
	    _this.position = options.position || null;
	    _this.cursorMode = options.cursorMode || false;
	    _this.targetEvent = options.targetEvent || null;
	    _this.buttons = options.buttons || [];
	    _this.condition = options.condition || null;
	    var events = main_core.Type.isPlainObject(options.events) ? options.events : {};

	    var _loop = function _loop(eventName) {
	      var callback = main_core.Type.isFunction(events[eventName]) ? events[eventName] : main_core.Reflection.getClass(events[eventName]);

	      if (callback) {
	        _this.subscribe(_this.constructor.getFullEventName(eventName), function () {
	          callback();
	        });
	      }
	    };

	    for (var eventName in events) {
	      _loop(eventName);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Step, [{
	    key: "getCondition",
	    value: function getCondition() {
	      return this.condition;
	    }
	  }, {
	    key: "getTarget",
	    value: function getTarget() {
	      if (main_core.Type.isString(this.target) && this.target !== '') {
	        return document.querySelector(this.target);
	      }

	      if (main_core.Type.isFunction(this.target)) {
	        return this.target();
	      }

	      return this.target;
	    }
	  }, {
	    key: "getTargetPos",
	    value: function getTargetPos() {
	      if (main_core.Type.isDomNode(this.target)) {
	        return main_core.Dom.getPosition(this.target);
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      return this.buttons;
	    }
	  }, {
	    key: "getAreaPadding",
	    value: function getAreaPadding() {
	      return this.areaPadding;
	    }
	  }, {
	    key: "getRounded",
	    value: function getRounded() {
	      return this.rounded;
	    }
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.link;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "getPosition",
	    value: function getPosition() {
	      return this.position;
	    }
	  }, {
	    key: "getArticle",
	    value: function getArticle() {
	      return this.article;
	    }
	  }, {
	    key: "getCursorMode",
	    value: function getCursorMode() {
	      return this.cursorMode;
	    }
	  }, {
	    key: "getTargetEvent",
	    value: function getTargetEvent() {
	      return this.targetEvent;
	    }
	  }, {
	    key: "setTarget",
	    value: function setTarget(target) {
	      this.target = target;
	    }
	  }, {
	    key: "initTargetEvent",
	    value: function initTargetEvent() {
	      if (main_core.Type.isFunction(this.targetEvent)) {
	        this.targetEvent();
	        return;
	      }

	      this.getTarget().dispatchEvent(new MouseEvent(this.targetEvent));
	    }
	  }], [{
	    key: "getFullEventName",
	    value: function getFullEventName(shortName) {
	      return "Step:" + shortName;
	    }
	  }]);
	  return Step;
	}(main_core.Event.EventEmitter);

	/**
	 * @namespace {BX.UI}
	 */
	var GuideConditionColor = function GuideConditionColor() {
	  babelHelpers.classCallCheck(this, GuideConditionColor);
	};

	babelHelpers.defineProperty(GuideConditionColor, "WARNING", '--condition-warning');
	babelHelpers.defineProperty(GuideConditionColor, "ALERT", '--condition-alert');
	babelHelpers.defineProperty(GuideConditionColor, "PRIMARY", '--condition-primary');

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17;
	var Guide = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(Guide, _Event$EventEmitter);

	  function Guide() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Guide);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Guide).call(this, options));
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    /** @var {Step[]}*/

	    _this.steps = [];

	    if (Array.isArray(options.steps)) {
	      options.steps.forEach(function (step) {
	        _this.steps.push(new Step(step));
	      });
	    }

	    if (_this.steps.length < 1) {
	      throw new Error("BX.UI.Tour.Guide: 'steps' argument is required.");
	    }

	    _this.id = "ui-tour-guide-" + main_core.Text.getRandom();

	    _this.setId(options.id);

	    _this.autoSave = false;
	    _this.popup = null;
	    _this.layout = {
	      overlay: null,
	      element: null,
	      title: null,
	      text: null,
	      link: null,
	      btnContainer: null,
	      nextBtn: null,
	      backBtn: null,
	      content: null,
	      finalContent: null,
	      counter: null,
	      currentCounter: null,
	      counterItems: []
	    };
	    _this.buttons = options.buttons || "";
	    _this.onEvents = options.onEvents || false;
	    _this.currentStepIndex = 0;
	    _this.targetPos = null;
	    _this.clickOnBackBtn = false;
	    _this.helper = top.BX.Helper;
	    _this.finalStep = options.finalStep || false;
	    _this.finalText = options.finalText || "";
	    _this.finalTitle = options.finalTitle || "";
	    _this.simpleMode = options.simpleMode || false;

	    _this.setAutoSave(options.autoSave);

	    var events = main_core.Type.isPlainObject(options.events) ? options.events : {};

	    var _loop = function _loop(eventName) {
	      var cb = main_core.Type.isFunction(events[eventName]) ? events[eventName] : main_core.Reflection.getClass(events[eventName]);

	      if (cb) {
	        _this.subscribe(_this.constructor.getFullEventName(eventName), function () {
	          cb();
	        });
	      }
	    };

	    for (var eventName in events) {
	      _loop(eventName);
	    }

	    main_core.Event.bind(window, "resize", _this.handleResizeWindow.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  /**
	   * @public
	   * @returns {string}
	   */


	  babelHelpers.createClass(Guide, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      if (main_core.Type.isString(id) && id !== '') {
	        this.id = id;
	      }
	    }
	    /**
	     * @public
	     * @returns {Boolean}
	     */

	  }, {
	    key: "getAutoSave",
	    value: function getAutoSave() {
	      return this.autoSave;
	    }
	  }, {
	    key: "setAutoSave",
	    value: function setAutoSave(mode) {
	      if (main_core.Type.isBoolean(mode)) {
	        this.autoSave = mode;
	      }
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var optionName = "view_date_" + this.getId();
	      main_core.userOptions.save("ui-tour", optionName, null, Math.floor(Date.now() / 1000));
	      main_core.userOptions.send(null);
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "start",
	    value: function start() {
	      this.emit(this.constructor.getFullEventName("onStart"), {
	        guide: this
	      });

	      if (this.getAutoSave()) {
	        this.save();
	      }

	      this.setOverlay();
	      var popup = this.getPopup();
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

	  }, {
	    key: "close",
	    value: function close() {
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

	  }, {
	    key: "showStep",
	    value: function showStep() {
	      var _this2 = this;

	      this.adjustEvents();
	      main_core.Dom.removeClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");

	      if (this.layout.element) {
	        main_core.Dom.removeClass(this.layout.element, "ui-tour-overlay-element-opacity");
	      }

	      if (this.layout.backBtn) {
	        setTimeout(function () {
	          _this2.layout.backBtn.style.display = "block";
	        }, 10);
	      }

	      this.setOverlayElementForm();

	      if (this.getCurrentStep()) {
	        this.setCoords(this.getCurrentStep().getTarget());
	      }

	      this.setPopupData();
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "showNextStep",
	    value: function showNextStep() {
	      if (this.currentStepIndex === this.steps.length) {
	        return;
	      }

	      if (this.getCurrentStep().getCursorMode()) {
	        this.showCursor();
	      } else {
	        var popup = this.getPopup();
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

	  }, {
	    key: "adjustEvents",
	    value: function adjustEvents() {
	      var _this3 = this;

	      var currentStep = this.getCurrentStep();
	      currentStep.emit(currentStep.constructor.getFullEventName("onShow"), {
	        step: currentStep,
	        guide: this
	      });

	      if (currentStep.getTarget()) {
	        var close = this.close.bind(this);

	        var clickEvent = function clickEvent(e) {
	          if (e.isTrusted) {
	            close();
	          }

	          main_core_events.EventEmitter.emit('UI.Tour.Guide:clickTarget', _this3);
	          main_core.Event.unbind(currentStep.getTarget(), 'click', clickEvent);
	        };

	        main_core.Event.bind(currentStep.getTarget(), 'click', clickEvent);
	        this.subscribe('UI.Tour.Guide:onFinish', function () {
	          main_core.Event.unbind(currentStep.getTarget(), 'click', close);
	        });
	        var targetPos = currentStep.getTarget().getBoundingClientRect();
	        var targetPosWindow = main_core.Dom.getPosition(currentStep.getTarget());

	        if (!this.isTargetVisible(targetPos)) {
	          this.scrollToTarget(targetPosWindow);
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "closeStep",
	    value: function closeStep() {
	      var currentStep = this.getCurrentStep();

	      if (currentStep) {
	        currentStep.emit(currentStep.constructor.getFullEventName("onClose"), {
	          step: currentStep,
	          guide: this
	        });
	        var target = currentStep.getTarget();

	        if (target) {
	          main_core.Dom.removeClass(target, "ui-tour-selector");
	        }
	      }
	    }
	  }, {
	    key: "setPopupPosition",
	    value: function setPopupPosition() {
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

	      var offsetLeft = 0;
	      var offsetTop = -15;
	      var angleOffset = 0;
	      var anglePosition = "top";
	      var bindOptions = {
	        forceTop: true,
	        forceLeft: true,
	        forceBindPosition: true
	      };
	      var popupWidth = this.getPopup().getPopupContainer().offsetWidth;
	      var clientWidth = document.documentElement.clientWidth;

	      if (this.getCurrentStep().getPosition() === "right") {
	        anglePosition = "left";
	        offsetLeft = this.targetPos.width + 30;
	        offsetTop = this.targetPos.height + this.getAreaPadding();

	        if (this.targetPos.left + offsetLeft + popupWidth > clientWidth) {
	          var left = this.targetPos.left - popupWidth;

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
	          var _left = this.targetPos.left - popupWidth;

	          if (_left < 0) {
	            offsetLeft = this.targetPos.width + 40;
	            anglePosition = "left";
	          }
	        }
	      } else // top || bottom
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

	      var bindElement = this.getCurrentStep().getTarget();
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

	  }, {
	    key: "setOverlay",
	    value: function setOverlay() {
	      this.layout.overlay = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<svg class=\"ui-tour-overlay\" xmlns=\"http://www.w3.org/2000/svg\" width=\"100%\" height=\"100%\" preserveAspectRatio=\"none\">\n\t\t\t\t<mask id=\"hole\">\n\t\t\t\t\t<defs>\n\t\t\t\t\t\t<filter id=\"ui-tour-filter\">\n\t\t\t\t\t\t\t<feGaussianBlur stdDeviation=\"0\"/>\n\t\t\t\t\t\t</filter>\n\t\t\t\t\t</defs>\n\t\t\t\t\t<rect x=\"0\" y=\"0\" width=\"100%\" height=\"100%\" fill=\"white\"></rect>\n\t\t\t\t\t<rect id=\"rect\" class=\"ui-tour-overlay-element ui-tour-overlay-element-rect\" x=\"1035.5\" y=\"338\" width=\"422\" rx=\"2\" ry=\"2\" height=\"58\" filter=\"url(#ui-tour-filter)\"></rect>\n\t\t\t\t\t<circle id=\"circle\" class=\"ui-tour-overlay-element ui-tour-overlay-element-circle\" cx=\"10\" cy=\"10\" r=\"10\" filter=\"url(#ui-tour-filter)\"></circle>\n\t\t\t\t</mask>\n\t\t\t\t<rect x=\"0\" y=\"0\" width=\"100%\" height=\"100%\" fill=\"#000\" mask=\"url(#hole)\"></rect>\n\t\t\t</svg>\n\t\t"])));
	      main_core.Dom.addClass(document.body, 'ui-tour-body-overflow');
	      main_core.Dom.append(this.layout.overlay, document.body);
	      this.setOverlayElementForm();
	    }
	  }, {
	    key: "setOverlayElementForm",
	    value: function setOverlayElementForm() {
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
	  }, {
	    key: "handleResizeWindow",
	    value: function handleResizeWindow() {
	      if (this.layout.element && this.getCurrentStep()) {
	        this.setCoords(this.getCurrentStep().getTarget());
	      }
	    }
	    /**
	     * @private
	     * @param {Element} node
	     */

	  }, {
	    key: "setCoords",
	    value: function setCoords(node) {
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
	  }, {
	    key: "getAreaPadding",
	    value: function getAreaPadding() {
	      var padding = 15;

	      if (this.getCurrentStep().getAreaPadding() >= 0) {
	        padding = this.getCurrentStep().getAreaPadding();
	      }

	      return padding;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "increaseCurrentStepIndex",
	    value: function increaseCurrentStepIndex() {
	      var _this4 = this;

	      this.currentStepIndex++;

	      if (this.currentStepIndex + 1 === this.steps.length && !this.finalStep && !this.onEvents) {
	        setTimeout(function () {
	          _this4.layout.nextBtn.textContent = main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE");
	        }, 200);
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "reduceCurrentStepIndex",
	    value: function reduceCurrentStepIndex() {
	      var _this5 = this;

	      if (this.currentStepIndex === 0) {
	        return;
	      }

	      if (this.currentStepIndex < this.steps.length && !this.finalStep) {
	        setTimeout(function () {
	          _this5.layout.nextBtn.textContent = main_core.Loc.getMessage("JS_UI_TOUR_BUTTON");
	        }, 200);
	      }

	      this.currentStepIndex--;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this6 = this;

	      if (!this.popup) {
	        var _this$getCurrentStep$4;

	        var bindElement = this.getCurrentStep() ? this.getCurrentStep().getTarget() : window;
	        var className = 'popup-window-ui-tour popup-window-ui-tour-opacity';

	        if (this.getCurrentStep().getCondition()) {
	          var _this$getCurrentStep$2;

	          if (main_core.Type.isString(this.getCurrentStep().getCondition())) {
	            className = className + ' --condition-' + this.getCurrentStep().getCondition().toLowerCase();
	          }

	          if (main_core.Type.isObject(this.getCurrentStep().getCondition())) {
	            var _this$getCurrentStep$;

	            className = className + ' --condition-' + ((_this$getCurrentStep$ = this.getCurrentStep().getCondition()) === null || _this$getCurrentStep$ === void 0 ? void 0 : _this$getCurrentStep$.color.toLowerCase());
	          }

	          if (((_this$getCurrentStep$2 = this.getCurrentStep().getCondition()) === null || _this$getCurrentStep$2 === void 0 ? void 0 : _this$getCurrentStep$2.top) !== false) {
	            className = className + ' --condition';
	          }
	        }

	        this.onEvents ? className = className + ' popup-window-ui-tour-animate' : null;
	        var buttons = [];

	        if (this.getCurrentStep() && this.getCurrentStep().getButtons().length > 0) {
	          this.getCurrentStep().getButtons().forEach(function (item) {
	            buttons.push(new main_popup.PopupWindowButton({
	              text: item.text,
	              className: 'ui-btn ui-btn-sm ui-btn-primary ui-btn-round',
	              events: {
	                click: main_core.Type.isFunction(item.event) ? item.event : null
	              }
	            }));
	          });
	        }

	        var popupWidth = this.onEvents ? 280 : 420;
	        this.popup = new main_popup.Popup({
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
	            onPopupClose: function onPopupClose(popup) {
	              if (popup.destroyed === false && _this6.onEvents) main_core_events.EventEmitter.emit('UI.Tour.Guide:onPopupClose', _this6);

	              _this6.close();
	            }
	          },
	          buttons: buttons
	        });
	        var conditionNodeTop = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup-condition-top\">\n\t\t\t\t\t<div class=\"ui-tour-popup-condition-angle\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	        var conditionNodeBottom = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup-condition-bottom\"></div>\n\t\t\t"])));

	        if (main_core.Type.isString(this.getCurrentStep().getCondition())) {
	          main_core.Dom.append(conditionNodeTop, this.popup.getContentContainer());
	        }

	        if (main_core.Type.isObject(this.getCurrentStep().getCondition())) {
	          var _this$getCurrentStep$3;

	          if (((_this$getCurrentStep$3 = this.getCurrentStep().getCondition()) === null || _this$getCurrentStep$3 === void 0 ? void 0 : _this$getCurrentStep$3.top) !== false) {
	            main_core.Dom.append(conditionNodeTop, this.popup.getContentContainer());
	          }
	        }

	        if (((_this$getCurrentStep$4 = this.getCurrentStep().getCondition()) === null || _this$getCurrentStep$4 === void 0 ? void 0 : _this$getCurrentStep$4.bottom) !== false) {
	          main_core.Dom.append(conditionNodeBottom, this.popup.getContentContainer());
	        }
	      }

	      return this.popup;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getContent",
	    value: function getContent() {
	      if (!this.layout.content) {
	        var linkNode = '';

	        if (this.getCurrentStep().getLink() || this.getCurrentStep().getArticle()) {
	          linkNode = this.getLink();
	        }

	        this.layout.content = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup ", " ", "\" >\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-tour-popup-content\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-tour-popup-footer\">\n\t\t\t\t\t\t<div class=\"ui-tour-popup-index\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), this.simpleMode ? 'ui-tour-popup-simple' : '', this.onEvents ? 'ui-tour-popup-events' : '', this.getTitle(), this.getText(), linkNode, linkNode, this.onEvents ? '' : this.getCounterItems(), this.onEvents ? '' : this.getCurrentCounter(), this.onEvents ? '' : this.getBtnContainer());
	      }

	      return this.layout.content;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setPopupData",
	    value: function setPopupData() {
	      main_core.Event.unbindAll(this.layout.link, 'click');
	      this.getTitle().innerHTML = this.getCurrentStep().getTitle();
	      this.getText().innerHTML = this.getCurrentStep().getText();

	      if (this.getCurrentStep().getArticle() || this.getCurrentStep().getLink()) {
	        main_core.Dom.removeClass(this.layout.link, "ui-tour-popup-link-hide");

	        if (this.getCurrentStep().getArticle()) {
	          main_core.Event.bind(this.layout.link, "click", this.handleClickLink.bind(this));
	        }

	        if (this.getCurrentStep().getLink()) {
	          this.getLink().setAttribute('href', this.getCurrentStep().getLink());
	        }
	      } else {
	        main_core.Dom.addClass(this.layout.link, "ui-tour-popup-link-hide");
	      }

	      this.getCurrentCounter().textContent = main_core.Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT").replace('#NUMBER#', this.currentStepIndex + 1).replace('#NUMBER_TOTAL#', this.steps.length);

	      for (var i = 0; i < this.steps.length; i++) {
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

	  }, {
	    key: "handleClickLink",
	    value: function handleClickLink() {
	      var _this7 = this;

	      event.preventDefault();

	      if (!this.helper) {
	        this.helper = top.BX.Helper;
	      }

	      this.helper.show("redirect=detail&code=" + this.getCurrentStep().getArticle());

	      if (this.onEvent) {
	        if (this.helper.isOpen()) this.getPopup().setAutoHide(false);
	        main_core_events.EventEmitter.subscribe(this.helper.getSlider(), 'SidePanel.Slider:onCloseComplete', function () {
	          _this7.getPopup().setAutoHide(true);
	        });
	      }
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      if (this.layout.title === null) {
	        this.layout.title = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup-title\"></div>\n\t\t\t"])));
	      }

	      return this.layout.title;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getText",
	    value: function getText() {
	      if (this.layout.text === null) {
	        this.layout.text = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup-text\"></div>\n\t\t\t"])));
	      }

	      return this.layout.text;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getLink",
	    value: function getLink() {
	      if (!this.layout.link) {
	        this.layout.link = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a target=\"_blank\" href=\"\" class=\"ui-tour-popup-link\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t"])), main_core.Loc.getMessage("JS_UI_TOUR_LINK"));
	      }

	      return this.layout.link;
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getCurrentCounter",
	    value: function getCurrentCounter() {
	      if (this.layout.currentCounter === null) {
	        this.layout.currentCounter = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-tour-popup-counter\">\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t"])), main_core.Loc.getMessage("JS_UI_TOUR_STEP_INDEX_TEXT").replace('#NUMBER#', this.currentStepIndex + 1).replace('#NUMBER_TOTAL#', this.steps.length));
	      }

	      return this.layout.currentCounter;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getBtnContainer",
	    value: function getBtnContainer() {
	      if (this.layout.btnContainer === null) {
	        this.layout.btnContainer = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup-btn-block\"></div>\n\t\t\t"])));
	        this.layout.nextBtn = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button id=\"next\" class=\"ui-tour-popup-btn-next\">\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"])), this.simpleMode ? main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_SIMPLE") : main_core.Loc.getMessage("JS_UI_TOUR_BUTTON"));
	        this.layout.backBtn = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button id=\"back\" class=\"ui-tour-popup-btn-back\">\n\t\t\t\t</button>\n\t\t\t"])));
	        main_core.Dom.append(this.layout.backBtn, this.layout.btnContainer);
	        main_core.Dom.append(this.layout.nextBtn, this.layout.btnContainer);
	        main_core.Event.bind(this.layout.nextBtn, "click", this.handleClickOnNextBtn.bind(this));
	        main_core.Event.bind(this.layout.backBtn, "click", this.handleClickOnBackBtn.bind(this));
	      }

	      return this.layout.btnContainer;
	    }
	  }, {
	    key: "getCounterItems",
	    value: function getCounterItems() {
	      if (this.layout.counter === null) {
	        this.layout.counter = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-tour-popup-index-items\">\n\t\t\t\t</span>\n\t\t\t"])));
	      }

	      this.layout.counterItems = [];

	      for (var i = 0; i < this.steps.length; i++) {
	        var currentStepIndex = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"ui-tour-popup-index-item\">\n\t\t\t\t</span>\n\t\t\t"])));
	        this.layout.counterItems.push(currentStepIndex);
	        main_core.Dom.append(currentStepIndex, this.layout.counter);
	      }

	      return this.layout.counter;
	    }
	    /**
	     * @returns {Step}
	     */

	  }, {
	    key: "getCurrentStep",
	    value: function getCurrentStep() {
	      return this.steps[this.currentStepIndex];
	    }
	    /**
	     * @returns {Step}
	     */

	  }, {
	    key: "getPreviousStep",
	    value: function getPreviousStep() {
	      if (this.steps[this.currentStepIndex - 1]) {
	        return this.steps[this.currentStepIndex - 1];
	      }
	    }
	  }, {
	    key: "handleClickOnNextBtn",
	    value: function handleClickOnNextBtn() {
	      var _this8 = this;

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
	        setTimeout(function () {
	          _this8.showStep();
	        }, 200);

	        if (main_core.Dom.hasClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden')) {
	          main_core.Dom.removeClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden');
	        }
	      }
	    }
	  }, {
	    key: "handleClickOnBackBtn",
	    value: function handleClickOnBackBtn() {
	      var _this9 = this;

	      main_core.Dom.addClass(this.layout.element, "ui-tour-overlay-element-opacity");
	      main_core.Dom.addClass(this.popup.getPopupContainer(), "popup-window-ui-tour-opacity");
	      this.closeStep();
	      this.reduceCurrentStepIndex();

	      if (this.currentStepIndex === 0) {
	        main_core.Dom.addClass(this.layout.backBtn, 'ui-tour-popup-btn-hidden');
	      }

	      this.clickOnBackBtn = true;
	      setTimeout(function () {
	        _this9.layout.backBtn.style.display = "none";

	        _this9.showStep();
	      }, 200);

	      if (this.getCurrentStep().getTarget()) {
	        main_core.Dom.addClass(this.getCurrentStep().getTarget(), 'ui-tour-selector');
	      }
	    }
	  }, {
	    key: "setFinalStep",
	    value: function setFinalStep() {
	      this.layout.element.style.display = "none";
	      this.getPopup().destroy();
	      var finalPopup = this.getFinalPopup();
	      finalPopup.show();
	      main_core.Dom.addClass(finalPopup.getPopupContainer(), "popup-window-ui-tour-final-show");
	    }
	    /**
	     * @public
	     */

	  }, {
	    key: "getFinalPopup",
	    value: function getFinalPopup() {
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
	  }, {
	    key: "getFinalContent",
	    value: function getFinalContent() {
	      if (!this.layout.finalContent) {
	        this.layout.finalContent = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-popup\">\n\t\t\t\t\t<div class=\"ui-tour-popup-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-tour-popup-content\">\n\t\t\t\t\t\t<div class=\"ui-tour-popup-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-tour-popup-footer-btn\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), this.finalTitle, this.finalText, this.getFinalBtn());
	      }

	      return this.layout.finalContent;
	    }
	  }, {
	    key: "getFinalBtn",
	    value: function getFinalBtn() {
	      var buttons = [];

	      if (this.buttons !== "") {
	        for (var i = 0; i < this.buttons.length; i++) {
	          var btn = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<button class=\"", "\" onclick=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t\t</button>\n\t\t\t\t"])), this.buttons[i]["class"], this.buttons[i].events.click, this.buttons[i].text);
	          buttons.push(btn);
	        }
	      } else {
	        var _btn = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button class=\"ui-btn ui-btn-sm ui-btn-primary ui-btn-round\" onclick=\"", "\">\n\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"])), this.close.bind(this), main_core.Loc.getMessage("JS_UI_TOUR_BUTTON_CLOSE"));

	        buttons.push(_btn);
	      }

	      return buttons;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "isTargetVisible",
	    value: function isTargetVisible(node) {
	      return node.top >= 0 && node.left >= 0 && node.bottom <= (window.innerHeight || document.documentElement.clientHeight) && node.right <= (window.innerWidth || document.documentElement.clientWidth);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "scrollToTarget",
	    value: function scrollToTarget(target) {
	      window.scrollTo(0, target.y - this.getAreaPadding());
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "showCursor",
	    value: function showCursor() {
	      var _this10 = this;

	      this.setCursorPos();
	      setTimeout(function () {
	        _this10.animateCursor();
	      }, 1000);
	    }
	  }, {
	    key: "getCursor",
	    value: function getCursor() {
	      var _this11 = this;

	      if (!this.layout.cursor) {
	        this.layout.cursor = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-tour-cursor\"></div>\n\t\t\t"])));
	        main_core.Event.bind(this.layout.cursor, 'transitionend', function () {
	          _this11.getCurrentStep().initTargetEvent();
	        });
	        main_core.Dom.append(this.layout.cursor, document.body);
	      }

	      return this.layout.cursor;
	    }
	  }, {
	    key: "setCursorPos",
	    value: function setCursorPos() {
	      var targetPos = this.getCurrentStep().getTargetPos();
	      var left = targetPos.left + targetPos.width / 2;

	      if (left < 0) {
	        left = 0;
	      }

	      this.cursorPaddingTop = 30;
	      var top = targetPos.bottom + this.cursorPaddingTop;

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
	  }, {
	    key: "animateCursor",
	    value: function animateCursor() {
	      var adjustment = this.cursorPaddingTop + this.getCurrentStep().getTargetPos().height / 2;
	      this.layout.cursor.style.transform = 'translateY(-' + adjustment + 'px)';
	    }
	  }], [{
	    key: "getFullEventName",
	    value: function getFullEventName(shortName) {
	      return "UI.Tour.Guide:" + shortName;
	    }
	  }]);
	  return Guide;
	}(main_core.Event.EventEmitter);
	babelHelpers.defineProperty(Guide, "ConditionColor", GuideConditionColor);

	var Manager = /*#__PURE__*/function () {
	  function Manager() {
	    babelHelpers.classCallCheck(this, Manager);
	    this.guides = new Map();
	    this.autoStartQueue = [];
	    this.currentGuide = null;
	  }

	  babelHelpers.createClass(Manager, [{
	    key: "create",
	    value: function create(options) {
	      options = main_core.Type.isPlainObject(options) ? options : {};
	      var id = options.id;

	      if (!main_core.Type.isString(id) && id !== '') {
	        throw new Error("'id' parameter is required.");
	      }

	      if (this.get(id)) {
	        throw new Error("The tour instance with the same 'id' already exists.");
	      }

	      var guide = new Guide(options);
	      this.guides.set(guide, true);
	      return guide;
	    }
	  }, {
	    key: "add",
	    value: function add(options) {
	      var _this = this;

	      var guide = this.create(options);
	      guide.subscribe("UI.Tour.Guide:onFinish", function () {
	        _this.handleTourFinish(guide);
	      });

	      if (!this.currentGuide) {
	        this.currentGuide = guide;
	        guide.start();
	      } else {
	        this.autoStartQueue.push(guide);
	      }
	    }
	    /**
	     * @public
	     * @param {string} id
	     * @returns {Guide|null}
	     */

	  }, {
	    key: "get",
	    value: function get(id) {
	      return this.guides.get(id);
	    }
	    /**
	     * @public
	     * @param {string} id
	     */

	  }, {
	    key: "remove",
	    value: function remove(id) {
	      this.guides["delete"](id);
	    }
	    /**
	     * @public
	     * @returns {Guide|null}
	     */

	  }, {
	    key: "getCurrentGuide",
	    value: function getCurrentGuide() {
	      return this.currentGuide;
	    }
	    /**
	     * @private
	     * @param {Guide} guide
	     */

	  }, {
	    key: "handleTourFinish",
	    value: function handleTourFinish(guide) {
	      this.currentGuide = null;
	      this.remove(guide.getId());
	      var autoStartGuide = this.autoStartQueue.shift();

	      if (autoStartGuide) {
	        this.currentGuide = autoStartGuide;
	        autoStartGuide.start();
	      }
	    }
	  }]);
	  return Manager;
	}();

	var manager = new Manager();

	exports.Guide = Guide;
	exports.Step = Step;
	exports.Manager = manager;

}((this.BX.UI.Tour = this.BX.UI.Tour || {}),BX.Main,BX.Event,BX,BX));
//# sourceMappingURL=tour.bundle.js.map
