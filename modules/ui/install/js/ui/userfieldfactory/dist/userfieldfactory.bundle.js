this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_events,main_popup,main_core) {
	'use strict';

	var MAX_FIELD_LENGTH = 20;
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var FieldTypes = Object.freeze({
	  string: 'string',
	  enumeration: 'enumeration',
	  date: 'date',
	  datetime: 'datetime',
	  address: 'address',
	  url: 'url',
	  file: 'file',
	  money: 'money',
	  boolean: 'boolean',
	  double: 'double',
	  employee: 'employee',
	  crm: 'crm',
	  crmStatus: 'crm_status'
	});
	var FieldDescriptions = Object.freeze({
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
	  boolean: {
	    title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_BOOLEAN_TITLE"),
	    description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_BOOLEAN_LEGEND")
	  },
	  double: {
	    title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_TITLE"),
	    description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_DOUBLE_LEGEND"),
	    defaultTitle: main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_DOUBLE_LABEL')
	  },
	  employee: {
	    title: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_TITLE"),
	    description: main_core.Loc.getMessage("UI_USERFIELD_FACTORY_UF_EMPLOYEE_LEGEND")
	  }
	});
	var DefaultData = Object.freeze({
	  MULTIPLE: 'N',
	  MANDATORY: 'N',
	  USER_TYPE_ID: FieldTypes.string,
	  SHOW_FILTER: 'E',
	  SHOW_IN_LIST: 'Y',
	  SETTINGS: {},
	  IS_SEARCHABLE: 'N'
	});
	var DefaultFieldData = Object.freeze({
	  file: {
	    SHOW_FILTER: 'N',
	    SHOW_IN_LIST: 'N'
	  },
	  employee: {
	    SHOW_FILTER: 'I'
	  },
	  crm: {
	    SHOW_FILTER: 'I'
	  },
	  crm_status: {
	    SHOW_FILTER: 'I'
	  },
	  enumeration: {
	    SETTINGS: {
	      DISPLAY: 'UI'
	    }
	  },
	  double: {
	    SETTINGS: {
	      PRECISION: 2
	    }
	  }
	});

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-item\" onclick=\"", "\">\n\t\t\t<div class=\"ui-userfieldfactory-creation-menu-item-title\">", "</div>\n\t\t\t<div class=\"ui-userfieldfactory-creation-menu-item-desc\">", "</div>\n\t\t</div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-list\"></div>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-scroll-bottom\">", "</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-scroll-top\">", "</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-creation-menu-container\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var CreationMenu =
	/*#__PURE__*/
	function () {
	  function CreationMenu(id, types, params) {
	    babelHelpers.classCallCheck(this, CreationMenu);
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
	        var options = babelHelpers.objectSpread({}, CreationMenu.getDefaultPopupOptions(), this.params);
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
	        this.container = main_core.Tag.render(_templateObject());
	        var scrollIcon = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"42\" height=\"13\" viewBox=\"0 0 42 13\">\n" + "  <polyline fill=\"none\" stroke=\"#CACDD1\" stroke-width=\"2\" points=\"274 98 284 78.614 274 59\" transform=\"rotate(90 186 -86.5)\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>\n" + "</svg>\n";
	        this.topScrollButton = main_core.Tag.render(_templateObject2(), scrollIcon);
	        this.bottomScrollButton = main_core.Tag.render(_templateObject3(), scrollIcon);
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
	        this.containerList = main_core.Tag.render(_templateObject4());
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

	      return main_core.Tag.render(_templateObject5(), function () {
	        _this2.onItemClick(item, onClick);
	      }, item.title, item.description);
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(item, onClick) {
	      if (main_core.Type.isFunction(onClick)) {
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
	      if (this._enableScrollToBottom) {
	        return;
	      }

	      this._enableScrollToBottom = true;
	      this._enableScrollToTop = false;
	      (function scroll() {
	        if (!this._enableScrollToBottom) {
	          return;
	        }

	        if (this.containerList.scrollTop + this.containerList.offsetHeight !== this.containerList.scrollHeight) {
	          this.containerList.scrollTop += 3;
	        }

	        if (this.containerList.scrollTop + this.containerList.offsetHeight === this.containerList.scrollHeight) {
	          this._enableScrollToBottom = false;
	        } else {
	          window.setTimeout(scroll.bind(this), 20);
	        }
	      }).bind(this)();
	    }
	  }, {
	    key: "onBottomButtonMouseOut",
	    value: function onBottomButtonMouseOut() {
	      this._enableScrollToBottom = false;
	    }
	  }, {
	    key: "onTopButtonMouseOver",
	    value: function onTopButtonMouseOver() {
	      if (this._enableScrollToTop) {
	        return;
	      }

	      this._enableScrollToBottom = false;
	      this._enableScrollToTop = true;
	      (function scroll() {
	        if (!this._enableScrollToTop) {
	          return;
	        }

	        if (this.containerList.scrollTop > 0) {
	          this.containerList.scrollTop -= 3;
	        }

	        if (this.containerList.scrollTop === 0) {
	          this._enableScrollToTop = false;
	        } else {
	          window.setTimeout(scroll.bind(this), 20);
	        }
	      }).bind(this)();
	    }
	  }, {
	    key: "onTopButtonMouseOut",
	    value: function onTopButtonMouseOut() {
	      this._enableScrollToTop = false;
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
	        this.topScrollButton.classList.add('hidden');
	      } else {
	        this.topScrollButton.classList.remove('hidden');
	      }

	      if (scrollTop + height === scrollHeight) {
	        this.bottomScrollButton.classList.add('hidden');
	      } else {
	        this.bottomScrollButton.classList.remove('hidden');
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
	var EnumItem =
	/*#__PURE__*/
	function () {
	  function EnumItem() {
	    var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	    babelHelpers.classCallCheck(this, EnumItem);
	    this.value = value;
	  }

	  babelHelpers.createClass(EnumItem, [{
	    key: "setNode",
	    value: function setNode(node) {
	      this.node = node;
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

	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var Field =
	/*#__PURE__*/
	function () {
	  function Field(data) {
	    babelHelpers.classCallCheck(this, Field);
	    babelHelpers.defineProperty(this, "saved", false);
	    this.data = data;
	    var id = main_core.Text.toInteger(data.ID);

	    if (id > 0) {
	      this.saved = true;
	    }
	  }

	  babelHelpers.createClass(Field, [{
	    key: "setData",
	    value: function setData(data) {
	      delete data.SIGNATURE;
	      this.data = babelHelpers.objectSpread({}, this.data, data);
	      return this;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.data;
	    }
	  }, {
	    key: "markAsSaved",
	    value: function markAsSaved() {
	      this.saved = true;
	      return this;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.data.FIELD;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      if (!this.isSaved()) {
	        this.data.FIELD = name;
	      }

	      return this;
	    }
	  }, {
	    key: "getEntityId",
	    value: function getEntityId() {
	      return this.data.ENTITY_ID;
	    }
	  }, {
	    key: "getTypeId",
	    value: function getTypeId() {
	      return this.data.USER_TYPE_ID;
	    }
	  }, {
	    key: "getEnumeration",
	    value: function getEnumeration() {
	      if (!main_core.Type.isArray(this.data.ENUM)) {
	        this.data.ENUM = [];
	      }

	      return this.data.ENUM;
	    }
	  }, {
	    key: "saveEnumeration",
	    value: function saveEnumeration(items) {
	      var _this = this;

	      this.data.ENUM = [];
	      var sort = 100;
	      items.forEach(function (item) {
	        _this.data.ENUM.push({
	          VALUE: item.getValue(),
	          SORT: sort
	        });

	        sort += 100;
	      });
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      var titleFields = Field.getTitleFields();
	      var titleFieldsCount = titleFields.length;

	      for (var index = 0; index < titleFieldsCount; index++) {
	        if (main_core.Type.isString(this.data[titleFields[index]]) && this.data[titleFields[index]].length > 0) {
	          return this.data[titleFields[index]];
	        }
	      }

	      return this.getName();
	    }
	  }, {
	    key: "setTitle",
	    value: function setTitle(title) {
	      var _this2 = this;

	      if (main_core.Type.isString(title) && title.length > 0) {
	        Field.getTitleFields().forEach(function (label) {
	          _this2.data[label] = title;
	        });

	        if (this.getTypeId() === FieldTypes.boolean) {
	          this.data.SETTINGS.LABEL_CHECKBOX = title;
	        }
	      }
	    }
	  }, {
	    key: "isSaved",
	    value: function isSaved() {
	      return this.saved;
	    }
	  }, {
	    key: "isMultiple",
	    value: function isMultiple() {
	      return this.data.MULTIPLE === 'Y';
	    }
	  }, {
	    key: "setIsMultiple",
	    value: function setIsMultiple(isMultiple) {
	      if (!this.isSaved()) {
	        this.data.MULTIPLE = main_core.Text.toBoolean(isMultiple) === true ? 'Y' : 'N';
	      }
	    }
	  }, {
	    key: "isDateField",
	    value: function isDateField() {
	      return this.getTypeId() === FieldTypes.datetime || this.getTypeId() === FieldTypes.date;
	    }
	  }, {
	    key: "isShowTime",
	    value: function isShowTime() {
	      return this.getTypeId() === FieldTypes.datetime;
	    }
	  }, {
	    key: "setIsShowTime",
	    value: function setIsShowTime(isShowTime) {
	      if (!this.isSaved()) {
	        isShowTime = main_core.Text.toBoolean(isShowTime);

	        if (isShowTime) {
	          this.data.USER_TYPE_ID = FieldTypes.datetime;
	        } else {
	          this.data.USER_TYPE_ID = FieldTypes.date;
	        }
	      }
	    }
	  }, {
	    key: "isSearchable",
	    value: function isSearchable() {
	      return this.data.IS_SEARCHABLE === 'Y';
	    }
	  }, {
	    key: "setIsSearchable",
	    value: function setIsSearchable(isSearchable) {
	      this.data.IS_SEARCHABLE = main_core.Text.toBoolean(isSearchable) === true ? 'Y' : 'N';
	    }
	  }], [{
	    key: "getTitleFields",
	    value: function getTitleFields() {
	      return Array.from(['EDIT_FORM_LABEL', 'LIST_COLUMN_LABEL', 'LIST_FILTER_LABEL']);
	    }
	  }]);
	  return Field;
	}();

	function _templateObject15() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t</div>"]);

	  _templateObject15 = function _templateObject15() {
	    return data;
	  };

	  return data;
	}

	function _templateObject14() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"checkbox\">"]);

	  _templateObject14 = function _templateObject14() {
	    return data;
	  };

	  return data;
	}

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-xs\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t</div>"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"checkbox\">"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\"></div>"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div style=\"margin-bottom: 10px;\" class=\"ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row\">\n\t\t\t<input class=\"ui-ctl-element\" type=\"text\" value=\"", "\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-remove-enum\" onclick=\"", "\"></div>\n\t\t</div>"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-title\">\n\t\t\t\t<span class=\"ui-userfieldfactory-configurator-title-text\">", "</span>\n\t\t\t</div>\n\t\t\t", "\n\t\t\t", "\n\t\t</div>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block-add-field\">\n\t\t\t<span class=\"ui-userfieldfactory-configurator-add-button\" onclick=\"", "\">", "</span>\n\t\t</div>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t", "", "\n\t\t</div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-btn ui-btn-light-border\" onclick=\"", "\">", "</span>"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-btn ui-btn-primary\" onclick=\"", "\">", "</span>"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator-block\">\n\t\t\t<div class=\"ui-userfieldfactory-configurator-title\">\n\t\t\t\t<span class=\"ui-userfieldfactory-configurator-title-text\">", "</span>\n\t\t\t</div>\n\t\t\t<div class=\"ui-userfieldfactory-configurator-content\">\n\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input class=\"ui-ctl-element\" type=\"text\" value=\"", "\" />"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-userfieldfactory-configurator\"></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberof BX.UI.UserFieldFactory
	 */

	var Configurator =
	/*#__PURE__*/
	function () {
	  function Configurator(params) {
	    babelHelpers.classCallCheck(this, Configurator);

	    if (main_core.Type.isPlainObject(params)) {
	      if (params.field instanceof Field) {
	        this.field = params.field;
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

	      this.node = main_core.Tag.render(_templateObject$1());
	      this.labelInput = main_core.Tag.render(_templateObject2$1(), this.field.getTitle());
	      this.node.appendChild(main_core.Tag.render(_templateObject3$1(), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_CONFIGURATOR_FIELD_TITLE'), this.labelInput));

	      if (this.field.getTypeId() === FieldTypes.enumeration) {
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

	      this.saveButton = main_core.Tag.render(_templateObject4$1(), save.bind(this), main_core.Loc.getMessage('UI_USERFIELD_SAVE'));
	      this.cancelButton = main_core.Tag.render(_templateObject5$1(), cancel.bind(this), main_core.Loc.getMessage('UI_USERFIELD_CANCEL'));
	      this.node.appendChild(main_core.Tag.render(_templateObject6(), this.saveButton, this.cancelButton));
	      return this.node;
	    }
	  }, {
	    key: "saveField",
	    value: function saveField() {
	      if (this.timeCheckbox) {
	        this.field.setIsShowTime(this.timeCheckbox.checked);
	      }

	      if (this.multipleCheckbox) {
	        this.field.setIsMultiple(this.multipleCheckbox.checked);
	      }

	      this.field.setTitle(this.labelInput.value);
	      this.field.saveEnumeration(this.enumItems);
	      return this.field;
	    }
	  }, {
	    key: "renderEnumeration",
	    value: function renderEnumeration() {
	      var _this2 = this;

	      this.enumItemsContainer = main_core.Tag.render(_templateObject7());
	      this.enumAddItemContainer = main_core.Tag.render(_templateObject8(), function () {
	        _this2.addEnumInput().focus();
	      }, main_core.Loc.getMessage('UI_USERFIELD_ADD'));
	      this.enumContainer = main_core.Tag.render(_templateObject9(), main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENUM_ITEMS'), this.enumItemsContainer, this.enumAddItemContainer);
	      this.field.getEnumeration().forEach(function (item) {
	        _this2.addEnumInput(item);
	      });
	      this.addEnumInput();
	      return this.enumContainer;
	    }
	  }, {
	    key: "addEnumInput",
	    value: function addEnumInput() {
	      var _this3 = this;

	      var item = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

	      if (!(item instanceof EnumItem)) {
	        if (main_core.Type.isPlainObject(item)) {
	          item = new EnumItem(item.VALUE);
	        } else {
	          item = new EnumItem();
	        }
	      }

	      var node = main_core.Tag.render(_templateObject10(), item.getValue(), function (event) {
	        event.preventDefault();

	        _this3.deleteEnumItem(item);
	      });
	      item.setNode(node);
	      this.enumItems.add(item);
	      this.enumItemsContainer.appendChild(node);
	      return node;
	    }
	  }, {
	    key: "deleteEnumItem",
	    value: function deleteEnumItem(item) {
	      this.enumItemsContainer.removeChild(item.getNode());
	      this.enumItems.delete(item);
	    }
	  }, {
	    key: "renderOptions",
	    value: function renderOptions() {
	      this.optionsContainer = main_core.Tag.render(_templateObject11());

	      if (!this.field.isSaved() && this.field.isDateField()) {
	        this.timeCheckbox = main_core.Tag.render(_templateObject12());
	        this.timeCheckbox.checked = this.field.isShowTime();
	        this.optionsContainer.appendChild(main_core.Tag.render(_templateObject13(), this.timeCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_UF_ENABLE_TIME')));
	      }

	      if (!this.field.isSaved() && this.field.getTypeId() !== FieldTypes.boolean) {
	        this.multipleCheckbox = main_core.Tag.render(_templateObject14());
	        this.multipleCheckbox.checked = this.field.isMultiple();
	        this.optionsContainer.appendChild(main_core.Tag.render(_templateObject15(), this.multipleCheckbox, main_core.Loc.getMessage('UI_USERFIELD_FACTORY_FIELD_MULTIPLE')));
	      }

	      return this.optionsContainer;
	    }
	  }]);
	  return Configurator;
	}();

	/**
	 * @memberof BX.UI.UserFieldFactory
	 * @mixes EventEmitter
	 */

	var Factory =
	/*#__PURE__*/
	function () {
	  function Factory(entityId) {
	    var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Factory);
	    main_core_events.EventEmitter.makeObservable(this, 'UI.UserFieldFactory.Factory');
	    this.configuratorClass = Configurator;

	    if (main_core.Type.isString(entityId) && entityId.length > 0) {
	      this.entityId = entityId;
	    }

	    if (main_core.Type.isPlainObject(params)) {
	      if (main_core.Type.isString(params.creationSignature)) {
	        this.creationSignature = params.creationSignature;
	      }

	      if (main_core.Type.isString(params.menuId)) {
	        this.menuId = params.menuId;
	      }

	      if (!main_core.Type.isArray(params.types)) {
	        params.types = [];
	      }

	      if (main_core.Type.isDomNode(params.bindElement)) {
	        this.bindElement = params.bindElement;
	      }

	      this.setConfiguratorClass(params.configuratorClass);
	    } else {
	      params.types = [];
	    }

	    this.types = this.getFieldTypes().concat(params.types);
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "getFieldTypes",
	    value: function getFieldTypes() {
	      var types = [];
	      Object.keys(FieldDescriptions).forEach(function (name) {
	        types.push(babelHelpers.objectSpread({}, FieldDescriptions[name], {
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

	      if (!this.menu) {
	        this.menu = new CreationMenu(this.menuId, this.types, params);
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
	    key: "getConfigurator",
	    value: function getConfigurator(params) {
	      return new this.configuratorClass(params);
	    }
	  }, {
	    key: "createField",
	    value: function createField(fieldType, fieldName) {
	      var data = babelHelpers.objectSpread({}, DefaultData, DefaultFieldData[fieldType], {
	        USER_TYPE_ID: fieldType
	      });

	      if (!main_core.Type.isString(fieldName) || fieldName.length <= 0 || fieldName.length > MAX_FIELD_LENGTH) {
	        fieldName = this.generateFieldName();
	      }

	      data.FIELD = fieldName;
	      data.ENTITY_ID = this.entityId;
	      data.SIGNATURE = this.creationSignature;
	      var field = new Field(data);
	      field.setTitle(this.getDefaultLabel(fieldType));
	      this.emit('onCreateField', {
	        field: field
	      });
	      return field;
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
	    key: "saveField",
	    value: function saveField(field) {
	      var _this = this;

	      return new Promise(function (resolve, reject) {
	        if (field instanceof Field) {
	          if (field.isSaved()) {
	            _this.getEditManager().update({
	              "FIELDS": [field.getData()]
	            }, function (response) {
	              _this.onFieldSave(field, response, resolve, reject);
	            });
	          } else {
	            _this.getEditManager().add({
	              "FIELDS": [field.getData()]
	            }, function (response) {
	              _this.onFieldSave(field, response, resolve, reject);
	            });
	          }
	        } else {
	          reject(['Wrong parameter: field must be instance of Field']);
	        }
	      });
	    }
	  }, {
	    key: "deleteField",
	    value: function deleteField(field) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (field instanceof Field) {
	          if (field.isSaved()) {
	            _this2.getEditManager().delete({
	              "FIELDS": [field.getData()]
	            }, function (response) {
	              _this2.onFieldDelete(field, response, resolve, reject);
	            });
	          }
	        } else {
	          reject(['Wrong parameter: field must be instance of Field']);
	        }
	      });
	    }
	  }, {
	    key: "onFieldSave",
	    value: function onFieldSave(field, response, onSuccess, onError) {
	      if (main_core.Type.isPlainObject(response)) {
	        if (response.ERROR && main_core.Type.isArray(response.ERROR) && response.ERROR.length > 0) {
	          onError(response.ERROR);
	        } else {
	          var fieldData = this.getFieldDataFromResponse(response);

	          if (fieldData) {
	            field.markAsSaved().setData(fieldData);

	            if (main_core.Type.isFunction(onSuccess)) {
	              onSuccess(field);
	            }

	            this.emit('onFieldSave', {
	              field: field
	            });
	          }
	        }
	      } else {
	        if (main_core.Type.isFunction(onError)) {
	          if (main_core.Type.isArray(this.managerErrors) && this.managerErrors.length > 0) {
	            onError(this.managerErrors);
	            this.managerErrors = [];
	          } else {
	            onError([main_core.Loc.getMessage('UI_USERFIELD_SAVE_ERROR')]);
	          }
	        }
	      }
	    }
	  }, {
	    key: "onFieldDelete",
	    value: function onFieldDelete(field, response, onSuccess, onError) {
	      if (main_core.Type.isPlainObject(response) || main_core.Type.isArray(response)) {
	        if (main_core.Type.isPlainObject(response) && response.ERROR && main_core.Type.isArray(response.ERROR) && response.ERROR.length > 0) {
	          onError(response.ERROR);
	        } else {
	          if (main_core.Type.isFunction(onSuccess)) {
	            onSuccess(field);
	          }

	          this.emit('onFieldDelete', {
	            field: field
	          });
	        }
	      } else {
	        if (main_core.Type.isFunction(onError)) {
	          if (main_core.Type.isArray(this.managerErrors) && this.managerErrors.length > 0) {
	            onError(this.managerErrors);
	            this.managerErrors = [];
	          } else {
	            onError([main_core.Loc.getMessage('UI_USERFIELD_DELETE_ERROR')]);
	          }
	        }
	      }
	    }
	  }, {
	    key: "getFieldDataFromResponse",
	    value: function getFieldDataFromResponse(response) {
	      if (main_core.Type.isPlainObject(response)) {
	        var fieldData = null;
	        Object.keys(response).forEach(function (fieldName) {
	          if (main_core.Type.isPlainObject(response[fieldName]['FIELD'])) {
	            fieldData = response[fieldName]['FIELD'];
	          }
	        });
	        return fieldData;
	      }

	      return null;
	    }
	  }, {
	    key: "getEditManager",
	    value: function getEditManager() {
	      var _this3 = this;

	      if (!this.editManager) {
	        this.editManager = BX.Main.UF.EditManager;

	        this.editManager.displayError = function (errors) {
	          _this3.managerErrors = errors;
	        };
	      }

	      return this.editManager;
	    }
	  }]);
	  return Factory;
	}();

	exports.Factory = Factory;
	exports.FieldTypes = FieldTypes;
	exports.Field = Field;
	exports.Configurator = Configurator;

}((this.BX.UI.UserFieldFactory = this.BX.UI.UserFieldFactory || {}),BX.Event,BX.Main,BX));
//# sourceMappingURL=userfieldfactory.bundle.js.map
