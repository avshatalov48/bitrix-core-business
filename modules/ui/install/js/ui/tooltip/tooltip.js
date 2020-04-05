(function() {

var BX = window.BX;

BX.namespace('BX.UI');

if (!!BX.UI.Tooltip)
{
	return;
}

BX.UI.Tooltip = {
	disabled: false,
	tooltipsList: {},

	disable: function(){ this.disabled = true; },
	enable: function(){ this.disabled = false; },

	getDisabledStatus: function() { return this.disabled; },
	getLoader: function() { return '/bitrix/tools/tooltip.php'; },
	getIdPrefix:  function() { return 'bx-ui-tooltip-'; }
};

BX.ready(function () {

	if (
		BX.browser.IsAndroid()
		|| BX.browser.IsIOS()
	)
	{
		return;
	}

	document.addEventListener('mouseover', function(e) {

		var node = BX.getEventTarget(e);

		var userId = node.getAttribute('bx-tooltip-user-id');
		var loader = node.getAttribute('bx-tooltip-loader');

		var tooltipId = userId;
		if(BX.type.isNotEmptyString(loader))
		{
			var loaderHash = 0;
			for(var i = 0, len = loader.length; i < len; i++)
			{
				loaderHash = (31 * loaderHash + loader.charCodeAt(i)) << 0;
			}

			tooltipId = loaderHash + userId;
		}

		if (parseInt(userId) > 0)
		{
			if (null == BX.UI.Tooltip.tooltipsList[tooltipId])
			{
				BX.UI.Tooltip.tooltipsList[tooltipId] = new BX.UI.TooltipBalloon({
					userId: parseInt(userId),
					node: node
				});
			}
			else
			{
				BX.UI.Tooltip.tooltipsList[tooltipId].node = node;
				BX.UI.Tooltip.tooltipsList[tooltipId].create();
			}

			e.preventDefault();
		}
	});

});

BX.UI.TooltipBalloon = function(params)
{
	this.node = params.node;
	this.userId = params.userId;

	this.tracking = false;
	this.active = false;

	this.width = 393;
	this.height = 302;

	this.realAnchor = null;
	this.coordsLeft = 0;
	this.coordsTop = 0;
	this.anchorRight = 0;
	this.anchorBottom = 0;

	this.rootClassName = this.node.getAttribute('bx-tooltip-classname');

	this.INFO = null;
	this.DIV = null;
	this.ROOT_DIV = null;

	var anchorParams = {};
	var paramsString = this.node.getAttribute('bx-tooltip-params');
	if (BX.type.isNotEmptyString(paramsString))
	{
		anchorParams = JSON.parse(paramsString);
		if (!BX.type.isPlainObject(anchorParams))
		{
			anchorParams = {};
		}
	}

	this.params = anchorParams;

	this.create = function()
	{
		if (!BX.UI.Tooltip.getDisabledStatus())
		{
			this.startTrackMouse();
		}

		BX.bind(this.node, 'mouseout', BX.delegate(this.stopTrackMouse, this));
	};

	this.trackMouseHandle = this.trackMouse.bind(this);

	this.create();

	return this;

};

BX.UI.TooltipBalloon.prototype.startTrackMouse = function()
{
	if(!this.tracking)
	{
		var _this = this;

		var elCoords = BX.pos(this.node);

		this.realAnchor = this.node;
		this.coordsLeft = elCoords.left + 0;
		this.coordsTop = elCoords.top - 325;
		this.anchorRight = elCoords.right;
		this.anchorBottom = elCoords.bottom;

		this.tracking = true;

		BX.bind(document, "mousemove", this.trackMouseHandle);

		setTimeout(BX.delegate(function() { this.tickTimer(); }, this), 500);

		BX.bind(this.node, 'mouseout', BX.delegate(this.stopTrackMouse, this));
	}
};

BX.UI.TooltipBalloon.prototype.stopTrackMouse = function()
{
	if(this.tracking)
	{
		var _this = this;

		BX.unbind(document, "mousemove", this.trackMouseHandle);

		this.active = false;
		setTimeout(BX.delegate(function() {this.hideTooltip()}, this), 500);
		this.tracking = false;
	}
};

BX.UI.TooltipBalloon.prototype.trackMouse = function(e)
{
	if(!this.tracking)
	{
		return;
	}

	var current = (
		e && e.pageX
			? {x: e.pageX, y: e.pageY}
			: {x: e.clientX + document.body.scrollLeft, y: e.clientY + document.body.scrollTop}
	);

	if(current.x < 0)
	{
		current.x = 0;
	}

	if(current.y < 0)
	{
		current.y = 0;
	}

	current.time = this.tracking;

	if(!this.active)
	{
		this.active = current;
	}
	else
	{
		if(
			this.active.x >= (current.x - 1) && this.active.x <= (current.x + 1)
			&& this.active.y >= (current.y - 1) && this.active.y <= (current.y + 1)
		)
		{
			if((this.active.time + 20/*2sec*/) <= current.time)
			{
				this.showTooltip();
			}
		}
		else
		{
			this.active = current;
		}
	}
};

BX.UI.TooltipBalloon.prototype.tickTimer = function()
{
	var _this = this;

	if(_this.tracking)
	{
		_this.tracking++;
		if (_this.active)
		{
			if( (_this.active.time + 5/*0.5sec*/)  <= _this.tracking)
			{
				_this.showTooltip();
			}
		}
		setTimeout(function() { _this.tickTimer(); }, 100);
	}
};

BX.UI.TooltipBalloon.prototype.hideTooltip = function()
{
	if (!this.tracking)
	{
		this.showOpacityEffect({func: this.SetInVisible, obj: this.DIV, arParams: []}, 1);
	}
};

BX.UI.TooltipBalloon.prototype.showOpacityEffect = function(oCallback, bFade)
{
	var steps = 3;
	var period = 1;
	var delta = 1 / steps;
	var i = 0, op, _this = this;

	if (BX.browser.IsIE() && _this.DIV)
	{
		_this.DIV.className = _this.classNameAnim;
	}

	var show = function()
	{
		i++;
		if (i > steps)
		{
			clearInterval(intId);

			if (!oCallback.arParams)
			{
				oCallback.arParams = [];
			}

			if (oCallback.func && oCallback.obj)
			{
				oCallback.func.apply(oCallback.obj, oCallback.arParams);
			}

			return;
		}
		op = bFade ? 1 - i * delta : i * delta;

		if (_this.DIV != null)
		{
			try
			{
				_this.DIV.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + (op * 100) + ')';
				_this.DIV.style.opacity = op;
				_this.DIV.style.MozOpacity = op;
				_this.DIV.style.KhtmlOpacity = op;
			}
			catch(e)
			{
			}
			finally
			{
				if (!bFade && i == 1)
				{
					_this.DIV.style.display = 'block';
				}

				if (bFade && i == steps && _this.DIV)
				{
					_this.DIV.style.display = 'none';
				}


				if (
					BX.browser.IsIE()
					&& i == 1
					&& bFade
					&& _this.IFRAME
				)
				{
					_this.IFRAME.style.display = 'none';
				}

				if (
					BX.browser.IsIE()
					&& i == steps
					&& _this.DIV
				)
				{
					if (!bFade)
					{
						_this.IFRAME.style.display = 'block';
					}

					_this.DIV.style.filter = _this.filterFixed;
					_this.DIV.className = _this.classNameFixed;
					_this.DIV.innerHTML = '' + _this.DIV.innerHTML;
				}

				if(bFade)
				{
					BX.onCustomEvent('onTooltipHide', [_this]);
				}
			}
		}

	};

	var intId = setInterval(show, period);
};

BX.UI.TooltipBalloon.prototype.showTooltip = function()
{
	var _this = this;

	var old = BX(BX.UI.Tooltip.getIdPrefix() + _this.userId);

	if (
		BX.UI.Tooltip.getDisabledStatus()
		|| (
			old
			&& old.style.display == 'block'
		)
	)
	{
		return;
	}

	if (null == _this.DIV && null == _this.ROOT_DIV)
	{
		_this.ROOT_DIV = document.body.appendChild(document.createElement('DIV'));
		_this.ROOT_DIV.style.position = 'absolute';

		_this.DIV = _this.ROOT_DIV.appendChild(document.createElement('DIV'));
		_this.DIV.className = 'bx-ui-tooltip-info-shadow';

		_this.DIV.style.width = _this.width + 'px';
		_this.DIV.style.height = _this.height + 'px';
	}

	var left = _this.coordsLeft;
	var top = _this.coordsTop + 30;
	var arScroll = BX.GetWindowScrollPos();
	var body = document.body;

	var h_mirror = false;
	var v_mirror = false;

	if((body.clientWidth + arScroll.scrollLeft) < (left + _this.width))
	{
		left = _this.anchorRight - _this.width;
		h_mirror = true;
	}

	if((top - arScroll.scrollTop) < 0)
	{
		top = _this.anchorBottom - 5;
		v_mirror = true;
		_this.v_delta = 40;
	}
	else
	{
		_this.v_delta = 0;
	}

	_this.ROOT_DIV.style.left = parseInt(left) + "px";
	_this.ROOT_DIV.style.top = parseInt(top) + "px";
	_this.ROOT_DIV.style.zIndex = 1200;

	BX.bind(BX(_this.ROOT_DIV), "click", BX.eventCancelBubble);

	if (
		this.rootClassName != 'undefined'
		&& this.rootClassName != null
		&& this.rootClassName.length > 0
	)
	{
		_this.ROOT_DIV.className = this.rootClassName;
	}

	var loader = BX.UI.Tooltip.getLoader();

	if ('' == _this.DIV.innerHTML)
	{
		var url = loader +
			(loader.indexOf('?') >= 0 ? '&' : '?') +
			'MODE=UI&MUL_MODE=INFO&USER_ID=' + _this.userId +
			'&site=' + (BX.message('SITE_ID') || '') +
			(
				typeof _this.params != 'undefined'
				&& typeof _this.params.entityType != 'undefined'
				&& _this.params.entityType.length > 0
					? '&entityType=' + _this.params.entityType
					: ''
			) +
			(
				typeof _this.params != 'undefined'
				&& typeof _this.params.entityId != 'undefined'
				&& parseInt(_this.params.entityId) > 0
					? '&entityId=' + parseInt(_this.params.entityId)
					: ''
			);

		BX.ajax.get(url, BX.delegate(_this.insertData, _this));

		_this.DIV.id = BX.UI.Tooltip.getIdPrefix() + _this.userId;

		_this.DIV.innerHTML = '<div class="bx-ui-tooltip-info-wrap">'
			+ '<div class="bx-ui-tooltip-info-leftcolumn">'
			+ '<div class="bx-ui-tooltip-photo" id="' + BX.UI.Tooltip.getIdPrefix() + 'photo-' + _this.userId + '"><div class="bx-ui-tooltip-info-data-loading">' + BX.message('JS_CORE_LOADING') + '</div></div>'
			+ '<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-left" id="' + BX.UI.Tooltip.getIdPrefix() + 'toolbar-' + _this.userId + '"></div>'
			+ '</div>'
			+ '<div class="bx-ui-tooltip-info-data">'
			+ '<div id="' + BX.UI.Tooltip.getIdPrefix() + 'data-card-' + _this.userId + '"></div>'
			+ '<div class="bx-ui-tooltip-info-data-tools">'
			+ '<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-right" id="' + BX.UI.Tooltip.getIdPrefix() + 'toolbar2-' + _this.userId + '"></div>'
			+ '<div class="bx-ui-tooltip-info-data-clear"></div>'
			+ '</div>'
			+ '</div>'
			+ '</div><div class="bx-ui-tooltip-info-bottomarea"></div>';
	}

	_this.DIV.className = 'bx-ui-tooltip-info-shadow';
	_this.classNameAnim = 'bx-ui-tooltip-info-shadow-anim';
	_this.classNameFixed = 'bx-ui-tooltip-info-shadow';

	_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-left-top.png', sizingMethod = 'crop' );";

	if (h_mirror && v_mirror)
	{
		_this.DIV.className = 'bx-ui-tooltip-info-shadow-hv';
		_this.classNameAnim = 'bx-ui-tooltip-info-shadow-hv-anim';
		_this.classNameFixed = 'bx-ui-tooltip-info-shadow-hv';
		_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-right-bottom.png', sizingMethod = 'crop' );";
	}
	else
	{
		if (h_mirror)
		{
			_this.DIV.className = 'bx-ui-tooltip-info-shadow-h';
			_this.classNameAnim = 'bx-ui-tooltip-info-shadow-h-anim';
			_this.classNameFixed = 'bx-ui-tooltip-info-shadow-h';
			_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-right-top.png', sizingMethod = 'crop' );";
		}

		if (v_mirror)
		{
			_this.DIV.className = 'bx-ui-tooltip-info-shadow-v';
			_this.classNameAnim = 'bx-ui-tooltip-info-shadow-v-anim';
			_this.classNameFixed = 'bx-ui-tooltip-info-shadow-v';

			_this.filterFixed = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/components/bitrix/main.user.link/templates/.default/images/cloud-left-bottom.png', sizingMethod = 'crop' );";
		}
	}

	if (BX.browser.IsIE() && null == _this.IFRAME)
	{
		_this.IFRAME = document.body.appendChild(document.createElement('IFRAME'));
		_this.IFRAME.id = _this.DIV.id + "_frame";
		_this.IFRAME.style.position = 'absolute';
		_this.IFRAME.style.width = (_this.width - 60) + 'px';
		_this.IFRAME.style.height = (_this.height - 100) + 'px';
		_this.IFRAME.style.borderStyle = 'solid';
		_this.IFRAME.style.borderWidth = '0px';
		_this.IFRAME.style.zIndex = 550;
		_this.IFRAME.style.display = 'none';
	}

	if (BX.browser.IsIE())
	{
		_this.IFRAME.style.left = (parseInt(left) + 25) + "px";
		_this.IFRAME.style.top = (parseInt(top) + 30 + _this.v_delta) + "px";
	}

	_this.DIV.style.display = 'none';
	_this.showOpacityEffect({func: _this.SetVisible, obj: _this.DIV, arParams: []}, 0);

	BX(BX.UI.Tooltip.getIdPrefix() + _this.userId).onmouseover = function() {
		_this.startTrackMouse(this);
	};

	BX(BX.UI.Tooltip.getIdPrefix() + _this.userId).onmouseout = function() {
		_this.stopTrackMouse(this);
	};

	BX.onCustomEvent('onTooltipShow', [this]);
};

BX.UI.TooltipBalloon.prototype.insertData = function(data)
{
	var _this = this;

	if (null != data && data.length > 0)
	{
		eval('_this.INFO = ' + data);

		var cardEl = BX(BX.UI.Tooltip.getIdPrefix() + 'data-card-' + _this.userId);
		cardEl.innerHTML = _this.INFO.RESULT.Card;

		var photoEl = BX(BX.UI.Tooltip.getIdPrefix() + 'photo-' + _this.userId);
		photoEl.innerHTML = _this.INFO.RESULT.Photo;

		var toolbarEl = BX(BX.UI.Tooltip.getIdPrefix() + 'toolbar-' + _this.userId);
		toolbarEl.innerHTML = _this.INFO.RESULT.Toolbar;

		var toolbar2El = BX(BX.UI.Tooltip.getIdPrefix() + 'toolbar2-' + _this.userId);
		toolbar2El.innerHTML = _this.INFO.RESULT.Toolbar2;

		if(BX.type.isArray(_this.INFO.RESULT.Scripts))
		{
			for(var i = 0; i < _this.INFO.RESULT.Scripts.length; i++)
			{
				eval(_this.INFO.RESULT.Scripts[i]);
			}
		}

		BX.onCustomEvent('onTooltipInsertData', [_this]);
	}
};

})();
