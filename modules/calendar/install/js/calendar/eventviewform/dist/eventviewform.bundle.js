this.BX = this.BX || {};
(function (exports,calendar_util,main_core,calendar_entry,calendar_controls,main_core_events) {
	'use strict';

	var EventViewForm = /*#__PURE__*/function () {
	  function EventViewForm() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventViewForm);
	    babelHelpers.defineProperty(this, "permissions", {});
	    babelHelpers.defineProperty(this, "name", 'eventviewform');
	    babelHelpers.defineProperty(this, "uid", null);
	    babelHelpers.defineProperty(this, "DOM", {});
	    this.type = options.type || 'user';
	    this.ownerId = options.ownerId || 0;
	    this.userId = options.userId || 0;
	    this.sliderId = "calendar:view-entry-slider";
	    this.zIndex = 3100;
	    this.entryId = options.entryId || null;
	    this.entryDateFrom = options.entryDateFrom || null;
	    this.timezoneOffset = options.timezoneOffset || null;
	    this.BX = calendar_util.Util.getBX();
	    this.sliderOnClose = this.hide.bind(this);
	    this.sliderOnLoad = this.onLoadSlider.bind(this);
	  }

	  babelHelpers.createClass(EventViewForm, [{
	    key: "initInSlider",
	    value: function initInSlider(slider, promiseResolve) {
	      this.BX.addCustomEvent(slider, "SidePanel.Slider:onLoad", this.sliderOnLoad);
	      this.BX.addCustomEvent(slider, "SidePanel.Slider:onClose", this.sliderOnClose);
	      this.BX.addCustomEvent(slider, "SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this)); //this.BX.removeCustomEvent(slider, "SidePanel.Slider:onBeforeCloseComplete", this.destroy.bind(this));

	      this.createContent(slider).then(function (html) {
	        if (main_core.Type.isFunction(promiseResolve)) {
	          promiseResolve(html);
	        }
	      }.bind(this));
	      this.opened = true;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (params.entryId) {
	        this.entryId = params.entryId;
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
	          onLoad: this.sliderOnLoad
	        }
	      });
	      this.opened = true;
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return this.opened;
	    }
	  }, {
	    key: "hide",
	    value: function hide(event) {
	      if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId) {
	        this.BX.removeCustomEvent("SidePanel.Slider::onClose", this.sliderOnClose);
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy(event) {
	      this.BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", this.BX.proxy(this.destroy, this));
	      main_core.Event.unbind(document, "click", calendar_util.Util.applyHacksForPopupzIndex);
	      this.BX.SidePanel.Instance.destroy(this.sliderId);
	      calendar_util.Util.closeAllPopups();
	      this.opened = false;
	    }
	  }, {
	    key: "onLoadSlider",
	    value: function onLoadSlider(event) {
	      var slider = event.getSlider();
	      this.DOM.content = slider.layout.content; // Used to execute javasctipt and attach CSS from ajax responce

	      this.BX.html(slider.layout.content, slider.getData().get("sliderContent"));
	      this.initControls(this.uid);
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
	            html = _this.BX.util.trim(response.data.html);
	            slider.getData().set("sliderContent", html);
	            var params = response.data.additionalParams;
	            _this.userId = params.userId;
	            _this.uid = params.uniqueId;
	            _this.entryUrl = params.entryUrl;

	            _this.handleEntryData(params.entry, params.userIndex, params.section);
	          }

	          resolve(html);
	        }, function (response) {
	          _this.displayError(response.errors);

	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "initControls",
	    value: function initControls(uid) {
	      var _this2 = this;

	      this.DOM.title = this.DOM.content.querySelector("#".concat(uid, "_title"));
	      this.DOM.formWrap = this.DOM.content.querySelector("#".concat(uid, "_form_wrap"));
	      this.DOM.form = this.DOM.content.querySelector("#".concat(uid, "_form"));
	      this.DOM.buttonSet = this.DOM.content.querySelector("#".concat(uid, "_buttonset"));
	      this.DOM.editButton = this.DOM.content.querySelector("#".concat(uid, "_but_edit"));
	      this.DOM.delButton = this.DOM.content.querySelector("#".concat(uid, "_but_del"));
	      this.DOM.sidebarInner = this.DOM.content.querySelector("#".concat(uid, "_sidebar_inner"));
	      this.DOM.chatLink = this.DOM.content.querySelector("#".concat(uid, "_but_chat"));

	      if (this.DOM.chatLink) {
	        main_core.Event.bind(this.DOM.chatLink, 'click', function () {
	          calendar_entry.EntryManager.openChatForEntry({
	            entryId: _this2.entry.parentId,
	            entry: _this2.entry
	          });
	        });
	      }

	      if (this.DOM.buttonSet) {
	        this.initPlannerControl(uid);
	        this.initUserListControl(uid);
	      }

	      if (this.DOM.content.querySelector("#".concat(uid, "_time_inner_wrap")).offsetHeight > 50) {
	        this.BX.addClass(this.DOM.content.querySelector("#".concat(uid, "_time_wrap")), 'calendar-slider-sidebar-head-long-time');
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
	          this.reminderControl = new calendar_controls.Reminder({
	            wrap: this.DOM.reminderWrap,
	            zIndex: this.zIndex,
	            viewMode: viewMode
	          });
	          this.reminderControl.setValue(this.entry.getReminders());

	          if (!viewMode) {
	            this.reminderControl.subscribe('onChange', function (event) {
	              if (event instanceof main_core_events.BaseEvent) {
	                this.reminderValues = event.getData().values;
	                this.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
	                  data: {
	                    entryId: this.entry.id,
	                    userId: this.userId,
	                    reminders: this.reminderValues
	                  }
	                });
	              }
	            }.bind(this));
	          }
	        }

	        var items = this.DOM.sidebarInner.querySelectorAll('.calendar-slider-sidebar-border-bottom');

	        if (items.length >= 2) {
	          this.BX.removeClass(items[items.length - 1], 'calendar-slider-sidebar-border-bottom');
	        }
	      } //this.DOM.reminderInputsWrap = this.DOM.reminderWrap.appendChild(Tag.render`<span></span>`);


	      if (this.canDo(this.entry, 'delete')) {
	        main_core.Event.bind(this.DOM.delButton, 'click', function () {
	          calendar_entry.EntryManager.deleteEntry(_this2.entry);
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
	        var attendees = this.entry.getAttendees();

	        if (main_core.Type.isArray(attendees)) {
	          if (window.location.host === 'cp.bitrix.ru' && this.DOM.chatLink && attendees.length > 1 && attendees.find(function (user) {
	            return user.STATUS !== 'N' && parseInt(user.ID) === parseInt(_this2.userId);
	          })) {
	            this.DOM.chatLink.style.display = '';
	          }
	        }
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
	      }

	      main_core.Event.unbind(document, "click", calendar_util.Util.applyHacksForPopupzIndex);
	      main_core.Event.bind(document, "click", calendar_util.Util.applyHacksForPopupzIndex);
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
	    }
	  }, {
	    key: "initPlannerControl",
	    value: function initPlannerControl(uid) {
	      var _this3 = this;

	      this.plannerId = uid + '_view_slider_planner';
	      this.DOM.plannerWrap = this.DOM.content.querySelector("#".concat(uid, "_view_planner_wrap"));
	      setTimeout(function () {
	        if (this.DOM.plannerWrap) {
	          this.BX.removeClass(this.DOM.plannerWrap, 'hidden');
	        }
	      }.bind(this), 500);
	      setTimeout(function () {
	        if (_this3.DOM.plannerWrap && _this3.DOM.plannerWrap.offsetWidth) {
	          _this3.BX.onCustomEvent('OnCalendarPlannerDoResize', [{
	            plannerId: _this3.plannerId,
	            timeoutCheck: true,
	            width: _this3.DOM.plannerWrap.offsetWidth
	          }]);
	        }
	      }, 200);
	      main_core.Event.bind(window, 'resize', function () {
	        if (_this3.DOM.plannerWrap && _this3.DOM.plannerWrap.offsetWidth) {
	          _this3.BX.onCustomEvent('OnCalendarPlannerDoResize', [{
	            plannerId: _this3.plannerId,
	            timeoutCheck: true,
	            width: _this3.DOM.plannerWrap.offsetWidth
	          }]);
	        }
	      });
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

	          if (user.EMAIL_USER) {
	            userWrap.appendChild(this.BX.create("DIV", {
	              props: {
	                className: 'calendar-slider-sidebar-user-info'
	              }
	            })).appendChild(this.BX.create("span", {
	              text: user.DISPLAY_NAME
	            }));
	          } else {
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
	          }
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
	            var result = calendar_entry.EntryManager.setMeetingStatus(this.entry, event.getData().status);

	            if (!result) {
	              this.statusControl.setStatus(this.entry.getCurrentStatus(), false);
	            }
	          }
	        }.bind(this));
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
	        // if ((entry.isMeeting() && entry.id !== entry.parentId)
	        // 	|| entry.isResourcebooking())
	        // {
	        // 	return false;
	        // }
	        return this.permissions.edit;
	      }

	      if (action === 'view') {
	        return this.permissions.view_full;
	      }

	      return false;
	    }
	  }]);
	  return EventViewForm;
	}();

	exports.EventViewForm = EventViewForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX,BX.Calendar,BX.Calendar.Controls,BX.Event));
//# sourceMappingURL=eventviewform.bundle.js.map
