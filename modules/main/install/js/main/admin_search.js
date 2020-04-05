function JCAdminTitleSearch(arParams)
{
	var _this = this;

	this.arParams = {
		'AJAX_PAGE': arParams.AJAX_PAGE,
		'CONTAINER_ID': arParams.CONTAINER_ID,
		'INPUT_ID': arParams.INPUT_ID,
		'MIN_QUERY_LEN': parseInt(arParams.MIN_QUERY_LEN)
	};
	if(arParams.WAIT_IMAGE)
		this.arParams.WAIT_IMAGE = arParams.WAIT_IMAGE;
	if(arParams.MIN_QUERY_LEN <= 0)
		arParams.MIN_QUERY_LEN = 1;

	this.cache = [];
	this.cache_key = null;

	this.startText = '';
	this.currentRow = -1;
	this.RESULT = null;
	this.CONTAINER = null;
	this.INPUT = null;
	this.WAIT = null;

	this.Hide = function()
	{
		_this.RESULT.style.display = 'none';
		_this.RESULT.innerHTML = '';
		_this.currentRow = -1;
		_this.UnSelectAll();
		BX.removeClass(_this.INPUT.parentNode,'adm-header-search-block-active-popup');
	};

	this.ShowResult = function(result)
	{
		this.AdjustResult();

		if(result != null)
		{
			_this.RESULT.innerHTML = result;
			setTimeout(this.AdjustResult, 50);
		}

		if(_this.RESULT.innerHTML.length > 0)
		{
			_this.RESULT.style.display = 'block';
			BX.addClass(_this.INPUT.parentNode,'adm-header-search-block-active-popup');
		}
		else
		{
			this.Hide();
		}

	};

	this.AdjustResult = function(result)
	{
		var pos = BX.pos(_this.CONTAINER);
		pos.width = pos.right - pos.left;
		_this.RESULT.style.position = 'absolute';
		_this.RESULT.style.top = '4px';
		_this.RESULT.style.left = (pos.left - 7) + 'px';
		_this.RESULT.style.width = (pos.width + 14)+ 'px';
	};

	this.onKeyPress = function(keyCode)
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'adm-search-result'}, true);
		if(!tbl)
			return false;

		var cnt = tbl.rows.length;

		switch (keyCode)
		{
		case 27: // escape key - close search div
			_this.Hide();
		return true;

		case 40: // down key - navigate down on search results
			if(_this.RESULT.style.display == 'none')
				_this.RESULT.style.display = 'block';

			var first = -1;
			for(var i = 0; i < cnt; i++)
			{
				if(first == -1)
					first = i;

				if(_this.currentRow < i)
				{
					_this.currentRow = i;
					break;
				}
				else if(tbl.rows[i].className == 'adm-search-selected')
				{
					tbl.rows[i].className = '';
				}
			}

			if(i == cnt && _this.currentRow != i)
				_this.currentRow = first;

			tbl.rows[_this.currentRow].className = 'adm-search-selected';
		return true;

		case 38: // up key - navigate up on search results
			if(_this.RESULT.style.display == 'none')
				_this.RESULT.style.display = 'block';

			var last = -1;
			for(var i = cnt-1; i >= 0; i--)
			{
				if(last == -1)
					last = i;

				if(_this.currentRow > i)
				{
					_this.currentRow = i;
					break;
				}
				else if(tbl.rows[i].className == 'adm-search-selected')
				{
					tbl.rows[i].className = '';
				}
			}

			if(i < 0)
				_this.currentRow = last;

			tbl.rows[_this.currentRow].className = 'adm-search-selected';
		return true;

		case 13: // enter key - choose current search result
			if(_this.RESULT.style.display == 'block')
			{
				for(var i = 0; i < cnt; i++)
				{
					if(_this.currentRow == i)
					{
						if(!BX.findChild(tbl.rows[i], {'class':'adm-search-separator'}, true))
						{
							var a = BX.findChild(tbl.rows[i], {'tag':'a'}, true);
							if(a)
							{
								window.location = a.href;
								return true;
							}
						}
					}
				}
			}
		return false;
		}

		return false;
	};

	this.onTimeout = function()
	{
		if(_this.INPUT.value != _this.oldValue && _this.INPUT.value != _this.startText)
		{
			if(_this.INPUT.value.length >= _this.arParams.MIN_QUERY_LEN)
			{
				_this.oldValue = _this.INPUT.value;
				_this.cache_key = _this.arParams.INPUT_ID + '|' + _this.INPUT.value;
				if(_this.cache[_this.cache_key] == null)
				{
					if(_this.WAIT)
					{
						var pos = BX.pos(_this.INPUT);
						var height = (pos.bottom - pos.top)-2;
						_this.WAIT.style.top = (pos.top+1) + 'px';
						_this.WAIT.style.height = height + 'px';
						_this.WAIT.style.width = height + 'px';
						_this.WAIT.style.left = (pos.right - height + 2) + 'px';
						_this.WAIT.style.display = 'block';
					}

					BX.ajax.post(
						_this.arParams.AJAX_PAGE,
						{
							'ajax_call':'y',
							'INPUT_ID':_this.arParams.INPUT_ID,
							'q':_this.INPUT.value
						},
						function(result)
						{
							_this.cache[_this.cache_key] = result;
							_this.ShowResult(result);
							_this.currentRow = -1;
							_this.EnableMouseEvents();
							if(_this.WAIT)
								_this.WAIT.style.display = 'none';
							setTimeout(_this.onTimeout, 500);
						}
					);
				}
				else
				{
					_this.ShowResult(_this.cache[_this.cache_key]);
					_this.currentRow = -1;
					_this.EnableMouseEvents();
					setTimeout(_this.onTimeout, 500);
				}
			}
			else
			{
				_this.Hide();
				setTimeout(_this.onTimeout, 500);
			}
		}
		else
		{
			setTimeout(_this.onTimeout, 500);
		}
	}

	this.UnSelectAll = function()
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'adm-search-result'}, true);
		if(tbl)
		{
			var cnt = tbl.rows.length;
			for(var i = 0; i < cnt; i++)
				tbl.rows[i].className = '';
		}
	};

	this.EnableMouseEvents = function()
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'adm-search-result'}, true);
		if(tbl)
		{
			var cnt = tbl.rows.length;
			for(var i = 0; i < cnt; i++)
			{
				tbl.rows[i].id = 'row_' + i;
				tbl.rows[i].onmouseover = function (e) {
					if(_this.currentRow != this.id.substr(4))
					{
						_this.UnSelectAll();
						this.className = 'adm-search-selected';
						_this.currentRow = this.id.substr(4);
					}
				};
				tbl.rows[i].onmouseout = function (e) {
					this.className = '';
					_this.currentRow = -1;
				};
			}
		}
	};

	this.onFocusLost = function(hide)
	{
		setTimeout(function(){_this.Hide();}, 250);
	};

	this.onFocusGain = function()
	{
		if(_this.RESULT.innerHTML.length)
			_this.ShowResult();
	};

	this.onKeyDown = function(e)
	{
		if(!e)
			e = window.event;

		if (_this.RESULT.style.display == 'block')
		{
			if(_this.onKeyPress(e.keyCode))
				return BX.PreventDefault(e);
		}
	};

	this.Init = function()
	{
		this.CONTAINER = document.getElementById(this.arParams.CONTAINER_ID);
		if (document.getElementById("bx-panel"))
		{
			this.RESULT = document.getElementById("bx-panel").appendChild(document.createElement("DIV"));
			this.RESULT.className = 'adm-search-result-wrap';
			this.RESULT.style.display = 'none';
		}

		this.INPUT = document.getElementById(this.arParams.INPUT_ID);
		this.startText = this.oldValue = this.INPUT.value;
		BX.bind(this.INPUT, 'focus', function() {_this.onFocusGain()});
		BX.bind(this.INPUT, 'blur', function() {_this.onFocusLost()});
		BX.bind(window, 'resize', function() {_this.onFocusGain()});
		this.INPUT.onkeydown = this.onKeyDown;

		if(this.arParams.WAIT_IMAGE)
		{
			this.WAIT = document.body.appendChild(document.createElement("DIV"));
			this.WAIT.style.backgroundImage = "url('" + this.arParams.WAIT_IMAGE + "')";
			if(!BX.browser.IsIE())
				this.WAIT.style.backgroundRepeat = 'none';
			this.WAIT.style.display = 'none';
			this.WAIT.style.position = 'absolute';
			this.WAIT.style.zIndex = '1100';
		}

		setTimeout(this.onTimeout, 500);
	};

	BX.ready(function (){_this.Init(arParams)});
}

