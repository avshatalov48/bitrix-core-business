this.BX = this.BX || {};
(function (exports,main_core,calendar_controls,calendar_util,calendar_entry,calendar_sectionmanager,main_core_events,calendar_planner,ui_entitySelector,calendar_sync_interface) {
	'use strict';

	var SliderDateTimeControl = /*#__PURE__*/function (_DateTimeControl) {
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

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span></span>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"hidden\" name=\"attendeesEntityList[", "][id]\" value=\"", "\">\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"hidden\" name=\"attendeesEntityList[", "][entityId]\" value=\"", "\">\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input name=\"requestUid\" type=\"hidden\">"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input name=\"sendInvitesAgain\" type=\"hidden\" value=\"", "\">"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EventEditForm = /*#__PURE__*/function () {
	  function EventEditForm() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventEditForm);
	    babelHelpers.defineProperty(this, "DOM", {});
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
	    this.userId = options.userId || parseInt(main_core.Loc.getMessage('USER_ID'));
	    this.ownerId = options.ownerId;
	    this.entryId = parseInt(options.entryId) || null;
	    this.entry = options.entry || null;
	    this.formDataValue = options.formDataValue || {};
	    this.emitter = new main_core_events.EventEmitter();
	    this.emitter.setEventNamespace('BX.Calendar.EventEditForm');
	    this.BX = calendar_util.Util.getBX();
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

	    this.organizerId = main_core.Type.isInteger(parseInt(options.organizerId)) ? parseInt(options.organizerId) : this.userId;
	    this.participantsEntityList = main_core.Type.isArray(options.participantsEntityList) ? options.participantsEntityList : [];

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

	      main_core.Event.bind(document, 'keydown', this.keyHandlerBind);
	      main_core_events.EventEmitter.subscribe('onPullEvent-calendar', this.handlePullBind); // region 'protection from closing slider by accident'

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
	      this.slider = event.getSlider();
	      this.DOM.content = this.slider.layout.content;
	      this.sliderId = this.slider.getUrl(); // Used to execute javasctipt and attach CSS from ajax responce

	      this.BX.html(this.slider.layout.content, this.slider.getData().get("sliderContent"));
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

	      if (this.state === this.STATE.REQUEST) {
	        return false;
	      }

	      options = main_core.Type.isPlainObject(options) ? options : {};

	      if (!this.entry.id && this.hasExternalEmailUsers() && calendar_util.Util.checkEmailLimitationPopup() && !options.emailLimitationDialogShown) {
	        calendar_entry.EntryManager.showEmailLimitationDialog({
	          callback: function callback() {
	            options.emailLimitationDialogShown = true;

	            _this2.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.userSettings.sendFromEmail && this.hasExternalEmailUsers()) {
	        calendar_entry.EntryManager.showConfirmedEmailDialog({
	          callback: function callback(params) {
	            if (params.sendFromEmail) {
	              _this2.userSettings.sendFromEmail = params.sendFromEmail;
	            }

	            _this2.save(options);
	          }
	        });
	        return false;
	      }

	      if (this.entry.id && this.entry.isRecursive() && !options.confirmed && this.getFormDataChanges(['section', 'notify']).length > 0) {
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

	      if (this.entry.id && this.entry.isMeeting() && options.sendInvitesAgain === undefined && this.getFormDataChanges().includes('date&time') && this.entry.getAttendees().find(function (item) {
	        return item.STATUS === 'N';
	      })) {
	        calendar_entry.EntryManager.showReInviteUsersDialog({
	          callback: function callback(params) {
	            options.sendInvitesAgain = params.sendInvitesAgain;

	            _this2.save(options);
	          }
	        });
	        return false;
	      }

	      main_core.Dom.addClass(this.DOM.saveBtn, this.BX.UI.Button.State.CLOCKING);
	      main_core.Dom.addClass(this.DOM.closeBtn, this.BX.UI.Button.State.DISABLED);
	      this.state = this.STATE.REQUEST;
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
	        } // this.BX.userOptions.save('calendar', 'user_settings', 'lastUsedSection', parseInt(section.ID));

	      }

	      this.DOM.form.current_date_from.value = options.recursionMode ? calendar_util.Util.formatDate(this.entry.from) : '';
	      this.DOM.form.rec_edit_mode.value = options.recursionMode || '';

	      if (options.sendInvitesAgain !== undefined) {
	        this.DOM.form.appendChild(main_core.Tag.render(_templateObject(), options.sendInvitesAgain ? 'Y' : 'N'));
	      }

	      if (!this.DOM.form.requestUid) {
	        this.DOM.requestUid = this.DOM.form.appendChild(main_core.Tag.render(_templateObject2()));
	      }

	      this.DOM.requestUid.value = calendar_util.Util.registerRequestId(); // Save attendees from userSelector

	      main_core.Dom.clean(this.DOM.userSelectorValueWarp);
	      this.getUserSelectorEntityList().forEach(function (entity, index) {
	        _this2.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_templateObject3(), index, entity.entityId));

	        _this2.DOM.userSelectorValueWarp.appendChild(main_core.Tag.render(_templateObject4(), index, entity.id));
	      });
	      this.BX.ajax.runAction('calendar.api.calendarajax.editEntry', {
	        data: new FormData(this.DOM.form),
	        analyticsLabel: {
	          calendarAction: this.entry.id ? 'edit_event' : 'create_event',
	          formType: 'full',
	          emailGuests: this.hasExternalEmailUsers() ? 'Y' : 'N',
	          markView: calendar_util.Util.getCurrentView() || 'outside',
	          markCrm: this.DOM.form['UF_CRM_CAL_EVENT[]'] && this.DOM.form['UF_CRM_CAL_EVENT[]'].value ? 'Y' : 'N',
	          markRrule: this.repeatSelector.getType(),
	          markMeeting: this.entry.isMeeting() ? 'Y' : 'N',
	          markType: this.type
	        }
	      }).then(function (response) {
	        _this2.state = _this2.STATE.READY;

	        _this2.allowSliderClose();

	        _this2.close();

	        main_core.Dom.removeClass(_this2.DOM.closeBtn, _this2.BX.UI.Button.State.DISABLED);
	        main_core.Dom.removeClass(_this2.DOM.saveBtn, _this2.BX.UI.Button.State.CLOCKING);

	        if (response.data.entryId) {
	          if (_this2.entry.id) {
	            calendar_entry.EntryManager.showEditEntryNotification(response.data.entryId);
	          } else {
	            calendar_entry.EntryManager.showNewEntryNotification(response.data.entryId);
	          }
	        }

	        if (response.data.displayMobileBanner) {
	          new calendar_sync_interface.MobileSyncBanner().showInPopup();
	        }

	        if (response.data.countEventWithEmailGuestAmount) {
	          calendar_util.Util.setEventWithEmailGuestAmount(response.data.countEventWithEmailGuestAmount);
	        }

	        if (main_core.Type.isArray(response.data.eventList) && response.data.eventList.length && response.data.eventList[0].REMIND && response.data.eventList[0].REMIND.length) {
	          calendar_entry.EntryManager.setNewEntryReminders(response.data.eventList[0].DT_SKIP_TIME === 'Y' ? 'fullDay' : 'withTime', response.data.eventList[0].REMIND);
	        }

	        _this2.emitter.emit('onSave', new main_core_events.BaseEvent({
	          data: {
	            responseData: response.data,
	            options: options
	          }
	        }));

	        main_core_events.EventEmitter.emit('BX.Calendar:onEntrySave', new main_core_events.BaseEvent({
	          data: {
	            sliderId: _this2.sliderId,
	            responseData: response.data,
	            options: options
	          }
	        }));
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
	      return true;
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
	        this.BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{
	          plannerId: this.plannerId
	        }]);
	        main_core.Event.unbind(document, 'keydown', this.keyHandlerBind);
	        main_core_events.EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullBind);
	        this.BX.SidePanel.Instance.destroy(this.sliderId);
	        calendar_util.Util.closeAllPopups();
	        this.planner = null;
	        this.opened = false;
	        calendar_util.Util.clearPlannerWatches();
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
	          ownerId: this.ownerId,
	          entityList: this.participantsEntityList
	        }
	      }).then(function (response) {
	        if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	          var html = _this4.BX.util.trim(response.data.html);

	          slider.getData().set("sliderContent", html);
	          var params = response.data.additionalParams;
	          _this4.uid = params.uniqueId;
	          _this4.editorId = params.editorId;
	          _this4.formSettings = _this4.getSettings(params.formSettings || []);
	          var attendeesEntityList = _this4.formDataValue.attendeesEntityList || params.attendeesEntityList || [];

	          if (!entry.id && _this4.participantsEntityList.length) {
	            attendeesEntityList = [].concat(babelHelpers.toConsumableArray(attendeesEntityList), babelHelpers.toConsumableArray(_this4.participantsEntityList));
	          }

	          if (main_core.Type.isArray(attendeesEntityList)) {
	            attendeesEntityList.forEach(function (item) {
	              if (item.entityId === 'user' && params.userIndex[item.id]) {
	                item.entityType = params.userIndex[item.id].EMAIL_USER ? 'email' : 'employee';
	              }
	            });
	          }

	          _this4.setUserSelectorEntityList(attendeesEntityList);

	          _this4.attendeesPreselectedItems = _this4.getUserSelectorEntityList().map(function (item) {
	            return [item.entityId, item.id];
	          });

	          _this4.setUserSettings(params.userSettings);

	          calendar_util.Util.setEventWithEmailGuestAmount(params.countEventWithEmailGuestAmount);
	          calendar_util.Util.setEventWithEmailGuestLimit(params.eventWithEmailGuestLimit);

	          _this4.handleSections(params.sections, params.trackingUsersList);

	          _this4.handleLocationData(params.locationFeatureEnabled, params.locationList, params.iblockMeetingRoomList);

	          if (!entry.id && !entry.sectionId) {
	            _this4.setCurrentEntry();
	          }

	          _this4.updateEntryData(params.entry, {
	            userSettings: _this4.userSettings
	          });

	          var key = _this4.type + _this4.ownerId;

	          if (_this4.userSettings.defaultSections && _this4.userSettings.defaultSections[key]) {
	            calendar_sectionmanager.SectionManager.setNewEntrySectionId(_this4.userSettings.defaultSections[key]);
	          }

	          promise.fulfill(html);
	        }
	      }, function (response) {//this.calendar.displayError(response.errors);
	      });
	      return promise;
	    }
	  }, {
	    key: "initControls",
	    value: function initControls(uid) {
	      this.DOM.title = this.DOM.content.querySelector("#".concat(uid, "_title"));
	      this.DOM.formWrap = this.DOM.content.querySelector("#".concat(uid, "_form_wrap"));
	      this.DOM.form = this.DOM.content.querySelector("#".concat(uid, "_form"));
	      this.DOM.buttonsWrap = this.DOM.content.querySelector('.calendar-form-buttons-fixed');
	      this.DOM.saveBtn = this.DOM.buttonsWrap.querySelector("#".concat(uid, "_save"));
	      this.DOM.closeBtn = this.DOM.buttonsWrap.querySelector("#".concat(uid, "_close"));
	      main_core.Event.bind(this.DOM.saveBtn, 'click', this.save.bind(this));
	      main_core.Event.bind(this.DOM.closeBtn, 'click', this.close.bind(this));
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

	      if (this.DOM.buttonsWrap) {
	        BX.ZIndexManager.register(this.DOM.buttonsWrap);
	      }
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
	      calendar_util.Util.setUserSettings(userSettings);
	    }
	  }, {
	    key: "setFormValues",
	    value: function setFormValues() {
	      var _this6 = this;

	      var entry = this.entry; // Date time

	      this.dateTimeControl.setValue({
	        from: this.formDataValue.from || entry.from,
	        to: this.formDataValue.to || entry.to,
	        fullDay: main_core.Type.isBoolean(this.formDataValue.fullDay) ? this.formDataValue.fullDay : entry.fullDay,
	        timezoneFrom: entry.getTimezoneFrom() || '',
	        timezoneTo: entry.getTimezoneTo() || '',
	        timezoneName: this.userSettings.timezoneName
	      });
	      this.DOM.entryName.value = this.formDataValue.name || entry.getName(); // Section

	      if (this.formDataValue.section) {
	        entry.sectionId = parseInt(this.formDataValue.section);
	      }

	      this.DOM.sectionInput.value = this.getCurrentSectionId();
	      this.sectionSelector.updateValue();

	      if (!this.fieldIsPinned('section')) {
	        var currentSection = this.getCurrentSection();

	        if (currentSection['CAL_TYPE'] !== this.type || currentSection['CAL_TYPE'] === this.type && parseInt(currentSection['OWNER_ID']) !== this.ownerId) {
	          this.pinField('section');
	        }
	      } // Color


	      this.colorSelector.setValue(this.formDataValue.color || entry.getColor() || this.getCurrentSection().COLOR); // Reminders

	      this.remindersControl.setValue(this.formDataValue.reminder || entry.getReminders(), true, false); // Recursion

	      this.repeatSelector.setValue(entry.getRrule()); // accessibility

	      if (this.DOM.accessibilityInput) {
	        this.DOM.accessibilityInput.value = entry.accessibility;
	      } // Location


	      this.locationSelector.setValue(this.formDataValue.location || entry.getLocation()); // Private

	      if (this.DOM.privateEventCheckbox) {
	        this.DOM.privateEventCheckbox.checked = entry.private;
	      } // Importance


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

	      this.loadPlannerData({
	        entityList: this.getUserSelectorEntityList(),
	        from: calendar_util.Util.formatDate(entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: entry.getTimezoneFrom(),
	        location: this.locationSelector.getTextValue()
	      }).then(function () {
	        if (_this6.hasExternalEmailUsers()) {
	          _this6.showHideGuestsOption();
	        } else {
	          _this6.hideHideGuestsOption();
	        }
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

	      if (this.remindersControl) {
	        this.remindersControl.setFullDayMode(value);
	      }

	      this.refreshPlanner();
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
	      var _this7 = this;

	      this.DOM.mainBlock = this.DOM.content.querySelector("#".concat(uid, "_main_block_wrap"));
	      this.DOM.additionalBlockWrap = this.DOM.content.querySelector("#".concat(uid, "_additional_block_wrap"));
	      this.DOM.additionalBlock = this.DOM.content.querySelector("#".concat(uid, "_additional_block"));
	      this.DOM.pinnedNamesWrap = this.DOM.content.querySelector("#".concat(uid, "_additional_pinned_names"));
	      this.DOM.additionalSwitch = this.DOM.content.querySelector("#".concat(uid, "_additional_switch"));
	      main_core.Event.bind(this.DOM.additionalSwitch, 'click', function () {
	        main_core.Dom.toggleClass(_this7.DOM.additionalSwitch, 'opened');
	        main_core.Dom.toggleClass(_this7.DOM.additionalBlock, 'invisible');
	      });
	      main_core.Event.bind(this.DOM.formWrap, 'click', function (e) {
	        var target = e.target || e.srcElement;

	        if (target && target.getAttribute && target.getAttribute('data-bx-fixfield')) {
	          var fieldName = target.getAttribute('data-bx-fixfield');

	          if (!_this7.fieldIsPinned(fieldName)) {
	            _this7.pinField(fieldName);
	          } else {
	            _this7.unPinField(fieldName);
	          }
	        }
	      });
	    }
	  }, {
	    key: "initDateTimeControl",
	    value: function initDateTimeControl(uid) {
	      var _this8 = this;

	      this.dateTimeControl = new SliderDateTimeControl(uid, {
	        showTimezone: true,
	        outerContent: this.DOM.content
	      });
	      this.dateTimeControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var value = event.getData().value;

	          if (_this8.remindersControl) {
	            _this8.remindersControl.setFullDayMode(value.fullDay);

	            if (!_this8.entry.id && !_this8.remindersControl.wasChangedByUser()) {
	              var defaultReminders = calendar_entry.EntryManager.getNewEntryReminders(value.fullDay ? 'fullDay' : 'withTime');

	              _this8.remindersControl.setValue(defaultReminders, true, false);
	            }
	          }

	          if (_this8.planner) {
	            _this8.planner.updateSelector(value.from, value.to, value.fullDay);
	          }
	        }
	      });
	    }
	  }, {
	    key: "initNameControl",
	    value: function initNameControl(uid) {
	      var _this9 = this;

	      this.DOM.entryName = this.DOM.content.querySelector("#".concat(uid, "_entry_name"));
	      setTimeout(function () {
	        _this9.DOM.entryName.focus();

	        _this9.DOM.entryName.select();
	      }, 500);
	    }
	  }, {
	    key: "initReminderControl",
	    value: function initReminderControl(uid) {
	      var _this10 = this;

	      this.reminderValues = [];
	      this.DOM.reminderWrap = this.DOM.content.querySelector("#".concat(uid, "_reminder"));
	      this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(main_core.Tag.render(_templateObject5()));
	      this.remindersControl = new calendar_controls.Reminder({
	        wrap: this.DOM.reminderWrap,
	        zIndex: this.zIndex
	      });
	      this.remindersControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          _this10.reminderValues = event.getData().values;
	          main_core.Dom.clean(_this10.DOM.reminderInputsWrap);

	          _this10.reminderValues.forEach(function (value) {
	            _this10.DOM.reminderInputsWrap.appendChild(main_core.Dom.create('INPUT', {
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
	  }, {
	    key: "initSectionSelector",
	    value: function initSectionSelector(uid) {
	      var _this11 = this;

	      this.DOM.sectionInput = this.DOM.content.querySelector("#".concat(uid, "_section"));
	      this.sectionSelector = new calendar_controls.SectionSelector({
	        outerWrap: this.DOM.content.querySelector("#".concat(uid, "_section_wrap")),
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
	        getCurrentSection: function getCurrentSection() {
	          var section = _this11.getCurrentSection();

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
	            _this11.DOM.sectionInput.value = sectionValue.id;

	            if (_this11.colorSelector) {
	              _this11.colorSelector.setValue(sectionValue.color);
	            }

	            _this11.entry.setSectionId(sectionValue.id);

	            calendar_sectionmanager.SectionManager.saveDefaultSectionId(sectionValue.id, {
	              calendarType: _this11.type,
	              ownerId: _this11.ownerId,
	              userId: _this11.userId,
	              sections: _this11.sections
	            });
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
	        onChangeCallback: this.refreshPlanner
	      });
	    }
	  }, {
	    key: "initRepeatRuleControl",
	    value: function initRepeatRuleControl(uid) {
	      var _this12 = this;

	      this.DOM.rruleWrap = this.DOM.content.querySelector("#".concat(uid, "_rrule_wrap"));
	      this.repeatSelector = new calendar_controls.RepeatSelector({
	        wrap: this.DOM.rruleWrap,
	        rruleType: this.DOM.content.querySelector("#".concat(uid, "_rrule_type")),
	        getDate: function () {
	          return this.dateTimeControl.getValue().from;
	        }.bind(this)
	      });
	      this.dateTimeControl.subscribe('onChange', function () {
	        if (_this12.repeatSelector.getType() === 'weekly') {
	          _this12.repeatSelector.changeType(_this12.repeatSelector.getType());
	        }
	      });
	    }
	  }, {
	    key: "initAttendeesControl",
	    value: function initAttendeesControl(uid) {
	      this.DOM.userSelectorWrap = this.DOM.content.querySelector('.calendar-attendees-selector-wrap');
	      this.DOM.userSelectorValueWarp = this.DOM.userSelectorWrap.appendChild(main_core.Tag.render(_templateObject6()));
	      this.userTagSelector = new ui_entitySelector.TagSelector({
	        dialogOptions: {
	          context: 'CALENDAR',
	          preselectedItems: this.attendeesPreselectedItems || [],
	          zIndex: this.slider.zIndex,
	          events: {
	            'Item:onSelect': this.handleUserSelectorChanges.bind(this),
	            'Item:onDeselect': this.handleUserSelectorChanges.bind(this)
	          },
	          entities: [{
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
	          }],
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
	  }, {
	    key: "handleUserSelectorChanges",
	    value: function handleUserSelectorChanges() {
	      if (this.planner) {
	        this.planner.show();
	        this.planner.showLoader();
	        var selectedItems = this.userTagSelector.getDialog().getSelectedItems();
	        this.setUserSelectorEntityList(selectedItems.map(function (item) {
	          return {
	            entityId: item.entityId,
	            id: item.id,
	            entityType: item.entityType
	          };
	        }));
	        this.refreshPlanner();
	      }
	    }
	  }, {
	    key: "hasExternalEmailUsers",
	    value: function hasExternalEmailUsers() {
	      return !!this.getUserSelectorEntityList().find(function (item) {
	        return item.entityType === 'email';
	      });
	    }
	  }, {
	    key: "showHideGuestsOption",
	    value: function showHideGuestsOption() {
	      this.DOM.hideGuestsWrap.style.display = '';
	      var hideGuestsHint = this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-helper');

	      if (main_core.Type.isElementNode(hideGuestsHint)) {
	        BX.UI.Hint.initNode(hideGuestsHint);
	      }
	    }
	  }, {
	    key: "hideHideGuestsOption",
	    value: function hideHideGuestsOption() {
	      this.DOM.hideGuestsWrap.style.display = 'none';
	    }
	  }, {
	    key: "setHideGuestsValue",
	    value: function setHideGuestsValue() {
	      var hideGuests = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      this.hideGuests = hideGuests;
	    }
	  }, {
	    key: "initPlanner",
	    value: function initPlanner(uid) {
	      this.DOM.plannerOuterWrap = this.DOM.content.querySelector("#".concat(uid, "_planner_outer_wrap"));
	      this.planner = new calendar_planner.Planner({
	        wrap: this.DOM.plannerOuterWrap,
	        minWidth: parseInt(this.DOM.plannerOuterWrap.offsetWidth)
	      });
	      this.planner.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
	      this.planner.subscribe('onExpandTimeline', this.handleExpandPlannerTimeline.bind(this));
	      this.planner.show();
	      this.planner.showLoader();
	    }
	  }, {
	    key: "loadPlannerData",
	    value: function loadPlannerData() {
	      var _this13 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.planner.showLoader();
	      return new Promise(function (resolve) {
	        _this13.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	          data: {
	            entryId: _this13.entry.id || 0,
	            ownerId: _this13.ownerId,
	            type: _this13.type,
	            entityList: params.entityList || [],
	            dateFrom: params.from || '',
	            dateTo: params.to || '',
	            timezone: params.timezone || '',
	            location: params.location || '',
	            entries: params.entrieIds || false
	          }
	        }).then(function (response) {
	          if (_this13.planner) {
	            _this13.planner.hideLoader();

	            var dateTime = _this13.dateTimeControl.getValue();

	            _this13.planner.update(response.data.entries, response.data.accessibility);

	            _this13.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
	          }

	          if (_this13.hasExternalEmailUsers()) {
	            _this13.showHideGuestsOption();
	          } else {
	            _this13.hideHideGuestsOption();
	          }

	          resolve(response);
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
	      this.entry = calendar_entry.EntryManager.getEntryInstance(entry, userIndex, {
	        type: this.type,
	        ownerId: this.ownerId
	      });
	      calendar_entry.EntryManager.registerEntrySlider(this.entry, this);
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

	      if (!section) {
	        section = calendar_sectionmanager.SectionManager.getNewEntrySectionId();

	        if (!this.sectionIndex[section]) {
	          section = null;
	        }
	      }

	      if (!section && this.sections[0]) {
	        section = parseInt(this.sections[0].ID);
	      }

	      return section;
	    }
	  }, {
	    key: "pinField",
	    value: function pinField(fieldName) {
	      var _this14 = this;

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
	          _this14.pinnedFieldsIndex[fieldName] = true;

	          _this14.saveSettings();

	          _this14.updateAdditionalBlockState();
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
	      var _this15 = this;

	      if (timeout !== false) {
	        if (this.updateAdditionalBlockTimeout) {
	          clearTimeout(this.updateAdditionalBlockTimeout);
	          this.updateAdditionalBlockTimeout = null;
	        }

	        this.updateAdditionalBlockTimeout = setTimeout(function () {
	          _this15.updateAdditionalBlockState(false);
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
	    }
	  }, {
	    key: "handleExpandPlannerTimeline",
	    value: function handleExpandPlannerTimeline(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var data = event.getData();

	        if (data.reload) {
	          //this.refreshPlanner();
	          var dateTime = this.dateTimeControl.getValue();
	          this.loadPlannerData({
	            entityList: this.getUserSelectorEntityList(),
	            from: calendar_util.Util.formatDate(data.dateFrom),
	            to: calendar_util.Util.formatDate(data.dateTo),
	            timezone: dateTime.timezoneFrom,
	            location: this.locationSelector.getTextValue()
	          });
	        }
	      }
	    }
	  }, {
	    key: "getUserSelectorEntityList",
	    value: function getUserSelectorEntityList() {
	      return this.selectorEntityList;
	    }
	  }, {
	    key: "setUserSelectorEntityList",
	    value: function setUserSelectorEntityList(selectorEntityList) {
	      this.selectorEntityList = selectorEntityList;
	    }
	  }, {
	    key: "refreshPlannerState",
	    value: function refreshPlannerState() {
	      var dateTime = this.dateTimeControl.getValue();
	      this.loadPlannerData({
	        entityList: this.getUserSelectorEntityList(),
	        from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: dateTime.timezoneFrom,
	        location: this.locationSelector.getTextValue()
	      });
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
	      var _this16 = this;

	      var errorText = '';

	      if (main_core.Type.isArray(errorList)) {
	        errorList.forEach(function (error) {
	          if (error.code === "edit_entry_location_busy") {
	            return calendar_util.Util.showFieldError(error.message, _this16.DOM.locationWrap, {
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
	    key: "getFormDataChanges",
	    value: function getFormDataChanges() {
	      var excludes = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      var entry = this.entry;
	      var fields = []; // Name

	      if (!excludes.includes('name') && entry.name !== this.DOM.form.name.value) {
	        fields.push('name');
	      } // Description
	      // if (!excludes.includes('name')
	      // 	&& this.descriptionValue !== this.DOM.form.desc.value)
	      // {
	      // 	fields.push('description');
	      // }
	      // Location


	      if (!excludes.includes('location') && this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(this.entry.getLocation())) !== this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(this.locationSelector.getTextValue()))) {
	        fields.push('location');
	      } // Date + time


	      var dateTime = this.dateTimeControl.getValue();

	      if (!excludes.includes('date&time') && (entry.isFullDay() !== dateTime.fullDay || dateTime.from.toString() !== entry.from.toString() || dateTime.to.toString() !== entry.to.toString())) {
	        fields.push('date&time');
	      } // Section


	      if (!excludes.includes('section') && parseInt(entry.sectionId) !== parseInt(this.DOM.sectionInput.value)) {
	        fields.push('section');
	      } // Access codes


	      if (!excludes.includes('codes') && this.getUserSelectorEntityList().map(function (item) {
	        return item.entityId + ':' + item.id;
	      }).join('|') !== entry.getAttendeesEntityList().map(function (item) {
	        return item.entityId + ':' + item.id;
	      }).join('|')) {
	        fields.push('codes');
	      }

	      return fields;
	    }
	  }, {
	    key: "formDataChanged",
	    value: function formDataChanged() {
	      return this.getFormDataChanges().length > 0;
	    }
	  }, {
	    key: "getUserCodes",
	    value: function getUserCodes() {
	      var codes = [],
	          valuesInput = this.DOM.attendeesWrap.querySelectorAll('input[name="EVENT_DESTINATION[]"]');

	      for (var i = 0; i < valuesInput.length; i++) {
	        if (!codes.includes(valuesInput[i].value)) {
	          codes.push(valuesInput[i].value);
	        }
	      }

	      return codes;
	    }
	  }, {
	    key: "handlePull",
	    value: function handlePull(event) {
	      if (!event instanceof main_core_events.BaseEvent) {
	        return;
	      }

	      var data = event.getData();
	      var command = data[0]; // const params = Type.isObjectLike(data[1]) ? data[1] : {};

	      switch (command) {
	        case 'edit_event':
	        case 'delete_event':
	        case 'set_meeting_status':
	          this.refreshPlannerState();
	          break;
	      }
	    }
	  }]);
	  return EventEditForm;
	}();

	exports.EventEditForm = EventEditForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar.Controls,BX.Calendar,BX.Calendar,BX.Calendar,BX.Event,BX.Calendar,BX.UI.EntitySelector,BX.Calendar.Sync.Interface));
//# sourceMappingURL=eventeditform.bundle.js.map
