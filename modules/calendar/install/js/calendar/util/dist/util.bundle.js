/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_date,main_popup,pull_client,ui_dialogs_messagebox) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	class Util {
	  static parseTime(str) {
	    let date = Util.parseDate1(BX.date.format(Util.getDateFormat(), new Date()) + ' ' + str, false);
	    return date ? {
	      h: date.getHours(),
	      m: date.getMinutes()
	    } : date;
	  }
	  static getTimeRounded(date) {
	    return Math.round(date.getTime() / 60000) * 60000;
	  }
	  static parseDate(str, bUTC, formatDate, formatDatetime) {
	    return BX.parseDate(str, bUTC, formatDate, formatDatetime);
	  }
	  static parseDate1(str, format, trimSeconds) {
	    let i,
	      cnt,
	      k,
	      regMonths;
	    if (!format) format = main_core.Loc.getMessage('FORMAT_DATETIME');
	    str = BX.util.trim(str);
	    if (trimSeconds !== false) format = format.replace(':SS', '');
	    if (BX.type.isNotEmptyString(str)) {
	      regMonths = '';
	      for (i = 1; i <= 12; i++) {
	        regMonths = regMonths + '|' + main_core.Loc.getMessage('MON_' + i);
	      }
	      let expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig'),
	        aDate = str.match(expr),
	        aFormat = main_core.Loc.getMessage('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
	        aDateArgs = [],
	        aFormatArgs = [],
	        aResult = {};
	      if (!aDate) {
	        return null;
	      }
	      if (aDate.length > aFormat.length) {
	        aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
	      }
	      for (i = 0, cnt = aDate.length; i < cnt; i++) {
	        if (BX.util.trim(aDate[i]) !== '') {
	          aDateArgs[aDateArgs.length] = aDate[i];
	        }
	      }
	      for (i = 0, cnt = aFormat.length; i < cnt; i++) {
	        if (BX.util.trim(aFormat[i]) != '') {
	          aFormatArgs[aFormatArgs.length] = aFormat[i];
	        }
	      }
	      let m = BX.util.array_search('MMMM', aFormatArgs);
	      if (m > 0) {
	        aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
	        aFormatArgs[m] = "MM";
	      } else {
	        m = BX.util.array_search('M', aFormatArgs);
	        if (m > 0) {
	          aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
	          aFormatArgs[m] = "MM";
	        }
	      }
	      for (i = 0, cnt = aFormatArgs.length; i < cnt; i++) {
	        k = aFormatArgs[i].toUpperCase();
	        aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
	      }
	      if (aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0) {
	        let d = new Date();
	        {
	          d.setDate(1);
	          d.setFullYear(aResult['YYYY']);
	          d.setMonth(aResult['MM'] - 1);
	          d.setDate(aResult['DD']);
	          d.setHours(0, 0, 0);
	        }
	        if ((!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G'])) && !isNaN(aResult['MI'])) {
	          if (!isNaN(aResult['H']) || !isNaN(aResult['G'])) {
	            let bPM = (aResult['T'] || aResult['TT'] || 'am').toUpperCase() == 'PM';
	            let h = parseInt(aResult['H'] || aResult['G'] || 0, 10);
	            if (bPM) {
	              aResult['HH'] = h + (h == 12 ? 0 : 12);
	            } else {
	              aResult['HH'] = h < 12 ? h : 0;
	            }
	          } else {
	            aResult['HH'] = parseInt(aResult['HH'] || aResult['GG'] || 0, 10);
	          }
	          if (isNaN(aResult['SS'])) aResult['SS'] = 0;
	          {
	            d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
	          }
	        }
	        return d;
	      }
	    }
	    return null;
	  }
	  static formatTime(hours, minutes) {
	    let day;
	    if (main_core.Type.isDate(hours)) {
	      day = hours;
	    } else {
	      day = new Date();
	      day.setHours(hours, minutes, 0);
	    }
	    return BX.date.format(Util.getTimeFormatShort(), day.getTime() / 1000);
	  }
	  static formatDate(timestamp) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }
	    return BX.date.format(Util.getDateFormat(), timestamp / 1000);
	  }
	  static formatDateTime(timestamp) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }
	    return BX.date.format(Util.getDateTimeFormat(), timestamp / 1000);
	  }
	  static formatTimeInterval(from, to) {
	    const formattedFrom = main_date.DateTimeFormat.format(Util.getTimeFormatShort(), from.getTime() / 1000);
	    const formattedTo = main_date.DateTimeFormat.format(Util.getTimeFormatShort(), to.getTime() / 1000);
	    return `${formattedFrom} - ${formattedTo}`;
	  }
	  static formatDuration(diffMinutes) {
	    const hours = Math.floor(diffMinutes / 60);
	    const minutes = diffMinutes % 60;
	    let hint = main_date.DateTimeFormat.format('idiff', new Date().getTime() / 1000 - minutes * 60);
	    if (hours > 0) {
	      hint = main_date.DateTimeFormat.format('Hdiff', new Date().getTime() / 1000 - hours * 60 * 60);
	      if (minutes > 0) {
	        hint += ' ' + main_date.DateTimeFormat.format('idiff', new Date().getTime() / 1000 - minutes * 60);
	      }
	    }
	    return hint;
	  }
	  static formatDateUsable(date, showYear = true, showDayOfWeek = false) {
	    const lang = main_core.Loc.getMessage('LANGUAGE_ID');
	    let format = Util.getDateFormat();
	    if (lang === 'ru' || lang === 'ua') {
	      format = showDayOfWeek ? 'l, j F' : 'j F';
	      if (date.getFullYear && date.getFullYear() !== new Date().getFullYear() && showYear !== false) {
	        format += ' Y';
	      }
	    }
	    return BX.date.format([["today", "today"], ["tommorow", "tommorow"], ["yesterday", "yesterday"], ["", format]], date);
	  }
	  static formatDayMonthShortTime(timestamp) {
	    return main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('DAY_MONTH_FORMAT'), timestamp) + ' ' + main_date.DateTimeFormat.format(main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'), timestamp);
	  }
	  static getDayLength() {
	    if (!Util.DAY_LENGTH) {
	      Util.DAY_LENGTH = 86400000;
	    }
	    return Util.DAY_LENGTH;
	  }
	  static getDefaultColorList() {
	    return ['#86b100', '#0092cc', '#00afc7', '#e89b06', '#00b38c', '#de2b24', '#bd7ac9', '#838fa0', '#c3612c', '#e97090'];
	  }
	  static findTargetNode(node, parentCont) {
	    let res = false;
	    if (node) {
	      let prefix = 'data-bx-calendar',
	        i;
	      if (node.attributes && node.attributes.length) {
	        for (i = 0; i < node.attributes.length; i++) {
	          if (node.attributes[i].name && node.attributes[i].name.substr(0, prefix.length) === prefix) {
	            res = node;
	            break;
	          }
	        }
	      }
	      if (!res) {
	        res = BX.findParent(node, function (n) {
	          let j;
	          if (n.attributes && n.attributes.length) {
	            for (j = 0; j < n.attributes.length; j++) {
	              if (n.attributes[j].name && n.attributes[j].name.substr(0, prefix.length) === prefix) return true;
	            }
	          }
	          return false;
	        }, parentCont);
	      }
	    }
	    return res;
	  }
	  static getFollowedUserList(userId) {
	    return [];
	  }
	  static getWeekDayByInd(index) {
	    return ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][index];
	  }
	  static getIndByWeekDay(weekDay) {
	    return new Object({
	      SU: 0,
	      MO: 1,
	      TU: 2,
	      WE: 3,
	      TH: 4,
	      FR: 5,
	      SA: 6
	    })[weekDay];
	  }
	  static getWeekdaysLoc(isFull = false) {
	    const today = new Date();
	    const weekdays = [];
	    const dayLength = 24 * 60 * 60 * 1000;
	    const format = isFull ? 'l' : 'D';
	    for (let weekOffset = 0; weekOffset < 7; weekOffset++) {
	      weekdays[(today.getDay() + weekOffset) % 7] = main_date.DateTimeFormat.format(format, new Date(today.getTime() + dayLength * weekOffset));
	    }
	    return weekdays;
	  }
	  static getLoader(size, className) {
	    return main_core.Tag.render(_t || (_t = _`
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
	  static getDayCode(date) {
	    return date.getFullYear() + '-' + ("0" + ~~(date.getMonth() + 1)).substr(-2, 2) + '-' + ("0" + ~~date.getDate()).substr(-2, 2);
	  }
	  static getTextColor(color) {
	    if (!color) {
	      return false;
	    }
	    if (color.charAt(0) === "#") {
	      color = color.substring(1, 7);
	    }
	    let r = parseInt(color.substring(0, 2), 16),
	      g = parseInt(color.substring(2, 4), 16),
	      b = parseInt(color.substring(4, 6), 16),
	      light = (r * 0.8 + g + b * 0.2) / 510 * 100;
	    return light < 50;
	  }
	  static getKeyCode(key) {
	    if (!main_core.Type.isString(key)) {
	      return false;
	    }
	    let KEY_CODES = {
	      'backspace': 8,
	      'enter': 13,
	      'escape': 27,
	      'space': 32,
	      'delete': 46,
	      'left': 37,
	      'right': 39,
	      'up': 38,
	      'down': 40,
	      'z': 90,
	      'y': 89,
	      'shift': 16,
	      'ctrl': 17,
	      'alt': 18,
	      'cmd': 91,
	      // 93, 224, 17 Browser dependent
	      'cmdRight': 93,
	      // 93, 224, 17 Browser dependent?
	      'pageUp': 33,
	      'pageDown': 34,
	      'd': 68,
	      'w': 87,
	      'm': 77,
	      'a': 65
	    };
	    return KEY_CODES[key.toLowerCase()];
	  }
	  static isAnyModifierKeyPressed(event = window.event) {
	    if (event) {
	      return event.altKey || event.shiftKey || event.ctrlKey || event.metaKey;
	    }
	    return null;
	  }
	  static getUsableDateTime(timestamp, roundMin) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }
	    let r = (roundMin || 10) * 60 * 1000;
	    timestamp = Math.ceil(timestamp / r) * r;
	    return new Date(timestamp);
	  }
	  static showNotification(message, actions = null) {
	    if (main_core.Type.isString(message) && message !== '') {
	      BX.UI.Notification.Center.notify({
	        content: message,
	        actions: actions
	      });
	    }
	  }
	  static showFieldError(message, wrap, options) {
	    if (main_core.Type.isDomNode(wrap) && main_core.Type.isString(message) && message !== '') {
	      main_core.Dom.remove(wrap.querySelector('.ui-alert'));
	      let alert = new BX.UI.Alert({
	        color: BX.UI.Alert.Color.DANGER,
	        icon: BX.UI.Alert.Icon.DANGER,
	        text: message
	      });
	      let alertWrap = alert.getContainer();
	      wrap.appendChild(alertWrap);
	      return alertWrap;
	    }
	  }
	  static getDateFormat() {
	    if (!Util.DATE_FORMAT) {
	      Util.DATE_FORMAT = BX.Main.Date.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATE"));
	    }
	    return Util.DATE_FORMAT;
	  }
	  static setDayOfWeekMonthFormat(value) {
	    Util.dayOfWeekMonthFormat = value;
	  }
	  static getDayOfWeekMonthFormat() {
	    return Util.dayOfWeekMonthFormat || 'l, j F';
	  }
	  static setDayMonthFormat(value) {
	    Util.dayMonthFormat = value;
	  }
	  static getDayMonthFormat() {
	    return Util.dayMonthFormat || 'j F';
	  }
	  static setLongDateFormat(value) {
	    Util.longDateFormat = value;
	  }
	  static getLongDateFormat() {
	    return Util.longDateFormat || 'j F Y';
	  }
	  static getDateTimeFormat() {
	    if (!Util.DATETIME_FORMAT) {
	      Util.DATETIME_FORMAT = BX.Main.Date.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATETIME"));
	    }
	    return Util.DATETIME_FORMAT;
	  }
	  static getTimeFormat() {
	    if (!Util.TIME_FORMAT) {
	      if (main_core.Loc.getMessage("FORMAT_DATETIME").substr(0, main_core.Loc.getMessage("FORMAT_DATE").length) === main_core.Loc.getMessage("FORMAT_DATE")) {
	        Util.TIME_FORMAT = BX.util.trim(Util.getDateTimeFormat().substr(Util.getDateFormat().length));
	        Util.TIME_FORMAT_BX = BX.util.trim(main_core.Loc.getMessage("FORMAT_DATETIME").substr(main_core.Loc.getMessage("FORMAT_DATE").length));
	      } else {
	        Util.TIME_FORMAT_BX = BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS';
	        Util.TIME_FORMAT = BX.date.convertBitrixFormat(BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
	      }
	    }
	    return Util.TIME_FORMAT;
	  }
	  static getTimeFormatShort() {
	    if (!Util.TIME_FORMAT_SHORT) {
	      Util.TIME_FORMAT_SHORT = Util.getTimeFormat().replace(':s', '');
	      Util.TIME_FORMAT_SHORT_BX = Util.TIME_FORMAT_BX.replace(':SS', '');
	    }
	    return Util.TIME_FORMAT_SHORT;
	  }
	  static getCurrentUserId() {
	    if (!Util.currentUserId) {
	      Util.currentUserId = parseInt(main_core.Loc.getMessage('USER_ID'));
	    }
	    return Util.currentUserId;
	  }
	  static getTimeByInt(intValue) {
	    intValue = parseInt(intValue);
	    let h = Math.floor(intValue / 60);
	    return {
	      hour: h,
	      min: intValue - h * 60
	    };
	  }
	  static preventSelection(node) {
	    node.ondrag = BX.False;
	    node.ondragstart = BX.False;
	    node.onselectstart = BX.False;
	  }
	  static getBX() {
	    return window.top.BX || window.BX;
	  }
	  static closeAllPopups() {
	    if (main_popup.PopupManager.isAnyPopupShown()) {
	      for (let i = 0, length = main_popup.PopupManager._popups.length; i < length; i++) {
	        if (main_popup.PopupManager._popups[i] && main_popup.PopupManager._popups[i].isShown()) {
	          main_popup.PopupManager._popups[i].close();
	        }
	      }
	    }
	  }
	  static showConfirmPopup(action, message, options = {}) {
	    this.confirmPopup = new ui_dialogs_messagebox.MessageBox({
	      message: main_core.Tag.render(_t2 || (_t2 = _`
				<div class="calendar-list-slider-messagebox-text">
					${0}
				</div>
			`), message),
	      minHeight: 120,
	      minWidth: 280,
	      maxWidth: 300,
	      buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
	      onOk: () => {
	        var _this$confirmPopup;
	        action();
	        (_this$confirmPopup = this.confirmPopup) == null ? void 0 : _this$confirmPopup.close();
	      },
	      onCancel: () => {
	        var _this$confirmPopup2;
	        (_this$confirmPopup2 = this.confirmPopup) == null ? void 0 : _this$confirmPopup2.close();
	      },
	      popupOptions: {
	        events: {
	          onPopupClose: () => {
	            delete this.confirmPopup;
	          }
	        },
	        closeByEsc: false,
	        padding: 0,
	        contentPadding: 0,
	        animation: 'fading-slide'
	      },
	      ...options
	    });
	    this.confirmPopup.show();
	  }
	  static sendAnalyticLabel(label) {
	    BX.ajax.runAction('calendar.api.calendarajax.sendAnalyticsLabel', {
	      analyticsLabel: label
	    });
	  }
	  static setOptions(config, additionalParams) {
	    Util.config = config;
	    Util.additionalParams = additionalParams;
	  }
	  static setUserSettings(userSettings) {
	    Util.userSettings = userSettings;
	  }
	  static getUserSettings() {
	    return main_core.Type.isObjectLike(Util.userSettings) ? Util.userSettings : {};
	  }
	  static setCalendarContext(calendarContext) {
	    Util.calendarContext = calendarContext;
	  }
	  static getCalendarContext() {
	    return Util.calendarContext || null;
	  }
	  static getMeetingStatusList() {
	    return ['Y', 'N', 'Q', 'H'];
	  }
	  static getWorkTimeStart() {
	    let workTimeStartParsed = this.config.work_time_start.split('.');
	    if (workTimeStartParsed.length === 1) {
	      return workTimeStartParsed[0] + '.00';
	    }
	    return this.config.work_time_start;
	  }
	  static getWorkTimeEnd() {
	    let workTimeEndParsed = this.config.work_time_end.split('.');
	    if (workTimeEndParsed.length === 1) {
	      return workTimeEndParsed[0] + '.00';
	    }
	    return this.config.work_time_end;
	  }
	  static checkEmailLimitationPopup() {
	    const emailGuestAmount = Util.getEventWithEmailGuestAmount();
	    const emailGuestLimit = Util.getEventWithEmailGuestLimit();
	    return emailGuestLimit > 0 && (emailGuestAmount === 8 || emailGuestAmount === 4 || emailGuestAmount >= emailGuestLimit);
	  }
	  static isEventWithEmailGuestAllowed() {
	    return Util.getEventWithEmailGuestLimit() === -1 || Util.getEventWithEmailGuestAmount() < Util.getEventWithEmailGuestLimit();
	  }
	  static setEventWithEmailGuestAmount(value) {
	    Util.countEventWithEmailGuestAmount = value;
	  }
	  static setEventWithEmailGuestLimit(value) {
	    Util.eventWithEmailGuestLimit = value;
	  }
	  static getEventWithEmailGuestAmount() {
	    return Util.countEventWithEmailGuestAmount;
	  }
	  static getEventWithEmailGuestLimit() {
	    return Util.eventWithEmailGuestLimit;
	  }
	  static setCurrentView(calendarView = null) {
	    Util.currentCalendarView = calendarView;
	  }
	  static getCurrentView() {
	    return Util.currentCalendarView || null;
	  }
	  static adjustDateForTimezoneOffset(date, timezoneOffset = 0, fullDay = false) {
	    if (!main_core.Type.isDate(date)) throw new Error('Wrong type for date attribute. DateTime object expected.');
	    if (!parseInt(timezoneOffset) || fullDay === true) return date;
	    return new Date(date.getTime() - parseInt(timezoneOffset) * 1000);
	  }
	  static getFormattedTimezone(timeZone) {
	    const timezoneOffset = this.getTimeZoneOffset(timeZone);
	    if (timezoneOffset === 0) {
	      return '(UTC) ' + timeZone;
	    }
	    const prefix = timezoneOffset > 0 ? '-' : '+';
	    const hours = ('0' + Math.floor(Math.abs(timezoneOffset) / 60)).slice(-2);
	    const minutes = ('0' + Math.abs(timezoneOffset) % 60).slice(-2);
	    return '(UTC ' + prefix + hours + ':' + minutes + ') ' + timeZone;
	  }
	  static getTimezoneDateFromTimestampUTC(timestampUTC, timeZone) {
	    return new Date(timestampUTC + this.getTimeZoneOffset() * 60 * 1000 - this.getTimeZoneOffset(timeZone) * 60 * 1000);
	  }
	  static getTimeZoneOffset(timeZone = undefined, date = new Date()) {
	    let timeInTimezone;
	    try {
	      timeInTimezone = new Date(date.toLocaleString('en-US', {
	        timeZone
	      })).getTime();
	    } catch (e) {
	      return 0;
	    }
	    const timeInUTC = new Date(date.toLocaleString('en-US', {
	      timeZone: 'UTC'
	    })).getTime();
	    return parseInt((timeInUTC - timeInTimezone) / 60000);
	  }
	  static randomInt(min, max) {
	    return Math.round(min - 0.5 + Math.random() * (max - min + 1));
	  }
	  static getRandomColor() {
	    const defaultColors = Util.getDefaultColorList();
	    return defaultColors[Util.randomInt(0, defaultColors.length - 1)];
	  }
	  static setAccessNames(accessNames = {}) {
	    Util.accessNames = {};
	    for (let code in accessNames) {
	      if (accessNames.hasOwnProperty(code)) {
	        Util.setAccessName(code, accessNames[code]);
	      }
	    }
	  }
	  static getAccessName(code) {
	    return Util.accessNames[code] || code;
	  }
	  static setAccessName(code, name) {
	    Util.accessNames[code] = name;
	  }
	  static getRandomInt(numCount = 6) {
	    return Math.round(Math.random() * Math.pow(10, numCount));
	  }
	  static displayError(errors, reloadPage) {
	    if (main_core.Type.isArray(errors)) {
	      let errorMessage = '';
	      for (let i = 0; i < errors.length; i++) {
	        errorMessage += errors[i].message + "\n";
	      }
	      errors = errorMessage;
	    }
	    setTimeout(() => {
	      alert(errors || '[Bitrix Calendar] Request error');
	      if (reloadPage) {
	        location.reload();
	      }
	    }, 200);
	  }
	  static convertEntityToAccessCode(entity) {
	    if (main_core.Type.isObjectLike(entity)) {
	      if (entity.entityId === 'meta-user' && entity.id === 'all-users') {
	        return 'UA';
	      } else if (entity.entityId === 'user') {
	        return 'U' + entity.id;
	      } else if (entity.entityId === 'project') {
	        return 'SG' + entity.id + '_K'; // for all members of group
	      } else if (entity.entityId === 'department') {
	        return 'DR' + entity.id;
	      } else if (entity.entityId === 'group') {
	        return entity.id;
	      }
	    }
	  }
	  static extendPlannerWatches({
	    entries,
	    userId
	  }) {
	    entries.forEach(entry => {
	      if (entry.type === 'user' && parseInt(entry.id) !== parseInt(userId)) {
	        const tag = Util.PLANNER_PULL_TAG.replace('#USER_ID#', entry.id);
	        if (!Util.PLANNER_WATCH_LIST.includes(tag)) {
	          pull_client.PULL.extendWatch(tag);
	          Util.PLANNER_WATCH_LIST.push(tag);
	        }
	      }
	    });
	  }
	  static clearPlannerWatches() {
	    Util.PLANNER_WATCH_LIST.forEach(tag => {
	      pull_client.PULL.clearWatch(tag);
	    });
	    Util.PLANNER_WATCH_LIST = [];
	  }
	  static registerRequestId() {
	    const requestUid = BX.Calendar.Util.getRandomInt(8);
	    Util.REQUEST_ID_LIST.push(requestUid);
	    return requestUid;
	  }
	  static unregisterRequestId(requestUid) {
	    Util.REQUEST_ID_LIST = Util.REQUEST_ID_LIST.filter(uid => {
	      return uid !== requestUid;
	    });
	  }
	  static checkRequestId(requestUid) {
	    requestUid = parseInt(requestUid);
	    return !main_core.Type.isInteger(requestUid) || !Util.REQUEST_ID_LIST.includes(requestUid);
	  }
	  static initHintNode(hintNode) {
	    var _bx$UI;
	    const bx = Util.getBX();
	    if (main_core.Type.isElementNode(hintNode) && bx != null && (_bx$UI = bx.UI) != null && _bx$UI.Hint) {
	      var _bx$UI2, _bx$UI2$Hint;
	      if (bx != null && (_bx$UI2 = bx.UI) != null && (_bx$UI2$Hint = _bx$UI2.Hint) != null && _bx$UI2$Hint.popup) {
	        bx.UI.Hint.popup.destroy();
	        bx.UI.Hint.popup = null;
	        bx.UI.Hint.content = null;
	      }
	      bx.UI.Hint.initNode(hintNode);
	    }
	  }
	  static documentIsDisplayingNow() {
	    return !document.hidden;
	  }
	  static removeHash() {
	    if ("pushState" in history) {
	      history.pushState("", document.title, window.location.pathname + window.location.search);
	    } else {
	      // Prevent scrolling by storing the page's current scroll offset
	      let scrollV = document.body.scrollTop;
	      let scrollH = document.body.scrollLeft;
	      window.location.hash = "";
	      // Restore the scroll offset, should be flicker free
	      document.body.scrollTop = scrollV;
	      document.body.scrollLeft = scrollH;
	    }
	  }

	  // TODO: move to syncManager
	  static setIphoneConnectionStatus(value) {
	    Util.iphoneConnectionStatus = value;
	  }
	  static isIphoneConnected() {
	    return Util.iphoneConnectionStatus;
	  }
	  static setMacConnectionStatus(value) {
	    Util.macConnectionStatus = value;
	  }
	  static isMacConnected() {
	    return Util.macConnectionStatus;
	  }
	  static setIcloudConnectionStatus(value) {
	    Util.icloudConnectionStatus = value;
	  }
	  static isIcloudConnected() {
	    return Util.icloudConnectionStatus;
	  }
	  static setGoogleConnectionStatus(value) {
	    Util.googleConnectionStatus = value;
	  }
	  static isGoogleConnected() {
	    return Util.googleConnectionStatus;
	  }
	  static setIsSharingFeatureEnabled(value) {
	    Util.isSharingFeatureEnabled = value;
	  }
	  static checkSharingFeatureEnabled() {
	    return Util.isSharingFeatureEnabled;
	  }
	  static setSharingConfig(value) {
	    Util.sharingConfig = value;
	  }
	  static getSharingConfig() {
	    return Util.sharingConfig;
	  }
	  static downloadIcsFile(fileContent, fileName) {
	    const link = document.createElement('a');
	    link.href = "data:text/calendar," + encodeURI(fileContent);
	    link.download = fileName;
	    link.click();
	  }
	  static isMobileBrowser() {
	    return navigator.userAgent.toLowerCase().includes('iphone') || navigator.userAgent.toLowerCase().includes('ipad') || navigator.userAgent.toLowerCase().includes('android');
	  }
	}
	Util.PLANNER_PULL_TAG = 'calendar-planner-#USER_ID#';
	Util.PLANNER_WATCH_LIST = [];
	Util.REQUEST_ID_LIST = [];
	Util.accessNames = {};

	exports.Util = Util;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Main,BX.Main,BX,BX.UI.Dialogs));
//# sourceMappingURL=util.bundle.js.map
