/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_fonts_opensans,main_popup,main_core_events,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	var _list = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("list");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _sync = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sync");
	var _addSilent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("addSilent");
	class Collection extends main_core_events.EventEmitter {
	  constructor(options = {}) {
	    super();
	    Object.defineProperty(this, _addSilent, {
	      value: _addSilent2
	    });
	    Object.defineProperty(this, _list, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sync, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('ui:sidepanel:menu:collection');
	    this.setItems(options.items);
	  }
	  setActiveFirstItem() {
	    const item = this.list()[0];
	    if (!item) {
	      return;
	    }
	    item.setActive(true);
	    item.getCollection().setActiveFirstItem();
	  }
	  getActiveItem() {
	    return this.list().filter(item => item.isActive())[0];
	  }
	  syncActive(excludeItem) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _sync)[_sync]) {
	      return this;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _sync)[_sync] = true;
	    this.list().filter(otherItem => otherItem !== excludeItem).forEach(otherItem => {
	      otherItem.getCollection().isEmpty() ? otherItem.setActive(false) : otherItem.getCollection().syncActive(otherItem);
	    });
	    this.emit('sync:active');
	    babelHelpers.classPrivateFieldLooseBase(this, _sync)[_sync] = false;
	    return this;
	  }
	  add(itemOptions) {
	    const item = babelHelpers.classPrivateFieldLooseBase(this, _addSilent)[_addSilent](itemOptions);
	    this.emit('change');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]) {
	      this.render();
	    }
	    return item;
	  }
	  get(id) {
	    return this.list().filter(item => item.getId() === id)[0];
	  }
	  change(id, options) {
	    const foundItem = this.list().find(item => item.getId() === id);
	    if (foundItem) {
	      foundItem.change(options);
	      return foundItem;
	    }
	    return null;
	  }
	  remove(id) {
	    const foundItem = this.list().find(item => item.getId() === id);
	    if (foundItem) {
	      this.emit('change');
	      babelHelpers.classPrivateFieldLooseBase(this, _list)[_list] = this.list().filter(otherItem => otherItem !== foundItem);
	      foundItem.remove();
	    }
	  }
	  setItems(items = []) {
	    babelHelpers.classPrivateFieldLooseBase(this, _list)[_list] = items.map(itemOptions => babelHelpers.classPrivateFieldLooseBase(this, _addSilent)[_addSilent](itemOptions));
	    this.emit('change');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]) {
	      this.render();
	    }
	    return this;
	  }
	  list() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _list)[_list];
	  }
	  isEmpty() {
	    return this.list().length === 0;
	  }
	  hasActive(recursively = true) {
	    const has = this.list().some(item => item.isActive());
	    if (has) {
	      return true;
	    }
	    if (!recursively) {
	      return false;
	    }
	    return this.list().some(item => item.getCollection().hasActive());
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _node)[_node] = main_core.Tag.render(_t || (_t = _`<div class="ui-sidepanel-menu-items"></div>`));
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _node)[_node].innerHTML = '';
	    babelHelpers.classPrivateFieldLooseBase(this, _list)[_list].forEach(item => babelHelpers.classPrivateFieldLooseBase(this, _node)[_node].appendChild(item.render()));
	    return babelHelpers.classPrivateFieldLooseBase(this, _node)[_node];
	  }
	}
	function _addSilent2(itemOptions) {
	  if (itemOptions.active) {
	    itemOptions.active = !this.hasActive();
	  } else {
	    itemOptions.active = false;
	  }
	  const item = new Item(itemOptions);
	  babelHelpers.classPrivateFieldLooseBase(this, _list)[_list].push(item);
	  item.subscribe('change:active', () => {
	    if (item.isActive() && item.getCollection().isEmpty()) {
	      this.syncActive(item);
	    }
	  });
	  item.subscribe('sync:active', () => this.syncActive(item));
	  item.subscribe('click', data => this.emit('click', data));
	  item.subscribe('change', () => setTimeout(() => this.render(), 0));
	  return item;
	}

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3;
	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _label = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("label");
	var _active = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("active");
	var _notice = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notice");
	var _onclick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onclick");
	var _collection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collection");
	var _node$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _actions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actions");
	var _moduleId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("moduleId");
	var _emitChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitChange");
	var _handleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleClick");
	var _showActionMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showActionMenu");
	class Item extends main_core_events.EventEmitter {
	  constructor(options) {
	    super(options);
	    Object.defineProperty(this, _showActionMenu, {
	      value: _showActionMenu2
	    });
	    Object.defineProperty(this, _handleClick, {
	      value: _handleClick2
	    });
	    Object.defineProperty(this, _emitChange, {
	      value: _emitChange2
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _label, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _active, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _notice, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onclick, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _collection, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _node$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _actions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _moduleId, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('ui:sidepanel:menu:item');
	    babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection] = new Collection();
	    this.setLabel(options.label).setActive(options.active).setNotice(options.notice).setId(options.id).setItems(options.items).setClickHandler(options.onclick).setActions(options.actions).setModuleId(options.moduleId);
	    babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].subscribe('sync:active', () => this.emit('sync:active'));
	    babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].subscribe('click', event => this.emit('click', event));
	  }
	  setLabel(label = '') {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _label)[_label] === label) {
	      return this;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _label)[_label] = label;
	    babelHelpers.classPrivateFieldLooseBase(this, _emitChange)[_emitChange]();
	    return this;
	  }
	  setId(id) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] === id) {
	      return this;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = id;
	    babelHelpers.classPrivateFieldLooseBase(this, _emitChange)[_emitChange]();
	    return this;
	  }
	  setActive(mode = true) {
	    mode = !!mode;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _active)[_active] === mode) {
	      return this;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _active)[_active] = mode;
	    babelHelpers.classPrivateFieldLooseBase(this, _emitChange)[_emitChange]({
	      active: babelHelpers.classPrivateFieldLooseBase(this, _active)[_active]
	    }, 'active');
	    return this;
	  }
	  setNotice(mode = false) {
	    babelHelpers.classPrivateFieldLooseBase(this, _notice)[_notice] = !!mode;
	    babelHelpers.classPrivateFieldLooseBase(this, _emitChange)[_emitChange]();
	    return this;
	  }
	  setClickHandler(handler) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onclick)[_onclick] = handler;
	    return this;
	  }
	  setModuleId(moduleId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _moduleId)[_moduleId] = moduleId;
	    return this;
	  }
	  setActions(actions = []) {
	    babelHelpers.classPrivateFieldLooseBase(this, _actions)[_actions] = actions;
	    return this;
	  }
	  setItems(items = []) {
	    babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].setItems(items || []);
	    babelHelpers.classPrivateFieldLooseBase(this, _emitChange)[_emitChange]();
	    return this;
	  }
	  getCollection() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection];
	  }
	  getLabel() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _label)[_label];
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  getModuleId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _moduleId)[_moduleId];
	  }
	  getClickHandler() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _onclick)[_onclick];
	  }
	  isActive() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _active)[_active];
	  }
	  hasNotice() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _notice)[_notice];
	  }
	  hasActions() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _actions)[_actions].length > 0;
	  }
	  change(options) {
	    if (!main_core.Type.isUndefined(options.label)) {
	      this.setLabel(options.label);
	    }
	    if (!main_core.Type.isUndefined(options.active)) {
	      this.setActive(options.active);
	    }
	    if (!main_core.Type.isUndefined(options.notice)) {
	      this.setNotice(options.notice);
	    }
	    if (!main_core.Type.isUndefined(options.id)) {
	      this.setId(options.id);
	    }
	    if (!main_core.Type.isUndefined(options.items)) {
	      this.setItems(options.items);
	    }
	    if (!main_core.Type.isUndefined(options.onclick)) {
	      this.setClickHandler(options.onclick);
	    }
	    if (!main_core.Type.isUndefined(options.actions)) {
	      this.setActions(options.actions);
	    }
	  }
	  remove() {
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1]);
	    babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1] = null;
	  }
	  render() {
	    const isEmpty = babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].isEmpty();
	    const classes = [];
	    if (babelHelpers.classPrivateFieldLooseBase(this, _active)[_active]) {
	      if (isEmpty) {
	        classes.push('ui-sidepanel-menu-active');
	      } else {
	        classes.push('ui-sidepanel-menu-expand');
	      }
	    }
	    const actionText = main_core.Loc.getMessage('UI_SIDEPANEL_MENU_JS_' + (this.isActive() ? 'COLLAPSE' : 'EXPAND'));
	    babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1] = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<li class="ui-sidepanel-menu-item ${0}">
				<a
					class="ui-sidepanel-menu-link"
					onclick="${0}"
					title="${0}"
				>
					<div class="ui-sidepanel-menu-link-text">${0}</div>
					${0}
					${0}
					${0}
				</a>
			</li>
		`), classes.join(' '), babelHelpers.classPrivateFieldLooseBase(this, _handleClick)[_handleClick].bind(this), main_core.Tag.safe(_t2 || (_t2 = _$1`${0}`), babelHelpers.classPrivateFieldLooseBase(this, _label)[_label]), main_core.Tag.safe(_t3 || (_t3 = _$1`${0}`), babelHelpers.classPrivateFieldLooseBase(this, _label)[_label]), !isEmpty ? `<div class="ui-sidepanel-toggle-btn">${actionText}</div>` : '', babelHelpers.classPrivateFieldLooseBase(this, _notice)[_notice] ? '<span class="ui-sidepanel-menu-notice-icon"></span>' : '', this.hasActions() ? '<span class="ui-sidepanel-menu-action-icon ui-btn ui-btn-link ui-btn-icon-edit"></span>' : '');
	    if (this.hasActions()) {
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1].querySelector('.ui-sidepanel-menu-action-icon'), 'click', babelHelpers.classPrivateFieldLooseBase(this, _showActionMenu)[_showActionMenu].bind(this));
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].isEmpty()) {
	      main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].render(), babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1]);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _node$1)[_node$1];
	  }
	}
	function _emitChange2(data = {}, type = null) {
	  this.emit('change', data);
	  if (type) {
	    this.emit('change:' + type, data);
	  }
	}
	function _handleClick2(event) {
	  event.preventDefault();
	  event.stopPropagation();
	  this.setActive(babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].isEmpty() || !this.isActive());
	  this.emit('click', {
	    item: this
	  });
	  if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _onclick)[_onclick])) {
	    babelHelpers.classPrivateFieldLooseBase(this, _onclick)[_onclick].apply(this);
	  }
	}
	function _showActionMenu2(event) {
	  event.preventDefault();
	  event.stopPropagation();
	  if (this.actionsMenu) {
	    this.actionsMenu.getPopupWindow().close();
	    return;
	  }
	  const targetIcon = event.currentTarget;
	  main_core.Dom.addClass(targetIcon, '--hover');
	  main_core.Dom.addClass(targetIcon.parentNode, '--hover');
	  this.actionsMenu = new main_popup.Menu({
	    id: `ui-sidepanel-menu-item-actions-${this.getId()}`,
	    bindElement: targetIcon
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _actions)[_actions].forEach(action => {
	    this.actionsMenu.addMenuItem({
	      text: action.label,
	      onclick: (event, menuItem) => {
	        menuItem.getMenuWindow().close();
	        action.onclick(this);
	      }
	    });
	  });
	  this.actionsMenu.getPopupWindow().subscribe('onClose', () => {
	    main_core.Dom.removeClass(targetIcon, '--hover');
	    main_core.Dom.removeClass(targetIcon.parentNode, '--hover');
	    this.actionsMenu.destroy();
	    this.actionsMenu = null;
	  });
	  this.actionsMenu.show();
	}

	let _$2 = t => t,
	  _t$2;
	var _node$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	class Menu extends Collection {
	  constructor(options = {}) {
	    super({
	      items: options.items
	    });
	    Object.defineProperty(this, _node$2, {
	      writable: true,
	      value: void 0
	    });
	    if (!this.hasActive()) {
	      this.setActiveFirstItem();
	    }
	  }
	  render() {
	    const itemsNode = super.render();
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _node$2)[_node$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _node$2)[_node$2] = main_core.Tag.render(_t$2 || (_t$2 = _$2`<ul class="ui-sidepanel-menu"></ul>`));
	      babelHelpers.classPrivateFieldLooseBase(this, _node$2)[_node$2].appendChild(itemsNode);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _node$2)[_node$2];
	  }
	  renderTo(target) {
	    const node = this.render();
	    target.appendChild(node);
	    return node;
	  }
	}

	exports.Item = Item;
	exports.Menu = Menu;

}((this.BX.UI.SidePanel = this.BX.UI.SidePanel || {}),BX,BX.Main,BX.Event,BX));
//# sourceMappingURL=sidepanel.menu.bundle.js.map
