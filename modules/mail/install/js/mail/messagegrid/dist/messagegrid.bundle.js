this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_fonts_opensans,main_core_events,main_core) {
	'use strict';

	var _templateObject, _templateObject2;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _loadingMessagesStubInGridWrapper = /*#__PURE__*/new WeakMap();

	var _gridWrapper = /*#__PURE__*/new WeakMap();

	var _id = /*#__PURE__*/new WeakMap();

	var _allRowsSelectedStatus = /*#__PURE__*/new WeakMap();

	var _panel = /*#__PURE__*/new WeakMap();

	var _checkboxNodeForCheckAll = /*#__PURE__*/new WeakMap();

	var _compareGrid = /*#__PURE__*/new WeakSet();

	var MessageGrid = /*#__PURE__*/function () {
	  function MessageGrid() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, MessageGrid);

	    _classPrivateMethodInitSpec(this, _compareGrid);

	    _classPrivateFieldInitSpec(this, _loadingMessagesStubInGridWrapper, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _gridWrapper, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _allRowsSelectedStatus, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _panel, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _checkboxNodeForCheckAll, {
	      writable: true,
	      value: void 0
	    });

	    if (babelHelpers["typeof"](MessageGrid.instance) === 'object') {
	      return MessageGrid.instance;
	    }

	    MessageGrid.instance = this;
	    main_core_events.EventEmitter.subscribe('Grid::allRowsSelected', function (event) {
	      if (_classPrivateMethodGet(_this, _compareGrid, _compareGrid2).call(_this, event)) babelHelpers.classPrivateFieldSet(_this, _allRowsSelectedStatus, true);
	    });
	    main_core_events.EventEmitter.subscribe('Grid::allRowsUnselected', function (event) {
	      if (_classPrivateMethodGet(_this, _compareGrid, _compareGrid2).call(_this, event)) babelHelpers.classPrivateFieldSet(_this, _allRowsSelectedStatus, false);
	    });
	    main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	      if (_classPrivateMethodGet(_this, _compareGrid, _compareGrid2).call(_this, event) && babelHelpers.classPrivateFieldGet(_this, _allRowsSelectedStatus)) {
	        if (babelHelpers.classPrivateFieldGet(_this, _checkboxNodeForCheckAll) !== undefined) {
	          babelHelpers.classPrivateFieldGet(_this, _checkboxNodeForCheckAll).checked = true;
	        }

	        _this.selectAll();
	      }
	    });
	    main_core_events.EventEmitter.subscribe('Mail::resetGridSelection', function (event) {
	      babelHelpers.classPrivateFieldSet(_this, _allRowsSelectedStatus, false);
	    });
	    main_core_events.EventEmitter.subscribe('Mail::directoryChanged', function () {
	      babelHelpers.classPrivateFieldSet(_this, _allRowsSelectedStatus, false);
	    });
	    main_core_events.EventEmitter.subscribe('Grid::thereSelectedRows', function (event) {
	      if (_classPrivateMethodGet(_this, _compareGrid, _compareGrid2).call(_this, event)) babelHelpers.classPrivateFieldSet(_this, _allRowsSelectedStatus, false);
	    });
	    main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 1),
	          grid = _event$getCompatData2[0];

	      if (grid !== undefined && main_core.Type.isFunction(grid.getId) && grid.getId() === _this.getId()) {
	        _this.replaceTheBlankEmailStub();
	      }
	    });
	    this.replaceTheBlankEmailStub();
	    return MessageGrid.instance;
	  }

	  babelHelpers.createClass(MessageGrid, [{
	    key: "setGridWrapper",
	    value: function setGridWrapper(gridWrapper) {
	      babelHelpers.classPrivateFieldSet(this, _gridWrapper, gridWrapper);
	    }
	  }, {
	    key: "getGridWrapper",
	    value: function getGridWrapper() {
	      return babelHelpers.classPrivateFieldGet(this, _gridWrapper);
	    }
	  }, {
	    key: "enableLoadingMessagesStub",
	    value: function enableLoadingMessagesStub() {
	      var _this2 = this;

	      if (this.getGridWrapper() !== undefined) {
	        babelHelpers.classPrivateFieldSet(this, _loadingMessagesStubInGridWrapper, this.getGridWrapper().appendChild(main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"mail-msg-list-grid-loader mail-msg-list-grid-loader-animate\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-loader-inner\">\n\t\t\t\t\t\t\t<img src=\"/bitrix/images/mail/mail-loader.svg\" alt=\"Load...\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>"])))));
	        setTimeout(function () {
	          if (babelHelpers.classPrivateFieldGet(_this2, _loadingMessagesStubInGridWrapper) !== undefined) {
	            babelHelpers.classPrivateFieldGet(_this2, _loadingMessagesStubInGridWrapper).remove();
	          }
	        }, 15000);
	      }
	    }
	  }, {
	    key: "replaceTheBlankEmailStub",
	    value: function replaceTheBlankEmailStub() {
	      var blankEmailStubs = document.getElementsByClassName("main-grid-row main-grid-row-empty main-grid-row-body");

	      if (blankEmailStubs.length > 0) {
	        var blankEmailStub = blankEmailStubs[0];

	        if (blankEmailStub.firstElementChild.firstElementChild) {
	          blankEmailStub.firstElementChild.firstElementChild.replaceWith(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"mail-msg-list-grid-empty\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-empty-inner\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-empty-title\">", "</div>\n\t\t\t\t\t\t<p class=\"mail-msg-list-grid-empty-text\">", "</p>\n\t\t\t\t\t\t<p class=\"mail-msg-list-grid-empty-text\">", "</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>"])), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TITLE"), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_1"), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_2")));
	        }
	      }
	    }
	  }, {
	    key: "setCheckboxNodeForCheckAll",
	    value: function setCheckboxNodeForCheckAll(node) {
	      babelHelpers.classPrivateFieldSet(this, _checkboxNodeForCheckAll, node);
	    }
	  }, {
	    key: "setPanel",
	    value: function setPanel(panel) {
	      babelHelpers.classPrivateFieldSet(this, _panel, panel);
	    }
	  }, {
	    key: "getPanel",
	    value: function getPanel() {
	      return babelHelpers.classPrivateFieldGet(this, _panel);
	    }
	  }, {
	    key: "hidePanel",
	    value: function hidePanel() {
	      var panel = this.getPanel();

	      if (panel && main_core.Type.isFunction(panel.hidePanel())) {
	        this.getPanel().hidePanel();
	      }
	    }
	  }, {
	    key: "setAllRowsSelectedStatus",
	    value: function setAllRowsSelectedStatus() {
	      babelHelpers.classPrivateFieldSet(this, _allRowsSelectedStatus, true);
	    }
	  }, {
	    key: "unsetAllRowsSelectedStatus",
	    value: function unsetAllRowsSelectedStatus() {
	      babelHelpers.classPrivateFieldSet(this, _allRowsSelectedStatus, false);
	    }
	  }, {
	    key: "reloadTable",
	    value: function reloadTable() {
	      this.getGrid().reloadTable();
	      this.getGrid().tableUnfade();
	    }
	  }, {
	    key: "setGridId",
	    value: function setGridId(gridId) {
	      if (babelHelpers.classPrivateFieldGet(this, _id) === gridId) {
	        return;
	      }

	      babelHelpers.classPrivateFieldSet(this, _id, gridId);
	      this.grid = BX.Main.gridManager.getInstanceById(gridId);
	    }
	  }, {
	    key: "selectAll",
	    value: function selectAll() {
	      this.getGrid().getRows().selectAll();
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getCountDisplayed",
	    value: function getCountDisplayed() {
	      if (this.getGrid()) {
	        return this.getGrid().getRows().getCountDisplayed();
	      }
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      return this.grid;
	    }
	  }, {
	    key: "getRows",
	    value: function getRows() {
	      return this.getGrid().getRows().getBodyChild();
	    }
	  }, {
	    key: "getRowById",
	    value: function getRowById(id) {
	      return this.getGrid().getRows().getById(id);
	    }
	  }, {
	    key: "getRowNodeById",
	    value: function getRowNodeById(id) {
	      return this.getRowById(id).getNode();
	    }
	  }, {
	    key: "getSelectedIds",
	    value: function getSelectedIds() {
	      return this.getGrid().getRows().getSelectedIds();
	    }
	  }, {
	    key: "hideRowByIds",
	    value: function hideRowByIds(ids) {
	      for (var i = 0; i < ids.length; i++) {
	        var rowNode = this.getRowNodeById(ids[i]);
	        main_core.Dom.style(rowNode, 'display', 'none');
	      }
	    }
	  }, {
	    key: "resetGridSelection",
	    value: function resetGridSelection() {
	      main_core_events.EventEmitter.emit(window, 'Mail::resetGridSelection');
	      this.getGrid().getRows().unselectAll();
	      this.getGrid().adjustCheckAllCheckboxes();
	      this.hidePanel();
	    }
	  }, {
	    key: "openGridSettingsWindow",
	    value: function openGridSettingsWindow() {
	      this.getGrid().getSettingsWindow()._onSettingsButtonClick();
	    }
	  }]);
	  return MessageGrid;
	}();

	function _compareGrid2(eventWithGrid, grid) {
	  if (this.getId() !== undefined) {
	    if (grid === undefined && eventWithGrid.getCompatData()) {
	      var _eventWithGrid$getCom = eventWithGrid.getCompatData();

	      var _eventWithGrid$getCom2 = babelHelpers.slicedToArray(_eventWithGrid$getCom, 1);

	      grid = _eventWithGrid$getCom2[0];
	    }

	    if (grid !== undefined && main_core.Type.isFunction(grid.getId) && grid.getId() === this.getId()) return true;
	  }

	  return false;
	}

	exports.MessageGrid = MessageGrid;

}((this.BX.Mail = this.BX.Mail || {}),BX,BX,BX.Event,BX));
//# sourceMappingURL=messagegrid.bundle.js.map
