(function(){

// TODO: Replace jsFloatDiv (from utils.js) by BX.window...

if(window.JCClock)
	return;

function JCClock(config)
{
	if(typeof(BX) != "undefined")
		BX.onCustomEvent("onJCClockInit", [config]);

	this.config = config;
	this.config.AmPmMode = BX.isAmPmMode();
//	this.config.AmPmMode = false;
	this.config.AmPm = '';
	this.deltaHour = 0;
	this.MESS = this.config.MESS;
	this.bCreated = false;
	this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
}

JCClock.prototype = {
	Create: function(cont)
	{
		this.bCreated = true;
		this.pInput = document.getElementById(this.config.inputId);
		this.pIcon = document.getElementById(this.config.iconId);

		if (cont && (cont = document.getElementById(cont)))
		{
			this.bInline = true;
			this.oDiv = cont;
			this.oDiv.className = 'bx-clock-div bx-clock-div-inline' + (this.config.AmPmMode ? '_am_pm' : '');
			this.oDiv.style.position = 'relative';
		}
		else
		{
			var zIndex = 150;
			if(typeof(this.config['zIndex']) != 'undefined')
			{
				zIndex = parseInt(this.config['zIndex']);
				if(zIndex <= 0)
					zIndex = 150;
			}

			this.oDiv = BX(this.config.inputId + '_div');
			if (this.oDiv)
				BX.cleanNode(this.oDiv, true);
			this.oDiv = BX.create('DIV', {props: {className: 'bx-clock-div', id: this.config.inputId + '_div'}, style: { zIndex: zIndex}});
			document.body.appendChild(this.oDiv);
		}

		// Create analog clock
		var clockContDiv = BX.create('DIV', {props: {className: 'bxc-clock-cont bxc-iconkit-c'}});
		this.arrowsContDiv = BX.create('DIV', {props: {className: 'bxc-arrows-cont h0 m0'}});
		this.MACDiv = this.arrowsContDiv.appendChild(BX.create('DIV', {props: {className: 'bxc-mouse-control-cont'}})); // Mouse-Arrow Control Div

		this.arrowsContDiv.appendChild(BX.create('IMG', {props:{src: '/bitrix/images/1.gif', className: 'bxc-min-arr-cont bxc-iconkit-a'}}));
		this.arrowsContDiv.appendChild(BX.create('IMG', {props: {src: '/bitrix/images/1.gif', className: 'bxc-hour-arr-cont bxc-iconkit-a'}}));
		clockContDiv.appendChild(this.arrowsContDiv);
		this.oDiv.appendChild(clockContDiv);

		this.CreateControls();

		this.InitMouseArrowControl();
	},

	CreateControls: function()
	{
		this.ControlsCont = BX.create('DIV', {props: {className: 'bxc-controls-cont'}});
		var
			i,
			arHours = [],
			arMinutes = [],
			_this = this;

		if (this.config.AmPmMode)
		{
			for (i = 0; i < 12; i++)
				arHours.push(this.Hour2Str(i, true));
		}
		else
		{
			for (i = 0; i < 24; i++)
				arHours.push(this.Int2Str(i));
		}

		for (i = 0; i < 60; i += this.config.step)
			arMinutes[i] = (this.Int2Str(i));

		var oSelH = this.CreateSelect(arHours, 1, this.MESS.Hours);
		this.hourSelect = oSelH.pSelect;
		var oSelM = this.CreateSelect(arMinutes, this.config.step, this.MESS.Minutes);
		this.minSelect = oSelM.pSelect;

		this.hourSelect.onchange = function(){
			if (!this.value || isNaN(this.value))
				this.value = 0;
			_this.SetTime(this.value, _this.curMin, true);
		};
		this.minSelect.onchange = function(){
			if (!this.value || isNaN(this.value))
				this.value = 0;
			_this.SetTime(_this.curHour, this.value, true);
		};
		this.minSelect.onfocus = function(){_this.lastArrow = 'min';};
		this.hourSelect.onfocus = function(){_this.lastArrow = 'hour';};

		if (!this.bInline)
		{
			var insBut = BX.create('INPUT', {props: {type: 'button', value: this.MESS.Insert}});
			insBut.onclick = function(){_this.Submit();};

			// Close button
			var closeImg = BX.create('IMG', {props: {src: '/bitrix/images/1.gif', className: 'bxc-close bxc-iconkit-c', title: this.MESS.Close}});
			closeImg.onclick = function(){_this.Close();};
		}

		var span = BX.create('SPAN', {props: {className: 'double-dot'}});
		span.innerHTML = ':';
		oSelH.pWnd.style.marginLeft = '11px';
		this.ControlsCont.appendChild(oSelH.pWnd);
		this.ControlsCont.appendChild(span);
		this.ControlsCont.appendChild(oSelM.pWnd);

		if (this.config.AmPmMode)
		{
			this.AmPm = BX.create('SPAN', {props: {className: 'bxc-am-pm', title: 'a.m.'}});
			this.AmPm.innerHTML = 'am';

			this.AmPm.onclick = function(){
				if (this.title == 'a.m.')
				{
					this.title = 'p.m.';
					this.innerHTML = 'pm';

					_this.config.AmPm = ' pm';
				}
				else
				{
					this.title = 'a.m.';
					this.innerHTML = 'am';
					_this.config.AmPm = ' am';
				}
				_this.SetTime(_this.curHour, _this.curMin, true);
			};


			this.ControlsCont.appendChild(this.AmPm);
		}

		if (!this.bInline)
		{
			this.ControlsCont.appendChild(insBut);
			this.ControlsCont.appendChild(closeImg);

			this.pTitle = this.ControlsCont.appendChild(BX.create('DIV', {props:{className: 'bxc-title'}}));
			this.pTitle.onmousedown = function(e) {jsFloatDiv.StartDrag(e, _this.oDiv);_this.bRecalculateCoordinates = true;};
		}

		this.oDiv.appendChild(this.ControlsCont);
	},

	CalculateCoordinates: function()
	{
		// GetPosition
		var pos = BX.pos(this.oDiv);
		this.top = pos.top;
		this.left = pos.left;

		this.centerX = pos.left + (this.bInline ? JCClock.getOption("centerXInline", 55) : JCClock.getOption("centerX", 55));
		this.centerY = pos.top + (this.bInline ? JCClock.getOption("centerYInline", 55) : JCClock.getOption("centerY", 71));

		var
			_this = this,

			mal = JCClock.getOption("minuteLength", 32), // minute arrow length (in px)
			hal = JCClock.getOption("hourLength", 25), // hour arrow length (in px)
			x = this.centerX, y = this.centerY, // Coordinates of center
			i, deg, xi, yi, abs_x, abs_y, xi1, yi1, abs_x1, abs_y1,
			delta = JCClock.getOption("inaccuracy", 8); // inaccuracy

		this.arHourCoords = [];
		this.bJumpByMinArrow30 = false;

		for(i = 0; i < 12; i++)
		{
			deg = (i * 30) * Math.PI / 180;
			xi = Math.round(hal * Math.sin(deg));
			yi = Math.round(hal * Math.cos(deg));
			abs_x = x + xi;
			abs_y = y - yi;

			xi1 = Math.round(16 * Math.sin(deg));
			yi1 = Math.round(16 * Math.cos(deg));
			abs_x1 = x + xi1;
			abs_y1 = y - yi1;

			this.arHourCoords[i] = {
				x : abs_x,
				y : abs_y,
				x_min: abs_x - delta, x_max: abs_x + delta,
				y_min: abs_y - delta, y_max: abs_y + delta,
				x_min1: abs_x1 - delta, x_max1: abs_x1 + delta,
				y_min1: abs_y1 - delta, y_max1: abs_y1 + delta
			};
		}

		this.arMinCoords = {};
		for(i = 0; i < 12; i++)
		{
			deg = (i * 30) * Math.PI / 180;
			xi = Math.round(mal * Math.sin(deg));
			yi = Math.round(mal * Math.cos(deg));
			abs_x = x + xi;
			abs_y = y - yi;

			xi1 = Math.round(18 * Math.sin(deg));
			yi1 = Math.round(18 * Math.cos(deg));
			abs_x1 = x + xi1;
			abs_y1 = y - yi1;

			this.arMinCoords[i * 5] =
			{
				x : abs_x,
				y : abs_y,
				x_min: abs_x - delta, x_max: abs_x + delta,
				y_min: abs_y - delta, y_max: abs_y + delta,
				x_min1: abs_x1 - delta, x_max1: abs_x1 + delta,
				y_min1: abs_y1 - delta, y_max1: abs_y1 + delta
			};
		}
		this.bRecalculateCoordinates = false;
	},

	Show: function(cont)
	{
		if (!this.bCreated)
			this.Create(cont);

		this.lastArrow = 'min';
		var startValue = this.pInput.value.toString();
		if (startValue.indexOf(':') == -1)
		{
			if (this.config.initTime.length <= 0 || this.config.initTime.indexOf(':') == -1)
				startValue = new Date().getHours() + ':' + new Date().getMinutes();
			else
				startValue = this.config.initTime;
		}

		startValue = this.parseTime(startValue);
		this.SetTime(startValue.h, startValue.m);

		if (this.bInline)
		{
			this.oDiv.style.display = 'block';
		}
		else
		{
			var alignItem = this.pIcon || this.pInput;
			var pos = this.AlignToPos(BX.pos(alignItem));
			this.top = pos.top;
			this.left = pos.left;

			jsFloatDiv.Show(this.oDiv, this.left, this.top);
			this.oDiv.style.display = 'block';
			jsFloatDiv.AdjustShadow(this.oDiv);
		}

		var _this = this;
		setTimeout(function() {
			_this.CalculateCoordinates();
		}, 20);

		window['_bxc_onmousedown' + this.config.inputId] = function(e){_this.CheckClick(e);};
		window['_bxc_onkeypress' + this.config.inputId] = function(e){_this.OnKeyDown(e);};

		setTimeout(function(){BX.bind(document, "mousedown", window['_bxc_onmousedown' + _this.config.inputId])}, 10);
		BX.bind(document, "keypress", window['_bxc_onkeypress' + this.config.inputId]);
	},

	AlignToPos: function(pos)
	{
		var
			h = JCClock.getOption("popupHeight", 250),
			x = pos.left,
			y = pos.top - h,
			scroll = BX.GetWindowScrollPos();

		if (scroll.scrollTop > y || y < 0)
			y = pos.top + 40;

		return {left: x, top: y};
	},

	Close: function()
	{
		if (this.bInline)
			return;

		BX.unbind(document, "mousedown", window['_bxc_onmousedown' + this.config.inputId]);
		BX.unbind(document, "keypress", window['_bxc_onkeypress' + this.config.inputId]);
		jsFloatDiv.Close(this.oDiv);
		this.oDiv.style.display = 'none';
	},

	Submit: function()
	{
		var mt = this.config.AmPmMode ? this.config.AmPm : '';
		this.pInput.value = this.Hour2Str(this.curHour, this.config.AmPmMode) + ':' + this.Int2Str(this.curMin) + mt;
		BX.fireEvent(this.pInput, 'change');

		if (!this.bInline)
			this.Close();
	},

	SetTime: function(h, m, bDontSetDigClock, bJumpByHourArrow)
	{
		//console.info(h, m);
		//console.trace();
		h = parseInt(h, 10);
		if (this.config.AmPmMode)
		{
			if (h >= 12)
			{
				if (h > 12)
					h = h - 12;
				if (this.config.AmPm == '')
					this.config.AmPm = ' pm';
			}
			else if (this.config.AmPm == '')
			{
				this.config.AmPm = ' am';
			}
			if (h < 1 || h > 12)
			{
				h = 12;
				this.config.AmPm = ' am';
			}
		}
		else
			if (h < 0 || h > 23)
				h = 0;

		m = parseInt(m, 10);
		var step = this.config.step;
		if (Math.round(m / step) != m / step)
			m = Math.round(m / step) * step;
		if (m < 0 || m > 59)
			m = 0;

		if (!bJumpByHourArrow)
			this.deltaHour = h >= 12 ? 12 : 0;

		this.curMin = m;
		this.curHour = h;

		if (this.pTitle)
			this.pTitle.innerHTML = this.Hour2Str(h, this.config.AmPmMode) + ':' + this.Int2Str(m);

		this.SetTimeAn(h, m);
		if (!bDontSetDigClock)
			this.SetTimeDig(h, m);

		if (this.bInline)
			this.Submit();
	},

	SetTimeAnH: function(h, m)
	{
		if (h == 0)
		{
			if(this.curHour < 12 && this.curHour > 6)
				this.deltaHour = 12;
			if(this.curHour < 24 && this.curHour > 18)
				this.deltaHour = 0;
		}

		if (this.curHour == 0 && h == 11)
		{
			h = 23;
			this.deltaHour = 12;
		}
		else if (this.curHour == 12 && h == 11)
		{
			h = 11;
			this.deltaHour = 0;
		}
		else
		{
			h += this.deltaHour;
		}
		this.SetTime(h, m, false, true);
	},

	SetTimeAnM: function(h, m)
	{
		m = parseInt(m, 10);
		var step = this.config.step;
		if (Math.round(m / step) != m / step)
			m = Math.round(m / step) * step;
		if (m < 0 || m > 59)
			m = 0;

		if (m == 30)
		{
			this.bJumpByMinArrow30 = true;
		}
		else if (this.bJumpByMinArrow30 && m == 0)
		{
			if (this.curMin > 30 && this.curMin < 59)
			{
				this.bJumpByMinArrow30 = false;
				this.SetTime(++h, m);
				return;
			}
			if (this.curMin > 0 && this.curMin < 30)
			{
				this.bJumpByMinArrow30 = false;
				if (h == 0)
					h = 24;
				this.SetTime(--h, m);
				return;
			}
		}
		this.SetTime(h, m);
	},

	SetTimeAn: function(h, m)
	{
		h = parseInt(h, 10);

		if (isNaN(h))
			h = 0;
		m = parseInt(m, 10);
		if (isNaN(m))
			m = 0;

		if (h >= 12)
			h -= 12;
		var cn = 'bxc-arrows-cont';
		if (h * 5 == m)
			cn += ' hideh hm' + h;
		else
			cn += ' h' + h + ' m' + m;
		this.arrowsContDiv.className = cn;
	},

	CreateSelect: function(arValues, step, title)
	{
		var bAmPmMode = !!this.config.AmPmMode;
		var select = BX.create('INPUT', {props: {type: 'text', className: 'bxc-cus-sel', size: "1", title: title}});
		var spinStop = function(d)
		{
			select._bxmousedown = false;
			if (window.bxinterval)
				clearTimeout(window.bxinterval);
		};
		var spinChange = function(d)
		{
			if (!select._bxmousedown)
			{
				spinStop();
				return;
			}

			var k = parseInt(select.value, 10);

			if (isNaN(k))
				k = 0;

			k = k + d;

			if (k >= arValues.length)
				k = 0;
			else if (k < 0)
				k = arValues.length - 1;

			if (typeof arValues[k] == 'undefined')
			{
				k -= d; // return old value
				select.value = k - (d > 0 ? 1 : -1); // one step in reverse direction
				spinChange(d); // try again
				return;
			}
			else
			{
				// hacks for AM/PM 00->12
				if (select.value == '11' && arValues[k] == '00' || select.value == '01' && arValues[k] == '00' && bAmPmMode)
				{
					select.value = '12';
				}
				else if (select.value == '12' && arValues[k] == '00')
				{
					select.value = '01';
				}
				else
					select.value = arValues[k];
				select.onchange();
			}

			if (window.bxinterval)
				clearTimeout(window.bxinterval);
			window.bxinterval = setTimeout(function(){spinChange(d);}, 100);
		};
		var spinStart = function(d)
		{
			if (window.bxinterval)
				spinStop();
			select._bxmousedown = true;
			BX.bind(document, "mouseup", spinStop);

			spinChange(d);

			if (window.bxinterval)
				clearTimeout(window.bxinterval);
			window.bxinterval = setTimeout(function(){spinChange(d);}, 800);
		};
		select.onkeydown = function(e)
		{
			if(!e) e = window.event;
			if(!e) return;
			if(e.keyCode == 38) // Up
			{
				spinStart(step);
				spinStop();
			}
			else if (e.keyCode == 40) // Down
			{
				spinStart(-step);
				spinStop();
			}
		};

		var
			tbl = BX.create('TABLE', {props: {className: 'bxc-cus-sel-tbl'}}),
			r = tbl.insertRow(-1),
			c = r.insertCell(-1);

		//tbl.border = "1";
		//c.setAttribute("rowSpan", "2");
		c.rowSpan = 2;
		c.appendChild(select);


		c = r.insertCell(-1);
		c.appendChild(BX.create('IMG', {props: {src: '/bitrix/images/1.gif', className: 'bxc-slide-up bxc-iconkit-c'}}));
		c.title = this.MESS.Up;
		c.className = 'bxc-pointer';
		c.onmousedown = function(){spinStart(step)};
		c = tbl.insertRow(-1).insertCell(-1);
		c.appendChild(BX.create('IMG', {props: {src: '/bitrix/images/1.gif', className: 'bxc-slide-down bxc-iconkit-c'}}));
		c.title = this.MESS.Down;
		c.className = 'bxc-pointer';
		c.onmousedown = function(){spinStart(-step)};

		return {pSelect: select, pWnd: tbl};
	},

	SetTimeDig: function(h, m)
	{
		this.hourSelect.value = this.Hour2Str(h, this.config.AmPmMode);
		this.minSelect.value = this.Int2Str(m);

		if (this.config.AmPmMode)
		{
			if (this.config.AmPm == ' pm')
			{
				this.AmPm.title = 'p.m.';
				this.AmPm.innerHTML = 'pm';
			}
			else
			{
				this.AmPm.title = 'a.m.';
				this.AmPm.innerHTML = 'am';
			}
		}

		if (this.bInline)
			this.Submit();
	},

	InitMouseArrowControl: function()
	{
		var _this = this;
		this.MACDiv.onmousedown = function(e){_this.MACMouseDown(e)};
		this.MACDiv.ondrag = BX.False;
		this.MACDiv.onselectstart = BX.False;
		this.MACDiv.style.MozUserSelect = 'none';
	},

	MACMouseDown: function(e)
	{
		if (this.bRecalculateCoordinates)
			this.CalculateCoordinates();

		if(!e) e = window.event;
		var
			_this = this,
			ar,
			windowSize = BX.GetWindowSize(),
			mouseX = e.clientX + windowSize.scrollLeft,
			mouseY = e.clientY + windowSize.scrollTop;

		this.ddMode = false;
		ar = this.arMinCoords[this.curMin];
		if ((mouseX > ar.x_min && mouseX < ar.x_max && mouseY > ar.y_min && mouseY < ar.y_max) ||
			(mouseX > ar.x_min1 && mouseX < ar.x_max1 && mouseY > ar.y_min1 && mouseY < ar.y_max1))
		{
			this.ddMode = 'min';
			this.lastArrow = 'min';
		}

		if (!this.ddMode)
		{
			ar = this.arHourCoords[this.curHour >= 12 ? this.curHour - 12 : this.curHour];
			if ((mouseX > ar.x_min && mouseX < ar.x_max && mouseY > ar.y_min && mouseY < ar.y_max) ||
				(mouseX > ar.x_min1 && mouseX < ar.x_max1 && mouseY > ar.y_min1 && mouseY < ar.y_max1))
			{
				this.ddMode = 'hour';
				this.lastArrow = 'hour';
			}
		}

		if (this.ddMode === false)
		{
			var i,
				dist,
				min = 1000,
				min_ind = 0;

			if (this.lastArrow == 'hour')
			{
				for(i = 0; i < 12; i++)
				{
					dist = this.GetDistance(this.arHourCoords[i].x, this.arHourCoords[i].y, mouseX, mouseY);
					if (dist <= min)
					{
						min = dist;
						min_ind = i;
					}
				}
				this.SetTimeAnH(min_ind, this.curMin);
			}
			else if (this.lastArrow == 'min')
			{
				for(i = 0; i < 12; i++)
				{
					dist = this.GetDistance(this.arMinCoords[i * 5].x, this.arMinCoords[i * 5].y, mouseX, mouseY);
					if (dist <= min)
					{
						min = dist;
						min_ind = i;
					}
				}
				this.SetTimeAnM(this.curHour, min_ind * 5);
			}
			return;
		}

		this.ControlsCont.style.zIndex = '145'; // Put controls div under MACDiv
		this.MACDiv.onmousemove = function(e){_this.MACMouseMove(e)};
		this.MACDiv.onmouseup = function(e){_this.MACMouseUp(e)};
	},

	MACMouseMove: function(e)
	{
		if (!this.ddMode)
		{
			this.StopDD();
			return;
		}

		if(!e) e = window.event;
		var i,
			dist,
			min = 1000,
			min_ind = 0,
			windowSize = BX.GetWindowSize(),
			mouseX = e.clientX + windowSize.scrollLeft,
			mouseY = e.clientY + windowSize.scrollTop;

		if (this.ddMode == 'hour')
		{
			for(i = 0; i < 12; i++)
			{
				dist = this.GetDistance(this.arHourCoords[i].x, this.arHourCoords[i].y, mouseX, mouseY);
				if (dist <= min)
				{
					min = dist;
					min_ind = i;
				}
			}
			this.SetTimeAnH(min_ind, this.curMin);
		}
		else if (this.ddMode == 'min')
		{
			for(i = 0; i < 12; i++)
			{
				dist = this.GetDistance(this.arMinCoords[i * 5].x, this.arMinCoords[i * 5].y, mouseX, mouseY);
				if (dist <= min)
				{
					min = dist;
					min_ind = i;
				}
			}
			this.SetTimeAnM(this.curHour, min_ind * 5);
		}
	},

	GetDistance: function(x1, y1, x2, y2)
	{
		return Math.round(Math.sqrt((Math.pow(x1 - x2, 2) + Math.pow(y1 - y2, 2))));
	},

	MACMouseUp: function(e)
	{
		this.StopDD();
	},

	StopDD: function()
	{
		this.ddMode = false;
		this.ControlsCont.style.zIndex = '156';  // Put controls div over MACDiv
		this.MACDiv.onmousemove = null;
		this.MACDiv.onmouseup = null;
		return false;
	},

	Int2Str: function(i)
	{
		i = parseInt(i, 10);
		if (isNaN(i))
			i = 0;
		return i < 10 ? '0' + i.toString() : i.toString();
	},

	Hour2Str: function(i, ampm)
	{
		i = parseInt(i, 10);
		if (isNaN(i))
			i = 0;
		return i < 10 && !ampm ? '0' + i.toString() : i.toString();
	},

	CheckClick: function(e)
	{
		if (this.bRecalculateCoordinates || this.bInline)
			return;
		if(!e)
			e = window.event;
		if(!e)
			return;

		var target;
		if (e.target)
			target = e.target;
		else if (e.srcElement)
			target = e.srcElement;
		if (target.nodeType == 3)
			target = target.parentNode;

		if (target != this.oDiv && !BX.findParent(target, {attribute: {id: this.oDiv.id}}))
			this.Close();
	},

	OnKeyDown: function(e)
	{
		if(!e) e = window.event;
		if(!e) return;
		if(e.keyCode == 27)
			this.Close();
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
};


JCClock.options = {};
JCClock.setOptions = function(options)
{
	if (!options || typeof(options) != "object")
		return;

	for (var option in options)
		JCClock.options[option] = options[option];
};

JCClock.getOption = function(option, defaultValue)
{
	if (typeof(JCClock.options[option]) != "undefined")
		return JCClock.options[option];
	else
		return defaultValue;
};

window.JCClock=JCClock;})();
