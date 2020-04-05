;(function(window) {

	function EditSlider(calendar)
	{
		this.calendar = calendar;
		this.sliderId = "calendar:edit-entry-slider";
		this.zIndex = 3100;
		this.DOM = {};
		this.denyClose = false;

		this.formSettings = {
			pinnedFields : {}
		};
	}

	EditSlider.prototype = {
		show: function (params)
		{
			this.id = 'calendar_slider_' + Math.round(Math.random() * 1000000);
			this.editorId = this.id + '_entry_slider_editor';

			this.entry = params.entry || this.getNewEntry(params.entry || params.newEntryData);

			this.formType = params.formType || 'slider_main';
			this.formSettings = this.getSettings();

			BX.SidePanel.Instance.open(this.sliderId, {
				contentCallback: BX.delegate(this.createContent, this)
			});

			BX.bind(document, 'keydown', BX.proxy(this.keyHandler, this));
			BX.addCustomEvent("SidePanel.Slider:onClose", BX.proxy(this.hide, this));
			BX.addCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
			BX.bind(document, "click", BX.proxy(this.calendar.util.applyHacksForPopupzIndex, this.calendar.util));
			this.calendar.disableKeyHandler();
			setTimeout(BX.delegate(function(){this.calendar.disableKeyHandler();}, this), 300);
			this.opened = true;
		},

		createContent: function (slider)
		{
			var promise = new BX.Promise();

			BX.ajax.get(this.calendar.util.getActionUrl(), {
				action: 'get_edit_slider',
				event_id: this.entry.id,
				unique_id: this.id,
				form_type: this.formType,
				sessid: BX.bitrix_sessid(),
				bx_event_calendar_request: 'Y',
				reqId: Math.round(Math.random() * 1000000)
			}, BX.delegate(function (html)
			{
				if ((BX.type.isFunction(slider.isOpen) && slider.isOpen()) || slider.isOpen === true)
				{
					promise.fulfill(BX.util.trim(html));
					this.initControls();
				}
				else
				{
					if (window["BXHtmlEditor"])
					{
						var editor = window["BXHtmlEditor"].Get(this.editorId);
						if (editor)
						{
							editor.Destroy();
						}
					}
				}
			}, this));

			return promise;
		},

		close: function ()
		{
			BX.SidePanel.Instance.close();
		},

		hide: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				if (this.checkDenyClose())
				{
					event.denyAction();
				}
				else
				{
					BX.removeCustomEvent("SidePanel.Slider::onClose", BX.proxy(this.hide, this));
					if (this.attendeesSelector)
						this.attendeesSelector.closeAll();
				}
			}
		},

		destroy: function (event)
		{
			if (event && event.getSliderPage && event.getSliderPage().getUrl() === this.sliderId)
			{
				if (window.LHEPostForm && window.LHEPostForm.unsetHandler && LHEPostForm.getHandler(this.editorId))
				{
					window.LHEPostForm.unsetHandler(this.editorId);
				}

				BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{plannerId: this.plannerId}]);
				BX.removeCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
				BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
				BX.removeCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));

				BX.removeCustomEvent("SidePanel.Slider:onCloseComplete", BX.proxy(this.destroy, this));
				BX.SidePanel.Instance.destroy(this.sliderId);

				if (this.attendeesSelector)
					this.attendeesSelector.closeAll();

				this.calendar.enableKeyHandler();
				BX.unbind(document, "click", BX.proxy(this.calendar.util.applyHacksForPopupzIndex, this.calendar.util));
				this.opened = false;
			}
		},

		isOpened: function()
		{
			return this.opened;
		},

		denySliderClose: function()
		{
			this.denyClose = true;
		},

		allowSliderClose: function()
		{
			this.denyClose = false;
		},

		checkDenyClose: function()
		{
			if (BX(this.id + '_time_from_div') && BX(this.id + '_time_from_div').style.display != 'none')
				return true;

			if (BX(this.id + '_time_to_div') && BX(this.id + '_time_to_div').style.display != 'none')
				return true;

			return this.denyClose;
		},


		initControls: function ()
		{
			this.DOM.title = BX(this.id + '_title');
			this.DOM.formWrap = BX(this.id + '_form_wrap');
			this.DOM.form = BX(this.id + '_form');
			this.DOM.importanceCheckbox = BX(this.id + '_important');
			this.DOM.entryName = BX.adjust(BX(this.id + '_entry_name'), {events:{click: nameInputClick}});
			this.DOM.privateCheckbox = BX(this.id + '_private');

			BX.bind(BX(this.id + '_save'), 'click', BX.proxy(this.save, this));
			BX.bind(BX(this.id + '_close'), 'click', BX.proxy(this.close, this));
			BX(this.id + '_save_cmd').innerHTML = BX.browser.IsMac() ? '(Cmd+Enter)' : '(Ctrl+Enter)';

			this.initDateTimeControl();
			this.initReminderControl();
			this.initSectionSelector();
			this.initEditorControl();
			this.initLocationControl();
			this.initRepeatRuleControl();
			this.initAttendeesControl();
			this.initColorControl();

			if (this.entry.accessibility)
				BX(this.id + '_accessibility').value = this.entry.accessibility;
			if (this.entry.important)
				BX(this.id + '_important').checked = this.entry.important;
			if (this.entry.private)
				BX(this.id + '_private').checked = this.entry.private;

			this.DOM.mainBlock = BX(this.id + '_main_block_wrap');
			this.DOM.additionalBlockWrap = BX(this.id + '_additional_block_wrap');
			this.DOM.additionalBlock = BX(this.id + '_additional_block');
			this.DOM.pinnedNamesWrap = BX(this.id + '_additional_pinned_names');
			this.DOM.additionalSwitch = BX.adjust(BX(this.id + '_additional_switch'), {events: {click: BX.proxy(function()
			{
				if (BX.hasClass(this.DOM.additionalSwitch, 'opened'))
				{
					this.hideAdditionalBlock();
				}
				else
				{
					this.showAdditionalBlock();
				}
			}, this)}});

			BX.bind(this.DOM.formWrap, 'click', BX.delegate(function(e)
			{
				var
					target = e.target || e.srcElement;
				if (target && target.getAttribute && target.getAttribute('data-bx-fixfield'))
				{
					var fieldName = target.getAttribute('data-bx-fixfield');
					if (!this.fieldIsPinned(fieldName))
					{
						this.pinField(fieldName);
					}
					else
					{
						this.unPinField(fieldName);
					}
				}
			}, this));

			this.DOM.entryName.value = this.entry.name;

			setTimeout(BX.delegate(function(){
				this.DOM.entryName.focus();
				this.DOM.entryName.select();
			}, this), 500);

			this.checkLastItemBorder();

			var _this = this;
			function nameInputClick()
			{
				_this.DOM.entryName.select();
				BX.unbind(_this.DOM.entryName, 'click', nameInputClick);
			}
		},

		initDateTimeControl: function ()
		{
			var _this = this;
			// From-to
			this.DOM.dateTimeWrap = BX(this.id + '_datetime_container');
			this.DOM.fromDate = BX.adjust(BX(this.id + '_date_from'), {events: {click: showCalendar, change: fromDateChanged}});
			this.DOM.toDate = BX.adjust(BX(this.id + '_date_to'), {events: {click: showCalendar, change: toDateChanged}});
			this.DOM.fromTime = BX.adjust(BX(this.id + '_time_from'), {events: {click: function (){_this.showClock('time_from');}, change: fromTimeChanged}});
			this.DOM.toTime = BX.adjust(BX(this.id + '_time_to'), {events: {click: function (){_this.showClock('time_to');}}, change: toTimeChanged});
			this.DOM.fullDay = BX.adjust(BX(this.id + '_date_full_day'), {events: {click: BX.proxy(this.switchFullDay, this)}});
			this.DOM.defTimezoneWrap = BX(this.id + '_timezone_default_wrap');
			this.DOM.defTimezone = BX(this.id + '_timezone_default');

			this.DOM.fromTz = BX.adjust(BX(this.id + '_timezone_from'), {events: {change: fromTimezoneChanged}});
			this.DOM.toTz = BX.adjust(BX(this.id + '_timezone_to'), {events: {click: toTimezoneChanged}});
			this.DOM.tzButton = BX.adjust(BX(this.id + '_timezone_btn'), {events: {click: BX.proxy(this.switchTimezone, this)}});
			this.DOM.tzOuterCont = BX(this.id + '_timezone_wrap');
			this.DOM.tzCont = BX(this.id + '_timezone_inner_wrap');
			BX(this.id + '_timezone_hint').title = BX.message('EC_EVENT_TZ_HINT');
			BX(this.id + '_timezone_default_hint').title = BX.message('EC_EVENT_TZ_DEF_HINT');
			BX.bind(this.DOM.fromTz, 'change', BX.delegate(function() {
				if (this.linkFromToTz)
					this.DOM.toTz.value = this.DOM.fromTz.value;
				this.linkFromToDefaultTz = false;
			}, this));
			BX.bind(this.DOM.toTz, 'change', BX.delegate(function() {
				this.linkFromToTz = false;
				this.linkFromToDefaultTz = false;
			}, this));
			BX.bind(this.DOM.defTimezone, 'change', BX.delegate(function() {
				this.calendar.util.setUserOption('timezoneName', this.DOM.defTimezone.value);
				if (this.linkFromToDefaultTz)
					this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value;
			}, this));

			this.linkFromToTz = this.DOM.fromTz.value == this.DOM.toTz.value;
			this.linkFromToDefaultTz = this.DOM.fromTz.value == this.DOM.toTz.value && this.DOM.fromTz.value == this.DOM.defTimezone.value;

			BX.addCustomEvent("onJCClockInit", function (config)
			{
				if (config.inputId == _this.id + '_time_from' || config.inputId == _this.id + '_time_to')
				{
					JCClock.setOptions({
						"popupHeight": 250
					});
				}
			});

			function showCalendar()
			{
				BX.calendar({node: this.parentNode, field: this, bTime: false});
				_this.denySliderClose();

				if (BX.calendar.get().popup)
					BX.removeCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(_this.allowSliderClose, _this));
				BX.addCustomEvent(BX.calendar.get().popup, 'onPopupClose', BX.proxy(_this.allowSliderClose, _this));
			}

			function fromDateChanged()
			{
				if (_this._fromDateValue)
				{
					var
						fromTime = _this.calendar.util.parseTime(_this.DOM.fromTime.value),
						toTime = _this.calendar.util.parseTime(_this.DOM.toTime.value),
						from = BX.parseDate(_this.DOM.fromDate.value),
						to = BX.parseDate(_this.DOM.toDate.value);

					if (_this.DOM.fullDay.checked && _this._fromDateValue)
					{
						_this._fromDateValue.setHours(0, 0, 0);
					}
					else
					{
						if (from && fromTime)
						{
							from.setHours(fromTime.h, fromTime.m, 0);
						}

						if (to && toTime)
						{
							to.setHours(toTime.h, toTime.m, 0);
						}
					}

					if (from && _this._fromDateValue)
					{
						to = new Date(from.getTime() + ((to.getTime() - _this._fromDateValue.getTime()) || 3600000));
						if (to)
						{
							_this.DOM.toDate.value = _this.calendar.util.formatDate(to);
						}
					}
				}
				_this._fromDateValue = from;

				_this.refreshPlannerState();
			}

			function toDateChanged()
			{
				_this.refreshPlannerState();
			}

			function fromTimeChanged()
			{
				var
					fromTime = _this.calendar.util.parseTime(_this.DOM.fromTime.value),
					toTime = _this.calendar.util.parseTime(_this.DOM.toTime.value),
					fromDate = BX.parseDate(_this.DOM.fromDate.value),
					toDate = BX.parseDate(_this.DOM.toDate.value);

				if (fromDate && fromTime)
					fromDate.setHours(fromTime.h, fromTime.m, 0);
				if (toDate && toTime)
					toDate.setHours(toTime.h, toTime.m, 0);

				if (_this._fromDateValue)
				{
					var newToDate = new Date(
						_this.calendar.util.getTimeEx(fromDate) +
						_this.calendar.util.getTimeEx(toDate)
						- _this.calendar.util.getTimeEx(_this._fromDateValue)
					);
					_this.DOM.toTime.value = _this.calendar.util.formatTime(newToDate);
					_this.DOM.toDate.value = _this.calendar.util.formatDate(newToDate);
				}

				_this._fromDateValue = fromDate;
				_this.refreshPlannerState();
			}

			function toTimeChanged()
			{
				_this.refreshPlannerState();
			}

			function fromTimezoneChanged()
			{
				if (_this.linkFromToTz)
					_this.DOM.toTz.value = _this.DOM.fromTz.value;
				_this.linkFromToDefaultTz = false;
				_this.refreshPlannerState();
			}

			function toTimezoneChanged()
			{
				_this.linkFromToTz = false;
				_this.linkFromToDefaultTz = false;
				_this.refreshPlannerState();
			}

			// Default timezone
			if (this.calendar.util.getUserOption('timezoneName'))
			{
				this.DOM.defTimezone.value = this.calendar.util.getUserOption('timezoneName') || this.calendar.util.getUserOption('timezoneDefaultName');
			}
			else
			{
				this.DOM.defTimezoneWrap.style.display = '';
				this.DOM.defTimezone.value = this.calendar.util.getUserOption('timezoneDefaultName') || '';
				if (this.DOM.defTimezone.value)
				{
					this.calendar.util.setUserOption('timezoneName', this.DOM.defTimezone.value);
				}
			}

			this.DOM.fullDay.checked = this.entry.fullDay;
			this.switchFullDay();

			var from, to;
			if (this.entry.id)
			{
				from = BX.parseDate(this.entry.data.DATE_FROM);
				to = new Date(from.getTime() + (this.entry.data.DT_LENGTH - (this.entry.fullDay ? 1 : 0)) * 1000);
			}
			else
			{
				from = this.entry.from;
				to = this.entry.to;
			}

			this.DOM.fromDate.value = this.calendar.util.formatDate(from);
			this.DOM.fromTime.value = this.calendar.util.formatTime(from.getHours(), from.getMinutes());
			this.DOM.toDate.value = this.calendar.util.formatDate(to);
			this.DOM.toTime.value = this.calendar.util.formatTime(to.getHours(), to.getMinutes());

			this._fromDateValue = this.entry.from;

			this.DOM.fromTz.value = (this.entry.data && this.entry.data.TZ_FROM) ? this.entry.data.TZ_FROM : this.DOM.defTimezone.value;
			this.DOM.toTz.value = (this.entry.data && this.entry.data.TZ_TO) ? this.entry.data.TZ_TO : this.DOM.defTimezone.value;
		},

		showClock: function(id)
		{
			top['bxShowClock_' + this.id + '_' + id]();
			setTimeout(BX.delegate(function(){
				if (BX(this.id + '_' + id + '_div'))
				{
					BX.addClass(BX(this.id + '_' + id + '_div'), 'calendar-clock-wrap');
				}
			}, this), 50);
		},

		switchFullDay: function (value)
		{
			value = this.DOM.fullDay.checked;

			//if (value && this.pFromDate.value !== '' && this.pFromTime.value === '' && this.pToDate.value !== '' && this.pToTime.value === '')
			//{
			//	var dateFrom = BX.parseDate(this.pFromDate.value), dateTo = BX.parseDate(this.pToDate.value), oneDay = dateFrom.getTime() === dateTo.getTime();
			//
			//	if (dateFrom)
			//	{
			//		dateFrom.setHours(12);
			//		dateFrom.setMinutes(0);
			//		this.pFromTime.value = this.oEC.FormatTime(dateFrom);
			//	}
			//	if (dateTo)
			//	{
			//		dateTo.setHours(oneDay ? 13 : 12);
			//		dateTo.setMinutes(0);
			//		this.pToTime.value = this.oEC.FormatTime(dateTo);
			//	}
			//
			//	this.UpdateAccessibility();
			//}

			if (value && this.calendar.util.getUserOption('timezoneName')
				&& (this.DOM.fromTz.value == '' || this.DOM.toTz.value == ''))
			{
				this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value = this.calendar.util.getUserOption('timezoneName');
			}

			if (value)
			{
				BX.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
			}
			else
			{
				BX.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
			}

			this.refreshPlannerState();
		},

		switchTimezone: function()
		{
			if (BX.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse'))
			{
				BX.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
				BX.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
			}
			else
			{
				BX.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
				BX.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
			}
		},

		initReminderControl: function()
		{
			var _this = this;

			this.reminderValues = [];
			this.DOM.reminderValuesWrap = BX(this.id + '_reminder_values_wrap');
			this.DOM.reminderInputsWrap = BX(this.id + '_reminder_inputs_wrap');
			this.DOM.reminderAddButton = BX(this.id + '_reminder_add_button');

			var selectedValues = [15];
			if (this.entry && this.entry.remind)
			{
				selectedValues = this.entry.remind;
			}
			else if (this.entry.getReminders)
			{
				selectedValues = this.entry.getReminders();
			}

			this.reminder = new window.BXEventCalendar.ReminderSelector({
				id: "reminder-slider-" + this.calendar.id,
				selectedValues: selectedValues,
				values: this.calendar.util.getRemindersList(),
				valuesContainerNode: this.DOM.reminderValuesWrap,
				addButtonNode: this.DOM.reminderAddButton,
				zIndex: this.zIndex,
				changeCallack: function(values){
					_this.reminderValues = values;
					BX.cleanNode(_this.DOM.reminderInputsWrap);
					_this.reminderValues.forEach(function(value){
						_this.DOM.reminderInputsWrap.appendChild(BX.create('INPUT', {
							props: {name: 'reminder[]', type: 'hidden'},
							attrs: {value: value}}));
					});
				},
				showPopupCallBack: function()
				{
					_this.denySliderClose();
				},
				hidePopupCallBack: function()
				{
					_this.allowSliderClose();
				}
			});
		},

		initSectionSelector: function()
		{
			this.DOM.sectionWrap = BX(this.id + '_section_wrap');
			this.DOM.sectionInput = BX(this.id + '_section');

			var section = this.entry.section;
			if (!section && this.entry.sectionId)
				section = this.calendar.sectionController.getSection(this.entry.sectionId);

			this.DOM.sectionInput.value = section.id;

			this.DOM.sectionSelect = this.DOM.sectionWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-field calendar-field-select'}
			}));
			this.DOM.sectionSelectInner = this.DOM.sectionSelect.appendChild(BX.create('DIV', {
				props: {className: 'calendar-field-select-icon'},
				style: {backgroundColor : section.color}
			}));
			this.DOM.sectionSelectInnerText = this.DOM.sectionSelect.appendChild(BX.create('SPAN', {text: section.name}));

			BX.bind(this.DOM.sectionSelect, 'click', showPopup);

			var
				_this = this,
				sectionList = this.calendar.sectionController.getSectionListForEdit();

			function showPopup()
			{
				if (_this.sectionMenu && _this.sectionMenu.popupWindow && _this.sectionMenu.popupWindow.isShown())
				{
					return _this.sectionMenu.close();
				}

				var i, menuItems = [], icon;

				for (i = 0; i < sectionList.length; i++)
				{
					menuItems.push({
						id: 'bx-calendar-section-' + sectionList[i].id,
						text: BX.util.htmlspecialchars(sectionList[i].name),
						color: sectionList[i].color,
						className: 'calendar-add-popup-section-menu-item',
						onclick: (function (value)
						{
							return function ()
							{
								var section = _this.calendar.sectionController.getSection(value);
								_this.calendar.util.setUserOption('lastUsedSection', section.id);
								_this.DOM.sectionInput.value = section.id;
								_this.DOM.sectionSelectInner.style.backgroundColor = section.color;
								_this.setColor(section.color);
								_this.DOM.sectionSelectInnerText.innerHTML = BX.util.htmlspecialchars(section.name);
								_this.sectionMenu.close();
							}
						})(sectionList[i].id)
					});
				}

				_this.sectionMenu = BX.PopupMenu.create(
					"sectionMenuSlider" + _this.calendar.id,
					_this.DOM.sectionSelect,
					menuItems,
					{
						closeByEsc : true,
						autoHide : true,
						zIndex: _this.zIndex,
						offsetTop: 0,
						offsetLeft: 0
					}
				);

				_this.sectionMenu.popupWindow.setWidth(_this.DOM.sectionSelect.offsetWidth - 2);
				_this.sectionMenu.show();

				// Paint round icons for section menu
				for (i = 0; i < _this.sectionMenu.menuItems.length; i++)
				{
					if (_this.sectionMenu.menuItems[i].layout.item)
					{
						icon = _this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
						if (icon)
						{
							icon.style.backgroundColor = _this.sectionMenu.menuItems[i].color;
						}
					}
				}

				BX.addClass(_this.DOM.sectionSelect, 'active');

				BX.addCustomEvent(_this.sectionMenu.popupWindow, 'onPopupClose', function()
				{
					//_this.popup.setAutoHide(true);
					BX.removeClass(_this.DOM.sectionSelect, 'active');
					BX.PopupMenu.destroy("sectionMenuSlider" + _this.calendar.id);
				});
			}
		},

		initEditorControl: function()
		{
			if (!window["BXHtmlEditor"])
			{
				return setTimeout(BX.delegate(this.initEditorControl, this), 100);
			}

			var
				_this = this,
				editor = window["BXHtmlEditor"].Get(this.editorId);

			if (editor && editor.IsShown())
			{
				customizeHtmlEditor(editor);
			}
			else
			{
				BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function (editor)
				{
					if (editor.id == _this.editorId)
					{
						customizeHtmlEditor(editor);
					}
				});
			}

			function customizeHtmlEditor(editor)
			{
				if (editor.toolbar.controls && editor.toolbar.controls.spoiler)
				{
					BX.remove(editor.toolbar.controls.spoiler.pCont);
				}
			}

			if (this.entry && this.entry.data && this.entry.data.DESCRIPTION)
				this.descriptionValue = this.entry.data.DESCRIPTION;
			else if (this.DOM.form && this.DOM.form.desc && this.DOM.form.desc.value)
				this.descriptionValue = this.DOM.form.desc.value;
			else
				this.descriptionValue = '';
		},

		initLocationControl: function()
		{
			this.locationSelector = new window.BXEventCalendar.LocationSelector(
				this.calendar.id + '-slider-location',
				{
					inputName: 'location_text',
					wrapNode: BX(this.id + '_location_wrap'),
					onChangeCallback: BX.proxy(this.checkPlannerState, this),
					value: this.entry.location
				}, this.calendar);
		},

		initRepeatRuleControl: function()
		{
			this.DOM.rruleWrap = BX(this.id + '_rrule_wrap');
			this.DOM.endsonDateInput = BX(this.id + '_endson_date_input');
			this.DOM.endsonDateRadio = BX(this.id + '_endson_date');

			this.DOM.rruleType = BX.adjust(BX(this.id + '_rrule_type'), {
				events: {change: BX.delegate(function()
				{
					this.DOM.rruleWrap.className = 'calendar-rrule-type-' + this.DOM.rruleType.value.toLowerCase();
				}, this)}});

			BX.bind(this.DOM.endsonDateInput, 'click', BX.proxy(function()
			{
				this.DOM.endsonDateRadio.checked = 'checked';
				BX.calendar({node: this.DOM.endsonDateInput, field: this.DOM.endsonDateInput, bTime: false});
				BX.focus(this.DOM.endsonDateInput);
			}, this));


			if (this.entry && this.entry.isRecursive && this.entry.isRecursive())
			{
				var rrule = this.entry.getRrule();
				this.DOM.rruleType.value = rrule.FREQ;
				this.DOM.rruleWrap.className = 'calendar-rrule-type-' + rrule.FREQ.toLowerCase();

				BX(this.id + '_rrule_count').value = rrule.INTERVAL;
				if (rrule.COUNT)
				{
					BX(this.id + '_endson_count').checked = 'checked';
					BX(this.id + 'event-endson-count-input').value = rrule.COUNT;
				}
				else if(rrule['~UNTIL'])
				{
					BX(this.id + '_endson_date').checked = 'checked';
					BX(this.id + '_endson_date_input').value = rrule['~UNTIL'];
				}
				else
				{
					BX(this.id + '_endson_never').checked = 'checked';
				}

				if (rrule.BYDAY && typeof rrule.BYDAY == 'object')
				{
					for(var day in rrule.BYDAY)
					{
						if (rrule.BYDAY.hasOwnProperty(day))
						{
							BX(this.id + '_rrule_byday_' + day).checked = 'checked';
						}
					}
				}
			}
		},

		initAttendeesControl: function()
		{
			this.attendees = this.entry.attendees || [this.calendar.currentUser];
			this.attendeesIndex = {};
			this.attendees.forEach(function(userId){this.attendeesIndex[userId] = true;}, this);

			if (this.entry.getAttendeesCodes)
				this.attendeesCodes = this.entry.getAttendeesCodes();
			else
				this.attendeesCodes = this.entry.attendeesCodes || false;

			this.DOM.attendeesWrap = BX(this.id + '_attendees_wrap');

			this.attendeesSelector = new window.BXEventCalendar.DestinationSelector(this.calendar.id + '-slider-destination',
			{
				calendar: this.calendar,
				wrapNode: this.DOM.attendeesWrap,
				itemsSelected : this.attendeesCodes || this.calendar.util.getSocnetDestinationConfig('itemsSelected')
			});

			this.DOM.plannerWrap = BX(this.id + '_planner_wrap');
			this.DOM.attendeesTitle = BX(this.id + '_attendees_title_wrap');
			this.plannerId = this.id + '_slider_planner';

			BX.addCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
			BX.addCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
			BX.addCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.onCalendarPlannerSelectorChanged, this));
			this.checkPlannerState();

			this.DOM.moreOuterWrap = BX(this.id + '_more_outer_wrap');
			//this.DOM.moreLink = BX.adjust(BX(this.id + '_more'), {events: {click: BX.delegate(function(){BX.toggleClass(this.DOM.moreWrap, 'collapse');}, this)}});
			//this.DOM.moreWrap = BX(this.id + '_more_wrap');

			if (this.DOM.form.allow_invite)
			{
				if (this.entry.data)
					this.DOM.form.allow_invite.checked = this.entry.data.MEETING && this.entry.data.MEETING.ALLOW_INVITE;
				else
					this.DOM.form.allow_invite.checked = this.entry.allowInvite;
			}

			if (this.DOM.form.meeting_notify)
			{
				if (this.entry.data)
					this.DOM.form.meeting_notify.checked = this.entry.data.MEETING && this.entry.data.MEETING.NOTIFY;
				else
					this.DOM.form.meeting_notify.checked = this.entry.meetingNotify;
			}
		},

		initColorControl: function()
		{
			var _this = this;
			this.DOM.colorSelectorWrap = BX(this.id + '_color_selector_wrap');
			BX.bind(this.DOM.colorSelectorWrap, 'click', selectColorClick);

			this.defaultColors = this.calendar.util.getDefaultColors();
			this.colors = [];
			this.activeColor = this.entry.color;

			if (!this.activeColor && this.entry.section)
			{
				this.activeColor = this.entry.section.color;
			}

			for (var i = 0; i < this.defaultColors.length; i++)
			{
				this.colors.push({
					color: this.defaultColors[i], node: this.DOM.colorSelectorWrap.appendChild(BX.create('LI', {
						props: {className: 'calendar-field-colorpicker-color-item'},
						attrs: {'data-bx-calendar-color': this.defaultColors[i]},
						style: {backgroundColor: this.defaultColors[i]},
						html: '<span class="calendar-field-colorpicker-color"></span>'
					}))
				});
			}

			this.customColorNode = this.DOM.colorSelectorWrap.appendChild(BX.create('LI', {
				props: {className: 'calendar-field-colorpicker-color-item'},
				style: {
					backgroundColor: 'transparent',
					width: 0
				},
				html: '<span class="calendar-field-colorpicker-color"></span>'
			}));


			this.otherColorLink = this.DOM.colorSelectorWrap.appendChild(BX.create('LI', {
				props: {className: 'calendar-field-colorpicker-color-item-more'},
				html: '<span class="calendar-field-colorpicker-color-item-more-link">' + BX.message('EC_COLOR') + '</span>',
				events: {click: BX.delegate(function(){
					if (!this.colorPicker)
					{
						this.colorPicker = new BX.ColorPicker({
							bindElement: this.otherColorLink,
							onColorSelected: BX.proxy(this.setColor, this),
							popupOptions: {
								zIndex: this.zIndex,
								events: {
									onPopupClose:function(){
									}
								}
							}
						});
					}
					this.colorPicker.open();
				}, this)}
			}));

			function selectColorClick(e)
			{
				var target = _this.calendar.util.findTargetNode(e.target || e.srcElement, this.outerWrap);
				if (target && target.getAttribute)
				{
					var value = target.getAttribute('data-bx-calendar-color');
					if(value !== null)
					{
						if (_this.activeColorNode)
						{
							BX.removeClass(_this.activeColorNode, 'active');
						}

						_this.activeColorNode = target;
						_this.activeColor = value;
						BX.addClass(_this.activeColorNode, 'active');
					}
				}
			}

			this.setColor(this.activeColor);
		},

		setColor: function(color)
		{
			this.activeColor = color;

			if (this.activeColorNode)
			{
				BX.removeClass(this.activeColorNode, 'active');
			}

			if (!BX.util.in_array(this.activeColor, this.defaultColors) && this.activeColor)
			{
				this.customColorNode.style.backgroundColor = this.activeColor;
				this.customColorNode.style.width = '';

				this.activeColorNode = this.customColorNode;
				BX.addClass(this.activeColorNode, 'active');
			}

			for (i = 0; i < this.colors.length; i++)
			{
				if (this.colors[i].color == this.activeColor)
				{
					this.activeColorNode = this.colors[i].node;
					BX.addClass(this.activeColorNode, 'active');
					break;
				}
			}
		},

		save: function(params)
		{
			if (!params)
				params = {};

			var
				url = this.calendar.util.getActionUrl(),
				reqId = Math.round(Math.random() * 1000000);

			url += (url.indexOf('?') == -1) ? '?' : '&';
			url += 'action=edit_event&bx_event_calendar_request=Y&sessid=' + BX.bitrix_sessid() + '&reqId=' + reqId;
			url += '&markAction=' + (this.entry.id ? 'editEvent' : 'newEvent');
			url += '&markType=' + this.calendar.util.type;
			url += '&markRrule=' + this.DOM.rruleType.value;
			url += '&markMeeting=' + (this.isMeeting() ? 'Y' : 'N');
			url += '&markCrm=' + (this.isCrm() ? 'Y' : 'N');

			this.DOM.form.action = url;

			var
				fromTime = this.calendar.util.parseTime(this.DOM.fromTime.value),
				toTime = this.calendar.util.parseTime(this.DOM.toTime.value),
				fromDate = BX.parseDate(this.DOM.fromDate.value),
				toDate = BX.parseDate(this.DOM.toDate.value);

			if (fromDate && fromTime)
				fromDate.setHours(fromTime.h, fromTime.m, 0);
			if (toDate && toTime)
				toDate.setHours(toTime.h, toTime.m, 0);

			this.fromDate = fromDate;
			this.toDate = toDate;

			BX(this.id + '_time_from_real').value = BX.date.format(this.calendar.util.TIME_FORMAT, fromDate.getTime() / 1000);
			BX(this.id + '_time_to_real').value = BX.date.format(this.calendar.util.TIME_FORMAT, toDate.getTime() / 1000);

			if (params.recurentEventEditMode)
			{
				BX(this.id + '_event_current_date_from').value = this.calendar.util.formatDate(this.entry.from);
				BX(this.id + '_event_rec_edit_mode').value = params.recurentEventEditMode;
			}
			else
			{
				BX(this.id + '_event_current_date_from').value = '';
				BX(this.id + '_event_rec_edit_mode').value = '';
			}

			// check users accessibility
			if (params.checkBusyUsers !== false)
			{
				var busyUsers = this.getBusyUserList();
				if (busyUsers && busyUsers.length > 0)
				{
					if (!this.busyUsersDialog)
						this.busyUsersDialog = new window.BXEventCalendar.BusyUsersDialog(this.calendar);

					this.busyUsersDialog.show({
						users: busyUsers,
						saveCallback: BX.delegate(function()
						{
							var i, userIds = [];
							for (i = 0; i < busyUsers.length; i++)
							{
								userIds.push(busyUsers[i].id);
							}
							BX(this.id + '_exclude_users').value = userIds.join(',');
							params.checkBusyUsers = false;
							this.save(params);
						}, this)
					});
					return;
				}
			}

			// Location
			BX(this.id + '_location_new').value = this.locationSelector.getTextValue();
			if (this.locationSelector.getTextValue().substr(0, 5) == 'ECMR_' && this.DOM.rruleType.value !== 'NONE')
			{
				alert(BX.message('EC_RESERVE_PERIOD_WARN'));
				return false;
			}

			// Check Meeting rooms accessibility
			//if (this.Loc.NEW.substr(0, 5) == 'ECMR_' && !params.bLocationChecked)
			//{
			//	if (toDate && this.pFullDay.checked)
			//	{
			//		toDate = new Date(toDate.getTime() + 86400000 /* one day*/);
			//	}
			//
			//	if (fromDate && toDate)
			//	{
			//		this.oEC.CheckMeetingRoom(
			//			{
			//				id : this.oEvent.ID || 0,
			//				from : _this.oEC.FormatDateTime(fromDate),
			//				to : _this.oEC.FormatDateTime(toDate),
			//				location_new : this.Loc.NEW,
			//				location_old : this.Loc.OLD || false
			//			},
			//			function(check)
			//			{
			//				if (!check)
			//					return alert(EC_MESS.MRReserveErr);
			//				if (check == 'reserved')
			//					return alert(EC_MESS.MRNotReservedErr);
			//
			//				params.bLocationChecked = true;
			//				_this.SaveForm(params);
			//			}
			//		);
			//		return false;
			//	}
			//}

			BX(this.id + '_id').value = this.entry.id || 0;
			//BX(this.id + '_month').value = month + 1;
			//BX(this.id + '_year').value = year;

			// RRULE
			//if (this.RepeatCheck.checked)
			//{
			//	var FREQ = this.RepeatSelect.value;
			//
			//	if (this.RepeatDiapTo.value == EC_MESS.NoLimits)
			//		this.RepeatDiapTo.value = '';
			//
			//	if (FREQ == 'WEEKLY')
			//	{
			//		var ar = [], i;
			//		for (i = 0; i < 7; i++)
			//			if (this.RepeatWeekDaysCh[i].checked)
			//				ar.push(this.RepeatWeekDaysCh[i].value);
			//
			//		if (ar.length == 0)
			//			this.RepeatSelect.value = 'NONE';
			//		else
			//			BX('event-rrule-byday' + this.id).value = ar.join(',');
			//	}
			//}

			if (this.entry.id && this.entry.isRecursive()
				&& !params.confirmed && this.checkForSignificantChanges())
			{
				this.calendar.entryController.showConfirmEditDialog({
						params: params,
						callback: BX.delegate(this.save, this)
					});
				return false;
			}

			// Color
			var section = this.calendar.sectionController.getSection(this.DOM.sectionInput.value);
			if (section)
			{
				section.show();
				if (section.color.toLowerCase() != this.activeColor.toLowerCase())
				{
					BX(this.id + '_color').value = this.activeColor;
				}
			}

			BX.ajax.submitAjax(this.DOM.form, {
				dataType: 'json',
				method: "POST",
				onsuccess: BX.delegate(function (response)
				{
					if (params.recurentEventEditMode)
					{
						this.calendar.reload();
					}
					else
					{
						if (response)
						{
							this.calendar.entryController.handleEntriesList(response.entries);
							this.calendar.getView().displayEntries();
						}
					}
				}, this)
			});

			this.close();
		},

		getBusyUserList: function()
		{
			var i, busyUsers = [];
			if (this.plannerData)
			{
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) &&
						this.plannerData.entries[i].id &&
						this.plannerData.entries[i].status != 'h' &&
						this.plannerData.entries[i].strictStatus &&
						!this.plannerData.entries[i].currentStatus
					)
					{
						busyUsers.push(this.plannerData.entries[i]);
					}
				}
			}
			return busyUsers;
		},

		checkForSignificantChanges: function()
		{
			var res = false;

			// Name
			if (!res && this.entry.name !== this.DOM.form.name.value)
				res = true;

			// Description
			if (!res && this.descriptionValue !== this.DOM.form.desc.value)
				res = true;

			// Location
			if (!res && this.entry.data.LOCATION !== this.DOM.form.location_text.value)
				res = true;

			// Date & time
			if (!res && this.entry.isFullDay() != this.DOM.form.skip_time.checked)
				res = true;

			if (!res)
			{
				var
					from = BX.parseDate(this.entry.data.DATE_FROM),
					to = new Date(from.getTime() + (this.entry.data.DT_LENGTH - (this.entry.isFullDay() ? 1 : 0)) * 1000);

				if (Math.abs(from.getTime() - this.fromDate.getTime()) > 1000
					|| Math.abs(to.getTime() - this.toDate.getTime()) > 1000)
							res = true;

				if (!res && !this.entry.isFullDay()
					&& (this.entry.data.TZ_FROM != this.DOM.form.tz_from.value
						|| this.entry.data.TZ_TO != this.DOM.form.tz_to.value))
				{
					res = true;
				}
			}

			// Attendees
			if (!res && this.plannerData && false)
			{
				var i, attendeesInd = {}, count = 0;
				if (this.oEvent.IS_MEETING && this.oEvent['~ATTENDEES'])
				{
					for (i in this.oEvent['~ATTENDEES'])
					{
						if (this.oEvent['~ATTENDEES'].hasOwnProperty(i) && this.oEvent['~ATTENDEES'][i]['USER_ID'])
						{
							attendeesInd[this.oEvent['~ATTENDEES'][i]['USER_ID']] = true;
							count++
						}
					}
				}

				// Check if we have new attendees
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) && this.plannerData.entries[i].type == 'user' && this.plannerData.entries[i].id)
					{
						if (attendeesInd[this.plannerData.entries[i].id])
						{
							attendeesInd[this.plannerData.entries[i].id] = '+';
						}
						else
						{
							res = true;
							break;
						}
					}
				}

				// Check if we have all old attendees
				if (!res && attendeesInd)
				{
					for (i in attendeesInd)
					{
						if (attendeesInd.hasOwnProperty(i) && attendeesInd[i] !== '+')
						{
							res = true;
							break;
						}
					}
				}
			}

			// Recurtion
			//if (!res && (this.oEvent.RRULE.FREQ != this.RepeatSelect.value))
			//	res = true;
			//
			//if (!res && (this.oEvent.RRULE.INTERVAL != this.RepeatCount.value))
			//	res = true;
			//
			//if (!res && this.oEvent.RRULE.FREQ == 'WEEKLY' && this.oEvent.RRULE.BYDAY)
			//{
			//	var BYDAY = [];
			//	for (i in this.oEvent.RRULE.BYDAY)
			//	{
			//		if (this.oEvent.RRULE.BYDAY.hasOwnProperty(i))
			//		{
			//			BYDAY.push(this.oEvent.RRULE.BYDAY[i]);
			//		}
			//	}
			//	if (BYDAY.join(',') != BX('event-rrule-byday' + this.id).value)
			//		res = true;
			//}

			return res;
		},

		onCalendarPlannerSelectorChanged: function(params)
		{
			this.DOM.fromDate.value = this.calendar.util.formatDate(params.dateFrom);
			this.DOM.fromTime.value = this.calendar.util.formatTime(params.dateFrom);
			this.DOM.toDate.value = this.calendar.util.formatDate(params.dateTo);
			this.DOM.toTime.value = this.calendar.util.formatTime(params.dateTo);
		},

		//OnCalendarPlannerScaleChanged: function(params)
		//{
		//	this.updatePlanner({
		//		entrieIds: params.entrieIds,
		//		entries: params.entries,
		//		from: params.from,
		//		to: params.to,
		//		location: this.Loc && this.Loc.NEW ? this.Loc.NEW : '',
		//		roomEventId: this.Loc && this.Loc.OLD_mrevid ? parseInt(this.Loc.OLD_mrevid) : '',
		//		focusSelector: params.focusSelector === true
		//	});
		//},

		//DestroyDestinationControls: function()
		//{
		//	BX.removeCustomEvent('OnDestinationAddNewItem', BX.proxy(this.checkPlannerState, this));
		//	BX.removeCustomEvent('OnDestinationUnselect', BX.proxy(this.checkPlannerState, this));
		//	BX.removeCustomEvent('OnCalendarPlannerSelectorChanged', BX.proxy(this.OnCalendarPlannerSelectorChanged, this));
		//	BX.removeCustomEvent('OnCalendarPlannerScaleChanged', BX.proxy(this.OnCalendarPlannerScaleChanged, this));
		//	BX.removeCustomEvent('OnSetTab', BX.proxy(this.OnPlannerTabShow, this));
		//	BX.onCustomEvent('OnCalendarPlannerDoUninstall', [{plannerId: this.plannerId}]);
		//},

		checkPlannerState: function()
		{
			var
				_this = this,
				from = BX.parseDate(this.DOM.fromDate.value),
				to = BX.parseDate(_this.DOM.toDate.value),
				params = {
					codes: this.attendeesSelector.getCodes(),
					from: this.calendar.util.formatDate(from.getTime() - this.calendar.util.dayLength * 3),
					to: this.calendar.util.formatDate(to.getTime() + this.calendar.util.dayLength * 10),
					location: this.locationSelector.getTextValue(),
					focusSelector: true
				};

			this.attendeesCodes = this.attendeesSelector.getAttendeesCodes();

			this.updatePlanner(params);
		},

		updatePlanner: function(params)
		{
			if (!params)
				params = {};

			var _this = this;

			this.calendar.request({
				data: {
					action: 'update_planner',
					codes: params.codes || [],
					cur_event_id: this.entry.id || 0,
					date_from: params.dateFrom || params.from || '',
					date_to: params.dateTo || params.to || '',
					timezone: this.DOM.fromTz.value ? this.DOM.fromTz.value : this.calendar.util.getUserOption('timezoneName'),
					location: params.location || '',
					//roomEventId: params.roomEventId || '',
					entries: params.entrieIds || false,
					add_cur_user_to_list: this.calendar.util.userIsOwner() ? 'Y' : 'N'
				},
				handler: function(response)
				{
					var
						i,
						attendees = [],
						attendeesIndex = {},
						updateAttendeesControl = false,
						showPlanner = !!(params.entries || (response.entries && response.entries.length > 0));

					for (i = 0; i < response.entries.length; i++)
					{
						if (response.entries[i].type == 'user')
						{
							attendees.push({
								id: response.entries[i].id,
								name: response.entries[i].name,
								avatar: response.entries[i].avatar,
								smallAvatar: response.entries[i].smallAvatar || response.entries[i].avatar,
								url: response.entries[i].url
							});
							attendeesIndex[response.entries[i].id] = true;

							if (!_this.attendeesIndex[response.entries[i].id])
								updateAttendeesControl = true;
						}
					}

					if (!updateAttendeesControl)
					{
						for (var id in _this.attendeesIndex)
						{
							if (_this.attendeesIndex.hasOwnProperty(id) && !attendeesIndex[id])
							{
								updateAttendeesControl = true;
								break;
							}
						}
					}

					// Show first time or refresh it state
					if (showPlanner)
					{
						var refreshParams = {};

						if (params.entries)
						{
							response.entries = params.entries;
							refreshParams.scaleFrom = params.from;
							refreshParams.scaleTo = params.to;
						}

						refreshParams.loadedDataFrom = params.from;
						refreshParams.loadedDataTo = params.to;

						refreshParams.data = {
							entries: response.entries,
							accessibility: response.accessibility
						};

						refreshParams.focusSelector = params.focusSelector == undefined ? false : params.focusSelector;
						_this.refreshPlannerState(refreshParams);
					}
				}
			});
		},

		refreshPlannerState: function(params)
		{
			if (!params || typeof params !== 'object')
				params = {};

			this.plannerData = params.data;

			var
				fromDate = BX.parseDate(this.DOM.fromDate.value),
				toDate = BX.parseDate(this.DOM.toDate.value),
				fromTime = this.calendar.util.parseTime(this.DOM.fromTime.value),
				toTime = this.calendar.util.parseTime(this.DOM.toTime.value),
				fullDay = this.DOM.fullDay.checked,
				config = {},
				scaleFrom, scaleTo;

			if (!fullDay)
			{
				if (fromDate && fromTime)
					fromDate.setHours(fromTime.h, fromTime.m, 0);
				if (toDate && toTime)
					toDate.setHours(toTime.h, toTime.m, 0);
			}

			if (params.focusSelector == undefined)
				params.focusSelector = true;

			if (fromDate && toDate &&
				fromDate.getTime && toDate.getTime &&
				fromDate.getTime() <= toDate.getTime())
			{
				//if (!this.plannerIsShown() && !params.data)
				//{
				//	this.checkPlannerState();
				//}
				//else
				//{
					//// Show planner cont
					//if (params.show)
					//{
						BX.addClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
						//this.pMeetingParams.style.display = 'block';
						if (!this.plannerIsShown() && params.show)
						{
							params.focusSelector = true;
						}
					//}

					if (fullDay)
					{
						scaleFrom = new Date(fromDate.getTime());
						scaleFrom = params.scaleFrom || new Date(scaleFrom.getTime() - this.calendar.util.dayLength * 3);
						scaleTo = params.scaleTo || new Date(scaleFrom.getTime() + this.calendar.util.dayLength * 10);

						config.scaleType = '1day';
						config.scaleDateFrom = scaleFrom;
						config.scaleDateTo = scaleTo;
						config.adjustCellWidth = false;
					}
					else
					{
						config.changeFromFullDay = {
							scaleType: '1hour',
							timelineCellWidth: 40
						};
						config.shownScaleTimeFrom = parseInt(this.calendar.util.getWorkTime().start);
						config.shownScaleTimeTo = parseInt(this.calendar.util.getWorkTime().end);
					}
					config.entriesListWidth = this.DOM.attendeesTitle.offsetWidth + 16;
					config.width = this.DOM.plannerWrap.offsetWidth;

					this.DOM.moreOuterWrap.style.paddingLeft = config.entriesListWidth + 'px';

					// RRULE
					var RRULE = false;
					if (this.DOM.rruleType.value !== 'NONE' && false)
					{
						RRULE = {
							FREQ: this.RepeatSelect.value,
							INTERVAL: this.RepeatCount.value,
							UNTIL: this.RepeatDiapTo.value
						};

						if (RRULE.UNTIL == EC_MESS.NoLimits)
							RRULE.UNTIL = '';

						if (RRULE.FREQ == 'WEEKLY')
						{
							RRULE.WEEK_DAYS = [];
							for (i = 0; i < 7; i++)
							{
								if (this.RepeatWeekDaysCh[i].checked)
								{
									RRULE.WEEK_DAYS.push(this.RepeatWeekDaysCh[i].value);
								}
							}

							if (!RRULE.WEEK_DAYS.length)
							{
								RRULE = false;
							}
						}
					}

					BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
						{
							plannerId: this.plannerId,
							config: config,
							focusSelector: params.focusSelector,
							selector: {
								from: fromDate,
								to: toDate,
								fullDay: fullDay,
								RRULE: RRULE,
								animation: true,
								updateScaleLimits: true
							},
							data: params.data || false,
							loadedDataFrom: params.loadedDataFrom,
							loadedDataTo: params.loadedDataTo,
							show: true
							//show: !!params.show
						}
					]);
				//}
			}
		},

		plannerIsShown: function()
		{
			return this.DOM.plannerWrap && BX.hasClass(this.DOM.plannerWrap, 'calendar-edit-planner-wrap-shown');
		},

		RepeatSelectOnChange: function(val)
		{
			var i, BYDAY, date;

			val = val.toUpperCase();

			if (val == 'NONE')
			{
				//this.RepeatSect.style.display =  'none';
			}
			else
			{
				//this.RepeatSect.style.display =  'block';
				this.RepeatPhrase2.innerHTML = EC_MESS.DeDot; // Works only for de lang

				if (val == 'WEEKLY')
				{
					this.RepeatPhrase1.innerHTML = EC_MESS.EveryF;
					this.RepeatPhrase2.innerHTML += EC_MESS.WeekP;
					this.RepeatWeekDays.style.display = (val == 'WEEKLY') ? 'inline-block' : 'none';
					BYDAY = {};

					if (!this.RepeatWeekDaysCh)
					{
						this.RepeatWeekDaysCh = [];
						for (i = 0; i < 7; i++)
							this.RepeatWeekDaysCh[i] = BX(this.id + 'bxec_week_day_' + i);
					}

					if (this.oEvent && this.oEvent.ID && this.oEvent.RRULE && this.oEvent.RRULE.BYDAY)
					{
						BYDAY = this.oEvent.RRULE.BYDAY;
					}
					else
					{
						date = BX.parseDate(this.pFromDate.value);
						if (!date)
							date = bxGetDateFromTS(this.oEvent.DT_FROM_TS);

						if(date)
							BYDAY[this.oEC.GetWeekDayByInd(date.getDay())] = true;
					}

					for (i = 0; i < 7; i++)
						this.RepeatWeekDaysCh[i].checked = !!BYDAY[this.RepeatWeekDaysCh[i].value];
				}
				else
				{
					if (val == 'YEARLY')
						this.RepeatPhrase1.innerHTML = EC_MESS.EveryN;
					else
						this.RepeatPhrase1.innerHTML = EC_MESS.EveryM;

					if (val == 'DAILY')
						this.RepeatPhrase2.innerHTML += EC_MESS.DayP;
					else if (val == 'MONTHLY')
						this.RepeatPhrase2.innerHTML += EC_MESS.MonthP;
					else if (val == 'YEARLY')
						this.RepeatPhrase2.innerHTML += EC_MESS.YearP;

					this.RepeatWeekDays.style.display = 'none';
				}

				var bPer = this.oEvent && this.oEC.Event.IsRecursive(this.oEvent);
				this.RepeatCount.value = (!this.oEvent.ID || !bPer) ? 1 : this.oEvent.RRULE.INTERVAL;

				if (!this.oEvent.ID || !bPer)
				{
					this.RepeatEndsOnNever.checked = true;
				}
				else
				{
					if (this.oEvent.RRULE && this.oEvent.RRULE.COUNT > 0)
					{
						this.RepeatCountInp.value = parseInt(this.oEvent.RRULE.COUNT);
						this.RepeatEndsOnCount.checked = true;
					}
					else if (this.oEvent.RRULE && this.oEvent.RRULE['~UNTIL'])
					{
						this.RepeatDiapTo.value = this.oEvent.RRULE['~UNTIL'];
						this.RepeatEndsOnUntil.checked = true;
					}
					else
					{
						this.RepeatEndsOnNever.checked = true;
					}
					this.EndsOnChange();
				}
			}
		},

		getNewEntry: function(newEntryData)
		{
			if (!newEntryData)
				newEntryData = {};
			var time = this.calendar.entryController.getTimeForNewEntry(new Date());

			return {
				from: newEntryData.from || time.from,
				to: newEntryData.to || time.to,
				fullDay: false,
				name: newEntryData.name || this.calendar.entryController.getDefaultEntryName(),
				section: newEntryData.section ? this.calendar.sectionController.getSection(newEntryData.section) : this.calendar.sectionController.getCurrentSection(),
				remind: newEntryData.remind || [this.calendar.util.getUserOption('defaultReminder', 15)],
				location: newEntryData.locationValue || '',
				attendeesList: newEntryData.attendees || [],
				attendees: newEntryData.attendees,
				attendeesCodes: newEntryData.attendeesCodes,
				attendeesCodesList: newEntryData.attendeesCodesList,
				meetingNotify: newEntryData.meetingNotify,
				allowInvite:  newEntryData.allowInvite
			};
		},

		fieldIsPinned: function(fieldName)
		{
			return this.pinnedFieldsIndex[fieldName];
		},

		collectPlaceholders: function(fieldName)
		{
			this.placeHolders = {};
			this.placeHoldersAdditional = {};
			var i,
				fieldId,
				nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-additional-placeholder');

			for (i = 0; i < nodes.length; i++)
			{
				fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
				if (fieldId)
				{
					this.placeHoldersAdditional[fieldId] = nodes[i];
				}
			}

			nodes = this.DOM.formWrap.querySelectorAll('.calendar-field-placeholder');
			for (i = 0; i < nodes.length; i++)
			{
				fieldId = nodes[i].getAttribute('data-bx-block-placeholer');
				if (fieldId)
				{
					this.placeHolders[fieldId] = nodes[i];
				}
			}
		},

		pinField: function(fieldName)
		{
			if (!this.placeHolders)
			{
				this.collectPlaceholders();
			}

			var
				_this = this,
				field = this.placeHoldersAdditional[fieldName],
				newField = this.placeHolders[fieldName],
				fieldHeight = field.offsetHeight;

			field.style.height = fieldHeight + 'px';
			setTimeout(function(){
				BX.addClass(field, 'calendar-hide-field');
			}, 0);
			newField.style.height = '0';

			if (fieldName == 'description')
			{
				setTimeout(function()
				{
					var wrap = BX(_this.id + '_description_additional_wrap');
					if (wrap)
					{
						while(wrap.firstChild)
						{
							newField.appendChild(wrap.firstChild);
						}
					}
					newField.style.height = fieldHeight + 'px';
				}, 200);

				setTimeout(function(){
					BX.removeClass(field, 'calendar-hide-field');
					field.style.display = 'none';
					newField.style.height = '';
					_this.pinnedFieldsIndex[fieldName] = true;

					var editor = window["BXHtmlEditor"].Get(_this.editorId);
					if (editor)
					{
						editor.CheckAndReInit();
					}
					_this.saveSettings();
					_this.updateAdditionalBlockState();
				}, 300);
			}
			else
			{
				setTimeout(function(){
					while(field.firstChild)
					{
						newField.appendChild(field.firstChild);
					}
					newField.style.height = fieldHeight + 'px';
				}, 200);

				setTimeout(function(){
					BX.removeClass(field, 'calendar-hide-field');
					field.style.height = '';
					newField.style.height = '';
					_this.pinnedFieldsIndex[fieldName] = true;
					_this.saveSettings();
					_this.updateAdditionalBlockState();
				}, 300);
			}
		},

		unPinField: function(fieldName)
		{
			if (!this.placeHolders)
			{
				this.collectPlaceholders()
			}

			var
				_this = this,
				newField = this.placeHoldersAdditional[fieldName],
				field = this.placeHolders[fieldName],
				fieldHeight = field.offsetHeight;

			field.style.height = fieldHeight + 'px';
			setTimeout(function(){
				BX.addClass(field, 'calendar-hide-field');
			}, 0);
			newField.style.height = '0';

			if (fieldName == 'description')
			{
				setTimeout(function(){
					var wrap = BX(_this.id + '_description_additional_wrap');
					if (wrap)
					{
						while(field.firstChild)
						{
							wrap.appendChild(field.firstChild);
						}
					}

					newField.style.display = '';
					newField.style.height = fieldHeight + 'px';
				}, 200);

				setTimeout(function(){
					BX.removeClass(field, 'calendar-hide-field');
					field.style.height = '';
					newField.style.height = '';
					_this.pinnedFieldsIndex[fieldName] = false;

					var editor = window["BXHtmlEditor"].Get(_this.editorId);
					if (editor)
					{
						editor.CheckAndReInit();
					}

					_this.saveSettings();
					_this.updateAdditionalBlockState();
				}, 300);
			}
			else
			{
				setTimeout(function(){
					while(field.firstChild)
					{
						newField.appendChild(field.firstChild);
					}
					newField.style.height = fieldHeight + 'px';
				}, 200);

				setTimeout(function(){
					BX.removeClass(field, 'calendar-hide-field');
					field.style.height = '';
					newField.style.height = '';
					_this.pinnedFieldsIndex[fieldName] = false;

					_this.saveSettings();
					_this.updateAdditionalBlockState();
				}, 300);
			}
		},

		getSettings: function()
		{
			this.pinnedFieldsIndex = {};
			var
				i, pinnedFields = [],
				settings = this.calendar.util.getFormSettings(this.formType);

			for (i in settings.pinnedFields)
			{
				if (settings.pinnedFields.hasOwnProperty(i))
				{
					pinnedFields.push(settings.pinnedFields[i]);
					this.pinnedFieldsIndex[settings.pinnedFields[i]] = true;
				}
			}
			settings.pinnedFields = pinnedFields;
			return settings;
		},

		saveSettings: function()
		{
			var fieldName, pinnedFields = [];
			for (fieldName in this.pinnedFieldsIndex)
			{
				if (this.pinnedFieldsIndex.hasOwnProperty(fieldName) && this.pinnedFieldsIndex[fieldName])
				{
					pinnedFields.push(fieldName);
				}
			}
			this.formSettings.pinnedFields = pinnedFields;
			this.calendar.util.saveFormSettings(this.formType, this.formSettings);
		},

		updateAdditionalBlockState: function()
		{
			setTimeout(BX.delegate(function()
			{
				var i, names = this.DOM.additionalBlock.getElementsByClassName('js-calendar-field-name');
				BX.cleanNode(this.DOM.pinnedNamesWrap);
				for (i = 0; i < names.length; i++)
				{
					this.DOM.pinnedNamesWrap.appendChild(BX.create("SPAN", {props: {className: 'calendar-additional-alt-promo-text'}, html: names[i].innerHTML}));
				}

				if (!names.length)
				{
					BX.addClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
				}
				else if (BX.hasClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden'))
				{
					BX.removeClass(this.DOM.additionalBlockWrap, 'calendar-additional-block-hidden');
				}
				this.checkLastItemBorder();
			},this), 300);
		},

		showAdditionalBlock: function()
		{
			BX.addClass(this.DOM.additionalSwitch, 'opened');
			BX.removeClass(this.DOM.additionalBlock, 'invisible');
		},

		hideAdditionalBlock: function()
		{
			BX.removeClass(this.DOM.additionalSwitch, 'opened');
			BX.addClass(this.DOM.additionalBlock, 'invisible');
		},

		keyHandler: function(e)
		{
			if((e.ctrlKey || e.metaKey) && !e.altKey && e.keyCode == this.calendar.util.KEY_CODES['enter'])
			{
				this.save();
			}
		},

		checkLastItemBorder: function()
		{
			var
				noBorderClass = 'no-border',
				i, nodes;

			nodes = this.DOM.mainBlock.querySelectorAll('.calendar-options-item-border');
			for (i = 0; i < nodes.length; i++)
			{
				if (i == nodes.length - 1)
				{
					BX.addClass(nodes[i], noBorderClass);
				}
				else
				{
					BX.removeClass(nodes[i], noBorderClass);
				}
			}

			nodes = this.DOM.additionalBlock.querySelectorAll('.calendar-options-item-border');
			for (i = 0; i < nodes.length; i++)
			{
				if (i == nodes.length - 1)
				{
					BX.addClass(nodes[i], noBorderClass);
				}
				else
				{
					BX.removeClass(nodes[i], noBorderClass);
				}
			}
		},

		isMeeting: function()
		{
			var code, n = 0;
			if (this.attendeesCodes)
			{
				for (code in this.attendeesCodes)
				{
					if (this.attendeesCodes.hasOwnProperty(code))
					{
						if (this.attendeesCodes[code] != 'users' || n > 0)
						{
							return true;
						}
						n++;
					}
				}
			}
			return false;
		},

		isCrm: function()
		{
			return this.DOM.form['UF_CRM_CAL_EVENT[]'] && (this.DOM.form['UF_CRM_CAL_EVENT[]'].length > 1
			|| this.DOM.form['UF_CRM_CAL_EVENT[]'].value);
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.EditEntrySlider = EditSlider;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.EditEntrySlider = EditSlider;
		});
	}
})(window);