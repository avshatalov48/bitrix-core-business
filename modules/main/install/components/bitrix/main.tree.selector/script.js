function JCTreeSelectControl(arParams)
{
	var _this = this;
	this.arParams = arParams; // {ONSELECT, MULTIPLE, VALUE, AJAX_PAGE, AJAX_PARAMS}

	this.arTreeData = {};

	this.multiple = this.arParams.MULTIPLE;

	if (null != arParams.VALUE)
		this.SetValue(arParams.VALUE);

	this.div = null;
	this._onkeypress = function(e)
	{
		if (null == e) e = window.event;
		if (null == e) return;

		if (e.keyCode == 27)
			_this.CloseDialog();
	};

	// current value and its setter and getter
	var current_value = [];

	this.getElementsByName = function(tag_name, control_name)
	{
		var result = [];
		var inputs = document.getElementsByTagName(tag_name);
		for(var i = 0; i < inputs.length; i++)
		{
			if(inputs[i].getAttribute("name") == control_name)
				result.push(inputs[i]);
		}
		return result;
	};

	this.SetValueFromInput = function(input_name)
	{
		var values = [];
		var inp = document.getElementById(input_name);
		if(inp)
		{
			values[values.length] = inp.value;
		}
		else
		{
			var inputs = this.getElementsByName('INPUT', input_name);
			if(inputs && inputs.length > 0)
			{
				for(var i = 0; i < inputs.length; i++)
					values[values.length] = inputs[i].value;
			}
			else
			{
				inputs = this.getElementsByName('INPUT', input_name + '[]');
				if(inputs && inputs.length > 0)
				{
					for(i = 0; i < inputs.length; i++)
						values[values.length] = inputs[i].value;
				}
			}
		}
		this.SetValue(values);
	};

	this.SetValue = function(value)
	{
		if (typeof value == 'string' || typeof value == 'object' && value.constructor == String)
			value = value.split(',');

		if (typeof value == 'object')
		{
			current_value = [];
			for (var i = 0; i < value.length; i++)
			{
				var q = parseInt(value[i]);
				if (!isNaN(q))
					current_value[current_value.length] = q;
			}
		}

		return typeof current_value == 'object';
	};

	this.GetValue = function(tostring)
	{
		if (null == tostring) tostring = false;

		if (tostring)
		{
			if (null != current_value)
				return current_value.join(',');
		}

		return current_value;
	};

	this._control = null;
	this._timerId = null;

	this._delay = 500;

	this._value = '';
	this._result = [];
	this._ajax_error = '';

	this._div = null;

	this._search_focus = -1;

	this.InitControl = function(control_id)
	{
		this._control = document.getElementById(control_id);
		if (this._control)
		{
			this._control.value = _this.arParams.START_TEXT;
			this._control.value_tmp = this._control.value;

			this._control.className = 'bx-search-control-empty';
			this._control.onfocus = this.__control_focus;
			this._control.onblur = this.__control_blur;

			this._control.onkeydown = this.__control_keypress;
		}
	};

	this.Run = function()
	{
		if (null != _this._timerId)
			clearTimeout(_this._timerId);

		_this._search_focus = -1;

		if (_this._control.value && _this._control.value != _this._control.value_tmp)
		{
			_this._value = _this._control.value;

			var url = _this.arParams.AJAX_PAGE+'?MODE=search&win_id=' + _this.arParams.WIN.id + '&search=' + encodeURIComponent(_this._value);
			if (_this.arParams.AJAX_PARAMS)
			{
				for(var param_name in _this.arParams.AJAX_PARAMS)
					url += '&' + param_name + '=' + encodeURIComponent(_this.arParams.AJAX_PARAMS[param_name]);
			}

			BX.ajax.get(url, _this.SetResult);
		}
	};

	this.SetResult = function(data)
	{
		var DATA = [];
		_this._ajax_error = '';

		if (data.length > 0)
		{
			if(data.substr(0, 1) == '[')
				eval('DATA = ' + data);
			else
				_this._ajax_error = data;
		}

		_this._result = DATA;
		_this.SearchShow();
	};

	this.SearchShow = function()
	{
		if (null == _this._div)
		{
			var _content_div = BX('_f_popup_content');
			_content_div.style.position = 'relative';

			var pos = BX.pos(_this._control, true);

			_this._div = _content_div.insertBefore(document.createElement('DIV'), _content_div.firstChild);
			_this._div.className = 'mts-search-result';
			_this._div.style.top = pos.bottom + 'px';
			_this._div.style.left = '0px';
			/*_this._div.style.zIndex = 1110; */
			_this._div.style.zIndex = 111000;

			jsUtils.addCustomEvent('onTreeSearchClose', _this.__onclose, [], _this);
		}
		else
		{
			_this._div.innerHTML = '';
		}

		if (_this._result.length > 0)
		{
			for (var i = 0; i < _this._result.length; i++)
			{
				_this._result[i]._row = _this._div.appendChild(document.createElement('DIV'));
				_this._result[i]._row.className = 'mts-search-result-row';
				_this._result[i]._row.innerHTML = _this._result[i].NAME;

				_this._result[i]._row.onclick = _this.__result_row_click;

				_this._result[i]._row.__bx_data = _this._result[i];
			}
		}
		else
		{
			if(_this._ajax_error.length > 0)
				_this._div.innerHTML = '<i>' + _this._ajax_error + '</i>';
			else
				_this._div.innerHTML = '<i>' + _this.arParams['NO_SEARCH_RESULT_TEXT'] + '</i>';
		}

		_this._div.style.display = 'block';

	};

	this._openSection = function(SECTION_ID, bScrollToSection)
	{
		if (null == bScrollToSection)
			bScrollToSection = false;

		var obSectionDiv = document.getElementById('mts_section_' + SECTION_ID);
		if (null != obSectionDiv)
		{
			var obParentSection = obSectionDiv.parentNode;
			if (null != obParentSection)
			{
				obParentSection = obParentSection.previousSibling;

				if (null != obParentSection && obParentSection.id && obParentSection.id.substr(0, 20) == 'mts_section_')
				{
					_this._openSection(parseInt(obParentSection.id.substr(20)));
				}
			}

			_this.LoadSection(SECTION_ID, true, bScrollToSection);
		}
	};

	this.__result_row_click = function()
	{
		_this._openSection(this.__bx_data.SECTION_ID, true);

		var obUserRow = document.getElementById('mts_' + this.__bx_data.ID);
		if (null != obUserRow)
		{

			if (obUserRow.className != 'mts-row mts-selected')
			{
				obUserRow.onclick();
			}
		}
		else
		{
			if(_this.multiple)
				current_selected[current_selected.length] = parseInt(this.__bx_data.ID);
			else
				current_selected[0] = parseInt(this.__bx_data.ID);
		}
	};

	this.__onclose = function()
	{
		if (null != _this._div)
			_this._div.parentNode.removeChild(_this._div);

		if (null != _this._timerId)
			clearTimeout(_this._timerId);

		jsUtils.removeCustomEvent('onTreeSearchClose', _this.__onclose);
	};

	this.__control_keypress = function(e)
	{
		if (null == e)
			e = window.event;

		// 40 - down, 38 - up, 13 - enter
		switch (e.keyCode)
		{
			case 13: //enter
				if (_this._search_focus < 0)
					_this.Run();
				else
				{
					_this._control.onblur();
					_this._control.blur();
					_this._result[_this._search_focus]._row.onclick();
				}

			break;

			case 40: //down
				if (_this._result.length > 0 && _this._search_focus < _this._result.length-1)
				{
					if (_this._search_focus >= 0)
						_this._result[_this._search_focus]._row.className = 'mts-search-result-row';

					_this._search_focus++;
					_this._result[_this._search_focus]._row.className = 'mts-search-result-row mts-search-result-row-selected';
				}
			break;

			case 38: //up
				if (_this._result.length > 0 && _this._search_focus > -1)
				{
					_this._result[_this._search_focus]._row.className = 'mts-search-result-row';
					_this._search_focus--;

					if (_this._search_focus >= 0)
						_this._result[_this._search_focus]._row.className = 'mts-search-result-row mts-search-result-row-selected';
				}

			break;
			default:
				if (null != _this._timerId)
					clearTimeout(_this._timerId);

				_this._timerId = setTimeout(_this.Run, _this._delay);
			break;
		}
	};

	this.__control_focus = function()
	{
		if (this.value == this.value_tmp)
		{
			this.value = '';
			this.className = '';
		}

		if (null != this._div)
			this._div.style.display = 'block';
	};

	this.__control_blur = function()
	{
		if (_this.value == '')
		{
			_this.value = _this.value_tmp;
			_this.className = 'bx-search-control-empty';
		}

		if (null != _this._div)
		{
			setTimeout(function() {
				_this._div.style.display = 'none';
			}, 300);
		}
	};

	this.OnSelect = function()
	{
		if (null != this.arParams.ONSELECT)
		{
			var value = this.GetValue();
			if (this.arParams.GET_FULL_INFO)
			{
				var new_value = [];
				for (var i = 0; i < value.length; i++)
				{
					new_value[new_value.length] = this.arTreeData[value[i]];
				}

				value = new_value;
				this.arParams.ONSELECT(value);
			}
			else
			{
				this.arParams.ONSELECT(value);
			}
		}
	};

	this.Show = function(arParams)
	{
		if(null != this.div)
			return;

		var _this = this;


		if (null == arParams) arParams = {};
		if (null == arParams.id) arParams.id = 'tree_selector_select_control';
		if (null == arParams.className) arParams.className = '';

		this.arParams.WIN = arParams;

		CHttpRequest.Action = function(result) {_this._ShowWindow(result)};
		var url = this.arParams.AJAX_PAGE+'?win_id=' +this.arParams.WIN.id;
		if (this.arParams.AJAX_PARAMS)
		{
			for(var param_name in this.arParams.AJAX_PARAMS)
				url += '&' + param_name + '=' + encodeURIComponent(this.arParams.AJAX_PARAMS[param_name]);
		}

		if(this.arParams.INPUT_NAME)
			this.SetValueFromInput(this.arParams.INPUT_NAME);

		var value = this.GetValue(true);
		if ((this.multiple ? value.length : value) > 0)
			url += '&value=' + value;

		if (this.multiple)
			url += '&multiple=Y';

		ShowWaitWindow();
		CHttpRequest.Send(url);
	};

	this._ShowWindow = function(result)
	{
		CloseWaitWindow();

		var _this = this;

		this.div = document.body.appendChild(document.createElement("DIV"));

		this.div.id = this.arParams.WIN.id;
		this.div.className = "settings-float-form" + (this.arParams.WIN && this.arParams.WIN.className ? ' ' + this.arParams.WIN.className : '');

		this.div.style.position = 'absolute';
		/*this.div.style.zIndex = '1100'; */
		this.div.style.zIndex = '110000';

		this.div.innerHTML = result;

		this.div.__object = this;

		var obSize = BX.GetWindowSize();

		var left = parseInt(obSize.scrollLeft + obSize.innerWidth/2 - this.div.offsetWidth/2);
		var top = parseInt(obSize.scrollTop + obSize.innerHeight/2 - this.div.offsetHeight/2);

		jsFloatDiv.Show(this.div, left, top);

		jsUtils.onCustomEvent('onTreeSearchShow', {div: this.div});
		jsUtils.onCustomEvent('onTreeSearchShow', {div: this.div});

		BX.bind(document, "keypress", this._onkeypress);
	};

	this.CloseDialog = function()
	{
		BX.unbind(document, "keypress", this._onkeypress);

		jsUtils.onCustomEvent('onTreeSearchClose', {div: this.div});

		this._div = null;

		jsFloatDiv.Close(this.div);
		this.div.parentNode.removeChild(this.div);
		this.div = null;
	};

	this.LoadSection = function(SECTION_ID, bShowOnly, bScrollToSection)
	{
		if (null == bShowOnly) bShowOnly = false;
		if (null == bScrollToSection) bScrollToSection = false;

		SECTION_ID = parseInt(SECTION_ID);

		var obSection = document.getElementById('mts_section_' + SECTION_ID);

		if (null == obSection.BX_LOADED)
		{
			var url = this.arParams.AJAX_PAGE+'?MODE=section&win_id=' + this.arParams.WIN.id + '&SECTION_ID=' + SECTION_ID;
			if (this.arParams.AJAX_PARAMS)
			{
				for(var param_name in this.arParams.AJAX_PARAMS)
					url += '&' + param_name + '=' + encodeURIComponent(this.arParams.AJAX_PARAMS[param_name]);
			}

			if (bScrollToSection)
			{
				BX.ajax.get(url, function(data){
					_this.ShowSection(data);
					document.getElementById('mts_search_layout').scrollTop = document.getElementById('mts_section_' + SECTION_ID).offsetTop - 40;
				});
			}
			else
			{
				BX.ajax.get(url, this.ShowSection);
			}
		}
		else if (bScrollToSection)
		{
			document.getElementById('mts_search_layout').scrollTop = document.getElementById('mts_section_' + SECTION_ID).offsetTop - 40;
		}

		var obChildren = document.getElementById('bx_children_' + SECTION_ID);
		if (bShowOnly || obChildren.style.display == 'none')
		{
			obSection.firstChild.className = obSection.firstChild.className.replace('mts-closed', 'mts-opened');

			obChildren.style.display = 'block';
		}
		else
		{
			obSection.firstChild.className = obSection.firstChild.className.replace('mts-opened', 'mts-closed');
			obChildren.style.display = 'none';
		}
	};

	this.ElementSet = function()
	{
		if (!!this.arParams.SET_ALWAYS || current_selected.length > 0)
		{
			this.SetValue(current_selected);
			this.OnSelect();
		}

		this.CloseDialog();
	};

	this.ElementSelect = function()
	{
		if(_this.multiple)
		{
			var bFound = false;
			for (var i = 0; i < current_selected.length; i++)
			{
				if (current_selected[i] == this.BX_ID)
				{
					bFound = true;
					break;
				}
			}

			if (bFound)
			{
				this.className = 'mts-row';
				current_selected = current_selected.slice(0, i).concat(current_selected.slice(i + 1));
				this.firstChild.checked = false;
			}
			else
			{
				current_selected[current_selected.length] = this.BX_ID;
				this.className = 'mts-row mts-selected';
				this.firstChild.checked = true;
			}
		}
		else
		{
			for (i = 0; i < current_selected.length; i++)
			{
				var row = document.getElementById('mts_' + current_selected[i]);
				if(row)
				{
					row.className = 'mts-row';
					row.firstChild.checked = false;
				}
			}

			current_selected = [this.BX_ID];
			this.className = 'mts-row mts-selected';
			this.firstChild.checked = true;
		}
	};

	this.ShowSection = function (data)
	{
		var DATA = [];

		if (data.length > 0)
			eval('DATA = ' + data);

		var SECTION_ID = DATA.SECTION_ID;
		var arElements = DATA.arElements;

		var obSection = document.getElementById('mts_section_' + SECTION_ID);

		if (!obSection.BX_LOADED)
		{
			obSection.BX_LOADED = true;

			var obSectionDiv = document.getElementById('mts_elements_' + SECTION_ID);
			if (obSectionDiv)
			{
				obSectionDiv.innerHTML = '';

				for (var i = 0; i < arElements.length; i++)
				{
					_this.arTreeData[arElements[i].ID] = {
						ID: arElements[i].ID,
						NAME: arElements[i].NAME
					};

					var obUserRow = document.createElement('DIV');
					obUserRow.id = 'mts_' + arElements[i].ID;
					obUserRow.className = 'mts-row';

					obUserRow.BX_ID = arElements[i].ID;

				if (_this.multiple)
				{
					var obCheckbox = document.createElement('INPUT');
					obCheckbox.type = 'checkbox';
					obCheckbox.id = 'mts_check_' + arElements[i].ID;
					obCheckbox.defaultChecked = false;

					for (var j = 0; j < current_selected.length; j++)
					{
						if (obUserRow.BX_ID == current_selected[j])
						{
							obCheckbox.defaultChecked = true;
							obUserRow.className += ' mts-selected';
							break;
						}
					}
				}
				else
				{
					for (j = 0; j < current_selected.length; j++)
					{
						if (obUserRow.BX_ID == current_selected[j])
						{
							obUserRow.className += ' mts-selected';
							break;
						}
					}

					obUserRow.ondblclick = function(){ _this.ElementSet() };
				}
					obUserRow.onclick = _this.ElementSelect;

					obUserRow.innerHTML = arElements[i].CONTENT;

				if (_this.multiple)
				{
					obUserRow.insertBefore(obCheckbox, obUserRow.firstChild);
				}

					obSectionDiv.appendChild(obUserRow);
				}

				var obClearer = obSectionDiv.appendChild(document.createElement('DIV'));
				obClearer.style.clear = 'both';
			}
		}
	}
}

