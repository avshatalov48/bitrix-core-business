(function() {

var BX = window.BX;
if (BX.UserContentView)
{
	return;
}

BX.UserContentView = {
	displayHeight: 0,
	mobile: false,
	ajaxUrl: '/bitrix/tools/sonet_set_content_view.php',
	pathToUserProfile: '',
	inited: false,
	viewAreaList: [],
/*	fullContentNodeList: {},*/
	lastViewAreaList: {},
	viewAreaReadList: [],
	viewAreaSentList: [],
	viewAreaAverageHeight: 200,
	viewAreaTimePeriodMin: 1000,
	viewAreaTimePeriodMax: 10000,
	viewAreaTimePeriodAvg: 1500,
	sendViewAreaTimeout: 5000,
	commentsContainerId: null,
	commentsClassName: 'feed-com-text-inner',
	commentsFullContentClassName: 'feed-com-text-inner-inner',
	currentPopupId: null,
	popupList: {}
};

BX.UserContentView.clear = function()
{
	this.viewAreaList = [];
};

BX.UserContentView.setDisplayHeight = function()
{
	this.displayHeight = document.documentElement.clientHeight;
};

BX.UserContentView.init = function(params)
{
	if (this.inited)
	{
		return;
	}

	this.inited = true;
	this.setDisplayHeight();

	window.addEventListener("scroll",  BX.throttle(function() {
		BX.UserContentView.getInViewScope();
	}, 80), { passive: true });

	window.addEventListener("resize",  BX.delegate(BX.UserContentView.setDisplayHeight, this));

	if (BX.type.isPlainObject(params))
	{

		if (BX.type.isBoolean(params.mobile))
		{
			this.mobile = params.mobile;
		}

		if (BX.type.isNotEmptyString(params.ajaxUrl))
		{
			this.ajaxUrl = params.ajaxUrl;
		}

		if (BX.type.isNotEmptyString(params.commentsContainerId))
		{
			this.commentsContainerId = params.commentsContainerId;
		}

		if (BX.type.isNotEmptyString(params.commentsClassName))
		{
			this.commentsClassName = params.commentsClassName;
		}

		if (BX.type.isNotEmptyString(params.commentsFullContentClassName))
		{
			this.commentsFullContentClassName = params.commentsFullContentClassName;
		}
	}

	if (BX.browser.SupportLocalStorage())
	{
		var viewedContent = BX.localStorage.get('viewedContent');
		if (BX.type.isArray(viewedContent))
		{
			this.viewAreaSentList = viewedContent;
		}
	}

	BX.addCustomEvent(window, 'OnUCRecordHasDrawn', BX.delegate(this.onUCRecordHasDrawn, this));
	BX.addCustomEvent(window, 'OnUCListWasShown', BX.delegate(this.OnUCListWasShown, this));

	if (this.mobile)
	{
		BX.addCustomEvent(window, 'OnUCHasBeenInitialized', BX.delegate(this.OnUCHasBeenInitializedMobile, this));
		this.sendViewAreaTimeout = 1500;
	}

	setTimeout(BX.delegate(this.sendViewAreaData, this), this.sendViewAreaTimeout);
};

BX.UserContentView.getInViewScopeNode = function(nodeId)
{
	var
		node = BX(nodeId),
		d = new Date(),
		currentTime = parseInt(d.getTime());

	if (!node)
	{
		for (var i = 0, length = this.viewAreaList.length; i < length; i++)
		{
			if (nodeId == this.viewAreaList[i])
			{
				delete this.viewAreaList[i];
			}
		}

		return;
	}

	if (this.isNodeVisibleOnScreen(node))
	{
		var xmlId = this.getXmlId(node);
		if (
			!BX.type.isBoolean(this.lastViewAreaList[nodeId])
			&& (
				!BX.type.isNotEmptyString(xmlId)
				|| !BX.util.in_array(xmlId, this.viewAreaSentList)
			)
		)
		{
			setTimeout(BX.delegate(function() {
				if (BX.UserContentView.isNodeVisibleOnScreen(this))
				{
					BX.UserContentView.setRead(this);
				}
				delete BX.UserContentView.lastViewAreaList[this.id];
			}, node), this.viewAreaTimePeriodAvg);
		}
		this.lastViewAreaList[nodeId] = true;
	}
};

BX.UserContentView.getInViewScope = function()
{
	for (var i = 0, length = this.viewAreaList.length; i < length; i++)
	{
		this.getInViewScopeNode(this.viewAreaList[i]);
	}
};

BX.UserContentView.getXmlId = function(node)
{
	return node.getAttribute("bx-content-view-xml-id");
};

BX.UserContentView.getSaveValue = function(node)
{
	return (node.getAttribute("bx-content-view-save") != 'N' ? 'Y' : 'N')
};

BX.UserContentView.setRead = function(node)
{
	var xmlId = this.getXmlId(node);
	if (xmlId.length > 0)
	{
		var found = false;
		for (var i = 0, length = this.viewAreaReadList.length; i < length; i++)
		{
			if (this.viewAreaReadList[i].xmlId == xmlId)
			{
				found = true;
				break;
			}
		}

		if (!found)
		{
			this.viewAreaReadList.push({
				xmlId: xmlId,
				save: this.getSaveValue(node)
			});

			var eventParams = {
				xmlId: xmlId
			};
			BX.onCustomEvent(window, 'BX.UserContentView.onSetRead', [eventParams]);
			if (typeof BXMobileApp != 'undefined')
			{
				BXMobileApp.onCustomEvent("BX.UserContentView.onSetRead", eventParams, true);
			}
/*BX.addClass(node, 'feed-post-contentview-read');*/
		}
	}
};

BX.UserContentView.isNodeVisibleOnScreen = function(node)
{
	var coords = node.getBoundingClientRect();
	var visibleAreaTop = parseInt(this.displayHeight/4);
	var visibleAreaBottom = parseInt(this.displayHeight * 3/4);

	return (
		(
			(
				coords.top > 0
				&& coords.top < visibleAreaBottom
			)
			|| (
				coords.bottom > visibleAreaTop
				&& coords.bottom < this.displayHeight
			)
		)
		&& (
			this.mobile
			|| !(
				(
					coords.top < visibleAreaTop
					&& coords.bottom < visibleAreaTop
				)
				|| (
					coords.top > visibleAreaBottom
					&& coords.bottom > visibleAreaBottom
				)
			)

		)
	);
};

BX.UserContentView.sendViewAreaData = function()
{
	var val = null,
		i = null,
		toSendList = [];

	for (i = 0, length = this.viewAreaReadList.length; i < length; i++)
	{
		val = this.viewAreaReadList[i];
		if (!BX.util.in_array(val.xmlId, this.viewAreaSentList))
		{
			toSendList.push(val);
		}
	}

	if (
		toSendList.length > 0
		&& this.ajaxUrl
	)
	{
		var request_data = {
			action: 'set_content_view',
			sessid: BX.bitrix_sessid(),
			site : BX.message('SITE_ID'),
			lang: BX.message('LANGUAGE_ID'),
			viewXMLIdList : toSendList
		};

		if (!!this.mobile)
		{
			request_data.mobile_action = 'set_content_view';
		}

		BX.ajax({
			url: this.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: request_data,
			onsuccess: BX.delegate(function(data) {
				if (
					BX.type.isNotEmptyString(data.SUCCESS)
					&& data.SUCCESS == "Y"
				)
				{
					for (i = 0, length = toSendList.length; i < length; i++)
					{
						this.viewAreaSentList.push(toSendList[i].xmlId);
					}
					if (BX.browser.SupportLocalStorage())
					{
						BX.localStorage.set('viewedContent', this.viewAreaSentList, 86400);
					}
				}
			}, this),
			onfailure: function(data) {
			}
		});
	}

	setTimeout(BX.delegate(this.sendViewAreaData, this), this.sendViewAreaTimeout);
};

BX.UserContentView.registerViewArea = function(nodeId, fullContentNode)
{
	if (
		nodeId.length > 0
		&& !BX.util.in_array(nodeId, this.viewAreaList)
		&& BX(nodeId)
	)
	{
		this.viewAreaList.push(nodeId);
/*
		if (fullContentNode)
		{
			this.fullContentNodeList[nodeId] = fullContentNode;
		}
*/
		this.getInViewScopeNode(nodeId);
	}
};

BX.UserContentView.onUCRecordHasDrawn = function(entityXmlId, id, data)
{
	if (
		typeof data == 'undefined'
		|| typeof data.ACTION == 'undefined'
		|| typeof id == 'undefined'
	)
	{
		return;
	}

	if (data.ACTION == 'REPLY')
	{
		var fn = BX.delegate(function() {
			this.onUCRecordHasDrawnFunc(id);
		}, this);

		var fnWeb = BX.debounce(BX.delegate(function() {
			BX.unbind(document, "mousemove", fnWeb);
			fn();
		}, this), 100, this);

		var fnMobile = BX.delegate(function() {
			BXMobileApp.UI.Page.isVisible({
				callback: function(data)
				{
					if (data && data.status == 'visible')
					{
						fn();
					}
					else
					{
						setTimeout(fnMobile, 50);
					}
				}
			});
		}, this);

		if (this.mobile)
		{
			setTimeout(fnMobile, 50);
		}
		else
		{
			BX.bind(document, "mousemove", fnWeb);
		}
	}
};

BX.UserContentView.onUCRecordHasDrawnFunc = function(id)
{
	var containerId = 'record-' + id.join('-');
	if (BX(containerId))
	{
		var viewArea = BX.findChild(BX(containerId), {
			tag: 'div',
			className: this.commentsClassName
		}, true);

		if (
			viewArea
			&& viewArea.id.length > 0
		)
		{
			var fullContentArea = BX.findChild(viewArea, {
				tag: 'div',
				className: this.commentsFullContentClassName
			});
			BX.UserContentView.registerViewArea(viewArea.id, (fullContentArea ? fullContentArea : null));
		}
	}
};

BX.UserContentView.OnUCListWasShown = function(ob, data, container)
{
	var
		fullContentArea = null,
		viewAreaList = BX.findChildren(container, {
		tag: 'div',
		className: this.commentsClassName
	}, true);

	for (var i = 0, length = viewAreaList.length; i < length; i++)
	{
		if (viewAreaList[i].id.length > 0)
		{
			fullContentArea = BX.findChild(viewAreaList[i], {
				tag: 'div',
				className: this.commentsFullContentClassName
			});
			this.registerViewArea(viewAreaList[i].id, (fullContentArea ? fullContentArea : null));
		}
	}
};

BX.UserContentView.OnUCHasBeenInitializedMobile = function(ENTITY_XML_ID, ob)
{
	this.registerViewAreaList({
		containerId: this.commentsContainerId,
		className: this.commentsClassName,
		fullContentClassName: this.commentsFullContentClassName
	});
};

BX.UserContentView.registerViewAreaList = function(params)
{
	if (
		typeof params == 'undefined'
		|| typeof params.containerId == 'undefined'
		|| typeof params.className == 'undefined'
	)
	{
		return;
	}

	if (BX(params.containerId))
	{
		var
			fullContentArea = null,
			viewAreaList = BX.findChildren(BX(params.containerId), {
			tag: 'div',
			className: params.className
		}, true);
		for (var i = 0, length = viewAreaList.length; i < length; i++)
		{
			if (viewAreaList[i].id.length > 0)
			{
				fullContentArea = BX.findChild(viewAreaList[i], {
					tag: 'div',
					className: params.fullContentClassName
				});
				this.registerViewArea(viewAreaList[i].id, (fullContentArea? fullContentArea : null));
			}
		}
	}
};

BX.UserContentView.liveUpdate = function(params)
{
	var cntNode = BX('feed-post-contentview-cnt-' + params.CONTENT_ID);
	var cntWrapNode = BX('feed-post-contentview-cnt-wrap-' + params.CONTENT_ID);

	if (cntNode && cntWrapNode)
	{
		var plusOneNode = BX.create("SPAN", {
			props : {
				className : "feed-content-view-plus-one"
			},
			style: {
				width: (cntWrapNode.clientWidth - 8)+'px',
				height: (cntWrapNode.clientHeight - 8)+'px'
			},
			html: '1'
		});

		cntWrapNode.insertBefore(
			plusOneNode,
			cntWrapNode.firstChild
		);

		setTimeout(function() {
			cntNode.innerHTML = parseInt(cntNode.innerHTML) + 1;
		}, 500);

		setTimeout(function() {
			BX.cleanNode(plusOneNode, true);
		}, 2000);
	}
};

BX.UserContentView.Counter = function()
{
	this.contentId = null;
	this.nodeId = null;
	this.node = null;
	this.popup = null;
	this.popupTimeoutId = null;
	this.popupContent = null;
	this.hiddenCountNode = null;
	this.popupContentPage = 1;
	this.popupShownIdList = [];
	this.pathToUserProfile = '';
};

BX.UserContentView.Counter.prototype.init = function(params)
{
	this.contentId = params.contentId;
	this.nodeId = params.nodeId;
	if (this.nodeId)
	{
		this.node = BX(this.nodeId);
		this.popupContent = BX.findChild(BX('bx-contentview-cnt-popup-cont-' + this.contentId), { tagName:'span', className:'bx-contentview-popup' }, true, false);
	}

	if (BX.type.isNotEmptyString(params.pathToUserProfile))
	{
		this.pathToUserProfile = params.pathToUserProfile;
	}

	if (typeof BX.PULL != 'undefined')
	{
		BX.PULL.extendWatch("CONTENTVIEW" + this.contentId);
	}

	this.popupScroll();

	BX.bind(this.node, 'mouseover' , BX.delegate(function() {
		if (
			this.popup !== null
			&& this.popup.isShown()
		)
		{
			return;
		}
		clearTimeout(this.popupTimeoutId);
		this.popupContentPage = 1;

		BX.cleanNode(this.popupContent);
		this.popupContent.appendChild(BX.create("SPAN", {
			props: {
				className: 'bx-contentview-wait'
			}
		}));

		this.popupTimeoutId = setTimeout(BX.delegate(function() {
			if (BX.UserContentView.currentPopupId == this.contentId)
			{
				return false;
			}

			if (this.popupContentPage == 1)
			{
				this.list({
					page: 1
				});
			}

			this.popupTimeoutId = setTimeout(BX.delegate(function() {
				this.openPopup();
			}, this), 400);
		}, this), 400);
	}, this));

	BX.bind(this.node, 'mouseout' , BX.delegate(function() {
		clearTimeout(this.popupTimeoutId);
	}, this));

	BX.bind(this.node, 'click' , BX.delegate(function() {
		clearTimeout(this.popupTimeoutId);

		if (this.popupContentPage == 1)
		{
			this.list({
				page: 1
			});
		}

		this.openPopup();
	}, this));
};

BX.UserContentView.Counter.prototype.list = function(params)
{
	var page = params.page;

	if (parseInt(this.node.innerHTML) == 0)
	{
		return false;
	}

	if (page == null)
	{
		page = this.popupContentPage;
	}

	if (page == 1)
	{
		this.popupShownIdList = [];
	}

	var request_data = {
		action: 'get_view_list',
		sessid: BX.bitrix_sessid(),
		site : BX.message('SITE_ID'),
		lang: BX.message('LANGUAGE_ID'),
		contentId: this.contentId,
		pathToUserProfile: this.pathToUserProfile,
		page: page
	};

	BX.ajax({
		url: BX.UserContentView.ajaxUrl,
		method: 'POST',
		dataType: 'json',
		data: request_data,
		onsuccess: BX.delegate(function(data)
		{
			if (
				parseInt(data.itemsCount) <= 0
				&& parseInt(data.hiddenCount) <= 0
			)
			{
				return false;
			}

			if (page == 1)
			{
				this.popupContent.innerHTML = '';
				var spanTag0 = document.createElement("span");
				spanTag0.className = "bx-contentview-bottom_scroll";
				this.popupContent.appendChild(spanTag0);
			}

			this.popupContentPage += 1;

			var avatarNode = null;

			for (var i=0; i<data.items.length; i++)
			{
				if (BX.util.in_array(data.items[i]['ID'], this.popupShownIdList))
				{
					continue;
				}

				this.popupShownIdList.push(data.items[i]['ID']);

				if (data.items[i]['PHOTO_SRC'].length > 0)
				{
					avatarNode = BX.create("IMG", {
						attrs: {src: data.items[i]['PHOTO_SRC']},
						props: {className: "bx-contentview-popup-avatar-img"}
					});
				}
				else
				{
					avatarNode = BX.create("IMG", {
						attrs: {src: '/bitrix/images/main/blank.gif'},
						props: {className: "bx-contentview-popup-avatar-img bx-contentview-popup-avatar-img-default"}
					});
				}

				this.popupContent.appendChild(
					BX.create("A", {
						attrs: {
							href: data.items[i]['URL'],
							target: '_blank',
							title: data.items[i]['DATE_VIEW_FORMATTED']
						},
						props: {
							className: "bx-contentview-popup-img" + (!!data.items[i]['TYPE'] ? " bx-contentview-popup-img-" + data.items[i]['TYPE'] : "")
						},
						children: [
							BX.create("SPAN", {
								props: {
									className: "bx-contentview-popup-avatar-new"
								},
								children: [
									avatarNode,
									BX.create("SPAN", {
										props: {className: "bx-contentview-popup-avatar-status-icon"}
									})
								]
							}),
							BX.create("SPAN", {
								props: {
									className: "bx-contentview-popup-name-new"
								},
								html: data.items[i]['FULL_NAME']
							})
						]
					})
				);
			}

			if (parseInt(data.hiddenCount) > 0)
			{
				BX.cleanNode(this.hiddenCountNode, true);
				this.hiddenCountNode = BX.create('SPAN', {
					props: {
						className: 'bx-contentview-popup-name-new contentview-counter-hidden'
					},
					html: BX.message('SONET_CONTENTVIEW_JS_HIDDEN_COUNT').replace('#CNT#', data.hiddenCount)
				});
				this.popupContent.appendChild(this.hiddenCountNode);
			}

			this.adjustWindow();
			this.popupScroll();

		}, this),
		onfailure: function(data) {}
	});
	return false;

};

BX.UserContentView.Counter.prototype.openPopup = function()
{
	if (parseInt(this.node.innerHTML) == 0)
	{
		return false;
	}

	if (this.popup == null)
	{
		this.popup = new BX.PopupWindow('contentview-popup-' + this.contentId, this.node, {
			lightShadow : true,
			offsetLeft: 5,
			autoHide: true,
			closeByEsc: true,
			zIndex: 2005,
			bindOptions: {position: "top"},
			events : {
				onPopupClose : function() {
					BX.UserContentView.currentPopupId = null;
				},
				onPopupDestroy : function() {  }
			},
			content : BX('bx-contentview-cnt-popup-cont-' + this.contentId),
			className: 'popup-window-contentview'
		});
		BX.UserContentView.popupList[this.contentId] = this.popup;

		this.popup.setAngle({});

		BX.bind(BX('contentview-popup-' + this.contentId), 'mouseout' , BX.delegate(function() {
			clearTimeout(this.popupTimeout);
			this.popupTimeout = setTimeout(BX.delegate(function() {
				this.popup.close();
			}, this), 1000);
		}, this));

		BX.bind(BX('contentview-popup-' + this.contentId), 'mouseover' , BX.delegate(function() {
			clearTimeout(this.popupTimeout);
		}, this));
	}

	if (BX.UserContentView.currentPopupId != null)
	{
		BX.UserContentView.popupList[BX.UserContentView.currentPopupId].close();
	}

	BX.UserContentView.currentPopupId = this.contentId;

	this.popup.show();
	this.adjustWindow();

};

BX.UserContentView.Counter.prototype.popupScroll = function()
{
	BX.bind(this.popupContent, 'scroll' , BX.delegate(function() {
		var _this = BX.proxy_context;
		if (_this.scrollTop > (_this.scrollHeight - _this.offsetHeight) / 1.5)
		{
			this.list({
				page: null
			});
			BX.unbindAll(_this);
		}
	}, this));
};

BX.UserContentView.Counter.prototype.adjustWindow = function()
{
	if (this.popup != null)
	{
		this.popup.bindOptions.forceBindPosition = true;
		this.popup.adjustPosition();
		this.popup.bindOptions.forceBindPosition = false;
	}
};

BX.addCustomEvent(window, "BX.UserContentView.onInitCall", BX.delegate(BX.UserContentView.init, BX.UserContentView));
BX.addCustomEvent(window, "BX.UserContentView.onRegisterViewAreaListCall", BX.delegate(BX.UserContentView.registerViewAreaList, BX.UserContentView));
BX.addCustomEvent(window, "BX.UserContentView.onClearCall", BX.delegate(BX.UserContentView.clear, BX.UserContentView));

BX.addCustomEvent("onPullEvent-contentview", function(command, params) {
	if (command == 'add')
	{
		BX.UserContentView.liveUpdate(params);
	}
});

})();