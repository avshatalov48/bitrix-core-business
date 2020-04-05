var phpVars;
if(!phpVars)
{
	phpVars = {
		ADMIN_THEME_ID: '.default',
		LANGUAGE_ID: 'en',
		FORMAT_DATE: 'DD.MM.YYYY',
		FORMAT_DATETIME: 'DD.MM.YYYY HH:MI:SS',
		opt_context_ctrl: false,
		cookiePrefix: 'BITRIX_SM',
		titlePrefix: '',
		bitrix_sessid: '',
		messHideMenu: '',
		messShowMenu: '',
		messHideButtons: '',
		messShowButtons: '',
		messFilterInactive: '',
		messFilterActive: '',
		messFilterLess: '',
		messLoading: 'Loading...',
		messMenuLoading: '',
		messMenuLoadingTitle: '',
		messNoData: '',
		messExpandTabs: '',
		messCollapseTabs: '',
		messPanelFixOn: '',
		messPanelFixOff: '',
		messPanelCollapse: '',
		messPanelExpand: ''
	};
}

function JCAdminCalendar()
{
	var _this = this;
	this.mess = {};
	this.floatDiv = null;
	this.content = null;
	this.dateInitial = new Date();
	this.dateCurrent = null;
	this.dateCreate = new Date();
	this.bTime = false;
	this.bFirst = true;
	this.menu = null;
	this.form = this.field = this.fieldFrom = this.fieldTo = null;

	/* Applying styles */
	var head = document.getElementsByTagName("HEAD");
	if(head)
	{
		var link = document.createElement("LINK");
		link.rel = 'stylesheet';
		link.href = '/bitrix/themes/'+top.phpVars.ADMIN_THEME_ID+'/calendar.css'+(top.jsUtils.IsOpera()? '':'?'+this.mess["css_ver"]);
		head[0].appendChild(link);
	}

	/* Main functions */
	this.Show = function(obj, field, fieldFrom, fieldTo, bTime, serverTime)
	{
		if(this.floatDiv)
			this.Close();

		this.form = top.jsUtils.FindParentObject(obj, 'form');
		if(!this.form)
		{
			//try to find form by input field name
			var input = document.getElementsByName(field);
			for(var i in input)
				if(input[i].form)
				{
					this.form = input[i].form;
					break;
				}
		}
		this.field = field;
		this.fieldFrom = fieldFrom;
		this.fieldTo = fieldTo;
		this.bTime = bTime;

		var difference = serverTime*1000 - (this.dateCreate.valueOf() - this.dateCreate.getTimezoneOffset()*60000);

		this.dateCurrent = this.ParseDate(this.form.elements[this.field].value);
		if(this.dateCurrent)
			this.dateInitial.setTime(this.dateCurrent.valueOf());
		else if(this.bFirst)
		{
			this.dateInitial.setTime((new Date()).valueOf() + difference);
			this.dateInitial.setHours(0, 0, 0);
		}

		var div = document.body.appendChild(document.createElement("DIV"));
		div.id = "calendar_float_div";
		div.className = "calendar-float";
		div.style.position = 'absolute';
		div.style.left = '-1000px';
		div.style.top = '-1000px';
		div.style.zIndex = BX && BX.WindowManager && BX.WindowManager.Get() ? (BX.WindowManager.Get().zIndex+2) : '1100';

		this.hoursSpin = new JCSpinner('hours');
		this.minutesSpin = new JCSpinner('minutes');
		this.secondsSpin = new JCSpinner('seconds');

		div.innerHTML =
			'<div class="title">'+
			'<table cellspacing="0" width="100%">'+
			'	<tr>'+
			'		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById(\'calendar_float_div\'));" id="calendar_float_title">'+this.mess["title"]+'</td><td width="0%"><a class="close" href="javascript:jsAdminCalendar.Close();" title="'+this.mess["close"]+'"></a></td></tr>'+
			'</table>'+
			'</div>'+
			'<div class="content"></div>'+
			'<div class="time" align="center" style="display:'+(this.bTime? 'block':'none')+'">'+
			'<form name="float_calendar_time">'+
			'<table cellspacing="0">'+
			'	<tr>'+
			'		<td>'+this.mess["hour"]+'</td>'+
			'		<td><input type="text" name="hours" value="'+this.Number(this.dateInitial.getHours())+'" size="2" title="'+this.mess['hour_title']+'" onchange="jsAdminCalendar.TimeChange(this);" onblur="jsAdminCalendar.TimeChange(this);"></td>'+
			'		<td>'+this.hoursSpin.Show('jsAdminCalendar.hoursSpin')+'</td>'+
			'		<td>&nbsp;'+this.mess["minute"]+'</td>'+
			'		<td><input type="text" name="minutes" value="'+this.Number(this.dateInitial.getMinutes())+'" size="2" title="'+this.mess['minute_title']+'" onchange="jsAdminCalendar.TimeChange(this);" onblur="jsAdminCalendar.TimeChange(this);"></td>'+
			'		<td>'+this.hoursSpin.Show('jsAdminCalendar.minutesSpin')+'</td>'+
			'		<td>&nbsp;'+this.mess["second"]+'</td>'+
			'		<td><input type="text" name="seconds" value="'+this.Number(this.dateInitial.getSeconds())+'" size="2" title="'+this.mess['second_title']+'" onchange="jsAdminCalendar.TimeChange(this);" onblur="jsAdminCalendar.TimeChange(this);"></td>'+
			'		<td>'+this.hoursSpin.Show('jsAdminCalendar.secondsSpin')+'</td>'+
			'		<td>&nbsp;</td>'+
			'		<td><a title="'+this.mess["set_time"]+'" href="javascript:jsAdminCalendar.CurrentTime('+difference+');" class="time set-time"></a></td>'+
			'		<td><a title="'+this.mess["clear_time"]+'" href="javascript:jsAdminCalendar.ClearTime();" class="time clear-time"></a></td>'+
			'	</tr>'+
			'</table>'+
			'</form>'+
			'</div>'+
			'<table cellspacing="0" class="time-toolbar">'+
			'	<tr>'+
			'		<td align="center"><a id="calendar_time_button" hidefocus="true" tabindex="-1" title="'+(this.bTime? this.mess["time_hide"]:this.mess["time"])+'" href="javascript:jsAdminCalendar.ToggleTime();" class="button '+(this.bTime? 'arrow-up':'arrow-down')+'"></a></td>'+
			'	</tr>'+
			'</table>';

		this.floatDiv = div;
		this.content = top.jsUtils.FindChildObject(this.floatDiv, 'div', 'content');
		this.content.innerHTML = this.GetMonthPage();

		var pos = top.jsUtils.GetRealPos(obj);
		pos["bottom"]+=2;
		pos = top.jsUtils.AlignToPos(pos, div.offsetWidth, div.offsetHeight);

		jsFloatDiv.Show(div, pos["left"], pos["top"], 3);

		setTimeout(function(){top.jsUtils.addEvent(document, "click", _this.CheckClick)}, 10);
		top.jsUtils.addEvent(document, "keypress", _this.OnKeyPress);

		this.bFirst = false;
	}

	this.GetMonthPage = function()
	{
		var aMonths = [this.mess["jan"], this.mess["feb"], this.mess["mar"], this.mess["apr"], this.mess["may"], this.mess["jun"], this.mess["jul"], this.mess["aug"], this.mess["sep"], this.mess["okt"], this.mess["nov"], this.mess["des"]];
		var initYear = this.dateInitial.getFullYear(), initMonth = this.dateInitial.getMonth(), initDay = this.dateInitial.getDate();
		var today = new Date();
		today.setHours(this.dateInitial.getHours(), this.dateInitial.getMinutes(), this.dateInitial.getSeconds());
		var bCurMonth = (today.getFullYear() == initYear && today.getMonth() == initMonth);

		document.getElementById('calendar_float_title').innerHTML = aMonths[initMonth]+', '+initYear;

		var s = '';
		s +=
			'<div style="width:100%;">'+
			'<table cellspacing="0" class="toolbar">'+
			'<tr>'+
				'<td><a title="'+this.mess["prev_mon"]+'" href="javascript:jsAdminCalendar.NavigateMonth('+(initMonth-1)+');" class="button left"></a></td>'+
				'<td width="50%"></td>'+
				'<td><a title="'+(bCurMonth? this.mess["curr_day"]:this.mess["curr"])+'" href="javascript:'+(bCurMonth? 'jsAdminCalendar.InsertDate(\''+today.valueOf()+'\')':'jsAdminCalendar.NavigateToday()')+';" class="button today"></a></td>'+
				'<td><a title="'+this.mess["per_mon"]+'" href="javascript:jsAdminCalendar.InsertPeriod(\''+this.getMonthFirst().valueOf()+'\', \''+this.getMonthLast().valueOf()+'\');" class="button menu">'+aMonths[initMonth]+'</a></td>'+
				'<td><a title="'+this.mess["month"]+'" href="javascript:void(0)" onclick="jsAdminCalendar.MenuMonth(this);" class="button arrow"></a></td>'+
				'<td><a title="'+this.mess["per_year"]+'" href="javascript:jsAdminCalendar.InsertPeriod(\''+this.getYearFirst().valueOf()+'\', \''+this.getYearLast().valueOf()+'\');" class="button menu">'+initYear+'</a></td>'+
				'<td><a title="'+this.mess["year"]+'" href="javascript:void(0)" onclick="jsAdminCalendar.MenuYear(this);" class="button arrow"></a></td>'+
				'<td width="50%"></td>'+
				'<td><a title="'+this.mess["next_mon"]+'" href="javascript:jsAdminCalendar.NavigateMonth('+(initMonth+1)+');" class="button right"></a></td>'+
			'</tr>'+
			'</table>';
		s +=
			'<div class="calendar">'+
			'<div style="width:100%;">'+
			'<table cellspacing="0">'+
			'<tr class="head">'+
			'<td class="week">&nbsp;</td>'+
			'<td>'+this.mess["mo"]+'</td>'+
			'<td>'+this.mess["tu"]+'</td>'+
			'<td>'+this.mess["we"]+'</td>'+
			'<td>'+this.mess["th"]+'</td>'+
			'<td>'+this.mess["fr"]+'</td>'+
			'<td>'+this.mess["sa"]+'</td>'+
			'<td>'+this.mess["su"]+'</td>'+
			'</tr>';

		var firstDate = new Date(initYear, initMonth, 1, this.dateInitial.getHours(), this.dateInitial.getMinutes(), this.dateInitial.getSeconds());
		var firstDay = firstDate.getDay()-1;
		if(firstDay == -1)
			firstDay = 6;

		var date = new Date();
		var bBreak = false;
		for(var i=0; i<6; i++)
		{
			var row = i*7;
			date.setTime(firstDate.valueOf());
			date.setDate(1-firstDay+row);
			if(i > 0 && date.getDate() == 1)
				break;

			var nWeek = this.WeekNumber(date);

			s += '<tr><td class="week"><a title="'+this.mess["per_week"]+'" href="javascript:jsAdminCalendar.InsertPeriod(\''+date.valueOf()+'\', \'';

			date.setTime(firstDate.valueOf());
			date.setDate(1-firstDay+row+6);
			s += date.valueOf()+'\');">'+nWeek+'</a></td>';

			for(var j=0; j<7; j++)
			{
				date.setTime(firstDate.valueOf());
				date.setDate(1-firstDay+row+j);
				var d = date.getDate();

				if(i > 0 && d == 1)
					bBreak = true;

				var sClass = '';
				if(row+j+1 > firstDay && !bBreak)
				{
					if(d == today.getDate() && bCurMonth)
						sClass += ' today';
					if(this.dateCurrent && d == this.dateCurrent.getDate() && initMonth == this.dateCurrent.getMonth() && initYear == this.dateCurrent.getFullYear())
						sClass += ' current';
				}
				if(j==5 || j==6)
					sClass += ' holiday';
				if(!(row+j+1 > firstDay && !bBreak))
					sClass += ' inactive';

				s += '<td'+(sClass != ''? ' class="'+sClass+'"':'')+'>';
				s += '<a title="'+this.mess["date"]+'" href="javascript:jsAdminCalendar.InsertDate(\''+date.valueOf()+'\')">'+d+'</a>';
				s += '</td>';
			}
			s += '</tr>';
			if(bBreak)
				break;
		}
		s +=
			'</table>'+
			'</div>'+
			'</div>'+
			'</div>';
		return s;
	}

	/* Dates arithmetics */
	this.WeekNumber = function(date)
	{
		var firstYearDate = new Date(date.getFullYear(), 0, 1);
		var firstYearDay = firstYearDate.getDay()-1;
		if(firstYearDay == -1)
			firstYearDay = 6;

		var nDays = Math.round((date.valueOf()-firstYearDate.valueOf())/(24*60*60*1000));
		var nWeek = (nDays-(7-firstYearDay))/7+1;
		if(firstYearDay < 4)
			nWeek++;

		if(nWeek > 52)
		{
			firstYearDate = new Date(date.getFullYear()+1, 0, 1);
			firstYearDay = firstYearDate.getDay()-1;
			if(firstYearDay == -1)
				firstYearDay = 6;
			if(firstYearDay < 4)
				nWeek = 1;
		}
		return nWeek;
	}

	this.NavigateToday = function()
	{
		var h = this.dateInitial.getHours(), m = this.dateInitial.getMinutes(), s = this.dateInitial.getSeconds();
		this.dateInitial.setTime((new Date()).valueOf());
		this.dateInitial.setHours(h, m, s);
		this.content.innerHTML = jsAdminCalendar.GetMonthPage();
	}

	this.NavigateMonth = function(mon)
	{
		this.dateInitial.setMonth(mon, 1);
		this.content.innerHTML = jsAdminCalendar.GetMonthPage();
	}

	this.NavigateYear = function(year)
	{
		this.dateInitial.setFullYear(year, this.dateInitial.getMonth(), 1);
		this.content.innerHTML = jsAdminCalendar.GetMonthPage();
	}

	this.getMonthFirst = function()
	{
		var d = new Date();
		d.setTime(this.dateInitial.valueOf());
		d.setDate(1);
		return d;
	}

	this.getMonthLast = function()
	{
		var d = new Date();
		d.setTime(this.dateInitial.valueOf());
		d.setMonth(d.getMonth()+1);
		d.setDate(0);
		return d;
	}

	this.getYearFirst = function()
	{
		var d = new Date();
		d.setTime(this.dateInitial.valueOf());
		d.setMonth(0);
		d.setDate(1);
		return d;
	}

	this.getYearLast = function()
	{
		var d = new Date();
		d.setTime(this.dateInitial.valueOf());
		d.setFullYear(d.getFullYear()+1);
		d.setMonth(0);
		d.setDate(0);
		return d;
	}

	/* Input / Output */
	this.InsertDaysBack = function(input, days)
	{
		if(days != '')
		{
			var d = new Date();
			if(days > 0)
				d.setTime(d.valueOf() - days*24*60*60*1000);
			input.value = this.FormatDate(d, top.phpVars.FORMAT_DATE);
			input.disabled = true;
		}
		else
		{
			input.disabled = false;
			input.value = '';
		}
	}

	this.ValueToString = function(value)
	{
		var date = new Date();
		date.setTime(value);
		if(this.bTime)
		{
			var form = document.forms.float_calendar_time;
			date.setHours(parseInt(form.hours.value, 10));
			date.setMinutes(parseInt(form.minutes.value, 10));
			date.setSeconds(parseInt(form.seconds.value, 10));
		}
		return this.FormatDate(date);
	}

	this.CurrentTime = function(difference)
	{
		var time = new Date();
		time.setTime(time.valueOf() + difference);

		var form = document.forms.float_calendar_time;
		form.hours.value = time.getHours();
		form.minutes.value = time.getMinutes();
		form.seconds.value = time.getSeconds();

		form.hours.onchange();
		form.minutes.onchange();
		form.seconds.onchange();
	}

	this.ClearTime = function()
	{
		var form = document.forms.float_calendar_time;
		form.hours.value = form.minutes.value = form.seconds.value = '00';
	}

	this.InsertDate = function(value)
	{
		this.form.elements[this.field].value = this.ValueToString(value);

		if (BX)
			BX.fireEvent(this.form.elements[this.field], 'change');

		this.Close();
	}

	this.InsertPeriod = function(value1, value2)
	{
		if(this.fieldFrom != '' && this.fieldTo != '')
		{
			this.form.elements[this.fieldFrom].value = this.ValueToString(value1);
			this.form.elements[this.fieldTo].value = this.ValueToString(value2);
		}
		else
			this.form.elements[this.field].value = this.ValueToString(value1);
		this.Close();
	}

	this.Number = function(val)
	{
		return (val < 10? '0'+val : val);
	}

	this.FormatDate = function(date, format)
	{
		var val;
		var str = (format? format : (this.bTime? top.phpVars.FORMAT_DATETIME : top.phpVars.FORMAT_DATE));
		str = str.replace(/YYYY/ig, date.getFullYear());
		str = str.replace(/MMMM/ig, this.Number(date.getMonth()+1));
		str = str.replace(/MM/ig, this.Number(date.getMonth()+1));
		str = str.replace(/MI/ig, this.Number(date.getMinutes()));
		str = str.replace(/M/ig, this.Number(date.getMonth()+1));
		str = str.replace(/DD/ig, this.Number(date.getDate()));
		str = str.replace(/GG/ig, this.Number(date.getHours()));
		str = str.replace(/HH/ig, this.Number(date.getHours()));

		if (BX.isAmPmMode())
		{
			var hour = this.Number(date.getHours());
			var amPm = 'am';
			if (hour > 12)
			{
				hour = hour - 12;
				amPm = 'pm';
			}
			else if (hour == 12)
				amPm = 'pm';

			str = str.replace(/G/ig, hour);
			str = str.replace(/H/ig, hour);
			str = str.replace(/TT/ig, amPm);
			str = str.replace(/T/ig, amPm);
		}

		str = str.replace(/SS/ig, this.Number(date.getSeconds()));
		return str;
	}

	this.ParseDate = function(str)
	{
		var aDate = str.split(/[^\w]/ig);
		var aFormat = top.phpVars.FORMAT_DATE.split(/[^\w]/ig);
		if(aDate.length > aFormat.length)
			aFormat = top.phpVars.FORMAT_DATETIME.split(/[^\w]/ig);

		var i, cnt;
		var aDateArgs=[], aFormatArgs=[];
		for(i = 0, cnt = aDate.length; i < cnt; i++)
			if(top.jsUtils.trim(aDate[i]) != '')
				aDateArgs[aDateArgs.length] = aDate[i];

		for(i = 0, cnt = aFormat.length; i < cnt; i++)
			if(top.jsUtils.trim(aFormat[i]) != '')
				aFormatArgs[aFormatArgs.length] = aFormat[i];
		var aResult={};
		for(i = 0, cnt = aFormatArgs.length; i < cnt; i++)
		{
			if (aDateArgs[i] != undefined && aDateArgs[i].match(/[a-z]/gi) != null)
				{
					aResult[aFormatArgs[i].toUpperCase()] = aDateArgs[i];
				}
			else
				{
					aResult[aFormatArgs[i].toUpperCase()] = parseInt(aDateArgs[i], 10);
				}
		}

		if (aResult['MM'] == undefined && (aResult['M'] != undefined || aResult['MMMM'] != undefined))
		{
			if (aResult['M'] != undefined)
			{
				if (typeof(aResult['M']) == 'string')
				{
					aResult['MM'] = BX.getNumMonth(aResult['M']);
				}
				else
				{
					aResult['MM'] = aResult['M'];
				}
			}
			else
			{
				if (typeof(aResult['MMMM']) == 'string')
				{
					aResult['MM'] = BX.getNumMonth(aResult['MMMM']);
				}
				else
				{
					aResult['MM'] = aResult['MMMM'];
				}
			}
		}

		if(aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0)
		{
			var d = new Date();
			d.setDate(1);
			d.setFullYear(aResult['YYYY']);
			d.setMonth(aResult['MM']-1);
			d.setDate(aResult['DD']);
			d.setHours(0, 0, 0);

			if (aResult['HH'] == undefined && (aResult['H'] != undefined || aResult['G'] != undefined || aResult['G'] != undefined))
			{
				if (aResult['H'] != undefined)
					aResult['HH'] = aResult['H'];
				else if (aResult['G'] != undefined)
					aResult['HH'] = aResult['G'];
				else aResult['HH'] = aResult['GG'];

				if (aResult['TT'] != undefined && aResult['TT'].toLowerCase() == 'pm')
					aResult['HH'] = aResult['HH'] + 12;
				else if (aResult['T'] != undefined && aResult['T'].toLowerCase() == 'pm')
					aResult['HH'] = aResult['HH'] + 12;
			}

			if(!isNaN(aResult['HH']) && !isNaN(aResult['MI']) && !isNaN(aResult['SS']))
			{
				this.bTime = true;
				d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
			}
			return d;
		}
		return null;
	}

	/* Navigation interface */
	this.MenuMonth = function(a)
	{
		var aMonths = [this.mess["jan"], this.mess["feb"], this.mess["mar"], this.mess["apr"], this.mess["may"], this.mess["jun"], this.mess["jul"], this.mess["aug"], this.mess["sep"], this.mess["okt"], this.mess["nov"], this.mess["des"]];
		var items = [];
		var mon = this.dateInitial.getMonth();
		for(var i in aMonths)
			items[i] = {'ICONCLASS': (mon == i? 'checked':''), 'TEXT': aMonths[i], 'ONCLICK': 'jsAdminCalendar.NavigateMonth('+i+')', 'DEFAULT': ((new Date()).getMonth() == i? true:false)};
		this.ShowMenu(a, items);
	}

	this.MenuYear = function(a)
	{
		var items = [];
		var y = this.dateInitial.getFullYear();
		for(var i=0; i<11; i++)
		{
			item_year = y-5+i;
			items[i] = {'ICONCLASS': (y == item_year? 'checked':''), 'TEXT': item_year, 'ONCLICK': 'jsAdminCalendar.NavigateYear('+item_year+')', 'DEFAULT': ((new Date()).getFullYear() == item_year? true:false)};
		}
		this.ShowMenu(a, items);
	}

	this.ShowMenu = function(a, items)
	{
		if(!this.menu)
		{
			this.menu = new PopupMenu('calendar_float_menu');
			this.menu.Create(parseInt(this.floatDiv.style.zIndex)+10, 3);
			this.menu.OnClose = function()
			{
				setTimeout(
					function(){
						if(_this.floatDiv)
							top.jsUtils.addEvent(document, "click", _this.CheckClick);
					}, 10);
				top.jsUtils.addEvent(document, "keypress", _this.OnKeyPress);
			}
		}
		if(this.menu.IsVisible())
			return;

		this.menu.SetItems(items);
		this.menu.BuildItems();
		var pos = top.jsUtils.GetRealPos(a);
		pos["bottom"]+=1;

		top.jsUtils.removeEvent(document, "click", _this.CheckClick);
		top.jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
		this.menu.PopupShow(pos);
	}

	this.ToggleTime = function()
	{
		var div = top.jsUtils.FindChildObject(this.floatDiv, 'div', 'time');
		var a = document.getElementById('calendar_time_button');
		if(div.style.display == 'none')
		{
			div.style.display = 'block';
			a.className = 'button arrow-up';
			a.title = this.mess['time_hide'];
		}
		else
		{
			div.style.display = 'none';
			a.className = 'button arrow-down';
			a.title = this.mess['time'];
		}
		a.blur();
		jsFloatDiv.AdjustShadow(this.floatDiv);
	}

	this.TimeChange = function(input)
	{
		this.bTime = true;

		var val = parseInt(input.value, 10);
		if(isNaN(val))
			val = '00';
		else if(val < 0)
		{
			if(input.name == 'hours')
				val = '23';
			else
				val = '59';
		}
		else if(input.name == 'hours' && val > 23 || val > 59)
			val = '00';
		else
			val = this.Number(val);

		input.value = val;
	}

	/* Window operations: close, drag, move */
	this.Close =  function()
	{
		top.jsUtils.removeEvent(document, "click", _this.CheckClick);
		top.jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);

		jsFloatDiv.Close(this.floatDiv);

		this.floatDiv.parentNode.removeChild(this.floatDiv);
		this.floatDiv = null;
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.Close();
	}

	this.CheckClick = function(e)
	{
		var div = _this.floatDiv;
		if(!div)
			return;

		var windowSize = jsUtils.GetWindowSize();
		var x = e.clientX + windowSize.scrollLeft;
		var y = e.clientY + windowSize.scrollTop;

		var arPos = jsUtils.GetRealPos(div);
		/*region*/
		//var posLeft = parseInt(div.style.left);
		//var posTop = parseInt(div.style.top);
		//var posRight = posLeft + div.offsetWidth;
		//var posBottom = posTop + div.offsetHeight;
		if(x >= arPos.left && x <= arPos.right && y >= arPos.top && y <= arPos.bottom)
			return;

		_this.Close();
	}
}
var jsAdminCalendar = new JCAdminCalendar();

function JCSpinner(name)
{
	var _this = this;
	this.name = name;
	this.mousedown = false;

	this.Show = function(name)
	{
		var s =
			'<table cellspacing="0" class="spin">'+
			'	<tr><td><a hidefocus="true" tabindex="-1" href="javascript:void(0);" onmousedown="'+name+'.Start(1);" class="spin spin-up"></a></td></tr>'+
			'	<tr><td><a hidefocus="true" tabindex="-1" href="javascript:void(0);" onmousedown="'+name+'.Start(-1);" class="spin spin-down"></a></td></tr>'+
			'</table>';
		return s;
	}

	this.Start = function(delta)
	{
		this.mousedown = true;
		top.jsUtils.addEvent(document, "mouseup", _this.MouseUp);
		this.ChangeValue(delta, true);
	}

	this.ChangeValue = function(delta, bFirst)
	{
		if(!this.mousedown)
			return;

		var input = document.forms.float_calendar_time.elements[this.name];
		input.value = parseInt(input.value, 10) + delta;
		input.onchange();
		setTimeout(function(){_this.ChangeValue(delta, false)}, (bFirst? 1000:150));
	}

	this.MouseUp = function()
	{
		_this.mousedown = false;
		top.jsUtils.removeEvent(document, "mouseup", _this.MouseUp);
	}
}

