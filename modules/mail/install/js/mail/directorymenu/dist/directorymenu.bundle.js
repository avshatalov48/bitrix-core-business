this.BX = this.BX || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	var _templateObject, _templateObject2;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _count = /*#__PURE__*/new WeakMap();

	var _nameOriginal = /*#__PURE__*/new WeakMap();

	var _name = /*#__PURE__*/new WeakMap();

	var _counterElement = /*#__PURE__*/new WeakMap();

	var _itemElement = /*#__PURE__*/new WeakMap();

	var _isActive = /*#__PURE__*/new WeakMap();

	var _path = /*#__PURE__*/new WeakMap();

	var _shiftWidthInPixels = /*#__PURE__*/new WeakMap();

	var _zeroLevelShiftWidth = /*#__PURE__*/new WeakMap();

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
	    var systemDirs = arguments.length > 3 ? arguments[3] : undefined;
	    babelHelpers.classCallCheck(this, Item);

	    _classPrivateFieldInitSpec(this, _count, {
	      writable: true,
	      value: 0
	    });

	    _classPrivateFieldInitSpec(this, _nameOriginal, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _name, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _counterElement, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _itemElement, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _isActive, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _path, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _shiftWidthInPixels, {
	      writable: true,
	      value: 10
	    });

	    _classPrivateFieldInitSpec(this, _zeroLevelShiftWidth, {
	      writable: true,
	      value: 29
	    });

	    babelHelpers.classPrivateFieldSet(this, _path, directory['path']);
	    var iconClass = 'default';

	    if (systemDirs['inbox'] === babelHelpers.classPrivateFieldGet(this, _path)) {
	      iconClass = 'inbox';
	    } else if (systemDirs['spam'] === babelHelpers.classPrivateFieldGet(this, _path)) {
	      iconClass = 'spam';
	    } else if (systemDirs['outcome'] === babelHelpers.classPrivateFieldGet(this, _path)) {
	      iconClass = 'outcome';
	    } else if (systemDirs['trash'] === babelHelpers.classPrivateFieldGet(this, _path)) {
	      iconClass = 'trash';
	    } else if (systemDirs['drafts'] === babelHelpers.classPrivateFieldGet(this, _path)) {
	      iconClass = 'drafts';
	    }

	    babelHelpers.classPrivateFieldSet(this, _nameOriginal, directory['name']);
	    babelHelpers.classPrivateFieldSet(this, _name, babelHelpers.classPrivateFieldGet(this, _nameOriginal).charAt(0).toUpperCase() + babelHelpers.classPrivateFieldGet(this, _nameOriginal).slice(1));
	    var itemContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div title=\"", "\" class=\"mail-menu-directory-item-container\"></div>"])), babelHelpers.classPrivateFieldGet(this, _name));
	    var itemElement = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<li class=\"ui-sidepanel-menu-item ui-sidepanel-menu-counter-white mail-menu-directory-item-", "\">\n\t\t\t\t<a style=\"padding-left: ", "px\" class=\"ui-sidepanel-menu-link\">\n\t\t\t\t\t<div class=\"ui-sidepanel-menu-link-text\">\n\t\t\t\t\t\t<span class=\"ui-sidepanel-menu-link-text-item\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<span class=\"ui-sidepanel-menu-link-text-counter\">", "</span>\n\t\t\t\t</a>\n\t\t\t</li>"])), iconClass, babelHelpers.classPrivateFieldGet(this, _zeroLevelShiftWidth) + babelHelpers.classPrivateFieldGet(this, _shiftWidthInPixels) * nestingLevel, babelHelpers.classPrivateFieldGet(this, _name), directory['count']);
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

	    itemContainer.setIconClass = function (name) {
	      return _this.setIconClass(name);
	    };

	    this.setCount(directory['count']);

	    for (var i = 0; i < directory['items'].length; i++) {
	      if (!Item.checkProperties(directory['items'][i])) {
	        continue;
	      }

	      var subdirectory = new Item(directory['items'][i], menu, nestingLevel + 1, systemDirs);
	      itemContainer.append(subdirectory);
	    }

	    menu.includeItem(itemContainer, babelHelpers.classPrivateFieldGet(this, _path));
	    return itemContainer;
	  }

	  return Item;
	}();

	var _templateObject$1;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _activeDir = /*#__PURE__*/new WeakMap();

	var _menu = /*#__PURE__*/new WeakMap();

	var _directoryCounters = /*#__PURE__*/new WeakMap();

	var _items = /*#__PURE__*/new WeakMap();

	var _systemDirs = /*#__PURE__*/new WeakMap();

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
	      babelHelpers.classPrivateFieldSet(this, _directoryCounters, dirsWithUnseenMailCounters);
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
	      var firstBuild = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      for (var i = 0; i < babelHelpers.classPrivateFieldGet(this, _directoryCounters).length; i++) {
	        var directory = babelHelpers.classPrivateFieldGet(this, _directoryCounters)[i];
	        var path = directory['path'];

	        if (!Item.checkProperties(directory)) {
	          continue;
	        }

	        if (babelHelpers.classPrivateFieldGet(this, _systemDirs)['inbox'] === path && firstBuild) {
	          BX.Mail.Home.FilterToolbar.setCount(directory['count']);
	        }

	        new Item(directory, this, 0, babelHelpers.classPrivateFieldGet(this, _systemDirs));
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
	      name = BX.Mail.Home.Counters.getShortcut(name);
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
	    key: "setDirectory",
	    value: function setDirectory(path) {
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
	      filterId: '',
	      systemDirs: {
	        spam: 'Spam',
	        trash: 'Trash',
	        outcome: 'Outcome',
	        drafts: 'Drafts',
	        inbox: 'Inbox'
	      }
	    };
	    babelHelpers.classCallCheck(this, DirectoryMenu);

	    _classPrivateFieldInitSpec$1(this, _activeDir, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec$1(this, _menu, {
	      writable: true,
	      value: main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<ul class=\"ui-mail-left-directory-menu\"></ul>"])))
	    });

	    _classPrivateFieldInitSpec$1(this, _directoryCounters, {
	      writable: true,
	      value: new Map()
	    });

	    _classPrivateFieldInitSpec$1(this, _items, {
	      writable: true,
	      value: new Map()
	    });

	    _classPrivateFieldInitSpec$1(this, _systemDirs, {
	      writable: true,
	      value: []
	    });

	    this.filter = BX.Main.filterManager.getById(config['filterId']);
	    babelHelpers.classPrivateFieldSet(this, _systemDirs, config['systemDirs']);
	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', function (event) {
	      var dir = BX.Mail.Home.Counters.getDirPath(_this.filter.getFilterFieldsValues()['DIR']);
	      main_core_events.EventEmitter.emit('BX.DirectoryMenu:onChangeFilter', new main_core_events.BaseEvent({
	        data: {
	          directory: dir
	        }
	      }));

	      _this.setDirectory(dir);
	    });
	    babelHelpers.classPrivateFieldSet(this, _directoryCounters, config['dirsWithUnseenMailCounters']);
	    this.buildMenu(true);
	  }

	  babelHelpers.createClass(DirectoryMenu, [{
	    key: "getNode",
	    value: function getNode() {
	      return babelHelpers.classPrivateFieldGet(this, _menu);
	    }
	  }]);
	  return DirectoryMenu;
	}();

	exports.DirectoryMenu = DirectoryMenu;

}((this.BX.Mail = this.BX.Mail || {}),BX.Event,BX));
//# sourceMappingURL=directorymenu.bundle.js.map
