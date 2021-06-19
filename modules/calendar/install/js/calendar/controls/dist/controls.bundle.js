this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_icons_b24,calendar_entry,calendar_planner,ui_entitySelector,intranet_controlButton,main_core,main_core_events,main_popup,calendar_controls,calendar_util) {
	'use strict';

	var TimeSelector = /*#__PURE__*/function () {
	  function TimeSelector(params) {
	    babelHelpers.classCallCheck(this, TimeSelector);
	    babelHelpers.defineProperty(this, "Z_INDEX", 4000);
	    babelHelpers.defineProperty(this, "MIN_WIDTH", 102);
	    this.DOM = {
	      wrap: params.wrap,
	      input: params.input
	    };
	    this.onChangeCallback = main_core.Type.isFunction(params.onChangeCallback) ? params.onChangeCallback : null;
	    this.create();
	  }

	  babelHelpers.createClass(TimeSelector, [{
	    key: "create",
	    value: function create() {
	      var _this = this;

	      this.selectContol = new BX.Calendar.Controls.SelectInput({
	        input: this.DOM.input,
	        zIndex: this.Z_INDEX,
	        values: TimeSelector.getValueList(),
	        minWidth: this.MIN_WIDTH,
	        onChangeCallback: function onChangeCallback() {
	          if (_this.onChangeCallback) {
	            _this.onChangeCallback(_this.selectContol.getInputValue());
	          }
	        }
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var time;

	      if (main_core.Type.isDate(value)) {
	        time = {
	          h: value.getHours(),
	          m: value.getMinutes()
	        };
	      } else {
	        time = calendar_util.Util.parseTime(value);
	      }

	      var adaptedValue = TimeSelector.adaptTimeValue(time);
	      this.selectContol.setValue({
	        value: adaptedValue.value
	      });
	      var hour = Math.floor(adaptedValue.value / 60);
	      var min = adaptedValue.value - hour * 60;
	      this.DOM.input.value = calendar_util.Util.formatTime(hour, min);
	    }
	  }], [{
	    key: "adaptTimeValue",
	    value: function adaptTimeValue(timeValue) {
	      timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
	      var timeList = TimeSelector.getValueList(),
	          diff = 24 * 60,
	          ind = false,
	          i;

	      for (i = 0; i < timeList.length; i++) {
	        if (Math.abs(timeList[i].value - timeValue) < diff) {
	          diff = Math.abs(timeList[i].value - timeValue);
	          ind = i;

	          if (diff <= 15) {
	            break;
	          }
	        }
	      }

	      return timeList[ind || 0];
	    }
	  }, {
	    key: "getValueList",
	    value: function getValueList() {
	      if (!TimeSelector.valueList) {
	        TimeSelector.valueList = [];
	        var i;

	        for (i = 0; i < 24; i++) {
	          TimeSelector.valueList.push({
	            value: i * 60,
	            label: calendar_util.Util.formatTime(i, 0)
	          });
	          TimeSelector.valueList.push({
	            value: i * 60 + 30,
	            label: calendar_util.Util.formatTime(i, 30)
	          });
	        }
	      }

	      return TimeSelector.valueList;
	    }
	  }]);
	  return TimeSelector;
	}();
	babelHelpers.defineProperty(TimeSelector, "valueList", null);

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<input id=\"inp-", "\" type=\"text\" class=\"calendar-field calendar-field-datetime\" value=\"\" autocomplete=\"off\" placeholder=\"", "\"/>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-reminder-clear-icon\" data-bxc-value=\"", "\"/>"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"calendar-reminder-item\">\n\t\t\t\t\t\t<span class=\"calendar-reminder-item-title\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-reminder-clear-icon\" data-bxc-value=\"", "\"/>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-reminder-item\">\n\t\t\t\t\t<span class=\"calendar-reminder-item-title\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</span>"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-reminder-clear-icon\" data-bxc-value=\"", "\"/>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"calendar-reminder-item\">\n\t\t\t\t\t\t<span class=\"calendar-reminder-item-title\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"calendar-notification-add-btn-wrap\">\n\t\t\t\t\t<span class=\"calendar-notification-text\">", "</span>\n\t\t\t\t\t<span class=\"calendar-notification-btn-container calendar-notification-btn-add\">\n\t\t\t\t\t\t<span class=\"calendar-notification-icon\"></span>\n\t\t\t\t\t</span>\n\t\t\t\t</span>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Reminder = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Reminder, _EventEmitter);

	  // 9.00
	  function Reminder(params) {
	    var _this2;

	    babelHelpers.classCallCheck(this, Reminder);
	    _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Reminder).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "defaultReminderTime", 540);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "fullDayMode", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "extendedMode", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "selectedValues", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "controlList", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "viewMode", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "DOM", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this2), "changedByUser", false);

	    _this2.setEventNamespace('BX.Calendar.Controls.Reminder');

	    _this2.values = _this2.getValues();
	    _this2.id = params.id || 'reminder-' + Math.round(Math.random() * 1000000);
	    _this2.zIndex = params.zIndex || 3200;
	    _this2.rawValuesMap = new Map();
	    _this2.viewMode = params.viewMode === true;
	    _this2.changeCallack = params.changeCallack;
	    _this2.showPopupCallBack = params.showPopupCallBack;
	    _this2.hidePopupCallBack = params.hidePopupCallBack;

	    _this2.create(params);

	    _this2.setValue(params.selectedValues || []);

	    _this2.bindEventHandlers();

	    return _this2;
	  }

	  babelHelpers.createClass(Reminder, [{
	    key: "create",
	    value: function create(params) {
	      if (main_core.Type.isElementNode(params.wrap)) {
	        this.DOM.wrap = params.wrap;
	      }

	      if (!main_core.Type.isElementNode(this.DOM.wrap)) {
	        return;
	      }

	      main_core.Dom.addClass(this.DOM.wrap, 'calendar-notification-values');

	      if (!this.viewMode) {
	        this.DOM.addButton = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject(), main_core.Loc.getMessage('EC_REMIND1_ADD')));
	      }
	    }
	  }, {
	    key: "bindEventHandlers",
	    value: function bindEventHandlers() {
	      if (main_core.Type.isDomNode(this.DOM.addButton)) {
	        main_core.Event.bind(this.DOM.addButton, 'click', this.showPopup.bind(this));
	      }

	      if (main_core.Type.isDomNode(this.DOM.wrap)) {
	        main_core.Event.bind(this.DOM.wrap, 'click', this.handleClick.bind(this));
	      }
	    }
	  }, {
	    key: "getValues",
	    value: function getValues() {
	      var values = [];

	      if (!this.fullDayMode) {
	        values = values.concat([{
	          value: 0,
	          label: main_core.Loc.getMessage("EC_REMIND1_0"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_0")
	        }, {
	          value: 5,
	          label: main_core.Loc.getMessage("EC_REMIND1_5"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_5")
	        }, {
	          value: 10,
	          label: main_core.Loc.getMessage("EC_REMIND1_10"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_10")
	        }, {
	          value: 15,
	          label: main_core.Loc.getMessage("EC_REMIND1_15"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_15")
	        }, {
	          value: 20,
	          label: main_core.Loc.getMessage("EC_REMIND1_20"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_20")
	        }, {
	          value: 30,
	          label: main_core.Loc.getMessage("EC_REMIND1_30"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_30")
	        }, {
	          value: 60,
	          label: main_core.Loc.getMessage("EC_REMIND1_60"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_60")
	        }, {
	          value: 120,
	          label: main_core.Loc.getMessage("EC_REMIND1_120"),
	          shortLabel: main_core.Loc.getMessage("EC_REMIND1_SHORT_120")
	        } //{value: 1440, label: Loc.getMessage("EC_REMIND1_1440"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_1440")},
	        //{value: 2880, label: Loc.getMessage("EC_REMIND1_2880"), shortLabel: Loc.getMessage("EC_REMIND1_SHORT_2880")}
	        ]);
	      }

	      if (this.extendedMode) {
	        values = values.concat([{
	          id: 'time-menu-day-0',
	          label: main_core.Loc.getMessage("EC_REMIND1_DAY_0"),
	          dataset: {
	            mode: 'time-menu',
	            daysBefore: 0,
	            time: this.defaultReminderTime
	          }
	        }, {
	          id: 'time-menu-day-1',
	          label: main_core.Loc.getMessage("EC_REMIND1_DAY_1"),
	          dataset: {
	            mode: 'time-menu',
	            daysBefore: 1,
	            time: this.defaultReminderTime
	          }
	        }, {
	          id: 'time-menu-day-2',
	          label: main_core.Loc.getMessage("EC_REMIND1_DAY_2"),
	          dataset: {
	            mode: 'time-menu',
	            daysBefore: 2,
	            time: this.defaultReminderTime
	          }
	        }, {
	          id: 'custom',
	          label: main_core.Loc.getMessage("EC_REMIND1_CUSTOM"),
	          dataset: {
	            mode: 'custom'
	          }
	        }]);
	      }

	      return values;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(reminderList) {
	      var _this3 = this;

	      var emitChanges = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var changedByUser = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      this.selectedValues.forEach(function (value) {
	        _this3.removeValue(value, emitChanges);
	      });

	      if (main_core.Type.isArray(reminderList)) {
	        reminderList.forEach(function (value) {
	          _this3.addValue(value, emitChanges, changedByUser);
	        }, this);
	      }
	    }
	  }, {
	    key: "getSelectedValues",
	    value: function getSelectedValues() {
	      return this.selectedValues;
	    }
	  }, {
	    key: "getSelectedRawValues",
	    value: function getSelectedRawValues() {
	      var _this4 = this;

	      var selectedRawValues = [];
	      this.selectedValues.forEach(function (value) {
	        selectedRawValues.push(_this4.rawValuesMap.get(value) || value);
	      });
	      return selectedRawValues;
	    }
	  }, {
	    key: "setFullDayMode",
	    value: function setFullDayMode(fullDayMode) {
	      if (fullDayMode !== this.fullDayMode) {
	        this.fullDayMode = fullDayMode;
	        this.values = this.getValues();
	      }
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup() {
	      var _this5 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      var _this = this;

	      var menuItems = [];
	      this.values.forEach(function (item) {
	        if (item.mode === 'time-menu' || item.mode === 'custom' || !BX.util.in_array(item.value, _this5.selectedValues)) {
	          var menuItem = {};

	          if (item.dataset && item.dataset.mode === 'time-menu') {
	            menuItem.id = item.id;
	            var defaultReminderTime = calendar_util.Util.getTimeByInt(_this5.defaultReminderTime);
	            menuItem.text = item.label.replace('#TIME#', calendar_util.Util.formatTime(defaultReminderTime.hour, defaultReminderTime.min));
	            menuItem.dataset = BX.util.objectMerge({
	              type: 'submenu-list',
	              value: _this5.defaultReminderTime
	            }, item.dataset);
	            menuItem.items = _this5.getSubmenuTimeValues(menuItem, item.label, params);

	            menuItem.onclick = function () {
	              return function () {
	                _this.addValue({
	                  before: item.dataset.daysBefore,
	                  time: item.dataset.time
	                });

	                BX.defer(function () {
	                  _this.reminderMenu.close();
	                }, _this)();

	                if (main_core.Type.isFunction(params.addValueCallback)) {
	                  params.addValueCallback();
	                }
	              };
	            }();
	          } else if (item.dataset && item.dataset.mode === 'custom') {
	            menuItem.id = 'custom';
	            menuItem.text = item.label;
	            menuItem.items = [{
	              id: 'tmp',
	              text: 'tmp'
	            }];
	          } else {
	            menuItem.text = item.label;

	            menuItem.onclick = function (value, mode) {
	              return function () {
	                _this.addValue(value);

	                _this.reminderMenu.close();

	                if (main_core.Type.isFunction(params.addValueCallback)) {
	                  params.addValueCallback();
	                }
	              };
	            }(item.value, item.mode);
	          }

	          menuItems.push(menuItem);
	        }
	      }, this);
	      this.reminderMenu = main_popup.MenuManager.create(this.id, params.bindTarget || this.DOM.addButton, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 9,
	        angle: true,
	        cacheable: false
	      });
	      var adjustSubmenuPopup = this.adjustSubmenuPopup.bind(this);
	      var closeSubmenuPopup = this.closeSubmenuPopup.bind(this);
	      main_core_events.EventEmitter.subscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
	      main_core_events.EventEmitter.subscribe('BX.Main.Popup:onClose', closeSubmenuPopup);
	      this.reminderMenu.popupWindow.subscribe('onClose', function () {
	        main_core_events.EventEmitter.unsubscribe('BX.Main.Popup:onShow', adjustSubmenuPopup);
	        main_core_events.EventEmitter.unsubscribe('BX.Main.Popup:onClose', closeSubmenuPopup);
	      });
	      this.reminderMenu.show();
	    }
	  }, {
	    key: "getSubmenuTimeValues",
	    value: function getSubmenuTimeValues(parentItem, parentItemMessage, params) {
	      var menuItems = [];
	      Reminder.getTimeValueList(60).forEach(function (menuItem) {
	        menuItems.push({
	          id: 'time-' + menuItem.value,
	          dataset: {
	            value: menuItem.value,
	            daysBefore: parentItem.dataset.daysBefore
	          },
	          text: menuItem.label,
	          onclick: function (e, item) {
	            var time = calendar_util.Util.getTimeByInt(item.dataset.value);
	            var parentMenuItem = this.reminderMenu.getMenuItem(parentItem.id);

	            if (parentMenuItem) {
	              parentMenuItem.setText(parentItemMessage.replace('#TIME#', calendar_util.Util.formatTime(time.hour, time.min)));
	            }

	            this.addValue({
	              time: item.dataset.value,
	              before: item.dataset.daysBefore
	            });
	            BX.defer(function () {
	              this.reminderMenu.close();
	            }, this)();

	            if (main_core.Type.isFunction(params.addValueCallback)) {
	              params.addValueCallback();
	            }
	          }.bind(this)
	        });
	      }, this);
	      return menuItems;
	    }
	  }, {
	    key: "addValue",
	    value: function addValue(value) {
	      var emitChanges = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var changedByUser = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      var item;
	      var formattedValue = Reminder.formatValue(value);

	      if (main_core.Type.isPlainObject(value) && value.count) {
	        value = parseInt(formattedValue);
	      }

	      if (main_core.Type.isPlainObject(value) && !this.selectedValues.includes(formattedValue)) {
	        if (main_core.Type.isInteger(parseInt(value.before)) && main_core.Type.isInteger(parseInt(value.time))) {
	          item = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject2(), Reminder.getReminderLabel(value)));

	          if (!this.viewMode) {
	            item.appendChild(main_core.Tag.render(_templateObject3(), formattedValue));
	          }
	        } else if (value.type === 'date' && main_core.Type.isDate(value.value)) {
	          item = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject4(), calendar_util.Util.formatDateUsable(value.value) + ' ' + calendar_util.Util.formatTime(value.value)));

	          if (!this.viewMode) {
	            item.appendChild(main_core.Tag.render(_templateObject5(), formattedValue));
	          }
	        }

	        this.selectedValues.push(formattedValue);
	        this.controlList[formattedValue] = item;
	        this.rawValuesMap.set(formattedValue, value);
	      } else if (parseInt(value) >= 0 && !this.selectedValues.includes(formattedValue)) {
	        for (var i = 0; i < this.values.length; i++) {
	          if (this.values[i].value === parseInt(value)) {
	            item = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject6(), this.values[i].shortLabel || this.values[i].label));

	            if (!this.viewMode) {
	              item.appendChild(main_core.Tag.render(_templateObject7(), formattedValue));
	            }

	            this.selectedValues.push(formattedValue);
	            this.controlList[formattedValue] = item;
	            this.rawValuesMap.set(formattedValue, value);
	            break;
	          }
	        }

	        if (item === undefined) {
	          item = this.DOM.wrap.appendChild(main_core.Dom.create('SPAN', {
	            props: {
	              className: 'calendar-reminder-item'
	            },
	            text: Reminder.getText(value)
	          }));

	          if (!this.viewMode) {
	            item.appendChild(main_core.Dom.create('SPAN', {
	              props: {
	                className: 'calendar-reminder-clear-icon'
	              },
	              events: {
	                click: function () {
	                  this.removeValue(value);
	                }.bind(this)
	              }
	            }));
	          }

	          this.selectedValues.push(value);
	          this.controlList[value] = item;
	          this.rawValuesMap.set(value, value);
	        }
	      }

	      if (this.changeCallack) {
	        this.changeCallack(this.selectedValues);
	      }

	      if (emitChanges) {
	        this.emit('onChange', new main_core_events.BaseEvent({
	          data: {
	            values: this.selectedValues
	          }
	        }));
	      }

	      this.changedByUser = emitChanges && changedByUser;

	      if (main_core.Type.isElementNode(this.DOM.addButton)) {
	        this.DOM.wrap.appendChild(this.DOM.addButton);
	      }
	    }
	  }, {
	    key: "removeValue",
	    value: function removeValue(value) {
	      var emitChanges = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	      if (this.controlList[value] && main_core.Type.isDomNode(this.controlList[value])) {
	        main_core.Dom.remove(this.controlList[value]);
	      }

	      this.selectedValues = BX.util.deleteFromArray(this.selectedValues, BX.util.array_search(value, this.selectedValues));

	      if (this.changeCallack) {
	        this.changeCallack(this.selectedValues);
	      }

	      if (emitChanges) {
	        this.emit('onChange', new main_core_events.BaseEvent({
	          data: {
	            values: this.selectedValues
	          }
	        }));
	        this.changedByUser = true;
	      }
	    }
	  }, {
	    key: "handleClick",
	    value: function handleClick(e) {
	      var _this6 = this;

	      var target = e.target || e.srcElement;
	      var remValue = target.getAttribute('data-bxc-value');

	      if (!main_core.Type.isNull(remValue) && this.selectedValues.includes(remValue)) {
	        this.removeValue(remValue);
	      }

	      if (main_core.Dom.hasClass(target, 'calendar-reminder-item-title')) {
	        this.showPopup({
	          bindTarget: target,
	          addValueCallback: function addValueCallback() {
	            var removeIcon = target.parentNode.querySelector('.calendar-reminder-clear-icon');

	            if (main_core.Type.isElementNode(removeIcon) && !main_core.Type.isNull(removeIcon.getAttribute('data-bxc-value'))) {
	              _this6.removeValue(removeIcon.getAttribute('data-bxc-value'));
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "adjustSubmenuPopup",
	    // Used to scroll into view and highlight default item in time menu
	    value: function adjustSubmenuPopup(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var submenuPopup = event.getTarget();

	        if (submenuPopup instanceof main_popup.Popup) {
	          if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId())) {
	            this.adjustTimeSubmenuPopup(submenuPopup);
	          } else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId())) {
	            this.adjustCalendarSubmenuPopup(submenuPopup);
	          }
	        }
	      }
	    }
	  }, {
	    key: "closeSubmenuPopup",
	    value: function closeSubmenuPopup(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var submenuPopup = event.getTarget();

	        if (submenuPopup instanceof main_popup.Popup) {
	          if (/^menu-popup-popup-submenu-time-menu-day-\d$/.test(submenuPopup.getId())) ; else if (/^menu-popup-popup-submenu-custom$/.test(submenuPopup.getId())) {
	            var layout = submenuPopup.bindElement;
	            var textNode = layout.querySelector('.menu-popup-item-text');

	            if (main_core.Type.isDomNode(textNode)) {
	              main_core.Dom.clean(textNode);
	              textNode.innerHTML = main_core.Loc.getMessage("EC_REMIND1_CUSTOM");
	            }
	          }
	        }
	      }
	    }
	  }, {
	    key: "adjustTimeSubmenuPopup",
	    value: function adjustTimeSubmenuPopup(popup) {
	      var selectedMenuItem = popup.getContentContainer().querySelector('span[data-value="' + this.defaultReminderTime + '"]');

	      if (main_core.Type.isDomNode(selectedMenuItem)) {
	        setTimeout(function () {
	          popup.getContentContainer().scrollTop = parseInt(selectedMenuItem.offsetTop) - 10;
	          main_core.Dom.addClass(selectedMenuItem, 'menu-popup-item-open');
	        }, 50);
	      }
	    }
	  }, {
	    key: "adjustCalendarSubmenuPopup",
	    value: function adjustCalendarSubmenuPopup(popup) {
	      var _this7 = this;

	      var layout = popup.bindElement;
	      var textNode = layout.querySelector('.menu-popup-item-text');

	      if (main_core.Type.isDomNode(textNode)) {
	        main_core.Dom.clean(textNode);
	        var input = textNode.appendChild(main_core.Tag.render(_templateObject8(), Math.round(Math.random() * 100000), main_core.Loc.getMessage('EC_REMIND1_CUSTOM_PLACEHOLDER')));
	        var calendarControl = BX.calendar.get(); // Hacks for BX.calendar - it works as singleton and has troubles with using inside menupopups
	        // We trying to reinitialize it everytime

	        calendarControl.popup = null;
	        calendarControl._current_layer = null;
	        calendarControl._layers = {};
	        calendarControl.Show({
	          node: input,
	          value: calendar_util.Util.formatDateTime(calendar_util.Util.getUsableDateTime(new Date())),
	          field: input,
	          bTime: true,
	          bHideTime: false
	        });
	        var calendarPopup = calendarControl.popup;
	        calendarPopup.cacheable = false;

	        if (calendarPopup && calendarPopup.popupContainer) {
	          var calendarWrap = calendarPopup.popupContainer.querySelector('.bx-calendar');

	          if (main_core.Type.isDomNode(calendarWrap)) {
	            popup.contentContainer.appendChild(calendarWrap);
	          }

	          calendarPopup.close();
	          main_popup.MenuManager.destroy(calendarPopup.uniquePopupId);
	        }

	        main_core.Event.bind(input, 'change', function () {
	          var value = input.value,
	              dateValue = calendar_util.Util.parseDate(value);

	          if (main_core.Type.isDate(dateValue)) {
	            _this7.addValue({
	              type: 'date',
	              value: dateValue
	            });

	            _this7.reminderMenu.close();
	          }
	        });
	      }
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(viewMode) {
	      this.viewMode = viewMode;

	      if (this.viewMode) {
	        main_core.Dom.addClass(this.DOM.wrap, 'calendar-reminder-readonly');

	        if (main_core.Type.isElementNode(this.DOM.addButton)) {
	          this.DOM.addButton.style.display = 'none';
	        }
	      } else {
	        main_core.Dom.removeClass(this.DOM.wrap, 'calendar-reminder-readonly');
	      }
	    }
	  }, {
	    key: "wasChangedByUser",
	    value: function wasChangedByUser() {
	      return this.changedByUser;
	    }
	  }], [{
	    key: "getTimeValueList",
	    value: function getTimeValueList() {
	      var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 30;

	      if (!Reminder.timeValueList) {
	        Reminder.timeValueList = [];
	        var i;

	        for (i = 0; i < 24; i++) {
	          Reminder.timeValueList.push({
	            value: i * 60,
	            label: calendar_util.Util.formatTime(i, 0)
	          });

	          if (mode <= 30) {
	            Reminder.timeValueList.push({
	              value: i * 60 + 30,
	              label: calendar_util.Util.formatTime(i, 30)
	            });
	          }
	        }
	      }

	      return Reminder.timeValueList;
	    }
	  }, {
	    key: "inputCalendarClosePopupHandler",
	    value: function inputCalendarClosePopupHandler(e) {
	      BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	    }
	  }, {
	    key: "getReminderLabel",
	    value: function getReminderLabel(value) {
	      var label = '';
	      value.before = parseInt(value.before);
	      value.time = parseInt(value.time);

	      if (main_core.Type.isInteger(value.before) && main_core.Type.isInteger(value.time) && [0, 1, 2].includes(value.before)) {
	        var time = calendar_util.Util.getTimeByInt(value.time);
	        label = main_core.Loc.getMessage('EC_REMIND1_DAY_' + value.before + '_SHORT').replace('#TIME#', calendar_util.Util.formatTime(time.hour, time.min));
	      }

	      return label;
	    }
	  }, {
	    key: "getText",
	    value: function getText(value) {
	      var tempValue = value,
	          dividers = [60, 24],
	          //list of time dividers
	      messageCodes = ['EC_REMIND1_MIN_COUNT', 'EC_REMIND1_HOUR_COUNT', 'EC_REMIND1_DAY_COUNT'],
	          result = '';

	      for (var i = 0; i < messageCodes.length; i++) {
	        if (tempValue < dividers[i] || i === dividers.length) {
	          result = main_core.Loc.getMessage(messageCodes[i]).toString();
	          result = result.replace('\#COUNT\#', tempValue.toString());
	          break;
	        } else {
	          tempValue = Math.ceil(tempValue / dividers[i]);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "formatValue",
	    value: function formatValue(remindValue) {
	      if (main_core.Type.isPlainObject(remindValue) && main_core.Type.isInteger(parseInt(remindValue.before)) && main_core.Type.isInteger(parseInt(remindValue.time))) {
	        return 'daybefore|' + remindValue.before + '|' + remindValue.time;
	      } else if (main_core.Type.isPlainObject(remindValue) && main_core.Type.isDate(remindValue.value)) {
	        return 'date|' + calendar_util.Util.formatDateTime(remindValue.value);
	      } else if (main_core.Type.isPlainObject(remindValue) && remindValue.type) {
	        if (remindValue.type === 'min') {
	          return remindValue.count.toString();
	        }

	        if (remindValue.type === 'hour') {
	          return (parseInt(remindValue.count) * 60).toString();
	        }

	        if (remindValue.type === 'day') {
	          return (parseInt(remindValue.count) * 60 * 24).toString();
	        }
	      }

	      return remindValue.toString();
	    }
	  }]);
	  return Reminder;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Reminder, "timeValueList", null);

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"calendar-text-link\">", "</span>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-field-place-link\">", "\n\t\t\t\t</div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var Location = /*#__PURE__*/function () {
	  function Location(params) {
	    babelHelpers.classCallCheck(this, Location);
	    this.params = params;
	    this.id = params.id || 'location-' + Math.round(Math.random() * 1000000);
	    this.zIndex = params.zIndex || 3100;
	    this.DOM = {
	      wrapNode: params.wrap
	    };
	    this.disabled = !params.richLocationEnabled;
	    this.value = {
	      type: '',
	      text: '',
	      value: ''
	    };
	    this.inlineEditModeEnabled = params.inlineEditModeEnabled;
	    this.meetingRooms = params.iblockMeetingRoomList || [];
	    Location.setMeetingRoomList(params.iblockMeetingRoomList);
	    Location.setLocationList(params.locationList);
	    this.create();
	  }

	  babelHelpers.createClass(Location, [{
	    key: "create",
	    value: function create() {
	      this.DOM.inputWrap = this.DOM.wrapNode.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-field-block'
	        }
	      }));

	      if (this.inlineEditModeEnabled) {
	        this.DOM.inlineEditLinkWrap = this.DOM.wrapNode.appendChild(main_core.Tag.render(_templateObject$1(), this.DOM.inlineEditLink = main_core.Tag.render(_templateObject2$1(), main_core.Loc.getMessage('EC_REMIND1_ADD'))));
	        this.DOM.inputWrap.style.display = 'none';
	        main_core.Event.bind(this.DOM.inlineEditLinkWrap, 'click', this.displayInlineEditControls.bind(this));
	      }

	      if (this.disabled) {
	        BX.addClass(this.DOM.wrapNode, 'locked');
	        this.DOM.inputWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-lock-icon'
	          },
	          events: {
	            click: function click() {
	              B24.licenseInfoPopup.show('calendar_location', main_core.Loc.getMessage('EC_B24_LOCATION_LIMITATION_TITLE'), main_core.Loc.getMessage('EC_B24_LOCATION_LIMITATION'));
	            }
	          }
	        }));
	      }

	      this.DOM.input = this.DOM.inputWrap.appendChild(main_core.Dom.create('INPUT', {
	        attrs: {
	          name: this.params.inputName || '',
	          placeholder: main_core.Loc.getMessage('EC_LOCATION_LABEL'),
	          type: 'text',
	          autocomplete: this.disabled ? 'on' : 'off'
	        },
	        props: {
	          className: 'calendar-field calendar-field-select'
	        }
	      }));
	    }
	  }, {
	    key: "setValues",
	    value: function setValues() {
	      var menuItemList = [],
	          selectedIndex = false,
	          meetingRooms = Location.getMeetingRoomList(),
	          locationList = Location.getLocationList();

	      if (main_core.Type.isArray(meetingRooms)) {
	        meetingRooms.forEach(function (room) {
	          room.ID = parseInt(room.ID);
	          menuItemList.push({
	            ID: room.ID,
	            label: BX.util.htmlspecialchars(room.NAME),
	            labelRaw: room.NAME,
	            value: room.ID,
	            type: 'mr'
	          });

	          if (this.value.type === 'mr' && parseInt(this.value.value) === room.ID) {
	            selectedIndex = menuItemList.length - 1;
	          }
	        }, this);

	        if (menuItemList.length > 0) {
	          menuItemList.push({
	            delimiter: true
	          });
	        }
	      }

	      if (main_core.Type.isArray(locationList)) {
	        if (locationList.length) {
	          locationList.forEach(function (room) {
	            room.ID = parseInt(room.ID);
	            menuItemList.push({
	              ID: room.ID,
	              label: BX.util.htmlspecialchars(room.NAME),
	              labelRaw: room.NAME,
	              value: room.ID,
	              type: 'calendar'
	            });

	            if (this.value.type === 'calendar' && parseInt(this.value.value) === parseInt(room.ID)) {
	              selectedIndex = menuItemList.length - 1;
	            }
	          }, this);
	          menuItemList.push({
	            delimiter: true
	          });
	          menuItemList.push({
	            label: main_core.Loc.getMessage('EC_LOCATION_MEETING_ROOM_SET'),
	            callback: this.editMeetingRooms.bind(this)
	          });
	        } else {
	          menuItemList.push({
	            label: main_core.Loc.getMessage('EC_ADD_LOCATION'),
	            callback: this.editMeetingRooms.bind(this)
	          });
	        }
	      }

	      if (this.value) {
	        this.DOM.input.value = this.value.str || '';

	        if (this.value.type && this.value.str === this.getTextLocation(this.value)) {
	          this.DOM.input.value = main_core.Loc.getMessage('EC_LOCATION_404');
	        }
	      }

	      if (this.selectContol) {
	        this.selectContol.destroy();
	      }

	      this.selectContol = new BX.Calendar.Controls.SelectInput({
	        input: this.DOM.input,
	        values: menuItemList,
	        valueIndex: selectedIndex,
	        zIndex: this.zIndex,
	        disabled: this.disabled,
	        onChangeCallback: BX.delegate(function () {
	          var i,
	              value = this.DOM.input.value;
	          this.value = {
	            text: value
	          };

	          for (i = 0; i < menuItemList.length; i++) {
	            if (menuItemList[i].labelRaw === value) {
	              this.value.type = menuItemList[i].type;
	              this.value.value = menuItemList[i].value;
	              break;
	            }
	          }

	          if (main_core.Type.isFunction(this.params.onChangeCallback)) {
	            this.params.onChangeCallback();
	          }
	        }, this)
	      });
	    }
	  }, {
	    key: "editMeetingRooms",
	    value: function editMeetingRooms() {
	      var params = {};

	      if (this.params.getControlContentCallback) {
	        params.wrap = this.params.getControlContentCallback();
	      }

	      if (!params.wrap) {
	        params.wrap = this.showEditMeetingRooms();
	      }

	      this.buildLocationEditControl(params);
	    }
	  }, {
	    key: "showEditMeetingRooms",
	    value: function showEditMeetingRooms() {
	      if (this.editDialog) {
	        this.editDialog.destroy();
	      }

	      this.editDialogContent = main_core.Dom.create('DIV', {
	        props: {
	          className: 'bxec-location-wrap'
	        }
	      });
	      this.editDialog = new BX.PopupWindow(this.id + '_popup', null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_MEETING_ROOM_LIST_TITLE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'bxc-popup-window',
	        buttons: [new BX.PopupWindowButton({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_SAVE'),
	          events: {
	            click: BX.delegate(function () {
	              this.saveValues();

	              if (this.editDialog) {
	                this.editDialog.close();
	              }
	            }, this)
	          }
	        }), new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: BX.delegate(function () {
	              if (this.editDialog) {
	                this.editDialog.close();
	              }
	            }, this)
	          }
	        })],
	        content: this.editDialogContent,
	        events: {}
	      });
	      this.editDialog.show();
	      return this.editDialogContent;
	    }
	  }, {
	    key: "buildLocationEditControl",
	    value: function buildLocationEditControl(params) {
	      var i;
	      this.locationEditControlShown = true;
	      this.editDialogWrap = params.wrap; // Display meeting room list

	      this.locationRoomList = [];
	      this.addNewButtonField = false;

	      if (main_core.Type.isArray(Location.locationList)) {
	        Location.locationList.forEach(function (room) {
	          if (room.NAME !== '' && room.ID) {
	            this.locationRoomList.push({
	              id: parseInt(room.ID),
	              name: room.NAME
	            });
	          }
	        }, this);
	      }

	      if (!this.locationRoomList.length) {
	        this.locationRoomList.push({
	          id: 0,
	          name: ''
	        });
	      }

	      for (i = 0; i < this.locationRoomList.length; i++) {
	        this.addRoomField(this.locationRoomList[i], params.wrap);
	      } // Display add button


	      this.addNewButtonField = {
	        outerWrap: params.wrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-field-container calendar-field-container-container-text'
	          }
	        }))
	      };
	      this.addNewButtonField.innerWrap = this.addNewButtonField.outerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-field-block'
	        }
	      }));
	      this.addNewButtonField.innerCont = this.addNewButtonField.innerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-text'
	        },
	        html: '<span class="calendar-text-link">' + main_core.Loc.getMessage('EC_MEETING_ROOM_ADD') + '</span>',
	        events: {
	          click: BX.delegate(function () {
	            var lastItem = this.locationRoomList[this.locationRoomList.length - 1];

	            if (lastItem.id || lastItem.deleted || BX.util.trim(lastItem.field.input.value)) {
	              this.locationRoomList.push(this.addRoomField({
	                id: 0
	              }, params.wrap));
	            }
	          }, this)
	        }
	      }));
	      params.wrap.appendChild(this.addNewButtonField.outerWrap);
	    }
	  }, {
	    key: "addRoomField",
	    value: function addRoomField(room) {
	      room.field = {
	        outerWrap: this.editDialogWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-field-container calendar-field-container-string'
	          }
	        }))
	      };
	      room.field.innerWrap = room.field.outerWrap.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'calendar-field-block'
	        }
	      }));
	      room.field.innerWrap.style.paddingRight = '40px';
	      room.field.input = room.field.innerWrap.appendChild(main_core.Dom.create('INPUT', {
	        props: {
	          className: 'calendar-field calendar-field-string'
	        },
	        attrs: {
	          value: room.name || '',
	          placeholder: main_core.Loc.getMessage('EC_MEETING_ROOM_PLACEHOLDER'),
	          type: 'text'
	        },
	        events: {
	          keyup: BX.delegate(function (e) {
	            if (parseInt(e.keyCode) === 13) {
	              this.editRoom(room);
	            }
	          }, this)
	        }
	      }));
	      room.field.delRoomEntry = room.field.innerWrap.appendChild(main_core.Dom.create('SPAN', {
	        props: {
	          className: 'calendar-remove-filed'
	        },
	        events: {
	          click: BX.delegate(function () {
	            Location.deleteField(room);
	          }, this)
	        }
	      }));

	      if (this.addNewButtonField) {
	        this.editDialogWrap.appendChild(this.addNewButtonField.outerWrap);
	      }

	      if (!room.id) {
	        room.field.input.focus();
	      }

	      return room;
	    }
	  }, {
	    key: "editRoom",
	    value: function editRoom(room) {
	      if (!this.locationEditControlShown) return;
	      room.field.input.value = BX.util.trim(room.field.input.value);

	      if (!room.id) {
	        if (room.field.input.value && BX.util.trim(room.field.input.value) !== BX.util.trim(room.name)) {
	          room.name = room.field.input.value;
	          this.locationRoomList.push(this.addRoomField({
	            id: 0
	          }));
	        }
	      } else {
	        if (BX.util.trim(room.field.input.value) !== room.name) {
	          room.name = room.field.input.value;
	          room.changed = true;
	        }
	      }
	    }
	  }, {
	    key: "saveValues",
	    value: function saveValues() {
	      var _this = this;

	      var i,
	          locationList = [];

	      for (i = 0; i < this.locationRoomList.length; i++) {
	        if (this.locationRoomList[i].field && this.locationRoomList[i].field.input) {
	          if (this.locationRoomList[i].name !== this.locationRoomList[i].field.input.value && this.locationRoomList[i].id) {
	            this.locationRoomList[i].changed = true;
	          }

	          this.locationRoomList[i].name = this.locationRoomList[i].field.input.value;
	        }

	        if (!this.locationRoomList[i].deleted && this.locationRoomList[i].name || this.locationRoomList[i].id) {
	          locationList.push({
	            id: this.locationRoomList[i].id || 0,
	            name: this.locationRoomList[i].name || '',
	            changed: this.locationRoomList[i].changed || !this.locationRoomList[i].id ? 'Y' : 'N',
	            deleted: this.locationRoomList[i].deleted || !this.locationRoomList[i].name ? 'Y' : 'N'
	          });
	        }
	      }

	      BX.ajax.runAction('calendar.api.calendarajax.saveLocationList', {
	        data: {
	          locationList: locationList
	        }
	      }).then( // Success
	      function (response) {
	        Location.setLocationList(response.data.locationList);

	        _this.setValues();
	      }, // Failure
	      function (response) {//this.calendar.displayError(response.errors);
	      });
	      this.locationEditControlShown = false;
	    }
	  }, {
	    key: "getTextValue",
	    value: function getTextValue(value) {
	      if (!value) {
	        value = this.value;
	      }

	      var res = value.str || value.text || '';

	      if (value && value.type === 'mr') {
	        res = 'ECMR_' + value.value + (value.mrevid ? '_' + value.mrevid : '');
	      } else if (value && value.type === 'calendar') {
	        res = 'calendar_' + value.value + (value.room_event_id ? '_' + value.room_event_id : '');
	      }

	      return res;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.value;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isPlainObject(value)) {
	        this.value.text = value.text || '';
	        this.value.type = value.type || '';
	        this.value.value = value.value || '';
	      } else {
	        this.value = Location.parseStringValue(value);
	      }

	      this.setValues();

	      if (this.inlineEditModeEnabled) {
	        var textLocation = this.getTextLocation(this.value);
	        this.DOM.inlineEditLink.innerHTML = main_core.Text.encode(textLocation || main_core.Loc.getMessage('EC_REMIND1_ADD'));
	      }
	    } // parseLocation

	  }, {
	    key: "getTextLocation",
	    value: function getTextLocation(location) {
	      var value = main_core.Type.isPlainObject(location) ? location : Location.parseStringValue(location),
	          i,
	          str = value.str;

	      if (main_core.Type.isArray(this.meetingRooms) && value.type === 'mr') {
	        for (i = 0; i < this.meetingRooms.length; i++) {
	          if (parseInt(value.value) === parseInt(this.meetingRooms[i].ID)) {
	            str = this.meetingRooms[i].NAME;
	            break;
	          }
	        }
	      }

	      if (main_core.Type.isArray(Location.locationList) && value.type === 'calendar') {
	        for (i = 0; i < Location.locationList.length; i++) {
	          if (parseInt(value.value) === parseInt(Location.locationList[i].ID)) {
	            str = Location.locationList[i].NAME;
	            break;
	          }
	        }
	      }

	      return str;
	    }
	  }, {
	    key: "displayInlineEditControls",
	    value: function displayInlineEditControls() {
	      this.DOM.inlineEditLinkWrap.style.display = 'none';
	      this.DOM.inputWrap.style.display = '';
	    }
	  }], [{
	    key: "deleteField",
	    value: function deleteField(room) {
	      BX.remove(room.field.outerWrap, true);
	      room.deleted = true;
	      room.changed = true;
	    }
	  }, {
	    key: "parseStringValue",
	    value: function parseStringValue(str) {
	      if (!main_core.Type.isString(str)) {
	        str = '';
	      }

	      var res = {
	        type: false,
	        value: false,
	        str: str
	      };

	      if (str.substr(0, 5) === 'ECMR_') {
	        res.type = 'mr';
	        var value = str.split('_');

	        if (value.length >= 2) {
	          if (!isNaN(parseInt(value[1])) && parseInt(value[1]) > 0) {
	            res.value = res.mrid = parseInt(value[1]);
	          }

	          if (!isNaN(parseInt(value[2])) && parseInt(value[2]) > 0) {
	            res.mrevid = parseInt(value[2]);
	          }
	        }
	      } else if (str.substr(0, 9) === 'calendar_') {
	        res.type = 'calendar';

	        var _value = str.split('_');

	        if (_value.length >= 2) {
	          if (!isNaN(parseInt(_value[1])) && parseInt(_value[1]) > 0) {
	            res.value = res.room_id = parseInt(_value[1]);
	          }

	          if (!isNaN(parseInt(_value[2])) && parseInt(_value[2]) > 0) {
	            res.room_event_id = parseInt(_value[2]);
	          }
	        }
	      }

	      return res;
	    }
	  }, {
	    key: "setLocationList",
	    value: function setLocationList(locationList) {
	      if (main_core.Type.isArray(locationList)) {
	        Location.locationList = locationList;
	      }
	    }
	  }, {
	    key: "getLocationList",
	    value: function getLocationList() {
	      return Location.locationList;
	    }
	  }, {
	    key: "setMeetingRoomList",
	    value: function setMeetingRoomList(meetingRoomList) {
	      if (main_core.Type.isArray(meetingRoomList)) {
	        Location.meetingRoomList = meetingRoomList;
	      }
	    }
	  }, {
	    key: "getMeetingRoomList",
	    value: function getMeetingRoomList() {
	      return Location.meetingRoomList;
	    }
	  }]);
	  return Location;
	}();
	babelHelpers.defineProperty(Location, "locationList", []);
	babelHelpers.defineProperty(Location, "meetingRoomList", []);

	var UserSelector = /*#__PURE__*/function () {
	  function UserSelector() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, UserSelector);
	    this.params = params;
	    this.id = params.id || 'user_selector_' + Math.round(Math.random() * 1000000);
	    this.zIndex = params.zIndex || 3100;
	    this.DOM = {
	      wrapNode: params.wrapNode
	    };
	    this.destinationInputName = params.inputName || 'EVENT_DESTINATION';

	    if (main_core.Type.isArray(this.params.itemsSelected) && this.params.itemsSelected.length) {
	      this.params.itemsSelected = this.convertAttendeesCodes(this.params.itemsSelected);
	    }

	    this.create();
	  }

	  babelHelpers.createClass(UserSelector, [{
	    key: "create",
	    value: function create() {
	      var id = this.id;
	      this.DOM.socnetDestinationWrap = this.DOM.wrapNode.appendChild(main_core.Dom.create('DIV', {
	        props: {
	          className: 'event-grid-dest-wrap'
	        },
	        events: {
	          click: function click(e) {
	            BX.SocNetLogDestination.openDialog(id);
	          }
	        }
	      }));
	      this.socnetDestinationItems = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	        props: {
	          className: ''
	        },
	        events: {
	          click: function click(e) {
	            var targ = e.target || e.srcElement;

	            if (targ.className === 'feed-event-del-but') // Delete button
	              {
	                top.BX.SocNetLogDestination.deleteItem(targ.getAttribute('data-item-id'), targ.getAttribute('data-item-type'), id);
	                e.preventDefault();
	                e.stopPropagation();
	              }
	          },
	          mouseover: function mouseover(e) {
	            var targ = e.target || e.srcElement;
	            if (targ.className === 'feed-event-del-but') // Delete button
	              BX.addClass(targ.parentNode, 'event-grid-dest-hover');
	          },
	          mouseout: function mouseout(e) {
	            var targ = e.target || e.srcElement;
	            if (targ.className === 'feed-event-del-but') // Delete button
	              BX.removeClass(targ.parentNode, 'event-grid-dest-hover');
	          }
	        }
	      }));
	      this.socnetDestinationInputWrap = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	        props: {
	          className: 'feed-add-destination-input-box'
	        }
	      }));
	      this.socnetDestinationInput = this.socnetDestinationInputWrap.appendChild(main_core.Dom.create('INPUT', {
	        props: {
	          id: id + '-inp',
	          className: 'feed-add-destination-inp'
	        },
	        attrs: {
	          value: '',
	          type: 'text'
	        },
	        events: {
	          keydown: function keydown(e) {
	            return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
	              formName: id,
	              inputId: id + '-inp'
	            });
	          },
	          keyup: function keyup(e) {
	            return top.BX.SocNetLogDestination.searchHandler(e, {
	              formName: id,
	              inputId: id + '-inp',
	              linkId: 'event-grid-dest-add-link',
	              sendAjax: true
	            });
	          }
	        }
	      }));
	      this.socnetDestinationLink = this.DOM.socnetDestinationWrap.appendChild(main_core.Dom.create('SPAN', {
	        html: this.params.addLinkMessage || BX.message('EC_DESTINATION_ADD_USERS'),
	        props: {
	          id: id + '-link',
	          className: 'feed-add-destination-link'
	        },
	        events: {
	          keydown: function keydown(e) {
	            return top.BX.SocNetLogDestination.searchBeforeHandler(e, {
	              formName: id,
	              inputId: id + '-inp'
	            });
	          },
	          keyup: function keyup(e) {
	            return top.BX.SocNetLogDestination.searchHandler(e, {
	              formName: id,
	              inputId: id + '-inp',
	              linkId: 'event-grid-dest-add-link',
	              sendAjax: true
	            });
	          }
	        }
	      })); // if (this.params.itemsSelected && !this.checkItemsSelected(
	      // 	this.params.items,
	      // 	this.params.itemsLast,
	      // 	this.params.itemsSelected,
	      // 	BX.proxy(this.init, this)
	      // ))
	      // {
	      // 	return;
	      // }

	      this.init();
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      var _this = this;

	      if (!this.socnetDestinationInput || !this.DOM.socnetDestinationWrap || !this.params.items) {
	        return;
	      }

	      if (this.params.selectGroups === false) {
	        this.params.items.groups = {};
	        this.params.items.department = {};
	        this.params.items.sonetgroups = {};
	      }

	      if (this.params.selectUsers === false) {
	        this.params.items.users = {};
	        this.params.items.groups = {};
	        this.params.items.department = {};
	      }

	      BX.SocNetLogDestination.init({
	        name: this.id,
	        searchInput: this.socnetDestinationInput,
	        extranetUser: false,
	        userSearchArea: 'I',
	        bindMainPopup: {
	          node: this.DOM.socnetDestinationWrap,
	          offsetTop: '5px',
	          offsetLeft: '15px'
	        },
	        bindSearchPopup: {
	          node: this.DOM.socnetDestinationWrap,
	          offsetTop: '5px',
	          offsetLeft: '15px'
	        },
	        callback: {
	          select: this.selectCallback.bind(this),
	          unSelect: this.unSelectCallback.bind(this),
	          openDialog: this.openDialogCallback.bind(this),
	          closeDialog: this.closeDialogCallback.bind(this),
	          openSearch: this.openDialogCallback.bind(this),
	          closeSearch: function closeSearch() {
	            _this.closeDialogCallback(true);
	          }
	        },
	        items: this.params.items,
	        itemsLast: this.params.itemsLast,
	        itemsSelected: this.params.itemsSelected,
	        departmentSelectDisable: this.params.selectGroups === false
	      });
	    } // checkItemsSelected(items, itemsLast, selected, callback)
	    // {
	    // 	let codes = [], code;
	    // 	for (code in selected)
	    // 	{
	    // 		if (selected.hasOwnProperty(code))
	    // 		{
	    // 			if (selected[code] === 'users' && !items.users[code])
	    // 			{
	    // 				codes.push(code);
	    // 			}
	    // 		}
	    // 	}
	    //
	    // 	if (codes.length > 0)
	    // 	{
	    // 		let loader = this.DOM.socnetDestinationWrap.appendChild(BX.adjust(Util.getLoader(40), {style: {height: '50px'}}));
	    //
	    //
	    //
	    //
	    //
	    // 		this.calendar.request({
	    // 			type: 'get',
	    // 			data: {
	    // 				action: 'get_destination_items',
	    // 				codes: codes
	    // 			},
	    // 			handler: BX.delegate(function(response)
	    // 			{
	    // 				if (loader)
	    // 				{
	    // 					Dom.remove(loader);
	    // 				}
	    //
	    // 				this.params.items = this.calendar.util.getSocnetDestinationConfig('items');
	    // 				this.params.itemsLast = this.calendar.util.getSocnetDestinationConfig('itemsLast');
	    //
	    // 				if (Type.isFunction(callback))
	    // 				{
	    // 					callback();
	    // 				}
	    // 			}, this)
	    // 		});
	    // 		return false;
	    // 	}
	    //
	    // 	return true;
	    // }

	  }, {
	    key: "closeAll",
	    value: function closeAll() {
	      if (top.BX.SocNetLogDestination.isOpenDialog()) {
	        top.BX.SocNetLogDestination.closeDialog();
	      }

	      top.BX.SocNetLogDestination.closeSearch();
	    }
	  }, {
	    key: "selectCallback",
	    value: function selectCallback(item, type) {
	      var type1 = type,
	          prefix = 'S';

	      if (type === 'sonetgroups') {
	        prefix = 'SG';
	      } else if (type === 'groups') {
	        prefix = 'UA';
	        type1 = 'all-users';
	      } else if (type === 'users') {
	        prefix = 'U';
	      } else if (type === 'department') {
	        prefix = 'DR';
	      }

	      this.socnetDestinationItems.appendChild(main_core.Dom.create("span", {
	        attrs: {
	          'data-id': item.id
	        },
	        props: {
	          className: "event-grid-dest event-grid-dest-" + type1
	        },
	        children: [main_core.Dom.create("input", {
	          attrs: {
	            type: 'hidden',
	            name: this.destinationInputName + '[' + prefix + '][]',
	            value: item.id
	          }
	        }), main_core.Dom.create("span", {
	          props: {
	            className: "event-grid-dest-text"
	          },
	          html: item.name
	        }), main_core.Dom.create("span", {
	          props: {
	            className: "feed-event-del-but"
	          },
	          attrs: {
	            'data-item-id': item.id,
	            'data-item-type': type
	          }
	        })]
	      }));
	      BX.onCustomEvent('OnDestinationAddNewItem', [item]);
	      this.socnetDestinationInput.value = '';
	      this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	    }
	  }, {
	    key: "unSelectCallback",
	    value: function unSelectCallback(item, type, search) {
	      var elements = BX.findChildren(this.socnetDestinationItems, {
	        attribute: {
	          'data-id': item.id
	        }
	      }, true);

	      if (elements != null) {
	        for (var j = 0; j < elements.length; j++) {
	          BX.remove(elements[j]);
	        }
	      }

	      BX.onCustomEvent('OnDestinationUnselect');
	      this.socnetDestinationInput.value = '';
	      this.socnetDestinationLink.innerHTML = this.params.addLinkMessage || (top.BX.SocNetLogDestination.getSelectedCount(this.id) > 0 ? BX.message('EC_DESTINATION_ADD_MORE') : BX.message('EC_DESTINATION_ADD_USERS'));
	    }
	  }, {
	    key: "openDialogCallback",
	    value: function openDialogCallback() {
	      BX.style(this.socnetDestinationInputWrap, 'display', 'inline-block');
	      BX.style(this.socnetDestinationLink, 'display', 'none');
	      BX.focus(this.socnetDestinationInput);
	    }
	  }, {
	    key: "closeDialogCallback",
	    value: function closeDialogCallback(cleanInputValue) {
	      if (!top.BX.SocNetLogDestination.isOpenSearch() && this.socnetDestinationInput.value.length <= 0) {
	        BX.style(this.socnetDestinationInputWrap, 'display', 'none');
	        BX.style(this.socnetDestinationLink, 'display', 'inline-block');
	        if (cleanInputValue === true) this.socnetDestinationInput.value = ''; // Disable backspace

	        if (top.BX.SocNetLogDestination.backspaceDisable || top.BX.SocNetLogDestination.backspaceDisable != null) BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);
	        BX.bind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable = function (e) {
	          if (e.keyCode === 8) {
	            e.preventDefault();
	            return false;
	          }
	        });
	        setTimeout(function () {
	          BX.unbind(window, 'keydown', top.BX.SocNetLogDestination.backspaceDisable);
	          top.BX.SocNetLogDestination.backspaceDisable = null;
	        }, 5000);
	      }
	    }
	  }, {
	    key: "getCodes",
	    value: function getCodes() {
	      var inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
	          codes = [],
	          i;

	      for (i = 0; i < inputsList.length; i++) {
	        codes.push(inputsList[i].value);
	      }

	      return codes;
	    }
	  }, {
	    key: "getAttendeesCodes",
	    value: function getAttendeesCodes() {
	      var inputsList = this.socnetDestinationItems.getElementsByTagName('INPUT'),
	          values = [],
	          i;

	      for (i = 0; i < inputsList.length; i++) {
	        values.push(inputsList[i].value);
	      }

	      return this.convertAttendeesCodes(values);
	    }
	  }, {
	    key: "convertAttendeesCodes",
	    value: function convertAttendeesCodes(values) {
	      var attendeesCodes = {};

	      if (main_core.Type.isArray(values)) {
	        values.forEach(function (code) {
	          if (code.substr(0, 2) === 'DR') {
	            attendeesCodes[code] = "department";
	          } else if (code.substr(0, 2) === 'UA') {
	            attendeesCodes[code] = "groups";
	          } else if (code.substr(0, 2) === 'SG') {
	            attendeesCodes[code] = "sonetgroups";
	          } else if (code.substr(0, 1) === 'U') {
	            attendeesCodes[code] = "users";
	          }
	        });
	      }

	      return attendeesCodes;
	    }
	  }, {
	    key: "getAttendeesCodesList",
	    value: function getAttendeesCodesList(codes) {
	      var result = [];
	      if (!codes) codes = this.getAttendeesCodes();

	      for (var i in codes) {
	        if (codes.hasOwnProperty(i)) {
	          result.push(i);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (this.socnetDestinationItems) {
	        main_core.Dom.clean(this.socnetDestinationItems);
	      }

	      if (main_core.Type.isArray(value)) {
	        this.params.itemsSelected = this.convertAttendeesCodes(value);
	      }

	      this.init();
	    }
	  }]);
	  return UserSelector;
	}();

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div style=\"background-color: #000;\" class=\"calendar-field-select-icon\"></div>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ColorSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ColorSelector, _EventEmitter);

	  function ColorSelector(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "LINE_MODE", 'line');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SELECTOR_MODE", 'selector');

	    _this.setEventNamespace('BX.Calendar.Controls.ColorSelector');

	    _this.id = params.id || 'color-select-' + Math.round(Math.random() * 1000000);
	    _this.defaultColors = calendar_util.Util.getDefaultColorList();
	    _this.colors = [];
	    _this.zIndex = 3100;
	    _this.mode = params.mode || _this.LINE_MODE;
	    _this.DOM = {
	      wrap: params.wrap
	    };

	    _this.create();

	    _this.setViewMode(params.viewMode || false);

	    return _this;
	  }

	  babelHelpers.createClass(ColorSelector, [{
	    key: "create",
	    value: function create() {
	      var _this2 = this;

	      if (this.mode === this.LINE_MODE) {
	        for (var i = 0; i < this.defaultColors.length; i++) {
	          this.colors.push({
	            color: this.defaultColors[i],
	            node: this.DOM.wrap.appendChild(main_core.Dom.create('LI', {
	              props: {
	                className: 'calendar-field-colorpicker-color-item'
	              },
	              attrs: {
	                'data-bx-calendar-color': this.defaultColors[i]
	              },
	              style: {
	                backgroundColor: this.defaultColors[i]
	              },
	              html: '<span class="calendar-field-colorpicker-color"></span>'
	            }))
	          });
	        }

	        this.DOM.customColorNode = this.DOM.wrap.appendChild(main_core.Dom.create('LI', {
	          props: {
	            className: 'calendar-field-colorpicker-color-item'
	          },
	          style: {
	            backgroundColor: 'transparent',
	            width: 0
	          },
	          html: '<span class="calendar-field-colorpicker-color"></span>'
	        }));
	        this.DOM.customColorLink = this.DOM.wrap.appendChild(main_core.Dom.create('LI', {
	          props: {
	            className: 'calendar-field-colorpicker-color-item-more'
	          },
	          html: '<span class="calendar-field-colorpicker-color-item-more-link">' + main_core.Loc.getMessage('EC_COLOR') + '</span>',
	          events: {
	            click: function click() {
	              if (!_this2.colorPickerPopup) {
	                _this2.colorPickerPopup = new BX.ColorPicker({
	                  bindElement: _this2.DOM.customColorLink,
	                  onColorSelected: _this2.setValue.bind(_this2),
	                  popupOptions: {
	                    zIndex: _this2.zIndex
	                  }
	                });
	              }

	              _this2.colorPickerPopup.open();
	            }
	          }
	        }));
	        main_core.Event.bind(this.DOM.wrap, 'click', this.handleColorClick.bind(this));
	      } else if (this.mode === this.SELECTOR_MODE) {
	        this.DOM.colorIcon = this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject$2()));
	        main_core.Event.bind(this.DOM.wrap, 'click', this.openPopup.bind(this));
	      }
	    }
	  }, {
	    key: "handleColorClick",
	    value: function handleColorClick(e) {
	      if (this.viewMode) {
	        return;
	      }

	      var target = calendar_util.Util.findTargetNode(e.target || e.srcElement, this.DOM.wrap);

	      if (target && target.getAttribute) {
	        var value = target.getAttribute('data-bx-calendar-color');

	        if (value !== null) {
	          this.setValue(value);
	        }
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(color) {
	      var emitChanges = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

	      if (this.viewMode) {
	        return;
	      }

	      this.activeColor = color;

	      if (this.mode === this.LINE_MODE) {
	        if (this.DOM.activeColorNode) {
	          BX.removeClass(this.DOM.activeColorNode, 'active');
	        }

	        if (!BX.util.in_array(this.activeColor, this.defaultColors) && this.activeColor) {
	          this.DOM.customColorNode.style.backgroundColor = this.activeColor;
	          this.DOM.customColorNode.style.width = '';
	          this.DOM.activeColorNode = this.DOM.customColorNode;
	          BX.addClass(this.DOM.activeColorNode, 'active');
	        }

	        var i;

	        for (i = 0; i < this.colors.length; i++) {
	          if (this.colors[i].color === this.activeColor) {
	            this.DOM.activeColorNode = this.colors[i].node;
	            BX.addClass(this.DOM.activeColorNode, 'active');
	            break;
	          }
	        }
	      } else if (this.mode === this.SELECTOR_MODE) {
	        if (this.DOM.colorIcon) {
	          this.DOM.colorIcon.style.backgroundColor = this.activeColor;
	        }

	        if (this.viewMode) {
	          this.DOM.wrap.style.backgroundColor = this.activeColor;
	        }
	      }

	      if (emitChanges) {
	        this.emit('onChange', new main_core_events.BaseEvent({
	          data: {
	            value: this.activeColor
	          }
	        }));
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.activeColor;
	    }
	  }, {
	    key: "openPopup",
	    value: function openPopup() {
	      var _this3 = this;

	      if (this.viewMode) {
	        return;
	      }

	      if (this.popup && this.popup.popupWindow && this.popup.popupWindow.isShown()) {
	        return this.popup.close();
	      }

	      var i,
	          menuItems = [],
	          icon;
	      this.defaultColors.forEach(function (color) {
	        menuItems.push({
	          text: color,
	          color: color,
	          className: 'calendar-add-popup-color-menu-item',
	          onclick: function (color) {
	            return function () {
	              _this3.setValue(color);

	              _this3.popup.close();
	            };
	          }(color)
	        });
	      });
	      this.popup = main_popup.MenuManager.create(this.id, this.DOM.colorIcon, menuItems, {
	        className: 'calendar-color-popup-wrap',
	        width: 162,
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 52,
	        angle: true,
	        cacheable: false
	      });
	      this.popup.show(); // Paint round icons for section menu

	      for (i = 0; i < this.popup.menuItems.length; i++) {
	        if (this.popup.menuItems[i].layout.item) {
	          icon = this.popup.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');

	          if (main_core.Type.isDomNode(icon)) {
	            icon.style.backgroundColor = this.popup.menuItems[i].color;
	          }
	        }
	      }

	      this.popup.popupWindow.angle.element.style.left = '6px';
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(viewMode) {
	      this.viewMode = viewMode;

	      if (this.viewMode) {
	        main_core.Dom.clean(this.DOM.wrap);
	        this.DOM.wrap.className = 'calendar-field-select-icon';
	        this.DOM.wrap.style.backgroundColor = this.activeColor;
	      }
	    }
	  }]);
	  return ColorSelector;
	}(main_core_events.EventEmitter);

	function _templateObject6$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-icon ui-icon-common-bitrix24\"><i></i></div>"]);

	  _templateObject6$1 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-icon ui-icon-common-user\"><i></i></div>"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-icon ui-icon-common-user-group\"><i></i></div>"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<img class=\"calendar-field-choice-calendar-img-value\" src=\"", "\">"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-choice-calendar-name\">", "</span>"]);

	  _templateObject2$2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-choice-calendar-img\"></span>"]);

	  _templateObject$3 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var SectionSelector = /*#__PURE__*/function () {
	  function SectionSelector(params) {
	    babelHelpers.classCallCheck(this, SectionSelector);
	    this.id = params.id || 'section-select-' + Math.round(Math.random() * 1000000);
	    this.sectionList = params.sectionList;
	    this.sectionGroupList = params.sectionGroupList;
	    this.selectCallback = params.selectCallback;
	    this.openPopupCallback = params.openPopupCallback;
	    this.closePopupCallback = params.closePopupCallback;
	    this.getCurrentSection = params.getCurrentSection;
	    this.defaultCalendarType = params.defaultCalendarType;
	    this.defaultOwnerId = parseInt(params.defaultOwnerId) || 0;
	    this.zIndex = params.zIndex || 3200;
	    this.mode = params.mode; // full|compact|textselect

	    this.DOM = {
	      outerWrap: params.outerWrap
	    };
	    this.create();
	    this.initEventHandlers();
	  }

	  babelHelpers.createClass(SectionSelector, [{
	    key: "create",
	    value: function create() {
	      if (this.mode === 'textselect') {
	        this.DOM.select = this.DOM.outerWrap;
	        this.DOM.selectImageWrap = this.DOM.select.appendChild(main_core.Tag.render(_templateObject$3()));
	        this.DOM.selectInnerText = this.DOM.select.appendChild(main_core.Tag.render(_templateObject2$2(), main_core.Text.encode(main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle())));
	      } else {
	        this.DOM.select = this.DOM.outerWrap.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-field calendar-field-select' + (this.mode === 'compact' ? ' calendar-field-tiny' : '')
	          }
	        }));
	        this.DOM.innerValue = this.DOM.select.appendChild(main_core.Dom.create('DIV', {
	          props: {
	            className: 'calendar-field-select-icon'
	          },
	          style: {
	            backgroundColor: this.getCurrentColor()
	          }
	        }));

	        if (this.mode === 'full') {
	          this.DOM.selectInnerText = this.DOM.select.appendChild(main_core.Dom.create('SPAN', {
	            text: this.getCurrentTitle()
	          }));
	        }
	      }
	    }
	  }, {
	    key: "initEventHandlers",
	    value: function initEventHandlers() {
	      main_core.Event.bind(this.DOM.select, 'click', BX.delegate(this.openPopup, this));
	    }
	  }, {
	    key: "openPopup",
	    value: function openPopup() {
	      var _this2 = this;

	      if (this.viewMode) {
	        return false;
	      }

	      if (this.sectionMenu && this.sectionMenu.popupWindow && this.sectionMenu.popupWindow.isShown()) {
	        return this.sectionMenu.close();
	      }

	      var submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';
	      var menuItems = [];
	      var sectionIdList = [];
	      var sectionList = this.getSectionList();
	      var i;

	      if (main_core.Type.isArray(this.sectionGroupList)) {
	        this.sectionGroupList.forEach(function (sectionGroup) {
	          var filteredList;

	          if (sectionGroup.belongsToView) {
	            filteredList = sectionList.filter(function (section) {
	              return SectionSelector.getSectionType(section) === _this2.defaultCalendarType && SectionSelector.getSectionOwner(section) === _this2.defaultOwnerId;
	            }, _this2);
	          } else if (sectionGroup.type === 'user') {
	            filteredList = sectionList.filter(function (section) {
	              return SectionSelector.getSectionType(section) === 'user' && SectionSelector.getSectionOwner(section) === sectionGroup.ownerId;
	            });
	          } else if (sectionGroup.type === 'company') {
	            filteredList = sectionList.filter(function (section) {
	              return SectionSelector.getSectionType(section) === 'company_calendar' || SectionSelector.getSectionType(section) === sectionGroup.type;
	            });
	          } else {
	            filteredList = sectionList.filter(function (section) {
	              return SectionSelector.getSectionType(section) === sectionGroup.type;
	            });
	          }

	          filteredList = filteredList.filter(function (section) {
	            var id = parseInt(section.id || section.ID);
	            if (sectionIdList.includes(id)) return false;
	            sectionIdList.push(id);
	            return true;
	          });

	          if (filteredList.length > 0) {
	            menuItems.push({
	              html: '<span>' + sectionGroup.title + '</span>',
	              className: submenuClass
	            });

	            for (var _i = 0; _i < filteredList.length; _i++) {
	              menuItems.push(_this2.getMenuItem(filteredList[_i]));
	            }
	          }
	        });
	      } else {
	        for (i = 0; i < sectionList.length; i++) {
	          menuItems.push(this.getMenuItem(sectionList[i]));
	        }
	      }

	      var offsetLeft = 0;

	      if (this.mode === 'compact') {
	        offsetLeft = 40;
	      } else if (this.mode === 'textselect') {
	        offsetLeft = 0;
	      }

	      this.sectionMenu = main_popup.MenuManager.create(this.id, this.DOM.select, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: offsetLeft,
	        angle: this.mode === 'compact'
	      });
	      this.sectionMenu.popupWindow.contentContainer.style.overflow = "auto";
	      this.sectionMenu.popupWindow.contentContainer.style.maxHeight = "400px";

	      if (this.mode === 'full') {
	        this.sectionMenu.popupWindow.setWidth(this.DOM.select.offsetWidth - 2);
	        this.sectionMenu.popupWindow.contentContainer.style.overflowX = "hidden";
	      }

	      this.sectionMenu.show(); // Paint round icons for section menu

	      for (i = 0; i < this.sectionMenu.menuItems.length; i++) {
	        if (this.sectionMenu.menuItems[i].layout.item) {
	          var icon = this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');

	          if (icon) {
	            icon.style.backgroundColor = this.sectionMenu.menuItems[i].color;
	          }
	        }
	      }

	      BX.addClass(this.DOM.select, 'active');

	      if (main_core.Type.isFunction(this.openPopupCallback)) {
	        this.openPopupCallback(this);
	      }

	      BX.addCustomEvent(this.sectionMenu.popupWindow, 'onPopupClose', BX.delegate(function () {
	        if (main_core.Type.isFunction(this.openPopupCallback)) {
	          this.closePopupCallback();
	        }

	        BX.removeClass(this.DOM.select, 'active');
	        BX.PopupMenu.destroy(this.id);
	        this.sectionMenu = null;
	      }, this));
	    }
	  }, {
	    key: "getCurrentColor",
	    value: function getCurrentColor() {
	      return (this.getCurrentSection() || {}).color || false;
	    }
	  }, {
	    key: "getCurrentTitle",
	    value: function getCurrentTitle() {
	      return (this.getCurrentSection() || {}).name || '';
	    }
	  }, {
	    key: "getSectionList",
	    value: function getSectionList() {
	      return this.sectionList.filter(function (section) {
	        return section.PERM && section.PERM.edit || main_core.Type.isFunction(section.canDo) && section.canDo('edit');
	      });
	    }
	  }, {
	    key: "updateSectionImageNode",
	    value: function updateSectionImageNode(section) {
	      var _this3 = this;

	      if (!main_core.Type.isElementNode(this.DOM.selectImageWrap)) {
	        return;
	      }

	      if (section === undefined) {
	        section = this.sectionList.find(function (section) {
	          return parseInt(section.id) === parseInt(_this3.getCurrentSection().id);
	        });
	      }

	      if (section && section.type) {
	        var imageSrc = SectionSelector.getSectionImage(section);
	        var imageNode;

	        if (imageSrc) {
	          imageNode = main_core.Tag.render(_templateObject3$1(), imageSrc);
	        } else if (section.type === 'group') {
	          imageNode = main_core.Tag.render(_templateObject4$1());
	        } else if (section.type === 'user') {
	          imageNode = main_core.Tag.render(_templateObject5$1());
	        } else {
	          imageNode = main_core.Tag.render(_templateObject6$1());
	        }

	        main_core.Dom.clean(this.DOM.selectImageWrap);
	        this.DOM.selectImageWrap.appendChild(imageNode);
	      }
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      return this.sectionMenu;
	    }
	  }, {
	    key: "getMenuItem",
	    value: function getMenuItem(sectionItem) {
	      var _this = this;

	      return {
	        html: BX.util.htmlspecialchars(sectionItem.name || sectionItem.NAME),
	        color: sectionItem.color || sectionItem.COLOR,
	        className: 'calendar-add-popup-section-menu-item' + (this.mode === 'full' ? ' section-menu-item-full' : ''),
	        onclick: function (section) {
	          return function () {
	            if (main_core.Type.isDomNode(_this.DOM.innerValue)) {
	              _this.DOM.innerValue.style.backgroundColor = section.color || sectionItem.COLOR;
	            }

	            _this.updateSectionImageNode(section);

	            if (main_core.Type.isFunction(_this.selectCallback)) {
	              if (!section.color && sectionItem.COLOR) {
	                section.color = sectionItem.COLOR;
	              }

	              if (!section.id && sectionItem.ID) {
	                section.id = sectionItem.ID;
	              }

	              _this.selectCallback(section);
	            }

	            _this.sectionMenu.close();

	            _this.updateValue();
	          };
	        }(sectionItem)
	      };
	    }
	  }, {
	    key: "updateValue",
	    value: function updateValue() {
	      if (main_core.Type.isDomNode(this.DOM.innerValue)) {
	        this.DOM.innerValue.style.backgroundColor = this.getCurrentColor();
	      }

	      if (this.mode === 'full') {
	        this.DOM.select.appendChild(main_core.Dom.adjust(this.DOM.selectInnerText, {
	          text: this.getCurrentTitle(),
	          props: {
	            title: this.getCurrentTitle()
	          }
	        }));
	      } else if (this.mode === 'textselect') {
	        this.updateSectionImageNode();
	        this.DOM.select.appendChild(main_core.Dom.adjust(this.DOM.selectInnerText, {
	          props: {
	            title: main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle()
	          },
	          text: main_core.Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle()
	        }));
	      }
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(viewMode) {
	      this.viewMode = viewMode;

	      if (this.viewMode) {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
	      } else {
	        main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
	      }
	    }
	  }], [{
	    key: "getSectionType",
	    value: function getSectionType(section) {
	      return section.type || section.CAL_TYPE;
	    }
	  }, {
	    key: "getSectionImage",
	    value: function getSectionImage() {
	      var section = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return section.data ? section.data.IMAGE : section.IMAGE || '';
	    }
	  }, {
	    key: "getSectionOwner",
	    value: function getSectionOwner(section) {
	      return parseInt(section.OWNER_ID || section.data.OWNER_ID);
	    }
	  }]);
	  return SectionSelector;
	}();

	var RepeatSelector = /*#__PURE__*/function () {
	  function RepeatSelector(params) {
	    babelHelpers.classCallCheck(this, RepeatSelector);
	    var formElements = params.rruleType.form.elements;
	    this.getDate = params.getDate;
	    this.DOM = {
	      formElements: formElements,
	      wrap: params.wrap,
	      rruleType: params.rruleType,
	      interval: formElements['EVENT_RRULE[INTERVAL]'],
	      rruleEndsOn: {
	        never: formElements['rrule_endson'][0],
	        count: formElements['rrule_endson'][1],
	        until: formElements['rrule_endson'][2]
	      },
	      count: formElements['EVENT_RRULE[COUNT]'],
	      until: formElements['EVENT_RRULE[UNTIL]']
	    };
	    this.create();
	  }

	  babelHelpers.createClass(RepeatSelector, [{
	    key: "create",
	    value: function create() {
	      BX.bind(this.DOM.rruleType, 'change', BX.delegate(function () {
	        this.changeType(this.DOM.rruleType.value);
	      }, this));
	      BX.bind(this.DOM.until, 'click', BX.proxy(function () {
	        BX.calendar({
	          node: this.DOM.until,
	          field: this.DOM.until,
	          bTime: false
	        });
	        BX.focus(this.DOM.until);
	        this.DOM.rruleEndsOn.until.checked = true;
	      }, this));
	      BX.bind(this.DOM.count, 'click', BX.proxy(function () {
	        this.DOM.rruleEndsOn.count.checked = true;
	      }, this));
	    }
	  }, {
	    key: "changeType",
	    value: function changeType(type) {
	      this.DOM.rruleType.value = type ? type.toUpperCase() : 'NONE';
	      var rruleType = this.DOM.rruleType.value.toLowerCase();
	      this.DOM.wrap.className = 'calendar-rrule-type-' + rruleType;

	      if (rruleType === 'weekly' && BX.type.isFunction(this.getDate)) {
	        var fromDate = this.getDate();

	        if (BX.type.isDate(fromDate)) {
	          var day = calendar_util.Util.getWeekDayByInd(fromDate.getDay());
	          this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function (input) {
	            input.checked = input.checked || input.value === day;
	          }, this);
	        }
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue() {
	      var rrule = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.changeType(rrule.FREQ);
	      this.DOM.interval.value = rrule.INTERVAL || 1;

	      if (rrule.COUNT) {
	        this.DOM.rruleEndsOn.count.checked = 'checked';
	        this.DOM.count.value = rrule.COUNT;
	      } else if (rrule['~UNTIL']) {
	        this.DOM.rruleEndsOn.until.checked = 'checked';
	        this.DOM.until.value = rrule['~UNTIL'];
	      } else {
	        this.DOM.rruleEndsOn.never.checked = 'checked';
	      }

	      if (BX.type.isPlainObject(rrule.BYDAY)) {
	        this.DOM.formElements['EVENT_RRULE[BYDAY][]'].forEach(function (input) {
	          input.checked = rrule.BYDAY.hasOwnProperty(input.value);
	        }, this);
	      }
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.DOM.rruleType.value.toLowerCase();
	    }
	  }]);
	  return RepeatSelector;
	}();

	var SelectInput = /*#__PURE__*/function () {
	  function SelectInput(params) {
	    babelHelpers.classCallCheck(this, SelectInput);
	    this.id = params.id || 'bx-select-input-' + Math.round(Math.random() * 1000000);
	    this.values = params.values || false;
	    this.input = params.input;
	    this.defaultValue = params.defaultValue || '';
	    this.openTitle = params.openTitle || '';
	    this.className = params.className || '';
	    this.onChangeCallback = params.onChangeCallback || null;
	    this.zIndex = params.zIndex || 1200;
	    this.disabled = params.disabled;
	    this.minWidth = params.minWidth || 0;
	    this.setValue({
	      value: params.value,
	      valueIndex: params.valueIndex
	    });
	    this.curInd = false;
	    this.bindEventHandlers();
	  }

	  babelHelpers.createClass(SelectInput, [{
	    key: "bindEventHandlers",
	    value: function bindEventHandlers() {
	      if (this.onChangeCallback) {
	        BX.bind(this.input, 'change', this.onChangeCallback);
	        BX.bind(this.input, 'keyup', this.onChangeCallback);
	      }

	      if (this.values) {
	        BX.bind(this.input, 'click', BX.proxy(this.onClick, this));
	        BX.bind(this.input, 'focus', BX.proxy(this.onFocus, this));
	        BX.bind(this.input, 'blur', BX.proxy(this.onBlur, this));
	        BX.bind(this.input, 'keyup', BX.proxy(this.onKeyup, this));
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(params) {
	      this.currentValue = {
	        value: params.value
	      };
	      this.currentValueIndex = params.valueIndex;

	      if (this.currentValueIndex !== undefined && this.values[this.currentValueIndex]) {
	        this.input.value = this.values[this.currentValueIndex].label;
	      }
	    }
	  }, {
	    key: "getInputValue",
	    value: function getInputValue() {
	      return this.input.value;
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup() {
	      if (this.shown || this.disabled) return;

	      var ind = 0,
	          j = 0,
	          menuItems = [],
	          i,
	          _this = this;

	      for (i = 0; i < this.values.length; i++) {
	        if (this.values[i].delimiter) {
	          menuItems.push(this.values[i]);
	        } else {
	          if (this.currentValue && this.values[i] && this.values[i].value === this.currentValue.value) {
	            ind = j;
	          }

	          menuItems.push({
	            id: this.values[i].value,
	            text: this.values[i].label,
	            onclick: this.values[i].callback || function (value, label) {
	              return function () {
	                _this.input.value = label;

	                _this.popupMenu.close();

	                _this.onChange();
	              };
	            }(this.values[i].value, this.values[i].labelRaw || this.values[i].label)
	          });
	          j++;
	        }
	      }

	      this.popupMenu = BX.PopupMenu.create(this.id, this.input, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: -1
	      });
	      this.popupMenu.popupWindow.setWidth(Math.max(this.input.offsetWidth + 2, this.minWidth));
	      var menuContainer = this.popupMenu.layout.menuContainer;
	      BX.addClass(this.popupMenu.layout.menuContainer, 'calendar-select-popup');
	      this.popupMenu.show();
	      var menuItem = this.popupMenu.menuItems[ind];

	      if (menuItem && menuItem.layout) {
	        menuContainer.scrollTop = menuItem.layout.item.offsetTop - menuItem.layout.item.offsetHeight;
	      }

	      BX.addCustomEvent(this.popupMenu.popupWindow, 'onPopupClose', function () {
	        BX.PopupMenu.destroy(this.id);
	        this.shown = false;
	        this.popupMenu = null;
	      }.bind(this));
	      this.input.select();
	      this.shown = true;
	    }
	  }, {
	    key: "closePopup",
	    value: function closePopup() {
	      BX.PopupMenu.destroy(this.id);
	      this.popupMenu = null;
	      this.shown = false;
	    }
	  }, {
	    key: "onFocus",
	    value: function onFocus() {
	      setTimeout(function () {
	        if (!this.shown) {
	          this.showPopup();
	        }
	      }.bind(this), 200);
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      if (this.shown) {
	        this.closePopup();
	      } else {
	        this.showPopup();
	      }
	    }
	  }, {
	    key: "onBlur",
	    value: function onBlur() {
	      setTimeout(BX.delegate(this.closePopup, this), 200);
	    }
	  }, {
	    key: "onKeyup",
	    value: function onKeyup() {
	      setTimeout(BX.delegate(this.closePopup, this), 50);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      var val = this.input.value;
	      BX.onCustomEvent(this, 'onSelectInputChanged', [this, val]);

	      if (BX.type.isFunction(this.onChangeCallback)) {
	        this.onChangeCallback({
	          value: val
	        });
	      }
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.onChangeCallback) {
	        BX.unbind(this.input, 'change', this.onChangeCallback);
	        BX.unbind(this.input, 'keyup', this.onChangeCallback);
	      }

	      BX.unbind(this.input, 'click', BX.proxy(this.onClick, this));
	      BX.unbind(this.input, 'focus', BX.proxy(this.onFocus, this));
	      BX.unbind(this.input, 'blur', BX.proxy(this.onBlur, this));
	      BX.unbind(this.input, 'keyup', BX.proxy(this.onKeyup, this));

	      if (this.popupMenu) {
	        this.popupMenu.close();
	      }

	      BX.PopupMenu.destroy(this.id);
	      this.popupMenu = null;
	      this.shown = false;
	    }
	  }]);
	  return SelectInput;
	}();

	var PopupDialog = /*#__PURE__*/function () {
	  function PopupDialog() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, PopupDialog);
	    this.id = params.id || 'popup-dialog-' + Math.random();
	    this.zIndex = params.zIndex || 3200;
	    this.DOM = {};
	    this.title = '';
	  }

	  babelHelpers.createClass(PopupDialog, [{
	    key: "create",
	    value: function create() {
	      this.dialog = new BX.PopupWindow(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: this.getTitle(),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'bxc-popup-window',
	        buttons: this.getButtons(),
	        content: this.getContent(),
	        events: {}
	      });
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      return this.title;
	    }
	  }, {
	    key: "getContent",
	    value: function getContent() {
	      this.DOM.content = BX.create('DIV');
	      return this.DOM.content;
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      this.buttons = [];
	      return this.buttons;
	    }
	  }, {
	    key: "show",
	    value: function show(params) {
	      if (!this.dialog) {
	        this.create();
	      }

	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }]);
	  return PopupDialog;
	}();

	function _templateObject5$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-text-mode-inner\" style=\"display: none;\"></div>"]);

	  _templateObject5$2 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-dropdown\"></div>"]);

	  _templateObject4$2 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-text-inner\"></div>"]);

	  _templateObject3$2 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-text\"></div>"]);

	  _templateObject2$3 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-selector\"></div>"]);

	  _templateObject$4 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ViewSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ViewSelector, _EventEmitter);

	  function ViewSelector() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ViewSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ViewSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "views", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "created", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "currentValue", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "currentViewMode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ViewSelector');

	    if (main_core.Type.isArray(params.views)) {
	      _this.views = params.views;
	    }

	    _this.zIndex = params.zIndex || 3200;
	    _this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);

	    _this.create();

	    if (params.currentView) {
	      _this.setValue(params.currentView);
	    }

	    if (params.currentViewMode) {
	      _this.setViewMode(params.currentViewMode);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(ViewSelector, [{
	    key: "create",
	    value: function create() {
	      this.DOM.wrap = main_core.Tag.render(_templateObject$4());
	      this.DOM.selectorText = main_core.Tag.render(_templateObject2$3());
	      this.DOM.selectorTextInner = this.DOM.selectorText.appendChild(main_core.Tag.render(_templateObject3$2()));
	      this.DOM.wrap.appendChild(this.DOM.selectorText);
	      this.DOM.wrap.appendChild(main_core.Tag.render(_templateObject4$2()));
	      main_core.Event.bind(this.DOM.wrap, 'click', this.showPopup.bind(this));
	      this.DOM.viewModeTextInner = this.DOM.selectorText.appendChild(main_core.Tag.render(_templateObject5$2()));
	      this.created = true;
	    }
	  }, {
	    key: "getOuterWrap",
	    value: function getOuterWrap() {
	      if (!this.created) {
	        this.create();
	      }

	      return this.DOM.wrap;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.currentValue = this.views.find(function (view) {
	        return value.name === view.name;
	      }, this);

	      if (this.currentValue) {
	        main_core.Dom.adjust(this.DOM.selectorTextInner, {
	          text: this.currentValue.text
	        });
	      }
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(value) {
	      if (value) {
	        this.currentViewMode = this.views.find(function (view) {
	          return value === view.name && view.type === 'additional';
	        }, this);

	        if (this.currentViewMode) {
	          main_core.Dom.adjust(this.DOM.viewModeTextInner, {
	            text: '(' + this.currentViewMode.text + ')'
	          });
	        }

	        this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      var menuItems = [];
	      this.views.forEach(function (view) {
	        if (view.type === 'base') {
	          menuItems.push({
	            html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
	            //text: view.text,
	            className: this.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
	            onclick: function () {
	              this.emit('onChange', {
	                name: view.name,
	                type: view.type,
	                dataset: view.dataset
	              });
	              this.menuPopup.close();
	            }.bind(this)
	          });
	        }
	      }, this);

	      if (menuItems.length < this.views.length) {
	        menuItems.push({
	          html: '<span>' + main_core.Loc.getMessage('EC_VIEW_MODE_SHOW_BY') + '</span>',
	          className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
	        });
	        this.views.forEach(function (view) {
	          if (view.type === 'additional') {
	            menuItems.push({
	              text: view.text,
	              className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
	              onclick: function () {
	                this.emit('onChange', {
	                  name: view.name,
	                  type: view.type,
	                  dataset: view.dataset
	                });
	                this.menuPopup.close();
	              }.bind(this)
	            });
	          }
	        }, this);
	      }

	      return menuItems;
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup() {
	      if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	        return this.menuPopup.close();
	      }

	      this.menuPopup = main_popup.MenuManager.create(this.popupId, this.DOM.selectorText, this.getMenuItems(), {
	        className: "calendar-view-switcher-popup",
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: -3,
	        offsetLeft: this.DOM.selectorText.offsetWidth - 6,
	        angle: true,
	        cacheable: false
	      });
	      this.menuPopup.show();
	    }
	  }, {
	    key: "closePopup",
	    value: function closePopup() {
	      if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	        this.menuPopup.close();
	      }
	    }
	  }]);
	  return ViewSelector;
	}(main_core_events.EventEmitter);

	function _templateObject2$4() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span \n\t\t\t\t\t\tclass=\"calendar-view-switcher-list-item\"\n\t\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t\t>", "</span>"]);

	  _templateObject2$4 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-view-switcher-list\"></div>"]);

	  _templateObject$5 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var LineViewSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(LineViewSelector, _EventEmitter);

	  function LineViewSelector() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, LineViewSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(LineViewSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "views", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "created", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "currentValue", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "currentViewMode", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.LineViewSelector');

	    if (main_core.Type.isArray(params.views)) {
	      _this.views = params.views;
	    }

	    _this.viewsMap = new WeakMap();
	    _this.zIndex = params.zIndex || 3200;
	    _this.popupId = params.id || 'view-selector-' + Math.round(Math.random() * 10000);

	    _this.create();

	    if (params.currentView) {
	      _this.setValue(params.currentView);
	    }

	    return _this;
	  }

	  babelHelpers.createClass(LineViewSelector, [{
	    key: "create",
	    value: function create() {
	      var _this2 = this;

	      this.DOM.wrap = main_core.Tag.render(_templateObject$5());
	      this.views.forEach(function (view) {
	        if (view.type === 'base') {
	          _this2.viewsMap.set(view, {
	            wrap: _this2.DOM.wrap.appendChild(main_core.Tag.render(_templateObject2$4(), function () {
	              _this2.emit('onChange', {
	                name: view.name,
	                type: view.type,
	                dataset: view.dataset
	              });
	            }, view.text))
	          });
	        }
	      });
	      this.created = true;
	    }
	  }, {
	    key: "getOuterWrap",
	    value: function getOuterWrap() {
	      if (!this.created) {
	        this.create();
	      }

	      return this.DOM.wrap;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.currentValue = this.views.find(function (view) {
	        return value.name === view.name;
	      }, this);

	      if (this.currentValue) {
	        var viewData = this.viewsMap.get(this.currentValue);
	        var currentActiveWrap = this.DOM.wrap.querySelector('.calendar-view-switcher-list-item-active');

	        if (main_core.Type.isDomNode(currentActiveWrap)) {
	          main_core.Dom.removeClass(currentActiveWrap, 'calendar-view-switcher-list-item-active');
	        }

	        if (main_core.Type.isDomNode(viewData.wrap)) {
	          main_core.Dom.addClass(viewData.wrap, 'calendar-view-switcher-list-item-active');
	        }
	      }
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(value) {
	      if (value) {
	        this.currentViewMode = this.views.find(function (view) {
	          return value === view.name && view.type === 'additional';
	        }, this); // if (this.currentViewMode)
	        // {
	        // 	Dom.adjust(this.DOM.viewModeTextInner, {text: '(' + this.currentViewMode.text + ')'});
	        // }
	        //this.DOM.viewModeTextInner.style.display = this.currentViewMode ? '' : 'block';
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems() {
	      var _this3 = this;

	      var menuItems = [];
	      this.views.forEach(function (view) {
	        if (view.type === 'base') {
	          menuItems.push({
	            html: '<span>' + view.text + '</span>' + (view.hotkey ? '<span class="calendar-item-hotkey">' + view.hotkey + '</span>' : ''),
	            className: _this3.currentValue.name === view.name ? 'menu-popup-item-accept' : ' ',
	            onclick: function onclick() {
	              _this3.emit('onChange', {
	                name: view.name,
	                type: view.type,
	                dataset: view.dataset
	              });

	              _this3.menuPopup.close();
	            }
	          });
	        }
	      });

	      if (menuItems.length < this.views.length) {
	        menuItems.push({
	          html: '<span>' + main_core.Loc.getMessage('EC_VIEW_MODE_SHOW_BY') + '</span>',
	          className: 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label'
	        });
	        this.views.forEach(function (view) {
	          if (view.type === 'additional') {
	            menuItems.push({
	              text: view.text,
	              className: this.currentViewMode.name === view.name ? 'menu-popup-item-accept' : ' ',
	              onclick: function () {
	                this.emit('onChange', {
	                  name: view.name,
	                  type: view.type,
	                  dataset: view.dataset
	                });
	                this.menuPopup.close();
	              }.bind(this)
	            });
	          }
	        }, this);
	      }

	      return menuItems;
	    } // showPopup()
	    // {
	    // 	this.closePopup();
	    //
	    // 	this.menuPopup = MenuManager.create(
	    // 		this.popupId,
	    // 		this.DOM.selectorText,
	    // 		this.getMenuItems(),
	    // 		{
	    // 			className: "calendar-view-switcher-popup",
	    // 			closeByEsc : true,
	    // 			autoHide : true,
	    // 			zIndex: this.zIndex,
	    // 			offsetTop: -3,
	    // 			offsetLeft: this.DOM.selectorText.offsetWidth - 6,
	    // 			angle: true,
	    // 			cacheable: false
	    // 		}
	    // 	);
	    //
	    // 	this.menuPopup.show();
	    // }
	    //
	    // closePopup()
	    // {
	    // 	if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown())
	    // 	{
	    // 		return this.menuPopup.close();
	    // 	}
	    // }

	  }]);
	  return LineViewSelector;
	}(main_core_events.EventEmitter);

	var AddButton = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(AddButton, _EventEmitter);

	  function AddButton() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, AddButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AddButton).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "showTasks", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.AddButton');

	    _this.zIndex = params.zIndex || 3200;
	    _this.popupId = params.id || 'add-button-' + Math.round(Math.random() * 10000);
	    _this.showTasks = params.showTasks;
	    _this.addEntryHandler = main_core.Type.isFunction(params.addEntry) ? params.addEntry : null;
	    _this.addTaskHandler = main_core.Type.isFunction(params.addTask) ? params.addTask : null;

	    _this.create();

	    return _this;
	  }

	  babelHelpers.createClass(AddButton, [{
	    key: "create",
	    value: function create() {
	      this.menuItems = [{
	        text: main_core.Loc.getMessage('EC_ADD_EVENT'),
	        onclick: this.addEntry.bind(this)
	      }];

	      if (this.addTaskHandler) {
	        this.menuItems.push({
	          text: main_core.Loc.getMessage('EC_ADD_TASK'),
	          onclick: this.addTask.bind(this)
	        });
	      }

	      if (this.menuItems.length > 1) {
	        this.DOM.wrap = main_core.Dom.create("span", {
	          props: {
	            className: "ui-btn-split ui-btn-success"
	          },
	          children: [main_core.Dom.create("button", {
	            props: {
	              className: "ui-btn-main",
	              type: "button"
	            },
	            html: main_core.Loc.getMessage('EC_ADD'),
	            events: {
	              click: this.addEntry.bind(this)
	            }
	          })]
	        });
	        this.DOM.addButtonExtra = main_core.Dom.create("span", {
	          props: {
	            className: "ui-btn-extra"
	          },
	          events: {
	            click: this.showPopup.bind(this)
	          }
	        });
	        this.DOM.wrap.appendChild(this.DOM.addButtonExtra);
	      } else {
	        this.DOM.wrap = main_core.Dom.create("button", {
	          props: {
	            className: "ui-btn ui-btn-success",
	            type: "button"
	          },
	          html: main_core.Loc.getMessage('EC_ADD'),
	          events: {
	            click: this.addEntry.bind(this)
	          }
	        });
	      }
	    }
	  }, {
	    key: "getWrap",
	    value: function getWrap() {
	      return this.DOM.wrap;
	    }
	  }, {
	    key: "showPopup",
	    value: function showPopup() {
	      if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	        return this.menuPopup.close();
	      }

	      this.menuPopup = BX.PopupMenu.create(this.popupId, this.DOM.addButtonExtra, this.menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 15,
	        angle: true
	      });
	      this.menuPopup.show();
	      BX.addCustomEvent(this.menuPopup.popupWindow, 'onPopupClose', function () {
	        BX.PopupMenu.destroy(this.popupId);
	        this.menuPopup = null;
	        this.addBtnMenu = null;
	      }.bind(this));
	    }
	  }, {
	    key: "addEntry",
	    value: function addEntry() {
	      if (this.addEntryHandler) {
	        this.addEntryHandler();
	      }

	      if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	        this.menuPopup.close();
	      }
	    }
	  }, {
	    key: "addTask",
	    value: function addTask() {
	      if (this.addTaskHandler) {
	        this.addTaskHandler();
	      }

	      if (this.menuPopup && this.menuPopup.popupWindow && this.menuPopup.popupWindow.isShown()) {
	        this.menuPopup.close();
	      }
	    }
	  }]);
	  return AddButton;
	}(main_core_events.EventEmitter);

	var MeetingStatusControl = /*#__PURE__*/function (_Event$EventEmitter) {
	  babelHelpers.inherits(MeetingStatusControl, _Event$EventEmitter);

	  function MeetingStatusControl() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MeetingStatusControl);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MeetingStatusControl).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "showTasks", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.MeetingStatusControl');

	    _this.BX = calendar_util.Util.getBX();

	    if (params.wrap && main_core.Type.isDomNode(params.wrap)) {
	      _this.DOM.wrap = params.wrap;
	    } else {
	      throw new Error("The argument \"params.wrap\" must be a DOM node.");
	    }

	    _this.id = params.id || 'meeting-status-control-' + Math.round(Math.random() * 10000);
	    _this.zIndex = 3100;

	    _this.create();

	    _this.status = params.currentStatus || null;

	    if (_this.status) {
	      _this.updateStatus();
	    }

	    return _this;
	  }

	  babelHelpers.createClass(MeetingStatusControl, [{
	    key: "create",
	    value: function create() {
	      this.acceptBtn = new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_Y'),
	        className: 'ui-btn ui-btn-primary',
	        events: {
	          click: this.accept.bind(this)
	        }
	      });
	      this.acceptBtn.renderTo(this.DOM.wrap);
	      this.declineBtn = new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_N'),
	        className: 'ui-btn ui-btn-light-border',
	        events: {
	          click: this.decline.bind(this)
	        }
	      });
	      this.declineBtn.renderTo(this.DOM.wrap);
	    }
	  }, {
	    key: "updateStatus",
	    value: function updateStatus() {
	      if (this.status === 'H') {
	        this.acceptBtn.getContainer().style.display = 'none';
	        this.declineBtn.getContainer().style.display = '';
	        this.declineBtn.setText(main_core.Loc.getMessage('EC_VIEW_DESIDE_BUT_OWNER_N'));
	      } else {
	        if (this.status === 'Y') {
	          this.acceptBtn.getContainer().style.display = 'none';
	          this.declineBtn.getContainer().style.display = '';
	        } else if (this.status === 'N') {
	          this.acceptBtn.getContainer().style.display = '';
	          this.declineBtn.getContainer().style.display = 'none';
	        } else {
	          this.acceptBtn.getContainer().style.display = '';
	          this.declineBtn.getContainer().style.display = '';
	        }
	      }
	    }
	  }, {
	    key: "accept",
	    value: function accept() {
	      this.setStatus('Y');
	    }
	  }, {
	    key: "decline",
	    value: function decline() {
	      this.setStatus('N');
	    }
	  }, {
	    key: "setStatus",
	    value: function setStatus(value) {
	      var emitEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      this.status = value;

	      if (this.menuPopup) {
	        this.menuPopup.close();
	      }

	      if (emitEvent) {
	        this.emit('onSetStatus', new main_core.Event.BaseEvent({
	          data: {
	            status: value
	          }
	        }));
	      }
	    }
	  }]);
	  return MeetingStatusControl;
	}(main_core.Event.EventEmitter);

	var ConfirmStatusDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ConfirmStatusDialog, _EventEmitter);

	  function ConfirmStatusDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, ConfirmStatusDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfirmStatusDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');

	    _this.zIndex = 3200;
	    _this.id = 'confirm-status-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(ConfirmStatusDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var content = main_core.Dom.create('DIV');
	      this.dialog = new BX.PopupWindow(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_DECLINE_REC_EVENT'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'bxc-popup-window',
	        buttons: [new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: this.close.bind(this)
	          }
	        })],
	        content: content,
	        events: {}
	      });
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_DECLINE_ONLY_THIS'),
	        events: {
	          click: function click() {
	            _this2.onDeclineHandler();

	            _this2.emit('onDecline', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'this'
	              }
	            }));
	          }
	        }
	      }).buttonNode);
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_DECLINE_NEXT'),
	        events: {
	          click: function click() {
	            _this2.onDeclineHandler();

	            _this2.emit('onDecline', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'next'
	              }
	            }));
	          }
	        }
	      }).buttonNode);
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_DECLINE_ALL'),
	        events: {
	          click: function click() {
	            _this2.onDeclineHandler();

	            _this2.emit('onDecline', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'all'
	              }
	            }));
	          }
	        }
	      }).buttonNode);
	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }, {
	    key: "onDeclineHandler",
	    value: function onDeclineHandler() {
	      this.close();
	      var compactForm = calendar_entry.EntryManager.getCompactViewForm();

	      if (compactForm && compactForm.isShown()) {
	        compactForm.close();
	      }
	    }
	  }]);
	  return ConfirmStatusDialog;
	}(main_core_events.EventEmitter);

	var ConfirmEditDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ConfirmEditDialog, _EventEmitter);

	  function ConfirmEditDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, ConfirmEditDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfirmEditDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ConfirmEditDialog');

	    _this.zIndex = 3200;
	    _this.id = 'confirm-status-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(ConfirmEditDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var content = main_core.Dom.create('DIV');
	      this.dialog = new BX.PopupWindow(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_EDIT_REC_EVENT'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'bxc-popup-window',
	        buttons: [new BX.PopupWindowButtonLink({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	          className: "popup-window-button-link-cancel",
	          events: {
	            click: this.close.bind(this)
	          }
	        })],
	        content: content,
	        events: {},
	        cacheable: false
	      });
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_REC_EV_ONLY_THIS_EVENT'),
	        events: {
	          click: function click() {
	            _this2.emit('onEdit', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'this'
	              }
	            }));

	            _this2.close();
	          }
	        }
	      }).buttonNode);
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_REC_EV_NEXT'),
	        events: {
	          click: function click() {
	            _this2.emit('onEdit', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'next'
	              }
	            }));

	            _this2.close();
	          }
	        }
	      }).buttonNode);
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_REC_EV_ALL'),
	        events: {
	          click: function click() {
	            _this2.emit('onEdit', new main_core_events.BaseEvent({
	              data: {
	                recursionMode: 'all'
	              }
	            }));

	            _this2.close();
	          }
	        }
	      }).buttonNode);
	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }]);
	  return ConfirmEditDialog;
	}(main_core_events.EventEmitter);

	var ConfirmDeleteDialog = /*#__PURE__*/function (_PopupDialog) {
	  babelHelpers.inherits(ConfirmDeleteDialog, _PopupDialog);

	  function ConfirmDeleteDialog() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ConfirmDeleteDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfirmDeleteDialog).call(this, params));
	    _this.title = BX.message('EC_DEL_REC_EVENT');
	    _this.entry = params.entry;
	    return _this;
	  }

	  babelHelpers.createClass(ConfirmDeleteDialog, [{
	    key: "getContent",
	    value: function getContent() {
	      this.DOM.content = BX.create('DIV');
	      this.DOM.content.appendChild(new BX.PopupWindowButton({
	        text: BX.message('EC_REC_EV_ONLY_THIS_EVENT'),
	        events: {
	          click: function () {
	            this.entry.deleteThis();
	            this.close();
	          }.bind(this)
	        }
	      }).buttonNode);
	      this.DOM.content.appendChild(new BX.PopupWindowButton({
	        text: BX.message('EC_REC_EV_NEXT'),
	        events: {
	          click: function () {
	            this.entry.deleteNext();
	            this.close();
	          }.bind(this)
	        }
	      }).buttonNode);
	      this.DOM.content.appendChild(new BX.PopupWindowButton({
	        text: BX.message('EC_REC_EV_ALL'),
	        events: {
	          click: function () {
	            this.entry.deleteAll();
	            this.close();
	          }.bind(this)
	        }
	      }).buttonNode);
	      return this.DOM.content;
	    }
	  }, {
	    key: "getButtons",
	    value: function getButtons() {
	      return [new BX.PopupWindowButtonLink({
	        text: BX.message('EC_SEC_SLIDER_CANCEL'),
	        className: "popup-window-button-link-cancel",
	        events: {
	          click: this.close.bind(this)
	        }
	      })];
	    }
	  }]);
	  return ConfirmDeleteDialog;
	}(PopupDialog);

	function _templateObject14() {
	  var data = babelHelpers.taggedTemplateLiteral(["<label for=\"{this.UID}\">", "</label>"]);

	  _templateObject14 = function _templateObject14() {
	    return data;
	  };

	  return data;
	}

	function _templateObject13() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input value=\"Y\" type=\"checkbox\" id=\"{this.UID}\"/>\n\t\t\t"]);

	  _templateObject13 = function _templateObject13() {
	    return data;
	  };

	  return data;
	}

	function _templateObject12() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"calendar-event-full-day\"></span>\n\t\t\t"]);

	  _templateObject12 = function _templateObject12() {
	    return data;
	  };

	  return data;
	}

	function _templateObject11() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-value calendar-field-value-date\"></span>"]);

	  _templateObject11 = function _templateObject11() {
	    return data;
	  };

	  return data;
	}

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"calendar-field calendar-field-datetime\" value=\"\" type=\"text\" autocomplete=\"off\" style=\"width: ", "px;\"/>"]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-value calendar-field-value-time\"></span>"]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"calendar-field calendar-field-time\" value=\"\" type=\"text\" autocomplete=\"off\" style=\"width: ", "px;\"/>\n\t\t\t"]);

	  _templateObject8$1 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-block calendar-field-block-right\"></div>"]);

	  _templateObject7$1 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-block calendar-field-block-between\" />"]);

	  _templateObject6$2 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-value calendar-field-value-time\"></span>"]);

	  _templateObject5$3 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"calendar-field calendar-field-time\" value=\"\" type=\"text\" autocomplete=\"off\" style=\"width: ", "px;\"/>\n\t\t\t"]);

	  _templateObject4$3 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-field-value calendar-field-value-date\"></span>"]);

	  _templateObject3$3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input class=\"calendar-field calendar-field-datetime\" value=\"\" type=\"text\" autocomplete=\"off\" style=\"width: ", "px;\"/>\n\t\t\t"]);

	  _templateObject2$5 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-field-block calendar-field-block-left\"></div>"]);

	  _templateObject$6 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DateTimeControl = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(DateTimeControl, _EventEmitter);

	  function DateTimeControl(uid) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
	      showTimezone: true
	    };
	    babelHelpers.classCallCheck(this, DateTimeControl);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateTimeControl).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DATE_INPUT_WIDTH", 110);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "TIME_INPUT_WIDTH", 70);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "MODIFIED_TIME_INPUT_WIDTH", 80);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "zIndex", 4200);

	    _this.setEventNamespace('BX.Calendar.Controls.DateTimeControl');

	    _this.showTimezone = options.showTimezone;
	    _this.inlineEditMode = !!options.inlineEditMode;
	    _this.currentInlineEditMode = options.currentInlineEditMode || 'view';
	    _this.UID = uid || 'date-time-' + Math.round(Math.random() * 100000);
	    _this.DOM = {
	      outerWrap: options.outerWrap || null,
	      outerContent: options.outerContent || null
	    };

	    _this.create();

	    return _this;
	  }

	  babelHelpers.createClass(DateTimeControl, [{
	    key: "create",
	    value: function create() {
	      if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	        if (this.inlineEditMode) {
	          main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	        }

	        this.DOM.leftInnerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject$6()));
	        this.DOM.fromDate = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_templateObject2$5(), this.DATE_INPUT_WIDTH));

	        if (this.inlineEditMode) {
	          this.DOM.fromDateText = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_templateObject3$3()));
	        }

	        this.DOM.fromTime = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_templateObject4$3(), this.TIME_INPUT_WIDTH));

	        if (this.inlineEditMode) {
	          this.DOM.fromTimeText = this.DOM.leftInnerWrap.appendChild(main_core.Tag.render(_templateObject5$3()));
	        }

	        this.DOM.betweenSpacer = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject6$2()));
	        this.DOM.rightInnerWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject7$1()));
	        this.DOM.toTime = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_templateObject8$1(), this.TIME_INPUT_WIDTH));

	        if (this.inlineEditMode) {
	          this.DOM.toTimeText = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_templateObject9()));
	        }

	        this.DOM.toDate = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_templateObject10(), this.DATE_INPUT_WIDTH));

	        if (this.inlineEditMode) {
	          this.DOM.toDateText = this.DOM.rightInnerWrap.appendChild(main_core.Tag.render(_templateObject11()));
	        }

	        this.fromTimeControl = new TimeSelector({
	          input: this.DOM.fromTime,
	          onChangeCallback: this.handleTimeFromChange.bind(this)
	        });
	        this.toTimeControl = new TimeSelector({
	          input: this.DOM.toTime,
	          onChangeCallback: this.handleTimeToChange.bind(this)
	        });
	        var fullDayWrap = this.DOM.outerWrap.appendChild(main_core.Tag.render(_templateObject12()));
	        this.DOM.fullDay = fullDayWrap.appendChild(main_core.Tag.render(_templateObject13()));
	        fullDayWrap.appendChild(main_core.Tag.render(_templateObject14(), main_core.Loc.getMessage('EC_ALL_DAY')));
	      } //this.DOM.defTimezoneWrap = BX(this.UID + '_timezone_default_wrap');
	      //this.DOM.defTimezone = BX(this.UID + '_timezone_default');


	      if (this.showTimezone) ;

	      this.bindEventHandlers();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue() {
	      var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.DOM.fromDate.value = calendar_util.Util.formatDate(value.from);
	      this.DOM.toDate.value = calendar_util.Util.formatDate(value.to);
	      this.lastDateValue = value.from;
	      this.fromTimeControl.setValue(value.from);
	      this.toTimeControl.setValue(value.to);
	      this.DOM.fromTime.value = calendar_util.Util.formatTime(value.from);
	      this.DOM.toTime.value = calendar_util.Util.formatTime(value.to);

	      if (this.inlineEditMode) {
	        this.DOM.fromDateText.innerHTML = calendar_util.Util.formatDateUsable(value.from, true, true);
	        this.DOM.toDateText.innerHTML = calendar_util.Util.formatDateUsable(value.to, true, true); // Hide right part if it's the same date

	        this.DOM.toDateText.style.display = this.DOM.fromDate.value === this.DOM.toDate.value ? 'none' : '';

	        if (value.fullDay) {
	          if (this.DOM.fromDate.value === this.DOM.toDate.value) {
	            this.DOM.toTimeText.innerHTML = main_core.Loc.getMessage('EC_ALL_DAY');
	            this.DOM.toTimeText.style.display = '';
	            this.DOM.fromTimeText.style.display = 'none';
	            this.DOM.fromTimeText.innerHTML = '';
	          } else {
	            this.DOM.betweenSpacer.style.display = '';
	            this.DOM.fromTimeText.style.display = 'none';
	            this.DOM.toTimeText.style.display = 'none';
	          }
	        } else {
	          this.DOM.fromTimeText.innerHTML = this.DOM.fromTime.value;
	          this.DOM.toTimeText.innerHTML = this.DOM.toTime.value;
	          this.DOM.betweenSpacer.style.display = '';
	          this.DOM.fromTimeText.style.display = '';
	          this.DOM.toTimeText.style.display = '';
	        }
	      }

	      if (value.fullDay !== undefined) {
	        this.DOM.fullDay.checked = value.fullDay;
	      }

	      if (this.showTimezone) {
	        value.timezoneFrom = value.timezoneFrom || value.timezoneName;
	        value.timezoneTo = value.timezoneTo || value.timezoneName;

	        if (value.timezoneFrom !== undefined && main_core.Type.isDomNode(this.DOM.fromTz)) {
	          this.DOM.fromTz.value = value.timezoneFrom;
	        }

	        if (value.timezoneTo !== undefined && main_core.Type.isDomNode(this.DOM.toTz)) {
	          this.DOM.toTz.value = value.timezoneTo;
	        }

	        if (value.timezoneName !== undefined && (value.timezoneName !== value.timezoneFrom || value.timezoneName !== value.timezoneTo)) {
	          this.switchTimezone(true);
	        }
	      }

	      this.value = value;
	      this.handleFullDayChange();
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var value = {
	        fullDay: this.DOM.fullDay.checked,
	        fromDate: this.DOM.fromDate.value,
	        toDate: this.DOM.toDate.value,
	        fromTime: this.DOM.fromTime.value,
	        toTime: this.DOM.toTime.value,
	        timezoneFrom: this.DOM.fromTz ? this.DOM.fromTz.value : this.value.timezoneFrom || this.value.timezoneName || null,
	        timezoneTo: this.DOM.toTz ? this.DOM.toTz.value : this.value.timezoneTo || this.value.timezoneName || null
	      };
	      value.from = calendar_util.Util.parseDate(value.fromDate);

	      if (main_core.Type.isDate(value.from)) {
	        value.to = calendar_util.Util.parseDate(value.toDate);

	        if (!main_core.Type.isDate(value.to)) {
	          value.to = value.from;
	        }

	        if (value.fullDay) {
	          value.from.setHours(0, 0, 0);
	          value.to.setHours(0, 0, 0);
	        } else {
	          var fromTime = calendar_util.Util.parseTime(value.fromTime),
	              toTime = calendar_util.Util.parseTime(value.toTime) || fromTime;

	          if (fromTime && toTime) {
	            value.from.setHours(fromTime.h, fromTime.m, 0);
	            value.to.setHours(toTime.h, toTime.m, 0);
	          }
	        }
	      }

	      return value;
	    }
	  }, {
	    key: "bindEventHandlers",
	    value: function bindEventHandlers() {
	      var _this2 = this;

	      main_core.Event.bind(this.DOM.fromDate, 'click', DateTimeControl.showInputCalendar);
	      main_core.Event.bind(this.DOM.fromDate, 'change', this.handleDateFromChange.bind(this));
	      main_core.Event.bind(this.DOM.toDate, 'click', DateTimeControl.showInputCalendar);
	      main_core.Event.bind(this.DOM.toDate, 'change', this.handleDateToChange.bind(this));
	      main_core.Event.bind(this.DOM.fullDay, 'click', function () {
	        _this2.handleFullDayChange();

	        _this2.handleValueChange();
	      });

	      if (this.inlineEditMode) {
	        main_core.Event.bind(this.DOM.outerWrap, 'click', this.changeInlineEditMode.bind(this));
	      }

	      if (main_core.Type.isDomNode(this.DOM.defTimezone)) {
	        main_core.Event.bind(this.DOM.defTimezone, 'change', BX.delegate(function () {
	          //this.calendar.util.setUserOption('timezoneName', this.DOM.defTimezone.value);
	          if (this.bindFromToDefaultTimezones) {
	            this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value;
	          }
	        }, this));
	      }

	      if (this.showTimezone) {
	        if (main_core.Type.isDomNode(this.DOM.tzButton)) {
	          main_core.Event.bind(this.DOM.tzButton, 'click', this.switchTimezone.bind(this));
	        }

	        main_core.Event.bind(this.DOM.fromTz, 'change', function () {
	          if (this.bindTimezones) {
	            this.DOM.toTz.value = this.DOM.fromTz.value;
	          }

	          this.bindFromToDefaultTimezones = false;
	        }.bind(this));
	        main_core.Event.bind(this.DOM.toTz, 'change', function () {
	          this.bindTimezones = false;
	          this.bindFromToDefaultTimezones = false;
	        }.bind(this));
	        this.bindTimezones = this.DOM.fromTz.value === this.DOM.toTz.value;
	        this.bindFromToDefaultTimezones = this.bindTimezones && this.DOM.fromTz.value === this.DOM.toTz.value && this.DOM.fromTz.value === this.DOM.defTimezone.value;
	      }
	    }
	  }, {
	    key: "handleDateFromChange",
	    value: function handleDateFromChange() {
	      var fromTime = calendar_util.Util.parseTime(this.DOM.fromTime.value),
	          toTime = calendar_util.Util.parseTime(this.DOM.toTime.value),
	          fromDate = calendar_util.Util.parseDate(this.DOM.fromDate.value),
	          toDate = calendar_util.Util.parseDate(this.DOM.toDate.value);

	      if (this.lastDateValue) {
	        if (this.DOM.fullDay.checked && this.lastDateValue) {
	          this.lastDateValue.setHours(0, 0, 0);
	        } else {
	          if (fromDate && fromTime) {
	            fromDate.setHours(fromTime.h, fromTime.m, 0);
	          }

	          if (toDate && toTime) {
	            toDate.setHours(toTime.h, toTime.m, 0);
	          }
	        }

	        if (fromDate && this.lastDateValue) {
	          toDate = new Date(fromDate.getTime() + (toDate.getTime() - this.lastDateValue.getTime() || 3600000));

	          if (toDate) {
	            this.DOM.toDate.value = calendar_util.Util.formatDate(toDate);
	          }
	        }
	      }

	      this.lastDateValue = fromDate;
	      this.handleValueChange();
	    }
	  }, {
	    key: "handleTimeFromChange",
	    value: function handleTimeFromChange() {
	      var fromTime = calendar_util.Util.parseTime(this.DOM.fromTime.value),
	          toTime = calendar_util.Util.parseTime(this.DOM.toTime.value),
	          fromDate = calendar_util.Util.parseDate(this.DOM.fromDate.value),
	          toDate = calendar_util.Util.parseDate(this.DOM.toDate.value);

	      if (fromDate && fromTime) {
	        fromDate.setHours(fromTime.h, fromTime.m, 0);
	      }

	      if (toDate && toTime) {
	        toDate.setHours(toTime.h, toTime.m, 0);
	      }

	      if (this.lastDateValue) {
	        var newToDate = new Date(calendar_util.Util.getTimeRounded(fromDate) + calendar_util.Util.getTimeRounded(toDate) - calendar_util.Util.getTimeRounded(this.lastDateValue));
	        this.DOM.toTime.value = calendar_util.Util.formatTime(newToDate);
	        this.DOM.toDate.value = calendar_util.Util.formatDate(newToDate);
	      }

	      this.lastDateValue = fromDate;
	      this.handleValueChange();
	    }
	  }, {
	    key: "handleDateToChange",
	    value: function handleDateToChange() {
	      this.handleValueChange();
	    }
	  }, {
	    key: "handleTimeToChange",
	    value: function handleTimeToChange() {
	      this.handleValueChange();
	    }
	  }, {
	    key: "handleFullDayChange",
	    value: function handleFullDayChange() {
	      var fullDay = this.getFullDayValue();

	      if (fullDay) {
	        if (main_core.Type.isDomNode(this.DOM.dateTimeWrap)) {
	          main_core.Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	        }

	        if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	          main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
	        }
	      } else {
	        if (main_core.Type.isDomNode(this.DOM.dateTimeWrap)) {
	          main_core.Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
	        }

	        if (main_core.Type.isDomNode(this.DOM.outerWrap)) {
	          main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
	        }
	      }
	    }
	  }, {
	    key: "handleValueChange",
	    value: function handleValueChange() {
	      this.emit('onChange', new main_core_events.BaseEvent({
	        data: {
	          value: this.getValue()
	        }
	      }));
	    }
	  }, {
	    key: "getFullDayValue",
	    value: function getFullDayValue() {
	      return !!this.DOM.fullDay.checked;
	    }
	  }, {
	    key: "switchTimezone",
	    value: function switchTimezone(showTimezone) {
	      if (!main_core.Type.isBoolean(showTimezone)) {
	        showTimezone = BX.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	      }

	      if (showTimezone) {
	        main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	        main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	      } else {
	        main_core.Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
	        main_core.Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
	      }
	    }
	  }, {
	    key: "changeInlineEditMode",
	    value: function changeInlineEditMode() {
	      if (!this.viewMode) {
	        this.setInlineEditMode('edit');
	      }
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(viewMode) {
	      this.viewMode = viewMode;

	      if (this.viewMode && this.currentInlineEditMode === 'edit') {
	        this.setInlineEditMode('view');
	      }
	    }
	  }, {
	    key: "setInlineEditMode",
	    value: function setInlineEditMode(currentInlineEditMode) {
	      if (this.inlineEditMode) {
	        this.currentInlineEditMode = currentInlineEditMode;

	        if (this.currentInlineEditMode === 'edit') {
	          main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
	          main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	        } else {
	          main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
	          main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
	        }
	      }
	    }
	  }], [{
	    key: "showInputCalendar",
	    value: function showInputCalendar(e) {
	      var target = e.target || e.srcElement;

	      if (main_core.Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input') {
	        var calendarControl = BX.calendar.get();

	        if (calendarControl.popup) {
	          // Workaround hack for BX.calendar - it works as singleton and we trying to reinit it
	          calendarControl.popup.destroy();
	          calendarControl.popup = null;
	          calendarControl._current_layer = null;
	          calendarControl._layers = {};
	        }

	        calendarControl.Show({
	          node: target.parentNode,
	          field: target,
	          bTime: false
	        });
	        BX.onCustomEvent(window, 'onCalendarControlChildPopupShown');
	        var calendarPopup = calendarControl.popup;

	        if (calendarPopup) {
	          BX.removeCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
	          BX.addCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
	        }
	      }
	    }
	  }, {
	    key: "inputCalendarClosePopupHandler",
	    value: function inputCalendarClosePopupHandler(e) {
	      BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	    }
	  }]);
	  return DateTimeControl;
	}(main_core_events.EventEmitter);

	var BusyUsersDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BusyUsersDialog, _EventEmitter);

	  function BusyUsersDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, BusyUsersDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BusyUsersDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');

	    _this.zIndex = 3200;
	    _this.id = 'confirm-status-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(BusyUsersDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.plural = params.users.length > 1;
	      var i,
	          userNames = [];

	      for (i = 0; i < params.users.length; i++) {
	        userNames.push(params.users[i].DISPLAY_NAME);
	      }

	      userNames = userNames.join(', ');
	      var content = BX.create('DIV', {
	        props: {
	          className: 'calendar-busy-users-content-wrap'
	        },
	        html: '<div class="calendar-busy-users-content">' + BX.util.htmlspecialchars(this.plural ? main_core.Loc.getMessage('EC_BUSY_USERS_PLURAL').replace('#USER_LIST#', userNames) : main_core.Loc.getMessage('EC_BUSY_USERS_SINGLE').replace('#USER_NAME#', params.users[0].DISPLAY_NAME)) + '</div>'
	      });
	      this.dialog = new BX.PopupWindow(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_BUSY_USERS_TITLE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'bxc-popup-window',
	        // buttons: [
	        // 	new BX.PopupWindowButtonLink({
	        // 		text: Loc.getMessage('EC_BUSY_USERS_CLOSE'),
	        // 		className: "popup-window-button-link-cancel",
	        // 		events: {click : () => {
	        // 			// if (this.calendar.editSlider)
	        // 			// 	this.calendar.editSlider.close();
	        //
	        // 			this.close();
	        // 		}
	        // 		}
	        // 	})
	        // ],
	        content: content,
	        events: {}
	      });
	      content.appendChild(new BX.PopupWindowButton({
	        text: main_core.Loc.getMessage('EC_BUSY_USERS_BACK2EDIT'),
	        events: {
	          click: function click() {
	            _this2.close();
	          }
	        }
	      }).buttonNode);
	      content.appendChild(new BX.PopupWindowButton({
	        text: this.plural ? main_core.Loc.getMessage('EC_BUSY_USERS_EXCLUDE_PLURAL') : main_core.Loc.getMessage('EC_BUSY_USERS_EXCLUDE_SINGLE'),
	        events: {
	          click: function click() {
	            _this2.emit('onSaveWithout');

	            _this2.close();
	          }
	        }
	      }).buttonNode);
	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }]);
	  return BusyUsersDialog;
	}(main_core_events.EventEmitter);

	function _templateObject2$6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<img \n\t\t\t\ttitle=\"", "\" \n\t\t\t\tclass=\"calendar-member\" \n\t\t\t\tid=\"simple_popup_", "\"\n\t\t\t\tsrc=\"", "\"\n\t\t\t>"]);

	  _templateObject2$6 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$7() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div title=\"", "\" class=\"ui-icon ", "\"><i></i></div>"]);

	  _templateObject$7 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var UserPlannerSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(UserPlannerSelector, _EventEmitter);

	  // 8
	  // 10
	  function UserPlannerSelector() {
	    var _this;

	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, UserPlannerSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserPlannerSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "zIndex", 4200);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "readOnlyMode", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "meetingNotifyValue", true);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "userSelectorDialog", null);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "attendeesEntityList", []);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "inlineEditMode", UserPlannerSelector.VIEW_MODE);

	    _this.setEventNamespace('BX.Calendar.Controls.UserPlannerSelector');

	    _this.selectorId = params.id || 'user-selector-' + Math.round(Math.random() * 10000);
	    _this.BX = calendar_util.Util.getBX();
	    _this.DOM = {
	      outerWrap: params.outerWrap,
	      wrap: params.wrap,
	      informWrap: params.informWrap,
	      informWrapText: params.informWrap.querySelector('.calendar-field-container-inform-text'),
	      moreLink: params.outerWrap.querySelector('.calendar-members-more'),
	      changeLink: params.outerWrap.querySelector('.calendar-members-change-link'),
	      attendeesLabel: params.outerWrap.querySelector('.calendar-attendees-label'),
	      attendeesList: params.outerWrap.querySelector('.calendar-attendees-list'),
	      userSelectorWrap: params.outerWrap.querySelector('.calendar-user-selector-wrap'),
	      plannerOuterWrap: params.plannerOuterWrap,
	      videocallWrap: params.outerWrap.querySelector('.calendar-videocall-wrap'),
	      hideGuestsWrap: params.hideGuestsWrap,
	      hideGuestsIcon: params.hideGuestsWrap.querySelector('.calendar-hide-members-icon-hidden')
	    };
	    _this.refreshPlanner = main_core.Runtime.debounce(_this.refreshPlannerState, 100, babelHelpers.assertThisInitialized(_this));

	    if (main_core.Type.isBoolean(params.readOnlyMode)) {
	      _this.readOnlyMode = params.readOnlyMode;
	    }

	    _this.userId = params.userId;
	    _this.type = params.type;
	    _this.ownerId = params.ownerId;
	    _this.zIndex = params.zIndex || _this.zIndex;

	    _this.create();

	    return _this;
	  }

	  babelHelpers.createClass(UserPlannerSelector, [{
	    key: "create",
	    value: function create() {
	      var _this2 = this;

	      if (this.DOM.changeLink && !this.isReadOnly()) {
	        main_core.Event.bind(this.DOM.changeLink, 'click', function () {
	          if (!_this2.userSelectorDialog) {
	            _this2.userSelectorDialog = new ui_entitySelector.Dialog({
	              targetNode: _this2.DOM.changeLink,
	              context: 'CALENDAR',
	              preselectedItems: _this2.attendeesPreselectedItems,
	              enableSearch: true,
	              zIndex: _this2.zIndex + 10,
	              events: {
	                'Item:onSelect': _this2.handleUserSelectorChanges.bind(_this2),
	                'Item:onDeselect': _this2.handleUserSelectorChanges.bind(_this2)
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
	            });
	          }

	          _this2.userSelectorDialog.show();
	        });
	      }

	      if (this.DOM.moreLink) {
	        main_core.Event.bind(this.DOM.moreLink, 'click', this.showMoreAttendeesPopup.bind(this));
	      }

	      this.planner = new calendar_planner.Planner({
	        wrap: this.DOM.plannerOuterWrap,
	        minWidth: UserPlannerSelector.PLANNER_WIDTH,
	        width: UserPlannerSelector.PLANNER_WIDTH,
	        showEntryName: false
	      });
	      main_core.Event.bind(this.DOM.informWrap, 'click', function () {
	        _this2.setInformValue(!_this2.meetingNotifyValue);

	        _this2.emit('onNotifyChange');
	      });
	      this.DOM.attendeesLabel.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));
	      this.planner.subscribe('onDateChange', function (event) {
	        _this2.emit('onDateChange', event);
	      });

	      if (this.DOM.hideGuestsWrap && !this.isReadOnly()) {
	        main_core.Event.bind(this.DOM.hideGuestsWrap, 'click', function () {
	          _this2.setHideGuestsValue(!_this2.hideGuests);
	        });
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(_ref) {
	      var _BX, _BX$Intranet;

	      var attendeesEntityList = _ref.attendeesEntityList,
	          attendees = _ref.attendees,
	          location = _ref.location,
	          notify = _ref.notify,
	          hideGuests = _ref.hideGuests,
	          viewMode = _ref.viewMode,
	          entry = _ref.entry;
	      this.attendeesEntityList = main_core.Type.isArray(attendeesEntityList) ? attendeesEntityList : [];
	      this.attendeesPreselectedItems = this.attendeesEntityList.map(function (item) {
	        return [item.entityId, item.id];
	      });
	      this.entry = entry;
	      this.entryId = this.entry.id;

	      if (this.attendeesEntityList.length > 1 && !viewMode) {
	        this.showPlanner();
	      }

	      this.setEntityList(this.attendeesEntityList);
	      this.setInformValue(notify);
	      this.setLocationValue(location);

	      if (main_core.Type.isArray(attendees)) {
	        this.displayAttendees(attendees);
	      }

	      this.refreshPlanner();

	      if (((_BX = BX) === null || _BX === void 0 ? void 0 : (_BX$Intranet = _BX.Intranet) === null || _BX$Intranet === void 0 ? void 0 : _BX$Intranet.ControlButton) && this.DOM.videocallWrap && this.entryId && this.entry.getCurrentStatus() !== false) {
	        main_core.Dom.clean(this.DOM.videocallWrap);
	        main_core.Dom.removeClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
	        this.intranetControllButton = new intranet_controlButton.ControlButton({
	          container: this.DOM.videocallWrap,
	          entityType: 'calendar_event',
	          entityId: this.entryId,
	          mainItem: 'chat',
	          entityData: {
	            dateFrom: calendar_util.Util.formatDate(this.entry.from),
	            parentId: this.entry.parentId
	          },
	          analyticsLabel: {
	            formType: 'compact'
	          }
	        });
	      } else if (this.DOM.videocallWrap) {
	        main_core.Dom.addClass(this.DOM.videocallWrap, 'calendar-videocall-hidden');
	      }

	      this.setHideGuestsValue(hideGuests);
	    }
	  }, {
	    key: "handleUserSelectorChanges",
	    value: function handleUserSelectorChanges() {
	      this.showPlanner();
	      this.setEntityList(this.userSelectorDialog.getSelectedItems().map(function (item) {
	        return {
	          entityId: item.entityId,
	          id: item.id,
	          entityType: item.entityType
	        };
	      }));
	      this.refreshPlanner();
	      this.emit('onUserCodesChange');
	    }
	  }, {
	    key: "getEntityList",
	    value: function getEntityList() {
	      return this.selectorEntityList;
	    }
	  }, {
	    key: "setEntityList",
	    value: function setEntityList(selectorEntityList) {
	      if (this.type === 'user' && this.userId !== this.ownerId) {
	        selectorEntityList.push({
	          entityId: 'user',
	          id: this.ownerId
	        });
	      } else {
	        selectorEntityList.push({
	          entityId: 'user',
	          id: this.userId
	        });
	      }

	      this.selectorEntityList = selectorEntityList;
	    }
	  }, {
	    key: "isReadOnly",
	    value: function isReadOnly() {
	      return this.readOnlyMode;
	    }
	  }, {
	    key: "getUserSelector",
	    value: function getUserSelector() {
	      return BX.UI.SelectorManager.instances[this.selectorId];
	    }
	  }, {
	    key: "showPlanner",
	    value: function showPlanner() {
	      if (!this.isPlannerDisplayed()) {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'user-selector-edit-mode');
	        this.planner.show();
	        this.planner.showLoader();
	      }
	    }
	  }, {
	    key: "checkBusyTime",
	    value: function checkBusyTime() {
	      var _this3 = this;

	      var dateTime = this.getDateTime();
	      this.runPlannerDataRequest({
	        entityList: this.getEntityList(),
	        from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	        to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	        timezone: dateTime.timezoneFrom,
	        location: this.getLocationValue(),
	        entryId: this.entryId
	      }).then(function (response) {
	        if (main_core.Type.isArray(response.data.accessibility[_this3.ownerId])) {
	          var from = _this3.getDateTime().from;

	          var to = _this3.getDateTime().to;

	          var preparedData = _this3.preparedDataAccessibility(response.data.accessibility[_this3.ownerId]);

	          var item = _this3.planner.checkTimePeriod(from, to, preparedData);

	          if (main_core.Type.isObject(item) && main_core.Type.isArray(response.data.entries)) {
	            _this3.showPlanner();

	            _this3.planner.update(response.data.entries, response.data.accessibility);

	            _this3.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);

	            _this3.planner.hideLoader();

	            _this3.displayAttendees(_this3.prepareAttendeesForDisplay(response.data.entries));
	          }
	        }
	      });
	    }
	  }, {
	    key: "prepareAttendeesForDisplay",
	    value: function prepareAttendeesForDisplay(attendees) {
	      return attendees.filter(function (item) {
	        return item.type === 'user';
	      }).map(function (item) {
	        return {
	          ID: item.id,
	          AVATAR: item.avatar,
	          DISPLAY_NAME: item.name,
	          EMAIL_USER: item.emailUser,
	          STATUS: (item.status || '').toUpperCase(),
	          URL: item.url
	        };
	      });
	    }
	  }, {
	    key: "refreshPlannerState",
	    value: function refreshPlannerState() {
	      var _this4 = this;

	      if (this.planner && this.planner.isShown()) {
	        var dateTime = this.getDateTime();
	        this.loadPlannerData({
	          entityList: this.getEntityList(),
	          from: calendar_util.Util.formatDate(dateTime.from.getTime() - calendar_util.Util.getDayLength() * 3),
	          to: calendar_util.Util.formatDate(dateTime.to.getTime() + calendar_util.Util.getDayLength() * 10),
	          timezone: dateTime.timezoneFrom,
	          location: this.getLocationValue(),
	          entryId: this.entryId
	        }).then(function (response) {
	          _this4.displayAttendees(_this4.prepareAttendeesForDisplay(response.data.entries || []));
	        });
	      }
	    }
	  }, {
	    key: "loadPlannerData",
	    value: function loadPlannerData() {
	      var _this5 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      this.planner.showLoader();
	      return new Promise(function (resolve) {
	        _this5.runPlannerDataRequest(params).then(function (response) {
	          _this5.planner.hideLoader();

	          var dateTime = _this5.getDateTime();

	          _this5.planner.update(response.data.entries, response.data.accessibility);

	          _this5.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);

	          resolve(response);
	        }, function (response) {
	          resolve(response);
	        });
	      });
	    }
	  }, {
	    key: "runPlannerDataRequest",
	    value: function runPlannerDataRequest(params) {
	      return this.BX.ajax.runAction('calendar.api.calendarajax.updatePlanner', {
	        data: {
	          entryId: params.entryId || 0,
	          ownerId: this.ownerId,
	          type: this.type,
	          entityList: params.entityList || [],
	          dateFrom: params.from || '',
	          dateTo: params.to || '',
	          timezone: params.timezone || '',
	          location: params.location || '',
	          entries: params.entrieIds || false
	        }
	      });
	    }
	  }, {
	    key: "setDateTime",
	    value: function setDateTime(dateTime) {
	      var updatePlaner = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      this.dateTime = dateTime;

	      if (this.planner && updatePlaner) {
	        this.planner.updateSelector(dateTime.from, dateTime.to, dateTime.fullDay);
	      }
	    }
	  }, {
	    key: "getDateTime",
	    value: function getDateTime() {
	      return this.dateTime;
	    }
	  }, {
	    key: "setLocationValue",
	    value: function setLocationValue(location) {
	      this.location = location;
	    }
	  }, {
	    key: "getLocationValue",
	    value: function getLocationValue() {
	      return this.location;
	    }
	  }, {
	    key: "displayAttendees",
	    value: function displayAttendees() {
	      var attendees = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      main_core.Dom.clean(this.DOM.attendeesList);
	      this.attendeeList = {
	        accepted: attendees.filter(function (user) {
	          return ['H', 'Y'].includes(user.STATUS);
	        }),
	        requested: attendees.filter(function (user) {
	          return user.STATUS === 'Q' || user.STATUS === '';
	        }),
	        declined: attendees.filter(function (user) {
	          return user.STATUS === 'N';
	        })
	      };
	      var userLength = this.attendeeList.accepted.length;

	      if (userLength > 0) {
	        if (userLength > UserPlannerSelector.MAX_USER_COUNT_DISPLAY) {
	          userLength = UserPlannerSelector.MAX_USER_COUNT;
	        }

	        for (var i = 0; i < userLength; i++) {
	          this.attendeeList.accepted[i].shown = true;
	          this.DOM.attendeesList.appendChild(UserPlannerSelector.getUserAvatarNode(this.attendeeList.accepted[i]));
	        }
	      }

	      if (userLength > 1) {
	        this.DOM.attendeesLabel.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_LABEL_NUM')).replace('#COUNT#', "<span>(</span>".concat(this.attendeeList.accepted.length, "<span>)</span>"));
	      } else {
	        this.DOM.attendeesLabel.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_LABEL_ONE'));
	      }

	      if (userLength < attendees.length) {
	        if (userLength === 1) {
	          this.DOM.moreLink.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_ALL_COUNT').replace('#COUNT#', attendees.length));
	        } else {
	          this.DOM.moreLink.innerHTML = main_core.Text.encode(main_core.Loc.getMessage('EC_ATTENDEES_ALL'));
	        }

	        main_core.Dom.show(this.DOM.moreLink);
	      } else {
	        main_core.Dom.hide(this.DOM.moreLink);
	      }

	      if (this.hasExternalEmailUsers(attendees) && this.isPlannerDisplayed() && !this.isReadOnly()) {
	        this.showHideGuestsOption();
	      } else {
	        this.hideHideGuestsOption();
	      }
	    }
	  }, {
	    key: "showMoreAttendeesPopup",
	    value: function showMoreAttendeesPopup() {
	      var _this6 = this;

	      if (this.morePopup) {
	        this.morePopup.destroy();
	      }

	      var submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label';
	      var menuItems = [];
	      [{
	        code: 'accepted',
	        // Accepted
	        title: main_core.Loc.getMessage('EC_ATTENDEES_Y_NUM')
	      }, {
	        code: 'requested',
	        // Still thinking about
	        title: main_core.Loc.getMessage('EC_ATTENDEES_Q_NUM')
	      }, {
	        code: 'declined',
	        // Declined
	        title: main_core.Loc.getMessage('EC_ATTENDEES_N_NUM')
	      }].forEach(function (group) {
	        var groupUsers = _this6.attendeeList[group.code];

	        if (groupUsers.length > 0) {
	          menuItems.push({
	            html: '<span>' + group.title.replace('#COUNT#', groupUsers.length) + '</span>',
	            className: submenuClass
	          });
	          groupUsers.forEach(function (user) {
	            user.toString = function () {
	              return user.ID;
	            };

	            menuItems.push({
	              text: BX.util.htmlspecialchars(user.DISPLAY_NAME),
	              dataset: {
	                user: user
	              },
	              className: 'calendar-add-popup-user-menu-item',
	              onclick: function onclick() {
	                if (!user.EMAIL_USER) {
	                  BX.SidePanel.Instance.open(user.URL, {
	                    loader: "intranet:profile",
	                    cacheable: false,
	                    allowChangeHistory: false,
	                    contentClassName: "bitrix24-profile-slider-content",
	                    width: 1100
	                  });

	                  _this6.morePopup.close();
	                }
	              }
	            });
	          });
	        }
	      });
	      this.morePopup = main_popup.MenuManager.create('compact-event-form-attendees' + Math.round(Math.random() * 100000), this.DOM.moreLink, menuItems, {
	        closeByEsc: true,
	        autoHide: true,
	        zIndex: this.zIndex,
	        offsetTop: 0,
	        offsetLeft: 15,
	        angle: true,
	        cacheable: false,
	        className: 'calendar-popup-user-menu'
	      });
	      this.morePopup.show();
	      this.morePopup.menuItems.forEach(function (item) {
	        var icon = item.layout.item.querySelector('.menu-popup-item-icon');

	        if (main_core.Type.isPlainObject(item.dataset)) {
	          icon.appendChild(UserPlannerSelector.getUserAvatarNode(item.dataset.user));
	        }
	      });
	    }
	  }, {
	    key: "setInformValue",
	    value: function setInformValue(value) {
	      if (main_core.Type.isBoolean(value)) {
	        var DISABLED_CLASS = 'calendar-field-container-inform-off';
	        this.meetingNotifyValue = value;

	        if (this.meetingNotifyValue) {
	          main_core.Dom.removeClass(this.DOM.informWrap, DISABLED_CLASS);
	          this.DOM.informWrap.title = main_core.Loc.getMessage('EC_NOTIFY_OPTION_ON_TITLE');
	          this.DOM.informWrapText.innerHTML = main_core.Loc.getMessage('EC_NOTIFY_OPTION');
	        } else {
	          main_core.Dom.addClass(this.DOM.informWrap, DISABLED_CLASS);
	          this.DOM.informWrap.title = main_core.Loc.getMessage('EC_NOTIFY_OPTION_OFF_TITLE');
	          this.DOM.informWrapText.innerHTML = main_core.Loc.getMessage('EC_DONT_NOTIFY_OPTION');
	        }
	      }
	    }
	  }, {
	    key: "getInformValue",
	    value: function getInformValue(value) {
	      return this.meetingNotifyValue;
	    }
	  }, {
	    key: "setViewMode",
	    value: function setViewMode(readOnlyMode) {
	      this.readOnlyMode = readOnlyMode;

	      if (this.readOnlyMode) {
	        main_core.Dom.addClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
	      } else {
	        main_core.Dom.removeClass(this.DOM.outerWrap, 'calendar-userselector-readonly');
	      }
	    }
	  }, {
	    key: "isPlannerDisplayed",
	    value: function isPlannerDisplayed() {
	      return this.planner.isShown();
	    }
	  }, {
	    key: "hasExternalEmailUsers",
	    value: function hasExternalEmailUsers() {
	      var attendees = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      return !!attendees.find(function (item) {
	        return item.EMAIL_USER;
	      }) || !!this.getEntityList().find(function (item) {
	        return item.entityType === 'email';
	      });
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.userSelectorDialog && this.userSelectorDialog.destroy) {
	        this.userSelectorDialog.destroy();
	        this.userSelectorDialog = null;
	      }

	      if (this.intranetControllButton && this.intranetControllButton.destroy) {
	        this.intranetControllButton.destroy();
	        this.intranetControllButton = null;
	      }
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

	      if (main_core.Type.isElementNode(this.DOM.hideGuestsIcon)) {
	        this.DOM.hideGuestsIcon.className = this.hideGuests ? 'calendar-hide-members-icon-hidden' : 'calendar-hide-members-icon-visible';
	      }

	      var hideGuestsText = this.DOM.hideGuestsWrap.querySelector('.calendar-hide-members-text');

	      if (main_core.Type.isElementNode(hideGuestsText)) {
	        hideGuestsText.innerHTML = this.hideGuests ? main_core.Loc.getMessage('EC_HIDE_GUEST_NAMES') : main_core.Loc.getMessage('EC_SHOW_GUEST_NAMES');
	      }
	    }
	  }, {
	    key: "preparedDataAccessibility",
	    value: function preparedDataAccessibility(calendarEventsAccessibility) {
	      return calendarEventsAccessibility.map(function (item) {
	        return calendar_planner.Planner.prepareAccessibilityItem(item);
	      });
	    }
	  }], [{
	    key: "getUserAvatarNode",
	    value: function getUserAvatarNode(user) {
	      var imageNode,
	          img = user.AVATAR || user.SMALL_AVATAR;

	      if (!img || img === "/bitrix/images/1.gif") {
	        imageNode = main_core.Tag.render(_templateObject$7(), main_core.Text.encode(user.DISPLAY_NAME), user.EMAIL_USER ? 'ui-icon-common-user-mail' : 'ui-icon-common-user');
	      } else {
	        imageNode = main_core.Tag.render(_templateObject2$6(), main_core.Text.encode(user.DISPLAY_NAME), parseInt(user.ID), img);
	      }

	      return imageNode;
	    }
	  }]);
	  return UserPlannerSelector;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(UserPlannerSelector, "VIEW_MODE", 'view');
	babelHelpers.defineProperty(UserPlannerSelector, "EDIT_MODE", 'edit');
	babelHelpers.defineProperty(UserPlannerSelector, "MAX_USER_COUNT", 8);
	babelHelpers.defineProperty(UserPlannerSelector, "MAX_USER_COUNT_DISPLAY", 10);
	babelHelpers.defineProperty(UserPlannerSelector, "PLANNER_WIDTH", 450);

	var ReinviteUserDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ReinviteUserDialog, _EventEmitter);

	  function ReinviteUserDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, ReinviteUserDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ReinviteUserDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ReinviteUserDialog');

	    _this.zIndex = 3200;
	    _this.id = 'reinvite-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(ReinviteUserDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var content = main_core.Dom.create('DIV');
	      this.dialog = new main_popup.Popup(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        closeByEsc: true,
	        zIndex: this.zIndex,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_REINVITE_TITLE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'reinvite-popup-window',
	        content: content,
	        events: {},
	        cacheable: false
	      });
	      new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_REINVITE_YES'),
	        className: "ui-btn ui-btn-primary",
	        events: {
	          click: function click() {
	            _this2.emit('onSelect', new main_core_events.BaseEvent({
	              data: {
	                sendInvitesAgain: true
	              }
	            }));

	            _this2.close();
	          }
	        }
	      }).renderTo(content);
	      new BX.UI.Button({
	        text: main_core.Loc.getMessage('EC_REINVITE_NO'),
	        className: "ui-btn ui-btn-light-border",
	        events: {
	          click: function click() {
	            _this2.emit('onSelect', new main_core_events.BaseEvent({
	              data: {
	                sendInvitesAgain: false
	              }
	            }));

	            _this2.close();
	          }
	        }
	      }).renderTo(content);
	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }]);
	  return ReinviteUserDialog;
	}(main_core_events.EventEmitter);

	function _templateObject$8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div style=\"display: none;\"></div>"]);

	  _templateObject$8 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EmailSelectorControl = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(EmailSelectorControl, _EventEmitter);

	  function EmailSelectorControl(params) {
	    var _this;

	    babelHelpers.classCallCheck(this, EmailSelectorControl);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EmailSelectorControl).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "CONFIRM_POPUP_ID", 'add_from_email');

	    _this.setEventNamespace('BX.Calendar.Controls.EmailSelectorControl');

	    _this.DOM.select = params.selectNode;
	    _this.mailboxList = main_core.Type.isArray(params.mailboxList) ? params.mailboxList : [];
	    _this.DOM.componentWrap = _this.DOM.select.parentNode.appendChild(main_core.Tag.render(_templateObject$8()));
	    _this.allowAddNewEmail = params.allowAddNewEmail;
	    _this.checkValueDebounce = main_core.Runtime.debounce(_this.checkValue, 50, babelHelpers.assertThisInitialized(_this));

	    _this.create();

	    return _this;
	  }

	  babelHelpers.createClass(EmailSelectorControl, [{
	    key: "create",
	    value: function create() {
	      this.setSelectValues();
	      main_core.Event.bind(this.DOM.select, 'change', this.checkValueDebounce);
	      main_core.Event.bind(this.DOM.select, 'click', this.checkValueDebounce);
	    }
	  }, {
	    key: "checkValue",
	    value: function checkValue() {
	      if (this.DOM.select.value === 'add') {
	        this.showAdd();
	        this.setValue('');
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.DOM.select.value;
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (this.mailboxList.length && this.mailboxList.find(function (mailbox) {
	        return mailbox.email === value;
	      })) {
	        this.DOM.select.value = value;
	      } else {
	        this.DOM.select.value = '';
	      }

	      this.emit('onSetValue', {
	        value: this.DOM.select.value
	      });
	    }
	  }, {
	    key: "setSelectValues",
	    value: function setSelectValues() {
	      var _this2 = this;

	      main_core.Dom.clean(this.DOM.select);
	      this.DOM.select.options.add(new Option(main_core.Loc.getMessage('EC_NO_VALUE'), ''));

	      if (this.mailboxList.length) {
	        this.mailboxList.forEach(function (value) {
	          _this2.DOM.select.options.add(new Option(value.formatted, value.email));
	        }, this);
	      }

	      if (this.allowAddNewEmail) {
	        this.DOM.select.options.add(new Option(main_core.Loc.getMessage('EC_ADD_NEW'), 'add'));
	      }
	    }
	  }, {
	    key: "onClick",
	    value: function onClick(item) {
	      this.input.value = item.sender;
	      this.mailbox.textContent = item.sender;
	    }
	  }, {
	    key: "showAdd",
	    value: function showAdd() {
	      var _this3 = this;

	      if (window.BXMainMailConfirm) {
	        window.BXMainMailConfirm.showForm(this.onAdd.bind(this));
	      }

	      var mainMailConfirmPopup = main_popup.PopupManager.getPopupById(this.CONFIRM_POPUP_ID);

	      if (mainMailConfirmPopup) {
	        mainMailConfirmPopup.subscribe('onClose', function () {
	          _this3.reloadMailboxList();
	        });
	      }
	    }
	  }, {
	    key: "onAdd",
	    value: function onAdd(data) {
	      var _this4 = this;

	      this.reloadMailboxList().then(function () {
	        setTimeout(function () {
	          _this4.setValue(data.email);
	        }, 0);
	      });
	    }
	  }, {
	    key: "getMenuItem",
	    value: function getMenuItem(item) {
	      return {
	        'id': item.id,
	        'text': BX.util.htmlspecialchars(item.sender),
	        'onclick': this.onClick.bind(this, item)
	      };
	    }
	  }, {
	    key: "loadMailboxData",
	    value: function loadMailboxData() {
	      var _this5 = this;

	      return new Promise(function (resolve) {
	        BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxData').then(function (response) {
	          BX.html(_this5.DOM.componentWrap, response.data.html);
	          _this5.mailboxList = response.data.additionalParams.mailboxList;

	          _this5.checkBXMainMailConfirmLoaded(resolve);
	        });
	      });
	    }
	  }, {
	    key: "checkBXMainMailConfirmLoaded",
	    value: function checkBXMainMailConfirmLoaded(resolve) {
	      var _this6 = this;

	      if (window.BXMainMailConfirm) {
	        this.setSelectValues();
	        resolve();
	      } else {
	        setTimeout(function () {
	          _this6.checkBXMainMailConfirmLoaded(resolve);
	        }, 200);
	      }
	    }
	  }, {
	    key: "reloadMailboxList",
	    value: function reloadMailboxList() {
	      var _this7 = this;

	      return new Promise(function (resolve) {
	        BX.ajax.runAction('calendar.api.calendarajax.getAllowedMailboxList').then(function (response) {
	          _this7.mailboxList = response.data.mailboxList;

	          _this7.setSelectValues();

	          resolve();
	        });
	      });
	    }
	  }]);
	  return EmailSelectorControl;
	}(main_core_events.EventEmitter);

	function _templateObject$9() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t<div class=\"calendar-confirm-email-text\">", "</div>\n\t\t\t<div class=\"calendar-confirm-email-text\"><a class=\"calendar-confirm-email-help-link\" href=\"javascript:void(0);\">", "</a></div>\n\t\t\t<div class=\"calendar-field-block\">\n\t\t\t\t<select class=\"calendar-field calendar-field-select ui-btn ui-btn ui-btn-light-border ui-btn-clock\"></select>\n\t\t\t</div>\n\t\t</div>"]);

	  _templateObject$9 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ConfirmedEmailDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ConfirmedEmailDialog, _EventEmitter);

	  function ConfirmedEmailDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, ConfirmedEmailDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConfirmedEmailDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "Z_INDEX", 3200);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "SLIDER_Z_INDEX", 4400);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "WIDTH", 400);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.ConfirmedEmailDialog');

	    _this.id = 'confirm-email-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(ConfirmedEmailDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      this.DOM.content = main_core.Tag.render(_templateObject$9(), main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_TEXT_1'), main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_HELP_LINK'));
	      this.dialog = new main_popup.Popup(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        width: this.WIDTH,
	        closeByEsc: true,
	        zIndex: this.Z_INDEX,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_TITLE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'confirmemail-popup-window',
	        content: this.DOM.content,
	        events: {},
	        cacheable: false,
	        buttons: [new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_CONFIRMED_EMAIL_SEND'),
	          className: "ui-btn ui-btn-primary ".concat(BX.UI.Button.State.DISABLED),
	          events: {
	            click: function click() {
	              if (_this2.DOM.select.value && _this2.DOM.select.value !== 'add') {
	                var userSettings = calendar_util.Util.getUserSettings();
	                userSettings.sendFromEmail = _this2.emailSelectorControl.getValue();
	                calendar_util.Util.setUserSettings(userSettings);
	                BX.userOptions.save('calendar', 'user_settings', 'sendFromEmail', userSettings.sendFromEmail);

	                _this2.emit('onSelect', new main_core_events.BaseEvent({
	                  data: {
	                    sendFromEmail: userSettings.sendFromEmail
	                  }
	                }));

	                _this2.close();
	              }
	            }
	          }
	        }), new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
	          className: "ui-btn ui-btn-light-border",
	          events: {
	            click: this.close.bind(this)
	          }
	        })]
	      });
	      this.DOM.processButton = this.dialog.buttons[0].button;
	      this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
	      main_core.Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
	      this.DOM.select.disabled = true;
	      this.emailSelectorControl = new calendar_controls.EmailSelectorControl({
	        selectNode: this.DOM.select,
	        allowAddNewEmail: true
	      });
	      main_core.Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
	      this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));
	      this.emailSelectorControl.loadMailboxData().then(function () {
	        _this2.emailSelectorControl.setValue(calendar_util.Util.getUserSettings().sendFromEmail);

	        _this2.DOM.select.disabled = false;
	        _this2.DOM.select.className = 'calendar-field calendar-field-select';
	      });
	      this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
	      main_core.Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));
	      this.dialog.show();
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }, {
	    key: "handleSelectChanges",
	    value: function handleSelectChanges() {
	      if (this.DOM.select.value && this.DOM.select.value !== 'add') {
	        main_core.Dom.removeClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
	      } else {
	        main_core.Dom.addClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
	      }
	    }
	  }, {
	    key: "openHelpSlider",
	    value: function openHelpSlider() {
	      if (BX.Helper) {
	        BX.Helper.show("redirect=detail&code=12070142", {
	          zIndex: this.SLIDER_Z_INDEX
	        });
	      }
	    }
	  }]);
	  return ConfirmedEmailDialog;
	}(main_core_events.EventEmitter);

	function _templateObject$a() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div>\n\t\t\t<div class=\"calendar-email-limit-text\">", "</div>\n\t\t\t<div class=\"calendar-email-limit-subtext\">", "</div>\n\t\t</div>"]);

	  _templateObject$a = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var EmailLimitationDialog = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(EmailLimitationDialog, _EventEmitter);

	  function EmailLimitationDialog() {
	    var _this;

	    babelHelpers.classCallCheck(this, EmailLimitationDialog);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(EmailLimitationDialog).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "Z_INDEX", 3200);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "EXPAND_LICENSE_URL", '/settings/license_all.php');
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "WIDTH", 480);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "DOM", {});

	    _this.setEventNamespace('BX.Calendar.Controls.EmailLimitationDialog');

	    _this.id = 'email-limitation-dialog-' + Math.round(Math.random() * 10000);
	    return _this;
	  }

	  babelHelpers.createClass(EmailLimitationDialog, [{
	    key: "show",
	    value: function show() {
	      var _this2 = this;

	      var eventsAmount = calendar_util.Util.getEventWithEmailGuestAmount();
	      var limit = calendar_util.Util.isEventWithEmailGuestAllowed();

	      if (eventsAmount === 4) {
	        this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_5');
	      } else if (eventsAmount === 8) {
	        this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_9');
	      } else {
	        this.text = main_core.Loc.getMessage('EC_EMAIL_LIMIT_DENY');
	      }

	      this.subText = main_core.Loc.getMessage('EC_EMAIL_LIMIT_SUBTEXT');
	      this.DOM.content = main_core.Tag.render(_templateObject$a(), this.text, this.subText);
	      this.dialog = this.getDialogPopup();
	      this.dialog.subscribe('onClose', function () {
	        _this2.emit('onClose');
	      }); // this.DOM.processButton = this.dialog.buttons[0].button;
	      //
	      // this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
	      // Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
	      // this.DOM.select.disabled = true;
	      //
	      // this.emailSelectorControl = new EmailSelectorControl({
	      // 	selectNode: this.DOM.select,
	      // 	allowAddNewEmail: true
	      // });
	      // Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
	      // this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));
	      //
	      // this.emailSelectorControl.loadMailboxData()
	      // 	.then(()=> {
	      // 		this.emailSelectorControl.setValue(Util.getUserSettings().sendFromEmail);
	      // 		this.DOM.select.disabled = false;
	      // 		this.DOM.select.className = 'calendar-field calendar-field-select';
	      // 	});
	      //
	      // this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
	      // Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));

	      this.dialog.show();
	    }
	  }, {
	    key: "getDialogPopup",
	    value: function getDialogPopup() {
	      var _this3 = this;

	      return new main_popup.Popup(this.id, null, {
	        overlay: {
	          opacity: 10
	        },
	        autoHide: true,
	        width: this.WIDTH,
	        closeByEsc: true,
	        zIndex: this.Z_INDEX,
	        offsetLeft: 0,
	        offsetTop: 0,
	        draggable: true,
	        bindOnResize: false,
	        titleBar: main_core.Loc.getMessage('EC_EMAIL_LIMIT_TITLE'),
	        closeIcon: {
	          right: "12px",
	          top: "10px"
	        },
	        className: 'email-limit-popup',
	        content: this.DOM.content,
	        events: {},
	        cacheable: false,
	        buttons: [new BX.UI.Button({
	          text: main_core.Loc.getMessage('EC_EMAIL_LIMIT_EXPAND_PLAN'),
	          className: "ui-btn ui-btn-primary ui-btn-icon-plan",
	          events: {
	            click: function click() {
	              window.open(_this3.EXPAND_LICENSE_URL, '_blank');
	            }
	          }
	        }), new BX.UI.Button({
	          text: calendar_util.Util.isEventWithEmailGuestAllowed() ? main_core.Loc.getMessage('EC_SEC_SLIDER_CLOSE') : main_core.Loc.getMessage('EC_EMAIL_LIMIT_SAVE_WITHOUT'),
	          className: "ui-btn ui-btn-link",
	          events: {
	            click: this.close.bind(this)
	          }
	        })]
	      });
	    }
	  }, {
	    key: "close",
	    value: function close() {
	      if (this.dialog) {
	        this.dialog.close();
	      }
	    }
	  }]);
	  return EmailLimitationDialog;
	}(main_core_events.EventEmitter);

	exports.Reminder = Reminder;
	exports.Location = Location;
	exports.UserSelector = UserSelector;
	exports.ColorSelector = ColorSelector;
	exports.SectionSelector = SectionSelector;
	exports.RepeatSelector = RepeatSelector;
	exports.TimeSelector = TimeSelector;
	exports.SelectInput = SelectInput;
	exports.PopupDialog = PopupDialog;
	exports.ViewSelector = ViewSelector;
	exports.LineViewSelector = LineViewSelector;
	exports.AddButton = AddButton;
	exports.MeetingStatusControl = MeetingStatusControl;
	exports.ConfirmStatusDialog = ConfirmStatusDialog;
	exports.ConfirmEditDialog = ConfirmEditDialog;
	exports.ConfirmDeleteDialog = ConfirmDeleteDialog;
	exports.DateTimeControl = DateTimeControl;
	exports.BusyUsersDialog = BusyUsersDialog;
	exports.UserPlannerSelector = UserPlannerSelector;
	exports.ReinviteUserDialog = ReinviteUserDialog;
	exports.EmailSelectorControl = EmailSelectorControl;
	exports.ConfirmedEmailDialog = ConfirmedEmailDialog;
	exports.EmailLimitationDialog = EmailLimitationDialog;

}((this.BX.Calendar.Controls = this.BX.Calendar.Controls || {}),BX,BX.Calendar,BX.Calendar,BX.UI.EntitySelector,BX.Intranet,BX,BX.Event,BX.Main,BX.Calendar.Controls,BX.Calendar));
//# sourceMappingURL=controls.bundle.js.map
