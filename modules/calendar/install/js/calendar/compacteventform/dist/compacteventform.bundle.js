this.BX = this.BX || {};
(function (exports,main_core,main_core_events,calendar_util,main_popup,calendar_controls,calendar_entry,calendar_sectionmanager,calendar_sync_interface) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13, _templateObject14, _templateObject15, _templateObject16, _templateObject17, _templateObject18, _templateObject19, _templateObject20, _templateObject21, _templateObject22, _templateObject23, _templateObject24, _templateObject25, _templateObject26, _templateObject27, _templateObject28, _templateObject29, _templateObject30;
	var CompactEventForm = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(CompactEventForm, _EventEmitter);

	  function CompactEventForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, CompactEventForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CompactEventForm).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "STATE", {
	      READY: 1,
	      REQUEST: 2,
	      ERROR: 3
	    });
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "zIndex", 1200);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "Z_INDEX_OFFSET", -1000);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "userSettings", '');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "displayed", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sections", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "sectionIndex", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "trackingUsersList", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "checkDataBeforeCloseMode", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "CHECK_CHANGES_DELAY", 500);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "RELOAD_DATA_DELAY", 500);

	    _this.setEventNamespace('BX.Calendar.CompactEventForm');

	    _this.userId = options.userId || calendar_util.Util.getCurrentUserId();
	    _this.type = options.type || 'user';
	    _this.isLocationCalendar = options.isLocationCalendar || false;
	    _this.calendarContext = options.calendarContext || null;
	    _this.ownerId = options.ownerId || _this.userId;
	    _this.BX = calendar_util.Util.getBX();
	    _this.checkForChangesDebounce = main_core.Runtime.debounce(_this.checkForChanges, _this.CHECK_CHANGES_DELAY, babelHelpers.assertThisInitialized(_this));
	    _this.reloadEntryDataDebounce = main_core.Runtime.debounce(_this.reloadEntryData, _this.RELOAD_DATA_DELAY, babelHelpers.assertThisInitialized(_this));
	    _this.checkOutsideClickClose = _this.checkOutsideClickClose.bind(babelHelpers.assertThisInitialized(_this));
	    _this.outsideMouseDownClose = _this.outsideMouseDownClose.bind(babelHelpers.assertThisInitialized(_this));
	    _this.keyHandler = _this.handleKeyPress.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(CompactEventForm, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : CompactEventForm.EDIT_MODE;
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.setParams(params);
	      this.setMode(mode);
	      this.state = this.STATE.READY;
	      this.popupId = 'compact-event-form-' + Math.round(Math.random() * 100000);

	      if (this.popup) {
	        this.popup.destroy();
	      }

	      this.popup = this.getPopup(params); // Small hack to use transparent titlebar to drag&drop popup

	      main_core.Dom.addClass(this.popup.titleBar, 'calendar-add-popup-titlebar');
	      main_core.Dom.removeClass(this.popup.popupContainer, 'popup-window-with-titlebar');
	      main_core.Dom.removeClass(this.popup.closeIcon, 'popup-window-titlebar-close-icon');
	      main_core.Event.bind(document, "mousedown", this.outsideMouseDownClose);
	      main_core.Event.bind(document, "mouseup", this.checkOutsideClickClose);
	      main_core.Event.bind(document, "keydown", this.keyHandler);
	      main_core.Event.bind(this.popup.popupContainer, 'transitionend', function () {
	        main_core.Dom.removeClass(_this2.popup.popupContainer, 'calendar-simple-view-popup-show');
	      }); // Fulfill previous deletions to avoid data inconsistency

	      if (this.getMode() === CompactEventForm.EDIT_MODE) {
	        calendar_entry.EntryManager.doDelayedActions();
	      }

	      this.prepareData().then(function () {
	        if (_this2.checkLocationView()) {
	          _this2.setFormValuesLocation();
	        } else {
	          _this2.setFormValues();
	        }

	        _this2.popup.show();

	        _this2.checkDataBeforeCloseMode = true;

	        if (_this2.canDo('edit') && _this2.DOM.titleInput && mode === CompactEventForm.EDIT_MODE) {
	          _this2.DOM.titleInput.focus();

	          _this2.DOM.titleInput.select();
	        }

	        _this2.displayed = true;

	        if (_this2.getMode() === CompactEventForm.VIEW_MODE) {
	          calendar_util.Util.sendAnalyticLabel({
	            calendarAction: 'view_event',
	            formType: 'compact'
	          });

	          _this2.popup.getButtons()[0].button.focus();
	        }

	        if (_this2.getMode() === CompactEventForm.EDIT_MODE && !_this2.userPlannerSelector.isPlannerDisplayed()) {
	          _this2.userPlannerSelector.checkBusyTime();
	        }
	      });
	    }
	  }, {
	    key: "checkLocationView",
	    value: function checkLocationView() {
	      return this.getMode() === CompactEventForm.VIEW_MODE && this.type === 'location';
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup(params) {
	      return new main_popup.Popup(this.popupId, params.bindNode, {
	        zIndex: this.zIndex + this.Z_INDEX_OFFSET,
	        closeByEsc: true,
	        offsetTop: 0,
	        offsetLeft: 0,
	        closeIcon: true,
	        titleBar: true,
	        draggable: true,
	        resizable: false,
	        lightShadow: true,
	        className: 'calendar-simple-view-popup calendar-simple-view-popup-show',
	        cacheable: false,
	        content: this.checkLocationView() ? this.getPopupContentLocation() : this.getPopupContentCalendar(),
	        buttons: this.getButtons(),
	        events: {
	          onPopupClose: this.close.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.displayed;
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      var _this3 = this;

	      if (this.getMode() === CompactEventForm.EDIT_MODE && this.formDataChanged() && this.checkDataBeforeCloseMode && !confirm(main_core.Loc.getMessage('EC_SAVE_ENTRY_CONFIRM'))) {
	        // Workaround to prevent form closing even if user don't want to and presses "cancel" in confirm
	        if (this.popup) {
	          this.popup.destroyed = true;
	          setTimeout(function () {
	            _this3.popup.destroyed = false;
	          }, 0);
	        }

	        return;
	      }

	      this.displayed = false;
	      this.emit('onClose');
	      main_core.Event.unbind(document, "mousedown", this.outsideMouseDownClose);
	      main_core.Event.unbind(document, "mouseup", this.checkOutsideClickClose);
	      main_core.Event.unbind(document, "keydown", this.keyHandler);

	      if (this.userPlannerSelector) {
	        this.userPlannerSelector.destroy();
	      }

	      if (this.popup) {
	        this.popup.destroy();
	      }

	      if (calendar_controls.Location) {
	        calendar_controls.Location.setCurrentCapacity(0);
	      }

	      calendar_util.Util.clearPlannerWatches();
	      calendar_util.Util.closeAllPopups();
	    }
	  }, {
	    key: "getPopupContentCalendar",
	    value: function getPopupContentCalendar() {
	      this.DOM.wrap = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-add-popup-wrap\">\n\t\t\t", "\n\t\t\t<div class=\"calendar-field-container calendar-field-container-choice\">\n\t\t\t\t", "\n\t\t\t</div>\n\n\t\t\t", "\n\n\t\t\t", "\n\n\t\t\t<div class=\"calendar-field-container calendar-field-container-info\">\n\t\t\t\t", "\n\n\t\t\t\t\t", "\n\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t</div>"])), this.DOM.titleOuterWrap = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-container calendar-field-container-string-select\">\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"])), this.getTitleControl(), this.getColorControl()), this.getSectionControl(), this.getDateTimeControl(), this.getUserPlannerSelector(), this.getTypeInfoControl(), this.getLocationControl(), this.DOM.remindersOuterWrap = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>"])), main_core.Loc.getMessage('EC_REMIND_LABEL'), this.createRemindersControl()), this.getRRuleInfoControl());
	      return this.DOM.wrap;
	    }
	  }, {
	    key: "getPopupContentLocation",
	    value: function getPopupContentLocation() {
	      this.DOM.wrap = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-add-popup-wrap\">\n\t\t\t", "\n\t\t\t<div class=\"calendar-field-container calendar-field-container-choice\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t", "\n\t\t\t\n\t\t</div>"])), this.DOM.titleOuterWrap = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-container calendar-field-container-string-select\">\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"])), this.getTitleControlLocation(), this.getColorControlsLocationView()), this.getLocationSectionControls(), this.getDateTimeControl());

	      if (this.entry.id !== this.entry.parentId) {
	        this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t", "\n\t\t\t"])), this.getHostControl()));
	      }

	      return this.DOM.wrap;
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      var _this4 = this;

	      var buttons = [];
	      var mode = this.getMode();

	      if (mode === CompactEventForm.EDIT_MODE) {
	        buttons.push(new BX.UI.Button({
	          name: 'save',
	          text: this.isNewEntry() ? main_core.Loc.getMessage('CALENDAR_EVENT_DO_ADD') : main_core.Loc.getMessage('CALENDAR_EVENT_DO_SAVE'),
	          className: "ui-btn ui-btn-primary",
	          events: {
	            click: function click() {
	              _this4.checkDataBeforeCloseMode = false;

	              _this4.save();
	            }
	          }
	        })); // For testing purposes

	        if (main_core.Type.isElementNode(buttons[0].button)) {
	          buttons[0].button.setAttribute('data-role', 'saveButton');
	        }

	        buttons.push(new BX.UI.Button({
	          text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
	          className: "ui-btn ui-btn-link",
	          events: {
	            click: function click() {
	              if (_this4.isNewEntry()) {
	                _this4.checkDataBeforeCloseMode = false;

	                _this4.close();
	              } else {
	                _this4.setFormValues();

	                if (_this4.userPlannerSelector) {
	                  _this4.userPlannerSelector.destroy();
	                }

	                _this4.setMode(CompactEventForm.VIEW_MODE);

	                _this4.popup.setButtons(_this4.getButtons());
	              }
	            }
	          }
	        }));
	        buttons.push(new BX.UI.Button({
	          text: main_core.Loc.getMessage('CALENDAR_EVENT_FULL_FORM'),
	          className: "ui-btn calendar-full-form-btn",
	          events: {
	            click: this.editEntryInSlider.bind(this)
	          }
	        })); //sideButton = true;
	        // if (!this.isNewEntry() && this.canDo('delete'))
	        // {
	        // 	buttons.push(
	        // 		new BX.UI.Button({
	        // 			text : Loc.getMessage('CALENDAR_EVENT_DO_DELETE'),
	        // 			className: "ui-btn ui-btn-link",
	        // 			events : {click : ()=>{
	        // 				EntryManager.deleteEntry(this.entry);
	        // 			}}
	        // 		})
	        // 	);
	        // }
	      } else if (mode === CompactEventForm.VIEW_MODE) {
	        if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Q') {
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-primary",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_Y'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this4.entry, 'Y').then(_this4.refreshMeetingStatus.bind(_this4));
	              }
	            }
	          }));
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-link",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_N'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this4.entry, 'N').then(function () {
	                  if (_this4.isShown()) {
	                    _this4.close();
	                  }
	                });
	              }
	            }
	          }));
	        }

	        if (this.checkLocationView()) {
	          if (this.entry.id !== this.entry.parentId) {
	            buttons.push(new BX.UI.Button({
	              className: "ui-btn ".concat(this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Q' ? 'ui-btn-link' : 'ui-btn-primary'),
	              text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_OPEN_PARENT'),
	              events: {
	                click: function click() {
	                  _this4.checkDataBeforeCloseMode = false;
	                  BX.Calendar.EntryManager.openViewSlider(_this4.entry.parentId, {
	                    userId: _this4.userId,
	                    from: _this4.entry.from,
	                    timezoneOffset: _this4.entry && _this4.entry.data ? _this4.entry.data.TZ_OFFSET_FROM : null
	                  });

	                  _this4.close();
	                }
	              }
	            }));
	          } else {
	            buttons.push(new BX.UI.Button({
	              className: "ui-btn ui-btn-disabled",
	              text: main_core.Loc.getMessage('CALENDAR_UPDATE_PROGRESS')
	            }));
	          }
	        } else {
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ".concat(this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Q' ? 'ui-btn-link' : 'ui-btn-primary'),
	            text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_OPEN'),
	            events: {
	              click: function click() {
	                _this4.checkDataBeforeCloseMode = false;
	                BX.Calendar.EntryManager.openViewSlider(_this4.entry.id, {
	                  entry: _this4.entry,
	                  calendarContext: _this4.calendarContext,
	                  type: _this4.type,
	                  ownerId: _this4.ownerId,
	                  userId: _this4.userId,
	                  from: _this4.entry.from,
	                  timezoneOffset: _this4.entry && _this4.entry.data ? _this4.entry.data.TZ_OFFSET_FROM : null
	                });

	                _this4.close();
	              }
	            }
	          }));
	        } // For testing purposes


	        if (main_core.Type.isElementNode(buttons[buttons.length - 1].button)) {
	          buttons[buttons.length - 1].button.setAttribute('data-role', 'openButton');
	        }

	        if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'N') {
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-link",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_Y'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this4.entry, 'Y').then(_this4.refreshMeetingStatus.bind(_this4));
	              }
	            }
	          }));
	        }

	        if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Y') {
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-link",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_N'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this4.entry, 'N').then(_this4.refreshMeetingStatus.bind(_this4));
	              }
	            }
	          }));
	        }

	        if (!this.isNewEntry() && this.canDo('edit') && this.type !== 'location') {
	          buttons.push(new BX.UI.Button({
	            text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_EDIT'),
	            className: "ui-btn ui-btn-link",
	            events: {
	              click: this.editEntryInSlider.bind(this)
	            }
	          }));
	        }

	        if (!this.isNewEntry() && this.canDo('delete') && this.entry.getCurrentStatus() === 'H' && !this.checkLocationView()) {
	          if (!this.entry.isMeeting() || !this.entry.getCurrentStatus() || this.entry.getCurrentStatus() === 'H') {
	            buttons.push(new BX.UI.Button({
	              text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_DELETE'),
	              className: "ui-btn ui-btn-link",
	              events: {
	                click: function click() {
	                  main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	                    _this4.checkDataBeforeCloseMode = false;

	                    _this4.close();
	                  });
	                  calendar_entry.EntryManager.deleteEntry(_this4.entry);

	                  if (!_this4.entry.wasEverRecursive()) {
	                    _this4.close();
	                  }
	                }
	              }
	            }));
	          }
	        }
	      }

	      if (buttons.length > 2) {
	        buttons[1].button.className = "ui-btn ui-btn-light-border";
	      }

	      return buttons;
	    }
	  }, {
	    key: "freezePopup",
	    value: function freezePopup() {
	      if (this.popup) {
	        this.popup.buttons.forEach(function (button) {
	          var _button$options;

	          if ((button === null || button === void 0 ? void 0 : (_button$options = button.options) === null || _button$options === void 0 ? void 0 : _button$options.name) === 'save') {
	            button.setClocking(true);
	          } else {
	            button.setDisabled(true);
	          }
	        });
	      }
	    }
	  }, {
	    key: "unfreezePopup",
	    value: function unfreezePopup() {
	      if (this.popup) {
	        this.popup.buttons.forEach(function (button) {
	          button.setClocking(false);
	          button.setDisabled(false);
	        });
	      }
	    }
	  }, {
	    key: "refreshMeetingStatus",
	    value: function refreshMeetingStatus() {
	      this.emit('doRefresh');
	      this.popup.setButtons(this.getButtons());

	      if (this.userPlannerSelector) {
	        this.userPlannerSelector.displayAttendees(this.entry.getAttendees());
	      }
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (main_core.Type.isDomNode(this.DOM.loader)) {
	        main_core.Dom.remove(this.DOM.loader);
	        this.DOM.loader = null;
	      }
	    }
	  }, {
	    key: "showInEditMode",
	    value: function showInEditMode() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return this.show(CompactEventForm.EDIT_MODE, params);
	    }
	  }, {
	    key: "showInViewMode",
	    value: function showInViewMode() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return this.show(CompactEventForm.VIEW_MODE, params);
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {
	      if (mode === 'edit' || mode === 'view') {
	        this.mode = mode;
	      }
	    }
	  }, {
	    key: "getMode",
	    value: function getMode() {
	      return this.mode;
	    }
	  }, {
	    key: "checkForChanges",
	    value: function checkForChanges() {
	      if (!this.isNewEntry() && this.getMode() === CompactEventForm.VIEW_MODE && this.formDataChanged()) {
	        this.setMode(CompactEventForm.EDIT_MODE);
	        this.popup.setButtons(this.getButtons());
	      } else if (!this.isNewEntry() && this.getMode() === CompactEventForm.EDIT_MODE && !this.formDataChanged()) {
	        this.setMode(CompactEventForm.VIEW_MODE);
	        this.popup.setButtons(this.getButtons());
	      }

	      this.emitOnChange();
	    }
	  }, {
	    key: "checkLocationForm",
	    value: function checkLocationForm(event) {
	      if (event && event instanceof main_core_events.BaseEvent) {
	        var data = event.getData();
	        var usersCount = data.usersCount;
	        var locationCapacity = calendar_controls.Location.getCurrentCapacity() || 0;

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
	  }, {
	    key: "getFormDataChanges",
	    value: function getFormDataChanges() {
	      var excludes = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      var entry = this.entry;
	      var fields = []; // Name

	      if (!excludes.includes('name') && entry.name !== this.DOM.titleInput.value) {
	        fields.push('name');
	      } // Location


	      if (!excludes.includes('location') && this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(entry.getLocation())) !== this.locationSelector.getTextLocation(calendar_controls.Location.parseStringValue(this.locationSelector.getTextValue()))) {
	        fields.push('location');
	      } // Date + time


	      var dateTime = this.dateTimeControl.getValue();

	      if (!excludes.includes('date&time') && (entry.isFullDay() !== dateTime.fullDay || dateTime.from.toString() !== entry.from.toString() || dateTime.to.toString() !== entry.to.toString())) {
	        fields.push('date&time');
	      } // Notify


	      if (!excludes.includes('notify') && (!entry.isMeeting() || entry.getMeetingNotify()) !== this.userPlannerSelector.getInformValue()) {
	        fields.push('notify');
	      } // Section


	      if (!excludes.includes('section') && parseInt(entry.sectionId) !== parseInt(this.sectionValue)) {
	        fields.push('section');
	      } // Access codes


	      if (!excludes.includes('codes') && this.userPlannerSelector.getEntityList().map(function (item) {
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
	    key: "setParams",
	    value: function setParams() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.userId = params.userId || calendar_util.Util.getCurrentUserId();
	      this.type = params.type || 'user';
	      this.isLocationCalendar = params.isLocationCalendar || false;
	      this.locationAccess = params.locationAccess || false;
	      this.dayOfWeekMonthFormat = params.dayOfWeekMonthFormat || false;
	      this.calendarContext = params.calendarContext || null;
	      this.ownerId = params.ownerId ? params.ownerId : 0;

	      if (this.type === 'user' && !this.ownerId) {
	        this.ownerId = this.userId;
	      }

	      this.entry = calendar_entry.EntryManager.getEntryInstance(params.entry, params.userIndex, {
	        type: this.type,
	        ownerId: this.ownerId
	      });
	      this.sectionValue = null;

	      if (!this.entry.id && main_core.Type.isPlainObject(params.entryTime) && main_core.Type.isDate(params.entryTime.from) && main_core.Type.isDate(params.entryTime.to)) {
	        this.entry.setDateTimeValue(params.entryTime);
	      }

	      if (main_core.Type.isPlainObject(params.userSettings)) {
	        this.userSettings = params.userSettings;
	      }

	      this.locationFeatureEnabled = !!params.locationFeatureEnabled;
	      this.locationList = params.locationList || [];
	      this.roomsManager = params.roomsManager || null;
	      this.iblockMeetingRoomList = params.iblockMeetingRoomList || [];
	      this.plannerFeatureEnabled = !!params.plannerFeatureEnabled;
	      this.setSections(params.sections, params.trackingUserList);
	    }
	  }, {
	    key: "setSections",
	    value: function setSections(sections) {
	      var _this5 = this;

	      var trackingUsersList = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      this.sections = sections;
	      this.sectionIndex = {};
	      this.trackingUsersList = trackingUsersList || [];

	      if (main_core.Type.isArray(sections)) {
	        sections.forEach(function (value, ind) {
	          var id = parseInt(value.ID || value.id);

	          if (id > 0) {
	            _this5.sectionIndex[id] = ind;
	          }
	        }, this);
	      }
	    }
	  }, {
	    key: "prepareData",
	    value: function prepareData() {
	      var _this6 = this;
	      return new Promise(function (resolve) {
	        var section = _this6.getCurrentSection();

	        if (section && section.canDo) {
	          resolve();
	        } else {
	          _this6.BX.ajax.runAction('calendar.api.calendarajax.getCompactFormData', {
	            data: {
	              entryId: _this6.entry.id,
	              loadSectionId: _this6.entry.sectionId
	            }
	          }).then(function (response) {
	            if (response && response.data && response.data.section) {
	              // todo: refactor this part to new Section entities
	              _this6.sections.push(new window.BXEventCalendar.Section(calendar_util.Util.getCalendarContext(), response.data.section));

	              _this6.setSections(_this6.sections);

	              resolve();
	            }
	          });
	        }
	      });
	    }
	  }, {
	    key: "getTitleControl",
	    value: function getTitleControl() {
	      this.DOM.titleInput = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input class=\"calendar-field calendar-field-string\"\n\t\t\t\tvalue=\"\"\n\t\t\t\tplaceholder=\"", "\"\n\t\t\t\ttype=\"text\"\n\t\t\t/>\n\t\t"])), main_core.Loc.getMessage('EC_ENTRY_NAME'));
	      main_core.Event.bind(this.DOM.titleInput, 'keyup', this.checkForChangesDebounce);
	      main_core.Event.bind(this.DOM.titleInput, 'change', this.checkForChangesDebounce);
	      return this.DOM.titleInput;
	    }
	  }, {
	    key: "getTitleControlLocation",
	    value: function getTitleControlLocation() {
	      this.DOM.titleInput = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input class=\"calendar-field calendar-field-string\"\n\t\t\t\tvalue=\"\"\n\t\t\t\tplaceholder=\"", "\"\n\t\t\t\ttype=\"text\"\n\t\t\t\treadonly\n\t\t\t/>\n\t\t"])), main_core.Loc.getMessage('EC_ENTRY_NAME'));
	      return this.DOM.titleInput;
	    }
	  }, {
	    key: "getHostControl",
	    value: function getHostControl() {
	      var userId = this.entry.data.CREATED_BY;
	      var userUrl = CompactEventForm.USER_URL.replace('#USER_ID#', userId);
	      var userAvatar = this.BX.Calendar.EntryManager.userIndex[userId] ? this.BX.Calendar.EntryManager.userIndex[userId].AVATAR : '';
	      this.DOM.hostBar = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-slider-detail-option-without-border\">\n\t\t\t\t<div class=\"calendar-slider-detail-option-block\">\n\t\t\t\t\t<div class=\"calendar-field-value\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<span class=\"calendar-field-location-host-img\">\n\t\t\t\t\t\t<a href=\"", "\">\n\t\t\t\t\t\t\t<img class=\"calendar-field-location-host-img-value\" src=\"", "\">\n\t\t\t\t\t\t</a>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div class=\"calendar-slider-detail-option-value\">\n\t\t\t\t\t\t<a href=\"", "\" class=\"calendar-slider-sidebar-user-info-name calendar-slider-sidebar-user-info-name-padding\">", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('EC_HOST') + ': ', userUrl, userAvatar, userUrl, BX.util.htmlspecialchars(this.entry.name));
	      return this.DOM.hostBar;
	    }
	  }, {
	    key: "getColorControl",
	    value: function getColorControl() {
	      var _this7 = this;

	      this.DOM.colorSelect = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field calendar-field-select calendar-field-tiny\"></div>"])));
	      this.colorSelector = new calendar_controls.ColorSelector({
	        wrap: this.DOM.colorSelect,
	        mode: 'selector'
	      });
	      this.colorSelector.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var color = event.getData().value;

	          if (!_this7.isNewEntry() && (_this7.canDo('edit') || _this7.entry.getCurrentStatus() !== false)) {
	            _this7.BX.ajax.runAction('calendar.api.calendarajax.updateColor', {
	              data: {
	                entryId: _this7.entry.id,
	                userId: _this7.userId,
	                color: color
	              }
	            });

	            _this7.entry.data.COLOR = color;

	            _this7.emit('doRefresh');

	            _this7.emitOnChange();
	          }
	        }
	      });
	      return this.DOM.colorSelect;
	    }
	  }, {
	    key: "getColorControlsLocationView",
	    value: function getColorControlsLocationView() {
	      this.DOM.colorSelect = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field calendar-field-select calendar-colorpicker-readonly calendar-field-tiny\"></div>"])));
	      this.colorSelector = new calendar_controls.ColorSelector({
	        wrap: this.DOM.colorSelect,
	        mode: 'view'
	      });
	      return this.DOM.colorSelect;
	    }
	  }, {
	    key: "getSectionControl",
	    value: function getSectionControl() {
	      var _this8 = this;

	      this.DOM.sectionSelectWrap = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-choice-calendar\"></div>"])));
	      this.sectionSelector = new calendar_controls.SectionSelector({
	        outerWrap: this.DOM.sectionSelectWrap,
	        defaultCalendarType: this.type,
	        defaultOwnerId: this.ownerId,
	        sectionList: this.sections,
	        sectionGroupList: calendar_sectionmanager.SectionManager.getSectionGroupList({
	          type: this.type,
	          ownerId: this.ownerId,
	          userId: this.userId,
	          trackingUsersList: this.trackingUsersList
	        }),
	        mode: 'textselect',
	        zIndex: this.zIndex,
	        getCurrentSection: function getCurrentSection() {
	          var section = _this8.getCurrentSection();

	          if (section) {
	            return {
	              id: section.id,
	              name: section.name,
	              color: section.color
	            };
	          }

	          return false;
	        },
	        selectCallback: function selectCallback(sectionValue) {
	          if (sectionValue) {
	            if (_this8.colorSelector) {
	              _this8.colorSelector.setValue(sectionValue.color);
	            }

	            _this8.sectionValue = sectionValue.id;

	            _this8.checkForChangesDebounce();

	            calendar_sectionmanager.SectionManager.saveDefaultSectionId(_this8.sectionValue);
	          }
	        }
	      });
	      return this.DOM.sectionSelectWrap;
	    }
	  }, {
	    key: "getLocationSectionControls",
	    value: function getLocationSectionControls() {
	      var _this9 = this;

	      this.DOM.sectionSelectWrap = main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-choice-calendar\"></div>"])));
	      this.sectionSelector = new calendar_controls.SectionSelector({
	        outerWrap: this.DOM.sectionSelectWrap,
	        defaultCalendarType: this.type,
	        defaultOwnerId: this.ownerId,
	        sectionList: this.sections,
	        sectionGroupList: calendar_sectionmanager.SectionManager.getSectionGroupList({
	          type: this.type,
	          ownerId: this.ownerId,
	          userId: this.userId,
	          trackingUsersList: this.trackingUsersList
	        }),
	        mode: 'location',
	        zIndex: this.zIndex,
	        getCurrentSection: function getCurrentSection() {
	          var section = _this9.getCurrentSection();

	          if (section) {
	            return {
	              id: section.id,
	              name: section.name,
	              color: section.color
	            };
	          }

	          return false;
	        }
	      });
	      return this.DOM.sectionSelectWrap;
	    }
	  }, {
	    key: "getDateTimeControl",
	    value: function getDateTimeControl() {
	      var _this10 = this;

	      this.DOM.dateTimeWrap = main_core.Tag.render(_templateObject14 || (_templateObject14 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-container calendar-field-container-datetime\"></div>"])));
	      this.dateTimeControl = new calendar_controls.DateTimeControl(null, {
	        showTimezone: false,
	        outerWrap: this.DOM.dateTimeWrap,
	        inlineEditMode: true
	      });
	      this.dateTimeControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var value = event.getData().value;

	          if (_this10.remindersControl) {
	            _this10.remindersControl.setFullDayMode(value.fullDay);

	            if (_this10.isNewEntry() && !_this10.remindersControl.wasChangedByUser()) {
	              var defaultReminders = calendar_entry.EntryManager.getNewEntryReminders(value.fullDay ? 'fullDay' : 'withTime');

	              _this10.remindersControl.setValue(defaultReminders, false);
	            }
	          }

	          if (_this10.userPlannerSelector) {
	            if (!_this10.userPlannerSelector.isPlannerDisplayed()) {
	              _this10.userPlannerSelector.showPlanner();
	            }

	            _this10.userPlannerSelector.setLocationValue(_this10.locationSelector.getTextValue());

	            _this10.userPlannerSelector.setDateTime(value, true);

	            _this10.userPlannerSelector.refreshPlannerStateDebounce();
	          }

	          if (_this10.locationSelector) {
	            _this10.locationSelector.checkLocationAccessibility({
	              from: event.getData().value.from,
	              to: event.getData().value.to,
	              fullDay: event.getData().value.fullDay,
	              currentEventId: _this10.entry.id
	            });
	          }

	          _this10.checkForChangesDebounce();
	        }
	      });
	      return this.DOM.dateTimeWrap;
	    }
	  }, {
	    key: "getUserPlannerSelector",
	    value: function getUserPlannerSelector() {
	      this.DOM.userPlannerSelectorOuterWrap = main_core.Tag.render(_templateObject15 || (_templateObject15 = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t<div class=\"calendar-field-container calendar-field-container-members\">\n\t\t\t\t", "\n\t\t\t\t<span class=\"calendar-videocall-wrap calendar-videocall-hidden\"></span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t<div class=\"calendar-user-selector-wrap\"></div>\n\t\t\t<div class=\"calendar-add-popup-planner-wrap calendar-add-popup-show-planner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t", "\n\t\t<div>"])), this.DOM.userSelectorWrap = main_core.Tag.render(_templateObject16 || (_templateObject16 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t<div class=\"calendar-members-selected\">\n\t\t\t\t\t\t<span class=\"calendar-attendees-label\"></span>\n\t\t\t\t\t\t<span class=\"calendar-attendees-list\"></span>\n\t\t\t\t\t\t<span class=\"calendar-members-more\">", "</span>\n\t\t\t\t\t\t<span class=\"calendar-members-change-link\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>"])), main_core.Loc.getMessage('EC_ATTENDEES_MORE'), main_core.Loc.getMessage('EC_SEC_SLIDER_CHANGE')), this.DOM.informWrap = main_core.Tag.render(_templateObject17 || (_templateObject17 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-container-inform\">\n\t\t\t\t\t<span class=\"calendar-field-container-inform-text\">", "</span>\n\t\t\t\t</div>"])), main_core.Loc.getMessage('EC_NOTIFY_OPTION')), this.DOM.plannerOuterWrap = main_core.Tag.render(_templateObject18 || (_templateObject18 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-planner-wrapper\" style=\"height: 0\">\n\t\t\t\t</div>"]))), this.DOM.hideGuestsWrap = main_core.Tag.render(_templateObject19 || (_templateObject19 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-hide-members-container\" style=\"display: none;\">\n\t\t\t\t<div class=\"calendar-hide-members-container-inner\">\n\t\t\t\t\t<div class=\"calendar-hide-members-icon-hidden\"></div>\n\t\t\t\t\t<div class=\"calendar-hide-members-text\">", "</div>\n\t\t\t\t\t<span class=\"calendar-hide-members-helper\" data-hint=\"", "\"></span>\n\t\t\t\t</div>\n\t\t\t</div>"])), main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES'), main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES_HINT')));
	      this.userPlannerSelector = new calendar_controls.UserPlannerSelector({
	        outerWrap: this.DOM.userPlannerSelectorOuterWrap,
	        wrap: this.DOM.userSelectorWrap,
	        informWrap: this.DOM.informWrap,
	        plannerOuterWrap: this.DOM.plannerOuterWrap,
	        hideGuestsWrap: this.DOM.hideGuestsWrap,
	        readOnlyMode: false,
	        userId: this.userId,
	        type: this.type,
	        ownerId: this.ownerId,
	        zIndex: this.zIndex + 10,
	        plannerFeatureEnabled: this.plannerFeatureEnabled,
	        dayOfWeekMonthFormat: this.dayOfWeekMonthFormat
	      });
	      this.userPlannerSelector.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
	      this.userPlannerSelector.subscribe('onNotifyChange', this.checkForChangesDebounce);
	      this.userPlannerSelector.subscribe('onUserCodesChange', this.checkForChangesDebounce);
	      return this.DOM.userPlannerSelectorOuterWrap;
	    }
	  }, {
	    key: "getLocationControl",
	    value: function getLocationControl() {
	      var _this11 = this;

	      this.DOM.locationWrap = main_core.Tag.render(_templateObject20 || (_templateObject20 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-place\"></div>"])));
	      this.DOM.locationOuterWrap = main_core.Tag.render(_templateObject21 || (_templateObject21 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-block\">\n\t\t\t<div class=\"calendar-field-title calendar-field-title-align-top\">", ":</div>\n\t\t\t", "\n\t\t</div>"])), main_core.Loc.getMessage('EC_LOCATION_LABEL'), this.DOM.locationWrap);
	      this.locationSelector = new calendar_controls.Location({
	        wrap: this.DOM.locationWrap,
	        richLocationEnabled: this.locationFeatureEnabled,
	        locationList: this.locationList || [],
	        roomsManager: this.roomsManager || null,
	        locationAccess: this.locationAccess || false,
	        iblockMeetingRoomList: this.iblockMeetingRoomList || [],
	        inlineEditModeEnabled: !this.isLocationCalendar,
	        onChangeCallback: function onChangeCallback() {
	          if (_this11.userPlannerSelector) {
	            _this11.userPlannerSelector.setLocationValue(_this11.locationSelector.getTextValue());

	            if (_this11.locationSelector.getValue().type !== undefined && !_this11.userPlannerSelector.isPlannerDisplayed()) {
	              _this11.userPlannerSelector.showPlanner();
	            }

	            _this11.userPlannerSelector.refreshPlannerStateDebounce();
	          }

	          _this11.checkForChangesDebounce();
	        }
	      });

	      if (this.userPlannerSelector) {
	        this.userPlannerSelector.subscribe('onDisplayAttendees', this.checkLocationForm.bind(this));
	      }

	      return this.DOM.locationOuterWrap;
	    }
	  }, {
	    key: "createRemindersControl",
	    value: function createRemindersControl() {
	      var _this12 = this;

	      this.reminderValues = [];
	      this.DOM.remindersWrap = main_core.Tag.render(_templateObject22 || (_templateObject22 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"])));
	      this.remindersControl = new calendar_controls.Reminder({
	        wrap: this.DOM.remindersWrap,
	        zIndex: this.zIndex
	      });
	      this.remindersControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          _this12.reminderValues = event.getData().values;

	          if (!_this12.isNewEntry() && (_this12.canDo('edit') || _this12.entry.getCurrentStatus() !== false)) {
	            _this12.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
	              data: {
	                entryId: _this12.entry.id,
	                userId: _this12.userId,
	                reminders: _this12.reminderValues
	              }
	            }).then(function (response) {
	              _this12.entry.data.REMIND = response.data.REMIND;
	            });
	          }
	        }
	      });
	      return this.DOM.remindersWrap;
	    }
	  }, {
	    key: "getTypeInfoControl",
	    value: function getTypeInfoControl() {
	      this.DOM.typeInfoTitle = main_core.Tag.render(_templateObject23 || (_templateObject23 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-title\"></div>"])));
	      this.DOM.typeInfoLink = main_core.Tag.render(_templateObject24 || (_templateObject24 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-link\"></div>"])));
	      this.DOM.typeInfoWrap = main_core.Tag.render(_templateObject25 || (_templateObject25 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.DOM.typeInfoTitle, this.DOM.typeInfoLink);
	      return this.DOM.typeInfoWrap;
	    }
	  }, {
	    key: "getRRuleInfoControl",
	    value: function getRRuleInfoControl() {
	      this.DOM.rruleInfo = main_core.Tag.render(_templateObject26 || (_templateObject26 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"])));
	      this.DOM.rruleInfoWrap = main_core.Tag.render(_templateObject27 || (_templateObject27 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('EC_REPEAT'), this.DOM.rruleInfo);
	      return this.DOM.rruleInfoWrap;
	    }
	  }, {
	    key: "getTimezoneInfoControl",
	    value: function getTimezoneInfoControl() {
	      this.DOM.timezoneInfo = main_core.Tag.render(_templateObject28 || (_templateObject28 = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"])));
	      this.DOM.timezoneInfoWrap = main_core.Tag.render(_templateObject29 || (_templateObject29 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Loc.getMessage('EC_TIMEZONE'), this.DOM.timezoneInfo);
	      return this.DOM.timezoneInfoWrap;
	    }
	  }, {
	    key: "isNewEntry",
	    value: function isNewEntry() {
	      return !this.entry.id;
	    }
	  }, {
	    key: "canDo",
	    value: function canDo(action) {
	      var section = this.getCurrentSection();

	      if (action === 'edit' || action === 'delete') {
	        if (this.entry.isMeeting() && this.entry.id !== this.entry.parentId) {
	          return false;
	        }

	        if (this.entry.isResourcebooking()) {
	          return false;
	        }

	        return section.canDo('edit');
	      }

	      if (action === 'view') {
	        return section.canDo('view_time');
	      }

	      if (action === 'viewFull') {
	        return section.canDo('view_full');
	      }

	      return true;
	    }
	  }, {
	    key: "setFormValues",
	    value: function setFormValues() {
	      var entry = this.entry,
	          section = this.getCurrentSection(),
	          readOnly = !this.canDo('edit'); // Date time

	      this.dateTimeControl.setValue({
	        from: calendar_util.Util.adjustDateForTimezoneOffset(entry.from, entry.userTimezoneOffsetFrom, entry.fullDay),
	        to: calendar_util.Util.adjustDateForTimezoneOffset(entry.to, entry.userTimezoneOffsetTo, entry.fullDay),
	        fullDay: entry.fullDay,
	        timezoneFrom: entry.getTimezoneFrom() || '',
	        timezoneTo: entry.getTimezoneTo() || '',
	        timezoneName: this.userSettings.timezoneName
	      });
	      this.dateTimeControl.setInlineEditMode(this.isNewEntry() ? 'edit' : 'view');
	      this.dateTimeControl.setViewMode(readOnly); // Title

	      this.DOM.titleInput.value = entry.getName();

	      if (readOnly) {
	        if (this.entry.getCurrentStatus() === false) {
	          this.DOM.titleInput.type = 'hidden'; // Hide input
	          // Add label instead

	          this.DOM.titleLabel = this.DOM.titleInput.parentNode.insertBefore(main_core.Tag.render(_templateObject30 || (_templateObject30 = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field calendar-field-string\">", "</span>"])), main_core.Text.encode(entry.getName())), this.DOM.titleInput);
	          main_core.Dom.addClass(this.DOM.titleOuterWrap, 'calendar-field-container-view');
	        } else {
	          this.DOM.titleInput.disabled = true;
	        }
	      } // Color


	      this.colorSelector.setValue(entry.getColor() || section.color, false);
	      this.colorSelector.setViewMode(readOnly && this.entry.getCurrentStatus() === false); // Section

	      this.sectionValue = this.getCurrentSectionId();
	      this.sectionSelector.updateValue();
	      this.sectionSelector.setViewMode(readOnly); // Reminders

	      this.remindersControl.setValue(entry.getReminders(), false);
	      this.remindersControl.setViewMode(readOnly && this.entry.getCurrentStatus() === false);

	      if (readOnly && this.entry.getCurrentStatus() === false) {
	        this.DOM.remindersOuterWrap.style.display = 'none';
	      } // Recurcion


	      if (entry.isRecursive()) {
	        this.DOM.rruleInfoWrap.style = '';
	        main_core.Dom.adjust(this.DOM.rruleInfo, {
	          text: entry.getRRuleDescription()
	        });
	      } // Timezone
	      // if (Type.isStringFilled(entry.getTimezoneFrom())
	      // 	&& entry.getTimezoneFrom() !== this.userSettings.timezoneName
	      // 	&& !this.isNewEntry())
	      // {
	      // 	this.DOM.timezoneInfoWrap.style = '';
	      // 	Dom.adjust(this.DOM.timezoneInfo, {text: entry.getTimezoneFrom()});
	      // }
	      // Location


	      var location = entry.getLocation();

	      if (readOnly && !location) {
	        this.DOM.locationOuterWrap.style.display = 'none';
	      } else {
	        this.locationSelector.setViewMode(readOnly);

	        if (this.isLocationCalendar) {
	          this.locationSelector.setValue(this.locationSelector["default"]);
	          location = this.locationSelector["default"];
	        } else {
	          this.DOM.locationOuterWrap.style.display = '';
	          this.locationSelector.setValue(entry.getLocation());
	        }
	      }

	      if (this.locationSelector) {
	        this.locationSelector.checkLocationAccessibility({
	          from: this.dateTimeControl.getValue().from,
	          to: this.dateTimeControl.getValue().to,
	          fullDay: this.dateTimeControl.getValue().fullDay,
	          currentEventId: this.entry.id
	        });
	      } //User Planner Selector


	      if (this.userPlannerSelector && (this.canDo('viewFull') || entry.getCurrentStatus() !== false)) {
	        this.userPlannerSelector.setValue({
	          attendeesEntityList: entry.getAttendeesEntityList(),
	          location: location,
	          attendees: entry.getAttendees(),
	          notify: !entry.isMeeting() || entry.getMeetingNotify(),
	          viewMode: this.getMode() === CompactEventForm.VIEW_MODE,
	          entry: entry,
	          hideGuests: entry.getHideGuests()
	        });
	        this.userPlannerSelector.setDateTime(this.dateTimeControl.getValue());
	        this.userPlannerSelector.setViewMode(readOnly);

	        if (this.isLocationCalendar) {
	          this.userPlannerSelector.showPlanner();
	        }
	      } else {
	        main_core.Dom.remove(this.DOM.userPlannerSelectorOuterWrap);
	      }

	      var hideInfoContainer = true;
	      this.DOM.infoContainer = this.DOM.wrap.querySelector('.calendar-field-container-info');

	      for (var i = 0; i <= this.DOM.infoContainer.childNodes.length; i++) {
	        if (main_core.Type.isElementNode(this.DOM.infoContainer.childNodes[i]) && this.DOM.infoContainer.childNodes[i].style.display !== 'none') {
	          hideInfoContainer = false;
	        }
	      }

	      if (hideInfoContainer) {
	        this.DOM.infoContainer.style.display = 'none';
	      }
	    }
	  }, {
	    key: "setFormValuesLocation",
	    value: function setFormValuesLocation() {
	      var entry = this.entry,
	          section = this.getCurrentSection(),
	          readOnly = true; // Date time

	      this.dateTimeControl.setValue({
	        from: calendar_util.Util.adjustDateForTimezoneOffset(entry.from, entry.userTimezoneOffsetFrom, entry.fullDay),
	        to: calendar_util.Util.adjustDateForTimezoneOffset(entry.to, entry.userTimezoneOffsetTo, entry.fullDay),
	        fullDay: entry.fullDay,
	        timezoneFrom: entry.getTimezoneFrom() || '',
	        timezoneTo: entry.getTimezoneTo() || '',
	        timezoneName: this.userSettings.timezoneName
	      });
	      this.dateTimeControl.setInlineEditMode(this.isNewEntry() ? 'edit' : 'view');
	      this.dateTimeControl.setViewMode(readOnly); // Title

	      if (this.entry.id !== this.entry.parentId) {
	        this.DOM.titleInput.value = section.name + ': ' + BX.util.htmlspecialchars(entry.getName());
	      } else {
	        this.DOM.titleInput.value = main_core.Loc.getMessage('CALENDAR_UPDATE');
	      } // Color


	      this.colorSelector.setValue(entry.getColor() || section.color, false);
	      this.colorSelector.setViewMode(!readOnly); // Section

	      this.sectionValue = this.getCurrentSectionId();
	      this.sectionSelector.updateValue();
	      this.sectionSelector.setViewMode(readOnly);
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this13 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (this.state === this.STATE.REQUEST) return false;
	      var entry = this.getCurrentEntry();
	      options = main_core.Type.isPlainObject(options) ? options : {};

	      if (this.isNewEntry() && this.userPlannerSelector.hasExternalEmailUsers() && calendar_util.Util.checkEmailLimitationPopup() && !options.emailLimitationDialogShown) {
	        calendar_entry.EntryManager.showEmailLimitationDialog({
	          callback: function callback(params) {
	            options.emailLimitationDialogShown = true;

	            _this13.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.userSettings.sendFromEmail && this.userPlannerSelector.hasExternalEmailUsers()) {
	        calendar_entry.EntryManager.showConfirmedEmailDialog({
	          callback: function callback(params) {
	            _this13.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.isNewEntry() && entry.isRecursive() && !options.confirmed && this.getFormDataChanges(['section', 'notify']).length > 0) {
	        calendar_entry.EntryManager.showConfirmEditDialog({
	          callback: function callback(params) {
	            options.recursionMode = params.recursionMode;
	            options.confirmed = true;

	            _this13.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.isNewEntry() && entry.isMeeting() && options.sendInvitesAgain === undefined && this.getFormDataChanges().includes('date&time') && entry.getAttendees().find(function (item) {
	        return item.STATUS === 'N';
	      })) {
	        calendar_entry.EntryManager.showReInviteUsersDialog({
	          callback: function callback(params) {
	            options.sendInvitesAgain = params.sendInvitesAgain;

	            _this13.save(options);
	          }
	        });
	        return false;
	      }

	      var dateTime = this.dateTimeControl.getValue();
	      var data = {
	        id: entry.id,
	        section: this.sectionValue,
	        name: this.DOM.titleInput.value,
	        desc: entry.getDescription(),
	        reminder: this.remindersControl.getSelectedValues(),
	        date_from: dateTime.fromDate,
	        date_to: dateTime.toDate,
	        skip_time: dateTime.fullDay ? 'Y' : 'N',
	        time_from: calendar_util.Util.formatTime(calendar_util.Util.adjustDateForTimezoneOffset(dateTime.from, -entry.userTimezoneOffsetFrom, dateTime.fullDay)),
	        time_to: calendar_util.Util.formatTime(calendar_util.Util.adjustDateForTimezoneOffset(dateTime.to, -entry.userTimezoneOffsetTo, dateTime.fullDay)),
	        location: this.locationSelector.getTextValue(),
	        tz_from: entry.getTimezoneFrom(),
	        tz_to: entry.getTimezoneTo(),
	        meeting_notify: this.userPlannerSelector.getInformValue() ? 'Y' : 'N',
	        meeting_host: entry.data.MEETING_HOST || '0',
	        chat_id: entry.data.MEETING ? entry.data.MEETING.CHAT_ID : 0,
	        exclude_users: this.excludeUsers || [],
	        attendeesEntityList: this.userPlannerSelector.getEntityList(),
	        sendInvitesAgain: options.sendInvitesAgain ? 'Y' : 'N',
	        hide_guests: this.userPlannerSelector.hideGuests ? 'Y' : 'N',
	        requestUid: BX.Calendar.Util.registerRequestId()
	      };
	      var checkCurrentUsersAccessibility = !entry.id || this.checkCurrentUsersAccessibility();

	      if (!checkCurrentUsersAccessibility && this.getFormDataChanges().includes('codes')) {
	        var previousAttendeesList = entry.getAttendeesEntityList();
	        var newAttendeesList = [];
	        data.attendeesEntityList.forEach(function (entity) {
	          if (!previousAttendeesList.find(function (item) {
	            return entity.entityId === item.entityId && parseInt(entity.id) === parseInt(item.id);
	          })) {
	            if (entity.entityId === 'user') {
	              newAttendeesList.push(entity.id);
	            } else {
	              checkCurrentUsersAccessibility = true;
	            }
	          }
	        });
	        data.newAttendeesList = newAttendeesList;
	      }

	      data.checkCurrentUsersAccessibility = checkCurrentUsersAccessibility ? 'Y' : 'N';

	      if (entry.id && entry.isRecursive()) {
	        data.EVENT_RRULE = entry.data.RRULE;
	      }

	      if (options.recursionMode) {
	        data.rec_edit_mode = options.recursionMode;
	        data.current_date_from = calendar_util.Util.formatDate(entry.from);
	      }

	      if (this.getCurrentSection().color.toLowerCase() !== this.colorSelector.getValue().toLowerCase()) {
	        data.color = this.colorSelector.getValue();
	      }

	      this.state = this.STATE.REQUEST;
	      this.freezePopup();
	      this.BX.ajax.runAction('calendar.api.calendarentryajax.editEntry', {
	        data: data,
	        analyticsLabel: {
	          calendarAction: this.isNewEntry() ? 'create_event' : 'edit_event',
	          formType: 'compact',
	          emailGuests: this.userPlannerSelector.hasExternalEmailUsers() ? 'Y' : 'N',
	          markView: calendar_util.Util.getCurrentView() || 'outside',
	          markCrm: 'N',
	          markRrule: 'NONE',
	          markMeeting: this.entry.isMeeting() ? 'Y' : 'N',
	          markType: this.type
	        }
	      }).then(function (response) {
	        if (_this13.isLocationCalendar) {
	          _this13.roomsManager.unsetHiddenRoom(calendar_controls.Location.parseStringValue(data.location).room_id);
	        }

	        _this13.unfreezePopup();

	        _this13.state = _this13.STATE.READY;

	        if (response.data.entryId) {
	          if (entry.id) {
	            calendar_entry.EntryManager.showEditEntryNotification(response.data.entryId);
	          } else {
	            calendar_entry.EntryManager.showNewEntryNotification(response.data.entryId);
	          }
	        }

	        _this13.emit('onSave', new main_core_events.BaseEvent({
	          data: {
	            responseData: response.data,
	            options: options
	          }
	        }));

	        _this13.close();

	        if (response.data.displayMobileBanner) {
	          new calendar_sync_interface.MobileSyncBanner().showInPopup();
	        }

	        if (response.data.countEventWithEmailGuestAmount) {
	          calendar_util.Util.setEventWithEmailGuestAmount(response.data.countEventWithEmailGuestAmount);
	        }

	        if (main_core.Type.isArray(response.data.eventList) && response.data.eventList.length && response.data.eventList[0].REMIND) {
	          calendar_entry.EntryManager.setNewEntryReminders(dateTime.fullDay ? 'fullDay' : 'withTime', response.data.eventList[0].REMIND);
	        }
	      }, function (response) {
	        _this13.unfreezePopup();

	        if (response.data && main_core.Type.isPlainObject(response.data.busyUsersList)) {
	          _this13.handleBusyUsersError(response.data.busyUsersList);

	          var errors = [];
	          response.errors.forEach(function (error) {
	            if (error.code !== "edit_entry_user_busy") {
	              errors.push(error);
	            }
	          });
	          response.errors = errors;
	        }

	        if (response.errors && response.errors.length) {
	          _this13.showError(response.errors);
	        }

	        _this13.state = _this13.STATE.ERROR;
	      });
	      return true;
	    }
	  }, {
	    key: "handleBusyUsersError",
	    value: function handleBusyUsersError(busyUsers) {
	      var _this14 = this;

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
	        _this14.excludeUsers = userIds.join(',');

	        _this14.save();
	      });
	      this.busyUsersDialog.show({
	        users: users
	      });
	    }
	  }, {
	    key: "handleKeyPress",
	    value: function handleKeyPress(e) {
	      var _this15 = this;

	      if (this.getMode() === CompactEventForm.EDIT_MODE && e.keyCode === calendar_util.Util.getKeyCode('enter') && this.checkTopSlider()) {
	        this.checkDataBeforeCloseMode = false;
	        this.save();
	      } else if (this.checkTopSlider() && e.keyCode === calendar_util.Util.getKeyCode('escape') && this.couldBeClosedByEsc()) {
	        this.close();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('delete') // || e.keyCode === Util.getKeyCode('backspace')
	      && !this.isNewEntry() && this.canDo('delete') && this.checkTopSlider()) {
	        var target = event.target || event.srcElement;
	        var tagName = main_core.Type.isElementNode(target) ? target.tagName.toLowerCase() : null;

	        if (tagName && !['input', 'textarea'].includes(tagName)) {
	          main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	            _this15.checkDataBeforeCloseMode = false;

	            _this15.close();
	          });
	          calendar_entry.EntryManager.deleteEntry(this.entry);
	        }
	      }
	    }
	  }, {
	    key: "getCurrentEntry",
	    value: function getCurrentEntry() {
	      return this.entry;
	    }
	  }, {
	    key: "getCurrentSection",
	    value: function getCurrentSection() {
	      var section = false;
	      var sectionId = this.getCurrentSectionId();

	      if (sectionId && this.sectionIndex[sectionId] !== undefined && this.sections[this.sectionIndex[sectionId]] !== undefined) {
	        section = this.sections[this.sectionIndex[sectionId]];
	      }

	      return section;
	    }
	  }, {
	    key: "getCurrentSectionId",
	    value: function getCurrentSectionId() {
	      var sectionId = 0;

	      if (this.sectionValue) {
	        sectionId = this.sectionValue;
	      } else {
	        var entry = this.getCurrentEntry();

	        if (entry instanceof calendar_entry.Entry) {
	          sectionId = parseInt(entry.sectionId);
	        } // TODO: refactor - don't take first section


	        if (!sectionId && this.sections[0]) {
	          sectionId = parseInt(this.sections[0].id);
	        }
	      }

	      return sectionId;
	    }
	  }, {
	    key: "handlePlannerSelectorChanges",
	    value: function handlePlannerSelectorChanges(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var dateTimeValue = this.dateTimeControl.getValue();
	        dateTimeValue.from = event.getData().dateFrom;
	        dateTimeValue.to = event.getData().dateTo; // Date time

	        this.dateTimeControl.setValue(dateTimeValue);
	        this.userPlannerSelector.setDateTime(this.dateTimeControl.getValue());

	        if (this.locationSelector) {
	          this.locationSelector.checkLocationAccessibility({
	            from: event.getData().dateFrom,
	            to: event.getData().dateTo,
	            fullDay: event.getData().fullDay,
	            currentEventId: this.entry.id
	          });
	        }
	      }
	    }
	  }, {
	    key: "editEntryInSlider",
	    value: function editEntryInSlider() {
	      this.checkDataBeforeCloseMode = false;
	      var dateTime = this.dateTimeControl.getValue();
	      BX.Calendar.EntryManager.openEditSlider({
	        entry: this.entry,
	        type: this.type,
	        isLocationCalendar: this.isLocationCalendar,
	        locationAccess: this.locationAccess,
	        dayOfWeekMonthFormat: this.dayOfWeekMonthFormat,
	        roomsManager: this.roomsManager,
	        locationCapacity: calendar_controls.Location.getCurrentCapacity(),
	        // for location component
	        ownerId: this.ownerId,
	        userId: this.userId,
	        formDataValue: {
	          section: this.sectionValue,
	          name: this.DOM.titleInput.value,
	          reminder: this.remindersControl.getSelectedRawValues(),
	          color: this.colorSelector.getValue(),
	          from: calendar_util.Util.adjustDateForTimezoneOffset(dateTime.from, -this.entry.userTimezoneOffsetFrom, dateTime.fullDay),
	          to: calendar_util.Util.adjustDateForTimezoneOffset(dateTime.to, -this.entry.userTimezoneOffsetTo, dateTime.fullDay),
	          fullDay: dateTime.fullDay,
	          location: this.locationSelector.getTextValue(),
	          meetingNotify: this.userPlannerSelector.getInformValue() ? 'Y' : 'N',
	          hideGuests: this.userPlannerSelector.hideGuests ? 'Y' : 'N',
	          attendeesEntityList: this.userPlannerSelector.getEntityList()
	        }
	      });
	      this.close();
	    }
	  }, {
	    key: "outsideMouseDownClose",
	    value: function outsideMouseDownClose(event) {
	      if (this.checkTopSlider()) {
	        var target = event.target || event.srcElement;
	        this.outsideMouseDown = !target.closest('div.popup-window');
	      }
	    }
	  }, {
	    key: "checkTopSlider",
	    value: function checkTopSlider() {
	      return !calendar_util.Util.getBX().SidePanel.Instance.getTopSlider();
	    }
	  }, {
	    key: "checkOutsideClickClose",
	    value: function checkOutsideClickClose(event) {
	      var target = event.target || event.srcElement;
	      this.outsideMouseUp = !target.closest('div.popup-window');

	      if (this.couldBeClosedByEsc() && this.outsideMouseDown && this.outsideMouseUp && (this.getMode() === CompactEventForm.VIEW_MODE || !this.formDataChanged() || this.isNewEntry())) {
	        setTimeout(this.close.bind(this), 0);
	      }
	    }
	  }, {
	    key: "couldBeClosedByEsc",
	    value: function couldBeClosedByEsc() {
	      var _this16 = this;

	      return !main_popup.PopupManager._popups.find(function (popup) {
	        return popup && popup.getId() !== _this16.popupId && popup.isShown();
	      });
	    }
	  }, {
	    key: "emitOnChange",
	    value: function emitOnChange() {
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          form: this,
	          entry: this.entry
	        }
	      }));
	    }
	  }, {
	    key: "showError",
	    value: function showError(errorList) {
	      var errorText = '';

	      if (main_core.Type.isArray(errorList)) {
	        errorList.forEach(function (error) {
	          errorText += error.message + "\n";
	        });
	      }

	      if (errorText !== '') {
	        alert(errorText);
	      }
	    }
	  }, {
	    key: "reloadEntryData",
	    value: function reloadEntryData() {
	      if (this.isShown() && !this.isNewEntry() && this.getMode() === CompactEventForm.VIEW_MODE) {
	        var calendar = calendar_util.Util.getCalendarContext();

	        if (calendar) {
	          this.entry = calendar_entry.EntryManager.getEntryInstance(calendar.getView().getEntryById(this.entry.getUniqueId()));
	          this.setFormValues();
	        }
	      }
	    }
	  }, {
	    key: "checkCurrentUsersAccessibility",
	    value: function checkCurrentUsersAccessibility() {
	      return this.getFormDataChanges().includes('date&time');
	    }
	  }, {
	    key: "handlePull",
	    value: function handlePull(params) {
	      var _this$userPlannerSele,
	          _this$userPlannerSele2,
	          _params$fields5,
	          _this17 = this;

	      if (this.userPlannerSelector && (_this$userPlannerSele = this.userPlannerSelector) !== null && _this$userPlannerSele !== void 0 && (_this$userPlannerSele2 = _this$userPlannerSele.planner) !== null && _this$userPlannerSele2 !== void 0 && _this$userPlannerSele2.isShown()) {
	        var _params$fields, _params$fields2, _params$fields3, _params$fields4;

	        var userIdList = main_core.Type.isArray(params === null || params === void 0 ? void 0 : (_params$fields = params.fields) === null || _params$fields === void 0 ? void 0 : _params$fields.ATTENDEES) ? params.fields.ATTENDEES : [];
	        var eventOwner = (params === null || params === void 0 ? void 0 : (_params$fields2 = params.fields) === null || _params$fields2 === void 0 ? void 0 : _params$fields2.CAL_TYPE) === 'user' ? parseInt(params === null || params === void 0 ? void 0 : (_params$fields3 = params.fields) === null || _params$fields3 === void 0 ? void 0 : _params$fields3.OWNER_ID) : parseInt(params === null || params === void 0 ? void 0 : (_params$fields4 = params.fields) === null || _params$fields4 === void 0 ? void 0 : _params$fields4.CREATED_BY);

	        if (!userIdList.includes(eventOwner)) {
	          userIdList.push(eventOwner);
	        }

	        this.userPlannerSelector.clearAccessibilityData(userIdList);
	        this.userPlannerSelector.refreshPlannerStateDebounce();
	      }

	      var entry = this.getCurrentEntry();

	      if (!this.isNewEntry() && entry && entry.parentId === parseInt(params === null || params === void 0 ? void 0 : (_params$fields5 = params.fields) === null || _params$fields5 === void 0 ? void 0 : _params$fields5.PARENT_ID)) {
	        var _params$fields6;

	        if (params.command === 'delete_event' && entry.getType() === (params === null || params === void 0 ? void 0 : (_params$fields6 = params.fields) === null || _params$fields6 === void 0 ? void 0 : _params$fields6.CAL_TYPE)) {
	          this.close();
	        } else {
	          var onEntryListReloadHandler = function onEntryListReloadHandler() {
	            _this17.reloadEntryDataDebounce();

	            BX.Event.EventEmitter.unsubscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
	          };

	          BX.Event.EventEmitter.subscribe('BX.Calendar:onEntryListReload', onEntryListReloadHandler);
	        }
	      }
	    }
	  }]);
	  return CompactEventForm;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(CompactEventForm, "VIEW_MODE", 'view');
	babelHelpers.defineProperty(CompactEventForm, "EDIT_MODE", 'edit');
	babelHelpers.defineProperty(CompactEventForm, "USER_URL", '/company/personal/user/#USER_ID#/');

	exports.CompactEventForm = CompactEventForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Event,BX.Calendar,BX.Main,BX.Calendar.Controls,BX.Calendar,BX.Calendar,BX.Calendar.Sync.Interface));
//# sourceMappingURL=compacteventform.bundle.js.map
