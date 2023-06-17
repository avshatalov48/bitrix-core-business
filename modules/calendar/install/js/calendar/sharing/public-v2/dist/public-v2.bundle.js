this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_icons_b24,calendar_util,main_core,main_popup,main_date,ui_bottomsheet,main_core_events,index) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _owner = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _link = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("link");
	var _currentLang = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentLang");
	var _name = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("name");
	var _lastName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastName");
	var _photo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("photo");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _handleTimelineNotify = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimelineNotify");
	var _getNodeButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeButton");
	var _getNodeLabel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeLabel");
	var _getNodeInfo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeInfo");
	class Welcome {
	  constructor(options) {
	    Object.defineProperty(this, _getNodeInfo, {
	      value: _getNodeInfo2
	    });
	    Object.defineProperty(this, _getNodeLabel, {
	      value: _getNodeLabel2
	    });
	    Object.defineProperty(this, _getNodeButton, {
	      value: _getNodeButton2
	    });
	    Object.defineProperty(this, _handleTimelineNotify, {
	      value: _handleTimelineNotify2
	    });
	    Object.defineProperty(this, _owner, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _link, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentLang, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _name, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _lastName, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _photo, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _owner)[_owner] = options.owner || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _link)[_link] = options.link || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentLang)[_currentLang] = options.currentLang || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] = babelHelpers.classPrivateFieldLooseBase(this, _owner)[_owner].name || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _lastName)[_lastName] = babelHelpers.classPrivateFieldLooseBase(this, _owner)[_owner].lastName || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _photo)[_photo] = babelHelpers.classPrivateFieldLooseBase(this, _owner)[_owner].photo || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {
	      wrapper: null,
	      button: null,
	      label: null
	    };
	    if (babelHelpers.classPrivateFieldLooseBase(this, _link)[_link] && babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].type === 'crm_deal' && babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].active === true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleTimelineNotify)[_handleTimelineNotify]('notViewed');
	    }
	  }
	  disableButton() {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, '--disabled');
	  }
	  enableButton() {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, '--disabled');
	  }
	  hideButton() {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, '--hidden');
	  }
	  handleWelcomePageButtonClick() {
	    if (main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, '--disabled')) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _link)[_link] && babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].type === 'crm_deal' && babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].active === true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleTimelineNotify)[_handleTimelineNotify]('viewed');
	    }
	    this.disableButton();
	    main_core_events.EventEmitter.emit('showSlotSelector', this);
	  }
	  setAccessDenied() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].info = babelHelpers.classPrivateFieldLooseBase(this, _getNodeInfo)[_getNodeInfo](true);
	  }
	  render() {
	    return main_core.Tag.render(_t || (_t = _`
			<div class="calendar-pub__block --welcome">
				${0}
				<div class="calendar-pub__welcome">
					<div class="calendar-pub__welcome-user">
						<div class="calendar-pub__welcome-userpic ui-icon ui-icon-common-user">
							<i ${0}></i>
						</div>
						<div class="calendar-pub-ui__typography-m">
							${0} ${0} 
						</div>
					</div>
					<div class="calendar-pub__block-separator"></div>
					${0}
					<div class="calendar-pub__welcome-bottom">
						${0}
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeLabel)[_getNodeLabel](), babelHelpers.classPrivateFieldLooseBase(this, _photo)[_photo] ? `style="background-image: url(${babelHelpers.classPrivateFieldLooseBase(this, _photo)[_photo]})"` : '', babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] ? babelHelpers.classPrivateFieldLooseBase(this, _name)[_name] : '', babelHelpers.classPrivateFieldLooseBase(this, _lastName)[_lastName] ? babelHelpers.classPrivateFieldLooseBase(this, _lastName)[_lastName] : '', babelHelpers.classPrivateFieldLooseBase(this, _getNodeInfo)[_getNodeInfo](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeButton)[_getNodeButton]());
	  }
	}
	function _handleTimelineNotify2(mode) {
	  BX.ajax.runAction('calendar.api.sharingajax.handleTimelineNotify', {
	    data: {
	      linkHash: babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].hash,
	      entityId: babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].entityId,
	      entityType: babelHelpers.classPrivateFieldLooseBase(this, _link)[_link].type,
	      notifyType: mode
	    }
	  });
	}
	function _getNodeButton2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="calendar-pub-ui__btn">
					<div class="calendar-pub-ui__btn-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_SELECT_SLOT'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button, 'click', () => {
	      this.handleWelcomePageButtonClick();
	    });
	    main_core_events.EventEmitter.subscribe('hideSlotSelector', this.enableButton.bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button;
	}
	function _getNodeLabel2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].label) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].label = main_core.Tag.render(_t3 || (_t3 = _`
				<div class="calendar-pub__block-label"></div>
			`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentLang)[_currentLang] === 'ru') {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].label, '--ru');
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].label;
	}
	function _getNodeInfo2(accessDenied = false) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].info) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoTitle = main_core.Tag.render(_t4 || (_t4 = _`
				<div class="calendar-pub-ui__typography-title calendar-pub__welcome-info_title"></div>
			`));
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoSubTitle = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="calendar-pub-ui__typography-s"></div>
			`));
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].info = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="calendar-pub__welcome-info">
					<div class="calendar-pub-ui__typography-title calendar-pub__welcome-info_title">${0}</div>
					<div class="calendar-pub-ui__typography-s">${0}</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoTitle, babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoSubTitle);
	  }
	  let title = main_core.Loc.getMessage('CALENDAR_SHARING_MY_FREE_SLOTS');
	  let subTitle = main_core.Loc.getMessage('CALENDAR_SHARING_YOU_CAN_CHOOSE_FREE_MEETING_TIME');
	  if (accessDenied) {
	    title = main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED');
	    subTitle = main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED_INFO');
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoTitle.innerText = title;
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].infoSubTitle.innerText = subTitle;
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].info;
	}

	let _$1 = t => t,
	  _t$1;
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _value = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _notCurrentMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("notCurrentMonth");
	var _today = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("today");
	var _selected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selected");
	var _weekend = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("weekend");
	var _slots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slots");
	var _enableBooking = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableBooking");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getNodeWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWrapper");
	class Day {
	  constructor(options) {
	    Object.defineProperty(this, _getNodeWrapper, {
	      value: _getNodeWrapper2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _notCurrentMonth, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _today, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selected, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _weekend, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _slots, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _enableBooking, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = options.value ? options.value : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _notCurrentMonth)[_notCurrentMonth] = options.notCurrentMonth ? options.notCurrentMonth : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _today)[_today] = options.today ? options.today : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots] = options.slots || null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {
	      wrapper: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected] = options.selected ? options.selected : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _weekend)[_weekend] = options.weekend ? options.weekend : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _enableBooking)[_enableBooking] = options.enableBooking ? options.enableBooking : null;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected]) {
	      this.select();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  isSelected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected];
	  }
	  getDay() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _value)[_value];
	  }
	  isEnableBooking() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _enableBooking)[_enableBooking];
	  }
	  select() {
	    this.highlight();
	    main_core_events.EventEmitter.emit('switchSlots', {
	      slots: babelHelpers.classPrivateFieldLooseBase(this, _slots)[_slots]
	    });
	  }
	  highlight() {
	    babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected] = true;
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper)[_getNodeWrapper](), '--selected');
	    main_core_events.EventEmitter.emit('selectDate', this);
	  }
	  unSelect() {
	    babelHelpers.classPrivateFieldLooseBase(this, _selected)[_selected] = null;
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper)[_getNodeWrapper](), '--selected');
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper)[_getNodeWrapper]();
	  }
	}
	function _bindEvents2() {
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper)[_getNodeWrapper](), 'click', this.select.bind(this));
	}
	function _getNodeWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="calendar-sharing__month-col --day">${0}</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _notCurrentMonth)[_notCurrentMonth]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper, '--not-current-month');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _weekend)[_weekend]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper, '--weekend');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _enableBooking)[_enableBooking]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper, '--enable-booking');
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrapper;
	}

	let _$2 = t => t,
	  _t$2,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5$1,
	  _t6$1,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16;
	var _owner$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _accessibility = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("accessibility");
	var _layout$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _currentMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentMonth");
	var _currentYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentYear");
	var _nowTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("nowTime");
	var _months = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("months");
	var _selectedDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedDay");
	var _monthSlots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("monthSlots");
	var _monthsSlotsMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("monthsSlotsMap");
	var _timezoneOffsetUtc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timezoneOffsetUtc");
	var _selectedTimezoneOffsetUtc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedTimezoneOffsetUtc");
	var _currentMonthIndex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentMonthIndex");
	var _currentDayNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentDayNumber");
	var _timezoneList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timezoneList");
	var _calendarSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarSettings");
	var _selectedTimezoneId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedTimezoneId");
	var _selectedTimezoneNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedTimezoneNode");
	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	var _loc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loc");
	var _timeZonePopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timeZonePopup");
	var _bindEvents$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _incrementTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("incrementTime");
	var _setConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setConfig");
	var _initCurrentMonthSlots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initCurrentMonthSlots");
	var _calculateDateTimeSlots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateDateTimeSlots");
	var _getDateTimeSlotsMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDateTimeSlotsMap");
	var _createMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMonth");
	var _reCreateCurrentMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("reCreateCurrentMonth");
	var _createNextMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createNextMonth");
	var _loadMonthAccessibility = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loadMonthAccessibility");
	var _getMonthName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMonthName");
	var _getMonthDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMonthDays");
	var _getDayToSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDayToSelect");
	var _isHoliday = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isHoliday");
	var _doIntervalsIntersect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("doIntervalsIntersect");
	var _getNodeTimeZone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTimeZone");
	var _getPopupTimezoneSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getPopupTimezoneSelect");
	var _getNodeTimezoneSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTimezoneSelect");
	var _updateTimezone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateTimezone");
	var _getFormattedTimezone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFormattedTimezone");
	var _getNodeDaysOfWeek = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeDaysOfWeek");
	var _getNodeDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeDay");
	var _getNodeMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeMonth");
	var _getNodeMonthWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeMonthWrapper");
	var _getNodeTimezoneWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTimezoneWrapper");
	var _getNodeCurrentMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeCurrentMonth");
	var _updateCalendar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCalendar");
	var _getNodePrevNav = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodePrevNav");
	var _getNodeNextNav = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeNextNav");
	var _getNodeNavigation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeNavigation");
	var _handleNextMonthArrowClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleNextMonthArrowClick");
	var _handlePreviousMonthArrowClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePreviousMonthArrowClick");
	var _getNodeBack = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeBack");
	var _getNodeWrapper$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWrapper");
	class Calendar {
	  constructor(options) {
	    Object.defineProperty(this, _getNodeWrapper$1, {
	      value: _getNodeWrapper2$1
	    });
	    Object.defineProperty(this, _getNodeBack, {
	      value: _getNodeBack2
	    });
	    Object.defineProperty(this, _handlePreviousMonthArrowClick, {
	      value: _handlePreviousMonthArrowClick2
	    });
	    Object.defineProperty(this, _handleNextMonthArrowClick, {
	      value: _handleNextMonthArrowClick2
	    });
	    Object.defineProperty(this, _getNodeNavigation, {
	      value: _getNodeNavigation2
	    });
	    Object.defineProperty(this, _getNodeNextNav, {
	      value: _getNodeNextNav2
	    });
	    Object.defineProperty(this, _getNodePrevNav, {
	      value: _getNodePrevNav2
	    });
	    Object.defineProperty(this, _updateCalendar, {
	      value: _updateCalendar2
	    });
	    Object.defineProperty(this, _getNodeCurrentMonth, {
	      value: _getNodeCurrentMonth2
	    });
	    Object.defineProperty(this, _getNodeTimezoneWrapper, {
	      value: _getNodeTimezoneWrapper2
	    });
	    Object.defineProperty(this, _getNodeMonthWrapper, {
	      value: _getNodeMonthWrapper2
	    });
	    Object.defineProperty(this, _getNodeMonth, {
	      value: _getNodeMonth2
	    });
	    Object.defineProperty(this, _getNodeDay, {
	      value: _getNodeDay2
	    });
	    Object.defineProperty(this, _getNodeDaysOfWeek, {
	      value: _getNodeDaysOfWeek2
	    });
	    Object.defineProperty(this, _getFormattedTimezone, {
	      value: _getFormattedTimezone2
	    });
	    Object.defineProperty(this, _updateTimezone, {
	      value: _updateTimezone2
	    });
	    Object.defineProperty(this, _getNodeTimezoneSelect, {
	      value: _getNodeTimezoneSelect2
	    });
	    Object.defineProperty(this, _getPopupTimezoneSelect, {
	      value: _getPopupTimezoneSelect2
	    });
	    Object.defineProperty(this, _getNodeTimeZone, {
	      value: _getNodeTimeZone2
	    });
	    Object.defineProperty(this, _doIntervalsIntersect, {
	      value: _doIntervalsIntersect2
	    });
	    Object.defineProperty(this, _isHoliday, {
	      value: _isHoliday2
	    });
	    Object.defineProperty(this, _getDayToSelect, {
	      value: _getDayToSelect2
	    });
	    Object.defineProperty(this, _getMonthDays, {
	      value: _getMonthDays2
	    });
	    Object.defineProperty(this, _getMonthName, {
	      value: _getMonthName2
	    });
	    Object.defineProperty(this, _loadMonthAccessibility, {
	      value: _loadMonthAccessibility2
	    });
	    Object.defineProperty(this, _createNextMonth, {
	      value: _createNextMonth2
	    });
	    Object.defineProperty(this, _reCreateCurrentMonth, {
	      value: _reCreateCurrentMonth2
	    });
	    Object.defineProperty(this, _createMonth, {
	      value: _createMonth2
	    });
	    Object.defineProperty(this, _getDateTimeSlotsMap, {
	      value: _getDateTimeSlotsMap2
	    });
	    Object.defineProperty(this, _calculateDateTimeSlots, {
	      value: _calculateDateTimeSlots2
	    });
	    Object.defineProperty(this, _initCurrentMonthSlots, {
	      value: _initCurrentMonthSlots2
	    });
	    Object.defineProperty(this, _setConfig, {
	      value: _setConfig2
	    });
	    Object.defineProperty(this, _incrementTime, {
	      value: _incrementTime2
	    });
	    Object.defineProperty(this, _bindEvents$1, {
	      value: _bindEvents2$1
	    });
	    Object.defineProperty(this, _owner$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _accessibility, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentMonth, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentYear, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _nowTime, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _months, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedDay, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _monthSlots, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _monthsSlotsMap, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _timezoneOffsetUtc, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedTimezoneOffsetUtc, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentMonthIndex, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentDayNumber, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _timezoneList, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendarSettings, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedTimezoneId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedTimezoneNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _config, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loc, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _timeZonePopup, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2] = {
	      wrapper: null,
	      monthWrapper: null,
	      timezoneWrapper: null,
	      month: null,
	      currentMonth: null,
	      prevNav: null,
	      nextNav: null,
	      daysOfWeek: null,
	      navigation: null,
	      back: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _owner$1)[_owner$1] = options.owner;
	    babelHelpers.classPrivateFieldLooseBase(this, _accessibility)[_accessibility] = options.accessibility;
	    babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList] = options.timezoneList;
	    babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings] = options.calendarSettings;
	    babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime] = new Date();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] = 0;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDayNumber)[_currentDayNumber] = 1;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
	    babelHelpers.classPrivateFieldLooseBase(this, _currentMonth)[_currentMonth] = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getMonth();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentYear)[_currentYear] = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getFullYear();
	    babelHelpers.classPrivateFieldLooseBase(this, _timezoneOffsetUtc)[_timezoneOffsetUtc] = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getTimezoneOffset();
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneOffsetUtc)[_selectedTimezoneOffsetUtc] = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getTimezoneOffset();
	    babelHelpers.classPrivateFieldLooseBase(this, _months)[_months] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _monthSlots)[_monthSlots] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _monthsSlotsMap)[_monthsSlotsMap] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config] = {
	      eventDurability: 3600000,
	      stepSize: 3600000,
	      weekHolidays: [6, 0],
	      weekStart: 1
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _loc)[_loc] = {
	      weekdays: calendar_util.Util.getWeekdaysLoc()
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _setConfig)[_setConfig]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initCurrentMonthSlots)[_initCurrentMonthSlots]();
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$1)[_bindEvents$1]();
	    setInterval(babelHelpers.classPrivateFieldLooseBase(this, _incrementTime)[_incrementTime].bind(this), 15000);
	  }
	  async updateEventSlotsList() {
	    const month = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]];
	    const currentYear = month.year;
	    const currentMonth = month.month + 1;
	    const arrayKey = currentMonth + '.' + currentYear;
	    babelHelpers.classPrivateFieldLooseBase(this, _accessibility)[_accessibility][arrayKey] = await babelHelpers.classPrivateFieldLooseBase(this, _loadMonthAccessibility)[_loadMonthAccessibility](currentYear, currentMonth);
	    babelHelpers.classPrivateFieldLooseBase(this, _monthSlots)[_monthSlots][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]] = babelHelpers.classPrivateFieldLooseBase(this, _calculateDateTimeSlots)[_calculateDateTimeSlots](currentYear, currentMonth - 1);
	    babelHelpers.classPrivateFieldLooseBase(this, _reCreateCurrentMonth)[_reCreateCurrentMonth]();
	  }
	  selectFirstAvailableDay() {
	    let visibleDays = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].days;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] === 0) {
	      const todayDay = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getDate();
	      visibleDays = visibleDays.filter(day => day.getDay() >= todayDay).slice(0, 14);
	    }
	    let dayToSelect = visibleDays.find(day => day.isEnableBooking());
	    if (dayToSelect === undefined) {
	      dayToSelect = visibleDays[0];
	    }
	    dayToSelect.select();
	  }
	  selectMonthDay() {
	    const dayToSelect = babelHelpers.classPrivateFieldLooseBase(this, _getDayToSelect)[_getDayToSelect]();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDayNumber)[_currentDayNumber] = dayToSelect.day;
	    dayToSelect.select();
	  }
	  highlightMonthDay() {
	    const dayToSelect = babelHelpers.classPrivateFieldLooseBase(this, _getDayToSelect)[_getDayToSelect]();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDayNumber)[_currentDayNumber] = dayToSelect.day;
	    dayToSelect.highlight();
	  }
	  getSelectedTimezoneId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId];
	  }
	  getTimezonePrefix(timezoneOffset) {
	    const offset = timezoneOffset * 1000 - babelHelpers.classPrivateFieldLooseBase(this, _timezoneOffsetUtc)[_timezoneOffsetUtc] * -60000;
	    const date = new Date(babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getTime() + offset);
	    return main_date.DateTimeFormat.format(calendar_util.Util.getTimeFormatShort(), date.getTime() / 1000);
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$1)[_getNodeWrapper$1]();
	  }
	}
	function _bindEvents2$1() {
	  main_core_events.EventEmitter.subscribe('selectDate', event => {
	    const newSelectedDay = event.data;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _selectedDay)[_selectedDay] !== newSelectedDay) {
	      var _babelHelpers$classPr;
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _selectedDay)[_selectedDay]) == null ? void 0 : _babelHelpers$classPr.unSelect();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedDay)[_selectedDay] = newSelectedDay;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDayNumber)[_currentDayNumber] = babelHelpers.classPrivateFieldLooseBase(this, _selectedDay)[_selectedDay].getDay();
	  });
	  main_core_events.EventEmitter.subscribe('onSaveEvent', async event => {
	    if (event.data.state === 'created' || event.data.state === 'not-created') {
	      await this.updateEventSlotsList();
	      this.highlightMonthDay();
	    }
	  });
	  main_core_events.EventEmitter.subscribe('onDeleteEvent', async () => {
	    await this.updateEventSlotsList();
	  });
	  main_core_events.EventEmitter.subscribe('onCreateAnotherEventButtonClick', () => {
	    this.selectFirstAvailableDay();
	  });
	}
	function _incrementTime2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime] = new Date();
	  const timezoneNode = babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimeZone)[_getNodeTimeZone]();
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezoneWrapper)[_getNodeTimezoneWrapper]());
	  main_core.Dom.append(timezoneNode, babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezoneWrapper)[_getNodeTimezoneWrapper]());
	}
	function _setConfig2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekHolidays) {
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekHolidays = babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekHolidays.map(weekDay => calendar_util.Util.getIndByWeekDay(weekDay));
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].yearHolidays) {
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].yearHolidays = babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].yearHolidays;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekStart) {
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekStart = calendar_util.Util.getIndByWeekDay(babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekStart);
	    babelHelpers.classPrivateFieldLooseBase(this, _loc)[_loc].weekdays.push(...babelHelpers.classPrivateFieldLooseBase(this, _loc)[_loc].weekdays.splice(0, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekStart));
	  }
	  const hourOffset = calendar_util.Util.getTimeZoneOffset(babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]) / 60;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].workTimeStart) {
	    const workTimeStart = parseFloat(babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].workTimeStart) - hourOffset;
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeStartHours = workTimeStart - workTimeStart % 1;
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeStartMinutes = workTimeStart % 1 * 60;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].workTimeEnd) {
	    const workTimeEnd = parseFloat(babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].workTimeEnd) - hourOffset;
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeEndHours = workTimeEnd - workTimeEnd % 1;
	    babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeEndMinutes = workTimeEnd % 1 * 60;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] === 'UTC' || !babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList][babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] = 'Africa/Dakar';
	  }
	}
	function _initCurrentMonthSlots2() {
	  const slots = babelHelpers.classPrivateFieldLooseBase(this, _calculateDateTimeSlots)[_calculateDateTimeSlots](babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getFullYear(), babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getMonth());
	  babelHelpers.classPrivateFieldLooseBase(this, _monthSlots)[_monthSlots].push(slots);
	  const slotsMap = babelHelpers.classPrivateFieldLooseBase(this, _getDateTimeSlotsMap)[_getDateTimeSlotsMap](slots);
	  const accessibilityArrayKey = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getMonth() + 1 + '.' + babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getFullYear();
	  babelHelpers.classPrivateFieldLooseBase(this, _monthsSlotsMap)[_monthsSlotsMap][accessibilityArrayKey] = slotsMap;
	  const month = babelHelpers.classPrivateFieldLooseBase(this, _createMonth)[_createMonth](babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getFullYear(), babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getMonth());
	  babelHelpers.classPrivateFieldLooseBase(this, _months)[_months].push(month);
	}
	function _calculateDateTimeSlots2(year, month) {
	  const result = [];
	  const daysCount = new Date(year, month + 1, 0).getDate();
	  const accessibilityArrayKey = month + 1 + '.' + year;
	  const nowTimestamp = babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getTime();
	  const browserSelectedTimezoneOffset = (calendar_util.Util.getTimeZoneOffset(babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]) - babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getTimezoneOffset()) * 60000;
	  const offset = calendar_util.Util.getTimezoneDateFromTimestampUTC(nowTimestamp, babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]) - nowTimestamp;
	  for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++) {
	    const currentDate = new Date(year, month, dayIndex);
	    const from = new Date(year, month, dayIndex, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeStartHours, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeStartMinutes);
	    const to = new Date(year, month, dayIndex, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeEndHours, babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].workTimeEndMinutes);
	    const dayAccessibility = babelHelpers.classPrivateFieldLooseBase(this, _accessibility)[_accessibility][accessibilityArrayKey].filter(event => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _doIntervalsIntersect)[_doIntervalsIntersect](parseInt(event.timestampFromUTC) * 1000, parseInt(event.timestampToUTC) * 1000, from.getTime(), to.getTime());
	    });
	    while (from.getTime() < to.getTime()) {
	      const slotStart = from.getTime();
	      const slotEnd = slotStart + babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].eventDurability;
	      if (slotEnd > to.getTime()) {
	        break;
	      }
	      const slotAccessibility = dayAccessibility.filter(currentSLot => {
	        return babelHelpers.classPrivateFieldLooseBase(this, _doIntervalsIntersect)[_doIntervalsIntersect](parseInt(currentSLot.timestampFromUTC) * 1000, parseInt(currentSLot.timestampToUTC) * 1000, slotStart, slotEnd);
	      });
	      const available = slotAccessibility.length === 0 && !babelHelpers.classPrivateFieldLooseBase(this, _isHoliday)[_isHoliday](currentDate) && slotStart > nowTimestamp;
	      const timeFrom = new Date(slotStart + browserSelectedTimezoneOffset + offset);
	      const timeTo = new Date(timeFrom.getTime() + (slotEnd - slotStart));
	      if (available) {
	        result.push({
	          timeFrom,
	          timeTo
	        });
	      }
	      from.setTime(from.getTime() + babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].stepSize);
	    }
	  }
	  return result;
	}
	function _getDateTimeSlotsMap2(slotList) {
	  const result = [];
	  slotList.forEach(slot => {
	    const timezoneOffset = (babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneOffsetUtc)[_selectedTimezoneOffsetUtc] - babelHelpers.classPrivateFieldLooseBase(this, _timezoneOffsetUtc)[_timezoneOffsetUtc]) * -60 * 1000;
	    const currentSlot = {
	      timeFrom: new Date(slot.timeFrom.getTime() + timezoneOffset),
	      timeTo: new Date(slot.timeTo.getTime() + timezoneOffset)
	    };
	    const dateIndex = currentSlot.timeFrom.getDate();
	    if (result[dateIndex] === undefined) {
	      result[dateIndex] = [];
	    }
	    if (slot.timeFrom.getMonth() === currentSlot.timeFrom.getMonth()) {
	      result[dateIndex].push(currentSlot);
	    }
	  });
	  return result;
	}
	function _createMonth2(year, month) {
	  return {
	    year: year,
	    month: month,
	    currentTimezoneOffset: babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneOffsetUtc)[_selectedTimezoneOffsetUtc],
	    name: babelHelpers.classPrivateFieldLooseBase(this, _getMonthName)[_getMonthName](month),
	    days: babelHelpers.classPrivateFieldLooseBase(this, _getMonthDays)[_getMonthDays](year, month)
	  };
	}
	function _reCreateCurrentMonth2() {
	  const year = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].year;
	  const month = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].month;
	  const monthSlots = babelHelpers.classPrivateFieldLooseBase(this, _monthSlots)[_monthSlots][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]];
	  const arrayKey = month + 1 + '.' + year;
	  babelHelpers.classPrivateFieldLooseBase(this, _monthsSlotsMap)[_monthsSlotsMap][arrayKey] = babelHelpers.classPrivateFieldLooseBase(this, _getDateTimeSlotsMap)[_getDateTimeSlotsMap](monthSlots);
	  babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]] = babelHelpers.classPrivateFieldLooseBase(this, _createMonth)[_createMonth](year, month);
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCalendar)[_updateCalendar]();
	}
	async function _createNextMonth2() {
	  const currentMonth = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]];
	  const currentYear = currentMonth.year;
	  const currentMonthIndex = currentMonth.month;
	  const nextMonthIndex = (currentMonthIndex + 1) % 12;
	  const nextYear = currentYear + Math.floor((currentMonthIndex + 1) / 12);
	  const nextMonth = nextMonthIndex + 1;
	  const arrayKey = nextMonth + '.' + nextYear;
	  babelHelpers.classPrivateFieldLooseBase(this, _accessibility)[_accessibility][arrayKey] = await babelHelpers.classPrivateFieldLooseBase(this, _loadMonthAccessibility)[_loadMonthAccessibility](nextYear, nextMonth);
	  const slots = babelHelpers.classPrivateFieldLooseBase(this, _calculateDateTimeSlots)[_calculateDateTimeSlots](nextYear, nextMonthIndex);
	  babelHelpers.classPrivateFieldLooseBase(this, _monthSlots)[_monthSlots].push(slots);
	  babelHelpers.classPrivateFieldLooseBase(this, _monthsSlotsMap)[_monthsSlotsMap][arrayKey] = babelHelpers.classPrivateFieldLooseBase(this, _getDateTimeSlotsMap)[_getDateTimeSlotsMap](slots);
	  const month = babelHelpers.classPrivateFieldLooseBase(this, _createMonth)[_createMonth](nextYear, nextMonthIndex);
	  babelHelpers.classPrivateFieldLooseBase(this, _months)[_months].push(month);
	}
	async function _loadMonthAccessibility2(year, month) {
	  const firstMonthDay = new Date(year, month - 1, 1);
	  const lastMonthDay = new Date(year, month, 0, 23, 59);
	  const response = await BX.ajax.runAction('calendar.api.sharingajax.getUserAccessibility', {
	    data: {
	      userId: babelHelpers.classPrivateFieldLooseBase(this, _owner$1)[_owner$1].id,
	      timestampFrom: firstMonthDay.getTime(),
	      timestampTo: lastMonthDay.getTime()
	    }
	  });
	  return response.data;
	}
	function _getMonthName2(month) {
	  const currentMonthDate = new Date(babelHelpers.classPrivateFieldLooseBase(this, _nowTime)[_nowTime].getFullYear(), month, 1);
	  return main_date.DateTimeFormat.format('f', currentMonthDate.getTime() / 1000);
	}
	function _getMonthDays2(year, month) {
	  const days = [];
	  const daysCount = new Date(year, month + 1, 0).getDate();
	  const accessibilityArrayKey = month + 1 + '.' + year;
	  for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++) {
	    var _babelHelpers$classPr2;
	    const newDay = new Date(year, month, dayIndex);
	    const slots = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _monthsSlotsMap)[_monthsSlotsMap][accessibilityArrayKey][newDay.getDate()]) != null ? _babelHelpers$classPr2 : [];
	    const params = {
	      value: dayIndex,
	      slots: slots,
	      weekend: babelHelpers.classPrivateFieldLooseBase(this, _isHoliday)[_isHoliday](newDay),
	      enableBooking: slots.length > 0
	    };
	    const day = new Day(params);
	    days.push(day);
	  }
	  return days;
	}
	function _getDayToSelect2() {
	  const monthDays = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].days;
	  let dayToSelect = monthDays.find(day => day.getDay() === babelHelpers.classPrivateFieldLooseBase(this, _currentDayNumber)[_currentDayNumber]);
	  if (dayToSelect === undefined) {
	    dayToSelect = monthDays[monthDays.length - 1];
	  }
	  return dayToSelect;
	}
	function _isHoliday2(day) {
	  const dayMonthKey = day.getDate() + '.' + ('0' + (day.getMonth() + 1)).slice(-2);
	  return babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekHolidays.includes(day.getDay()) || babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].yearHolidays[dayMonthKey] !== undefined;
	}
	function _doIntervalsIntersect2(from1, to1, from2, to2) {
	  const startsInside = from2 <= from1 && from1 < to2;
	  const endsInside = from2 < to1 && to1 <= to2;
	  const startsBeforeEndsAfter = from1 <= from2 && to1 >= to2;
	  return startsInside || endsInside || startsBeforeEndsAfter;
	}
	function _getNodeTimeZone2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneNode)[_selectedTimezoneNode] = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar-sharing__timezone-value">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getFormattedTimezone)[_getFormattedTimezone](babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]));
	  const timezoneSelect = main_core.Tag.render(_t2$1 || (_t2$1 = _$2`
			<div class="calendar-sharing__timezone">
				${0}
				<div class="calendar-sharing__timezone-area">
					<div class="calendar-sharing__timezone-title">${0}:</div>
					${0}
				</div>
			</div>
		`), main_core.Browser.isMobile() ? babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezoneSelect)[_getNodeTimezoneSelect]() : '', main_core.Loc.getMessage('CALENDAR_SHARING_YOR_TIME'), babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneNode)[_selectedTimezoneNode]);
	  babelHelpers.classPrivateFieldLooseBase(this, _getPopupTimezoneSelect)[_getPopupTimezoneSelect]();
	  if (!main_core.Browser.isMobile()) {
	    timezoneSelect.addEventListener('click', () => {
	      const timezonesPopup = babelHelpers.classPrivateFieldLooseBase(this, _getPopupTimezoneSelect)[_getPopupTimezoneSelect]().getPopupWindow();
	      timezonesPopup.show();
	      const popupContent = timezonesPopup.getContentContainer();
	      const selectedTimezoneItem = popupContent.querySelector('.menu-popup-item.--selected');
	      const selectOffset = timezoneSelect.getBoundingClientRect().top + timezoneSelect.offsetHeight / 4 - popupContent.getBoundingClientRect().top;
	      popupContent.scrollTop = selectedTimezoneItem.offsetTop - selectOffset;
	    });
	  }
	  return timezoneSelect;
	}
	function _getPopupTimezoneSelect2() {
	  var _babelHelpers$classPr3, _babelHelpers$classPr4;
	  if ((_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup]) != null && _babelHelpers$classPr3.getPopupWindow().isShown()) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup];
	  }
	  (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup]) == null ? void 0 : _babelHelpers$classPr4.destroy();
	  const items = Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList]).map(timezoneId => ({
	    text: babelHelpers.classPrivateFieldLooseBase(this, _getFormattedTimezone)[_getFormattedTimezone](timezoneId),
	    className: timezoneId === babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
	    onclick: () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateTimezone)[_updateTimezone](timezoneId);
	      babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup].close();
	    }
	  }));
	  babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup] = main_popup.MenuManager.create({
	    id: 'momomiomsiomx92984j',
	    className: 'calendar-sharing-timezone-select-popup',
	    items: items,
	    autoHide: true,
	    maxHeight: window.innerHeight - 150
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _timeZonePopup)[_timeZonePopup];
	}
	function _getNodeTimezoneSelect2() {
	  const selectNode = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<select class="calendar-sharing__timezone-select">
				${0}
			</select>
		`), Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList]).map(timezoneId => main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
					<option value="${0}" ${0}>
						${0}
					</option>
				`), timezoneId, timezoneId === babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] ? 'selected' : '', babelHelpers.classPrivateFieldLooseBase(this, _getFormattedTimezone)[_getFormattedTimezone](timezoneId))));
	  selectNode.addEventListener('change', () => babelHelpers.classPrivateFieldLooseBase(this, _updateTimezone)[_updateTimezone](selectNode.value));
	  return selectNode;
	}
	function _updateTimezone2(timezoneId) {
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId] = timezoneId;
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneOffsetUtc)[_selectedTimezoneOffsetUtc] = -(babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList][babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]].offset / 60);
	  main_core_events.EventEmitter.emit('updateTimezone', {
	    timezone: timezoneId
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneNode)[_selectedTimezoneNode].innerHTML = babelHelpers.classPrivateFieldLooseBase(this, _getFormattedTimezone)[_getFormattedTimezone](babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId)[_selectedTimezoneId]);
	  babelHelpers.classPrivateFieldLooseBase(this, _reCreateCurrentMonth)[_reCreateCurrentMonth]();
	  this.selectMonthDay();
	}
	function _getFormattedTimezone2(timezoneId) {
	  return `${this.getTimezonePrefix(babelHelpers.classPrivateFieldLooseBase(this, _timezoneList)[_timezoneList][timezoneId].offset)} - ${timezoneId}`;
	}
	function _getNodeDaysOfWeek2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].daysOfWeek) {
	    const nodesWeekDays = babelHelpers.classPrivateFieldLooseBase(this, _loc)[_loc].weekdays.map(weekDay => {
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
					<div class="calendar-sharing__month-col --day-of-week">${0}</div>
				`), weekDay);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].daysOfWeek = main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
				<div class="calendar-sharing__month-row">${0}</div>
			`), nodesWeekDays);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].daysOfWeek;
	}
	function _getNodeDay2(param = {}) {
	  var _babelHelpers$classPr5;
	  param.selected = ((_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _selectedDay)[_selectedDay]) == null ? void 0 : _babelHelpers$classPr5.getDay()) === param.value && param.currentMonth === true;
	  const day = new Day(param);
	  return day.render();
	}
	function _getNodeMonth2() {
	  const monthInfo = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]];
	  const year = monthInfo.year;
	  const month = monthInfo.month;
	  const firstDayOfMonth = (new Date(year, month, 7).getDay() - (babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekStart - 1) + 7) % 7;
	  const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
	  const lastDayOfLastMonth = month === 0 ? new Date(year - 1, 11, 0).getDate() : new Date(year, month, 0).getDate();
	  const nodeMonth = main_core.Tag.render(_t7 || (_t7 = _$2`<div class="calendar-sharing__month-row"></div>`));
	  let k = lastDayOfLastMonth - firstDayOfMonth + 1;
	  for (let j = 0; j < firstDayOfMonth; j++) {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getNodeDay)[_getNodeDay]({
	      value: k,
	      notCurrentMonth: true
	    }), nodeMonth);
	    k++;
	  }
	  for (let i = 0; i <= lastDateOfMonth - 1; i++) {
	    const day = monthInfo.days[i];
	    main_core.Dom.append(day.render(), nodeMonth);
	  }
	  let dayOfWeek = (new Date(year, month, lastDateOfMonth).getDay() - babelHelpers.classPrivateFieldLooseBase(this, _config)[_config].weekStart + 7) % 7;
	  for (k = 1; dayOfWeek < 6; dayOfWeek++) {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getNodeDay)[_getNodeDay]({
	      value: k,
	      notCurrentMonth: true
	    }), nodeMonth);
	    k++;
	  }
	  const result = main_core.Tag.render(_t8 || (_t8 = _$2`
			<div class="calendar-sharing__month">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeDaysOfWeek)[_getNodeDaysOfWeek](), nodeMonth);
	  let touchPosition = {
	    x: null
	  };
	  let touchMove = ev => {
	    touchPosition.x = ev.changedTouches[0].clientX;
	  };
	  result.addEventListener('touchstart', ev => {
	    touchMove(ev);
	  });
	  result.addEventListener('touchend', ev => {
	    if (touchPosition.x < ev.changedTouches[0].clientX - 100) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handlePreviousMonthArrowClick)[_handlePreviousMonthArrowClick]();
	    }
	    if (touchPosition.x > ev.changedTouches[0].clientX + 100) {
	      babelHelpers.classPrivateFieldLooseBase(this, _handleNextMonthArrowClick)[_handleNextMonthArrowClick]();
	    }
	    result.style.removeProperty('transform');
	  });
	  result.addEventListener('touchmove', ev => {
	    ev.preventDefault();
	  });
	  return result;
	}
	function _getNodeMonthWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].monthWrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].monthWrapper = main_core.Tag.render(_t9 || (_t9 = _$2`
				<div class="calendar-sharing__calendar-block --month">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeMonth)[_getNodeMonth]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].monthWrapper;
	}
	function _getNodeTimezoneWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].timezoneWrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].timezoneWrapper = main_core.Tag.render(_t10 || (_t10 = _$2`
				<div class="calendar-sharing__calendar-block">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimeZone)[_getNodeTimeZone]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].timezoneWrapper;
	}
	function _getNodeCurrentMonth2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].currentMonth) {
	    const currentMonthName = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].name;
	    const currentYear = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].year;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].currentMonth = main_core.Tag.render(_t11 || (_t11 = _$2`
				<div class="calendar-sharing__calendar-title-day calendar-pub-ui__typography-title">${0}, ${0}</div>
			`), currentMonthName, currentYear);
	    main_core_events.EventEmitter.subscribe(this, 'updateCalendar', () => {
	      const currentMonthName = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].name;
	      const currentYear = babelHelpers.classPrivateFieldLooseBase(this, _months)[_months][babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex]].year;
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].currentMonth.innerHTML = `${currentMonthName}, ${currentYear}`;
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].currentMonth;
	}
	function _updateCalendar2(direction) {
	  main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getNodeMonthWrapper)[_getNodeMonthWrapper]());
	  const nodeMonth = babelHelpers.classPrivateFieldLooseBase(this, _getNodeMonth)[_getNodeMonth]();
	  if (main_core.Type.isString(direction)) {
	    main_core.Dom.addClass(nodeMonth, `--animate-${direction}`);
	    nodeMonth.addEventListener('animationend', () => {
	      main_core.Dom.removeClass(nodeMonth, `--animate-${direction}`);
	    }, {
	      once: true
	    });
	  }
	  main_core.Dom.append(nodeMonth, babelHelpers.classPrivateFieldLooseBase(this, _getNodeMonthWrapper)[_getNodeMonthWrapper]());
	  main_core_events.EventEmitter.emit(this, 'updateCalendar');
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] === 0) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav, '--disabled');
	  } else {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav, '--disabled');
	  }
	}
	function _getNodePrevNav2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav = main_core.Tag.render(_t12 || (_t12 = _$2`
				<div class="calendar-sharing__calendar-nav_prev --disabled" title="${0}"></div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_NAV_PREV'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav, 'click', babelHelpers.classPrivateFieldLooseBase(this, _handlePreviousMonthArrowClick)[_handlePreviousMonthArrowClick].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].prevNav;
	}
	function _getNodeNextNav2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].nextNav) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].nextNav = main_core.Tag.render(_t13 || (_t13 = _$2`
				<div class="calendar-sharing__calendar-nav_next" title="${0}"></div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_NAV_NEXT'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].nextNav, 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleNextMonthArrowClick)[_handleNextMonthArrowClick].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].nextNav;
	}
	function _getNodeNavigation2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].navigation) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].navigation = main_core.Tag.render(_t14 || (_t14 = _$2`
				<div class="calendar-sharing__calendar-nav">
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodePrevNav)[_getNodePrevNav](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeNextNav)[_getNodeNextNav]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].navigation;
	}
	async function _handleNextMonthArrowClick2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] === babelHelpers.classPrivateFieldLooseBase(this, _months)[_months].length - 1) {
	    await babelHelpers.classPrivateFieldLooseBase(this, _createNextMonth)[_createNextMonth]();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] += 1;
	  main_core_events.EventEmitter.emit(this, 'clickNextMonth');
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCalendar)[_updateCalendar]('next');
	  this.selectMonthDay();
	}
	function _handlePreviousMonthArrowClick2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] === 0) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _currentMonthIndex)[_currentMonthIndex] -= 1;
	  main_core_events.EventEmitter.emit(this, 'clickPrevMonth');
	  babelHelpers.classPrivateFieldLooseBase(this, _updateCalendar)[_updateCalendar]('prev');
	  this.selectMonthDay();
	}
	function _getNodeBack2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].back) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].back = main_core.Tag.render(_t15 || (_t15 = _$2`
				<div class="calendar-sharing__calendar-back"></div>
			`));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].back, 'click', () => {
	      main_core_events.EventEmitter.emit('hideSlotSelector', this);
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].back;
	}
	function _getNodeWrapper2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper = main_core.Tag.render(_t16 || (_t16 = _$2`
				<div class="calendar-sharing__calendar">
					<div class="calendar-sharing__calendar-bar">
						${0}
						${0}
						${0}
					</div>
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeBack)[_getNodeBack](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeCurrentMonth)[_getNodeCurrentMonth](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeNavigation)[_getNodeNavigation](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeMonthWrapper)[_getNodeMonthWrapper](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezoneWrapper)[_getNodeTimezoneWrapper]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper;
	}

	let _$3 = t => t,
	  _t$3,
	  _t2$2;
	var _wrapNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapNode");
	var _isHiddenOnStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isHiddenOnStart");
	var _getWrapNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWrapNode");
	var _bindEvents$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _hide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hide");
	var _show = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("show");
	class Base {
	  constructor(options) {
	    Object.defineProperty(this, _show, {
	      value: _show2
	    });
	    Object.defineProperty(this, _hide, {
	      value: _hide2
	    });
	    Object.defineProperty(this, _bindEvents$2, {
	      value: _bindEvents2$2
	    });
	    Object.defineProperty(this, _getWrapNode, {
	      value: _getWrapNode2
	    });
	    Object.defineProperty(this, _wrapNode, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isHiddenOnStart, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _isHiddenOnStart)[_isHiddenOnStart] = options.isHiddenOnStart;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$2)[_bindEvents$2]();
	  }
	  getContent() {
	    return main_core.Tag.render(_t$3 || (_t$3 = _$3`
			<div></div>
		`));
	  }
	  getType() {
	    return 'base';
	  }
	  render() {
	    main_core.Dom.append(this.getContent(), babelHelpers.classPrivateFieldLooseBase(this, _getWrapNode)[_getWrapNode]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode];
	  }
	}
	function _getWrapNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode] = main_core.Tag.render(_t2$2 || (_t2$2 = _$3`<div class="calendar-pub__slots-wrap"></div>`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isHiddenOnStart)[_isHiddenOnStart]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode], '--hidden');
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode];
	}
	function _bindEvents2$2() {
	  main_core_events.EventEmitter.subscribe('selectorTypeChange', ev => {
	    if (ev.data !== this.getType()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hide)[_hide]();
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _show)[_show]();
	    }
	  });
	}
	function _hide2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode], '--hidden');
	}
	function _show2() {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapNode)[_wrapNode], '--hidden');
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$3,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$2,
	  _t7$1;
	var _layout$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _value$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _getNodeCalendarPageMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeCalendarPageMonth");
	var _getNodeCalendarPageDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeCalendarPageDay");
	var _getNodeCalendarPageTimeFrom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeCalendarPageTimeFrom");
	var _getNodeDayInfo = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeDayInfo");
	var _getNodeTimeInterval = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTimeInterval");
	var _getNodeTimezone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTimezone");
	class WidgetDate {
	  constructor() {
	    Object.defineProperty(this, _getNodeTimezone, {
	      value: _getNodeTimezone2
	    });
	    Object.defineProperty(this, _getNodeTimeInterval, {
	      value: _getNodeTimeInterval2
	    });
	    Object.defineProperty(this, _getNodeDayInfo, {
	      value: _getNodeDayInfo2
	    });
	    Object.defineProperty(this, _getNodeCalendarPageTimeFrom, {
	      value: _getNodeCalendarPageTimeFrom2
	    });
	    Object.defineProperty(this, _getNodeCalendarPageDay, {
	      value: _getNodeCalendarPageDay2
	    });
	    Object.defineProperty(this, _getNodeCalendarPageMonth, {
	      value: _getNodeCalendarPageMonth2
	    });
	    Object.defineProperty(this, _layout$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3] = {
	      calendarPage: {
	        month: null,
	        day: null,
	        timeFrom: null
	      },
	      dayInfo: null,
	      timeInterval: null,
	      timezone: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1] = {
	      from: null,
	      to: null,
	      timezone: null
	    };
	  }
	  updateValue(data) {
	    if (data.from) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].from = data.from;
	    }
	    if (data.to) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].to = data.to;
	    }
	    if (data.timezone) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].timezone = data.timezone;
	    }
	    this.updateLayout();
	  }
	  updateLayout() {
	    const timestampFrom = babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].from.getTime() / 1000;
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageTimeFrom)[_getNodeCalendarPageTimeFrom]().innerText = main_date.DateTimeFormat.format(calendar_util.Util.getTimeFormatShort(), timestampFrom);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageMonth)[_getNodeCalendarPageMonth]().innerText = main_date.DateTimeFormat.format('f', timestampFrom);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageDay)[_getNodeCalendarPageDay]().innerText = main_date.DateTimeFormat.format('d', timestampFrom);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeDayInfo)[_getNodeDayInfo]().innerText = main_date.DateTimeFormat.format(calendar_util.Util.getDayOfWeekMonthFormat(), timestampFrom);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimeInterval)[_getNodeTimeInterval]().innerText = calendar_util.Util.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].from, babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].to);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezone)[_getNodeTimezone]().innerText = calendar_util.Util.getFormattedTimezone(babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].timezone);
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezone)[_getNodeTimezone]().setAttribute('title', calendar_util.Util.getFormattedTimezone(babelHelpers.classPrivateFieldLooseBase(this, _value$1)[_value$1].timezone));
	  }
	  render() {
	    return main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="calendar-pub__form-date">
					<div class="calendar-pub__form-date-day">
						${0}
						<div class="calendar-pub__form-date-content">
							${0}
							${0}
						</div>
					</div>
					<div class="calendar-pub__form-date-info">
						${0}
						${0}
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageMonth)[_getNodeCalendarPageMonth](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageDay)[_getNodeCalendarPageDay](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeCalendarPageTimeFrom)[_getNodeCalendarPageTimeFrom](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeDayInfo)[_getNodeDayInfo](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimeInterval)[_getNodeTimeInterval](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeTimezone)[_getNodeTimezone]());
	  }
	}
	function _getNodeCalendarPageMonth2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.month) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.month = main_core.Tag.render(_t2$3 || (_t2$3 = _$4`
				<div class="calendar-pub__form-date-day_month"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.month;
	}
	function _getNodeCalendarPageDay2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.day) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.day = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
				<div class="calendar-pub__form-date-day_num"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.day;
	}
	function _getNodeCalendarPageTimeFrom2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.timeFrom) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.timeFrom = main_core.Tag.render(_t4$2 || (_t4$2 = _$4`
				<div class="calendar-pub__form-date-day_time">13:00</div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].calendarPage.timeFrom;
	}
	function _getNodeDayInfo2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].dayInfo) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].dayInfo = main_core.Tag.render(_t5$2 || (_t5$2 = _$4`
				<div class="calendar-pub__form-date-info_day"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].dayInfo;
	}
	function _getNodeTimeInterval2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timeInterval) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timeInterval = main_core.Tag.render(_t6$2 || (_t6$2 = _$4`
				<div class="calendar-pub__form-date-info_time"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timeInterval;
	}
	function _getNodeTimezone2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timezone) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timezone = main_core.Tag.render(_t7$1 || (_t7$1 = _$4`
				<div class="calendar-pub__form-date-info_time-zone"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].timezone;
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$4,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$3,
	  _t7$2,
	  _t8$1;
	var _layout$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _value$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _widgetDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgetDate");
	var _owner$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _link$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("link");
	var _sharingUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sharingUser");
	var _phoneDb = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("phoneDb");
	var _isFromCrm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFromCrm");
	var _hasContactData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasContactData");
	var _isPhoneFeatureEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPhoneFeatureEnabled");
	var _isMailFeatureEnabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMailFeatureEnabled");
	var _inputData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputData");
	var _inputErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputErrors");
	var _getNodeWrapper$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWrapper");
	var _getEventName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEventName");
	var _getNodeButtonSend = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeButtonSend");
	var _handleSaveButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSaveButtonClick");
	var _saveEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("saveEvent");
	var _parseDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("parseDate");
	var _validateData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateData");
	var _validatePhone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validatePhone");
	var _validateEmail = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateEmail");
	var _clearContactDataError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearContactDataError");
	var _clearContactNameError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearContactNameError");
	var _showFullContactPlaceholder = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showFullContactPlaceholder");
	var _isMailContactOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isMailContactOnly");
	var _isPhoneContactOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPhoneContactOnly");
	var _isCrmAndHasContact = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCrmAndHasContact");
	var _getNodeWidgetDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWidgetDate");
	var _getNodeFormArea = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeFormArea");
	var _getContactDataPlaceholder = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContactDataPlaceholder");
	var _getNodeInputName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeInputName");
	var _getNodeInputContact = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeInputContact");
	var _getNodeInputDescription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeInputDescription");
	var _getNodeInputError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeInputError");
	var _renderInputErrors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderInputErrors");
	var _getNodeBack$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeBack");
	var _onPhoneInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPhoneInput");
	var _getTextBeforeCursor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextBeforeCursor");
	var _setCursorToFormattedPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setCursorToFormattedPosition");
	var _getTextEscapedForRegex = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextEscapedForRegex");
	var _onPhoneInputKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onPhoneInputKeyDown");
	var _isPhoneTypeInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isPhoneTypeInput");
	var _isDigit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDigit");
	var _isControlKey = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isControlKey");
	var _formatPhone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("formatPhone");
	var _findMask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("findMask");
	class Form extends Base {
	  constructor(options) {
	    super({
	      isHiddenOnStart: options.isHiddenOnStart
	    });
	    Object.defineProperty(this, _findMask, {
	      value: _findMask2
	    });
	    Object.defineProperty(this, _formatPhone, {
	      value: _formatPhone2
	    });
	    Object.defineProperty(this, _isControlKey, {
	      value: _isControlKey2
	    });
	    Object.defineProperty(this, _isDigit, {
	      value: _isDigit2
	    });
	    Object.defineProperty(this, _isPhoneTypeInput, {
	      value: _isPhoneTypeInput2
	    });
	    Object.defineProperty(this, _onPhoneInputKeyDown, {
	      value: _onPhoneInputKeyDown2
	    });
	    Object.defineProperty(this, _getTextEscapedForRegex, {
	      value: _getTextEscapedForRegex2
	    });
	    Object.defineProperty(this, _setCursorToFormattedPosition, {
	      value: _setCursorToFormattedPosition2
	    });
	    Object.defineProperty(this, _getTextBeforeCursor, {
	      value: _getTextBeforeCursor2
	    });
	    Object.defineProperty(this, _onPhoneInput, {
	      value: _onPhoneInput2
	    });
	    Object.defineProperty(this, _getNodeBack$1, {
	      value: _getNodeBack2$1
	    });
	    Object.defineProperty(this, _renderInputErrors, {
	      value: _renderInputErrors2
	    });
	    Object.defineProperty(this, _getNodeInputError, {
	      value: _getNodeInputError2
	    });
	    Object.defineProperty(this, _getNodeInputDescription, {
	      value: _getNodeInputDescription2
	    });
	    Object.defineProperty(this, _getNodeInputContact, {
	      value: _getNodeInputContact2
	    });
	    Object.defineProperty(this, _getNodeInputName, {
	      value: _getNodeInputName2
	    });
	    Object.defineProperty(this, _getContactDataPlaceholder, {
	      value: _getContactDataPlaceholder2
	    });
	    Object.defineProperty(this, _getNodeFormArea, {
	      value: _getNodeFormArea2
	    });
	    Object.defineProperty(this, _getNodeWidgetDate, {
	      value: _getNodeWidgetDate2
	    });
	    Object.defineProperty(this, _isCrmAndHasContact, {
	      value: _isCrmAndHasContact2
	    });
	    Object.defineProperty(this, _isPhoneContactOnly, {
	      value: _isPhoneContactOnly2
	    });
	    Object.defineProperty(this, _isMailContactOnly, {
	      value: _isMailContactOnly2
	    });
	    Object.defineProperty(this, _showFullContactPlaceholder, {
	      value: _showFullContactPlaceholder2
	    });
	    Object.defineProperty(this, _clearContactNameError, {
	      value: _clearContactNameError2
	    });
	    Object.defineProperty(this, _clearContactDataError, {
	      value: _clearContactDataError2
	    });
	    Object.defineProperty(this, _validateEmail, {
	      value: _validateEmail2
	    });
	    Object.defineProperty(this, _validatePhone, {
	      value: _validatePhone2
	    });
	    Object.defineProperty(this, _validateData, {
	      value: _validateData2
	    });
	    Object.defineProperty(this, _parseDate, {
	      value: _parseDate2
	    });
	    Object.defineProperty(this, _saveEvent, {
	      value: _saveEvent2
	    });
	    Object.defineProperty(this, _handleSaveButtonClick, {
	      value: _handleSaveButtonClick2
	    });
	    Object.defineProperty(this, _getNodeButtonSend, {
	      value: _getNodeButtonSend2
	    });
	    Object.defineProperty(this, _getEventName, {
	      value: _getEventName2
	    });
	    Object.defineProperty(this, _getNodeWrapper$2, {
	      value: _getNodeWrapper2$2
	    });
	    Object.defineProperty(this, _layout$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _widgetDate, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _owner$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _link$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sharingUser, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _phoneDb, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isFromCrm, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasContactData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isPhoneFeatureEnabled, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isMailFeatureEnabled, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inputData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inputErrors, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _owner$2)[_owner$2] = options.owner;
	    babelHelpers.classPrivateFieldLooseBase(this, _link$1)[_link$1] = options.link;
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate] = new WidgetDate();
	    babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser] = options.sharingUser;
	    babelHelpers.classPrivateFieldLooseBase(this, _isFromCrm)[_isFromCrm] = options.isFromCrm;
	    babelHelpers.classPrivateFieldLooseBase(this, _hasContactData)[_hasContactData] = options.hasContactData;
	    babelHelpers.classPrivateFieldLooseBase(this, _isPhoneFeatureEnabled)[_isPhoneFeatureEnabled] = options.isPhoneFeatureEnabled;
	    babelHelpers.classPrivateFieldLooseBase(this, _isMailFeatureEnabled)[_isMailFeatureEnabled] = options.isMailFeatureEnabled;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4] = {
	      wrapper: null,
	      buttonSend: null,
	      widgetDate: null,
	      formArea: null,
	      back: null,
	      calendarPage: {
	        month: null,
	        day: null,
	        timeFrom: null
	      },
	      dayInfo: null,
	      timeInterval: null,
	      timezone: null,
	      inputs: {
	        name: null,
	        contact: null,
	        description: null
	      }
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2] = {
	      from: null,
	      to: null,
	      timezone: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData] = {
	      authorName: '',
	      contactData: '',
	      description: ''
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors] = {
	      authorNameEmpty: false,
	      contactDataEmpty: false,
	      contactDataIncorrect: false
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _phoneDb)[_phoneDb] = null;
	  }
	  cleanDescription() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputDescription)[_getNodeInputDescription]().value = null;
	  }
	  getType() {
	    return 'form';
	  }
	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$2)[_getNodeWrapper$2]();
	  }
	  updateFormValue(data) {
	    if (data.from) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from = data.from;
	    }
	    if (data.to) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to = data.to;
	    }
	    if (data.timezone) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone = data.timezone;
	    }
	    this.updateFormLayout();
	  }
	  updateFormLayout() {
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate].updateValue(babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2]);
	  }
	  clearInputErrors() {
	    babelHelpers.classPrivateFieldLooseBase(this, _clearContactNameError)[_clearContactNameError]();
	    babelHelpers.classPrivateFieldLooseBase(this, _clearContactDataError)[_clearContactDataError]();
	  }
	}
	function _getNodeWrapper2$2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper = main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="calendar-pub__form">
					<div class="calendar-sharing__calendar-bar">
						${0}
						<div class="calendar-sharing__calendar-title-day calendar-pub-ui__typography-title">
							${0}
						</div>
					</div>
					<div class="calendar-sharing__calendar-block">
						${0}
					</div>
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeBack$1)[_getNodeBack$1](), babelHelpers.classPrivateFieldLooseBase(this, _getEventName)[_getEventName](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeWidgetDate)[_getNodeWidgetDate](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeFormArea)[_getNodeFormArea]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper;
	}
	function _getEventName2() {
	  return main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_NAME', {
	    '#OWNER_NAME#': babelHelpers.classPrivateFieldLooseBase(this, _owner$2)[_owner$2].name + ' ' + babelHelpers.classPrivateFieldLooseBase(this, _owner$2)[_owner$2].lastName
	  });
	}
	function _getNodeButtonSend2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend = main_core.Tag.render(_t2$4 || (_t2$4 = _$5`
				<div class="calendar-pub-ui__btn">
					<div class="calendar-pub-ui__btn-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_CREATE_MEETING'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _handleSaveButtonClick)[_handleSaveButtonClick]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend;
	}
	async function _handleSaveButtonClick2() {
	  if (main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend, '--wait')) {
	    return;
	  }
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend, '--wait');
	  this.clearInputErrors();
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _validateData)[_validateData]()) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend, '--wait');
	    return;
	  }
	  const isSuccessful = await babelHelpers.classPrivateFieldLooseBase(this, _saveEvent)[_saveEvent]();
	  if (isSuccessful) {
	    main_core_events.EventEmitter.emit('selectorTypeChange', 'event', {
	      eventName: babelHelpers.classPrivateFieldLooseBase(this, _getEventName)[_getEventName](),
	      from: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from,
	      to: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to,
	      timezone: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone
	    });
	  }
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].buttonSend, '--wait');
	}
	async function _saveEvent2() {
	  let response = null;
	  try {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isFromCrm)[_isFromCrm]) {
	      response = await BX.ajax.runAction('calendar.api.sharingajax.saveCrmEvent', {
	        data: {
	          ownerCreated: babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser].ownerCreated,
	          ownerId: babelHelpers.classPrivateFieldLooseBase(this, _owner$2)[_owner$2].id,
	          dateFrom: babelHelpers.classPrivateFieldLooseBase(this, _parseDate)[_parseDate](babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from),
	          dateTo: babelHelpers.classPrivateFieldLooseBase(this, _parseDate)[_parseDate](babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to),
	          userName: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName,
	          userContact: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData,
	          timezone: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone,
	          crmDealLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _link$1)[_link$1].hash,
	          description: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].description
	        }
	      });
	    } else {
	      response = await BX.ajax.runAction('calendar.api.sharingajax.saveEvent', {
	        data: {
	          ownerCreated: babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser].ownerCreated,
	          ownerId: babelHelpers.classPrivateFieldLooseBase(this, _owner$2)[_owner$2].id,
	          userName: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName,
	          userContact: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData,
	          dateFrom: babelHelpers.classPrivateFieldLooseBase(this, _parseDate)[_parseDate](babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from),
	          dateTo: babelHelpers.classPrivateFieldLooseBase(this, _parseDate)[_parseDate](babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to),
	          timezone: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone,
	          parentLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _link$1)[_link$1].hash,
	          description: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].description
	        }
	      });
	    }
	  } catch (e) {
	    response = e;
	  }
	  if (response.errors.length === 0) {
	    main_core_events.EventEmitter.emit('onSaveEvent', {
	      eventName: babelHelpers.classPrivateFieldLooseBase(this, _getEventName)[_getEventName](),
	      from: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from,
	      to: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to,
	      timezone: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone,
	      eventId: response.data.eventId,
	      eventLinkId: response.data.eventLinkId,
	      eventLinkHash: response.data.eventLinkHash,
	      eventLinkShortUrl: response.data.eventLinkShortUrl,
	      userName: babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName,
	      state: 'created',
	      isView: false
	    });
	    return true;
	  }
	  if (response.data.contactDataError || response.data.isEmptyContactName) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataIncorrect = response.data.contactDataError === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].authorNameEmpty = response.data.isEmptyContactName === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _renderInputErrors)[_renderInputErrors]();
	    return false;
	  }
	  main_core_events.EventEmitter.emit('onSaveEvent', {
	    eventName: babelHelpers.classPrivateFieldLooseBase(this, _getEventName)[_getEventName](),
	    from: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].from,
	    to: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].to,
	    timezone: babelHelpers.classPrivateFieldLooseBase(this, _value$2)[_value$2].timezone,
	    state: 'not-created',
	    isView: false
	  });
	  return false;
	}
	function _parseDate2(date) {
	  const dateInFormat = main_date.DateTimeFormat.format(calendar_util.Util.getDateFormat(), date.getTime() / 1000);
	  const timeInFormat = main_date.DateTimeFormat.format(calendar_util.Util.getTimeFormat(), date.getTime() / 1000);
	  return dateInFormat + ' ' + timeInFormat;
	}
	function _validateData2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isCrmAndHasContact)[_isCrmAndHasContact]()) {
	    return true;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName.length === 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].authorNameEmpty = true;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData.length === 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataEmpty = true;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataEmpty) {
	    babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataIncorrect = !babelHelpers.classPrivateFieldLooseBase(this, _validatePhone)[_validatePhone]() && !babelHelpers.classPrivateFieldLooseBase(this, _validateEmail)[_validateEmail]();
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _renderInputErrors)[_renderInputErrors]();
	  return !babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].authorNameEmpty && !babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataEmpty && !babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataIncorrect;
	}
	function _validatePhone2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isMailContactOnly)[_isMailContactOnly]()) {
	    return false;
	  }
	  const phone = babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData.replace(/[()\s\-]+/g, '');
	  const match = phone.match(/(^\+?\d{4,25}$)/i);
	  return (match == null ? void 0 : match[0]) === phone;
	}
	function _validateEmail2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isPhoneContactOnly)[_isPhoneContactOnly]()) {
	    return false;
	  }
	  const match = babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData.match(/(^[^@]+@.+$)/i);
	  return (match == null ? void 0 : match[0]) === babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData;
	}
	function _clearContactDataError2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataEmpty = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataIncorrect = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _renderInputErrors)[_renderInputErrors]();
	}
	function _clearContactNameError2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].authorNameEmpty = false;
	  babelHelpers.classPrivateFieldLooseBase(this, _renderInputErrors)[_renderInputErrors]();
	}
	function _showFullContactPlaceholder2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isMailContactOnly)[_isMailContactOnly]() && !babelHelpers.classPrivateFieldLooseBase(this, _isPhoneContactOnly)[_isPhoneContactOnly]();
	}
	function _isMailContactOnly2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _isPhoneFeatureEnabled)[_isPhoneFeatureEnabled] && babelHelpers.classPrivateFieldLooseBase(this, _isMailFeatureEnabled)[_isMailFeatureEnabled];
	}
	function _isPhoneContactOnly2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isPhoneFeatureEnabled)[_isPhoneFeatureEnabled] && !babelHelpers.classPrivateFieldLooseBase(this, _isMailFeatureEnabled)[_isMailFeatureEnabled];
	}
	function _isCrmAndHasContact2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isFromCrm)[_isFromCrm] && babelHelpers.classPrivateFieldLooseBase(this, _hasContactData)[_hasContactData];
	}
	function _getNodeWidgetDate2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].widgetDate) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].widgetDate = babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate].render();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].widgetDate;
	}
	function _getNodeFormArea2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].formArea) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].nameInputError = babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputError)[_getNodeInputError]();
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].contactInputError = babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputError)[_getNodeInputError]();
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].formArea = main_core.Tag.render(_t3$3 || (_t3$3 = _$5`
				<div class="calendar-sharing__calendar-block --form">
					<div class="calendar-sharing__form-area">
						<div class="calendar-sharing__form-input">
							${0}
							<div class="calendar-sharing__form-input-title">${0}<span>*</span></div>
							${0}
						</div>
						<div class="calendar-sharing__form-input">
							${0}
							<div class="calendar-sharing__form-input-title">${0}<span>*</span></div>
							${0}
						</div>
						<div class="calendar-sharing__form-input">
							${0}
							<div class="calendar-sharing__form-input-title">${0}</div>
						</div>
					</div>
					<div class="calendar-pub__welcome-bottom">
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputName)[_getNodeInputName](), main_core.Loc.getMessage('CALENDAR_SHARING_FORM_INPUT_NAME'), babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].nameInputError, babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputContact)[_getNodeInputContact](), babelHelpers.classPrivateFieldLooseBase(this, _getContactDataPlaceholder)[_getContactDataPlaceholder](), babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].contactInputError, babelHelpers.classPrivateFieldLooseBase(this, _getNodeInputDescription)[_getNodeInputDescription](), main_core.Loc.getMessage('CALENDAR_SHARING_FORM_INPUT_INFO'), babelHelpers.classPrivateFieldLooseBase(this, _getNodeButtonSend)[_getNodeButtonSend]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].formArea;
	}
	function _getContactDataPlaceholder2() {
	  let messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_ENABLED';
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isMailContactOnly)[_isMailContactOnly]()) {
	    messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_DISABLED';
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isPhoneContactOnly)[_isPhoneContactOnly]()) {
	    messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_MAIL_FEATURE_DISABLED';
	  }
	  return main_core.Loc.getMessage(messageCode);
	}
	function _getNodeInputName2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name) {
	    var _babelHelpers$classPr;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name = main_core.Tag.render(_t4$3 || (_t4$3 = _$5`
				<input type="text" placeholder=" " class="calendar-sharing__form-input-area">
			`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasContactData)[_hasContactData]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name, '--hidden');
	    } else if ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser]) != null && _babelHelpers$classPr.userName) {
	      var _babelHelpers$classPr2;
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name.value = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser]) == null ? void 0 : _babelHelpers$classPr2.userName;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name.value;
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name, 'input', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].authorName = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name.value;
	    });
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name, 'focus', babelHelpers.classPrivateFieldLooseBase(this, _clearContactNameError)[_clearContactNameError].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name;
	}
	function _getNodeInputContact2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact = main_core.Tag.render(_t5$3 || (_t5$3 = _$5`
				<input type="text" placeholder=" " class="calendar-sharing__form-input-area">
			`));
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isMailContactOnly)[_isMailContactOnly]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.inputMode = 'email';
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isPhoneContactOnly)[_isPhoneContactOnly]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.inputMode = 'tel';
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasContactData)[_hasContactData]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact, '--hidden');
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser]) {
	      if (babelHelpers.classPrivateFieldLooseBase(this, _isMailFeatureEnabled)[_isMailFeatureEnabled] && babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser].personalMailbox) {
	        var _babelHelpers$classPr3;
	        babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.value = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser]) == null ? void 0 : _babelHelpers$classPr3.personalMailbox;
	      } else if (babelHelpers.classPrivateFieldLooseBase(this, _isPhoneFeatureEnabled)[_isPhoneFeatureEnabled] && babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser].personalPhone) {
	        var _babelHelpers$classPr4;
	        babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.value = (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _sharingUser)[_sharingUser]) == null ? void 0 : _babelHelpers$classPr4.personalPhone;
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.value;
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact, 'input', event => {
	      babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.value;
	      babelHelpers.classPrivateFieldLooseBase(this, _onPhoneInput)[_onPhoneInput](event);
	    });
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _onPhoneInputKeyDown)[_onPhoneInputKeyDown].bind(this));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact, 'focus', babelHelpers.classPrivateFieldLooseBase(this, _clearContactDataError)[_clearContactDataError].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact;
	}
	function _getNodeInputDescription2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description = main_core.Tag.render(_t6$3 || (_t6$3 = _$5`
				<textarea type="text" placeholder=" " class="calendar-sharing__form-input-area --textarea"></textarea>
			`));
	    babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].description = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description.value;
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description, 'input', () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].description = babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description.value;
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.description;
	}
	function _getNodeInputError2() {
	  return main_core.Tag.render(_t7$2 || (_t7$2 = _$5`
			<span class="calendar-sharing__form-input-error"></span>
		`));
	}
	function _renderInputErrors2() {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name.parentNode, '--error');
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.parentNode, '--error');
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].authorNameEmpty) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.name.parentNode, '--error');
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].nameInputError.innerText = main_core.Loc.getMessage('CALENDAR_SHARING_INPUT_ERROR_REQUIRED');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataEmpty) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.parentNode, '--error');
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].contactInputError.innerText = main_core.Loc.getMessage('CALENDAR_SHARING_INPUT_ERROR_REQUIRED');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inputErrors)[_inputErrors].contactDataIncorrect) {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.parentNode, '--error');
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].contactInputError.innerText = main_core.Loc.getMessage('CALENDAR_SHARING_INPUT_ERROR_INCORRECT');
	  }
	}
	function _getNodeBack2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].back) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].back = main_core.Tag.render(_t8$1 || (_t8$1 = _$5`
				<div class="calendar-sharing__calendar-back"></div>
			`));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].back, 'click', () => {
	      main_core_events.EventEmitter.emit('selectorTypeChange', 'slot-list');
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].back;
	}
	function _onPhoneInput2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _clearContactDataError)[_clearContactDataError]();
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isPhoneTypeInput)[_isPhoneTypeInput]()) {
	    return;
	  }
	  const textBeforeCursor = babelHelpers.classPrivateFieldLooseBase(this, _getTextBeforeCursor)[_getTextBeforeCursor](babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact);
	  babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData = babelHelpers.classPrivateFieldLooseBase(this, _formatPhone)[_formatPhone](babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact.value = babelHelpers.classPrivateFieldLooseBase(this, _inputData)[_inputData].contactData;
	  babelHelpers.classPrivateFieldLooseBase(this, _setCursorToFormattedPosition)[_setCursorToFormattedPosition](babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].inputs.contact, textBeforeCursor);
	}
	function _getTextBeforeCursor2(input) {
	  const selectionStart = input.selectionStart;
	  return input.value.slice(0, selectionStart);
	}
	function _setCursorToFormattedPosition2(input, textBeforeCursor) {
	  const firstPart = babelHelpers.classPrivateFieldLooseBase(this, _getTextEscapedForRegex)[_getTextEscapedForRegex](textBeforeCursor.slice(0, -1));
	  const lastCharacter = babelHelpers.classPrivateFieldLooseBase(this, _getTextEscapedForRegex)[_getTextEscapedForRegex](textBeforeCursor.slice(-1));
	  const matches = input.value.match(`${firstPart}.*?${lastCharacter}`);
	  if (!matches) {
	    return;
	  }
	  const match = matches[0];
	  const formattedPosition = input.value.indexOf(match) + match.length;
	  input.setSelectionRange(formattedPosition, formattedPosition);
	}
	function _getTextEscapedForRegex2(text) {
	  return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	}
	function _onPhoneInputKeyDown2(e) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isPhoneTypeInput)[_isPhoneTypeInput]()) {
	    return;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isDigit)[_isDigit](e.key) && !babelHelpers.classPrivateFieldLooseBase(this, _isControlKey)[_isControlKey](e.key) && !calendar_util.Util.isAnyModifierKeyPressed(e)) {
	    e.preventDefault();
	  }
	}
	function _isPhoneTypeInput2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isPhoneContactOnly)[_isPhoneContactOnly]() || babelHelpers.classPrivateFieldLooseBase(this, _showFullContactPlaceholder)[_showFullContactPlaceholder]() && this.contactData.slice(0, 1) === '+';
	}
	function _isDigit2(key) {
	  return /^\d+$/.test(key);
	}
	function _isControlKey2(key) {
	  return ['Esc', 'Delete', 'Backspace', 'Tab'].indexOf(key) >= 0 || key.includes('Arrow');
	}
	function _formatPhone2(value) {
	  var _value2;
	  (_value2 = value) != null ? _value2 : value = '';
	  let hasPlus = value.indexOf('+') === 0;
	  value = value.replace(/\D/g, '');
	  if (!hasPlus && value.substr(0, 1) === '8') {
	    value = '7' + value.substr(1);
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _phoneDb)[_phoneDb]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _phoneDb)[_phoneDb] = "247,ac,___-____|376,ad,___-___-___|971,ae,___-_-___-____|93,af,__-__-___-____|1268,ag,_ (___) ___-____|1264,ai,_ (___) ___-____|355,al,___ (___) ___-___|374,am,___-__-___-___|599,bq,___-___-____|244,ao,___ (___) ___-___|6721,aq,___-___-___|54,ar,__ (___) ___-____|1684,as,_ (___) ___-____|43,at,__ (___) ___-____|61,au,__-_-____-____|297,aw,___-___-____|994,az,___ (__) ___-__-__|387,ba,___-__-____|1246,bb,_ (___) ___-____|880,bd,___-__-___-___|32,be,__ (___) ___-___|226,bf,___-__-__-____|359,bg,___ (___) ___-___|973,bh,___-____-____|257,bi,___-__-__-____|229,bj,___-__-__-____|1441,bm,_ (___) ___-____|673,bn,___-___-____|591,bo,___-_-___-____|55,br,__-(__)-____-____|1242,bs,_ (___) ___-____|975,bt,___-_-___-___|267,bw,___-__-___-___|375,by,___ (__) ___-__-__|501,bz,___-___-____|243,cd,___ (___) ___-___|236,cf,___-__-__-____|242,cg,___-__-___-____|41,ch,__-__-___-____|225,ci,___-__-___-___|682,ck,___-__-___|56,cl,__-_-____-____|237,cm,___-____-____|86,cn,__ (___) ____-___|57,co,__ (___) ___-____|506,cr,___-____-____|53,cu,__-_-___-____|238,cv,___ (___) __-__|357,cy,___-__-___-___|420,cz,___ (___) ___-___|49,de,__-___-___|253,dj,___-__-__-__-__|45,dk,__-__-__-__-__|1767,dm,_ (___) ___-____|1809,do,_ (___) ___-____|,do,_ (___) ___-____|213,dz,___-__-___-____|593,ec,___-_-___-____|372,ee,___-___-____|20,eg,__ (___) ___-____|291,er,___-_-___-___|34,es,__ (___) ___-___|251,et,___-__-___-____|358,fi,___ (___) ___-__-__|679,fj,___-__-_____|500,fk,___-_____|691,fm,___-___-____|298,fo,___-___-___|262,fr,___-_____-____|33,fr,__ (___) ___-___|508,fr,___-__-____|590,fr,___ (___) ___-___|241,ga,___-_-__-__-__|1473,gd,_ (___) ___-____|995,ge,___ (___) ___-___|594,gf,___-_____-____|233,gh,___ (___) ___-___|350,gi,___-___-_____|299,gl,___-__-__-__|220,gm,___ (___) __-__|224,gn,___-__-___-___|240,gq,___-__-___-____|30,gr,__ (___) ___-____|502,gt,___-_-___-____|1671,gu,_ (___) ___-____|245,gw,___-_-______|592,gy,___-___-____|852,hk,___-____-____|504,hn,___-____-____|385,hr,___-__-___-___|509,ht,___-__-__-____|36,hu,__ (___) ___-___|62,id,__-__-___-__|353,ie,___ (___) ___-___|972,il,___-_-___-____|91,in,__ (____) ___-___|246,io,___-___-____|964,iq,___ (___) ___-____|98,ir,__ (___) ___-____|354,is,___-___-____|39,it,__ (___) ____-___|1876,jm,_ (___) ___-____|962,jo,___-_-____-____|81,jp,__ (___) ___-___|254,ke,___-___-______|996,kg,___ (___) ___-___|855,kh,___ (__) ___-___|686,ki,___-__-___|269,km,___-__-_____|1869,kn,_ (___) ___-____|850,kp,___-___-___|82,kr,__-__-___-____|965,kw,___-____-____|1345,ky,_ (___) ___-____|77,kz,_ (___) ___-__-__|856,la,___-__-___-___|961,lb,___-_-___-___|1758,lc,_ (___) ___-____|423,li,___ (___) ___-____|94,lk,__-__-___-____|231,lr,___-__-___-___|266,ls,___-_-___-____|370,lt,___ (___) __-___|352,lu,___ (___) ___-___|371,lv,___-__-___-___|218,ly,___-__-___-___|212,ma,___-__-____-___|377,mc,___-__-___-___|373,md,___-____-____|382,me,___-__-___-___|261,mg,___-__-__-_____|692,mh,___-___-____|389,mk,___-__-___-___|223,ml,___-__-__-____|95,mm,__-___-___|976,mn,___-__-__-____|853,mo,___-____-____|1670,mp,_ (___) ___-____|596,mq,___ (___) __-__-__|222,mr,___ (__) __-____|1664,ms,_ (___) ___-____|356,mt,___-____-____|230,mu,___-___-____|960,mv,___-___-____|265,mw,___-_-____-____|52,mx,__-__-__-____|60,my,__-_-___-___|258,mz,___-__-___-___|264,na,___-__-___-____|687,nc,___-__-____|227,ne,___-__-__-____|6723,nf,___-___-___|234,ng,___-__-___-__|505,ni,___-____-____|31,nl,__-__-___-____|47,no,__ (___) __-___|977,np,___-__-___-___|674,nr,___-___-____|683,nu,___-____|64,nz,__-__-___-___|968,om,___-__-___-___|507,pa,___-___-____|51,pe,__ (___) ___-___|689,pf,___-__-__-__|675,pg,___ (___) __-___|63,ph,__ (___) ___-____|92,pk,__ (___) ___-____|48,pl,__ (___) ___-___|970,ps,___-__-___-____|351,pt,___-__-___-____|680,pw,___-___-____|595,py,___ (___) ___-___|974,qa,___-____-____|40,ro,__-__-___-____|381,rs,___-__-___-____|7,ru,_ (___) ___-__-__|250,rw,___ (___) ___-___|966,sa,___-_-___-____|677,sb,___-_____|248,sc,___-_-___-___|249,sd,___-__-___-____|46,se,__-__-___-____|65,sg,__-____-____|386,si,___-__-___-___|421,sk,___ (___) ___-___|232,sl,___-__-______|378,sm,___-____-______|221,sn,___-__-___-____|252,so,___-_-___-___|597,sr,___-___-___|211,ss,___-__-___-____|239,st,___-__-_____|503,sv,___-__-__-____|1721,sx,_ (___) ___-____|963,sy,___-__-____-___|268,sz,___ (__) __-____|1649,tc,_ (___) ___-____|235,td,___-__-__-__-__|228,tg,___-__-___-___|66,th,__-__-___-___|992,tj,___-__-___-____|690,tk,___-____|670,tl,___-___-____|993,tm,___-_-___-____|216,tn,___-__-___-___|676,to,___-_____|90,tr,__ (___) ___-____|1868,tt,_ (___) ___-____|688,tv,___-_____|886,tw,___-____-____|255,tz,___-__-___-____|380,ua,___ (__) ___-__-__|256,ug,___ (___) ___-___|44,gb,__-__-____-____|598,uy,___-_-___-__-__|998,uz,___-__-___-____|396698,va,__-_-___-_____|1784,vc,_ (___) ___-____|58,ve,__ (___) ___-____|1284,vg,_ (___) ___-____|1340,vi,_ (___) ___-____|84,vn,__-__-____-___|678,vu,___-_____|681,wf,___-__-____|685,ws,___-__-____|967,ye,___-_-___-___|27,za,__-__-___-____|260,zm,___ (__) ___-____|263,zw,___-_-______|1,us,_ (___) ___-____|".split('|').map(item => {
	      item = item.split(',');
	      return {
	        code: item[0],
	        id: item[1],
	        mask: item[2]
	      };
	    });
	  }
	  if (value.length > 0) {
	    let mask = babelHelpers.classPrivateFieldLooseBase(this, _findMask)[_findMask](value);
	    mask += ((mask.indexOf('-') >= 0 ? '-' : ' ') + '__').repeat(10);
	    for (let i = 0; i < value.length; i++) {
	      mask = mask.replace('_', value.slice(i, i + 1));
	    }
	    value = mask.replace(/\D+$/, '').replace(/_/g, '0');
	  }
	  if (hasPlus || value.length > 0) {
	    value = '+' + value;
	  }
	  return value;
	}
	function _findMask2(value) {
	  let r = babelHelpers.classPrivateFieldLooseBase(this, _phoneDb)[_phoneDb].filter(item => {
	    return value.indexOf(item.code) === 0;
	  }).sort((a, b) => {
	    return b.code.length - a.code.length;
	  })[0];
	  return r ? r.mask : '_ ___ __ __ __';
	}

	let _$6 = t => t,
	  _t$6;
	var _layout$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _bindEvents$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getNodeEmptyState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeEmptyState");
	class EmptyState extends Base {
	  constructor(options) {
	    super({
	      isHiddenOnStart: options.isHiddenOnStart
	    });
	    Object.defineProperty(this, _getNodeEmptyState, {
	      value: _getNodeEmptyState2
	    });
	    Object.defineProperty(this, _bindEvents$3, {
	      value: _bindEvents2$3
	    });
	    Object.defineProperty(this, _layout$5, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5] = {
	      content: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$3)[_bindEvents$3]();
	  }
	  getType() {
	    return 'empty-state';
	  }
	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeEmptyState)[_getNodeEmptyState]();
	  }
	}
	function _bindEvents2$3() {}
	function _getNodeEmptyState2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].content) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].content = main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="calendar-pub__slots-empty">
					<div class="calendar-pub__slots-empty_title">${0}</div>
					<div class="calendar-pub__slots-empty_info">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_EMPTY'), main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_EMPTY_INFO'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].content;
	}

	let _$7 = t => t,
	  _t$7;
	var _layout$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _bindEvents$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getNodeEmptyState$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeEmptyState");
	class AccessDenied extends Base {
	  constructor(options) {
	    super({
	      isHiddenOnStart: options.isHiddenOnStart
	    });
	    Object.defineProperty(this, _getNodeEmptyState$1, {
	      value: _getNodeEmptyState2$1
	    });
	    Object.defineProperty(this, _bindEvents$4, {
	      value: _bindEvents2$4
	    });
	    Object.defineProperty(this, _layout$6, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6] = {
	      content: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$4)[_bindEvents$4]();
	  }
	  getType() {
	    return 'access-denied';
	  }
	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeEmptyState$1)[_getNodeEmptyState$1]();
	  }
	}
	function _bindEvents2$4() {}
	function _getNodeEmptyState2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].content) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].content = main_core.Tag.render(_t$7 || (_t$7 = _$7`
				<div class="calendar-pub__slots-empty --icon-cross">
					<div class="calendar-pub__slots-empty_title">${0}</div>
					<div class="calendar-pub__slots-empty_info">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED'), main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED_INFO'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].content;
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$5,
	  _t3$4;
	var _layout$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _selected$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selected");
	var _value$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _bindEvents$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getNodeSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeSelect");
	var _getNodeValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeValue");
	var _getNodeWrapper$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWrapper");
	class SlotItem {
	  constructor(options) {
	    Object.defineProperty(this, _getNodeWrapper$3, {
	      value: _getNodeWrapper2$3
	    });
	    Object.defineProperty(this, _getNodeValue, {
	      value: _getNodeValue2
	    });
	    Object.defineProperty(this, _getNodeSelect, {
	      value: _getNodeSelect2
	    });
	    Object.defineProperty(this, _bindEvents$5, {
	      value: _bindEvents2$5
	    });
	    Object.defineProperty(this, _layout$7, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selected$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value$3, {
	      writable: true,
	      value: void 0
	    });
	    this.BUTTON_MAX_WIDTH = 123;
	    babelHelpers.classPrivateFieldLooseBase(this, _selected$1)[_selected$1] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7] = {
	      wrapper: null,
	      value: null,
	      select: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _value$3)[_value$3] = options.value;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$5)[_bindEvents$5]();
	  }
	  isSelected() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selected$1)[_selected$1];
	  }
	  select() {
	    babelHelpers.classPrivateFieldLooseBase(this, _selected$1)[_selected$1] = true;
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$3)[_getNodeWrapper$3](), '--selected');
	    main_core_events.EventEmitter.emit('selectSlot', this);
	  }
	  unSelect() {
	    babelHelpers.classPrivateFieldLooseBase(this, _selected$1)[_selected$1] = null;
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$3)[_getNodeWrapper$3](), '--selected');
	  }
	  showForm() {
	    main_core_events.EventEmitter.emit('confirmedSelectSlot', {
	      value: babelHelpers.classPrivateFieldLooseBase(this, _value$3)[_value$3]
	    });
	  }
	  render() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$3)[_getNodeWrapper$3]();
	  }
	}
	function _bindEvents2$5() {
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$3)[_getNodeWrapper$3](), 'click', this.select.bind(this));
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _getNodeSelect)[_getNodeSelect](), 'click', this.showForm.bind(this));
	}
	function _getNodeSelect2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select = main_core.Tag.render(_t$8 || (_t$8 = _$8`
				<div class="calendar-sharing__slot-select">${0}</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_SELECT_SLOT'));
	    document.body.append(babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select.offsetWidth > this.BUTTON_MAX_WIDTH) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select, '--compact');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select.remove();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].select;
	}
	function _getNodeValue2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].value) {
	    let value = calendar_util.Util.formatTimeInterval(babelHelpers.classPrivateFieldLooseBase(this, _value$3)[_value$3].from, babelHelpers.classPrivateFieldLooseBase(this, _value$3)[_value$3].to);
	    value = value.replace(/(am|pm)/g, `<span class="calendar-sharing-am-pm">$1</span>`);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].value = main_core.Tag.render(_t2$5 || (_t2$5 = _$8`
				<div class="calendar-sharing__slot-value">${0}</div>
			`), value);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].value;
	}
	function _getNodeWrapper2$3() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper = main_core.Tag.render(_t3$4 || (_t3$4 = _$8`
				<div class="calendar-sharing__slot-item">
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeValue)[_getNodeValue](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeSelect)[_getNodeSelect]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper;
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$6,
	  _t3$5,
	  _t4$4;
	var _layout$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _slots$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slots");
	var _selectedSlot = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedSlot");
	var _bindEvents$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _getNodeSlotList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeSlotList");
	var _getNodeTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeTitle");
	var _getNodeList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeList");
	var _getNodeListItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeListItems");
	class SlotList extends Base {
	  constructor(options) {
	    super({
	      isHiddenOnStart: options.isHiddenOnStart
	    });
	    Object.defineProperty(this, _getNodeListItems, {
	      value: _getNodeListItems2
	    });
	    Object.defineProperty(this, _getNodeList, {
	      value: _getNodeList2
	    });
	    Object.defineProperty(this, _getNodeTitle, {
	      value: _getNodeTitle2
	    });
	    Object.defineProperty(this, _getNodeSlotList, {
	      value: _getNodeSlotList2
	    });
	    Object.defineProperty(this, _bindEvents$6, {
	      value: _bindEvents2$6
	    });
	    Object.defineProperty(this, _layout$8, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _slots$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedSlot, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8] = {
	      title: null,
	      list: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _slots$1)[_slots$1] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$6)[_bindEvents$6]();
	  }
	  getType() {
	    return 'slot-list';
	  }
	  getContent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getNodeSlotList)[_getNodeSlotList]();
	  }
	  updateSlotsList() {
	    main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]());
	    const slotListNode = babelHelpers.classPrivateFieldLooseBase(this, _getNodeListItems)[_getNodeListItems]();
	    main_core.Dom.append(slotListNode, babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]());
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList](), '--shadow-top');
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList](), '--shadow-bottom');
	  }
	}
	function _bindEvents2$6() {
	  main_core_events.EventEmitter.subscribe('updateSlotsList', event => {
	    babelHelpers.classPrivateFieldLooseBase(this, _slots$1)[_slots$1] = event.data.slots;
	    this.updateSlotsList();
	  });
	  main_core_events.EventEmitter.subscribe('selectSlot', event => {
	    const newSelectedSlot = event.data;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _selectedSlot)[_selectedSlot] !== newSelectedSlot) {
	      var _babelHelpers$classPr;
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _selectedSlot)[_selectedSlot]) == null ? void 0 : _babelHelpers$classPr.unSelect();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedSlot)[_selectedSlot] = newSelectedSlot;
	  });
	}
	function _getNodeSlotList2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slotSelector) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slotSelector = main_core.Tag.render(_t$9 || (_t$9 = _$9`
				<div class="calendar-pub__slot-list-wrap">
					${0}
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeTitle)[_getNodeTitle](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slotSelector;
	}
	function _getNodeTitle2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].title = main_core.Tag.render(_t2$6 || (_t2$6 = _$9`
				<div class="calendar-sharing__calendar-bar">
					<div class="calendar-pub-ui__typography-m">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_SLOTS_FREE'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].title;
	}
	function _getNodeList2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slots) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slots = main_core.Tag.render(_t3$5 || (_t3$5 = _$9`
				<div class="calendar-sharing__calendar-block --overflow-hidden --shadow">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeListItems)[_getNodeListItems]());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].slots;
	}
	function _getNodeListItems2() {
	  const currentDaySlots = babelHelpers.classPrivateFieldLooseBase(this, _slots$1)[_slots$1].map(slot => new SlotItem({
	    value: {
	      from: slot.timeFrom,
	      to: slot.timeTo
	    }
	  }));
	  const result = main_core.Tag.render(_t4$4 || (_t4$4 = _$9`
			<div class="calendar-sharing__slots">
				${0}
			</div>
		`), currentDaySlots.map(slotItem => slotItem.render()));
	  result.addEventListener('scroll', () => {
	    if (result.scrollTop > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]().classList.add('--shadow-top');
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]().classList.remove('--shadow-top');
	    }
	    if (result.scrollHeight > result.offsetHeight && Math.ceil(result.offsetHeight + result.scrollTop) < result.scrollHeight) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]().classList.add('--shadow-bottom');
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]().classList.remove('--shadow-bottom');
	    }
	  });
	  setTimeout(() => {
	    if (result.scrollHeight > result.offsetHeight) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getNodeList)[_getNodeList]().classList.add('--shadow-bottom');
	    }
	  });
	  return result;
	}

	let _$a = t => t,
	  _t$a,
	  _t2$7,
	  _t3$6,
	  _t4$5,
	  _t5$4,
	  _t6$4,
	  _t7$3,
	  _t8$2,
	  _t9$1,
	  _t10$1,
	  _t11$1,
	  _t12$1,
	  _t13$1,
	  _t14$1;
	var _event = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("event");
	var _owner$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _currentTimezone = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentTimezone");
	var _icsFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icsFile");
	var _widgetDate$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgetDate");
	var _layout$9 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _value$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _state = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("state");
	var _inDeletedSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inDeletedSlider");
	var _isView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isView");
	var _showBackCalendarButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showBackCalendarButtons");
	var _getWidgetDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getWidgetDate");
	var _initEventData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initEventData");
	var _updateState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateState");
	var _getNodeIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeIcon");
	var _getNodeBackWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeBackWrapper");
	var _createIconByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createIconByState");
	var _getIconClassByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconClassByState");
	var _getEventNameNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEventNameNode");
	var _getStateTitleNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStateTitleNode");
	var _getStateTitleTextByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStateTitleTextByState");
	var _getAdditionalBlockNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAdditionalBlockNode");
	var _createAdditionalBlockContentByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAdditionalBlockContentByState");
	var _getBottomButtonNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBottomButtonNode");
	var _createBottomButtonByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createBottomButtonByState");
	var _onDownloadButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDownloadButtonClick");
	var _onReturnButtonClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onReturnButtonClick");
	var _getNodeWidgetDate$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWidgetDate");
	var _getEventName$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEventName");
	class Event extends Base {
	  constructor(options) {
	    var _options$timezone;
	    super({
	      isHiddenOnStart: options.isHiddenOnStart
	    });
	    Object.defineProperty(this, _getEventName$1, {
	      value: _getEventName2$1
	    });
	    Object.defineProperty(this, _getNodeWidgetDate$1, {
	      value: _getNodeWidgetDate2$1
	    });
	    Object.defineProperty(this, _onReturnButtonClick, {
	      value: _onReturnButtonClick2
	    });
	    Object.defineProperty(this, _onDownloadButtonClick, {
	      value: _onDownloadButtonClick2
	    });
	    Object.defineProperty(this, _createBottomButtonByState, {
	      value: _createBottomButtonByState2
	    });
	    Object.defineProperty(this, _getBottomButtonNode, {
	      value: _getBottomButtonNode2
	    });
	    Object.defineProperty(this, _createAdditionalBlockContentByState, {
	      value: _createAdditionalBlockContentByState2
	    });
	    Object.defineProperty(this, _getAdditionalBlockNode, {
	      value: _getAdditionalBlockNode2
	    });
	    Object.defineProperty(this, _getStateTitleTextByState, {
	      value: _getStateTitleTextByState2
	    });
	    Object.defineProperty(this, _getStateTitleNode, {
	      value: _getStateTitleNode2
	    });
	    Object.defineProperty(this, _getEventNameNode, {
	      value: _getEventNameNode2
	    });
	    Object.defineProperty(this, _getIconClassByState, {
	      value: _getIconClassByState2
	    });
	    Object.defineProperty(this, _createIconByState, {
	      value: _createIconByState2
	    });
	    Object.defineProperty(this, _getNodeBackWrapper, {
	      value: _getNodeBackWrapper2
	    });
	    Object.defineProperty(this, _getNodeIcon, {
	      value: _getNodeIcon2
	    });
	    Object.defineProperty(this, _updateState, {
	      value: _updateState2
	    });
	    Object.defineProperty(this, _initEventData, {
	      value: _initEventData2
	    });
	    Object.defineProperty(this, _getWidgetDate, {
	      value: _getWidgetDate2
	    });
	    Object.defineProperty(this, _event, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _owner$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentTimezone, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icsFile, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _widgetDate$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$9, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _state, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _inDeletedSlider, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isView, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showBackCalendarButtons, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = options.state;
	    babelHelpers.classPrivateFieldLooseBase(this, _isView)[_isView] = options.isView;
	    babelHelpers.classPrivateFieldLooseBase(this, _event)[_event] = options.event;
	    babelHelpers.classPrivateFieldLooseBase(this, _owner$3)[_owner$3] = options.owner;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentTimezone)[_currentTimezone] = (_options$timezone = options.timezone) != null ? _options$timezone : Intl.DateTimeFormat().resolvedOptions().timeZone;
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate$1)[_widgetDate$1] = babelHelpers.classPrivateFieldLooseBase(this, _getWidgetDate)[_getWidgetDate]();
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9] = {
	      back: null,
	      widgetDate: null,
	      eventName: null,
	      icon: null,
	      stateTitle: null,
	      additionalBlock: null,
	      bottomButton: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4] = {
	      from: null,
	      to: null,
	      timezone: null,
	      canceledTimestamp: null,
	      canceledUserName: null,
	      eventName: null,
	      canceledByManager: options.canceledByManager,
	      eventLinkHash: options.eventLinkHash,
	      eventId: options.eventId
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _icsFile)[_icsFile] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _inDeletedSlider)[_inDeletedSlider] = options.inDeletedSlider === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _showBackCalendarButtons)[_showBackCalendarButtons] = options.showBackCalendarButtons;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _event)[_event]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _initEventData)[_initEventData]();
	    }
	    if (options.action === 'cancel') {
	      setTimeout(this.showCancelEventPopup.bind(this), 0);
	    }
	    if (options.action === 'ics') {
	      this.downloadIcsFile();
	    }
	    if (options.action === 'videoconference') {
	      this.startVideoconference();
	    }
	  }
	  getType() {
	    return 'event';
	  }
	  updateValue(data) {
	    if (data.from) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].from = data.from;
	    }
	    if (data.to) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].to = data.to;
	    }
	    if (data.timezone) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].timezone = data.timezone;
	    }
	    if (data.eventLinkHash) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventLinkHash = data.eventLinkHash;
	    }
	    if (data.eventName) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventName = data.eventName;
	    }
	    if (data.state) {
	      babelHelpers.classPrivateFieldLooseBase(this, _updateState)[_updateState](data.state);
	    }
	    if (data.isView) {
	      babelHelpers.classPrivateFieldLooseBase(this, _isView)[_isView] = false;
	    }
	    if (data.eventId) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventId = data.eventId;
	    }
	    if (data.userName) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledUserName = data.userName;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledByManager === true) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledByManager = false;
	    }
	    this.updateFormLayout();
	  }
	  updateFormLayout() {
	    babelHelpers.classPrivateFieldLooseBase(this, _getWidgetDate)[_getWidgetDate]().updateValue(babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4]);
	    babelHelpers.classPrivateFieldLooseBase(this, _getEventNameNode)[_getEventNameNode]().innerText = babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventName;
	  }
	  getContent() {
	    return main_core.Tag.render(_t$a || (_t$a = _$a`
			<div class="calendar-sharing__form-result">
				${0}
				<div class="calendar-sharing__calendar-block --form --center">
					${0}
					${0}
					${0}
				</div>
				
				<div class="calendar-sharing__calendar-block --form --center">
					${0}
				</div>
				
				<div class="calendar-sharing__calendar-block --form --center">
					${0}
				</div>
				
				<div class="calendar-sharing__calendar-block --top-auto">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeBackWrapper)[_getNodeBackWrapper](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeIcon)[_getNodeIcon](), babelHelpers.classPrivateFieldLooseBase(this, _getEventNameNode)[_getEventNameNode](), babelHelpers.classPrivateFieldLooseBase(this, _getStateTitleNode)[_getStateTitleNode](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeWidgetDate$1)[_getNodeWidgetDate$1](), babelHelpers.classPrivateFieldLooseBase(this, _getAdditionalBlockNode)[_getAdditionalBlockNode](), babelHelpers.classPrivateFieldLooseBase(this, _getBottomButtonNode)[_getBottomButtonNode]());
	  }
	  getPopup() {
	    if (!this.popup) {
	      const popupContent = main_core.Tag.render(_t2$7 || (_t2$7 = _$a`
				<div>
					<div class="calendar-pub__cookies-title">${0}</div>
					<div class="calendar-pub__cookies-info">${0}</div>
					<div class="calendar-pub__cookies-buttons ${0}">
						<div onclick="${0}" class="calendar-pub-ui__btn --inline --m --light-border">
							<div class="calendar-pub-ui__btn-text">${0}</div>
						</div>
						<div onclick="${0}" class="calendar-pub-ui__btn --inline --m --secondary">
							<div class="calendar-pub-ui__btn-text">${0}</div>
						</div>
					</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED'), main_core.Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED_INFO'), main_core.Browser.isMobile() ? '--center' : '--flex-end', this.closeCancelEventPopup.bind(this), main_core.Loc.getMessage('CALENDAR_SHARING_POPUP_LEAVE'), this.handleDeleteButtonClick.bind(this), main_core.Loc.getMessage('CALENDAR_SHARING_POPUP_CANCEL'));
	      if (main_core.Browser.isMobile()) {
	        this.popup = new ui_bottomsheet.BottomSheet({
	          className: 'calendar-pub__state',
	          content: popupContent,
	          padding: '20px 25px'
	        });
	      } else {
	        this.popup = new main_popup.Popup({
	          className: 'calendar-pub__popup',
	          contentBackground: 'transparent',
	          width: 380,
	          animation: 'fading-slide',
	          content: popupContent,
	          overlay: true
	        });
	      }
	    }
	    return this.popup;
	  }
	  showCancelEventPopup() {
	    this.getPopup().show();
	  }
	  closeCancelEventPopup() {
	    this.getPopup().close();
	  }
	  async handleDeleteButtonClick() {
	    this.closeCancelEventPopup();
	    const isSuccess = await this.deleteEvent();
	    if (isSuccess) {
	      babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledTimestamp = new Date().getTime() / 1000;
	      babelHelpers.classPrivateFieldLooseBase(this, _updateState)[_updateState]('declined');
	      main_core_events.EventEmitter.emit('onDeleteEvent');
	    }
	  }
	  async deleteEvent() {
	    let response = null;
	    try {
	      response = await BX.ajax.runAction('calendar.api.sharingajax.deleteEvent', {
	        data: {
	          eventId: babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventId,
	          eventLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventLinkHash
	        }
	      });
	    } catch (e) {
	      response = e;
	    }
	    return response.errors.length === 0;
	  }
	  async startVideoconference() {
	    var _response$data;
	    const response = await BX.ajax.runAction('calendar.api.sharingajax.getConferenceLink', {
	      data: {
	        eventLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventLinkHash
	      }
	    });
	    const conferenceLink = ((_response$data = response.data) == null ? void 0 : _response$data.conferenceLink) || null;
	    if (conferenceLink) {
	      window.location.href = conferenceLink;
	    }
	  }
	  async downloadIcsFile() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _icsFile)[_icsFile]) {
	      const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
	        data: {
	          eventLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventLinkHash
	        }
	      });
	      this.icsFile = response.data;
	    }
	    calendar_util.Util.downloadIcsFile(this.icsFile, 'event');
	  }
	}
	function _getWidgetDate2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _widgetDate$1)[_widgetDate$1]) {
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate$1)[_widgetDate$1] = new WidgetDate();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _widgetDate$1)[_widgetDate$1];
	}
	function _initEventData2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].from = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].timestampFromUTC) * 1000, babelHelpers.classPrivateFieldLooseBase(this, _currentTimezone)[_currentTimezone]);
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].to = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].timestampToUTC) * 1000, babelHelpers.classPrivateFieldLooseBase(this, _currentTimezone)[_currentTimezone]);
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].timezone = babelHelpers.classPrivateFieldLooseBase(this, _currentTimezone)[_currentTimezone];
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventName = babelHelpers.classPrivateFieldLooseBase(this, _getEventName$1)[_getEventName$1]();
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledTimestamp = babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].canceledTimestamp;
	  babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledUserName = babelHelpers.classPrivateFieldLooseBase(this, _event)[_event].externalUserName;
	  this.updateFormLayout();
	}
	function _updateState2(state) {
	  babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = state;
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeIcon)[_getNodeIcon](), ['--accept', '--decline']);
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeIcon)[_getNodeIcon](), babelHelpers.classPrivateFieldLooseBase(this, _getIconClassByState)[_getIconClassByState](state));
	  babelHelpers.classPrivateFieldLooseBase(this, _getStateTitleNode)[_getStateTitleNode]().innerText = babelHelpers.classPrivateFieldLooseBase(this, _getStateTitleTextByState)[_getStateTitleTextByState](state);
	  let oldNode = babelHelpers.classPrivateFieldLooseBase(this, _getAdditionalBlockNode)[_getAdditionalBlockNode]();
	  let newNode = babelHelpers.classPrivateFieldLooseBase(this, _createAdditionalBlockContentByState)[_createAdditionalBlockContentByState](state);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].additionalBlock = newNode;
	  main_core.Dom.replace(oldNode, newNode);
	  oldNode = babelHelpers.classPrivateFieldLooseBase(this, _getBottomButtonNode)[_getBottomButtonNode]();
	  newNode = babelHelpers.classPrivateFieldLooseBase(this, _createBottomButtonByState)[_createBottomButtonByState](state);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].bottomButton = newNode;
	  main_core.Dom.replace(oldNode, newNode);
	}
	function _getNodeIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].icon) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].icon = babelHelpers.classPrivateFieldLooseBase(this, _createIconByState)[_createIconByState](babelHelpers.classPrivateFieldLooseBase(this, _state)[_state]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].icon;
	}
	function _getNodeBackWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].back) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _showBackCalendarButtons)[_showBackCalendarButtons] && main_core.Browser.isMobile()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].back = main_core.Tag.render(_t3$6 || (_t3$6 = _$a`
					<div class="calendar-sharing__calendar-bar">
						<div class="calendar-sharing__calendar-back" onclick="${0}"></div>
					</div>
				`), babelHelpers.classPrivateFieldLooseBase(this, _onReturnButtonClick)[_onReturnButtonClick].bind(this));
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].back = main_core.Tag.render(_t4$5 || (_t4$5 = _$a`<div class="calendar-sharing__calendar-bar --no-margin"></div>`));
	    }
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].back;
	}
	function _createIconByState2(state) {
	  let result = main_core.Tag.render(_t5$4 || (_t5$4 = _$a`
			<div class="calendar-sharing__form-result_icon"></div>
		`));
	  main_core.Dom.addClass(result, babelHelpers.classPrivateFieldLooseBase(this, _getIconClassByState)[_getIconClassByState](state));
	  return result;
	}
	function _getIconClassByState2(state) {
	  let result = '';
	  switch (state) {
	    case "created":
	      result = '--accept';
	      break;
	    case "not-created":
	      result = '--decline';
	      break;
	    case "declined":
	      result = '--decline';
	      break;
	  }
	  return result;
	}
	function _getEventNameNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].eventName) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].eventName = main_core.Tag.render(_t6$4 || (_t6$4 = _$a`
				<div class="calendar-pub-ui__typography-title --center --line-height-normal">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].eventName);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].eventName;
	}
	function _getStateTitleNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].stateTitle) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].stateTitle = main_core.Tag.render(_t7$3 || (_t7$3 = _$a`
				<div class="calendar-pub-ui__typography-s --center"></div>
			`));
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].stateTitle.innerText = babelHelpers.classPrivateFieldLooseBase(this, _getStateTitleTextByState)[_getStateTitleTextByState](babelHelpers.classPrivateFieldLooseBase(this, _state)[_state]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].stateTitle;
	}
	function _getStateTitleTextByState2(state) {
	  let result = '';
	  switch (state) {
	    case "created":
	      if (!babelHelpers.classPrivateFieldLooseBase(this, _isView)[_isView]) {
	        result = main_core.Loc.getMessage('CALENDAR_SHARING_MEETING_CREATED');
	      }
	      break;
	    case "not-created":
	      result = main_core.Loc.getMessage('CALENDAR_SHARING_MEETING_NOT_CREATED');
	      break;
	    case "declined":
	      result = main_core.Loc.getMessage('CALENDAR_SHARING_MEETING_CANCELED');
	      break;
	  }
	  return result;
	}
	function _getAdditionalBlockNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].additionalBlock) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].additionalBlock = babelHelpers.classPrivateFieldLooseBase(this, _createAdditionalBlockContentByState)[_createAdditionalBlockContentByState](babelHelpers.classPrivateFieldLooseBase(this, _state)[_state]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].additionalBlock;
	}
	function _createAdditionalBlockContentByState2(state) {
	  let result = '';
	  switch (state) {
	    case "created":
	      result = main_core.Tag.render(_t8$2 || (_t8$2 = _$a`
					<div onclick="${0}" class="calendar-pub__form-status --decline">
						<div class="calendar-pub__form-status_text">
							${0}
						</div>
					</div>
				`), this.showCancelEventPopup.bind(this), main_core.Loc.getMessage('CALENDAR_SHARING_DECLINE_MEETING'));
	      break;
	    case "not-created":
	      result = main_core.Tag.render(_t9$1 || (_t9$1 = _$a`
					<div></div>
				`));
	      break;
	    case "declined":
	      const date = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledTimestamp) * 1000, babelHelpers.classPrivateFieldLooseBase(this, _currentTimezone)[_currentTimezone]);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledByManager) {
	        babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledUserName = babelHelpers.classPrivateFieldLooseBase(this, _owner$3)[_owner$3].name + ' ' + babelHelpers.classPrivateFieldLooseBase(this, _owner$3)[_owner$3].lastName;
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledTimestamp && babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledUserName && date) {
	        result = main_core.Tag.render(_t10$1 || (_t10$1 = _$a`
						<div class="calendar-pub__form-status">
							<div class="calendar-pub__form-status_text">
								${0}: ${0}<br> ${0}
							</div>
						</div>
					`), main_core.Loc.getMessage('CALENDAR_SHARING_WHO_CANCELED'), babelHelpers.classPrivateFieldLooseBase(this, _value$4)[_value$4].canceledUserName, main_date.DateTimeFormat.format('j F ' + calendar_util.Util.getTimeFormatShort(), date.getTime() / 1000));
	      } else {
	        result = main_core.Tag.render(_t11$1 || (_t11$1 = _$a`
						<div></div>
					`));
	      }
	      break;
	  }
	  return result;
	}
	function _getBottomButtonNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].bottomButton) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].bottomButton = babelHelpers.classPrivateFieldLooseBase(this, _createBottomButtonByState)[_createBottomButtonByState](babelHelpers.classPrivateFieldLooseBase(this, _state)[_state]);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].bottomButton;
	}
	function _createBottomButtonByState2(state) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _inDeletedSlider)[_inDeletedSlider]) {
	    return '';
	  }
	  let result = '';
	  switch (state) {
	    case "created":
	      result = main_core.Tag.render(_t12$1 || (_t12$1 = _$a`
					<div onclick="${0}" class="calendar-pub-ui__btn --light-border --m">
						<div class="calendar-pub-ui__btn-text">${0}</div>
					</div>
				`), babelHelpers.classPrivateFieldLooseBase(this, _onDownloadButtonClick)[_onDownloadButtonClick].bind(this), main_core.Loc.getMessage('CALENDAR_SHARING_ADD_TO_CALENDAR'));
	      break;
	    case "not-created":
	    case "declined":
	      if (babelHelpers.classPrivateFieldLooseBase(this, _showBackCalendarButtons)[_showBackCalendarButtons]) {
	        result = main_core.Tag.render(_t13$1 || (_t13$1 = _$a`
						<div onclick="${0}" class="calendar-pub-ui__btn --light-border --m">
							<div class="calendar-pub-ui__btn-text">${0}</div>
						</div>
					`), babelHelpers.classPrivateFieldLooseBase(this, _onReturnButtonClick)[_onReturnButtonClick].bind(this), main_core.Loc.getMessage('CALENDAR_SHARING_RETURN_TO_SLOT_LIST'));
	      } else {
	        result = main_core.Tag.render(_t14$1 || (_t14$1 = _$a`<div></div>`));
	      }
	      break;
	  }
	  return result;
	}
	async function _onDownloadButtonClick2() {
	  main_core.Dom.addClass(this, '--wait');
	  await this.downloadIcsFile();
	  main_core.Dom.removeClass(this, '--wait');
	}
	function _onReturnButtonClick2() {
	  main_core_events.EventEmitter.emit('onCreateAnotherEventButtonClick');
	}
	function _getNodeWidgetDate2$1() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].widgetDate) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].widgetDate = babelHelpers.classPrivateFieldLooseBase(this, _widgetDate$1)[_widgetDate$1].render();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$9)[_layout$9].widgetDate;
	}
	function _getEventName2$1() {
	  return main_core.Loc.getMessage('CALENDAR_SHARING_EVENT_NAME', {
	    '#OWNER_NAME#': babelHelpers.classPrivateFieldLooseBase(this, _owner$3)[_owner$3].name + ' ' + babelHelpers.classPrivateFieldLooseBase(this, _owner$3)[_owner$3].lastName
	  });
	}

	let _$b = t => t,
	  _t$b;
	var _layout$a = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _components = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("components");
	var _selectedTimezoneId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedTimezoneId");
	var _owner$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _link$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("link");
	var _sharingUser$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sharingUser");
	var _isFromCrm$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isFromCrm");
	var _hasContactData$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasContactData");
	var _calendarSettings$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarSettings");
	var _eventLinkHash = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventLinkHash");
	var _event$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("event");
	var _action = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("action");
	var _showBackCalendarButtons$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showBackCalendarButtons");
	var _bindEvents$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	class SlotSelector {
	  constructor(options) {
	    Object.defineProperty(this, _bindEvents$7, {
	      value: _bindEvents2$7
	    });
	    Object.defineProperty(this, _layout$a, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _components, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _selectedTimezoneId$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _owner$4, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _link$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _sharingUser$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _isFromCrm$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _hasContactData$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendarSettings$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _eventLinkHash, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _event$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _action, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _showBackCalendarButtons$1, {
	      writable: true,
	      value: void 0
	    });
	    this.BLOCK_NAME_FORM = 'form';
	    this.BLOCK_NAME_SLOT_LIST = 'slot-list';
	    this.BLOCK_NAME_EMPTY_STATE = 'empty-state';
	    this.BLOCK_NAME_ACCESS_DENIED = 'access-denied';
	    this.BLOCK_NAME_EVENT = 'event';
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId$1)[_selectedTimezoneId$1] = options.selectedTimezoneId;
	    babelHelpers.classPrivateFieldLooseBase(this, _owner$4)[_owner$4] = options.owner;
	    babelHelpers.classPrivateFieldLooseBase(this, _link$2)[_link$2] = options.link;
	    babelHelpers.classPrivateFieldLooseBase(this, _sharingUser$1)[_sharingUser$1] = options.sharingUser;
	    babelHelpers.classPrivateFieldLooseBase(this, _eventLinkHash)[_eventLinkHash] = options.eventLinkHash;
	    babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1] = options.event;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$a)[_layout$a] = {
	      wrapper: null,
	      empty: null,
	      title: null,
	      slots: null,
	      slotSelector: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _components)[_components] = {
	      form: null,
	      slotList: null,
	      emptyState: null,
	      event: null,
	      accessDenied: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _isFromCrm$1)[_isFromCrm$1] = babelHelpers.classPrivateFieldLooseBase(this, _link$2)[_link$2].type === 'crm_deal';
	    babelHelpers.classPrivateFieldLooseBase(this, _hasContactData$1)[_hasContactData$1] = options.hasContactData;
	    babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1] = options.calendarSettings;
	    babelHelpers.classPrivateFieldLooseBase(this, _showBackCalendarButtons$1)[_showBackCalendarButtons$1] = options.showBackCalendarButtons;
	    babelHelpers.classPrivateFieldLooseBase(this, _action)[_action] = options.action;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$7)[_bindEvents$7]();
	    // EventEmitter.subscribe('selectorStateChange', this.showForm.bind(this));
	    // EventEmitter.subscribe('hideForm', this.hideForm.bind(this));
	  }

	  openForm() {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form) == null ? void 0 : _babelHelpers$classPr.clearInputErrors();
	    this.openBlock(this.BLOCK_NAME_FORM);
	  }
	  openSlotList() {
	    this.openBlock(this.BLOCK_NAME_SLOT_LIST);
	  }
	  openEmptyState() {
	    this.openBlock(this.BLOCK_NAME_EMPTY_STATE);
	  }
	  openAccessDenied() {
	    this.openBlock(this.BLOCK_NAME_ACCESS_DENIED);
	  }
	  openEvent() {
	    this.openBlock(this.BLOCK_NAME_EVENT);
	  }
	  openBlock(blockName) {
	    main_core_events.EventEmitter.emit('selectorTypeChange', blockName);
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form) {
	      babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form = new Form({
	        isHiddenOnStart: true,
	        owner: babelHelpers.classPrivateFieldLooseBase(this, _owner$4)[_owner$4],
	        link: babelHelpers.classPrivateFieldLooseBase(this, _link$2)[_link$2],
	        sharingUser: babelHelpers.classPrivateFieldLooseBase(this, _sharingUser$1)[_sharingUser$1],
	        isFromCrm: babelHelpers.classPrivateFieldLooseBase(this, _isFromCrm$1)[_isFromCrm$1],
	        hasContactData: babelHelpers.classPrivateFieldLooseBase(this, _hasContactData$1)[_hasContactData$1],
	        isPhoneFeatureEnabled: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1].phoneFeatureEnabled,
	        isMailFeatureEnabled: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1].mailFeatureEnabled
	      });
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].emptyState) {
	      babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].emptyState = new EmptyState({
	        isHiddenOnStart: true
	      });
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].accessDenied) {
	      babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].accessDenied = new AccessDenied({
	        isHiddenOnStart: true
	      });
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].slotList) {
	      babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].slotList = new SlotList({
	        isHiddenOnStart: false
	      });
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].event) {
	      var _babelHelpers$classPr2, _babelHelpers$classPr3, _babelHelpers$classPr4;
	      let state = 'created';
	      if (babelHelpers.classPrivateFieldLooseBase(this, _link$2)[_link$2].active === false || ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1]) == null ? void 0 : _babelHelpers$classPr2.meetingStatus) === 'N' || ((_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1]) == null ? void 0 : _babelHelpers$classPr3.deleted) === 'Y') {
	        state = 'declined';
	      }
	      let canceledByManager = false;
	      if (((_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1]) == null ? void 0 : _babelHelpers$classPr4.meetingStatus) === 'N') {
	        canceledByManager = true;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].event = new Event({
	        isHiddenOnStart: false,
	        owner: babelHelpers.classPrivateFieldLooseBase(this, _owner$4)[_owner$4],
	        event: babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1],
	        eventLinkHash: babelHelpers.classPrivateFieldLooseBase(this, _eventLinkHash)[_eventLinkHash],
	        state: state,
	        eventId: babelHelpers.classPrivateFieldLooseBase(this, _event$1)[_event$1].id,
	        isView: main_core.Type.isString(babelHelpers.classPrivateFieldLooseBase(this, _eventLinkHash)[_eventLinkHash]),
	        canceledByManager: canceledByManager,
	        showBackCalendarButtons: babelHelpers.classPrivateFieldLooseBase(this, _showBackCalendarButtons$1)[_showBackCalendarButtons$1],
	        action: babelHelpers.classPrivateFieldLooseBase(this, _action)[_action]
	      });
	    }
	    return main_core.Tag.render(_t$b || (_t$b = _$b`
			<div class="calendar-pub__slots">
				${0}
				${0}
				${0}
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].slotList.render(), babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form.render(), babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].emptyState.render(), babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].event.render(), babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].accessDenied.render());
	  }
	}
	function _bindEvents2$7() {
	  main_core_events.EventEmitter.subscribe('confirmedSelectSlot', event => {
	    const data = event.data;
	    const value = data.value;
	    babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form.updateFormValue({
	      from: value.from,
	      to: value.to,
	      timezone: babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId$1)[_selectedTimezoneId$1]
	    });
	    this.openForm();
	  });
	  main_core_events.EventEmitter.subscribe('switchSlots', event => {
	    var _event$data$slots;
	    const slots = (_event$data$slots = event.data.slots) != null ? _event$data$slots : [];
	    if (slots.length > 0) {
	      main_core_events.EventEmitter.emit('updateSlotsList', event);
	      this.openSlotList();
	    } else {
	      this.openEmptyState();
	    }
	  });
	  main_core_events.EventEmitter.subscribe('updateTimezone', event => {
	    const data = event.data;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedTimezoneId$1)[_selectedTimezoneId$1] = data.timezone;
	  });
	  main_core_events.EventEmitter.subscribe('onSaveEvent', event => {
	    const eventData = event.data;
	    babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].form.cleanDescription();
	    babelHelpers.classPrivateFieldLooseBase(this, _components)[_components].event.updateValue(eventData);
	    this.openEvent();
	  });
	}

	let _$c = t => t,
	  _t$c,
	  _t2$8,
	  _t3$7,
	  _t4$6;
	var _layout$b = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _owner$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("owner");
	var _welcomePage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("welcomePage");
	var _calendar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendar");
	var _slotsBlock = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slotsBlock");
	var _bindEvents$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _renderFreeSlots = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderFreeSlots");
	var _renderSlotsSelector = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSlotsSelector");
	var _getNodeWrapper$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWrapper");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class PublicV2 {
	  constructor(_options) {
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _getNodeWrapper$4, {
	      value: _getNodeWrapper2$4
	    });
	    Object.defineProperty(this, _renderSlotsSelector, {
	      value: _renderSlotsSelector2
	    });
	    Object.defineProperty(this, _renderFreeSlots, {
	      value: _renderFreeSlots2
	    });
	    Object.defineProperty(this, _bindEvents$8, {
	      value: _bindEvents2$8
	    });
	    Object.defineProperty(this, _layout$b, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _owner$5, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _welcomePage, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendar, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _slotsBlock, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _owner$5)[_owner$5] = _options.owner ? _options.owner : null;
	    this.target = main_core.Type.isDomNode(_options.target) ? _options.target : null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b] = {
	      wrapper: null,
	      animate: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$8)[_bindEvents$8]();
	    this.showPageWelcome(_options);
	    if (_options.link.type === 'event') {
	      if (_options.parentLink && _options.parentLink.active === true) {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderFreeSlots)[_renderFreeSlots](_options);
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].handleWelcomePageButtonClick();
	        babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].openEvent();
	      } else if (_options.event) {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderSlotsSelector)[_renderSlotsSelector](_options);
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].handleWelcomePageButtonClick();
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].hideButton();
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].setAccessDenied();
	        babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].openEvent();
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderSlotsSelector)[_renderSlotsSelector](_options);
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].handleWelcomePageButtonClick();
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].hideButton();
	        babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].openAccessDenied();
	      }
	    } else {
	      if (_options.link.active === true) {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderFreeSlots)[_renderFreeSlots](_options);
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderSlotsSelector)[_renderSlotsSelector](_options);
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].handleWelcomePageButtonClick();
	        babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].hideButton();
	        babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].openAccessDenied();
	      }
	    }

	    // this.showFreeSlots();
	  }

	  showPageWelcome(options) {
	    if (!options.owner) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage] = new Welcome({
	      owner: options.owner,
	      link: options.link,
	      currentLang: options.currentLang
	    });
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _welcomePage)[_welcomePage].render(), babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4]());
	  }
	  showFreeSlots() {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4](), '--hide');
	  }
	  hideFreeSlots() {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4](), '--hide');
	  }
	}
	function _bindEvents2$8() {
	  main_core_events.EventEmitter.subscribe('showSlotSelector', this.showFreeSlots.bind(this));
	  main_core_events.EventEmitter.subscribe('hideSlotSelector', this.hideFreeSlots.bind(this));
	}
	function _renderFreeSlots2(options) {
	  babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar] = new Calendar({
	    owner: options.owner,
	    accessibility: options.userAccessibility,
	    timezoneList: options.timezoneList,
	    calendarSettings: options.calendarSettings
	  });
	  let eventLinkHash = null;
	  if (options.link.type === 'event') {
	    eventLinkHash = options.link.hash;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock] = new SlotSelector({
	    selectedTimezoneId: babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar].getSelectedTimezoneId(),
	    owner: babelHelpers.classPrivateFieldLooseBase(this, _owner$5)[_owner$5],
	    link: options.parentLink || options.link,
	    sharingUser: options.sharingUser,
	    hasContactData: options.hasContactData,
	    calendarSettings: options.calendarSettings,
	    event: options.event,
	    showBackCalendarButtons: true,
	    eventLinkHash: eventLinkHash,
	    action: options.action
	  });
	  const firstNodeWrapper = main_core.Tag.render(_t$c || (_t$c = _$c`
			<div class="calendar-pub__block --plus">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar].render());
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].animate = main_core.Tag.render(_t2$8 || (_t2$8 = _$c`
			<div class="calendar-pub__block-animate">
				${0}
				<div class="calendar-pub__block">
					${0}
				</div>
			</div>
		`), firstNodeWrapper, babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].render());
	  main_core_events.EventEmitter.subscribe('selectorTypeChange', ev => {
	    if (ev.data === 'form' || ev.data === 'event') {
	      main_core.Dom.addClass(firstNodeWrapper, '--hidden');
	    } else {
	      main_core.Dom.removeClass(firstNodeWrapper, '--hidden');
	    }
	  });
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].animate, babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4]());
	  if (options.link.type !== 'event') {
	    babelHelpers.classPrivateFieldLooseBase(this, _calendar)[_calendar].selectFirstAvailableDay();
	  }
	}
	function _renderSlotsSelector2(options) {
	  let eventLinkHash = null;
	  if (options.link.type === 'event') {
	    eventLinkHash = options.link.hash;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock] = new SlotSelector({
	    selectedTimezoneId: null,
	    owner: babelHelpers.classPrivateFieldLooseBase(this, _owner$5)[_owner$5],
	    link: options.link,
	    sharingUser: options.sharingUser,
	    hasContactData: options.hasContactData,
	    calendarSettings: options.calendarSettings,
	    event: options.event,
	    showBackCalendarButtons: false,
	    action: options.action,
	    eventLinkHash: eventLinkHash
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].animate = main_core.Tag.render(_t3$7 || (_t3$7 = _$c`
			<div class="calendar-pub__block-animate">
				<div class="calendar-pub__block">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _slotsBlock)[_slotsBlock].render());
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].animate, babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4]());
	}
	function _getNodeWrapper2$4() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].wrapper) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].wrapper = main_core.Tag.render(_t4$6 || (_t4$6 = _$c`
				<div class="calendar-pub__wrapper calendar-pub__state --hide"></div>
			`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$b)[_layout$b].wrapper;
	}
	function _render2() {
	  if (!this.target) {
	    console.warn('BX.Calendar.Sharing: "target" is not defined');
	    return;
	  }
	  if (this.target.parentNode) {
	    main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getNodeWrapper$4)[_getNodeWrapper$4](), this.target.parentNode);
	    main_core.Dom.remove(this.target);
	  }
	}
	function _init2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	}

	exports.PublicV2 = PublicV2;
	exports.Event = Event;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX,BX.Calendar,BX,BX.Main,BX.Main,BX.UI,BX.Event,BX));
//# sourceMappingURL=public-v2.bundle.js.map
