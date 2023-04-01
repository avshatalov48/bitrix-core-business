this.BX = this.BX || {};
(function (exports,main_loader,ui_notification,ui_switcher,main_popup,main_core_events,ui_entitySelector,main_core) {
	'use strict';

	var _templateObject, _templateObject2;
	var BX$1 = main_core.Reflection.namespace('BX');

	var Grid = /*#__PURE__*/function () {
	  function Grid(options) {
	    babelHelpers.classCallCheck(this, Grid);
	    options = options || {};
	    this.options = options;
	    this.renderTo = options.renderTo;
	    this.buttonPanel = BX$1.UI.ButtonPanel || null;
	    this.layout = {
	      container: null
	    };
	    this.component = options.component ? options.component : null;
	    this.actionSave = options.actionSave || Grid.ACTION_SAVE;
	    this.actionDelete = options.actionDelete || Grid.ACTION_DELETE;
	    this.actionLoad = options.actionLoad || Grid.ACTION_LOAD;
	    this.mode = options.mode || Grid.MODE;
	    this.openPopupEvent = options.openPopupEvent ? options.openPopupEvent : null;
	    this.popupContainer = options.popupContainer ? options.popupContainer : null;
	    this.additionalSaveParams = options.additionalSaveParams ? options.additionalSaveParams : null;
	    this.loadParams = options.loadParams ? options.loadParams : null;
	    this.loader = null;
	    this.timer = null;
	    this.initData();

	    if (options.userGroups) {
	      this.userGroups = options.userGroups;
	    }

	    if (options.accessRights) {
	      this.accessRights = options.accessRights;
	    }

	    this.isRequested = false;
	    this.loadData();
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Grid, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:updateRole', this.updateRole.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:selectAccessItems', this.updateAccessVariationRight.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOn', this.updateAccessRight.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:accessOff', this.updateAccessRight.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:update', this.adjustButtonPanel.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addUserGroup.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:addRole', this.addRoleColumn.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addRoleColumn.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:copyRole', this.addUserGroup.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.removeRoleColumn.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.adjustButtonPanel.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.Main.SelectorV2:onGetEntityTypes', this.onGetEntityTypes.bind(this));
	    }
	  }, {
	    key: "initData",
	    value: function initData() {
	      this.accessRights = [];
	      this.userGroups = [];
	      this.accessRightsSections = [];
	      this.headSection = null;
	      this.members = [];
	      this.columns = [];
	    }
	  }, {
	    key: "fireEventReset",
	    value: function fireEventReset() {
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights:reset', this);
	    }
	  }, {
	    key: "fireEventRefresh",
	    value: function fireEventRefresh() {
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights:refresh', this);
	    }
	  }, {
	    key: "getButtonPanel",
	    value: function getButtonPanel() {
	      return this.buttonPanel;
	    }
	  }, {
	    key: "showNotification",
	    value: function showNotification(title) {
	      BX$1.UI.Notification.Center.notify({
	        content: title,
	        position: 'top-right',
	        autoHideDelay: 3000
	      });
	    }
	  }, {
	    key: "sendActionRequest",
	    value: function sendActionRequest() {
	      var _this = this;

	      if (this.isRequested) {
	        return;
	      }

	      this.isRequested = true;
	      main_core_events.EventEmitter.emit(this, 'onBeforeSave', this);
	      this.timer = setTimeout(function () {
	        _this.blockGrid();
	      }, 1000);
	      var needReload = false;
	      var dataToSave = [];

	      for (var i = 0; i < this.userGroups.length; i++) {
	        if (main_core.Text.toNumber(this.userGroups[i].id) === 0) {
	          needReload = true;
	        }

	        dataToSave.push({
	          accessCodes: this.userGroups[i].accessCodes,
	          id: this.userGroups[i].id,
	          title: this.userGroups[i].title,
	          type: this.userGroups[i].type,
	          accessRights: this.userGroups[i].accessRights
	        });
	      }

	      BX$1.ajax.runComponentAction(this.component, this.actionSave, {
	        mode: this.mode,
	        data: {
	          userGroups: dataToSave,
	          parameters: this.additionalSaveParams
	        } // analyticsLabel: {
	        // 	viewMode: 'grid',
	        // 	filterState: 'closed'
	        // }

	      }).then(function () {
	        if (needReload) {
	          _this.reloadGrid();
	        }

	        _this.isRequested = false;

	        _this.showNotification(main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_STTINGS_HAVE_BEEN_SAVED'));

	        _this.unBlockGrid();

	        _this.fireEventRefresh();

	        setTimeout(function () {
	          _this.adjustButtonPanel();
	        });
	        clearTimeout(_this.timer);

	        var waitContainer = _this.buttonPanel.getContainer().querySelector('.ui-btn-wait');

	        main_core.Dom.removeClass(waitContainer, 'ui-btn-wait');
	      }, function () {
	        _this.isRequested = false;

	        _this.showNotification('Error message');

	        _this.unBlockGrid();

	        clearTimeout(_this.timer);

	        var waitContainer = _this.buttonPanel.getContainer().querySelector('.ui-btn-wait');

	        main_core.Dom.removeClass(waitContainer, 'ui-btn-wait');
	      });
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights:preservation', this);
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      main_core.Dom.addClass(this.getMainContainer(), '--lock');
	    }
	  }, {
	    key: "unlock",
	    value: function unlock() {
	      main_core.Dom.removeClass(this.getMainContainer(), '--lock');
	    }
	  }, {
	    key: "deleteActionRequest",
	    value: function deleteActionRequest(roleId) {
	      var _this2 = this;

	      if (this.isRequested) {
	        return;
	      }

	      this.isRequested = true;
	      this.timer = setTimeout(function () {
	        _this2.blockGrid();
	      }, 1000);
	      BX$1.ajax.runComponentAction(this.component, this.actionDelete, {
	        mode: this.mode,
	        data: {
	          roleId: roleId
	        } // analyticsLabel: {
	        // 	viewMode: 'grid',
	        // 	filterState: 'closed'
	        // }

	      }).then(function () {
	        _this2.isRequested = false;

	        _this2.showNotification(main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_REMOVE'));

	        _this2.unBlockGrid();

	        clearTimeout(_this2.timer);
	      }, function () {
	        _this2.isRequested = false;

	        _this2.showNotification('Error message');

	        _this2.unBlockGrid();

	        clearTimeout(_this2.timer);
	      });
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var _this3 = this;

	      this.initData();
	      BX$1.ajax.runComponentAction(this.component, this.actionLoad, {
	        mode: this.mode,
	        data: {
	          parameters: this.loadParams
	        }
	      }).then(function (response) {
	        if (response.data['ACCESS_RIGHTS'] && response.data['USER_GROUPS']) {
	          _this3.accessRights = response.data.ACCESS_RIGHTS;
	          _this3.userGroups = response.data.USER_GROUPS;

	          _this3.loadData();

	          _this3.draw();
	        }

	        _this3.unBlockGrid();
	      }, function () {
	        return _this3.unBlockGrid;
	      });
	    }
	  }, {
	    key: "blockGrid",
	    value: function blockGrid() {
	      var _this4 = this;

	      var offsetTop = this.layout.container.getBoundingClientRect().top < 0 ? '0' : this.layout.container.getBoundingClientRect().top;
	      main_core.Dom.addClass(this.layout.container, 'ui-access-rights-block');
	      main_core.Dom.style(this.layout.container, 'height', 'calc(100vh - ' + offsetTop + 'px)');
	      setTimeout(function () {
	        main_core.Dom.style(_this4.layout.container, 'height', 'calc(100vh - ' + offsetTop + 'px)');
	      });
	      this.getLoader().show();
	    }
	  }, {
	    key: "unBlockGrid",
	    value: function unBlockGrid() {
	      main_core.Dom.removeClass(this.layout.container, 'ui-access-rights-block');
	      main_core.Dom.style(this.layout.container, 'height', null);
	      this.getLoader().hide();
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.layout.container
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "removeRoleColumn",
	    value: function removeRoleColumn(param) {
	      this.headSection.removeColumn(param.data);
	      this.accessRightsSections.map(function (data) {
	        data.removeColumn(param.data);
	      });
	      var targetIndex = this.userGroups.indexOf(param.data.userGroup);
	      this.userGroups.splice(targetIndex, 1);
	      var roleId = param.data.userGroup.id;

	      if (roleId > 0) {
	        this.deleteActionRequest(roleId);
	      }
	    }
	  }, {
	    key: "addRoleColumn",
	    value: function addRoleColumn(event) {
	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          param = _event$getData2[0];

	      if (!param) {
	        return;
	      }

	      var sections = this.accessRightsSections;

	      for (var i = 0; i < sections.length; i++) {
	        param.headSection = false;
	        param.newColumn = true;
	        sections[i].addColumn(param);
	        sections[i].scrollToRight(sections[i].getColumnsContainer().scrollWidth - sections[i].getColumnsContainer().offsetWidth, 'stop');
	      }

	      param.headSection = true;
	      param.newColumn = true;
	      this.headSection.addColumn(param);
	    }
	  }, {
	    key: "addUserGroup",
	    value: function addUserGroup(event) {
	      var _event$getData3 = event.getData(),
	          _event$getData4 = babelHelpers.slicedToArray(_event$getData3, 1),
	          options = _event$getData4[0];

	      options = options || {};
	      this.userGroups.push(options);
	    }
	  }, {
	    key: "updateRole",
	    value: function updateRole(event) {
	      var item = event.getData();
	      var index = this.userGroups.indexOf(item.userGroup);

	      if (index >= 0) {
	        this.userGroups[index].title = item.text;
	      }
	    }
	  }, {
	    key: "adjustButtonPanel",
	    value: function adjustButtonPanel() {
	      var modifiedItems = this.getMainContainer().querySelectorAll('.ui-access-rights-column-item-changer-on');
	      var modifiedRoles = this.getMainContainer().querySelectorAll('.ui-access-rights-column-new');
	      var modifiedUsers = this.getMainContainer().querySelectorAll('.ui-access-rights-members-item-new');
	      var modifiedVariables = this.getMainContainer().querySelectorAll('.ui-tag-selector-container');

	      if (modifiedItems.length > 0 || modifiedRoles.length > 0 || modifiedUsers.length > 0 || modifiedVariables.length > 0) {
	        this.buttonPanel.show();
	      } else {
	        this.buttonPanel.hide();
	      }
	    }
	  }, {
	    key: "updateAccessRight",
	    value: function updateAccessRight(event) {
	      var data = event.getData();
	      var userGroup = this.userGroups[this.userGroups.indexOf(data.userGroup)];
	      var accessId = data.access.id;

	      for (var i = 0; i < userGroup.accessRights.length; i++) {
	        var item = userGroup.accessRights[i];

	        if (item && item.id === accessId) {
	          item.value = item.value === '0' ? '1' : '0';
	          return;
	        }
	      }

	      userGroup.accessRights.push({
	        id: accessId,
	        value: data.switcher.checked ? '1' : '0'
	      });
	    }
	  }, {
	    key: "updateAccessVariationRight",
	    value: function updateAccessVariationRight(event) {
	      var item = event.getData();
	      var userGroup = this.userGroups[this.userGroups.indexOf(item.userGroup)];
	      var accessId = item.access.id;
	      var deleteIds = [];

	      for (var i = 0; i < userGroup.accessRights.length; i++) {
	        var _item = userGroup.accessRights[i];

	        if (_item && _item.id === accessId) {
	          deleteIds.push(i);
	        }
	      }

	      deleteIds.forEach(function (i) {
	        delete userGroup.accessRights[i];
	      });
	      var values = item.selectedValues || [];
	      values.forEach(function (value) {
	        userGroup.accessRights.push({
	          id: accessId,
	          value: value
	        });
	      });
	    }
	  }, {
	    key: "loadData",
	    value: function loadData() {
	      var _this5 = this;

	      this.accessRights.map(function (data, index) {
	        data.id = index;

	        _this5.accessRightsSections.push(_this5.addSection(data));
	      });
	    }
	  }, {
	    key: "getColumns",
	    value: function getColumns() {
	      return this.columns;
	    }
	  }, {
	    key: "getSections",
	    value: function getSections() {
	      return this.accessRightsSections;
	    }
	  }, {
	    key: "getUserGroups",
	    value: function getUserGroups() {
	      this.userGroups.forEach(function (item) {
	        if (item.accessCodes) {
	          for (var user in item.members) {
	            item.accessCodes[user] = item.members[user].type;
	          }
	        }
	      });
	      return this.userGroups;
	    }
	  }, {
	    key: "getHeadSection",
	    value: function getHeadSection() {
	      if (!this.headSection) {
	        this.headSection = new Section({
	          headSection: true,
	          userGroups: this.userGroups,
	          grid: this
	        });
	      }

	      return this.headSection;
	    }
	  }, {
	    key: "addSection",
	    value: function addSection(options) {
	      options = options || {};
	      return new Section({
	        id: options.id,
	        hint: options.sectionHint,
	        title: options.sectionTitle,
	        rights: options.rights ? options.rights : [],
	        grid: this
	      });
	    }
	  }, {
	    key: "getSectionNode",
	    value: function getSectionNode() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section'></div>"])));
	    }
	  }, {
	    key: "getMainContainer",
	    value: function getMainContainer() {
	      if (!this.layout.container) {
	        this.layout.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights'></div>"])));
	      }

	      return this.layout.container;
	    }
	  }, {
	    key: "draw",
	    value: function draw() {
	      var docFragmentSections = document.createDocumentFragment();
	      main_core.Dom.append(this.getHeadSection().render(), docFragmentSections);
	      this.getSections().map(function (data) {
	        main_core.Dom.append(data.render(), docFragmentSections);
	      });
	      this.layout.container = null;
	      main_core.Dom.append(docFragmentSections, this.getMainContainer());
	      this.renderTo.innerHTML = '';
	      main_core.Dom.append(this.getMainContainer(), this.renderTo);
	      this.afterRender();
	    }
	  }, {
	    key: "afterRender",
	    value: function afterRender() {
	      this.getHeadSection().adjustEars();
	      this.getSections().map(function (data) {
	        data.adjustEars();
	      });
	    }
	  }, {
	    key: "onMemberSelect",
	    value: function onMemberSelect(params) {
	      var option = Grid.buildOption(params);

	      if (!option) {
	        return;
	      }

	      if (params.state === 'select') {
	        main_core_events.EventEmitter.emit('BX.UI.AccessRights:addToAccessCodes', option);
	      }
	    }
	  }, {
	    key: "onMemberUnselect",
	    value: function onMemberUnselect(params) {
	      var option = Grid.buildOption(params);

	      if (!option) {
	        return;
	      }

	      main_core_events.EventEmitter.emit('BX.UI.AccessRights:removeFromAccessCodes', option);
	    }
	  }, {
	    key: "onGetEntityTypes",
	    value: function onGetEntityTypes() {
	      var controls = BX$1.Main.selectorManagerV2.controls;
	      var selectorInstance = controls[Object.keys(controls)[0]];
	      selectorInstance.entityTypes.USERGROUPS = {
	        options: {
	          enableSearch: 'Y',
	          searchById: 'Y',
	          addTab: 'Y',
	          returnItemUrl: selectorInstance.getOption('returnItemUrl') === 'N' ? 'N' : 'Y'
	        }
	      };
	    }
	  }], [{
	    key: "buildOption",
	    value: function buildOption(params) {
	      var controls = BX$1.Main.selectorManagerV2.controls;
	      var selectorInstance = controls[Object.keys(controls)[0]].selectorInstance;
	      var dataColumnAttribute = 'bx-data-column-id';
	      var node = selectorInstance.bindOptions.node;

	      if (!node.hasAttribute(dataColumnAttribute) || main_core.Type.isUndefined(params.item)) {
	        return false;
	      }

	      var columnId = node.getAttribute(dataColumnAttribute);
	      var accessItem = params.item.id;
	      var entityType = params.entityType;
	      var accessCodesResult = {};
	      accessCodesResult[accessItem] = entityType;
	      return {
	        accessCodes: accessCodesResult,
	        columnId: columnId,
	        item: params.item
	      };
	    }
	  }]);
	  return Grid;
	}();

	babelHelpers.defineProperty(Grid, "ACTION_SAVE", 'save');
	babelHelpers.defineProperty(Grid, "ACTION_DELETE", 'delete');
	babelHelpers.defineProperty(Grid, "ACTION_LOAD", 'load');
	babelHelpers.defineProperty(Grid, "MODE", 'ajax');
	var namespace = main_core.Reflection.namespace('BX.UI');
	namespace.AccessRights = Grid;

	var _templateObject$1;

	var Base = /*#__PURE__*/function () {
	  function Base(options) {
	    babelHelpers.classCallCheck(this, Base);
	    this.currentValue = options.currentValue || null;
	    this.identificator = 'col-' + Math.random();
	    this.parentContainer = options.container;
	    this.grid = options.grid;
	    this.text = options.text;
	    this.userGroup = options.userGroup;
	    this.access = options.access;
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Base, [{
	    key: "bindEvents",
	    value: function bindEvents() {}
	  }, {
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div></div>"])));
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.identificator;
	    }
	  }]);
	  return Base;
	}();

	var _templateObject$2;

	var Title = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Title, _Base);

	  function Title() {
	    babelHelpers.classCallCheck(this, Title);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Title).apply(this, arguments));
	  }

	  babelHelpers.createClass(Title, [{
	    key: "render",
	    value: function render() {
	      var _this = this;

	      var node = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass='ui-access-rights-column-item-text'\n\t\t\t\tdata-id='", "'\n\t\t\t>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getId(), main_core.Text.encode(this.text));
	      main_core.Event.bind(node, 'mouseenter', this.adjustPopupHelper.bind(this));
	      main_core.Event.bind(node, 'mouseleave', function () {
	        if (_this.popupHelper) {
	          _this.popupHelper.close();
	        }
	      });
	      return node;
	    }
	  }, {
	    key: "adjustPopupHelper",
	    value: function adjustPopupHelper() {
	      var set = this.parentContainer.cloneNode(true);
	      main_core.Dom.style(set, 'position', 'absolute');
	      main_core.Dom.style(set, 'display', 'inline');
	      main_core.Dom.style(set, 'visibility', 'hidden');
	      main_core.Dom.style(set, 'height', '0');
	      main_core.Dom.append(set, document.body);
	      setTimeout(function () {
	        main_core.Dom.remove(set);
	      });

	      if (set.offsetWidth > this.parentContainer.offsetWidth) {
	        main_core.Dom.style(set, 'visibility', 'visible');
	        this.getPopupHelper().show();
	      }
	    }
	  }, {
	    key: "getPopupHelper",
	    value: function getPopupHelper() {
	      if (!this.popupHelper) {
	        this.popupHelper = main_popup.PopupWindowManager.create(null, this.parentContainer, {
	          autoHide: true,
	          darkMode: true,
	          content: this.text,
	          maxWidth: this.parentContainer.offsetWidth,
	          offsetTop: -9,
	          offsetLeft: 5,
	          animation: 'fading-slide'
	        });
	      }

	      return this.popupHelper;
	    }
	  }]);
	  return Title;
	}(Base);

	babelHelpers.defineProperty(Title, "TYPE", 'title');

	var _templateObject$3;

	var Hint = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Hint, _Base);

	  function Hint(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Hint);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Hint).call(this, options));
	    _this.hint = options.hint;
	    _this.className = options.className;
	    _this.hintNode = null;
	    return _this;
	  }

	  babelHelpers.createClass(Hint, [{
	    key: "render",
	    value: function render() {
	      if (!this.hintNode && this.hint) {
	        var hintManager = BX.UI.Hint.createInstance({
	          id: 'access-rights-ui-hint-' + this.getId(),
	          popupParameters: {
	            className: 'ui-access-rights-popup-pointer-events ui-hint-popup',
	            autoHide: true,
	            darkMode: true,
	            maxWidth: 280,
	            offsetTop: 0,
	            offsetLeft: 8,
	            angle: true,
	            animation: 'fading-slide'
	          }
	        });
	        this.hintNode = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<span class='", "'></span>"])), this.className);
	        this.hintNode.setAttribute(hintManager.attributeName, this.hint);
	        this.hintNode.setAttribute(hintManager.attributeHtmlName, true);
	        this.hintNode.setAttribute(hintManager.attributeInteractivityName, true);
	        hintManager.initNode(this.hintNode);
	      }

	      return this.hintNode;
	    }
	  }]);
	  return Hint;
	}(Base);

	var _templateObject$4, _templateObject2$1, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18, _templateObject19, _templateObject20;
	var BX$2 = main_core.Reflection.namespace('BX');

	var Member = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Member, _Base);

	  function Member(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Member);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Member).call(this, options));
	    _this.openPopupEvent = options.openPopupEvent;
	    _this.popupContainer = options.popupContainer;
	    _this.accessCodes = options.accessCodes || [];
	    return _this;
	  }

	  babelHelpers.createClass(Member, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:addToAccessCodes', this.addToAccessCodes.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:removeFromAccessCodes', this.removeFromAccessCodes.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.resetNewMembers.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetNewMembers.bind(this));
	    }
	  }, {
	    key: "getMember",
	    value: function getMember() {
	      if (!this.member) {
	        var members = this.userGroup.members || {};
	        var membersFragment = document.createDocumentFragment();
	        var counter = 0;
	        this.validateVariables();
	        Object.keys(members).reverse().forEach(function (item) {
	          counter++;

	          if (counter < 7) {
	            var user = members[item];
	            var userNode = main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<div class='ui-access-rights-members-item'></div>\n\t\t\t\t\t\t"])));

	            if (user["new"]) {
	              main_core.Dom.addClass(userNode, 'ui-access-rights-members-item-new');
	            }

	            if (user.avatar) {
	              var userAvatar = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<a class='ui-access-rights-members-item-avatar' title=\"", "\"></a>"])), main_core.Text.encode(user.name));
	              main_core.Dom.style(userAvatar, 'backgroundImage', 'url(\'' + encodeURI(user.avatar) + '\')');
	              main_core.Dom.style(userAvatar, 'backgroundSize', 'cover');
	              main_core.Dom.append(userAvatar, userNode);
	            } else {
	              var avatarClass = 'ui-icon-common-user';

	              if (user.type === 'groups') {
	                avatarClass = 'ui-icon-common-user-group';
	              } else if (user.type === 'sonetgroups') {
	                avatarClass = 'ui-icon-common-company';
	              } else if (user.type === 'usergroups') {
	                avatarClass = 'ui-icon-common-user-group';
	              }

	              var emptyAvatar = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<a class='ui-icon ui-icon-xs' title=\"", "\"><i></i></a>"])), main_core.Text.encode(user.name));
	              main_core.Dom.addClass(emptyAvatar, avatarClass);
	              main_core.Dom.append(emptyAvatar, userNode);
	            }

	            main_core.Dom.append(userNode, membersFragment);
	          }
	        });
	        main_core.Dom.append(this.getAddUserToRole(), membersFragment);
	        this.member = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-members'>", "</div>"])), membersFragment);
	        main_core.Event.bind(this.member, 'click', this.adjustPopupUserControl.bind(this));
	      }

	      return this.member;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getMember();
	    }
	  }, {
	    key: "resetNewMembers",
	    value: function resetNewMembers() {
	      var newMembers = this.getMember().querySelectorAll('.ui-access-rights-members-item-new');
	      newMembers.forEach(function (item) {
	        main_core.Dom.removeClass(item, 'ui-access-rights-members-item-new');
	      });
	    }
	  }, {
	    key: "validateVariables",
	    value: function validateVariables() {
	      if (main_core.Type.isUndefined(this.userGroup.accessCodes)) {
	        this.userGroup.accessCodes = [];
	      }
	    }
	  }, {
	    key: "updateMembers",
	    value: function updateMembers() {
	      main_core.Dom.remove(this.member);
	      this.member = null;
	      main_core.Dom.append(this.getMember(), this.parentContainer);
	      this.grid.getButtonPanel().show();
	    }
	  }, {
	    key: "addToAccessCodes",
	    value: function addToAccessCodes(event) {
	      var params = event.getData();

	      if (params.columnId !== this.getId()) {
	        return;
	      }

	      var firstKey = Object.keys(params.accessCodes)[0];
	      var type = params.accessCodes[firstKey].toUpperCase();
	      this.userGroup.accessCodes = Object.keys(this.accessCodes);
	      var item = params.item;

	      if (!main_core.Type.isUndefined(item) && Object.keys(item).length) {
	        this.userGroup.members[firstKey] = {
	          id: item.entityId,
	          name: item.name,
	          avatar: item.avatar,
	          url: '',
	          "new": true,
	          type: type.toLowerCase()
	        };
	        this.updateMembers();
	      }

	      this.userGroup.accessCodes = [];

	      for (var key in this.userGroup.members) {
	        this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
	      }
	    }
	  }, {
	    key: "removeFromAccessCodes",
	    value: function removeFromAccessCodes(event) {
	      var params = event.data;

	      if (params.columnId !== this.identificator) {
	        return;
	      }

	      var firstKey = Object.keys(params.accessCodes)[0];
	      delete this.userGroup.members[firstKey];
	      this.updateMembers();
	      this.userGroup.accessCodes = [];

	      for (var key in this.userGroup.members) {
	        this.userGroup.accessCodes[key] = this.userGroup.members[key].type;
	      }
	    }
	  }, {
	    key: "adjustPopupUserControl",
	    value: function adjustPopupUserControl() {
	      var users = [];
	      var groups = [];
	      var departments = [];
	      var sonetgroups = [];

	      for (var item in this.userGroup.members) {
	        this.userGroup.members[item].key = item;

	        if (this.userGroup.members[item].type === 'users') {
	          users.push(this.userGroup.members[item]);
	        } else if (this.userGroup.members[item].type === 'groups') {
	          groups.push(this.userGroup.members[item]);
	        } else if (this.userGroup.members[item].type === 'usergroups') {
	          groups.push(this.userGroup.members[item]);
	        } else if (this.userGroup.members[item].type === 'departments') {
	          departments.push(this.userGroup.members[item]);
	        } else if (this.userGroup.members[item].type === 'sonetgroups') {
	          sonetgroups.push(this.userGroup.members[item]);
	        }
	      }

	      var counterUsers = [];

	      for (var key in this.userGroup.members) {
	        counterUsers.push(this.userGroup.members[key]);
	      }

	      if (counterUsers.length === 0) {
	        this.showUserSelectorPopup();
	        return;
	      }

	      this.getUserPopup(users, groups, departments, sonetgroups).show();
	    }
	  }, {
	    key: "getAddUserToRole",
	    value: function getAddUserToRole() {
	      if (!this.addUserToRole) {
	        this.addUserToRole = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span \n\t\t\t\t\tclass='ui-access-rights-members-item ui-access-rights-members-item-add'\n\t\t\t\t\tbx-data-column-id='", "'\n\t\t\t\t>\n\t\t\t\t</span>\n\t\t\t"])), this.getId());
	      }

	      return this.addUserToRole;
	    }
	  }, {
	    key: "getUserPopup",
	    value: function getUserPopup(users, groups, departments, sonetgroups) {
	      var _this2 = this;

	      if (!this.popupUsers) {
	        users = users || [];
	        groups = groups || [];
	        departments = departments || [];
	        sonetgroups = sonetgroups || [];
	        var content = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler'></div>"])));
	        var contentTitle = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-title'></div>"])));

	        var onTitleClick = function onTitleClick(event) {
	          var node = event.target;
	          activate(node);
	          adjustSlicker(node);
	        };

	        if (groups.length > 0) {
	          var groupTitleItem = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass='ui-access-rights-popup-toggler-title-item ui-access-rights-popup-toggler-title-item-active'\n\t\t\t\t\t\tdata-role='ui-access-rights-popup-toggler-content-groups'\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_USER_GROUPS'));
	          main_core.Event.bind(groupTitleItem, 'click', onTitleClick.bind(this));
	          main_core.Dom.append(groupTitleItem, contentTitle);
	        }

	        if (departments.length > 0) {
	          var _groupTitleItem = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass='ui-access-rights-popup-toggler-title-item'\n\t\t\t\t\t\tdata-role='ui-access-rights-popup-toggler-content-departments'\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_DEPARTMENTS'));

	          main_core.Event.bind(_groupTitleItem, 'click', onTitleClick.bind(this));
	          main_core.Dom.append(_groupTitleItem, contentTitle);
	        }

	        if (users.length > 0) {
	          var _groupTitleItem2 = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass='ui-access-rights-popup-toggler-title-item'\n\t\t\t\t\t\tdata-role='ui-access-rights-popup-toggler-content-users'\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_STAFF'));

	          main_core.Event.bind(_groupTitleItem2, 'click', onTitleClick.bind(this));
	          main_core.Dom.append(_groupTitleItem2, contentTitle);
	        }

	        if (sonetgroups.length > 0) {
	          var _groupTitleItem3 = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div \n\t\t\t\t\t\tclass='ui-access-rights-popup-toggler-title-item'\n\t\t\t\t\t\tdata-role='ui-access-rights-popup-toggler-content-sonetgroups'\n\t\t\t\t\t>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_SOCNETGROUP'));

	          main_core.Event.bind(_groupTitleItem3, 'click', onTitleClick.bind(this));
	          main_core.Dom.append(_groupTitleItem3, contentTitle);
	        }

	        main_core.Dom.append(main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-title-slicker'></div>"]))), contentTitle);
	        main_core.Dom.append(contentTitle, content);

	        if (groups.length > 0) {
	          main_core.Dom.append(this.getUserPopupTogglerGroup(groups, 'groups'), content);
	        }

	        if (departments.length > 0) {
	          main_core.Dom.append(this.getUserPopupTogglerGroup(departments, 'departments'), content);
	        }

	        if (users.length > 0) {
	          main_core.Dom.append(this.getUserPopupTogglerGroup(users, 'users'), content);
	        }

	        if (sonetgroups.length > 0) {
	          main_core.Dom.append(this.getUserPopupTogglerGroup(sonetgroups, 'sonetgroups'), content);
	        }

	        var footer = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-footer'></div>"])));
	        var footerLink = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-popup-toggler-footer-link'>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD'));
	        main_core.Event.bind(footerLink, 'click', function (event) {
	          _this2.popupUsers.close();

	          _this2.showUserSelectorPopup();

	          event.preventDefault();
	        });
	        main_core.Dom.append(footerLink, footer);
	        main_core.Dom.append(footer, content);

	        var adjustSlicker = function adjustSlicker(node) {
	          if (!main_core.Type.isDomNode(node)) {
	            node = content.querySelector('.ui-access-rights-popup-toggler-title-item-active');
	          }

	          var slicker = content.querySelector('.ui-access-rights-popup-toggler-title-slicker');
	          main_core.Dom.style(slicker, 'left', node.offsetLeft + 'px');
	          main_core.Dom.style(slicker, 'width', node.offsetWidth + 'px');
	        };

	        var activate = function activate(node) {
	          var titles = content.querySelectorAll('.ui-access-rights-popup-toggler-title-item');
	          var contents = content.querySelectorAll('.ui-access-rights-popup-toggler-content');
	          var target = content.querySelector('.' + node.getAttribute('data-role'));
	          titles.forEach(function (item) {
	            main_core.Dom.removeClass(item, 'ui-access-rights-popup-toggler-title-item-active');
	          });
	          contents.forEach(function (item) {
	            main_core.Dom.style(item, 'display', 'none');
	          });
	          main_core.Dom.style(target, 'display', 'block');
	          main_core.Dom.addClass(node, 'ui-access-rights-popup-toggler-title-item-active');
	        };

	        this.popupUsers = main_popup.PopupWindowManager.create(null, this.getAddUserToRole(), {
	          contentPadding: 10,
	          animation: 'fading-slide',
	          content: content,
	          padding: 0,
	          offsetTop: 5,
	          angle: {
	            position: 'top',
	            offset: 35
	          },
	          autoHide: true,
	          closeEsc: true,
	          events: {
	            onPopupShow: function onPopupShow() {
	              setTimeout(function () {
	                var firstActiveNode = content.querySelector('.ui-access-rights-popup-toggler-title-item');

	                if (!firstActiveNode) {
	                  return;
	                }

	                main_core.Dom.addClass(firstActiveNode, 'ui-access-rights-popup-toggler-title-item-active');
	                adjustSlicker(firstActiveNode);
	              });
	            },
	            onPopupClose: function onPopupClose() {
	              _this2.popupUsers.destroy();

	              _this2.popupUsers = null;
	            }
	          }
	        });
	      }

	      return this.popupUsers;
	    }
	  }, {
	    key: "getUserPopupTogglerGroup",
	    value: function getUserPopupTogglerGroup(array, type) {
	      var _this3 = this;

	      var node = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-content'></div>"])));
	      main_core.Dom.addClass(node, 'ui-access-rights-popup-toggler-content-' + type);
	      array.forEach(function (item) {
	        var toggler = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-content-item'></div>"])));

	        if (item.avatar) {
	          var avatar = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a \n\t\t\t\t\t\tclass='ui-access-rights-popup-toggler-content-item-userpic'\n\t\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t\t></a>\n\t\t\t\t"])), main_core.Text.encode(item.name));
	          main_core.Dom.style(avatar, 'backgroundImage', 'url(\'' + encodeURI(item.avatar) + '\')');
	          main_core.Dom.style(avatar, 'backgroundSize', 'cover');
	          main_core.Dom.append(avatar, toggler);
	        } else {
	          var iconClass = '';

	          if (type === 'users') {
	            iconClass = 'ui-icon-common-user';
	          } else if (type === 'groups') {
	            iconClass = 'ui-icon-common-user-group';
	          } else if (type === 'sonetgroups' || type === 'departments') {
	            iconClass = 'ui-icon-common-company';
	          }

	          var emptyAvatar = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["<a class='ui-icon ui-icon-sm' title=\"", "\"><i></i></a>"])), main_core.Text.encode(item.name));
	          main_core.Dom.addClass(emptyAvatar, iconClass);
	          main_core.Dom.style(emptyAvatar, 'margin', '5px 10px');
	          main_core.Dom.append(emptyAvatar, toggler);
	        }

	        main_core.Dom.append(main_core.Tag.render(_templateObject19 || (_templateObject19 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-popup-toggler-content-item-name'>", "</div>"])), main_core.Text.encode(item.name)), toggler);
	        var removeButton = main_core.Tag.render(_templateObject20 || (_templateObject20 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-popup-toggler-content-item-remove'>", "</div>\n\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_REMOVE'));
	        main_core.Event.bind(removeButton, 'click', function () {
	          _this3.userGroup.accessCodes.splice(_this3.userGroup.accessCodes.indexOf(item.key), 1);

	          delete _this3.userGroup.accessCodes[item.key];
	          delete _this3.userGroup.members[item.key];
	          main_core.Dom.remove(toggler);

	          _this3.updateMembers();

	          _this3.adjustPopupUserControl();

	          _this3.grid.getButtonPanel().show();
	        });
	        main_core.Dom.append(removeButton, toggler);
	        main_core.Dom.append(toggler, node);
	      });
	      return node;
	    }
	  }, {
	    key: "showUserSelectorPopup",
	    value: function showUserSelectorPopup() {
	      var _BX$Main$selectorMana;

	      var selectorInstance = (_BX$Main$selectorMana = BX$2.Main.selectorManagerV2.controls[this.popupContainer]) === null || _BX$Main$selectorMana === void 0 ? void 0 : _BX$Main$selectorMana.selectorInstance;

	      if (selectorInstance) {
	        selectorInstance.itemsSelected = {};
	      }

	      BX$2.onCustomEvent(this.openPopupEvent, [{
	        id: this.popupContainer,
	        bindNode: this.getAddUserToRole()
	      }]);
	      BX$2.onCustomEvent('BX.Main.SelectorV2:reInitDialog', [{
	        selectorId: this.popupContainer,
	        selectedItems: this.userGroup.accessCodes
	      }]);
	    }
	  }]);
	  return Member;
	}(Base);

	babelHelpers.defineProperty(Member, "TYPE", 'members');

	var _templateObject$5, _templateObject2$2, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1;

	var Role = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Role, _Base);

	  function Role(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Role);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Role).call(this, options));
	    _this.column = options.column;
	    return _this;
	  }

	  babelHelpers.createClass(Role, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this2 = this;

	      main_core.Event.bind(window, 'click', function (event) {
	        if (event.target === _this2.getRole() || event.target.closest('.ui-access-rights-role')) {
	          return;
	        }

	        _this2.updateRole();

	        _this2.offRoleEditMode();
	      });
	      main_core_events.EventEmitter.subscribe(this.grid, 'onBeforeSave', function () {
	        _this2.updateRole();

	        _this2.offRoleEditMode();
	      });
	    }
	  }, {
	    key: "getRole",
	    value: function getRole() {
	      var _this3 = this;

	      if (this.role) {
	        return this.role;
	      }

	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.updateRole.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:preservation', this.offRoleEditMode.bind(this));
	      this.roleInput = main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t\ttype='text'\n\t\t\t\t\tclass='ui-access-rights-role-input'\n\t\t\t\t\tvalue='", "'\n\t\t\t\t\tplaceholder='", "'\n\t\t\t\t/>\n\t\t\t"])), main_core.Text.encode(this.text), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'));
	      main_core.Event.bind(this.roleInput, 'keydown', function (event) {
	        if (event.keyCode === 13) {
	          _this3.updateRole();

	          _this3.offRoleEditMode();
	        }
	      });
	      main_core.Event.bind(this.roleInput, 'input', function () {
	        _this3.grid.getButtonPanel().show();
	      });
	      this.roleValue = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-role-value'>", "</div>"])), main_core.Text.encode(this.text));
	      var editControl = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-role-edit'></div>"])));
	      main_core.Event.bind(editControl, 'click', this.onRoleEditMode.bind(this));
	      var removeControl = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-role-remove'></div>"])));
	      main_core.Event.bind(removeControl, 'click', this.showPopupConfirm.bind(this));
	      var roleControlWrapper = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-role-controls'>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), editControl, removeControl);
	      this.role = main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-role'>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.roleInput, this.roleValue, roleControlWrapper);
	      return this.role;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      return this.getRole();
	    }
	  }, {
	    key: "onRoleEditMode",
	    value: function onRoleEditMode() {
	      main_core.Dom.addClass(this.getRole(), 'ui-access-rights-role-edit-mode');
	      this.roleInput.focus();
	    }
	  }, {
	    key: "showPopupConfirm",
	    value: function showPopupConfirm() {
	      var _this4 = this;

	      if (!this.popupConfirm) {
	        /**@ToDO check role*/
	        this.popupConfirm = main_popup.PopupWindowManager.create(null, this.getRole(), {
	          width: 250,
	          overlay: true,
	          contentPadding: 10,
	          content: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE_THIS_ROLE'),
	          animation: 'fading-slide'
	        });
	        this.popupConfirm.setButtons([new BX.UI.Button({
	          text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_REMOVE'),
	          className: 'ui-btn ui-btn-sm ui-btn-primary',
	          events: {
	            click: function click() {
	              _this4.popupConfirm.close();

	              main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:removeRole', _this4);
	            }
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_POPUP_CANCEL'),
	          className: 'ui-btn ui-btn-sm ui-btn-link',
	          events: {
	            click: function click() {
	              _this4.popupConfirm.close();
	            }
	          }
	        })]);
	      }

	      this.popupConfirm.show();
	    }
	  }, {
	    key: "updateRole",
	    value: function updateRole() {
	      if (this.roleValue.innerHTML === this.roleInput.value || this.roleInput.value === '') {
	        return;
	      }

	      this.text = this.roleInput.value;
	      this.userGroup = this.column.getUserGroup();
	      this.roleValue.innerText = this.roleInput.value;
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:updateRole', this);
	    }
	  }, {
	    key: "offRoleEditMode",
	    value: function offRoleEditMode() {
	      main_core.Dom.removeClass(this.getRole(), 'ui-access-rights-role-edit-mode');
	    }
	  }]);
	  return Role;
	}(Base);

	babelHelpers.defineProperty(Role, "TYPE", 'role');

	var _templateObject$6;

	var Changer = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Changer, _Base);

	  function Changer(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Changer);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Changer).call(this, options));
	    _this.isModify = false;
	    return _this;
	  }

	  babelHelpers.createClass(Changer, [{
	    key: "getChanger",
	    value: function getChanger() {
	      if (!this.changer) {
	        this.changer = main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["<a class='ui-access-rights-column-item-changer'></a>"])));
	      }

	      return this.changer;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.offChanger.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refreshStatus.bind(this));
	    }
	  }, {
	    key: "refreshStatus",
	    value: function refreshStatus() {
	      main_core.Dom.removeClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	    }
	  }, {
	    key: "offChanger",
	    value: function offChanger() {
	      var _this2 = this;

	      if (this.isModify) {
	        setTimeout(function () {
	          _this2.refreshStatus();
	        });
	      }
	    }
	  }, {
	    key: "adjustChanger",
	    value: function adjustChanger() {
	      this.isModify = !this.isModify;
	      main_core.Dom.toggleClass(this.getChanger(), 'ui-access-rights-column-item-changer-on');
	    }
	  }]);
	  return Changer;
	}(Base);

	var Toggler = /*#__PURE__*/function (_Changer) {
	  babelHelpers.inherits(Toggler, _Changer);

	  function Toggler(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Toggler);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Toggler).call(this, options));
	    _this.switcher = new BX.UI.Switcher({
	      size: 'small',
	      checked: _this.currentValue === '1',
	      handlers: {
	        checked: function checked() {
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOn', babelHelpers.assertThisInitialized(_this));
	        },
	        unchecked: function unchecked() {
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:accessOff', babelHelpers.assertThisInitialized(_this));
	        },
	        toggled: function toggled() {
	          _this.adjustChanger();

	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', babelHelpers.assertThisInitialized(_this));
	        }
	      }
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Toggler, [{
	    key: "offChanger",
	    value: function offChanger() {
	      if (this.isModify) {
	        this.switcher.check(!this.switcher.isChecked());
	      }

	      babelHelpers.get(babelHelpers.getPrototypeOf(Toggler.prototype), "offChanger", this).call(this);
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      main_core.Dom.append(this.switcher.getNode(), this.getChanger());
	      return this.getChanger();
	    }
	  }]);
	  return Toggler;
	}(Changer);

	babelHelpers.defineProperty(Toggler, "TYPE", 'toggler');

	var _templateObject$7, _templateObject2$3, _templateObject3$2;

	var Controller = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Controller, _Base);

	  function Controller() {
	    babelHelpers.classCallCheck(this, Controller);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Controller).apply(this, arguments));
	  }

	  babelHelpers.createClass(Controller, [{
	    key: "render",
	    value: function render() {
	      var _this = this;

	      if (!this.controller) {
	        this.controllerLink = main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-column-item-controller-link'>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_CREATE_ROLE'));
	        this.controllerMenu = main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-column-item-controller-link'>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_COPY_ROLE'));
	        main_core.Event.bind(this.controllerMenu, 'click', function () {
	          if (_this.popupMenu) {
	            _this.popupMenu.close();
	          } else if (_this.grid.getUserGroups().length > 0) {
	            _this.getPopupMenu(_this.grid.getUserGroups()).show();
	          }
	        });
	        this.toggleControllerMenu();
	        this.controller = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-column-item-controller'>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.controllerLink, this.controllerMenu);
	        main_core.Event.bind(this.controllerLink, 'click', function () {
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:addRole', [{
	            id: '0',
	            title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
	            accessRights: [],
	            members: [],
	            accessCodes: [],
	            type: Role.TYPE
	          }]);
	          main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', _this);

	          _this.toggleControllerMenu();

	          _this.grid.lock();
	        });
	        main_core_events.EventEmitter.subscribe('BX.UI.AccessRights.ColumnItem:removeRole', this.toggleControllerMenu.bind(this));
	      }

	      return this.controller;
	    }
	  }, {
	    key: "getPopupMenu",
	    value: function getPopupMenu(options) {
	      var _this2 = this;

	      if (!options) {
	        return;
	      }

	      var menuItems = [];
	      options.map(function (data) {
	        menuItems.push({
	          text: main_core.Text.encode(data.title),
	          onclick: function onclick() {
	            var accessRightsCopy = Object.assign([], data.accessRights);
	            var accessCodesCopy = Object.assign([], data.accessCodes);
	            main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:copyRole', [{
	              id: '0',
	              title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLE_NAME'),
	              accessRights: accessRightsCopy,
	              accessCodes: accessCodesCopy,
	              type: Role.TYPE,
	              members: data.members
	            }]);
	            main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', _this2);

	            _this2.popupMenu.destroy();
	          }
	        });
	      });
	      return this.popupMenu = new main_popup.Menu('ui_accessrights_copy_role_list', this.controllerMenu, menuItems, {
	        events: {
	          onPopupClose: function onPopupClose() {
	            _this2.popupMenu.destroy();

	            _this2.popupMenu = null;
	          }
	        }
	      });
	    }
	  }, {
	    key: "toggleControllerMenu",
	    value: function toggleControllerMenu() {
	      if (this.grid.getUserGroups().length === 0) {
	        main_core.Dom.addClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
	      } else {
	        main_core.Dom.removeClass(this.controllerMenu, 'ui-access-rights-column-item-controller-link--disabled');
	      }
	    }
	  }]);
	  return Controller;
	}(Base);

	var _templateObject$8;

	var VariableSelector = /*#__PURE__*/function (_Changer) {
	  babelHelpers.inherits(VariableSelector, _Changer);

	  function VariableSelector(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, VariableSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VariableSelector).call(this, options));
	    _this.selectedValues = [_this.currentValue];
	    _this.variables = options.variables || [];
	    return _this;
	  }

	  babelHelpers.createClass(VariableSelector, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this$getSelected$tit, _this$getSelected;

	      var title = (_this$getSelected$tit = (_this$getSelected = this.getSelected()) === null || _this$getSelected === void 0 ? void 0 : _this$getSelected.title) !== null && _this$getSelected$tit !== void 0 ? _this$getSelected$tit : main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
	      var variablesValue = main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-column-item-text-link'>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Text.encode(title));
	      main_core.Event.bind(variablesValue, 'click', this.showVariablesPopup.bind(this));
	      main_core.Dom.append(variablesValue, this.getChanger());
	      return this.getChanger();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh() {
	      if (this.isModify) {
	        this.currentValue = this.selectedValues[0];
	        this.reset();
	      }
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      if (this.isModify) {
	        this.selectedValues = [this.currentValue];
	        this.getChanger().innerHTML = '';
	        this.adjustChanger();
	        this.render();
	      }
	    }
	  }, {
	    key: "getSelected",
	    value: function getSelected() {
	      var _this2 = this;

	      var selected = this.variables.filter(function (variable) {
	        return _this2.selectedValues.includes(variable.id);
	      });
	      return selected[0];
	    }
	  }, {
	    key: "showVariablesPopup",
	    value: function showVariablesPopup(event) {
	      var _this3 = this;

	      var menuItems = [];
	      this.variables.map(function (data) {
	        menuItems.push({
	          id: data.id,
	          text: data.title,
	          onclick: _this3.select.bind(_this3)
	        });
	      });
	      main_popup.PopupMenu.show('ui-access-rights-column-item-popup-variables', event.target, menuItems, {
	        autoHide: true,
	        events: {
	          onPopupClose: function onPopupClose() {
	            main_popup.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
	          }
	        }
	      });
	    }
	  }, {
	    key: "select",
	    value: function select(event, item) {
	      var _item$getMenuWindow;

	      this.selectedValues = [item.id];
	      (_item$getMenuWindow = item.getMenuWindow()) === null || _item$getMenuWindow === void 0 ? void 0 : _item$getMenuWindow.close();
	      this.getChanger().innerHTML = '';
	      this.render();
	      this.adjustChanger();
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	    }
	  }]);
	  return VariableSelector;
	}(Changer);

	babelHelpers.defineProperty(VariableSelector, "TYPE", 'variables');

	var _templateObject$9;

	var UserGroupTitle = /*#__PURE__*/function (_Title) {
	  babelHelpers.inherits(UserGroupTitle, _Title);

	  function UserGroupTitle() {
	    babelHelpers.classCallCheck(this, UserGroupTitle);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserGroupTitle).apply(this, arguments));
	  }

	  babelHelpers.createClass(UserGroupTitle, [{
	    key: "render",
	    value: function render() {
	      return main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div \n\t\t\t\tclass='ui-access-rights-column-item-text'\n\t\t\t\tdata-id='", "'\n\t\t\t>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getId(), main_core.Text.encode(this.text));
	    }
	  }]);
	  return UserGroupTitle;
	}(Title);

	babelHelpers.defineProperty(UserGroupTitle, "TYPE", 'userGroupTitle');

	var _templateObject$a, _templateObject2$4, _templateObject3$3;

	var Footer = /*#__PURE__*/function (_DefaultFooter) {
	  babelHelpers.inherits(Footer, _DefaultFooter);

	  function Footer(dialog, options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Footer);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Footer).call(this, dialog, options));
	    _this.selectAllButton = main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-footer-link ui-selector-search-footer-label--hide\">", "</div>"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_SELECT_LABEL'));
	    main_core.Event.bind(_this.selectAllButton, 'click', _this.selectAll.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.deselectAllButton = main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-selector-footer-link ui-selector-search-footer-label--hide\">", "</div>"])), main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_DESELECT_LABEL'));
	    main_core.Event.bind(_this.deselectAllButton, 'click', _this.deselectAll.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.getDialog().subscribe('Item:onSelect', _this.onItemStatusChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.getDialog().subscribe('Item:onDeselect', _this.onItemStatusChange.bind(babelHelpers.assertThisInitialized(_this)));

	    return _this;
	  }

	  babelHelpers.createClass(Footer, [{
	    key: "getContent",
	    value: function getContent() {
	      this.toggleSelectButtons();
	      return main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-selector-search-footer-box\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.selectAllButton, this.deselectAllButton);
	    }
	  }, {
	    key: "toggleSelectButtons",
	    value: function toggleSelectButtons() {
	      if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length) {
	        if (main_core.Dom.hasClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide')) {
	          main_core.Dom.addClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	          main_core.Dom.removeClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	        }
	      } else if (main_core.Dom.hasClass(this.selectAllButton, 'ui-selector-search-footer-label--hide')) {
	        main_core.Dom.addClass(this.deselectAllButton, 'ui-selector-search-footer-label--hide');
	        main_core.Dom.removeClass(this.selectAllButton, 'ui-selector-search-footer-label--hide');
	      }
	    }
	  }, {
	    key: "selectAll",
	    value: function selectAll() {
	      if (this.getDialog().getSelectedItems().length === this.getDialog().getItems().length) {
	        return;
	      }

	      this.getDialog().getItems().forEach(function (item) {
	        item.select();
	      });
	    }
	  }, {
	    key: "deselectAll",
	    value: function deselectAll() {
	      this.getDialog().getSelectedItems().forEach(function (item) {
	        item.deselect();
	      });
	    }
	  }, {
	    key: "onItemStatusChange",
	    value: function onItemStatusChange() {
	      this.toggleSelectButtons();
	    }
	  }]);
	  return Footer;
	}(ui_entitySelector.DefaultFooter);

	var _templateObject$b;

	var MultiSelector = /*#__PURE__*/function (_Changer) {
	  babelHelpers.inherits(MultiSelector, _Changer);

	  function MultiSelector(options) {
	    var _options$enableSearch, _options$showAvatars, _options$compactView;

	    var _this;

	    babelHelpers.classCallCheck(this, MultiSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MultiSelector).call(this, options));
	    _this.variables = options.variables || [];
	    _this.enableSearch = (_options$enableSearch = options.enableSearch) !== null && _options$enableSearch !== void 0 ? _options$enableSearch : false;
	    _this.placeholder = options.placeholder || '';
	    _this.hintTitle = options.hintTitle || '';
	    _this.allSelectedCode = main_core.Text.toNumber(options.allSelectedCode || -1);
	    _this.showAvatars = (_options$showAvatars = options.showAvatars) !== null && _options$showAvatars !== void 0 ? _options$showAvatars : true;
	    _this.compactView = (_options$compactView = options.compactView) !== null && _options$compactView !== void 0 ? _options$compactView : false;
	    _this.currentValue = main_core.Type.isArray(options.currentValue) ? options.currentValue : [];
	    _this.currentValue = _this.currentValue.map(function (value) {
	      return main_core.Text.toNumber(value);
	    });
	    _this.selectedValues = _this.currentValue;
	    _this.variables = _this.variables.map(function (item) {
	      item.entityId = item.entityId || 'editor-right-item';
	      item.tabs = 'recents';
	      return item;
	    });
	    _this.selector = _this.createSelector();
	    return _this;
	  }

	  babelHelpers.createClass(MultiSelector, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:reset', this.reset.bind(this));
	      main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.refresh.bind(this));
	    }
	  }, {
	    key: "createSelector",
	    value: function createSelector() {
	      return new ui_entitySelector.Dialog({
	        height: 300,
	        id: this.getId(),
	        context: 'editor-right-items',
	        enableSearch: this.enableSearch,
	        multiple: true,
	        dropdownMode: true,
	        compactView: this.compactView,
	        showAvatars: this.showAvatars,
	        selectedItems: this.getSelected(),
	        searchOptions: {
	          allowCreateItem: false
	        },
	        events: {
	          'Item:onSelect': this.setSelectedInputs.bind(this),
	          'Item:onDeselect': this.setSelectedInputs.bind(this)
	        },
	        entities: [{
	          id: 'editor-right-item'
	        }],
	        items: this.variables,
	        footer: Footer
	      });
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var title = '';

	      if (this.includesSelected(this.allSelectedCode)) {
	        title = main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ALL_ACCEPTED');
	      } else {
	        var _this$getSelected;

	        var titles = [];
	        (_this$getSelected = this.getSelected()) === null || _this$getSelected === void 0 ? void 0 : _this$getSelected.forEach(function (item) {
	          titles.push(item.title);
	        });

	        if (titles.length > 0) {
	          var firstItem = titles[0];
	          title = titles.length - 1 > 0 ? main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_HAS_SELECTED_ITEMS', {
	            '#FIRST_ITEM_NAME#': firstItem.length > 10 ? firstItem.slice(0, 10) + '...' : firstItem,
	            '#COUNT_REST_ITEMS#': titles.length - 1
	          }) : firstItem;
	        } else {
	          title = main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ADD');
	        }
	      }

	      var hint = '';

	      if (this.selector.getSelectedItems().length > 0) {
	        var hintTitle = main_core.Type.isStringFilled(this.hintTitle) ? this.hintTitle : main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_SELECTED_ITEMS_TITLE');
	        hint += '<p>' + hintTitle + ':</p>';
	        hint += '<ul>';
	        this.selector.getSelectedItems().forEach(function (item) {
	          return hint += '<li>' + main_core.Text.encode(item.getTitle());
	        });
	        hint += '</ul>';
	      }

	      var variablesValue = main_core.Tag.render(_templateObject$b || (_templateObject$b = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-column-item-text-link' data-hint-html data-hint-no-icon data-hint=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), main_core.Text.encode(hint), main_core.Text.encode(title));
	      main_core.Event.bind(variablesValue, 'click', this.showSelector.bind(this));
	      main_core.Dom.append(variablesValue, this.getChanger());
	      BX.UI.Hint.init(this.getChanger());
	      return this.getChanger();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh() {
	      if (this.isModify) {
	        this.currentValue = this.selectedValues;
	        this.reset();
	      }
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      if (this.isModify) {
	        this.selectedValues = this.currentValue;
	        this.selector = this.createSelector();
	        this.getChanger().innerHTML = '';
	        this.adjustChanger();
	        this.render();
	      }
	    }
	  }, {
	    key: "getSelected",
	    value: function getSelected() {
	      var _this2 = this;

	      if (this.includesSelected(this.allSelectedCode)) {
	        return this.variables;
	      }

	      return this.variables.filter(function (variable) {
	        return _this2.includesSelected(variable.id);
	      });
	    }
	  }, {
	    key: "includesSelected",
	    value: function includesSelected(item) {
	      return this.selectedValues.includes(main_core.Text.toNumber(item));
	    }
	  }, {
	    key: "showSelector",
	    value: function showSelector(event) {
	      this.selector.show();
	    }
	  }, {
	    key: "setSelectedInputs",
	    value: function setSelectedInputs() {
	      var _this3 = this;

	      var selected = this.selector.getSelectedItems();
	      this.selectedValues = [];

	      if (selected.length === this.variables.length) {
	        this.selectedValues.push(this.allSelectedCode);
	      } else {
	        selected.forEach(function (item) {
	          _this3.selectedValues.push(main_core.Text.toNumber(item.id));
	        });
	      }

	      this.getChanger().innerHTML = '';

	      if (!this.isModify) {
	        this.adjustChanger();
	      }

	      this.render();
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:update', this);
	      main_core_events.EventEmitter.emit('BX.UI.AccessRights.ColumnItem:selectAccessItems', this);
	    }
	  }]);
	  return MultiSelector;
	}(Changer);

	babelHelpers.defineProperty(MultiSelector, "TYPE", 'multivariables');

	var _templateObject$c;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var ColumnItem = /*#__PURE__*/function () {
	  function ColumnItem(options) {
	    babelHelpers.classCallCheck(this, ColumnItem);
	    this.options = options;
	    this.type = options.type ? options.type : null;
	    this.hint = options.hint ? options.hint : null;
	    this.controller = options.controller ? options.controller : null;
	    this.column = options.column;
	  }

	  babelHelpers.createClass(ColumnItem, [{
	    key: "render",
	    value: function render() {
	      var item = null;
	      var container = main_core.Tag.render(_templateObject$c || (_templateObject$c = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-column-item'></div>"])));
	      this.options.container = container;

	      if (this.type === Role.TYPE) {
	        item = new Role(this.options);

	        if (this.column.newColumn) {
	          setTimeout(function () {
	            item.onRoleEditMode();
	            item.roleInput.value = '';
	          });
	        }
	      } else if (this.type === Member.TYPE) {
	        item = new Member(this.options);
	      } else if (this.type === Title.TYPE) {
	        item = new Title(this.options);
	      } else if (this.type === VariableSelector.TYPE) {
	        item = new VariableSelector(this.options);
	      } else if (this.type === MultiSelector.TYPE) {
	        item = new MultiSelector(this.options);
	      } else if (this.type === Toggler.TYPE) {
	        item = new Toggler(this.options);
	      }

	      if (item) {
	        main_core.Dom.append(item.render(), container);
	      }

	      if (this.hint) {
	        var hintOptions = _objectSpread({
	          className: 'ui-access-rights-column-item-notify'
	        }, this.options);

	        main_core.Dom.append(new Hint(hintOptions).render(), container);
	      }

	      if (this.type === UserGroupTitle.TYPE) {
	        main_core.Dom.append(new UserGroupTitle(this.options).render(), container);
	      }

	      if (this.controller) {
	        main_core.Dom.append(new Controller(this.options).render(), container);
	      }

	      return container;
	    }
	  }]);
	  return ColumnItem;
	}();
	var namespace$1 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$1.ColumnItem = ColumnItem;

	var _templateObject$d;

	var Column = /*#__PURE__*/function () {
	  function Column(options) {
	    babelHelpers.classCallCheck(this, Column);
	    this.layout = {
	      container: null
	    };
	    this.grid = options.grid ? options.grid : null;
	    this.items = options.items ? options.items : [];
	    this.userGroup = options.userGroup ? options.userGroup : null;
	    this.accessCodes = options.accessCodes ? options.accessCodes : null;
	    this.section = options.section ? options.section : null;
	    this.headSection = options.headSection;
	    this.newColumn = options.newColumn ? options.newColumn : null;
	    this.openPopupEvent = options.grid.openPopupEvent ? options.grid.openPopupEvent : null;
	    this.popupContainer = options.grid.popupContainer ? options.grid.popupContainer : null;
	  }

	  babelHelpers.createClass(Column, [{
	    key: "getItem",
	    value: function getItem(options) {
	      options = options || {};
	      var param = {};

	      if (options.type === UserGroupTitle.TYPE) {
	        param = {
	          type: options.type,
	          text: options.title,
	          controller: options.controller
	        };
	      }

	      if (options.type === Title.TYPE) {
	        param = {
	          id: options.id,
	          type: options.type,
	          hint: options.hint,
	          text: options.title,
	          controller: options.controller
	        };
	      }

	      if (options.type === Toggler.TYPE) {
	        param = {
	          type: options.type,
	          access: options.access
	        };
	      }

	      if (options.type === VariableSelector.TYPE || options.type === MultiSelector.TYPE) {
	        param = {
	          type: options.type,
	          text: options.title,
	          variables: options.variables,
	          access: options.access
	        };
	      }

	      if (options.type === MultiSelector.TYPE) {
	        param.allSelectedCode = options.allSelectedCode;
	        param.enableSearch = options.enableSearch;
	        param.showAvatars = options.showAvatars;
	        param.compactView = options.compactView;
	        param.hintTitle = options.hintTitle;
	      }

	      if (options.type === Role.TYPE) {
	        param = {
	          type: options.type,
	          text: options.title
	        };
	      }

	      if (options.type === Member.TYPE) {
	        var accessCodes = [];

	        for (var item in options.members) {
	          accessCodes[item] = options.members[item].type;
	        }

	        param = {
	          type: options.type,
	          accessCodes: accessCodes
	        };
	      }

	      param.column = this;
	      param.userGroup = this.userGroup;
	      param.openPopupEvent = this.openPopupEvent;
	      param.popupContainer = this.popupContainer;
	      param.currentValue = null;
	      param.grid = this.grid;

	      if (options.type === VariableSelector.TYPE || options.type === MultiSelector.TYPE || options.type === Toggler.TYPE) {
	        var _param$userGroup$acce, _param$userGroup;

	        var accessId = param.access.id.toString();
	        var accessRights = (_param$userGroup$acce = (_param$userGroup = param.userGroup) === null || _param$userGroup === void 0 ? void 0 : _param$userGroup.accessRights) !== null && _param$userGroup$acce !== void 0 ? _param$userGroup$acce : [];

	        for (var i = 0; i < accessRights.length; i++) {
	          if (accessId === accessRights[i].id.toString()) {
	            if (options.type === MultiSelector.TYPE) {
	              var _param$currentValue;

	              param.currentValue = (_param$currentValue = param.currentValue) !== null && _param$currentValue !== void 0 ? _param$currentValue : [];
	              param.currentValue.push(accessRights[i].value);
	            } else {
	              param.currentValue = accessRights[i].value;
	            }
	          }
	        }
	      }

	      return new ColumnItem(param);
	    }
	  }, {
	    key: "getUserGroup",
	    value: function getUserGroup() {
	      return this.userGroup;
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      var _this = this;

	      if (main_core.Dom.hasClass(this.layout.container, 'ui-access-rights-column-new')) {
	        this.resetClassNew();
	      }

	      main_core.Dom.addClass(this.layout.container, 'ui-access-rights-column-remove');
	      main_core.Dom.style(this.layout.container, 'width', this.layout.container.offsetWidth + 'px');
	      main_core.Event.bind(this.layout.container, 'animationend', function () {
	        main_core.Dom.style(_this.layout.container, 'minWidth', '0px');
	        main_core.Dom.style(_this.layout.container, 'maxWidth', '0px');
	      });
	      setTimeout(function () {
	        main_core.Dom.remove(_this.layout.container);
	      }, 500);
	    }
	  }, {
	    key: "resetClassNew",
	    value: function resetClassNew() {
	      main_core.Dom.removeClass(this.layout.container, 'ui-access-rights-column-new');
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      if (!this.layout.container) {
	        var itemsFragment = document.createDocumentFragment();

	        if (this.headSection) {
	          this.userGroup.type = Role.TYPE;
	          main_core.Dom.append(this.getItem(this.userGroup).render(), itemsFragment);
	          this.userGroup.type = Member.TYPE;
	          main_core.Dom.append(this.getItem(this.userGroup).render(), itemsFragment);
	        }

	        this.items.map(function (data) {
	          var item = _this2.getItem(data);

	          main_core.Dom.append(item.render(), itemsFragment);
	        });
	        this.layout.container = main_core.Tag.render(_templateObject$d || (_templateObject$d = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-column'></div>"])));

	        if (this.newColumn) {
	          main_core.Dom.addClass('ui-access-rights-column-new', this.layout.container);
	        }

	        main_core_events.EventEmitter.subscribe('BX.UI.AccessRights:refresh', this.resetClassNew.bind(this));
	        main_core.Dom.append(itemsFragment, this.layout.container);
	        return this.layout.container;
	      }
	    }
	  }]);
	  return Column;
	}();
	var namespace$2 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$2.Column = Column;

	var _templateObject$e, _templateObject2$5, _templateObject3$4, _templateObject4$2, _templateObject5$2, _templateObject6$2, _templateObject7$1, _templateObject8$1;

	var Section = /*#__PURE__*/function () {
	  function Section(options) {
	    var _options$id;

	    babelHelpers.classCallCheck(this, Section);
	    this.id = (_options$id = options.id) !== null && _options$id !== void 0 ? _options$id : null;
	    this.headSection = options.headSection ? options.headSection : null;
	    this.title = options.title;
	    this.hint = options.hint;
	    this.rights = options.rights ? options.rights : [];
	    this.userGroups = options.userGroups ? options.userGroups : [];
	    this.grid = options.grid ? options.grid : null;
	    this.layout = {
	      title: null,
	      headColumn: null,
	      columns: null,
	      content: null,
	      earLeft: null,
	      earRight: null
	    };
	    this.scroll = 0;
	    this.earTimer = null;
	    this.earLeftTimer = null;
	    this.earRightTimer = null;
	    this.columns = [];
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Section, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe(this.grid, 'AccessRights.Section:scroll', function (event) {
	        var _event$getData = event.getData(),
	            _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	            object = _event$getData2[0];

	        if (_this.title !== object.title) {
	          _this.getColumnsContainer().scrollLeft = object.getScroll();
	        }

	        object.adjustEars();
	        main_popup.PopupMenu.destroy('ui-access-rights-column-item-popup-variables');
	      });
	      main_core.Event.bind(window, 'resize', this.adjustEars.bind(this));
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      return this.grid;
	    }
	  }, {
	    key: "addColumn",
	    value: function addColumn(param) {
	      if (!param) {
	        return;
	      }

	      var options = Object.assign({}, param);
	      options.userGroup = param;
	      var column = this.getColumn(options);
	      main_core.Dom.append(column.render(), this.layout.columns);
	      this.columns.push(column);
	    }
	  }, {
	    key: "getColumn",
	    value: function getColumn(options) {
	      var controls = [];
	      this.rights.map(function (data) {
	        var isVariable = data.type === VariableSelector.TYPE || data.type === MultiSelector.TYPE;
	        controls.push({
	          type: data.type,
	          title: isVariable ? data.title : null,
	          hint: data.hint,
	          variables: isVariable ? data.variables : [],
	          enableSearch: isVariable ? data.enableSearch : null,
	          showAvatars: isVariable ? data.showAvatars : false,
	          compactView: isVariable ? data.compactView : false,
	          hintTitle: isVariable ? data.hintTitle : null,
	          allSelectedCode: isVariable ? data.allSelectedCode : null,
	          access: data
	        });
	      });
	      return new Column({
	        items: controls,
	        userGroup: options.userGroup ? options.userGroup : null,
	        section: this,
	        headSection: options.headSection,
	        grid: this.grid,
	        newColumn: options.newColumn ? options.newColumn : null
	      });
	    }
	  }, {
	    key: "removeColumn",
	    value: function removeColumn(param) {
	      if (!param) {
	        return;
	      }

	      for (var i = 0; i < this.columns.length; i++) {
	        if (param.userGroup === this.columns[i].userGroup) {
	          this.columns[i].remove();
	          break;
	        }
	      }
	    }
	  }, {
	    key: "addHeadColumn",
	    value: function addHeadColumn() {
	      var titles = [];

	      if (!this.headSection) {
	        this.rights.map(function (data) {
	          titles.push({
	            id: data.id,
	            type: Title.TYPE,
	            title: data.title,
	            hint: data.hint
	          });
	        });
	      }

	      if (this.headSection) {
	        titles = [{
	          type: UserGroupTitle.TYPE,
	          title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_ROLES'),
	          controller: true
	        }, {
	          type: UserGroupTitle.TYPE,
	          title: main_core.Loc.getMessage('JS_UI_ACCESSRIGHTS_EMPLOYEES_AND_DEPARTMENTS'),
	          controller: false
	        }];
	      }

	      var column = new Column({
	        items: titles,
	        section: this,
	        grid: this.grid
	      });
	      main_core.Dom.append(column.render(), this.layout.headColumn);
	      return column;
	    }
	  }, {
	    key: "getColumnsContainer",
	    value: function getColumnsContainer() {
	      if (!this.layout.columns) {
	        var column = main_core.Tag.render(_templateObject$e || (_templateObject$e = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section-wrapper'></div>"])));
	        main_core.Event.bind(column, 'scroll', this.adjustScroll.bind(this));
	        this.layout.columns = column;
	      }

	      return this.layout.columns;
	    }
	  }, {
	    key: "getTitleNode",
	    value: function getTitleNode() {
	      var node = main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section-title'>", "</div>"])), main_core.Text.encode(this.title));

	      if (this.hint) {
	        var hintNode = new Hint({
	          hint: this.hint,
	          className: 'ui-access-rights-section-title-hint'
	        });
	        node.appendChild(hintNode.render());
	      }

	      return node;
	    }
	  }, {
	    key: "adjustScroll",
	    value: function adjustScroll() {
	      if (main_core.Text.toNumber(this.scroll) !== main_core.Text.toNumber(this.getColumnsContainer().scrollLeft)) {
	        this.scroll = this.getColumnsContainer().scrollLeft;
	        main_core_events.EventEmitter.emit(this.grid, "AccessRights.Section:scroll", [this]);
	      }
	    }
	  }, {
	    key: "adjustEars",
	    value: function adjustEars() {
	      var container = this.getColumnsContainer();
	      var scroll = container.scrollLeft;
	      var isLeftVisible = scroll > 0;
	      var isRightVisible = container.scrollWidth > Math.round(scroll + container.offsetWidth);
	      this.getContentContainer().classList[isLeftVisible ? 'add' : 'remove']('ui-access-rights-section-ear-left-shown');
	      this.getContentContainer().classList[isRightVisible ? 'add' : 'remove']('ui-access-rights-section-ear-right-shown');
	    }
	  }, {
	    key: "getContentContainer",
	    value: function getContentContainer() {
	      if (!this.layout.content) {
	        this.layout.content = main_core.Tag.render(_templateObject3$4 || (_templateObject3$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class='ui-access-rights-section-content'>\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.getColumnsContainer(), this.getEarLeft(), this.getEarRight());
	      }

	      return this.layout.content;
	    }
	  }, {
	    key: "getEarLeft",
	    value: function getEarLeft() {
	      var _this2 = this;

	      if (!this.layout.earLeft) {
	        this.layout.earLeft = main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section-ear-left'></div>"])));
	        main_core.Event.bind(this.layout.earLeft, 'mouseenter', function () {
	          _this2.stopAutoScroll();

	          _this2.earLeftTimer = setTimeout(function () {
	            _this2.scrollToLeft();
	          }, 110);
	        });
	        main_core.Event.bind(this.layout.earLeft, 'mouseleave', function () {
	          clearTimeout(_this2.earLeftTimer);

	          _this2.stopAutoScroll();
	        });
	      }

	      return this.layout.earLeft;
	    }
	  }, {
	    key: "getEarRight",
	    value: function getEarRight() {
	      var _this3 = this;

	      if (!this.layout.earRight) {
	        this.layout.earRight = main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section-ear-right'></div>"])));
	        main_core.Event.bind(this.layout.earRight, 'mouseenter', function () {
	          _this3.stopAutoScroll();

	          _this3.earRightTimer = setTimeout(function () {
	            _this3.scrollToRight();
	          }, 110);
	        });
	        main_core.Event.bind(this.layout.earRight, 'mouseleave', function () {
	          clearTimeout(_this3.earRightTimer);

	          _this3.stopAutoScroll();
	        });
	      }

	      return this.layout.earRight;
	    }
	  }, {
	    key: "scrollToRight",
	    value: function scrollToRight(param, stop) {
	      var _this4 = this;

	      var interval = param ? 2 : 20;
	      this.earTimer = setInterval(function () {
	        _this4.getColumnsContainer().scrollLeft += 10;

	        if (param && param <= _this4.getColumnsContainer().scrollLeft) {
	          _this4.stopAutoScroll();
	        }
	      }, interval);

	      if (stop === 'stop') {
	        setTimeout(function () {
	          _this4.stopAutoScroll();

	          _this4.getGrid().unlock();
	        }, param * 2);
	      }
	    }
	  }, {
	    key: "scrollToLeft",
	    value: function scrollToLeft() {
	      var _this5 = this;

	      this.earTimer = setInterval(function () {
	        _this5.getColumnsContainer().scrollLeft -= 10;
	      }, 20);
	    }
	  }, {
	    key: "stopAutoScroll",
	    value: function stopAutoScroll() {
	      clearInterval(this.earTimer);
	    }
	  }, {
	    key: "getScroll",
	    value: function getScroll() {
	      return this.scroll;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this$grid$getUserGro;

	      var title = this.title ? this.getTitleNode() : null;
	      var sectionContainer = main_core.Tag.render(_templateObject6$2 || (_templateObject6$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-access-rights-section'>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), title, this.getMainContainer());

	      if (this.headSection) {
	        main_core.Dom.addClass(sectionContainer, 'ui-access-rights--head-section');
	      }

	      this.addHeadColumn();
	      var columnsFragment = document.createDocumentFragment();
	      var userGroups = (_this$grid$getUserGro = this.grid.getUserGroups()) !== null && _this$grid$getUserGro !== void 0 ? _this$grid$getUserGro : [];

	      for (var i = 0; i < userGroups.length; i++) {
	        var column = this.getColumn({
	          headSection: this.headSection ? this.headSection : null,
	          userGroup: userGroups[i]
	        });
	        this.columns.push(column);
	        main_core.Dom.append(column.render(), columnsFragment);
	      }

	      main_core.Dom.append(columnsFragment, this.getColumnsContainer());
	      return sectionContainer;
	    }
	  }, {
	    key: "getMainContainer",
	    value: function getMainContainer() {
	      this.layout.headColumn = main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["<div class='ui-access-rights-section-head'></div>"])));
	      return main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-access-rights-section-container'>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.layout.headColumn, this.getContentContainer());
	    }
	  }]);
	  return Section;
	}();
	var namespace$3 = main_core.Reflection.namespace('BX.UI.AccessRights');
	namespace$3.Section = Section;

	exports.Grid = Grid;
	exports.Section = Section;
	exports.Column = Column;
	exports.ColumnItem = ColumnItem;

}((this.BX.UI = this.BX.UI || {}),BX,BX,BX,BX.Main,BX.Event,BX.UI.EntitySelector,BX));
//# sourceMappingURL=accessrights.bundle.js.map
