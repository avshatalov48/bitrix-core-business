this.BX = this.BX || {};
(function (exports,main_core,main_date,main_popup,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t;
	class ViewControlAbstract {
	  constructor(params) {
	    if (new.target === ViewControlAbstract) {
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
	  isDisplayed() {
	    return this.data.show !== 'N';
	  }
	  isShown() {
	    return this.shown;
	  }
	  display() {
	    this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	      props: {
	        className: this.classNames.wrap
	      }
	    }));
	    this.DOM.dataWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t || (_t = _`<div data-bx-resource-data-wrap="Y"></div>`)));
	    if (this.isDisplayed()) {
	      this.show({
	        animation: false
	      });
	    }
	  }
	  refresh(data) {
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
	  setDataConfig() {
	    return true;
	  }
	  refreshLabel(data) {
	    if (this.data.label !== data.label) {
	      main_core.Dom.adjust(this.DOM.labelWrap, {
	        text: data.label
	      });
	    }
	  }
	  show() {
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
	  hide() {
	    main_core.Dom.remove(this.DOM.innerWrap);
	    this.DOM.innerWrap = null;
	    this.shown = false;
	  }
	  displayControl() {}
	  showWarning(errorMessage) {
	    if (this.shown && this.DOM.wrap && this.DOM.innerWrap) {
	      main_core.Dom.addClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
	      this.displayErrorText(errorMessage || main_core.Loc.getMessage('WEBF_RES_BOOKING_REQUIRED_WARNING'));
	    }
	  }
	  hideWarning() {
	    if (this.DOM.wrap) {
	      main_core.Dom.removeClass(this.DOM.wrap, "calendar-resbook-webform-block-error");
	      if (this.DOM.errorTextWrap) {
	        main_core.Dom.remove(this.DOM.errorTextWrap);
	      }
	    }
	  }
	  displayErrorText(errorMessage) {
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
	}

	class ViewDropDownSelect {
	  constructor(params) {
	    this.id = 'viewform-dropdown-select-' + Math.round(Math.random() * 100000);
	    this.DOM = {
	      wrap: params.wrap
	    };
	    this.maxHeight = params.maxHeight;
	    this.selectAllMessage = main_core.Loc.getMessage('WEBF_RES_SELECT_ALL');
	    this.setSettings(params);
	  }
	  build() {
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
	  setSettings(params) {
	    this.handleChangesCallback = main_core.Type.isFunction(params.handleChangesCallback) ? params.handleChangesCallback : null;
	    this.values = params.values;
	    this.selected = !main_core.Type.isArray(params.selected) ? [params.selected] : params.selected;
	    this.multiple = params.multiple;
	  }
	  openPopup() {
	    if (this.isPopupShown()) {
	      return this.closePopup();
	    }
	    let menuItems = [];
	    this.values.forEach(function (item) {
	      let className = 'menu-popup-no-icon';
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
	        let checked;
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
	  closePopup() {
	    if (this.isPopupShown()) {
	      this.popup.close();
	      if (this.multiple) {
	        main_core.Event.unbind(document, 'click', this.handleClick.bind(this));
	      }
	    }
	  }
	  isPopupShown() {
	    return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() && this.popup.popupWindow.popupContainer && main_core.Dom.isShown(this.popup.popupWindow.popupContainer);
	  }
	  menuItemClick(e, menuItem) {
	    let selectAllcheckbox,
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
	  selectItem(value) {
	    if (!this.selected.includes(value.id)) {
	      this.selected.push(value.id);
	    }
	  }
	  deselectItem(value) {
	    let index = this.selected.indexOf(parseInt(value.id));
	    if (index >= 0) {
	      this.selected = this.selected.slice(0, index).concat(this.selected.slice(index + 1));
	    }
	  }
	  selectAllItemClick(e, menuItem) {
	    let target = e.target || e.srcElement;
	    if (target && (main_core.Dom.hasClass(target, "menu-popup-item") || main_core.Dom.hasClass(target, "menu-popup-item-resource-checkbox"))) {
	      let checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');
	      if (main_core.Dom.hasClass(target, "menu-popup-item")) {
	        checkbox.checked = !checkbox.checked;
	      }
	      let i,
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
	  handleClick(e) {
	    if (this.isPopupShown() && !this.popupContainer.contains(e.target || e.srcElement)) {
	      this.closePopup({
	        animation: true
	      });
	    }
	    this.handleControlChanges();
	  }
	  getSelectedValues() {
	    return this.selected;
	  }
	  setSelectedValues(values) {
	    let i,
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
	  handleControlChanges() {
	    if (this.handleChangesCallback) {
	      this.handleChangesCallback(this.getSelectedValues());
	    }
	  }
	}

	class UserSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'UserSelector';
	    this.data = params.data || {};
	    this.userList = [];
	    this.userIndex = {};
	    this.values = [];
	    this.defaultMode = 'auto';
	    this.previewMode = params.previewMode === undefined;
	    this.autoSelectDefaultValue = params.autoSelectDefaultValue;
	    this.changeValueCallback = params.changeValueCallback;
	    this.handleSettingsData(this.data, params.userIndex);
	  }
	  displayControl() {
	    this.selectedValue = this.getSelectedUser();
	    this.dropdownSelect = new ViewDropDownSelect({
	      wrap: this.DOM.controlWrap,
	      values: this.userList,
	      selected: this.selectedValue,
	      handleChangesCallback: this.handleChanges.bind(this)
	    });
	    this.dropdownSelect.build();
	  }
	  refresh(data, userIndex) {
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
	  handleSettingsData(data, userIndex) {
	    if (main_core.Type.isPlainObject(userIndex)) {
	      for (let id in userIndex) {
	        if (userIndex.hasOwnProperty(id)) {
	          this.userIndex[id] = userIndex[id];
	        }
	      }
	    }
	    this.defaultMode = this.data.defaultMode === 'none' ? 'none' : 'auto';
	    let dataValue = [];
	    this.userList = [];
	    if (this.data.value) {
	      let dataValueRaw = main_core.Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
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
	  getSelectedUser() {
	    let selected = null;
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
	  setSelectedUser(userId) {
	    if (this.dropdownSelect) {
	      this.dropdownSelect.setSelectedValues([userId]);
	    } else {
	      this.autoSelectDefaultValue = parseInt(userId);
	    }
	  }
	  handleChanges(selectedValues) {
	    if (!this.previewMode && main_core.Type.isFunction(this.changeValueCallback)) {
	      this.changeValueCallback(selectedValues[0] || null);
	    }
	  }
	}

	class ResourceSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'ResourceSelector';
	    this.data = params.data;
	    this.allResourceList = params.resourceList;
	    this.autoSelectDefaultValue = params.autoSelectDefaultValue;
	    this.changeValueCallback = params.changeValueCallback;
	    this.handleSettingsData(params.data);
	  }
	  handleSettingsData(data) {
	    if (!main_core.Type.isArray(data.value)) {
	      let dataValue = [];
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
	  displayControl() {
	    this.dropdownSelect = new ViewDropDownSelect({
	      wrap: this.DOM.controlWrap,
	      values: this.resourceList,
	      selected: this.selectedValues,
	      multiple: this.data.multiple === 'Y',
	      handleChangesCallback: this.changeValueCallback
	    });
	    this.dropdownSelect.build();
	  }
	  refresh(data) {
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
	  getSelectedValues() {
	    let selected = null;
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
	  setSelectedValues(selectedValues) {
	    this.selectedValues = selectedValues;
	  }
	  setSelectedResource(id) {
	    if (this.dropdownSelect) {
	      this.dropdownSelect.setSelectedValues([id]);
	    } else {
	      this.autoSelectDefaultValue = parseInt(id);
	      this.selectedValues = [id];
	    }
	  }
	}

	class ServiceSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'ServiceSelector';
	    this.data = params.data;
	    this.serviceList = [];
	    this.allServiceList = [];
	    if (main_core.Type.isArray(params.serviceList)) {
	      params.serviceList.forEach(service => {
	        if (main_core.Type.isString(name)) {
	          service.name = service.name.trim();
	        }
	        this.allServiceList.push(service);
	      });
	    }
	    this.values = [];
	    this.changeValueCallback = main_core.Type.isFunction(params.changeValueCallback) ? params.changeValueCallback : null;
	    if (params.selectedValue) {
	      this.setSelectedService(params.selectedValue);
	    }
	    this.handleSettingsData(this.data);
	  }
	  displayControl() {
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
	  refresh(data) {
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
	  handleSettingsData() {
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
	      let dataValueRaw = main_core.Type.isArray(this.data.value) ? this.data.value : this.data.value.split('|');
	      dataValueRaw.forEach(function (id) {
	        let service = this.serviceIndex[this.prepareServiceId(id)];
	        if (main_core.Type.isPlainObject(service) && main_core.Type.isString(service.name) && service.name.trim() !== '') {
	          this.serviceList.push({
	            id: this.prepareServiceId(service.name),
	            title: service.name + ' - ' + BookingUtil$$1.getDurationLabel(service.duration)
	          });
	        }
	      }, this);
	    }
	  }
	  setSelectedService(serviceName) {
	    this.selectedService = serviceName;
	  }
	  getSelectedService(getMeta) {
	    return getMeta !== true ? this.selectedService || null : this.serviceIndex[this.prepareServiceId(this.selectedService)] || null;
	  }
	  prepareServiceId(str) {
	    return BookingUtil$$1.translit(str);
	  }
	}

	class DurationSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'DurationSelector';
	    this.data = params.data;
	    this.durationList = BookingUtil$$1.getDurationList(params.fullDay);
	    this.changeValueCallback = params.changeValueCallback;
	    this.defaultValue = params.defaultValue || this.data.defaultValue;
	    this.handleSettingsData(params.data);
	  }
	  handleSettingsData() {
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
	  displayControl() {
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
	  refresh(data) {
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
	  getSelectedValue() {
	    let duration = null;
	    if (this.durationControl) {
	      duration = BookingUtil$$1.parseDuration(this.durationControl.getValue());
	    } else {
	      duration = parseInt(this.defaultValue);
	    }
	    return duration;
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13;
	class DateSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: null
	    };
	    this.data = params.data || {};
	    this.changeValueCallback = params.changeValueCallback;
	    this.requestDataCallback = params.requestDataCallback;
	    this.previewMode = params.previewMode === undefined;
	    this.allowOverbooking = params.allowOverbooking;
	    this.setDataConfig();
	    this.displayed = true;
	  }
	  display(params) {
	    params = params || {};
	    this.setDateIndex(params.availableDateIndex);
	    this.setCurrentDate(params.selectedValue);
	    this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`<div class="calendar-resbook-webform-block"></div>`)));
	    this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t2 || (_t2 = _$1`<div class="calendar-resbook-webform-block-inner"></div>`)));
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
	  refresh(data, params) {
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
	  setDataConfig() {
	    let style = this.data.style === 'line' ? 'line' : 'popup',
	      // line|popup
	      start = this.data.start === 'today' ? 'today' : 'free',
	      configWasChanged = this.style !== style || this.start !== start;
	    this.style = style;
	    this.start = start;
	    return configWasChanged;
	  }
	  hide() {
	    main_core.Dom.remove(this.DOM.innerWrap);
	    this.DOM.innerWrap = null;
	  }
	  displayControl() {
	    this.DOM.controlWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t3 || (_t3 = _$1`<div class="calendar-resbook-webform-block-date"></div>`)));
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
	  setCurrentDate(date) {
	    if (main_core.Type.isDate(date)) {
	      this.currentDate = date;
	    }
	  }
	  setDateIndex(availableDateIndex) {
	    if (main_core.Type.isPlainObject(availableDateIndex)) {
	      this.availableDateIndex = availableDateIndex;
	    }
	  }
	  isDateLoaded(date) {
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
	  isDateAvailable(date) {
	    if (this.previewMode || this.allowOverbooking) {
	      return true;
	    }
	    if (main_core.Type.isDate(date) && !this.isItPastDate(date) && this.availableDateIndex) {
	      let dateKey = BookingUtil$$1.formatDate(null, date);
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
	  isItPastDate(date) {
	    if (main_core.Type.isDate(date)) {
	      let nowDate = new Date(),
	        checkDate = new Date(date.getTime());
	      nowDate.setHours(0, 0, 0, 0);
	      checkDate.setHours(0, 0, 0, 0);
	      return checkDate.getTime() < nowDate.getTime();
	    }
	    return false;
	  }
	  refreshCurrentValue() {
	    this.onChange(this.getDisplayedValue());
	  }
	  getDisplayedValue() {
	    return this.style === 'popup' ? this.popupSateControl.getValue() : this.lineDateControl.getValue();
	  }
	  onChange(date) {
	    if (main_core.Type.isFunction(this.changeValueCallback)) {
	      let realDate = date;
	      if (!main_core.Type.isDate(realDate)) {
	        realDate = this.getDisplayedValue();
	      }
	      this.setCurrentDate(date);
	      this.changeValueCallback(date, realDate, this.isDateAvailable(realDate));
	    }
	  }
	  getValue() {
	    if (!this.currentDate) {
	      this.currentDate = new Date();
	    }
	    return this.currentDate;
	  }
	}
	class PopupDateSelector {
	  constructor(params) {
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
	  build() {
	    this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-block-strip'
	      },
	      events: {
	        click: this.handleClick.bind(this)
	      }
	    }));
	    this.DOM.valueInput = this.DOM.wrap.appendChild(main_core.Tag.render(_t4 || (_t4 = _$1`<input type="hidden" 
value=""/>`)));
	    this.DOM.previousArrow = this.DOM.wrap.appendChild(main_core.Tag.render(_t5 || (_t5 = _$1`<span class="calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev" data-bx-resbook-date-meta="previous"/>`)));
	    this.DOM.stateWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t6 || (_t6 = _$1`<span class="calendar-resbook-webform-block-strip-text" data-bx-resbook-date-meta="calendar"/>`)));
	    this.DOM.stateWrapDate = this.DOM.stateWrap.appendChild(main_core.Tag.render(_t7 || (_t7 = _$1`<span class="calendar-resbook-webform-block-strip-date"/>`)));
	    this.DOM.stateWrapDay = this.DOM.stateWrap.appendChild(main_core.Tag.render(_t8 || (_t8 = _$1`<span class="calendar-resbook-webform-block-strip-day"/>`)));
	    this.DOM.nextArrow = this.DOM.wrap.appendChild(main_core.Tag.render(_t9 || (_t9 = _$1`<span class="calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next" data-bx-resbook-date-meta="next"/>`)));
	  }
	  getValue() {
	    return this.value;
	  }
	  setValue(dateValue) {
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
	  handleClick(e) {
	    let dateValue,
	      target = e.target || e.srcElement;
	    if (target.hasAttribute('data-bx-resbook-date-meta') || (target = target.closest('[data-bx-resbook-date-meta]'))) {
	      let dateMeta = target.getAttribute('data-bx-resbook-date-meta');
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
	  openCalendarPopup() {
	    this.DOM.valueInput.value = BookingUtil$$1.formatDate(null, this.getValue().getTime() / 1000);
	    if (PopupDateSelector.isExternalDatePickerEnabled()) {
	      this.openExternalDatePicker();
	    } else {
	      this.openBxCalendar();
	    }
	  }
	  openBxCalendar() {
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
	  handleCalendarClose() {
	    this.setValue(BookingUtil$$1.parseDate(this.DOM.valueInput.value));
	  }
	  static isExternalDatePickerEnabled() {
	    if (main_core.Type.isNull(PopupDateSelector.externalDatePickerIsEnabled)) {
	      PopupDateSelector.externalDatePickerIsEnabled = !!(window.BX && BX.UI && BX.UI.Vue && BX.UI.Vue.Components && BX.UI.Vue.Components.DatePick);
	    }
	    return PopupDateSelector.externalDatePickerIsEnabled;
	  }
	  openExternalDatePicker() {
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
	}
	PopupDateSelector.externalDatePickerIsEnabled = null;
	class LineDateSelector {
	  constructor(params) {
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
	  build() {
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
	    this.DOM.controlStaticWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t10 || (_t10 = _$1`<div class="calendar-resbook-webform-block-date-range-static-wrap" 
></div>`)));
	    this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(main_core.Tag.render(_t11 || (_t11 = _$1`<div class="calendar-resbook-webform-block-date-range-inner-wrap" 
></div>`)));
	    this.DOM.valueInput = this.DOM.wrap.appendChild(main_core.Tag.render(_t12 || (_t12 = _$1`<input type="hidden" 
value=""/>`)));
	    this.fillDays();
	    this.initCustomScroll();
	  }
	  fillDays() {
	    let i,
	      startDate = this.getStartLoadDate(),
	      date = new Date(startDate.getTime());
	    for (i = 0; i < this.DAYS_DISPLAY_SIZE; i++) {
	      this.addDateSlot(date);
	      date.setDate(date.getDate() + 1);
	    }
	    this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);
	  }
	  addDateSlot(date) {
	    let dateCode = BookingUtil$$1.formatDate('Y-m-d', date.getTime() / 1000);
	    this.dayNodeIndex[dateCode] = new Date(date.getTime());
	    this.DOM.dayNodes[dateCode] = this.DOM.controlInnerWrap.appendChild(main_core.Dom.create("div", {
	      attrs: {
	        className: 'calendar-resbook-webform-block-date-item' + (this.isDateAvailable(date) ? '' : ' calendar-resbook-webform-block-date-item-off'),
	        'data-bx-resbook-date-meta': dateCode
	      },
	      html: '<div class="calendar-resbook-webform-block-date-item-inner">' + '<span class="calendar-resbook-webform-block-date-number">' + BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DATE'), date) + '</span>' + '<span class="calendar-resbook-webform-block-date-day">' + BookingUtil$$1.formatDate(main_core.Loc.getMessage('WEBF_RES_DATE_FORMAT_DAY_OF_THE_WEEK'), date) + '</span>' + '</div>'
	    }));
	  }
	  refreshDateAvailability() {
	    for (let dateCode in this.DOM.dayNodes) {
	      if (this.DOM.dayNodes.hasOwnProperty(dateCode)) {
	        if (this.isDateAvailable(this.dayNodeIndex[dateCode])) {
	          main_core.Dom.removeClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
	        } else {
	          main_core.Dom.addClass(this.DOM.dayNodes[dateCode], 'calendar-resbook-webform-block-date-item-off');
	        }
	      }
	    }
	  }
	  handleClick(e) {
	    let dateValue,
	      target = e.target || e.srcElement;
	    if (target.hasAttribute('data-bx-resbook-date-meta') || (target = target.closest('[data-bx-resbook-date-meta]'))) {
	      let dateMeta = target.getAttribute('data-bx-resbook-date-meta');
	      if (dateMeta && (dateValue = BookingUtil$$1.parseDate(dateMeta, false, 'YYYY-MM-DD'))) {
	        this.setValue(dateValue);
	      }
	    }
	  }
	  setValue(dateValue) {
	    if (main_core.Type.isDate(dateValue)) {
	      this.value = dateValue;
	      let dayNode = this.getDayNode(dateValue);
	      if (dayNode) {
	        this.setSelected(dayNode);
	      }
	      this.onChange(this.value);
	    }
	  }
	  getValue() {
	    return this.value;
	  }
	  getDayNode(dateValue) {
	    let dateCode = BookingUtil$$1.formatDate('Y-m-d', dateValue.getTime() / 1000);
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
	  setSelected(dayNode) {
	    if (this.currentSelected) {
	      main_core.Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-date-item-select');
	    }
	    this.currentSelected = dayNode;
	    main_core.Dom.addClass(dayNode, 'calendar-resbook-webform-block-date-item-select');
	  }
	  getStartLoadDate() {
	    if (!this.startLoadDate) {
	      this.startLoadDate = new Date();
	    } else {
	      this.startLoadDate.setDate(this.startLoadDate.getDate() + this.DAYS_DISPLAY_SIZE);
	    }
	    return this.startLoadDate;
	  }
	  initCustomScroll() {
	    let arrowWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t13 || (_t13 = _$1`<div class="calendar-resbook-webform-block-arrow-container" 
></div>`)));
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
	  handleNextArrowClick() {
	    this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
	    this.checkScrollPosition();
	  }
	  handlePreletrowClick() {
	    this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
	    this.checkScrollPosition();
	  }
	  mousewheelScrollHandler(e) {
	    e = e || window.event;
	    let delta = e.deltaY || e.detail || e.wheelDelta;
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
	  checkScrollPosition() {
	    if (this.outerWidth <= this.innerWidth) {
	      this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft === 0 ? 'none' : '';
	      //this.DOM.rightArrow.style.display = (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) ? 'none' : '';
	      if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) {
	        this.fillDays();
	      }
	    }
	    this.updateMonthTitle();
	  }
	  updateMonthTitle() {
	    if (!this.dayNodeOuterWidth) {
	      this.dayNodeOuterWidth = this.DOM.controlInnerWrap.childNodes[1].offsetLeft - this.DOM.controlInnerWrap.childNodes[0].offsetLeft;
	      if (!this.dayNodeOuterWidth) {
	        return setTimeout(this.updateMonthTitle.bind(this), 100);
	      }
	    }
	    let monthFrom,
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
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1,
	  _t11$1;
	class TimeSelector extends ViewControlAbstract {
	  constructor(params) {
	    super(params);
	    this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: null
	    };
	    this.data = params.data || {};
	    this.setDataConfig();
	    this.timeFrom = this.data.timeFrom || params.timeFrom || 7;
	    if (params.timeFrom !== undefined) {
	      this.timeFrom = params.timeFrom;
	    }
	    this.timeTo = this.data.timeTo || 20;
	    if (params.timeTo !== undefined) {
	      this.timeTo = params.timeTo;
	    }
	    this.SLOTS_ROW_AMOUNT = 6;
	    this.id = 'time-selector-' + Math.round(Math.random() * 1000);
	    this.popupSelectId = this.id + '-select-popup';
	    this.previewMode = params.previewMode === undefined;
	    this.changeValueCallback = params.changeValueCallback;
	    this.timezone = params.timezone;
	    this.timezoneOffset = params.timezoneOffset;
	    this.timezoneOffsetLabel = params.timezoneOffsetLabel;
	    this.timeMidday = 12;
	    this.timeEvening = 17;
	    this.displayed = true;
	  }
	  setDataConfig() {
	    let style = this.data.style === 'select' ? 'select' : 'slots',
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
	  display() {
	    this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$2 || (_t$2 = _$2`<div class="calendar-resbook-webform-block"></div>`)));
	    this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$2`<div class="calendar-resbook-webform-block-inner"></div>`)));
	    if (this.data.label) {
	      this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-title'
	        },
	        text: this.data.label + '*'
	      }));
	      if (this.timezone) {
	        this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$2`<div class="calendar-resbook-webform-block-title-timezone"></div>`)));
	        main_core.Dom.adjust(this.DOM.timezoneLabelWrap, {
	          html: main_core.Loc.getMessage('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)
	        });
	      }
	    }
	    this.displayControl();
	    this.setValue(this.getValue());
	    this.shown = true;
	  }
	  refresh(data, params) {
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
	          this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(main_core.Tag.render(_t4$1 || (_t4$1 = _$2`<div class="calendar-resbook-webform-block-title-timezone"></div>`)));
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
	  setSlotIndex(slotIndex) {
	    if (main_core.Type.isPlainObject(slotIndex)) {
	      this.availableSlotIndex = slotIndex;
	    }
	  }
	  setCurrentValue(timeValue) {
	    if (timeValue && (this.previewMode || this.availableSlotIndex[timeValue])) {
	      this.setValue(timeValue);
	    } else {
	      this.setValue(null);
	    }
	  }
	  showEmptyWarning() {
	    if (this.DOM.labelWrap) {
	      this.DOM.labelWrap.style.display = 'none';
	    }
	    if (!this.DOM.warningWrap) {
	      this.DOM.warningTextNode = main_core.Tag.render(_t5$1 || (_t5$1 = _$2`<span class="calendar-resbook-webform-block-notice-date"/>`));
	      this.DOM.warningWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-block-notice'
	        },
	        children: [main_core.Tag.render(_t6$1 || (_t6$1 = _$2`<span class="calendar-resbook-webform-block-notice-icon"/>`)), this.DOM.warningTextNode, main_core.Dom.create("span", {
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
	  hideEmptyWarning() {
	    this.noSlotsAvailable = false;
	    if (this.DOM.labelWrap) {
	      this.DOM.labelWrap.style.display = '';
	    }
	    if (this.DOM.warningWrap) {
	      this.DOM.warningWrap.style.display = 'none';
	    }
	  }
	  displayControl() {
	    let slotsInfo = this.getSlotsInfo();
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
	  hide() {
	    if (this.DOM.innerWrap) {
	      this.DOM.innerWrap.style.display = 'none';
	    }
	  }
	  show() {
	    if (this.DOM.innerWrap) {
	      this.DOM.innerWrap.style.display = '';
	    }
	  }
	  createSlotsControl() {
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
	    this.DOM.controlStaticWrap = this.DOM.controlWrap.appendChild(main_core.Tag.render(_t7$1 || (_t7$1 = _$2`<div class="calendar-resbook-webform-block-time-static-wrap"></div>`)));
	    this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(main_core.Tag.render(_t8$1 || (_t8$1 = _$2`<div class="calendar-resbook-webform-block-time-inner-wrap"></div>`)));
	    let itemsInColumn,
	      maxColumnNumber = 3,
	      parts = {},
	      itemNumber = 0,
	      innerWrap;

	    // FilterSlots
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
	        parts[slot.partOfTheDay].itemsWrap = parts[slot.partOfTheDay].wrap.appendChild(main_core.Tag.render(_t9$1 || (_t9$1 = _$2`<div class="calendar-resbook-webform-block-col-list"></div>`)));
	        if (parts[slot.partOfTheDay].items.length > maxColumnNumber * itemsInColumn) {
	          itemsInColumn = Math.ceil(parts[slot.partOfTheDay].items.length / maxColumnNumber);
	        }
	      }
	      if (itemNumber % itemsInColumn === 0) {
	        innerWrap = parts[slot.partOfTheDay].itemsWrap.appendChild(main_core.Tag.render(_t10$1 || (_t10$1 = _$2`<div class="calendar-resbook-webform-block-col-list-inner"></div>`)));
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
	    let k;
	    for (k in parts) {
	      if (parts.hasOwnProperty(k) && parts[k].itemsAmount > 0) {
	        this.DOM.controlInnerWrap.appendChild(parts[k].wrap);
	      }
	    }
	    this.initCustomScrollForSlots();
	  }
	  createSelectControl() {
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
	  setValue(value) {
	    let slot = this.getSlotByTime(value);
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
	  getValue() {
	    if (!this.value && (this.previewMode || this.style === 'select')) {
	      this.value = this.slots[0].time;
	    }
	    return this.value;
	  }
	  hasAvailableSlots() {
	    return !this.noSlotsAvailable;
	  }
	  getTimeTextBySlot(slot) {
	    return slot.fromTime + (this.showFinishTime ? ' - ' + slot.toTime : '');
	  }
	  getSlotByTime(time) {
	    return main_core.Type.isArray(this.slots) ? this.slots.find(function (slot) {
	      return parseInt(slot.time) === parseInt(time);
	    }) : null;
	  }
	  handleClick(e) {
	    let target = e.target || e.srcElement;
	    if (target.hasAttribute('data-bx-resbook-time-meta') || (target = target.closest('[data-bx-resbook-time-meta]'))) {
	      let meta = target.getAttribute('data-bx-resbook-time-meta');
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
	  getSlotsInfo() {
	    let slots = [],
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
	  initCustomScrollForSlots() {
	    let arrowWrap = this.DOM.controlWrap.appendChild(main_core.Tag.render(_t11$1 || (_t11$1 = _$2`<div class="calendar-resbook-webform-block-arrow-container" />`)));
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
	  handleNextArrowClick() {
	    this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
	    this.checkSlotsScroll();
	  }
	  handlePreletrowClick() {
	    this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
	    this.checkSlotsScroll();
	  }
	  mousewheelScrollHandler(e) {
	    e = e || window.event;
	    let delta = e.deltaY || e.detail || e.wheelDelta;
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
	  checkSlotsScroll() {
	    if (this.outerWidth <= this.innerWidth) {
	      this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft ? '' : 'none';
	      if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft) {
	        this.DOM.rightArrow.style.display = 'none';
	      } else {
	        this.DOM.rightArrow.style.display = '';
	      }
	    }
	  }
	  openSelectPopup() {
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
	  closeSelectPopup() {
	    if (this.isSelectPopupShown()) {
	      this.popup.close();
	      main_core.Event.unbind(document, 'click', this.handleClick.bind(this));
	    }
	  }
	  isSelectPopupShown() {
	    return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown();
	  }
	  getTimeSelectItems() {
	    let menuItems = [];
	    this.slots.forEach(function (slot) {
	      if (this.showOnlyFree && slot.booked) {
	        return;
	      }
	      let className = 'menu-popup-no-icon';
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
	  menuItemClick(e, menuItem) {
	    if (menuItem && menuItem.dataset && menuItem.dataset.value) {
	      if (!menuItem.dataset.booked) {
	        this.setValue(menuItem.dataset.value);
	      }
	    }
	    this.closeSelectPopup();
	  }
	  getSlotNode(time) {
	    let i,
	      slotNodes = this.DOM.controlInnerWrap.querySelectorAll('.calendar-resbook-webform-block-col-item');
	    for (i = 0; i < slotNodes.length; i++) {
	      if (parseInt(slotNodes[i].getAttribute('data-bx-resbook-slot')) === parseInt(time)) {
	        return slotNodes[i];
	      }
	    }
	    return null;
	  }
	  setSelected(slotNode) {
	    if (main_core.Type.isDomNode(slotNode)) {
	      if (this.currentSelected) {
	        main_core.Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-col-item-select');
	      }
	      this.currentSelected = slotNode;
	      main_core.Dom.addClass(slotNode, 'calendar-resbook-webform-block-col-item-select');
	    }
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$2,
	  _t3$2;
	class StatusInformer {
	  constructor(params) {
	    this.DOM = {
	      outerWrap: params.outerWrap
	    };
	    this.timezone = params.timezone;
	    this.timezoneOffsetLabel = params.timezoneOffsetLabel;
	    this.shown = false;
	    this.built = false;
	  }
	  isShown() {
	    return this.shown;
	  }
	  build() {
	    this.DOM.wrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$3 || (_t$3 = _$3`<div class="calendar-resbook-webform-block-result" style="display: none" 
></div>`)));
	    this.DOM.innerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t2$2 || (_t2$2 = _$3`<div class="calendar-resbook-webform-block-result-inner"></div>`)));
	    this.DOM.labelWrap = this.DOM.innerWrap.appendChild(main_core.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-block-result-text'
	      },
	      text: main_core.Loc.getMessage('WEBF_RES_BOOKING_STATUS_LABEL')
	    }));
	    this.DOM.statusWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t3$2 || (_t3$2 = _$3`<div class="calendar-resbook-webform-block-result-value"></div>`)));
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
	  refresh(params) {
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
	  getStatusText(params) {
	    let dateFrom = params.dateFrom,
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
	  hide() {
	    if (this.built && this.shown) {
	      this.DOM.wrap.style.display = 'none';
	      this.shown = false;
	    }
	  }
	  show() {
	    if (this.built && !this.shown) {
	      this.DOM.wrap.style.display = '';
	      this.shown = true;
	    }
	  }
	  setError(message) {
	    if (this.DOM.labelWrap) {
	      this.DOM.labelWrap.style.display = 'none';
	    }
	    main_core.Dom.addClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	    main_core.Dom.adjust(this.DOM.statusWrap, {
	      text: message
	    });
	  }
	  isErrorSet() {
	    return this.shown && main_core.Dom.hasClass(this.DOM.wrap, 'calendar-resbook-webform-block-result-error');
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$3,
	  _t3$3,
	  _t4$2,
	  _t5$2,
	  _t6$2;
	class LiveFieldController extends main_core_events.EventEmitter {
	  constructor(params) {
	    super(params);
	    this.setEventNamespace('BX.Calendar.LiveFieldController');
	    this.params = params;
	    this.actionAgent = params.actionAgent || BX.ajax.runAction;
	    this.timeFrom = params.timeFrom || 7;
	    this.timeTo = params.timeTo || 20;
	    this.inputName = params.field.name + '[]';
	    this.DATE_FORMAT = BookingUtil$$1.getDateFormat();
	    this.DATETIME_FORMAT = BookingUtil$$1.getDateTimeFormat();
	    this.userIndex = null;
	    this.timezoneOffset = null;
	    this.timezoneOffsetLabel = null;
	    this.userFieldParams = null;
	    this.loadedDates = [];
	    this.externalSiteContext = main_core.Type.isFunction(params.actionAgent);
	    this.accessibility = {
	      user: {},
	      resource: {}
	    };
	    this.busySlotMatrix = {
	      user: {},
	      resource: {}
	    };
	    this.DOM = {
	      wrap: this.params.wrap,
	      valueInputs: []
	    };
	  }
	  init() {
	    const settingsData = this.getSettingsData();
	    if (!settingsData.users || !settingsData.resources) {
	      throw new Error('Can\'t init resourcebooking field, because \'settings_data\' parameter is not provided or has incorrect structure');
	      return;
	    }
	    this.scale = parseInt(settingsData.time && settingsData.time.scale ? settingsData.time.scale : 60, 10);
	    this.DOM.outerWrap = this.DOM.wrap.appendChild(main_core.Tag.render(_t$4 || (_t$4 = _$4`<div class="calendar-resbook-webform-wrapper"></div>`)));
	    this.showMainLoader();
	    this.requireFormData().then(() => {
	      this.hideMainLoader();
	      this.buildFormControls();
	      this.onChangeValues();
	    });
	  }
	  check() {
	    let result = true;
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
	  buildFormControls() {
	    this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t2$3 || (_t2$3 = _$4`<div class="calendar-resbook-webform-inner"></div>`)));
	    this.DOM.inputsWrap = this.DOM.innerWrap.appendChild(main_core.Tag.render(_t3$3 || (_t3$3 = _$4`<div></div>`)));
	    if (!this.getFieldParams()) {
	      this.statusControl = new StatusInformer({
	        outerWrap: this.DOM.innerWrap
	      });
	      this.statusControl.refresh({});
	      this.statusControl.setError('[UF_NOT_FOUND] ' + main_core.Loc.getMessage('WEBF_RES_BOOKING_UF_WARNING'));
	    } else {
	      if (this.externalSiteContext && BX.ZIndexManager) {
	        const stack = BX.ZIndexManager.getOrAddStack(document.body);
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
	  refreshControlsState() {
	    if (this.selectorCanBeShown('resources') && this.resourceControl && !this.resourceControl.isShown()) {
	      this.resourceControl.display();
	    }

	    // Show services
	    if (this.selectorCanBeShown('services') && this.serviceControl && !this.serviceControl.isShown()) {
	      this.serviceControl.display();
	    }

	    // Show duration
	    if (this.selectorCanBeShown('duration') && this.durationControl && !this.durationControl.isShown()) {
	      this.durationControl.display();
	    }
	    let settingsData = this.getSettingsData();
	    // Show date & time control
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
	        let startValue;
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
	  onChangeValues() {
	    let allValuesValue = [],
	      dateFromValue = '',
	      dateFrom = this.getCurrentDate(),
	      duration = this.getCurrentDuration() * 60,
	      // Duration in minutes
	      serviceName = this.getCurrentServiceName(),
	      entries = [];

	    // Clear inputs
	    main_core.Dom.clean(this.DOM.inputsWrap);
	    this.DOM.valueInputs = [];
	    if (main_core.Type.isDate(dateFrom) && !this.statusControl.isErrorSet()) {
	      let resources = this.getSelectedResources();
	      if (main_core.Type.isArray(resources)) {
	        resources.forEach(function (resourceId) {
	          entries = entries.concat({
	            type: 'resource',
	            id: resourceId
	          });
	        });
	      }
	      let selectedUser = this.getSelectedUser();
	      if (selectedUser) {
	        entries = entries.concat({
	          type: 'user',
	          id: selectedUser
	        });
	      }
	      dateFromValue = BookingUtil$$1.formatDate(this.DATETIME_FORMAT, dateFrom.getTime() / 1000);
	      entries.forEach(function (entry) {
	        let value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
	        allValuesValue.push(value);
	        this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(main_core.Tag.render(_t4$2 || (_t4$2 = _$4`
					<input 
						name="${0}"
						value="${0}" 
						type="hidden"
						>
					`), main_core.Text.encode(this.inputName), main_core.Text.encode(value))));
	      }, this);
	    }
	    if (!entries.length) {
	      allValuesValue.push('empty');
	      this.DOM.valueInputs.push(this.DOM.inputsWrap.appendChild(main_core.Tag.render(_t5$2 || (_t5$2 = _$4`
					<input 
						name="${0}"
						value="empty" 
						type="hidden"
						>
					`), main_core.Text.encode(this.inputName))));
	    }
	    this.emit('change', allValuesValue);
	  }
	  showMainLoader() {
	    if (this.DOM.wrap) {
	      this.hideMainLoader();
	      let loaderWrap = main_core.Tag.render(_t6$2 || (_t6$2 = _$4`<div class="calendar-resbook-webform-wrapper-loader-wrap"></div>`));
	      loaderWrap.appendChild(BookingUtil$$1.getLoader(160));
	      this.DOM.mainLoader = this.DOM.outerWrap.appendChild(loaderWrap);
	    }
	  }
	  hideMainLoader() {
	    main_core.Dom.remove(this.DOM.mainLoader);
	  }
	  showStatusLoader() {
	    this.showMainLoader();
	  }
	  hideStatusLoader() {
	    this.hideMainLoader();
	  }
	  requestAccessibilityData(params) {
	    if (!this.requestedFormData) {
	      this.showStatusLoader();
	      this.requestedFormData = true;
	      let formDataParams = {
	        from: params.date
	      };
	      this.requireFormData(formDataParams).then(() => {
	        this.hideStatusLoader();
	        this.refreshControlsState();
	        this.dateControl.refreshCurrentValue();
	        this.onChangeValues();
	        this.requestedFormData = false;
	      });
	    }
	  }
	  requireFormData(params) {
	    params = main_core.Type.isPlainObject(params) ? params : {};
	    return new Promise((resolve, reject) => {
	      let data = {
	        settingsData: this.getSettingsData() || null
	      };
	      if (!this.userFieldParams) {
	        data.fieldName = this.params.field.entity_field_name;
	      }
	      let dateFrom = main_core.Type.isDate(params.from) ? params.from : new Date(),
	        dateTo;
	      if (main_core.Type.isDate(params.to)) {
	        dateTo = params.to;
	      } else {
	        dateTo = new Date(dateFrom.getTime());
	        dateTo.setDate(dateFrom.getDate() + 60);
	      }
	      data.from = BookingUtil$$1.formatDate(this.DATE_FORMAT, dateFrom);
	      data.to = BookingUtil$$1.formatDate(this.DATE_FORMAT, dateTo);
	      this.setLoadedDataLimits(dateFrom, dateTo);
	      this.actionAgent('calendar.api.resourcebookingajax.getfillformdata', {
	        data: data
	      }).then(response => {
	        if (!main_core.Type.isPlainObject(response) || !response.data) {
	          resolve(response);
	        } else {
	          if (main_core.Type.isNumber(response.data.timezoneOffset)) {
	            this.timezoneOffset = response.data.timezoneOffset;
	            this.timezoneOffsetLabel = response.data.timezoneOffsetLabel;
	          }
	          if (response.data.workTimeStart !== undefined && response.data.workTimeEnd !== undefined) {
	            this.timeFrom = parseInt(response.data.workTimeStart);
	            this.timeTo = parseInt(response.data.workTimeEnd);
	          }
	          if (response.data.fieldSettings) {
	            this.userFieldParams = response.data.fieldSettings;
	          }
	          if (response.data.userIndex) {
	            this.userIndex = response.data.userIndex;
	          }
	          this.handleAccessibilityData(response.data.usersAccessibility, 'user');
	          this.handleAccessibilityData(response.data.resourcesAccessibility, 'resource');
	          resolve(response.data);
	        }
	      }, response => {
	        resolve(response);
	      });
	    });
	  }
	  setLoadedDataLimits(from, to) {
	    this.loadedDataFrom = main_core.Type.isDate(from) ? from : BookingUtil$$1.parseDate(from);
	    this.loadedDataTo = main_core.Type.isDate(to) ? to : BookingUtil$$1.parseDate(to);
	    this.loadedDates = this.loadedDates || [];
	    this.loadedDatesIndex = this.loadedDatesIndex || {};
	    let dateKey,
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
	  fillDataIndex(date, time, entityType, entityId) {
	    let dateIndex = this.loadedDatesIndex[date];
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
	  handleAccessibilityData(data, entityType) {
	    if (main_core.Type.isPlainObject(data) && (entityType === 'user' || entityType === 'resource')) {
	      // For each entry which has accessibility entries
	      for (let entityId in data) {
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
	          }, this);
	        }
	      }
	      this.accessibility[entityType] = BookingUtil$$1.mergeEx(this.accessibility[entityType], data);
	    }
	  }
	  fillBusySlotMatrix(entry, entityType, entityId) {
	    if (!this.busySlotMatrix[entityType][entityId]) {
	      this.busySlotMatrix[entityType][entityId] = {};
	    }
	    let fromDate = new Date(entry.from.getTime()),
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
	        if (count > 10000)
	          // emergency exit
	          {
	            break;
	          }
	      }
	    }
	  }
	  getCaption() {
	    return this.params.field.caption;
	  }
	  getSettingsData() {
	    return this.params.field.settings_data || {};
	  }
	  getUserIndex() {
	    return this.userIndex;
	  }
	  getFieldParams() {
	    return this.userFieldParams;
	  }
	  getSettings() {
	    return {
	      caption: this.getCaption(),
	      data: this.getSettingsData()
	    };
	  }
	  isUserSelectorInAutoMode() {
	    return this.usersDisplayed() && this.getSettingsData().users.show === "N";
	  }
	  isResourceSelectorInAutoMode() {
	    return this.resourcesDisplayed() && this.getSettingsData().resources.show === "N";
	  }
	  autoAdjustUserSelector() {
	    let currentDate = this.dateControl.getValue(),
	      timeValue = this.timeControl.getValue();
	    if (main_core.Type.isDate(currentDate) && timeValue) {
	      let i,
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
	  autoAdjustResourceSelector() {
	    let currentDate = this.dateControl.getValue(),
	      timeValue = this.timeControl.getValue();
	    if (main_core.Type.isDate(currentDate) && timeValue) {
	      let i,
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
	  preparaAutoSelectValues() {
	    let settingsData = this.getSettingsData(),
	      autoSelectUser = this.usersDisplayed() && (settingsData.users.defaultMode === 'auto' || settingsData.users.show === "N"),
	      autoSelectResource = this.resourcesDisplayed() && (settingsData.resources.defaultMode === 'auto' || settingsData.resources.show === "N"),
	      autoSelectDate = settingsData.date.start === 'free',
	      maxStepsAuto = 60,
	      date,
	      i;
	    this.selectedUserId = false;
	    this.selectedResourceId = false;
	    date = new Date();
	    // Walk through each date searching for free space
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
	  getFreeEntitiesForDate(date, params) {
	    let settingsData = this.getSettingsData(),
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
	  displayUsersControl() {
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
	  displayResourcesControl() {
	    let valueIndex = {},
	      fieldParams = this.getFieldParams(),
	      settingsData = this.getSettingsData();
	    if (this.resourcesDisplayed()) {
	      this.getResourceValue().forEach(function (id) {
	        id = parseInt(id);
	        if (id > 0) {
	          valueIndex[id] = true;
	        }
	      });
	      let resourceList = [];
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
	  displayServicesControl() {
	    let fieldParams = this.getFieldParams(),
	      settingsData = this.getSettingsData();
	    if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value) {
	      let dataValueRaw = this.getServicesValue();
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
	  displayDurationControl() {
	    let fieldParams = this.getFieldParams(),
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
	  displayDateTimeControl() {
	    let timezone = false,
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
	        let userTimezoneOffset = -new Date().getTimezoneOffset() * 60;
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
	  handleDateChanging(date, realDate) {
	    this.emit('BX.Calendar.Resourcebooking.LiveFieldController:dateChanged');
	    if (this.timeSelectorDisplayed()) {
	      if (realDate) {
	        this.timeControl.show();
	        let timeValueFrom,
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
	        });

	        // this.timeControl.refresh(
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
	  handleSelectedDateTimeChanging(value, useTimeout) {
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
	  updateStatusControl() {
	    if (this.statusControl && this.selectorCanBeShown('date')) {
	      let currentDate = this.getCurrentDate();
	      if (this.dateControl.isItPastDate(currentDate)) {
	        this.statusControl.setError(main_core.Loc.getMessage('WEBF_RES_BOOKING_PAST_DATE_WARNING'));
	      } else {
	        if (this.timeSelectorDisplayed()) {
	          if (this.timeControl.hasAvailableSlots()) {
	            let timeValue = this.timeControl.getValue();
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
	  getFreeDate(params) {
	    let slotsAmount = Math.ceil(params.duration / this.scale),
	      freeDate = null,
	      date = this.loadedDataFrom;

	    // Walk through each date searching for free space
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
	  getAvailableDateIndex(params) {
	    let userIsFree,
	      resourcesAreFree,
	      dateIndex = {};
	    if (this.timeSelectorDisplayed()) {
	      let slotsAmount = Math.ceil(params.duration / this.scale);
	      this.loadedDates.forEach(function (date) {
	        dateIndex[date.key] = this.checkSlotsForDate(date.key, slotsAmount, {
	          resources: params.resources,
	          user: params.user
	        });
	      }, this);
	    } else {
	      let i,
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
	  getSlotIndex(params) {
	    if (params.date) {
	      params.date = this.dateControl.getValue();
	    }
	    let slotIndex = {};
	    if (main_core.Type.isDate(params.date)) {
	      if (this.getFieldParams().ALLOW_OVERBOOKING !== "Y" && (this.isUserSelectorInAutoMode() || this.isResourceSelectorInAutoMode())) {
	        const fieldParams = this.getFieldParams();
	        let freeSlot,
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
	          const today = new Date();
	          const deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N' ? today.getTimezoneOffset() * 60 + this.timezoneOffset : 0;
	          todayNowTime = today.getHours() * 60 + today.getMinutes() + deltaOffset / 60;
	        }

	        // Prefill slotIndex
	        timeSlots.forEach(function (slot) {
	          slotIndex[slot.time] = true;
	        }, this);
	        if (this.isUserSelectorInAutoMode()) {
	          const userList = this.getUsersValue();
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
	          const resList = this.getResourceValue();
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
	  getAvailableSlotIndex(params) {
	    let todayNowTime = 0;
	    const fieldParams = this.getFieldParams();
	    let dateKey,
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
	      slotIndex = {};

	    // Prefill slotIndex
	    timeSlots.forEach(function (slot) {
	      slotIndex[slot.time] = true;
	    }, this);
	    if (main_core.Type.isDate(params.date)) {
	      dateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, params.date);
	      loadedDate = this.loadedDates[this.loadedDatesIndex[dateKey]];
	      slotGap = 1;
	      if (this.checkIsTodayDate(dateKey)) {
	        const today = new Date();
	        const deltaOffset = fieldParams.USE_USER_TIMEZONE === 'N' ? today.getTimezoneOffset() * 60 + this.timezoneOffset : 0;
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
	  checkSlotsForDate(date, slotsAmount, params) {
	    let userIsFree = true,
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
	  entityHasSlotsForDate(params) {
	    let busySlotList,
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
	  getSelectedResources() {
	    let result = null;
	    if (this.resourceControl) {
	      result = this.resourceControl.getSelectedValues();
	      if (main_core.Type.isArray(result) && !result.length) {
	        result = null;
	      }
	    }
	    return result;
	  }
	  getSelectedUser() {
	    let result = null;
	    if (this.userControl) {
	      result = this.userControl.getSelectedUser();
	    }
	    return result;
	  }
	  getCurrentDuration() {
	    let result = null;
	    if (this.durationControl) {
	      result = this.durationControl.getSelectedValue();
	    } else if (this.serviceControl) {
	      let service = this.serviceControl.getSelectedService(true);
	      if (service && service.duration) {
	        result = parseInt(service.duration);
	      }
	    }
	    return result;
	  }
	  getDefaultDurationSlotsAmount() {
	    let settingsData = this.getSettingsData(),
	      fieldParams = this.getFieldParams(),
	      duration,
	      i,
	      slotsAmount;
	    if (fieldParams.USE_SERVICES === 'Y' && settingsData.services.value) {
	      const services = this.getServicesValue();
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
	  getCurrentServiceName() {
	    let result = '';
	    if (this.serviceControl) {
	      let service = this.serviceControl.getSelectedService(true);
	      if (service && service.name) {
	        result = service.name;
	      }
	    }
	    return result;
	  }
	  getCurrentDate() {
	    let result = null;
	    if (this.dateControl && this.dateControl.isShown()) {
	      result = this.dateControl.getValue();
	      if (this.timeSelectorDisplayed()) {
	        let hour,
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
	  getTimeSlots() {
	    if (!this.slots) {
	      this.slots = [];
	      let slot;
	      let finishTime;
	      let time = this.timeFrom * 60;
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
	  usersDisplayed() {
	    if (this.useUsers === undefined) {
	      this.useUsers = this.getFieldParams()['USE_USERS'] === 'Y';
	    }
	    return this.useUsers;
	  }
	  resourcesDisplayed() {
	    if (this.useResources === undefined) {
	      let fieldParams = this.getFieldParams();
	      this.useResources = !!(fieldParams.USE_RESOURCES === 'Y' && fieldParams.SELECTED_RESOURCES);
	    }
	    return this.useResources;
	  }
	  timeSelectorDisplayed() {
	    if (this.useTime === undefined) {
	      this.useTime = this.getFieldParams().FULL_DAY !== 'Y';
	    }
	    return this.useTime;
	  }
	  selectorCanBeShown(type) {
	    let result = false;
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
	  checkIsTodayDate(dateKey) {
	    if (!this.todayDateKey) {
	      let today = new Date();
	      this.todayDateKey = BookingUtil$$1.formatDate(this.DATE_FORMAT, today);
	    }
	    return this.todayDateKey === dateKey;
	  }
	  getResourceValue() {
	    const settingsData = this.getSettingsData();
	    let value = [];
	    if (main_core.Type.isArray(settingsData.resources.value)) {
	      value = settingsData.resources.value;
	    } else if (main_core.Type.isString(settingsData.resources.value)) {
	      value = settingsData.resources.value.split('|');
	    }
	    return value;
	  }
	  getUsersValue() {
	    const settingsData = this.getSettingsData();
	    let value = [];
	    if (main_core.Type.isArray(settingsData.users.value)) {
	      value = settingsData.users.value;
	    } else if (main_core.Type.isString(settingsData.users.value)) {
	      value = settingsData.users.value.split('|');
	    }
	    return value;
	  }
	  getServicesValue() {
	    const settingsData = this.getSettingsData();
	    let value = [];
	    if (main_core.Type.isArray(settingsData.services.value)) {
	      value = settingsData.services.value;
	    } else if (main_core.Type.isString(settingsData.services.value)) {
	      value = settingsData.services.value.split('|');
	    }
	    return value;
	  }
	}

	class Translit {
	  static run(str) {
	    let replaceChar = '_',
	      regexpEnChars = /[A-Z0-9]/i,
	      regexpSpace = /\s/,
	      maxLength = 100,
	      len = str.length,
	      result = '',
	      lastNewChar = '',
	      i;
	    for (i = 0; i < len; i++) {
	      let newChar,
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
	  static generateReplacementCharTable() {
	    let separator = ',',
	      charTableFrom = (main_core.Loc.getMessage('TRANSLIT_FROM') || '').split(separator),
	      charTableTo = (main_core.Loc.getMessage('TRANSLIT_TO') || '').split(separator),
	      i,
	      len;
	    Translit.replacementCharTable = [];
	    for (i = 0, len = charTableFrom.length; i < len; i++) {
	      Translit.replacementCharTable[i] = [charTableFrom[i], charTableTo[i]];
	    }
	  }
	  static getChar(chr) {
	    if (Translit.replacementCharTable === null) {
	      Translit.generateReplacementCharTable();
	    }
	    for (let i = 0, len = Translit.replacementCharTable.length; i < len; i++) {
	      if (chr === Translit.replacementCharTable[i][0]) {
	        return Translit.replacementCharTable[i][1];
	      }
	    }
	    return null;
	  }
	}
	Translit.replacementCharTable = null;

	let _$5 = t => t,
	  _t$5;
	class BookingUtil$$1 {
	  static getDateFormat() {
	    if (main_core.Type.isNull(BookingUtil$$1.DATE_FORMAT)) {
	      BookingUtil$$1.DATE_FORMAT = CoreDate.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATE"));
	    }
	    return BookingUtil$$1.DATE_FORMAT;
	  }
	  static getDateTimeFormat() {
	    if (main_core.Type.isNull(BookingUtil$$1.DATETIME_FORMAT)) {
	      BookingUtil$$1.DATETIME_FORMAT = CoreDate.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATETIME"));
	    }
	    return BookingUtil$$1.DATETIME_FORMAT;
	  }
	  static getTimeFormat() {
	    if (main_core.Type.isNull(BookingUtil$$1.TIME_FORMAT)) {
	      let DATETIME_FORMAT = BookingUtil$$1.getDateTimeFormat();
	      let DATE_FORMAT = BookingUtil$$1.getDateFormat();
	      if (DATETIME_FORMAT.substr(0, DATE_FORMAT.length) === DATE_FORMAT) {
	        BookingUtil$$1.TIME_FORMAT = DATETIME_FORMAT.substr(DATE_FORMAT.length).trim();
	      } else {
	        BookingUtil$$1.TIME_FORMAT = CoreDate.convertBitrixFormat(CoreDate.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
	      }
	      BookingUtil$$1.TIME_FORMAT_SHORT = BookingUtil$$1.TIME_FORMAT.replace(':s', '');
	    }
	    return BookingUtil$$1.TIME_FORMAT;
	  }
	  static getTimeFormatShort() {
	    if (main_core.Type.isNull(BookingUtil$$1.TIME_FORMAT_SHORT)) {
	      BookingUtil$$1.TIME_FORMAT_SHORT = BookingUtil$$1.getTimeFormat().replace(':s', '');
	    }
	    return BookingUtil$$1.TIME_FORMAT_SHORT;
	  }
	  static formatDate(format, timestamp, now, utc) {
	    if (format === null) {
	      format = BookingUtil$$1.getDateFormat();
	    }
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime() / 1000;
	    }
	    return CoreDate.format(format, timestamp, now, utc);
	  }
	  static parseDate(str, bUTC, formatDate, formatDatetime) {
	    return CoreDate.parse(str, bUTC, formatDate, formatDatetime);
	  }
	  static formatTime(h, m) {
	    let d = new Date();
	    d.setHours(h, m, 0);
	    return CoreDate.format(BookingUtil$$1.getTimeFormatShort(), d.getTime() / 1000);
	  }
	  static translit(str) {
	    return main_core.Type.isString(str) ? Translit.run(str).replace(/[^a-z0-9_]/ig, "_") : str;
	  }
	  static getLoader(size, className) {
	    return main_core.Tag.render(_t$5 || (_t$5 = _$5`
		<div class="${0}">
			<svg class="calendar-loader-circular"
				style="width:${0}px; height:${0}px;"
				viewBox="25 25 50 50">
					<circle class="calendar-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					<circle class="calendar-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
`), className || 'calendar-loader', parseInt(size), parseInt(size));
	  }
	  static fireCustomEvent(eventObject, eventName, eventParams, secureParams) {
	    if (window.BX && main_core.Type.isFunction(BX.onCustomEvent)) {
	      return BX.onCustomEvent(eventObject, eventName, eventParams, secureParams);
	    }
	  }
	  static bindCustomEvent(eventObject, eventName, eventHandler) {
	    if (window.BX && main_core.Type.isFunction(BX.addCustomEvent)) {
	      return BX.addCustomEvent(eventObject, eventName, eventHandler);
	    }
	  }
	  static unbindCustomEvent(eventObject, eventName, eventHandler) {
	    if (window.BX && main_core.Type.isFunction(BX.removeCustomEvent)) {
	      return BX.removeCustomEvent(eventObject, eventName, eventHandler);
	    }
	  }
	  static isAmPmMode() {
	    return CoreDate.isAmPmMode();
	  }
	  static mergeEx() {
	    let arg = Array.prototype.slice.call(arguments);
	    if (arg.length < 2) {
	      return {};
	    }
	    let result = arg.shift();
	    for (let i = 0; i < arg.length; i++) {
	      for (let k in arg[i]) {
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
	  static getDurationList(fullDay) {
	    let values = [5, 10, 15, 20, 25, 30, 40, 45, 50, 60, 90, 120, 180, 240, 300, 360, 1440, 1440 * 2, 1440 * 3, 1440 * 4, 1440 * 5, 1440 * 6, 1440 * 7, 1440 * 10],
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
	  static getDurationLabel(val) {
	    let label;
	    if (val % 1440 === 0)
	      // Days
	      {
	        label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_DAY').replace('#NUM#', val / 1440);
	      } else if (val % 60 === 0 && val !== 60)
	      // Hours
	      {
	        label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_HOUR').replace('#NUM#', val / 60);
	      }
	      // Minutes
	    else {
	      label = main_core.Loc.getMessage('USER_TYPE_DURATION_X_MIN').replace('#NUM#', val);
	    }
	    return label;
	  }
	  static parseDuration(value) {
	    let stringValue = value,
	      numValue = parseInt(value),
	      parsed = false,
	      dayRegexp = new RegExp('(\\d)\\s*(' + main_core.Loc.getMessage('USER_TYPE_DURATION_REGEXP_DAY') + ').*', 'ig'),
	      hourRegexp = new RegExp('(\\d)\\s*(' + main_core.Loc.getMessage('USER_TYPE_DURATION_REGEXP_HOUR') + ').*', 'ig');
	    value = value.replace(dayRegexp, function (str, num) {
	      parsed = true;
	      return num;
	    });
	    // It's days
	    if (parsed) {
	      value = numValue * 1440;
	    } else {
	      value = stringValue.replace(hourRegexp, function (str, num) {
	        parsed = true;
	        return num;
	      });
	      // It's hours
	      if (parsed) {
	        value = numValue * 60;
	      } else
	        // Minutes
	        {
	          value = numValue;
	        }
	    }
	    return parseInt(value) || 0;
	  }
	  static getSimpleTimeList() {
	    if (main_core.Type.isNull(BookingUtil$$1.simpleTimeList)) {
	      let i,
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
	  static adaptTimeValue(timeValue) {
	    timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
	    let timeList = BookingUtil$$1.getSimpleTimeList(),
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
	  static getDayLength() {
	    return BookingUtil$$1.DAY_LENGTH;
	  }
	  static showLimitationPopup() {
	    if (top.BX.getClass("BX.UI.InfoHelper")) {
	      top.BX.UI.InfoHelper.show('limit_crm_booking');
	    }
	  }
	}
	BookingUtil$$1.simpleTimeList = null;
	BookingUtil$$1.DAY_LENGTH = 86400000;
	BookingUtil$$1.TIME_FORMAT = null;
	BookingUtil$$1.TIME_FORMAT_SHORT = null;
	BookingUtil$$1.DATE_FORMAT = null;
	BookingUtil$$1.DATETIME_FORMAT = null;

	class FieldViewControllerAbstract extends main_core.Event.EventEmitter {
	  constructor(params) {
	    super(params);
	    this.settings = params.settings || {};
	    this.showTitle = params.displayTitle !== false;
	    this.title = params.title || '';
	    this.DOM = {
	      wrap: params.wrap // outer wrap of the form
	    };
	  }

	  build() {
	    this.controls = {};
	    // inner wrap
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
	  destroy() {
	    main_core.Dom.remove(this.DOM.outerWrap);
	  }
	  displayTitle() {
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
	  updateTitle(title) {
	    if (this.showTitle) {
	      this.title = title;
	      main_core.Dom.adjust(this.DOM.titleWrap, {
	        text: this.title
	      });
	    }
	  }
	  displayWarning(message) {
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
	  displayUsersControl() {
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
	  displayResourcesControl() {
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
	  displayServicesControl() {
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
	  displayDurationControl() {
	    if (!this.settings.userfieldSettings.useServices) {
	      this.controls.duration = new DurationSelector({
	        outerWrap: this.DOM.innerWrap,
	        data: this.settings.data.duration,
	        fullDay: this.settings.userfieldSettings.fullDay
	      });
	      this.controls.duration.display();
	    }
	  }
	  displayDateControl() {
	    this.controls.date = new DateSelector({
	      outerWrap: this.DOM.innerWrap,
	      data: this.settings.data.date
	    });
	    this.controls.date.display();
	  }
	  displayTimeControl() {
	    if (!this.settings.userfieldSettings.fullDay) {
	      this.controls.time = new TimeSelector({
	        outerWrap: this.DOM.innerWrap,
	        data: this.settings.data.time
	      });
	      this.controls.time.display();
	    }
	  }
	  refreshLayout(settingsData) {
	    for (let k in this.controls) {
	      if (this.controls.hasOwnProperty(k) && main_core.Type.isFunction(this.controls[k].refresh)) {
	        this.controls[k].refresh(settingsData[k] || this.settings.data[k]);
	      }
	    }
	  }
	  getInnerWrap() {
	    return this.DOM.innerWrap;
	  }
	  getOuterWrap() {
	    return this.DOM.outerWrap;
	  }
	}

	class FieldViewControllerEdit extends FieldViewControllerAbstract {
	  constructor(params) {
	    super(params);
	  }
	}

	class FieldViewControllerPreview extends FieldViewControllerAbstract {
	  constructor(params) {
	    super(params);
	  }
	  build() {
	    super.build();
	    this.DOM.outerWrap.className = 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-preview calendar-resbook-webform-wrapper-dark';
	  }
	}

	class SelectInput$$1 extends main_core.Event.EventEmitter {
	  constructor(params) {
	    super(params);
	    this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);
	    if (main_core.Type.isFunction(params.getValues)) {
	      this.getValues = params.getValues;
	      this.values = this.getValues();
	    } else {
	      this.values = params.values || false;
	    }
	    this.input = params.input;
	    this.defaultValue = params.defaultValue || '';
	    this.openTitle = params.openTitle || '';
	    this.className = params.className || '';
	    this.currentValue = params.value;
	    this.currentValueIndex = params.valueIndex;
	    this.onChangeCallback = main_core.Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
	    this.onAfterMenuOpen = params.onAfterMenuOpen || null;
	    this.zIndex = params.zIndex || 1200;
	    this.disabled = params.disabled;
	    this.editable = params.editable !== false;
	    this.setFirstIfNotFound = !!params.setFirstIfNotFound;
	    if (this.onChangeCallback) {
	      main_core.Event.bind(this.input, 'change', this.onChangeCallback);
	      main_core.Event.bind(this.input, 'keyup', this.onChangeCallback);
	    }
	    this.curInd = false;
	    if (main_core.Type.isArray(this.values)) {
	      main_core.Event.bind(this.input, 'click', this.onClick.bind(this));
	      if (this.editable) {
	        main_core.Event.bind(this.input, 'focus', this.onFocus.bind(this));
	        main_core.Event.bind(this.input, 'blur', this.onBlur.bind(this));
	        main_core.Event.bind(this.input, 'keyup', this.onKeyup.bind(this));
	      } else {
	        main_core.Event.bind(this.input, 'focus', function () {
	          this.input.blur();
	        }.bind(this));
	      }
	      if (this.currentValueIndex === undefined && this.currentValue !== undefined) {
	        this.currentValueIndex = -1;
	        for (let i = 0; i < this.values.length; i++) {
	          if (parseInt(this.values[i].value) === parseInt(this.currentValue)) {
	            this.currentValueIndex = i;
	            break;
	          }
	        }
	        if (this.currentValueIndex === -1) {
	          this.currentValueIndex = this.setFirstIfNotFound ? 0 : undefined;
	        }
	      }
	    }
	    if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex]) {
	      this.input.value = this.values[this.currentValueIndex].label;
	    }
	  }
	  showPopup() {
	    if (this.getValues) {
	      this.values = this.getValues();
	    }
	    if (this.shown || this.disabled || !this.values.length) {
	      return;
	    }
	    let ind = 0,
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
	    if (!BX.browser.IsFirefox()) {
	      this.popupMenu.popupWindow.setMinWidth(this.input.offsetWidth);
	    }
	    this.popupMenu.popupWindow.setMaxWidth(300);
	    let menuContainer = this.popupMenu.getPopupWindow().getContentContainer();
	    main_core.Dom.addClass(this.popupMenu.layout.menuContainer, 'calendar-resourcebook-select-popup');
	    this.popupMenu.show();
	    let menuItem = this.popupMenu.menuItems[ind];
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
	  closePopup() {
	    main_popup.MenuManager.destroy(this.id);
	    this.shown = false;
	  }
	  onFocus() {
	    setTimeout(function () {
	      if (!this.shown) {
	        this.showPopup();
	      }
	    }.bind(this), 200);
	  }
	  onClick() {
	    if (this.shown) {
	      this.closePopup();
	    } else {
	      this.showPopup();
	    }
	  }
	  onBlur() {
	    setTimeout(this.closePopup.bind(this), 200);
	  }
	  onKeyup() {
	    setTimeout(this.closePopup.bind(this), 50);
	  }
	  onChange(value) {
	    let val = this.input.value;
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
	  destroy() {
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
	  setValue(value) {
	    this.input.value = value;
	    if (main_core.Type.isArray(this.values)) {
	      let currentValueIndex = -1;
	      for (let i = 0; i < this.values.length; i++) {
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
	  getValue() {
	    return this.input.value;
	  }
	}

	let CoreDate = window.BX && BX.Main && BX.Main.Date ? BX.Main.Date : null;
	class Resourcebooking {
	  static getLiveField(params) {
	    if (!params.wrap || !main_core.Type.isDomNode(params.wrap)) {
	      throw new Error('The argument "params.wrap" must be a DOM node');
	    }
	    if (main_core.Type.isNull(CoreDate)) {
	      throw new Error('The error occured during Date extention loading');
	    }
	    let liveFieldController = new LiveFieldController(params);
	    liveFieldController.init();
	    return liveFieldController;
	  }
	  static getPreviewField(params) {}
	}

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

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Main,BX.Main,BX.Event));
//# sourceMappingURL=resourcebooking.bundle.js.map
