this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core,main_core_events,main_popup,ui_draganddrop_draggable) {
	'use strict';

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");

	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");

	var _menu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menu");

	var _bindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");

	var _draggable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("draggable");

	var _promise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("promise");

	var _closeResolver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeResolver");

	var _maxVisibleItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxVisibleItems");

	var _resolveWithCancel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveWithCancel");

	var _resolveWithItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveWithItems");

	var _getItemById = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemById");

	var _createMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMenu");

	var _getSaveItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSaveItem");

	var _getCancelItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCancelItem");

	var _save = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("save");

	var _cancel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cancel");

	var _getMenuItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenuItem");

	var _getVisibleSectionTitleItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getVisibleSectionTitleItem");

	var _getHiddenSectionTitleItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHiddenSectionTitleItem");

	var _initDraggable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initDraggable");

	var _saveItemsFromMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveItemsFromMenu");

	var _getItemNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getItemNode");

	var _getHiddenSectionTitleNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getHiddenSectionTitleNode");

	var _adjustMaxVisibleItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustMaxVisibleItems");

	class Menu extends main_core_events.EventEmitter {
	  constructor(parameters) {
	    super();
	    Object.defineProperty(this, _adjustMaxVisibleItems, {
	      value: _adjustMaxVisibleItems2
	    });
	    Object.defineProperty(this, _getHiddenSectionTitleNode, {
	      value: _getHiddenSectionTitleNode2
	    });
	    Object.defineProperty(this, _getItemNode, {
	      value: _getItemNode2
	    });
	    Object.defineProperty(this, _saveItemsFromMenu, {
	      value: _saveItemsFromMenu2
	    });
	    Object.defineProperty(this, _initDraggable, {
	      value: _initDraggable2
	    });
	    Object.defineProperty(this, _getHiddenSectionTitleItem, {
	      value: _getHiddenSectionTitleItem2
	    });
	    Object.defineProperty(this, _getVisibleSectionTitleItem, {
	      value: _getVisibleSectionTitleItem2
	    });
	    Object.defineProperty(this, _getMenuItem, {
	      value: _getMenuItem2
	    });
	    Object.defineProperty(this, _cancel, {
	      value: _cancel2
	    });
	    Object.defineProperty(this, _save, {
	      value: _save2
	    });
	    Object.defineProperty(this, _getCancelItem, {
	      value: _getCancelItem2
	    });
	    Object.defineProperty(this, _getSaveItem, {
	      value: _getSaveItem2
	    });
	    Object.defineProperty(this, _createMenu, {
	      value: _createMenu2
	    });
	    Object.defineProperty(this, _getItemById, {
	      value: _getItemById2
	    });
	    Object.defineProperty(this, _resolveWithItems, {
	      value: _resolveWithItems2
	    });
	    Object.defineProperty(this, _resolveWithCancel, {
	      value: _resolveWithCancel2
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _menu, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _draggable, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _promise, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _closeResolver, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _maxVisibleItems, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Type.isStringFilled(parameters.id) ? parameters.id : 'settings-popup-' + Math.random().toString().substring(2);
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = parameters.items;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement] = parameters.bindElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleItems)[_maxVisibleItems] = Number(parameters.maxVisibleItems);

	    babelHelpers.classPrivateFieldLooseBase(this, _createMenu)[_createMenu]();

	    this.setEventNamespace('BX.UI.MenuConfigurable.Menu');
	  }

	  open(bindElement) {
	    var _babelHelpers$classPr2;

	    if (bindElement) {
	      var _babelHelpers$classPr;

	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) == null ? void 0 : _babelHelpers$classPr.getPopupWindow().setBindElement(bindElement);
	    }

	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) == null ? void 0 : _babelHelpers$classPr2.show();

	    if (!babelHelpers.classPrivateFieldLooseBase(this, _promise)[_promise]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _promise)[_promise] = new Promise(resolve => {
	        babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver] = resolve;
	      });
	    }

	    return babelHelpers.classPrivateFieldLooseBase(this, _promise)[_promise];
	  }

	  close() {
	    babelHelpers.classPrivateFieldLooseBase(this, _createMenu)[_createMenu]();

	    babelHelpers.classPrivateFieldLooseBase(this, _resolveWithCancel)[_resolveWithCancel]();
	  }

	  setItems(items) {
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = items;
	    return this;
	  }

	  getItemsFromMenu() {
	    const items = [];
	    let isHidden = false;

	    babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer.querySelectorAll('.menu-configurable-item').forEach(node => {
	      if (node.classList.contains('menu-configurable-hidden-section-title')) {
	        isHidden = true;
	      }

	      const itemId = node.dataset.id;

	      const item = babelHelpers.classPrivateFieldLooseBase(this, _getItemById)[_getItemById](itemId);

	      if (item) {
	        const clonedItem = main_core.Runtime.clone(item);
	        clonedItem.isHidden = isHidden;
	        items.push(clonedItem);
	      }
	    });

	    return items;
	  }

	}

	function _resolveWithCancel2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _promise)[_promise] = null;

	  if (babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver]({
	      isCanceled: true
	    });
	  }

	  babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver] = null;
	}

	function _resolveWithItems2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _promise)[_promise] = null;

	  if (babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver]({
	      items: babelHelpers.classPrivateFieldLooseBase(this, _items)[_items]
	    });
	  }

	  babelHelpers.classPrivateFieldLooseBase(this, _closeResolver)[_closeResolver] = null;
	}

	function _getItemById2(id) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].find(item => item.id === id);
	}

	function _createMenu2(bindElement) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].destroy();

	    babelHelpers.classPrivateFieldLooseBase(this, _draggable)[_draggable] = null;
	  }

	  const menuItems = [];
	  menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getVisibleSectionTitleItem)[_getVisibleSectionTitleItem]());

	  const visibleItems = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].filter(item => !item.isHidden);

	  const hiddenItems = babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].filter(item => item.isHidden);

	  visibleItems.forEach(item => {
	    menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getMenuItem)[_getMenuItem](item));
	  });
	  menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getHiddenSectionTitleItem)[_getHiddenSectionTitleItem]());
	  hiddenItems.forEach(item => {
	    menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getMenuItem)[_getMenuItem](item));
	  });
	  menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getSaveItem)[_getSaveItem]());
	  menuItems.push(babelHelpers.classPrivateFieldLooseBase(this, _getCancelItem)[_getCancelItem]());
	  babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu] = main_popup.MenuManager.create({
	    id: babelHelpers.classPrivateFieldLooseBase(this, _id)[_id],
	    items: menuItems,
	    bindElement: bindElement != null ? bindElement : babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement],
	    events: {
	      onClose: this.close.bind(this)
	    }
	  });

	  babelHelpers.classPrivateFieldLooseBase(this, _initDraggable)[_initDraggable]();

	  return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu];
	}

	function _getSaveItem2() {
	  return {
	    text: main_core.Loc.getMessage('UI_JS_MENU_CONFIGURABLE_SAVE'),
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _save)[_save].bind(this)
	  };
	}

	function _getCancelItem2() {
	  return {
	    text: main_core.Loc.getMessage('UI_JS_MENU_CONFIGURABLE_CANCEL'),
	    onclick: babelHelpers.classPrivateFieldLooseBase(this, _cancel)[_cancel].bind(this)
	  };
	}

	function _save2() {
	  const event = new main_core_events.BaseEvent();
	  this.emit('Save', event);

	  if (event.isDefaultPrevented()) {
	    return;
	  }

	  babelHelpers.classPrivateFieldLooseBase(this, _saveItemsFromMenu)[_saveItemsFromMenu]();

	  babelHelpers.classPrivateFieldLooseBase(this, _resolveWithItems)[_resolveWithItems]();

	  babelHelpers.classPrivateFieldLooseBase(this, _createMenu)[_createMenu]();
	}

	function _cancel2() {
	  const event = new main_core_events.BaseEvent();
	  this.emit('Cancel', event);

	  if (event.isDefaultPrevented()) {
	    return;
	  }

	  this.close();
	}

	function _getMenuItem2(item) {
	  return {
	    id: item.id,
	    text: item.text,
	    html: item.html,
	    className: 'menu-configurable-item',
	    dataset: {
	      id: item.id
	    }
	  };
	}

	function _getVisibleSectionTitleItem2() {
	  return {
	    delimiter: true,
	    html: '<span>' + main_core.Loc.getMessage('UI_JS_MENU_CONFIGURABLE_VISIBLE') + '</span>',
	    className: 'menu-configurable-visible-section-title menu-configurable-delimiter-item'
	  };
	}

	function _getHiddenSectionTitleItem2() {
	  return {
	    delimiter: true,
	    html: '<span>' + main_core.Loc.getMessage('UI_JS_MENU_CONFIGURABLE_HIDDEN') + '</span>',
	    className: 'menu-configurable-hidden-section-title menu-configurable-delimiter-item menu-configurable-item'
	  };
	}

	function _initDraggable2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _draggable)[_draggable] = new ui_draganddrop_draggable.Draggable({
	    container: babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer,
	    draggable: '.menu-configurable-item',
	    dragElement: '.menu-popup-item-icon',
	    type: ui_draganddrop_draggable.Draggable.MOVE
	  });

	  babelHelpers.classPrivateFieldLooseBase(this, _draggable)[_draggable].subscribe('end', babelHelpers.classPrivateFieldLooseBase(this, _adjustMaxVisibleItems)[_adjustMaxVisibleItems].bind(this));
	}

	function _saveItemsFromMenu2() {
	  this.setItems(this.getItemsFromMenu());
	}

	function _getItemNode2(item) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer.querySelector('.menu-configurable-item[data-id="' + item.id + '"]');
	}

	function _getHiddenSectionTitleNode2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _menu)[_menu].itemsContainer.querySelector('.menu-configurable-hidden-section-title');
	}

	function _adjustMaxVisibleItems2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleItems)[_maxVisibleItems] <= 0) {
	    return;
	  }

	  const runtimeItems = this.getItemsFromMenu();
	  const visibleItems = runtimeItems.filter(item => !item.isHidden);
	  const visibleItemsCount = visibleItems.length;

	  const hiddenSectionTitleNode = babelHelpers.classPrivateFieldLooseBase(this, _getHiddenSectionTitleNode)[_getHiddenSectionTitleNode]();

	  if (hiddenSectionTitleNode && visibleItemsCount > babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleItems)[_maxVisibleItems]) {
	    for (let index = babelHelpers.classPrivateFieldLooseBase(this, _maxVisibleItems)[_maxVisibleItems]; index < visibleItemsCount; index++) {
	      const item = visibleItems[index];

	      const node = babelHelpers.classPrivateFieldLooseBase(this, _getItemNode)[_getItemNode](item);

	      if (node) {
	        main_core.Dom.insertAfter(node, hiddenSectionTitleNode);
	      }
	    }
	  }
	}

	exports.Menu = Menu;

}((this.BX.UI.MenuConfigurable = this.BX.UI.MenuConfigurable || {}),BX,BX.Event,BX.Main,BX.UI.DragAndDrop));
//# sourceMappingURL=menu-configurable.bundle.js.map
