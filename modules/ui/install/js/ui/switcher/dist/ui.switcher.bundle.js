/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	const AirSwitcherStyle = Object.freeze({
	  SOLID: 'SOLID',
	  TINTED: 'TINTED'
	});

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;

	/*
	* extraLarge, large, extraExtraSmall options supported only by the air button
	* */
	const SwitcherSize = Object.freeze({
	  extraLarge: 'extra-large',
	  large: 'large',
	  medium: 'medium',
	  small: 'small',
	  extraSmall: 'extra-small',
	  extraExtraSmall: 'extra-extra-small'
	});
	const SwitcherColor = Object.freeze({
	  primary: 'primary',
	  green: 'green'
	});
	var _classNameSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("classNameSize");
	var _classNameColor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("classNameColor");
	var _classNameStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("classNameStyle");
	var _useAirDesign = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useAirDesign");
	var _disabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("disabled");
	var _inputName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputName");
	var _loading = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loading");
	var _classNameOff = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("classNameOff");
	var _classNameLock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("classNameLock");
	var _attributeName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attributeName");
	var _showStateTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showStateTitle");
	var _attributeInitName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("attributeInitName");
	var _initNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initNode");
	var _fireEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fireEvent");
	class Switcher {
	  /**
	   * Switcher.
	   *
	   * @param {object} [options] - Options.
	   * @param {string} [options.attributeName] - Name of switcher attribute.
	   * @param {Element} [options.node] - Node.
	   * @param {string} [options.id] - ID.
	   * @param {123} [options.checked] - Checked.
	   * @param {string} [options.inputName] - Input name.
	   * @constructor
	   */
	  constructor(options) {
	    Object.defineProperty(this, _fireEvent, {
	      value: _fireEvent2
	    });
	    Object.defineProperty(this, _initNode, {
	      value: _initNode2
	    });
	    Object.defineProperty(this, _classNameSize, {
	      writable: true,
	      value: {
	        [SwitcherSize.extraExtraSmall]: 'ui-switcher-size-xss',
	        [SwitcherSize.extraSmall]: 'ui-switcher-size-xs',
	        [SwitcherSize.small]: 'ui-switcher-size-sm',
	        [SwitcherSize.medium]: 'ui-switcher-size-md',
	        [SwitcherSize.large]: 'ui-switcher-size-lg',
	        [SwitcherSize.extraLarge]: 'ui-switcher-size-xl'
	      }
	    });
	    Object.defineProperty(this, _classNameColor, {
	      writable: true,
	      value: {
	        [SwitcherColor.primary]: '',
	        [SwitcherColor.green]: 'ui-switcher-color-green'
	      }
	    });
	    Object.defineProperty(this, _classNameStyle, {
	      writable: true,
	      value: {
	        [AirSwitcherStyle.SOLID]: '--style-solid',
	        [AirSwitcherStyle.TINTED]: '--style-tinted'
	      }
	    });
	    Object.defineProperty(this, _useAirDesign, {
	      writable: true,
	      value: false
	    });
	    this.node = null;
	    this.checked = false;
	    this.id = '';
	    Object.defineProperty(this, _disabled, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _inputName, {
	      writable: true,
	      value: ''
	    });
	    Object.defineProperty(this, _loading, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _classNameOff, {
	      writable: true,
	      value: 'ui-switcher-off'
	    });
	    Object.defineProperty(this, _classNameLock, {
	      writable: true,
	      value: 'ui-switcher-lock'
	    });
	    Object.defineProperty(this, _attributeName, {
	      writable: true,
	      value: 'data-switcher'
	    });
	    Object.defineProperty(this, _showStateTitle, {
	      writable: true,
	      value: true
	    });
	    this.init(options);
	    Switcher.list.push(this);
	  }
	  static getById(id) {
	    return Switcher.list.find(item => item.id === id) || null;
	  }
	  static initByClassName() {
	    const nodes = document.getElementsByClassName(Switcher.className);
	    Array.from(nodes).forEach(function (node) {
	      if (node.getAttribute(babelHelpers.classPrivateFieldLooseBase(Switcher, _attributeInitName)[_attributeInitName])) {
	        return;
	      }
	      new Switcher({
	        node: node
	      });
	    });
	  }
	  static getList() {
	    return Switcher.list;
	  }
	  init(options = {}) {
	    babelHelpers.classPrivateFieldLooseBase(this, _attributeName)[_attributeName] = main_core.Type.isString(options.attributeName) ? options.attributeName : babelHelpers.classPrivateFieldLooseBase(this, _attributeName)[_attributeName];
	    this.handlers = main_core.Type.isPlainObject(options.handlers) ? options.handlers : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName] = main_core.Type.isString(options.inputName) ? options.inputName : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _loading)[_loading] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _showStateTitle)[_showStateTitle] = main_core.Type.isBoolean(options.showStateTitle) ? options.showStateTitle : true;
	    this.events = {
	      toggled: 'toggled',
	      checked: 'checked',
	      unchecked: 'unchecked',
	      lock: 'lock',
	      unlock: 'unlock'
	    };
	    if (options.node) {
	      if (!main_core.Type.isDomNode(options.node)) {
	        throw new Error('Parameter `node` DOM Node expected.');
	      }
	      this.node = options.node;
	      let data = this.node.getAttribute(babelHelpers.classPrivateFieldLooseBase(this, _attributeName)[_attributeName]);
	      try {
	        data = JSON.parse(data) || {};
	      } catch (e) {
	        data = {};
	      }
	      if (data.id) {
	        this.id = data.id;
	      }
	      this.checked = Boolean(data.checked);
	      babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName] = data.inputName;
	      if (main_core.Type.isString(data.color) && Object.values(SwitcherColor).includes(data.color)) {
	        options.color = data.color;
	      }
	      if (main_core.Type.isString(data.size) && Object.values(SwitcherSize).includes(data.size)) {
	        options.size = data.size;
	      }
	      if (main_core.Dom.hasClass(this.node, '--air')) {
	        options.useAirDesign = true;
	      }
	    } else {
	      this.node = document.createElement('span');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign] = options.useAirDesign === true;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	      var _babelHelpers$classPr;
	      this.setAirDesign();
	      main_core.Dom.addClass(this.node, (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _classNameStyle)[_classNameStyle][options.style]) != null ? _babelHelpers$classPr : '');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _classNameSize)[_classNameSize][options.size]) {
	      main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameSize)[_classNameSize][options.size]);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _classNameColor)[_classNameColor][options.color] && babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign] === false) {
	      main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameColor)[_classNameColor][options.color]);
	    }
	    if (main_core.Type.isString(options.id) || main_core.Type.isNumber(options.id)) {
	      this.id = options.id;
	    } else if (!this.id) {
	      this.id = Math.random();
	    }
	    if (main_core.Type.isString(options.inputName)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName] = options.inputName;
	    }
	    this.checked = main_core.Type.isBoolean(options.checked) ? options.checked : this.checked;
	    babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled] = main_core.Type.isBoolean(options.disabled) ? options.disabled : babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled];
	    babelHelpers.classPrivateFieldLooseBase(this, _initNode)[_initNode]();
	    this.check(this.checked, false);
	    this.disable(babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled], false);
	  }
	  disable(disabled, fireEvents = true, event = {}) {
	    if (this.isLoading()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled] = disabled;
	    fireEvents = fireEvents !== false;
	    if (disabled) {
	      main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameLock)[_classNameLock]);
	      fireEvents ? babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent](this.events.lock, event) : null;
	    } else {
	      main_core.Dom.removeClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameLock)[_classNameLock]);
	      fireEvents ? babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent](this.events.unlock, event) : null;
	    }
	  }
	  check(checked, fireEvents = true, event = {}) {
	    if (this.isLoading()) {
	      return;
	    }
	    this.checked = !!checked;
	    if (this.inputNode) {
	      this.inputNode.value = this.checked ? 'Y' : 'N';
	    }
	    fireEvents = fireEvents !== false;
	    if (this.checked) {
	      main_core.Dom.removeClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameOff)[_classNameOff]);
	      fireEvents ? babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent](this.events.unchecked, event) : null;
	    } else {
	      main_core.Dom.addClass(this.node, babelHelpers.classPrivateFieldLooseBase(this, _classNameOff)[_classNameOff]);
	      fireEvents ? babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent](this.events.checked, event) : null;
	    }
	    if (fireEvents) {
	      babelHelpers.classPrivateFieldLooseBase(this, _fireEvent)[_fireEvent](this.events.toggled, event);
	    }
	  }
	  isDisabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _disabled)[_disabled];
	  }
	  isChecked() {
	    return this.checked;
	  }
	  toggle(event) {
	    if (this.isDisabled()) {
	      return;
	    }
	    this.check(!this.isChecked(), true, event);
	  }
	  setLoading(mode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _loading)[_loading] = Boolean(mode);
	    const cursor = this.getNode().querySelector('.ui-switcher-cursor');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _loading)[_loading]) {
	      const svg = main_core.Tag.render(_t || (_t = _`
				<svg viewBox="25 25 50 50">
					<circle
						class="ui-sidepanel-wrapper-loader-path"
						cx="50"
						cy="50"
						r="19"
						fill="none"
						stroke-width="5"
						stroke-miterlimit="10"
					>
					</circle>
				</svg>
			`));
	      main_core.Dom.append(svg, cursor);
	    } else {
	      cursor.innerHTML = '';
	    }
	  }
	  isLoading() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _loading)[_loading];
	  }
	  setAirDesign(flag = true) {
	    if (flag) {
	      main_core.Dom.addClass(this.node, '--air');
	    } else {
	      main_core.Dom.removeClass(this.node, '--air');
	    }
	  }
	  renderTo(targetNode) {
	    if (!main_core.Type.isDomNode(targetNode)) {
	      throw new Error('Target node must be HTMLElement');
	    }
	    return main_core.Dom.append(this.getNode(), targetNode);
	  }
	  getNode() {
	    return this.node;
	  }
	  getAttributeName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _attributeName)[_attributeName];
	  }
	  getInputName() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName];
	  }
	}
	function _initNode2() {
	  if (this.node.getAttribute(babelHelpers.classPrivateFieldLooseBase(Switcher, _attributeInitName)[_attributeInitName])) {
	    return;
	  }
	  this.node.setAttribute(babelHelpers.classPrivateFieldLooseBase(Switcher, _attributeInitName)[_attributeInitName], 'y');
	  main_core.Dom.addClass(this.node, Switcher.className);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	    this.setAirDesign();
	  }
	  const element = main_core.Tag.render(_t2 || (_t2 = _`
			<div>
				<span class="ui-switcher-cursor"></span>
				<span class="ui-switcher-enabled">
					${0}
				</span>
				<span class="ui-switcher-disabled">
					${0}
				</span>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _showStateTitle)[_showStateTitle] ? main_core.Loc.getMessage('UI_SWITCHER_ON') : '', babelHelpers.classPrivateFieldLooseBase(this, _showStateTitle)[_showStateTitle] ? main_core.Loc.getMessage('UI_SWITCHER_OFF') : '');
	  this.node.innerHTML = element.innerHTML;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName]) {
	    this.inputNode = main_core.Tag.render(_t3 || (_t3 = _`
				<input type="hidden" name="${0}" />
			`), babelHelpers.classPrivateFieldLooseBase(this, _inputName)[_inputName]);
	    main_core.Dom.append(this.inputNode, this.node);
	  }
	  main_core.bind(this.node, 'click', this.toggle.bind(this));
	}
	function _fireEvent2(eventName, event) {
	  main_core.onCustomEvent(this, eventName);
	  if (this.handlers[eventName]) {
	    this.handlers[eventName].call(this, event);
	  }
	}
	Object.defineProperty(Switcher, _attributeInitName, {
	  writable: true,
	  value: 'data-switcher-init'
	});
	Switcher.list = [];
	Switcher.className = 'ui-switcher';
	Switcher.classNameOff = 'ui-switcher-off';

	exports.SwitcherSize = SwitcherSize;
	exports.SwitcherColor = SwitcherColor;
	exports.Switcher = Switcher;
	exports.AirSwitcherStyle = AirSwitcherStyle;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=ui.switcher.bundle.js.map
