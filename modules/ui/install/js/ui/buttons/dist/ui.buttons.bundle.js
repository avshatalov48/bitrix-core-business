/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events,main_popup,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonTag = function ButtonTag() {
	  babelHelpers.classCallCheck(this, ButtonTag);
	};
	babelHelpers.defineProperty(ButtonTag, "BUTTON", 0);
	babelHelpers.defineProperty(ButtonTag, "LINK", 1);
	babelHelpers.defineProperty(ButtonTag, "SUBMIT", 2);
	babelHelpers.defineProperty(ButtonTag, "INPUT", 3);
	babelHelpers.defineProperty(ButtonTag, "DIV", 4);
	babelHelpers.defineProperty(ButtonTag, "SPAN", 5);

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	let BaseButton = /*#__PURE__*/function () {
	  function BaseButton(options) {
	    babelHelpers.classCallCheck(this, BaseButton);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.options = Object.assign(this.getDefaultOptions(), options);

	    /**
	     * 'buttonNode', 'textNode' and counterNode options use only in ButtonManager.createFromNode
	     */
	    this.button = main_core.Type.isDomNode(this.options.buttonNode) ? this.options.buttonNode : null;
	    this.textNode = main_core.Type.isDomNode(this.options.textNode) ? this.options.textNode : null;
	    this.counterNode = main_core.Type.isDomNode(this.options.counterNode) ? this.options.counterNode : null;
	    this.text = '';
	    this.counter = null;
	    this.events = {};
	    this.link = '';
	    this.maxWidth = null;
	    this.tag = this.isEnumValue(this.options.tag, ButtonTag) ? this.options.tag : ButtonTag.BUTTON;
	    if (main_core.Type.isStringFilled(this.options.link)) {
	      this.tag = ButtonTag.LINK;
	    }
	    this.baseClass = main_core.Type.isStringFilled(this.options.baseClass) ? this.options.baseClass : '';
	    this.disabled = false;
	    this.handleEvent = this.handleEvent.bind(this);
	    this.init(); // needs to initialize private properties in derived classes.

	    if (this.options.disabled === true) {
	      this.setDisabled();
	    }
	    this.setText(this.options.text);
	    this.setCounter(this.options.counter);
	    this.setProps(this.options.props);
	    this.setDataSet(this.options.dataset);
	    this.addClass(this.options.className);
	    this.setLink(this.options.link);
	    this.setMaxWidth(this.options.maxWidth);
	    this.bindEvent('click', this.options.onclick);
	    this.bindEvents(this.options.events);
	  }

	  /**
	   * @protected
	   */
	  babelHelpers.createClass(BaseButton, [{
	    key: "init",
	    value: function init() {
	      // needs to initialize private properties in derived classes.
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {};
	    }
	    /**
	     * @public
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getContainer();
	    }
	    /**
	     * @public
	     * @param {HTMLElement} node
	     * @return {?HTMLElement}
	     */
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node.appendChild(this.getContainer());
	      }
	      return null;
	    }
	    /**
	     * @public
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.button !== null) {
	        return this.button;
	      }
	      switch (this.getTag()) {
	        case ButtonTag.BUTTON:
	        default:
	          this.button = main_core.Tag.render(_t || (_t = _`<button class="${0}"></button>`), this.getBaseClass());
	          break;
	        case ButtonTag.INPUT:
	          this.button = main_core.Tag.render(_t2 || (_t2 = _`<input class="${0}" type="button">`), this.getBaseClass());
	          break;
	        case ButtonTag.LINK:
	          this.button = main_core.Tag.render(_t3 || (_t3 = _`<a class="${0}" href=""></a>`), this.getBaseClass());
	          break;
	        case ButtonTag.SUBMIT:
	          this.button = main_core.Tag.render(_t4 || (_t4 = _`<input class="${0}" type="submit">`), this.getBaseClass());
	          break;
	        case ButtonTag.DIV:
	          this.button = main_core.Tag.render(_t5 || (_t5 = _`<div class="${0}"></div>`), this.getBaseClass());
	          break;
	        case ButtonTag.SPAN:
	          this.button = main_core.Tag.render(_t6 || (_t6 = _`<span class="${0}"></span>`), this.getBaseClass());
	          break;
	      }
	      return this.button;
	    }
	    /**
	     * @protected
	     * @return {string}
	     */
	  }, {
	    key: "getBaseClass",
	    value: function getBaseClass() {
	      return this.baseClass;
	    }
	    /**
	     * @public
	     * @param {string} text
	     * @return {this}
	     */
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      if (main_core.Type.isString(text)) {
	        this.text = text;
	        if (this.isInputType()) {
	          this.getContainer().value = text;
	        } else if (text.length > 0) {
	          if (this.textNode === null) {
	            this.textNode = main_core.Tag.render(_t7 || (_t7 = _`<span class="ui-btn-text"></span>`));
	          }
	          if (!this.textNode.parentNode) {
	            main_core.Dom.prepend(this.textNode, this.getContainer());
	          }
	          this.textNode.textContent = text;
	        } else {
	          if (this.textNode !== null) {
	            main_core.Dom.remove(this.textNode);
	          }
	        }
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {string}
	     */
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.text;
	    }
	    /**
	     *
	     * @param {number | string} counter
	     * @return {this}
	     */
	  }, {
	    key: "setCounter",
	    value: function setCounter(counter) {
	      if ([0, '0', '', null, false].includes(counter)) {
	        if (this.counterNode !== null) {
	          main_core.Dom.remove(this.counterNode);
	          this.counterNode = null;
	        }
	        this.counter = null;
	      } else if (main_core.Type.isNumber(counter) && counter > 0 || main_core.Type.isStringFilled(counter)) {
	        if (this.isInputType()) {
	          throw new Error('BX.UI.Button: an input button cannot have a counter.');
	        }
	        if (this.counterNode === null) {
	          this.counterNode = main_core.Tag.render(_t8 || (_t8 = _`<span class="ui-btn-counter"></span>`));
	          main_core.Dom.append(this.counterNode, this.getContainer());
	        }
	        this.counter = counter;
	        this.counterNode.textContent = counter;
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {number | string | null}
	     */
	  }, {
	    key: "getCounter",
	    value: function getCounter() {
	      return this.counter;
	    }
	    /**
	     *
	     * @param {string} link
	     * @return {this}
	     */
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      if (main_core.Type.isString(link)) {
	        if (this.getTag() !== ButtonTag.LINK) {
	          throw new Error('BX.UI.Button: only an anchor button tag supports a link.');
	        }
	        this.getContainer().href = link;
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {string}
	     */
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.getContainer().href;
	    }
	  }, {
	    key: "setMaxWidth",
	    value: function setMaxWidth(maxWidth) {
	      if (main_core.Type.isNumber(maxWidth) && maxWidth > 0) {
	        this.maxWidth = maxWidth;
	        this.getContainer().style.maxWidth = `${maxWidth}px`;
	      } else if (maxWidth === null) {
	        this.getContainer().style.removeProperty('max-width');
	        this.maxWidth = null;
	      }
	      return this;
	    }
	  }, {
	    key: "getMaxWidth",
	    value: function getMaxWidth() {
	      return this.maxWidth;
	    }
	    /**
	     * @public
	     * @return {ButtonTag}
	     */
	  }, {
	    key: "getTag",
	    value: function getTag() {
	      return this.tag;
	    }
	    /**
	     * @public
	     * @param {object.<string, string>} props
	     * @return {this}
	     */
	  }, {
	    key: "setProps",
	    value: function setProps(props) {
	      if (!main_core.Type.isPlainObject(props)) {
	        return this;
	      }
	      for (let propName in props) {
	        const propValue = props[propName];
	        main_core.Dom.attr(this.getContainer(), propName, propValue);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {object.<string, string>}
	     */
	  }, {
	    key: "getProps",
	    value: function getProps() {
	      const attrs = this.getContainer().attributes;
	      const result = {};
	      const reserved = this.isInputType() ? ['class', 'type'] : ['class'];
	      for (let i = 0; i < attrs.length; i++) {
	        const {
	          name,
	          value
	        } = attrs[i];
	        if (reserved.includes(name) || name.startsWith('data-')) {
	          continue;
	        }
	        result[name] = value;
	      }
	      return result;
	    }
	    /**
	     * @public
	     * @param {object.<string, string>} props
	     * @return {this}
	     */
	  }, {
	    key: "setDataSet",
	    value: function setDataSet(props) {
	      if (!main_core.Type.isPlainObject(props)) {
	        return this;
	      }
	      for (let propName in props) {
	        const propValue = props[propName];
	        if (propValue === null) {
	          delete this.getDataSet()[propName];
	        } else {
	          this.getDataSet()[propName] = propValue;
	        }
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {DOMStringMap}
	     */
	  }, {
	    key: "getDataSet",
	    value: function getDataSet() {
	      return this.getContainer().dataset;
	    }
	    /**
	     * @public
	     * @param {string} className
	     * @return {this}
	     */
	  }, {
	    key: "addClass",
	    value: function addClass(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        main_core.Dom.addClass(this.getContainer(), className);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {string} className
	     * @return {this}
	     */
	  }, {
	    key: "removeClass",
	    value: function removeClass(className) {
	      if (main_core.Type.isStringFilled(className)) {
	        main_core.Dom.removeClass(this.getContainer(), className);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDisabled",
	    value: function setDisabled(flag) {
	      if (flag === false) {
	        this.disabled = false;
	        this.setProps({
	          disabled: null
	        });
	      } else {
	        this.disabled = true;
	        this.setProps({
	          disabled: true
	        });
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {boolean}
	     */
	  }, {
	    key: "isDisabled",
	    value: function isDisabled() {
	      return this.disabled;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isInputType",
	    value: function isInputType() {
	      return this.getTag() === ButtonTag.SUBMIT || this.getTag() === ButtonTag.INPUT;
	    }
	    /**
	     * @public
	     * @param {object.<string, function>} events
	     * @return {this}
	     */
	  }, {
	    key: "bindEvents",
	    value: function bindEvents(events) {
	      if (main_core.Type.isPlainObject(events)) {
	        for (let eventName in events) {
	          const fn = events[eventName];
	          this.bindEvent(eventName, fn);
	        }
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {string[]} events
	     * @return {this}
	     */
	  }, {
	    key: "unbindEvents",
	    value: function unbindEvents(events) {
	      if (main_core.Type.isArray(events)) {
	        events.forEach(eventName => {
	          this.unbindEvent(eventName);
	        });
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {string} eventName
	     * @param {function} fn
	     * @return {this}
	     */
	  }, {
	    key: "bindEvent",
	    value: function bindEvent(eventName, fn) {
	      if (main_core.Type.isStringFilled(eventName) && main_core.Type.isFunction(fn)) {
	        this.unbindEvent(eventName);
	        this.events[eventName] = fn;
	        main_core.Event.bind(this.getContainer(), eventName, this.handleEvent);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {string} eventName
	     * @return {this}
	     */
	  }, {
	    key: "unbindEvent",
	    value: function unbindEvent(eventName) {
	      if (this.events[eventName]) {
	        delete this.events[eventName];
	        main_core.Event.unbind(this.getContainer(), eventName, this.handleEvent);
	      }
	      return this;
	    }
	    /**
	     * @private
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handleEvent",
	    value: function handleEvent(event) {
	      const eventName = event.type;
	      if (this.events[eventName]) {
	        const fn = this.events[eventName];
	        fn.call(this, this, event);
	      }
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "isEnumValue",
	    value: function isEnumValue(value, enumeration) {
	      for (let code in enumeration) {
	        if (enumeration[code] === value) {
	          return true;
	        }
	      }
	      return false;
	    }
	  }]);
	  return BaseButton;
	}();

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonColor = function ButtonColor() {
	  babelHelpers.classCallCheck(this, ButtonColor);
	};
	babelHelpers.defineProperty(ButtonColor, "DANGER", 'ui-btn-danger');
	babelHelpers.defineProperty(ButtonColor, "DANGER_DARK", 'ui-btn-danger-dark');
	babelHelpers.defineProperty(ButtonColor, "DANGER_LIGHT", 'ui-btn-danger-light');
	babelHelpers.defineProperty(ButtonColor, "SUCCESS", 'ui-btn-success');
	babelHelpers.defineProperty(ButtonColor, "SUCCESS_DARK", 'ui-btn-success-dark');
	babelHelpers.defineProperty(ButtonColor, "SUCCESS_LIGHT", 'ui-btn-success-light');
	babelHelpers.defineProperty(ButtonColor, "PRIMARY_DARK", 'ui-btn-primary-dark');
	babelHelpers.defineProperty(ButtonColor, "PRIMARY", 'ui-btn-primary');
	babelHelpers.defineProperty(ButtonColor, "SECONDARY", 'ui-btn-secondary');
	babelHelpers.defineProperty(ButtonColor, "SECONDARY_LIGHT", 'ui-btn-secondary-light');
	babelHelpers.defineProperty(ButtonColor, "WARNING_LIGHT", 'ui-btn-warning-light');
	babelHelpers.defineProperty(ButtonColor, "LINK", 'ui-btn-link');
	babelHelpers.defineProperty(ButtonColor, "LIGHT", 'ui-btn-light');
	babelHelpers.defineProperty(ButtonColor, "LIGHT_BORDER", 'ui-btn-light-border');
	babelHelpers.defineProperty(ButtonColor, "AI", 'ui-btn-color-ai');
	babelHelpers.defineProperty(ButtonColor, "BASE_LIGHT", 'ui-btn-base-light');

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonSize = function ButtonSize() {
	  babelHelpers.classCallCheck(this, ButtonSize);
	};
	babelHelpers.defineProperty(ButtonSize, "LARGE", 'ui-btn-lg');
	babelHelpers.defineProperty(ButtonSize, "MEDIUM", 'ui-btn-md');
	babelHelpers.defineProperty(ButtonSize, "SMALL", 'ui-btn-sm');
	babelHelpers.defineProperty(ButtonSize, "EXTRA_SMALL", 'ui-btn-xs');

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonIcon = function ButtonIcon() {
	  babelHelpers.classCallCheck(this, ButtonIcon);
	};
	babelHelpers.defineProperty(ButtonIcon, "UNFOLLOW", 'ui-btn-icon-unfollow');
	babelHelpers.defineProperty(ButtonIcon, "FOLLOW", 'ui-btn-icon-follow');
	babelHelpers.defineProperty(ButtonIcon, "ADD", 'ui-btn-icon-add');
	babelHelpers.defineProperty(ButtonIcon, "STOP", 'ui-btn-icon-stop');
	babelHelpers.defineProperty(ButtonIcon, "START", 'ui-btn-icon-start');
	babelHelpers.defineProperty(ButtonIcon, "PAUSE", 'ui-btn-icon-pause');
	babelHelpers.defineProperty(ButtonIcon, "ADD_FOLDER", 'ui-btn-icon-add-folder');
	babelHelpers.defineProperty(ButtonIcon, "SETTING", 'ui-btn-icon-setting');
	babelHelpers.defineProperty(ButtonIcon, "TASK", 'ui-btn-icon-task');
	babelHelpers.defineProperty(ButtonIcon, "INFO", 'ui-btn-icon-info');
	babelHelpers.defineProperty(ButtonIcon, "SEARCH", 'ui-btn-icon-search');
	babelHelpers.defineProperty(ButtonIcon, "PRINT", 'ui-btn-icon-print');
	babelHelpers.defineProperty(ButtonIcon, "LIST", 'ui-btn-icon-list');
	babelHelpers.defineProperty(ButtonIcon, "BUSINESS", 'ui-btn-icon-business');
	babelHelpers.defineProperty(ButtonIcon, "BUSINESS_CONFIRM", 'ui-btn-icon-business-confirm');
	babelHelpers.defineProperty(ButtonIcon, "BUSINESS_WARNING", 'ui-btn-icon-business-warning');
	babelHelpers.defineProperty(ButtonIcon, "CAMERA", 'ui-btn-icon-camera');
	babelHelpers.defineProperty(ButtonIcon, "PHONE_UP", 'ui-btn-icon-phone-up');
	babelHelpers.defineProperty(ButtonIcon, "PHONE_DOWN", 'ui-btn-icon-phone-down');
	babelHelpers.defineProperty(ButtonIcon, "PHONE_CALL", 'ui-btn-icon-phone-call');
	babelHelpers.defineProperty(ButtonIcon, "BACK", 'ui-btn-icon-back');
	babelHelpers.defineProperty(ButtonIcon, "REMOVE", 'ui-btn-icon-remove');
	babelHelpers.defineProperty(ButtonIcon, "DOWNLOAD", 'ui-btn-icon-download');
	babelHelpers.defineProperty(ButtonIcon, "DOTS", 'ui-btn-icon-ui-btn-icon-dots');
	babelHelpers.defineProperty(ButtonIcon, "DONE", 'ui-btn-icon-done');
	babelHelpers.defineProperty(ButtonIcon, "CANCEL", 'ui-btn-icon-cancel');
	babelHelpers.defineProperty(ButtonIcon, "DISK", 'ui-btn-icon-disk');
	babelHelpers.defineProperty(ButtonIcon, "LOCK", 'ui-btn-icon-lock');
	babelHelpers.defineProperty(ButtonIcon, "MAIL", 'ui-btn-icon-mail');
	babelHelpers.defineProperty(ButtonIcon, "CHAT", 'ui-btn-icon-chat');
	babelHelpers.defineProperty(ButtonIcon, "PAGE", 'ui-btn-icon-page');
	babelHelpers.defineProperty(ButtonIcon, "CLOUD", 'ui-btn-icon-cloud');
	babelHelpers.defineProperty(ButtonIcon, "EDIT", 'ui-btn-icon-edit');
	babelHelpers.defineProperty(ButtonIcon, "SHARE", 'ui-btn-icon-share');
	babelHelpers.defineProperty(ButtonIcon, "ANGLE_UP", 'ui-btn-icon-angle-up');
	babelHelpers.defineProperty(ButtonIcon, "ANGLE_DOWN", 'ui-btn-icon-angle-down');
	babelHelpers.defineProperty(ButtonIcon, "EYE_OPENED", 'ui-btn-icon-eye-opened');
	babelHelpers.defineProperty(ButtonIcon, "EYE_CLOSED", 'ui-btn-icon-eye-closed');
	babelHelpers.defineProperty(ButtonIcon, "ALERT", 'ui-btn-icon-alert');
	babelHelpers.defineProperty(ButtonIcon, "FAIL", 'ui-btn-icon-fail');
	babelHelpers.defineProperty(ButtonIcon, "SUCCESS", 'ui-btn-icon-success');
	babelHelpers.defineProperty(ButtonIcon, "PLAN", 'ui-btn-icon-plan');
	babelHelpers.defineProperty(ButtonIcon, "TARIFF", 'ui-btn-icon-tariff');
	babelHelpers.defineProperty(ButtonIcon, "BATTERY", 'ui-btn-icon-battery');
	babelHelpers.defineProperty(ButtonIcon, "NO_BATTERY", 'ui-btn-icon-no-battery');
	babelHelpers.defineProperty(ButtonIcon, "HALF_BATTERY", 'ui-btn-icon-half-battery');
	babelHelpers.defineProperty(ButtonIcon, "LOW_BATTERY", 'ui-btn-icon-low-battery');
	babelHelpers.defineProperty(ButtonIcon, "CRIT_BATTERY", 'ui-btn-icon-crit-battery');
	babelHelpers.defineProperty(ButtonIcon, "DEMO", 'ui-btn-icon-demo');
	babelHelpers.defineProperty(ButtonIcon, "ROBOTS", 'ui-btn-icon-robots');
	babelHelpers.defineProperty(ButtonIcon, "NOTE", 'ui-btn-icon-note');
	babelHelpers.defineProperty(ButtonIcon, "SCRIPT", 'ui-btn-icon-script');
	babelHelpers.defineProperty(ButtonIcon, "PRINT2", 'ui-btn-icon-print-2');
	babelHelpers.defineProperty(ButtonIcon, "FUNNEL", 'ui-btn-icon-funnel');
	babelHelpers.defineProperty(ButtonIcon, "FORWARD", 'ui-btn-icon-forward');
	babelHelpers.defineProperty(ButtonIcon, "COPY", 'ui-btn-icon-copy');
	babelHelpers.defineProperty(ButtonIcon, "AI", 'ui-btn-icon-ai ui-icon-set__scope');

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonState = function ButtonState() {
	  babelHelpers.classCallCheck(this, ButtonState);
	};
	babelHelpers.defineProperty(ButtonState, "HOVER", 'ui-btn-hover');
	babelHelpers.defineProperty(ButtonState, "ACTIVE", 'ui-btn-active');
	babelHelpers.defineProperty(ButtonState, "DISABLED", 'ui-btn-disabled');
	babelHelpers.defineProperty(ButtonState, "CLOCKING", 'ui-btn-clock');
	babelHelpers.defineProperty(ButtonState, "WAITING", 'ui-btn-wait');
	babelHelpers.defineProperty(ButtonState, "AI_WAITING", 'ui-btn-ai-waiting');

	/**
	 * @namespace {BX.UI}
	 */
	let ButtonStyle = function ButtonStyle() {
	  babelHelpers.classCallCheck(this, ButtonStyle);
	};
	babelHelpers.defineProperty(ButtonStyle, "NO_CAPS", 'ui-btn-no-caps');
	babelHelpers.defineProperty(ButtonStyle, "ROUND", 'ui-btn-round');
	babelHelpers.defineProperty(ButtonStyle, "DROPDOWN", 'ui-btn-dropdown');
	babelHelpers.defineProperty(ButtonStyle, "COLLAPSED", 'ui-btn-collapsed');
	babelHelpers.defineProperty(ButtonStyle, "DEPEND_ON_THEME", 'ui-btn-themes');

	/**
	 * @namespace {BX.UI}
	 */
	let Button = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(Button, _BaseButton);
	  function Button(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, Button);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    options.baseClass = main_core.Type.isStringFilled(options.baseClass) ? options.baseClass : Button.BASE_CLASS;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Button).call(this, options));
	    _this.isDependOnTheme = null;
	    _this.size = null;
	    _this.color = null;
	    _this.icon = null;
	    _this.state = null;
	    _this.id = null;
	    _this.context = null;
	    _this.menuWindow = null;
	    _this.handleMenuClick = _this.handleMenuClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.handleMenuClose = _this.handleMenuClose.bind(babelHelpers.assertThisInitialized(_this));
	    _this.setDependOnTheme(_this.options.dependOnTheme);
	    _this.setSize(_this.options.size);
	    _this.setColor(_this.options.color);
	    _this.setIcon(_this.options.icon);
	    _this.setState(_this.options.state);
	    _this.setId(_this.options.id);
	    _this.setMenu(_this.options.menu);
	    _this.setContext(_this.options.context);
	    _this.options.noCaps && _this.setNoCaps();
	    _this.options.round && _this.setRound();
	    if (_this.options.dropdown || _this.getMenuWindow() && _this.options.dropdown !== false) {
	      _this.setDropdown();
	    }
	    return _this;
	  }
	  babelHelpers.createClass(Button, [{
	    key: "setSize",
	    /**
	     * @public
	     * @param {ButtonSize|null} size
	     * @return {this}
	     */
	    value: function setSize(size) {
	      return this.setProperty('size', size, ButtonSize);
	    }
	    /**
	     * @public
	     * @return {?ButtonSize}
	     */
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.size;
	    }
	    /**
	     * @public
	     * @param {ButtonColor|null} color
	     * @return {this}
	     */
	  }, {
	    key: "setColor",
	    value: function setColor(color) {
	      return this.setProperty('color', color, ButtonColor);
	    }
	    /**
	     * @public
	     * @return {?ButtonSize}
	     */
	  }, {
	    key: "getColor",
	    value: function getColor() {
	      return this.color;
	    }
	    /**
	     * @public
	     * @param {?ButtonIcon} icon
	     * @return {this}
	     */
	  }, {
	    key: "setIcon",
	    value: function setIcon(icon) {
	      this.setProperty('icon', icon, ButtonIcon);
	      if (this.isInputType() && this.getIcon() !== null) {
	        throw new Error('BX.UI.Button: Input type button cannot have an icon.');
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {?ButtonIcon}
	     */
	  }, {
	    key: "getIcon",
	    value: function getIcon() {
	      return this.icon;
	    }
	    /**
	     * @public
	     * @param {ButtonState|null} state
	     * @return {this}
	     */
	  }, {
	    key: "setState",
	    value: function setState(state) {
	      return this.setProperty('state', state, ButtonState);
	    }
	    /**
	     * @public
	     * @return {?ButtonState}
	     */
	  }, {
	    key: "getState",
	    value: function getState() {
	      return this.state;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setNoCaps",
	    value: function setNoCaps(flag) {
	      if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.NO_CAPS);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.NO_CAPS);
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {boolean}
	     */
	  }, {
	    key: "isNoCaps",
	    value: function isNoCaps() {
	      return main_core.Dom.hasClass(this.getContainer(), ButtonStyle.NO_CAPS);
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setRound",
	    value: function setRound(flag) {
	      if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.ROUND);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.ROUND);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isRound",
	    value: function isRound() {
	      return main_core.Dom.hasClass(this.getContainer(), ButtonStyle.ROUND);
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDependOnTheme",
	    value: function setDependOnTheme(flag) {
	      if (flag === true) {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.DEPEND_ON_THEME);
	      } else if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.DEPEND_ON_THEME);
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {boolean}
	     */
	  }, {
	    key: "isDependOnTheme",
	    value: function isDependOnTheme() {
	      if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.DEPEND_ON_THEME);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.DEPEND_ON_THEME);
	      }
	      return this;
	    }
	    /**
	     *
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDropdown",
	    value: function setDropdown(flag) {
	      if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.DROPDOWN);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.DROPDOWN);
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {boolean}
	     */
	  }, {
	    key: "isDropdown",
	    value: function isDropdown() {
	      return main_core.Dom.hasClass(this.getContainer(), ButtonStyle.DROPDOWN);
	    }
	    /**
	     *
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setCollapsed",
	    value: function setCollapsed(flag) {
	      if (flag === false) {
	        main_core.Dom.removeClass(this.getContainer(), ButtonStyle.COLLAPSED);
	      } else {
	        main_core.Dom.addClass(this.getContainer(), ButtonStyle.COLLAPSED);
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {boolean}
	     */
	  }, {
	    key: "isCollapsed",
	    value: function isCollapsed() {
	      return main_core.Dom.hasClass(this.getContainer(), ButtonStyle.COLLAPSED);
	    }
	    /**
	     * @protected
	     * @param {MenuOptions|false} options
	     */
	  }, {
	    key: "setMenu",
	    value: function setMenu(options) {
	      if (main_core.Type.isPlainObject(options) && main_core.Type.isArray(options.items) && options.items.length > 0) {
	        this.setMenu(false);
	        this.menuWindow = new main_popup.Menu({
	          id: `ui-btn-menu-${main_core.Text.getRandom().toLowerCase()}`,
	          bindElement: this.getMenuBindElement(),
	          ...options
	        });
	        this.menuWindow.getPopupWindow().subscribe('onClose', this.handleMenuClose);
	        main_core.Event.bind(this.getMenuClickElement(), 'click', this.handleMenuClick);
	      } else if (options === false && this.menuWindow !== null) {
	        this.menuWindow.close();
	        this.menuWindow.getPopupWindow().unsubscribe('onClose', this.handleMenuClose);
	        main_core.Event.unbind(this.getMenuClickElement(), 'click', this.handleMenuClick);
	        this.menuWindow.destroy();
	        this.menuWindow = null;
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "getMenuBindElement",
	    value: function getMenuBindElement() {
	      return this.getContainer();
	    }
	    /**
	     * @public
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "getMenuClickElement",
	    value: function getMenuClickElement() {
	      return this.getContainer();
	    }
	    /**
	     * @protected
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handleMenuClick",
	    value: function handleMenuClick(event) {
	      this.getMenuWindow().show();
	      this.setActive(this.getMenuWindow().getPopupWindow().isShown());
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "handleMenuClose",
	    value: function handleMenuClose() {
	      this.setActive(false);
	    }
	    /**
	     * @public
	     * @return {Menu}
	     */
	  }, {
	    key: "getMenuWindow",
	    value: function getMenuWindow() {
	      return this.menuWindow;
	    }
	    /**
	     * @public
	     * @param {string|null} id
	     * @return {this}
	     */
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      if (main_core.Type.isStringFilled(id) || main_core.Type.isNull(id)) {
	        this.id = id;
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {?string}
	     */
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setActive",
	    value: function setActive(flag) {
	      return this.setState(flag === false ? null : ButtonState.ACTIVE);
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.getState() === ButtonState.ACTIVE;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setHovered",
	    value: function setHovered(flag) {
	      return this.setState(flag === false ? null : ButtonState.HOVER);
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isHover",
	    value: function isHover() {
	      return this.getState() === ButtonState.HOVER;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDisabled",
	    value: function setDisabled(flag) {
	      this.setState(flag === false ? null : ButtonState.DISABLED);
	      babelHelpers.get(babelHelpers.getPrototypeOf(Button.prototype), "setDisabled", this).call(this, flag);
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isDisabled",
	    value: function isDisabled() {
	      return this.getState() === ButtonState.DISABLED;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setWaiting",
	    value: function setWaiting(flag) {
	      if (flag === false) {
	        this.setState(null);
	        this.setProps({
	          disabled: null
	        });
	      } else {
	        this.setState(ButtonState.WAITING);
	        this.setProps({
	          disabled: true
	        });
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isWaiting",
	    value: function isWaiting() {
	      return this.getState() === ButtonState.WAITING;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setClocking",
	    value: function setClocking(flag) {
	      if (flag === false) {
	        this.setState(null);
	        this.setProps({
	          disabled: null
	        });
	      } else {
	        this.setState(ButtonState.CLOCKING);
	        this.setProps({
	          disabled: true
	        });
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isClocking",
	    value: function isClocking() {
	      return this.getState() === ButtonState.CLOCKING;
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "setProperty",
	    value: function setProperty(property, value, enumeration) {
	      if (this.isEnumValue(value, enumeration)) {
	        main_core.Dom.removeClass(this.getContainer(), this[property]);
	        main_core.Dom.addClass(this.getContainer(), value);
	        this[property] = value;
	      } else if (value === null) {
	        main_core.Dom.removeClass(this.getContainer(), this[property]);
	        this[property] = null;
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @param {*} context
	     */
	  }, {
	    key: "setContext",
	    value: function setContext(context) {
	      if (!main_core.Type.isUndefined(context)) {
	        this.context = context;
	      }
	      return this;
	    }
	    /**
	     *
	     * @return {*}
	     */
	  }, {
	    key: "getContext",
	    value: function getContext() {
	      return this.context;
	    }
	  }]);
	  return Button;
	}(BaseButton);
	babelHelpers.defineProperty(Button, "BASE_CLASS", 'ui-btn');
	babelHelpers.defineProperty(Button, "Size", ButtonSize);
	babelHelpers.defineProperty(Button, "Color", ButtonColor);
	babelHelpers.defineProperty(Button, "State", ButtonState);
	babelHelpers.defineProperty(Button, "Icon", ButtonIcon);
	babelHelpers.defineProperty(Button, "Tag", ButtonTag);
	babelHelpers.defineProperty(Button, "Style", ButtonStyle);

	/**
	 * @namespace {BX.UI}
	 */
	let SplitButtonState = function SplitButtonState() {
	  babelHelpers.classCallCheck(this, SplitButtonState);
	};
	babelHelpers.defineProperty(SplitButtonState, "HOVER", 'ui-btn-hover');
	babelHelpers.defineProperty(SplitButtonState, "MAIN_HOVER", 'ui-btn-main-hover');
	babelHelpers.defineProperty(SplitButtonState, "MENU_HOVER", 'ui-btn-menu-hover');
	babelHelpers.defineProperty(SplitButtonState, "ACTIVE", 'ui-btn-active');
	babelHelpers.defineProperty(SplitButtonState, "MAIN_ACTIVE", 'ui-btn-main-active');
	babelHelpers.defineProperty(SplitButtonState, "MENU_ACTIVE", 'ui-btn-menu-active');
	babelHelpers.defineProperty(SplitButtonState, "DISABLED", 'ui-btn-disabled');
	babelHelpers.defineProperty(SplitButtonState, "MAIN_DISABLED", 'ui-btn-main-disabled');
	babelHelpers.defineProperty(SplitButtonState, "MENU_DISABLED", 'ui-btn-menu-disabled');
	babelHelpers.defineProperty(SplitButtonState, "CLOCKING", 'ui-btn-clock');
	babelHelpers.defineProperty(SplitButtonState, "WAITING", 'ui-btn-wait');

	/**
	 * @namespace {BX.UI}
	 */
	let SplitSubButtonType = function SplitSubButtonType() {
	  babelHelpers.classCallCheck(this, SplitSubButtonType);
	};
	babelHelpers.defineProperty(SplitSubButtonType, "MAIN", 'ui-btn-main');
	babelHelpers.defineProperty(SplitSubButtonType, "MENU", 'ui-btn-menu');

	/**
	 * @namespace {BX.UI}
	 */
	let SplitSubButton = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(SplitSubButton, _BaseButton);
	  function SplitSubButton(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, SplitSubButton);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    options.baseClass = options.buttonType === SplitSubButtonType.MAIN ? SplitSubButtonType.MAIN : SplitSubButtonType.MENU;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SplitSubButton).call(this, options));
	    if (_this.isInputType()) {
	      throw new Error('BX.UI.SplitSubButton: Split button cannot be an input tag.');
	    }
	    return _this;
	  }
	  babelHelpers.createClass(SplitSubButton, [{
	    key: "init",
	    value: function init() {
	      this.buttonType = this.options.buttonType;
	      this.splitButton = this.options.splitButton;
	      babelHelpers.get(babelHelpers.getPrototypeOf(SplitSubButton.prototype), "init", this).call(this);
	    }
	    /**
	     * @public
	     * @return {SplitButton}
	     */
	  }, {
	    key: "getSplitButton",
	    value: function getSplitButton() {
	      return this.splitButton;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isMainButton",
	    value: function isMainButton() {
	      return this.buttonType === SplitSubButtonType.MAIN;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isMenuButton",
	    value: function isMenuButton() {
	      return this.buttonType === SplitSubButtonType.MENU;
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      if (main_core.Type.isString(text) && this.isMenuButton()) {
	        throw new Error('BX.UI.SplitButton: a menu button doesn\'t support a text caption.');
	      }
	      return babelHelpers.get(babelHelpers.getPrototypeOf(SplitSubButton.prototype), "setText", this).call(this, text);
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setActive",
	    value: function setActive(flag) {
	      this.toggleState(flag, SplitButtonState.ACTIVE, SplitButtonState.MAIN_ACTIVE, SplitButtonState.MENU_ACTIVE);
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      const state = this.getSplitButton().getState();
	      if (state === SplitButtonState.ACTIVE) {
	        return true;
	      }
	      if (this.isMainButton()) {
	        return state === SplitButtonState.MAIN_ACTIVE;
	      }
	      return state === SplitButtonState.MENU_ACTIVE;
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDisabled",
	    value: function setDisabled(flag) {
	      this.toggleState(flag, SplitButtonState.DISABLED, SplitButtonState.MAIN_DISABLED, SplitButtonState.MENU_DISABLED);
	      babelHelpers.get(babelHelpers.getPrototypeOf(SplitSubButton.prototype), "setDisabled", this).call(this, flag);
	      return this;
	    }
	    /**
	     * @public
	     * @param {boolean} flag
	     * @return {this}
	     */
	  }, {
	    key: "setHovered",
	    value: function setHovered(flag) {
	      this.toggleState(flag, SplitButtonState.HOVER, SplitButtonState.MAIN_HOVER, SplitButtonState.MENU_HOVER);
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isHovered",
	    value: function isHovered() {
	      const state = this.getSplitButton().getState();
	      if (state === SplitButtonState.HOVER) {
	        return true;
	      }
	      if (this.isMainButton()) {
	        return state === SplitButtonState.MAIN_HOVER;
	      }
	      return state === SplitButtonState.MENU_HOVER;
	    }
	    /**
	     * @private
	     * @param flag
	     * @param globalState
	     * @param mainState
	     * @param menuState
	     */
	  }, {
	    key: "toggleState",
	    value: function toggleState(flag, globalState, mainState, menuState) {
	      const state = this.getSplitButton().getState();
	      if (flag === false) {
	        if (state === globalState) {
	          this.getSplitButton().setState(this.isMainButton() ? menuState : mainState);
	        } else {
	          this.getSplitButton().setState(null);
	        }
	      } else {
	        if (state === mainState && this.isMenuButton()) {
	          this.getSplitButton().setState(globalState);
	        } else if (state === menuState && this.isMainButton()) {
	          this.getSplitButton().setState(globalState);
	        } else if (state !== globalState) {
	          this.getSplitButton().setState(this.isMainButton() ? mainState : menuState);
	        }
	      }
	    }
	  }]);
	  return SplitSubButton;
	}(BaseButton);
	babelHelpers.defineProperty(SplitSubButton, "Type", SplitSubButtonType);

	let _$1 = t => t,
	  _t$1;

	/**
	 * @namespace {BX.UI}
	 */
	let SplitButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(SplitButton, _Button);
	  function SplitButton(options) {
	    babelHelpers.classCallCheck(this, SplitButton);
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    // delete options.round;

	    if (main_core.Type.isStringFilled(options.link)) {
	      options.mainButton = main_core.Type.isPlainObject(options.mainButton) ? options.mainButton : {};
	      options.mainButton.link = options.link;
	      delete options.link;
	    }
	    options.tag = ButtonTag.DIV;
	    options.baseClass = SplitButton.BASE_CLASS;
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SplitButton).call(this, options));
	  }
	  babelHelpers.createClass(SplitButton, [{
	    key: "init",
	    value: function init() {
	      const mainOptions = main_core.Type.isPlainObject(this.options.mainButton) ? this.options.mainButton : {};
	      const menuOptions = main_core.Type.isPlainObject(this.options.menuButton) ? this.options.menuButton : {};
	      mainOptions.buttonType = SplitSubButtonType.MAIN;
	      mainOptions.splitButton = this;
	      menuOptions.buttonType = SplitSubButtonType.MENU;
	      menuOptions.splitButton = this;
	      this.mainButton = new SplitSubButton(mainOptions);
	      this.menuButton = new SplitSubButton(menuOptions);
	      this.menuTarget = SplitSubButtonType.MAIN;
	      if (this.options.menuTarget === SplitSubButtonType.MENU) {
	        this.menuTarget = SplitSubButtonType.MENU;
	      }
	      babelHelpers.get(babelHelpers.getPrototypeOf(SplitButton.prototype), "init", this).call(this);
	    }
	  }, {
	    key: "getContainer",
	    /**
	     * @public
	     * @return {HTMLElement}
	     */
	    value: function getContainer() {
	      if (this.button === null) {
	        this.button = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="${0}">${0}</div>
			`), this.getBaseClass(), [this.getMainButton().getContainer(), this.getMenuButton().getContainer()]);
	      }
	      return this.button;
	    }
	    /**
	     * @public
	     * @return {SplitSubButton}
	     */
	  }, {
	    key: "getMainButton",
	    value: function getMainButton() {
	      return this.mainButton;
	    }
	    /**
	     * @public
	     * @return {SplitSubButton}
	     */
	  }, {
	    key: "getMenuButton",
	    value: function getMenuButton() {
	      return this.menuButton;
	    }
	    /**
	     * @public
	     * @param {string} text
	     * @return {this}
	     */
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      if (main_core.Type.isString(text)) {
	        this.getMainButton().setText(text);
	      }
	      return this;
	    }
	    /**
	     * @public
	     * @return {string}
	     */
	  }, {
	    key: "getText",
	    value: function getText() {
	      return this.getMainButton().getText();
	    }
	    /**
	     *
	     * @param {number | string} counter
	     * @return {this}
	     */
	  }, {
	    key: "setCounter",
	    value: function setCounter(counter) {
	      return this.getMainButton().setCounter(counter);
	    }
	    /**
	     *
	     * @return {number | string | null}
	     */
	  }, {
	    key: "getCounter",
	    value: function getCounter() {
	      return this.getMainButton().getCounter();
	    }
	    /**
	     *
	     * @param {string} link
	     * @return {this}
	     */
	  }, {
	    key: "setLink",
	    value: function setLink(link) {
	      return this.getMainButton().setLink(link);
	    }
	    /**
	     *
	     * @return {string}
	     */
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.getMainButton().getLink();
	    }
	    /**
	     * @public
	     * @param {SplitButtonState|null} state
	     * @return {this}
	     */
	  }, {
	    key: "setState",
	    value: function setState(state) {
	      return this.setProperty('state', state, SplitButtonState);
	    }
	    /**
	     * @public
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDisabled",
	    value: function setDisabled(flag) {
	      this.setState(flag === false ? null : ButtonState.DISABLED);
	      this.getMainButton().setDisabled(flag);
	      this.getMenuButton().setDisabled(flag);
	      return this;
	    }
	    /**
	     * @protected
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "getMenuBindElement",
	    value: function getMenuBindElement() {
	      if (this.getMenuTarget() === SplitSubButtonType.MENU) {
	        return this.getMenuButton().getContainer();
	      } else {
	        return this.getContainer();
	      }
	    }
	    /**
	     * @protected
	     * @param {MouseEvent} event
	     */
	  }, {
	    key: "handleMenuClick",
	    value: function handleMenuClick(event) {
	      this.getMenuWindow().show();
	      const isActive = this.getMenuWindow().getPopupWindow().isShown();
	      this.getMenuButton().setActive(isActive);
	    }
	    /**
	     * @protected
	     */
	  }, {
	    key: "handleMenuClose",
	    value: function handleMenuClose() {
	      this.getMenuButton().setActive(false);
	    }
	    /**
	     * @protected
	     * @return {HTMLElement}
	     */
	  }, {
	    key: "getMenuClickElement",
	    value: function getMenuClickElement() {
	      return this.getMenuButton().getContainer();
	    }
	    /**
	     * @public
	     * @return {SplitSubButtonType}
	     */
	  }, {
	    key: "getMenuTarget",
	    value: function getMenuTarget() {
	      return this.menuTarget;
	    }
	    /**
	     *
	     * @param {boolean} [flag=true]
	     * @return {this}
	     */
	  }, {
	    key: "setDropdown",
	    value: function setDropdown(flag) {
	      return this;
	    }
	    /**
	     * @public
	     * @return {boolean}
	     */
	  }, {
	    key: "isDropdown",
	    value: function isDropdown() {
	      return true;
	    }
	  }]);
	  return SplitButton;
	}(Button);
	babelHelpers.defineProperty(SplitButton, "BASE_CLASS", 'ui-btn-split');
	babelHelpers.defineProperty(SplitButton, "State", SplitButtonState);

	let _$2 = t => t,
	  _t$2;
	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	let ButtonManager = /*#__PURE__*/function () {
	  function ButtonManager() {
	    babelHelpers.classCallCheck(this, ButtonManager);
	  }
	  babelHelpers.createClass(ButtonManager, null, [{
	    key: "createFromNode",
	    /**
	     * @public
	     * @param {HTMLButtonElement | HTMLAnchorElement | HTMLInputElement} node
	     * @return {Button | SplitButton}
	     */
	    value: function createFromNode(node) {
	      if (!main_core.Type.isDomNode(node)) {
	        throw new Error('BX.UI.ButtonManager.createFromNode: "node" must be a DOM node.');
	      }
	      if (!main_core.Dom.hasClass(node, Button.BASE_CLASS) && !main_core.Dom.hasClass(node, SplitButton.BASE_CLASS)) {
	        throw new Error('BX.UI.ButtonManager.createFromNode: "node" is not a button.');
	      }
	      const isSplitButton = main_core.Dom.hasClass(node, SplitButton.BASE_CLASS);
	      let tag = null;
	      let text = null;
	      let textNode = null;
	      let counterNode = null;
	      let disabled = false;
	      let mainButtonOptions = {};
	      let menuButtonOptions = {};
	      if (isSplitButton) {
	        const mainButton = node.querySelector(`.${SplitSubButtonType.MAIN}`);
	        const menuButton = node.querySelector(`.${SplitSubButtonType.MENU}`);
	        if (!mainButton) {
	          throw new Error('BX.UI.ButtonManager.createFromNode: a split button doesn\'t have a main button.');
	        }
	        if (!menuButton) {
	          throw new Error('BX.UI.ButtonManager.createFromNode: a split button doesn\'t have a menu button.');
	        }
	        const mainButtonTag = _classStaticPrivateMethodGet(this, ButtonManager, _getTag).call(this, mainButton);
	        if (mainButtonTag === ButtonTag.INPUT || mainButtonTag === ButtonTag.SUBMIT) {
	          text = mainButton.value;
	        } else {
	          [textNode, counterNode] = _classStaticPrivateMethodGet(this, ButtonManager, _getTextNode).call(this, mainButton);
	          text = textNode.textContent;
	        }
	        disabled = main_core.Dom.hasClass(node, SplitButtonState.DISABLED);
	        mainButtonOptions = {
	          tag: mainButtonTag,
	          textNode,
	          counterNode,
	          buttonNode: mainButton,
	          disabled: main_core.Dom.hasClass(node, SplitButtonState.MAIN_DISABLED)
	        };
	        menuButtonOptions = {
	          tag: _classStaticPrivateMethodGet(this, ButtonManager, _getTag).call(this, menuButton),
	          buttonNode: menuButton,
	          textNode: null,
	          counterNode: null,
	          disabled: main_core.Dom.hasClass(node, SplitButtonState.MENU_DISABLED)
	        };
	      } else {
	        tag = _classStaticPrivateMethodGet(this, ButtonManager, _getTag).call(this, node);
	        if (tag === null) {
	          throw new Error('BX.UI.ButtonManager.createFromNode: "node" must be a button, link or input.');
	        }
	        disabled = main_core.Dom.hasClass(node, ButtonState.DISABLED);
	        if (tag === ButtonTag.INPUT || tag === ButtonTag.SUBMIT) {
	          text = node.value;
	        } else {
	          [textNode, counterNode] = _classStaticPrivateMethodGet(this, ButtonManager, _getTextNode).call(this, node);
	          text = textNode.textContent;
	        }
	      }
	      const options = {
	        id: node.dataset.btnUniqid,
	        buttonNode: node,
	        textNode: isSplitButton ? null : textNode,
	        counterNode: isSplitButton ? null : counterNode,
	        counter: _classStaticPrivateMethodGet(this, ButtonManager, _getCounter).call(this, counterNode),
	        tag,
	        text,
	        disabled,
	        mainButton: mainButtonOptions,
	        menuButton: menuButtonOptions,
	        size: _classStaticPrivateMethodGet(this, ButtonManager, _getEnumProp).call(this, node, ButtonSize),
	        color: _classStaticPrivateMethodGet(this, ButtonManager, _getEnumProp).call(this, node, ButtonColor),
	        icon: _classStaticPrivateMethodGet(this, ButtonManager, _getEnumProp).call(this, node, ButtonIcon),
	        state: _classStaticPrivateMethodGet(this, ButtonManager, _getEnumProp).call(this, node, isSplitButton ? SplitButtonState : ButtonState),
	        noCaps: main_core.Dom.hasClass(node, ButtonStyle.NO_CAPS),
	        round: main_core.Dom.hasClass(node, ButtonStyle.ROUND)
	      };
	      const nodeOptions = main_core.Dom.attr(node, 'data-json-options') || {};
	      if (main_core.Dom.hasClass(node, ButtonStyle.DROPDOWN)) {
	        options.dropdown = true;
	      } else if (nodeOptions.dropdown === false) {
	        options.dropdown = false;
	      }
	      if (nodeOptions.onclick) {
	        options.onclick = _classStaticPrivateMethodGet(this, ButtonManager, _convertEventHandler).call(this, nodeOptions.onclick);
	      }
	      if (main_core.Type.isPlainObject(nodeOptions.events)) {
	        options.events = nodeOptions.events;
	        _classStaticPrivateMethodGet(this, ButtonManager, _convertEvents).call(this, options.events);
	      }
	      if (main_core.Type.isPlainObject(nodeOptions.menu)) {
	        options.menu = nodeOptions.menu;
	        _classStaticPrivateMethodGet(this, ButtonManager, _convertMenuEvents).call(this, options.menu.items);
	      }
	      ['mainButton', 'menuButton'].forEach(button => {
	        if (!main_core.Type.isPlainObject(nodeOptions[button])) {
	          return;
	        }
	        options[button] = main_core.Runtime.merge(options[button], nodeOptions[button]);
	        if (options[button].onclick) {
	          options[button].onclick = _classStaticPrivateMethodGet(this, ButtonManager, _convertEventHandler).call(this, options[button].onclick);
	        }
	        _classStaticPrivateMethodGet(this, ButtonManager, _convertEvents).call(this, options[button].events);
	      });
	      if (main_core.Type.isStringFilled(nodeOptions.menuTarget)) {
	        options.menuTarget = nodeOptions.menuTarget;
	      }
	      return isSplitButton ? new SplitButton(options) : new Button(options);
	    }
	  }, {
	    key: "createByUniqId",
	    value: function createByUniqId(id) {
	      if (!main_core.Type.isStringFilled(id)) {
	        return null;
	      }
	      const node = document.querySelector(`[data-btn-uniqid="${id}"]`);
	      return node ? this.createFromNode(node) : null;
	    }
	    /**
	     * @private
	     * @param {HTMLElement} node
	     * @return {null|number}
	     */
	  }, {
	    key: "getByUniqid",
	    /**
	     * @deprecated
	     * @param uniqId
	     * @return {null|*}
	     */
	    value: function getByUniqid(uniqId) {
	      const toolbar = BX.UI.ToolbarManager.getDefaultToolbar();
	      return toolbar ? toolbar.getButton(uniqId) : null;
	    }
	  }]);
	  return ButtonManager;
	}();
	function _getTag(node) {
	  if (node.nodeName === 'A') {
	    return ButtonTag.LINK;
	  } else if (node.nodeName === 'BUTTON') {
	    return ButtonTag.BUTTON;
	  } else if (node.nodeName === 'INPUT' && node.type === 'button') {
	    return ButtonTag.INPUT;
	  } else if (node.nodeName === 'INPUT' && node.type === 'submit') {
	    return ButtonTag.SUBMIT;
	  }
	  return null;
	}
	function _getTextNode(node) {
	  let textNode = node.querySelector('.ui-btn-text');
	  const counterNode = node.querySelector('.ui-btn-counter');
	  if (!textNode) {
	    if (counterNode) {
	      main_core.Dom.remove(counterNode);
	    }
	    textNode = main_core.Tag.render(_t$2 || (_t$2 = _$2`<span class="ui-btn-text">${0}</span>`), node.innerHTML.trim());
	    main_core.Dom.clean(node);
	    main_core.Dom.append(textNode, node);
	    if (counterNode) {
	      main_core.Dom.append(counterNode, node);
	    }
	  }
	  return [textNode, counterNode];
	}
	function _getCounter(counterNode) {
	  if (main_core.Type.isDomNode(counterNode)) {
	    const textContent = counterNode.textContent;
	    const counter = Number(textContent);
	    return main_core.Type.isNumber(counter) ? counter : textContent;
	  }
	  return null;
	}
	function _getEnumProp(node, enumeration) {
	  for (let key in enumeration) {
	    if (!enumeration.hasOwnProperty(key)) {
	      continue;
	    }
	    if (main_core.Dom.hasClass(node, enumeration[key])) {
	      return enumeration[key];
	    }
	  }
	  return null;
	}
	function _convertEventHandler(handler) {
	  if (main_core.Type.isFunction(handler)) {
	    return handler;
	  }
	  if (!main_core.Type.isObject(handler)) {
	    throw new Error('BX.UI.ButtonManager.createFromNode: Event handler must be described as object or function.');
	  }
	  if (main_core.Type.isStringFilled(handler.code)) {
	    return function () {
	      // handle code can use callback arguments
	      eval(handler.code);
	    };
	  } else if (main_core.Type.isStringFilled(handler.event)) {
	    return function (...args) {
	      let event;
	      if (args[0] instanceof main_core_events.BaseEvent) {
	        event = args[0];
	      } else {
	        if (args[0] instanceof BaseButton) {
	          event = new main_core_events.BaseEvent({
	            data: {
	              button: args[0],
	              event: args[1]
	            }
	          });
	        } else if (args[1] instanceof main_popup.MenuItem) {
	          event = new main_core_events.BaseEvent({
	            data: {
	              item: args[1],
	              event: args[0]
	            }
	          });
	        } else {
	          event = new main_core_events.BaseEvent({
	            data: args
	          });
	        }
	      }
	      main_core_events.EventEmitter.emit(handler.event, event);
	    };
	  } else if (main_core.Type.isStringFilled(handler.handler)) {
	    return function (...args) {
	      const fn = main_core.Reflection.getClass(handler.handler);
	      if (main_core.Type.isFunction(fn)) {
	        let context = this;
	        if (main_core.Type.isStringFilled(handler.context)) {
	          context = main_core.Reflection.getClass(handler.context);
	        }
	        return fn.apply(context, args);
	      } else {
	        console.warn(`BX.UI.ButtonManager.createFromNode: be aware, the handler ${handler.handler} is not a function.`);
	      }
	      return null;
	    };
	  }
	  return null;
	}
	function _convertEvents(events) {
	  if (main_core.Type.isPlainObject(events)) {
	    for (let [eventName, eventFn] of Object.entries(events)) {
	      events[eventName] = _classStaticPrivateMethodGet(this, ButtonManager, _convertEventHandler).call(this, eventFn);
	    }
	  }
	}
	function _convertMenuEvents(items) {
	  if (!main_core.Type.isArray(items)) {
	    return;
	  }
	  items.forEach(item => {
	    if (item.onclick) {
	      item.onclick = _classStaticPrivateMethodGet(this, ButtonManager, _convertEventHandler).call(this, item.onclick);
	    }
	    if (item.events) {
	      _classStaticPrivateMethodGet(this, ButtonManager, _convertEvents).call(this, item.events);
	    }
	    if (main_core.Type.isArray(item.items)) {
	      _classStaticPrivateMethodGet(this, ButtonManager, _convertMenuEvents).call(this, item.items);
	    }
	  });
	}

	/**
	 * @namespace {BX.UI}
	 */
	let IButton = /*#__PURE__*/function () {
	  function IButton() {
	    babelHelpers.classCallCheck(this, IButton);
	  }
	  babelHelpers.createClass(IButton, [{
	    key: "render",
	    value: function render() {
	      throw new Error('BX.UI.IButton: Must be implemented by a subclass');
	    }
	  }]);
	  return IButton;
	}();

	/**
	 * @namespace {BX.UI}
	 */
	let AddButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(AddButton, _Button);
	  function AddButton() {
	    babelHelpers.classCallCheck(this, AddButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AddButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(AddButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_ADD_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return AddButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let ApplyButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(ApplyButton, _Button);
	  function ApplyButton() {
	    babelHelpers.classCallCheck(this, ApplyButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ApplyButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(ApplyButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_APPLY_BTN_TEXT'),
	        color: ButtonColor.LIGHT_BORDER
	      };
	    }
	  }]);
	  return ApplyButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let CancelButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(CancelButton, _Button);
	  function CancelButton() {
	    babelHelpers.classCallCheck(this, CancelButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CancelButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CancelButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CANCEL_BTN_TEXT'),
	        color: ButtonColor.LINK
	      };
	    }
	  }]);
	  return CancelButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let CloseButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(CloseButton, _Button);
	  function CloseButton() {
	    babelHelpers.classCallCheck(this, CloseButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CloseButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CloseButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CLOSE_BTN_TEXT'),
	        color: ButtonColor.LINK
	      };
	    }
	  }]);
	  return CloseButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let CreateButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(CreateButton, _Button);
	  function CreateButton() {
	    babelHelpers.classCallCheck(this, CreateButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CreateButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CreateButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CREATE_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return CreateButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let SaveButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(SaveButton, _Button);
	  function SaveButton() {
	    babelHelpers.classCallCheck(this, SaveButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SaveButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(SaveButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_SAVE_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return SaveButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let SendButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(SendButton, _Button);
	  function SendButton() {
	    babelHelpers.classCallCheck(this, SendButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SendButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(SendButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_SEND_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return SendButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let SettingsButton = /*#__PURE__*/function (_Button) {
	  babelHelpers.inherits(SettingsButton, _Button);
	  function SettingsButton() {
	    babelHelpers.classCallCheck(this, SettingsButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SettingsButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(SettingsButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        icon: ButtonIcon.SETTING,
	        color: ButtonColor.LIGHT_BORDER,
	        dropdown: false
	      };
	    }
	  }]);
	  return SettingsButton;
	}(Button);

	/**
	 * @namespace {BX.UI}
	 */
	let AddSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(AddSplitButton, _SplitButton);
	  function AddSplitButton() {
	    babelHelpers.classCallCheck(this, AddSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AddSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(AddSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_ADD_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return AddSplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let ApplySplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(ApplySplitButton, _SplitButton);
	  function ApplySplitButton() {
	    babelHelpers.classCallCheck(this, ApplySplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ApplySplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(ApplySplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_APPLY_BTN_TEXT'),
	        color: ButtonColor.LIGHT_BORDER
	      };
	    }
	  }]);
	  return ApplySplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let CancelSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(CancelSplitButton, _SplitButton);
	  function CancelSplitButton() {
	    babelHelpers.classCallCheck(this, CancelSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CancelSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CancelSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CANCEL_BTN_TEXT'),
	        color: ButtonColor.LINK
	      };
	    }
	  }]);
	  return CancelSplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let CloseSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(CloseSplitButton, _SplitButton);
	  function CloseSplitButton() {
	    babelHelpers.classCallCheck(this, CloseSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CloseSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CloseSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CLOSE_BTN_TEXT'),
	        color: ButtonColor.LINK
	      };
	    }
	  }]);
	  return CloseSplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let CreateSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(CreateSplitButton, _SplitButton);
	  function CreateSplitButton() {
	    babelHelpers.classCallCheck(this, CreateSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CreateSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(CreateSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_CREATE_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return CreateSplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let SaveSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(SaveSplitButton, _SplitButton);
	  function SaveSplitButton() {
	    babelHelpers.classCallCheck(this, SaveSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SaveSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(SaveSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_SAVE_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return SaveSplitButton;
	}(SplitButton);

	/**
	 * @namespace {BX.UI}
	 */
	let SendSplitButton = /*#__PURE__*/function (_SplitButton) {
	  babelHelpers.inherits(SendSplitButton, _SplitButton);
	  function SendSplitButton() {
	    babelHelpers.classCallCheck(this, SendSplitButton);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SendSplitButton).apply(this, arguments));
	  }
	  babelHelpers.createClass(SendSplitButton, [{
	    key: "getDefaultOptions",
	    value: function getDefaultOptions() {
	      return {
	        text: main_core.Loc.getMessage('UI_BUTTONS_SEND_BTN_TEXT'),
	        color: ButtonColor.SUCCESS
	      };
	    }
	  }]);
	  return SendSplitButton;
	}(SplitButton);

	exports.IButton = IButton;
	exports.BaseButton = BaseButton;
	exports.Button = Button;
	exports.SplitButton = SplitButton;
	exports.SplitSubButton = SplitSubButton;
	exports.ButtonManager = ButtonManager;
	exports.ButtonIcon = ButtonIcon;
	exports.ButtonSize = ButtonSize;
	exports.ButtonState = ButtonState;
	exports.ButtonColor = ButtonColor;
	exports.ButtonStyle = ButtonStyle;
	exports.ButtonTag = ButtonTag;
	exports.SplitButtonState = SplitButtonState;
	exports.SplitSubButtonType = SplitSubButtonType;
	exports.AddButton = AddButton;
	exports.ApplyButton = ApplyButton;
	exports.CancelButton = CancelButton;
	exports.CloseButton = CloseButton;
	exports.CreateButton = CreateButton;
	exports.SaveButton = SaveButton;
	exports.SendButton = SendButton;
	exports.SettingsButton = SettingsButton;
	exports.AddSplitButton = AddSplitButton;
	exports.ApplySplitButton = ApplySplitButton;
	exports.CancelSplitButton = CancelSplitButton;
	exports.CloseSplitButton = CloseSplitButton;
	exports.CreateSplitButton = CreateSplitButton;
	exports.SaveSplitButton = SaveSplitButton;
	exports.SendSplitButton = SendSplitButton;

}((this.BX.UI = this.BX.UI || {}),BX.Event,BX.Main,BX));
//# sourceMappingURL=ui.buttons.bundle.js.map
