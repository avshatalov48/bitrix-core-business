this.BX = this.BX || {};
(function (exports,main_core,ui_cnt,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _bindEvents = /*#__PURE__*/new WeakSet();

	var _getPanel = /*#__PURE__*/new WeakSet();

	var _getCounter = /*#__PURE__*/new WeakSet();

	var _getValue = /*#__PURE__*/new WeakSet();

	var _getTitle = /*#__PURE__*/new WeakSet();

	var _getCross = /*#__PURE__*/new WeakSet();

	var _setEvents = /*#__PURE__*/new WeakSet();

	var CounterItem = /*#__PURE__*/function () {
	  function CounterItem(options) {
	    babelHelpers.classCallCheck(this, CounterItem);

	    _classPrivateMethodInitSpec(this, _setEvents);

	    _classPrivateMethodInitSpec(this, _getCross);

	    _classPrivateMethodInitSpec(this, _getTitle);

	    _classPrivateMethodInitSpec(this, _getValue);

	    _classPrivateMethodInitSpec(this, _getCounter);

	    _classPrivateMethodInitSpec(this, _getPanel);

	    _classPrivateMethodInitSpec(this, _bindEvents);

	    this.id = options.id;
	    this.title = main_core.Type.isString(options.title) ? options.title : null;
	    this.value = main_core.Type.isNumber(options.value) ? options.value : null;
	    this.color = main_core.Type.isString(options.color) ? options.color : null;
	    this.eventsForActive = main_core.Type.isObject(options.eventsForActive) ? options.eventsForActive : null;
	    this.eventsForUnActive = main_core.Type.isObject(options.eventsForUnActive) ? options.eventsForUnActive : null;
	    this.panel = options.panel ? options.panel : null;
	    this.layout = {
	      container: null,
	      value: null,
	      title: null,
	      cross: null
	    };
	    this.counter = null;
	    this.isActive = false;

	    if (!_classPrivateMethodGet(this, _getPanel, _getPanel2).call(this).isMultiselect()) {
	      _classPrivateMethodGet(this, _bindEvents, _bindEvents2).call(this);
	    }
	  }

	  babelHelpers.createClass(CounterItem, [{
	    key: "updateValue",
	    value: function updateValue(param) {
	      if (main_core.Type.isNumber(param)) {
	        this.value = param;

	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).update(param);

	        _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).show();

	        if (param === 0) {
	          this.updateColor('THEME');
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
	      this.getContainer().classList.add('--active');

	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:activate', this);
	      }
	    }
	  }, {
	    key: "deactivate",
	    value: function deactivate() {
	      var isEmitEvent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.isActive = false;
	      this.getContainer().classList.remove('--active');
	      this.getContainer().classList.remove('--hover');

	      if (isEmitEvent) {
	        main_core_events.EventEmitter.emit('BX.UI.CounterPanel.Item:deactivate', this);
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var _this = this;

	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _classPrivateMethodGet(this, _getValue, _getValue2).call(this), this.title ? _classPrivateMethodGet(this, _getTitle, _getTitle2).call(this) : '', _classPrivateMethodGet(this, _getCross, _getCross2).call(this));

	        _classPrivateMethodGet(this, _setEvents, _setEvents2).call(this);

	        this.layout.container.addEventListener('mouseenter', function () {
	          if (!_this.isActive) {
	            _this.layout.container.classList.add('--hover');
	          }
	        });
	        this.layout.container.addEventListener('mouseleave', function () {
	          if (!_this.isActive) {
	            _this.layout.container.classList.remove('--hover');
	          }
	        });
	        this.layout.container.addEventListener('click', function () {
	          _this.isActive ? _this.deactivate() : _this.activate();
	        });
	      }

	      return this.layout.container;
	    }
	  }]);
	  return CounterItem;
	}();

	function _bindEvents2() {
	  var _this2 = this;

	  main_core_events.EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', function (item) {
	    if (item.data !== _this2) {
	      _this2.deactivate();
	    }
	  });
	}

	function _getPanel2() {
	  return this.panel;
	}

	function _getCounter2() {
	  if (!this.counter) {
	    this.counter = new ui_cnt.Counter({
	      value: this.value ? this.value : 0,
	      color: this.color ? ui_cnt.Counter.Color[this.color] : ui_cnt.Counter.Color.THEME,
	      animation: true
	    });
	  }

	  return this.counter;
	}

	function _getValue2() {
	  if (!this.layout.value) {
	    this.layout.value = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-value\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _classPrivateMethodGet(this, _getCounter, _getCounter2).call(this).getContainer());
	  }

	  return this.layout.value;
	}

	function _getTitle2() {
	  if (!this.layout.title) {
	    this.layout.title = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-title\">", "</div>\n\t\t\t"])), this.title);
	  }

	  return this.layout.title;
	}

	function _getCross2() {
	  if (!this.layout.cross) {
	    this.layout.cross = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel__item-cross\">\n\t\t\t\t\t<div class=\"ui-counter-panel__item-cross--icon\"></div>\n\t\t\t\t</div>\n\t\t\t"])));
	  }

	  return this.layout.cross;
	}

	function _setEvents2() {
	  var _this3 = this;

	  if (this.eventsForActive) {
	    var eventKeys = Object.keys(this.eventsForActive);

	    var _loop = function _loop(i) {
	      var event = eventKeys[i];

	      _this3.getContainer().addEventListener(event, function () {
	        if (_this3.isActive) {
	          _this3.eventsForActive[event]();
	        }
	      });
	    };

	    for (var i = 0; i < eventKeys.length; i++) {
	      _loop(i);
	    }
	  }

	  if (this.eventsForUnActive) {
	    var _eventKeys = Object.keys(this.eventsForUnActive);

	    var _loop2 = function _loop2(_i) {
	      var event = _eventKeys[_i];

	      _this3.getContainer().addEventListener(event, function () {
	        if (!_this3.isActive) {
	          _this3.eventsForUnActive[event]();
	        }
	      });
	    };

	    for (var _i = 0; _i < _eventKeys.length; _i++) {
	      _loop2(_i);
	    }
	  }
	}

	var _templateObject$1, _templateObject2$1;

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
	    this.container = null;
	    this.keys = [];
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
	    _this.keys.push(item.id);

	    return new CounterItem({
	      id: item.id ? item.id : null,
	      title: item.title ? item.title : null,
	      value: item.value ? parseInt(item.value, 10) : null,
	      cross: item.cross ? item.cross : null,
	      color: item.color ? item.color : null,
	      eventsForActive: item.eventsForActive ? item.eventsForActive : null,
	      eventsForUnActive: item.eventsForUnActive ? item.eventsForUnActive : null,
	      panel: _this
	    });
	  });
	  this.getItemById();
	}

	function _getContainer2() {
	  if (!this.container) {
	    this.container = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-panel ui-counter-panel__scope\"></div>\n\t\t\t"])));
	  }

	  return this.container;
	}

	function _render2() {
	  var _this2 = this;

	  if (this.target && this.items.length > 0) {
	    this.items.map(function (item, key) {
	      if (item instanceof CounterItem) {
	        _classPrivateMethodGet$1(_this2, _getContainer, _getContainer2).call(_this2).appendChild(item.getContainer());

	        if (_this2.items.length !== key + 1 && _this2.items.length > 1) {
	          _classPrivateMethodGet$1(_this2, _getContainer, _getContainer2).call(_this2).appendChild(main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class=\"ui-counter-panel__item-separator\"></div>\n\t\t\t\t\t\t"]))));
	        }
	      }
	    });
	    main_core.Dom.clean(this.target);
	    this.target.appendChild(_classPrivateMethodGet$1(this, _getContainer, _getContainer2).call(this));
	  }
	}

	exports.CounterPanel = CounterPanel;
	exports.CounterItem = CounterItem;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI,BX.Event));
//# sourceMappingURL=counterpanel.bundle.js.map
