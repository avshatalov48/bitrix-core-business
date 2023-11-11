/* eslint-disable */
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var namespace = main_core.Reflection.namespace('BX.Ui.Form');
	var _items = /*#__PURE__*/new WeakMap();
	var Config = function Config(options) {
	  var _this = this;
	  babelHelpers.classCallCheck(this, Config);
	  babelHelpers.defineProperty(this, "isOpen", false);
	  _classPrivateFieldInitSpec(this, _items, {
	    writable: true,
	    value: []
	  });
	  babelHelpers.defineProperty(this, "popupContainer", null);
	  options.scopes.forEach(function (item) {
	    item.config = _this;
	    babelHelpers.classPrivateFieldGet(_this, _items).push(new BX.Ui.Form.ConfigItem(item));
	  }, this);
	  this.popupContainer = options.componentId;
	};
	var _scopeId = /*#__PURE__*/new WeakMap();
	var _members = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _selectedItems = /*#__PURE__*/new WeakMap();
	var _moduleId = /*#__PURE__*/new WeakMap();
	var _openPopupEvent = /*#__PURE__*/new WeakMap();
	var _reinitDialogEvent = /*#__PURE__*/new WeakMap();
	var _drawMembers = /*#__PURE__*/new WeakSet();
	var _createMember = /*#__PURE__*/new WeakSet();
	var _createPlusButton = /*#__PURE__*/new WeakSet();
	var _showPopup = /*#__PURE__*/new WeakSet();
	var _addEvents = /*#__PURE__*/new WeakSet();
	var _getSelectedItems = /*#__PURE__*/new WeakSet();
	var _removeEvents = /*#__PURE__*/new WeakSet();
	var _adjust = /*#__PURE__*/new WeakSet();
	var ConfigItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ConfigItem, _EventEmitter);
	  function ConfigItem(options) {
	    var _this2;
	    babelHelpers.classCallCheck(this, ConfigItem);
	    _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfigItem).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _adjust);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _removeEvents);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _getSelectedItems);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _addEvents);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _showPopup);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _createPlusButton);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _createMember);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this2), _drawMembers);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _scopeId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _members, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _selectedItems, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _moduleId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _openPopupEvent, {
	      writable: true,
	      value: 'BX.Ui.Form.ConfigItem:onComponentOpen'
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this2), _reinitDialogEvent, {
	      writable: true,
	      value: 'BX.Main.SelectorV2:reInitDialog'
	    });
	    _this2.setEventNamespace('BX.Ui.Form');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this2), _scopeId, options['scopeId'] || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this2), _members, options['members'] || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this2), _node, BX("ui-editor-config-".concat(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this2), _scopeId))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this2), _selectedItems, null);
	    _this2.drawingIconsLimit = options['drawingIconsLimit'] || 10;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this2), _moduleId, options['moduleId'] || null);
	    _this2.config = options['config'] || null;
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this2), _drawMembers, _drawMembers2).call(babelHelpers.assertThisInitialized(_this2));
	    _this2.addToAccessCodesHandler = BX.delegate(_this2.onAddToAccessCodes, babelHelpers.assertThisInitialized(_this2));
	    _this2.removeFromAccessCodesHandler = BX.delegate(_this2.onRemoveFromAccessCodes, babelHelpers.assertThisInitialized(_this2));
	    _this2.closePopupHandler = BX.delegate(_this2.onClosePopup, babelHelpers.assertThisInitialized(_this2));
	    BX.addCustomEvent('Grid::updated', _this2.onGridUpdate.bind(babelHelpers.assertThisInitialized(_this2)));
	    setTimeout(function () {
	      BX.onCustomEvent('BX.Ui.Form.ConfigItem:onComponentLoad', [{
	        openDialogWhenInit: false
	      }]);
	    }, 100);
	    return _this2;
	  }
	  babelHelpers.createClass(ConfigItem, [{
	    key: "onGridUpdate",
	    value: function onGridUpdate(params) {
	      _classPrivateMethodGet(this, _adjust, _adjust2).call(this);
	    }
	  }, {
	    key: "onClosePopup",
	    value: function onClosePopup(event) {
	      this.config.isOpen = false;
	      _classPrivateMethodGet(this, _removeEvents, _removeEvents2).call(this);
	    }
	  }, {
	    key: "onAddToAccessCodes",
	    value: function onAddToAccessCodes(event) {
	      var _this3 = this;
	      BX.ajax.runComponentAction('bitrix:ui.form.config', 'updateScopeAccessCodes', {
	        'data': {
	          moduleId: babelHelpers.classPrivateFieldGet(this, _moduleId),
	          scopeId: babelHelpers.classPrivateFieldGet(this, _scopeId),
	          accessCodes: _classPrivateMethodGet(this, _getSelectedItems, _getSelectedItems2).call(this)
	        }
	      }).then(function (result) {
	        _classPrivateMethodGet(_this3, _adjust, _adjust2).call(_this3, result.data);
	      });
	    }
	  }, {
	    key: "onRemoveFromAccessCodes",
	    value: function onRemoveFromAccessCodes(event) {
	      this.onAddToAccessCodes(event);
	    }
	  }], [{
	    key: "onMemberSelect",
	    value: function onMemberSelect(params) {
	      if (params.state === 'select') {
	        //BX.onCustomEvent('BX.Ui.Form.ConfigItem:addToAccessCodes', params);
	        main_core_events.EventEmitter.emit('BX.Ui.Form.ConfigItem:addToAccessCodes', params);
	      }
	    }
	  }, {
	    key: "onDialogClose",
	    value: function onDialogClose(params) {
	      //BX.onCustomEvent('BX.Ui.Form.ConfigItem:closePopup', params);
	      main_core_events.EventEmitter.emit('BX.Ui.Form.ConfigItem:closePopup', params);
	    }
	  }, {
	    key: "onMemberUnselect",
	    value: function onMemberUnselect(params) {
	      main_core_events.EventEmitter.emit('BX.Ui.Form.ConfigItem:removeFromAccessCodes', params);
	      //BX.onCustomEvent('BX.Ui.Form.ConfigItem:removeFromAccessCodes', params);
	    }
	  }]);
	  return ConfigItem;
	}(main_core_events.EventEmitter);
	function _drawMembers2() {
	  if (babelHelpers.classPrivateFieldGet(this, _members)) {
	    var i = 0;
	    for (var member in babelHelpers.classPrivateFieldGet(this, _members)) {
	      var item = babelHelpers.classPrivateFieldGet(this, _members)[member];
	      babelHelpers.classPrivateFieldGet(this, _node).appendChild(_classPrivateMethodGet(this, _createMember, _createMember2).call(this, item));
	      if (i++ > this.drawingIconsLimit) {
	        break;
	      }
	    }
	  }
	  babelHelpers.classPrivateFieldGet(this, _node).appendChild(_classPrivateMethodGet(this, _createPlusButton, _createPlusButton2).call(this));
	}
	function _createMember2(member) {
	  var children = member.avatar ? main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-editor-config-item-avatar\"  title=\"", "\" style=\"background-image: url('", "')\"></a>"])), member.url, main_core.Text.encode(member.name), member.avatar) : main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-icon ui-icon-xs ui-icon-common-user\" title=\"", "\"><i></i></a>"])), member.url, main_core.Text.encode(member.name));
	  return main_core.Dom.create('div', {
	    attrs: {
	      "class": 'ui-editor-config-item'
	    },
	    children: [children]
	  });
	}
	function _createPlusButton2() {
	  var _this4 = this;
	  return main_core.Dom.create('div', {
	    events: {
	      click: function click(event) {
	        if (!_this4.config.isOpen) {
	          _classPrivateMethodGet(_this4, _showPopup, _showPopup2).call(_this4);
	        }
	      }
	    },
	    attrs: {
	      "class": 'ui-editor-config-item ui-editor-config-item--add'
	    }
	  });
	}
	function _showPopup2() {
	  this.config.isOpen = true;
	  _classPrivateMethodGet(this, _addEvents, _addEvents2).call(this);
	  var selectorInstance = BX.Main.selectorManagerV2.controls[this.config.popupContainer].selectorInstance;
	  selectorInstance.itemsSelected = {};
	  BX.onCustomEvent(babelHelpers.classPrivateFieldGet(this, _openPopupEvent), [{
	    id: this.config.popupContainer,
	    bindNode: babelHelpers.classPrivateFieldGet(this, _node)
	  }]);
	  BX.onCustomEvent(babelHelpers.classPrivateFieldGet(this, _reinitDialogEvent), [{
	    selectorId: this.config.popupContainer,
	    selectedItems: _classPrivateMethodGet(this, _getSelectedItems, _getSelectedItems2).call(this)
	  }]);
	}
	function _addEvents2() {
	  main_core_events.EventEmitter.subscribe('BX.Ui.Form.ConfigItem:addToAccessCodes', this.addToAccessCodesHandler);
	  main_core_events.EventEmitter.subscribe('BX.Ui.Form.ConfigItem:removeFromAccessCodes', this.removeFromAccessCodesHandler);
	  main_core_events.EventEmitter.subscribe('BX.Ui.Form.ConfigItem:closePopup', this.closePopupHandler);
	}
	function _getSelectedItems2() {
	  if (babelHelpers.classPrivateFieldGet(this, _members) && !babelHelpers.classPrivateFieldGet(this, _selectedItems)) {
	    var items = {};
	    for (var member in babelHelpers.classPrivateFieldGet(this, _members)) {
	      items[member] = babelHelpers.classPrivateFieldGet(this, _members)[member].type.toUpperCase();
	    }
	    babelHelpers.classPrivateFieldSet(this, _selectedItems, items);
	  }
	  return babelHelpers.classPrivateFieldGet(this, _selectedItems) || {};
	}
	function _removeEvents2() {
	  main_core_events.EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:addToAccessCodes', this.addToAccessCodesHandler);
	  main_core_events.EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:removeFromAccessCodes', this.removeFromAccessCodesHandler);
	  main_core_events.EventEmitter.unsubscribe('BX.Ui.Form.ConfigItem:closePopup', this.closePopupHandler);
	}
	function _adjust2(members) {
	  babelHelpers.classPrivateFieldSet(this, _node, BX("ui-editor-config-".concat(babelHelpers.classPrivateFieldGet(this, _scopeId))));
	  if (members) {
	    babelHelpers.classPrivateFieldSet(this, _members, members);
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _node)) {
	    while (babelHelpers.classPrivateFieldGet(this, _node).firstChild) {
	      babelHelpers.classPrivateFieldGet(this, _node).removeChild(babelHelpers.classPrivateFieldGet(this, _node).firstChild);
	    }
	    _classPrivateMethodGet(this, _drawMembers, _drawMembers2).call(this);
	  }
	}
	namespace.Config = Config;
	namespace.ConfigItem = ConfigItem;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
