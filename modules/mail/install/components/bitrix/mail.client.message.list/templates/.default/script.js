(function (exports,mail_avatar,mail_messagegrid,mail_directorymenu,main_core_events,main_core,ui_buttons) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _node = /*#__PURE__*/new WeakMap();
	var _errorTitleNode = /*#__PURE__*/new WeakMap();
	var _errorTextNode = /*#__PURE__*/new WeakMap();
	var _errorBoxNode = /*#__PURE__*/new WeakMap();
	var _syncButton = /*#__PURE__*/new WeakMap();
	var _errorHintNode = /*#__PURE__*/new WeakMap();
	var ProgressBar = /*#__PURE__*/function () {
	  function ProgressBar(node) {
	    babelHelpers.classCallCheck(this, ProgressBar);
	    _classPrivateFieldInitSpec(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _errorTitleNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _errorTextNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _errorBoxNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _syncButton, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _errorHintNode, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _node, node);
	  }
	  babelHelpers.createClass(ProgressBar, [{
	    key: "setSyncButton",
	    value: function setSyncButton(button) {
	      babelHelpers.classPrivateFieldSet(this, _syncButton, button);
	    }
	  }, {
	    key: "getSyncButton",
	    value: function getSyncButton() {
	      return babelHelpers.classPrivateFieldGet(this, _syncButton);
	    }
	  }, {
	    key: "getErrorBoxNode",
	    value: function getErrorBoxNode() {
	      return babelHelpers.classPrivateFieldGet(this, _errorBoxNode);
	    }
	  }, {
	    key: "setErrorBoxNode",
	    value: function setErrorBoxNode(errorBoxNode) {
	      babelHelpers.classPrivateFieldSet(this, _errorBoxNode, errorBoxNode);
	    }
	  }, {
	    key: "setErrorTitleNode",
	    value: function setErrorTitleNode(errorTitleNode) {
	      babelHelpers.classPrivateFieldSet(this, _errorTitleNode, errorTitleNode);
	    }
	  }, {
	    key: "setErrorTextNode",
	    value: function setErrorTextNode(errorTextNode) {
	      babelHelpers.classPrivateFieldSet(this, _errorTextNode, errorTextNode);
	    }
	  }, {
	    key: "setErrorHintNode",
	    value: function setErrorHintNode(errorHintNode) {
	      babelHelpers.classPrivateFieldSet(this, _errorHintNode, errorHintNode);
	    }
	  }, {
	    key: "getErrorTextNode",
	    value: function getErrorTextNode() {
	      return babelHelpers.classPrivateFieldGet(this, _errorTextNode);
	    }
	  }, {
	    key: "getErrorHintNode",
	    value: function getErrorHintNode() {
	      return babelHelpers.classPrivateFieldGet(this, _errorHintNode);
	    }
	  }, {
	    key: "getErrorTitleNode",
	    value: function getErrorTitleNode() {
	      return babelHelpers.classPrivateFieldGet(this, _errorTitleNode);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.getSyncButton() !== undefined) this.getSyncButton().setWaiting(true);
	      babelHelpers.classPrivateFieldGet(this, _node).classList.add("mail-progress-show");
	      babelHelpers.classPrivateFieldGet(this, _node).classList.remove("mail-progress-hide");
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.getSyncButton() !== undefined) this.getSyncButton().setWaiting(false);
	      babelHelpers.classPrivateFieldGet(this, _node).classList.add("mail-progress-hide");
	      babelHelpers.classPrivateFieldGet(this, _node).classList.remove("mail-progress-show");
	    }
	  }, {
	    key: "hideErrorBox",
	    value: function hideErrorBox() {
	      babelHelpers.classPrivateFieldGet(this, _errorBoxNode).classList.add("mail-hidden-element");
	      babelHelpers.classPrivateFieldGet(this, _errorBoxNode).classList.remove("mail-visible-element");
	    }
	  }, {
	    key: "showErrorBox",
	    value: function showErrorBox() {
	      babelHelpers.classPrivateFieldGet(this, _errorBoxNode).classList.add("mail-visible-element");
	      babelHelpers.classPrivateFieldGet(this, _errorBoxNode).classList.remove("mail-hidden-element");
	    }
	  }]);
	  return ProgressBar;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _name = /*#__PURE__*/new WeakMap();
	var Counters = /*#__PURE__*/function () {
	  function Counters(name, selectedDirectory) {
	    babelHelpers.classCallCheck(this, Counters);
	    babelHelpers.defineProperty(this, "cachedCounters", []);
	    babelHelpers.defineProperty(this, "counters", []);
	    babelHelpers.defineProperty(this, "hiddenCountersForTotalCounter", []);
	    babelHelpers.defineProperty(this, "shortcuts", []);
	    _classPrivateFieldInitSpec$1(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _name, name);
	    this.setDirectory(selectedDirectory);
	  }
	  babelHelpers.createClass(Counters, [{
	    key: "getCounters",
	    value: function getCounters() {
	      return this.counters;
	    }
	  }, {
	    key: "getDirPath",
	    value: function getDirPath(shortcut) {
	      if (shortcut === undefined) {
	        shortcut = '';
	      }
	      if (this.shortcuts[shortcut] !== undefined) {
	        return this.shortcuts[shortcut];
	      }
	      return shortcut;
	    }
	  }, {
	    key: "getShortcut",
	    value: function getShortcut(path) {
	      //because they have a closure
	      return this.getDirPath(path);
	    }
	  }, {
	    key: "setDirectory",
	    value: function setDirectory(name) {
	      if (name === undefined) {
	        name = '';
	      }
	      if (this.shortcuts[name]) {
	        this.selectedDirectory = this.shortcuts[name];
	      } else {
	        this.selectedDirectory = name;
	      }
	      var resultCounters = {};
	      resultCounters[this.selectedDirectory] = this.getCounter(this.selectedDirectory);
	      this.sendCounterUpdateEvent(resultCounters);
	    }
	  }, {
	    key: "setShortcut",
	    value: function setShortcut(shortcutName, name) {
	      //backlink
	      this.shortcuts[shortcutName] = name;
	      this.shortcuts[name] = shortcutName;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return babelHelpers.classPrivateFieldGet(this, _name);
	    }
	  }, {
	    key: "setHiddenCountersForTotalCounter",
	    value: function setHiddenCountersForTotalCounter(counterNames) {
	      var _iterator = _createForOfIteratorHelper(counterNames),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var counter = _step.value;
	          this.hiddenCountersForTotalCounter[counter] = 'disabled';
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "isHidden",
	    value: function isHidden(name) {
	      if (this.hiddenCountersForTotalCounter[name] === 'disabled') {
	        return true;
	      }
	      return false;
	    }
	  }, {
	    key: "getTotalCounter",
	    value: function getTotalCounter() {
	      var counters = 0;
	      for (var name in this.counters) {
	        if (name in this.hiddenCountersForTotalCounter) {
	          continue;
	        }
	        counters += this.counters[name];
	      }
	      return counters;
	    }
	  }, {
	    key: "getCounterObjects",
	    value: function getCounterObjects() {
	      return this.counters;
	    }
	  }, {
	    key: "getCounter",
	    value: function getCounter(name) {
	      return this.counters[name];
	    }
	  }, {
	    key: "addCounter",
	    value: function addCounter(name, count) {
	      this.counters[name] = Number(count);
	      return this.counters[name];
	    }
	  }, {
	    key: "addCounters",
	    value: function addCounters(counters) {
	      this.cacheCounters();
	      var resultCounters = {};
	      for (var i = 0; i < counters.length; i++) {
	        var counter = counters[i];
	        counter['count'] = Number(counter['count']);
	        var path = counter['path'];
	        this.addCounter(path, counter['count']);
	        if (this.shortcuts[path]) {
	          resultCounters[this.shortcuts[path]] = counter['count'];
	        } else {
	          resultCounters[path] = counter['count'];
	        }
	      }
	      this.sendCounterUpdateEvent(resultCounters);
	    } /*Set counters as when adding. Old counters with different names are retained*/
	  }, {
	    key: "setCounters",
	    value: function setCounters(counters) {
	      this.addCounters(counters);
	    }
	  }, {
	    key: "isExists",
	    value: function isExists(name) {
	      return this.counters[name] !== undefined;
	    }
	  }, {
	    key: "increaseCounter",
	    value: function increaseCounter(name) {
	      var count = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
	      this.cacheCounters();
	      if (name in this.hiddenCountersForTotalCounter) {
	        return "hidden counters for total counter";
	      }
	      if (!this.isExists(name)) {
	        return "no counter";
	      }
	      this.counters[name] += Number(count);
	    }
	  }, {
	    key: "lowerCounter",
	    value: function lowerCounter(name) {
	      var count = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
	      this.cacheCounters();
	      if (name in this.hiddenCountersForTotalCounter) {
	        return "hidden counters for total counter";
	      }
	      if (!this.isExists(name)) {
	        return "no counter";
	      }
	      var newValue = this.counters[name] - Number(count);
	      if (newValue < 0) {
	        return "negative value";
	      }
	      this.counters[name] = newValue;
	    }
	  }, {
	    key: "cacheCounters",
	    value: function cacheCounters() {
	      this.cachedCounters = [];
	      Object.assign(this.cachedCounters, this.counters);
	    }
	  }, {
	    key: "restoreFromCache",
	    value: function restoreFromCache() {
	      this.counters = [];
	      Object.assign(this.counters, this.cachedCounters);
	      this.sendCounterUpdateEvent(this.counters);
	    } /*Change counters by rule*/
	  }, {
	    key: "updateCounters",
	    value: function updateCounters() {
	      var counters = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [{
	        name: 'counter1',
	        count: 2,
	        increase: false,
	        lower: true
	      }, {
	        name: 'counter2',
	        count: 2,
	        increase: true,
	        lower: false
	      }];
	      this.cacheCounters();
	      var resultCounters = {};
	      var countersAreNotLoadedFromTheServer = false;
	      for (var i = 0; i < counters.length; i++) {
	        var counter = counters[i];
	        var name = counter['name'];
	        if (counter['lower']) {
	          if (this.lowerCounter(name, counter['count']) === "negative value") {
	            countersAreNotLoadedFromTheServer = true;
	          }
	        }
	        if (counter['increase'] && countersAreNotLoadedFromTheServer === false) {
	          this.increaseCounter(name, counter['count']);
	        }
	        if (this.shortcuts[name]) {
	          resultCounters[this.shortcuts[name]] = this.getCounter(name);
	        } else {
	          resultCounters[name] = this.getCounter(name);
	        }
	      }
	      this.sendCounterUpdateEvent(resultCounters);
	    }
	  }, {
	    key: "sendCounterUpdateEvent",
	    value: function sendCounterUpdateEvent(counters) {
	      if (counters === undefined) {
	        counters = this.counters;
	      }
	      if (counters.length === 0) {
	        return;
	      }
	      var event = new main_core_events.BaseEvent({
	        data: {
	          counters: counters,
	          hidden: this.hiddenCountersForTotalCounter,
	          selectedDirectory: this.selectedDirectory,
	          name: this.getName(),
	          total: this.getTotalCounter()
	        }
	      });
	      main_core_events.EventEmitter.emit('BX.Mail.Home:updatingCounters', event);
	    }
	  }]);
	  return Counters;
	}();

	var LeftMenu = function LeftMenu() {
	  var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	    dirsWithUnseenMailCounters: {},
	    mailboxId: '',
	    filterId: '',
	    systemDirs: {
	      spam: 'Spam',
	      trash: 'Trash',
	      outcome: 'Outcome',
	      drafts: 'Drafts',
	      inbox: 'Inbox'
	    }
	  };
	  babelHelpers.classCallCheck(this, LeftMenu);
	  var leftDirectoryMenuWrapper = document.querySelector('.mail-left-menu-wrapper');
	  this.directoryMenu = new mail_directorymenu.DirectoryMenu({
	    dirsWithUnseenMailCounters: config['dirsWithUnseenMailCounters'],
	    filterId: config['filterId'],
	    systemDirs: config['systemDirs']
	  });
	  leftDirectoryMenuWrapper.append(this.directoryMenu.getNode());
	};

	var List = /*#__PURE__*/function () {
	  function List(options) {
	    babelHelpers.classCallCheck(this, List);
	    this.mailReadAllButton = options.mailReadAllButton;
	    this.gridId = options.gridId;
	    this.mailboxId = options.mailboxId;
	    this.canMarkSpam = options.canMarkSpam;
	    this.canDelete = options.canDelete;
	    this.ERROR_CODE_CAN_NOT_DELETE = options.ERROR_CODE_CAN_NOT_DELETE;
	    this.ERROR_CODE_CAN_NOT_MARK_SPAM = options.ERROR_CODE_CAN_NOT_MARK_SPAM;
	    this.disabledClassName = 'js-disabled';
	    this.userInterfaceManager = new BX.Mail.Client.Message.List.UserInterfaceManager(options);
	    this.userInterfaceManager.resetGridSelection = this.resetGridSelection.bind(this);
	    this.userInterfaceManager.isSelectedRowsHaveClass = this.isSelectedRowsHaveClass.bind(this);
	    this.userInterfaceManager.getGridInstance = this.getGridInstance.bind(this);
	    this.userInterfaceManager.updateCountersFromBackend = this.updateCountersFromBackend.bind(this);
	    this.cache = {};
	    this.addEventHandlers();
	    BX.Mail.Client.Message.List[options.id] = this;
	  }
	  babelHelpers.createClass(List, [{
	    key: "addEventHandlers",
	    value: function addEventHandlers() {
	      var _this = this;
	      // todo delete this hack
	      // it is here to prevent grid's title changing after filter apply
	      BX.ajax.UpdatePageData = function () {};
	      main_core_events.EventEmitter.subscribe('onSubMenuShow', function (event) {
	        var menuItem = event.target;
	        var container = menuItem.getMenuWindow().getPopupWindow().getPopupContainer();
	        var id = null;
	        if (container) {
	          id = BX.data(container, 'grid-row-id');
	        }
	        BX.data(menuItem.getSubMenu().getPopupWindow().getPopupContainer(), 'grid-row-id', menuItem.gridRowId || id);
	      });
	      main_core_events.EventEmitter.subscribe('Mail::directoryChanged', function () {
	        _this.resetGridSelection();
	      });
	      main_core_events.EventEmitter.subscribe('BX.Mail.Home:updatingCounters', function (event) {
	        if (event['data']['name'] !== 'mailboxCounters') {
	          var counters = event['data']['counters'];
	          BX.Mail.Home.LeftMenuNode.directoryMenu.setCounters(counters);
	          BX.Mail.Home.mailboxCounters.setCounters([{
	            path: 'unseenCountInCurrentMailbox',
	            count: BX.Mail.Home.Counters.getTotalCounter()
	          }]);
	        } else {
	          _this.userInterfaceManager.updateLeftMenuCounter();
	        }
	      });
	      main_core_events.EventEmitter.subscribe('BX.Main.Menu.Item:onmouseenter', function (event) {
	        var menuItem = event.target;
	        if (!menuItem.dataset || !menuItem.getMenuWindow()) {
	          return;
	        }
	        var menuWindow = menuItem.getMenuWindow();
	        var subMenuItems = menuWindow.getMenuItems();
	        var path = menuItem.dataset.path;
	        var hash = menuItem.dataset.dirMd5;
	        var hasChild = menuItem.dataset.hasChild;
	        if (!hasChild) {
	          return;
	        }
	        for (var i = 0; i < subMenuItems.length; i++) {
	          var item = subMenuItems[i];
	          if (item.getId() === path) {
	            var hasSubMenu = item.hasSubMenu();
	            if (hasSubMenu) {
	              item.showSubMenu();
	              var subMenu = item.getSubMenu();
	              var hasLoadingItem = false;
	              if (subMenu) {
	                var items = subMenu.getMenuItems();
	                for (var k = 0; k < items.length; k++) {
	                  var subItem = items[k];
	                  if (subItem.getId() === 'loading') {
	                    hasLoadingItem = true;
	                  }
	                }
	              }
	              if (!hasLoadingItem) {
	                return;
	              }
	            }
	            this.loadLevelMenu(item, hash);
	          }
	        }
	      }.bind(this));
	      var itemsMenu = document.querySelectorAll('.ical-event-control-menu');
	      for (var i = 0; i < itemsMenu.length; i++) {
	        itemsMenu[i].addEventListener('click', this.showICalMenuDropdown.bind(this));
	      }
	      BX.bindDelegate(document.body, 'click', {
	        className: 'ical-event-control-button'
	      }, this.onClickICalButton.bind(this));
	    }
	  }, {
	    key: "loadLevelMenu",
	    value: function loadLevelMenu(menuItem, hash) {
	      var menu = this.getCache(menuItem.getId());
	      var popup = BX.Main.PopupManager.getPopupById('menu-popup-popup-submenu-' + menuItem.getId());
	      if (popup) {
	        popup.destroy();
	      }
	      if (menu) {
	        menuItem.destroySubMenu();
	        menuItem.addSubMenu(menu);
	        menuItem.showSubMenu();
	        return;
	      }
	      var subItem = {
	        'id': 'loading',
	        'text': main_core.Loc.getMessage('MAIL_CLIENT_BUTTON_LOADING'),
	        'disabled': true
	      };
	      menuItem.destroySubMenu();
	      menuItem.addSubMenu([subItem]);
	      menuItem.showSubMenu();
	      BX.ajax.runComponentAction('bitrix:mail.client.config.dirs', 'level', {
	        mode: 'class',
	        data: {
	          mailboxId: this.mailboxId,
	          dir: {
	            path: menuItem.getId(),
	            dirMd5: hash
	          }
	        }
	      }).then(function (response) {
	        var dirs = response.data.dirs;
	        var items = [];
	        for (var i = 0; i < dirs.length; i++) {
	          var hasChild = /(HasChildren)/i.test(dirs[i].FLAGS);
	          var item = {
	            'id': dirs[i].PATH,
	            'text': dirs[i].NAME,
	            'dataset': {
	              'path': dirs[i].PATH,
	              'dirMd5': dirs[i].DIR_MD5,
	              'isDisabled': dirs[i].IS_DISABLED,
	              'hasChild': hasChild
	            },
	            items: hasChild ? [{
	              id: 'loading',
	              'text': main_core.Loc.getMessage('MAIL_CLIENT_BUTTON_LOADING'),
	              'disabled': true
	            }] : []
	          };
	          items.push(item);
	        }
	        this.setCache(menuItem.getId(), items);
	        var popup = BX.Main.PopupManager.getPopupById('menu-popup-popup-submenu-' + menuItem.getId());
	        var isShown = menuItem.getMenuWindow().getPopupWindow().isShown();
	        if (popup) {
	          popup.destroy();
	        }
	        if (isShown) {
	          menuItem.destroySubMenu();
	          menuItem.addSubMenu(items);
	          menuItem.showSubMenu();
	        }
	      }.bind(this), function (response) {}.bind(this));
	    }
	  }, {
	    key: "onCrmClick",
	    value: function onCrmClick(id) {
	      var selected = this.getGridInstance().getRows().getSelected();
	      var row = id ? this.getGridInstance().getRows().getById(id) : selected[0];
	      if (!(row && row.node)) {
	        return;
	      }
	      var addToCrm = this.userInterfaceManager.isAddToCrmActionAvailable(row.node);
	      var messageIdNode = row.node.querySelector('[data-message-id]');
	      if (!(messageIdNode.dataset && messageIdNode.dataset.messageId)) {
	        return;
	      }
	      if (id === undefined) {
	        this.resetGridSelection();
	      }
	      if (addToCrm) {
	        var crmBtnInRow = row.node.querySelector('.mail-binding-crm.mail-ui-not-active');
	        if (crmBtnInRow) {
	          crmBtnInRow.startWait();
	        }
	        if (babelHelpers["typeof"](this.isAddingToCrmInProgress) !== "object") {
	          this.isAddingToCrmInProgress = {};
	        }
	        if (this.isAddingToCrmInProgress[id] === true) {
	          return;
	        }
	        this.isAddingToCrmInProgress[id] = true;
	        BX.ajax.runComponentAction('bitrix:mail.client', 'createCrmActivity', {
	          mode: 'ajax',
	          data: {
	            messageId: messageIdNode.dataset.messageId
	          },
	          analyticsLabel: {
	            'groupCount': selected.length,
	            'bindings': this.getRowsBindings([row])
	          }
	        }).then(function (id) {
	          this.isAddingToCrmInProgress[id] = false;
	          this.notify(main_core.Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADDED_TO_CRM'));
	        }.bind(this, id), function (json) {
	          if (crmBtnInRow) {
	            crmBtnInRow.stopWait();
	          }
	          this.isAddingToCrmInProgress[id] = false;
	          if (json.errors && json.errors.length > 0) {
	            this.notify(json.errors.map(function (item) {
	              return item.message;
	            }).join('<br>'), 5000);
	          } else {
	            this.notify(main_core.Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR'));
	          }
	        }.bind(this));
	      } else {
	        this.userInterfaceManager.onCrmBindingDeleted(messageIdNode.dataset.messageId);
	        BX.ajax.runComponentAction('bitrix:mail.client', 'removeCrmActivity', {
	          mode: 'ajax',
	          data: {
	            messageId: messageIdNode.dataset.messageId
	          },
	          analyticsLabel: {
	            'groupCount': selected.length,
	            'bindings': this.getRowsBindings([row])
	          }
	        }).then(function (messageIdNode) {
	          this.notify(main_core.Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_EXCLUDED_FROM_CRM'));
	        }.bind(this, messageIdNode));
	      }
	      var selectedIds = this.getGridInstance().getRows().getSelectedIds();
	      if (selectedIds.length === 1 && selectedIds[0] === id) {
	        this.resetGridSelection();
	      }
	    }
	  }, {
	    key: "onViewClick",
	    value: function onViewClick(id) {
	      if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0) {
	        return;
	      }
	      // @TODO: path
	      BX.SidePanel.Instance.open("/mail/message/" + id, {
	        width: 1080,
	        loader: 'view-mail-loader'
	      });
	    }
	  }, {
	    key: "onDeleteImmediately",
	    value: function onDeleteImmediately(id) {
	      var additionalOptions = {
	        'deleteImmediately': true
	      };
	      this.onDeleteClick(id, additionalOptions);
	    }
	  }, {
	    key: "onDeleteClick",
	    value: function onDeleteClick(id, additionalOptions) {
	      var selected = this.getGridInstance().getRows().getSelected();
	      if (id === undefined && selected.length === 0) {
	        return;
	      }
	      if (!this.canDelete) {
	        this.showDirsSlider();
	        return;
	      }
	      var options = {
	        params: additionalOptions !== undefined ? additionalOptions : {},
	        keepRows: true,
	        analyticsLabel: {
	          'groupCount': selected.length,
	          'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected)
	        }
	      };
	      var selectedIds;
	      if (id === undefined) {
	        selectedIds = BX.Mail.Home.Grid.getSelectedIds();
	      } else {
	        selectedIds = [id];
	      }
	      selectedIds = this.filterRowsByClassName(this.disabledClassName, selectedIds, true);
	      options.ids = selectedIds;
	      if (this.userInterfaceManager.isCurrentFolderTrash || additionalOptions !== undefined && additionalOptions['deleteImmediately']) {
	        var confirmPopup = this.getConfirmDeletePopup(options);
	        confirmPopup.show();
	      } else {
	        BX.Mail.Home.Grid.hideRowByIds(selectedIds);
	        var unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;
	        if (this.getCurrentFolder() !== '') {
	          BX.Mail.Home.Counters.updateCounters([{
	            name: this.getCurrentFolder(),
	            lower: true,
	            count: unseenRowsIdsCount
	          }]);
	        }
	        this.runAction('delete', options, function () {
	          return BX.Mail.Home.Grid.reloadTable();
	        });
	        if (id === undefined) {
	          this.resetGridSelection();
	        }
	      }
	    }
	  }, {
	    key: "onMoveToFolderClick",
	    value: function onMoveToFolderClick(event) {
	      var folderOptions = event.currentTarget.dataset;
	      var toFolderByPath = folderOptions.path;
	      var toFolderByName = toFolderByPath;
	      if (toFolderByPath === this.getCurrentFolder()) {
	        this.notify(main_core.Loc.getMessage('MESSAGES_ALREADY_EXIST_IN_FOLDER'));
	        return;
	      }
	      var id = undefined;
	      var popupSubmenu = BX.findParent(event.currentTarget, {
	        className: 'popup-window'
	      });
	      if (popupSubmenu) {
	        id = BX.data(popupSubmenu, 'grid-row-id');
	      }
	      var isDisabled = JSON.parse(folderOptions.isDisabled);
	      if (id === undefined && this.getGridInstance().getRows().getSelectedIds().length === 0 || isDisabled) {
	        return;
	      }
	      var selected = this.getGridInstance().getRows().getSelected();
	      var idsForMoving = id ? [id] : this.getGridInstance().getRows().getSelectedIds();
	      idsForMoving = this.filterRowsByClassName(this.disabledClassName, idsForMoving, true);
	      if (!idsForMoving.length) {
	        return;
	      }

	      // to hide the context menu
	      BX.onCustomEvent('Grid::updated');
	      var selectedIds;
	      if (id === undefined) {
	        selectedIds = BX.Mail.Home.Grid.getSelectedIds();
	      } else {
	        selectedIds = [id];
	      }
	      BX.Mail.Home.Grid.hideRowByIds(selectedIds);
	      var unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;
	      if (this.getCurrentFolder() !== '') {
	        BX.Mail.Home.Counters.updateCounters([{
	          name: toFolderByName,
	          increase: true,
	          count: unseenRowsIdsCount
	        }, {
	          name: this.getCurrentFolder(),
	          lower: true,
	          count: unseenRowsIdsCount
	        }]);
	      }
	      this.runAction('moveToFolder', {
	        keepRows: true,
	        ids: idsForMoving,
	        params: {
	          folder: toFolderByPath
	        },
	        analyticsLabel: {
	          'groupCount': selected.length,
	          'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected)
	        }
	      }, function () {
	        BX.Mail.Home.Grid.reloadTable();
	      });
	      if (id === undefined) {
	        this.resetGridSelection();
	      }
	    }
	  }, {
	    key: "onReadClick",
	    value: function onReadClick(id) {
	      var selected = [];
	      var resultIds = [];
	      if (id === undefined) {
	        selected = this.getGridInstance().getRows().getSelected();
	        resultIds = this.getGridInstance().getRows().getSelectedIds();
	      } else {
	        var selectedIds = this.getGridInstance().getRows().getSelectedIds();
	        if (selectedIds.length === 1 && selectedIds[0] === id) {
	          /*if the action is non-group, but one cell is selected,
	          then the action was performed through the "Action panel"
	          and the selection should be reset*/
	          selected = this.getGridInstance().getRows().getSelected();
	          resultIds = selectedIds;
	          id = undefined;
	        } else {
	          resultIds = [id];
	        }
	      }
	      if (id === undefined && selected.length === 0) {
	        return;
	      }
	      var actionName = 'all' == id || this.isSelectedRowsHaveClass('mail-msg-list-cell-unseen', id) ? 'markAsSeen' : 'markAsUnseen';
	      resultIds = this.filterRowsByClassName('mail-msg-list-cell-unseen', resultIds, actionName !== 'markAsSeen');
	      resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
	      if (!resultIds.length) {
	        return;
	      }
	      var handler = function handler() {
	        this.userInterfaceManager.onMessagesRead(resultIds, {
	          action: actionName
	        });
	        var currentFolder = this.getCurrentFolder();
	        var oldMessagesCount = actionName !== 'markAsSeen' ? this.isSelectedRowsHaveClass('mail-msg-list-cell-old') : 0;
	        var countMessages = resultIds.length - oldMessagesCount;
	        if (this.getCurrentFolder() !== '') {
	          if (actionName === 'markAsSeen') {
	            if ('all' === id) {
	              countMessages = BX.Mail.Home.Counters.getCounter(currentFolder) - oldMessagesCount;
	            }
	            BX.Mail.Home.Counters.updateCounters([{
	              name: currentFolder,
	              lower: true,
	              count: countMessages
	            }]);
	          } else {
	            BX.Mail.Home.Counters.updateCounters([{
	              name: currentFolder,
	              increase: true,
	              count: countMessages
	            }]);
	          }
	        }
	        if (id === undefined) {
	          this.resetGridSelection();
	        }
	        if ('all' == id) {
	          resultIds['for_all'] = this.mailboxId + '-' + this.userInterfaceManager.getCurrentFolder();
	        }
	        this.userInterfaceManager.updateUnreadCounters();
	        this.runAction(actionName, {
	          ids: resultIds,
	          keepRows: true,
	          successParams: actionName,
	          analyticsLabel: {
	            'groupCount': selected.length,
	            'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected)
	          },
	          onSuccess: function () {
	            this.updateCountersFromBackend();
	          }.bind(this)
	        });
	        return true;
	      };
	      handler.apply(this);
	    }
	  }, {
	    key: "onSpamClick",
	    value: function onSpamClick(id) {
	      var selected = this.getGridInstance().getRows().getSelected();
	      if (id === undefined && selected.length === 0) {
	        return;
	      }
	      if (!this.canMarkSpam) {
	        this.showDirsSlider();
	        return;
	      }
	      var actionName = this.isSelectedRowsHaveClass('js-spam', id) ? 'restoreFromSpam' : 'markAsSpam';
	      var resultIds = this.filterRowsByClassName('js-spam', id, actionName !== 'restoreFromSpam');
	      resultIds = this.filterRowsByClassName(this.disabledClassName, resultIds, true);
	      if (!resultIds.length) {
	        return;
	      }
	      var options = {
	        keepRows: true,
	        analyticsLabel: {
	          'groupCount': selected.length,
	          'bindings': this.getRowsBindings(id ? [this.getGridInstance().getRows().getById(id)] : selected)
	        }
	      };
	      var selectedIds;
	      if (id === undefined) {
	        selectedIds = BX.Mail.Home.Grid.getSelectedIds();
	      } else {
	        selectedIds = [id];
	      }
	      options.ids = selectedIds;
	      BX.Mail.Home.Grid.hideRowByIds(selectedIds);
	      var unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', selectedIds).length;
	      if (this.getCurrentFolder() !== '') {
	        if (actionName === 'markAsSpam') {
	          BX.Mail.Home.Counters.updateCounters([{
	            name: this.userInterfaceManager.spamDir,
	            increase: true,
	            count: unseenRowsIdsCount
	          }, {
	            name: this.getCurrentFolder(),
	            lower: true,
	            count: unseenRowsIdsCount
	          }]);
	        } else {
	          BX.Mail.Home.Counters.updateCounters([{
	            name: this.userInterfaceManager.spamDir,
	            lower: true,
	            count: unseenRowsIdsCount
	          }, {
	            name: this.userInterfaceManager.inboxDir,
	            increase: true,
	            count: unseenRowsIdsCount
	          }]);
	        }
	      }
	      this.runAction(actionName, options, function () {
	        return BX.Mail.Home.Grid.reloadTable();
	      });
	      if (id === undefined) {
	        this.resetGridSelection();
	      }
	    }
	  }, {
	    key: "getConfirmDeletePopup",
	    value: function getConfirmDeletePopup(options) {
	      return new BX.UI.Dialogs.MessageBox({
	        title: main_core.Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_TITLE'),
	        message: main_core.Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE'),
	        buttons: [new BX.UI.Button({
	          color: BX.UI.Button.Color.DANGER,
	          text: main_core.Loc.getMessage('MAIL_MESSAGE_LIST_CONFIRM_DELETE_BTN'),
	          onclick: function (button) {
	            var unseenRowsIdsCount = this.filterRowsByClassName('mail-msg-list-cell-unseen', options.ids).length;
	            BX.Mail.Home.Counters.updateCounters([{
	              name: this.getCurrentFolder(),
	              lower: true,
	              count: unseenRowsIdsCount
	            }]);
	            this.runAction('delete', options, function () {
	              return BX.Mail.Home.Grid.reloadTable();
	            });
	            button.getContext().close();
	            BX.Mail.Home.Grid.hideRowByIds(options.ids);
	          }.bind(this)
	        }), new BX.UI.CancelButton({
	          onclick: function onclick(button) {
	            button.getContext().close();
	          }
	        })]
	      });
	    }
	  }, {
	    key: "resetGridSelection",
	    value: function resetGridSelection() {
	      BX.onCustomEvent('Mail::resetGridSelection');
	      this.getGridInstance().getRows().unselectAll();
	      this.getGridInstance().adjustCheckAllCheckboxes();
	      BX.Mail.Home.Grid.hidePanel();
	    }
	  }, {
	    key: "isSelectedRowsHaveClass",
	    value: function isSelectedRowsHaveClass(className, id) {
	      var selectedIds;
	      if (id === undefined) {
	        selectedIds = this.getGridInstance().getRows().getSelectedIds();
	      } else {
	        selectedIds = [id];
	      }
	      var ids = selectedIds.length ? selectedIds : id ? [id] : [];
	      var selectedLinesWithClassNumber = 0;
	      for (var i = 0; i < ids.length; i++) {
	        var row = this.getGridInstance().getRows().getById(ids[i]);
	        if (row && row.node) {
	          var columns = row.node.getElementsByClassName(className);
	          if (columns && columns.length) {
	            selectedLinesWithClassNumber++;
	          }
	        }
	      }
	      return selectedLinesWithClassNumber;
	    }
	  }, {
	    key: "filterRowsByClassName",
	    value: function filterRowsByClassName(className, ids, isReversed) {
	      var resIds = [];
	      if ('all' == ids) {
	        resIds = this.getGridInstance().getRows().getBodyChild().map(function (current) {
	          return current.getId();
	        });
	      } else if (Array.isArray(ids)) {
	        resIds = ids;
	      } else {
	        var selectedIds = this.getGridInstance().getRows().getSelectedIds();
	        resIds = selectedIds.length ? selectedIds : ids ? [ids] : [];
	      }
	      var resultIds = [];
	      for (var i = resIds.length - 1; i >= 0; i--) {
	        var row = this.getGridInstance().getRows().getById(resIds[i]);
	        if (row && row.node) {
	          var columns = row.node.getElementsByClassName(className);
	          if (!isReversed && columns && columns.length) {
	            resultIds.push(resIds[i]);
	          } else if (isReversed && !(columns && columns.length)) {
	            resultIds.push(resIds[i]);
	          }
	        }
	      }
	      return resultIds;
	    }
	  }, {
	    key: "notify",
	    value: function notify(text, delay) {
	      top.BX.UI.Notification.Center.notify({
	        autoHideDelay: delay > 0 ? delay : 2000,
	        content: text ? text : main_core.Loc.getMessage('MAIL_MESSAGE_LIST_NOTIFY_SUCCESS')
	      });
	    }
	  }, {
	    key: "updateCountersFromBackend",
	    value: function updateCountersFromBackend() {
	      if (this.getCurrentFolder() === '') {
	        BX.ajax.runComponentAction('bitrix:mail.client.message.list', 'getDirsWithUnseenMailCounters', {
	          mode: 'class',
	          data: {
	            mailboxId: this.mailboxId
	          }
	        }).then(function (response) {
	          BX.Mail.Home.Counters.setCounters(response.data);
	        });
	      }
	    }
	  }, {
	    key: "runAction",
	    value: function runAction(actionName, options, actionOnSuccess) {
	      options = options ? options : {};
	      var selectedIds = [];
	      if (options.ids) {
	        selectedIds = options.ids;
	      }
	      if (!selectedIds.length && !selectedIds.for_all) {
	        return;
	      }
	      if (!options.keepRows) {
	        this.getGridInstance().tableFade();
	      }
	      var data = {
	        ids: selectedIds
	      };
	      if (options.params) {
	        var optionsKeys = Object.keys(Object(options.params));
	        for (var nextIndex = 0, len = optionsKeys.length; nextIndex < len; nextIndex++) {
	          var nextKey = optionsKeys[nextIndex];
	          var desc = Object.getOwnPropertyDescriptor(options.params, nextKey);
	          if (desc !== undefined && desc.enumerable) {
	            data[nextKey] = options.params[nextKey];
	          }
	        }
	      }
	      BX.ajax.runComponentAction('bitrix:mail.client', actionName, {
	        mode: 'ajax',
	        data: data,
	        analyticsLabel: options.analyticsLabel
	      }).then(function () {
	        if (options.onSuccess === false) {
	          return;
	        }
	        this.updateCountersFromBackend();
	        if (options.onSuccess && typeof options.onSuccess === "function") {
	          options.onSuccess.bind(this, selectedIds, options.successParams)();
	          return;
	        }
	        if (actionOnSuccess === undefined) {
	          this.notify();
	        } else {
	          actionOnSuccess();
	        }
	      }.bind(this), function (response) {
	        BX.Mail.Home.Counters.restoreFromCache();
	        BX.Mail.Home.Grid.reloadTable();
	        options.onError && typeof options.onError === "function" ? options.onError().bind(this, response) : this.onErrorRequest(response);
	      }.bind(this));
	    }
	  }, {
	    key: "onErrorRequest",
	    value: function onErrorRequest(response) {
	      var options = {};
	      this.checkErrorRights(response.errors);
	      options.errorMessage = response.errors[0].message;
	      this.notify(options.errorMessage);
	    }
	  }, {
	    key: "checkErrorRights",
	    value: function checkErrorRights(errors) {
	      for (var i = 0; i < errors.length; i++) {
	        if (errors[i].code === this.ERROR_CODE_CAN_NOT_DELETE) {
	          this.canDelete = false;
	        }
	        if (errors[i].code === this.ERROR_CODE_CAN_NOT_MARK_SPAM) {
	          this.canMarkSpam = false;
	        }
	      }
	    }
	  }, {
	    key: "showDirsSlider",
	    value: function showDirsSlider() {
	      var url = BX.util.add_url_param("/mail/config/dirs", {
	        mailboxId: this.mailboxId
	      });
	      BX.SidePanel.Instance.open(url, {
	        width: 640,
	        cacheable: false,
	        allowChangeHistory: false
	      });
	      this.canDelete = true;
	      this.canMarkSpam = true;
	    }
	  }, {
	    key: "onDisabledGroupActionClick",
	    value: function onDisabledGroupActionClick() {}
	  }, {
	    key: "getCurrentFolder",
	    value: function getCurrentFolder() {
	      return this.userInterfaceManager.getCurrentFolder();
	    }
	  }, {
	    key: "getGridInstance",
	    value: function getGridInstance() {
	      return BX.Main.gridManager.getById(this.gridId).instance;
	    }
	  }, {
	    key: "getRowsBindings",
	    value: function getRowsBindings(rows) {
	      return BX.util.array_unique(Array.prototype.concat.apply([], rows.map(function (row) {
	        if (!row || !row.node) {
	          return null;
	        }
	        return Array.prototype.map.call(row.node.querySelectorAll('[class^="js-bind-"] [data-type]'), function (node) {
	          return node.dataset.type;
	        });
	      })));
	    }
	  }, {
	    key: "getCache",
	    value: function getCache(key) {
	      if (!key) {
	        return;
	      }
	      return this.cache[key] ? this.cache[key] : null;
	    }
	  }, {
	    key: "setCache",
	    value: function setCache(key, value) {
	      return this.cache[key] = value;
	    }
	  }, {
	    key: "showICalMenuDropdown",
	    value: function showICalMenuDropdown(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      var menu = event.currentTarget.dataset.menu;
	      if (!menu) {
	        return;
	      }
	      this.iCalMenuDropdown = BX.Main.MenuManager.create({
	        id: 'mail-client-message-list-ical-dropdown-menu',
	        autoHide: true,
	        closeByEsc: true,
	        items: JSON.parse(menu),
	        zIndex: 7001,
	        maxHeight: 400,
	        maxWidth: 200,
	        angle: {
	          position: "top",
	          offset: 40
	        },
	        events: {
	          onPopupClose: function () {
	            this.removeICalMenuDropdown();
	          }.bind(this)
	        }
	      });
	      this.iCalMenuDropdown.popupWindow.setBindElement(event.currentTarget);
	      this.iCalMenuDropdown.show();
	    }
	  }, {
	    key: "removeICalMenuDropdown",
	    value: function removeICalMenuDropdown() {
	      if (this.iCalMenuDropdown) {
	        BX.Main.MenuManager.destroy(this.iCalMenuDropdown.id);
	      }
	    }
	  }, {
	    key: "onClickICalButton",
	    value: function onClickICalButton(event) {
	      event.stopPropagation();
	      event.preventDefault();
	      var messageId = event.target.dataset.messageid || event.target.parentNode.dataset.messageid;
	      var action = event.target.dataset.action || event.target.parentNode.dataset.action;
	      var button = event.target;
	      button.classList.add('ui-btn-wait');
	      this.removeICalMenuDropdown();
	      this.sendICal(messageId, action).then(function () {
	        button.classList.remove('ui-btn-wait');
	        this.notify(main_core.Loc.getMessage(action === 'cancelled' ? 'MAIL_MESSAGE_ICAL_NOTIFY_REJECT' : 'MAIL_MESSAGE_ICAL_NOTIFY_ACCEPT'));
	      }.bind(this))["catch"](function () {
	        button.classList.remove('ui-btn-wait');
	        this.notify(main_core.Loc.getMessage('MAIL_MESSAGE_ICAL_NOTIFY_ERROR'));
	      }.bind(this));
	    }
	  }, {
	    key: "sendICal",
	    value: function sendICal(messageId, action) {
	      return new Promise(function (resolve, reject) {
	        BX.ajax.runComponentAction('bitrix:mail.client', 'ical', {
	          mode: 'ajax',
	          data: {
	            messageId: messageId,
	            action: action
	          }
	        }).then(function () {
	          resolve();
	        }.bind(this), function () {
	          reject();
	        }.bind(this));
	      });
	    }
	  }]);
	  return List;
	}();

	var namespaceMailHome = main_core.Reflection.namespace('BX.Mail.Home');
	main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', function (event) {
	  var _event$getCompatData = event.getCompatData(),
	    _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	    messageEvent = _event$getCompatData2[0];
	  if (messageEvent.getEventId() === 'mail-mailbox-config-success') {
	    BXMailMailbox.sync(namespaceMailHome.ProgressBar, namespaceMailHome.Grid.getId(), false, true);
	  }
	});
	var sliderPage;
	var progressBar;
	var errorBox;
	var syncButtonWrapper;
	var selectedIdsForRecovery = {};
	main_core.Event.ready(function () {
	  syncButtonWrapper = document.querySelector('[data-role="mail-msg-sync-button-wrapper"]');
	  var syncButton = new ui_buttons.Button({
	    className: "ui-btn ui-btn-themes ui-btn-light-border mail-msg-sync-button",
	    icon: ui_buttons.Button.Icon.BUSINESS,
	    props: {
	      title: main_core.Loc.getMessage("MAIL_MESSAGE_SYNC_BTN_HINT")
	    },
	    onclick: function onclick() {
	      BXMailMailbox.sync(namespaceMailHome.ProgressBar, main_core.Loc.getMessage("MAIL_MESSAGE_GRID_ID"), false, true);
	    }
	  });
	  syncButtonWrapper.append(syncButton.getContainer());
	  main_core_events.EventEmitter.subscribe('BX.Main.Grid:onBeforeReload', function (event) {
	    var _event$getCompatData3 = event.getCompatData(),
	      _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	      grid = _event$getCompatData4[0];
	    if (grid !== {} && grid !== undefined && main_core.Loc.getMessage("MAIL_MESSAGE_GRID_ID") === grid.getId()) {
	      selectedIdsForRecovery = grid.getRows().getSelectedIds();
	    }
	  });
	  main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	    var _event$getCompatData5 = event.getCompatData(),
	      _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 1),
	      grid = _event$getCompatData6[0];
	    if (grid !== {} && grid !== undefined && main_core.Loc.getMessage("MAIL_MESSAGE_GRID_ID") === grid.getId()) {
	      var rowsWereSelected = false;
	      namespaceMailHome.Grid.getRows().map(function (row) {
	        if (main_core.Type.isFunction(selectedIdsForRecovery.indexOf) && selectedIdsForRecovery.indexOf(row.getId()) !== -1) {
	          if (row.isShown()) {
	            row.select();
	            rowsWereSelected = true;
	          }
	        }
	      });
	      selectedIdsForRecovery = {};
	      if (rowsWereSelected) {
	        setTimeout(function () {
	          main_core_events.EventEmitter.emit(window, 'Grid::thereSelectedRows');
	        }, 0);
	      }
	    }
	  });
	  mail_avatar.Avatar.replaceTagsWithAvatars({
	    className: 'mail-ui-avatar'
	  });
	  sliderPage = document.getElementsByClassName("ui-slider-page")[0];
	  progressBar = document.querySelector('[data-role="mail-progress-bar"]');
	  sliderPage.insertBefore(progressBar, sliderPage.firstChild);
	  errorBox = document.querySelector('[data-role="error-box"]');
	  namespaceMailHome.ProgressBar = new ProgressBar(progressBar);
	  namespaceMailHome.unreadMessageMailboxesMarker = document.querySelector('[data-role="unreadMessageMailboxesMarker"]');
	  namespaceMailHome.ProgressBar.setSyncButton(syncButton);
	  namespaceMailHome.ProgressBar.setErrorBoxNode(document.querySelector('[data-role="error-box"]'));
	  namespaceMailHome.ProgressBar.setErrorTextNode(document.querySelector('[data-role="error-box-text"]'));
	  namespaceMailHome.ProgressBar.setErrorHintNode(document.querySelector('[data-role="error-box-hint"]'));
	  namespaceMailHome.ProgressBar.setErrorTitleNode(document.querySelector('[data-role="error-box-title"]'));
	});
	BX.ready(function () {
	  namespaceMailHome.Counters = new Counters('dirs', main_core.Loc.getMessage("DEFAULT_DIR"));
	  namespaceMailHome.mailboxCounters = new Counters('mailboxCounters');
	  namespaceMailHome.Grid = new mail_messagegrid.MessageGrid(main_core.Loc.getMessage("MAILBOX_IS_SYNC_AVAILABILITY"));
	});
	namespaceMailHome.LeftMenu = LeftMenu;
	var namespaceClientMessage = main_core.Reflection.namespace('BX.Mail.Client.Message');
	namespaceClientMessage.List = List;

}((this.window = this.window || {}),BX.Mail,BX.Mail,BX.Mail,BX.Event,BX,BX.UI));
//# sourceMappingURL=script.js.map
