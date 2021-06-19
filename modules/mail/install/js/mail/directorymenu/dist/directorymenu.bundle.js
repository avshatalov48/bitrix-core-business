this.BX = this.BX || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<li class=\"ui-sidepanel-menu-item ui-sidepanel-menu-counter-white\">\n\t\t\t\t<a style=\"padding-left: ", "px\" class=\"ui-sidepanel-menu-link\">\n\t\t\t\t\t<div class=\"ui-sidepanel-menu-link-text\">\n\t\t\t\t\t\t<span class=\"ui-sidepanel-menu-link-text-item\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<span class=\"ui-sidepanel-menu-link-text-counter\">", "</span>\n\t\t\t\t</a>\n\t\t\t</li>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-menu-directory-item-container\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Item = /*#__PURE__*/function () {
	  babelHelpers.createClass(Item, [{
	    key: "setCount",
	    value: function setCount(number) {
	      babelHelpers.classPrivateFieldSet(this, _count, number);
	      babelHelpers.classPrivateFieldGet(this, _counterElement).textContent = number;

	      if (number === 0) {
	        babelHelpers.classPrivateFieldGet(this, _counterElement).classList.add('ui-sidepanel-menu-link-text-counter-hidden');
	      } else {
	        babelHelpers.classPrivateFieldGet(this, _counterElement).classList.remove('ui-sidepanel-menu-link-text-counter-hidden');
	      }
	    }
	  }, {
	    key: "getCount",
	    value: function getCount() {
	      return Number(babelHelpers.classPrivateFieldGet(this, _count));
	    }
	  }, {
	    key: "disableActivity",
	    value: function disableActivity() {
	      babelHelpers.classPrivateFieldSet(this, _isActive, false);
	      babelHelpers.classPrivateFieldGet(this, _itemElement).classList.remove('ui-sidepanel-menu-active');
	    }
	  }, {
	    key: "getPath",
	    value: function getPath() {
	      return babelHelpers.classPrivateFieldGet(this, _path);
	    }
	  }, {
	    key: "enableActivity",
	    value: function enableActivity() {
	      babelHelpers.classPrivateFieldSet(this, _isActive, true);
	      babelHelpers.classPrivateFieldGet(this, _itemElement).classList.add('ui-sidepanel-menu-active');
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return babelHelpers.classPrivateFieldGet(this, _isActive);
	    }
	    /**
	     * So as not to break the menu with incorrectly synchronized directories.
	     *
	     * @param directory (directory structure).
	     * @returns {boolean}
	     */

	  }], [{
	    key: "checkProperties",
	    value: function checkProperties(directory) {
	      if (directory['path'] === undefined || directory['name'] === undefined || directory['name'] === undefined) {
	        return false;
	      }

	      return true;
	    }
	  }]);

	  function Item(directory, menu) {
	    var _this = this;

	    var nestingLevel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	    babelHelpers.classCallCheck(this, Item);

	    _count.set(this, {
	      writable: true,
	      value: 0
	    });

	    _nameOriginal.set(this, {
	      writable: true,
	      value: ''
	    });

	    _name.set(this, {
	      writable: true,
	      value: ''
	    });

	    _counterElement.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _itemElement.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _isActive.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _path.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _shiftWidthInPixels.set(this, {
	      writable: true,
	      value: 10
	    });

	    _zeroLevelShiftWidth.set(this, {
	      writable: true,
	      value: 29
	    });

	    babelHelpers.classPrivateFieldSet(this, _path, directory['path']);
	    babelHelpers.classPrivateFieldSet(this, _nameOriginal, directory['name']);
	    babelHelpers.classPrivateFieldSet(this, _name, babelHelpers.classPrivateFieldGet(this, _nameOriginal).charAt(0).toUpperCase() + babelHelpers.classPrivateFieldGet(this, _nameOriginal).slice(1));
	    var itemContainer = main_core.Tag.render(_templateObject());
	    var itemElement = main_core.Tag.render(_templateObject2(), babelHelpers.classPrivateFieldGet(this, _zeroLevelShiftWidth) + babelHelpers.classPrivateFieldGet(this, _shiftWidthInPixels) * nestingLevel, babelHelpers.classPrivateFieldGet(this, _name), directory['count']);
	    itemContainer.append(itemElement);

	    itemElement.onclick = function () {
	      if (!itemContainer.isActive()) {
	        menu.chooseFunction(directory['path']);
	        itemContainer.enableActivity();
	      }
	    };

	    var counterElement = itemElement.querySelector(".ui-sidepanel-menu-link-text-counter");
	    babelHelpers.classPrivateFieldSet(this, _counterElement, counterElement);
	    babelHelpers.classPrivateFieldSet(this, _itemElement, itemElement);

	    itemContainer.getCount = function () {
	      return _this.getCount();
	    };

	    itemContainer.setCount = function (number) {
	      return _this.setCount(number);
	    };

	    itemContainer.enableActivity = function () {
	      return _this.enableActivity();
	    };

	    itemContainer.disableActivity = function () {
	      return _this.disableActivity();
	    };

	    itemContainer.isActive = function () {
	      return _this.isActive();
	    };

	    this.setCount(directory['count']);

	    for (var i = 0; i < directory['items'].length; i++) {
	      if (!Item.checkProperties(directory['items'][i])) {
	        continue;
	      }

	      var subdirectory = new Item(directory['items'][i], menu, nestingLevel + 1);
	      itemContainer.append(subdirectory);
	    }

	    menu.includeItem(itemContainer, babelHelpers.classPrivateFieldGet(this, _path));
	    return itemContainer;
	  }

	  return Item;
	}();

	var _count = new WeakMap();

	var _nameOriginal = new WeakMap();

	var _name = new WeakMap();

	var _counterElement = new WeakMap();

	var _itemElement = new WeakMap();

	var _isActive = new WeakMap();

	var _path = new WeakMap();

	var _shiftWidthInPixels = new WeakMap();

	var _zeroLevelShiftWidth = new WeakMap();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<ul class=\"ui-mail-left-directory-menu\"></ul>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var DirectoryMenu = /*#__PURE__*/function () {
	  babelHelpers.createClass(DirectoryMenu, [{
	    key: "getActiveDir",
	    value: function getActiveDir() {
	      return babelHelpers.classPrivateFieldGet(this, _activeDir);
	    }
	  }, {
	    key: "setActiveDir",
	    value: function setActiveDir(path) {
	      babelHelpers.classPrivateFieldSet(this, _activeDir, path);
	    }
	  }, {
	    key: "clearActiveMenuButtons",
	    value: function clearActiveMenuButtons() {
	      var _iterator = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _items).values()),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var item = _step.value;
	          item.disableActivity();
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "rebuildMenu",
	    value: function rebuildMenu(dirsWithUnseenMailCounters) {
	      babelHelpers.classPrivateFieldSet(this, _dirsWithUnseenMailCounters, dirsWithUnseenMailCounters);
	      this.cleanItems();
	      this.buildMenu();
	      this.setDirectory(this.getActiveDir());
	    }
	  }, {
	    key: "cleanItems",
	    value: function cleanItems() {
	      var _iterator2 = _createForOfIteratorHelper(babelHelpers.classPrivateFieldGet(this, _items).values()),
	          _step2;

	      try {
	        for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	          var item = _step2.value;
	          babelHelpers.classPrivateFieldGet(this, _menu).removeChild(item);
	        }
	      } catch (err) {
	        _iterator2.e(err);
	      } finally {
	        _iterator2.f();
	      }

	      babelHelpers.classPrivateFieldGet(this, _items).clear();
	    }
	  }, {
	    key: "includeItem",
	    value: function includeItem(domItem, directoryPath) {
	      babelHelpers.classPrivateFieldGet(this, _items).set(directoryPath, domItem);
	      babelHelpers.classPrivateFieldGet(this, _menu).append(domItem);
	    }
	  }, {
	    key: "chooseFunction",
	    value: function chooseFunction(path) {
	      this.clearActiveMenuButtons();
	      this.setActiveDir(path);
	      this.setFilterDir(path);
	    }
	  }, {
	    key: "buildMenu",
	    value: function buildMenu() {
	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _dirsWithUnseenMailCounters).length; i++) {
	        var directory = babelHelpers.classPrivateFieldGet(this, _dirsWithUnseenMailCounters)[i];
	        var path = directory['path'];

	        if (!Item.checkProperties(directory)) {
	          continue;
	        }

	        var itemElement = new Item(directory, this);
	      }
	    }
	  }, {
	    key: "setFilterDir",
	    value: function setFilterDir(name) {
	      var event = new main_core_events.BaseEvent({
	        data: {
	          directory: name
	        }
	      });
	      main_core_events.EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', event);
	      name = this.convertPathForFilter(name);
	      var filter = this.filter;

	      if (!!filter && filter instanceof BX.Main.Filter) {
	        var FilterApi = filter.getApi();
	        FilterApi.setFields({
	          'DIR': name
	        });
	        FilterApi.apply();
	      }
	    }
	  }, {
	    key: "changeCounter",
	    value: function changeCounter(dirPath, number, mode) {
	      var item = babelHelpers.classPrivateFieldGet(this, _items).get(dirPath);
	      if (item === undefined) return;

	      if (mode !== 'set') {
	        item.setCount(item.getCount() + Number(number));
	      } else {
	        item.setCount(Number(number));
	      }
	    }
	  }, {
	    key: "setCounters",
	    value: function setCounters(counters) {
	      for (var path in counters) {
	        if (counters.hasOwnProperty(path)) {
	          this.changeCounter(path, counters[path], 'set');
	        }
	      }
	    }
	  }, {
	    key: "convertPathForFilter",
	    value: function convertPathForFilter(path) {
	      if (path === 'INBOX' || path === undefined) {
	        path = '';
	      }

	      return path;
	    }
	  }, {
	    key: "convertPathForMenu",
	    value: function convertPathForMenu(path) {
	      if (path === '' || path === undefined) {
	        path = 'INBOX';
	      }

	      return path;
	    }
	  }, {
	    key: "setDirectory",
	    value: function setDirectory(path) {
	      path = this.convertPathForMenu(path);
	      this.clearActiveMenuButtons();
	      if (path === undefined) return;
	      var item = babelHelpers.classPrivateFieldGet(this, _items).get(path);

	      if (item) {
	        this.setActiveDir(path);
	        item.enableActivity();
	      }
	    }
	  }]);

	  function DirectoryMenu() {
	    var _this = this;

	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      dirsWithUnseenMailCounters: {},
	      filterId: ''
	    };
	    babelHelpers.classCallCheck(this, DirectoryMenu);

	    _activeDir.set(this, {
	      writable: true,
	      value: ''
	    });

	    _menu.set(this, {
	      writable: true,
	      value: main_core.Tag.render(_templateObject$1())
	    });

	    _dirsWithUnseenMailCounters.set(this, {
	      writable: true,
	      value: new Map()
	    });

	    _items.set(this, {
	      writable: true,
	      value: new Map()
	    });

	    this.filter = BX.Main.filterManager.getById(config['filterId']);
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', function (event) {
	      var dir = _this.filter.getFilterFieldsValues()['DIR'];

	      dir = _this.convertPathForMenu(dir);
	      main_core_events.EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', new main_core_events.BaseEvent({
	        data: {
	          directory: dir
	        }
	      }));

	      _this.setDirectory(dir);
	    });
	    babelHelpers.classPrivateFieldSet(this, _dirsWithUnseenMailCounters, config['dirsWithUnseenMailCounters']);
	    this.buildMenu();
	  }

	  babelHelpers.createClass(DirectoryMenu, [{
	    key: "getNode",
	    value: function getNode() {
	      return babelHelpers.classPrivateFieldGet(this, _menu);
	    }
	  }]);
	  return DirectoryMenu;
	}();

	var _activeDir = new WeakMap();

	var _menu = new WeakMap();

	var _dirsWithUnseenMailCounters = new WeakMap();

	var _items = new WeakMap();

	exports.DirectoryMenu = DirectoryMenu;

}((this.BX.Mail = this.BX.Mail || {}),BX.Event,BX));
//# sourceMappingURL=directorymenu.bundle.js.map
