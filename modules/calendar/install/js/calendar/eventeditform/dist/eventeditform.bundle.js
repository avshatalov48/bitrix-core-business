/* eslint-disable */
this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_planner,calendar_roomsmanager,calendar_sectionmanager,main_core_events,ui_entitySelector,main_core,calendar_controls,calendar_util) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2;
	class SliderDateTimeControl extends calendar_controls.DateTimeControl {
	  create() {
	    this.DOM.dateTimeWrap = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_container`);
	    this.DOM.editor = this.DOM.outerContent.querySelector(`#${this.UID}_datetime_editor`);
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
	  setReadonly(timezoneHint) {
	    const value = this.getValue();
	    let result = '';
	    const dateFrom = calendar_util.Util.formatDateUsable(value.from, true, true);
	    const dateTo = calendar_util.Util.formatDateUsable(value.to, true, true);
	    const timeFrom = this.DOM.fromTime.value;
	    const timeTo = this.DOM.toTime.value;
	    result += dateFrom + ', ';
	    if (value.fullDay) {
	      if (dateFrom === dateTo) {
	        result += main_core.Loc.getMessage('EC_ALL_DAY');
	      } else {
	        result += ' - ';
	      }
	    } else {
	      result += timeFrom + ' - ' + timeTo;
	    }
	    if (!value.fullDay && dateFrom !== dateTo) {
	      result += ', ' + dateTo;
	    }
	    let timezoneIcon = '';
	    if (main_core.Type.isStringFilled(timezoneHint)) {
	      timezoneIcon = main_core.Tag.render(_t || (_t = _`
				<div class="calendar-date-selector-readonly-timezone" title="${0}">
					<div class="calendar-date-selector-readonly-timezone-icon"></div>
				</div>
			`), timezoneHint);
	    }
	    main_core.Dom.style(this.DOM.editor, 'display', 'none');
	    const readonlyElement = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="calendar-options-item-column-right">
				<div class="calendar-field calendar-date-selector-readonly">
					${0}
					${0}
				</div>
			</div>
		`), result, timezoneIcon);
	    main_core.Dom.append(readonlyElement, this.DOM.dateTimeWrap);
	  }
	}

	let _$1 = t => t,
	  _t$1,
	  _t2$1,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
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
	class EventEditForm {
	  constructor(options = {}) {
	    var _this$entry, _this$entry$data, _options$calendarCont, _this$entry2;
	    this.DOM = {};
	    this.uid = null;
	    this.sliderId = 'calendar:edit-entry-slider';
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
	    this.eventOptions = {};
	    this.name = options.name || 'eventeditform';
	    this.type = options.type || 'user';
	    this.isLocationCalendar = options.isLocationCalendar || false;
	    this.locationAccess = options.locationAccess || false;
	    this.isProjectFeatureEnabled = calendar_util.Util.isProjectFeatureEnabled() || false;
	    this.locationCapacity = options.locationCapacity || 0;
	    this.roomsManager = options.roomsManager || null;
	    this.userId = options.userId || parseInt(main_core.Loc.getMessage('USER_ID'));
	    this.ownerId = options.ownerId;
	    this.entryId = parseInt(options.entryId) || null;
	    this.entry = options.entry || null;
	    this.eventOptions = ((_this$entry = this.entry) == null ? void 0 : (_this$entry$data = _this$entry.data) == null ? void 0 : _this$entry$data.OPTIONS) || {};
	    this.formDataValue = options.formDataValue || {};
	    this.emitter = new main_core_events.EventEmitter();
	    this.emitter.setEventNamespace('BX.Calendar.EventEditForm');
	    this.BX = calendar_util.Util.getBX();
	    this.isCollabUser = ((_options$calendarCont = options.calendarContext) == null ? void 0 : _options$calendarCont.isCollabUser) || false;
	    this.analyticsChatId = options.createChatId || null;
	    this.analyticsSubSection = options.analyticsSubSection || this.getFormAnalyticsContext();
	    if (this.isCollabUser) {
	      calendar_util.Util.setCalendarContext(options.calendarContext);
	    } else {
	      var _Util$getCalendarCont;
	      this.context = (_Util$getCalendarCont = calendar_util.Util.getCalendarContext()) != null ? _Util$getCalendarCont : options.calendarContext;
	      if (!calendar_util.Util.getCalendarContext()) {
	        calendar_util.Util.setCalendarContext(this.context);
	      }
	    }
	    this.isOpenEvent = (((_this$entry2 = this.entry) == null ? void 0 : _this$entry2.data['CAL_TYPE']) || this.type) === 'open_event';
	    // TODO: remove this check, planner enabled always
	    this.plannerEnabled = true;
	    this.sectionSelectorEnabled = !this.isOpenEvent;
	    this.attendeesControlEnabled = !this.isOpenEvent;
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
	    if (options.jumpToControl) {
	      this.jumpToControl = options.jumpToControl;
	    }
	    if (this.plannerEnabled) {
	      this.refreshPlanner = main_core.Runtime.debounce(this.refreshPlannerState, 100, this);
	    }
	    this.state = this.STATE.READY;
	    this.doShowConfirmPopup = true;
	    this.sliderOnClose = this.hideWithConfirm.bind(this);
	    this.handlePullBind = this.handlePull.bind(this);
	    this.keyHandlerBind = this.keyHandler.bind(this);
	    this.lastUsedSaveOptions = {};
	    this.timezoneHint = '';
	    this.isAvailable = true;
	  }
	  getFormAnalyticsContext() {
	    if (this.analyticsChatId) {
	      return 'chat_textarea';
	    }
	    if (this.type === 'group') {
	      return 'calendar_collab';
	    }
	    return 'calendar_personal';
	  }
	  initInSlider(slider, promiseResolve) {
	    this.sliderId = slider.getUrl();
	    this.BX.addCustomEvent(slider, 'SidePanel.Slider:onLoad', this.onLoadSlider.bind(this));
	    this.BX.addCustomEvent(slider, 'SidePanel.Slider:onClose', this.sliderOnClose);
	    this.BX.addCustomEvent(slider, 'SidePanel.Slider:onBeforeCloseComplete', this.destroy.bind(this));
	    this.setCurrentEntry(this.entry || null);
	    this.createContent(slider).then(html => {
	      if (main_core.Type.isFunction(promiseResolve)) {
	        promiseResolve(html);
	      }
	    });
	    this.opened = true;
	    this.bindEventHandlers();
	  }
	  canEdit() {
	    var _this$entry$permissio;
	    if (main_core.Type.isBoolean((_this$entry$permissio = this.entry.permissions) == null ? void 0 : _this$entry$permissio.edit)) {
	      return this.entry.permissions.edit;
	    }
	    if (this.entry.isMeeting() && this.entry.sectionId !== this.getCurrentSectionId()) {
	      return false;
	    }
	    if (this.entry.isResourcebooking()) {
	      return false;
	    }
	    return new calendar_sectionmanager.CalendarSection(this.getCurrentSection()).canDo('edit');
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
	        bgColor: '#55D0E0'
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
	      const target = e.target || e.srcElement;
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
	    this.BX.addCustomEvent(window, 'onCalendarControlChildPopupShown', this.BX.proxy(this.denySliderClose, this));
	    this.BX.addCustomEvent(window, 'onCalendarControlChildPopupClosed', this.BX.proxy(this.allowSliderClose, this));
	  }
	  onLoadSlider(event) {
	    this.slider = event.getSlider();
	    this.DOM.content = this.slider.layout.content;
	    this.sliderId = this.slider.getUrl();
	    if (!this.isAvailable) {
	      return;
	    }

	    // Used to execute javasctipt and attach CSS from ajax responce
	    this.BX.html(this.slider.layout.content, this.slider.getData().get('sliderContent'));
	    this.initControls(this.uid);
	    this.setFormValues();
	    if (main_core.Type.isStringFilled(this.jumpToControl)) {
	      if (this.jumpToControl === 'userSelector') {
	        const attendeesSelectorWrap = this.DOM.content.querySelector(`#${this.uid}_attendees_selector`);
	        main_core.Dom.style(attendeesSelectorWrap, 'transition', '300ms background ease');
	        setTimeout(() => this.highlightField(attendeesSelectorWrap), 900);
	      }
	      if (this.jumpToControl === 'location') {
	        const fieldWrap = document.querySelector(`[data-bx-block-placeholer=${this.jumpToControl}]`);
	        main_core.Dom.style(fieldWrap, 'transition', '300ms background ease');
	        setTimeout(() => this.highlightField(fieldWrap), 900);
	      }
	    }
	    main_core.Event.bind(this.DOM.content, 'wheel', () => {
	      var _this$highlightFieldS;
	      return (_this$highlightFieldS = this.highlightFieldScrollAnimation) == null ? void 0 : _this$highlightFieldS.stop();
	    });
	  }
	  close() {
	    if (!this.checkDenyClose()) {
	      this.state = this.STATE.READY;
	      this.BX.SidePanel.Instance.close();
	    }
	  }
	  save(options = {}) {
	    if (this.state === this.STATE.REQUEST || this.DOM.locationRepeatBusyErrorPopup) {
	      return false;
	    }
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    const formDataChanges = this.getFormDataChanges();
	    if (this.isEditForm() && formDataChanges.length === 0) {
	      this.BX.SidePanel.Instance.close();
	      return true;
	    }
	    if (!this.userSettings.sendFromEmail && this.hasExternalEmailUsers() && this.canEdit() && !options.emailConfirmDialogShown) {
	      calendar_entry.EntryManager.showConfirmedEmailDialog({
	        callback: params => {
	          if (params.sendFromEmail) {
	            this.userSettings.sendFromEmail = params.sendFromEmail;
	          }
	          options.emailConfirmDialogShown = true;
	          this.save(options);
	        }
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isRecursive() && !options.confirmed && this.getFormDataChanges(['section']).length > 0) {
	      calendar_entry.EntryManager.showConfirmEditDialog({
	        callback: params => {
	          options.recursionMode = this.entry.isFirstInstance() && params.recursionMode === 'next' ? 'all' : params.recursionMode;
	          options.confirmed = true;
	          this.lastUsedSaveOptions = options;
	          this.save(options);
	        },
	        canEditOnlyThis: this.canEditOnlyThis()
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isMeeting() && options.sendInvitesAgain === undefined && formDataChanges.includes('date&time') && this.entry.getAttendees().find(item => {
	      return item.STATUS === 'N';
	    })) {
	      calendar_entry.EntryManager.showReInviteUsersDialog({
	        callback: params => {
	          options.sendInvitesAgain = params.sendInvitesAgain;
	          this.lastUsedSaveOptions = options;
	          this.save(options);
	        }
	      });
	      return false;
	    }
	    if (this.entry.id && this.entry.isRecursive() && !options.confirmed && formDataChanges.includes('section')) {
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
	    const section = this.getCurrentSection();
	    if (section && section.COLOR.toLowerCase() !== this.colorSelector.getValue().toLowerCase()) {
	      // Color
	      this.DOM.form.color.value = this.colorSelector.getValue();
	    }
	    if (options.recursionMode) {
	      this.DOM.form.current_date_from.value = calendar_util.Util.formatDate(this.entry.from);
	      this.DOM.form.rec_edit_mode.value = options.recursionMode;
	    } else {
	      this.DOM.form.current_date_from.value = null;
	      this.DOM.form.rec_edit_mode.value = null;
	    }
	    if (options.sendInvitesAgain !== undefined) {
	      this.DOM.form.appendChild(main_core.Tag.render(_t$1 || (_t$1 = _$1`<input name="sendInvitesAgain" type="hidden" value="${0}">`), options.sendInvitesAgain ? 'Y' : 'N'));
	    }
	    if (!this.DOM.form.requestUid) {
	      this.DOM.requestUid = this.DOM.form.appendChild(main_core.Tag.render(_t2$1 || (_t2$1 = _$1`<input name="requestUid" type="hidden">`)));
	    }
	    if (!this.DOM.form.meeting_host) {
	      this.DOM.meeting_host = this.DOM.form.appendChild(main_core.Tag.render(_t3 || (_t3 = _$1`<input type="hidden" name="meeting_host" value="${0}">`), this.entry.data.MEETING_HOST || '0'));
	    }
	    if (!this.DOM.form.chat_id) {
	      this.DOM.chat_id = this.DOM.form.appendChild(main_core.Tag.render(_t4 || (_t4 = _$1`<input type="hidden" name="chat_id" value="${0}">`), this.entry.data.MEETING ? this.entry.data.MEETING.CHAT_ID : 0));
	    }
	    this.DOM.requestUid.value = calendar_util.Util.registerRequestId();
	    let attendeesEntityList;
	    if (this.attendeesControlEnabled) {
	      // Save attendees from userSelector
	      attendeesEntityList = this.getUserSelectorEntityList();
	      main_core.Dom.clean(this.DOM.userSelectorValueWarp);
	      attendeesEntityList.forEach((entity, index) => {
	        this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t5 || (_t5 = _$1`
				<input type="hidden" name="attendeesEntityList[${0}][entityId]" value="${0}">
			`), index, entity.entityId));
	        this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t6 || (_t6 = _$1`
				<input type="hidden" name="attendeesEntityList[${0}][id]" value="${0}">
			`), index, entity.id));
	      });
	    }
	    let checkCurrentUsersAccessibility = !this.entry.id || this.checkCurrentUsersAccessibility();
	    if (!checkCurrentUsersAccessibility && formDataChanges.includes('codes')) {
	      const previousAttendeesList = this.entry.getAttendeesEntityList();
	      attendeesEntityList.forEach(entity => {
	        if (!previousAttendeesList.find(item => {
	          return entity.entityId === item.entityId && Number(entity.id) === Number(item.id);
	        })) {
	          if (entity.entityId === 'user') {
	            this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t7 || (_t7 = _$1`
							<input type="hidden" name="newAttendeesList[]" value="${0}">
						`), parseInt(entity.id)));
	          } else {
	            checkCurrentUsersAccessibility = true;
	          }
	        }
	      });
	    }
	    if (this.attendeesControlEnabled) {
	      this.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_t8 || (_t8 = _$1`
				<input type="hidden" name="checkCurrentUsersAccessibility" value="${0}">
			`), checkCurrentUsersAccessibility ? 'Y' : 'N'));
	    }
	    if (this.isOpenEvent) {
	      const selectedCategories = this.categoryTagSelector.getDialog().getSelectedItems();
	      const selectedCategory = selectedCategories[0].id;
	      this.DOM.form.appendChild(main_core.Tag.render(_t9 || (_t9 = _$1`<input type="hidden" name="category" value="${0}">`), selectedCategory));
	    }
	    if (this.analyticsSubSection) {
	      this.DOM.form.appendChild(main_core.Tag.render(_t10 || (_t10 = _$1`<input type="hidden" name="analyticsSubSection" value="${0}">`), this.analyticsSubSection));
	    }
	    if (this.analyticsChatId) {
	      this.DOM.form.appendChild(main_core.Tag.render(_t11 || (_t11 = _$1`<input type="hidden" name="analyticsChatId" value="${0}">`), this.analyticsChatId));
	    }
	    this.DOM.form.doCheckOccupancy.value = options.doCheckOccupancy || 'Y';
	    const data = new FormData(this.DOM.form);
	    this.BX.ajax.runAction('calendar.api.calendarentryajax.editEntry', {
	      data
	    }).then(async response => {
	      if (this.canEditOnlyThis() && formDataChanges.includes('color')) {
	        var _response$data, _response$data$eventL, _newChildEvent$ID;
	        const newChildEvent = (_response$data = response.data) == null ? void 0 : (_response$data$eventL = _response$data.eventList) == null ? void 0 : _response$data$eventL.find(event => event.DATE_FROM.includes(data.get('date_from')) && parseInt(event.OWNER_ID, 10) === parseInt(this.ownerId, 10));
	        const colorEntryId = (_newChildEvent$ID = newChildEvent == null ? void 0 : newChildEvent.ID) != null ? _newChildEvent$ID : this.entryId;
	        await this.BX.ajax.runAction('calendar.api.calendarajax.updateColor', {
	          data: {
	            entryId: colorEntryId,
	            color: this.colorSelector.getValue().toLowerCase()
	          }
	        });
	      }
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
	      this.doShowConfirmPopup = false;
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
	      if (main_core.Type.isArray(response.data.eventList) && response.data.eventList.length > 0 && response.data.eventList[0].REMIND && response.data.eventList[0].REMIND.length > 0) {
	        calendar_entry.EntryManager.setNewEntryReminders(response.data.eventList[0].DT_SKIP_TIME === 'Y' ? 'fullDay' : 'withTime', response.data.eventList[0].REMIND);
	      }
	      this.emitter.emit('onSave', new main_core_events.BaseEvent({
	        data: {
	          responseData: response.data,
	          options
	        }
	      }));
	      main_core_events.EventEmitter.emit('BX.Calendar:onEntrySave', new main_core_events.BaseEvent({
	        data: {
	          sliderId: this.sliderId,
	          responseData: response.data,
	          options
	        }
	      }));
	    }, response => {
	      main_core.Dom.removeClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	      main_core.Dom.removeClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	      if (response.data && main_core.Type.isPlainObject(response.data.busyUsersList)) {
	        this.handleBusyUsersError(response.data.busyUsersList);
	        const errors = [];
	        response.errors.forEach(error => {
	          if (error.code !== 'edit_entry_user_busy') {
	            errors.push(error);
	          }
	        });
	        response.errors = errors;
	      }
	      if (response.errors && response.errors.length > 0) {
	        this.showError(response.errors);
	      }
	      this.state = this.STATE.ERROR;
	    });
	    return true;
	  }
	  canEditOnlyThis() {
	    const permissions = this.entry.permissions;
	    if (!permissions) {
	      return false;
	    }
	    return permissions.edit_attendees && permissions.edit_location && !permissions.edit;
	  }
	  handleBusyUsersError(busyUsers) {
	    const users = [];
	    const userIds = [];
	    for (const id in busyUsers) {
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
	      users
	    });
	  }
	  hideWithConfirm(event) {
	    if (!(event && event.getSlider && event.getSlider().getUrl() === this.sliderId)) {
	      return;
	    }
	    if (!this.isAvailable) {
	      this.BX.removeCustomEvent('SidePanel.Slider:onClose', this.sliderOnClose);
	      return;
	    }
	    calendar_util.Util.closeAllPopups();
	    if (this.checkDenyClose()) {
	      event.denyAction();
	      return;
	    }
	    if (this.needToShowConfirmPopup()) {
	      event.denyAction();
	      const message = this.isCreateForm() ? main_core.Loc.getMessage('EC_CLOSE_CREATE_FORM_CONFIRM_QUESTION') : main_core.Loc.getMessage('EC_CLOSE_EDIT_FORM_CONFIRM_QUESTION');
	      calendar_util.Util.showConfirmPopup(this.hide.bind(this), message, {
	        okCaption: main_core.Loc.getMessage('EC_CLOSE_EDIT_FORM_CONFIRM_OK'),
	        minWidth: 350,
	        maxWidth: 350
	      });
	    } else {
	      this.BX.removeCustomEvent('SidePanel.Slider:onClose', this.sliderOnClose);
	    }
	  }
	  hide() {
	    this.doShowConfirmPopup = false;
	    this.slider.close();
	  }
	  destroy(event) {
	    if (event && event.getSlider() && event.getSlider().getUrl() === this.sliderId) {
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
	    const promise = new this.BX.Promise();
	    let entry = this.getCurrentEntry();
	    this.BX.ajax.runAction('calendar.api.calendarajax.getEditEventSlider', {
	      data: {
	        event_id: this.entryId || entry.id,
	        date_from: entry ? calendar_util.Util.formatDate(entry.from) : '',
	        form_type: this.formType,
	        type: (_entry$data$CAL_TYPE = entry.data.CAL_TYPE) != null ? _entry$data$CAL_TYPE : this.type,
	        ownerId: (_entry$data$OWNER_ID = entry.data.OWNER_ID) != null ? _entry$data$OWNER_ID : this.ownerId,
	        entityList: this.participantsEntityList
	      }
	    }).then(response => {
	      if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	        const html = this.BX.util.trim(response.data.html);
	        slider.getData().set('sliderContent', html);
	        const params = response.data.additionalParams;
	        this.updateEntryData(params.entry, {
	          userSettings: this.userSettings,
	          meetSection: params.meetSection
	        });
	        entry = this.getCurrentEntry();
	        this.uid = params.uniqueId;
	        this.editorId = params.editorId;
	        this.formSettings = this.getSettings(params.formSettings || []);
	        this.isCollabUser = params.isCollabUser;
	        let attendeesEntityList = this.formDataValue.attendeesEntityList || params.attendeesEntityList || [];
	        if (!entry.id && this.participantsEntityList.length > 0) {
	          attendeesEntityList = this.participantsEntityList;
	        }
	        if (main_core.Type.isArray(attendeesEntityList)) {
	          attendeesEntityList.forEach(item => {
	            if (item.entityId === 'user' && params.userIndex[item.id]) {
	              if (params.userIndex[item.id].EMAIL_USER) {
	                item.entityType = 'email';
	                item.title = params.userIndex[item.id].DISPLAY_NAME;
	              } else if (params.userIndex[item.id].SHARING_USER) {
	                item.entityType = 'sharing';
	                item.title = params.userIndex[item.id].DISPLAY_NAME;
	              } else {
	                item.entityType = 'employee';
	              }
	            }
	          });
	        }
	        this.setUserSelectorEntityList(attendeesEntityList);
	        this.attendeesPreselectedItems = this.getUserSelectorEntityList().map(item => {
	          return [item.entityId, item.id];
	        });
	        this.setUserSettings(params.userSettings);
	        calendar_util.Util.setEventWithEmailGuestEnabled(params.eventWithEmailGuestEnabled);
	        this.handleSections(params.sections, params.trackingUsersList);
	        this.handleLocationData(params.locationFeatureEnabled, params.locationList, params.iblockMeetingRoomList);
	        this.locationAccess = params.locationAccess;
	        this.plannerFeatureEnabled = Boolean(params.plannerFeatureEnabled);
	        this.isProjectFeatureEnabled = params.projectFeatureEnabled;
	        if (this.planner && !this.plannerFeatureEnabled) {
	          this.planner.lock();
	        }
	        if (!entry.id && !entry.sectionId) {
	          this.setCurrentEntry();
	        }
	        if (this.userSettings.meetSection && this.type === 'user') {
	          calendar_sectionmanager.SectionManager.setNewEntrySectionId(this.userSettings.meetSection);
	        }
	        if (this.isOpenEvent) {
	          var _this$formDataValue, _this$eventOptions;
	          const categoryId = ((_this$formDataValue = this.formDataValue) == null ? void 0 : _this$formDataValue.category) || ((_this$eventOptions = this.eventOptions) == null ? void 0 : _this$eventOptions.CATEGORY_ID);
	          const preSelectedCategoryId = categoryId || params.defaultCategoryId;
	          this.preSelectedCategory = preSelectedCategoryId ? ['event-category', preSelectedCategoryId] : null;
	        }
	        this.timezoneHint = params.timezoneHint;
	        promise.fulfill(html);
	      }
	    }, response => {
	      if (response.data && !main_core.Type.isNil(response.data.isAvailable) && !response.data.isAvailable) {
	        this.isAvailable = false;
	        const showHelperCallback = () => {
	          top.BX.UI.InfoHelper.show('limit_office_calendar_off', {
	            isLimit: true,
	            limitAnalyticsLabels: {
	              module: 'calendar',
	              source: 'eventEditForm'
	            }
	          });
	        };
	        const sliderInstance = BX.SidePanel.Instance.getSlider(this.sliderId);
	        if (sliderInstance) {
	          this.BX.removeCustomEvent('SidePanel.Slider:onClose', this.sliderOnClose);
	          sliderInstance.close(true, showHelperCallback);
	        } else {
	          showHelperCallback();
	        }
	      }
	      const html = this.BX.util.trim('<div></div>');
	      slider.getData().set('sliderContent', html);
	      promise.fulfill(html);
	      // this.calendar.displayError(response.errors);
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
	    if (this.plannerEnabled) {
	      this.initPlanner(uid);
	    }
	    this.initReminderControl(uid);
	    if (this.sectionSelectorEnabled) {
	      this.initSectionSelector(uid);
	    }
	    this.initLocationControl(uid);
	    this.initRepeatRuleControl(uid);
	    this.initColorControl(uid);
	    this.initCrmUfControl(uid);
	    this.initAdditionalControls(uid);
	    this.checkLastItemBorder();
	    if (this.isOpenEvent) {
	      this.initCategoryControl();
	    }
	    if (this.DOM.buttonsWrap) {
	      BX.ZIndexManager.register(this.DOM.buttonsWrap);
	    }
	  }
	  updateEntryData(entryData, options = {}) {
	    if (this.entry instanceof calendar_entry.Entry) {
	      const userSettings = options.userSettings || {};
	      if (main_core.Type.isPlainObject(entryData)) {
	        this.entry.prepareData(entryData);
	      } else if (!this.entry.getTimezoneFrom() || this.entry.getTimezoneTo()) {
	        this.entry.setTimezone(userSettings.timezoneName || userSettings.timezoneDefaultName || null);
	      }
	      if (!this.entry.id && options.meetSection && this.type === calendar_entry.Entry.CAL_TYPES.user) {
	        this.entry.setSectionId(options.meetSection);
	      }
	    }
	  }
	  handleSections(sections, trackingUsersList) {
	    this.sections = calendar_util.Util.filterSectionsByContext(sections, {
	      isCollabUser: this.isCollabUser,
	      calendarType: this.type,
	      calendarOwnerId: this.ownerId
	    });
	    this.sectionIndex = {};
	    this.trackingUsersList = trackingUsersList || [];
	    if (main_core.Type.isArray(this.sections)) {
	      this.sections.forEach((value, ind) => {
	        this.sectionIndex[parseInt(value.ID, 10)] = ind;
	      });
	    }
	    const section = this.getCurrentSection();
	    if (this.entry.id) {
	      this.getSectionsForEditEvent(this.sections, section);
	    }
	  }
	  handleLocationData(locationFeatureEnabled, locationList, iblockMeetingRoomList) {
	    this.locationFeatureEnabled = Boolean(locationFeatureEnabled);
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
	    var _this$repeatSelector;
	    const entry = this.entry;

	    // Date time
	    this.dateTimeControl.setValue({
	      from: this.formDataValue.from || entry.from,
	      to: this.formDataValue.to || entry.to,
	      fullDay: main_core.Type.isBoolean(this.formDataValue.fullDay) ? this.formDataValue.fullDay : entry.fullDay,
	      timezoneFrom: entry.getTimezoneFrom() || '',
	      timezoneTo: entry.getTimezoneTo() || '',
	      timezoneName: this.userSettings.timezoneName
	    });
	    this.initialTimezoneFrom = entry.getTimezoneFrom();
	    this.initialTimezoneTo = entry.getTimezoneTo();
	    if (entry.isSharingEvent() || !this.canEdit()) {
	      this.dateTimeControl.setReadonly(this.timezoneHint);
	    }
	    if (!this.canEdit()) {
	      main_core.Dom.attr(this.DOM.entryName, 'readonly', 'readonly');
	      main_core.Dom.style(this.DOM.entryName, 'pointer-events', 'none');
	    }
	    const entryName = this.formDataValue.name || entry.getName();
	    this.DOM.entryName.value = entryName;
	    this.DOM.entryName.title = entryName;
	    main_core.Event.bind(this.DOM.entryName, 'keyup', this.updateEventNameInputTitle.bind(this));
	    main_core.Event.bind(this.DOM.entryName, 'change', this.updateEventNameInputTitle.bind(this));

	    // Section
	    const section = this.getCurrentSection();
	    this.initialSectionId = this.getCurrentSectionId();
	    if (this.sectionSelectorEnabled) {
	      if (this.formDataValue.section) {
	        entry.sectionId = Number(this.formDataValue.section);
	      }
	      this.DOM.sectionInput.value = this.getCurrentSectionId();
	      this.initialSectionId = this.getCurrentSectionId();
	      this.sectionSelector.updateValue();
	      if (!this.fieldIsPinned('section') && (section.CAL_TYPE !== this.type || section.CAL_TYPE === this.type) && parseInt(section.OWNER_ID, 10) !== this.ownerId) {
	        this.pinField('section');
	      }
	      if ((this.isSyncSection(section) || entry.isSharingEvent()) && entry.id || !this.canEdit()) {
	        this.sectionSelector.setViewMode(true);
	      }
	    }

	    // Color
	    if (this.formDataValue.color) {
	      entry.data.COLOR = this.formDataValue.color;
	    }
	    this.colorSelector.setValue(entry.getColor() || section.COLOR);

	    // Reminders
	    this.remindersControl.setValue(this.formDataValue.reminder || entry.getReminders(), true, false);

	    // Recursion
	    (_this$repeatSelector = this.repeatSelector) == null ? void 0 : _this$repeatSelector.setValue(this.formDataValue.rrule || entry.getRrule());
	    this.initialRrule = this.getFormRrule();
	    if (entry.id && entry.isSharingEvent() || !this.canEdit()) {
	      var _this$repeatSelector2;
	      (_this$repeatSelector2 = this.repeatSelector) == null ? void 0 : _this$repeatSelector2.setViewMode(entry.getRRuleDescription());
	    }
	    if (entry.hasRecurrenceId()) {
	      var _this$repeatSelector3;
	      (_this$repeatSelector3 = this.repeatSelector) == null ? void 0 : _this$repeatSelector3.setViewMode(entry.getRRuleDescription());
	    }

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
	        timezone: this.entry.getTimezoneFrom(),
	        fullDay: main_core.Type.isBoolean(this.formDataValue.fullDay) ? this.formDataValue.fullDay : entry.fullDay,
	        currentEventId: this.entry.id
	      });
	    }

	    //max attendees
	    if (this.DOM.form.max_attendees) {
	      var _entry$data$OPTIONS$O, _entry$data, _entry$data$OPTIONS, _JSON$parse;
	      const optionsJson = (_entry$data$OPTIONS$O = entry == null ? void 0 : (_entry$data = entry.data) == null ? void 0 : (_entry$data$OPTIONS = _entry$data.OPTIONS) == null ? void 0 : _entry$data$OPTIONS.OPTIONS) != null ? _entry$data$OPTIONS$O : null;
	      this.DOM.form.max_attendees.value = ((_JSON$parse = JSON.parse(optionsJson)) == null ? void 0 : _JSON$parse.max_attendees) || '';
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
	    const dateTime = this.dateTimeControl.getValue();
	    if (this.plannerEnabled) {
	      this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay, {
	        focus: true
	      });
	    }
	    if (entry.isSharingEvent() || !this.canEdit()) {
	      this.planner.setReadonly();
	      this.planner.setSolid();
	      this.planner.setShowWorkTimeNotice();
	    }
	    if (!this.canEdit()) {
	      const [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
	      for (const fieldEditableOnlyByPermission of this.getFieldsEditableOnlyByPermission()) {
	        main_core.Dom.style(placeHolders[fieldEditableOnlyByPermission], 'display', 'none');
	        main_core.Dom.style(placeHoldersAdditional[fieldEditableOnlyByPermission], 'display', 'none');
	      }
	      main_core.Dom.style(this.DOM.importantEventCheckbox, 'display', 'none');
	      main_core.Dom.style(this.DOM.importantEventCheckboxContainer, 'display', 'none');
	      main_core.Dom.style(this.DOM.moreSettings, 'display', 'none');
	      main_core.Dom.style(this.DOM.accessibilityInput, 'display', 'none');
	      const accessibilityText = main_core.Tag.render(_t12 || (_t12 = _$1`
				<span class="calendar-field calendar-repeat-selector-readonly">
					${0}
				</span>
			`), this.DOM.accessibilityInput.options[this.DOM.accessibilityInput.selectedIndex].text);
	      this.DOM.accessibilityInput.after(accessibilityText);
	    }
	    if (this.plannerEnabled) {
	      this.loadPlannerData({
	        entityList: this.getUserSelectorEntityList(),
	        from: calendar_util.Util.formatDate(entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: entry.getTimezoneFrom(),
	        location: this.locationSelector.getTextValue()
	      });
	    }
	  }
	  initCategoryControl() {
	    this.DOM.categorySelectorWrap = this.DOM.content.querySelector('.calendar-category-selector-wrap');
	    this.DOM.categorySelectorValueWarp = this.DOM.categorySelectorWrap.appendChild(main_core.Tag.render(_t13 || (_t13 = _$1`<div></div>`)));
	    this.categoryTagSelector = new ui_entitySelector.TagSelector({
	      multiple: false,
	      dialogOptions: {
	        context: 'calendar',
	        preselectedItems: this.preSelectedCategory ? [this.preSelectedCategory] : [],
	        preload: true,
	        zIndex: this.slider.zIndex,
	        multiple: false,
	        entities: [{
	          id: 'event-category',
	          dynamicLoad: true,
	          dynamicSearch: true
	        }],
	        events: {
	          'onLoad': () => {
	            this.categoryTagSelector.getDialog().getItems().forEach(item => item.setDeselectable(false));
	            this.categoryTagSelector.getTags().forEach(tag => tag.render());
	          }
	        }
	      }
	    });
	    this.categoryTagSelector.renderTo(this.DOM.categorySelectorWrap);
	  }
	  updateEventNameInputTitle() {
	    if (this.isTitleOverflowing()) {
	      this.DOM.entryName.title = this.DOM.entryName.value;
	    } else {
	      this.DOM.entryName.title = '';
	    }
	  }
	  isTitleOverflowing() {
	    const el = this.DOM.entryName;
	    return el.clientWidth < el.scrollWidth || el.clientHeight < el.scrollHeight;
	  }
	  switchFullDay(value) {
	    value = Boolean(this.DOM.fullDay.checked);
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
	      const target = e.target || e.srcElement;
	      if (target && target.getAttribute && target.getAttribute('data-bx-fixfield')) {
	        const fieldName = target.getAttribute('data-bx-fixfield');
	        if (this.fieldIsPinned(fieldName)) {
	          this.unPinField(fieldName);
	        } else {
	          this.pinField(fieldName);
	        }
	      }
	    });
	    const fieldButtons = document.querySelectorAll('[data-bx-field-id]');
	    this.bindAdditionalFieldButtons(fieldButtons);
	  }
	  initDateTimeControl(uid) {
	    this.dateTimeControl = new SliderDateTimeControl(uid, {
	      showTimezone: true,
	      outerContent: this.DOM.content
	    });
	    this.dateTimeControl.subscribe('onChange', event => {
	      if (event instanceof main_core_events.BaseEvent) {
	        const value = event.getData().value;
	        this.entry.setTimezone(value.timezoneFrom);
	        if (this.remindersControl) {
	          this.remindersControl.setFullDayMode(value.fullDay);
	          if (!this.entry.id && !this.remindersControl.wasChangedByUser()) {
	            const defaultReminders = calendar_entry.EntryManager.getNewEntryReminders(value.fullDay ? 'fullDay' : 'withTime');
	            this.remindersControl.setValue(defaultReminders, true, false);
	          }
	        }
	        if (this.planner) {
	          this.planner.updateSelector(value.from, value.to, value.fullDay);
	          this.planner.updateTimezone(value.timezoneFrom);
	        }
	        if (this.locationSelector) {
	          this.locationSelector.checkLocationAccessibility({
	            from: value.from,
	            to: value.to,
	            timezone: this.entry.getTimezoneFrom(),
	            fullDay: value.fullDay,
	            currentEventId: this.entry.id
	          });
	        }
	      }
	    });
	  }
	  initNameControl(uid) {
	    this.DOM.entryName = this.DOM.content.querySelector(`#${uid}_entry_name`);
	    if (this.canEdit()) {
	      setTimeout(() => {
	        this.DOM.entryName.focus();
	        this.DOM.entryName.select();
	      }, 500);
	    }
	    let isInputFocus = false;
	    main_core.Event.bind(this.DOM.entryName, 'focusout', () => {
	      if (this.DOM.entryName.scrollWidth > this.DOM.entryName.offsetWidth) {
	        this.getTitleFade(uid).classList.add('--show');
	      } else {
	        this.getTitleFade(uid).classList.remove('--show');
	      }
	      isInputFocus = false;
	    });
	    main_core.Event.bind(this.DOM.entryName, 'focus', () => {
	      this.getTitleFade(uid).classList.remove('--show');
	      isInputFocus = true;
	    });
	    main_core.Event.bind(this.DOM.entryName, 'scroll', () => {
	      if (this.DOM.entryName.scrollWidth > this.DOM.entryName.offsetWidth && Math.ceil(this.DOM.entryName.offsetWidth + this.DOM.entryName.scrollLeft) < this.DOM.entryName.scrollWidth && !isInputFocus) {
	        this.getTitleFade(uid).classList.add('--show');
	      } else {
	        this.getTitleFade(uid).classList.remove('--show');
	      }
	    });
	  }
	  getTitleFade(uid) {
	    if (!this.DOM.entryNameFade) {
	      this.DOM.entryNameFade = this.DOM.content.querySelector(`#${uid}_input_fade`);
	    }
	    return this.DOM.entryNameFade;
	  }
	  initReminderControl(uid) {
	    const reminderWrap = this.DOM.content.querySelector(`#${uid}_reminder`);
	    if (!reminderWrap) {
	      return;
	    }
	    this.reminderValues = [];
	    this.DOM.reminderWrap = reminderWrap;
	    this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(main_core.Tag.render(_t14 || (_t14 = _$1`<span></span>`)));
	    this.remindersControl = new calendar_controls.Reminder({
	      wrap: this.DOM.reminderWrap,
	      zIndex: this.zIndex
	    });
	    this.remindersControl.subscribe('onChange', event => {
	      if (event instanceof main_core_events.BaseEvent) {
	        this.reminderValues = event.getData().values;
	        main_core.Dom.clean(this.DOM.reminderInputsWrap);
	        this.reminderValues.forEach(value => {
	          this.DOM.reminderInputsWrap.appendChild(main_core.Tag.render(_t15 || (_t15 = _$1`
						<input value="${0}" name="reminder[]" type="hidden">
					`), value));
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
	        trackingUsersList: this.trackingUsersList,
	        isCollabUser: this.isCollabUser,
	        isCollabContext: this.isCollabContext()
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
	    if (!window.BXHtmlEditor) {
	      return setTimeout(BX.delegate(this.initEditorControl, this), 50);
	    }
	    this.editor = null;
	    if (window.BXHtmlEditor) {
	      this.editor = window.BXHtmlEditor.Get(this.editorId);
	    }
	    if (!this.editor && top.BXHtmlEditor && top.BXHtmlEditor !== window.BXHtmlEditor) {
	      this.editor = top.BXHtmlEditor.Get(this.editorId);
	    }
	    if (this.editor && this.editor.IsShown()) {
	      this.customizeHtmlEditor();
	      if (this.formDataValue.description) {
	        this.editor.SetContent(this.formDataValue.description);
	      }
	    } else {
	      this.BX.addCustomEvent(window.BXHtmlEditor, 'OnEditorCreated', editor => {
	        if (editor.id === this.editorId) {
	          this.editor = editor;
	          this.customizeHtmlEditor();
	          if (this.formDataValue.description) {
	            this.editor.SetContent(this.formDataValue.description);
	          }
	        }
	      });
	    }
	  }
	  customizeHtmlEditor() {
	    const editor = this.editor;
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
	      hideLocationLock: this.isCollabUser,
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
	    if (this.plannerEnabled) {
	      this.planner.subscribe('onDateChange', () => {
	        if (this.repeatSelector.getType() === 'weekly') {
	          this.repeatSelector.changeType(this.repeatSelector.getType());
	        }
	      });
	    }
	  }
	  initAttendeesControl() {
	    if (this.attendeesControlEnabled) {
	      this.DOM.userSelectorWrap = this.DOM.content.querySelector('.calendar-attendees-selector-wrap');
	      this.DOM.userSelectorValueWarp = this.DOM.userSelectorWrap.appendChild(main_core.Tag.render(_t16 || (_t16 = _$1`<div></div>`)));
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
	          selectedItems: this.getSelectedItemsForTagSelector(),
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
	    }
	    if (this.plannerEnabled) {
	      this.DOM.hideGuestsWrap = this.DOM.content.querySelector('.calendar-hide-members-wrap');
	    }
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
	  getSelectedItemsForTagSelector() {
	    const result = [];
	    const canEdit = this.canEdit();
	    this.getUserSelectorEntityList().forEach(item => {
	      if (item.entityType === 'sharing') {
	        result.push({
	          id: item.id,
	          entityId: item.entityId,
	          entityType: 'extranet',
	          title: item.title,
	          deselectable: false
	        });
	      }
	      if (!canEdit && item.entityType === 'email') {
	        result.push({
	          id: item.id,
	          entityId: item.entityId,
	          entityType: 'email',
	          title: item.title,
	          deselectable: false
	        });
	      }
	    });
	    return result;
	  }
	  hasExternalEmailUsers() {
	    return Boolean(this.getUserSelectorEntityList().find(item => {
	      return item.entityType === 'email';
	    }));
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
	      locked: !this.plannerFeatureEnabled,
	      entryTimezone: this.entry.getTimezoneFrom()
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
	          hostId: this.entry.data.MEETING_HOST || null,
	          type: this.type,
	          // open_event need only location planner
	          entityList: !this.isOpenEvent && params.entityList || [],
	          dateFrom: calendar_util.Util.formatDate(this.planner.scaleDateFrom),
	          dateTo: calendar_util.Util.formatDate(this.planner.scaleDateTo),
	          timezone: params.timezone || '',
	          location: params.location || '',
	          prevUserList: this.prevUserList
	        }
	      }).then(response => {
	        if (this.planner) {
	          for (const id in response.data.accessibility) {
	            if (response.data.accessibility.hasOwnProperty(id)) {
	              this.loadedAccessibilityData[id] = response.data.accessibility[id];
	            }
	          }
	          if (main_core.Type.isArray(response.data.entries)) {
	            response.data.entries.forEach(entry => {
	              const hasAccessibility = this.loadedAccessibilityData[entry.id];
	              if (entry.type === 'user' && !this.prevUserList.includes(parseInt(entry.id)) && hasAccessibility) {
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
	    this.DOM.importantEventCheckboxContainer = this.DOM.importantEventCheckbox.closest('.calendar-info-panel-important');
	    this.DOM.moreSettings = this.DOM.content.querySelector(`#${uid}_more_outer_wrap`);
	  }
	  initColorControl(uid) {
	    this.DOM.colorWrap = this.DOM.content.querySelector(`#${uid}_color_selector_wrap`);
	    this.colorSelector = new calendar_controls.ColorSelector({
	      wrap: this.DOM.colorWrap
	    });
	  }
	  initCrmUfControl(uid) {
	    const crmUfWrap = BX(`${uid}-uf-crm-wrap`);
	    if (!crmUfWrap) {
	      return;
	    }
	    this.DOM.crmUfWrap = crmUfWrap;
	    if (this.DOM.crmUfWrap) {
	      const entry = this.getCurrentEntry();
	      const loader = this.DOM.crmUfWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(50), {
	        style: {
	          height: '40px',
	          width: '40px'
	        }
	      }));
	      this.DOM.saveBtn.disabled = true;
	      setTimeout(() => {
	        this.BX.ajax.runAction('calendar.api.calendarajax.getCrmUserfield', {
	          data: {
	            event_id: entry && entry.id ? entry.id : 0
	          }
	        }).then(
	        // Success
	        response => {
	          if (main_core.Type.isDomNode(this.DOM.crmUfWrap)) {
	            this.BX.html(this.DOM.crmUfWrap, response.data.html);
	            this.DOM.saveBtn.disabled = false;
	          }
	        },
	        // Failure
	        response => {
	          main_core.Dom.remove(loader);
	          this.DOM.saveBtn.disabled = false;
	        });
	      }, 800);
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
	  needToShowConfirmPopup() {
	    return this.doShowConfirmPopup && this.formDataChanged();
	  }
	  setCurrentEntry(entry = null, userIndex = null) {
	    const currentSectionId = this.getCurrentSectionId();
	    this.entry = calendar_entry.EntryManager.getEntryInstance(entry, userIndex, {
	      type: this.type,
	      ownerId: this.ownerId
	    });
	    if (!calendar_util.Util.getCalendarContext() && this.type === 'group') {
	      this.entry.setSectionId(currentSectionId);
	    }
	    calendar_entry.EntryManager.registerEntrySlider(this.entry, this);
	  }
	  getCurrentEntry() {
	    return this.entry;
	  }
	  getCurrentSection() {
	    let section = false;
	    const sectionId = this.getCurrentSectionId();
	    if (sectionId && this.sectionIndex[sectionId] !== undefined && this.sections[this.sectionIndex[sectionId]] !== undefined) {
	      section = this.sections[this.sectionIndex[sectionId]];
	    }
	    return section;
	  }
	  getCurrentSectionId() {
	    let section = 0;
	    const entry = this.getCurrentEntry();
	    if (entry instanceof calendar_entry.Entry && this.sections[this.sectionIndex[entry.sectionId]]) {
	      section = parseInt(entry.sectionId);
	    }
	    if (!section) {
	      if (this.type === 'location') {
	        section = calendar_roomsmanager.RoomsManager.getNewEntrySectionId();
	      } else {
	        if (!calendar_util.Util.getCalendarContext() && this.type === 'group' && this.sections.length) {
	          section = this.getSectionIdByCurrentContext();
	        } else {
	          section = calendar_sectionmanager.SectionManager.getNewEntrySectionId(this.type, this.ownerId);
	        }
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
	    const [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
	    const field = placeHoldersAdditional[fieldName];
	    const newField = placeHolders[fieldName];
	    const fieldHeight = field.offsetHeight;
	    field.style.height = `${fieldHeight}px`;
	    setTimeout(() => {
	      main_core.Dom.addClass(field, 'calendar-hide-field');
	    }, 0);
	    newField.style.height = '0';
	    if (fieldName === 'description') {
	      setTimeout(() => {
	        if (!this.DOM.descriptionAdditionalWrap) {
	          this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
	        }
	        if (this.DOM.descriptionAdditionalWrap) {
	          while (this.DOM.descriptionAdditionalWrap.firstChild) {
	            newField.appendChild(this.DOM.descriptionAdditionalWrap.firstChild);
	          }
	        }
	        newField.style.height = `${fieldHeight}px`;
	      }, 200);
	      setTimeout(() => {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.display = 'none';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = true;
	        const editor = window.BXHtmlEditor.Get(this.editorId);
	        if (editor) {
	          editor.CheckAndReInit();
	        }
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }, 500);
	    } else {
	      setTimeout(() => {
	        while (field.firstChild) {
	          newField.appendChild(field.firstChild);
	        }
	        newField.style.height = `${fieldHeight}px`;
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
	    const [placeHolders, placeHoldersAdditional] = this.getPlaceholders();
	    const field = placeHolders[fieldName];
	    const newField = placeHoldersAdditional[fieldName];
	    const fieldHeight = field.offsetHeight;
	    field.style.height = `${fieldHeight}px`;
	    setTimeout(() => {
	      main_core.Dom.addClass(field, 'calendar-hide-field');
	    }, 0);
	    newField.style.height = '0';
	    if (fieldName === 'description') {
	      setTimeout(() => {
	        if (!this.DOM.descriptionAdditionalWrap) {
	          this.DOM.descriptionAdditionalWrap = this.DOM.additionalBlock.querySelector('.calendar-info-panel-description');
	        }
	        if (this.DOM.descriptionAdditionalWrap) {
	          while (field.firstChild) {
	            this.DOM.descriptionAdditionalWrap.appendChild(field.firstChild);
	          }
	        }
	        newField.style.display = '';
	        newField.style.height = `${fieldHeight}px`;
	      }, 200);
	      setTimeout(() => {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.height = '';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = false;
	        const editor = window.BXHtmlEditor.Get(this.editorId);
	        if (editor) {
	          editor.CheckAndReInit();
	        }
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }, 300);
	    } else {
	      setTimeout(() => {
	        while (field.firstChild) {
	          newField.appendChild(field.firstChild);
	        }
	        newField.style.height = `${fieldHeight}px`;
	      }, 200);
	      setTimeout(() => {
	        main_core.Dom.removeClass(field, 'calendar-hide-field');
	        field.style.height = '';
	        newField.style.height = '';
	        this.pinnedFieldsIndex[fieldName] = false;
	        this.saveSettings();
	        this.updateAdditionalBlockState();
	      }, 300);
	    }
	  }
	  fieldIsPinned(fieldName) {
	    return this.pinnedFieldsIndex[fieldName];
	  }
	  getPlaceholders() {
	    if (!this.placeHolders) {
	      this.placeHolders = {};
	      this.placeHoldersAdditional = {};
	      let i;
	      let fieldId;
	      let nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-additional-placeholder');
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
	    let i;
	    const pinnedFields = [];
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
	    let fieldName;
	    const pinnedFields = [];
	    for (fieldName in this.pinnedFieldsIndex) {
	      if (this.pinnedFieldsIndex.hasOwnProperty(fieldName) && this.pinnedFieldsIndex[fieldName]) {
	        pinnedFields.push(fieldName);
	      }
	    }
	    this.formSettings.pinnedFields = pinnedFields;
	    this.BX.userOptions.save('calendar', this.formType, 'pinnedFields', pinnedFields);
	  }
	  updateAdditionalBlockState(timeout) {
	    if (timeout === false) {
	      main_core.Dom.clean(this.DOM.pinnedNamesWrap);
	      const additionalFields = [...this.DOM.additionalBlock.querySelectorAll('.calendar-field-additional-placeholder[data-bx-block-placeholer]')].filter(field => field.innerText !== '' && field.style.display !== 'none');
	      const fieldButtons = additionalFields.map(field => main_core.Dom.create('SPAN', {
	        attrs: {
	          'data-bx-field-id': field.getAttribute('data-bx-block-placeholer')
	        },
	        props: {
	          className: 'calendar-additional-alt-promo-text'
	        },
	        html: field.querySelector('.js-calendar-field-name').innerText
	      }));
	      this.DOM.pinnedNamesWrap.append(...fieldButtons);
	      this.bindAdditionalFieldButtons(fieldButtons);
	      if (fieldButtons.length === 0) {
	        main_core.Dom.addClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
	      } else if (main_core.Dom.hasClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden')) {
	        main_core.Dom.removeClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
	      }
	      this.checkLastItemBorder();
	    } else {
	      if (this.updateAdditionalBlockTimeout) {
	        clearTimeout(this.updateAdditionalBlockTimeout);
	        this.updateAdditionalBlockTimeout = null;
	      }
	      this.updateAdditionalBlockTimeout = setTimeout(() => {
	        this.updateAdditionalBlockState(false);
	      }, 300);
	    }
	  }
	  bindAdditionalFieldButtons(fieldButtons) {
	    for (const fieldButton of fieldButtons) {
	      const fieldId = fieldButton.getAttribute('data-bx-field-id');
	      if (!this.canEdit() && this.getFieldsEditableOnlyByPermission().includes(fieldId)) {
	        fieldButton.remove();
	        continue;
	      }
	      main_core.Event.bind(fieldButton, 'click', event => {
	        const fieldWrap = document.querySelector(`.calendar-openable-block [data-bx-block-placeholer=${fieldId}]`);
	        this.highlightField(fieldWrap);
	        if (this.isAdditionalBlockOpened()) {
	          event.stopPropagation();
	        }
	      });
	    }
	  }
	  getFieldsEditableOnlyByPermission() {
	    return ['description', 'private', 'crm'];
	  }
	  highlightField(fieldWrap) {
	    main_core.Dom.addClass(fieldWrap, 'calendar-field-highlighted');
	    const fieldAbsoluteTop = this.DOM.content.scrollTop + fieldWrap.getBoundingClientRect().top;
	    this.highlightFieldScrollAnimation = new BX.easing({
	      duration: 400,
	      start: {
	        scroll: this.DOM.content.scrollTop
	      },
	      finish: {
	        scroll: fieldAbsoluteTop
	      },
	      transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
	      step: state => {
	        this.DOM.content.scrollTop = state.scroll;
	      },
	      complete: () => {}
	    });
	    this.highlightFieldScrollAnimation.animate();
	    clearTimeout(fieldWrap.highlightTimeout);
	    fieldWrap.highlightTimeout = setTimeout(() => BX.Dom.removeClass(fieldWrap, 'calendar-field-highlighted'), 2500);
	  }
	  isAdditionalBlockOpened() {
	    return main_core.Dom.hasClass(this.DOM.additionalSwitch, 'opened');
	  }
	  checkLastItemBorder() {
	    const noBorderClass = 'no-border';
	    let i;
	    let nodes;
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
	      const data = event.getData();
	      // Date time
	      this.dateTimeControl.setValue({
	        from: data.dateFrom,
	        to: data.dateTo
	      });
	      if (this.locationSelector) {
	        this.locationSelector.checkLocationAccessibility({
	          from: data.dateFrom,
	          to: data.dateTo,
	          timezone: this.entry.getTimezoneFrom(),
	          fullDay: data.fullDay,
	          currentEventId: this.entry.id
	        });
	      }
	      if (this.planner) {
	        const fromHours = parseInt(data.dateFrom.getHours()) + Math.floor(data.dateFrom.getMinutes() / 60);
	        const toHours = parseInt(data.dateTo.getHours()) + Math.floor(data.dateTo.getMinutes() / 60);
	        if (fromHours !== 0 && fromHours <= this.planner.shownScaleTimeFrom || toHours !== 0 && toHours !== 23 && toHours + 1 >= this.planner.shownScaleTimeTo) {
	          this.planner.updateSelector(data.dateFrom, data.dateTo, data.fullDay);
	        }
	      }
	    }
	  }
	  handleExpandPlannerTimeline(event) {
	    if (event instanceof main_core_events.BaseEvent) {
	      const data = event.getData();
	      if (data.reload) {
	        this.prevUserList = [];
	        const dateTime = this.dateTimeControl.getValue();
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
	    const dateTime = this.dateTimeControl.getValue();
	    this.loadPlannerData({
	      entityList: this.isOpenEvent ? [] : this.getUserSelectorEntityList(),
	      from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	      to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	      timezone: dateTime.timezoneFrom,
	      location: this.locationSelector.getTextValue()
	    });
	  }
	  checkLocationForm(event) {
	    if (!this.isCollabUser && event && event instanceof main_core_events.BaseEvent) {
	      const data = event.getData();
	      const usersCount = data.usersCount;
	      if (this.locationCapacity !== 0) {
	        calendar_controls.Location.setCurrentCapacity(this.locationCapacity);
	        this.locationCapacity = 0;
	      }
	      let locationCapacity = calendar_controls.Location.getCurrentCapacity() || 0;
	      if (this.locationSelector.value.type === undefined && locationCapacity) {
	        locationCapacity = 0;
	        calendar_controls.Location.setCurrentCapacity(0);
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
	    if ((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode === calendar_util.Util.getKeyCode('enter') && this.checkTopSlider() && !this.isAdditionalPopupShown()) {
	      if (this.busyUsersDialog && this.busyUsersDialog.isShown()) {
	        return;
	      }
	      this.save();
	    }
	  }
	  isAdditionalPopupShown() {
	    var _this$DOM$locationRep, _this$DOM$locationRep2;
	    return (_this$DOM$locationRep = this.DOM.locationRepeatBusyErrorPopup) == null ? void 0 : (_this$DOM$locationRep2 = _this$DOM$locationRep.getPopupWindow()) == null ? void 0 : _this$DOM$locationRep2.isShown();
	  }
	  checkTopSlider() {
	    const slider = calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	    return slider && slider.options.type === 'calendar:slider';
	  }
	  showError(errorList) {
	    let errorText = '';
	    if (main_core.Type.isArray(errorList)) {
	      errorList.forEach(error => {
	        if (error.code === 'edit_entry_location_busy' || error.code === 'edit_entry_location_busy_recurrence') {
	          this.locationBusyAlert = calendar_util.Util.showFieldError(error.message, this.DOM.locationWrap, {
	            clearTimeout: 10000
	          });
	          return;
	        }
	        if (error.code === 'edit_entry_location_repeat_busy') {
	          this.showLocationRepeatBusyErrorPopup(error.message);
	          return;
	        }
	        errorText += `${error.message}\n`;
	      });
	    }
	    if (errorText !== '') {
	      alert(errorText);
	    }
	  }
	  showLocationRepeatBusyErrorPopup(message) {
	    if (!this.DOM.locationRepeatBusyErrorPopup) {
	      this.DOM.locationRepeatBusyErrorPopup = calendar_entry.EntryManager.getLocationRepeatBusyErrorPopup({
	        message,
	        onYesCallback: () => {
	          if (main_core.Type.isDomNode(this.locationBusyAlert)) {
	            main_core.Dom.remove(this.locationBusyAlert);
	            this.locationBusyAlert = null;
	          }
	          this.lastUsedSaveOptions.doCheckOccupancy = 'N';
	          this.DOM.locationRepeatBusyErrorPopup.close();
	          this.save(this.lastUsedSaveOptions);
	          this.lastUsedSaveOptions = {};
	        },
	        onCancelCallback: () => {
	          this.lastUsedSaveOptions = {};
	          this.DOM.locationRepeatBusyErrorPopup.close();
	        },
	        onPopupCloseCallback: () => {
	          delete this.DOM.locationRepeatBusyErrorPopup;
	        }
	      });
	      this.DOM.locationRepeatBusyErrorPopup.show();
	    }
	  }
	  getFormDataChanges(excludes = []) {
	    var _this$DOM$form, _this$DOM$form$max_at, _entry$data$OPTIONS$O2, _entry$data2, _entry$data2$OPTIONS, _JSON$parse$max_atten, _JSON$parse2;
	    if (!this.DOM.form) {
	      return [];
	    }
	    const entry = this.entry;
	    const fields = [];

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
	    if (this.sectionSelectorEnabled && !excludes.includes('section') && Number(this.initialSectionId) !== Number(this.DOM.sectionInput.value)) {
	      fields.push('section');
	    }

	    // Access codes
	    if (this.attendeesControlEnabled && !excludes.includes('codes') && this.getUserSelectorEntityList().map(item => {
	      return item.entityId + ':' + item.id;
	    }).join('|') !== entry.getAttendeesEntityList().map(item => {
	      return item.entityId + ':' + item.id;
	    }).join('|')) {
	      fields.push('codes');
	    }
	    if (!excludes.includes('color')) {
	      const entryColor = (entry.data.COLOR || this.getCurrentSection().COLOR).toLowerCase();
	      if (entryColor !== this.colorSelector.getValue().toLowerCase()) {
	        fields.push('color');
	      }
	    }
	    if (!excludes.includes('reminder')) {
	      const reminder = entry.getReminders();
	      if (main_core.Type.isArrayFilled(reminder) && reminder.length !== this.reminderValues.length) {
	        fields.push('reminder');
	      }
	    }
	    if (this.wasRruleChanged()) {
	      fields.push('rrule');
	    }
	    if (this.DOM.privateEventCheckbox && this.DOM.privateEventCheckbox.checked !== entry.isPrivate()) {
	      fields.push('private');
	    }
	    if (this.DOM.importantEventCheckbox && this.DOM.importantEventCheckbox.checked !== entry.important) {
	      fields.push('important');
	    }
	    if (this.DOM.form.tz_from.value !== this.initialTimezoneFrom) {
	      fields.push('tz_from');
	    }
	    if (this.DOM.form.tz_to.value !== this.initialTimezoneTo) {
	      fields.push('tz_to');
	    }
	    const currentUFCrm = entry.data.UF_CRM_CAL_EVENT || [];
	    const newUFCrm = new FormData(this.DOM.form).getAll('UF_CRM_CAL_EVENT[]');
	    if (JSON.stringify(currentUFCrm.sort()) !== JSON.stringify(newUFCrm.sort())) {
	      fields.push('uf_crm');
	    }
	    if (this.DOM.form.meeting_notify && entry.data.MEETING && this.DOM.form.meeting_notify.checked !== entry.data.MEETING.NOTIFY) {
	      fields.push('meeting_notify');
	    }
	    if (this.DOM.accessibilityInput && this.DOM.accessibilityInput.value !== entry.accessibility) {
	      fields.push('accessibility');
	    }
	    const formMaxAttendees = parseInt((_this$DOM$form = this.DOM.form) == null ? void 0 : (_this$DOM$form$max_at = _this$DOM$form.max_attendees) == null ? void 0 : _this$DOM$form$max_at.value, 10) || 0;
	    const optionsJson = (_entry$data$OPTIONS$O2 = entry == null ? void 0 : (_entry$data2 = entry.data) == null ? void 0 : (_entry$data2$OPTIONS = _entry$data2.OPTIONS) == null ? void 0 : _entry$data2$OPTIONS.OPTIONS) != null ? _entry$data$OPTIONS$O2 : null;
	    const eventMaxAttendees = (_JSON$parse$max_atten = (_JSON$parse2 = JSON.parse(optionsJson)) == null ? void 0 : _JSON$parse2.max_attendees) != null ? _JSON$parse$max_atten : 0;
	    if (formMaxAttendees !== eventMaxAttendees) {
	      fields.push('max_attendees');
	    }
	    const currentUfWebDavCalEnv = this.entry.data.UF_WEBDAV_CAL_EVENT || [];
	    const newUfWebDavCalEnv = new FormData(this.DOM.form).getAll('UF_WEBDAV_CAL_EVENT[]').filter(Boolean);
	    if (JSON.stringify(currentUfWebDavCalEnv.sort()) !== JSON.stringify(newUfWebDavCalEnv.sort())) {
	      fields.push('UF_WEBDAV_CAL_EVENT[]');
	    }
	    return fields;
	  }
	  wasRruleChanged() {
	    return JSON.stringify(this.getFormRrule()) !== JSON.stringify(this.initialRrule) && !this.entry.hasRecurrenceId();
	  }
	  getFormRrule() {
	    var _formData$get;
	    const formData = new FormData(this.DOM.form);
	    const endsOn = formData.get('rrule_endson');
	    const FREQ = (_formData$get = formData.get('EVENT_RRULE[FREQ]')) != null ? _formData$get : 'NONE';
	    let INTERVAL = parseInt(formData.get('EVENT_RRULE[INTERVAL]'), 10) || null;
	    let COUNT = null;
	    let UNTIL = null;
	    let BYDAY = null;
	    if (endsOn === 'count') {
	      const defaultCount = 10;
	      COUNT = parseInt(formData.get('EVENT_RRULE[COUNT]'), 10) || defaultCount;
	    }
	    if (endsOn === 'until') {
	      UNTIL = formData.get('EVENT_RRULE[UNTIL]');
	    }
	    if (FREQ === 'NONE') {
	      INTERVAL = COUNT = UNTIL = null;
	    }
	    if (FREQ === 'WEEKLY') {
	      BYDAY = formData.getAll('EVENT_RRULE[BYDAY][]');
	    }
	    return {
	      FREQ,
	      INTERVAL,
	      COUNT,
	      UNTIL,
	      BYDAY
	    };
	  }
	  checkCurrentUsersAccessibility() {
	    return this.getFormDataChanges().includes('date&time');
	  }
	  formDataChanged() {
	    return this.getFormDataChanges().length > 0;
	  }
	  getUserCodes() {
	    const codes = [];
	    const valuesInput = this.DOM.attendeesWrap.querySelectorAll('input[name="EVENT_DESTINATION[]"]');
	    for (const element of valuesInput) {
	      if (!codes.includes(element.value)) {
	        codes.push(element.value);
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
	    if (main_core.Type.isArray(userIdList) && userIdList.length > 0 && this.prevUserList.length > 0) {
	      this.prevUserList = this.prevUserList.filter(userId => !userIdList.includes(userId));
	    }
	  }
	  getParticipantsSelectorEntityList() {
	    if (this.participantsSelectorEntityList && this.participantsSelectorEntityList.length > 0) {
	      return this.participantsSelectorEntityList;
	    }
	    let entityList = [{
	      id: 'user',
	      options: {
	        inviteEmployeeLink: this.canEdit(),
	        inviteGuestLink: this.canEdit(),
	        emailUsers: calendar_util.Util.isEventWithEmailGuestAllowed() && this.canEdit(),
	        analyticsSource: 'calendar',
	        lockGuestLink: !calendar_util.Util.isEventWithEmailGuestAllowed(),
	        lockGuestLinkFeatureId: 'calendar_events_with_email_guests'
	      },
	      filters: [{
	        id: 'calendar.attendeeFilter',
	        options: {
	          isSharingEvent: this.entry.isSharingEvent(),
	          eventId: this.entry.id
	        }
	      }]
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
	    if (this.isProjectFeatureEnabled) {
	      entityList.push({
	        id: 'project'
	      });
	    }
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
	    return section.EXTERNAL_TYPE === 'icloud' || section.EXTERNAL_TYPE === 'google' || section.EXTERNAL_TYPE === 'office365' || section.connectionLinks && section.connectionLinks.length > 0;
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
	      });
	    }
	  }
	  unsetHiddenSection(section, sectionManager) {
	    const sectId = parseInt(section.ID);
	    if (!sectionManager.sectionIsShown(sectId)) {
	      let hiddenSections = sectionManager.getHiddenSections();
	      hiddenSections = hiddenSections.filter(sectionId => {
	        return sectionId !== sectId;
	      });
	      sectionManager.setHiddenSections(hiddenSections);
	      sectionManager.saveHiddenSections();
	    }
	  }
	  isCreateForm() {
	    return !this.isEditForm();
	  }
	  isEditForm() {
	    return parseInt(this.entry.id) > 0;
	  }
	  isCollabContext() {
	    const currentSection = this.getCurrentSection();
	    return currentSection && main_core.Type.isFunction(currentSection.isCollab) ? currentSection.isCollab() : currentSection.IS_COLLAB;
	  }
	  getSectionIdByCurrentContext() {
	    const sectionObj = this.sections.find(section => parseInt(section.OWNER_ID, 10) === this.ownerId && section.CAL_TYPE === this.type);
	    return sectionObj && parseInt(sectionObj.ID, 10);
	  }
	}

	exports.EventEditForm = EventEditForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.UI.EntitySelector,BX,BX.Calendar.Controls,BX.Calendar));
//# sourceMappingURL=eventeditform.bundle.js.map
