this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_sectionmanager,calendar_util,main_core_events,calendar_compacteventform,ui_notification,calendar_roomsmanager,main_core) {
	'use strict';

	class EntryManager {
	  static getNewEntry(options) {
	    const newEntryData = {};
	    const dateTime = EntryManager.getNewEntryTime(new Date());
	    const userSettings = calendar_util.Util.getUserSettings();
	    const userId = calendar_util.Util.getCurrentUserId();
	    newEntryData.ID = null;
	    newEntryData.NAME = EntryManager.getNewEntryName();
	    newEntryData.dateFrom = dateTime.from;
	    newEntryData.dateTo = dateTime.to;
	    if (options.type === 'location') {
	      newEntryData.SECT_ID = calendar_roomsmanager.RoomsManager.getNewEntrySectionId(options.type, parseInt(options.ownerId));
	    } else {
	      newEntryData.SECT_ID = calendar_sectionmanager.SectionManager.getNewEntrySectionId(options.type, parseInt(options.ownerId));
	    }
	    newEntryData.REMIND = EntryManager.getNewEntryReminders();
	    newEntryData.attendeesEntityList = [{
	      entityId: 'user',
	      id: userId
	    }];
	    newEntryData.ATTENDEE_LIST = [{
	      id: calendar_util.Util.getCurrentUserId(),
	      status: "H"
	    }];
	    if (options.type === 'user' && userId !== options.ownerId) {
	      newEntryData.attendeesEntityList.push({
	        entityId: 'user',
	        id: options.ownerId
	      });
	      newEntryData.ATTENDEE_LIST = [{
	        id: options.ownerId,
	        status: "H"
	      }, {
	        id: calendar_util.Util.getCurrentUserId(),
	        status: "Y"
	      }];
	    } else if (options.type === 'group') {
	      newEntryData.attendeesEntityList.push({
	        entityId: 'project',
	        id: options.ownerId
	      });
	    }
	    newEntryData.TZ_FROM = userSettings.timezoneName || userSettings.timezoneDefaultName || '';
	    newEntryData.TZ_TO = userSettings.timezoneName || userSettings.timezoneDefaultName || '';
	    return new calendar_entry.Entry({
	      data: newEntryData
	    });
	  }
	  static getNewEntryTime(date, duration) {
	    date = calendar_util.Util.getUsableDateTime(date);
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      const displayedViewRange = calendarContext.getDisplayedViewRange();
	      if (main_core.Type.isDate(displayedViewRange == null ? void 0 : displayedViewRange.start)) {
	        const dateTime = date.getTime();
	        if (dateTime < displayedViewRange.start.getTime() || dateTime > displayedViewRange.end.getTime()) {
	          const startDate = new Date(displayedViewRange.start.getTime());
	          const workTime = calendarContext.util.getWorkTime();
	          startDate.setHours(workTime.start, 0, 0, 0);
	          date = calendar_util.Util.getUsableDateTime(startDate);
	        }
	      }
	    }
	    return {
	      from: date,
	      to: new Date(date.getTime() + (duration || 3600) * 1000)
	    };
	  }
	  static getNewEntryName() {
	    return EntryManager.newEntryName || '';
	  }
	  static setNewEntryName(newEntryName) {
	    EntryManager.newEntryName = newEntryName;
	  }
	  static showEditEntryNotification(entryId) {
	    calendar_util.Util.showNotification(main_core.Loc.getMessage('CALENDAR_SAVE_EVENT_NOTIFICATION'), [{
	      title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
	      events: {
	        click: function (event, balloon, action) {
	          EntryManager.openViewSlider(entryId);
	          balloon.close();
	        }
	      }
	    }]);
	  }
	  static showNewEntryNotification(entryId) {
	    calendar_util.Util.showNotification(main_core.Loc.getMessage('CALENDAR_NEW_EVENT_NOTIFICATION'), [{
	      title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
	      events: {
	        click: (event, balloon, action) => {
	          EntryManager.openViewSlider(entryId);
	          balloon.close();
	        }
	      }
	    }]);
	  }
	  static showDeleteEntryNotification(entry) {
	    if (entry && entry instanceof calendar_entry.Entry) {
	      BX.UI.Notification.Center.notify({
	        id: 'calendar' + entry.getUniqueId(),
	        content: main_core.Loc.getMessage('CALENDAR_DELETE_EVENT_NOTIFICATION'),
	        actions: [{
	          title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
	          events: {
	            click: (event, balloon, action) => {
	              entry.cancelDelete();
	              balloon.close();
	            }
	          }
	        }]
	      });
	    }
	  }
	  static showReleaseLocationNotification() {
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('CALENDAR_RELEASE_LOCATION_NOTIFICATION')
	    });
	  }
	  static closeDeleteNotificationBalloon(entry) {
	    if (entry && entry instanceof calendar_entry.Entry) {
	      const balloon = BX.UI.Notification.Center.getBalloonById('calendar' + entry.getUniqueId());
	      if (balloon) {
	        balloon.close();
	      }
	    }
	  }
	  static openEditSlider(options = {}) {
	    const bx = calendar_util.Util.getBX();
	    if (bx.Calendar && bx.Calendar.SliderLoader) {
	      new bx.Calendar.SliderLoader(options.entry ? 'EDIT' + options.entry.id : 'NEW', {
	        calendarContext: options.calendarContext,
	        entry: options.entry || null,
	        type: options.type,
	        isLocationCalendar: options.isLocationCalendar || false,
	        roomsManager: options.roomsManager || null,
	        locationAccess: options.locationAccess || false,
	        dayOfWeekMonthFormat: options.dayOfWeekMonthFormat || false,
	        locationCapacity: options.locationCapacity || 0,
	        ownerId: options.ownerId,
	        userId: options.userId,
	        formDataValue: options.formDataValue || null
	      }).show();
	    }
	  }
	  static openViewSlider(eventId = null, options = {}) {
	    if (!main_core.Type.isNull(eventId)) {
	      const bx = calendar_util.Util.getBX();
	      if (bx.Calendar && bx.Calendar.SliderLoader) {
	        new bx.Calendar.SliderLoader(eventId, {
	          entryDateFrom: options.from,
	          timezoneOffset: options.timezoneOffset,
	          dayOfWeekMonthFormat: options.dayOfWeekMonthFormat || false,
	          calendarContext: options.calendarContext || null,
	          link: options.link
	        }).show();
	      }
	    }
	  }
	  static deleteEntry(entry, calendarContext = null) {
	    if (entry instanceof calendar_entry.Entry) {
	      const slider = calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	      const beforeDeleteHandler = () => {
	        if (slider && slider.options.type === 'calendar:slider') {
	          calendar_util.Util.getBX().SidePanel.Instance.close();
	        }
	      };
	      main_core_events.EventEmitter.subscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);
	      const deleteHandler = () => {
	        const calendar = calendar_util.Util.getCalendarContext();
	        if (!calendar && !calendarContext) {
	          return calendar_util.Util.getBX().reload();
	        }
	        if (calendar) {
	          calendar.reload();
	        } else if (calendarContext) {
	          calendarContext.reload();
	        }
	        main_core_events.EventEmitter.unsubscribe('BX.Calendar.Entry:delete', deleteHandler);
	        main_core_events.EventEmitter.unsubscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);
	      };
	      main_core_events.EventEmitter.subscribe('BX.Calendar.Entry:delete', deleteHandler);
	      entry.delete();
	    }
	  }
	  static setMeetingStatus(entry, status, params = {}) {
	    return new Promise(resolve => {
	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }
	      params.recursionMode = params.recursionMode || false;
	      if (status === 'N' && !params.confirmed) {
	        if (entry.isRecursive()) {
	          this.showConfirmStatusDialog(entry, resolve);
	          return false;
	        }
	      }
	      BX.ajax.runAction('calendar.api.calendarajax.setMeetingStatus', {
	        data: {
	          entryId: entry.id,
	          entryParentId: entry.parentId,
	          status: status,
	          recursionMode: params.recursionMode,
	          currentDateFrom: calendar_util.Util.formatDate(entry.from)
	        }
	      }).then(response => {
	        BX.Event.EventEmitter.emit('BX.Calendar.Entry:onChangeMeetingStatus', new main_core.Event.BaseEvent({
	          data: {
	            entry: entry,
	            status: status,
	            recursionMode: params.recursionMode,
	            currentDateFrom: entry.from,
	            counters: response.data.counters
	          }
	        }));
	        if (entry instanceof calendar_entry.Entry) {
	          entry.setCurrentStatus(status);
	        }
	        resolve({
	          entry: entry,
	          status: status,
	          recursionMode: params.recursionMode,
	          currentDateFrom: entry.from
	        });
	      });
	    });
	  }
	  static showConfirmStatusDialog(entry, resolvePromiseCallback = null) {
	    if (!this.confirmDeclineDialog) {
	      this.confirmDeclineDialog = this.createConfirmStatusDialog();
	    }
	    this.confirmDeclineDialog.show();
	    this.confirmDeclineDialog.unsubscribeAll('onDecline');
	    this.confirmDeclineDialog.subscribe('onDecline', function (event) {
	      if (event && main_core.Type.isFunction(event.getData)) {
	        EntryManager.setMeetingStatus(entry, 'N', {
	          recursionMode: event.getData().recursionMode,
	          confirmed: true
	        }).then(() => {
	          if (main_core.Type.isFunction(resolvePromiseCallback)) {
	            resolvePromiseCallback();
	          }
	        });
	      }
	    });
	  }
	  static showConfirmEditDialog(options) {
	    if (!this.confirmEditDialog) {
	      this.confirmEditDialog = this.createConfirmEditDialog();
	    }
	    this.confirmEditDialog.show();
	    if (main_core.Type.isFunction(options.callback)) {
	      this.confirmEditDialog.unsubscribeAll('onEdit');
	      this.confirmEditDialog.subscribe('onEdit', event => {
	        if (event && main_core.Type.isFunction(event.getData)) {
	          options.callback(event.getData());
	        }
	      });
	    }
	  }
	  static showReInviteUsersDialog(options) {
	    if (!this.reinviteUsersDialog) {
	      this.reinviteUsersDialog = this.createReinviteUserDialog();
	    }
	    this.reinviteUsersDialog.show();
	    if (main_core.Type.isFunction(options.callback)) {
	      this.reinviteUsersDialog.unsubscribeAll('onSelect');
	      this.reinviteUsersDialog.subscribe('onSelect', function (event) {
	        if (event && main_core.Type.isFunction(event.getData)) {
	          options.callback(event.getData());
	        }
	      });
	    }
	  }
	  static showConfirmedEmailDialog(options = {}) {
	    if (!this.confirmedEmailDialog) {
	      this.confirmedEmailDialog = this.createConfirmedEmailDialog();
	    }
	    this.confirmedEmailDialog.show();
	    if (main_core.Type.isFunction(options.callback)) {
	      this.confirmedEmailDialog.unsubscribeAll('onSelect');
	      this.confirmedEmailDialog.subscribe('onSelect', function (event) {
	        if (event && main_core.Type.isFunction(event.getData)) {
	          options.callback(event.getData());
	        }
	      });
	    }
	  }
	  static showEmailLimitationDialog(options = {}) {
	    if (!this.limitationEmailDialog) {
	      this.limitationEmailDialog = this.createEmailLimitationDialog();
	    }
	    this.limitationEmailDialog.subscribe('onClose', () => {
	      if (main_core.Type.isFunction(options.callback)) {
	        options.callback();
	      }
	    });
	    this.limitationEmailDialog.show();
	  }
	  static getCompactViewForm(create = true) {
	    if (!EntryManager.compactEntryForm && create) {
	      EntryManager.compactEntryForm = new calendar_compacteventform.CompactEventForm();
	    }
	    return EntryManager.compactEntryForm;
	  }
	  static openCompactViewForm(options = {}) {
	    const compactForm = EntryManager.getCompactViewForm();
	    if (!compactForm.isShown()) {
	      compactForm.unsubscribeAll('onClose');
	      if (main_core.Type.isFunction(options.closeCallback)) {
	        compactForm.subscribe('onClose', options.closeCallback);
	      }
	      compactForm.showInViewMode(options);
	    }
	  }
	  static openCompactEditForm(options = {}) {
	    const compactForm = EntryManager.getCompactViewForm();
	    if (!compactForm.isShown()) {
	      compactForm.unsubscribeAll('onClose');
	      if (main_core.Type.isFunction(options.closeCallback)) {
	        compactForm.subscribe('onClose', options.closeCallback);
	      }
	      compactForm.showInEditMode(options);
	    }
	  }
	  static getEntryInstance(entry, userIndex, options = {}) {
	    let entryInstance = null;
	    if (entry instanceof calendar_entry.Entry) {
	      entryInstance = entry;
	    } else {
	      if (main_core.Type.isObject(entry) && main_core.Type.isObject(entry.data)) {
	        entryInstance = new calendar_entry.Entry({
	          data: entry.data,
	          userIndex: userIndex
	        });
	      } else if (main_core.Type.isObject(entry)) {
	        entryInstance = new calendar_entry.Entry({
	          data: entry,
	          userIndex: userIndex
	        });
	      } else {
	        entryInstance = EntryManager.getNewEntry(options);
	      }
	    }
	    return entryInstance;
	  }
	  static getUserIndex(options = {}) {
	    return EntryManager.userIndex;
	  }
	  static setUserIndex(userIndex) {
	    EntryManager.userIndex = userIndex;
	  }
	  handlePullChanges(params) {
	    var _params$fields6;
	    if (!BX.Calendar.Util.checkRequestId(params.requestUid)) {
	      return;
	    }
	    const compactForm = EntryManager.getCompactViewForm();
	    if (compactForm && compactForm.isShown()) {
	      compactForm.handlePull(params);
	    }
	    BX.SidePanel.Instance.getOpenSliders().forEach(slider => {
	      var _params$fields;
	      const data = EntryManager.slidersMap.get(slider);
	      if (data && data.entry && data.entry.parentId === parseInt(params == null ? void 0 : (_params$fields = params.fields) == null ? void 0 : _params$fields.PARENT_ID)) {
	        var _params$fields2;
	        if (params.command === 'delete_event' && data.entry.getType() === (params == null ? void 0 : (_params$fields2 = params.fields) == null ? void 0 : _params$fields2.CAL_TYPE)) {
	          slider.close();
	        }
	      }
	    });
	    if (params.command === 'set_meeting_status') {
	      top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
	    } else if (params.command === 'delete_event' || params.command === 'edit_event') {
	      var _params$fields3, _params$fields4, _params$fields5, _top$BX$Calendar, _top$BX$Calendar$Cont;
	      if (!params.fields || params != null && (_params$fields3 = params.fields) != null && _params$fields3.IS_MEETING && (params == null ? void 0 : (_params$fields4 = params.fields) == null ? void 0 : _params$fields4.MEETING_STATUS) === 'Q') {
	        top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
	      }
	      if ((params == null ? void 0 : (_params$fields5 = params.fields) == null ? void 0 : _params$fields5.CAL_TYPE) === 'location' && (_top$BX$Calendar = top.BX.Calendar) != null && (_top$BX$Calendar$Cont = _top$BX$Calendar.Controls) != null && _top$BX$Calendar$Cont.Location) {
	        top.BX.Calendar.Controls.Location.handlePull(params);
	      }
	    }
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    const entrySectionId = parseInt(params == null ? void 0 : (_params$fields6 = params.fields) == null ? void 0 : _params$fields6.SECTION_ID);
	    let sectionDisplayed = main_core.Type.isArray(params.sections) && params.sections.find(section => {
	      return section.id === entrySectionId && section.isShown();
	    });
	    let loadedEntry = params != null && params.fields ? EntryManager.getEntryInstance(calendarContext.getView().getEntryById(EntryManager.getEntryUniqueId(params.fields))) : null;
	    if ((sectionDisplayed || loadedEntry) && calendarContext) {
	      calendarContext.reloadDebounce();
	    }
	  }
	  static registerDeleteTimeout(params) {
	    EntryManager.delayedActionList.push(params);
	  }
	  static unregisterDeleteTimeout({
	    action,
	    data
	  }) {
	    EntryManager.delayedActionList = EntryManager.delayedActionList.filter(item => {
	      return item.action !== action || item.data.entryId !== data.entryId || item.data.recursionMode !== data.recursionMode || item.data.excludeDate !== data.excludeDate;
	    });
	  }
	  static doDelayedActions() {
	    let requestList = [];
	    return new Promise(resolve => {
	      if (!EntryManager.delayedActionList.length) {
	        resolve();
	      }
	      EntryManager.delayedActionList.forEach(({
	        action,
	        data,
	        params
	      }) => {
	        const requestUid = parseInt(data.requestUid);
	        requestList.push(data.requestUid);
	        if (params.entry) {
	          EntryManager.closeDeleteNotificationBalloon(params.entry);
	        }
	        BX.ajax.runAction(`calendar.api.calendarajax.${action}`, {
	          data: data
	        }).then(() => {
	          main_core.Type.isFunction(params.callback);
	          {
	            params.callback();
	          }
	          requestList = requestList.filter(uid => {
	            return uid !== requestUid;
	          });
	          if (!requestList.length) {
	            resolve();
	          }
	        }, () => {
	          requestList = requestList.filter(uid => {
	            return uid !== requestUid;
	          });
	          if (!requestList.length) {
	            resolve();
	          }
	        });
	        EntryManager.unregisterDeleteTimeout({
	          action,
	          data,
	          params
	        });
	      });
	    });
	  }
	  static getEntryUniqueId(entryData, entry) {
	    let sid = entryData.PARENT_ID || entryData.ID;
	    if (entryData.RRULE) {
	      sid += '|' + (entry ? calendar_util.Util.formatDate(entry.from) : calendar_util.Util.formatDate(BX.parseDate(entryData.DATE_FROM)));
	    }
	    if (entryData['~TYPE'] === 'tasks') {
	      sid += '|' + 'task';
	    }
	    return sid;
	  }
	  static registerEntrySlider(entry, control) {
	    const slider = calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	    if (slider) {
	      EntryManager.slidersMap.set(slider, {
	        entry,
	        control
	      });
	    }
	  }
	  static getNewEntryReminders(type = 'withTime') {
	    const userSettings = calendar_util.Util.getUserSettings();
	    if (main_core.Type.isObjectLike(userSettings.defaultReminders) && main_core.Type.isArray(userSettings.defaultReminders[type]) && userSettings.defaultReminders[type].length) {
	      return userSettings.defaultReminders[type];
	    }
	    return type === 'withTime' ? [{
	      type: 'min',
	      count: 15
	    }] : [{
	      type: 'daybefore',
	      before: 0,
	      time: 480
	    }];
	  }
	  static setNewEntryReminders(type = 'withTime', reminders) {
	    const userSettings = calendar_util.Util.getUserSettings();
	    if (main_core.Type.isObjectLike(userSettings.defaultReminders)) {
	      userSettings.defaultReminders[type] = reminders;
	    }
	    calendar_util.Util.setUserSettings(userSettings);
	  }

	  //this is because extensions cant be loaded in iframe with import
	  static createConfirmEditDialog() {
	    const bx = calendar_util.Util.getBX();
	    return new bx.Calendar.Controls.ConfirmEditDialog();
	  }
	  static createConfirmStatusDialog() {
	    const bx = calendar_util.Util.getBX();
	    return new bx.Calendar.Controls.ConfirmStatusDialog();
	  }
	  static createReinviteUserDialog() {
	    const bx = calendar_util.Util.getBX();
	    return new bx.Calendar.Controls.ReinviteUserDialog();
	  }
	  static createConfirmedEmailDialog() {
	    const bx = calendar_util.Util.getBX();
	    return new bx.Calendar.Controls.ConfirmedEmailDialog();
	  }
	  static createEmailLimitationDialog() {
	    const bx = calendar_util.Util.getBX();
	    return new bx.Calendar.Controls.EmailLimitationDialog();
	  }
	}
	EntryManager.newEntryName = '';
	EntryManager.userIndex = {};
	EntryManager.delayedActionList = [];
	EntryManager.DELETE_DELAY_TIMEOUT = 4000;
	EntryManager.slidersMap = new WeakMap();

	class Entry {
	  constructor(options = {}) {
	    this.FULL_DAY_LENGTH = 86400;
	    this.prepareData(options.data);
	    this.parts = [];
	    if (options.userIndex) {
	      this.setUserIndex(options.userIndex);
	    }
	    this.delayTimeoutMap = new Map();
	  }
	  prepareData(data) {
	    this.data = data;
	    this.id = parseInt(this.data.ID || 0);
	    this.parentId = parseInt(this.data.PARENT_ID || 0);
	    if (!this.data.DT_SKIP_TIME) {
	      this.data.DT_SKIP_TIME = this.data.SKIP_TIME ? 'Y' : 'N';
	    }
	    this.fullDay = this.data.DT_SKIP_TIME === 'Y';
	    this.accessibility = this.data.ACCESSIBILITY || 'busy';
	    this.important = this.data.IMPORTANCE === 'high';
	    this.private = !!this.data.PRIVATE_EVENT;
	    this.setSectionId(this.data.SECT_ID);
	    this.name = this.data.NAME;
	    this.userTimezoneOffsetFrom = parseInt(this.data['~USER_OFFSET_FROM']) || 0;
	    this.userTimezoneOffsetTo = parseInt(this.data['~USER_OFFSET_TO']) || this.userTimezoneOffsetFrom;
	    if (!this.data.DT_LENGTH) {
	      this.data.DT_LENGTH = this.data.DURATION || 0;
	    }
	    if (this.fullDay && !this.data.DT_LENGTH) {
	      this.data.DT_LENGTH = this.FULL_DAY_LENGTH;
	    }
	    if (!main_core.Type.isString(this.data.DATE_FROM) && !main_core.Type.isString(this.data.DATE_TO) && main_core.Type.isDate(this.data.dateFrom) && main_core.Type.isDate(this.data.dateTo)) {
	      this.from = this.data.dateFrom;
	      this.to = this.data.dateTo;
	      this.data.DT_LENGTH = Math.round((this.to.getTime() - this.from.getTime()) / 1000);
	      this.data.DURATION = this.data.DT_LENGTH;
	      if (this.fullDay) {
	        this.data.DATE_FROM = calendar_util.Util.formatDate(this.from.getTime());
	        this.data.DATE_TO = calendar_util.Util.formatDate(this.to.getTime());
	      } else {
	        this.from = new Date(this.from.getTime() - (parseInt(this.data['~USER_OFFSET_FROM']) || 0) * 1000);
	        this.to = new Date(this.to.getTime() - (parseInt(this.data['~USER_OFFSET_TO']) || 0) * 1000);
	      }
	    } else {
	      if (this.isTask()) {
	        this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
	        this.to = BX.parseDate(this.data.DATE_TO) || this.from;
	      } else {
	        this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
	        this.to = BX.parseDate(this.data.DATE_TO) || this.from;
	      }
	    }
	    if (this.fullDay) {
	      this.from.setHours(0, 0, 0, 0);
	      this.to.setHours(0, 0, 0, 0);
	    }
	    if (!this.data.ATTENDEES_CODES && !this.isTask()) {
	      if (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID) {
	        this.data.ATTENDEES_CODES = ['U' + this.data.OWNER_ID];
	      } else if (this.data.CREATED_BY) {
	        this.data.ATTENDEES_CODES = ['U' + this.data.CREATED_BY];
	      }
	    }
	    this.startDayCode = this.from;
	    this.endDayCode = this.to;
	    if (!main_core.Type.isArray(this.data.REMIND) && main_core.Type.isArray(this.data.remind)) {
	      this.data.REMIND = [];
	      this.data.remind.forEach(function (value) {
	        this.data.REMIND.push({
	          type: 'min',
	          count: value
	        });
	      }, this);
	      delete this.data.remind;
	    }
	    if (this.data.permissions) {
	      this.permissions = this.data.permissions;
	    }
	  }
	  getAttendeesCodes() {
	    return this.data.ATTENDEES_CODES || [];
	  }
	  getAttendeesEntityList() {
	    return this.data.attendeesEntityList || [];
	  }
	  getAttendees() {
	    if (!this.attendeeList) {
	      this.attendeeList = [];
	      if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	        const userIndex = this.getUserIndex();
	        this.data['ATTENDEE_LIST'].forEach(user => {
	          if (userIndex[user.id]) {
	            let attendee = BX.clone(userIndex[user.id]);
	            attendee.STATUS = user.status;
	            attendee.ENTRY_ID = user.entryId || false;
	            this.attendeeList.push(attendee);
	          }
	        });
	      }
	    }
	    return this.attendeeList;
	  }
	  setUserIndex(userIndex) {
	    this.userIndex = userIndex;
	  }
	  getUserIndex() {
	    return this.userIndex || EntryManager.getUserIndex();
	  }
	  cleanParts() {
	    this.parts = [];
	  }
	  startPart(part) {
	    part.partIndex = this.parts.length;
	    this.parts.push(part);
	    return this.parts[part.partIndex];
	  }
	  registerPartNode(part, params) {
	    part.params = params;
	  }
	  checkPartIsRegistered(part) {
	    return main_core.Type.isPlainObject(part.params);
	  }
	  getPart(partIndex) {
	    return this.parts[partIndex] || false;
	  }
	  getWrap(partIndex) {
	    return this.parts[partIndex || 0].params.wrapNode;
	  }
	  getSectionName() {
	    //return this.calendar.sectionController.getSection(this.sectionId).name || '';
	  }
	  getDescription() {
	    return this.data.DESCRIPTION || '';
	  }
	  applyViewRange(viewRange) {
	    let viewRangeStart = viewRange.start.getTime(),
	      viewRangeEnd = viewRange.end.getTime(),
	      fromTime = this.from.getTime(),
	      toTime = this.to.getTime();
	    if (toTime < viewRangeStart || fromTime > viewRangeEnd) return false;
	    if (fromTime < viewRangeStart) {
	      this.displayFrom = viewRange.start;
	      this.startDayCode = this.displayFrom;
	    }
	    if (toTime > viewRangeEnd) {
	      this.displayTo = viewRange.end;
	      this.endDayCode = this.displayTo;
	    }
	    return true;
	  }
	  isPersonal() {
	    //return (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID == this.calendar.util.userId);
	  }
	  isMeeting() {
	    return !!this.data.IS_MEETING;
	  }
	  isPrivate() {
	    return this.private;
	  }
	  isResourcebooking() {
	    return this.data.EVENT_TYPE === '#resourcebooking#';
	  }
	  isTask() {
	    return this.data['~TYPE'] === 'tasks';
	  }
	  isSharingEvent() {
	    return this.data['EVENT_TYPE'] === '#shared#' || this.data['EVENT_TYPE'] === '#shared_crm#';
	  }
	  isInvited() {
	    return this.getCurrentStatus() === 'Q';
	  }
	  isLocation() {
	    return this.getType() === 'location';
	  }
	  isFullDay() {
	    return this.fullDay;
	  }
	  isLongWithTime() {
	    return !this.fullDay && calendar_util.Util.getDayCode(this.from) !== calendar_util.Util.getDayCode(this.to);
	  }
	  isExpired() {
	    return this.to.getTime() < new Date().getTime();
	  }
	  hasEmailAttendees() {
	    if (this.emailAttendeesCache === undefined) {
	      const userIndex = EntryManager.getUserIndex();
	      for (let i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	        let user = this.data['ATTENDEE_LIST'][i];
	        if ((user.status === 'Y' || user.status === 'Q') && userIndex[user.id] && userIndex[user.id].EMAIL_USER) {
	          this.emailAttendeesCache = true;
	          break;
	        }
	      }
	    }
	    return this.emailAttendeesCache;
	  }
	  ownerIsEmailUser() {
	    if (this.ownerIsEmailUserCache === undefined) {
	      const userIndex = EntryManager.getUserIndex();
	      this.ownerIsEmailUserCache = userIndex[parseInt(this.data.MEETING_HOST)] && userIndex[parseInt(this.data.MEETING_HOST)].EMAIL_USER;
	    }
	    return this.ownerIsEmailUserCache;
	  }
	  isSelected() {
	    return !!this.selected;
	  }
	  isCrm() {
	    return !!this.data.UF_CRM_CAL_EVENT;
	  }
	  isFirstReccurentEntry() {
	    return (this.data.DATE_FROM_TS_UTC === Math.floor(BX.parseDate(this.data['~DATE_FROM']).getTime() / 1000) * 1000 || BX.parseDate(this.data['DATE_FROM']).getTime() === BX.parseDate(this.data['~DATE_FROM']).getTime()) && !this.data.RECURRENCE_ID;
	  }
	  isRecursive() {
	    return !!this.data.RRULE;
	  }
	  isFirstInstance() {
	    return this.data.RRULE && this.data.RINDEX === 0;
	  }
	  getMeetingHost() {
	    return parseInt(this.data.MEETING_HOST);
	  }
	  getMeetingNotify() {
	    return this.data.MEETING.NOTIFY;
	  }
	  getHideGuests() {
	    return this.data.MEETING && BX.Type.isBoolean(this.data.MEETING.HIDE_GUESTS) ? this.data.MEETING.HIDE_GUESTS : true;
	  }
	  getRrule() {
	    return this.data.RRULE;
	  }
	  getRRuleDescription() {
	    return this.data['~RRULE_DESCRIPTION'];
	  }
	  hasRecurrenceId() {
	    return this.data.RECURRENCE_ID;
	  }
	  wasEverRecursive() {
	    return this.data.RRULE || this.data.RECURRENCE_ID;
	  }
	  deselect() {
	    this.selected = false;
	  }
	  select() {
	    this.selected = true;
	  }
	  deleteParts(recursionMode) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      const wrap = calendarContext.getView().getContainer();
	      if (recursionMode === 'all') {
	        calendarContext.getView().entries.forEach(entry => {
	          if (parseInt(entry.id) === this.id || parseInt(entry.data.RECURRENCE_ID) === this.id || parseInt(entry.data.RECURRENCE_ID) === parseInt(this.data.RECURRENCE_ID) || parseInt(entry.id) === parseInt(this.data.RECURRENCE_ID)) {
	            const entryPart = wrap.querySelector('div[data-bx-calendar-entry="' + entry.uid + '"]');
	            if (entryPart) {
	              entryPart.style.opacity = 0;
	              setTimeout(() => {
	                entryPart.style.display = 'none';
	              }, 200);
	            }
	          }
	        });
	      } else if (recursionMode === 'next') {
	        calendarContext.getView().entries.forEach(entry => {
	          if ((parseInt(entry.id) === this.id || parseInt(entry.data.RECURRENCE_ID) === this.id || parseInt(entry.data.RECURRENCE_ID) === parseInt(this.data.RECURRENCE_ID) || parseInt(entry.id) === parseInt(this.data.RECURRENCE_ID)) && entry.from.getTime() > this.from.getTime()) {
	            const entryPart = wrap.querySelector('div[data-bx-calendar-entry="' + entry.uid + '"]');
	            if (entryPart) {
	              entryPart.style.opacity = 0;
	              setTimeout(() => {
	                entryPart.style.display = 'none';
	              }, 200);
	            }
	          }
	        });
	      } else if (recursionMode === 'this' || !recursionMode) {
	        const parts = wrap.querySelectorAll('div[data-bx-calendar-entry="' + this.getUniqueId() + '"]');
	        parts.forEach(entryPart => {
	          entryPart.style.opacity = 0;
	          setTimeout(() => {
	            entryPart.style.display = 'none';
	          }, 200);
	        });
	      }
	    }
	  }
	  getUniqueId() {
	    return EntryManager.getEntryUniqueId(this.data, this);
	  }
	  getCurrentStatus() {
	    let userId = calendar_util.Util.getCurrentUserId(),
	      status = false,
	      i,
	      user;
	    if (this.isMeeting()) {
	      if (userId === parseInt(this.data.CREATED_BY)) {
	        status = this.data.MEETING_STATUS || 'Q';
	      } else if (userId === parseInt(this.data.MEETING_HOST)) {
	        status = 'H';
	        //status = this.data.MEETING_STATUS || 'H';
	      } else if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	        for (i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	          user = this.data['ATTENDEE_LIST'][i];
	          if (parseInt(user.id) === userId) {
	            status = user.status;
	            break;
	          }
	        }
	      }
	    } else if (userId === parseInt(this.data.CREATED_BY)) {
	      status = this.data.MEETING_STATUS || 'H';
	    }
	    return calendar_util.Util.getMeetingStatusList().includes(status) ? status : false;
	  }
	  setCurrentStatus(status) {
	    if (this.isMeeting() && calendar_util.Util.getMeetingStatusList().includes(status)) {
	      this.data.MEETING_STATUS = status;
	      const userId = calendar_util.Util.getCurrentUserId();
	      if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	        for (let i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	          if (parseInt(this.data['ATTENDEE_LIST'][i].id) === userId) {
	            this.data['ATTENDEE_LIST'][i].status = status;
	            this.attendeeList = null;
	            break;
	          }
	        }
	      }
	    }
	  }
	  getReminders() {
	    let res = [];
	    if (this.data && this.data.REMIND) {
	      this.data.REMIND.forEach(function (remind) {
	        switch (remind.type) {
	          case 'min':
	            res.push(remind.count);
	            break;
	          case 'hour':
	            res.push(parseInt(remind.count) * 60);
	            break;
	          case 'day':
	            res.push(parseInt(remind.count) * 60 * 24);
	            break;
	          case 'daybefore':
	            res.push(remind);
	            break;
	          case 'date':
	            if (!main_core.Type.isDate(remind.value)) {
	              remind.value = calendar_util.Util.parseDate(remind.value);
	            }
	            if (main_core.Type.isDate(remind.value)) {
	              res.push(remind);
	            }
	            break;
	        }
	      });
	    }
	    return res;
	  }
	  getLengthInDays() {
	    let from = new Date(this.from.getFullYear(), this.from.getMonth(), this.from.getDate(), 0, 0, 0),
	      to = new Date(this.to.getFullYear(), this.to.getMonth(), this.to.getDate(), 0, 0, 0);
	    return Math.round((to.getTime() - from.getTime()) / calendar_util.Util.getDayLength()) + 1;
	  }
	  getName() {
	    return this.name || '';
	  }
	  getColor() {
	    return this.data.COLOR;
	  }
	  getType() {
	    return this.data.CAL_TYPE;
	  }
	  getOwnerId() {
	    return this.data.OWNER_ID;
	  }
	  delete(params) {
	    params = main_core.Type.isPlainObject(params) ? params : {};
	    let recursionMode = params.recursionMode || false;
	    if (this.wasEverRecursive() && !params.confirmed) {
	      return this.showConfirmDeleteDialog({
	        entry: this
	      });
	    } else {
	      // Broadcast event
	      BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	        entryId: this.id,
	        recursionMode: recursionMode,
	        entryData: this.data
	      }]);
	      EntryManager.showDeleteEntryNotification(this);
	      this.deleteParts(recursionMode);
	      const action = 'deleteCalendarEntry';
	      const data = {
	        entryId: this.id,
	        recursionMode: params.recursionMode || false,
	        requestUid: calendar_util.Util.registerRequestId()
	      };
	      EntryManager.registerDeleteTimeout({
	        action,
	        data,
	        params: {
	          entry: this,
	          callback: () => {
	            BX.onCustomEvent('BX.Calendar.Entry:delete', [{
	              entryId: this.id,
	              recursionMode: recursionMode
	            }]);
	          }
	        }
	      });
	      this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	      this.delayTimeoutMap.set(this.deleteTimeout, {
	        action,
	        data
	      });
	    }
	  }
	  deleteThis() {
	    let recursionMode = 'this';
	    if (this.isRecursive()) {
	      BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	        entryId: this.id,
	        recursionMode: recursionMode,
	        entryData: this.data
	      }]);
	      EntryManager.showDeleteEntryNotification(this);
	      this.deleteParts(recursionMode);
	      const action = 'excludeRecursionDate';
	      const data = {
	        entryId: this.id,
	        recursionMode: recursionMode,
	        excludeDate: this.data.DATE_FROM
	      };
	      EntryManager.registerDeleteTimeout({
	        action,
	        data,
	        params: {
	          entry: this,
	          callback: () => {
	            BX.onCustomEvent('BX.Calendar.Entry:delete', [data]);
	          }
	        }
	      });
	      this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	      this.delayTimeoutMap.set(this.deleteTimeout, {
	        action,
	        data
	      });
	    } else if (this.hasRecurrenceId()) {
	      this.delete({
	        confirmed: true,
	        recursionMode: 'this'
	      });
	    }
	  }
	  deleteNext() {
	    let recursionMode = 'next';
	    if (this.isRecursive() && this.isFirstReccurentEntry()) {
	      this.deleteAll();
	    } else {
	      BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	        entryId: this.id,
	        recursionMode: recursionMode
	      }]);
	      EntryManager.showDeleteEntryNotification(this);
	      this.deleteParts(recursionMode);
	      const calendarContext = calendar_util.Util.getCalendarContext();
	      const action = 'changeRecurciveEntryUntil';
	      const data = {
	        entryId: this.id,
	        recursionMode: recursionMode,
	        untilDate: calendar_util.Util.formatDate(this.from.getTime() - calendar_util.Util.getDayLength())
	      };
	      EntryManager.registerDeleteTimeout({
	        action,
	        data,
	        params: {
	          entry: this,
	          callback: () => {
	            BX.onCustomEvent('BX.Calendar.Entry:delete', [data]);
	          }
	        }
	      });
	      this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	      this.delayTimeoutMap.set(this.deleteTimeout, {
	        action,
	        data
	      });
	    }
	  }
	  deleteAll() {
	    return this.delete({
	      confirmed: true,
	      recursionMode: 'all'
	    });
	  }
	  cancelDelete() {
	    if (this.deleteTimeout) {
	      const deleteTimeoutData = this.delayTimeoutMap.get(this.deleteTimeout);
	      if (deleteTimeoutData) {
	        EntryManager.unregisterDeleteTimeout(deleteTimeoutData);
	        BX.onCustomEvent('BX.Calendar.Entry:cancelDelete', [{
	          entryId: this.id,
	          entryData: this.data
	        }]);
	        this.delayTimeoutMap.delete(this.delayTimeoutMap);
	      }
	      clearTimeout(this.deleteTimeout);
	      this.deleteTimeout = null;
	    }
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.reload();
	    }
	  }
	  showConfirmDeleteDialog(params) {
	    if (!this.confirmDeleteDialog) {
	      this.confirmDeleteDialog = new (window.BX || window.top.BX).Calendar.Controls.ConfirmDeleteDialog({
	        entry: params.entry
	      });
	    }
	    this.confirmDeleteDialog.show();
	  }
	  save() {}
	  getLocation() {
	    return this.data.LOCATION;
	  }
	  setTimezone(timezoneFrom, timezoneTo = null) {
	    if (main_core.Type.isString(timezoneFrom)) {
	      this.data.TZ_FROM = timezoneFrom;
	      if (main_core.Type.isNull(timezoneTo)) {
	        this.data.TZ_TO = timezoneFrom;
	      }
	    }
	    if (main_core.Type.isString(timezoneTo)) {
	      this.data.TZ_TO = timezoneTo;
	    }
	  }
	  getTimezoneFrom() {
	    return this.data.TZ_FROM || '';
	  }
	  getTimezoneTo() {
	    return this.data.TZ_TO || '';
	  }
	  setSectionId(value) {
	    this.data.SECT_ID = this.sectionId = this.isTask() ? 'tasks' : parseInt(value);
	  }
	  setDateTimeValue({
	    from,
	    to
	  }) {
	    if (main_core.Type.isDate(from) && main_core.Type.isDate(to)) {
	      this.from = this.data.dateFrom = from;
	      this.to = this.data.dateTo = to;
	      this.data.DT_LENGTH = Math.round((this.to.getTime() - this.from.getTime()) / 1000);
	      this.data.DURATION = this.data.DT_LENGTH;
	      if (this.fullDay) {
	        this.data.DATE_FROM = calendar_util.Util.formatDate(this.from.getTime());
	        this.data.DATE_TO = calendar_util.Util.formatDate(this.to.getTime());
	      } else {
	        this.data.DATE_FROM = calendar_util.Util.formatDateTime(this.from.getTime());
	        this.data.DATE_TO = calendar_util.Util.formatDateTime(this.to.getTime());
	      }
	    }
	  }
	}
	Entry.CAL_TYPES = {
	  'user': 'user',
	  'group': 'group',
	  'company': 'company_calendar'
	};

	exports.EntryManager = EntryManager;
	exports.Entry = Entry;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.Calendar,BX,BX.Calendar,BX));
//# sourceMappingURL=entry.bundle.js.map
