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

		var tooltipId = userId; // don't use integer value!
		if(BX.type.isNotEmptyString(loader))
		{
			var loaderHash = 0;
			for(var i = 0, len = loader.length; i < len; i++)
			{
				loaderHash = (31 * loaderHash + loader.charCodeAt(i)) << 0;
			}

			tooltipId = loaderHash + userId;
		}

		if (BX.type.isNotEmptyString(userId))
		{
			if (null == BX.UI.Tooltip.tooltipsList[tooltipId])
			{
				BX.UI.Tooltip.tooltipsList[tooltipId] = new BX.UI.TooltipBalloon({
					userId: userId,
					node: node,
					loader: loader
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
	this.loader = (BX.type.isNotEmptyString(params.loader) ? params.loader : '');

	this.version = (
		typeof params.version != 'undefined'
		&& parseInt(params.version) > 0
			? parseInt(params.version)
			: (BX.type.isNotEmptyString(this.loader) ? 2 : 3)
	);

	this.tracking = false;
	this.active = false;

	this.width = 364; // 393
	this.height = 215; // 302

	this.realAnchor = null;
	this.coordsLeft = 0;
	this.coordsTop = 0;
	this.anchorRight = 0;
	this.anchorTop = 0;
	this.hMirror = false;
	this.vMirror = false;

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

		BX.addCustomEvent("SidePanel.Slider:onOpen", function(event) {
			if (this.tracking)
			{
				this.stopTrackMouse();
			}
			else
			{
				this.hideTooltip();
			}
		}.bind(this));
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

		this.coordsLeft = (
			elCoords.width < 40
				? (elCoords.left - 35)
				: (elCoords.left + 0)
		);
		this.coordsTop = elCoords.top - 245; // 325
		this.anchorRight = elCoords.right;
		this.anchorTop = elCoords.top;

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
		this.showOpacityEffect(1);
	}
};

BX.UI.TooltipBalloon.prototype.showOpacityEffect = function(bFade)
{
	var steps = 3;
	var period = 1;
	var delta = 1 / steps;
	var i = 0, op, _this = this;

	var show = function()
	{
		i++;
		if (i > steps)
		{
			clearInterval(intId);
			return;
		}
		op = bFade ? 1 - i * delta : i * delta;

		if (_this.DIV != null)
		{
			try
			{
				_this.DIV.style.opacity = op;
			}
			catch(e)
			{
			}
			finally
			{
				if (!bFade && i == 1)
				{
					_this.DIV.classList.add("ui-tooltip-info-shadow-show");
					_this.DIV.style.display = 'block';
				}

				if (bFade && i == steps && _this.DIV)
				{
					_this.DIV.classList.remove("ui-tooltip-info-shadow-show");
					_this.DIV.classList.add("ui-tooltip-info-shadow-hide");
					setTimeout(BX.delegate(function() {_this.DIV.style.display = 'none'}, this), 500);
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
			&& old.classList.contains('ui-tooltip-info-shadow-show')
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
//		_this.DIV.style.height = _this.height + 'px';
	}

	var left = _this.coordsLeft;
	var top = _this.coordsTop + 30;
	var arScroll = BX.GetWindowScrollPos();
	var body = document.body;

	_this.hMirror = false;
	_this.vMirror = ((top - arScroll.scrollTop) < 0);

	if((body.clientWidth + arScroll.scrollLeft) < (left + _this.width))
	{
		left = _this.anchorRight - _this.width;
		_this.hMirror = true;
	}

	_this.ROOT_DIV.style.left = parseInt(left) + "px";
	_this.ROOT_DIV.style.top = parseInt(top) + "px";
	_this.ROOT_DIV.style.zIndex = 3200;

	BX.bind(BX(_this.ROOT_DIV), "click", BX.eventCancelBubble);

	if (BX.type.isNotEmptyString(this.rootClassName))
	{
		_this.ROOT_DIV.className = this.rootClassName;
	}

	var loader = (BX.type.isNotEmptyString(_this.loader) ? _this.loader : BX.UI.Tooltip.getLoader());

	// create stub
	var stubCreated = false;

	if ('' == _this.DIV.innerHTML)
	{
		stubCreated = true;

		if (_this.version >= 3)
		{
			BX.ajax.runComponentAction('bitrix:ui.tooltip', 'getData', {
				mode: 'ajax',
				data: {
					userId: _this.userId,
					params: (typeof _this.params != 'undefined' ? _this.params : {})
				}
			}).then(function (response) {

				var detailUrl = ((BX.type.isNotEmptyString(response.data.user.detailUrl)) ? response.data.user.detailUrl : '');
				var cardUserName = '';

				if (BX.type.isNotEmptyString(response.data.user.nameFormatted))
				{
					if (BX.type.isNotEmptyString(detailUrl))
					{
						cardUserName = '<a href="' + detailUrl + '">' + response.data.user.nameFormatted + '</a>';
					}
					else
					{
						cardUserName = response.data.user.nameFormatted;
					}
				}

				var cardFields = '<div class="bx-ui-tooltip-info-data-info">';
				for (var fieldCode in response.data.user.cardFields)
				{
					if (response.data.user.cardFields.hasOwnProperty(fieldCode))
					{
						cardFields += '<span class="bx-ui-tooltip-field-row bx-ui-tooltip-field-row-' + fieldCode.toLowerCase() + '"><span class="bx-ui-tooltip-field-name">' + response.data.user.cardFields[fieldCode].name + '</span>: <span class="bx-ui-tooltip-field-value">' + response.data.user.cardFields[fieldCode].value + '</span></span>';
					}
				}
				cardFields += '</div>';

				var cardFieldsClassName = (
					parseInt(BX.message('USER_ID')) > 0
					&& response.data.currentUserPerms.operations.videocall
						? 'bx-ui-tooltip-info-data-cont-video'
						: 'bx-ui-tooltip-info-data-cont'
				);
				cardFields = '<div id="bx_user_info_data_cont_' + response.data.user.id + '" class="' + cardFieldsClassName + '">' + cardFields + '</div>';

				var photo = '';
				var photoClassName = 'bx-ui-tooltip-info-data-photo no-photo';

				if (BX.type.isNotEmptyString(response.data.user.photo))
				{
					photo = response.data.user.photo;
					photoClassName = 'bx-ui-tooltip-info-data-photo';
				}

				photo = (
					BX.type.isNotEmptyString(detailUrl)
						? '<a href="' + detailUrl + '" class="' + photoClassName + '">' + photo + '</a>'
						: '<span class="' + photoClassName + '">' + photo + '</span>'
				);

				var toolbar = toolbar2 = '';

				if (
					parseInt(BX.message('USER_ID')) > 0
					&& response.data.user.active
					&& response.data.user.id != BX.message('USER_ID')
					&& response.data.currentUserPerms.operations.message
				)
				{
					toolbar2 += '<li class="bx-icon bx-icon-message"><span onclick="return BX.tooltip.openIM(' + response.data.user.id +');">' + BX.message('MAIN_UL_TOOLBAR_MESSAGES_CHAT') + '</span></li>';
					toolbar2 += '<li id="im-video-call-button' + response.data.user.id + '" class="bx-icon bx-icon-video"><span onclick="return BX.tooltip.openCallTo(' + response.data.user.id +');">' + BX.message('MAIN_UL_TOOLBAR_VIDEO_CALL') + '</span></li>';
					toolbar2 += '<script type="text/javascript">BX.ready(function() {BX.tooltip.checkCallTo("im-video-call-button' + response.data.user.id + '"); };</script>';
				}

				toolbar2 = (BX.type.isNotEmptyString(toolbar2) ? '<div class="bx-ui-tooltip-info-data-separator"></div><ul>' + toolbar2 + '</ul>' : '');

				if (response.data.user.hasBirthday)
				{
					toolbar += '<li class="bx-icon bx-icon-birth">' + BX.message('MAIN_UL_TOOLBAR_BIRTHDAY') + '</li>';
				}

				if (response.data.user.hasHonour)
				{
					toolbar += '<li class="bx-icon bx-icon-featured">' + BX.message('MAIN_UL_TOOLBAR_HONORED') + '</li>';
				}

				if (response.data.user.hasAbsence)
				{
					toolbar += '<li class="bx-icon bx-icon-away">' + BX.message('MAIN_UL_TOOLBAR_ABSENT') + '</li>';
				}

				toolbar = (BX.type.isNotEmptyString(toolbar) ? '<ul>' + toolbar + '</ul>' : '');

				_this.insertData({
					RESULT: {
						Name: cardUserName,
						Position: (BX.type.isNotEmptyString(response.data.user.position) ? response.data.user.position : ''),
						Card: cardFields,
						Photo: photo,
						Toolbar: toolbar,
						Toolbar2: toolbar2
					}
				});
				_this.adjustPosition();

			}, function (response) {
				/**
				 {
					 "status": "error",
					 "errors": [...]
				 }
				 **/
			});
		}
		else
		{
			var url = loader +
				(loader.indexOf('?') >= 0 ? '&' : '?') +
				'MODE=UI&MUL_MODE=INFO&USER_ID=' + _this.userId +
				'&site=' + (BX.message('SITE_ID') || '') +
				'&version=' + _this.version +
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

			BX.ajax.get(url, BX.proxy(function(data) {
				_this.insertData(data);
				_this.adjustPosition();
			}, _this));
		}

		_this.DIV.id = BX.UI.Tooltip.getIdPrefix() + _this.userId;

		_this.DIV.innerHTML = '<div class="bx-ui-tooltip-info-wrap">'
			+ '<div class="bx-ui-tooltip-info-leftcolumn">'
			+ '<div class="bx-ui-tooltip-photo" id="' + BX.UI.Tooltip.getIdPrefix() + 'photo-' + _this.userId + '"><div class="bx-ui-tooltip-info-data-loading">' + BX.message('JS_CORE_LOADING') + '</div></div>'
			+ '</div>'
			+ '<div class="bx-ui-tooltip-info-data">'
			+ '<div id="' + BX.UI.Tooltip.getIdPrefix() + 'data-card-' + _this.userId + '"></div>'
			+ '<div class="bx-ui-tooltip-info-data-tools">'
			+ '<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-left" id="' + BX.UI.Tooltip.getIdPrefix() + 'toolbar-' + _this.userId + '"></div>'
			+ '<div class="bx-ui-tooltip-tb-control bx-ui-tooltip-tb-control-right" id="' + BX.UI.Tooltip.getIdPrefix() + 'toolbar2-' + _this.userId + '"></div>'
			+ '<div class="bx-ui-tooltip-info-data-clear"></div>'
			+ '</div>'
			+ '</div>'
			+ '</div><div class="bx-ui-tooltip-info-bottomarea"></div>';
	}

	_this.DIV.className = 'bx-ui-tooltip-info-shadow';
	_this.classNameAnim = 'bx-ui-tooltip-info-shadow-anim';
	_this.classNameFixed = 'bx-ui-tooltip-info-shadow';

	if (_this.hMirror && _this.vMirror)
	{
		_this.DIV.className = 'bx-ui-tooltip-info-shadow-hv';
		_this.classNameAnim = 'bx-ui-tooltip-info-shadow-hv-anim';
		_this.classNameFixed = 'bx-ui-tooltip-info-shadow-hv';
	}
	else
	{
		if (_this.hMirror)
		{
			_this.DIV.className = 'bx-ui-tooltip-info-shadow-h';
			_this.classNameAnim = 'bx-ui-tooltip-info-shadow-h-anim';
			_this.classNameFixed = 'bx-ui-tooltip-info-shadow-h';
		}

		if (_this.vMirror)
		{
			_this.DIV.className = 'bx-ui-tooltip-info-shadow-v';
			_this.classNameAnim = 'bx-ui-tooltip-info-shadow-v-anim';
			_this.classNameFixed = 'bx-ui-tooltip-info-shadow-v';
		}
	}

	_this.DIV.style.display = 'block';

	if (!stubCreated)
	{
		_this.adjustPosition();
	}

//	_this.DIV.style.display = 'none';
	_this.showOpacityEffect(0);

	BX(BX.UI.Tooltip.getIdPrefix() + _this.userId).onmouseover = function() {
		_this.startTrackMouse(this);
	};

	BX(BX.UI.Tooltip.getIdPrefix() + _this.userId).onmouseout = function() {
		_this.stopTrackMouse(this);
	};

	BX.onCustomEvent('onTooltipShow', [this]);
};

BX.UI.TooltipBalloon.prototype.adjustPosition = function()
{
	var tooltipCoords = BX.pos(this.DIV);

	if (this.vMirror)
	{
		this.ROOT_DIV.style.top = parseInt(this.anchorTop + 13) + "px";
	}
	else
	{
		this.ROOT_DIV.style.top = parseInt(this.anchorTop - tooltipCoords.height - 13 + 12) + "px"; // 12 - bottom block
	}
};

BX.UI.TooltipBalloon.prototype.insertData = function(data)
{
	var _this = this;

	if (
		null != data
		&& (
			_this.version >= 3
			|| data.length > 0
		)
	)
	{
		if (_this.version >= 3)
		{
			_this.INFO = data;
		}
		else
		{
			eval('_this.INFO = ' + data);
		}

		var cardEl = BX(BX.UI.Tooltip.getIdPrefix() + 'data-card-' + _this.userId);
		cardEl.innerHTML = '';
		if (BX.type.isNotEmptyString(_this.INFO.RESULT.Name))
		{
			cardEl.innerHTML += '<div class="bx-ui-tooltip-user-name">' + _this.INFO.RESULT.Name + '</div>';
		}
		if (BX.type.isNotEmptyString(_this.INFO.RESULT.Position))
		{
			cardEl.innerHTML += '<div class="bx-ui-tooltip-user-position">' + _this.INFO.RESULT.Position + '</div>';
		}
		cardEl.innerHTML += _this.INFO.RESULT.Card;

		// use _this.INFO.RESULT.Position

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
