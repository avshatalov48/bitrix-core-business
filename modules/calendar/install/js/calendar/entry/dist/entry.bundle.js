this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_calendarsection,calendar_util,calendar_controls,calendar_compacteventform,main_core) {
	'use strict';

	var EntryManager =
	/*#__PURE__*/
	function () {
	  function EntryManager() {
	    babelHelpers.classCallCheck(this, EntryManager);
	  }

	  babelHelpers.createClass(EntryManager, null, [{
	    key: "getNewEntry",
	    value: function getNewEntry(options) {
	      var newEntryData = {};
	      var dateTime = EntryManager.getNewEntryTime(new Date());
	      newEntryData.ID = null;
	      newEntryData.NAME = EntryManager.getNewEntryName();
	      newEntryData.dateFrom = dateTime.from;
	      newEntryData.dateTo = dateTime.to;
	      newEntryData.SECT_ID = calendar_calendarsection.CalendarSectionManager.getNewEntrySectionId();
	      newEntryData.REMIND = [{
	        type: 'min',
	        count: 15
	      }];
	      newEntryData.ATTENDEES_CODES = ['U' + calendar_util.Util.getCurrentUserId()]; //newEntryData.TIMEZONE_FROM = userSettings.timezoneName || userSettings.timezoneDefaultName || null;

	      return new calendar_entry.Entry({
	        data: newEntryData
	      });
	    }
	  }, {
	    key: "getNewEntryTime",
	    value: function getNewEntryTime(date, duration) {
	      date = calendar_util.Util.getUsableDateTime(date);
	      return {
	        from: date,
	        to: new Date(date.getTime() + (duration || 3600) * 1000)
	      };
	    }
	  }, {
	    key: "getNewEntryName",
	    value: function getNewEntryName() {
	      return EntryManager.newEntryName || main_core.Loc.getMessage('CALENDAR_DEFAULT_ENTRY_NAME');
	    }
	  }, {
	    key: "setNewEntryName",
	    value: function setNewEntryName(newEntryName) {
	      EntryManager.newEntryName = newEntryName;
	    }
	  }, {
	    key: "showEditEntryNotification",
	    value: function showEditEntryNotification(entryId) {
	      calendar_util.Util.showNotification(main_core.Loc.getMessage('CALENDAR_SAVE_EVENT_NOTIFICATION'), [{
	        title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
	        events: {
	          click: function click(event, balloon, action) {
	            EntryManager.openViewSlider(entryId);
	            balloon.close();
	          }
	        }
	      }]);
	    }
	  }, {
	    key: "showNewEntryNotification",
	    value: function showNewEntryNotification(entryId) {
	      calendar_util.Util.showNotification(main_core.Loc.getMessage('CALENDAR_NEW_EVENT_NOTIFICATION'), [{
	        title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
	        events: {
	          click: function click(event, balloon, action) {
	            EntryManager.openViewSlider(entryId);
	            balloon.close();
	          }
	        }
	      }]);
	    }
	  }, {
	    key: "openEditSlider",
	    value: function openEditSlider() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var bx = calendar_util.Util.getBX();

	      if (bx.Calendar && bx.Calendar.SliderLoader) {
	        new bx.Calendar.SliderLoader(options.entry ? 'EDIT' + options.entry.id : 'NEW', {
	          entry: options.entry || null,
	          type: options.type,
	          ownerId: options.ownerId,
	          userId: options.userId
	        }).show();
	      }
	    }
	  }, {
	    key: "openViewSlider",
	    value: function openViewSlider() {
	      var eventId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (!main_core.Type.isNull(eventId)) {
	        var bx = calendar_util.Util.getBX();

	        if (bx.Calendar && bx.Calendar.SliderLoader) {
	          new bx.Calendar.SliderLoader(eventId, {
	            entryDateFrom: options.from,
	            timezoneOffset: options.timezoneOffset
	          }).show();
	        }
	      }
	    }
	  }, {
	    key: "deleteEntry",
	    value: function deleteEntry(entry) {
	      if (entry instanceof calendar_entry.Entry) {
	        BX.addCustomEvent('BX.Calendar.Entry:beforeDelete', function () {
	          if (calendar_util.Util.getBX().SidePanel.Instance) {
	            calendar_util.Util.getBX().SidePanel.Instance.close();
	          }
	        });
	        BX.addCustomEvent('BX.Calendar.Entry:delete', function () {
	          calendar_util.Util.getBX().reload();
	        });
	        entry.delete();
	      }
	    }
	  }, {
	    key: "setMeetingStatus",
	    value: function setMeetingStatus(entry, status) {
	      var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      if (!main_core.Type.isPlainObject(params)) {
	        params = {};
	      }

	      params.recursionMode = params.recursionMode || false;

	      if (status === 'N' && !params.confirmed) {
	        if (entry.isRecursive()) {
	          this.showConfirmStatusDialog(entry);
	          return false;
	        } else if (!confirm(main_core.Loc.getMessage('EC_DECLINE_MEETING_CONFIRM'))) {
	          return false;
	        }
	      }

	      BX.ajax.runAction('calendar.api.calendarajax.setMeetingStatus', {
	        data: {
	          entryId: entry.id,
	          entryParentId: entry.parentId,
	          status: status,
	          recursionMode: params.recursionMode,
	          currentDateDrom: calendar_util.Util.formatDate(entry.from)
	        }
	      }).then(function (response) {
	        BX.Event.EventEmitter.emit('BX.Calendar.Entry:onChangeMeetingStatus', new main_core.Event.BaseEvent({
	          data: {
	            entry: entry,
	            status: status,
	            recursionMode: params.recursionMode,
	            currentDateDrom: entry.from
	          }
	        }));
	      }.bind(this));
	    }
	  }, {
	    key: "showConfirmStatusDialog",
	    value: function showConfirmStatusDialog(entry) {
	      if (!this.confirmDeclineDialog) {
	        this.confirmDeclineDialog = new calendar_controls.ConfirmStatusDialog();
	      }

	      this.confirmDeclineDialog.show();
	      this.confirmDeclineDialog.unsubscribeAll('onDecline');
	      this.confirmDeclineDialog.subscribe('onDecline', function (event) {
	        if (event instanceof main_core.Event.BaseEvent) {
	          EntryManager.setMeetingStatus(entry, 'N', {
	            recursionMode: event.getData().recursionMode,
	            confirmed: true
	          });
	        }
	      });
	    }
	  }, {
	    key: "showConfirmEditDialog",
	    value: function showConfirmEditDialog(options) {
	      if (!this.confirmEditDialog) {
	        this.confirmEditDialog = new calendar_controls.ConfirmEditDialog();
	      }

	      this.confirmEditDialog.show();

	      if (main_core.Type.isFunction(options.callback)) {
	        this.confirmEditDialog.unsubscribeAll('onEdit');
	        this.confirmEditDialog.subscribe('onEdit', function (event) {
	          if (event instanceof main_core.Event.BaseEvent) {
	            options.callback(event.getData());
	          }
	        });
	      }
	    }
	  }, {
	    key: "getCompactViewForm",
	    value: function getCompactViewForm() {
	      if (!EntryManager.compactEntryForm) {
	        EntryManager.compactEntryForm = new calendar_compacteventform.CompactEventForm();
	      }

	      return EntryManager.compactEntryForm;
	    }
	  }, {
	    key: "openCompactViewForm",
	    value: function openCompactViewForm() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      EntryManager.getCompactViewForm().showInViewMode(options);
	    }
	  }, {
	    key: "openCompactEditForm",
	    value: function openCompactEditForm() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      EntryManager.getCompactViewForm().showInEditMode(options);
	    }
	  }, {
	    key: "getEntryInstance",
	    value: function getEntryInstance(entry, userIndex) {
	      var entryInstance = null;

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
	          entryInstance = EntryManager.getNewEntry();
	        }
	      }

	      return entryInstance;
	    }
	  }]);
	  return EntryManager;
	}();
	babelHelpers.defineProperty(EntryManager, "newEntryName", '');

	var Entry =
	/*#__PURE__*/
	function () {
	  function Entry() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Entry);
	    this.prepareData(options.data);
	    this.parts = [];

	    if (options.userIndex) {
	      this.setUserIndex(options.userIndex);
	    } //this.uid = this.calendar.entryController.getUniqueId(data, this);

	  }

	  babelHelpers.createClass(Entry, [{
	    key: "prepareData",
	    value: function prepareData(data) {
	      this.data = data;
	      this.id = this.data.ID || 0;

	      if (!this.data.DT_SKIP_TIME) {
	        this.data.DT_SKIP_TIME = this.data.SKIP_TIME ? 'Y' : 'N';
	      }

	      this.fullDay = this.data.DT_SKIP_TIME === 'Y';
	      this.parentId = this.data.PARENT_ID || 0;
	      this.accessibility = this.data.ACCESSIBILITY || 'busy';
	      this.important = this.data.IMPORTANCE === 'high';
	      this.private = !!this.data.PRIVATE_EVENT;
	      this.setSectionId(this.data.SECT_ID);
	      this.name = this.data.NAME;

	      if (!this.data.DT_LENGTH) {
	        this.data.DT_LENGTH = this.data.DURATION || 0;
	      }

	      if (this.fullDay && !this.data.DT_LENGTH) {
	        this.data.DT_LENGTH = 86400;
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
	          this.data.DATE_FROM = calendar_util.Util.formatDateTime(this.from.getTime());
	          this.data.DATE_TO = calendar_util.Util.formatDateTime(this.to.getTime());
	        }
	      } else {
	        if (this.isTask()) {
	          this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
	          this.to = BX.parseDate(this.data.DATE_TO) || this.from;
	        } else {
	          this.from = BX.parseDate(this.data.DATE_FROM) || new Date(); // if (this.data.DT_SKIP_TIME !== "Y")
	          // {
	          // 	this.from = new Date(this.from.getTime() - (parseInt(this.data['~USER_OFFSET_FROM']) || 0) * 1000);
	          // }

	          this.to = new Date(this.from.getTime() + (this.data.DT_LENGTH - (this.fullDay ? 1 : 0)) * 1000);
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
	    }
	  }, {
	    key: "getAttendeesCodes",
	    value: function getAttendeesCodes() {
	      return this.data.ATTENDEES_CODES;
	    }
	  }, {
	    key: "getAttendees",
	    value: function getAttendees() {

	      if (!this.attendeeList && main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	        this.attendeeList = [];

	        var _userIndex = this.getUserIndex();

	        this.data['ATTENDEE_LIST'].forEach(function (user) {
	          if (_userIndex[user.id]) {
	            var attendee = BX.clone(_userIndex[user.id]);
	            attendee.STATUS = user.status;
	            attendee.ENTRY_ID = user.entryId;
	            this.attendeeList.push(attendee);
	          }
	        }, this);
	      }

	      return this.attendeeList || [];
	    }
	  }, {
	    key: "setUserIndex",
	    value: function setUserIndex(userIndex) {
	      this.userIndex = userIndex;
	    }
	  }, {
	    key: "getUserIndex",
	    value: function getUserIndex() {
	      //let userIndex = this.calendar.entryController.getUserIndex();
	      return this.userIndex;
	    }
	  }, {
	    key: "cleanParts",
	    value: function cleanParts() {
	      this.parts = [];
	    }
	  }, {
	    key: "startPart",
	    value: function startPart(part) {
	      part.partIndex = this.parts.length;
	      this.parts.push(part);
	      return this.parts[part.partIndex];
	    }
	  }, {
	    key: "registerPartNode",
	    value: function registerPartNode(part, params) {
	      part.params = params;
	    }
	  }, {
	    key: "checkPartIsRegistered",
	    value: function checkPartIsRegistered(part) {
	      return main_core.Type.isPlainObject(part.params);
	    }
	  }, {
	    key: "getPart",
	    value: function getPart(partIndex) {
	      return this.parts[partIndex] || false;
	    }
	  }, {
	    key: "getWrap",
	    value: function getWrap(partIndex) {
	      return this.parts[partIndex || 0].params.wrapNode;
	    }
	  }, {
	    key: "getSectionName",
	    value: function getSectionName() {//return this.calendar.sectionController.getSection(this.sectionId).name || '';
	    }
	  }, {
	    key: "getDescription",
	    value: function getDescription(callback) {
	      if (this.data.DESCRIPTION && this.data['~DESCRIPTION'] && main_core.Type.isFunction(callback)) {
	        setTimeout(function () {
	          callback(this.data['~DESCRIPTION']);
	        }.bind(this), 50);
	      }
	    }
	  }, {
	    key: "applyViewRange",
	    value: function applyViewRange(viewRange) {
	      var viewRangeStart = viewRange.start.getTime(),
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
	  }, {
	    key: "isPersonal",
	    value: function isPersonal() {//return (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID == this.calendar.util.userId);
	    }
	  }, {
	    key: "isMeeting",
	    value: function isMeeting() {
	      return !!this.data.IS_MEETING;
	    }
	  }, {
	    key: "isResourcebooking",
	    value: function isResourcebooking() {
	      return this.data.EVENT_TYPE === '#resourcebooking#';
	    }
	  }, {
	    key: "isTask",
	    value: function isTask() {
	      return this.data['~TYPE'] === 'tasks';
	    }
	  }, {
	    key: "isFullDay",
	    value: function isFullDay() {
	      return this.fullDay;
	    }
	  }, {
	    key: "isLongWithTime",
	    value: function isLongWithTime() {
	      return !this.fullDay && calendar_util.Util.getDayCode(this.from) !== calendar_util.Util.getDayCode(this.to);
	    }
	  }, {
	    key: "isExpired",
	    value: function isExpired() {
	      return this.to.getTime() < new Date().getTime();
	    }
	  }, {
	    key: "isExternal",
	    value: function isExternal() {
	      return false;
	    }
	  }, {
	    key: "isSelected",
	    value: function isSelected() {
	      return !!this.selected;
	    }
	  }, {
	    key: "isCrm",
	    value: function isCrm() {
	      return !!this.data.UF_CRM_CAL_EVENT;
	    }
	  }, {
	    key: "isFirstReccurentEntry",
	    value: function isFirstReccurentEntry() {
	      return (this.data.DATE_FROM_TS_UTC === Math.floor(BX.parseDate(this.data['~DATE_FROM']).getTime() / 1000) * 1000 || BX.parseDate(this.data['DATE_FROM']).getTime() === BX.parseDate(this.data['~DATE_FROM']).getTime()) && !this.data.RECURRENCE_ID;
	    }
	  }, {
	    key: "isRecursive",
	    value: function isRecursive() {
	      return !!this.data.RRULE;
	    }
	  }, {
	    key: "getMeetingHost",
	    value: function getMeetingHost() {
	      return parseInt(this.data.MEETING_HOST);
	    }
	  }, {
	    key: "getRrule",
	    value: function getRrule() {
	      return this.data.RRULE;
	    }
	  }, {
	    key: "hasRecurrenceId",
	    value: function hasRecurrenceId() {
	      return this.data.RECURRENCE_ID;
	    }
	  }, {
	    key: "wasEverRecursive",
	    value: function wasEverRecursive() {
	      return this.data.RRULE || this.data.RECURRENCE_ID;
	    }
	  }, {
	    key: "deselect",
	    value: function deselect() {
	      this.selected = false;
	    }
	  }, {
	    key: "select",
	    value: function select() {
	      this.selected = true;
	    }
	  }, {
	    key: "deleteParts",
	    value: function deleteParts() {
	      if (main_core.Type.isArray(this.parts)) {
	        this.parts.forEach(function (part) {
	          if (part.params) {
	            if (part.params.wrapNode) {
	              part.params.wrapNode.style.opacity = 0;
	            }
	          }
	        }, this);
	        setTimeout(function () {
	          this.parts.forEach(function (part) {
	            if (part.params) {
	              if (part.params.wrapNode) {
	                BX.remove(part.params.wrapNode);
	              }
	            }
	          }, this);
	        }.bind(this), 300);
	      }
	    }
	  }, {
	    key: "getUniqueId",
	    value: function getUniqueId() {
	      var sid = this.data.PARENT_ID || this.data.PARENT_ID;
	      if (this.isRecursive()) sid += '|' + this.data.DT_FROM_TS;
	      if (this.data['~TYPE'] === 'tasks') sid += '|' + 'task';
	      return sid;
	    }
	  }, {
	    key: "getCurrentStatus",
	    value: function getCurrentStatus() {
	      var userId = calendar_util.Util.getCurrentUserId(),
	          status = false,
	          i,
	          user;

	      if (this.isMeeting()) {
	        if (userId === parseInt(this.data.CREATED_BY) || userId === parseInt(this.data.MEETING_HOST)) {
	          status = this.data.MEETING_STATUS;
	        } else if (userId === parseInt(this.data.MEETING_HOST)) {
	          status = this.data.MEETING_STATUS;
	        } else if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	          for (i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	            user = this.data['ATTENDEE_LIST'][i];

	            if (parseInt(this.data['ATTENDEE_LIST'][i].id) === userId) {
	              status = this.data['ATTENDEE_LIST'][i].status;
	              break;
	            }
	          }
	        }
	      }

	      return status || 'Q';
	    }
	  }, {
	    key: "getReminders",
	    value: function getReminders() {
	      var res = [];

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
	  }, {
	    key: "getLengthInDays",
	    value: function getLengthInDays() {
	      var from = new Date(this.from.getFullYear(), this.from.getMonth(), this.from.getDate(), 0, 0, 0),
	          to = new Date(this.to.getFullYear(), this.to.getMonth(), this.to.getDate(), 0, 0, 0);
	      return Math.round((to.getTime() - from.getTime()) / calendar_util.Util.getDayLength()) + 1;
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return this.name || this.defaultNewName;
	    }
	  }, {
	    key: "getColor",
	    value: function getColor() {
	      return this.data.COLOR;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.data.CAL_TYPE;
	    }
	  }, {
	    key: "getOwnerId",
	    value: function getOwnerId() {
	      return this.data.OWNER_ID;
	    }
	  }, {
	    key: "delete",
	    value: function _delete(params) {
	      params = main_core.Type.isPlainObject(params) ? params : {};
	      var recursionMode = params.recursionMode || false;

	      if (this.wasEverRecursive() && !params.confirmed) {
	        return this.showConfirmDeleteDialog({
	          entry: this
	        });
	      } else {
	        if (!params.confirmed && !confirm(BX.message('EC_DELETE_EVENT_CONFIRM'))) {
	          return false;
	        } // Broadcast event


	        BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	          entryId: this.id,
	          recursionMode: recursionMode
	        }]);
	        this.deleteParts();
	        BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarEntry', {
	          data: {
	            entryId: this.id,
	            recursionMode: params.recursionMode || false
	          }
	        }).then(function (response) {
	          BX.onCustomEvent('BX.Calendar.Entry:delete', [{
	            entryId: this.id,
	            recursionMode: recursionMode
	          }]);
	        }.bind(this));
	      }
	    }
	  }, {
	    key: "deleteThis",
	    value: function deleteThis() {
	      var recursionMode = 'this';

	      if (this.isRecursive()) {
	        BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	          entryId: this.id,
	          recursionMode: recursionMode
	        }]);
	        BX.ajax.runAction('calendar.api.calendarajax.excludeRecursionDate', {
	          data: {
	            entryId: this.id,
	            excludeDate: this.data.DATE_FROM
	          }
	        }).then( // Success
	        function (response) {
	          BX.onCustomEvent('BX.Calendar.Entry:delete', [{
	            entryId: this.id,
	            recursionMode: recursionMode
	          }]);
	        }.bind(this));
	      } else if (this.hasRecurrenceId()) {
	        this.delete({
	          confirmed: true,
	          recursionMode: 'this'
	        });
	      }
	    }
	  }, {
	    key: "deleteNext",
	    value: function deleteNext() {
	      var recursionMode = 'next';

	      if (this.isRecursive() && this.isFirstReccurentEntry()) {
	        this.deleteAll();
	      } else {
	        BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	          entryId: this.id,
	          recursionMode: recursionMode
	        }]);
	        BX.ajax.runAction('calendar.api.calendarajax.changeRecurciveEntryUntil', {
	          data: {
	            entryId: this.id,
	            untilDate: calendar_util.Util.formatDate(this.from.getTime() - calendar_util.Util.getDayLength())
	          }
	        }).then( // Success
	        function (response) {
	          BX.onCustomEvent('BX.Calendar.Entry:delete', [{
	            entryId: this.id,
	            recursionMode: recursionMode
	          }]);
	        }.bind(this));
	      }
	    }
	  }, {
	    key: "deleteAll",
	    value: function deleteAll() {
	      return this.delete({
	        confirmed: true,
	        recursionMode: 'all'
	      });
	    }
	  }, {
	    key: "showConfirmDeleteDialog",
	    value: function showConfirmDeleteDialog(params) {
	      if (!this.confirmDeleteDialog) {
	        this.confirmDeleteDialog = new calendar_controls.ConfirmDeleteDialog({
	          entry: params.entry
	        });
	      }

	      this.confirmDeleteDialog.show();
	    }
	  }, {
	    key: "save",
	    value: function save() {}
	  }, {
	    key: "getLocation",
	    value: function getLocation() {
	      return this.data.LOCATION;
	    }
	  }, {
	    key: "setTimezone",
	    value: function setTimezone(timezoneFrom) {
	      var timezoneTo = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

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
	  }, {
	    key: "getTimezoneFrom",
	    value: function getTimezoneFrom() {
	      return this.data.TZ_FROM;
	    }
	  }, {
	    key: "getTimezoneTo",
	    value: function getTimezoneTo() {
	      return this.data.TZ_TO;
	    }
	  }, {
	    key: "setSectionId",
	    value: function setSectionId(value) {
	      this.data.SECT_ID = this.sectionId = this.isTask() ? 'tasks' : parseInt(value);
	    }
	  }]);
	  return Entry;
	}();

	exports.EntryManager = EntryManager;
	exports.Entry = Entry;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX.Calendar,BX.Calendar.Controls,BX.Calendar,BX));
//# sourceMappingURL=entry.bundle.js.map
