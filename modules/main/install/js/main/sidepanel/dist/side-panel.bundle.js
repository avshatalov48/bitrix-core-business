/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_cache,main_core_zIndexManager,main_core,main_core_events,main_popup,main_pageobject) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _data = /*#__PURE__*/new WeakMap();
	let Dictionary = /*#__PURE__*/function () {
	  function Dictionary(data) {
	    babelHelpers.classCallCheck(this, Dictionary);
	    _classPrivateFieldInitSpec(this, _data, {
	      writable: true,
	      value: null
	    });
	    if (!main_core.Type.isPlainObject(data)) {
	      throw new TypeError('The argument must be a plain object.');
	    }
	    babelHelpers.classPrivateFieldSet(this, _data, data);
	  }
	  babelHelpers.createClass(Dictionary, [{
	    key: "set",
	    value: function set(key, value) {
	      if (!main_core.Type.isStringFilled(key)) {
	        throw new Error('The \'key\' must be a string.');
	      }
	      babelHelpers.classPrivateFieldGet(this, _data)[key] = value;
	    }
	  }, {
	    key: "get",
	    value: function get(key) {
	      return babelHelpers.classPrivateFieldGet(this, _data)[key];
	    }
	  }, {
	    key: "delete",
	    value: function _delete(key) {
	      delete babelHelpers.classPrivateFieldGet(this, _data)[key];
	    }
	  }, {
	    key: "has",
	    value: function has(key) {
	      return key in babelHelpers.classPrivateFieldGet(this, _data);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      babelHelpers.classPrivateFieldSet(this, _data, {});
	    }
	  }, {
	    key: "entries",
	    value: function entries() {
	      return babelHelpers.classPrivateFieldGet(this, _data);
	    }
	  }]);
	  return Dictionary;
	}();

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _handleClick = /*#__PURE__*/new WeakSet();
	let Label = /*#__PURE__*/function () {
	  function Label(slider, labelOptions) {
	    babelHelpers.classCallCheck(this, Label);
	    _classPrivateMethodInitSpec(this, _handleClick);
	    babelHelpers.defineProperty(this, "slider", null);
	    babelHelpers.defineProperty(this, "color", null);
	    babelHelpers.defineProperty(this, "bgColor", null);
	    babelHelpers.defineProperty(this, "className", '');
	    babelHelpers.defineProperty(this, "iconClass", '');
	    babelHelpers.defineProperty(this, "iconTitle", '');
	    babelHelpers.defineProperty(this, "onclick", null);
	    babelHelpers.defineProperty(this, "text", null);
	    babelHelpers.defineProperty(this, "hidden", false);
	    babelHelpers.defineProperty(this, "visible", true);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.slider = slider;
	    const options = main_core.Type.isPlainObject(labelOptions) ? labelOptions : {};
	    this.hidden = main_core.Type.isBoolean(options.hidden) ? options.hidden : this.hidden;
	    this.visible = main_core.Type.isBoolean(options.visible) ? options.visible : this.visible;
	    this.setColor(options.color);
	    this.setBgColor(options.bgColor);
	    this.setText(options.text);
	    this.setClassName(options.className);
	    this.setIconClass(options.iconClass);
	    this.setIconTitle(options.iconTitle);
	    this.setOnclick(options.onclick);
	  }
	  babelHelpers.createClass(Label, [{
	    key: "getContainer",
	    value: function getContainer() {
	      return this.cache.remember('container', () => {
	        const classes = ['side-panel-label'];
	        if (this.getClassName()) {
	          classes.push(this.getClassName());
	        }
	        if (this.isHidden()) {
	          classes.push('--hidden');
	        }
	        if (this.isVisible()) {
	          classes.push('--visible');
	        }
	        return main_core.Dom.create('div', {
	          props: {
	            className: classes.join(' ')
	          },
	          children: [this.getIconBox(), this.getTextContainer()],
	          events: {
	            click: _classPrivateMethodGet(this, _handleClick, _handleClick2).bind(this)
	          }
	        });
	      });
	    }
	  }, {
	    key: "adjustLayout",
	    value: function adjustLayout() {
	      const overlayRect = this.getSlider().getOverlay().getBoundingClientRect();
	      const containerRect = this.getSlider().getContainer().getBoundingClientRect();
	      const maxWidth = containerRect.left - overlayRect.left;
	      if (maxWidth <= this.getSlider().getMinLeftBoundary()) {
	        this.hideText();
	      } else {
	        this.showText();
	      }
	      main_core.Dom.style(this.getContainer(), 'max-width', `${maxWidth - this.constructor.MIN_LEFT_OFFSET}px`);
	    }
	  }, {
	    key: "getIconBox",
	    value: function getIconBox() {
	      return this.cache.remember('icon-box', () => {
	        return main_core.Dom.create('div', {
	          props: {
	            className: 'side-panel-label-icon-box'
	          },
	          children: [this.getIconContainer()]
	        });
	      });
	    }
	  }, {
	    key: "getIconContainer",
	    value: function getIconContainer() {
	      return this.cache.remember('icon-container', () => {
	        return main_core.Dom.create('div', {
	          props: {
	            className: `side-panel-label-icon ${this.getIconClass()}`
	          }
	        });
	      });
	    }
	  }, {
	    key: "showIcon",
	    value: function showIcon() {
	      main_core.Dom.removeClass(this.getContainer(), 'side-panel-label-icon--hide');
	    }
	  }, {
	    key: "hideIcon",
	    value: function hideIcon() {
	      main_core.Dom.addClass(this.getContainer(), 'side-panel-label-icon--hide');
	    }
	  }, {
	    key: "darkenIcon",
	    value: function darkenIcon() {
	      main_core.Dom.addClass(this.getContainer(), 'side-panel-label-icon--darken');
	    }
	  }, {
	    key: "lightenIcon",
	    value: function lightenIcon() {
	      main_core.Dom.removeClass(this.getContainer(), 'side-panel-label-icon--darken');
	    }
	  }, {
	    key: "hideText",
	    value: function hideText() {
	      main_core.Dom.addClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	    }
	  }, {
	    key: "showText",
	    value: function showText() {
	      main_core.Dom.removeClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	    }
	  }, {
	    key: "isTextHidden",
	    value: function isTextHidden() {
	      return main_core.Dom.hasClass(this.getTextContainer(), 'side-panel-label-text-hidden');
	    }
	  }, {
	    key: "getTextContainer",
	    value: function getTextContainer() {
	      return this.cache.remember('text-container', () => {
	        return main_core.Dom.create('span', {
	          props: {
	            className: 'side-panel-label-text'
	          }
	        });
	      });
	    }
	  }, {
	    key: "setColor",
	    value: function setColor(color) {
	      if (main_core.Type.isStringFilled(color) || color === null) {
	        this.color = color;
	        main_core.Dom.style(this.getTextContainer(), 'color', this.color);
	        main_core.Dom.style(this.getIconContainer(), '--ui-icon-set__icon-color', this.color);
	      }
	    }
	  }, {
	    key: "getColor",
	    value: function getColor() {
	      return this.color;
	    }
	  }, {
	    key: "setBgColor",
	    value: function setBgColor(color, opacity) {
	      let bgColor = main_core.Type.isArray(color) ? color[0] : color;
	      let alfa = main_core.Type.isArray(color) ? color[1] : opacity;
	      if (main_core.Type.isStringFilled(bgColor)) {
	        const matches = bgColor.match(/^#([\dA-Fa-f]{6}|[\dA-Fa-f]{3})$/);
	        if (matches) {
	          let hex = matches[1];
	          if (hex.length === 3) {
	            hex = hex.replaceAll(/([\da-f])/gi, '$1$1');
	          }
	          alfa = main_core.Type.isNumber(alfa) && alfa >= 0 && alfa <= 100 ? alfa : 95;
	          const alfaHex = `0${Math.round(255 * (alfa / 100)).toString(16)}`.slice(-2).toUpperCase();
	          bgColor = `#${hex}${alfaHex}`;
	        }
	        this.bgColor = bgColor;
	        main_core.Dom.style(this.getContainer(), '--ui-color', bgColor);
	        if (this.getColor() === null) {
	          main_core.Dom.style(this.getIconContainer(), '--ui-icon-set__icon-color', '#fff');
	        }
	      } else if (bgColor === null) {
	        this.bgColor = null;
	        main_core.Dom.style(this.getContainer(), '--ui-color', null);
	        main_core.Dom.style(this.getIconContainer(), '--ui-icon-set__icon-color', null);
	      }
	    }
	  }, {
	    key: "getBgColor",
	    value: function getBgColor() {
	      return this.bgColor;
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      if (main_core.Type.isStringFilled(text)) {
	        this.text = text;
	        this.getTextContainer().textContent = text;
	      } else if (text === null) {
	        this.text = text;
	        this.getTextContainer().textContent = '';
	      }
	    }
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	  }, {
	    key: "setClassName",
	    value: function setClassName(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        main_core.Dom.removeClass(this.getContainer(), this.className);
	        this.className = className;
	        main_core.Dom.addClass(this.getContainer(), this.className);
	      } else if (className === null) {
	        main_core.Dom.removeClass(this.getContainer(), this.className);
	        this.className = className;
	      }
	    }
	  }, {
	    key: "getClassName",
	    value: function getClassName() {
	      return this.className;
	    }
	  }, {
	    key: "setIconClass",
	    value: function setIconClass(iconClass) {
	      if (main_core.Type.isStringFilled(iconClass)) {
	        main_core.Dom.removeClass(this.getIconContainer(), this.iconClass);
	        this.iconClass = iconClass;
	        main_core.Dom.addClass(this.getIconContainer(), this.iconClass);
	      } else if (iconClass === null) {
	        main_core.Dom.removeClass(this.getIconContainer(), this.iconClass);
	        this.iconClass = iconClass;
	      }
	    }
	  }, {
	    key: "getIconClass",
	    value: function getIconClass() {
	      return this.iconClass;
	    }
	  }, {
	    key: "setIconTitle",
	    value: function setIconTitle(iconTitle) {
	      if (main_core.Type.isStringFilled(iconTitle) || iconTitle === null) {
	        main_core.Dom.attr(this.getIconBox(), 'title', iconTitle);
	        this.iconTitle = iconTitle;
	      }
	    }
	  }, {
	    key: "getIconTitle",
	    value: function getIconTitle() {
	      return this.iconTitle;
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.hidden = true;
	      main_core.Dom.addClass(this.getContainer(), '--hidden');
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      this.hidden = false;
	      main_core.Dom.removeClass(this.getContainer(), '--hidden');
	    }
	  }, {
	    key: "isVisible",
	    value: function isVisible() {
	      return this.visible;
	    }
	  }, {
	    key: "setVisible",
	    value: function setVisible(isVisible = true) {
	      main_core.Dom.toggleClass(this.getContainer(), '--visible', isVisible);
	    }
	  }, {
	    key: "setOnclick",
	    value: function setOnclick(fn) {
	      if (main_core.Type.isFunction(fn) || fn === null) {
	        this.onclick = fn;
	      }
	    }
	  }, {
	    key: "getOnclick",
	    value: function getOnclick() {
	      return this.onclick;
	    }
	  }, {
	    key: "getSlider",
	    value: function getSlider() {
	      return this.slider;
	    }
	  }, {
	    key: "moveAt",
	    value: function moveAt(position) {
	      if (main_core.Type.isNumber(position) && position >= 0) {
	        main_core.Dom.style(this.getSlider().getLabelsContainer(), 'top', `${this.constructor.MIN_TOP_OFFSET + position * this.constructor.INTERVAL_TOP_OFFSET}px`);
	      }
	    }
	  }]);
	  return Label;
	}();
	function _handleClick2(event) {
	  event.stopPropagation();
	  const fn = this.getOnclick();
	  if (fn) {
	    fn(this, this.getSlider());
	  }
	}
	babelHelpers.defineProperty(Label, "MIN_LEFT_OFFSET", 25);
	babelHelpers.defineProperty(Label, "MIN_TOP_OFFSET", 17);
	babelHelpers.defineProperty(Label, "INTERVAL_TOP_OFFSET", 50);

	let SliderEvent = /*#__PURE__*/function () {
	  function SliderEvent() {
	    babelHelpers.classCallCheck(this, SliderEvent);
	    this.slider = null;
	    this.action = true;
	    this.name = null;
	  }
	  babelHelpers.createClass(SliderEvent, [{
	    key: "allowAction",
	    value: function allowAction() {
	      this.action = true;
	    }
	  }, {
	    key: "denyAction",
	    value: function denyAction() {
	      this.action = false;
	    }
	  }, {
	    key: "isActionAllowed",
	    value: function isActionAllowed() {
	      return this.action;
	    }
	    /**
	     * @deprecated use getSlider method
	     */
	  }, {
	    key: "getSliderPage",
	    value: function getSliderPage() {
	      return this.slider;
	    }
	  }, {
	    key: "getSlider",
	    value: function getSlider() {
	      return this.slider;
	    }
	  }, {
	    key: "setSlider",
	    value: function setSlider(slider) {
	      if (slider instanceof Slider) {
	        this.slider = slider;
	      }
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.name;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (main_core.Type.isStringFilled(name)) {
	        this.name = name;
	      }
	    }
	  }, {
	    key: "getFullName",
	    value: function getFullName() {
	      return Slider.getEventFullName(this.getName());
	    }
	  }]);
	  return SliderEvent;
	}();

	let MessageEvent = /*#__PURE__*/function (_SliderEvent) {
	  babelHelpers.inherits(MessageEvent, _SliderEvent);
	  function MessageEvent(eventOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, MessageEvent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MessageEvent).call(this));
	    const options = main_core.Type.isPlainObject(eventOptions) ? eventOptions : {};
	    if (!(options.sender instanceof Slider)) {
	      throw new TypeError('\'sender\' is not an instance of BX.SidePanel.Slider');
	    }
	    _this.setName('onMessage');
	    _this.setSlider(options.slider);
	    _this.sender = options.sender;
	    _this.data = 'data' in options ? options.data : null;
	    _this.eventId = main_core.Type.isStringFilled(options.eventId) ? options.eventId : null;
	    return _this;
	  }
	  babelHelpers.createClass(MessageEvent, [{
	    key: "getSlider",
	    value: function getSlider() {
	      return this.slider;
	    }
	  }, {
	    key: "getSender",
	    value: function getSender() {
	      return this.sender;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "getEventId",
	    value: function getEventId() {
	      return this.eventId;
	    }
	  }]);
	  return MessageEvent;
	}(SliderEvent);

	let _ = t => t,
	  _t,
	  _t2;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$2(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _refs = /*#__PURE__*/new WeakMap();
	var _startPosition = /*#__PURE__*/new WeakMap();
	var _startAnimationState = /*#__PURE__*/new WeakMap();
	var _endAnimationState = /*#__PURE__*/new WeakMap();
	var _currentAnimationState = /*#__PURE__*/new WeakMap();
	var _outerBoundary = /*#__PURE__*/new WeakMap();
	var _hideToolbarOnOpen = /*#__PURE__*/new WeakMap();
	var _designSystemContext = /*#__PURE__*/new WeakMap();
	var _zIndexComponent = /*#__PURE__*/new WeakMap();
	var _autoOffset = /*#__PURE__*/new WeakMap();
	var _subscribeEvents = /*#__PURE__*/new WeakSet();
	var _getAnimationState = /*#__PURE__*/new WeakSet();
	var _calculateOuterBoundary = /*#__PURE__*/new WeakSet();
	let Slider = /*#__PURE__*/function () {
	  function Slider(url, sliderOptions) {
	    babelHelpers.classCallCheck(this, Slider);
	    _classPrivateMethodInitSpec$1(this, _calculateOuterBoundary);
	    _classPrivateMethodInitSpec$1(this, _getAnimationState);
	    _classPrivateMethodInitSpec$1(this, _subscribeEvents);
	    _classPrivateFieldInitSpec$1(this, _refs, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    _classPrivateFieldInitSpec$1(this, _startPosition, {
	      writable: true,
	      value: 'right'
	    });
	    _classPrivateFieldInitSpec$1(this, _startAnimationState, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(this, _endAnimationState, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(this, _currentAnimationState, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(this, _outerBoundary, {
	      writable: true,
	      value: {}
	    });
	    _classPrivateFieldInitSpec$1(this, _hideToolbarOnOpen, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec$1(this, _designSystemContext, {
	      writable: true,
	      value: '--ui-context-content-light'
	    });
	    _classPrivateFieldInitSpec$1(this, _zIndexComponent, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$1(this, _autoOffset, {
	      writable: true,
	      value: true
	    });
	    const options = main_core.Type.isPlainObject(sliderOptions) ? sliderOptions : {};
	    this.contentCallback = main_core.Type.isFunction(options.contentCallback) ? options.contentCallback : null;
	    this.contentCallbackInvoved = false;
	    this.contentClassName = main_core.Type.isStringFilled(options.contentClassName) ? options.contentClassName : null;
	    this.containerClassName = main_core.Type.isStringFilled(options.containerClassName) ? options.containerClassName : null;
	    this.overlayClassName = main_core.Type.isStringFilled(options.overlayClassName) ? options.overlayClassName : null;
	    this.url = this.contentCallback ? url : this.refineUrl(url);
	    this.offset = null;
	    this.hideControls = options.hideControls === true;
	    this.width = main_core.Type.isNumber(options.width) ? options.width : null;
	    this.cacheable = options.cacheable !== false;
	    this.autoFocus = options.autoFocus !== false;
	    this.printable = options.printable === true;
	    this.allowChangeHistory = main_core.Type.isBoolean(options.allowChangeHistory) ? options.allowChangeHistory : null;
	    this.allowChangeTitle = main_core.Type.isBoolean(options.allowChangeTitle) ? options.allowChangeTitle : null;
	    this.allowCrossOrigin = options.allowCrossOrigin === true;
	    this.data = new Dictionary(main_core.Type.isPlainObject(options.data) ? options.data : {});
	    this.customLeftBoundary = null;
	    this.customRightBoundary = null;
	    this.setCustomLeftBoundary(options.customLeftBoundary);
	    this.setCustomRightBoundary(options.customRightBoundary);
	    this.title = null;
	    this.setTitle(options.title);
	    /**
	     *
	     * @type {HTMLIFrameElement}
	     */
	    this.iframe = null;
	    this.iframeSrc = null;
	    this.iframeId = null;
	    this.requestMethod = main_core.Type.isStringFilled(options.requestMethod) && options.requestMethod.toLowerCase() === 'post' ? 'post' : 'get';
	    this.requestParams = main_core.Type.isPlainObject(options.requestParams) ? options.requestParams : {};
	    this.opened = false;
	    this.hidden = false;
	    this.destroyed = false;
	    this.loaded = false;
	    this.loadedCnt = 0;
	    this.minimizing = false;
	    this.maximizing = false;
	    this.handleFrameKeyDown = this.handleFrameKeyDown.bind(this);
	    this.handleFrameFocus = this.handleFrameFocus.bind(this);
	    this.handleFrameUnload = this.handleFrameUnload.bind(this);
	    this.handlePopupInit = this.handlePopupInit.bind(this);
	    this.handleCrossOriginWindowMessage = this.handleCrossOriginWindowMessage.bind(this);
	    this.layout = {
	      overlay: null,
	      container: null,
	      loader: null,
	      content: null
	    };
	    this.loader = main_core.Type.isStringFilled(options.loader) || main_core.Type.isElementNode(options.loader) ? options.loader : main_core.Type.isStringFilled(options.typeLoader) ? options.typeLoader : 'default-loader';
	    this.animation = null;
	    this.animationDuration = main_core.Type.isNumber(options.animationDuration) ? options.animationDuration : 200;
	    this.overlayBgColor = main_core.Type.isStringFilled(options.overlayBgColor) && /^#[\dA-Za-f]{6}$/.test(options.overlayBgColor) ? options.overlayBgColor : '#000000';
	    this.overlayOpacity = main_core.Type.isNumber(options.overlayOpacity) ? Math.min(Math.max(options.overlayOpacity, 0), 100) : 40;
	    this.overlayBgCallback = main_core.Type.isFunction(options.overlayBgCallback) ? options.overlayBgCallback : null;
	    babelHelpers.classPrivateFieldSet(this, _startPosition, ['right', 'bottom', 'top'].includes(options.startPosition) ? options.startPosition : babelHelpers.classPrivateFieldGet(this, _startPosition));
	    babelHelpers.classPrivateFieldSet(this, _outerBoundary, main_core.Type.isPlainObject(options.outerBoundary) ? options.outerBoundary : {});
	    babelHelpers.classPrivateFieldSet(this, _startAnimationState, _classPrivateMethodGet$1(this, _getAnimationState, _getAnimationState2).call(this, 'start'));
	    babelHelpers.classPrivateFieldSet(this, _endAnimationState, _classPrivateMethodGet$1(this, _getAnimationState, _getAnimationState2).call(this, 'end'));
	    babelHelpers.classPrivateFieldSet(this, _currentAnimationState, null);
	    this.overlayAnimation = false;
	    this.animationName = 'sliding';
	    this.animationOptions = {};
	    this.minimizeOptions = null;
	    this.setMinimizeOptions(options.minimizeOptions);
	    this.setToolbarOnOpen(options.hideToolbarOnOpen);
	    this.setDesignSystemContext(options.designSystemContext);
	    this.setAutoOffset(options.autoOffset);
	    this.label = new Label(this, {
	      className: '--close-label --ui-hoverable',
	      iconClass: 'side-panel-label-icon-close ui-icon-set --cross-l',
	      iconTitle: main_core.Loc.getMessage('MAIN_SIDEPANEL_CLOSE'),
	      onclick(label, slider) {
	        slider.close();
	      }
	    });
	    const labelOptions = main_core.Type.isPlainObject(options.label) ? options.label : {};
	    this.label.setText(labelOptions.text);
	    this.label.setColor(labelOptions.color);
	    this.label.setBgColor(labelOptions.bgColor, labelOptions.opacity);
	    this.minimizeLabel = new Label(this, {
	      className: '--ui-hoverable',
	      iconClass: 'side-panel-label-icon-minimize ui-icon-set --o-minimize',
	      iconTitle: main_core.Loc.getMessage('MAIN_SIDEPANEL_MINIMIZE'),
	      onclick: (label, slider) => {
	        if (this.isLoaded()) {
	          this.minimize();
	        }
	      },
	      visible: this.areMinimizeOptionsValid(this.minimizeOptions)
	    });
	    this.newWindowLabel = null;
	    this.copyLinkLabel = null;
	    this.printLabel = null;
	    if (options.newWindowLabel === true && (this.canChangeHistory() || main_core.Type.isStringFilled(options.newWindowUrl))) {
	      this.newWindowLabel = new Label(this, {
	        className: '--ui-hoverable',
	        iconClass: 'side-panel-label-icon-new-window ui-icon-set --go-to-l',
	        iconTitle: main_core.Loc.getMessage('MAIN_SIDEPANEL_NEW_WINDOW'),
	        onclick(label, slider) {
	          const newWindowUrl = main_core.Type.isStringFilled(options.newWindowUrl) ? options.newWindowUrl : slider.getUrl();
	          Object.assign(document.createElement('a'), {
	            target: '_blank',
	            href: newWindowUrl
	          }).click();
	        }
	      });
	    }
	    if (options.copyLinkLabel === true && (this.canChangeHistory() || main_core.Type.isStringFilled(options.newWindowUrl))) {
	      this.copyLinkLabel = new Label(this, {
	        className: '--ui-hoverable',
	        iconClass: 'side-panel-label-icon-copy-link ui-icon-set --o-link',
	        iconTitle: main_core.Loc.getMessage('MAIN_SIDEPANEL_COPY_LINK')
	      });
	      BX.clipboard.bindCopyClick(this.copyLinkLabel.getIconBox(), {
	        text: () => {
	          const link = document.createElement('a');
	          link.href = main_core.Type.isStringFilled(options.newWindowUrl) ? options.newWindowUrl : this.getUrl();
	          return link.href;
	        }
	      });
	    }
	    this.printLabel = new Label(this, {
	      hidden: !this.isPrintable(),
	      className: '--side-panel-label-print --ui-hoverable',
	      iconClass: 'side-panel-label-icon-print ui-icon-set --o-printer',
	      iconTitle: main_core.Loc.getMessage('MAIN_SIDEPANEL_PRINT'),
	      onclick: this.handlePrintBtnClick.bind(this)
	    });

	    // Compatibility
	    if (this.url.includes('crm.activity.planner/slider.php') && options.events && main_core.Type.isFunction(options.events.onOpen) && options.events.compatibleEvents !== false) {
	      const onOpen = options.events.onOpen;
	      delete options.events.onOpen;
	      options.events.onLoad = function (event) {
	        onOpen(event.getSlider());
	      };
	    }
	    [options.events].flat().forEach(events => _classPrivateMethodGet$1(this, _subscribeEvents, _subscribeEvents2).call(this, events));
	  }
	  babelHelpers.createClass(Slider, [{
	    key: "open",
	    value: function open() {
	      if (this.isOpen()) {
	        return false;
	      }
	      if (!this.canOpen()) {
	        return false;
	      }
	      if (this.isDestroyed()) {
	        return false;
	      }
	      if (this.maximizing) {
	        this.fireEvent('onMaximizeStart');
	      }
	      this.createLayout();
	      main_core.Dom.removeClass(this.getOverlay(), '--closing');
	      main_core.Dom.addClass(this.getOverlay(), '--opening');
	      this.adjustLayout();
	      main_core_zIndexManager.ZIndexManager.bringToFront(this.getOverlay());
	      this.opened = true;
	      this.fireEvent('onOpenStart');
	      this.animateOpening();
	      return true;
	    }
	  }, {
	    key: "close",
	    value: function close(immediately, callback) {
	      if (!this.isOpen()) {
	        return false;
	      }
	      if (!this.canClose()) {
	        return false;
	      }
	      if (this.minimizing) {
	        this.fireEvent('onMinimizeStart');
	      }
	      this.fireEvent('onCloseStart');
	      this.opened = false;
	      if (this.isDestroyed()) {
	        return false;
	      }
	      if (this.animation) {
	        this.animation.stop();
	      }
	      main_core.Dom.removeClass(this.getOverlay(), '--opening');
	      main_core.Dom.addClass(this.getOverlay(), '--closing');
	      this.fireEvent('onClosing');
	      if (immediately === true || main_core.Browser.isMobile()) {
	        babelHelpers.classPrivateFieldSet(this, _currentAnimationState, babelHelpers.classPrivateFieldGet(this, _startAnimationState));
	        this.completeAnimation(callback);
	      } else {
	        this.animation = new BX.easing({
	          duration: this.animationDuration,
	          start: babelHelpers.classPrivateFieldGet(this, _currentAnimationState),
	          finish: babelHelpers.classPrivateFieldGet(this, _startAnimationState),
	          step: state => {
	            babelHelpers.classPrivateFieldSet(this, _currentAnimationState, state);
	            this.animateStep(state);
	          },
	          complete: () => {
	            this.completeAnimation(callback);
	          }
	        });

	        // Chrome rendering bug
	        main_core.Dom.style(this.getContainer(), 'opacity', 0.96);
	        if (this.animationName === 'scale' && main_core.Type.isStringFilled(this.animationOptions.origin)) {
	          main_core.Dom.style(this.getContainer(), 'transform-origin', this.animationOptions.origin);
	        }
	        this.animation.animate();
	      }
	      return true;
	    }
	  }, {
	    key: "minimize",
	    value: function minimize(immediately, callback) {
	      this.minimizing = true;
	      const success = this.close(immediately, callback);
	      if (!success) {
	        this.minimizing = false;
	      }
	      return success;
	    }
	  }, {
	    key: "isMinimizing",
	    value: function isMinimizing() {
	      return this.minimizing;
	    }
	  }, {
	    key: "maximize",
	    value: function maximize() {
	      this.maximizing = true;
	      const success = this.open();
	      if (!success) {
	        this.maximizing = false;
	      }
	      return success;
	    }
	  }, {
	    key: "isMaximizing",
	    value: function isMaximizing() {
	      return this.maximizing;
	    }
	  }, {
	    key: "setAnimation",
	    value: function setAnimation(type, options) {
	      this.animationName = type === 'scale' ? type : 'sliding';
	      this.animationOptions = main_core.Type.isPlainObject(options) ? options : {};
	    }
	  }, {
	    key: "setMinimizeOptions",
	    value: function setMinimizeOptions(minimizeOptions) {
	      var _this$minimizeLabel;
	      const showMinimizeLabel = this.areMinimizeOptionsValid(minimizeOptions);
	      this.minimizeOptions = minimizeOptions;
	      (_this$minimizeLabel = this.minimizeLabel) === null || _this$minimizeLabel === void 0 ? void 0 : _this$minimizeLabel.setVisible(showMinimizeLabel);
	    }
	  }, {
	    key: "areMinimizeOptionsValid",
	    value: function areMinimizeOptionsValid(minimizeOptions) {
	      return main_core.Type.isPlainObject(minimizeOptions) && main_core.Type.isStringFilled(minimizeOptions.entityType) && (main_core.Type.isStringFilled(minimizeOptions.entityId) || main_core.Type.isNumber(minimizeOptions.entityId)) && main_core.Type.isStringFilled(minimizeOptions.url);
	    }
	  }, {
	    key: "getMinimizeOptions",
	    value: function getMinimizeOptions() {
	      return this.minimizeOptions;
	    }
	  }, {
	    key: "setToolbarOnOpen",
	    value: function setToolbarOnOpen(flag) {
	      if (main_core.Type.isBoolean(flag)) {
	        babelHelpers.classPrivateFieldSet(this, _hideToolbarOnOpen, flag);
	      }
	    }
	  }, {
	    key: "shouldHideToolbarOnOpen",
	    value: function shouldHideToolbarOnOpen() {
	      return babelHelpers.classPrivateFieldGet(this, _hideToolbarOnOpen);
	    }
	  }, {
	    key: "getDesignSystemContext",
	    value: function getDesignSystemContext() {
	      return babelHelpers.classPrivateFieldGet(this, _designSystemContext);
	    }
	  }, {
	    key: "setDesignSystemContext",
	    value: function setDesignSystemContext(context) {
	      if (main_core.Type.isString(context)) {
	        if (this.layout.container !== null) {
	          main_core.Dom.removeClass(this.layout.container, babelHelpers.classPrivateFieldGet(this, _designSystemContext));
	          main_core.Dom.addClass(this.layout.container, context);
	        }
	        babelHelpers.classPrivateFieldSet(this, _designSystemContext, context);
	      }
	    }
	  }, {
	    key: "getUrl",
	    value: function getUrl() {
	      return this.url;
	    }
	  }, {
	    key: "setUrl",
	    value: function setUrl(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        this.url = url;
	      }
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      this.getWindow().focus();

	      // if (this.isSelfContained())
	      // {
	      // 	this.getContentContainer().setAttribute("tabindex", "0");
	      // 	this.getContentContainer().focus();
	      // }
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.opened;
	    }
	  }, {
	    key: "getStartPosition",
	    value: function getStartPosition() {
	      return babelHelpers.classPrivateFieldGet(this, _startPosition);
	    }
	    /**
	     * @deprecated
	     */
	  }, {
	    key: "setZindex",
	    value: function setZindex(zIndex) {}
	    /**
	     * @public
	     * @returns {number}
	     */
	  }, {
	    key: "getZindex",
	    value: function getZindex() {
	      return this.getZIndexComponent().getZIndex();
	    }
	  }, {
	    key: "getZIndexComponent",
	    value: function getZIndexComponent() {
	      return babelHelpers.classPrivateFieldGet(this, _zIndexComponent);
	    }
	  }, {
	    key: "setOffset",
	    value: function setOffset(offset) {
	      if (main_core.Type.isNumber(offset) || offset === null) {
	        this.offset = offset;
	      }
	    }
	  }, {
	    key: "getOffset",
	    value: function getOffset() {
	      return this.offset;
	    }
	  }, {
	    key: "setAutoOffset",
	    value: function setAutoOffset(autoOffset) {
	      if (main_core.Type.isBoolean(autoOffset)) {
	        babelHelpers.classPrivateFieldSet(this, _autoOffset, autoOffset);
	      }
	    }
	  }, {
	    key: "shouldUseAutoOffset",
	    value: function shouldUseAutoOffset() {
	      return babelHelpers.classPrivateFieldGet(this, _autoOffset);
	    }
	  }, {
	    key: "setWidth",
	    value: function setWidth(width) {
	      if (main_core.Type.isNumber(width)) {
	        this.width = width;
	      }
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return this.width;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;
	      }
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "isSelfContained",
	    value: function isSelfContained() {
	      return this.contentCallback !== null;
	    }
	  }, {
	    key: "isPostMethod",
	    value: function isPostMethod() {
	      return this.requestMethod === 'post';
	    }
	  }, {
	    key: "getRequestParams",
	    value: function getRequestParams() {
	      return this.requestParams;
	    }
	    /**
	     * @public
	     * @returns {string}
	     */
	  }, {
	    key: "getFrameId",
	    value: function getFrameId() {
	      if (this.iframeId === null) {
	        this.iframeId = `iframe_${main_core.Text.getRandom(10).toLowerCase()}`;
	      }
	      return this.iframeId;
	    }
	  }, {
	    key: "getWindow",
	    value: function getWindow() {
	      return this.iframe ? this.iframe.contentWindow : window;
	    }
	  }, {
	    key: "getFrameWindow",
	    value: function getFrameWindow() {
	      return this.iframe ? this.iframe.contentWindow : null;
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden;
	    }
	  }, {
	    key: "isCacheable",
	    value: function isCacheable() {
	      return this.cacheable;
	    }
	  }, {
	    key: "isFocusable",
	    value: function isFocusable() {
	      return this.autoFocus;
	    }
	  }, {
	    key: "isPrintable",
	    value: function isPrintable() {
	      return this.printable;
	    }
	  }, {
	    key: "isDestroyed",
	    value: function isDestroyed() {
	      return this.destroyed;
	    }
	  }, {
	    key: "isLoaded",
	    value: function isLoaded() {
	      return this.loaded;
	    }
	  }, {
	    key: "canChangeHistory",
	    value: function canChangeHistory() {
	      if (this.allowCrossOrigin || /^\/bitrix\/(components|tools)\//i.test(this.getUrl())) {
	        return false;
	      }
	      if (this.allowChangeHistory === null) {
	        return !this.isSelfContained();
	      }
	      return this.allowChangeHistory;
	    }
	  }, {
	    key: "canChangeTitle",
	    value: function canChangeTitle() {
	      if (this.allowChangeTitle === null) {
	        if (this.getTitle() !== null) {
	          return true;
	        }
	        return this.canChangeHistory();
	      }
	      return this.allowChangeTitle;
	    }
	  }, {
	    key: "setCacheable",
	    value: function setCacheable(cacheable = true) {
	      this.cacheable = cacheable !== false;
	    }
	  }, {
	    key: "setAutoFocus",
	    value: function setAutoFocus(autoFocus = true) {
	      this.autoFocus = autoFocus !== false;
	    }
	    /**
	     * @public
	     * @param {boolean} printable
	     */
	  }, {
	    key: "setPrintable",
	    value: function setPrintable(printable = true) {
	      this.printable = printable !== false;
	      if (this.printable) {
	        this.showPrintBtn();
	      } else {
	        this.hidePrintBtn();
	      }
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      const loader = this.getLoader();
	      if (!this.layout.loader) {
	        this.createLoader(loader);
	      }
	      main_core.Dom.style(this.layout.loader, {
	        opacity: 1,
	        display: 'block'
	      });
	    }
	  }, {
	    key: "closeLoader",
	    value: function closeLoader() {
	      if (this.layout.loader) {
	        main_core.Dom.style(this.layout.loader, {
	          opacity: 0,
	          display: 'none'
	        });
	      }
	    }
	  }, {
	    key: "showCloseBtn",
	    value: function showCloseBtn() {
	      this.getLabel().show();
	    }
	  }, {
	    key: "hideCloseBtn",
	    value: function hideCloseBtn() {
	      this.getLabel().hide();
	    }
	  }, {
	    key: "showOrLightenCloseBtn",
	    value: function showOrLightenCloseBtn() {
	      if (main_core.Type.isStringFilled(this.getLabel().getText())) {
	        this.getLabel().showIcon();
	      } else {
	        this.getLabel().lightenIcon();
	      }
	    }
	  }, {
	    key: "hideOrDarkenCloseBtn",
	    value: function hideOrDarkenCloseBtn() {
	      if (main_core.Type.isStringFilled(this.getLabel().getText())) {
	        this.getLabel().hideIcon();
	      } else {
	        this.getLabel().darkenIcon();
	      }
	    }
	  }, {
	    key: "showPrintBtn",
	    value: function showPrintBtn() {
	      if (this.printLabel !== null) {
	        this.printLabel.show();
	      }
	    }
	  }, {
	    key: "hidePrintBtn",
	    value: function hidePrintBtn() {
	      if (this.printLabel !== null) {
	        this.printLabel.hide();
	      }
	    }
	  }, {
	    key: "showExtraLabels",
	    value: function showExtraLabels() {
	      main_core.Dom.removeClass(this.getExtraLabelsContainer(), '--hidden');
	    }
	  }, {
	    key: "hideExtraLabels",
	    value: function hideExtraLabels() {
	      main_core.Dom.addClass(this.getExtraLabelsContainer(), '--hidden');
	    }
	  }, {
	    key: "setContentClass",
	    value: function setContentClass(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        this.removeContentClass();
	        this.contentClassName = className;
	        main_core.Dom.addClass(this.getContentContainer(), className);
	      }
	    }
	  }, {
	    key: "removeContentClass",
	    value: function removeContentClass() {
	      if (this.contentClassName !== null) {
	        main_core.Dom.removeClass(this.getContentContainer(), this.contentClassName);
	        this.contentClassName = null;
	      }
	    }
	  }, {
	    key: "setContainerClass",
	    value: function setContainerClass(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        this.removeContainerClass();
	        this.containerClassName = className;
	        main_core.Dom.addClass(this.getContainer(), className);
	      }
	    }
	  }, {
	    key: "removeContainerClass",
	    value: function removeContainerClass() {
	      if (this.containerClassName !== null) {
	        main_core.Dom.removeClass(this.getContainer(), this.containerClassName);
	        this.containerClassName = null;
	      }
	    }
	  }, {
	    key: "setOverlayClass",
	    value: function setOverlayClass(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        this.removeOverlayClass();
	        this.overlayClassName = className;
	        main_core.Dom.addClass(this.getOverlay(), className);
	      }
	    }
	  }, {
	    key: "removeOverlayClass",
	    value: function removeOverlayClass() {
	      if (this.overlayClassName !== null) {
	        main_core.Dom.removeClass(this.getOverlay(), this.overlayClassName);
	        this.overlayClassName = null;
	      }
	    }
	  }, {
	    key: "applyHacks",
	    value: function applyHacks() {
	      // You can override this method in a derived class
	    }
	  }, {
	    key: "applyPostHacks",
	    value: function applyPostHacks() {
	      // You can override this method in a derived class
	    }
	  }, {
	    key: "resetHacks",
	    value: function resetHacks() {
	      // You can override this method in a derived class
	    }
	  }, {
	    key: "resetPostHacks",
	    value: function resetPostHacks() {
	      // You can override this method in a derived class
	    }
	  }, {
	    key: "getTopBoundary",
	    value: function getTopBoundary() {
	      return 0;
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "calculateLeftBoundary",
	    value: function calculateLeftBoundary() {
	      const customLeftBoundary = this.getCustomLeftBoundary();
	      if (customLeftBoundary !== null) {
	        return customLeftBoundary;
	      }
	      return this.getLeftBoundary();
	    }
	  }, {
	    key: "getLeftBoundary",
	    value: function getLeftBoundary() {
	      const windowWidth = main_core.Browser.isMobile() ? window.innerWidth : document.documentElement.clientWidth;
	      return windowWidth < 1160 ? this.getMinLeftBoundary() : 300;
	    }
	  }, {
	    key: "getMinLeftBoundary",
	    value: function getMinLeftBoundary() {
	      return this.hideControls && this.getCustomLeftBoundary() !== null ? 0 : 65;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "getLeftBoundaryOffset",
	    value: function getLeftBoundaryOffset() {
	      const offset = this.getOffset() === null ? 0 : this.getOffset();
	      return Math.max(this.calculateLeftBoundary(), this.getMinLeftBoundary()) + offset;
	    }
	  }, {
	    key: "setCustomLeftBoundary",
	    value: function setCustomLeftBoundary(boundary) {
	      if (main_core.Type.isNumber(boundary) || boundary === null) {
	        this.customLeftBoundary = boundary;
	      }
	    }
	  }, {
	    key: "getCustomLeftBoundary",
	    value: function getCustomLeftBoundary() {
	      return this.customLeftBoundary;
	    }
	  }, {
	    key: "setCustomRightBoundary",
	    value: function setCustomRightBoundary(boundary) {
	      if (main_core.Type.isNumber(boundary) || boundary === null) {
	        this.customRightBoundary = boundary;
	      }
	    }
	  }, {
	    key: "getCustomRightBoundary",
	    value: function getCustomRightBoundary() {
	      return this.customRightBoundary;
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "calculateRightBoundary",
	    value: function calculateRightBoundary() {
	      const customRightBoundary = this.getCustomRightBoundary();
	      if (customRightBoundary !== null) {
	        return -window.pageXOffset + customRightBoundary;
	      }
	      return this.getRightBoundary();
	    }
	  }, {
	    key: "getRightBoundary",
	    value: function getRightBoundary() {
	      return -window.pageXOffset;
	    }
	  }, {
	    key: "getOuterBoundary",
	    value: function getOuterBoundary() {
	      return babelHelpers.classPrivateFieldGet(this, _outerBoundary);
	    }
	  }, {
	    key: "calculateOuterBoundary",
	    value: function calculateOuterBoundary() {
	      // You can override this method in a derived class
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.isDestroyed()) {
	        return false;
	      }
	      this.firePageEvent('onDestroy');
	      this.fireFrameEvent('onDestroy');
	      const frameWindow = this.getFrameWindow();
	      if (frameWindow && !this.allowCrossOrigin) {
	        main_core.Event.unbind(frameWindow, 'keydown', this.handleFrameKeyDown);
	        main_core.Event.unbind(frameWindow, 'focus', this.handleFrameFocus);
	        main_core.Event.unbind(frameWindow, 'unload', this.handleFrameUnload);
	      } else if (this.allowCrossOrigin) {
	        main_core.Event.unbind(window, 'message', this.handleCrossOriginWindowMessage);
	      }
	      main_core_events.EventEmitter.unsubscribe('BX.Main.Popup:onInit', this.handlePopupInit);
	      main_core_zIndexManager.ZIndexManager.unregister(this.layout.overlay);
	      babelHelpers.classPrivateFieldSet(this, _zIndexComponent, null);
	      main_core.Dom.remove(this.layout.overlay);
	      this.layout.container = null;
	      this.layout.overlay = null;
	      this.layout.content = null;
	      this.layout.closeBtn = null;
	      this.layout.loader = null;
	      babelHelpers.classPrivateFieldSet(this, _refs, null);
	      this.iframe = null;
	      this.destroyed = true;
	      main_core_events.EventEmitter.unsubscribeAll(this);
	      this.firePageEvent('onDestroyComplete');
	      return true;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.hidden = true;
	      main_core.Dom.style(this.getContainer(), 'display', 'none');
	      main_core.Dom.style(this.getOverlay(), 'display', 'none');
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "unhide",
	    value: function unhide() {
	      this.hidden = false;
	      main_core.Dom.style(this.getContainer(), 'display', null);
	      main_core.Dom.style(this.getOverlay(), 'display', null);
	    }
	    /**
	     * @public
	     */
	  }, {
	    key: "reload",
	    value: function reload() {
	      this.loaded = false;
	      if (this.isSelfContained()) {
	        this.contentCallbackInvoved = false;
	        this.showLoader();
	        this.setContent();
	      } else {
	        this.showLoader();
	        this.getFrameWindow().location.reload();
	      }
	    }
	    /**
	     * @public
	     */
	  }, {
	    key: "adjustLayout",
	    value: function adjustLayout() {
	      const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
	      const windowHeight = main_core.Browser.isMobile() ? window.innerHeight : document.documentElement.clientHeight;
	      let topBoundary = this.getTopBoundary();
	      const isTopBoundaryVisible = topBoundary - scrollTop > 0;
	      topBoundary = isTopBoundaryVisible ? topBoundary : scrollTop;
	      const height = isTopBoundaryVisible > 0 ? windowHeight - topBoundary + scrollTop : windowHeight;
	      const leftBoundary = this.getLeftBoundaryOffset();
	      const rightBoundary = this.calculateRightBoundary();
	      main_core.Dom.style(this.getOverlay(), {
	        left: `${window.pageXOffset}px`,
	        top: `${topBoundary}px`,
	        right: `${rightBoundary}px`,
	        height: `${height}px`
	      });
	      const {
	        right = null,
	        top = null,
	        bottom = null
	      } = _classPrivateMethodGet$1(this, _calculateOuterBoundary, _calculateOuterBoundary2).call(this);
	      main_core.Dom.style(this.getContainer(), {
	        width: `calc(100% - ${leftBoundary + (right === null ? 0 : right)}px)`,
	        maxWidth: this.getWidth() === null ? null : `${this.getWidth()}px`,
	        right: right === null ? null : `${right}px`,
	        top: top === null ? null : `${top}px`,
	        bottom: bottom === null ? null : `${bottom}px`
	        // height: `${height}px`, // height: '100%',
	      });

	      this.getLabel().adjustLayout();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "createLayout",
	    value: function createLayout() {
	      if (this.layout.overlay !== null && this.layout.overlay.parentNode) {
	        return;
	      }
	      if (this.isSelfContained()) {
	        main_core.Dom.addClass(this.getOverlay(), '--self-contained');
	        main_core.Dom.append(this.getOverlay(), document.body);
	        this.setContent();
	        main_core_events.EventEmitter.subscribe('BX.Main.Popup:onInit', this.handlePopupInit);
	      } else {
	        main_core.Dom.append(this.getFrame(), this.getContentContainer());
	        main_core.Dom.append(this.getOverlay(), document.body);
	        this.setFrameSrc(); // setFrameSrc must be below than appendChild, otherwise POST method fails.
	      }

	      babelHelpers.classPrivateFieldSet(this, _zIndexComponent, main_core_zIndexManager.ZIndexManager.register(this.getOverlay()));
	    }
	  }, {
	    key: "getFrame",
	    value: function getFrame() {
	      if (this.iframe !== null) {
	        return this.iframe;
	      }
	      this.iframe = main_core.Dom.create('iframe', {
	        attrs: {
	          referrerpolicy: this.allowCrossOrigin ? 'strict-origin' : false,
	          src: 'about:blank',
	          frameborder: '0'
	        },
	        props: {
	          className: 'side-panel-iframe',
	          name: this.getFrameId(),
	          id: this.getFrameId()
	        },
	        events: {
	          load: this.handleFrameLoad.bind(this)
	        }
	      });
	      return this.iframe;
	    }
	  }, {
	    key: "getOverlay",
	    value: function getOverlay() {
	      if (this.layout.overlay !== null) {
	        return this.layout.overlay;
	      }
	      const overlayClass = this.overlayClassName === null ? '' : ` ${this.overlayClassName}`;
	      this.layout.overlay = main_core.Dom.create('div', {
	        props: {
	          className: `side-panel side-panel-overlay${overlayClass}`
	        },
	        events: {
	          mousedown: this.handleOverlayClick.bind(this)
	        },
	        children: [this.getContainer()]
	      });
	      return this.layout.overlay;
	    }
	  }, {
	    key: "unhideOverlay",
	    value: function unhideOverlay() {
	      main_core.Dom.removeClass(this.getOverlay(), '--hidden');
	    }
	  }, {
	    key: "hideOverlay",
	    value: function hideOverlay() {
	      main_core.Dom.addClass(this.getOverlay(), '--hidden');
	    }
	  }, {
	    key: "hideShadow",
	    value: function hideShadow() {
	      main_core.Dom.removeClass(this.getContainer(), 'side-panel-show-shadow');
	    }
	  }, {
	    key: "showShadow",
	    value: function showShadow() {
	      main_core.Dom.addClass(this.getContainer(), 'side-panel-show-shadow');
	    }
	  }, {
	    key: "setOverlayBackground",
	    value: function setOverlayBackground() {
	      if (this.overlayBgCallback === null) {
	        const opacity = parseInt(this.overlayOpacity / 100 * 255, 10).toString(16).padStart(2, 0);
	        main_core.Dom.style(this.getOverlay(), 'background-color', `${this.overlayBgColor}${opacity}`);
	      } else {
	        const state = _classPrivateMethodGet$1(this, _getAnimationState, _getAnimationState2).call(this, 'end');
	        main_core.Dom.style(this.getOverlay(), 'background', this.overlayBgCallback(state, this));
	      }
	    }
	  }, {
	    key: "setOverlayAnimation",
	    value: function setOverlayAnimation(animate) {
	      if (main_core.Type.isBoolean(animate)) {
	        this.overlayAnimation = animate;
	      }
	    }
	  }, {
	    key: "getOverlayAnimation",
	    value: function getOverlayAnimation() {
	      return this.overlayAnimation;
	    }
	  }, {
	    key: "getOverlayBgColor",
	    value: function getOverlayBgColor() {
	      return this.overlayBgColor;
	    }
	  }, {
	    key: "getOverlayOpacity",
	    value: function getOverlayOpacity() {
	      return this.overlayOpacity;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.layout.container !== null) {
	        return this.layout.container;
	      }
	      const content = main_core.Tag.render(_t || (_t = _`
			<div class="side-panel-content-wrapper">${0}</div>
		`), this.getContentContainer());
	      this.layout.container = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="side-panel side-panel-container">
				${0}
			</div>
		`), this.hideControls ? content : [content, this.getLabelsContainer()]);
	      main_core.Dom.addClass(this.layout.container, this.getDesignSystemContext());
	      main_core.Dom.addClass(this.layout.container, this.containerClassName);
	      return this.layout.container;
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      if (this.layout.content !== null) {
	        return this.layout.content;
	      }
	      const contentClass = this.contentClassName === null ? '' : ` ${this.contentClassName}`;
	      this.layout.content = main_core.Dom.create('div', {
	        props: {
	          className: `side-panel-content-container${contentClass}`
	        }
	      });
	      return this.layout.content;
	    }
	  }, {
	    key: "getLabelsContainer",
	    value: function getLabelsContainer() {
	      return babelHelpers.classPrivateFieldGet(this, _refs).remember('labels-container', () => {
	        return main_core.Dom.create('div', {
	          props: {
	            className: 'side-panel-labels'
	          },
	          children: [this.getLabel().getContainer(), this.getExtraLabelsContainer()]
	        });
	      });
	    }
	  }, {
	    key: "getExtraLabelsContainer",
	    value: function getExtraLabelsContainer() {
	      return babelHelpers.classPrivateFieldGet(this, _refs).remember('icon-labels', () => {
	        return main_core.Dom.create('div', {
	          props: {
	            className: 'side-panel-extra-labels'
	          },
	          children: [this.minimizeLabel.getContainer(), this.newWindowLabel ? this.newWindowLabel.getContainer() : null, this.copyLinkLabel ? this.copyLinkLabel.getContainer() : null, this.printLabel ? this.printLabel.getContainer() : null]
	        });
	      });
	    }
	  }, {
	    key: "getCloseBtn",
	    value: function getCloseBtn() {
	      return this.getLabel().getIconBox();
	    }
	  }, {
	    key: "getLabel",
	    value: function getLabel() {
	      return this.label;
	    }
	  }, {
	    key: "getNewWindowLabel",
	    value: function getNewWindowLabel() {
	      return this.newWindowLabel;
	    }
	  }, {
	    key: "getCopyLinkLabel",
	    value: function getCopyLinkLabel() {
	      return this.copyLinkLabel;
	    }
	  }, {
	    key: "getMinimizeLabel",
	    value: function getMinimizeLabel() {
	      return this.minimizeLabel;
	    }
	  }, {
	    key: "getPrintLabel",
	    value: function getPrintLabel() {
	      return this.printLabel;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "setContent",
	    value: function setContent() {
	      if (this.contentCallbackInvoved) {
	        return;
	      }
	      this.contentCallbackInvoved = true;
	      main_core.Dom.clean(this.getContentContainer());
	      let promise = this.contentCallback(this);
	      const isPromiseReturned = promise && (Object.prototype.toString.call(promise) === '[object Promise]' || promise.toString() === '[object BX.Promise]');
	      if (!isPromiseReturned) {
	        promise = Promise.resolve(promise);
	      }
	      promise.then(result => {
	        if (this.isDestroyed()) {
	          return;
	        }
	        if (main_core.Type.isPlainObject(result) && main_core.Type.isStringFilled(result.html)) {
	          main_core.Runtime.html(this.getContentContainer(), result.html).then(() => {
	            this.removeLoader();
	            this.loaded = true;
	            this.firePageEvent('onLoad');
	          }).catch(reason => {
	            this.removeLoader();
	            this.getContentContainer().innerHTML = reason;
	          });
	        } else {
	          if (main_core.Type.isDomNode(result)) {
	            main_core.Dom.append(result, this.getContentContainer());
	          } else if (main_core.Type.isStringFilled(result)) {
	            this.getContentContainer().innerHTML = result;
	          }
	          this.removeLoader();
	          this.loaded = true;
	          this.firePageEvent('onLoad');
	        }
	      }).catch(reason => {
	        this.removeLoader();
	        this.getContentContainer().innerHTML = reason;
	      });
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "setFrameSrc",
	    value: function setFrameSrc() {
	      if (this.iframeSrc === this.getUrl()) {
	        return;
	      }
	      const url = main_core.Uri.addParam(this.getUrl(), {
	        IFRAME: 'Y',
	        IFRAME_TYPE: 'SIDE_SLIDER'
	      });
	      if (this.isPostMethod()) {
	        const form = document.createElement('form');
	        form.method = 'POST';
	        form.action = url;
	        form.target = this.getFrameId();
	        main_core.Dom.style(form, 'display', 'none');
	        BX.util.addObjectToForm(this.getRequestParams(), form);
	        main_core.Dom.append(form, document.body);
	        form.submit();
	        main_core.Dom.remove(form);
	      } else {
	        this.iframeSrc = this.getUrl();
	        this.iframe.src = url;
	      }
	      this.loaded = false;
	      this.listenIframeLoading();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "createLoader",
	    value: function createLoader(sliderLoader) {
	      main_core.Dom.remove(this.layout.loader);
	      const loader = main_core.Type.isStringFilled(sliderLoader) || main_core.Type.isElementNode(sliderLoader) ? sliderLoader : 'default-loader';
	      const oldLoaders = ['task-new-loader', 'task-edit-loader', 'task-view-loader', 'crm-entity-details-loader', 'crm-button-view-loader', 'crm-webform-view-loader', 'create-mail-loader', 'view-mail-loader'];
	      if (main_core.Type.isElementNode(loader)) {
	        this.layout.loader = this.createHTMLLoader(loader);
	      } else if (oldLoaders.includes(loader) && this.loaderExists(loader)) {
	        this.layout.loader = this.createOldLoader(loader);
	      } else if (loader.charAt(0) === '/') {
	        this.layout.loader = this.createSvgLoader(loader);
	      } else {
	        const matches = loader.match(/^([\w.-]+):([\w.-]+)$/i);
	        if (matches) {
	          const moduleId = matches[1];
	          const svgName = matches[2];
	          const svg = `/bitrix/images/${moduleId}/slider/${svgName}.svg`;
	          this.layout.loader = this.createSvgLoader(svg);
	        } else {
	          this.layout.loader = this.createDefaultLoader();
	        }
	      }
	      main_core.Dom.append(this.layout.loader, this.getContainer());
	    }
	  }, {
	    key: "createSvgLoader",
	    value: function createSvgLoader(svg) {
	      return main_core.Dom.create('div', {
	        props: {
	          className: 'side-panel-loader'
	        },
	        children: [main_core.Dom.create('div', {
	          props: {
	            className: 'side-panel-loader-container'
	          },
	          style: {
	            backgroundImage: `url("${svg}")`
	          }
	        })]
	      });
	    }
	  }, {
	    key: "createDefaultLoader",
	    value: function createDefaultLoader() {
	      return main_core.Dom.create('div', {
	        props: {
	          className: 'side-panel-loader'
	        },
	        children: [main_core.Dom.create('div', {
	          props: {
	            className: 'side-panel-default-loader-container'
	          },
	          html: '<svg class="side-panel-default-loader-circular" viewBox="25 25 50 50">' + '<circle ' + 'class="side-panel-default-loader-path" ' + 'cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"' + '/>' + '</svg>'
	        })]
	      });
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "createOldLoader",
	    value: function createOldLoader(loader) {
	      if (loader === 'crm-entity-details-loader') {
	        return main_core.Dom.create('div', {
	          props: {
	            className: `side-panel-loader ${loader}`
	          },
	          children: [main_core.Dom.create('img', {
	            attrs: {
	              src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX' + '///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC'
	            },
	            props: {
	              className: 'side-panel-loader-mask top'
	            }
	          }), main_core.Dom.create('div', {
	            props: {
	              className: 'side-panel-loader-bg left'
	            },
	            children: [main_core.Dom.create('img', {
	              attrs: {
	                src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1B' + 'MVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC'
	              },
	              props: {
	                className: 'side-panel-loader-mask left'
	              }
	            })]
	          }), main_core.Dom.create('div', {
	            props: {
	              className: 'side-panel-loader-bg right'
	            },
	            children: [main_core.Dom.create('img', {
	              attrs: {
	                src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BM' + 'VEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC'
	              },
	              props: {
	                className: 'side-panel-loader-mask right'
	              }
	            })]
	          })]
	        });
	      }
	      return main_core.Dom.create('div', {
	        props: {
	          className: `side-panel-loader ${loader}`
	        },
	        children: [main_core.Dom.create('img', {
	          attrs: {
	            src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA1BMVEX' + '///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC'
	          },
	          props: {
	            className: 'side-panel-loader-mask left'
	          }
	        }), main_core.Dom.create('img', {
	          attrs: {
	            src: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAA' + '1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAtJREFUeAFjGMQAAACcAAG25ruvAAAAAElFTkSuQmCC'
	          },
	          props: {
	            className: 'side-panel-loader-mask right'
	          }
	        })]
	      });
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "createHTMLLoader",
	    value: function createHTMLLoader(loader) {
	      return main_core.Dom.create('div', {
	        children: [loader]
	      });
	    }
	  }, {
	    key: "loaderExists",
	    value: function loaderExists(loader) {
	      if (!main_core.Type.isStringFilled(loader)) {
	        return false;
	      }
	      for (let i = 0; i < document.styleSheets.length; i++) {
	        const style = document.styleSheets[i];
	        if (!main_core.Type.isStringFilled(style.href) || !style.href.includes('sidepanel')) {
	          continue;
	        }
	        let rules = null;
	        try {
	          rules = style.rules || style.cssRules;
	        } catch {
	          try {
	            rules = style.cssRules;
	          } catch {
	            rules = [];
	          }
	        }
	        for (const rule of rules) {
	          if (main_core.Type.isStringFilled(rule.selectorText) && rule.selectorText.includes(loader)) {
	            return true;
	          }
	        }
	      }
	      return false;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "removeLoader",
	    value: function removeLoader() {
	      main_core.Dom.remove(this.layout.loader);
	      this.layout.loader = null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "animateOpening",
	    value: function animateOpening() {
	      if (this.animation) {
	        this.animation.stop();
	      }
	      this.fireEvent('onOpening');
	      if (main_core.Browser.isMobile()) {
	        babelHelpers.classPrivateFieldSet(this, _currentAnimationState, babelHelpers.classPrivateFieldGet(this, _endAnimationState));
	        this.animateStep(babelHelpers.classPrivateFieldGet(this, _currentAnimationState));
	        this.completeAnimation();
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _currentAnimationState, babelHelpers.classPrivateFieldGet(this, _currentAnimationState) === null ? babelHelpers.classPrivateFieldGet(this, _startAnimationState) : babelHelpers.classPrivateFieldGet(this, _currentAnimationState));
	      this.animation = new BX.easing({
	        duration: this.animationDuration,
	        start: babelHelpers.classPrivateFieldGet(this, _currentAnimationState),
	        finish: babelHelpers.classPrivateFieldGet(this, _endAnimationState),
	        step: state => {
	          babelHelpers.classPrivateFieldSet(this, _currentAnimationState, state);
	          this.animateStep(state);
	        },
	        complete: () => {
	          this.completeAnimation();
	        }
	      });
	      if (this.animationName === 'scale' && main_core.Type.isStringFilled(this.animationOptions.origin)) {
	        main_core.Dom.style(this.getContainer(), 'transform-origin', this.animationOptions.origin);
	      }
	      this.animation.animate();
	    }
	    /**
	     * @private
	     * @param {object} state
	     */
	  }, {
	    key: "animateStep",
	    value: function animateStep(state) {
	      if (this.animationName === 'scale') {
	        main_core.Dom.style(this.getContainer(), 'transform', `scale(${state.scale / 100})`);
	      } else {
	        main_core.Dom.style(this.getContainer(), 'transform', `translate(${state.translateX}%, ${state.translateY}%)`);
	      }
	      if (this.getOverlayAnimation()) {
	        if (this.overlayBgCallback === null) {
	          const opacity = parseInt(state.opacity / 100 * 255, 10).toString(16).padStart(2, 0);
	          main_core.Dom.style(this.getOverlay(), 'background-color', `${this.overlayBgColor}${opacity}`);
	        } else {
	          main_core.Dom.style(this.getOverlay(), 'background', this.overlayBgCallback(state, this));
	        }
	      }
	    }
	    /**
	     * @private
	     * @param callback
	     */
	  }, {
	    key: "completeAnimation",
	    value: function completeAnimation(callback) {
	      this.animation = null;
	      if (this.isOpen()) {
	        babelHelpers.classPrivateFieldSet(this, _currentAnimationState, babelHelpers.classPrivateFieldGet(this, _endAnimationState));
	        this.maximizing = false;
	        main_core.Dom.removeClass(this.getOverlay(), '--opening');
	        main_core.Dom.addClass(this.getOverlay(), '--open');
	        if (this.animationName === 'scale') {
	          const state = _classPrivateMethodGet$1(this, _getAnimationState, _getAnimationState2).call(this, 'end');
	          main_core.Dom.style(this.getContainer(), {
	            'transform-origin': null,
	            transform: `translate(${state.translateX}%, ${state.translateY}%)`
	          });
	        }
	        this.firePageEvent('onBeforeOpenComplete');
	        this.fireFrameEvent('onBeforeOpenComplete');
	        this.firePageEvent('onOpenComplete');
	        this.fireFrameEvent('onOpenComplete');
	        if (!this.isLoaded()) {
	          this.showLoader();
	        }
	        if (this.isFocusable()) {
	          this.focus();
	        }
	      } else {
	        babelHelpers.classPrivateFieldSet(this, _currentAnimationState, babelHelpers.classPrivateFieldGet(this, _startAnimationState));
	        this.minimizing = false;
	        main_core.Dom.removeClass(this.getOverlay(), '--open --opening --closing');
	        if (this.animationName === 'scale') {
	          const state = _classPrivateMethodGet$1(this, _getAnimationState, _getAnimationState2).call(this, 'start');
	          main_core.Dom.style(this.getContainer(), {
	            'transform-origin': null,
	            transform: `translate(${state.translateX}%, ${state.translateY}%)`
	          });
	        }
	        main_core.Dom.style(this.getContainer(), {
	          width: null,
	          right: null,
	          opacity: null,
	          'max-width': null,
	          'min-width': null
	        });
	        main_core.Dom.style(this.getCloseBtn(), 'opacity', null);
	        this.firePageEvent('onBeforeCloseComplete');
	        this.fireFrameEvent('onBeforeCloseComplete');
	        this.firePageEvent('onCloseComplete');
	        this.fireFrameEvent('onCloseComplete');
	        if (main_core.Type.isFunction(callback)) {
	          callback(this);
	        }
	        if (!this.isCacheable()) {
	          this.destroy();
	        }
	      }
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "firePageEvent",
	    value: function firePageEvent(eventName) {
	      const sliderEvent = this.getEvent(eventName);
	      if (sliderEvent === null) {
	        throw new Error('\'eventName\' is invalid.');
	      }
	      main_core_events.EventEmitter.emit(this, sliderEvent.getFullName().toLowerCase(), new main_core_events.BaseEvent({
	        data: [sliderEvent],
	        compatData: [sliderEvent]
	      }));

	      // Events for compatibility
	      if (['onClose', 'onOpen'].includes(eventName)) {
	        main_core_events.EventEmitter.emit(`BX.Bitrix24.PageSlider:${eventName}`, new main_core_events.BaseEvent({
	          data: [this],
	          compatData: [this]
	        }));
	        main_core_events.EventEmitter.emit(`Bitrix24.Slider:${eventName}`, new main_core_events.BaseEvent({
	          data: [this],
	          compatData: [this]
	        }));
	      }
	      return sliderEvent;
	    }
	    /**
	     * @internal
	     */
	  }, {
	    key: "fireFrameEvent",
	    value: function fireFrameEvent(eventName) {
	      const sliderEvent = this.getEvent(eventName);
	      if (sliderEvent === null) {
	        throw new Error('\'eventName\' is invalid.');
	      }
	      if (this.allowCrossOrigin) {
	        return null;
	      }
	      const frameWindow = this.getFrameWindow();
	      if (frameWindow && frameWindow.BX && frameWindow.BX.onCustomEvent) {
	        frameWindow.BX.onCustomEvent(this, sliderEvent.getFullName(), [sliderEvent]);

	        // Events for compatibility
	        if (['onClose', 'onOpen'].includes(eventName)) {
	          frameWindow.BX.onCustomEvent(`BX.Bitrix24.PageSlider:${eventName}`, [this]);
	          frameWindow.BX.onCustomEvent(`Bitrix24.Slider:${eventName}`, [this]); // Compatibility
	        }
	      }

	      return sliderEvent;
	    }
	  }, {
	    key: "fireEvent",
	    value: function fireEvent(eventName) {
	      this.firePageEvent(eventName);
	      this.fireFrameEvent(eventName);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getEvent",
	    value: function getEvent(eventName) {
	      let event = null;
	      if (main_core.Type.isStringFilled(eventName)) {
	        event = new SliderEvent();
	        event.setSlider(this);
	        event.setName(eventName);
	      } else if (eventName instanceof SliderEvent) {
	        event = eventName;
	      }
	      return event;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "canOpen",
	    value: function canOpen() {
	      return this.canAction('open');
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "canClose",
	    value: function canClose() {
	      return this.canAction('close');
	    }
	    /**
	     * @package
	     * @returns {boolean}
	     */
	  }, {
	    key: "canCloseByEsc",
	    value: function canCloseByEsc() {
	      return this.canAction('closeByEsc');
	    }
	    /**
	     * @private
	     * @param {string} action
	     * @returns {boolean}
	     */
	  }, {
	    key: "canAction",
	    value: function canAction(action) {
	      if (!main_core.Type.isStringFilled(action)) {
	        return false;
	      }
	      const eventName = `on${action.charAt(0).toUpperCase()}${action.slice(1)}`;
	      const pageEvent = this.firePageEvent(eventName);
	      const frameEvent = this.fireFrameEvent(eventName);
	      return pageEvent.isActionAllowed() && (!frameEvent || frameEvent.isActionAllowed());
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleCrossOriginWindowMessage",
	    value: function handleCrossOriginWindowMessage(event) {
	      const frameUrl = new URL(this.url);
	      const eventUrl = new URL(event.origin);
	      if (eventUrl.origin !== frameUrl.origin) {
	        return;
	      }
	      const message = {
	        type: '',
	        data: undefined
	      };
	      if (main_core.Type.isString(event.data)) {
	        message.type = event.data;
	      } else if (main_core.Type.isPlainObject(event.data)) {
	        message.type = event.data.type;
	        message.data = event.data.data;
	      }
	      switch (message.type) {
	        case 'BX:SidePanel:close':
	          {
	            this.close();
	            break;
	          }
	        case 'BX:SidePanel:load:force':
	          {
	            if (!this.isLoaded() && !this.isDestroyed()) {
	              this.handleFrameLoad();
	            }
	            break;
	          }
	        case 'BX:SidePanel:data:send':
	          {
	            const pageEvent = new MessageEvent({
	              sender: this,
	              data: message.data
	            });
	            pageEvent.setName('onXDomainMessage');
	            this.firePageEvent(pageEvent);
	            break;
	          }
	        default:
	        // No default
	      }
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleFrameLoad",
	    value: function handleFrameLoad(event) {
	      if (this.loaded) {
	        return;
	      }
	      const frameWindow = this.iframe.contentWindow;
	      const iframeLocation = frameWindow.location;
	      if (this.allowCrossOrigin) {
	        main_core.Event.bind(window, 'message', this.handleCrossOriginWindowMessage);
	      }
	      try {
	        if (iframeLocation.toString() === 'about:blank') {
	          return;
	        }
	      } catch (e) {
	        if (this.allowCrossOrigin) {
	          this.loaded = true;
	          this.closeLoader();
	          return;
	        }

	        // eslint-disable-next-line no-console
	        console.warn('SidePanel: Try to use "allowCrossOrigin: true" option.');
	        throw e;
	      }
	      main_core.Event.bind(frameWindow, 'keydown', this.handleFrameKeyDown);
	      main_core.Event.bind(frameWindow, 'focus', this.handleFrameFocus);
	      main_core.Event.bind(frameWindow, 'unload', this.handleFrameUnload);
	      if (main_core.Browser.isMobile()) {
	        frameWindow.document.body.style.paddingBottom = `${window.innerHeight * 2 / 3}px`;
	      }
	      const iframeUrl = iframeLocation.pathname + iframeLocation.search + iframeLocation.hash;
	      this.iframeSrc = this.refineUrl(iframeUrl);
	      this.url = this.iframeSrc;
	      if (this.isPrintable()) {
	        this.injectPrintStyles();
	      }
	      this.loaded = true;
	      this.loadedCnt++;
	      if (this.loadedCnt > 1) {
	        this.firePageEvent('onLoad');
	        this.fireFrameEvent('onLoad');
	        this.firePageEvent('onReload');
	        this.fireFrameEvent('onReload');
	      } else {
	        this.firePageEvent('onLoad');
	        this.fireFrameEvent('onLoad');
	      }
	      if (this.isFocusable()) {
	        this.focus();
	      }
	      this.closeLoader();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "listenIframeLoading",
	    value: function listenIframeLoading() {
	      if (this.allowCrossOrigin) {
	        return;
	      }
	      const isLoaded = setInterval(() => {
	        if (this.isLoaded() || this.isDestroyed()) {
	          clearInterval(isLoaded);
	          return;
	        }
	        if (this.iframe.contentWindow.location.toString() === 'about:blank') {
	          return;
	        }
	        if (this.iframe.contentWindow.document.readyState === 'complete' || this.iframe.contentWindow.document.readyState === 'interactive') {
	          clearInterval(isLoaded);
	          this.handleFrameLoad();
	        }
	      }, 200);
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleFrameUnload",
	    value: function handleFrameUnload(event) {
	      this.loaded = false;
	      this.listenIframeLoading();
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleFrameKeyDown",
	    value: function handleFrameKeyDown(event) {
	      var _this$getWindow$BX, _this$getWindow$BX$Ma;
	      if (event.keyCode !== 27) {
	        return;
	      }
	      const framePopupManager = (_this$getWindow$BX = this.getWindow().BX) === null || _this$getWindow$BX === void 0 ? void 0 : (_this$getWindow$BX$Ma = _this$getWindow$BX.Main) === null || _this$getWindow$BX$Ma === void 0 ? void 0 : _this$getWindow$BX$Ma.PopupManager;
	      if (framePopupManager) {
	        const popups = framePopupManager.getPopups();
	        for (const popup of popups) {
	          if (popup.isShown()) {
	            return;
	          }
	        }
	      }
	      const centerX = this.getWindow().document.documentElement.clientWidth / 2;
	      const centerY = this.getWindow().document.documentElement.clientHeight / 2;
	      const element = this.getWindow().document.elementFromPoint(centerX, centerY);
	      if (main_core.Dom.hasClass(element, 'bx-core-dialog-overlay') || main_core.Dom.hasClass(element, 'bx-core-window')) {
	        return;
	      }
	      if (element.closest('.bx-core-window')) {
	        return;
	      }
	      this.firePageEvent('onEscapePress');
	      this.fireFrameEvent('onEscapePress');
	    }
	  }, {
	    key: "isOnTopOfPopup",
	    value: function isOnTopOfPopup(popup) {
	      const sameStack = this.getZIndexComponent().getStack() === popup.getZIndexComponent().getStack();
	      const popupOnTop = sameStack && popup.getZindex() > this.getZindex();
	      let popupInside = this.getContainer().contains(popup.getPopupContainer());
	      if (this.getFrameWindow()) {
	        popupInside = this.getFrameWindow().document.contains(popup.getPopupContainer());
	      }
	      return !(popup.isShown() && (popupOnTop || popupInside));
	    }
	    /**
	     * @private
	     * @param {BaseEvent} event
	     */
	  }, {
	    key: "handlePopupInit",
	    value: function handlePopupInit(event) {
	      const data = event.getCompatData();
	      const bindElement = data[1];
	      const params = data[2];
	      if (!main_core.Type.isElementNode(params.targetContainer) && main_core.Type.isElementNode(bindElement) && this.getContentContainer().contains(bindElement)) {
	        params.targetContainer = this.getContentContainer();
	      }
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleFrameFocus",
	    value: function handleFrameFocus(event) {
	      this.firePageEvent('onFrameFocus');
	    }
	    /**
	     * @private
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handleOverlayClick",
	    value: function handleOverlayClick(event) {
	      if (event.target === this.getOverlay()) {
	        if (this.animation === null) {
	          this.close();
	          event.stopPropagation();
	        } else {
	          event.preventDefault();
	        }
	      }
	    }
	    /**
	     * @private
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handlePrintBtnClick",
	    value: function handlePrintBtnClick(event) {
	      if (this.isSelfContained()) {
	        const frame = document.createElement('iframe');
	        frame.src = 'about:blank';
	        frame.name = 'sidepanel-print-frame';
	        main_core.Dom.style(frame, 'display', 'none');
	        main_core.Dom.append(frame, document.body);
	        const frameWindow = frame.contentWindow;
	        const frameDoc = frameWindow.document;
	        frameDoc.open();
	        frameDoc.write('<html><head>');
	        let headTags = '';
	        const links = document.head.querySelectorAll('link, style');
	        for (const link of links) {
	          headTags += link.outerHTML;
	        }
	        headTags += '<style>html, body { background: #fff !important; height: 100%; }</style>';
	        frameDoc.write(headTags);
	        frameDoc.write('</head><body>');
	        frameDoc.write(this.getContentContainer().innerHTML);
	        frameDoc.write('</body></html>');
	        frameDoc.close();
	        frameWindow.focus();
	        frameWindow.print();
	        setTimeout(() => {
	          main_core.Dom.remove(frame);
	          window.focus();
	        }, 1000);
	      } else {
	        this.focus();
	        this.getFrameWindow().print();
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "injectPrintStyles",
	    value: function injectPrintStyles() {
	      const frameDocument = this.getFrameWindow().document;
	      let bodyClass = '';
	      const classList = frameDocument.body.classList;
	      for (const className of classList) {
	        bodyClass += `.${className}`;
	      }
	      const bodyStyle = `@media print { body${bodyClass} { ` + 'background: #fff !important; ' + '-webkit-print-color-adjust: exact;' + 'color-adjust: exact; ' + '} }';
	      const style = frameDocument.createElement('style');
	      style.type = 'text/css';
	      if (style.styleSheet) {
	        style.styleSheet.cssText = bodyStyle;
	      } else {
	        style.appendChild(frameDocument.createTextNode(bodyStyle));
	      }
	      frameDocument.head.appendChild(style);
	    }
	  }, {
	    key: "refineUrl",
	    value: function refineUrl(url) {
	      if (main_core.Type.isStringFilled(url) && /IFRAME/.test(url)) {
	        return main_core.Uri.removeParam(url, ['IFRAME', 'IFRAME_TYPE']);
	      }
	      return url;
	    }
	  }], [{
	    key: "getEventFullName",
	    value: function getEventFullName(eventName) {
	      return `SidePanel.Slider:${eventName}`;
	    }
	  }]);
	  return Slider;
	}();
	function _subscribeEvents2(events) {
	  if (main_core.Type.isPlainObject(events)) {
	    for (const [eventName, fn] of Object.entries(events)) {
	      if (main_core.Type.isFunction(fn)) {
	        main_core_events.EventEmitter.subscribe(this, Slider.getEventFullName(eventName), fn, {
	          compatMode: true
	        });
	      }
	    }
	  }
	}
	function _getAnimationState2(mode) {
	  const states = {
	    right: {
	      start: {
	        translateX: 100,
	        translateY: 0,
	        opacity: 0,
	        scale: 0,
	        progress: 0,
	        intensity: 0
	      },
	      end: {
	        translateX: 0,
	        translateY: 0,
	        opacity: this.overlayOpacity,
	        scale: 100,
	        progress: 100,
	        intensity: 255
	      }
	    },
	    bottom: {
	      start: {
	        translateX: 0,
	        translateY: 100,
	        opacity: 0,
	        scale: 0,
	        progress: 0,
	        intensity: 0
	      },
	      end: {
	        translateX: 0,
	        translateY: 0,
	        opacity: this.overlayOpacity,
	        scale: 100,
	        progress: 100,
	        intensity: 255
	      }
	    },
	    top: {
	      start: {
	        translateX: 0,
	        translateY: -100,
	        opacity: 0,
	        scale: 0,
	        progress: 0,
	        intensity: 0
	      },
	      end: {
	        translateX: 0,
	        translateY: 0,
	        opacity: this.overlayOpacity,
	        scale: 100,
	        progress: 100,
	        intensity: 255
	      }
	    }
	  };
	  return states[babelHelpers.classPrivateFieldGet(this, _startPosition)][mode];
	}
	function _calculateOuterBoundary2() {
	  const outerBoundary = this.calculateOuterBoundary();
	  return main_core.Runtime.merge(main_core.Type.isPlainObject(outerBoundary) ? outerBoundary : {}, this.getOuterBoundary());
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3;
	let ToolbarItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ToolbarItem, _EventEmitter);
	  function ToolbarItem(itemOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, ToolbarItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ToolbarItem).call(this));
	    _this.setEventNamespace('BX.Main.SidePanel.ToolbarItem');
	    const options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};
	    _this.id = main_core.Type.isStringFilled(options.id) ? options.id : `toolbar-item-${main_core.Text.getRandom().toLowerCase()}`;
	    _this.title = '';
	    _this.url = '';
	    _this.entityType = '';
	    _this.entityId = '0';
	    _this.entityName = '';
	    _this.refs = new main_core.Cache.MemoryCache();
	    _this.rendered = false;
	    _this.setTitle(options.title);
	    _this.setUrl(options.url);
	    _this.setEntityType(options.entityType);
	    _this.setEntityId(options.entityId);
	    return _this;
	  }
	  babelHelpers.createClass(ToolbarItem, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getUrl",
	    value: function getUrl() {
	      return this.url;
	    }
	  }, {
	    key: "setUrl",
	    value: function setUrl(url) {
	      if (main_core.Type.isStringFilled(url)) {
	        this.url = url;
	        if (this.rendered) {
	          this.getContainer().href = url;
	        }
	      }
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      if (main_core.Type.isStringFilled(title)) {
	        this.title = title;
	        if (this.rendered) {
	          this.getTitleContainer().textContent = title;
	        }
	      }
	    }
	  }, {
	    key: "getEntityType",
	    value: function getEntityType() {
	      return this.entityType;
	    }
	  }, {
	    key: "setEntityType",
	    value: function setEntityType(entityType) {
	      if (main_core.Type.isStringFilled(entityType)) {
	        this.entityType = entityType;
	      }
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.entityId;
	    }
	  }, {
	    key: "setEntityId",
	    value: function setEntityId(entityId) {
	      if (main_core.Type.isNumber(entityId) || main_core.Type.isStringFilled(entityId)) {
	        this.entityId = String(entityId);
	      }
	    }
	  }, {
	    key: "getEntityName",
	    value: function getEntityName() {
	      return this.entityName;
	    }
	  }, {
	    key: "setEntityName",
	    value: function setEntityName(entityName) {
	      if (main_core.Type.isStringFilled(entityName)) {
	        this.entityName = entityName;
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.refs.remember('container', () => {
	        return main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div 
					class="side-panel-toolbar-item"
					onmouseenter="${0}"
					onmouseleave="${0}"
				>
					${0}
					<div class="side-panel-toolbar-item-remove-btn" onclick="${0}">
						<div class="ui-icon-set --cross-20" style="--ui-icon-set__icon-size: 100%;"></div>
					</div>
				</div>
			`), this.handleMouseEnter.bind(this), this.handleMouseLeave.bind(this), this.getTitleContainer(), this.handleRemoveBtnClick.bind(this));
	      });
	    }
	  }, {
	    key: "isRendered",
	    value: function isRendered() {
	      return this.rendered;
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      return this.refs.remember('title', () => {
	        return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<a 
					class="side-panel-toolbar-item-title"
					href="${0}"
					data-slider-maximize="true"
				>${0}</a>
			`), encodeURI(this.getUrl()), main_core.Text.encode(this.getTitle()));
	      });
	    }
	  }, {
	    key: "prependTo",
	    value: function prependTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.prepend(this.getContainer(), node);
	        this.rendered = true;
	      }
	    }
	  }, {
	    key: "appendTo",
	    value: function appendTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.append(this.getContainer(), node);
	        this.rendered = true;
	      }
	    }
	  }, {
	    key: "insertBefore",
	    value: function insertBefore(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.insertBefore(this.getContainer(), node);
	        this.rendered = true;
	      }
	    }
	  }, {
	    key: "insertAfter",
	    value: function insertAfter(node) {
	      if (main_core.Type.isDomNode(node)) {
	        main_core.Dom.insertAfter(this.getContainer(), node);
	        this.rendered = true;
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      main_core.Dom.remove(this.getContainer());
	      this.rendered = false;
	    }
	  }, {
	    key: "showTooltip",
	    value: function showTooltip() {
	      const targetNode = this.getContainer();
	      const rect = targetNode.getBoundingClientRect();
	      const targetNodeWidth = rect.width;
	      const popupWidth = Math.min(Math.max(100, this.getTitleContainer().scrollWidth + 20), 300);
	      const hint = main_popup.PopupManager.create({
	        id: 'sidepanel-toolbar-item-hint',
	        cacheable: false,
	        bindElement: rect,
	        bindOptions: {
	          forceBindPosition: true,
	          forceTop: true,
	          position: 'top'
	        },
	        width: popupWidth,
	        content: main_core.Tag.render(_t3 || (_t3 = _$1`
				<div class="sidepanel-toolbar-item-hint">
					<div class="sidepanel-toolbar-item-hint-title">${0}</div>
					<div class="sidepanel-toolbar-item-hint-content">${0}</div>
				</div>
			`), main_core.Text.encode(this.getEntityName()), main_core.Text.encode(this.getTitle())),
	        darkMode: true,
	        fixed: true,
	        offsetTop: 0,
	        events: {
	          onShow: event => {
	            const popup = event.getTarget();
	            const offsetLeft = targetNodeWidth / 2 - popupWidth / 2;
	            const angleShift = main_popup.Popup.getOption('angleLeftOffset') - main_popup.Popup.getOption('angleMinTop');
	            popup.setAngle({
	              offset: popupWidth / 2 - angleShift
	            });
	            popup.setOffset({
	              offsetLeft: offsetLeft + main_popup.Popup.getOption('angleLeftOffset')
	            });
	          }
	        }
	      });
	      hint.show();
	      hint.adjustPosition();
	    }
	  }, {
	    key: "hideTooltip",
	    value: function hideTooltip() {
	      const hint = main_popup.PopupManager.getPopupById('sidepanel-toolbar-item-hint');
	      if (hint) {
	        hint.close();
	      }
	    }
	  }, {
	    key: "handleMouseEnter",
	    value: function handleMouseEnter() {
	      this.showTooltip();
	    }
	  }, {
	    key: "handleMouseLeave",
	    value: function handleMouseLeave() {
	      this.hideTooltip();
	    }
	  }, {
	    key: "handleRemoveBtnClick",
	    value: function handleRemoveBtnClick(event) {
	      event.stopPropagation();
	      this.emit('onRemove');
	    }
	  }, {
	    key: "toJSON",
	    value: function toJSON() {
	      return {
	        title: this.getTitle(),
	        url: this.getUrl(),
	        entityType: this.getEntityType(),
	        entityId: this.getEntityId()
	      };
	    }
	  }]);
	  return ToolbarItem;
	}(main_core_events.EventEmitter);

	let instance = null;
	function getInstance() {
	  const topWindow = main_pageobject.PageObject.getRootWindow();
	  if (topWindow !== window) {
	    return topWindow.BX.SidePanel.Instance;
	  }
	  if (instance === null) {
	    instance = new SliderManager();
	  }
	  return instance;
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$1,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	let Toolbar = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Toolbar, _EventEmitter);
	  function Toolbar(toolbarOptions) {
	    var _this;
	    babelHelpers.classCallCheck(this, Toolbar);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Toolbar).call(this));
	    _this.setEventNamespace('BX.Main.SidePanel.Toolbar');
	    const options = main_core.Type.isPlainObject(toolbarOptions) ? toolbarOptions : {};
	    if (!main_core.Type.isStringFilled(options.context)) {
	      throw new Error('BX.Main.SidePanel.Toolbar: "context" parameter is required.');
	    }
	    _this.context = options.context;
	    _this.items = [];
	    _this.rendered = false;
	    _this.refs = new main_core.Cache.MemoryCache();
	    _this.container = null;
	    _this.lsKey = 'bx.sidepanel.toolbar.item';
	    _this.initialPosition = {
	      right: '5px',
	      bottom: '20px'
	    };
	    _this.shiftedPosition = {
	      right: '5px',
	      bottom: '20px'
	    };
	    if (main_core.Type.isPlainObject(options.position)) {
	      _this.initialPosition = options.position;
	    }
	    if (main_core.Type.isPlainObject(options.shiftedPosition)) {
	      _this.shiftedPosition = options.shiftedPosition;
	    }
	    _this.collapsed = options.collapsed !== false;
	    _this.muted = false;
	    _this.shifted = false;
	    _this.maxVisibleItems = main_core.Type.isNumber(options.maxVisibleItems) ? Math.max(options.maxVisibleItems, 1) : 5;
	    _this.addItems(options.items);
	    const item = _this.restoreItemFromLocalStorage();
	    if (item !== null) {
	      const {
	        entityType,
	        entityId
	      } = item;
	      if (_this.getItem(entityType, entityId)) {
	        _this.clearLocalStorage();
	      } else {
	        _this.minimizeItem(item);
	      }
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Toolbar, [{
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getContainer(), '--show');
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return main_core.Dom.hasClass(this.getContainer(), '--show');
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getContainer(), '--show');
	    }
	  }, {
	    key: "mute",
	    value: function mute() {
	      if (this.muted) {
	        return false;
	      }
	      this.muted = true;
	      main_core.Dom.addClass(this.getContainer(), '--muted');
	      return true;
	    }
	  }, {
	    key: "unmute",
	    value: function unmute() {
	      if (!this.muted) {
	        return false;
	      }
	      this.muted = false;
	      main_core.Dom.removeClass(this.getContainer(), '--muted');
	      return true;
	    }
	  }, {
	    key: "isMuted",
	    value: function isMuted() {
	      return this.muted;
	    }
	  }, {
	    key: "toggleMuteness",
	    value: function toggleMuteness() {
	      if (this.canShowOnTop()) {
	        return this.unmute();
	      }
	      return this.mute();
	    }
	  }, {
	    key: "shift",
	    value: function shift() {
	      if (this.shifted) {
	        return false;
	      }
	      this.shifted = true;
	      main_core.Dom.addClass(this.getContainer(), '--shifted');
	      main_core.Dom.style(document.body, '--side-panel-toolbar-shifted', 1);
	      this.setPosition(this.getContainer(), this.shiftedPosition);
	      return true;
	    }
	  }, {
	    key: "unshift",
	    value: function unshift() {
	      if (!this.shifted) {
	        return false;
	      }
	      this.shifted = false;
	      main_core.Dom.removeClass(this.getContainer(), '--shifted');
	      main_core.Dom.style(document.body, '--side-panel-toolbar-shifted', null);
	      this.setPosition(this.getContainer(), this.initialPosition);
	      return true;
	    }
	  }, {
	    key: "isShifted",
	    value: function isShifted() {
	      return this.shifted;
	    }
	  }, {
	    key: "toggleShift",
	    value: function toggleShift() {
	      const sliders = getInstance().getOpenSliders();
	      if (sliders.length === 0 || sliders.length === 1 && !sliders[0].isOpen()) {
	        return this.unshift();
	      }
	      return this.shift();
	    }
	  }, {
	    key: "setPosition",
	    value: function setPosition(container, position) {
	      for (const prop of ['top', 'right', 'bottom', 'left']) {
	        main_core.Dom.style(container, prop, null);
	        if (main_core.Type.isStringFilled(position[prop])) {
	          main_core.Dom.style(container, prop, position[prop]);
	        }
	      }
	    }
	  }, {
	    key: "collapse",
	    value: function collapse(immediately = false) {
	      if (this.collapsed) {
	        return;
	      }
	      if (immediately === true) {
	        main_core.Dom.addClass(this.getContainer(), '--collapsed');
	        main_core.Dom.style(this.getContentContainer(), 'width', null);
	      } else {
	        const width = this.getContentContainer().scrollWidth;
	        main_core.Dom.style(this.getContentContainer(), 'width', `${width}px`);
	        main_core.Event.unbindAll(this.getContentContainer(), 'transitionend');
	        requestAnimationFrame(() => {
	          requestAnimationFrame(() => {
	            main_core.Dom.style(this.getContentContainer(), 'width', 0);
	            main_core.Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
	              main_core.Dom.addClass(this.getContainer(), '--collapsed');
	              main_core.Dom.style(this.getContentContainer(), 'width', null);
	            });
	          });
	        });
	      }
	      this.collapsed = true;
	    }
	  }, {
	    key: "expand",
	    value: function expand(immediately = false) {
	      if (!this.collapsed) {
	        return;
	      }
	      if (immediately === true) {
	        main_core.Dom.removeClass(this.getContainer(), '--collapsed');
	        main_core.Dom.style(this.getContentContainer(), 'width', null);
	      } else {
	        main_core.Dom.removeClass(this.getContainer(), '--collapsed');
	        const width = this.getContentContainer().scrollWidth;
	        main_core.Dom.style(this.getContentContainer(), 'width', 0);
	        main_core.Event.unbindAll(this.getContentContainer(), 'transitionend');
	        requestAnimationFrame(() => {
	          requestAnimationFrame(() => {
	            main_core.Dom.style(this.getContentContainer(), 'width', `${width}px`);
	            main_core.Event.bindOnce(this.getContentContainer(), 'transitionend', () => {
	              main_core.Dom.style(this.getContentContainer(), 'width', null);
	            });
	          });
	        });
	      }
	      this.collapsed = false;
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      if (this.collapsed) {
	        this.request('expand');
	        this.expand();
	      } else {
	        this.request('collapse');
	        this.collapse();
	      }
	    }
	  }, {
	    key: "isCollapsed",
	    value: function isCollapsed() {
	      return this.collapsed;
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getItemsCount",
	    value: function getItemsCount() {
	      return this.items.length;
	    }
	  }, {
	    key: "addItems",
	    value: function addItems(itemsOptions) {
	      if (main_core.Type.isArrayFilled(itemsOptions)) {
	        itemsOptions.forEach(itemOptions => {
	          this.addItem(itemOptions);
	        });
	      }
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(itemOptions) {
	      const item = this.createItem(itemOptions);
	      if (item === null) {
	        return null;
	      }
	      this.items.push(item);
	      if (this.rendered) {
	        this.redraw();
	      }
	      return item;
	    }
	    /**
	     *
	     * @param itemOptions
	     * @returns {ToolbarItem|null}
	     */
	  }, {
	    key: "prependItem",
	    value: function prependItem(itemOptions) {
	      const item = this.createItem(itemOptions);
	      if (item === null) {
	        return null;
	      }
	      this.items.unshift(item);
	      if (this.rendered) {
	        this.redraw();
	      }
	      return item;
	    }
	  }, {
	    key: "createItem",
	    value: function createItem(itemOptions) {
	      const options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};
	      if (!main_core.Type.isStringFilled(options.entityType) || !(main_core.Type.isStringFilled(options.entityId) || main_core.Type.isNumber(options.entityId)) || !main_core.Type.isStringFilled(options.title) || !main_core.Type.isStringFilled(options.url)) {
	        return null;
	      }
	      const item = new ToolbarItem(options);
	      if (!main_core.Type.isStringFilled(item.getEntityName())) {
	        const minimizeOptions = getInstance().getMinimizeOptions(item.getUrl());
	        if (main_core.Type.isPlainObject(minimizeOptions) && main_core.Type.isStringFilled(minimizeOptions.entityName)) {
	          item.setEntityName(minimizeOptions.entityName);
	        }
	      }
	      item.subscribe('onRemove', this.handleItemRemove.bind(this));
	      return item;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "minimizeItem",
	    value: function minimizeItem(itemOptions) {
	      const {
	        entityType,
	        entityId
	      } = itemOptions;
	      let item = this.getItem(entityType, entityId);
	      const itemExists = item !== null;
	      if (!itemExists) {
	        item = this.prependItem(itemOptions);
	      }
	      if (item !== null) {
	        if (!itemExists) {
	          this.saveItemToLocalStorage(item);
	        }
	        this.request('minimize', item).then(response => {
	          if (response.status === 'success') {
	            this.clearLocalStorage();
	          }
	        }).catch(() => {
	          this.clearLocalStorage();
	          this.removeItem(item);
	        });
	      }
	      return item;
	    }
	  }, {
	    key: "saveItemToLocalStorage",
	    value: function saveItemToLocalStorage(item) {
	      const cache = {
	        item,
	        ttl: Date.now()
	      };
	      localStorage.setItem(this.lsKey, JSON.stringify(cache));
	    }
	  }, {
	    key: "restoreItemFromLocalStorage",
	    value: function restoreItemFromLocalStorage() {
	      const data = localStorage.getItem(this.lsKey);
	      if (main_core.Type.isStringFilled(data)) {
	        const {
	          item,
	          ttl
	        } = JSON.parse(data);
	        if (Date.now() - ttl > 10000) {
	          this.clearLocalStorage();
	          return null;
	        }
	        if (main_core.Type.isPlainObject(item)) {
	          return item;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "clearLocalStorage",
	    value: function clearLocalStorage() {
	      localStorage.removeItem(this.lsKey);
	    }
	  }, {
	    key: "getContext",
	    value: function getContext() {
	      return this.context;
	    }
	  }, {
	    key: "request",
	    value: function request(action, item, data) {
	      const additional = main_core.Type.isPlainObject(data) ? data : {};
	      return main_core.ajax.runAction(`main.api.sidepanel.toolbar.${action}`, {
	        json: {
	          toolbar: {
	            context: this.getContext()
	          },
	          item: item ? item.toJSON() : null,
	          ...additional
	        }
	      });
	    }
	  }, {
	    key: "handleItemRemove",
	    value: function handleItemRemove(event) {
	      const item = event.getTarget();
	      item.hideTooltip();
	      this.removeItem(item);
	    }
	  }, {
	    key: "handleMenuItemRemove",
	    value: function handleMenuItemRemove(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      const itemId = event.currentTarget.dataset.menuItemId;
	      const itemToRemove = this.getItemById(itemId);
	      if (itemToRemove) {
	        this.removeItem(itemToRemove);
	      }
	      const menu = this.getMenu();
	      if (menu) {
	        menu.removeMenuItem(itemId);
	        const invisibleItemsCount = this.getItems().reduce((count, item) => {
	          return item.isRendered() ? count : count + 1;
	        }, 0);
	        if (invisibleItemsCount > 0) {
	          menu.getPopupWindow().adjustPosition();
	        } else {
	          menu.close();
	        }
	      }
	    }
	  }, {
	    key: "removeItem",
	    value: function removeItem(itemToRemove) {
	      itemToRemove.remove();
	      this.items = this.items.filter(item => {
	        return item !== itemToRemove;
	      });
	      const restored = this.restoreItemFromLocalStorage();
	      if (restored !== null) {
	        const {
	          entityType,
	          entityId
	        } = restored;
	        if (itemToRemove.getEntityType() === entityType && itemToRemove.getEntityId() === entityId) {
	          this.clearLocalStorage();
	        }
	      }
	      if (this.rendered) {
	        this.redraw();
	        this.request('remove', itemToRemove);
	        if (this.getItemsCount() === 0) {
	          this.hide();
	        }
	      }
	    }
	  }, {
	    key: "redraw",
	    value: function redraw() {
	      let visibleItemsCount = 0;
	      for (let i = 0; i < this.getItems().length; i++) {
	        const item = this.getItems()[i];
	        if (visibleItemsCount >= this.maxVisibleItems) {
	          if (item.isRendered()) {
	            item.remove();
	          }
	        } else {
	          if (!item.isRendered()) {
	            const previousItem = this.getItems()[i - 1] || null;
	            const nextItem = this.getItems()[i + 1] || null;
	            if (previousItem) {
	              item.insertAfter(previousItem.getContainer());
	            } else if (nextItem) {
	              // eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
	              item.insertBefore(nextItem.getContainer());
	            } else {
	              item.appendTo(this.getItemsContainer());
	            }
	          }
	          visibleItemsCount++;
	        }
	      }
	    }
	  }, {
	    key: "removeAll",
	    value: function removeAll() {
	      this.getItemsContainer().innerHTML = '';
	      this.items = [];
	      this.clearLocalStorage();
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(entityType, entityId) {
	      return this.items.find(item => {
	        return item.getEntityType() === entityType && item.getEntityId() === String(entityId);
	      }) || null;
	    }
	  }, {
	    key: "getItemByUrl",
	    value: function getItemByUrl(url) {
	      return this.items.find(item => item.getUrl() === url) || null;
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(id) {
	      return this.items.find(item => item.getId() === id) || null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      return this.refs.remember('container', () => {
	        const classes = [];
	        if (this.collapsed) {
	          classes.push('--collapsed');
	        }
	        const container = main_core.Tag.render(_t$2 || (_t$2 = _$2`
				<div class="side-panel-toolbar ${0}">
					${0}
					<div class="side-panel-toolbar-toggle" onclick="${0}"></div>
				</div>
			`), classes.join(' '), this.getContentContainer(), this.handleToggleClick.bind(this));
	        this.setPosition(container, this.initialPosition);
	        main_core.Dom.append(container, document.body);
	        main_core.ZIndexManager.register(container, {
	          alwaysOnTop: true
	        });
	        this.rendered = true;
	        const toggleMuteness = main_core.Runtime.debounce(this.toggleMuteness, 50, this);
	        main_core_events.EventEmitter.subscribe('BX.Main.Popup:onShow', toggleMuteness);
	        main_core_events.EventEmitter.subscribe('BX.Main.Popup:onClose', toggleMuteness);
	        main_core_events.EventEmitter.subscribe('BX.Main.Popup:onDestroy', toggleMuteness);
	        main_core_events.EventEmitter.subscribe('onWindowClose', toggleMuteness);
	        main_core_events.EventEmitter.subscribe('onWindowRegister', toggleMuteness);
	        let forceCollapsed = false;
	        const onSliderClose = () => {
	          this.toggleMuteness();
	          if (this.isMuted()) {
	            return;
	          }
	          this.toggleShift();
	          if (!this.isShifted() && forceCollapsed) {
	            forceCollapsed = false;
	            this.expand();
	          }
	        };
	        main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClosing', onSliderClose);
	        main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', onSliderClose);
	        main_core_events.EventEmitter.subscribe('SidePanel.Slider:onDestroyComplete', onSliderClose);
	        main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpening', () => {
	          this.toggleMuteness();
	          if (this.isMuted()) {
	            return;
	          }
	          if (!this.isCollapsed()) {
	            forceCollapsed = true;
	            this.collapse();
	          }
	          this.toggleShift();
	        });
	        main_core_events.EventEmitter.subscribe('BX.UI.Viewer.Controller:onBeforeShow', toggleMuteness);
	        main_core_events.EventEmitter.subscribe('BX.UI.Viewer.Controller:onClose', main_core.Runtime.debounce(this.toggleMuteness, 500, this));
	        main_core.Event.bind(window, 'resize', main_core.Runtime.throttle(() => {
	          const menu = this.getMenu();
	          if (menu !== null) {
	            menu.close();
	          }
	        }, 300));
	        return container;
	      });
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      return this.refs.remember('content-container', () => {
	        return main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
				<div class="side-panel-toolbar-content">
					<div class="side-panel-toolbar-collapse-btn" onclick="${0}">
						<div class="ui-icon-set --chevron-right"></div>
					</div>
					${0}
					${0}
				</div>
			`), this.handleToggleClick.bind(this), this.getItemsContainer(), this.getMoreButton());
	      });
	    }
	  }, {
	    key: "getItemsContainer",
	    value: function getItemsContainer() {
	      return this.refs.remember('items-container', () => {
	        const container = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`<div class="side-panel-toolbar-items"></div>`));
	        [...this.items].slice(0, this.maxVisibleItems).forEach(item => {
	          item.appendTo(container);
	        });
	        return container;
	      });
	    }
	  }, {
	    key: "getMoreButton",
	    value: function getMoreButton() {
	      return this.refs.remember('more-button', () => {
	        return main_core.Tag.render(_t4 || (_t4 = _$2`
				<div class="side-panel-toolbar-more-btn" onclick="${0}">
					<div class="ui-icon-set --more"></div>
				</div>
			`), this.handleMoreBtnClick.bind(this));
	      });
	    }
	  }, {
	    key: "handleMoreBtnClick",
	    value: function handleMoreBtnClick(event) {
	      const targetNode = this.getMoreButton();
	      const rect = targetNode.getBoundingClientRect();
	      const targetNodeWidth = rect.width;
	      const items = [...this.items].filter(item => !item.isRendered()).map(item => {
	        const title = main_core.Type.isStringFilled(item.getEntityName()) ? `${item.getEntityName()}\n${item.getTitle()}` : item.getTitle();
	        return {
	          id: item.getId(),
	          html: this.createMenuItemText(item),
	          title,
	          href: item.getUrl(),
	          onclick: () => {
	            menu.close();
	          }
	        };
	      });
	      if (items.length > 0) {
	        items.push({
	          delimiter: true
	        });
	      }
	      items.push({
	        text: main_core.Loc.getMessage('MAIN_SIDEPANEL_REMOVE_ALL'),
	        onclick: () => {
	          this.removeAll();
	          this.hide();
	          menu.close();
	          this.request('removeAll');
	        }
	      });
	      const menu = main_popup.MenuManager.create({
	        id: 'sidepanel-toolbar-more-btn',
	        cacheable: false,
	        bindElement: rect,
	        bindOptions: {
	          forceBindPosition: true,
	          forceTop: true,
	          position: 'top'
	        },
	        maxWidth: 260,
	        fixed: true,
	        offsetTop: 0,
	        maxHeight: 305,
	        items,
	        events: {
	          onShow: event => {
	            const popup = event.getTarget();
	            const popupWidth = popup.getPopupContainer().offsetWidth;
	            const offsetLeft = targetNodeWidth / 2 - popupWidth / 2;
	            const angleShift = main_popup.Popup.getOption('angleLeftOffset') - main_popup.Popup.getOption('angleMinTop');
	            popup.setAngle({
	              offset: popupWidth / 2 - angleShift
	            });
	            popup.setOffset({
	              offsetLeft: offsetLeft + main_popup.Popup.getOption('angleLeftOffset')
	            });
	          }
	        }
	      });
	      menu.show();
	    }
	  }, {
	    key: "canShowOnTop",
	    value: function canShowOnTop() {
	      const popups = main_popup.PopupManager.getPopups();
	      for (const popup of popups) {
	        if (!popup.isShown()) {
	          continue;
	        }
	        if (popup.getId().startsWith('timeman_weekly_report_popup_') || popup.getId().startsWith('timeman_daily_report_popup_') || main_core.Dom.hasClass(popup.getPopupContainer(), 'b24-whatsnew__popup')) {
	          return false;
	        }
	      }
	      if (main_core.Reflection.getClass('BX.UI.Viewer.Instance') && BX.UI.Viewer.Instance.isOpen()) {
	        return false;
	      }
	      const sliders = getInstance().getOpenSliders();
	      for (const slider of sliders) {
	        const sliderId = slider.getUrl().toString();
	        if (slider.shouldHideToolbarOnOpen() || sliderId.startsWith('im:slider') || sliderId.startsWith('release-slider') || sliderId.startsWith('main:helper') || sliderId.startsWith('ui:info_helper')) {
	          return false;
	        }
	      }
	      const stack = main_core.ZIndexManager.getStack(document.body);
	      const components = stack === null ? [] : stack.getComponents();
	      for (const component of components) {
	        if (component.getOverlay() !== null && component.getOverlay().offsetWidth > 0) {
	          return false;
	        }
	      }
	      return true;
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu() {
	      return main_popup.MenuManager.getMenuById('sidepanel-toolbar-more-btn');
	    }
	  }, {
	    key: "createMenuItemText",
	    value: function createMenuItemText(item) {
	      return main_core.Tag.render(_t5 || (_t5 = _$2`
			<span class="side-panel-toolbar-menu-item">${0}</span>
		`), [main_core.Tag.render(_t6 || (_t6 = _$2`
					<span class="side-panel-toolbar-menu-item-title">${0}</span>
				`), main_core.Text.encode(item.getTitle())), main_core.Tag.render(_t7 || (_t7 = _$2`
					<span
						class="side-panel-toolbar-menu-item-remove"
						data-slider-ignore-autobinding="true"
						data-menu-item-id="${0}"
						onclick="${0}"
					>
						<span class="ui-icon-set --cross-20" data-slider-ignore-autobinding="true"></span>
					</span>
				`), item.getId(), this.handleMenuItemRemove.bind(this))]);
	    }
	  }, {
	    key: "handleToggleClick",
	    value: function handleToggleClick() {
	      this.toggle();
	    }
	  }]);
	  return Toolbar;
	}(main_core_events.EventEmitter);

	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	let sliderClassName = null;
	let sliderDefaultOptions = null;
	let sliderPriorityOptions = null;

	/**
	 * @namespace BX.SidePanel
	 * @alias Manager
	 */
	var _createSlider = /*#__PURE__*/new WeakSet();
	var _addOpenSlider = /*#__PURE__*/new WeakSet();
	var _removeOpenSlider = /*#__PURE__*/new WeakSet();
	var _setLastOpenSlider = /*#__PURE__*/new WeakSet();
	var _resetLastOpenSlider = /*#__PURE__*/new WeakSet();
	var _getLastOffset = /*#__PURE__*/new WeakSet();
	var _getMinOffset = /*#__PURE__*/new WeakSet();
	var _getMaxOffset = /*#__PURE__*/new WeakSet();
	var _getItemOrigin = /*#__PURE__*/new WeakSet();
	let SliderManager = /*#__PURE__*/function () {
	  function SliderManager() {
	    babelHelpers.classCallCheck(this, SliderManager);
	    _classPrivateMethodInitSpec$2(this, _getItemOrigin);
	    _classPrivateMethodInitSpec$2(this, _getMaxOffset);
	    _classPrivateMethodInitSpec$2(this, _getMinOffset);
	    _classPrivateMethodInitSpec$2(this, _getLastOffset);
	    _classPrivateMethodInitSpec$2(this, _resetLastOpenSlider);
	    _classPrivateMethodInitSpec$2(this, _setLastOpenSlider);
	    _classPrivateMethodInitSpec$2(this, _removeOpenSlider);
	    _classPrivateMethodInitSpec$2(this, _addOpenSlider);
	    _classPrivateMethodInitSpec$2(this, _createSlider);
	    this.anchorRules = [];
	    this.anchorBinding = true;
	    this.openSliders = [];
	    this.lastOpenSlider = null;
	    this.opened = false;
	    this.hidden = false;
	    this.hacksApplied = false;
	    this.pageUrl = this.getCurrentUrl();
	    this.pageTitle = this.getCurrentTitle();
	    this.titleChanged = false;
	    this.toolbar = null;
	    this.fullScreenSlider = null;
	    this.handleAnchorClick = this.handleAnchorClick.bind(this);
	    this.handleDocumentKeyDown = this.handleDocumentKeyDown.bind(this);
	    this.handleWindowResize = main_core.Runtime.throttle(this.handleWindowResize, 300, this);
	    this.handleWindowScroll = this.handleWindowScroll.bind(this);
	    this.handleTouchMove = this.handleTouchMove.bind(this);
	    this.handleSliderOpenStart = this.handleSliderOpenStart.bind(this);
	    this.handleSliderOpenComplete = this.handleSliderOpenComplete.bind(this);
	    this.handleSliderMaximizeStart = this.handleSliderMaximizeStart.bind(this);
	    this.handleSliderCloseStart = this.handleSliderCloseStart.bind(this);
	    this.handleSliderCloseComplete = this.handleSliderCloseComplete.bind(this);
	    this.handleSliderMinimizeStart = this.handleSliderMinimizeStart.bind(this);
	    this.handleSliderLoad = this.handleSliderLoad.bind(this);
	    this.handleSliderDestroy = this.handleSliderDestroy.bind(this);
	    this.handleEscapePress = this.handleEscapePress.bind(this);
	    this.handleFullScreenChange = this.handleFullScreenChange.bind(this);
	    main_core_events.EventEmitter.subscribe('SidePanel:open', this.open.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:close', this.close.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:closeAll', this.closeAll.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:destroy', this.destroy.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:hide', this.hide.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:unhide', this.unhide.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:postMessage', this.postMessage.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:postMessageAll', this.postMessageAll.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('SidePanel:postMessageTop', this.postMessageTop.bind(this), {
	      compatMode: true
	    });

	    // Compatibility
	    main_core_events.EventEmitter.subscribe('BX.Bitrix24.PageSlider:close', this.close.bind(this), {
	      compatMode: true
	    });
	    main_core_events.EventEmitter.subscribe('Bitrix24.Slider:postMessage', this.handlePostMessageCompatible.bind(this), {
	      compatMode: true
	    });
	  }
	  babelHelpers.createClass(SliderManager, [{
	    key: "open",
	    value: function open(url, options) {
	      const slider = _classPrivateMethodGet$2(this, _createSlider, _createSlider2).call(this, url, options);
	      if (slider === null) {
	        return false;
	      }
	      return this.tryApplyHacks(slider, () => slider.open());
	    }
	  }, {
	    key: "getMinimizeOptions",
	    value: function getMinimizeOptions(url) {
	      const rule = this.getUrlRule(url);
	      const ruleOptions = rule !== null && main_core.Type.isPlainObject(rule.options) ? rule.options : {};
	      return main_core.Type.isPlainObject(ruleOptions.minimizeOptions) ? ruleOptions.minimizeOptions : null;
	    }
	  }, {
	    key: "maximize",
	    value: function maximize(url, options) {
	      const slider = _classPrivateMethodGet$2(this, _createSlider, _createSlider2).call(this, url, options);
	      if (slider === null) {
	        return false;
	      }
	      return this.tryApplyHacks(slider, () => slider.maximize());
	    }
	  }, {
	    key: "tryApplyHacks",
	    value: function tryApplyHacks(slider, cb) {
	      if (!this.isOpen()) {
	        this.applyHacks(slider);
	      }
	      const success = cb();
	      if (!success) {
	        this.resetHacks(slider);
	      }
	      return success;
	    }
	  }, {
	    key: "isOpen",
	    value: function isOpen() {
	      return this.opened;
	    }
	  }, {
	    key: "close",
	    value: function close(immediately, callback) {
	      const topSlider = this.getTopSlider();
	      if (topSlider) {
	        topSlider.close(immediately, callback);
	      }
	    }
	  }, {
	    key: "closeAll",
	    value: function closeAll(immediately) {
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        const success = slider.close(immediately);
	        if (!success) {
	          break;
	        }
	      }
	    }
	  }, {
	    key: "minimize",
	    value: function minimize(immediately, callback) {
	      const topSlider = this.getTopSlider();
	      if (topSlider) {
	        topSlider.minimize(immediately, callback);
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.hidden) {
	        return false;
	      }
	      const topSlider = this.getTopSlider();
	      this.getOpenSliders().forEach(slider => {
	        slider.hide();
	      });
	      this.hidden = true;
	      this.resetHacks(topSlider);
	      return true;
	    }
	  }, {
	    key: "unhide",
	    value: function unhide() {
	      if (!this.hidden) {
	        return false;
	      }
	      this.getOpenSliders().forEach(slider => {
	        slider.unhide();
	      });
	      this.hidden = false;
	      setTimeout(() => {
	        this.applyHacks(this.getTopSlider());
	      }, 0);
	      return true;
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden() {
	      return this.hidden;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(sliderUrl) {
	      if (!main_core.Type.isStringFilled(sliderUrl)) {
	        return;
	      }
	      const url = this.refineUrl(sliderUrl);
	      const sliderToDestroy = this.getSlider(url);
	      if (this.getLastOpenSlider() && (sliderToDestroy || this.getLastOpenSlider().getUrl() === url)) {
	        this.getLastOpenSlider().destroy();
	      }
	      if (sliderToDestroy !== null) {
	        const openSliders = this.getOpenSliders();
	        for (let i = openSliders.length - 1; i >= 0; i--) {
	          const slider = openSliders[i];
	          slider.destroy();
	          if (slider === sliderToDestroy) {
	            break;
	          }
	        }
	      }
	    }
	  }, {
	    key: "reload",
	    value: function reload() {
	      const topSlider = this.getTopSlider();
	      if (topSlider) {
	        topSlider.reload();
	      }
	    }
	  }, {
	    key: "getTopSlider",
	    value: function getTopSlider() {
	      const count = this.openSliders.length;
	      return this.openSliders[count - 1] || null;
	    }
	  }, {
	    key: "getPreviousSlider",
	    value: function getPreviousSlider(currentSlider) {
	      let previousSlider = null;
	      const openSliders = this.getOpenSliders();
	      currentSlider = currentSlider || this.getTopSlider();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (slider === currentSlider) {
	          previousSlider = openSliders[i - 1] || null;
	          break;
	        }
	      }
	      return previousSlider;
	    }
	  }, {
	    key: "getSlider",
	    value: function getSlider(sliderUrl) {
	      const url = this.refineUrl(sliderUrl);
	      const openSliders = this.getOpenSliders();
	      for (const slider of openSliders) {
	        if (slider.getUrl() === url) {
	          return slider;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "getSliderByWindow",
	    value: function getSliderByWindow(window) {
	      const openSliders = this.getOpenSliders();
	      for (const slider of openSliders) {
	        if (slider.getFrameWindow() === window) {
	          return slider;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "getOpenSliders",
	    value: function getOpenSliders() {
	      return this.openSliders;
	    }
	  }, {
	    key: "getOpenSlidersCount",
	    value: function getOpenSlidersCount() {
	      return this.openSliders.length;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getLastOpenSlider",
	    value: function getLastOpenSlider() {
	      return this.lastOpenSlider;
	    }
	  }, {
	    key: "adjustLayout",
	    value: function adjustLayout() {
	      this.getOpenSliders().forEach(slider => {
	        slider.adjustLayout();
	      });
	    }
	  }, {
	    key: "createToolbar",
	    value: function createToolbar(options) {
	      if (this.toolbar === null) {
	        this.toolbar = new Toolbar(options);
	      }
	      return this.toolbar;
	    }
	  }, {
	    key: "getToolbar",
	    value: function getToolbar() {
	      return this.toolbar;
	    }
	  }, {
	    key: "refineUrl",
	    value: function refineUrl(url) {
	      if (main_core.Type.isStringFilled(url) && /IFRAME/.test(url)) {
	        return main_core.Uri.removeParam(url, ['IFRAME', 'IFRAME_TYPE']);
	      }
	      return url;
	    }
	  }, {
	    key: "getPageUrl",
	    value: function getPageUrl() {
	      return this.pageUrl;
	    }
	  }, {
	    key: "getCurrentUrl",
	    value: function getCurrentUrl() {
	      return window.location.pathname + window.location.search + window.location.hash;
	    }
	  }, {
	    key: "getPageTitle",
	    value: function getPageTitle() {
	      return this.pageTitle;
	    }
	  }, {
	    key: "getCurrentTitle",
	    value: function getCurrentTitle() {
	      let title = document.title;
	      if (!main_core.Type.isUndefined(window.BXIM)) {
	        title = title.replace(/^\(\d+\) /, ''); // replace a messenger counter.
	      }

	      return title;
	    }
	  }, {
	    key: "enterFullScreen",
	    value: function enterFullScreen() {
	      if (!this.getTopSlider() || this.getFullScreenSlider()) {
	        return;
	      }
	      const container = document.body;
	      if (container.requestFullscreen) {
	        main_core.Event.bind(document, 'fullscreenchange', this.handleFullScreenChange);
	        container.requestFullscreen();
	      } else if (container.webkitRequestFullScreen) {
	        main_core.Event.bind(document, 'webkitfullscreenchange', this.handleFullScreenChange);
	        container.webkitRequestFullScreen();
	      } else if (container.msRequestFullscreen) {
	        main_core.Event.bind(document, 'MSFullscreenChange', this.handleFullScreenChange);
	        container.msRequestFullscreen();
	      } else if (container.mozRequestFullScreen) {
	        main_core.Event.bind(document, 'mozfullscreenchange', this.handleFullScreenChange);
	        container.mozRequestFullScreen();
	      } else {
	        console.log('Slider: Full Screen mode is not supported.');
	      }
	    }
	  }, {
	    key: "exitFullScreen",
	    value: function exitFullScreen() {
	      if (!this.getFullScreenSlider()) {
	        return;
	      }
	      if (document.exitFullscreen) {
	        document.exitFullscreen();
	      } else if (document.webkitExitFullscreen) {
	        document.webkitExitFullscreen();
	      } else if (document.msExitFullscreen) {
	        document.msExitFullscreen();
	      } else if (document.mozCancelFullScreen) {
	        document.mozCancelFullScreen();
	      }
	    }
	  }, {
	    key: "getFullScreenElement",
	    value: function getFullScreenElement() {
	      return document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || null;
	    }
	  }, {
	    key: "getFullScreenSlider",
	    value: function getFullScreenSlider() {
	      return this.fullScreenSlider;
	    }
	  }, {
	    key: "handleFullScreenChange",
	    value: function handleFullScreenChange(event) {
	      if (this.getFullScreenElement()) {
	        this.fullScreenSlider = this.getTopSlider();
	        main_core.Dom.addClass(this.fullScreenSlider.getOverlay(), 'side-panel-fullscreen');
	        this.fullScreenSlider.fireEvent('onFullScreenEnter');
	      } else {
	        if (this.getFullScreenSlider()) {
	          main_core.Dom.removeClass(this.getFullScreenSlider().getOverlay(), 'side-panel-fullscreen');
	          this.fullScreenSlider.fireEvent('onFullScreenExit');
	          this.fullScreenSlider = null;
	        }
	        main_core.Event.unbind(document, event.type, this.handleFullScreenChange);
	        window.scrollTo(0, this.pageScrollTop);
	        setTimeout(() => {
	          this.adjustLayout();
	          const resizeEvent = document.createEvent('Event');
	          resizeEvent.initEvent('resize', true, true);
	          window.dispatchEvent(resizeEvent);
	        }, 1000);
	      }
	    }
	  }, {
	    key: "postMessage",
	    value: function postMessage(source, eventId, data) {
	      const sender = this.getSliderFromSource(source);
	      if (!sender) {
	        return;
	      }
	      let previousSlider = null;
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (slider === sender) {
	          previousSlider = openSliders[i - 1] || null;
	          break;
	        }
	      }
	      const sliderWindow = previousSlider ? previousSlider.getWindow() : window;
	      sliderWindow.BX.onCustomEvent('Bitrix24.Slider:onMessage', [sender, data]); // Compatibility

	      const event = new MessageEvent({
	        sender,
	        slider: previousSlider || null,
	        data,
	        eventId
	      });
	      if (previousSlider) {
	        previousSlider.firePageEvent(event);
	        previousSlider.fireFrameEvent(event);
	      } else {
	        main_core_events.EventEmitter.emit(event.getFullName().toLowerCase(), new main_core_events.BaseEvent({
	          data: [event],
	          compatData: [event]
	        }));
	      }
	    }
	  }, {
	    key: "postMessageAll",
	    value: function postMessageAll(source, eventId, data) {
	      const sender = this.getSliderFromSource(source);
	      if (!sender) {
	        return;
	      }
	      let event = null;
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (slider === sender) {
	          continue;
	        }
	        event = new MessageEvent({
	          sender,
	          slider,
	          data,
	          eventId
	        });
	        slider.firePageEvent(event);
	        slider.fireFrameEvent(event);
	      }
	      event = new MessageEvent({
	        sender,
	        slider: null,
	        data,
	        eventId
	      });
	      main_core_events.EventEmitter.emit(event.getFullName().toLowerCase(), new main_core_events.BaseEvent({
	        data: [event],
	        compatData: [event]
	      }));
	    }
	  }, {
	    key: "postMessageTop",
	    value: function postMessageTop(source, eventId, data) {
	      const sender = this.getSliderFromSource(source);
	      if (!sender) {
	        return;
	      }
	      const event = new MessageEvent({
	        sender,
	        slider: null,
	        data,
	        eventId
	      });
	      main_core_events.EventEmitter.emit(event.getFullName().toLowerCase(), new main_core_events.BaseEvent({
	        data: [event],
	        compatData: [event]
	      }));
	    }
	  }, {
	    key: "bindAnchors",
	    value: function bindAnchors(parameters) {
	      if (!main_core.Type.isPlainObject(parameters) || !main_core.Type.isArray(parameters.rules) || parameters.rules.length === 0) {
	        return;
	      }
	      if (this.anchorRules.length === 0) {
	        this.registerAnchorListener(window.document);
	      }
	      if (!(parameters.rules instanceof Object)) {
	        console.error('BX.SitePanel: anchor rules were created in a different context. ' + 'This might be a reason for a memory leak.');
	        console.trace();
	      }
	      parameters.rules.forEach(rule => {
	        var _rule$options;
	        if (main_core.Type.isArray(rule.condition)) {
	          for (let m = 0; m < rule.condition.length; m++) {
	            if (main_core.Type.isString(rule.condition[m])) {
	              rule.condition[m] = new RegExp(rule.condition[m], 'i');
	            }
	          }
	        }
	        (_rule$options = rule.options) !== null && _rule$options !== void 0 ? _rule$options : rule.options = {};
	        this.anchorRules.push(rule);
	      });
	    }
	  }, {
	    key: "isAnchorBinding",
	    value: function isAnchorBinding() {
	      return this.anchorBinding;
	    }
	  }, {
	    key: "enableAnchorBinding",
	    value: function enableAnchorBinding() {
	      this.anchorBinding = true;
	    }
	  }, {
	    key: "disableAnchorBinding",
	    value: function disableAnchorBinding() {
	      this.anchorBinding = false;
	    }
	  }, {
	    key: "registerAnchorListener",
	    value: function registerAnchorListener(targetDocument) {
	      main_core.Event.bind(targetDocument, 'click', this.handleAnchorClick, true);
	    }
	  }, {
	    key: "unregisterAnchorListener",
	    value: function unregisterAnchorListener(targetDocument) {
	      main_core.Event.unbind(targetDocument, 'click', this.handleAnchorClick, true);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleSliderOpenStart",
	    value: function handleSliderOpenStart(event) {
	      if (!event.isActionAllowed()) {
	        return;
	      }
	      const slider = event.getSlider();
	      if (slider.isDestroyed()) {
	        return;
	      }
	      if (this.getTopSlider()) {
	        this.exitFullScreen();
	        this.getTopSlider().hideOverlay();
	        slider.setOverlayBackground();
	        const sameWidth = this.getTopSlider().getOffset() === slider.getOffset() && this.getTopSlider().getWidth() === slider.getWidth() && this.getTopSlider().getCustomLeftBoundary() === slider.getCustomLeftBoundary();
	        if (!sameWidth) {
	          this.getTopSlider().showShadow();
	        }
	        this.getTopSlider().hideCloseBtn();
	        this.getTopSlider().hideExtraLabels();
	      } else {
	        slider.setOverlayAnimation(true);
	      }
	      _classPrivateMethodGet$2(this, _addOpenSlider, _addOpenSlider2).call(this, slider);

	      // this.getOpenSliders().forEach((currentSlider: Slider, index: number, openSliders: Slider[]) => {
	      // 	currentSlider.getLabel().moveAt(openSliders.length - index - 1); // move down
	      // });

	      this.losePageFocus();
	      if (!this.opened) {
	        this.pageUrl = this.getCurrentUrl();
	        this.pageTitle = this.getCurrentTitle();
	      }
	      this.opened = true;
	      _classPrivateMethodGet$2(this, _resetLastOpenSlider, _resetLastOpenSlider2).call(this);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleSliderOpenComplete",
	    value: function handleSliderOpenComplete(event) {
	      this.setBrowserHistory(event.getSlider());
	      this.updateBrowserTitle();
	      event.getSlider().setAnimation('sliding');
	      const openSliders = this.getOpenSliders();
	      const topSlider = event.getSlider();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (topSlider === slider) {
	          continue;
	        }
	        if (topSlider.getContainer().offsetLeft <= slider.getContainer().offsetLeft) {
	          main_core.Dom.addClass(slider.getOverlay(), '--invisible');
	        } else {
	          break;
	        }
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleSliderCloseStart",
	    value: function handleSliderCloseStart(event) {
	      if (!event.isActionAllowed()) {
	        return;
	      }
	      if (event.getSlider() && event.getSlider().isDestroyed()) {
	        return;
	      }
	      const previousSlider = this.getPreviousSlider();
	      const topSlider = this.getTopSlider();
	      this.exitFullScreen();

	      // this.getOpenSliders().forEach((slider, index, openSliders) => {
	      // 	slider.getLabel().moveAt(openSliders.length - index - 2); // move up
	      // });

	      let visibleSlider = null;
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (event.getSlider() === slider) {
	          continue;
	        }
	        if (visibleSlider === null || slider.getContainer().offsetLeft < visibleSlider.getContainer().offsetLeft) {
	          main_core.Dom.removeClass(slider.getOverlay(), '--invisible');
	          visibleSlider = slider;
	        }
	      }
	      if (previousSlider) {
	        previousSlider.unhideOverlay();
	        previousSlider.hideShadow();
	        previousSlider.showCloseBtn();
	        previousSlider.showExtraLabels();
	        if (topSlider) {
	          topSlider.hideOverlay();
	          topSlider.hideShadow();
	        }
	      }
	    }
	  }, {
	    key: "handleSliderMaximizeStart",
	    value: function handleSliderMaximizeStart(event) {
	      if (!event.isActionAllowed() || this.getToolbar() === null) {
	        return;
	      }
	      const slider = event.getSlider();
	      if (slider && slider.isDestroyed()) {
	        return;
	      }
	      const {
	        entityType,
	        entityId
	      } = slider.getMinimizeOptions() || {};
	      const item = this.getToolbar().getItem(entityType, entityId);
	      this.getToolbar().request('maximize', item);
	      const origin = _classPrivateMethodGet$2(this, _getItemOrigin, _getItemOrigin2).call(this, slider, item);
	      slider.setAnimation('scale', {
	        origin
	      });
	    }
	  }, {
	    key: "handleSliderMinimizeStart",
	    value: function handleSliderMinimizeStart(event) {
	      if (!event.isActionAllowed() || this.getToolbar() === null) {
	        return;
	      }
	      const slider = event.getSlider();
	      if (slider && slider.isDestroyed()) {
	        return;
	      }
	      if (!this.getToolbar().isShown()) {
	        this.getToolbar().show();
	      }
	      let title = slider.getTitle();
	      if (!title) {
	        title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
	      }
	      this.getToolbar().expand(true);
	      const minimizeOptions = this.getMinimizeOptions(slider.getUrl());
	      const {
	        entityType,
	        entityId,
	        url
	      } = slider.getMinimizeOptions() || minimizeOptions || {};
	      const item = this.getToolbar().minimizeItem({
	        title,
	        url: main_core.Type.isStringFilled(url) ? url : slider.getUrl(),
	        entityType,
	        entityId
	      });
	      const origin = _classPrivateMethodGet$2(this, _getItemOrigin, _getItemOrigin2).call(this, slider, item);
	      slider.setAnimation('scale', {
	        origin
	      });
	    }
	  }, {
	    key: "handleSliderCloseComplete",
	    /**
	     * @private
	     */
	    value: function handleSliderCloseComplete(event) {
	      const slider = event.getSlider();
	      if (slider === this.getTopSlider()) {
	        _classPrivateMethodGet$2(this, _setLastOpenSlider, _setLastOpenSlider2).call(this, slider);
	      }
	      event.getSlider().setAnimation('sliding');
	      this.cleanUpClosedSlider(slider);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleSliderDestroy",
	    value: function handleSliderDestroy(event) {
	      const slider = event.getSlider();
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onOpenStart', this.handleSliderOpenStart);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onBeforeOpenComplete', this.handleSliderOpenComplete);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onMaximizeStart', this.handleSliderMaximizeStart);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onCloseStart', this.handleSliderCloseStart);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onBeforeCloseComplete', this.handleSliderCloseComplete);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onMinimizeStart', this.handleSliderMinimizeStart);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onLoad', this.handleSliderLoad);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onDestroy', this.handleSliderDestroy);
	      main_core_events.EventEmitter.unsubscribe(slider, 'SidePanel.Slider:onEscapePress', this.handleEscapePress);
	      const frameWindow = event.getSlider().getFrameWindow();
	      if (frameWindow && !event.getSlider().allowCrossOrigin) {
	        this.unregisterAnchorListener(frameWindow.document);
	      }
	      if (slider === this.getLastOpenSlider()) {
	        this.lastOpenSlider = null;
	      }
	      this.cleanUpClosedSlider(slider);
	    }
	  }, {
	    key: "handleEscapePress",
	    value: function handleEscapePress(event) {
	      const topSlider = this.getTopSlider();
	      if (topSlider !== null && topSlider !== void 0 && topSlider.canCloseByEsc() && this.isOnTop(topSlider)) {
	        topSlider.close();
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "cleanUpClosedSlider",
	    value: function cleanUpClosedSlider(slider) {
	      _classPrivateMethodGet$2(this, _removeOpenSlider, _removeOpenSlider2).call(this, slider);
	      slider.unhideOverlay();
	      slider.hideShadow();

	      // this.getOpenSliders().forEach((slider, index, openSliders) => {
	      // 	slider.getLabel().moveAt(openSliders.length - index - 1); //update position
	      // });

	      if (this.getTopSlider()) {
	        this.getTopSlider().unhideOverlay();
	        this.getTopSlider().hideShadow();
	        this.getTopSlider().showCloseBtn();
	        this.getTopSlider().showExtraLabels();
	        this.getTopSlider().focus();
	      } else {
	        window.focus();
	      }
	      if (!this.getOpenSlidersCount()) {
	        this.resetHacks(slider);
	        this.opened = false;
	      }
	      this.resetBrowserHistory();
	      this.updateBrowserTitle();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleSliderLoad",
	    value: function handleSliderLoad(event) {
	      const frameWindow = event.getSlider().getFrameWindow();
	      if (frameWindow) {
	        this.registerAnchorListener(frameWindow.document);
	      }
	      this.setBrowserHistory(event.getSlider());
	      this.updateBrowserTitle();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handlePostMessageCompatible",
	    value: function handlePostMessageCompatible(source, data) {
	      this.postMessage(source, '', data);
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getSliderFromSource",
	    value: function getSliderFromSource(source) {
	      if (source instanceof Slider) {
	        return source;
	      }
	      if (main_core.Type.isStringFilled(source)) {
	        return this.getSlider(source);
	      }
	      if (source !== null && source === source.window && window !== source) {
	        return this.getSliderByWindow(source);
	      }
	      return null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "applyHacks",
	    value: function applyHacks(slider) {
	      if (this.hacksApplied) {
	        return false;
	      }
	      if (slider) {
	        slider.applyHacks();
	      }
	      this.disablePageScrollbar();
	      this.bindEvents();
	      if (slider) {
	        slider.applyPostHacks();
	      }
	      this.hacksApplied = true;
	      return true;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "resetHacks",
	    value: function resetHacks(slider) {
	      if (!this.hacksApplied) {
	        return false;
	      }
	      if (slider) {
	        slider.resetPostHacks();
	      }
	      this.enablePageScrollbar();
	      this.unbindEvents();
	      if (slider) {
	        slider.resetHacks();
	      }
	      this.hacksApplied = false;
	      return true;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core.Event.bind(document, 'keydown', this.handleDocumentKeyDown);
	      main_core.Event.bind(window, 'resize', this.handleWindowResize);
	      main_core.Event.bind(window, 'scroll', this.handleWindowScroll); // Live Comments can change scrollTop

	      if (main_core.Browser.isMobile()) {
	        main_core.Event.bind(document.body, 'touchmove', this.handleTouchMove);
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents() {
	      main_core.Event.unbind(document, 'keydown', this.handleDocumentKeyDown);
	      main_core.Event.unbind(window, 'resize', this.handleWindowResize);
	      main_core.Event.unbind(window, 'scroll', this.handleWindowScroll);
	      if (main_core.Browser.isMobile()) {
	        main_core.Event.unbind(document.body, 'touchmove', this.handleTouchMove);
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "disablePageScrollbar",
	    value: function disablePageScrollbar() {
	      const scrollWidth = window.innerWidth - document.documentElement.clientWidth;
	      document.body.style.paddingRight = scrollWidth + 'px';
	      main_core.Dom.style(document.body, '--scroll-shift-width', `${scrollWidth}px`);
	      main_core.Dom.addClass(document.body, 'side-panel-disable-scrollbar');
	      this.pageScrollTop = window.pageYOffset || document.documentElement.scrollTop;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "enablePageScrollbar",
	    value: function enablePageScrollbar() {
	      document.body.style.removeProperty('padding-right');
	      main_core.Dom.style(document.body, '--scroll-shift-width', null);
	      main_core.Dom.removeClass(document.body, 'side-panel-disable-scrollbar');
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "losePageFocus",
	    value: function losePageFocus() {
	      if (main_core.Type.isDomNode(document.activeElement)) {
	        document.activeElement.blur();
	      }
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleDocumentKeyDown",
	    value: function handleDocumentKeyDown(event) {
	      if (event.keyCode !== 27) {
	        return;
	      }
	      event.preventDefault(); // otherwise an iframe loading can be cancelled by a browser

	      this.handleEscapePress();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleWindowResize",
	    value: function handleWindowResize() {
	      this.adjustLayout();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "handleWindowScroll",
	    value: function handleWindowScroll() {
	      window.scrollTo(0, this.pageScrollTop);
	      this.adjustLayout();
	    }
	    /**
	     * @private
	     * @param {Event} event
	     */
	  }, {
	    key: "handleTouchMove",
	    value: function handleTouchMove(event) {
	      event.preventDefault();
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "isOnTop",
	    value: function isOnTop(slider) {
	      if (slider) {
	        const popups = main_popup.PopupManager.getPopups();
	        const isOnTopOfAllPopups = popups.every(popup => slider.isOnTopOfPopup(popup));
	        if (!isOnTopOfAllPopups) {
	          return false;
	        }
	      }

	      // Photo Slider or something else can cover Side Panel.
	      const centerX = document.documentElement.clientWidth / 2;
	      const centerY = document.documentElement.clientHeight / 2;
	      const element = document.elementFromPoint(centerX, centerY);
	      return main_core.Dom.hasClass(element, 'side-panel') || element.closest('.side-panel') !== null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "extractLinkFromEvent",
	    value: function extractLinkFromEvent(event) {
	      const target = event.target;
	      if (event.which !== 1 || !main_core.Type.isDomNode(target) || event.ctrlKey || event.metaKey) {
	        return null;
	      }
	      let a = target;
	      if (target.nodeName !== 'A' && main_core.Type.isElementNode(target)) {
	        a = target.closest('a');
	      }
	      if (!main_core.Type.isDomNode(a)) {
	        return null;
	      }

	      // do not use a.href here, the code will fail on links like <a href="#SG13"></a>
	      const href = a.getAttribute('href');
	      if (href) {
	        return {
	          url: href,
	          anchor: a,
	          target: a.getAttribute('target')
	        };
	      }
	      return null;
	    }
	    /**
	     * @private
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handleAnchorClick",
	    value: function handleAnchorClick(event) {
	      if (!this.isAnchorBinding()) {
	        return;
	      }
	      const link = this.extractLinkFromEvent(event);
	      if (!link || main_core.Dom.attr(link.anchor, 'data-slider-ignore-autobinding') !== null) {
	        return;
	      }
	      if (main_core.Dom.attr(event.target, 'data-slider-ignore-autobinding') !== null) {
	        return;
	      }
	      const rule = this.getUrlRule(link.url, link);
	      if (!this.isValidLink(rule, link)) {
	        return;
	      }
	      if (main_core.Type.isFunction(rule.handler)) {
	        rule.handler(event, link);
	      } else {
	        event.preventDefault();
	        if (main_core.Dom.attr(event.target, 'data-slider-maximize') === null) {
	          this.open(link.url, rule.options);
	        } else {
	          this.maximize(link.url, rule.options);
	        }
	      }
	    }
	    /**
	     * @public
	     * @param {string} url
	     */
	  }, {
	    key: "emulateAnchorClick",
	    value: function emulateAnchorClick(url) {
	      const link = {
	        url,
	        anchor: null,
	        target: null
	      };
	      const rule = this.getUrlRule(url, link);
	      if (!this.isValidLink(rule, link)) {
	        BX.reload(url);
	      } else if (main_core.Type.isFunction(rule.handler)) {
	        rule.handler(new MouseEvent('slider', {
	          bubbles: false,
	          cancelable: true
	        }), link);
	      } else {
	        this.open(link.url, rule.options);
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getUrlRule",
	    value: function getUrlRule(href, link) {
	      if (!main_core.Type.isStringFilled(href)) {
	        return null;
	      }
	      if (!main_core.Type.isPlainObject(link)) {
	        const a = document.createElement('a');
	        a.href = href;
	        link = {
	          url: href,
	          anchor: a,
	          target: ''
	        };
	      }
	      for (let k = 0; k < this.anchorRules.length; k++) {
	        const rule = this.anchorRules[k];
	        if (!main_core.Type.isArray(rule.condition)) {
	          continue;
	        }
	        for (let m = 0; m < rule.condition.length; m++) {
	          const matches = href.match(rule.condition[m]);
	          if (matches && !this.hasStopParams(href, rule.stopParameters)) {
	            link.matches = matches;
	            let options = main_core.Type.isFunction(rule.options) ? rule.options(link) : rule.options;
	            const minimizeOptions = main_core.Type.isFunction(rule.minimizeOptions) ? rule.minimizeOptions(link) : null;
	            if (main_core.Type.isPlainObject(minimizeOptions)) {
	              if (main_core.Type.isPlainObject(options)) {
	                options.minimizeOptions = minimizeOptions;
	              } else {
	                options = {
	                  minimizeOptions
	                };
	              }
	            }
	            if (main_core.Type.isStringFilled(rule.loader) && !main_core.Type.isStringFilled(options.loader)) {
	              options.loader = rule.loader;
	              delete rule.loader;
	            }
	            return {
	              ...rule,
	              options
	            };
	          }
	        }
	      }
	      return null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "isValidLink",
	    value: function isValidLink(rule, link) {
	      if (!rule) {
	        return false;
	      }
	      if (rule.allowCrossDomain !== true && main_core.ajax.isCrossDomain(link.url)) {
	        return false;
	      }
	      if (rule.mobileFriendly !== true && main_core.Browser.isMobile()) {
	        return false;
	      }
	      return !main_core.Type.isFunction(rule.validate) || rule.validate(link);
	    }
	    /**
	     * @private
	     * @param {BX.SidePanel.Slider} slider
	     */
	  }, {
	    key: "setBrowserHistory",
	    value: function setBrowserHistory(slider) {
	      if (!(slider instanceof Slider)) {
	        return;
	      }
	      if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded()) {
	        window.history.replaceState({}, '', slider.getUrl());
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "resetBrowserHistory",
	    value: function resetBrowserHistory() {
	      let topSlider = null;
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        const slider = openSliders[i];
	        if (slider.canChangeHistory() && slider.isOpen() && slider.isLoaded()) {
	          topSlider = slider;
	          break;
	        }
	      }
	      const url = topSlider ? topSlider.getUrl() : this.getPageUrl();
	      if (url) {
	        window.history.replaceState({}, '', url);
	      }
	    }
	    /**
	     * @public
	     */
	  }, {
	    key: "updateBrowserTitle",
	    value: function updateBrowserTitle() {
	      let title = null;
	      const openSliders = this.getOpenSliders();
	      for (let i = openSliders.length - 1; i >= 0; i--) {
	        title = this.getBrowserTitle(openSliders[i]);
	        if (main_core.Type.isStringFilled(title)) {
	          break;
	        }
	      }
	      if (main_core.Type.isStringFilled(title)) {
	        document.title = title;
	        this.titleChanged = true;
	      } else if (this.titleChanged) {
	        document.title = this.getPageTitle();
	        this.titleChanged = false;
	      }
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "getBrowserTitle",
	    value: function getBrowserTitle(slider) {
	      if (!slider || !slider.canChangeTitle() || !slider.isOpen() || !slider.isLoaded()) {
	        return null;
	      }
	      let title = slider.getTitle();
	      if (!title && !slider.isSelfContained()) {
	        title = slider.getFrameWindow() ? slider.getFrameWindow().document.title : null;
	      }
	      return main_core.Type.isStringFilled(title) ? title : null;
	    }
	    /**
	     * @private
	     */
	  }, {
	    key: "hasStopParams",
	    value: function hasStopParams(url, params) {
	      if (!params || !main_core.Type.isArray(params) || !main_core.Type.isStringFilled(url)) {
	        return false;
	      }
	      const questionPos = url.indexOf('?');
	      if (questionPos === -1) {
	        return false;
	      }
	      const query = url.slice(Math.max(0, questionPos));
	      for (const param of params) {
	        if (new RegExp(`[?&]${param}=`, 'i').test(query)) {
	          return true;
	        }
	      }
	      return false;
	    }
	    /**
	     * @deprecated use getLastOpenSlider method
	     */
	  }, {
	    key: "getLastOpenPage",
	    value: function getLastOpenPage() {
	      return this.getLastOpenSlider();
	    }
	    /**
	     * @deprecated use getTopSlider method
	     */
	  }, {
	    key: "getCurrentPage",
	    value: function getCurrentPage() {
	      return this.getTopSlider();
	    }
	  }], [{
	    key: "registerSliderClass",
	    value: function registerSliderClass(className, defaultOptions = null, priorityOptions = null) {
	      if (main_core.Type.isStringFilled(className)) {
	        sliderClassName = className;
	      }
	      if (main_core.Type.isPlainObject(defaultOptions)) {
	        sliderDefaultOptions = defaultOptions;
	      }
	      if (main_core.Type.isPlainObject(priorityOptions)) {
	        sliderPriorityOptions = priorityOptions;
	      }
	    }
	  }, {
	    key: "getSliderClass",
	    value: function getSliderClass() {
	      const sliderClass = sliderClassName === null ? null : main_core.Reflection.getClass(sliderClassName);
	      return sliderClass === null ? Slider : sliderClass;
	    }
	  }, {
	    key: "getSliderDefaultOptions",
	    value: function getSliderDefaultOptions() {
	      return sliderDefaultOptions === null ? {} : sliderDefaultOptions;
	    }
	  }, {
	    key: "getSliderPriorityOptions",
	    value: function getSliderPriorityOptions() {
	      return sliderPriorityOptions === null ? {} : sliderPriorityOptions;
	    }
	  }]);
	  return SliderManager;
	}();
	function _createSlider2(sliderUrl, sliderOptions) {
	  var _ref, _sliderOptions$useGlo;
	  if (!main_core.Type.isStringFilled(sliderUrl)) {
	    return null;
	  }
	  const url = this.refineUrl(sliderUrl);
	  if (this.isHidden()) {
	    this.unhide();
	  }
	  const topSlider = this.getTopSlider();
	  if (topSlider && topSlider.isOpen() && topSlider.getUrl() === url) {
	    return null;
	  }
	  if (this.getLastOpenSlider() && this.getLastOpenSlider().getUrl() === url) {
	    return this.getLastOpenSlider();
	  }
	  const rule = this.getUrlRule(url);
	  const ruleOptions = rule !== null && main_core.Type.isPlainObject(rule.options) ? rule.options : {};
	  const useGlobalOptions = (_ref = (_sliderOptions$useGlo = sliderOptions === null || sliderOptions === void 0 ? void 0 : sliderOptions.useGlobalOptions) !== null && _sliderOptions$useGlo !== void 0 ? _sliderOptions$useGlo : ruleOptions.useGlobalOptions) !== null && _ref !== void 0 ? _ref : true;
	  const useRuleOptions = sliderOptions !== ruleOptions && useGlobalOptions;
	  const options = {
	    ...(useRuleOptions ? ruleOptions : {}),
	    ...sliderOptions,
	    events: [useRuleOptions && ruleOptions.events, sliderOptions === null || sliderOptions === void 0 ? void 0 : sliderOptions.events]
	  };
	  const defaultOptions = SliderManager.getSliderDefaultOptions();
	  const priorityOptions = SliderManager.getSliderPriorityOptions();
	  const SliderClass = SliderManager.getSliderClass();
	  const slider = new SliderClass(url, main_core.Runtime.merge(defaultOptions, options, priorityOptions));
	  let offset = null;
	  if (slider.shouldUseAutoOffset() && slider.getWidth() === null && slider.getCustomLeftBoundary() === null) {
	    offset = 0;
	    const lastOffset = _classPrivateMethodGet$2(this, _getLastOffset, _getLastOffset2).call(this);
	    if (topSlider && lastOffset !== null) {
	      offset = Math.min(lastOffset + _classPrivateMethodGet$2(this, _getMinOffset, _getMinOffset2).call(this), _classPrivateMethodGet$2(this, _getMaxOffset, _getMaxOffset2).call(this));
	    }
	  }
	  slider.setOffset(offset);
	  if (topSlider && topSlider.getCustomRightBoundary() !== null) {
	    const rightBoundary = slider.calculateRightBoundary();
	    if (rightBoundary > topSlider.getCustomRightBoundary()) {
	      slider.setCustomRightBoundary(topSlider.getCustomRightBoundary());
	    }
	  }
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onOpenStart', this.handleSliderOpenStart, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onBeforeOpenComplete', this.handleSliderOpenComplete, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onMaximizeStart', this.handleSliderMaximizeStart, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onCloseStart', this.handleSliderCloseStart, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onBeforeCloseComplete', this.handleSliderCloseComplete, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onMinimizeStart', this.handleSliderMinimizeStart, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onLoad', this.handleSliderLoad, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onDestroy', this.handleSliderDestroy, {
	    compatMode: true
	  });
	  main_core_events.EventEmitter.subscribe(slider, 'SidePanel.Slider:onEscapePress', this.handleEscapePress, {
	    compatMode: true
	  });
	  return slider;
	}
	function _addOpenSlider2(slider) {
	  if (!(slider instanceof Slider)) {
	    throw new TypeError('Slider is not an instance of BX.SidePanel.Slider');
	  }
	  this.openSliders.push(slider);
	}
	function _removeOpenSlider2(slider) {
	  const openSliders = [...this.getOpenSliders()];
	  for (const [i, openSlider] of openSliders.entries()) {
	    if (openSlider === slider) {
	      this.openSliders.splice(i, 1);
	      return true;
	    }
	  }
	  return false;
	}
	function _setLastOpenSlider2(slider) {
	  if (this.lastOpenSlider !== slider) {
	    if (this.lastOpenSlider) {
	      this.lastOpenSlider.destroy();
	    }
	    this.lastOpenSlider = slider;
	  }
	}
	function _resetLastOpenSlider2() {
	  if (this.lastOpenSlider && this.getTopSlider() !== this.lastOpenSlider) {
	    this.lastOpenSlider.destroy();
	  }
	  this.lastOpenSlider = null;
	}
	function _getLastOffset2() {
	  const openSliders = this.getOpenSliders();
	  for (let i = openSliders.length - 1; i >= 0; i--) {
	    const slider = openSliders[i];
	    if (slider.getOffset() !== null) {
	      return slider.getOffset();
	    }
	  }
	  return null;
	}
	function _getMinOffset2() {
	  return 63;
	}
	function _getMaxOffset2() {
	  return _classPrivateMethodGet$2(this, _getMinOffset, _getMinOffset2).call(this) * 3;
	}
	function _getItemOrigin2(slider, item) {
	  if (item && item.getContainer().offsetWidth > 0) {
	    const rect = item.getContainer().getBoundingClientRect();
	    const offset = slider.getContainer().getBoundingClientRect().left;
	    const left = rect.left - offset + rect.width / 2;
	    return `${left}px ${rect.top}px`;
	  }
	  return '50% 100%';
	}

	const SidePanel = {};
	Object.defineProperty(SidePanel, 'Instance', {
	  enumerable: false,
	  get: getInstance
	});
	const namespace = main_core.Reflection.namespace('BX.SidePanel');
	Object.defineProperty(namespace, 'Instance', {
	  enumerable: false,
	  get: getInstance
	});

	exports.SidePanel = SidePanel;
	exports.Slider = Slider;
	exports.SliderManager = SliderManager;
	exports.Manager = SliderManager;
	exports.SliderEvent = SliderEvent;
	exports.Event = SliderEvent;
	exports.MessageEvent = MessageEvent;
	exports.Toolbar = Toolbar;
	exports.ToolbarItem = ToolbarItem;
	exports.Label = Label;
	exports.Dictionary = Dictionary;

}((this.BX.SidePanel = this.BX.SidePanel || {}),BX.Cache,BX,BX,BX.Event,BX.Main,BX));
//# sourceMappingURL=side-panel.bundle.js.map
