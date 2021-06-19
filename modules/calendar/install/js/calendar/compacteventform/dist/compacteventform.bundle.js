this.BX = this.BX || {};
(function (exports,main_core,main_core_events,calendar_util,main_popup,calendar_controls,calendar_entry,calendar_sectionmanager,calendar_sync_interface) {
	'use strict';

	function _templateObject23() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field calendar-field-string\">", "</span>"]);

	  _templateObject23 = function _templateObject23() {
	    return data;
	  };

	  return data;
	}

	function _templateObject22() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject22 = function _templateObject22() {
	    return data;
	  };

	  return data;
	}

	function _templateObject21() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"]);

	  _templateObject21 = function _templateObject21() {
	    return data;
	  };

	  return data;
	}

	function _templateObject20() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject20 = function _templateObject20() {
	    return data;
	  };

	  return data;
	}

	function _templateObject19() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"]);

	  _templateObject19 = function _templateObject19() {
	    return data;
	  };

	  return data;
	}

	function _templateObject18() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-block\" style=\"display: none\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject18 = function _templateObject18() {
	    return data;
	  };

	  return data;
	}

	function _templateObject17() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-link\"></div>"]);

	  _templateObject17 = function _templateObject17() {
	    return data;
	  };

	  return data;
	}

	function _templateObject16() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-title\"></div>"]);

	  _templateObject16 = function _templateObject16() {
	    return data;
	  };

	  return data;
	}

	function _templateObject15() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-text\"></div>"]);

	  _templateObject15 = function _templateObject15() {
	    return data;
	  };

	  return data;
	}

	function _templateObject14() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-block\">\n\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t", "\n\t\t</div>"]);

	  _templateObject14 = function _templateObject14() {
	    return data;
	  };

	  return data;
	}

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-place\"></div>"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-hide-members-container\" style=\"display: none;\">\n\t\t\t\t<div class=\"calendar-hide-members-container-inner\">\n\t\t\t\t\t<div class=\"calendar-hide-members-icon-hidden\"></div>\n\t\t\t\t\t<div class=\"calendar-hide-members-text\">", "</div>\n\t\t\t\t\t<span class=\"calendar-hide-members-helper\" data-hint=\"", "\"></span>\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-planner-wrapper\" style=\"height: 0\">\n\t\t\t\t</div>"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-container-inform\">\n\t\t\t\t\t<span class=\"calendar-field-container-inform-text\">", "</span>\n\t\t\t\t</div>"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t<div class=\"calendar-members-selected\">\n\t\t\t\t\t\t<span class=\"calendar-attendees-label\"></span>\n\t\t\t\t\t\t<span class=\"calendar-attendees-list\"></span>\n\t\t\t\t\t\t<span class=\"calendar-members-more\">", "</span>\n\t\t\t\t\t\t<span class=\"calendar-members-change-link\">", "</span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t<div class=\"calendar-field-container calendar-field-container-members\">\n\t\t\t\t", "\n\t\t\t\t<span class=\"calendar-videocall-wrap calendar-videocall-hidden\"></span>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t<div class=\"calendar-user-selector-wrap\"></div>\n\t\t\t<div class=\"calendar-add-popup-planner-wrap calendar-add-popup-show-planner\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t", "\n\t\t<div>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-container calendar-field-container-datetime\"></div>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-choice-calendar\"></div>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field calendar-field-select calendar-field-tiny\"></div>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input class=\"calendar-field calendar-field-string\" \n\t\t\t\tvalue=\"\" \n\t\t\t\tplaceholder=\"", "\" \n\t\t\t\ttype=\"text\" \n\t\t\t/>\n\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t<div class=\"calendar-field-title\">", ":</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"calendar-field-container calendar-field-container-string-select\">\n\t\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-add-popup-wrap\">\n\t\t\t", "\n\t\t\t<div class=\"calendar-field-container calendar-field-container-choice\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t\n\t\t\t", "\n\t\t\t\n\t\t\t", "\n\t\t\t\n\t\t\t<div class=\"calendar-field-container calendar-field-container-info\">\n\t\t\t\t", "\n\t\t\t\t\n\t\t\t\t\t", "\n\t\t\t\t\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
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

	    _this.setEventNamespace('BX.Calendar.CompactEventForm');

	    _this.userId = options.userId || calendar_util.Util.getCurrentUserId();
	    _this.type = options.type || 'user';
	    _this.ownerId = options.ownerId || _this.userId;
	    _this.BX = calendar_util.Util.getBX();
	    _this.checkForChanges = main_core.Runtime.debounce(_this.checkForChangesImmediately, 300, babelHelpers.assertThisInitialized(_this));
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
	        _this2.setFormValues();

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

	        if (!_this2.userPlannerSelector.isPlannerDisplayed() && _this2.getMode() === CompactEventForm.EDIT_MODE) {
	          _this2.userPlannerSelector.checkBusyTime();
	        }
	      });
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
	        content: this.getPopupContent(),
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
	      if (this.getMode() === CompactEventForm.EDIT_MODE && this.formDataChanged() && this.checkDataBeforeCloseMode && !confirm(BX.message('EC_SAVE_ENTRY_CONFIRM'))) {
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

	      calendar_util.Util.clearPlannerWatches();
	      calendar_util.Util.closeAllPopups();
	    }
	  }, {
	    key: "getPopupContent",
	    value: function getPopupContent() {
	      this.DOM.wrap = main_core.Tag.render(_templateObject(), this.DOM.titleOuterWrap = main_core.Tag.render(_templateObject2(), this.getTitleControl(), this.getColorControl()), this.getSectionControl(), this.getDateTimeControl(), this.getUserPlannerSelector(), this.getTypeInfoControl(), this.getLocationControl(), this.DOM.remindersOuterWrap = main_core.Tag.render(_templateObject3(), main_core.Loc.getMessage('EC_REMIND_LABEL'), this.createRemindersControl()), this.getRRuleInfoControl());
	      return this.DOM.wrap;
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      var _this3 = this;

	      var buttons = [];
	      var mode = this.getMode();

	      if (mode === CompactEventForm.EDIT_MODE) {
	        buttons.push(new BX.UI.Button({
	          name: 'save',
	          text: this.isNewEntry() ? main_core.Loc.getMessage('CALENDAR_EVENT_DO_ADD') : main_core.Loc.getMessage('CALENDAR_EVENT_DO_SAVE'),
	          className: "ui-btn ui-btn-primary",
	          events: {
	            click: function click() {
	              _this3.checkDataBeforeCloseMode = false;

	              _this3.save();
	            }
	          }
	        }));
	        buttons.push(new BX.UI.Button({
	          text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
	          className: "ui-btn ui-btn-link",
	          events: {
	            click: function click() {
	              if (_this3.isNewEntry()) {
	                _this3.checkDataBeforeCloseMode = false;

	                _this3.close();
	              } else {
	                _this3.setFormValues();

	                if (_this3.userPlannerSelector) {
	                  _this3.userPlannerSelector.destroy();
	                }

	                _this3.setMode(CompactEventForm.VIEW_MODE);

	                _this3.popup.setButtons(_this3.getButtons());
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
	                calendar_entry.EntryManager.setMeetingStatus(_this3.entry, 'Y').then(_this3.refreshMeetingStatus.bind(_this3));
	              }
	            }
	          }));
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-link",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_N'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this3.entry, 'N').then(function () {
	                  if (_this3.isShown()) {
	                    _this3.close();
	                  }
	                });
	              }
	            }
	          }));
	        }

	        buttons.push(new BX.UI.Button({
	          className: "ui-btn ".concat(this.entry.isMeeting() && this.entry.getCurrentStatus() === 'Q' ? 'ui-btn-link' : 'ui-btn-primary'),
	          text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_OPEN'),
	          events: {
	            click: function click() {
	              _this3.checkDataBeforeCloseMode = false;
	              BX.Calendar.EntryManager.openViewSlider(_this3.entry.id, {
	                entry: _this3.entry,
	                type: _this3.type,
	                ownerId: _this3.ownerId,
	                userId: _this3.userId,
	                from: _this3.entry.from,
	                timezoneOffset: _this3.entry && _this3.entry.data ? _this3.entry.data.TZ_OFFSET_FROM : null
	              });

	              _this3.close();
	            }
	          }
	        }));

	        if (this.entry.isMeeting() && this.entry.getCurrentStatus() === 'N') {
	          buttons.push(new BX.UI.Button({
	            className: "ui-btn ui-btn-link",
	            text: main_core.Loc.getMessage('EC_DESIDE_BUT_Y'),
	            events: {
	              click: function click() {
	                calendar_entry.EntryManager.setMeetingStatus(_this3.entry, 'Y').then(_this3.refreshMeetingStatus.bind(_this3));
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
	                calendar_entry.EntryManager.setMeetingStatus(_this3.entry, 'N').then(_this3.refreshMeetingStatus.bind(_this3));
	              }
	            }
	          }));
	        } // if (!this.isNewEntry() && this.canDo('edit'))
	        // {
	        // 	buttons.push(
	        // 		new BX.UI.Button({
	        // 			text : Loc.getMessage('CALENDAR_EVENT_DO_EDIT')
	        // 			//events : {click : this.save.bind(this)}
	        // 		})
	        // 	);
	        // }


	        if (!this.isNewEntry() && this.canDo('edit')) {
	          buttons.push(new BX.UI.Button({
	            text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_EDIT'),
	            className: "ui-btn ui-btn-link",
	            events: {
	              click: this.editEntryInSlider.bind(this)
	            }
	          })); //sideButton = true;
	        }

	        if (!this.isNewEntry() && this.canDo('delete')) {
	          if (!this.entry.isMeeting() || !this.entry.getCurrentStatus() || this.entry.getCurrentStatus() === 'H') {
	            buttons.push(new BX.UI.Button({
	              text: main_core.Loc.getMessage('CALENDAR_EVENT_DO_DELETE'),
	              className: "ui-btn ui-btn-link",
	              events: {
	                click: function click() {
	                  main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	                    _this3.checkDataBeforeCloseMode = false;

	                    _this3.close();
	                  });
	                  calendar_entry.EntryManager.deleteEntry(_this3.entry);

	                  if (!_this3.entry.wasEverRecursive()) {
	                    _this3.close();
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
	    key: "checkForChangesImmediately",
	    value: function checkForChangesImmediately() {
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
	    key: "updateSetMeetingButtons",
	    value: function updateSetMeetingButtons() {
	      var entry = this.getCurrentEntry();

	      if (entry.isMeeting()) ;
	    }
	  }, {
	    key: "getformDataChanges",
	    value: function getformDataChanges() {
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
	      return this.getformDataChanges().length > 0;
	    }
	  }, {
	    key: "setParams",
	    value: function setParams() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.userId = params.userId || calendar_util.Util.getCurrentUserId();
	      this.type = params.type || 'user';
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
	      this.iblockMeetingRoomList = params.iblockMeetingRoomList || [];
	      this.setSections(params.sections, params.trackingUserList);
	    }
	  }, {
	    key: "setSections",
	    value: function setSections(sections) {
	      var _this4 = this;

	      var trackingUsersList = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      this.sections = sections;
	      this.sectionIndex = {};
	      this.trackingUsersList = trackingUsersList || [];

	      if (main_core.Type.isArray(sections)) {
	        sections.forEach(function (value, ind) {
	          var id = parseInt(value.ID || value.id);

	          if (id > 0) {
	            _this4.sectionIndex[id] = ind;
	          }
	        }, this);
	      }
	    }
	  }, {
	    key: "prepareData",
	    value: function prepareData() {
	      var _this5 = this;
	      return new Promise(function (resolve) {
	        var section = _this5.getCurrentSection();

	        if (section && section.canDo) {
	          resolve();
	        } else {
	          _this5.BX.ajax.runAction('calendar.api.calendarajax.getCompactFormData', {
	            data: {
	              entryId: _this5.entry.id,
	              loadSectionId: _this5.entry.sectionId
	            }
	          }).then(function (response) {
	            if (response && response.data && response.data.section) {
	              // todo: refactor this part to new Section entities
	              _this5.sections.push(new window.BXEventCalendar.Section(calendar_util.Util.getCalendarContext(), response.data.section));

	              _this5.setSections(_this5.sections);

	              resolve();
	            }
	          });
	        }
	      });
	    }
	  }, {
	    key: "getTitleControl",
	    value: function getTitleControl() {
	      this.DOM.titleInput = main_core.Tag.render(_templateObject4(), main_core.Loc.getMessage('EC_ENTRY_NAME'));
	      main_core.Event.bind(this.DOM.titleInput, 'keyup', this.checkForChanges);
	      main_core.Event.bind(this.DOM.titleInput, 'change', this.checkForChanges);
	      return this.DOM.titleInput;
	    }
	  }, {
	    key: "getColorControl",
	    value: function getColorControl() {
	      var _this6 = this;

	      this.DOM.colorSelect = main_core.Tag.render(_templateObject5());
	      this.colorSelector = new calendar_controls.ColorSelector({
	        wrap: this.DOM.colorSelect,
	        mode: 'selector'
	      });
	      this.colorSelector.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var color = event.getData().value;

	          if (!_this6.isNewEntry() && (_this6.canDo('edit') || _this6.entry.getCurrentStatus() !== false)) {
	            _this6.BX.ajax.runAction('calendar.api.calendarajax.updateColor', {
	              data: {
	                entryId: _this6.entry.id,
	                userId: _this6.userId,
	                color: color
	              }
	            });

	            _this6.entry.data.COLOR = color;

	            _this6.emit('doRefresh');

	            _this6.emitOnChange();
	          }
	        }
	      });
	      return this.DOM.colorSelect;
	    }
	  }, {
	    key: "getSectionControl",
	    value: function getSectionControl() {
	      var _this7 = this;

	      this.DOM.sectionSelectWrap = main_core.Tag.render(_templateObject6());
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
	          var section = _this7.getCurrentSection();

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
	            if (_this7.colorSelector) {
	              _this7.colorSelector.setValue(sectionValue.color);
	            }

	            _this7.sectionValue = sectionValue.id;

	            _this7.checkForChanges();

	            calendar_sectionmanager.SectionManager.saveDefaultSectionId(_this7.sectionValue);
	          }
	        }
	      });
	      return this.DOM.sectionSelectWrap;
	    }
	  }, {
	    key: "getDateTimeControl",
	    value: function getDateTimeControl() {
	      var _this8 = this;

	      this.DOM.dateTimeWrap = main_core.Tag.render(_templateObject7());
	      this.dateTimeControl = new calendar_controls.DateTimeControl(null, {
	        showTimezone: false,
	        outerWrap: this.DOM.dateTimeWrap,
	        inlineEditMode: true
	      });
	      this.dateTimeControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          var value = event.getData().value;

	          if (_this8.remindersControl) {
	            _this8.remindersControl.setFullDayMode(value.fullDay);

	            if (_this8.isNewEntry() && !_this8.remindersControl.wasChangedByUser()) {
	              var defaultReminders = calendar_entry.EntryManager.getNewEntryReminders(value.fullDay ? 'fullDay' : 'withTime');

	              _this8.remindersControl.setValue(defaultReminders, false);
	            }
	          }

	          if (_this8.userPlannerSelector) {
	            if (!_this8.userPlannerSelector.isPlannerDisplayed()) {
	              _this8.userPlannerSelector.showPlanner();
	            }

	            _this8.userPlannerSelector.setLocationValue(_this8.locationSelector.getTextValue());

	            _this8.userPlannerSelector.setDateTime(value, true);

	            _this8.userPlannerSelector.refreshPlanner();
	          }

	          _this8.checkForChanges();
	        }
	      });
	      return this.DOM.dateTimeWrap;
	    }
	  }, {
	    key: "getUserPlannerSelector",
	    value: function getUserPlannerSelector() {
	      this.DOM.userPlannerSelectorOuterWrap = main_core.Tag.render(_templateObject8(), this.DOM.userSelectorWrap = main_core.Tag.render(_templateObject9(), main_core.Loc.getMessage('EC_ATTENDEES_MORE'), main_core.Loc.getMessage('EC_SEC_SLIDER_CHANGE')), this.DOM.informWrap = main_core.Tag.render(_templateObject10(), main_core.Loc.getMessage('EC_NOTIFY_OPTION')), this.DOM.plannerOuterWrap = main_core.Tag.render(_templateObject11()), this.DOM.hideGuestsWrap = main_core.Tag.render(_templateObject12(), main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES'), main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES_HINT')));
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
	        zIndex: this.zIndex + 10
	      });
	      this.userPlannerSelector.subscribe('onDateChange', this.handlePlannerSelectorChanges.bind(this));
	      this.userPlannerSelector.subscribe('onNotifyChange', this.checkForChanges); // this.subscribe('onLoad', this.userPlannerSelector.checkEmployment.bind(this.userPlannerSelector));

	      this.userPlannerSelector.subscribe('onUserCodesChange', this.checkForChanges);
	      return this.DOM.userPlannerSelectorOuterWrap;
	    }
	  }, {
	    key: "getLocationControl",
	    value: function getLocationControl() {
	      var _this9 = this;

	      this.DOM.locationWrap = main_core.Tag.render(_templateObject13());
	      this.DOM.locationOuterWrap = main_core.Tag.render(_templateObject14(), main_core.Loc.getMessage('EC_LOCATION_LABEL'), this.DOM.locationWrap);
	      this.locationSelector = new calendar_controls.Location({
	        wrap: this.DOM.locationWrap,
	        richLocationEnabled: this.locationFeatureEnabled,
	        locationList: this.locationList || [],
	        iblockMeetingRoomList: this.iblockMeetingRoomList || [],
	        inlineEditModeEnabled: true,
	        onChangeCallback: function onChangeCallback() {
	          if (_this9.userPlannerSelector) {
	            _this9.userPlannerSelector.setLocationValue(_this9.locationSelector.getTextValue());

	            if (_this9.locationSelector.getValue().type !== undefined && !_this9.userPlannerSelector.isPlannerDisplayed()) {
	              _this9.userPlannerSelector.showPlanner();
	            }

	            _this9.userPlannerSelector.refreshPlanner();
	          }

	          _this9.checkForChanges();
	        }
	      });
	      return this.DOM.locationOuterWrap;
	    }
	  }, {
	    key: "createRemindersControl",
	    value: function createRemindersControl() {
	      var _this10 = this;

	      this.reminderValues = [];
	      this.DOM.remindersWrap = main_core.Tag.render(_templateObject15());
	      this.remindersControl = new calendar_controls.Reminder({
	        wrap: this.DOM.remindersWrap,
	        zIndex: this.zIndex
	      });
	      this.remindersControl.subscribe('onChange', function (event) {
	        if (event instanceof main_core_events.BaseEvent) {
	          _this10.reminderValues = event.getData().values;

	          if (!_this10.isNewEntry() && (_this10.canDo('edit') || _this10.entry.getCurrentStatus() !== false)) {
	            _this10.BX.ajax.runAction('calendar.api.calendarajax.updateReminders', {
	              data: {
	                entryId: _this10.entry.id,
	                userId: _this10.userId,
	                reminders: _this10.reminderValues
	              }
	            }).then(function (response) {
	              _this10.entry.data.REMIND = response.data.REMIND;
	            });
	          }
	        }
	      });
	      return this.DOM.remindersWrap;
	    }
	  }, {
	    key: "getTypeInfoControl",
	    value: function getTypeInfoControl() {
	      this.DOM.typeInfoTitle = main_core.Tag.render(_templateObject16());
	      this.DOM.typeInfoLink = main_core.Tag.render(_templateObject17());
	      this.DOM.typeInfoWrap = main_core.Tag.render(_templateObject18(), this.DOM.typeInfoTitle, this.DOM.typeInfoLink);
	      return this.DOM.typeInfoWrap;
	    }
	  }, {
	    key: "getRRuleInfoControl",
	    value: function getRRuleInfoControl() {
	      this.DOM.rruleInfo = main_core.Tag.render(_templateObject19());
	      this.DOM.rruleInfoWrap = main_core.Tag.render(_templateObject20(), main_core.Loc.getMessage('EC_REPEAT'), this.DOM.rruleInfo);
	      return this.DOM.rruleInfoWrap;
	    }
	  }, {
	    key: "getTimezoneInfoControl",
	    value: function getTimezoneInfoControl() {
	      this.DOM.timezoneInfo = main_core.Tag.render(_templateObject21());
	      this.DOM.timezoneInfoWrap = main_core.Tag.render(_templateObject22(), main_core.Loc.getMessage('EC_TIMEZONE'), this.DOM.timezoneInfo);
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

	          this.DOM.titleLabel = this.DOM.titleInput.parentNode.insertBefore(main_core.Tag.render(_templateObject23(), main_core.Text.encode(entry.getName())), this.DOM.titleInput);
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
	        this.DOM.locationOuterWrap.style.display = '';
	        this.locationSelector.setValue(entry.getLocation());
	      }

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
	      } else {
	        main_core.Dom.remove(this.DOM.userPlannerSelectorOuterWrap);
	      }

	      this.updateSetMeetingButtons();
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
	    key: "save",
	    value: function save() {
	      var _this11 = this;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      if (this.state === this.STATE.REQUEST) return false;
	      var entry = this.getCurrentEntry();
	      options = main_core.Type.isPlainObject(options) ? options : {};

	      if (this.isNewEntry() && this.userPlannerSelector.hasExternalEmailUsers() && calendar_util.Util.checkEmailLimitationPopup() && !options.emailLimitationDialogShown) {
	        calendar_entry.EntryManager.showEmailLimitationDialog({
	          callback: function callback(params) {
	            options.emailLimitationDialogShown = true;

	            _this11.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.userSettings.sendFromEmail && this.userPlannerSelector.hasExternalEmailUsers()) {
	        calendar_entry.EntryManager.showConfirmedEmailDialog({
	          callback: function callback(params) {
	            _this11.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.isNewEntry() && entry.isRecursive() && !options.confirmed && this.getformDataChanges(['section', 'notify']).length > 0) {
	        calendar_entry.EntryManager.showConfirmEditDialog({
	          callback: function callback(params) {
	            options.recursionMode = params.recursionMode;
	            options.confirmed = true;

	            _this11.save(options);
	          }
	        });
	        return false;
	      }

	      if (!this.isNewEntry() && entry.isMeeting() && options.sendInvitesAgain === undefined && this.getformDataChanges().includes('date&time') && entry.getAttendees().find(function (item) {
	        return item.STATUS === 'N';
	      })) {
	        calendar_entry.EntryManager.showReInviteUsersDialog({
	          callback: function callback(params) {
	            options.sendInvitesAgain = params.sendInvitesAgain;

	            _this11.save(options);
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
	        exclude_users: this.excludeUsers || [],
	        attendeesEntityList: this.userPlannerSelector.getEntityList(),
	        sendInvitesAgain: options.sendInvitesAgain ? 'Y' : 'N',
	        hide_guests: this.userPlannerSelector.hideGuests ? 'Y' : 'N',
	        requestUid: BX.Calendar.Util.registerRequestId()
	      };

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
	      this.BX.ajax.runAction('calendar.api.calendarajax.editEntry', {
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
	        _this11.unfreezePopup();

	        _this11.state = _this11.STATE.READY;

	        if (response.data.entryId) {
	          if (entry.id) {
	            calendar_entry.EntryManager.showEditEntryNotification(response.data.entryId);
	          } else {
	            calendar_entry.EntryManager.showNewEntryNotification(response.data.entryId);
	          }
	        }

	        _this11.emit('onSave', new main_core_events.BaseEvent({
	          data: {
	            responseData: response.data,
	            options: options
	          }
	        }));

	        _this11.close();

	        if (response.data.displayMobileBanner) {
	          new calendar_sync_interface.MobileSyncBanner().showInPopup();
	        }

	        if (response.data.countEventWithEmailGuestAmount) {
	          calendar_util.Util.setEventWithEmailGuestAmount(response.data.countEventWithEmailGuestAmount);
	        }

	        if (main_core.Type.isArray(response.data.eventList) && response.data.eventList.length && response.data.eventList[0].REMIND && response.data.eventList[0].REMIND.length) {
	          calendar_entry.EntryManager.setNewEntryReminders(dateTime.fullDay ? 'fullDay' : 'withTime', response.data.eventList[0].REMIND);
	        }
	      }, function (response) {
	        _this11.unfreezePopup();

	        if (response.data && main_core.Type.isPlainObject(response.data.busyUsersList)) {
	          _this11.handleBusyUsersError(response.data.busyUsersList);

	          var errors = [];
	          response.errors.forEach(function (error) {
	            if (error.code !== "edit_entry_user_busy") {
	              errors.push(error);
	            }
	          });
	          response.errors = errors;
	        }

	        if (response.errors && response.errors.length) {
	          _this11.showError(response.errors);
	        }

	        _this11.state = _this11.STATE.ERROR;
	      });
	      return true;
	    }
	  }, {
	    key: "handleBusyUsersError",
	    value: function handleBusyUsersError(busyUsers) {
	      var _this12 = this;

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
	        _this12.excludeUsers = userIds.join(',');

	        _this12.save();
	      });
	      this.busyUsersDialog.show({
	        users: users
	      });
	    }
	  }, {
	    key: "handleKeyPress",
	    value: function handleKeyPress(e) {
	      var _this13 = this;

	      if (this.getMode() === CompactEventForm.EDIT_MODE && e.keyCode === calendar_util.Util.getKeyCode('enter')) {
	        this.save();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('escape') && this.couldBeClosedByEsc()) {
	        this.close();
	      } else if (e.keyCode === calendar_util.Util.getKeyCode('delete') // || e.keyCode === Util.getKeyCode('backspace')
	      && !this.isNewEntry() && this.canDo('delete')) {
	        var target = event.target || event.srcElement;
	        var tagName = main_core.Type.isElementNode(target) ? target.tagName.toLowerCase() : null;

	        if (tagName && !['input', 'textarea'].includes(tagName)) {
	          main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Entry:beforeDelete', function () {
	            _this13.checkDataBeforeCloseMode = false;

	            _this13.close();
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
	      var target = event.target || event.srcElement;
	      this.outsideMouseDown = !target.closest('div.popup-window');
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
	      var _this14 = this;

	      return !main_popup.PopupManager._popups.find(function (popup) {
	        return popup && popup.getId() !== _this14.popupId && popup.isShown();
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
	  }]);
	  return CompactEventForm;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(CompactEventForm, "VIEW_MODE", 'view');
	babelHelpers.defineProperty(CompactEventForm, "EDIT_MODE", 'edit');

	exports.CompactEventForm = CompactEventForm;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Event,BX.Calendar,BX.Main,BX.Calendar.Controls,BX.Calendar,BX.Calendar,BX.Calendar.Sync.Interface));
//# sourceMappingURL=compacteventform.bundle.js.map
