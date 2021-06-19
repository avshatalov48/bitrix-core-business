this.BX = this.BX || {};
(function (exports,main_core_events,main_core) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"mail-msg-list-grid-empty\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-empty-inner\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-empty-title\">", "</div>\n\t\t\t\t\t\t<p class=\"mail-msg-list-grid-empty-text\">", "</p>\n\t\t\t\t\t\t<p class=\"mail-msg-list-grid-empty-text\">", "</p>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"mail-msg-list-grid-loader mail-msg-list-grid-loader-animate\">\n\t\t\t\t\t<div class=\"mail-msg-list-grid-loader-inner\">\n\t\t\t\t\t<div class=\"mail-msg-list-grid-loader-img\">\n\t\t\t\t\t\t<div class=\"mail-msg-list-grid-loader-img-inner\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-progressbar-track\">\n\t\t\t\t\t\t<div class=\"ui-progressbar-bar mail-message-grid-bar\" data-role=\"mailGridFirstLoadingUIProgressbar\"></div></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var MessageGrid = /*#__PURE__*/function () {
	  function MessageGrid() {
	    var _this = this;

	    babelHelpers.classCallCheck(this, MessageGrid);

	    _compareGrid.add(this);

	    _loadingMessagesStubInGridWrapper.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _gridWrapper.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _id.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _allRowsSelectedStatus.set(this, {
	      writable: true,
	      value: false
	    });

	    _panel.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _checkboxNodeForCheckAll.set(this, {
	      writable: true,
	      value: void 0
	    });

	    if (babelHelpers.typeof(MessageGrid.instance) === 'object') {
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
	        babelHelpers.classPrivateFieldSet(this, _loadingMessagesStubInGridWrapper, this.getGridWrapper().appendChild(main_core.Tag.render(_templateObject())));
	        setTimeout(function () {
	          babelHelpers.classPrivateFieldGet(_this2, _loadingMessagesStubInGridWrapper).querySelector('[data-role="mailGridFirstLoadingUIProgressbar"]').classList.add('mail-message-grid-filling-bar');
	        }, 0);
	        setTimeout(function () {
	          if (babelHelpers.classPrivateFieldGet(_this2, _loadingMessagesStubInGridWrapper) !== undefined) {
	            babelHelpers.classPrivateFieldGet(_this2, _loadingMessagesStubInGridWrapper).remove();
	          }
	        }, 10000);
	      }
	    }
	  }, {
	    key: "replaceTheBlankEmailStub",
	    value: function replaceTheBlankEmailStub() {
	      var blankEmailStubs = document.getElementsByClassName("main-grid-row main-grid-row-empty main-grid-row-body");

	      if (blankEmailStubs.length > 0) {
	        var blankEmailStub = blankEmailStubs[0];

	        if (blankEmailStub.firstElementChild.firstElementChild) {
	          blankEmailStub.firstElementChild.firstElementChild.replaceWith(main_core.Tag.render(_templateObject2(), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TITLE"), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_1"), main_core.Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_2")));
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
	  }]);
	  return MessageGrid;
	}();

	var _loadingMessagesStubInGridWrapper = new WeakMap();

	var _gridWrapper = new WeakMap();

	var _id = new WeakMap();

	var _allRowsSelectedStatus = new WeakMap();

	var _panel = new WeakMap();

	var _checkboxNodeForCheckAll = new WeakMap();

	var _compareGrid = new WeakSet();

	var _compareGrid2 = function _compareGrid2(eventWithGrid, grid) {
	  if (this.getId() !== undefined) {
	    if (grid === undefined && eventWithGrid.getCompatData()) {
	      var _eventWithGrid$getCom = eventWithGrid.getCompatData();

	      var _eventWithGrid$getCom2 = babelHelpers.slicedToArray(_eventWithGrid$getCom, 1);

	      grid = _eventWithGrid$getCom2[0];
	    }

	    if (grid !== undefined && main_core.Type.isFunction(grid.getId) && grid.getId() === this.getId()) return true;
	  }

	  return false;
	};

	exports.MessageGrid = MessageGrid;

}((this.BX.Mail = this.BX.Mail || {}),BX.Event,BX));
//# sourceMappingURL=messagegrid.bundle.js.map
