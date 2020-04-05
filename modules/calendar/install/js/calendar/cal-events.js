// Doesn't contain data, coolects common methods to manipulate events
(function(window) {
window.JSECEvent = function(oEC)
{
	this.oEC = oEC;
};

JSECEvent.prototype = {
	Get: function(id)
	{
		for (i = 0, l = this.oEC.arEvents.length; i < l; i++)
			if (this.oEC.arEvents[i].ID == id)
				return this.oEC.arEvents[i];
	},

	IsMeeting: function(oEvent)
	{
		return this.oEC.allowMeetings && oEvent && !!oEvent.IS_MEETING;
	},

	Attendees: function(oEvent)
	{
		var res = {};
		if (oEvent && oEvent.ID && this.oEC.arAttendees[oEvent.ID])
			res = this.oEC.arAttendees[oEvent.ID];

		return res;
	},

	View: function(oEvent)
	{
		if (oEvent)
			this.oEC.HighlightEvent_M(oEvent, false, true);

		this.oEC.DefaultAction(); // Reset state

		// Call custom handlers here
		BX.onCustomEvent(this.oEC, 'onBeforeCalendarEventView', [this.oEC, oEvent]);

		if (this.oEC.DefaultAction()) // Check state from custom handlers
		{
			if (oEvent['~TYPE'] == 'tasks' && window.taskIFramePopup && oEvent.ID > 0)
				taskIFramePopup.view(parseInt(oEvent.ID));
			else
				this.oEC.ShowViewEventPopup(oEvent);
			BX.onCustomEvent(this.oEC, 'onAfterCalendarEventView', [this.oEC, oEvent]);
		}
	},

	Edit: function(Params)
	{
		if (Params.oEvent)
			this.oEC.HighlightEvent_M(Params.oEvent, false, true);

		this.oEC.DefaultAction(); // Reset state

		// Call custom handlers here
		BX.onCustomEvent(this.oEC, 'onBeforeCalendarEventEdit', [this.oEC, Params]);

		if (this.oEC.DefaultAction()) // Check state from custom handlers
		{
			if (((!Params.oEvent && Params.bTasks) || (Params.oEvent && Params.oEvent['~TYPE'] == 'tasks' && Params.oEvent.ID > 0))  && window.taskIFramePopup)
			{
				if (Params.oEvent)
					taskIFramePopup.edit(parseInt(Params.oEvent.ID)); // Edit task
				else
					taskIFramePopup.add(); // Add task
			}
			else
				this.oEC.ShowEditEventPopup(Params);
			BX.onCustomEvent(this.oEC, 'onAfterCalendarEventView', [this.oEC, Params]);
		}
	},

	DeleteAllReccurent: function(oEvent, bConfirmed)
	{
		if (this.IsRecursive(oEvent) || oEvent.RECURRENCE_ID)
		{
			var
				event, arLoadedEventsId = {},
				i, arEvents = [];

			for (i = 0; i < this.oEC.arEvents.length; i++)
			{
				event = this.oEC.arEvents[i];
				if (event.ID != oEvent.ID &&
					event.RECURRENCE_ID != oEvent.ID &&
					event.ID != oEvent.RECURRENCE_ID &&
					(event.RECURRENCE_ID != oEvent.RECURRENCE_ID || !event.RECURRENCE_ID)
				)
				{
					arLoadedEventsId[this.SmartId(event)] = true;
					arEvents.push(event);
				}
				else
				{
					this.Blink(this.oEC.arEvents[i], false);
				}
			}
			this.oEC.arEvents = arEvents;
			this.oEC.arLoadedEventsId = arLoadedEventsId;
		}

		this.Display();

		return this.Delete(oEvent, bConfirmed, {recursionMode: 'all'});
	},

	Delete: function(oEvent, bConfirmed, params)
	{
		if (oEvent)
			this.oEC.HighlightEvent_M(oEvent, false, true);

		if (!params)
			params = {};

		if (!oEvent || !oEvent.ID)
			return false;

		this.oEC.DefaultAction(); // Reset state

		// Call custom handlers here
		BX.onCustomEvent(this.oEC, 'onBeforeCalendarEventDelete', [this.oEC, oEvent]);

		if (this.oEC.DefaultAction()) // Check state from custom handlers
		{
			if (oEvent['~TYPE'] == 'tasks')
			{
				// Do nothing
			}
			else
			{
				if ((this.IsRecursive(oEvent) || oEvent.RECURRENCE_ID) && !bConfirmed)
				{
					this.oEC.ShowConfirmDeleteDialog(oEvent);
					return false;
				}
				else
				{
					bConfirmed = !!bConfirmed;
					if (this.IsAttendee(oEvent) && !this.IsHost(oEvent))
					{
						bConfirmed = true;
						if (!confirm(EC_MESS.DeclineConfirm))
							return false;
					}

					if (this.IsHost(oEvent) && !bConfirmed)
					{
						bConfirmed = true;
						if (!confirm(EC_MESS.DelMeetingConfirm))
							return false;
					}

					if ((!oEvent.IS_MEETING || this.IsHost(oEvent)) && !bConfirmed)
					{
						if (!confirm(EC_MESS.DelEventConfirm))
							return false;
					}

					var _this = this;
					if (this.IsAttendee(oEvent) && !this.IsHost(oEvent))
					{
						return this.SetMeetingStatus(true, {eventId: bxInt(oEvent.ID), comment: ''});
					}
					else
					{
						this.oEC.Request({
							postData: this.oEC.GetReqData('delete', {
								id : bxInt(oEvent.ID),
								name : oEvent.NAME,
								calendar : bxInt(oEvent.SECT_ID),
								rec_mode : params.recursionMode || false
							}),
							errorText: EC_MESS.DelEventError,
							handler: function(oRes)
							{
								if (oRes)
								{
									_this.UnDisplay(oEvent);
								}
							}
						});
					}
					BX.onCustomEvent(this.oEC, 'onAfterCalendarEventDelete', [this.oEC]);
				}
			}
		}
		return true;
	},

	SetColor : function(oEvent)
	{
		var
			sect,
			_this = this,
			id = oEvent.SECT_ID,
			color, textColor;

		function getSectColor(id)
		{
			var sect = {};
			if (_this.oEC.arSectionsInd[id] && _this.oEC.arSections[_this.oEC.arSectionsInd[id]])
				sect = _this.oEC.arSections[_this.oEC.arSectionsInd[id]];

			if (!sect.TEXT_COLOR && sect.COLOR)
				sect.TEXT_COLOR = _this.oEC.ColorIsDark(sect.COLOR) ? '#FFFFFF' : '#000000';

			return sect;
		}

		if (oEvent['~TYPE'] == 'tasks')
		{
			color = this.oEC.taskBgColor;
			textColor =  this.oEC.taskTextColor;
		}
		else
		{
			if (oEvent.USER_MEETING && !this.IsHost(oEvent))
			{
				color = oEvent.USER_MEETING.COLOR;
				textColor = oEvent.USER_MEETING.TEXT_COLOR;

				if (!color)
					color = oEvent.COLOR;
				if (!textColor)
					textColor = oEvent.TEXT_COLOR;

				if (!color)
				{
					sect = getSectColor(id);
					if (sect.COLOR)
					{
						color = this.oEC.oSections[id].COLOR;
						textColor = this.oEC.oSections[id].TEXT_COLOR;
					}
				}

				if ((!color || !textColor) && this.oEC.arSections.length)
				{
					id = this.oEC.GetMeetingSection();
					sect = getSectColor(id);
					if (id)
					{
						if (!color && sect.COLOR)
							color = sect.COLOR;
						if (!textColor && sect.TEXT_COLOR)
							textColor = sect.TEXT_COLOR;
					}
				}
			}
			else if (id)
			{
				color = oEvent.COLOR;
				textColor = oEvent.TEXT_COLOR;

				sect = getSectColor(id);
				if (!color && sect.COLOR)
					color = sect.COLOR;
				if (!textColor && sect.TEXT_COLOR)
					textColor = sect.TEXT_COLOR;
			}
		}

		if (!color)
			color = '#CEE669';

		oEvent.displayColor = color;
		oEvent.bDark = this.oEC.ColorIsDark(color);
		if (!textColor)
			textColor = oEvent.bDark ? '#FFFFFF' : '#000000';
		oEvent.displayTextColor = textColor;

		return oEvent;
	},

	SaveUserFields: function(UFForm, eventId)
	{
		if (UFForm && UFForm.event_id && eventId > 0)
		{
			UFForm.event_id.value = parseInt(eventId);
			var reqId = this.oEC.GetReqData('').reqId;
			var _this = this;

			if(UFForm.reqId)
				UFForm.reqId.value = reqId;

			BX.ajax.submit(
				UFForm,
				function()
				{
					var oRes = top.BXCRES[reqId];
					if(oRes && oRes['refresh'])
						_this.ReloadAll(false);
				}
			);
		}
	},

	GetQuestIcon: function(oEvent)
	{
		if (!this.IsBlinked(oEvent))
			return '';
		return '<b title="' + EC_MESS.NotConfirmed + '" class="bxec-stat-q">?</b>';
	},

	IsTask: function(oEvent)
	{
		return oEvent['~TYPE'] == 'tasks';
	},

	IsHost: function(oEvent, userId)
	{
		if (!userId)
			userId = this.oEC.userId;
		return (oEvent.IS_MEETING && (oEvent.MEETING_STATUS == 'H' || oEvent.ID == oEvent.PARENT_ID && userId == oEvent.MEETING_HOST));
	},

	IsAttendee: function(oEvent, userId)
	{
		if (!userId)
			userId = this.oEC.userId;

		//if (oEvent.IS_MEETING && oEvent.USER_MEETING)
		if (oEvent.IS_MEETING)
		{
			//if (oEvent.USER_MEETING.ATTENDEE_ID != userId)
			//	return false;
			return true;
		}
		return false;
	},

	IsCrm: function(oEvent)
	{
		return oEvent.UF_CRM_CAL_EVENT && oEvent.UF_CRM_CAL_EVENT != "";
	},

	IsBlinked: function(oEvent)
	{
		return oEvent.IS_MEETING && oEvent.MEETING_STATUS == 'Q';
	},

	IsRecursive: function(oEvent)
	{
		return !!oEvent.RRULE;
	},

	Blink: function(oEvent, bBlink, bCheck)
	{
		if (oEvent && oEvent.display && oEvent.OWNER_ID == this.oEC.userId)
		{
			if (bCheck)
				bBlink = this.IsBlinked(oEvent);

			if (oEvent._blinkInterval)
				oEvent._blinkInterval = !!clearInterval(oEvent._blinkInterval);

			if (bBlink && this.oEC.userSettings.blink) // Set blinked
			{
				var _this = this;
				oEvent._blinkInterval = setInterval(function ()
				{
					_this.BlinkInterval(oEvent);
				}, 600);
			}
			else if (!bBlink) // Clear blinking
			{
				var i, cn = "bxec-event-blink";
				if (oEvent.oParts)
				{
					for (i = 0; i < oEvent.oParts.length; i++)
					{
						if (oEvent.oParts[i])
							BX.removeClass(oEvent.oParts[i], cn);
					}
				}

				if (oEvent.oDaysT)
				{
					if (oEvent.oDaysT.week)
						BX.removeClass(oEvent.oDaysT.week, cn);

					if (oEvent.oDaysT.day)
						BX.removeClass(oEvent.oDaysT.day, cn);
				}

				if (oEvent.oTLParts)
				{
					if (oEvent.oTLParts.week)
					{
						for (i = 0; i < oEvent.oTLParts.week.length; i++)
						{
							if (oEvent.oTLParts.week[i])
								BX.removeClass(oEvent.oTLParts.week[i], cn);
						}
					}

					if (oEvent.oTLParts.day)
					{
						for (i = 0; i < oEvent.oTLParts.day.length; i++)
						{
							if (oEvent.oTLParts.day[i])
								BX.removeClass(oEvent.oTLParts.day[i], cn);
						}
					}
				}
			}
		}
	},

	BlinkInterval: function(oEvent)
	{
		if (!this.oEC.userSettings.blink)
			return this.Blink(oEvent, false, false);

		var i, len, cn = "bxec-event-blink", tab = this.oEC.activeTabId;
		if (tab == 'month')
		{
			if (oEvent.oParts)
			{
				len = oEvent.oParts.length;
				for (i = 0; i < len; i++)
					if (oEvent.oParts[i])
						BX.toggleClass(oEvent.oParts[i], cn);
			}
		}
		else // week, day
		{
			if (oEvent.oDaysT && oEvent.oTLParts)
			{
				if (oEvent.oDaysT[tab])
					BX.toggleClass(oEvent.oDaysT[tab], cn);

				if (oEvent.oTLParts[tab])
				{
					len = oEvent.oTLParts[tab].length;
					for (i = 0; i < len; i++)
						if (oEvent.oTLParts[tab][i])
							BX.toggleClass(oEvent.oTLParts[tab][i], cn);
				}
			}
		}
	},

	SetMeetingStatus: function(bAccept, params) // Confirm
	{
		var
			event = {},
			parentId,
			eventId = params && params.eventId ? params.eventId : 0;

		if (typeof params == 'undefined')
			params = {};

		if (!eventId && this.oEC.oViewEventDialog)
		{
			event = this.oEC.oViewEventDialog.CAL.oEvent;
			eventId = event.ID;
		}
		parentId = parseInt(params.parentId || event.PARENT_ID);

		if (!params.eventId)
			params.eventId = eventId;

		if (!bAccept && !params.confirmed)
		{
			if (this.IsRecursive(event))
			{
				this.oEC.ShowConfirmDeclineDialog(event);
				return false;
			}
			else if (!confirm(EC_MESS.DeclineConfirm))
			{
				return false;
			}
		}

		var _this = this;
		this.oEC.Request({
			postData: this.oEC.GetReqData('set_meeting_status',
			{
				event_id: parseInt(params.eventId),
				parent_id: parentId,
				status: bAccept ? 'Y' : 'N',
				reccurent_mode: params.reccurentMode || false,
				current_date_from: params.currentDateFrom || false
			}),
			handler: function(oRes)
			{
				if (oRes)
				{
					if (!_this.oEC.userSettings.showDeclined &&
						!_this.IsHost(event) &&
						!bAccept &&
						!params.reccurentMode
					)
					{
						_this.UnDisplay(_this.Get(params.eventId));
					}

					if (bAccept || params.reccurentMode)
					{
						_this.ReloadAll(false);
					}
				}
				return true;
			}
		});
		return true;
	},

	SmartId : function(e)
	{
		var sid = e.PARENT_ID || e.ID;
		if (this.IsRecursive(e))
			sid += '|' + e.DT_FROM_TS;

		if (e['~TYPE'] == 'tasks')
			sid += '|' + 'task';

		return sid;
	},

	ReloadAll: function(bTimeout)
	{
		if (this._reloadTimeout)
			clearTimeout(this._reloadTimeout);

		var _this = this;
		this.oEC.arLoadedEventsId = {};
		this.oEC.arLoadedParentId = {};
		this.oEC.arLoadedMonth = {};
		this.oEC.arEvents = [];
		this.oEC.SetTabNeedRefresh('month', true);
		this.oEC.SetTabNeedRefresh('week', true);
		this.oEC.SetTabNeedRefresh('day', true);

		if (bTimeout === false)
			this.oEC.LoadEvents();
		else
			this._reloadTimeout = setTimeout(function(){_this.oEC.LoadEvents();}, 600);
	},

	Save: function(P)
	{
		var
			month = parseInt(this.oEC.activeDate.month, 10),
			year = this.oEC.activeDate.year;

		var postData = this.oEC.GetReqData('edit_event',
			{
				id: P.id || 0,
				name: P.name,
				desc: P.desc || '',
				date_from: P.date_from,
				date_to: P.date_to,
				default_tz: P.default_tz,
				sections: [P.calendar],
				location: P.location || {OLD: '', NEW: '', CHANGED: ''},
				month: month + 1,
				year: year,
				skip_time: P.skip_time || 'N'
			}
		);

		this.oEC.arLoadedMonth = {};

		if (P.RRULE && P.RRULE)
			postData.rrule = P.RRULE;

		if (P.remind)
			postData.remind = [{type: P.remind_type, count: P.remind_count}];

		if (this.oEC.allowMeetings)
		{
			postData.is_meeting = P.isMeeting || '';
			if (P.isMeeting)
			{
				postData.meeting = P.meeting || {};
				if (P.guests)
					postData.guest = P.guests.length > 0 ? P.guests : [0];
			}
		}

		postData.color = P.color || '';
		postData.text_color = P.text_color || '';

		// Other
		if (P.accessibility)
			postData.accessibility = P.accessibility;
		if (P.importance)
			postData.importance = P.importance;
		if (P.private_event)
			postData.private_event = P.private_event;

		var _this = this;
		this.oEC.Request({
			postData: postData,
			errorText: EC_MESS.EventSaveError,
			handler: function(oRes)
			{
				// Try to save userfields
				if (oRes.id && P.UFForm)
					_this.SaveUserFields(P.UFForm, oRes.id);

				if (oRes.eventIds && oRes.eventIds.length > 0)
				{
					for (var i = 0; i < oRes.eventIds.length; i++)
					{
						_this.UnDisplay(oRes.eventIds[i], false);
					}
				}

				_this.oEC.HandleEvents(oRes.events, oRes.attendees);
				_this.oEC.arLoadedMonth[month + '.' + year] = true;

				_this.Display();
				return true;
			}
		});
	},

	Display: function()
	{
		this.oEC.SetTabNeedRefresh(this.oEC.activeTabId);
		if (this.oEC.activeTabId == 'month')
		{
			this.oEC.DisplayEventsMonth(true);
		}
		else // week, day
		{
			this.oEC.DeSelectTime(this.oEC.activeTabId);
			this.oEC.ReBuildEvents(this.oEC.activeTabId);
		}
	},

	UnDisplay: function(oEvent, bDisplay)
	{
		var id;
		if (typeof oEvent == 'object' && oEvent.ID)
			id = oEvent.ID;
		else if(oEvent > 0)
			id = oEvent;

		// Clean events
		var
			e, arLoadedEventsId = {},
			i, arEvents = [];

		for (i = 0; i < this.oEC.arEvents.length; i++)
		{
			e = this.oEC.arEvents[i];
			if (e && (e.ID != id || e['~TYPE'] == 'tasks'))
			{
				arLoadedEventsId[this.SmartId(e)] = true;
				arEvents.push(e);
			}
			else
				this.Blink(this.oEC.arEvents[i], false);
		}
		this.oEC.arEvents = arEvents;
		this.oEC.arLoadedEventsId = arLoadedEventsId;

		if (bDisplay !== false)
			this.Display();
	},

	SetMeetingParams: function(Params)
	{
		var D = this.oEC.oEditEventDialog;

		var postData = this.oEC.GetReqData('set_meeting_params',
			{
				event_id: D.CAL.oEvent.ID,
				accessibility: D.CAL.DOM.Accessibility.value
			}
		);

		if (this.oEC.allowReminders && D.CAL.DOM.RemCheck.checked)
		{
			var rcount = D.CAL.DOM.RemCount.value || '';
			rcount = rcount.replace(/,/g, '.');
			rcount = rcount.replace(/[^\d|\.]/g, '');
			postData.remind = [{
				type: D.CAL.DOM.RemType.value,
				count: rcount
			}];
		}

		this.oEC.Request({
			postData: postData,
			handler: function(oRes)
			{
				D.CAL.oEvent.USER_MEETING.REMIND = postData.remind || [];
				D.CAL.oEvent.USER_MEETING.ACCESSIBILITY = postData.accessibility;
			}
		});

		if (Params.callback && typeof Params.callback == 'function')
			Params.callback();
	},

	CanDo: function(oEvent, action, userId)
	{
		if (!oEvent)
			return false;

		if (!userId)
			userId = this.oEC.userId;

		if (action == 'edit' || action == 'delete')
		{
			if (this.bReadOnly)
				return false;

			if (!oEvent['IS_MEETING'] && oEvent.MEETING && oEvent.MEETING.ORGANIZER)
				return false;

			if (oEvent.SECT_ID)
			{
				var oSect = this.oEC.oSections[oEvent.SECT_ID];
				if(oSect)
				{
					if (oSect.SUPERPOSED &&
						(oSect.OWNER_ID != this.oEC.ownerId || oSect.CAL_TYPE != this.oEC.type))
					{
						return false;
					}

					if (oSect.CAL_DAV_CAL && oSect.CAL_DAV_CAL.indexOf('@virtual/events/') !== -1)
					{
						return false;
					}

					if (oSect.PERM)
						return !!oSect.PERM.edit;
				}
			}
		}
		return false;
	},

	BuildActions: function(P)
	{
		var
			ic,
			oEvent = P.oEvent,
			isTask = oEvent['~TYPE'] == 'tasks',
			count = 0,
			oDiv = BX.create('DIV', {props:{className : 'bxec-event-actions'}}),
			oDiv_ = oDiv.appendChild(BX.create('DIV', {props: {className : P.bTimeline ? 'bxec-icon-cont-tl' : 'bxec-icon-cont'}}));

		if ((!this.IsMeeting(oEvent) || (this.IsMeeting(oEvent) && this.IsHost(oEvent))) &&
			(this.CanDo(oEvent, 'edit') || (isTask && oEvent.CAN_EDIT)) &&
			(!oEvent.PRIVATE_EVENT || this.oEC.Personal()))
		{
			ic = oDiv_.appendChild(BX.create('I', {props: {className : 'bxec-event-but bxec-ev-edit-icon', title: isTask ? EC_MESS.TaskEdit : EC_MESS.EditEvent}}));
			ic.setAttribute('data-bx-event-action', 'edit');
			count++;

			// Add del button
			if (this.IsAttendee(oEvent) && !this.IsHost(oEvent))
			{
				if (oEvent.MEETING_STATUS != 'N')
				{
					ic = oDiv_.appendChild(BX.create('I', {props: {className : 'bxec-event-but bxec-ev-del-icon', title: EC_MESS.DelEncounter}}));
					ic.setAttribute('data-bx-event-action', 'del');
					count++;
				}
			}
			else if (!isTask)
			{
				ic = oDiv_.appendChild(BX.create('I', {props: {className : 'bxec-event-but bxec-ev-del-icon', title: EC_MESS.DelEvent}}));
				ic.setAttribute('data-bx-event-action', 'del');
				count++;
			}
		}

		if (count != 2)
		{
			oDiv_.style.height = '18px';
			oDiv_.style.width = (18 * count) + 'px';
			oDiv_.style.left = '-' + (18 * count) + 'px';
		}

		P.cont.appendChild(oDiv);
	},

	GetLabelStyle: function(oEvent)
	{
		var
			labelStyle = '',
			imp = oEvent.IMPORTANCE;
		if (imp && imp != 'normal')
			labelStyle = ' style="' + (imp == 'high' ? 'font-weight: bold;' : 'color: #535353;') + '"';
		return labelStyle;
	},

	PreHandle: function(oEvent)
	{
		if (oEvent.DATE_FROM && oEvent.DATE_TO)
		{
			oEvent.dateFrom = BX.parseDate(oEvent.DATE_FROM);
			oEvent.dateTo = BX.parseDate(oEvent.DATE_TO);

			if (oEvent.dateFrom && oEvent.dateTo)
			{
				oEvent.DT_FROM_TS = Math.floor(oEvent.dateFrom.getTime() / 1000) * 1000;
				oEvent.DT_TO_TS = Math.floor(oEvent.dateTo.getTime() / 1000) * 1000;

				if (oEvent.DT_SKIP_TIME !== "Y")
				{
					oEvent.DT_FROM_TS -= (oEvent['~USER_OFFSET_FROM'] || 0) * 1000;
					oEvent.DT_TO_TS -= (oEvent['~USER_OFFSET_TO'] || 0) * 1000;
				}
			}
		}
		return oEvent;
	},

	CutOffRecursiveEvent: function(oEvent, untilDate)
	{
		var _this = this;
		// Change event
		var untilDateTs = Math.floor(this.oEC.ParseDate(untilDate).getTime() / 1000) * 1000;
		untilDate = this.oEC.FormatDate(new Date(untilDateTs - this.oEC.dayLength));

		if (oEvent.RRULE)
		{
			oEvent.RRULE.UNTIL = untilDate;
			oEvent.RRULE['~UNTIL'] = untilDate;
		}

		// Clean events
		var
			ev, arLoadedEventsId = {},
			i, arEvents = [];

		if (this.IsRecursive(oEvent))
		{
			for (i = 0; i < this.oEC.arEvents.length; i++)
			{
				ev = this.oEC.arEvents[i];
				if (ev && (ev.ID !== oEvent.ID || ev.DT_FROM_TS < untilDateTs || ev['~TYPE'] == 'tasks'))
				{
					arLoadedEventsId[this.SmartId(ev)] = true;
					arEvents.push(ev);
				}
				else
				{
					this.Blink(this.oEC.arEvents[i], false);
				}
			}
			this.oEC.arEvents = arEvents;
			this.oEC.arLoadedEventsId = arLoadedEventsId;
		}
		else
		{
			for (i = 0; i < this.oEC.arEvents.length; i++)
			{
				ev = this.oEC.arEvents[i];
				if (ev && (ev.ID !== oEvent.ID || ev.DT_FROM_TS < untilDateTs || ev['~TYPE'] == 'tasks'))
				{
					arLoadedEventsId[this.SmartId(ev)] = true;
					arEvents.push(ev);
				}
				else
				{
					this.Blink(this.oEC.arEvents[i], false);
				}
			}
			this.oEC.arEvents = arEvents;
			this.oEC.arLoadedEventsId = arLoadedEventsId;
		}

		this.oEC.Request({
			postData: this.oEC.GetReqData('change_recurcive_event_until',
				{
					event_id: parseInt(oEvent.ID),
					until_date: untilDate
				}),
			handler: function()
			{
				_this.ReloadAll(false);
				return true;
			}
		});

		this.Display();
	},

	ExcludeRecursionDate: function(oEvent, date)
	{
		var _this = this;
		var dateTs = Math.floor(this.oEC.ParseDate(date).getTime() / 1000) * 1000;

		// Clean events
		var
			e, arLoadedEventsId = {},
			i, arEvents = [];

		for (i = 0; i < this.oEC.arEvents.length; i++)
		{
			e = this.oEC.arEvents[i];
			if (e &&
					(e.ID !== oEvent.ID ||
					e.DT_FROM_TS != dateTs ||
					e['~TYPE'] == 'tasks'))
			{
				arLoadedEventsId[this.SmartId(e)] = true;
				arEvents.push(e);
			}
			else
			{
				this.Blink(this.oEC.arEvents[i], false);
			}
		}
		this.oEC.arEvents = arEvents;
		this.oEC.arLoadedEventsId = arLoadedEventsId;

		this.oEC.Request({
			postData: this.oEC.GetReqData('exclude_recursion_date',
				{
					event_id: parseInt(oEvent.ID),
					exclude_date: date
				}),
			handler: function()
			{
				_this.ReloadAll(false);
				return true;
			}
		});

		this.Display();
	}
};
})(window);