/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_popup) {
	'use strict';

	var allowedAttributes = {
	  value: "data-value",
	  name: "data-name",
	  disabled: "data-disabled",
	  "class": "class",
	  type: "type"
	};
	var Element = /*#__PURE__*/function () {
	  function Element(element) {
	    var attributes = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    var i18n = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	    babelHelpers.classCallCheck(this, Element);
	    this._node = element instanceof HTMLElement ? element : document.createElement(element);
	    this._config = {
	      i18n: i18n
	    };
	    this._setAttributes(attributes);
	    if (attributes.textContent) {
	      this._setTextContent(attributes.textContent);
	    }
	    return this;
	  }
	  babelHelpers.createClass(Element, [{
	    key: "get",
	    value: function get() {
	      return this._node;
	    }
	  }, {
	    key: "append",
	    value: function append(element) {
	      this._node.appendChild(element);
	      return this;
	    }
	  }, {
	    key: "addClass",
	    value: function addClass(className) {
	      this._node.classList.add(className);
	      return this;
	    }
	  }, {
	    key: "removeClass",
	    value: function removeClass(className) {
	      this._node.classList.remove(className);
	      return this;
	    }
	  }, {
	    key: "toggleClass",
	    value: function toggleClass(className) {
	      this._node.classList.toggle(className);
	      return this;
	    }
	  }, {
	    key: "addEventListener",
	    value: function addEventListener(type, callback) {
	      this._node.addEventListener(type, callback);
	      return this;
	    }
	  }, {
	    key: "removeEventListener",
	    value: function removeEventListener(type, callback) {
	      this._node.removeEventListener(type, callback);
	      return this;
	    }
	  }, {
	    key: "setText",
	    value: function setText(text) {
	      this._setTextContent(text);
	      return this;
	    }
	  }, {
	    key: "getHeight",
	    value: function getHeight() {
	      return window.getComputedStyle(this._node).height;
	    }
	  }, {
	    key: "getWidth",
	    value: function getWidth() {
	      return window.getComputedStyle(this._node).width;
	    }
	  }, {
	    key: "setTop",
	    value: function setTop(top) {
	      this._node.style.top = "".concat(top, "px");
	      return this;
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      this._node.focus();
	      return this;
	    }
	  }, {
	    key: "_setTextContent",
	    value: function _setTextContent(textContent) {
	      this._node.textContent = textContent;
	    }
	  }, {
	    key: "_setAttributes",
	    value: function _setAttributes(attributes) {
	      for (var key in attributes) {
	        if (allowedAttributes[key] && attributes[key]) {
	          this._setAttribute(allowedAttributes[key], attributes[key]);
	        }
	      }
	    }
	  }, {
	    key: "_setAttribute",
	    value: function _setAttribute(key, value) {
	      this._node.setAttribute(key, value);
	    }
	  }, {
	    key: "_getAttribute",
	    value: function _getAttribute(key) {
	      this._node.getAttribute(key);
	    }
	  }]);
	  return Element;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CLASSES = {
	  select: "main-ui-control",
	  dropdownShown: "autocomplete-select--opened",
	  multiselect: "main-ui-multi-select",
	  label: "main-ui-square-container",
	  placeholder: "autocomplete-placeholder",
	  dropdown: "popup-select-content",
	  option: "main-ui-select-inner-item",
	  remove: "main-ui-item-icon main-ui-square-delete",
	  optionDisabled: "autocomplete-option--disabled",
	  autocompleteInput: "main-ui-control main-ui-control-string",
	  selectedLabel: "main-ui-square",
	  selectedOption: "autocomplete-option--selected",
	  placeholderHidden: "autocomplete-placeholder--hidden",
	  optionHidden: "autocomplete-option--hidden"
	};
	var Autocomplete = /*#__PURE__*/function () {
	  function Autocomplete(element, config) {
	    babelHelpers.classCallCheck(this, Autocomplete);
	    this._config = _objectSpread(_objectSpread({}, config), {}, {
	      classNames: _objectSpread(_objectSpread({}, CLASSES), config.classNames),
	      disabledOptions: []
	    });
	    this._state = {
	      opened: false
	    };
	    this._icons = [];
	    this._holderElement = element;
	    this._boundHandleClick = this._handleClick.bind(this);
	    this._boundUnselectOption = this._unselectOption.bind(this);
	    this._boundSortOptions = this._sortOptions.bind(this);
	    this._body = new Element(document.body);
	    this._create(element);
	    if (!this._config.value) {
	      return;
	    }
	    this._setValue();
	  }
	  babelHelpers.createClass(Autocomplete, [{
	    key: "setOptions",
	    value: function setOptions(data) {
	      this._config.options = data;
	      this._options = this._generateOptions();
	    }
	  }, {
	    key: "value",
	    value: function value() {
	      return this._config.value;
	    }
	  }, {
	    key: "removeAutocompleteNode",
	    value: function removeAutocompleteNode() {
	      BX.remove(this._autocomplete.get());
	      this._options.map(function (_option) {
	        BX.remove(_option.get());
	      });
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this._config.value = this._config.multiple ? [] : null;
	      this._setValue();
	    }
	  }, {
	    key: "_create",
	    value: function _create(_element) {
	      var element = typeof _element === "string" ? document.querySelector(_element) : _element;
	      this._parent = new Element(element);
	      var selector = element.querySelectorAll("div[data-name=".concat(element.dataset.name, "]"))[0];
	      var selectClone = selector.cloneNode(true);
	      element.removeChild(selector);
	      this._select = new Element(selectClone);
	      this._label = new Element("span", {
	        "class": this._config.classNames.label
	      });
	      this._optionsWrapper = new Element("div", {
	        "class": this._config.classNames.dropdown
	      });
	      if (this._config.multiple) {
	        this._select.addClass(this._config.classNames.multiselect);
	      }
	      this._options = this._generateOptions();
	      this._select.addEventListener("click", this._boundHandleClick);
	      this._select.append(this._label.get());
	      var deleteButton = this._parent.get().parentNode.querySelectorAll('div.main-ui-control-value-delete');
	      if (deleteButton.length > 0) {
	        BX.bind(deleteButton[0], "click", this.reset.bind(this));
	        this._select.append(deleteButton[0]);
	      }
	      this._parent.append(this._select.get());
	      this._placeholder = new Element("div", {
	        "class": this._config.classNames.placeholder,
	        textContent: this._config.placeholder
	      });
	      this._select.append(this._placeholder.get());
	      this._popup = new main_popup.Popup({
	        id: "autocomplete" + Math.random(),
	        bindElement: _element,
	        zIndex: 3000,
	        width: 515,
	        maxHeight: 300
	      });
	      this._popup.setContent(this._optionsWrapper.get());
	    }
	  }, {
	    key: "_generateOptions",
	    value: function _generateOptions() {
	      var _this = this;
	      if (this._config.autocomplete && !this._autocomplete) {
	        this._autocomplete = new Element("input", {
	          "class": this._config.classNames.autocompleteInput,
	          name: "autocomplete-".concat(this._parent.get().dataset.name),
	          type: "text"
	        });
	        this._autocomplete.addEventListener("input", this._boundSortOptions);
	        this._optionsWrapper.append(this._autocomplete.get());
	      }
	      return this._config.options.map(function (_option) {
	        var preOption = document.querySelectorAll("div.".concat(_this._config.classNames.option, "[data-value=\"").concat(_option.id, "\"]"));
	        if (preOption.length > 0) {
	          return new Element(preOption[0]);
	        }
	        var option = new Element("div", {
	          "class": "".concat(_this._config.classNames.option).concat(_option.disabled ? " " + _this._config.classNames.optionDisabled : ""),
	          value: _option.id,
	          textContent: _option.name,
	          disabled: _option.disabled
	        });
	        if (_option.disabled) {
	          _this._config.disabledOptions.push(String(_option.id));
	        }
	        _this._optionsWrapper.append(option.get());
	        return option;
	      });
	    }
	  }, {
	    key: "_handleClick",
	    value: function _handleClick(event) {
	      event.stopPropagation();
	      if (event.target.className === this._config.classNames.autocompleteInput) {
	        return;
	      }
	      if (this._state.opened) {
	        var option = this._options.find(function (_option) {
	          if (_option) {
	            return _option.get() === event.target;
	          }
	        });
	        if (option !== undefined) {
	          this._setValue(option.get().getAttribute("data-value"), true);
	        }
	        this._popup.close();
	        this._select.removeClass(this._config.classNames.dropdownShown);
	        this._body.removeEventListener("click", this._boundHandleClick);
	        this._select.addEventListener("click", this._boundHandleClick);
	        this._state.opened = false;
	        return;
	      }
	      if (event.target.className === this._config.icon) {
	        return;
	      }
	      this._popup.show();
	      this._select.addClass(this._config.classNames.dropdownShown);
	      this._body.addEventListener("click", this._boundHandleClick);
	      this._select.removeEventListener("click", this._boundHandleClick);
	      this._state.opened = true;
	      if (this._autocomplete) {
	        this._autocomplete.focus();
	      }
	    }
	  }, {
	    key: "_prepareDataValue",
	    value: function _prepareDataValue() {
	      var _this2 = this;
	      var dataValue = [];
	      this._config.options.forEach(function (_option) {
	        _this2._config.value.forEach(function (_value) {
	          if (_option.id.toString() === _value) {
	            dataValue.push({
	              NAME: _option.name,
	              VALUE: _option.id.toString()
	            });
	          }
	        });
	      });
	      this._parent.get().dataset.value = JSON.stringify(dataValue);
	      this._select.get().dataset.value = JSON.stringify(dataValue);
	      return dataValue;
	    }
	  }, {
	    key: "_setValue",
	    value: function _setValue(value, manual, unselected) {
	      var _this3 = this;
	      if (this._config.disabledOptions.indexOf(value) > -1) {
	        return;
	      }
	      if (value && !unselected) {
	        this._config.value = this._config.multiple ? [].concat(babelHelpers.toConsumableArray(this._config.value || []), [value]) : value;
	      }
	      if (value && unselected) {
	        this._config.value = value;
	      }
	      this._options.forEach(function (_option) {
	        _option.removeClass(_this3._config.classNames.selectedOption);
	      });
	      this._placeholder.removeClass(this._config.classNames.placeholderHidden);
	      if (this._config.multiple) {
	        var options = this._config.value.map(function (_value) {
	          var option = _this3._config.options.find(function (_option) {
	            if (_option) {
	              return _option.id.toString() === _value;
	            }
	          });
	          if (!option) {
	            return false;
	          }
	          var optionNode = _this3._options.find(function (_option) {
	            if (_option) {
	              return _option.get().getAttribute("data-value") === option.id.toString();
	            }
	          });
	          optionNode.addClass(_this3._config.classNames.selectedOption);
	          return option;
	        });
	        if (options.length) {
	          this._placeholder.addClass(this._config.classNames.placeholderHidden);
	        }
	        this._selectOptions(options, manual);
	        this._prepareDataValue();
	        return;
	      }
	      var option = this._config.value ? this._config.options.find(function (_option) {
	        if (_option) {
	          _option.id.toString() === _this3._config.value;
	        }
	      }) : this._config.options[0];
	      var optionNode = this._options.find(function (_option) {
	        if (_option) {
	          _option.get().getAttribute("data-value") === option.id.toString();
	        }
	      });
	      this._prepareDataValue();
	      if (!this._config.value) {
	        this._label.setText("");
	        return;
	      }
	      optionNode.addClass(this._config.classNames.selectedOption);
	      this._placeholder.addClass(this._config.classNames.placeholderHidden);
	      this._selectOption(option, manual);
	    }
	  }, {
	    key: "_selectOption",
	    value: function _selectOption(option, manual) {
	      this._selectedOption = option;
	      this._label.setText(option.name);
	      if (this._config.onChange && manual) {
	        this._config.onChange(option.id, this._prepareDataValue());
	      }
	    }
	  }, {
	    key: "_selectOptions",
	    value: function _selectOptions(options, manual) {
	      var _this4 = this;
	      this._label.setText("");
	      this._icons = options.map(function (_option) {
	        if (_option) {
	          var selectedLabel = new Element("span", {
	            "class": _this4._config.classNames.selectedLabel,
	            textContent: _option.name
	          });
	          var remove = new Element("span", {
	            "class": "".concat(_this4._config.classNames.remove),
	            value: _option.id
	          });
	          remove.addEventListener("click", _this4._boundUnselectOption);
	          selectedLabel.append(remove.get());
	          _this4._label.append(selectedLabel.get());
	          return remove.get();
	        }
	      });
	      if (manual) {
	        this._optionsWrapper.setTop(Number(this._select.getHeight().split("px")[0]) + 5);
	      }
	      if (this._config.onChange && manual) {
	        this._config.onChange(this._config.value, this._prepareDataValue());
	      }
	    }
	  }, {
	    key: "_unselectOption",
	    value: function _unselectOption(event) {
	      var newValue = babelHelpers.toConsumableArray(this._config.value);
	      var index = newValue.indexOf(event.target.getAttribute("data-value"));
	      if (index !== -1) {
	        newValue.splice(index, 1);
	      }
	      this._setValue(newValue, true, true);
	    }
	  }, {
	    key: "_sortOptions",
	    value: function _sortOptions(event) {
	      var _this5 = this;
	      this._options.forEach(function (_option) {
	        if (!_option.get().textContent.toLowerCase().startsWith(event.target.value.toLowerCase())) {
	          _option.addClass(_this5._config.classNames.optionHidden);
	          return;
	        }
	        _option.removeClass(_this5._config.classNames.optionHidden);
	      });
	    }
	  }]);
	  return Autocomplete;
	}();

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _page = /*#__PURE__*/new WeakMap();
	var _helper = /*#__PURE__*/new WeakMap();
	var _context = /*#__PURE__*/new WeakMap();
	var _actionUri = /*#__PURE__*/new WeakMap();
	var _isFrame = /*#__PURE__*/new WeakMap();
	var _prettyDateFormat = /*#__PURE__*/new WeakMap();
	var _isSaved = /*#__PURE__*/new WeakMap();
	var _isRegistered = /*#__PURE__*/new WeakMap();
	var _isOutside = /*#__PURE__*/new WeakMap();
	var _mess = /*#__PURE__*/new WeakMap();
	var _letterTile = /*#__PURE__*/new WeakMap();
	var _selectorNode = /*#__PURE__*/new WeakMap();
	var _editorNode = /*#__PURE__*/new WeakMap();
	var _titleNode = /*#__PURE__*/new WeakMap();
	var _loginNode = /*#__PURE__*/new WeakMap();
	var _formNode = /*#__PURE__*/new WeakMap();
	var _oauthCodeNode = /*#__PURE__*/new WeakMap();
	var _filterNode = /*#__PURE__*/new WeakMap();
	var _filterData = /*#__PURE__*/new WeakMap();
	var _filterId = /*#__PURE__*/new WeakMap();
	var _filter = /*#__PURE__*/new WeakMap();
	var _isAvailable = /*#__PURE__*/new WeakMap();
	var _ajaxAction = /*#__PURE__*/new WeakMap();
	var _messageFields = /*#__PURE__*/new WeakMap();
	var _templateChangeButton = /*#__PURE__*/new WeakMap();
	var _buttonsNode = /*#__PURE__*/new WeakMap();
	var _templateNameNode = /*#__PURE__*/new WeakMap();
	var _templateTypeNode = /*#__PURE__*/new WeakMap();
	var _templateIdNode = /*#__PURE__*/new WeakMap();
	var _templateData = /*#__PURE__*/new WeakMap();
	var _REGION_BY_IP = /*#__PURE__*/new WeakMap();
	var _REGION_BY_PHONE = /*#__PURE__*/new WeakMap();
	var _isSlider = /*#__PURE__*/new WeakMap();
	var _toolbarId = /*#__PURE__*/new WeakMap();
	var _uiToolbar = /*#__PURE__*/new WeakMap();
	var Toloka = /*#__PURE__*/function () {
	  function Toloka() {
	    babelHelpers.classCallCheck(this, Toloka);
	    _classPrivateFieldInitSpec(this, _page, {
	      writable: true,
	      value: BX.Sender.Page
	    });
	    _classPrivateFieldInitSpec(this, _helper, {
	      writable: true,
	      value: BX.Sender.Helper
	    });
	    _classPrivateFieldInitSpec(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _actionUri, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isFrame, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _prettyDateFormat, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isSaved, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isRegistered, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isOutside, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _mess, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _letterTile, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _selectorNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _editorNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _titleNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _loginNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _formNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _oauthCodeNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _filterNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _filterData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _filterId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _filter, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _isAvailable, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _ajaxAction, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _messageFields, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(this, _templateChangeButton, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _buttonsNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateNameNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateTypeNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateIdNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _templateData, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _REGION_BY_IP, {
	      writable: true,
	      value: 'REGION_BY_IP'
	    });
	    _classPrivateFieldInitSpec(this, _REGION_BY_PHONE, {
	      writable: true,
	      value: 'REGION_BY_PHONE'
	    });
	    _classPrivateFieldInitSpec(this, _isSlider, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _toolbarId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _uiToolbar, {
	      writable: true,
	      value: void 0
	    });
	  }
	  babelHelpers.createClass(Toloka, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;
	      this._expireInNode.addEventListener('change', this.validateRequiredFields.bind(this));
	      if (BX.Sender.Template && BX.Sender.Template.Selector) {
	        var selector = BX.Sender.Template.Selector;
	        BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
	        BX.addCustomEvent(selector, selector.events.selectorClose, this.closeTemplateSelector.bind(this));
	      }
	      if (this._saveBtn) {
	        BX.bind(this._saveBtn, 'click', this.applyChanges.bind(this));
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _templateChangeButton)) {
	        BX.bind(babelHelpers.classPrivateFieldGet(this, _templateChangeButton), 'click', this.showTemplateSelector.bind(this));
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _isSaved)) {
	        top.BX.onCustomEvent(top, 'sender-letter-edit-change', [this.letterTile]);
	        babelHelpers.classPrivateFieldGet(this, _page).slider.close();
	        if (babelHelpers.classPrivateFieldGet(this, _isOutside)) {
	          BX.UI.Notification.Center.notify({
	            content: babelHelpers.classPrivateFieldGet(this, _mess).outsideSaveSuccess,
	            autoHideDelay: 5000
	          });
	        }
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _uiToolbar) && babelHelpers.classPrivateFieldGet(this, _isSlider)) {
	        babelHelpers.classPrivateFieldGet(this, _uiToolbar).subscribe(BX.UI.ToolbarEvents.finishEditing, function (event) {
	          var updatedTitle = event.getData().updatedTitle;
	          if (updatedTitle && babelHelpers.classPrivateFieldGet(_this, _titleNode)) {
	            babelHelpers.classPrivateFieldGet(_this, _titleNode).value = updatedTitle;
	          }
	        });
	      }
	      this.initWidget();
	      var filter = this.getFilter();
	      filter.getAddPresetButton().style.display = 'none';
	      filter.getPreset().getPresets().forEach(function (preset) {
	        preset.style.display = 'none';
	      });
	      BX.bind(filter.getResetButton(), 'click', this.reInitAddressWidget.bind(this));
	      var clearFilterBtn = document.querySelector('.main-ui-delete');
	      BX.bind(clearFilterBtn, 'click', this.reInitAddressWidget.bind(this));
	    }
	  }, {
	    key: "initialize",
	    value: function initialize(params) {
	      babelHelpers.classPrivateFieldSet(this, _context, BX(params.containerId));
	      babelHelpers.classPrivateFieldSet(this, _filterData, []);
	      babelHelpers.classPrivateFieldGet(this, _filterData)[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)] = {
	        region: []
	      };
	      babelHelpers.classPrivateFieldGet(this, _filterData)[babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)] = {
	        region: []
	      };
	      babelHelpers.classPrivateFieldSet(this, _filterId, 'toloka-filter-connector');
	      babelHelpers.classPrivateFieldSet(this, _filterNode, document.getElementById("".concat(babelHelpers.classPrivateFieldGet(this, _filterId), "_search_container")));
	      babelHelpers.classPrivateFieldSet(this, _filter, this.getFilter());
	      babelHelpers.classPrivateFieldSet(this, _templateChangeButton, BX('SENDER_TOLOKA_BUTTON_CHANGE'));
	      babelHelpers.classPrivateFieldGet(this, _helper).changeDisplay(babelHelpers.classPrivateFieldGet(this, _templateChangeButton), false);
	      babelHelpers.classPrivateFieldSet(this, _actionUri, params.actionUri);
	      babelHelpers.classPrivateFieldSet(this, _ajaxAction, new BX.AjaxAction(babelHelpers.classPrivateFieldGet(this, _actionUri)));
	      babelHelpers.classPrivateFieldSet(this, _isFrame, params.isFrame || false);
	      babelHelpers.classPrivateFieldSet(this, _prettyDateFormat, params.prettyDateFormat);
	      babelHelpers.classPrivateFieldSet(this, _isSaved, params.isSaved || false);
	      babelHelpers.classPrivateFieldSet(this, _isRegistered, params.isRegistered || false);
	      babelHelpers.classPrivateFieldSet(this, _isOutside, params.isOutside || false);
	      babelHelpers.classPrivateFieldSet(this, _isAvailable, params.isAvailable || true);
	      babelHelpers.classPrivateFieldSet(this, _mess, params.mess);
	      babelHelpers.classPrivateFieldSet(this, _letterTile, params.letterTile || {});
	      babelHelpers.classPrivateFieldSet(this, _templateData, []);
	      babelHelpers.classPrivateFieldSet(this, _messageFields, this.objectKeysToLowerCase(JSON.parse(params.preset)));
	      this.optionData = [];
	      babelHelpers.classPrivateFieldSet(this, _isSlider, params.isSlider);
	      babelHelpers.classPrivateFieldSet(this, _toolbarId, params.toolbarId);
	      babelHelpers.classPrivateFieldSet(this, _uiToolbar, BX.UI.ToolbarManager.get(this.toolbarId));
	      this.prepareNodes();
	      this.buildDispatchNodes();
	      this._filterNode = [];
	      this._regionInput = [];
	      this._autocomplete = [];
	      this.bindEvents();
	      babelHelpers.classPrivateFieldGet(this, _page).initButtons();
	      if (this.isMSBrowser()) {
	        babelHelpers.classPrivateFieldGet(this, _context).classList.add('bx-sender-letter-ms-ie');
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _isRegistered)) {
	        babelHelpers.classPrivateFieldGet(this, _loginNode).style = '';
	        babelHelpers.classPrivateFieldGet(this, _formNode).style = 'display:none;';
	      }
	    }
	  }, {
	    key: "prepareNodes",
	    value: function prepareNodes() {
	      babelHelpers.classPrivateFieldSet(this, _selectorNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('template-selector', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _editorNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('editor', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _titleNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('title', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _loginNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('login', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _formNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('sender-toloka-form', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _oauthCodeNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('toloka-oauth-code', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _buttonsNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('letter-buttons', babelHelpers.classPrivateFieldGet(this, _context)));
	      babelHelpers.classPrivateFieldSet(this, _templateNameNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('template-name', babelHelpers.classPrivateFieldGet(this, _editorNode)));
	      babelHelpers.classPrivateFieldSet(this, _templateTypeNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('template-type', babelHelpers.classPrivateFieldGet(this, _editorNode)));
	      babelHelpers.classPrivateFieldSet(this, _templateIdNode, babelHelpers.classPrivateFieldGet(this, _helper).getNode('template-id', babelHelpers.classPrivateFieldGet(this, _editorNode)));
	      this._projectNode = document.getElementById('CONFIGURATION_PROJECT_ID');
	      this._poolNode = document.getElementById('CONFIGURATION_POOL_ID');
	      this._taskSuiteNode = document.getElementById('CONFIGURATION_TASK_SUITE_ID');
	      this._descriptionNode = document.getElementById('CONFIGURATION_DESCRIPTION');
	      this._instructionNode = document.getElementById('CONFIGURATION_INSTRUCTION');
	      this._tasksNode = document.getElementById('CONFIGURATION_TASKS');
	      this._overlapNode = document.getElementById('CONFIGURATION_OVERLAP');
	      this._adultContentNode = document.getElementById('CONFIGURATION_ADULT_CONTENT');
	      this._priceNode = document.getElementById('CONFIGURATION_PRICE');
	      this._expireInNode = document.getElementById('CONFIGURATION_EXPIRE_IN');
	      this._saveBtn = document.getElementById('ui-button-panel-save');
	      this._projectNode.parentNode.parentNode.style = 'display:none';
	      this._poolNode.parentNode.parentNode.style = 'display:none';
	      this._taskSuiteNode.parentNode.parentNode.style = 'display:none';
	    }
	  }, {
	    key: "reInitAddressWidget",
	    value: function reInitAddressWidget() {
	      if (this._filterNode[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)] && this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)]) {
	        this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)].removeAutocompleteNode();
	        this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)] = null;
	      }
	      if (this._filterNode[babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)] && this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)]) {
	        this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)].removeAutocompleteNode();
	        this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)] = null;
	      }
	      this.initWidget();
	    }
	  }, {
	    key: "initWidget",
	    value: function initWidget() {
	      if (babelHelpers.classPrivateFieldGet(this, _filterNode)) {
	        BX.bind(babelHelpers.classPrivateFieldGet(this, _filterNode), 'click', this.initAddressWidget.bind(this, babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)));
	        BX.bind(babelHelpers.classPrivateFieldGet(this, _filterNode), 'click', this.initAddressWidget.bind(this, babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)));
	        BX.bind(this.getFilter().getPopup().popupContainer, 'click', this.initAddressWidget.bind(this, babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)));
	        BX.bind(this.getFilter().getPopup().popupContainer, 'click', this.initAddressWidget.bind(this, babelHelpers.classPrivateFieldGet(this, _REGION_BY_PHONE)));
	      }
	    }
	  }, {
	    key: "initAddressWidget",
	    value: function initAddressWidget(name, event) {
	      var _this2 = this;
	      if (event.target && this.getFilter().getSearch().isSquareRemoveButton(event.target)) {
	        this.reInitAddressWidget();
	      }
	      this._filterNode[name] = document.querySelectorAll(".main-ui-filter-field-container-list > div[data-name=".concat(name, "]"))[0];
	      if (!this._filterNode[name]) {
	        if (this._autocomplete[name]) {
	          this._autocomplete[name].removeAutocompleteNode();
	          this._autocomplete[babelHelpers.classPrivateFieldGet(this, _REGION_BY_IP)] = null;
	        }
	        return;
	      }
	      if (this._autocomplete[name]) {
	        return;
	      }
	      var self = this;
	      this.optionData[name] = this.optionData[name] || [];
	      this._autocomplete[name] = new Autocomplete(this._filterNode[name], {
	        options: this.optionData[name],
	        multiple: true,
	        autocomplete: true,
	        onChange: function onChange(value, preparedValue) {
	          babelHelpers.classPrivateFieldGet(self, _filterData)[name] = value;
	          babelHelpers.classPrivateFieldGet(_this2, _filter).getFieldByName(name).ITEMS = preparedValue;
	          babelHelpers.classPrivateFieldGet(_this2, _filter).getFieldByName(name).VALUE = preparedValue;
	        }
	      });
	      this._regionInput[name] = document.querySelectorAll("input[data-name=autocomplete-".concat(name, "]"))[0];
	      BX.bind(this._regionInput[name], 'keyup', this.getLocationList.bind(this, name));
	    }
	  }, {
	    key: "register",
	    value: function register() {
	      var self = this;
	      babelHelpers.classPrivateFieldGet(this, _ajaxAction).request({
	        action: 'registerOAuth',
	        onsuccess: function onsuccess(response) {
	          babelHelpers.classPrivateFieldGet(self, _loginNode).style = 'display:none;';
	          babelHelpers.classPrivateFieldGet(self, _formNode).style = '';
	        },
	        data: {
	          'access_code': babelHelpers.classPrivateFieldGet(this, _oauthCodeNode).value
	        }
	      });
	    }
	  }, {
	    key: "isMSBrowser",
	    value: function isMSBrowser() {
	      return window.navigator.userAgent.match(/(Trident\/|MSIE|Edge\/)/) !== null;
	    }
	  }, {
	    key: "getPatternTitle",
	    value: function getPatternTitle(name) {
	      return babelHelpers.classPrivateFieldGet(this, _helper).replace(babelHelpers.classPrivateFieldGet(this, _mess).patternTitle, {
	        'name': name,
	        'date': BX.date.format(babelHelpers.classPrivateFieldGet(this, _prettyDateFormat))
	      });
	    }
	  }, {
	    key: "onTemplateSelect",
	    value: function onTemplateSelect(template) {
	      if (babelHelpers.classPrivateFieldGet(this, _templateNameNode)) {
	        babelHelpers.classPrivateFieldGet(this, _templateNameNode).textContent = template.name;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _templateTypeNode)) {
	        babelHelpers.classPrivateFieldGet(this, _templateTypeNode).value = template.type;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _templateIdNode)) {
	        babelHelpers.classPrivateFieldGet(this, _templateIdNode).value = template.code;
	      }
	      babelHelpers.classPrivateFieldSet(this, _messageFields, template.messageFields);
	      this.buildDispatchNodes();
	      babelHelpers.classPrivateFieldGet(this, _titleNode).value = this.getPatternTitle(template.name);
	      BX.fireEvent(babelHelpers.classPrivateFieldGet(this, _titleNode), 'change');
	      this.closeTemplateSelector();
	      window.scrollTo(0, 0);
	    }
	  }, {
	    key: "buildDispatchNodes",
	    value: function buildDispatchNodes() {
	      var self = this;
	      babelHelpers.classPrivateFieldGet(this, _helper).getNodes('dispatch', babelHelpers.classPrivateFieldGet(this, _context)).forEach(function (node) {
	        var code = node.getAttribute('data-code');
	        for (var field in babelHelpers.classPrivateFieldGet(self, _messageFields)) {
	          if (!babelHelpers.classPrivateFieldGet(self, _messageFields).hasOwnProperty(field)) {
	            continue;
	          }
	          var data = babelHelpers.classPrivateFieldGet(self, _messageFields)[field];
	          if (data.code === code && node.innerHTML.length === 0) {
	            node.innerHTML = data.value;
	          }
	          babelHelpers.classPrivateFieldGet(self, _templateData)[data.code] = data.value;
	        }
	      });
	    }
	  }, {
	    key: "closeTemplateSelector",
	    value: function closeTemplateSelector() {
	      this.changeDisplayingTemplateSelector(false);
	    }
	  }, {
	    key: "showTemplateSelector",
	    value: function showTemplateSelector() {
	      this.changeDisplayingTemplateSelector(true);
	    }
	  }, {
	    key: "changeDisplayingTemplateSelector",
	    value: function changeDisplayingTemplateSelector(isShow) {
	      var classShow = 'bx-sender-letter-show';
	      var classHide = 'bx-sender-letter-hide';
	      babelHelpers.classPrivateFieldGet(this, _helper).changeClass(babelHelpers.classPrivateFieldGet(this, _selectorNode), classShow, isShow);
	      babelHelpers.classPrivateFieldGet(this, _helper).changeClass(babelHelpers.classPrivateFieldGet(this, _selectorNode), classHide, !isShow);
	      babelHelpers.classPrivateFieldGet(this, _helper).changeClass(babelHelpers.classPrivateFieldGet(this, _editorNode), classShow, !isShow);
	      babelHelpers.classPrivateFieldGet(this, _helper).changeClass(babelHelpers.classPrivateFieldGet(this, _editorNode), classHide, isShow);
	      babelHelpers.classPrivateFieldGet(this, _helper).changeDisplay(babelHelpers.classPrivateFieldGet(this, _templateChangeButton), !isShow);
	      babelHelpers.classPrivateFieldGet(this, _helper).changeDisplay(babelHelpers.classPrivateFieldGet(this, _buttonsNode), !isShow);
	    }
	  }, {
	    key: "objectKeysToLowerCase",
	    value: function objectKeysToLowerCase(origObj) {
	      var self = this;
	      if (origObj === null) {
	        return origObj;
	      }
	      return Object.keys(origObj).reduce(function (newObj, key) {
	        var val = origObj[key];
	        newObj[key.toLowerCase()] = babelHelpers["typeof"](val) === 'object' ? self.objectKeysToLowerCase(val) : val;
	        return newObj;
	      }, {});
	    }
	  }, {
	    key: "getLocationList",
	    value: function getLocationList(name) {
	      var _this3 = this;
	      if (this._regionInput[name].value.length < 3) {
	        return;
	      }
	      this.usedWords = this.usedWords || [];
	      var value = this._regionInput[name].value;
	      if (this.usedWords.includes(value)) {
	        return;
	      }
	      this.usedWords.push(value);
	      var self = this;
	      babelHelpers.classPrivateFieldGet(this, _ajaxAction).request({
	        action: 'getGeoList',
	        data: {
	          name: value
	        },
	        onsuccess: function onsuccess(response) {
	          if (!_this3.optionData[name]) {
	            _this3.optionData[name] = [];
	          }
	          for (var _value in response) {
	            var responseData = response[_value];
	            if (babelHelpers["typeof"](responseData) === 'object' && 'id' in responseData) {
	              _this3.optionData[name].push(responseData);
	            }
	          }
	          if (self._autocomplete[name]) {
	            _this3.optionData[name] = _this3.optionData[name].reduce(function (acc, current) {
	              var x = acc.find(function (item) {
	                return item.id === current.id;
	              });
	              if (!x) {
	                return acc.concat([current]);
	              } else {
	                return acc;
	              }
	            }, []);
	            self._autocomplete[name].setOptions(_this3.optionData[name]);
	          }
	        }
	      });
	    }
	  }, {
	    key: "validateRequiredFields",
	    value: function validateRequiredFields() {
	      var _this4 = this;
	      var success = true;
	      [this._expireInNode, this._priceNode, this._tasksNode].every(function (element) {
	        if (!_this4.validateField(element)) {
	          success = false;
	          return false;
	        }
	      });
	      if (!success) {
	        this.removeLoader();
	      }
	      return success;
	    }
	  }, {
	    key: "removeLoader",
	    value: function removeLoader() {
	      this._saveBtn.classList.remove("ui-btn-wait");
	    }
	  }, {
	    key: "validateField",
	    value: function validateField(field) {
	      if (!this._validatorPopup) {
	        this._validatorPopup = new main_popup.Popup({
	          id: "sender-toloka-validator",
	          content: "".concat(babelHelpers.classPrivateFieldGet(this, _mess).required)
	        });
	      }
	      if (!field.value) {
	        this._validatorPopup.setBindElement(field);
	        this._validatorPopup.show();
	        field.classList.add("bx-sender-form-control-danger");
	        field.scrollIntoView();
	        return false;
	      }
	      this._validatorPopup.close();
	      field.classList.remove("bx-sender-form-control-danger");
	      return true;
	    }
	  }, {
	    key: "createProject",
	    value: function createProject() {
	      var _this5 = this;
	      if (!this.validateRequiredFields()) {
	        return;
	      }
	      var input_key = Object.keys(babelHelpers.classPrivateFieldGet(this, _templateData)['INPUT_VALUE'])[0];
	      var output_key = Object.keys(babelHelpers.classPrivateFieldGet(this, _templateData)['OUTPUT_VALUE'])[0];
	      babelHelpers.classPrivateFieldGet(this, _ajaxAction).request({
	        action: 'createProject',
	        data: {
	          id: this._projectNode.value,
	          name: babelHelpers.classPrivateFieldGet(this, _titleNode).value,
	          description: this._descriptionNode.value,
	          instruction: this._instructionNode.value,
	          input_type: babelHelpers.classPrivateFieldGet(this, _templateData)['INPUT_VALUE'][input_key],
	          input_identificator: input_key,
	          output_type: babelHelpers.classPrivateFieldGet(this, _templateData)['OUTPUT_VALUE'][output_key],
	          output_identificator: output_key,
	          markup: babelHelpers.classPrivateFieldGet(this, _templateData)['PRESET'].template,
	          script: babelHelpers.classPrivateFieldGet(this, _templateData)['PRESET'].js,
	          styles: babelHelpers.classPrivateFieldGet(this, _templateData)['PRESET'].css
	        },
	        onsuccess: function onsuccess(response) {
	          _this5._projectNode.value = response.id;
	          _this5.createPool(response.id);
	        },
	        onfailure: function onfailure(response) {
	          _this5.removeLoader();
	        }
	      });
	    }
	  }, {
	    key: "createPool",
	    value: function createPool(projectId) {
	      var _this6 = this;
	      var input_key = Object.keys(babelHelpers.classPrivateFieldGet(this, _templateData)['INPUT_VALUE'])[0];
	      babelHelpers.classPrivateFieldGet(this, _ajaxAction).request({
	        action: 'createPool',
	        data: {
	          id: this._poolNode.value,
	          task_suite_id: this._taskSuiteNode.value,
	          project_id: projectId,
	          private_name: babelHelpers.classPrivateFieldGet(this, _titleNode).value,
	          public_description: this._descriptionNode.value,
	          may_contain_adult_content: this._adultContentNode.checked,
	          reward_per_assignment: this._priceNode.value,
	          will_expire: this._expireInNode.value,
	          overlap: this._overlapNode.value,
	          tasks: this._tasksNode.value,
	          identificator: input_key,
	          filter: babelHelpers.classPrivateFieldGet(this, _filterData)
	        },
	        onsuccess: function onsuccess(response) {
	          _this6._poolNode.value = response.pool_id;
	          _this6._taskSuiteNode.value = response.id;
	          var form = babelHelpers.classPrivateFieldGet(_this6, _context).getElementsByTagName('form');
	          if (form && form[0]) {
	            form[0].appendChild(BX.create('input', {
	              attrs: {
	                type: "hidden",
	                name: "apply",
	                value: "Y"
	              }
	            }));
	            form[0].submit();
	          }
	        },
	        onfailure: function onfailure(response) {
	          _this6.removeLoader();
	        }
	      });
	    }
	  }, {
	    key: "applyChanges",
	    value: function applyChanges(event) {
	      if (!babelHelpers.classPrivateFieldGet(this, _isAvailable)) {
	        BX.UI.InfoHelper.show('limit_crm_marketing_toloka');
	        return;
	      }
	      this.createProject();
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter() {
	      var filter = BX.Main.filterManager.getById(babelHelpers.classPrivateFieldGet(this, _filterId));
	      if (!filter || !(filter instanceof BX.Main.Filter)) {
	        return null;
	      }
	      return filter;
	    }
	  }], [{
	    key: "create",
	    value: function create(settings) {
	      var self = new Toloka();
	      self.initialize(settings);
	      return self;
	    }
	  }]);
	  return Toloka;
	}();

	exports.Toloka = Toloka;

}((this.BX.Sender = this.BX.Sender || {}),BX.Main));
//# sourceMappingURL=toloka.bundle.js.map
