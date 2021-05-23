/************ Main control object class ****************/
function JCMainLookupSelector(arParams)
{
	var _this = this;

	this.timerId = null;
	this._currentSearchStr = '';

	this.LAYOUT = null;
	this.VALUE_CONTAINER = null;

	this.VISUAL = null;
	this.SEARCH = null;

	this.__search_current_row = null;

	arParams.VISUAL.CONTROL_ID = arParams.CONTROL_ID;

	this.arParams = {
		'AJAX_PAGE': arParams.AJAX_PAGE,
		'CONTROL_ID': arParams.CONTROL_ID,
		'LAYOUT_ID': arParams.LAYOUT_ID,
		'INPUT_NAME': arParams.INPUT_NAME,
		'VISUAL': arParams.VISUAL
	};

	this.arParams.PROACTIVE = 'NONE';
	if (!!arParams.PROACTIVE)
		this.arParams.PROACTIVE = arParams.PROACTIVE;
	this.arParams.PROACTIVE = this.arParams.PROACTIVE.toUpperCase();

	if (!!arParams.AJAX_PARAMS)
	{
		this.arParams.AJAX_PARAMS = arParams.AJAX_PARAMS;
	}

	if (!!arParams.VALUE)
	{
		//this.arParams.VISUAL.START_TEXT = '';
		this.arParams.VALUE = arParams.VALUE;
	}

	if (!!arParams.INPUT_NAME_SUSPICIOUS)
	{
		this.INPUT_SUSPICIOUS = null;
		this.arParams.INPUT_NAME_SUSPICIOUS = arParams.INPUT_NAME_SUSPICIOUS;
	}

	this.processSearchStr = function()
	{
		var url = _this.arParams.AJAX_PAGE+'?MODE=SEARCH';

		url += '&search=' + encodeURIComponent(_this._currentSearchStr);
		if (_this.arParams.AJAX_PARAMS)
		{
			for(var param_name in _this.arParams.AJAX_PARAMS)
				url += '&' + param_name + '=' + encodeURIComponent(_this.arParams.AJAX_PARAMS[param_name]);
		}
		BX.ajax.get(url, _this.ShowSearchResults);
	};

	this.ShowSearchResults = function(data)
	{
		if (null != _this.__search_current_row)
			_this.__search_current_row = null;

		if (null != _this.SEARCH)
			_this.SEARCH.innerHTML = '';

		var DATA = [];

		if (BX.type.isNotEmptyString(data))
		{
			var data_test = BX.parseJSON(data);
			if (data_test)
			{
				eval('DATA = ' + data);
			}
			else
			{
				if ("''" != data)
				{
					if ('MESSAGE' == _this.arParams.PROACTIVE || 'BXWINDOW' == _this.arParams.PROACTIVE)
					{
						if ('MESSAGE' == _this.arParams.PROACTIVE)
						{
							alert(data);	// this alert show proactive message
						}
						else
						{
							var obDialog = new BX.CDialog({
							'content': data,
							'draggable': true,
							'resizable': false,
							'buttons': [BX.CDialog.btnClose]
						});
							obDialog.Show();
						}

						_this.VISUAL.TEXT.value = _this.VISUAL.TEXT.value.replace(_this._currentSearchStr,'');
						_this._currentSearchStr = '';
					}
				}
			}
		}

		if (DATA.length > 0)
		{
			if (DATA.length == 1 && null != DATA[0].READY)
			{
				_this.VISUAL.SetTokenData(_this._currentSearchStr, DATA[0], false);
				return;
			}

			if (null == _this.SEARCH)
			{
				if (!!_this.arParams.VISUAL.SEARCH_POSITION && 'absolute' == _this.arParams.VISUAL.SEARCH_POSITION)
				{
					_this.SEARCH = BX.GetDocElement().appendChild(document.createElement('DIV'));
					_this.SEARCH.className = 'mli-search-results';
					_this.SEARCH.style.position = 'absolute';
					if (!!_this.arParams.VISUAL.SEARCH_ZINDEX)
					{
						_this.arParams.VISUAL.SEARCH_ZINDEX = parseInt(_this.arParams.VISUAL.SEARCH_ZINDEX);
						if (!isNaN(_this.arParams.VISUAL.SEARCH_ZINDEX) && 0 < _this.arParams.VISUAL.SEARCH_ZINDEX)
						{
							_this.SEARCH.style.zIndex = _this.arParams.VISUAL.SEARCH_ZINDEX;
						}
					}
				}
				else
				{
					_this.SEARCH = _this.LAYOUT.appendChild(document.createElement('DIV'));
					_this.SEARCH.className = 'mli-search-results';
					_this.SEARCH.style.position = 'absolute';
				}
			}

			var pos;
			if (!!_this.arParams.VISUAL.SEARCH_POSITION && 'absolute' == _this.arParams.VISUAL.SEARCH_POSITION)
			{
				pos = BX.pos(_this.VISUAL.TEXT, false);
			}
			else
			{
				pos = BX.pos(_this.VISUAL.TEXT, true);
			}
			_this.SEARCH.style.top = pos.bottom + 'px';
			_this.SEARCH.style.left = pos.left + 'px';
			_this.SEARCH.style.width = (pos.right - pos.left - 2) + 'px';

			for (var i = 0; i < DATA.length; i++)
			{
				var obSearchResult = _this.SEARCH.appendChild(document.createElement('DIV'));
				obSearchResult.className = 'mli-search-result';
				obSearchResult.appendChild(document.createTextNode(DATA[i].NAME + ' [' + DATA[i].ID + ']'));

				obSearchResult.BX_ROW_DATA = DATA[i];
				obSearchResult.onclick = _this.__search_result_click;
				obSearchResult.onmouseover = _this.__search_result_over;
				BX.bind(obSearchResult, "mousedown", function(event) {
					event.stopPropagation();
				});
			}

			_this.SEARCH.style.display = 'block';
		}
		else
		{
			_this.__hideSearch();
		}
	};

	this.__search_result_click = function()
	{
		_this.VISUAL.SetTokenData(_this._currentSearchStr, this.BX_ROW_DATA);
		_this.__hideSearch();
	};

	this.__search_result_over = function()
	{
		if (null != _this.__search_current_row)
			_this.__search_current_row.className = 'mli-search-result';

		_this.__search_current_row = this;
		this.className = 'mli-search-result mli-search-current';
	};

	jsUtils.addCustomEvent('onEmpUserSelectorChangeTokenActivity', this.SetTokenInput, null, this);
	BX.addCustomEvent('onGridClearFilter', BX.delegate(this.ClearValues, this));
	BX.ready(function() {_this.Init(); });
}

JCMainLookupSelector.prototype.Init = function()
{
	if (!!this.bInit) return;
	this.bInit = true;

	var _this = this;

	this.LAYOUT = document.getElementById(this.arParams.LAYOUT_ID);
	this.VISUAL = new JCMainLookupSelectorText(this.arParams.VISUAL);
	this.VISUAL.parent = this;

	this.VISUAL.onCurrentStringExists = function(str)
	{
		if (_this._currentSearchStr == str)
		{
			if (null != _this.SEARCH && _this.SEARCH.innerHTML.length > 0)
				_this.SEARCH.style.display = 'block';
		}
		else
		{
			if (null != _this.timerId)
				clearTimeout(_this.timerId);

			_this._currentSearchStr = str;
			// timeout moved to keys processing inside the textarea
			//_this.timerId = setTimeout(_this.processSearchStr, 100);
			_this.processSearchStr();
		}
	};

	this.VISUAL.onCurrentStringChange = function()
	{
		if (null != _this.timerId)
			clearTimeout(_this.timerId);

		_this.__hideSearch();
	};

	this.VISUAL.onUnidentifiedTokenFound = function(str)
	{
		var url = _this.arParams.AJAX_PAGE+'?MODE=SEARCH';

		url += '&search=' + encodeURIComponent(str);
		if (_this.arParams.AJAX_PARAMS)
		{
			for(var param_name in _this.arParams.AJAX_PARAMS)
				url += '&' + param_name + '=' + encodeURIComponent(_this.arParams.AJAX_PARAMS[param_name]);
		}
		BX.ajax.get(url, function(data) {
			if (data.length <= 0)
				return;

			var DATA = [];
			eval('DATA = ' + data);
			if (DATA.length == 1 && DATA[0].READY == 'Y')
				_this.VISUAL.SetTokenData(str, DATA[0], false);
		});
	};

	this.VISUAL.onControlKeyPressed = function(keyCode)
	{
		if (null != _this.SEARCH && _this.SEARCH.style.display == 'block')
		{
			switch (keyCode)
			{
				case 27: // escape key - close search div
					_this.SEARCH.style.display = 'none';
					return false;
				break;

				case 40: // down key - navigate down on search results
					if (null == _this.__search_current_row)
						_this.SEARCH.firstChild.onmouseover();
					else if (null != _this.__search_current_row.nextSibling)
						_this.__search_current_row.nextSibling.onmouseover();
					return false;
				break;

				case 38: // up key - navigate up on search results
					if (null == _this.__search_current_row)
						_this.SEARCH.lastChild.onmouseover();
					else if (null != _this.__search_current_row.previousSibling)
						_this.__search_current_row.previousSibling.onmouseover();
					return false;
				break;

				case 13: // enter key - choose current search result
					if (null != _this.__search_current_row)
						_this.__search_current_row.onclick();
					return false;
				break;
			}
		}

		return true;
	};

	if (null != this.arParams.INPUT_NAME_SUSPICIOUS)
	{
		this.VISUAL.onSuspiciousTokensFound = function(arWords)
		{
			if (null == _this.INPUT_SUSPICIOUS)
			{
				_this.INPUT_SUSPICIOUS = document.createElement('INPUT');
				_this.INPUT_SUSPICIOUS.type = 'hidden';
				_this.INPUT_SUSPICIOUS.name = _this.arParams.INPUT_NAME_SUSPICIOUS;

				_this.VALUE_CONTAINER.appendChild(_this.INPUT_SUSPICIOUS);
			}

			_this.INPUT_SUSPICIOUS.value = arWords.join(';');
		};
	}

	this.__hideSearch = function() {if (null != _this.SEARCH) _this.SEARCH.style.display = 'none';};
	this.__delayedHideSearch = function() {if (null != _this.SEARCH) setTimeout(_this.__hideSearch, 500);};

	jsUtils.addEvent(this.VISUAL.TEXT, 'blur', this.__delayedHideSearch);

	if (BX('value_container_' + this.arParams['CONTROL_ID']))
		this.VALUE_CONTAINER = BX('value_container_' + this.arParams['CONTROL_ID']);
	else
		this.VALUE_CONTAINER = this.LAYOUT.appendChild(document.createElement('DIV'));
		
	this.VALUE_CONTAINER.style.display = 'none';

	if (null != this.arParams.VALUE)
		this.SetValue(this.arParams.VALUE);
};

// object destructor
JCMainLookupSelector.prototype.Clear = function()
{
	// remove global event handler
	jsUtils.removeCustomEvent('onEmpUserSelectorChangeTokenActivity', this.SetTokenInput);

	// clear textarea object internal event handlers
	this.VISUAL.onCurrentStringChange = null;
	this.VISUAL.onCurrentStringExists = null;
	this.VISUAL.onCurrentTokenExists = null;
	this.VISUAL.onUnidentifiedTokenFound = null;
	this.VISUAL.onControlKeyPressed = null;
	this.VISUAL.onSuspiciousTokensFound = null;

	jsUtils.removeEvent(this.VISUAL.TEXT, 'blur', this.__delayedHideSearch);

	// reset and kill textarea processing object
	this.VISUAL.Reset(false, true);
	this.VISUAL = null;

	// collect garbage
	if (null != this.timerId)
		clearTimeout(this.timerId);

	if (null != this.SEARCH)
	{
		this.SEARCH.parentNode.removeChild(this.SEARCH);
		this.SEARCH = null;
	}

	this._currentSearchStr = '';
	BX.cleanNode(this.LAYOUT, true);
};

JCMainLookupSelector.prototype.SetTokenInput = function(arParams, arEventParams)
{
	if (arEventParams.CONTROL_ID != this.arParams.CONTROL_ID)
		return;

	if (null == this.VALUE_CONTAINER) return;
	if (null == arEventParams.TOKEN.DATA || null == arEventParams.TOKEN.DATA.ID) return;

	if (null == arEventParams.TOKEN.INPUT)
	{
		arEventParams.TOKEN.INPUT = document.createElement('INPUT');
		arEventParams.TOKEN.INPUT.type = 'hidden';
		arEventParams.TOKEN.INPUT.name = this.arParams.INPUT_NAME + '[]';
		arEventParams.TOKEN.INPUT.value = arEventParams.TOKEN.DATA.ID;
	}

	if (arEventParams.TOKEN.ACTIVE && null == arEventParams.TOKEN.INPUT.parentNode)
	{
		this.AddInput(arEventParams.TOKEN.INPUT);
		jsUtils.onCustomEvent('onLookupInputChange', {'CONTROL_ID': this.arParams.CONTROL_ID, 'ACTION': 'add', 'DATA': arEventParams.TOKEN.DATA});
	}
	else if (!arEventParams.TOKEN.ACTIVE && null != arEventParams.TOKEN.INPUT.parentNode)
	{
		this.DeleteInput(arEventParams.TOKEN.INPUT);
		jsUtils.onCustomEvent('onLookupInputChange', {'CONTROL_ID': this.arParams.CONTROL_ID, 'ACTION': 'remove', 'DATA': arEventParams.TOKEN.DATA});
	}
};


JCMainLookupSelector.prototype.AddValue = function(arValue)
{
	if (typeof arValue != 'object' || null == arValue.length || null == arValue[0])
		arValue = [arValue];

	var _this = this;
	for (var i = 0; i < arValue.length; i++)
	{
		if (typeof arValue[i] == 'object')
		{
			if (null != arValue[i].ID && null != arValue[i].NAME)
			{
				this.VISUAL.AddTokenData(arValue[i], false);
			}
		}
		else
		{
			var val = parseInt(arValue[i]);
			if (!isNaN(val))
			{
				var str = 'q <q@q> [' + val + ']'; // hack
				var url = this.arParams.AJAX_PAGE+'?MODE=SEARCH';

				url += '&search=' + encodeURIComponent(str);
				if (this.arParams.AJAX_PARAMS)
				{
					for(var param_name in this.arParams.AJAX_PARAMS)
						url += '&' + param_name + '=' + encodeURIComponent(this.arParams.AJAX_PARAMS[param_name]);
				}
				BX.ajax.get(url, function(data) {
					if (data.length <= 0)
						return;

					var DATA = [];
					eval('DATA = ' + data);
					if (DATA.length == 1 && DATA[0].READY == 'Y')
					{
						_this.VISUAL.AddTokenData(DATA[0], false);
					}
				});
			}
		}
	}
};

JCMainLookupSelector.prototype.SetValue = function(arValue)
{
	this.VISUAL.Reset(true, false);
	this.ClearValues();
	this.AddValue(arValue);
};

JCMainLookupSelector.prototype.ClearValues = function()
{
	if (this.VALUE_CONTAINER && this.VALUE_CONTAINER.childNodes)
	{
		var children = this.VALUE_CONTAINER.childNodes;
		for (var i = 0; i < children.length; i++)
		{
			var child = children[i];
			child.value = '';
		}
		this.CleanUpValues();
	}
};

JCMainLookupSelector.prototype.AddInput = function(input)
{
	if (this.VALUE_CONTAINER)
	{
		this.VALUE_CONTAINER.appendChild(input);
		this.CleanUpValues();
	}
};

JCMainLookupSelector.prototype.DeleteInput = function(input)
{
	if (this.VALUE_CONTAINER)
	{
		input.value = '';
		this.CleanUpValues();
	}
};

JCMainLookupSelector.prototype.CleanUpValues = function(input)
{
	if (this.VALUE_CONTAINER && this.VALUE_CONTAINER.childNodes)
	{
		var i;
		var found = false;
		var children = this.VALUE_CONTAINER.childNodes;
		for (i = 0; i < children.length; i++)
		{
			var child = children[i];
			if (child.value.length > 0)
				found++;
		}

		i = 0;
		while (i < children.length)
		{
			var child = children[i];
			if (child.value.length == 0)
			{
				if (found > 0)
				{
					this.VALUE_CONTAINER.removeChild(child);
				}
				else
				{
					i++;
					found++;
				}
			}
			else
			{
				i++;
			}
		}
	}
};

/**************** Visual textarea **********************/
function JCMainLookupSelectorText(arParams)
{
	var _this = this;

	this.__split_reg = /([,;\n])/;
	this.__check_reg = /^(.*?) \[\d+\]/m;
	this.__check_suspicious = [/^[a-z0-9.\-_]+@[a-z0-9.\-]+$/i, /^(.*?)<[a-z0-9.\-_]+@[a-z0-9.\-]+>$/i, /^(.*?)\[[a-z0-9.\-_]+@[a-z0-9.\-]+\]$/i];

	this.arParams = arParams;

	this.__token_index = 0;

	this.previousCurrentHash = '';
	this.previousCurrentIndex = -1;

	this.timerId = null;

	this.isMainUiFilter = (this.arParams['MAIN_UI_FILTER'] === 'Y');
	this.isMultiple = (this.arParams['MULTIPLE'] === 'Y');

	this.TEXT = document.getElementById(this.arParams.ID);
	this.TEXT.bx_last_position = 0;
	this.TEXT.bx_focused = false;

	this.TEXT.style.width = '95%';
	if (this.arParams.MAX_WIDTH) this.TEXT.style.width = this.arParams.MAX_WIDTH + 'px';
	if(this.TEXT.type.toLowerCase() == "textarea")
		this.TEXT.style.height = this.arParams.MIN_HEIGHT + 'px';

	this.__text_focus = function()
	{
		_this.TEXT.bx_focused = true;
		if (_this.TEXT.value == _this.arParams.START_TEXT)
		{
			_this.TEXT.value = '';
		}
	};
	this.__text_blur = function()
	{
		_this.TEXT.bx_focused = false;
		if (_this.TEXT.value == '')
		{
			_this.TEXT.value = _this.arParams.START_TEXT;
		}
	};
	this.__text_additional_check = function()
	{
		_this.TEXT.bx_focused = false;
		_this.__process();
	};

	if (this.TEXT.value == '')
	{
		this.TEXT.value = this.arParams.START_TEXT;
	}

	jsUtils.addEvent(this.TEXT, 'focus', this.__text_focus);
	jsUtils.addEvent(this.TEXT, 'blur', this.__text_blur);
	jsUtils.addEvent(this.TEXT, 'blur', this.__text_additional_check);

	if (null != this.TEXT.form)
	{
		jsUtils.addEvent(this.TEXT.form, 'submit', this.__text_focus);
	}

	this.arTokens = [];
	this.arTokensMap = {};

	/*** internal event handlers ****/
	this.onCurrentStringExists = null; // caret in textarea is on the unidentified string
	this.onCurrentStringChange = null; // caret in textarea changes to unidentified string
	this.onCurrentTokenExists = null; // caret in textarea is on the identified token
	this.onUnidentifiedTokenFound = null; // found a string in required format, which hasn't a token object
	this.onControlKeyPressed = null; // arrow or escape key was pressed while textarea editing; return false to prevent default key action
	this.onSuspiciousTokensFound = null; // found some suspicious tokens found. array of them is passing as parameter (empty one if none found)

	/*** tokens parsing ****/
	this.__ignore_key = false;

	this.__pre_process = function() {_this.__process(); };

	this.TEXT.onkeydown = function(e)
	{
		if (null == e) e = window.event;

		if (null != _this.onControlKeyPressed && ((e.keyCode >= 37 && e.keyCode <= 40) || e.keyCode == 27 || e.keyCode == 13))
		{
			if (!_this.onControlKeyPressed(e.keyCode))
			{
				_this.__ignore_key = true;
				return jsUtils.PreventDefault(e);
			}
		}

	};

	this.TEXT.onclick = this.TEXT.onkeyup = function(e) {
		if (null == e) e = window.event;

		if (e.type == 'keyup' && e.keyCode >= 16 && e.keyCode <= 18) // shift, alt & ctrl keys for avoing of event doubling while copy-pasting
			return;

		if (e.type == 'keyup' && _this.__ignore_key)
		{
			_this.__ignore_key = false;
			return false;
		}

		if (null != _this.timerId)
			clearTimeout(_this.timerId);

		_this.timerId = setTimeout(_this.__pre_process, 500);
	};

	if (this.TEXT.value.length > 0)
	{
		this.timerId = setTimeout(this.__pre_process, 200);
	}

	_this.AdjustHeight();
}


//This function splits string with respect to __check_reg pattern
JCMainLookupSelectorText.prototype.__split = function (str, separator, limit) {

	if (Object.prototype.toString.call(separator) !== "[object RegExp]") {
		return str.split(separator, limit);
	}

	var _compliantExecNpcg = /()??/.exec("")[1] === undefined;

	var output = [];
	var lastLastIndex = 0;
	var flags = (separator.ignoreCase ? "i" : "") + (separator.multiline  ? "m" : "") + (separator.sticky     ? "y" : "");
	separator = RegExp(separator.source, flags + "g");
	var separator2, match, lastIndex, lastLength;

	str = str + ""; // type conversion
	if (!_compliantExecNpcg)
		separator2 = RegExp("^" + separator.source + "$(?!\\s)", flags); // doesn't need /g or /y, but they don't hurt

	/* behavior for `limit`: if it's...
	- `undefined`: no limit.
	- `NaN` or zero: return an empty array.
	- a positive number: use `Math.floor(limit)`.
	- a negative number: no limit.
	- other: type-convert, then use the above rules. */
	if (limit === undefined || +limit < 0)
	{
		limit = Infinity;
	}
	else
	{
		limit = Math.floor(+limit);
		if (!limit)
			return [];
	}

	while (match = separator.exec(str))
	{
		lastIndex = match.index + match[0].length; // `separator.lastIndex` is not reliable cross-browser

		if (lastIndex > lastLastIndex)
		{
			output.push(str.slice(lastLastIndex, match.index));

			// fix browsers whose `exec` methods don't consistently return `undefined` for nonparticipating capturing groups
			if (!_compliantExecNpcg && match.length > 1)
			{
				match[0].replace(separator2, function () {
					for (var i = 1; i < arguments.length - 2; i++)
						if (arguments[i] === undefined)
							match[i] = undefined;
				});
			}

			if (match.length > 1 && match.index < str.length)
				Array.prototype.push.apply(output, match.slice(1));

			lastLength = match[0].length;
			lastLastIndex = lastIndex;

			if (output.length >= limit)
				break;

		}

		if (separator.lastIndex === match.index)
			separator.lastIndex++; // avoid an infinite loop
	}

	if (lastLastIndex === str.length)
	{
		if (lastLength || !separator.test(""))
		{
			output.push("");
		}
	}
	else
	{
		output.push(str.slice(lastLastIndex));
	}

	return output.length > limit ? output.slice(0, limit) : output;
};

//This function splits string with respect to __check_reg pattern
JCMainLookupSelectorText.prototype.__parse = function(str, split_reg, check_reg, arTokens, newStr)
{
	var arResult = [];

	var arToks = [];
	var tok = '';

	if(arTokens && arTokens.length > 0)
	{
		for(var j = 0; j < arTokens.length; j++)
		{
			if(arTokens[j])
			{
				tok = jsUtils.trim(arTokens[j].TOKEN);
				if(tok.length)
				{
					arToks[arToks.length] = tok;
					var start = -1;
					while( (start = str.indexOf(tok, start+1)) > -1 )
					{
						arResult[arResult.length] = {
							'start' : start,
							'end' : start + tok.length,
							'tok' : tok,
							'trimmed': jsUtils.trim(tok),
							'delim' : ''
						};
					}
				}
			}
		}
	}
	if(newStr && newStr.length > 0)
	{
		tok = jsUtils.trim(newStr);
		if(tok.length)
		{
			arToks[arToks.length] = tok;
			start = -1;
			while( (start = str.indexOf(tok, start+1)) > -1 )
			{
				var found = false;
				for(var i =0; i < arResult.length; i++)
				{
					if(start >= arResult[i].start && start < arResult[i].end)
					{
						start = arResult[i].end;
						found = true;
						break;
					}
				}

				if(!found)
				{
					arResult[arResult.length] = {
						'start' : start,
						'end' : start + tok.length,
						'tok' : tok,
						'trimmed': jsUtils.trim(tok),
						'delim' : ''
					};
					break;
				}
			}
		}
	}

	var arTmp = this.__split(str, split_reg);

	tok = '';
	var delim = '';
	var cur_pos = 0;
	for(i = 0; i < arTmp.length; i++)
	{
		tok = arTmp[i];

		i++;
		if(i < arTmp.length)
			delim = arTmp[i];
		else
			delim = '';

		var skip = false;
		if(tok.length)
		{
			for(var ii = 0; ii < arResult.length && !skip; ii++)
			{
				if ( cur_pos >= arResult[ii].start && cur_pos < arResult[ii].end )
				{
					arResult[ii].delim = delim;
					skip = true;
				}
			}
			//if ( cur_pos >= strNew_start && cur_pos < strNew_end )
			//	skip = true;
		}

		if(tok.length && !skip)
		{
			//Additional check if this is string followed known token
			if(check_reg.test(tok) && arToks.length > 0)
			{
				for(j = 0; j < arToks.length; j++)
				{
					if(
						tok.length > arToks[j].length
						&& arToks[j] == tok.substr(tok.length - arToks[j].length)
					)
					{

						var pre_tok = tok.substr(0, tok.length - arToks[j].length);
						tok = tok.substr(pre_tok.length);

						while(pre_tok && pre_tok.length > 0 && pre_tok.substr(0, 1) == ' ')
						{
							cur_pos++;
							pre_tok = pre_tok.substr(1);
						}

						if(pre_tok && pre_tok.length > 0)
						{
							arResult[arResult.length] = {
								'start' : cur_pos,
								'end' : cur_pos + pre_tok.length,
								'tok' : pre_tok,
								'trimmed': jsUtils.trim(pre_tok),
								'delim' : ''
							};
							cur_pos += pre_tok.length;
						}
					}
				}
			}

			while(tok.length && tok.substr(0, 1) == ' ')
			{
				cur_pos++;
				tok = tok.substr(1);
			}
			arResult[arResult.length] = {
				'start' : cur_pos,
				'end' : cur_pos + tok.length,
				'tok' : tok,
				'trimmed': jsUtils.trim(tok),
				'delim' : delim
			};
		}

		cur_pos += tok.length + delim.length;
	}

//	if(tok.length)
//		arResult[arResult.length] = {
//			'start' : cur_pos,
//			'end' : cur_pos + tok.length,
//			'tok' : tok,
//			'delim' : ''
//		};

	return arResult;
};

JCMainLookupSelectorText.prototype.__process = function()
{
	this.__current_token = null;

	if (
		(
			this.arParams.START_TEXT.length > 0
			&& this.TEXT.value == this.arParams.START_TEXT
		) || (
			this.TEXT.value == ''
		)
	)
	{
		this.parent.ClearValues();
		return;
	}

	var arToks = this.__parse(this.TEXT.value, this.__split_reg, this.__check_reg, this.arTokens);

	var cur_pos = this.GetCursorPos();
	var bCurrent;

	var arSuspiciousTokens = [];

	for (var i = 0; i < arToks.length; i++)
	{
		bCurrent = (cur_pos > arToks[i].start && cur_pos <= arToks[i].end);

		var str = jsUtils.trim(arToks[i].tok);
		if (str.length > 0)
		{
			if (null != this.onUnidentifiedTokenFound && this.__check_reg.test(str))
			{
				if (null == this.arTokensMap[this.GetHash(str)])
					this.onUnidentifiedTokenFound(str);
			}
			else
			{
				if (bCurrent)
				{
					var currentHash = this.GetHash(str);
					var currentIndex = i;

					if (
						null != this.arTokensMap[this.previousCurrentHash]
						&& currentHash != this.previousCurrentHash
						&& currentIndex == this.previousCurrentIndex
					)
						this.arTokensMap[this.previousCurrentHash] = null;

					/**** external events block *****/
					if (
						null != this.onCurrentStringChange
						&& null != this.previousCurrentIndex
						&& this.previousCurrentIndex != currentIndex
					)
						this.onCurrentStringChange();


					if (
						null != this.onCurrentStringExists
						&& null == this.arTokensMap[currentHash]
					)
						this.onCurrentStringExists(str);

					if (
						null != this.onCurrentTokenExists
						&& null != this.arTokensMap[currentHash]
					)
						this.onCurrentTokenExists(this.arTokensMap[currentHash]);

					this.previousCurrentHash = currentHash;
					this.previousCurrentIndex = currentIndex;
				}

				if (null != this.onSuspiciousTokensFound)
				{
					for (var j = 0; j < this.__check_suspicious.length; j++)
					{
						if (this.__check_suspicious[j].test(str))
						{
							arSuspiciousTokens[arSuspiciousTokens.length] = str;
							break;
						}
					}
				}
			}
		}
	}

	if (null != this.onSuspiciousTokensFound)
		this.onSuspiciousTokensFound(arSuspiciousTokens);

	this.CheckTokens(arToks);
	this.AdjustHeight();
};

JCMainLookupSelectorText.prototype.Reset = function(bClearText, bClearEvents)
{
	if (null == bClearText) bClearEvents = false;
	if (null == bClearEvents) bClearEvents = false;

	for (var i = 0; i < this.arTokens.length; i++)
	{
		if (null == this.arTokens[i])
			continue;
		this.arTokensMap[this.arTokens[i].TEXT_HASH] = null;
		this.arTokens[i] = null;
	}

	this.arTokens = [];
	this.arTokensMap = {};

	this.__token_index = 0;

	this.previousCurrentHash = '';
	this.previousCurrentIndex = -1;

	if (bClearText)
	{
		this.TEXT.value = this.arParams.START_TEXT;
	}

	if (bClearEvents)
	{
		this.TEXT.onkeydown = this.TEXT.onkeyup = this.TEXT.onclick = null;
		jsUtils.removeEvent(this.TEXT, 'focus', this.__text_focus);
		jsUtils.removeEvent(this.TEXT, 'blur', this.__text_blur);
		jsUtils.removeEvent(this.TEXT, 'blur', this.__text_additional_check);
		if (null != this.TEXT.form)
			jsUtils.removeEvent(this.TEXT.form, 'submit', this.__text_focus);

	}
};

JCMainLookupSelectorText.prototype.CheckTokens = function(arToks)
{
	if (!arToks)
	{
		arToks = this.__parse(this.TEXT.value, this.__split_reg, this.__check_reg, this.arTokens);
	}

	var index = [];
	for (var j = 0; j < arToks.length; j++)
	{
		if (arToks[j].trimmed.length <= 0)
			continue;
		if (index[arToks[j].trimmed] !== undefined)
			continue;
		index[arToks[j].trimmed] = j;
	}
	
	for (var i = 0; i < this.arTokens.length; i++)
	{
		if (null == this.arTokens[i])
			continue;

		var tok = jsUtils.trim(this.arTokens[i].TOKEN);

		this.arTokens[i].SetActive(index[tok] !== undefined);
	}
};

JCMainLookupSelectorText.prototype.AdjustHeight = function()
{
	if (this.TEXT.scrollHeight > this.TEXT.clientHeight)
	{
		var dy = this.TEXT.offsetHeight - this.TEXT.clientHeight;
		var newHeight = this.TEXT.scrollHeight + dy;

		if (newHeight > this.arParams.MAX_HEIGHT)
			newHeight = this.arParams.MAX_HEIGHT;

		if(this.TEXT.type.toLowerCase() == "textarea")
			this.TEXT.style.height = newHeight + 'px';
	}
};

JCMainLookupSelectorText.prototype.AddTokenData = function(data, bSelect)
{
	if (this.TEXT.value == this.arParams.START_TEXT)
		this.TEXT.value = '';

	// IE bug: focus on hidden input
	//try{this.TEXT.focus();}catch(e){};
	var scrollTop = this.TEXT.scrollTop;

	if(this.TEXT.type.toLowerCase() == "textarea")
	{
		var str = jsUtils.trim(data.NAME + ' [' + data.ID + ']');
		if(this.TEXT.value.indexOf(str) < 0)
		{
			if (this.TEXT.value.length > 0 && this.TEXT.value.substr(this.TEXT.value.length-1, 1) != "\n")
				this.TEXT.value += "\n";

			this.TEXT.value += str + "\n";
			this.TEXT.scrollTop = scrollTop;
			this.SetTokenData(str, data, bSelect);
		}
	}
	else
	{
		str = jsUtils.trim(data.NAME + ' [' + data.ID + ']');
		if (this.isMultiple && this.isMainUiFilter)
		{
			if(this.TEXT.value.indexOf(str) < 0)
			{
				this.TEXT.value += str + "\u00A0";
				this.SetTokenData(str, data, bSelect);
			}
		}
		else
		{
			if(this.TEXT.value.indexOf(str) < 0)
			{
				this.TEXT.value = str;
				this.SetTokenData(str, data, bSelect);
			}
		}
	}

};

JCMainLookupSelectorText.prototype.SetTokenData = function(str, data, bSelect)
{
	if (null == bSelect) bSelect = true;

	var arToks = this.__parse(this.TEXT.value, this.__split_reg, this.__check_reg, this.arTokens, str);

	var delim = '';
	for (var i = 0; i < arToks.length; i++)
	{
		if(delim == '')//take first delim
			delim = arToks[i].delim;
		if(delim.length)
			break;
	}
	if(delim == '')//fall to default
		delim = "\n";

	for (i = 0; i < arToks.length; i++)
	{
		var s = jsUtils.trim(arToks[i].tok);

		if (s.length <= 0)
			continue;

		if (str == s)
		{
			var newText = jsUtils.trim(data.NAME + ' [' + data.ID + ']');

			//delimiter is needed only at the end of input
			//at when replaced text has no delimiter just before next token
			var actual_delim = '';
			if(i == arToks.length || arToks[i].delim == '')
				actual_delim = delim;

			// IE bug: focus on hidden input
			//try{this.TEXT.focus();}catch(e){};

			var scrollTop = this.TEXT.scrollTop;

			var prefix = this.TEXT.value.substr(0, arToks[i].start);
			var posfix = this.TEXT.value.substr(arToks[i].end);

			this.TEXT.value = prefix + newText + actual_delim + posfix;

			this.TEXT.scrollTop = scrollTop;

			var obToken = new JCMainLookupSelectorToken({
				'CONTROL_ID': this.arParams.CONTROL_ID,
				'TOKEN': newText,
				'INDEX': this.__token_index++,
				'START': arToks[i].start,
				'FINISH': arToks[i].start + newText.length + actual_delim.length,
				'ACTIVE': true,
				'DATA': data
			});

			if (null != this.arTokensMap[obToken.TEXT_HASH])
				this.arTokens[this.arTokensMap[obToken.TEXT_HASH].INDEX] = null;

			var change = newText.length + actual_delim.length - arToks[i].tok.length;
			this.AdjustTokensPos(arToks[i].end, change);

			this.arTokensMap[obToken.TEXT_HASH] = this.arTokens[obToken.INDEX] = obToken;

			if (bSelect)
				this.SetCursorPos(obToken.FINISH);

			if (null != this.onCurrentTokenExists)
				this.onCurrentStringExists(obToken);

			this.AdjustHeight();
			this.__pre_process();
			return;
		}
	}

	this.AddTokenData(data, bSelect);
};

JCMainLookupSelectorText.prototype.AdjustTokensPos = function(start_pos, change)
{
	for (var i = 0; i < this.arTokens.length; i++)
	{
		if (null != this.arTokens[i] && this.arTokens[i].ACTIVE && this.arTokens[i].START >= start_pos)
		{
			this.arTokens[i].START += change;
			this.arTokens[i].FINISH += change;
		}
	}
};

JCMainLookupSelectorText.prototype.SetCursorPos = function(pos)
{
	if (null != this.TEXT.selectionStart)// Gecko
		this.TEXT.setSelectionRange(pos, pos);
	else if (document.selection)//IE
	{
		try {
			var r = this.TEXT.createTextRange();
			r.collapse(true);
			r.moveEnd('character', pos);
			r.moveStart('character', pos);
			r.select();
		} catch (e) {}
	}
};

JCMainLookupSelectorText.prototype.GetCursorPos = function()
{
	try {
		if (null != this.TEXT.selectionStart)// Gecko
			return this.TEXT.selectionStart;
		else if (document.selection && this.TEXT.bx_focused)//IE
		{
			if(this.TEXT.type.toLowerCase() == "textarea")
			{
				var sel = document.selection.createRange();
				var clone = sel.duplicate();
				clone.moveToElementText(this.TEXT);
				clone.setEndPoint('EndToEnd', sel);
				this.TEXT.bx_last_position = clone.text.length;
			}
			else
				this.TEXT.bx_last_position = 1;
		}

		return this.TEXT.bx_last_position;
	} catch (e) {return 0; }
};

JCMainLookupSelectorText.prototype.GetHash = function(text)
{
	var hash = '';
	for (var i = 0; i < text.length; i++)
	{
		hash += text.charCodeAt(i).toString(16);
	}
	return hash;
};

/**************** Visual textarea token ******************/
function JCMainLookupSelectorToken(arParams)
{
	var _this = this;

	this.CONTROL_ID = arParams.CONTROL_ID;

	this.TOKEN = arParams.TOKEN;
	this.INDEX = arParams.INDEX;
	this.START = arParams.START;
	this.FINISH = arParams.FINISH;

	this.DATA = arParams.DATA;

	this.SetActive(null == arParams.ACTIVE ? false : arParams.ACTIVE);

	this.HASH = this.GetHash(this.INDEX + '$$' + jsUtils.trim(this.TOKEN));
	this.TEXT_HASH = this.GetHash(jsUtils.trim(this.TOKEN));
}

JCMainLookupSelectorToken.prototype.SetActive = function(flag)
{
	this.ACTIVE = !!flag;
	jsUtils.onCustomEvent('onEmpUserSelectorChangeTokenActivity', {'CONTROL_ID': this.CONTROL_ID, 'TOKEN': this});
};

JCMainLookupSelectorToken.prototype.SetPos = function(start, finish)
{
	if (null == finish) finish = start+this.TOKEN.length;

	this.START = start; this.FINISH = finish;
};

JCMainLookupSelectorToken.prototype.GetHash = JCMainLookupSelectorText.prototype.GetHash;

