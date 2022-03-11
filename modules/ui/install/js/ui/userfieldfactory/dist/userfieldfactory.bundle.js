this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_popup,main_core,ui_userfield) {
	'use strict';

	var MAX_FIELD_LENGTH = 50;
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var FieldTypes = /*#__PURE__*/function () {
	  function FieldTypes() {
	    babelHelpers.classCallCheck(this, FieldTypes);
	  }

	  babelHelpers.createClass(FieldTypes, null, [{
	    key: "getTypes",
	    value: function getTypes() {
	      return Object.freeze({
	        string: 'string',
	        enumeration: 'enumeration',
	        date: 'date',
	        datetime: 'datetime',
	        address: 'address',
	        url: 'url',
	        file: 'file',
	        money: 'money',
	        "boolean": 'boolean',
	        "double": 'double',
	        employee: 'employee',
	        crm: 'crm',
	        crmStatus: 'crm_status'
	      });
	    }
	  }, {
	    key: "getDescriptions",
	    value: function getDescriptions() {
	      return Object.freeze({
	        string: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_STRING_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_STRING_LABEL')
	        },
	        enumeration: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ENUM_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUMERATION_LABEL')
	        },
	        datetime: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DATETIME_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DATETIME_LABEL')
	        },
	        address: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_ADDRESS_LEGEND")
	        },
	        url: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_URL_LEGEND")
	        },
	        file: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_FILE_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_FILE_LABEL')
	        },
	        money: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_MONEY_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_MONEY_LABEL')
	        },
	        "boolean": {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_BOOLEAN_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_BOOLEAN_LEGEND")
	        },
	        "double": {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_LEGEND"),
	          defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DOUBLE_LABEL')
	        },
	        employee: {
	          title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_TITLE"),
	          description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_LEGEND")
	        }
	      });
	    }
	  }, {
	    key: "getCustomTypeDescription",
	    value: function getCustomTypeDescription() {
	      return Object.freeze({
	        name: 'custom',
	        title: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_TITLE'),
	        description: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_CUSTOM_LEGEND')
	      });
	    }
	  }]);
	  return FieldTypes;
	}();
	var DefaultData = Object.freeze({
	  multiple: 'N',
	  mandatory: 'N',
	  userTypeId: FieldTypes.string,
	  showFilter: 'E',
	  showInList: 'Y',
	  settings: {},
	  isSearchable: 'N'
	});
	var DefaultFieldData = Object.freeze({
	  file: {
	    showFilter: 'N',
	    showInList: 'N'
	  },
	  employee: {
	    showFilter: 'I'
	  },
	  crm: {
	    showFilter: 'I'
	  },
	  crm_status: {
	    showFilter: 'I'
	  },
	  enumeration: {
	    settings: {
	      DISPLAY: 'UI'
	    }
	  },
	  "double": {
	    settings: {
	      PRECISION: 2
	    }
	  }
	});

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var SCROLL_OFFSET = 3;
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var _enableScrollToBottom = /*#__PURE__*/new WeakMap();

	var _enableScrollToTop = /*#__PURE__*/new WeakMap();

	var CreationMenu = /*#__PURE__*/function () {
	  function CreationMenu(id, types, params) {
	    babelHelpers.classCallCheck(this, CreationMenu);

	    _classPrivateFieldInitSpec(this, _enableScrollToBottom, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _enableScrollToTop, {
	      writable: true,
	      value: void 0
	    });

	    this.id = id;
	    this.items = types;
	    this.params = {};

	    if (main_core.Type.isPlainObject(params)) {
	      this.params = params;
	    }
	  }

	  babelHelpers.createClass(CreationMenu, [{
	    key: "getId",
	    value: function getId() {
	      if (!this.id) {
	        return 'ui-user-field-factory-menu';
	      }

	      return this.id;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var onItemClick = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (!this.popup) {
	        var options = _objectSpread(_objectSpread({}, CreationMenu.getDefaultPopupOptions()), this.params);

	        options.events = {
	          onPopupShow: this.onPopupShow.bind(this),
	          onPopupDestroy: this.onPopupDestroy.bind(this)
	        };
	        options.id = this.getId();
	        this.popup = new main_popup.Popup(options);
	      }

	      this.popup.setContent(this.render(onItemClick));
	      return this.popup;
	    }
	  }, {
	    key: "open",
	    value: function open(callback) {
	      var popup = this.getPopup(callback);

	      if (!popup.isShown()) {
	        popup.show();
	      }
	    }
	  }, {
	    key: "render",
	    value: function render(onItemClick) {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-container\"></div>"])));
	        var scrollIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"42\" height=\"13\" viewBox=\"0 0 42 13\">\n" + "  <polyline fill=\"none\" stroke=\"#CACDD1\" stroke-width=\"2\" points=\"274 98 284 78.614 274 59\" transform=\"rotate(90 186 -86.5)\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" + "</svg>\n";
	        this.topScrollButton = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-scroll-top\">", "</div>"])), scrollIcon);
	        this.bottomScrollButton = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-scroll-bottom\">", "</div>"])), scrollIcon);
	        this.container.appendChild(this.topScrollButton);
	        this.container.appendChild(this.bottomScrollButton);
	        this.container.appendChild(this.renderList(onItemClick));
	      }

	      return this.container;
	    }
	  }, {
	    key: "renderList",
	    value: function renderList(onItemClick) {
	      var _this = this;

	      if (!this.containerList) {
	        this.containerList = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-list\"></div>"])));
	        this.items.forEach(function (item) {
	          _this.containerList.appendChild(_this.renderItem(item, onItemClick));
	        });
	      }

	      return this.containerList;
	    }
	  }, {
	    key: "renderItem",
	    value: function renderItem(item, onClick) {
	      var _this2 = this;

	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-item\" onclick=\"", "\">\n\t\t\t<div class=\"ui-userfieldfactory-creation-menu-item-title\">", "</div>\n\t\t\t<div class=\"ui-userfieldfactory-creation-menu-item-desc\">", "</div>\n\t\t</div>"])), function () {
	        _this2.handleItemClick(item, onClick);
	      }, item.title, item.description);
	    }
	  }, {
	    key: "handleItemClick",
	    value: function handleItemClick(item, onClick) {
	      if (main_core.Type.isFunction(item.onClick)) {
	        item.onClick(item.name);
	      } else if (main_core.Type.isFunction(onClick)) {
	        onClick(item.name);
	      }

	      this.getPopup().close();
	    }
	  }, {
	    key: "onPopupShow",
	    value: function onPopupShow() {
	      main_core.Event.bind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
	      main_core.Event.bind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
	      main_core.Event.bind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
	      main_core.Event.bind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
	      main_core.Event.bind(this.containerList, "scroll", this.onScroll.bind(this));
	      window.setTimeout(this.adjust.bind(this), 100);
	    }
	  }, {
	    key: "onPopupDestroy",
	    value: function onPopupDestroy() {
	      main_core.Event.unbind(this.bottomScrollButton, "mouseover", this.onBottomButtonMouseOver.bind(this));
	      main_core.Event.unbind(this.bottomScrollButton, "mouseout", this.onBottomButtonMouseOut.bind(this));
	      main_core.Event.unbind(this.topScrollButton, "mouseover", this.onTopButtonMouseOver.bind(this));
	      main_core.Event.unbind(this.topScrollButton, "mouseout", this.onTopButtonMouseOut.bind(this));
	      main_core.Event.unbind(this.containerList, "scroll", this.onScroll.bind(this));
	      this.container = null;
	      this.containerList = null;
	      this.topScrollButton = null;
	      this.bottomScrollButton = null;
	      this.popup = null;
	    }
	  }, {
	    key: "onBottomButtonMouseOver",
	    value: function onBottomButtonMouseOver() {
	      if (babelHelpers.classPrivateFieldGet(this, _enableScrollToBottom)) {
	        return;
	      }

	      babelHelpers.classPrivateFieldSet(this, _enableScrollToBottom, true);
	      babelHelpers.classPrivateFieldSet(this, _enableScrollToTop, false);
	      (function scroll() {
	        if (!babelHelpers.classPrivateFieldGet(this, _enableScrollToBottom)) {
	          return;
	        }

	        if (this.containerList.scrollTop + this.containerList.offsetHeight !== this.containerList.scrollHeight) {
	          this.containerList.scrollTop += SCROLL_OFFSET;
	        }

	        if (this.containerList.scrollTop + this.containerList.offsetHeight === this.containerList.scrollHeight) {
	          babelHelpers.classPrivateFieldSet(this, _enableScrollToBottom, false);
	        } else {
	          window.setTimeout(scroll.bind(this), 20);
	        }
	      }).bind(this)();
	    }
	  }, {
	    key: "onBottomButtonMouseOut",
	    value: function onBottomButtonMouseOut() {
	      babelHelpers.classPrivateFieldSet(this, _enableScrollToBottom, false);
	    }
	  }, {
	    key: "onTopButtonMouseOver",
	    value: function onTopButtonMouseOver() {
	      if (babelHelpers.classPrivateFieldGet(this, _enableScrollToTop)) {
	        return;
	      }

	      babelHelpers.classPrivateFieldSet(this, _enableScrollToBottom, false);
	      babelHelpers.classPrivateFieldSet(this, _enableScrollToTop, true);
	      (function scroll() {
	        if (!babelHelpers.classPrivateFieldGet(this, _enableScrollToTop)) {
	          return;
	        }

	        if (this.containerList.scrollTop > 0) {
	          this.containerList.scrollTop -= SCROLL_OFFSET;
	        }

	        if (this.containerList.scrollTop === 0) {
	          babelHelpers.classPrivateFieldSet(this, _enableScrollToTop, false);
	        } else {
	          window.setTimeout(scroll.bind(this), 20);
	        }
	      }).bind(this)();
	    }
	  }, {
	    key: "onTopButtonMouseOut",
	    value: function onTopButtonMouseOut() {
	      babelHelpers.classPrivateFieldSet(this, _enableScrollToTop, false);
	    }
	  }, {
	    key: "onScroll",
	    value: function onScroll() {
	      this.adjust();
	    }
	  }, {
	    key: "adjust",
	    value: function adjust() {
	      var height = this.containerList.offsetHeight;
	      var scrollTop = this.containerList.scrollTop;
	      var scrollHeight = this.containerList.scrollHeight;

	      if (scrollTop === 0) {
	        main_core.Dom.hide(this.topScrollButton);
	      } else {
	        main_core.Dom.show(this.topScrollButton);
	      }

	      if (scrollTop + height === scrollHeight) {
	        main_core.Dom.hide(this.bottomScrollButton);
	      } else {
	        main_core.Dom.show(this.bottomScrollButton);
	      }
	    }
	  }], [{
	    key: "getDefaultPopupOptions",
	    value: function getDefaultPopupOptions() {
	      return {
	        autoHide: true,
	        draggable: false,
	        offsetLeft: 0,
	        offsetTop: 0,
	        noAllPaddings: true,
	        bindOptions: {
	          forceBindPosition: true
	        },
	        closeByEsc: true,
	        cacheable: false
	      };
	    }
	  }]);
	  return CreationMenu;
	}();

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */
	var EnumItem = /*#__PURE__*/function () {
	  function EnumItem() {
	    var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    var id = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	    babelHelpers.classCallCheck(this, EnumItem);
	    this.value = value;
	    this.id = id;
	  }

	  babelHelpers.createClass(EnumItem, [{
	    key: "setNode",
	    value: function setNode(node) {
	      this.node = node;
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      return this.node;
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      var node = this.getNode();

	      if (!node) {
	        return null;
	      }

	      if (node instanceof HTMLInputElement) {
	        return node;
	      }

	      return node.querySelector('input');
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var input = this.getInput();

	      if (input && input.value) {
	        return input.value;
	      }

	      return this.value || '';
	    }
	  }]);
	  return EnumItem;
	}();

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4$1, _templateObject5$1, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17;
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var Configurator = /*#__PURE__*/function () {
	  function Configurator(params) {
	    babelHelpers.classCallCheck(this, Configurator);

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.userField) {
	        this.userField = params.userField;
	      }

	      if (main_core.Type.isFunction(params.onSave)) {
	        this.onSave = params.onSave;
	      }

	      if (main_core.Type.isFunction(params.onCancel)) {
	        this.onCancel = params.onCancel;
	      }
	    }

	    this.enumItems = new Set();
	  }

	  babelHelpers.createClass(Configurator, [{
	    key: "render",
	    value: function render() {
	      var _this = this;

	      this.node = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator\"></div>"])));
	      this.labelInput = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"text\" value=\"", "\" />"])), main_core.Text.encode(this.userField.getTitle()));
	      this.node.appendChild(main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-title\">\n\t\t\t\t<span class=\"ui-userfieldfactory-configurator-title-text\">", "</span>\n\t\t\t</div>\n\t\t\t<div class=\"ui-userfieldfactory-configurator-content\">\n\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>"])), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_CONFIGURATOR_FIELD_TITLE'), this.labelInput));

	      if (this.userField.getUserTypeId() === FieldTypes.getTypes().enumeration) {
	        this.node.appendChild(this.renderEnumeration());
	      }

	      this.node.appendChild(this.renderOptions());

	      var save = function save(event) {
	        event.preventDefault();

	        if (main_core.Type.isFunction(_this.onSave)) {
	          _this.onSave(_this.saveField());
	        }
	      };

	      var cancel = function cancel(event) {
	        event.preventDefault();

	        if (main_core.Type.isFunction(_this.onCancel)) {
	          _this.onCancel();
	        } else {
	          _this.node.style.display = 'none';
	        }
	      };

	      this.saveButton = main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-btn ui-btn-primary\" onclick=\"", "\">", "</span>"])), save.bind(this), main_core.Loc.getMessage('UI_USERFIELD_SAVE'));
	      this.cancelButton = main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-btn ui-btn-light-border\" onclick=\"", "\">", "</span>"])), cancel.bind(this), main_core.Loc.getMessage('UI_USERFIELD_CANCEL'));
	      this.node.appendChild(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t", "", "\n\t\t</div>"])), this.saveButton, this.cancelButton));
	      return this.node;
	    }
	  }, {
	    key: "saveField",
	    value: function saveField() {
	      if (this.timeCheckbox) {
	        if (this.timeCheckbox.checked) {
	          this.userField.setUserTypeId(FieldTypes.getTypes().datetime);
	        } else {
	          this.userField.setUserTypeId(FieldTypes.getTypes().date);
	        }
	      }

	      if (this.multipleCheckbox) {
	        this.userField.setIsMultiple(this.multipleCheckbox.checked);
	      }

	      this.userField.setTitle(this.labelInput.value);
	      this.userField.setIsMandatory(this.mandatoryCheckbox.checked);
	      this.saveEnumeration(this.userField, this.enumItems);
	      return this.userField;
	    }
	  }, {
	    key: "renderEnumeration",
	    value: function renderEnumeration() {
	      var _this2 = this;

	      this.enumItemsContainer = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\"></div>"])));
	      this.enumAddItemContainer = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block-add-field\">\n\t\t\t<span class=\"ui-userfieldfactory-configurator-add-button\" onclick=\"", "\">", "</span>\n\t\t</div>"])), function () {
	        _this2.addEnumInput().focus();
	      }, main_core.Loc.getMessage('UI_USERFIELD_ADD'));
	      this.enumContainer = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-title\">\n\t\t\t\t<span class=\"ui-userfieldfactory-configurator-title-text\">", "</span>\n\t\t\t</div>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"])), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUM_ITEMS'), this.enumItemsContainer, this.enumAddItemContainer);
	      this.userField.getEnumeration().forEach(function (item) {
	        _this2.addEnumInput(item);
	      });
	      this.addEnumInput();
	      return this.enumContainer;
	    }
	  }, {
	    key: "addEnumInput",
	    value: function addEnumInput(item) {
	      var _this3 = this;

	      var enumItem;

	      if (main_core.Type.isPlainObject(item)) {
	        enumItem = new EnumItem(item.value, item.id);
	      } else {
	        enumItem = new EnumItem();
	      }

	      var node = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div style=\"margin-bottom: 10px;\" class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row\">\n\t\t\t<input class=\"ui-ctl-element\" type=\"text\" value=\"", "\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-remove-enum\" onclick=\"", "\"></div>\n\t\t</div>"])), main_core.Text.encode(enumItem.getValue()), function (event) {
	        event.preventDefault();

	        _this3.deleteEnumItem(enumItem);
	      });
	      enumItem.setNode(node);
	      this.enumItems.add(enumItem);
	      this.enumItemsContainer.appendChild(node);
	      return node;
	    }
	  }, {
	    key: "deleteEnumItem",
	    value: function deleteEnumItem(item) {
	      this.enumItemsContainer.removeChild(item.getNode());
	      this.enumItems["delete"](item);
	    }
	  }, {
	    key: "renderOptions",
	    value: function renderOptions() {
	      this.optionsContainer = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\"></div>"])));
	      this.mandatoryCheckbox = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"checkbox\">"])));
	      this.mandatoryCheckbox.checked = this.userField.isMandatory();
	      this.optionsContainer.appendChild(main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t</div>"])), this.mandatoryCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_REQUIRED')));

	      if (!this.userField.isSaved() && (this.userField.getUserTypeId() === FieldTypes.getTypes().date || this.userField.getUserTypeId() === FieldTypes.getTypes().datetime)) {
	        this.timeCheckbox = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"checkbox\">"])));
	        this.timeCheckbox.checked = this.userField.getUserTypeId() === FieldTypes.getTypes().datetime;
	        this.optionsContainer.appendChild(main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t</div>"])), this.timeCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENABLE_TIME')));
	      }

	      if (!this.userField.isSaved() && this.userField.getUserTypeId() !== FieldTypes.getTypes()["boolean"]) {
	        this.multipleCheckbox = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"checkbox\">"])));
	        this.multipleCheckbox.checked = this.userField.isMultiple();
	        this.optionsContainer.appendChild(main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t</div>"])), this.multipleCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_MULTIPLE')));
	      }

	      return this.optionsContainer;
	    }
	  }, {
	    key: "saveEnumeration",
	    value: function saveEnumeration(userField, enumItems) {
	      var items = [];
	      var sort = 100;
	      enumItems.forEach(function (item) {
	        items.push({
	          value: item.getValue(),
	          sort: sort,
	          id: item.getId()
	        });
	        sort += 100;
	      });
	      userField.setEnumeration(items);
	    }
	  }]);
	  return Configurator;
	}();

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	/**
	 * @memberof BX.UI.UserFieldFactory
	 * @mixes EventEmitter
	 */

	var Factory = /*#__PURE__*/function () {
	  function Factory(entityId) {
	    var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Factory);
	    main_core_events.EventEmitter.makeObservable(this, 'BX.UI.UserFieldFactory.Factory');
	    this.configuratorClass = Configurator;

	    if (main_core.Type.isString(entityId) && entityId.length > 0) {
	      this.entityId = entityId;
	    }

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.menuId)) {
	        this.menuId = params.menuId;
	      }

	      if (!main_core.Type.isArray(params.types)) {
	        params.types = [];
	      }

	      if (main_core.Type.isDomNode(params.bindElement)) {
	        this.bindElement = params.bindElement;
	      }

	      this.moduleId = params.moduleId;
	      this.setCustomTypesUrl(params.customTypesUrl).setConfiguratorClass(params.configuratorClass);
	    } else {
	      params.types = [];
	    }

	    this.types = this.getFieldTypes().concat(params.types);
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "getFieldTypes",
	    value: function getFieldTypes() {
	      var types = [];
	      Object.keys(FieldTypes.getDescriptions()).forEach(function (name) {
	        types.push(_objectSpread$1(_objectSpread$1({}, FieldTypes.getDescriptions()[name]), {
	          name: name
	        }));
	      });
	      this.emit('OnGetUserTypes', {
	        types: types
	      });
	      return types;
	    }
	  }, {
	    key: "getMenu",
	    value: function getMenu(params) {
	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      if (!main_core.Type.isDomNode(params.bindElement)) {
	        params.bindElement = this.bindElement;
	      }

	      var types = this.types;

	      if (this.customTypesUrl && !this.isCustomTypeAdded) {
	        var customType = _objectSpread$1({}, FieldTypes.getCustomTypeDescription());

	        customType.onClick = this.onCustomTypeClick.bind(this);
	        types.push(customType);
	        this.isCustomTypeAdded = true;
	      }

	      if (!this.menu) {
	        this.menu = new CreationMenu(this.menuId, types, params);
	      }

	      return this.menu;
	    }
	  }, {
	    key: "setConfiguratorClass",
	    value: function setConfiguratorClass(configuratorClassName) {
	      var configuratorClass = null;

	      if (main_core.Type.isString(configuratorClassName)) {
	        configuratorClass = main_core.Reflection.getClass(configuratorClassName);
	      } else if (main_core.Type.isFunction(configuratorClassName)) {
	        configuratorClass = configuratorClassName;
	      }

	      if (main_core.Type.isFunction(configuratorClass) && configuratorClass.prototype instanceof Configurator) {
	        this.configuratorClass = configuratorClass;
	      }
	    }
	  }, {
	    key: "setCustomTypesUrl",
	    value: function setCustomTypesUrl(customTypesUrl) {
	      this.customTypesUrl = customTypesUrl;
	      return this;
	    }
	  }, {
	    key: "getConfigurator",
	    value: function getConfigurator(params) {
	      return new this.configuratorClass(params);
	    }
	  }, {
	    key: "createUserField",
	    value: function createUserField(fieldType, fieldName) {
	      var data = _objectSpread$1(_objectSpread$1(_objectSpread$1({}, DefaultData), DefaultFieldData[fieldType]), {
	        userTypeId: fieldType
	      });

	      if (!main_core.Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH) {
	        fieldName = this.generateFieldName();
	      }

	      data.fieldName = fieldName;
	      data.entityId = this.entityId;
	      var userField = new ui_userfield.UserField(data, {
	        moduleId: this.moduleId
	      });
	      userField.setTitle(this.getDefaultLabel(fieldType));
	      this.emit('onCreateField', {
	        userField: userField
	      });
	      return userField;
	    }
	  }, {
	    key: "getDefaultLabel",
	    value: function getDefaultLabel(fieldType) {
	      var label = main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_LABEL');
	      this.types.forEach(function (type) {
	        if (type.name === fieldType && main_core.Type.isString(type.defaultTitle)) {
	          label = type.defaultTitle;
	        }
	      });
	      return label;
	    }
	  }, {
	    key: "generateFieldName",
	    value: function generateFieldName() {
	      var name = 'UF_' + (this.entityId ? this.entityId + "_" : "");
	      var dateSuffix = new Date().getTime().toString();

	      if (name.length + dateSuffix.length > MAX_FIELD_LENGTH) {
	        dateSuffix = dateSuffix.substr(name.length + dateSuffix.length - MAX_FIELD_LENGTH);
	      }

	      name += dateSuffix;
	      return name;
	    }
	  }, {
	    key: "onCustomTypeClick",
	    value: function onCustomTypeClick() {
	      var _this = this;

	      if (!this.customTypesUrl) {
	        return;
	      }

	      BX.SidePanel.Instance.open(this.customTypesUrl.toString(), {
	        cacheable: false,
	        allowChangeHistory: false,
	        width: 900,
	        events: {
	          onClose: function onClose(event) {
	            var slider = event.getSlider();

	            if (slider) {
	              var userFieldData = slider.getData().get('userFieldData');

	              if (userFieldData) {
	                var userField = ui_userfield.UserField.unserialize(userFieldData);

	                _this.emit('onCreateCustomUserField', {
	                  userField: userField
	                });
	              }
	            }
	          }
	        }
	      });
	    }
	  }]);
	  return Factory;
	}();

	exports.Factory = Factory;
	exports.FieldTypes = FieldTypes;
	exports.Configurator = Configurator;

}((this.BX.UI.UserFieldFactory = this.BX.UI.UserFieldFactory || {}),BX.Event,BX.Main,BX,BX.UI.UserField));
//# sourceMappingURL=userfieldfactory.bundle.js.map
