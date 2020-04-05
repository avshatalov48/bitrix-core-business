(function(window){
if (BX.CAutoSave && top.BX.CAutoSave) return;
/******************************* AUTOSAVE *********************************/

BX.CAutoSave = function(params)
{
	this.FORM_NAME = params.form;
	this.FORM_MARKER = params.form_marker;
	this.FORM_ID = params.form_id;

	this.PERIOD = params.period || [4001, 20990];

	this.RESTORE_DATA = null;
	this.TIMERS = [null, null];

	this.bInited = false;
	this.bRestoreInProgress = false;

	this.DISABLE_STANDARD_NOTIFY = params.DISABLE_STANDARD_NOTIFY;
	this.NOTIFY_CONTEXT = null;

	BX.ready(BX.defer(this.Prepare, this));
	BX.garbage(BX.delegate(this.Clear, this));

	if (
		BX.type.isNotEmptyString(this.FORM_MARKER)
		&& BX(this.FORM_MARKER)
	)
	{
		var formMarker = BX(this.FORM_MARKER);
		if (
			BX(formMarker.form)
			&& BX.type.isNotEmptyString(formMarker.form.name)
		)
		{
			BX.addCustomEvent(window.top, 'onExtAutoSaveReset_' + formMarker.form.name, BX.proxy(this.Reset, this));
		}
	}
};

BX.CAutoSave.prototype.Prepare = function()
{
	var i;

	if (this.FORM_NAME && BX.type.isString(this.FORM_NAME))
		this.FORM = document.forms[this.FORM_NAME];
	else if (this.FORM_MARKER && BX.type.isString(this.FORM_MARKER))
		this.FORM = (BX(this.FORM_MARKER)||{form:null}).form;

	if (!BX.type.isDomNode(this.FORM))
		return;

	this.FORM.BXAUTOSAVE = this;
	BX.bind(this.FORM, 'submit', BX.proxy(this.ClearTimers, this));

	for (i=0; i<this.FORM.elements.length; i++)
	{
		this.RegisterInput(this.FORM.elements[i]);
	}

	setTimeout(BX.delegate(this._PrepareAfter, this), 10);
};

BX.CAutoSave.prototype.RegisterInput = function(inp)
{
	if (BX.type.isString(inp))
	{
		setTimeout(BX.delegate(function(){this.RegisterInput(this.FORM[inp] || BX(inp))}, this), 10);
	}
	else if (BX.type.isDomNode(inp))
	{
		if (
			inp.type != 'button'
			&& inp.type != 'submit'
			&& inp.type != 'reset'
			&& inp.type != 'image'
			&& inp.type != 'hidden'
		)
		{
			BX.bind(inp, 'change', BX.proxy(this.Init, this));

			if (inp.type == 'text' || inp.type == 'textarea')
			{
				BX.bind(inp, 'keyup', BX.proxy(this.Init, this));
			}

			if (inp.type == 'checkbox' || inp.type == 'radio')
			{
				BX.bind(inp, 'click', BX.proxy(this.Init, this));
			}
		}
	}
};

BX.CAutoSave.prototype.UnRegisterInput = function(inp)
{
	if (BX.type.isString(inp))
		inp = this.FORM[inp] || BX(inp);
	if (BX.type.isDomNode(inp))
	{
		BX.unbind(inp, 'change', BX.proxy(this.Init, this));
		BX.unbind(inp, 'keyup', BX.proxy(this.Init, this));
		BX.unbind(inp, 'click', BX.proxy(this.Init, this));
	}
};

BX.CAutoSave.prototype._PrepareAfter = function()
{
	// we can set other "target events" here
	BX.onCustomEvent(this.FORM, 'onAutoSavePrepare', [this, BX.proxy(this.Init, this)]);

	if (this.RESTORE_DATA)
	{
		var id = this.FORM.name || Math.random();
		BX.addCustomEvent('onExtAutoSaveRestoreClick_' + id, BX.proxy(this.Restore, this));

		var o = this._NotifyContext();
		if (o)
		{
			o.Notify(BX.message('AUTOSAVE') + ' <a href="javascript:void(0)" onclick="BX.CAutoSave.Restore(\'' + BX.util.urlencode(id) + '\', this); return false;">' + BX.message('AUTOSAVE_R') + '</a>');
		}

		// may be useful sometimes
		BX.onCustomEvent(this.FORM, 'onAutoSaveRestoreFound', [this, this.RESTORE_DATA]);
	}
};

BX.CAutoSave.prototype.Init = function()
{
	// if (this.bRestoreInProgress)
		// return;

	if (this.TIMERS[0])
	{
		clearTimeout(this.TIMERS[0]);
		this.TIMERS[0] = null;
	}

	this.TIMERS[0] = setTimeout(BX.proxy(this.TimerHandler, this), this.PERIOD[0]);

	if (!this.TIMERS[1])
	{
		this.TIMERS[1] = setInterval(BX.proxy(this.Save, this), this.PERIOD[1]);
	}

	// may also be useful
	BX.onCustomEvent(this.FORM, 'onAutoSaveInit', [this]);

	return true;
};

BX.CAutoSave.prototype.TimerHandler = function()
{
	if (this.TIMERS[1])
	{
		clearInterval(this.TIMERS[1]);
		this.TIMERS[1] = null;
	}
	this.Save();
};

BX.CAutoSave.prototype.Save = function()
{
	if (this.FORM && BX.isNodeInDom(this.FORM))
	{
		var i, j, el, data = {autosave_id: this.FORM_ID, form_data: {}};

		for (i=0; i<this.FORM.elements.length; i++)
		{
			el = this.FORM.elements[i];

			if (el.name && el.name != 'sessid' && el.name != 'lang' && el.name != 'autosave_id')
			{
				var n = el.name, v = '', t = el.type.toLowerCase();

				switch (t)
				{
					case 'button':
					case 'submit':
					case 'reset':
					case 'image':
					case 'file':
					case 'password':
						break;

					case 'radio':
					case 'checkbox':
						if (el.checked)
							v = el.value || 'on';
					break;

					case 'select-multiple':
						n = n.substring(0, n.length-2);
						v = [];
						for (j=0;j<el.options.length;j++)
						{
							if (el.options[j].selected)
							{
								v.push(el.options[j].value);
							}
						}
					break;

					default:
						v = el.value;
				}

				if (n.indexOf('[]') > 0)
				{
					n = _encodeName(n);
					if (typeof(data.form_data[n]) == 'undefined')
						data.form_data[n] = [v];
					else
						data.form_data[n].push(v);
				}
				else
					data.form_data[_encodeName(n)] = v;
			}
		}

		// we can adjust form_data before autosaving
		BX.onCustomEvent(this.FORM, 'onAutoSave', [this, data.form_data]);
		BX.ajax.post(
			'/bitrix/tools/autosave.php?bxsender=core_autosave&sessid=' + BX.bitrix_sessid(), data, BX.proxy(this._Save, this)
		);
	}
	else
	{
		this.Clear();
	}
};

BX.CAutoSave.prototype.Reset = function()
{
	if (this.FORM && BX.isNodeInDom(this.FORM))
	{
		BX.ajax.post(
			'/bitrix/tools/autosave.php?bxsender=core_autosave&action=reset&sessid=' + BX.bitrix_sessid(), {autosave_id: this.FORM_ID }, null
		);
	}
};

BX.CAutoSave.prototype._Save = function(data)
{
	BX.onCustomEvent(this.FORM, 'onAutoSaveFinished', [this, data]);
};

BX.CAutoSave.prototype.Restore = function(data, clicker)
{
	if (data)
	{
		this.RESTORE_DATA = _decodeData(data);
	}
	else if (this.FORM && this.RESTORE_DATA)
	{
		// we can change restore data or make some unusual actions here
		BX.onCustomEvent(this.FORM, 'onAutoSaveRestore', [this, this.RESTORE_DATA]);

		this.bRestoreInProgress = true;

		for (var i=0; i<this.FORM.elements.length; i++)
		{
			var el = this.FORM.elements[i];
			if (el && BX.type.isDomNode(el) && el.name)
			{
				var value = undefined, n = el.name;

				if (el.type == 'select-multiple')
					n = el.name.substring(0, el.name.length-2);

				value = this.RESTORE_DATA[n];

				if (n.indexOf('[]') > 0 && BX.type.isArray(value))
					value = this.RESTORE_DATA[n].shift();

				if (el.type != 'checkbox' && typeof value == 'undefined')
					continue;

				var bChange = false;

				switch(el.type)
				{
					case 'radio':
						if (!el.checked && !!(value == el.value))
						{
							bChange = true;
							BX.fireEvent(el, 'click');
						}
					break;
					case 'checkbox':
						if (el.checked != !!(value == el.value))
						{
							bChange = true;
							BX.fireEvent(el, 'click');
						}
					break;

					case 'select-one':
						for (var j = 0; j < el.options.length; j++)
						{
							var q = el.options[j].selected;
							el.options[j].selected = !!(value == el.options[j].value);
							bChange |= el.options[j].selected != q;
						}

						break;

					case 'select-multiple':
						value = this.RESTORE_DATA[el.name.substring(0, el.name.length-2)];
						for (j = 0; j < el.options.length; j++)
						{
							q = el.options[j].selected;
							el.options[j].selected = !!(BX.type.isArray(value) && BX.util.in_array(el.options[j].value, value));
							bChange |= el.options[j].selected != q;
						}
						break;

					case 'file':
					case 'button':
					case 'image':
					case 'submit':
					case 'reset':
					case 'password':
						break;

					default:
						bChange = value != el.value;
						el.value = value;
				}

				if (bChange)
					BX.fireEvent(el, 'change');
			}
		}

		var o = this._NotifyContext();
		if (o)
			o.hideNotify(clicker.parentNode.parentNode);

		this.bRestoreInProgress = false;

		BX.onCustomEvent(this.FORM, 'onAutoSaveRestoreFinished', [this, this.RESTORE_DATA]);
	}
};

BX.CAutoSave.prototype._NotifyContext = function()
{
	var o = null;

	if (!this.DISABLE_STANDARD_NOTIFY)
	{
		if (this.NOTIFY_CONTEXT)
			o = this.NOTIFY_CONTEXT;
		else if (BX.WindowManager && BX.WindowManager.Get())
			o = BX.WindowManager.Get();
		else if (BX.adminPanel)
			o = BX.adminPanel;
		else if (BX.admin && BX.admin.panel)
			o = BX.admin.panel;

		this.NOTIFY_CONTEXT = o;
	}

	return o;
};

BX.CAutoSave.prototype.ClearTimers = function()
{
	if (this.TIMERS)
	{
		clearTimeout(this.TIMERS[0]);
		clearInterval(this.TIMERS[1]);
	}
};

BX.CAutoSave.prototype.Clear = function()
{
	if (this.FORM)
	{
		this.FORM.BXAUTOSAVE = null;

		for (var i=0; i<this.FORM.elements.length; i++)
		{
			this.UnRegisterInput(this.FORM.elements[i]);
		}
	}

	this.ClearTimers();

	// we should unset any additional "target events" here
	BX.onCustomEvent(this.FORM, 'onAutoSaveClear', [this]);

	this.FORM = null;
	this.TIMERS = null;
};

BX.CAutoSave.Restore = function(id, el)
{
	BX.onCustomEvent('onExtAutoSaveRestoreClick_' + id, [null, el]);
};

function _encodeName(n)
{
	var q;
	while (q = /[^a-zA-Z0-9_\-]/.exec(n))
	{
		n = n.replace(q[0], 'X' + BX.util.str_pad_left(q[0].charCodeAt(0).toString(), 6, '0') + 'X');
	}
	return n;
}

function _decodeName(n)
{
	var q;
	while (q = /X[\d]{6}X/.exec(n))
	{
		n = n.replace(q[0], String.fromCharCode(parseInt(q[0].replace(/(^X[0]*)|(X$)/g, ''))))
	}
	return n;
}

function _decodeData(data)
{
	var d = {};
	for (var i in data)
	{
		d[_decodeName(i)] = data[i];
	}
	return d;
}
	top.BX.CAutoSave = BX.CAutoSave;
})(window);

