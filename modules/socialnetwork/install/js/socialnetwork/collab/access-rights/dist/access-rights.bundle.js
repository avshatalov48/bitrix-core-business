/* eslint-disable */
this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,ui_formElements_view,ui_forms,ui_hint,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _params = /*#__PURE__*/new WeakMap();
	var _layout = /*#__PURE__*/new WeakMap();
	var _hintManager = /*#__PURE__*/new WeakMap();
	var _sidePanel = /*#__PURE__*/new WeakMap();
	var _renderButtons = /*#__PURE__*/new WeakSet();
	var _checkFormData = /*#__PURE__*/new WeakSet();
	var _collectFormData = /*#__PURE__*/new WeakSet();
	var _prepareFields = /*#__PURE__*/new WeakSet();
	var _prepareBaseFields = /*#__PURE__*/new WeakSet();
	var _prepareTasksFields = /*#__PURE__*/new WeakSet();
	var _getOwnerField = /*#__PURE__*/new WeakSet();
	var _getModeratorsField = /*#__PURE__*/new WeakSet();
	var _getShowHistoryField = /*#__PURE__*/new WeakSet();
	var _getWhoCanInviteField = /*#__PURE__*/new WeakSet();
	var _getManageMessagesField = /*#__PURE__*/new WeakSet();
	var _getTasksViewUsersField = /*#__PURE__*/new WeakSet();
	var _getTasksSortTasksField = /*#__PURE__*/new WeakSet();
	var _getTasksCreateTasksField = /*#__PURE__*/new WeakSet();
	var _getTasksEditTasksField = /*#__PURE__*/new WeakSet();
	var _getTasksDeleteTasksField = /*#__PURE__*/new WeakSet();
	var _getFieldLabel = /*#__PURE__*/new WeakSet();
	var _getSelector = /*#__PURE__*/new WeakSet();
	var Form = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Form, _EventEmitter);
	  function Form(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Form);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Form).call(this, params));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getSelector);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getFieldLabel);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTasksDeleteTasksField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTasksEditTasksField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTasksCreateTasksField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTasksSortTasksField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getTasksViewUsersField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getManageMessagesField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getWhoCanInviteField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getShowHistoryField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getModeratorsField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getOwnerField);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareTasksFields);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareBaseFields);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _prepareFields);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _collectFormData);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _checkFormData);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderButtons);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _params, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _layout, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _hintManager, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _sidePanel, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _params, params);
	    _this.setEventNamespace('BX.Socialnetwork.Collab.Form');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _layout, {});
	    _this.sidePanelId = 'sn-collab-access-rights';
	    return _this;
	  }
	  babelHelpers.createClass(Form, [{
	    key: "open",
	    value: function open() {}
	  }, {
	    key: "prepareFormData",
	    value: function prepareFormData(data) {
	      return {
	        id: Number(data.id),
	        ownerId: Number(data.ownerId),
	        moderators: main_core.Type.isArray(data.moderatorMembers) ? data.moderatorMembers : [],
	        permissions: main_core.Type.isPlainObject(data.permissions) ? data.permissions : {},
	        options: main_core.Type.isPlainObject(data.options) ? data.options : {},
	        permissionsLabels: main_core.Type.isPlainObject(data.permissionsLabels) ? data.permissionsLabels : {},
	        rightsPermissionsLabels: main_core.Type.isPlainObject(data.rightsPermissionsLabels) ? data.rightsPermissionsLabels : {},
	        optionsLabels: main_core.Type.isPlainObject(data.optionsLabels) ? data.optionsLabels : {}
	      };
	    }
	  }, {
	    key: "render",
	    value: function render(formData) {
	      var _this2 = this;
	      var uiStyles = 'ui-sidepanel-layout-content ui-sidepanel-layout-content-margin';
	      _classPrivateMethodGet(this, _prepareFields, _prepareFields2).call(this, formData);
	      var _ref = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div ref=\"content\" class=\"sn-collab__access-right-side-panel ui-sidepanel-layout\">\n\t\t\t\t<div class=\"ui-sidepanel-layout-header\">\n\t\t\t\t\t<div class=\"ui-sidepanel-layout-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<form ref=\"form\" class=\"", " sn-collab__access-right-form\">\n\t\t\t\t\t<div class=\"sn-collab__access-right-form-box\">\n\t\t\t\t\t\t<div class=\"sn-collab__access-right-form-box-label\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"sn-collab__access-right-form-box --selectors\">\n\t\t\t\t\t\t<div class=\"sn-collab__access-right-form-box-label\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</form>\n\t\t\t\t<div class=\"ui-sidepanel-layout-footer-anchor\"></div>\n\t\t\t\t<div class=\"ui-sidepanel-layout-footer\">\n\t\t\t\t<div class=\"ui-sidepanel-layout-buttons\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS'), uiStyles, main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MANAGEMENT_LABEL'), babelHelpers.classPrivateFieldGet(this, _layout).ownerField.render(), babelHelpers.classPrivateFieldGet(this, _layout).moderatorsField.render(), babelHelpers.classPrivateFieldGet(this, _layout).showHistory.render(), babelHelpers.classPrivateFieldGet(this, _layout).whoCanInvite.render(), babelHelpers.classPrivateFieldGet(this, _layout).manageMessages.render(), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TASKS_LABEL'), babelHelpers.classPrivateFieldGet(this, _layout).tasksViewUsersField.render(), babelHelpers.classPrivateFieldGet(this, _layout).tasksSortTasksField.render(), babelHelpers.classPrivateFieldGet(this, _layout).tasksCreateTasksField.render(), babelHelpers.classPrivateFieldGet(this, _layout).tasksEditTasksField.render(), babelHelpers.classPrivateFieldGet(this, _layout).tasksDeleteTasksField.render(), _classPrivateMethodGet(this, _renderButtons, _renderButtons2).call(this, formData)),
	        content = _ref.content,
	        form = _ref.form;
	      main_core.Event.bind(form, 'change', function () {
	        _classPrivateMethodGet(_this2, _checkFormData, _checkFormData2).call(_this2);
	      });
	      return content;
	    }
	  }, {
	    key: "onLoad",
	    value: function onLoad(event) {
	      babelHelpers.classPrivateFieldSet(this, _hintManager, BX.UI.Hint.createInstance({
	        id: this.sidePanelId,
	        popupParameters: {
	          targetContainer: window.top.document.body
	        }
	      }));
	      babelHelpers.classPrivateFieldGet(this, _hintManager).init(event.slider.getContainer());
	      babelHelpers.classPrivateFieldSet(this, _sidePanel, event.slider);
	    }
	  }, {
	    key: "onClose",
	    value: function onClose() {
	      babelHelpers.classPrivateFieldGet(this, _hintManager).hide();
	    }
	  }]);
	  return Form;
	}(main_core_events.EventEmitter);
	function _renderButtons2(formData) {
	  var _this3 = this;
	  var _ref2 = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div ref=\"buttons\">\n\t\t\t\t<button ref=\"save\" class=\"ui-btn ui-btn-success\">\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t\t<button ref=\"cancel\" class=\"ui-btn ui-btn-link\">\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_BUTTON_SAVE'), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_BUTTON_CANCEL')),
	    buttons = _ref2.buttons,
	    save = _ref2.save,
	    cancel = _ref2.cancel;
	  main_core.Event.bind(save, 'click', function () {
	    if (_classPrivateMethodGet(_this3, _checkFormData, _checkFormData2).call(_this3)) {
	      if (babelHelpers.classPrivateFieldGet(_this3, _params).enableServerSave === true) {
	        main_core.ajax.runAction('socialnetwork.collab.AccessRights.saveRights', {
	          data: _classPrivateMethodGet(_this3, _collectFormData, _collectFormData2).call(_this3, formData)
	        }).then(function () {
	          babelHelpers.classPrivateFieldGet(_this3, _sidePanel).close();
	        })["catch"](function (error) {
	          console.error(error);
	        });
	      } else {
	        _this3.emit('save', _classPrivateMethodGet(_this3, _collectFormData, _collectFormData2).call(_this3, formData));
	        babelHelpers.classPrivateFieldGet(_this3, _sidePanel).close();
	      }
	    }
	  });
	  main_core.Event.bind(cancel, 'click', function () {
	    _this3.emit('cancel');
	    babelHelpers.classPrivateFieldGet(_this3, _sidePanel).close();
	  });
	  return buttons;
	}
	function _checkFormData2() {
	  var _babelHelpers$classPr;
	  var ownerIds = (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _layout).ownerField) === null || _babelHelpers$classPr === void 0 ? void 0 : _babelHelpers$classPr.getSelector().getTags().map(function (tag) {
	    return tag.id;
	  });
	  if (ownerIds.length === 0) {
	    babelHelpers.classPrivateFieldGet(this, _layout).ownerField.setErrors([main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_ERROR_REQUIRED_OWNER')]);
	    return false;
	  }
	  babelHelpers.classPrivateFieldGet(this, _layout).ownerField.cleanError();
	  return true;
	}
	function _collectFormData2(formData) {
	  var _babelHelpers$classPr2, _babelHelpers$classPr4;
	  var ownerId = formData.ownerId;
	  if ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _layout).ownerField) !== null && _babelHelpers$classPr2 !== void 0 && _babelHelpers$classPr2.getSelector().getDialog().isLoaded()) {
	    var _babelHelpers$classPr3;
	    ownerId = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _layout).ownerField) === null || _babelHelpers$classPr3 === void 0 ? void 0 : _babelHelpers$classPr3.getSelector().getTags().map(function (tag) {
	      return tag.id;
	    });
	  }
	  var moderators = formData.moderators;
	  if ((_babelHelpers$classPr4 = babelHelpers.classPrivateFieldGet(this, _layout).moderatorsField) !== null && _babelHelpers$classPr4 !== void 0 && _babelHelpers$classPr4.getSelector().getDialog().isLoaded()) {
	    var _babelHelpers$classPr5;
	    moderators = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldGet(this, _layout).moderatorsField) === null || _babelHelpers$classPr5 === void 0 ? void 0 : _babelHelpers$classPr5.getSelector().getTags().map(function (tag) {
	      return tag.id;
	    });
	  }
	  return {
	    id: formData.id,
	    ownerId: ownerId[0],
	    moderators: moderators,
	    options: {
	      showHistory: babelHelpers.classPrivateFieldGet(this, _layout).showHistory.getValue(),
	      manageMessages: babelHelpers.classPrivateFieldGet(this, _layout).manageMessages.getValue(),
	      whoCanInvite: babelHelpers.classPrivateFieldGet(this, _layout).whoCanInvite.getValue()
	    },
	    permissions: {
	      tasks: {
	        view_all: babelHelpers.classPrivateFieldGet(this, _layout).tasksViewUsersField.getValue(),
	        sort: babelHelpers.classPrivateFieldGet(this, _layout).tasksSortTasksField.getValue(),
	        create_tasks: babelHelpers.classPrivateFieldGet(this, _layout).tasksCreateTasksField.getValue(),
	        edit_tasks: babelHelpers.classPrivateFieldGet(this, _layout).tasksEditTasksField.getValue(),
	        delete_tasks: babelHelpers.classPrivateFieldGet(this, _layout).tasksDeleteTasksField.getValue()
	      }
	    }
	  };
	}
	function _prepareFields2(formData) {
	  _classPrivateMethodGet(this, _prepareBaseFields, _prepareBaseFields2).call(this, formData);
	  _classPrivateMethodGet(this, _prepareTasksFields, _prepareTasksFields2).call(this, formData);
	}
	function _prepareBaseFields2(formData) {
	  var _formData$options, _formData$options2, _formData$options3;
	  babelHelpers.classPrivateFieldGet(this, _layout).ownerField = _classPrivateMethodGet(this, _getOwnerField, _getOwnerField2).call(this, formData.ownerId);
	  babelHelpers.classPrivateFieldGet(this, _layout).moderatorsField = _classPrivateMethodGet(this, _getModeratorsField, _getModeratorsField2).call(this, formData.moderators);
	  babelHelpers.classPrivateFieldGet(this, _layout).showHistory = _classPrivateMethodGet(this, _getShowHistoryField, _getShowHistoryField2).call(this, formData.optionsLabels, (_formData$options = formData.options) === null || _formData$options === void 0 ? void 0 : _formData$options.showHistory);
	  babelHelpers.classPrivateFieldGet(this, _layout).whoCanInvite = _classPrivateMethodGet(this, _getWhoCanInviteField, _getWhoCanInviteField2).call(this, formData.permissionsLabels, (_formData$options2 = formData.options) === null || _formData$options2 === void 0 ? void 0 : _formData$options2.whoCanInvite);
	  babelHelpers.classPrivateFieldGet(this, _layout).manageMessages = _classPrivateMethodGet(this, _getManageMessagesField, _getManageMessagesField2).call(this, formData.permissionsLabels, (_formData$options3 = formData.options) === null || _formData$options3 === void 0 ? void 0 : _formData$options3.manageMessages);
	}
	function _prepareTasksFields2(formData) {
	  var _formData$permissions;
	  var tasks = main_core.Type.isPlainObject((_formData$permissions = formData.permissions) === null || _formData$permissions === void 0 ? void 0 : _formData$permissions.tasks) ? formData.permissions.tasks : {};
	  babelHelpers.classPrivateFieldGet(this, _layout).tasksViewUsersField = _classPrivateMethodGet(this, _getTasksViewUsersField, _getTasksViewUsersField2).call(this, formData.rightsPermissionsLabels, tasks === null || tasks === void 0 ? void 0 : tasks.view_all);
	  babelHelpers.classPrivateFieldGet(this, _layout).tasksSortTasksField = _classPrivateMethodGet(this, _getTasksSortTasksField, _getTasksSortTasksField2).call(this, formData.rightsPermissionsLabels, tasks === null || tasks === void 0 ? void 0 : tasks.sort);
	  babelHelpers.classPrivateFieldGet(this, _layout).tasksCreateTasksField = _classPrivateMethodGet(this, _getTasksCreateTasksField, _getTasksCreateTasksField2).call(this, formData.rightsPermissionsLabels, tasks === null || tasks === void 0 ? void 0 : tasks.create_tasks);
	  babelHelpers.classPrivateFieldGet(this, _layout).tasksEditTasksField = _classPrivateMethodGet(this, _getTasksEditTasksField, _getTasksEditTasksField2).call(this, formData.rightsPermissionsLabels, tasks === null || tasks === void 0 ? void 0 : tasks.edit_tasks);
	  babelHelpers.classPrivateFieldGet(this, _layout).tasksDeleteTasksField = _classPrivateMethodGet(this, _getTasksDeleteTasksField, _getTasksDeleteTasksField2).call(this, formData.rightsPermissionsLabels, tasks === null || tasks === void 0 ? void 0 : tasks.delete_tasks);
	}
	function _getOwnerField2(ownerId) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'OwnerHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_OWNER_LABEL'), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_OWNER_LABEL_HINT'));
	  return new ui_formElements_view.UserSelector({
	    id: 'sn-collab-form-field-owner',
	    label: label,
	    enableAll: false,
	    enableDepartments: false,
	    multiple: false,
	    values: [['user', ownerId]]
	  });
	}
	function _getModeratorsField2(moderators) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'ModeratorsHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MODERATORS_LABEL'), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_MODERATORS_LABEL_HINT'));
	  return new ui_formElements_view.UserSelector({
	    id: 'sn-collab-form-field-moderators',
	    label: label,
	    enableAll: false,
	    enableDepartments: false,
	    multiple: true,
	    values: moderators.map(function (moderatorId) {
	      return ['user', moderatorId];
	    })
	  });
	}
	function _getShowHistoryField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'ShowHistoryHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_SHOW_HISTORY_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'showHistory', label, options, selectedValue);
	}
	function _getWhoCanInviteField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'InitiateHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_INITIATE_LABEL'), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_INITIATE_LABEL_HINT'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'whoCanInvite', label, options, selectedValue);
	}
	function _getManageMessagesField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'ChatHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_CHAT_LABEL'), main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_CHAT_LABEL_HINT'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'manageMessages', label, options, selectedValue);
	}
	function _getTasksViewUsersField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'TasksViewUsersHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TVU_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'initiate', label, options, selectedValue);
	}
	function _getTasksSortTasksField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'TasksSortTasksHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TST_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'initiate', label, options, selectedValue);
	}
	function _getTasksCreateTasksField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'TasksCreateTasksHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TCT_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'initiate', label, options, selectedValue);
	}
	function _getTasksEditTasksField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'TasksEditTasksHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TET_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'initiate', label, options, selectedValue);
	}
	function _getTasksDeleteTasksField2(options, selectedValue) {
	  var label = _classPrivateMethodGet(this, _getFieldLabel, _getFieldLabel2).call(this, 'TasksDeleteTasksHint', main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS_TDT_LABEL'));
	  return _classPrivateMethodGet(this, _getSelector, _getSelector2).call(this, 'initiate', label, options, selectedValue);
	}
	function _getFieldLabel2(id, label) {
	  var hint = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	  if (hint === null) {
	    return "\n\t\t\t\t<div class=\"tasks-flow__create-title-with-hint\">\n\t\t\t\t\t".concat(label, "\n\t\t\t\t</div>\n\t\t\t");
	  }
	  return "\n\t\t\t<div class=\"tasks-flow__create-title-with-hint\">\n\t\t\t\t".concat(label, "\n\t\t\t\t<span\n\t\t\t\t\tdata-id=\"").concat(id, "\"\n\t\t\t\t\tclass=\"ui-hint\"\n\t\t\t\t\tdata-hint=\"").concat(hint, "\" \n\t\t\t\t\tdata-hint-no-icon\n\t\t\t\t><span class=\"ui-hint-icon\"></span></span>\n\t\t\t</div>\n\t\t");
	}
	function _getSelector2(id, label, options) {
	  var selectedValue = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'K';
	  var items = [];
	  Object.entries(options).forEach(function (_ref3) {
	    var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	      value = _ref4[0],
	      name = _ref4[1];
	    items.push({
	      value: value,
	      name: name,
	      selected: value === selectedValue
	    });
	  });
	  return new ui_formElements_view.Selector({
	    id: "sn-collab-form-field-".concat(id),
	    label: label,
	    items: items,
	    current: selectedValue
	  });
	}

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	var AddForm = /*#__PURE__*/function (_Form) {
	  babelHelpers.inherits(AddForm, _Form);
	  function AddForm() {
	    babelHelpers.classCallCheck(this, AddForm);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AddForm).apply(this, arguments));
	  }
	  babelHelpers.createClass(AddForm, [{
	    key: "open",
	    value: function open() {
	      var _this = this;
	      var slider = BX.SidePanel.Instance.getSlider(this.sidePanelId);
	      if (slider !== null && slider !== void 0 && slider.isOpen()) {
	        return;
	      }
	      BX.SidePanel.Instance.open(this.sidePanelId, {
	        cacheable: false,
	        title: main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS'),
	        contentCallback: function () {
	          var _contentCallback = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(sidePanel) {
	            var _yield$ajax$runAction, data;
	            return _regeneratorRuntime().wrap(function _callee$(_context) {
	              while (1) switch (_context.prev = _context.next) {
	                case 0:
	                  _context.prev = 0;
	                  _context.next = 3;
	                  return main_core.ajax.runAction('socialnetwork.collab.AccessRights.getAddForm', {
	                    data: {}
	                  });
	                case 3:
	                  _yield$ajax$runAction = _context.sent;
	                  data = _yield$ajax$runAction.data;
	                  return _context.abrupt("return", _this.render(_this.prepareFormData(data)));
	                case 8:
	                  _context.prev = 8;
	                  _context.t0 = _context["catch"](0);
	                  console.error(_context.t0);
	                  return _context.abrupt("return", Promise.reject());
	                case 12:
	                case "end":
	                  return _context.stop();
	              }
	            }, _callee, null, [[0, 8]]);
	          }));
	          function contentCallback(_x) {
	            return _contentCallback.apply(this, arguments);
	          }
	          return contentCallback;
	        }(),
	        width: 661,
	        events: {
	          onLoad: this.onLoad.bind(this),
	          onClose: this.onClose.bind(this)
	        }
	      });
	    }
	  }]);
	  return AddForm;
	}(Form);

	function _regeneratorRuntime$1() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$1 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _params$1 = /*#__PURE__*/new WeakMap();
	var EditForm = /*#__PURE__*/function (_Form) {
	  babelHelpers.inherits(EditForm, _Form);
	  function EditForm(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, EditForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EditForm).call(this, params));
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _params$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _params$1, params);
	    if (!main_core.Type.isNumber(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _params$1).collabId)) {
	      throw new TypeError('Collab id is required');
	    }
	    return _this;
	  }
	  babelHelpers.createClass(EditForm, [{
	    key: "open",
	    value: function open() {
	      var _this2 = this;
	      var sidePanelId = "sn-collab-access-rights-".concat(babelHelpers.classPrivateFieldGet(this, _params$1).collabId);
	      var slider = BX.SidePanel.Instance.getSlider(sidePanelId);
	      if (slider !== null && slider !== void 0 && slider.isOpen()) {
	        return;
	      }
	      BX.SidePanel.Instance.open(sidePanelId, {
	        cacheable: false,
	        title: main_core.Loc.getMessage('SN_COLLAB_ACCESS_RIGHTS'),
	        contentCallback: function () {
	          var _contentCallback = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$1().mark(function _callee(sidePanel) {
	            var _yield$ajax$runAction, data;
	            return _regeneratorRuntime$1().wrap(function _callee$(_context) {
	              while (1) switch (_context.prev = _context.next) {
	                case 0:
	                  _context.prev = 0;
	                  _context.next = 3;
	                  return main_core.ajax.runAction('socialnetwork.collab.AccessRights.getEditForm', {
	                    data: {
	                      collabId: babelHelpers.classPrivateFieldGet(_this2, _params$1).collabId
	                    }
	                  });
	                case 3:
	                  _yield$ajax$runAction = _context.sent;
	                  data = _yield$ajax$runAction.data;
	                  return _context.abrupt("return", _this2.render(_this2.prepareFormData(data)));
	                case 8:
	                  _context.prev = 8;
	                  _context.t0 = _context["catch"](0);
	                  console.error(_context.t0);
	                  return _context.abrupt("return", Promise.reject());
	                case 12:
	                case "end":
	                  return _context.stop();
	              }
	            }, _callee, null, [[0, 8]]);
	          }));
	          function contentCallback(_x) {
	            return _contentCallback.apply(this, arguments);
	          }
	          return contentCallback;
	        }(),
	        width: 661,
	        events: {
	          onLoad: this.onLoad.bind(this),
	          onClose: this.onClose.bind(this)
	        }
	      });
	    }
	  }]);
	  return EditForm;
	}(Form);

	function _regeneratorRuntime$2() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime$2 = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
	var AccessRights = /*#__PURE__*/function () {
	  function AccessRights() {
	    babelHelpers.classCallCheck(this, AccessRights);
	  }
	  babelHelpers.createClass(AccessRights, null, [{
	    key: "openForm",
	    value: function () {
	      var _openForm = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime$2().mark(function _callee(params) {
	        var isEditMode, _form, form;
	        return _regeneratorRuntime$2().wrap(function _callee$(_context) {
	          while (1) switch (_context.prev = _context.next) {
	            case 0:
	              _context.next = 2;
	              return top.BX.Runtime.loadExtension('socialnetwork.collab.access-rights');
	            case 2:
	              isEditMode = Number(params === null || params === void 0 ? void 0 : params.collabId) > 0;
	              if (!isEditMode) {
	                _context.next = 7;
	                break;
	              }
	              _form = new EditForm(params);
	              _form.open();
	              return _context.abrupt("return", _form);
	            case 7:
	              form = new AddForm(params);
	              form.open();
	              return _context.abrupt("return", form);
	            case 10:
	            case "end":
	              return _context.stop();
	          }
	        }, _callee);
	      }));
	      function openForm(_x) {
	        return _openForm.apply(this, arguments);
	      }
	      return openForm;
	    }()
	  }]);
	  return AccessRights;
	}();

	exports.AccessRights = AccessRights;

}((this.BX.Socialnetwork.Collab = this.BX.Socialnetwork.Collab || {}),BX.UI.FormElements,BX,BX,BX,BX.Event));
//# sourceMappingURL=access-rights.bundle.js.map
