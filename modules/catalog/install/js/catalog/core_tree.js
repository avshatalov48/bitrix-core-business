(function(window) {

if (BX.TreeConditions)
	return;

/**
 * @param {{}} parentContainer
 * @param {{
 * 		values: {},
 * 		labels: {}
 * }} state
 * @param {{
 * 		id: string,
 * 		name: string,
 * 		text: string,
 * 		type: string,
 * 		show_value: string,
 * 		defaultText: string,
 * 		defaultValue: string
 * }} arParams
 *
 * @return {boolean}
 */
BX.TreeCondCtrlAtom = function(parentContainer, state, arParams)
{
	this.boolResult = false;
	if (!parentContainer || !state || !state.values)
		return this.boolResult;

	this.parentContainer = parentContainer;
	this.valuesContainer = state.values;
	if (!BX.type.isNotEmptyString(arParams) && !BX.type.isPlainObject(arParams))
		return this.boolResult;

	if (BX.type.isNotEmptyString(arParams))
	{
		// noinspection JSValidateTypes
		arParams = {text: arParams, type: 'string'};
	}

	if (BX.type.isPlainObject(arParams))
	{
		if (!BX.type.isNotEmptyString(arParams.type))
			return this.boolResult;

		this.arStartParams = arParams;

		this.id = null;
		this.name = null;
		this.type = arParams.type;
		this.showValue = false;
		if (BX.type.isNotEmptyString(arParams.show_value))
		{
			this.showValue = (arParams.show_value === 'Y');
		}

		if (this.type !== 'string' && this.type !== 'prefix')
		{
			if (!arParams.id)
			{
				return this.boolResult;
			}

			this.id = arParams.id;
			this.name = (!arParams.name ? arParams.id : arParams.name);

			this.defaultText = (BX.type.isNotEmptyString(arParams.defaultText) ? arParams.defaultText : '...');
			this.defaultValue = (arParams.defaultValue && arParams.defaultValue.length > 0 ? arParams.defaultValue : '');
			if (!this.valuesContainer[this.id] || this.valuesContainer[this.id].length === 0)
			{
				this.valuesContainer[this.id] = this.defaultValue;
			}
		}
		this.boolResult = true;
		if (this.type === 'string' || this.type === 'prefix')
		{
			this.Init();
		}
	}
	return this.boolResult;
};

/**
 * @return {boolean}
 */
BX.TreeCondCtrlAtom.prototype.Init = function()
{
	if (this.boolResult)
	{
		this.parentContainer = BX(this.parentContainer);
		if (!!this.parentContainer)
		{
			if (this.type === 'string' || this.type === 'prefix')
			{
				this.parentContainer.appendChild(BX.create(
					'SPAN',
					{
						props: { className: (this.type === 'prefix' ? 'control-prefix' : 'control-string') },
						html: BX.util.htmlspecialchars(this.arStartParams.text)
					}
				));
			}
			else
			{
				this.CreateLink();
			}
		}
		else
		{
			this.boolResult = false;
		}
	}
	return this.boolResult;
};

BX.TreeCondCtrlAtom.prototype.IsValue = function()
{
	return (this.valuesContainer[this.id] && this.valuesContainer[this.id].length > 0);
};

BX.TreeCondCtrlAtom.prototype.InitValue = function()
{
	return this.IsValue();
};

BX.TreeCondCtrlAtom.prototype.ReInitValue = function(controls)
{
	if (BX.util.in_array(this.id, controls))
	{
		this.InitValue();
	}
};

BX.TreeCondCtrlAtom.prototype.SetValue = function()
{
	return this.IsValue();
};

BX.TreeCondCtrlAtom.prototype.View = function(boolShow)
{

};

BX.TreeCondCtrlAtom.prototype.onChange = function()
{
	this.SetValue();
	this.View(false);
};

BX.TreeCondCtrlAtom.prototype.onKeypress = function(e)
{
	if (!e)
	{
		e = window.event;
	}
	if (!!e.keyCode)
	{
		switch (e.keyCode)
		{
			case 13:
				this.onChange();
				break;
			case 27:
				this.InitValue();
				this.View(false);
				break;
		}
		if (e.keyCode === 13)
		{
			return BX.PreventDefault(e);
		}
	}
};

BX.TreeCondCtrlAtom.prototype.onClick = function()
{
	this.InitValue();
	this.View(true);
};

BX.TreeCondCtrlAtom.prototype.Delete = function()
{
	if (this.type !== 'string')
	{
		if (this.link)
		{
			BX.unbindAll(this.link);
			this.link = BX.remove(this.link);
		}
	}
};

/**
 * @return {boolean}
 */
BX.TreeCondCtrlAtom.prototype.CreateLink = function()
{
	if (this.boolResult)
	{
		this.link = null;
		this.link = this.parentContainer.appendChild(BX.create(
			'A',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_link',
					className: ''
				},
				style: { display: '' },
				html: (this.IsValue() ? BX.util.htmlspecialchars(this.valuesContainer[this.id]) : this.defaultText)
			}
		));
		if (!this.link)
		{
			this.boolResult = false;
		}
	}
	return this.boolResult;
};

BX.TreeCondCtrlAtom.prototype.prepareData = function(arData, prefix)
{
	var data = '',
		i,
		name = '',
		firstKey = true;
	if (BX.type.isString(arData))
	{
		data = arData;
	}
	else if (BX.type.isPlainObject(arData))
	{
		for (i in arData)
		{
			if (arData.hasOwnProperty(i))
			{
				data += (!firstKey ? '&' : '');
				name = BX.util.urlencode(i);
				if (prefix)
				{
					name = prefix + '[' + name + ']';
				}
				if (BX.type.isPlainObject(arData[i]))
				{
					data += this.prepareData(arData[i], name);
				}
				else
				{
					data += name + '=' + BX.util.urlencode(arData[i]);
				}
				firstKey = false;
			}
		}
	}
	return data;
};

/**
 * @return {string}
 */
BX.TreeCondCtrlAtom.prototype.ViewFormat = function(value, label)
{
	return (this.showValue ? label + ' [' + value + ']' : label);
};

BX.TreeCondCtrlInput = function(parentContainer, state, arParams)
{
	if (BX.TreeCondCtrlInput.superclass.constructor.apply(this, arguments))
		this.Init();

	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlInput, BX.TreeCondCtrlAtom);

BX.TreeCondCtrlInput.prototype.Init = function()
{
	if (this.boolResult && BX.TreeCondCtrlInput.superclass.Init.apply(this, arguments))
	{
		this.input = null;
		this.input = this.parentContainer.appendChild(BX.create(
			'INPUT',
			{
				props: {
					type: 'text',
					id: this.parentContainer.id+'_'+this.id,
					name: this.name,
					className: '',
					value: (this.IsValue() ? this.valuesContainer[this.id] : '')
				},
				style: { display: 'none' },
				events: {
					change: BX.proxy(this.onChange, this),
					blur: BX.proxy(this.onChange, this),
					keypress: BX.proxy(this.onKeypress, this)
				}
			}
		));
		this.boolResult = !!this.input;
	}
	return this.boolResult;
};

BX.TreeCondCtrlInput.prototype.InitValue = function()
{
	if (BX.TreeCondCtrlInput.superclass.InitValue.apply(this, arguments))
	{
		BX.adjust(this.link, {html : BX.util.htmlspecialchars(this.valuesContainer[this.id]) });
		this.input.value = this.valuesContainer[this.id];
	}
	else
	{
		BX.adjust(this.link, {html : this.defaultText });
		this.input.value = '';
	}
};

BX.TreeCondCtrlInput.prototype.SetValue = function()
{
	this.valuesContainer[this.id] = this.input.value;
	if (BX.TreeCondCtrlInput.superclass.SetValue.apply(this, arguments))
	{
		BX.adjust(this.link, {html : BX.util.htmlspecialchars(this.valuesContainer[this.id]) });
	}
	else
	{
		BX.adjust(this.link, {html : this.defaultText });
	}
};

BX.TreeCondCtrlInput.prototype.View = function(boolShow)
{
	BX.TreeCondCtrlInput.superclass.View.apply(this, arguments);
	if (boolShow)
	{
		BX.style(this.link, 'display', 'none');
		BX.style(this.input, 'display', '');
		BX.focus(this.input);
	}
	else
	{
		BX.style(this.input, 'display', 'none');
		BX.style(this.link, 'display', '');
		this.input.blur();
	}
};

BX.TreeCondCtrlInput.prototype.Delete = function()
{
	BX.TreeCondCtrlInput.superclass.Delete.apply(this, arguments);
	if (this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
};

BX.TreeCondCtrlInput.prototype.CreateLink = function()
{
	if (BX.TreeCondCtrlInput.superclass.CreateLink.apply(this, arguments))
	{
		BX.bind(this.link, 'click', BX.proxy(this.onClick, this));
	}
	return this.boolResult;
};

BX.TreeCondCtrlBaseSelect = function(parentContainer, state, params)
{
	this.values = [];
	this.labels = [];

	this.multiple = false;
	this.size = 3;
	this.first_option = '...';

	this.boolVisual = false;
	this.visual = null;

	if (BX.TreeCondCtrlBaseSelect.superclass.constructor.apply(this, arguments))
	{
		if (BX.type.isNotEmptyString(params.multiple))
			this.multiple = params.multiple === 'Y';
		if (BX.type.isString(params.size) || BX.type.isNumber(params.size))
		{
			params.size = parseInt(params.size, 10);
			if (!isNaN(params.size) && params.size > 0)
				this.size = params.size;
		}
		if (BX.type.isNotEmptyString(params.first_option))
			this.first_option = params.first_option;

		this.dontShowFirstOption = !!params.dontShowFirstOption;
	}

	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlBaseSelect, BX.TreeCondCtrlAtom);

BX.TreeCondCtrlBaseSelect.prototype.Init = function()
{
	BX.TreeCondCtrlInput.superclass.Init.apply(this, arguments);
	return this.boolResult;
};

BX.TreeCondCtrlBaseSelect.prototype.setValueText = function()
{
	var titles,
		i,
		j;

	if (BX.type.isString(this.valuesContainer[this.id]))
		this.valuesContainer[this.id] = this.valuesContainer[this.id].split(',');

	titles = [];
	this.select.selectedIndex = -1;
	for (i = 0; i < this.select.options.length; i++)
	{
		if (BX.util.in_array(this.select.options[i].value, this.valuesContainer[this.id]))
		{
			j = BX.util.array_search(this.select.options[i].value, this.values);
			if (j > -1)
			{
				this.select.options[i].selected = true;
				if (this.select.selectedIndex === -1)
				{
					this.select.selectedIndex = i;
				}
				titles[titles.length] = this.ViewFormat(this.values[j], this.labels[j]);
			}
			else
			{
				this.select.options[i].selected = false;
			}
		}
		else
		{
			this.select.options[i].selected = false;
		}
	}
	if (titles.length === 0)
		titles[0] = this.defaultText;

	BX.adjust(this.link, { html: BX.util.htmlspecialchars(titles.join(', ')) });
	titles = null;
};

BX.TreeCondCtrlBaseSelect.prototype.SetValue = function()
{
	var arText = [],
		arSelVal = [],
		i,
		j;

	if (this.multiple)
	{
		for (i = 0; i < this.select.options.length; i++)
		{
			if (this.select.options[i].selected)
			{
				arSelVal[arSelVal.length] = this.select.options[i].value;
				j = BX.util.array_search(this.select.options[i].value, this.values);
				if (j > -1)
				{
					arText[arText.length] = this.ViewFormat(this.values[j], this.labels[j]);
				}
			}
		}
		if (arText.length === 0)
		{
			arText[0] = this.defaultText;
		}
		this.valuesContainer[this.id] = arSelVal;
	}
	else
	{
		if (this.select.selectedIndex > -1 && this.select.options[this.select.selectedIndex])
		{
			this.valuesContainer[this.id] = [this.select.options[this.select.selectedIndex].value];
			i = BX.util.array_search(this.select.options[this.select.selectedIndex].value, this.values);
			arText[0] = (i > -1 ? this.ViewFormat(this.values[i],this.labels[i]) : this.defaultText);
		}
	}
	if (BX.TreeCondCtrlBaseSelect.superclass.SetValue.apply(this, arguments))
		BX.adjust(this.link, {html : BX.util.htmlspecialchars(arText.join(', ')) });
	else
		BX.adjust(this.link, {html : this.defaultText });
};

BX.TreeCondCtrlBaseSelect.prototype.View = function(boolShow)
{
	BX.TreeCondCtrlBaseSelect.superclass.View.apply(this, arguments);
	if (boolShow)
	{
		BX.style(this.link, 'display', 'none');
		BX.style(this.select, 'display', '');
		BX.focus(this.select);
	}
	else
	{
		BX.style(this.select, 'display', 'none');
		BX.style(this.link, 'display', '');
		this.select.blur();
	}
};

BX.TreeCondCtrlBaseSelect.prototype.onChange = function()
{
	this.SetValue();
	if (!this.multiple)
		this.View(false);
	if (this.boolVisual)
		this.visual();
};

BX.TreeCondCtrlBaseSelect.prototype.onBlur = function()
{
	this.View(false);
};

BX.TreeCondCtrlBaseSelect.prototype.onKeypress = function(e)
{
	if (!e)
		e = window.event;

	if (e.keyCode && (e.keyCode === 13 || e.keyCode === 27))
	{
		this.View(false);
		if (e.keyCode === 13)
			return BX.PreventDefault(e);
	}
};

BX.TreeCondCtrlBaseSelect.prototype.onClick = function()
{
	this.View(true);
};

BX.TreeCondCtrlBaseSelect.prototype.Delete = function()
{
	BX.TreeCondCtrlBaseSelect.superclass.Delete.apply(this, arguments);
	if (this.select)
	{
		BX.unbindAll(this.select);
		this.select = BX.remove(this.select);
	}
	if (this.boolVisual)
		this.visual = null;
};

BX.TreeCondCtrlBaseSelect.prototype.CreateLink = function()
{
	if (BX.TreeCondCtrlBaseSelect.superclass.CreateLink.apply(this, arguments))
		BX.bind(this.link, 'click', BX.proxy(this.onClick, this));

	return this.boolResult;
};

BX.TreeCondCtrlBaseSelect.prototype.CreateSelect = function()
{
	var props;

	props = {
		id: this.parentContainer.id + '_' + this.id,
		name: this.name,
		className: '',
		selectedIndex: -1
	};
	if (this.multiple)
	{
		props.name = this.name + '[]';
		props.multiple = true;
		props.size = this.size;
	}
	this.select = this.parentContainer.appendChild(BX.create(
		'select',
		{
			props: props,
			style: {display: 'none'},
			events: {
				change: BX.proxy(this.onChange, this),
				blur: BX.proxy(this.onBlur, this),
				keypress: BX.proxy(this.onKeypress, this)
			}
		}
	));
	if (BX.type.isElementNode(this.select))
	{
		if (!this.multiple && !this.dontShowFirstOption)
		{
			this.select.appendChild(BX.create(
				'option',
				{
					props: {value: ''},
					html: this.first_option
				}
			));
		}
	}
	props = null;
};

BX.TreeCondCtrlSelect = function(parentContainer, state, arParams)
{
	var i;
	if (BX.TreeCondCtrlSelect.superclass.constructor.apply(this, arguments))
	{
		if (!BX.type.isPlainObject(arParams.values))
		{
			return this.boolResult;
		}
		for (i in arParams.values)
		{
			if (arParams.values.hasOwnProperty(i))
			{
				this.values[this.values.length] = i;
				this.labels[this.labels.length] = arParams.values[i];
			}
		}
		if (this.values.length === 0)
		{
			return this.boolResult;
		}
		if (this.defaultValue.length > 0)
		{
			i = BX.util.array_search(this.defaultValue, this.values);
			this.defaultText = (i > -1 ? this.labels[i] : '');
		}

		if (BX.type.isPlainObject(arParams.events) && BX.type.isFunction(arParams.events.visual))
		{
			this.boolVisual = true;
			this.visual = arParams.events.visual;
		}
		this.Init();
	}
	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlSelect, BX.TreeCondCtrlBaseSelect);

BX.TreeCondCtrlSelect.prototype.Init = function()
{
	var i;

	if (this.boolResult && BX.TreeCondCtrlSelect.superclass.Init.apply(this, arguments))
	{
		this.CreateSelect();
		if (BX.type.isElementNode(this.select))
		{
			for (i in this.values)
			{
				if (this.values.hasOwnProperty(i))
				{
					this.select.appendChild(BX.create(
						'option',
						{
							props: { value: this.values[i] },
							html: BX.util.htmlspecialchars(this.ViewFormat(this.values[i] ,this.labels[i]))
						}
					));
				}
			}
			this.InitValue();
		}
		this.boolResult = !!this.select;
	}
	return this.boolResult;
};

BX.TreeCondCtrlSelect.prototype.InitValue = function()
{
	if (BX.TreeCondCtrlSelect.superclass.InitValue.apply(this, arguments))
		this.setValueText();
	else
		this.select.selectedIndex = -1;
};

BX.TreeCondCtrlSelect.prototype.CreateLink = function()
{
	if (BX.TreeCondCtrlSelect.superclass.CreateLink.apply(this, arguments))
		BX.bind(this.link, 'click', BX.proxy(this.onClick, this));

	return this.boolResult;
};

/**
 * @param {{}} parentContainer
 * @param {{
 * 		values: {},
 * 		labels: {}
 * }} state
 * @param {{
 * 		id: string,
 * 		name: string,
 * 		text: string,
 * 		type: string,
 * 		show_value: string,
 * 		defaultText: string,
 * 		defaultValue: string,
 *
 * 		load_url: string,
 * 		load_params: {}
 * }} params
 *
 * @return {boolean}
 */
BX.TreeCondCtrlLazySelect = function(parentContainer, state, params)
{
	var i;

	this.loaded = false;
	this.loadProgress = false;

	this.loadUrl = '';
	this.loadUrlParams = {};

	if (BX.TreeCondCtrlLazySelect.superclass.constructor.apply(this, arguments))
	{
		if (BX.type.isNotEmptyString(params.load_url))
			this.loadUrl = params.load_url;
		if (BX.type.isPlainObject(params.load_params))
		{
			for (i in params.load_params)
			{
				if (params.load_params.hasOwnProperty(i))
					this.loadUrlParams[i] = params.load_params[i];
			}
		}
		this.Init();
	}
	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlLazySelect, BX.TreeCondCtrlBaseSelect);

BX.TreeCondCtrlLazySelect.prototype.Init = function()
{
	if (this.boolResult && BX.TreeCondCtrlLazySelect.superclass.Init.apply(this, arguments))
	{
		this.CreateSelect();
		this.InitValue();
	}

	return this.boolResult;
};

BX.TreeCondCtrlLazySelect.prototype.InitValue = function()
{
	if (BX.TreeCondCtrlLazySelect.superclass.InitValue.apply(this, arguments))
	{
		if (!this.loaded)
			this.ajaxLoad('InitValue');
		else
			this.setValueText();
	}
	else
	{
		this.select.selectedIndex = -1;
	}
};

BX.TreeCondCtrlLazySelect.prototype.ajaxLoad = function(source)
{
	var ajaxParams = {},
		i,
		successFunc;

	if (this.loaded || this.loadProgress)
		return;

	for (i in this.loadUrlParams)
	{
		if (this.loadUrlParams.hasOwnProperty(i))
			ajaxParams[i] = this.loadUrlParams[i];
	}
	ajaxParams.sessid = BX.bitrix_sessid();
	ajaxParams.lang = BX.message('LANGUAGE_ID');

	switch (source)
	{
		default:
		case 'InitValue':
			successFunc = BX.proxy(this.ajaxLoadResultFromInit, this);
			break;
		case 'onClick':
			successFunc = BX.proxy(this.ajaxLoadResultFromClick, this);
			break;
	}

	this.loadProgress = true;
	BX.showWait(this.parentContainer);
	BX.ajax({
		'method': 'POST',
		'dataType': 'json',
		'url': this.loadUrl,
		'data': ajaxParams,
		'onsuccess': successFunc
	});
	successFunc = null;
	ajaxParams = null;
};

BX.TreeCondCtrlLazySelect.prototype.ajaxLoadResult = function(result)
{
	var i;

	this.loadProgress = false;
	BX.closeWait(this.parentContainer);
	if (BX.type.isArray(result))
	{
		for (i = 0; i < result.length; i++)
		{
			this.values[this.values.length] = result[i].value;
			this.labels[this.labels.length] = result[i].label;

			this.select.appendChild(BX.create(
				'option',
				{
					props: { value: result[i].value },
					html: BX.util.htmlspecialchars(this.ViewFormat(result[i].value, result[i].label))
				}
			));
		}
		this.loaded = true;
	}
};

BX.TreeCondCtrlLazySelect.prototype.ajaxLoadResultFromInit = function(result)
{
	if (BX.type.isArray(result))
	{
		this.ajaxLoadResult(result);
		if (this.loaded)
			this.setValueText();
	}
};

BX.TreeCondCtrlLazySelect.prototype.ajaxLoadResultFromClick = function(result)
{
	if (BX.type.isArray(result))
	{
		this.ajaxLoadResult(result);
		if (this.loaded)
			this.onClick();
	}
};

BX.TreeCondCtrlLazySelect.prototype.onClick = function()
{
	if (this.loaded)
		this.View(true);
	else
		this.ajaxLoad('onClick');
};

/**
 * @param {{}} parentContainer
 * @param {{
 * 		values: {},
 * 		labels: {}
 * }} state
 * @param {{
 * 		id: string,
 * 		name: string,
 * 		text: string,
 * 		type: string,
 * 		show_value: string,
 * 		defaultText: string,
 * 		defaultValue: string,
 *
 * 		popup_url: string,
 * 		popup_params: {},
 * 		param_id: string
 * }} arParams
 *
 * @return {boolean}
 */
BX.TreeCondCtrlPopup = function(parentContainer, state, arParams)
{
	var i;

	if (BX.TreeCondCtrlPopup.superclass.constructor.apply(this, arguments))
	{
		if (!arParams.popup_url)
		{
			return this.boolResult;
		}
		this.popup_url = arParams.popup_url;

		this.popup_params = {};
		if (arParams.popup_params)
		{
			for (i in arParams.popup_params)
			{
				if (arParams.popup_params.hasOwnProperty(i))
				{
					this.popup_params[i] = arParams.popup_params[i];
				}
			}
		}

		this.popup_param_id = null;
		if (BX.type.isNotEmptyString(arParams.param_id))
		{
			this.popup_param_id = arParams.param_id;
		}

		this.label = '';
		if (!!state.labels && !!state.labels[this.id])
		{
			this.label = state.labels[this.id];
		}
		if (this.label.length === 0)
		{
			this.label = (this.valuesContainer[this.id].length > 0 ? this.valuesContainer[this.id] : this.defaultText);
		}

		this.Init();
	}
	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlPopup, BX.TreeCondCtrlAtom);

BX.TreeCondCtrlPopup.prototype.Init = function()
{
	if (this.boolResult && BX.TreeCondCtrlPopup.superclass.Init.apply(this, arguments))
	{
		if (this.popup_param_id)
		{
			this.popup_params[this.popup_param_id] = this.parentContainer.id+'_'+this.id;
		}
		this.input = this.parentContainer.appendChild(BX.create(
			'INPUT',
			{
				props: {
					type: 'hidden',
					id: this.parentContainer.id+'_'+this.id,
					name: this.name,
					className: '',
					value: (this.IsValue() ? this.valuesContainer[this.id] : '')
				},
				style: { display: 'none' },
				events: {
					change: BX.proxy(this.onChange, this)
				}
			}
		));
		this.boolResult = !!this.input;
	}
	return this.boolResult;
};

BX.TreeCondCtrlPopup.prototype.CreateLink = function()
{
	if (this.boolResult)
	{
		this.link = this.parentContainer.appendChild(BX.create(
			'A',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_link',
					className: ''
				},
				style: { display: '' },
				html: (this.IsValue() ? BX.util.htmlspecialchars(this.ViewFormat(this.valuesContainer[this.id], this.label)) : this.defaultText),
				events: {
					click: BX.proxy(this.PopupShow, this)
				}
			}
		));
		this.boolResult = !!this.link;
	}
	return this.boolResult;
};

BX.TreeCondCtrlPopup.prototype.PopupShow = function()
{
	var url = this.popup_url,
		data = this.prepareData(this.popup_params);

	if (data.length > 0)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
	}

	var wnd = window.open(url,'', 'scrollbars=yes,resizable=yes,width=900,height=600,top='+parseInt((screen.height - 500)/2-14, 10)+',left='+parseInt((screen.width - 600)/2-5, 10));
	wnd.onbeforeunload = function(){BX.onCustomEvent('onTreeCondPopupClose')};
};

BX.TreeCondCtrlPopup.prototype.onChange = function()
{
	this.valuesContainer[this.id] = this.input.value;
};

BX.TreeCondCtrlPopup.prototype.Delete = function()
{
	BX.TreeCondCtrlPopup.superclass.Delete.apply(this, arguments);
	if (this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
};

BX.TreeUserCondCtrlPopup = function(parentContainer, state, arParams)
{
	var i;

	if (BX.TreeUserCondCtrlPopup.superclass.constructor.apply(this, arguments))
	{
		if (!arParams.popup_url)
		{
			return this.boolResult;
		}
		this.user_load_url = arParams.user_load_url;

		this.popup_url = arParams.popup_url;

		if (arParams.popup_params)
		{
			for (i in arParams.popup_params)
			{
				if (arParams.popup_params.hasOwnProperty(i))
				{
					this.popup_params[i] = arParams.popup_params[i];
				}
			}
		}

		this.popup_param_id = null;
		if (BX.type.isNotEmptyString(arParams.param_id))
		{
			this.popup_param_id = arParams.param_id;
		}

		this.label = '';
		if (!!state.labels && !!state.labels[this.id])
		{
			this.label = state.labels[this.id];
		}
		if (this.label.length === 0)
		{
			this.label = (this.valuesContainer[this.id].length > 0 ? this.valuesContainer[this.id] : this.defaultText);
		}
	}
	return this.boolResult;
};
BX.extend(BX.TreeUserCondCtrlPopup, BX.TreeCondCtrlPopup);

BX.TreeUserCondCtrlPopup.prototype.Init = function()
{
	var i;

	this.inputs = [];
	if(this.valuesContainer[this.id] === "")
	{
		this.valuesContainer[this.id] = [];
		this.label = [];
	}
	if (!BX.type.isArray(this.valuesContainer[this.id]))
	{
		this.valuesContainer[this.id] = [this.valuesContainer[this.id]];
		this.label = [this.label];
	}
	if (this.boolResult && BX.TreeUserCondCtrlPopup.superclass.Init.apply(this, arguments))
	{
		if (this.input)
		{
			BX.unbindAll(this.input);
			this.input = BX.remove(this.input);
		}

		if (this.popup_param_id)
		{
			this.popup_params[this.popup_param_id] = this.parentContainer.id+'_'+this.id;
		}
		if (!this.IsValue())
		{
			this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', '');
		}
		else
		{
			for (i in this.valuesContainer[this.id])
			{
				if(!this.valuesContainer[this.id].hasOwnProperty(i))
					continue;

				this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', this.valuesContainer[this.id][i]);
			}
		}
		this.AppendFakeInputNode(this.parentContainer.id+'_'+this.id, this.name);

		this.boolResult = !!this.inputs;
	}
	return this.boolResult;
};

BX.TreeUserCondCtrlPopup.prototype.CreateLink = function()
{
	this.popup_params['FN'] = 'sale_discount_form';
	this.popup_params['FC'] = 'fake_' + this.name;

	var i;
	if (this.boolResult)
	{
		this.defaultLabel = BX.create('SPAN', {
			text: this.defaultText,
			style: {cursor: 'pointer'},
			props: {
				className: 'condition-dots'
			}
		});
		this.link = this.parentContainer.appendChild(BX.create(
			'SPAN',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_link',
					className: 'condition-list-wrap'
				},
				style: { display: '' },
				events: {
					click: BX.proxy(this.PopupShow, this)
				},
				children: [
					this.defaultLabel
				]
			}
		));

		for (i in this.valuesContainer[this.id])
		{
			if (!this.valuesContainer[this.id].hasOwnProperty(i))
				continue;

			this.AppendItemNode(this.valuesContainer[this.id][i], this.label[i]);
		}

		this.boolResult = !!this.link;
	}
	return this.boolResult;
};
BX.TreeUserCondCtrlPopup.prototype.AppendItemNode = function(value, label)
{
	this.link.insertBefore(BX.create(
			'SPAN',
			{
				props: {
					className: 'condition-item'
				},
				style: {display: ''},
				children: [
					BX.create('SPAN', {
						props: {
							className: 'condition-item-text'
						},
						html: BX.util.htmlspecialchars(this.ViewFormat(value, label))
					}),
					BX.create('SPAN', {
						props: {
							className: 'condition-item-del'
						},
						attrs: {
							'bx-data-value': value
						},
						events: {
							click: BX.proxy(this.DeleteItem, this)
						}
					})
				]
			}
	), this.defaultLabel);
};
BX.TreeUserCondCtrlPopup.prototype.AppendInputNode = function(id, name, value)
{
	this.inputs.push(this.parentContainer.appendChild(BX.create(
		'INPUT',
		{
			props: {
				type: 'hidden',
				id: id,
				name: name,
				value: value
			},
			style: {display: 'none'},
			events: {
				change: BX.proxy(this.onChange, this)
			}
		}
	)));
};
BX.TreeUserCondCtrlPopup.prototype.AppendFakeInputNode = function(id, name)
{
	this.inputs.push(this.parentContainer.appendChild(BX.create(
		'INPUT',
		{
			props: {
				type: 'hidden',
				id: 'fake_' + id,
				name: 'fake_' + name
			},
			style: {display: 'none'},
			events: {
				change: BX.proxy(this.onChangeFake, this)
			}
		}
	)));
};
BX.TreeUserCondCtrlPopup.prototype.onChangeFake = function(params)
{
	var userId = params.target.value;

	BX.ajax({
		'method': 'POST',
		'dataType': 'json',
		'url': this.user_load_url,
		'data': {
			sessid: BX.bitrix_sessid(),
			AJAX_ACTION: 'getUserName',
			USER_ID: userId
		},
		'onsuccess': BX.delegate(function (data)
		{
			var name = data.name;
			this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', userId);
			this.AppendItemNode(userId, name);

			this.valuesContainer[this.id].push(userId);
		}, this)
	});

};
BX.TreeUserCondCtrlPopup.prototype.onSave = function(params)
{
	if (BX.type.isPlainObject(params))
	{
		this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', params.id);
		this.AppendItemNode(params.id, params.name);

		this.valuesContainer[this.id].push(params.id);
	}
};
BX.TreeUserCondCtrlPopup.prototype.DeleteItem = function(e)
{
	var srcElement = e.target || e.srcElement;
	if(!srcElement)
	{
		BX.PreventDefault(e);
		return;
	}

	var itemContainer = BX.findParent(srcElement, {className: 'condition-item', tagName: 'span'}, 3);
	if(!itemContainer)
	{
		BX.PreventDefault(e);
		return;
	}

	BX.remove(BX.findParent(srcElement, {className: 'condition-item', tagName: 'span'}, 3));
	BX.remove(BX.findChild(this.parentContainer, {tagName: 'input', attribute: {name: this.name+'[]', value: srcElement.getAttribute('bx-data-value')}}, 3));

	BX.PreventDefault(e);
};
BX.TreeUserCondCtrlPopup.prototype.Delete = function()
{
	BX.TreeUserCondCtrlPopup.superclass.Delete.apply(this, arguments);
	if (this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
	if (this.inputs)
	{
		for(var i in this.inputs)
		{
			if(!this.inputs.hasOwnProperty(i))
				continue;
			BX.unbindAll(this.inputs[i]);
			BX.remove((this.inputs[i]));
			delete (this.inputs[i]);
		}
		this.inputs = [];
	}

	if (!!this.dialog)
	{
		this.dialog = null;
	}
};

BX.TreeCondCtrlDialog = function(parentContainer, state, arParams)
{
	var data;

	if (BX.TreeCondCtrlDialog.superclass.constructor.apply(this, arguments))
	{
		this.popup_params.event = 'onTreeCondDialogSave';

		data = this.prepareData(this.popup_params);
		if (data.length > 0)
		{
			this.popup_url += (this.popup_url.indexOf('?') !== -1 ? "&" : "?") + data;
		}
		this.dialog = null;
	}
	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlDialog, BX.TreeCondCtrlPopup);

BX.TreeCondCtrlDialog.prototype.CreateLink = function()
{
	if (this.boolResult)
	{
		this.link = this.parentContainer.appendChild(BX.create(
			'A',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_link',
					className: ''
				},
				style: { display: '' },
				html: (this.IsValue() ? BX.util.htmlspecialchars(this.ViewFormat(this.valuesContainer[this.id], this.label)) : this.defaultText),
				events: {
					click: BX.proxy(this.DialogShow, this)
				}
			}
		));
		this.boolResult = !!this.link;
	}
	return this.boolResult;
};

BX.TreeCondCtrlDialog.prototype.onChange = function()
{
	this.valuesContainer[this.id] = this.input.value;
};

BX.TreeCondCtrlDialog.prototype.DialogShow = function()
{
	if (this.dialog !== null)
		this.dialog = null;
	this.dialog = new BX.CAdminDialog({
		content_url: this.popup_url,
		height: Math.max(500, window.innerHeight-400),
		width: Math.max(800, window.innerWidth-400),
		draggable: true,
		resizable: true,
		min_height: 500,
		min_width: 800
	});
	if (!!this.dialog)
	{
		BX.addCustomEvent('onTreeCondDialogSave', BX.proxy(this.onSave, this));
		this.dialog.Show();
	}
};

BX.TreeCondCtrlDialog.prototype.onSave = function(params)
{
	BX.removeCustomEvent('onTreeCondDialogSave', BX.proxy(this.onSave, this));
	if (BX.type.isPlainObject(params))
	{
		this.input.value = params.id;
		this.link.innerHTML = BX.util.htmlspecialchars(this.ViewFormat(params.id, params.name));
		this.onChange();
	}
	this.dialog.Close();
	this.dialog = null;
};

BX.TreeCondCtrlDialog.prototype.Delete = function()
{
	BX.TreeCondCtrlDialog.superclass.Delete.apply(this, arguments);
	if (this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
	if (!!this.dialog)
	{
		this.dialog = null;
	}
};

BX.TreeMultiCondCtrlDialog = function(parentContainer, state, arParams)
{
	this.defaultLabel = null;
	var data;

	if (BX.TreeMultiCondCtrlDialog.superclass.constructor.apply(this, arguments))
	{
		this.popup_params.event = 'onTreeCondDialogSave';

		data = this.prepareData(this.popup_params);
		if (data.length > 0)
		{
			this.popup_url += (this.popup_url.indexOf('?') !== -1 ? "&" : "?") + data;
		}
		this.dialog = null;

		BX.addClass(parentContainer, 'condition-multi');
	}

	return this.boolResult;
};
BX.extend(BX.TreeMultiCondCtrlDialog, BX.TreeCondCtrlPopup);

BX.TreeMultiCondCtrlDialog.prototype.AppendItemNode = function(value, label)
{
	this.link.insertBefore(BX.create(
			'SPAN',
			{
				props: {
					className: 'condition-item'
				},
				style: {display: ''},
				children: [
					BX.create('SPAN', {
						props: {
							className: 'condition-item-text'
						},
						html: BX.util.htmlspecialchars(this.ViewFormat(value, label))
					}),
					BX.create('SPAN', {
						props: {
							className: 'condition-item-del'
						},
						attrs: {
							'bx-data-value': value
						},
						events: {
							click: BX.proxy(this.DeleteItem, this)
						}
					})
				]
			}
	), this.defaultLabel);
};
BX.TreeMultiCondCtrlDialog.prototype.AppendInputNode = function(id, name, value)
{
	this.inputs.push(this.parentContainer.appendChild(BX.create(
		'INPUT',
		{
			props: {
				type: 'hidden',
				id: id,
				name: name,
				value: value
			},
			style: {display: 'none'},
			events: {
				change: BX.proxy(this.onChange, this)
			}
		}
	)));
};

BX.TreeMultiCondCtrlDialog.prototype.Init = function()
{
	var i;

	this.inputs = [];
	if(this.valuesContainer[this.id] === "")
	{
		this.valuesContainer[this.id] = [];
		this.label = [];
	}
	if (!BX.type.isArray(this.valuesContainer[this.id]))
	{
		this.valuesContainer[this.id] = [this.valuesContainer[this.id]];
		this.label = [this.label];
	}
	if (this.boolResult && BX.TreeMultiCondCtrlDialog.superclass.Init.apply(this, arguments))
	{
		if (this.input)
		{
			BX.unbindAll(this.input);
			this.input = BX.remove(this.input);
		}

		if (this.popup_param_id)
		{
			this.popup_params[this.popup_param_id] = this.parentContainer.id+'_'+this.id;
		}
		if (!this.IsValue())
		{
			this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', '');
		}
		else
		{
			for (i in this.valuesContainer[this.id])
			{
				if(!this.valuesContainer[this.id].hasOwnProperty(i))
					continue;

				this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', this.valuesContainer[this.id][i]);
			}
		}
		this.boolResult = !!this.inputs;
	}
	return this.boolResult;
};

BX.TreeMultiCondCtrlDialog.prototype.CreateLink = function()
{
	var i;

	if (this.boolResult)
	{
		this.defaultLabel = BX.create('SPAN', {
			text: this.defaultText,
			style: {cursor: 'pointer'},
			props: {
				className: 'condition-dots'
			}
		});
		this.link = this.parentContainer.appendChild(BX.create(
			'SPAN',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_link',
					className: 'condition-list-wrap'
				},
				style: { display: '' },
				events: {
					click: BX.proxy(this.DialogShow, this)
				},
				children: [
					this.defaultLabel
				]
			}
		));

		for (i in this.valuesContainer[this.id])
		{
			if (!this.valuesContainer[this.id].hasOwnProperty(i))
				continue;

			this.AppendItemNode(this.valuesContainer[this.id][i], this.label[i]);
		}

		this.boolResult = !!this.link;
	}
	return this.boolResult;
};

BX.TreeMultiCondCtrlDialog.prototype.onChange = function()
{
};

BX.TreeMultiCondCtrlDialog.prototype.DialogShow = function()
{
	if (this.dialog !== null)
		this.dialog = null;
	this.dialog = new BX.CAdminDialog({
		content_url: this.popup_url,
		height: Math.max(500, window.innerHeight-400),
		width: Math.max(800, window.innerWidth-400),
		draggable: true,
		resizable: true,
		min_height: 500,
		min_width: 800
	});
	if (!!this.dialog)
	{
		BX.addCustomEvent('onTreeCondDialogSave', BX.proxy(this.onSave, this));

		BX.addCustomEvent(this.dialog, 'onWindowClose', BX.delegate(function(){
			BX.removeCustomEvent('onTreeCondDialogSave', BX.proxy(this.onSave, this));
		}, this));

		this.dialog.Show();
	}
};

BX.TreeMultiCondCtrlDialog.prototype.onSave = function(params)
{
	if (BX.type.isPlainObject(params))
	{
		this.AppendInputNode(this.parentContainer.id+'_'+this.id, this.name+'[]', params.id);
		this.AppendItemNode(params.id, params.name);

		this.valuesContainer[this.id].push(params.id);
	}
};

BX.TreeMultiCondCtrlDialog.prototype.DeleteItem = function(e)
{
	var srcElement = e.target || e.srcElement;
	if(!srcElement)
	{
		BX.PreventDefault(e);
		return;
	}

	var itemContainer = BX.findParent(srcElement, {className: 'condition-item', tagName: 'span'}, 3);
	if(!itemContainer)
	{
		BX.PreventDefault(e);
		return;
	}

	BX.remove(BX.findParent(srcElement, {className: 'condition-item', tagName: 'span'}, 3));
	BX.remove(BX.findChild(this.parentContainer, {tagName: 'input', attribute: {name: this.name+'[]', value: srcElement.getAttribute('bx-data-value')}}, 3));

	BX.PreventDefault(e);
};
BX.TreeMultiCondCtrlDialog.prototype.Delete = function()
{
	BX.TreeMultiCondCtrlDialog.superclass.Delete.apply(this, arguments);
	if (this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
	if (this.inputs)
	{
		for(var i in this.inputs)
		{
			if(!this.inputs.hasOwnProperty(i))
				continue;
			BX.unbindAll(this.inputs[i]);
			BX.remove((this.inputs[i]));
			delete (this.inputs[i]);
		}
		this.inputs = [];
	}

	if (!!this.dialog)
	{
		this.dialog = null;
	}
};

BX.TreeCondCtrlDateTime = function(parentContainer, state, arParams)
{
	if (BX.TreeCondCtrlDateTime.superclass.constructor.apply(this, arguments))
	{
		this.format = (!!arParams.format && arParams.format === 'date' ? 'date' : 'datetime');
		this.Init();
	}
	return this.boolResult;
};
BX.extend(BX.TreeCondCtrlDateTime, BX.TreeCondCtrlAtom);

BX.TreeCondCtrlDateTime.prototype.Init = function()
{
	if (this.boolResult && BX.TreeCondCtrlDateTime.superclass.Init.apply(this, arguments))
	{
		this.input = BX.create(
			'INPUT',
			{
				props: {
					type: 'text',
					id: this.parentContainer.id+'_'+this.id,
					name: this.name,
					className: 'adm-input',
					value: (this.IsValue() ? this.valuesContainer[this.id] : '')
				},
				events: {
					change: BX.proxy(this.onChange, this),
					keypress: BX.proxy(this.onKeypress, this)
				}
			}
		);
		this.icon = BX.create(
			'SPAN',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_icon',
					className: 'adm-calendar-icon',
					title: BX.message('JC_CORE_TREE_CONTROL_DATETIME_ICON')
				},
				events: {
					click: BX.proxy(this.ShowCalendarControl, this)
				}
			}
		);
		this.calendarText = BX.create(
			'DIV',
			{
				props: { className: 'adm-input-wrap' },
				children: [
					this.input,
					this.icon
				]
			}
		);
		this.calendar = this.parentContainer.appendChild(BX.create(
			'DIV',
			{
				props: {
					id: this.parentContainer.id+'_'+this.id+'_calendar',
					className: 'adm-calendar-block adm-filter-alignment'
				},
				style: { display: 'none', verticalAlign: 'middle' },
				children: [
					BX.create(
						'DIV',
						{
							props: { className: 'adm-filter-box-sizing' },
							style: { verticalAlign: 'middle' },
							children: [
								this.calendarText
							]
						}
					)
				]
			}
		));
		this.boolResult = !!this.calendar;
	}
};

BX.TreeCondCtrlDateTime.prototype.InitValue = function()
{
	if (BX.TreeCondCtrlDateTime.superclass.InitValue.apply(this, arguments))
	{
		BX.adjust(this.link, {html : BX.util.htmlspecialchars(this.valuesContainer[this.id]) });
		this.input.value = this.valuesContainer[this.id];
	}
	else
	{
		BX.adjust(this.link, {html : this.defaultText });
		this.input.value = '';
	}
};

BX.TreeCondCtrlDateTime.prototype.SetValue = function()
{
	this.valuesContainer[this.id] = this.input.value;
	if (BX.TreeCondCtrlDateTime.superclass.SetValue.apply(this, arguments))
	{
		BX.adjust(this.link, {html : BX.util.htmlspecialchars(this.valuesContainer[this.id]) });
	}
	else
	{
		BX.adjust(this.link, {html : this.defaultText });
	}
};

BX.TreeCondCtrlDateTime.prototype.View = function(boolShow)
{
	BX.TreeCondCtrlDateTime.superclass.View.apply(this, arguments);
	if (boolShow)
	{
		BX.style(this.link, 'display', 'none');
		BX.style(this.calendar, 'display', 'inline-block');
		BX.focus(this.input);
	}
	else
	{
		BX.style(this.calendar, 'display', 'none');
		BX.style(this.link, 'display', '');
	}
};

BX.TreeCondCtrlDateTime.prototype.Delete = function()
{
	BX.TreeCondCtrlDateTime.superclass.Delete.apply(this, arguments);
	if (!!this.input)
	{
		BX.unbindAll(this.input);
		this.input = BX.remove(this.input);
	}
	if (!!this.icon)
	{
		BX.unbindAll(this.icon);
		this.icon = BX.remove(this.icon);
	}
	if (!!this.calendarText)
	{
		this.calendarText = BX.remove(this.calendarText);
	}
	if (!!this.calendar)
	{
		BX.unbindAll(this.calendar);
		this.calendar = BX.remove(this.calendar);
	}
};

BX.TreeCondCtrlDateTime.prototype.CreateLink = function()
{
	if (BX.TreeCondCtrlDateTime.superclass.CreateLink.apply(this, arguments))
	{
		BX.bind(this.link, 'click', BX.proxy(this.onClick, this));
	}
	return this.boolResult;
};

BX.TreeCondCtrlDateTime.prototype.ShowCalendarControl = function()
{
	if (!!this.calendarText)
	{
		BX.calendar({
			node: this.calendarText,
			field: this.input,
			form: '',
			bTime: (this.format === 'datetime'),
			bHideTime: false
		});
	}
};

BX.TreeConditions = function(arParams, obTree, obControls)
{
	var i;

	BX.onCustomEvent('onTreeConditionsInit', [arParams, obTree, obControls]);

	this.boolResult = false;
	if (!arParams || typeof arParams !== 'object' || !arParams.parentContainer)
	{
		return this.boolResult;
	}
	this.parentContainer = arParams.parentContainer;
	if (!arParams.form && !arParams.formName)
	{
		return this.boolResult;
	}
	this.arStartParams = arParams;
	this.form = (!!arParams.form ? arParams.form : null);
	this.formName = (!!arParams.formName ? arParams.formName : null);
	this.mess = null;
	if (BX.type.isPlainObject(arParams.mess))
	{
		this.mess = arParams.mess;
		BX.message(this.mess);
	}

	this.messTree = {
		'SELECT_CONTROL': BX.message('JC_CORE_TREE_SELECT_CONTROL'),
		'ADD_CONTROL': BX.message('JC_CORE_TREE_ADD_CONTROL'),
		'DELETE_CONTROL': BX.message('JC_CORE_TREE_DELETE_CONTROL'),
		'CONTROL_DATETIME_ICON': BX.message('JC_CORE_TREE_CONTROL_DATETIME_ICON'),
		'CONDITION_ERROR': BX.message('JC_CORE_TREE_CONDITION_ERROR'),
		'CONDITION_FATAL_ERROR': BX.message('JC_CORE_TREE_CONDITION_FATAL_ERROR')
	};

	if (BX.type.isPlainObject(arParams.messTree))
	{
		for (i in arParams.messTree)
		{
			if (arParams.messTree.hasOwnProperty(i))
			{
				this.messTree[i] = arParams.messTree[i];
			}
		}
	}

	this.sepID = (!!arParams.sepID ? arParams.sepID : '__');
	this.sepName = (!!arParams.sepName ? arParams.sepName : this.sepID);
	this.prefix = (!!arParams.prefix ? arParams.prefix : 'rule');

	this.AtomTypes = {
		'prefix': BX.TreeCondCtrlAtom,
		'input': BX.TreeCondCtrlInput,
		'select': BX.TreeCondCtrlSelect,
		'lazySelect': BX.TreeCondCtrlLazySelect,
		'popup': BX.TreeCondCtrlPopup,
		'userPopup': BX.TreeUserCondCtrlPopup,
		'datetime': BX.TreeCondCtrlDateTime,
		'dialog': BX.TreeCondCtrlDialog,
		'multiDialog': BX.TreeMultiCondCtrlDialog
	};

	if (!!arParams.atomtypes && typeof(arParams.atomtypes) === 'object')
	{
		for (i in arParams.atomtypes)
		{
			if (!arParams.atomtypes.hasOwnProperty(i) || !!this.AtomTypes[i])
				continue;
			this.AtomTypes[i] = arParams.atomtypes[i];
		}
	}

	if (!obTree || typeof obTree !== 'object')
	{
		return this.boolResult;
	}
	this.tree = obTree;

	if (!obControls || !BX.type.isArray(obControls))
	{
		return this.boolResult;
	}
	this.controls = obControls;
	this.boolResult = true;
	BX.ready(BX.delegate(this.RenderTree, this));
	return this.boolResult;
};

BX.TreeConditions.prototype.Delete = function()
{
	if (this.tree)
	{
		this.DeleteLevel(this.tree);
	}
};

/**
 * @return {boolean|{}}
 */
BX.TreeConditions.prototype.ControlSearch = function(controlId)
{
	var curControl = false,
		i;
	if (this.boolResult && !!this.controls)
	{
		for (i = 0; i < this.controls.length; i++)
		{
			if (!!this.controls[i].controlgroup)
			{
				curControl = this.ControlInGrpSearch(this.controls[i].children, controlId);
				if (false !== curControl)
				{
					break;
				}
			}
			else
			{
				if (controlId === this.controls[i].controlId)
				{
					curControl = this.controls[i];
					break;
				}
			}
		}
	}
	return curControl;
};

/**
 * @return {boolean|{}}
 */
BX.TreeConditions.prototype.ControlInGrpSearch = function(controls, controlId)
{
	var curControl = false,
		i;
	if (this.boolResult && !!controls)
	{
		for (i = 0; i < controls.length; i++)
		{
			if (controlId === controls[i].controlId)
			{
				curControl = controls[i];
				break;
			}
		}
	}
	return curControl;
};

BX.TreeConditions.prototype.RenderTree = function()
{
	if (this.boolResult)
	{
		this.form = (this.form ? BX(this.form) : document.forms[this.formName]);
		if (!this.form)
		{
			this.boolResult = false;
		}
		else
		{
			this.formName = this.form.name;
			this.parentContainer = BX(this.parentContainer);
			if (!!this.parentContainer)
			{
				BX.adjust(this.parentContainer, {style: {position: 'relative', zIndex: 1}});
				this.RenderLevel(this.parentContainer, null, this.tree);
			}
			else
			{
				this.boolResult = false;
			}
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.RenderLevel = function(parentContainer, obParent, obTreeLevel, obParams)
{
	var CurControl,
		strContClassName,
		wrapper = null,
		logic = null,
		div = null,
		zIndex,
		i, j, k,
		elem,
		item,
		params,
		intCurrentIndex,
		obLogicParams;

	if (this.boolResult)
	{
		if (!parentContainer)
		{
			this.boolResult = false;
			return this.boolResult;
		}
		if (typeof obTreeLevel !== 'object')
		{
			this.boolResult = false;
			return this.boolResult;
		}
		if (obTreeLevel.id === null || obTreeLevel.id === undefined)
		{
			this.boolResult = false;
			return this.boolResult;
		}
		if (obTreeLevel.controlId === null || obTreeLevel.controlId === undefined)
		{
			this.boolResult = false;
			return this.boolResult;
		}

		CurControl = this.ControlSearch(obTreeLevel.controlId);
		if (!CurControl)
		{
			this.boolResult = false;
			return this.boolResult;
		}

		strContClassName = (!!CurControl.group ? (obParent !== null ? 'condition-container' : 'condition-border') : 'condition-simple-control');

		zIndex = parseInt(BX.style(parentContainer, 'z-index'), 10);
		if (isNaN(zIndex))
		{
			zIndex = 1;
		}
		wrapper = BX.create(
			'DIV',
			{
				props: {
					id: parentContainer.id + this.sepID + obTreeLevel.id+'_wrap',
					className: 'condition-wrapper'
				},
				style: { zIndex: zIndex+100 }
			}
		);

		div = wrapper.appendChild(BX.create(
			'DIV',
			{
				props: {
					id: parentContainer.id + this.sepID + obTreeLevel.id,
					className: strContClassName
				},
				style: { zIndex: zIndex+110 }
			}
		));
		if (!div)
		{
			this.boolResult = false;
			return this.boolResult;
		}
		if (parentContainer.childNodes.length === 0)
		{
			parentContainer.appendChild(wrapper);
		}
		else
		{
			parentContainer.insertBefore(wrapper, parentContainer.childNodes[parentContainer.childNodes.length - 1]);
		}

		div.appendChild(BX.create(
			'INPUT',
			{
				props: {
					type: 'hidden',
					id: div.id+'_controlId',
					name: (this.prefix + '[' + parentContainer.id + this.sepID + obTreeLevel.id + '][controlId]').replace(this.parentContainer.id+this.sepID,''),
					className: '',
					value: obTreeLevel.controlId
				}
			}
		));

		obTreeLevel.wrapper = wrapper;
		obTreeLevel.logic = logic;
		obTreeLevel.container = div;
		obTreeLevel.obj = [];
		obTreeLevel.addBtn = null;
		obTreeLevel.deleteBtn = null;
		obTreeLevel.visual = null;

		if (obParent !== null)
		{
			if (obTreeLevel.showDeleteButton === null || obTreeLevel.showDeleteButton === undefined || obTreeLevel.showDeleteButton === true)
			{
				this.RenderDeleteBtn(obTreeLevel, obParent);
			}
		}

		if (!!obTreeLevel.err_cond && obTreeLevel.err_cond === 'Y')
		{
			div.appendChild(BX.create(
				'SPAN',
				{
					props: {
						className: 'condition-alert',
						title: (!!obTreeLevel.err_cond_mess ? obTreeLevel.err_cond_mess : (!obTreeLevel.fatal_err_cond ? this.messTree.CONDITION_ERROR : this.messTree.CONDITION_FATAL_ERROR))
					}
				}
			));
		}

		if (!obTreeLevel.fatal_err_cond)
		{
			if (!!CurControl.group)
			{
				if (!!CurControl.visual && typeof (CurControl.visual) === 'object')
				{
					obTreeLevel.visual = CurControl.visual;
					if (!(!!obTreeLevel.visual.values && BX.type.isArray(obTreeLevel.visual.values) &&
						!!obTreeLevel.visual.logic && BX.type.isArray(obTreeLevel.visual.logic) &&
						obTreeLevel.visual.values.length === obTreeLevel.visual.logic.length
					))
					{
						obTreeLevel.visual = null;
					}
				}
			}

			for (i = 0; i < CurControl.control.length; i++)
			{
				elem = null;
				if (0 < i)
				{
					div.appendChild(BX.create(
						'SPAN',
						{
							props: { className: 'condition-space' },
							html: '&nbsp;'
						}
					));
				}

				item = CurControl.control[i];
				if (typeof item === 'object')
				{
					params = {};
					for (k in item)
					{
						if (item.hasOwnProperty(k))
						{
							params[k] = (k === 'name' ?
								(this.prefix + '[' + parentContainer.id + this.sepID + obTreeLevel.id + '][' + item[k] + ']').replace(this.parentContainer.id+this.sepID,'') :
								item[k]
							);
						}
					}

					if (!!obTreeLevel.visual)
					{
						if (BX.util.in_array(item.id, obTreeLevel.visual.controls))
						{
							if (!params.events)
							{
								params.events = {};
							}
							params.events.visual = BX.delegate(function(){ this.ChangeVisual(obTreeLevel); }, this);
						}
					}

					if (!!this.AtomTypes[item.type])
					{
						if (item.type === 'prefix')
						{
							elem = new this.AtomTypes[item.type](div, obTreeLevel, params);
						}
						else
						{
							elem = new this.AtomTypes[item.type](div, obTreeLevel, params);
							obTreeLevel.obj[obTreeLevel.obj.length] = elem;
						}
					}
				}
				else
				{
					elem = new BX.TreeCondCtrlAtom(div, obTreeLevel, item);
				}
			}

			if (!!CurControl.group)
			{
				div.appendChild(BX.create(
					'DIV',
					{
						props: { className: 'condition-group-sep' }
					}
				));

				if(!!CurControl.containsOneAction)
				{
					this.RenderCreateOneActionBtn(obTreeLevel, CurControl);
				}
				else
				{
					this.RenderCreateBtn(obTreeLevel, CurControl);
				}

				if (!!obTreeLevel.children && !!obTreeLevel.children.length && obTreeLevel.children.length > 0)
				{
					if (!!obTreeLevel.visual && typeof (obTreeLevel.visual) === 'object')
					{
						intCurrentIndex = this.SearchVisual(obTreeLevel);
						if (-1 < intCurrentIndex)
						{
							obLogicParams = obTreeLevel.visual.logic[intCurrentIndex];
							obLogicParams.visual = BX.delegate(function(){ this.NextVisual(obTreeLevel); }, this);
							for (j = 0; j < obTreeLevel.children.length; j++)
							{
								this.RenderLevel(div, obTreeLevel, obTreeLevel.children[j]);
								if (j < (obTreeLevel.children.length - 1))
								{
									this.CreateLogic(obTreeLevel.children[j], obTreeLevel, obLogicParams);
								}
							}
						}
						else
						{
							for (j = 0; j < obTreeLevel.children.length; j++)
							{
								this.RenderLevel(div, obTreeLevel, obTreeLevel.children[j]);
							}
						}
					}
					else
					{
						for (j = 0; j < obTreeLevel.children.length; j++)
						{
							this.RenderLevel(div, obTreeLevel, obTreeLevel.children[j]);
						}
					}
				}
			}
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.CreateLevel = function(obTreeLevel, controlId, num)
{
	var indexPrev,
		intCurrentIndex,
		obLogicParams;

	if (!!obTreeLevel && !!obTreeLevel.children)
	{
		if (num === undefined || num === null)
		{
			num = obTreeLevel.children.length;
		}
		obTreeLevel.children[obTreeLevel.children.length] = {
			id: num,
			controlId: controlId,
			values: {},
			children: []
		};
		if (!this.RenderLevel(obTreeLevel.container, obTreeLevel, obTreeLevel.children[obTreeLevel.children.length-1]))
		{
			obTreeLevel.children.pop();
		}
		else
		{
			indexPrev = this.SearchForCreateLogic(obTreeLevel);
			if (-1 < indexPrev)
			{
				intCurrentIndex = this.SearchVisual(obTreeLevel);
				if (-1 < intCurrentIndex)
				{
					obLogicParams = obTreeLevel.visual.logic[intCurrentIndex];
					obLogicParams.visual = BX.delegate(function(){ this.NextVisual(obTreeLevel); }, this);
					this.CreateLogic(obTreeLevel.children[indexPrev], obTreeLevel, obLogicParams);
				}
			}
			BX.onCustomEvent('onAdminTabsChange');
		}
	}
};

BX.TreeConditions.prototype.SearchForDeleteLevel = function(obTreeLevel, obParent)
{
	var arRes = {
			indexDel: -1,
			indexPrev: -1
		},
		j,
		boolNeedDelVisual;

	if (!!obParent)
	{
		if (!!obParent.children)
		{
			for (j = 0; j < obParent.children.length; j++)
			{
				if (!!obParent.children[j] && obParent.children[j] === obTreeLevel)
				{
					arRes.indexDel = j;
					break;
				}
			}
			if (-1 < arRes.indexDel)
			{
				if (!!obParent.visual && typeof(obParent.visual) === 'object')
				{
					boolNeedDelVisual = true;
					for (j = arRes.indexDel + 1; j < obParent.children.length; j++)
					{
						if (!!obParent.children[j])
						{
							boolNeedDelVisual = false;
							break;
						}
					}
					if (boolNeedDelVisual)
					{
						for (j = arRes.indexDel - 1; j > -1; j--)
						{
							if (!!obParent.children[j])
							{
								arRes.indexPrev = j;
								break;
							}
						}
					}
				}
			}
		}
	}
	return arRes;
};

/**
 * @return {number}
 */
BX.TreeConditions.prototype.SearchForCreateLogic = function(obTreeLevel, indexCurrent)
{
	var indexPrev = -1,
		j;
	if (!!obTreeLevel && !!obTreeLevel.children)
	{
		if (!!obTreeLevel.visual && typeof(obTreeLevel.visual) === 'object')
		{
			if (indexCurrent === undefined || indexCurrent === null)
			{
				indexCurrent = obTreeLevel.children.length-1;
			}
			for (j = indexCurrent-1; j > -1; j--)
			{
				if (!!obTreeLevel.children[j])
				{
					indexPrev = j;
					break;
				}
			}
		}
	}
	return indexPrev;
};

BX.TreeConditions.prototype.DeleteLevel = function(obTreeLevel, obParent)
{
	var j,
		arDel;

	if (!!obTreeLevel)
	{
		if (!!obTreeLevel.children)
		{
			if (obTreeLevel.children.length > 0)
			{
				for (j = 0; j < obTreeLevel.children.length; j++)
				{
					this.DeleteLevel(obTreeLevel.children[j]);
				}
			}
			obTreeLevel.children.length = 0;
		}
		if (!!obTreeLevel.addBtn)
		{
			if (obTreeLevel.addBtn.link)
			{
				BX.unbindAll(obTreeLevel.addBtn.link);
				obTreeLevel.addBtn.link = BX.remove(obTreeLevel.addBtn.link);
			}
			if (obTreeLevel.addBtn.select)
			{
				BX.unbindAll(obTreeLevel.addBtn.select);
				obTreeLevel.addBtn.link = BX.remove(obTreeLevel.addBtn.select);
			}
			obTreeLevel.addBtn = BX.remove(obTreeLevel.addBtn);
		}
		if (!!obTreeLevel.obj)
		{
			if (obTreeLevel.obj.length > 0)
			{
				for (j = 0; j < obTreeLevel.obj.length; j++)
				{
					obTreeLevel.obj[j].Delete();
				}
				obTreeLevel.obj.length = 0;
			}
		}
		if (!!obTreeLevel.deleteBtn)
		{
			BX.unbindAll(obTreeLevel.deleteBtn);
			obTreeLevel.deleteBtn = BX.remove(obTreeLevel.deleteBtn);
		}

		BX.unbindAll(obTreeLevel.container);
		obTreeLevel.container = BX.remove(obTreeLevel.container);
		if (!!obTreeLevel.logic)
		{
			BX.unbindAll(obTreeLevel.logic);
			obTreeLevel.logic = BX.remove(obTreeLevel.logic);
		}
		BX.unbindAll(obTreeLevel.wrapper);
		obTreeLevel.wrapper = BX.remove(obTreeLevel.wrapper);

		arDel = this.SearchForDeleteLevel(obTreeLevel, obParent);
		if (-1 < arDel.indexDel)
		{
			obParent.children[arDel.indexDel] = null;
			obTreeLevel = null;
		}
		if (-1 < arDel.indexPrev)
		{
			this.DeleteLogic(obParent.children[arDel.indexPrev]);
		}
		BX.onCustomEvent('onAdminTabsChange');
		BX.onCustomEvent('onAdminTabsDeleteLevel', [obTreeLevel, obParent]);
	}
};

BX.TreeConditions.prototype.RenderCreateBtn = function(obTreeLevel, CurControl)
{
	var divAdd,
		addBtn,
		addSelect,
		i,
		j,
		grp,
		found;

	if (this.boolResult)
	{
		if (!!obTreeLevel.container)
		{
			if (CurControl.group)
			{
				divAdd = obTreeLevel.container.appendChild(BX.create(
					'DIV',
					{
						props: {
							id: obTreeLevel.container.id + '_add',
							className: 'condition-add'
						}
					}
				));
				if (!divAdd)
				{
					this.boolResult = false;
					return this.boolResult;
				}
				obTreeLevel.addBtn = divAdd;
				addBtn = divAdd.appendChild(BX.create(
					'A',
					{
						props: {
							id: divAdd.id + '_link',
							className: ''
						},
						style: {
							display: ''
						},
						html: (!!CurControl.mess && !!CurControl.mess.ADD_CONTROL ? CurControl.mess.ADD_CONTROL : this.messTree.ADD_CONTROL)
					}
				));
				addSelect = divAdd.appendChild(BX.create(
					'SELECT',
					{
						props: {
							id: divAdd.id + '_select',
							className: ''
						},
						style: {
							display: 'none'
						}
					}
				));
				if (!!addSelect)
				{
					addSelect.appendChild(BX.create(
						'OPTION',
						{
							props: {
								value: ''
							},
							html: (!!CurControl.mess && !!CurControl.mess.SELECT_CONTROL ? CurControl.mess.SELECT_CONTROL : this.messTree.SELECT_CONTROL)
						}
					));

					for (i = 0; i < this.controls.length; i++)
					{
						if (BX.util.in_array(CurControl.controlId, this.controls[i].showIn))
						{
							if (!!this.controls[i].controlgroup)
							{
								found = false;
								grp = BX.create(
									'OPTGROUP',
									{
										props: { label: this.controls[i].label }
									}
								);
								if (!!grp && !!this.controls[i].children && !!this.controls[i].children.length && this.controls[i].children.length > 0)
								{
									for (j = 0; j <  this.controls[i].children.length; j++)
									{
										if (BX.util.in_array(CurControl.controlId, this.controls[i].children[j].showIn))
										{
											found = true;
											grp.appendChild(BX.create(
												'OPTION',
												{
													props: {value: this.controls[i].children[j].controlId},
													html: BX.util.htmlspecialchars(this.controls[i].children[j].label)
												}
											));
										}
									}
									if (found)
										addSelect.appendChild(grp);
								}
							}
							else
							{
								addSelect.appendChild(BX.create(
									'OPTION',
									{
										props: { value: this.controls[i].controlId },
										html: BX.util.htmlspecialchars(this.controls[i].label)
									}
								));
							}
						}
					}

				}
				if (!!addBtn && !!addSelect)
				{
					divAdd.link = addBtn;
					divAdd.select = addSelect;
					BX.bind(addBtn,'click', BX.delegate(
						function(){
							BX.style(divAdd.select, 'display', '');
							BX.style(divAdd.link, 'display', 'none');
							BX.focus(divAdd.select);
						}, divAdd
					));
					BX.bind(addSelect, 'change', BX.delegate(
						function(){
							if (0 < divAdd.select.selectedIndex)
							{
								this.CreateLevel(obTreeLevel, divAdd.select.options[divAdd.select.selectedIndex].value);
							}
							divAdd.select.selectedIndex = 0;
							BX.style(divAdd.select, 'display', 'none');
							BX.style(divAdd.link, 'display', '');
						}, this
					));
					BX.bind(addSelect, 'blur', BX.delegate(
						function(){
							divAdd.select.selectedIndex = 0;
							BX.style(divAdd.select, 'display', 'none');
							BX.style(divAdd.link, 'display', '');
						}, divAdd
					));
					BX.bind(addSelect, 'keypress', BX.delegate(
						function(e){
							if (!e)
							{
								e = window.event;
							}
							if (!!e.keyCode && (e.keyCode === 13 || e.keyCode === 27))
							{
								if (e.keyCode === 13)
								{
									if (0 < divAdd.select.selectedIndex)
									{
										this.CreateLevel(obTreeLevel);
									}
									divAdd.select.selectedIndex = 0;
								}
								else
								{
									divAdd.select.selectedIndex = 0;
								}
								BX.style(divAdd.select, 'display', 'none');
								BX.style(divAdd.link, 'display', '');
								if (e.keyCode === 13)
								{
									return BX.PreventDefault(e);
								}
							}
						}, this
					));


				}
				else
				{
					this.boolResult = false;
				}
			}
		}
		else
		{
			this.boolResult = false;
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.RenderCreateOneActionBtn = function(obTreeLevel, CurControl)
{
	if(this.RenderCreateBtn(obTreeLevel, CurControl))
	{
		BX.bind(obTreeLevel.addBtn.select, 'change', function(){
			BX.hide(obTreeLevel.addBtn);
		});
		BX.adjust(obTreeLevel.addBtn, {attrs: {'bx-data-create-one-action-btn': true}});

		var addBtnId = BX.clone(obTreeLevel.addBtn.id, true);
		BX.addCustomEvent('onAdminTabsDeleteLevel', function(obTreeLevel, obParent){
			if(
					obParent &&
					obParent.addBtn &&
					obParent.addBtn.getAttribute('bx-data-create-one-action-btn') &&
					obParent.addBtn.id === addBtnId
			)
			{
				BX.show(obParent.addBtn, 'block');
			}
		});

		var isEmptyObject = function(obj)
		{
			if (obj == null) return true;
			if (obj.length && obj.length > 0)
				return false;
			if (obj.length === 0)
				return true;

			for (var key in obj) {
				if (hasOwnProperty.call(obj, key))
					return false;
			}

			return true;
		}
		if(obTreeLevel.children && !isEmptyObject(obTreeLevel.children))
		{
			BX.hide(obTreeLevel.addBtn);
		}
	}

	return this.boolResult;
};

BX.TreeConditions.prototype.RenderDeleteBtn = function(obTreeLevel, obParent)
{
	var delBtn;

	if (this.boolResult)
	{
		if (!!obTreeLevel.container)
		{
			delBtn = obTreeLevel.container.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: obTreeLevel.id + '_del',
						className: 'condition-delete',
						title: this.messTree.DELETE_CONTROL
					}
				}
			));
			if (!!delBtn)
			{
				obTreeLevel.delBtn = delBtn;
				BX.bind(delBtn, 'click', BX.delegate(
					function(){
						this.DeleteLevel(obTreeLevel, obParent);
					},
					this
				));
				BX.bind(obTreeLevel.container, 'mouseover', BX.delegate(
					function(e){
						BX.style(delBtn, 'display', 'block');
						return BX.eventCancelBubble(e);
					},
					this
				));
				BX.bind(obTreeLevel.container, 'mouseout', BX.delegate(
					function(e){
						BX.style(delBtn, 'display', 'none');
						return BX.eventCancelBubble(e);
					},
					this
				));
			}
			else
			{
				this.boolResult = false;
			}
		}
		else
		{
			this.boolResult = false;
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.CreateLogic = function(obTreeLevel, obParent, obParams)
{
	var logic,
		strClass;

	if (this.boolResult)
	{
		if (!!obTreeLevel.logic && typeof (obTreeLevel.logic) === 'object')
		{
			this.boolResult = this.UpdateLogic(obTreeLevel, obParams);
		}
		else
		{
			strClass = 'condition-logic';
			if (!!obParams.style)
			{
				strClass = strClass.concat(' ', obParams.style);
			}
			logic = BX.create(
				'DIV',
				{
					props: { className: strClass },
					style: { zIndex: parseInt(BX.style(obTreeLevel.wrapper, 'z-index'), 10)+1 },
					html: obParams.message
				}
			);
			if (!!logic)
			{
				obTreeLevel.wrapper.insertBefore(logic,obTreeLevel.wrapper.childNodes[0]);
				obTreeLevel.logic = logic;
				BX.bind(obTreeLevel.logic, 'click', obParams.visual);
			}
			else
			{
				this.boolResult = false;
			}
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.DeleteLogic = function(obTreeLevel)
{
	if (this.boolResult && !!obTreeLevel.logic && typeof (obTreeLevel.logic) === 'object')
	{
		BX.unbindAll(obTreeLevel.logic);
		obTreeLevel.logic = BX.remove(obTreeLevel.logic);
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.UpdateLogic = function(obTreeLevel, obParams)
{
	var strClass;

	if (this.boolResult && !!obTreeLevel.logic && typeof (obTreeLevel.logic) === 'object')
	{
		strClass = 'condition-logic';
		if (!!obParams.style)
			strClass = strClass.concat(' ', obParams.style);
		BX.adjust(obTreeLevel.logic, {props: {className: strClass}, html : obParams.message });
	}
	return this.boolResult;
};

/**
 * @return {number}
 */
BX.TreeConditions.prototype.SearchVisual = function(obTreeLevel)
{
	var intCurrentIndex = -1,
		arCurrent,
		strName,
		i, j, k,
		oneRow,
		boolEqual;

	if (this.boolResult && !!obTreeLevel.visual && typeof (obTreeLevel.visual) === 'object')
	{
		if (!!obTreeLevel.visual.controls)
		{
			arCurrent = {};
			for (i in obTreeLevel.visual.controls)
			{
				if (obTreeLevel.visual.controls.hasOwnProperty(i))
				{
					strName = obTreeLevel.visual.controls[i];
					arCurrent[strName] = obTreeLevel.values[strName];
				}
			}
			if (!!obTreeLevel.visual.values)
			{
				for (j = 0; j < obTreeLevel.visual.values.length; j++)
				{
					oneRow = obTreeLevel.visual.values[j];
					boolEqual = true;
					for (k in arCurrent)
					{
						if (oneRow[k] != arCurrent[k])
						{
							boolEqual = false;
							break;
						}
					}
					if (boolEqual)
					{
						intCurrentIndex = j;
						break;
					}
				}
			}
		}
	}
	return intCurrentIndex;
};

BX.TreeConditions.prototype.ChangeVisual = function(obTreeLevel)
{
	var intCurrentIndex,
		obParams,
		j;

	if (this.boolResult)
	{
		intCurrentIndex = this.SearchVisual(obTreeLevel);
		if (-1 < intCurrentIndex)
		{
			obParams = obTreeLevel.visual.logic[intCurrentIndex];
			for (j = 0; j < obTreeLevel.children.length; j++)
			{
				if (!!obTreeLevel.children[j])
					this.UpdateLogic(obTreeLevel.children[j], obParams);
			}
		}
	}
	return this.boolResult;
};

BX.TreeConditions.prototype.NextVisual = function(obTreeLevel)
{
	var intCurrentIndex,
		arValues,
		i, j,
		obParams;

	if (this.boolResult)
	{
		intCurrentIndex = this.SearchVisual(obTreeLevel);
		if (-1 < intCurrentIndex)
		{
			intCurrentIndex++;
			if (intCurrentIndex >= obTreeLevel.visual.logic.length)
				intCurrentIndex = 0;

			arValues = obTreeLevel.visual.values[intCurrentIndex];
			for (j in arValues)
			{
				if (arValues.hasOwnProperty(j))
					obTreeLevel.values[j] = arValues[j];
			}
			for (i = 0; i < obTreeLevel.obj.length; i++)
			{
				obTreeLevel.obj[i].ReInitValue(obTreeLevel.visual.controls);
			}

			obParams = obTreeLevel.visual.logic[intCurrentIndex];
			for (i = 0; i < obTreeLevel.children.length; i++)
			{
				if (!!obTreeLevel.children[i])
					this.UpdateLogic(obTreeLevel.children[i], obParams);
			}
		}

		BX.onCustomEvent('onNextVisualChange', [obTreeLevel]);
	}
};
})(window);