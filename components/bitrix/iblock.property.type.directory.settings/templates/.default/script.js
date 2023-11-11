/* eslint-disable */
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
	      form.app = ui_vue3.BitrixVue.createApp(form.getAppConfig()).mount(options.settingsFormSelector);
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
	    key: "getDirectoryName",
	    value: function getDirectoryName() {
	      return this.app.directoryName || '';
	    }
	  }, {
	    key: "getDirectoryValue",
	    value: function getDirectoryValue() {
	      return this.app.directoryValue || '';
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
	              var menu = main_popup.MenuManager.getMenuById(menuId);

	              // destroy menu if binded element destroyed
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
	                  change_case: 'L',
	                  replace_space: '',
	                  delete_repeat_replace: true
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

	function _regeneratorRuntime() { /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return exports; }; var exports = {}, Op = Object.prototype, hasOwn = Op.hasOwnProperty, defineProperty = Object.defineProperty || function (obj, key, desc) { obj[key] = desc.value; }, $Symbol = "function" == typeof Symbol ? Symbol : {}, iteratorSymbol = $Symbol.iterator || "@@iterator", asyncIteratorSymbol = $Symbol.asyncIterator || "@@asyncIterator", toStringTagSymbol = $Symbol.toStringTag || "@@toStringTag"; function define(obj, key, value) { return Object.defineProperty(obj, key, { value: value, enumerable: !0, configurable: !0, writable: !0 }), obj[key]; } try { define({}, ""); } catch (err) { define = function define(obj, key, value) { return obj[key] = value; }; } function wrap(innerFn, outerFn, self, tryLocsList) { var protoGenerator = outerFn && outerFn.prototype instanceof Generator ? outerFn : Generator, generator = Object.create(protoGenerator.prototype), context = new Context(tryLocsList || []); return defineProperty(generator, "_invoke", { value: makeInvokeMethod(innerFn, self, context) }), generator; } function tryCatch(fn, obj, arg) { try { return { type: "normal", arg: fn.call(obj, arg) }; } catch (err) { return { type: "throw", arg: err }; } } exports.wrap = wrap; var ContinueSentinel = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var IteratorPrototype = {}; define(IteratorPrototype, iteratorSymbol, function () { return this; }); var getProto = Object.getPrototypeOf, NativeIteratorPrototype = getProto && getProto(getProto(values([]))); NativeIteratorPrototype && NativeIteratorPrototype !== Op && hasOwn.call(NativeIteratorPrototype, iteratorSymbol) && (IteratorPrototype = NativeIteratorPrototype); var Gp = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(IteratorPrototype); function defineIteratorMethods(prototype) { ["next", "throw", "return"].forEach(function (method) { define(prototype, method, function (arg) { return this._invoke(method, arg); }); }); } function AsyncIterator(generator, PromiseImpl) { function invoke(method, arg, resolve, reject) { var record = tryCatch(generator[method], generator, arg); if ("throw" !== record.type) { var result = record.arg, value = result.value; return value && "object" == babelHelpers["typeof"](value) && hasOwn.call(value, "__await") ? PromiseImpl.resolve(value.__await).then(function (value) { invoke("next", value, resolve, reject); }, function (err) { invoke("throw", err, resolve, reject); }) : PromiseImpl.resolve(value).then(function (unwrapped) { result.value = unwrapped, resolve(result); }, function (error) { return invoke("throw", error, resolve, reject); }); } reject(record.arg); } var previousPromise; defineProperty(this, "_invoke", { value: function value(method, arg) { function callInvokeWithMethodAndArg() { return new PromiseImpl(function (resolve, reject) { invoke(method, arg, resolve, reject); }); } return previousPromise = previousPromise ? previousPromise.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(innerFn, self, context) { var state = "suspendedStart"; return function (method, arg) { if ("executing" === state) throw new Error("Generator is already running"); if ("completed" === state) { if ("throw" === method) throw arg; return doneResult(); } for (context.method = method, context.arg = arg;;) { var delegate = context.delegate; if (delegate) { var delegateResult = maybeInvokeDelegate(delegate, context); if (delegateResult) { if (delegateResult === ContinueSentinel) continue; return delegateResult; } } if ("next" === context.method) context.sent = context._sent = context.arg;else if ("throw" === context.method) { if ("suspendedStart" === state) throw state = "completed", context.arg; context.dispatchException(context.arg); } else "return" === context.method && context.abrupt("return", context.arg); state = "executing"; var record = tryCatch(innerFn, self, context); if ("normal" === record.type) { if (state = context.done ? "completed" : "suspendedYield", record.arg === ContinueSentinel) continue; return { value: record.arg, done: context.done }; } "throw" === record.type && (state = "completed", context.method = "throw", context.arg = record.arg); } }; } function maybeInvokeDelegate(delegate, context) { var methodName = context.method, method = delegate.iterator[methodName]; if (undefined === method) return context.delegate = null, "throw" === methodName && delegate.iterator["return"] && (context.method = "return", context.arg = undefined, maybeInvokeDelegate(delegate, context), "throw" === context.method) || "return" !== methodName && (context.method = "throw", context.arg = new TypeError("The iterator does not provide a '" + methodName + "' method")), ContinueSentinel; var record = tryCatch(method, delegate.iterator, context.arg); if ("throw" === record.type) return context.method = "throw", context.arg = record.arg, context.delegate = null, ContinueSentinel; var info = record.arg; return info ? info.done ? (context[delegate.resultName] = info.value, context.next = delegate.nextLoc, "return" !== context.method && (context.method = "next", context.arg = undefined), context.delegate = null, ContinueSentinel) : info : (context.method = "throw", context.arg = new TypeError("iterator result is not an object"), context.delegate = null, ContinueSentinel); } function pushTryEntry(locs) { var entry = { tryLoc: locs[0] }; 1 in locs && (entry.catchLoc = locs[1]), 2 in locs && (entry.finallyLoc = locs[2], entry.afterLoc = locs[3]), this.tryEntries.push(entry); } function resetTryEntry(entry) { var record = entry.completion || {}; record.type = "normal", delete record.arg, entry.completion = record; } function Context(tryLocsList) { this.tryEntries = [{ tryLoc: "root" }], tryLocsList.forEach(pushTryEntry, this), this.reset(!0); } function values(iterable) { if (iterable) { var iteratorMethod = iterable[iteratorSymbol]; if (iteratorMethod) return iteratorMethod.call(iterable); if ("function" == typeof iterable.next) return iterable; if (!isNaN(iterable.length)) { var i = -1, next = function next() { for (; ++i < iterable.length;) if (hasOwn.call(iterable, i)) return next.value = iterable[i], next.done = !1, next; return next.value = undefined, next.done = !0, next; }; return next.next = next; } } return { next: doneResult }; } function doneResult() { return { value: undefined, done: !0 }; } return GeneratorFunction.prototype = GeneratorFunctionPrototype, defineProperty(Gp, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), defineProperty(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, toStringTagSymbol, "GeneratorFunction"), exports.isGeneratorFunction = function (genFun) { var ctor = "function" == typeof genFun && genFun.constructor; return !!ctor && (ctor === GeneratorFunction || "GeneratorFunction" === (ctor.displayName || ctor.name)); }, exports.mark = function (genFun) { return Object.setPrototypeOf ? Object.setPrototypeOf(genFun, GeneratorFunctionPrototype) : (genFun.__proto__ = GeneratorFunctionPrototype, define(genFun, toStringTagSymbol, "GeneratorFunction")), genFun.prototype = Object.create(Gp), genFun; }, exports.awrap = function (arg) { return { __await: arg }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, asyncIteratorSymbol, function () { return this; }), exports.AsyncIterator = AsyncIterator, exports.async = function (innerFn, outerFn, self, tryLocsList, PromiseImpl) { void 0 === PromiseImpl && (PromiseImpl = Promise); var iter = new AsyncIterator(wrap(innerFn, outerFn, self, tryLocsList), PromiseImpl); return exports.isGeneratorFunction(outerFn) ? iter : iter.next().then(function (result) { return result.done ? result.value : iter.next(); }); }, defineIteratorMethods(Gp), define(Gp, toStringTagSymbol, "Generator"), define(Gp, iteratorSymbol, function () { return this; }), define(Gp, "toString", function () { return "[object Generator]"; }), exports.keys = function (val) { var object = Object(val), keys = []; for (var key in object) keys.push(key); return keys.reverse(), function next() { for (; keys.length;) { var key = keys.pop(); if (key in object) return next.value = key, next.done = !1, next; } return next.done = !0, next; }; }, exports.values = values, Context.prototype = { constructor: Context, reset: function reset(skipTempReset) { if (this.prev = 0, this.next = 0, this.sent = this._sent = undefined, this.done = !1, this.delegate = null, this.method = "next", this.arg = undefined, this.tryEntries.forEach(resetTryEntry), !skipTempReset) for (var name in this) "t" === name.charAt(0) && hasOwn.call(this, name) && !isNaN(+name.slice(1)) && (this[name] = undefined); }, stop: function stop() { this.done = !0; var rootRecord = this.tryEntries[0].completion; if ("throw" === rootRecord.type) throw rootRecord.arg; return this.rval; }, dispatchException: function dispatchException(exception) { if (this.done) throw exception; var context = this; function handle(loc, caught) { return record.type = "throw", record.arg = exception, context.next = loc, caught && (context.method = "next", context.arg = undefined), !!caught; } for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i], record = entry.completion; if ("root" === entry.tryLoc) return handle("end"); if (entry.tryLoc <= this.prev) { var hasCatch = hasOwn.call(entry, "catchLoc"), hasFinally = hasOwn.call(entry, "finallyLoc"); if (hasCatch && hasFinally) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } else if (hasCatch) { if (this.prev < entry.catchLoc) return handle(entry.catchLoc, !0); } else { if (!hasFinally) throw new Error("try statement without catch or finally"); if (this.prev < entry.finallyLoc) return handle(entry.finallyLoc); } } } }, abrupt: function abrupt(type, arg) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc <= this.prev && hasOwn.call(entry, "finallyLoc") && this.prev < entry.finallyLoc) { var finallyEntry = entry; break; } } finallyEntry && ("break" === type || "continue" === type) && finallyEntry.tryLoc <= arg && arg <= finallyEntry.finallyLoc && (finallyEntry = null); var record = finallyEntry ? finallyEntry.completion : {}; return record.type = type, record.arg = arg, finallyEntry ? (this.method = "next", this.next = finallyEntry.finallyLoc, ContinueSentinel) : this.complete(record); }, complete: function complete(record, afterLoc) { if ("throw" === record.type) throw record.arg; return "break" === record.type || "continue" === record.type ? this.next = record.arg : "return" === record.type ? (this.rval = this.arg = record.arg, this.method = "return", this.next = "end") : "normal" === record.type && afterLoc && (this.next = afterLoc), ContinueSentinel; }, finish: function finish(finallyLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.finallyLoc === finallyLoc) return this.complete(entry.completion, entry.afterLoc), resetTryEntry(entry), ContinueSentinel; } }, "catch": function _catch(tryLoc) { for (var i = this.tryEntries.length - 1; i >= 0; --i) { var entry = this.tryEntries[i]; if (entry.tryLoc === tryLoc) { var record = entry.completion; if ("throw" === record.type) { var thrown = record.arg; resetTryEntry(entry); } return thrown; } } throw new Error("illegal catch attempt"); }, delegateYield: function delegateYield(iterable, resultName, nextLoc) { return this.delegate = { iterator: values(iterable), resultName: resultName, nextLoc: nextLoc }, "next" === this.method && (this.arg = undefined), ContinueSentinel; } }, exports; }
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
	          var _ref = babelHelpers.asyncToGenerator( /*#__PURE__*/_regeneratorRuntime().mark(function _callee(e) {
	            return _regeneratorRuntime().wrap(function _callee$(_context) {
	              while (1) switch (_context.prev = _context.next) {
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
	      result.append('fields[DIRECTORY_NAME]', this.settingsForm.getDirectoryName());
	      result.append('fields[DIRECTORY_TABLE_NAME]', this.settingsForm.getDirectoryValue());
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
