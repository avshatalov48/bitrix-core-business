(function() {

var BX = window.BX;
if (BX.UserContentView)
{
	return;
}

BX.UserContentView = {
	mobile: false,
	context: '',
	pathToUserProfile: '',
	inited: false,
	viewAreaSentList: [],
	viewAreaTimePeriodAvg: 1500,
	viewAreaTimePeriodAvgMobile: 50,
	sendViewAreaTimeout: 5000,
	failedSetTimeout: 10*60*1000,
	commentsContainerId: null,
	commentsClassName: 'feed-com-text-inner',
	commentsFullContentClassName: 'feed-com-text-inner-inner',
	currentPopupId: null,
	popupList: {},
	toSendList: [],
	ignoreCurrentUserLive: [],
	viewAreaReadList: {},

	observer: null,
	checkerMap: null,
	viewAreaMap: null,

	ajaxSent: false,
	failedAjaxCounter: 0,
	failedAjaxLimit: 10,

	preventRead: false,

	viewedContentKey: 'viewedContentV2',
};

BX.UserContentView.clear = function()
{
	this.viewAreaMap = new WeakMap();
};

BX.UserContentView.init = function(params)
{
	if (this.inited)
	{
		return;
	}

	if ((BX.message('USER_ID') ? parseInt(BX.message('USER_ID')) : 0) <= 0)
	{
		return;
	}

	if (BX.type.isPlainObject(params))
	{
		if (BX.type.isBoolean(params.mobile))
		{
			this.mobile = params.mobile;
		}

		if (BX.type.isNotEmptyString(params.context))
		{
			this.context = params.context;
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

	var observerOptions = {
		rootMargin: (this.mobile ? '0%' : '-10% 0% -10% 0%'),
		threshold: 0.10
	};

	this.observer = new IntersectionObserver(this.onIntersection.bind(this), observerOptions);
	this.checkerMap = new WeakMap();
	this.viewAreaMap = new WeakMap();
	this.viewAreaReadList = {};

	this.inited = true;

	if (BX.browser.SupportLocalStorage())
	{
		var viewedContent = BX.localStorage.get(this.viewedContentKey);
		if (BX.type.isArray(viewedContent))
		{
			this.viewAreaSentList = viewedContent;
		}
	}

	BX.addCustomEvent(window, 'OnUCRecordHasDrawn', this.onUCRecordHasDrawn.bind(this));
	BX.addCustomEvent(window, 'OnUCListWasShown', this.OnUCListWasShown.bind(this));

	if (this.mobile)
	{
		BX.addCustomEvent('OnUCHasBeenInitialized', this.OnUCHasBeenInitializedMobile.bind(this));
		BX.addCustomEvent('BX.UserContentView.onSetPreventNextPage', this.OnSetPreventNextPage.bind(this));
		this.sendViewAreaTimeout = 1500;
	}

	setTimeout(this.sendViewAreaData.bind(this), this.sendViewAreaTimeout);
};


BX.UserContentView.getXmlId = function(node)
{
	return node.getAttribute('bx-content-view-xml-id');
};

BX.UserContentView.getSaveValue = function(node)
{
	var signedKey = node.getAttribute('bx-content-view-key-signed');

	return {
		signedKey: (signedKey ? signedKey : ''),
		value: (node.getAttribute('bx-content-view-save') != 'N' ? 'Y' : 'N'),
	}
};

BX.UserContentView.getReadStatus = function(xmlId)
{
	return(
		BX.type.isNotEmptyString(xmlId)
		&& BX(xmlId)
		&& this.viewAreaReadList.hasOwnProperty(xmlId)
	);
};

BX.UserContentView.setRead = function(node)
{
	var xmlId = this.getXmlId(node);
	if (
		BX.type.isNotEmptyString(xmlId)
		&& !this.getReadStatus(xmlId)
	)
	{
		this.viewAreaReadList[xmlId] = this.getSaveValue(node);

		var eventParams = {
			xmlId: xmlId
		};

		BX.onCustomEvent(window, 'BX.UserContentView.onSetRead', [eventParams]);
		if (
			typeof BXMobileApp != 'undefined'
			&& BX.type.isNotEmptyObject(BXMobileApp)
		)
		{
			BXMobileApp.onCustomEvent('BX.UserContentView.onSetRead', eventParams, true);
		}
	}
};

BX.UserContentView.sendViewAreaData = function()
{
	this.toSendList = [];

	for (var xmlId in this.viewAreaReadList)
	{
		if (
			!this.viewAreaReadList.hasOwnProperty(xmlId)
			|| BX.util.in_array(xmlId, this.viewAreaSentList)
		)
		{
			continue;
		}

		this.toSendList.push({
			xmlId: xmlId,
			save: this.viewAreaReadList[xmlId].value,
			signedKey: this.viewAreaReadList[xmlId].signedKey,
		});
	}

	if (this.failedAjaxCounter >= this.failedAjaxLimit)
	{
		setTimeout(function() {
			this.failedAjaxCounter = 0;
		}.bind(this), this.failedSetTimeout);
	}
	else if (
		this.toSendList.length > 0
		&& !this.ajaxSent
	)
	{
		this.ajaxSent = true;

		var ajaxApi = (!!this.mobile ? new MobileAjaxWrapper : BX.ajax);
		ajaxApi.runAction('socialnetwork.api.contentview.set', {
			data: {
				params: {
					viewXMLIdList: this.toSendList,
					context: this.context
				}
			}
		}).then(function(response) {
			this.ajaxSent = false;
			this.failedAjaxCounter = 0;

			this.success(response.data);
		}.bind(this), function(response) {
			this.ajaxSent = false;
			this.failedAjaxCounter++;
		}.bind(this));
	}

	setTimeout(this.sendViewAreaData.bind(this), this.sendViewAreaTimeout);
};

BX.UserContentView.success = function(data)
{
	if (
		BX.type.isNotEmptyString(data.SUCCESS)
		&& data.SUCCESS == 'Y'
	)
	{
		for (i = 0, length = this.toSendList.length; i < length; i++)
		{
			this.viewAreaSentList.push(this.toSendList[i].xmlId);
		}
		if (BX.browser.SupportLocalStorage())
		{
			BX.localStorage.set(this.viewedContentKey, this.viewAreaSentList, 86400);
		}
	}
};

BX.UserContentView.registerViewArea = function(nodeId)
{
	if (
		BX.type.isNotEmptyString(nodeId)
		&& BX(nodeId)
		&& this.viewAreaMap
		&& !this.viewAreaMap.has(BX(nodeId))
	)
	{
		this.observer.observe(BX(nodeId));
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
		var fn = function() {
			this.onUCRecordHasDrawnFunc(id);
		}.bind(this);

		var fnWeb = BX.debounce(function() {
			BX.unbind(document, 'mousemove', fnWeb);
			fn();
		}.bind(this), 100, this);

		var fnMobile = function() {
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
		}.bind(this);

		if (this.mobile)
		{
			setTimeout(fnMobile, 50);
		}
		else
		{
			BX.bind(document, 'mousemove', fnWeb);
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
			&& BX.type.isNotEmptyString(viewArea.id)
		)
		{
			BX.UserContentView.registerViewArea(viewArea.id);
		}
	}
};

BX.UserContentView.OnUCListWasShown = function(ob, data, container)
{
	var viewAreaCollection = BX.findChildren(container, {
			tag: 'div',
			className: this.commentsClassName
		}, true);

	for (var i in viewAreaCollection)
	{
		if (!viewAreaCollection.hasOwnProperty(i))
		{
			continue;
		}

		if (BX.type.isNotEmptyString(viewAreaCollection[i].id))
		{
			this.registerViewArea(viewAreaCollection[i].id);
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

BX.UserContentView.OnSetPreventNextPage = function(status)
{
	this.preventRead = !!status;
};

BX.UserContentView.registerViewAreaList = function(params)
{
	if (
		!BX.type.isNotEmptyObject(params)
		|| !BX.type.isNotEmptyString(params.containerId)
		|| !BX.type.isNotEmptyString(params.className)
		|| !BX(params.containerId)
	)
	{
		return;
	}

	var viewAreaCollection = BX.findChildren(BX(params.containerId), {
		tag: 'div',
		className: params.className
	}, true);

	for (var i in viewAreaCollection)
	{
		if (!viewAreaCollection.hasOwnProperty(i))
		{
			continue;
		}

		if (BX.type.isNotEmptyString(viewAreaCollection[i].id))
		{
			this.registerViewArea(viewAreaCollection[i].id);
		}
	}
};

BX.UserContentView.liveUpdate = function(params)
{
	if (
		BX.type.isNotEmptyString(params.CONTENT_ID)
		&& BX.util.in_array(params.CONTENT_ID, BX.UserContentView.ignoreCurrentUserLive)
		&& typeof params.USER_ID != 'undefined'
		&& parseInt(params.USER_ID) > 0
		&& parseInt(params.USER_ID) == parseInt(BX.message('USER_ID'))
	)
	{
		return;
	}

	var cntNode = BX('feed-post-contentview-cnt-' + params.CONTENT_ID);
	var cntWrapNode = BX('feed-post-contentview-cnt-wrap-' + params.CONTENT_ID);

	if (cntNode && cntWrapNode)
	{
		const currentViews = parseInt(cntNode.innerHTML);
		const totalViews = parseInt(params.TOTAL_VIEWS ?? 0);

		if (currentViews === totalViews)
		{
			return;
		}

		var plusOneNode = BX.create('SPAN', {
			props : {
				className: 'feed-content-view-plus-one',
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
			cntNode.innerHTML = totalViews;
		}, 500);

		setTimeout(function() {
			BX.cleanNode(plusOneNode, true);
		}, 2000);
	}
};

/**
* @param {IntersectionObserverEntry[]} entries
*/
BX.UserContentView.onIntersection = function(entries)
{
	if (this.preventRead)
	{
		return;
	}

	entries.forEach(function (entry) {
		if (entry.isIntersecting)
		{
			var xmlId = this.getXmlId(entry.target);
			if (
				!BX.type.isNotEmptyString(xmlId)
				|| this.getReadStatus(xmlId)
			)
			{
				return;
			}

			if (!this.checkerMap.has(entry.target))
			{
				this.checkerMap.set(entry.target, true);
			}

			setTimeout(function() {
				if (BX.UserContentView.checkerMap.has(this))
				{
					BX.UserContentView.setRead(this);
				}
			}.bind(entry.target), (this.mobile ? this.viewAreaTimePeriodAvgMobile : this.viewAreaTimePeriodAvg))
		}
		else if (this.checkerMap.has(entry.target))
		{
			this.checkerMap.delete(entry.target);
		}

	}.bind(this));
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
	this.mouseLeaveTimeoutId = null;
	this.listXHR = null;
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

	if (
		BX.type.isNotEmptyString(params.isSet)
		&& params.isSet == 'Y'
		&& !BX.util.in_array(this.contentId, BX.UserContentView.ignoreCurrentUserLive)
	)
	{
		BX.UserContentView.ignoreCurrentUserLive.push(this.contentId);
	}

	if (typeof BX.PULL != 'undefined')
	{
		BX.PULL.extendWatch('CONTENTVIEW' + this.contentId);
	}

	this.popupScroll();

	BX.bind(this.node, 'mouseover' , function() {
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
		this.popupContent.appendChild(BX.create('SPAN', {
			props: {
				className: 'bx-contentview-wait',
			},
		}));

		this.popupTimeoutId = setTimeout(function() {
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

			this.popupTimeoutId = setTimeout(function() {
				this.openPopup();
			}.bind(this), 400);

		}.bind(this), 400);

	}.bind(this));

	this.node.addEventListener('mouseout', function() {
		clearTimeout(this.popupTimeoutId);
	}.bind(this));

	this.node.addEventListener('click', function() {
		clearTimeout(this.popupTimeoutId);
		if (this.popupContentPage == 1)
		{
			this.list({
				page: 1
			});
		}

		this.openPopup();
	}.bind(this));
};

BX.UserContentView.Counter.prototype.list = function(params)
{
	if (this.listXHR)
	{
		this.listXHR.abort();
	}

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

	BX.ajax.runAction('socialnetwork.api.contentview.getlist', {
		data: {
			params: {
				contentId: this.contentId,
				pathToUserProfile: this.pathToUserProfile,
				page: page
			}
		},
		onrequeststart: function(requestXhr) {
			this.listXHR = requestXhr;
		}.bind(this)
	}).then(function(response) {
		var data  = response.data;

		if (
			!data
			|| (
				parseInt(data.itemsCount) <= 0
				&& parseInt(data.hiddenCount) <= 0
			)
		)
		{
			return false;
		}

		if (page == 1)
		{
			this.popupContent.innerHTML = '';
		}

		this.popupContentPage += 1;

		var avatarNode = null;

		for (var i in data.items)
		{
			if (
				!data.items.hasOwnProperty(i)
				|| BX.util.in_array(data.items[i]['ID'], this.popupShownIdList)
			)
			{
				continue;
			}

			this.popupShownIdList.push(data.items[i]['ID']);

			if (BX.type.isNotEmptyString(data.items[i]['PHOTO_SRC']))
			{
				avatarNode = BX.create('IMG', {
					attrs: {
						src: encodeURI(data.items[i]['PHOTO_SRC']),
					},
					props: {
						className: 'bx-contentview-popup-avatar-img',
					},
				});
			}
			else
			{
				avatarNode = BX.create('IMG', {
					attrs: {
						src: '/bitrix/images/main/blank.gif',
					},
					props: {
						className: 'bx-contentview-popup-avatar-img bx-contentview-popup-avatar-img-default',
					},
				});
			}

			this.popupContent.appendChild(
				BX.create('A', {
					attrs: {
						href: data.items[i]['URL'],
						target: '_blank',
						title: data.items[i]['DATE_VIEW_FORMATTED'],
					},
					props: {
						className: 'bx-contentview-popup-img' + (
							!!data.items[i]['TYPE']
								? ' bx-contentview-popup-img-' + data.items[i]['TYPE']
								: ''
						),
					},
					children: [
						BX.create('SPAN', {
							props: {
								className: 'bx-contentview-popup-avatar-new',
							},
							children: [
								avatarNode,
								BX.create('SPAN', {
									props: {
										className: 'bx-contentview-popup-avatar-status-icon',
									},
								}),
							],
						}),
						BX.create('SPAN', {
							props: {
								className: 'bx-contentview-popup-name-new',
							},
							html: data.items[i]['FULL_NAME'],
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
					className: 'bx-contentview-popup-name-new contentview-counter-hidden',
				},
				html: BX.message('SONET_CONTENTVIEW_JS_HIDDEN_COUNT').replace('#CNT#', data.hiddenCount),
			});
			this.popupContent.appendChild(this.hiddenCountNode);
		}

		this.adjustWindow();
		this.popupScroll();
	}.bind(this), function(response) {

	}.bind(this));

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
			offsetLeft: -22,
			autoHide: true,
			closeByEsc: true,
			zIndex: 2005,
			bindOptions: {
				position: 'top'
			},
			animationOptions: {
				show: {
					type: 'opacity-transform'
				},
				close: {
					type: 'opacity'
				}
			},
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

		BX.bind(BX('contentview-popup-' + this.contentId), 'mouseout' , function() {
			clearTimeout(this.popupTimeout);
			this.popupTimeout = setTimeout(function() {
				this.popup.close();
			}.bind(this), 1000);
		}.bind(this));

		BX.bind(BX('contentview-popup-' + this.contentId), 'mouseover' , function() {
			clearTimeout(this.popupTimeout);
			clearTimeout(this.mouseLeaveTimeoutId);
		}.bind(this));

		BX.bind(this.node, 'mouseleave' , function() {
			this.mouseLeaveTimeoutId = setTimeout(function() {
				this.popup.close();
			}.bind(this), 1000);
		}.bind(this));
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

// used only in mobile livefeed so far
BX.addCustomEvent(window, 'BX.UserContentView.onInitCall', BX.UserContentView.init.bind(BX.UserContentView));
BX.addCustomEvent(window, 'BX.UserContentView.onRegisterViewAreaListCall', BX.UserContentView.registerViewAreaList.bind(BX.UserContentView));
BX.addCustomEvent(window, 'BX.UserContentView.onClearCall', BX.UserContentView.clear.bind(BX.UserContentView));

BX.addCustomEvent('onPullEvent-contentview', function(command, params) {
	if (command === 'add')
	{
		BX.UserContentView.liveUpdate(params);
	}
});

})();