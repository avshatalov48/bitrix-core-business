(function(window){
if (window.BX.timer) return;

var timers = [],
	timeout = 200,
	obTimer = null,
	last_index = 0;

BX.timer = function(container, params)
{
	params = params || {};
	if (BX.type.isString(container) || BX.type.isElementNode(container))
		params.container = container;
	else if (typeof (container) == "object")
		params = container;

	if (!params.container)
		return false;

	var ob = new BX.CTimer(params);
	BX.timer.start(ob);

	if (null == obTimer)
	{
		obTimer = setInterval(_RunTimer, timeout);
		BX.garbage(BX.timer.clear);
	}

	return ob;
}

BX.timer.stop = function(timer)
{
	timers[timer.TIMER_INDEX] = null;
}

BX.timer.start = function(timer)
{
	timer.TIMER_INDEX = last_index;
	timers[last_index++] = timer;
}

BX.timer.clock = function(cont, dt)
{
	return BX.timer({container: cont, dt: dt});
}

BX.timer.clear = function()
{
	clearInterval(obTimer);
	timers = null;
}

BX.timer.registerFormat = function(format_name, format_handler)
{
	BX.CTimer.prototype.formatValueHandlers[format_name] = format_handler
}

BX.timer.getHandler = function(format_name)
{
	return BX.CTimer.prototype.formatValueHandlers[format_name];
}

BX.CTimer = function(params, index)
{
	this.container = params.container;
	this.from = params.from ? parseInt(params.from.valueOf()) : null;
	this.to = params.to ? parseInt(params.to.valueOf()) : null;
	this.index = index;

	this.dt = parseInt(params.dt);
	if (isNaN(this.dt))
		this.dt = 0;

	this.display = params.display || (BX.isAmPmMode() ? 'clock_am_pm' : 'clock'); // other variants - 'simple', 'worktime'

	this.accuracy = params.accuracy || 60; // default timing acccuracy is 1 minute

	// if (this.from)
		// this.from = new Date(parseInt(this.from.valueOf() / this.accuracy) * this.accuracy);
	// if (this.to)
		// this.to = new Date(parseInt(this.to.valueOf() / this.accuracy) * this.accuracy);

	this.callback = this.from ? this._callback_from : (this.to ? this._callback_to : this._callback);
	this.callback_finish = params.callback_finish;

	this.formatValue = this.formatValueHandlers.clock;

	this.bInited = false;
	BX.ready(BX.delegate(this.Init, this));
}

BX.CTimer.prototype.Init = function()
{
	if (this.bInited)
		return;

	this.container = BX(this.container);
	this.container_value_fld = this.container.tagName.toUpperCase() == 'INPUT' ? 'value' : 'innerHTML';
	if (this.container_value_fld == 'value' && (this.display == 'clock' || this.display == 'clock_am_pm'))
	{
		if (this.display == 'clock')
		{
			this.display = 'simple';
		}
		else if (this.display == 'clock_am_pm')
		{
			this.display = 'simple_am_pm';
		}
	}

	this.formatValue = this.formatValueHandlers[this.display] ? this.formatValueHandlers[this.display] : this.formatValueHandlers.clock;

	this.bInited = true;
}

BX.CTimer.prototype.setFrom = function(from)
{
	if (!this.from) return;
	this.from = from;
}

BX.CTimer.prototype.setTo = function(to)
{
	if (!this.to) return;
	this.to = to;
}

BX.CTimer.prototype._callback = function(date)
{
	if (this.dt !== 0)
		var date = new Date(date.valueOf() + this.dt);

	this.setValue(this.formatValue(date.getHours(), date.getMinutes(), date.getSeconds()));
}

BX.CTimer.prototype._callback_from = function(date)
{
	var diff = (date.valueOf() - this.from.valueOf() + this.dt)/1000;
	this.setValue(
		this.formatValue(
			parseInt(diff / 3600),
			parseInt((diff % 3600) / 60),
			parseInt(diff % 60)
		)
	);
}

BX.CTimer.prototype._callback_to = function(date)
{
	var diff = (this.to.valueOf() - date.valueOf())/1000;
	if (diff > 0)
	{
		this.setValue(
			this.formatValue(
				parseInt(diff / 3600),
				parseInt((diff % 3600) / 60),
				parseInt(diff % 60)
			)
		);
	}
	else
	{
		this.Finish();
	}
}

BX.CTimer.prototype.formatValueHandlers = {
	clock: function(h, m, s)
	{
		var d = '<span class="bx-timer-semicolon">:</span>';

		return BX.util.str_pad(h, 2, '0', 'left')
			+ d
			+ (this.accuracy >= 3600
				? '00'
				: BX.util.str_pad(m, 2, '0', 'left'))
			+ (this.accuracy >= 60
				? ''
				:
				(d + BX.util.str_pad(s, 2, '0', 'left'))
			);
	},
	clock_am_pm: function(h, m, s)
	{
		var mt = 'am';
		var d = '<span class="bx-timer-semicolon">:</span>';

		if (h > 12)
		{
			h = h - 12;
			mt = 'pm';
		}
		else if (h == 0)
		{
			h = 12;
			mt = 'am';
		}
		else if (h == 12)
		{
			mt = 'pm';
		}

		return h
			+ d
			+ (this.accuracy >= 3600
				? '00'
				: BX.util.str_pad(m, 2, '0', 'left'))
			+ (this.accuracy >= 60
				? ''
				:
				(d + BX.util.str_pad(s, 2, '0', 'left'))
			)
			+ ' ' + mt;
	},
	simple: function(h, m, s)
	{
		return BX.util.str_pad(h, 2, '0', 'left')
			+ ':'
			+ (this.accuracy >= 3600
				? '00'
				: BX.util.str_pad(m, 2, '0', 'left'))

			+ (this.accuracy >= 60
				? ''
				:
				(':' + BX.util.str_pad(s, 2, '0', 'left'))
			);
	},
	simple_am_pm: function(h, m, s)
	{
		var mt = 'am';

		if (h > 12)
		{
			h = h - 12;
			mt = 'pm';
		}
		else if (h == 0)
		{
			h = 12;
			mt = 'am';
		}
		else if (h == 12)
		{
			mt = 'pm';
		}
		return h
			+ ':'
			+ (this.accuracy >= 3600
				? '00'
				: BX.util.str_pad(m, 2, '0', 'left'))

			+ (this.accuracy >= 60
				? ''
				:
				(':' + BX.util.str_pad(s, 2, '0', 'left'))
			) + ' ' + mt;
	},
	worktime: function(h, m, s)
	{
		return h + BX.message('JS_CORE_H') + ' '
			+ (this.accuracy >= 3600
				? ''
				: m + BX.message('JS_CORE_M')
					+ (this.accuracy >= 60
						? ''
						: ' ' + s + BX.message('JS_CORE_S')
					)
				);
	},
	worktime_short: function(h, m, s)
	{
		return BX.util.rtrim((h > 0 ? h + BX.message('JS_CORE_H') + ' ' : '')
			+ (m > 0 && this.accuracy < 3600 ? m + BX.message('JS_CORE_M') + ' ' : '')
			+ (this.accuracy >= 60
				? ''
				: (s > 0 ? s + BX.message('JS_CORE_S') : '')
			));
	}
}

BX.CTimer.prototype.setValue = function(value)
{
	if (this.bInited)
	{
		if (value != this._last_value)
			this.container[this.container_value_fld] = value;

		this._last_value = value;
	}
}

BX.CTimer.prototype.Finish = function()
{
	BX.timer.stop(this);

	if (this.callback_finish)
		this.callback_finish.apply(this);

	BX.cleanNode(this.container.parentNode);
}

function _RunTimer()
{
	var current_moment = new Date();

	for (var i=0,len=last_index;i<len;i++)
	{
		if (timers[i] && timers[i].callback)
			timers[i].callback.apply(timers[i], [current_moment]);
	}

	current_moment = null;
}
})(window)
