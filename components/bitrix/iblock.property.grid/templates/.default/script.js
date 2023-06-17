(function (exports,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _gridId = /*#__PURE__*/new WeakMap();

	var _sliderSettings = /*#__PURE__*/new WeakMap();

	var _getGrid = /*#__PURE__*/new WeakSet();

	var PropertyListGrid = /*#__PURE__*/function () {
	  function PropertyListGrid(options) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, PropertyListGrid);

	    _classPrivateMethodInitSpec(this, _getGrid);

	    _classPrivateFieldInitSpec(this, _gridId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _sliderSettings, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _gridId, options.id);
	    babelHelpers.classPrivateFieldSet(this, _sliderSettings, {
	      width: 900,
	      cacheable: false,
	      allowChangeHistory: false,
	      events: {
	        onClose: function onClose() {
	          _classPrivateMethodGet(_this, _getGrid, _getGrid2).call(_this).reload();
	        }
	      }
	    });
	  }

	  babelHelpers.createClass(PropertyListGrid, [{
	    key: "openDetailSlider",
	    value: function openDetailSlider(id) {
	      top.BX.SidePanel.Instance.open("details/".concat(parseInt(id), "/"), babelHelpers.classPrivateFieldGet(this, _sliderSettings));
	    }
	  }, {
	    key: "openCreateSlider",
	    value: function openCreateSlider() {
	      top.BX.SidePanel.Instance.open('details/0/', babelHelpers.classPrivateFieldGet(this, _sliderSettings));
	    }
	  }, {
	    key: "delete",
	    value: function _delete(id) {
	      var _this2 = this;

	      BX.UI.Dialogs.MessageBox.confirm(main_core.Loc.getMessage('IBLOCK_PROPERTY_LIST_TEMPLATE_CONFIRM_DELETE'), function () {
	        // emulate run group action `delete`
	        var grid = _classPrivateMethodGet(_this2, _getGrid, _getGrid2).call(_this2);

	        var data = {
	          'ID': [id]
	        };
	        data[grid.getActionKey()] = 'delete';
	        grid.reloadTable('POST', data);
	        return true;
	      });
	    }
	  }], [{
	    key: "openCreateSliderStatic",
	    value: function openCreateSliderStatic() {
	      PropertyListGrid.Instance.openCreateSlider();
	    }
	  }]);
	  return PropertyListGrid;
	}();

	function _getGrid2() {
	  if (babelHelpers.classPrivateFieldGet(this, _gridId)) {
	    var grid = BX.Main.gridManager.getInstanceById(babelHelpers.classPrivateFieldGet(this, _gridId));

	    if (grid) {
	      return grid;
	    }
	  }

	  throw new Error("Not found grid for property list with id ".concat(babelHelpers.classPrivateFieldGet(this, _gridId)));
	}

	babelHelpers.defineProperty(PropertyListGrid, "Instance", null);
	main_core.Reflection.namespace('BX.Iblock').PropertyListGrid = PropertyListGrid;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
