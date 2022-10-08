;(function(){

if(!!window.BX.CCalendarPlannerHandler)
	return;

var BX = window.BX;

BX.CCalendarPlannerHandler = function()
{
	this.PLANNER = null;
	this.EVENTS = null;
	this.EVENTS_LIST = null;
	this.EVENTWND = {};
	this.CLOCK = null;

	BX.addCustomEvent('onPlannerDataRecieved', BX.proxy(this.draw, this));
};

BX.CCalendarPlannerHandler.prototype.draw = function(obPlanner, DATA)
{
	if(!!this._skipDraw)
	{
		this._skipDraw = false;
		return;
	}

	this.PLANNER = obPlanner;

	if(!DATA.CALENDAR_ENABLED)
		return;

	if (!this.EVENTS)
	{
		this.EVENTS = BX.create('DIV');
		this.EVENTS.appendChild(BX.create('DIV', {
			props: {className: 'tm-popup-section tm-popup-section-events'},
			html: '<span class="tm-popup-section-text">' + BX.message('JS_CORE_PL_EVENTS') + '</span>'
		}));

		this.EVENTS.appendChild(BX.create('DIV', {
			props: {className: 'tm-popup-events' + (BX.isAmPmMode() ? " tm-popup-events-ampm" : "")},
			children: [
				(this.EVENTS_LIST = BX.create('DIV', {
					props: {className: 'tm-popup-event-list'}
				})),
				this.drawEventForm(BX.proxy(this._createEventCallback, this))
			]
		}));
	}
	else
	{
		BX.cleanNode(this.EVENTS_LIST);
	}

	if (DATA.EVENTS.length > 0)
	{
		BX.removeClass(this.EVENTS, 'tm-popup-events-empty');
		var LAST_EVENT = null;
		for (var i=0,l=DATA.EVENTS.length;i<l;i++)
		{
			var q = this.EVENTS_LIST.appendChild(this.drawEvent(DATA.EVENTS[i]));

			if (DATA.EVENT_LAST_ID && DATA.EVENT_LAST_ID == DATA.EVENTS[i].ID)
				LAST_EVENT = q;
		}

		if (!!LAST_EVENT)
		{
			BX.defer(function()
			{
				if (LAST_EVENT.offsetTop < this.EVENTS_LIST.scrollTop || LAST_EVENT.offsetTop + LAST_EVENT.offsetHeight > this.EVENTS_LIST.scrollTop + this.EVENTS_LIST.offsetHeight)
				{
					this.EVENTS_LIST.scrollTop = LAST_EVENT.offsetTop - parseInt(this.EVENTS_LIST.offsetHeight/2);
				}
			}, this)();
		}
	}
	else
	{
		BX.addClass(this.EVENTS, 'tm-popup-events-empty');
	}

	obPlanner.addBlock(this.EVENTS, 300);
};

BX.CCalendarPlannerHandler.prototype.drawEvent = function(event, additional_props, fulldate)
{
	additional_props = additional_props || {};
	additional_props.className = 'tm-popup-event-name';
	fulldate = fulldate || false;

	return BX.create('DIV', {
		props: {
			className: 'tm-popup-event',
			bx_event_id: event.ID
		},
		children: [
			BX.create('DIV', {
				props: {className: 'tm-popup-event-datetime'},
				html: '<span class="tm-popup-event-time-start' + (event.DATE_FROM_TODAY ? '' : ' tm-popup-event-time-passed') + '">' + (fulldate?BX.timeman.formatDate(event.DATE_FROM)+' ':'') + event.TIME_FROM + '</span><span class="tm-popup-event-separator">-</span><span class="tm-popup-event-time-end' + (event.DATE_TO_TODAY ? '' : ' tm-popup-event-time-passed') + '">' +(fulldate?BX.timeman.formatDate(event.DATE_TO)+' ':'')+  event.TIME_TO + '</span>'
			}),
			BX.create('DIV', {
				props: additional_props,
				// events: event.ID ? {click: BX.proxy(this.showEvent, this)} : null,
				html: '<a class="tm-popup-event-text" href="' + event.EVENT_PATH + '">' + BX.util.htmlspecialchars(event.NAME) + '</a>'
			})
		]
	});
};

BX.CCalendarPlannerHandler.prototype.showEvent = function(e)
{
	var event_id = BX.proxy_context.parentNode.bx_event_id;

	if (this.EVENTWND[event_id] && this.EVENTWND[event_id].node != BX.proxy_context)
	{
		this.EVENTWND[event_id].Clear();
		this.EVENTWND[event_id] = null;
	}

	if (!this.EVENTWND[event_id])
	{
		this.EVENTWND[event_id] = new BX.CCalendarPlannerEventPopup({
			planner: this.PLANNER,
			node: BX.proxy_context,
			bind: this.EVENTS.firstChild,
			id: event_id
		});
	}

	BX.onCustomEvent(this, 'onEventWndShow', [this.EVENTWND[event_id]]);

	this._skipDraw = true;
	this.EVENTWND[event_id].Show(this.PLANNER);

	return BX.PreventDefault(e);
};

BX.CCalendarPlannerHandler.prototype.drawEventForm = function(cb)
{
	var mt_format_css = BX.isAmPmMode() ? '_am_pm' : '';

	var handler = BX.delegate(function(e, bEnterPressed)
		{
			inp_Name.value = BX.util.trim(inp_Name.value);
			if (inp_Name.value && inp_Name.value!=BX.message('JS_CORE_PL_EVENTS_ADD'))
			{
				cb({
					from: inp_TimeFrom.value,
					to: inp_TimeTo.value,
					name: inp_Name.value,
					absence: inp_Absence.checked ? 'Y' : 'N'
				});

				BX.timer.start(inp_TimeFrom.bxtimer);
				BX.timer.start(inp_TimeTo.bxtimer);

				if (!bEnterPressed)
				{
					BX.addClass(inp_Name.parentNode, 'tm-popup-event-form-disabled')
					inp_Name.value = BX.message('JS_CORE_PL_EVENTS_ADD');
				}
				else
				{
					inp_Name.value = '';
				}
			}

			return (e || window.event) ? BX.PreventDefault(e) : null;
		}, this),

		handler_name_focus = function()
		{
			BX.removeClass(this.parentNode, 'tm-popup-event-form-disabled');
			if(this.value == BX.message('JS_CORE_PL_EVENTS_ADD'))
				this.value = '';
		};

	var inp_TimeFrom = BX.create('INPUT', {
		props: {type: 'text', className: 'tm-popup-event-start-time-textbox' + mt_format_css}
	});

	inp_TimeFrom.onclick = BX.delegate(function()
	{
		var cb = BX.delegate(function(value) {
			this.CLOCK.closeWnd();

			var oldvalue_From = unFormatTime(inp_TimeFrom.value),
				oldvalue_To = unFormatTime(inp_TimeTo.value);

			var diff = 3600;
			if (oldvalue_From && oldvalue_To)
				diff = oldvalue_To - oldvalue_From;

			BX.timer.stop(inp_TimeFrom.bxtimer);
			BX.timer.stop(inp_TimeTo.bxtimer);

			inp_TimeFrom.value = value;

			inp_TimeTo.value = formatTime(unFormatTime(value) + diff);

			inp_TimeTo.focus();
			inp_TimeTo.onclick();
		}, this);

		if (!this.CLOCK)
		{
			this.CLOCK = new BX.CClockSelector({
				start_time: unFormatTime(inp_TimeFrom.value),
				node: inp_TimeFrom,
				callback: cb
			});
		}
		else
		{
			this.CLOCK.setNode(inp_TimeFrom);
			this.CLOCK.setTime(unFormatTime(inp_TimeFrom.value));
			this.CLOCK.setCallback(cb);
		}

		inp_TimeFrom.blur();
		this.CLOCK.Show();
	}, this);

	inp_TimeFrom.bxtimer = BX.timer(inp_TimeFrom, {dt: 3600000, accuracy: 3600});

	var inp_TimeTo = BX.create('INPUT', {
		props: {type: 'text', className: 'tm-popup-event-end-time-textbox' + mt_format_css}
	});

	inp_TimeTo.onclick = BX.delegate(function()
	{
		var cb = BX.delegate(function(value) {
			this.CLOCK.closeWnd();
			inp_TimeTo.value = value;

			BX.timer.stop(inp_TimeFrom.bxtimer);
			BX.timer.stop(inp_TimeTo.bxtimer);

			inp_Name.focus();
			handler_name_focus.apply(inp_Name);
		}, this);

		if (!this.CLOCK)
		{
			this.CLOCK = new BX.CClockSelector({
				start_time: unFormatTime(inp_TimeTo.value),
				node: inp_TimeTo,
				callback: cb
			});
		}
		else
		{
			this.CLOCK.setNode(inp_TimeTo);
			this.CLOCK.setTime(unFormatTime(inp_TimeTo.value));
			this.CLOCK.setCallback(cb);
		}

		inp_TimeTo.blur();
		this.CLOCK.Show();
	}, this);

	inp_TimeTo.bxtimer = BX.timer(inp_TimeTo, {dt: 7200000, accuracy: 3600});

	var inp_Name = BX.create('INPUT', {
		props: {type: 'text', className: 'tm-popup-event-form-textbox' + mt_format_css, value: BX.message('JS_CORE_PL_EVENTS_ADD')},
		events: {
			keypress: function(e) {
				return (e.keyCode == 13) ? handler(e, true) : true;
			},
			blur: function() {
				if (this.value == '')
				{
					BX.addClass(this.parentNode, 'tm-popup-event-form-disabled');
					this.value = BX.message('JS_CORE_PL_EVENTS_ADD');
				}
			},
			focus: handler_name_focus
		}
	});

	var id = 'bx_tm_absence_' + Math.random();
	var inp_Absence = BX.create('INPUT', {
		props: {type: 'checkbox', className: 'checkbox', id: id}
	});

	this.EVENTS_FORM = BX.create('DIV', {
		props: {className: 'tm-popup-event-form tm-popup-event-form-disabled'},
		children: [
			inp_TimeFrom, inp_TimeTo, inp_Name,
			BX.create('SPAN', {
				props: {className: 'tm-popup-event-form-submit'},
				events: {
					click: handler
				}
			}),
			BX.create('DIV', {
				props: {className:'tm-popup-event-form-options'},
				children: [
					inp_Absence,
					BX.create('LABEL', {props: {htmlFor: id}, text: BX.message('JS_CORE_PL_EVENT_ABSENT')})
				]
			})
		]
	});

	return this.EVENTS_FORM;
};

BX.CCalendarPlannerHandler.prototype._createEventCallback = function(ev)
{
	calendarLastParams = ev;

	this.PLANNER.query('calendar_add', ev);

	this.EVENTS_LIST.appendChild(this.drawEvent({
		DATE_FROM_TODAY: true, DATE_TO_TODAY: true,
		NAME: BX.util.htmlspecialchars(ev.name),
		TIME_FROM: ev.from,
		TIME_TO: ev.to
	}));
};

/**************************/
BX.CCalendarPlannerEventPopup = function(params)
{
	this.params = params;
	this.node = params.node;

	var ie7 = false;
	/*@cc_on
		 @if (@_jscript_version <= 5.7)
			ie7 = true;
		/*@end
	@*/

	this.popup = BX.PopupWindowManager.create('event_' + this.params.id, this.params.bind, {
		closeIcon : {right: "12px", top: "10px"},
		closeByEsc: true,
		offsetLeft : ie7 || (document.documentMode && document.documentMode <= 7) ? -347 : -340,
		autoHide: true,
		bindOptions : {
			forceBindPosition : true,
			forceTop : true
		},
		angle : {
			position: "right",
			offset : this.params.angle_offset || 27
		}
	});

	BX.addCustomEvent(this, 'onEventWndShow', this.onEventWndShow.bind(this));

	this.bSkipShow = false;
	this.isReady = false;
};

BX.CCalendarPlannerEventPopup.prototype.onEventWndShow = function(wnd)
{
	if (wnd != this)
	{
		if (this.popup)
			this.popup.close();
		else
			this.bSkipShow = true;
	}
};

BX.CCalendarPlannerEventPopup.prototype.Show = function(planner, data)
{
	BX.removeCustomEvent(planner, 'onPlannerDataRecieved', BX.proxy(this.Show, this));

	data = data || this.data;

	if (data && data.error)
		return;

	if (!data)
	{
		BX.addCustomEvent(planner, 'onPlannerDataRecieved', BX.proxy(this.Show, this));
		return planner.query('calendar_show', {id: this.params.id});
	}

	if(data.EVENT)
	{
		data = data.EVENT;
	}

	this.data = data;

	if (this.bSkipShow)
	{
		this.bSkipShow = false;
	}
	else
	{
		this.popup.setContent(this.GetContent());
		this.popup.setButtons(this.GetButtons());

		var offset = 0;
		if (this.params.node && this.params.node.parentNode && this.params.node.parentNode.parentNode)
		{
			offset = this.params.node.parentNode.offsetTop - this.params.node.parentNode.parentNode.scrollTop;
		}

		this.popup.setOffset({offsetTop: this.params.offsetTop || (offset - 20)});
		//popup.setAngle({ offset : 27 });
		this.popup.adjustPosition();
		this.popup.show();
	}

	return true;
};

BX.CCalendarPlannerEventPopup.prototype.GetContent = function()
{
	var html = '<div class="tm-event-popup">',
		hr = '<div class="popup-window-hr"><i></i></div>';

	html += '<div class="tm-popup-title"><a class="tm-popup-title-link" href="' + this.data.URL + '">' + BX.util.htmlspecialchars(this.data.NAME) +'</a></div>';
	if (this.data.DESCRIPTION)
	{
		html += hr + '<div class="tm-event-popup-description">' + this.data.DESCRIPTION + '</div>';
	}

	html += hr;

	html += '<div class="tm-event-popup-time"><div class="tm-event-popup-time-interval">' + this.data.DATE_F + '</div>';
	if (this.data.DATE_F_TO && this.data.DATE_F_TO > 0)
		html += '<div class="tm-event-popup-time-hint">(' + this.data.DATE_F_TO + ')</div></div>'


	if (this.data.GUESTS)
	{
		html += hr + '<div class="tm-event-popup-participants">';

		if (this.data.HOST)
		{
			html += '<div class="tm-event-popup-participant"><div class="tm-event-popup-participant-status tm-event-popup-participant-status-accept"></div><div class="tm-event-popup-participant-name"><a class="tm-event-popup-participant-link" href="' + this.data.HOST.url + '">' + this.data.HOST.name + '</a><span class="tm-event-popup-participant-hint">' + BX.message('JS_CORE_PL_EVENT_HOST') + '</span></div></div>';
		}

		if (this.data.GUESTS.length > 0)
		{
			html += '<table cellspacing="0" class="tm-event-popup-participants-grid"><tbody><tr>';

			var d = Math.ceil(this.data.GUESTS.length/2),
				grids = ['',''];

			for (var i=0;i<this.data.GUESTS.length; i++)
			{
				var status = '';
				if (this.data.GUESTS[i].status == 'Y')
					status = 'tm-event-popup-participant-status-accept';
				else if (this.data.GUESTS[i].status == 'N')
					status = 'tm-event-popup-participant-status-decline';

				grids[i<d?0:1] += '<div class="tm-event-popup-participant"><div class="tm-event-popup-participant-status ' + status + '"></div><div class="tm-event-popup-participant-name"><a class="tm-event-popup-participant-link" href="' + this.data.GUESTS[i].url + '">' + this.data.GUESTS[i].name + '</a></div></div>';
			}

			html += '<td class="tm-event-popup-participants-grid-left">' + grids[0] + '</td><td class="tm-event-popup-participants-grid-right">' + grids[1] + '</td>';

			html += '</tr></tbody></table>';

		}

		html += '</div>';
	}

	html += '</div>';

	return html;
};

BX.CCalendarPlannerEventPopup.prototype.GetButtons = function()
{
	var btns = [], q = BX.proxy(this.Query, this);

	if (this.data.STATUS === 'Q')
	{
		btns.push(new BX.PopupWindowButton({
			text : BX.message('JS_CORE_PL_EVENT_CONFIRM'),
			className : "popup-window-button-create",
			events : {
				click: function() {q('CONFIRM=Y');}
			}
		}));
		btns.push(new BX.PopupWindowButton({
			text : BX.message('JS_CORE_PL_EVENT_REJECT'),
			className : "popup-window-button-cancel",
			events : {
				click: function() {q('CONFIRM=N');}
			}
		}));
	}
	else
	{
		btns.push(new BX.PopupWindowButtonLink({
			text : BX.message('JS_CORE_WINDOW_CLOSE'),
			className : "popup-window-button-link-cancel",
			events : {click : function(e) {this.popupWindow.close();return BX.PreventDefault(e);}}
		}));

	}

	return btns;
};

BX.CCalendarPlannerEventPopup.prototype.Clear = function()
{
	if (this.popup)
	{
		this.popup.close();
		this.popup.destroy();
		this.popup = null;
	}

	this.node = null;
};

BX.CCalendarPlannerEventPopup.prototype.Query = function(str)
{
	BX.ajax({
		method: 'GET',
		url: this.data.URL + '&' + str,
		processData: false,
		onsuccess: BX.proxy(this._Query, this)
	});
};

BX.CCalendarPlannerEventPopup.prototype._Query = function()
{
	this.data = null;
	this.Show();
};


function formatTime(time, bSec, bSkipAmPm)
{
	var mt = '';
	if (BX.isAmPmMode() && !bSkipAmPm)
	{
		if (parseInt(time/3600) > 12)
		{
			time = parseInt(time) - 12*3600;
			mt = ' pm';
		}
		else if (parseInt(time/3600) == 12)
		{
			mt = ' pm';
		}
		else if (parseInt(time/3600) == 0)
		{
			time = parseInt(time) + 12*3600;
			mt = ' am';
		}
		else
			mt = ' am';

		if (!!bSec)
			return parseInt(time/3600) + ':' + BX.util.str_pad(parseInt((time%3600)/60), 2, '0', 'left') + ':' + BX.util.str_pad(time%60, 2, '0', 'left') + mt;
		else
			return parseInt(time/3600) + ':' + BX.util.str_pad(parseInt((time%3600)/60), 2, '0', 'left') + mt;
	}
	else
	{
		if (!!bSec)
			return BX.util.str_pad(parseInt(time/3600), 2, '0', 'left') + ':' + BX.util.str_pad(parseInt((time%3600)/60), 2, '0', 'left') + ':' + BX.util.str_pad(time%60, 2, '0', 'left') + mt;
		else
			return BX.util.str_pad(parseInt(time/3600), 2, '0', 'left') + ':' + BX.util.str_pad(parseInt((time%3600)/60), 2, '0', 'left') + mt;
	}
};

function unFormatTime(time)
{
	var q = time.split(/[\s:]+/);
	if (q.length == 3)
	{
		var mt = q[2];
		if (mt == 'pm' && q[0] < 12)
			q[0] = parseInt(q[0], 10) + 12;

		if (mt == 'am' && q[0] == 12)
			q[0] = 0;

	}
	return parseInt(q[0], 10) * 3600 + parseInt(q[1], 10) * 60;
};

new BX.CCalendarPlannerHandler();
})();