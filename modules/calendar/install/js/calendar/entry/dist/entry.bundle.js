this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_sectionmanager,calendar_util,main_core_events,calendar_controls,calendar_compacteventform,ui_notification,calendar_eventviewform,main_core) {
	'use strict';

	var EntryManager = /*#__PURE__*/function () {
	  function EntryManager() {
	    babelHelpers.classCallCheck(this, EntryManager);
	  }

	  babelHelpers.createClass(EntryManager, [{
	    key: "handlePullChanges",
	    value: function handlePullChanges(params) {
	      var compactForm = EntryManager.getCompactViewForm();

	      if (compactForm && compactForm.isShown()) {
	        var _compactForm$userPlan, _compactForm$userPlan2, _params$fields;

	        if (compactForm.userPlannerSelector && ((_compactForm$userPlan = compactForm.userPlannerSelector) === null || _compactForm$userPlan === void 0 ? void 0 : (_compactForm$userPlan2 = _compactForm$userPlan.planner) === null || _compactForm$userPlan2 === void 0 ? void 0 : _compactForm$userPlan2.isShown())) {
	          var _compactForm$userPlan3;

	          compactForm === null || compactForm === void 0 ? void 0 : (_compactForm$userPlan3 = compactForm.userPlannerSelector) === null || _compactForm$userPlan3 === void 0 ? void 0 : _compactForm$userPlan3.refreshPlannerState();
	        }

	        var entry = compactForm.getCurrentEntry();

	        if (!compactForm.isNewEntry() && entry && entry.parentId === parseInt(params === null || params === void 0 ? void 0 : (_params$fields = params.fields) === null || _params$fields === void 0 ? void 0 : _params$fields.PARENT_ID)) {
	          if (params.command === 'delete_event') {
	            compactForm.close();
	          } else {
	            var onEntryListReloadHandler = function onEntryListReloadHandler() {
	              if (compactForm) {
	                compactForm.reloadEntryData();
	              }

	              BX.Event.EventEmitter.unsubscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
	            };

	            BX.Event.EventEmitter.subscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
	          }
	        }
	      }

	      BX.SidePanel.Instance.getOpenSliders().forEach(function (slider) {
	        var _params$fields2;

	        var data = EntryManager.slidersMap.get(slider);

	        if (data && data.entry && data.entry.parentId === parseInt(params === null || params === void 0 ? void 0 : (_params$fields2 = params.fields) === null || _params$fields2 === void 0 ? void 0 : _params$fields2.PARENT_ID)) {
	          if (params.command === 'delete_event') {
	            slider.close();
	          } else if (data.control instanceof calendar_eventviewform.EventViewForm) {
	            data.control.reloadSlider(params);
	          }
	        }
	      });

	      if (params.command === 'set_meeting_status') {
	        top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
	      } else if (params.command === 'delete_event' || params.command === 'edit_event') {
	        var _params$fields3, _params$fields4;

	        if (!params.fields || (params === null || params === void 0 ? void 0 : (_params$fields3 = params.fields) === null || _params$fields3 === void 0 ? void 0 : _params$fields3.IS_MEETING) && (params === null || params === void 0 ? void 0 : (_params$fields4 = params.fields) === null || _params$fields4 === void 0 ? void 0 : _params$fields4.MEETING_STATUS) === 'Q') {
	          top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
	        }
	      }
	    }
	  }], [{
	    key: "getNewEntry",
	    value: function getNewEntry(options) {
	      var newEntryData = {};
	      var dateTime = EntryManager.getNewEntryTime(new Date());
	      var userSettings = calendar_util.Util.getUserSettings();
	      var userId = calendar_util.Util.getCurrentUserId();
	      newEntryData.ID = null;
	      newEntryData.NAME = EntryManager.getNewEntryName();
	      newEntryData.dateFrom = dateTime.from;
	      newEntryData.dateTo = dateTime.to;
	      newEntryData.SECT_ID = calendar_sectionmanager.SectionManager.getNewEntrySectionId(options.type, parseInt(options.ownerId));
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
	    key: "showDeleteEntryNotification",
	    value: function showDeleteEntryNotification(entry) {
	      if (entry && entry instanceof calendar_entry.Entry) {
	        BX.UI.Notification.Center.notify({
	          id: 'calendar' + entry.getUniqueId(),
	          content: main_core.Loc.getMessage('CALENDAR_DELETE_EVENT_NOTIFICATION'),
	          actions: [{
	            title: main_core.Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
	            events: {
	              click: function click(event, balloon, action) {
	                entry.cancelDelete();
	                balloon.close();
	              }
	            }
	          }]
	        });
	      }
	    }
	  }, {
	    key: "closeDeleteNotificationBalloon",
	    value: function closeDeleteNotificationBalloon(entry) {
	      if (entry && entry instanceof calendar_entry.Entry) {
	        var balloon = BX.UI.Notification.Center.getBalloonById('calendar' + entry.getUniqueId());

	        if (balloon) {
	          balloon.close();
	        }
	      }
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
	          userId: options.userId,
	          formDataValue: options.formDataValue || null
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
	        var beforeDeleteHandler = function beforeDeleteHandler() {
	          if (calendar_util.Util.getBX().SidePanel.Instance) {
	            calendar_util.Util.getBX().SidePanel.Instance.close();
	          }
	        };

	        main_core_events.EventEmitter.subscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);

	        var deleteHandler = function deleteHandler() {
	          var calendar = calendar_util.Util.getCalendarContext();

	          if (!calendar) {
	            return calendar_util.Util.getBX().reload();
	          }

	          calendar.reload();
	          main_core_events.EventEmitter.unsubscribe('BX.Calendar.Entry:delete', deleteHandler);
	          main_core_events.EventEmitter.unsubscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);
	        };

	        main_core_events.EventEmitter.subscribe('BX.Calendar.Entry:delete', deleteHandler);
	        entry.delete();
	      }
	    }
	  }, {
	    key: "setMeetingStatus",
	    value: function setMeetingStatus(entry, status) {
	      var _this = this;

	      var params = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      return new Promise(function (resolve) {
	        if (!main_core.Type.isPlainObject(params)) {
	          params = {};
	        }

	        params.recursionMode = params.recursionMode || false;

	        if (status === 'N' && !params.confirmed) {
	          if (entry.isRecursive()) {
	            _this.showConfirmStatusDialog(entry, resolve);

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
	        }).then(function (response) {
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
	  }, {
	    key: "showConfirmStatusDialog",
	    value: function showConfirmStatusDialog(entry) {
	      var resolvePromiseCallback = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

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
	          }).then(function () {
	            if (main_core.Type.isFunction(resolvePromiseCallback)) {
	              resolvePromiseCallback();
	            }
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
	    key: "showReInviteUsersDialog",
	    value: function showReInviteUsersDialog(options) {
	      if (!this.reinviteUsersDialog) {
	        this.reinviteUsersDialog = new calendar_controls.ReinviteUserDialog();
	      }

	      this.reinviteUsersDialog.show();

	      if (main_core.Type.isFunction(options.callback)) {
	        this.reinviteUsersDialog.unsubscribeAll('onSelect');
	        this.reinviteUsersDialog.subscribe('onSelect', function (event) {
	          if (event instanceof main_core.Event.BaseEvent) {
	            options.callback(event.getData());
	          }
	        });
	      }
	    }
	  }, {
	    key: "showConfirmedEmailDialog",
	    value: function showConfirmedEmailDialog() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (!this.confirmedEmailDialog) {
	        this.confirmedEmailDialog = new calendar_controls.ConfirmedEmailDialog();
	      }

	      this.confirmedEmailDialog.show();

	      if (main_core.Type.isFunction(options.callback)) {
	        this.confirmedEmailDialog.unsubscribeAll('onSelect');
	        this.confirmedEmailDialog.subscribe('onSelect', function (event) {
	          if (event instanceof main_core.Event.BaseEvent) {
	            options.callback(event.getData());
	          }
	        });
	      }
	    }
	  }, {
	    key: "showEmailLimitationDialog",
	    value: function showEmailLimitationDialog() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var confirmedEmailDialog = new calendar_controls.EmailLimitationDialog();
	      confirmedEmailDialog.subscribe('onClose', function () {
	        if (main_core.Type.isFunction(options.callback)) {
	          options.callback();
	        }
	      });
	      confirmedEmailDialog.show();
	    }
	  }, {
	    key: "getCompactViewForm",
	    value: function getCompactViewForm() {
	      var create = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

	      if (!EntryManager.compactEntryForm && create) {
	        EntryManager.compactEntryForm = new calendar_compacteventform.CompactEventForm();
	      }

	      return EntryManager.compactEntryForm;
	    }
	  }, {
	    key: "openCompactViewForm",
	    value: function openCompactViewForm() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var compactForm = EntryManager.getCompactViewForm();

	      if (!compactForm.isShown()) {
	        compactForm.unsubscribeAll('onClose');

	        if (main_core.Type.isFunction(options.closeCallback)) {
	          compactForm.subscribe('onClose', options.closeCallback);
	        }

	        compactForm.showInViewMode(options);
	      }
	    }
	  }, {
	    key: "openCompactEditForm",
	    value: function openCompactEditForm() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var compactForm = EntryManager.getCompactViewForm();

	      if (!compactForm.isShown()) {
	        compactForm.unsubscribeAll('onClose');

	        if (main_core.Type.isFunction(options.closeCallback)) {
	          compactForm.subscribe('onClose', options.closeCallback);
	        }

	        compactForm.showInEditMode(options);
	      }
	    }
	  }, {
	    key: "getEntryInstance",
	    value: function getEntryInstance(entry, userIndex) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
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
	          entryInstance = EntryManager.getNewEntry(options);
	        }
	      }

	      return entryInstance;
	    }
	  }, {
	    key: "getUserIndex",
	    value: function getUserIndex() {
	      return EntryManager.userIndex;
	    }
	  }, {
	    key: "setUserIndex",
	    value: function setUserIndex(userIndex) {
	      EntryManager.userIndex = userIndex;
	    }
	  }, {
	    key: "registerDeleteTimeout",
	    value: function registerDeleteTimeout(params) {
	      EntryManager.delayedActionList.push(params);
	    }
	  }, {
	    key: "unregisterDeleteTimeout",
	    value: function unregisterDeleteTimeout(_ref) {
	      var action = _ref.action,
	          data = _ref.data;
	      EntryManager.delayedActionList = EntryManager.delayedActionList.filter(function (item) {
	        return item.action !== action || item.data.entryId !== data.entryId || item.data.recursionMode !== data.recursionMode || item.data.excludeDate !== data.excludeDate;
	      });
	    }
	  }, {
	    key: "doDelayedActions",
	    value: function doDelayedActions() {
	      var requestList = [];
	      return new Promise(function (resolve) {
	        if (!EntryManager.delayedActionList.length) {
	          resolve();
	        }

	        EntryManager.delayedActionList.forEach(function (_ref2) {
	          var action = _ref2.action,
	              data = _ref2.data,
	              params = _ref2.params;
	          var requestUid = parseInt(data.requestUid);
	          requestList.push(data.requestUid);

	          if (params.entry) {
	            EntryManager.closeDeleteNotificationBalloon(params.entry);
	          }

	          BX.ajax.runAction("calendar.api.calendarajax.".concat(action), {
	            data: data
	          }).then(function () {
	            main_core.Type.isFunction(params.callback);
	            {
	              params.callback();
	            }
	            requestList = requestList.filter(function (uid) {
	              return uid !== requestUid;
	            });

	            if (!requestList.length) {
	              resolve();
	            }
	          }, function () {
	            requestList = requestList.filter(function (uid) {
	              return uid !== requestUid;
	            });

	            if (!requestList.length) {
	              resolve();
	            }
	          });
	          EntryManager.unregisterDeleteTimeout({
	            action: action,
	            data: data,
	            params: params
	          });
	        });
	      });
	    }
	  }, {
	    key: "getEntryUniqueId",
	    value: function getEntryUniqueId(entryData, entry) {
	      var sid = entryData.PARENT_ID || entryData.ID;

	      if (entryData.RRULE) {
	        sid += '|' + (entry ? calendar_util.Util.formatDate(entry.from) : calendar_util.Util.formatDate(BX.parseDate(entryData.DATE_FROM)));
	      }

	      if (entryData['~TYPE'] === 'tasks') {
	        sid += '|' + 'task';
	      }

	      return sid;
	    }
	  }, {
	    key: "registerEntrySlider",
	    value: function registerEntrySlider(entry, control) {
	      var slider = calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();

	      if (slider) {
	        EntryManager.slidersMap.set(slider, {
	          entry: entry,
	          control: control
	        });
	      }
	    }
	  }, {
	    key: "getNewEntryReminders",
	    value: function getNewEntryReminders() {
	      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'withTime';
	      var userSettings = calendar_util.Util.getUserSettings();

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
	  }, {
	    key: "setNewEntryReminders",
	    value: function setNewEntryReminders() {
	      var type = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'withTime';
	      var reminders = arguments.length > 1 ? arguments[1] : undefined;
	      var userSettings = calendar_util.Util.getUserSettings();

	      if (main_core.Type.isObjectLike(userSettings.defaultReminders) && reminders.length) {
	        userSettings.defaultReminders[type] = reminders;
	      }

	      calendar_util.Util.setUserSettings(userSettings);
	    }
	  }]);
	  return EntryManager;
	}();
	babelHelpers.defineProperty(EntryManager, "newEntryName", '');
	babelHelpers.defineProperty(EntryManager, "userIndex", {});
	babelHelpers.defineProperty(EntryManager, "delayedActionList", []);
	babelHelpers.defineProperty(EntryManager, "DELETE_DELAY_TIMEOUT", 4000);
	babelHelpers.defineProperty(EntryManager, "slidersMap", new WeakMap());

	var Entry = /*#__PURE__*/function () {
	  function Entry() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Entry);
	    babelHelpers.defineProperty(this, "FULL_DAY_LENGTH", 86400);
	    this.prepareData(options.data);
	    this.parts = [];

	    if (options.userIndex) {
	      this.setUserIndex(options.userIndex);
	    }

	    this.delayTimeoutMap = new Map();
	  }

	  babelHelpers.createClass(Entry, [{
	    key: "prepareData",
	    value: function prepareData(data) {
	      this.data = data; // this.id = this.data.ID || 0;

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
	          this.to = new Date(this.from.getTime() + (this.data.DT_LENGTH - (this.fullDay ? 1 : 0)) * 1000);
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
	      return this.data.ATTENDEES_CODES || [];
	    }
	  }, {
	    key: "getAttendeesEntityList",
	    value: function getAttendeesEntityList() {
	      return this.data.attendeesEntityList || [];
	    }
	  }, {
	    key: "getAttendees",
	    value: function getAttendees() {
	      var _this = this;

	      if (!this.attendeeList) {
	        this.attendeeList = [];

	        if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	          var userIndex = this.getUserIndex();
	          this.data['ATTENDEE_LIST'].forEach(function (user) {
	            if (userIndex[user.id]) {
	              var attendee = BX.clone(userIndex[user.id]);
	              attendee.STATUS = user.status;
	              attendee.ENTRY_ID = user.entryId || false;

	              _this.attendeeList.push(attendee);
	            }
	          });
	        }
	      }

	      return this.attendeeList;
	    }
	  }, {
	    key: "setUserIndex",
	    value: function setUserIndex(userIndex) {
	      this.userIndex = userIndex;
	    }
	  }, {
	    key: "getUserIndex",
	    value: function getUserIndex() {
	      return this.userIndex || EntryManager.getUserIndex();
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
	    value: function getDescription() {
	      return this.data.DESCRIPTION || '';
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
	    key: "hasEmailAttendees",
	    value: function hasEmailAttendees() {
	      if (this.emailAttendeesCache === undefined) {
	        var userIndex = EntryManager.getUserIndex();

	        for (var i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	          var user = this.data['ATTENDEE_LIST'][i];

	          if ((user.status === 'Y' || user.status === 'Q') && userIndex[user.id] && userIndex[user.id].EMAIL_USER) {
	            this.emailAttendeesCache = true;
	            break;
	          }
	        }
	      }

	      return this.emailAttendeesCache;
	    }
	  }, {
	    key: "ownerIsEmailUser",
	    value: function ownerIsEmailUser() {
	      if (this.ownerIsEmailUserCache === undefined) {
	        var userIndex = EntryManager.getUserIndex();
	        this.ownerIsEmailUserCache = userIndex[parseInt(this.data.MEETING_HOST)] && userIndex[parseInt(this.data.MEETING_HOST)].EMAIL_USER;
	      }

	      return this.ownerIsEmailUserCache;
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
	    key: "getMeetingNotify",
	    value: function getMeetingNotify() {
	      return this.data.MEETING.NOTIFY;
	    }
	  }, {
	    key: "getHideGuests",
	    value: function getHideGuests() {
	      return this.data.MEETING && BX.Type.isBoolean(this.data.MEETING.HIDE_GUESTS) ? this.data.MEETING.HIDE_GUESTS : true;
	    }
	  }, {
	    key: "getRrule",
	    value: function getRrule() {
	      return this.data.RRULE;
	    }
	  }, {
	    key: "getRRuleDescription",
	    value: function getRRuleDescription() {
	      return this.data['~RRULE_DESCRIPTION'];
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
	    value: function deleteParts(recursionMode) {
	      var _this2 = this;

	      var calendarContext = calendar_util.Util.getCalendarContext();

	      if (calendarContext) {
	        var wrap = calendarContext.getView().getContainer();

	        if (recursionMode === 'all') {
	          calendarContext.getView().entries.forEach(function (entry) {
	            if (parseInt(entry.id) === _this2.id || parseInt(entry.data.RECURRENCE_ID) === _this2.id || parseInt(entry.data.RECURRENCE_ID) === parseInt(_this2.data.RECURRENCE_ID) || parseInt(entry.id) === parseInt(_this2.data.RECURRENCE_ID)) {
	              var entryPart = wrap.querySelector('div[data-bx-calendar-entry="' + entry.uid + '"]');

	              if (entryPart) {
	                entryPart.style.opacity = 0;
	                setTimeout(function () {
	                  entryPart.style.display = 'none';
	                }, 200);
	              }
	            }
	          });
	        } else if (recursionMode === 'next') {
	          calendarContext.getView().entries.forEach(function (entry) {
	            if ((parseInt(entry.id) === _this2.id || parseInt(entry.data.RECURRENCE_ID) === _this2.id || parseInt(entry.data.RECURRENCE_ID) === parseInt(_this2.data.RECURRENCE_ID) || parseInt(entry.id) === parseInt(_this2.data.RECURRENCE_ID)) && entry.from.getTime() > _this2.from.getTime()) {
	              var entryPart = wrap.querySelector('div[data-bx-calendar-entry="' + entry.uid + '"]');

	              if (entryPart) {
	                entryPart.style.opacity = 0;
	                setTimeout(function () {
	                  entryPart.style.display = 'none';
	                }, 200);
	              }
	            }
	          });
	        } else if (recursionMode === 'this' || !recursionMode) {
	          var parts = wrap.querySelectorAll('div[data-bx-calendar-entry="' + this.getUniqueId() + '"]');
	          parts.forEach(function (entryPart) {
	            entryPart.style.opacity = 0;
	            setTimeout(function () {
	              entryPart.style.display = 'none';
	            }, 200);
	          });
	        }
	      }
	    }
	  }, {
	    key: "getUniqueId",
	    value: function getUniqueId() {
	      return EntryManager.getEntryUniqueId(this.data, this);
	    }
	  }, {
	    key: "getCurrentStatus",
	    value: function getCurrentStatus() {
	      var userId = calendar_util.Util.getCurrentUserId(),
	          status = false,
	          i,
	          user;

	      if (this.isMeeting()) {
	        if (userId === parseInt(this.data.CREATED_BY)) {
	          status = this.data.MEETING_STATUS || 'Q';
	        } else if (userId === parseInt(this.data.MEETING_HOST)) {
	          status = 'H'; //status = this.data.MEETING_STATUS || 'H';
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
	  }, {
	    key: "setCurrentStatus",
	    value: function setCurrentStatus(status) {
	      if (this.isMeeting() && calendar_util.Util.getMeetingStatusList().includes(status)) {
	        this.data.MEETING_STATUS = status;
	        var userId = calendar_util.Util.getCurrentUserId();

	        if (main_core.Type.isArray(this.data['ATTENDEE_LIST'])) {
	          for (var i = 0; i < this.data['ATTENDEE_LIST'].length; i++) {
	            if (parseInt(this.data['ATTENDEE_LIST'][i].id) === userId) {
	              this.data['ATTENDEE_LIST'][i].status = status;
	              this.attendeeList = null;
	              break;
	            }
	          }
	        }
	      }
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
	      var _this3 = this;

	      params = main_core.Type.isPlainObject(params) ? params : {};
	      var recursionMode = params.recursionMode || false;

	      if (this.wasEverRecursive() && !params.confirmed) {
	        return this.showConfirmDeleteDialog({
	          entry: this
	        });
	      } else {
	        // Broadcast event
	        BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{
	          entryId: this.id,
	          recursionMode: recursionMode
	        }]);
	        EntryManager.showDeleteEntryNotification(this);
	        this.deleteParts(recursionMode);
	        var action = 'deleteCalendarEntry';
	        var data = {
	          entryId: this.id,
	          recursionMode: params.recursionMode || false,
	          requestUid: calendar_util.Util.registerRequestId()
	        };
	        EntryManager.registerDeleteTimeout({
	          action: action,
	          data: data,
	          params: {
	            entry: this,
	            callback: function callback() {
	              BX.onCustomEvent('BX.Calendar.Entry:delete', [{
	                entryId: _this3.id,
	                recursionMode: recursionMode
	              }]);
	            }
	          }
	        });
	        this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	        this.delayTimeoutMap.set(this.deleteTimeout, {
	          action: action,
	          data: data
	        });
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
	        EntryManager.showDeleteEntryNotification(this);
	        this.deleteParts(recursionMode);
	        var action = 'excludeRecursionDate';
	        var data = {
	          entryId: this.id,
	          recursionMode: recursionMode,
	          excludeDate: this.data.DATE_FROM
	        };
	        EntryManager.registerDeleteTimeout({
	          action: action,
	          data: data,
	          params: {
	            entry: this,
	            callback: function callback() {
	              BX.onCustomEvent('BX.Calendar.Entry:delete', [data]);
	            }
	          }
	        });
	        this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	        this.delayTimeoutMap.set(this.deleteTimeout, {
	          action: action,
	          data: data
	        });
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
	        EntryManager.showDeleteEntryNotification(this);
	        this.deleteParts(recursionMode);
	        var calendarContext = calendar_util.Util.getCalendarContext();

	        var action = 'changeRecurciveEntryUntil';
	        var data = {
	          entryId: this.id,
	          recursionMode: recursionMode,
	          untilDate: calendar_util.Util.formatDate(this.from.getTime() - calendar_util.Util.getDayLength())
	        };
	        EntryManager.registerDeleteTimeout({
	          action: action,
	          data: data,
	          params: {
	            entry: this,
	            callback: function callback() {
	              BX.onCustomEvent('BX.Calendar.Entry:delete', [data]);
	            }
	          }
	        });
	        this.deleteTimeout = setTimeout(EntryManager.doDelayedActions, EntryManager.DELETE_DELAY_TIMEOUT);
	        this.delayTimeoutMap.set(this.deleteTimeout, {
	          action: action,
	          data: data
	        });
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
	    key: "cancelDelete",
	    value: function cancelDelete() {
	      if (this.deleteTimeout) {
	        var deleteTimeoutData = this.delayTimeoutMap.get(this.deleteTimeout);

	        if (deleteTimeoutData) {
	          EntryManager.unregisterDeleteTimeout(deleteTimeoutData);
	          this.delayTimeoutMap.delete(this.delayTimeoutMap);
	        }

	        clearTimeout(this.deleteTimeout);
	        this.deleteTimeout = null;
	      }

	      var calendarContext = calendar_util.Util.getCalendarContext();

	      if (calendarContext) {
	        calendarContext.reload();
	      }
	    }
	  }, {
	    key: "showConfirmDeleteDialog",
	    value: function showConfirmDeleteDialog(params) {
	      if (!this.confirmDeleteDialog) {
	        this.confirmDeleteDialog = new (window.BX || window.top.BX).Calendar.Controls.ConfirmDeleteDialog({
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
	      return this.data.TZ_FROM || '';
	    }
	  }, {
	    key: "getTimezoneTo",
	    value: function getTimezoneTo() {
	      return this.data.TZ_TO || '';
	    }
	  }, {
	    key: "setSectionId",
	    value: function setSectionId(value) {
	      this.data.SECT_ID = this.sectionId = this.isTask() ? 'tasks' : parseInt(value);
	    }
	  }, {
	    key: "setDateTimeValue",
	    value: function setDateTimeValue(_ref) {
	      var from = _ref.from,
	          to = _ref.to;

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
	  }]);
	  return Entry;
	}();

	exports.EntryManager = EntryManager;
	exports.Entry = Entry;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.Calendar.Controls,BX.Calendar,BX,BX.Calendar,BX));
//# sourceMappingURL=entry.bundle.js.map
