this.BX = this.BX || {};
(function (exports,calendar_util,calendar_controls,main_core,ui_entitySelector,main_core_events,ui_messagecard) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	var SettingsInterface = /*#__PURE__*/function () {
	  function SettingsInterface(options) {
	    babelHelpers.classCallCheck(this, SettingsInterface);
	    babelHelpers.defineProperty(this, "sliderId", "calendar:settings-slider");
	    babelHelpers.defineProperty(this, "name", 'SettingsInterface');
	    babelHelpers.defineProperty(this, "SLIDER_WIDTH", 500);
	    babelHelpers.defineProperty(this, "SLIDER_DURATION", 80);
	    babelHelpers.defineProperty(this, "DOM", {});
	    this.calendarContext = options.calendarContext;
	    this.showPersonalSettings = options.showPersonalSettings;
	    this.showGeneralSettings = options.showGeneralSettings;
	    this.showAccessControl = options.showAccessControl !== false && main_core.Type.isObjectLike(this.calendarContext.util.config.TYPE_ACCESS);
	    this.settings = options.settings;
	    this.BX = calendar_util.Util.getBX();
	    this.hideMessageBinded = this.hideMessage.bind(this);
	  }

	  babelHelpers.createClass(SettingsInterface, [{
	    key: "show",
	    value: function show() {
	      this.BX.SidePanel.Instance.open(this.sliderId, {
	        contentCallback: this.createContent.bind(this),
	        width: this.SLIDER_WIDTH,
	        animationDuration: this.SLIDER_DURATION,
	        events: {
	          onCloseByEsc: this.escHide.bind(this),
	          onClose: this.hide.bind(this),
	          onCloseComplete: this.destroy.bind(this),
	          onLoad: this.onLoadSlider.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "escHide",
	    value: function escHide(event) {
	      if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId && this.denyClose) {
	        event.denyAction();
	      }
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      this.isOpenedState = false;
	      BX.SidePanel.Instance.close();
	    }
	  }, {
	    key: "isOpened",
	    value: function isOpened() {
	      return this.isOpenedState;
	    }
	  }, {
	    key: "hide",
	    value: function hide(event) {
	      if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	        if (this.denyClose) {
	          event.denyAction();
	        } else {
	          BX.removeCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
	        }
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
	    key: "destroy",
	    value: function destroy(event) {
	      if (event && event.getSlider && event.getSlider().getUrl() === this.sliderId) {
	        // this.destroyEventEmitterSubscriptions();
	        // Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:delete', this.deleteSectionHandlerBinded);
	        // Util.getBX().Event.EventEmitter.unsubscribe('BX.Calendar.Section:pull-delete', this.deleteSectionHandlerBinded);
	        // BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
	        BX.SidePanel.Instance.destroy(this.sliderId);
	        delete this.DOM.sectionListWrap;
	      }
	    }
	  }, {
	    key: "createContent",
	    value: function createContent(slider) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        top.BX.ajax.runAction('calendar.api.calendarajax.getSettingsSlider', {
	          data: {
	            showPersonalSettings: _this2.showPersonalSettings ? 'Y' : 'N',
	            showGeneralSettings: _this2.showGeneralSettings ? 'Y' : 'N',
	            showAccessControl: _this2.showAccessControl ? 'Y' : 'N',
	            uid: _this2.uid
	          }
	        }).then(function (response) {
	          slider.getData().set("sliderContent", response.data.html);
	          var params = response.data.additionalParams;
	          _this2.mailboxList = params.mailboxList;
	          _this2.uid = params.uid;
	          resolve(response.data.html);
	        });
	      });
	    }
	  }, {
	    key: "onLoadSlider",
	    value: function onLoadSlider(event) {
	      var slider = event.getSlider();
	      this.DOM.content = slider.layout.content;
	      this.sliderId = slider.getUrl(); // Used to execute javasctipt and attach CSS from ajax responce

	      BX.html(slider.layout.content, slider.getData().get("sliderContent"));
	      this.initControls();
	      this.setControlsValue();
	    }
	  }, {
	    key: "initControls",
	    value: function initControls() {
	      this.DOM.buttonsWrap = this.DOM.content.querySelector('.calendar-form-buttons-fixed');
	      BX.ZIndexManager.register(this.DOM.buttonsWrap);
	      this.DOM.saveBtn = this.DOM.buttonsWrap.querySelector('[data-role="save_btn"]');
	      this.DOM.closeBtn = this.DOM.buttonsWrap.querySelector('[data-role="close_btn"]');
	      BX.Event.bind(this.DOM.saveBtn, 'click', this.save.bind(this));
	      BX.Event.bind(this.DOM.closeBtn, 'click', this.close.bind(this));

	      if (this.showPersonalSettings) {
	        this.DOM.denyBusyInvitation = this.DOM.content.querySelector('[data-role="deny_busy_invitation"]');
	        this.DOM.showWeekNumbers = this.DOM.content.querySelector('[data-role="show_week_numbers"]');
	        this.DOM.meetSectionSelect = this.DOM.content.querySelector('[data-role="meet_section"]');
	        this.DOM.crmSelect = this.DOM.content.querySelector('[data-role="crm_section"]');
	        this.DOM.showDeclined = this.DOM.content.querySelector('[data-role="show_declined"]');
	        this.DOM.showTasks = this.DOM.content.querySelector('[data-role="show_tasks"]');
	        this.DOM.syncTasks = this.DOM.content.querySelector('[data-role="sync_tasks"]');
	        this.DOM.showCompletedTasks = this.DOM.content.querySelector('[data-role="show_completed_tasks"]');
	        this.DOM.timezoneSelect = this.DOM.content.querySelector('[data-role="set_tz_sel"]');
	        this.DOM.syncPeriodPast = this.DOM.content.querySelector('[data-role="sync_period_past"]');
	        this.DOM.syncPeriodFuture = this.DOM.content.querySelector('[data-role="sync_period_future"]');
	        this.DOM.sendFromEmailSelect = this.DOM.content.querySelector('[data-role="send_from_email"]');

	        if (this.BX.Type.isElementNode(this.DOM.sendFromEmailSelect)) {
	          this.emailSelectorControl = new calendar_controls.EmailSelectorControl({
	            selectNode: this.DOM.sendFromEmailSelect,
	            allowAddNewEmail: true,
	            mailboxList: this.mailboxList
	          });
	          this.DOM.emailHelpIcon = this.DOM.content.querySelector('.calendar-settings-question');

	          if (this.DOM.emailHelpIcon && BX.Helper) {
	            BX.Event.bind(this.DOM.emailHelpIcon, 'click', function () {
	              BX.Helper.show("redirect=detail&code=12070142");
	            });
	            calendar_util.Util.initHintNode(this.DOM.emailHelpIcon);
	          }

	          this.emailSelectorControl.setValue(this.calendarContext.util.getUserOption('sendFromEmail'));
	          this.DOM.emailWrap = this.DOM.content.querySelector('.calendar-settings-email-wrap');

	          if (BX.Calendar.Util.isEventWithEmailGuestAllowed()) {
	            BX.Dom.removeClass(this.DOM.emailWrap, 'lock');
	            this.DOM.sendFromEmailSelect.disabled = false;
	          } else {
	            BX.Dom.addClass(this.DOM.emailWrap, 'lock');
	            this.DOM.sendFromEmailSelect.disabled = true;
	            BX.Event.bind(this.DOM.sendFromEmailSelect.parentNode, 'click', function () {
	              BX.UI.InfoHelper.show('limit_calendar_invitation_by_mail');
	            });
	          }
	        }
	      } // General settings


	      if (this.showGeneralSettings) {
	        this.DOM.workTimeStart = this.DOM.content.querySelector('[data-role="work_time_start"]');
	        this.DOM.workTimeEnd = this.DOM.content.querySelector('[data-role="work_time_end"]');
	        this.DOM.weekHolidays = this.DOM.content.querySelector('[data-role="week_holidays"]');
	        this.DOM.yearHolidays = this.DOM.content.querySelector('[data-role="year_holidays"]');
	        this.DOM.yearWorkdays = this.DOM.content.querySelector('[data-role="year_workdays"]');
	      }

	      if (this.showAccessControl) {
	        this.DOM.accessMessageWrap = this.DOM.content.querySelector('[data-role="type-access-message-card"]');
	        this.DOM.accessOuterWrap = this.DOM.content.querySelector('[data-role="type-access-values-cont"]');
	        this.DOM.accessHelpIcon = this.DOM.content.querySelector('.calendar-settings-access-hint');

	        if (main_core.Type.isElementNode(this.DOM.accessHelpIcon) && this.calendarContext.util.type === 'location') {
	          this.initMessageControl();
	        } else if (main_core.Type.isElementNode(this.DOM.accessHelpIcon)) {
	          this.DOM.accessHelpIcon.remove();
	        }

	        if (main_core.Type.isElementNode(this.DOM.accessOuterWrap)) {
	          this.initAccessController();
	        }
	      }
	    }
	  }, {
	    key: "initMessageControl",
	    value: function initMessageControl() {
	      var _this3 = this;

	      var moreMessageButton = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a class=\"ui-btn ui-btn-primary\">", "</a>\n\t\t"])), main_core.Loc.getMessage('EC_LOCATION_SETTINGS_MORE_INFO'));
	      main_core.Event.bind(moreMessageButton, 'click', this.openHelpDesk);
	      var header = "";
	      var description = main_core.Loc.getMessage('EC_LOCATION_SETTINGS_MESSAGE_DESCRIPTION');
	      this.message = new ui_messagecard.MessageCard({
	        id: 'locationSettingsInfo',
	        header: header,
	        description: description,
	        angle: false,
	        hidden: true,
	        actionElements: [moreMessageButton]
	      });
	      main_core_events.EventEmitter.subscribe(this.message, 'onClose', this.hideMessageBinded);
	      main_core.Event.bind(this.DOM.accessHelpIcon, 'click', function () {
	        _this3.onClickHint();
	      });

	      if (this.DOM.accessMessageWrap) {
	        this.DOM.accessMessageWrap.appendChild(this.message.getLayout());
	        this.DOM.accessMessageWrap.firstChild.childNodes[1].remove();

	        if (!this.calendarContext.util.config.hideSettingsHintLocation) {
	          this.showMessage();
	        }
	      }
	    }
	  }, {
	    key: "onClickHint",
	    value: function onClickHint() {
	      if (!this.message) {
	        return;
	      }

	      if (this.message.isShown()) {
	        this.hideMessage();
	      } else {
	        this.showMessage();
	      }
	    }
	  }, {
	    key: "setControlsValue",
	    value: function setControlsValue() {
	      // Set personal user settings
	      if (this.showPersonalSettings) {
	        this.DOM.meetSectionSelect.options.length = 0;
	        var sections = this.calendarContext.sectionManager.getSectionListForEdit();
	        var crmSection = parseInt(this.calendarContext.util.getUserOption('crmSection'));
	        var meetSection = parseInt(this.calendarContext.util.getUserOption('meetSection'));
	        var section;
	        var selected;

	        for (var i = 0; i < sections.length; i++) {
	          section = sections[i];

	          if (section.belongsToOwner()) {
	            if (!meetSection) {
	              meetSection = section.id;
	            }

	            selected = meetSection === parseInt(section.id);
	            this.DOM.meetSectionSelect.options.add(new Option(section.name, section.id, selected, selected));

	            if (!crmSection) {
	              crmSection = section.id;
	            }

	            selected = crmSection === parseInt(section.id);
	            this.DOM.crmSelect.options.add(new Option(section.name, section.id, selected, selected));
	          }
	        }
	      }

	      if (this.DOM.showDeclined) {
	        this.DOM.showDeclined.checked = this.calendarContext.util.getUserOption('showDeclined');
	      }

	      var showTasks = this.calendarContext.util.getUserOption('showTasks') === 'Y';

	      if (this.DOM.showTasks) {
	        this.DOM.showTasks.checked = showTasks;
	        BX.Event.bind(this.DOM.showTasks, 'click', function () {
	          if (this.DOM.showCompletedTasks) {
	            this.DOM.showCompletedTasks.disabled = !this.DOM.showTasks.checked;
	            this.DOM.showCompletedTasks.checked = this.DOM.showCompletedTasks.checked && this.DOM.showTasks.checked;
	          }

	          if (this.DOM.syncTasks) {
	            this.DOM.syncTasks.disabled = !this.DOM.showTasks.checked;
	            this.DOM.syncTasks.checked = this.DOM.syncTasks.checked && this.DOM.showTasks.checked;
	          }
	        }.bind(this));
	      }

	      if (this.DOM.showCompletedTasks) {
	        this.DOM.showCompletedTasks.checked = this.calendarContext.util.getUserOption('showCompletedTasks') === 'Y' && this.DOM.showTasks.checked;
	        this.DOM.showCompletedTasks.disabled = !showTasks;
	      }

	      if (this.DOM.syncTasks) {
	        this.DOM.syncTasks.checked = this.calendarContext.util.getUserOption('syncTasks') === 'Y' && this.DOM.showTasks.checked;
	        this.DOM.syncTasks.disabled = !showTasks;
	      }

	      if (this.DOM.denyBusyInvitation) {
	        this.DOM.denyBusyInvitation.checked = this.calendarContext.util.getUserOption('denyBusyInvitation');
	      }

	      if (this.DOM.showWeekNumbers) {
	        this.DOM.showWeekNumbers.checked = this.calendarContext.util.showWeekNumber();
	      }

	      if (this.DOM.timezoneSelect) {
	        this.DOM.timezoneSelect.value = this.calendarContext.util.getUserOption('timezoneName') || '';
	      }

	      if (this.DOM.syncPeriodPast) {
	        this.DOM.syncPeriodPast.value = this.calendarContext.util.getUserOption('syncPeriodPast') || 3;
	      }

	      if (this.DOM.syncPeriodFuture) {
	        this.DOM.syncPeriodFuture.value = this.calendarContext.util.getUserOption('syncPeriodFuture') || 12;
	      }

	      if (this.showGeneralSettings) {
	        // Set access for calendar type
	        this.DOM.workTimeStart.value = this.settings.work_time_start;
	        this.DOM.workTimeEnd.value = this.settings.work_time_end;

	        if (this.DOM.weekHolidays) {
	          for (var _i = 0; _i < this.DOM.weekHolidays.options.length; _i++) {
	            this.DOM.weekHolidays.options[_i].selected = this.settings.week_holidays.includes(this.DOM.weekHolidays.options[_i].value);
	          }
	        }

	        this.DOM.yearHolidays.value = this.settings.year_holidays;
	        this.DOM.yearWorkdays.value = this.settings.year_workdays;
	      } // Access


	      if (this.showAccessControl && main_core.Type.isElementNode(this.DOM.accessOuterWrap)) {
	        var typeAccess = this.calendarContext.util.config.TYPE_ACCESS;

	        for (var code in typeAccess) {
	          if (typeAccess.hasOwnProperty(code)) {
	            this.insertAccessRow(this.calendarContext.util.getAccessName(code), code, typeAccess[code]);
	          }
	        }
	      }
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var userSettings = this.calendarContext.util.config.userSettings; // Save user settings

	      if (this.DOM.showDeclined) {
	        userSettings.showDeclined = this.DOM.showDeclined.checked ? 1 : 0;
	      }

	      if (this.DOM.showWeekNumbers) {
	        userSettings.showWeekNumbers = this.DOM.showWeekNumbers.checked ? 'Y' : 'N';
	      }

	      if (this.DOM.showTasks) {
	        userSettings.showTasks = this.DOM.showTasks.checked ? 'Y' : 'N';
	      }

	      if (this.DOM.syncTasks) {
	        userSettings.syncTasks = this.DOM.syncTasks.checked ? 'Y' : 'N';
	      }

	      if (this.DOM.showCompletedTasks) {
	        userSettings.showCompletedTasks = this.DOM.showCompletedTasks.checked ? 'Y' : 'N';
	      }

	      if (this.DOM.meetSectionSelect) {
	        userSettings.meetSection = this.DOM.meetSectionSelect.value;
	      }

	      if (this.DOM.crmSelect) {
	        userSettings.crmSection = this.DOM.crmSelect.value;
	      }

	      if (this.DOM.denyBusyInvitation) {
	        userSettings.denyBusyInvitation = this.DOM.denyBusyInvitation.checked ? 1 : 0;
	      }

	      if (this.DOM.timezoneSelect) {
	        userSettings.userTimezoneName = this.DOM.timezoneSelect.value;
	      }

	      if (this.DOM.syncPeriodPast) {
	        userSettings.syncPeriodPast = this.DOM.syncPeriodPast.value;
	      }

	      if (this.DOM.syncPeriodFuture) {
	        userSettings.syncPeriodFuture = this.DOM.syncPeriodFuture.value;
	      }

	      if (this.emailSelectorControl) {
	        userSettings.sendFromEmail = this.emailSelectorControl.getValue();
	      }

	      var data = {
	        type: this.calendarContext.util.config.type,
	        user_settings: userSettings,
	        user_timezone_name: userSettings.userTimezoneName
	      };

	      if (this.showGeneralSettings && this.DOM.workTimeStart) {
	        data.settings = {
	          work_time_start: this.DOM.workTimeStart.value,
	          work_time_end: this.DOM.workTimeEnd.value,
	          week_holidays: [],
	          year_holidays: this.DOM.yearHolidays.value,
	          year_workdays: this.DOM.yearWorkdays.value
	        };

	        for (var i = 0; i < this.DOM.weekHolidays.options.length; i++) {
	          if (this.DOM.weekHolidays.options[i].selected) {
	            data.settings.week_holidays.push(this.DOM.weekHolidays.options[i].value);
	          }
	        }
	      }

	      if (this.showAccessControl) {
	        data.type_access = this.access;
	      }

	      BX.ajax.runAction('calendar.api.calendarajax.saveSettings', {
	        data: data
	      }).then(function () {
	        BX.reload();
	      });
	      this.close();
	    }
	  }, {
	    key: "initAccessController",
	    value: function initAccessController() {
	      var _this$calendarContext,
	          _this$calendarContext2,
	          _this$calendarContext3,
	          _this$calendarContext4,
	          _this$calendarContext5,
	          _this4 = this;

	      this.DOM.accessWrap = this.DOM.accessOuterWrap.appendChild(main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-list-slider-access-container shown\">\n\t\t\t\t\t<div class=\"calendar-list-slider-access-inner-wrap\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"calendar-list-slider-new-calendar-options-container\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>"])), this.DOM.accessTable = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<table class=\"calendar-section-slider-access-table\" />\n\t\t\t\t\t\t"]))), this.DOM.accessButton = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t<span class=\"calendar-list-slider-new-calendar-option-add\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</span>"])), main_core.Loc.getMessage('EC_SEC_SLIDER_ACCESS_ADD'))));
	      this.access = {};
	      this.accessControls = {};
	      this.accessTasks = (_this$calendarContext = this.calendarContext) === null || _this$calendarContext === void 0 ? void 0 : (_this$calendarContext2 = _this$calendarContext.util) === null || _this$calendarContext2 === void 0 ? void 0 : _this$calendarContext2.getTypeAccessTasks();

	      if ((_this$calendarContext3 = this.calendarContext) !== null && _this$calendarContext3 !== void 0 && (_this$calendarContext4 = _this$calendarContext3.util) !== null && _this$calendarContext4 !== void 0 && (_this$calendarContext5 = _this$calendarContext4.config) !== null && _this$calendarContext5 !== void 0 && _this$calendarContext5.accessNames) {
	        calendar_util.Util.setAccessNames(this.calendarContext.util.config.accessNames);
	      }

	      main_core.Event.bind(this.DOM.accessButton, 'click', function () {
	        _this4.entitySelectorDialog = new ui_entitySelector.Dialog({
	          targetNode: _this4.DOM.accessButton,
	          context: 'CALENDAR',
	          preselectedItems: [],
	          enableSearch: true,
	          events: {
	            'Item:onSelect': _this4.handleEntitySelectorChanges.bind(_this4),
	            'Item:onDeselect': _this4.handleEntitySelectorChanges.bind(_this4)
	          },
	          popupOptions: {
	            targetContainer: document.body
	          },
	          entities: [{
	            id: 'user'
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
	          }]
	        });

	        _this4.entitySelectorDialog.show();

	        _this4.entitySelectorDialog.subscribe('onHide', _this4.allowSliderClose.bind(_this4));

	        _this4.denySliderClose();
	      });
	      main_core.Event.bind(this.DOM.accessWrap, 'click', function (e) {
	        var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, _this4.DOM.outerWrap);

	        if (main_core.Type.isElementNode(target)) {
	          if (target.getAttribute('data-bx-calendar-access-selector') !== null) {
	            // show selector
	            var code = target.getAttribute('data-bx-calendar-access-selector');

	            if (_this4.accessControls[code]) {
	              _this4.showAccessSelectorPopup({
	                node: _this4.accessControls[code].removeIcon,
	                setValueCallback: function setValueCallback(value) {
	                  if (_this4.accessTasks[value] && _this4.accessControls[code]) {
	                    _this4.accessControls[code].valueNode.innerHTML = main_core.Text.encode(_this4.accessTasks[value].title);
	                    _this4.access[code] = value;
	                  }
	                }
	              });
	            }
	          } else if (target.getAttribute('data-bx-calendar-access-remove') !== null) {
	            var _code = target.getAttribute('data-bx-calendar-access-remove');

	            if (_this4.accessControls[_code]) {
	              main_core.Dom.remove(_this4.accessControls[_code].rowNode);
	              _this4.accessControls[_code] = null;
	              delete _this4.access[_code];
	            }
	          }
	        }
	      });
	    }
	  }, {
	    key: "handleEntitySelectorChanges",
	    value: function handleEntitySelectorChanges() {
	      var _this5 = this;

	      var entityList = this.entitySelectorDialog.getSelectedItems();
	      this.entitySelectorDialog.hide();

	      if (main_core.Type.isArray(entityList)) {
	        entityList.forEach(function (entity) {
	          var title = entity.title.text;
	          var code = calendar_util.Util.convertEntityToAccessCode(entity);
	          calendar_util.Util.setAccessName(code, title);

	          _this5.insertAccessRow(title, code);
	        });
	      }

	      main_core.Runtime.debounce(function () {
	        _this5.entitySelectorDialog.destroy();
	      }, 400)();
	    }
	  }, {
	    key: "insertAccessRow",
	    value: function insertAccessRow(title, code, value) {
	      if (!this.accessControls[code]) {
	        if (value === undefined) {
	          for (var taskId in this.accessTasks) {
	            if (this.accessTasks.hasOwnProperty(taskId) && this.accessTasks[taskId].name === 'calendar_type_edit') {
	              value = taskId;
	              break;
	            }
	          }
	        }

	        var rowNode = main_core.Dom.adjust(this.DOM.accessTable.insertRow(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-row'
	          }
	        });
	        var titleNode = main_core.Dom.adjust(rowNode.insertCell(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-cell'
	          },
	          html: '<span class="calendar-section-slider-access-title">' + main_core.Text.encode(title) + ':</span>'
	        });
	        var valueCell = main_core.Dom.adjust(rowNode.insertCell(-1), {
	          props: {
	            className: 'calendar-section-slider-access-table-cell'
	          },
	          attrs: {
	            'data-bx-calendar-access-selector': code
	          }
	        });
	        var selectNode = valueCell.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-section-slider-access-container'
	          }
	        }));
	        var valueNode = selectNode.appendChild(main_core.Dom.create('SPAN', {
	          text: this.accessTasks[value] ? this.accessTasks[value].title : '',
	          props: {
	            className: 'calendar-section-slider-access-value'
	          }
	        }));
	        var removeIcon = selectNode.appendChild(main_core.Dom.create('SPAN', {
	          props: {
	            className: 'calendar-section-slider-access-remove'
	          },
	          attrs: {
	            'data-bx-calendar-access-remove': code
	          }
	        }));
	        this.access[code] = value;
	        this.accessControls[code] = {
	          rowNode: rowNode,
	          titleNode: titleNode,
	          valueNode: valueNode,
	          removeIcon: removeIcon
	        };
	      }
	    }
	  }, {
	    key: "checkAccessTableHeight",
	    value: function checkAccessTableHeight() {
	      var _this6 = this;

	      if (this.checkTableTimeout) {
	        this.checkTableTimeout = clearTimeout(this.checkTableTimeout);
	      }

	      this.checkTableTimeout = setTimeout(function () {
	        if (main_core.Dom.hasClass(_this6.DOM.accessWrap, 'shown')) {
	          if (_this6.DOM.accessWrap.offsetHeight - _this6.DOM.accessTable.offsetHeight < 36) {
	            _this6.DOM.accessWrap.style.maxHeight = parseInt(_this6.DOM.accessTable.offsetHeight) + 100 + 'px';
	          }
	        } else {
	          _this6.DOM.accessWrap.style.maxHeight = '';
	        }
	      }, 300);
	    }
	  }, {
	    key: "showAccessSelectorPopup",
	    value: function showAccessSelectorPopup(params) {
	      if (this.accessPopupMenu && this.accessPopupMenu.popupWindow && this.accessPopupMenu.popupWindow.isShown()) {
	        return this.accessPopupMenu.close();
	      }

	      var _this = this;

	      var menuItems = [];

	      for (var taskId in this.accessTasks) {
	        if (this.accessTasks.hasOwnProperty(taskId)) {
	          menuItems.push({
	            text: this.accessTasks[taskId].title,
	            onclick: function (value) {
	              return function () {
	                params.setValueCallback(value);

	                _this.accessPopupMenu.close();
	              };
	            }(taskId)
	          });
	        }
	      }

	      this.accessPopupMenu = this.BX.PopupMenu.create('section-access-popup' + calendar_util.Util.randomInt(), params.node, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        offsetTop: -5,
	        offsetLeft: 0,
	        angle: true,
	        cacheable: false,
	        events: {
	          onPopupClose: this.allowSliderClose.bind(this)
	        }
	      });
	      this.accessPopupMenu.show();
	      this.denySliderClose();
	    }
	  }, {
	    key: "openHelpDesk",
	    value: function openHelpDesk() {
	      var helpDeskCode = 14326208;
	      top.BX.Helper.show('redirect=detail&code=' + helpDeskCode);
	    }
	  }, {
	    key: "showMessage",
	    value: function showMessage() {
	      if (this.message) {
	        this.message.show();
	        this.DOM.accessMessageWrap.style.maxHeight = 300 + "px";
	        main_core.Dom.addClass(this.DOM.accessHelpIcon, 'calendar-settings-message-arrow-target');
	      }
	    }
	  }, {
	    key: "hideMessage",
	    value: function hideMessage() {
	      if (this.message) {
	        main_core.Dom.removeClass(this.DOM.accessHelpIcon, 'calendar-settings-message-arrow-target');
	        this.message.hide();
	        this.DOM.accessMessageWrap.style.maxHeight = 0;

	        if (!this.calendarContext.util.config.hideSettingsHintLocation) {
	          BX.ajax.runAction('calendar.api.locationajax.hideSettingsHintLocation', {
	            data: {
	              value: true
	            }
	          }).then(function () {});
	        }
	      }
	    }
	  }]);
	  return SettingsInterface;
	}();

	exports.SettingsInterface = SettingsInterface;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar.Controls,BX,BX.UI.EntitySelector,BX.Event,BX.UI));
//# sourceMappingURL=settingsinterface.bundle.js.map
