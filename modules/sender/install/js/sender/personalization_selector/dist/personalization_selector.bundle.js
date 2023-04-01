this.BX = this.BX || {};
(function (exports,main_core,ui_entitySelector) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _dialog = /*#__PURE__*/new WeakMap();

	var _menuButton = /*#__PURE__*/new WeakMap();

	var _targetInput = /*#__PURE__*/new WeakMap();

	var _onItemClick = /*#__PURE__*/new WeakMap();

	var _fields = /*#__PURE__*/new WeakMap();

	var _prepareItem = /*#__PURE__*/new WeakSet();

	var PersonalizationSelector = /*#__PURE__*/function () {
	  function PersonalizationSelector(options) {
	    babelHelpers.classCallCheck(this, PersonalizationSelector);

	    _classPrivateMethodInitSpec(this, _prepareItem);

	    _classPrivateFieldInitSpec(this, _dialog, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _menuButton, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _targetInput, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _onItemClick, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _fields, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _menuButton, options.button);
	    babelHelpers.classPrivateFieldSet(this, _targetInput, options.targetInput);
	    babelHelpers.classPrivateFieldSet(this, _fields, options.fields);
	    babelHelpers.classPrivateFieldSet(this, _onItemClick, options.onItemClick || {});
	    main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _menuButton), 'click', this.openMenu.bind(this));
	  }

	  babelHelpers.createClass(PersonalizationSelector, [{
	    key: "setName",
	    value: function setName(name) {
	      if (main_core.Type.isString(name)) {
	        this.name = name;
	      }
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.name;
	    }
	  }, {
	    key: "onKeyDown",
	    value: function onKeyDown(container, e) {
	      if (e.keyCode == 45 && e.altKey === false && e.ctrlKey === false && e.shiftKey === false) {
	        this.openMenu(e);
	        e.preventDefault();
	      }
	    }
	  }, {
	    key: "openMenu",
	    value: function openMenu(e) {
	      var _this = this;

	      if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	        babelHelpers.classPrivateFieldGet(this, _dialog).show();
	        return;
	      }

	      var menuItems = [];
	      var menuGroups = {
	        'ROOT': {
	          title: main_core.Loc.getMessage('SENDER_PERSONALIZATION_SELECTOR_ROOT'),
	          entityId: 'sender',
	          tabs: 'recents',
	          id: 'ROOT',
	          children: []
	        }
	      };

	      _classPrivateMethodGet(this, _prepareItem, _prepareItem2).call(this, babelHelpers.classPrivateFieldGet(this, _fields), menuGroups);

	      if (Object.keys(menuGroups).length < 2) {
	        if (menuGroups['ROOT']['children'].length > 0) {
	          menuItems = menuGroups['ROOT']['children'];
	        }
	      } else {
	        if (menuGroups['ROOT']['children'].length > 0) {
	          menuItems.push(menuGroups['ROOT']);
	        }

	        delete menuGroups['ROOT'];

	        for (var groupKey in menuGroups) {
	          if (menuGroups.hasOwnProperty(groupKey) && menuGroups[groupKey]['children'].length > 0) {
	            menuItems.push(menuGroups[groupKey]);
	          }
	        }
	      }

	      babelHelpers.classPrivateFieldSet(this, _dialog, new ui_entitySelector.EntitySelector.Dialog({
	        targetNode: babelHelpers.classPrivateFieldGet(this, _menuButton),
	        tagSelectorOptions: {
	          textBoxWidth: 500
	        },
	        width: 500,
	        height: 300,
	        multiple: false,
	        dropdownMode: true,
	        enableSearch: true,
	        items: this.injectDialogMenuTitles(menuItems.reverse()),
	        showAvatars: false,
	        events: {
	          'Item:onBeforeSelect': main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _onItemClick)) ? babelHelpers.classPrivateFieldGet(this, _onItemClick) : function (event) {
	            event.preventDefault();

	            _this.onFieldSelect(event.getData().item.getCustomData().get('property'));
	          }
	        },
	        compactView: true
	      }));
	      babelHelpers.classPrivateFieldGet(this, _dialog).show();
	    }
	  }, {
	    key: "injectDialogMenuTitles",
	    value: function injectDialogMenuTitles(items) {
	      var _this2 = this;

	      items.forEach(function (parent) {
	        if (main_core.Type.isArray(parent.children)) {
	          parent.searchable = false;

	          _this2.injectDialogMenuSupertitles(parent.title, parent.children);
	        }
	      }, this);
	      return items;
	    }
	  }, {
	    key: "injectDialogMenuSupertitles",
	    value: function injectDialogMenuSupertitles(title, children) {
	      children.forEach(function (child) {
	        if (!child.supertitle) {
	          child.supertitle = title;
	        }

	        if (main_core.Type.isArray(child.children)) {
	          child.searchable = false;
	          this.injectDialogMenuSupertitles(child.title, child.children);
	        }
	      }, this);
	    }
	  }, {
	    key: "onFieldSelect",
	    value: function onFieldSelect(field) {
	      if (!field) {
	        return;
	      }

	      babelHelpers.classPrivateFieldGet(this, _targetInput).value = babelHelpers.classPrivateFieldGet(this, _targetInput).value + field.id;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	        babelHelpers.classPrivateFieldGet(this, _dialog).destroy();
	      }
	    }
	  }]);
	  return PersonalizationSelector;
	}();

	function _prepareItem2(fields, menuGroups) {
	  var _this3 = this;

	  fields.forEach(function (field) {
	    var groupKey = field.id.indexOf('.') < 0 ? field.items && field.items.length > 0 ? field.id : 'ROOT' : field.id.split('.')[0] + '#';

	    if (!field.text && !field.title) {
	      return;
	    }

	    if (!menuGroups[groupKey]) {
	      menuGroups[groupKey] = {
	        title: field.text || field.title,
	        entityId: 'sender',
	        tabs: 'recents',
	        tabId: 'sender',
	        id: field.id,
	        children: []
	      };
	    }

	    if (field.items && field.items.length > 0) {
	      _classPrivateMethodGet(_this3, _prepareItem, _prepareItem2).call(_this3, field.items, menuGroups);

	      return;
	    }

	    menuGroups[groupKey]['children'].push({
	      title: field.text || field.title,
	      customData: {
	        property: field
	      },
	      entityId: 'sender',
	      tabs: 'recents',
	      id: field.id
	    });
	  });
	}

	exports.PersonalizationSelector = PersonalizationSelector;

}((this.BX.Sender = this.BX.Sender || {}),BX,BX.UI.EntitySelector));
//# sourceMappingURL=personalization_selector.bundle.js.map
