this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	var UiFilterConverter = /*#__PURE__*/function () {
	  function UiFilterConverter() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      filterId: '',
	      useQuickSearch: false,
	      quickSearchField: {
	        name: '',
	        field: ''
	      }
	    };
	    babelHelpers.classCallCheck(this, UiFilterConverter);
	    this.filterId = options.filterId;
	    this.useQuickSearch = options.useQuickSearch;
	    this.quickSearchField = options.quickSearchField;
	    this.filter = null; // BX.Main.filterManager.getById

	    this.currentPreset = [];
	    this.currentFields = [];
	    this.sourceFilter = [];
	  }

	  babelHelpers.createClass(UiFilterConverter, [{
	    key: "getFilterId",
	    value: function getFilterId() {
	      return this.filterId;
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter() {
	      this.sourceFilter = [];
	      this.initFilter();

	      if (!main_core.Type.isNil(this.filter)) {
	        this.parseFilterRows();
	        this.parseQuickSearchValue();
	      }

	      return this.sourceFilter;
	    }
	  }, {
	    key: "parseFilterRows",
	    value: function parseFilterRows() {
	      var _this = this;

	      var searchFieldData = this.filter.getSearch().prepareSquaresData(this.currentFields);
	      this.sourceFilter = searchFieldData.map(function (item) {
	        var field = _this.currentFields.find(function (currentField) {
	          return currentField.NAME === item.value && !_this.filter.getPreset().isEmptyField(currentField);
	        });

	        var row = {
	          name: item.name,
	          key: field.NAME,
	          value: main_core.Runtime.clone(field.VALUE || field.VALUES)
	        };

	        if (main_core.Type.isString(row.value)) {
	          row.value = {
	            VALUE: row.value
	          };
	        }

	        if (main_core.Type.isPlainObject(field.SUB_TYPE)) {
	          row.value.SUB_TYPE = field.SUB_TYPE.VALUE;
	        }

	        return row;
	      });
	    }
	  }, {
	    key: "parseQuickSearchValue",
	    value: function parseQuickSearchValue() {
	      var _this2 = this;

	      if (this.useQuickSearch) {
	        var quickSearchValue = this.filter.getSearch().getSearchString();

	        if (quickSearchValue !== '') {
	          var row = {
	            VALUE: quickSearchValue,
	            QUICK_SEARCH: 'Y'
	          };
	          var name = "".concat(this.quickSearchField.name, ": ").concat(quickSearchValue);
	          var found = false;

	          if (this.sourceFilter.length > 0) {
	            var index = this.sourceFilter.findIndex(function (_ref) {
	              var key = _ref.key;
	              return key === _this2.quickSearchField.field;
	            });

	            if (index > -1) {
	              found = true;
	              this.sourceFilter[index].name = name;
	              this.sourceFilter[index].value = row;
	            }
	          }

	          if (!found) {
	            this.sourceFilter.push({
	              name: name,
	              key: this.quickSearchField.field,
	              value: row
	            });
	          }
	        }
	      }
	    }
	  }, {
	    key: "initFilter",
	    value: function initFilter() {
	      if (this.filter === null) {
	        // eslint-disable-next-line
	        this.filter = BX.Main.filterManager.getById(this.getFilterId());
	      }

	      if (!main_core.Type.isNil(this.filter)) {
	        this.currentPreset = this.filter.getPreset().getCurrentPresetData();
	        this.currentFields = [].concat(babelHelpers.toConsumableArray(this.currentPreset.FIELDS), babelHelpers.toConsumableArray(this.currentPreset.ADDITIONAL));
	      }
	    }
	  }]);
	  return UiFilterConverter;
	}();

	exports.UiFilterConverter = UiFilterConverter;

}((this.BX.Landing = this.BX.Landing || {}),BX));
//# sourceMappingURL=uifilterconverter.bundle.js.map
