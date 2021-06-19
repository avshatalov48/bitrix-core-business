this.BX = this.BX || {};
(function (exports,main_core,main_date,main_popup,main_core_events) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div data-bx-resource-data-wrap=\"Y\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ViewControlAbstract = /*#__PURE__*/function () {
	  function ViewControlAbstract(params) {
	    babelHelpers.classCallCheck(this, ViewControlAbstract);

	    if ((this instanceof ViewControlAbstract ? this.constructor : void 0) === ViewControlAbstract) {
	      throw new TypeError("Cannot construct Abstract instances directly");
	    }

	    this.name = null;
	    this.classNames = {
	      wrap: params.wrapClassName || 'calendar-resbook-webform-block',
	      innerWrap: 'calendar-resbook-webform-block-inner',
	      title: 'calendar-resbook-webform-block-title',
	      field: 'calendar-resbook-webform-block-field'
	    };
	    this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: null,
	      dataWrap: null,
	      innerWrap: null,
	      labelWrap: null
	    };
	    this.data = params.data;
	    this.shown = false;
	  }

	  babelHelpers.createClass(ViewControlAbstract, [{
	    key: "isDisplayed",
	    value: function isDisplayed() {
	      return this.data.show !== 'N';
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.shown;
	    }
	  }, {
	    key: "display",
	    value: function display() {
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: this.classNames.wrap
	        }
	      }));
	      this.DOM.dataWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject()));

	      if (this.isDisplayed()) {
	        this.show({
	          animation: false
	        });
	      }
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data) {
	      this.refreshLabel(data);
	      this.data = data;

	      if (this.setDataConfig()) {
	        if (this.isDisplayed()) {
	          this.show({
	            animation: true
	          });
	        } else {
	          this.hide({
	            animation: true
	          });
	        }
	      }

	      this.data = data;
	    }
	  }, {
	    key: "setDataConfig",
	    value: function setDataConfig() {
	      return true;
	    }
	  }, {
	    key: "refreshLabel",
	    value: function refreshLabel(data) {
	      if (this.data.label !== data.label) {
	        main_core.Dom.adjust(this.DOM.labelWrap, {
	          text: data.label
	        });
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.DOM.innerWrap) {
	        this.hide();
	      }

	      this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: this.classNames.innerWrap
	        }
	      }));

	      if (this.data.label || this.label) {
	        this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	          props: {
	            className: this.classNames.title
	          },
	          text: this.data.label || this.label
	        }));
	      }

	      this.DOM.controlWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: this.classNames.field
	        }
	      }));
	      this.displayControl();
	      this.shown = true;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.remove(this.DOM.innerWrap);
	      this.DOM.innerWrap = null;
	      this.shown = false;
	    }
	  }, {
	    key: "displayControl",
	    value: function displayControl() {}
	  }, {
	    key: "showWarning",
	    value: function showWarning(errorMessage) {
	      if (this.shown && this.DOM.wrap && this.DOM.innerWrap) {
	        main_core.Dom.addClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
	        this.displayErrorText(errorMessage || main_core.Loc.getMessage('WEBF_RES_BOOKING_REQUIRED_WARNING'));
	      }
	    }
	  }, {
	    key: "hideWarning",
	    value: function hideWarning() {
	      if (this.DOM.wrap) {
	        main_core.Dom.removeClass(this.DOM.wrap, "calendar-resbook-webform-block-error");

	        if (this.DOM.errorTextWrap) {
	          main_core.Dom.remove(this.DOM.errorTextWrap);
	        }
	      }
	    }
	  }, {
	    key: "displayErrorText",
	    value: function displayErrorText(errorMessage) {
	      if (this.DOM.errorTextWrap) {
	        main_core.Dom.remove(this.DOM.errorTextWrap);
	      }

	      this.DOM.errorTextWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-error-text'
	        },
	        text: errorMessage
	      }));
	    }
	  }]);
	  return ViewControlAbstract;
	}();

	var ViewDropDownSelect = /*#__PURE__*/function () {
	  function ViewDropDownSelect(params) {
	    babelHelpers.classCallCheck(this, ViewDropDownSelect);
	    this.id = 'viewform-dropdown-select-' + Math.round(Math.random() * 100000);
	    this.DOM = {
	      wrap: params.wrap
	    };
	    this.maxHeight = params.maxHeight;
	    this.selectAllMessage = main_core.Loc.getMessage('WEBF_RES_SELECT_ALL');
	    this.setSettings(params);
	  }

	  babelHelpers.createClass(ViewDropDownSelect, [{
	    key: "build",
	    value: function build() {
	      this.DOM.select = this.DOM.wrap.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: "calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown"
	        },
	        events: {
	          click: this.openPopup.bind(this)
	        }
	      }));
	      this.setSelectedValues(this.selected);
	    }
	  }, {
	    key: "setSettings",
	    value: function setSettings(params) {
	      this.handleChangesCallback = main_core.Type.isFunction(params.handleChangesCallback) ? params.handleChangesCallback : null;
	      this.values = params.values;
	      this.selected = !main_core.Type.isArray(params.selected) ? [params.selected] : params.selected;
	      this.multiple = params.multiple;
	    }
	  }, {
	    key: "openPopup",
	    value: function openPopup() {
	      if (this.isPopupShown()) {
	        return this.closePopup();
	      }

	      var menuItems = [];
	      this.values.forEach(function (item) {
	        var className = 'menu-popup-no-icon';

	        if (main_core.Type.isArray(this.selected) && this.selected.includes(parseInt(item.id))) {
	          className += ' menu-item-selected';
	        }

	        menuItems.push({
	          id: item.id,
	          className: className,
	          text: main_core.Text.encode(item.title),
	          onclick: this.menuItemClick.bind(this)
	        });
	      }, this);

	      if (this.multiple && menuItems.length <= 1) {
	        this.multiple = false;
	      }

	      if (this.multiple) {
	        menuItems.push({
	          id: 'select-all',
	          text: this.selectAllMessage,
	          onclick: this.selectAllItemClick.bind(this)
	        });
	      }

	      this.popup = main_popup.MenuManager.create(this.id, this.DOM.select, menuItems, {
	        className: 'calendar-resbook-form-popup' + (this.multiple ? ' popup-window-resource-select' : ''),
	        closeByEsc: true,
	        autoHide: !this.multiple,
	        offsetTop: 0,
	        offsetLeft: 0,
	        cacheable: false
	      });
	      this.popup.show(true);
	      this.popupContainer = this.popup.popupWindow.popupContainer;
	      this.popupContainer.style.width = parseInt(this.DOM.select.offsetWidth) + 'px';

	      if (this.multiple) {
	        this.popup.menuItems.forEach(function (menuItem) {
	          var checked;

	          if (menuItem.id === 'select-all') {
	            this.selectAllChecked = !this.values.find(function (value) {
	              return !this.selected.find(function (itemId) {
	                return itemId === value.id;
	              });
	            }, this);
	            menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
	            menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' + '</div>' + '</div>';
	          } else {
	            checked = this.selected.find(function (itemId) {
	              return itemId === menuItem.id;
	            });
	            menuItem.layout.item.className = 'menu-popup-item';
	            menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' + '</div>' + '</div>';
	          }
	        }, this);
	        main_core.Event.unbind(document, 'click', this.handleClick.bind(this));
	        setTimeout(function () {
	          main_core.Event.bind(document, 'click', this.handleClick.bind(this));
	        }.bind(this), 50);
	      }
	    }
	  }, {
	    key: "closePopup",
	    value: function closePopup() {
	      if (this.isPopupShown()) {
	        this.popup.close();

	        if (this.multiple) {
	          main_core.Event.unbind(document, 'click', this.handleClick.bind(this));
	        }
	      }
	    }
	  }, {
	    key: "isPopupShown",
	    value: function isPopupShown() {
	      return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() && this.popup.popupWindow.popupContainer && main_core.Dom.isShown(this.popup.popupWindow.popupContainer);
	    }
	  }, {
	    key: "menuItemClick",
	    value: function menuItemClick(e, menuItem) {
	      var selectAllcheckbox,
	          target = e.target || e.srcElement,
	          foundValue,
	          checkbox;

	      if (this.multiple) {
	        foundValue = this.values.find(function (value) {
	          return value.id == menuItem.id;
	        });
	        checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

	        if (foundValue && target && (main_core.Dom.hasClass(target, "menu-popup-item") || main_core.Dom.hasClass(target, "menu-popup-item-resource-checkbox") || main_core.Dom.hasClass(target, "menu-popup-item-inner"))) {
	          if (!main_core.Dom.hasClass(target, "menu-popup-item-resource-checkbox")) {
	            checkbox.checked = !checkbox.checked;
	          }

	          if (checkbox.checked) {
	            this.selectItem(foundValue);
	          } else {
	            this.deselectItem(foundValue);
	            selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
	            this.selectAllChecked = false;

	            if (selectAllcheckbox) {
	              selectAllcheckbox.checked = false;
	            }
	          }

	          this.setSelectedValues(this.selected);
	          this.handleControlChanges();
	        }
	      } else {
	        this.setSelectedValues([menuItem.id]);
	        this.handleControlChanges();
	        this.closePopup();
	      }
	    }
	  }, {
	    key: "selectItem",
	    value: function selectItem(value) {
	      if (!this.selected.includes(value.id)) {
	        this.selected.push(value.id);
	      }
	    }
	  }, {
	    key: "deselectItem",
	    value: function deselectItem(value) {
	      var index = this.selected.indexOf(parseInt(value.id));

	      if (index >= 0) {
	        this.selected = this.selected.slice(0, index).concat(this.selected.slice(index + 1));
	      }
	    }
	  }, {
	    key: "selectAllItemClick",
	    value: function selectAllItemClick(e, menuItem) {
	      var target = e.target || e.srcElement;

	      if (target && (main_core.Dom.hasClass(target, "menu-popup-item") || main_core.Dom.hasClass(target, "menu-popup-item-resource-checkbox"))) {
	        var checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');

	        if (main_core.Dom.hasClass(target, "menu-popup-item")) {
	          checkbox.checked = !checkbox.checked;
	        }

	        var i,
	            checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
	        this.selectAllChecked = checkbox.checked;

	        for (i = 0; i < checkboxes.length; i++) {
	          checkboxes[i].checked = this.selectAllChecked;
	        }

	        this.selected = [];

	        if (this.selectAllChecked) {
	          this.values.forEach(function (value) {
	            this.selected.push(value.id);
	          }, this);
	        }

	        this.setSelectedValues(this.selected);
	        this.handleControlChanges();
	      }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick(e) {
	      if (this.isPopupShown() && !this.popupContainer.contains(e.target || e.srcElement)) {
	        this.closePopup({
	          animation: true
	        });
	      }

	      this.handleControlChanges();
	    }
	  }, {
	    key: "getSelectedValues",
	    value: function getSelectedValues() {
	      return this.selected;
	    }
	  }, {
	    key: "setSelectedValues",
	    value: function setSelectedValues(values) {
	      var i,
	          foundValue,
	          textValues = [],
	          selectedValues = [];

	      for (i = 0; i < values.length; i++) {
	        foundValue = this.values.find(function (value) {
	          return value.id === values[i];
	        });

	        if (foundValue) {
	          textValues.push(foundValue.title);
	          selectedValues.push(foundValue.id);
	        }
	      }

	      this.selected = selectedValues;
	      main_core.Dom.adjust(this.DOM.select, {
	        text: textValues.length ? textValues.join(', ') : main_core.Loc.getMessage('USER_TYPE_RESOURCE_LIST_PLACEHOLDER')
	      });
	    }
	  }, {
	    key: "handleControlChanges",
	    value: function handleControlChanges() {
	      if (this.handleChangesCallback) {
	        this.handleChangesCallback(this.getSelectedValues());
	      }
	    }
	  }]);
	  return ViewDropDownSelect;
	}();

	var UserSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(UserSelector, _ViewControlAbstract);

	  function UserSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, UserSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelector).call(this, params));
	    _this.name = 'UserSelector';
	    _this.data = params.data || {};
	    _this.userList = [];
	    _this.userIndex = {};
	    _this.values = [];
	    _this.defaultMode = 'auto';
	    _this.previewMode = params.previewMode === undefined;
	    _this.autoSelectDefaultValue = params.autoSelectDefaultValue;
	    _this.changeValueCallback = params.changeValueCallback;

	    _this.handleSettingsData(_this.data, params.userIndex);

	    return _this;
	  }

	  babelHelpers.createClass(UserSelector, [{
	    key: "displayControl",
	    value: function displayControl() {
	      this.selectedValue = this.getSelectedUser();
	      this.dropdownSelect = new ViewDropDownSelect({
	        wrap: this.DOM.controlWrap,
	        values: this.userList,
	        selected: this.selectedValue,
	        handleChangesCallback: this.handleChanges.bind(this)
	      });
	      this.dropdownSelect.build();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data, userIndex) {
	      this.refreshLabel(data);
	      this.data = data;
	      this.handleSettingsData(this.data, userIndex);
	      this.selectedValue = this.getSelectedUser();

	      if (this.dropdownSelect) {
	        this.dropdownSelect.setSettings({
	          values: this.userList,
	          selected: this.selectedValue
	        });
	      }

	      if (this.setDataConfig()) {
	        if (this.isDisplayed()) {
	          this.show({
	            animation: true
	          });
	        } else {
	          this.hide({
	            animation: true
	          });
	        }
	      }
	    }
	  }, {
	    key: "handleSettingsData",
	    value: function handleSettingsData(data, userIndex) {
	      if (main_core.Type.isPlainObject(userIndex)) {
	        for (var id in userIndex) {
	          if (userIndex.hasOwnProperty(id)) {
	            this.userIndex[id] = userIndex[id];
	          }
	        }
	      }

	      this.defaultMode = this.data.defaultMode === 'none' ? 'none' : 'auto';
	      var dataValue = [];
	      this.userList = [];

	      if (this.data.value) {
	        var dataValueRaw = main_core.Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
	        dataValueRaw.forEach(function (id) {
	          id = parseInt(id);

	          if (id > 0) {
	            dataValue.push(id);

	            if (this.userIndex[id]) {
	              this.userList.push({
	                id: id,
	                title: this.userIndex[id].displayName
	              });
	            }
	          }
	        }, this);
	      }

	      this.values = dataValue;
	    }
	  }, {
	    key: "getSelectedUser",
	    value: function getSelectedUser() {
	      var selected = null;

	      if (this.dropdownSelect) {
	        selected = this.dropdownSelect.getSelectedValues();
	        selected = main_core.Type.isArray(selected) && selected.length ? selected[0] : null;
	      }

	      if (!selected && this.previewMode && this.data.defaultMode === 'auto' && this.userList && this.userList[0]) {
	        selected = this.userList[0].id;
	      }

	      if (!selected && this.autoSelectDefaultValue) {
	        selected = this.autoSelectDefaultValue;
	      }

	      return selected;
	    }
	  }, {
	    key: "setSelectedUser",
	    value: function setSelectedUser(userId) {
	      if (this.dropdownSelect) {
	        this.dropdownSelect.setSelectedValues([userId]);
	      } else {
	        this.autoSelectDefaultValue = parseInt(userId);
	      }
	    }
	  }, {
	    key: "handleChanges",
	    value: function handleChanges(selectedValues) {
	      if (!this.previewMode && main_core.Type.isFunction(this.changeValueCallback)) {
	        this.changeValueCallback(selectedValues[0] || null);
	      }
	    }
	  }]);
	  return UserSelector;
	}(ViewControlAbstract);

	var ResourceSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(ResourceSelector, _ViewControlAbstract);

	  function ResourceSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, ResourceSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ResourceSelector).call(this, params));
	    _this.name = 'ResourceSelector';
	    _this.data = params.data;
	    _this.allResourceList = params.resourceList;
	    _this.autoSelectDefaultValue = params.autoSelectDefaultValue;
	    _this.changeValueCallback = params.changeValueCallback;

	    _this.handleSettingsData(params.data);

	    return _this;
	  }

	  babelHelpers.createClass(ResourceSelector, [{
	    key: "handleSettingsData",
	    value: function handleSettingsData(data) {
	      if (!main_core.Type.isArray(data.value)) {
	        var dataValue = [];

	        if (data.value) {
	          data.value.split('|').forEach(function (id) {
	            if (parseInt(id) > 0) {
	              dataValue.push(parseInt(id));
	            }
	          });
	        }

	        this.data.value = dataValue;
	      }

	      this.resourceList = [];

	      if (main_core.Type.isArray(this.allResourceList) && main_core.Type.isArray(this.data.value)) {
	        this.allResourceList.forEach(function (item) {
	          if (this.data.value.includes(parseInt(item.id))) {
	            this.resourceList.push(item);
	          }
	        }, this);
	      }

	      this.setSelectedValues(this.getSelectedValues());
	    }
	  }, {
	    key: "displayControl",
	    value: function displayControl() {
	      this.dropdownSelect = new ViewDropDownSelect({
	        wrap: this.DOM.controlWrap,
	        values: this.resourceList,
	        selected: this.selectedValues,
	        multiple: this.data.multiple === 'Y',
	        handleChangesCallback: this.changeValueCallback
	      });
	      this.dropdownSelect.build();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data) {
	      this.refreshLabel(data);
	      this.data = data;
	      this.handleSettingsData(this.data);
	      this.setSelectedValues(this.getSelectedValues());

	      if (this.dropdownSelect) {
	        this.dropdownSelect.setSettings({
	          values: this.resourceList,
	          selected: this.selectedValues,
	          multiple: this.data.multiple === 'Y'
	        });
	      }

	      if (this.setDataConfig()) {
	        if (this.isDisplayed()) {
	          this.show({
	            animation: true
	          });
	        } else {
	          this.hide({
	            animation: true
	          });
	        }
	      }
	    }
	  }, {
	    key: "getSelectedValues",
	    value: function getSelectedValues() {
	      var selected = null;

	      if (this.dropdownSelect) {
	        selected = this.dropdownSelect.getSelectedValues();
	      }

	      if (!selected && this.autoSelectDefaultValue) {
	        selected = [this.autoSelectDefaultValue];
	      }

	      if (!selected && this.data.defaultMode === 'auto') {
	        if (this.resourceList && this.resourceList[0]) {
	          selected = [this.resourceList[0].id];
	        }
	      }

	      return selected;
	    }
	  }, {
	    key: "setSelectedValues",
	    value: function setSelectedValues(selectedValues) {
	      this.selectedValues = selectedValues;
	    }
	  }, {
	    key: "setSelectedResource",
	    value: function setSelectedResource(id) {
	      if (this.dropdownSelect) {
	        this.dropdownSelect.setSelectedValues([id]);
	      } else {
	        this.autoSelectDefaultValue = parseInt(id);
	        this.selectedValues = [id];
	      }
	    }
	  }]);
	  return ResourceSelector;
	}(ViewControlAbstract);

	var ServiceSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(ServiceSelector, _ViewControlAbstract);

	  function ServiceSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, ServiceSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ServiceSelector).call(this, params));
	    _this.name = 'ServiceSelector';
	    _this.data = params.data;
	    _this.serviceList = [];
	    _this.allServiceList = [];

	    if (main_core.Type.isArray(params.serviceList)) {
	      params.serviceList.forEach(function (service) {
	        if (main_core.Type.isString(name)) {
	          service.name = service.name.trim();
	        }

	        _this.allServiceList.push(service);
	      });
	    }

	    _this.values = [];
	    _this.changeValueCallback = main_core.Type.isFunction(params.changeValueCallback) ? params.changeValueCallback : null;

	    if (params.selectedValue) {
	      _this.setSelectedService(params.selectedValue);
	    }

	    _this.handleSettingsData(_this.data);

	    return _this;
	  }

	  babelHelpers.createClass(ServiceSelector, [{
	    key: "displayControl",
	    value: function displayControl() {
	      this.dropdownSelect = new ViewDropDownSelect({
	        wrap: this.DOM.controlWrap,
	        values: this.serviceList,
	        selected: this.getSelectedService(),
	        handleChangesCallback: function (selectedValues) {
	          if (selectedValues && selectedValues[0]) {
	            this.setSelectedService(selectedValues[0]);

	            if (this.changeValueCallback) {
	              this.changeValueCallback();
	            }
	          }
	        }.bind(this)
	      });
	      this.dropdownSelect.build();
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data) {
	      this.refreshLabel(data);
	      this.data = data;
	      this.handleSettingsData(this.data);

	      if (this.dropdownSelect) {
	        this.dropdownSelect.setSettings({
	          values: this.serviceList,
	          selected: this.getSelectedService()
	        });
	      }

	      if (this.setDataConfig()) {
	        if (this.isDisplayed()) {
	          this.show({
	            animation: true
	          });
	        } else {
	          this.hide({
	            animation: true
	          });
	        }
	      }
	    }
	  }, {
	    key: "handleSettingsData",
	    value: function handleSettingsData() {
	      this.serviceIndex = {};

	      if (main_core.Type.isArray(this.allServiceList)) {
	        this.allServiceList.forEach(function (service) {
	          if (main_core.Type.isPlainObject(service) && main_core.Type.isString(service.name) && service.name.trim() !== '') {
	            this.serviceIndex[this.prepareServiceId(service.name)] = service;
	          }
	        }, this);
	      }

	      this.serviceList = [];

	      if (this.data.value) {
	        var dataValueRaw = main_core.Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
	        dataValueRaw.forEach(function (id) {
	          var service = this.serviceIndex[this.prepareServiceId(id)];

	          if (main_core.Type.isPlainObject(service) && main_core.Type.isString(service.name) && service.name.trim() !== '') {
	            this.serviceList.push({
	              id: this.prepareServiceId(service.name),
	              title: service.name + ' - ' + BookingUtil$$1.getDurationLabel(service.duration)
	            });
	          }
	        }, this);
	      }
	    }
	  }, {
	    key: "setSelectedService",
	    value: function setSelectedService(serviceName) {
	      this.selectedService = serviceName;
	    }
	  }, {
	    key: "getSelectedService",
	    value: function getSelectedService(getMeta) {
	      return getMeta !== true ? this.selectedService || null : this.serviceIndex[this.prepareServiceId(this.selectedService)] || null;
	    }
	  }, {
	    key: "prepareServiceId",
	    value: function prepareServiceId(str) {
	      return BookingUtil$$1.translit(str);
	    }
	  }]);
	  return ServiceSelector;
	}(ViewControlAbstract);

	var DurationSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(DurationSelector, _ViewControlAbstract);

	  function DurationSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, DurationSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DurationSelector).call(this, params));
	    _this.name = 'DurationSelector';
	    _this.data = params.data;
	    _this.durationList = BookingUtil$$1.getDurationList(params.fullDay);
	    _this.changeValueCallback = params.changeValueCallback;
	    _this.defaultValue = params.defaultValue || _this.data.defaultValue;

	    _this.handleSettingsData(params.data);

	    return _this;
	  }

	  babelHelpers.createClass(DurationSelector, [{
	    key: "handleSettingsData",
	    value: function handleSettingsData() {
	      this.durationItems = [];

	      if (main_core.Type.isArray(this.durationList)) {
	        this.durationList.forEach(function (item) {
	          this.durationItems.push({
	            id: item.value,
	            title: item.label
	          });
	        }, this);
	      }
	    }
	  }, {
	    key: "displayControl",
	    value: function displayControl() {
	      this.DOM.durationInput = this.DOM.controlWrap.appendChild(main_core.Dom.create('INPUT', {
	        attrs: {
	          value: this.data.defaultValue || null,
	          type: 'text'
	        },
	        props: {
	          className: 'calendar-resbook-webform-block-input calendar-resbook-webform-block-input-dropdown'
	        }
	      }));
	      this.durationControl = new SelectInput$$1({
	        input: this.DOM.durationInput,
	        values: this.durationList,
	        value: this.data.defaultValue || null,
	        editable: this.data.manualInput === 'Y',
	        defaultValue: this.defaultValue,
	        setFirstIfNotFound: true,
	        onChangeCallback: this.changeValueCallback
	      });
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data) {
	      this.refreshLabel(data);
	      this.data = data;
	      this.handleSettingsData(this.data);

	      if (this.setDataConfig()) {
	        if (this.isDisplayed()) {
	          this.show({
	            animation: true
	          });

	          if (this.durationControl) {
	            this.durationControl.setValue(this.data.defaultValue || null);
	          }
	        } else {
	          this.hide({
	            animation: true
	          });
	        }
	      }
	    }
	  }, {
	    key: "getSelectedValue",
	    value: function getSelectedValue() {
	      var duration = null;

	      if (this.durationControl) {
	        duration = BookingUtil$$1.parseDuration(this.durationControl.getValue());
	      } else {
	        duration = parseInt(this.defaultValue);
	      }

	      return duration;
	    }
	  }]);
	  return DurationSelector;
	}(ViewControlAbstract);

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-arrow-container\" \n></div>"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" \nvalue=\"\"/>"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-date-range-inner-wrap\" \n></div>"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-date-range-static-wrap\" \n></div>"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next\" data-bx-resbook-date-meta=\"next\"/>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-strip-day\"/>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-strip-date\"/>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-strip-text\" data-bx-resbook-date-meta=\"calendar\"/>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev\" data-bx-resbook-date-meta=\"previous\"/>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input type=\"hidden\" \nvalue=\"\"/>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-date\"></div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-inner\"></div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block\"></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DateSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(DateSelector, _ViewControlAbstract);

	  function DateSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, DateSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateSelector).call(this, params));
	    _this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: null
	    };
	    _this.data = params.data || {};
	    _this.changeValueCallback = params.changeValueCallback;
	    _this.requestDataCallback = params.requestDataCallback;
	    _this.previewMode = params.previewMode === undefined;
	    _this.allowOverbooking = params.allowOverbooking;

	    _this.setDataConfig();

	    _this.displayed = true;
	    return _this;
	  }

	  babelHelpers.createClass(DateSelector, [{
	    key: "display",
	    value: function display(params) {
	      params = params || {};
	      this.setDateIndex(params.availableDateIndex);
	      this.setCurrentDate(params.selectedValue);
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$1()));
	      this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject2()));

	      if (this.data.label) {
	        this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	          props: {
	            className: 'calendar-resbook-webform-block-title'
	          },
	          text: this.data.label + '*'
	        }));
	      }

	      this.displayControl();
	      this.shown = true;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data, params) {
	      params = params || {};
	      this.setDateIndex(params.availableDateIndex);
	      this.setCurrentDate(params.selectedValue);
	      this.data = data;
	      main_core.Dom.adjust(this.DOM.labelWrap, {
	        text: this.data.label + '*'
	      });

	      if (this.setDataConfig()) {
	        main_core.Dom.remove(this.DOM.controlWrap);
	        this.displayControl();
	      }

	      if (this.style === 'line') {
	        this.lineDateControl.refreshDateAvailability();
	      }
	    }
	  }, {
	    key: "setDataConfig",
	    value: function setDataConfig() {
	      var style = this.data.style === 'line' ? 'line' : 'popup',
	          // line|popup
	      start = this.data.start === 'today' ? 'today' : 'free',
	          configWasChanged = this.style !== style || this.start !== start;
	      this.style = style;
	      this.start = start;
	      return configWasChanged;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.remove(this.DOM.innerWrap);
	      this.DOM.innerWrap = null;
	    }
	  }, {
	    key: "displayControl",
	    value: function displayControl() {
	      this.DOM.controlWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject3()));

	      if (this.style === 'popup') {
	        this.DOM.controlWrap.className = 'calendar-resbook-webform-block-calendar';
	        this.popupSateControl = new PopupDateSelector({
	          wrap: this.DOM.controlWrap,
	          isDateAvailable: this.isDateAvailable.bind(this),
	          onChange: function (value) {
	            this.onChange(value);
	          }.bind(this)
	        });
	        this.popupSateControl.build();
	        this.popupSateControl.setValue(this.getValue());
	      } else if (this.style === 'line') {
	        this.DOM.controlWrap.className = 'calendar-resbook-webform-block-date';
	        this.lineDateControl = new LineDateSelector({
	          wrap: this.DOM.controlWrap,
	          isDateAvailable: this.isDateAvailable.bind(this),
	          onChange: this.onChange.bind(this)
	        });
	        this.lineDateControl.build();
	        this.lineDateControl.setValue(this.getValue());
	      }
	    }
	  }, {
	    key: "setCurrentDate",
	    value: function setCurrentDate(date) {
	      if (main_core.Type.isDate(date)) {
	        this.currentDate = date;
	      }
	    }
	  }, {
	    key: "setDateIndex",
	    value: function setDateIndex(availableDateIndex) {
	      if (main_core.Type.isPlainObject(availableDateIndex)) {
	        this.availableDateIndex = availableDateIndex;
	      }
	    }
	  }, {
	    key: "isDateLoaded",
	    value: function isDateLoaded(date) {
	      if (main_core.Type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex) {
	        if (this.availableDateIndex[BookingUtil$$1.formatDate(null, date)] !== undefined) {
	          return true;
	        }

	        if (main_core.Type.isFunction(this.requestDataCallback)) {
	          this.requestDataCallback({
	            date: date
	          });
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "isDateAvailable",
	    value: function isDateAvailable(date) {
	      if (this.previewMode || this.allowOverbooking) {
	        return true;
	      }

	      if (main_core.Type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex) {
	        var dateKey = BookingUtil$$1.formatDate(null, date);

	        if (this.availableDateIndex[dateKey] === undefined) {
	          if (main_core.Type.isFunction(this.requestDataCallback)) {
	            this.requestDataCallback({
	              date: date
	            });
	          }

	          return false;
	        } else {
	          return this.availableDateIndex[dateKey];
	        }
	      }

	      return false;
	    }
	  }, {
	    key: "isItPastDate",
	    value: function isItPastDate(date) {
	      if (main_core.Type.isDate(date)) {
	        var nowDate = new Date(),
	            checkDate = new Date(date.getTime());
	        nowDate.setHours(0, 0, 0, 0);
	        checkDate.setHours(0, 0, 0, 0);
	        return checkDate.getTime() < nowDate.getTime();
	      }

	      return false;
	    }
	  }, {
	    key: "refreshCurrentValue",
	    value: function refreshCurrentValue() {
	      this.onChange(this.getDisplayedValue());
	    }
	  }, {
	    key: "getDisplayedValue",
	    value: function getDisplayedValue() {
	      return this.style === 'popup' ? this.popupSateControl.getValue() : this.lineDateControl.getValue();
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(date) {
	      if (main_core.Type.isFunction(this.changeValueCallback)) {
	        var realDate = date;

	        if (!main_core.Type.isDate(realDate)) {
	          realDate = this.getDisplayedValue();
	        }

	        this.setCurrentDate(date);
	        this.changeValueCallback(date, realDate, this.isDateAvailable(realDate));
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (!this.currentDate) {
	        this.currentDate = new Date();
	      }

	      return this.currentDate;
	    }
	  }]);
	  return DateSelector;
	}(ViewControlAbstract);

	var PopupDateSelector = /*#__PURE__*/function () {
	  function PopupDateSelector(params) {
	    babelHelpers.classCallCheck(this, PopupDateSelector);
	    this.DOM = {
	      outerWrap: params.wrap,
	      wrap: null
	    };
	    this.value = null;
	    this.datePicker = null;
	    this.isDateAvailable = main_core.Type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function () {
	      return true;
	    };
	    this.onChange = main_core.Type.isFunction(params.onChange) ? params.onChange : function () {};
	  }

	  babelHelpers.createClass(PopupDateSelector, [{
	    key: "build",
	    value: function build() {
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-strip'
	        },
	        events: {
	          click: this.handleClick.bind(this)
	        }
	      }));
	      this.DOM.valueInput = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject4()));
	      this.DOM.previousArrow = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject5()));
	      this.DOM.stateWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject6()));
	      this.DOM.stateWrapDate = this.DOM.stateWrap.appendChild(main_core.Tag.render(_templateObject7()));
	      this.DOM.stateWrapDay = this.DOM.stateWrap.appendChild(main_core.Tag.render(_templateObject8()));
	      this.DOM.nextArrow = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject9()));
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.value;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(dateValue) {
	      this.value = dateValue;
	      main_core.Dom.adjust(this.DOM.stateWrapDate, {
	        text: BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DATE_LINE'), dateValue)
	      });
	      main_core.Dom.adjust(this.DOM.stateWrapDay, {
	        text: BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DAY_LINE'), dateValue)
	      });

	      if (!this.isDateAvailable(dateValue) || !main_core.Type.isDate(dateValue)) {
	        this.onChange(false);
	      } else {
	        this.onChange(this.value);
	      }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick(e) {
	      var dateValue,
	          target = e.target || e.srcElement;

	      if (target.hasAttribute('data-bx-resbook-date-meta') || (target = target.closest('[data-bx-resbook-date-meta]'))) {
	        var dateMeta = target.getAttribute('data-bx-resbook-date-meta');

	        if (dateMeta === 'previous') {
	          dateValue = this.getValue();
	          dateValue.setDate(dateValue.getDate() - 1);
	          this.setValue(dateValue);
	        } else if (dateMeta === 'next') {
	          dateValue = this.getValue();
	          dateValue.setDate(dateValue.getDate() + 1);
	          this.setValue(dateValue);
	        } else if (dateMeta === 'calendar') {
	          this.openCalendarPopup();
	        }
	      }
	    }
	  }, {
	    key: "openCalendarPopup",
	    value: function openCalendarPopup() {
	      this.DOM.valueInput.value = BookingUtil$$1.formatDate(null, this.getValue().getTime() / 1000);

	      if (PopupDateSelector.isExternalDatePickerEnabled()) {
	        this.openExternalDatePicker();
	      } else {
	        this.openBxCalendar();
	      }
	    }
	  }, {
	    key: "openBxCalendar",
	    value: function openBxCalendar() {
	      BX.calendar({
	        node: this.DOM.stateWrap,
	        field: this.DOM.valueInput,
	        bTime: false
	      });

	      if (BX.calendar.get().popup) {
	        BookingUtil$$1.unbindCustomEvent(BX.calendar.get().popup, 'onPopupClose', this.handleCalendarClose.bind(this));
	        BookingUtil$$1.bindCustomEvent(BX.calendar.get().popup, 'onPopupClose', this.handleCalendarClose.bind(this));
	      }
	    }
	  }, {
	    key: "handleCalendarClose",
	    value: function handleCalendarClose() {
	      this.setValue(BookingUtil$$1.parseDate(this.DOM.valueInput.value));
	    }
	  }, {
	    key: "openExternalDatePicker",
	    value: function openExternalDatePicker() {
	      if (main_core.Type.isNull(this.datePicker)) {
	        this.datePicker = new BX.UI.Vue.Components.DatePick({
	          node: this.DOM.stateWrap,
	          hasTime: false,
	          events: {
	            change: function (value) {
	              this.DOM.valueInput.value = value;
	              this.handleCalendarClose();
	            }.bind(this)
	          }
	        });
	      }

	      this.datePicker.value = this.DOM.valueInput.value;
	      this.datePicker.toggle();
	    }
	  }], [{
	    key: "isExternalDatePickerEnabled",
	    value: function isExternalDatePickerEnabled() {
	      if (main_core.Type.isNull(PopupDateSelector.externalDatePickerIsEnabled)) {
	        PopupDateSelector.externalDatePickerIsEnabled = !!(window.BX && BX.UI && BX.UI.Vue && BX.UI.Vue.Components && BX.UI.Vue.Components.DatePick);
	      }

	      return PopupDateSelector.externalDatePickerIsEnabled;
	    }
	  }]);
	  return PopupDateSelector;
	}();

	babelHelpers.defineProperty(PopupDateSelector, "externalDatePickerIsEnabled", null);

	var LineDateSelector = /*#__PURE__*/function () {
	  function LineDateSelector(params) {
	    babelHelpers.classCallCheck(this, LineDateSelector);
	    params = params || {};
	    this.DOM = {
	      outerWrap: params.wrap,
	      wrap: null
	    };
	    this.value = null;
	    this.isDateAvailable = main_core.Type.isFunction(params.isDateAvailable) ? params.isDateAvailable : function () {
	      return true;
	    };
	    this.onChange = main_core.Type.isFunction(params.onChange) ? params.onChange : function () {};
	    this.DAYS_DISPLAY_SIZE = 30;
	    this.DOM.dayNodes = {};
	    this.dayNodeIndex = {};
	  }

	  babelHelpers.createClass(LineDateSelector, [{
	    key: "build",
	    value: function build() {
	      this.DOM.monthTitle = this.DOM.outerWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-date-month'
	        }
	      }));
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-date-range'
	        },
	        events: {
	          click: this.handleClick.bind(this)
	        }
	      }));
	      this.DOM.controlStaticWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject10()));
	      this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(main_core.Tag.render(_templateObject11()));
	      this.DOM.valueInput = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject12()));
	      this.fillDays();
	      this.initCustomScroll();
	    }
	  }, {
	    key: "fillDays",
	    value: function fillDays() {
	      var i,
	          startDate = this.getStartLoadDate(),
	          date = new Date(startDate.getTime());

	      for (i = 0; i < this.DAYS_DISPLAY_SIZE; i++) {
	        this.addDateSlot(date);
	        date.setDate(date.getDate() + 1);
	      }

	      this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);
	    }
	  }, {
	    key: "addDateSlot",
	    value: function addDateSlot(date) {
	      var dateCode = BookingUtil$$1.formatDate('Y-m-d', date.getTime() / 1000);
	      this.dayNodeIndex[dateCode] = new Date(date.getTime());
	      this.DOM.dayNodes[dateCode] = this.DOM.controlInnerWrap.appendChild(main_core.Dom.create("div", {
	        attrs: {
	          className: 'calendar-resbook-webform-block-date-item' + (this.isDateAvailable(date) ? '' : ' calendar-resbook-webform-block-date-item-off'),
	          'data-bx-resbook-date-meta': dateCode
	        },
	        html: '<div class="calendar-resbook-webform-block-date-item-inner">' + '<span class="calendar-resbook-webform-block-date-number">' + BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DATE'), date) + '</span>' + '<span class="calendar-resbook-webform-block-date-day">' + BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DAY_OF_THE_WEEK'), date) + '</span>' + '</div>'
	      }));
	    }
	  }, {
	    key: "refreshDateAvailability",
	    value: function refreshDateAvailability() {
	      for (var dateCode in this.DOM.dayNodes) {
	        if (this.DOM.dayNodes.hasOwnProperty(dateCode)) {
	          if (this.isDateAvailable(this.dayNodeIndex[dateCode])) {
	            main_core.Dom.removeClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
	          } else {
	            main_core.Dom.addClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
	          }
	        }
	      }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick(e) {
	      var dateValue,
	          target = e.target || e.srcElement;

	      if (target.hasAttribute('data-bx-resbook-date-meta') || (target = target.closest('[data-bx-resbook-date-meta]'))) {
	        var dateMeta = target.getAttribute('data-bx-resbook-date-meta');

	        if (dateMeta && (dateValue = BookingUtil$$1.parseDate(dateMeta, false, 'YYYY-MM-DD'))) {
	          this.setValue(dateValue);
	        }
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(dateValue) {
	      if (main_core.Type.isDate(dateValue)) {
	        this.value = dateValue;
	        var dayNode = this.getDayNode(dateValue);

	        if (dayNode) {
	          this.setSelected(dayNode);
	        }

	        this.onChange(this.value);
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.value;
	    }
	  }, {
	    key: "getDayNode",
	    value: function getDayNode(dateValue) {
	      var dateCode = BookingUtil$$1.formatDate('Y-m-d', dateValue.getTime() / 1000);

	      if (this.DOM.dayNodes[dateCode]) {
	        return this.DOM.dayNodes[dateCode];
	      } else {
	        this.fillDays(dateValue);

	        if (this.DOM.dayNodes[dateCode]) {
	          return this.DOM.dayNodes[dateCode];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "setSelected",
	    value: function setSelected(dayNode) {
	      if (this.currentSelected) {
	        main_core.Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-date-item-select');
	      }

	      this.currentSelected = dayNode;
	      main_core.Dom.addClass(dayNode, 'calendar-resbook-webform-block-date-item-select');
	    }
	  }, {
	    key: "getStartLoadDate",
	    value: function getStartLoadDate() {
	      if (!this.startLoadDate) {
	        this.startLoadDate = new Date();
	      } else {
	        this.startLoadDate.setDate(this.startLoadDate.getDate() + this.DAYS_DISPLAY_SIZE);
	      }

	      return this.startLoadDate;
	    }
	  }, {
	    key: "initCustomScroll",
	    value: function initCustomScroll() {
	      var arrowWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject13()));
	      this.DOM.leftArrow = arrowWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'
	        },
	        events: {
	          click: this.handlePreletrowClick.bind(this)
	        }
	      }));
	      this.DOM.rightArrow = arrowWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'
	        },
	        events: {
	          click: this.handleNextArrowClick.bind(this)
	        }
	      }));
	      this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
	      this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);

	      if ('onwheel' in document) {
	        main_core.Event.bind(this.DOM.controlStaticWrap, "wheel", this.mousewheelScrollHandler.bind(this));
	      } else {
	        main_core.Event.bind(this.DOM.controlStaticWrap, "mousewheel", this.mousewheelScrollHandler.bind(this));
	      }

	      this.checkScrollPosition();
	    }
	  }, {
	    key: "handleNextArrowClick",
	    value: function handleNextArrowClick() {
	      this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
	      this.checkScrollPosition();
	    }
	  }, {
	    key: "handlePreletrowClick",
	    value: function handlePreletrowClick() {
	      this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
	      this.checkScrollPosition();
	    }
	  }, {
	    key: "mousewheelScrollHandler",
	    value: function mousewheelScrollHandler(e) {
	      e = e || window.event;
	      var delta = e.deltaY || e.detail || e.wheelDelta;

	      if (Math.abs(delta) > 0) {
	        if (!main_core.Browser.isMac()) {
	          delta = delta * 3;
	        }

	        this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
	        this.checkScrollPosition();

	        if (e.stopPropagation) {
	          e.preventDefault();
	          e.stopPropagation();
	        }

	        return false;
	      }
	    }
	  }, {
	    key: "checkScrollPosition",
	    value: function checkScrollPosition() {
	      if (this.outerWidth <= this.innerWidth) {
	        this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft === 0 ? 'none' : ''; //this.DOM.rightArrow.style.display = (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) ? 'none' : '';

	        if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) {
	          this.fillDays();
	        }
	      }

	      this.updateMonthTitle();
	    }
	  }, {
	    key: "updateMonthTitle",
	    value: function updateMonthTitle() {
	      if (!this.dayNodeOuterWidth) {
	        this.dayNodeOuterWidth = this.DOM.controlInnerWrap.childNodes[1].offsetLeft - this.DOM.controlInnerWrap.childNodes[0].offsetLeft;

	        if (!this.dayNodeOuterWidth) {
	          return setTimeout(this.updateMonthTitle.bind(this), 100);
	        }
	      }

	      var monthFrom,
	          monthTo,
	          dateMeta,
	          dateValue,
	          firstDayNodeIndex = Math.floor(this.DOM.controlStaticWrap.scrollLeft / this.dayNodeOuterWidth),
	          lastDayNodeIndex = Math.floor((this.DOM.controlStaticWrap.scrollLeft + this.outerWidth) / this.dayNodeOuterWidth);

	      if (this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex]) {
	        dateMeta = this.DOM.controlInnerWrap.childNodes[firstDayNodeIndex].getAttribute('data-bx-resbook-date-meta');

	        if (dateMeta && (dateValue = BookingUtil$$1.parseDate(dateMeta, false, 'YYYY-MM-DD'))) {
	          monthFrom = monthTo = BookingUtil$$1.formatDate('f', dateValue);
	        }
	      }

	      if (this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex]) {
	        dateMeta = this.DOM.controlInnerWrap.childNodes[lastDayNodeIndex].getAttribute('data-bx-resbook-date-meta');

	        if (dateMeta && (dateValue = BookingUtil$$1.parseDate(dateMeta, false, 'YYYY-MM-DD'))) {
	          monthTo = BookingUtil$$1.formatDate('f', dateValue);
	        }
	      }

	      if (monthFrom && monthTo) {
	        main_core.Dom.adjust(this.DOM.monthTitle, {
	          text: monthTo === monthFrom ? monthFrom : monthFrom + ' - ' + monthTo
	        });
	      }
	    }
	  }]);
	  return LineDateSelector;
	}();

	function _templateObject11$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-arrow-container\" />"]);

	  _templateObject11$1 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-col-list-inner\"></div>"]);

	  _templateObject10$1 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-col-list\"></div>"]);

	  _templateObject9$1 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-time-inner-wrap\"></div>"]);

	  _templateObject8$1 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-time-static-wrap\"></div>"]);

	  _templateObject7$1 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-notice-icon\"/>"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-resbook-webform-block-notice-date\"/>"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-title-timezone\"></div>"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-title-timezone\"></div>"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-inner\"></div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block\"></div>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var TimeSelector = /*#__PURE__*/function (_ViewControlAbstract) {
	  babelHelpers.inherits(TimeSelector, _ViewControlAbstract);

	  function TimeSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, TimeSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TimeSelector).call(this, params));
	    _this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: null
	    };
	    _this.data = params.data || {};

	    _this.setDataConfig();

	    _this.timeFrom = _this.data.timeFrom || params.timeFrom || 7;

	    if (params.timeFrom !== undefined) {
	      _this.timeFrom = params.timeFrom;
	    }

	    _this.timeTo = _this.data.timeTo || 20;

	    if (params.timeTo !== undefined) {
	      _this.timeTo = params.timeTo;
	    }

	    _this.SLOTS_ROW_AMOUNT = 6;
	    _this.id = 'time-selector-' + Math.round(Math.random() * 1000);
	    _this.popupSelectId = _this.id + '-select-popup';
	    _this.previewMode = params.previewMode === undefined;
	    _this.changeValueCallback = params.changeValueCallback;
	    _this.timezone = params.timezone;
	    _this.timezoneOffset = params.timezoneOffset;
	    _this.timezoneOffsetLabel = params.timezoneOffsetLabel;
	    _this.timeMidday = 12;
	    _this.timeEvening = 17;
	    _this.displayed = true;
	    return _this;
	  }

	  babelHelpers.createClass(TimeSelector, [{
	    key: "setDataConfig",
	    value: function setDataConfig() {
	      var style = this.data.style === 'select' ? 'select' : 'slots',
	          // select|slots
	      showOnlyFree = this.data.showOnlyFree !== 'N',
	          showFinishTime = this.data.showFinishTime === 'Y',
	          scale = parseInt(this.data.scale || 30),
	          configWasChanged = this.style !== style || this.showOnlyFree !== showOnlyFree || this.showFinishTime !== showFinishTime || this.scale !== scale;
	      this.style = style;
	      this.showOnlyFree = showOnlyFree;
	      this.showFinishTime = showFinishTime;
	      this.scale = scale;
	      return configWasChanged;
	    }
	  }, {
	    key: "display",
	    value: function display() {
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$2()));
	      this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject2$1()));

	      if (this.data.label) {
	        this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	          props: {
	            className: 'calendar-resbook-webform-block-title'
	          },
	          text: this.data.label + '*'
	        }));

	        if (this.timezone) {
	          this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(main_core.Tag.render(_templateObject3$1()));
	          main_core.Dom.adjust(this.DOM.timezoneLabelWrap, {
	            html: main_core.Loc.getMessage('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)
	          });
	        }
	      }

	      this.displayControl();
	      this.setValue(this.getValue());
	      this.shown = true;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(data, params) {
	      params = params || {};
	      this.setSlotIndex(params.slotIndex);
	      this.currentDate = params.currentDate || new Date();
	      this.data = data;

	      if (!this.isShown()) {
	        this.setDataConfig();
	        this.display();
	      } else {
	        if (this.DOM.labelWrap && this.data.label) {
	          main_core.Dom.adjust(this.DOM.labelWrap, {
	            text: this.data.label + '*'
	          });
	        }

	        if (this.timezone) {
	          if (!this.DOM.timezoneLabelWrap || !this.DOM.labelWrap.contains(this.DOM.timezoneLabelWrap)) {
	            this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(main_core.Tag.render(_templateObject4$1()));
	          }

	          main_core.Dom.adjust(this.DOM.timezoneLabelWrap, {
	            html: main_core.Loc.getMessage('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)
	          });
	        }

	        if (this.setDataConfig() || params.slotIndex || params.selectedValue) {
	          main_core.Dom.remove(this.DOM.controlWrap);
	          this.displayControl();
	        }
	      }

	      this.setCurrentValue(params.selectedValue || this.getValue());
	    }
	  }, {
	    key: "setSlotIndex",
	    value: function setSlotIndex(slotIndex) {
	      if (main_core.Type.isPlainObject(slotIndex)) {
	        this.availableSlotIndex = slotIndex;
	      }
	    }
	  }, {
	    key: "setCurrentValue",
	    value: function setCurrentValue(timeValue) {
	      if (timeValue && (this.previewMode || this.availableSlotIndex[timeValue])) {
	        this.setValue(timeValue);
	      } else {
	        this.setValue(null);
	      }
	    }
	  }, {
	    key: "showEmptyWarning",
	    value: function showEmptyWarning() {
	      if (this.DOM.labelWrap) {
	        this.DOM.labelWrap.style.display = 'none';
	      }

	      if (!this.DOM.warningWrap) {
	        this.DOM.warningTextNode = main_core.Tag.render(_templateObject5$1());
	        this.DOM.warningWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	          props: {
	            className: 'calendar-resbook-webform-block-notice'
	          },
	          children: [main_core.Tag.render(_templateObject6$1()), this.DOM.warningTextNode, main_core.Dom.create("span", {
	            props: {
	              className: 'calendar-resbook-webform-block-notice-detail'
	            },
	            text: main_core.Loc.getMessage('WEBF_RES_BOOKING_BUSY_DAY_WARNING')
	          })]
	        }));
	      }

	      if (this.DOM.warningWrap) {
	        main_core.Dom.adjust(this.DOM.warningTextNode, {
	          text: BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_BUSY_DAY_DATE_FORMAT'), this.currentDate)
	        });
	        this.DOM.warningWrap.style.display = '';
	        this.noSlotsAvailable = true;
	      }
	    }
	  }, {
	    key: "hideEmptyWarning",
	    value: function hideEmptyWarning() {
	      this.noSlotsAvailable = false;

	      if (this.DOM.labelWrap) {
	        this.DOM.labelWrap.style.display = '';
	      }

	      if (this.DOM.warningWrap) {
	        this.DOM.warningWrap.style.display = 'none';
	      }
	    }
	  }, {
	    key: "displayControl",
	    value: function displayControl() {
	      var slotsInfo = this.getSlotsInfo();
	      this.slots = slotsInfo.slots;

	      if (!slotsInfo.freeSlotsCount) {
	        this.showEmptyWarning();
	      } else {
	        this.hideEmptyWarning();

	        if (this.style === 'select') {
	          this.createSelectControl();
	        } else if (this.style === 'slots') {
	          this.createSlotsControl();
	        }
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.DOM.innerWrap) {
	        this.DOM.innerWrap.style.display = 'none';
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.DOM.innerWrap) {
	        this.DOM.innerWrap.style.display = '';
	      }
	    }
	  }, {
	    key: "createSlotsControl",
	    value: function createSlotsControl() {
	      if (this.DOM.controlWrap) {
	        main_core.Dom.remove(this.DOM.controlWrap);
	      }

	      this.DOM.controlWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-time'
	        },
	        events: {
	          click: this.handleClick.bind(this)
	        }
	      }));

	      if (!this.showFinishTime && !BookingUtil$$1.isAmPmMode()) {
	        main_core.Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-sm');
	      } else if (!this.showFinishTime && BookingUtil$$1.isAmPmMode()) {
	        main_core.Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-md');
	      } else if (BookingUtil$$1.isAmPmMode()) {
	        main_core.Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-lg');
	      }

	      this.DOM.controlStaticWrap = this.DOM.controlWrap.appendChild(main_core.Tag.render(_templateObject7$1()));
	      this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(main_core.Tag.render(_templateObject8$1()));
	      var itemsInColumn,
	          maxColumnNumber = 3,
	          parts = {},
	          itemNumber = 0,
	          innerWrap; // FilterSlots

	      this.slots.forEach(function (slot) {
	        if (!parts[slot.partOfTheDay]) {
	          parts[slot.partOfTheDay] = {
	            items: []
	          };
	        }

	        parts[slot.partOfTheDay].items.push(slot);
	      });
	      this.slots.forEach(function (slot) {
	        if (!parts[slot.partOfTheDay].wrap) {
	          itemNumber = 0;
	          itemsInColumn = 6;
	          parts[slot.partOfTheDay].wrap = main_core.Dom.create("div", {
	            props: {
	              className: 'calendar-resbook-webform-block-col'
	            },
	            html: '<span class="calendar-resbook-webform-block-col-title">' + main_core.Loc.getMessage('WEBF_RES_PART_OF_THE_DAY_' + slot.partOfTheDay.toUpperCase()) + '</span>'
	          });
	          parts[slot.partOfTheDay].itemsWrap = parts[slot.partOfTheDay].wrap.appendChild(main_core.Tag.render(_templateObject9$1()));

	          if (parts[slot.partOfTheDay].items.length > maxColumnNumber * itemsInColumn) {
	            itemsInColumn = Math.ceil(parts[slot.partOfTheDay].items.length / maxColumnNumber);
	          }
	        }

	        if (itemNumber % itemsInColumn === 0) {
	          innerWrap = parts[slot.partOfTheDay].itemsWrap.appendChild(main_core.Tag.render(_templateObject10$1()));
	        }

	        if (innerWrap && (!slot.booked || !this.showOnlyFree)) {
	          innerWrap.appendChild(main_core.Dom.create("div", {
	            attrs: {
	              'data-bx-resbook-time-meta': 'slot' + (slot.booked ? '-off' : ''),
	              'data-bx-resbook-slot': slot.time.toString(),
	              className: 'calendar-resbook-webform-block-col-item' + (slot.selected ? ' calendar-resbook-webform-block-col-item-select' : '') + (slot.booked ? ' calendar-resbook-webform-block-col-item-off' : '')
	            },
	            html: '<div class="calendar-resbook-webform-block-col-item-inner">' + '<span class="calendar-resbook-webform-block-col-time">' + slot.fromTime + '</span>' + (this.showFinishTime ? '- <span class="calendar-resbook-webform-block-col-time calendar-resbook-webform-block-col-time-end">' + slot.toTime + '</span>' : '') + '</div>'
	          }));
	          itemNumber++;
	        }

	        parts[slot.partOfTheDay].itemsAmount = itemNumber;
	      }, this);
	      var k;

	      for (k in parts) {
	        if (parts.hasOwnProperty(k) && parts[k].itemsAmount > 0) {
	          this.DOM.controlInnerWrap.appendChild(parts[k].wrap);
	        }
	      }

	      this.initCustomScrollForSlots();
	    }
	  }, {
	    key: "createSelectControl",
	    value: function createSelectControl() {
	      if (this.DOM.controlWrap) {
	        main_core.Dom.remove(this.DOM.controlWrap);
	      }

	      this.DOM.controlWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-field'
	        },
	        events: {
	          click: this.handleClick.bind(this)
	        }
	      }));
	      this.DOM.timeSelectWrap = this.DOM.controlWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-strip'
	        }
	      }));
	      this.DOM.valueInput = this.DOM.timeSelectWrap.appendChild(main_core.Dom.create("input", {
	        attrs: {
	          type: 'hidden',
	          value: ''
	        }
	      }));
	      this.DOM.previousArrow = this.DOM.timeSelectWrap.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev',
	          'data-bx-resbook-time-meta': 'previous'
	        }
	      }));
	      this.DOM.stateWrap = this.DOM.timeSelectWrap.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: 'calendar-resbook-webform-block-strip-text',
	          'data-bx-resbook-time-meta': 'select'
	        }
	      }));
	      this.DOM.stateWrap = this.DOM.stateWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-strip-date'
	        }
	      }));
	      this.DOM.nextArrow = this.DOM.timeSelectWrap.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next',
	          'data-bx-resbook-time-meta': 'next'
	        }
	      }));
	      this.setValue(this.getValue());
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var slot = this.getSlotByTime(value);

	      if (slot) {
	        if (this.style === 'select' && main_core.Type.isDomNode(this.DOM.stateWrap)) {
	          main_core.Dom.adjust(this.DOM.stateWrap, {
	            text: this.getTimeTextBySlot(slot)
	          });
	        } else if (this.style === 'slots') {
	          this.setSelected(this.getSlotNode(slot.time));
	        }

	        this.value = slot.time;
	      } else {
	        this.value = null;
	      }

	      if (!this.previewMode && main_core.Type.isFunction(this.changeValueCallback)) {
	        this.changeValueCallback(this.value);
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (!this.value && (this.previewMode || this.style === 'select')) {
	        this.value = this.slots[0].time;
	      }

	      return this.value;
	    }
	  }, {
	    key: "hasAvailableSlots",
	    value: function hasAvailableSlots() {
	      return !this.noSlotsAvailable;
	    }
	  }, {
	    key: "getTimeTextBySlot",
	    value: function getTimeTextBySlot(slot) {
	      return slot.fromTime + (this.showFinishTime ? ' - ' + slot.toTime : '');
	    }
	  }, {
	    key: "getSlotByTime",
	    value: function getSlotByTime(time) {
	      return main_core.Type.isArray(this.slots) ? this.slots.find(function (slot) {
	        return parseInt(slot.time) === parseInt(time);
	      }) : null;
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick(e) {
	      var target = e.target || e.srcElement;

	      if (target.hasAttribute('data-bx-resbook-time-meta') || (target = target.closest('[data-bx-resbook-time-meta]'))) {
	        var meta = target.getAttribute('data-bx-resbook-time-meta');

	        if (this.style === 'select') {
	          if (meta === 'previous') {
	            this.setValue(this.getValue() - this.scale);
	          } else if (meta === 'next') {
	            this.setValue(this.getValue() + this.scale);
	          } else if (meta === 'select') {
	            this.openSelectPopup();
	          }
	        } else if (meta === 'slot') {
	          this.setValue(parseInt(target.getAttribute('data-bx-resbook-slot')));
	        }
	      }
	    }
	  }, {
	    key: "getSlotsInfo",
	    value: function getSlotsInfo() {
	      var slots = [],
	          slot,
	          freeSlotsCount = 0,
	          finishTime,
	          hourFrom,
	          minFrom,
	          hourTo,
	          minTo,
	          part = 'morning',
	          num = 0,
	          time = this.timeFrom * 60;

	      while (time < this.timeTo * 60) {
	        if (time >= this.timeEvening * 60) {
	          part = 'evening';
	        } else if (time >= this.timeMidday * 60) {
	          part = 'afternoon';
	        }

	        hourFrom = Math.floor(time / 60);
	        minFrom = time - hourFrom * 60;
	        finishTime = time + this.scale;
	        hourTo = Math.floor(finishTime / 60);
	        minTo = finishTime - hourTo * 60;
	        slot = {
	          time: time,
	          fromTime: BookingUtil$$1.formatTime(hourFrom, minFrom),
	          toTime: BookingUtil$$1.formatTime(hourTo, minTo),
	          partOfTheDay: part
	        };

	        if (this.previewMode) {
	          if (!num) {
	            slot.selected = true;
	          } else if (Math.round(Math.random() * 10) <= 3) {
	            slot.booked = true;
	          }
	        } else if (this.availableSlotIndex) {
	          slot.booked = !this.availableSlotIndex[time];
	        }

	        if (!slot.booked) {
	          freeSlotsCount++;
	        }

	        slots.push(slot);
	        time += this.scale;
	        num++;
	      }

	      return {
	        slots: slots,
	        freeSlotsCount: freeSlotsCount
	      };
	    }
	  }, {
	    key: "initCustomScrollForSlots",
	    value: function initCustomScrollForSlots() {
	      var arrowWrap = this.DOM.controlWrap.appendChild(main_core.Tag.render(_templateObject11$1()));
	      this.DOM.leftArrow = arrowWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'
	        },
	        events: {
	          click: this.handlePreletrowClick.bind(this)
	        }
	      }));
	      this.DOM.rightArrow = arrowWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'
	        },
	        events: {
	          click: this.handleNextArrowClick.bind(this)
	        }
	      }));
	      this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
	      this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);
	      if ('onwheel' in document) main_core.Event.bind(this.DOM.controlStaticWrap, "wheel", this.mousewheelScrollHandler.bind(this));else main_core.Event.bind(this.DOM.controlStaticWrap, "mousewheel", this.mousewheelScrollHandler.bind(this));
	      this.checkSlotsScroll();
	    }
	  }, {
	    key: "handleNextArrowClick",
	    value: function handleNextArrowClick() {
	      this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
	      this.checkSlotsScroll();
	    }
	  }, {
	    key: "handlePreletrowClick",
	    value: function handlePreletrowClick() {
	      this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
	      this.checkSlotsScroll();
	    }
	  }, {
	    key: "mousewheelScrollHandler",
	    value: function mousewheelScrollHandler(e) {
	      e = e || window.event;
	      var delta = e.deltaY || e.detail || e.wheelDelta;

	      if (Math.abs(delta) > 0) {
	        if (!main_core.Browser.isMac()) {
	          delta = delta * 5;
	        }

	        this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
	        this.checkSlotsScroll();

	        if (e.stopPropagation) {
	          e.preventDefault();
	          e.stopPropagation();
	        }

	        return false;
	      }
	    }
	  }, {
	    key: "checkSlotsScroll",
	    value: function checkSlotsScroll() {
	      if (this.outerWidth <= this.innerWidth) {
	        this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft ? '' : 'none';

	        if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) {
	          this.DOM.rightArrow.style.display = 'none';
	        } else {
	          this.DOM.rightArrow.style.display = '';
	        }
	      }
	    }
	  }, {
	    key: "openSelectPopup",
	    value: function openSelectPopup() {
	      if (this.isSelectPopupShown()) {
	        return this.closeSelectPopup();
	      }

	      this.popup = main_popup.MenuManager.create(this.popupSelectId, this.DOM.stateWrap, this.getTimeSelectItems(), {
	        className: "calendar-resbook-time-select-popup",
	        angle: true,
	        closeByEsc: true,
	        autoHide: true,
	        offsetTop: 5,
	        offsetLeft: 10,
	        cacheable: false
	      });
	      this.popup.show(true);
	    }
	  }, {
	    key: "closeSelectPopup",
	    value: function closeSelectPopup() {
	      if (this.isSelectPopupShown()) {
	        this.popup.close();
	        main_core.Event.unbind(document, 'click', this.handleClick.bind(this));
	      }
	    }
	  }, {
	    key: "isSelectPopupShown",
	    value: function isSelectPopupShown() {
	      return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown();
	    }
	  }, {
	    key: "getTimeSelectItems",
	    value: function getTimeSelectItems() {
	      var menuItems = [];
	      this.slots.forEach(function (slot) {
	        if (this.showOnlyFree && slot.booked) {
	          return;
	        }

	        var className = 'menu-popup-no-icon';

	        if (slot.booked) {
	          className += ' menu-item-booked';
	        }

	        if (slot.selected) {
	          className += ' menu-item-selected';
	        }

	        menuItems.push({
	          className: className,
	          text: this.getTimeTextBySlot(slot),
	          dataset: {
	            value: slot.time,
	            booked: !!slot.booked
	          },
	          onclick: this.menuItemClick.bind(this)
	        });
	      }, this);
	      return menuItems;
	    }
	  }, {
	    key: "menuItemClick",
	    value: function menuItemClick(e, menuItem) {
	      if (menuItem && menuItem.dataset && menuItem.dataset.value) {
	        if (!menuItem.dataset.booked) {
	          this.setValue(menuItem.dataset.value);
	        }
	      }

	      this.closeSelectPopup();
	    }
	  }, {
	    key: "getSlotNode",
	    value: function getSlotNode(time) {
	      var i,
	          slotNodes = this.DOM.controlInnerWrap.querySelectorAll('.calendar-resbook-webform-block-col-item');

	      for (i = 0; i < slotNodes.length; i++) {
	        if (parseInt(slotNodes[i].getAttribute('data-bx-resbook-slot')) === parseInt(time)) {
	          return slotNodes[i];
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "setSelected",
	    value: function setSelected(slotNode) {
	      if (main_core.Type.isDomNode(slotNode)) {
	        if (this.currentSelected) {
	          main_core.Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-col-item-select');
	        }

	        this.currentSelected = slotNode;
	        main_core.Dom.addClass(slotNode, 'calendar-resbook-webform-block-col-item-select');
	      }
	    }
	  }]);
	  return TimeSelector;
	}(ViewControlAbstract);

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-result-value\"></div>"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-result-inner\"></div>"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-block-result\" style=\"display: none\" \n></div>"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var StatusInformer = /*#__PURE__*/function () {
	  function StatusInformer(params) {
	    babelHelpers.classCallCheck(this, StatusInformer);
	    this.DOM = {
	      outerWrap: params.outerWrap
	    };
	    this.timezone = params.timezone;
	    this.timezoneOffsetLabel = params.timezoneOffsetLabel;
	    this.shown = false;
	    this.built = false;
	  }

	  babelHelpers.createClass(StatusInformer, [{
	    key: "isShown",
	    value: function isShown() {
	      return this.shown;
	    }
	  }, {
	    key: "build",
	    value: function build() {
	      this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$3()));
	      this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject2$2()));
	      this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-result-text'
	        },
	        text: main_core.Loc.getMessage('WEBF_RES_BOOKING_STATUS_LABEL')
	      }));
	      this.DOM.statusWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject3$2()));
	      this.DOM.statusTimezone = this.DOM.innerWrap.appendChild(main_core.Dom.create("span", {
	        props: {
	          className: 'calendar-resbook-webform-block-result-timezone'
	        },
	        text: this.timezoneOffsetLabel || '',
	        style: {
	          display: 'none'
	        }
	      }));
	      this.built = true;
	    }
	  }, {
	    key: "refresh",
	    value: function refresh(params) {
	      if (!this.built) {
	        this.build();
	      }

	      if (!this.isShown()) {
	        this.show();
	      }

	      if (params.dateFrom) {
	        this.DOM.labelWrap.style.display = '';
	        main_core.Dom.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');

	        if (this.timezone) {
	          this.DOM.statusTimezone.style.display = '';
	        }

	        main_core.Dom.adjust(this.DOM.statusWrap, {
	          text: this.getStatusText(params)
	        });
	      } else if (!params.dateFrom && params.fullDay) {
	        this.DOM.labelWrap.style.display = 'none';
	        this.DOM.statusTimezone.style.display = 'none';
	        main_core.Dom.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	        main_core.Dom.adjust(this.DOM.statusWrap, {
	          text: main_core.Loc.getMessage('WEBF_RES_BOOKING_STATUS_DATE_IS_NOT_AVAILABLE')
	        });
	      } else {
	        this.DOM.labelWrap.style.display = 'none';
	        this.DOM.statusTimezone.style.display = 'none';
	        main_core.Dom.removeClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	        main_core.Dom.adjust(this.DOM.statusWrap, {
	          text: main_core.Loc.getMessage('WEBF_RES_BOOKING_STATUS_NO_TIME_SELECTED')
	        });
	      }
	    }
	  }, {
	    key: "getStatusText",
	    value: function getStatusText(params) {
	      var dateFrom = params.dateFrom,
	          dateTo = new Date(dateFrom.getTime() + params.duration * 60 * 1000 + (params.fullDay ? -1 : 0)),
	          text = '';

	      if (params.fullDay) {
	        if (BookingUtil$$1.formatDate('Y-m-d', dateFrom.getTime() / 1000) === BookingUtil$$1.formatDate('Y-m-d', dateTo.getTime() / 1000)) {
	          text = BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom);
	        } else {
	          text = main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_FROM_TO').replace('#DATE_FROM#', BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom)).replace('#DATE_TO#', BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo));
	        }
	      } else {
	        if (BookingUtil$$1.formatDate('Y-m-d', dateFrom.getTime() / 1000) === BookingUtil$$1.formatDate('Y-m-d', dateTo.getTime() / 1000)) {
	          text = BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS'), dateFrom) + ' ' + main_core.Loc.getMessage('WEBF_RES_TIME_FORMAT_FROM_TO').replace('#TIME_FROM#', BookingUtil$$1.formatTime(dateFrom.getHours(), dateFrom.getMinutes())).replace('#TIME_TO#', BookingUtil$$1.formatTime(dateTo.getHours(), dateTo.getMinutes()));
	        } else {
	          text = main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_FROM_TO').replace('#DATE_FROM#', BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateFrom) + ' ' + BookingUtil$$1.formatTime(dateFrom.getHours(), dateFrom.getMinutes())).replace('#DATE_TO#', BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_STATUS_SHORT'), dateTo) + ' ' + BookingUtil$$1.formatTime(dateTo.getHours(), dateTo.getMinutes()));
	        }
	      }

	      return text;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.built && this.shown) {
	        this.DOM.wrap.style.display = 'none';
	        this.shown = false;
	      }
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.built && !this.shown) {
	        this.DOM.wrap.style.display = '';
	        this.shown = true;
	      }
	    }
	  }, {
	    key: "setError",
	    value: function setError(message) {
	      if (this.DOM.labelWrap) {
	        this.DOM.labelWrap.style.display = 'none';
	      }

	      main_core.Dom.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	      main_core.Dom.adjust(this.DOM.statusWrap, {
	        text: message
	      });
	    }
	  }, {
	    key: "isErrorSet",
	    value: function isErrorSet() {
	      return this.shown && main_core.Dom.hasClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	    }
	  }]);
	  return StatusInformer;
	}();

	function _templateObject6$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-wrapper-loader-wrap\"></div>"]);

	  _templateObject6$2 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<input \n\t\t\t\t\t\tname=\"", "\"\n\t\t\t\t\t\tvalue=\"empty\" \n\t\t\t\t\t\ttype=\"hidden\"\n\t\t\t\t\t\t>\n\t\t\t\t\t"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<input \n\t\t\t\t\t\tname=\"", "\"\n\t\t\t\t\t\tvalue=\"", "\" \n\t\t\t\t\t\ttype=\"hidden\"\n\t\t\t\t\t\t>\n\t\t\t\t\t"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-inner\"></div>"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-resbook-webform-wrapper\"></div>"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var LiveFieldController = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(LiveFieldController, _EventEmitter);

	  function LiveFieldController(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, LiveFieldController);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LiveFieldController).call(this, params));

	    _this.setEventNamespace('BX.Calendar.LiveFieldController');

	    _this.params = params;
	    _this.actionAgent = params.actionAgent || BX.ajax.runAction;
	    _this.timeFrom = params.timeFrom || 7;
	    _this.timeTo = params.timeTo || 20;
	    _this.inputName = params.field.name + '[]';
	    _this.DATE_FORMAT = BookingUtil$$1.getDateFormat();
	    _this.DATETIME_FORMAT = BookingUtil$$1.getDateTimeFormat();
	    _this.userIndex = null;
	    _this.timezoneOffset = null;
	    _this.timezoneOffsetLabel = null;
	    _this.userFieldParams = null;
	    _this.loadedDates = [];
	    _this.externalSiteContext = main_core.Type.isFunction(params.actionAgent);
	    _this.accessibility = {
	      user: {},
	      resource: {}
	    };
	    _this.busySlotMatrix = {
	      user: {},
	      resource: {}
	    };
	    _this.DOM = {
	      wrap: _this.params.wrap,
	      valueInputs: []
	    };
	    return _this;
	  }

	  babelHelpers.createClass(LiveFieldController, [{
	    key: "init",
	    value: function init() {
	      var _this2 = this;

	      var settingsData = this.getSettingsData();

	      if (!settingsData.users || !settingsData.resources) {
	        throw new Error('Can\'t init resourcebooking field, because \'settings_data\' parameter is not provided or has incorrect structure');
	        return;
	      }

	      this.scale = parseInt(settingsData.time && settingsData.time.scale ? settingsData.time.scale : 60, 10);
	      this.DOM.outerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject$4()));
	      this.showMainLoader();
	      this.requireFormData().then(function () {
	        _this2.hideMainLoader();

	        _this2.buildFormControls();

	        _this2.onChangeValues();
	      });
	    }
	  }, {
	    key: "check",
	    value: function check() {
	      var result = true;

	      if (this.usersDisplayed() && !this.getSelectedUser()) {
	        this.userControl.showWarning();
	        result = false;
	      }

	      if (result && this.resourcesDisplayed() && !this.getSelectedResources()) {
	        this.resourceControl.showWarning();
	        result = false;
	      }

	      if (result && !this.getCurrentDuration()) {
	        if (this.durationControl) {
	          this.durationControl.showWarning();
	        } else if (this.serviceControl) {
	          this.serviceControl.showWarning();
	        }

	        result = false;
	      }

	      if (result && (!this.dateControl.getValue() || this.statusControl.isErrorSet())) {
	        this.dateControl.showWarning();
	        result = false;
	      }

	      if (result && this.timeSelectorDisplayed() && !this.timeControl.getValue()) {
	        this.timeControl.showWarning();
	        result = false;
	      }

	      return result;
	    }
	  }, {
	    key: "buildFormControls",
	    value: function buildFormControls() {
	      this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject2$3()));
	      this.DOM.inputsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_templateObject3$3()));

	      if (!this.getFieldParams()) {
	        this.statusControl = new StatusInformer({
	          outerWrap: this.DOM.innerWrap
	        });
	        this.statusControl.refresh({});
	        this.statusControl.setError('[UF_NOT_FOUND] ' + main_core.Loc.getMessage('WEBF_RES_BOOKING_UF_WARNING'));
	      } else {
	        if (this.externalSiteContext && BX.ZIndexManager) {
	          var stack = BX.ZIndexManager.getOrAddStack(document.body);
	          stack.baseIndex = 100000;
	          stack.sort();
	        }

	        this.preparaAutoSelectValues();
	        this.displayUsersControl();
	        this.displayResourcesControl();
	        this.displayServicesControl();
	        this.displayDurationControl();
	        this.displayDateTimeControl();

	        if (this.selectedUserId || this.selectedResourceId) {
	          this.refreshControlsState();
	        }
	      }
	    }
	  }, {
	    key: "refreshControlsState",
	    value: function refreshControlsState() {
	      if (this.selectorCanBeShown('resources') && this.resourceControl && !this.resourceControl.isShown()) {
	        this.resourceControl.display();
	      } // Show services


	      if (this.selectorCanBeShown('services') && this.serviceControl && !this.serviceControl.isShown()) {
	        this.serviceControl.display();
	      } // Show duration


	      if (this.selectorCanBeShown('duration') && this.durationControl && !this.durationControl.isShown()) {
	        this.durationControl.display();
	      }

	      var settingsData = this.getSettingsData(); // Show date & time control

	      if (this.selectorCanBeShown('date') && this.dateControl) {
	        if (this.dateControl.isShown()) {
	          this.dateControl.refresh(settingsData.date, {
	            availableDateIndex: this.getAvailableDateIndex({
	              resources: this.getSelectedResources(),
	              user: this.getSelectedUser(),
	              duration: this.getCurrentDuration()
	            })
	          });

	          if (this.timeControl) {
	            this.timeControl.refresh(settingsData.time, {
	              slotIndex: this.getSlotIndex({
	                date: this.dateControl.getValue()
	              }),
	              currentDate: this.dateControl.getValue()
	            });
	          }
	        } else {
	          var startValue;

	          if (settingsData.date.start === 'free') {
	            startValue = this.getFreeDate({
	              resources: this.getSelectedResources(),
	              user: this.getSelectedUser(),
	              duration: this.getCurrentDuration()
	            });
	          } else {
	            startValue = new Date();
	          }

	          this.dateControl.display({
	            selectedValue: startValue,
	            availableDateIndex: this.getAvailableDateIndex({
	              resources: this.getSelectedResources(),
	              user: this.getSelectedUser(),
	              duration: this.getCurrentDuration()
	            })
	          });
	        }
	      }

	      this.updateStatusControl();
	      this.onChangeValues();
	      BookingUtil$$1.fireCustomEvent(window, 'crmWebFormFireResize');
	    }
	  }, {
	    key: "onChangeValues",
	    value: function onChangeValues() {
	      var allValuesValue = [],
	          dateFromValue = '',
	          dateFrom = this.getCurrentDate(),
	          duration = this.getCurrentDuration() * 60,
	          // Duration in minutes
	      serviceName = this.getCurrentServiceName(),
	          entries = []; // Clear inputs

	      main_core.Dom.clean(this.DOM.inputsWrap);
	      this.DOM.valueInputs = [];

	      if (main_core.Type.isDate(dateFrom) && !this.statusControl.isErrorSet()) {
	        var resources = this.getSelectedResources();

	        if (main_core.Type.isArray(resources)) {
	          resources.forEach(function (resourceId) {
	            entries = entries.concat({
	              type: 'resource',
	              id: resourceId
	            });
	          });
	        }

	        var selectedUser = this.getSelectedUser();

	        if (selectedUser) {
	          entries = entries.concat({
	            type: 'user',
	            id: selectedUser
	          });
	        }

	        dateFromValue = BookingUtil$$1.formatDate(this.DATETIME_FORMAT, dateFrom.getTime() / 1000);
	        entries.forEach(function (entry) {
	          var value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
	          allValuesValue.push(value);
	          this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(main_core.Tag.render(_templateObject4$2(), main_core.Text.encode(this.inputName), main_core.Text.encode(value))));
	        }, this);
	      }

	      if (!entries.length) {
	        allValuesValue.push('empty');
	        this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(main_core.Tag.render(_templateObject5$2(), main_core.Text.encode(this.inputName))));
	      }

	      this.emit('change', allValuesValue);
	    }
	  }, {
	    key: "showMainLoader",
	    value: function showMainLoader() {
	      if (this.DOM.wrap) {
	        this.hideMainLoader();
	        var loaderWrap = main_core.Tag.render(_templateObject6$2());
	        loaderWrap.appendChild(BookingUtil$$1.getLoader(160));
	        this.DOM.mainLoader = this.DOM.outerWrap.appendChild(loaderWrap);
	      }
	    }
	  }, {
	    key: "hideMainLoader",
	    value: function hideMainLoader() {
	      main_core.Dom.remove(this.DOM.mainLoader);
	    }
	  }, {
	    key: "showStatusLoader",
	    value: function showStatusLoader() {
	      this.showMainLoader();
	    }
	  }, {
	    key: "hideStatusLoader",
	    value: function hideStatusLoader() {
	      this.hideMainLoader();
	    }
	  }, {
	    key: "requestAccessibilityData",
	    value: function requestAccessibilityData(params) {
	      var _this3 = this;

	      if (!this.requestedFormData) {
	        this.showStatusLoader();
	        this.requestedFormData = true;
	        var formDataParams = {
	          from: params.date
	        };
	        this.requireFormData(formDataParams).then(function () {
	          _this3.hideStatusLoader();

	          _this3.refreshControlsState();

	          _this3.dateControl.refreshCurrentValue();

	          _this3.onChangeValues();

	          _this3.requestedFormData = false;
	        });
	      }
	    }
	  }, {
	    key: "requireFormData",
	    value: function requireFormData(params) {
	      var _this4 = this;

	      params = main_core.Type.isPlainObject(params) ? params : {};
	      return new Promise(function (resolve, reject) {
	        var data = {
	          settingsData: _this4.getSettingsData() || null
	        };

	        if (!_this4.userFieldParams) {
	          data.fieldName = _this4.params.field.entity_field_name;
	        }

	        var dateFrom = main_core.Type.isDate(params.from) ? params.from : new Date(),
	            dateTo;

	        if (main_core.Type.isDate(params.to)) {
	          dateTo = params.to;
	        } else {
	          dateTo = new Date(dateFrom.getTime());
	          dateTo.setDate(dateFrom.getDate() + 60);
	        }

	        data.from = BookingUtil$$1.formatDate(_this4.DATE_FORMAT, dateFrom);
	        data.to = BookingUtil$$1.formatDate(_this4.DATE_FORMAT, dateTo);

	        _this4.setLoadedDataLimits(dateFrom, dateTo);

	        _this4.actionAgent('calendar.api.resourcebookingajax.getfillformdata', {
	          data: data
	        }).then(function (response) {
	          if (!main_core.Type.isPlainObject(response) || !response.data) {
	            resolve(response);
	          } else {
	            if (main_core.Type.isNumber(response.data.timezoneOffset)) {
	              _this4.timezoneOffset = response.data.timezoneOffset;
	              _this4.timezoneOffsetLabel = response.data.timezoneOffsetLabel;
	            }

	            if (response.data.workTimeStart !== undefined && response.data.workTimeEnd !== undefined) {
	              _this4.timeFrom = parseInt(response.data.workTimeStart);
	              _this4.timeTo = parseInt(response.data.workTimeEnd);
	            }

	            if (response.data.fieldSettings) {
	              _this4.userFieldParams = response.data.fieldSettings;
	            }

	            if (response.data.userIndex) {
	              _this4.userIndex = response.data.userIndex;
	            }

	            _this4.handleAccessibilityData(response.data.usersAccessibility, 'user');

	            _this4.handleAccessibilityData(response.data.resourcesAccessibility, 'resource');

	            resolve(response.data);
	          }
	        }, function (response) {
	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "setLoadedDataLimits",
	    value: function setLoadedDataLimits(from, to) {
	      this.loadedDataFrom = main_core.Type.isDate(from) ? from : BookingUtil$$1.parseDate(from);
	      this.loadedDataTo = main_core.Type.isDate(to) ? to : BookingUtil$$1.parseDate(to);
	      this.loadedDates = this.loadedDates || [];
	      this.loadedDatesIndex = this.loadedDatesIndex || {};
	      var dateKey,
	          date = new Date(this.loadedDataFrom.getTime());

	      while (true) {
	        dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, date);
	        this.loadedDatesIndex[dateKey] = this.loadedDates.length;
	        this.loadedDates.push({
	          key: BookingUtil$$1.formatDate(this.DATE_FORMAT, date),
	          slots: {},
	          slotsCount: {}
	        });
	        date.setDate(date.getDate() + 1);

	        if (date.getTime() > this.loadedDataTo.getTime()) {
	          break;
	        }
	      }
	    }
	  }, {
	    key: "fillDataIndex",
	    value: function fillDataIndex(date, time, entityType, entityId) {
	      var dateIndex = this.loadedDatesIndex[date];

	      if (this.loadedDates[dateIndex]) {
	        if (!this.loadedDates[dateIndex].slots[time]) {
	          this.loadedDates[dateIndex].slots[time] = {};
	        }

	        if (this.loadedDates[dateIndex].slotsCount[entityType + entityId] === undefined) {
	          this.loadedDates[dateIndex].slotsCount[entityType + entityId] = 0;
	        }

	        this.loadedDates[dateIndex].slots[time][entityType + entityId] = true;
	        this.loadedDates[dateIndex].slotsCount[entityType + entityId]++;
	      }
	    }
	  }, {
	    key: "handleAccessibilityData",
	    value: function handleAccessibilityData(data, entityType) {
	      var _this5 = this;

	      if (main_core.Type.isPlainObject(data) && (entityType === 'user' || entityType === 'resource')) {
	        var _loop = function _loop(entityId) {
	          if (data.hasOwnProperty(entityId)) {
	            data[entityId].forEach(function (entry) {
	              if (!entry.from) {
	                entry.from = BookingUtil$$1.parseDate(entry.dateFrom);

	                if (entry.from) {
	                  entry.from.setSeconds(0, 0);
	                  entry.fromTimestamp = entry.from.getTime();
	                }
	              }

	              if (!entry.to) {
	                entry.to = BookingUtil$$1.parseDate(entry.dateTo);

	                if (entry.to) {
	                  if (entry.fullDay) {
	                    entry.to.setHours(23, 59, 0, 0);
	                  } else {
	                    entry.to.setSeconds(0, 0);
	                  }

	                  entry.toTimestamp = entry.to.getTime();
	                }
	              }

	              if (entry.from && entry.to) {
	                this.fillBusySlotMatrix(entry, entityType, entityId);
	              }
	            }, _this5);
	          }
	        };

	        // For each entry which has accessibility entries
	        for (var entityId in data) {
	          _loop(entityId);
	        }

	        this.accessibility[entityType] = BookingUtil$$1.mergeEx(this.accessibility[entityType], data);
	      }
	    }
	  }, {
	    key: "fillBusySlotMatrix",
	    value: function fillBusySlotMatrix(entry, entityType, entityId) {
	      if (!this.busySlotMatrix[entityType][entityId]) {
	        this.busySlotMatrix[entityType][entityId] = {};
	      }

	      var fromDate = new Date(entry.from.getTime()),
	          dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, fromDate),
	          dateToKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, entry.to),
	          timeValueFrom = fromDate.getHours() * 60 + fromDate.getMinutes(),
	          duration = Math.round((entry.toTimestamp - entry.fromTimestamp) / 60000),
	          // in minutes
	      timeValueTo = timeValueFrom + duration,
	          slots = this.getTimeSlots(),
	          count = 0,
	          i;

	      if (duration > 0) {
	        while (true) {
	          if (!this.busySlotMatrix[entityType][entityId][dateKey]) {
	            this.busySlotMatrix[entityType][entityId][dateKey] = {};
	          }

	          for (i = 0; i < slots.length; i++) {
	            if (timeValueFrom < slots[i].time + this.scale && timeValueTo > slots[i].time) {
	              this.busySlotMatrix[entityType][entityId][dateKey][slots[i].time] = true;
	              this.fillDataIndex(dateKey, slots[i].time, entityType, entityId);
	            }
	          }

	          if (dateKey === dateToKey) {
	            break;
	          } else {
	            fromDate.setDate(fromDate.getDate() + 1);
	            dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, fromDate);
	            timeValueFrom = 0;

	            if (dateKey === dateToKey) {
	              timeValueTo = entry.to.getHours() * 60 + entry.to.getMinutes();
	            } else {
	              timeValueTo = 1440; // end of the day - 24 hours
	            }
	          }

	          count++;

	          if (count > 10000) // emergency exit
	            {
	              break;
	            }
	        }
	      }
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      return this.params.field.caption;
	    }
	  }, {
	    key: "getSettingsData",
	    value: function getSettingsData() {
	      return this.params.field.settings_data || {};
	    }
	  }, {
	    key: "getUserIndex",
	    value: function getUserIndex() {
	      return this.userIndex;
	    }
	  }, {
	    key: "getFieldParams",
	    value: function getFieldParams() {
	      return this.userFieldParams;
	    }
	  }, {
	    key: "getSettings",
	    value: function getSettings() {
	      return {
	        caption: this.getCaption(),
	        data: this.getSettingsData()
	      };
	    }
	  }, {
	    key: "isUserSelectorInAutoMode",
	    value: function isUserSelectorInAutoMode() {
	      return this.usersDisplayed() && this.getSettingsData().users.show === "N";
	    }
	  }, {
	    key: "isResourceSelectorInAutoMode",
	    value: function isResourceSelectorInAutoMode() {
	      return this.resourcesDisplayed() && this.getSettingsData().resources.show === "N";
	    }
	  }, {
	    key: "autoAdjustUserSelector",
	    value: function autoAdjustUserSelector() {
	      var currentDate = this.dateControl.getValue(),
	          timeValue = this.timeControl.getValue();

	      if (main_core.Type.isDate(currentDate) && timeValue) {
	        var i,
	            loadedDate = this.loadedDates[this.loadedDatesIndex[BookingUtil$$1.formatDate(this.DATE_FORMAT, currentDate)]];

	        if (loadedDate.slots[timeValue]) {
	          for (i = 0; i < this.userControl.values.length; i++) {
	            if (!loadedDate.slots[timeValue]['user' + this.userControl.values[i]]) {
	              this.userControl.setSelectedUser(this.userControl.values[i]);
	              break;
	            }
	          }
	        }
	      }
	    }
	  }, {
	    key: "autoAdjustResourceSelector",
	    value: function autoAdjustResourceSelector() {
	      var currentDate = this.dateControl.getValue(),
	          timeValue = this.timeControl.getValue();

	      if (main_core.Type.isDate(currentDate) && timeValue) {
	        var i,
	            id,
	            loadedDate = this.loadedDates[this.loadedDatesIndex[BookingUtil$$1.formatDate(this.DATE_FORMAT, currentDate)]];

	        if (loadedDate.slots[timeValue]) {
	          for (i = 0; i < this.resourceControl.resourceList.length; i++) {
	            id = parseInt(this.resourceControl.resourceList[i].id);

	            if (!loadedDate.slots[timeValue]['resource' + id]) {
	              this.resourceControl.setSelectedResource(id);
	              break;
	            }
	          }
	        }
	      }
	    }
	  }, {
	    key: "preparaAutoSelectValues",
	    value: function preparaAutoSelectValues() {
	      var settingsData = this.getSettingsData(),
	          autoSelectUser = this.usersDisplayed() && (settingsData.users.defaultMode === 'auto' || settingsData.users.show === "N"),
	          autoSelectResource = this.resourcesDisplayed() && (settingsData.resources.defaultMode === 'auto' || settingsData.resources.show === "N"),
	          autoSelectDate = settingsData.date.start === 'free',
	          maxStepsAuto = 60,
	          date,
	          i;
	      this.selectedUserId = false;
	      this.selectedResourceId = false;
	      date = new Date(); // Walk through each date searching for free space

	      for (i = 0; i <= maxStepsAuto; i++) {
	        this.getFreeEntitiesForDate(date, {
	          autoSelectUser: autoSelectUser,
	          autoSelectResource: autoSelectResource,
	          slotsAmount: this.getDefaultDurationSlotsAmount()
	        });

	        if ((this.selectedUserId || !autoSelectUser) && (this.selectedResourceId || !autoSelectResource)) {
	          break;
	        }

	        if (!autoSelectDate) {
	          break;
	        }

	        date.setDate(date.getDate() + 1);
	      }
	    }
	  }, {
	    key: "getFreeEntitiesForDate",
	    value: function getFreeEntitiesForDate(date, params) {
	      var settingsData = this.getSettingsData(),
	          slotsAmount = params.slotsAmount || 1,
	          i,
	          userList,
	          resList;

	      if (params.autoSelectUser) {
	        userList = this.getUsersValue();

	        for (i = 0; i < userList.length; i++) {
	          if (this.checkSlotsForDate(date, slotsAmount, {
	            user: parseInt(userList[i])
	          })) {
	            this.selectedUserId = parseInt(userList[i]);
	            break;
	          }
	        }
	      }

	      if (params.autoSelectResource) {
	        resList = this.getResourceValue();

	        for (i = 0; i < resList.length; i++) {
	          if (this.checkSlotsForDate(date, slotsAmount, {
	            resources: [parseInt(resList[i])],
	            user: this.selectedUserId || null
	          })) {
	            this.selectedResourceId = parseInt(resList[i]);
	            break;
	          }
	        }
	      }
	    }
	  }, {
	    key: "displayUsersControl",
	    value: function displayUsersControl() {
	      if (this.usersDisplayed()) {
	        this.userControl = new UserSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.getSettingsData().users,
	          userIndex: this.getUserIndex(),
	          previewMode: false,
	          autoSelectDefaultValue: this.selectedUserId,
	          changeValueCallback: function (userId) {
	            this.emit('BX.Calendar.Resourcebooking.LiveFieldController:userChanged', new main_core_events.BaseEvent({
	              data: {
	                userId: userId
	              }
	            }));
	            this.refreshControlsState();
	          }.bind(this)
	        });
	        this.userControl.display();
	      }
	    }
	  }, {
	    key: "displayResourcesControl",
	    value: function displayResourcesControl() {
	      var valueIndex = {},
	          fieldParams = this.getFieldParams(),
	          settingsData = this.getSettingsData();

	      if (this.resourcesDisplayed()) {
	        this.getResourceValue().forEach(function (id) {
	          id = parseInt(id);

	          if (id > 0) {
	            valueIndex[id] = true;
	          }
	        });
	        var resourceList = [];
	        fieldParams.SELECTED_RESOURCES.forEach(function (res) {
	          res.id = parseInt(res.id);

	          if (valueIndex[res.id]) {
	            resourceList.push(res);
	          }
	        }, this);
	        this.resourceControl = new ResourceSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: {
	            show: settingsData.resources.show,
	            defaultMode: settingsData.resources.defaultMode,
	            label: settingsData.resources.label,
	            multiple: settingsData.resources.multiple,
	            value: settingsData.resources.value
	          },
	          resourceList: resourceList,
	          autoSelectDefaultValue: this.selectedResourceId,
	          changeValueCallback: function () {
	            this.emit('BX.Calendar.Resourcebooking.LiveFieldController:resourceChanged');
	            this.refreshControlsState();
	          }.bind(this)
	        });

	        if (this.selectorCanBeShown('resources')) {
	          this.resourceControl.display();
	        }
	      }
	    }
	  }, {
	    key: "displayServicesControl",
	    value: function displayServicesControl() {
	      var fieldParams = this.getFieldParams(),
	          settingsData = this.getSettingsData();

	      if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value) {
	        var dataValueRaw = this.getServicesValue();
	        this.serviceControl = new ServiceSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: settingsData.services,
	          serviceList: fieldParams.SERVICE_LIST,
	          selectedValue: dataValueRaw.length > 0 ? dataValueRaw[0] : null,
	          changeValueCallback: function () {
	            this.emit('BX.Calendar.Resourcebooking.LiveFieldController:serviceChanged');
	            this.refreshControlsState();
	          }.bind(this)
	        });

	        if (this.selectorCanBeShown('services')) {
	          this.serviceControl.display();
	        }
	      }
	    }
	  }, {
	    key: "displayDurationControl",
	    value: function displayDurationControl() {
	      var fieldParams = this.getFieldParams(),
	          settingsData = this.getSettingsData();

	      if (!this.serviceControl) {
	        this.durationControl = new DurationSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: settingsData.duration,
	          fullDay: fieldParams.FULL_DAY === 'Y',
	          changeValueCallback: function () {
	            this.emit('BX.Calendar.Resourcebooking.LiveFieldController:durationChanged');
	            this.refreshControlsState();
	          }.bind(this)
	        });

	        if (this.selectorCanBeShown('duration')) {
	          this.durationControl.display();
	        }
	      }
	    }
	  }, {
	    key: "displayDateTimeControl",
	    value: function displayDateTimeControl() {
	      var timezone = false,
	          startValue = null,
	          settingsData = this.getSettingsData(),
	          fieldParams = this.getFieldParams();
	      this.dateControl = new DateSelector({
	        outerWrap: this.DOM.innerWrap,
	        data: settingsData.date,
	        previewMode: false,
	        allowOverbooking: fieldParams.ALLOW_OVERBOOKING === "Y",
	        changeValueCallback: this.handleDateChanging.bind(this),
	        requestDataCallback: this.requestAccessibilityData.bind(this)
	      });

	      if (this.timeSelectorDisplayed()) {
	        if (fieldParams.USE_USER_TIMEZONE === 'N') {
	          var userTimezoneOffset = -new Date().getTimezoneOffset() * 60;

	          if (userTimezoneOffset !== this.timezoneOffset) {
	            timezone = fieldParams.TIMEZONE;
	          }
	        }

	        this.timeControl = new TimeSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: settingsData.time,
	          previewMode: false,
	          changeValueCallback: this.handleSelectedDateTimeChanging.bind(this),
	          timeFrom: this.timeFrom,
	          timeTo: this.timeTo,
	          timezone: timezone,
	          timezoneOffset: this.timezoneOffset,
	          timezoneOffsetLabel: this.timezoneOffsetLabel
	        });
	      }

	      this.statusControl = new StatusInformer({
	        outerWrap: this.DOM.innerWrap,
	        timezone: timezone,
	        timezoneOffsetLabel: this.timezoneOffsetLabel
	      });

	      if (this.selectorCanBeShown('date')) {
	        this.statusControl.show();

	        if (settingsData.date.start === 'free') {
	          startValue = this.getFreeDate({
	            resources: this.getSelectedResources(),
	            user: this.getSelectedUser(),
	            duration: this.getCurrentDuration()
	          });
	        }

	        this.dateControl.display({
	          selectedValue: startValue
	        });

	        if (this.timeControl && !this.timeControl.isShown()) {
	          this.timeControl.display();
	        }
	      } else {
	        this.statusControl.hide();
	      }
	    }
	  }, {
	    key: "handleDateChanging",
	    value: function handleDateChanging(date, realDate) {
	      this.emit('BX.Calendar.Resourcebooking.LiveFieldController:dateChanged');

	      if (this.timeSelectorDisplayed()) {
	        if (realDate) {
	          this.timeControl.show();
	          var timeValueFrom,
	              currentDate = this.getCurrentDate();

	          if (currentDate) {
	            timeValueFrom = currentDate.getHours() * 60 + currentDate.getMinutes();
	          }

	          this.timeControl.refresh(this.getSettingsData().time, {
	            slotIndex: this.getSlotIndex({
	              date: realDate
	            }),
	            currentDate: realDate,
	            selectedValue: timeValueFrom
	          }); // this.timeControl.refresh(
	          // 	this.getSettingsData().time,
	          // 	{
	          // 		slotIndex: this.getAvailableSlotIndex({
	          // 			date: realDate,
	          // 			resources: this.getSelectedResources(),
	          // 			user: this.getSelectedUser(),
	          // 			duration: this.getCurrentDuration()
	          // 		}),
	          // 		currentDate: realDate,
	          // 		selectedValue: timeValueFrom
	          // 	});
	        }
	      } else {
	        this.handleSelectedDateTimeChanging(null, true);
	      }

	      this.onChangeValues();
	    }
	  }, {
	    key: "handleSelectedDateTimeChanging",
	    value: function handleSelectedDateTimeChanging(value, useTimeout) {
	      if (useTimeout !== false) {
	        if (this.updateTimeStatusTimeout) {
	          this.updateTimeStatusTimeout = clearTimeout(this.updateTimeStatusTimeout);
	        }

	        this.updateTimeStatusTimeout = setTimeout(function () {
	          this.handleSelectedDateTimeChanging(value, false);
	        }.bind(this), 100);
	      } else {
	        if (this.isUserSelectorInAutoMode()) {
	          this.autoAdjustUserSelector();
	        }

	        if (this.isResourceSelectorInAutoMode()) {
	          this.autoAdjustResourceSelector();
	        }

	        this.updateStatusControl();
	        BookingUtil$$1.fireCustomEvent(window, 'crmWebFormFireResize');
	      }

	      this.onChangeValues();
	    }
	  }, {
	    key: "updateStatusControl",
	    value: function updateStatusControl() {
	      if (this.statusControl && this.selectorCanBeShown('date')) {
	        var currentDate = this.getCurrentDate();

	        if (this.dateControl.isItPastDate(currentDate)) {
	          this.statusControl.setError(main_core.Loc.getMessage('WEBF_RES_BOOKING_PAST_DATE_WARNING'));
	        } else {
	          if (this.timeSelectorDisplayed()) {
	            if (this.timeControl.hasAvailableSlots()) {
	              var timeValue = this.timeControl.getValue();
	              this.statusControl.refresh({
	                dateFrom: timeValue ? currentDate : null,
	                duration: timeValue ? this.getCurrentDuration() : null,
	                fullDay: false
	              });
	            } else {
	              this.statusControl.hide();
	            }
	          } else {
	            this.statusControl.refresh({
	              dateFrom: this.dateControl.isDateAvailable(currentDate) ? currentDate : null,
	              duration: this.getCurrentDuration(),
	              fullDay: true
	            });
	          }
	        }
	      }
	    }
	  }, {
	    key: "getFreeDate",
	    value: function getFreeDate(params) {
	      var slotsAmount = Math.ceil(params.duration / this.scale),
	          freeDate = null,
	          date = this.loadedDataFrom; // Walk through each date searching for free space

	      while (true) {
	        if (this.checkSlotsForDate(date, slotsAmount, {
	          resources: params.resources,
	          user: params.user
	        })) {
	          freeDate = date;
	          break;
	        }

	        date.setDate(date.getDate() + 1);

	        if (date.getTime() >= this.loadedDataTo.getTime()) {
	          break;
	        }
	      }

	      return freeDate;
	    }
	  }, {
	    key: "getAvailableDateIndex",
	    value: function getAvailableDateIndex(params) {
	      var userIsFree,
	          resourcesAreFree,
	          dateIndex = {};

	      if (this.timeSelectorDisplayed()) {
	        var slotsAmount = Math.ceil(params.duration / this.scale);
	        this.loadedDates.forEach(function (date) {
	          dateIndex[date.key] = this.checkSlotsForDate(date.key, slotsAmount, {
	            resources: params.resources,
	            user: params.user
	          });
	        }, this);
	      } else {
	        var i,
	            daysGap,
	            date,
	            j,
	            userKey = params.user ? 'user' + params.user : null,
	            daysAmount = Math.ceil(params.duration / 1440);
	        daysGap = 1;

	        for (i = this.loadedDates.length; i--; i >= 0) {
	          userIsFree = true;
	          resourcesAreFree = true;
	          date = this.loadedDates[i];

	          if (userKey) {
	            // All day is free for user
	            userIsFree = !date.slotsCount[userKey];
	          }

	          if (userIsFree && params.resources && params.resources.length > 0) {
	            for (j = 0; j < params.resources.length; j++) {
	              resourcesAreFree = resourcesAreFree && !date.slotsCount['resource' + params.resources[j]];

	              if (!resourcesAreFree) {
	                break;
	              }
	            }
	          }

	          if (userIsFree && resourcesAreFree) {
	            daysGap++;
	          } else {
	            daysGap = 0;
	          }

	          dateIndex[date.key] = userIsFree && resourcesAreFree && daysAmount <= daysGap;
	        }
	      }

	      return dateIndex;
	    }
	  }, {
	    key: "getSlotIndex",
	    value: function getSlotIndex(params) {
	      if (params.date) {
	        params.date = this.dateControl.getValue();
	      }

	      var slotIndex = {};

	      if (main_core.Type.isDate(params.date)) {
	        if (this.getFieldParams().ALLOW_OVERBOOKING !== "Y" && (this.isUserSelectorInAutoMode() || this.isResourceSelectorInAutoMode())) {
	          var fieldParams = this.getFieldParams();
	          var freeSlot,
	              i,
	              j,
	              time,
	              slotGap = 1,
	              todayNowTime = 0,
	              timeSlots = this.getTimeSlots(),
	              dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, params.date),
	              loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]],
	              slotsAmount = Math.ceil(this.getCurrentDuration() / this.scale);

	          if (this.checkIsTodayDate(dateKey)) {
	            var today = new Date();
	            var deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N' ? today.getTimezoneOffset() * 60 + this.timezoneOffset : 0;
	            todayNowTime = today.getHours() * 60 + today.getMinutes() + deltaOffset / 60;
	          } // Prefill slotIndex


	          timeSlots.forEach(function (slot) {
	            slotIndex[slot.time] = true;
	          }, this);

	          if (this.isUserSelectorInAutoMode()) {
	            var userList = this.getUsersValue();

	            for (i = timeSlots.length; i--; i >= 0) {
	              time = timeSlots[i].time;
	              freeSlot = false;

	              if (todayNowTime && time < todayNowTime) {
	                slotIndex[time] = false;
	                continue;
	              }

	              for (j = 0; j < userList.length; j++) {
	                if (!loadedDate.slots[time] || !loadedDate.slots[time]['user' + userList[j]]) {
	                  freeSlot = true;
	                  break;
	                }
	              }

	              slotIndex[time] = slotIndex[time] && freeSlot && slotsAmount <= slotGap;
	              slotGap = freeSlot ? slotGap + 1 : 1;
	            }
	          }

	          if (this.isResourceSelectorInAutoMode()) {
	            var resList = this.getResourceValue();

	            for (i = timeSlots.length; i--; i >= 0) {
	              time = timeSlots[i].time;
	              freeSlot = false;

	              if (todayNowTime && time < todayNowTime) {
	                slotIndex[time] = false;
	                continue;
	              }

	              for (j = 0; j < resList.length; j++) {
	                if (!loadedDate.slots[time] || !loadedDate.slots[time]['resource' + resList[j]]) {
	                  freeSlot = true;
	                  break;
	                }
	              }

	              slotIndex[time] = slotIndex[time] && freeSlot && slotsAmount <= slotGap;
	              slotGap = freeSlot ? slotGap + 1 : 1;
	            }
	          }
	        } else {
	          slotIndex = this.getAvailableSlotIndex({
	            date: params.date || this.dateControl.getValue(),
	            resources: this.getSelectedResources(),
	            user: this.getSelectedUser(),
	            duration: this.getCurrentDuration()
	          });
	        }
	      }

	      return slotIndex;
	    }
	  }, {
	    key: "getAvailableSlotIndex",
	    value: function getAvailableSlotIndex(params) {
	      var todayNowTime = 0;
	      var fieldParams = this.getFieldParams();
	      var dateKey,
	          loadedDate,
	          i,
	          j,
	          time,
	          slotGap,
	          userKey = params.user ? 'user' + params.user : null,
	          slotsAmount = Math.ceil(params.duration / this.scale),
	          userIsFree,
	          resourcesAreFree,
	          timeSlots = this.getTimeSlots(),
	          allowOverbooking = fieldParams.ALLOW_OVERBOOKING === "Y",
	          slotIndex = {}; // Prefill slotIndex

	      timeSlots.forEach(function (slot) {
	        slotIndex[slot.time] = true;
	      }, this);

	      if (main_core.Type.isDate(params.date)) {
	        dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, params.date);
	        loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]];
	        slotGap = 1;

	        if (this.checkIsTodayDate(dateKey)) {
	          var today = new Date();
	          var deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N' ? today.getTimezoneOffset() * 60 + this.timezoneOffset : 0;
	          todayNowTime = today.getHours() * 60 + today.getMinutes() + deltaOffset / 60;
	        }

	        for (i = timeSlots.length; i--; i >= 0) {
	          time = timeSlots[i].time;

	          if (todayNowTime && time < todayNowTime) {
	            slotIndex[time] = false;
	            continue;
	          }

	          if (allowOverbooking) {
	            slotIndex[time] = slotsAmount <= slotGap;
	            slotGap++;
	          } else {
	            userIsFree = true;
	            resourcesAreFree = true;

	            if (userKey) {
	              // Time is free for user
	              userIsFree = !loadedDate.slots[time] || !loadedDate.slots[time][userKey];
	            }

	            if (params.resources && params.resources.length > 0) {
	              for (j = 0; j < params.resources.length; j++) {
	                resourcesAreFree = resourcesAreFree && (!loadedDate.slots[time] || !loadedDate.slots[time]['resource' + params.resources[j]]);

	                if (!resourcesAreFree) {
	                  break;
	                }
	              }
	            }

	            slotIndex[time] = userIsFree && resourcesAreFree && slotsAmount <= slotGap;

	            if (userIsFree && resourcesAreFree) {
	              slotGap++;
	            } else {
	              slotGap = 1;
	            }
	          }
	        }
	      }

	      return slotIndex;
	    }
	  }, {
	    key: "checkSlotsForDate",
	    value: function checkSlotsForDate(date, slotsAmount, params) {
	      var userIsFree = true,
	          resourcesAreFree = true,
	          dateKey = main_core.Type.isDate(date) ? BookingUtil$$1.formatDate(this.DATE_FORMAT, date) : date;
	      params = params || {};

	      if (this.usersDisplayed() && params.user) {
	        if (this.busySlotMatrix.user[params.user] && !this.entityHasSlotsForDate({
	          entityType: 'user',
	          entityId: params.user,
	          dateKey: dateKey,
	          slotsAmount: slotsAmount
	        })) {
	          userIsFree = false;
	        }
	      }

	      if (this.resourcesDisplayed() && userIsFree && main_core.Type.isArray(params.resources) && params.resources.length > 0) {
	        params.resources.forEach(function (resourceId) {
	          if (resourcesAreFree && this.busySlotMatrix.resource[resourceId] && !this.entityHasSlotsForDate({
	            entityType: 'resource',
	            entityId: resourceId,
	            dateKey: dateKey,
	            slotsAmount: slotsAmount
	          })) {
	            resourcesAreFree = false;
	          }
	        }, this);
	      }

	      return userIsFree && resourcesAreFree;
	    }
	  }, {
	    key: "entityHasSlotsForDate",
	    value: function entityHasSlotsForDate(params) {
	      var busySlotList,
	          slots,
	          i,
	          freeSlotCount = 0,
	          hasFreeSlots = false;

	      if (this.busySlotMatrix[params.entityType][params.entityId] && this.busySlotMatrix[params.entityType][params.entityId][params.dateKey]) {
	        busySlotList = this.busySlotMatrix[params.entityType][params.entityId][params.dateKey];
	        slots = this.getTimeSlots();

	        for (i = 0; i < slots.length; i++) {
	          if (!busySlotList[slots[i].time]) {
	            freeSlotCount++;

	            if (freeSlotCount >= params.slotsAmount) {
	              hasFreeSlots = true;
	              break;
	            }
	          } else {
	            freeSlotCount = 0;
	          }
	        }
	      } else {
	        hasFreeSlots = true;
	      }

	      return hasFreeSlots;
	    }
	  }, {
	    key: "getSelectedResources",
	    value: function getSelectedResources() {
	      var result = null;

	      if (this.resourceControl) {
	        result = this.resourceControl.getSelectedValues();

	        if (main_core.Type.isArray(result) && !result.length) {
	          result = null;
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getSelectedUser",
	    value: function getSelectedUser() {
	      var result = null;

	      if (this.userControl) {
	        result = this.userControl.getSelectedUser();
	      }

	      return result;
	    }
	  }, {
	    key: "getCurrentDuration",
	    value: function getCurrentDuration() {
	      var result = null;

	      if (this.durationControl) {
	        result = this.durationControl.getSelectedValue();
	      } else if (this.serviceControl) {
	        var service = this.serviceControl.getSelectedService(true);

	        if (service && service.duration) {
	          result = parseInt(service.duration);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getDefaultDurationSlotsAmount",
	    value: function getDefaultDurationSlotsAmount() {
	      var settingsData = this.getSettingsData(),
	          fieldParams = this.getFieldParams(),
	          duration,
	          i,
	          slotsAmount;

	      if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value) {
	        var services = this.getServicesValue();

	        if (main_core.Type.isArray(fieldParams.SERVICE_LIST) && services.length > 0) {
	          for (i = 0; i < fieldParams.SERVICE_LIST.length; i++) {
	            if (BookingUtil$$1.translit(fieldParams.SERVICE_LIST[i].name) === services[0]) {
	              duration = parseInt(fieldParams.SERVICE_LIST[i].duration);
	              break;
	            }
	          }
	        }
	      } else {
	        duration = parseInt(settingsData.duration.defaultValue);
	      }

	      slotsAmount = Math.ceil(duration / this.scale);
	      return slotsAmount;
	    }
	  }, {
	    key: "getCurrentServiceName",
	    value: function getCurrentServiceName() {
	      var result = '';

	      if (this.serviceControl) {
	        var service = this.serviceControl.getSelectedService(true);

	        if (service && service.name) {
	          result = service.name;
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getCurrentDate",
	    value: function getCurrentDate() {
	      var result = null;

	      if (this.dateControl && this.dateControl.isShown()) {
	        result = this.dateControl.getValue();

	        if (this.timeSelectorDisplayed()) {
	          var hour,
	              min,
	              timeValue = this.timeControl.getValue();

	          if (timeValue) {
	            hour = Math.floor(timeValue / 60);
	            min = timeValue - hour * 60;
	            result.setHours(hour, min, 0, 0);
	          }
	        } else {
	          result.setHours(0, 0, 0, 0);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getTimeSlots",
	    value: function getTimeSlots() {
	      if (!this.slots) {
	        this.slots = [];
	        var slot;
	        var finishTime;
	        var time = this.timeFrom * 60;

	        while (time < this.timeTo * 60) {
	          finishTime = time + this.scale;
	          slot = {
	            time: time
	          };
	          this.slots.push(slot);
	          time += this.scale;
	        }
	      }

	      return this.slots;
	    }
	  }, {
	    key: "usersDisplayed",
	    value: function usersDisplayed() {
	      if (this.useUsers === undefined) {
	        this.useUsers = this.getFieldParams()['USE_USERS'] === 'Y';
	      }

	      return this.useUsers;
	    }
	  }, {
	    key: "resourcesDisplayed",
	    value: function resourcesDisplayed() {
	      if (this.useResources === undefined) {
	        var fieldParams = this.getFieldParams();
	        this.useResources = !!(fieldParams.USE_RESOURCES === 'Y' && fieldParams.SELECTED_RESOURCES);
	      }

	      return this.useResources;
	    }
	  }, {
	    key: "timeSelectorDisplayed",
	    value: function timeSelectorDisplayed() {
	      if (this.useTime === undefined) {
	        this.useTime = this.getFieldParams().FULL_DAY !== 'Y';
	      }

	      return this.useTime;
	    }
	  }, {
	    key: "selectorCanBeShown",
	    value: function selectorCanBeShown(type) {
	      var result = false;

	      if (type === 'resources') {
	        if (this.resourcesDisplayed() && !this.usersDisplayed()) {
	          result = true;
	        } else if (this.usersDisplayed()) {
	          result = this.getSelectedUser();
	        }
	      } else if (type === 'date' || type === 'services' || type === 'duration') {
	        if (this.usersDisplayed() && this.resourcesDisplayed()) {
	          result = this.getSelectedUser() && this.getSelectedResources();
	        } else if (this.usersDisplayed()) {
	          result = this.getSelectedUser();
	        } else if (this.resourcesDisplayed()) {
	          result = this.getSelectedResources();
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "checkIsTodayDate",
	    value: function checkIsTodayDate(dateKey) {
	      if (!this.todayDateKey) {
	        var today = new Date();
	        this.todayDateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, today);
	      }

	      return this.todayDateKey === dateKey;
	    }
	  }, {
	    key: "getResourceValue",
	    value: function getResourceValue() {
	      var settingsData = this.getSettingsData();
	      var value = [];

	      if (main_core.Type.isArray(settingsData.resources.value)) {
	        value = settingsData.resources.value;
	      } else if (main_core.Type.isString(settingsData.resources.value)) {
	        value = settingsData.resources.value.split('|');
	      }

	      return value;
	    }
	  }, {
	    key: "getUsersValue",
	    value: function getUsersValue() {
	      var settingsData = this.getSettingsData();
	      var value = [];

	      if (main_core.Type.isArray(settingsData.users.value)) {
	        value = settingsData.users.value;
	      } else if (main_core.Type.isString(settingsData.users.value)) {
	        value = settingsData.users.value.split('|');
	      }

	      return value;
	    }
	  }, {
	    key: "getServicesValue",
	    value: function getServicesValue() {
	      var settingsData = this.getSettingsData();
	      var value = [];

	      if (main_core.Type.isArray(settingsData.services.value)) {
	        value = settingsData.services.value;
	      } else if (main_core.Type.isString(settingsData.services.value)) {
	        value = settingsData.services.value.split('|');
	      }

	      return value;
	    }
	  }]);
	  return LiveFieldController;
	}(main_core_events.EventEmitter);

	var Translit = /*#__PURE__*/function () {
	  function Translit() {
	    babelHelpers.classCallCheck(this, Translit);
	  }

	  babelHelpers.createClass(Translit, null, [{
	    key: "run",
	    value: function run(str) {
	      var replaceChar = '_',
	          regexpEnChars = /[A-Z0-9]/i,
	          regexpSpace = /\s/,
	          maxLength = 100,
	          len = str.length,
	          result = '',
	          lastNewChar = '',
	          i;

	      for (i = 0; i < len; i++) {
	        var newChar = void 0,
	            chr = str.charAt(i);

	        if (regexpEnChars.test(chr)) {
	          newChar = chr;
	        } else if (regexpSpace.test(chr)) {
	          if (i > 0 && lastNewChar !== replaceChar) {
	            newChar = replaceChar;
	          } else {
	            newChar = '';
	          }
	        } else {
	          newChar = Translit.getChar(chr);

	          if (newChar === null) {
	            if (i > 0 && i !== len - 1 && lastNewChar !== replaceChar) {
	              newChar = replaceChar;
	            } else {
	              newChar = '';
	            }
	          }
	        }

	        if (null != newChar && newChar.length > 0) {
	          newChar = newChar.toLowerCase();
	          result += newChar;
	          lastNewChar = newChar;
	        }

	        if (result.length >= maxLength) {
	          break;
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "generateReplacementCharTable",
	    value: function generateReplacementCharTable() {
	      var separator = ',',
	          charTableFrom = (main_core.Loc.getMessage('TRANSLIT_FROM') || '').split(separator),
	          charTableTo = (main_core.Loc.getMessage('TRANSLIT_TO') || '').split(separator),
	          i,
	          len;
	      Translit.replacementCharTable = [];

	      for (i = 0, len = charTableFrom.length; i < len; i++) {
	        Translit.replacementCharTable[i] = [charTableFrom[i], charTableTo[i]];
	      }
	    }
	  }, {
	    key: "getChar",
	    value: function getChar(chr) {
	      if (Translit.replacementCharTable === null) {
	        Translit.generateReplacementCharTable();
	      }

	      for (var i = 0, len = Translit.replacementCharTable.length; i < len; i++) {
	        if (chr === Translit.replacementCharTable[i][0]) {
	          return Translit.replacementCharTable[i][1];
	        }
	      }

	      return null;
	    }
	  }]);
	  return Translit;
	}();
	babelHelpers.defineProperty(Translit, "replacementCharTable", null);

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"", "\">\n\t\t\t<svg class=\"calendar-loader-circular\"\n\t\t\t\tstyle=\"width:", "px; height:", "px;\"\n\t\t\t\tviewBox=\"25 25 50 50\">\n\t\t\t\t\t<circle class=\"calendar-loader-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t<circle class=\"calendar-loader-inner-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t</svg>\n\t\t</div>\n"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var BookingUtil$$1 = /*#__PURE__*/function () {
	  function BookingUtil$$1() {
	    babelHelpers.classCallCheck(this, BookingUtil$$1);
	  }

	  babelHelpers.createClass(BookingUtil$$1, null, [{
	    key: "getDateFormat",
	    value: function getDateFormat() {
	      if (main_core.Type.isNull(BookingUtil$$1.DATE_FORMAT)) {
	        BookingUtil$$1.DATE_FORMAT = CoreDate.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATE"));
	      }

	      return BookingUtil$$1.DATE_FORMAT;
	    }
	  }, {
	    key: "getDateTimeFormat",
	    value: function getDateTimeFormat() {
	      if (main_core.Type.isNull(BookingUtil$$1.DATETIME_FORMAT)) {
	        BookingUtil$$1.DATETIME_FORMAT = CoreDate.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATETIME"));
	      }

	      return BookingUtil$$1.DATETIME_FORMAT;
	    }
	  }, {
	    key: "getTimeFormat",
	    value: function getTimeFormat() {
	      if (main_core.Type.isNull(BookingUtil$$1.TIME_FORMAT)) {
	        var DATETIME_FORMAT = BookingUtil$$1.getDateTimeFormat();
	        var DATE_FORMAT = BookingUtil$$1.getDateFormat();

	        if (DATETIME_FORMAT.substr(0, DATE_FORMAT.length) === DATE_FORMAT) {
	          BookingUtil$$1.TIME_FORMAT = DATETIME_FORMAT.substr(DATE_FORMAT.length).trim();
	        } else {
	          BookingUtil$$1.TIME_FORMAT = CoreDate.convertBitrixFormat(CoreDate.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
	        }

	        BookingUtil$$1.TIME_FORMAT_SHORT = BookingUtil$$1.TIME_FORMAT.replace(':s', '');
	      }

	      return BookingUtil$$1.TIME_FORMAT;
	    }
	  }, {
	    key: "getTimeFormatShort",
	    value: function getTimeFormatShort() {
	      if (main_core.Type.isNull(BookingUtil$$1.TIME_FORMAT_SHORT)) {
	        BookingUtil$$1.TIME_FORMAT_SHORT = BookingUtil$$1.getTimeFormat().replace(':s', '');
	      }

	      return BookingUtil$$1.TIME_FORMAT_SHORT;
	    }
	  }, {
	    key: "formatDate",
	    value: function formatDate(format, timestamp, now, utc) {
	      if (format === null) {
	        format = BookingUtil$$1.getDateFormat();
	      }

	      if (main_core.Type.isDate(timestamp)) {
	        timestamp = timestamp.getTime() / 1000;
	      }

	      return CoreDate.format(format, timestamp, now, utc);
	    }
	  }, {
	    key: "parseDate",
	    value: function parseDate(str, bUTC, formatDate, formatDatetime) {
	      return CoreDate.parse(str, bUTC, formatDate, formatDatetime);
	    }
	  }, {
	    key: "formatTime",
	    value: function formatTime(h, m) {
	      var d = new Date();
	      d.setHours(h, m, 0);
	      return CoreDate.format(BookingUtil$$1.getTimeFormatShort(), d.getTime() / 1000);
	    }
	  }, {
	    key: "translit",
	    value: function translit(str) {
	      return main_core.Type.isString(str) ? Translit.run(str).replace(/[^a-z0-9_]/ig, "_") : str;
	    }
	  }, {
	    key: "getLoader",
	    value: function getLoader(size, className) {
	      return main_core.Tag.render(_templateObject$5(), className || 'calendar-loader', parseInt(size), parseInt(size));
	    }
	  }, {
	    key: "fireCustomEvent",
	    value: function fireCustomEvent(eventObject, eventName, eventParams, secureParams) {
	      if (window.BX && main_core.Type.isFunction(BX.onCustomEvent)) {
	        return BX.onCustomEvent(eventObject, eventName, eventParams, secureParams);
	      }
	    }
	  }, {
	    key: "bindCustomEvent",
	    value: function bindCustomEvent(eventObject, eventName, eventHandler) {
	      if (window.BX && main_core.Type.isFunction(BX.addCustomEvent)) {
	        return BX.addCustomEvent(eventObject, eventName, eventHandler);
	      }
	    }
	  }, {
	    key: "unbindCustomEvent",
	    value: function unbindCustomEvent(eventObject, eventName, eventHandler) {
	      if (window.BX && main_core.Type.isFunction(BX.removeCustomEvent)) {
	        return BX.removeCustomEvent(eventObject, eventName, eventHandler);
	      }
	    }
	  }, {
	    key: "isAmPmMode",
	    value: function isAmPmMode() {
	      return CoreDate.isAmPmMode();
	    }
	  }, {
	    key: "mergeEx",
	    value: function mergeEx() {
	      var arg = Array.prototype.slice.call(arguments);

	      if (arg.length < 2) {
	        return {};
	      }

	      var result = arg.shift();

	      for (var i = 0; i < arg.length; i++) {
	        for (var k in arg[i]) {
	          if (typeof arg[i] === "undefined" || arg[i] == null || !arg[i].hasOwnProperty(k)) {
	            continue;
	          }

	          if (main_core.Type.isPlainObject(arg[i][k]) && main_core.Type.isPlainObject(result[k])) {
	            BookingUtil$$1.mergeEx(result[k], arg[i][k]);
	          } else {
	            result[k] = main_core.Type.isPlainObject(arg[i][k]) ? main_core.Runtime.clone(arg[i][k]) : arg[i][k];
	          }
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getDurationList",
	    value: function getDurationList(fullDay) {
	      var values = [5, 10, 15, 20, 25, 30, 40, 45, 50, 60, 90, 120, 180, 240, 300, 360, 1440, 1440 * 2, 1440 * 3, 1440 * 4, 1440 * 5, 1440 * 6, 1440 * 7, 1440 * 10],
	          val,
	          i,
	          res = [];

	      for (i = 0; i < values.length; i++) {
	        val = values[i];

	        if (fullDay && val % 1440 !== 0) {
	          continue;
	        }

	        res.push({
	          value: val,
	          label: BookingUtil$$1.getDurationLabel(val)
	        });
	      }

	      return res;
	    }
	  }, {
	    key: "getDurationLabel",
	    value: function getDurationLabel(val) {
	      var label;

	      if (val % 1440 === 0) // Days
	        {
	          label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_DAY').replace('#NUM#', val / 1440);
	        } else if (val % 60 === 0 && val !== 60) // Hours
	        {
	          label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_HOUR').replace('#NUM#', val / 60);
	        } // Minutes
	      else {
	          label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_MIN').replace('#NUM#', val);
	        }

	      return label;
	    }
	  }, {
	    key: "parseDuration",
	    value: function parseDuration(value) {
	      var stringValue = value,
	          numValue = parseInt(value),
	          parsed = false,
	          dayRegexp = new RegExp('(\\d)\\s*(' + main_core.Loc.getMessage('USER_TYPE_DURATION_REGEXP_DAY') + ').*', 'ig'),
	          hourRegexp = new RegExp('(\\d)\\s*(' + main_core.Loc.getMessage('USER_TYPE_DURATION_REGEXP_HOUR') + ').*', 'ig');
	      value = value.replace(dayRegexp, function (str, num) {
	        parsed = true;
	        return num;
	      }); // It's days

	      if (parsed) {
	        value = numValue * 1440;
	      } else {
	        value = stringValue.replace(hourRegexp, function (str, num) {
	          parsed = true;
	          return num;
	        }); // It's hours

	        if (parsed) {
	          value = numValue * 60;
	        } else // Minutes
	          {
	            value = numValue;
	          }
	      }

	      return parseInt(value) || 0;
	    }
	  }, {
	    key: "getSimpleTimeList",
	    value: function getSimpleTimeList() {
	      if (main_core.Type.isNull(BookingUtil$$1.simpleTimeList)) {
	        var i,
	            res = [];

	        for (i = 0; i < 24; i++) {
	          res.push({
	            value: i * 60,
	            label: this.formatTime(i, 0)
	          });
	          res.push({
	            value: i * 60 + 30,
	            label: this.formatTime(i, 30)
	          });
	        }

	        BookingUtil$$1.simpleTimeList = res;
	      }

	      return BookingUtil$$1.simpleTimeList;
	    }
	  }, {
	    key: "adaptTimeValue",
	    value: function adaptTimeValue(timeValue) {
	      timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
	      var timeList = BookingUtil$$1.getSimpleTimeList(),
	          diff = 24 * 60,
	          ind = false,
	          i;

	      for (i = 0; i < timeList.length; i++) {
	        if (Math.abs(timeList[i].value - timeValue) < diff) {
	          diff = Math.abs(timeList[i].value - timeValue);
	          ind = i;

	          if (diff <= 15) {
	            break;
	          }
	        }
	      }

	      return timeList[ind || 0];
	    }
	  }, {
	    key: "getDayLength",
	    value: function getDayLength() {
	      return BookingUtil$$1.DAY_LENGTH;
	    }
	  }, {
	    key: "showLimitationPopup",
	    value: function showLimitationPopup() {
	      if (top.BX.getClass("BX.UI.InfoHelper")) {
	        top.BX.UI.InfoHelper.show('limit_crm_booking');
	      }
	    }
	  }]);
	  return BookingUtil$$1;
	}();
	babelHelpers.defineProperty(BookingUtil$$1, "simpleTimeList", null);
	babelHelpers.defineProperty(BookingUtil$$1, "DAY_LENGTH", 86400000);
	babelHelpers.defineProperty(BookingUtil$$1, "TIME_FORMAT", null);
	babelHelpers.defineProperty(BookingUtil$$1, "TIME_FORMAT_SHORT", null);
	babelHelpers.defineProperty(BookingUtil$$1, "DATE_FORMAT", null);
	babelHelpers.defineProperty(BookingUtil$$1, "DATETIME_FORMAT", null);

	var FieldViewControllerAbstract = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(FieldViewControllerAbstract, _Event$EventEmitter);

	  function FieldViewControllerAbstract(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, FieldViewControllerAbstract);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldViewControllerAbstract).call(this, params));
	    _this.settings = params.settings || {};
	    _this.showTitle = params.displayTitle !== false;
	    _this.title = params.title || '';
	    _this.DOM = {
	      wrap: params.wrap // outer wrap of the form

	    };
	    return _this;
	  }

	  babelHelpers.createClass(FieldViewControllerAbstract, [{
	    key: "build",
	    value: function build() {
	      this.controls = {}; // inner wrap

	      this.DOM.outerWrap = this.DOM.wrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-form'
	        }
	      }));
	      this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-inner'
	        }
	      }));

	      if (this.settings.userfieldSettings.useUsers || this.settings.userfieldSettings.useResources) {
	        this.displayTitle();
	        this.displayUsersControl();
	        this.displayResourcesControl();
	        this.displayServicesControl();
	        this.displayDurationControl();
	        this.displayDateControl();
	        this.displayTimeControl();
	      } else {
	        this.displayWarning(main_core.Loc.getMessage('WEBF_RES_BOOKING_WARNING'));
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Dom.remove(this.DOM.outerWrap);
	    }
	  }, {
	    key: "displayTitle",
	    value: function displayTitle() {
	      if (this.showTitle) {
	        this.DOM.titleWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	          props: {
	            className: 'calendar-resbook-webform-title'
	          }
	        })).appendChild(main_core.Dom.create("div", {
	          props: {
	            className: 'calendar-resbook-webform-title-text'
	          }
	        }));
	        this.updateTitle(this.title);
	      }
	    }
	  }, {
	    key: "updateTitle",
	    value: function updateTitle(title) {
	      if (this.showTitle) {
	        this.title = title;
	        main_core.Dom.adjust(this.DOM.titleWrap, {
	          text: this.title
	        });
	      }
	    }
	  }, {
	    key: "displayWarning",
	    value: function displayWarning(message) {
	      this.DOM.warningWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'ui-alert ui-alert-warning ui-alert-text-center ui-alert-icon-warning'
	        },
	        style: {
	          marginBottom: 0
	        },
	        html: '<span class="ui-alert-message">' + message + '</span>'
	      }));
	    }
	  }, {
	    key: "displayUsersControl",
	    value: function displayUsersControl() {
	      if (this.settings.userfieldSettings.useUsers) {
	        if (this.settings.data.users.value === null && main_core.Type.isArray(this.settings.userfieldSettings.users)) {
	          this.settings.data.users.value = this.settings.userfieldSettings.users;
	        }

	        this.controls.users = new UserSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.settings.data.users,
	          userIndex: this.settings.userfieldSettings.userIndex
	        });
	        this.controls.users.display();
	      }
	    }
	  }, {
	    key: "displayResourcesControl",
	    value: function displayResourcesControl() {
	      if (this.settings.userfieldSettings.useResources) {
	        if (this.settings.data.resources.value === null && main_core.Type.isArray(this.settings.userfieldSettings.resources)) {
	          this.settings.data.resources.value = [];
	          this.settings.userfieldSettings.resources.forEach(function (res) {
	            this.settings.data.resources.value.push(parseInt(res.id));
	          }, this);
	        }

	        this.controls.resources = new ResourceSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.settings.data.resources,
	          resourceList: this.settings.userfieldSettings.resources
	        });
	        this.controls.resources.display();
	      }
	    }
	  }, {
	    key: "displayServicesControl",
	    value: function displayServicesControl() {
	      if (this.settings.userfieldSettings.useServices) {
	        if (this.settings.data.services.value === null && main_core.Type.isArray(this.settings.userfieldSettings.services)) {
	          this.settings.data.services.value = [];
	          this.settings.userfieldSettings.services.forEach(function (serv) {
	            this.settings.data.services.value.push(serv.name);
	          }, this);
	        }

	        this.controls.services = new ServiceSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.settings.data.services,
	          serviceList: this.settings.userfieldSettings.services
	        });
	        this.controls.services.display();
	      }
	    }
	  }, {
	    key: "displayDurationControl",
	    value: function displayDurationControl() {
	      if (!this.settings.userfieldSettings.useServices) {
	        this.controls.duration = new DurationSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.settings.data.duration,
	          fullDay: this.settings.userfieldSettings.fullDay
	        });
	        this.controls.duration.display();
	      }
	    }
	  }, {
	    key: "displayDateControl",
	    value: function displayDateControl() {
	      this.controls.date = new DateSelector({
	        outerWrap: this.DOM.innerWrap,
	        data: this.settings.data.date
	      });
	      this.controls.date.display();
	    }
	  }, {
	    key: "displayTimeControl",
	    value: function displayTimeControl() {
	      if (!this.settings.userfieldSettings.fullDay) {
	        this.controls.time = new TimeSelector({
	          outerWrap: this.DOM.innerWrap,
	          data: this.settings.data.time
	        });
	        this.controls.time.display();
	      }
	    }
	  }, {
	    key: "refreshLayout",
	    value: function refreshLayout(settingsData) {
	      for (var k in this.controls) {
	        if (this.controls.hasOwnProperty(k) && main_core.Type.isFunction(this.controls[k].refresh)) {
	          this.controls[k].refresh(settingsData[k] || this.settings.data[k]);
	        }
	      }
	    }
	  }, {
	    key: "getInnerWrap",
	    value: function getInnerWrap() {
	      return this.DOM.innerWrap;
	    }
	  }, {
	    key: "getOuterWrap",
	    value: function getOuterWrap() {
	      return this.DOM.outerWrap;
	    }
	  }]);
	  return FieldViewControllerAbstract;
	}(main_core.Event.EventEmitter);

	var FieldViewControllerEdit = /*#__PURE__*/function (_FieldViewControllerA) {
	  babelHelpers.inherits(FieldViewControllerEdit, _FieldViewControllerA);

	  function FieldViewControllerEdit(params) {
	    babelHelpers.classCallCheck(this, FieldViewControllerEdit);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldViewControllerEdit).call(this, params));
	  }

	  return FieldViewControllerEdit;
	}(FieldViewControllerAbstract);

	var FieldViewControllerPreview = /*#__PURE__*/function (_FieldViewControllerA) {
	  babelHelpers.inherits(FieldViewControllerPreview, _FieldViewControllerA);

	  function FieldViewControllerPreview(params) {
	    babelHelpers.classCallCheck(this, FieldViewControllerPreview);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FieldViewControllerPreview).call(this, params));
	  }

	  babelHelpers.createClass(FieldViewControllerPreview, [{
	    key: "build",
	    value: function build() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(FieldViewControllerPreview.prototype), "build", this).call(this);
	      this.DOM.outerWrap.className = 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-preview calendar-resbook-webform-wrapper-dark';
	    }
	  }]);
	  return FieldViewControllerPreview;
	}(FieldViewControllerAbstract);

	var SelectInput$$1 = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(SelectInput$$1, _Event$EventEmitter);

	  function SelectInput$$1(params) {
	    var _this2;

	    babelHelpers.classCallCheck(this, SelectInput$$1);
	    _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectInput$$1).call(this, params));
	    _this2.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);

	    if (main_core.Type.isFunction(params.getValues)) {
	      _this2.getValues = params.getValues;
	      _this2.values = _this2.getValues();
	    } else {
	      _this2.values = params.values || false;
	    }

	    _this2.input = params.input;
	    _this2.defaultValue = params.defaultValue || '';
	    _this2.openTitle = params.openTitle || '';
	    _this2.className = params.className || '';
	    _this2.currentValue = params.value;
	    _this2.currentValueIndex = params.valueIndex;
	    _this2.onChangeCallback = main_core.Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
	    _this2.onAfterMenuOpen = params.onAfterMenuOpen || null;
	    _this2.zIndex = params.zIndex || 1200;
	    _this2.disabled = params.disabled;
	    _this2.editable = params.editable !== false;
	    _this2.setFirstIfNotFound = !!params.setFirstIfNotFound;

	    if (_this2.onChangeCallback) {
	      main_core.Event.bind(_this2.input, 'change', _this2.onChangeCallback);
	      main_core.Event.bind(_this2.input, 'keyup', _this2.onChangeCallback);
	    }

	    _this2.curInd = false;

	    if (main_core.Type.isArray(_this2.values)) {
	      main_core.Event.bind(_this2.input, 'click', _this2.onClick.bind(babelHelpers.assertThisInitialized(_this2)));

	      if (_this2.editable) {
	        main_core.Event.bind(_this2.input, 'focus', _this2.onFocus.bind(babelHelpers.assertThisInitialized(_this2)));
	        main_core.Event.bind(_this2.input, 'blur', _this2.onBlur.bind(babelHelpers.assertThisInitialized(_this2)));
	        main_core.Event.bind(_this2.input, 'keyup', _this2.onKeyup.bind(babelHelpers.assertThisInitialized(_this2)));
	      } else {
	        main_core.Event.bind(_this2.input, 'focus', function () {
	          this.input.blur();
	        }.bind(babelHelpers.assertThisInitialized(_this2)));
	      }

	      if (_this2.currentValueIndex === undefined && _this2.currentValue !== undefined) {
	        _this2.currentValueIndex = -1;

	        for (var i = 0; i < _this2.values.length; i++) {
	          if (parseInt(_this2.values[i].value) === parseInt(_this2.currentValue)) {
	            _this2.currentValueIndex = i;
	            break;
	          }
	        }

	        if (_this2.currentValueIndex === -1) {
	          _this2.currentValueIndex = _this2.setFirstIfNotFound ? 0 : undefined;
	        }
	      }
	    }

	    if (_this2.currentValueIndex !== undefined && _this2.values[_this2.currentValueIndex]) {
	      _this2.input.value = _this2.values[_this2.currentValueIndex].label;
	    }

	    return _this2;
	  }

	  babelHelpers.createClass(SelectInput$$1, [{
	    key: "showPopup",
	    value: function showPopup() {
	      if (this.getValues) {
	        this.values = this.getValues();
	      }

	      if (this.shown || this.disabled || !this.values.length) {
	        return;
	      }

	      var ind = 0,
	          j = 0,
	          menuItems = [],
	          i,
	          _this = this;

	      for (i = 0; i < this.values.length; i++) {
	        if (this.values[i].delimiter) {
	          menuItems.push(this.values[i]);
	        } else {
	          if (this.currentValue && this.values[i] && this.values[i].value === this.currentValue.value || this.input.value === this.values[i].label) {
	            ind = j;
	          }

	          menuItems.push({
	            id: this.values[i].value + '_' + i,
	            text: this.values[i].label,
	            onclick: this.values[i].callback || function (value, label) {
	              return function () {
	                _this.input.value = label;

	                _this.popupMenu.close();

	                _this.onChange(value, label);
	              };
	            }(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
	          });
	          j++;
	        }
	      }

	      this.popupMenu = main_popup.MenuManager.create(this.id, this.input, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 0,
	        cacheable: false
	      });
	      this.popupMenu.popupWindow.setWidth(this.input.offsetWidth - 2);
	      var menuContainer = this.popupMenu.layout.menuContainer;
	      main_core.Dom.addClass(this.popupMenu.layout.menuContainer, 'calendar-resourcebook-select-popup');
	      this.popupMenu.show();
	      var menuItem = this.popupMenu.menuItems[ind];

	      if (menuItem && menuItem.layout) {
	        menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
	      }

	      BookingUtil$$1.bindCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function () {
	        this.shown = false;
	      }.bind(this));
	      this.input.select();

	      if (main_core.Type.isFunction(this.onAfterMenuOpen)) {
	        this.onAfterMenuOpen(ind, this.popupMenu);
	      }

	      this.shown = true;
	    }
	  }, {
	    key: "closePopup",
	    value: function closePopup() {
	      main_popup.MenuManager.destroy(this.id);
	      this.shown = false;
	    }
	  }, {
	    key: "onFocus",
	    value: function onFocus() {
	      setTimeout(function () {
	        if (!this.shown) {
	          this.showPopup();
	        }
	      }.bind(this), 200);
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      if (this.shown) {
	        this.closePopup();
	      } else {
	        this.showPopup();
	      }
	    }
	  }, {
	    key: "onBlur",
	    value: function onBlur() {
	      setTimeout(this.closePopup.bind(this), 200);
	    }
	  }, {
	    key: "onKeyup",
	    value: function onKeyup() {
	      setTimeout(this.closePopup.bind(this), 50);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(value) {
	      var val = this.input.value;
	      this.emit('BX.Calendar.Resourcebooking.SelectInput:changed', new main_core.Event.BaseEvent({
	        data: {
	          selectinput: this,
	          value: val,
	          realValue: value
	        }
	      }));

	      if (this.onChangeCallback) {
	        this.onChangeCallback({
	          value: val,
	          realValue: value
	        });
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.onChangeCallback) {
	        main_core.Event.unbind(this.input, 'change', this.onChangeCallback);
	        main_core.Event.unbind(this.input, 'keyup', this.onChangeCallback);
	      }

	      main_core.Event.unbind(this.input, 'click', this.onClick.bind(this));
	      main_core.Event.unbind(this.input, 'focus', this.onFocus.bind(this));
	      main_core.Event.unbind(this.input, 'blur', this.onBlur.bind(this));
	      main_core.Event.unbind(this.input, 'keyup', this.onKeyup.bind(this));

	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }

	      main_popup.MenuManager.destroy(this.id);
	      this.shown = false;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.input.value = value;

	      if (main_core.Type.isArray(this.values)) {
	        var currentValueIndex = -1;

	        for (var i = 0; i < this.values.length; i++) {
	          if (this.values[i].value === value) {
	            currentValueIndex = i;
	            break;
	          }
	        }

	        if (currentValueIndex !== -1) {
	          this.input.value = this.values[currentValueIndex].label;
	          this.currentValueIndex = currentValueIndex;
	        }
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.input.value;
	    }
	  }]);
	  return SelectInput$$1;
	}(main_core.Event.EventEmitter);

	var CoreDate = window.BX && BX.Main && BX.Main.Date ? BX.Main.Date : null;
	var Resourcebooking = /*#__PURE__*/function () {
	  function Resourcebooking() {
	    babelHelpers.classCallCheck(this, Resourcebooking);
	  }

	  babelHelpers.createClass(Resourcebooking, null, [{
	    key: "getLiveField",
	    value: function getLiveField(params) {
	      if (!params.wrap || !main_core.Type.isDomNode(params.wrap)) {
	        throw new Error('The argument "params.wrap" must be a DOM node');
	      }

	      if (main_core.Type.isNull(CoreDate)) {
	        throw new Error('The error occured during Date extention loading');
	      }

	      var liveFieldController = new LiveFieldController(params);
	      liveFieldController.init();
	      return liveFieldController;
	    }
	  }, {
	    key: "getPreviewField",
	    value: function getPreviewField(params) {}
	  }]);
	  return Resourcebooking;
	}();

	exports.Type = main_core.Type;
	exports.Loc = main_core.Loc;
	exports.Dom = main_core.Dom;
	exports.Event = main_core.Event;
	exports.Tag = main_core.Tag;
	exports.Browser = main_core.Browser;
	exports.Text = main_core.Text;
	exports.Runtime = main_core.Runtime;
	exports.PopupManager = main_popup.PopupManager;
	exports.MenuManager = main_popup.MenuManager;
	exports.BaseEvent = main_core_events.BaseEvent;
	exports.EventEmitter = main_core_events.EventEmitter;
	exports.CoreDate = CoreDate;
	exports.BookingUtil = BookingUtil$$1;
	exports.FieldViewControllerEdit = FieldViewControllerEdit;
	exports.FieldViewControllerPreview = FieldViewControllerPreview;
	exports.SelectInput = SelectInput$$1;
	exports.Resourcebooking = Resourcebooking;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX,BX.Main,BX.Event));
//# sourceMappingURL=resourcebooking.bundle.js.map
