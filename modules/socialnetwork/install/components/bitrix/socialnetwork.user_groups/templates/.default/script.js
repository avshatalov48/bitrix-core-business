BitrixSUG = function ()
{
	this.bLoadStarted = null;
	this.refreshUrl = null;
	this.mainFilterUsed = false;
	this.sortKey = 'alpha';
	this.project = 'N';
};

BitrixSUG.prototype.init = function(params)
{
	if (typeof params != 'undefined')
	{
		this.refreshUrl = (typeof params.refreshUrl != 'undefined' ? params.refreshUrl : top.location.href);
		this.project = (typeof params.project != 'undefined' && params.project == 'Y' ? 'Y' : 'N');
		if (
			typeof params.keyboardPageNavigation != 'undefined'
			&& params.keyboardPageNavigation
			&& typeof params.navId != 'undefined'
		)
		{
			this.initKeyboardPageNavigation(params.navId);
		}
	}

	this.processNavigation();

	BX.addCustomEvent("BX.SonetGroupList.Filter:beforeApply", BX.delegate(function(filterValues, filterPromise) {
		this.showRefreshFade();
	}, this));

	BX.addCustomEvent("BX.SonetGroupList.Filter:apply", BX.delegate(function(filterValues, filterPromise, filterParams) {

		if (typeof filterParams != 'undefined')
		{
			filterParams.autoResolve = false;
		}

		this.refresh({
			useBXMainFilter: 'Y'
		}, filterPromise);

	}, this));

	BX.addCustomEvent("BX.SonetGroupList.Filter:searchInput", BX.delegate(function(searchString) {

		if (
			typeof searchString != 'undefined'
			&& BX.util.trim(searchString).length > 0
		)
		{
			this.showRefreshFade();
		}
		else
		{
			this.hideRefreshFade();
		}

	}, this));

	BX.addCustomEvent('SidePanel.Slider:onMessage', BX.delegate(function(event){
		if (event.getEventId() == 'sonetGroupEvent')
		{
			var eventData = event.getData();
			if (
				BX.type.isNotEmptyString(eventData.code)
				&& eventData.code == 'afterEdit'
			)
			{
				BX.SocialnetworkUICommon.reload();
			}
		}
	}, this));
};

BitrixSUG.prototype.sendRequest = function(params)
{
	if (
		typeof params == 'undefined'
		|| typeof params.groupId == 'undefined'
		|| parseInt(params.groupId) <= 0
	)
	{
		return false;
	}

	if (
		typeof params.action == 'undefined'
		|| !BX.util.in_array(params.action, ['REQUEST', 'FAVORITES'])
	)
	{
		return false;
	}

	var requestParams = {};

	if (params.action == 'FAVORITES')
	{
		requestParams.value = (typeof params.value != 'undefined' ? params.value : 'Y');
	}

	BX.ajax({
		url: '/bitrix/components/bitrix/socialnetwork.user_groups/ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			sessid : BX.bitrix_sessid(),
			site : BX.message('SITE_ID'),
			groupId: parseInt(params.groupId),
			action : params.action,
			params : requestParams
		},
		onsuccess: function(responseData)
		{
			if (typeof responseData.SUCCESS != 'undefined')
			{
				params.callback_success(responseData);
			}
			else
			{
				params.callback_failure(typeof responseData.ERROR != 'undefined' ? responseData.ERROR : BX('SONET_C33_T_F_REQUEST_ERROR'));
			}
		},
		onfailure: function(responseData)
		{
			params.callback_failure(BX('SONET_C33_T_F_REQUEST_ERROR'));
		}
	});

	return false;
};

BitrixSUG.prototype.showRequestWait = function(target)
{
	BX.addClass(target, 'popup-window-button-wait');
};

BitrixSUG.prototype.closeRequestWait = function(target)
{
	BX.removeClass(target, 'popup-window-button-wait');
};

BitrixSUG.prototype.setFavorites = function(node, active)
{
	if (BX(node))
	{
		var flyingStar = node.cloneNode();
		flyingStar.style.marginLeft = "-" + node.offsetWidth + "px";
		node.parentNode.appendChild(flyingStar);

		new BX.easing({
			duration: 200,
			start: { opacity: 100, scale: 100 },
			finish: { opacity: 0, scale: 300 },
			transition : BX.easing.transitions.linear,
			step: function(state) {
				flyingStar.style.transform = "scale(" + state.scale / 100 + ")";
				flyingStar.style.opacity = state.opacity / 100;
			},
			complete: function() {
				flyingStar.parentNode.removeChild(flyingStar);
			}
		}).animate();

		if (!!active)
		{
			BX.addClass(node, 'sonet-groups-group-title-favorites-active');
			BX.adjust(node, { attrs : {title : BX.message('SONET_C33_T_ACT_FAVORITES_REMOVE')} });
		}
		else
		{
			BX.removeClass(node, 'sonet-groups-group-title-favorites-active');
			BX.adjust(node, { attrs : {title : BX.message('SONET_C33_T_ACT_FAVORITES_ADD')} });
		}
	}
};

BitrixSUG.prototype.setRequestSent = function(node, sentNode, role)
{
	if (BX(node))
	{
		this.closeRequestWait(node);
		BX.addClass(node, 'sonet-groups-group-btn-sent');
	}

	if (
		typeof role != 'undefined'
		&& role == 'Z'
		&& BX(sentNode)
	)
	{
		BX.addClass(sentNode, 'sonet-groups-group-desc-container-active');
	}
};

BitrixSUG.prototype.showRequestSent = function(sentNode)
{
	if (BX(sentNode))
	{
		BX.addClass(sentNode, 'sonet-groups-group-desc-container-active');
	}
};

BitrixSUG.prototype.showError = function(errorText)
{
	var errorPopup = new BX.PopupWindow('ug-error' + Math.random(), window, {
		autoHide: true,
		lightShadow: false,
		zIndex: 2,
		content: BX.create('DIV', {props: {'className': 'sonet-groups-error-text-block'}, html: BX.message('SONET_C33_T_F_REQUEST_ERROR').replace('#ERROR#', errorText)}),
		closeByEsc: true,
		closeIcon: true
	});
	errorPopup.show();
};

BitrixSUG.prototype.showSortMenu = function(params)
{
	BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
	BX.PopupMenu.show('bx-sonet-groups-sort-menu', params.bindNode, [
		{
			text: BX.message('SONET_C33_T_F_SORT_ALPHA'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_ALPHA'),
					key: 'alpha',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		},
		(
			parseInt(params.userId) == BX.message('USER_ID')
			&& parseInt(params.userId) > 0
				? {
					text: BX.message('SONET_C33_T_F_SORT_DATE_REQUEST'),
					onclick: BX.proxy(function() {
						this.selectSortMenuItem({
							text: BX.message('SONET_C33_T_F_SORT_DATE_REQUEST'),
							key: 'date_request',
							valueNode: params.valueNode
						});
						BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
					}, this)
				}
				: null
		),
		(
			parseInt(params.userId) == BX.message('USER_ID')
			&& parseInt(params.userId) > 0
				? {
					text: BX.message('SONET_C33_T_F_SORT_DATE_VIEW'),
					onclick: BX.proxy(function() {
						this.selectSortMenuItem({
							text: BX.message('SONET_C33_T_F_SORT_DATE_VIEW'),
							key: 'date_view',
							valueNode: params.valueNode
						});
						BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
					}, this)
				}
				: null
		),
		(
			params.showMembersCountItem
				? {
					text: BX.message('SONET_C33_T_F_SORT_MEMBERS_COUNT'),
					onclick: BX.proxy(function() {
						this.selectSortMenuItem({
							text: BX.message('SONET_C33_T_F_SORT_MEMBERS_COUNT'),
							key: 'members_count',
							valueNode: params.valueNode
						});
						BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
					}, this)
				}
				: null
		),
		{
			text: BX.message('SONET_C33_T_F_SORT_DATE_ACTIVITY'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_DATE_ACTIVITY'),
					key: 'date_activity',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		},
		{
			text: BX.message('SONET_C33_T_F_SORT_DATE_CREATE'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_DATE_CREATE'),
					key: 'date_create',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		}
	], {
		offsetLeft: 100,
		offsetTop: 0,
		lightShadow: false,
		angle: {position: 'top', offset : 50}
 	});

	return false;
};

BitrixSUG.prototype.selectSortMenuItem = function(params)
{
	this.setSortMenuItemText(params);

	var url = null;

	if (!BX.util.in_array(params.key, ['alpha', 'date_request', 'date_view', 'members_count', 'date_activity', 'date_create']))
	{
		params.key = 'alpha';
	}

	this.sortKey = params.key;

	switch(params.key)
	{
		case 'alpha':
			url = BX.message('filterAlphaUrl');
			break;
		case 'date_request':
			url = BX.message('filterDateRequestUrl');
			break;
		case 'date_view':
			url = BX.message('filterDateViewUrl');
			break;
		case 'members_count':
			url = BX.message('filterMembersCountUrl');
			break;
		case 'date_activity':
			url = BX.message('filterDateActivityUrl');
			break;
		case 'date_create':
			url = BX.message('filterDateCreateUrl');
			break;
		default:
			url = BX.message('filterAlphaUrl')
	}

	if (this.mainFilterUsed)
	{
		url += '&useBXMainFilter=Y'
	}

	this.refresh({
		url: url
	});
};

BitrixSUG.prototype.setSortMenuItemText = function(params)
{
	BX(params.valueNode).innerHTML = params.text;
};

BitrixSUG.prototype.refresh = function(params, filterPromise)
{
	if (this.bLoadStarted)
	{
		return;
	}

	var url = this.refreshUrl;

	if (
		typeof params != 'undefined'
		&& typeof params.url != 'undefined'
	)
	{
		url = params.url;
		params.url = null;
	}

	this.bLoadStarted = true;
	this.showRefreshFade();

	var useBXMainFilter = (
		(
			typeof params != 'undefined'
			&& typeof params.useBXMainFilter != 'undefined'
			&& params.useBXMainFilter == 'Y'
		)
		|| url.indexOf('&useBXMainFilter=Y') >= 0
	);

	if (typeof params == 'undefined')
	{
		params = {};
	}

	params.order = this.sortKey;

	var urlParams = BX.ajax.prepareData(params);
	if (urlParams)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + urlParams;
	}

	this.bLoadStarted = false;

	BX.ajax({
		url: url,
		method: 'GET',
		dataType: 'json',
		onsuccess: BX.delegate(function(data)
		{
			this.bLoadStarted = false;
			this.hideRefreshFade();
			this.mainFilterUsed = useBXMainFilter;

			if (
				typeof data != 'undefined'
				&& typeof (data.PROPS) != 'undefined'
			)
			{
				if (filterPromise)
				{
					filterPromise.fulfill();
				}

				var loaderContainer = null;
				if (BX('sonet-groups-loader-loader-container'))
				{
					loaderContainer = BX('sonet-groups-loader-loader-container');
				}

				BX.cleanNode('sonet-groups-content-container', false);

				if (
					typeof data.PROPS.EMPTY != 'undefined'
					&& (data.PROPS.EMPTY == 'Y')
				)
				{
					BX.addClass(BX('sonet-groups-content-wrap'), 'no-groups');

					BX('sonet-groups-content-container').appendChild(BX.create('DIV', {
						props: {
							id: 'sonet-groups-list-container',
							className: 'sonet-groups-list-container'
						},
						children: [
							BX.create('DIV', {
								props: {
									id: 'sonet-groups-loader-container',
									className: 'sonet-groups-loader-container'
								},
								html: '<svg class="sonet-groups-loader-circular" viewBox="25 25 50 50"><circle class="sonet-groups-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"></circle></svg>'
							}),
							BX.create('DIV', {
								props: {
									className: 'sonet-groups-group-message'
								},
								children: [
									BX.create('DIV', {
										props: {
											className: 'sonet-groups-group-message-text'
										},
										html: (this.project == 'Y' ? BX.message('SONET_C36_T_NO_GROUPS_PROJECT') : BX.message('SONET_C36_T_NO_GROUPS'))
									})
								]
							})
						]
					}));
				}
				else
				{
					BX.removeClass(BX('sonet-groups-content-wrap'), 'no-groups');
				}

				if (loaderContainer)
				{
					BX('sonet-groups-list-container').appendChild(loaderContainer);
				}

				if (
					typeof (data.PROPS.CONTENT) != 'undefined'
					&& (data.PROPS.CONTENT.length > 0)
				)
				{
					this.processAjaxBlock(data.PROPS);
				}
				this.processNavigation();
			}
			else
			{
				if (filterPromise)
				{
					filterPromise.reject();
				}
				this.showRefreshError();
			}
		}, this),
		onfailure: BX.delegate(function(data)
		{
			this.bLoadStarted = false;
			if (filterPromise)
			{
				filterPromise.reject();
			}

			this.hideRefreshFade();
			this.showRefreshError();
		}, this)
	});

	return false;
};

BitrixSUG.prototype.showRefreshFade = function()
{
	if (
		BX('sonet-groups-list-container')
		&& !BX.hasClass(BX('sonet-groups-list-container'), 'sonet-groups-internal-mask')
	)
	{
		BX.addClass(BX('sonet-groups-list-container'), 'sonet-groups-internal-mask');

		var loaderContainer = BX('sonet-groups-loader-container');
		if (loaderContainer)
		{
			BX.style(loaderContainer, 'display', 'block');
			BX.removeClass(loaderContainer, 'sonet-groups-hide-loader');

			setTimeout(function() {
				BX.addClass(loaderContainer, 'sonet-groups-show-loader');
			}, 0);

		}
	}
};

BitrixSUG.prototype.hideRefreshFade = function()
{
	BX.removeClass(BX('sonet-groups-list-container'), 'sonet-groups-internal-mask');

	var loaderContainer = BX('sonet-groups-loader-container');
	if (loaderContainer)
	{
		BX.removeClass(loaderContainer, 'sonet-groups-show-loader');
		BX.addClass(loaderContainer, 'sonet-groups-hide-loader');
	}
};

BitrixSUG.prototype.showRefreshError = function()
{
};
/*
BitrixSUG.prototype._onAnimationEnd = function(event)
{
	if (
		'animationName' in event
		&& event.animationName
		&& event.animationName === 'hideLoader'
	)
	{
		var loaderContainer = BX('feed-loader-container');
		BX.removeClass(loaderContainer, 'livefeed-show-loader');
		BX.removeClass(loaderContainer, 'livefeed-hide-loader');
		BX.style(loaderContainer, 'display', '');
	}
};
*/
BitrixSUG.prototype.processAjaxBlock = function(block)
{
	if (!block)
	{
		return;
	}

	var htmlWasInserted = false;
	var scriptsLoaded = false;

	insertHTML();
	processInlineJS();

	function insertHTML()
	{
		BX('sonet-groups-content-container').appendChild(BX.create('DIV', {
			props: {},
			html: block.CONTENT
		}));

		htmlWasInserted = true;
		if (scriptsLoaded)
		{
			processInlineJS();
		}
	}

	function processInlineJS()
	{
		scriptsLoaded = true;
		if (htmlWasInserted)
		{
			BX.ajax.processRequestData(block.CONTENT, {
				scriptsRunFirst: false,
				dataType: "HTML"
			});
		}
	}
};

BitrixSUG.prototype.initKeyboardPageNavigation = function(navId)
{
	window.addEventListener("keydown", BX.delegate(function(event) {
		if (
			(event.ctrlKey || event.metaKey)
			&& [39, 37].indexOf(event.keyCode) !== -1
		)
		{
			var link = null;

			if (event.keyCode === 39)
			{
				link = BX(navId + '_next_page');
			}

			if (event.keyCode === 37)
			{
				link = BX(navId + '_previous_page');
			}

			if (link && link.href)
			{
				event.preventDefault();
				event.stopImmediatePropagation();

				this.refresh({
					url: link.href + "&refreshAjax=Y"
				});
			}
		}
	}, this), true);
};

BitrixSUG.prototype.processNavigation = function()
{
	var navContainer = BX('sonet-groups-nav-container');
	if (!navContainer)
	{
		return;
	}

	var anchorsList = BX.findChildren(navContainer, { tagName: 'a' }, true);
	for (var i = 0; i < anchorsList.length; i++)
	{
		BX.bind(anchorsList[i], 'click', BX.delegate(function(e) {
			var link = e.currentTarget.href;

			if (
				link
				&& link.length > 0
			)
			{
				this.clickNavigation(link);
				e.preventDefault();
			}
		}, this));
	}
};

BitrixSUG.prototype.clickNavigation = function(link)
{
	this.refresh({
		url: link + "&refreshAjax=Y"
	});
};

oSUG = new BitrixSUG;
window.oSUG = oSUG;