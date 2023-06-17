(function (exports,main_core,main_core_events,ui_alerts) {
	'use strict';

	var PropertyListValues = /*#__PURE__*/function () {
	  function PropertyListValues(options) {
	    babelHelpers.classCallCheck(this, PropertyListValues);
	    this.grid = BX.Main.gridManager.getInstanceById(options.gridId);
	    this.signedParameters = options.signedParameters;
	    this.initAppendRowButton();
	    this.initSaveButton();
	    this.initGrid();
	    this.initErrorAlert();
	  }

	  babelHelpers.createClass(PropertyListValues, [{
	    key: "getGridBodyRows",
	    value: function getGridBodyRows() {
	      return this.grid.getRows().getBodyChild();
	    }
	  }, {
	    key: "initGrid",
	    value: function initGrid() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe('Grid::updated', function (event) {
	        var grid = event.getCompatData()[0];

	        if (grid && grid.getId() === _this.grid.getId()) {
	          var delayToExitStream = 10;
	          setTimeout(_this.initGridRows.bind(_this), delayToExitStream);
	        }
	      });
	      this.initGridRows();
	    }
	  }, {
	    key: "initGridRows",
	    value: function initGridRows() {
	      var bodyRows = this.getGridBodyRows();

	      if (bodyRows.length === 0) {
	        for (var i = 0; i < 5; i++) {
	          this.appendNewRowToGrid();
	        }
	      } else {
	        bodyRows.forEach(function (row) {
	          row.edit();
	        });
	      }
	    }
	  }, {
	    key: "getGridValues",
	    value: function getGridValues() {
	      var result = {};
	      var newRowsCount = 0;
	      this.getGridBodyRows().forEach(function (row) {
	        var id = parseInt(row.getId());

	        if (isNaN(id) || !id) {
	          newRowsCount++;
	          id = 'n' + newRowsCount;
	        }

	        result[id] = row.getEditorValue();
	      });
	      return result;
	    }
	  }, {
	    key: "reloadGrid",
	    value: function reloadGrid() {
	      this.grid.reload();
	    }
	  }, {
	    key: "initAppendRowButton",
	    value: function initAppendRowButton() {
	      var _this2 = this;

	      var button = document.querySelector('.iblock-property-type-list-values-append-row');

	      if (button) {
	        button.addEventListener('click', function (e) {
	          e.preventDefault();

	          _this2.appendNewRowToGrid();
	        });
	      }
	    }
	  }, {
	    key: "appendNewRowToGrid",
	    value: function appendNewRowToGrid() {
	      var newRow = this.grid.appendRowEditor();
	      newRow.setId('');
	    }
	  }, {
	    key: "initSaveButton",
	    value: function initSaveButton() {
	      var _this3 = this;

	      var button = document.querySelector('#ui-button-panel-save');

	      if (button) {
	        button.addEventListener('click', /*#__PURE__*/function () {
	          var _ref = babelHelpers.asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(e) {
	            return regeneratorRuntime.wrap(function _callee$(_context) {
	              while (1) {
	                switch (_context.prev = _context.next) {
	                  case 0:
	                    e.preventDefault();
	                    _context.next = 3;
	                    return _this3.clearErrors();

	                  case 3:
	                    main_core.ajax.runComponentAction('bitrix:iblock.property.type.list.values', 'save', {
	                      data: {
	                        values: _this3.getGridValues()
	                      },
	                      mode: 'class',
	                      signedParameters: _this3.signedParameters
	                    }).then(function (response) {
	                      button.classList.remove('ui-btn-wait');

	                      _this3.reloadGrid();
	                    })["catch"](function (response) {
	                      button.classList.remove('ui-btn-wait');

	                      _this3.showErrors(response.errors);
	                    });

	                  case 4:
	                  case "end":
	                    return _context.stop();
	                }
	              }
	            }, _callee);
	          }));

	          return function (_x) {
	            return _ref.apply(this, arguments);
	          };
	        }());
	      }
	    }
	  }, {
	    key: "clearErrors",
	    value: function clearErrors() {
	      var _this4 = this;

	      return new Promise(function (resolve, reject) {
	        var animateClosingDelay = 300;

	        _this4.errorAlert.hide();

	        setTimeout(resolve, animateClosingDelay);
	      });
	    }
	  }, {
	    key: "showErrors",
	    value: function showErrors(errors) {
	      this.errorAlert.setText(errors.map(function (i) {
	        return i.message;
	      }).join('<br>'));
	      this.errorAlert.renderTo(document.querySelector('#ui-button-panel'));
	    }
	  }, {
	    key: "initErrorAlert",
	    value: function initErrorAlert() {
	      this.errorAlert = new ui_alerts.Alert({
	        color: ui_alerts.AlertColor.DANGER,
	        animated: true,
	        customClass: 'iblock-property-type-list-values-errors-container'
	      });
	    }
	  }]);
	  return PropertyListValues;
	}();

	main_core.Reflection.namespace('BX.Iblock').PropertyListValues = PropertyListValues;

}((this.window = this.window || {}),BX,BX.Event,BX.UI));
//# sourceMappingURL=script.js.map
