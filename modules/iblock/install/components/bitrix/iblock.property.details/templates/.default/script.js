/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_buttons,ui_dialogs_messagebox,main_core) {
	'use strict';

	var Buttons = /*#__PURE__*/function () {
	  function Buttons(container, callbacks) {
	    babelHelpers.classCallCheck(this, Buttons);
	    this.callbacks = callbacks;
	    this.container = container;
	    var saveButtonNode = container.querySelector('#ui-button-panel-save');
	    if (saveButtonNode) {
	      saveButtonNode.addEventListener('click', this.handleSaveButtonClick.bind(this));
	      this.saveButton = ui_buttons.ButtonManager.createFromNode(saveButtonNode);
	    }
	    var removeButtonNode = container.querySelector('#ui-button-panel-remove');
	    if (removeButtonNode) {
	      removeButtonNode.addEventListener('click', this.handleRemoveButtonClick.bind(this));
	      this.removeButton = ui_buttons.ButtonManager.createFromNode(removeButtonNode);
	    }
	  }
	  babelHelpers.createClass(Buttons, [{
	    key: "handleSaveButtonClick",
	    value: function handleSaveButtonClick(e) {
	      var _this = this;
	      var clearState = function clearState() {
	        _this.saveButton.setWaiting(false);
	        main_core.Dom.removeClass(_this.saveButton.getContainer(), 'ui-btn-wait');
	      };
	      this.callbacks.onSave().then(clearState)["catch"](clearState);
	    }
	  }, {
	    key: "handleRemoveButtonClick",
	    value: function handleRemoveButtonClick(e) {
	      var _this2 = this;
	      var clearState = function clearState() {
	        _this2.removeButton.setWaiting(false);
	        main_core.Dom.removeClass(_this2.removeButton.getContainer(), 'ui-btn-wait');
	      };
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('IBLOCK_PROPERTY_DETAILS_REMOVE_POPUP_MESSAGE'), function () {
	        _this2.callbacks.onRemove().then(clearState)["catch"](clearState);
	        return true;
	      }, null, function () {
	        clearState();
	        return true;
	      });
	    }
	  }]);
	  return Buttons;
	}();

	var Errors = /*#__PURE__*/function () {
	  function Errors(container) {
	    babelHelpers.classCallCheck(this, Errors);
	    this.errorsWrapper = container.querySelector('#iblock-property-details-errors');
	    this.errorsMessage = this.errorsWrapper.querySelector('.ui-alert-message');
	  }
	  babelHelpers.createClass(Errors, [{
	    key: "show",
	    value: function show(errors) {
	      if (main_core.Type.isArray(errors)) {
	        this.errorsMessage.innerHTML = errors.map(function (i) {
	          return i.message;
	        }).join("\n");
	      } else {
	        this.errorsMessage.innerHTML = 'Unknown error';
	      }
	      this.errorsWrapper.style.display = 'block';
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      this.errorsMessage.innerHTML = '';
	      this.errorsWrapper.style.display = 'none';
	    }
	  }]);
	  return Errors;
	}();

	var Progress = /*#__PURE__*/function () {
	  function Progress(container) {
	    babelHelpers.classCallCheck(this, Progress);
	    babelHelpers.defineProperty(this, "isProgress", false);
	    this.container = container;
	  }
	  babelHelpers.createClass(Progress, [{
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_core.Loader({
	          size: 150
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "start",
	    value: function start() {
	      this.isProgress = true;
	      if (!this.getLoader().isShown()) {
	        this.getLoader().show(this.container);
	      }
	    }
	  }, {
	    key: "stop",
	    value: function stop() {
	      this.isProgress = false;
	      this.getLoader().hide();
	    }
	  }]);
	  return Progress;
	}();

	var storageKey = 'iblockPropertyDetails:deferredSlider';
	var Sliders = /*#__PURE__*/function () {
	  function Sliders() {
	    babelHelpers.classCallCheck(this, Sliders);
	  }
	  babelHelpers.createClass(Sliders, null, [{
	    key: "getDeferredSlider",
	    value: function getDeferredSlider() {
	      var sliderName = top[storageKey];
	      top[storageKey] = null;
	      return sliderName;
	    }
	  }, {
	    key: "setDeferredSlider",
	    value: function setDeferredSlider(sliderName) {
	      top[storageKey] = sliderName;
	    }
	  }]);
	  return Sliders;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _runAction = /*#__PURE__*/new WeakSet();
	var _getSlider = /*#__PURE__*/new WeakSet();
	var PropertyDetails = /*#__PURE__*/function () {
	  function PropertyDetails(options) {
	    babelHelpers.classCallCheck(this, PropertyDetails);
	    _classPrivateMethodInitSpec(this, _getSlider);
	    _classPrivateMethodInitSpec(this, _runAction);
	    this.iblockId = options.iblockId;
	    this.propertyId = options.propertyId;
	    this.slidersOptions = options.sliders;
	    this.signedParameters = options.signedParameters;
	    this.detailPageUrlTemplate = options.detailPageUrlTemplate || '';
	    this.container = document.querySelector(options.containerSelector);
	    this.errors = new Errors(this.container);
	    this.progress = new Progress(this.container);
	    this.buttons = new Buttons(this.container, {
	      onSave: this.handlerSaveButtonClick.bind(this),
	      onRemove: this.handlerRemoveButtonClick.bind(this)
	    });
	    this.initEvents();
	    this.adjustVisibilityLeftMenu();
	    this.stylizationSettingsControls();
	    BX.UI.Hint.init(this.container);
	    var deferredSliderName = Sliders.getDeferredSlider();
	    if (deferredSliderName) {
	      this.openSlider(deferredSliderName);
	    }
	  }
	  babelHelpers.createClass(PropertyDetails, [{
	    key: "initEvents",
	    value: function initEvents() {
	      this.getPropertyTypeInput().addEventListener('change', this.handlePropertyTypeChange.bind(this));
	    }
	  }, {
	    key: "getTabs",
	    value: function getTabs() {
	      return this.container.querySelectorAll('.iblock-property-details-tab');
	    }
	  }, {
	    key: "getAdditionalTab",
	    value: function getAdditionalTab() {
	      return Array.prototype.find.call(this.getTabs(), function (node) {
	        return node.dataset.tab === 'additional';
	      });
	    }
	  }, {
	    key: "getPropertyTypeInput",
	    value: function getPropertyTypeInput() {
	      return this.container.querySelector('[name="PROPERTY_TYPE"]');
	    }
	  }, {
	    key: "openTab",
	    value: function openTab(tabName) {
	      var activeClassName = 'iblock-property-details-tab_current';
	      this.getTabs().forEach(function (tab) {
	        if (tab.dataset.tab === tabName) {
	          tab.classList.add(activeClassName);
	        } else if (tab.classList.contains(activeClassName)) {
	          tab.classList.remove(activeClassName);
	        }
	      });
	    }
	  }, {
	    key: "openSlider",
	    value: function openSlider(sliderName) {
	      var _this = this;
	      var sliderOptions = this.slidersOptions[sliderName];
	      if (!sliderOptions) {
	        throw new Error("Cannot find config for slider '".concat(sliderName, "'"));
	      }
	      if (this.isNewProperty() && sliderOptions.newPropertyConfirmMessage) {
	        ui_dialogs_messagebox.MessageBox.confirm(sliderOptions.newPropertyConfirmMessage, function () {
	          Sliders.setDeferredSlider(sliderName);
	          _this.handlerSaveButtonClick();
	          return true;
	        }, main_core.Loc.getMessage('IBLOCK_PROPERTY_DETAILS_POPUP_OPEN_SLIDER_CONFIRM_SAVE_BUTTON'));
	      } else {
	        top.BX.SidePanel.Instance.open(sliderOptions.url, sliderOptions);
	      }
	    }
	  }, {
	    key: "handlePropertyTypeChange",
	    value: function handlePropertyTypeChange(e) {
	      var _this2 = this;
	      if (this.progress.isProgress) {
	        return;
	      }
	      this.progress.start();
	      this.errors.hide();
	      _classPrivateMethodGet(this, _runAction, _runAction2).call(this, 'getSettings', {
	        propertyFullType: this.getPropertyTypeInput().value
	      }).then(function (response) {
	        var _response$data$info;
	        var showedFields = (_response$data$info = response.data.info) === null || _response$data$info === void 0 ? void 0 : _response$data$info.showedFields;
	        if (main_core.Type.isArray(showedFields)) {
	          _this2.adjustVisibilityCommonFields(showedFields);
	        }
	        var html = '';
	        if (response.data.html && response.data.html.length > 0) {
	          html = response.data.html;
	        }
	        _this2.progress.stop();
	        main_core.Runtime.html(_this2.getAdditionalTab(), html).then(function () {
	          _this2.adjustVisibilityLeftMenu();
	          _this2.stylizationSettingsControls();
	        });
	      })["catch"](function (response) {
	        _this2.progress.stop();
	        _this2.errors.show(response.errors);
	      });
	    }
	  }, {
	    key: "adjustVisibilityCommonFields",
	    value: function adjustVisibilityCommonFields(fields) {
	      var commonTab = this.container.querySelector('[data-tab="common"]');
	      if (!commonTab) {
	        return;
	      }
	      commonTab.querySelectorAll('input, select, textarea').forEach(function (input) {
	        if (!input.name || input.name === 'PROPERTY_TYPE') {
	          return;
	        }
	        var inputContainer = input.closest('.iblock-property-details-input');
	        if (fields.includes(input.name)) {
	          input.disabled = false;
	          if (inputContainer) {
	            inputContainer.style.display = null;
	          }
	        } else {
	          input.disabled = true;
	          if (inputContainer) {
	            inputContainer.style.display = 'none';
	          }
	        }
	      });
	    }
	  }, {
	    key: "adjustVisibilityLeftMenu",
	    value: function adjustVisibilityLeftMenu() {
	      var _this$container$query;
	      var propertyType = (_this$container$query = this.container.querySelector('[name="PROPERTY_TYPE"]')) === null || _this$container$query === void 0 ? void 0 : _this$container$query.value;
	      var listMenuItem = document.querySelector('#iblock-property-details-sidepanel-menu [data-slider="list-values"]');
	      if (propertyType === 'L') {
	        listMenuItem.style.display = 'flex';
	      } else {
	        listMenuItem.style.display = 'none';
	      }
	      var directoryMenuItem = document.querySelector('#iblock-property-details-sidepanel-menu [data-slider="directory-items"]');
	      if (propertyType === 'S:directory') {
	        directoryMenuItem.style.display = 'flex';
	      } else {
	        directoryMenuItem.style.display = 'none';
	      }
	    }
	  }, {
	    key: "stylizationSettingsControls",
	    value: function stylizationSettingsControls() {
	      var buttonInputTypes = new Set(['button', 'submit', 'reset']);
	      var flagInputTypes = new Set(['checkbox', 'radio']);
	      var isOnlyChild = function isOnlyChild(control) {
	        var childs = control.parentNode.childNodes;
	        childs = Array.prototype.filter.call(childs, function (item) {
	          if (item instanceof Text) {
	            return item.nodeValue.trim() !== '';
	          }
	          return true;
	        });
	        return childs.length === 1;
	      };
	      var prepareControl = function prepareControl(control) {
	        // skip `ui.forms` controls
	        if (control.classList.contains('ui-ctl-element')) {
	          return;
	        }
	        switch (control.nodeName) {
	          case 'INPUT':
	            {
	              var type = control.type || 'text';
	              if (buttonInputTypes.has(type)) ; else if (flagInputTypes.has(type)) ; else if (type === 'hidden') ; else {
	                control.classList.add('ui-ctl-element');
	                if (isOnlyChild(control)) {
	                  control.classList.add('ui-ctl-w100');
	                } else {
	                  control.classList.add('ui-ctl-inline');
	                }
	              }
	              break;
	            }
	          case 'SELECT':
	            {
	              control.classList.add('ui-ctl-element');
	              if (!isOnlyChild(control)) {
	                control.classList.add('ui-ctl-inline');
	              }
	              break;
	            }
	          case 'TEXTAREA':
	            {
	              control.classList.add('ui-ctl-element');
	              control.classList.add('ui-ctl-textarea');
	              break;
	            }
	          // No default
	        }
	      };

	      var settingsContainer = this.getAdditionalTab().querySelector('.iblock-property-details-settings-table');
	      if (settingsContainer) {
	        settingsContainer.querySelectorAll('input, select, textarea').forEach(prepareControl);
	      }
	      var defaultValueControl = this.getAdditionalTab().querySelector('[name="DEFAULT_VALUE"]');
	      if (defaultValueControl) {
	        defaultValueControl.closest('.iblock-property-details-input').querySelectorAll('input, select, textarea').forEach(prepareControl);
	      }
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      var result = new FormData();
	      var m;
	      var regex = /^(.+?)(\[.+)$/;
	      var formData = new FormData(this.container.querySelector('form'));
	      var _iterator = _createForOfIteratorHelper(formData.entries()),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var pair = _step.value;
	          var name = pair[0];
	          if (m = regex.exec(name)) {
	            name = "fields[".concat(m[1], "]").concat(m[2]);
	          } else {
	            name = "fields[".concat(name, "]");
	          }
	          result.append(name, pair[1]);
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	      return result;
	    }
	  }, {
	    key: "isNewProperty",
	    value: function isNewProperty() {
	      return parseInt(this.propertyId) === 0;
	    }
	  }, {
	    key: "handlerSaveButtonClick",
	    value: function handlerSaveButtonClick() {
	      var _this3 = this;
	      this.progress.start();
	      this.errors.hide();
	      var data = this.getFields();
	      data.append('propertyId', this.propertyId);
	      data.append('iblockId', this.iblockId);
	      data.append('sessid', BX.bitrix_sessid());
	      return _classPrivateMethodGet(this, _runAction, _runAction2).call(this, 'save', data).then(function (response) {
	        _this3.progress.stop();
	        if (response.errors.length > 0) {
	          _this3.errors.show(response.errors);
	          return false;
	        }
	        _classPrivateMethodGet(_this3, _getSlider, _getSlider2).call(_this3).close();
	        top.BX.Event.EventEmitter.emit('IblockPropertyDetails:saved', [response.data]);
	        return true;
	      })["catch"](function (response) {
	        _this3.progress.stop();
	        _this3.errors.show(response.errors || []);
	        return false;
	      });
	    }
	  }, {
	    key: "handlerRemoveButtonClick",
	    value: function handlerRemoveButtonClick() {
	      var _this4 = this;
	      this.progress.start();
	      this.errors.hide();
	      return _classPrivateMethodGet(this, _runAction, _runAction2).call(this, 'delete', {
	        id: this.propertyId
	      }).then(function (response) {
	        _this4.progress.stop();
	        _classPrivateMethodGet(_this4, _getSlider, _getSlider2).call(_this4).close();
	        return true;
	      })["catch"](function (response) {
	        _this4.progress.stop();
	        _this4.errors.show(response.errors);
	        return false;
	      });
	    }
	  }]);
	  return PropertyDetails;
	}();
	function _runAction2(action, data) {
	  return main_core.ajax.runComponentAction('bitrix:iblock.property.details', action, {
	    mode: 'class',
	    signedParameters: this.signedParameters,
	    data: data
	  });
	}
	function _getSlider2() {
	  return top.BX.SidePanel.Instance.getTopSlider();
	}

	exports.PropertyDetails = PropertyDetails;

}((this.BX.Iblock = this.BX.Iblock || {}),BX.UI,BX.UI.Dialogs,BX));
//# sourceMappingURL=script.js.map
