(function (exports,main_core,ui_entitySelector) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');
	var _gridId = /*#__PURE__*/new WeakMap();
	var _delegateToSelector = /*#__PURE__*/new WeakMap();
	var _delegateToUserId = /*#__PURE__*/new WeakMap();
	var _initSelectors = /*#__PURE__*/new WeakSet();
	var TaskList = /*#__PURE__*/function () {
	  function TaskList(options) {
	    babelHelpers.classCallCheck(this, TaskList);
	    _classPrivateMethodInitSpec(this, _initSelectors);
	    _classPrivateFieldInitSpec(this, _gridId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _delegateToSelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _delegateToUserId, {
	      writable: true,
	      value: 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _gridId, options.gridId);
	    _classPrivateMethodGet(this, _initSelectors, _initSelectors2).call(this);
	  }
	  babelHelpers.createClass(TaskList, [{
	    key: "init",
	    value: function init() {
	      var delegateToWrapper = document.getElementById('ACTION_DELEGATE_TO_WRAPPER');
	      if (delegateToWrapper) {
	        babelHelpers.classPrivateFieldGet(this, _delegateToSelector).renderTo(delegateToWrapper);
	      }
	    }
	  }, {
	    key: "applyActionPanelValues",
	    value: function applyActionPanelValues() {
	      var _this = this;
	      var grid = this.getGrid();
	      var actionsPanel = grid === null || grid === void 0 ? void 0 : grid.getActionsPanel();
	      if (grid && actionsPanel) {
	        var _actionsPanel$getForA, _data, _this$getGrid;
	        var data = (_data = {}, babelHelpers.defineProperty(_data, 'action_all_rows_' + babelHelpers.classPrivateFieldGet(this, _gridId), (_actionsPanel$getForA = actionsPanel.getForAllCheckbox()) !== null && _actionsPanel$getForA !== void 0 && _actionsPanel$getForA.checked ? 'Y' : 'N'), babelHelpers.defineProperty(_data, "ACTION_DELEGATE_TO_ID", babelHelpers.classPrivateFieldGet(this, _delegateToUserId)), babelHelpers.defineProperty(_data, "ID", grid.getRows().getSelectedIds()), _data);
	        for (var _i = 0, _Object$entries = Object.entries(actionsPanel.getValues()); _i < _Object$entries.length; _i++) {
	          var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	            key = _Object$entries$_i[0],
	            value = _Object$entries$_i[1];
	          data[key] = main_core.Type.isString(value) ? value.trim().replace(/^['"]+|['"]+$/g, '') : value;
	        }
	        (_this$getGrid = this.getGrid()) === null || _this$getGrid === void 0 ? void 0 : _this$getGrid.reloadTable('POST', data, function () {
	          return _this.init();
	        });
	      }
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      var grid = this.getGrid();
	      if (grid) {
	        grid.reload();
	      }
	    }
	  }, {
	    key: "getGrid",
	    value: function getGrid() {
	      if (babelHelpers.classPrivateFieldGet(this, _gridId)) {
	        return BX.Main.gridManager && BX.Main.gridManager.getInstanceById(babelHelpers.classPrivateFieldGet(this, _gridId));
	      }
	      return null;
	    }
	  }]);
	  return TaskList;
	}();
	function _initSelectors2() {
	  var self = this;
	  babelHelpers.classPrivateFieldSet(this, _delegateToSelector, new ui_entitySelector.TagSelector({
	    multiple: false,
	    tagMaxWidth: 180,
	    events: {
	      onTagAdd: function onTagAdd(event) {
	        babelHelpers.classPrivateFieldSet(self, _delegateToUserId, parseInt(event.getData().tag.getId()));
	        if (!main_core.Type.isInteger(babelHelpers.classPrivateFieldGet(self, _delegateToUserId))) {
	          babelHelpers.classPrivateFieldSet(self, _delegateToUserId, 0);
	        }
	      },
	      onTagRemove: function onTagRemove() {
	        babelHelpers.classPrivateFieldSet(self, _delegateToUserId, 0);
	      }
	    },
	    dialogOptions: {
	      entities: [{
	        id: 'user',
	        options: {
	          intranetUsersOnly: true,
	          inviteEmployeeLink: false
	        }
	      }]
	    }
	  }));
	}
	namespace.TaskList = TaskList;

}((this.window = this.window || {}),BX,BX.UI.EntitySelector));
//# sourceMappingURL=script.js.map
