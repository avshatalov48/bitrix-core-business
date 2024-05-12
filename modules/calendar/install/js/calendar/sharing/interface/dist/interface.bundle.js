this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_loader,main_qrcode,ui_designTokens,main_date,calendar_sharing_analytics,ui_entitySelector,main_core,calendar_util,ui_iconSet_api_core,main_popup,ui_dialogs_messagebox,ui_buttons,main_core_events,ui_iconSet_actions,ui_switcher,spotlight,ui_tour,ui_cnt) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _qrCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("qrCode");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	class DialogQr {
	  constructor(options) {
	    this.QRCODE_SIZE = 114;
	    this.QRCODE_COLOR_LIGHT = '#fff';
	    this.QRCODE_COLOR_DARK = '#000';
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _qrCode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {
	      qr: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _qrCode)[_qrCode] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = options.context;
	    this.sharingUrl = options.sharingUrl;
	  }

	  /**
	   *
	   * @returns {Popup}
	   */
	  getPopup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	        className: 'calendar-sharing__qr',
	        width: 315,
	        padding: 0,
	        content: this.getContent(),
	        closeIcon: true,
	        closeByEsc: true,
	        autoHide: true,
	        overlay: true,
	        animation: 'fading-slide'
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }

	  /**
	   *
	   * @returns {Loader}
	   */
	  getLoader() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = new main_loader.Loader({
	        size: 95
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader];
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getNodeQr() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr = main_core.Tag.render(_t || (_t = _`
				<div class="calendar-sharing__qr-block"></div>
			`));

	      // qr emulation
	      this.getLoader().show(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr);
	      this.showQr();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr;
	  }
	  async showQr() {
	    await this.initQrCode();
	    this.QRCode = new QRCode(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].qr, {
	      text: this.sharingUrl,
	      width: this.QRCODE_SIZE,
	      height: this.QRCODE_SIZE,
	      colorDark: this.QRCODE_COLOR_DARK,
	      colorLight: this.QRCODE_COLOR_LIGHT,
	      correctLevel: QRCode.CorrectLevel.H
	    });
	    await this.getLoader().hide();
	  }
	  async initQrCode() {
	    await main_core.Runtime.loadExtension(['main.qrcode']);
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getContent() {
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="calendar-sharing__qr-content">
				<div class="calendar-sharing__qr-title">${0}</div>
				${0}
				<div class="calendar-sharing__qr-info">${0}</div>
				<a class="calendar-sharing__dialog-link" href="${0}" target="_blank">${0}</a>
			</div>
		`), this.getPhraseDependsOnContext('SHARING_INFO_POPUP_QR_TITLE'), this.getNodeQr(), main_core.Loc.getMessage('SHARING_INFO_POPUP_QR_INFO'), this.sharingUrl, main_core.Loc.getMessage('SHARING_INFO_POPUP_QR_OPEN_LINK'));
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  close() {
	    this.getPopup().close();
	  }
	  show() {
	    this.getPopup().show();
	  }
	  destroy() {
	    this.getPopup().destroy();
	  }
	  getPhraseDependsOnContext(code) {
	    return main_core.Loc.getMessage(`${code}_${babelHelpers.classPrivateFieldLooseBase(this, _context)[_context].toUpperCase()}`);
	  }
	}

	let _$1 = t => t,
	  _t$1;
	class Weekday {
	  constructor(options) {
	    this.wrap = null;
	    this.name = options.name;
	    this.index = options.index;
	    this.active = options.active;
	    this.onSelected = main_core.Type.isFunction(options.onSelected) ? options.onSelected : () => {};
	    this.onDiscarded = main_core.Type.isFunction(options.onDiscarded) ? options.onDiscarded : () => {};
	    this.onMouseDown = main_core.Type.isFunction(options.onMouseDown) ? options.onMouseDown : () => {};
	    this.canBeDiscarded = main_core.Type.isFunction(options.canBeDiscarded) ? options.canBeDiscarded : () => {};
	  }
	  render() {
	    const className = this.active ? '--selected' : '';
	    this.wrap = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-sharing__settings-popup-weekday ${0}" onmousedown="${0}">
				<div class="calendar-sharing__settings-popup-weekday-text">${0}</div>
				<div class="calendar-sharing__settings-popup-weekday-icon"></div>
			</div>
		`), className, e => this.handleMouseDown(e), this.name);
	    return this.wrap;
	  }
	  handleMouseDown(event) {
	    if (this.active) {
	      this.discard();
	    } else {
	      this.select();
	    }
	    this.onMouseDown(event, this);
	  }
	  select() {
	    this.active = true;
	    main_core.Dom.addClass(this.wrap, '--selected');
	    this.onSelected();
	  }
	  discard() {
	    if (!this.canBeDiscarded()) {
	      return;
	    }
	    this.active = false;
	    main_core.Dom.removeClass(this.wrap, '--selected');
	    this.onDiscarded();
	  }
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$1,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7;
	class Range {
	  constructor(options) {
	    this.layout = {
	      wrap: null,
	      weekdaysSelect: null,
	      startSelect: null,
	      endSelect: null
	    };
	    this.readOnly = options.readOnly;
	    this.weekStart = options.weekStart;
	    this.workDays = options.workDays;
	    this.getSlotSize = options.getSlotSize;
	    this.rule = {
	      from: options.from,
	      to: options.to,
	      weekdays: this.getSortedWeekdays(options.weekdays)
	    };
	    this.ruleUpdated = main_core.Type.isFunction(options.ruleUpdated) ? options.ruleUpdated : () => {};
	    this.addRange = main_core.Type.isFunction(options.addRange) ? options.addRange : () => {};
	    this.removeRange = main_core.Type.isFunction(options.removeRange) ? options.removeRange : () => {};
	    this.showReadOnlyPopup = main_core.Type.isFunction(options.showReadOnlyPopup) ? options.showReadOnlyPopup : () => {};
	    this.show = options.show;
	    this.deletable = false;
	  }
	  getRule() {
	    return this.rule;
	  }
	  settingPopupShown() {
	    const weekdaysPopupShown = main_core.Dom.hasClass(this.layout.weekdaysSelect, '--active');
	    const startPopupShown = main_core.Dom.hasClass(this.layout.fromTimeSelect, '--active');
	    const endPopupShown = main_core.Dom.hasClass(this.layout.toTimeSelect, '--active');
	    return weekdaysPopupShown || startPopupShown || endPopupShown;
	  }
	  getWrap() {
	    return this.layout.wrap;
	  }
	  disableAnimation() {
	    this.show = false;
	  }
	  render() {
	    this.layout.wrap = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar-sharing__settings-range">
				${0}
				<div class="calendar-sharing__settings-time-interval">
					${0}
					<div class="calendar-sharing__settings-dash"></div>
					${0}
				</div>
				${0}
			</div>
		`), this.renderWeekdaysSelect(), this.renderTimeFromSelect(), this.renderTimeToSelect(), this.renderButton());
	    if (this.show) {
	      main_core.Dom.addClass(this.layout.wrap, '--animate-show');
	      setTimeout(() => main_core.Dom.removeClass(this.layout.wrap, '--animate-show'), 300);
	    }
	    return this.layout.wrap;
	  }
	  renderButton() {
	    this.layout.button = this.getButton();
	    return this.layout.button;
	  }
	  update() {
	    const maxFrom = 24 * 60 - this.getSlotSize();
	    if (this.rule.from > maxFrom) {
	      this.rule.from = maxFrom;
	      if (this.layout.fromTimeSelect) {
	        this.layout.fromTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.from));
	      }
	      this.rule.to = 24 * 60;
	      if (this.layout.toTimeSelect) {
	        this.layout.toTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.to));
	      }
	    } else {
	      this.updateTo();
	    }
	  }
	  updateTo() {
	    const minToMinutes = this.rule.from + this.getSlotSize();
	    if (minToMinutes > this.rule.to) {
	      this.rule.to = minToMinutes;
	      if (this.layout.toTimeSelect) {
	        this.layout.toTimeSelect.innerHTML = this.formatAmPmSpan(this.formatMinutes(this.rule.to));
	      }
	    }
	  }
	  setDeletable(isDeletable) {
	    this.deletable = isDeletable;
	    const button = this.layout.button;
	    this.layout.button = this.getButton();
	    button == null ? void 0 : button.replaceWith(this.layout.button);
	  }
	  getButton() {
	    let button;
	    if (this.deletable) {
	      button = main_core.Tag.render(_t2$1 || (_t2$1 = _$2`
				<div class="calendar-sharing__settings-delete"></div>
			`));
	      main_core.Event.bind(button, 'click', this.onDeleteButtonClickHandler.bind(this));
	    } else {
	      button = main_core.Tag.render(_t3 || (_t3 = _$2`
				<div class="calendar-sharing__settings-add"></div>
			`));
	      main_core.Event.bind(button, 'click', this.onAddButtonClickHandler.bind(this));
	    }
	    return button;
	  }
	  onDeleteButtonClickHandler() {
	    if (this.readOnly) {
	      this.showReadOnlyPopup(this.layout.button);
	    } else {
	      this.remove();
	    }
	  }
	  onAddButtonClickHandler() {
	    if (this.readOnly) {
	      this.showReadOnlyPopup(this.layout.button);
	    } else {
	      this.addRange(this);
	    }
	  }
	  hideButton() {
	    main_core.Dom.addClass(this.layout.button, '--hidden');
	  }
	  showButton() {
	    main_core.Dom.removeClass(this.layout.button, '--hidden');
	  }
	  remove() {
	    if (!this.removeRange(this)) {
	      return;
	    }
	    main_core.Dom.addClass(this.layout.wrap, '--animate-remove');
	    setTimeout(() => this.layout.wrap.remove(), 300);
	  }
	  renderWeekdaysSelect() {
	    const weekdaysLoc = calendar_util.Util.getWeekdaysLoc().map((loc, index) => {
	      return {
	        loc,
	        index,
	        active: this.rule.weekdays.includes(index)
	      };
	    });
	    weekdaysLoc.push(...weekdaysLoc.splice(0, this.weekStart));
	    this.layout.weekdaysSelect = main_core.Tag.render(_t4 || (_t4 = _$2`
			<div
				class="calendar-sharing__settings-weekdays calendar-sharing__settings-select calendar-sharing__settings-select-arrow"
				title="${0}"
			>
				${0}
			</div>
		`), this.formatWeekdays(false), this.getWeekdaysTitle());
	    main_core.Event.bind(this.layout.weekdaysSelect, 'click', this.onWeekdaysSelectClickHandler.bind(this));
	    this.weekdays = weekdaysLoc.map(weekdayLoc => this.createWeekday(weekdayLoc));
	    this.weekdaysMenu = new main_popup.Popup({
	      id: `calendar-sharing-settings-weekdays${Date.now()}`,
	      bindElement: this.layout.weekdaysSelect,
	      content: main_core.Tag.render(_t5 || (_t5 = _$2`
				<div class="calendar-sharing__settings-popup-weekdays">
					${0}
				</div>
			`), this.weekdays.map(weekday => weekday.render())),
	      autoHide: true,
	      closeByEsc: true,
	      angle: {
	        position: 'top',
	        offset: 105
	      },
	      autoHideHandler: () => this.weekdaysMenu.canBeClosed,
	      events: {
	        onPopupShow: () => main_core.Dom.addClass(this.layout.weekdaysSelect, '--active'),
	        onPopupClose: () => main_core.Dom.removeClass(this.layout.weekdaysSelect, '--active')
	      }
	    });
	    this.weekdaysMenu.canBeClosed = true;
	    return this.layout.weekdaysSelect;
	  }
	  createWeekday(weekdayLoc) {
	    return new Weekday({
	      name: weekdayLoc.loc,
	      index: weekdayLoc.index,
	      active: weekdayLoc.active,
	      onSelected: () => {
	        if (this.rule.weekdays.includes(weekdayLoc.index)) {
	          return;
	        }
	        this.rule.weekdays.push(weekdayLoc.index);
	        this.rule.weekdays = this.getSortedWeekdays(this.rule.weekdays);
	        this.layout.weekdaysSelect.title = this.formatWeekdays();
	        this.layout.weekdaysSelect.innerText = this.getWeekdaysTitle();
	        this.ruleUpdated();
	      },
	      onDiscarded: () => {
	        const index = this.rule.weekdays.indexOf(weekdayLoc.index);
	        if (index < 0) {
	          return;
	        }
	        this.rule.weekdays.splice(index, 1);
	        this.layout.weekdaysSelect.title = this.formatWeekdays();
	        this.layout.weekdaysSelect.innerText = this.getWeekdaysTitle();
	        this.ruleUpdated();
	      },
	      canBeDiscarded: () => this.rule.weekdays.length > 1,
	      onMouseDown: this.onWeekdayMouseDown.bind(this)
	    });
	  }
	  onWeekdayMouseDown(event, currentWeekday) {
	    this.weekdaysMenu.canBeClosed = false;
	    const startX = event.clientX;
	    const select = currentWeekday.active;
	    this.controllableWeekdays = [];
	    this.collectIntersectedWeekdays = e => {
	      for (const weekday of this.weekdays) {
	        const right = weekday.wrap.getBoundingClientRect().right;
	        const left = weekday.wrap.getBoundingClientRect().left;
	        if (startX > right && e.clientX < right || startX < left && e.clientX > left || left < startX && startX < right) {
	          if (!this.controllableWeekdays.includes(weekday)) {
	            this.controllableWeekdays.push(weekday);
	          }
	          weekday.intersected = true;
	        }
	      }
	    };
	    this.onMouseMove = e => {
	      this.controllableWeekdays.forEach(controllableWeekday => {
	        controllableWeekday.intersected = false;
	      });
	      this.collectIntersectedWeekdays(e);
	      for (const weekday of this.controllableWeekdays) {
	        if (weekday.intersected && select || !weekday.intersected && !select) {
	          weekday.select();
	        } else {
	          weekday.discard();
	        }
	      }
	    };
	    main_core.Event.bind(document, 'mousemove', this.onMouseMove);
	    main_core.Event.bind(document, 'mouseup', () => {
	      main_core.Event.unbind(document, 'mousemove', this.onMouseMove);
	      setTimeout(() => {
	        this.weekdaysMenu.canBeClosed = true;
	      }, 0);
	    });
	  }
	  renderTimeFromSelect() {
	    const fromFormatted = this.formatMinutes(this.rule.from);
	    this.layout.fromTimeSelect = this.renderTimeSelect(fromFormatted, {
	      isSelected: minutes => this.rule.from === minutes,
	      onItemSelected: minutes => {
	        this.rule.from = minutes;
	        this.updateTo();
	      },
	      getMaxMinutes: () => 24 * 60 - this.getSlotSize()
	    }, 'calendar-sharing-settings-range-from');
	    return this.layout.fromTimeSelect;
	  }
	  renderTimeToSelect() {
	    const toFormatted = this.formatMinutes(this.rule.to);
	    this.layout.toTimeSelect = this.renderTimeSelect(toFormatted, {
	      isSelected: minutes => this.rule.to === minutes,
	      onItemSelected: minutes => {
	        this.rule.to = minutes;
	      },
	      getMinMinutes: () => this.rule.from + this.getSlotSize()
	    }, 'calendar-sharing-settings-range-to');
	    return this.layout.toTimeSelect;
	  }
	  renderTimeSelect(time, callbacks, dataId) {
	    const timeSelect = main_core.Tag.render(_t6 || (_t6 = _$2`
			<div
				class="calendar-sharing__settings-select calendar-sharing__settings-time calendar-sharing__settings-select-arrow"
				data-id="${0}"
			>
				${0}
			</div>
		`), dataId, this.formatAmPmSpan(time));
	    main_core.Event.bind(timeSelect, 'click', () => this.onTimeSelectClickHandler(timeSelect, callbacks));
	    return timeSelect;
	  }
	  showTimeMenu(timeSelect, callbacks) {
	    const timeStamps = [];
	    for (let hour = 0; hour <= 24; hour++) {
	      if ((!main_core.Type.isFunction(callbacks.getMinMinutes) || hour * 60 >= callbacks.getMinMinutes()) && (!main_core.Type.isFunction(callbacks.getMaxMinutes) || hour * 60 <= callbacks.getMaxMinutes())) {
	        timeStamps.push({
	          minutes: hour * 60,
	          label: this.formatAmPmSpan(calendar_util.Util.formatTime(hour, 0))
	        });
	      }
	      if (hour !== 24 && (!main_core.Type.isFunction(callbacks.getMinMinutes) || hour * 60 + 30 >= callbacks.getMinMinutes()) && (!main_core.Type.isFunction(callbacks.getMaxMinutes) || hour * 60 + 30 <= callbacks.getMaxMinutes())) {
	        timeStamps.push({
	          minutes: hour * 60 + 30,
	          label: this.formatAmPmSpan(calendar_util.Util.formatTime(hour, 30))
	        });
	      }
	    }
	    let timeMenu;
	    const items = timeStamps.map(timeStamp => {
	      return {
	        html: main_core.Tag.render(_t7 || (_t7 = _$2`
					<div class="calendar-sharing__am-pm-container">${0}</div>
				`), timeStamp.label),
	        className: callbacks.isSelected(timeStamp.minutes) ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
	        onclick: () => {
	          timeSelect.innerHTML = timeStamp.label;
	          callbacks.onItemSelected(timeStamp.minutes);
	          this.ruleUpdated();
	          timeMenu.close();
	        }
	      };
	    });
	    timeMenu = main_popup.MenuManager.create({
	      id: `calendar-sharing-settings-time-menu${Date.now()}`,
	      className: 'calendar-sharing-settings-time-menu',
	      bindElement: timeSelect,
	      items,
	      autoHide: true,
	      closeByEsc: true,
	      events: {
	        onShow: () => main_core.Dom.addClass(timeSelect, '--active'),
	        onClose: () => main_core.Dom.removeClass(timeSelect, '--active')
	      },
	      maxHeight: 300,
	      minWidth: timeSelect.offsetWidth
	    });
	    timeMenu.show();
	    const timezonesPopup = timeMenu.getPopupWindow();
	    const popupContent = timezonesPopup.getContentContainer();
	    const selectedTimezoneItem = popupContent.querySelector('.menu-popup-item.--selected');
	    popupContent.scrollTop = selectedTimezoneItem.offsetTop - selectedTimezoneItem.offsetHeight * 2;
	  }
	  onWeekdaysSelectClickHandler() {
	    if (this.readOnly) {
	      this.showReadOnlyPopup(this.layout.weekdaysSelect);
	    } else {
	      this.weekdaysMenu.show();
	    }
	  }
	  onTimeSelectClickHandler(timeSelect, callbacks) {
	    if (this.readOnly) {
	      this.showReadOnlyPopup(timeSelect);
	    } else if (!main_core.Dom.hasClass(timeSelect, '--active')) {
	      this.showTimeMenu(timeSelect, callbacks);
	    }
	  }
	  formatAmPmSpan(time) {
	    return time.toLowerCase().replace(/(am|pm)/g, '<span class="calendar-sharing__settings-time-am-pm">$1</span>');
	  }
	  formatMinutes(minutes) {
	    const date = new Date(calendar_util.Util.parseDate('01.01.2000').getTime() + minutes * 60 * 1000);
	    return calendar_util.Util.formatTime(date);
	  }
	  getWeekdaysTitle() {
	    if ([...this.rule.weekdays].sort().toString() === this.workDays.sort().toString()) {
	      return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_WORKDAYS_MSGVER_1');
	    }
	    return this.formatWeekdays();
	  }
	  formatWeekdays(singleDay = true) {
	    if (singleDay && this.rule.weekdays.length === 1) {
	      return calendar_util.Util.getWeekdaysLoc(true)[this.rule.weekdays[0]];
	    }
	    const weekdaysLoc = calendar_util.Util.getWeekdaysLoc();
	    return this.rule.weekdays.map(w => weekdaysLoc[w]).reduce((a, b) => `${a}, ${b}`, '');
	  }
	  getSortedWeekdays(weekdays) {
	    return weekdays.map(w => w < this.weekStart ? w + 10 : w).sort((a, b) => a - b).map(w => w % 10);
	  }
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$2,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8,
	  _t9;
	class Settings {
	  constructor(options) {
	    this.AVAILABLE_INTERVALS = [30, 45, 60, 90, 120, 180];
	    this.MAX_RANGES = 5;
	    this.layout = {};
	    this.readOnly = options.readOnly;
	    this.collapsed = !options.readOnly && options.collapsed;
	    if (!main_core.Type.isBoolean(options.collapsed)) {
	      this.notExpandable = true;
	    }
	    this.workTimeStart = options.workTimeStart;
	    this.workTimeEnd = options.workTimeEnd;
	    this.workDays = options.workDays;
	    this.weekStart = options.weekStart;
	    this.rule = {
	      slotSize: options.rule.slotSize
	    };
	    this.ranges = [];
	    for (const rangeOptions of options.rule.ranges) {
	      this.ranges.push(this.getRange({
	        ...rangeOptions,
	        show: false
	      }));
	    }
	    if (!main_core.Type.isArrayFilled(this.ranges)) {
	      this.ranges = [this.getRange()];
	    }
	    this.sortRanges();
	    this.updateRanges();
	  }
	  getRule() {
	    const ranges = this.ranges.map(range => range.getRule());
	    ranges.sort((a, b) => this.compareRanges(a, b));
	    return JSON.parse(JSON.stringify({
	      ranges,
	      ...this.rule
	    }));
	  }
	  sortRanges() {
	    this.ranges.sort((a, b) => this.compareRanges(a.getRule(), b.getRule()));
	  }
	  compareRanges(range1, range2) {
	    const weekdaysWeight1 = this.getWeekdaysWeight(range1.weekdays);
	    const weekdaysWeight2 = this.getWeekdaysWeight(range2.weekdays);
	    if (weekdaysWeight1 !== weekdaysWeight2) {
	      return weekdaysWeight1 - weekdaysWeight2;
	    }
	    if (range1.from !== range2.from) {
	      return range1.from - range2.from;
	    }
	    return range1.to - range2.to;
	  }
	  getWeekdaysWeight(weekdays) {
	    return weekdays.map(w => w < this.weekStart ? w + 10 : w).sort((a, b) => a - b).reduce((accumulator, w, index) => {
	      return accumulator + w * 10 ** (10 - index);
	    }, 0);
	  }
	  settingPopupShown() {
	    var _this$ranges$filter, _this$readOnlyPopup;
	    const rangesWithPopup = (_this$ranges$filter = this.ranges.filter(range => range.settingPopupShown())) != null ? _this$ranges$filter : [];
	    const rangePopupShown = rangesWithPopup.length > 0;
	    const slotSizePopupShown = main_core.Dom.hasClass(this.layout.slotSizeSelect, '--active');
	    const readOnlyPopupShown = (_this$readOnlyPopup = this.readOnlyPopup) == null ? void 0 : _this$readOnlyPopup.isShown();
	    return rangePopupShown || slotSizePopupShown || readOnlyPopupShown;
	  }
	  render() {
	    const readOnlyClass = this.readOnly ? '--read-only' : '';
	    const expandedClass = this.collapsed ? '--hide' : '';
	    this.layout.wrap = main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div class="calendar-sharing__settings ${0} ${0}">
				${0}
				${0}
			</div>
		`), readOnlyClass, expandedClass, this.renderHeader(), this.renderRule());
	    return this.layout.wrap;
	  }
	  renderHeader() {
	    return main_core.Tag.render(_t2$2 || (_t2$2 = _$3`
			<div class="calendar-sharing__settings-header-container">
				<div class="calendar-sharing__settings-header">
					<div class="calendar-sharing__settings-title">
						${0}
					</div>
					${0}
				</div>
				<div class="calendar-sharing__settings-header-button">
					${0}
				</div>
			</div>
		`), main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_TITLE_V2'), this.renderSubtitle(), this.renderExpandRuleButton());
	  }
	  renderSubtitle() {
	    this.layout.subtitle = main_core.Tag.render(_t3$1 || (_t3$1 = _$3`
			<div class="calendar-sharing__settings-subtitle">
				${0}
			</div>
		`), this.getSubtitleText());
	    return this.layout.subtitle;
	  }
	  updateSubtitle() {
	    if (!this.layout.subtitle) {
	      return;
	    }
	    this.layout.subtitle.innerText = this.getSubtitleText();
	  }
	  getSubtitleText() {
	    if (this.isDefaultRule()) {
	      return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_DEFAULT');
	    }
	    return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_PERSONAL');
	  }
	  renderExpandRuleButton() {
	    if (this.readOnly || this.notExpandable) {
	      return '';
	    }
	    this.layout.expandRuleArrow = main_core.Tag.render(_t4$1 || (_t4$1 = _$3`
			<div class="calendar-sharing__settings-select-arrow ${0}"></div>
		`), this.collapsed ? '' : '--active');
	    this.layout.expandRuleButton = main_core.Tag.render(_t5$1 || (_t5$1 = _$3`
			<div class="calendar-sharing__settings-expand">
				${0}
			</div>
		`), this.layout.expandRuleArrow);
	    main_core.Event.bind(this.layout.expandRuleButton, 'click', this.toggle.bind(this));
	    return this.layout.expandRuleButton;
	  }
	  renderRule() {
	    this.layout.rule = main_core.Tag.render(_t6$1 || (_t6$1 = _$3`
			<div class="calendar-sharing__settings-rule">
				${0}
				<div class="calendar-sharing__settings-slotSize">
					<span class="calendar-sharing__settings-slotSize-title">${0}</span>
					${0}
				</div>
			</div>
		`), this.renderRanges(), main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SLOT_SIZE_V2'), this.getSettingsSlotSizeSelect());
	    return this.layout.rule;
	  }
	  toggle() {
	    this.updateRuleHeight();
	    setTimeout(() => {
	      main_core.Dom.toggleClass(this.layout.wrap, '--hide');
	      main_core.Dom.toggleClass(this.layout.expandRuleArrow, '--active');
	      this.updateSharingSettingsCollapsedAction(main_core.Dom.hasClass(this.layout.wrap, '--hide'));
	    }, 0);
	  }
	  updateSharingSettingsCollapsedAction(isCollapsed) {
	    BX.ajax.runAction('calendar.api.sharingajax.updateSharingSettingsCollapsed', {
	      data: {
	        collapsed: isCollapsed ? 'Y' : 'N'
	      }
	    });
	  }
	  renderRanges() {
	    this.ranges.forEach(range => range.disableAnimation());
	    const rangesContainer = main_core.Tag.render(_t7$1 || (_t7$1 = _$3`
			<div class="calendar-sharing__settings-range-list">
				${0}
			</div>
		`), this.ranges.map(range => range.render()));
	    if (this.ranges.length === this.MAX_RANGES) {
	      this.ranges[0].hideButton();
	    }
	    if (main_core.Type.isDomNode(this.layout.rangesContainer)) {
	      this.layout.rangesContainer.replaceWith(rangesContainer);
	    }
	    this.layout.rangesContainer = rangesContainer;
	    return rangesContainer;
	  }
	  getRange(rangeOptions) {
	    var _this$ranges;
	    const date = new Date().toDateString();
	    const from = new Date(`${date} ${`${this.workTimeStart}`.replace('.', ':')}:00`);
	    const to = new Date(`${date} ${`${this.workTimeEnd}`.replace('.', ':')}:00`);
	    const isNotFirst = ((_this$ranges = this.ranges) == null ? void 0 : _this$ranges.length) >= 1;
	    return new Range({
	      getSlotSize: () => this.rule.slotSize,
	      from: this.getMinutesFromDate(from),
	      to: this.getMinutesFromDate(to),
	      weekdays: this.workDays,
	      weekStart: this.weekStart,
	      workDays: this.workDays,
	      addRange: range => this.addRange(range),
	      removeRange: range => this.removeRange(range),
	      showReadOnlyPopup: node => this.showReadOnlyPopup(node),
	      ruleUpdated: () => this.ruleUpdated(),
	      show: isNotFirst,
	      readOnly: this.readOnly,
	      ...rangeOptions
	    });
	  }
	  addRange(afterRange) {
	    if (this.ranges.length >= this.MAX_RANGES) {
	      return;
	    }
	    const newRange = this.getRange();
	    this.ranges.push(newRange);
	    afterRange.getWrap().after(newRange.render());
	    this.updateRanges();
	  }
	  removeRange(deletedRange) {
	    if (this.ranges.length <= 1) {
	      return false;
	    }
	    this.ranges = this.ranges.filter(range => range !== deletedRange);
	    this.updateRanges();
	    return true;
	  }
	  getSettingsSlotSizeSelect() {
	    this.layout.slotSizeText = main_core.Tag.render(_t8 || (_t8 = _$3`
			<span class="calendar-sharing__settings-select-link">
				${0}
			</span>
		`), calendar_util.Util.formatDuration(this.rule.slotSize));
	    this.layout.slotSizeSelect = main_core.Tag.render(_t9 || (_t9 = _$3`
			<span class="calendar-sharing__settings-select-arrow --small-arrow">
				${0}
			</span>
		`), this.layout.slotSizeText);
	    main_core.Event.bind(this.layout.slotSizeSelect, 'click', this.slotSizeSelectClickHandler.bind(this));
	    const items = this.AVAILABLE_INTERVALS.map(minutes => {
	      return {
	        text: calendar_util.Util.formatDuration(minutes),
	        onclick: () => {
	          this.rule.slotSize = minutes;
	          this.layout.slotSizeText.innerHTML = calendar_util.Util.formatDuration(this.rule.slotSize);
	          this.slotSizeMenu.close();
	          this.updateRanges();
	        }
	      };
	    });
	    this.slotSizeMenu = main_popup.MenuManager.create({
	      id: `calendar-sharing-settings-slotSize${Date.now()}`,
	      bindElement: this.layout.slotSizeSelect,
	      items,
	      closeByEsc: true,
	      events: {
	        onShow: () => main_core.Dom.addClass(this.layout.slotSizeSelect, '--active'),
	        onClose: () => main_core.Dom.removeClass(this.layout.slotSizeSelect, '--active')
	      }
	    });
	    return this.layout.slotSizeSelect;
	  }
	  updateRanges() {
	    for (const range of this.ranges.slice(0, -1)) {
	      range.setDeletable(true);
	      range.update();
	    }
	    const lastRange = this.ranges.slice(-1)[0];
	    lastRange.setDeletable(this.ranges.length === 5);
	    lastRange.update();
	    this.ruleUpdated();
	  }
	  ruleUpdated() {
	    this.updateSubtitle();
	    this.removeRuleHeight();
	  }
	  updateRuleHeight() {
	    main_core.Dom.style(this.layout.rule, 'height', `${this.calculateRuleHeight()}px`);
	  }
	  calculateRuleHeight() {
	    const topMarginHeight = 10;
	    const bottomMarginHeight = 2;
	    const marginsHeight = topMarginHeight + bottomMarginHeight;
	    const slotSizeHeight = 15;
	    const rangeHeight = 45;
	    return rangeHeight * this.ranges.length + (marginsHeight + slotSizeHeight);
	  }
	  removeRuleHeight() {
	    main_core.Dom.style(this.layout.rule, 'height', null);
	  }
	  slotSizeSelectClickHandler() {
	    if (this.readOnly) {
	      this.showReadOnlyPopup(this.layout.slotSizeSelect);
	    } else {
	      this.slotSizeMenu.show();
	    }
	  }
	  showReadOnlyPopup(pivotNode) {
	    this.closeReadOnlyPopup();
	    this.getReadOnlyPopup(pivotNode).show();
	  }
	  getReadOnlyPopup(pivotNode) {
	    this.readOnlyPopup = new main_popup.Popup({
	      bindElement: pivotNode,
	      className: 'calendar-sharing__settings-read-only-hint',
	      content: main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT'),
	      angle: {
	        offset: 0
	      },
	      width: 300,
	      offsetLeft: pivotNode.offsetWidth / 2,
	      darkMode: true,
	      autoHide: true
	    });
	    main_core.Event.bind(this.readOnlyPopup.popupContainer, 'click', () => this.closeReadOnlyPopup());
	    clearTimeout(this.closePopupTimeout);
	    this.closePopupTimeout = setTimeout(() => this.closeReadOnlyPopup(), 3000);
	    return this.readOnlyPopup;
	  }
	  closeReadOnlyPopup() {
	    var _this$readOnlyPopup2;
	    (_this$readOnlyPopup2 = this.readOnlyPopup) == null ? void 0 : _this$readOnlyPopup2.destroy();
	  }
	  getMinutesFromDate(date) {
	    const parsedTime = calendar_util.Util.parseTime(main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), date / 1000));
	    return parsedTime.h * 60 + parsedTime.m;
	  }
	  isDefaultRule() {
	    return !this.isDifferentFrom(this.getDefaultRule());
	  }
	  isDifferentFrom(anotherRule) {
	    return !this.objectsEqual(anotherRule, this.getRule());
	  }
	  getChanges() {
	    const defaultRule = this.getDefaultRule();
	    const rule = this.getRule();
	    const sizeChanged = rule.slotSize !== defaultRule.slotSize;
	    const daysChanged = JSON.stringify(rule.ranges) !== JSON.stringify(defaultRule.ranges);
	    const changes = [];
	    if (daysChanged) {
	      changes.push(calendar_sharing_analytics.Analytics.ruleChanges.custom_days);
	    }
	    if (sizeChanged) {
	      changes.push(calendar_sharing_analytics.Analytics.ruleChanges.custom_length);
	    }
	    return changes;
	  }
	  getDefaultRule() {
	    return {
	      slotSize: 60,
	      ranges: [this.getRange().getRule()]
	    };
	  }
	  objectsEqual(obj1, obj2) {
	    return JSON.stringify(this.sortKeys(obj1)) === JSON.stringify(this.sortKeys(obj2));
	  }
	  sortKeys(object) {
	    return Object.keys(object).sort().reduce((obj, key) => {
	      obj[key] = object[key];
	      return obj;
	    }, {});
	  }
	}

	let _$4 = t => t,
	  _t$4;
	var _bindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _popup$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	class HintInfo {
	  constructor(props) {
	    Object.defineProperty(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement] = props.bindElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = new main_popup.Popup({
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement],
	      bindOptions: {
	        position: 'top'
	      },
	      angle: {
	        offset: babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement].offsetWidth / 2 + 24
	      },
	      borderRadius: '24px',
	      width: 425,
	      content: this.getContent(),
	      animation: 'fading-slide'
	    });
	  }
	  getContent() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper = main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="calendar-sharing__user-selector-hint-wrapper">
					<div class="calendar-sharing__user-selector-hint-text-wrapper">
						<div class="calendar-sharing__user-selector-hint-text-title">
							${0}
						</div>
						<div class="calendar-sharing__user-selector-hint-text-desc">
							${0}
						</div>
					</div>
					<div class="calendar-sharing__user-selector-hint-icon"></div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_HINT_TITLE'), main_core.Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_HINT_DESC'));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper;
	  }
	  show() {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1]) == null ? void 0 : _babelHelpers$classPr.show();
	  }
	  close() {
	    var _babelHelpers$classPr2;
	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1]) == null ? void 0 : _babelHelpers$classPr2.close();
	  }
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$2;
	var _layout$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _userSelectorDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userSelectorDialog");
	var _selectedEntityList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedEntityList");
	var _selectedEntityNodeList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedEntityNodeList");
	var _defaultUserEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultUserEntity");
	var _isOpened = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOpened");
	var _onMembersAdded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMembersAdded");
	class UserSelector {
	  constructor(props = {}) {
	    Object.defineProperty(this, _layout$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userSelectorDialog, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedEntityList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedEntityNodeList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _defaultUserEntity, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isOpened, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onMembersAdded, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity] = props.userInfo || {};
	    babelHelpers.classPrivateFieldLooseBase(this, _isOpened)[_isOpened] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _onMembersAdded)[_onMembersAdded] = props.onMembersAdded;
	    this.openEntitySelector = this.openEntitySelector.bind(this);
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper = main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="calendar-sharing__user-selector-main">
					<div class="calendar-sharing__user-selector-title">
						${0}
					</div>
					${0}
				</div>
			`), this.renderTitle(), this.renderUserSelectorWrapper());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper;
	  }
	  renderTitle() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].title) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].title = main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
				<div class="calendar-sharing__user-selector-title-text">
					${0}
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_TITLE_V2'));
	      const infoNotify = babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].title.querySelector('[ data-role="calendar-sharing_popup-joint-slots"]');
	      if (infoNotify) {
	        let hintInfo;
	        let timer;
	        main_core.Event.bind(infoNotify, 'mouseenter', () => {
	          timer = setTimeout(() => {
	            if (!hintInfo) {
	              hintInfo = new HintInfo({
	                bindElement: infoNotify
	              });
	            }
	            hintInfo.show();
	          }, 1000);
	        });
	        main_core.Event.bind(infoNotify, 'mouseleave', () => {
	          clearTimeout(timer);
	          if (hintInfo) {
	            hintInfo.close();
	          }
	        });
	      }
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].title;
	  }
	  renderUserSelectorWrapper() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelectorWrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelectorWrapper = main_core.Tag.render(_t3$2 || (_t3$2 = _$5`
				<div class="calendar-sharing__user-selector-wrapper">
					${0}
					<div class="calendar-sharing__user-selector-add">
				</div>
			`), this.renderUserSelector());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelectorWrapper, 'click', this.openEntitySelector);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelectorWrapper;
	  }
	  renderUserSelector() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector) {
	      const entityNode = this.getDefaultEntityNode();
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector = main_core.Tag.render(_t4$2 || (_t4$2 = _$5`
				<div class="calendar-sharing__user-selector-container" data-id="calendar-sharing-members">
					${0}
				</div>
			`), entityNode);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector;
	  }
	  getDefaultEntityNode() {
	    const entityNode = this.renderUserEntity(babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity]);
	    const key = this.getEntityKey(babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity].id);
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList][key] = babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity];
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key] = entityNode;
	    return entityNode;
	  }
	  renderUserEntity(entity) {
	    if (this.hasAvatar(entity.avatar)) {
	      return main_core.Tag.render(_t5$2 || (_t5$2 = _$5`
				<div class="calendar-sharing__user-selector-entity-container">
					<img class="calendar-sharing__user-selector-entity" title="${0}" src="${0}" alt="">
				</div>
			`), main_core.Text.encode(entity.name), entity.avatar);
	    }
	    return main_core.Tag.render(_t6$2 || (_t6$2 = _$5`
			<div class="ui-icon ui-icon-common-user calendar-sharing__user-selector-entity" title="${0}"><i></i></div>
		`), main_core.Text.encode(entity == null ? void 0 : entity.name));
	  }
	  hasAvatar(avatar) {
	    return avatar && avatar !== '/bitrix/images/1.gif';
	  }
	  openEntitySelector() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector) {
	      return;
	    }
	    const preselectedItem = ['user', babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity].id];
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog] = new ui_entitySelector.Dialog({
	        width: 340,
	        targetNode: babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector,
	        context: 'CALENDAR_SHARING',
	        preselectedItems: [preselectedItem],
	        enableSearch: true,
	        zIndex: 4200,
	        events: {
	          'Item:onSelect': event => {
	            this.onUserSelectorSelect(event);
	          },
	          'Item:onDeselect': event => {
	            this.onUserSelectorDeselect(event);
	          },
	          'onHide': () => {
	            if (this.hasChanges()) {
	              babelHelpers.classPrivateFieldLooseBase(this, _onMembersAdded)[_onMembersAdded]();
	            }
	          }
	        },
	        entities: [{
	          id: 'user',
	          options: {
	            intranetUsersOnly: true,
	            emailUsers: false,
	            inviteEmployeeLink: false,
	            inviteGuestLink: false,
	            analyticsSource: 'calendar'
	          },
	          filters: [{
	            id: 'calendar.jointSharingFilter'
	          }]
	        }]
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog].show();
	  }
	  isUserSelectorDialogOpened() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog]) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog].isOpen();
	    }
	    return false;
	  }
	  onUserSelectorSelect(event) {
	    var _item$customData$get;
	    const item = event.data.item;
	    const entity = {
	      id: item.id,
	      avatar: item.avatar,
	      name: `${item.customData.get('name')} ${(_item$customData$get = item.customData.get('lastName')) != null ? _item$customData$get : ''}`.trim()
	    };
	    const entityNode = this.renderUserEntity(entity);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector) {
	      main_core.Dom.append(entityNode, babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector);
	    }
	    const key = this.getEntityKey(entity.id);
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList][key] = entity;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key] = entityNode;
	  }
	  onUserSelectorDeselect(event) {
	    const item = event.data.item;
	    const key = this.getEntityKey(item.id);
	    const entityNode = babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key];
	    if (entityNode) {
	      main_core.Dom.remove(entityNode);
	      delete babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList][key];
	      delete babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key];
	    }
	  }
	  clearSelectedUsers() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector);
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList] = {};
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList] = {};
	      const entityNode = this.getDefaultEntityNode();
	      main_core.Dom.append(entityNode, babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].userSelector);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog].destroy();
	      babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog] = null;
	    }
	  }
	  hasChanges() {
	    return this.getPeopleCount() > 1;
	  }
	  getPeopleCount() {
	    return Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList]).length;
	  }
	  getSelectedUserIdList() {
	    const result = [];
	    Object.values(babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList]).forEach(entity => {
	      result.push(entity.id);
	    });
	    return result;
	  }
	  getEntityKey(id) {
	    return `user-${id}`;
	  }
	}

	let _$6 = t => t,
	  _t$6,
	  _t2$4,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$3,
	  _t7$2,
	  _t8$1,
	  _t9$1,
	  _t10;
	const MAX_AVATAR_COUNT = 4;
	var _props = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("props");
	var _layout$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _avatarPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("avatarPopup");
	var _deletePopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deletePopup");
	class ListItem {
	  constructor(props) {
	    Object.defineProperty(this, _props, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _avatarPopup, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _deletePopup, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _props)[_props] = props;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup] = null;
	    this.openAvatarList = this.openAvatarList.bind(this);
	    this.onCopyButtonClick = this.onCopyButtonClick.bind(this);
	    this.onDeleteButtonClick = this.onDeleteButtonClick.bind(this);
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper = main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="calendar-sharing__dialog-link-list-item">
					${0}
					${0}
					${0}
					${0}
				</div>
			`), this.renderAvatarContainer(), this.renderDate(), this.renderCopyButton(), this.renderDeleteButton());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper;
	  }
	  renderAvatarContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarContainer) {
	      const showMoreIcon = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length > MAX_AVATAR_COUNT;
	      const moreCounter = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length - MAX_AVATAR_COUNT;
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarContainer = main_core.Tag.render(_t2$4 || (_t2$4 = _$6`
				<div class="calendar-sharing__dialog-link-list-item-avatar-container">
					${0}
					${0}
					${0}
				</div>
			`), this.renderAvatar(babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].userInfo), babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.slice(0, MAX_AVATAR_COUNT).map(member => this.renderAvatar(member)), showMoreIcon ? this.renderMore(moreCounter) : null);
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarContainer, 'click', this.openAvatarList);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarContainer;
	  }
	  renderAvatar(user) {
	    var _user$lastName;
	    const name = `${user.name} ${(_user$lastName = user.lastName) != null ? _user$lastName : ''}`.trim();
	    if (this.hasAvatar(user.avatar)) {
	      return main_core.Tag.render(_t3$3 || (_t3$3 = _$6`
				<img class="calendar-sharing__dialog-link-list-item-avatar" title="${0}" alt="" src="${0}">
			`), main_core.Text.encode(name), user.avatar);
	    }
	    return main_core.Tag.render(_t4$3 || (_t4$3 = _$6`
			<div class="ui-icon ui-icon-common-user calendar-sharing__dialog-link-list-item-avatar" title="${0}"><i></i></div>
		`), main_core.Text.encode(name));
	  }
	  hasAvatar(avatar) {
	    return avatar && avatar !== '/bitrix/images/1.gif';
	  }
	  renderMore(counter) {
	    return main_core.Tag.render(_t5$3 || (_t5$3 = _$6`
			<div class="calendar-sharing__dialog-link-list-item-more">
				<div class="calendar-sharing__dialog-link-list-item-more-text">${0}</div>
			</div>
		`), '+' + counter);
	  }
	  openAvatarList() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup]) {
	      const uid = BX.util.getRandomString(6);
	      babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup] = main_popup.MenuManager.create({
	        id: 'calendar-sharing-dialog_' + uid,
	        bindElement: babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].avatarContainer,
	        bindOptions: {
	          position: 'top'
	        },
	        autoHide: true,
	        closeByEsc: true,
	        className: 'calendar-sharing__dialog-link-list-user-popup-container',
	        items: this.getAvatarPopupItems(),
	        maxHeight: 250,
	        maxWidth: 300
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup].getPopupWindow().subscribe('onClose', () => {
	        this.setPopupState(false);
	      });
	      const menuContainer = babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup].getMenuContainer();
	      let timeout;
	      main_core.Event.bind(menuContainer, 'mouseleave', () => {
	        clearTimeout(timeout);
	        timeout = setTimeout(() => {
	          this.closeAvatarList();
	        }, 500);
	      });
	      main_core.Event.bind(menuContainer, 'mouseenter', () => {
	        clearTimeout(timeout);
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup].show();
	    this.setPopupState(true);
	  }
	  closeAvatarList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup].close();
	    }
	  }
	  getAvatarPopupItems() {
	    const result = [];
	    result.push(this.getAvatarPopupItem(babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].userInfo));
	    babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.forEach(member => {
	      result.push(this.getAvatarPopupItem(member));
	    });
	    return result;
	  }
	  getAvatarPopupItem(user) {
	    var _user$lastName2;
	    const avatar = user.avatar;
	    const name = `${user.name} ${(_user$lastName2 = user.lastName) != null ? _user$lastName2 : ''}`.trim();
	    const userPath = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].pathToUser.replace('#USER_ID#', user.id);
	    return {
	      html: main_core.Tag.render(_t6$3 || (_t6$3 = _$6`
				<a href="${0}" target="_blank" class="calendar-sharing__dialog-link-list-user-popup-item">
					<span class="ui-icon ui-icon-common-user calendar-sharing__dialog-link-list-user-popup-item-avatar">
						<i style="${0}"></i>
					</span>
					<div class="calendar-sharing__dialog-link-list-user-popup-item-text">
						${0}
					</div>
				</a>
			`), userPath, this.hasAvatar(avatar) ? `background-image: url('${avatar}')` : '', main_core.Text.encode(name))
	    };
	  }
	  renderDate() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].date) {
	      const date = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].dateCreate ? new Date(babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].dateCreate) : new Date();
	      const formattedDate = calendar_util.Util.formatDate(date);
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].date = main_core.Tag.render(_t7$2 || (_t7$2 = _$6`
				<div class="calendar-sharing__dialog-link-list-item-date" title="${0}">${0}</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DATE_CREATE'), formattedDate);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].date;
	  }
	  renderCopyButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].copyButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Main.LINK_3,
	        size: 14
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].copyButton = main_core.Tag.render(_t8$1 || (_t8$1 = _$6`
				<div class="calendar-sharing__dialog-link-list-item-copy-container">
					${0}
					<div class="calendar-sharing__dialog-link-list-item-copy-text">${0}</div>
				</div>
			`), icon.render(), main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_COPY'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].copyButton, 'click', this.onCopyButtonClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].copyButton;
	  }
	  onCopyButtonClick() {
	    main_core_events.EventEmitter.emit('CalendarSharing:onJointLinkCopy', {
	      id: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].id,
	      shortUrl: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].shortUrl,
	      hash: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].hash,
	      members: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members
	    });
	  }
	  renderDeleteButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length) {
	      return main_core.Tag.render(_t9$1 || (_t9$1 = _$6`<div class="calendar-sharing__dialog-link-list-item-delete"></div>`));
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].deleteButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.CROSS_30,
	        size: 18
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].deleteButton = main_core.Tag.render(_t10 || (_t10 = _$6`
				<div class="calendar-sharing__dialog-link-list-item-delete">
					${0}
				</div>
			`), icon.render());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].deleteButton, 'click', this.onDeleteButtonClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].deleteButton;
	  }
	  onDeleteButtonClick() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup] = new ui_dialogs_messagebox.MessageBox({
	        title: main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DELETE_MESSAGE_TITLE_MSGVER_1'),
	        message: main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DELETE_MESSAGE_DESC_MSGVER_1'),
	        buttons: this.getDeletePopupButtons(),
	        popupOptions: {
	          autoHide: true,
	          closeByEsc: true,
	          draggable: false,
	          closeIcon: true,
	          minWidth: 365,
	          maxWidth: 385,
	          minHeight: 180
	        }
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup].show();
	    this.setPopupState(true);
	  }
	  getDeletePopupButtons() {
	    return [new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.DANGER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_DELETE'),
	      events: {
	        click: () => {
	          this.deleteLink();
	          babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup].close();
	          this.setPopupState(false);
	        }
	      }
	    }), new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
	      events: {
	        click: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup].close();
	          this.setPopupState(false);
	        }
	      }
	    })];
	  }
	  deleteLink() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper) {
	      BX.ajax.runAction('calendar.api.sharingajax.disableUserLink', {
	        data: {
	          hash: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].hash
	        }
	      });
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper, '--animate-delete');
	      setTimeout(() => {
	        main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper);
	      }, 300);
	      main_core_events.EventEmitter.emit('CalendarSharing:onJointLinkDelete', {
	        id: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].id
	      });
	    }
	  }
	  setPopupState(state) {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props]) == null ? void 0 : _babelHelpers$classPr.setListItemPopupState(state);
	  }
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$5,
	  _t3$4,
	  _t4$4,
	  _t5$4,
	  _t6$4,
	  _t7$3,
	  _t8$2;
	const DEFAULT_LIST_HEIGHT = 300;
	const LIST_PADDING_SUM = 45;
	var _props$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("props");
	var _layout$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _linkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linkList");
	var _popupOpenState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOpenState");
	var _sortByFrequentUse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sortByFrequentUse");
	var _pathToUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToUser");
	var _getSortingName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSortingName");
	class List {
	  constructor(props) {
	    Object.defineProperty(this, _getSortingName, {
	      value: _getSortingName2
	    });
	    Object.defineProperty(this, _props$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _linkList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popupOpenState, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _sortByFrequentUse, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pathToUser, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1] = props;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse] = props.sortJointLinksByFrequentUse;
	    this.getLinkListInfo();
	    this.setListItemPopupState = this.setListItemPopupState.bind(this);
	    this.eventSubscribe();
	  }
	  eventSubscribe() {
	    main_core_events.EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', event => {
	      this.onJointLinkCopy(event);
	    });
	    main_core_events.EventEmitter.subscribe('CalendarSharing:onJointLinkDelete', event => {
	      this.onJointLinkDelete(event);
	    });
	  }
	  getLinkListInfo() {
	    BX.ajax.runAction('calendar.api.sharingajax.getAllUserLink').then(response => {
	      if (response && response.data) {
	        babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList] = response.data.userLinks;
	        babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser] = response.data.pathToUser;
	        this.updateLinkList();
	        if (this.isListEmpty()) {
	          this.hideSortingButton();
	          return;
	        }
	        if (babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]) {
	          this.showSortingButton();
	        }
	      }
	    });
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper = main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="calendar-sharing__dialog-link-list-wrapper">
					${0}
					${0}
				</div>
			`), this.getTitleNode(), this.getListNode());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper;
	  }
	  getTitleNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].title) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].title = main_core.Tag.render(_t2$5 || (_t2$5 = _$7`
				<div class="calendar-sharing__dialog-link-list-title-wrapper">
					<div class="calendar-sharing__dialog-link-list-title">
						${0}
						<div class="calendar-sharing__dialog-link-list-title-text">
							${0}
						</div>
					</div>
					${0}
				</div>
			`), this.getChevronBackIcon(), main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_TITLE'), this.getSortingButton());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].title;
	  }
	  getChevronBackIcon() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].backButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.CHEVRON_LEFT,
	        size: 24
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].backButton = main_core.Tag.render(_t3$4 || (_t3$4 = _$7`
				<div class="calendar-sharing__dialog-link-list-back-button">
					${0}
				</div>
			`), icon.render());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].backButton, 'click', this.close.bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].backButton;
	  }
	  getSortingButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.SORT,
	        size: 14,
	        color: '#2066b0'
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton = main_core.Tag.render(_t4$4 || (_t4$4 = _$7`
				<div class="calendar-sharing__dialog-link-list-sorting-button">
					${0}
					${0}
				</div>
			`), icon.render(), this.getSortingButtonText());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton, 'click', this.changeListSort.bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton;
	  }
	  getSortingButtonText() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButtonText) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButtonText = main_core.Tag.render(_t5$4 || (_t5$4 = _$7`
				<div class="calendar-sharing__dialog-link-list-sorting-button-text">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getSortingName)[_getSortingName]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButtonText;
	  }
	  getListNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list = main_core.Tag.render(_t6$4 || (_t6$4 = _$7`
				<div class="calendar-sharing__dialog-link-list-container">
					${0}
				</div>
			`), this.getListItemsNode());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list;
	  }
	  getListItemsNode() {
	    if (this.isListEmpty()) {
	      return this.getEmptyStateNode();
	    }
	    const linkListItems = this.getListItems();
	    return main_core.Tag.render(_t7$3 || (_t7$3 = _$7`
			<div class="calendar-sharing__dialog-link-list">
				${0}
			</div>
		`), linkListItems.map(listItem => listItem.render()));
	  }
	  getEmptyStateNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].emptyState) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].emptyState = main_core.Tag.render(_t8$2 || (_t8$2 = _$7`
				<div class="calendar-sharing__dialog-link-list-empty-state-wrapper">
					<div class="calendar-sharing__dialog-link-list-empty-state-icon"></div>
					<div class="calendar-sharing__dialog-link-list-empty-state-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_LIST_EMPTY_TITLE'));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].emptyState;
	  }
	  getListItems() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse]) {
	      return this.getSortedByFrequentUseListItems();
	    }
	    return this.getSortedByDateListItems();
	  }
	  getSortedByFrequentUseListItems() {
	    return Object.values(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]).sort((a, b) => {
	      if (a.frequentUse > b.frequentUse) {
	        return -1;
	      }
	      if (a.frequentUse < b.frequentUse) {
	        return 1;
	      }
	      if (a.id > b.id) {
	        return -1;
	      }
	      if (a.id < b.id) {
	        return 1;
	      }
	      return 0;
	    }).map(item => new ListItem({
	      ...item,
	      userInfo: babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].userInfo,
	      pathToUser: babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser],
	      setListItemPopupState: this.setListItemPopupState
	    }));
	  }
	  getSortedByDateListItems() {
	    return Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]).sort((a, b) => b - a).map(index => {
	      return new ListItem({
	        ...babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][index],
	        userInfo: babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].userInfo,
	        pathToUser: babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser],
	        setListItemPopupState: this.setListItemPopupState
	      });
	    });
	  }
	  show(maxListHeight) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list && maxListHeight) {
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list, 'max-height', `${maxListHeight - LIST_PADDING_SUM}px`);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper, '--show');
	    }
	  }
	  close() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list) {
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list, 'max-height', `${DEFAULT_LIST_HEIGHT}px`);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper, '--show');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].onLinkListClose) {
	      babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].onLinkListClose();
	    }
	  }
	  updateLinkList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list) {
	      main_core.Dom.clean(this.getListNode());
	      const listItems = this.getListItemsNode();
	      main_core.Dom.append(listItems, babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].list);
	    }
	  }
	  changeListSort() {
	    babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse] = !babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse];
	    BX.ajax.runAction('calendar.api.sharingajax.setSortJointLinksByFrequentUse', {
	      data: {
	        sortByFrequentUse: babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse] ? 'Y' : 'N'
	      }
	    });
	    const sortName = babelHelpers.classPrivateFieldLooseBase(this, _getSortingName)[_getSortingName]();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButtonText) {
	      main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButtonText, {
	        text: sortName
	      });
	    }
	    this.updateLinkList();
	  }
	  setListItemPopupState(state) {
	    babelHelpers.classPrivateFieldLooseBase(this, _popupOpenState)[_popupOpenState] = state;
	  }
	  isOpenListItemPopup() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popupOpenState)[_popupOpenState];
	  }
	  onJointLinkCopy(event) {
	    const id = event.data.id;
	    const hash = event.data.hash;
	    setTimeout(() => {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][id]) {
	        babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][id].frequentUse = babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][id].frequentUse + 1;
	        this.updateLinkList();
	      }
	    }, 1000);
	    BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
	      data: {
	        hash
	      }
	    });
	  }
	  onJointLinkDelete(event) {
	    const id = event.data.id;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][id]) {
	      delete babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][id];
	    }
	    if (this.isListEmpty()) {
	      this.updateLinkList();
	      this.hideSortingButton();
	    }
	  }
	  isListEmpty() {
	    return main_core.Type.isNil(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]) || main_core.Type.isArray(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]) && !main_core.Type.isArrayFilled(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]) || main_core.Type.isObject(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]) && !Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]).length;
	  }
	  hideSortingButton() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton, '--hide');
	    }
	  }
	  showSortingButton() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].sortingButton, '--hide');
	    }
	  }
	}
	function _getSortingName2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _sortByFrequentUse)[_sortByFrequentUse] ? main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_RECENT') : main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_DATE');
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$6,
	  _t3$5,
	  _t4$5,
	  _t5$5,
	  _t6$5,
	  _t7$4;
	var _options = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("options");
	var _popup$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _layout$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _dialogQr = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogQr");
	var _context$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	var _settings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settings");
	var _calendarContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarContext");
	var _crmContext = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("crmContext");
	var _settingsControl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsControl");
	var _userSelectorControl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userSelectorControl");
	var _linkList$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linkList");
	class DialogNew {
	  // eslint-disable-next-line unicorn/numeric-separators-style

	  // eslint-disable-next-line unicorn/numeric-separators-style

	  constructor(options) {
	    var _options$readOnly, _options$sharingRule$, _options$sharingRule$2;
	    this.HELP_DESK_CODE_CALENDAR = 17198666;
	    this.HELP_DESK_CODE_CRM = 17502612;
	    Object.defineProperty(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogQr, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _context$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendarContext, {
	      writable: true,
	      value: 'calendar'
	    });
	    Object.defineProperty(this, _crmContext, {
	      writable: true,
	      value: 'crm'
	    });
	    Object.defineProperty(this, _settingsControl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userSelectorControl, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _linkList$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _options)[_options] = options;
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5] = {
	      wrapper: null,
	      contentWrapper: null,
	      contentTop: null,
	      contentBody: null,
	      contentBottom: null,
	      listWrapper: null,
	      buttonCopy: null,
	      buttonHistory: null,
	      buttonWhatSeeUsers: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] = options.context;
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1] = null;
	    const weekHolidays = new Set(options.calendarSettings.weekHolidays.map(day => calendar_util.Util.getIndByWeekDay(day)));
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings] = {
	      readOnly: (_options$readOnly = options.readOnly) != null ? _options$readOnly : false,
	      rule: {
	        slotSize: (_options$sharingRule$ = options.sharingRule.slotSize) != null ? _options$sharingRule$ : 60,
	        ranges: (_options$sharingRule$2 = options.sharingRule.ranges) != null ? _options$sharingRule$2 : []
	      },
	      weekStart: calendar_util.Util.getIndByWeekDay(options.calendarSettings.weekStart),
	      workDays: [0, 1, 2, 3, 4, 5, 6].filter(day => !weekHolidays.has(day)),
	      workTimeStart: options.calendarSettings.workTimeStart,
	      workTimeEnd: options.calendarSettings.workTimeEnd,
	      collapsed: options.settingsCollapsed
	    };
	    this.bindElement = options.bindElement || null;
	    this.sharingUrl = options.sharingUrl || null;
	    this.linkHash = options.linkHash;
	    this.userInfo = options.userInfo || null;
	    this.onPopupClose = this.onPopupClose.bind(this);
	    this.onCopyButtonClick = this.onCopyButtonClick.bind(this);
	    main_core_events.EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', event => {
	      const shortUrl = event.data.shortUrl;
	      if (this.copyLink(shortUrl)) {
	        this.onSuccessfulCopyingLink();
	      }
	      calendar_sharing_analytics.Analytics.sendLinkCopiedList(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1], {
	        peopleCount: event.data.members.length + 1,
	        ruleChanges: babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].getChanges()
	      });
	    });
	    this.bindEvents();
	  }
	  bindEvents() {
	    main_core.Event.bind(window, 'beforeunload', this.saveSharingRule.bind(this));
	  }

	  /**
	   *
	   * @returns {Popup}
	   */
	  getPopup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2] = new main_popup.Popup({
	        bindElement: this.bindElement,
	        className: 'calendar-sharing__dialog',
	        closeByEsc: true,
	        autoHide: true,
	        padding: 0,
	        width: 470,
	        angle: {
	          offset: this.bindElement.offsetWidth / 2 + 16
	        },
	        autoHideHandler: event => this.canBeClosed(event),
	        content: this.getPopupWrapper(),
	        animation: 'fading-slide',
	        events: {
	          onPopupShow: this.onPopupShow.bind(this),
	          onPopupClose: this.onPopupClose.bind(this)
	        }
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2];
	  }
	  onPopupShow() {
	    main_core.Dom.addClass(this.bindElement, 'ui-btn-hover');
	    calendar_sharing_analytics.Analytics.sendPopupOpened(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1]);
	  }
	  onPopupClose() {
	    main_core.Dom.removeClass(this.bindElement, 'ui-btn-hover');
	    this.saveSharingRule();
	    this.clearSelectedUsers();
	    this.closeLinkList();
	  }
	  canBeClosed(event) {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3;
	    const isClickInside = babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper.contains(event.target);
	    const isQrDialogShown = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr]) == null ? void 0 : _babelHelpers$classPr.isShown();
	    const isSettingsPopupShown = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].settingPopupShown();
	    const isUserSelectorDialogOpened = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) == null ? void 0 : _babelHelpers$classPr2.isUserSelectorDialogOpened();
	    const isListItemPopupOpened = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) == null ? void 0 : _babelHelpers$classPr3.isOpenListItemPopup();
	    const checkTopSlider = babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _calendarContext)[_calendarContext] ? calendar_util.Util.getBX().SidePanel.Instance.getTopSlider() : false;
	    return !isClickInside && !isQrDialogShown && !isSettingsPopupShown && !isUserSelectorDialogOpened && !isListItemPopupOpened && !checkTopSlider;
	  }

	  /**
	   *
	   * @returns {DialogQr}
	   */
	  getDialogQr() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr] = new DialogQr({
	        sharingUrl: this.sharingUrl,
	        context: babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1]
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _dialogQr)[_dialogQr];
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupWrapper() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper = main_core.Tag.render(_t$8 || (_t$8 = _$8`
				<div class="calendar-sharing__dialog-wrapper">
					${0}
					${0}
				</div>
			`), this.getPopupContentMain(), this.getPopupContentList());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper;
	  }
	  getPopupContentMain() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper = main_core.Tag.render(_t2$6 || (_t2$6 = _$8`
				<div class="calendar-sharing__dialog-content-wrapper --show">
					${0}
						<div class="calendar-sharing__dialog-body">
							<div class="calendar-sharing__dialog-message">
								<div class="calendar-sharing__dialog-info-icon-container">
									<div class="calendar-sharing__dialog-info-icon"></div>
								</div>
								<div class="calendar-sharing__dialog-notify" onclick="${0}">
									${0}
								</div>
							</div>
							${0}
							${0}
						</div>
					${0}
				</div>
			`), this.getPopupContentTop(), this.onOpenLink.bind(this), main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4_V3', {
	        '#LINK#': this.sharingUrl
	      }), this.getSettingsNode(), this.getUserSelectorNode(), this.getPopupContentBottom());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper;
	  }
	  getPopupContentList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _crmContext)[_crmContext]) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1] = new List({
	        userInfo: this.userInfo,
	        onLinkListClose: this.onLinkListClose.bind(this),
	        sortJointLinksByFrequentUse: babelHelpers.classPrivateFieldLooseBase(this, _options)[_options].sortJointLinksByFrequentUse
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1].render();
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupCopyLinkButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy = main_core.Tag.render(_t3$5 || (_t3$5 = _$8`
				<span class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps calendar-sharing__dialog-copy">
					${0}
				</span>
			`), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy, 'click', this.onCopyButtonClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy;
	  }
	  getPopupLinkHistoryButton() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _crmContext)[_crmContext]) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonHistory) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonHistory = main_core.Tag.render(_t4$5 || (_t4$5 = _$8`
				<span
					class="ui-btn ui-btn-round ui-btn-light ui-btn-no-caps calendar-sharing__dialog-people"
					data-id="calendar-sharing-history-btn"
				>
					${0}
				</span>
			`), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_JOINT_SLOTS_BUTTON'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonHistory, 'click', this.openLinkList.bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonHistory;
	  }
	  getPopupWhatSeeUsersButton() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _calendarContext)[_calendarContext]) {
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonWhatSeeUsers) {
	      const adjustClick = () => {
	        this.saveSharingRule();
	        this.getDialogQr().show();
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonWhatSeeUsers = main_core.Tag.render(_t5$5 || (_t5$5 = _$8`
				<span onclick="${0}" class="calendar-sharing__dialog-link">
					${0}
				</span>
			`), adjustClick, main_core.Loc.getMessage('SHARING_INFO_POPUP_WHAT_SEE_USERS'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonWhatSeeUsers, 'click', adjustClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonWhatSeeUsers;
	  }
	  onCopyButtonClick() {
	    var _babelHelpers$classPr4, _babelHelpers$classPr5;
	    const params = {
	      peopleCount: (_babelHelpers$classPr4 = (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) == null ? void 0 : _babelHelpers$classPr5.getPeopleCount()) != null ? _babelHelpers$classPr4 : 1,
	      ruleChanges: babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].getChanges()
	    };
	    if (babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl] && babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].hasChanges()) {
	      calendar_sharing_analytics.Analytics.sendLinkCopied(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1], calendar_sharing_analytics.Analytics.linkTypes.multiple, params);
	      this.saveJointLink();
	    } else if (this.copyLink(this.sharingUrl)) {
	      calendar_sharing_analytics.Analytics.sendLinkCopied(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1], calendar_sharing_analytics.Analytics.linkTypes.solo, params);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _calendarContext)[_calendarContext]) {
	        BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
	          data: {
	            hash: this.linkHash
	          }
	        });
	      }
	      this.onSuccessfulCopyingLink();
	    }
	  }
	  async saveJointLink() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy && main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy, 'ui-btn-clock')) {
	      return;
	    }
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy, 'ui-btn-clock');
	    const memberIds = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].getSelectedUserIdList();
	    const response = await BX.ajax.runAction('calendar.api.sharingajax.generateUserJointSharingLink', {
	      data: {
	        memberIds
	      }
	    });
	    if (response && response.data) {
	      var _babelHelpers$classPr6;
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].buttonCopy, 'ui-btn-clock');
	      const {
	        url
	      } = response.data;
	      if (this.copyLink(url)) {
	        this.onSuccessfulCopyingLink();
	      }
	      (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) == null ? void 0 : _babelHelpers$classPr6.getLinkListInfo();
	    }
	  }
	  clearSelectedUsers() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].clearSelectedUsers();
	    }
	  }
	  openLinkList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper && babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper, '--show');
	      babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1].show(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper.offsetHeight);
	    }
	  }
	  closeLinkList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) {
	      setTimeout(() => {
	        babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1].close();
	      }, 200);
	    }
	  }
	  onLinkListClose() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentWrapper, '--show');
	    }
	  }
	  copyLink(linkUrl) {
	    if (!linkUrl) {
	      return;
	    }
	    const result = BX.clipboard.copy(linkUrl);
	    if (result) {
	      calendar_util.Util.showNotification(main_core.Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
	      main_core_events.EventEmitter.emit('CalendarSharing:LinkCopied');
	    }
	    return result;
	  }
	  async onOpenLink() {
	    await this.saveSharingRule();
	    window.open(this.sharingUrl, '_blank').focus();
	  }
	  onSuccessfulCopyingLink() {
	    this.getPopup().close();
	  }
	  getSettingsNode() {
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl] = new Settings(babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings]);
	    babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].rule = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].getRule();
	    return babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].render();
	  }
	  getUserSelectorNode() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1] === babelHelpers.classPrivateFieldLooseBase(this, _crmContext)[_crmContext]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl] = new UserSelector({
	      userInfo: this.userInfo,
	      onMembersAdded: () => calendar_sharing_analytics.Analytics.sendMembersAdded(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1], babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].getPeopleCount())
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].render();
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupContentBottom() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentBottom) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentBottom = main_core.Tag.render(_t6$5 || (_t6$5 = _$8`
				<div class="calendar-sharing__dialog-bottom">
					${0}
					${0}
					${0}
				</div>
			`), this.getPopupCopyLinkButton(), this.getPopupLinkHistoryButton(), this.getPopupWhatSeeUsersButton());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentBottom;
	  }

	  /**
	   *
	   * @returns {HTMLElement}
	   */
	  getPopupContentTop() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentTop) {
	      const openHelpDesk = () => {
	        top.BX.Helper.show(`redirect=detail&code=${this.getHelpDeskCodeDependsOnContext()}`);
	      };
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentTop = main_core.Tag.render(_t7$4 || (_t7$4 = _$8`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${0}</span>
						<span onclick="${0}" class="calendar-sharing__dialog-title-help" title="${0}"></span>
					</div>
					<div class="calendar-sharing__dialog-info">${0}</div>
				</div>
			`), main_core.Loc.getMessage('SHARING_BUTTON_TITLE'), openHelpDesk, main_core.Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK'), `${this.getPhraseDependsOnContext('SHARING_INFO_POPUP_CONTENT_3')} `);
	      const infoNotify = babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentTop.querySelector('[ data-role="calendar-sharing_popup-open-link"]');
	      if (infoNotify) {
	        let infoNotifyHint;
	        let timer;
	        main_core.Event.bind(infoNotify, 'mouseenter', () => {
	          timer = setTimeout(() => {
	            if (!infoNotifyHint) {
	              infoNotifyHint = new main_popup.Popup({
	                bindElement: infoNotify,
	                angle: {
	                  offset: infoNotify.offsetWidth / 2 + 16
	                },
	                width: 410,
	                darkMode: true,
	                content: main_core.Loc.getMessage('SHARING_INFO_POPUP_SLOT_DESC'),
	                animation: 'fading-slide'
	              });
	            }
	            infoNotifyHint.show();
	          }, 1000);
	        });
	        main_core.Event.bind(infoNotify, 'mouseleave', () => {
	          clearTimeout(timer);
	          if (infoNotifyHint) {
	            infoNotifyHint.close();
	          }
	        });
	      }
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].contentTop;
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  show() {
	    var _babelHelpers$classPr7, _babelHelpers$classPr8, _babelHelpers$classPr9;
	    if (!this.bindElement) {
	      // eslint-disable-next-line no-console
	      console.warn('BX.Calendar.Sharing: "bindElement" is not defined');
	      return;
	    }
	    (_babelHelpers$classPr7 = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl]) == null ? void 0 : _babelHelpers$classPr7.sortRanges();
	    (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl]) == null ? void 0 : _babelHelpers$classPr8.updateRanges();
	    (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl]) == null ? void 0 : _babelHelpers$classPr9.renderRanges();
	    this.getPopup().show();
	  }
	  destroy() {
	    this.getPopup().destroy();
	    this.getDialogQr().destroy();
	  }
	  getPhraseDependsOnContext(code) {
	    return main_core.Loc.getMessage(`${code}_${babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1].toUpperCase()}`);
	  }
	  getHelpDeskCodeDependsOnContext() {
	    let code;
	    switch (babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1]) {
	      case babelHelpers.classPrivateFieldLooseBase(this, _calendarContext)[_calendarContext]:
	        {
	          code = this.HELP_DESK_CODE_CALENDAR;
	          break;
	        }
	      case babelHelpers.classPrivateFieldLooseBase(this, _crmContext)[_crmContext]:
	        {
	          code = this.HELP_DESK_CODE_CRM;
	          break;
	        }
	      default:
	        code = 0;
	    }
	    return code;
	  }
	  saveSharingRule() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].isDifferentFrom(babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].rule)) {
	      return;
	    }
	    const changes = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].getChanges();
	    calendar_sharing_analytics.Analytics.sendRuleUpdated(babelHelpers.classPrivateFieldLooseBase(this, _context$1)[_context$1], changes);
	    const newRule = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].getRule();
	    BX.ajax.runAction('calendar.api.sharingajax.saveLinkRule', {
	      data: {
	        linkHash: this.linkHash,
	        ruleArray: newRule
	      }
	    }).then(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _settings)[_settings].rule = newRule;
	      main_core_events.EventEmitter.emit('CalendarSharing:RuleUpdated');
	    }, error => {
	      // eslint-disable-next-line no-console
	      console.error(error);
	    });
	  }
	  getSettingsControlRule() {
	    var _babelHelpers$classPr10;
	    return (_babelHelpers$classPr10 = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl]) == null ? void 0 : _babelHelpers$classPr10.getRule();
	  }
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$7,
	  _t3$6;
	class SharingButton {
	  constructor(options = {}) {
	    var _this$sharingConfig, _this$sharingConfig2, _this$sharingConfig3;
	    this.PAY_ATTENTION_TO_NEW_FEATURE_DELAY = 1000;
	    this.PAY_ATTENTION_TO_NEW_FEATURE_FIRST = 'first-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_NEW = 'new-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND = 'remind-feature';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_JOINT = 'joint-sharing';
	    this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS = [this.PAY_ATTENTION_TO_NEW_FEATURE_FIRST];
	    this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS = [this.PAY_ATTENTION_TO_NEW_FEATURE_NEW, this.PAY_ATTENTION_TO_NEW_FEATURE_REMIND];
	    this.AVAILABLE_PAY_ATTENTION_TO_NEW_FEATURE_MODS = [...this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS, ...this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS];
	    this.wrap = options.wrap;
	    this.userInfo = options.userInfo || null;
	    this.sharingConfig = calendar_util.Util.getSharingConfig();
	    this.sharingUrl = ((_this$sharingConfig = this.sharingConfig) == null ? void 0 : _this$sharingConfig.url) || null;
	    this.linkHash = ((_this$sharingConfig2 = this.sharingConfig) == null ? void 0 : _this$sharingConfig2.hash) || null;
	    this.sharingRule = ((_this$sharingConfig3 = this.sharingConfig) == null ? void 0 : _this$sharingConfig3.rule) || null;
	    this.payAttentionToNewFeatureMode = options.payAttentionToNewFeature;
	    this.sharingFeatureLimit = options.sharingFeatureLimit;
	    this.sharingSettingsCollapsed = options.sharingSettingsCollapsed;
	    this.sortJointLinksByFrequentUse = options.sortJointLinksByFrequentUse;
	  }
	  show() {
	    main_core.Dom.addClass(this.wrap, 'calendar-sharing__btn-wrap');
	    this.button = new ui_buttons.Button({
	      text: main_core.Loc.getMessage('SHARING_BUTTON_TITLE'),
	      round: true,
	      size: ui_buttons.ButtonSize.EXTRA_SMALL,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      icon: this.sharingFeatureLimit ? ui_buttons.ButtonIcon.LOCK : null,
	      className: 'ui-btn-themes calendar-sharing__btn',
	      onclick: (button, event) => {
	        if (!this.switcher.getNode().contains(event.target)) {
	          this.handleSharingButtonClick();
	        }
	      }
	    });
	    this.button.renderTo(this.wrap);
	    this.renderSwitcher();
	    if (this.payAttentionToNewFeatureMode === this.PAY_ATTENTION_TO_NEW_FEATURE_JOINT) {
	      setTimeout(() => {
	        if (BX.SidePanel.Instance.getTopSlider() === null) {
	          this.payAttentionToNewFeatureWithText();
	          BX.ajax.runAction('calendar.api.sharingajax.disableOptionPayAttentionToNewSharingFeature');
	        }
	      }, this.PAY_ATTENTION_TO_NEW_FEATURE_DELAY);
	    }
	  }
	  handleSharingButtonClick() {
	    if (this.sharingFeatureLimit) {
	      top.BX.UI.InfoHelper.show('limit_office_calendar_free_slots');
	      return;
	    }
	    if (this.isSharingEnabled()) {
	      this.openDialog();
	    } else {
	      this.switcher.toggle();
	    }
	  }
	  getSwitcherContainer() {
	    return main_core.Tag.render(_t$9 || (_t$9 = _$9`
			<div class="calendar-sharing__switcher"></div>
		`));
	  }
	  getSwitcherDivider() {
	    return main_core.Tag.render(_t2$7 || (_t2$7 = _$9`
			<div class="calendar-sharing__switcher_divider"></div>
		`));
	  }
	  renderSwitcher() {
	    main_core.Dom.append(this.getSwitcherDivider(), this.wrap);
	    this.switcherWrap = main_core.Tag.render(_t3$6 || (_t3$6 = _$9`<div class="calendar-sharing__switcher-wrap"></div>`));
	    main_core.Dom.append(this.switcherWrap, this.wrap);
	    main_core.Event.bind(this.switcherWrap, 'click', this.handleSwitcherWrapClick.bind(this), {
	      capture: true
	    });
	    this.switcher = new BX.UI.Switcher({
	      node: this.getSwitcherContainer(),
	      checked: this.isSharingEnabled() && !this.sharingFeatureLimit,
	      color: 'green',
	      size: 'small',
	      handlers: {
	        toggled: () => this.handleSwitcherToggled()
	      }
	    });
	    this.switcher.renderTo(this.switcherWrap);
	  }
	  handleSwitcherWrapClick(event) {
	    if (this.switcher.isChecked()) {
	      this.showWarningPopup();
	      event.stopPropagation();
	    }
	  }
	  handleSwitcherToggled() {
	    if (this.sharingFeatureLimit && this.switcher.isChecked()) {
	      top.BX.UI.InfoHelper.show('limit_office_calendar_free_slots');
	      this.switcher.toggle();
	      return;
	    }
	    if (this.isToggledAfterErrorOccurred()) {
	      return;
	    }
	    if (this.switcher.isChecked()) {
	      this.enableSharing();
	    } else {
	      this.disableSharing();
	    }
	  }
	  isToggledAfterErrorOccurred() {
	    return this.switcher.isChecked() === this.isSharingEnabled();
	  }
	  isSharingEnabled() {
	    return main_core.Type.isString(this.sharingUrl);
	  }
	  enableSharing() {
	    const action = 'calendar.api.sharingajax.enableUserSharing';
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';
	    BX.ajax.runAction(action).then(response => {
	      this.sharingUrl = response.data.url;
	      this.linkHash = response.data.hash;
	      this.sharingRule = response.data.rule;
	      this.openDialog();
	      main_core_events.EventEmitter.emit(event, {
	        isChecked: this.switcher.isChecked(),
	        url: response.data.url
	      });
	    }).catch(() => {
	      this.switcher.toggle();
	    });
	  }
	  openDialog() {
	    var _this$pulsar;
	    (_this$pulsar = this.pulsar) == null ? void 0 : _this$pulsar.close();
	    main_core.Dom.remove(this.counterNode);
	    if (!this.newDialog) {
	      this.newDialog = new DialogNew({
	        bindElement: this.button.getContainer(),
	        sharingUrl: this.sharingUrl,
	        linkHash: this.linkHash,
	        sharingRule: this.sharingRule,
	        context: 'calendar',
	        calendarSettings: {
	          weekHolidays: calendar_util.Util.config.week_holidays,
	          weekStart: calendar_util.Util.config.week_start,
	          workTimeStart: calendar_util.Util.config.work_time_start,
	          workTimeEnd: calendar_util.Util.config.work_time_end
	        },
	        userInfo: this.userInfo,
	        settingsCollapsed: this.sharingSettingsCollapsed,
	        sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse
	      });
	    }
	    if (!this.newDialog.isShown()) {
	      this.newDialog.show();
	    }
	  }
	  disableSharing() {
	    const action = 'calendar.api.sharingajax.disableUserSharing';
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingDisabled';
	    this.warningPopup.close();
	    BX.ajax.runAction(action).then(() => {
	      this.sharingUrl = null;
	      if (this.newDialog) {
	        this.newDialog.destroy();
	        this.newDialog = null;
	      }
	      main_core_events.EventEmitter.emit(event, {
	        isChecked: this.switcher.isChecked()
	      });
	    }).catch(() => {
	      this.switcher.toggle();
	    });
	  }
	  showWarningPopup() {
	    if (!this.warningPopup) {
	      this.warningPopup = new ui_dialogs_messagebox.MessageBox({
	        title: main_core.Loc.getMessage('SHARING_WARNING_POPUP_TITLE_1'),
	        message: main_core.Loc.getMessage('SHARING_WARNING_POPUP_CONTENT_2'),
	        buttons: this.getWarningPopupButtons(),
	        popupOptions: {
	          autoHide: true,
	          closeByEsc: true,
	          draggable: false,
	          closeIcon: true,
	          minWidth: 365,
	          maxWidth: 385,
	          minHeight: 180
	        }
	      });
	    }
	    this.warningPopup.show();
	  }
	  getWarningPopupButtons() {
	    return [this.getSubmitButton(), this.getCancelButton()];
	  }
	  getSubmitButton() {
	    return new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.DANGER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_SUBMIT_BUTTON_NEW_MSGVER_1'),
	      events: {
	        click: () => this.handleSubmitButtonClick()
	      }
	    });
	  }
	  getCancelButton() {
	    return new ui_buttons.Button({
	      size: ui_buttons.ButtonSize.MEDIUM,
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_CANCEL_BUTTON'),
	      events: {
	        click: () => this.handleCancelButtonClick()
	      }
	    });
	  }
	  handleSubmitButtonClick() {
	    this.switcher.toggle();
	    this.warningPopup.close();
	  }
	  handleCancelButtonClick() {
	    this.warningPopup.close();
	  }
	  payAttentionToNewFeature(mode) {
	    if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITHOUT_TEXT_MODS.includes(mode)) {
	      this.payAttentionToNewFeatureWithoutText();
	    }
	    if (this.PAY_ATTENTION_TO_NEW_FEATURE_WITH_TEXT_MODS.includes(mode)) {
	      this.payAttentionToNewFeatureWithText();
	    }
	  }
	  payAttentionToNewFeatureWithoutText() {
	    this.pulsar = this.getPulsar(this.wrap, false);
	    this.pulsar.show();
	    main_core.Event.bind(this.pulsar.container, 'click', () => {
	      this.handleSharingButtonClick();
	    });
	    this.counterNode = new ui_cnt.Counter({
	      value: 1,
	      color: ui_cnt.Counter.Color.DANGER,
	      size: ui_cnt.Counter.Size.MEDIUM,
	      animation: false
	    }).getContainer();
	    main_core.Dom.addClass(this.counterNode, 'calendar-sharing__new-feature-counter');
	    main_core.Dom.append(this.counterNode, this.wrap);
	  }
	  payAttentionToNewFeatureWithText() {
	    const title = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_JOINT_TITLE');
	    const text = main_core.Loc.getMessage('CALENDAR_PAY_ATTENTION_TO_NEW_FEATURE_JOINT_TEXT');
	    const guide = this.getGuide(title, text);
	    const pulsar = this.getPulsar(this.wrap);
	    guide.showNextStep();
	    guide.getPopup().setAngle({
	      offset: 210
	    });
	    pulsar.show();
	  }
	  getGuide(title, text) {
	    const guide = new ui_tour.Guide({
	      simpleMode: true,
	      onEvents: true,
	      steps: [{
	        target: this.wrap,
	        title,
	        text,
	        position: 'bottom',
	        condition: {
	          top: true,
	          bottom: false,
	          color: 'primary'
	        }
	      }]
	    });
	    const guidePopup = guide.getPopup();
	    main_core.Dom.addClass(guidePopup.popupContainer, 'calendar-popup-ui-tour-animate');
	    guidePopup.setWidth(400);
	    main_core.Dom.style(guidePopup.getContentContainer(), 'paddingRight', getComputedStyle(guidePopup.closeIcon).width);
	    return guide;
	  }
	  getPulsar(target, hideOnHover = true) {
	    const pulsar = new BX.SpotLight({
	      targetElement: target,
	      targetVertex: 'middle-center',
	      lightMode: true
	    });
	    if (hideOnHover) {
	      pulsar.bindEvents({
	        onTargetEnter: () => pulsar.close()
	      });
	    }
	    return pulsar;
	  }
	}

	class Interface {
	  constructor(options) {
	    var _options$payAttention, _options$sharingFeatu, _options$sharingSetti, _options$sortJointLin;
	    this.buttonWrap = options.buttonWrap;
	    this.userInfo = options.userInfo || null;
	    this.payAttentionToNewFeature = (_options$payAttention = options.payAttentionToNewFeature) != null ? _options$payAttention : false;
	    this.sharingFeatureLimit = (_options$sharingFeatu = options.sharingFeatureLimit) != null ? _options$sharingFeatu : false;
	    this.sharingSettingsCollapsed = (_options$sharingSetti = options.sharingSettingsCollapsed) != null ? _options$sharingSetti : false;
	    this.sortJointLinksByFrequentUse = (_options$sortJointLin = options.sortJointLinksByFrequentUse) != null ? _options$sortJointLin : false;
	  }
	  showSharingButton() {
	    this.sharingButton = new SharingButton({
	      wrap: this.buttonWrap,
	      userInfo: this.userInfo,
	      payAttentionToNewFeature: this.payAttentionToNewFeature,
	      sharingFeatureLimit: this.sharingFeatureLimit,
	      sharingSettingsCollapsed: this.sharingSettingsCollapsed,
	      sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse
	    });
	    this.sharingButton.show();
	  }
	}

	exports.Interface = Interface;
	exports.SharingButton = SharingButton;
	exports.DialogNew = DialogNew;
	exports.DialogQr = DialogQr;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX,BX,BX,BX.Main,BX.Calendar.Sharing,BX.UI.EntitySelector,BX,BX.Calendar,BX.UI.IconSet,BX.Main,BX.UI.Dialogs,BX.UI,BX.Event,BX,BX.UI,BX,BX.UI.Tour,BX.UI));
//# sourceMappingURL=interface.bundle.js.map
