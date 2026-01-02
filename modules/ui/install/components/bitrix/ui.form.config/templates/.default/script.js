/* eslint-disable */
(function (exports,main_core_events,ui_entitySelector,main_core,ui_buttons,ui_dialogs_messagebox) {
	'use strict';

	/**
	 * @bxjs_lang_path template.php
	 */

	var Config = /*#__PURE__*/function () {
	  function Config() {
	    babelHelpers.classCallCheck(this, Config);
	  }
	  babelHelpers.createClass(Config, null, [{
	    key: "popupIsOpen",
	    value: function popupIsOpen() {
	      return this.isOpen;
	    }
	  }, {
	    key: "setPopupClose",
	    value: function setPopupClose() {
	      this.isOpen = false;
	    }
	  }, {
	    key: "setPopupOpen",
	    value: function setPopupOpen() {
	      this.isOpen = true;
	    }
	  }, {
	    key: "isSubscribed",
	    value: function isSubscribed(scopeId) {
	      return Config.subscribedItems.includes(scopeId);
	    }
	  }, {
	    key: "addSubscribed",
	    value: function addSubscribed(scopeId) {
	      Config.subscribedItems.push(scopeId);
	    }
	  }, {
	    key: "showConfirmDialog",
	    value: function showConfirmDialog(title, acceptButtonText, acceptFunc) {
	      ui_dialogs_messagebox.MessageBox.confirm(title, acceptFunc, acceptButtonText, undefined, undefined, true);
	    }
	  }]);
	  return Config;
	}();
	babelHelpers.defineProperty(Config, "isOpen", false);
	babelHelpers.defineProperty(Config, "subscribedItems", []);

	var _templateObject, _templateObject2, _templateObject3;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * @bxjs_lang_path template.php
	 */

	var MAX_SHOWN_MEMBERS = 3;
	var _scopeId = /*#__PURE__*/new WeakMap();
	var _members = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _selectedItems = /*#__PURE__*/new WeakMap();
	var _moduleId = /*#__PURE__*/new WeakMap();
	var _config = /*#__PURE__*/new WeakMap();
	var _entityTypeId = /*#__PURE__*/new WeakMap();
	var _memberSelector = /*#__PURE__*/new WeakMap();
	var _useHumanResourcesModule = /*#__PURE__*/new WeakMap();
	var _subscribeEvents = /*#__PURE__*/new WeakSet();
	var _getMemberSelector = /*#__PURE__*/new WeakSet();
	var _getSelectedItems = /*#__PURE__*/new WeakSet();
	var _drawMembers = /*#__PURE__*/new WeakSet();
	var _createMemberElement = /*#__PURE__*/new WeakSet();
	var _createPlusButton = /*#__PURE__*/new WeakSet();
	var _createNumberButton = /*#__PURE__*/new WeakSet();
	var _showPopup = /*#__PURE__*/new WeakSet();
	var _onMemberAdd = /*#__PURE__*/new WeakMap();
	var _onMemberRemove = /*#__PURE__*/new WeakMap();
	var _getMemberFromEvent = /*#__PURE__*/new WeakMap();
	var _onClosePopup = /*#__PURE__*/new WeakMap();
	var _showAlertDialog = /*#__PURE__*/new WeakSet();
	var _getItemIdByAccessCode = /*#__PURE__*/new WeakSet();
	var _getAccessCodeByItem = /*#__PURE__*/new WeakSet();
	var _onError = /*#__PURE__*/new WeakSet();
	var _updateScopeAccessCodes = /*#__PURE__*/new WeakMap();
	var _copyContextAction = /*#__PURE__*/new WeakMap();
	var _deleteContextAction = /*#__PURE__*/new WeakMap();
	var _responseAction = /*#__PURE__*/new WeakSet();
	var _notifyStatus = /*#__PURE__*/new WeakSet();
	var ConfigItem = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ConfigItem, _EventEmitter);
	  function ConfigItem(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, ConfigItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfigItem).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _notifyStatus);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _responseAction);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _onError);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getAccessCodeByItem);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getItemIdByAccessCode);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _showAlertDialog);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _showPopup);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createNumberButton);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createPlusButton);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createMemberElement);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _drawMembers);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getSelectedItems);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getMemberSelector);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _subscribeEvents);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _scopeId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _members, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _selectedItems, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _moduleId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _config, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _entityTypeId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _memberSelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _useHumanResourcesModule, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onMemberAdd, {
	      writable: true,
	      value: function value(event) {
	        var member = babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _getMemberFromEvent).call(babelHelpers.assertThisInitialized(_this), event);
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _selectedItems).push([member.type, member.id]);
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _members)[member.accessCode] = member;
	        _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _drawMembers, _drawMembers2).call(babelHelpers.assertThisInitialized(_this), true);
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onMemberRemove, {
	      writable: true,
	      value: function value(event) {
	        var member = babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _getMemberFromEvent).call(babelHelpers.assertThisInitialized(_this), event);
	        babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _selectedItems, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _memberSelector).getSelectedItems());
	        delete babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _members)[member.accessCode];
	        _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _drawMembers, _drawMembers2).call(babelHelpers.assertThisInitialized(_this), true);
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _getMemberFromEvent, {
	      writable: true,
	      value: function value(event) {
	        var _event$getData = event.getData(),
	          item = _event$getData.item;
	        return {
	          id: item.id,
	          accessCode: _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getAccessCodeByItem, _getAccessCodeByItem2).call(babelHelpers.assertThisInitialized(_this), item),
	          type: item.entityId,
	          name: item.title.text,
	          avatar: main_core.Type.isStringFilled(item.avatar) ? item.avatar : null
	        };
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _onClosePopup, {
	      writable: true,
	      value: function value() {
	        if (BX.type.isNotEmptyObject(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _members))) {
	          babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _updateScopeAccessCodes).call(babelHelpers.assertThisInitialized(_this));
	          babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _config).setPopupClose();
	        } else {
	          _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _showAlertDialog, _showAlertDialog2).call(babelHelpers.assertThisInitialized(_this));
	        }
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _updateScopeAccessCodes, {
	      writable: true,
	      value: function value() {
	        BX.ajax.runComponentAction('bitrix:ui.form.config', 'updateScopeAccessCodes', {
	          data: {
	            moduleId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _moduleId),
	            scopeId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId),
	            accessCodes: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _members)
	          }
	        }).then(function (result) {
	          _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _responseAction, _responseAction2).call(babelHelpers.assertThisInitialized(_this));
	        })["catch"](_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _onError, _onError2).bind(babelHelpers.assertThisInitialized(_this)));
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _copyContextAction, {
	      writable: true,
	      value: function value(event) {
	        if (event.data.scopeId !== main_core.Text.toInteger(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId))) {
	          return;
	        }
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _config).showConfirmDialog(main_core.Loc.getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_COPY'), main_core.Loc.getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_COPY'), function () {
	          BX.ajax.runComponentAction('bitrix:ui.form.config', 'copyScope', {
	            data: {
	              moduleId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _moduleId),
	              entityTypeId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _entityTypeId),
	              scopeId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId)
	            }
	          }).then(function (result) {
	            _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _responseAction, _responseAction2).call(babelHelpers.assertThisInitialized(_this));
	          })["catch"](_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _onError, _onError2).bind(babelHelpers.assertThisInitialized(_this)));
	          return true;
	        });
	      }
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _deleteContextAction, {
	      writable: true,
	      value: function value(event) {
	        if (event.data.scopeId !== main_core.Text.toInteger(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId))) {
	          return;
	        }
	        babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _config).showConfirmDialog(main_core.Loc.getMessage('UI_SCOPE_LIST_CONFIRM_TITLE_DELETE'), main_core.Loc.getMessage('UI_SCOPE_LIST_CONFIRM_ACCEPT_DELETE'), function () {
	          BX.ajax.runComponentAction('bitrix:ui.form.config', 'removeScope', {
	            data: {
	              moduleId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _moduleId),
	              scopeId: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId)
	            }
	          }).then(function (result) {
	            _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _responseAction, _responseAction2).call(babelHelpers.assertThisInitialized(_this));
	          })["catch"](_classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _onError, _onError2).bind(babelHelpers.assertThisInitialized(_this)));
	          return true;
	        });
	      }
	    });
	    _this.setEventNamespace('BX.Ui.Form');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _scopeId, options.scopeId || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _members, options.members || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _node, BX("ui-editor-config-".concat(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _scopeId))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _moduleId, options.moduleId || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _config, Config);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _entityTypeId, options.entityTypeId || null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _useHumanResourcesModule, BX.prop.getBoolean(options, 'useHumanResourcesModule', false));
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _drawMembers, _drawMembers2).call(babelHelpers.assertThisInitialized(_this));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _memberSelector, _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _getMemberSelector, _getMemberSelector2).call(babelHelpers.assertThisInitialized(_this)));
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _subscribeEvents, _subscribeEvents2).call(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  return ConfigItem;
	}(main_core_events.EventEmitter);
	function _subscribeEvents2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _config).isSubscribed(babelHelpers.classPrivateFieldGet(this, _scopeId))) {
	    main_core_events.EventEmitter.subscribe('BX.Ui.Form.ConfigItem:copyContextAction', babelHelpers.classPrivateFieldGet(this, _copyContextAction));
	    main_core_events.EventEmitter.subscribe('BX.Ui.Form.ConfigItem:deleteContextAction', babelHelpers.classPrivateFieldGet(this, _deleteContextAction));
	    babelHelpers.classPrivateFieldGet(this, _config).addSubscribed(babelHelpers.classPrivateFieldGet(this, _scopeId));
	  }
	}
	function _getMemberSelector2() {
	  return new ui_entitySelector.Dialog({
	    targetNode: babelHelpers.classPrivateFieldGet(this, _node),
	    enableSearch: true,
	    context: 'EDITOR_CONFIG_USER_CONTEXT',
	    preselectedItems: _classPrivateMethodGet(this, _getSelectedItems, _getSelectedItems2).call(this),
	    items: [],
	    entities: [{
	      id: 'user'
	    }, {
	      id: 'project'
	    }, {
	      id: babelHelpers.classPrivateFieldGet(this, _useHumanResourcesModule) ? 'structure-node' : 'department',
	      options: {
	        selectMode: 'usersAndDepartments'
	      }
	    }],
	    events: {
	      'Item:onSelect': babelHelpers.classPrivateFieldGet(this, _onMemberAdd),
	      'Item:onDeselect': babelHelpers.classPrivateFieldGet(this, _onMemberRemove),
	      onHide: babelHelpers.classPrivateFieldGet(this, _onClosePopup)
	    }
	  });
	}
	function _getSelectedItems2() {
	  var _this2 = this;
	  if (babelHelpers.classPrivateFieldGet(this, _members) && !main_core.Type.isArrayFilled(Object.keys(babelHelpers.classPrivateFieldGet(this, _selectedItems)))) {
	    var items = [];
	    var members = babelHelpers.classPrivateFieldGet(this, _members);
	    Object.entries(members).forEach(function (_ref) {
	      var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	        key = _ref2[0],
	        value = _ref2[1];
	      items.push(_classPrivateMethodGet(_this2, _getItemIdByAccessCode, _getItemIdByAccessCode2).call(_this2, key));
	    });
	    babelHelpers.classPrivateFieldSet(this, _selectedItems, items);
	  }
	  return babelHelpers.classPrivateFieldGet(this, _selectedItems) || [];
	}
	function _drawMembers2(withClean) {
	  var _this3 = this;
	  if (!babelHelpers.classPrivateFieldGet(this, _members)) {
	    return;
	  }
	  if (withClean) {
	    main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _node));
	  }
	  var shownMembers = babelHelpers.classPrivateFieldGet(this, _members);
	  var notShownMembersCount = 0;
	  if (Object.values(babelHelpers.classPrivateFieldGet(this, _members)).length > MAX_SHOWN_MEMBERS) {
	    shownMembers = Object.values(babelHelpers.classPrivateFieldGet(this, _members)).slice(0, MAX_SHOWN_MEMBERS);
	    notShownMembersCount = Object.values(babelHelpers.classPrivateFieldGet(this, _members)).length - MAX_SHOWN_MEMBERS;
	  }
	  Object.values(shownMembers).forEach(function (item) {
	    main_core.Dom.append(_classPrivateMethodGet(_this3, _createMemberElement, _createMemberElement2).call(_this3, item), babelHelpers.classPrivateFieldGet(_this3, _node));
	  });
	  if (notShownMembersCount > 0) {
	    main_core.Dom.append(_classPrivateMethodGet(this, _createNumberButton, _createNumberButton2).call(this, notShownMembersCount), babelHelpers.classPrivateFieldGet(this, _node));
	  }
	  main_core.Dom.append(_classPrivateMethodGet(this, _createPlusButton, _createPlusButton2).call(this), babelHelpers.classPrivateFieldGet(this, _node));
	}
	function _createMemberElement2(member) {
	  var children = member.avatar ? main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-editor-config-item-avatar\"  title=\"", "\" style=\"background-image: url('", "')\"></a>"])), member.url, main_core.Text.encode(member.name), encodeURI(main_core.Text.encode(member.avatar))) : main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-icon ui-icon-xs ui-icon-common-user\" title=\"", "\"><i></i></a>"])), member.url, main_core.Text.encode(member.name));
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
	        if (!babelHelpers.classPrivateFieldGet(_this4, _config).popupIsOpen()) {
	          _classPrivateMethodGet(_this4, _showPopup, _showPopup2).call(_this4);
	        }
	      }
	    },
	    attrs: {
	      "class": 'ui-editor-config-item ui-editor-config-item--add'
	    }
	  });
	}
	function _createNumberButton2(number) {
	  return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-editor-config-item\"><span href=\"#\" class=\"ui-editor-config-item--number\">+", "</span></div>"])), number);
	}
	function _showPopup2() {
	  babelHelpers.classPrivateFieldGet(this, _config).setPopupOpen();
	  babelHelpers.classPrivateFieldGet(this, _memberSelector).show();
	}
	function _showAlertDialog2() {
	  var _this5 = this;
	  var alert = ui_dialogs_messagebox.MessageBox.create({
	    message: main_core.Loc.getMessage('UI_SCOPE_LIST_ALERT_EMPTY_CODES'),
	    useAirDesign: true,
	    buttons: ui_dialogs_messagebox.MessageBoxButtons.OK,
	    onOk: function onOk(messagebox) {
	      messagebox.close();
	      babelHelpers.classPrivateFieldGet(_this5, _memberSelector).show();
	    }
	  });
	  alert.show();
	}
	function _getItemIdByAccessCode2(accessCode) {
	  if (/^I?U(\d+)$/.test(accessCode)) {
	    var match = accessCode.match(/^I?U(\d+)$/) || null;
	    var userId = match ? match[1] : null;
	    return ['user', userId];
	  }
	  if (/^DR(\d+)$/.test(accessCode)) {
	    var _match = accessCode.match(/^DR(\d+)$/) || null;
	    var departmentId = _match ? _match[1] : null;
	    return ['department', departmentId];
	  }
	  if (/^D(\d+)$/.test(accessCode)) {
	    var _match2 = accessCode.match(/^D(\d+)$/) || null;
	    var _departmentId = _match2 ? _match2[1] : null;
	    return ['department', "".concat(_departmentId, ":F")];
	  }
	  if (/^G(\d+)$/.test(accessCode)) {
	    var _match3 = accessCode.match(/^G(\d+)$/) || null;
	    var groupId = _match3 ? _match3[1] : null;
	    return ['site-groups', groupId];
	  }
	  if (accessCode.at(0) === 'A') {
	    return ['user-groups', accessCode];
	  }
	  if (/^SG(\d+)_([AEK])$/.test(accessCode)) {
	    return ['project-access-codes', accessCode];
	  }
	  if (/^SND(\d+)$/.test(accessCode)) {
	    var _match4 = accessCode.match(/^SND(\d+)$/) || null;
	    var structureNodeId = _match4 ? _match4[1] : null;
	    return ['structure-node', "".concat(structureNodeId, ":F")];
	  }
	  if (/^SNDR(\d+)$/.test(accessCode)) {
	    var _match5 = accessCode.match(/^SNDR(\d+)$/) || null;
	    var _structureNodeId = _match5 ? _match5[1] : null;
	    return ['structure-node', _structureNodeId];
	  }
	  return ['unknown', accessCode];
	}
	function _getAccessCodeByItem2(item) {
	  var entityId = item.entityId;
	  if (entityId === 'user') {
	    return "U".concat(item.id);
	  }
	  if (entityId === 'department') {
	    if (main_core.Type.isString(item.id) && item.id.endsWith(':F')) {
	      var match = item.id.match(/^(\d+):F$/);
	      var originalId = match ? match[1] : null;

	      // only members of the department itself
	      return "D".concat(originalId);
	    }

	    // whole department recursively
	    return "DR".concat(item.id);
	  }
	  if (entityId === 'structure-node') {
	    if (main_core.Type.isString(item.id) && item.id.endsWith(':F')) {
	      var _match6 = item.id.match(/^(\d+):F$/);
	      var _originalId = _match6 ? _match6[1] : null;
	      return "SND".concat(_originalId);
	    }
	    return "SNDR".concat(item.id);
	  }
	  if (entityId === 'site-groups') {
	    return "G".concat(item.id);
	  }
	  if (entityId === 'user-groups') {
	    return item.id;
	  }
	  if (entityId === 'project-access-codes') {
	    return item.id;
	  }
	  return '';
	}
	function _onError2(response) {
	  if (response.status === 'error') {
	    _classPrivateMethodGet(this, _notifyStatus, _notifyStatus2).call(this, response.data[0]);
	  }
	}
	function _responseAction2(result) {
	  BX.Main.gridManager.getInstanceById('editor_scopes').reload();
	}
	function _notifyStatus2(err) {
	  BX.UI.Notification.Center.notify({
	    content: err === null || err === void 0 ? void 0 : err.message,
	    autoHideDelay: 5000
	  });
	}

	var namespace = main_core.Reflection.namespace('BX.Ui.Form');
	namespace.Config = Config;
	namespace.ConfigItem = ConfigItem;

}((this.window = this.window || {}),BX.Event,BX.UI.EntitySelector,BX,BX.UI,BX.UI.Dialogs));
//# sourceMappingURL=script.js.map
