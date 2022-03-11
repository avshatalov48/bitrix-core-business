this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	var _templateObject;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _list = /*#__PURE__*/new WeakMap();

	var _node = /*#__PURE__*/new WeakMap();

	var _sync = /*#__PURE__*/new WeakMap();

	var _addSilent = /*#__PURE__*/new WeakSet();

	var Collection = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Collection, _EventEmitter);

	  function Collection() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Collection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Collection).call(this));

	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _addSilent);

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _list, {
	      writable: true,
	      value: []
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _sync, {
	      writable: true,
	      value: false
	    });

	    _this.setEventNamespace('ui:sidepanel:menu:collection');

	    _this.setItems(options.items);

	    return _this;
	  }

	  babelHelpers.createClass(Collection, [{
	    key: "setActiveFirstItem",
	    value: function setActiveFirstItem() {
	      var item = this.list()[0];

	      if (!item) {
	        return;
	      }

	      item.setActive(true);
	      item.getCollection().setActiveFirstItem();
	    }
	  }, {
	    key: "getActiveItem",
	    value: function getActiveItem() {
	      return this.list().filter(function (item) {
	        return item.isActive();
	      })[0];
	    }
	  }, {
	    key: "syncActive",
	    value: function syncActive(excludeItem) {
	      if (babelHelpers.classPrivateFieldGet(this, _sync)) {
	        return this;
	      }

	      babelHelpers.classPrivateFieldSet(this, _sync, true);
	      this.list().filter(function (otherItem) {
	        return otherItem !== excludeItem;
	      }).forEach(function (otherItem) {
	        otherItem.getCollection().isEmpty() ? otherItem.setActive(false) : otherItem.getCollection().syncActive(otherItem);
	      });
	      this.emit('sync:active');
	      babelHelpers.classPrivateFieldSet(this, _sync, false);
	      return this;
	    }
	  }, {
	    key: "add",
	    value: function add(itemOptions) {
	      var item = _classPrivateMethodGet(this, _addSilent, _addSilent2).call(this, itemOptions);

	      this.emit('change');

	      if (babelHelpers.classPrivateFieldGet(this, _node)) {
	        this.render();
	      }

	      return item;
	    }
	  }, {
	    key: "setItems",
	    value: function setItems() {
	      var _this2 = this;

	      var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      babelHelpers.classPrivateFieldSet(this, _list, items.map(function (itemOptions) {
	        return _classPrivateMethodGet(_this2, _addSilent, _addSilent2).call(_this2, itemOptions);
	      }));
	      this.emit('change');

	      if (babelHelpers.classPrivateFieldGet(this, _node)) {
	        this.render();
	      }

	      return this;
	    }
	  }, {
	    key: "list",
	    value: function list() {
	      return babelHelpers.classPrivateFieldGet(this, _list);
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.list().length === 0;
	    }
	  }, {
	    key: "hasActive",
	    value: function hasActive() {
	      var recursively = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      var has = this.list().some(function (item) {
	        return item.isActive();
	      });

	      if (has) {
	        return true;
	      }

	      if (!recursively) {
	        return false;
	      }

	      return this.list().some(function (item) {
	        return item.getCollection().hasActive();
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this3 = this;

	      if (!babelHelpers.classPrivateFieldGet(this, _node)) {
	        babelHelpers.classPrivateFieldSet(this, _node, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-sidepanel-menu-items\"></div>"]))));
	      }

	      babelHelpers.classPrivateFieldGet(this, _node).innerHTML = '';
	      babelHelpers.classPrivateFieldGet(this, _list).forEach(function (item) {
	        return babelHelpers.classPrivateFieldGet(_this3, _node).appendChild(item.render());
	      });
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }]);
	  return Collection;
	}(main_core_events.EventEmitter);

	function _addSilent2(itemOptions) {
	  var _this4 = this;

	  if (itemOptions.active) {
	    itemOptions.active = !this.hasActive();
	  } else {
	    itemOptions.active = false;
	  }

	  var item = new Item(itemOptions);
	  babelHelpers.classPrivateFieldGet(this, _list).push(item);
	  item.subscribe('change:active', function () {
	    if (item.isActive() && item.getCollection().isEmpty()) {
	      _this4.syncActive(item);
	    }
	  });
	  item.subscribe('sync:active', function () {
	    return _this4.syncActive(item);
	  });
	  item.subscribe('click', function (data) {
	    return _this4.emit('click', data);
	  });
	  item.subscribe('change', function () {
	    return setTimeout(function () {
	      return _this4.render();
	    }, 0);
	  });
	  return item;
	}

	var _templateObject$1, _templateObject2, _templateObject3;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _id = /*#__PURE__*/new WeakMap();

	var _label = /*#__PURE__*/new WeakMap();

	var _active = /*#__PURE__*/new WeakMap();

	var _notice = /*#__PURE__*/new WeakMap();

	var _onclick = /*#__PURE__*/new WeakMap();

	var _collection = /*#__PURE__*/new WeakMap();

	var _node$1 = /*#__PURE__*/new WeakMap();

	var _emitChange = /*#__PURE__*/new WeakSet();

	var _handleClick = /*#__PURE__*/new WeakSet();

	var Item = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Item, _EventEmitter);

	  function Item(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Item);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Item).call(this));

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _handleClick);

	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _emitChange);

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _id, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _label, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _active, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _notice, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _onclick, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _collection, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _node$1, {
	      writable: true,
	      value: void 0
	    });

	    _this.setEventNamespace('ui:sidepanel:menu:item');

	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _collection, new Collection());

	    _this.setLabel(options.label).setActive(options.active).setNotice(options.notice).setId(options.id).setItems(options.items).setClickHandler(options.onclick);

	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _collection).subscribe('sync:active', function () {
	      return _this.emit('sync:active');
	    });
	    babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _collection).subscribe('click', function (event) {
	      return _this.emit('click', event);
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Item, [{
	    key: "setLabel",
	    value: function setLabel() {
	      var label = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

	      if (babelHelpers.classPrivateFieldGet(this, _label) === label) {
	        return this;
	      }

	      babelHelpers.classPrivateFieldSet(this, _label, label);

	      _classPrivateMethodGet$1(this, _emitChange, _emitChange2).call(this);

	      return this;
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      if (babelHelpers.classPrivateFieldGet(this, _id) === id) {
	        return this;
	      }

	      babelHelpers.classPrivateFieldSet(this, _id, id);

	      _classPrivateMethodGet$1(this, _emitChange, _emitChange2).call(this);

	      return this;
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      mode = !!mode;

	      if (babelHelpers.classPrivateFieldGet(this, _active) === mode) {
	        return this;
	      }

	      babelHelpers.classPrivateFieldSet(this, _active, mode);

	      _classPrivateMethodGet$1(this, _emitChange, _emitChange2).call(this, {
	        active: babelHelpers.classPrivateFieldGet(this, _active)
	      }, 'active');

	      return this;
	    }
	  }, {
	    key: "setNotice",
	    value: function setNotice() {
	      var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      babelHelpers.classPrivateFieldSet(this, _notice, !!mode);

	      _classPrivateMethodGet$1(this, _emitChange, _emitChange2).call(this);

	      return this;
	    }
	  }, {
	    key: "setClickHandler",
	    value: function setClickHandler(handler) {
	      babelHelpers.classPrivateFieldSet(this, _onclick, handler);
	      return this;
	    }
	  }, {
	    key: "setItems",
	    value: function setItems() {
	      var items = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      babelHelpers.classPrivateFieldGet(this, _collection).setItems(items || []);

	      _classPrivateMethodGet$1(this, _emitChange, _emitChange2).call(this);

	      return this;
	    }
	  }, {
	    key: "getCollection",
	    value: function getCollection() {
	      return babelHelpers.classPrivateFieldGet(this, _collection);
	    }
	  }, {
	    key: "getLabel",
	    value: function getLabel() {
	      return babelHelpers.classPrivateFieldGet(this, _label);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getClickHandler",
	    value: function getClickHandler() {
	      return babelHelpers.classPrivateFieldGet(this, _onclick);
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return babelHelpers.classPrivateFieldGet(this, _active);
	    }
	  }, {
	    key: "hasNotice",
	    value: function hasNotice() {
	      return babelHelpers.classPrivateFieldGet(this, _notice);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var isEmpty = babelHelpers.classPrivateFieldGet(this, _collection).isEmpty();
	      var classes = [];

	      if (babelHelpers.classPrivateFieldGet(this, _active)) {
	        if (isEmpty) {
	          classes.push('ui-sidepanel-menu-active');
	        } else {
	          classes.push('ui-sidepanel-menu-expand');
	        }
	      }

	      var actionText = main_core.Loc.getMessage('UI_SIDEPANEL_MENU_JS_' + (this.isActive() ? 'COLLAPSE' : 'EXPAND'));
	      babelHelpers.classPrivateFieldSet(this, _node$1, main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<li class=\"ui-sidepanel-menu-item ", "\">\n\t\t\t\t<a\n\t\t\t\t\tclass=\"ui-sidepanel-menu-link\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t<div class=\"ui-sidepanel-menu-link-text\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t</li>\n\t\t"])), classes.join(' '), _classPrivateMethodGet$1(this, _handleClick, _handleClick2).bind(this), main_core.Tag.safe(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["", ""])), babelHelpers.classPrivateFieldGet(this, _label)), main_core.Tag.safe(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["", ""])), babelHelpers.classPrivateFieldGet(this, _label)), !isEmpty ? "<div class=\"ui-sidepanel-toggle-btn\">".concat(actionText, "</div>") : '', babelHelpers.classPrivateFieldGet(this, _notice) ? '<span class="ui-sidepanel-menu-notice-icon"></span>' : ''));

	      if (!babelHelpers.classPrivateFieldGet(this, _collection).isEmpty()) {
	        babelHelpers.classPrivateFieldGet(this, _node$1).appendChild(babelHelpers.classPrivateFieldGet(this, _collection).render());
	      }

	      return babelHelpers.classPrivateFieldGet(this, _node$1);
	    }
	  }]);
	  return Item;
	}(main_core_events.EventEmitter);

	function _emitChange2() {
	  var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	  this.emit('change', data);

	  if (type) {
	    this.emit('change:' + type, data);
	  }
	}

	function _handleClick2(event) {
	  event.preventDefault();
	  event.stopPropagation();
	  this.setActive(babelHelpers.classPrivateFieldGet(this, _collection).isEmpty() || !this.isActive());
	  this.emit('click', {
	    item: this
	  });

	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _onclick))) {
	    babelHelpers.classPrivateFieldGet(this, _onclick).apply(this);
	  }
	}

	var _templateObject$2;

	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _node$2 = /*#__PURE__*/new WeakMap();

	var Menu = /*#__PURE__*/function (_Collection) {
	  babelHelpers.inherits(Menu, _Collection);

	  function Menu() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Menu);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Menu).call(this, {
	      items: options.items
	    }));

	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _node$2, {
	      writable: true,
	      value: void 0
	    });

	    if (!_this.hasActive()) {
	      _this.setActiveFirstItem();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(Menu, [{
	    key: "render",
	    value: function render() {
	      var itemsNode = babelHelpers.get(babelHelpers.getPrototypeOf(Menu.prototype), "render", this).call(this);

	      if (!babelHelpers.classPrivateFieldGet(this, _node$2)) {
	        babelHelpers.classPrivateFieldSet(this, _node$2, main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["<ul class=\"ui-sidepanel-menu\"></ul>"]))));
	        babelHelpers.classPrivateFieldGet(this, _node$2).appendChild(itemsNode);
	      }

	      return babelHelpers.classPrivateFieldGet(this, _node$2);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(target) {
	      var node = this.render();
	      target.appendChild(node);
	      return node;
	    }
	  }]);
	  return Menu;
	}(Collection);

	exports.Item = Item;
	exports.Menu = Menu;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX.Event,BX));
//# sourceMappingURL=bundle.js.map
