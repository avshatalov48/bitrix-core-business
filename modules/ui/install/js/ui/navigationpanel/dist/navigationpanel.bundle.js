/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_actionsBar,main_core,main_core_events,main_popup,ui_iconSet_api_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _isDropdown = /*#__PURE__*/new WeakMap();
	var _menuItems = /*#__PURE__*/new WeakMap();
	var _renderDropdownIcon = /*#__PURE__*/new WeakSet();
	var _showMenu = /*#__PURE__*/new WeakSet();
	var _getMenu = /*#__PURE__*/new WeakSet();
	let NavigationItem = /*#__PURE__*/function () {
	  function NavigationItem({
	    id,
	    title,
	    active,
	    events,
	    link,
	    locked,
	    dropdown = false,
	    menuItems = []
	  }) {
	    babelHelpers.classCallCheck(this, NavigationItem);
	    _classPrivateMethodInitSpec(this, _getMenu);
	    _classPrivateMethodInitSpec(this, _showMenu);
	    _classPrivateMethodInitSpec(this, _renderDropdownIcon);
	    _classPrivateFieldInitSpec(this, _isDropdown, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec(this, _menuItems, {
	      writable: true,
	      value: []
	    });
	    this.id = id !== null && id !== void 0 ? id : null;
	    this.title = main_core.Type.isString(title) ? title : null;
	    this.active = main_core.Type.isBoolean(active) ? active : false;
	    this.events = events !== null && events !== void 0 ? events : null;
	    this.link = link !== null && link !== void 0 ? link : null;
	    this.locked = main_core.Type.isBoolean(locked) ? locked : false;
	    babelHelpers.classPrivateFieldSet(this, _isDropdown, dropdown === true);
	    babelHelpers.classPrivateFieldSet(this, _menuItems, menuItems !== null && menuItems !== void 0 ? menuItems : []);
	    this.linkContainer = null;
	    this.bindEvents();
	  }
	  babelHelpers.createClass(NavigationItem, [{
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.title) {
	        this.title = main_core.Tag.render(_t || (_t = _`
				<div class="ui-nav-panel__item-title">${0}</div>	
			`), this.title);
	      }
	      return this.title;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.active === false && babelHelpers.classPrivateFieldGet(this, _isDropdown)) {
	        return null;
	      }
	      if (!this.linkContainer) {
	        const id = this.id ? `id="ui-nav-panel-item-${this.id}"` : '';
	        this.linkContainer = main_core.Tag.render(_t2 || (_t2 = _`
				<div ${0} class="ui-nav-panel__item">
					<span>${0}</span>
					${0}
				</div>
			`), id, this.title ? this.getTitle() : '', babelHelpers.classPrivateFieldGet(this, _isDropdown) ? _classPrivateMethodGet(this, _renderDropdownIcon, _renderDropdownIcon2).call(this) : '');
	        if (babelHelpers.classPrivateFieldGet(this, _isDropdown)) {
	          main_core.Dom.addClass(this.linkContainer, '--dropdown');
	        }
	        this.setEvents();
	        if (this.active) {
	          this.activate();
	        } else {
	          this.inactivate();
	        }
	        if (this.locked) {
	          this.lock();
	        } else {
	          this.unLock();
	        }
	      }
	      return this.linkContainer;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.NavigationPanel.Item:active', item => {
	        if (item.data !== this) {
	          this.inactivate();
	        }
	      });
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
	    key: "setEvents",
	    value: function setEvents() {
	      if (babelHelpers.classPrivateFieldGet(this, _isDropdown)) {
	        main_core.Event.bind(this.linkContainer, 'click', () => {
	          _classPrivateMethodGet(this, _showMenu, _showMenu2).call(this);
	        });
	        return;
	      }
	      if (this.link) {
	        this.linkContainer = main_core.Tag.render(_t3 || (_t3 = _`
				<a class="ui-nav-panel__item">
					<span>${0}</span>
				</a>
			`), this.title ? this.getTitle() : '');
	        Object.entries(this.link).forEach(([linkKey, linkValue]) => {
	          this.linkContainer.setAttribute(linkKey, linkValue);
	        });
	      }
	      if (this.events) {
	        Object.entries(this.events).forEach(([eventKey, eventHandler]) => {
	          main_core.Event.bind(this.getContainer(), eventKey, () => {
	            eventHandler();
	          });
	        });
	      }
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      this.active = true;
	      if (babelHelpers.classPrivateFieldGet(this, _isDropdown) === false) {
	        main_core.Dom.addClass(this.getContainer(), '--active');
	      }
	      main_core_events.EventEmitter.emit('BX.UI.NavigationPanel.Item:active', this);
	    }
	  }, {
	    key: "inactivate",
	    value: function inactivate() {
	      this.active = false;
	      if (babelHelpers.classPrivateFieldGet(this, _isDropdown) === false) {
	        main_core.Dom.removeClass(this.getContainer(), '--active');
	      }
	      main_core_events.EventEmitter.emit('BX.UI.NavigationPanel.Item:inactive', this);
	    }
	  }]);
	  return NavigationItem;
	}();
	function _renderDropdownIcon2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    size: 16,
	    icon: ui_iconSet_api_core.Outline.CHEVRON_DOWN_L
	  }).render();
	  return main_core.Tag.render(_t4 || (_t4 = _`
			<span class="ui-nav-panel__item-dropdown-icon ui-icon-set__scope">${0}</span>
		`), icon);
	}
	function _showMenu2() {
	  _classPrivateMethodGet(this, _getMenu, _getMenu2).call(this).show();
	}
	function _getMenu2() {
	  return new main_popup.Menu({
	    items: babelHelpers.classPrivateFieldGet(this, _menuItems),
	    bindElement: this.getContainer(),
	    cacheable: false,
	    events: {
	      onPopupShow: () => {
	        main_core.Dom.addClass(this.linkContainer, '--active');
	      },
	      onPopupClose: () => {
	        main_core.Dom.removeClass(this.linkContainer, '--active');
	      }
	    }
	  });
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1;
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _isCollapsed = /*#__PURE__*/new WeakMap();
	var _getMenuItems = /*#__PURE__*/new WeakSet();
	var _renderMenuItem = /*#__PURE__*/new WeakSet();
	var _renderMenuItemLockedIcon = /*#__PURE__*/new WeakSet();
	let NavigationPanel = /*#__PURE__*/function () {
	  function NavigationPanel(options) {
	    babelHelpers.classCallCheck(this, NavigationPanel);
	    _classPrivateMethodInitSpec$1(this, _renderMenuItemLockedIcon);
	    _classPrivateMethodInitSpec$1(this, _renderMenuItem);
	    _classPrivateMethodInitSpec$1(this, _getMenuItems);
	    _classPrivateFieldInitSpec$1(this, _isCollapsed, {
	      writable: true,
	      value: false
	    });
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    this.items = main_core.Type.isArray(options.items) ? options.items : [];
	    this.container = null;
	    this.keys = [];
	    babelHelpers.classPrivateFieldSet(this, _isCollapsed, options.collapsed === true);
	  }
	  babelHelpers.createClass(NavigationPanel, [{
	    key: "adjustItem",
	    value: function adjustItem() {
	      this.items = this.items.map(item => {
	        var _item$id, _item$title, _item$events, _item$link;
	        this.keys.push(item.id);
	        return new NavigationItem({
	          id: (_item$id = item.id) !== null && _item$id !== void 0 ? _item$id : null,
	          title: (_item$title = item.title) !== null && _item$title !== void 0 ? _item$title : null,
	          active: item.active === true,
	          events: (_item$events = item.events) !== null && _item$events !== void 0 ? _item$events : null,
	          link: (_item$link = item.link) !== null && _item$link !== void 0 ? _item$link : null,
	          locked: item.locked === true,
	          dropdown: item.active === true && babelHelpers.classPrivateFieldGet(this, _isCollapsed),
	          menuItems: item.active === true && babelHelpers.classPrivateFieldGet(this, _isCollapsed) ? _classPrivateMethodGet$1(this, _getMenuItems, _getMenuItems2).call(this) : []
	        });
	      });
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(value) {
	      if (value) {
	        const id = this.keys.indexOf(value);
	        return this.items[id];
	      }
	      return null;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-nav-panel ui-nav-panel__scope"></div>
			`));
	        if (this.hasAirDesign()) {
	          main_core.Dom.addClass(this.container, '--air');
	        }
	        if (babelHelpers.classPrivateFieldGet(this, _isCollapsed)) {
	          main_core.Dom.addClass(this.container, '--collapsed');
	        }
	      }
	      return this.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.items.forEach(item => {
	        if (babelHelpers.classPrivateFieldGet(this, _isCollapsed) && item.active === false) {
	          return;
	        }
	        if (item instanceof NavigationItem) {
	          main_core.Dom.append(item.getContainer(), this.getContainer());
	        }
	      });
	      main_core.Dom.clean(this.target);
	      main_core.Dom.append(this.getContainer(), this.target);
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.adjustItem();
	      this.render();
	    }
	  }, {
	    key: "hasAirDesign",
	    value: function hasAirDesign() {
	      return main_core.Extension.getSettings('ui.navigationpanel').get('useAirDesign');
	    }
	  }]);
	  return NavigationPanel;
	}();
	function _getMenuItems2() {
	  return this.items.map(item => {
	    var _item$link2;
	    if (item.active) {
	      return null;
	    }
	    return {
	      id: Math.random(),
	      text: item.title,
	      href: (_item$link2 = item.link) === null || _item$link2 === void 0 ? void 0 : _item$link2.href,
	      html: _classPrivateMethodGet$1(this, _renderMenuItem, _renderMenuItem2).call(this, item),
	      className: item.locked ? '--locked' : '',
	      onclick: () => {
	        var _item$events2;
	        (_item$events2 = item.events) === null || _item$events2 === void 0 ? void 0 : _item$events2.click();
	      }
	    };
	  }).filter(item => Boolean(item));
	}
	function _renderMenuItem2(item) {
	  const airModifier = this.hasAirDesign() ? '--air' : '';
	  return main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="ui-nav-panel__menu-item ${0} ${0}">
				${0}
				<span>${0}</span>
			</div>
		`), item.locked ? '--locked' : '', airModifier, item.locked ? _classPrivateMethodGet$1(this, _renderMenuItemLockedIcon, _renderMenuItemLockedIcon2).call(this) : '', item.title);
	}
	function _renderMenuItemLockedIcon2() {
	  const icon = new ui_iconSet_api_core.Icon({
	    icon: ui_iconSet_api_core.Outline.LOCK_L,
	    size: 20
	  }).render();
	  return main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
			<span class="ui-nav-panel__menu-item-icon ui-icon-set__scope">${0}</span>
		`), icon);
	}

	exports.NavigationPanel = NavigationPanel;

}((this.BX.UI = this.BX.UI || {}),BX.UI,BX,BX.Event,BX.Main,BX.UI.IconSet));
//# sourceMappingURL=navigationpanel.bundle.js.map
