/* eslint-disable */
(function (exports,main_core,main_loader,ui_dialogs_messagebox,ui_userfield,ui_buttons) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }
	var namespace = main_core.Reflection.namespace('BX.Main.UserField');

	/**
	 * @memberOf BX.Main.UserField
	 */
	var Config = /*#__PURE__*/function () {
	  function Config(params) {
	    babelHelpers.classCallCheck(this, Config);
	    babelHelpers.defineProperty(this, "id", 0);
	    babelHelpers.defineProperty(this, "inputs", new Map());
	    babelHelpers.defineProperty(this, "tabs", new Map());
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "settingsContainer", null);
	    babelHelpers.defineProperty(this, "settingsTable", null);
	    babelHelpers.defineProperty(this, "errorsContainer", null);
	    babelHelpers.defineProperty(this, "saveButton", null);
	    babelHelpers.defineProperty(this, "cancelButton", null);
	    babelHelpers.defineProperty(this, "deleteButton", null);
	    this.tabs = new Map();
	    this.inputs = new Map();
	    var saveButtonNode = document.getElementById('ui-button-panel-save');
	    if (saveButtonNode) {
	      this.saveButton = ui_buttons.ButtonManager.createFromNode(saveButtonNode);
	    }
	    var cancelButtonNode = document.getElementById('ui-button-panel-cancel');
	    if (cancelButtonNode) {
	      this.cancelButton = ui_buttons.ButtonManager.createFromNode(cancelButtonNode);
	    }
	    var deleteButtonNode = document.getElementById('ui-button-panel-remove');
	    if (deleteButtonNode) {
	      this.deleteButton = ui_buttons.ButtonManager.createFromNode(deleteButtonNode);
	    }
	    if (main_core.Type.isPlainObject(params)) {
	      this.id = main_core.Text.toInteger(params.id);
	      if (main_core.Type.isDomNode(params.container)) {
	        this.container = params.container;
	      }
	      if (main_core.Type.isDomNode(params.errorsContainer)) {
	        this.errorsContainer = params.errorsContainer;
	      }
	      this.moduleId = params.moduleId;
	    }
	    this.bindEvents();
	    this.fillTabs();
	    _classStaticPrivateFieldSpecGet(this.constructor, Config, _instances).set(this.id, this);
	    this.adjustVisibility();
	    this.syncEnumDefaultSelector();
	  }
	  babelHelpers.createClass(Config, [{
	    key: "getBooleanInputNames",
	    value: function getBooleanInputNames() {
	      return ['multiple', 'mandatory', 'showFilter', 'isSearchable'];
	    }
	  }, {
	    key: "getSettingsContainer",
	    value: function getSettingsContainer() {
	      if (this.container && !this.settingsContainer) {
	        this.settingsContainer = this.container.querySelector('[data-role="main-user-field-settings-container"]');
	      }
	      return this.settingsContainer;
	    }
	  }, {
	    key: "getSettingsTable",
	    value: function getSettingsTable() {
	      if (!this.settingsTable) {
	        var settingsContainer = this.getSettingsContainer();
	        if (settingsContainer) {
	          this.settingsTable = settingsContainer.querySelector('[data-role="main-user-field-settings-table"]');
	        }
	      }
	      return this.settingsTable;
	    }
	  }, {
	    key: "fillTabs",
	    value: function fillTabs() {
	      var _this = this;
	      var tabNames = ['common', 'labels', 'additional', 'list'];
	      if (this.container) {
	        tabNames.forEach(function (name) {
	          var tab = _this.container.querySelector('[data-tab="' + name + '"]');
	          if (tab) {
	            _this.tabs.set(name, tab);
	          }
	        });
	      }
	    }
	  }, {
	    key: "showTab",
	    value: function showTab(name) {
	      var _this2 = this;
	      Array.from(this.tabs.keys()).forEach(function (tabName) {
	        if (tabName === name) {
	          _this2.tabs.get(tabName).classList.add('main-user-field-edit-tab-current');
	          if (name === 'list') {
	            _this2.syncEnumDefaultSelector();
	          }
	        } else {
	          _this2.tabs.get(tabName).classList.remove('main-user-field-edit-tab-current');
	        }
	      });
	    }
	  }, {
	    key: "getInput",
	    value: function getInput(name) {
	      if (this.container && !this.inputs.has(name)) {
	        var input = this.container.querySelector('[data-role="main-user-field-' + name + '"]');
	        if (input) {
	          this.inputs.set(name, input);
	        }
	      }
	      return this.inputs.get(name);
	    }
	  }, {
	    key: "getInputValue",
	    value: function getInputValue(name) {
	      if (name === 'userTypeId') {
	        return this.getSelectedUserTypeId();
	      }
	      var input = this.getInput(name);
	      if (input) {
	        if (this.getBooleanInputNames().includes(name)) {
	          return input.checked ? 'Y' : 'N';
	        }
	        return input.value;
	      }
	      return '';
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this3 = this;
	      var userTypeIdSelector = this.getInput('userTypeId');
	      if (userTypeIdSelector) {
	        main_core.Event.bind(userTypeIdSelector, 'change', this.handleUserTypeChange.bind(this));
	      }
	      var commonLabelInput = this.getInput('editFormLabel');
	      if (commonLabelInput && commonLabelInput.parentElement && commonLabelInput.parentElement.parentElement) {
	        var languageId = commonLabelInput.parentElement.parentElement.dataset['language'];
	        var currentLanguageLabelInput = this.getInput('editFormLabel-' + languageId);
	        if (currentLanguageLabelInput) {
	          main_core.Event.bind(commonLabelInput, 'change', function () {
	            _this3.syncLabelInputs(commonLabelInput, currentLanguageLabelInput);
	          });
	          main_core.Event.bind(currentLanguageLabelInput, 'change', function () {
	            _this3.syncLabelInputs(currentLanguageLabelInput, commonLabelInput);
	          });
	        }
	      }
	      var addEnum = this.container.querySelector('[data-role="main-user-field-enum-add"]');
	      if (addEnum) {
	        main_core.Event.bind(addEnum, 'click', this.addEnumRow.bind(this));
	      }
	      var deleteButtons = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-delete"]'));
	      deleteButtons.forEach(function (target) {
	        main_core.Event.bind(target, 'click', _this3.deleteEnumRow.bind(_this3));
	      });
	      var enumRows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
	      enumRows.forEach(function (row) {
	        var input = row.querySelector('[data-role="main-user-field-enum-value"]');
	        if (input) {
	          main_core.Event.bind(input, 'change', _this3.syncEnumDefaultSelector.bind(_this3));
	        }
	      });
	      main_core.Event.bind(this.saveButton.getContainer(), 'click', function (event) {
	        event.preventDefault();
	        _this3.save();
	      }, {
	        passive: false
	      });
	      if (this.deleteButton) {
	        main_core.Event.bind(this.deleteButton.getContainer(), 'click', function (event) {
	          event.preventDefault();
	          _this3["delete"]();
	        });
	      }
	    }
	  }, {
	    key: "getSelectedUserTypeId",
	    value: function getSelectedUserTypeId() {
	      var option = this.getSelectedOption('userTypeId');
	      if (option) {
	        return option.value;
	      }
	      return null;
	    }
	  }, {
	    key: "getSelectedOption",
	    value: function getSelectedOption(inputName) {
	      var input = this.getInput(inputName);
	      if (input) {
	        var options = Array.from(input.querySelectorAll('option'));
	        var index = input.selectedIndex;
	        return options[index];
	      }
	      return null;
	    }
	  }, {
	    key: "getSelectedOptions",
	    value: function getSelectedOptions(inputName) {
	      var input = this.getInput(inputName);
	      if (input && input instanceof HTMLSelectElement) {
	        return input.selectedOptions;
	      }
	      return null;
	    }
	  }, {
	    key: "handleUserTypeChange",
	    value: function handleUserTypeChange() {
	      var _this4 = this;
	      if (this.isProgress) {
	        return;
	      }
	      var settingsTable = this.getSettingsTable();
	      if (!settingsTable) {
	        return;
	      }
	      var userTypeId = this.getSelectedUserTypeId();
	      if (!userTypeId) {
	        return;
	      }
	      this.startProgress();
	      main_core.ajax.runComponentAction('bitrix:main.field.config.detail', 'getSettings', {
	        data: {
	          userTypeId: userTypeId
	        },
	        analyticsLabel: 'mainUserFieldConfigGetSettings',
	        mode: 'class'
	      }).then(function (response) {
	        _this4.stopProgress();
	        var html = '';
	        if (response.data.html && response.data.html.length > 0) {
	          html = response.data.html;
	        }
	        main_core.Runtime.html(settingsTable, html).then(function () {
	          _this4.adjustVisibility();
	        });
	      })["catch"](function (response) {
	        _this4.stopProgress();
	        _this4.showErrors(response.errors);
	      });
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          size: 150
	        });
	      }
	      return this.loader;
	    }
	  }, {
	    key: "startProgress",
	    value: function startProgress() {
	      this.isProgress = true;
	      if (!this.getLoader().isShown()) {
	        this.getLoader().show(this.container);
	      }
	      this.hideErrors();
	    }
	  }, {
	    key: "stopProgress",
	    value: function stopProgress() {
	      var _this5 = this;
	      this.isProgress = false;
	      this.getLoader().hide();
	      setTimeout(function () {
	        _this5.saveButton.setWaiting(false);
	        main_core.Dom.removeClass(_this5.saveButton.getContainer(), 'ui-btn-wait');
	        if (_this5.deleteButton) {
	          _this5.deleteButton.setWaiting(false);
	          main_core.Dom.removeClass(_this5.deleteButton.getContainer(), 'ui-btn-wait');
	        }
	      }, 200);
	    }
	  }, {
	    key: "showErrors",
	    value: function showErrors(errors) {
	      var text = '';
	      errors.forEach(function (message) {
	        text += message;
	      });
	      if (main_core.Type.isDomNode(this.errorsContainer)) {
	        this.errorsContainer.innerText = text;
	        this.errorsContainer.parentElement.style.display = 'block';
	      } else {
	        console.error(text);
	      }
	    }
	  }, {
	    key: "hideErrors",
	    value: function hideErrors() {
	      if (main_core.Type.isDomNode(this.errorsContainer)) {
	        this.errorsContainer.innerText = '';
	        this.errorsContainer.parentElement.style.display = 'none';
	      }
	    }
	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      var settings = {};
	      var settingsForm = this.container.querySelector('[data-role="main-user-field-settings"]');
	      if (settingsForm) {
	        var formData = new FormData(settingsForm);
	        var _iterator = _createForOfIteratorHelper(formData.entries()),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var pair = _step.value;
	            var name = pair[0].substr(9, pair[0].length - 10);
	            settings[name] = pair[1];
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      }
	      return settings;
	    }
	  }, {
	    key: "prepareFieldData",
	    value: function prepareFieldData() {
	      var _this6 = this;
	      if (!this.container) {
	        return {};
	      }
	      var editFormLabel = {};
	      var labelInputs = Array.from(this.container.querySelectorAll('[data-role="main-user-field-label-container"]'));
	      labelInputs.forEach(function (labelContainer) {
	        var languageId = labelContainer.dataset['language'];
	        editFormLabel[languageId] = _this6.getInputValue('editFormLabel-' + languageId);
	      });
	      var list = [];
	      var userTypeId = this.getInputValue('userTypeId');
	      if (userTypeId === 'enumeration') {
	        this.syncEnumDefaultSelector();
	        var selectedAttributes = this.getSelectedEnumDefaultAttributes();
	        var sortStep = 100;
	        var sort = 0;
	        var rows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
	        rows.forEach(function (row) {
	          var input = row.querySelector('[data-role="main-user-field-enum-value"]');
	          if (!input) {
	            return;
	          }
	          var id = main_core.Text.toInteger(row.dataset['id']);
	          var value = input.value;
	          var def = 'N';
	          if (id > 0 && selectedAttributes.id.includes(id) || selectedAttributes.value.includes(value)) {
	            def = 'Y';
	          }
	          sort += sortStep;
	          list.push({
	            value: input.value,
	            def: def,
	            sort: sort,
	            id: id
	          });
	        });
	      }
	      var id = main_core.Text.toInteger(this.getInputValue('id'));
	      var fieldName = this.getInputValue('fieldName');
	      if (id <= 0) {
	        fieldName = this.getInputValue('fieldPrefix') + fieldName;
	      }
	      return {
	        id: id,
	        editFormLabel: editFormLabel,
	        entityId: this.getInputValue('entityId'),
	        fieldName: fieldName,
	        sort: this.getInputValue('sort'),
	        multiple: this.getInputValue('multiple'),
	        mandatory: this.getInputValue('mandatory'),
	        showFilter: this.getInputValue('showFilter'),
	        isSearchable: this.getInputValue('isSearchable'),
	        userTypeId: userTypeId,
	        settings: this.getSettings(),
	        "enum": list
	      };
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this7 = this;
	      if (this.isProgress) {
	        return;
	      }
	      if (!this.moduleId) {
	        return;
	      }
	      this.startProgress();
	      var fieldData = this.prepareFieldData();
	      var languageId = null;
	      var commonLabelInput = this.getInput('editFormLabel');
	      if (commonLabelInput && commonLabelInput.parentElement && commonLabelInput.parentElement.parentElement) {
	        languageId = commonLabelInput.parentElement.parentElement.dataset['language'];
	      }
	      var userField = new ui_userfield.UserField(fieldData, {
	        languageId: languageId,
	        moduleId: this.moduleId
	      });
	      userField.save().then(function () {
	        _this7.afterSave(userField);
	        _this7.stopProgress();
	      })["catch"](function (errors) {
	        _this7.showErrors(errors);
	        _this7.stopProgress();
	      });
	    }
	  }, {
	    key: "delete",
	    value: function _delete() {
	      var _this8 = this;
	      if (this.isProgress) {
	        return;
	      }
	      if (!this.moduleId) {
	        return;
	      }
	      var id = main_core.Text.toInteger(this.getInputValue('id'));
	      if (id <= 0) {
	        return;
	      }
	      ui_dialogs_messagebox.MessageBox.confirm(main_core.Loc.getMessage('MAIN_FIELD_CONFIG_DELETE_CONFIRM'), function () {
	        return new Promise(function (resolve) {
	          var userField = new ui_userfield.UserField(_this8.prepareFieldData(), {
	            moduleId: _this8.moduleId
	          });
	          _this8.startProgress();
	          userField["delete"]().then(function () {
	            _this8.stopProgress();
	            var slider = _this8.getSlider();
	            if (slider) {
	              _this8.addDataToSlider('userFieldData', userField.serialize());
	              slider.close();
	            } else {
	              ui_dialogs_messagebox.MessageBox.alert(main_core.Loc.getMessage('MAIN_FIELD_CONFIG_DELETE_SUCCESS'));
	            }
	            resolve();
	          })["catch"](function (errors) {
	            _this8.stopProgress();
	            _this8.showErrors(errors);
	            resolve();
	          });
	        });
	      }, null, function (box) {
	        _this8.stopProgress();
	        box.close();
	      });
	    }
	  }, {
	    key: "adjustVisibility",
	    value: function adjustVisibility() {
	      var settingsTable = this.getSettingsTable();
	      var settingsTab = document.querySelector('[data-role="tab-additional"]');
	      var listTab = document.querySelector('[data-role="tab-list"]');
	      if (!settingsTable || !settingsTab || !listTab) {
	        return;
	      }
	      if (settingsTable.childElementCount <= 0) {
	        settingsTab.style.display = 'none';
	      } else {
	        settingsTab.style.display = 'block';
	      }
	      var userTypeId = this.getSelectedUserTypeId();
	      if (userTypeId === 'enumeration') {
	        listTab.style.display = 'flex';
	      } else {
	        listTab.style.display = 'none';
	      }
	      if (userTypeId === 'boolean') {
	        this.changeInputVisibility('multiple', 'none');
	        this.changeInputVisibility('mandatory', 'none');
	      } else {
	        this.changeInputVisibility('multiple', 'block');
	        this.changeInputVisibility('mandatory', 'block');
	      }
	    }
	  }, {
	    key: "changeInputVisibility",
	    value: function changeInputVisibility(inputName, display) {
	      var input = this.getInput(inputName);
	      if (input && input.parentElement && input.parentElement.parentElement) {
	        input.parentElement.parentElement.style.display = display;
	      }
	    }
	  }, {
	    key: "afterSave",
	    value: function afterSave(userField) {
	      this.addDataToSlider('userFieldData', userField.serialize());
	      var slider = this.getSlider();
	      if (slider) {
	        slider.close();
	      } else {
	        var id = main_core.Text.toInteger(this.getInputValue('id'));
	        if (id <= 0) {
	          if (!!userField.getDetailUrl()) {
	            location.href = userField.getDetailUrl();
	            return;
	          }
	          this.getInput('id').value = userField.getId();
	          var prefixInput = this.getInput('fieldPrefix');
	          if (prefixInput && prefixInput.parentElement && prefixInput.parentElement.parentElement) {
	            prefixInput.parentElement.parentElement.classList.remove('main-user-field-name-with-prefix');
	            main_core.Dom.remove(prefixInput.parentElement);
	          }
	          this.getInput('fieldName').value = userField.getName();
	          this.getInput('fieldName').disabled = true;
	          this.getInput('fieldName').parentElement.classList.remove('ui-ctl-inline');
	        }
	      }
	    }
	  }, {
	    key: "getSlider",
	    value: function getSlider() {
	      if (main_core.Reflection.getClass('BX.SidePanel')) {
	        return BX.SidePanel.Instance.getSliderByWindow(window);
	      }
	      return null;
	    }
	  }, {
	    key: "addDataToSlider",
	    value: function addDataToSlider(key, data) {
	      if (main_core.Type.isString(key)) {
	        var slider = this.getSlider();
	        if (slider) {
	          slider.getData().set(key, data);
	          BX.SidePanel.Instance.postMessage(slider, 'userfield-list-update');
	        }
	      }
	    }
	  }, {
	    key: "syncLabelInputs",
	    value: function syncLabelInputs(fromLabel, toLabel) {
	      var tab = fromLabel.closest('.main-user-field-edit-tab');
	      if (tab && tab.classList.contains('main-user-field-edit-tab-current')) {
	        toLabel.value = fromLabel.value;
	      }
	    }
	  }, {
	    key: "addEnumRow",
	    value: function addEnumRow() {
	      var addEnum = this.container.querySelector('[data-role="main-user-field-enum-add"]');
	      if (addEnum) {
	        var input = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"text\" name=\"ENUM[][VALUE]\" value=\"\"\n\t\t\t\t\t\t\t\t data-role=\"main-user-field-enum-value\"\n\t\t\t\t\t\t\t\t onchange=\"", "\">"])), this.syncEnumDefaultSelector.bind(this));
	        var row = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"main-user-field-enum-row\" data-role=\"main-user-field-enum-row\">\n\t\t\t\t\t\t<div class=\"main-user-field-enum-row-inner ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row\">\n\t\t\t\t\t\t\t<span class=\"main-user-field-enum-row-draggable\" style=\"\"></span>\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<div class=\"main-user-field-enum-delete\" onclick=\"", "\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>"])), input, this.deleteEnumRow.bind(this));
	        main_core.Dom.append(row, document.querySelector('.main-user-field-enum-row-list'));
	        input.focus();
	        var item = new DragDropItem();
	        item.init(row);
	      }
	    }
	  }, {
	    key: "deleteEnumRow",
	    value: function deleteEnumRow(_ref) {
	      var target = _ref.target;
	      main_core.Dom.remove(target.parentElement);
	      this.syncEnumDefaultSelector();
	    }
	  }, {
	    key: "getSelectedEnumDefaultAttributes",
	    value: function getSelectedEnumDefaultAttributes() {
	      var result = {
	        id: [],
	        value: []
	      };
	      var selectedDefaultOptions = this.getSelectedOptions('enumDefault');
	      if (selectedDefaultOptions) {
	        Array.from(selectedDefaultOptions).forEach(function (option) {
	          if (option.dataset['id'] && option.dataset['id'] > 0) {
	            result['id'].push(main_core.Text.toInteger(option.dataset['id']));
	          } else {
	            result['value'].push(option.value);
	          }
	        });
	      }
	      return result;
	    }
	  }, {
	    key: "syncEnumDefaultSelector",
	    value: function syncEnumDefaultSelector() {
	      var userTypeId = this.getInputValue('userTypeId');
	      if (userTypeId === 'enumeration') {
	        var selector = this.getInput('enumDefault');
	        if (!selector) {
	          return;
	        }
	        var isMultiple = this.getInputValue('multiple');
	        if (isMultiple === 'Y') {
	          selector.multiple = true;
	          selector.size = 3;
	          selector.parentElement.classList.add('ui-ctl-multiple-select');
	          selector.parentElement.classList.remove('ui-ctl-after-icon');
	          selector.parentElement.classList.remove('ui-ctl-dropdown');
	        } else {
	          selector.multiple = false;
	          selector.parentElement.classList.remove('ui-ctl-multiple-select');
	          selector.parentElement.classList.add('ui-ctl-after-icon');
	          selector.parentElement.classList.add('ui-ctl-dropdown');
	        }
	        var selectedAttributes = this.getSelectedEnumDefaultAttributes();
	        var options = Array.from(selector.querySelectorAll('option'));
	        options.forEach(function (option) {
	          if (option.value !== 'empty') {
	            main_core.Dom.remove(option);
	          }
	        });
	        var rows = Array.from(this.container.querySelectorAll('[data-role="main-user-field-enum-row"]'));
	        rows.forEach(function (row) {
	          var id = main_core.Text.toInteger(row.dataset['id']);
	          var input = row.querySelector('[data-role="main-user-field-enum-value"]');
	          if (!input) {
	            return;
	          }
	          var value = input.value;
	          var selected = id > 0 && selectedAttributes.id.includes(id) || selectedAttributes.value.includes(value);
	          if (value.length > 0) {
	            selector.appendChild(main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<option ", " value=\"", "\" data-id=\"", "\">", "</option>"])), selected ? 'selected="selected"' : '', main_core.Text.encode(value), id, main_core.Text.encode(value)));
	          }
	        });
	      }
	    }
	  }], [{
	    key: "handleLeftMenuClick",
	    value: function handleLeftMenuClick(id, tabName) {
	      if (_classStaticPrivateFieldSpecGet(Config, Config, _instances)) {
	        var instance = _classStaticPrivateFieldSpecGet(Config, Config, _instances).get(id);
	        if (instance) {
	          instance.showTab(tabName);
	        }
	      }
	    }
	  }]);
	  return Config;
	}();
	var _instances = {
	  writable: true,
	  value: new Map()
	};
	var DragDropItem = /*#__PURE__*/function () {
	  function DragDropItem() {
	    babelHelpers.classCallCheck(this, DragDropItem);
	    this.itemContainer = null;
	    this.draggableItemContainer = null;
	    this.dragElement = null;
	  }
	  babelHelpers.createClass(DragDropItem, [{
	    key: "init",
	    value: function init(item) {
	      this.itemContainer = item;
	      var dragButton = this.itemContainer.querySelector('.main-user-field-enum-row-draggable');
	      if (jsDD) {
	        dragButton.onbxdragstart = this.onDragStart.bind(this);
	        dragButton.onbxdrag = this.onDrag.bind(this);
	        dragButton.onbxdragstop = this.onDragStop.bind(this);
	        jsDD.registerObject(dragButton);
	        this.itemContainer.onbxdestdraghover = this.onDragEnter.bind(this);
	        this.itemContainer.onbxdestdraghout = this.onDragLeave.bind(this);
	        this.itemContainer.onbxdestdragfinish = this.onDragDrop.bind(this);
	        jsDD.registerDest(this.itemContainer, 30);
	      }
	    }
	  }, {
	    key: "onDragStart",
	    value: function onDragStart() {
	      main_core.Dom.addClass(this.itemContainer, "main-user-field-enum-row-disabled");
	      if (!this.dragElement) {
	        this.dragElement = this.itemContainer.cloneNode(true);
	        this.dragElement.style.position = "absolute";
	        this.dragElement.style.width = this.itemContainer.offsetWidth + "px";
	        this.dragElement.className = "main-user-field-enum-row-drag";
	        main_core.Dom.append(this.dragElement, document.body);
	      }
	    }
	  }, {
	    key: "onDrag",
	    value: function onDrag(x, y) {
	      if (this.dragElement) {
	        this.dragElement.style.left = x + "px";
	        this.dragElement.style.top = y + "px";
	      }
	    }
	  }, {
	    key: "onDragStop",
	    value: function onDragStop() {
	      main_core.Dom.removeClass(this.itemContainer, "main-user-field-enum-row-disabled");
	      main_core.Dom.remove(this.dragElement);
	      this.dragElement = null;
	    }
	  }, {
	    key: "onDragEnter",
	    value: function onDragEnter(draggableItem) {
	      this.draggableBtnContainer = draggableItem.closest('.main-user-field-enum-row');
	      if (this.draggableBtnContainer !== this.itemContainer) {
	        this.showDragTarget();
	      }
	    }
	  }, {
	    key: "onDragLeave",
	    value: function onDragLeave() {
	      this.hideDragTarget();
	    }
	  }, {
	    key: "onDragDrop",
	    value: function onDragDrop() {
	      if (this.draggableBtnContainer !== this.itemContainer) {
	        this.hideDragTarget();
	        main_core.Dom.remove(this.draggableBtnContainer);
	        main_core.Dom.insertBefore(this.draggableBtnContainer, this.itemContainer);
	      }
	    }
	  }, {
	    key: "showDragTarget",
	    value: function showDragTarget() {
	      main_core.Dom.addClass(this.itemContainer, 'main-user-field-enum-row-target-shown');
	      this.getDragTarget().style.height = this.itemContainer.offsetHeight + "px";
	    }
	  }, {
	    key: "hideDragTarget",
	    value: function hideDragTarget() {
	      main_core.Dom.removeClass(this.itemContainer, "main-user-field-enum-row-target-shown");
	      this.getDragTarget().style.height = 0;
	    }
	  }, {
	    key: "getDragTarget",
	    value: function getDragTarget() {
	      if (!this.dragTarget) {
	        this.dragTarget = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-user-field-enum-row-drag-target\"></div>"])));
	        main_core.Dom.prepend(this.dragTarget, this.itemContainer);
	      }
	      return this.dragTarget;
	    }
	  }]);
	  return DragDropItem;
	}();
	var DragDropBtnContainer = /*#__PURE__*/function () {
	  function DragDropBtnContainer() {
	    babelHelpers.classCallCheck(this, DragDropBtnContainer);
	    this.container = document.querySelector('.main-user-field-enum-row-list');
	    this.height = null;
	  }
	  babelHelpers.createClass(DragDropBtnContainer, [{
	    key: "init",
	    value: function init() {
	      this.container.onbxdestdraghover = BX.delegate(this.onDragEnter, this);
	      this.container.onbxdestdraghout = BX.delegate(this.onDragLeave, this);
	      this.container.onbxdestdragfinish = BX.delegate(this.onDragDrop, this);
	      jsDD.registerDest(this.container, 40);
	    }
	  }, {
	    key: "onDragEnter",
	    value: function onDragEnter(draggableItem) {
	      this.draggableBtnContainer = draggableItem.closest('.main-user-field-enum-row');
	      this.height = this.draggableBtnContainer.offsetHeight;
	      this.showDragTarget();
	    }
	  }, {
	    key: "onDragLeave",
	    value: function onDragLeave() {
	      this.hideDragTarget();
	    }
	  }, {
	    key: "onDragDrop",
	    value: function onDragDrop() {
	      this.hideDragTarget();
	      main_core.Dom.remove(this.draggableBtnContainer);
	      main_core.Dom.insertBefore(this.draggableBtnContainer, this.dragTarget);
	    }
	  }, {
	    key: "showDragTarget",
	    value: function showDragTarget() {
	      main_core.Dom.addClass(this.container, 'main-user-field-enum-row-list-target-shown');
	      this.getDragTarget().style.height = this.height + "px";
	    }
	  }, {
	    key: "hideDragTarget",
	    value: function hideDragTarget() {
	      main_core.Dom.removeClass(this.container, "main-user-field-enum-row-list-target-shown");
	      this.getDragTarget().style.height = 0;
	    }
	  }, {
	    key: "getDragTarget",
	    value: function getDragTarget() {
	      if (!this.dragTarget) {
	        this.dragTarget = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"main-user-field-enum-row-list-target\"></div>"])));
	        main_core.Dom.append(this.dragTarget, this.container);
	      }
	      return this.dragTarget;
	    }
	  }]);
	  return DragDropBtnContainer;
	}();
	namespace.Config = Config;
	namespace.DragDropItem = DragDropItem;
	namespace.DragDropBtnContainer = DragDropBtnContainer;

}((this.window = this.window || {}),BX,BX,BX.UI.Dialogs,BX.UI.UserField,BX.UI));
//# sourceMappingURL=script.js.map
