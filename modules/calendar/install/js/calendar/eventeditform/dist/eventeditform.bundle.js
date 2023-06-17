this.BX = this.BX || {};
(function (exports,main_core,calendar_controls,calendar_util,calendar_entry,calendar_sectionmanager,main_core_events,calendar_planner,ui_entitySelector,calendar_roomsmanager) {
	'use strict';

	class SliderDateTimeControl extends calendar_controls.DateTimeControl {
	  create() {
	    this.DOM.dateTimeWrap = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_container`);
	    this.DOM.fromDate = this.DOM.outerContent.querySelector(`#${this.UID}_date_from`);
	    this.DOM.toDate = this.DOM.outerContent.querySelector(`#${this.UID}_date_to`);
	    this.DOM.fromTime = this.DOM.outerContent.querySelector(`#${this.UID}_time_from`);
	    this.DOM.toTime = this.DOM.outerContent.querySelector(`#${this.UID}_time_to`);
	    this.fromTimeControl = new calendar_controls.TimeSelector({
	      input: this.DOM.fromTime,
	      onChangeCallback: this.handleTimeFromChange.bind(this)
	    });
	    this.toTimeControl = new calendar_controls.TimeSelector({
	      input: this.DOM.toTime,
	      onChangeCallback: this.handleTimeToChange.bind(this)
	    });
	    this.DOM.fullDay = this.DOM.outerContent.querySelector(`#${this.UID}_date_full_day`);
	    this.DOM.defTimezoneWrap = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default_wrap`);
	    this.DOM.defTimezone = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default`);
	    this.DOM.fromTz = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_from`);
	    this.DOM.toTz = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_to`);
	    this.DOM.tzButton = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_btn`);
	    this.DOM.tzOuterCont = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_wrap`);
	    this.DOM.tzCont = this.DOM.outerContent.querySelector(`#${this.UID}_timezone_inner_wrap`);
	    this.DOM.outerContent.querySelector(`#${this.UID}_timezone_hint`).title = main_core.Loc.getMessage('EC_EVENT_TZ_HINT');
	    this.DOM.outerContent.querySelector(`#${this.UID}_timezone_default_hint`).title = main_core.Loc.getMessage('EC_EVENT_TZ_DEF_HINT');
	    this.prepareModel();
	    this.bindEventHandlers();
	    if (BX.isAmPmMode()) {
	      this.DOM.fromTime.style.minWidth = '8em';
	      this.DOM.toTime.style.minWidth = '8em';
	    } else {
	      this.DOM.fromTime.style.minWidth = '6em';
	      this.DOM.toTime.style.minWidth = '6em';
	    }
	  }
	  prepareModel() {
	    main_core.Dom.adjust(this.DOM.fromDate, {
	      props: {
	        autocomplete: 'off'
	      }
	    });
	    main_core.Dom.adjust(this.DOM.toDate, {
	      props: {
	        autocomplete: 'off'
	      }
	    });
	    main_core.Dom.adjust(this.DOM.fromTime, {
	      props: {
	        autocomplete: 'off'
	      }
	    });
	    main_core.Dom.adjust(this.DOM.toTime, {
	      props: {
	        autocomplete: 'off'
	      }
	    });
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
	  _t8,
	  _t9,
	  _t10;
	class EventEditForm {
	  constructor(options = {}) {
	    var _Util$getCalendarCont;
	    this.DOM = {};
	    this.uid = null;
	    this.sliderId = "calendar:edit-entry-slider";
	    this.zIndex = 3100;
	    this.denyClose = false;
	    this.formType = 'slider_main';
	    this.STATE = {
	      READY: 1,
	      REQUEST: 2,
	      ERROR: 3
	    };
	    this.sections = [];
	    this.sectionIndex = {};
	    this.trackingUsersList = [];
	    this.userSettings = {};
	    this.prevUserList = [];
	    this.loadedAccessibilityData = {};
	    this.name = options.name || 'eventeditform';
	    this.type = options.type || 'user';
	    this.isLocationCalendar = options.isLocationCalendar || false;
	    this.locationAccess = options.locationAccess || false;
	    this.locationCapacity = options.locationCapacity || 0;
	    this.dayOfWeekMonthFormat = options.dayOfWeekMonthFormat || false;
	    this.roomsManager = options.roomsManager || null;
	    this.userId = options.userId || parseInt(main_core.Loc.getMessage('USER_ID'));
	    this.ownerId = options.ownerId;
	    this.entryId = parseInt(options.entryId) || null;
	    this.entry = options.entry || null;
	    this.formDataValue = options.formDataValue || {};
	    this.emitter = new main_core_events.EventEmitter();
	    this.emitter.setEventNamespace('BX.Calendar.EventEditForm');
	    this.BX = calendar_util.Util.getBX();
	    this.context = (_Util$getCalendarCont = calendar_util.Util.getCalendarContext()) != null ? _Util$getCalendarCont : options.calendarContext;
	    if (!calendar_util.Util.getCalendarContext()) {
	      calendar_util.Util.setCalendarContext(this.context);
	    }
	    this.formSettings = {
	      pinnedFields: {}
	    };
	    if (!this.ownerId && this.type === 'user') {
	      this.ownerId = this.userId;
	    }
	    if (main_core.Type.isDate(options.entryDateFrom) && !this.formDataValue.from) {
	      this.formDataValue.from = options.entryDateFrom;
	      this.formDataValue.to = new Date(options.entryDateFrom.getTime() + 3600);
	    }
	    this.participantsEntityList = main_core.Type.isArray(options.participantsEntityList) ? options.participantsEntityList : [];
	    this.participantsSelectorEntityList = main_core.Type.isArray(options.participantsSelectorEntityList) ? options.participantsSelectorEntityList : [];
	    if (options.entryName && !this.entryId) {
	      this.formDataValue.name = options.entryName;
	    }
	    if (options.entryDescription && !this.entryId) {
	      this.formDataValue.description = options.entryDescription;
	    }
	    this.refreshPlanner = main_core.Runtime.debounce(this.refreshPlannerState, 100, this);
	    this.state = this.STATE.READY;
	    this.sliderOnClose = this.hide.bind(this);
	    this.handlePullBind = this.handlePull.bind(this);
	    this.keyHandlerBind = this.keyHandler.bind(this);
	  }
	  initInSlider(slider, promiseResolve) {
	    this.sliderId = slider.getUrl();
	    this.BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.onLoadSlider.bind(this));
	    this.BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.sliderOnClose);
	    this.BX.addCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.destroy.bind(this));
	    this.setCurrentEntry(this.entry || null);
	    this.createContent(slider).then(function (html) {
	      if (main_core.Type.isFunction(promiseResolve)) {
	        promiseResolve(html);
	      }
	    }.bind(this));
	    this.opened = true;
	    this.bindEventHandlers();
	  }
	  show(params = {}) {
	    this.setCurrentEntry(params.entry);
	    if (params.formType) {
	      this.formType = params.formType;
	    }
	    this.BX.SidePanel.Instance.open(this.sliderId, {
	      contentCallback: this.createContent.bind(this),
	      label: {
	        text: main_core.Loc.getMessage('CALENDAR_EVENT'),
	        bgColor: "#55D0E0"
	      },
	      events: {
	        onClose: this.sliderOnClose,
	        onCloseComplete: this.destroy.bind(this),
	        onLoad: this.onLoadSlider.bind(this)
	      }
	    });
	    this.opened = true;
	    this.bindEventHandlers();
	  }
	  isOpened() {
	    return this.opened;
	  }
	  bindEventHandlers() {
	    main_core.Event.bind(document, 'keydown', this.keyHandlerBind);
	    main_core_events.EventEmitter.subscribe('onPullEvent-calendar', this.handlePullBind);

	    // region 'protection from closing slider by accident'
	    this.mouseUpNodeCheck = null;
	    main_core.Event.bind(document, 'mousedown', e => {
	      this.mousedownTarget = e.target || e.srcElement;
	    });
	    main_core.Event.bind(document, 'mouseup', e => {
	      let target = e.target || e.srcElement;
	      if (this.mousedownTarget !== target) {
	        this.mouseUpNodeCheck = false;
	      }
	      setTimeout(() => {
	        this.mouseUpNodeCheck = null;
	      }, 0);
	    });
	    // endregion

	    main_core_events.EventEmitter.subscribe('Calendar.LocationControl.onValueChange', () => {
	      if (this.locationBusyAlert) {
	        main_core.Dom.remove(this.locationBusyAlert);
	        this.locationBusyAlert = null;
	      }
	    });
	    this.BX.addCustomEvent(window, "onCalendarControlChildPopupShown", this.BX.proxy(this.denySliderClose, this));
	    this.BX.addCustomEvent(window, "onCalendarControlChildPopupClosed", this.BX.proxy(this.allowSliderClose, this));
	  }
	  onLoadSlider(event) {
	    this.slider = event.getSlider();
	    this.DOM.content = this.slider.layout.content;
	    this.sliderId = this.slider.getUrl();

	    // Used to execute javasctipt and attach CSS from ajax responce
	    this.BX.html(this.slider.layout.content, this.slider.getData().get("sliderContent"));
	    this.initControls(this.uid);
	    this.setFormValues();
	  }
	  close() {
	    if (!this.checkDenyClose()) {
	      this.state = this.STATE.READY;
	      this.BX.SidePanel.Instance.close();
	    }
	  }
	  save(options = {}) {
	    var _this$DOM$form, _this$repeatSelector;
	    if (this.state === this.STATE.REQUEST) {
	      return false;
	    }
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    if (!this.entry.id && this.hasExternalEmailUsers() && calendar_util.Util.checkEmailLimitationPopup() && !options.emailLimitationDialogShown) {
	      calendar_entry.EntryManager.showEmailLimitationDialog({
	        callback: () => {
	          options.emailLimitationDialogShown = true;
	          this.save(options);
	        }
	      });
	      return false;
	    }
	    if (!this.userSettings.sendFromEmail && this.hasExternalEmailUsers()) {
	      calendar_entry.EntryManager.showConfirmedEmailDialog({
	        callback: params => {
	          if (params.sendFromEmail) {
	            this.userSettings.sendFromEmail = params.sendFromEmail;
	          }
	          this.save(options);
	        }
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isRecursive() && !options.confirmed && this.getFormDataChanges(['section', 'notify']).length > 0) {
	      calendar_entry.EntryManager.showConfirmEditDialog({
	        callback: params => {
	          this.save({
	            recursionMode: this.entry.isFirstInstance() && params.recursionMode === 'next' ? 'all' : params.recursionMode,
	            confirmed: true
	          });
	        }
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isMeeting() && options.sendInvitesAgain === undefined && this.getFormDataChanges().includes('date&time') && this.entry.getAttendees().find(item => {
	      return item.STATUS === 'N';
	    })) {
	      calendar_entry.EntryManager.showReInviteUsersDialog({
	        callback: params => {
	          options.sendInvitesAgain = params.sendInvitesAgain;
	          this.save(options);
	        }
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isRecursive() && !options.confirmed && this.getFormDataChanges().includes('section')) {
	      options.recursionMode = this.entry.isFirstInstance() ? 'all' : 'next';
	    }
	    main_core.Dom.addClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	    main_core.Dom.addClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	    this.state = this.STATE.REQUEST;
	    this.DOM.form.id.value = this.entry.id || 0;

	    // Location
	    this.DOM.form.location.value = this.locationSelector.getTextValue();
	    if (this.editor) {
	      this.editor.SaveContent();
	    }
	    let section = this.getCurrentSection();
	    if (section) {
	      // Color
	      if (section.COLOR.toLowerCase() !== this.colorSelector.getValue().toLowerCase()) {
	        this.DOM.form.color.value = this.colorSelector.getValue();
	      }
	      // this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', parseInt(section.ID));
	    }

	    this.DOM.form.current_date_from.value = options.recursionMode ? calendar_util.Util.formatDate(this.entry.from) : '';
	    this.DOM.form.rec_edit_mode.value = options.recursionMode || '';
	    if (options.sendInvitesAgain !== undefined) {
	      this.DOM.form.appendChild(main_core.Tag.render(_t || (_t = _`<input name="sendInvitesAgain" type="hidden" value="${0}">`), options.sendInvitesAgain ? 'Y' : 'N'));
	    }
	    if (!this.DOM.form.requestUid) {
	      this.DOM.requestUid = this.DOM.form.appendChild(main_core.Tag.render(_t2 || (_t2 = _`<input name="requestUid" type="hidden">`)));
	    }
	    if (!this.DOM.form.meeting_host) {
	      this.DOM.meeting_host = this.DOM.form.appendChild(main_core.Tag.render(_t3 || (_t3 = _`<input type="hidden" name="meeting_host" value="${0}">`), this.entry.data.MEETING_HOST || '0'));
	    }
	    if (!this.DOM.form.chat_id) {
	      this.DOM.chat_id = this.DOM.form.appendChild(main_core.Tag.render(_t4 || (_t4 = _`<input type="hidden" name="chat_id" value="${0}">`), this.entry.data.MEETING ? this.entry.data.MEETING.CHAT_ID : 0));
	    }
	    this.DOM.requestUid.value = calendar_util.Util.registerRequestId();

	    // Save attendees from userSelector
	    const attendeesEntityList = this.getUserSelectorEntityList();
	    main_core.Dom.clean(this.DOM.userSelectorValueWarp);
	    attendeesEntityList.forEach((entity, index) => {
	      this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t5 || (_t5 = _`
				<input type="hidden" name="attendeesEntityList[${0}][entityId]" value="${0}">
			`), index, entity.entityId));
	      this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t6 || (_t6 = _`
				<input type="hidden" name="attendeesEntityList[${0}][id]" value="${0}">
			`), index, entity.id));
	    });
	    let checkCurrentUsersAccessibility = !this.entry.id || this.checkCurrentUsersAccessibility();
	    if (!checkCurrentUsersAccessibility && this.getFormDataChanges().includes('codes')) {
	      const previousAttendeesList = this.entry.getAttendeesEntityList();
	      attendeesEntityList.forEach(entity => {
	        if (!previousAttendeesList.find(item => {
	          return entity.entityId === item.entityId && parseInt(entity.id) === parseInt(item.id);
	        })) {
	          if (entity.entityId === 'user') {
	            this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t7 || (_t7 = _`
							<input type="hidden" name="newAttendeesList[]" value="${0}">
						`), parseInt(entity.id)));
	          } else {
	            checkCurrentUsersAccessibility = true;
	          }
	        }
	      });
	    }
	    this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t8 || (_t8 = _`
			<input type="hidden" name="checkCurrentUsersAccessibility" value="${0}">
		`), checkCurrentUsersAccessibility ? 'Y' : 'N'));
	    this.BX.ajax.runAction('calendar.api.calendarentryajax.editEntry', {
	      data: new FormData(this.DOM.form),
	      analyticsLabel: {
	        calendarAction: this.entry.id ? 'edit_event' : 'create_event',
	        formType: 'full',
	        emailGuests: this.hasExternalEmailUsers() ? 'Y' : 'N',
	        markView: calendar_util.Util.getCurrentView() || 'outside',
	        markCrm: (_this$DOM$form = this.DOM.form) != null && _this$DOM$form['UF_CRM_CAL_EVENT[]'] && this.DOM.form['UF_CRM_CAL_EVENT[]'].value ? 'Y' : 'N',
	        markRrule: (_this$repeatSelector = this.repeatSelector) == null ? void 0 : _this$repeatSelector.getType(),
	        markMeeting: this.entry.isMeeting() ? 'Y' : 'N',
	        markType: this.type
	      }
	    }).then(response => {
	      if (this.isLocationCalendar) {
	        this.roomsManager.unsetHiddenRoom(calendar_controls.Location.parseStringValue(this.DOM.form.location.value).room_id);
	      }

	      // unset section from hidden
	      const section = this.getCurrentSection();
	      if (section && this.context && this.context.sectionManager) {
	        this.unsetHiddenSection(section, this.context.sectionManager);
	      }
	      this.state = this.STATE.READY;
	      this.allowSliderClose();
	      this.close();
	      main_core.Dom.removeClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	      main_core.Dom.removeClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	      if (response.data.entryId) {
	        if (this.entry.id) {
	          calendar_entry.EntryManager.showEditEntryNotification(response.data.entryId);
	        } else {
	          calendar_entry.EntryManager.showNewEntryNotification(response.data.entryId);
	        }
	      }
	      if (response.data.countEventWithEmailGuestAmount) {
	        calendar_util.Util.setEventWithEmailGuestAmount(response.data.countEventWithEmailGuestAmount);
	      }
	      if (main_core.Type.isArray(response.data.eventList) && response.data.eventList.length && response.data.eventList[0].REMIND && response.data.eventList[0].REMIND.length) {
	        calendar_entry.EntryManager.setNewEntryReminders(response.data.eventList[0].DT_SKIP_TIME === 'Y' ? 'fullDay' : 'withTime', response.data.eventList[0].REMIND);
	      }
	      this.emitter.emit('onSave', new main_core_events.BaseEvent({
	        data: {
	          responseData: response.data,
	          options: options
	        }
	      }));
	      main_core_events.EventEmitter.emit('BX.Calendar:onEntrySave', new main_core_events.BaseEvent({
	        data: {
	          sliderId: this.sliderId,
	          responseData: response.data,
	          options: options
	        }
	      }));
	    }, response => {
	      main_core.Dom.removeClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	      main_core.Dom.removeClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	      if (response.data && main_core.Type.isPlainObject(response.data.busyUsersList)) {
	        this.handleBusyUsersError(response.data.busyUsersList);
	        let errors = [];
	        response.errors.forEach(error => {
	          if (error.code !== "edit_entry_user_busy") {
	            errors.push(error);
	          }
	        });
	        response.errors = errors;
	      }
	      if (response.errors && response.errors.length) {
	        this.showError(response.errors);
	      }
	      this.state = this.STATE.ERROR;
	    });
	    return true;
	  }
	  handleBusyUsersError(busyUsers) {
	    let users = [],
	      userIds = [];
	    for (let id in busyUsers) {
	      if (busyUsers.hasOwnProperty(id)) {
	        users.push(busyUsers[id]);
	        userIds.push(id);
	      }
	    }
	    this.busyUsersDialog = new calendar_controls.BusyUsersDialog();
	    this.busyUsersDialog.subscribe('onSaveWithout', () => {
	      this.DOM.form.exclude_users.value = userIds.join(',');
	      this.save();
	    });
	    this.busyUsersDialog.show({
	      users: users
	    });
	  }
	  clientSideCheck() {}
	  hide(event) {
	    if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	      if (this.checkDenyClose()) {
	        event.denyAction();
	      } else {
	        this.BX.removeCustomEvent("SidePanel.Slider::onClose", this.sliderOnClose);
	        if (this.attendeesSelector) {
	          this.attendeesSelector.closeAll();
	        }
	      }
	    }
	  }
	  destroy(event) {
	    if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	      this.BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{
	        plannerId: this.plannerId
	      }]);
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBind);
	      main_core_events.EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullBind);
	      this.BX.SidePanel.Instance.destroy(this.sliderId);
	      if (calendar_controls.Location) {
	        calendar_controls.Location.setCurrentCapacity(0);
	      }
	      calendar_util.Util.closeAllPopups();
	      this.planner = null;
	      this.opened = false;
	      calendar_util.Util.clearPlannerWatches();
	    }
	  }
	  createContent(slider) {
	    var _entry$data$CAL_TYPE, _entry$data$OWNER_ID;
	    let promise = new this.BX.Promise();
	    let entry = this.getCurrentEntry();
	    this.BX.ajax.runAction('calendar.api.calendarajax.getEditEventSlider', {
	      data: {
	        event_id: this.entryId || entry.id,
	        date_from: entry ? calendar_util.Util.formatDate(entry.from) : '',
	        form_type: this.formType,
	        type: (_entry$data$CAL_TYPE = entry.data['CAL_TYPE']) != null ? _entry$data$CAL_TYPE : this.type,
	        ownerId: (_entry$data$OWNER_ID = entry.data['OWNER_ID']) != null ? _entry$data$OWNER_ID : this.ownerId,
	        entityList: this.participantsEntityList
	      }
	    }).then(response => {
	      if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	        let html = this.BX.util.trim(response.data.html);
	        slider.getData().set("sliderContent", html);
	        let params = response.data.additionalParams;
	        this.updateEntryData(params.entry, {
	          userSettings: this.userSettings,
	          meetSection: params.meetSection
	        });
	        entry = this.getCurrentEntry();
	        this.uid = params.uniqueId;
	        this.editorId = params.editorId;
	        this.formSettings = this.getSettings(params.formSettings || []);
	        let attendeesEntityList = this.formDataValue.attendeesEntityList || params.attendeesEntityList || [];
	        if (!entry.id && this.participantsEntityList.length) {
	          attendeesEntityList = this.participantsEntityList;
	        }
	        if (main_core.Type.isArray(attendeesEntityList)) {
	          attendeesEntityList.forEach(item => {
	            if (item.entityId === 'user' && params.userIndex[item.id]) {
	              item.entityType = params.userIndex[item.id].EMAIL_USER ? 'email' : 'employee';
	            }
	          });
	        }
	        this.setUserSelectorEntityList(attendeesEntityList);
	        this.attendeesPreselectedItems = this.getUserSelectorEntityList().map(item => {
	          return [item.entityId, item.id];
	        });
	        this.setUserSettings(params.userSettings);
	        calendar_util.Util.setEventWithEmailGuestAmount(params.countEventWithEmailGuestAmount);
	        calendar_util.Util.setEventWithEmailGuestLimit(params.eventWithEmailGuestLimit);
	        this.handleSections(params.sections, params.trackingUsersList);
	        this.handleLocationData(params.locationFeatureEnabled, params.locationList, params.iblockMeetingRoomList);
	        this.locationAccess = params.locationAccess;
	        this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat;
	        this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;
	        if (this.planner && !this.plannerFeatureEnabled) {
	          this.planner.lock();
	        }
	        if (!entry.id && !entry.sectionId) {
	          this.setCurrentEntry();
	        }
	        if (this.userSettings.meetSection && this.type === 'user') {
	          calendar_sectionmanager.SectionManager.setNewEntrySectionId(this.userSettings.meetSection);
	        }
	        promise.fulfill(html);
	      }
	    }, response => {
	      //this.calendar.displayError(response.errors);
	    });
	    return promise;
	  }
	  initControls(uid) {
	    this.DOM.title = this.DOM.content.querySelector(`#${uid}_title`);
	    this.DOM.formWrap = this.DOM.content.querySelector(`#${uid}_form_wrap`);
	    this.DOM.form = this.DOM.content.querySelector(`#${uid}_form`);
	    this.DOM.buttonsWrap = this.DOM.content.querySelector('.calendar-form-buttons-fixed');
	    this.DOM.saveBtn = this.DOM.buttonsWrap.querySelector(`#${uid}_save`);
	    this.DOM.closeBtn = this.DOM.buttonsWrap.querySelector(`#${uid}_close`);
	    main_core.Event.bind(this.DOM.saveBtn, 'click', this.save.bind(this));
	    main_core.Event.bind(this.DOM.closeBtn, 'click', this.close.bind(this));
	    this.initFormFieldManager(uid);
	    this.initDateTimeControl(uid);
	    this.initNameControl(uid);
	    this.initEditorControl(uid);
	    this.initAttendeesControl();
	    this.initPlanner(uid);
	    this.initReminderControl(uid);
	    this.initSectionSelector(uid);
	    this.initLocationControl(uid);
	    this.initRepeatRuleControl(uid);
	    this.initColorControl(uid);
	    this.initCrmUfControl(uid);
	    this.initAdditionalControls(uid);
	    this.checkLastItemBorder();
	    if (this.DOM.buttonsWrap) {
	      BX.ZIndexManager.register(this.DOM.buttonsWrap);
	    }
	  }
	  updateEntryData(entryData, options = {}) {
	    if (this.entry instanceof calendar_entry.Entry) {
	      let userSettings = options.userSettings || {};
	      if (main_core.Type.isPlainObject(entryData)) {
	        this.entry.prepareData(entryData);
	      } else {
	        if (!this.entry.getTimezoneFrom() || this.entry.getTimezoneTo()) {
	          this.entry.setTimezone(userSettings.timezoneName || userSettings.timezoneDefaultName || null);
	        }
	      }
	      if (!this.entry.id && options.meetSection && this.type === calendar_entry.Entry.CAL_TYPES['user']) {
	        this.entry.setSectionId(options.meetSection);
	      }
	    }
	  }
	  handleSections(sections, trackingUsersList) {
	    this.sections = sections;
	    this.sectionIndex = {};
	    this.trackingUsersList = trackingUsersList || [];
	    if (main_core.Type.isArray(sections)) {
	      sections.forEach((value, ind) => {
	        this.sectionIndex[parseInt(value.ID)] = ind;
	      }, this);
	    }
	    const section = this.getCurrentSection();
	    if (this.entry.id) {
	      this.getSectionsForEditEvent(this.sections, section);
	    }
	  }
	  handleLocationData(locationFeatureEnabled, locationList, iblockMeetingRoomList) {
	    this.locationFeatureEnabled = !!locationFeatureEnabled;
	    this.locationList = main_core.Type.isArray(locationList) ? locationList.filter(locationItem => {
	      return locationItem.PERM.view_full;
	    }) : [];
	    this.iblockMeetingRoomList = iblockMeetingRoomList || [];
	    calendar_controls.Location.setLocationList(locationList);
	    calendar_controls.Location.setMeetingRoomList(iblockMeetingRoomList);
	  }
	  setUserSettings(userSettings) {
	    this.userSettings = userSettings;
	    calendar_util.Util.setUserSettings(userSettings);
	  }
	  setFormValues() {
	    var _this$repeatSelector2;
	    let entry = this.entry;

	    // Date time
	    this.dateTimeControl.setValue({
	      from: this.formDataValue.from || entry.from,
	      to: this.formDataValue.to || entry.to,
	      fullDay: main_core.Type.isBoolean(this.formDataValue.fullDay) ? this.formDataValue.fullDay : entry.fullDay,
	      timezoneFrom: entry.getTimezoneFrom() || '',
	      timezoneTo: entry.getTimezoneTo() || '',
	      timezoneName: this.userSettings.timezoneName
	    });
	    this.DOM.entryName.value = this.formDataValue.name || entry.getName();

	    // Section
	    const section = this.getCurrentSection();
	    if (this.formDataValue.section) {
	      entry.sectionId = parseInt(this.formDataValue.section);
	    }
	    this.DOM.sectionInput.value = this.getCurrentSectionId();
	    this.sectionSelector.updateValue();
	    if (!this.fieldIsPinned('section')) {
	      if (section['CAL_TYPE'] !== this.type || section['CAL_TYPE'] === this.type && parseInt(section['OWNER_ID']) !== this.ownerId) {
	        this.pinField('section');
	      }
	    }
	    if (this.isSyncSection(section) && entry.id) {
	      this.sectionSelector.setViewMode(true);
	    }

	    // Color
	    this.colorSelector.setValue(this.formDataValue.color || entry.getColor() || section.COLOR);

	    // Reminders
	    this.remindersControl.setValue(this.formDataValue.reminder || entry.getReminders(), true, false);

	    // Recursion
	    (_this$repeatSelector2 = this.repeatSelector) == null ? void 0 : _this$repeatSelector2.setValue(this.formDataValue.rrule || entry.getRrule());

	    // accessibility
	    if (this.DOM.accessibilityInput) {
	      this.DOM.accessibilityInput.value = entry.accessibility;
	    }

	    // Location
	    if (this.locationSelector) {
	      this.locationSelector.setValue(this.formDataValue.location || this.locationSelector.default || entry.getLocation(), false);
	      this.locationSelector.checkLocationAccessibility({
	        from: this.formDataValue.from || entry.from,
	        to: this.formDataValue.to || entry.to,
	        fullDay: main_core.Type.isBoolean(this.formDataValue.fullDay) ? this.formDataValue.fullDay : entry.fullDay,
	        currentEventId: this.entry.id
	      });
	    }
	    // Private
	    if (this.DOM.privateEventCheckbox) {
	      this.DOM.privateEventCheckbox.checked = entry.private;
	    }

	    // Importance
	    if (this.DOM.importantEventCheckbox) {
	      this.DOM.importantEventCheckbox.checked = entry.important;
	    }
	    if (this.DOM.form.meeting_notify) {
	      if (this.formDataValue.meetingNotify !== undefined) {
	        this.DOM.form.meeting_notify.checked = this.formDataValue.meetingNotify;
	      }
	      if (this.entry.data && this.entry.data.MEETING) {
	        this.DOM.form.meeting_notify.checked = this.entry.data.MEETING.NOTIFY;
	      } else {
	        this.DOM.form.meeting_notify.checked = true; // default value
	      }
	    }

	    if (this.DOM.form.hide_guests) {
	      if (this.formDataValue.hideGuests !== undefined) {
	        this.DOM.form.hide_guests.checked = this.formDataValue.hideGuests === 'Y';
	      } else if (this.entry.data && this.entry.data.MEETING) {
	        this.DOM.form.hide_guests.checked = this.entry.data.MEETING.HIDE_GUESTS;
	      } else {
	        this.DOM.form.hide_guests.checked = true; // default value
	      }
	    }

	    if (this.DOM.form.allow_invite) {
	      if (this.entry.data) {
	        this.DOM.form.allow_invite.checked = this.entry.data.MEETING && this.entry.data.MEETING.ALLOW_INVITE;
	      } else {
	        this.DOM.form.allow_invite.checked = this.entry.allowInvite;
	      }
	    }
	    let dateTime = this.dateTimeControl.getValue();
	    this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay, {
	      focus: true
	    });
	    this.loadPlannerData({
	      entityList: this.getUserSelectorEntityList(),
	      from: calendar_util.Util.formatDate(entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	      to: calendar_util.Util.formatDate(entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	      timezone: entry.getTimezoneFrom(),
	      location: this.locationSelector.getTextValue()
	    }).then(() => {
	      if (this.hasExternalEmailUsers()) {
	        this.showHideGuestsOption();
	      } else {
	        this.hideHideGuestsOption();
	      }
	    });
	  }
	  switchFullDay(value) {
	    value = !!this.DOM.fullDay.checked;
	    if (value && main_core.Type.isString(this.userSettings.timezoneName) && (!this.DOM.fromTz.value || !this.DOM.toTz.value)) {
	      this.DOM.fromTz.value = this.userSettings.timezoneName;
	      this.DOM.toTz.value = this.userSettings.timezoneName;
	      this.DOM.defTimezone.value = this.userSettings.timezoneName;
	    }
	    if (value) {
	      main_core.Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	    } else {
	      main_core.Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	    }
	    if (this.remindersControl) {
	      this.remindersControl.setFullDayMode(value);
	    }
	    this.refreshPlanner();
	  }
	  switchTimezone() {
	    if (main_core.Dom.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse')) {
	      main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	      main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	    } else {
	      main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	      main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	    }
	  }
	  initFormFieldManager(uid) {
	    this.DOM.mainBlock = this.DOM.content.querySelector(`#${uid}_main_block_wrap`);
	    this.DOM.additionalBlockWrap = this.DOM.content.querySelector(`#${uid}_additional_block_wrap`);
	    this.DOM.additionalBlock = this.DOM.content.querySelector(`#${uid}_additional_block`);
	    this.DOM.pinnedNamesWrap = this.DOM.content.querySelector(`#${uid}_additional_pinned_names`);
	    this.DOM.additionalSwitch = this.DOM.content.querySelector(`#${uid}_additional_switch`);
	    if (this.isLocationCalendar && !this.fieldIsPinned('location')) {
	      this.pinField('location');
	    }
	    main_core.Event.bind(this.DOM.additionalSwitch, 'click', () => {
	      main_core.Dom.toggleClass(this.DOM.additionalSwitch, 'opened');
	      main_core.Dom.toggleClass(this.DOM.additionalBlock, 'invisible');
	    });
	    main_core.Event.bind(this.DOM.formWrap, 'click', e => {
	      let target = e.target || e.srcElement;
	      if (target && target.getAttribute && target.getAttribute('data-bx-fixfield')) {
	        let fieldName = target.getAttribute('data-bx-fixfield');
	        if (!this.fieldIsPinned(fieldName)) {
	          this.pinField(fieldName);
	        } else {
	          this.unPinField(fieldName);
	        }
	      }
	    });
	  }
	  initDateTimeControl(uid) {
	    this.dateTimeControl = new SliderDateTimeControl(uid, {
	      showTimezone: true,
	      outerContent: this.DOM.content
	    });
	    this.dateTimeControl.subscribe('onChange', event => {
	      if (event instanceof main_core_events.BaseEvent) {
	        let value = event.getData().value;
	        if (this.remindersControl) {
	          this.remindersControl.setFullDayMode(value.fullDay);
	          if (!this.entry.id && !this.remindersControl.wasChangedByUser()) {
	            const defaultReminders = calendar_entry.EntryManager.getNewEntryReminders(value.fullDay ? 'fullDay' : 'withTime');
	            this.remindersControl.setValue(defaultReminders, true, false);
	          }
	        }
	        if (this.planner) {
	          this.planner.updateSelector(value.from, value.to, value.fullDay);
	        }
	        if (this.locationSelector) {
	          this.locationSelector.checkLocationAccessibility({
	            from: value.from,
	            to: value.to,
	            fullDay: value.fullDay,
	            currentEventId: this.entry.id
	          });
	        }
	      }
	    });
	  }
	  initNameControl(uid) {
	    this.DOM.entryName = this.DOM.content.querySelector(`#${uid}_entry_name`);
	    setTimeout(() => {
	      this.DOM.entryName.focus();
	      this.DOM.entryName.select();
	    }, 500);
	  }
	  initReminderControl(uid) {
	    const reminderWrap = this.DOM.content.querySelector(`#${uid}_reminder`);
	    if (!reminderWrap) {
	      return;
	    }
	    this.reminderValues = [];
	    this.DOM.reminderWrap = reminderWrap;
	    this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(main_core.Tag.render(_t9 || (_t9 = _`<span></span>`)));
	    this.remindersControl = new calendar_controls.Reminder({
	      wrap: this.DOM.reminderWrap,
	      zIndex: this.zIndex
	    });
	    this.remindersControl.subscribe('onChange', event => {
	      if (event instanceof main_core_events.BaseEvent) {
	        this.reminderValues = event.getData().values;
	        main_core.Dom.clean(this.DOM.reminderInputsWrap);
	        this.reminderValues.forEach(value => {
	          this.DOM.reminderInputsWrap.appendChild(main_core.Dom.create('INPUT', {
	            props: {
	              name: 'reminder[]',
	              type: 'hidden'
	            },
	            attrs: {
	              value: value
	            }
	          }));
	        });
	      }
	    });
	  }
	  initSectionSelector(uid) {
	    this.DOM.sectionInput = this.DOM.content.querySelector(`#${uid}_section`);
	    this.sectionSelector = new calendar_controls.SectionSelector({
	      outerWrap: this.DOM.content.querySelector(`#${uid}_section_wrap`),
	      defaultCalendarType: this.type,
	      defaultOwnerId: this.ownerId,
	      sectionList: this.sections,
	      sectionGroupList: calendar_sectionmanager.SectionManager.getSectionGroupList({
	        type: this.type || 'user',
	        ownerId: this.ownerId || this.userId,
	        userId: this.userId,
	        trackingUsersList: this.trackingUsersList
	      }),
	      mode: 'full',
	      zIndex: this.zIndex,
	      getCurrentSection: () => {
	        const section = this.getCurrentSection();
	        if (section) {
	          return {
	            id: section.ID,
	            name: section.NAME,
	            color: section.COLOR
	          };
	        }
	        return false;
	      },
	      selectCallback: sectionValue => {
	        if (sectionValue) {
	          this.DOM.sectionInput.value = sectionValue.id;
	          if (this.colorSelector) {
	            this.colorSelector.setValue(sectionValue.color);
	          }
	          this.entry.setSectionId(sectionValue.id);
	          calendar_sectionmanager.SectionManager.saveDefaultSectionId(sectionValue.id, {
	            calendarType: this.type,
	            ownerId: this.ownerId,
	            userId: this.userId,
	            sections: this.sections
	          });
	        }
	      }
	    });
	  }
	  initEditorControl(uid) {
	    if (!window["BXHtmlEditor"]) {
	      return setTimeout(BX.delegate(this.initEditorControl, this), 50);
	    }
	    this.editor = null;
	    if (window["BXHtmlEditor"]) {
	      this.editor = window["BXHtmlEditor"].Get(this.editorId);
	    }
	    if (!this.editor && top["BXHtmlEditor"] && top["BXHtmlEditor"] !== window["BXHtmlEditor"]) {
	      this.editor = top["BXHtmlEditor"].Get(this.editorId);
	    }
	    if (this.editor && this.editor.IsShown()) {
	      this.customizeHtmlEditor();
	      if (this.formDataValue.description) {
	        this.editor.SetContent(this.formDataValue.description);
	      }
	    } else {
	      this.BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function (editor) {
	        if (editor.id === this.editorId) {
	          this.editor = editor;
	          this.customizeHtmlEditor();
	          if (this.formDataValue.description) {
	            this.editor.SetContent(this.formDataValue.description);
	          }
	        }
	      }.bind(this));
	    }
	  }
	  customizeHtmlEditor() {
	    let editor = this.editor;
	    if (editor.toolbar && editor.toolbar.controls && editor.toolbar.controls.spoiler) {
	      main_core.Dom.remove(editor.toolbar.controls.spoiler.pCont);
	    }
	  }
	  initLocationControl(uid) {
	    this.DOM.locationWrap = this.DOM.content.querySelector(`#${uid}_location_wrap`);
	    this.DOM.locationInput = this.DOM.content.querySelector(`#${uid}_location`);
	    this.locationSelector = new calendar_controls.Location({
	      inputName: 'lo_cation',
	      // don't use 'location' word here mantis:107863
	      wrap: this.DOM.locationWrap,
	      richLocationEnabled: this.locationFeatureEnabled,
	      locationList: this.locationList || [],
	      roomsManager: this.roomsManager || null,
	      locationAccess: this.locationAccess || false,
	      iblockMeetingRoomList: this.iblockMeetingRoomList,
	      onChangeCallback: this.refreshPlanner
	    });
	  }
	  initRepeatRuleControl(uid) {
	    const rruleWrap = this.DOM.content.querySelector(`#${uid}_rrule_wrap`);
	    if (!rruleWrap) {
	      return;
	    }
	    this.DOM.rruleWrap = rruleWrap;
	    this.repeatSelector = new calendar_controls.RepeatSelector({
	      wrap: this.DOM.rruleWrap,
	      rruleType: this.DOM.content.querySelector(`#${uid}_rrule_type`),
	      getDate: function () {
	        return this.dateTimeControl.getValue().from;
	      }.bind(this)
	    });
	    this.dateTimeControl.subscribe('onChange', () => {
	      if (this.repeatSelector.getType() === 'weekly') {
	        this.repeatSelector.changeType(this.repeatSelector.getType());
	      }
	    });
	    this.planner.subscribe('onDateChange', () => {
	      if (this.repeatSelector.getType() === 'weekly') {
	        this.repeatSelector.changeType(this.repeatSelector.getType());
	      }
	    });
	  }
	  initAttendeesControl() {
	    this.DOM.userSelectorWrap = this.DOM.content.querySelector('.calendar-attendees-selector-wrap');
	    this.DOM.userSelectorValueWarp = this.DOM.userSelectorWrap.appendChild(main_core.Tag.render(_t10 || (_t10 = _`<div></div>`)));
	    this.userTagSelector = new ui_entitySelector.TagSelector({
	      dialogOptions: {
	        context: 'CALENDAR',
	        preselectedItems: this.attendeesPreselectedItems || [],
	        zIndex: this.slider.zIndex,
	        events: {
	          'Item:onSelect': this.handleUserSelectorChanges.bind(this),
	          'Item:onDeselect': this.handleUserSelectorChanges.bind(this)
	        },
	        entities: this.getParticipantsSelectorEntityList(),
	        searchTabOptions: {
	          stubOptions: {
	            title: main_core.Loc.getMessage('EC_USER_DIALOG_404_TITLE'),
	            subtitle: main_core.Loc.getMessage('EC_USER_DIALOG_404_SUBTITLE'),
	            icon: '/bitrix/images/calendar/search-email.svg',
	            iconOpacity: 100,
	            arrow: true
	          }
	        }
	      }
	    });
	    this.userTagSelector.renderTo(this.DOM.userSelectorWrap);
	    this.DOM.hideGuestsWrap = this.DOM.content.querySelector('.calendar-hide-members-wrap');
	  }
	  handleUserSelectorChanges() {
	    if (this.planner) {
	      this.planner.show();
	      this.planner.showLoader();
	      const selectedItems = this.userTagSelector.getDialog().getSelectedItems();
	      this.setUserSelectorEntityList(selectedItems.map(item => {
	        return {
	          entityId: item.entityId,
	          id: item.id,
	          entityType: item.entityType
	        };
	      }));
	      this.refreshPlanner();
	    }
	  }
	  hasExternalEmailUsers() {
	    return !!this.getUserSelectorEntityList().find(item => {
	      return item.entityType === 'email';
	    });
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
	  }
	  initPlanner(uid) {
	    this.DOM.plannerOuterWrap = this.DOM.content.querySelector(`#${uid}_planner_outer_wrap`);
	    this.planner = new calendar_planner.Planner({
	      wrap: this.DOM.plannerOuterWrap,
	      minWidth: parseInt(this.DOM.plannerOuterWrap.offsetWidth),
	      dayOfWeekMonthFormat: this.dayOfWeekMonthFormat,
	      locked: !this.plannerFeatureEnabled
	    });
	    this.planner.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
	    this.planner.subscribe('onExpandTimeline', this.handleExpandPlannerTimeline.bind(this));
	    this.planner.subscribe('onDisplayAttendees', this.checkLocationForm.bind(this));
	    this.planner.show();
	    this.planner.showLoader();
	  }
	  loadPlannerData(params = {}) {
	    this.planner.showLoader();
	    return new Promise(resolve => {
	      this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	        data: {
	          entryId: this.entry.id || 0,
	          entryLocation: this.entry.data.LOCATION || '',
	          ownerId: this.ownerId,
	          type: this.type,
	          entityList: params.entityList || [],
	          dateFrom: calendar_util.Util.formatDate(this.planner.scaleDateFrom),
	          dateTo: calendar_util.Util.formatDate(this.planner.scaleDateTo),
	          timezone: params.timezone || '',
	          location: params.location || '',
	          prevUserList: this.prevUserList
	        }
	      }).then(response => {
	        if (this.planner) {
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
	        }
	        if (this.hasExternalEmailUsers()) {
	          this.showHideGuestsOption();
	        } else {
	          this.hideHideGuestsOption();
	        }
	        resolve(response);
	      }, response => {
	        resolve(response);
	      });
	    });
	  }
	  initAdditionalControls(uid) {
	    this.DOM.accessibilityInput = this.DOM.content.querySelector(`#${uid}_accessibility`);
	    this.DOM.privateEventCheckbox = this.DOM.content.querySelector(`#${uid}_private`);
	    this.DOM.importantEventCheckbox = this.DOM.content.querySelector(`#${uid}_important`);
	  }
	  initColorControl(uid) {
	    this.DOM.colorWrap = this.DOM.content.querySelector(`#${uid}_color_selector_wrap`);
	    this.colorSelector = new calendar_controls.ColorSelector({
	      wrap: this.DOM.colorWrap
	    });
	  }
	  initCrmUfControl(uid) {
	    const crmUfWrap = BX(uid + '-uf-crm-wrap');
	    if (!crmUfWrap) {
	      return;
	    }
	    this.DOM.crmUfWrap = crmUfWrap;
	    if (this.DOM.crmUfWrap) {
	      let entry = this.getCurrentEntry();
	      let loader = this.DOM.crmUfWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(50), {
	        style: {
	          height: '40px',
	          width: '40px'
	        }
	      }));
	      setTimeout(function () {
	        this.BX.ajax.runAction('calendar.api.calendarajax.getCrmUserfield', {
	          data: {
	            event_id: entry && entry.id ? entry.id : 0
	          }
	        }).then(
	        // Success
	        function (response) {
	          if (main_core.Type.isDomNode(this.DOM.crmUfWrap)) {
	            this.BX.html(this.DOM.crmUfWrap, response.data.html);
	          }
	        }.bind(this),
	        // Failure
	        function (response) {
	          main_core.Dom.remove(loader);
	        }.bind(this));
	      }.bind(this), 800);
	    }
	  }
	  denySliderClose() {
	    this.denyClose = true;
	  }
	  allowSliderClose() {
	    this.denyClose = false;
	  }
	  checkDenyClose() {
	    // pending request
	    if (this.state === this.STATE.REQUEST) {
	      return true;
	    }

	    // Check if closing of slider was caused during selection.
	    if (!main_core.Type.isNull(this.mouseUpNodeCheck)) {
	      return !this.mouseUpNodeCheck;
	    }

	    // if (top.BX(this.id + '_time_from_div') && top.BX(this.id + '_time_from_div').style.display !== 'none')
	    // 	return true;
	    //
	    // if (top.BX(this.id + '_time_to_div') && top.BX(this.id + '_time_to_div').style.display !== 'none')
	    // 	return true;

	    return this.denyClose;
	  }
	  setCurrentEntry(entry = null, userIndex = null) {
	    this.entry = calendar_entry.EntryManager.getEntryInstance(entry, userIndex, {
	      type: this.type,
	      ownerId: this.ownerId
	    });
	    calendar_entry.EntryManager.registerEntrySlider(this.entry, this);
	  }
	  getCurrentEntry() {
	    return this.entry;
	  }
	  getCurrentSection() {
	    let section = false,
	      sectionId = this.getCurrentSectionId();
	    if (sectionId && this.sectionIndex[sectionId] !== undefined && this.sections[this.sectionIndex[sectionId]] !== undefined) {
	      section = this.sections[this.sectionIndex[sectionId]];
	    }
	    return section;
	  }
	  getCurrentSectionId() {
	    let section = 0,
	      entry = this.getCurrentEntry();
	    if (entry instanceof calendar_entry.Entry && this.sections[this.sectionIndex[entry.sectionId]]) {
	      section = parseInt(entry.sectionId);
	    }
	    if (!section) {
	      if (this.type === 'location') {
	        section = calendar_roomsmanager.RoomsManager.getNewEntrySectionId();
	      } else {
	        section = calendar_sectionmanager.SectionManager.getNewEntrySectionId(this.type, this.ownerId);
	      }
	      if (!this.sectionIndex[section]) {
	        section = null;
	      }
	    }
	    if (!section && this.sections[0]) {
	      section = parseInt(this.sections[0].ID);
	    }
	    return section;
	  }
	  pinField(fieldName) {
	    let [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
	    let field = placeHoldersAdditional[fieldName],
	      newField = placeHolders[fieldName],
	      fieldHeight = field.offsetHeight;
	    field.style.height = fieldHeight + 'px';
	    setTimeout(function () {
	      main_core.Dom.addClass(field, 'calendar-hide-field');
	    }, 0);
	    newField.style.height = '0';
	    if (fieldName === 'description') {
	      setTimeout(function () {
	        if (!this.DOM.descriptionAdditionalWrap) {
	          this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
	        }
	        if (this.DOM.descriptionAdditionalWrap) {
	          while (this.DOM.descriptionAdditionalWrap.firstChild) {
	            newField.appendChild(this.DOM.descriptionAdditionalWrap.firstChild);
	          }
	        }
	        newField.style.height = fieldHeight + 'px';
	      }.bind(this), 200);
	      setTimeout(function () {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.display = 'none';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = true;
	        let editor = window["BXHtmlEditor"].Get(this.editorId);
	        if (editor) {
	          editor.CheckAndReInit();
	        }
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }.bind(this), 500);
	    } else {
	      setTimeout(function () {
	        while (field.firstChild) {
	          newField.appendChild(field.firstChild);
	        }
	        newField.style.height = fieldHeight + 'px';
	      }, 200);
	      setTimeout(() => {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.height = '';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = true;
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }, 300);
	    }
	  }
	  unPinField(fieldName) {
	    let [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
	    let field = placeHolders[fieldName],
	      newField = placeHoldersAdditional[fieldName],
	      fieldHeight = field.offsetHeight;
	    field.style.height = fieldHeight + 'px';
	    setTimeout(function () {
	      main_core.Dom.addClass(field, 'calendar-hide-field');
	    }, 0);
	    newField.style.height = '0';
	    if (fieldName === 'description') {
	      setTimeout(function () {
	        if (!this.DOM.descriptionAdditionalWrap) {
	          this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
	        }
	        if (this.DOM.descriptionAdditionalWrap) {
	          while (field.firstChild) {
	            this.DOM.descriptionAdditionalWrap.appendChild(field.firstChild);
	          }
	        }
	        newField.style.display = '';
	        newField.style.height = fieldHeight + 'px';
	      }.bind(this), 200);
	      setTimeout(function () {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.height = '';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = false;
	        let editor = window["BXHtmlEditor"].Get(this.editorId);
	        if (editor) {
	          editor.CheckAndReInit();
	        }
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }.bind(this), 300);
	    } else {
	      setTimeout(function () {
	        while (field.firstChild) {
	          newField.appendChild(field.firstChild);
	        }
	        newField.style.height = fieldHeight + 'px';
	      }, 200);
	      setTimeout(function () {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.height = '';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = false;
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }.bind(this), 300);
	    }
	  }
	  fieldIsPinned(fieldName) {
	    return this.pinnedFieldsIndex[fieldName];
	  }
	  getPlaceholders() {
	    if (!this.placeHolders) {
	      this.placeHolders = {};
	      this.placeHoldersAdditional = {};
	      let i,
	        fieldId,
	        nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-additional-placeholder');
	      for (i = 0; i < nodes.length; i++) {
	        fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
	        if (fieldId) {
	          this.placeHoldersAdditional[fieldId] = nodes[i];
	        }
	      }
	      nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-placeholder');
	      for (i = 0; i < nodes.length; i++) {
	        fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
	        if (fieldId) {
	          this.placeHolders[fieldId] = nodes[i];
	        }
	      }
	    }
	    return [this.placeHolders, this.placeHoldersAdditional];
	  }
	  getSettings(settings) {
	    this.pinnedFieldsIndex = {};
	    let i,
	      pinnedFields = [];
	    for (i in settings.pinnedFields) {
	      if (settings.pinnedFields.hasOwnProperty(i)) {
	        pinnedFields.push(settings.pinnedFields[i]);
	        this.pinnedFieldsIndex[settings.pinnedFields[i]] = true;
	      }
	    }
	    settings.pinnedFields = pinnedFields;
	    return settings;
	  }
	  saveSettings() {
	    let fieldName,
	      pinnedFields = [];
	    for (fieldName in this.pinnedFieldsIndex) {
	      if (this.pinnedFieldsIndex.hasOwnProperty(fieldName) && this.pinnedFieldsIndex[fieldName]) {
	        pinnedFields.push(fieldName);
	      }
	    }
	    this.formSettings.pinnedFields = pinnedFields;
	    this.BX.userOptions.save('calendar', this.formType, 'pinnedFields', pinnedFields);
	  }
	  updateAdditionalBlockState(timeout) {
	    if (timeout !== false) {
	      if (this.updateAdditionalBlockTimeout) {
	        clearTimeout(this.updateAdditionalBlockTimeout);
	        this.updateAdditionalBlockTimeout = null;
	      }
	      this.updateAdditionalBlockTimeout = setTimeout(() => {
	        this.updateAdditionalBlockState(false);
	      }, 300);
	    } else {
	      let i,
	        names = this.DOM.additionalBlock.getElementsByClassName('js-calendar-field-name');
	      main_core.Dom.clean(this.DOM.pinnedNamesWrap);
	      for (i = 0; i < names.length; i++) {
	        this.DOM.pinnedNamesWrap.appendChild(main_core.Dom.create("SPAN", {
	          props: {
	            className: 'calendar-additional-alt-promo-text'
	          },
	          html: names[i].innerHTML
	        }));
	      }
	      if (!names.length) {
	        main_core.Dom.addClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
	      } else if (main_core.Dom.hasClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden')) {
	        main_core.Dom.removeClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
	      }
	      this.checkLastItemBorder();
	    }
	  }
	  checkLastItemBorder() {
	    let noBorderClass = 'no-border',
	      i,
	      nodes;
	    nodes = this.DOM.mainBlock.querySelectorAll('.calendar-options-item-border');
	    for (i = 0; i < nodes.length; i++) {
	      if (i === nodes.length - 1) {
	        main_core.Dom.addClass(nodes[i], noBorderClass);
	      } else {
	        main_core.Dom.removeClass(nodes[i], noBorderClass);
	      }
	    }
	    nodes = this.DOM.additionalBlock.querySelectorAll('.calendar-options-item-border');
	    for (i = 0; i < nodes.length; i++) {
	      if (i === nodes.length - 1) {
	        main_core.Dom.addClass(nodes[i], noBorderClass);
	      } else {
	        main_core.Dom.removeClass(nodes[i], noBorderClass);
	      }
	    }
	  }
	  handlePlannerSelectorChanges(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      let data = event.getData();
	      // Date time
	      this.dateTimeControl.setValue({
	        from: data.dateFrom,
	        to: data.dateTo
	      });
	      if (this.locationSelector) {
	        this.locationSelector.checkLocationAccessibility({
	          from: data.dateFrom,
	          to: data.dateTo,
	          fullDay: data.fullDay,
	          currentEventId: this.entry.id
	        });
	      }
	      if (this.planner) {
	        let fromHours = parseInt(data.dateFrom.getHours()) + Math.floor(data.dateFrom.getMinutes() / 60);
	        let toHours = parseInt(data.dateTo.getHours()) + Math.floor(data.dateTo.getMinutes() / 60);
	        if (fromHours !== 0 && fromHours <= this.planner.shownScaleTimeFrom || toHours !== 0 && toHours !== 23 && toHours + 1 >= this.planner.shownScaleTimeTo) {
	          this.planner.updateSelector(data.dateFrom, data.dateTo, data.fullDay);
	        }
	      }
	    }
	  }
	  handleExpandPlannerTimeline(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      let data = event.getData();
	      if (data.reload) {
	        this.prevUserList = [];
	        let dateTime = this.dateTimeControl.getValue();
	        this.loadPlannerData({
	          entityList: this.getUserSelectorEntityList(),
	          from: calendar_util.Util.formatDate(data.dateFrom),
	          to: calendar_util.Util.formatDate(data.dateTo),
	          timezone: dateTime.timezoneFrom,
	          location: this.locationSelector.getTextValue(),
	          focusSelector: false
	        });
	      }
	    }
	  }
	  getUserSelectorEntityList() {
	    return this.selectorEntityList;
	  }
	  setUserSelectorEntityList(selectorEntityList) {
	    this.selectorEntityList = selectorEntityList;
	  }
	  refreshPlannerState() {
	    let dateTime = this.dateTimeControl.getValue();
	    this.loadPlannerData({
	      entityList: this.getUserSelectorEntityList(),
	      from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	      to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	      timezone: dateTime.timezoneFrom,
	      location: this.locationSelector.getTextValue()
	    });
	  }
	  checkLocationForm(event) {
	    if (event && event instanceof main_core_events.BaseEvent) {
	      const data = event.getData();
	      const usersCount = data.usersCount;
	      if (this.locationCapacity !== 0) {
	        calendar_controls.Location.setCurrentCapacity(this.locationCapacity);
	        this.locationCapacity = 0;
	      }
	      let locationCapacity = calendar_controls.Location.getCurrentCapacity() || 0;
	      if (this.locationSelector.value.type === undefined) {
	        if (locationCapacity) {
	          locationCapacity = 0;
	          calendar_controls.Location.setCurrentCapacity(0);
	        }
	      }
	      if (locationCapacity < usersCount && locationCapacity !== 0) {
	        this.locationSelector.addCapacityAlert();
	      } else {
	        this.locationSelector.removeCapacityAlert();
	      }
	    }
	  }
	  plannerIsShown() {
	    return this.DOM.plannerWrap && main_core.Dom.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
	  }
	  keyHandler(e) {
	    if ((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode === calendar_util.Util.getKeyCode('enter') && this.checkTopSlider()) {
	      this.save();
	    }
	  }
	  checkTopSlider() {
	    const slider = calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	    return slider && slider.options.type === 'calendar:slider';
	  }
	  showError(errorList) {
	    let errorText = '';
	    if (main_core.Type.isArray(errorList)) {
	      errorList.forEach(error => {
	        if (error.code === "edit_entry_location_busy") {
	          this.locationBusyAlert = calendar_util.Util.showFieldError(error.message, this.DOM.locationWrap, {
	            clearTimeout: 10000
	          });
	          return;
	        }
	        errorText += error.message + "\n";
	      });
	    }
	    if (errorText !== '') {
	      alert(errorText);
	    }
	  }
	  getFormDataChanges(excludes = []) {
	    const entry = this.entry;
	    let fields = [];

	    // Name
	    if (!excludes.includes('name') && entry.name !== this.DOM.form.name.value) {
	      fields.push('name');
	    }

	    // Description
	    if (!excludes.includes('description') && entry.getDescription() !== this.DOM.form.desc.value) {
	      fields.push('description');
	    }

	    // Location
	    if (!excludes.includes('location') && this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(this.entry.getLocation())) !== this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(this.locationSelector.getTextValue()))) {
	      fields.push('location');
	    }

	    // Date + time
	    const dateTime = this.dateTimeControl.getValue();
	    if (!excludes.includes('date&time') && (entry.isFullDay() !== dateTime.fullDay || dateTime.from.toString() !== entry.from.toString() || dateTime.to.toString() !== entry.to.toString())) {
	      fields.push('date&time');
	    }

	    // Section
	    if (!excludes.includes('section') && parseInt(entry.sectionId) !== parseInt(this.DOM.sectionInput.value)) {
	      fields.push('section');
	    }

	    // Access codes
	    if (!excludes.includes('codes') && this.getUserSelectorEntityList().map(item => {
	      return item.entityId + ':' + item.id;
	    }).join('|') !== entry.getAttendeesEntityList().map(item => {
	      return item.entityId + ':' + item.id;
	    }).join('|')) {
	      fields.push('codes');
	    }
	    return fields;
	  }
	  checkCurrentUsersAccessibility() {
	    return this.getFormDataChanges().includes('date&time');
	  }
	  formDataChanged() {
	    return this.getFormDataChanges().length > 0;
	  }
	  getUserCodes() {
	    const codes = [],
	      valuesInput = this.DOM.attendeesWrap.querySelectorAll('input[name="EVENT_DESTINATION[]"]');
	    for (let i = 0; i < valuesInput.length; i++) {
	      if (!codes.includes(valuesInput[i].value)) {
	        codes.push(valuesInput[i].value);
	      }
	    }
	    return codes;
	  }
	  handlePull(event) {
	    var _params$fields, _params$fields2, _params$fields3, _params$fields4;
	    if (!event instanceof main_core_events.BaseEvent) {
	      return;
	    }
	    const data = event.getData();
	    const command = data[0];
	    const params = main_core.Type.isObjectLike(data[1]) ? data[1] : {};
	    switch (command) {
	      case 'edit_event':
	      case 'delete_event':
	      case 'set_meeting_status':
	        const userIdList = main_core.Type.isArray(params == null ? void 0 : (_params$fields = params.fields) == null ? void 0 : _params$fields.ATTENDEES) ? params.fields.ATTENDEES : [];
	        const eventOwner = (params == null ? void 0 : (_params$fields2 = params.fields) == null ? void 0 : _params$fields2.CAL_TYPE) === 'user' ? parseInt(params == null ? void 0 : (_params$fields3 = params.fields) == null ? void 0 : _params$fields3.OWNER_ID) : parseInt(params == null ? void 0 : (_params$fields4 = params.fields) == null ? void 0 : _params$fields4.CREATED_BY);
	        if (!userIdList.includes(eventOwner)) {
	          userIdList.push(eventOwner);
	        }
	        this.clearAccessibilityData(userIdList);
	        this.refreshPlannerState();
	        break;
	    }
	  }
	  clearAccessibilityData(userIdList) {
	    if (main_core.Type.isArray(userIdList) && userIdList.length && this.prevUserList.length) {
	      this.prevUserList = this.prevUserList.filter(userId => {
	        return !userIdList.includes(userId);
	      });
	    }
	  }
	  getParticipantsSelectorEntityList() {
	    if (this.participantsSelectorEntityList && this.participantsSelectorEntityList.length) {
	      return this.participantsSelectorEntityList;
	    }
	    let entityList = [{
	      id: 'user',
	      options: {
	        inviteGuestLink: true,
	        emailUsers: true
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
	    }];
	    if (this.attendeesPreselectedItems) {
	      let projectRole = null;
	      this.attendeesPreselectedItems.forEach(item => {
	        const type = item[0];
	        const role = item[1];
	        if (type === 'project-roles') {
	          projectRole = role;
	        }
	      });
	      if (projectRole) {
	        entityList = [{
	          id: 'user'
	        }, {
	          id: 'project-roles',
	          options: {
	            projectId: projectRole.split('_')[0]
	          },
	          dynamicLoad: true
	        }];
	      }
	    }
	    return entityList;
	  }
	  isSyncSection(section) {
	    return section.EXTERNAL_TYPE === 'icloud' || section.EXTERNAL_TYPE === 'google' || section.EXTERNAL_TYPE === 'office365' || section.connectionLinks && section.connectionLinks.length;
	  }
	  getSectionsForEditEvent(sections, currentSection) {
	    const result = [];
	    const currentType = currentSection.CAL_TYPE;
	    result.push(currentSection);
	    sections.forEach(section => {
	      if (!this.isSyncSection(section) && section.CAL_TYPE === currentType) {
	        result.push(section);
	      }
	    });
	    this.sections = result;
	    this.sectionIndex = [];
	    if (main_core.Type.isArray(this.sections)) {
	      this.sections.forEach((value, ind) => {
	        this.sectionIndex[parseInt(value.ID)] = ind;
	      }, this);
	    }
	  }
	  unsetHiddenSection(section, sectionManager) {
	    const sectId = parseInt(section.ID);
	    if (!sectionManager.sectionIsShown(sectId)) {
	      let hiddenSections = sectionManager.getHiddenSections();
	      hiddenSections = hiddenSections.filter(sectionId => {
	        return sectionId !== sectId;
	      }, this);
	      sectionManager.setHiddenSections(hiddenSections);
	      sectionManager.saveHiddenSections();
	    }
	  }
	}

	exports.EventEditForm = EventEditForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar.Controls,BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.Calendar,BX.UI.EntitySelector,BX.Calendar));
//# sourceMappingURL=eventeditform.bundle.js.map
