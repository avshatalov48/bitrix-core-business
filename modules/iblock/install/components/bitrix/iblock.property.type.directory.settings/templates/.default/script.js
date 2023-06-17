this.BX = this.BX || {};
(function (exports,ui_alerts,main_core_events,main_core,main_popup,ui_vue3) {
	'use strict';

	var GridController = /*#__PURE__*/function () {
	  function GridController(options) {
	    babelHelpers.classCallCheck(this, GridController);
	    this.grid = BX.Main.gridManager.getInstanceById(options.gridId);
	    this.initGrid();
	  }

	  babelHelpers.createClass(GridController, [{
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
	          this.prependRowEditor();
	        }
	      } else {
	        bodyRows.forEach(function (row) {
	          row.edit();
	        });
	      }
	    }
	  }, {
	    key: "prependRowEditor",
	    value: function prependRowEditor() {
	      var newRow = this.grid.prependRowEditor();
	      newRow.setId('');
	      newRow.unselect();
	    }
	  }, {
	    key: "removeGridSelectedRows",
	    value: function removeGridSelectedRows() {
	      var rows = this.grid.getRows().getSelected(false);

	      if (main_core.Type.isArray(rows)) {
	        rows.forEach(function (row) {
	          row.hide();
	        });
	        this.grid.getRows().reset();
	      }
	    }
	  }]);
	  return GridController;
	}();

	var SettingsForm = /*#__PURE__*/function () {
	  babelHelpers.createClass(SettingsForm, null, [{
	    key: "createApp",
	    value: function createApp(gridController, options) {
	      var form = new SettingsForm(gridController, options);
	      form.app = ui_vue3.BitrixVue.createApp(form.getAppConfig());
	      form.app.mount(options.settingsFormSelector);
	      return form;
	    }
	  }]);

	  function SettingsForm(gridController, options) {
	    babelHelpers.classCallCheck(this, SettingsForm);
	    babelHelpers.defineProperty(this, "newDirectoryValue", '-1');
	    this.gridController = gridController;
	    this.directoryItems = main_core.Type.isArray(options.directoryItems) ? options.directoryItems : [];
	    this.selectedDirectory = this.newDirectoryValue;

	    if (options.selectedDirectory) {
	      var selectedItem = this.directoryItems.find(function (item) {
	        return item.VALUE === options.selectedDirectory;
	      });

	      if (selectedItem) {
	        this.selectedDirectory = selectedItem.VALUE;
	      }
	    }
	  }

	  babelHelpers.createClass(SettingsForm, [{
	    key: "reloadDirectory",
	    value: function reloadDirectory(directoryTableName) {
	      var url = new main_core.Uri(location.href);
	      url.setQueryParam('directoryTableName', directoryTableName);
	      location.href = url.toString();
	    }
	  }, {
	    key: "getAppConfig",
	    value: function getAppConfig() {
	      var form = this;
	      return function () {
	        return {
	          data: function data() {
	            return {
	              directoryName: null,
	              directoryValue: form.selectedDirectory,
	              directoryItems: form.directoryItems
	            };
	          },
	          computed: {
	            selectedDirectoryName: function selectedDirectoryName() {
	              if (this.isNewDirectory) {
	                return main_core.Loc.getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_NEW_DIRECTORY_NAME');
	              }

	              return this.directoryItemsMap[this.directoryValue];
	            },
	            directoryItemsMap: function directoryItemsMap() {
	              var result = {};
	              this.directoryItems.forEach(function (item) {
	                result[item.VALUE] = item.NAME;
	              });
	              return result;
	            },
	            directoryItemsFull: function directoryItemsFull() {
	              var result = [{
	                NAME: main_core.Loc.getMessage('IBLOCK_PROPERTY_TYPE_DIRECTORY_SETTINGS_NEW_DIRECTORY_NAME'),
	                VALUE: form.newDirectoryValue
	              }];
	              result.push.apply(result, babelHelpers.toConsumableArray(this.directoryItems));
	              return result;
	            },
	            directoryItemsAsMenuItems: function directoryItemsAsMenuItems() {
	              var _this = this;

	              return this.directoryItemsFull.map(function (item) {
	                return {
	                  id: item.VALUE,
	                  text: item.NAME,
	                  onclick: _this.onSelectDirectoryItem.bind(_this)
	                };
	              });
	            },
	            isNewDirectory: function isNewDirectory() {
	              return this.directoryValue === form.newDirectoryValue;
	            }
	          },
	          methods: {
	            getDirectoryDropdownMenu: function getDirectoryDropdownMenu(bindElement) {
	              var menuId = 'directory-items';
	              var menu = main_popup.MenuManager.getMenuById(menuId); // destroy menu if binded element destroyed

	              if (menu && bindElement && menu.getPopupWindow().bindElement !== bindElement) {
	                main_popup.MenuManager.destroy(menu.getId());
	                menu = null;
	              }

	              if (!menu && bindElement) {
	                menu = main_popup.MenuManager.create({
	                  id: menuId,
	                  items: this.directoryItemsAsMenuItems,
	                  bindElement: bindElement
	                });
	              }

	              return menu;
	            },
	            toggleDirectoryDropdown: function toggleDirectoryDropdown(e) {
	              this.getDirectoryDropdownMenu(e.target).toggle();
	            },
	            onSelectDirectoryItem: function onSelectDirectoryItem(e, item) {
	              this.directoryValue = item.id;
	              this.getDirectoryDropdownMenu().close();
	              form.reloadDirectory(this.directoryValue);
	            },
	            normalizeName: function normalizeName(e) {
	              var input = e.target;

	              if (input) {
	                input.value = BX.translit(input.value, {
	                  'change_case': 'L',
	                  'replace_space': '',
	                  'delete_repeat_replace': true
	                });
	              }
	            },
	            addNewRow: function addNewRow() {
	              form.gridController.prependRowEditor();
	            }
	          }
	        };
	      }();
	    }
	  }]);
	  return SettingsForm;
	}();

	var PropertyDirectorySettings = /*#__PURE__*/function () {
	  function PropertyDirectorySettings(options) {
	    babelHelpers.classCallCheck(this, PropertyDirectorySettings);
	    this.gridController = new GridController(options);
	    this.signedParameters = options.signedParameters;
	    this.settingsForm = SettingsForm.createApp(this.gridController, options);
	    this.initErrorAlert();
	    this.initSaveButton();
	  }

	  babelHelpers.createClass(PropertyDirectorySettings, [{
	    key: "removeGridSelectedRows",
	    value: function removeGridSelectedRows() {
	      this.gridController.removeGridSelectedRows();
	    }
	  }, {
	    key: "initSaveButton",
	    value: function initSaveButton() {
	      var _this = this;

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
	                    return _this.clearErrors();

	                  case 3:
	                    main_core.ajax.runComponentAction('bitrix:iblock.property.type.directory.settings', 'save', {
	                      data: _this.getFormData(),
	                      mode: 'class',
	                      signedParameters: _this.signedParameters
	                    }).then(function (response) {
	                      button.classList.remove('ui-btn-wait');
	                      location.reload();
	                    })["catch"](function (response) {
	                      button.classList.remove('ui-btn-wait');

	                      _this.showErrors(response.errors);
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
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        var animateClosingDelay = 300;

	        _this2.errorAlert.hide();

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
	        customClass: 'iblock-property-type-directory-settings-errors-container'
	      });
	    }
	  }, {
	    key: "getFormData",
	    value: function getFormData() {
	      var result = new FormData();
	      result.append('fields[DIRECTORY_NAME]', this.settingsForm.app._instance.data.directoryName || '');
	      result.append('fields[DIRECTORY_TABLE_NAME]', this.settingsForm.app._instance.data.directoryValue || '');
	      var newRowsCount = 0;
	      this.gridController.getGridBodyRows().forEach(function (row) {
	        var id = parseInt(row.getId());

	        if (isNaN(id) || !id) {
	          newRowsCount++;
	          id = 'n' + newRowsCount;
	        }

	        var rowValues = row.getEditorValue();

	        if (row.isShown() === false) {
	          rowValues.UF_DELETE = 'Y';
	        }

	        for (var fieldName in rowValues) {
	          if (Object.hasOwnProperty.call(rowValues, fieldName)) {
	            result.append("fields[DIRECTORY_ITEMS][".concat(id, "][").concat(fieldName, "]"), rowValues[fieldName]);
	          }
	        }
	      });
	      return result;
	    }
	  }]);
	  return PropertyDirectorySettings;
	}();

	exports.PropertyDirectorySettings = PropertyDirectorySettings;

}((this.BX.Iblock = this.BX.Iblock || {}),BX.UI,BX.Event,BX,BX.Main,BX.Vue3));
//# sourceMappingURL=script.js.map
