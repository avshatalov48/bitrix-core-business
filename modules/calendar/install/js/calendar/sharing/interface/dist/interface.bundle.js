/* eslint-disable */
this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_date,calendar_sharing_analytics,ui_entitySelector,ui_avatar,ui_iconSet_api_core,ui_dialogs_messagebox,ui_buttons,ui_iconSet_actions,main_qrcode,ui_designTokens,ui_switcher,spotlight,ui_tour,ui_cnt,ui_infoHelper,calendar_util,main_core,main_popup,main_loader,main_core_events) {
	'use strict';

	var _calendarSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarSettings");
	var _getSlotSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSlotSize");
	class RangeModel extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _getSlotSize, {
	      value: _getSlotSize2
	    });
	    Object.defineProperty(this, _calendarSettings, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Calendar.Sharing.Range');
	    const {
	      id,
	      range,
	      rule,
	      calendarSettings,
	      isNew
	    } = params;
	    this.id = id;
	    this.rule = rule;
	    this.from = range.from;
	    this.to = range.to;
	    this.new = isNew;
	    this.deletable = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings] = calendarSettings;
	    this.setWeekDays(range.weekdays);
	  }
	  toArray() {
	    return {
	      from: this.getFrom(),
	      to: this.to,
	      weekdays: this.getWeekDays()
	    };
	  }
	  getRule() {
	    return this.rule;
	  }
	  getId() {
	    return this.id;
	  }
	  getFromFormatted() {
	    return this.formatMinutes(this.getFrom());
	  }
	  getFrom() {
	    return this.from;
	  }
	  setFrom(value) {
	    this.from = parseInt(value, 10);
	    if (this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]() > this.to) {
	      this.to = this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]();
	    }
	    this.updated();
	  }
	  getToFormatted() {
	    return this.formatMinutes(this.getTo());
	  }
	  getTo() {
	    return this.to;
	  }
	  setTo(value) {
	    this.to = value;
	    this.updated();
	  }
	  updateSlotSize() {
	    const maxFrom = 24 * 60 - babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]();
	    if (this.from > maxFrom) {
	      this.from = maxFrom;
	      this.to = this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]();
	    } else if (this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]() > this.to) {
	      this.to = this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]();
	    }
	    this.updated();
	  }
	  addWeekday(weekday) {
	    if (this.weekdays.includes(weekday)) {
	      return;
	    }
	    this.setWeekDays([...this.weekdays, weekday]);
	  }
	  removeWeekday(weekday) {
	    this.setWeekDays(this.weekdays.filter(w => w !== weekday));
	  }
	  getWeekDays() {
	    return this.weekdays;
	  }
	  setWeekDays(weekdays) {
	    this.weekdays = this.sortWeekdays(weekdays);
	    this.updated();
	  }
	  getWeekdaysTitle(forceLong = false) {
	    if ([...this.weekdays].sort().join(',') === [1, 2, 3, 4, 5].sort().join(',')) {
	      return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_WORKDAYS_MSGVER_1');
	    }
	    return this.formatWeekdays(forceLong);
	  }
	  formatWeekdays(forceLong) {
	    const weekdaysLoc = calendar_util.Util.getWeekdaysLoc(forceLong || this.weekdays.length === 1);
	    const weekdays = this.getWeekDays();
	    if (weekdays.length === 0) {
	      return '';
	    }
	    return weekdays.map(w => weekdaysLoc[w]).reduce((a, b) => `${a}, ${b}`);
	  }
	  sortWeekdays(weekdays) {
	    return weekdays.map(w => w < babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekStart ? w + 10 : w).sort((a, b) => a - b).map(w => w % 10);
	  }
	  getAvailableTimeFrom() {
	    const timeStamps = [];
	    const maxFrom = 24 * 60 - babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]();
	    for (let hour = 0; hour <= 24; hour++) {
	      if (hour * 60 <= maxFrom) {
	        timeStamps.push({
	          value: hour * 60,
	          name: calendar_util.Util.formatTime(hour, 0)
	        });
	      }
	      if (hour !== 24 && hour * 60 + 30 <= maxFrom) {
	        timeStamps.push({
	          value: hour * 60 + 30,
	          name: calendar_util.Util.formatTime(hour, 30)
	        });
	      }
	    }
	    return timeStamps;
	  }
	  getAvailableTimeTo() {
	    const timeStamps = [];
	    for (let hour = 0; hour <= 24; hour++) {
	      if (hour * 60 >= this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]()) {
	        timeStamps.push({
	          value: hour * 60,
	          name: calendar_util.Util.formatTime(hour, 0)
	        });
	      }
	      if (hour !== 24 && hour * 60 + 30 >= this.from + babelHelpers.classPrivateFieldLooseBase(this, _getSlotSize)[_getSlotSize]()) {
	        timeStamps.push({
	          value: hour * 60 + 30,
	          name: calendar_util.Util.formatTime(hour, 30)
	        });
	      }
	    }
	    return timeStamps;
	  }
	  isDeletable() {
	    return this.deletable;
	  }
	  setDeletable(deletable) {
	    this.deletable = deletable;
	  }
	  isNew() {
	    return this.new;
	  }
	  setNew(isNew) {
	    this.new = isNew;
	  }
	  getWeekStart() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings)[_calendarSettings].weekStart;
	  }
	  formatMinutes(minutes) {
	    const date = new Date(calendar_util.Util.parseDate('01.01.2000').getTime() + minutes * 60 * 1000);
	    return calendar_util.Util.formatTime(date);
	  }
	  updated() {
	    this.emit('updated');
	    this.getRule().updated();
	  }
	}
	function _getSlotSize2() {
	  return this.rule.getSlotSize();
	}

	var _calendarSettings$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarSettings");
	var _updateRanges = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRanges");
	class RuleModel extends main_core_events.EventEmitter {
	  constructor(params) {
	    super();
	    Object.defineProperty(this, _updateRanges, {
	      value: _updateRanges2
	    });
	    this.AVAILABLE_INTERVALS = [30, 45, 60, 90, 120, 180];
	    this.MAX_RANGES = 5;
	    this.DEFAULT_SLOT_SIZE = 60;
	    Object.defineProperty(this, _calendarSettings$1, {
	      writable: true,
	      value: void 0
	    });
	    this.setEventNamespace('Calendar.Sharing.Rule');
	    const {
	      rule,
	      calendarSettings
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1] = calendarSettings;
	    this.ranges = [];
	    for (const range of rule.ranges) {
	      this.addRange(range, false);
	    }
	    this.setSlotSize(rule.slotSize);
	    this.sortRanges();
	  }
	  toArray() {
	    return {
	      slotSize: this.getSlotSize(),
	      ranges: this.getSortedRanges().map(range => range.toArray())
	    };
	  }
	  getDefaultRule() {
	    return new RuleModel({
	      rule: {
	        slotSize: this.DEFAULT_SLOT_SIZE,
	        ranges: [{
	          from: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1].workTimeStart,
	          to: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1].workTimeEnd,
	          weekdays: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1].workDays
	        }]
	      },
	      calendarSettings: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1]
	    });
	  }
	  getAvailableIntervals() {
	    return this.AVAILABLE_INTERVALS;
	  }
	  getFormattedSlotSize() {
	    return calendar_util.Util.formatDuration(this.getSlotSize());
	  }
	  getSlotSize() {
	    return this.slotSize;
	  }
	  setSlotSize(value) {
	    const slotSize = parseInt(value, 10);
	    this.slotSize = this.getAvailableIntervals().includes(slotSize) ? slotSize : this.DEFAULT_SLOT_SIZE;
	    for (const range of this.getRanges()) {
	      range.updateSlotSize();
	    }
	  }
	  getRanges() {
	    return this.ranges;
	  }
	  sortRanges() {
	    this.ranges = this.getSortedRanges();
	  }
	  getSortedRanges() {
	    return [...this.ranges].sort((a, b) => this.compareRanges(a, b));
	  }
	  compareRanges(firstRange, secondRange) {
	    const firstWeekdaysWeight = this.getWeekdaysWeight(firstRange.getWeekDays());
	    const secondWeekdaysWeight = this.getWeekdaysWeight(secondRange.getWeekDays());
	    if (firstWeekdaysWeight !== secondWeekdaysWeight) {
	      return firstWeekdaysWeight - secondWeekdaysWeight;
	    }
	    if (firstRange.getFrom() !== secondRange.getFrom()) {
	      return firstRange.getFrom() - secondRange.getFrom();
	    }
	    return firstRange.getTo() - secondRange.getTo();
	  }
	  getWeekdaysWeight(weekdays) {
	    return weekdays.reduce((accumulator, w, index) => {
	      return accumulator + w * 10 ** (10 - index);
	    }, 0);
	  }
	  addRange(range, isNew = true) {
	    var _this$internalRangeId;
	    if (!this.canAddRange()) {
	      return;
	    }
	    (_this$internalRangeId = this.internalRangeId) != null ? _this$internalRangeId : this.internalRangeId = 1;
	    this.ranges.push(new RangeModel({
	      id: this.internalRangeId++,
	      range: range != null ? range : this.getDefaultRule().getRanges()[0],
	      calendarSettings: babelHelpers.classPrivateFieldLooseBase(this, _calendarSettings$1)[_calendarSettings$1],
	      isNew,
	      rule: this
	    }));
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRanges)[_updateRanges]();
	    this.updated();
	  }
	  removeRange(rangeToRemove) {
	    if (!this.canRemoveRange()) {
	      return false;
	    }
	    this.ranges = this.ranges.filter(range => {
	      return range.getId() !== rangeToRemove.getId();
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _updateRanges)[_updateRanges]();
	    this.rangeDeleted();
	    return true;
	  }
	  canAddRange() {
	    return this.ranges.length < this.MAX_RANGES;
	  }
	  canRemoveRange() {
	    return this.ranges.length > 1;
	  }
	  updated() {
	    this.emit('updated');
	  }
	  rangeDeleted() {
	    this.emit('rangeDeleted');
	  }
	}
	function _updateRanges2() {
	  for (const range of this.ranges.slice(0, -1)) {
	    range.setDeletable(true);
	  }
	  this.ranges.slice(-1)[0].setDeletable(this.ranges.length === 5);
	}

	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _rule = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rule");
	var _memberIds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("memberIds");
	var _createRuleModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createRuleModel");
	var _updateSortByFrequentUse = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSortByFrequentUse");
	class SettingsModel {
	  constructor(params) {
	    Object.defineProperty(this, _updateSortByFrequentUse, {
	      value: _updateSortByFrequentUse2
	    });
	    Object.defineProperty(this, _createRuleModel, {
	      value: _createRuleModel2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _rule, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _memberIds, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    const {
	      rule: _rule2,
	      calendarSettings: _calendarSettings
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _rule)[_rule] = babelHelpers.classPrivateFieldLooseBase(this, _createRuleModel)[_createRuleModel](_rule2, _calendarSettings);
	  }
	  getMinutesFromTime(time) {
	    const dateString = new Date().toDateString();
	    const date = new Date(`${dateString} ${`${time}`.replace('.', ':')}:00`);
	    const shortTimeFormat = main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
	    const parsedTime = calendar_util.Util.parseTime(main_date.DateTimeFormat.format(shortTimeFormat, date / 1000));
	    return parsedTime.h * 60 + parsedTime.m;
	  }
	  getWorkingDays(weekHolidays) {
	    const weekHolidaysInt = new Set(weekHolidays.map(day => calendar_util.Util.getIndByWeekDay(day)));
	    return [0, 1, 2, 3, 4, 5, 6].filter(day => !weekHolidaysInt.has(day));
	  }
	  isDefaultRule() {
	    return !this.isDifferentFrom(this.getRule().getDefaultRule());
	  }
	  isDifferentFrom(anotherRule) {
	    return this.getChanges(anotherRule, this.getRule()).length > 0;
	  }
	  getChanges(rule) {
	    const currentRule = this.getRule().toArray();
	    const anotherRule = (rule != null ? rule : this.getRule().getDefaultRule()).toArray();
	    const sizeChanged = currentRule.slotSize !== anotherRule.slotSize;
	    const daysChanged = JSON.stringify(currentRule.ranges) !== JSON.stringify(anotherRule.ranges);
	    const changes = [];
	    if (daysChanged) {
	      changes.push(calendar_sharing_analytics.Analytics.ruleChanges.custom_days);
	    }
	    if (sizeChanged) {
	      changes.push(calendar_sharing_analytics.Analytics.ruleChanges.custom_length);
	    }
	    return changes;
	  }
	  sortRanges() {
	    this.getRule().sortRanges();
	  }
	  getRule() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rule)[_rule];
	  }
	  getUserInfo() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].userInfo;
	  }
	  getContext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].context;
	  }
	  getLinkHash() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].linkHash;
	  }
	  getSharingUrl() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sharingUrl;
	  }
	  isCollapsed() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].collapsed;
	  }
	  sortJointLinksByFrequentUse() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortJointLinksByFrequentUse;
	  }
	  getCalendarContext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].calendarContext;
	  }
	  changeSortJointLinksByFrequentUse() {
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortJointLinksByFrequentUse = !babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortJointLinksByFrequentUse;
	    babelHelpers.classPrivateFieldLooseBase(this, _updateSortByFrequentUse)[_updateSortByFrequentUse]();
	  }
	  setMemberIds(memberIds) {
	    babelHelpers.classPrivateFieldLooseBase(this, _memberIds)[_memberIds] = memberIds;
	  }
	  getMemberIds() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _memberIds)[_memberIds];
	  }
	  async saveJointLink() {
	    var _babelHelpers$classPr, _babelHelpers$classPr2;
	    const action = ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].calendarContext) == null ? void 0 : _babelHelpers$classPr.sharingObjectType) === 'group' ? 'calendar.api.sharinggroupajax.generateJointSharingLink' : 'calendar.api.sharingajax.generateUserJointSharingLink';
	    const response = await BX.ajax.runAction(action, {
	      data: {
	        memberIds: this.getMemberIds(),
	        groupId: (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].calendarContext) == null ? void 0 : _babelHelpers$classPr2.sharingObjectId
	      }
	    });
	    return response.data;
	  }
	  save() {
	    if (!this.isDifferentFrom(babelHelpers.classPrivateFieldLooseBase(this, _createRuleModel)[_createRuleModel](babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].rule, babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].calendarSettings))) {
	      return null;
	    }
	    const changes = this.getChanges();
	    calendar_sharing_analytics.Analytics.sendRuleUpdated(this.getContext(), changes);
	    const newRule = this.getRule().toArray();
	    return new Promise((resolve, reject) => {
	      BX.ajax.runAction('calendar.api.sharingajax.saveLinkRule', {
	        data: {
	          linkHash: this.getLinkHash(),
	          ruleArray: newRule
	        }
	      }).then(() => {
	        main_core_events.EventEmitter.emit('CalendarSharing:RuleUpdated');
	        babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].rule = newRule;
	        resolve();
	      }, error => {
	        // eslint-disable-next-line no-console
	        console.error(error);
	        reject();
	      });
	    });
	  }
	  increaseFrequentUse() {
	    void BX.ajax.runAction('calendar.api.sharingajax.increaseFrequentUse', {
	      data: {
	        hash: this.getLinkHash()
	      }
	    });
	  }
	  updateCollapsed(isCollapsed) {
	    void BX.ajax.runAction('calendar.api.sharingajax.updateSharingSettingsCollapsed', {
	      data: {
	        collapsed: isCollapsed ? 'Y' : 'N'
	      }
	    });
	  }
	}
	function _createRuleModel2(rule, calendarSettings) {
	  const {
	    weekStart,
	    weekHolidays,
	    workTimeStart,
	    workTimeEnd
	  } = calendarSettings;
	  return new RuleModel({
	    rule,
	    calendarSettings: {
	      weekStart: calendar_util.Util.getIndByWeekDay(weekStart),
	      workTimeStart: this.getMinutesFromTime(workTimeStart),
	      workTimeEnd: this.getMinutesFromTime(workTimeEnd),
	      workDays: this.getWorkingDays(weekHolidays)
	    }
	  });
	}
	function _updateSortByFrequentUse2() {
	  BX.ajax.runAction('calendar.api.sharingajax.setSortJointLinksByFrequentUse', {
	    data: {
	      sortByFrequentUse: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].sortJointLinksByFrequentUse ? 'Y' : 'N'
	    }
	  });
	}

	let _ = t => t,
	  _t;
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
	    this.wrap = main_core.Tag.render(_t || (_t = _`
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

	let _$1 = t => t,
	  _t$1,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	var _params$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _model = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("model");
	var _bindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _unbindEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindEvents");
	var _onRangeUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRangeUpdated");
	var _animate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("animate");
	var _getButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButton");
	var _onDeleteButtonClickHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDeleteButtonClickHandler");
	var _onAddButtonClickHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onAddButtonClickHandler");
	var _add = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("add");
	var _remove = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("remove");
	var _renderWeekdaysSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWeekdaysSelect");
	var _getTextNodeWidth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTextNodeWidth");
	var _createWeekdaysPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWeekdaysPopup");
	var _createWeekday = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createWeekday");
	var _onWeekdayMouseDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWeekdayMouseDown");
	var _renderTimeFromSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTimeFromSelect");
	var _renderTimeToSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTimeToSelect");
	var _renderTimeSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTimeSelect");
	var _onTimeSelectClickHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTimeSelectClickHandler");
	var _showTimeMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showTimeMenu");
	var _onWeekdaysSelectClickHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onWeekdaysSelectClickHandler");
	class Range {
	  constructor(params) {
	    Object.defineProperty(this, _onWeekdaysSelectClickHandler, {
	      value: _onWeekdaysSelectClickHandler2
	    });
	    Object.defineProperty(this, _showTimeMenu, {
	      value: _showTimeMenu2
	    });
	    Object.defineProperty(this, _onTimeSelectClickHandler, {
	      value: _onTimeSelectClickHandler2
	    });
	    Object.defineProperty(this, _renderTimeSelect, {
	      value: _renderTimeSelect2
	    });
	    Object.defineProperty(this, _renderTimeToSelect, {
	      value: _renderTimeToSelect2
	    });
	    Object.defineProperty(this, _renderTimeFromSelect, {
	      value: _renderTimeFromSelect2
	    });
	    Object.defineProperty(this, _onWeekdayMouseDown, {
	      value: _onWeekdayMouseDown2
	    });
	    Object.defineProperty(this, _createWeekday, {
	      value: _createWeekday2
	    });
	    Object.defineProperty(this, _createWeekdaysPopup, {
	      value: _createWeekdaysPopup2
	    });
	    Object.defineProperty(this, _getTextNodeWidth, {
	      value: _getTextNodeWidth2
	    });
	    Object.defineProperty(this, _renderWeekdaysSelect, {
	      value: _renderWeekdaysSelect2
	    });
	    Object.defineProperty(this, _remove, {
	      value: _remove2
	    });
	    Object.defineProperty(this, _add, {
	      value: _add2
	    });
	    Object.defineProperty(this, _onAddButtonClickHandler, {
	      value: _onAddButtonClickHandler2
	    });
	    Object.defineProperty(this, _onDeleteButtonClickHandler, {
	      value: _onDeleteButtonClickHandler2
	    });
	    Object.defineProperty(this, _getButton, {
	      value: _getButton2
	    });
	    Object.defineProperty(this, _animate, {
	      value: _animate2
	    });
	    Object.defineProperty(this, _onRangeUpdated, {
	      value: _onRangeUpdated2
	    });
	    Object.defineProperty(this, _unbindEvents, {
	      value: _unbindEvents2
	    });
	    Object.defineProperty(this, _bindEvents, {
	      value: _bindEvents2
	    });
	    Object.defineProperty(this, _model, {
	      get: _get_model,
	      set: void 0
	    });
	    Object.defineProperty(this, _params$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {};
	    this.showReadOnlyPopup = main_core.Type.isFunction(params.showReadOnlyPopup) ? params.showReadOnlyPopup : () => {};
	    this.onRangeUpdated = babelHelpers.classPrivateFieldLooseBase(this, _onRangeUpdated)[_onRangeUpdated].bind(this);
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents)[_bindEvents]();
	  }
	  hasShownPopups() {
	    const weekdaysPopupShown = this.weekdaysMenu.isShown();
	    const startPopupShown = main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].fromTimeSelect, '--active');
	    const endPopupShown = main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].toTimeSelect, '--active');
	    return weekdaysPopupShown || startPopupShown || endPopupShown;
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap.remove();
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindEvents)[_unbindEvents]();
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap = main_core.Tag.render(_t$1 || (_t$1 = _$1`
			<div class="calendar-sharing__settings-range">
				${0}
				<div class="calendar-sharing__settings-time-interval">
					${0}
					<div class="calendar-sharing__settings-dash"></div>
					${0}
				</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderWeekdaysSelect)[_renderWeekdaysSelect](), babelHelpers.classPrivateFieldLooseBase(this, _renderTimeFromSelect)[_renderTimeFromSelect](), babelHelpers.classPrivateFieldLooseBase(this, _renderTimeToSelect)[_renderTimeToSelect](), this.renderButton());
	    if (babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].isNew()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _animate)[_animate]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap;
	  }
	  renderButton() {
	    var _babelHelpers$classPr;
	    const button = babelHelpers.classPrivateFieldLooseBase(this, _getButton)[_getButton]();
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button) == null ? void 0 : _babelHelpers$classPr.replaceWith(button);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button = button;
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button;
	  }
	  updateWeekdaysTitle() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.title = babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].formatWeekdays(false);
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.innerText = babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekdaysTitle(true);
	    const weekdaysSelectWidth = babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.offsetWidth - 32;
	    const weekdaysTextWidth = babelHelpers.classPrivateFieldLooseBase(this, _getTextNodeWidth)[_getTextNodeWidth](babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.firstChild);
	    const weekdaysWidthIsOverflowing = weekdaysSelectWidth < weekdaysTextWidth;
	    if (weekdaysWidthIsOverflowing) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.innerText = babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekdaysTitle(false);
	    }
	  }
	  formatAmPmSpan(time) {
	    return time.toLowerCase().replace(/(am|pm)/g, '<span class="calendar-sharing__settings-time-am-pm">$1</span>');
	  }
	}
	function _get_model() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].model;
	}
	function _bindEvents2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].subscribe('updated', this.onRangeUpdated);
	}
	function _unbindEvents2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].unsubscribe('updated', this.onRangeUpdated);
	}
	function _onRangeUpdated2() {
	  this.updateWeekdaysTitle();
	}
	function _animate2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap, '--animate-show');
	  setTimeout(() => {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap, '--animate-show');
	    babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].setNew(false);
	  }, 300);
	}
	function _getButton2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].isDeletable()) {
	    return main_core.Tag.render(_t2 || (_t2 = _$1`
				<div
					class="calendar-sharing__settings-delete"
					onclick="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _onDeleteButtonClickHandler)[_onDeleteButtonClickHandler].bind(this));
	  }
	  return main_core.Tag.render(_t3 || (_t3 = _$1`
			<div
				class="calendar-sharing__settings-add"
				onclick="${0}"
			></div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _onAddButtonClickHandler)[_onAddButtonClickHandler].bind(this));
	}
	function _onDeleteButtonClickHandler2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].readOnly) {
	    this.showReadOnlyPopup(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button);
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _remove)[_remove]();
	  }
	}
	function _onAddButtonClickHandler2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].readOnly) {
	    this.showReadOnlyPopup(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].button);
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _add)[_add]();
	  }
	}
	function _add2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getRule().addRange();
	}
	function _remove2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getRule().removeRange(babelHelpers.classPrivateFieldLooseBase(this, _model)[_model])) {
	    return;
	  }
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].wrap, '--animate-remove');
	  setTimeout(() => this.destroy(), 300);
	}
	function _renderWeekdaysSelect2() {
	  const weekdaysLoc = calendar_util.Util.getWeekdaysLoc().map((loc, index) => {
	    return {
	      loc,
	      index,
	      active: babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekDays().includes(index)
	    };
	  });
	  weekdaysLoc.push(...weekdaysLoc.splice(0, babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekStart()));
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect = main_core.Tag.render(_t4 || (_t4 = _$1`
			<div
				class="calendar-sharing__settings-weekdays calendar-sharing__settings-select calendar-sharing__settings-select-arrow"
				title="${0}"
			>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].formatWeekdays(), babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekdaysTitle());
	  const observer = new IntersectionObserver(() => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect.offsetWidth > 0) {
	      this.updateWeekdaysTitle();
	    }
	  });
	  observer.observe(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect);
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onWeekdaysSelectClickHandler)[_onWeekdaysSelectClickHandler].bind(this));
	  this.weekdays = weekdaysLoc.map(weekdayLoc => babelHelpers.classPrivateFieldLooseBase(this, _createWeekday)[_createWeekday](weekdayLoc));
	  const weekdaysPopupId = `calendar-sharing-settings-weekdays-${babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].model.id}`;
	  this.weekdaysMenu = main_popup.PopupManager.getPopupById(weekdaysPopupId);
	  if (!this.weekdaysMenu) {
	    this.weekdaysMenu = babelHelpers.classPrivateFieldLooseBase(this, _createWeekdaysPopup)[_createWeekdaysPopup](weekdaysPopupId);
	    this.weekdaysMenu.canBeClosed = true;
	  }
	  this.weekdaysMenu.setBindElement(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect);
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect;
	}
	function _getTextNodeWidth2(textNode) {
	  const spanNode = BX.Tag.render(_t5 || (_t5 = _$1`<span style="position: absolute;">${0}</span>`), textNode.cloneNode());
	  textNode.replaceWith(spanNode);
	  const textWidth = spanNode.offsetWidth;
	  spanNode.replaceWith(textNode);
	  return textWidth;
	}
	function _createWeekdaysPopup2(id) {
	  return new main_popup.Popup({
	    id,
	    content: main_core.Tag.render(_t6 || (_t6 = _$1`
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
	      onPopupShow: () => main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect, '--active'),
	      onPopupClose: () => main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect, '--active')
	    }
	  });
	}
	function _createWeekday2(weekdayLoc) {
	  return new Weekday({
	    name: weekdayLoc.loc,
	    index: weekdayLoc.index,
	    active: weekdayLoc.active,
	    onSelected: () => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].addWeekday(weekdayLoc.index),
	    onDiscarded: () => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].removeWeekday(weekdayLoc.index),
	    canBeDiscarded: () => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getWeekDays().length > 1,
	    onMouseDown: babelHelpers.classPrivateFieldLooseBase(this, _onWeekdayMouseDown)[_onWeekdayMouseDown].bind(this)
	  });
	}
	function _onWeekdayMouseDown2(event, currentWeekday) {
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
	function _renderTimeFromSelect2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].fromTimeSelect = babelHelpers.classPrivateFieldLooseBase(this, _renderTimeSelect)[_renderTimeSelect](babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getFromFormatted(), {
	    getTimeStamps: () => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getAvailableTimeFrom(),
	    isSelected: minutes => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getFrom() === minutes,
	    onItemSelected: minutes => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].setFrom(minutes)
	  }, 'calendar-sharing-settings-range-from');
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].fromTimeSelect;
	}
	function _renderTimeToSelect2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].toTimeSelect = babelHelpers.classPrivateFieldLooseBase(this, _renderTimeSelect)[_renderTimeSelect](babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getToFormatted(), {
	    getTimeStamps: () => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getAvailableTimeTo(),
	    isSelected: minutes => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].getTo() === minutes,
	    onItemSelected: minutes => babelHelpers.classPrivateFieldLooseBase(this, _model)[_model].setTo(minutes)
	  }, 'calendar-sharing-settings-range-to');
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].toTimeSelect;
	}
	function _renderTimeSelect2(time, callbacks, dataId) {
	  const timeSelect = main_core.Tag.render(_t7 || (_t7 = _$1`
			<div
				class="calendar-sharing__settings-select calendar-sharing__settings-time calendar-sharing__settings-select-arrow"
				data-id="${0}"
			>
				${0}
			</div>
		`), dataId, this.formatAmPmSpan(time));
	  main_core.Event.bind(timeSelect, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _onTimeSelectClickHandler)[_onTimeSelectClickHandler](timeSelect, callbacks));
	  return timeSelect;
	}
	function _onTimeSelectClickHandler2(timeSelect, callbacks) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].readOnly) {
	    this.showReadOnlyPopup(timeSelect);
	  } else if (!main_core.Dom.hasClass(timeSelect, '--active')) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showTimeMenu)[_showTimeMenu](timeSelect, callbacks);
	  }
	}
	function _showTimeMenu2(timeSelect, callbacks) {
	  let timeMenu;
	  const items = callbacks.getTimeStamps().map(timeStamp => {
	    return {
	      html: main_core.Tag.render(_t8 || (_t8 = _$1`
					<div class="calendar-sharing__am-pm-container">${0}</div>
				`), timeStamp.name),
	      className: callbacks.isSelected(timeStamp.value) ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
	      onclick: () => {
	        timeSelect.innerHTML = timeStamp.name;
	        callbacks.onItemSelected(timeStamp.value);
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
	function _onWeekdaysSelectClickHandler2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params$1)[_params$1].readOnly) {
	    this.showReadOnlyPopup(babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].weekdaysSelect);
	  } else {
	    this.weekdaysMenu.show();
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
	  _t9;
	var _params$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _model$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("model");
	var _rule$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rule");
	var _bindEvents$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _onRuleUpdated = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRuleUpdated");
	var _onRangeDeleted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRangeDeleted");
	var _renderHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHeader");
	var _renderSubtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSubtitle");
	var _updateSubtitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateSubtitle");
	var _getSubtitleText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSubtitleText");
	var _renderExpandRuleButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderExpandRuleButton");
	var _renderRule = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRule");
	var _toggleExpand = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleExpand");
	var _updateRuleHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateRuleHeight");
	var _renderRanges = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderRanges");
	var _createRange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createRange");
	var _renderSettingsSlotSizeSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettingsSlotSizeSelect");
	var _calculateRuleHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calculateRuleHeight");
	var _removeRuleHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeRuleHeight");
	var _slotSizeSelectClickHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("slotSizeSelectClickHandler");
	var _showReadOnlyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showReadOnlyPopup");
	var _getReadOnlyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getReadOnlyPopup");
	var _closeReadOnlyPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeReadOnlyPopup");
	class Settings {
	  constructor(params) {
	    Object.defineProperty(this, _closeReadOnlyPopup, {
	      value: _closeReadOnlyPopup2
	    });
	    Object.defineProperty(this, _getReadOnlyPopup, {
	      value: _getReadOnlyPopup2
	    });
	    Object.defineProperty(this, _showReadOnlyPopup, {
	      value: _showReadOnlyPopup2
	    });
	    Object.defineProperty(this, _slotSizeSelectClickHandler, {
	      value: _slotSizeSelectClickHandler2
	    });
	    Object.defineProperty(this, _removeRuleHeight, {
	      value: _removeRuleHeight2
	    });
	    Object.defineProperty(this, _calculateRuleHeight, {
	      value: _calculateRuleHeight2
	    });
	    Object.defineProperty(this, _renderSettingsSlotSizeSelect, {
	      value: _renderSettingsSlotSizeSelect2
	    });
	    Object.defineProperty(this, _createRange, {
	      value: _createRange2
	    });
	    Object.defineProperty(this, _renderRanges, {
	      value: _renderRanges2
	    });
	    Object.defineProperty(this, _updateRuleHeight, {
	      value: _updateRuleHeight2
	    });
	    Object.defineProperty(this, _toggleExpand, {
	      value: _toggleExpand2
	    });
	    Object.defineProperty(this, _renderRule, {
	      value: _renderRule2
	    });
	    Object.defineProperty(this, _renderExpandRuleButton, {
	      value: _renderExpandRuleButton2
	    });
	    Object.defineProperty(this, _getSubtitleText, {
	      value: _getSubtitleText2
	    });
	    Object.defineProperty(this, _updateSubtitle, {
	      value: _updateSubtitle2
	    });
	    Object.defineProperty(this, _renderSubtitle, {
	      value: _renderSubtitle2
	    });
	    Object.defineProperty(this, _renderHeader, {
	      value: _renderHeader2
	    });
	    Object.defineProperty(this, _onRangeDeleted, {
	      value: _onRangeDeleted2
	    });
	    Object.defineProperty(this, _onRuleUpdated, {
	      value: _onRuleUpdated2
	    });
	    Object.defineProperty(this, _bindEvents$1, {
	      value: _bindEvents2$1
	    });
	    Object.defineProperty(this, _rule$1, {
	      get: _get_rule,
	      set: void 0
	    });
	    Object.defineProperty(this, _model$1, {
	      get: _get_model$1,
	      set: void 0
	    });
	    Object.defineProperty(this, _params$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1] = {};
	    this.readOnly = params.readOnly;
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$1)[_bindEvents$1]();
	  }
	  hasShownPopups() {
	    var _babelHelpers$classPr, _this$readOnlyPopup;
	    const rangesWithPopup = (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges.filter(range => range.hasShownPopups())) != null ? _babelHelpers$classPr : [];
	    const rangePopupShown = rangesWithPopup.length > 0;
	    const slotSizePopupShown = main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect, '--active');
	    const readOnlyPopupShown = (_this$readOnlyPopup = this.readOnlyPopup) == null ? void 0 : _this$readOnlyPopup.isShown();
	    return rangePopupShown || slotSizePopupShown || readOnlyPopupShown;
	  }
	  render() {
	    const readOnlyClass = this.readOnly ? '--read-only' : '';
	    const expandedClass = babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].isCollapsed() ? '--hide' : '';
	    const contextClass = `--${babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].getContext()}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrap = main_core.Tag.render(_t$2 || (_t$2 = _$2`
			<div class="calendar-sharing__settings ${0} ${0} ${0}">
				${0}
				${0}
			</div>
		`), readOnlyClass, expandedClass, contextClass, babelHelpers.classPrivateFieldLooseBase(this, _renderHeader)[_renderHeader](), babelHelpers.classPrivateFieldLooseBase(this, _renderRule)[_renderRule]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrap;
	  }
	}
	function _get_model$1() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _params$2)[_params$2].model;
	}
	function _get_rule() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].getRule();
	}
	function _bindEvents2$1() {
	  babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].subscribe('updated', babelHelpers.classPrivateFieldLooseBase(this, _onRuleUpdated)[_onRuleUpdated].bind(this));
	  babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].subscribe('rangeDeleted', babelHelpers.classPrivateFieldLooseBase(this, _onRangeDeleted)[_onRangeDeleted].bind(this));
	}
	function _onRuleUpdated2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateSubtitle)[_updateSubtitle]();
	  babelHelpers.classPrivateFieldLooseBase(this, _removeRuleHeight)[_removeRuleHeight]();
	  babelHelpers.classPrivateFieldLooseBase(this, _renderRanges)[_renderRanges]();
	}
	function _onRangeDeleted2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateSubtitle)[_updateSubtitle]();
	  babelHelpers.classPrivateFieldLooseBase(this, _removeRuleHeight)[_removeRuleHeight]();
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges.forEach(range => range.renderButton());
	}
	function _renderHeader2() {
	  return main_core.Tag.render(_t2$1 || (_t2$1 = _$2`
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
		`), main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_TITLE_V2'), babelHelpers.classPrivateFieldLooseBase(this, _renderSubtitle)[_renderSubtitle](), babelHelpers.classPrivateFieldLooseBase(this, _renderExpandRuleButton)[_renderExpandRuleButton]());
	}
	function _renderSubtitle2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].subtitle = main_core.Tag.render(_t3$1 || (_t3$1 = _$2`
			<div class="calendar-sharing__settings-subtitle">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getSubtitleText)[_getSubtitleText]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].subtitle;
	}
	function _updateSubtitle2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].subtitle) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].subtitle.innerText = babelHelpers.classPrivateFieldLooseBase(this, _getSubtitleText)[_getSubtitleText]();
	}
	function _getSubtitleText2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].isDefaultRule()) {
	    return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_DEFAULT');
	  }
	  return main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SUBTITLE_PERSONAL');
	}
	function _renderExpandRuleButton2() {
	  if (this.readOnly) {
	    return '';
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleArrow = main_core.Tag.render(_t4$1 || (_t4$1 = _$2`
			<div class="calendar-sharing__settings-select-arrow ${0}"></div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].isCollapsed() ? '' : '--active');
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleButton = main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
			<div class="calendar-sharing__settings-expand">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleArrow);
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleButton, 'click', babelHelpers.classPrivateFieldLooseBase(this, _toggleExpand)[_toggleExpand].bind(this));
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleButton;
	}
	function _renderRule2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rule = main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
			<div class="calendar-sharing__settings-rule">
				${0}
				<div class="calendar-sharing__settings-slotSize">
					<span class="calendar-sharing__settings-slotSize-title">${0}</span>
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderRanges)[_renderRanges](), main_core.Loc.getMessage('CALENDAR_SHARING_SETTINGS_SLOT_SIZE_V2'), babelHelpers.classPrivateFieldLooseBase(this, _renderSettingsSlotSizeSelect)[_renderSettingsSlotSizeSelect]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rule;
	}
	function _toggleExpand2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _updateRuleHeight)[_updateRuleHeight]();
	  setTimeout(() => {
	    main_core.Dom.toggleClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrap, '--hide');
	    main_core.Dom.toggleClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].expandRuleArrow, '--active');
	    babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].updateCollapsed(main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].wrap, '--hide'));
	  }, 0);
	}
	function _updateRuleHeight2() {
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rule, 'height', `${babelHelpers.classPrivateFieldLooseBase(this, _calculateRuleHeight)[_calculateRuleHeight]()}px`);
	}
	function _renderRanges2() {
	  var _babelHelpers$classPr2, _babelHelpers$classPr3;
	  (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges) == null ? void 0 : _babelHelpers$classPr2.forEach(range => range.destroy());
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges = babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].getRanges().map(range => babelHelpers.classPrivateFieldLooseBase(this, _createRange)[_createRange](range));
	  const rangesContainer = main_core.Tag.render(_t7$1 || (_t7$1 = _$2`
			<div class="calendar-sharing__settings-range-list">
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges.map(range => range.render()));
	  (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rangesContainer) == null ? void 0 : _babelHelpers$classPr3.replaceWith(rangesContainer);
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rangesContainer = rangesContainer;
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].ranges.forEach(range => range.updateWeekdaysTitle());
	  return rangesContainer;
	}
	function _createRange2(range) {
	  return new Range({
	    model: range,
	    readOnly: this.readOnly,
	    showReadOnlyPopup: babelHelpers.classPrivateFieldLooseBase(this, _showReadOnlyPopup)[_showReadOnlyPopup].bind(this)
	  });
	}
	function _renderSettingsSlotSizeSelect2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeText = main_core.Tag.render(_t8$1 || (_t8$1 = _$2`
			<span class="calendar-sharing__settings-select-link">
				${0}
			</span>
		`), babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].getFormattedSlotSize());
	  babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect = main_core.Tag.render(_t9 || (_t9 = _$2`
			<span class="calendar-sharing__settings-select-arrow --small-arrow">
				${0}
			</span>
		`), babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeText);
	  main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect, 'click', babelHelpers.classPrivateFieldLooseBase(this, _slotSizeSelectClickHandler)[_slotSizeSelectClickHandler].bind(this));
	  this.slotSizeMenu = main_popup.MenuManager.create({
	    id: `calendar-sharing-settings-slotSize${Date.now()}`,
	    bindElement: babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect,
	    items: babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].getRule().getAvailableIntervals().map(minutes => {
	      return {
	        text: calendar_util.Util.formatDuration(minutes),
	        onclick: () => {
	          babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].setSlotSize(minutes);
	          babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeText.innerHTML = babelHelpers.classPrivateFieldLooseBase(this, _rule$1)[_rule$1].getFormattedSlotSize();
	          this.slotSizeMenu.close();
	        }
	      };
	    }),
	    closeByEsc: true,
	    events: {
	      onShow: () => main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect, '--active'),
	      onClose: () => main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect, '--active')
	    }
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect;
	}
	function _calculateRuleHeight2() {
	  const topMarginHeight = 10;
	  const bottomMarginHeight = 2;
	  const marginsHeight = topMarginHeight + bottomMarginHeight;
	  const slotSizeHeight = 15;
	  const rangeHeight = 45;
	  return rangeHeight * babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].getRule().getRanges().length + (marginsHeight + slotSizeHeight);
	}
	function _removeRuleHeight2() {
	  main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].rule, 'height', null);
	}
	function _slotSizeSelectClickHandler2() {
	  if (this.readOnly) {
	    babelHelpers.classPrivateFieldLooseBase(this, _showReadOnlyPopup)[_showReadOnlyPopup](babelHelpers.classPrivateFieldLooseBase(this, _layout$1)[_layout$1].slotSizeSelect);
	  } else {
	    this.slotSizeMenu.show();
	  }
	}
	function _showReadOnlyPopup2(pivotNode) {
	  babelHelpers.classPrivateFieldLooseBase(this, _closeReadOnlyPopup)[_closeReadOnlyPopup]();
	  babelHelpers.classPrivateFieldLooseBase(this, _getReadOnlyPopup)[_getReadOnlyPopup](pivotNode).show();
	}
	function _getReadOnlyPopup2(pivotNode) {
	  var _babelHelpers$classPr4;
	  const readonlyHint = ((_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _model$1)[_model$1].getCalendarContext()) == null ? void 0 : _babelHelpers$classPr4.sharingObjectType) === 'group' ? 'CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT_GROUP' : 'CALENDAR_SHARING_SETTINGS_READ_ONLY_HINT';
	  this.readOnlyPopup = new main_popup.Popup({
	    bindElement: pivotNode,
	    className: 'calendar-sharing__settings-read-only-hint',
	    content: main_core.Loc.getMessage(readonlyHint),
	    angle: {
	      offset: 0
	    },
	    width: 300,
	    offsetLeft: pivotNode.offsetWidth / 2,
	    darkMode: true,
	    autoHide: true
	  });
	  main_core.Event.bind(this.readOnlyPopup.popupContainer, 'click', () => babelHelpers.classPrivateFieldLooseBase(this, _closeReadOnlyPopup)[_closeReadOnlyPopup]());
	  clearTimeout(this.closePopupTimeout);
	  this.closePopupTimeout = setTimeout(() => babelHelpers.classPrivateFieldLooseBase(this, _closeReadOnlyPopup)[_closeReadOnlyPopup](), 3000);
	  return this.readOnlyPopup;
	}
	function _closeReadOnlyPopup2() {
	  var _this$readOnlyPopup2;
	  (_this$readOnlyPopup2 = this.readOnlyPopup) == null ? void 0 : _this$readOnlyPopup2.destroy();
	}

	let _$3 = t => t,
	  _t$3;
	var _bindElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");
	var _layout$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	class HintInfo {
	  constructor(props) {
	    Object.defineProperty(this, _bindElement, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _bindElement)[_bindElement] = props.bindElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper = main_core.Tag.render(_t$3 || (_t$3 = _$3`
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$2)[_layout$2].wrapper;
	  }
	  show() {
	    var _babelHelpers$classPr;
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr.show();
	  }
	  close() {
	    var _babelHelpers$classPr2;
	    (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup]) == null ? void 0 : _babelHelpers$classPr2.close();
	  }
	}

	let _$4 = t => t,
	  _t$4,
	  _t2$2,
	  _t3$2,
	  _t4$2,
	  _t5$2,
	  _t6$2,
	  _t7$2;
	var _layout$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _userSelectorDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userSelectorDialog");
	var _selectedEntityList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedEntityList");
	var _selectedEntityNodeList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedEntityNodeList");
	var _defaultUserEntity = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultUserEntity");
	var _onMembersAdded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onMembersAdded");
	var _model$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("model");
	var _renderTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTitle");
	var _getTitleText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitleText");
	var _renderCollabAvatar = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCollabAvatar");
	class UserSelector {
	  constructor(props = {}) {
	    Object.defineProperty(this, _renderCollabAvatar, {
	      value: _renderCollabAvatar2
	    });
	    Object.defineProperty(this, _getTitleText, {
	      value: _getTitleText2
	    });
	    Object.defineProperty(this, _renderTitle, {
	      value: _renderTitle2
	    });
	    Object.defineProperty(this, _layout$3, {
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
	    Object.defineProperty(this, _onMembersAdded, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _model$2, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2] = props.model;
	    babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity] = babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].getUserInfo();
	    babelHelpers.classPrivateFieldLooseBase(this, _onMembersAdded)[_onMembersAdded] = props.onMembersAdded;
	    this.openEntitySelector = this.openEntitySelector.bind(this);
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper) {
	      const contextClass = `--${babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].getContext()}`;
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper = main_core.Tag.render(_t$4 || (_t$4 = _$4`
				<div class="calendar-sharing__user-selector-main ${0}">
					${0}
					${0}
				</div>
			`), contextClass, babelHelpers.classPrivateFieldLooseBase(this, _renderTitle)[_renderTitle](), this.renderUserSelectorWrapper());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].wrapper;
	  }
	  renderUserSelectorWrapper() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelectorWrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelectorWrapper = main_core.Tag.render(_t2$2 || (_t2$2 = _$4`
				<div class="calendar-sharing__user-selector-wrapper">
					${0}
					<div class="calendar-sharing__user-selector-add">
						<div class="ui-icon-set --plus-20"></div>
					</div>
				</div>
			`), this.renderUserSelector());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelectorWrapper, 'click', this.openEntitySelector);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelectorWrapper;
	  }
	  renderUserSelector() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector) {
	      const entityNode = this.getDefaultEntityNode();
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector = main_core.Tag.render(_t3$2 || (_t3$2 = _$4`
				<div class="calendar-sharing__user-selector-container" data-id="calendar-sharing-members">
					${0}
				</div>
			`), entityNode);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector;
	  }
	  getDefaultEntityNode() {
	    const entityNode = this.renderUserEntity(babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity]);
	    const key = this.getEntityKey(babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity].id);
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList][key] = babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity];
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key] = entityNode;
	    babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].setMemberIds(this.getSelectedUserIdList());
	    return entityNode;
	  }
	  renderUserEntity(entity) {
	    if (entity.isCollabUser) {
	      return main_core.Tag.render(_t4$2 || (_t4$2 = _$4`
				<div class="calendar-sharing__user-selector-entity-container">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _renderCollabAvatar)[_renderCollabAvatar](entity));
	    }
	    if (this.hasAvatar(entity.avatar)) {
	      return main_core.Tag.render(_t5$2 || (_t5$2 = _$4`
				<div class="calendar-sharing__user-selector-entity-container">
					<img class="calendar-sharing__user-selector-entity" title="${0}" src="${0}" alt="">
				</div>
			`), main_core.Text.encode(entity.name), entity.avatar);
	    }
	    return main_core.Tag.render(_t6$2 || (_t6$2 = _$4`
			<div class="ui-icon ui-icon-common-user calendar-sharing__user-selector-entity" title="${0}"><i></i></div>
		`), main_core.Text.encode(entity == null ? void 0 : entity.name));
	  }
	  hasAvatar(avatar) {
	    return avatar && avatar !== '/bitrix/images/1.gif';
	  }
	  openEntitySelector() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector) {
	      return;
	    }
	    const preselectedItem = ['user', babelHelpers.classPrivateFieldLooseBase(this, _defaultUserEntity)[_defaultUserEntity].id];
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog]) {
	      var _babelHelpers$classPr;
	      babelHelpers.classPrivateFieldLooseBase(this, _userSelectorDialog)[_userSelectorDialog] = new ui_entitySelector.Dialog({
	        width: 340,
	        targetNode: babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector,
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
	            intranetUsersOnly: !(((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].getCalendarContext()) == null ? void 0 : _babelHelpers$classPr.sharingObjectType) === 'group'),
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
	    const name = item.customData.get('name') ? `${item.customData.get('name')} ${(_item$customData$get = item.customData.get('lastName')) != null ? _item$customData$get : ''}`.trim() : String(item.customData.get('login'));
	    const entity = {
	      id: item.id,
	      avatar: item.avatar,
	      name,
	      isCollabUser: item.entityType === 'collaber'
	    };
	    const entityNode = this.renderUserEntity(entity);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector) {
	      main_core.Dom.append(entityNode, babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector);
	    }
	    const key = this.getEntityKey(entity.id);
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList][key] = entity;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList][key] = entityNode;
	    babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].setMemberIds(this.getSelectedUserIdList());
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
	    babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].setMemberIds(this.getSelectedUserIdList());
	  }
	  clearSelectedUsers() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector);
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityList)[_selectedEntityList] = {};
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedEntityNodeList)[_selectedEntityNodeList] = {};
	      const entityNode = this.getDefaultEntityNode();
	      main_core.Dom.append(entityNode, babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].userSelector);
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
	function _renderTitle2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].title) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].title = main_core.Tag.render(_t7$2 || (_t7$2 = _$4`
				<div class="calendar-sharing__user-selector-title">
					<div class="calendar-sharing__user-selector-title-icon"></div>
					<div class="calendar-sharing__user-selector-title-text">
						${0}
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getTitleText)[_getTitleText]());
	    const infoNotify = babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].title.querySelector('[ data-role="calendar-sharing_popup-joint-slots"]');
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$3)[_layout$3].title;
	}
	function _getTitleText2() {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _model$2)[_model$2].getContext()) {
	    case 'calendar':
	      return main_core.Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_TITLE_V2');
	    case 'crm':
	      return main_core.Loc.getMessage('CALENDAR_SHARING_USER_SELECTOR_TITLE_CRM');
	    default:
	      return '';
	  }
	}
	function _renderCollabAvatar2(member) {
	  return new ui_avatar.AvatarRoundGuest({
	    size: 36,
	    userName: member.name,
	    userpicPath: this.hasAvatar(member.avatar) && member.avatar,
	    baseColor: '#19cc45'
	  }).getContainer();
	}

	let _$5 = t => t,
	  _t$5,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$3,
	  _t6$3,
	  _t7$3,
	  _t8$2,
	  _t9$1,
	  _t10;
	const MAX_AVATAR_COUNT = 4;
	var _props = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("props");
	var _layout$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _avatarPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("avatarPopup");
	var _deletePopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("deletePopup");
	class ListItem {
	  constructor(props) {
	    Object.defineProperty(this, _props, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$4, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _deletePopup)[_deletePopup] = null;
	    this.openAvatarList = this.openAvatarList.bind(this);
	    this.onCopyButtonClick = this.onCopyButtonClick.bind(this);
	    this.onDeleteButtonClick = this.onDeleteButtonClick.bind(this);
	  }
	  render() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper = main_core.Tag.render(_t$5 || (_t$5 = _$5`
				<div class="calendar-sharing__dialog-link-list-item">
					${0}
					${0}
					${0}
					${0}
				</div>
			`), this.renderAvatarContainer(), this.renderDate(), this.renderCopyButton(), this.renderDeleteButton());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper;
	  }
	  renderAvatarContainer() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].avatarContainer) {
	      const showMoreIcon = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length > MAX_AVATAR_COUNT;
	      const moreCounter = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length - MAX_AVATAR_COUNT;
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].avatarContainer = main_core.Tag.render(_t2$3 || (_t2$3 = _$5`
				<div class="calendar-sharing__dialog-link-list-item-avatar-container">
					${0}
					${0}
					${0}
				</div>
			`), this.renderAvatar(babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].userInfo), babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.slice(0, MAX_AVATAR_COUNT).map(member => this.renderAvatar(member)), showMoreIcon ? this.renderMore(moreCounter) : null);
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].avatarContainer, 'click', this.openAvatarList);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].avatarContainer;
	  }
	  renderAvatar(user) {
	    var _user$lastName;
	    const name = `${user.name} ${(_user$lastName = user.lastName) != null ? _user$lastName : ''}`.trim();
	    if (this.hasAvatar(user.avatar)) {
	      return main_core.Tag.render(_t3$3 || (_t3$3 = _$5`
				<img class="calendar-sharing__dialog-link-list-item-avatar" title="${0}" alt="" src="${0}">
			`), main_core.Text.encode(name), user.avatar);
	    }
	    return main_core.Tag.render(_t4$3 || (_t4$3 = _$5`
			<div class="ui-icon ui-icon-common-user calendar-sharing__dialog-link-list-item-avatar" title="${0}"><i></i></div>
		`), main_core.Text.encode(name));
	  }
	  hasAvatar(avatar) {
	    return avatar && avatar !== '/bitrix/images/1.gif';
	  }
	  renderMore(counter) {
	    return main_core.Tag.render(_t5$3 || (_t5$3 = _$5`
			<div class="calendar-sharing__dialog-link-list-item-more">
				<div class="calendar-sharing__dialog-link-list-item-more-text">${0}</div>
			</div>
		`), `+${counter}`);
	  }
	  openAvatarList() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup]) {
	      const uid = BX.util.getRandomString(6);
	      babelHelpers.classPrivateFieldLooseBase(this, _avatarPopup)[_avatarPopup] = main_popup.MenuManager.create({
	        id: `calendar-sharing-dialog_${uid}`,
	        bindElement: babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].avatarContainer,
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

	      // eslint-disable-next-line init-declarations
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
	      html: main_core.Tag.render(_t6$3 || (_t6$3 = _$5`
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].date) {
	      const date = babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].dateCreate ? new Date(babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].dateCreate) : new Date();
	      const formattedDate = calendar_util.Util.formatDate(date);
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].date = main_core.Tag.render(_t7$3 || (_t7$3 = _$5`
				<div class="calendar-sharing__dialog-link-list-item-date" title="${0}">${0}</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_DATE_CREATE'), formattedDate);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].date;
	  }
	  renderCopyButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].copyButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Main.LINK_3,
	        size: 14
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].copyButton = main_core.Tag.render(_t8$2 || (_t8$2 = _$5`
				<div class="calendar-sharing__dialog-link-list-item-copy-container">
					${0}
					<div class="calendar-sharing__dialog-link-list-item-copy-text">${0}</div>
				</div>
			`), icon.render(), main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_COPY'));
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].copyButton, 'click', this.onCopyButtonClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].copyButton;
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].members.length === 0) {
	      return main_core.Tag.render(_t9$1 || (_t9$1 = _$5`<div class="calendar-sharing__dialog-link-list-item-delete"></div>`));
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].deleteButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.CROSS_30,
	        size: 18
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].deleteButton = main_core.Tag.render(_t10 || (_t10 = _$5`
				<div class="calendar-sharing__dialog-link-list-item-delete">
					${0}
				</div>
			`), icon.render());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].deleteButton, 'click', this.onDeleteButtonClick);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].deleteButton;
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
	      text: main_core.Loc.getMessage('SHARING_WARNING_POPUP_SUBMIT_BUTTON_NEW_MSGVER_1'),
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper) {
	      BX.ajax.runAction('calendar.api.sharingajax.disableUserLink', {
	        data: {
	          hash: babelHelpers.classPrivateFieldLooseBase(this, _props)[_props].hash
	        }
	      });
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper, '--animate-delete');
	      setTimeout(() => {
	        main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _layout$4)[_layout$4].wrapper);
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

	let _$6 = t => t,
	  _t$6,
	  _t2$4,
	  _t3$4,
	  _t4$4,
	  _t5$4,
	  _t6$4,
	  _t7$4,
	  _t8$3;
	const DEFAULT_LIST_HEIGHT = 300;
	const LIST_PADDING_SUM = 45;
	var _props$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("props");
	var _layout$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _linkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linkList");
	var _popupOpenState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOpenState");
	var _pathToUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pathToUser");
	var _model$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("model");
	var _getSortingName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSortingName");
	class List {
	  constructor(props) {
	    Object.defineProperty(this, _getSortingName, {
	      value: _getSortingName2
	    });
	    Object.defineProperty(this, _model$3, {
	      get: _get_model$2,
	      set: void 0
	    });
	    Object.defineProperty(this, _props$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$5, {
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
	    Object.defineProperty(this, _pathToUser, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1] = props;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser] = null;
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper = main_core.Tag.render(_t$6 || (_t$6 = _$6`
				<div class="calendar-sharing__dialog-link-list-wrapper">
					${0}
					${0}
				</div>
			`), this.getTitleNode(), this.getListNode());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper;
	  }
	  getTitleNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].title) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].title = main_core.Tag.render(_t2$4 || (_t2$4 = _$6`
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].title;
	  }
	  getChevronBackIcon() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].backButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.CHEVRON_LEFT,
	        size: 24
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].backButton = main_core.Tag.render(_t3$4 || (_t3$4 = _$6`
				<div class="calendar-sharing__dialog-link-list-back-button">
					${0}
				</div>
			`), icon.render());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].backButton, 'click', this.close.bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].backButton;
	  }
	  getSortingButton() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton) {
	      const icon = new ui_iconSet_api_core.Icon({
	        icon: ui_iconSet_api_core.Actions.SORT,
	        size: 14,
	        color: '#2066b0'
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton = main_core.Tag.render(_t4$4 || (_t4$4 = _$6`
				<div class="calendar-sharing__dialog-link-list-sorting-button">
					${0}
					${0}
				</div>
			`), icon.render(), this.getSortingButtonText());
	      main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton, 'click', this.changeListSort.bind(this));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton;
	  }
	  getSortingButtonText() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButtonText) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButtonText = main_core.Tag.render(_t5$4 || (_t5$4 = _$6`
				<div class="calendar-sharing__dialog-link-list-sorting-button-text">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _getSortingName)[_getSortingName]());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButtonText;
	  }
	  getListNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list = main_core.Tag.render(_t6$4 || (_t6$4 = _$6`
				<div class="calendar-sharing__dialog-link-list-container">
					${0}
				</div>
			`), this.getListItemsNode());
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list;
	  }
	  getListItemsNode() {
	    if (this.isListEmpty()) {
	      return this.getEmptyStateNode();
	    }
	    const linkListItems = this.getListItems();
	    return main_core.Tag.render(_t7$4 || (_t7$4 = _$6`
			<div class="calendar-sharing__dialog-link-list">
				${0}
			</div>
		`), linkListItems.map(listItem => listItem.render()));
	  }
	  getEmptyStateNode() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].emptyState) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].emptyState = main_core.Tag.render(_t8$3 || (_t8$3 = _$6`
				<div class="calendar-sharing__dialog-link-list-empty-state-wrapper">
					<div class="calendar-sharing__dialog-link-list-empty-state-icon"></div>
					<div class="calendar-sharing__dialog-link-list-empty-state-text">${0}</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_LIST_EMPTY_TITLE'));
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].emptyState;
	  }
	  getListItems() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _model$3)[_model$3].sortJointLinksByFrequentUse()) {
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
	      userInfo: babelHelpers.classPrivateFieldLooseBase(this, _model$3)[_model$3].getUserInfo(),
	      pathToUser: babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser],
	      setListItemPopupState: this.setListItemPopupState
	    }));
	  }
	  getSortedByDateListItems() {
	    return Object.keys(babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList]).sort((a, b) => b - a).map(index => {
	      return new ListItem({
	        ...babelHelpers.classPrivateFieldLooseBase(this, _linkList)[_linkList][index],
	        userInfo: babelHelpers.classPrivateFieldLooseBase(this, _model$3)[_model$3].getUserInfo(),
	        pathToUser: babelHelpers.classPrivateFieldLooseBase(this, _pathToUser)[_pathToUser],
	        setListItemPopupState: this.setListItemPopupState
	      });
	    });
	  }
	  show(maxListHeight) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list && maxListHeight) {
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list, 'max-height', `${maxListHeight - LIST_PADDING_SUM}px`);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper, '--show');
	    }
	  }
	  close() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list) {
	      main_core.Dom.style(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list, 'max-height', `${DEFAULT_LIST_HEIGHT}px`);
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].wrapper, '--show');
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].onLinkListClose) {
	      babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].onLinkListClose();
	    }
	  }
	  updateLinkList() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list) {
	      main_core.Dom.clean(this.getListNode());
	      const listItems = this.getListItemsNode();
	      main_core.Dom.append(listItems, babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].list);
	    }
	  }
	  changeListSort() {
	    babelHelpers.classPrivateFieldLooseBase(this, _model$3)[_model$3].changeSortJointLinksByFrequentUse();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButtonText) {
	      main_core.Dom.adjust(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButtonText, {
	        text: babelHelpers.classPrivateFieldLooseBase(this, _getSortingName)[_getSortingName]()
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton, '--hide');
	    }
	  }
	  showSortingButton() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$5)[_layout$5].sortingButton, '--hide');
	    }
	  }
	}
	function _get_model$2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _props$1)[_props$1].model;
	}
	function _getSortingName2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _model$3)[_model$3].sortJointLinksByFrequentUse() ? main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_RECENT') : main_core.Loc.getMessage('CALENDAR_SHARING_LINK_LIST_SORT_DATE');
	}

	let _$7 = t => t,
	  _t$7,
	  _t2$5,
	  _t3$5,
	  _t4$5,
	  _t5$5,
	  _t6$5,
	  _t7$5,
	  _t8$4;
	var _params$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _layout$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _settingsControl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsControl");
	var _userSelectorControl = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userSelectorControl");
	var _linkList$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("linkList");
	var _settingsModel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsModel");
	var _bindEvents$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindEvents");
	var _renderMain = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMain");
	var _renderDialogMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDialogMessage");
	var _onOpenLink = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onOpenLink");
	var _renderTop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTop");
	var _renderHowDoesItWorkIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHowDoesItWorkIcon");
	var _openHelpDesk = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openHelpDesk");
	var _getSharingInfoMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSharingInfoMessage");
	var _getContextHelpDeskCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContextHelpDeskCode");
	var _renderSettings = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderSettings");
	var _renderMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMembers");
	var _renderMainBottom = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMainBottom");
	var _renderCopyLinkButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderCopyLinkButton");
	var _onButtonCopyClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onButtonCopyClick");
	var _renderLinkHistoryButton = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLinkHistoryButton");
	var _renderLinkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderLinkList");
	var _getLinkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLinkList");
	var _openLinkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openLinkList");
	var _closeLinkList = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("closeLinkList");
	var _copyToClipboard = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyToClipboard");
	class Layout {
	  constructor(_params2) {
	    var _params2$settingsMode;
	    Object.defineProperty(this, _copyToClipboard, {
	      value: _copyToClipboard2
	    });
	    Object.defineProperty(this, _closeLinkList, {
	      value: _closeLinkList2
	    });
	    Object.defineProperty(this, _openLinkList, {
	      value: _openLinkList2
	    });
	    Object.defineProperty(this, _getLinkList, {
	      value: _getLinkList2
	    });
	    Object.defineProperty(this, _renderLinkList, {
	      value: _renderLinkList2
	    });
	    Object.defineProperty(this, _renderLinkHistoryButton, {
	      value: _renderLinkHistoryButton2
	    });
	    Object.defineProperty(this, _onButtonCopyClick, {
	      value: _onButtonCopyClick2
	    });
	    Object.defineProperty(this, _renderCopyLinkButton, {
	      value: _renderCopyLinkButton2
	    });
	    Object.defineProperty(this, _renderMainBottom, {
	      value: _renderMainBottom2
	    });
	    Object.defineProperty(this, _renderMembers, {
	      value: _renderMembers2
	    });
	    Object.defineProperty(this, _renderSettings, {
	      value: _renderSettings2
	    });
	    Object.defineProperty(this, _getContextHelpDeskCode, {
	      value: _getContextHelpDeskCode2
	    });
	    Object.defineProperty(this, _getSharingInfoMessage, {
	      value: _getSharingInfoMessage2
	    });
	    Object.defineProperty(this, _openHelpDesk, {
	      value: _openHelpDesk2
	    });
	    Object.defineProperty(this, _renderHowDoesItWorkIcon, {
	      value: _renderHowDoesItWorkIcon2
	    });
	    Object.defineProperty(this, _renderTop, {
	      value: _renderTop2
	    });
	    Object.defineProperty(this, _onOpenLink, {
	      value: _onOpenLink2
	    });
	    Object.defineProperty(this, _renderDialogMessage, {
	      value: _renderDialogMessage2
	    });
	    Object.defineProperty(this, _renderMain, {
	      value: _renderMain2
	    });
	    Object.defineProperty(this, _bindEvents$2, {
	      value: _bindEvents2$2
	    });
	    Object.defineProperty(this, _settingsModel, {
	      get: _get_settingsModel,
	      set: void 0
	    });
	    this.HELP_DESK_CODE_CALENDAR = 17198666;
	    this.HELP_DESK_CODE_CRM = 17502612;
	    this.CONTEXT = {
	      CRM: 'crm',
	      CALENDAR: 'calendar'
	    };
	    Object.defineProperty(this, _params$3, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$6, {
	      writable: true,
	      value: void 0
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
	    babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3] = _params2;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _bindEvents$2)[_bindEvents$2]();
	    this.isGroupContext = ((_params2$settingsMode = _params2.settingsModel.getCalendarContext()) == null ? void 0 : _params2$settingsMode.sharingObjectType) === 'group';
	  }
	  reset() {
	    var _babelHelpers$classPr;
	    void babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].save();
	    (_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) == null ? void 0 : _babelHelpers$classPr.clearSelectedUsers();
	    setTimeout(() => {
	      var _babelHelpers$classPr2;
	      return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) == null ? void 0 : _babelHelpers$classPr2.close();
	    }, 200);
	  }
	  hasShownPopups() {
	    var _babelHelpers$classPr3, _babelHelpers$classPr4;
	    const isSettingsPopupShown = babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].hasShownPopups();
	    const isUserSelectorDialogOpened = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) == null ? void 0 : _babelHelpers$classPr3.isUserSelectorDialogOpened();
	    const isListItemPopupOpened = (_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) == null ? void 0 : _babelHelpers$classPr4.isOpenListItemPopup();
	    return isSettingsPopupShown || isUserSelectorDialogOpened || isListItemPopupOpened;
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].wrap = main_core.Tag.render(_t$7 || (_t$7 = _$7`
			<div class="calendar-sharing__dialog-wrapper">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderMain)[_renderMain](), this.isGroupContext ? null : babelHelpers.classPrivateFieldLooseBase(this, _renderLinkList)[_renderLinkList]());
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].wrap;
	  }
	  async saveJointLink() {
	    var _babelHelpers$classPr5;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy && main_core.Dom.hasClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy, 'ui-btn-clock')) {
	      return;
	    }
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy, 'ui-btn-clock');
	    const link = await babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].saveJointLink();
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy, 'ui-btn-clock');
	    await this.copyLink(link.url, link.hash);
	    (_babelHelpers$classPr5 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1]) == null ? void 0 : _babelHelpers$classPr5.getLinkListInfo();
	  }
	  async copyLink(url, hash) {
	    if (!url) {
	      return false;
	    }
	    try {
	      await babelHelpers.classPrivateFieldLooseBase(this, _copyToClipboard)[_copyToClipboard](url);
	    } catch {
	      return false;
	    }
	    calendar_util.Util.showNotification(main_core.Loc.getMessage('SHARING_COPY_LINK_NOTIFICATION'));
	    main_core_events.EventEmitter.emit('CalendarSharing:LinkCopied', {
	      url,
	      hash
	    });
	    return true;
	  }
	}
	function _get_settingsModel() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].settingsModel;
	}
	function _bindEvents2$2() {
	  main_core.Event.bind(window, 'beforeunload', () => babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].save());
	  main_core_events.EventEmitter.subscribe('CalendarSharing:onJointLinkCopy', async event => {
	    const shortUrl = event.data.shortUrl;
	    const linkHash = event.data.hash;
	    await this.copyLink(shortUrl, linkHash);
	    calendar_sharing_analytics.Analytics.sendLinkCopiedList(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext(), {
	      peopleCount: event.data.members.length + 1,
	      ruleChanges: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getChanges()
	    });
	  });
	}
	function _renderMain2() {
	  var _babelHelpers$classPr6, _babelHelpers$classPr7;
	  (_babelHelpers$classPr7 = (_babelHelpers$classPr6 = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6]).main) != null ? _babelHelpers$classPr7 : _babelHelpers$classPr6.main = main_core.Tag.render(_t2$5 || (_t2$5 = _$7`
			<div class="calendar-sharing__dialog-content-wrapper --show">
				${0}
				<div class="calendar-sharing__dialog-body">
					${0}
					${0}
					${0}
				</div>
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderTop)[_renderTop](), babelHelpers.classPrivateFieldLooseBase(this, _renderDialogMessage)[_renderDialogMessage](), babelHelpers.classPrivateFieldLooseBase(this, _renderSettings)[_renderSettings](), babelHelpers.classPrivateFieldLooseBase(this, _renderMembers)[_renderMembers](), babelHelpers.classPrivateFieldLooseBase(this, _renderMainBottom)[_renderMainBottom]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].main;
	}
	function _renderDialogMessage2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext() === this.CONTEXT.CRM || this.isGroupContext) {
	    return '';
	  }
	  return main_core.Tag.render(_t3$5 || (_t3$5 = _$7`
			<div class="calendar-sharing__dialog-message">
				<div class="calendar-sharing__dialog-info-icon-container">
					<div class="calendar-sharing__dialog-info-icon"></div>
				</div>
				<div class="calendar-sharing__dialog-notify" onclick="${0}">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _onOpenLink)[_onOpenLink].bind(this), main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_4_V3', {
	    '#LINK#': babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getSharingUrl()
	  }));
	}
	async function _onOpenLink2() {
	  await babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].save();
	  window.open(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getSharingUrl(), '_blank').focus();
	}
	function _renderTop2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainTop) {
	    var _babelHelpers$classPr8;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainTop = main_core.Tag.render(_t4$5 || (_t4$5 = _$7`
				<div class="calendar-sharing__dialog-top">
					<div class="calendar-sharing__dialog-title">
						<span>${0}</span>
						${0}
						${0}
					</div>
					<div class="calendar-sharing__dialog-info">
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('SHARING_BUTTON_TITLE'), babelHelpers.classPrivateFieldLooseBase(this, _renderHowDoesItWorkIcon)[_renderHowDoesItWorkIcon](), (_babelHelpers$classPr8 = babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].externalIcon) != null ? _babelHelpers$classPr8 : '', babelHelpers.classPrivateFieldLooseBase(this, _getSharingInfoMessage)[_getSharingInfoMessage]());
	    const howDoesItWork = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainTop.querySelector('[data-role="calendar-sharing-how-does-it-work"]');
	    main_core.Event.bind(howDoesItWork, 'click', babelHelpers.classPrivateFieldLooseBase(this, _openHelpDesk)[_openHelpDesk].bind(this));
	    const infoNotify = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainTop.querySelector('[data-role="calendar-sharing_popup-open-link"]');
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
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainTop;
	}
	function _renderHowDoesItWorkIcon2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext() === this.CONTEXT.CRM) {
	    return '';
	  }
	  const howDoesItWork = main_core.Tag.render(_t5$5 || (_t5$5 = _$7`
			<span
				class="calendar-sharing__dialog-title-help"
				title="${0}"
			></span>
		`), main_core.Loc.getMessage('SHARING_INFO_POPUP_HOW_IT_WORK'));
	  main_core.Event.bind(howDoesItWork, 'click', babelHelpers.classPrivateFieldLooseBase(this, _openHelpDesk)[_openHelpDesk].bind(this));
	  return howDoesItWork;
	}
	function _openHelpDesk2() {
	  top.BX.Helper.show(`redirect=detail&code=${babelHelpers.classPrivateFieldLooseBase(this, _getContextHelpDeskCode)[_getContextHelpDeskCode]()}`);
	}
	function _getSharingInfoMessage2() {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext()) {
	    case this.CONTEXT.CALENDAR:
	      return main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_3_CALENDAR');
	    case this.CONTEXT.CRM:
	      return main_core.Loc.getMessage('SHARING_INFO_POPUP_CONTENT_3_CRM_MSGVER_2');
	    default:
	      return '';
	  }
	}
	function _getContextHelpDeskCode2() {
	  switch (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext()) {
	    case this.CONTEXT.CALENDAR:
	      return this.HELP_DESK_CODE_CALENDAR;
	    case this.CONTEXT.CRM:
	      return this.HELP_DESK_CODE_CRM;
	    default:
	      return 0;
	  }
	}
	function _renderSettings2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl] = new Settings({
	    readOnly: babelHelpers.classPrivateFieldLooseBase(this, _params$3)[_params$3].readOnly,
	    model: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel]
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _settingsControl)[_settingsControl].render();
	}
	function _renderMembers2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl] = new UserSelector({
	    model: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel],
	    onMembersAdded: () => calendar_sharing_analytics.Analytics.sendMembersAdded(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext(), babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].getPeopleCount())
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].render();
	}
	function _renderMainBottom2() {
	  var _babelHelpers$classPr9, _babelHelpers$classPr10;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext() === this.CONTEXT.CRM) {
	    return '';
	  }
	  (_babelHelpers$classPr10 = (_babelHelpers$classPr9 = babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6]).mainBottom) != null ? _babelHelpers$classPr10 : _babelHelpers$classPr9.mainBottom = main_core.Tag.render(_t6$5 || (_t6$5 = _$7`
			<div class="calendar-sharing__dialog-bottom">
				${0}
				${0}
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _renderCopyLinkButton)[_renderCopyLinkButton](), this.isGroupContext ? null : babelHelpers.classPrivateFieldLooseBase(this, _renderLinkHistoryButton)[_renderLinkHistoryButton]());
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].mainBottom;
	}
	function _renderCopyLinkButton2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy = main_core.Tag.render(_t7$5 || (_t7$5 = _$7`
				<span class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps calendar-sharing__dialog-copy">
					${0}
				</span>
			`), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_COPY_LINK_BUTTON'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy, 'click', babelHelpers.classPrivateFieldLooseBase(this, _onButtonCopyClick)[_onButtonCopyClick].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonCopy;
	}
	async function _onButtonCopyClick2() {
	  var _babelHelpers$classPr11, _babelHelpers$classPr12;
	  const params = {
	    peopleCount: (_babelHelpers$classPr11 = (_babelHelpers$classPr12 = babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl]) == null ? void 0 : _babelHelpers$classPr12.getPeopleCount()) != null ? _babelHelpers$classPr11 : 1,
	    ruleChanges: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getChanges()
	  };
	  if (babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl] && babelHelpers.classPrivateFieldLooseBase(this, _userSelectorControl)[_userSelectorControl].hasChanges()) {
	    calendar_sharing_analytics.Analytics.sendLinkCopied(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext(), calendar_sharing_analytics.Analytics.linkTypes.multiple, params);
	    void this.saveJointLink();
	  } else if (await this.copyLink(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getSharingUrl())) {
	    calendar_sharing_analytics.Analytics.sendLinkCopied(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext(), calendar_sharing_analytics.Analytics.linkTypes.solo, params);
	    if (!this.isGroupContext) {
	      babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].increaseFrequentUse();
	    }
	  }
	}
	function _renderLinkHistoryButton2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonHistory) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonHistory = main_core.Tag.render(_t8$4 || (_t8$4 = _$7`
				<span
					class="ui-btn ui-btn-round ui-btn-light ui-btn-no-caps calendar-sharing__dialog-people"
					data-id="calendar-sharing-history-btn"
				>
					${0}
				</span>
			`), main_core.Loc.getMessage('SHARING_DIALOG_SHARING_BLOCK_JOINT_SLOTS_BUTTON'));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonHistory, 'click', babelHelpers.classPrivateFieldLooseBase(this, _openLinkList)[_openLinkList].bind(this));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].buttonHistory;
	}
	function _renderLinkList2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel].getContext() === this.CONTEXT.CRM) {
	    return null;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _getLinkList)[_getLinkList]().render();
	}
	function _getLinkList2() {
	  var _babelHelpers$classPr13, _babelHelpers$classPr14;
	  (_babelHelpers$classPr14 = (_babelHelpers$classPr13 = babelHelpers.classPrivateFieldLooseBase(this, _linkList$1))[_linkList$1]) != null ? _babelHelpers$classPr14 : _babelHelpers$classPr13[_linkList$1] = new List({
	    model: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel)[_settingsModel],
	    onLinkListClose: babelHelpers.classPrivateFieldLooseBase(this, _closeLinkList)[_closeLinkList].bind(this)
	  });
	  return babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1];
	}
	function _openLinkList2() {
	  main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].main, '--show');
	  babelHelpers.classPrivateFieldLooseBase(this, _linkList$1)[_linkList$1].show(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].main.offsetHeight);
	}
	function _closeLinkList2() {
	  main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$6)[_layout$6].main, '--show');
	}
	async function _copyToClipboard2(textToCopy) {
	  var _BX$clipboard;
	  if (!main_core.Type.isString(textToCopy)) {
	    return Promise.reject();
	  }

	  // navigator.clipboard defined only if window.isSecureContext === true
	  // so or https should be activated, or localhost address
	  if (navigator.clipboard) {
	    // safari not allowed clipboard manipulation as result of ajax request
	    // so timeout is hack for this, to prevent "not have permission"
	    return new Promise((resolve, reject) => {
	      setTimeout(() => navigator.clipboard.writeText(textToCopy).then(() => resolve()).catch(e => reject(e)), 0);
	    });
	  }
	  return (_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(textToCopy) ? Promise.resolve() : Promise.reject();
	}

	var _popup$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _layout$7 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _dialogLayout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogLayout");
	var _settingsModel$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("settingsModel");
	var _isExternalSharing = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isExternalSharing");
	var _getAngleConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAngleConfig");
	class DialogNew {
	  constructor(options) {
	    Object.defineProperty(this, _getAngleConfig, {
	      value: _getAngleConfig2
	    });
	    Object.defineProperty(this, _isExternalSharing, {
	      value: _isExternalSharing2
	    });
	    Object.defineProperty(this, _popup$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$7, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogLayout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _settingsModel$1, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7] = {};
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].bindElement = options.bindElement;
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1] = new SettingsModel({
	      context: options.context,
	      linkHash: options.linkHash,
	      sharingUrl: options.sharingUrl,
	      userInfo: options.userInfo,
	      rule: options.sharingRule,
	      calendarSettings: options.calendarSettings,
	      collapsed: options.settingsCollapsed,
	      sortJointLinksByFrequentUse: options.sortJointLinksByFrequentUse,
	      calendarContext: options.calendarContext
	    });
	    this.bindEvents();
	  }
	  bindEvents() {
	    main_core_events.EventEmitter.subscribe('CalendarSharing:LinkCopied', this.onSuccessfulCopyingLink.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', event => this.checkAndClosePopupOnSlider(event));
	  }
	  getPopup() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1] = new main_popup.Popup({
	        bindElement: babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].bindElement,
	        targetContainer: document.body,
	        className: 'calendar-sharing__dialog',
	        closeByEsc: true,
	        autoHide: true,
	        padding: 0,
	        width: 470,
	        angle: babelHelpers.classPrivateFieldLooseBase(this, _getAngleConfig)[_getAngleConfig](),
	        autoHideHandler: event => this.canBeClosed(event),
	        content: this.getPopupWrapper(),
	        animation: 'fading-slide',
	        events: {
	          onPopupShow: this.onPopupShow.bind(this),
	          onPopupClose: this.onPopupClose.bind(this)
	        }
	      });
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup$1)[_popup$1];
	  }
	  onPopupShow() {
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].bindElement, 'ui-btn-hover');
	    calendar_sharing_analytics.Analytics.sendPopupOpened(babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].getContext());
	  }
	  onPopupClose() {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].bindElement, 'ui-btn-hover');
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogLayout)[_dialogLayout].reset();
	  }
	  canBeClosed(event) {
	    const isClickInside = babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper.contains(event.target);
	    const layoutHasShownPopups = babelHelpers.classPrivateFieldLooseBase(this, _dialogLayout)[_dialogLayout].hasShownPopups();
	    const topSlider = this.getTopSlider();
	    const calendarOpenInTopSlider = topSlider && this.getCalendarSliderParams(topSlider);
	    return !isClickInside && !layoutHasShownPopups && (!topSlider || calendarOpenInTopSlider || babelHelpers.classPrivateFieldLooseBase(this, _isExternalSharing)[_isExternalSharing]());
	  }
	  getPopupWrapper() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper) {
	      var _babelHelpers$classPr;
	      babelHelpers.classPrivateFieldLooseBase(this, _dialogLayout)[_dialogLayout] = new Layout({
	        readOnly: ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].getCalendarContext()) == null ? void 0 : _babelHelpers$classPr.sharingObjectType) === 'group',
	        settingsModel: babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1]
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper = babelHelpers.classPrivateFieldLooseBase(this, _dialogLayout)[_dialogLayout].render();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper = babelHelpers.classPrivateFieldLooseBase(this, _dialogLayout)[_dialogLayout].render();
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].wrapper;
	  }
	  onSuccessfulCopyingLink() {
	    this.closePopup();
	  }
	  closePopup() {
	    this.getPopup().close();
	  }
	  isShown() {
	    return this.getPopup().isShown();
	  }
	  show() {
	    babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].sortRanges();
	    this.getPopup().adjustPosition({
	      forceBindPosition: true
	    });
	    this.getPopup().show();
	  }
	  destroy() {
	    this.getPopup().destroy();
	  }
	  getTopSlider() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].getContext() === 'calendar' ? BX.SidePanel.Instance.getTopSlider() : false;
	  }
	  getCalendarSliderParams(slider) {
	    var _slider$iframeSrc;
	    return (_slider$iframeSrc = slider.iframeSrc) == null ? void 0 : _slider$iframeSrc.match(/\/workgroups\/group\/(\d+)\/calendar\//i);
	  }
	  checkAndClosePopupOnSlider(event) {
	    var _event$getData$;
	    if (!this.isShown()) {
	      return;
	    }
	    const slider = event.getData() && ((_event$getData$ = event.getData()[0]) == null ? void 0 : _event$getData$.slider);
	    const sliderParams = slider && this.getCalendarSliderParams(slider);
	    if (!sliderParams) {
	      return;
	    }
	    const groupId = parseInt(sliderParams[1], 10);
	    if (!groupId) {
	      return;
	    }
	    const currentGroupId = babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].getCalendarContext().sharingObjectId;
	    if (groupId !== currentGroupId) {
	      return;
	    }
	    this.closePopup();
	  }
	}
	function _isExternalSharing2() {
	  var _babelHelpers$classPr2;
	  return Boolean((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _settingsModel$1)[_settingsModel$1].getCalendarContext()) == null ? void 0 : _babelHelpers$classPr2.externalSharing);
	}
	function _getAngleConfig2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isExternalSharing)[_isExternalSharing]()) {
	    return null;
	  }
	  return {
	    offset: babelHelpers.classPrivateFieldLooseBase(this, _layout$7)[_layout$7].bindElement.offsetWidth / 2 + 16
	  };
	}

	let _$8 = t => t,
	  _t$8,
	  _t2$6,
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
	      ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	        featureId: 'calendar_sharing'
	      }).show();
	      return;
	    }
	    if (this.isSharingEnabled()) {
	      this.openDialog();
	    } else {
	      this.switcher.toggle();
	    }
	  }
	  getSwitcherContainer() {
	    return main_core.Tag.render(_t$8 || (_t$8 = _$8`
			<div class="calendar-sharing__switcher"></div>
		`));
	  }
	  getSwitcherDivider() {
	    return main_core.Tag.render(_t2$6 || (_t2$6 = _$8`
			<div class="calendar-sharing__switcher_divider"></div>
		`));
	  }
	  renderSwitcher() {
	    main_core.Dom.append(this.getSwitcherDivider(), this.wrap);
	    this.switcherWrap = main_core.Tag.render(_t3$6 || (_t3$6 = _$8`<div class="calendar-sharing__switcher-wrap"></div>`));
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
	      ui_infoHelper.FeaturePromotersRegistry.getPromoter({
	        featureId: 'calendar_sharing'
	      }).show();
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

	class GroupSharingButton extends SharingButton {
	  constructor(options = {}) {
	    super(options);
	    this.calendarContext = options.calendarContext;
	  }

	  /**
	   * @override
	   */
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
	        sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
	        calendarContext: this.calendarContext
	      });
	    }
	    if (!this.newDialog.isShown()) {
	      this.newDialog.show();
	    }
	  }

	  /**
	   * @override
	   */
	  enableSharing() {
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';
	    BX.ajax.runAction('calendar.api.sharinggroupajax.enableSharing', {
	      data: {
	        groupId: this.calendarContext.sharingObjectId
	      }
	    }).then(response => {
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

	  /**
	   * @override
	   */
	  disableSharing() {
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingDisabled';
	    this.warningPopup.close();
	    BX.ajax.runAction('calendar.api.sharinggroupajax.disableSharing', {
	      data: {
	        groupId: this.calendarContext.sharingObjectId
	      }
	    }).then(() => {
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
	}

	class Interface {
	  constructor(options) {
	    var _options$payAttention, _options$sharingFeatu, _options$sharingSetti, _options$sortJointLin, _options$calendarCont;
	    this.buttonWrap = options.buttonWrap;
	    this.userInfo = options.userInfo || null;
	    this.payAttentionToNewFeature = (_options$payAttention = options.payAttentionToNewFeature) != null ? _options$payAttention : false;
	    this.sharingFeatureLimit = (_options$sharingFeatu = options.sharingFeatureLimit) != null ? _options$sharingFeatu : false;
	    this.sharingSettingsCollapsed = (_options$sharingSetti = options.sharingSettingsCollapsed) != null ? _options$sharingSetti : false;
	    this.sortJointLinksByFrequentUse = (_options$sortJointLin = options.sortJointLinksByFrequentUse) != null ? _options$sortJointLin : false;
	    this.calendarContext = (_options$calendarCont = options.calendarContext) != null ? _options$calendarCont : null;
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
	  showGroupSharingButton() {
	    this.sharingButton = new GroupSharingButton({
	      wrap: this.buttonWrap,
	      userInfo: this.userInfo,
	      payAttentionToNewFeature: this.payAttentionToNewFeature,
	      sharingFeatureLimit: this.sharingFeatureLimit,
	      sharingSettingsCollapsed: this.sharingSettingsCollapsed,
	      sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
	      calendarContext: this.calendarContext
	    });
	    this.sharingButton.show();
	  }
	}

	let _$9 = t => t,
	  _t$9,
	  _t2$7;
	var _popup$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _loader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("loader");
	var _layout$8 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _qrCode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("qrCode");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	class DialogQr {
	  constructor(options) {
	    this.QRCODE_SIZE = 114;
	    this.QRCODE_COLOR_LIGHT = '#fff';
	    this.QRCODE_COLOR_DARK = '#000';
	    Object.defineProperty(this, _popup$2, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _loader, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _layout$8, {
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
	    babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _loader)[_loader] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8] = {
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2] = new main_popup.Popup({
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
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup$2)[_popup$2];
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].qr) {
	      babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].qr = main_core.Tag.render(_t$9 || (_t$9 = _$9`
				<div class="calendar-sharing__qr-block"></div>
			`));

	      // qr emulation
	      this.getLoader().show(babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].qr);
	      this.showQr();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].qr;
	  }
	  async showQr() {
	    await this.initQrCode();
	    this.QRCode = new QRCode(babelHelpers.classPrivateFieldLooseBase(this, _layout$8)[_layout$8].qr, {
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
	    return main_core.Tag.render(_t2$7 || (_t2$7 = _$9`
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

	class GroupSharing extends GroupSharingButton {
	  constructor(options = {}) {
	    super(options);
	    this.bindElement = options.bindElement;
	    this.calendarSettings = options.calendarSettings;
	    this.context = options.context;
	    if (options.sharingConfig) {
	      var _this$sharingConfig, _this$sharingConfig2, _this$sharingConfig3;
	      this.sharingConfig = options.sharingConfig;
	      this.sharingUrl = ((_this$sharingConfig = this.sharingConfig) == null ? void 0 : _this$sharingConfig.url) || null;
	      this.linkHash = ((_this$sharingConfig2 = this.sharingConfig) == null ? void 0 : _this$sharingConfig2.hash) || null;
	      this.sharingRule = ((_this$sharingConfig3 = this.sharingConfig) == null ? void 0 : _this$sharingConfig3.rule) || null;
	    }
	  }

	  /**
	   * @override
	   */
	  openDialog() {
	    if (!this.newDialog) {
	      this.newDialog = new DialogNew({
	        bindElement: this.bindElement,
	        sharingUrl: this.sharingUrl,
	        linkHash: this.linkHash,
	        sharingRule: this.sharingRule,
	        context: this.context,
	        calendarSettings: {
	          weekHolidays: this.calendarSettings.week_holidays,
	          weekStart: this.calendarSettings.week_start,
	          workTimeStart: this.calendarSettings.work_time_start,
	          workTimeEnd: this.calendarSettings.work_time_end
	        },
	        userInfo: this.userInfo,
	        settingsCollapsed: this.sharingSettingsCollapsed,
	        sortJointLinksByFrequentUse: this.sortJointLinksByFrequentUse,
	        calendarContext: this.calendarContext
	      });
	    }
	    if (!this.newDialog.isShown()) {
	      this.newDialog.show();
	    }
	  }

	  /**
	   * @override
	   */
	  enableSharing() {
	    const event = 'Calendar.Sharing.copyLinkButton:onSharingEnabled';
	    const action = 'calendar.api.sharinggroupajax.enableSharing';
	    const data = {
	      groupId: this.calendarContext.sharingObjectId
	    };
	    BX.ajax.runAction(action, {
	      data
	    }).then(response => {
	      main_core_events.EventEmitter.emit(event, {
	        isChecked: true,
	        url: response.data.url
	      });
	    });
	  }
	}

	var _groupSharing = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupSharing");
	var _groupId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("groupId");
	var _bindElement$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindElement");
	var _config = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("config");
	var _getSharingConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getSharingConfig");
	var _requestSharingConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestSharingConfig");
	class GroupSharingController {
	  static async getGroupSharing(groupId, bindElement) {
	    if (babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _groupSharing)[_groupSharing] && babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _groupId)[_groupId] === groupId && babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _bindElement$1)[_bindElement$1] === bindElement) {
	      return babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _groupSharing)[_groupSharing];
	    }
	    const config = await babelHelpers.classPrivateFieldLooseBase(this, _getSharingConfig)[_getSharingConfig](groupId);
	    babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _groupSharing)[_groupSharing] = new GroupSharing({
	      bindElement,
	      context: 'calendar',
	      calendarContext: {
	        sharingObjectType: 'group',
	        sharingObjectId: groupId,
	        externalSharing: true
	      },
	      userInfo: {
	        id: config.user.id,
	        name: config.user.name,
	        avatar: config.user.avatar,
	        isCollabUser: config.user.isCollabUser
	      },
	      sharingConfig: config.link,
	      calendarSettings: config.userCalendarSettings
	    });
	    babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _config)[_config] = config;
	    babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _groupId)[_groupId] = groupId;
	    babelHelpers.classPrivateFieldLooseBase(GroupSharingController, _bindElement$1)[_bindElement$1] = bindElement;
	    return babelHelpers.classPrivateFieldLooseBase(this, _groupSharing)[_groupSharing];
	  }
	}
	async function _getSharingConfig2(groupId) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _groupId)[_groupId] === groupId && babelHelpers.classPrivateFieldLooseBase(this, _config)[_config]) {
	    return new Promise(resolve => {
	      void resolve(babelHelpers.classPrivateFieldLooseBase(this, _config)[_config]);
	    });
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _requestSharingConfig)[_requestSharingConfig](groupId);
	}
	async function _requestSharingConfig2(groupId) {
	  const action = 'calendar.api.sharinggroupajax.enableAndGetSharingConfig';
	  const response = await BX.ajax.runAction(action, {
	    data: {
	      groupId
	    }
	  });
	  return response.data;
	}
	Object.defineProperty(GroupSharingController, _requestSharingConfig, {
	  value: _requestSharingConfig2
	});
	Object.defineProperty(GroupSharingController, _getSharingConfig, {
	  value: _getSharingConfig2
	});
	Object.defineProperty(GroupSharingController, _groupSharing, {
	  writable: true,
	  value: null
	});
	Object.defineProperty(GroupSharingController, _groupId, {
	  writable: true,
	  value: null
	});
	Object.defineProperty(GroupSharingController, _bindElement$1, {
	  writable: true,
	  value: null
	});
	Object.defineProperty(GroupSharingController, _config, {
	  writable: true,
	  value: null
	});

	exports.Interface = Interface;
	exports.SharingButton = SharingButton;
	exports.DialogNew = DialogNew;
	exports.DialogQr = DialogQr;
	exports.Layout = Layout;
	exports.RuleModel = RuleModel;
	exports.RangeModel = RangeModel;
	exports.SettingsModel = SettingsModel;
	exports.GroupSharing = GroupSharing;
	exports.GroupSharingController = GroupSharingController;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Main,BX.Calendar.Sharing,BX.UI.EntitySelector,BX.UI,BX.UI.IconSet,BX.UI.Dialogs,BX.UI,BX,BX,BX,BX.UI,BX,BX.UI.Tour,BX.UI,BX.UI,BX.Calendar,BX,BX.Main,BX,BX.Event));
//# sourceMappingURL=interface.bundle.js.map
