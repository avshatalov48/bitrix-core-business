this.BX = this.BX || {};
(function (exports,calendar_util,main_core,calendar_entry,calendar_controls,main_core_events,calendar_planner,intranet_controlButton) {
	'use strict';

	var EventViewForm = /*#__PURE__*/function () {
	  function EventViewForm() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventViewForm);
	    babelHelpers.defineProperty(this, "permissions", {});
	    babelHelpers.defineProperty(this, "name", 'eventviewform');
	    babelHelpers.defineProperty(this, "uid", null);
	    babelHelpers.defineProperty(this, "DOM", {});
	    babelHelpers.defineProperty(this, "RELOAD_REQUESTED", 'RELOAD_REQUESTED');
	    babelHelpers.defineProperty(this, "RELOAD_FINISHED", 'RELOAD_FINISHED');
	    babelHelpers.defineProperty(this, "reloadStatus", null);
	    babelHelpers.defineProperty(this, "entityChanged", false);
	    babelHelpers.defineProperty(this, "LOAD_DELAY", 500);
	    this.type = options.type || 'user';
	    this.ownerId = options.ownerId || 0;
	    this.userId = options.userId || 0;
	    this.zIndex = 3100;
	    this.entryId = options.entryId || null;
	    this.calendarContext = options.calendarContext || null;
	    this.entryDateFrom = options.entryDateFrom || null;
	    this.timezoneOffset = options.timezoneOffset || null;
	    this.BX = calendar_util.Util.getBX();
	    this.sliderOnLoad = this.onLoadSlider.bind(this);
	    this.handlePullBind = this.handlePull.bind(this);
	    this.keyHandlerBind = this.keyHandler.bind(this);
	    this.destroyBind = this.destroy.bind(this);
	    this.loadPlannerDataDebounce = main_core.Runtime.debounce(this.loadPlannerData, this.LOAD_DELAY, this);
	    this.reloadSliderDebounce = main_core.Runtime.debounce(this.reloadSlider, this.LOAD_DELAY, this);
	    this.pullEventList = new Set();
	  }

	  babelHelpers.createClass(EventViewForm, [{
	    key: "initInSlider",
	    value: function initInSlider(slider, promiseResolve) {
	      this.slider = slider;
	      main_core_events.EventEmitter.subscribe(slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	      main_core_events.EventEmitter.subscribe(slider, "SidePanel.Slider:onCloseComplete", this.destroyBind);
	      main_core.Event.bind(document, 'keydown', this.keyHandlerBind);
	      main_core.Event.bind(document, 'visibilitychange', this.handleVisibilityChange.bind(this));
	      main_core_events.EventEmitter.subscribe('onPullEvent-calendar', this.handlePullBind);
	      this.createContent(slider).then(function (html) {
	        if (main_core.Type.isFunction(promiseResolve)) {
	          promiseResolve(html);
	        }
	      }.bind(this));
	      this.opened = true;
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return this.opened;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	      main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onCloseComplete", this.destroyBind);
	      main_core_events.EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullBind);
	      main_core.Event.unbind(document, 'keydown', this.keyHandlerBind);

	      if (this.intranetControllButton && this.intranetControllButton.destroy) {
	        this.intranetControllButton.destroy();
	      } // this.BX.SidePanel.Instance.destroy(this.sliderId);


	      calendar_util.Util.closeAllPopups();
	      this.opened = false;
	    }
	  }, {
	    key: "onLoadSlider",
	    value: function onLoadSlider(event) {
	      var _data$;

	      if (!event instanceof main_core_events.BaseEvent) {
	        return;
	      }

	      var data = event.getData();
	      var slider = (_data$ = data[0]) === null || _data$ === void 0 ? void 0 : _data$.slider;
	      this.DOM.content = slider.layout.content; // Used to execute javasctipt and attach CSS from ajax responce

	      this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));

	      if (!main_core.Type.isNull(this.uid)) {
	        this.initControls(this.uid);
	      }

	      this.reloadStatus = this.RELOAD_FINISHED;
	    }
	  }, {
	    key: "createContent",
	    value: function createContent(slider) {
	      var _this = this;

	      return new Promise(function (resolve) {
	        _this.BX.ajax.runAction('calendar.api.calendarajax.getViewEventSlider', {
	          analyticsLabel: {
	            calendarAction: 'view_event',
	            formType: 'full'
	          },
	          data: {
	            entryId: _this.entryId,
	            dateFrom: calendar_util.Util.formatDate(_this.entryDateFrom),
	            timezoneOffset: _this.timezoneOffset
	          }
	        }).then(function (response) {
	          var html = '';

	          if (main_core.Type.isFunction(slider.isOpen) && slider.isOpen() || slider.isOpen === true) {
	            html = response.data.html;
	            slider.getData().set("sliderContent", html);
	            var params = response.data.additionalParams;
	            _this.userId = params.userId;
	            _this.uid = params.uniqueId;
	            _this.entryUrl = params.entryUrl;
	            _this.userTimezone = params.userTimezone;
	            _this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat;
	            _this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;

	            if (_this.planner && !_this.plannerFeatureEnabled) {
	              _this.planner.lock();
	            }

	            _this.handleEntryData(params.entry, params.userIndex, params.section);
	          }

	          resolve(html);
	        }, function (response) {
	          if (response.errors && response.errors.length) {
	            slider.getData().set("sliderContent", '<div class="calendar-slider-alert">' + '<div class="calendar-slider-alert-inner">' + '<div class="calendar-slider-alert-img"></div>' + '<h1 class="calendar-slider-alert-text">' + main_core.Text.encode(response.errors[0].message) + '</h1>' + '</div>' + '</div>');
	          }

	          _this.displayError(response.errors);

	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "initControls",
	    value: function initControls(uid) {
	      var _this2 = this,
	          _BX,
	          _BX$Intranet;

	      this.DOM.title = this.DOM.content.querySelector("#".concat(uid, "_title"));
	      this.DOM.buttonSet = this.DOM.content.querySelector("#".concat(uid, "_buttonset"));
	      this.DOM.editButton = this.DOM.content.querySelector("#".concat(uid, "_but_edit"));
	      this.DOM.delButton = this.DOM.content.querySelector("#".concat(uid, "_but_del"));
	      this.DOM.sidebarInner = this.DOM.content.querySelector("#".concat(uid, "_sidebar_inner"));

	      if (this.DOM.buttonSet) {
	        this.initPlannerControl(uid);
	        this.initUserListControl(uid);
	      }

	      var innerTimeWrap = this.DOM.content.querySelector("#".concat(uid, "_time_inner_wrap"));

	      if (main_core.Type.isElementNode(innerTimeWrap) && innerTimeWrap.offsetHeight > 50) {
	        main_core.Dom.addClass(this.DOM.content.querySelector("#".concat(uid, "_time_wrap")), 'calendar-slider-sidebar-head-long-time');
	      }

	      if (this.canDo(this.entry, 'edit') && this.DOM.editButton) {
	        main_core.Event.bind(this.DOM.editButton, 'click', function () {
	          _this2.BX.SidePanel.Instance.close(false, function () {
	            calendar_entry.EntryManager.openEditSlider({
	              entry: this.entry,
	              type: this.type,
	              ownerId: this.ownerId,
	              userId: this.userId
	            });
	          }.bind(_this2));
	        });
	      } else {
	        this.BX.remove(this.DOM.editButton);
	      }

	      if (this.DOM.sidebarInner) {
	        // Reminder
	        this.DOM.reminderWrap = this.DOM.sidebarInner.querySelector('.calendar-slider-sidebar-remind-wrap');

	        if (main_core.Type.isDomNode(this.DOM.reminderWrap)) {
	          var viewMode = !this.canDo(this.entry, 'edit') && this.entry.getCurrentStatus() === false;
	          this.reminderControl = new this.BX.Calendar.Controls.Reminder({
	            wrap: this.DOM.reminderWrap,
	            zIndex: this.zIndex,
	            viewMode: viewMode
	          });
	          this.reminderControl.setValue(this.entry.getReminders());

	          if (!viewMode) {
	            this.reminderControl.subscribe('onChange', function (event) {
	              if (event instanceof main_core_events.BaseEvent) {
	                _this2.handleEntityChanges();

	                _this2.reminderValues = event.getData().values;

	                _this2.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
	                  data: {
	                    entryId: _this2.entry.id,
	                    userId: _this2.userId,
	                    reminders: _this2.reminderValues
	                  }
	                });
	              }
	            });
	          }
	        }

	        var items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');

	        if (items.length >= 2) {
	          this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
	        }
	      }

	      if (this.canDo(this.entry, 'delete')) {
	        main_core.Event.bind(this.DOM.delButton, 'click', function () {
	          main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	            _this2.BX.SidePanel.Instance.close();
	          });
	          calendar_entry.EntryManager.deleteEntry(_this2.entry, _this2.calendarContext);
	        });
	      } else {
	        this.BX.remove(this.DOM.delButton);
	      }

	      this.BX.viewElementBind(uid + '_' + this.entry.id + '_files_wrap', {
	        showTitle: true
	      }, function (node) {
	        return main_core.Type.isElementNode(node) && (node.getAttribute('data-bx-viewer') || node.getAttribute('data-bx-image'));
	      });

	      if (this.entry && this.entry.isMeeting()) {
	        this.initAcceptMeetingControl(uid);
	      }

	      if (this.DOM.sidebarInner) {
	        var _items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');

	        if (_items.length >= 2) {
	          this.BX.removeClass(_items[_items.length - 1], 'calendar-slider-sidebar-border-bottom');
	        }
	      }

	      this.DOM.copyButton = this.DOM.content.querySelector("#".concat(uid, "_copy_url_btn"));

	      if (this.DOM.copyButton) {
	        main_core.Event.bind(this.DOM.copyButton, 'click', this.copyEventUrl.bind(this));
	      } // Init "Videocall" control


	      this.DOM.videoCall = this.DOM.sidebarInner.querySelector('.calendar-slider-sidebar-videocall');

	      if ((_BX = BX) !== null && _BX !== void 0 && (_BX$Intranet = _BX.Intranet) !== null && _BX$Intranet !== void 0 && _BX$Intranet.ControlButton && main_core.Type.isElementNode(this.DOM.videoCall) && this.entry.getCurrentStatus() !== false) {
	        this.DOM.videoCall.style.display = '';
	        this.intranetControllButton = new intranet_controlButton.ControlButton({
	          container: this.DOM.videoCall,
	          entityType: 'calendar_event',
	          entityId: this.entry.parentId,
	          entityData: {
	            dateFrom: calendar_util.Util.formatDate(this.entry.from),
	            parentId: this.entry.parentId
	          },
	          analyticsLabel: {
	            formType: 'full'
	          }
	        });
	      }
	    }
	  }, {
	    key: "handleEntryData",
	    value: function handleEntryData(entryData, userIndex, sectionData) {
	      this.entry = new calendar_entry.Entry({
	        data: entryData,
	        userIndex: userIndex
	      });

	      if (main_core.Type.isPlainObject(sectionData)) {
	        this.permissions = sectionData.PERM;
	      }

	      calendar_entry.EntryManager.registerEntrySlider(this.entry, this);
	    }
	  }, {
	    key: "initPlannerControl",
	    value: function initPlannerControl(uid) {
	      var _this3 = this;

	      this.plannerId = uid + '_view_slider_planner';
	      this.DOM.plannerWrapOuter = this.DOM.content.querySelector(".calendar-slider-detail-timeline");
	      this.DOM.plannerWrap = this.DOM.plannerWrapOuter.querySelector(".calendar-view-planner-wrap");
	      this.planner = new calendar_planner.Planner({
	        wrap: this.DOM.plannerWrap,
	        minWidth: parseInt(this.DOM.plannerWrap.offsetWidth),
	        solidStatus: true,
	        readonly: true,
	        locked: !this.plannerFeatureEnabled,
	        dayOfWeekMonthFormat: this.dayOfWeekMonthFormat
	      });
	      this.planner.show();
	      this.planner.showLoader();
	      setTimeout(function () {
	        if (_this3.DOM.plannerWrapOuter) {
	          main_core.Dom.removeClass(_this3.DOM.plannerWrapOuter, 'hidden');
	        }
	      }, 500);
	      this.loadPlannerDataDebounce();
	    }
	  }, {
	    key: "initUserListControl",
	    value: function initUserListControl(uid) {
	      var _this4 = this;

	      var userList = {
	        y: [],
	        i: [],
	        q: [],
	        n: []
	      };

	      if (this.entry.isMeeting()) {
	        this.entry.getAttendees().forEach(function (user) {
	          if (user.STATUS === 'H') {
	            userList.y.push(user);
	          } else if (userList[user.STATUS.toLowerCase()]) {
	            userList[user.STATUS.toLowerCase()].push(user);
	          }
	        }, this);
	      }

	      this.DOM.attendeesListY = this.DOM.content.querySelector("#".concat(uid, "_attendees_y"));
	      this.DOM.attendeesListN = this.DOM.content.querySelector("#".concat(uid, "_attendees_n"));
	      this.DOM.attendeesListQ = this.DOM.content.querySelector("#".concat(uid, "_attendees_q"));
	      this.DOM.attendeesListI = this.DOM.content.querySelector("#".concat(uid, "_attendees_i"));
	      main_core.Event.bind(this.DOM.attendeesListY, 'click', function () {
	        _this4.showUserListPopup(_this4.DOM.attendeesListY, userList.y);
	      });
	      main_core.Event.bind(this.DOM.attendeesListN, 'click', function () {
	        _this4.showUserListPopup(_this4.DOM.attendeesListN, userList.n);
	      });
	      main_core.Event.bind(this.DOM.attendeesListQ, 'click', function () {
	        _this4.showUserListPopup(_this4.DOM.attendeesListQ, userList.q);
	      });
	      main_core.Event.bind(this.DOM.attendeesListI, 'click', function () {
	        _this4.showUserListPopup(_this4.DOM.attendeesListI, userList.i);
	      });
	    }
	  }, {
	    key: "showUserListPopup",
	    value: function showUserListPopup(node, userList) {
	      var _this5 = this;

	      if (this.userListPopup) {
	        this.userListPopup.close();
	      }

	      if (userList && userList.length) {
	        this.DOM.userListPopupWrap = this.BX.create('DIV', {
	          props: {
	            className: 'calendar-user-list-popup-block'
	          }
	        });
	        userList.forEach(function (user) {
	          var userWrap = this.DOM.userListPopupWrap.appendChild(this.BX.create('DIV', {
	            props: {
	              className: 'calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card'
	            }
	          }));
	          userWrap.appendChild(this.BX.create('DIV', {
	            props: {
	              className: 'calendar-slider-sidebar-user-block-avatar'
	            }
	          })).appendChild(this.BX.create('DIV', {
	            props: {
	              className: 'calendar-slider-sidebar-user-block-item'
	            }
	          })).appendChild(this.BX.create('IMG', {
	            props: {
	              width: 34,
	              height: 34,
	              src: user.AVATAR
	            }
	          }));
	          userWrap.appendChild(this.BX.create("DIV", {
	            props: {
	              className: 'calendar-slider-sidebar-user-info'
	            }
	          })).appendChild(this.BX.create("A", {
	            props: {
	              href: user.URL ? user.URL : '#',
	              className: 'calendar-slider-sidebar-user-info-name'
	            },
	            text: user.DISPLAY_NAME
	          }));
	        }, this);
	        this.userListPopup = this.BX.PopupWindowManager.create("user-list-popup-" + Math.random(), node, {
	          autoHide: true,
	          closeByEsc: true,
	          offsetTop: 0,
	          offsetLeft: 0,
	          resizable: false,
	          lightShadow: true,
	          content: this.DOM.userListPopupWrap,
	          className: 'calendar-user-list-popup',
	          zIndex: 4000
	        });
	        this.userListPopup.setAngle({
	          offset: 36
	        });
	        this.userListPopup.show();
	        this.BX.addCustomEvent(this.userListPopup, 'onPopupClose', function () {
	          _this5.userListPopup.destroy();
	        });
	      }
	    }
	  }, {
	    key: "initAcceptMeetingControl",
	    value: function initAcceptMeetingControl(uid) {
	      var _this6 = this;

	      this.DOM.statusButtonset = this.DOM.content.querySelector("#".concat(uid, "_status_buttonset"));
	      this.DOM.statusButtonset.style.marginRight = '12px';

	      if (this.entry.getCurrentStatus() === 'H' || this.entry.getCurrentStatus() === false) {
	        main_core.Dom.remove(this.DOM.statusButtonset);
	      } else {
	        this.statusControl = new calendar_controls.MeetingStatusControl({
	          wrap: this.DOM.statusButtonset,
	          currentStatus: this.DOM.content.querySelector("#".concat(uid, "_current_status")).value || this.entry.getCurrentStatus()
	        });
	        this.statusControl.subscribe('onSetStatus', function (event) {
	          if (event instanceof main_core_events.BaseEvent) {
	            _this6.handleEntityChanges();

	            calendar_entry.EntryManager.setMeetingStatus(_this6.entry, event.getData().status).then(function () {
	              _this6.statusControl.setStatus(_this6.entry.getCurrentStatus(), false);

	              _this6.statusControl.updateStatus();
	            });
	          }
	        });
	      }
	    }
	  }, {
	    key: "copyEventUrl",
	    value: function copyEventUrl() {
	      if (!this.entryUrl || !this.BX.clipboard.copy(this.entryUrl)) {
	        return;
	      }

	      this.timeoutIds = this.timeoutIds || [];
	      var popup = new this.BX.PopupWindow('calendar_clipboard_copy', this.DOM.copyButton, {
	        content: main_core.Loc.getMessage('CALENDAR_TIP_TEMPLATE_LINK_COPIED'),
	        darkMode: true,
	        autoHide: true,
	        zIndex: 1000,
	        angle: true,
	        offsetLeft: 20,
	        cachable: false
	      });
	      popup.show();
	      var timeoutId;

	      while (timeoutId = this.timeoutIds.pop()) {
	        clearTimeout(timeoutId);
	      }

	      this.timeoutIds.push(setTimeout(function () {
	        popup.close();
	      }, 1500));
	    }
	  }, {
	    key: "displayError",
	    value: function displayError() {//errors
	    }
	  }, {
	    key: "canDo",
	    value: function canDo(entry, action) {
	      if (action === 'edit' || action === 'delete') {
	        if (entry.isResourcebooking()) {
	          return false;
	        }

	        return this.permissions.edit;
	      }

	      if (action === 'view') {
	        return this.permissions.view_full;
	      }

	      return false;
	    }
	  }, {
	    key: "plannerIsShown",
	    value: function plannerIsShown() {
	      return this.DOM.plannerWrap && main_core.Dom.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
	    }
	  }, {
	    key: "loadPlannerData",
	    value: function loadPlannerData() {
	      var _this7 = this;

	      this.planner.showLoader();
	      return new Promise(function (resolve) {
	        _this7.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	          data: {
	            entryId: _this7.entry.id || 0,
	            entryLocation: _this7.entry.data.LOCATION || '',
	            ownerId: _this7.ownerId,
	            hostId: _this7.entry.getMeetingHost(),
	            type: _this7.type,
	            entityList: _this7.entry.getAttendeesEntityList(),
	            dateFrom: calendar_util.Util.formatDate(_this7.entry.from.getTime() - calendar_util.Util.getDayLength() * 3),
	            dateTo: calendar_util.Util.formatDate(_this7.entry.to.getTime() + calendar_util.Util.getDayLength() * 10),
	            timezone: _this7.userTimezone,
	            location: _this7.entry.getLocation()
	          }
	        }).then(function (response) {
	          _this7.planner.hideLoader();

	          _this7.planner.update(response.data.entries, response.data.accessibility);

	          _this7.planner.updateSelector(calendar_util.Util.adjustDateForTimezoneOffset(_this7.entry.from, _this7.entry.userTimezoneOffsetFrom, _this7.entry.fullDay), calendar_util.Util.adjustDateForTimezoneOffset(_this7.entry.to, _this7.entry.userTimezoneOffsetTo, _this7.entry.fullDay), _this7.entry.fullDay);

	          resolve(response);
	        }, function (response) {
	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "keyHandler",
	    value: function keyHandler(e) {
	      var _this8 = this;

	      if (e.keyCode === calendar_util.Util.getKeyCode('delete') // || e.keyCode === Util.getKeyCode('backspace')
	      && this.canDo(this.entry, 'delete')) {
	        var target = event.target || event.srcElement;
	        var tagName = main_core.Type.isElementNode(target) ? target.tagName.toLowerCase() : null;

	        if (tagName && !['input', 'textarea'].includes(tagName)) {
	          main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	            _this8.BX.SidePanel.Instance.close();
	          });
	          calendar_entry.EntryManager.deleteEntry(this.entry, this.calendarContext);
	        }
	      }
	    }
	  }, {
	    key: "handlePull",
	    value: function handlePull(event) {
	      if (!event instanceof main_core_events.BaseEvent) {
	        return;
	      }

	      var data = event.getData();
	      var command = data[0];

	      if (BX.Calendar.Util.documentIsDisplayingNow()) {
	        switch (command) {
	          case 'edit_event':
	          case 'delete_event':
	          case 'set_meeting_status':
	            var calendarContext = calendar_util.Util.getCalendarContext();

	            if (calendarContext) {
	              if (this.planner && this.reloadStatus === this.RELOAD_FINISHED) {
	                this.loadPlannerDataDebounce();
	              }
	            } else {
	              this.reloadSliderDebounce();
	            }

	            break;
	        }
	      } else {
	        var params = {
	          command: command
	        };

	        if (this.pullEventList.has(params)) {
	          this.pullEventList["delete"](params);
	        }

	        this.pullEventList.add(params);
	      }
	    }
	  }, {
	    key: "handleVisibilityChange",
	    value: function handleVisibilityChange() {
	      var _this9 = this;

	      if (this.pullEventList.size) {
	        this.pullEventList.forEach(function (value, valueAgain, set) {
	          if (['edit_event', 'delete_event', 'set_meeting_status'].includes(value.command)) {
	            if (!calendar_util.Util.getCalendarContext()) {
	              _this9.reloadSliderDebounce();
	            }
	          }
	        });
	        this.pullEventList.clear();
	      }
	    }
	  }, {
	    key: "handleEntityChanges",
	    value: function handleEntityChanges() {
	      this.entityChanged = true;
	    }
	  }, {
	    key: "reloadSlider",
	    value: function reloadSlider() {
	      var _this10 = this;

	      if (this.reloadStatus === this.RELOAD_FINISHED) {
	        var activeElement = document.activeElement;

	        if (['IFRAME', 'TEXTAREA'].includes(activeElement.tagName.toUpperCase())) {
	          return;
	        } // Protection from reloading same page during changes (status or reminder)


	        if (this.entityChanged) {
	          setTimeout(function () {
	            _this10.entityChanged = false;
	          }, 500);
	          return;
	        }

	        main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	        main_core_events.EventEmitter.unsubscribe(this.slider, "SidePanel.Slider:onCloseComplete", this.destroyBind);
	        main_core_events.EventEmitter.unsubscribe('onPullEvent-calendar', this.handlePullBind);
	        main_core.Event.unbind(document, 'keydown', this.keyHandlerBind);
	        this.reloadStatus = this.RELOAD_REQUESTED;
	        this.slider.reload();
	      }
	    }
	  }]);
	  return EventViewForm;
	}();

	exports.EventViewForm = EventViewForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX,BX.Calendar,BX.Calendar.Controls,BX.Event,BX.Calendar,BX.Intranet));
//# sourceMappingURL=eventviewform.bundle.js.map
