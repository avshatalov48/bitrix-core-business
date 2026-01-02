/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_actionsBar,ui_designTokens_air,main_popup,main_core,ui_cnt,main_core_events) {
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
	  _t9;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _collapsedIcon = /*#__PURE__*/new WeakMap();
	var _collapsed = /*#__PURE__*/new WeakMap();
	var _dataAttributes = /*#__PURE__*/new WeakMap();
	var _useAirDesign = /*#__PURE__*/new WeakMap();
	var _bindEvents = /*#__PURE__*/new WeakSet();
	var _getPanel = /*#__PURE__*/new WeakSet();
	var _getCounter = /*#__PURE__*/new WeakSet();
	var _getValue = /*#__PURE__*/new WeakSet();
	var _getTitle = /*#__PURE__*/new WeakSet();
	var _getCollapsedIcon = /*#__PURE__*/new WeakSet();
	var _getCross = /*#__PURE__*/new WeakSet();
	var _setElementDataAttributes = /*#__PURE__*/new WeakSet();
	var _getCounterStyleByColor = /*#__PURE__*/new WeakSet();
	var _getItemClassModifierByValue = /*#__PURE__*/new WeakSet();
	var _getZeroItemClassModifier = /*#__PURE__*/new WeakSet();
	let CounterItem$$1 = /*#__PURE__*/function () {
	  function CounterItem$$1(args) {
	    var _args$id, _args$panel, _args$title, _args$color, _args$parentId, _args$collapsedIcon;
	    babelHelpers.classCallCheck(this, CounterItem$$1);
	    _classPrivateMethodInitSpec(this, _getZeroItemClassModifier);
	    _classPrivateMethodInitSpec(this, _getItemClassModifierByValue);
	    _classPrivateMethodInitSpec(this, _getCounterStyleByColor);
	    _classPrivateMethodInitSpec(this, _setElementDataAttributes);
	    _classPrivateMethodInitSpec(this, _getCross);
	    _classPrivateMethodInitSpec(this, _getCollapsedIcon);
	    _classPrivateMethodInitSpec(this, _getTitle);
	    _classPrivateMethodInitSpec(this, _getValue);
	    _classPrivateMethodInitSpec(this, _getCounter);
	    _classPrivateMethodInitSpec(this, _getPanel);
	    _classPrivateMethodInitSpec(this, _bindEvents);
	    _classPrivateFieldInitSpec(this, _collapsedIcon, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _collapsed, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _dataAttributes, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _useAirDesign, {
	      writable: true,
	      value: false
	    });
	    this.id = (_args$id = args.id) !== null && _args$id !== void 0 ? _args$id : null;
	    this.separator = main_core.Type.isBoolean(args.separator) ? args.separator : true;
	    this.items = main_core.Type.isArray(args.items) ? args.items : [];
	    this.popupMenu = null;
	    this.isActive = main_core.Type.isBoolean(args.isActive) ? args.isActive : false;
	    this.isRestricted = main_core.Type.isBoolean(args.isRestricted) ? args.isRestricted : false;
	    this.panel = (_args$panel = args.panel) !== null && _args$panel !== void 0 ? _args$panel : null;
	    this.title = (_args$title = args.title) !== null && _args$title !== void 0 ? _args$title : null;
	    this.value = main_core.Type.isNumber(args.value) && args.value !== undefined ? args.value : null;
	    this.titleOrder = null;
	    this.valueOrder = null;
	    this.color = (_args$color = args.color) !== null && _args$color !== void 0 ? _args$color : null;
	    this.parent = main_core.Type.isBoolean(args.parent) ? args.parent : null;
	    this.parentId = (_args$parentId = args.parentId) !== null && _args$parentId !== void 0 ? _args$parentId : null;
	    this.locked = args.locked === true;
	    this.type = main_core.Type.isString(args.type) ? args.type.toLowerCase() : null;
	    this.eventsForActive = main_core.Type.isObject(args.eventsForActive) ? args.eventsForActive : {};
	    this.eventsForUnActive = main_core.Type.isObject(args.eventsForUnActive) ? args.eventsForUnActive : {};
	    this.hideValue = main_core.Type.isBoolean(args.hideValue) ? args.hideValue : false;
	    babelHelpers.classPrivateFieldSet(this, _collapsedIcon, (_args$collapsedIcon = args.collapsedIcon) !== null && _args$collapsedIcon !== void 0 ? _args$collapsedIcon : null);
	    babelHelpers.classPrivateFieldSet(this, _collapsed, args.collapsed === true);
	    babelHelpers.classPrivateFieldSet(this, _dataAttributes, main_core.Type.isPlainObject(args.dataAttributes) ? args.dataAttributes : {});
	    babelHelpers.classPrivateFieldSet(this, _useAirDesign, args.useAirDesign === true);
	    if (main_core.Type.isObject(args.title)) {
	      var _args$title$value;
	      this.title = (_args$title$value = args.title.value) !== null && _args$title$value !== void 0 ? _args$title$value : null;
	      this.titleOrder = main_core.Type.isNumber(args.title.order) ? args.title.order : null;
	    }
	    if (main_core.Type.isObject(args.value)) {
	      this.value = main_core.Type.isNumber(args.value.value) ? args.value.value : null;
	      this.valueOrder = main_core.Type.isNumber(args.value.order) ? args.value.order : null;
	    }
	    this.layout = {
	      container: null,
	      value: null,
	      title: null,
	      cross: null,
	      dropdownArrow: null,
	      menuItem: null
	    };
	    this.counter = _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this);
	    if (!_classPrivateMethodGet(this, _getPanel, _getPanel2).call(this).isMultiselect()) {
	      _classPrivateMethodGet(this, _bindEvents, _bindEvents2).call(this);
	    }
	  }
	  babelHelpers.createClass(CounterItem$$1, [{
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "hasParentId",
	    value: function hasParentId() {
	      return this.parentId;
	    }
	  }, {
	    key: "updateValue",
	    value: function updateValue(param) {
	      if (main_core.Type.isNumber(param)) {
	        this.value = param;
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).update(param);
	        if (param === 0) {
	          this.updateColor(this.parentId ? 'GRAY' : 'THEME');
	          main_core.Dom.addClass(this.layout.container, _classPrivateMethodGet(this, _getZeroItemClassModifier, _getZeroItemClassModifier2).call(this));
	        } else {
	          main_core.Dom.removeClass(this.layout.container, _classPrivateMethodGet(this, _getZeroItemClassModifier, _getZeroItemClassModifier2).call(this));
	        }
	      }
	    }
	  }, {
	    key: "updateValueAnimate",
	    value: function updateValueAnimate(param) {
	      if (main_core.Type.isNumber(param)) {
	        this.value = param;
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).update(param);
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).show();
	        if (param === 0) {
	          const color = this.parentId ? 'GRAY' : 'THEME';
	          this.updateColor(color);
	          _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).setStyle(_classPrivateMethodGet(this, _getCounterStyleByColor, _getCounterStyleByColor2).call(this, ui_cnt.Counter.Color[color]));
	        }
	      }
	    }
	  }, {
	    key: "updateColor",
	    value: function updateColor(param) {
	      if (main_core.Type.isString(param)) {
	        this.color = param;
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).setColor(ui_cnt.Counter.Color[param]);
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).setStyle(_classPrivateMethodGet(this, _getCounterStyleByColor, _getCounterStyleByColor2).call(this, ui_cnt.Counter.Color[param]));
	      }
	    }
	  }, {
	    key: "activate",
	    value: function activate(isEmitEvent = true) {
	      this.isActive = true;
	      if (this.parentId) {
	        const target = BX.findParent(this.getContainerMenu(), {
	          className: 'ui-counter-panel__popup-item'
	        });
	        if (target) {
	          main_core.Dom.addClass(target, '-active');
	        }
	      } else {
	        main_core.Dom.addClass(this.getContainer(), '--active');
	      }
	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:activate', this);
	      }
	    }
	  }, {
	    key: "deactivate",
	    value: function deactivate(isEmitEvent = true) {
	      this.isActive = false;
	      if (this.parentId) {
	        const target = BX.findParent(this.getContainerMenu(), {
	          className: 'ui-counter-panel__popup-item'
	        });
	        if (target) {
	          main_core.Dom.removeClass(target, '--active');
	          main_core.Dom.removeClass(target, '--hover');
	        }
	      } else {
	        main_core.Dom.removeClass(this.getContainer(), '--active');
	        main_core.Dom.removeClass(this.getContainer(), '--hover');
	      }
	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
	      }
	    }
	  }, {
	    key: "collapse",
	    value: function collapse() {
	      main_core.Dom.addClass(this.getContainer(), '--collapsed');
	    }
	  }, {
	    key: "expand",
	    value: function expand() {
	      main_core.Dom.removeClass(this.getContainer(), '--collapsed');
	    }
	  }, {
	    key: "getSeparator",
	    value: function getSeparator() {
	      return this.separator;
	    }
	  }, {
	    key: "getCounterContainer",
	    value: function getCounterContainer() {
	      return this.layout.value;
	    }
	  }, {
	    key: "setEvents",
	    value: function setEvents(container) {
	      if (!container) {
	        container = this.getContainer();
	      }
	      if (this.eventsForActive) {
	        const eventKeys = Object.keys(this.eventsForActive);
	        for (const event of eventKeys) {
	          main_core.Event.bind(container, event, () => {
	            if (this.isActive) {
	              this.eventsForActive[event]();
	            }
	          });
	        }
	      }
	      if (this.eventsForUnActive) {
	        const eventKeys = Object.keys(this.eventsForUnActive);
	        for (const event of eventKeys) {
	          main_core.Event.bind(container, event, () => {
	            if (!this.isActive) {
	              this.eventsForUnActive[event]();
	            }
	          });
	        }
	      }
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      this.locked = true;
	      main_core.Dom.addClass(this.getContainer(), '--locked');
	    }
	  }, {
	    key: "unLock",
	    value: function unLock() {
	      this.locked = false;
	      main_core.Dom.removeClass(this.getContainer(), '--locked');
	    }
	  }, {
	    key: "getArrowDropdown",
	    value: function getArrowDropdown() {
	      if (!this.layout.dropdownArrow) {
	        this.layout.dropdownArrow = main_core.Tag.render(_t || (_t = _`
				<div class="ui-counter-panel__item-dropdown">
					<i></i>
				</div>
			`));
	      }
	      return this.layout.dropdownArrow;
	    }
	  }, {
	    key: "getContainerMenu",
	    value: function getContainerMenu() {
	      if (!this.layout.menuItem) {
	        this.layout.menuItem = main_core.Tag.render(_t2 || (_t2 = _`
				<span>
					${0}
					${0}
					${0}
				</span>
			`), _classPrivateMethodGet(this, _getValue, _getValue2).call(this), this.title, _classPrivateMethodGet(this, _getCross, _getCross2).call(this));
	      }
	      return this.layout.menuItem;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.layout.container) {
	        const type = this.type ? `id="ui-counter-panel-item-${this.type}"` : '';
	        const isValue = main_core.Type.isNumber(this.value);
	        this.layout.container = main_core.Tag.render(_t3 || (_t3 = _`
				<div ${0} class="ui-counter-panel__item ${0}">
					${0}
					${0}
					${0}
					${0}
				</div>
			`), type, _classPrivateMethodGet(this, _getItemClassModifierByValue, _getItemClassModifierByValue2).call(this, this.value), babelHelpers.classPrivateFieldGet(this, _collapsedIcon) ? _classPrivateMethodGet(this, _getCollapsedIcon, _getCollapsedIcon2).call(this) : '', isValue && !this.hideValue ? _classPrivateMethodGet(this, _getValue, _getValue2).call(this) : '', this.title ? _classPrivateMethodGet(this, _getTitle, _getTitle2).call(this) : '', isValue ? _classPrivateMethodGet(this, _getCross, _getCross2).call(this) : '');
	        if (this.parent) {
	          this.layout.container = main_core.Tag.render(_t4 || (_t4 = _`
					<div class="ui-counter-panel__item">
						${0}
						${0}
						${0}
					</div>
				`), this.title ? _classPrivateMethodGet(this, _getTitle, _getTitle2).call(this) : '', isValue ? _classPrivateMethodGet(this, _getValue, _getValue2).call(this) : '', _classPrivateMethodGet(this, _getCross, _getCross2).call(this));
	          main_core.Event.bind(_classPrivateMethodGet(this, _getCross, _getCross2).call(this), 'click', ev => {
	            this.deactivate();
	            ev.stopPropagation();
	          });
	          main_core.Dom.addClass(this.layout.container, '--dropdown');
	        }
	        if (!isValue) {
	          main_core.Dom.addClass(this.layout.container, '--string');
	        }
	        if (!isValue && !this.eventsForActive && !this.eventsForUnActive) {
	          main_core.Dom.addClass(this.layout.container, '--title');
	        }
	        if (!this.separator) {
	          main_core.Dom.addClass(this.layout.container, '--without-separator');
	        }
	        if (this.locked) {
	          main_core.Dom.addClass(this.layout.container, '--locked');
	        }
	        if (this.isActive) {
	          this.activate();
	        }
	        if (this.isRestricted) {
	          main_core.Dom.addClass(this.layout.container, '--restricted');
	        }
	        if (babelHelpers.classPrivateFieldGet(this, _collapsed)) {
	          this.collapse();
	        }
	        if (this.locked) {
	          this.lock();
	        }
	        this.setEvents(this.layout.container);
	        _classPrivateMethodGet(this, _setElementDataAttributes, _setElementDataAttributes2).call(this, this.layout.container);
	        main_core.Event.bind(this.layout.container, 'click', () => {
	          main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:click', {
	            item: this
	          });
	        });
	        if (isValue && this.items.length === 0 && !this.parent) {
	          main_core.Event.bind(this.layout.container, 'mouseenter', () => {
	            if (!this.isActive) {
	              main_core.Dom.addClass(this.layout.container, '--hover');
	            }
	          });
	          main_core.Event.bind(this.layout.container, 'mouseleave', () => {
	            if (!this.isActive) {
	              main_core.Dom.removeClass(this.layout.container, '--hover');
	            }
	          });
	          main_core.Event.bind(this.layout.container, 'click', () => {
	            if (this.isActive) {
	              this.deactivate();
	            } else {
	              this.activate();
	            }
	          });
	        }
	        if (this.parent) {
	          main_core.Dom.append(this.getArrowDropdown(), this.layout.container);
	        }
	      }
	      return this.layout.container;
	    }
	  }, {
	    key: "setDataAttributes",
	    value: function setDataAttributes(attributes) {
	      babelHelpers.classPrivateFieldSet(this, _dataAttributes, main_core.Type.isPlainObject(attributes) || {});
	      _classPrivateMethodGet(this, _setElementDataAttributes, _setElementDataAttributes2).call(this, this.getContainer());
	    }
	  }]);
	  return CounterItem$$1;
	}();
	function _bindEvents2() {
	  main_core_events.EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', item => {
	    const isLinkedItems = item.data.parentId === this.id;
	    if (item.data !== this && !isLinkedItems) {
	      this.deactivate();
	    }
	  });
	}
	function _getPanel2() {
	  return this.panel;
	}
	function _getCounter2() {
	  if (!this.counter) {
	    const counterColor = this.color ? ui_cnt.Counter.Color[this.color.toUpperCase()] : this.parentId ? ui_cnt.Counter.Color.GRAY : ui_cnt.Counter.Color.THEME;
	    this.counter = new ui_cnt.Counter({
	      color: counterColor,
	      value: this.value,
	      animation: false,
	      useAirDesign: babelHelpers.classPrivateFieldGet(this, _useAirDesign),
	      style: _classPrivateMethodGet(this, _getCounterStyleByColor, _getCounterStyleByColor2).call(this, counterColor)
	    });
	  }
	  return this.counter;
	}
	function _getValue2() {
	  if (!this.layout.value) {
	    const counterValue = this.isRestricted ? main_core.Tag.render(_t5 || (_t5 = _`<div class="ui-counter-panel__item-lock"></div>`)) : _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).getContainer();
	    this.layout.value = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="ui-counter-panel__item-value">
					${0}
				</div>
			`), counterValue);
	    main_core.Dom.style(this.layout.value, 'order', this.valueOrder);
	  }
	  return this.layout.value;
	}
	function _getTitle2() {
	  if (!this.layout.title) {
	    this.layout.title = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="ui-counter-panel__item-title">${0}</div>
			`), this.title);
	    main_core.Dom.style(this.layout.title, 'order', this.titleOrder);
	  }
	  return this.layout.title;
	}
	function _getCollapsedIcon2() {
	  return main_core.Tag.render(_t8 || (_t8 = _`
			<div class="ui-counter-panel__item-collapsed-icon ui-icon-set__scope --icon-${0}"></div>
		`), babelHelpers.classPrivateFieldGet(this, _collapsedIcon));
	}
	function _getCross2() {
	  if (!this.layout.cross) {
	    this.layout.cross = main_core.Tag.render(_t9 || (_t9 = _`
				<div class="ui-counter-panel__item-cross">
					<i></i>
				</div>
			`));
	  }
	  return this.layout.cross;
	}
	function _setElementDataAttributes2(element) {
	  if (!element) {
	    return;
	  }
	  Object.entries(babelHelpers.classPrivateFieldGet(this, _dataAttributes)).forEach(([key, value]) => {
	    main_core.Dom.attr(element, `data-${key}`, value);
	  });
	}
	function _getCounterStyleByColor2(color) {
	  if (color === ui_cnt.CounterColor.DANGER) {
	    return ui_cnt.CounterStyle.FILLED_ALERT;
	  }
	  if (color === ui_cnt.CounterColor.SUCCESS) {
	    return ui_cnt.CounterStyle.FILLED_SUCCESS;
	  }
	  return ui_cnt.CounterStyle.OUTLINE_NO_ACCENT;
	}
	function _getItemClassModifierByValue2(value) {
	  return value === 0 ? _classPrivateMethodGet(this, _getZeroItemClassModifier, _getZeroItemClassModifier2).call(this) : '';
	}
	function _getZeroItemClassModifier2() {
	  return '--zero';
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _adjustData = /*#__PURE__*/new WeakSet();
	var _getContainer = /*#__PURE__*/new WeakSet();
	var _render = /*#__PURE__*/new WeakSet();
	let CounterPanel = /*#__PURE__*/function () {
	  function CounterPanel(options) {
	    babelHelpers.classCallCheck(this, CounterPanel);
	    _classPrivateMethodInitSpec$1(this, _render);
	    _classPrivateMethodInitSpec$1(this, _getContainer);
	    _classPrivateMethodInitSpec$1(this, _adjustData);
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    this.items = main_core.Type.isArray(options.items) ? options.items : [];
	    this.multiselect = main_core.Type.isBoolean(options.multiselect) ? options.multiselect : null;
	    this.title = main_core.Type.isStringFilled(options.title) ? options.title : null;
	    this.container = null;
	    this.keys = [];
	    this.hasParent = [];
	  }
	  babelHelpers.createClass(CounterPanel, [{
	    key: "isMultiselect",
	    value: function isMultiselect() {
	      return this.multiselect;
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(param) {
	      if (param) {
	        const index = this.keys.indexOf(param);
	        return this.items[index];
	      }
	      return undefined;
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      _classPrivateMethodGet$1(this, _adjustData, _adjustData2).call(this);
	      _classPrivateMethodGet$1(this, _render, _render2).call(this);
	    }
	  }, {
	    key: "setItems",
	    value: function setItems(items) {
	      this.items = items;
	    }
	  }, {
	    key: "hasAirDesign",
	    value: function hasAirDesign() {
	      return main_core.Extension.getSettings('ui.counterpanel').get('useAirDesign') === true;
	    }
	  }]);
	  return CounterPanel;
	}();
	function _adjustData2() {
	  this.items = this.items.map(item => {
	    this.keys.push(item.id);
	    if (item.parentId) {
	      this.hasParent.push(item.parentId);
	    }
	    return new CounterItem$$1({
	      ...item,
	      useAirDesign: this.hasAirDesign(),
	      panel: this
	    });
	  });
	  this.hasParent.forEach(item => {
	    const index = this.keys.indexOf(item);
	    this.items[index].parent = true;
	  });
	  this.items.forEach(item => {
	    if (item.parentId) {
	      const index = this.keys.indexOf(item.parentId);
	      this.items[index].items.push(item.id);
	    }
	  });
	}
	function _getContainer2() {
	  if (!this.container) {
	    let myHead = '';
	    if (this.title) {
	      myHead = main_core.Tag.render(_t$1 || (_t$1 = _$1`
					<div class="ui-counter-panel__item-head">${0}</div>
				`), this.title);
	    }
	    this.container = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
				<div class="ui-counter-panel ui-counter-panel__scope">${0}</div>
			`), myHead);
	    if (this.hasAirDesign() === true) {
	      main_core.Dom.addClass(this.container, '--air');
	    }
	  }
	  return this.container;
	}
	function _render2() {
	  if (this.target && this.items.length > 0) {
	    this.items.forEach((item, key) => {
	      if (item instanceof CounterItem$$1) {
	        if (!item.hasParentId()) {
	          main_core.Dom.append(item.getContainer(), _classPrivateMethodGet$1(this, _getContainer, _getContainer2).call(this));
	          if (this.items.length !== key + 1 && this.items.length > 1) {
	            main_core.Dom.append(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
								<div class="ui-counter-panel__item-separator ${0}"></div>
							`), item.getSeparator() ? '' : '--invisible'), _classPrivateMethodGet$1(this, _getContainer, _getContainer2).call(this));
	          }
	        }
	        if (item.parent) {
	          main_core.Event.bind(item.getContainer(), 'click', () => {
	            const itemsArr = [];
	            item.getItems().forEach(item => {
	              const itemCounter = this.getItemById(item);
	              const test = {
	                html: itemCounter.getContainerMenu(),
	                className: `ui-counter-panel__popup-item ${this.hasAirDesign() ? '--air' : ''} menu-popup-no-icon ${itemCounter.isActive ? '--active' : ''}`,
	                onclick: () => {
	                  if (itemCounter.isActive) {
	                    itemCounter.deactivate();
	                  } else {
	                    itemCounter.activate();
	                  }
	                }
	              };
	              itemsArr.push(test);
	            });
	            const popup = new main_popup.PopupMenuWindow({
	              className: 'ui-counter-panel__popup ui-counter-panel__scope',
	              bindElement: item.getArrowDropdown(),
	              autoHide: true,
	              closeByEsc: true,
	              items: itemsArr,
	              angle: true,
	              offsetLeft: 6,
	              offsetTop: 5,
	              animation: 'fading-slide',
	              events: {
	                onPopupShow: () => {
	                  main_core.Dom.addClass(item.getContainer(), '--hover');
	                  main_core.Dom.addClass(item.getContainer(), '--pointer-events-none');
	                },
	                onPopupClose: () => {
	                  main_core.Dom.removeClass(item.getContainer(), '--hover');
	                  main_core.Dom.removeClass(item.getContainer(), '--pointer-events-none');
	                  popup.destroy();
	                }
	              }
	            });
	            popup.show();
	          });
	        }
	      }
	    });
	    main_core.Dom.clean(this.target);
	    main_core.Dom.append(_classPrivateMethodGet$1(this, _getContainer, _getContainer2).call(this), this.target);
	  }
	}

	const CounterItemCollapsedIcon = Object.freeze({
	  CHAT_CHECK: 'chat-chek'
	});

	exports.CounterPanel = CounterPanel;
	exports.CounterItem = CounterItem$$1;
	exports.CounterItemCollapsedIcon = CounterItemCollapsedIcon;

}((this.BX.UI = this.BX.UI || {}),BX.UI,BX,BX.Main,BX,BX.UI,BX.Event));
//# sourceMappingURL=counterpanel.bundle.js.map
