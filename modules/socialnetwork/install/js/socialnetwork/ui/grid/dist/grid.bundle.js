this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
this.BX.Socialnetwork.UI = this.BX.Socialnetwork.UI || {};
(function (exports,main_popup,main_loader,main_core,main_core_events) {
	'use strict';

	var Pin = /*#__PURE__*/function () {
	  function Pin(params) {
	    babelHelpers.classCallCheck(this, Pin);
	    this.grid = params.gridInstance;
	    this.bindEvents();
	    this.colorPinnedRows();
	  }

	  babelHelpers.createClass(Pin, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Main.grid:paramsUpdated', this.onParamsUpdated.bind(this));
	    }
	  }, {
	    key: "onParamsUpdated",
	    value: function onParamsUpdated() {
	      this.colorPinnedRows();
	    }
	  }, {
	    key: "colorPinnedRows",
	    value: function colorPinnedRows() {
	      var _this = this;

	      this.getRows().forEach(function (row) {
	        var node = row.getNode();
	        _this.getIsPinned(row.getId()) ? main_core.Dom.addClass(node, Pin["class"].pinned) : main_core.Dom.removeClass(node, Pin["class"].pinned);
	      });
	    }
	  }, {
	    key: "resetRows",
	    value: function resetRows() {
	      this.grid.getRows().reset();
	    }
	  }, {
	    key: "getRows",
	    value: function getRows() {
	      return this.grid.getRows().getBodyChild();
	    }
	  }, {
	    key: "getLastPinnedRowId",
	    value: function getLastPinnedRowId() {
	      var _this2 = this;

	      var pinnedRows = Object.values(this.getRows()).filter(function (row) {
	        return _this2.getIsPinned(row.getId());
	      });
	      var keys = Object.keys(pinnedRows);

	      if (keys.length > 0) {
	        return pinnedRows[keys[keys.length - 1]].getId();
	      }

	      return 0;
	    }
	  }, {
	    key: "getIsPinned",
	    value: function getIsPinned(rowId) {
	      return this.isRowExist(rowId) && main_core.Type.isDomNode(this.getRowNodeById(rowId).querySelector('.main-grid-cell-content-action-pin.main-grid-cell-content-action-active'));
	    }
	  }, {
	    key: "getRowNodeById",
	    value: function getRowNodeById(id) {
	      return this.getRowById(id).getNode();
	    }
	  }, {
	    key: "getRowById",
	    value: function getRowById(id) {
	      return this.grid.getRows().getById(id);
	    }
	  }, {
	    key: "isRowExist",
	    value: function isRowExist(id) {
	      return this.getRowById(id) !== null;
	    }
	  }]);
	  return Pin;
	}();
	babelHelpers.defineProperty(Pin, "class", {
	  pinned: 'sonet-ui-grid-row-pinned'
	});

	var Grid = /*#__PURE__*/function () {
	  babelHelpers.createClass(Grid, null, [{
	    key: "class",
	    get: function get() {
	      return {
	        highlighted: 'sonet-ui-grid-row-highlighted'
	      };
	    }
	  }]);

	  function Grid(options) {
	    babelHelpers.classCallCheck(this, Grid);
	    this.grid = BX.Main.gridManager.getInstanceById(options.id);
	    this.sort = options.sort;
	    this.pageSize = parseInt(options.pageSize);
	    this.stub = options.gridStub;
	    this.items = new Map();
	    this.fillItems(options.items);
	    this.pinController = new Pin({
	      gridInstance: this.getGrid()
	    });
	    this.init();
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Grid, [{
	    key: "init",
	    value: function init() {}
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Main.grid:sort', this.onColumnSort.bind(this));
	    }
	  }, {
	    key: "onColumnSort",
	    value: function onColumnSort(event) {
	      var data = event.getData();
	      var grid = data[1];
	      var column = data[0];

	      if (grid === this.getGrid()) {
	        this.sort = {};
	        this.sort[column.sort_by] = column.sort_order;
	      }
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      return this.grid;
	    }
	  }, {
	    key: "getPinController",
	    value: function getPinController() {
	      return this.pinController;
	    }
	  }, {
	    key: "getSort",
	    value: function getSort() {
	      return this.sort;
	    }
	  }, {
	    key: "addRow",
	    value: function addRow(id, data, params) {
	      var options = {
	        id: id,
	        columns: data.columns,
	        actions: data.actions,
	        cellActions: data.cellActions //			counters: data.counters,

	      };
	      var moveParams = params.moveParams || {};

	      if (moveParams.rowBefore) {
	        options.insertAfter = moveParams.rowBefore;
	      } else if (moveParams.rowAfter) {
	        options.insertBefore = moveParams.rowAfter;
	      } else {
	        options.append = true;
	      }

	      if (this.items.size > this.getCurrentPage() * this.pageSize) {
	        var lastRowId = this.getLastRowId();
	        this.removeItem(lastRowId);
	        main_core.Dom.remove(this.getRowNodeById(lastRowId));
	        this.showMoreButton();
	      }

	      this.hideStub();
	      this.getRealtime().addRow(options);
	      this.getPinController().colorPinnedRows();
	      main_core_events.EventEmitter.emit('SocialNetwork.Projects.Grid:RowAdd', {
	        id: id
	      });
	    }
	  }, {
	    key: "updateRow",
	    value: function updateRow(id, data, params) {
	      var _this = this;

	      var row = this.getRowById(id);

	      if (main_core.Type.isPlainObject(data)) {
	        if (!main_core.Type.isUndefined(data.columns)) {
	          row.setCellsContent(data.columns);
	        }

	        if (!main_core.Type.isUndefined(data.actions)) {
	          row.setActions(data.actions);
	        }

	        if (!main_core.Type.isUndefined(data.cellActions)) {
	          row.setCellActions(data.cellActions);
	        }

	        if (!main_core.Type.isUndefined(data.counters)) {
	          row.setCounters(data.counters);
	        }
	      }

	      this.resetRows();
	      this.moveRow(id, params.moveParams || {});
	      this.highlightRow(id, params.highlightParams || {}).then(function () {
	        return _this.getPinController().colorPinnedRows();
	      }, function () {});
	      this.getGrid().bindOnRowEvents();
	    }
	  }, {
	    key: "resetRows",
	    value: function resetRows() {
	      this.getRows().reset();
	    }
	  }, {
	    key: "removeRow",
	    value: function removeRow(rowId) {
	      if (!this.isRowExist(rowId)) {
	        return;
	      }

	      this.removeItem(rowId);
	      this.grid.removeRow(rowId);
	    }
	  }, {
	    key: "moveRow",
	    value: function moveRow(rowId, params) {
	      if (params.skip) {
	        return;
	      }

	      var rowBefore = params.rowBefore || 0;
	      var rowAfter = params.rowAfter || 0;

	      if (rowBefore) {
	        this.getRows().insertAfter(rowId, rowBefore);
	      } else if (rowAfter) {
	        this.getRows().insertBefore(rowId, rowAfter);
	      }
	    }
	  }, {
	    key: "highlightRow",
	    value: function highlightRow(rowId, params) {
	      var _this2 = this;

	      params = params || {};
	      return new Promise(function (resolve, reject) {
	        if (!_this2.isRowExist(rowId)) {
	          reject();
	          return;
	        }

	        if (params.skip) {
	          resolve();
	          return;
	        }

	        var node = _this2.getRowNodeById(rowId);

	        var isPinned = main_core.Dom.hasClass(node, Pin["class"].pinned);

	        if (isPinned) {
	          main_core.Dom.removeClass(node, Pin["class"].pinned);
	        }

	        main_core.Dom.addClass(node, Grid["class"].highlighted);
	        setTimeout(function () {
	          main_core.Dom.removeClass(node, Grid["class"].highlighted);

	          if (isPinned) {
	            main_core.Dom.addClass(node, Pin["class"].pinned);
	          }

	          resolve();
	        }, 900);
	      });
	    }
	  }, {
	    key: "isRowExist",
	    value: function isRowExist(rowId) {
	      return this.getRowById(rowId) !== null;
	    }
	  }, {
	    key: "getRows",
	    value: function getRows() {
	      return this.getGrid().getRows();
	    }
	  }, {
	    key: "getRowById",
	    value: function getRowById(rowId) {
	      return this.getRows().getById(rowId);
	    }
	  }, {
	    key: "getRowNodeById",
	    value: function getRowNodeById(id) {
	      return this.getRowById(id).getNode();
	    }
	  }, {
	    key: "getFirstRowId",
	    value: function getFirstRowId() {
	      var firstRow = this.getRows().getBodyFirstChild();
	      return firstRow ? this.getRowProperty(firstRow, 'id') : 0;
	    }
	  }, {
	    key: "getLastRowId",
	    value: function getLastRowId() {
	      var lastRow = this.getRows().getBodyLastChild();
	      return lastRow ? this.getRowProperty(lastRow, 'id') : 0;
	    }
	  }, {
	    key: "getRowProperty",
	    value: function getRowProperty(row, propertyName) {
	      return BX.data(row.getNode(), propertyName);
	    }
	  }, {
	    key: "getCurrentPage",
	    value: function getCurrentPage() {
	      return this.getGrid().getCurrentPage();
	    }
	  }, {
	    key: "fillItems",
	    value: function fillItems(items) {
	      var _this3 = this;

	      Object.keys(items).forEach(function (id) {
	        return _this3.addItem(id);
	      });
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return Array.from(this.items.keys());
	    }
	  }, {
	    key: "hasItem",
	    value: function hasItem(id) {
	      return this.items.has(parseInt(id));
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(id) {
	      this.items.set(parseInt(id));
	    }
	  }, {
	    key: "removeItem",
	    value: function removeItem(id) {
	      this.items["delete"](parseInt(id));
	    }
	  }, {
	    key: "clearItems",
	    value: function clearItems() {
	      this.items.clear();
	    }
	  }, {
	    key: "getRealtime",
	    value: function getRealtime() {
	      return this.getGrid().getRealtime();
	    }
	  }, {
	    key: "showStub",
	    value: function showStub() {
	      if (this.stub) {
	        this.getRealtime().showStub({
	          content: this.stub
	        });
	      }
	    }
	  }, {
	    key: "hideStub",
	    value: function hideStub() {
	      this.getGrid().hideEmptyStub();
	    }
	  }, {
	    key: "showMoreButton",
	    value: function showMoreButton() {
	      this.getGrid().getMoreButton().getNode().style.display = 'inline-block';
	    }
	  }, {
	    key: "hideMoreButton",
	    value: function hideMoreButton() {
	      this.getGrid().getMoreButton().getNode().style.display = 'none';
	    }
	  }]);
	  return Grid;
	}();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10;
	var MembersPopup = /*#__PURE__*/function () {
	  function MembersPopup(options) {
	    babelHelpers.classCallCheck(this, MembersPopup);
	    this.componentName = options.componentName;
	    this.signedParameters = options.signedParameters;
	  }

	  babelHelpers.createClass(MembersPopup, [{
	    key: "showPopup",
	    value: function showPopup(groupId, groupType, bindNode) {
	      var _this = this;

	      var type = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'all';

	      if (this.isPopupShown) {
	        this.popup.destroy();
	      }

	      this.groupId = groupId;
	      this.resetPopupData(groupType);
	      this.changeType(type, false);
	      this.popup = main_popup.PopupWindowManager.create({
	        id: 'workgroup-grid-members-popup-menu',
	        className: 'sonet-ui-members-popup',
	        bindElement: bindNode,
	        autoHide: true,
	        closeByEsc: true,
	        lightShadow: true,
	        bindOptions: {
	          position: 'bottom'
	        },
	        animationOptions: {
	          show: {
	            type: 'opacity-transform'
	          },
	          close: {
	            type: 'opacity'
	          }
	        },
	        events: {
	          onPopupDestroy: function onPopupDestroy() {
	            _this.loader = null;
	            _this.isPopupShown = false;
	          },
	          onPopupClose: function onPopupClose() {
	            _this.popup.destroy();
	          },
	          onAfterPopupShow: function onAfterPopupShow(popup) {
	            popup.contentContainer.appendChild(_this.renderContainer());

	            _this.showLoader();

	            _this.showUsers(groupId, type);

	            _this.isPopupShown = true;
	          }
	        }
	      });
	      this.popupScroll(groupId, type);
	      this.popup.show();
	    }
	  }, {
	    key: "renderContainer",
	    value: function renderContainer() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"sonet-ui-members-popup-container\">\n\t\t\t\t<span class=\"sonet-ui-members-popup-head\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t\t<span class=\"sonet-ui-members-popup-body\">\n\t\t\t\t\t<div class=\"sonet-ui-members-popup-content\">\n\t\t\t\t\t\t<div class=\"sonet-ui-members-popup-content-box\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</span>\n\t\t\t</span>\n\t\t"])), this.popupData.all.tab, this.popupData.heads.tab, this.popupData.members.tab, this.getCurrentPopupData().innerContainer);
	    }
	  }, {
	    key: "popupScroll",
	    value: function popupScroll(groupId, type) {
	      var _this2 = this;

	      if (!main_core.Type.isDomNode(this.getCurrentPopupData().innerContainer)) {
	        return;
	      }

	      main_core.Event.bind(this.getCurrentPopupData().innerContainer, 'scroll', function (event) {
	        var area = event.target;

	        if (area.scrollTop > (area.scrollHeight - area.offsetHeight) / 1.5) {
	          _this2.showUsers(groupId, type);

	          main_core.Event.unbindAll(_this2.getCurrentPopupData().innerContainer);
	        }
	      });
	    }
	  }, {
	    key: "showUsers",
	    value: function showUsers(groupId, type) {
	      var _this3 = this;

	      main_core.ajax.runAction('socialnetwork.api.workgroup.getGridPopupMembers', {
	        data: {
	          groupId: groupId,
	          type: type,
	          page: this.getCurrentPopupData().currentPage,
	          componentName: this.componentName,
	          signedParameters: this.signedParameters
	        }
	      }).then(function (response) {
	        if (_this3.groupId !== groupId || _this3.currentType !== type) {
	          _this3.hideLoader();

	          return;
	        }

	        if (response.data.length > 0) {
	          _this3.renderUsers(response.data);

	          _this3.popupScroll(groupId, _this3.currentType);
	        } else if (!_this3.getCurrentPopupData().innerContainer.hasChildNodes()) {
	          _this3.getCurrentPopupData().innerContainer.innerText = main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_EMPTY');
	        }

	        _this3.getCurrentPopupData().currentPage++;

	        _this3.hideLoader();
	      }, function () {
	        return _this3.hideLoader();
	      });
	    }
	  }, {
	    key: "renderUsers",
	    value: function renderUsers(users) {
	      var _this4 = this;

	      Object.values(users).forEach(function (user) {
	        if (_this4.getCurrentPopupData().renderedUsers.indexOf(user.ID) >= 0) {
	          return;
	        }

	        _this4.getCurrentPopupData().renderedUsers.push(user.ID);

	        _this4.getCurrentPopupData().innerContainer.appendChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<a class=\"sonet-ui-members-popup-item\" href=\"", "\" target=\"_blank\">\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-avatar-new\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-avatar-status-icon\"></span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-name\">", "</span>\n\t\t\t\t\t</a>\n\t\t\t\t"])), user['HREF'], _this4.getAvatar(user), user['FORMATTED_NAME']));
	      });
	    }
	  }, {
	    key: "getAvatar",
	    value: function getAvatar(user) {
	      if (main_core.Type.isStringFilled(user['PHOTO'])) {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-icon ui-icon-common-user sonet-ui-members-popup-avatar-img\">\n\t\t\t\t\t<i style=\"background-image: url('", "')\"></i>\n\t\t\t\t</div>\n\t\t\t"])), encodeURI(user['PHOTO']));
	      }

	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-icon ui-icon-common-user sonet-ui-members-popup-avatar-img\"><i></i></div>\n\t\t"])));
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.popup.getPopupContainer().querySelector('.sonet-ui-members-popup-content'),
	          size: 40
	        });
	      }

	      void this.loader.show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (this.loader) {
	        void this.loader.hide();
	        this.loader = null;
	      }
	    }
	  }, {
	    key: "changeType",
	    value: function changeType(newType) {
	      var loadUsers = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var oldType = this.currentType;
	      this.currentType = newType;
	      Object.values(this.popupData).forEach(function (item) {
	        main_core.Dom.removeClass(item.tab, 'sonet-ui-members-popup-head-item-current');
	      });
	      main_core.Dom.addClass(this.getCurrentPopupData().tab, 'sonet-ui-members-popup-head-item-current');

	      if (oldType) {
	        main_core.Dom.replace(this.popupData[oldType].innerContainer, this.getCurrentPopupData().innerContainer);
	      }

	      if (loadUsers && this.getCurrentPopupData().currentPage === 1) {
	        this.showLoader();
	        this.showUsers(this.groupId, newType);
	      }
	    }
	  }, {
	    key: "resetPopupData",
	    value: function resetPopupData(groupType) {
	      var headTitle = main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS');
	      var membersTitle = main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS');

	      if (groupType === 'project') {
	        headTitle = main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS_PROJECT');
	        membersTitle = main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS_PROJECT');
	      }

	      this.popupData = {
	        all: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-item\" onclick=\"", "\">\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'all'), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_ALL')),
	          innerContainer: main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        },
	        heads: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-item\" onclick=\"", "\">\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'heads'), headTitle),
	          innerContainer: main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        },
	        members: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-item\" onclick=\"", "\">\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'members'), membersTitle),
	          innerContainer: main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        }
	      };
	    }
	  }, {
	    key: "getCurrentPopupData",
	    value: function getCurrentPopupData() {
	      return this.popupData[this.currentType];
	    }
	  }]);
	  return MembersPopup;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6$1, _templateObject7$1, _templateObject8$1, _templateObject9$1, _templateObject10$1, _templateObject11;
	var ScrumMembersPopup = /*#__PURE__*/function (_MembersPopup) {
	  babelHelpers.inherits(ScrumMembersPopup, _MembersPopup);

	  function ScrumMembersPopup() {
	    babelHelpers.classCallCheck(this, ScrumMembersPopup);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ScrumMembersPopup).apply(this, arguments));
	  }

	  babelHelpers.createClass(ScrumMembersPopup, [{
	    key: "renderContainer",
	    value: function renderContainer() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<span class=\"sonet-ui-members-popup-container\">\n\t\t\t\t<span class=\"sonet-ui-members-popup-head\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</span>\n\t\t\t\t<span class=\"sonet-ui-members-popup-body\">\n\t\t\t\t\t<div class=\"sonet-ui-members-popup-content\">\n\t\t\t\t\t\t<div class=\"sonet-ui-members-popup-content-box\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</span>\n\t\t\t</span>\n\t\t"])), this.popupData.all.tab, this.popupData.scrumTeam.tab, this.popupData.members.tab, this.getCurrentPopupData().innerContainer);
	    }
	  }, {
	    key: "resetPopupData",
	    value: function resetPopupData() {
	      this.popupData = {
	        all: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span\n\t\t\t\t\t\tclass=\"sonet-ui-members-popup-head-item\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'all'), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_ALL')),
	          innerContainer: main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        },
	        scrumTeam: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span\n\t\t\t\t\t\tclass=\"sonet-ui-members-popup-head-item\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'scrumTeam'), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS_SCRUM_1')),
	          innerContainer: main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        },
	        members: {
	          currentPage: 1,
	          renderedUsers: [],
	          tab: main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span\n\t\t\t\t\t\tclass=\"sonet-ui-members-popup-head-item\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-head-text\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>\n\t\t\t\t"])), this.changeType.bind(this, 'members'), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS_SCRUM')),
	          innerContainer: main_core.Tag.render(_templateObject7$1 || (_templateObject7$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"sonet-ui-members-popup-inner\"></div>"])))
	        }
	      };
	    }
	  }, {
	    key: "renderUsers",
	    value: function renderUsers(users) {
	      var _this = this;

	      if (this.currentType === 'scrumTeam') {
	        this.renderLabels(users);
	        Object.values(users).forEach(function (user) {
	          if (_this.getCurrentPopupData().renderedUsers.indexOf(user.ID) >= 0 && user.ROLE !== 'M') {
	            return;
	          }

	          _this.getCurrentPopupData().renderedUsers.push(user.ID);

	          var containersMap = new Map();
	          containersMap.set('A', 'sonet-ui-scrum-members-popup-owner-container');
	          containersMap.set('M', 'sonet-ui-scrum-members-popup-master-container');
	          containersMap.set('E', 'sonet-ui-scrum-members-popup-team-container');

	          if (main_core.Type.isUndefined(containersMap.get(user.ROLE))) {
	            return;
	          }

	          _this.getCurrentPopupData().innerContainer.querySelector('.' + containersMap.get(user.ROLE)).appendChild(main_core.Tag.render(_templateObject8$1 || (_templateObject8$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<a\n\t\t\t\t\t\t\t\tclass=\"sonet-ui-members-popup-item\"\n\t\t\t\t\t\t\t\thref=\"", "\"\n\t\t\t\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t<span class=\"sonet-ui-members-popup-avatar-new\">\n\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t\t<span\n\t\t\t\t\t\t\t\t\t\tclass=\"sonet-ui-members-popup-avatar-status-icon\"\n\t\t\t\t\t\t\t\t\t></span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t\t<span\n\t\t\t\t\t\t\t\t\tclass=\"sonet-ui-scrum-members-popup-name\"\n\t\t\t\t\t\t\t\t>", "</span>\n\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t"])), user['HREF'], _this.getAvatar(user), user['FORMATTED_NAME']));
	        });
	      } else {
	        babelHelpers.get(babelHelpers.getPrototypeOf(ScrumMembersPopup.prototype), "renderUsers", this).call(this, users);
	      }
	    }
	  }, {
	    key: "renderLabels",
	    value: function renderLabels(users) {
	      var hasOwner = users.find(function (user) {
	        return user.ROLE === 'A';
	      });
	      var hasMaster = users.find(function (user) {
	        return user.ROLE === 'M';
	      });
	      var hasTeam = users.find(function (user) {
	        return user.ROLE === 'E';
	      });

	      if (hasOwner) {
	        if (main_core.Type.isNull(this.getCurrentPopupData().innerContainer.querySelector('.sonet-ui-scrum-members-popup-owner-container'))) {
	          this.getCurrentPopupData().innerContainer.appendChild(main_core.Tag.render(_templateObject9$1 || (_templateObject9$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"sonet-ui-scrum-members-popup-owner-container\">\n\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label\">\n\t\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label-text\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_OWNER')));
	        }
	      }

	      if (hasMaster) {
	        if (main_core.Type.isNull(this.getCurrentPopupData().innerContainer.querySelector('.sonet-ui-scrum-members-popup-master-container'))) {
	          this.getCurrentPopupData().innerContainer.appendChild(main_core.Tag.render(_templateObject10$1 || (_templateObject10$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"sonet-ui-scrum-members-popup-master-container\">\n\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label\">\n\t\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label-text\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_MASTER')));
	        }
	      }

	      if (hasTeam) {
	        if (main_core.Type.isNull(this.getCurrentPopupData().innerContainer.querySelector('.sonet-ui-scrum-members-popup-team-container'))) {
	          this.getCurrentPopupData().innerContainer.appendChild(main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"sonet-ui-scrum-members-popup-team-container\">\n\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label\">\n\t\t\t\t\t\t\t<span class=\"sonet-ui-scrum-members-popup-label-text\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t"])), main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_TEAM')));
	        }
	      }
	    }
	  }]);
	  return ScrumMembersPopup;
	}(MembersPopup);

	var Actions = /*#__PURE__*/function () {
	  function Actions() {
	    babelHelpers.classCallCheck(this, Actions);
	  }

	  babelHelpers.createClass(Actions, null, [{
	    key: "setOptions",
	    value: function setOptions(options) {
	      Actions.options = options;
	    }
	  }, {
	    key: "setActionsPanel",
	    value: function setActionsPanel(actionsPanel) {
	      Actions.actionsPanel = actionsPanel;
	    }
	  }, {
	    key: "changePin",
	    value: function changePin(groupId, event) {
	      var _event$getData = event.getData(),
	          button = _event$getData.button;

	      var action = main_core.Dom.hasClass(button, Actions["class"].active) ? 'unpin' : 'pin';
	      main_core.ajax.runAction('socialnetwork.api.workgroup.changePin', {
	        data: {
	          groupIdList: [groupId],
	          action: action,
	          componentName: Actions.options.componentName,
	          signedParameters: Actions.options.signedParameters
	        }
	      }).then(function () {
	        if (action === 'unpin') {
	          main_core.Dom.removeClass(button, Actions["class"].active);
	          main_core.Dom.addClass(button, Actions["class"].showByHover);
	        } else {
	          main_core.Dom.addClass(button, Actions["class"].active);
	          main_core.Dom.removeClass(button, Actions["class"].showByHover);
	        }
	      }, function (response) {
	        var errorMessage = main_core.Type.isStringFilled(response.message) ? response.message : main_core.Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_ACTION_ERROR');
	        BX.UI.Notification.Center.notify({
	          content: errorMessage
	        });
	      });
	    }
	  }, {
	    key: "getActionIds",
	    value: function getActionIds(params) {
	      if (!main_core.Type.isUndefined(params.groupId)) {
	        return [params.groupId];
	      }

	      var selected = Actions.getSelectedRows();

	      if (selected.length === 0) {
	        return [];
	      }

	      return selected.map(function (row) {
	        return row.getDataset().id;
	      });
	    }
	  }, {
	    key: "hideActionsPanel",
	    value: function hideActionsPanel() {
	      if (!Actions.actionsPanel) {
	        return;
	      }

	      Actions.actionsPanel.hidePanel();
	    }
	  }, {
	    key: "getSelectedRows",
	    value: function getSelectedRows() {
	      return Actions.getGridInstance().getRows().getSelected();
	    }
	  }, {
	    key: "unselectRows",
	    value: function unselectRows() {
	      Actions.getGridInstance().getRows().unselectAll();
	    }
	  }, {
	    key: "getGridInstance",
	    value: function getGridInstance() {
	      return Actions.options.gridInstance;
	    }
	  }]);
	  return Actions;
	}();
	babelHelpers.defineProperty(Actions, "options", {});
	babelHelpers.defineProperty(Actions, "class", {
	  active: 'main-grid-cell-content-action-active',
	  showByHover: 'main-grid-cell-content-action-by-hover'
	});
	babelHelpers.defineProperty(Actions, "actionsPanel", null);

	var Tag = /*#__PURE__*/function () {
	  function Tag() {
	    babelHelpers.classCallCheck(this, Tag);
	  }

	  babelHelpers.createClass(Tag, null, [{
	    key: "setOptions",
	    value: function setOptions(options) {
	      Tag.options = options;
	    }
	  }, {
	    key: "onTagClick",
	    value: function onTagClick(field) {
	      var filter = Tag.options.filter;
	      filter.toggleByField(field);
	    }
	  }, {
	    key: "onTagAddClick",
	    value: function onTagAddClick(groupId, event) {
	      main_core.Runtime.loadExtension('socialnetwork.entity-selector').then(function (exports) {

	        var onTagsChange = function onTagsChange(event) {
	          var dialog = event.getTarget();
	          var tags = dialog.getSelectedItems().map(function (item) {
	            return item.getId();
	          });
	          void Tag.update(groupId, tags);
	        };

	        var Dialog = exports.Dialog,
	            Footer = exports.Footer;
	        var dialog = new Dialog({
	          targetNode: event.getData().button,
	          enableSearch: true,
	          width: 350,
	          height: 400,
	          multiple: true,
	          dropdownMode: true,
	          compactView: true,
	          context: 'SONET_GROUP_TAG',
	          entities: [{
	            id: 'project-tag',
	            options: {
	              groupId: groupId
	            }
	          }],
	          searchOptions: {
	            allowCreateItem: true,
	            footerOptions: {
	              label: main_core.Loc.getMessage('SOCNET_ENTITY_SELECTOR_TAG_FOOTER_LABEL')
	            }
	          },
	          footer: Footer,
	          footerOptions: {
	            tagCreationLabel: true
	          },
	          events: {
	            onShow: function onShow() {
	              /*
	              						EventEmitter.subscribe('Tasks.Projects.Grid:RowUpdate', onRowUpdate);
	              						EventEmitter.subscribe('Tasks.Projects.Grid:RowRemove', onRowRemove);
	              */
	            },
	            onHide: function onHide() {
	              /*
	              						EventEmitter.unsubscribe('Tasks.Projects.Grid:RowUpdate', onRowUpdate);
	              						EventEmitter.unsubscribe('Tasks.Projects.Grid:RowRemove', onRowRemove);
	              */
	            },
	            'Search:onItemCreateAsync': function SearchOnItemCreateAsync(event) {
	              return new Promise(function (resolve) {
	                var _event$getData2 = event.getData(),
	                    searchQuery = _event$getData2.searchQuery;

	                var name = searchQuery.getQuery().toLowerCase();
	                var dialog = event.getTarget();
	                setTimeout(function () {
	                  var item = dialog.addItem({
	                    id: name,
	                    entityId: 'project-tag',
	                    title: name,
	                    tabs: 'all'
	                  });

	                  if (item) {
	                    item.select();
	                  }

	                  resolve();
	                }, 1000);
	              });
	            },
	            'Item:onSelect': onTagsChange,
	            'Item:onDeselect': onTagsChange
	          }
	        });
	        dialog.show();
	      });
	    }
	  }, {
	    key: "update",
	    value: function update(groupId, tagList) {
	      main_core.ajax.runAction('socialnetwork.api.workgroup.update', {
	        data: {
	          groupId: groupId,
	          fields: {
	            KEYWORDS: tagList.join(',')
	          }
	        }
	      }).then(function (response) {}, function (response) {})["catch"](function (response) {});
	      Actions.hideActionsPanel();
	      Actions.unselectRows();
	    }
	  }]);
	  return Tag;
	}();
	babelHelpers.defineProperty(Tag, "options", {});

	var Filter = /*#__PURE__*/function () {
	  function Filter(options) {
	    babelHelpers.classCallCheck(this, Filter);
	    this.filterInstance = BX.Main.filterManager.getById(options.filterId);

	    if (!this.filterInstance) {
	      return;
	    }

	    this.defaultFilterPresetId = options.defaultFilterPresetId;
	    this.gridId = options.gridId;
	    this.init();
	    this.bindEvents();
	  }

	  babelHelpers.createClass(Filter, [{
	    key: "init",
	    value: function init() {
	      this.fields = this.filterInstance.getFilterFieldsValues();
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	    }
	  }, {
	    key: "onFilterApply",
	    value: function onFilterApply() {
	      this.updateFields();
	    }
	  }, {
	    key: "updateFields",
	    value: function updateFields() {
	      this.fields = this.filterInstance.getFilterFieldsValues();
	    }
	  }, {
	    key: "toggleByField",
	    value: function toggleByField(field) {
	      var _this = this;

	      var name = Object.keys(field)[0];
	      var value = field[name];

	      if (!this.isFilteredByFieldValue(name, value)) {
	        this.filterInstance.getApi().extendFilter(babelHelpers.defineProperty({}, name, value));
	        return;
	      }

	      this.filterInstance.getFilterFields().forEach(function (field) {
	        if (field.getAttribute('data-name') === name) {
	          _this.filterInstance.getFields().deleteField(field);
	        }
	      });
	      this.filterInstance.getSearch().apply();
	    }
	  }, {
	    key: "isFilteredByFieldValue",
	    value: function isFilteredByFieldValue(field, value) {
	      return this.isFilteredByField(field) && this.fields[field] === value;
	    }
	  }, {
	    key: "isFilteredByField",
	    value: function isFilteredByField(field) {
	      if (!Object.keys(this.fields).includes(field)) {
	        return false;
	      }

	      if (main_core.Type.isArray(this.fields[field])) {
	        return this.fields[field].length > 0;
	      }

	      return this.fields[field] !== '';
	    }
	  }]);
	  return Filter;
	}();

	var Controller = /*#__PURE__*/function () {
	  babelHelpers.createClass(Controller, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return Controller.repo.get(id);
	    }
	  }]);

	  function Controller(options) {
	    babelHelpers.classCallCheck(this, Controller);
	    this.gridInstance = new Grid(options);
	    this.membersPopup = new MembersPopup(options);
	    this.scrumMembersPopup = new ScrumMembersPopup(options);
	    Controller.repo.set(options.id, this);
	  }

	  babelHelpers.createClass(Controller, [{
	    key: "getMembersPopup",
	    value: function getMembersPopup() {
	      return this.membersPopup;
	    }
	  }, {
	    key: "getScrumMembersPopup",
	    value: function getScrumMembersPopup() {
	      return this.scrumMembersPopup;
	    }
	  }, {
	    key: "getInstance",
	    value: function getInstance() {
	      return this.gridInstance;
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      return this.getInstance().getGrid();
	    }
	  }]);
	  return Controller;
	}();

	babelHelpers.defineProperty(Controller, "repo", new Map());

	exports.Controller = Controller;
	exports.ActionController = Actions;
	exports.TagController = Tag;
	exports.Filter = Filter;
	exports.PinManager = Pin;

}((this.BX.Socialnetwork.UI.Grid = this.BX.Socialnetwork.UI.Grid || {}),BX.Main,BX,BX,BX.Event));
//# sourceMappingURL=grid.bundle.js.map
