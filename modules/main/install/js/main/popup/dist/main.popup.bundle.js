this.BX = this.BX || {};
(function (exports,main_core_zIndexManager,main_core_events,main_core) {
	'use strict';

	/**
	 * @memberOf BX.Main.Popup
	 * @deprecated use BX.UI.Button
	 */
	var Button = /*#__PURE__*/function () {
	  function Button(params) {
	    babelHelpers.classCallCheck(this, Button);
	    this.popupWindow = null;
	    this.params = params || {};
	    this.text = this.params.text || '';
	    this.id = this.params.id || '';
	    this.className = this.params.className || '';
	    this.events = this.params.events || {};
	    this.contextEvents = {};

	    for (var eventName in this.events) {
	      if (main_core.Type.isFunction(this.events[eventName])) {
	        this.contextEvents[eventName] = this.events[eventName].bind(this);
	      }
	    }

	    this.buttonNode = main_core.Dom.create('span', {
	      props: {
	        className: 'popup-window-button' + (this.className.length > 0 ? ' ' + this.className : ''),
	        id: this.id
	      },
	      events: this.contextEvents,
	      text: this.text
	    });
	  }

	  babelHelpers.createClass(Button, [{
	    key: "render",
	    value: function render() {
	      return this.buttonNode;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.buttonNode;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.text;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      this.text = name || '';

	      if (this.buttonNode) {
	        main_core.Dom.clean(this.buttonNode);
	        main_core.Dom.adjust(this.buttonNode, {
	          text: this.text
	        });
	      }
	    }
	  }, {
	    key: "setClassName",
	    value: function setClassName(className) {
	      if (this.buttonNode) {
	        if (main_core.Type.isString(this.className) && this.className !== '') {
	          main_core.Dom.removeClass(this.buttonNode, this.className);
	        }

	        main_core.Dom.addClass(this.buttonNode, className);
	      }

	      this.className = className;
	    }
	  }, {
	    key: "addClassName",
	    value: function addClassName(className) {
	      if (this.buttonNode) {
	        main_core.Dom.addClass(this.buttonNode, className);
	        this.className = this.buttonNode.className;
	      }
	    }
	  }, {
	    key: "removeClassName",
	    value: function removeClassName(className) {
	      if (this.buttonNode) {
	        main_core.Dom.removeClass(this.buttonNode, className);
	        this.className = this.buttonNode.className;
	      }
	    }
	  }]);
	  return Button;
	}();

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _left = /*#__PURE__*/new WeakMap();

	var _top = /*#__PURE__*/new WeakMap();

	var PositionEvent = /*#__PURE__*/function (_BaseEvent) {
	  babelHelpers.inherits(PositionEvent, _BaseEvent);

	  function PositionEvent() {
	    var _this;

	    babelHelpers.classCallCheck(this, PositionEvent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PositionEvent).call(this));

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _left, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _top, {
	      writable: true,
	      value: void 0
	    });

	    return _this;
	  }

	  babelHelpers.createClass(PositionEvent, [{
	    key: "left",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _left);
	    },
	    set: function set(value) {
	      if (main_core.Type.isNumber(value)) {
	        babelHelpers.classPrivateFieldSet(this, _left, value);
	      }
	    }
	  }, {
	    key: "top",
	    get: function get() {
	      return babelHelpers.classPrivateFieldGet(this, _top);
	    },
	    set: function set(value) {
	      if (main_core.Type.isNumber(value)) {
	        babelHelpers.classPrivateFieldSet(this, _top, value);
	      }
	    }
	  }]);
	  return PositionEvent;
	}(main_core_events.BaseEvent);

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9;
	var aliases = {
	  onPopupWindowInit: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onInit'
	  },
	  onPopupWindowIsInitialized: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onAfterInit'
	  },
	  onPopupFirstShow: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onFirstShow'
	  },
	  onPopupShow: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onShow'
	  },
	  onAfterPopupShow: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onAfterShow'
	  },
	  onPopupClose: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onClose'
	  },
	  onPopupAfterClose: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onAfterClose'
	  },
	  onPopupDestroy: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onDestroy'
	  },
	  onPopupFullscreenLeave: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onFullscreenLeave'
	  },
	  onPopupFullscreenEnter: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onFullscreenEnter'
	  },
	  onPopupDragStart: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onDragStart'
	  },
	  onPopupDrag: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onDrag'
	  },
	  onPopupDragEnd: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onDragEnd'
	  },
	  onPopupResizeStart: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onResizeStart'
	  },
	  onPopupResize: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onResize'
	  },
	  onPopupResizeEnd: {
	    namespace: 'BX.Main.Popup',
	    eventName: 'onResizeEnd'
	  }
	};
	main_core_events.EventEmitter.registerAliases(aliases);
	/**
	 * @memberof BX.Main
	 */

	var Popup = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Popup, _EventEmitter);
	  babelHelpers.createClass(Popup, null, [{
	    key: "setOptions",

	    /**
	     * @private
	     */

	    /**
	     * @private
	     */
	    value: function setOptions(options) {
	      if (!main_core.Type.isPlainObject(options)) {
	        return;
	      }

	      for (var option in options) {
	        this.options[option] = options[option];
	      }
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(option, defaultValue) {
	      if (!main_core.Type.isUndefined(this.options[option])) {
	        return this.options[option];
	      } else if (!main_core.Type.isUndefined(defaultValue)) {
	        return defaultValue;
	      } else {
	        return this.defaultOptions[option];
	      }
	    }
	  }]);

	  function Popup(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Popup);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Popup).call(this));

	    _this.setEventNamespace('BX.Main.Popup');

	    var _arguments = Array.prototype.slice.call(arguments),
	        popupId = _arguments[0],
	        bindElement = _arguments[1],
	        params = _arguments[2]; //compatible arguments


	    _this.compatibleMode = params && main_core.Type.isBoolean(params.compatibleMode) ? params.compatibleMode : true;

	    if (main_core.Type.isPlainObject(options) && !bindElement && !params) {
	      params = options;
	      popupId = options.id;
	      bindElement = options.bindElement;
	      _this.compatibleMode = false;
	    }

	    params = params || {};
	    _this.params = params;

	    if (!main_core.Type.isStringFilled(popupId)) {
	      popupId = 'popup-window-' + main_core.Text.getRandom().toLowerCase();
	    }

	    _this.emit('onInit', new main_core_events.BaseEvent({
	      compatData: [popupId, bindElement, params]
	    }));
	    /**
	     * @private
	     */


	    _this.uniquePopupId = popupId;
	    _this.params.zIndex = main_core.Type.isNumber(params.zIndex) ? parseInt(params.zIndex) : 0;
	    _this.params.zIndexAbsolute = main_core.Type.isNumber(params.zIndexAbsolute) ? parseInt(params.zIndexAbsolute) : 0;
	    _this.buttons = params.buttons && main_core.Type.isArray(params.buttons) ? params.buttons : [];
	    _this.offsetTop = Popup.getOption('offsetTop');
	    _this.offsetLeft = Popup.getOption('offsetLeft');
	    _this.firstShow = false;
	    _this.bordersWidth = 20;
	    _this.bindElementPos = null;
	    _this.closeIcon = null;
	    _this.resizeIcon = null;
	    _this.angle = null;
	    _this.angleArrowElement = null;
	    _this.overlay = null;
	    _this.titleBar = null;
	    _this.bindOptions = babelHelpers["typeof"](params.bindOptions) === 'object' ? params.bindOptions : {};
	    _this.autoHide = params.autoHide === true;
	    _this.autoHideHandler = main_core.Type.isFunction(params.autoHideHandler) ? params.autoHideHandler : null;
	    _this.handleAutoHide = _this.handleAutoHide.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleOverlayClick = _this.handleOverlayClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.isAutoHideBinded = false;
	    _this.closeByEsc = params.closeByEsc === true;
	    _this.isCloseByEscBinded = false;
	    _this.toFrontOnShow = true;
	    _this.cacheable = true;
	    _this.destroyed = false;
	    _this.width = null;
	    _this.height = null;
	    _this.minWidth = null;
	    _this.minHeight = null;
	    _this.maxWidth = null;
	    _this.maxHeight = null;
	    _this.padding = null;
	    _this.contentPadding = null;
	    _this.background = null;
	    _this.contentBackground = null;
	    _this.borderRadius = null;
	    _this.contentBorderRadius = null;
	    _this.targetContainer = main_core.Type.isElementNode(params.targetContainer) ? params.targetContainer : document.body;
	    _this.dragOptions = {
	      cursor: '',
	      callback: function callback() {},
	      eventName: ''
	    };
	    _this.dragged = false;
	    _this.dragPageX = 0;
	    _this.dragPageY = 0;
	    _this.animationShowClassName = null;
	    _this.animationCloseClassName = null;
	    _this.animationCloseEventType = null;
	    _this.handleDocumentMouseMove = _this.handleDocumentMouseMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleDocumentMouseUp = _this.handleDocumentMouseUp.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleDocumentKeyUp = _this.handleDocumentKeyUp.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleResizeWindow = _this.handleResizeWindow.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleResize = _this.handleResize.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleMove = _this.handleMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onTitleMouseDown = _this.onTitleMouseDown.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleFullScreen = _this.handleFullScreen.bind(babelHelpers.assertThisInitialized(_this));

	    _this.subscribeFromOptions(params.events);

	    var popupClassName = 'popup-window';

	    if (params.titleBar) {
	      popupClassName += ' popup-window-with-titlebar';
	    }

	    if (params.className && main_core.Type.isStringFilled(params.className)) {
	      popupClassName += ' ' + params.className;
	    }

	    if (params.darkMode) {
	      popupClassName += ' popup-window-dark';
	    }

	    if (params.titleBar) {
	      _this.titleBar = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"popup-window-titlebar\" id=\"popup-window-titlebar-", "\"></div>\n\t\t\t"])), popupId);
	    }

	    if (params.closeIcon) {
	      var className = 'popup-window-close-icon' + (params.titleBar ? ' popup-window-titlebar-close-icon' : '');
	      _this.closeIcon = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"", "\" onclick=\"", "\"></span>\n\t\t\t"])), className, _this.handleCloseIconClick.bind(babelHelpers.assertThisInitialized(_this)));

	      if (main_core.Type.isPlainObject(params.closeIcon)) {
	        main_core.Dom.style(_this.closeIcon, params.closeIcon);
	      }
	    }
	    /**
	     * @private
	     */


	    _this.contentContainer = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div id=\"popup-window-content-", "\" class=\"popup-window-content\"></div>"])), popupId);
	    /**
	     * @private
	     */

	    _this.popupContainer = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div\n\t\t\t\tclass=\"", "\"\n\t\t\t\tid=\"", "\"\n\t\t\t\tstyle=\"display: none; position: absolute; left: 0; top: 0;\"\n\t\t\t>", "</div>"])), popupClassName, popupId, [_this.titleBar, _this.contentContainer, _this.closeIcon]);

	    _this.targetContainer.appendChild(_this.popupContainer);

	    _this.zIndexComponent = main_core_zIndexManager.ZIndexManager.register(_this.popupContainer, params.zIndexOptions);
	    _this.buttonsContainer = null;

	    if (params.contentColor && main_core.Type.isStringFilled(params.contentColor)) {
	      if (params.contentColor === 'white' || params.contentColor === 'gray') {
	        popupClassName += ' popup-window-content-' + params.contentColor;
	      }

	      _this.setContentColor(params.contentColor);
	    }

	    if (params.angle) {
	      _this.setAngle(params.angle);
	    }

	    if (params.overlay) {
	      _this.setOverlay(params.overlay);
	    }

	    _this.setOffset(params);

	    _this.setBindElement(bindElement);

	    _this.setTitleBar(params.titleBar);

	    _this.setContent(params.content);

	    _this.setButtons(params.buttons);

	    _this.setWidth(params.width);

	    _this.setHeight(params.height);

	    _this.setMinWidth(params.minWidth);

	    _this.setMinHeight(params.minHeight);

	    _this.setMaxWidth(params.maxWidth);

	    _this.setMaxHeight(params.maxHeight);

	    _this.setResizeMode(params.resizable);

	    _this.setPadding(params.padding);

	    _this.setContentPadding(params.contentPadding);

	    _this.setBorderRadius(params.borderRadius);

	    _this.setContentBorderRadius(params.contentBorderRadius);

	    _this.setBackground(params.background);

	    _this.setContentBackground(params.contentBackground);

	    _this.setAnimation(params.animation);

	    _this.setCacheable(params.cacheable);

	    _this.setToFrontOnShow(params.toFrontOnShow); // Compatibility


	    if (params.contentNoPaddings) {
	      _this.setContentPadding(0);
	    }

	    if (params.noAllPaddings) {
	      _this.setPadding(0);

	      _this.setContentPadding(0);
	    }

	    if (params.bindOnResize !== false) {
	      main_core.Event.bind(window, 'resize', _this.handleResizeWindow);
	    }

	    _this.emit('onAfterInit', new main_core_events.BaseEvent({
	      compatData: [popupId, babelHelpers.assertThisInitialized(_this)]
	    }));

	    return _this;
	  }
	  /**
	   * @private
	   */


	  babelHelpers.createClass(Popup, [{
	    key: "subscribeFromOptions",
	    value: function subscribeFromOptions(events) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Popup.prototype), "subscribeFromOptions", this).call(this, events, aliases);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.uniquePopupId;
	    }
	  }, {
	    key: "isCompatibleMode",
	    value: function isCompatibleMode() {
	      return this.compatibleMode;
	    }
	  }, {
	    key: "setContent",
	    value: function setContent(content) {
	      if (!this.contentContainer || !content) {
	        return;
	      }

	      if (main_core.Type.isElementNode(content)) {
	        main_core.Dom.clean(this.contentContainer);
	        var hasParent = main_core.Type.isDomNode(content.parentNode);
	        this.contentContainer.appendChild(content);

	        if (this.isCompatibleMode() || hasParent) {
	          content.style.display = 'block';
	        }
	      } else if (main_core.Type.isString(content)) {
	        this.contentContainer.innerHTML = content;
	      } else {
	        this.contentContainer.innerHTML = '&nbsp;';
	      }
	    }
	  }, {
	    key: "setButtons",
	    value: function setButtons(buttons) {
	      this.buttons = buttons && main_core.Type.isArray(buttons) ? buttons : [];

	      if (this.buttonsContainer) {
	        main_core.Dom.remove(this.buttonsContainer);
	      }

	      var ButtonClass = main_core.Reflection.getClass('BX.UI.Button');

	      if (this.buttons.length > 0 && this.contentContainer) {
	        var newButtons = [];

	        for (var i = 0; i < this.buttons.length; i++) {
	          var button = this.buttons[i];

	          if (button instanceof Button) {
	            button.popupWindow = this;
	            newButtons.push(button.render());
	          } else if (ButtonClass && button instanceof ButtonClass) {
	            button.setContext(this);
	            newButtons.push(button.render());
	          }
	        }

	        this.buttonsContainer = this.contentContainer.parentNode.appendChild(main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"popup-window-buttons\">", "</div>"])), newButtons));
	      }
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      return this.buttons;
	    }
	  }, {
	    key: "getButton",
	    value: function getButton(id) {
	      for (var i = 0; i < this.buttons.length; i++) {
	        var button = this.buttons[i];

	        if (button.getId() === id) {
	          return button;
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "setBindElement",
	    value: function setBindElement(bindElement) {
	      if (bindElement === null) {
	        this.bindElement = null;
	      } else if (babelHelpers["typeof"](bindElement) === 'object') {
	        if (main_core.Type.isDomNode(bindElement) || main_core.Type.isNumber(bindElement.top) && main_core.Type.isNumber(bindElement.left)) {
	          this.bindElement = bindElement;
	        } else if (main_core.Type.isNumber(bindElement.clientX) && main_core.Type.isNumber(bindElement.clientY)) {
	          this.bindElement = {
	            left: bindElement.pageX,
	            top: bindElement.pageY,
	            bottom: bindElement.pageY
	          };
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getBindElementPos",
	    value: function getBindElementPos(bindElement) {
	      if (main_core.Type.isDomNode(bindElement)) {
	        if (this.isTargetDocumentBody()) {
	          return main_core.Dom.getPosition(bindElement);
	        } else {
	          return this.getPositionRelativeToTarget(bindElement);
	        }
	      } else if (bindElement && babelHelpers["typeof"](bindElement) === 'object') {
	        if (!main_core.Type.isNumber(bindElement.bottom)) {
	          bindElement.bottom = bindElement.top;
	        }

	        return bindElement;
	      } else {
	        var windowSize = this.getWindowSize();
	        var windowScroll = this.getWindowScroll();
	        var popupWidth = this.getPopupContainer().offsetWidth;
	        var popupHeight = this.getPopupContainer().offsetHeight;
	        this.bindOptions.forceTop = true;
	        return {
	          left: windowSize.innerWidth / 2 - popupWidth / 2 + windowScroll.scrollLeft,
	          top: windowSize.innerHeight / 2 - popupHeight / 2 + windowScroll.scrollTop,
	          bottom: windowSize.innerHeight / 2 - popupHeight / 2 + windowScroll.scrollTop,
	          //for optimisation purposes
	          windowSize: windowSize,
	          windowScroll: windowScroll,
	          popupWidth: popupWidth,
	          popupHeight: popupHeight
	        };
	      }
	    }
	    /**
	     * @internal
	     */

	  }, {
	    key: "getPositionRelativeToTarget",
	    value: function getPositionRelativeToTarget(element) {
	      var offsetLeft = element.offsetLeft;
	      var offsetTop = element.offsetTop;
	      var offsetElement = element.offsetParent;

	      while (offsetElement && offsetElement !== this.getTargetContainer()) {
	        offsetLeft += offsetElement.offsetLeft;
	        offsetTop += offsetElement.offsetTop;
	        offsetElement = offsetElement.offsetParent;
	      }

	      var elementRect = element.getBoundingClientRect();
	      return new DOMRect(offsetLeft, offsetTop, elementRect.width, elementRect.height);
	    } // private

	  }, {
	    key: "getWindowSize",
	    value: function getWindowSize() {
	      if (this.isTargetDocumentBody()) {
	        return {
	          innerWidth: window.innerWidth,
	          innerHeight: window.innerHeight
	        };
	      } else {
	        return {
	          innerWidth: this.getTargetContainer().offsetWidth,
	          innerHeight: this.getTargetContainer().offsetHeight
	        };
	      }
	    } // private

	  }, {
	    key: "getWindowScroll",
	    value: function getWindowScroll() {
	      if (this.isTargetDocumentBody()) {
	        return {
	          scrollLeft: window.pageXOffset,
	          scrollTop: window.pageYOffset
	        };
	      } else {
	        return {
	          scrollLeft: this.getTargetContainer().scrollLeft,
	          scrollTop: this.getTargetContainer().scrollTop
	        };
	      }
	    }
	  }, {
	    key: "setAngle",
	    value: function setAngle(params) {
	      if (params === false) {
	        if (this.angle !== null) {
	          main_core.Dom.remove(this.angle.element);
	        }

	        this.angle = null;
	        this.angleArrowElement = null;
	        return;
	      }

	      var className = 'popup-window-angly';

	      if (this.angle === null) {
	        var position = this.bindOptions.position && this.bindOptions.position === 'top' ? 'bottom' : 'top';
	        var angleMinLeft = Popup.getOption(position === 'top' ? 'angleMinTop' : 'angleMinBottom');
	        var defaultOffset = main_core.Type.isNumber(params.offset) ? params.offset : 0;
	        var angleLeftOffset = Popup.getOption('angleLeftOffset', null);

	        if (defaultOffset > 0 && main_core.Type.isNumber(angleLeftOffset)) {
	          defaultOffset += angleLeftOffset - Popup.defaultOptions.angleLeftOffset;
	        }

	        this.angleArrowElement = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"popup-window-angly--arrow\"></div>"])));

	        if (this.background) {
	          this.angleArrowElement.style.background = this.background;
	        }

	        this.angle = {
	          element: main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"", " ", "-", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), className, className, position, this.angleArrowElement),
	          position: position,
	          offset: 0,
	          defaultOffset: Math.max(defaultOffset, angleMinLeft) //Math.max(Type.isNumber(params.offset) ? params.offset : 0, angleMinLeft)

	        };
	        this.getPopupContainer().appendChild(this.angle.element);
	      }

	      if (babelHelpers["typeof"](params) === 'object' && params.position && ['top', 'right', 'bottom', 'left', 'hide'].includes(params.position)) {
	        main_core.Dom.removeClass(this.angle.element, className + '-' + this.angle.position);
	        main_core.Dom.addClass(this.angle.element, className + '-' + params.position);
	        this.angle.position = params.position;
	      }

	      if (babelHelpers["typeof"](params) === 'object' && main_core.Type.isNumber(params.offset)) {
	        var offset = params.offset;
	        var minOffset, maxOffset;

	        if (this.angle.position === 'top') {
	          minOffset = Popup.getOption('angleMinTop');
	          maxOffset = this.getPopupContainer().offsetWidth - Popup.getOption('angleMaxTop');
	          maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;
	          this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
	          this.angle.element.style.left = this.angle.offset + 'px';
	          this.angle.element.style.marginLeft = 0;
	          this.angle.element.style.removeProperty('top');
	        } else if (this.angle.position === 'bottom') {
	          minOffset = Popup.getOption('angleMinBottom');
	          maxOffset = this.getPopupContainer().offsetWidth - Popup.getOption('angleMaxBottom');
	          maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;
	          this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
	          this.angle.element.style.marginLeft = this.angle.offset + 'px';
	          this.angle.element.style.left = 0;
	          this.angle.element.style.removeProperty('top');
	        } else if (this.angle.position === 'right') {
	          minOffset = Popup.getOption('angleMinRight');
	          maxOffset = this.getPopupContainer().offsetHeight - Popup.getOption('angleMaxRight');
	          maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;
	          this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
	          this.angle.element.style.top = this.angle.offset + 'px';
	          this.angle.element.style.removeProperty('left');
	          this.angle.element.style.removeProperty('margin-left');
	        } else if (this.angle.position === 'left') {
	          minOffset = Popup.getOption('angleMinLeft');
	          maxOffset = this.getPopupContainer().offsetHeight - Popup.getOption('angleMaxLeft');
	          maxOffset = maxOffset < minOffset ? Math.max(minOffset, offset) : maxOffset;
	          this.angle.offset = Math.min(Math.max(minOffset, offset), maxOffset);
	          this.angle.element.style.top = this.angle.offset + 'px';
	          this.angle.element.style.removeProperty('left');
	          this.angle.element.style.removeProperty('margin-left');
	        }
	      }
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      this.setWidthProperty('width', width);
	    }
	  }, {
	    key: "getHeight",
	    value: function getHeight() {
	      return this.height;
	    }
	  }, {
	    key: "setHeight",
	    value: function setHeight(height) {
	      this.setHeightProperty('height', height);
	    }
	  }, {
	    key: "getMinWidth",
	    value: function getMinWidth() {
	      return this.minWidth;
	    }
	  }, {
	    key: "setMinWidth",
	    value: function setMinWidth(width) {
	      this.setWidthProperty('minWidth', width);
	    }
	  }, {
	    key: "getMinHeight",
	    value: function getMinHeight() {
	      return this.minHeight;
	    }
	  }, {
	    key: "setMinHeight",
	    value: function setMinHeight(height) {
	      this.setHeightProperty('minHeight', height);
	    }
	  }, {
	    key: "getMaxWidth",
	    value: function getMaxWidth() {
	      return this.maxWidth;
	    }
	  }, {
	    key: "setMaxWidth",
	    value: function setMaxWidth(width) {
	      this.setWidthProperty('maxWidth', width);
	    }
	  }, {
	    key: "getMaxHeight",
	    value: function getMaxHeight() {
	      return this.maxHeight;
	    }
	  }, {
	    key: "setMaxHeight",
	    value: function setMaxHeight(height) {
	      this.setHeightProperty('maxHeight', height);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setWidthProperty",
	    value: function setWidthProperty(property, width) {
	      var props = ['width', 'minWidth', 'maxWidth'];

	      if (props.indexOf(property) === -1) {
	        return;
	      }

	      if (main_core.Type.isNumber(width) && width >= 0) {
	        this[property] = width;
	        this.getResizableContainer().style[property] = width + 'px';
	        this.getContentContainer().style.overflowX = 'auto';
	        this.getPopupContainer().classList.add('popup-window-fixed-width');

	        if (this.getTitleContainer() && main_core.Browser.isIE11()) {
	          this.getTitleContainer().style[property] = width + 'px';
	        }
	      } else if (width === null || width === false) {
	        this[property] = null;
	        this.getResizableContainer().style.removeProperty(main_core.Text.toKebabCase(property));
	        var hasOtherProps = props.some(function (prop) {
	          return this.getResizableContainer().style.getPropertyValue(main_core.Text.toKebabCase(prop)) !== '';
	        }, this);

	        if (!hasOtherProps) {
	          this.getContentContainer().style.removeProperty('overflow-x');
	          this.getPopupContainer().classList.remove('popup-window-fixed-width');
	        }

	        if (this.getTitleContainer() && main_core.Browser.isIE11()) {
	          this.getTitleContainer().style.removeProperty(main_core.Text.toKebabCase(property));
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "setHeightProperty",
	    value: function setHeightProperty(property, height) {
	      var props = ['height', 'minHeight', 'maxHeight'];

	      if (props.indexOf(property) === -1) {
	        return;
	      }

	      if (main_core.Type.isNumber(height) && height >= 0) {
	        this[property] = height;
	        this.getResizableContainer().style[property] = height + 'px';
	        this.getContentContainer().style.overflowY = 'auto';
	        this.getPopupContainer().classList.add('popup-window-fixed-height');
	      } else if (height === null || height === false) {
	        this[property] = null;
	        this.getResizableContainer().style.removeProperty(main_core.Text.toKebabCase(property));
	        var hasOtherProps = props.some(function (prop) {
	          return this.getResizableContainer().style.getPropertyValue(main_core.Text.toKebabCase(prop)) !== '';
	        }, this);

	        if (!hasOtherProps) {
	          this.getContentContainer().style.removeProperty('overflow-y');
	          this.getPopupContainer().classList.remove('popup-window-fixed-height');
	        }
	      }
	    }
	  }, {
	    key: "setPadding",
	    value: function setPadding(padding) {
	      if (main_core.Type.isNumber(padding) && padding >= 0) {
	        this.padding = padding;
	        this.getPopupContainer().style.padding = padding + 'px';
	      } else if (padding === null) {
	        this.padding = null;
	        this.getPopupContainer().style.removeProperty('padding');
	      }
	    }
	  }, {
	    key: "getPadding",
	    value: function getPadding() {
	      return this.padding;
	    }
	  }, {
	    key: "setContentPadding",
	    value: function setContentPadding(padding) {
	      if (main_core.Type.isNumber(padding) && padding >= 0) {
	        this.contentPadding = padding;
	        this.getContentContainer().style.padding = padding + 'px';
	      } else if (padding === null) {
	        this.contentPadding = null;
	        this.getContentContainer().style.removeProperty('padding');
	      }
	    }
	  }, {
	    key: "getContentPadding",
	    value: function getContentPadding() {
	      return this.contentPadding;
	    }
	  }, {
	    key: "setBorderRadius",
	    value: function setBorderRadius(radius) {
	      if (main_core.Type.isStringFilled(radius)) {
	        this.borderRadius = radius;
	        this.getPopupContainer().style.setProperty('--popup-window-border-radius', radius);
	      } else if (radius === null) {
	        this.borderRadius = null;
	        this.getPopupContainer().style.removeProperty('--popup-window-border-radius');
	      }
	    }
	  }, {
	    key: "setContentBorderRadius",
	    value: function setContentBorderRadius(radius) {
	      if (main_core.Type.isStringFilled(radius)) {
	        this.contentBorderRadius = radius;
	        this.getContentContainer().style.setProperty('--popup-window-content-border-radius', radius);
	      } else if (radius === null) {
	        this.contentBorderRadius = null;
	        this.getContentContainer().style.removeProperty('--popup-window-content-border-radius');
	      }
	    }
	  }, {
	    key: "setContentColor",
	    value: function setContentColor(color) {
	      if (main_core.Type.isString(color) && this.contentContainer) {
	        this.contentContainer.style.backgroundColor = color;
	      } else if (color === null) {
	        this.contentContainer.style.style.removeProperty('background-color');
	      }
	    }
	  }, {
	    key: "setBackground",
	    value: function setBackground(background) {
	      if (main_core.Type.isStringFilled(background)) {
	        this.background = background;
	        this.getPopupContainer().style.background = background;

	        if (this.angleArrowElement) {
	          this.angleArrowElement.style.background = background;
	        }
	      } else if (background === null) {
	        this.background = null;
	        this.getPopupContainer().style.removeProperty('background');

	        if (this.angleArrowElement) {
	          this.angleArrowElement.style.removeProperty('background');
	        }
	      }
	    }
	  }, {
	    key: "getBackground",
	    value: function getBackground() {
	      return this.background;
	    }
	  }, {
	    key: "setContentBackground",
	    value: function setContentBackground(background) {
	      if (main_core.Type.isStringFilled(background)) {
	        this.contentBackground = background;
	        this.getContentContainer().style.background = background;
	      } else if (background === null) {
	        this.contentBackground = null;
	        this.getContentContainer().style.removeProperty('background');
	      }
	    }
	  }, {
	    key: "getContentBackground",
	    value: function getContentBackground() {
	      return this.contentBackground;
	    }
	  }, {
	    key: "isDestroyed",
	    value: function isDestroyed() {
	      return this.destroyed;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(cacheable) {
	      this.cacheable = cacheable !== false;
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "setToFrontOnShow",
	    value: function setToFrontOnShow(flag) {
	      this.toFrontOnShow = flag !== false;
	    }
	  }, {
	    key: "shouldFrontOnShow",
	    value: function shouldFrontOnShow() {
	      return this.toFrontOnShow;
	    }
	  }, {
	    key: "setResizeMode",
	    value: function setResizeMode(mode) {
	      if (mode === true || main_core.Type.isPlainObject(mode)) {
	        if (!this.resizeIcon) {
	          this.resizeIcon = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"popup-window-resize\" onmousedown=\"", "\"></div>\n\t\t\t\t"])), this.handleResizeMouseDown.bind(this));
	          this.getPopupContainer().appendChild(this.resizeIcon);
	        } //Compatibility


	        this.setMinWidth(mode.minWidth);
	        this.setMinHeight(mode.minHeight);
	      } else if (mode === false && this.resizeIcon) {
	        main_core.Dom.remove(this.resizeIcon);
	        this.resizeIcon = null;
	      }
	    }
	  }, {
	    key: "getTargetContainer",
	    value: function getTargetContainer() {
	      return this.targetContainer;
	    }
	  }, {
	    key: "isTargetDocumentBody",
	    value: function isTargetDocumentBody() {
	      return this.getTargetContainer() === document.body;
	    }
	  }, {
	    key: "getPopupContainer",
	    value: function getPopupContainer() {
	      return this.popupContainer;
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      return this.contentContainer;
	    }
	  }, {
	    key: "getResizableContainer",
	    value: function getResizableContainer() {
	      return main_core.Browser.isIE11() ? this.getContentContainer() : this.getPopupContainer();
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.titleBar;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "onTitleMouseDown",
	    value: function onTitleMouseDown(event) {
	      this._startDrag(event, {
	        cursor: 'move',
	        callback: this.handleMove,
	        eventName: 'Drag'
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleResizeMouseDown",
	    value: function handleResizeMouseDown(event) {
	      this._startDrag(event, {
	        cursor: 'nwse-resize',
	        eventName: 'Resize',
	        callback: this.handleResize
	      });

	      if (this.isTargetDocumentBody()) {
	        this.resizeContentPos = main_core.Dom.getPosition(this.getResizableContainer());
	        this.resizeContentOffset = this.resizeContentPos.left - main_core.Dom.getPosition(this.getPopupContainer()).left;
	      } else {
	        this.resizeContentPos = this.getPositionRelativeToTarget(this.getResizableContainer());
	        this.resizeContentOffset = this.resizeContentPos.left - this.getPositionRelativeToTarget(this.getPopupContainer()).left;
	      }

	      this.resizeContentPos.offsetX = 0;
	      this.resizeContentPos.offsetY = 0;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleResize",
	    value: function handleResize(offsetX, offsetY, pageX, pageY) {
	      this.resizeContentPos.offsetX += offsetX;
	      this.resizeContentPos.offsetY += offsetY;
	      var width = this.resizeContentPos.width + this.resizeContentPos.offsetX;
	      var height = this.resizeContentPos.height + this.resizeContentPos.offsetY;
	      var scrollWidth = this.isTargetDocumentBody() ? document.documentElement.scrollWidth : this.getTargetContainer().scrollWidth;

	      if (this.resizeContentPos.left + width + this.resizeContentOffset >= scrollWidth) {
	        width = scrollWidth - this.resizeContentPos.left - this.resizeContentOffset;
	      }

	      width = Math.max(width, this.getMinWidth());
	      height = Math.max(height, this.getMinHeight());

	      if (this.getMaxWidth() !== null) {
	        width = Math.min(width, this.getMaxWidth());
	      }

	      if (this.getMaxHeight() !== null) {
	        height = Math.min(height, this.getMaxHeight());
	      }

	      this.setWidth(width);
	      this.setHeight(height);
	    }
	  }, {
	    key: "isTopAngle",
	    value: function isTopAngle() {
	      return this.angle !== null && this.angle.position === 'top';
	    }
	  }, {
	    key: "isBottomAngle",
	    value: function isBottomAngle() {
	      return this.angle !== null && this.angle.position === 'bottom';
	    }
	  }, {
	    key: "isTopOrBottomAngle",
	    value: function isTopOrBottomAngle() {
	      return this.angle !== null && (this.angle.position === 'top' || this.angle.position === 'bottom');
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getAngleHeight",
	    value: function getAngleHeight() {
	      return this.isTopOrBottomAngle() ? Popup.getOption('angleTopOffset') : 0;
	    }
	  }, {
	    key: "setOffset",
	    value: function setOffset(params) {
	      if (!main_core.Type.isPlainObject(params)) {
	        return;
	      }

	      if (main_core.Type.isNumber(params.offsetLeft)) {
	        this.offsetLeft = params.offsetLeft + Popup.getOption('offsetLeft');
	      }

	      if (main_core.Type.isNumber(params.offsetTop)) {
	        this.offsetTop = params.offsetTop + Popup.getOption('offsetTop');
	      }
	    }
	  }, {
	    key: "setTitleBar",
	    value: function setTitleBar(params) {
	      if (!this.titleBar) {
	        return;
	      }

	      if (babelHelpers["typeof"](params) === 'object' && main_core.Type.isDomNode(params.content)) {
	        this.titleBar.innerHTML = '';
	        this.titleBar.appendChild(params.content);
	      } else if (typeof params === 'string') {
	        this.titleBar.innerHTML = '';
	        this.titleBar.appendChild(main_core.Dom.create('span', {
	          props: {
	            className: 'popup-window-titlebar-text'
	          },
	          text: params
	        }));
	      }

	      if (this.params.draggable) {
	        this.titleBar.style.cursor = 'move';
	        main_core.Event.bind(this.titleBar, 'mousedown', this.onTitleMouseDown);
	      }
	    }
	  }, {
	    key: "setClosingByEsc",
	    value: function setClosingByEsc(enable) {
	      enable = main_core.Type.isBoolean(enable) ? enable : true;

	      if (enable) {
	        this.closeByEsc = true;
	        this.bindClosingByEsc();
	      } else {
	        this.closeByEsc = false;
	        this.unbindClosingByEsc();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "bindClosingByEsc",
	    value: function bindClosingByEsc() {
	      if (this.closeByEsc && !this.isCloseByEscBinded) {
	        main_core.Event.bind(document, 'keyup', this.handleDocumentKeyUp);
	        this.isCloseByEscBinded = true;
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "unbindClosingByEsc",
	    value: function unbindClosingByEsc() {
	      if (this.isCloseByEscBinded) {
	        main_core.Event.unbind(document, 'keyup', this.handleDocumentKeyUp);
	        this.isCloseByEscBinded = false;
	      }
	    }
	  }, {
	    key: "setAutoHide",
	    value: function setAutoHide(enable) {
	      enable = main_core.Type.isBoolean(enable) ? enable : true;

	      if (enable) {
	        this.autoHide = true;
	        this.bindAutoHide();
	      } else {
	        this.autoHide = false;
	        this.unbindAutoHide();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "bindAutoHide",
	    value: function bindAutoHide() {
	      if (this.autoHide && !this.isAutoHideBinded && this.isShown()) {
	        this.isAutoHideBinded = true;

	        if (this.isCompatibleMode()) {
	          main_core.Event.bind(this.getPopupContainer(), 'click', this.handleContainerClick);
	        }

	        if (this.overlay && this.overlay.element) {
	          main_core.Event.bind(this.overlay.element, 'click', this.handleOverlayClick);
	        } else {
	          if (this.isCompatibleMode()) {
	            main_core.Event.bind(document, 'click', this.handleAutoHide);
	          } else {
	            document.addEventListener('click', this.handleAutoHide, true);
	          }
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "unbindAutoHide",
	    value: function unbindAutoHide() {
	      if (this.isAutoHideBinded) {
	        this.isAutoHideBinded = false;

	        if (this.isCompatibleMode()) {
	          main_core.Event.unbind(this.getPopupContainer(), 'click', this.handleContainerClick);
	        }

	        if (this.overlay && this.overlay.element) {
	          main_core.Event.unbind(this.overlay.element, 'click', this.handleOverlayClick);
	        } else {
	          if (this.isCompatibleMode()) {
	            main_core.Event.unbind(document, 'click', this.handleAutoHide);
	          } else {
	            document.removeEventListener('click', this.handleAutoHide, true);
	          }
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleAutoHide",
	    value: function handleAutoHide(event) {
	      if (this.isDestroyed()) {
	        return;
	      }

	      if (this.autoHideHandler !== null) {
	        if (this.autoHideHandler(event)) {
	          this._tryCloseByEvent(event);
	        }
	      } else if (event.target !== this.getPopupContainer() && !this.getPopupContainer().contains(event.target)) {
	        this._tryCloseByEvent(event);
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_tryCloseByEvent",
	    value: function _tryCloseByEvent(event) {
	      var _this2 = this;

	      if (this.isCompatibleMode()) {
	        this.tryCloseByEvent(event);
	      } else {
	        setTimeout(function () {
	          _this2.tryCloseByEvent(event);
	        }, 0);
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "tryCloseByEvent",
	    value: function tryCloseByEvent(event) {
	      if (event.button === 0) {
	        this.close();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleOverlayClick",
	    value: function handleOverlayClick(event) {
	      this.tryCloseByEvent(event);
	      event.stopPropagation();
	    }
	  }, {
	    key: "setOverlay",
	    value: function setOverlay(params) {
	      if (this.overlay === null) {
	        this.overlay = {
	          element: main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"popup-window-overlay\" id=\"popup-window-overlay-", "\"></div>\n\t\t\t\t"])), this.getId())
	        };
	        this.resizeOverlay();
	        this.targetContainer.appendChild(this.overlay.element);
	        this.getZIndexComponent().setOverlay(this.overlay.element);
	      }

	      if (params && main_core.Type.isNumber(params.opacity) && params.opacity >= 0 && params.opacity <= 100) {
	        this.overlay.element.style.opacity = parseFloat(params.opacity / 100).toPrecision(3);
	      }

	      if (params && params.backgroundColor) {
	        this.overlay.element.style.backgroundColor = params.backgroundColor;
	      }
	    }
	  }, {
	    key: "removeOverlay",
	    value: function removeOverlay() {
	      if (this.overlay !== null && this.overlay.element !== null) {
	        main_core.Dom.remove(this.overlay.element);
	        this.getZIndexComponent().setOverlay(null);
	      }

	      if (this.overlayTimeout) {
	        clearInterval(this.overlayTimeout);
	        this.overlayTimeout = null;
	      }

	      this.overlay = null;
	    }
	  }, {
	    key: "hideOverlay",
	    value: function hideOverlay() {
	      if (this.overlay !== null && this.overlay.element !== null) {
	        if (this.overlayTimeout) {
	          clearInterval(this.overlayTimeout);
	          this.overlayTimeout = null;
	        }

	        this.overlay.element.style.display = 'none';
	      }
	    }
	  }, {
	    key: "showOverlay",
	    value: function showOverlay() {
	      var _this3 = this;

	      if (this.overlay !== null && this.overlay.element !== null) {
	        this.overlay.element.style.display = 'block';
	        var popupHeight = this.getPopupContainer().offsetHeight;
	        this.overlayTimeout = setInterval(function () {
	          if (popupHeight !== _this3.getPopupContainer().offsetHeight) {
	            _this3.resizeOverlay();

	            popupHeight = _this3.getPopupContainer().offsetHeight;
	          }
	        }, 1000);
	      }
	    }
	  }, {
	    key: "resizeOverlay",
	    value: function resizeOverlay() {
	      if (this.overlay !== null && this.overlay.element !== null) {
	        var scrollWidth;
	        var scrollHeight;

	        if (this.isTargetDocumentBody()) {
	          scrollWidth = document.documentElement.scrollWidth;
	          scrollHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight, document.body.offsetHeight, document.documentElement.offsetHeight, document.body.clientHeight, document.documentElement.clientHeight);
	        } else {
	          scrollWidth = this.getTargetContainer().scrollWidth;
	          scrollHeight = this.getTargetContainer().scrollHeight;
	        }

	        this.overlay.element.style.width = scrollWidth + 'px';
	        this.overlay.element.style.height = scrollHeight + 'px';
	      }
	    }
	  }, {
	    key: "getZindex",
	    value: function getZindex() {
	      return this.getZIndexComponent().getZIndex();
	    }
	  }, {
	    key: "getZIndexComponent",
	    value: function getZIndexComponent() {
	      return this.zIndexComponent;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this4 = this;

	      if (this.isShown() || this.isDestroyed()) {
	        return;
	      }

	      this.emit('onBeforeShow');
	      this.showOverlay();
	      this.getPopupContainer().style.display = 'block';

	      if (this.shouldFrontOnShow()) {
	        this.bringToFront();
	      }

	      if (!this.firstShow) {
	        this.emit('onFirstShow', new main_core_events.BaseEvent({
	          compatData: [this]
	        }));
	        this.firstShow = true;
	      }

	      this.emit('onShow', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	      this.adjustPosition();
	      this.animateOpening(function () {
	        if (_this4.isDestroyed()) {
	          return;
	        }

	        main_core.Dom.removeClass(_this4.getPopupContainer(), _this4.animationShowClassName);

	        _this4.emit('onAfterShow', new main_core_events.BaseEvent({
	          compatData: [_this4]
	        }));
	      });
	      this.bindClosingByEsc();

	      if (this.isCompatibleMode()) {
	        setTimeout(function () {
	          _this4.bindAutoHide();
	        }, 100);
	      } else {
	        this.bindAutoHide();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      var _this5 = this;

	      if (this.isDestroyed() || !this.isShown()) {
	        return;
	      }

	      this.emit('onClose', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));

	      if (this.isDestroyed()) {
	        return;
	      }

	      this.animateClosing(function () {
	        if (_this5.isDestroyed()) {
	          return;
	        }

	        _this5.hideOverlay();

	        _this5.getPopupContainer().style.display = 'none';
	        main_core.Dom.removeClass(_this5.getPopupContainer(), _this5.animationCloseClassName);

	        _this5.unbindClosingByEsc();

	        if (_this5.isCompatibleMode()) {
	          setTimeout(function () {
	            _this5.unbindAutoHide();
	          }, 0);
	        } else {
	          _this5.unbindAutoHide();
	        }

	        _this5.emit('onAfterClose', new main_core_events.BaseEvent({
	          compatData: [_this5]
	        }));

	        if (!_this5.isCacheable()) {
	          _this5.destroy();
	        }
	      });
	    }
	  }, {
	    key: "bringToFront",
	    value: function bringToFront() {
	      if (this.isShown()) {
	        main_core_zIndexManager.ZIndexManager.bringToFront(this.getPopupContainer());
	      }
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      this.isShown() ? this.close() : this.show();
	    }
	    /**
	     *
	     * @private
	     */

	  }, {
	    key: "animateOpening",
	    value: function animateOpening(callback) {
	      main_core.Dom.removeClass(this.getPopupContainer(), this.animationCloseClassName);

	      if (this.animationShowClassName !== null) {
	        main_core.Dom.addClass(this.getPopupContainer(), this.animationShowClassName);

	        if (this.animationCloseEventType !== null) {
	          var eventName = this.animationCloseEventType + 'end';
	          this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
	            this.removeEventListener(eventName, handleTransitionEnd);
	            callback();
	          });
	        } else {
	          callback();
	        }
	      } else {
	        callback();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "animateClosing",
	    value: function animateClosing(callback) {
	      main_core.Dom.removeClass(this.getPopupContainer(), this.animationShowClassName);

	      if (this.animationCloseClassName !== null) {
	        main_core.Dom.addClass(this.getPopupContainer(), this.animationCloseClassName);

	        if (this.animationCloseEventType !== null) {
	          var eventName = this.animationCloseEventType + 'end';
	          this.getPopupContainer().addEventListener(eventName, function handleTransitionEnd() {
	            this.removeEventListener(eventName, handleTransitionEnd);
	            callback();
	          });
	        } else {
	          callback();
	        }
	      } else {
	        callback();
	      }
	    }
	  }, {
	    key: "setAnimation",
	    value: function setAnimation(options) {
	      if (main_core.Type.isPlainObject(options)) {
	        this.animationShowClassName = main_core.Type.isStringFilled(options.showClassName) ? options.showClassName : null;
	        this.animationCloseClassName = main_core.Type.isStringFilled(options.closeClassName) ? options.closeClassName : null;
	        this.animationCloseEventType = options.closeAnimationType === 'animation' || options.closeAnimationType === 'transition' ? options.closeAnimationType : null;
	      } else if (main_core.Type.isStringFilled(options)) {
	        var animationName = options;

	        if (animationName === 'fading') {
	          this.animationShowClassName = 'popup-window-show-animation-opacity';
	          this.animationCloseClassName = 'popup-window-close-animation-opacity';
	          this.animationCloseEventType = 'animation';
	        } else if (animationName === 'fading-slide') {
	          this.animationShowClassName = 'popup-window-show-animation-opacity-transform';
	          this.animationCloseClassName = 'popup-window-close-animation-opacity';
	          this.animationCloseEventType = 'animation';
	        } else if (animationName === 'scale') {
	          this.animationShowClassName = 'popup-window-show-animation-scale';
	          this.animationCloseClassName = 'popup-window-close-animation-opacity';
	          this.animationCloseEventType = 'animation';
	        }
	      } else if (options === false || options === null) {
	        this.animationShowClassName = null;
	        this.animationCloseClassName = null;
	        this.animationCloseEventType = null;
	      }
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return !this.isDestroyed() && this.getPopupContainer().style.display === 'block';
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      var _this6 = this;

	      if (this.destroyed) {
	        return;
	      }

	      this.destroyed = true;
	      this.emit('onDestroy', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	      this.unbindClosingByEsc();

	      if (this.isCompatibleMode()) {
	        setTimeout(function () {
	          _this6.unbindAutoHide();
	        }, 0);
	      } else {
	        this.unbindAutoHide();
	      }

	      main_core.Event.unbindAll(this);
	      main_core.Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
	      main_core.Event.unbind(document, 'mouseup', this.handleDocumentMouseUp);
	      main_core.Event.unbind(window, 'resize', this.handleResizeWindow);
	      this.removeOverlay();
	      main_core_zIndexManager.ZIndexManager.unregister(this.popupContainer);
	      this.zIndexComponent = null;
	      main_core.Dom.remove(this.popupContainer);
	      this.popupContainer = null;
	      this.contentContainer = null;
	      this.closeIcon = null;
	      this.titleBar = null;
	      this.buttonsContainer = null;
	      this.angle = null;
	      this.angleArrowElement = null;
	      this.resizeIcon = null;
	    }
	  }, {
	    key: "adjustPosition",
	    value: function adjustPosition(bindOptions) {
	      if (bindOptions && babelHelpers["typeof"](bindOptions) === 'object') {
	        this.bindOptions = bindOptions;
	      }

	      var bindElementPos = this.getBindElementPos(this.bindElement);

	      if (!this.bindOptions.forceBindPosition && this.bindElementPos !== null && bindElementPos.top === this.bindElementPos.top && bindElementPos.left === this.bindElementPos.left) {
	        return;
	      }

	      this.bindElementPos = bindElementPos;
	      var windowSize = bindElementPos.windowSize ? bindElementPos.windowSize : this.getWindowSize();
	      var windowScroll = bindElementPos.windowScroll ? bindElementPos.windowScroll : this.getWindowScroll();
	      var popupWidth = bindElementPos.popupWidth ? bindElementPos.popupWidth : this.popupContainer.offsetWidth;
	      var popupHeight = bindElementPos.popupHeight ? bindElementPos.popupHeight : this.popupContainer.offsetHeight;
	      var angleTopOffset = Popup.getOption('angleTopOffset');
	      var left = this.bindElementPos.left + this.offsetLeft - (this.isTopOrBottomAngle() ? Popup.getOption('angleLeftOffset') : 0);

	      if (!this.bindOptions.forceLeft && left + popupWidth + this.bordersWidth >= windowSize.innerWidth + windowScroll.scrollLeft && windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth > 0) {
	        var bindLeft = left;
	        left = windowSize.innerWidth + windowScroll.scrollLeft - popupWidth - this.bordersWidth;

	        if (this.isTopOrBottomAngle()) {
	          this.setAngle({
	            offset: bindLeft - left + this.angle.defaultOffset
	          });
	        }
	      } else if (this.isTopOrBottomAngle()) {
	        this.setAngle({
	          offset: this.angle.defaultOffset + (left < 0 ? left : 0)
	        });
	      }

	      if (left < 0) {
	        left = 0;
	      }

	      var top = 0;

	      if (this.bindOptions.position && this.bindOptions.position === 'top') {
	        top = this.bindElementPos.top - popupHeight - this.offsetTop - (this.isBottomAngle() ? angleTopOffset : 0);

	        if (top < 0 || !this.bindOptions.forceTop && top < windowScroll.scrollTop) {
	          top = this.bindElementPos.bottom + this.offsetTop;

	          if (this.angle !== null) {
	            top += angleTopOffset;
	            this.setAngle({
	              position: 'top'
	            });
	          }
	        } else if (this.isTopAngle()) {
	          top = top - angleTopOffset + Popup.getOption('positionTopXOffset');
	          this.setAngle({
	            position: 'bottom'
	          });
	        } else {
	          top += Popup.getOption('positionTopXOffset');
	        }
	      } else {
	        top = this.bindElementPos.bottom + this.offsetTop + this.getAngleHeight();

	        if (!this.bindOptions.forceTop && top + popupHeight > windowSize.innerHeight + windowScroll.scrollTop && this.bindElementPos.top - popupHeight - this.getAngleHeight() >= 0) //Can we place the PopupWindow above the bindElement?
	          {
	            //The PopupWindow doesn't place below the bindElement. We should place it above.
	            top = this.bindElementPos.top - popupHeight;

	            if (this.isTopOrBottomAngle()) {
	              top -= angleTopOffset;
	              this.setAngle({
	                position: 'bottom'
	              });
	            }

	            top += Popup.getOption('positionTopXOffset');
	          } else if (this.isBottomAngle()) {
	          top += angleTopOffset;
	          this.setAngle({
	            position: 'top'
	          });
	        }
	      }

	      if (top < 0) {
	        top = 0;
	      }

	      var event = new PositionEvent();
	      event.left = left;
	      event.top = top;
	      this.emit('onBeforeAdjustPosition', event);
	      main_core.Dom.adjust(this.popupContainer, {
	        style: {
	          top: event.top + 'px',
	          left: event.left + 'px'
	        }
	      });
	    }
	  }, {
	    key: "enterFullScreen",
	    value: function enterFullScreen() {
	      if (Popup.fullscreenStatus) {
	        if (document.cancelFullScreen) {
	          document.cancelFullScreen();
	        } else if (document.mozCancelFullScreen) {
	          document.mozCancelFullScreen();
	        } else if (document.webkitCancelFullScreen) {
	          document.webkitCancelFullScreen();
	        }
	      } else {
	        if (this.contentContainer.requestFullScreen) {
	          this.contentContainer.requestFullScreen();
	          main_core.Event.bind(window, 'fullscreenchange', this.handleFullScreen);
	        } else if (this.contentContainer.mozRequestFullScreen) {
	          this.contentContainer.mozRequestFullScreen();
	          main_core.Event.bind(window, 'mozfullscreenchange', this.handleFullScreen);
	        } else if (this.contentContainer.webkitRequestFullScreen) {
	          this.contentContainer.webkitRequestFullScreen();
	          main_core.Event.bind(window, 'webkitfullscreenchange', this.handleFullScreen);
	        } else {
	          console.log('fullscreen mode is not supported');
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleFullScreen",
	    value: function handleFullScreen(event) {
	      if (Popup.fullscreenStatus) {
	        main_core.Event.unbind(window, 'fullscreenchange', this.handleFullScreen);
	        main_core.Event.unbind(window, 'webkitfullscreenchange', this.handleFullScreen);
	        main_core.Event.unbind(window, 'mozfullscreenchange', this.handleFullScreen);
	        Popup.fullscreenStatus = false;

	        if (!this.isDestroyed()) {
	          main_core.Dom.removeClass(this.contentContainer, 'popup-window-fullscreen');
	          this.emit('onFullscreenLeave');
	          this.adjustPosition();
	        }
	      } else {
	        Popup.fullscreenStatus = true;

	        if (!this.isDestroyed()) {
	          main_core.Dom.addClass(this.contentContainer, 'popup-window-fullscreen');
	          this.emit('onFullscreenEnter');
	          this.adjustPosition();
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleCloseIconClick",
	    value: function handleCloseIconClick(event) {
	      this.tryCloseByEvent(event);
	      event.stopPropagation();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleContainerClick",
	    value: function handleContainerClick(event) {
	      event.stopPropagation();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleDocumentKeyUp",
	    value: function handleDocumentKeyUp(event) {
	      var _this7 = this;

	      if (event.keyCode === 27) {
	        checkEscPressed(this.getZindex(), function () {
	          _this7.close();
	        });
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleResizeWindow",
	    value: function handleResizeWindow() {
	      if (this.isShown()) {
	        this.adjustPosition();

	        if (this.overlay !== null) {
	          this.resizeOverlay();
	        }
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleMove",
	    value: function handleMove(offsetX, offsetY, pageX, pageY) {
	      var left = parseInt(this.popupContainer.style.left) + offsetX;
	      var top = parseInt(this.popupContainer.style.top) + offsetY;

	      if (babelHelpers["typeof"](this.params.draggable) === 'object' && this.params.draggable.restrict) {
	        //Left side
	        if (left < 0) {
	          left = 0;
	        }

	        var scrollWidth;
	        var scrollHeight;

	        if (this.isTargetDocumentBody()) {
	          scrollWidth = document.documentElement.scrollWidth;
	          scrollHeight = document.documentElement.scrollHeight;
	        } else {
	          scrollWidth = this.getTargetContainer().scrollWidth;
	          scrollHeight = this.getTargetContainer().scrollHeight;
	        } //Right side


	        var floatWidth = this.popupContainer.offsetWidth;
	        var floatHeight = this.popupContainer.offsetHeight;

	        if (left > scrollWidth - floatWidth) {
	          left = scrollWidth - floatWidth;
	        }

	        if (top > scrollHeight - floatHeight) {
	          top = scrollHeight - floatHeight;
	        } //Top side


	        if (top < 0) {
	          top = 0;
	        }
	      }

	      this.popupContainer.style.left = left + 'px';
	      this.popupContainer.style.top = top + 'px';
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "_startDrag",
	    value: function _startDrag(event, options) {
	      options = options || {};

	      if (main_core.Type.isStringFilled(options.cursor)) {
	        this.dragOptions.cursor = options.cursor;
	      }

	      if (main_core.Type.isStringFilled(options.eventName)) {
	        this.dragOptions.eventName = options.eventName;
	      }

	      if (main_core.Type.isFunction(options.callback)) {
	        this.dragOptions.callback = options.callback;
	      }

	      this.dragPageX = event.pageX;
	      this.dragPageY = event.pageY;
	      this.dragged = false;
	      main_core.Event.bind(document, 'mousemove', this.handleDocumentMouseMove);
	      main_core.Event.bind(document, 'mouseup', this.handleDocumentMouseUp);

	      if (document.body.setCapture) {
	        document.body.setCapture();
	      }

	      document.body.ondrag = function () {
	        return false;
	      };

	      document.body.onselectstart = function () {
	        return false;
	      };

	      document.body.style.cursor = this.dragOptions.cursor;
	      document.body.style.MozUserSelect = 'none';
	      this.popupContainer.style.MozUserSelect = 'none';

	      if (this.shouldFrontOnShow()) {
	        this.bringToFront();
	      }

	      event.preventDefault();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleDocumentMouseMove",
	    value: function handleDocumentMouseMove(event) {
	      if (this.dragPageX === event.pageX && this.dragPageY === event.pageY) {
	        return;
	      }

	      this.dragOptions.callback(event.pageX - this.dragPageX, event.pageY - this.dragPageY, event.pageX, event.pageY);
	      this.dragPageX = event.pageX;
	      this.dragPageY = event.pageY;

	      if (!this.dragged) {
	        this.emit("on".concat(this.dragOptions.eventName, "Start"), new main_core_events.BaseEvent({
	          compatData: [this]
	        }));
	        this.dragged = true;
	      }

	      this.emit("on".concat(this.dragOptions.eventName), new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleDocumentMouseUp",
	    value: function handleDocumentMouseUp(event) {
	      if (document.body.releaseCapture) {
	        document.body.releaseCapture();
	      }

	      main_core.Event.unbind(document, 'mousemove', this.handleDocumentMouseMove);
	      main_core.Event.unbind(document, 'mouseup', this.handleDocumentMouseUp);
	      document.body.ondrag = null;
	      document.body.onselectstart = null;
	      document.body.style.cursor = '';
	      document.body.style.MozUserSelect = '';
	      this.popupContainer.style.MozUserSelect = '';
	      this.emit("on".concat(this.dragOptions.eventName, "End"), new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	      this.dragged = false;
	      event.preventDefault();
	    }
	  }]);
	  return Popup;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(Popup, "options", {});
	babelHelpers.defineProperty(Popup, "defaultOptions", {
	  //left offset for popup about target
	  angleLeftOffset: 40,
	  //when popup position is 'top' offset distance between popup body and target node
	  positionTopXOffset: -11,
	  //offset distance between popup body and target node if use angle, sum with positionTopXOffset
	  angleTopOffset: 10,
	  popupZindex: 1000,
	  popupOverlayZindex: 1100,
	  angleMinLeft: 10,
	  angleMaxLeft: 30,
	  angleMinRight: 10,
	  angleMaxRight: 30,
	  angleMinBottom: 23,
	  angleMaxBottom: 25,
	  angleMinTop: 23,
	  angleMaxTop: 25,
	  offsetLeft: 0,
	  offsetTop: 0
	});
	var escCallbackIndex = -1;
	var escCallback = null;

	function checkEscPressed(zIndex, callback) {
	  if (zIndex === false) {
	    if (escCallback && escCallback.length > 0) {
	      for (var i = 0; i < escCallback.length; i++) {
	        escCallback[i]();
	      }

	      escCallback = null;
	      escCallbackIndex = -1;
	    }
	  } else {
	    if (escCallback === null) {
	      escCallback = [];
	      escCallbackIndex = -1;
	      setTimeout(function () {
	        checkEscPressed(false);
	      }, 10);
	    }

	    if (zIndex > escCallbackIndex) {
	      escCallbackIndex = zIndex;
	      escCallback = [callback];
	    } else if (zIndex === escCallbackIndex) {
	      escCallback.push(callback);
	    }
	  }
	}

	var PopupManager = /*#__PURE__*/function () {
	  function PopupManager() {
	    babelHelpers.classCallCheck(this, PopupManager);
	    throw new Error('You cannot make an instance of PopupManager.');
	  }

	  babelHelpers.createClass(PopupManager, null, [{
	    key: "create",
	    value: function create(options) {
	      var _arguments = Array.prototype.slice.call(arguments),
	          popupId = _arguments[0],
	          bindElement = _arguments[1],
	          params = _arguments[2]; //compatible arguments


	      var id = popupId;
	      var compatMode = true;

	      if (main_core.Type.isPlainObject(popupId) && !bindElement && !params) {
	        compatMode = false;
	        id = popupId.id;

	        if (!main_core.Type.isStringFilled(id)) {
	          throw new Error('BX.Main.Popup.Manager: "id" parameter is required.');
	        }
	      }

	      var popupWindow = this.getPopupById(id);

	      if (popupWindow === null) {
	        popupWindow = compatMode ? new Popup(popupId, bindElement, params) : new Popup(options);
	        popupWindow.subscribe('onShow', this.handlePopupShow);
	        popupWindow.subscribe('onClose', this.handlePopupClose);
	      }

	      return popupWindow;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleOnAfterInit",
	    value: function handleOnAfterInit(event) {
	      event.getTarget().subscribeOnce('onDestroy', this.handlePopupDestroy);

	      this._popups.forEach(function (popup) {
	        if (popup.getId() === event.getTarget().getId()) {
	          console.error("Duplicate id (".concat(popup.getId(), ") for the BX.Main.Popup instance."));
	        }
	      });

	      this._popups.push(event.getTarget());
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handlePopupDestroy",
	    value: function handlePopupDestroy(event) {
	      this._popups = this._popups.filter(function (popup) {
	        return popup !== event.getTarget();
	      });
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handlePopupShow",
	    value: function handlePopupShow(event) {
	      if (this._currentPopup !== null) {
	        this._currentPopup.close();
	      }

	      this._currentPopup = event.getTarget();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handlePopupClose",
	    value: function handlePopupClose() {
	      this._currentPopup = null;
	    }
	  }, {
	    key: "getCurrentPopup",
	    value: function getCurrentPopup() {
	      return this._currentPopup;
	    }
	  }, {
	    key: "isPopupExists",
	    value: function isPopupExists(id) {
	      return this.getPopupById(id) !== null;
	    }
	  }, {
	    key: "isAnyPopupShown",
	    value: function isAnyPopupShown() {
	      for (var i = 0, length = this._popups.length; i < length; i++) {
	        if (this._popups[i].isShown()) {
	          return true;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "getPopupById",
	    value: function getPopupById(id) {
	      for (var i = 0; i < this._popups.length; i++) {
	        if (this._popups[i].getId() === id) {
	          return this._popups[i];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "getMaxZIndex",
	    value: function getMaxZIndex() {
	      var zIndex = 0;
	      this.getPopups().forEach(function (popup) {
	        zIndex = Math.max(zIndex, popup.getZindex());
	      });
	      return zIndex;
	    }
	  }, {
	    key: "getPopups",
	    value: function getPopups() {
	      return this._popups;
	    }
	  }]);
	  return PopupManager;
	}();

	babelHelpers.defineProperty(PopupManager, "_popups", []);
	babelHelpers.defineProperty(PopupManager, "_currentPopup", null);
	PopupManager.handlePopupDestroy = PopupManager.handlePopupDestroy.bind(PopupManager);
	PopupManager.handlePopupShow = PopupManager.handlePopupShow.bind(PopupManager);
	PopupManager.handlePopupClose = PopupManager.handlePopupClose.bind(PopupManager);
	PopupManager.handleOnAfterInit = PopupManager.handleOnAfterInit.bind(PopupManager);
	main_core_events.EventEmitter.subscribe('BX.Main.Popup:onAfterInit', PopupManager.handleOnAfterInit);

	var _templateObject$1, _templateObject2$1, _templateObject3$1;
	var aliases$1 = {
	  onSubMenuShow: {
	    namespace: 'BX.Main.Menu.Item',
	    eventName: 'SubMenu:onShow'
	  },
	  onSubMenuClose: {
	    namespace: 'BX.Main.Menu.Item',
	    eventName: 'SubMenu:onClose'
	  }
	};
	var reEscape = /[<>'"]/g;
	var escapeEntities = {
	  '<': '&lt;',
	  '>': '&gt;',
	  "'": '&#39;',
	  '"': '&quot;'
	};

	function encodeSafe(value) {
	  if (main_core.Type.isString(value)) {
	    return value.replace(reEscape, function (item) {
	      return escapeEntities[item];
	    });
	  }

	  return value;
	}

	main_core_events.EventEmitter.registerAliases(aliases$1);

	var MenuItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(MenuItem, _EventEmitter);

	  function MenuItem(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, MenuItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuItem).call(this));

	    _this.setEventNamespace('BX.Main.Menu.Item');

	    options = options || {};
	    _this.options = options;
	    _this.id = options.id || main_core.Text.getRandom();
	    _this.text = '';
	    _this.allowHtml = false;

	    if (main_core.Type.isStringFilled(options.html) || main_core.Type.isElementNode(options.html)) {
	      _this.text = options.html;
	      _this.allowHtml = true;
	    } else if (main_core.Type.isStringFilled(options.text)) {
	      _this.text = options.text;

	      if (_this.text.match(/<[^>]+>/)) {
	        console.warn('BX.Main.MenuItem: use "html" option for the html item content.', _this.getText());
	      }
	    }

	    _this.title = main_core.Type.isStringFilled(options.title) ? options.title : '';
	    _this.delimiter = options.delimiter === true;
	    _this.href = main_core.Type.isStringFilled(options.href) ? options.href : null;
	    _this.target = main_core.Type.isStringFilled(options.target) ? options.target : null;
	    _this.dataset = main_core.Type.isPlainObject(options.dataset) ? options.dataset : null;
	    _this.className = main_core.Type.isStringFilled(options.className) ? options.className : null;
	    _this.menuShowDelay = main_core.Type.isNumber(options.menuShowDelay) ? options.menuShowDelay : 300;
	    _this.subMenuOffsetX = main_core.Type.isNumber(options.subMenuOffsetX) ? options.subMenuOffsetX : 4;
	    _this._items = main_core.Type.isArray(options.items) ? options.items : [];
	    _this.disabled = options.disabled === true;
	    _this.cacheable = options.cacheable === true;
	    /**
	     *
	     * @type {function|string}
	     */

	    _this.onclick = main_core.Type.isStringFilled(options.onclick) || main_core.Type.isFunction(options.onclick) ? options.onclick : null;

	    _this.subscribeFromOptions(options.events, aliases$1);
	    /**
	     *
	     * @type {Menu}
	     */


	    _this.menuWindow = null;
	    /**
	     *
	     * @type {Menu}
	     */

	    _this.subMenuWindow = null;
	    /**
	     *
	     * @type {{item: HTMLElement, text: HTMLElement}}
	     */

	    _this.layout = {
	      item: null,
	      text: null
	    };

	    _this.getLayout(); //compatibility
	    //compatibility
	    //now use this.options


	    _this.events = {};
	    _this.items = [];

	    for (var property in options) {
	      if (options.hasOwnProperty(property) && typeof _this[property] === 'undefined') {
	        _this[property] = options[property];
	      }
	    }

	    return _this;
	  }

	  babelHelpers.createClass(MenuItem, [{
	    key: "getLayout",
	    value: function getLayout() {
	      if (this.layout.item) {
	        return this.layout;
	      }

	      if (this.delimiter) {
	        if (main_core.Type.isStringFilled(this.getText())) {
	          this.layout.item = main_core.Dom.create('span', {
	            props: {
	              className: ['popup-window-delimiter-section', this.className ? this.className : ''].join(' ')
	            },
	            children: [this.layout.text = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"popup-window-delimiter-text\">", "</span>\n\t\t\t\t\t\t"])), this.allowHtml ? this.getText() : encodeSafe(this.getText()))]
	          });
	        } else {
	          this.layout.item = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"popup-window-delimiter\">"])));
	        }
	      } else {
	        this.layout.item = main_core.Dom.create(this.href ? 'a' : 'span', {
	          props: {
	            className: ['menu-popup-item', this.className ? this.className : 'menu-popup-no-icon', this.hasSubMenu() ? 'menu-popup-item-submenu' : ''].join(' ')
	          },
	          attrs: {
	            title: this.title,
	            onclick: main_core.Type.isString(this.onclick) ? this.onclick : '',
	            // compatibility
	            target: this.target ? this.target : ''
	          },
	          dataset: this.dataset,
	          events: main_core.Type.isFunction(this.onclick) ? {
	            click: this.onItemClick.bind(this)
	          } : null,
	          children: [main_core.Dom.create('span', {
	            props: {
	              className: 'menu-popup-item-icon'
	            }
	          }), this.layout.text = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<span class=\"menu-popup-item-text\">", "</span>\n\t\t\t\t\t"])), this.allowHtml ? this.getText() : encodeSafe(this.getText()))]
	        });

	        if (this.href) {
	          this.layout.item.href = this.href;
	        }

	        if (this.isDisabled()) {
	          this.disable();
	        }

	        main_core.Event.bind(this.layout.item, 'mouseenter', this.onItemMouseEnter.bind(this));
	        main_core.Event.bind(this.layout.item, 'mouseleave', this.onItemMouseLeave.bind(this));
	      }

	      return this.layout;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.getLayout().item;
	    }
	  }, {
	    key: "getTextContainer",
	    value: function getTextContainer() {
	      return this.getLayout().text;
	    }
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      var allowHtml = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

	      if (main_core.Type.isString(text) || main_core.Type.isElementNode(text)) {
	        this.allowHtml = allowHtml;
	        this.text = text;

	        if (main_core.Type.isElementNode(text)) {
	          main_core.Dom.clean(this.getTextContainer());

	          if (this.allowHtml) {
	            main_core.Dom.append(text, this.getTextContainer());
	          } else {
	            this.getTextContainer().innerHTML = encodeSafe(text.outerHTML);
	          }
	        } else {
	          this.getTextContainer().innerHTML = this.allowHtml ? text : encodeSafe(text);
	        }
	      }
	    }
	  }, {
	    key: "hasSubMenu",
	    value: function hasSubMenu() {
	      return this.subMenuWindow !== null || this._items.length;
	    }
	  }, {
	    key: "showSubMenu",
	    value: function showSubMenu() {
	      if (!this.getMenuWindow().getPopupWindow().isShown()) {
	        return;
	      }

	      this.addSubMenu(this._items);

	      if (this.subMenuWindow) {
	        main_core.Dom.addClass(this.layout.item, 'menu-popup-item-open');
	        this.closeSiblings();
	        this.closeChildren();
	        var popupWindow = this.subMenuWindow.getPopupWindow();

	        if (!popupWindow.isShown()) {
	          this.emit('SubMenu:onShow');
	          popupWindow.show();
	        }

	        this.adjustSubMenu();
	      }
	    }
	  }, {
	    key: "addSubMenu",
	    value: function addSubMenu(items) {
	      if (this.subMenuWindow !== null || !main_core.Type.isArray(items) || !items.length) {
	        return;
	      }

	      var rootMenuWindow = this.getMenuWindow().getRootMenuWindow() || this.getMenuWindow();
	      var rootOptions = Object.assign({}, rootMenuWindow.params);
	      delete rootOptions.events;
	      var subMenuOptions = main_core.Type.isPlainObject(rootMenuWindow.params.subMenuOptions) ? rootMenuWindow.params.subMenuOptions : {};
	      var options = Object.assign({}, rootOptions, subMenuOptions); //Override root menu options

	      options.autoHide = false;
	      options.menuShowDelay = this.menuShowDelay;
	      options.cacheable = this.isCacheable();
	      options.targetContainer = this.getMenuWindow().getPopupWindow().getTargetContainer();
	      options.bindOptions = {
	        forceTop: true,
	        forceLeft: true,
	        forceBindPosition: true
	      };
	      delete options.angle;
	      delete options.overlay;
	      this.subMenuWindow = new Menu('popup-submenu-' + this.id, this.layout.item, items, options);
	      this.subMenuWindow.setParentMenuWindow(this.getMenuWindow());
	      this.subMenuWindow.setParentMenuItem(this);
	      this.subMenuWindow.getPopupWindow().subscribe('onDestroy', this.handleSubMenuDestroy.bind(this));
	      main_core.Dom.addClass(this.layout.item, 'menu-popup-item-submenu');
	      return this.subMenuWindow;
	    }
	  }, {
	    key: "closeSubMenu",
	    value: function closeSubMenu() {
	      this.clearSubMenuTimeout();

	      if (this.subMenuWindow) {
	        main_core.Dom.removeClass(this.layout.item, 'menu-popup-item-open');
	        this.closeChildren();
	        var popup = this.subMenuWindow.getPopupWindow();

	        if (popup.isShown()) {
	          this.emit('SubMenu:onClose');
	        }

	        this.subMenuWindow.close();
	      }
	    }
	  }, {
	    key: "closeSiblings",
	    value: function closeSiblings() {
	      var siblings = this.menuWindow.getMenuItems();

	      for (var i = 0; i < siblings.length; i++) {
	        if (siblings[i] !== this) {
	          siblings[i].closeSubMenu();
	        }
	      }
	    }
	  }, {
	    key: "closeChildren",
	    value: function closeChildren() {
	      if (this.subMenuWindow) {
	        var children = this.subMenuWindow.getMenuItems();

	        for (var i = 0; i < children.length; i++) {
	          children[i].closeSubMenu();
	        }
	      }
	    }
	  }, {
	    key: "destroySubMenu",
	    value: function destroySubMenu() {
	      if (this.subMenuWindow) {
	        main_core.Dom.removeClass(this.layout.item, 'menu-popup-item-open menu-popup-item-submenu');
	        this.destroyChildren();
	        this.subMenuWindow.destroy();
	        this.subMenuWindow = null;
	        this._items = [];
	      }
	    }
	  }, {
	    key: "destroyChildren",
	    value: function destroyChildren() {
	      if (this.subMenuWindow) {
	        var children = this.subMenuWindow.getMenuItems();

	        for (var i = 0; i < children.length; i++) {
	          children[i].destroySubMenu();
	        }
	      }
	    }
	  }, {
	    key: "adjustSubMenu",
	    value: function adjustSubMenu() {
	      if (!this.subMenuWindow || !this.layout.item) {
	        return;
	      }

	      var popupWindow = this.subMenuWindow.getPopupWindow();
	      var itemRect = this.getBoundingClientRect();
	      var offsetLeft = itemRect.width + this.subMenuOffsetX;
	      var offsetTop = itemRect.height + this.getPopupPadding();
	      var angleOffset = itemRect.height / 2 - this.getPopupPadding();
	      var anglePosition = 'left';
	      var popupWidth = popupWindow.getPopupContainer().offsetWidth;
	      var popupHeight = popupWindow.getPopupContainer().offsetHeight;
	      var popupBottom = itemRect.top + popupHeight;
	      var targetContainer = this.getMenuWindow().getPopupWindow().getTargetContainer();
	      var isGlobalContext = this.getMenuWindow().getPopupWindow().isTargetDocumentBody();
	      var clientWidth = isGlobalContext ? document.documentElement.clientWidth : targetContainer.offsetWidth;
	      var clientHeight = isGlobalContext ? document.documentElement.clientHeight : targetContainer.offsetHeight; // let's try to fit a submenu to the browser viewport

	      var exceeded = popupBottom - clientHeight;

	      if (exceeded > 0) {
	        var roundOffset = Math.ceil(exceeded / itemRect.height) * itemRect.height;

	        if (roundOffset > itemRect.top) {
	          // it cannot be higher than the browser viewport.
	          roundOffset -= Math.ceil((roundOffset - itemRect.top) / itemRect.height) * itemRect.height;
	        }

	        if (itemRect.bottom > popupBottom - roundOffset) {
	          // let's sync bottom boundaries.
	          roundOffset -= itemRect.bottom - (popupBottom - roundOffset) + this.getPopupPadding();
	        }

	        offsetTop += roundOffset;
	        angleOffset += roundOffset;
	      }

	      if (itemRect.left + offsetLeft + popupWidth > clientWidth) {
	        var left = itemRect.left - popupWidth - this.subMenuOffsetX;

	        if (left > 0) {
	          offsetLeft = -popupWidth - this.subMenuOffsetX;
	          anglePosition = 'right';
	        }
	      }

	      popupWindow.setBindElement(this.layout.item);
	      popupWindow.setOffset({
	        offsetLeft: offsetLeft,
	        offsetTop: -offsetTop
	      });
	      popupWindow.setAngle({
	        position: anglePosition,
	        offset: angleOffset
	      });
	      popupWindow.adjustPosition();
	    }
	  }, {
	    key: "getBoundingClientRect",
	    value: function getBoundingClientRect() {
	      var popup = this.getMenuWindow().getPopupWindow();

	      if (popup.isTargetDocumentBody()) {
	        return this.layout.item.getBoundingClientRect();
	      } else {
	        var rect = popup.getPositionRelativeToTarget(this.layout.item);
	        var targetContainer = this.getMenuWindow().getPopupWindow().getTargetContainer();
	        return new DOMRect(rect.left - targetContainer.scrollLeft, rect.top - targetContainer.scrollTop, rect.width, rect.height);
	      }
	    }
	  }, {
	    key: "getPopupPadding",
	    value: function getPopupPadding() {
	      if (!main_core.Type.isNumber(this.popupPadding)) {
	        if (this.subMenuWindow) {
	          var menuContainer = this.subMenuWindow.layout.menuContainer;
	          this.popupPadding = parseInt(main_core.Dom.style(menuContainer, 'paddingTop'), 10);
	        } else {
	          this.popupPadding = 0;
	        }
	      }

	      return this.popupPadding;
	    }
	  }, {
	    key: "getSubMenu",
	    value: function getSubMenu() {
	      return this.subMenuWindow;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setMenuWindow",
	    value: function setMenuWindow(menu) {
	      this.menuWindow = menu;
	    }
	  }, {
	    key: "getMenuWindow",
	    value: function getMenuWindow() {
	      return this.menuWindow;
	    }
	  }, {
	    key: "getMenuShowDelay",
	    value: function getMenuShowDelay() {
	      return this.menuShowDelay;
	    }
	  }, {
	    key: "enable",
	    value: function enable() {
	      this.disabled = false;
	      this.getContainer().classList.remove('menu-popup-item-disabled');
	    }
	  }, {
	    key: "disable",
	    value: function disable() {
	      this.disabled = true;
	      this.closeSubMenu();
	      this.getContainer().classList.add('menu-popup-item-disabled');
	    }
	  }, {
	    key: "isDisabled",
	    value: function isDisabled() {
	      return this.disabled;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(cacheable) {
	      this.cacheable = cacheable !== false;
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "onItemClick",
	    value: function onItemClick(event) {
	      this.onclick.call(this.menuWindow, event, this); //compatibility
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "onItemMouseEnter",
	    value: function onItemMouseEnter(mouseEvent) {
	      if (this.isDisabled()) {
	        return;
	      }

	      var event = new main_core_events.BaseEvent({
	        data: {
	          mouseEvent: mouseEvent
	        }
	      });
	      main_core_events.EventEmitter.emit(this, 'onMouseEnter', event, {
	        thisArg: this
	      });

	      if (event.isDefaultPrevented()) {
	        return;
	      }

	      this.clearSubMenuTimeout();

	      if (this.hasSubMenu()) {
	        this.subMenuTimeout = setTimeout(function () {
	          this.showSubMenu();
	        }.bind(this), this.menuShowDelay);
	      } else {
	        this.subMenuTimeout = setTimeout(function () {
	          this.closeSiblings();
	        }.bind(this), this.menuShowDelay);
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "onItemMouseLeave",
	    value: function onItemMouseLeave(mouseEvent) {
	      if (this.isDisabled()) {
	        return;
	      }

	      var event = new main_core_events.BaseEvent({
	        data: {
	          mouseEvent: mouseEvent
	        }
	      });
	      main_core_events.EventEmitter.emit(this, 'onMouseLeave', event, {
	        thisArg: this
	      });

	      if (event.isDefaultPrevented()) {
	        return;
	      }

	      this.clearSubMenuTimeout();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "clearSubMenuTimeout",
	    value: function clearSubMenuTimeout() {
	      if (this.subMenuTimeout) {
	        clearTimeout(this.subMenuTimeout);
	      }

	      this.subMenuTimeout = null;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleSubMenuDestroy",
	    value: function handleSubMenuDestroy() {
	      this.subMenuWindow = null;
	    }
	  }]);
	  return MenuItem;
	}(main_core_events.EventEmitter);

	var _templateObject$2, _templateObject2$2;

	/**
	 * @memberof BX.Main
	 */
	var Menu = /*#__PURE__*/function () {
	  function Menu(options) {
	    babelHelpers.classCallCheck(this, Menu);

	    var _arguments = Array.prototype.slice.call(arguments),
	        id = _arguments[0],
	        bindElement = _arguments[1],
	        menuItems = _arguments[2],
	        params = _arguments[3];

	    if (main_core.Type.isPlainObject(options) && !bindElement && !menuItems && !params) {
	      params = options;
	      params.compatibleMode = false;
	      id = options.id;
	      bindElement = options.bindElement;
	      menuItems = options.items;

	      if (!main_core.Type.isStringFilled(id)) {
	        id = 'menu-popup-' + main_core.Text.getRandom();
	      }
	    }

	    this.id = id;
	    this.bindElement = bindElement;
	    /**
	     *
	     * @type {MenuItem[]}
	     */

	    this.menuItems = [];
	    this.itemsContainer = null;
	    this.params = params && babelHelpers["typeof"](params) === 'object' ? params : {};
	    this.parentMenuWindow = null;
	    this.parentMenuItem = null;

	    if (menuItems && main_core.Type.isArray(menuItems)) {
	      for (var i = 0; i < menuItems.length; i++) {
	        this.addMenuItemInternal(menuItems[i], null);
	      }
	    }

	    this.layout = {
	      menuContainer: null,
	      itemsContainer: null
	    };
	    this.popupWindow = this.__createPopup();
	  }
	  /**
	   * @private
	   */


	  babelHelpers.createClass(Menu, [{
	    key: "__createPopup",
	    value: function __createPopup() {
	      var domItems = [];

	      for (var i = 0; i < this.menuItems.length; i++) {
	        var item = this.menuItems[i];
	        var itemLayout = item.getLayout();
	        domItems.push(itemLayout.item);
	      }

	      var defaults = {
	        closeByEsc: false,
	        angle: false,
	        autoHide: true,
	        offsetTop: 1,
	        offsetLeft: 0,
	        animation: 'fading'
	      };
	      var options = Object.assign(defaults, this.params); //Override user params

	      options.noAllPaddings = true;
	      options.darkMode = false;
	      options.autoHideHandler = this.handleAutoHide.bind(this);
	      this.layout.itemsContainer = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"menu-popup-items\">", "</div>\n\t\t"])), domItems);
	      this.layout.menuContainer = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"menu-popup\">", "</div>\n\t\t"])), this.layout.itemsContainer);
	      this.itemsContainer = this.layout.itemsContainer;
	      options.content = this.layout.menuContainer; //Make internal event handlers first in the queue.

	      options.events = {
	        onClose: this.handlePopupClose.bind(this),
	        onDestroy: this.handlePopupDestroy.bind(this)
	      };
	      var id = options.compatibleMode === false ? this.getId() : 'menu-popup-' + this.getId();
	      var popup = new Popup(id, this.bindElement, options);

	      if (this.params && this.params.events) {
	        popup.subscribeFromOptions(this.params.events);
	      }

	      return popup;
	    }
	  }, {
	    key: "getPopupWindow",
	    value: function getPopupWindow() {
	      return this.popupWindow;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.getPopupWindow().show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.getPopupWindow().close();
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.getPopupWindow().destroy();
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      if (this.getPopupWindow().isShown()) {
	        this.close();
	      } else {
	        this.show();
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handlePopupClose",
	    value: function handlePopupClose() {
	      for (var i = 0; i < this.menuItems.length; i++) {
	        var item = this.menuItems[i];
	        item.closeSubMenu();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handlePopupDestroy",
	    value: function handlePopupDestroy() {
	      for (var i = 0; i < this.menuItems.length; i++) {
	        var item = this.menuItems[i];
	        item.destroySubMenu();
	      }
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "handleAutoHide",
	    value: function handleAutoHide(event) {
	      return !this.containsTarget(event.target);
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "containsTarget",
	    value: function containsTarget(target) {
	      var el = this.getPopupWindow().getPopupContainer();

	      if (this.getPopupWindow().isShown() && (target === el || el.contains(target))) {
	        return true;
	      }

	      return this.getMenuItems().some(function (item) {
	        return item.getSubMenu() && item.getSubMenu().containsTarget(target);
	      });
	    }
	  }, {
	    key: "setParentMenuWindow",
	    value: function setParentMenuWindow(parentMenu) {
	      if (parentMenu instanceof Menu) {
	        this.parentMenuWindow = parentMenu;
	      }
	    }
	  }, {
	    key: "getParentMenuWindow",
	    value: function getParentMenuWindow() {
	      return this.parentMenuWindow;
	    }
	  }, {
	    key: "getRootMenuWindow",
	    value: function getRootMenuWindow() {
	      var root = null;
	      var parent = this.getParentMenuWindow();

	      while (parent !== null) {
	        root = parent;
	        parent = parent.getParentMenuWindow();
	      }

	      return root;
	    }
	  }, {
	    key: "setParentMenuItem",
	    value: function setParentMenuItem(parentItem) {
	      if (parentItem instanceof MenuItem) {
	        this.parentMenuItem = parentItem;
	      }
	    }
	  }, {
	    key: "getParentMenuItem",
	    value: function getParentMenuItem() {
	      return this.parentMenuItem;
	    }
	  }, {
	    key: "addMenuItem",
	    value: function addMenuItem(menuItemJson, targetItemId) {
	      var menuItem = this.addMenuItemInternal(menuItemJson, targetItemId);

	      if (!menuItem) {
	        return null;
	      }

	      var itemLayout = menuItem.getLayout();
	      var targetItem = this.getMenuItem(targetItemId);

	      if (targetItem !== null) {
	        var targetLayout = targetItem.getLayout();
	        this.itemsContainer.insertBefore(itemLayout.item, targetLayout.item);
	      } else {
	        this.itemsContainer.appendChild(itemLayout.item);
	      }

	      return menuItem;
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "addMenuItemInternal",
	    value: function addMenuItemInternal(menuItemJson, targetItemId) {
	      if (!menuItemJson || !menuItemJson.delimiter && !main_core.Type.isStringFilled(menuItemJson.text) && !main_core.Type.isStringFilled(menuItemJson.html) && !main_core.Type.isElementNode(menuItemJson.html) || menuItemJson.id && this.getMenuItem(menuItemJson.id) !== null) {
	        return null;
	      }

	      if (main_core.Type.isNumber(this.params.menuShowDelay)) {
	        menuItemJson.menuShowDelay = this.params.menuShowDelay;
	      }

	      var menuItem = new MenuItem(menuItemJson);
	      menuItem.setMenuWindow(this);
	      var position = this.getMenuItemPosition(targetItemId);

	      if (position >= 0) {
	        this.menuItems.splice(position, 0, menuItem);
	      } else {
	        this.menuItems.push(menuItem);
	      }

	      return menuItem;
	    }
	  }, {
	    key: "removeMenuItem",
	    value: function removeMenuItem(itemId) {
	      var item = this.getMenuItem(itemId);

	      if (!item) {
	        return;
	      }

	      for (var position = 0; position < this.menuItems.length; position++) {
	        if (this.menuItems[position] === item) {
	          item.destroySubMenu();
	          this.menuItems.splice(position, 1);
	          break;
	        }
	      }

	      if (!this.menuItems.length) {
	        var menuWindow = item.getMenuWindow();

	        if (menuWindow) {
	          var parentMenuItem = menuWindow.getParentMenuItem();

	          if (parentMenuItem) {
	            parentMenuItem.destroySubMenu();
	          } else {
	            menuWindow.destroy();
	          }
	        }
	      }

	      item.layout.item.parentNode.removeChild(item.layout.item);
	      item.layout = {
	        item: null,
	        text: null
	      };
	    }
	  }, {
	    key: "getMenuItem",
	    value: function getMenuItem(itemId) {
	      for (var i = 0; i < this.menuItems.length; i++) {
	        if (this.menuItems[i].id && this.menuItems[i].id === itemId) {
	          return this.menuItems[i];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      return this.menuItems;
	    }
	  }, {
	    key: "getMenuItemPosition",
	    value: function getMenuItemPosition(itemId) {
	      if (itemId) {
	        for (var i = 0; i < this.menuItems.length; i++) {
	          if (this.menuItems[i].id && this.menuItems[i].id === itemId) {
	            return i;
	          }
	        }
	      }

	      return -1;
	    }
	  }, {
	    key: "getMenuContainer",
	    value: function getMenuContainer() {
	      return this.getPopupWindow().getPopupContainer();
	    }
	  }]);
	  return Menu;
	}();

	var MenuManager = /*#__PURE__*/function () {
	  /**
	   * @private
	   */

	  /**
	   * @private
	   */
	  function MenuManager() {
	    babelHelpers.classCallCheck(this, MenuManager);
	    throw new Error('You cannot make an instance of MenuManager.');
	  }

	  babelHelpers.createClass(MenuManager, null, [{
	    key: "show",
	    value: function show() {
	      if (this.currentItem !== null) {
	        this.currentItem.popupWindow.close();
	      }

	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      this.currentItem = this.create.apply(this, args);
	      this.currentItem.popupWindow.show();
	    }
	  }, {
	    key: "create",
	    value: function create(options) {
	      var menuId = null; //Compatibility

	      var bindElement = arguments[1];
	      var menuItems = arguments[2];
	      var params = arguments[3];

	      if (main_core.Type.isPlainObject(options) && !bindElement && !menuItems && !params) {
	        menuId = options.id;

	        if (!main_core.Type.isStringFilled(menuId)) {
	          throw new Error('BX.Main.Menu.create: "id" parameter is required.');
	        }
	      } else {
	        menuId = options;
	      }

	      if (!this.Data[menuId]) {
	        var menu = new Menu(options, bindElement, menuItems, params);
	        menu.getPopupWindow().subscribe('onDestroy', function () {
	          MenuManager.destroy(menuId);
	        });
	        this.Data[menuId] = menu;
	      }

	      return this.Data[menuId];
	    }
	  }, {
	    key: "getCurrentMenu",
	    value: function getCurrentMenu() {
	      return this.currentItem;
	    }
	  }, {
	    key: "getMenuById",
	    value: function getMenuById(id) {
	      return this.Data[id] ? this.Data[id] : null;
	    }
	    /**
	     * compatibility
	     * @private
	     */

	  }, {
	    key: "onPopupDestroy",
	    value: function onPopupDestroy(popupMenuWindow) {
	      this.destroy(popupMenuWindow.id);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(id) {
	      var menu = this.getMenuById(id);

	      if (menu) {
	        if (this.currentItem === menu) {
	          this.currentItem = null;
	        }

	        delete this.Data[id];
	        menu.getPopupWindow().destroy();
	      }
	    }
	  }]);
	  return MenuManager;
	}();

	babelHelpers.defineProperty(MenuManager, "Data", {});
	babelHelpers.defineProperty(MenuManager, "currentItem", null);

	/**
	 * @deprecated use Popup class instead: import { Popup } from 'main.popup'
	 */

	var PopupWindow = /*#__PURE__*/function (_Popup) {
	  babelHelpers.inherits(PopupWindow, _Popup);

	  function PopupWindow() {
	    babelHelpers.classCallCheck(this, PopupWindow);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupWindow).apply(this, arguments));
	  }

	  return PopupWindow;
	}(Popup);

	/**
	 * @deprecated use BX.UI.Button
	 */

	var PopupWindowButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(PopupWindowButton, _Button);

	  function PopupWindowButton() {
	    babelHelpers.classCallCheck(this, PopupWindowButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupWindowButton).apply(this, arguments));
	  }

	  return PopupWindowButton;
	}(Button);

	/**
	 * @deprecated use BX.UI.Button
	 */

	var ButtonLink = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(ButtonLink, _Button);

	  function ButtonLink(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, ButtonLink);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ButtonLink).call(this, params));
	    _this.buttonNode = main_core.Dom.create('span', {
	      props: {
	        className: 'popup-window-button popup-window-button-link' + (_this.className.length > 0 ? ' ' + _this.className : ''),
	        id: _this.id
	      },
	      text: _this.text,
	      events: _this.contextEvents
	    });
	    return _this;
	  }

	  return ButtonLink;
	}(Button);

	/**
	 * @deprecated use BX.UI.Button
	 */

	var PopupWindowButtonLink = /*#__PURE__*/function (_ButtonLink) {
	  babelHelpers.inherits(PopupWindowButtonLink, _ButtonLink);

	  function PopupWindowButtonLink() {
	    babelHelpers.classCallCheck(this, PopupWindowButtonLink);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupWindowButtonLink).apply(this, arguments));
	  }

	  return PopupWindowButtonLink;
	}(ButtonLink);

	/**
	 * @deprecated use BX.UI.Button
	 */

	var CustomButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(CustomButton, _Button);

	  function CustomButton(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, CustomButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CustomButton).call(this, params));
	    _this.buttonNode = main_core.Dom.create('span', {
	      props: {
	        className: _this.className.length > 0 ? _this.className : '',
	        id: _this.id
	      },
	      events: _this.contextEvents,
	      text: _this.text
	    });
	    return _this;
	  }

	  return CustomButton;
	}(Button);

	/**
	 * @deprecated use BX.UI.Button
	 */

	var PopupWindowCustomButton = /*#__PURE__*/function (_CustomButton) {
	  babelHelpers.inherits(PopupWindowCustomButton, _CustomButton);

	  function PopupWindowCustomButton() {
	    babelHelpers.classCallCheck(this, PopupWindowCustomButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupWindowCustomButton).apply(this, arguments));
	  }

	  return PopupWindowCustomButton;
	}(CustomButton);

	/**
	 * @deprecated use Menu class instead: import { Menu } from 'main.popup'
	 */

	var PopupMenuWindow = /*#__PURE__*/function (_Menu) {
	  babelHelpers.inherits(PopupMenuWindow, _Menu);

	  function PopupMenuWindow() {
	    babelHelpers.classCallCheck(this, PopupMenuWindow);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupMenuWindow).apply(this, arguments));
	  }

	  return PopupMenuWindow;
	}(Menu);

	/**
	 * @deprecated use Menu.Item class instead: import { MenuItem } from 'main.popup'
	 */

	var PopupMenuItem = /*#__PURE__*/function (_MenuItem) {
	  babelHelpers.inherits(PopupMenuItem, _MenuItem);

	  function PopupMenuItem() {
	    babelHelpers.classCallCheck(this, PopupMenuItem);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PopupMenuItem).apply(this, arguments));
	  }

	  return PopupMenuItem;
	}(MenuItem);

	/**
	 * @deprecated
	 */

	var InputPopup = /*#__PURE__*/function () {
	  function InputPopup(params) {
	    babelHelpers.classCallCheck(this, InputPopup);
	    this.id = params.id || 'bx-inp-popup-' + Math.round(Math.random() * 1000000);
	    this.handler = params.handler || false;
	    this.values = params.values || false;
	    this.pInput = params.input;
	    this.bValues = !!this.values;
	    this.defaultValue = params.defaultValue || '';
	    this.openTitle = params.openTitle || '';
	    this.className = params.className || '';
	    this.noMRclassName = params.noMRclassName || 'ec-no-rm';
	    this.emptyClassName = params.noMRclassName || 'ec-label';

	    var _this = this;

	    this.curInd = false;

	    if (this.bValues) {
	      this.pInput.onfocus = this.pInput.onclick = function (e) {
	        if (this.value == _this.defaultValue) {
	          this.value = '';
	          this.className = _this.className;
	        }

	        _this.ShowPopup();

	        return e.preventDefault();
	      };

	      this.pInput.onblur = function () {
	        if (_this.bShowed) {
	          setTimeout(function () {
	            _this.ClosePopup(true);
	          }, 200);
	        }

	        _this.OnChange();
	      };
	    } else {
	      this.pInput.className = this.noMRclassName;
	      this.pInput.onblur = this.OnChange.bind(this);
	    }
	  }

	  babelHelpers.createClass(InputPopup, [{
	    key: "ShowPopup",
	    value: function ShowPopup() {
	      if (this.bShowed) {
	        return;
	      }

	      var _this = this;

	      if (!this.oPopup) {
	        var pWnd = main_core.Dom.create('DIV', {
	          props: {
	            className: 'bxecpl-loc-popup ' + this.className
	          }
	        });

	        for (var i = 0, l = this.values.length; i < l; i++) {
	          var pRow = pWnd.appendChild(main_core.Dom.create('DIV', {
	            props: {
	              id: 'bxecmr_' + i
	            },
	            text: this.values[i].NAME,
	            events: {
	              mouseover: function mouseover() {
	                main_core.Dom.addClass(this, 'bxecplloc-over');
	              },
	              mouseout: function mouseout() {
	                main_core.Dom.removeClass(this, 'bxecplloc-over');
	              },
	              click: function click() {
	                var ind = this.id.substr('bxecmr_'.length);
	                _this.pInput.value = _this.values[ind].NAME;
	                _this.curInd = ind;

	                _this.OnChange();

	                _this.ClosePopup(true);
	              }
	            }
	          }));

	          if (this.values[i].DESCRIPTION) {
	            pRow.title = this.values[i].DESCRIPTION;
	          }

	          if (this.values[i].CLASS_NAME) {
	            main_core.Dom.addClass(pRow, this.values[i].CLASS_NAME);
	          }

	          if (this.values[i].URL) {
	            pRow.appendChild(main_core.Dom.create('a', {
	              props: {
	                href: this.values[i].URL,
	                className: 'bxecplloc-view',
	                target: '_blank',
	                title: this.openTitle
	              }
	            }));
	          }
	        }

	        this.oPopup = new Popup(this.id, this.pInput, {
	          autoHide: true,
	          offsetTop: 1,
	          offsetLeft: 0,
	          lightShadow: true,
	          closeByEsc: true,
	          content: pWnd,
	          events: {
	            onClose: this.ClosePopup.bind(this)
	          }
	        });
	      }

	      this.oPopup.show();
	      this.pInput.select();
	      this.bShowed = true;
	      main_core_events.EventEmitter.emit(this, 'onInputPopupShow', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));
	    }
	  }, {
	    key: "ClosePopup",
	    value: function ClosePopup(bClosePopup) {
	      this.bShowed = false;

	      if (this.pInput.value === '') {
	        this.OnChange();
	      }

	      main_core_events.EventEmitter.emit(this, 'onInputPopupClose', new main_core_events.BaseEvent({
	        compatData: [this]
	      }));

	      if (bClosePopup === true) {
	        this.oPopup.close();
	      }
	    }
	  }, {
	    key: "OnChange",
	    value: function OnChange() {
	      var val = this.pInput.value;

	      if (this.bValues) {
	        if (this.pInput.value == '' || this.pInput.value == this.defaultValue) {
	          this.pInput.value = this.defaultValue;
	          this.pInput.className = this.emptyClassName;
	          val = '';
	        } else {
	          this.pInput.className = '';
	        }
	      }

	      if (isNaN(parseInt(this.curInd)) || this.curInd !== false && val != this.values[this.curInd].NAME) {
	        this.curInd = false;
	      } else {
	        this.curInd = parseInt(this.curInd);
	      }

	      main_core_events.EventEmitter.emit(this, 'onInputPopupChanged', new main_core_events.BaseEvent({
	        compatData: [this, this.curInd, val]
	      }));

	      if (this.handler && typeof this.handler == 'function') {
	        this.handler({
	          ind: this.curInd,
	          value: val
	        });
	      }
	    }
	  }, {
	    key: "Set",
	    value: function Set(ind, val, bOnChange) {
	      this.curInd = ind;

	      if (this.curInd !== false) {
	        this.pInput.value = this.values[this.curInd].NAME;
	      } else {
	        this.pInput.value = val;
	      }

	      if (bOnChange !== false) {
	        this.OnChange();
	      }
	    }
	  }, {
	    key: "Get",
	    value: function Get(ind) {
	      var id = false;

	      if (typeof ind == 'undefined') {
	        ind = this.curInd;
	      }

	      if (ind !== false && this.values[ind]) {
	        id = this.values[ind].ID;
	      }

	      return id;
	    }
	  }, {
	    key: "GetIndex",
	    value: function GetIndex(id) {
	      for (var i = 0, l = this.values.length; i < l; i++) {
	        if (this.values[i].ID == id) {
	          return i;
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "Deactivate",
	    value: function Deactivate(bDeactivate) {
	      if (this.pInput.value == '' || this.pInput.value == this.defaultValue) {
	        if (bDeactivate) {
	          this.pInput.value = '';
	          this.pInput.className = this.noMRclassName;
	        } else if (this.oEC.bUseMR) {
	          this.pInput.value = this.defaultValue;
	          this.pInput.className = this.emptyClassName;
	        }
	      }

	      this.pInput.disabled = bDeactivate;
	    }
	  }]);
	  return InputPopup;
	}();

	/*

	//ES6
	import { Popup, PopupManager } from 'main.popup';
	const popup = new Popup();
	PopupManager.create();

	//ES5
	var popup = new BX.Main.Popup();
	BX.Main.PopupManager.create();

	//ES6
	import { Menu, MenuItem, MenuManager } from 'main.popup';
	const menu = new Menu();
	const item = new MenuItem();
	MenuManager.create();

	//ES5
	var menu = new BX.Main.Menu();
	var item = new BX.Main.MenuItem();
	BX.Main.MenuManager.create();

	 */
	var BX = main_core.Reflection.namespace('BX');
	/** @deprecated use BX.Main.Popup or import { Popup } from 'main.popup' */

	BX.PopupWindow = Popup;
	/** @deprecated use BX.Main.PopupManager or import { PopupManager } from 'main.popup' */

	BX.PopupWindowManager = PopupManager;
	/** @deprecated use BX.Main.Menu or import { Menu } from 'main.popup' */

	BX.PopupMenuWindow = Menu;
	/** @deprecated use BX.Main.MenuManager or import { MenuManager } from 'main.popup' */

	BX.PopupMenu = MenuManager;
	/** @deprecated use BX.Main.MenuItem or import { MenuItem } from 'main.popup' */

	BX.PopupMenuItem = MenuItem;
	/** @deprecated use BX.UI.Button */

	BX.PopupWindowButton = Button;
	/** @deprecated use BX.UI.Button */

	BX.PopupWindowButtonLink = ButtonLink;
	/** @deprecated use BX.UI.Button */

	BX.PopupWindowCustomButton = CustomButton;
	/** @deprecated use another API */

	window.BXInputPopup = InputPopup;

	exports.Popup = Popup;
	exports.Menu = Menu;
	exports.MenuItem = MenuItem;
	exports.PopupManager = PopupManager;
	exports.MenuManager = MenuManager;
	exports.PopupWindow = PopupWindow;
	exports.PopupMenuWindow = PopupMenuWindow;
	exports.PopupMenuItem = PopupMenuItem;
	exports.PopupWindowManager = PopupManager;
	exports.PopupMenu = MenuManager;
	exports.PopupWindowButton = PopupWindowButton;
	exports.PopupWindowButtonLink = PopupWindowButtonLink;
	exports.PopupWindowCustomButton = PopupWindowCustomButton;

}((this.BX.Main = this.BX.Main || {}),BX,BX.Event,BX));
//# sourceMappingURL=main.popup.bundle.js.map
