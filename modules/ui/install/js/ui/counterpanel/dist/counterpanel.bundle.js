this.BX = this.BX || {};
(function (exports,main_popup,main_core,ui_cnt,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _bindEvents = /*#__PURE__*/new WeakSet();
	var _getPanel = /*#__PURE__*/new WeakSet();
	var _getCounter = /*#__PURE__*/new WeakSet();
	var _getValue = /*#__PURE__*/new WeakSet();
	var _getTitle = /*#__PURE__*/new WeakSet();
	var _getCross = /*#__PURE__*/new WeakSet();
	var CounterItem = /*#__PURE__*/function () {
	  function CounterItem(args) {
	    babelHelpers.classCallCheck(this, CounterItem);
	    _classPrivateMethodInitSpec(this, _getCross);
	    _classPrivateMethodInitSpec(this, _getTitle);
	    _classPrivateMethodInitSpec(this, _getValue);
	    _classPrivateMethodInitSpec(this, _getCounter);
	    _classPrivateMethodInitSpec(this, _getPanel);
	    _classPrivateMethodInitSpec(this, _bindEvents);
	    this.id = args.id ? args.id : null;
	    this.separator = main_core.Type.isBoolean(args.separator) ? args.separator : true;
	    this.items = main_core.Type.isArray(args.items) ? args.items : [];
	    this.popupMenu = null;
	    this.isActive = main_core.Type.isBoolean(args.isActive) ? args.isActive : false;
	    this.isRestricted = main_core.Type.isBoolean(args.isRestricted) ? args.isRestricted : false;
	    this.panel = args.panel ? args.panel : null;
	    this.title = args.title ? args.title : null;
	    this.value = main_core.Type.isNumber(args.value) && args.value !== undefined ? args.value : null;
	    this.titleOrder = null;
	    this.valueOrder = null;
	    this.color = args.color ? args.color : null;
	    this.parent = main_core.Type.isBoolean(args.parent) ? args.parent : null;
	    this.parentId = args.parentId ? args.parentId : null;
	    this.locked = false;
	    this.type = main_core.Type.isString(args.type) ? args.type.toLowerCase() : null;
	    this.eventsForActive = main_core.Type.isObject(args.eventsForActive) ? args.eventsForActive : {};
	    this.eventsForUnActive = main_core.Type.isObject(args.eventsForUnActive) ? args.eventsForUnActive : {};
	    if (main_core.Type.isObject(args.title)) {
	      this.title = args.title.value ? args.title.value : null;
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
	  babelHelpers.createClass(CounterItem, [{
	    key: "getItems",
	    value: function getItems() {
	      return this.items;
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
	          this.updateColor(this.parentId ? 'GRAY' : 'THEME');
	        }
	      }
	    }
	  }, {
	    key: "updateColor",
	    value: function updateColor(param) {
	      if (main_core.Type.isString(param)) {
	        this.color = param;
	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).setColor(ui_cnt.Counter.Color[param]);
	      }
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      var isEmitEvent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.isActive = true;
	      if (this.parentId) {
	        var target = BX.findParent(this.getContainerMenu(), {
	          'className': 'ui-counter-panel__popup-item'
	        });
	        if (target) {
	          target.classList.add('--active');
	        }
	      } else {
	        this.getContainer().classList.add('--active');
	      }
	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:activate', this);
	      }
	    }
	  }, {
	    key: "deactivate",
	    value: function deactivate() {
	      var isEmitEvent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.isActive = false;
	      if (this.parentId) {
	        var target = BX.findParent(this.getContainerMenu(), {
	          'className': 'ui-counter-panel__popup-item'
	        });
	        if (target) {
	          target.classList.remove('--active');
	          target.classList.remove('--hover');
	        }
	      } else {
	        this.getContainer().classList.remove('--active');
	        this.getContainer().classList.remove('--hover');
	      }
	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
	      }
	    }
	  }, {
	    key: "getSeparator",
	    value: function getSeparator() {
	      return this.separator;
	    }
	  }, {
	    key: "setEvents",
	    value: function setEvents(container) {
	      var _this = this;
	      if (!container) {
	        container = this.getContainer();
	      }
	      if (this.eventsForActive) {
	        var eventKeys = Object.keys(this.eventsForActive);
	        var _loop = function _loop() {
	          var event = eventKeys[i];
	          container.addEventListener(event, function () {
	            if (_this.isActive) {
	              _this.eventsForActive[event]();
	            }
	          });
	        };
	        for (var i = 0; i < eventKeys.length; i++) {
	          _loop();
	        }
	      }
	      if (this.eventsForUnActive) {
	        var _eventKeys = Object.keys(this.eventsForUnActive);
	        var _loop2 = function _loop2() {
	          var event = _eventKeys[_i];
	          container.addEventListener(event, function () {
	            if (!_this.isActive) {
	              _this.eventsForUnActive[event]();
	            }
	          });
	        };
	        for (var _i = 0; _i < _eventKeys.length; _i++) {
	          _loop2();
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
	      this.getContainer().classList.add('--locked');
	    }
	  }, {
	    key: "unLock",
	    value: function unLock() {
	      this.locked = false;
	      this.getContainer().classList.remove('--locked');
	    }
	  }, {
	    key: "getArrowDropdown",
	    value: function getArrowDropdown() {
	      if (!this.layout.dropdownArrow) {
	        this.layout.dropdownArrow = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-dropdown\">\n\t\t\t\t\t<i></i>\n\t\t\t\t</div>\n\t\t\t"])));
	      }
	      return this.layout.dropdownArrow;
	    }
	  }, {
	    key: "getContainerMenu",
	    value: function getContainerMenu() {
	      if (!this.layout.menuItem) {
	        this.layout.menuItem = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t"])), _classPrivateMethodGet(this, _getValue, _getValue2).call(this), this.title, _classPrivateMethodGet(this, _getCross, _getCross2).call(this));
	      }
	      return this.layout.menuItem;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this2 = this;
	      if (!this.layout.container) {
	        var type = this.type ? "id=\"ui-counter-panel-item-".concat(this.type, "\"") : '';
	        var isValue = main_core.Type.isNumber(this.value);
	        this.layout.container = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div ", " class=\"ui-counter-panel__item\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), type, isValue ? _classPrivateMethodGet(this, _getValue, _getValue2).call(this) : '', this.title ? _classPrivateMethodGet(this, _getTitle, _getTitle2).call(this) : '', isValue ? _classPrivateMethodGet(this, _getCross, _getCross2).call(this) : '');
	        if (this.parent) {
	          this.layout.container = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-counter-panel__item\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), this.title ? _classPrivateMethodGet(this, _getTitle, _getTitle2).call(this) : '', isValue ? _classPrivateMethodGet(this, _getValue, _getValue2).call(this) : '', _classPrivateMethodGet(this, _getCross, _getCross2).call(this));
	          _classPrivateMethodGet(this, _getCross, _getCross2).call(this).addEventListener('click', function (ev) {
	            _this2.deactivate();
	            ev.stopPropagation();
	          });
	          main_core.Dom.addClass(this.layout.container, '--dropdown');
	        }
	        if (!isValue) {
	          this.layout.container.classList.add('--string');
	        }
	        if (!isValue && !this.eventsForActive && !this.eventsForUnActive) {
	          this.layout.container.classList.add('--title');
	        }
	        if (!this.separator) {
	          this.layout.container.classList.add('--without-separator');
	        }
	        if (this.locked) {
	          this.layout.container.classList.add('--locked');
	        }
	        if (this.isActive) {
	          this.activate();
	        }
	        if (this.isRestricted) {
	          this.layout.container.classList.add('--restricted');
	        }
	        this.setEvents(this.layout.container);
	        if (isValue && this.items.length === 0) {
	          if (!this.parent) {
	            this.layout.container.addEventListener('mouseenter', function () {
	              if (!_this2.isActive) {
	                _this2.layout.container.classList.add('--hover');
	              }
	            });
	            this.layout.container.addEventListener('mouseleave', function () {
	              if (!_this2.isActive) {
	                _this2.layout.container.classList.remove('--hover');
	              }
	            });
	            this.layout.container.addEventListener('click', function () {
	              _this2.isActive ? _this2.deactivate() : _this2.activate();
	            });
	          }
	        }
	        if (this.parent) {
	          main_core.Dom.append(this.getArrowDropdown(), this.layout.container);
	        }
	      }
	      return this.layout.container;
	    }
	  }]);
	  return CounterItem;
	}();
	function _bindEvents2() {
	  var _this3 = this;
	  main_core_events.EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', function (item) {
	    var isLinkedItems = item.data.parentId === _this3.id;
	    if (item.data !== _this3 && !isLinkedItems) {
	      _this3.deactivate();
	    }
	  });
	}
	function _getPanel2() {
	  return this.panel;
	}
	function _getCounter2(value, color) {
	  if (!this.counter) {
	    this.counter = new ui_cnt.Counter({
	      value: this.value,
	      color: this.color ? ui_cnt.Counter.Color[this.color.toUpperCase()] : this.parentId ? ui_cnt.Counter.Color.GRAY : ui_cnt.Counter.Color.THEME,
	      animation: false
	    });
	  }
	  return this.counter;
	}
	function _getValue2() {
	  if (!this.layout.value) {
	    var counterValue = this.isRestricted ? main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-counter-panel__item-lock\"></div>"]))) : _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).getContainer();
	    this.layout.value = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-value\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), counterValue);
	    this.layout.value.style.setProperty('order', this.valueOrder);
	  }
	  return this.layout.value;
	}
	function _getTitle2() {
	  if (!this.layout.title) {
	    this.layout.title = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-title\">", "</div>\n\t\t\t"])), this.title);
	    this.layout.title.style.setProperty('order', this.titleOrder);
	  }
	  return this.layout.title;
	}
	function _getCross2() {
	  if (!this.layout.cross) {
	    this.layout.cross = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-cross\">\n\t\t\t\t\t<i></i>\n\t\t\t\t</div>\n\t\t\t"])));
	  }
	  return this.layout.cross;
	}

	var _templateObject$1, _templateObject2$1, _templateObject3$1;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _adjustData = /*#__PURE__*/new WeakSet();
	var _getContainer = /*#__PURE__*/new WeakSet();
	var _render = /*#__PURE__*/new WeakSet();
	var CounterPanel = /*#__PURE__*/function () {
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
	    this.childKeys = [];
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
	        var index = this.keys.indexOf(param);
	        return this.items[index];
	      }
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      _classPrivateMethodGet$1(this, _adjustData, _adjustData2).call(this);
	      _classPrivateMethodGet$1(this, _render, _render2).call(this);
	    }
	  }]);
	  return CounterPanel;
	}();
	function _adjustData2() {
	  var _this = this;
	  this.items = this.items.map(function (item) {
	    item.panel = _this;
	    _this.keys.push(item.id);
	    if (item.parentId) {
	      _this.hasParent.push(item.parentId);
	    }
	    return new CounterItem(item);
	  });
	  this.hasParent.forEach(function (item) {
	    var index = _this.keys.indexOf(item);
	    _this.items[index].parent = true;
	  });
	  this.items.map(function (item) {
	    if (item.parentId) {
	      var index = _this.keys.indexOf(item.parentId);
	      _this.items[index].items.push(item.id);
	    }
	  });
	}
	function _getContainer2() {
	  if (!this.container) {
	    var myHead = '';
	    if (this.title) {
	      myHead = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"ui-counter-panel__item-head\">", "</div>\n\t\t\t\t"])), this.title);
	    }
	    this.container = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel ui-counter-panel__scope\">", "</div>\n\t\t\t"])), myHead);
	  }
	  return this.container;
	}
	function _render2() {
	  var _this2 = this;
	  if (this.target && this.items.length > 0) {
	    this.items.map(function (item, key) {
	      if (item instanceof CounterItem) {
	        if (!item.hasParentId()) {
	          _classPrivateMethodGet$1(_this2, _getContainer, _getContainer2).call(_this2).appendChild(item.getContainer());
	          if (_this2.items.length !== key + 1 && _this2.items.length > 1) {
	            _classPrivateMethodGet$1(_this2, _getContainer, _getContainer2).call(_this2).appendChild(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t<div class=\"ui-counter-panel__item-separator ", "\"></div>\n\t\t\t\t\t\t\t"])), !item.getSeparator() ? '--invisible' : ''));
	          }
	        }
	        if (item.parent) {
	          item.getContainer().addEventListener('click', function () {
	            var itemsArr = [];
	            item.getItems().forEach(function (item) {
	              var itemCounter = _this2.getItemById(item);
	              var test = {
	                html: itemCounter.getContainerMenu(),
	                className: "ui-counter-panel__popup-item menu-popup-no-icon ".concat(itemCounter.isActive ? '--active' : ''),
	                onclick: function onclick() {
	                  itemCounter.isActive ? itemCounter.deactivate() : itemCounter.activate();
	                }
	              };
	              itemsArr.push(test);
	            });
	            var popup = new main_popup.PopupMenuWindow({
	              className: 'ui-counter-panel__popup ui-counter-panel__scope',
	              bindElement: item.getArrowDropdown(),
	              autoHide: true,
	              closeByEsc: true,
	              items: itemsArr,
	              angle: true,
	              offsetLeft: 6,
	              offsetTop: -7,
	              animation: 'fading-slide',
	              events: {
	                onPopupShow: function onPopupShow() {
	                  item.getContainer().classList.add('--hover');
	                  item.getContainer().classList.add('--pointer-events-none');
	                },
	                onPopupClose: function onPopupClose() {
	                  item.getContainer().classList.remove('--hover');
	                  item.getContainer().classList.remove('--pointer-events-none');
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
	    this.target.appendChild(_classPrivateMethodGet$1(this, _getContainer, _getContainer2).call(this));
	  }
	}

	exports.CounterPanel = CounterPanel;
	exports.CounterItem = CounterItem;

}((this.BX.UI = this.BX.UI || {}),BX.Main,BX,BX.UI,BX.Event));
//# sourceMappingURL=counterpanel.bundle.js.map
