this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,calendar_roomsmanager,calendar_categorymanager,ui_icons_b24,main_loader,calendar_entry,ui_dialogs_messagebox,ui_buttons,calendar_planner,ui_entitySelector,main_core_events,main_popup,calendar_controls,intranet_controlButton,main_core,calendar_util) {
	'use strict';

	class TimeSelector {
	  constructor(params) {
	    this.Z_INDEX = 4000;
	    this.valueList = [];
	    this.DOM = {
	      wrap: params.wrap,
	      input: params.input
	    };
	    for (let hour = 0; hour < 24; hour++) {
	      this.valueList.push({
	        value: hour * 60,
	        label: calendar_util.Util.formatTime(hour, 0)
	      });
	      this.valueList.push({
	        value: hour * 60 + 30,
	        label: calendar_util.Util.formatTime(hour, 30)
	      });
	    }
	    this.onChangeCallback = main_core.Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
	    this.selectContol = new calendar_controls.SelectInput({
	      input: this.DOM.input,
	      zIndex: this.Z_INDEX,
	      values: this.valueList,
	      onChangeCallback: data => {
	        if (this.onChangeCallback) {
	          this.onChangeCallback(this.selectContol.getInputValue(), data.dataValue);
	        }
	      },
	      onPopupShowCallback: () => main_core.Dom.addClass(this.DOM.input.parentNode, 'active'),
	      onPopupCloseCallback: () => main_core.Dom.removeClass(this.DOM.input.parentNode, 'active')
	    });
	  }
	  highlightValue(date) {
	    this.valueList.forEach(el => el.selected = false); // unselect previous time

	    const minutes = date.getHours() * 60 + date.getMinutes();
	    this.selectContol.setValue({
	      value: minutes
	    }); // this is needed for correct scroll

	    let selectedValue = this.valueList.find(el => el.value === minutes);
	    if (!selectedValue) {
	      return;
	    }
	    selectedValue.selected = true;
	    this.selectContol.setValueList(this.valueList);
	  }
	  updateDurationHints(fromTime, toTime, fromDate, toDate) {
	    const parsedFromTime = calendar_util.Util.parseTime(fromTime);
	    const parsedToTime = calendar_util.Util.parseTime(toTime);
	    const parsedFromDate = calendar_util.Util.parseDate(fromDate);
	    const parsedToDate = calendar_util.Util.parseDate(toDate);
	    const fromMinutes = parsedFromTime.h * 60 + parsedFromTime.m;
	    const toMinutes = parsedToTime.h * 60 + parsedToTime.m;
	    const isSameDate = fromDate === toDate;
	    const iterateFrom = isSameDate ? this.approximate(fromMinutes + 15, 15) : 0;
	    const firstHour = this.approximate(fromMinutes + 60 + 15 / 2, 30);
	    this.valueList = [];
	    if (fromDate === toDate) {
	      this.valueList.push(this.getValueElement(fromMinutes, fromMinutes, toMinutes, parsedFromDate, parsedToDate));
	    }
	    for (let minute = iterateFrom; minute <= 24 * 60; minute += isSameDate && minute < firstHour ? 15 : 30) {
	      this.valueList.push(this.getValueElement(fromMinutes, minute, toMinutes, parsedFromDate, parsedToDate));
	    }
	    this.selectContol.setValueList(this.valueList);
	  }
	  getValueElement(fromMinute, currentMinute, toMinute, fromDate, toDate) {
	    const hour = Math.floor(currentMinute / 60);
	    const min = currentMinute % 60;
	    const time = calendar_util.Util.formatTime(hour, min);
	    const durationHint = this.getStyledDurationHint(fromMinute, currentMinute, fromDate, toDate);
	    const selected = currentMinute === toMinute;
	    return {
	      value: currentMinute,
	      label: time,
	      hint: durationHint,
	      selected
	    };
	  }
	  getStyledDurationHint(fromMinute, currentMinute, fromDate, toDate) {
	    const durationHint = this.getDurationHint(fromMinute, currentMinute, fromDate, toDate);
	    if (durationHint !== '') {
	      return `<div class="menu-popup-item-hint">${durationHint}</div>`;
	    }
	    return '';
	  }
	  getDurationHint(fromMinutes, toMinutes, fromDate, toDate) {
	    const from = new Date(fromDate.getTime() + fromMinutes * 60 * 1000);
	    const to = new Date(toDate.getTime() + toMinutes * 60 * 1000);
	    const diff = to.getTime() - from.getTime();
	    const diffDays = this.approximateFloor(diff / (1000 * 60 * 60 * 24), 1);
	    const diffHours = this.approximate(diff / (1000 * 60 * 60), 0.5);
	    const diffMinutes = this.approximate(diff / (1000 * 60), 1);
	    const diffMinutesApproximation = this.approximate(diffMinutes, 15);
	    if (diffDays >= 1) {
	      return '';
	    }
	    if (diffMinutes >= 60) {
	      const approximationMark = diffMinutes !== diffMinutesApproximation ? '~' : '';
	      return `${approximationMark}${this.formatDecimal(diffHours)} ${main_core.Loc.getMessage('EC_HOUR_SHORT')}`;
	    }
	    return `${this.formatDecimal(diffMinutes)} ${main_core.Loc.getMessage('EC_MINUTE_SHORT')}`;
	  }
	  formatDecimal(decimal) {
	    return `${decimal}`.replace('.', ',');
	  }
	  approximateFloor(value, accuracy) {
	    return Math.floor(value / accuracy) * accuracy;
	  }
	  approximate(value, accuracy) {
	    return Math.round(value / accuracy) * accuracy;
	  }
	}

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	class Reminder extends main_core_events.EventEmitter {
	  // 9.00

	  constructor(params) {
	    super();
	    this.defaultReminderTime = 540;
	    this.fullDayMode = false;
	    this.extendedMode = true;
	    this.selectedValues = [];
	    this.controlList = {};
	    this.viewMode = false;
	    this.DOM = {};
	    this.changedByUser = false;
	    this.setEventNamespace('BX.Calendar.Controls.Reminder');
	    this.values = this.getValues();
	    this.id = params.id || 'reminder-' + Math.round(Math.random() * 1000000);
	    this.zIndex = params.zIndex || 3200;
	    this.rawValuesMap = new Map();
	    this.viewMode = params.viewMode === true;
	    this.changeCallack = params.changeCallack;
	    this.showPopupCallBack = params.showPopupCallBack;
	    this.hidePopupCallBack = params.hidePopupCallBack;
	    this.create(params);
	    this.setValue(params.selectedValues || []);
	    this.bindEventHandlers();
	  }
	  create(params) {
	    if (main_core.Type.isElementNode(params.wrap)) {
	      this.DOM.wrap = params.wrap;
	    }
	    if (!main_core.Type.isElementNode(this.DOM.wrap)) {
	      return;
	    }
	    main_core.Dom.addClass(this.DOM.wrap, 'calendar-notification-values');
	    if (!this.viewMode) {
	      this.DOM.addButton = this.DOM.wrap.appendChild(main_core.Tag.render(_t || (_t = _`
				<span class="calendar-notification-add-btn-wrap">
					<span class="calendar-notification-text">${0}</span>
					<span class="calendar-notification-btn-container calendar-notification-btn-add">
						<span class="calendar-notification-icon"></span>
					</span>
				</span>`), main_core.Loc.getMessage('EC_REMIND1_ADD')));
	    }
	  }
	  bindEventHandlers() {
	    if (main_core.Type.isDomNode(this.DOM.addButton)) {
	      main_core.Event.bind(this.DOM.addButton, 'click', this.showPopup.bind(this));
	    }
	    if (main_core.Type.isDomNode(this.DOM.wrap)) {
	      main_core.Event.bind(this.DOM.wrap, 'click', this.handleClick.bind(this));
	    }
	  }
	  getValues() {
	    let values = [];
	    if (!this.fullDayMode) {
	      values = values.concat([{
	        value: 0,
	        label: main_core.Loc.getMessage("EC_REMIND1_0"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_0")
	      }, {
	        value: 5,
	        label: main_core.Loc.getMessage("EC_REMIND1_5"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_5")
	      }, {
	        value: 10,
	        label: main_core.Loc.getMessage("EC_REMIND1_10"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_10")
	      }, {
	        value: 15,
	        label: main_core.Loc.getMessage("EC_REMIND1_15"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_15")
	      }, {
	        value: 20,
	        label: main_core.Loc.getMessage("EC_REMIND1_20"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_20")
	      }, {
	        value: 30,
	        label: main_core.Loc.getMessage("EC_REMIND1_30"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_30")
	      }, {
	        value: 60,
	        label: main_core.Loc.getMessage("EC_REMIND1_60"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_60")
	      }, {
	        value: 120,
	        label: main_core.Loc.getMessage("EC_REMIND1_120"),
	        shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_120")
	      }
	      //{value: 1440, label: Loc.getMessage("EC_REMIND1_1440"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_1440")},
	      //{value: 2880, label: Loc.getMessage("EC_REMIND1_2880"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_2880")}
	      ]);
	    }

	    if (this.extendedMode) {
	      values = values.concat([{
	        id: 'time-menu-day-0',
	        label: main_core.Loc.getMessage("EC_REMIND1_DAY_0"),
	        dataset: {
	          mode: 'time-menu',
	          daysBefore: 0,
	          time: this.defaultReminderTime
	        }
	      }, {
	        id: 'time-menu-day-1',
	        label: main_core.Loc.getMessage("EC_REMIND1_DAY_1"),
	        dataset: {
	          mode: 'time-menu',
	          daysBefore: 1,
	          time: this.defaultReminderTime
	        }
	      }, {
	        id: 'time-menu-day-2',
	        label: main_core.Loc.getMessage("EC_REMIND1_DAY_2"),
	        dataset: {
	          mode: 'time-menu',
	          daysBefore: 2,
	          time: this.defaultReminderTime
	        }
	      }, {
	        id: 'custom',
	        label: main_core.Loc.getMessage("EC_REMIND1_CUSTOM"),
	        dataset: {
	          mode: 'custom'
	        }
	      }]);
	    }
	    return values;
	  }
	  setValue(reminderList, emitChanges = true, changedByUser = true) {
	    this.selectedValues.forEach(value => {
	      this.removeValue(value, emitChanges);
	    });
	    if (main_core.Type.isArray(reminderList)) {
	      reminderList.forEach(value => {
	        this.addValue(value, emitChanges, changedByUser);
	      }, this);
	    }
	  }
	  getSelectedValues() {
	    return this.selectedValues;
	  }
	  getSelectedRawValues() {
	    let selectedRawValues = [];
	    this.selectedValues.forEach(value => {
	      selectedRawValues.push(this.rawValuesMap.get(value) || value);
	    });
	    return selectedRawValues;
	  }
	  setFullDayMode(fullDayMode) {
	    if (fullDayMode !== this.fullDayMode) {
	      this.fullDayMode = fullDayMode;
	      this.values = this.getValues();
	    }
	  }
	  showPopup(params = {}) {
	    const _this = this;
	    const menuItems = [];
	    this.values.forEach(item => {
	      if (item.mode === 'time-menu' || item.mode === 'custom' || !BX.util.in_array(item.value, this.selectedValues)) {
	        let menuItem = {};
	        if (item.dataset && item.dataset.mode === 'time-menu') {
	          menuItem.id = item.id;
	          let defaultReminderTime = calendar_util.Util.getTimeByInt(this.defaultReminderTime);
	          menuItem.text = item.label.replace('#TIME#', calendar_util.Util.formatTime(defaultReminderTime.hour, defaultReminderTime.min));
	          menuItem.dataset = BX.util.objectMerge({
	            type: 'submenu-list',
	            value: this.defaultReminderTime
	          }, item.dataset);
	          menuItem.items = this.getSubmenuTimeValues(menuItem, item.label, params);
	          menuItem.onclick = function () {
	            return function () {
	              _this.addValue({
	                before: item.dataset.daysBefore,
	                time: item.dataset.time
	              });
	              BX.defer(function () {
	                _this.reminderMenu.close();
	              }, _this)();
	              if (main_core.Type.isFunction(params.addValueCallback)) {
	                params.addValueCallback();
	              }
	            };
	          }();
	        } else if (item.dataset && item.dataset.mode === 'custom') {
	          menuItem.id = 'custom';
	          menuItem.text = item.label;
	          menuItem.items = [{
	            id: 'tmp',
	            text: 'tmp'
	          }];
	        } else {
	          menuItem.text = item.label;
	          menuItem.onclick = function (value, mode) {
	            return () => {
	              _this.addValue(value);
	              _this.reminderMenu.close();
	              if (main_core.Type.isFunction(params.addValueCallback)) {
	                params.addValueCallback();
	              }
	            };
	          }(item.value, item.mode);
	        }
	        menuItems.push(menuItem);
	      }
	    }, this);
	    this.reminderMenu = main_popup.MenuManager.create(this.id, params.bindTarget || this.DOM.addButton, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 9,
	      angle: true,
	      cacheable: false
	    });
	    let adjustSubmenuPopup = this.adjustSubmenuPopup.bind(this);
	    let closeSubmenuPopup = this.closeSubmenuPopup.bind(this);
	    main_core_events.EventEmitter.subscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
	    main_core_events.EventEmitter.subscribe('BX.Main.Popup:onClose', closeSubmenuPopup);
	    this.reminderMenu.popupWindow.subscribe('onClose', () => {
	      main_core_events.EventEmitter.unsubscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
	      main_core_events.EventEmitter.unsubscribe('BX.Main.Popup:onClose', closeSubmenuPopup);
	    });
	    this.reminderMenu.show();
	  }
	  getSubmenuTimeValues(parentItem, parentItemMessage, params) {
	    let menuItems = [];
	    Reminder.getTimeValueList(60).forEach(function (menuItem) {
	      menuItems.push({
	        id: 'time-' + menuItem.value,
	        dataset: {
	          value: menuItem.value,
	          daysBefore: parentItem.dataset.daysBefore
	        },
	        text: menuItem.label,
	        onclick: function (e, item) {
	          let time = calendar_util.Util.getTimeByInt(item.dataset.value);
	          let parentMenuItem = this.reminderMenu.getMenuItem(parentItem.id);
	          if (parentMenuItem) {
	            parentMenuItem.setText(parentItemMessage.replace('#TIME#', calendar_util.Util.formatTime(time.hour, time.min)));
	          }
	          this.addValue({
	            time: item.dataset.value,
	            before: item.dataset.daysBefore
	          });
	          BX.defer(function () {
	            this.reminderMenu.close();
	          }, this)();
	          if (main_core.Type.isFunction(params.addValueCallback)) {
	            params.addValueCallback();
	          }
	        }.bind(this)
	      });
	    }, this);
	    return menuItems;
	  }
	  addValue(value, emitChanges = true, changedByUser = true) {
	    let item;
	    const formattedValue = Reminder.formatValue(value);
	    if (main_core.Type.isPlainObject(value) && value.count) {
	      value = parseInt(formattedValue);
	    }
	    if (main_core.Type.isPlainObject(value) && !this.selectedValues.includes(formattedValue)) {
	      if (main_core.Type.isInteger(parseInt(value.before)) && main_core.Type.isInteger(parseInt(value.time))) {
	        item = this.DOM.wrap.appendChild(main_core.Tag.render(_t2 || (_t2 = _`
					<span class="calendar-reminder-item">
						<span class="calendar-reminder-item-title">
							${0}
						</span>
					</span>`), Reminder.getReminderLabel(value)));
	        if (!this.viewMode) {
	          item.appendChild(main_core.Tag.render(_t3 || (_t3 = _`<span class="calendar-reminder-clear-icon" data-bxc-value="${0}"/>`), formattedValue));
	        }
	      } else if (value.type === 'date' && main_core.Type.isDate(value.value)) {
	        item = this.DOM.wrap.appendChild(main_core.Tag.render(_t4 || (_t4 = _`<span class="calendar-reminder-item">
					<span class="calendar-reminder-item-title">
						${0}
					</span>
				</span>`), calendar_util.Util.formatDateUsable(value.value) + ' ' + calendar_util.Util.formatTime(value.value)));
	        if (!this.viewMode) {
	          item.appendChild(main_core.Tag.render(_t5 || (_t5 = _`<span class="calendar-reminder-clear-icon" data-bxc-value="${0}"/>`), formattedValue));
	        }
	      }
	      this.selectedValues.push(formattedValue);
	      this.controlList[formattedValue] = item;
	      this.rawValuesMap.set(formattedValue, value);
	    } else if (parseInt(value) >= 0 && !this.selectedValues.includes(formattedValue)) {
	      for (let i = 0; i < this.values.length; i++) {
	        if (this.values[i].value === parseInt(value)) {
	          item = this.DOM.wrap.appendChild(main_core.Tag.render(_t6 || (_t6 = _`
					<span class="calendar-reminder-item">
						<span class="calendar-reminder-item-title">
							${0}
						</span>
					</span>`), this.values[i].shortLabel || this.values[i].label));
	          if (!this.viewMode) {
	            item.appendChild(main_core.Tag.render(_t7 || (_t7 = _`<span class="calendar-reminder-clear-icon" data-bxc-value="${0}"/>`), formattedValue));
	          }
	          this.selectedValues.push(formattedValue);
	          this.controlList[formattedValue] = item;
	          this.rawValuesMap.set(formattedValue, value);
	          break;
	        }
	      }
	      if (item === undefined) {
	        item = this.DOM.wrap.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-reminder-item'
	          },
	          text: Reminder.getText(value)
	        }));
	        if (!this.viewMode) {
	          item.appendChild(main_core.Dom.create('SPAN', {
	            props: {
	              className: 'calendar-reminder-clear-icon'
	            },
	            events: {
	              click: function () {
	                this.removeValue(value);
	              }.bind(this)
	            }
	          }));
	        }
	        this.selectedValues.push(value);
	        this.controlList[value] = item;
	        this.rawValuesMap.set(value, value);
	      }
	    }
	    if (this.changeCallack) {
	      this.changeCallack(this.selectedValues);
	    }
	    if (emitChanges) {
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          values: this.selectedValues
	        }
	      }));
	    }
	    this.changedByUser = emitChanges && changedByUser;
	    if (main_core.Type.isElementNode(this.DOM.addButton)) {
	      this.DOM.wrap.appendChild(this.DOM.addButton);
	    }
	  }
	  removeValue(value, emitChanges = true) {
	    if (this.controlList[value] && main_core.Type.isDomNode(this.controlList[value])) {
	      main_core.Dom.remove(this.controlList[value]);
	    }
	    this.selectedValues = BX.util.deleteFromArray(this.selectedValues, BX.util.array_search(value, this.selectedValues));
	    if (this.changeCallack) {
	      this.changeCallack(this.selectedValues);
	    }
	    if (emitChanges) {
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          values: this.selectedValues
	        }
	      }));
	      this.changedByUser = true;
	    }
	  }
	  static getTimeValueList(mode = 30) {
	    if (!Reminder.timeValueList) {
	      Reminder.timeValueList = [];
	      let i;
	      for (i = 0; i < 24; i++) {
	        Reminder.timeValueList.push({
	          value: i * 60,
	          label: calendar_util.Util.formatTime(i, 0)
	        });
	        if (mode <= 30) {
	          Reminder.timeValueList.push({
	            value: i * 60 + 30,
	            label: calendar_util.Util.formatTime(i, 30)
	          });
	        }
	      }
	    }
	    return Reminder.timeValueList;
	  }
	  handleClick(e) {
	    let target = e.target || e.srcElement;
	    let remValue = target.getAttribute('data-bxc-value');
	    if (!main_core.Type.isNull(remValue) && this.selectedValues.includes(remValue)) {
	      this.removeValue(remValue);
	    }
	    if (main_core.Dom.hasClass(target, 'calendar-reminder-item-title')) {
	      this.showPopup({
	        bindTarget: target,
	        addValueCallback: () => {
	          const removeIcon = target.parentNode.querySelector('.calendar-reminder-clear-icon');
	          if (main_core.Type.isElementNode(removeIcon) && !main_core.Type.isNull(removeIcon.getAttribute('data-bxc-value'))) {
	            this.removeValue(removeIcon.getAttribute('data-bxc-value'));
	          }
	        }
	      });
	    }
	  }
	  static inputCalendarClosePopupHandler(e) {
	    BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	  }
	  static getReminderLabel(value) {
	    let label = '';
	    value.before = parseInt(value.before);
	    value.time = parseInt(value.time);
	    if (main_core.Type.isInteger(value.before) && main_core.Type.isInteger(value.time) && [0, 1, 2].includes(value.before)) {
	      let time = calendar_util.Util.getTimeByInt(value.time);
	      label = main_core.Loc.getMessage('EC_REMIND1_DAY_' + value.before + '_SHORT').replace('#TIME#', calendar_util.Util.formatTime(time.hour, time.min));
	    }
	    return label;
	  }

	  // Used to scroll into view and highlight default item in time menu
	  adjustSubmenuPopup(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      let submenuPopup = event.getTarget();
	      if (submenuPopup instanceof main_popup.Popup) {
	        if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId())) {
	          this.adjustTimeSubmenuPopup(submenuPopup);
	        } else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId())) {
	          this.adjustCalendarSubmenuPopup(submenuPopup);
	        }
	      }
	    }
	  }
	  closeSubmenuPopup(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      let submenuPopup = event.getTarget();
	      if (submenuPopup instanceof main_popup.Popup) {
	        if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId())) ; else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId())) {
	          let layout = submenuPopup.bindElement;
	          let textNode = layout.querySelector('.menu-popup-item-text');
	          if (main_core.Type.isDomNode(textNode)) {
	            main_core.Dom.clean(textNode);
	            textNode.innerHTML = main_core.Loc.getMessage("EC_REMIND1_CUSTOM");
	          }
	        }
	      }
	    }
	  }
	  adjustTimeSubmenuPopup(popup) {
	    let selectedMenuItem = popup.getContentContainer().querySelector('span[data-value="' + this.defaultReminderTime + '"]');
	    if (main_core.Type.isDomNode(selectedMenuItem)) {
	      setTimeout(() => {
	        popup.getContentContainer().scrollTop = parseInt(selectedMenuItem.offsetTop) - 10;
	        main_core.Dom.addClass(selectedMenuItem, 'menu-popup-item-open');
	      }, 50);
	    }
	  }
	  adjustCalendarSubmenuPopup(popup) {
	    let layout = popup.bindElement;
	    let textNode = layout.querySelector('.menu-popup-item-text');
	    if (main_core.Type.isDomNode(textNode)) {
	      main_core.Dom.clean(textNode);
	      const input = textNode.appendChild(main_core.Tag.render(_t8 || (_t8 = _`<input id="inp-${0}" type="text" class="calendar-field calendar-field-datetime" value="" autocomplete="off" placeholder="${0}"/>`), Math.round(Math.random() * 100000), main_core.Loc.getMessage('EC_REMIND1_CUSTOM_PLACEHOLDER')));
	      const calendarControl = BX.calendar.get();

	      // Hacks for BX.calendar - it works as singleton and has troubles with using inside menupopups
	      // We trying to reinitialize it everytime
	      if (calendarControl.popup) {
	        calendarControl.popup.destroy();
	        calendarControl.popup = null;
	        calendarControl._current_layer = null;
	        calendarControl._layers = {};
	      }
	      if (calendarControl.popup_month) {
	        calendarControl.popup_month.destroy();
	        calendarControl.popup_month = null;
	      }
	      if (calendarControl.popup_year) {
	        calendarControl.popup_year.destroy();
	        calendarControl.popup_year = null;
	      }
	      calendarControl.Show({
	        node: input,
	        value: calendar_util.Util.formatDateTime(calendar_util.Util.getUsableDateTime(new Date())),
	        field: input,
	        bTime: true,
	        bHideTime: false
	      });
	      let calendarPopup = calendarControl.popup;
	      calendarPopup.cacheable = false;
	      if (calendarPopup && calendarPopup.popupContainer) {
	        let calendarWrap = calendarPopup.popupContainer.querySelector('.bx-calendar');
	        if (main_core.Type.isDomNode(calendarWrap)) {
	          popup.contentContainer.appendChild(calendarWrap);
	        }
	        calendarPopup.close();
	        main_popup.MenuManager.destroy(calendarPopup.uniquePopupId);
	      }
	      main_core.Event.bind(input, 'change', () => {
	        let value = input.value,
	          dateValue = calendar_util.Util.parseDate(value);
	        if (main_core.Type.isDate(dateValue)) {
	          this.addValue({
	            type: 'date',
	            value: dateValue
	          });
	          this.reminderMenu.close();
	        }
	      });
	    }
	  }
	  setViewMode(viewMode) {
	    this.viewMode = viewMode;
	    if (this.viewMode) {
	      main_core.Dom.addClass(this.DOM.wrap, 'calendar-reminder-readonly');
	      if (main_core.Type.isElementNode(this.DOM.addButton)) {
	        this.DOM.addButton.style.display = 'none';
	      }
	    } else {
	      main_core.Dom.removeClass(this.DOM.wrap, 'calendar-reminder-readonly');
	    }
	  }
	  wasChangedByUser() {
	    return this.changedByUser;
	  }
	  static getText(value) {
	    let tempValue = value,
	      dividers = [60, 24],
	      //list of time dividers
	      messageCodes = ['EC_REMIND1_MIN_COUNT', 'EC_REMIND1_HOUR_COUNT', 'EC_REMIND1_DAY_COUNT'],
	      result = '';
	    for (let i = 0; i < messageCodes.length; i++) {
	      if (tempValue < dividers[i] || i === dividers.length) {
	        result = main_core.Loc.getMessage(messageCodes[i]).toString();
	        result = result.replace('\#COUNT\#', tempValue.toString());
	        break;
	      } else {
	        tempValue = Math.ceil(tempValue / dividers[i]);
	      }
	    }
	    return result;
	  }
	  static formatValue(remindValue) {
	    if (main_core.Type.isPlainObject(remindValue) && main_core.Type.isInteger(parseInt(remindValue.before)) && main_core.Type.isInteger(parseInt(remindValue.time))) {
	      return 'daybefore|' + remindValue.before + '|' + remindValue.time;
	    } else if (main_core.Type.isPlainObject(remindValue) && main_core.Type.isDate(remindValue.value)) {
	      return 'date|' + calendar_util.Util.formatDateTime(remindValue.value);
	    } else if (main_core.Type.isPlainObject(remindValue) && remindValue.type) {
	      if (remindValue.type === 'min') {
	        return remindValue.count.toString();
	      }
	      if (remindValue.type === 'hour') {
	        return (parseInt(remindValue.count) * 60).toString();
	      }
	      if (remindValue.type === 'day') {
	        return (parseInt(remindValue.count) * 60 * 24).toString();
	      }
	    }
	    return remindValue.toString();
	  }
	}
	Reminder.timeValueList = null;

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1;
	class Location {
	  constructor(params) {
	    this.datesRange = [];
	    this.viewMode = false;
	    this.params = params;
	    this.id = params.id || 'location-' + Math.round(Math.random() * 1000000);
	    this.zIndex = params.zIndex || 3100;
	    this.DOM = {
	      wrapNode: params.wrap
	    };
	    this.roomsManager = params.roomsManager || null;
	    this.locationAccess = params.locationAccess || false;
	    this.disabled = !params.richLocationEnabled;
	    this.value = {
	      type: '',
	      text: '',
	      value: ''
	    };
	    this.isLoading = false;
	    this.inlineEditModeEnabled = params.inlineEditModeEnabled;
	    this.meetingRooms = params.iblockMeetingRoomList || [];
	    Location.setMeetingRoomList(params.iblockMeetingRoomList);
	    Location.setLocationList(params.locationList);
	    if (!this.disabled) {
	      this.default = this.setDefaultRoom(params.locationList) || '';
	    }
	    this.create();
	    this.setViewMode(params.viewMode === true);
	    this.processValue();
	    this.setCategoryManager();
	    this.setValuesDebounced = BX.debounce(this.setValues.bind(this), 100);
	    this.updateAccessibilityDebounce = main_core.Runtime.debounce(this.updateAccessibility.bind(this), 100);
	    Location.instances.push(this);
	  }
	  create() {
	    this.DOM.wrapNode.style.display = 'flex';
	    this.DOM.inputWrap = this.DOM.wrapNode.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-field-block"></div>
		`)));
	    this.DOM.alertIconLocation = main_core.Tag.render(_t2$1 || (_t2$1 = _$1`
			<div class="ui-alert-icon-danger calendar-location-alert-icon" data-hint-no-icon="Y" data-hint="${0}">
			<i></i>
			</div>
		`), main_core.Loc.getMessage('EC_LOCATION_OVERFLOW'));
	    if (this.inlineEditModeEnabled) {
	      this.DOM.inlineEditLinkWrap = this.DOM.wrapNode.appendChild(main_core.Tag.render(_t3$1 || (_t3$1 = _$1`
				<div class="calendar-field-place-link calendar-location-readonly">${0}
				</div>`), this.DOM.inlineEditLink = main_core.Tag.render(_t4$1 || (_t4$1 = _$1`
					<span class="calendar-text-link">${0}</span>`), main_core.Loc.getMessage('EC_REMIND1_ADD'))));
	      this.DOM.inputWrap.style.display = 'none';
	      main_core.Event.bind(this.DOM.inlineEditLinkWrap, 'click', () => {
	        this.displayInlineEditControls();
	        this.selectContol.showPopup();
	      });
	    }
	    this.DOM.inputWrapInner = this.DOM.inputWrap.appendChild(main_core.Tag.render(_t5$1 || (_t5$1 = _$1`
				<div class="calendar-event-location-input-wrap-inner">
				</div>`)));
	    this.DOM.input = this.DOM.inputWrapInner.appendChild(main_core.Dom.create('INPUT', {
	      attrs: {
	        name: this.params.inputName || '',
	        placeholder: this.disabled ? main_core.Loc.getMessage('EC_LOCATION_PLACEHOLDER_LOCKED') : main_core.Loc.getMessage('EC_LOCATION_PLACEHOLDER'),
	        type: 'text',
	        autocomplete: this.disabled ? 'on' : 'off'
	      },
	      props: {
	        className: 'calendar-field calendar-field-select'
	      },
	      style: {
	        paddingRight: 25 + 'px',
	        minWidth: 300 + 'px',
	        maxWidth: 300 + 'px'
	      }
	    }));
	    if (this.disabled) {
	      main_core.Dom.addClass(this.DOM.wrapNode, 'locked');
	      this.DOM.lockIcon = main_core.Tag.render(_t6$1 || (_t6$1 = _$1`
				<div class="calendar-lock-icon"></div>
			`));
	      main_core.Event.bind(this.DOM.lockIcon, 'click', () => {
	        top.BX.UI.InfoHelper.show('limit_office_calendar_location');
	      });
	      main_core.Dom.append(this.DOM.lockIcon, this.DOM.inputWrapInner);
	    }
	  }
	  setValues() {
	    var _this$categoryManager, _this$selectContol;
	    this.addLocationRemoveButton();
	    if (!this.categoryManagerFromDB) {
	      this.setValuesDebounced();
	      return;
	    }
	    this.prohibitClick();
	    let menuItemList = [],
	      selectedIndex = false,
	      meetingRooms = Location.getMeetingRoomList(),
	      locationList = Location.getLocationList();
	    const roomList = this.createRoomList(locationList);
	    this.categoriesWithRooms = this == null ? void 0 : (_this$categoryManager = this.categoryManagerFromDB) == null ? void 0 : _this$categoryManager.getCategoriesWithRooms(roomList);
	    if (main_core.Type.isArray(meetingRooms)) {
	      meetingRooms.forEach(function (room) {
	        room.ID = parseInt(room.ID);
	        menuItemList.push({
	          ID: room.ID,
	          label: room.NAME,
	          labelRaw: room.NAME,
	          value: room.ID,
	          capacity: 0,
	          type: 'mr'
	        });
	        if (this.value.type === 'mr' && parseInt(this.value.value) === room.ID) {
	          selectedIndex = menuItemList.length - 1;
	        }
	      }, this);
	      if (menuItemList.length > 0) {
	        menuItemList.push({
	          delimiter: true
	        });
	      }
	    }
	    const pushRoomToItemList = room => {
	      room.id = parseInt(room.id);
	      room.location_id = parseInt(room.location_id);
	      const isSelected = parseInt(this.value.value) === parseInt(room.id);
	      menuItemList.push({
	        ID: room.id,
	        LOCATION_ID: room.location_id,
	        label: room.name,
	        capacity: parseInt(room.capacity) || 0,
	        color: room.color,
	        reserved: room.reserved || false,
	        labelRaw: room.name,
	        labelCapacity: this.getCapacityMessage(room.capacity),
	        value: room.id,
	        type: 'calendar',
	        selected: isSelected
	      });
	      if (this.value.type === 'calendar' && isSelected) {
	        selectedIndex = menuItemList.length - 1;
	      }
	    };
	    if (main_core.Type.isObject(this.categoriesWithRooms)) {
	      if (this.categoriesWithRooms.categories.length || this.categoriesWithRooms.default.length) {
	        this.categoriesWithRooms.categories.forEach(category => {
	          if (category.rooms.length) {
	            menuItemList.push({
	              text: category.name,
	              delimiter: true
	            });
	            category.rooms.forEach(room => pushRoomToItemList(room), this);
	          }
	        });
	        if (this.categoriesWithRooms.default.length) {
	          menuItemList.push({
	            text: "\0",
	            className: 'calendar-popup-window-delimiter-default-category',
	            delimiter: true
	          });
	          this.categoriesWithRooms.default.forEach(room => pushRoomToItemList(room), this);
	        }
	        if (this.locationAccess) {
	          this.loadRoomSlider();
	          menuItemList.push({
	            delimiter: true
	          });
	          menuItemList.push({
	            label: main_core.Loc.getMessage('EC_LOCATION_MEETING_ROOM_SET'),
	            callback: this.openRoomsSlider.bind(this)
	          });
	        }
	      } else {
	        if (this.locationAccess) {
	          this.loadRoomSlider();
	          menuItemList.push({
	            label: main_core.Loc.getMessage('EC_ADD_LOCATION'),
	            callback: this.openRoomsSlider.bind(this)
	          });
	        }
	      }
	    }
	    let disabledControl = this.disabled;
	    if (!menuItemList.length) {
	      disabledControl = true;
	    }
	    this.processValue();
	    this.menuItemList = menuItemList;
	    const selectControlCreated = this.selectContol;
	    (_this$selectContol = this.selectContol) != null ? _this$selectContol : this.selectContol = new calendar_controls.SelectInput({
	      input: this.DOM.input,
	      values: menuItemList,
	      valueIndex: selectedIndex,
	      zIndex: this.zIndex,
	      disabled: disabledControl,
	      minWidth: 300,
	      onChangeCallback: () => {
	        const menuItemList = this.menuItemList;
	        main_core_events.EventEmitter.emit('Calendar.LocationControl.onValueChange');
	        let i,
	          value = this.DOM.input.value;
	        this.value = {
	          text: value
	        };
	        for (i = 0; i < menuItemList.length; i++) {
	          if (menuItemList[i].labelRaw === value) {
	            this.value.type = menuItemList[i].type;
	            this.value.value = menuItemList[i].value;
	            Location.setCurrentCapacity(menuItemList[i].capacity);
	            break;
	          }
	        }
	        if (main_core.Type.isFunction(this.params.onChangeCallback)) {
	          this.params.onChangeCallback();
	        }
	        if (this.value.text === '') {
	          this.removeLocationRemoveButton();
	        }
	        this.addLocationRemoveButton();
	        menuItemList.forEach(location => {
	          location.selected = location.value === this.value.value;
	        });
	        this.selectContol.setValueList(menuItemList);
	        this.allowClick();
	      },
	      onPopupShowCallback: () => {
	        if (this.getShouldCheckLocationAccessibility()) {
	          this.checkLocationAccessibility(this.accessibilityParams);
	        }
	      }
	    });
	    this.selectContol.setValueList(menuItemList);
	    this.selectContol.setValue({
	      valueIndex: selectedIndex
	    });
	    this.selectContol.setDisabled(disabledControl);
	    if (!selectControlCreated) {
	      this.setLoading(this.isLoading);
	    }
	    this.allowClick();
	  }
	  processValue() {
	    if (this.value) {
	      this.DOM.input.value = this.value.str || '';
	      if (this.value.type && (this.value.str === this.getTextLocation(this.value) || this.getTextLocation(this.value) === main_core.Loc.getMessage('EC_LOCATION_EMPTY'))) {
	        this.DOM.input.value = '';
	        this.value = '';
	      }
	      for (const locationListElement of Location.locationList) {
	        if (parseInt(locationListElement.ID) === this.value.room_id) {
	          Location.setCurrentCapacity(parseInt(locationListElement.CAPACITY));
	          break;
	        }
	      }
	    }
	  }
	  setValuesDebounce() {
	    this.setValuesDebounced();
	  }
	  removeValue() {
	    this.setValue(false, false);
	    this.selectContol.onChangeCallback();
	    this.removeLocationRemoveButton();
	  }
	  removeLocationRemoveButton() {
	    if (this.DOM.inputWrap.contains(this.DOM.removeLocationButton)) {
	      this.DOM.inputWrap.removeChild(this.DOM.removeLocationButton);
	    } else if (this.DOM.wrapNode.contains(this.DOM.removeLocationButton)) {
	      this.DOM.wrapNode.removeChild(this.DOM.removeLocationButton);
	    }
	    this.DOM.removeLocationButton = null;
	    if (main_core.Type.isDomNode(this.DOM.inlineEditLink)) {
	      this.displayInlineEditControls();
	    }
	  }
	  addLocationRemoveButton() {
	    var _this$DOM, _this$DOM$inlineEditL;
	    let wrap = this.DOM.inputWrap;
	    if (((_this$DOM = this.DOM) == null ? void 0 : (_this$DOM$inlineEditL = _this$DOM.inlineEditLinkWrap) == null ? void 0 : _this$DOM$inlineEditL.style.display) === '') {
	      wrap = this.DOM.wrapNode;
	    }
	    if ((this.value.value || this.value.str || this.value.text) && !this.viewMode && !this.DOM.removeLocationButton && this.value.text !== '') {
	      this.DOM.removeLocationButton = wrap.appendChild(main_core.Tag.render(_t7$1 || (_t7$1 = _$1`
				<span class="calendar-location-clear-btn-wrap calendar-location-readonly">
					<span class="calendar-location-clear-btn-text">${0}</span>
				</span>`), main_core.Loc.getMessage('EC_LOCATION_CLEAR_INPUT')));
	      main_core.Event.bind(this.DOM.removeLocationButton, 'click', this.removeValue.bind(this));
	    }
	  }
	  isShown() {
	    var _this$selectContol$sh, _this$selectContol2;
	    return (_this$selectContol$sh = (_this$selectContol2 = this.selectContol) == null ? void 0 : _this$selectContol2.shown) != null ? _this$selectContol$sh : false;
	  }
	  setViewMode(viewMode) {
	    this.viewMode = viewMode;
	    if (this.viewMode) {
	      main_core.Dom.addClass(this.DOM.wrapNode, 'calendar-location-readonly');
	    } else {
	      main_core.Dom.removeClass(this.DOM.wrapNode, 'calendar-location-readonly');
	    }
	  }
	  addCapacityAlert() {
	    if (!main_core.Dom.hasClass(this.DOM.input, 'calendar-field-location-select-border')) {
	      main_core.Dom.addClass(this.DOM.input, 'calendar-field-location-select-border');
	    }
	    if (main_core.Type.isDomNode(this.DOM.alertIconLocation)) {
	      calendar_util.Util.initHintNode(this.DOM.alertIconLocation);
	    }
	    setTimeout(() => {
	      this.DOM.inputWrapInner.after(this.DOM.alertIconLocation);
	    }, 200);
	  }
	  removeCapacityAlert() {
	    if (main_core.Dom.hasClass(this.DOM.input, 'calendar-field-location-select-border')) {
	      main_core.Dom.removeClass(this.DOM.input, 'calendar-field-location-select-border');
	    }
	    if (this.DOM.alertIconLocation.parentNode === this.DOM.inputWrap) {
	      main_core.Dom.remove(this.DOM.alertIconLocation);
	    }
	  }
	  getCapacityMessage(capacity) {
	    let suffix;
	    if (capacity % 100 > 10 && capacity % 100 < 20) {
	      suffix = 5;
	    } else {
	      suffix = capacity % 10;
	    }
	    return main_core.Loc.getMessage('EC_LOCATION_CAPACITY_' + suffix, {
	      '#NUM#': capacity
	    });
	  }
	  getShouldCheckLocationAccessibility() {
	    return this.shouldCheckLocationAccessibility;
	  }
	  setShouldCheckLocationAccessibility(shouldCheck) {
	    this.shouldCheckLocationAccessibility = shouldCheck;
	  }
	  checkLocationAccessibility(params) {
	    this.accessibilityParams = params;
	    this.setLoading(true);
	    this.updateAccessibilityDebounce();
	  }
	  updateAccessibility() {
	    const params = this.accessibilityParams;
	    this.getLocationAccessibility(params.from, params.to).then(() => {
	      const timezone = params.timezone && params.timezone !== '' ? params.timezone : calendar_util.Util.getUserSettings().timezoneName;
	      const timezoneOffset = calendar_util.Util.getTimeZoneOffset(timezone) * 60 * 1000;
	      const fromTs = new Date(params.from.getTime() + timezoneOffset).getTime();
	      let toTs = new Date(params.to.getTime() + timezoneOffset).getTime();
	      if (params.fullDay) {
	        toTs += Location.DAY_LENGTH;
	      }
	      for (const index in Location.locationList) {
	        Location.locationList[index].reserved = false;
	        let roomId = Location.locationList[index].ID;
	        for (const date of this.datesRange) {
	          if (main_core.Type.isUndefined(Location.accessibility[date]) || !main_core.Type.isArrayFilled(Location.accessibility[date][roomId])) {
	            continue;
	          }
	          for (const event of Location.accessibility[date][roomId]) {
	            if (parseInt(event.PARENT_ID) === parseInt(params.currentEventId)) {
	              continue;
	            }
	            let eventTimezoneOffset = 0;
	            if (event.DT_SKIP_TIME === 'N') {
	              eventTimezoneOffset = calendar_util.Util.getTimeZoneOffset(event.TZ_FROM) * 60 * 1000;
	            }
	            const eventTsFrom = new Date(calendar_util.Util.parseDate(event.DATE_FROM).getTime() + eventTimezoneOffset).getTime();
	            let eventTsTo = new Date(calendar_util.Util.parseDate(event.DATE_TO).getTime() + eventTimezoneOffset).getTime();
	            if (event.DT_SKIP_TIME === 'Y') {
	              eventTsTo += Location.DAY_LENGTH;
	            }
	            if (eventTsFrom < toTs && eventTsTo > fromTs) {
	              Location.locationList[index].reserved = true;
	              break;
	            }
	          }
	          if (Location.locationList[index].reserved) {
	            break;
	          }
	        }
	      }
	      this.setValues();
	      this.setLoading(false);
	    });
	  }
	  setLoading(isLoading) {
	    var _this$selectContol3;
	    this.isLoading = isLoading;
	    (_this$selectContol3 = this.selectContol) == null ? void 0 : _this$selectContol3.setLoading(isLoading);
	  }
	  getLocationAccessibility(from, to) {
	    return new Promise(resolve => {
	      this.datesRange = Location.getDatesRange(from, to);
	      let isCheckedAccessibility = true;
	      for (let date of this.datesRange) {
	        if (main_core.Type.isUndefined(Location.accessibility[date])) {
	          isCheckedAccessibility = false;
	          break;
	        }
	      }
	      if (!isCheckedAccessibility) {
	        BX.ajax.runAction('calendar.api.locationajax.getLocationAccessibility', {
	          data: {
	            datesRange: this.datesRange,
	            locationList: Location.locationList
	          }
	        }).then(response => {
	          for (let date of this.datesRange) {
	            Location.accessibility[date] = response.data[date];
	          }
	          resolve(Location.accessibility, this.datesRange);
	        }, response => {
	          resolve(response.errors);
	        });
	      } else {
	        resolve(Location.accessibility, this.datesRange);
	      }
	    });
	  }
	  static handlePull(params) {
	    var _entry$EXDATE;
	    const entry = params.fields;
	    if (!entry.DATE_FROM || !entry.DATE_TO) {
	      return;
	    }
	    let dateFrom = calendar_util.Util.parseDate(entry.DATE_FROM);
	    let dateTo = calendar_util.Util.parseDate(entry.DATE_TO);
	    let datesRange = Location.getDatesRange(dateFrom, dateTo);
	    const excludedDates = (_entry$EXDATE = entry.EXDATE) == null ? void 0 : _entry$EXDATE.split(';');
	    if (main_core.Type.isArrayFilled(excludedDates)) {
	      datesRange.push(excludedDates.pop());
	    }
	    for (let date of datesRange) {
	      if (Location.accessibility[date]) {
	        delete Location.accessibility[date];
	      }
	    }
	    Location.instances.forEach(instance => {
	      if (instance.isShown()) {
	        instance.checkLocationAccessibility(instance.accessibilityParams);
	      } else {
	        instance.setShouldCheckLocationAccessibility(true);
	      }
	    });
	  }
	  loadRoomSlider() {
	    this.setRoomsManager();
	  }
	  openRoomsSlider() {
	    this.getRoomsInterface().then(function (RoomsInterface) {
	      if (!this.roomsInterface) {
	        this.roomsInterface = new RoomsInterface({
	          calendarContext: null,
	          readonly: false,
	          roomsManager: this.roomsManagerFromDB,
	          categoryManager: this.categoryManagerFromDB,
	          isConfigureList: true
	        });
	      }
	      this.roomsInterface.show();
	    }.bind(this));
	  }
	  getTextValue(value) {
	    if (!value) {
	      value = this.value;
	    }
	    let res = value.str || value.text || '';
	    if (value && value.type === 'mr') {
	      res = 'ECMR_' + value.value + (value.mrevid ? '_' + value.mrevid : '');
	    } else if (value && value.type === 'calendar') {
	      res = 'calendar_' + value.value + (value.room_event_id ? '_' + value.room_event_id : '');
	    }
	    return res;
	  }
	  getValue() {
	    return this.value;
	  }
	  setValue(value, debounced = true) {
	    if (main_core.Type.isPlainObject(value)) {
	      this.value.text = value.text || '';
	      this.value.type = value.type || '';
	      this.value.value = value.value || '';
	    } else {
	      this.value = Location.parseStringValue(value);
	    }
	    if (debounced) {
	      this.setValuesDebounce();
	    } else {
	      this.setValues();
	    }
	    if (this.inlineEditModeEnabled) {
	      let textLocation = this.getTextLocation(this.value);
	      this.DOM.inlineEditLink.innerHTML = main_core.Text.encode(textLocation || main_core.Loc.getMessage('EC_REMIND1_ADD'));
	      if (textLocation) {
	        this.addLocationRemoveButton();
	      }
	    }
	  }

	  // parseLocation
	  static parseStringValue(str) {
	    if (!main_core.Type.isString(str)) {
	      str = '';
	    }
	    let res = {
	      type: false,
	      value: false,
	      str: str
	    };
	    if (str.substr(0, 5) === 'ECMR_') {
	      res.type = 'mr';
	      let value = str.split('_');
	      if (value.length >= 2) {
	        if (!isNaN(parseInt(value[1])) && parseInt(value[1]) > 0) {
	          res.value = res.mrid = parseInt(value[1]);
	        }
	        if (!isNaN(parseInt(value[2])) && parseInt(value[2]) > 0) {
	          res.mrevid = parseInt(value[2]);
	        }
	      }
	    } else if (str.substr(0, 9) === 'calendar_') {
	      res.type = 'calendar';
	      let value = str.split('_');
	      if (value.length >= 2) {
	        if (!isNaN(parseInt(value[1])) && parseInt(value[1]) > 0) {
	          res.value = res.room_id = parseInt(value[1]);
	        }
	        if (!isNaN(parseInt(value[2])) && parseInt(value[2]) > 0) {
	          res.room_event_id = parseInt(value[2]);
	        }
	      }
	    }
	    return res;
	  }
	  getTextLocation(location) {
	    let value = main_core.Type.isPlainObject(location) ? location : Location.parseStringValue(location),
	      i,
	      str = value.str;
	    if (main_core.Type.isArray(this.meetingRooms) && value.type === 'mr') {
	      str = main_core.Loc.getMessage('EC_LOCATION_EMPTY');
	      for (i = 0; i < this.meetingRooms.length; i++) {
	        if (parseInt(value.value) === parseInt(this.meetingRooms[i].ID)) {
	          str = this.meetingRooms[i].NAME;
	          break;
	        }
	      }
	    }
	    if (main_core.Type.isArray(Location.locationList) && value.type === 'calendar') {
	      str = main_core.Loc.getMessage('EC_LOCATION_EMPTY');
	      for (i = 0; i < Location.locationList.length; i++) {
	        if (parseInt(value.value) === parseInt(Location.locationList[i].ID)) {
	          str = Location.locationList[i].NAME;
	          break;
	        }
	      }
	    }
	    return str;
	  }
	  static setLocationList(locationList) {
	    if (main_core.Type.isArray(locationList)) {
	      Location.locationList = locationList;
	      this.sortLocationList();
	    }
	  }
	  static sortLocationList() {
	    Location.locationList.sort((a, b) => {
	      if (a.NAME.toLowerCase() > b.NAME.toLowerCase()) {
	        return 1;
	      }
	      if (a.NAME.toLowerCase() < b.NAME.toLowerCase()) {
	        return -1;
	      }
	      return 0;
	    });
	  }
	  static getLocationList() {
	    return Location.locationList;
	  }
	  static setMeetingRoomList(meetingRoomList) {
	    if (main_core.Type.isArray(meetingRoomList)) {
	      Location.meetingRoomList = meetingRoomList;
	    }
	  }
	  static getMeetingRoomList() {
	    return Location.meetingRoomList;
	  }
	  static setLocationAccessibility(accessibility) {
	    Location.accessibility = accessibility;
	  }
	  static getLocationAccessibility() {
	    return Location.accessibility;
	  }
	  static setCurrentCapacity(capacity) {
	    Location.currentRoomCapacity = capacity;
	  }
	  static getCurrentCapacity() {
	    return Location.currentRoomCapacity || 0;
	  }
	  displayInlineEditControls() {
	    this.DOM.inlineEditLinkWrap.style.display = 'none';
	    this.DOM.inputWrap.style.display = '';
	    this.addLocationRemoveButton();
	  }
	  setDefaultRoom(locationList) {
	    if (this.roomsManager && !calendar_roomsmanager.RoomsManager.isEmpty(locationList)) {
	      this.activeRooms = this.roomsManager.getRoomsInfo().active;
	      if (!calendar_roomsmanager.RoomsManager.isEmpty(this.activeRooms)) {
	        const activeRoomId = this.activeRooms[0];
	        for (const locationListElement of locationList) {
	          if (parseInt(locationListElement.ID) === activeRoomId) {
	            Location.setCurrentCapacity(parseInt(locationListElement.CAPACITY));
	            return 'calendar_' + activeRoomId;
	          }
	        }
	      } else {
	        Location.setCurrentCapacity(parseInt(locationList[0].CAPACITY));
	        return 'calendar_' + locationList[0].ID;
	      }
	    } else {
	      return '';
	    }
	  }
	  getRoomsInterface() {
	    return new Promise(resolve => {
	      const bx = BX.Calendar.Util.getBX();
	      const extensionName = 'calendar.rooms';
	      bx.Runtime.loadExtension(extensionName).then(() => {
	        if (bx.Calendar.Rooms.RoomsInterface) {
	          resolve(bx.Calendar.Rooms.RoomsInterface);
	        } else {
	          console.error('Extension ' + extensionName + ' not found');
	          resolve(bx.Calendar.Rooms.RoomsInterface);
	        }
	      });
	    });
	  }
	  getRoomsManager() {
	    return new Promise(resolve => {
	      const bx = BX.Calendar.Util.getBX();
	      const extensionName = 'calendar.roomsmanager';
	      bx.Runtime.loadExtension(extensionName).then(() => {
	        if (bx.Calendar.RoomsManager) {
	          resolve(bx.Calendar.RoomsManager);
	        } else {
	          console.error('Extension ' + extensionName + ' not found');
	          resolve(bx.Calendar.RoomsManager);
	        }
	      });
	    });
	  }
	  getRoomsManagerData() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.locationajax.getRoomsManagerData').then(response => {
	        this.roomsManagerFromDB = new calendar_roomsmanager.RoomsManager({
	          sections: response.data.sections,
	          rooms: response.data.rooms
	        }, {
	          locationAccess: response.data.config.locationAccess,
	          hiddenSections: response.data.config.hiddenSections,
	          type: response.data.config.type,
	          ownerId: response.data.config.ownerId,
	          userId: response.data.config.userId,
	          new_section_access: response.data.config.defaultSectionAccess,
	          sectionAccessTasks: response.data.config.sectionAccessTasks,
	          showTasks: response.data.config.showTasks,
	          locationContext: this,
	          //for updating list of locations in event creation menu
	          accessNames: response.data.config.accessNames
	        });
	        resolve(response.data);
	      },
	      // Failure
	      response => {
	        console.error('Extension not found');
	        resolve(response.data);
	      });
	    });
	  }
	  createRoomList(locationList) {
	    return locationList.map(location => {
	      return new calendar_roomsmanager.RoomsSection(location);
	    });
	  }
	  setRoomsManager() {
	    if (!this.roomsManagerFromDB) {
	      this.getRoomsManager().then(this.getRoomsManagerData());
	    }
	  }
	  getCategoryManager() {
	    return new Promise(resolve => {
	      const bx = BX.Calendar.Util.getBX();
	      const extensionName = 'calendar.categorymanager';
	      bx.Runtime.loadExtension(extensionName).then(() => {
	        if (bx.Calendar.CategoryManager) {
	          resolve(bx.Calendar.CategoryManager);
	        } else {
	          console.error('Extension ' + extensionName + ' not found');
	          resolve(bx.Calendar.CategoryManager);
	        }
	      });
	    });
	  }
	  getCategoryManagerData() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.locationajax.getCategoryManagerData').then(response => {
	        this.categoryManagerFromDB = new calendar_categorymanager.CategoryManager({
	          categories: response.data.categories
	        }, {
	          perm: response.data.permissions,
	          locationContext: this //for updating list of locations in event creation menu
	        });

	        resolve(response.data);
	      },
	      // Failure
	      response => {
	        console.error('Extension not found');
	        resolve(response.data);
	      });
	    });
	  }
	  setCategoryManager() {
	    if (!this.categoryManagerFromDB) {
	      this.getCategoryManager().then(this.getCategoryManagerData());
	    }
	  }
	  prohibitClick() {
	    if (this.DOM.inlineEditLinkWrap && !main_core.Dom.hasClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly')) {
	      main_core.Dom.addClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly');
	    }
	    if (this.DOM.removeLocationButton && !main_core.Dom.hasClass(this.DOM.removeLocationButton, 'calendar-location-readonly')) {
	      main_core.Dom.addClass(this.DOM.removeLocationButton, 'calendar-location-readonly');
	    }
	  }
	  allowClick() {
	    if (this.DOM.inlineEditLinkWrap && main_core.Dom.hasClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly')) {
	      main_core.Dom.removeClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly');
	    }
	    if (this.DOM.removeLocationButton && main_core.Dom.hasClass(this.DOM.removeLocationButton, 'calendar-location-readonly')) {
	      main_core.Dom.removeClass(this.DOM.removeLocationButton, 'calendar-location-readonly');
	    }
	  }
	  static getDateInFormat(date) {
	    return ('0' + date.getDate()).slice(-2) + '.' + ('0' + (date.getMonth() + 1)).slice(-2) + '.' + date.getFullYear();
	  }
	  static getDatesRange(from, to) {
	    const fromDate = new Date(from.getTime() - calendar_util.Util.getDayLength());
	    const toDate = new Date(to.getTime() + calendar_util.Util.getDayLength());
	    let startDate = fromDate.setHours(0, 0, 0, 0);
	    let finishDate = toDate.setHours(0, 0, 0, 0);
	    let result = [];
	    while (startDate <= finishDate) {
	      result.push(Location.getDateInFormat(new Date(startDate)));
	      startDate += Location.DAY_LENGTH;
	    }
	    return result;
	  }
	}
	Location.locationList = [];
	Location.meetingRoomList = [];
	Location.currentRoomCapacity = 0;
	Location.accessibility = [];
	Location.DAY_LENGTH = 86400000;
	Location.instances = [];

	class UserSelector {
	  constructor(params = {}) {
	    this.params = params;
	    this.id = params.id || 'user_selector_' + Math.round(Math.random() * 1000000);
	    this.zIndex = params.zIndex || 3100;
	    this.DOM = {
	      wrapNode: params.wrapNode
	    };
	    this.destinationInputName = params.inputName || 'EVENT_DESTINATION';
	    if (main_core.Type.isArray(this.params.itemsSelected) && this.params.itemsSelected.length) {
	      this.params.itemsSelected = this.convertAttendeesCodes(this.params.itemsSelected);
	    }
	    this.create();
	  }
	  create() {
	    let id = this.id;
	    this.DOM.socnetDestinationWrap = this.DOM.wrapNode.appendChild(main_core.Dom.create('DIV', {
	      props: {
	        className: 'event-grid-dest-wrap'
	      },
	      events: {
	        click: e => {
	          BX.SocNetLogDestination.openDialog(id);
	        }
	      }
	    }));
	    this.socnetDestinationItems = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	      props: {
	        className: ''
	      },
	      events: {
	        click: function (e) {
	          var targ = e.target || e.srcElement;
	          if (targ.className === 'feed-event-del-but')
	            // Delete button
	            {
	              top.BX.SocNetLogDestination.deleteItem(targ.getAttribute('data-item-id'), targ.getAttribute('data-item-type'), id);
	              e.preventDefault();
	              e.stopPropagation();
	            }
	        },
	        mouseover: function (e) {
	          var targ = e.target || e.srcElement;
	          if (targ.className === 'feed-event-del-but')
	            // Delete button
	            BX.addClass(targ.parentNode, 'event-grid-dest-hover');
	        },
	        mouseout: function (e) {
	          var targ = e.target || e.srcElement;
	          if (targ.className === 'feed-event-del-but')
	            // Delete button
	            BX.removeClass(targ.parentNode, 'event-grid-dest-hover');
	        }
	      }
	    }));
	    this.socnetDestinationInputWrap = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	      props: {
	        className: 'feed-add-destination-input-box'
	      }
	    }));
	    this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(main_core.Dom.create('INPUT', {
	      props: {
	        id: id + '-inp',
	        className: 'feed-add-destination-inp'
	      },
	      attrs: {
	        value: '',
	        type: 'text'
	      },
	      events: {
	        keydown: function (e) {
	          return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
	            formName: id,
	            inputId: id + '-inp'
	          });
	        },
	        keyup: function (e) {
	          return top.BX.SocNetLogDestination.searchHandler(e, {
	            formName: id,
	            inputId: id + '-inp',
	            linkId: 'event-grid-dest-add-link',
	            sendAjax: true
	          });
	        }
	      }
	    }));
	    this.socnetDestinationLink = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	      html: this.params.addLinkMessage || BX.message('EC_DESTINATION_ADD_USERS'),
	      props: {
	        id: id + '-link',
	        className: 'feed-add-destination-link'
	      },
	      events: {
	        keydown: function (e) {
	          return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
	            formName: id,
	            inputId: id + '-inp'
	          });
	        },
	        keyup: function (e) {
	          return top.BX.SocNetLogDestination.searchHandler(e, {
	            formName: id,
	            inputId: id + '-inp',
	            linkId: 'event-grid-dest-add-link',
	            sendAjax: true
	          });
	        }
	      }
	    }));

	    // if (this.params.itemsSelected && !this.checkItemsSelected(
	    // 	this.params.items,
	    // 	this.params.itemsLast,
	    // 	this.params.itemsSelected,
	    // 	BX.proxy(this.init, this)
	    // ))
	    // {
	    // 	return;
	    // }

	    this.init();
	  }
	  init() {
	    if (!this.socnetDestinationInput || !this.DOM.socnetDestinationWrap || !this.params.items) {
	      return;
	    }
	    if (this.params.selectGroups === false) {
	      this.params.items.groups = {};
	      this.params.items.department = {};
	      this.params.items.sonetgroups = {};
	    }
	    if (this.params.selectUsers === false) {
	      this.params.items.users = {};
	      this.params.items.groups = {};
	      this.params.items.department = {};
	    }
	    BX.SocNetLogDestination.init({
	      name: this.id,
	      searchInput: this.socnetDestinationInput,
	      extranetUser: false,
	      userSearchArea: 'I',
	      bindMainPopup: {
	        node: this.DOM.socnetDestinationWrap,
	        offsetTop: '5px',
	        offsetLeft: '15px'
	      },
	      bindSearchPopup: {
	        node: this.DOM.socnetDestinationWrap,
	        offsetTop: '5px',
	        offsetLeft: '15px'
	      },
	      callback: {
	        select: this.selectCallback.bind(this),
	        unSelect: this.unSelectCallback.bind(this),
	        openDialog: this.openDialogCallback.bind(this),
	        closeDialog: this.closeDialogCallback.bind(this),
	        openSearch: this.openDialogCallback.bind(this),
	        closeSearch: () => {
	          this.closeDialogCallback(true);
	        }
	      },
	      items: this.params.items,
	      itemsLast: this.params.itemsLast,
	      itemsSelected: this.params.itemsSelected,
	      departmentSelectDisable: this.params.selectGroups === false
	    });
	  }
	  closeAll() {
	    if (top.BX.SocNetLogDestination.isOpenDialog()) {
	      top.BX.SocNetLogDestination.closeDialog();
	    }
	    top.BX.SocNetLogDestination.closeSearch();
	  }
	  selectCallback(item, type) {
	    var type1 = type,
	      prefix = 'S';
	    if (type === 'sonetgroups') {
	      prefix = 'SG';
	    } else if (type === 'groups') {
	      prefix = 'UA';
	      type1 = 'all-users';
	    } else if (type === 'users') {
	      prefix = 'U';
	    } else if (type === 'department') {
	      prefix = 'DR';
	    }
	    this.socnetDestinationItems.appendChild(main_core.Dom.create("span", {
	      attrs: {
	        'data-id': item.id
	      },
	      props: {
	        className: "event-grid-dest event-grid-dest-" + type1
	      },
	      children: [main_core.Dom.create("input", {
	        attrs: {
	          type: 'hidden',
	          name: this.destinationInputName + '[' + prefix + '][]',
	          value: item.id
	        }
	      }), main_core.Dom.create("span", {
	        props: {
	          className: "event-grid-dest-text"
	        },
	        html: item.name
	      }), main_core.Dom.create("span", {
	        props: {
	          className: "feed-event-del-but"
	        },
	        attrs: {
	          'data-item-id': item.id,
	          'data-item-type': type
	        }
	      })]
	    }));
	    BX.onCustomEvent('OnDestinationAddNewItem', [item]);
	    this.socnetDestinationInput.value = '';
	    this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	  }
	  unSelectCallback(item, type, search) {
	    var elements = BX.findChildren(this.socnetDestinationItems, {
	      attribute: {
	        'data-id': item.id
	      }
	    }, true);
	    if (elements != null) {
	      for (var j = 0; j < elements.length; j++) {
	        BX.remove(elements[j]);
	      }
	    }
	    BX.onCustomEvent('OnDestinationUnselect');
	    this.socnetDestinationInput.value = '';
	    this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	  }
	  openDialogCallback() {
	    BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
	    BX.style(this.socnetDestinationLink, 'display', 'none');
	    BX.focus(this.socnetDestinationInput);
	  }
	  closeDialogCallback(cleanInputValue) {
	    if (!top.BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0) {
	      BX.style(this.socnetDestinationInputWrap, 'display', 'none');
	      BX.style(this.socnetDestinationLink, 'display', 'inline-block');
	      if (cleanInputValue === true) this.socnetDestinationInput.value = '';

	      // Disable backspace
	      if (top.BX.SocNetLogDestination.backspaceDisable || top.BX.SocNetLogDestination.backspaceDisable != null) BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);
	      BX.bind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable = function (e) {
	        if (e.keyCode === 8) {
	          e.preventDefault();
	          return false;
	        }
	      });
	      setTimeout(function () {
	        BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);
	        top.BX.SocNetLogDestination.backspaceDisable = null;
	      }, 5000);
	    }
	  }
	  getCodes() {
	    var inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
	      codes = [],
	      i;
	    for (i = 0; i < inputsList.length; i++) {
	      codes.push(inputsList[i].value);
	    }
	    return codes;
	  }
	  getAttendeesCodes() {
	    var inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
	      values = [],
	      i;
	    for (i = 0; i < inputsList.length; i++) {
	      values.push(inputsList[i].value);
	    }
	    return this.convertAttendeesCodes(values);
	  }
	  convertAttendeesCodes(values) {
	    let attendeesCodes = {};
	    if (main_core.Type.isArray(values)) {
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
	  setValue(value) {
	    if (this.socnetDestinationItems) {
	      main_core.Dom.clean(this.socnetDestinationItems);
	    }
	    if (main_core.Type.isArray(value)) {
	      this.params.itemsSelected = this.convertAttendeesCodes(value);
	    }
	    this.init();
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$2;
	class ColorSelector extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    this.LINE_MODE = 'line';
	    this.SELECTOR_MODE = 'selector';
	    this.VIEW_MODE = 'view';
	    this.setEventNamespace('BX.Calendar.Controls.ColorSelector');
	    this.id = params.id || 'color-select-' + Math.round(Math.random() * 1000000);
	    this.defaultColors = calendar_util.Util.getDefaultColorList();
	    this.colors = [];
	    this.zIndex = 3100;
	    this.mode = params.mode || this.LINE_MODE;
	    this.DOM = {
	      wrap: params.wrap
	    };
	    this.create();
	    this.setViewMode(params.viewMode || false);
	  }
	  create() {
	    if (this.mode === this.LINE_MODE) {
	      for (let i = 0; i < this.defaultColors.length; i++) {
	        this.colors.push({
	          color: this.defaultColors[i],
	          node: this.DOM.wrap.appendChild(main_core.Tag.render(_t$2 || (_t$2 = _$2`
						<li class="calendar-field-colorpicker-color-item" data-bx-calendar-color="${0}" style="background-color: ${0}">
							<span class="calendar-field-colorpicker-color"></span>
						</li>
					`), this.defaultColors[i], this.defaultColors[i]))
	        });
	      }
	      this.DOM.customColorNode = this.DOM.wrap.appendChild(main_core.Tag.render(_t2$2 || (_t2$2 = _$2`
				<li class="calendar-field-colorpicker-color-item" style="background-color: transparent; width: 0">
					<span class="calendar-field-colorpicker-color"></span>
				</li>`)));
	      this.DOM.customColorLink = this.DOM.wrap.appendChild(main_core.Tag.render(_t3$2 || (_t3$2 = _$2`
				<li class="calendar-field-colorpicker-color-item-more">
					<span class="calendar-field-colorpicker-color-item-more-link">${0}</span>
				</li>
			`), main_core.Loc.getMessage('EC_COLOR')));
	      main_core.Event.bind(this.DOM.customColorLink, 'click', () => {
	        if (!this.colorPickerPopup) {
	          this.colorPickerPopup = new BX.ColorPicker({
	            bindElement: this.DOM.customColorLink,
	            onColorSelected: this.setValue.bind(this),
	            popupOptions: {
	              zIndex: this.zIndex
	            }
	          });
	        }
	        this.colorPickerPopup.open();
	      });
	      main_core.Event.bind(this.DOM.wrap, 'click', this.handleColorClick.bind(this));
	    } else if (this.mode === this.SELECTOR_MODE) {
	      this.DOM.colorIcon = this.DOM.wrap.appendChild(main_core.Tag.render(_t4$2 || (_t4$2 = _$2`
				<div style="background-color: #000;" class="calendar-field-select-icon"></div>
			`)));
	      main_core.Event.bind(this.DOM.wrap, 'click', this.openPopup.bind(this));
	    } else if (this.mode === this.VIEW_MODE) {
	      this.DOM.colorIcon = this.DOM.wrap.appendChild(main_core.Tag.render(_t5$2 || (_t5$2 = _$2`
				<div style="background-color: #000;" class="calendar-field-select-icon"></div>
			`)));
	    }
	  }
	  handleColorClick(e) {
	    if (this.viewMode) {
	      return;
	    }
	    let target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.wrap);
	    if (target && target.getAttribute) {
	      let value = target.getAttribute('data-bx-calendar-color');
	      if (value !== null) {
	        this.setValue(value);
	      }
	    }
	  }
	  setValue(color, emitChanges = true) {
	    if (this.viewMode) {
	      return;
	    }
	    this.activeColor = color;
	    if (this.mode === this.LINE_MODE) {
	      if (this.DOM.activeColorNode) {
	        main_core.Dom.removeClass(this.DOM.activeColorNode, 'active');
	      }
	      if (!BX.util.in_array(this.activeColor, this.defaultColors) && this.activeColor) {
	        main_core.Dom.attr(this.DOM.customColorNode, 'data-bx-calendar-color', this.activeColor);
	        this.DOM.customColorNode.style.backgroundColor = this.activeColor;
	        this.DOM.customColorNode.style.width = '';
	        this.DOM.activeColorNode = this.DOM.customColorNode;
	        main_core.Dom.addClass(this.DOM.activeColorNode, 'active');
	      }
	      let i;
	      for (i = 0; i < this.colors.length; i++) {
	        if (this.colors[i].color === this.activeColor) {
	          this.DOM.activeColorNode = this.colors[i].node;
	          main_core.Dom.addClass(this.DOM.activeColorNode, 'active');
	          break;
	        }
	      }
	    } else if (this.mode === this.SELECTOR_MODE || this.mode === this.VIEW_MODE) {
	      if (this.DOM.colorIcon) {
	        this.DOM.colorIcon.style.backgroundColor = this.activeColor;
	      }
	      if (this.viewMode) {
	        this.DOM.wrap.style.backgroundColor = this.activeColor;
	      }
	    }
	    if (emitChanges) {
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          value: this.activeColor
	        }
	      }));
	    }
	  }
	  getValue() {
	    return this.activeColor;
	  }
	  openPopup() {
	    if (this.viewMode) {
	      return;
	    }
	    if (this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown()) {
	      return this.popup.close();
	    }
	    let i,
	      menuItems = [],
	      icon;
	    this.defaultColors.forEach(color => {
	      menuItems.push({
	        text: color,
	        color: color,
	        className: 'calendar-add-popup-color-menu-item',
	        onclick: (color => {
	          return () => {
	            this.setValue(color);
	            this.popup.close();
	          };
	        })(color)
	      });
	    });
	    this.popup = main_popup.MenuManager.create(this.id, this.DOM.colorIcon, menuItems, {
	      className: 'calendar-color-popup-wrap',
	      width: 162,
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 52,
	      angle: true,
	      cacheable: false
	    });
	    this.popup.show();

	    // Paint round icons for section menu
	    for (i = 0; i < this.popup.menuItems.length; i++) {
	      if (this.popup.menuItems[i].layout.item) {
	        icon = this.popup.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
	        if (main_core.Type.isDomNode(icon)) {
	          icon.style.backgroundColor = this.popup.menuItems[i].color;
	        }
	      }
	    }
	    this.popup.popupWindow.angle.element.style.left = '6px';
	  }
	  setViewMode(viewMode) {
	    this.viewMode = viewMode;
	    if (this.viewMode) {
	      main_core.Dom.addClass(this.DOM.wrap, 'calendar-colorpicker-readonly');
	      main_core.Event.unbind(this.DOM.wrap, 'click', this.openPopup.bind(this));
	    }
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$2,
	  _t7$2,
	  _t8$1,
	  _t9,
	  _t10,
	  _t11;
	class SectionSelector {
	  constructor(params) {
	    this.id = params.id || 'section-select-' + Math.round(Math.random() * 1000000);
	    this.sectionList = params.sectionList;
	    this.sectionGroupList = params.sectionGroupList;
	    this.selectCallback = params.selectCallback;
	    this.openPopupCallback = params.openPopupCallback;
	    this.closePopupCallback = params.closePopupCallback;
	    this.getCurrentSection = params.getCurrentSection;
	    this.defaultCalendarType = params.defaultCalendarType;
	    this.defaultOwnerId = parseInt(params.defaultOwnerId) || 0;
	    this.zIndex = params.zIndex || 3200;
	    this.mode = params.mode; // full|compact|textselect
	    this.DOM = {
	      outerWrap: params.outerWrap
	    };
	    this.create();
	    this.initEventHandlers();
	  }
	  create() {
	    if (this.mode === 'textselect') {
	      this.DOM.select = this.DOM.outerWrap;
	      this.DOM.selectImageWrap = this.DOM.select.appendChild(main_core.Tag.render(_t$3 || (_t$3 = _$3`<span class="calendar-field-choice-calendar-img"></span>`)));
	      this.DOM.selectInnerText = this.DOM.select.appendChild(main_core.Tag.render(_t2$3 || (_t2$3 = _$3`<span class="calendar-field-choice-calendar-name">${0}</span>`), main_core.Text.encode(main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle())));
	    } else if (this.mode === 'location') {
	      this.DOM.select = this.DOM.outerWrap;
	      this.DOM.selectImageWrap = this.DOM.select.appendChild(main_core.Tag.render(_t3$3 || (_t3$3 = _$3`<span class="calendar-field-choice-calendar-img"></span>`)));
	      this.DOM.selectInnerText = this.DOM.select.appendChild(main_core.Tag.render(_t4$3 || (_t4$3 = _$3`<span class="calendar-field-choice-calendar-name calendar-field-choice-calendar-name-location">${0}</span>`), main_core.Text.encode(main_core.Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle())));
	    } else {
	      this.DOM.select = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t5$3 || (_t5$3 = _$3`<div class="calendar-field calendar-field-select"></div>`)));
	      this.DOM.innerValue = this.DOM.select.appendChild(main_core.Tag.render(_t6$2 || (_t6$2 = _$3`
				<div class="calendar-field-select-icon" style="background-color: ${0}"></div>`), this.getCurrentColor()));
	      if (this.mode === 'full') {
	        this.DOM.selectInnerText = this.DOM.select.appendChild(main_core.Tag.render(_t7$2 || (_t7$2 = _$3`<span>${0}</span>`), main_core.Text.encode(this.getCurrentTitle())));
	      }
	    }
	  }
	  initEventHandlers() {
	    main_core.Event.bind(this.DOM.select, 'click', this.openPopup.bind(this));
	  }
	  openPopup() {
	    if (this.viewMode) {
	      return false;
	    }
	    if (this.sectionMenu && this.sectionMenu.popupWindow && this.sectionMenu.popupWindow.isShown()) {
	      return this.sectionMenu.close();
	    }
	    const menuItems = [];
	    const sectionIdList = [];
	    const sectionList = this.getSectionList();
	    let i;
	    if (main_core.Type.isArray(this.sectionGroupList)) {
	      this.sectionGroupList.forEach(sectionGroup => {
	        let filteredList;
	        if (sectionGroup.belongsToView) {
	          filteredList = sectionList.filter(section => {
	            return SectionSelector.getSectionType(section) === this.defaultCalendarType && SectionSelector.getSectionOwner(section) === this.defaultOwnerId;
	          }, this);
	        } else if (sectionGroup.type === 'user' || sectionGroup.type === 'location') {
	          filteredList = sectionList.filter(section => {
	            return SectionSelector.getSectionType(section) === 'user' && SectionSelector.getSectionOwner(section) === sectionGroup.ownerId;
	          });
	        } else if (sectionGroup.type === 'company') {
	          filteredList = sectionList.filter(section => {
	            return SectionSelector.getSectionType(section) === 'company_calendar' || SectionSelector.getSectionType(section) === 'calendar_company' || SectionSelector.getSectionType(section) === sectionGroup.type;
	          });
	        } else {
	          filteredList = sectionList.filter(section => {
	            return SectionSelector.getSectionType(section) === sectionGroup.type;
	          });
	        }
	        filteredList = filteredList.filter(section => {
	          const id = parseInt(section.id || section.ID);
	          if (sectionIdList.includes(id)) return false;
	          sectionIdList.push(id);
	          return true;
	        });
	        if (filteredList.length > 0) {
	          menuItems.push(new main_popup.MenuItem({
	            text: sectionGroup.title,
	            delimiter: true
	          }));
	          for (let i = 0; i < filteredList.length; i++) {
	            menuItems.push(this.getMenuItem(filteredList[i]));
	          }
	        }
	      });
	    } else {
	      for (i = 0; i < sectionList.length; i++) {
	        menuItems.push(this.getMenuItem(sectionList[i]));
	      }
	    }
	    let offsetLeft = 0;
	    if (this.mode === 'compact') {
	      offsetLeft = 40;
	    } else if (this.mode === 'textselect' || this.mode === 'location') {
	      offsetLeft = 0;
	    }
	    this.sectionMenu = main_popup.MenuManager.create(this.id, this.DOM.select, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: offsetLeft,
	      angle: this.mode === 'compact'
	    });
	    this.sectionMenu.popupWindow.contentContainer.style.overflow = "auto";
	    this.sectionMenu.popupWindow.contentContainer.style.maxHeight = "400px";
	    if (this.mode === 'full') {
	      this.sectionMenu.popupWindow.setWidth(this.DOM.select.offsetWidth - 2);
	      this.sectionMenu.popupWindow.contentContainer.style.overflowX = "hidden";
	    }
	    this.sectionMenu.show();

	    // Paint round icons for section menu
	    for (i = 0; i < this.sectionMenu.menuItems.length; i++) {
	      if (this.sectionMenu.menuItems[i].layout.item) {
	        let icon = this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
	        if (icon) {
	          icon.style.backgroundColor = this.sectionMenu.menuItems[i].color;
	        }
	      }
	    }
	    main_core.Dom.addClass(this.DOM.select, 'active');
	    if (main_core.Type.isFunction(this.openPopupCallback)) {
	      this.openPopupCallback(this);
	    }
	    BX.addCustomEvent(this.sectionMenu.popupWindow, 'onPopupClose', BX.delegate(function () {
	      if (main_core.Type.isFunction(this.openPopupCallback)) {
	        this.closePopupCallback();
	      }
	      main_core.Dom.removeClass(this.DOM.select, 'active');
	      main_popup.MenuManager.destroy(this.id);
	      this.sectionMenu = null;
	    }, this));
	  }
	  getCurrentColor() {
	    return (this.getCurrentSection() || {}).color || false;
	  }
	  getCurrentTitle() {
	    return (this.getCurrentSection() || {}).name || '';
	  }
	  getSectionList() {
	    return this.sectionList.filter(section => {
	      return section.PERM && section.PERM.edit || main_core.Type.isFunction(section.canDo) && section.canDo('edit');
	    });
	  }
	  updateSectionImageNode(section) {
	    if (!main_core.Type.isElementNode(this.DOM.selectImageWrap)) {
	      return;
	    }
	    if (section === undefined) {
	      section = this.sectionList.find(section => {
	        return parseInt(section.id) === parseInt(this.getCurrentSection().id);
	      });
	    }
	    if (section && section.type) {
	      const imageSrc = SectionSelector.getSectionImage(section);
	      let imageNode;
	      if (imageSrc) {
	        imageNode = main_core.Tag.render(_t8$1 || (_t8$1 = _$3`<img class="calendar-field-choice-calendar-img-value" src="${0}">`), encodeURI(imageSrc));
	      } else if (section.type === 'group') {
	        imageNode = main_core.Tag.render(_t9 || (_t9 = _$3`<div class="ui-icon ui-icon-common-user-group"><i></i></div>`));
	      } else if (section.type === 'user') {
	        imageNode = main_core.Tag.render(_t10 || (_t10 = _$3`<div class="ui-icon ui-icon-common-user"><i></i></div>`));
	      } else {
	        imageNode = main_core.Tag.render(_t11 || (_t11 = _$3`<div class="ui-icon ui-icon-common-bitrix24"><i></i></div>`));
	      }
	      main_core.Dom.clean(this.DOM.selectImageWrap);
	      this.DOM.selectImageWrap.appendChild(imageNode);
	    }
	  }
	  getPopup() {
	    return this.sectionMenu;
	  }
	  getMenuItem(sectionItem) {
	    return {
	      html: BX.util.htmlspecialchars(sectionItem.name || sectionItem.NAME),
	      color: sectionItem.color || sectionItem.COLOR,
	      className: 'calendar-add-popup-section-menu-item' + (this.mode === 'full' ? ' section-menu-item-full' : ''),
	      onclick: (section => {
	        return () => {
	          if (main_core.Type.isDomNode(this.DOM.innerValue)) {
	            this.DOM.innerValue.style.backgroundColor = section.color || sectionItem.COLOR;
	          }
	          this.updateSectionImageNode(section);
	          if (main_core.Type.isFunction(this.selectCallback)) {
	            if (!section.color && sectionItem.COLOR) {
	              section.color = sectionItem.COLOR;
	            }
	            if (!section.id && sectionItem.ID) {
	              section.id = sectionItem.ID;
	            }
	            this.selectCallback(section);
	          }
	          this.sectionMenu.close();
	          this.updateValue();
	        };
	      })(sectionItem)
	    };
	  }
	  static getSectionType(section) {
	    return section.type || section.CAL_TYPE;
	  }
	  static getSectionImage(section = {}) {
	    return section.data ? section.data.IMAGE : section.IMAGE || '';
	  }
	  static getSectionOwner(section) {
	    return parseInt(section.OWNER_ID || section.data.OWNER_ID);
	  }
	  updateValue() {
	    if (main_core.Type.isDomNode(this.DOM.innerValue)) {
	      this.DOM.innerValue.style.backgroundColor = this.getCurrentColor();
	    }
	    if (this.mode === 'full') {
	      this.DOM.select.appendChild(main_core.Dom.adjust(this.DOM.selectInnerText, {
	        text: this.getCurrentTitle(),
	        props: {
	          title: this.getCurrentTitle()
	        }
	      }));
	    } else if (this.mode === 'textselect') {
	      this.updateSectionImageNode();
	      this.DOM.select.appendChild(main_core.Dom.adjust(this.DOM.selectInnerText, {
	        props: {
	          title: main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle()
	        },
	        text: main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle()
	      }));
	    } else if (this.mode === 'location') {
	      this.updateSectionImageNode();
	      this.DOM.select.appendChild(main_core.Dom.adjust(this.DOM.selectInnerText, {
	        props: {
	          title: main_core.Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle()
	        },
	        text: main_core.Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle()
	      }));
	    }
	  }
	  setViewMode(viewMode) {
	    this.viewMode = viewMode;
	    if (this.viewMode) {
	      main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
	      if (this.DOM.outerWrap !== this.DOM.select) {
	        main_core.Dom.removeClass(this.DOM.select, 'calendar-field-select');
	        main_core.Dom.addClass(this.DOM.select, 'calendar-section-selector-readonly');
	      }
	    } else {
	      main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
	    }
	  }
	}

	class RepeatSelector {
	  constructor(params) {
	    let formElements = params.rruleType.form.elements;
	    this.getDate = params.getDate;
	    this.previousDate = null;
	    this.DOM = {
	      formElements: formElements,
	      wrap: params.wrap,
	      rruleType: params.rruleType,
	      interval: formElements['EVENT_RRULE[INTERVAL]'],
	      rruleEndsOn: {
	        never: formElements['rrule_endson'][0],
	        count: formElements['rrule_endson'][1],
	        until: formElements['rrule_endson'][2]
	      },
	      count: formElements['EVENT_RRULE[COUNT]'],
	      until: formElements['EVENT_RRULE[UNTIL]']
	    };
	    this.viewMode = false;
	    this.create();
	  }
	  create() {
	    BX.bind(this.DOM.rruleType, 'change', () => {
	      this.changeType(this.DOM.rruleType.value);
	    });
	    BX.bind(this.DOM.until, 'click', e => {
	      calendar_controls.DateTimeControl.showInputCalendar(e);
	      this.DOM.rruleEndsOn.until.checked = true;
	    });
	    BX.bind(this.DOM.count, 'click', () => {
	      this.DOM.rruleEndsOn.count.checked = true;
	    });
	  }
	  changeType(type) {
	    this.DOM.rruleType.value = type ? type.toUpperCase() : 'NONE';
	    let rruleType = this.DOM.rruleType.value.toLowerCase();
	    this.DOM.wrap.className = 'calendar-rrule-type-' + rruleType;
	    if (rruleType === 'weekly' && BX.type.isFunction(this.getDate)) {
	      let fromDate = this.getDate();
	      if (BX.type.isDate(fromDate)) {
	        let day = calendar_util.Util.getWeekDayByInd(fromDate.getDay());
	        this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function (input) {
	          if (input.checked && this.previousDay === input.value && this.previousDay !== day) {
	            input.checked = false;
	          } else {
	            input.checked = input.checked || input.value === day;
	          }
	        }, this);
	        this.previousDay = day;
	      }
	    }
	  }
	  setValue(rrule = {}) {
	    if (main_core.Type.isNil(rrule)) {
	      rrule = {};
	    }
	    this.changeType(rrule.FREQ);
	    this.DOM.interval.value = rrule.INTERVAL || 1;
	    if (rrule.COUNT) {
	      this.DOM.rruleEndsOn.count.checked = 'checked';
	      this.DOM.count.value = rrule.COUNT;
	    } else if (rrule['~UNTIL']) {
	      this.DOM.rruleEndsOn.until.checked = 'checked';
	      this.DOM.until.value = rrule['~UNTIL'];
	    } else {
	      this.DOM.rruleEndsOn.never.checked = 'checked';
	    }
	    if (BX.type.isPlainObject(rrule.BYDAY)) {
	      this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function (input) {
	        input.checked = rrule.BYDAY.hasOwnProperty(input.value);
	      }, this);
	    }
	  }
	  getType() {
	    return this.DOM.rruleType.value.toLowerCase();
	  }
	  setViewMode(description) {
	    if (!main_core.Type.isStringFilled(description)) {
	      description = this.DOM.rruleType.options[this.DOM.rruleType.options.selectedIndex].innerText;
	    }
	    main_core.Dom.clean(this.DOM.wrap);
	    this.DOM.wrap.innerText = description.toLowerCase();
	    main_core.Dom.addClass(this.DOM.wrap, 'calendar-field calendar-repeat-selector-readonly');
	  }
	}

	let _$4 = t => t,
	  _t$4;
	var _renderLoader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLoader");
	class SelectInput {
	  constructor(params) {
	    Object.defineProperty(this, _renderLoader, {
	      value: _renderLoader2
	    });
	    this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);
	    this.values = params.values || false;
	    this.input = params.input;
	    this.defaultValue = params.defaultValue || '';
	    this.openTitle = params.openTitle || '';
	    this.className = params.className || '';
	    this.onChangeCallback = main_core.Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : () => {};
	    this.onPopupShowCallback = main_core.Type.isFunction(params.onPopupShowCallback) ? params.onPopupShowCallback : () => {};
	    this.onPopupCloseCallback = main_core.Type.isFunction(params.onPopupCloseCallback) ? params.onPopupCloseCallback : () => {};
	    this.zIndex = params.zIndex || 1200;
	    this.disabled = params.disabled;
	    this.setValue({
	      value: params.value,
	      valueIndex: params.valueIndex
	    });
	    this.curInd = false;
	    this.eventHandlers = {
	      click: this.onClick.bind(this),
	      focus: this.onFocus.bind(this),
	      keydown: this.onKeydown.bind(this),
	      change: this.onChangeCallback
	    };
	    this.bindEventHandlers(this.eventHandlers);
	  }
	  bindEventHandlers(eventHandlers) {
	    for (const [eventName, handler] of Object.entries(eventHandlers)) {
	      main_core.Event.bind(this.input, eventName, handler);
	    }
	  }
	  unbindEventHandlers(eventHandlers) {
	    for (const [eventName, handler] of Object.entries(eventHandlers)) {
	      main_core.Event.unbind(this.input, eventName, handler);
	    }
	  }
	  setValue(params) {
	    this.currentValue = {
	      value: params.value
	    };
	    this.currentValueIndex = params.valueIndex;
	    if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex]) {
	      this.input.value = this.values[this.currentValueIndex].label;
	    }
	  }
	  setValueList(valueList) {
	    this.values = valueList;
	    if (!this.popupMenu) {
	      return;
	    }
	    const menuItemsLength = this.popupMenu.getMenuItems().length;
	    for (let i = 0; i < menuItemsLength; i++) {
	      const popupMenuItem = this.popupMenu.getMenuItems()[0];
	      this.popupMenu.removeMenuItem(popupMenuItem.getId(), {
	        destroyEmptyPopup: false
	      });
	    }
	    for (const menuItem of this.getMenuItems()) {
	      this.popupMenu.addMenuItem(menuItem);
	    }
	    this.updateIconColors();
	  }
	  setDisabled(disabled) {
	    this.disabled = disabled;
	    if (disabled) {
	      this.closePopup();
	    }
	  }
	  getInputValue() {
	    return this.input.value;
	  }
	  showPopup() {
	    if (this.shown || this.disabled) {
	      return;
	    }
	    const menuItems = this.getMenuItems();
	    this.popupMenu = main_popup.MenuManager.create(this.id, this.input, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: -1,
	      events: {
	        onPopupShow: this.onPopupShowCallback,
	        onPopupClose: this.onPopupCloseCallback,
	        onPopupDestroy: this.onPopupCloseCallback
	      }
	    });
	    this.updateLoader();
	    if (!BX.browser.IsFirefox()) {
	      this.popupMenu.popupWindow.setMinWidth(this.input.offsetWidth + 2);
	    }
	    this.popupMenu.popupWindow.setMaxWidth(300);
	    let menuContainer = this.popupMenu.getPopupWindow().getContentContainer();
	    main_core.Dom.addClass(this.popupMenu.layout.menuContainer, 'calendar-select-popup');
	    this.popupMenu.show();
	    const currentItem = this.getCurrentItem();
	    if (currentItem != null && currentItem.layout) {
	      menuContainer.scrollTop = currentItem.layout.item.offsetTop - currentItem.layout.item.offsetHeight - 36 * 3;
	    }
	    this.updateIconColors();
	    BX.addCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', () => {
	      main_popup.MenuManager.destroy(this.id);
	      this.shown = false;
	      this.popupMenu = null;
	    });
	    this.input.select();
	    this.shown = true;
	  }
	  getMenuItems() {
	    const menuItems = [];
	    for (let i = 0; i < this.values.length; i++) {
	      if (this.values[i].delimiter) {
	        menuItems.push(this.values[i]);
	      } else {
	        let htmlTemp;
	        if (this.values[i].reserved) {
	          htmlTemp = `
						<span class="calendar-menu-item-title-with-status">
							${BX.util.htmlspecialchars(this.values[i].label)}
						</span>
						<span class="calendar-menu-item-status --red">
							${main_core.Loc.getMessage('EC_LOCATION_RESERVED')}
						</span>`;
	        } else if (this.values[i].capacity) {
	          htmlTemp = `
						<span class="calendar-menu-item-title-with-status">
					    	${BX.util.htmlspecialchars(this.values[i].label)}
				     	</span>
				     	<span class="calendar-menu-item-capacity">
					    	${BX.util.htmlspecialchars(this.values[i].labelCapacity)}
				    	</span>`;
	        } else {
	          htmlTemp = `
						<span class="calendar-menu-item-title">
							${BX.util.htmlspecialchars(this.values[i].label)}
						</span>`;
	        }
	        const classSelected = this.values[i].selected ? 'calendar-menu-popup-time-selected' : '';
	        if (this.values[i].color) {
	          menuItems.push({
	            id: this.values[i].value,
	            title: this.values[i].label,
	            className: "menu-popup-display-flex calendar-location-popup-menu-item " + classSelected,
	            html: htmlTemp,
	            color: this.values[i].color,
	            onclick: this.values[i].callback || ((value, label) => {
	              return () => {
	                this.input.value = label;
	                this.popupMenu.close();
	                this.onChange();
	              };
	            })(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
	          });
	        } else {
	          const hint = this.values[i].hint ? ' ' + this.values[i].hint : '';
	          menuItems.push({
	            id: this.values[i].value,
	            html: this.values[i].label + hint,
	            title: this.values[i].label,
	            className: "menu-popup-no-icon " + classSelected,
	            onclick: this.values[i].callback || ((value, label) => {
	              return () => {
	                this.input.value = label;
	                this.popupMenu.close();
	                this.onChange(value);
	              };
	            })(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
	          });
	        }
	      }
	    }
	    return menuItems;
	  }
	  updateIconColors() {
	    let popupMenuItems = this.popupMenu.menuItems;
	    for (let i = 0; i < popupMenuItems.length; i++) {
	      if (popupMenuItems[i].layout.item) {
	        let icon = popupMenuItems[i].layout.item.querySelector('.menu-popup-item-icon');
	        if (icon) {
	          icon.style.backgroundColor = popupMenuItems[i].color;
	        }
	      }
	    }
	  }
	  getCurrentItem() {
	    return this.popupMenu.menuItems[this.getCurrentIndex()];
	  }
	  getCurrentIndex() {
	    for (let i = 0; i < this.values.length; i++) {
	      if (this.values[i].selected) {
	        return i;
	      }
	      if (this.currentValue && this.values[i] && i > 0 && this.currentValue.value >= this.values[i - 1].value && this.currentValue.value <= this.values[i].value) {
	        return i;
	      }
	    }
	    return 0;
	  }
	  closePopup() {
	    main_popup.MenuManager.destroy(this.id);
	    this.popupMenu = null;
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
	  onKeydown() {
	    setTimeout(BX.delegate(this.closePopup, this), 50);
	  }
	  onChange(value) {
	    const inputValue = this.input.value;
	    BX.onCustomEvent(this, 'onSelectInputChanged', [this, inputValue]);
	    this.onChangeCallback({
	      value: inputValue,
	      dataValue: value
	    });
	  }
	  setLoading(isLoading) {
	    this.isLoading = isLoading;
	    this.updateLoader();
	  }
	  updateLoader() {
	    if (!this.popupMenu) {
	      return;
	    }
	    if (this.isLoading) {
	      this.popupMenu.getPopupWindow().getPopupContainer().append(babelHelpers.classPrivateFieldLooseBase(this, _renderLoader)[_renderLoader]());
	    } else {
	      var _this$loaderContainer;
	      (_this$loaderContainer = this.loaderContainer) == null ? void 0 : _this$loaderContainer.remove();
	    }
	  }
	  destroy() {
	    this.unbindEventHandlers(this.eventHandlers);
	    if (this.popupMenu) {
	      this.popupMenu.close();
	    }
	    main_popup.MenuManager.destroy(this.id);
	    this.popupMenu = null;
	    this.shown = false;
	  }
	}
	function _renderLoader2() {
	  if (this.loaderContainer) {
	    return this.loaderContainer;
	  }
	  this.loaderContainer = main_core.Tag.render(_t$4 || (_t$4 = _$4`<div style="position: absolute; inset: 0;"></div>`));
	  void new main_loader.Loader().show(this.loaderContainer);
	  return this.loaderContainer;
	}

	class PopupDialog {
	  constructor(params = {}) {
	    this.id = params.id || 'popup-dialog-' + Math.random();
	    this.zIndex = params.zIndex || 3200;
	    this.DOM = {};
	    this.title = '';
	  }
	  create() {
	    this.dialog = new BX.PopupWindow(this.id, null, {
	      overlay: {
	        opacity: 10
	      },
	      autoHide: true,
	      closeByEsc: true,
	      zIndex: this.zIndex,
	      offsetLeft: 0,
	      offsetTop: 0,
	      draggable: true,
	      bindOnResize: false,
	      titleBar: this.getTitle(),
	      closeIcon: {
	        right: "12px",
	        top: "10px"
	      },
	      className: 'bxc-popup-window',
	      buttons: this.getButtons(),
	      content: this.getContent(),
	      events: {}
	    });
	  }
	  getTitle() {
	    return this.title;
	  }
	  getContent() {
	    this.DOM.content = BX.create('DIV');
	    return this.DOM.content;
	  }
	  getButtons() {
	    this.buttons = [];
	    return this.buttons;
	  }
	  show(params) {
	    if (!this.dialog) {
	      this.create();
	    }
	    this.dialog.show();
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$4,
	  _t3$4,
	  _t4$4,
	  _t5$4;
	class ViewSelector extends main_core_events.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.views = [];
	    this.created = false;
	    this.currentValue = null;
	    this.currentViewMode = null;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ViewSelector');
	    if (main_core.Type.isArray(params.views)) {
	      this.views = params.views;
	    }
	    this.zIndex = params.zIndex || 3200;
	    this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);
	    this.create();
	    if (params.currentView) {
	      this.setValue(params.currentView);
	    }
	    if (params.currentViewMode) {
	      this.setViewMode(params.currentViewMode);
	    }
	  }
	  create() {
	    this.DOM.wrap = main_core.Tag.render(_t$5 || (_t$5 = _$5`<div class="calendar-view-switcher-selector"></div>`));
	    this.DOM.selectorText = main_core.Tag.render(_t2$4 || (_t2$4 = _$5`<div class="calendar-view-switcher-text"></div>`));
	    this.DOM.selectorTextInner = this.DOM.selectorText.appendChild(main_core.Tag.render(_t3$4 || (_t3$4 = _$5`<div class="calendar-view-switcher-text-inner"></div>`)));
	    this.DOM.wrap.appendChild(this.DOM.selectorText);
	    this.DOM.wrap.appendChild(main_core.Tag.render(_t4$4 || (_t4$4 = _$5`<div class="calendar-view-switcher-dropdown"></div>`)));
	    main_core.Event.bind(this.DOM.wrap, 'click', this.showPopup.bind(this));
	    this.DOM.viewModeTextInner = this.DOM.selectorText.appendChild(main_core.Tag.render(_t5$4 || (_t5$4 = _$5`<div class="calendar-view-switcher-text-mode-inner" style="display: none;"></div>`)));
	    this.created = true;
	  }
	  getOuterWrap() {
	    if (!this.created) {
	      this.create();
	    }
	    return this.DOM.wrap;
	  }
	  setValue(value) {
	    this.currentValue = this.views.find(function (view) {
	      return value.name === view.name;
	    }, this);
	    if (this.currentValue) {
	      main_core.Dom.adjust(this.DOM.selectorTextInner, {
	        text: this.currentValue.text
	      });
	    }
	  }
	  setViewMode(value) {
	    if (value) {
	      this.currentViewMode = this.views.find(function (view) {
	        return value === view.name && view.type === 'additional';
	      }, this);
	      if (this.currentViewMode) {
	        main_core.Dom.adjust(this.DOM.viewModeTextInner, {
	          text: '(' + this.currentViewMode.text + ')'
	        });
	      }
	      this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
	    }
	  }
	  getMenuItems() {
	    let menuItems = [];
	    this.views.forEach(function (view) {
	      if (view.type === 'base') {
	        menuItems.push({
	          html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
	          //text: view.text,
	          className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
	          onclick: function () {
	            this.emit('onChange', {
	              name: view.name,
	              type: view.type,
	              dataset: view.dataset
	            });
	            this.menuPopup.close();
	          }.bind(this)
	        });
	      }
	    }, this);
	    if (menuItems.length < this.views.length) {
	      menuItems.push({
	        html: '<span>' + main_core.Loc.getMessage('EC_VIEW_MODE_SHOW_BY') + '</span>',
	        className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
	      });
	      this.views.forEach(function (view) {
	        if (view.type === 'additional') {
	          menuItems.push({
	            text: view.text,
	            className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
	            onclick: function () {
	              this.emit('onChange', {
	                name: view.name,
	                type: view.type,
	                dataset: view.dataset
	              });
	              this.menuPopup.close();
	            }.bind(this)
	          });
	        }
	      }, this);
	    }
	    return menuItems;
	  }
	  showPopup() {
	    if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	      return this.menuPopup.close();
	    }
	    this.menuPopup = main_popup.MenuManager.create(this.popupId, this.DOM.selectorText, this.getMenuItems(), {
	      className: "calendar-view-switcher-popup",
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: -3,
	      offsetLeft: this.DOM.selectorText.offsetWidth - 6,
	      angle: true,
	      cacheable: false
	    });
	    this.menuPopup.show();
	  }
	  closePopup() {
	    if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	      this.menuPopup.close();
	    }
	  }
	  show() {
	    this.DOM.wrap.style.display = '';
	  }
	  hide() {
	    this.DOM.wrap.style.display = 'none';
	  }
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$5;
	class LineViewSelector extends main_core_events.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.views = [];
	    this.created = false;
	    this.currentValue = null;
	    this.currentViewMode = null;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.LineViewSelector');
	    if (main_core.Type.isArray(params.views)) {
	      this.views = params.views;
	    }
	    this.viewsMap = new WeakMap();
	    this.zIndex = params.zIndex || 3200;
	    this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);
	    this.create();
	    if (params.currentView) {
	      this.setValue(params.currentView);
	    }
	  }
	  create() {
	    this.DOM.wrap = main_core.Tag.render(_t$6 || (_t$6 = _$6`<div class="calendar-view-switcher-list"></div>`));
	    this.views.forEach(view => {
	      if (view.type === 'base') {
	        this.viewsMap.set(view, {
	          wrap: this.DOM.wrap.appendChild(main_core.Tag.render(_t2$5 || (_t2$5 = _$6`<span 
						class="calendar-view-switcher-list-item"
						onclick="${0}"
					>${0}</span>`), () => {
	            this.emit('onChange', {
	              name: view.name,
	              type: view.type,
	              dataset: view.dataset
	            });
	          }, view.text))
	        });
	      }
	    });
	    this.created = true;
	  }
	  getOuterWrap() {
	    if (!this.created) {
	      this.create();
	    }
	    return this.DOM.wrap;
	  }
	  setValue(value) {
	    this.currentValue = this.views.find(function (view) {
	      return value.name === view.name;
	    }, this);
	    if (this.currentValue) {
	      let viewData = this.viewsMap.get(this.currentValue);
	      let currentActiveWrap = this.DOM.wrap.querySelector('.calendar-view-switcher-list-item-active');
	      if (main_core.Type.isDomNode(currentActiveWrap)) {
	        main_core.Dom.removeClass(currentActiveWrap, 'calendar-view-switcher-list-item-active');
	      }
	      if (main_core.Type.isDomNode(viewData.wrap)) {
	        main_core.Dom.addClass(viewData.wrap, 'calendar-view-switcher-list-item-active');
	      }
	    }
	  }
	  setViewMode(value) {
	    if (value) {
	      this.currentViewMode = this.views.find(function (view) {
	        return value === view.name && view.type === 'additional';
	      }, this);

	      // if (this.currentViewMode)
	      // {
	      // 	Dom.adjust(this.DOM.viewModeTextInner, {text: '(' + this.currentViewMode.text + ')'});
	      // }
	      //this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
	    }
	  }

	  getMenuItems() {
	    let menuItems = [];
	    this.views.forEach(view => {
	      if (view.type === 'base') {
	        menuItems.push({
	          html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
	          className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
	          onclick: () => {
	            this.emit('onChange', {
	              name: view.name,
	              type: view.type,
	              dataset: view.dataset
	            });
	            this.menuPopup.close();
	          }
	        });
	      }
	    });
	    if (menuItems.length < this.views.length) {
	      menuItems.push({
	        html: '<span>' + main_core.Loc.getMessage('EC_VIEW_MODE_SHOW_BY') + '</span>',
	        className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
	      });
	      this.views.forEach(function (view) {
	        if (view.type === 'additional') {
	          menuItems.push({
	            text: view.text,
	            className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
	            onclick: function () {
	              this.emit('onChange', {
	                name: view.name,
	                type: view.type,
	                dataset: view.dataset
	              });
	              this.menuPopup.close();
	            }.bind(this)
	          });
	        }
	      }, this);
	    }
	    return menuItems;
	  }

	  // showPopup()
	  // {
	  // 	this.closePopup();
	  //
	  // 	this.menuPopup = MenuManager.create(
	  // 		this.popupId,
	  // 		this.DOM.selectorText,
	  // 		this.getMenuItems(),
	  // 		{
	  // 			className: "calendar-view-switcher-popup",
	  // 			closeByEsc : true,
	  // 			autoHide : true,
	  // 			zIndex: this.zIndex,
	  // 			offsetTop: -3,
	  // 			offsetLeft: this.DOM.selectorText.offsetWidth - 6,
	  // 			angle: true,
	  // 			cacheable: false
	  // 		}
	  // 	);
	  //
	  // 	this.menuPopup.show();
	  // }
	  //
	  // closePopup()
	  // {
	  // 	if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
	  // 	{
	  // 		return this.menuPopup.close();
	  // 	}
	  // }
	}

	class AddButton extends main_core_events.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.showTasks = false;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.AddButton');
	    this.zIndex = params.zIndex || 3200;
	    this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
	    this.showTasks = params.showTasks;
	    this.addEntryHandler = main_core.Type.isFunction(params.addEntry) ? params.addEntry : null;
	    this.addTaskHandler = main_core.Type.isFunction(params.addTask) ? params.addTask : null;
	    this.create();
	  }
	  create() {
	    this.menuItems = [{
	      text: main_core.Loc.getMessage('EC_EVENT_BUTTON'),
	      onclick: this.addEntry.bind(this)
	    }];
	    if (this.addTaskHandler) {
	      this.menuItems.push({
	        text: main_core.Loc.getMessage('EC_TASK_BUTTON'),
	        onclick: this.addTask.bind(this)
	      });
	    }
	    if (this.menuItems.length > 1) {
	      this.DOM.wrap = main_core.Dom.create("span", {
	        props: {
	          className: "ui-btn-split ui-btn-success"
	        },
	        children: [main_core.Dom.create("button", {
	          props: {
	            className: "ui-btn-main",
	            type: "button"
	          },
	          html: main_core.Loc.getMessage('EC_CREATE'),
	          events: {
	            click: this.addEntry.bind(this)
	          }
	        })]
	      });
	      this.DOM.addButtonExtra = main_core.Dom.create("span", {
	        props: {
	          className: "ui-btn-extra"
	        },
	        events: {
	          click: this.showPopup.bind(this)
	        }
	      });
	      this.DOM.wrap.appendChild(this.DOM.addButtonExtra);
	    } else {
	      this.DOM.wrap = main_core.Dom.create("button", {
	        props: {
	          className: "ui-btn ui-btn-success",
	          type: "button"
	        },
	        html: main_core.Loc.getMessage('EC_CREATE'),
	        events: {
	          click: this.addEntry.bind(this)
	        }
	      });
	    }
	    this.DOM.wrap.setAttribute('data-role', 'addButton');
	  }
	  getWrap() {
	    return this.DOM.wrap;
	  }
	  showPopup() {
	    if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	      return this.menuPopup.close();
	    }
	    this.menuPopup = BX.PopupMenu.create(this.popupId, this.DOM.addButtonExtra, this.menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 15,
	      angle: true
	    });
	    this.menuPopup.show();
	    BX.addCustomEvent(this.menuPopup.popupWindow, 'onPopupClose', function () {
	      BX.PopupMenu.destroy(this.popupId);
	      this.menuPopup = null;
	      this.addBtnMenu = null;
	    }.bind(this));
	  }
	  addEntry() {
	    if (this.addEntryHandler) {
	      this.addEntryHandler();
	    }
	    if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	      this.menuPopup.close();
	    }
	  }
	  addTask() {
	    if (this.addTaskHandler) {
	      this.addTaskHandler();
	    }
	    if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	      this.menuPopup.close();
	    }
	  }
	}

	class MeetingStatusControl extends main_core.Event.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.showTasks = false;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.MeetingStatusControl');
	    this.BX = calendar_util.Util.getBX();
	    if (params.wrap && main_core.Type.isDomNode(params.wrap)) {
	      this.DOM.wrap = params.wrap;
	    } else {
	      throw new Error("The argument \"params.wrap\" must be a DOM node.");
	    }
	    this.id = params.id || 'meeting-status-control-' + Math.round(Math.random() * 10000);
	    this.zIndex = 3100;
	    this.create();
	    this.status = params.currentStatus || null;
	    if (this.status) {
	      this.updateStatus();
	    }
	  }
	  create() {
	    this.acceptBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_Y'),
	      className: 'ui-btn ui-btn-primary',
	      events: {
	        click: this.accept.bind(this)
	      }
	    });
	    this.acceptBtn.renderTo(this.DOM.wrap);
	    this.declineBtn = new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_N'),
	      className: 'ui-btn ui-btn-light-border',
	      events: {
	        click: this.decline.bind(this)
	      }
	    });
	    this.declineBtn.renderTo(this.DOM.wrap);
	  }
	  updateStatus() {
	    if (this.status === 'H') {
	      this.acceptBtn.getContainer().style.display = 'none';
	      this.declineBtn.getContainer().style.display = '';
	      this.declineBtn.setText(main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_OWNER_N'));
	    } else {
	      if (this.status === 'Y') {
	        this.acceptBtn.getContainer().style.display = 'none';
	        this.declineBtn.getContainer().style.display = '';
	      } else if (this.status === 'N') {
	        this.acceptBtn.getContainer().style.display = '';
	        this.declineBtn.getContainer().style.display = 'none';
	      } else {
	        this.acceptBtn.getContainer().style.display = '';
	        this.declineBtn.getContainer().style.display = '';
	      }
	    }
	  }
	  accept() {
	    this.setStatus('Y');
	  }
	  decline() {
	    this.setStatus('N');
	  }
	  setStatus(value, emitEvent = true) {
	    this.status = value;
	    if (this.menuPopup) {
	      this.menuPopup.close();
	    }
	    if (emitEvent) {
	      this.emit('onSetStatus', new main_core.Event.BaseEvent({
	        data: {
	          status: value
	        }
	      }));
	    }
	  }
	}

	let _$7 = t => t,
	  _t$7;
	class ConfirmStatusDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');
	    this.zIndex = 3200;
	    this.id = 'confirm-status-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show() {
	    this.dialog = new main_popup.Popup({
	      id: this.id,
	      titleBar: main_core.Loc.getMessage('EC_DECLINE_REC_EVENT'),
	      content: this.getContent(),
	      className: 'calendar__confirm-dialog',
	      lightShadow: true,
	      maxWidth: 700,
	      minHeight: 120,
	      autoHide: true,
	      closeByEsc: true,
	      draggable: true,
	      closeIcon: true,
	      animation: 'fading-slide',
	      contentBackground: "#fff",
	      overlay: {
	        opacity: 15
	      },
	      cacheable: false
	    });
	    this.dialog.show();
	  }
	  getContent() {
	    const thisEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_DECLINE_ONLY_THIS'),
	      events: {
	        click: () => {
	          this.onDeclineHandler();
	          this.emit('onDecline', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'this'
	            }
	          }));
	        }
	      }
	    });
	    const nextEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_DECLINE_NEXT'),
	      events: {
	        click: () => {
	          this.onDeclineHandler();
	          this.emit('onDecline', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'next'
	            }
	          }));
	        }
	      }
	    });
	    const allEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_DECLINE_ALL'),
	      events: {
	        click: () => {
	          this.onDeclineHandler();
	          this.emit('onDecline', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'all'
	            }
	          }));
	        }
	      }
	    });
	    return main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="calendar__confirm-dialog-content">
				${0}
				${0}
				${0}
			</div>
		`), thisEventButton.render(), nextEventButton.render(), allEventButton.render());
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	  onDeclineHandler() {
	    this.close();
	    const compactForm = calendar_entry.EntryManager.getCompactViewForm();
	    if (compactForm && compactForm.isShown()) {
	      compactForm.close();
	    }
	  }
	}

	let _$8 = t => t,
	  _t$8;
	class ConfirmEditDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ConfirmEditDialog');
	    this.zIndex = 3200;
	    this.id = 'confirm-edit-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show() {
	    this.dialog = new main_popup.Popup({
	      titleBar: main_core.Loc.getMessage('EC_EDIT_REC_EVENT'),
	      content: this.getContent(),
	      className: 'calendar__confirm-dialog',
	      lightShadow: true,
	      maxWidth: 700,
	      minHeight: 120,
	      autoHide: true,
	      closeByEsc: true,
	      draggable: true,
	      closeIcon: true,
	      animation: 'fading-slide',
	      contentBackground: "#fff",
	      overlay: {
	        opacity: 15
	      },
	      cacheable: false
	    });
	    this.dialog.show();
	  }
	  getContent() {
	    const thisEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_ONLY_THIS_EVENT'),
	      events: {
	        click: () => {
	          this.emit('onEdit', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'this'
	            }
	          }));
	          this.close();
	        }
	      }
	    });
	    const nextEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_NEXT'),
	      events: {
	        click: () => {
	          this.emit('onEdit', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'next'
	            }
	          }));
	          this.close();
	        }
	      }
	    });
	    const allEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_ALL'),
	      events: {
	        click: () => {
	          this.emit('onEdit', new main_core_events.BaseEvent({
	            data: {
	              recursionMode: 'all'
	            }
	          }));
	          this.close();
	        }
	      }
	    });
	    return main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div class="calendar__confirm-dialog-content">
				${0}
				${0}
				${0}
			</div>
		`), thisEventButton.render(), nextEventButton.render(), allEventButton.render());
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	}

	let _$9 = t => t,
	  _t$9;
	class ConfirmDeleteDialog {
	  constructor(params = {}) {
	    this.entry = params.entry;
	  }
	  show() {
	    this.dialog = new main_popup.Popup({
	      titleBar: main_core.Loc.getMessage('EC_DEL_REC_EVENT'),
	      content: this.getContent(),
	      className: 'calendar__confirm-dialog',
	      lightShadow: true,
	      maxWidth: 700,
	      minHeight: 120,
	      autoHide: true,
	      closeByEsc: true,
	      draggable: true,
	      closeIcon: true,
	      animation: 'fading-slide',
	      contentBackground: "#fff",
	      overlay: {
	        opacity: 15
	      },
	      cacheable: false
	    });
	    this.dialog.show();
	  }
	  getContent() {
	    const thisEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_ONLY_THIS_EVENT'),
	      events: {
	        click: () => {
	          this.entry.deleteThis();
	          this.close();
	        }
	      }
	    });
	    const nextEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_NEXT'),
	      events: {
	        click: () => {
	          this.entry.deleteNext();
	          this.close();
	        }
	      }
	    });
	    const allEventButton = new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('EC_REC_EV_ALL'),
	      events: {
	        click: () => {
	          this.entry.deleteAll();
	          this.close();
	        }
	      }
	    });
	    return main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div class="calendar__confirm-dialog-content">
				${0}
				${0}
				${0}
			</div>
		`), thisEventButton.render(), nextEventButton.render(), allEventButton.render());
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	}

	let _$a = t => t,
	  _t$a,
	  _t2$6,
	  _t3$5,
	  _t4$5,
	  _t5$5,
	  _t6$3,
	  _t7$3,
	  _t8$2,
	  _t9$1,
	  _t10$1,
	  _t11$1,
	  _t12,
	  _t13,
	  _t14;
	class DateTimeControl extends main_core_events.EventEmitter {
	  constructor(uid, options = {
	    showTimezone: true
	  }) {
	    super();
	    this.DATE_INPUT_WIDTH = 110;
	    this.TIME_INPUT_WIDTH = 90;
	    this.zIndex = 4200;
	    this.from = null;
	    this.to = null;
	    this.setEventNamespace('BX.Calendar.Controls.DateTimeControl');
	    this.showTimezone = options.showTimezone;
	    this.inlineEditMode = !!options.inlineEditMode;
	    this.currentInlineEditMode = options.currentInlineEditMode || 'view';
	    this.UID = uid || 'date-time-' + Math.round(Math.random() * 100000);
	    this.DOM = {
	      outerWrap: options.outerWrap || null,
	      outerContent: options.outerContent || null
	    };
	    this.create();
	  }
	  create() {
	    if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	      if (this.inlineEditMode) {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	      }
	      this.DOM.leftInnerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t$a || (_t$a = _$a`<div class="calendar-field-block calendar-field-block-left"></div>`)));
	      this.DOM.fromDate = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_t2$6 || (_t2$6 = _$a`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: ${0}px;"/>
			`), this.DATE_INPUT_WIDTH));
	      if (this.inlineEditMode) {
	        this.DOM.fromDateText = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_t3$5 || (_t3$5 = _$a`<span class="calendar-field-value calendar-field-value-date"></span>`)));
	      }
	      this.DOM.fromTime = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_t4$5 || (_t4$5 = _$a`
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${0}px; max-width: ${0}px;"/>
			`), this.TIME_INPUT_WIDTH, this.TIME_INPUT_WIDTH));
	      if (this.inlineEditMode) {
	        this.DOM.fromTimeText = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_t5$5 || (_t5$5 = _$a`<span class="calendar-field-value calendar-field-value-time"></span>`)));
	      }
	      this.DOM.betweenSpacer = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t6$3 || (_t6$3 = _$a`<div class="calendar-field-block calendar-field-block-between" />`)));
	      this.DOM.rightInnerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t7$3 || (_t7$3 = _$a`<div class="calendar-field-block calendar-field-block-right"></div>`)));
	      this.DOM.toTime = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_t8$2 || (_t8$2 = _$a`
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${0}px; max-width: ${0}px;"/>
			`), this.TIME_INPUT_WIDTH, this.TIME_INPUT_WIDTH));
	      if (this.inlineEditMode) {
	        this.DOM.toTimeText = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_t9$1 || (_t9$1 = _$a`<span class="calendar-field-value calendar-field-value-time"></span>`)));
	      }
	      this.DOM.toDate = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_t10$1 || (_t10$1 = _$a`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: ${0}px;"/>`), this.DATE_INPUT_WIDTH));
	      if (this.inlineEditMode) {
	        this.DOM.toDateText = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_t11$1 || (_t11$1 = _$a`<span class="calendar-field-value calendar-field-value-date"></span>`)));
	      }
	      this.fromTimeControl = new TimeSelector({
	        input: this.DOM.fromTime,
	        onChangeCallback: this.handleTimeFromChange.bind(this)
	      });
	      this.toTimeControl = new TimeSelector({
	        input: this.DOM.toTime,
	        onChangeCallback: this.handleTimeToChange.bind(this)
	      });
	      let fullDayWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_t12 || (_t12 = _$a`
				<span class="calendar-event-full-day"></span>
			`)));
	      this.DOM.fullDay = fullDayWrap.appendChild(main_core.Tag.render(_t13 || (_t13 = _$a`
				<input value="Y" type="checkbox" id="{this.UID}"/>
			`)));
	      fullDayWrap.appendChild(main_core.Tag.render(_t14 || (_t14 = _$a`<label for="{this.UID}">${0}</label>`), main_core.Loc.getMessage('EC_ALL_DAY')));
	    }

	    //this.DOM.defTimezoneWrap = BX(this.UID + '_timezone_default_wrap');
	    //this.DOM.defTimezone = BX(this.UID + '_timezone_default');

	    if (this.showTimezone) ;
	    this.bindEventHandlers();
	  }
	  setValue(value = {}) {
	    this.DOM.fromDate.value = calendar_util.Util.formatDate(value.from);
	    this.DOM.toDate.value = calendar_util.Util.formatDate(value.to);
	    this.DOM.fromTime.value = calendar_util.Util.formatTime(value.from);
	    this.DOM.toTime.value = calendar_util.Util.formatTime(value.to);
	    const parsedFromTime = calendar_util.Util.parseTime(this.DOM.fromTime.value);
	    const parsedToTime = calendar_util.Util.parseTime(this.DOM.toTime.value);
	    this.fromMinutes = parsedFromTime.h * 60 + parsedFromTime.m;
	    this.toMinutes = parsedToTime.h * 60 + parsedToTime.m;
	    this.updateTimePeriod();
	    if (this.inlineEditMode) {
	      this.DOM.fromDateText.innerHTML = calendar_util.Util.formatDateUsable(value.from, true, true);
	      this.DOM.toDateText.innerHTML = calendar_util.Util.formatDateUsable(value.to, true, true);

	      // Hide right part if it's the same date
	      this.DOM.toDateText.style.display = this.DOM.fromDate.value === this.DOM.toDate.value ? 'none' : '';
	      if (value.fullDay) {
	        if (this.DOM.fromDate.value === this.DOM.toDate.value) {
	          this.DOM.toTimeText.innerHTML = main_core.Loc.getMessage('EC_ALL_DAY');
	          this.DOM.toTimeText.style.display = '';
	          this.DOM.fromTimeText.style.display = 'none';
	          this.DOM.fromTimeText.innerHTML = '';
	        } else {
	          this.DOM.betweenSpacer.style.display = '';
	          this.DOM.fromTimeText.style.display = 'none';
	          this.DOM.toTimeText.style.display = 'none';
	        }
	      } else {
	        this.DOM.fromTimeText.innerHTML = this.DOM.fromTime.value;
	        this.DOM.toTimeText.innerHTML = this.DOM.toTime.value;
	        this.DOM.betweenSpacer.style.display = '';
	        this.DOM.fromTimeText.style.display = '';
	        this.DOM.toTimeText.style.display = '';
	      }
	    }
	    if (value.fullDay !== undefined) {
	      this.DOM.fullDay.checked = value.fullDay;
	    }
	    if (this.showTimezone) {
	      value.timezoneFrom = value.timezoneFrom || value.timezoneName;
	      value.timezoneTo = value.timezoneTo || value.timezoneName;
	      if (value.timezoneFrom !== undefined && main_core.Type.isDomNode(this.DOM.fromTz)) {
	        this.DOM.fromTz.value = value.timezoneFrom;
	      }
	      if (value.timezoneTo !== undefined && main_core.Type.isDomNode(this.DOM.toTz)) {
	        this.DOM.toTz.value = value.timezoneTo;
	      }
	      if (value.timezoneName !== undefined && (value.timezoneName !== value.timezoneFrom || value.timezoneName !== value.timezoneTo)) {
	        this.switchTimezone(true);
	      }
	    }
	    this.value = value;
	    this.handleFullDayChange();
	    this.emit('onSetValue');
	  }
	  updateTimePeriod() {
	    this.from = this.getFrom();
	    this.to = this.getTo();
	    this.fromTimeControl.highlightValue(this.from);
	    this.toTimeControl.highlightValue(this.to);
	    this.updateToTimeDurationHints();
	  }
	  getFrom() {
	    return this.getDateWithTime(this.DOM.fromDate.value, this.fromMinutes);
	  }
	  getTo() {
	    return this.getDateWithTime(this.DOM.toDate.value, this.toMinutes);
	  }
	  getDateWithTime(date, minutes) {
	    const parsedDate = calendar_util.Util.parseDate(date);
	    if (!parsedDate) {
	      return null;
	    }
	    return new Date(parsedDate.getTime() + minutes * 60 * 1000);
	  }
	  getValue() {
	    let value = {
	      fullDay: this.DOM.fullDay.checked,
	      fromDate: this.DOM.fromDate.value,
	      toDate: this.DOM.toDate.value,
	      fromTime: this.DOM.fromTime.value,
	      toTime: this.DOM.toTime.value,
	      timezoneFrom: this.DOM.fromTz ? this.DOM.fromTz.value : this.value.timezoneFrom || this.value.timezoneName || null,
	      timezoneTo: this.DOM.toTz ? this.DOM.toTz.value : this.value.timezoneTo || this.value.timezoneName || null
	    };
	    value.from = calendar_util.Util.parseDate(value.fromDate);
	    if (main_core.Type.isDate(value.from)) {
	      value.to = calendar_util.Util.parseDate(value.toDate);
	      if (!main_core.Type.isDate(value.to)) {
	        value.to = value.from;
	      }
	      if (value.fullDay) {
	        value.from.setHours(0, 0, 0);
	        value.to.setHours(0, 0, 0);
	      } else {
	        let fromTime = calendar_util.Util.parseTime(value.fromTime),
	          toTime = calendar_util.Util.parseTime(value.toTime) || fromTime;
	        if (fromTime && toTime) {
	          value.from.setHours(fromTime.h, fromTime.m, 0);
	          value.to.setHours(toTime.h, toTime.m, 0);
	        }
	      }
	    }
	    return value;
	  }
	  bindEventHandlers() {
	    main_core.Event.bind(this.DOM.fromDate, 'click', DateTimeControl.showInputCalendar);
	    main_core.Event.bind(this.DOM.fromDate, 'change', this.handleDateFromChange.bind(this));
	    main_core.Event.bind(this.DOM.toDate, 'click', DateTimeControl.showInputCalendar);
	    main_core.Event.bind(this.DOM.toDate, 'change', this.handleDateToChange.bind(this));
	    main_core.Event.bind(this.DOM.fromTime, 'input', this.handleTimeInput.bind(this));
	    main_core.Event.bind(this.DOM.toTime, 'input', this.handleTimeInput.bind(this));
	    main_core.Event.bind(this.DOM.fromTz, 'change', this.handleValueChange.bind(this));
	    main_core.Event.bind(this.DOM.toTz, 'change', this.handleValueChange.bind(this));
	    main_core.Event.bind(this.DOM.fullDay, 'click', () => {
	      this.handleFullDayChange();
	      this.handleValueChange();
	    });
	    if (this.inlineEditMode) {
	      main_core.Event.bind(this.DOM.outerWrap, 'click', this.changeInlineEditMode.bind(this));
	    }
	    if (main_core.Type.isDomNode(this.DOM.defTimezone)) {
	      main_core.Event.bind(this.DOM.defTimezone, 'change', BX.delegate(function () {
	        //this.calendar.util.setUserOption('timezoneName', this.DOM.defTimezone.value);
	        if (this.bindFromToDefaultTimezones) {
	          this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value;
	        }
	      }, this));
	    }
	    if (this.showTimezone) {
	      if (main_core.Type.isDomNode(this.DOM.tzButton)) {
	        main_core.Event.bind(this.DOM.tzButton, 'click', this.switchTimezone.bind(this));
	      }
	      main_core.Event.bind(this.DOM.fromTz, 'change', function () {
	        if (this.bindTimezones) {
	          this.DOM.toTz.value = this.DOM.fromTz.value;
	        }
	        this.bindFromToDefaultTimezones = false;
	      }.bind(this));
	      main_core.Event.bind(this.DOM.toTz, 'change', function () {
	        this.bindTimezones = false;
	        this.bindFromToDefaultTimezones = false;
	      }.bind(this));
	      this.bindTimezones = this.DOM.fromTz.value === this.DOM.toTz.value;
	      this.bindFromToDefaultTimezones = this.bindTimezones && this.DOM.fromTz.value === this.DOM.toTz.value && this.DOM.fromTz.value === this.DOM.defTimezone.value;
	    }
	  }
	  static showInputCalendar(e) {
	    let target = e.target || e.srcElement;
	    if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input') {
	      const calendarControl = BX.calendar.get();
	      if (calendarControl.popup) {
	        // Workaround hack for BX.calendar - it works as singleton and we trying to reinit it
	        calendarControl.popup.destroy();
	        calendarControl.popup = null;
	        calendarControl._current_layer = null;
	        calendarControl._layers = {};
	      }
	      if (calendarControl.popup_month) {
	        calendarControl.popup_month.destroy();
	        calendarControl.popup_month = null;
	      }
	      if (calendarControl.popup_year) {
	        calendarControl.popup_year.destroy();
	        calendarControl.popup_year = null;
	      }
	      calendarControl.Show({
	        node: target.parentNode,
	        field: target,
	        bTime: false
	      });
	      BX.onCustomEvent(window, 'onCalendarControlChildPopupShown');
	      const calendarPopup = calendarControl.popup;
	      if (calendarPopup) {
	        BX.removeCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
	        BX.addCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
	      }
	    }
	  }
	  static inputCalendarClosePopupHandler() {
	    BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	  }
	  handleDateFromChange() {
	    if (!this.getFrom()) {
	      this.DOM.fromDate.value = calendar_util.Util.formatDate(this.from.getTime());
	      return;
	    }
	    this.DOM.fromDate.value = calendar_util.Util.formatDate(this.getFrom());
	    const difference = this.getFrom().getTime() - this.from.getTime();
	    this.DOM.toDate.value = calendar_util.Util.formatDate(this.to.getTime() + difference);
	    this.handleValueChange();
	  }
	  handleDateToChange() {
	    if (!this.getTo()) {
	      this.DOM.toDate.value = calendar_util.Util.formatDate(this.to.getTime());
	      return;
	    }
	    this.DOM.toDate.value = calendar_util.Util.formatDate(this.getTo());
	    const difference = Math.abs(this.to.getTime() - this.getTo().getTime());
	    const yearDuration = 1000 * 60 * 60 * 24 * 300;
	    if (difference > yearDuration) {
	      const duration = this.to.getTime() - this.from.getTime();
	      const toDate = calendar_util.Util.parseDate(this.DOM.toDate.value);
	      toDate.setHours(this.to.getHours(), this.to.getMinutes(), 0, 0);
	      const fromDate = new Date(toDate.getTime() - duration);
	      this.DOM.fromDate.value = calendar_util.Util.formatDate(fromDate);
	    }
	    if (this.getTo() < this.getFrom()) {
	      this.DOM.toDate.value = this.DOM.fromDate.value;
	      this.DOM.toTime.value = this.DOM.fromTime.value;
	      this.toMinutes = this.getMinutesFromFormattedTime(this.DOM.toTime.value);
	    }
	    this.handleValueChange();
	  }
	  handleTimeFromChange(inputValue, dataValue) {
	    this.handleTimeChange(this.DOM.fromTime);
	    if (this.isIncorrectTimeValue(this.DOM.fromTime.value)) {
	      this.DOM.fromTime.value = calendar_util.Util.formatTime(this.from);
	    } else {
	      this.fromMinutes = dataValue != null ? dataValue : this.getMinutesFromFormattedTime(this.DOM.fromTime.value);
	      this.DOM.fromTime.value = calendar_util.Util.formatTime(this.getFrom());
	    }
	    if (this.getTo()) {
	      const difference = this.getFrom().getTime() - this.from.getTime();
	      this.toMinutes = this.toMinutes + difference / (60 * 1000);
	    }
	    this.handleValueChange();
	  }
	  handleTimeToChange(inputValue, dataValue) {
	    this.handleTimeChange(this.DOM.toTime);
	    if (this.isIncorrectTimeValue(this.DOM.toTime.value)) {
	      this.DOM.toTime.value = calendar_util.Util.formatTime(this.to);
	    } else {
	      this.toMinutes = dataValue != null ? dataValue : this.getMinutesFromFormattedTime(this.DOM.toTime.value);
	      this.DOM.toTime.value = calendar_util.Util.formatTime(this.getTo());
	    }
	    if (this.getTo() < this.getFrom()) {
	      const difference = this.getTo().getTime() - this.to.getTime();
	      this.fromMinutes = this.fromMinutes + difference / (60 * 1000);
	      const newFromDate = new Date(this.from.getTime() + difference);
	      this.DOM.fromTime.value = calendar_util.Util.formatTime(newFromDate);
	      this.DOM.fromDate.value = calendar_util.Util.formatDate(newFromDate);
	    }
	    this.handleValueChange();
	  }
	  isIncorrectTimeValue(timeValue) {
	    if (BX.isAmPmMode()) {
	      return timeValue === '';
	    }
	    return timeValue === '' || timeValue[0] !== '0' && calendar_util.Util.parseTime(timeValue).h === 0;
	  }
	  handleTimeChange(timeSelector) {
	    if (timeSelector.value === '') {
	      return;
	    }
	    let time = this.getMaskedTime(timeSelector.value);
	    time = this.beautifyTime(time);
	    if (BX.isAmPmMode()) {
	      var _timeSelector$value$t;
	      let amPmSymbol = ((_timeSelector$value$t = timeSelector.value.toLowerCase().match(/[ap]/g)) != null ? _timeSelector$value$t : []).pop();
	      if (!amPmSymbol) {
	        const hour = parseInt(this.getMinutesAndHours(time).hours);
	        if (8 <= hour && hour <= 11) {
	          amPmSymbol = 'a';
	        } else {
	          amPmSymbol = 'p';
	        }
	      }
	      if (amPmSymbol === 'a') {
	        time += ' am';
	      }
	      if (amPmSymbol === 'p') {
	        time += ' pm';
	      }
	    }
	    timeSelector.value = time;
	  }
	  handleTimeInput(e) {
	    e.target.value = this.getMaskedTime(e.target.value, e.data, e.inputType === 'deleteContentBackward');
	  }
	  getMaskedTime(value, key, backspace = false) {
	    if (backspace) {
	      return value;
	    }
	    let time = '';
	    const {
	      hours,
	      minutes
	    } = this.getMinutesAndHours(value, key);
	    if (hours && !minutes) {
	      time = `${hours}`;
	      if (value.length - time.length === 1 || value.indexOf(':') !== -1) {
	        time += ':';
	      }
	    }
	    if (hours && minutes) {
	      time = `${hours}:${minutes}`;
	    }
	    if (BX.isAmPmMode() && this.clearTimeString(time) !== '') {
	      var _value$toLowerCase$ma;
	      const amPmSymbol = ((_value$toLowerCase$ma = value.toLowerCase().match(/[ap]/g)) != null ? _value$toLowerCase$ma : []).pop();
	      if (amPmSymbol === 'a') {
	        time = this.beautifyTime(time) + ' am';
	      }
	      if (amPmSymbol === 'p') {
	        time = this.beautifyTime(time) + ' pm';
	      }
	    }
	    return time;
	  }
	  getMinutesAndHours(value, key) {
	    let time = this.clearTimeString(value, key);
	    let hours, minutes;
	    if (time.indexOf(':') !== -1) {
	      hours = time.match(/[\d]*:/g)[0].slice(0, -1);
	      minutes = time.match(/:[\d]*/g)[0].slice(1);
	    } else {
	      var _time$match;
	      const digits = ((_time$match = time.match(/\d/g)) != null ? _time$match : []).splice(0, 4).map(d => parseInt(d));
	      if (digits.length === 4 && digits[0] > this.getMaxHours() / 10) {
	        digits.pop();
	      }
	      if (digits.length === 1) {
	        hours = `${digits[0]}`;
	      }
	      if (digits.length === 2) {
	        hours = `${digits[0]}${digits[1]}`;
	        if (parseInt(hours) > this.getMaxHours()) {
	          hours = `${digits[0]}`;
	          minutes = `${digits[1]}`;
	        }
	      }
	      if (digits.length === 3) {
	        if (BX.isAmPmMode()) {
	          if (digits[0] >= 1) {
	            hours = `${digits[0]}`;
	            minutes = `${digits[1]}${digits[2]}`;
	          } else {
	            hours = `${digits[0]}${digits[1]}`;
	            minutes = `${digits[2]}`;
	          }
	        } else {
	          if (parseInt(`${digits[0]}${digits[1]}`) < 24) {
	            hours = `${digits[0]}${digits[1]}`;
	            minutes = `${digits[2]}`;
	          } else {
	            hours = `${digits[0]}`;
	            minutes = `${digits[1]}${digits[2]}`;
	          }
	        }
	      }
	      if (digits.length === 4) {
	        hours = `${digits[0]}${digits[1]}`;
	        minutes = `${digits[2]}${digits[3]}`;
	      }
	    }
	    if (hours) {
	      hours = this.formatHours(hours);
	    }
	    if (minutes) {
	      minutes = this.formatMinutes(minutes);
	    }
	    return {
	      hours,
	      minutes
	    };
	  }
	  clearTimeString(str, key) {
	    let validatedTime = str.replace(/[ap]/g, '').replace(/\D/g, ':'); // remove a and p and replace not digits to :
	    validatedTime = validatedTime.replace(/:*/, ''); // remove everything before first digit

	    // leave only first :
	    const firstColonIndex = validatedTime.indexOf(':');
	    validatedTime = validatedTime.substr(0, firstColonIndex + 1) + validatedTime.slice(firstColonIndex + 1).replaceAll(':', '');

	    // leave not more than 2 hour digits and 2 minute digits
	    if (firstColonIndex !== -1) {
	      const hours = this.formatHours(validatedTime.match(/[\d]*:/g)[0].slice(0, -1));
	      const minutes = validatedTime.match(/:[\d]*/g)[0].slice(1).slice(0, 3);
	      if (hours.length === 1 && minutes.length === 3 && !isNaN(parseInt(key)) && this.areTimeDigitsCorrect(`${hours}${minutes}`)) {
	        return `${hours}${minutes}`;
	      }
	      return `${hours}:${minutes}`;
	    }
	    return validatedTime.slice(0, 4);
	  }
	  areTimeDigitsCorrect(time) {
	    const hh = time.slice(0, 2);
	    const mm = time.slice(2);
	    return this.formatHours(hh) === hh && this.formatMinutes(mm) === mm;
	  }
	  formatHours(str) {
	    const firstDigit = str[0];
	    if (parseInt(firstDigit) > this.getMaxHours() / 10) {
	      return `0${firstDigit}`;
	    }
	    if (parseInt(str) <= this.getMaxHours()) {
	      var _str$;
	      return `${firstDigit}${(_str$ = str[1]) != null ? _str$ : ''}`;
	    }
	    return `${firstDigit}`;
	  }
	  formatMinutes(str) {
	    var _str$2;
	    const firstDigit = str[0];
	    if (firstDigit >= 6) {
	      return `0${firstDigit}`;
	    }
	    return `${firstDigit}${(_str$2 = str[1]) != null ? _str$2 : ''}`;
	  }
	  beautifyTime(time) {
	    if (this.clearTimeString(time) === '') {
	      return '';
	    }
	    if (time.indexOf(':') === -1) {
	      time += ':00';
	    }
	    if (time.indexOf(':') === time.length - 1) {
	      time += '00';
	    }
	    let {
	      hours,
	      minutes
	    } = this.getMinutesAndHours(time);
	    hours = `0${hours}`.slice(-2);
	    minutes = `0${minutes}`.slice(-2);
	    return `${hours}:${minutes}`;
	  }
	  getMaxHours() {
	    return BX.isAmPmMode() ? 12 : 24;
	  }
	  handleFullDayChange() {
	    let fullDay = this.getFullDayValue();
	    if (fullDay) {
	      if (main_core.Type.isDomNode(this.DOM.dateTimeWrap)) {
	        main_core.Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	      }
	      if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
	      }
	    } else {
	      if (main_core.Type.isDomNode(this.DOM.dateTimeWrap)) {
	        main_core.Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	      }
	      if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	        main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
	      }
	    }
	  }
	  handleValueChange() {
	    this.setValue({
	      from: this.getFrom(),
	      to: this.getTo()
	    });
	    this.emit('onChange', new main_core_events.BaseEvent({
	      data: {
	        value: this.getValue()
	      }
	    }));
	  }
	  updateToTimeDurationHints() {
	    this.toTimeControl.updateDurationHints(this.DOM.fromTime.value, this.DOM.toTime.value, this.DOM.fromDate.value, this.DOM.toDate.value);
	  }
	  getFullDayValue() {
	    return !!this.DOM.fullDay.checked;
	  }
	  getMinutesFromFormattedTime(time) {
	    const parsedTime = calendar_util.Util.parseTime(time);
	    return parsedTime.h * 60 + parsedTime.m;
	  }
	  switchTimezone(showTimezone) {
	    if (!main_core.Type.isBoolean(showTimezone)) {
	      showTimezone = BX.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	    }
	    if (showTimezone) {
	      main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	      main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	    } else {
	      main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	      main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	    }
	  }
	  changeInlineEditMode() {
	    if (!this.viewMode) {
	      this.setInlineEditMode('edit');
	    }
	  }
	  setViewMode(viewMode) {
	    this.viewMode = viewMode;
	    if (this.viewMode && this.currentInlineEditMode === 'edit') {
	      this.setInlineEditMode('view');
	    }
	  }
	  setInlineEditMode(currentInlineEditMode) {
	    if (this.inlineEditMode) {
	      this.currentInlineEditMode = currentInlineEditMode;
	      if (this.currentInlineEditMode === 'edit') {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
	        main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	      } else {
	        main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	      }
	    }
	  }
	}

	class BusyUsersDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');
	    this.zIndex = 3200;
	    this.id = 'busy-user-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show(params = {}) {
	    this.plural = params.users.length > 1;
	    const userNames = [];
	    params.users.forEach(user => {
	      userNames.push(user.DISPLAY_NAME);
	    });
	    const userNamesPrepared = userNames.join(', ');
	    const message = this.plural ? main_core.Loc.getMessage('EC_BUSY_USERS_PLURAL').replace('#USER_LIST#', userNamesPrepared) : main_core.Loc.getMessage('EC_BUSY_USERS_SINGLE').replace('#USER_NAME#', params.users[0].DISPLAY_NAME);
	    this.dialog = new ui_dialogs_messagebox.MessageBox({
	      title: main_core.Loc.getMessage('EC_BUSY_USERS_TITLE'),
	      message: main_core.Text.encode(message),
	      buttons: this.getButtons(),
	      popupOptions: {
	        autoHide: true,
	        closeByEsc: true,
	        draggable: false,
	        closeIcon: true,
	        maxWidth: 700,
	        minHeight: 150,
	        animation: 'fading-slide'
	      }
	    });
	    this.dialog.show();
	  }
	  getButtons() {
	    return [new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.SMALL,
	      color: ui_buttons.ButtonColor.PRIMARY,
	      text: main_core.Loc.getMessage('EC_BUSY_USERS_BACK2EDIT'),
	      events: {
	        click: () => {
	          this.emit('onContinueEditing');
	          this.close();
	        }
	      }
	    }), new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.SMALL,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: this.plural ? main_core.Loc.getMessage('EC_BUSY_USERS_EXCLUDE_PLURAL') : main_core.Loc.getMessage('EC_BUSY_USERS_EXCLUDE_SINGLE'),
	      events: {
	        click: () => {
	          this.emit('onSaveWithout');
	          this.close();
	        }
	      }
	    })];
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	  isShown() {
	    if (this.dialog) {
	      return this.dialog.getPopupWindow().isShown();
	    }
	    return false;
	  }
	}

	let _$b = t => t,
	  _t$b,
	  _t2$7,
	  _t3$6;
	class UserPlannerSelector extends main_core_events.EventEmitter {
	  constructor(params = {}) {
	    super();
	    this.zIndex = 4200;
	    this.readOnlyMode = true;
	    this.meetingNotifyValue = true;
	    this.userSelectorDialog = null;
	    this.attendeesEntityList = [];
	    this.inlineEditMode = UserPlannerSelector.VIEW_MODE;
	    this.prevUserList = [];
	    this.loadedAccessibilityData = {};
	    this.REFRESH_PLANNER_DELAY = 500;
	    this.setEventNamespace('BX.Calendar.Controls.UserPlannerSelector');
	    this.selectorId = params.id || 'user-selector-' + Math.round(Math.random() * 10000);
	    this.BX = calendar_util.Util.getBX();
	    this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: params.wrap,
	      informWrap: params.informWrap,
	      informWrapText: params.informWrap.querySelector('.calendar-field-container-inform-text'),
	      moreLink: params.outerWrap.querySelector('.calendar-members-more'),
	      changeLink: params.outerWrap.querySelector('.calendar-members-change-link'),
	      attendeesLabel: params.outerWrap.querySelector('.calendar-attendees-label'),
	      attendeesList: params.outerWrap.querySelector('.calendar-attendees-list'),
	      userSelectorWrap: params.outerWrap.querySelector('.calendar-user-selector-wrap'),
	      plannerOuterWrap: params.plannerOuterWrap,
	      videocallWrap: params.outerWrap.querySelector('.calendar-videocall-wrap'),
	      hideGuestsWrap: params.hideGuestsWrap,
	      hideGuestsIcon: params.hideGuestsWrap.querySelector('.calendar-hide-members-icon-hidden')
	    };
	    this.refreshPlannerStateDebounce = main_core.Runtime.debounce(this.refreshPlannerState, this.REFRESH_PLANNER_DELAY, this);
	    if (main_core.Type.isBoolean(params.readOnlyMode)) {
	      this.readOnlyMode = params.readOnlyMode;
	    }
	    this.userId = params.userId;
	    this.type = params.type;
	    this.ownerId = params.ownerId;
	    this.zIndex = params.zIndex || this.zIndex;
	    this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat;
	    this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;
	    this.isEditableSharingEvent = !!params.isEditableSharingEvent;
	    this.openEditFormCallback = params.openEditFormCallback;
	    this.create();
	  }
	  create() {
	    if (this.DOM.changeLink && !this.isReadOnly()) {
	      let clickAction;
	      if (!this.isEditableSharingEvent) {
	        clickAction = () => {
	          if (!this.userSelectorDialog) {
	            this.userSelectorDialog = new ui_entitySelector.Dialog({
	              targetNode: this.DOM.changeLink,
	              context: 'CALENDAR',
	              preselectedItems: this.attendeesPreselectedItems,
	              enableSearch: true,
	              zIndex: this.zIndex + 10,
	              events: {
	                'Item:onSelect': this.handleUserSelectorChanges.bind(this),
	                'Item:onDeselect': this.handleUserSelectorChanges.bind(this)
	              },
	              entities: [{
	                id: 'user',
	                options: {
	                  inviteGuestLink: true,
	                  emailUsers: true,
	                  analyticsSource: 'calendar'
	                }
	              }, {
	                id: 'project'
	              }, {
	                id: 'department',
	                options: {
	                  selectMode: 'usersAndDepartments'
	                }
	              }, {
	                id: 'meta-user',
	                options: {
	                  'all-users': true
	                }
	              }],
	              searchTabOptions: {
	                stubOptions: {
	                  title: main_core.Loc.getMessage('EC_USER_DIALOG_404_TITLE'),
	                  subtitle: main_core.Loc.getMessage('EC_USER_DIALOG_404_SUBTITLE'),
	                  icon: '/bitrix/images/calendar/search-email.svg',
	                  iconOpacity: 100,
	                  arrow: true
	                }
	              }
	            });
	          }
	          this.userSelectorDialog.show();
	        };
	        main_core.Event.bind(this.DOM.changeLink, 'click', clickAction);
	      }
	    }
	    if (this.DOM.moreLink) {
	      main_core.Event.bind(this.DOM.moreLink, 'click', this.showMoreAttendeesPopup.bind(this));
	    }
	    this.planner = new calendar_planner.Planner({
	      wrap: this.DOM.plannerOuterWrap,
	      minWidth: UserPlannerSelector.PLANNER_WIDTH,
	      width: UserPlannerSelector.PLANNER_WIDTH,
	      showEntryName: false,
	      locked: !this.plannerFeatureEnabled,
	      dayOfWeekMonthFormat: this.dayOfWeekMonthFormat
	    });
	    main_core.Event.bind(this.DOM.informWrap, 'click', () => {
	      this.setInformValue(!this.meetingNotifyValue);
	      this.emit('onNotifyChange');
	    });
	    this.DOM.attendeesLabel.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));
	    this.planner.subscribe('onDateChange', event => {
	      this.emit('onDateChange', event);
	    });
	    this.planner.subscribe('onExpandTimeline', this.handleExpandPlannerTimeline.bind(this));
	    if (this.DOM.hideGuestsWrap && !this.isReadOnly()) {
	      main_core.Event.bind(this.DOM.hideGuestsWrap, 'click', () => {
	        this.setHideGuestsValue(!this.hideGuests);
	      });
	    }
	  }
	  setValue({
	    attendeesEntityList,
	    attendees,
	    location,
	    notify,
	    hideGuests,
	    viewMode,
	    entry
	  }) {
	    var _BX, _BX$Intranet;
	    this.attendeesEntityList = main_core.Type.isArray(attendeesEntityList) ? attendeesEntityList : [];
	    this.attendeesPreselectedItems = this.attendeesEntityList.map(item => {
	      return [item.entityId, item.id];
	    });
	    this.entry = entry;
	    this.entryId = this.entry.id;
	    this.setEntityList(this.attendeesEntityList);
	    this.setInformValue(notify);
	    this.setLocationValue(location);
	    if (main_core.Type.isArray(attendees)) {
	      this.displayAttendees(attendees);
	    }
	    this.refreshPlannerStateDebounce();
	    let dateTime = this.getDateTime();
	    if (dateTime) {
	      this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
	    }
	    if (this.entryId && this.entry && this.entry.data['PARENT_ID'] && (this.entry.data['EVENT_TYPE'] === '#shared#' || this.entry.data['EVENT_TYPE'] === '#shared_crm#') && this.entry.getCurrentStatus() !== false) {
	      main_core.Dom.clean(this.DOM.videocallWrap);
	      main_core.Dom.removeClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
	      this.conferenceButton = main_core.Tag.render(_t$b || (_t$b = _$b`
				<div class="calendar-text-link --gray">${0}</div>
			`), main_core.Loc.getMessage('EC_CONFERENCE_START'));
	      main_core.Event.bind(this.conferenceButton, 'click', this.handleVideoconferenceButtonClick.bind(this));
	      main_core.Dom.append(this.conferenceButton, this.DOM.videocallWrap);
	    } else if ((_BX = BX) != null && (_BX$Intranet = _BX.Intranet) != null && _BX$Intranet.ControlButton && this.DOM.videocallWrap && this.entryId && this.entry.getCurrentStatus() !== false) {
	      main_core.Dom.clean(this.DOM.videocallWrap);
	      main_core.Dom.removeClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
	      this.intranetControllButton = new calendar_controls.IntranetButton({
	        intranetControlButtonParams: {
	          container: this.DOM.videocallWrap,
	          entityType: 'calendar_event',
	          entityId: this.entry.parentId,
	          mainItem: 'chat',
	          entityData: {
	            dateFrom: calendar_util.Util.formatDate(this.entry.from),
	            parentId: this.entry.parentId
	          },
	          analyticsLabel: {
	            formType: 'compact'
	          }
	        },
	        callbacks: {
	          getUsersCount: () => this.attendeeList.accepted.length + this.attendeeList.requested.length,
	          hasChat: () => {
	            var _this$entry$data, _this$entry$data$MEET;
	            return ((_this$entry$data = this.entry.data) == null ? void 0 : (_this$entry$data$MEET = _this$entry$data.MEETING) == null ? void 0 : _this$entry$data$MEET.CHAT_ID) > 0;
	          }
	        }
	      });
	    } else if (this.DOM.videocallWrap) {
	      main_core.Dom.addClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
	    }
	    this.setHideGuestsValue(hideGuests);
	  }
	  handleUserSelectorChanges() {
	    this.showPlanner();
	    const dateTime = this.getDateTime();
	    this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
	    this.setEntityList(this.userSelectorDialog.getSelectedItems().map(item => {
	      return {
	        entityId: item.entityId,
	        id: item.id,
	        entityType: item.entityType
	      };
	    }));
	    this.refreshPlannerStateDebounce();
	    this.emit('onUserCodesChange');
	  }
	  getEntityList() {
	    return this.selectorEntityList;
	  }
	  setEntityList(selectorEntityList) {
	    if (this.type === 'user' && this.userId !== this.ownerId) {
	      selectorEntityList.push({
	        entityId: 'user',
	        id: this.ownerId
	      });
	    }
	    this.selectorEntityList = selectorEntityList;
	  }
	  isReadOnly() {
	    return this.readOnlyMode;
	  }
	  getUserSelector() {
	    return BX.UI.SelectorManager.instances[this.selectorId];
	  }
	  showPlanner() {
	    if (!this.isPlannerDisplayed()) {
	      main_core.Dom.addClass(this.DOM.outerWrap, 'user-selector-edit-mode');
	      this.planner.show();
	      this.planner.showLoader();
	      if (this.entry.isFullDay()) {
	        this.planner.updateSelector(this.planner.currentFromDate, this.planner.currentToDate, true);
	      }
	    }
	  }
	  checkBusyTime() {
	    const dateTime = this.getDateTime();
	    const entityList = this.getEntityList();
	    this.planner.updateScaleLimitsFromEntry(dateTime.from, dateTime.to);
	    this.runPlannerDataRequest({
	      entityList: entityList,
	      timezone: dateTime.timezoneFrom,
	      location: this.getLocationValue(),
	      entryId: this.entryId
	    }).then(response => {
	      for (let id in response.data.accessibility) {
	        if (response.data.accessibility.hasOwnProperty(id)) {
	          this.loadedAccessibilityData[id] = response.data.accessibility[id];
	        }
	      }
	      if (main_core.Type.isArray(response.data.entries)) {
	        response.data.entries.forEach(entry => {
	          if (entry.type === 'user' && !this.prevUserList.includes(parseInt(entry.id))) {
	            this.prevUserList.push(parseInt(entry.id));
	          }
	        });
	      }
	      if (main_core.Type.isArray(response.data.accessibility[this.ownerId])) {
	        const from = this.getDateTime().from;
	        const to = this.getDateTime().to;
	        const preparedData = this.preparedDataAccessibility(response.data.accessibility[this.ownerId]);
	        if (!this.planner.currentFromDate) {
	          this.planner.currentFromDate = from;
	        }
	        if (!this.planner.currentToDate) {
	          this.planner.currentToDate = to;
	        }
	        const item = this.planner.checkTimePeriod(from, to, preparedData);
	        if (main_core.Type.isObject(item) && main_core.Type.isArray(response.data.entries)) {
	          this.showPlanner();
	          this.planner.update(response.data.entries, response.data.accessibility);
	          this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
	          this.planner.hideLoader();
	          this.displayAttendees(this.prepareAttendeesForDisplay(response.data.entries));
	        }
	      }
	    });
	  }
	  prepareAttendeesForDisplay(attendees) {
	    return attendees.filter(item => {
	      return item.type === 'user';
	    }).map(item => {
	      return {
	        ID: item.id,
	        AVATAR: item.avatar,
	        DISPLAY_NAME: item.name,
	        EMAIL_USER: item.emailUser,
	        SHARING_USER: item.sharingUser,
	        STATUS: (item.status || '').toUpperCase(),
	        URL: item.url
	      };
	    });
	  }
	  refreshPlannerState() {
	    if (this.planner && this.planner.isShown()) {
	      let dateTime = this.getDateTime();
	      this.loadPlannerData({
	        entityList: this.getEntityList(),
	        timezone: dateTime.timezoneFrom,
	        location: this.getLocationValue(),
	        entryId: this.entryId,
	        prevUserList: this.prevUserList
	      });
	    }
	  }
	  loadPlannerData(params = {}) {
	    this.planner.showLoader();
	    return new Promise(resolve => {
	      this.runPlannerDataRequest(params).then(response => {
	        for (let id in response.data.accessibility) {
	          if (response.data.accessibility.hasOwnProperty(id)) {
	            this.loadedAccessibilityData[id] = response.data.accessibility[id];
	          }
	        }
	        if (main_core.Type.isArray(response.data.entries)) {
	          response.data.entries.forEach(entry => {
	            if (entry.type === 'user' && !this.prevUserList.includes(parseInt(entry.id))) {
	              this.prevUserList.push(parseInt(entry.id));
	            }
	          });
	        }
	        this.planner.hideLoader();
	        this.planner.update(response.data.entries, this.loadedAccessibilityData);
	        resolve(response);
	      }, response => {
	        resolve(response);
	      });
	    });
	  }
	  runPlannerDataRequest(params) {
	    return this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	      data: {
	        entryId: params.entryId || 0,
	        entryLocation: this.entry.data.LOCATION || '',
	        ownerId: this.ownerId,
	        hostId: this.entry.data.MEETING_HOST || null,
	        type: this.type,
	        entityList: params.entityList || [],
	        dateFrom: calendar_util.Util.formatDate(this.planner.scaleDateFrom),
	        dateTo: calendar_util.Util.formatDate(this.planner.scaleDateTo),
	        timezone: params.timezone || '',
	        location: params.location || '',
	        entries: params.entrieIds || false,
	        prevUserList: params.prevUserList || []
	      }
	    });
	  }
	  setDateTime(dateTime, updatePlaner = false) {
	    // 0183864
	    const dateTimeCloned = {
	      ...dateTime
	    };
	    if (dateTimeCloned.fullDay) {
	      dateTimeCloned.from.setHours(0, 0, 0, 0);
	      const dayCount = Math.ceil((dateTimeCloned.to.getTime() - dateTimeCloned.from.getTime() + 1) / (1000 * 3600 * 24));
	      dateTimeCloned.to = new Date(dateTimeCloned.from.getTime() + (dayCount - 1) * 24 * 3600 * 1000);
	      dateTimeCloned.to.setHours(23, 55, 0, 0);
	    }
	    this.dateTime = dateTimeCloned;
	    this.planner.currentFromDate = dateTimeCloned.from;
	    this.planner.currentToDate = dateTimeCloned.to;
	    if (this.planner && updatePlaner) {
	      this.planner.updateSelector(dateTimeCloned.from, dateTimeCloned.to, dateTimeCloned.fullDay);
	    } else if (this.planner) {
	      let fromHours = parseInt(dateTimeCloned.from.getHours()) + Math.floor(dateTimeCloned.from.getMinutes() / 60);
	      let toHours = parseInt(dateTimeCloned.to.getHours()) + Math.floor(dateTimeCloned.to.getMinutes() / 60);
	      if (fromHours !== 0 && fromHours <= this.planner.shownScaleTimeFrom || toHours !== 0 && toHours !== 23 && toHours + 1 >= this.planner.shownScaleTimeTo) {
	        this.planner.updateSelector(dateTimeCloned.from, dateTimeCloned.to, dateTimeCloned.fullDay);
	      }
	    }
	  }
	  getDateTime() {
	    return this.dateTime;
	  }
	  setLocationValue(location) {
	    this.location = location;
	  }
	  getLocationValue() {
	    return this.location;
	  }
	  displayAttendees(attendees = []) {
	    main_core.Dom.clean(this.DOM.attendeesList);
	    this.attendeeList = calendar_controls.AttendeesList.sortAttendees(attendees);
	    const usersCount = this.attendeeList.accepted.length + this.attendeeList.requested.length;
	    this.emit('onDisplayAttendees', new main_core_events.BaseEvent({
	      data: {
	        usersCount: usersCount
	      }
	    }));
	    const userLength = Math.min(this.attendeeList.accepted.length, UserPlannerSelector.MAX_USER_COUNT_DISPLAY);
	    if (userLength > 0) {
	      for (let i = 0; i < userLength; i++) {
	        this.attendeeList.accepted[i].shown = true;
	        this.DOM.attendeesList.appendChild(UserPlannerSelector.getUserAvatarNode(this.attendeeList.accepted[i]));
	      }
	    }
	    this.DOM.attendeesLabel.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));
	    if (attendees.length > 1) {
	      this.DOM.moreLink.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_ALL_COUNT').replace('#COUNT#', attendees.length));
	      main_core.Dom.show(this.DOM.moreLink);
	    } else {
	      main_core.Dom.hide(this.DOM.moreLink);
	    }
	    if (this.hasExternalEmailUsers(attendees) && this.isPlannerDisplayed() && !this.isReadOnly()) {
	      this.showHideGuestsOption();
	    } else {
	      this.hideHideGuestsOption();
	    }
	  }
	  static getUserAvatarNode(user) {
	    let imageNode,
	      img = user.AVATAR || user.SMALL_AVATAR;
	    if (!img || img === "/bitrix/images/1.gif") {
	      let defaultAvatarClass = 'ui-icon-common-user';
	      if (user.EMAIL_USER) {
	        defaultAvatarClass = 'ui-icon-common-user-mail';
	      }
	      if (user.SHARING_USER) {
	        defaultAvatarClass += ' ui-icon-common-user-sharing';
	      }
	      imageNode = main_core.Tag.render(_t2$7 || (_t2$7 = _$b`<div title="${0}" class="ui-icon ${0}"><i></i></div>`), main_core.Text.encode(user.DISPLAY_NAME), defaultAvatarClass);
	    } else {
	      imageNode = main_core.Tag.render(_t3$6 || (_t3$6 = _$b`
			<img
				title="${0}"
				class="calendar-member"
				id="simple_popup_${0}"
				src="${0}"
			>`), main_core.Text.encode(user.DISPLAY_NAME), parseInt(user.ID), encodeURI(img));
	    }
	    return imageNode;
	  }
	  showMoreAttendeesPopup() {
	    new calendar_controls.AttendeesList(this.DOM.moreLink, this.attendeeList).showPopup();
	  }
	  setInformValue(value) {
	    if (main_core.Type.isBoolean(value)) {
	      const DISABLED_CLASS = 'calendar-field-container-inform-off';
	      this.meetingNotifyValue = value;
	      if (this.meetingNotifyValue) {
	        main_core.Dom.removeClass(this.DOM.informWrap, DISABLED_CLASS);
	        this.DOM.informWrap.title = main_core.Loc.getMessage('EC_NOTIFY_OPTION_ON_TITLE');
	        this.DOM.informWrapText.innerHTML = main_core.Loc.getMessage('EC_NOTIFY_OPTION');
	      } else {
	        main_core.Dom.addClass(this.DOM.informWrap, DISABLED_CLASS);
	        this.DOM.informWrap.title = main_core.Loc.getMessage('EC_NOTIFY_OPTION_OFF_TITLE');
	        this.DOM.informWrapText.innerHTML = main_core.Loc.getMessage('EC_DONT_NOTIFY_OPTION');
	      }
	    }
	  }
	  getInformValue(value) {
	    return this.meetingNotifyValue;
	  }
	  setViewMode(readOnlyMode) {
	    this.readOnlyMode = readOnlyMode;
	    if (this.readOnlyMode) {
	      main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
	    } else {
	      main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
	    }
	  }
	  isPlannerDisplayed() {
	    return this.planner.isShown();
	  }
	  hasExternalEmailUsers(attendees = []) {
	    return !!attendees.find(item => {
	      return item.EMAIL_USER;
	    }) || !!this.getEntityList().find(item => {
	      return item.entityType === 'email';
	    });
	  }
	  destroy() {
	    if (this.userSelectorDialog && this.userSelectorDialog.destroy) {
	      this.userSelectorDialog.destroy();
	      this.userSelectorDialog = null;
	    }
	    if (this.intranetControllButton && this.intranetControllButton.destroy) {
	      this.intranetControllButton.destroy();
	      this.intranetControllButton = null;
	    }
	  }
	  showHideGuestsOption() {
	    this.DOM.hideGuestsWrap.style.display = '';
	    calendar_util.Util.initHintNode(this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-helper'));
	  }
	  hideHideGuestsOption() {
	    this.DOM.hideGuestsWrap.style.display = 'none';
	  }
	  setHideGuestsValue(hideGuests = true) {
	    this.hideGuests = hideGuests;
	    if (main_core.Type.isElementNode(this.DOM.hideGuestsIcon)) {
	      this.DOM.hideGuestsIcon.className = this.hideGuests ? 'calendar-hide-members-icon-hidden' : 'calendar-hide-members-icon-visible';
	    }
	    const hideGuestsText = this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-text');
	    if (main_core.Type.isElementNode(hideGuestsText)) {
	      hideGuestsText.innerHTML = this.hideGuests ? main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES') : main_core.Loc.getMessage('EC_SHOW_GUEST_NAMES');
	    }
	  }
	  preparedDataAccessibility(calendarEventsAccessibility) {
	    return calendarEventsAccessibility.map(item => {
	      return calendar_planner.Planner.prepareAccessibilityItem(item);
	    });
	  }
	  clearAccessibilityData(userIdList) {
	    if (main_core.Type.isArray(userIdList) && userIdList.length && this.prevUserList.length) {
	      this.prevUserList = this.prevUserList.filter(userId => {
	        return !userIdList.includes(userId);
	      });
	    }
	  }
	  handleExpandPlannerTimeline(event) {
	    if (event && event.getData) {
	      let data = event.getData();
	      if (data.reload) {
	        const dateTime = this.getDateTime();
	        this.loadPlannerData({
	          entityList: this.getEntityList(),
	          timezone: dateTime.timezoneFrom,
	          location: this.getLocationValue(),
	          entryId: this.entryId,
	          focusSelector: false
	        });
	      }
	    }
	  }
	  handleVideoconferenceButtonClick() {
	    this.getConferenceChatId();
	  }
	  getConferenceChatId() {
	    return this.BX.ajax.runAction('calendar.api.calendarajax.getConferenceChatId', {
	      data: {
	        eventId: this.entry.data['PARENT_ID']
	      }
	    }).then(response => {
	      if (top.window.BXIM && response.data && response.data.chatId) {
	        top.BXIM.openMessenger('chat' + parseInt(response.data.chatId));
	        return null;
	      }
	      alert(main_core.Loc.getMessage('EC_CONFERENCE_ERROR'));
	      return null;
	    }, response => {
	      alert(main_core.Loc.getMessage('EC_CONFERENCE_ERROR'));
	      return null;
	    });
	  }
	  setEditableSharingEventMode() {
	    main_core.Dom.style(this.DOM.changeLink, 'display', 'inline-block');
	    const clickAction = () => {
	      if (main_core.Type.isFunction(this.openEditFormCallback())) {
	        this.openEditFormCallback();
	      }
	    };
	    main_core.Event.bind(this.DOM.changeLink, 'click', clickAction);
	    if (this.attendeesPreselectedItems.length <= 2) {
	      const hintPopup = new BX.PopupWindow('ui-hint-popup-' + +new Date(), this.DOM.changeLink, {
	        darkMode: true,
	        content: main_core.Loc.getMessage('EC_EDIT_SHARING_EVENTS_FEATURE_POPUP_CONTENT'),
	        angle: {
	          position: 'top',
	          offset: 50
	        },
	        autoHide: true,
	        animation: {
	          showClassName: "calendar-edit-sharing-events-feature-popup-animation-open",
	          closeClassName: "calendar-edit-sharing-events-feature-popup-animation-close",
	          closeAnimationType: "animation"
	        }
	      });
	      setTimeout(() => hintPopup.show(), 500);
	      setTimeout(() => hintPopup.close(), 5000);
	    }
	  }
	}
	UserPlannerSelector.VIEW_MODE = 'view';
	UserPlannerSelector.EDIT_MODE = 'edit';
	UserPlannerSelector.MAX_USER_COUNT_DISPLAY = 8;
	UserPlannerSelector.PLANNER_WIDTH = 550;

	class ReinviteUserDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ReinviteUserDialog');
	    this.zIndex = 3200;
	    this.id = 'reinvite-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show() {
	    const content = main_core.Dom.create('DIV');
	    this.close();
	    this.dialog = new main_popup.Popup(this.id, null, {
	      overlay: {
	        opacity: 10
	      },
	      autoHide: true,
	      closeByEsc: true,
	      zIndex: this.zIndex,
	      offsetLeft: 0,
	      offsetTop: 0,
	      draggable: true,
	      bindOnResize: false,
	      titleBar: main_core.Loc.getMessage('EC_REINVITE_TITLE'),
	      closeIcon: {
	        right: "12px",
	        top: "10px"
	      },
	      className: 'reinvite-popup-window',
	      content: content,
	      events: {},
	      cacheable: false
	    });
	    new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_REINVITE_YES'),
	      className: "ui-btn ui-btn-primary",
	      events: {
	        click: () => {
	          this.emit('onSelect', new main_core_events.BaseEvent({
	            data: {
	              sendInvitesAgain: true
	            }
	          }));
	          this.close();
	        }
	      }
	    }).renderTo(content);
	    new BX.UI.Button({
	      text: main_core.Loc.getMessage('EC_REINVITE_NO'),
	      className: "ui-btn ui-btn-light-border",
	      events: {
	        click: () => {
	          this.emit('onSelect', new main_core_events.BaseEvent({
	            data: {
	              sendInvitesAgain: false
	            }
	          }));
	          this.close();
	        }
	      }
	    }).renderTo(content);
	    this.dialog.show();
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	}

	let _$c = t => t,
	  _t$c;
	class EmailSelectorControl extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    this.DOM = {};
	    this.CONFIRM_POPUP_ID = 'add_from_email';
	    this.setEventNamespace('BX.Calendar.Controls.EmailSelectorControl');
	    this.DOM.select = params.selectNode;
	    this.mailboxList = main_core.Type.isArray(params.mailboxList) ? params.mailboxList : [];
	    this.DOM.componentWrap = this.DOM.select.parentNode.appendChild(main_core.Tag.render(_t$c || (_t$c = _$c`<div style="display: none;"></div>`)));
	    this.allowAddNewEmail = params.allowAddNewEmail;
	    this.checkValueDebounce = main_core.Runtime.debounce(this.checkValue, 50, this);
	    this.create();
	  }
	  create() {
	    this.setSelectValues();
	    main_core.Event.bind(this.DOM.select, 'change', this.checkValueDebounce);
	    main_core.Event.bind(this.DOM.select, 'click', this.checkValueDebounce);
	  }
	  checkValue() {
	    if (this.DOM.select.value === 'add') {
	      this.showAdd();
	      this.setValue('');
	    }
	  }
	  getValue() {
	    return this.DOM.select.value;
	  }
	  setValue(value) {
	    if (this.mailboxList.length && this.mailboxList.find(mailbox => {
	      return mailbox.email === value;
	    })) {
	      this.DOM.select.value = value;
	    } else {
	      this.DOM.select.value = '';
	    }
	    this.emit('onSetValue', {
	      value: this.DOM.select.value
	    });
	  }
	  setSelectValues() {
	    main_core.Dom.clean(this.DOM.select);
	    this.DOM.select.options.add(new Option(main_core.Loc.getMessage('EC_NO_VALUE'), ''));
	    if (this.mailboxList.length) {
	      this.mailboxList.forEach(value => {
	        this.DOM.select.options.add(new Option(value.formatted, value.email));
	      }, this);
	    }
	    if (this.allowAddNewEmail) {
	      this.DOM.select.options.add(new Option(main_core.Loc.getMessage('EC_ADD_NEW'), 'add'));
	    }
	  }
	  onClick(item) {
	    this.input.value = item.sender;
	    this.mailbox.textContent = item.sender;
	  }
	  showAdd() {
	    if (window.BXMainMailConfirm) {
	      window.BXMainMailConfirm.showForm(this.onAdd.bind(this));
	    }
	    const mainMailConfirmPopup = main_popup.PopupManager.getPopupById(this.CONFIRM_POPUP_ID);
	    if (mainMailConfirmPopup) {
	      mainMailConfirmPopup.subscribe('onClose', () => {
	        this.reloadMailboxList();
	      });
	    }
	  }
	  onAdd(data) {
	    this.reloadMailboxList().then(() => {
	      setTimeout(() => {
	        this.setValue(data.email);
	      }, 0);
	    });
	  }
	  getMenuItem(item) {
	    return {
	      'id': item.id,
	      'text': BX.util.htmlspecialchars(item.sender),
	      'onclick': this.onClick.bind(this, item)
	    };
	  }
	  loadMailboxData() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxData').then(response => {
	        BX.html(this.DOM.componentWrap, response.data.html);
	        this.mailboxList = response.data.additionalParams.mailboxList;
	        this.checkBXMainMailConfirmLoaded(resolve);
	      });
	    });
	  }
	  checkBXMainMailConfirmLoaded(resolve) {
	    if (window.BXMainMailConfirm) {
	      this.setSelectValues();
	      resolve();
	    } else {
	      setTimeout(() => {
	        this.checkBXMainMailConfirmLoaded(resolve);
	      }, 200);
	    }
	  }
	  reloadMailboxList() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxList').then(response => {
	        this.mailboxList = response.data.mailboxList;
	        this.setSelectValues();
	        resolve();
	      });
	    });
	  }
	}

	let _$d = t => t,
	  _t$d;
	class ConfirmedEmailDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.Z_INDEX = 3200;
	    this.SLIDER_Z_INDEX = 4400;
	    this.WIDTH = 400;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.ConfirmedEmailDialog');
	    this.id = 'confirm-email-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show() {
	    this.DOM.content = main_core.Tag.render(_t$d || (_t$d = _$d`<div>
			<div class="calendar-confirm-email-text">${0}</div>
			<div class="calendar-confirm-email-text"><a class="calendar-confirm-email-help-link" href="javascript:void(0);">${0}</a></div>
			<div class="calendar-field-block">
				<select class="calendar-field calendar-field-select ui-btn ui-btn ui-btn-light-border ui-btn-clock"></select>
			</div>
		</div>`), main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_TEXT_1'), main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_HELP_LINK'));
	    this.dialog = new main_popup.Popup(this.id, null, {
	      overlay: {
	        opacity: 10
	      },
	      autoHide: true,
	      width: this.WIDTH,
	      closeByEsc: true,
	      zIndex: this.Z_INDEX,
	      offsetLeft: 0,
	      offsetTop: 0,
	      draggable: true,
	      bindOnResize: false,
	      titleBar: main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_TITLE'),
	      closeIcon: {
	        right: "12px",
	        top: "10px"
	      },
	      className: 'confirmemail-popup-window',
	      content: this.DOM.content,
	      events: {},
	      cacheable: false,
	      buttons: [new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_SEND'),
	        className: `ui-btn ui-btn-primary ${BX.UI.Button.State.DISABLED}`,
	        events: {
	          click: () => {
	            if (this.DOM.select.value && this.DOM.select.value !== 'add') {
	              const userSettings = calendar_util.Util.getUserSettings();
	              userSettings.sendFromEmail = this.emailSelectorControl.getValue();
	              calendar_util.Util.setUserSettings(userSettings);
	              BX.userOptions.save('calendar', 'user_settings', 'sendFromEmail', userSettings.sendFromEmail);
	              this.emit('onSelect', new main_core_events.BaseEvent({
	                data: {
	                  sendFromEmail: userSettings.sendFromEmail
	                }
	              }));
	              this.close();
	            }
	          }
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	        className: "ui-btn ui-btn-light-border",
	        events: {
	          click: this.close.bind(this)
	        }
	      })]
	    });
	    this.DOM.processButton = this.dialog.buttons[0].button;
	    this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
	    main_core.Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
	    this.DOM.select.disabled = true;
	    this.emailSelectorControl = new calendar_controls.EmailSelectorControl({
	      selectNode: this.DOM.select,
	      allowAddNewEmail: true
	    });
	    main_core.Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
	    this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));
	    this.emailSelectorControl.loadMailboxData().then(() => {
	      this.emailSelectorControl.setValue(calendar_util.Util.getUserSettings().sendFromEmail);
	      this.DOM.select.disabled = false;
	      this.DOM.select.className = 'calendar-field calendar-field-select';
	    });
	    this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
	    main_core.Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));
	    this.dialog.show();
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	  handleSelectChanges() {
	    if (this.DOM.select.value && this.DOM.select.value !== 'add') {
	      main_core.Dom.removeClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
	    } else {
	      main_core.Dom.addClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
	    }
	  }
	  openHelpSlider() {
	    if (BX.Helper) {
	      BX.Helper.show("redirect=detail&code=12070142", {
	        zIndex: this.SLIDER_Z_INDEX
	      });
	    }
	  }
	}

	let _$e = t => t,
	  _t$e;
	class EmailLimitationDialog extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.Z_INDEX = 3200;
	    this.EXPAND_LICENSE_URL = '/settings/license_all.php';
	    this.WIDTH = 480;
	    this.DOM = {};
	    this.setEventNamespace('BX.Calendar.Controls.EmailLimitationDialog');
	    this.id = 'email-limitation-dialog-' + Math.round(Math.random() * 10000);
	  }
	  show() {
	    const eventsAmount = calendar_util.Util.getEventWithEmailGuestAmount();
	    const limit = calendar_util.Util.isEventWithEmailGuestAllowed();
	    if (eventsAmount === 4) {
	      this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_5');
	    } else if (eventsAmount === 8) {
	      this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_9');
	    } else {
	      this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_DENY');
	    }
	    this.subText = main_core.Loc.getMessage('EC_EMAIL_LIMIT_SUBTEXT');
	    this.DOM.content = main_core.Tag.render(_t$e || (_t$e = _$e`<div>
			<div class="calendar-email-limit-text">${0}</div>
			<div class="calendar-email-limit-subtext">${0}</div>
		</div>`), this.text, this.subText);
	    this.dialog = this.getDialogPopup();
	    this.dialog.subscribe('onClose', () => {
	      this.emit('onClose');
	    });

	    // this.DOM.processButton = this.dialog.buttons[0].button;
	    //
	    // this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
	    // Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
	    // this.DOM.select.disabled = true;
	    //
	    // this.emailSelectorControl = new EmailSelectorControl({
	    // 	selectNode: this.DOM.select,
	    // 	allowAddNewEmail: true
	    // });
	    // Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
	    // this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));
	    //
	    // this.emailSelectorControl.loadMailboxData()
	    // 	.then(()=> {
	    // 		this.emailSelectorControl.setValue(Util.getUserSettings().sendFromEmail);
	    // 		this.DOM.select.disabled = false;
	    // 		this.DOM.select.className = 'calendar-field calendar-field-select';
	    // 	});
	    //
	    // this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
	    // Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));

	    this.dialog.show();
	  }
	  getDialogPopup() {
	    return new main_popup.Popup(this.id, null, {
	      overlay: {
	        opacity: 10
	      },
	      autoHide: true,
	      width: this.WIDTH,
	      closeByEsc: true,
	      zIndex: this.Z_INDEX,
	      offsetLeft: 0,
	      offsetTop: 0,
	      draggable: true,
	      bindOnResize: false,
	      titleBar: main_core.Loc.getMessage('EC_EMAIL_LIMIT_TITLE'),
	      closeIcon: {
	        right: "12px",
	        top: "10px"
	      },
	      className: 'email-limit-popup',
	      content: this.DOM.content,
	      events: {},
	      cacheable: false,
	      buttons: [new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_EMAIL_LIMIT_EXPAND_PLAN'),
	        className: `ui-btn ui-btn-primary ui-btn-icon-plan`,
	        events: {
	          click: () => {
	            window.open(this.EXPAND_LICENSE_URL, '_blank');
	          }
	        }
	      }), new BX.UI.Button({
	        text: calendar_util.Util.isEventWithEmailGuestAllowed() ? main_core.Loc.getMessage('EC_SEC_SLIDER_CLOSE') : main_core.Loc.getMessage('EC_EMAIL_LIMIT_SAVE_WITHOUT'),
	        className: `ui-btn ui-btn-link`,
	        events: {
	          click: this.close.bind(this)
	        }
	      })]
	    });
	  }
	  close() {
	    if (this.dialog) {
	      this.dialog.close();
	    }
	  }
	}

	class AttendeesList {
	  constructor(node, attendeesList = {}) {
	    this.attendeesList = attendeesList;
	    this.node = node;
	  }
	  setAttendeesList(attendeesList) {
	    this.attendeesList = attendeesList;
	    return this;
	  }
	  showPopup() {
	    if (this.popup) {
	      this.popup.destroy();
	    }
	    const menuItems = this.getMenuItems();
	    this.popup = this.getPopup(menuItems);
	    this.popup.show();
	    this.addAvatarToMenuItems();
	  }
	  addAvatarToMenuItems() {
	    this.popup.menuItems.forEach(item => {
	      const icon = item.layout.item.querySelector('.menu-popup-item-icon');
	      if (main_core.Type.isPlainObject(item.dataset)) {
	        icon.appendChild(calendar_controls.UserPlannerSelector.getUserAvatarNode(item.dataset.user));
	      }
	    });
	  }
	  getPopup(menuItems) {
	    return main_popup.MenuManager.create('compact-event-form-attendees' + Math.round(Math.random() * 100000), this.node, menuItems, {
	      closeByEsc: true,
	      autoHide: true,
	      zIndex: this.zIndex,
	      offsetTop: 0,
	      offsetLeft: 15,
	      angle: true,
	      cacheable: false,
	      className: 'calendar-popup-user-menu'
	    });
	  }
	  getMenuItems() {
	    const menuItems = [];
	    [{
	      code: 'accepted',
	      // Accepted
	      title: main_core.Loc.getMessage('EC_ATTENDEES_Y_NUM')
	    }, {
	      code: 'requested',
	      // Still thinking about
	      title: main_core.Loc.getMessage('EC_ATTENDEES_Q_NUM')
	    }, {
	      code: 'declined',
	      // Declined
	      title: main_core.Loc.getMessage('EC_ATTENDEES_N_NUM')
	    }].forEach(group => {
	      let groupUsers = this.attendeesList[group.code];
	      if (groupUsers.length > 0) {
	        menuItems.push(new main_popup.MenuItem({
	          text: group.title.replace('#COUNT#', groupUsers.length),
	          delimiter: true
	        }));
	        groupUsers.forEach(user => {
	          user.toString = () => {
	            return user.ID;
	          };
	          menuItems.push({
	            text: BX.util.htmlspecialchars(user.DISPLAY_NAME),
	            dataset: {
	              user: user
	            },
	            className: 'calendar-add-popup-user-menu-item',
	            onclick: () => {
	              BX.SidePanel.Instance.open(user.URL, {
	                loader: "intranet:profile",
	                cacheable: false,
	                allowChangeHistory: false,
	                contentClassName: "bitrix24-profile-slider-content",
	                width: 1100
	              });
	            }
	          });
	        });
	      }
	    });
	    return menuItems;
	  }
	  static sortAttendees(attendees) {
	    return {
	      accepted: attendees.filter(user => {
	        return ['H', 'Y'].includes(user.STATUS);
	      }),
	      requested: attendees.filter(user => {
	        return user.STATUS === 'Q' || user.STATUS === '';
	      }),
	      declined: attendees.filter(user => {
	        return user.STATUS === 'N';
	      })
	    };
	  }
	}

	class IntranetButton {
	  constructor(params = {}) {
	    this.intranetControllButton = new intranet_controlButton.ControlButton(params.intranetControlButtonParams);
	    this.hasChat = params.callbacks.hasChat;
	    this.getUsersCount = params.callbacks.getUsersCount;
	    if (main_core.Type.isElementNode(this.intranetControllButton.button)) {
	      this.openChat = this.intranetControllButton.openChat.bind(this.intranetControllButton);
	      this.intranetControllButton.openChat = this.openChatWithConfirm.bind(this);
	      this.startVideoCall = this.intranetControllButton.startVideoCall.bind(this.intranetControllButton);
	      this.intranetControllButton.startVideoCall = this.startVideoCallWithConfirm.bind(this);
	      const chatButton = this.intranetControllButton.button.querySelector('button.ui-btn-main');
	      if (params.intranetControlButtonParams.mainItem === 'chat') {
	        this.setClickListener(chatButton, this.openChatWithConfirm.bind(this));
	      } else {
	        this.setClickListener(chatButton, this.startVideoCallWithConfirm.bind(this));
	      }

	      // For testing purposes
	      this.intranetControllButton.button.setAttribute('data-role', 'videocallButton');
	    }
	  }
	  openChatWithConfirm() {
	    if (this.shouldNotConfirmOpenChat()) {
	      this.openChat();
	      return;
	    }
	    calendar_util.Util.showConfirmPopup(this.openChat, main_core.Loc.getMessage('EC_CREATE_CHAT_CONFIRM_QUESTION'), {
	      okCaption: main_core.Loc.getMessage('EC_CREATE_CHAT_OK'),
	      minWidth: 350,
	      maxWidth: 350
	    });
	  }
	  startVideoCallWithConfirm() {
	    if (this.shouldNotConfirmOpenChat()) {
	      this.startVideoCall();
	      return;
	    }
	    calendar_util.Util.showConfirmPopup(this.startVideoCall, main_core.Loc.getMessage('EC_START_VIDEOCONFERENCE_CONFIRM_QUESTION'), {
	      okCaption: main_core.Loc.getMessage('EC_START_VIDEOCONFERENCE_OK'),
	      minWidth: 350,
	      maxWidth: 350
	    });
	  }
	  shouldNotConfirmOpenChat() {
	    return this.hasChat() || this.getUsersCount() < 10;
	  }
	  setClickListener(element, handler) {
	    const clonedNode = element.cloneNode(true);
	    main_core.Event.bind(clonedNode, 'click', handler);
	    element.parentNode.replaceChild(clonedNode, element);
	  }
	  destroy() {
	    if (this.intranetControllButton && this.intranetControllButton.destroy) {
	      this.intranetControllButton.destroy();
	      this.intranetControllButton = null;
	    }
	  }
	}

	exports.Reminder = Reminder;
	exports.Location = Location;
	exports.UserSelector = UserSelector;
	exports.ColorSelector = ColorSelector;
	exports.SectionSelector = SectionSelector;
	exports.RepeatSelector = RepeatSelector;
	exports.TimeSelector = TimeSelector;
	exports.SelectInput = SelectInput;
	exports.PopupDialog = PopupDialog;
	exports.ViewSelector = ViewSelector;
	exports.LineViewSelector = LineViewSelector;
	exports.AddButton = AddButton;
	exports.MeetingStatusControl = MeetingStatusControl;
	exports.ConfirmStatusDialog = ConfirmStatusDialog;
	exports.ConfirmEditDialog = ConfirmEditDialog;
	exports.ConfirmDeleteDialog = ConfirmDeleteDialog;
	exports.DateTimeControl = DateTimeControl;
	exports.BusyUsersDialog = BusyUsersDialog;
	exports.UserPlannerSelector = UserPlannerSelector;
	exports.ReinviteUserDialog = ReinviteUserDialog;
	exports.EmailSelectorControl = EmailSelectorControl;
	exports.ConfirmedEmailDialog = ConfirmedEmailDialog;
	exports.EmailLimitationDialog = EmailLimitationDialog;
	exports.AttendeesList = AttendeesList;
	exports.IntranetButton = IntranetButton;

}((this.BX.Calendar.Controls = this.BX.Calendar.Controls || {}),BX.Calendar,BX.Calendar,BX,BX,BX.Calendar,BX.UI.Dialogs,BX.UI,BX.Calendar,BX.UI.EntitySelector,BX.Event,BX.Main,BX.Calendar.Controls,BX.Intranet,BX,BX.Calendar));
//# sourceMappingURL=controls.bundle.js.map
