;(function(window){
	window.EditEventManager = function(config)
	{
		this.config = config;
		this.id = this.config.id;
		this.bAMPM = this.config.bAMPM;
		this.dayLength = 86400000;
		this.plannerId = 'calendarLiveFeedPlanner';
		this.ajaxAction = '/bitrix/components/bitrix/calendar.livefeed.edit/ajax_action.php';

		this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
		this.DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));
		if ((this.DATETIME_FORMAT.substr(0, this.DATE_FORMAT.length) == this.DATE_FORMAT))
			this.TIME_FORMAT = BX.util.trim(this.DATETIME_FORMAT.substr(this.DATE_FORMAT.length));
		else
			this.TIME_FORMAT = BX.date.convertBitrixFormat(this.bAMPM ? 'H:MI:SS T' : 'HH:MI:SS');
		this.TIME_FORMAT_SHORT = this.TIME_FORMAT.replace(':s', '');

		this.bFullDay = false;
		this.bReminder = false;
		this.bAdditional = false;
		this.locationName = 'EVENT_LOCATION';

		var _this = this;

		BX.addCustomEvent('onCalendarLiveFeedShown', function()
		{
			_this.Init();

			_this.defaultValues = {
				remind: {count: 15, type: 'min'}
			};

			_this.config.arEvent = _this.HandleEvent(_this.config.arEvent);
			_this.ShowFormData(_this.config.arEvent);
		});
	};

	window.EditEventManager.prototype = {
		Init: function()
		{
			var _this = this;
			// From-to
			this.pFromToCont = BX('feed-cal-from-to-cont' + this.id);
			this.pFromDate = BX('feed-cal-event-from' + this.id);
			this.pToDate = BX('feed-cal-event-to' + this.id);
			this.pFromTime = BX('feed_cal_event_from_time' + this.id);
			this.pToTime = BX('feed_cal_event_to_time' + this.id);
			this.pFullDay = BX('event-full-day' + this.id);

			// Timezones controls
			this.pDefTimezone = BX('feed-cal-tz-def' + this.id);
			this.pDefTimezoneWrap = BX('feed-cal-tz-def-wrap' + this.id);
			this.pFromTz = BX('feed-cal-tz-from' + this.id);
			this.pToTz = BX('feed-cal-tz-to' + this.id);
			this.pDefTimezone.onchange = BX.proxy(this.DefaultTimezoneOnChange, this);

			this.pTzOuterCont = BX('feed-cal-tz-cont-outer' + this.id);
			this.pTzSwitch = BX('feed-cal-tz-switch' + this.id);
			this.pTzCont = BX('feed-cal-tz-cont' + this.id);
			this.pTzInnerCont = BX('feed-cal-tz-inner-cont' + this.id);
			this.pTzSwitch.onclick = BX.proxy(this.TimezoneSwitch, this);

			this.pFromTz.onchange = BX.proxy(this.TimezoneFromOnChange, this);
			this.pToTz.onchange = BX.proxy(this.TimezoneToOnChange, this);

			// Hints for dialog
			new BX.CHint({parent: BX('feed-cal-tz-tip' + this.id), hint: _this.config.message.eventTzHint});
			new BX.CHint({parent: BX('feed-cal-tz-def-tip' + this.id), hint: _this.config.message.eventTzDefHint});

			//Reminder
			this.pReminderCont = BX('feed-cal-reminder-cont' + this.id);
			this.pReminder = BX('event-reminder' + this.id);

			this.pEventName = BX('feed-cal-event-name' + this.id);
			this.pForm = this.pEventName.form;
			this.pLocation = BX('event-location' + this.id);
			this.pImportance = BX('event-importance' + this.id);
			this.pAccessibility = BX('event-accessibility' + this.id);
			this.pSection = BX('event-section' + this.id);
			this.pRemCount = BX('event-remind_count' + this.id);
			this.pRemType = BX('event-remind_type' + this.id);

			// Planner
			this.pPlannerBlock = BX('event-planner-block' + this.id);
			this.pPlannerTitle = BX('event-planner-block-title' + this.id);
			this.pPlannerLinkWrap = BX('event-planner-expand-link-wrap' + this.id);
			BX.bind(this.pPlannerBlock, 'click', BX.proxy(this.ExpandPlanner, this));
			this.pPlannerProposeLink = BX('event-planner-propose-link' + this.id);
			BX.bind(this.pPlannerProposeLink, 'click', BX.proxy(this.ProposeTime, this));

			// Location
			if (this.config.meetingRooms)
 			{
				this.Location = new BXInputPopup({
					id: this.id + '_loc_mr',
					values: this.config.meetingRooms,
					input: this.pLocation,
					defaultValue: this.config.message.SelectMR,
					openTitle: this.config.message.OpenMRPage,
					className: 'calendar-inp calendar-inp-time calendar-inp-loc',
					noMRclassName: 'calendar-inp calendar-inp-time calendar-inp-loc'
				});
				this.Loc = {};
				BX.addCustomEvent(this.Location, 'onInputPopupChanged', BX.proxy(this.LocationOnChange, this));
				BX.addClass(this.pLocation, "calendar-inp-time");
				this.Location.Set(false, '');
			}

			// Control events
			this.pFullDay.onclick = BX.proxy(this.FullDay, this);
			this.pReminder.onclick = BX.proxy(this.Reminder, this);

			BX.bind(this.pFullDay, 'click', BX.proxy(this.RefreshPlannerState, this));

			BX.bind(this.pForm, 'submit', BX.proxy(this.OnSubmit, this));
			// *************** Init events ***************

			BX("feed-cal-additional-show").onclick = BX("feed-cal-additional-hide").onclick = BX.proxy(this.ShowAdditionalParams, this);

			this.InitDateTimeControls();

			var oEditor = window["BXHtmlEditor"].Get(this.config.editorId);
			if (oEditor && oEditor.IsShown())
			{
				this.CustomizeHtmlEditor(oEditor);
			}
			else
			{
				BX.addCustomEvent(window["BXHtmlEditor"], 'OnEditorCreated', function(editor)
				{
					if (editor.id == _this.config.editorId)
					{
						_this.CustomizeHtmlEditor(editor);
					}
				});
			}

			// repeat
			this.pRepeat = BX('event-repeat' + this.id);
			this.pRepeatDetails = BX('event-repeat-details' + this.id);

			this.RepeatEndsOnNever = BX(this.id + 'event-endson-never');
			this.RepeatEndsOnCount = BX(this.id + 'event-endson-count');
			this.RepeatEndsOnUntil = BX(this.id + 'event-endson-until');
			this.RepeatDiapTo = BX('event-repeat-to' + this.id);
			this.RepeatCountInp = BX(this.id + 'event-endson-count-input');

			this.pRepeat.onchange = function()
			{
				var value = this.value;
				_this.pRepeatDetails.className = "feed-cal-repeat-details feed-cal-repeat-details-" + value.toLowerCase();
			};
			this.pRepeat.onchange();

			BX.bind(this.RepeatEndsOnNever, 'change', BX.proxy(this.EndsOnChange, this));
			BX.bind(this.RepeatEndsOnCount, 'change', BX.proxy(this.EndsOnChange, this));
			BX.bind(this.RepeatEndsOnUntil, 'change', BX.proxy(this.EndsOnChange, this));

			BX.bind(this.RepeatDiapTo, 'click', BX.proxy(function()
			{
				this.RepeatEndsOnUntil.checked = 'checked';
				BX.calendar({node: this.RepeatDiapTo, field: this.RepeatDiapTo, bTime: false});
				BX.focus(this.RepeatDiapTo);
				this.EndsOnChange();
			}, this));

			BX.bind(this.RepeatCountInp, 'click', BX.proxy(function()
			{
				this.RepeatEndsOnCount.checked = 'checked';
				BX.focus(this.RepeatCountInp);
				this.EndsOnChange();
			}, this));

			this.eventNode = BX('div' + this.config.editorId);
			if (this.eventNode)
			{
				BX.onCustomEvent(this.eventNode, 'OnShowLHE', ['justShow']);
			}

			BX.addCustomEvent('OnDestinationLivefeedChanged', BX.proxy(this.CheckPlannerState, this));

			// planner events
			BX.addCustomEvent('OnCalendarPlannerSelectorChanged', function(params)
			{
				_this.pFromDate.value = _this.FormatDate(params.dateFrom);
				_this.pFromTime.value = _this.FormatTime(params.dateFrom);
				_this.pToDate.value = _this.FormatDate(params.dateTo);
				_this.pToTime.value = _this.FormatTime(params.dateTo);

				_this.pFullDay.checked = params.fullDay;
				_this.FullDay(false, !params.fullDay);
			});

			BX.addCustomEvent('OnCalendarPlannerScaleChanged', function(params)
			{
				_this.UpdatePlanner({
					entrieIds: params.entrieIds,
					entries: params.entries,
					from: params.from,
					to: params.to,
					location: _this.Loc ? _this.Loc.NEW : _this.pLocation.value,
					focusSelector: params.focusSelector === true,
					params: params.params
				});
			});

			setTimeout(function(){BX.bind(window, "resize", BX.proxy(_this.OnResize, _this))},200);
		},

		EndsOnChange: function()
		{
			if (this.RepeatEndsOnNever.checked)
			{
				this.RepeatCountInp.value = '';
				this.RepeatDiapTo.value = '';
			}
			else if (this.RepeatEndsOnCount.checked)
			{
				this.RepeatDiapTo.value = '';
				if (!this.RepeatCountInp.value)
					this.RepeatCountInp.value = this.RepeatCountInp.placeholder;
				BX.focus(this.RepeatCountInp);
				this.RepeatCountInp.select();
			}
			else
			{
				this.RepeatCountInp.value = '';
				BX.focus(this.RepeatDiapTo);
				this.RepeatDiapTo.select();
			}
		},

		OnResize: function()
		{
			var plannerShown = this.pPlannerBlock && BX.hasClass(this.pPlannerBlock, 'feed-event-planner-block-shown');
			if (plannerShown)
			{
				var plannerBlockWidth = this.pPlannerBlock.offsetWidth - this.pPlannerTitle.offsetWidth - this.pPlannerLinkWrap.offsetWidth - 80;

				// Update scale type only for simple view
				if (this.pPlannerTitle.offsetWidth > 0)
				{
					var scale = '15min';
					if (plannerBlockWidth < 800)
						scale = '30min';
					if (plannerBlockWidth < 500)
						scale = '1hour';
					if (plannerBlockWidth < 400)
						scale = '2hour';
					BX.onCustomEvent('OnCalendarPlannerDoSetConfig', [
						{
							plannerId: this.plannerId,
							config: {
								scaleType : scale
							}
						}
					]);
				}

				BX.onCustomEvent('OnCalendarPlannerDoResize', [
					{
						plannerId: this.plannerId,
						timeoutCheck: true,
						width: plannerBlockWidth
					}
				]);
			}
		},

		CustomizeHtmlEditor: function(editor)
		{
			if (editor.toolbar.controls && editor.toolbar.controls.spoiler)
			{
				BX.remove(editor.toolbar.controls.spoiler.pCont);
			}
		},

		InitDateTimeControls: function()
		{
			var _this = this;
			// Date
			this.pFromDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};
			this.pToDate.onclick = function(){BX.calendar({node: this.parentNode, field: this, bTime: false});};

			this.pFromDate.onchange = function()
			{
				var prevF = _this._FromDateValue ? BX.parseDate(_this._FromDateValue) : false;
				if(prevF && prevF.getTime)
				{
					var
						F = BX.parseDate(_this.pFromDate.value),
						T = BX.parseDate(_this.pToDate.value);

					if (F && T)
					{
						var duration = T.getTime() - prevF.getTime();
						if (duration < 0)
							duration = 0;
						T = new Date(F.getTime() + duration);
						if (T)
							_this.pToDate.value = bxFormatDate(T.getDate(), T.getMonth() + 1, T.getFullYear());
					}

					_this._FromDateValue = _this.pFromDate.value;
				}
				_this.RefreshPlannerState();
			};

			// Time
			this.pFromTime.parentNode.onclick = this.pFromTime.onclick = window['bxShowClock_' + 'feed_cal_event_from_time' + this.id];
			this.pToTime.parentNode.onclick = this.pToTime.onclick = window['bxShowClock_' + 'feed_cal_event_to_time' + this.id];

			this.pFromTime.onchange = function()
			{
				var fromDate = _this.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + BX.util.trim(_this.pFromTime.value));
				if (_this.pToDate.value == '')
					_this.pToDate.value = _this.pFromDate.value;

				var toDate = _this.ParseDate(BX.util.trim(_this.pToDate.value) + ' ' + BX.util.trim(_this.pToTime.value));

				if (_this._FromTimeValue)
				{
					var prefFromDate = _this.ParseDate(BX.util.trim(_this.pFromDate.value) + ' ' + _this._FromTimeValue);
					var duration = toDate.getTime() - prefFromDate.getTime();
					if (duration < 0)
						duration = 3600000; // 1 hour

					var newToDate = new Date(fromDate.getTime() + duration);
					_this.pToDate.value = _this.FormatDate(newToDate);
					_this.pToTime.value = _this.FormatTime(newToDate);
				}

				_this._FromTimeValue = _this.pFromTime.value;
				_this.RefreshPlannerState();
			};

			BX.bind(this.pToDate, 'change', BX.proxy(this.RefreshPlannerState, this));
			BX.bind(this.pToTime, 'change', BX.proxy(this.RefreshPlannerState, this));
		},


		OnSubmit: function(e)
		{
			if (!this.CheckUserAccessibility())
			{
				alert(this.config.message.EC_BUSY_ALERT);
				setBlogPostFormSubmitted(false);
				return BX.PreventDefault(e);
			}

			var
				_this = this,
				fromTime = this.parseTime(this.pFromTime.value),
				toTime = this.parseTime(this.pToTime.value),
				fromDate = BX.parseDate(BX.util.trim(this.pFromDate.value)),
				toDate = BX.parseDate(BX.util.trim(this.pToDate.value));

			if (fromDate && fromTime)
				fromDate.setHours(fromTime.h, fromTime.m, 0);
			if (toDate && toTime)
				toDate.setHours(toTime.h, toTime.m, 0);

			BX(this.id + '_time_from_real').value = BX.date.format(this.TIME_FORMAT, fromDate.getTime() / 1000);
			BX(this.id + '_time_to_real').value = BX.date.format(this.TIME_FORMAT, toDate.getTime() / 1000);

			this.pLocation.name = this.locationName;
			// Check Meeting and Video Meeting rooms accessibility
			if (this.Loc && this.Loc.NEW && this.Loc.NEW.substr(0, 5) == 'ECMR_' && !this.bLocationChecked && window.setBlogPostFormSubmitted)
			{
				top.BXCRES_Check = null;
				this.CheckMeetingRoom(
					{
						from : this.FormatDateTime(fromDate),
						to : this.FormatDateTime(toDate),
						location : this.Loc.NEW
					},
					function()
					{
						setTimeout(function()
						{
							var check = top.BXCRES_Check;
							if ((!check || check == 'reserved') && BX("blog-submit-button-save"))
							{
								setBlogPostFormSubmitted(false);
								BX.removeClass(BX("blog-submit-button-save"), 'ui-btn-clock');
							}

							if (!check)
							{
								return alert(_this.config.message.MRReserveErr);
							}

							if (check == 'reserved')
							{
								return alert(_this.config.message.MRNotReservedErr);
							}

							_this.bLocationChecked = true;
							BX('event-location-new' + _this.id).name = _this.locationName;
							BX('event-location-new' + _this.id).value = _this.Loc.NEW;
							_this.pLocation.name = '';
							setBlogPostFormSubmitted(false);
							submitBlogPostForm();
						}, 100);
					}
				);
				return BX.PreventDefault(e);
			}
			else if (this.Loc && this.Loc.NEW != undefined && !this.bLocationChecked)
			{
				BX('event-location' + this.id).value = this.Loc.NEW;
			}
		},

		CheckUserAccessibility: function()
		{
			var i, res = true;
			if (this.plannerData)
			{
				for (i in this.plannerData.entries)
				{
					if (this.plannerData.entries.hasOwnProperty(i) &&
						this.plannerData.entries[i].id &&
						this.plannerData.entries[i].status !== 'h' &&
						parseInt(this.plannerData.entries[i].strictStatus) &&
						!this.plannerData.entries[i].currentStatus
					)
					{
						res = false;
						break;
					}
				}
			}
			return res;
		},

		HandleEvent: function(oEvent)
		{
			if(oEvent)
			{
				oEvent.DT_FROM_TS = BX.date.getBrowserTimestamp(oEvent.DT_FROM_TS);
				oEvent.DT_TO_TS = BX.date.getBrowserTimestamp(oEvent.DT_TO_TS);

				if (oEvent.DT_FROM_TS > oEvent.DT_TO_TS)
					oEvent.DT_FROM_TS = oEvent.DT_TO_TS;

				if ((oEvent.RRULE && oEvent.RRULE.FREQ && oEvent.RRULE.FREQ != 'NONE'))
				{
					oEvent['~DT_FROM_TS'] = BX.date.getBrowserTimestamp(oEvent['~DT_FROM_TS']);
					oEvent['~DT_TO_TS'] = BX.date.getBrowserTimestamp(oEvent['~DT_TO_TS']);

					if (oEvent.RRULE && oEvent.RRULE.UNTIL)
						oEvent.RRULE.UNTIL = BX.date.getBrowserTimestamp(oEvent.RRULE.UNTIL);
				}
			}
			return oEvent;
		},

		ShowFormData: function(oEvent)
		{
			var bNew = false;
			if (!oEvent || !oEvent.ID)
			{
				bNew = true;
				oEvent = {};
			}

			// Name
			this.pEventName.value = oEvent.NAME || '';

			this.linkFromToTz = true;
			this.linkFromToDefaultTz = true;

			// Default Timezone
			if (this.config.userTimezoneName)
			{
				this.pDefTimezoneWrap.style.display = 'none';
				this.pDefTimezone.value = this.config.userTimezoneName;
				this.pFromTz.value = this.pToTz.value = this.config.userTimezoneName;
			}
			else
			{
				this.pDefTimezoneWrap.style.display = '';
				this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneDefault || '';
			}

			// Dafault values for from-to fields
			var dateFrom = this.GetUsableDateTime(new Date().getTime(), 30);
			var dateTo = this.GetUsableDateTime(dateFrom.getTime() + 3600000 /* one hour*/, 30);

			this.pFromDate.value = this.FormatDate(dateFrom);
			this.pToDate.value = this.FormatDate(dateTo);
			this.pFromTime.value = this.FormatTime(dateFrom);
			this.pToTime.value = this.FormatTime(dateTo);

			this._FromDateValue = this.pFromDate.value;
			this._FromTimeValue = this.pFromTime.value;

			// Default Timezone
			if (this.config.userTimezoneName)
			{
				this.pDefTimezoneWrap.style.display = 'none';
				this.pDefTimezone.value = this.config.userTimezoneName;
				this.pFromTz.value = this.pToTz.value = this.config.userTimezoneName;
			}
			else
			{
				this.pDefTimezoneWrap.style.display = '';
				this.pFromTz.value = this.pToTz.value = this.pDefTimezone.value = this.config.userTimezoneDefault || '';
			}

			this.pFullDay.checked = oEvent.DT_SKIP_TIME == "Y";
			this.FullDay(false, oEvent.DT_SKIP_TIME !== "Y");

			if (bNew)
			{
				this.pLocation.value = '';
				if (this.Location)
				{
					this.Location.Set(false, '');
				}

				this.pImportance.value = 'normal';
				this.pAccessibility.value = 'busy';
				if (this.pSection.options && this.pSection.options.length > 0)
					this.pSection.value = this.pSection.options[0].value;

				this.pReminder.checked = !!this.defaultValues.remind;
				this.pRemCount.value = (this.defaultValues.remind && this.defaultValues.remind.count) || '15';
				this.pRemType.value = (this.defaultValues.remind && this.defaultValues.remind.type) || 'min';
			}
			else
			{
				this.pLocation.value = oEvent.LOCATION;
				this.pImportance.value = oEvent.IMPORTANCE;
				this.pAccessibility.value = oEvent.ACCESSIBILITY;
				this.pSection.value = oEvent.SECT_ID;

				// Remind
				this.pReminder.checked = oEvent.REMIND && oEvent.REMIND[0];
				this.pRemCount.value = oEvent.REMIND[0].count;
				this.pRemType.value = oEvent.REMIND[0].type;
			}
			this.Reminder(false, true);

			var _this = this;
			setTimeout(function()
			{
				BX.focus(_this.pEventName);
			}, 100);
		},

		FullDay: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bFullDay;

			if (value)
				BX.removeClass(this.pFromToCont, 'feed-cal-full-day');
			else
				BX.addClass(this.pFromToCont, 'feed-cal-full-day');
			this.bFullDay = value;
		},

		Reminder: function(bSaveOption, value)
		{
			if (value == undefined)
				value = !this.bReminder;

			this.pReminderCont.className = value ? 'feed-event-reminder' : 'feed-event-reminder-collapsed';

			this.bReminder = value;
		},

		ShowAdditionalParams: function()
		{
			var value = !this.bAdditional;
			if (!this.pAdditionalCont)
				this.pAdditionalCont = BX("feed-cal-additional");

			if (value)
				BX.removeClass(this.pAdditionalCont, 'feed-event-additional-hidden');
			else
				BX.addClass(this.pAdditionalCont, 'feed-event-additional-hidden');

			this.bAdditional = value;
		},

		parseTime: function(str)
		{
			var date = this.parseDate(BX.date.format(this.DATE_FORMAT, new Date()) + ' ' + str, false);
			return date ? {
				h: date.getHours(),
				m: date.getMinutes()
			} : date;
		},

		parseDate: function(str, format, trimSeconds)
		{
			var
				i, cnt, k,
				regMonths,
				bUTC = false;

			if (!format)
				format = BX.message('FORMAT_DATETIME');

			str = BX.util.trim(str);

			if (trimSeconds !== false)
				format = format.replace(':SS', '');

			if (BX.type.isNotEmptyString(str))
			{
				regMonths = '';
				for (i = 1; i <= 12; i++)
				{
					regMonths = regMonths + '|' + BX.message('MON_'+i);
				}

				var
					expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig'),
					aDate = str.match(expr),
					aFormat = BX.message('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
					aDateArgs = [],
					aFormatArgs = [],
					aResult = {};

				if (!aDate)
				{
					return null;
				}

				if(aDate.length > aFormat.length)
				{
					aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
				}

				for(i = 0, cnt = aDate.length; i < cnt; i++)
				{
					if(BX.util.trim(aDate[i]) != '')
					{
						aDateArgs[aDateArgs.length] = aDate[i];
					}
				}

				for(i = 0, cnt = aFormat.length; i < cnt; i++)
				{
					if(BX.util.trim(aFormat[i]) != '')
					{
						aFormatArgs[aFormatArgs.length] = aFormat[i];
					}
				}

				var m = BX.util.array_search('MMMM', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
				else
				{
					m = BX.util.array_search('M', aFormatArgs);
					if (m > 0)
					{
						aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
						aFormatArgs[m] = "MM";
					}
				}

				for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
				{
					k = aFormatArgs[i].toUpperCase();
					aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
				}

				if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
				{
					var d = new Date();

					if(bUTC)
					{
						d.setUTCDate(1);
						d.setUTCFullYear(aResult['YYYY']);
						d.setUTCMonth(aResult['MM'] - 1);
						d.setUTCDate(aResult['DD']);
						d.setUTCHours(0, 0, 0);
					}
					else
					{
						d.setDate(1);
						d.setFullYear(aResult['YYYY']);
						d.setMonth(aResult['MM'] - 1);
						d.setDate(aResult['DD']);
						d.setHours(0, 0, 0);
					}

					if(
						(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
						&& !isNaN(aResult['MI'])
					)
					{
						if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
						{
							var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
							var h = parseInt(aResult['H']||aResult['G']||0, 10);
							if(bPM)
							{
								aResult['HH'] = h + (h == 12 ? 0 : 12);
							}
							else
							{
								aResult['HH'] = h < 12 ? h : 0;
							}
						}
						else
						{
							aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
						}

						if (isNaN(aResult['SS']))
							aResult['SS'] = 0;

						if(bUTC)
						{
							d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
						else
						{
							d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
					}

					return d;
				}
			}

			return null;
		},

		TimezoneSwitch: function()
		{
			if(this.pTzCont.offsetHeight > 0)
			{
				this.pTzCont.style.height = 0;
				BX.removeClass(this.pTzOuterCont, 'feed-ev-timezone-outer-wrap-opened');
			}
			else
			{
				this.pTzCont.style.height = this.pTzInnerCont.offsetHeight + 'px';
				BX.addClass(this.pTzOuterCont, 'feed-ev-timezone-outer-wrap-opened');
			}
		},

		DefaultTimezoneOnChange: function()
		{
			var defTimezoneName = this.pDefTimezone.value;
			BX.userOptions.save('calendar', 'timezone_name', 'timezone_name', defTimezoneName);
			if (this.linkFromToDefaultTz)
				this.pToTz.value = this.pFromTz.value = this.pDefTimezone.value;
		},

		TimezoneFromOnChange: function()
		{
			if (this.linkFromToTz)
				this.pToTz.value = this.pFromTz.value;
			this.linkFromToDefaultTz = false;
			this.CheckPlannerState();
		},

		TimezoneToOnChange: function()
		{
			this.linkFromToTz = false;
			this.linkFromToDefaultTz = false;
		},

		FormatDate: function(date)
		{
			return BX.date.format(this.DATE_FORMAT, date.getTime() / 1000);
		},

		FormatTime: function(date, seconds)
		{
			return BX.date.format(seconds === true ? this.TIME_FORMAT : this.TIME_FORMAT_SHORT, date.getTime() / 1000);
		},

		FormatDateTime: function(date)
		{
			return BX.date.format(this.DATETIME_FORMAT, date.getTime() / 1000);
		},

		GetUsableDateTime: function(timestamp, roundMin)
		{
			var r = (roundMin || 10) * 60 * 1000;
			timestamp = Math.ceil(timestamp / r) * r;
			return new Date(timestamp);
		},

		ParseDate: function(str, trimSeconds)
		{
			var bUTC = false;
			var format = BX.message('FORMAT_DATETIME');

			if (trimSeconds !== false)
				format = format.replace(':SS', '');

			if (BX.type.isNotEmptyString(str))
			{
				var regMonths = '';
				for (i = 1; i <= 12; i++)
				{
					regMonths = regMonths + '|' + BX.message('MON_'+i);
				}

				var expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig');
				var aDate = str.match(expr),
					aFormat = BX.message('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
					i, cnt,
					aDateArgs=[], aFormatArgs=[],
					aResult={};

				if (!aDate)
					return null;

				if(aDate.length > aFormat.length)
				{
					aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
				}

				for(i = 0, cnt = aDate.length; i < cnt; i++)
				{
					if(BX.util.trim(aDate[i]) != '')
					{
						aDateArgs[aDateArgs.length] = aDate[i];
					}
				}

				for(i = 0, cnt = aFormat.length; i < cnt; i++)
				{
					if(BX.util.trim(aFormat[i]) != '')
					{
						aFormatArgs[aFormatArgs.length] = aFormat[i];
					}
				}

				var m = BX.util.array_search('MMMM', aFormatArgs);
				if (m > 0)
				{
					aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
					aFormatArgs[m] = "MM";
				}
				else
				{
					m = BX.util.array_search('M', aFormatArgs);
					if (m > 0)
					{
						aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
						aFormatArgs[m] = "MM";
					}
				}

				for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
				{
					var k = aFormatArgs[i].toUpperCase();
					aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
				}

				if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
				{
					var d = new Date();

					if(bUTC)
					{
						d.setUTCDate(1);
						d.setUTCFullYear(aResult['YYYY']);
						d.setUTCMonth(aResult['MM'] - 1);
						d.setUTCDate(aResult['DD']);
						d.setUTCHours(0, 0, 0);
					}
					else
					{
						d.setDate(1);
						d.setFullYear(aResult['YYYY']);
						d.setMonth(aResult['MM'] - 1);
						d.setDate(aResult['DD']);
						d.setHours(0, 0, 0);
					}

					if(
						(!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G']))
							&& !isNaN(aResult['MI'])
						)
					{
						if (!isNaN(aResult['H']) || !isNaN(aResult['G']))
						{
							var bPM = (aResult['T']||aResult['TT']||'am').toUpperCase()=='PM';
							var h = parseInt(aResult['H']||aResult['G']||0, 10);
							if(bPM)
							{
								aResult['HH'] = h + (h == 12 ? 0 : 12);
							}
							else
							{
								aResult['HH'] = h < 12 ? h : 0;
							}
						}
						else
						{
							aResult['HH'] = parseInt(aResult['HH']||aResult['GG']||0, 10);
						}

						if (isNaN(aResult['SS']))
							aResult['SS'] = 0;

						if(bUTC)
						{
							d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
						else
						{
							d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
						}
					}

					return d;
				}
			}
		},

		LocationOnChange: function(oLoc, ind, value)
		{
			this.pLocation.className = 'calendar-inp calendar-inp-time calendar-inp-loc';
			if (ind === false)
			{
				this.Loc.NEW = value || '';
			}
			else
			{
				this.Loc.NEW = 'ECMR_' + this.config.meetingRooms[ind].ID;
			}
			this.CheckPlannerState();
		},

		CheckMeetingRoom: function(params, callback)
		{
			params.bx_event_calendar_check_meeting_room = 'Y';
			params.sessid = BX.bitrix_sessid();
			BX.ajax.get(
				this.ajaxAction,
				params,
				function()
				{
					if (callback && typeof callback == 'function')
						callback();
					return true;
				}
			);
		},

		CheckPlannerState: function(timeout)
		{
			if(timeout !== false)
			{
				var _this = this;
				if (this.checkPlannerTimeout)
					this.checkPlannerTimeout = !!clearTimeout(this.checkPlannerTimeout);
				this.checkPlannerTimeout = setTimeout(function(){_this.CheckPlannerState(false)}, 300);
			}
			else if (BX('feed-event-dest-cont', true))
			{
				var
					params = {},
					fromDate = this.ParseDate(BX.util.trim(this.pFromDate.value)),
					toDate = this.ParseDate(BX.util.trim(this.pToDate.value)),
					arInputs = BX('feed-event-dest-cont', true).getElementsByTagName('INPUT'),
					i, arCodes = [];

				for (i = 0; i < arInputs.length; i++)
				{
					arCodes.push(arInputs[i].value);
				}

				if (fromDate && toDate && fromDate.getTime && toDate.getTime && fromDate.getTime() <= toDate.getTime() && (params.location || arCodes.length > 0))
				{
					params.codes = arCodes;
					params.from = BX.date.format(this.DATE_FORMAT, (fromDate.getTime() - this.dayLength * 3) / 1000);
					params.to = BX.date.format(this.DATE_FORMAT, (toDate.getTime() + this.dayLength * 10) / 1000);
					params.location = this.Loc ? this.Loc.NEW : this.pLocation.value;

					if (params.location || params.codes.length > 0)
						this.UpdatePlanner(params);
				}
				else if (this.pPlannerBlock && BX.hasClass(this.pPlannerBlock, 'feed-event-planner-block-shown'))
				{
					this.HidePlanner();
				}
			}

		},

		UpdatePlanner: function(params)
		{
			var _this = this;
			top.BXCRES_Planner = {};

			BX.ajax.get(
				this.ajaxAction,
				{
					codes: params.codes || false,
					from: params.from,
					to: params.to,
					location: params.location,
					entries: params.entrieIds,
					sessid: BX.bitrix_sessid(),
					timezone: this.pFromTz.value,
					bx_event_calendar_update_planner: 'Y'
				},
				function()
				{
					setTimeout(function()
					{
						var
							showPlanner = !!(params.entries ||
								(top.BXCRES_Planner && top.BXCRES_Planner.entries && top.BXCRES_Planner.entries.length > 0)),
							plannerShown = BX.hasClass(_this.pPlannerBlock, 'feed-event-planner-block-shown');


						if (showPlanner) // Show first time
						{
							var refreshParams = {
								show: showPlanner && !plannerShown,
								params: params.params
							};

							if (params.entries)
							{
								top.BXCRES_Planner.entries = params.entries;
								refreshParams.scaleFrom = params.from;
								refreshParams.scaleTo = params.to;
							}

							refreshParams.loadedDataFrom = params.from;
							refreshParams.loadedDataTo = params.to;

							refreshParams.data = top.BXCRES_Planner;
							refreshParams.focusSelector = params.focusSelector == undefined ? false : params.focusSelector;

							_this.ShowPlannerAnimation();
							_this.RefreshPlannerState(refreshParams);
						}
						else if (!showPlanner && plannerShown) // Hide
						{
							_this.HidePlanner();
						}
					}, 100);
					return true;
				}
			);
		},

		RefreshPlannerState: function(params)
		{
			if (!params || typeof params !== 'object')
				params = {};

			this.plannerData = params.data;

			var
				fromDate, toDate,
				fullDay = this.pFullDay.checked,
				config = {},
				dayCellWidth = 90,
				scaleFrom, scaleTo,
				plannerBlockWidth,
				daysCount, duration,
				compactMode,
				plannerShown = this.pPlannerBlock && BX.hasClass(this.pPlannerBlock, 'feed-event-planner-block-shown');

			if (params.focusSelector == undefined)
				params.focusSelector = true;

			// Show planner cont if we should
			if (!plannerShown && params.show)
			{
				BX.addClass(this.pPlannerBlock, 'feed-event-planner-block-shown');
				BX.removeClass(this.pPlannerBlock, 'feed-event-planner-expanded');
				//width: plannerBlockWidth,
				//scaleDateFrom: scaleFrom,
				//scaleDateTo: scaleTo
				config.showTimelineDayTitle = false;
				config.minWidth = 300;
				config.adjustCellWidth = true;
				config.readonly = true;
				config.compactMode = true;
			}
			// Check
			compactMode = this.pPlannerTitle.offsetWidth > 0;

			if (fullDay)
			{
				fromDate = this.ParseDate(BX.util.trim(this.pFromDate.value));
				toDate = this.ParseDate(BX.util.trim(this.pToDate.value)) || fromDate;
			}
			else
			{
				fromDate = this.ParseDate(BX.util.trim(this.pFromDate.value) + ' ' + BX.util.trim(this.pFromTime.value));
				if (this.pToDate.value == '')
					this.pToDate.value = this.pFromDate.value;
				toDate = this.ParseDate(BX.util.trim(this.pToDate.value) + ' ' + BX.util.trim(this.pToTime.value));
			}


			if (fromDate && toDate &&
					fromDate.getTime && toDate.getTime &&
					fromDate.getTime() <= toDate.getTime())
			{
				if (!plannerShown && !params.data)
				{
					this.CheckPlannerState();
				}
				else
				{
					if (compactMode)
					{
						plannerBlockWidth = this.pPlannerBlock.offsetWidth - this.pPlannerTitle.offsetWidth - this.pPlannerLinkWrap.offsetWidth - 80;
					}

					if (fullDay)
					{
						// Event duration
						duration = Math.round(((toDate.getTime() - fromDate.getTime()) / this.dayLength) + 1);
						scaleFrom = new Date(fromDate.getTime());

						// Scale
						if (compactMode)
						{
							daysCount = Math.floor(plannerBlockWidth / dayCellWidth);

							if (duration >= daysCount)
							{
								scaleTo = new Date(scaleFrom.getTime() + this.dayLength * daysCount);
							}
							else
							{
								scaleFrom = new Date(scaleFrom.getTime() - this.dayLength);
								scaleTo = new Date(scaleFrom.getTime() + this.dayLength * (daysCount - 1));
							}

							config.width = daysCount * dayCellWidth;
							params.focusSelector = false;
						}
						else
						{
							scaleFrom = params.scaleFrom || new Date(scaleFrom.getTime() - this.dayLength * 3);
							scaleTo = params.scaleTo || new Date(scaleFrom.getTime() + this.dayLength * 10);
						}

						config.scaleType = '1day';
						config.scaleDateFrom = scaleFrom;
						config.scaleDateTo = scaleTo;
						config.adjustCellWidth = false;
					}
					else
					{
						// Event duration in hours
						//duration = Math.round((toDate.getTime() - fromDate.getTime()) / 3600000);

						if (compactMode)
						{
							config.scaleType = '15min';
							if (plannerBlockWidth < 800)
								config.scaleType = '30min';
							if (plannerBlockWidth < 500)
								config.scaleType = '1hour';
							if (plannerBlockWidth < 400)
								config.scaleType = '2hour';

							config.width = plannerBlockWidth;
							config.adjustCellWidth = true;
							config.scaleDateFrom = new Date(fromDate.getTime());
							config.scaleDateTo = new Date(fromDate.getTime());
							params.focusSelector = false;
						}
						else
						{
							config.changeFromFullDay = {
								scaleType: '1hour',
								timelineCellWidth: 40
							};
						}

						config.shownScaleTimeFrom = parseInt(this.config.workTimeStart);
						config.shownScaleTimeTo = parseInt(this.config.workTimeEnd);
					}

					BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
						{
							plannerId: this.plannerId,
							config: config,
							focusSelector: params.focusSelector,
							selector: {
								from: fromDate,
								to: toDate,
								fullDay: !!this.pFullDay.checked,
								animation: true,
								updateScaleLimits: true
							},
							data: params.data || false,
							loadedDataFrom: params.loadedDataFrom,
							loadedDataTo: params.loadedDataTo,
							show: !!params.show,
							params: params.params
						}
					]);
				}
			}
			else if (plannerShown)
			{
				this.HidePlanner();
			}
		},

		HidePlanner: function()
		{
			var _this = this;
			BX.onCustomEvent('OnCalendarPlannerDoUpdate', [
				{
					plannerId: this.plannerId,
					hide: true
				}
			]);

			// Opacity animation
			this.pPlannerBlock.style.opacity = 1;
			this.pPlannerBlock.style.display = '';
			this.pPlannerBlock.style.height = this.pPlannerBlock.offsetHeight + 'px';
			this.pPlannerBlock.style.overflow = 'hidden';

			new BX.easing({
				duration: 600,
				start: {opacity: 100, height: parseInt(this.pPlannerBlock.offsetHeight), padding: 14},
				finish: {opacity: 0, height: 0, padding: 0},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function(state)
				{
					_this.pPlannerBlock.style.opacity = state.opacity / 100;
					_this.pPlannerBlock.style.height = state.height + 'px';
					_this.pPlannerBlock.style.padding = Math.max(Math.round(state.padding), 0) + 'px';
				},
				complete: function()
				{
					// Show planner cont
					BX.removeClass(_this.pPlannerBlock, 'feed-event-planner-block-shown');
					_this.pPlannerBlock.removeAttribute('style');
				}
			}).animate();
		},

		ShowPlannerAnimation: function()
		{
			var _this = this;
			// Opacity animation
			this.pPlannerBlock.style.opacity = 0;
			this.pPlannerBlock.style.display = '';
			new BX.easing({
				duration: 300,
				start: {opacity: 0},
				finish: {opacity: 100},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function(state)
				{
					_this.pPlannerBlock.style.opacity = state.opacity / 100;
				},
				complete: function()
				{
					_this.pPlannerBlock.removeAttribute('style');
				}
			}).animate();
		},

		ExpandPlanner: function()
		{
			BX.addClass(this.pPlannerBlock, 'feed-event-planner-expanded');
			var plannerBlockWidth = this.pPlannerBlock.offsetWidth - 26;

			BX.onCustomEvent('OnCalendarPlannerDoExpand', [
				{
					plannerId: this.plannerId,
					config: {
						scaleType : '1hour',
						timelineCellWidth: 40,
						compactMode : false,
						readonly : false,
						width: plannerBlockWidth,
						adjustCellWidth: false
					}
				}
			]);
		},

		ProposeTime: function()
		{
			BX.onCustomEvent('OnCalendarPlannerDoProposeTime', [
				{
					plannerId: this.plannerId
				}
			]);
		}
	};

	function bxFormatDate(d, m, y)
	{
		var str = BX.message("FORMAT_DATE");

		str = str.replace(/YY(YY)?/ig, y);
		str = str.replace(/MMMM/ig, BX.message('MONTH_' + this.Number(m)));
		str = str.replace(/MM/ig, zeroInt(m));
		str = str.replace(/M/ig, BX.message('MON_' + this.Number(m)));
		str = str.replace(/DD/ig, zeroInt(d));

		return str;
	}

	function zeroInt(x)
	{
		x = parseInt(x, 10);
		if (isNaN(x))
			x = 0;
		return x < 10 ? '0' + x.toString() : x.toString();
	}

	function bxGetDateFromTS(ts, getObject)
	{
		var oDate = new Date(ts);
		if (!getObject)
		{
			var
				ho = oDate.getHours() || 0,
				mi = oDate.getMinutes() || 0;

			oDate = {
				date: oDate.getDate(),
				month: oDate.getMonth() + 1,
				year: oDate.getFullYear(),
				bTime: !!(ho || mi),
				oDate: oDate
			};

			if (oDate.bTime)
			{
				oDate.hour = ho;
				oDate.min = mi;
			}
		}

		return oDate;
	}

	function getUsableDateTime(timestamp, roundMin)
	{
		var r = (roundMin || 10) * 60 * 1000;
		timestamp = Math.ceil(timestamp / r) * r;
		return bxGetDateFromTS(timestamp);
	}

	function formatTimeByNum(h, m, bAMPM)
	{
		var res = '';
		if (m == undefined)
			m = '00';
		else
		{
			m = parseInt(m, 10);
			if (isNaN(m))
				m = '00';
			else
			{
				if (m > 59)
					m = 59;
				m = (m < 10) ? '0' + m.toString() : m.toString();
			}
		}

		h = parseInt(h, 10);
		if (h > 24)
			h = 24;
		if (isNaN(h))
			h = 0;

		if (bAMPM)
		{
			var ampm = 'am';

			if (h == 0)
			{
				h = 12;
			}
			else if (h == 12)
			{
				ampm = 'pm';
			}
			else if (h > 12)
			{
				ampm = 'pm';
				h -= 12;
			}

			res = h.toString() + ':' + m.toString() + ' ' + ampm;
		}
		else
		{
			res = ((h < 10) ? '0' : '') + h.toString() + ':' + m.toString();
		}
		return res;
	}


})(window);


