this.BX = this.BX || {};
(function (exports,main_core,calendar_controls,calendar_util,calendar_entry,calendar_calendarsection,main_core_events,calendar_planner) {
	'use strict';

	var SliderDateTimeControl =
	/*#__PURE__*/
	function (_DateTimeControl) {
	  babelHelpers.inherits(SliderDateTimeControl, _DateTimeControl);

	  function SliderDateTimeControl() {
	    babelHelpers.classCallCheck(this, SliderDateTimeControl);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SliderDateTimeControl).apply(this, arguments));
	  }

	  babelHelpers.createClass(SliderDateTimeControl, [{
	    key: "create",
	    value: function create() {
	      this.DOM.dateTimeWrap = this.DOM.outerContent.querySelector("#".concat(this.UID, "_datetime_container"));
	      this.DOM.fromDate = this.DOM.outerContent.querySelector("#".concat(this.UID, "_date_from"));
	      this.DOM.toDate = this.DOM.outerContent.querySelector("#".concat(this.UID, "_date_to"));
	      this.DOM.fromTime = this.DOM.outerContent.querySelector("#".concat(this.UID, "_time_from"));
	      this.DOM.toTime = this.DOM.outerContent.querySelector("#".concat(this.UID, "_time_to"));
	      this.fromTimeControl = new calendar_controls.TimeSelector({
	        input: this.DOM.fromTime,
	        onChangeCallback: this.handleTimeFromChange.bind(this)
	      });
	      this.toTimeControl = new calendar_controls.TimeSelector({
	        input: this.DOM.toTime,
	        onChangeCallback: this.handleTimeToChange.bind(this)
	      });
	      this.DOM.fullDay = this.DOM.outerContent.querySelector("#".concat(this.UID, "_date_full_day"));
	      this.DOM.defTimezoneWrap = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_default_wrap"));
	      this.DOM.defTimezone = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_default"));
	      this.DOM.fromTz = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_from"));
	      this.DOM.toTz = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_to"));
	      this.DOM.tzButton = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_btn"));
	      this.DOM.tzOuterCont = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_wrap"));
	      this.DOM.tzCont = this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_inner_wrap"));
	      this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_hint")).title = main_core.Loc.getMessage('EC_EVENT_TZ_HINT');
	      this.DOM.outerContent.querySelector("#".concat(this.UID, "_timezone_default_hint")).title = main_core.Loc.getMessage('EC_EVENT_TZ_DEF_HINT');
	      this.prepareModel();
	      this.bindEventHandlers();
	    }
	  }, {
	    key: "prepareModel",
	    value: function prepareModel() {
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
	  }]);
	  return SliderDateTimeControl;
	}(calendar_controls.DateTimeControl);

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span></span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EventEditForm =
	/*#__PURE__*/
	function () {
	  function EventEditForm() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventEditForm);
	    babelHelpers.defineProperty(this, "DOM", {});
	    babelHelpers.defineProperty(this, "planner", null);
	    babelHelpers.defineProperty(this, "uid", null);
	    babelHelpers.defineProperty(this, "sliderId", "calendar:edit-entry-slider");
	    babelHelpers.defineProperty(this, "zIndex", 3100);
	    babelHelpers.defineProperty(this, "denyClose", false);
	    babelHelpers.defineProperty(this, "formType", 'slider_main');
	    babelHelpers.defineProperty(this, "STATE", {
	      READY: 1,
	      REQUEST: 2,
	      ERROR: 3
	    });
	    babelHelpers.defineProperty(this, "sections", []);
	    babelHelpers.defineProperty(this, "sectionIndex", {});
	    babelHelpers.defineProperty(this, "trackingUsersList", []);
	    babelHelpers.defineProperty(this, "userSettings", {});
	    this.name = options.name || 'eventeditform';
	    this.type = options.type || 'user';
	    this.ownerId = options.ownerId;
	    this.userId = options.userId || parseInt(main_core.Loc.getMessage('USER_ID'));
	    this.entryId = parseInt(options.entryId);
	    this.entry = options.entry || null;
	    this.emitter = new main_core_events.EventEmitter();
	    this.emitter.setEventNamespace('BX.Calendar.EventEditForm');
	    this.BX = calendar_util.Util.getBX();
	    this.formSettings = {
	      pinnedFields: {}
	    };

	    if (!this.ownerId && this.type === 'user') {
	      this.ownerId = parseInt(main_core.Loc.getMessage('USER_ID'));
	    }

	    this.state = this.STATE.READY;
	    this.sliderOnClose = this.hide.bind(this);
	  }

	  babelHelpers.createClass(EventEditForm, [{
	    key: "initInSlider",
	    value: function initInSlider(slider, promiseResolve) {
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
	  }, {
	    key: "show",
	    value: function show() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
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
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return this.opened;
	    }
	  }, {
	    key: "bindEventHandlers",
	    value: function bindEventHandlers() {
	      var _this = this;

	      this.keyHandlerBind = this.keyHandler.bind(this);
	      main_core.Event.bind(document, "click", calendar_util.Util.applyHacksForPopupzIndex);
	      main_core.Event.bind(document, 'keydown', this.keyHandlerBind); // region 'protection from closing slider by accident'

	      this.mouseUpNodeCheck = null;
	      main_core.Event.bind(document, 'mousedown', function (e) {
	        _this.mousedownTarget = e.target || e.srcElement;
	      });
	      main_core.Event.bind(document, 'mouseup', function (e) {
	        var target = e.target || e.srcElement;

	        if (_this.mousedownTarget !== target) {
	          _this.mouseUpNodeCheck = false;
	        }

	        setTimeout(function () {
	          _this.mouseUpNodeCheck = null;
	        }, 0);
	      }); // endregion

	      this.BX.addCustomEvent(window, "onCalendarControlChildPopupShown", this.BX.proxy(this.denySliderClose, this));
	      this.BX.addCustomEvent(window, "onCalendarControlChildPopupClosed", this.BX.proxy(this.allowSliderClose, this));
	    }
	  }, {
	    key: "onLoadSlider",
	    value: function onLoadSlider(event) {
	      var slider = event.getSlider();
	      this.DOM.content = slider.layout.content;
	      this.sliderId = slider.getUrl(); // Used to execute javasctipt and attach CSS from ajax responce

	      this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));
	      this.initControls(this.uid);
	      this.setFormValues();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (!this.checkDenyClose()) {
	        this.state = this.STATE.READY;
	        this.BX.SidePanel.Instance.close();
	      }
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this2 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (this.state === this.STATE.REQUEST) return;
	      options = main_core.Type.isPlainObject(options) ? options : {};

	      if (this.entry.id && this.entry.isRecursive() && !options.confirmed && this.checkForSignificantChanges()) {
	        calendar_entry.EntryManager.showConfirmEditDialog({
	          callback: function callback(params) {
	            _this2.save({
	              recursionMode: params.recursionMode,
	              confirmed: true
	            });
	          }
	        });
	        return false;
	      }

	      main_core.Dom.addClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	      main_core.Dom.addClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	      this.DOM.form.id.value = this.entry.id || 0; // Location

	      this.DOM.form.location.value = this.locationSelector.getTextValue();

	      if (this.editor) {
	        this.editor.SaveContent();
	      }

	      var section = this.getCurrentSection();

	      if (section) {
	        // Color
	        if (section.COLOR.toLowerCase() !== this.colorSelector.getValue().toLowerCase()) {
	          this.DOM.form.color.value = this.colorSelector.getValue();
	        }

	        this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', parseInt(section.ID));
	      }

	      this.DOM.form.current_date_from.value = options.recursionMode ? calendar_util.Util.formatDate(this.entry.from) : '';
	      this.DOM.form.rec_edit_mode.value = options.recursionMode || '';
	      this.state = this.STATE.REQUEST;
	      this.BX.ajax.runAction('calendar.api.calendarajax.editEntry', {
	        data: new FormData(this.DOM.form)
	      }).then(function (response) {
	        main_core.Dom.removeClass(_this2.DOM.saveBtn, _this2.BX.UI.Button.State.CLOCKING);
	        main_core.Dom.removeClass(_this2.DOM.closeBtn, _this2.BX.UI.Button.State.DISABLED);
	        _this2.state = _this2.STATE.READY;

	        if (response.data.entryId) {
	          if (_this2.entry.id) {
	            calendar_entry.EntryManager.showEditEntryNotification(response.data.entryId);
	          } else {
	            calendar_entry.EntryManager.showNewEntryNotification(response.data.entryId);
	          }
	        }

	        _this2.emitter.emit('onSave', new main_core_events.BaseEvent({
	          data: {
	            responseData: response.data,
	            options: options
	          }
	        }));

	        _this2.close();
	      }, function (response) {
	        main_core.Dom.removeClass(_this2.DOM.saveBtn, _this2.BX.UI.Button.State.CLOCKING);
	        main_core.Dom.removeClass(_this2.DOM.closeBtn, _this2.BX.UI.Button.State.DISABLED);

	        if (response.data && main_core.Type.isPlainObject(response.data.busyUsersList)) {
	          _this2.handleBusyUsersError(response.data.busyUsersList);

	          var errors = [];
	          response.errors.forEach(function (error) {
	            if (error.code !== "edit_entry_user_busy") {
	              errors.push(error);
	            }
	          });
	          response.errors = errors;
	        }

	        if (response.errors && response.errors.length) {
	          _this2.showError(response.errors);
	        }

	        _this2.state = _this2.STATE.ERROR;
	      });
	    }
	  }, {
	    key: "handleBusyUsersError",
	    value: function handleBusyUsersError(busyUsers) {
	      var _this3 = this;

	      var users = [],
	          userIds = [];

	      for (var id in busyUsers) {
	        if (busyUsers.hasOwnProperty(id)) {
	          users.push(busyUsers[id]);
	          userIds.push(id);
	        }
	      }

	      this.busyUsersDialog = new calendar_controls.BusyUsersDialog();
	      this.busyUsersDialog.subscribe('onSaveWithout', function () {
	        _this3.DOM.form.exclude_users.value = userIds.join(',');

	        _this3.save();
	      });
	      this.busyUsersDialog.show({
	        users: users
	      });
	    }
	  }, {
	    key: "clientSideCheck",
	    value: function clientSideCheck() {}
	  }, {
	    key: "hide",
	    value: function hide(event) {
	      if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	        if (this.checkDenyClose()) {
	          event.denyAction();
	        } else {
	          this.BX.removeCustomEvent("SidePanel.Slider::onClose", this.sliderOnClose);
	          if (this.attendeesSelector) this.attendeesSelector.closeAll();
	          this.destroy(event);
	        }
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(event) {
	      if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	        // if (window.LHEPostForm && window.LHEPostForm.unsetHandler && LHEPostForm.getHandler(this.editorId))
	        // {
	        // 	window.LHEPostForm.unsetHandler(this.editorId);
	        // }
	        this.BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{
	          plannerId: this.plannerId
	        }]);
	        this.BX.removeCustomEvent('OnDestinationAddNewItem', this.BX.proxy(this.checkPlannerState, this));
	        this.BX.removeCustomEvent('OnDestinationUnselect', this.BX.proxy(this.checkPlannerState, this)); //this.BX.removeCustomEvent('OnCalendarPlannerSelectorChanged',
	        // this.BX.proxy(this.onCalendarPlannerSelectorChanged, this));

	        main_core.Event.unbind(document, 'keydown', this.keyHandlerBind); //this.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this));

	        this.BX.SidePanel.Instance.destroy(this.sliderId); //if (this.attendeesSelector)
	        //	this.attendeesSelector.closeAll();
	        //this.calendar.enableKeyHandler();

	        main_core.Event.unbind(document, "click", calendar_util.Util.applyHacksForPopupzIndex);
	        this.planner = null;
	        this.opened = false;
	      }
	    }
	  }, {
	    key: "createContent",
	    value: function createContent(slider) {
	      var _this4 = this;

	      var promise = new this.BX.Promise();
	      var entry = this.getCurrentEntry();
	      this.BX.ajax.runAction('calendar.api.calendarajax.getEditEventSlider', {
	        data: {
	          event_id: this.entryId || entry.id,
	          date_from: entry ? calendar_util.Util.formatDate(entry.from) : '',
	          form_type: this.formType,
	          type: this.type,
	          ownerId: this.ownerId
	        }
	      }).then( // Success
	      function (response) {
	        if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	          var html = _this4.BX.util.trim(response.data.html);

	          slider.getData().set("sliderContent", html);
	          var params = response.data.additionalParams;
	          _this4.uid = params.uniqueId;
	          _this4.editorId = params.editorId;
	          _this4.lastUsedSection = params.lastSection;
	          _this4.socnetDestination = params.socnetDestination || {};
	          _this4.formSettings = _this4.getSettings(params.formSettings || []);

	          _this4.setUserSettings(params.userSettings);

	          _this4.handleSections(params.sections, params.trackingUsersList);

	          _this4.handleLocationData(params.locationFeatureEnabled, params.locationList, params.iblockMeetingRoomList);

	          if (!entry.id && !entry.sectionId) {
	            _this4.setCurrentEntry();
	          }

	          _this4.updateEntryData(params.entry, {
	            userSettings: _this4.userSettings
	          });

	          promise.fulfill(html);

	          if (window.top.BX !== window.BX) {
	            // TODO: Dirty Hack! Must get rid of it
	            window.top.BX.loadCSS(['/bitrix/components/bitrix/calendar.grid/templates/.default/style.css', '/bitrix/js/calendar/new/calendar.css', '/bitrix/js/calendar/cal-style.css']);
	          }
	        }
	      }, // Failure
	      function (response) {//this.calendar.displayError(response.errors);
	      });
	      return promise;
	    }
	  }, {
	    key: "initControls",
	    value: function initControls(uid) {
	      this.DOM.title = this.DOM.content.querySelector("#".concat(uid, "_title"));
	      this.DOM.formWrap = this.DOM.content.querySelector("#".concat(uid, "_form_wrap"));
	      this.DOM.form = this.DOM.content.querySelector("#".concat(uid, "_form"));
	      this.DOM.saveBtn = this.DOM.content.querySelector("#".concat(uid, "_save"));
	      this.DOM.closeBtn = this.DOM.content.querySelector("#".concat(uid, "_close"));
	      main_core.Event.bind(this.DOM.saveBtn, 'click', this.save.bind(this));
	      main_core.Event.bind(this.DOM.closeBtn, 'click', this.close.bind(this));
	      this.DOM.content.querySelector("#".concat(uid, "_save_cmd")).innerHTML = main_core.Browser.isMac() ? '(Cmd+Enter)' : '(Ctrl+Enter)';
	      this.initFormFieldManager(uid);
	      this.initDateTimeControl(uid);
	      this.initNameControl(uid);
	      this.initEditorControl(uid);
	      this.initAttendeesControl(uid);
	      this.initPlanner(uid);
	      this.initReminderControl(uid);
	      this.initSectionSelector(uid);
	      this.initLocationControl(uid);
	      this.initRepeatRuleControl(uid);
	      this.initColorControl(uid);
	      this.initCrmUfControl(uid);
	      this.initAdditionalControls(uid);
	      this.checkLastItemBorder();
	    }
	  }, {
	    key: "updateEntryData",
	    value: function updateEntryData(entryData) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (this.entry instanceof calendar_entry.Entry) {
	        var userSettings = options.userSettings || {};

	        if (main_core.Type.isPlainObject(entryData)) {
	          this.entry.prepareData(entryData);
	        } else {
	          if (!this.entry.getTimezoneFrom() || this.entry.getTimezoneTo()) {
	            this.entry.setTimezone(userSettings.timezoneName || userSettings.timezoneDefaultName || null);
	          }
	        }
	      }
	    }
	  }, {
	    key: "handleSections",
	    value: function handleSections(sections, trackingUsersList) {
	      var _this5 = this;

	      this.sections = sections;
	      this.sectionIndex = {};
	      this.trackingUsersList = trackingUsersList || [];

	      if (main_core.Type.isArray(sections)) {
	        sections.forEach(function (value, ind) {
	          _this5.sectionIndex[parseInt(value.ID)] = ind;
	        }, this);
	      }
	    }
	  }, {
	    key: "handleLocationData",
	    value: function handleLocationData(locationFeatureEnabled, locationList, iblockMeetingRoomList) {
	      this.locationFeatureEnabled = !!locationFeatureEnabled;
	      this.locationList = locationList || [];
	      this.iblockMeetingRoomList = iblockMeetingRoomList || [];
	      calendar_controls.Location.setLocationList(locationList);
	      calendar_controls.Location.setMeetingRoomList(iblockMeetingRoomList);
	    }
	  }, {
	    key: "setUserSettings",
	    value: function setUserSettings(userSettings) {
	      this.userSettings = userSettings;
	    }
	  }, {
	    key: "setFormValues",
	    value: function setFormValues() {
	      var entry = this.entry; // Date time

	      this.dateTimeControl.setValue({
	        from: entry.from,
	        to: entry.to,
	        fullDay: entry.fullDay,
	        timezoneFrom: entry.getTimezoneFrom() || '',
	        timezoneTo: entry.getTimezoneTo() || '',
	        timezoneName: this.userSettings.timezoneName
	      });
	      this.DOM.entryName.value = entry.getName(); // Section

	      this.DOM.sectionInput.value = this.getCurrentSectionId();
	      this.sectionSelector.updateValue();

	      if (!this.fieldIsPinned('section')) {
	        var currentSection = this.getCurrentSection();

	        if (currentSection['CAL_TYPE'] !== this.type || currentSection['CAL_TYPE'] === this.type && parseInt(currentSection['OWNER_ID']) !== this.ownerId) {
	          this.pinField('section');
	        }
	      } // Color


	      this.colorSelector.setValue(entry.getColor() || this.getCurrentSection().COLOR); // Reminders

	      this.reminderControl.setValue(entry.getReminders()); // Recursion

	      this.repeatSelector.setValue(entry.getRrule()); // accessibility

	      if (this.DOM.accessibilityInput) {
	        this.DOM.accessibilityInput.value = entry.accessibility;
	      } // Location


	      this.locationSelector.setValue(entry.getLocation()); // Private

	      if (this.DOM.privateEventCheckbox) {
	        this.DOM.privateEventCheckbox.checked = entry.private;
	      } // Importance


	      if (this.DOM.importantEventCheckbox) {
	        this.DOM.importantEventCheckbox.checked = entry.important;
	      }

	      if (this.userSelector) {
	        this.userSelector.setValue(entry.getAttendeesCodes());
	      }

	      this.loadPlannerData({
	        codes: entry.getAttendeesCodes(),
	        from: calendar_util.Util.formatDate(entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: entry.getTimezoneFrom(),
	        location: this.locationSelector.getTextValue()
	      });
	    }
	  }, {
	    key: "switchFullDay",
	    value: function switchFullDay(value) {
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

	      if (this.reminderControl) {
	        this.reminderControl.setFullDayMode(value);
	      }

	      this.refreshPlannerState();
	    }
	  }, {
	    key: "switchTimezone",
	    value: function switchTimezone() {
	      if (main_core.Dom.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse')) {
	        main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	        main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	      } else {
	        main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	        main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	      }
	    }
	  }, {
	    key: "initFormFieldManager",
	    value: function initFormFieldManager(uid) {
	      var _this6 = this;

	      this.DOM.mainBlock = this.DOM.content.querySelector("#".concat(uid, "_main_block_wrap"));
	      this.DOM.additionalBlockWrap = this.DOM.content.querySelector("#".concat(uid, "_additional_block_wrap"));
	      this.DOM.additionalBlock = this.DOM.content.querySelector("#".concat(uid, "_additional_block"));
	      this.DOM.pinnedNamesWrap = this.DOM.content.querySelector("#".concat(uid, "_additional_pinned_names"));
	      this.DOM.additionalSwitch = this.DOM.content.querySelector("#".concat(uid, "_additional_switch"));
	      main_core.Event.bind(this.DOM.additionalSwitch, 'click', function () {
	        main_core.Dom.toggleClass(_this6.DOM.additionalSwitch, 'opened');
	        main_core.Dom.toggleClass(_this6.DOM.additionalBlock, 'invisible');
	      });
	      main_core.Event.bind(this.DOM.formWrap, 'click', function (e) {
	        var target = e.target || e.srcElement;

	        if (target && target.getAttribute && target.getAttribute('data-bx-fixfield')) {
	          var fieldName = target.getAttribute('data-bx-fixfield');

	          if (!_this6.fieldIsPinned(fieldName)) {
	            _this6.pinField(fieldName);
	          } else {
	            _this6.unPinField(fieldName);
	          }
	        }
	      });
	    }
	  }, {
	    key: "initDateTimeControl",
	    value: function initDateTimeControl(uid) {
	      this.dateTimeControl = new SliderDateTimeControl(uid, {
	        showTimezone: true,
	        outerContent: this.DOM.content
	      });
	    }
	  }, {
	    key: "initNameControl",
	    value: function initNameControl(uid) {
	      var _this7 = this;

	      this.DOM.entryName = this.DOM.content.querySelector("#".concat(uid, "_entry_name"));
	      setTimeout(function () {
	        _this7.DOM.entryName.focus();

	        _this7.DOM.entryName.select();
	      }, 500);
	    }
	  }, {
	    key: "initReminderControl",
	    value: function initReminderControl(uid) {
	      var _this8 = this;

	      this.reminderValues = [];
	      this.DOM.reminderWrap = this.DOM.content.querySelector("#".concat(uid, "_reminder"));
	      this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(main_core.Tag.render(_templateObject()));
	      this.reminderControl = new calendar_controls.Reminder({
	        wrap: this.DOM.reminderWrap,
	        zIndex: this.zIndex // showPopupCallBack: function()
	        // {
	        // 	//_this.denySliderClose();
	        // },
	        // hidePopupCallBack: function()
	        // {
	        // 	//_this.allowSliderClose();
	        // }

	      });
	      this.reminderControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          _this8.reminderValues = event.getData().values;
	          main_core.Dom.clean(_this8.DOM.reminderInputsWrap);

	          _this8.reminderValues.forEach(function (value) {
	            _this8.DOM.reminderInputsWrap.appendChild(main_core.Dom.create('INPUT', {
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

	      if (this.dateTimeControl) {
	        this.dateTimeControl.subscribe('onChange', function (event) {
	          if (event instanceof main_core_events.BaseEvent) {
	            var value = event.getData().value;
	            this.reminderControl.setFullDayMode(value.fullDay);

	            if (this.newPlanner) {
	              this.newPlanner.updateSelector(value.from, value.to, value.fullDay);
	            }
	          }
	        }.bind(this));
	      }
	    }
	  }, {
	    key: "initSectionSelector",
	    value: function initSectionSelector(uid) {
	      var _this9 = this;

	      this.DOM.sectionInput = this.DOM.content.querySelector("#".concat(uid, "_section"));
	      this.sectionSelector = new calendar_controls.SectionSelector({
	        outerWrap: this.DOM.content.querySelector("#".concat(uid, "_section_wrap")),
	        defaultCalendarType: this.type,
	        defaultOwnerId: this.ownerId,
	        sectionList: this.sections,
	        sectionGroupList: calendar_calendarsection.CalendarSectionManager.getSectionGroupList({
	          type: this.type || 'user',
	          ownerId: this.ownerId || this.userId,
	          userId: this.userId,
	          trackingUsersList: this.trackingUsersList
	        }),
	        mode: 'full',
	        zIndex: this.zIndex,
	        getCurrentSection: function getCurrentSection() {
	          var section = _this9.getCurrentSection();

	          if (section) {
	            return {
	              id: section.ID,
	              name: section.NAME,
	              color: section.COLOR
	            };
	          }

	          return false;
	        },
	        selectCallback: function selectCallback(sectionValue) {
	          if (sectionValue) {
	            //this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', sectionValue.id);
	            _this9.DOM.sectionInput.value = sectionValue.id;

	            if (_this9.colorSelector) {
	              _this9.colorSelector.setValue(sectionValue.color);
	            }

	            _this9.entry.setSectionId(sectionValue.id);
	          }
	        }
	      });
	    }
	  }, {
	    key: "initEditorControl",
	    value: function initEditorControl(uid) {
	      if (!window["BXHtmlEditor"]) {
	        return setTimeout(BX.delegate(this.initEditorControl, this), 50);
	      }

	      this.editor = null;

	      if (window["BXHtmlEditor"]) {
	        this.editor = window["BXHtmlEditor"].Get(this.editorId);
	      }

	      if (!this.editor && top["BXHtmlEditor"] !== window["BXHtmlEditor"]) {
	        this.editor = top["BXHtmlEditor"].Get(this.editorId);
	      }

	      if (this.editor && this.editor.IsShown()) {
	        this.customizeHtmlEditor();
	      } else {
	        this.BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function (editor) {
	          if (editor.id === this.editorId) {
	            this.editor = editor;
	            this.customizeHtmlEditor();
	          }
	        }.bind(this));
	      }
	    }
	  }, {
	    key: "customizeHtmlEditor",
	    value: function customizeHtmlEditor() {
	      var editor = this.editor;

	      if (editor.toolbar && editor.toolbar.controls && editor.toolbar.controls.spoiler) {
	        main_core.Dom.remove(editor.toolbar.controls.spoiler.pCont);
	      }
	    }
	  }, {
	    key: "initLocationControl",
	    value: function initLocationControl(uid) {
	      this.DOM.locationWrap = this.DOM.content.querySelector("#".concat(uid, "_location_wrap"));
	      this.DOM.locationInput = this.DOM.content.querySelector("#".concat(uid, "_location"));
	      this.locationSelector = new calendar_controls.Location({
	        inputName: 'lo_cation',
	        // don't use 'location' word here mantis:107863
	        wrap: this.DOM.locationWrap,
	        richLocationEnabled: this.locationFeatureEnabled,
	        locationList: this.locationList,
	        iblockMeetingRoomList: this.iblockMeetingRoomList,
	        onChangeCallback: this.checkPlannerState.bind(this)
	      });
	    }
	  }, {
	    key: "initRepeatRuleControl",
	    value: function initRepeatRuleControl(uid) {
	      var _this10 = this;

	      this.DOM.rruleWrap = this.DOM.content.querySelector("#".concat(uid, "_rrule_wrap"));
	      this.repeatSelector = new calendar_controls.RepeatSelector({
	        wrap: this.DOM.rruleWrap,
	        rruleType: this.DOM.content.querySelector("#".concat(uid, "_rrule_type")),
	        getDate: function () {
	          return this.dateTimeControl.getValue().from;
	        }.bind(this)
	      });
	      this.dateTimeControl.subscribe('onChange', function () {
	        if (_this10.repeatSelector.getType() === 'weekly') {
	          _this10.repeatSelector.changeType(_this10.repeatSelector.getType());
	        }
	      });
	    }
	  }, {
	    key: "initAttendeesControl",
	    value: function initAttendeesControl(uid) {
	      var _this11 = this;

	      // return;
	      // if (!this.calendar.util.isMeetingsEnabled())
	      // {
	      // 	Dom.remove(this.DOM.formWrap.querySelector('.calendar-options-item-destination'));
	      // 	Dom.remove(this.DOM.formWrap.querySelector('.calendar-options-item-planner'));
	      // 	return;
	      // }
	      this.DOM.attendeesWrap = this.DOM.content.querySelector("#".concat(uid, "_attendees_wrap"));
	      this.DOM.plannerWrap = this.DOM.content.querySelector("#".concat(uid, "_planner_wrap"));
	      this.DOM.attendeesTitle = this.DOM.content.querySelector("#".concat(uid, "_attendees_title_wrap"));
	      this.plannerId = uid + '_slider_planner'; // let attendeesCodes = null;
	      // if (this.entry.id)
	      // {
	      // 	this.DOM.attendeesCodesInput = BX(this.id + '_attendees_codes');
	      // 	if (this.DOM.attendeesCodesInput && this.DOM.attendeesCodesInput.value)
	      // 	{
	      // 		this.attendeesCodes = this.DOM.attendeesCodesInput.value.split(',');
	      // 	}
	      // }
	      //
	      // if (!this.attendeesCodes)
	      // {
	      // 	this.attendeesCodes = this.entry.getAttendeesCodes ? this.entry.getAttendeesCodes() : (this.entry.attendeesCodes || false);
	      // }

	      this.userSelector = new calendar_controls.UserSelector({
	        wrapNode: this.DOM.attendeesWrap,
	        items: this.getUserSelectorConfig('items'),
	        itemsSelected: this.getUserSelectorConfig('itemsSelected'),
	        itemsLast: this.getUserSelectorConfig('itemsLast')
	      }); // this.attendees = this.entry.attendees || [this.calendar.currentUser];
	      // this.attendeesIndex = {};
	      // this.attendees.forEach(function(userId){this.attendeesIndex[userId] = true;}, this);
	      // this.attendeesCodes = null;
	      // if (this.entry.id)
	      // {
	      // 	this.DOM.attendeesCodesInput = BX(this.id + '_attendees_codes');
	      // 	if (this.DOM.attendeesCodesInput && this.DOM.attendeesCodesInput.value)
	      // 	{
	      // 		this.attendeesCodes = this.DOM.attendeesCodesInput.value.split(',');
	      // 	}
	      // }
	      //
	      // if (!this.attendeesCodes)
	      // {
	      // 	this.attendeesCodes = this.entry.getAttendeesCodes ? this.entry.getAttendeesCodes() : (this.entry.attendeesCodes || false);
	      // }

	      this.DOM.plannerWrap = this.DOM.content.querySelector("#".concat(uid, "_planner_wrap1"));
	      this.DOM.attendeesTitle = this.DOM.content.querySelector("#".concat(uid, "_attendees_title_wrap"));
	      this.plannerId = uid + '_slider_planner';
	      setTimeout(function () {
	        _this11.BX.addCustomEvent('OnDestinationAddNewItem', _this11.checkPlannerState.bind(_this11));

	        _this11.BX.addCustomEvent('OnDestinationUnselect', _this11.checkPlannerState.bind(_this11));
	      }, 100); //BX.addCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));
	      //BX.addCustomEvent('OnCalendarPlannerUpdated', BX.proxy(this.onCalendarPlannerUpdatedHandler, this));

	      this.DOM.moreOuterWrap = BX(this.id + '_more_outer_wrap'); //this.DOM.moreLink = BX.adjust(BX(this.id + '_more'), {events: {click: BX.delegate(function(){BX.toggleClass(this.DOM.moreWrap, 'collapse');}, this)}});
	      //this.DOM.moreWrap = BX(this.id + '_more_wrap');

	      if (this.DOM.form.allow_invite) {
	        if (this.entry.data) this.DOM.form.allow_invite.checked = this.entry.data.MEETING && this.entry.data.MEETING.ALLOW_INVITE;else this.DOM.form.allow_invite.checked = this.entry.allowInvite;
	      }

	      if (this.DOM.form.meeting_notify) {
	        if (this.entry.data && this.entry.data.MEETING) this.DOM.form.meeting_notify.checked = this.entry.data.MEETING.NOTIFY;else this.DOM.form.meeting_notify.checked = true; // default value
	      }

	      this.dateTimeControl.subscribe('onChange', function (event) {// TODO: we don't need to do additional request here. Need to check it
	        //this.checkPlannerState();
	      }.bind(this));
	    }
	  }, {
	    key: "initPlanner",
	    value: function initPlanner(uid) {
	      this.DOM.plannerOuterWrap = this.DOM.content.querySelector("#".concat(uid, "_planner_outer_wrap"));
	      this.newPlanner = new calendar_planner.Planner({
	        wrap: this.DOM.plannerOuterWrap,
	        minWidth: parseInt(this.DOM.plannerOuterWrap.offsetWidth)
	      });
	      this.newPlanner.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
	      this.newPlanner.show();
	      this.newPlanner.showLoader();
	    }
	  }, {
	    key: "loadPlannerData",
	    value: function loadPlannerData() {
	      var _this12 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.newPlanner.showLoader();
	      return new Promise(function (resolve) {
	        _this12.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	          data: {
	            entryId: _this12.entry.id || 0,
	            codes: params.codes || [],
	            dateFrom: params.from || '',
	            dateTo: params.to || '',
	            timezone: params.timezone || '',
	            location: params.location || '',
	            entries: params.entrieIds || false
	          }
	        }).then(function (response) {
	          _this12.newPlanner.hideLoader();

	          var attendees = [],
	              entries = response.data.entries;

	          if (main_core.Type.isArray(response.data.entries)) {
	            response.data.entries.forEach(function (entry) {
	              if (entry.type === 'user') {
	                attendees.push({
	                  id: entry.id,
	                  name: entry.name,
	                  avatar: entry.avatar,
	                  smallAvatar: entry.smallAvatar || entry.avatar,
	                  url: entry.url
	                });
	              }
	            });
	          }

	          var dateTime = _this12.dateTimeControl.getValue();

	          _this12.newPlanner.update(response.data.entries, response.data.accessibility);

	          _this12.newPlanner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay); // let
	          // 	i,
	          // 	attendees = [],
	          // 	attendeesIndex = {},
	          // 	updateAttendeesControl = false,
	          // 	entries = response.data.entries,
	          // 	accessibility = response.data.accessibility,
	          // 	showPlanner = !!(params.entries || (entries && entries.length > 0));
	          //
	          // if (Type.isArray(entries))
	          // {
	          // 	for (i = 0; i < entries.length; i++)
	          // 	{
	          // 		if (entries[i].type === 'user')
	          // 		{
	          // 			attendees.push({
	          // 				id: entries[i].id,
	          // 				name: entries[i].name,
	          // 				avatar: entries[i].avatar,
	          // 				smallAvatar: entries[i].smallAvatar || entries[i].avatar,
	          // 				url: entries[i].url
	          // 			});
	          // 			attendeesIndex[entries[i].id] = true;
	          // 		}
	          // 	}
	          // }


	          resolve(response); // // Show first time or refresh it state
	          // if (showPlanner)
	          // {
	          // 	let refreshParams = {};
	          //
	          // 	if (params.entries)
	          // 	{
	          // 		entries = params.entries;
	          // 		refreshParams.scaleFrom = params.from;
	          // 		refreshParams.scaleTo = params.to;
	          // 	}
	          //
	          // 	refreshParams.loadedDataFrom = params.from;
	          // 	refreshParams.loadedDataTo = params.to;
	          //
	          // 	refreshParams.data = {
	          // 		entries: entries,
	          // 		accessibility: accessibility
	          // 	};
	          //
	          // 	refreshParams.focusSelector = params.focusSelector === undefined ? false : params.focusSelector;
	          // 	this.refreshPlanner(refreshParams);
	          // }
	        }, function (response) {
	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "initAdditionalControls",
	    value: function initAdditionalControls(uid) {
	      this.DOM.accessibilityInput = this.DOM.content.querySelector("#".concat(uid, "_accessibility"));
	      this.DOM.privateEventCheckbox = this.DOM.content.querySelector("#".concat(uid, "_private"));
	      this.DOM.importantEventCheckbox = this.DOM.content.querySelector("#".concat(uid, "_important"));
	    }
	  }, {
	    key: "initColorControl",
	    value: function initColorControl(uid) {
	      this.DOM.colorWrap = this.DOM.content.querySelector("#".concat(uid, "_color_selector_wrap"));
	      this.colorSelector = new calendar_controls.ColorSelector({
	        wrap: this.DOM.colorWrap
	      });
	    }
	  }, {
	    key: "initCrmUfControl",
	    value: function initCrmUfControl(uid) {
	      this.DOM.crmUfWrap = BX(uid + '-uf-crm-wrap');

	      if (this.DOM.crmUfWrap) {
	        var entry = this.getCurrentEntry();
	        var loader = this.DOM.crmUfWrap.appendChild(main_core.Dom.adjust(calendar_util.Util.getLoader(50), {
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
	          }).then( // Success
	          function (response) {
	            if (main_core.Type.isDomNode(this.DOM.crmUfWrap)) {
	              this.BX.html(this.DOM.crmUfWrap, response.data.html);
	            }
	          }.bind(this), // Failure
	          function (response) {
	            main_core.Dom.remove(loader);
	          }.bind(this));
	        }.bind(this), 800);
	      }
	    }
	  }, {
	    key: "denySliderClose",
	    value: function denySliderClose() {
	      this.denyClose = true;
	    }
	  }, {
	    key: "allowSliderClose",
	    value: function allowSliderClose() {
	      this.denyClose = false;
	    }
	  }, {
	    key: "checkDenyClose",
	    value: function checkDenyClose() {
	      // pending request
	      if (this.state === this.STATE.REQUEST) {
	        return true;
	      } // Check if closing of slider was caused during selection.


	      if (!main_core.Type.isNull(this.mouseUpNodeCheck)) {
	        return !this.mouseUpNodeCheck;
	      } // if (top.BX(this.id + '_time_from_div') && top.BX(this.id + '_time_from_div').style.display !== 'none')
	      // 	return true;
	      //
	      // if (top.BX(this.id + '_time_to_div') && top.BX(this.id + '_time_to_div').style.display !== 'none')
	      // 	return true;


	      return this.denyClose;
	    }
	  }, {
	    key: "setCurrentEntry",
	    value: function setCurrentEntry() {
	      var entry = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var userIndex = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      this.entry = calendar_entry.EntryManager.getEntryInstance(entry, userIndex);
	    }
	  }, {
	    key: "getCurrentEntry",
	    value: function getCurrentEntry() {
	      return this.entry;
	    }
	  }, {
	    key: "getCurrentSection",
	    value: function getCurrentSection() {
	      var section = false,
	          sectionId = this.getCurrentSectionId();

	      if (sectionId && this.sectionIndex[sectionId] !== undefined && this.sections[this.sectionIndex[sectionId]] !== undefined) {
	        section = this.sections[this.sectionIndex[sectionId]];
	      }

	      return section;
	    }
	  }, {
	    key: "getCurrentSectionId",
	    value: function getCurrentSectionId() {
	      var section = 0,
	          entry = this.getCurrentEntry();

	      if (entry instanceof calendar_entry.Entry) {
	        section = parseInt(entry.sectionId);
	      }

	      if (!section && this.lastUsedSection && this.sections[this.sectionIndex[parseInt(this.lastUsedSection)]]) {
	        section = parseInt(this.lastUsedSection);
	      }

	      if (!section && this.sections[0]) {
	        section = parseInt(this.sections[0].ID);
	      }

	      return section;
	    }
	  }, {
	    key: "getUserSelectorConfig",
	    value: function getUserSelectorConfig(key) {
	      var userSelectorConfig;

	      if (key === 'items') {
	        userSelectorConfig = {
	          users: this.socnetDestination.USERS || {},
	          groups: this.socnetDestination.EXTRANET_USER === 'Y' || this.socnetDestination.DENY_TOALL ? {} : {
	            UA: {
	              id: 'UA',
	              name: this.socnetDestination.DEPARTMENT ? main_core.Loc.getMessage('EC_SOCNET_DESTINATION_4') : main_core.Loc.getMessage('EC_SOCNET_DESTINATION_3')
	            }
	          },
	          sonetgroups: this.socnetDestination.SONETGROUPS || {},
	          department: this.socnetDestination.DEPARTMENT || {},
	          departmentRelation: this.socnetDestination.DEPARTMENT_RELATION || {}
	        };
	      } else if (key === 'itemsLast' && this.socnetDestination.LAST) {
	        userSelectorConfig = {
	          users: this.socnetDestination.LAST.USERS || {},
	          groups: this.socnetDestination.EXTRANET_USER === 'Y' ? {} : {
	            UA: true
	          },
	          sonetgroups: this.socnetDestination.LAST.SONETGROUPS || {},
	          department: this.socnetDestination.LAST.DEPARTMENT || {}
	        };
	      } else if (key === 'itemsSelected') {
	        userSelectorConfig = this.socnetDestination.SELECTED || {};
	      }

	      return userSelectorConfig;
	    }
	  }, {
	    key: "setUserSelectorConfig",
	    value: function setUserSelectorConfig(socnetDestination) {
	      this.socnetDestination = socnetDestination;
	    }
	  }, {
	    key: "pinField",
	    value: function pinField(fieldName) {
	      var _this13 = this;

	      var _this$getPlaceholders = this.getPlaceholders(),
	          _this$getPlaceholders2 = babelHelpers.slicedToArray(_this$getPlaceholders, 2),
	          placeHolders = _this$getPlaceholders2[0],
	          placeHoldersAdditional = _this$getPlaceholders2[1];

	      var field = placeHoldersAdditional[fieldName],
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
	          var editor = window["BXHtmlEditor"].Get(this.editorId);

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
	        setTimeout(function () {
	          main_core.Dom.removeClass(field, 'calendar-hide-field');
	          field.style.height = '';
	          newField.style.height = '';
	          _this13.pinnedFieldsIndex[fieldName] = true;

	          _this13.saveSettings();

	          _this13.updateAdditionalBlockState();
	        }, 300);
	      }
	    }
	  }, {
	    key: "unPinField",
	    value: function unPinField(fieldName) {
	      var _this$getPlaceholders3 = this.getPlaceholders(),
	          _this$getPlaceholders4 = babelHelpers.slicedToArray(_this$getPlaceholders3, 2),
	          placeHolders = _this$getPlaceholders4[0],
	          placeHoldersAdditional = _this$getPlaceholders4[1];

	      var field = placeHolders[fieldName],
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
	          var editor = window["BXHtmlEditor"].Get(this.editorId);

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
	  }, {
	    key: "fieldIsPinned",
	    value: function fieldIsPinned(fieldName) {
	      return this.pinnedFieldsIndex[fieldName];
	    }
	  }, {
	    key: "getPlaceholders",
	    value: function getPlaceholders() {
	      if (!this.placeHolders) {
	        this.placeHolders = {};
	        this.placeHoldersAdditional = {};
	        var i,
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
	  }, {
	    key: "getSettings",
	    value: function getSettings(settings) {
	      this.pinnedFieldsIndex = {};
	      var i,
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
	  }, {
	    key: "saveSettings",
	    value: function saveSettings() {
	      var fieldName,
	          pinnedFields = [];

	      for (fieldName in this.pinnedFieldsIndex) {
	        if (this.pinnedFieldsIndex.hasOwnProperty(fieldName) && this.pinnedFieldsIndex[fieldName]) {
	          pinnedFields.push(fieldName);
	        }
	      }

	      this.formSettings.pinnedFields = pinnedFields;
	      this.BX.userOptions.save('calendar', this.formType, 'pinnedFields', pinnedFields);
	    }
	  }, {
	    key: "updateAdditionalBlockState",
	    value: function updateAdditionalBlockState(timeout) {
	      var _this14 = this;

	      if (timeout !== false) {
	        if (this.updateAdditionalBlockTimeout) {
	          clearTimeout(this.updateAdditionalBlockTimeout);
	          this.updateAdditionalBlockTimeout = null;
	        }

	        this.updateAdditionalBlockTimeout = setTimeout(function () {
	          _this14.updateAdditionalBlockState(false);
	        }, 300);
	      } else {
	        var i,
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
	  }, {
	    key: "checkLastItemBorder",
	    value: function checkLastItemBorder() {
	      var noBorderClass = 'no-border',
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
	  }, {
	    key: "handlePlannerSelectorChanges",
	    value: function handlePlannerSelectorChanges(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var data = event.getData(); // Date time

	        this.dateTimeControl.setValue({
	          from: data.dateFrom,
	          to: data.dateTo
	        }); //this.checkLocationAccessibility();
	      }
	    } // onCalendarPlannerSelectorChanged(params)
	    // {
	    // 	if (params.plannerId === this.plannerId)
	    // 	{
	    // 		if (!this.planner)
	    // 		{
	    // 			this.planner = params.planner;
	    // 		}
	    //
	    // 		// Date time
	    // 		this.dateTimeControl.setValue({
	    // 			from: params.dateFrom,
	    // 			to: params.dateTo
	    // 		});
	    // 		//this.checkLocationAccessibility();
	    // 	}
	    // }
	    // onCalendarPlannerUpdatedHandler(planner, params)
	    // {
	    // 	if (!this.planner)
	    // 	{
	    // 		this.planner = planner;
	    // 		//this.checkLocationAccessibility();
	    // 	}
	    // }

	  }, {
	    key: "checkPlannerState",
	    value: function checkPlannerState() {
	      var dateTime = this.dateTimeControl.getValue();
	      this.loadPlannerData({
	        codes: this.userSelector.getCodes(),
	        from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: dateTime.timezoneFrom,
	        location: this.locationSelector.getTextValue()
	      });
	    }
	  }, {
	    key: "updatePlanner",
	    value: function updatePlanner(params) {
	      if (!params) {
	        params = {};
	      } //let currentEntryLocation = this.calendar.util.parseLocation(this.entry.location);


	      this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	        data: {
	          entry_id: this.entry.id || 0,
	          codes: params.codes || [],
	          date_from: params.from || '',
	          date_to: params.to || '',
	          timezone: params.timezone || '',
	          location: params.location || '',
	          //roomEventId: currentEntryLocation ? (currentEntryLocation.room_event_id || currentEntryLocation.mrevid ||
	          // false) : false,
	          entries: params.entrieIds || false //add_cur_user_to_list: this.calendar.util.userIsOwner() ? 'Y' : 'N'

	        }
	      }).then( // Success
	      function (response) {
	        var i,
	            attendees = [],
	            attendeesIndex = {},
	            entries = response.data.entries,
	            accessibility = response.data.accessibility,
	            showPlanner = !!(params.entries || entries && entries.length > 0);

	        if (main_core.Type.isArray(entries)) {
	          for (i = 0; i < entries.length; i++) {
	            if (entries[i].type === 'user') {
	              attendees.push({
	                id: entries[i].id,
	                name: entries[i].name,
	                avatar: entries[i].avatar,
	                smallAvatar: entries[i].smallAvatar || entries[i].avatar,
	                url: entries[i].url
	              });
	              attendeesIndex[entries[i].id] = true; // if (!_this.attendeesIndex[response.entries[i].id])
	              // 	updateAttendeesControl = true;
	            }
	          }
	        } // if (!updateAttendeesControl)
	        // {
	        // 	for (let id in _this.attendeesIndex)
	        // 	{
	        // 		if (this.attendeesIndex.hasOwnProperty(id) && !attendeesIndex[id])
	        // 		{
	        // 			updateAttendeesControl = true;
	        // 			break;
	        // 		}
	        // 	}
	        // }
	        // Show first time or refresh it state


	        if (showPlanner) {
	          var refreshParams = {};

	          if (params.entries) {
	            entries = params.entries;
	            refreshParams.scaleFrom = params.from;
	            refreshParams.scaleTo = params.to;
	          }

	          refreshParams.loadedDataFrom = params.from;
	          refreshParams.loadedDataTo = params.to;
	          refreshParams.data = {
	            entries: entries,
	            accessibility: accessibility
	          };
	          refreshParams.focusSelector = params.focusSelector === undefined ? false : params.focusSelector;
	          this.refreshPlanner(refreshParams);
	        }
	      }.bind(this), // Failure
	      function (response) {}.bind(this));
	    }
	  }, {
	    key: "refreshPlanner",
	    value: function refreshPlanner() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var dateTime = this.dateTimeControl.getValue(); //this.plannerData = params.data;

	      var from = dateTime.from,
	          to = dateTime.to,
	          config = {},
	          scaleFrom,
	          scaleTo;

	      if (main_core.Type.isDate(from) && main_core.Type.isDate(to) && from.getTime() <= to.getTime()) {
	        main_core.Dom.addClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');

	        if (!this.plannerIsShown() && params.show || params.focusSelector === undefined) {
	          params.focusSelector = true;
	        }

	        if (dateTime.fullDay) {
	          scaleFrom = params.scaleFrom || new Date(from.getTime() - calendar_util.Util.getDayLength() * 3);
	          scaleTo = params.scaleTo || new Date(scaleFrom.getTime() + calendar_util.Util.getDayLength() * 10);
	          config.scaleType = '1day';
	          config.scaleDateFrom = scaleFrom;
	          config.scaleDateTo = scaleTo;
	          config.adjustCellWidth = false;
	        } else {
	          config.changeFromFullDay = {
	            scaleType: '1hour',
	            timelineCellWidth: 40
	          }; //config.shownScaleTimeFrom = parseInt(this.calendar.util.getWorkTime().start);
	          //config.shownScaleTimeTo = parseInt(this.calendar.util.getWorkTime().end);
	        }

	        config.entriesListWidth = this.DOM.attendeesTitle.offsetWidth + 16;
	        config.width = this.DOM.plannerWrap.offsetWidth;

	        if (this.DOM.moreOuterWrap) {
	          this.DOM.moreOuterWrap.style.paddingLeft = config.entriesListWidth + 'px';
	        } // RRULE


	        var RRULE = false; // if (this.DOM.rruleType.value !== 'NONE' && false)
	        // {
	        // 	RRULE = {
	        // 		FREQ: this.RepeatSelect.value,
	        // 		INTERVAL: this.RepeatCount.value,
	        // 		UNTIL: this.RepeatDiapTo.value
	        // 	};
	        //
	        // 	if (RRULE.UNTIL == EC_MESS.NoLimits)
	        // 		RRULE.UNTIL = '';
	        //
	        // 	if (RRULE.FREQ == 'WEEKLY')
	        // 	{
	        // 		RRULE.WEEK_DAYS = [];
	        // 		for (i = 0; i < 7; i++)
	        // 		{
	        // 			if (this.RepeatWeekDaysCh[i].checked)
	        // 			{
	        // 				RRULE.WEEK_DAYS.push(this.RepeatWeekDaysCh[i].value);
	        // 			}
	        // 		}
	        //
	        // 		if (!RRULE.WEEK_DAYS.length)
	        // 		{
	        // 			RRULE = false;
	        // 		}
	        // 	}
	        // }
	        //this.checkLocationAccessibility();

	        this.BX.onCustomEvent('OnCalendarPlannerDoUpdate', [{
	          plannerId: this.plannerId,
	          config: config,
	          focusSelector: params.focusSelector,
	          selector: {
	            from: from,
	            to: to,
	            fullDay: dateTime.fullDay,
	            RRULE: RRULE,
	            animation: true,
	            updateScaleLimits: true
	          },
	          data: params.data || false,
	          loadedDataFrom: params.loadedDataFrom,
	          loadedDataTo: params.loadedDataTo,
	          show: true
	        }]);
	      }
	    }
	  }, {
	    key: "plannerIsShown",
	    value: function plannerIsShown() {
	      return this.DOM.plannerWrap && main_core.Dom.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
	    }
	  }, {
	    key: "keyHandler",
	    value: function keyHandler(e) {
	      if ((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	        this.save();
	      }
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorList) {
	      var _this15 = this;

	      var errorText = '';

	      if (main_core.Type.isArray(errorList)) {
	        errorList.forEach(function (error) {
	          if (error.code === "edit_entry_location_busy") {
	            return calendar_util.Util.showFieldError(error.message, _this15.DOM.locationWrap, {
	              clearTimeout: 10000
	            });
	          }

	          errorText += error.message + "\n";
	        });
	      }

	      if (errorText !== '') {
	        alert(errorText);
	      }
	    }
	  }, {
	    key: "checkForSignificantChanges",
	    value: function checkForSignificantChanges() {
	      return true;
	      var res = false; // Name

	      if (!res && this.entry.name !== this.DOM.form.name.value) res = true; // Description

	      if (!res && this.descriptionValue !== this.DOM.form.desc.value) res = true; // Location

	      if (!res && this.entry.data.LOCATION !== this.DOM.form.lo_cation.value) res = true; // Date & time

	      if (!res && this.entry.isFullDay() != this.DOM.form.skip_time.checked) res = true;

	      if (!res) {
	        var from = BX.parseDate(this.entry.data.DATE_FROM),
	            to = new Date(from.getTime() + (this.entry.data.DT_LENGTH - (this.entry.isFullDay() ? 1 : 0)) * 1000);
	        if (Math.abs(from.getTime() - this.fromDate.getTime()) > 1000 || Math.abs(to.getTime() - this.toDate.getTime()) > 1000) res = true;

	        if (!res && !this.entry.isFullDay() && (this.entry.data.TZ_FROM !== this.DOM.form.tz_from.value || this.entry.data.TZ_TO !== this.DOM.form.tz_to.value)) {
	          res = true;
	        }
	      } // Attendees


	      if (!res && this.plannerData && false) {
	        var i,
	            attendeesInd = {};

	        if (this.entry.isMeeting()) {
	          var attendeeList = this.entry.getAttendees();

	          for (i in attendeeList) {
	            if (attendeeList.hasOwnProperty(i) && attendeeList[i]['ID']) {
	              attendeesInd[attendeeList[i]['ID']] = true;
	            }
	          }
	        } // Check if we have new attendees


	        for (i in this.plannerData.entries) {
	          if (this.plannerData.entries.hasOwnProperty(i) && this.plannerData.entries[i].type == 'user' && this.plannerData.entries[i].id) {
	            if (attendeesInd[this.plannerData.entries[i].id]) {
	              attendeesInd[this.plannerData.entries[i].id] = '+';
	            } else {
	              res = true;
	              break;
	            }
	          }
	        } // Check if we have all old attendees


	        if (!res && attendeesInd) {
	          for (i in attendeesInd) {
	            if (attendeesInd.hasOwnProperty(i) && attendeesInd[i] !== '+') {
	              res = true;
	              break;
	            }
	          }
	        }
	      } // Recurtion
	      //if (!res && (this.oEvent.RRULE.FREQ != this.RepeatSelect.value))
	      //	res = true;
	      //
	      //if (!res && (this.oEvent.RRULE.INTERVAL != this.RepeatCount.value))
	      //	res = true;
	      //
	      //if (!res && this.oEvent.RRULE.FREQ == 'WEEKLY' && this.oEvent.RRULE.BYDAY)
	      //{
	      //	let BYDAY = [];
	      //	for (i in this.oEvent.RRULE.BYDAY)
	      //	{
	      //		if (this.oEvent.RRULE.BYDAY.hasOwnProperty(i))
	      //		{
	      //			BYDAY.push(this.oEvent.RRULE.BYDAY[i]);
	      //		}
	      //	}
	      //	if (BYDAY.join(',') != top.BX('event-rrule-byday' + this.id).value)
	      //		res = true;
	      //}


	      return res;
	    }
	  }]);
	  return EventEditForm;
	}();

	exports.EventEditForm = EventEditForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar.Controls,BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.Calendar));
//# sourceMappingURL=eventeditform.bundle.js.map
