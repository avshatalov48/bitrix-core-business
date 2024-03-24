/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_entitySelector,main_core_events,helper,main_popup,main_core,calendar_resourcebookinguserfield,calendar_resourcebooking) {
	'use strict';

	class FormFieldTunnerAbstract {
	  constructor() {
	    this.label = '';
	    this.formLabel = '';
	    this.displayed = false;
	    this.valuePopup = null;
	    this.statePopup = null;
	    this.displayCheckboxDisabled = false;
	    this.DOM = {};
	  }
	  build(params) {
	    this.updateConfig(params.params);
	    this.DOM.fieldWrap = calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-item'
	      }
	    });
	    this.DOM.labelWrap = this.DOM.fieldWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-field'
	      }
	    }));
	    this.DOM.labelNode = this.DOM.labelWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-field-title'
	      },
	      text: this.getLabel()
	    }));

	    // Label in form
	    this.DOM.formTitleWrap = this.DOM.labelWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-field-subtitle' + (this.isDisplayed() ? ' show' : '')
	      }
	    }));
	    this.DOM.formTitleLabel = this.DOM.formTitleWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'
	      },
	      text: this.getFormLabel(),
	      events: {
	        click: this.enableFormTitleEditMode.bind(this)
	      }
	    }));
	    this.DOM.formTitleEditIcon = this.DOM.formTitleWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-field-edit'
	      },
	      events: {
	        click: this.enableFormTitleEditMode.bind(this)
	      }
	    }));

	    // Display checkbox
	    this.DOM.checkboxNode = this.DOM.fieldWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-checkbox-container'
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("input", {
	      attrs: {
	        type: "checkbox",
	        value: 'Y',
	        checked: this.isDisplayed(),
	        disabled: this.displayCheckboxDisabled
	      },
	      events: {
	        click: this.checkDisplayMode.bind(this)
	      }
	    }));

	    // State popup
	    this.buildStatePopup({
	      wrap: this.DOM.fieldWrap,
	      config: params.config || {}
	    });

	    // Value popup
	    this.buildValuePopup({
	      wrap: this.DOM.fieldWrap,
	      config: params.config || {}
	    });
	    if (calendar_resourcebooking.Type.isFunction(params.changeSettingsCallback)) {
	      this.changeSettingsCallback = params.changeSettingsCallback;
	    }
	    params.wrap.appendChild(this.DOM.fieldWrap);
	  }
	  destroy() {
	    if (this.valuePopup && calendar_resourcebooking.Type.isFunction(this.valuePopup.closePopup)) {
	      this.valuePopup.closePopup();
	    }
	    if (this.statePopup && calendar_resourcebooking.Type.isFunction(this.statePopup.closePopup)) {
	      this.statePopup.closePopup();
	    }
	  }
	  updateConfig(params = {}) {
	    this.setFormLabel(params.label || this.formLabel);
	    if (params.show) {
	      this.displayed = params.show !== 'N';
	    }
	  }
	  buildStatePopup(params) {}
	  buildValuePopup(params) {}
	  getLabel() {
	    return this.label;
	  }
	  getFormLabel() {
	    return this.formLabel;
	  }
	  setFormLabel(formLabel) {
	    this.formLabel = formLabel || '';
	  }
	  isDisplayed() {
	    return this.displayed;
	  }
	  checkDisplayMode() {
	    this.displayed = !!this.DOM.checkboxNode.checked;
	    if (this.displayed) {
	      this.displayInForm();
	    } else {
	      this.hideInForm();
	    }
	  }
	  displayInForm() {
	    calendar_resourcebooking.Dom.addClass(this.DOM.formTitleWrap, 'show');
	    this.triggerChangeRefresh();
	  }
	  hideInForm() {
	    calendar_resourcebooking.Dom.removeClass(this.DOM.formTitleWrap, 'show');
	    this.triggerChangeRefresh();
	  }
	  enableFormTitleEditMode() {
	    if (!this.DOM.formTitleInputNode) {
	      this.DOM.formTitleInputNode = this.DOM.formTitleWrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	        attrs: {
	          type: 'text',
	          className: 'calendar-resbook-webform-settings-popup-field-subtitle-text'
	        },
	        events: {
	          blur: this.finishFormTitleEditMode.bind(this)
	        }
	      }));
	    }
	    this.DOM.formTitleInputNode.value = this.getFormLabel();
	    this.DOM.formTitleInputNode.style.display = '';
	    this.DOM.formTitleLabel.style.display = 'none';
	    this.DOM.formTitleEditIcon.style.display = 'none';
	    this.DOM.formTitleInputNode.focus();
	  }
	  finishFormTitleEditMode() {
	    this.setFormLabel(this.DOM.formTitleInputNode.value);
	    calendar_resourcebooking.Dom.adjust(this.DOM.formTitleLabel, {
	      text: this.getFormLabel()
	    });
	    this.DOM.formTitleLabel.style.display = '';
	    this.DOM.formTitleEditIcon.style.display = '';
	    this.DOM.formTitleInputNode.style.display = 'none';
	    this.triggerChangeRefresh();
	  }
	  getSettingsValue() {}
	  triggerChangeRefresh() {
	    setTimeout(function () {
	      BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');
	    }.bind(this), 50);
	  }
	}

	class FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    this.id = 'resourcebooking-settings-popup-' + Math.round(Math.random() * 100000);
	    this.menuItems = [];
	    this.DOM = {
	      outerWrap: params.wrap
	    };
	    this.handleClickFunc = this.handleClick.bind(this);
	  }
	  build() {
	    this.DOM.innerWrap = this.DOM.outerWrap.appendChild(main_core.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-select'
	      }
	    }));
	    this.DOM.currentStateLink = this.DOM.innerWrap.appendChild(main_core.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-select-value'
	      },
	      text: this.getCurrentModeState(),
	      events: {
	        click: this.showPopup.bind(this)
	      }
	    }));
	  }
	  showPopup() {
	    if (this.isPopupShown() || this.disabled) {
	      return this.closePopup();
	    }
	    this.menuItems = this.getMenuItems();
	    main_core.Event.unbind(document, 'click', this.handleClickFunc);
	    this.popup = BX.PopupMenu.create(this.id, this.DOM.currentStateLink, this.menuItems, {
	      className: 'popup-window-resource-select',
	      closeByEsc: true,
	      autoHide: false,
	      offsetTop: 0,
	      offsetLeft: 0,
	      cacheable: false
	    });
	    this.popup.popupWindow.setAngle({
	      offset: 30,
	      position: 'top'
	    });
	    this.popup.show(true);
	    this.popupContainer = this.popup.popupWindow.popupContainer;

	    //this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';

	    // BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', function()
	    // {
	    // 	BX.PopupMenu.destroy(this.id);
	    // 	this.popup = null;
	    // }.bind(this));

	    this.popup.menuItems.forEach(function (menuItem) {
	      let inputType = false,
	        className,
	        checked,
	        inputNameStr = '';
	      if (menuItem.dataset && menuItem.dataset.type) {
	        checked = menuItem.dataset.checked;
	        let menuItemClassName = 'menu-popup-item';
	        if (menuItem.dataset.type === 'radio') {
	          inputType = 'radio';
	          className = 'menu-popup-item-resource-radio';
	          if (menuItem.dataset.inputName) {
	            inputNameStr = ' name="' + menuItem.dataset.inputName + '" ';
	          }
	        } else if (menuItem.dataset.type === 'checkbox') {
	          inputType = 'checkbox';
	          className = 'menu-popup-item-resource-checkbox';
	        }
	        let innerHtml = '<div class="menu-popup-item-inner">';
	        if (menuItem.dataset.type === 'submenu-list') {
	          menuItemClassName += ' menu-popup-item-submenu';
	          innerHtml += '<div class="menu-popup-item-resource menu-popup-item-resource-wide">' + '<span class="menu-popup-item-text">' + '<span>' + menuItem.text + '</span>' + '<span class="menu-popup-item-resource-subvalue">' + (menuItem.dataset.textValue || menuItem.dataset.value) + '</span>' + '</span>' + '</div>';
	        } else if (inputType) {
	          innerHtml += '<div class="menu-popup-item-resource">';
	          if (inputType) {
	            innerHtml += '<input class="' + className + '" type="' + inputType + '"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '" ' + inputNameStr + '>' + '<label class="menu-popup-item-text"  for="' + menuItem.id + '">' + menuItem.text + '</label>';
	          }
	          innerHtml += '</div>';
	        }
	        innerHtml += '</div>';
	        menuItem.layout.item.className = menuItemClassName;
	        menuItem.layout.item.innerHTML = innerHtml;
	      }
	    }, this);
	    setTimeout(() => {
	      main_core.Event.bind(document, 'click', this.handleClickFunc);
	    }, 50);
	  }
	  closePopup() {
	    if (this.isPopupShown()) {
	      this.popup.close();
	      this.popupContainer.style.maxHeight = '';
	    }
	  }
	  isPopupShown() {
	    return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() && this.popup.popupWindow.popupContainer && BX.isNodeInDom(this.popup.popupWindow.popupContainer);
	  }
	  getCurrentModeState() {
	    return '';
	  }
	  getMenuItems() {
	    return [];
	  }
	  getPopupContent() {
	    this.DOM.innerWrap = main_core.Dom.create("div", {
	      props: {
	        className: ''
	      }
	    });
	    return this.DOM.innerWrap;
	  }
	  handlePopupClick(e) {
	    let target = e.target || e.srcElement;
	    if (target.hasAttribute('data-bx-resbook-control-node') || BX.findParent(target, {
	      attribute: 'data-bx-resbook-control-node'
	    }, this.DOM.innerWrap)) {
	      this.handleControlChanges();
	    }
	  }
	  handleControlChanges() {
	    if (this.changesTimeout) {
	      this.changesTimeout = clearTimeout(this.changesTimeout);
	    }
	    this.changesTimeout = setTimeout(BX.delegate(function () {
	      BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');
	    }, this), 50);
	  }
	  menuItemClick(e, menuItem) {}
	  handleClick(e) {
	    let target = e.target || e.srcElement;
	    if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target)) {
	      return this.closePopup({
	        animation: true
	      });
	    }
	  }
	  setDisabled() {
	    this.disabled = true;
	    if (this.isPopupShown()) {
	      this.closePopup();
	    }
	    main_core.Dom.addClass(this.DOM.innerWrap, 'disabled');
	  }
	  setEnabled() {
	    this.disabled = false;
	    main_core.Dom.removeClass(this.DOM.innerWrap, 'disabled');
	  }
	}

	class FormFieldTunnerValuePopupAbstract {
	  constructor(params) {
	    this.id = 'resourcebooking-settings-value-popup-' + Math.round(Math.random() * 100000);
	    this.selectedValues = [];
	    this.DOM = {
	      outerWrap: params.wrap
	    };
	  }
	  build() {
	    this.DOM.innerWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-select-result'
	      }
	    }));
	    this.DOM.valueLink = this.DOM.innerWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-select-value'
	      },
	      text: this.getCurrentValueState(),
	      events: {
	        click: this.showPopup.bind(this),
	        mouseover: this.showHoverPopup.bind(this),
	        mouseout: this.hideHoverPopup.bind(this)
	      }
	    }));
	  }
	  showPopup() {
	    if (this.popup && this.popup.isShown()) {
	      return this.popup.close();
	    }
	    this.popup = new BX.PopupWindow(this.id, this.DOM.valueLink, {
	      autoHide: true,
	      loseByEsc: true,
	      offsetTop: 0,
	      offsetLeft: 0,
	      width: this.getPopupWidth(),
	      lightShadow: true,
	      content: this.getPopupContent()
	    });
	    this.popup.setAngle({
	      offset: 60,
	      position: 'top'
	    });
	    this.popup.show(true);
	    BX.unbind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));
	    BX.bind(this.DOM.innerWrap, 'click', BX.proxy(this.handlePopupClick, this));
	    BX.addCustomEvent(this.popup, 'onPopupClose', BX.delegate(function () {
	      this.handlePopupCloose();
	      this.popup.destroy(this.id);
	      this.popup = null;
	    }, this));
	  }
	  closePopup() {
	    if (this.isPopupShown()) {
	      this.popup.close();
	    }
	  }
	  isPopupShown() {
	    return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() && this.popup.popupWindow.popupContainer && BX.isNodeInDom(this.popup.popupWindow.popupContainer);
	  }
	  showHoverPopup() {}
	  hideHoverPopup() {}
	  handlePopupCloose() {}
	  getCurrentValueState() {
	    return BX.message('WEBF_RES_NO_VALUE');
	  }
	  getPopupContent() {
	    this.DOM.innerWrap = calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: ''
	      }
	    });
	    this.DOM.innerWrap.style.minWidth = '500px';
	    this.DOM.innerWrap.style.minHeight = '30px';
	    return this.DOM.innerWrap;
	  }
	  getPopupWidth() {
	    return null;
	  }
	  handlePopupClick(e) {
	    var target = e.target || e.srcElement;
	    if (target.hasAttribute('data-bx-resbook-control-node') || BX.findParent(target, {
	      attribute: 'data-bx-resbook-control-node'
	    }, this.DOM.innerWrap)) {
	      this.handleControlChanges();
	    }
	  }
	  handleControlChanges() {
	    setTimeout(BX.delegate(function () {
	      BX.onCustomEvent('ResourceBooking.webformSettings:onChanged');
	    }, this), 50);
	  }
	  showPopupLoader() {
	    if (this.DOM.innerWrap) {
	      this.hidePopupLoader();
	      this.DOM.popupLoader = this.DOM.innerWrap.appendChild(calendar_resourcebooking.BookingUtil.getLoader(50));
	    }
	  }
	  hidePopupLoader() {
	    calendar_resourcebooking.Dom.remove(this.DOM.popupLoader);
	  }
	}
	class FormFieldTunnerMultipleChecknoxPopupAbstract extends FormFieldTunnerValuePopupAbstract {
	  constructor(params) {
	    super(params);
	    this.id = 'resourcebooking-settings-multiple-checknox-' + Math.round(Math.random() * 100000);
	  }
	  showPopup() {
	    if (this.isPopupShown()) {
	      return this.closePopup();
	    }
	    var menuItems = [];
	    this.values.forEach(function (item) {
	      menuItems.push({
	        id: item.id,
	        text: BX.util.htmlspecialchars(item.title),
	        dataset: item.dataset,
	        onclick: BX.proxy(this.menuItemClick, this)
	      });
	    }, this);
	    if (menuItems.length > 1) {
	      this.selectAllMessage = this.selectAllMessage || 'select all';
	      menuItems.push({
	        text: this.selectAllMessage,
	        onclick: BX.proxy(this.selectAllItemClick, this)
	      });
	    }
	    this.popup = BX.PopupMenu.create(this.id, this.DOM.valueLink, menuItems, {
	      className: 'popup-window-resource-select',
	      closeByEsc: true,
	      autoHide: false,
	      offsetTop: 0,
	      offsetLeft: 0
	    });
	    this.popup.popupWindow.setAngle({
	      offset: 60,
	      position: 'top'
	    });
	    this.popup.show(true);
	    this.popupContainer = this.popup.popupWindow.popupContainer;
	    BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function () {
	      this.handlePopupCloose();
	      BX.PopupMenu.destroy(this.id);
	      this.popup = null;
	    }, this));
	    this.popup.menuItems.forEach(function (menuItem) {
	      var checked;
	      if (menuItem.dataset && menuItem.dataset.id) {
	        checked = this.selectedValues.find(function (itemId) {
	          return itemId === menuItem.id;
	        });
	        menuItem.layout.item.className = 'menu-popup-item';
	        menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' + '</div>' + '</div>';
	      } else {
	        this.selectAllChecked = !this.values.find(function (value) {
	          return !this.selectedValues.find(function (itemId) {
	            return itemId === value.id;
	          });
	        }, this);
	        menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
	        menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + menuItem.text + '</label>' + '</div>' + '</div>';
	      }
	    }, this);
	    setTimeout(BX.delegate(function () {
	      BX.bind(document, 'click', BX.proxy(this.handleClick, this));
	    }, this), 50);
	  }
	  menuItemClick(e, menuItem) {
	    var selectAllcheckbox,
	      target = e.target || e.srcElement,
	      checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
	      foundValue = this.values.find(function (value) {
	        return value.id === menuItem.id;
	      });
	    if (foundValue) {
	      if (target && (calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item") || calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item-resource-checkbox") || calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item-inner"))) {
	        if (!calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item-resource-checkbox")) {
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
	      }
	      this.handleControlChanges();
	    }
	  }
	  selectItem(value) {
	    if (!BX.util.in_array(value.id, this.selectedValues)) {
	      this.selectedValues.push(value.id);
	    }
	  }
	  deselectItem(value) {
	    var index = BX.util.array_search(value.id, this.selectedValues);
	    if (index >= 0) {
	      this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
	    }
	  }
	  selectAllItemClick(e, menuItem) {
	    var target = e.target || e.srcElement;
	    if (target && (calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item") || calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item-resource-checkbox"))) {
	      var checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');
	      if (calendar_resourcebooking.Dom.hasClass(target, "menu-popup-item")) {
	        checkbox.checked = !checkbox.checked;
	      }
	      var i,
	        checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
	      this.selectAllChecked = checkbox.checked;
	      for (i = 0; i < checkboxes.length; i++) {
	        checkboxes[i].checked = this.selectAllChecked;
	      }
	      this.selectedValues = [];
	      if (this.selectAllChecked) {
	        this.values.forEach(function (value) {
	          this.selectedValues.push(value.id);
	        }, this);
	      }
	      this.handleControlChanges();
	    }
	  }
	  handleClick(e) {
	    var target = e.target || e.srcElement;
	    if (this.isPopupShown() && !BX.isParentForNode(this.popupContainer, target)) {
	      this.closePopup({
	        animation: true
	      });
	    }
	    this.handleControlChanges();
	  }
	  closePopup() {
	    if (this.isPopupShown()) {
	      this.popup.close();
	      this.popupContainer.style.maxHeight = '';
	      BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
	    }
	  }
	  getSelectedValues() {
	    return this.selectedValues;
	  }
	}

	let _ = t => t,
	  _t,
	  _t2;
	class UserSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_USERS');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_USERS_LABEL');
	    this.displayed = true;
	    this.selectedUsers = [];
	  }
	  updateConfig(params) {
	    super.updateConfig(params);
	    this.defaultMode = params.defaultMode;
	  }
	  buildStatePopup(params) {
	    params.isDisplayed = this.isDisplayed.bind(this);
	    params.defaultMode = params.defaultMode || this.defaultMode;
	    this.statePopup = new UsersStatePopup(params);
	  }
	  buildValuePopup(params) {
	    this.selectedUsers = main_core.Type.isArray(params.config.selected) ? params.config.selected : params.config.selected.split('|');
	    this.DOM.valueWrap = params.wrap;
	    this.DOM.valueWrap.appendChild(main_core.Tag.render(_t || (_t = _`
				<div class="calendar-resbook-webform-settings-popup-select-result">
					${0}
				</div>
			`), this.DOM.usersValueLink = main_core.Tag.render(_t2 || (_t2 = _`
						<span 
							class="calendar-resbook-webform-settings-popup-select-value"
							onclick="${0}"
							>
								${0}
						</span>
					`), this.showUserSelectorDialog.bind(this), this.getCurrentUsersValueText())));
	  }
	  getCurrentUsersValueText() {
	    const count = this.selectedUsers.length;
	    return count ? count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_USER', count) : main_core.Loc.getMessage('WEBF_RES_NO_VALUE');
	  }
	  showUserSelectorDialog() {
	    if (!(this.userSelectorDialog instanceof ui_entitySelector.Dialog)) {
	      this.userSelectorDialog = new ui_entitySelector.Dialog({
	        targetNode: this.DOM.usersValueLink,
	        context: 'RESOURCEBOOKING',
	        preselectedItems: this.selectedUsers.map(userId => {
	          return ['user', userId];
	        }),
	        enableSearch: true,
	        zIndex: this.zIndex + 10,
	        events: {
	          'Item:onSelect': this.handleUserSelectorChanges.bind(this),
	          'Item:onDeselect': this.handleUserSelectorChanges.bind(this)
	        },
	        entities: [{
	          id: 'user',
	          options: {
	            inviteGuestLink: false,
	            emailUsers: false,
	            analyticsSource: 'calendar'
	          }
	        }]
	      });
	    }
	    this.userSelectorDialog.show();
	  }
	  handleUserSelectorChanges() {
	    this.selectedUsers = [];
	    this.userSelectorDialog.getSelectedItems().forEach(item => {
	      if (item.entityId === "user") {
	        this.selectedUsers.push(item.id);
	      }
	    });
	    this.DOM.usersValueLink.innerHTML = this.getCurrentUsersValueText();
	    main_core_events.EventEmitter.emit('ResourceBooking.settingsUserSelector:onChanged');
	    setTimeout(() => {
	      main_core_events.EventEmitter.emit('ResourceBooking.webformSettings:onChanged');
	    }, 50);
	  }
	  displayInForm() {
	    super.displayInForm();
	    this.statePopup.handleControlChanges();
	    this.statePopup.setEnabled();
	  }
	  hideInForm() {
	    super.hideInForm();
	    this.statePopup.handleControlChanges();
	    this.statePopup.setDisabled();
	  }
	  getValue() {
	    return {
	      show: this.isDisplayed() ? 'Y' : 'N',
	      label: this.getFormLabel(),
	      defaultMode: this.statePopup.getDefaultMode(),
	      value: this.selectedUsers
	    };
	  }
	}
	class UsersStatePopup extends FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'usersStatePopup';
	    this.inputName = 'user-select-mode';
	    this.id = 'users-state-' + Math.round(Math.random() * 1000);
	    this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
	    this.isDisplayed = main_core.Type.isFunction(params.isDisplayed) ? params.isDisplayed : function () {
	      return false;
	    };
	    this.build();
	  }
	  build() {
	    super.build();
	    this.handleControlChanges();
	  }
	  getMenuItems() {
	    return [new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DEFAULT_TITLE'),
	      delimiter: true
	    }), {
	      id: 'users-state-list',
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DEFAULT_EMPTY'),
	      dataset: {
	        type: 'radio',
	        value: 'none',
	        inputName: this.inputName,
	        checked: this.defaultMode === 'none'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: 'users-state-auto',
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DEFAULT_FREE_USER'),
	      dataset: {
	        type: 'radio',
	        value: 'auto',
	        inputName: this.inputName,
	        checked: this.defaultMode === 'auto'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }];
	  }
	  menuItemClick(e, menuItem) {
	    var target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input' && menuItem.dataset && menuItem.dataset.inputName === this.inputName) {
	      this.defaultMode = menuItem.dataset.value;
	    }
	    this.handleControlChanges();
	    setTimeout(this.closePopup.bind(this), 50);
	  }
	  getCurrentModeState() {
	    return this.isDisplayed() ? main_core.Loc.getMessage('WEBF_RES_SELECT_USER_FROM_LIST_SHORT') + (this.defaultMode === 'none' ? '' : ',<br>' + main_core.Loc.getMessage('WEBF_RES_AUTO_SELECT_USER_SHORT')) : main_core.Loc.getMessage('WEBF_RES_SELECT_USER_FROM_LIST_AUTO');
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	    BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	  }
	  getDefaultMode() {
	    return this.defaultMode;
	  }
	}

	class ResourceSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_RESOURCES');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_RESOURCES_LABEL');
	    this.displayed = true;
	  }
	  updateConfig(params) {
	    super.updateConfig(params);
	    this.defaultMode = params.defaultMode;
	    this.multiple = params.multiple === 'Y';
	  }
	  buildStatePopup(params) {
	    params.isDisplayed = this.isDisplayed.bind(this);
	    params.defaultMode = params.defaultMode || this.defaultMode;
	    params.multiple = params.multiple == null ? this.multiple : params.multiple;
	    this.statePopup = new ResourcesStatePopup(params);
	  }
	  buildValuePopup(params) {
	    this.valuePopup = new ResourcesValuePopup(params);
	  }
	  displayInForm() {
	    super.displayInForm();
	    this.statePopup.handleControlChanges();
	    this.statePopup.setEnabled();
	  }
	  hideInForm() {
	    super.hideInForm();
	    this.statePopup.handleControlChanges();
	    this.statePopup.setDisabled();
	  }
	  getValue() {
	    return {
	      show: this.isDisplayed() ? 'Y' : 'N',
	      label: this.getFormLabel(),
	      defaultMode: this.statePopup.getDefaultMode(),
	      multiple: this.statePopup.getMultiple() ? 'Y' : 'N',
	      value: this.valuePopup.getSelectedId()
	    };
	  }
	}
	class ResourcesStatePopup extends FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'resourcesStatePopup';
	    this.inputName = 'resource-select-mode';
	    this.defaultMode = params.defaultMode === 'none' ? 'none' : 'auto';
	    this.multiple = !!params.multiple;
	    this.isDisplayed = main_core.Type.isFunction(params.isDisplayed) ? params.isDisplayed : function () {
	      return false;
	    };
	    this.build();
	  }
	  build() {
	    super.build();
	    this.handleControlChanges();
	  }
	  getMenuItems() {
	    return [new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DEFAULT_TITLE'),
	      delimiter: true
	    }), {
	      id: 'resources-state-list',
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DEFAULT_EMPTY'),
	      dataset: {
	        type: 'radio',
	        value: 'none',
	        inputName: this.inputName,
	        checked: this.defaultMode === 'none'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: 'resources-state-auto',
	      text: main_core.Loc.getMessage('WEBF_RES_AUTO_SELECT_RES'),
	      dataset: {
	        type: 'radio',
	        value: 'auto',
	        inputName: this.inputName,
	        checked: this.defaultMode === 'auto'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      delimiter: true
	    }, {
	      id: 'resources-state-multiple',
	      text: main_core.Loc.getMessage('WEBF_RES_MULTIPLE'),
	      dataset: {
	        type: 'checkbox',
	        value: 'Y',
	        checked: this.multiple
	      },
	      onclick: this.menuItemClick.bind(this)
	    }];
	  }
	  getCurrentModeState() {
	    return this.isDisplayed() ? main_core.Loc.getMessage('WEBF_RES_SELECT_RES_FROM_LIST_SHORT') + (this.defaultMode === 'none' ? '' : ',<br>' + main_core.Loc.getMessage('WEBF_RES_AUTO_SELECT_RES_SHORT')) : main_core.Loc.getMessage('WEBF_RES_SELECT_RES_FROM_LIST_AUTO');
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	    BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	  }
	  menuItemClick(e, menuItem) {
	    let target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input' && menuItem.dataset) {
	      if (menuItem.dataset.inputName === this.inputName) {
	        this.defaultMode = menuItem.dataset.value;
	      } else if (menuItem.id === 'resources-state-multiple') {
	        this.multiple = !!target.checked;
	      }
	    }
	    this.handleControlChanges();
	  }
	  getDefaultMode() {
	    return this.defaultMode;
	  }
	  getMultiple() {
	    return this.multiple;
	  }
	}
	class ResourcesValuePopup extends FormFieldTunnerMultipleChecknoxPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'resourcesValuePopup';
	    this.selectAllMessage = main_core.Loc.getMessage('USER_TYPE_RESOURCE_SELECT_ALL');
	    let selectedItems,
	      selectedIndex = {},
	      selectAll = params.config.selected === null;
	    if (main_core.Type.isArray(params.config.selected)) {
	      selectedItems = params.config.selected;
	    } else if (main_core.Type.isString(params.config.selected)) {
	      selectedItems = params.config.selected.split('|');
	    }
	    if (main_core.Type.isArray(selectedItems)) {
	      for (let i = 0; i < selectedItems.length; i++) {
	        selectedIndex[selectedItems[i]] = true;
	      }
	    }
	    this.values = [];
	    this.selectedValues = [];
	    if (main_core.Type.isArray(params.config.resources)) {
	      params.config.resources.forEach(function (resource) {
	        let valueId = this.prepareValueId(resource);
	        this.values.push({
	          id: valueId,
	          title: resource.title,
	          dataset: resource
	        });
	        if (selectAll || selectedIndex[resource.id]) {
	          this.selectedValues.push(valueId);
	        }
	      }, this);
	    }
	    this.build();
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    main_core.Dom.adjust(this.DOM.valueLink, {
	      text: this.getCurrentValueState()
	    });
	  }
	  getCurrentValueState() {
	    let count = this.selectedValues.length;
	    return count ? count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_RESOURCE', count) : main_core.Loc.getMessage('WEBF_RES_NO_VALUE');
	  }
	  prepareValueId(resource) {
	    return resource.type + '|' + resource.id;
	  }
	  getSelectedId() {
	    let result = [];
	    this.getSelectedValues().forEach(function (value) {
	      let val = value.split('|');
	      if (val && val[1]) {
	        result.push(parseInt(val[1]));
	      }
	    });
	    return result;
	  }
	}

	class ServiceSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_SERVICES');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_SERVICE_LABEL');
	    this.displayed = true;
	  }
	  buildStatePopup(params) {
	    if (params && main_core.Type.isDomNode(params.wrap)) {
	      params.wrap.appendChild(main_core.Dom.create("div", {
	        props: {
	          className: 'calendar-resbook-webform-settings-popup-select disabled'
	        },
	        html: '<span class="calendar-resbook-webform-settings-popup-select-value">' + main_core.Loc.getMessage('WEBF_RES_FROM_LIST') + '</span>'
	      }));
	    }
	  }
	  buildValuePopup(params) {
	    this.valuePopup = new ServiceValuePopup(params);
	  }
	  getValue() {
	    return {
	      show: this.isDisplayed() ? 'Y' : 'N',
	      label: this.getFormLabel(),
	      value: this.valuePopup.getSelectedValues()
	    };
	  }
	}
	class ServiceValuePopup extends FormFieldTunnerMultipleChecknoxPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'ServiceValuePopup';
	    this.selectAllMessage = main_core.Loc.getMessage('WEBF_RES_SELECT_ALL_SERVICES');
	    let selectAll = params.config.selected === null || params.config.selected === '' || params.config.selected === undefined;
	    this.values = [];
	    this.selectedValues = [];
	    let selectedItems,
	      selectedIndex = {};
	    if (main_core.Type.isArray(params.config.selected)) {
	      selectedItems = params.config.selected;
	    } else if (main_core.Type.isString(params.config.selected)) {
	      selectedItems = params.config.selected.split('|');
	    }
	    if (main_core.Type.isArray(selectedItems)) {
	      for (let i = 0; i < selectedItems.length; i++) {
	        selectedIndex[calendar_resourcebooking.BookingUtil.translit(selectedItems[i])] = true;
	      }
	    }
	    if (main_core.Type.isArray(params.config.services)) {
	      params.config.services.forEach(function (service) {
	        service.id = calendar_resourcebooking.BookingUtil.translit(service.name);
	        if (service.id !== '') {
	          this.values.push({
	            id: service.id,
	            title: service.name + ' - ' + calendar_resourcebooking.BookingUtil.getDurationLabel(service.duration),
	            dataset: service
	          });
	          if (selectAll || selectedIndex[calendar_resourcebooking.BookingUtil.translit(service.name)]) {
	            this.selectedValues.push(service.id);
	          }
	        }
	      }, this);
	    }
	    this.config = {};
	    this.build();
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    main_core.Dom.adjust(this.DOM.valueLink, {
	      text: this.getCurrentValueState()
	    });
	  }
	  getSelectedValues() {
	    return this.selectedValues.length ? this.selectedValues : '#EMPTY-SERVICE-LIST#';
	  }
	  getCurrentValueState() {
	    let count = this.selectedValues.length;
	    return count ? count + ' ' + ResourcebookingUserfield.getPluralMessage('WEBF_RES_SERVICE', count) : main_core.Loc.getMessage('WEBF_RES_NO_VALUE');
	  }
	}

	class DurationSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_DURATION');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_DURATION_LABEL');
	  }
	  updateConfig(params) {
	    super.updateConfig();
	    this.defaultValue = params.defaultValue;
	    this.manualInput = params.manualInput === 'Y';
	  }
	  buildStatePopup(params) {
	    params.isDisplayed = this.isDisplayed.bind(this);
	    params.defaultValue = this.defaultValue;
	    params.manualInput = this.manualInput;
	    this.statePopup = new DurationStatePopup(params);
	  }
	  displayInForm() {
	    super.displayInForm();
	    this.statePopup.handleControlChanges();
	  }
	  hideInForm() {
	    super.hideInForm();
	    this.statePopup.handleControlChanges();
	  }
	  getValue() {
	    return {
	      show: this.isDisplayed() ? 'Y' : 'N',
	      label: this.getFormLabel(),
	      defaultValue: this.statePopup.getDefaultValue(),
	      manualInput: this.statePopup.getManualInput() ? 'Y' : 'N'
	    };
	  }
	}
	class DurationStatePopup extends FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'durationStatePopup';
	    this.inputName = 'duration-select-mode';
	    this.manualInput = !!params.manualInput;
	    this.defaultValue = params.defaultValue || 60;
	    this.isDisplayed = main_core.Type.isFunction(params.isDisplayed) ? params.isDisplayed : function () {
	      return false;
	    };
	    this.durationList = calendar_resourcebooking.BookingUtil.getDurationList(params.fullDay);
	    this.build();
	  }
	  build() {
	    super.build();
	    this.handleControlChanges();
	  }
	  getMenuItems() {
	    return [{
	      id: 'duration-default-value',
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_DURATION_AUTO'),
	      dataset: {
	        type: 'submenu-list',
	        value: this.defaultValue,
	        textValue: this.getDurationLabelByValue(this.defaultValue)
	      },
	      items: this.getDefaultMenuItems()
	    }].concat(this.isDisplayed() ? [{
	      delimiter: true
	    }, {
	      id: 'duration-manual-input',
	      text: main_core.Loc.getMessage('WEBF_RES_SELECT_MANUAL_INPUT'),
	      dataset: {
	        type: 'checkbox',
	        value: 'Y',
	        checked: this.manualInput
	      },
	      onclick: this.menuItemClick.bind(this)
	    }] : []);
	  }
	  getDefaultMenuItems() {
	    let menuItems = [];
	    if (main_core.Type.isArray(this.durationList)) {
	      this.durationList.forEach(function (item) {
	        menuItems.push({
	          id: 'duration-' + item.value,
	          dataset: {
	            type: 'duration',
	            value: item.value
	          },
	          text: item.label,
	          onclick: this.menuItemClick.bind(this)
	        });
	      }, this);
	    }
	    return menuItems;
	  }
	  getDurationLabelByValue(duration) {
	    let foundDuration = this.durationList.find(function (item) {
	      return parseInt(item.value) === parseInt(duration);
	    });
	    return foundDuration ? foundDuration.label : null;
	  }
	  getCurrentModeState() {
	    return this.isDisplayed() ? main_core.Loc.getMessage('WEBF_RES_SELECT_DURATION_FROM_LIST_SHORT') + (',<br>' + main_core.Loc.getMessage('WEBF_RES_SELECT_DURATION_BY_DEFAULT') + ' ' + this.getDurationLabelByValue(this.defaultValue)) : main_core.Loc.getMessage('WEBF_RES_SELECT_DURATION_AUTO') + ' ' + this.getDurationLabelByValue(this.defaultValue);
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	    BX.onCustomEvent(this, "ResourceBooking.userSettingsField:onControlChanged", []);
	  }
	  menuItemClick(e, menuItem) {
	    let target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input' && menuItem.dataset) {
	      if (menuItem.id === 'duration-manual-input') {
	        this.manualInput = !!target.checked;
	      }
	    } else if (menuItem.dataset && menuItem.dataset.type === 'duration') {
	      this.defaultValue = parseInt(menuItem.dataset.value);
	    }
	    this.handleControlChanges();
	  }
	  getManualInput() {
	    return this.manualInput;
	  }
	  getDefaultValue() {
	    return this.defaultValue;
	  }
	}

	class DateSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_DATE');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_DATE_LABEL');
	    this.displayed = true;
	    this.displayCheckboxDisabled = true;
	  }
	  updateConfig(params) {
	    super.updateConfig();
	    this.style = params.style;
	    this.start = params.start;
	  }
	  buildStatePopup(params) {
	    params.style = params.style || this.style;
	    params.start = params.start || this.start;
	    this.statePopup = new DateStatePopup(params);
	  }
	  getValue() {
	    return {
	      label: this.getFormLabel(),
	      style: this.statePopup.getStyle(),
	      start: this.statePopup.getStart()
	    };
	  }
	}
	class DateStatePopup extends FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'dateStatePopup';
	    this.styleInputName = 'date-select-style';
	    this.startInputName = 'date-select-start';
	    this.style = params.style === 'popup' ? 'popup' : 'line'; // popup|line
	    this.start = params.start === 'today' ? 'today' : 'free'; // today|free
	    this.build();
	  }
	  getMenuItems() {
	    return [new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_STYLE'),
	      delimiter: true
	    }), {
	      id: 'date-state-style-popup',
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_STYLE_POPUP'),
	      dataset: {
	        type: 'radio',
	        value: 'popup',
	        inputName: this.styleInputName,
	        checked: this.style === 'popup'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: 'date-state-style-line',
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_STYLE_LINE'),
	      dataset: {
	        type: 'radio',
	        value: 'line',
	        inputName: this.styleInputName,
	        checked: this.style === 'line'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_START_FROM'),
	      delimiter: true
	    }), {
	      id: 'date-state-start-from-today',
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_TODAY'),
	      dataset: {
	        type: 'radio',
	        value: 'today',
	        inputName: this.startInputName,
	        checked: this.start === 'today'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: 'date-state-start-from-free',
	      text: main_core.Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_FREE'),
	      dataset: {
	        type: 'radio',
	        value: 'free',
	        inputName: this.startInputName,
	        checked: this.start === 'free'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }];
	  }
	  getCurrentModeState() {
	    return (this.style === 'popup' ? main_core.Loc.getMessage('WEBF_RES_CALENDAR_STYLE_POPUP') : main_core.Loc.getMessage('WEBF_RES_CALENDAR_STYLE_LINE')) + ', ' + (this.start === 'today' ? main_core.Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_TODAY_SHORT') : main_core.Loc.getMessage('WEBF_RES_CALENDAR_START_FROM_FREE_SHORT'));
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    main_core.Dom.adjust(this.DOM.currentStateLink, {
	      text: this.getCurrentModeState()
	    });
	  }
	  menuItemClick(e, menuItem) {
	    let target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input' && menuItem.dataset) {
	      if (menuItem.dataset.inputName === this.styleInputName) {
	        this.style = menuItem.dataset.value;
	      } else if (menuItem.dataset.inputName === this.startInputName) {
	        this.start = menuItem.dataset.value;
	      }
	    }
	    this.handleControlChanges();
	  }
	  getStyle() {
	    return this.style;
	  }
	  getStart() {
	    return this.start;
	  }
	}

	class TimeSelectorFieldTunner extends FormFieldTunnerAbstract {
	  constructor() {
	    super();
	    this.label = main_core.Loc.getMessage('WEBF_RES_TIME');
	    this.formLabel = main_core.Loc.getMessage('WEBF_RES_TIME_LABEL');
	    this.displayed = true;
	    this.displayCheckboxDisabled = true;
	  }
	  updateConfig(params) {
	    super.updateConfig();
	    this.style = params.style;
	    this.showOnlyFree = params.showOnlyFree === 'Y';
	    this.showFinishTime = params.showFinishTime === 'Y';
	    this.scale = parseInt(params.scale);
	  }
	  buildStatePopup(params) {
	    params.style = params.style || this.style;
	    params.showOnlyFree = this.showOnlyFree;
	    params.showFinishTime = this.showFinishTime;
	    params.scale = this.scale;
	    this.statePopup = new TimeStatePopup(params);
	  }
	  getValue() {
	    return {
	      label: this.getFormLabel(),
	      style: this.statePopup.getStyle(),
	      showFinishTime: this.statePopup.getShowFinishTime(),
	      showOnlyFree: this.statePopup.getShowOnlyFree(),
	      scale: this.statePopup.getScale()
	    };
	  }
	}
	class TimeStatePopup extends FormFieldTunnerPopupAbstract {
	  constructor(params) {
	    super(params);
	    this.name = 'timeStatePopup';
	    this.styleInputName = 'date-select-style';
	    this.showOnlyFree = params.showOnlyFree;
	    this.showFinishTime = params.showFinishTime;
	    this.scale = params.scale;
	    this.stateShowFreeId = 'time-state-show-free';
	    this.stateShowFinishId = 'time-state-show-finish';
	    this.style = params.style === 'select' ? 'select' : 'slots'; // select|slots

	    this.build();
	  }
	  build() {
	    super.build();
	    this.handleControlChanges();
	  }
	  getMenuItems() {
	    return [new main_popup.MenuItem({
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_STYLE'),
	      delimiter: true
	    }), {
	      id: 'time-state-style-select',
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_STYLE_SELECT'),
	      dataset: {
	        type: 'radio',
	        value: 'select',
	        inputName: this.styleInputName,
	        checked: this.style === 'select'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: 'time-state-style-slots',
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_STYLE_SLOT'),
	      dataset: {
	        type: 'radio',
	        value: 'slots',
	        inputName: this.styleInputName,
	        checked: this.style === 'slots'
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      delimiter: true
	    }, {
	      id: 'time-state-scale',
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_BOOKING_SIZE'),
	      dataset: {
	        type: 'submenu-list',
	        value: this.scale,
	        textValue: this.getDurationLabelByValue(this.scale)
	      },
	      items: this.getDurationMenuItems()
	    }, {
	      delimiter: true
	    }, {
	      id: this.stateShowFreeId,
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_SHOW_FREE_ONLY'),
	      dataset: {
	        type: 'checkbox',
	        value: 'Y',
	        checked: this.showOnlyFree
	      },
	      onclick: this.menuItemClick.bind(this)
	    }, {
	      id: this.stateShowFinishId,
	      text: main_core.Loc.getMessage('WEBF_RES_TIME_SHOW_FINISH_TIME'),
	      dataset: {
	        type: 'checkbox',
	        value: 'Y',
	        checked: this.showFinishTime
	      },
	      onclick: this.menuItemClick.bind(this)
	    }];
	  }
	  getCurrentModeState() {
	    return (this.style === 'select' ? main_core.Loc.getMessage('WEBF_RES_TIME_STYLE_SELECT') : main_core.Loc.getMessage('WEBF_RES_TIME_STYLE_SLOT')) + ',<br>' + main_core.Loc.getMessage('WEBF_RES_TIME_BOOKING_SIZE') + ': ' + this.getDurationLabelByValue(this.scale);
	  }
	  handleControlChanges() {
	    super.handleControlChanges();
	    this.DOM.currentStateLink.innerHTML = this.getCurrentModeState();
	  }
	  menuItemClick(e, menuItem) {
	    let target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input' && menuItem.dataset) {
	      if (menuItem.dataset.inputName === this.styleInputName) {
	        this.style = menuItem.dataset.value;
	      } else if (menuItem.id === this.stateShowFreeId) {
	        this.showOnlyFree = !!target.checked;
	      } else if (menuItem.id === this.stateShowFinishId) {
	        this.showFinishTime = !!target.checked;
	      }
	    } else if (menuItem.dataset && menuItem.dataset.type === 'scale') {
	      this.scale = parseInt(menuItem.dataset.value);
	    }
	    this.handleControlChanges();
	  }
	  getDurationMenuItems() {
	    let durationList = this.getDurationList(),
	      menuItems = [];
	    durationList.forEach(function (duration) {
	      menuItems.push({
	        id: 'duration-' + duration.value,
	        dataset: {
	          type: 'scale',
	          value: duration.value
	        },
	        text: duration.label,
	        onclick: this.menuItemClick.bind(this)
	      });
	    }, this);
	    return menuItems;
	  }
	  getDurationList() {
	    if (!this.durationList) {
	      this.durationList = calendar_resourcebooking.BookingUtil.getDurationList(false);
	      this.durationList = this.durationList.filter(function (duration) {
	        return duration.value && duration.value >= 15 && duration.value <= 240;
	      });
	    }
	    return this.durationList;
	  }
	  getDurationLabelByValue(duration) {
	    let foundDuration = this.getDurationList().find(function (item) {
	      return item.value === duration;
	    });
	    return foundDuration ? foundDuration.label : null;
	  }
	  getStyle() {
	    return this.style;
	  }
	  getScale() {
	    return this.scale;
	  }
	  getShowOnlyFree() {
	    return this.showOnlyFree ? 'Y' : 'N';
	  }
	  getShowFinishTime() {
	    return this.showFinishTime ? 'Y' : 'N';
	  }
	}

	class AdjustFieldController extends calendar_resourcebooking.EventEmitter {
	  constructor(params) {
	    super();
	    this.setEventNamespace('BX.Calendar.ResourcebookingUserfield.AdjustFieldController');
	    this.params = params;
	    this.complexFields = {};
	    this.userFieldParams = null;
	    this.id = 'resbook-settings-popup-' + Math.round(Math.random() * 100000);
	    this.settingsData = AdjustFieldController.getSettingsData(this.params.settings.data);
	    this.params.settings.data = this.settingsData;
	    this.DOM = {
	      innerWrap: this.params.innerWrap,
	      settingsWrap: this.params.innerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        attrs: {
	          'data-bx-resource-field-settings': 'Y'
	        }
	      })),
	      captionNode: this.params.captionNode,
	      settingsInputs: {}
	    };
	  }
	  init() {
	    // Request field params
	    this.showFieldLoader();
	    ResourcebookingUserfield.getUserFieldParams({
	      fieldName: this.params.entityName,
	      selectedUsers: this.getSelectedUsers()
	    }).then(fieldParams => {
	      this.hideFieldLoader();
	      this.userFieldParams = fieldParams;
	      this.fieldLayout = new calendar_resourcebooking.FieldViewControllerEdit({
	        wrap: this.DOM.innerWrap,
	        displayTitle: false,
	        title: this.getCaption(),
	        settings: this.getSettings()
	      });
	      this.fieldLayout.build();
	      this.updateSettingsDataInputs();
	      this.emit('afterInit', new calendar_resourcebooking.BaseEvent({
	        data: {
	          fieldName: this.params.entityName,
	          settings: this.getSettings()
	        }
	      }));
	    });
	  }
	  showSettingsPopup() {
	    ResourcebookingUserfield.getUserFieldParams({
	      fieldName: this.params.entityName,
	      selectedUsers: this.getSelectedUsers()
	    }).then(function (fieldParams) {
	      this.userFieldParams = fieldParams;
	      this.settingsPopupId = 'calendar-resourcebooking-settings-popup-' + Math.round(Math.random() * 100000);
	      this.settingsPopup = new BX.PopupWindow(this.settingsPopupId, null, {
	        content: this.getSettingsContentNode(),
	        className: 'calendar-resbook-webform-settings-popup-window',
	        autoHide: false,
	        lightShadow: true,
	        closeByEsc: true,
	        overlay: {
	          backgroundColor: 'black',
	          opacity: 500
	        },
	        zIndex: -400,
	        titleBar: calendar_resourcebooking.Loc.getMessage('WEBF_RES_SETTINGS'),
	        closeIcon: true,
	        buttons: [new BX.PopupWindowButton({})]
	      });
	      let buttonNodeWrap = this.settingsPopup.buttons[0].buttonNode.parentNode;
	      calendar_resourcebooking.Dom.remove(this.settingsPopup.buttons[0].buttonNode);
	      this.settingsPopup.buttons[0].buttonNode = buttonNodeWrap.appendChild(calendar_resourcebooking.Dom.create("button", {
	        props: {
	          className: 'ui-btn ui-btn-success'
	        },
	        events: {
	          click: function () {
	            this.settingsPopup.close();
	          }.bind(this)
	        },
	        text: calendar_resourcebooking.Loc.getMessage('WEBF_RES_CLOSE_SETTINGS_POPUP')
	      }));
	      BX.removeClass(this.settingsPopup.buttons[0].buttonNode, 'popup-window-button');
	      this.settingsPopup.show();
	      BX.addCustomEvent(this.settingsPopup, 'onPopupClose', function (popup) {
	        this.destroyControls();
	        this.settingsPopup.destroy(this.id);
	        this.settingsPopup = null;
	        if (this.previewFieldLayout) {
	          this.previewFieldLayout.destroy();
	        }
	      }.bind(this));
	    }.bind(this));
	  }
	  getSettingsContentNode() {
	    let outerWrap = calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup'
	      }
	    });
	    let leftWrap = outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-inner'
	      }
	    }));
	    this.buildSettingsForm({
	      wrap: leftWrap
	    });
	    let previewWrap = outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-preview'
	      }
	    }));
	    this.previewFieldLayout = new calendar_resourcebooking.FieldViewControllerPreview({
	      wrap: previewWrap,
	      title: this.getCaption(),
	      settings: this.getSettings()
	    });
	    this.previewFieldLayout.build();
	    BX.addCustomEvent('ResourceBooking.webformSettings:onChanged', this.handleWebformSettingsChanges.bind(this));
	    return outerWrap;
	  }
	  buildSettingsForm(params) {
	    let settings = this.getSettings(),
	      wrap = params.wrap,
	      titleId = 'title-' + this.id;
	    this.DOM.captionWrap = wrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-title'
	      },
	      html: '<label for="' + titleId + '" class="calendar-resbook-webform-settings-popup-label">' + calendar_resourcebooking.Loc.getMessage('WEBF_RES_NAME_LABEL') + '</label>'
	    }));
	    this.DOM.captionInput = this.DOM.captionWrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	      attrs: {
	        id: titleId,
	        className: 'calendar-resbook-webform-settings-popup-input',
	        type: 'text',
	        value: this.getCaption()
	      },
	      events: {
	        change: this.updateCaption.bind(this),
	        blur: this.updateCaption.bind(this),
	        keyup: this.updateCaption.bind(this)
	      }
	    }));
	    this.updateCaption();
	    this.DOM.fieldsOuterWrap = wrap.appendChild(calendar_resourcebooking.Dom.create('div', {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-content'
	      },
	      html: '<div class="calendar-resbook-webform-settings-popup-head">' + '<div class="calendar-resbook-webform-settings-popup-head-inner">' + '<span class="calendar-resbook-webform-settings-popup-head-text">' + calendar_resourcebooking.Loc.getMessage('WEBF_RES_FIELD_NAME') + '</span>' + '<span class="calendar-resbook-webform-settings-popup-head-decs">' + calendar_resourcebooking.Loc.getMessage('WEBF_RES_FIELD_NAME_IN_FORM') + '</span>' + '</div>' + '<div class="calendar-resbook-webform-settings-popup-head-inner">' + '<span class="calendar-resbook-webform-settings-popup-head-text">' + calendar_resourcebooking.Loc.getMessage('WEBF_RES_FIELD_SHOW_IN_FORM') + '</span>' + '</div>' + '</div>'
	    }));
	    this.DOM.fieldsWrap = this.DOM.fieldsOuterWrap.appendChild(calendar_resourcebooking.Dom.create('div', {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-list'
	      }
	    }));
	    if (settings.userfieldSettings.useUsers) {
	      this.buildComplexField('users', {
	        wrap: this.DOM.fieldsWrap,
	        changeSettingsCallback: this.updateSettings.bind(this),
	        params: settings.data.users,
	        config: {
	          users: settings.userfieldSettings.users,
	          selected: settings.data.users.value
	        }
	      });
	      BX.addCustomEvent('ResourceBooking.settingsUserSelector:onChanged', this.checkBitrix24Limitation.bind(this));
	    }
	    if (settings.userfieldSettings.useResources) {
	      this.buildComplexField('resources', {
	        wrap: this.DOM.fieldsWrap,
	        changeSettingsCallback: this.updateSettings.bind(this),
	        params: settings.data.resources,
	        config: {
	          resources: settings.userfieldSettings.resources,
	          selected: settings.data.resources.value
	        }
	      });
	    }
	    if (settings.userfieldSettings.useServices) {
	      this.buildComplexField('services', {
	        wrap: this.DOM.fieldsWrap,
	        changeSettingsCallback: this.updateSettings.bind(this),
	        params: settings.data.services,
	        config: {
	          services: settings.userfieldSettings.services,
	          selected: settings.data.services.value
	        }
	      });
	    } else {
	      this.buildComplexField('duration', {
	        wrap: this.DOM.fieldsWrap,
	        changeSettingsCallback: this.updateSettings.bind(this),
	        params: settings.data.duration
	      });
	    }
	    this.buildComplexField('date', {
	      wrap: this.DOM.fieldsWrap,
	      changeSettingsCallback: this.updateSettings.bind(this),
	      params: settings.data.date
	    });
	    if (!settings.userfieldSettings.fullDay) {
	      this.buildComplexField('time', {
	        wrap: this.DOM.fieldsWrap,
	        changeSettingsCallback: this.updateSettings.bind(this),
	        params: settings.data.time
	      });
	    }
	    this.DOM.fieldsWrap.appendChild(calendar_resourcebooking.Dom.create('div', {
	      props: {
	        className: 'calendar-resbook-webform-settings-popup-item'
	      },
	      html: '<div class="calendar-resbook-webform-settings-popup-decs">' + calendar_resourcebooking.Loc.getMessage('WEBF_RES_BOOKING_SETTINGS_HELP').replace('#START_LINK#', '<a href="javascript:void(0);"' + ' onclick="if (top.BX.Helper){top.BX.Helper.show(\'redirect=detail&code=8366733\');}">').replace('#END_LINK#', '</a>') + '</div>'
	    }));
	  }
	  destroyControls() {
	    for (let k in this.complexFields) {
	      if (this.complexFields.hasOwnProperty(k) && calendar_resourcebooking.Type.isFunction(this.complexFields[k].destroy)) {
	        this.complexFields[k].destroy();
	      }
	    }
	  }
	  handleWebformSettingsChanges() {
	    if (this.refreshLayoutTimeout) {
	      this.refreshLayoutTimeout = clearTimeout(this.refreshLayoutTimeout);
	    }
	    this.refreshLayoutTimeout = setTimeout(function () {
	      // Update settings and inputs
	      for (let k in this.complexFields) {
	        if (this.complexFields.hasOwnProperty(k) && calendar_resourcebooking.Type.isFunction(this.complexFields[k].getValue)) {
	          this.settingsData[k] = this.complexFields[k].getValue();
	        }
	      }
	      this.updateSettingsDataInputs();

	      // Refresh preview
	      this.previewFieldLayout.refreshLayout(this.settingsData);
	      // Refresh form layout (behind the settings popup)
	      this.fieldLayout.refreshLayout(this.settingsData);

	      // Small Hack to make form look better - height adjusment
	      this.previewFieldLayout.getOuterWrap().style.maxHeight = Math.round(this.previewFieldLayout.getInnerWrap().offsetHeight * 0.73) + 'px';
	    }.bind(this), 100);
	  }
	  buildComplexField(type, params) {
	    switch (type) {
	      case 'users':
	        this.complexFields[type] = new UserSelectorFieldTunner();
	        break;
	      case 'resources':
	        this.complexFields[type] = new ResourceSelectorFieldTunner();
	        break;
	      case 'services':
	        this.complexFields[type] = new ServiceSelectorFieldTunner();
	        break;
	      case 'duration':
	        this.complexFields[type] = new DurationSelectorFieldTunner();
	        break;
	      case 'date':
	        this.complexFields[type] = new DateSelectorFieldTunner();
	        break;
	      case 'time':
	        this.complexFields[type] = new TimeSelectorFieldTunner();
	        break;
	    }
	    if (calendar_resourcebooking.Type.isObject(this.complexFields[type])) {
	      this.complexFields[type].build(params);
	    }
	  }
	  static getSettingsData(data) {
	    let field,
	      option,
	      settingsData = BX.clone(AdjustFieldController.getDefaultSettingsData(), true);
	    if (calendar_resourcebooking.Type.isPlainObject(data)) {
	      for (field in data) {
	        if (data.hasOwnProperty(field) && settingsData[field]) {
	          if (calendar_resourcebooking.Type.isPlainObject(data[field])) {
	            for (option in data[field]) {
	              if (data[field].hasOwnProperty(option)) {
	                settingsData[field][option] = data[field][option];
	              }
	            }
	          } else {
	            settingsData[field] = data[field];
	          }
	        }
	      }
	    }
	    return settingsData;
	  }
	  static getDefaultSettingsData() {
	    return {
	      users: {
	        show: 'Y',
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_USERS_LABEL'),
	        defaultMode: 'auto',
	        // none|auto
	        value: null
	      },
	      resources: {
	        show: 'Y',
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_RESOURCES_LABEL'),
	        defaultMode: 'auto',
	        // none|auto
	        multiple: 'N',
	        value: null
	      },
	      services: {
	        show: 'Y',
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_SERVICE_LABEL'),
	        value: null
	      },
	      duration: {
	        show: 'Y',
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_DURATION_LABEL'),
	        defaultValue: 60,
	        manualInput: 'N'
	      },
	      date: {
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_DATE_LABEL'),
	        style: 'line',
	        // line|popup
	        start: 'today'
	      },
	      time: {
	        label: calendar_resourcebooking.Loc.getMessage('WEBF_RES_TIME_LABEL'),
	        style: 'slots',
	        showOnlyFree: 'Y',
	        showFinishTime: 'N',
	        scale: 60
	      }
	    };
	  }
	  getSelectedUsers() {
	    return this.settingsData && this.settingsData.users && calendar_resourcebooking.Type.isString(this.settingsData.users.value) ? this.settingsData.users.value.split('|') : [];
	  }
	  updateSettingsDataInputs() {
	    let field, option;
	    for (field in this.settingsData) {
	      if (this.settingsData.hasOwnProperty(field)) {
	        if (calendar_resourcebooking.Type.isPlainObject(this.settingsData[field])) {
	          for (option in this.settingsData[field]) {
	            if (this.settingsData[field].hasOwnProperty(option)) {
	              this.updateSettingsInputValue([field, option], this.settingsData[field][option]);
	            }
	          }
	        } else {
	          this.updateSettingsInputValue([field], this.settingsData[field]);
	        }
	      }
	    }
	  }
	  updateSettingsInputValue(key, value) {
	    let uniKey = key.join('-');
	    if (!this.DOM.settingsInputs[uniKey]) {
	      this.DOM.settingsInputs[uniKey] = this.DOM.settingsWrap.appendChild(calendar_resourcebooking.Dom.create('input', {
	        attrs: {
	          type: 'hidden',
	          name: this.params.formName + '[SETTINGS_DATA][' + key.join('][') + ']'
	        }
	      }));
	    }
	    if (calendar_resourcebooking.Type.isArray(value)) {
	      value = value.join('|');
	    }
	    this.DOM.settingsInputs[uniKey].value = value;
	  }
	  showFieldLoader() {
	    if (this.DOM.innerWrap) {
	      this.hideFieldLoader();
	      this.DOM.fieldLoader = this.DOM.innerWrap.appendChild(calendar_resourcebooking.BookingUtil.getLoader(100));
	    }
	  }
	  hideFieldLoader() {
	    calendar_resourcebooking.Dom.remove(this.DOM.fieldLoader);
	  }
	  getSettings() {
	    if (!this.params.settings.userfieldSettings) {
	      this.params.settings.userfieldSettings = {
	        resources: this.userFieldParams.SETTINGS.SELECTED_RESOURCES,
	        users: this.userFieldParams.SETTINGS.SELECTED_USERS,
	        services: this.userFieldParams.SETTINGS.SERVICE_LIST,
	        fullDay: this.userFieldParams.SETTINGS.FULL_DAY === 'Y',
	        useResources: this.userFieldParams.SETTINGS.USE_RESOURCES === 'Y' && this.userFieldParams.SETTINGS.SELECTED_RESOURCES.length,
	        useUsers: this.userFieldParams.SETTINGS.USE_USERS === 'Y',
	        useServices: this.userFieldParams.SETTINGS.USE_SERVICES === 'Y',
	        resourceLimit: this.userFieldParams.SETTINGS.RESOURCE_LIMIT,
	        userIndex: this.userFieldParams.SETTINGS.USER_INDEX
	      };
	    }
	    return this.params.settings;
	  }
	  updateSettings(settings) {}
	  getCaption() {
	    return this.params.settings.caption;
	  }
	  updateCaption() {
	    let caption = this.DOM.captionInput.value;
	    if (this.params.settings.caption !== caption || !this.DOM.settingsInputs.caption) {
	      this.params.settings.caption = caption;
	      if (this.previewFieldLayout) {
	        this.previewFieldLayout.updateTitle(this.params.settings.caption);
	      }

	      // Update title
	      if (!this.DOM.settingsInputs.caption) {
	        this.DOM.settingsInputs.caption = this.DOM.settingsWrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	          attrs: {
	            type: "hidden",
	            name: this.params.formName + '[CAPTION]'
	          }
	        }));
	      }
	      this.DOM.settingsInputs.caption.value = this.params.settings.caption;
	      if (this.DOM.captionNode) {
	        calendar_resourcebooking.Dom.adjust(this.DOM.captionNode, {
	          text: this.params.settings.caption
	        });
	      }
	    }
	  }
	  isRequired() {
	    return this.params.settings.required === 'Y';
	  }
	  updateRequiredValue() {
	    this.params.settings.required = this.DOM.requiredCheckbox.checked ? 'Y' : 'N';
	    if (!this.DOM.settingsInputs.required) {
	      this.DOM.settingsInputs.required = this.DOM.settingsWrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	        attrs: {
	          type: "hidden",
	          name: this.params.formName + '[REQUIRED]'
	        }
	      }));
	    }
	    this.DOM.settingsInputs.required.value = this.params.settings.required;
	  }
	  checkBitrix24Limitation() {
	    let count = 0,
	      settings = this.getSettings();
	    if (calendar_resourcebooking.Type.isArray(this.params.settings.userfieldSettings.resources)) {
	      count += this.params.settings.userfieldSettings.resources.length;
	    }
	    if (settings.userfieldSettings.useUsers && this.complexFields.users) {
	      let usersValue = this.complexFields.users.getValue();
	      if (usersValue && calendar_resourcebooking.Type.isArray(usersValue.value)) {
	        count += usersValue.value.length;
	      }
	    }
	    if (settings.userfieldSettings.resourceLimit > 0 && count > settings.userfieldSettings.resourceLimit) {
	      calendar_resourcebooking.BookingUtil.showLimitationPopup();
	    }
	  }
	}

	class UserSelectorFieldEditControl {
	  constructor(params) {
	    this.params = params || {};
	    this.id = this.params.id || 'user-selector-' + Math.round(Math.random() * 100000);
	    this.wrapNode = this.params.wrapNode;
	    this.destinationInputName = this.params.inputName || 'EVENT_DESTINATION';
	    this.params.selectGroups = false;
	    this.addMessage = this.params.addMessage || BX.message('USER_TYPE_RESOURCE_ADD_USER');
	    this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;
	    if (!this.params.itemsSelected) {
	      this.params.itemsSelected = this.getSocnetDestinationConfig('itemsSelected');
	    }
	    this.DOM = {
	      outerWrap: this.params.outerWrap,
	      wrapNode: this.params.wrapNode
	    };
	    this.create();
	  }
	  create() {
	    if (this.DOM.outerWrap) {
	      calendar_resourcebooking.Dom.addClass(this.DOM.outerWrap, 'calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));
	    }
	    let id = this.id;
	    BX.bind(this.wrapNode, 'click', BX.delegate(function (e) {
	      let target = e.target || e.srcElement;
	      if (target.className === 'calendar-resourcebook-content-block-control-delete')
	        // Delete button
	        {
	          BX.SocNetLogDestination.deleteItem(target.getAttribute('data-item-id'), target.getAttribute('data-item-type'), id);
	          let block = BX.findParent(target, {
	            className: 'calendar-resourcebook-content-block-control-inner'
	          });
	          if (block && BX.hasClass(block, 'shown')) {
	            BX.removeClass(block, 'shown');
	            setTimeout(function () {
	              BX.remove(block);
	            }, 300);
	          }
	        } else {
	        BX.SocNetLogDestination.openDialog(id);
	      }
	    }, this));
	    this.socnetDestinationInputWrap = this.wrapNode.appendChild(BX.create('SPAN', {
	      props: {
	        className: 'calendar-resourcebook-destination-input-box'
	      }
	    }));
	    this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(BX.create('INPUT', {
	      props: {
	        id: id + '-inp',
	        className: 'calendar-resourcebook-destination-input'
	      },
	      attrs: {
	        value: '',
	        type: 'text'
	      },
	      events: {
	        keydown: function (e) {
	          return BX.SocNetLogDestination.searchBeforeHandler(e, {
	            formName: id,
	            inputId: id + '-inp'
	          });
	        },
	        keyup: function (e) {
	          return BX.SocNetLogDestination.searchHandler(e, {
	            formName: id,
	            inputId: id + '-inp',
	            linkId: 'event-grid-dest-add-link',
	            sendAjax: true
	          });
	        }
	      }
	    }));
	    this.socnetDestinationLink = this.wrapNode.appendChild(BX.create('DIV', {
	      props: {
	        className: 'calendar-resourcebook-content-block-control-text calendar-resourcebook-content-block-control-text-add'
	      },
	      text: this.addMessage
	    }));
	    this.init();
	  }
	  show() {
	    if (this.DOM.outerWrap) {
	      calendar_resourcebooking.Dom.addClass(this.DOM.outerWrap, 'shown');
	    }
	  }
	  hide() {
	    if (this.DOM.outerWrap) {
	      BX.removeClass(this.DOM.outerWrap, 'shown');
	    }
	  }
	  isShown() {
	    if (this.DOM.outerWrap) {
	      return BX.hasClass(this.DOM.outerWrap, 'shown');
	    }
	  }
	  init() {
	    if (!this.socnetDestinationInput || !this.wrapNode) return;
	    let _this = this;
	    this.params.items = this.getSocnetDestinationConfig('items');
	    this.params.itemsLast = this.getSocnetDestinationConfig('itemsLast');
	    if (this.params.selectGroups === false) {
	      this.params.items.groups = {};
	      this.params.items.department = {};
	      this.params.items.sonetgroups = {};
	    }
	    BX.SocNetLogDestination.init({
	      name: this.id,
	      searchInput: this.socnetDestinationInput,
	      extranetUser: false,
	      userSearchArea: 'I',
	      bindMainPopup: {
	        node: this.wrapNode,
	        offsetTop: '5px',
	        offsetLeft: '15px'
	      },
	      bindSearchPopup: {
	        node: this.wrapNode,
	        offsetTop: '5px',
	        offsetLeft: '15px'
	      },
	      callback: {
	        select: BX.proxy(this.selectCallback, this),
	        unSelect: BX.proxy(this.unSelectCallback, this),
	        openDialog: BX.proxy(this.openDialogCallback, this),
	        closeDialog: BX.proxy(this.closeDialogCallback, this),
	        openSearch: BX.proxy(this.openDialogCallback, this),
	        closeSearch: function () {
	          _this.closeDialogCallback(true);
	        }
	      },
	      items: this.params.items,
	      itemsLast: this.params.itemsLast,
	      itemsSelected: this.params.itemsSelected,
	      departmentSelectDisable: this.params.selectGroups === false
	    });
	  }
	  closeAll() {
	    if (BX.SocNetLogDestination.isOpenDialog()) {
	      BX.SocNetLogDestination.closeDialog();
	    }
	    BX.SocNetLogDestination.closeSearch();
	  }
	  selectCallback(item, type) {
	    if (type === 'users') {
	      this.addUserBlock(item);
	      BX.onCustomEvent('OnResourceBookDestinationAddNewItem', [item, this.id]);
	      this.socnetDestinationInput.value = '';
	    }
	  }
	  addUserBlock(item, animation) {
	    if (this.checkLimit && !this.checkLimit()) {
	      return calendar_resourcebooking.BookingUtil.showLimitationPopup();
	    }
	    if (this.getAttendeesCodesList().includes(item.id)) {
	      return;
	    }
	    const blocks = this.wrapNode.querySelectorAll(`calendar-resourcebook-content-block-control-inner[data-id='${item.id}']`);
	    for (let i = 0; i < blocks.length; i++) {
	      BX.remove(blocks[i]);
	    }
	    const itemWrap = this.wrapNode.appendChild(BX.create("DIV", {
	      attrs: {
	        'data-id': item.id,
	        className: "calendar-resourcebook-content-block-control-inner green"
	      },
	      html: '<div class="calendar-resourcebook-content-block-control-text">' + item.name + '</div>' + '<div data-item-id="' + item.id + '" data-item-type="users" class="calendar-resourcebook-content-block-control-delete"></div>' + '<input type="hidden" name="' + this.destinationInputName + '[U][]' + '" value="' + item.id + '">'
	    }));
	    if (animation !== false) {
	      setTimeout(BX.delegate(function () {
	        calendar_resourcebooking.Dom.addClass(itemWrap, 'shown');
	      }, this), 1);
	    } else {
	      calendar_resourcebooking.Dom.addClass(itemWrap, 'shown');
	    }
	    this.wrapNode.appendChild(this.socnetDestinationInputWrap);
	    this.wrapNode.appendChild(this.socnetDestinationLink);
	  }
	  unSelectCallback(item) {
	    let elements = BX.findChildren(this.wrapNode, {
	      attribute: {
	        'data-id': item.id
	      }
	    }, true);
	    if (elements != null) {
	      for (let j = 0; j < elements.length; j++) {
	        BX.remove(elements[j]);
	      }
	    }
	    BX.onCustomEvent('OnResourceBookDestinationUnselect', [item, this.id]);
	    this.socnetDestinationInput.value = '';
	    this.socnetDestinationLink.innerHTML = this.addMessage;
	  }
	  openDialogCallback() {
	    BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
	    BX.style(this.socnetDestinationLink, 'display', 'none');
	    BX.focus(this.socnetDestinationInput);
	  }
	  closeDialogCallback(cleanInputValue) {
	    if (!BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0) {
	      BX.style(this.socnetDestinationInputWrap, 'display', 'none');
	      BX.style(this.socnetDestinationLink, 'display', 'inline-block');
	      if (cleanInputValue === true) this.socnetDestinationInput.value = '';

	      // Disable backspace
	      if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null) BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
	      BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function (e) {
	        if (e.keyCode === 8) {
	          e.preventDefault();
	          return false;
	        }
	      });
	      setTimeout(function () {
	        BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
	        BX.SocNetLogDestination.backspaceDisable = null;
	      }, 5000);
	    }
	  }
	  getCodes() {
	    let inputsList = this.wrapNode.getElementsByTagName('INPUT'),
	      codes = [],
	      i,
	      value;
	    for (i = 0; i < inputsList.length; i++) {
	      value = BX.util.trim(inputsList[i].value);
	      if (value) {
	        codes.push(inputsList[i].value);
	      }
	    }
	    return codes;
	  }
	  getAttendeesCodes() {
	    let inputsList = this.wrapNode.getElementsByTagName('INPUT'),
	      values = [],
	      i;
	    for (i = 0; i < inputsList.length; i++) {
	      values.push(inputsList[i].value);
	    }
	    return this.convertAttendeesCodes(values);
	  }
	  convertAttendeesCodes(values) {
	    let attendeesCodes = {};
	    if (BX.type.isArray(values)) {
	      values.forEach(function (code) {
	        if (code.substr(0, 2) === 'DR') {
	          attendeesCodes[code] = "department";
	        } else if (code.substr(0, 2) === 'UA') {
	          attendeesCodes[code] = "groups";
	        } else if (code.substr(0, 2) === 'SG') {
	          attendeesCodes[code] = "sonetgroups";
	        } else if (code.substr(0, 1) === 'U') {
	          attendeesCodes[code] = "users";
	        }
	      });
	    }
	    return attendeesCodes;
	  }
	  getAttendeesCodesList(codes) {
	    let result = [];
	    if (!codes) codes = this.getAttendeesCodes();
	    for (let i in codes) {
	      if (codes.hasOwnProperty(i)) {
	        result.push(i);
	      }
	    }
	    return result;
	  }
	  getSocnetDestinationConfig(key) {
	    let res,
	      socnetDestination = this.params.socnetDestination || {};
	    if (key === 'items') {
	      res = {
	        users: socnetDestination.USERS || {},
	        groups: socnetDestination.EXTRANET_USER === 'Y' || socnetDestination.DENY_TOALL ? {} : {
	          UA: {
	            id: 'UA',
	            name: BX.message('USER_TYPE_RESOURCE_TO_ALL_USERS')
	          }
	        },
	        sonetgroups: socnetDestination.SONETGROUPS || {},
	        department: socnetDestination.DEPARTMENT || {},
	        departmentRelation: socnetDestination.DEPARTMENT_RELATION || {}
	      };
	    } else if (key === 'itemsLast' && socnetDestination.LAST) {
	      res = {
	        users: socnetDestination.LAST.USERS || {},
	        groups: socnetDestination.EXTRANET_USER === 'Y' ? {} : {
	          UA: true
	        },
	        sonetgroups: socnetDestination.LAST.SONETGROUPS || {},
	        department: socnetDestination.LAST.DEPARTMENT || {}
	      };
	    } else if (key === 'itemsSelected') {
	      res = socnetDestination.SELECTED || {};
	    }
	    return res || {};
	  }
	  getSelectedValues() {
	    let result = [],
	      i,
	      inputs = this.wrapNode.querySelectorAll('input');
	    for (i = 0; i < inputs.length; i++) {
	      if (inputs[i].type === 'hidden' && inputs[i].value) {
	        if (inputs[i].value.substr(0, 1) === 'U') {
	          result.push(parseInt(inputs[i].value.substr(1)));
	        }
	      }
	    }
	    return result;
	  }
	  setValues(userList, trigerOnChange) {
	    let i, user;
	    const blocks = this.wrapNode.querySelectorAll('.calendar-resourcebook-content-block-control-inner');
	    for (i = 0; i < blocks.length; i++) {
	      BX.remove(blocks[i]);
	    }
	    for (i = 0; i < userList.length; i++) {
	      if (BX.SocNetLogDestination.obItems[this.id]['users']) {
	        user = BX.SocNetLogDestination.obItems[this.id]['users']['U' + userList[i]];
	        if (user) {
	          this.addUserBlock({
	            id: 'U' + userList[i],
	            name: user.name
	          }, false);
	        }
	      }
	    }
	    if (trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback)) {
	      setTimeout(BX.proxy(this.onChangeCallback, this), 100);
	    }
	  }
	  getId() {
	    return this.id;
	  }
	}

	class ResourceSelectorFieldEditControl {
	  constructor(params) {
	    this.params = params || {};
	    this.editMode = !!this.params.editMode;
	    this.id = this.params.id || 'resource-selector-' + Math.round(Math.random() * 100000);
	    this.resourceList = BX.type.isArray(params.resourceList) ? params.resourceList : [];
	    this.checkLimit = BX.type.isFunction(params.checkLimitCallback) ? params.checkLimitCallback : false;
	    this.checkLimitForNew = BX.type.isFunction(params.checkLimitCallbackForNew) ? params.checkLimitCallbackForNew : false;
	    this.selectedValues = [];
	    this.selectedValuesIndex = {};
	    this.selectedBlocks = [];
	    this.newValues = [];
	    this.DOM = {
	      outerWrap: this.params.outerWrap,
	      blocksWrap: this.params.blocksWrap || false,
	      listWrap: this.params.listWrap
	    };
	    if (this.editMode) {
	      this.DOM.controlsWrap = this.params.controlsWrap;
	    } else {
	      this.DOM.arrowNode = BX.create("span", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail-icon calendar-resourcebook-content-block-detail-icon-arrow"
	        }
	      });
	    }
	    this.onChangeCallback = this.params.onChangeCallback || null;
	    this.create();
	    this.setValues(params.values);
	  }
	  create() {
	    BX.addClass(this.DOM.outerWrap, 'calendar-resourcebook-resource-list-wrap calendar-resourcebook-folding-block' + (this.params.shown !== false ? ' shown' : ''));
	    if (this.editMode) {
	      this.DOM.addButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
	        props: {
	          className: "calendar-resource-content-block-add-link"
	        },
	        text: BX.message('USER_TYPE_RESOURCE_ADD'),
	        events: {
	          click: BX.delegate(this.addResourceBlock, this)
	        }
	      }));
	      if (this.resourceList.length > 0) {
	        this.DOM.selectButton = this.DOM.controlsWrap.appendChild(BX.create("span", {
	          props: {
	            className: "calendar-resource-content-block-add-link"
	          },
	          text: BX.message('USER_TYPE_RESOURCE_SELECT'),
	          events: {
	            click: BX.delegate(this.openResourcesPopup, this)
	          }
	        }));
	      }
	    } else {
	      BX.bind(this.DOM.blocksWrap, 'click', BX.delegate(this.handleBlockClick, this));
	    }
	  }
	  show() {
	    BX.addClass(this.DOM.outerWrap, 'shown');
	  }
	  hide() {
	    this.DOM.outerWrap.style.maxHeight = '';
	    BX.removeClass(this.DOM.outerWrap, 'shown');
	  }
	  isShown() {
	    return BX.hasClass(this.DOM.outerWrap, 'shown');
	  }
	  handleBlockClick(e) {
	    let target = e.target || e.srcElement;
	    if (target) {
	      let blockValue = target.getAttribute('data-bx-remove-block');
	      if (blockValue) {
	        // Remove from blocks
	        this.selectedBlocks.find(function (element, index) {
	          if (element.value === blockValue) {
	            BX.removeClass(element.wrap, 'shown');
	            setTimeout(BX.delegate(function () {
	              BX.remove(element.wrap);
	            }, this), 300);
	            this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
	          }
	        }, this);

	        // Remove from values
	        this.selectedValues.find(function (element, index) {
	          if (element.title === blockValue) {
	            this.selectedValues = BX.util.deleteFromArray(this.selectedValues, index);
	          }
	        }, this);
	        if (BX.type.isFunction(this.onChangeCallback)) {
	          setTimeout(BX.proxy(this.onChangeCallback, this), 100);
	        }
	        this.checkBlockWrapState();
	      }
	      if (!blockValue) {
	        this.openResourcesPopup();
	      }
	    }
	  }
	  openResourcesPopup() {
	    if (!this.resourceList.length) {
	      return this.addResourceBlock();
	    }
	    if (this.isResourcesPopupShown()) {
	      return;
	    }
	    let menuItems = [];
	    this.resourceList.forEach(function (resource) {
	      if (resource.deleted) {
	        return;
	      }
	      menuItems.push({
	        text: BX.util.htmlspecialchars(resource.title),
	        dataset: {
	          type: resource.type,
	          id: resource.id,
	          title: resource.title
	        },
	        onclick: BX.delegate(function (e, menuItem) {
	          let selectAllcheckbox,
	            target = e.target || e.srcElement,
	            checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox'),
	            foundResource = this.resourceList.find(function (resource) {
	              return parseInt(resource.id) === parseInt(menuItem.dataset.id) && resource.type === menuItem.dataset.type;
	            }, this);
	          if (foundResource) {
	            // Complete removing of the resource
	            if (target && BX.hasClass(target, "calendar-resourcebook-content-block-control-delete")) {
	              this.removeResourceBlock({
	                resource: foundResource,
	                trigerOnChange: true
	              });
	              this.selectedValues = this.getSelectedValues();
	              this.checkResourceInputs();
	              selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
	              this.selectAllChecked = false;
	              if (selectAllcheckbox) {
	                selectAllcheckbox.checked = false;
	              }
	              let menuItemNode = BX.findParent(target, {
	                className: 'menu-popup-item'
	              });
	              if (menuItemNode) {
	                BX.addClass(menuItemNode, 'menu-popup-item-resource-remove-loader');
	                menuItemNode.appendChild(calendar_resourcebooking.BookingUtil.getLoader(25));
	                let textNode = menuItemNode.querySelector('.menu-popup-item-text');
	                if (textNode) {
	                  textNode.innerHTML = BX.message('USER_TYPE_RESOURCE_DELETING');
	                }
	              }
	              foundResource.deleted = true;
	              setTimeout(BX.delegate(function () {
	                if (menuItemNode) {
	                  menuItemNode.style.maxHeight = '0';
	                }
	                if (!this.resourceList.find(function (resource) {
	                  return !resource.deleted;
	                })) {
	                  BX.PopupMenu.destroy(this.id);
	                  this.DOM.selectButton.style.opacity = 0;
	                  setTimeout(BX.delegate(function () {
	                    BX.remove(this.DOM.selectButton);
	                  }, this), 500);
	                }
	              }, this), 500);
	            } else if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox") || BX.hasClass(target, "menu-popup-item-inner"))) {
	              if (!BX.hasClass(target, "menu-popup-item-resource-checkbox")) {
	                checkbox.checked = !checkbox.checked;
	              }
	              if (checkbox.checked) {
	                this.addResourceBlock({
	                  resource: foundResource,
	                  value: foundResource.title,
	                  trigerOnChange: true
	                });
	                this.selectedValues = this.getSelectedValues();
	              } else {
	                this.removeResourceBlock({
	                  resource: foundResource,
	                  trigerOnChange: true
	                });
	                this.selectedValues = this.getSelectedValues();
	                this.checkResourceInputs();
	                selectAllcheckbox = this.popupContainer.querySelector('.menu-popup-item-all-resources-checkbox');
	                this.selectAllChecked = false;
	                if (selectAllcheckbox) {
	                  selectAllcheckbox.checked = false;
	                }
	              }
	            }
	          }
	        }, this)
	      });
	    }, this);
	    if (menuItems.length > 1) {
	      menuItems.push({
	        text: BX.message('USER_TYPE_RESOURCE_SELECT_ALL'),
	        onclick: BX.delegate(function (e, menuItem) {
	          let target = e.target || e.srcElement;
	          if (target && (BX.hasClass(target, "menu-popup-item") || BX.hasClass(target, "menu-popup-item-resource-checkbox"))) {
	            let checkbox = menuItem.layout.item.querySelector('.menu-popup-item-resource-checkbox');
	            if (BX.hasClass(target, "menu-popup-item")) {
	              checkbox.checked = !checkbox.checked;
	            }
	            let i,
	              checkboxes = this.popupContainer.querySelectorAll('input.menu-popup-item-resource-checkbox');
	            this.selectAllChecked = checkbox.checked;
	            for (i = 0; i < checkboxes.length; i++) {
	              checkboxes[i].checked = this.selectAllChecked;
	            }
	            this.resourceList.forEach(function (resource) {
	              if (resource.deleted) {
	                return;
	              }
	              if (this.selectAllChecked) {
	                this.addResourceBlock({
	                  resource: resource,
	                  value: resource.title,
	                  trigerOnChange: true
	                });
	              } else {
	                this.removeResourceBlock({
	                  resource: resource,
	                  trigerOnChange: true
	                });
	              }
	            }, this);
	            this.selectedValues = this.getSelectedValues();
	            this.checkResourceInputs();
	          }
	        }, this)
	      });
	    }
	    this.popup = BX.PopupMenu.create(this.id, this.DOM.selectButton || this.DOM.blocksWrap, menuItems, {
	      className: 'popup-window-resource-select',
	      closeByEsc: true,
	      autoHide: false,
	      offsetTop: 0,
	      offsetLeft: 0
	    });
	    this.popup.show(true);
	    this.popupContainer = this.popup.popupWindow.popupContainer;
	    if (!this.editMode) {
	      this.popupContainer.style.width = parseInt(this.DOM.blocksWrap.offsetWidth) + 'px';
	    }
	    BX.addCustomEvent(this.popup.popupWindow, 'onPopupClose', BX.proxy(function () {
	      BX.PopupMenu.destroy(this.id);
	    }, this));
	    this.popup.menuItems.forEach(function (menuItem) {
	      let checked;
	      if (menuItem.dataset && menuItem.dataset.type) {
	        checked = this.selectedValues.find(function (item) {
	          return parseInt(item.id) === parseInt(menuItem.dataset.id) && item.type === menuItem.dataset.type;
	        });
	        menuItem.layout.item.className = 'menu-popup-item';
	        menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox" type="checkbox"' + (checked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.util.htmlspecialchars(menuItem.dataset.title) + '</label>' + '</div>' + (this.editMode ? '<div class="calendar-resourcebook-content-block-control-delete"></div>' : '') + '</div>';
	      } else {
	        this.selectAllChecked = !this.resourceList.find(function (resource) {
	          return !this.selectedValues.find(function (item) {
	            return parseInt(item.id) === parseInt(resource.id) && item.type === resource.type;
	          });
	        }, this);
	        menuItem.layout.item.className = 'menu-popup-item menu-popup-item-resource-all';
	        menuItem.layout.item.innerHTML = '<div class="menu-popup-item-inner">' + '<div class="menu-popup-item-resource">' + '<input class="menu-popup-item-resource-checkbox menu-popup-item-all-resources-checkbox" type="checkbox"' + (this.selectAllChecked ? 'checked="checked"' : '') + ' id="' + menuItem.id + '">' + '<label class="menu-popup-item-text" for="' + menuItem.id + '">' + BX.message('USER_TYPE_RESOURCE_SELECT_ALL') + '</label>' + '</div>' + '</div>';
	      }
	    }, this);
	    setTimeout(BX.delegate(function () {
	      BX.bind(document, 'click', BX.proxy(this.handleClick, this));
	    }, this), 50);
	  }
	  addResourceBlock(params) {
	    if (!BX.type.isPlainObject(params)) {
	      params = {};
	    }
	    if (params.resource && this.checkLimit && !this.checkLimit() && window.B24 || !params.resource && this.checkLimitForNew && !this.checkLimitForNew() && window.B24) {
	      return calendar_resourcebooking.BookingUtil.showLimitationPopup();
	    }
	    let _this = this,
	      blockEntry;
	    if (this.editMode) {
	      if (params.resource && this.selectedValues.find(function (val) {
	        return val.id && parseInt(val.id) === parseInt(params.resource.id) && val.type === params.resource.type;
	      })) {
	        return;
	      }
	      if (!params.value) {
	        params.value = '';
	      }
	      blockEntry = {
	        value: params.value,
	        wrap: this.DOM.listWrap.appendChild(BX.create("div", {
	          props: {
	            className: "calendar-resourcebook-content-block-detail calendar-resourcebook-outer-resource-wrap"
	          }
	        })).appendChild(BX.create("div", {
	          props: {
	            className: "calendar-resourcebook-content-block-detail-resource"
	          }
	        })).appendChild(BX.create("div", {
	          props: {
	            className: "calendar-resourcebook-content-block-detail-resource-inner calendar-resourcebook-content-block-detail-resource-inner-wide"
	          }
	        }))
	      };
	      blockEntry.input = blockEntry.wrap.appendChild(BX.create("input", {
	        props: {
	          className: "calendar-resourcebook-content-input",
	          value: params.value,
	          type: 'text',
	          placeholder: BX.message('USER_TYPE_RESOURCE_NAME')
	        },
	        dataset: {
	          resourceType: params.resource ? params.resource.type : '',
	          resourceId: params.resource ? params.resource.id : ''
	        }
	      }));
	      blockEntry.delButton = blockEntry.wrap.appendChild(BX.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-control-delete"
	        },
	        events: {
	          click() {
	            BX.remove(BX.findParent(this, {
	              className: 'calendar-resourcebook-outer-resource-wrap'
	            }));
	            _this.selectedValues = _this.getSelectedValues();
	            _this.checkResourceInputs();
	          }
	        }
	      }));
	      if (params.focusInput !== false) {
	        BX.focus(blockEntry.input);
	      }
	    } else {
	      if (params.value && this.selectedBlocks.find(function (val) {
	        return val.value && val.value === params.value;
	      })) {
	        return;
	      }
	      blockEntry = {
	        value: params.value,
	        resource: params.resource || false,
	        wrap: this.DOM.blocksWrap.appendChild(BX.create("div", {
	          props: {
	            className: "calendar-resourcebook-content-block-control-inner" + (params.animation ? '' : ' shown') + (params.transparent ? ' transparent' : '')
	          },
	          children: [BX.create("div", {
	            props: {
	              className: "calendar-resourcebook-content-block-control-text"
	            },
	            text: params.value || ''
	          }), BX.create("div", {
	            attrs: {
	              'data-bx-remove-block': params.value
	            },
	            props: {
	              className: "calendar-resourcebook-content-block-control-delete"
	            }
	          })]
	        }))
	      };
	      this.selectedBlocks.push(blockEntry);

	      // Show it with animation
	      if (params.animation) {
	        setTimeout(BX.delegate(function () {
	          BX.addClass(blockEntry.wrap, 'shown');
	        }, this), 1);
	      }
	      if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback)) {
	        setTimeout(BX.proxy(this.onChangeCallback, this), 100);
	      }
	      this.checkBlockWrapState();
	    }

	    // Adjust outer wrap max height
	    if (this.DOM.listWrap && this.DOM.outerWrap) {
	      if (BX.hasClass(this.DOM.outerWrap, 'shown')) {
	        this.DOM.outerWrap.style.maxHeight = Math.max(10000, this.DOM.listWrap.childNodes.length * 45 + 100) + 'px';
	      } else {
	        this.DOM.outerWrap.style.maxHeight = '';
	      }
	    }
	    return blockEntry;
	  }
	  removeResourceBlock(params) {
	    if (this.editMode) {
	      let resourceType,
	        resourceId,
	        i,
	        inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');
	      for (i = 0; i < inputs.length; i++) {
	        resourceType = inputs[i].getAttribute('data-resource-type');
	        resourceId = inputs[i].getAttribute('data-resource-id');
	        if (resourceType === params.resource.type && parseInt(resourceId) === parseInt(params.resource.id)) {
	          BX.remove(BX.findParent(inputs[i], {
	            className: 'calendar-resourcebook-outer-resource-wrap'
	          }));
	        }
	      }
	    } else {
	      if (params.resource) {
	        this.selectedBlocks.find(function (element, index) {
	          if (element.value === params.resource.title) {
	            BX.removeClass(element.wrap, 'shown');
	            setTimeout(BX.delegate(function () {
	              BX.remove(element.wrap);
	            }, this), 300);
	            this.selectedBlocks = BX.util.deleteFromArray(this.selectedBlocks, index);
	          }
	        }, this);
	      }
	      this.checkBlockWrapState();
	      if (params.trigerOnChange !== false && this.onChangeCallback && BX.type.isFunction(this.onChangeCallback)) {
	        setTimeout(BX.proxy(this.onChangeCallback, this), 100);
	      }
	    }
	  }
	  checkResourceInputs() {
	    if (this.editMode) {
	      if (!this.selectedValues.length) {
	        this.addResourceBlock({
	          animation: true
	        });
	      }
	    }
	  }
	  checkBlockWrapState() {
	    if (!this.editMode) {
	      if (!this.selectedBlocks.length) {
	        if (!this.DOM.emptyPlaceholder) {
	          this.DOM.emptyPlaceholder = this.DOM.blocksWrap.appendChild(BX.create("DIV", {
	            props: {
	              className: "calendar-resourcebook-content-block-control-empty"
	            },
	            html: '<span class="calendar-resourcebook-content-block-control-text">' + BX.message('USER_TYPE_RESOURCE_LIST_PLACEHOLDER') + '</span>'
	          }));
	        } else {
	          this.DOM.emptyPlaceholder.className = "calendar-resourcebook-content-block-control-empty";
	          this.DOM.blocksWrap.appendChild(this.DOM.emptyPlaceholder);
	        }
	        setTimeout(BX.delegate(function () {
	          if (BX.isNodeInDom(this.DOM.emptyPlaceholder)) {
	            BX.addClass(this.DOM.emptyPlaceholder, 'show');
	          }
	        }, this), 50);
	      } else if (this.DOM.emptyPlaceholder) {
	        BX.remove(this.DOM.emptyPlaceholder);
	      }
	    }
	  }
	  handleClick(e) {
	    let target = e.target || e.srcElement;
	    if (this.isResourcesPopupShown() && !BX.isParentForNode(this.popupContainer, target)) {
	      this.closeResourcesPopup({
	        animation: true
	      });
	    }
	  }
	  isResourcesPopupShown() {
	    return this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown && this.popup.popupWindow.isShown() && this.popup.popupWindow.popupContainer && BX.isNodeInDom(this.popup.popupWindow.popupContainer);
	  }
	  closeResourcesPopup(params) {
	    if (this.popup) {
	      this.popup.close();
	      this.popupContainer.style.maxHeight = '';
	      BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
	    }
	  }
	  getValues() {
	    return this.resourceList;
	  }
	  addToSelectedValues(value) {
	    if (!this.selectedValues.find(function (val) {
	      return parseInt(val.id) === parseInt(value.id) && val.type === value.type;
	    })) {
	      this.selectedValues.push(value);
	    }
	  }
	  getSelectedValues() {
	    this.selectedValues = [];
	    if (this.editMode) {
	      let resourceType,
	        resourceId,
	        i,
	        inputs = this.DOM.listWrap.querySelectorAll('.calendar-resourcebook-content-input');
	      for (i = 0; i < inputs.length; i++) {
	        resourceType = inputs[i].getAttribute('data-resource-type');
	        resourceId = inputs[i].getAttribute('data-resource-id');
	        if (resourceType && resourceId) {
	          this.selectedValues.push({
	            type: resourceType,
	            id: resourceId,
	            title: inputs[i].value
	          });
	        } else {
	          this.selectedValues.push({
	            type: 'resource',
	            title: inputs[i].value
	          });
	        }
	      }
	    } else {
	      this.selectedBlocks.forEach(function (element) {
	        this.selectedValues.push({
	          type: element.resource.type,
	          id: element.resource.id
	        });
	      }, this);
	    }
	    return this.selectedValues;
	  }
	  getDeletedValues() {
	    return this.resourceList.filter(function (resource) {
	      return resource.deleted;
	    });
	  }
	  setValues(values, trigerOnChange) {
	    this.selectedBlocks.forEach(function (element) {
	      BX.remove(element.wrap);
	    });
	    this.selectedBlocks = [];
	    trigerOnChange = trigerOnChange !== false;
	    if (BX.type.isArray(values)) {
	      values.forEach(function (value) {
	        let foundResource = this.resourceList.find(function (resource) {
	          return parseInt(resource.id) === parseInt(value.id) && resource.type === value.type;
	        }, this);
	        if (foundResource) {
	          this.addResourceBlock({
	            resource: foundResource,
	            value: foundResource.title,
	            trigerOnChange: trigerOnChange
	          });
	          this.addToSelectedValues(foundResource);
	        }
	      }, this);
	    }
	    if (this.editMode) {
	      this.selectedValues = this.getSelectedValues();
	      this.checkResourceInputs();
	    } else {
	      if (this.DOM.arrowNode) {
	        this.DOM.blocksWrap.appendChild(this.DOM.arrowNode);
	      }
	    }
	    this.checkBlockWrapState();
	  }
	}

	class PlannerPopup {
	  constructor(params) {}
	  show(params) {
	    if (!params) {
	      params = {};
	    }
	    this.params = params;
	    this.bindNode = params.bindNode;
	    this.plannerId = this.params.plannerId;
	    this.config = this.params.plannerConfig;
	    if (this.isShown() || !this.bindNode) {
	      return;
	    }
	    if (this.lastPlannerIdShown && this.lastPlannerIdShown !== this.plannerId) {
	      this.close({
	        animation: false
	      });
	    }
	    this.currentEntries = [];
	    this.plannerWrap = calendar_resourcebooking.Dom.create('DIV', {
	      attrs: {
	        id: this.plannerId,
	        className: 'calendar-planner-wrapper'
	      }
	    });
	    this.popup = new main_popup.Popup(this.plannerId + "_popup", this.bindNode, {
	      autoHide: false,
	      closeByEsc: true,
	      offsetTop: -parseInt(this.bindNode.offsetHeight) - 20,
	      offsetLeft: this.bindNode.offsetWidth + 38,
	      lightShadow: true,
	      content: this.plannerWrap
	    });
	    this.popup.setAngle({
	      offset: 100,
	      position: 'left'
	    });
	    this.popup.show();
	    this.lastPlannerIdShown = this.plannerId;
	    let bindPos = BX.pos(this.bindNode),
	      winSize = BX.GetWindowSize();
	    this.plannerWidth = winSize.innerWidth - bindPos.right - 160;
	    this.config.width = this.plannerWidth;
	    if (this.popup && this.popup.popupContainer) {
	      calendar_resourcebooking.Dom.addClass(this.popup.popupContainer, 'calendar-resbook-planner-popup');
	      calendar_resourcebooking.Dom.addClass(this.popup.popupContainer, 'show');
	      this.popup.popupContainer.style.width = this.plannerWidth + 40 + 'px';
	      calendar_resourcebooking.Event.bind(document, 'click', this.handleClick.bind(this));
	    }
	    this.showPlanner();
	    BX.addCustomEvent(this.popup, 'onPopupClose', this.close.bind(this));
	  }
	  update(params, refreshParams) {
	    if (!this.isShown()) {
	      return;
	    }
	    let codes = [],
	      i,
	      k,
	      code,
	      codeIndex = {},
	      plannerConfig = BX.clone(this.config, true),
	      fromTimestamp,
	      toTimestamp,
	      dateFrom,
	      dateTo;
	    if (calendar_resourcebooking.Type.isPlainObject(this.lastUpdateParams) && calendar_resourcebooking.Type.isPlainObject(params) && refreshParams !== true) {
	      for (k in params) {
	        if (params.hasOwnProperty(k)) {
	          this.lastUpdateParams[k] = params[k];
	        }
	      }
	      params = this.lastUpdateParams;
	    }

	    // Save selector information
	    if (calendar_resourcebooking.Type.isPlainObject(params)) {
	      this.lastUpdateParams = params;
	    }
	    params.focusSelector = params.focusSelector !== false;
	    if (params.from && params.to) {
	      dateFrom = calendar_resourcebooking.BookingUtil.parseDate(params.from);
	      dateTo = calendar_resourcebooking.BookingUtil.parseDate(params.to);
	      fromTimestamp = dateFrom.getTime();
	      toTimestamp = dateTo.getTime();
	    } else {
	      if (params.selector.fullDay) {
	        fromTimestamp = params.selector.from.getTime() - calendar_resourcebooking.BookingUtil.getDayLength() * 12;
	        toTimestamp = params.selector.from.getTime() + calendar_resourcebooking.BookingUtil.getDayLength() * 14;
	      } else {
	        fromTimestamp = params.selector.from.getTime() - calendar_resourcebooking.BookingUtil.getDayLength() * 3;
	        toTimestamp = params.selector.from.getTime() + calendar_resourcebooking.BookingUtil.getDayLength() * 5;
	      }
	      dateFrom = new Date(fromTimestamp);
	      dateTo = new Date(toTimestamp);
	      plannerConfig.scaleDateFrom = dateFrom;
	      plannerConfig.scaleDateTo = dateTo;
	    }
	    if (calendar_resourcebooking.Type.isArray(params.userList)) {
	      for (i = 0; i < params.userList.length; i++) {
	        code = 'U' + params.userList[i].id;
	        if (!codeIndex[code]) {
	          codes.push(code);
	          codeIndex[code] = true;
	        }
	      }
	    }
	    if (calendar_resourcebooking.Type.isArray(params.selectedUsers)) {
	      for (i = 0; i < params.selectedUsers.length; i++) {
	        code = 'U' + params.selectedUsers[i];
	        if (!codeIndex[code]) {
	          codes.push(code);
	          codeIndex[code] = true;
	        }
	      }
	    }
	    let requestData = {
	      codes: codes,
	      resources: params.resourceList,
	      from: calendar_resourcebooking.BookingUtil.formatDate(null, fromTimestamp / 1000),
	      to: calendar_resourcebooking.BookingUtil.formatDate(null, toTimestamp / 1000),
	      currentEventList: this.params.currentEventList || []
	    };
	    if (this.checkUpdateParams(requestData) && this.isShown()) {
	      this.showPlannerLoader();
	      BX.ajax.runAction('calendar.api.resourcebookingajax.getplannerdata', {
	        data: requestData
	      }).then(function (response) {
	        this.hidePlannerLoader();
	        if (this.lastRequestData) {
	          this.lastRequestData.response = response;
	        }
	        this.currentEntries = response.data.entries;
	        this.currentAccessibility = response.data.accessibility;
	        this.currentLoadedDataFrom = dateFrom;
	        this.currentLoadedDataTo = dateTo;
	        if (calendar_resourcebooking.Type.isArray(response.data.entries)) {
	          response.data.entries.forEach(function (entry) {
	            entry.selected = entry.type === 'user' && params.selectedUsers.find(function (userId) {
	              return parseInt(entry.id) === parseInt(userId);
	            }) || entry.type === 'resource' && params.selectedResources.find(function (item) {
	              return entry.type === item.type && parseInt(entry.id) === parseInt(item.id);
	            });
	          });
	        }
	        if (this.isShown()) {
	          BX.onCustomEvent('OnCalendarPlannerDoUpdate', [{
	            plannerId: this.plannerId,
	            config: plannerConfig,
	            focusSelector: params.focusSelector,
	            selector: {
	              from: params.selector.from,
	              to: params.selector.to,
	              fullDay: params.selector.fullDay,
	              animation: params.focusSelector,
	              updateScaleLimits: params.focusSelector
	            },
	            data: {
	              entries: response.data.entries,
	              accessibility: response.data.accessibility
	            },
	            loadedDataFrom: dateFrom,
	            loadedDataTo: dateTo,
	            show: false
	          }]);
	        }
	      }.bind(this));
	    } else if (calendar_resourcebooking.Type.isPlainObject(this.lastRequestData.response)) {
	      let response = this.lastRequestData.response;
	      this.currentEntries = response.data.entries;
	      this.currentAccessibility = response.data.accessibility;
	      this.currentLoadedDataFrom = dateFrom;
	      this.currentLoadedDataTo = dateTo;
	      if (calendar_resourcebooking.Type.isArray(response.data.entries)) {
	        response.data.entries.forEach(function (entry) {
	          entry.selected = entry.type === 'user' && params.selectedUsers.find(function (userId) {
	            return parseInt(entry.id) === parseInt(userId);
	          }) || entry.type === 'resource' && params.selectedResources.find(function (item) {
	            return entry.type === item.type && parseInt(entry.id) === parseInt(item.id);
	          });
	        });
	      }
	      if (this.isShown()) {
	        BX.onCustomEvent('OnCalendarPlannerDoUpdate', [{
	          plannerId: this.plannerId,
	          config: plannerConfig,
	          focusSelector: params.focusSelector,
	          selector: {
	            from: params.selector.from,
	            to: params.selector.to,
	            fullDay: params.selector.fullDay,
	            animation: params.focusSelector,
	            updateScaleLimits: params.focusSelector
	          },
	          data: {
	            entries: response.data.entries,
	            accessibility: response.data.accessibility
	          },
	          loadedDataFrom: dateFrom,
	          loadedDataTo: dateTo,
	          show: false
	        }]);
	      }
	    }
	  }
	  checkUpdateParams(requestData) {
	    let requestPlannerUpdate = false;
	    if (!this.lastRequestData || this.lastRequestPlannerId !== this.plannerId) {
	      requestPlannerUpdate = true;
	    }

	    // 1. Compare dates
	    if (!requestPlannerUpdate && requestData.from !== this.lastRequestData.from) {
	      requestPlannerUpdate = true;
	    }
	    // 2. Compare users
	    if (!requestPlannerUpdate && calendar_resourcebooking.Type.isArray(requestData.codes) && calendar_resourcebooking.Type.isArray(this.lastRequestData.codes) && BX.util.array_diff(requestData.codes, this.lastRequestData.codes).length > 0) {
	      requestPlannerUpdate = true;
	    }

	    // 3. Compare resources
	    if (!requestPlannerUpdate && calendar_resourcebooking.Type.isArray(requestData.resources) && calendar_resourcebooking.Type.isArray(this.lastRequestData.resources)) {
	      if (requestData.resources.length !== this.lastRequestData.resources.length) {
	        requestPlannerUpdate = true;
	      } else {
	        let resIndex = {};
	        requestData.resources.forEach(function (res) {
	          resIndex[res.type + '_' + res.id] = true;
	        });
	        this.lastRequestData.resources.forEach(function (res) {
	          if (!resIndex[res.type + '_' + res.id]) {
	            requestPlannerUpdate = true;
	          }
	        });
	      }
	    }

	    // Save request data for future comparing
	    if (requestPlannerUpdate) {
	      this.lastRequestData = requestData;
	      this.lastRequestPlannerId = this.plannerId;
	    }
	    return requestPlannerUpdate;
	  }
	  showPlanner() {
	    this.planner = new CalendarPlanner(this.params.plannerConfig, {
	      config: this.config,
	      data: {
	        accessibility: this.currentAccessibility || {},
	        entries: this.currentEntries
	      },
	      selector: {
	        from: this.params.selector.from,
	        to: this.params.selector.to,
	        fullDay: this.params.selector.fullDay,
	        updateScaleLimits: true,
	        updateScaleType: false,
	        focus: true,
	        RRULE: false,
	        animation: false
	      },
	      loadedDataFrom: this.currentLoadedDataFrom,
	      loadedDataTo: this.currentLoadedDataTo,
	      focusSelector: true,
	      plannerId: this.plannerId,
	      show: true
	    });

	    // planner events
	    if (calendar_resourcebooking.Type.isFunction(this.params.selectorOnChangeCallback)) {
	      BX.addCustomEvent('OnCalendarPlannerSelectorChanged', this.params.selectorOnChangeCallback);
	    }
	    if (calendar_resourcebooking.Type.isFunction(this.params.selectEntriesOnChangeCallback)) {
	      BX.addCustomEvent('OnCalendarPlannerSelectedEntriesOnChange', this.params.selectEntriesOnChangeCallback);
	    }
	    if (calendar_resourcebooking.Type.isFunction(this.params.checkSelectorStatusCallback)) {
	      BX.addCustomEvent('OnCalendarPlannerSelectorStatusOnChange', this.params.checkSelectorStatusCallback);
	    }
	    BX.addCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(function (params) {
	      this.update({
	        from: params.from,
	        to: params.to,
	        focusSelector: params.focusSelector === true
	      });
	    }, this));
	  }
	  showPlannerLoader() {
	    if (this.planner && this.planner.outerWrap) {
	      if (this.loader) {
	        calendar_resourcebooking.Dom.remove(this.loader);
	      }
	      this.loader = this.planner.outerWrap.appendChild(calendar_resourcebooking.BookingUtil.getLoader(150));
	    }
	  }
	  hidePlannerLoader() {
	    if (this.loader) {
	      calendar_resourcebooking.Dom.remove(this.loader);
	      this.loader = false;
	    }
	  }
	  close(params) {
	    if (this.popup) {
	      if (params && params.animation) {
	        calendar_resourcebooking.Dom.removeClass(this.popup.popupContainer, 'show');
	        setTimeout(BX.delegate(function () {
	          params.animation = false;
	          this.close(params);
	        }, this), 300);
	      } else {
	        BX.unbind(document, 'click', BX.proxy(this.handleClick, this));
	        BX.removeCustomEvent(this.popup, 'onPopupClose', BX.proxy(this.close, this));
	        this.popup.destroy();
	        this.planner = null;
	        this.popup = null;
	      }
	    }
	  }
	  isShown() {
	    return this.lastPlannerIdShown === this.plannerId && this.popup && this.popup.isShown();
	  }
	  getPlannerId() {
	    if (typeof this.plannerId === 'undefined') {
	      this.plannerId = 'calendar-planner-' + Math.round(Math.random() * 100000);
	    }
	    return this.plannerId;
	  }
	  handleClick(e) {
	    let target = e.target || e.srcElement;
	    if (this.isShown() && !BX.isParentForNode(this.bindNode, target) && !BX.isParentForNode(BX('BXSocNetLogDestination'), target) && !BX.isParentForNode(this.popup.popupContainer, target) && !calendar_resourcebooking.Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete')) {
	      if (!document.querySelector('div.popup-window-resource-select')) {
	        this.close({
	          animation: true
	        });
	      }
	    }
	  }
	}

	class EditFieldController {
	  constructor(params) {
	    this.params = params;
	    this.plannerPopup = null;
	    this.DOM = {
	      outerWrap: BX(params.controlId),
	      valueInputs: []
	    };
	    this.isNew = !this.params.value || !this.params.value.DATE_FROM;
	    if (this.params.socnetDestination) {
	      calendar_resourcebookinguserfield.ResourcebookingUserfield.setSocnetDestination(this.params.socnetDestination);
	    }
	  }
	  init() {
	    this.buildUserfieldWrap();
	    this.createEventHandlers();
	    this.setControlValues();
	  }
	  buildUserfieldWrap() {
	    this.buildDateControl();
	    this.buildTimeControl();
	    this.buildServiceControl();
	    this.buildDurationControl();
	    this.buildUserSelectorControl();
	    this.buildResourceSelectorControl();
	  }
	  createEventHandlers() {
	    calendar_resourcebooking.Event.bind(this.DOM.outerWrap, 'click', this.showPlannerPopup.bind(this));
	    calendar_resourcebooking.Event.bind(this.DOM.fromInput, 'focus', this.showPlannerPopup.bind(this));
	    calendar_resourcebooking.Event.bind(this.DOM.durationInput, 'focus', this.showPlannerPopup.bind(this));
	    setTimeout(function () {
	      BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldSetValidator', [this.params.controlId, function (result) {
	        if (!this.params.allowOverbooking && this.isOverbooked()) {
	          if (result && result.addError && BX.Crm && BX.Crm.EntityValidationError) {
	            result.addError(BX.Crm.EntityValidationError.create({
	              field: this
	            }));
	          }
	        }
	        return new Promise(resolve => {
	          resolve();
	        });
	      }.bind(this)]);
	    }.bind(this), 100);
	    setTimeout(this.onChangeValues.bind(this), 100);
	  }
	  setControlValues() {
	    this.allValuesValue = null;
	    let dateFrom,
	      duration,
	      defaultDuration = this.params.fullDay ? 1440 : 60,
	      // One day or one hour as default
	      dateTo;
	    if (this.isNew) {
	      let params = calendar_resourcebookinguserfield.ResourcebookingUserfield.getParamsFromHash(this.params.userfieldId);
	      if (params && params.length > 1) {
	        dateFrom = BX.parseDate(params[0]);
	        dateTo = BX.parseDate(params[1]);
	        if (dateFrom && dateTo) {
	          duration = Math.round(Math.max((dateTo.getTime() - dateFrom.getTime()) / 60000, 0));
	        }
	      }
	      if (!dateFrom) {
	        dateFrom = new Date();
	        let roundMin = 30,
	          r = (roundMin) * 60 * 1000,
	          timestamp = Math.ceil(dateFrom.getTime() / r) * r;
	        dateFrom = new Date(timestamp);
	      }
	    } else {
	      dateFrom = BX.parseDate(this.params.value.DATE_FROM);
	      dateTo = BX.parseDate(this.params.value.DATE_TO);
	      duration = Math.round(Math.max((dateTo.getTime() - dateFrom.getTime()) / 60000, 0));
	    }
	    if (!duration) {
	      duration = defaultDuration;
	    }
	    this.DOM.fromInput.value = calendar_resourcebooking.BookingUtil.formatDate(calendar_resourcebooking.BookingUtil.getDateFormat(), dateFrom);
	    if (this.DOM.timeFromInput) {
	      this.DOM.timeFromInput.value = calendar_resourcebooking.BookingUtil.formatDate(calendar_resourcebooking.BookingUtil.getTimeFormatShort(), dateFrom);
	    }
	    if (this.durationList) {
	      this.durationList.setValue(duration);
	    }
	    if (this.serviceList) {
	      this.serviceList.setValue(this.params.value.SERVICE_NAME || '');
	    }
	    let selectedUsers = [];
	    let selectedResources = [];
	    if (this.params.value && calendar_resourcebooking.Type.isArray(this.params.value.ENTRIES)) {
	      this.params.value.ENTRIES.forEach(function (entry) {
	        if (entry.TYPE === 'user') {
	          selectedUsers.push(parseInt(entry.RESOURCE_ID));
	        } else {
	          selectedResources.push({
	            id: parseInt(entry.RESOURCE_ID),
	            type: entry.TYPE
	          });
	        }
	      });
	    }
	    if (this.resourceSelector) {
	      this.resourceSelector.setValues(selectedResources, false);
	    }
	    if (this.userSelector) {
	      this.userSelector.setValues(selectedUsers, false);
	    }
	  }
	  buildDateControl() {
	    this.DOM.dateTimeWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"
	      }
	    }));
	    this.DOM.dateWrap = this.DOM.dateTimeWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail"
	      },
	      html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DATE_LABEL') + '</span></div>'
	    }));
	    this.DOM.fromInput = this.DOM.dateWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        value: '',
	        placeholder: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DATE_LABEL'),
	        type: 'text'
	      },
	      events: {
	        click: EditFieldController.showCalendarPicker,
	        change: this.triggerUpdatePlanner.bind(this)
	      },
	      props: {
	        className: 'calendar-resbook-date-input calendar-resbook-field-datetime'
	      }
	    }));
	    this.DOM.emptyInput = this.DOM.dateWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        value: '',
	        type: 'text'
	      },
	      props: {
	        className: 'calendar-resbook-empty-input'
	      }
	    }));
	  }
	  buildTimeControl() {
	    if (!this.params.fullDay) {
	      this.DOM.timeWrap = this.DOM.dateTimeWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"
	        }
	      })).appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail"
	        },
	        html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_TIME_LABEL') + '</span></div>'
	      }));
	      this.DOM.timeFromInput = this.DOM.timeWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	        attrs: {
	          value: '',
	          placeholder: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_TIME_LABEL'),
	          type: 'text'
	        },
	        style: {
	          width: '100px'
	        },
	        props: {
	          className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'
	        }
	      }));
	      this.fromTime = new calendar_resourcebooking.SelectInput({
	        input: this.DOM.timeFromInput,
	        values: calendar_resourcebooking.BookingUtil.getSimpleTimeList(),
	        onChangeCallback: this.triggerUpdatePlanner.bind(this),
	        onAfterMenuOpen: (ind, popupMenu) => {
	          if (!ind && popupMenu) {
	            const formatDatetime = BX.isAmPmMode() ? calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME").replace(':SS', '') : calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME");
	            const dateFrom = calendar_resourcebooking.BookingUtil.parseDate(this.DOM.fromInput.value + ' ' + this.DOM.timeFromInput.value, false, false, formatDatetime);
	            let i, menuItem;
	            const nearestTimeValue = calendar_resourcebooking.BookingUtil.adaptTimeValue({
	              h: dateFrom.getHours(),
	              m: dateFrom.getMinutes()
	            });
	            if (nearestTimeValue && nearestTimeValue.label) {
	              for (i = 0; i < popupMenu.menuItems.length; i++) {
	                menuItem = popupMenu.menuItems[i];
	                if (menuItem && nearestTimeValue.label === menuItem.text && menuItem.layout) {
	                  popupMenu.layout.menuContainer.scrollTop = menuItem.layout.item.offsetTop - 2;
	                }
	              }
	            }
	          }
	        }
	      });
	    }
	  }
	  buildServiceControl() {
	    if (this.params.useServices && calendar_resourcebooking.Type.isArray(this.params.serviceList) && this.params.serviceList.length > 0) {
	      if (this.params.fullDay) {
	        this.DOM.durationWrap = this.DOM.dateTimeWrap;
	      } else {
	        this.DOM.durationWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	          props: {
	            className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-content-block-detail-wrap-flex"
	          }
	        }));
	      }
	      this.DOM.servicesWrap = this.DOM.durationWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"
	        }
	      })).appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail"
	        },
	        html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span></div>'
	      }));
	      this.DOM.serviceInput = this.DOM.servicesWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	        attrs: {
	          value: '',
	          //value: this.params.value.SERVICE_NAME || '',
	          placeholder: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL'),
	          type: 'text'
	        },
	        style: {
	          width: '200px'
	        },
	        props: {
	          className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'
	        }
	      }));
	      let serviceListValues = [];
	      this.params.serviceList.forEach(function (service) {
	        if (service.name !== '') {
	          serviceListValues.push({
	            value: service.duration,
	            label: service.name
	          });
	        }
	      });
	      if (this.isNew && serviceListValues.length >= 1) {
	        this.DOM.serviceInput.value = serviceListValues[0].label;
	        //duration = parseInt(serviceListValues[0].value);
	      }

	      this.serviceList = new calendar_resourcebooking.SelectInput({
	        input: this.DOM.serviceInput,
	        values: serviceListValues,
	        onChangeCallback: function (state) {
	          if (calendar_resourcebooking.Type.isPlainObject(state) && state.realValue) {
	            this.durationList.setValue(parseInt(state.realValue));
	            this.duration = calendar_resourcebooking.BookingUtil.parseDuration(this.DOM.durationInput.value);
	            this.triggerUpdatePlanner();
	          }
	        }.bind(this)
	      });
	    }
	  }
	  buildDurationControl() {
	    if (!this.DOM.durationWrap) {
	      this.DOM.durationWrap = this.DOM.dateTimeWrap;
	    }

	    // region Duration
	    this.DOM.durationControlWrap = this.DOM.durationWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-inner calendar-resourcebook-content-block-detail-wrap-down"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail"
	      },
	      html: '<div class="calendar-resourcebook-content-block-title"><span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span></div>'
	    }));
	    this.DOM.durationInput = this.DOM.durationControlWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        //value: duration,
	        placeholder: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL'),
	        type: 'text'
	      },
	      style: {
	        width: '90px'
	      },
	      props: {
	        className: 'calendar-resbook-date-input calendar-resbook-field-datetime-menu'
	      }
	    }));

	    //this.duration = parseInt(duration);
	    this.durationList = new calendar_resourcebooking.SelectInput({
	      input: this.DOM.durationInput,
	      values: calendar_resourcebooking.BookingUtil.getDurationList(this.params.fullDay),
	      //value: duration,
	      onChangeCallback: function () {
	        this.duration = calendar_resourcebooking.BookingUtil.parseDuration(this.DOM.durationInput.value);
	        this.triggerUpdatePlanner();
	      }.bind(this)
	    });
	  }
	  buildUserSelectorControl() {
	    if (this.params.useUsers) {
	      this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create('DIV', {
	        props: {
	          className: 'calendar-resbook-users-selector-wrap'
	        }
	      }));
	      this.DOM.userSelectorWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create('DIV', {
	        props: {
	          className: 'calendar-resourcebook-content-block-control-field'
	        }
	      }));
	      let userSelectorTitle = calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME');
	      this.DOM.userSelectorWrap.appendChild(calendar_resourcebooking.Dom.create('DIV', {
	        props: {
	          className: 'calendar-resourcebook-content-block-title'
	        }
	      })).appendChild(calendar_resourcebooking.Dom.create('SPAN', {
	        props: {
	          className: 'calendar-resourcebook-content-block-title-text'
	        },
	        text: userSelectorTitle
	      }));
	      this.DOM.userListWrap = this.DOM.userSelectorWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-control custom-field-item"
	        }
	      }));
	      let itemsSelected = {};
	      if (this.params.value && calendar_resourcebooking.Type.isArray(this.params.value.ENTRIES)) {
	        this.params.value.ENTRIES.forEach(function (entry) {
	          if (entry.TYPE === 'user') {
	            const userKey = 'U' + parseInt(entry.RESOURCE_ID);
	            itemsSelected[userKey] = 'users';
	          }
	        });
	      }
	      this.userSelector = new UserSelectorFieldEditControl({
	        wrapNode: this.DOM.userListWrap,
	        socnetDestination: calendar_resourcebookinguserfield.ResourcebookingUserfield.getSocnetDestination(),
	        addMessage: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_SELECT_USER'),
	        checkLimitCallback: this.checkResourceCountLimit.bind(this),
	        itemsSelected: itemsSelected
	      });
	      BX.addCustomEvent('OnResourceBookDestinationAddNewItem', this.triggerUpdatePlanner.bind(this));
	      BX.addCustomEvent('OnResourceBookDestinationUnselect', this.triggerUpdatePlanner.bind(this));
	    }
	  }
	  buildResourceSelectorControl() {
	    if (this.params.useResources) {
	      this.DOM.resourcesWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	        }
	      }));
	      let resSelectorTitle = calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME');
	      this.DOM.resourcesWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-title"
	        }
	      })).appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-title-text"
	        },
	        text: resSelectorTitle
	      }));
	      this.DOM.resourcesListWrap = this.DOM.resourcesWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-control custom-field-item"
	        }
	      }));
	      this.resourceSelector = new ResourceSelectorFieldEditControl({
	        outerWrap: this.DOM.resourcesWrap,
	        blocksWrap: this.DOM.resourcesListWrap,
	        values: [],
	        resourceList: this.params.resourceList,
	        onChangeCallback: this.triggerUpdatePlanner.bind(this),
	        checkLimitCallback: this.checkResourceCountLimit.bind(this)
	      });
	    }
	  }
	  static showCalendarPicker(e) {
	    let target = e.target || e.srcElement;
	    BX.calendar({
	      node: target,
	      field: target,
	      bTime: false
	    });
	    BX.focus(target);
	  }
	  onChangeValues() {
	    this.duration = this.duration || calendar_resourcebooking.BookingUtil.parseDuration(this.DOM.durationInput.value);
	    const duration = this.duration * 60;
	    let allValuesValue = '',
	      formatDatetime = BX.isAmPmMode() ? calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME").replace(':SS', '') : calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME"),
	      dateFrom,
	      dateFromValue = '',
	      serviceName = this.DOM.serviceInput ? this.DOM.serviceInput.value : '',
	      entries = [];
	    dateFrom = this.params.fullDay ? calendar_resourcebooking.BookingUtil.parseDate(this.DOM.fromInput.value) : calendar_resourcebooking.BookingUtil.parseDate(this.DOM.fromInput.value + ' ' + this.DOM.timeFromInput.value, false, false, formatDatetime);
	    if (calendar_resourcebooking.Type.isDate(dateFrom)) {
	      if (this.params.useResources) {
	        entries = entries.concat(this.getSelectedResourceList());
	      }
	      if (this.params.useUsers) {
	        entries = entries.concat(this.getSelectedUserList());
	      }
	      dateFromValue = calendar_resourcebooking.BookingUtil.formatDate(calendar_resourcebooking.BookingUtil.getDateTimeFormat(), dateFrom.getTime() / 1000);
	    }

	    // Clear inputs
	    this.DOM.valueInputs.forEach(function (input) {
	      BX.remove(input);
	    });
	    this.DOM.valueInputs = [];
	    entries.forEach(function (entry) {
	      let value = entry.type + '|' + entry.id + '|' + dateFromValue + '|' + duration + '|' + serviceName;
	      allValuesValue += value + '#';
	      this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	        attrs: {
	          name: this.params.inputName,
	          value: value,
	          type: 'hidden'
	        }
	      })));
	    }, this);
	    if (!entries.length) {
	      this.DOM.valueInputs.push(this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	        attrs: {
	          name: this.params.inputName,
	          value: 'empty',
	          type: 'hidden'
	        }
	      })));
	    }
	    if (this.allValuesValue !== null && this.allValuesValue !== allValuesValue) {
	      BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [this.params.controlId]);
	      BX.fireEvent(this.DOM.emptyInput, 'change');
	    }
	    this.allValuesValue = allValuesValue;
	  }
	  showPlannerPopup() {
	    let currentEventList = [];
	    if (this.params.value && calendar_resourcebooking.Type.isArray(this.params.value.ENTRIES)) {
	      this.params.value.ENTRIES.forEach(function (entry) {
	        currentEventList.push(entry.EVENT_ID);
	      });
	    }
	    if (calendar_resourcebooking.Type.isNull(this.plannerPopup)) {
	      this.plannerPopup = new PlannerPopup();
	    }
	    this.plannerPopup.show({
	      plannerId: this.params.plannerId,
	      bindNode: this.DOM.outerWrap,
	      plannerConfig: this.getPlannerConfig(),
	      selector: this.getSelectorData(),
	      selectorOnChangeCallback: this.plannerSelectorOnChange.bind(this),
	      selectEntriesOnChangeCallback: this.plannerSelectedEntriesOnChange.bind(this),
	      checkSelectorStatusCallback: this.checkSelectorStatusCallback.bind(this),
	      currentEventList: currentEventList
	    });
	    this.triggerUpdatePlanner();
	  }
	  triggerUpdatePlanner() {
	    if (!calendar_resourcebooking.Type.isNull(this.plannerPopup) && this.plannerPopup.plannerId === this.params.plannerId && this.plannerPopup.isShown()) {
	      this.plannerPopup.update({
	        plannerId: this.params.plannerId,
	        plannerConfig: this.getPlannerConfig(),
	        selector: this.getSelectorData(),
	        resourceList: this.getResourceList(),
	        selectedResources: this.resourceSelector ? this.resourceSelector.getSelectedValues() : false,
	        userList: this.getUserList(),
	        selectedUsers: this.userSelector ? this.userSelector.getSelectedValues() : false
	      }, true);
	    }
	    this.onChangeValues();
	  }
	  getPlannerConfig() {
	    if (!this.params.plannerConfig) {
	      this.params.plannerConfig = {
	        id: this.params.plannerId,
	        selectEntriesMode: true,
	        scaleLimitOffsetLeft: 2,
	        scaleLimitOffsetRight: 2,
	        maxTimelineSize: 300,
	        minEntryRows: 300,
	        entriesListWidth: 120,
	        timelineCellWidth: 49,
	        minWidth: 300,
	        accuracy: 300,
	        workTime: [parseInt(this.params.workTime[0]), parseInt(this.params.workTime[1])]
	      };
	    }
	    this.params.plannerConfig.clickSelectorScaleAccuracy = Math.max(this.duration * 60 || 300, 3600);
	    return this.params.plannerConfig;
	  }
	  plannerSelectorOnChange(params) {
	    if (params.plannerId === this.params.plannerId && calendar_resourcebooking.Type.isDate(params.dateFrom) && calendar_resourcebooking.Type.isDate(params.dateTo)) {
	      let dateFrom = params.dateFrom,
	        dateTo = params.dateTo;
	      this.DOM.fromInput.value = calendar_resourcebooking.BookingUtil.formatDate(calendar_resourcebooking.BookingUtil.getDateFormat(), dateFrom);
	      if (this.DOM.timeFromInput) {
	        this.DOM.timeFromInput.value = calendar_resourcebooking.BookingUtil.formatDate(calendar_resourcebooking.BookingUtil.getTimeFormatShort(), dateFrom);
	      }

	      // Duration in minutes
	      this.duration = (dateTo.getTime() - dateFrom.getTime() + (this.params.fullDay ? calendar_resourcebooking.BookingUtil.getDayLength() : 0)) / 60000;
	      this.duration = Math.round(Math.max(this.duration, 0));
	      this.durationList.setValue(this.duration);
	      this.onChangeValues();
	    }
	  }
	  plannerSelectedEntriesOnChange(params) {
	    if (params.plannerId === this.params.plannerId && calendar_resourcebooking.Type.isArray(params.entries)) {
	      let selectedResources = [],
	        selectedUsers = [];
	      params.entries.forEach(function (entry) {
	        if (entry.selected) {
	          if (entry.type === 'user') {
	            selectedUsers.push(entry.id);
	          } else {
	            selectedResources.push({
	              id: entry.id,
	              type: entry.type
	            });
	          }
	        }
	      });
	      if (this.resourceSelector) {
	        this.resourceSelector.setValues(selectedResources, false);
	      }
	      if (this.userSelector) {
	        this.userSelector.setValues(selectedUsers, false);
	      }
	      this.onChangeValues();
	    }
	  }
	  checkSelectorStatusCallback(params) {
	    if (params.plannerId === this.params.plannerId && !this.params.allowOverbooking) {
	      let errorClass = 'calendar-resbook-error';
	      this.overbooked = params.status === 'busy';
	      if (this.overbooked) {
	        if (!this.DOM.errorNode) {
	          this.DOM.errorNode = this.DOM.dateTimeWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	            props: {
	              className: "calendar-resbook-content-error-text"
	            },
	            text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_BOOKED_ERROR')
	          }));
	        }
	        if (this.DOM.fromInput) {
	          BX.addClass(this.DOM.fromInput, errorClass);
	        }
	        if (this.DOM.timeFromInput) {
	          BX.addClass(this.DOM.timeFromInput, errorClass);
	        }
	        setTimeout(BX.delegate(function () {
	          BX.focus(this.DOM.fromInput);
	        }, this), 50);
	      } else {
	        if (this.DOM.errorNode) {
	          BX.remove(this.DOM.errorNode);
	          this.DOM.errorNode = null;
	        }
	        if (this.DOM.fromInput) {
	          BX.removeClass(this.DOM.fromInput, errorClass);
	        }
	        if (this.DOM.timeFromInput) {
	          BX.removeClass(this.DOM.timeFromInput, errorClass);
	        }
	      }
	    }
	  }
	  getSelectorData() {
	    let formatDatetime = BX.isAmPmMode() ? calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME").replace(':SS', '') : calendar_resourcebooking.Loc.getMessage("FORMAT_DATETIME"),
	      selector,
	      dateTo,
	      duration = this.duration,
	      dateFrom = calendar_resourcebooking.BookingUtil.parseDate(this.DOM.fromInput.value + (this.DOM.timeFromInput ? ' ' + this.DOM.timeFromInput.value : ''), false, false, formatDatetime);
	    if (!duration) {
	      duration = this.params.fullDay ? 1440 : 60;
	    }
	    if (!calendar_resourcebooking.Type.isDate(dateFrom)) {
	      dateFrom = new Date();
	    }
	    dateTo = new Date(dateFrom.getTime() + duration * 60000 - (this.params.fullDay ? calendar_resourcebooking.BookingUtil.getDayLength() : 0));
	    selector = {
	      from: dateFrom,
	      to: dateTo,
	      fullDay: this.params.fullDay,
	      updateScaleLimits: true
	    };
	    return selector;
	  }
	  getResourceList() {
	    let entries = [];
	    if (this.resourceSelector) {
	      this.resourceSelector.getValues().forEach(function (value) {
	        entries.push({
	          id: parseInt(value.id),
	          type: value.type,
	          name: value.title
	        });
	      });
	    }
	    return entries;
	  }
	  getSelectedResourceList() {
	    let entries = [];
	    if (this.resourceSelector) {
	      this.resourceSelector.getSelectedValues().forEach(function (value) {
	        entries.push({
	          id: parseInt(value.id),
	          type: value.type,
	          name: value.title
	        });
	      });
	    }
	    return entries;
	  }
	  getUserList() {
	    let entries = [],
	      index = {},
	      userId;
	    if (this.userSelector) {
	      if (calendar_resourcebooking.Type.isArray(this.params.userList)) {
	        this.params.userList.forEach(function (userId) {
	          if (!index[userId]) {
	            entries.push({
	              id: userId,
	              type: 'user'
	            });
	            index[userId] = true;
	          }
	        });
	      }
	      this.userSelector.getAttendeesCodesList().forEach(function (code) {
	        if (code.substr(0, 1) === 'U') {
	          userId = parseInt(code.substr(1));
	          if (!index[userId]) {
	            entries.push({
	              id: userId,
	              type: 'user'
	            });
	            index[userId] = true;
	          }
	        }
	      });
	    }
	    return entries;
	  }
	  getSelectedUserList() {
	    let entries = [];
	    if (this.userSelector) {
	      this.userSelector.getAttendeesCodesList().forEach(function (code) {
	        if (code.substr(0, 1) === 'U') {
	          entries.push({
	            id: parseInt(code.substr(1)),
	            type: 'user'
	          });
	        }
	      });
	    }
	    return entries;
	  }
	  checkResourceCountLimit() {
	    return this.params.resourceLimit <= 0 || this.getTotalResourceCount() <= this.params.resourceLimit;
	  }
	  getTotalResourceCount() {
	    let result = 0;
	    if (this.params.useResources && this.resourceSelector) {
	      result += this.resourceSelector.getValues().length;
	    }
	    if (this.params.useUsers) {
	      result += this.getSelectedUserList().length;
	    }
	    return result;
	  }
	  isOverbooked() {
	    return this.overbooked;
	  }
	}

	class ServiceSelector {
	  constructor(params) {
	    this.params = calendar_resourcebooking.Type.isPlainObject(params) ? params : {};
	    this.outerCont = this.params.outerCont;
	    this.fieldSettings = this.params.fieldSettings || {};
	    this.create();
	  }
	  create() {
	    this.serviceListOuterWrap = this.outerCont.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-wrap calendar-resourcebook-service-list-wrap"
	      }
	    }));
	    this.durationTitleId = 'duration-title-wrap-' + Math.round(Math.random() * 100000);
	    this.servicesTitleWrap = this.serviceListOuterWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-inner"
	      },
	      html: '<div class="calendar-resourcebook-content-block-detail-resource">' + '<div class="calendar-resourcebook-content-block-title">' + '<span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_LABEL') + '</span>' + '</div>' + '<div id="' + this.durationTitleId + '" class="calendar-resourcebook-content-block-title calendar-resourcebook-content-block-duration-title">' + '<span class="calendar-resourcebook-content-block-title-text">' + calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DURATION_LABEL') + '</span>' + '</div>' + '</div>'
	    }));
	    this.serviceListRowsWrap = this.serviceListOuterWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-inner"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail"
	      }
	    }));
	    BX.bind(this.serviceListRowsWrap, 'click', this.handlePopupClick.bind(this));
	    if (calendar_resourcebooking.Type.isArray(this.fieldSettings.SERVICE_LIST) && this.fieldSettings.SERVICE_LIST.length > 0) {
	      this.fieldSettings.SERVICE_LIST.forEach(function (service) {
	        this.addRow(service, false);
	      }, this);
	    } else {
	      this.addRow(false, false);
	    }
	    this.serviceListAddWrap = this.serviceListOuterWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resource-content-block-add-field"
	      }
	    }));
	    this.serviceAddButton = this.serviceListAddWrap.appendChild(calendar_resourcebooking.Dom.create("span", {
	      props: {
	        className: "calendar-resource-content-block-add-link calendar-resource-content-block-add-link-icon"
	      },
	      text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_ADD_SERVICE'),
	      events: {
	        click: this.addRow.bind(this)
	      }
	    }));
	    BX.bind(window, 'resize', this.checkDurationTitlePosition.bind(this));
	    this.checkDurationTitlePosition();
	    this.show(this.fieldSettings.USE_SERVICES === 'Y');
	  }
	  show(show) {
	    if (show) {
	      this.serviceListOuterWrap.style.display = '';
	      calendar_resourcebooking.Dom.addClass(this.serviceListOuterWrap, 'show');
	    } else {
	      this.serviceListOuterWrap.style.display = 'none';
	      calendar_resourcebooking.Dom.removeClass(this.serviceListOuterWrap, 'show');
	    }
	  }
	  addRow(row, animation) {
	    animation = animation !== false;
	    if (!calendar_resourcebooking.Type.isPlainObject(row)) {
	      row = {
	        name: '',
	        duration: this.getDefaultDuration()
	      };
	    }
	    let service = {
	      outerWrap: this.serviceListRowsWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-detail-resource calendar-resourcebook-service-row"
	        }
	      }))
	    };
	    if (animation) {
	      setTimeout(function () {
	        calendar_resourcebooking.Dom.addClass(service.outerWrap, 'show');
	      }, 1);
	    } else {
	      calendar_resourcebooking.Dom.addClass(service.outerWrap, 'show');
	    }
	    service.wrap = service.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-resource-inner"
	      }
	    }));
	    service.nameInput = service.wrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	      props: {
	        className: "calendar-resourcebook-content-input calendar-resourcebook-service-input",
	        placeholder: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_SERVICE_PLACEHOLDER'),
	        type: "text",
	        value: row.name
	      },
	      attrs: {}
	    }));
	    service.durationInput = service.wrap.appendChild(calendar_resourcebooking.Dom.create("input", {
	      props: {
	        className: "calendar-resbook-duration-input calendar-resbook-field-datetime-menu",
	        type: "text",
	        value: row.duration
	      },
	      attrs: {}
	    }));
	    service.durationList = new calendar_resourcebooking.SelectInput({
	      input: service.durationInput,
	      getValues: function () {
	        let fullday = false;
	        if (calendar_resourcebooking.Type.isFunction(this.params.getFullDayValue)) {
	          fullday = this.params.getFullDayValue();
	        }
	        return calendar_resourcebooking.BookingUtil.getDurationList(fullday);
	      }.bind(this),
	      value: row.duration
	    });
	    service.deleteWrap = service.wrap.appendChild(calendar_resourcebooking.Dom.create("DIV", {
	      props: {
	        className: "calendar-resourcebook-content-block-detail-delete"
	      },
	      html: '<span class="calendar-resourcebook-content-block-control-delete calendar-resourcebook-content-block-control-delete-detail"></span>'
	    }));

	    // Adjust outer wrap max height
	    this.serviceListOuterWrap.style.maxHeight = Math.max(500, this.serviceListRowsWrap.childNodes.length * 45 + 100) + 'px';
	  }
	  checkDurationTitlePosition(timeout) {
	    if (timeout !== false) {
	      if (this.checkDurationTitlePositionTimeout) {
	        clearTimeout(this.checkDurationTitlePositionTimeout);
	      }
	      this.checkDurationTitlePositionTimeout = setTimeout(function () {
	        this.checkDurationTitlePosition(false);
	      }.bind(this), 100);
	      return;
	    }
	    let durationInput = this.serviceListOuterWrap.querySelector('input.calendar-resbook-duration-input');
	    if (this.durationTitleId && BX(this.durationTitleId) && durationInput) {
	      BX(this.durationTitleId).style.left = durationInput.offsetLeft + 15 + 'px';
	    }
	  }
	  getDefaultDuration() {
	    let fullday = false;
	    if (calendar_resourcebooking.Type.isFunction(this.params.getFullDayValue)) {
	      fullday = this.params.getFullDayValue();
	    }
	    return fullday ? 1440 : 30;
	  }
	  clickHandler(e) {
	    let target = e.target || e.srcElement;
	    if (calendar_resourcebooking.Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete') || calendar_resourcebooking.Dom.hasClass(target, 'calendar-resourcebook-content-block-detail-delete'))
	      // Delete button
	      {
	        let resWrap = BX.findParent(target, {
	          className: 'calendar-resourcebook-service-row'
	        });
	        if (resWrap) {
	          calendar_resourcebooking.Dom.removeClass(resWrap, 'show');
	          setTimeout(function () {
	            calendar_resourcebooking.Dom.remove(resWrap);
	          }, 500);
	          this.checkRows();
	        }
	      }
	  }
	  getValues(e) {
	    let serviceList = [],
	      nameInput,
	      durationInput,
	      i,
	      rows = this.serviceListRowsWrap.querySelectorAll('.calendar-resourcebook-service-row');
	    for (i = 0; i < rows.length; i++) {
	      if (calendar_resourcebooking.Dom.hasClass(rows[i], 'show')) {
	        nameInput = rows[i].querySelector('input.calendar-resourcebook-service-input');
	        durationInput = rows[i].querySelector('input.calendar-resbook-duration-input');
	        if (nameInput && durationInput) {
	          serviceList.push({
	            name: nameInput.value,
	            duration: calendar_resourcebooking.BookingUtil.parseDuration(durationInput.value)
	          });
	        }
	      }
	    }
	    return serviceList;
	  }
	  checkRows() {
	    let serviceList = this.getValues();
	    if (!serviceList.length) {
	      this.show(false);
	      if (calendar_resourcebooking.Type.isFunction(this.params.onFullClearHandler)) {
	        this.params.onFullClearHandler();
	      }
	      this.addRow(false, false);
	    }
	  }
	  handlePopupClick(e) {
	    let target = e.target || e.srcElement;
	    if (calendar_resourcebooking.Dom.hasClass(target, 'calendar-resourcebook-content-block-control-delete') || calendar_resourcebooking.Dom.hasClass(target, 'calendar-resourcebook-content-block-detail-delete'))
	      // Delete button
	      {
	        let resWrap = BX.findParent(target, {
	          className: 'calendar-resourcebook-service-row'
	        });
	        if (resWrap) {
	          BX.removeClass(resWrap, 'show');
	          setTimeout(function () {
	            BX.remove(resWrap);
	          }, 500);
	          this.checkRows();
	        }
	      }
	  }
	}

	class TimezoneSelector {
	  constructor(params) {
	    this.params = calendar_resourcebooking.Type.isPlainObject(params) ? params : {};
	    this.DOM = {
	      outerWrap: this.params.outerWrap
	    };
	    calendar_resourcebooking.Dom.addClass(this.DOM.outerWrap, 'fields enumeration field-item');
	    this.create();
	  }
	  create() {
	    this.DOM.select = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create('select'));
	    this.DOM.select.options.add(new Option(calendar_resourcebooking.Loc.getMessage('USER_TYPE_LOADING_TIMEZONE_LIST'), this.params.selectedValue || '', true, true));
	    this.getTimezoneList().then(function (timezoneList) {
	      calendar_resourcebooking.Dom.remove(this.DOM.select.options[0]);
	      timezoneList.forEach(function (timezone) {
	        let selected = this.params.selectedValue ? this.params.selectedValue === timezone.value : timezone.selected;
	        this.DOM.select.options.add(new Option(timezone.label, timezone.value, selected, selected));
	      }, this);
	    }.bind(this));
	  }
	  getTimezoneList(params) {
	    params = params || {};
	    return new Promise(resolve => {
	      if (!TimezoneSelector.timezoneList || params.clearCache) {
	        BX.ajax.runAction('calendar.api.calendarajax.getTimezoneList').then(function (response) {
	          TimezoneSelector.timezoneList = [];
	          for (let key in response.data) {
	            if (response.data.hasOwnProperty(key)) {
	              TimezoneSelector.timezoneList.push({
	                value: response.data[key].timezone_id,
	                label: response.data[key].title,
	                selected: response.data[key].default
	              });
	            }
	          }
	          resolve(TimezoneSelector.timezoneList);
	        }.bind(this), function (response) {
	          resolve(response);
	        });
	      } else {
	        resolve(TimezoneSelector.timezoneList);
	      }
	    });
	  }
	  getValue() {
	    return this.DOM.select.value;
	  }
	}

	class ModeSelector {
	  constructor(params) {
	    this.params = params;
	    this.outerWrap = this.create();
	  }
	  create() {
	    let wrapNode = calendar_resourcebooking.Dom.create("span", {
	        props: {
	          className: "calendar-resourcebook-content-block-select calendar-resourcebook-mode-selector"
	        }
	      }),
	      menuItems = [{
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES'),
	        onclick: function (e, item) {
	          if (calendar_resourcebooking.Type.isFunction(this.params.showResources)) {
	            this.params.showResources();
	          }
	          wrapNode.innerHTML = item.text;
	          this.modeSwitcherPopup.close();
	        }.bind(this)
	      }, {
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_USERS'),
	        onclick: function (e, item) {
	          if (calendar_resourcebooking.Type.isFunction(this.params.showUsers)) {
	            this.params.showUsers();
	          }
	          wrapNode.innerHTML = item.text;
	          this.modeSwitcherPopup.close();
	        }.bind(this)
	      }, {
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS'),
	        onclick: function (e, item) {
	          if (calendar_resourcebooking.Type.isFunction(this.params.showResourcesAndUsers)) {
	            this.params.showResourcesAndUsers();
	          }
	          wrapNode.innerHTML = item.text;
	          this.modeSwitcherPopup.close();
	        }.bind(this)
	      }],
	      switcherId = 'mode-switcher-' + Math.round(Math.random() * 100000);
	    calendar_resourcebooking.Event.bind(wrapNode, 'click', function () {
	      if (this.modeSwitcherPopup && this.modeSwitcherPopup.popupWindow && this.modeSwitcherPopup.popupWindow.isShown()) {
	        return this.modeSwitcherPopup.close();
	      }
	      this.modeSwitcherPopup = BX.PopupMenu.create(switcherId, wrapNode, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        offsetTop: 0,
	        offsetLeft: 20,
	        angle: true
	      });
	      this.modeSwitcherPopup.show();
	      BX.addCustomEvent(this.modeSwitcherPopup.popupWindow, 'onPopupClose', function () {
	        BX.PopupMenu.destroy(switcherId);
	        this.modeSwitcherPopup = null;
	      }.bind(this));
	    }.bind(this));
	    if (this.params.useUsers && !this.params.useResources) {
	      wrapNode.innerHTML = calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_USERS');
	    } else if (this.params.useUsers && this.params.useResources) {
	      wrapNode.innerHTML = calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES_AND_USERS');
	    } else {
	      wrapNode.innerHTML = calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE_RESOURCES');
	    }
	    return wrapNode;
	  }
	  getOuterWrap() {
	    return this.outerWrap;
	  }
	}

	let customizeCrmEntityEditor = function (CrmConfigurator) {
	  let Configurator = function () {
	    Configurator.superclass.constructor.apply(this);
	  };
	  BX.extend(Configurator, CrmConfigurator);
	  Configurator.create = function (id, settings) {
	    let self = new Configurator();
	    self.initialize(id, settings);
	    return self;
	  };
	  Configurator.prototype.layout = function (options, params) {
	    if (this._hasLayout) {
	      return;
	    }
	    if (!BX.type.isPlainObject(params)) {
	      params = {};
	    }
	    if (this._mode === BX.Crm.EntityEditorMode.view) {
	      throw "EntityEditorUserFieldConfigurator. View mode is not supported by this control type.";
	    }
	    this.getBitrix24Limitation({
	      callback: BX.delegate(function (limit) {
	        this.RESOURCE_LIMIT = limit;
	      }, this)
	    });
	    if (this._field) {
	      this.fieldInfo = this._field.getFieldInfo();
	    } else if (!params.settings) {
	      return this.getDefaultUserfieldSettings({
	        displayCallback: BX.delegate(function (settings) {
	          this.layout(options, {
	            settings: settings
	          });
	        }, this)
	      });
	    }
	    this._wrapper = BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content"
	      }
	    });
	    this._innerWrapper = this._wrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-wrap"
	      }
	    })).appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-inner"
	      }
	    }));
	    var fieldSettings = this.fieldInfo ? this.fieldInfo.SETTINGS : params.settings,
	      resourceList = [],
	      selectedResourceList = [],
	      isNew = this._field === null,
	      title = this.getMessage("labelField"),
	      manager = this._editor.getUserFieldManager(),
	      label = this._field ? this._field.getTitle() : manager.getDefaultFieldLabel(this._typeId);
	    this.RESOURCE_LIMIT = fieldSettings.RESOURCE_LIMIT || 0;

	    // region Field Title
	    this._labelInput = BX.create("input", {
	      attrs: {
	        className: "crm-entity-widget-content-input",
	        type: "text",
	        value: label
	      }
	    });
	    this._innerWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block"
	      },
	      children: [
	      // Title
	      BX.create("div", {
	        props: {
	          className: "crm-entity-widget-content-block-title"
	        },
	        children: [BX.create("span", {
	          attrs: {
	            className: "crm-entity-widget-content-block-title-text"
	          },
	          text: title
	        })]
	      }),
	      // Input
	      BX.create("div", {
	        props: {
	          className: "calendar-resourcebook-content-block-field"
	        },
	        children: [this._labelInput]
	      }),
	      // Hr
	      BX.create("hr", {
	        props: {
	          className: "crm-entity-widget-hr"
	        }
	      })]
	    }));
	    // endregion

	    // region Users&Resources Mode selector
	    this._innerWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block"
	      },
	      children: [BX.create("span", {
	        props: {
	          className: "calendar-resourcebook-content-block-title-text"
	        },
	        text: BX.message('USER_TYPE_RESOURCE_CHOOSE')
	      }), new ModeSelector({
	        useResources: fieldSettings.USE_RESOURCES === 'Y',
	        useUsers: fieldSettings.USE_USERS === 'Y',
	        showUsers: function () {
	          this.resourceList.hide();
	          this.userList.show();
	        }.bind(this),
	        showResources: function () {
	          this.resourceList.show();
	          this.userList.hide();
	        }.bind(this),
	        showResourcesAndUsers: function () {
	          this.resourceList.show();
	          this.userList.show();
	        }.bind(this)
	      }).getOuterWrap()]
	    }));
	    // endregion

	    var optionWrapper = this._innerWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block"
	      }
	    }));

	    // region Use Resources Option
	    this.resourcesWrap = optionWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.resourcesTitleWrap = this.resourcesWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: BX.message('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME') + ':'
	    }));
	    this.resourcesListWrap = this.resourcesWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-new-entries-wrap calendar-resourcebook-content-block-detail-inner"
	      }
	    }));
	    this.resourcesListLowControls = this.resourcesWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resource-content-block-add-field"
	      }
	    }));
	    if (fieldSettings.RESOURCES && BX.type.isPlainObject(fieldSettings.RESOURCES['resource']) && BX.type.isArray(fieldSettings.RESOURCES['resource'].SECTIONS)) {
	      fieldSettings.RESOURCES['resource'].SECTIONS.forEach(function (resource) {
	        resourceList.push({
	          id: resource.ID,
	          title: resource.NAME,
	          type: resource.CAL_TYPE
	        });
	      });
	    }
	    if (BX.type.isArray(fieldSettings.SELECTED_RESOURCES)) {
	      fieldSettings.SELECTED_RESOURCES.forEach(function (resource) {
	        selectedResourceList.push({
	          id: resource.id,
	          type: resource.type
	        });
	      });
	    }
	    this.resourceList = new ResourceSelectorFieldEditControl({
	      shown: fieldSettings.USE_RESOURCES === 'Y',
	      editMode: true,
	      outerWrap: this.resourcesWrap,
	      listWrap: this.resourcesListWrap,
	      controlsWrap: this.resourcesListLowControls,
	      values: selectedResourceList,
	      resourceList: resourceList,
	      checkLimitCallback: this.checkResourceCountLimit.bind(this),
	      checkLimitCallbackForNew: this.checkResourceCountLimitForNewEntries.bind(this)
	    });
	    // endregion

	    // region Users Selector
	    this.userSelectorWrap = optionWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.usersTitleWrap = this.userSelectorWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: BX.message('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME') + ':'
	    }));
	    this.usersListWrap = this.userSelectorWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control"
	      }
	    }));
	    var itemsSelected = [];
	    if (BX.type.isArray(fieldSettings.SELECTED_USERS)) {
	      fieldSettings.SELECTED_USERS.forEach(function (user) {
	        itemsSelected.push('U' + parseInt(user));
	      });
	    }
	    this.userList = new UserSelectorFieldEditControl({
	      shown: fieldSettings.USE_USERS === 'Y',
	      outerWrap: this.userSelectorWrap,
	      wrapNode: this.usersListWrap,
	      socnetDestination: calendar_resourcebookinguserfield.ResourcebookingUserfield.getSocnetDestination(),
	      itemsSelected: itemsSelected,
	      checkLimitCallback: this.checkResourceCountLimit.bind(this)
	    });
	    // endregion

	    // Region Data, Time and services
	    optionWrapper.appendChild(BX.create("hr", {
	      props: {
	        className: "crm-entity-widget-hr"
	      }
	    }));
	    this.datetimeOptionsWrap = optionWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.datetimeOptionsWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: BX.message('USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE') + ':'
	    }));
	    this.datetimeOptionsInnerWrap = this.datetimeOptionsWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-options"
	      }
	    }));
	    this.timezoneSettingsWrap = optionWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-options"
	      },
	      style: {
	        display: fieldSettings.FULL_DAY === 'Y' ? 'none' : ''
	      }
	    }));
	    this.timezoneSettingsWrap.appendChild(BX.create("hr", {
	      props: {
	        className: "crm-entity-widget-hr"
	      }
	    }));
	    this.timezoneSettingsWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(BX.create("span", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: BX.message('USER_TYPE_RESOURCE_TIMEZONE_SETTINGS_TITLE') + ':'
	    }));
	    this.timezoneSelectorWrap = this.timezoneSettingsWrap.appendChild(BX.create("div", {
	      style: {
	        display: fieldSettings.USE_USER_TIMEZONE === 'Y' ? 'none' : ''
	      }
	    }));
	    this.timezoneSelectWrap = this.timezoneSelectorWrap.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-field"
	      }
	    }));
	    this.timezoneSelector = new TimezoneSelector({
	      outerWrap: this.timezoneSelectWrap,
	      selectedValue: fieldSettings.TIMEZONE
	    });
	    this.useUserTimezoneCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox",
	        checked: fieldSettings.USE_USER_TIMEZONE === 'Y'
	      }
	    });
	    this.timezoneSettingsWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this.useUserTimezoneCheckBox, BX.create("span", {
	        text: BX.message('USER_TYPE_RESOURCE_USE_USER_TIMEZONE')
	      })],
	      events: {
	        click: BX.proxy(this.handleUserTimezoneCheckbox, this)
	      }
	    }));

	    // endregion

	    //region Checkbox "Full day"
	    this._fulldayCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox",
	        checked: fieldSettings.FULL_DAY === 'Y'
	      },
	      events: {
	        click: BX.proxy(this.handleFullDayMode, this)
	      }
	    });
	    this.datetimeOptionsInnerWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this._fulldayCheckBox, BX.create("span", {
	        text: BX.message('USER_TYPE_RESOURCE_FULL_DAY')
	      })]
	    }));
	    //endregion

	    //region Checkbox "Add services"
	    this._servicesCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox",
	        checked: fieldSettings.USE_SERVICES === 'Y'
	      },
	      events: {
	        click: BX.delegate(function () {
	          if (this.serviceList) {
	            this.serviceList.show(this._servicesCheckBox.checked);
	          }
	        }, this)
	      }
	    });
	    this.datetimeOptionsInnerWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this._servicesCheckBox, BX.create("span", {
	        text: BX.message('USER_TYPE_RESOURCE_ADD_SERVICES')
	      })]
	    }));
	    this.serviceList = new ServiceSelector({
	      outerCont: this.datetimeOptionsInnerWrap,
	      onFullClearHandler: function () {
	        this._servicesCheckBox.checked = false;
	      }.bind(this),
	      fieldSettings: fieldSettings,
	      getFullDayValue: function () {
	        return this._fulldayCheckBox.checked;
	      }.bind(this)
	    });
	    optionWrapper.appendChild(BX.create("hr", {
	      props: {
	        className: "crm-entity-widget-hr"
	      }
	    }));

	    //region Checkbox "Is Required"
	    this.additionaOptionsWrap = optionWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-options"
	      }
	    }));
	    this._isRequiredCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox",
	        checked: this._field && this._field.isRequired()
	      }
	    });
	    this.additionaOptionsWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this._isRequiredCheckBox, BX.create("span", {
	        text: this.getMessage("isRequiredField")
	      })]
	    }));
	    //endregion

	    //region Checkbox "Show Always"
	    this._showAlwaysCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox"
	      }
	    });
	    if (isNew) {
	      this._showAlwaysCheckBox.checked = BX.prop.getBoolean(this._settings, "showAlways", true);
	    } else {
	      this._showAlwaysCheckBox.checked = this._field.checkOptionFlag(BX.Crm.EntityEditorControlOptions.showAlways);
	    }
	    this.additionaOptionsWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this._showAlwaysCheckBox, BX.create("span", {
	        text: this.getMessage("showAlways")
	      })]
	    }));
	    //endregion

	    //region Checkbox "Overbooking"
	    this._overbookingCheckBox = BX.create("input", {
	      props: {
	        type: "checkbox",
	        checked: fieldSettings.ALLOW_OVERBOOKING === 'Y'
	      }
	    });
	    this.additionaOptionsWrap.appendChild(BX.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this._overbookingCheckBox, BX.create("span", {
	        text: BX.message('USER_TYPE_RESOURCE_OVERBOOKING')
	      })]
	    }));
	    //endregion

	    this._innerWrapper.appendChild(BX.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-btn-container"
	      },
	      children: [BX.create("hr", {
	        props: {
	          className: "crm-entity-widget-hr"
	        }
	      }), BX.create("button", {
	        props: {
	          type: "button",
	          className: "ui-btn ui-btn-sm ui-btn-primary"
	        },
	        text: BX.message("CRM_EDITOR_SAVE"),
	        events: {
	          click: BX.delegate(this.onSaveButtonClick, this)
	        }
	      }), BX.create("button", {
	        props: {
	          type: "button",
	          className: "ui-btn ui-btn-sm ui-btn-light-border"
	        },
	        text: BX.message("CRM_EDITOR_CANCEL"),
	        events: {
	          click: BX.delegate(this.onCancelButtonClick, this)
	        }
	      })]
	    }));
	    this.fieldSettings = fieldSettings;
	    this.registerLayout(options);
	    this._hasLayout = true;
	  };
	  Configurator.prototype.getDefaultUserfieldSettings = function (params) {
	    BX.ajax.runAction('calendar.api.resourcebookingajax.getdefaultuserfieldsettings', {
	      data: {}
	    }).then(function (response) {
	      if (params && BX.type.isFunction(params.displayCallback)) {
	        params.displayCallback(response.data);
	      }
	    }, function (response) {
	      /**
	       {
	      		 "status": "error",
	      		 "errors": [...]
	      	 }
	       **/
	    });
	  };
	  Configurator.prototype.getBitrix24Limitation = function (params) {
	    BX.ajax.runAction('calendar.api.resourcebookingajax.getbitrix24limitation', {
	      data: {}
	    }).then(function (response) {
	      if (params && BX.type.isFunction(params.callback)) {
	        params.callback(response.data);
	      }
	    }, function (response) {
	      /**
	       {
	      		 "status": "error",
	      		 "errors": [...]
	      	 }
	       **/
	    });
	  };
	  Configurator.prototype.onSaveButtonClick = function () {
	    if (this._isLocked) {
	      return;
	    }
	    if (this.RESOURCE_LIMIT > 0 && this.getTotalResourceCount() > this.RESOURCE_LIMIT) {
	      calendar_resourcebooking.BookingUtil.showLimitationPopup();
	      return;
	    }
	    var params = {
	      typeId: this._typeId,
	      label: this._labelInput.value,
	      mandatory: this._isRequiredCheckBox.checked,
	      showAlways: this._showAlwaysCheckBox.checked,
	      multiple: true
	    };
	    if (this._field) {
	      params["field"] = this._field;
	    }
	    this.fieldSettings.USE_RESOURCES = this.resourceList.isShown() ? 'Y' : 'N';
	    this.fieldSettings.USE_USERS = this.userList.isShown() ? 'Y' : 'N';
	    if (this.fieldSettings && BX.type.isPlainObject(this.fieldSettings.RESOURCES) && BX.type.isPlainObject(this.fieldSettings.RESOURCES['resource'])) {
	      this.fieldSettings.SELECTED_RESOURCES = [];
	      this.resourceList.getSelectedValues().forEach(function (value) {
	        this.fieldSettings.SELECTED_RESOURCES.push(value);
	      }, this);
	      this.resourceList.getDeletedValues().forEach(function (value) {
	        this.fieldSettings.SELECTED_RESOURCES.push(value);
	      }, this);
	    }
	    if (this.fieldSettings && this.userList) {
	      this.fieldSettings.SELECTED_USERS = [0];
	      this.userList.getAttendeesCodesList().forEach(function (code) {
	        if (code.substr(0, 1) === 'U') {
	          this.fieldSettings.SELECTED_USERS.push(parseInt(code.substr(1)));
	        }
	      }, this);
	    }
	    this.fieldSettings.USE_SERVICES = this._servicesCheckBox.checked ? 'Y' : 'N';
	    this.fieldSettings.SERVICE_LIST = [];
	    if (this._servicesCheckBox.checked && this.serviceList) {
	      this.fieldSettings.SERVICE_LIST = this.serviceList.getValues();
	    }
	    this.fieldSettings.FULL_DAY = this._fulldayCheckBox.checked ? 'Y' : 'N';
	    this.fieldSettings.ALLOW_OVERBOOKING = this._overbookingCheckBox.checked ? 'Y' : 'N';
	    if (this.fieldSettings.FULL_DAY === 'N') {
	      this.fieldSettings.TIMEZONE = this.timezoneSelector.getValue();
	      this.fieldSettings.USE_USER_TIMEZONE = this.useUserTimezoneCheckBox.checked ? 'Y' : 'N';
	    } else {
	      this.fieldSettings.TIMEZONE = '';
	      this.fieldSettings.USE_USER_TIMEZONE = 'N';
	    }
	    params["settings"] = this.fieldSettings;
	    BX.onCustomEvent(this, "onSave", [this, params]);
	  };
	  Configurator.prototype.getTotalResourceCount = function () {
	    var result = 0;
	    if (this.fieldSettings) {
	      if (BX.type.isPlainObject(this.fieldSettings.RESOURCES) && BX.type.isPlainObject(this.fieldSettings.RESOURCES.resource) && BX.type.isArray(this.fieldSettings.RESOURCES.resource.SECTIONS)) {
	        result += this.fieldSettings.RESOURCES.resource.SECTIONS.length;
	      }
	      result -= this.resourceList.getDeletedValues().length;
	      this.resourceList.getSelectedValues().forEach(function (value) {
	        if (!value.id && value.title !== '') {
	          result++;
	        }
	      }, this);
	      if (this.userList) {
	        result += this.userList.getAttendeesCodesList().length;
	      }
	    }
	    return result;
	  };
	  Configurator.prototype.checkResourceCountLimitForNewEntries = function () {
	    return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() < this.RESOURCE_LIMIT;
	  };
	  Configurator.prototype.checkResourceCountLimit = function () {
	    return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() <= this.RESOURCE_LIMIT;
	  };
	  Configurator.prototype.handleFullDayMode = function () {
	    this.timezoneSettingsWrap.style.display = this._fulldayCheckBox.checked ? 'none' : '';
	  };
	  Configurator.prototype.handleUserTimezoneCheckbox = function () {
	    this.timezoneSelectorWrap.style.display = this.useUserTimezoneCheckBox.checked ? 'none' : '';
	  };
	  return Configurator;
	};

	class CalendarViewSettingsSlider {
	  constructor(params) {
	    this.id = 'calendar_custom_settings_' + Math.round(Math.random() * 1000000);
	    this.zIndex = 3100;
	    this.sliderId = "calendar:resbook-settings-slider";
	    this.SLIDER_WIDTH = 400;
	    this.SLIDER_DURATION = 80;
	    this.DOM = {};
	    this.params = params;
	  }
	  show() {
	    BX.SidePanel.Instance.open(this.sliderId, {
	      contentCallback: BX.delegate(this.create, this),
	      width: this.SLIDER_WIDTH,
	      animationDuration: this.SLIDER_DURATION
	    });
	    this.hideHandler = this.hide.bind(this);
	    this.destroyHandler = this.destroy.bind(this);
	    BX.addCustomEvent("SidePanel.Slider:onClose", this.hideHandler);
	    BX.addCustomEvent("SidePanel.Slider:onCloseComplete");
	  }
	  close() {
	    BX.SidePanel.Instance.close();
	  }
	  hide(event) {
	    if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	      // if (this.denyClose)
	      // {
	      // 	event.denyAction();
	      // }
	      // else
	      // {
	      BX.removeCustomEvent("SidePanel.Slider:onClose", this.hideHandler);
	      //}
	    }
	  }

	  destroy(event) {
	    if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	      BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.destroyHandler);
	      BX.SidePanel.Instance.destroy(this.sliderId);
	    }
	  }
	  create() {
	    let promise = new BX.Promise();
	    let html = '<div class="webform-buttons calendar-form-buttons-fixed">' + '<span id="' + this.id + '_save" class="webform-small-button webform-small-button-blue">' + BX.message('USER_TYPE_RESOURCE_SAVE') + '</span>' + '<span id="' + this.id + '_close" class="webform-button-link">' + BX.message('USER_TYPE_RESOURCE_CLOSE') + '</span>' + '</div>' + '<div class="calendar-slider-calendar-wrap">' + '<div class="calendar-slider-header"><div class="calendar-head-area"><div class="calendar-head-area-inner"><div class="calendar-head-area-title">' + '<span class="calendar-head-area-name">' + BX.message('USER_TYPE_RESOURCE_SETTINGS') + '</span>' + '</div></div></div></div>' + '<div class="resource-booking-slider-workarea"><div class="resource-booking-slider-content"><div id="' + this.id + '_content" class="resource-booking-settings"></div></div></div></div>';
	    promise.fulfill(html);
	    setTimeout(this.initControls.bind(this), 100);
	    return promise;
	  }
	  initControls() {
	    this.DOM.content = BX(this.id + '_content');
	    BX.bind(BX(this.id + '_save'), 'click', this.save.bind(this));
	    BX.bind(BX(this.id + '_close'), 'click', this.close.bind(this));

	    // 1. Field
	    if (this.params && BX.type.isArray(this.params.filterSelectValues)) {
	      this.DOM.fieldOuterWrap = this.DOM.content.appendChild(BX.create('DIV', {
	        attrs: {
	          className: 'calendar-settings-control'
	        }
	      }));
	      this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {
	        attrs: {
	          className: 'calendar-settings-control-name'
	        },
	        text: BX.message('USER_TYPE_RESOURCE_FILTER_NAME')
	      }));
	      this.DOM.fieldSelect = this.DOM.fieldOuterWrap.appendChild(BX.create('DIV', {
	        attrs: {
	          className: 'calendar-field-container calendar-field-container-select'
	        }
	      })).appendChild(BX.create('DIV', {
	        attrs: {
	          className: 'calendar-field-block'
	        }
	      })).appendChild(BX.create('select', {
	        attrs: {
	          className: 'calendar-field calendar-field-select'
	        }
	      }));
	      this.params.filterSelectValues.forEach(function (value) {
	        this.DOM.fieldSelect.options.add(new Option(value.TEXT, value.VALUE, this.params.filterSelect === value.VALUE, this.params.filterSelect === value.VALUE));
	      }, this);
	    }
	  }
	  save() {
	    let entityType = this.params.entityType || 'none';
	    BX.userOptions.save('calendar', 'resourceBooking', entityType, this.DOM.fieldSelect.value);
	    this.close();
	    BX.reload();
	  }
	}

	class AdminSettingsViewer {
	  constructor(params = {}) {
	    this.params = calendar_resourcebooking.Type.isPlainObject(params) ? params : {};
	    this.fieldSettings = calendar_resourcebooking.Type.isPlainObject(this.params.settings) ? this.params.settings : {};
	    this.DOM = {
	      outerWrap: document.getElementById(this.params.outerWrapId),
	      form: document.forms[this.params.formName]
	    };
	  }
	  showLayout() {
	    if (!this.DOM.outerWrap || !this.DOM.form) return;
	    calendar_resourcebooking.Event.bind(this.DOM.form, 'submit', this.onSubmit.bind(this));
	    calendar_resourcebooking.Dom.addClass(this.DOM.outerWrap, 'calendar-resourcebook-content calendar-resourcebook-content-admin-settings');
	    this.DOM.innerWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-wrap"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-inner"
	      }
	    }));
	    let resourceList = [],
	      selectedResourceList = [];
	    this.DOM.innerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block"
	      },
	      children: [calendar_resourcebooking.Dom.create("span", {
	        props: {
	          className: "calendar-resourcebook-content-block-title-text"
	        },
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_CHOOSE')
	      }), new ModeSelector({
	        useResources: this.fieldSettings.USE_RESOURCES === 'Y',
	        useUsers: this.fieldSettings.USE_USERS === 'Y',
	        showUsers: function () {
	          this.resourceList.hide();
	          this.userList.show();
	        }.bind(this),
	        showResources: function () {
	          this.resourceList.show();
	          this.userList.hide();
	        }.bind(this),
	        showResourcesAndUsers: function () {
	          this.resourceList.show();
	          this.userList.show();
	        }.bind(this)
	      }).getOuterWrap()]
	    }));
	    this.DOM.optionWrap = this.DOM.innerWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block"
	      }
	    }));

	    // region Use Resources Option
	    this.resourcesWrap = this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.resourcesTitleWrap = this.resourcesWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_RESOURCE_CONTROL_DEFAULT_NAME') + ':'
	    }));
	    this.resourcesListWrap = this.resourcesWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-new-entries-wrap calendar-resourcebook-content-block-detail-inner"
	      }
	    }));
	    this.resourcesListLowControls = this.resourcesWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resource-content-block-add-field"
	      }
	    }));
	    if (this.fieldSettings.RESOURCES && calendar_resourcebooking.Type.isPlainObject(this.fieldSettings.RESOURCES['resource']) && calendar_resourcebooking.Type.isArray(this.fieldSettings.RESOURCES['resource'].SECTIONS)) {
	      this.fieldSettings.RESOURCES['resource'].SECTIONS.forEach(function (resource) {
	        resourceList.push({
	          id: resource.ID,
	          title: resource.NAME,
	          type: resource.CAL_TYPE
	        });
	      });
	    }
	    if (calendar_resourcebooking.Type.isArray(this.fieldSettings.SELECTED_RESOURCES)) {
	      this.fieldSettings.SELECTED_RESOURCES.forEach(function (resource) {
	        selectedResourceList.push({
	          id: resource.id,
	          type: resource.type
	        });
	      });
	    }
	    this.resourceList = new ResourceSelectorFieldEditControl({
	      shown: this.fieldSettings.USE_RESOURCES === 'Y',
	      editMode: true,
	      outerWrap: this.resourcesWrap,
	      listWrap: this.resourcesListWrap,
	      controlsWrap: this.resourcesListLowControls,
	      values: selectedResourceList,
	      resourceList: resourceList,
	      checkLimitCallback: this.checkResourceCountLimit.bind(this)
	    });
	    this.userSelectorWrap = this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.usersTitleWrap = this.userSelectorWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_USERS_CONTROL_DEFAULT_NAME') + ':'
	    }));
	    this.usersListWrap = this.userSelectorWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control custom-field-item"
	      }
	    }));
	    let itemsSelected = [];
	    if (calendar_resourcebooking.Type.isArray(this.fieldSettings.SELECTED_USERS)) {
	      this.fieldSettings.SELECTED_USERS.forEach(function (user) {
	        itemsSelected.push('U' + parseInt(user));
	      });
	    }
	    this.userList = new UserSelectorFieldEditControl({
	      shown: this.fieldSettings.USE_USERS === 'Y',
	      outerWrap: this.userSelectorWrap,
	      wrapNode: this.usersListWrap,
	      socnetDestination: this.params.socnetDestination,
	      itemsSelected: itemsSelected
	    });

	    // Region Data, Time and services
	    this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("hr", {
	      props: {
	        className: "calendar-resbook-hr"
	      }
	    }));
	    this.datetimeOptionsWrap = this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-control-field calendar-resourcebook-content-block-control-field-add"
	      }
	    }));
	    this.datetimeOptionsWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title"
	      }
	    })).appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-title-text"
	      },
	      text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_DATETIME_BLOCK_TITLE') + ':'
	    }));
	    this.datetimeOptionsInnerWrap = this.datetimeOptionsWrap.appendChild(calendar_resourcebooking.Dom.create("div", {
	      props: {
	        className: "calendar-resourcebook-content-block-options"
	      }
	    }));
	    // endregion

	    //region Checkbox "Full day"
	    this.DOM.fulldayCheckBox = calendar_resourcebooking.Dom.create("input", {
	      props: {
	        type: "checkbox",
	        checked: this.fieldSettings.FULL_DAY === 'Y'
	      }
	    });
	    this.datetimeOptionsInnerWrap.appendChild(calendar_resourcebooking.Dom.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this.DOM.fulldayCheckBox, calendar_resourcebooking.Dom.create("span", {
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_FULL_DAY')
	      })]
	    }));
	    //endregion

	    //region Checkbox "Add services"
	    this.DOM.useServicedayCheckBox = calendar_resourcebooking.Dom.create("input", {
	      props: {
	        type: "checkbox",
	        checked: this.fieldSettings.USE_SERVICES === 'Y'
	      },
	      events: {
	        'click': function () {
	          if (this.serviceList) {
	            this.serviceList.show(this.DOM.useServicedayCheckBox.checked);
	          }
	        }.bind(this)
	      }
	    });
	    this.datetimeOptionsInnerWrap.appendChild(calendar_resourcebooking.Dom.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this.DOM.useServicedayCheckBox, calendar_resourcebooking.Dom.create("span", {
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_ADD_SERVICES')
	      })]
	    }));
	    this.serviceList = new ServiceSelector({
	      outerCont: this.datetimeOptionsInnerWrap,
	      fieldSettings: this.fieldSettings,
	      getFullDayValue: function () {
	        return this.DOM.fulldayCheckBox.checked;
	      }.bind(this)
	    });
	    this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("hr", {
	      props: {
	        className: "calendar-resbook-hr"
	      }
	    }));
	    this.DOM.overbookingCheckbox = calendar_resourcebooking.Dom.create("input", {
	      props: {
	        type: "checkbox",
	        checked: this.fieldSettings.ALLOW_OVERBOOKING === 'Y'
	      }
	    });
	    this.DOM.optionWrap.appendChild(calendar_resourcebooking.Dom.create("label", {
	      props: {
	        className: 'calendar-resourcebook-content-block-option'
	      },
	      children: [this.DOM.overbookingCheckbox, calendar_resourcebooking.Dom.create("span", {
	        text: calendar_resourcebooking.Loc.getMessage('USER_TYPE_RESOURCE_OVERBOOKING')
	      })]
	    }));
	    //endregion
	  }

	  onSubmit(e) {
	    if (!this.DOM.inputsWrap) {
	      this.DOM.inputsWrap = this.DOM.outerWrap.appendChild(calendar_resourcebooking.Dom.create("DIV"));
	    } else {
	      calendar_resourcebooking.Dom.clean(this.DOM.inputsWrap);
	    }
	    let inputName = this.params.htmlControl.NAME;
	    this.DOM.inputsWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        name: inputName + '[USE_USERS]',
	        value: this.userList.isShown() ? 'Y' : 'N',
	        type: 'hidden'
	      }
	    }));
	    this.DOM.inputsWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        name: inputName + '[USE_RESOURCES]',
	        value: this.resourceList.isShown() ? 'Y' : 'N',
	        type: 'hidden'
	      }
	    }));
	    this.DOM.inputsWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        name: inputName + '[USE_SERVICES]',
	        value: this.DOM.useServicedayCheckBox.checked ? 'Y' : 'N',
	        type: 'hidden'
	      }
	    }));
	    this.DOM.inputsWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        name: inputName + '[FULL_DAY]',
	        value: this.DOM.fulldayCheckBox.checked ? 'Y' : 'N',
	        type: 'hidden'
	      }
	    }));
	    this.DOM.inputsWrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	      attrs: {
	        name: inputName + '[ALLOW_OVERBOOKING]',
	        value: this.DOM.overbookingCheckbox.checked ? 'Y' : 'N',
	        type: 'hidden'
	      }
	    }));

	    // Selected resources
	    if (this.resourceList) {
	      this.prepareFormDataInputs(this.DOM.inputsWrap, this.resourceList.getSelectedValues().concat(this.resourceList.getDeletedValues()), inputName + '[SELECTED_RESOURCES]');
	    }

	    // // Selected users
	    if (this.userList) {
	      let SELECTED_USERS = [];
	      this.userList.getAttendeesCodesList().forEach(function (code) {
	        if (code.substr(0, 1) === 'U') {
	          SELECTED_USERS.push(parseInt(code.substr(1)));
	        }
	      }, this);
	      this.prepareFormDataInputs(this.DOM.inputsWrap, SELECTED_USERS, inputName + '[SELECTED_USERS]');
	    }
	    if (this.DOM.useServicedayCheckBox.checked && this.serviceList) {
	      this.prepareFormDataInputs(this.DOM.inputsWrap, this.serviceList.getValues(), inputName + '[SERVICE_LIST]');
	    }
	  }
	  prepareFormDataInputs(wrap, data, inputName) {
	    data.forEach(function (value, ind) {
	      if (calendar_resourcebooking.Type.isPlainObject(value)) {
	        let k;
	        for (k in value) {
	          if (value.hasOwnProperty(k)) {
	            wrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	              attrs: {
	                name: inputName + '[' + ind + '][' + k + ']',
	                value: value[k],
	                type: 'hidden'
	              }
	            }));
	          }
	        }
	      } else {
	        wrap.appendChild(calendar_resourcebooking.Dom.create('INPUT', {
	          attrs: {
	            name: inputName + '[' + ind + ']',
	            value: value,
	            type: 'hidden'
	          }
	        }));
	      }
	    }, this);
	  }
	  getTotalResourceCount() {
	    let result = 0;
	    if (this.fieldSettings) {
	      if (calendar_resourcebooking.Type.isPlainObject(this.fieldSettings.RESOURCES) && calendar_resourcebooking.Type.isPlainObject(this.fieldSettings.RESOURCES.resource) && calendar_resourcebooking.Type.isArray(this.fieldSettings.RESOURCES.resource.SECTIONS)) {
	        result += this.fieldSettings.RESOURCES.resource.SECTIONS.length;
	      }
	      if (this.resourceList) {
	        result -= this.resourceList.getDeletedValues().length;
	        this.resourceList.getSelectedValues().forEach(function (value) {
	          if (!value.id && value.title !== '') {
	            result++;
	          }
	        }, this);
	      }
	      if (this.userList) {
	        result += this.userList.getAttendeesCodesList().length;
	      }
	    }
	    return result;
	  }
	  checkResourceCountLimitForNewEntries() {
	    return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() < this.RESOURCE_LIMIT;
	  }
	  checkResourceCountLimit() {
	    return this.RESOURCE_LIMIT <= 0 || this.getTotalResourceCount() <= this.RESOURCE_LIMIT;
	  }
	}

	class ResourcebookingUserfield {
	  /**
	   * Creates instance of Resourcebooking field for crm form edit interface (not for live form)
	   * and initialize it with given field params
	   * Used in CRM webform module to display and adjust resourcebooking field
	   *
	   * @param {array} params - incoming data params
	   */
	  static initCrmFormFieldController(params) {
	    if (!main_core.Type.isPlainObject(params)) {
	      params = {
	        field: {}
	      };
	    }
	    let bookingFieldParams = {};
	    if (main_core.Type.isDomNode(params.field.node)) {
	      bookingFieldParams.outerWrap = params.field.node;
	    } else {
	      throw new Error("The argument \"params.field.node\" must be a DOM node.");
	    }
	    bookingFieldParams.innerWrap = bookingFieldParams.outerWrap.querySelector('.crm-webform-resourcebooking-wrap');
	    if (!bookingFieldParams.innerWrap) {
	      throw new Error("Can't find necessary DOM node \"div.crm-webform-resourcebooking-wrap\"");
	    }
	    bookingFieldParams.name = params.field.name;
	    bookingFieldParams.formName = 'FIELD[' + params.field.name + ']';
	    bookingFieldParams.captionNode = params.field.lblCaption;
	    bookingFieldParams.entityFieldName = params.field.entity_field_name;
	    bookingFieldParams.entityName = params.field.dict.entity_field_name;
	    bookingFieldParams.settings = {
	      caption: params.field.captionValue || params.field.dict.caption,
	      required: params.field.isRequired || params.field.dict.required,
	      data: main_core.Type.isPlainObject(params.field.booking) && main_core.Type.isPlainObject(params.field.booking.settings_data) ? params.field.booking.settings_data : params.field.settingsData || []
	    };
	    let adjustFieldController = new AdjustFieldController(bookingFieldParams);
	    adjustFieldController.init();
	    return adjustFieldController;
	  }
	  static initEditFieldController(params) {
	    let editFieldController = new EditFieldController(params);
	    editFieldController.init();
	    return editFieldController;
	  }
	  static getCrmFieldConfigurator(id, settings) {
	    if (window.BX && BX.Crm && main_core.Type.isFunction(BX.Crm.EntityEditorUserFieldConfigurator)) {
	      return customizeCrmEntityEditor(BX.Crm.EntityEditorUserFieldConfigurator).create(id, settings);
	    }
	  }
	  static getUserFieldParams(params = {}) {
	    return new Promise(resolve => {
	      let fieldName = params.fieldName || '';
	      if (params.clearCache || !ResourcebookingUserfield.fieldParamsCache[params.fieldName]) {
	        BX.ajax.runAction('calendar.api.resourcebookingajax.getfieldparams', {
	          data: {
	            fieldname: params.fieldName,
	            selectedUsers: params.selectedUsers || []
	          }
	        }).then(response => {
	          ResourcebookingUserfield.fieldParamsCache[fieldName] = response.data;
	          resolve(response.data);
	        }, response => {});
	      } else {
	        resolve(ResourcebookingUserfield.fieldParamsCache[fieldName]);
	      }
	    });
	  }
	  static getPluralMessage(messageId, number) {
	    let pluralForm, langId;
	    langId = BX.message('LANGUAGE_ID') || 'en';
	    number = parseInt(number);
	    if (number < 0) {
	      number = -1 * number;
	    }
	    if (langId) {
	      switch (langId) {
	        case 'ru':
	        case 'ua':
	          if (number % 10 === 1 && number % 100 !== 11) {
	            pluralForm = 0;
	          } else {
	            pluralForm = number % 10 >= 2 && number % 10 <= 4 && (number % 100 < 10 || number % 100 >= 20) ? 1 : 2;
	          }
	          break;
	        case 'pl':
	          if (number <= 4) {
	            pluralForm = number === 1 ? 0 : 1;
	          } else {
	            pluralForm = 2;
	          }
	          break;
	        default:
	          // en, de and other languages
	          pluralForm = number !== 1 ? 1 : 0;
	          break;
	      }
	    } else {
	      pluralForm = 1;
	    }
	    return BX.message(messageId + '_PLURAL_' + pluralForm);
	  }
	  static getParamsFromHash(userfieldId) {
	    let params,
	      regRes,
	      hash = unescape(window.location.hash);
	    if (hash) {
	      regRes = new RegExp('#calendar:' + userfieldId + '\\|(.*)', 'ig').exec(hash);
	      if (regRes && regRes.length > 1) {
	        params = regRes[1].split('|');
	      }
	    }
	    return params;
	  }
	  static openExternalSettingsSlider(params) {
	    let settingsSlider = new CalendarViewSettingsSlider(params);
	    settingsSlider.show();
	  }
	  static setSocnetDestination(socnetDestination) {
	    ResourcebookingUserfield.socnetDestination = socnetDestination;
	  }
	  static getSocnetDestination() {
	    return ResourcebookingUserfield.socnetDestination;
	  }
	}
	ResourcebookingUserfield.fieldParamsCache = {};
	ResourcebookingUserfield.socnetDestination = null;

	exports.Resourcebooking = calendar_resourcebooking.Resourcebooking;
	exports.BookingUtil = calendar_resourcebooking.BookingUtil;
	exports.AdminSettingsViewer = AdminSettingsViewer;
	exports.ResourcebookingUserfield = ResourcebookingUserfield;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.UI.EntitySelector,BX.Event,BX,BX.Main,BX,BX.Calendar,BX.Calendar));
//# sourceMappingURL=resourcebookinguserfield.bundle.js.map
