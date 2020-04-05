/**
 * Class for construct window
 * @param params
 * @constructor
 */

;(function (window)
{
	if (window.BX.MessengerWindow) return;

	var BX = window.BX;

	var MessengerWindow = function ()
	{
		this.popupConfirm = null;

		this.BXIM = {};
		this.popup = null;
		this.backgroundSelector = null;
		this.content = null;
		this.contentFullWindow = true;
		this.contentBodyWindow = false;
		this.contentMenu = null;
		this.contentAvatar = null;
		this.contentTab = null;
		this.contentTabContent = null;

		this.currentTab = '';
		this.currentTabTarget = '';
		this.lastTab = '';
		this.lastTabTarget = '';

		this.tabItems = {};
		this.tabRedrawTimeout = null;
		this.userInfo = {id: 0, name: '', gender: 'M', avatar: '', profile: ''};

		this.inited = false;

		/* sizes */
		this.width = 914;
		this.height = 454;
		this.initWidth = 914;
		this.initHeight = 454;
		this.minWidth = 515;
		this.minHeight = 384;
	};

	MessengerWindow.prototype.init = function (params)
	{
		params = params || {};
		if (this.inited)
		{
			return true;
		}
		this.inited = true;

		this.BXIM = params.bxim || {};
		this.context = params.context || "DESKTOP";
		this.design = params.design || "DESKTOP";

		if (this.context == 'FULLSCREEN' || this.context == 'POPUP-FULLSCREEN' || this.context == 'PAGE' || this.context == 'DIALOG' || this.context == 'LINES')
		{
			if (this.context == 'FULLSCREEN' || this.context == 'PAGE' || this.context == 'POPUP-FULLSCREEN')
			{
				this.contentBodyWindow = true;
			}



			this.popup = BX('im-workarea-popup');
			this.popupBackground = this.popup;
			this.content = BX('im-workarea-content');
			this.apps = BX('im-workarea-apps');
			this.backgroundSelector = BX('im-workarea-backgound-selector');

			if (!this.content)
			{
				this.popup = BX('workarea-popup');
				this.content = BX('workarea-content');
			}
			if (this.popup)
			{
				BX.addClass(this.popup, 'bx-im-fullscreen-closed');
				BX.bind(this.popup, 'click', BX.delegate(this.closePopup, this));
			}
			else
			{
				this.popupBackground = BX('im-workarea-popup-bg');
			}


			if (this.context == 'PAGE')
			{
				var scrollSize = window.innerWidth - document.documentElement.clientWidth;
				BX.onCustomEvent(window, 'onMessengerWindowBodyOverflow', [this, scrollSize]);
				BX.addClass(document.body, 'bx-im-fullscreen-block-scroll');
			}

			if (this.backgroundSelector)
			{
				BX.bind(this.backgroundSelector.parentNode, 'click', BX.delegate(BX.PreventDefault, this));
				BX.bind(this.backgroundSelector, 'change', BX.delegate(function(e){
					this.backgroundChange();
					BX.localStorage.set('imFullscreenBackground', this.backgroundSelector.value, 3000000);
					return BX.PreventDefault(e);
				}, this));

				var imFullscreenBackground = BX.localStorage.get('imFullscreenBackground');
				if (imFullscreenBackground !== null)
				{
					this.backgroundSelector.value = imFullscreenBackground;
				}
				this.backgroundChange();
			}
			if (!this.content)
			{
				this.content = BX.create('div', {attrs: {className: 'bx-desktop'}});
				document.body.insertBefore(this.content, document.body.firstChild);
			}
			if (this.apps)
			{
				BX.bind(this.apps, 'click', BX.delegate(BX.MessengerCommon.preventDefault, this));
			}

			BX.bind(this.content, 'click', BX.delegate(BX.MessengerCommon.preventDefault, this));
			if (!BX.hasClass(this.content, 'bx-desktop'))
			{
				BX.addClass(this.content, 'bx-desktop')
			}

			if (this.context == 'LINES' || this.context == 'DIALOG')
			{
				this.contentFullWindow = false;
			}
			else if (this.context != 'POPUP-FULLSCREEN')
			{
				if (this.content.offsetWidth < this.minWidth)
				{
					BX.style(this.content, 'width', this.minWidth+'px');
				}
			}
		}
		else
		{
			this.content = BX.create('div');
			document.body.insertBefore(this.content, document.body.firstChild);
		}

		if (BX.desktop && BX.desktop.apiReady && !BX.desktop.enableInVersion(29))
		{
			BX.PULL.tryConnectSet(null, false);
			BX.desktop.notSupported();
			BX.desktop.apiReady = false;
			BX.desktop.disableLogin = true;

			return false;
		}

		if (BX.browser.SupportLocalStorage())
		{
			BX.addCustomEvent(window, "onLocalStorageSet", BX.delegate(this.storageSet, this));
		}
		if (BX.MessengerCommon.isDesktop())
		{
			BX.MessengerWindow.addTab({
				id: 'exit',
				title: BX.message('BXD_LOGOUT'),
				order: 1100,
				target: false,
				events: {
					open: BX.delegate(function(){
						this.logout(false, 'exit_tab');
					}, this)
				}
			});
		}
		BX.bind(window, "resize", BX.delegate(function(){
			this.adjustSize();
		}, this));
	}

	MessengerWindow.prototype.browse = function(url)
	{
		if (BX.MessengerCommon.isDesktop())
		{
			BX.desktop.browse(url);
		}
		else if (this.context == 'POPUP-FULLSCREEN')
		{
			location.href = url;
		}
		else
		{
			window.open(url,'_blank');
		}
	};

	MessengerWindow.prototype.getCurrentUrl = function ()
	{
		return document.location.protocol+'//'+document.location.hostname+(document.location.port == ''?'':':'+document.location.port)
	}

	MessengerWindow.prototype.windowReload = function ()
	{
		location.reload();
	}

	MessengerWindow.prototype.logout = function (terminate, reason, skipCheck)
	{
		if (typeof(BXDesktopSystem) == "undefined" || typeof(BXDesktopWindow) == "undefined")
		{
			location.href = '/?logout=yes';
			return true;
		}

		if (BX.desktop && BX.desktop.apiReady)
		{
			BX.desktop.logout(terminate, reason, skipCheck);
		}

		return true;
	}

	MessengerWindow.prototype.adjustSize = function (width, height)
	{
		if (this.context == 'POPUP-FULLSCREEN' && BX.hasClass(this.popup, 'bx-im-fullscreen-closed'))
		{
			return false;
		}
		var innerWidth = 0;
		var innerHeight = 0;

		var setFirstHeight = false;
		if (this.contentBodyWindow)
		{
			if (!this.popupFullscreenSizeTop && !this.popupFullscreenSizeBottom)
			{
				var popupPos = BX.pos(BX.MessengerWindow.content.parentNode);
				this.popupFullscreenSizeTop = popupPos.top;
				this.popupFullscreenSizeBottom = window.innerHeight-popupPos.top-popupPos.height;
			}
			innerHeight = Math.max(window.innerHeight-this.popupFullscreenSizeTop-this.popupFullscreenSizeBottom, this.initHeight);
			innerWidth = BX.MessengerWindow.content.offsetWidth;
		}
		else if (this.contentFullWindow)
		{
			innerWidth = window.innerWidth;
			innerHeight = window.innerHeight;
		}
		else
		{
			try {
				BX.style(document.body, 'height', window.innerHeight+'px');
			}
			catch (e)
			{
				setTimeout(function(){
					BX.MessengerWindow.adjustSize(width, height);
				}, 500);
			}
			innerWidth = Math.max(this.content.offsetWidth, this.minWidth);
			innerHeight = Math.max(this.content.offsetHeight, this.minHeight);
		}

		if (BX.desktop && BX.desktop.apiReady && (!width || !height) && (innerHeight < this.minHeight || innerWidth < this.minWidth))
		{
			BXDesktopWindow.SetProperty("clientSize", { Width: this.width, Height: this.height});
			return false;
		}

		if (this.context == 'POPUP-FULLSCREEN' && BX.browser.IsMobile())
		{
			this.height = this.initHeight;
			this.width = this.initWidth;
		}
		else
		{
			BX.addClass(this.content, 'bx-im-fullscreen-adaptive');
			this.width = width? width: innerWidth;
			this.height = height? height: innerHeight;
		}

		BX.style(this.contentMenu, 'height', this.height+'px');
		BX.style(this.contentTabContent, 'height', this.height+'px');
		BX.style(this.content, 'max-width', window.innerWidth+'px');

		return true;
	}

	MessengerWindow.prototype.openConfirm = function(text, buttons, modal)
	{
		if (this.popupConfirm != null)
			this.popupConfirm.destroy();

		if (typeof(text) == "object")
			text = '<div class="bx-desktop-confirm-title">'+text.title+'</div>'+text.message;

		modal = modal !== false;
		if (typeof(buttons) == "undefined" || typeof(buttons) == "object" && buttons.length <= 0)
		{
			buttons = [new BX.PopupWindowButton({
				text : BX.message('BXD_CONFIRM_CLOSE'),
				className : "popup-window-button-decline",
				events : { click : function(e) { this.popupWindow.close(); BX.PreventDefault(e) } }
			})];
		}
		this.popupConfirm = new BX.PopupWindow('bx-desktop-confirm', null, {
			zIndex: 200,
			autoHide: buttons === false,
			buttons : buttons,
			closeByEsc: buttons === false,
			overlay : modal,
			events : { onPopupClose : function() { this.destroy() }, onPopupDestroy : BX.delegate(function() { this.popupConfirm = null }, this)},
			content : BX.create("div", { props : { className : (buttons === false? " bx-desktop-confirm-without-buttons": "bx-desktop-confirm") }, html: text})
		});
		this.popupConfirm.show();
		BX.bind(this.popupConfirm.popupContainer, "click", BX.PreventDefault);
		BX.bind(this.popupConfirm.contentContainer, "click", BX.PreventDefault);
		BX.bind(this.popupConfirm.overlay.element, "click", BX.PreventDefault);

		return true;
	};

	MessengerWindow.prototype.addSeparator = function (params)
	{
		params.type = 'separator';
		params.id = 'sep'+(+new Date())
		this.tabItems[params.id] = params;

		this.drawTabs();
	}

	MessengerWindow.prototype.addTab = function (params)
	{
		if (!params || !params.id || !params.title)
			return false;

		if (!params.order)
			params.order = 500;

		params.hide = params.hide? true: false;

		if (parseInt(params.badge) > 0)
		{
			params.badge = parseInt(params.badge);
		}
		else
		{
			params.badge = 0;
		}

		if (!params.initContent || !BX.type.isDomNode(params.initContent))
			params.initContent = null;

		if (!params.events)
			params.events = {};

		if (typeof(params.target) == 'undefined')
			params.target = params.id;

		if (!params.events.open)
			params.events.open = function() {}

		if (!params.events.close)
			params.events.close = function() {}

		if (!params.events.init)
			params.events.init = function() {}

		params.type = 'item';

		this.tabItems[params.id] = params;

		this.drawTabs();
	}

	MessengerWindow.prototype.hideTab = function (id)
	{
		if (!id || !this.tabItems[id])
			return false;

		this.tabItems[id].hide = true;

		this.drawTabs();
	}

	MessengerWindow.prototype.showTab = function (id)
	{
		if (!id || !this.tabItems[id])
			return false;

		this.tabItems[id].hide = false;

		this.drawTabs();
	}

	MessengerWindow.prototype.existsTab = function (id)
	{
		return this.tabItems[id];
	}

	MessengerWindow.prototype.drawTabs = function (force)
	{
		if (!force)
		{
			clearTimeout(this.tabRedrawTimeout);
			this.tabRedrawTimeout = setTimeout(BX.delegate(function(){
				this.drawTabs(true);
			}, this), 100);

			return true;
		}
		if (!this.contentTabContent)
		{
			if (!this.drawAppearance())
				return false;
		}

		this.contentTab.innerHTML = '';
		var arTabs = BX.util.objectSort(this.tabItems, 'order', 'asc');
		for (var i = 0; i < arTabs.length; i++)
		{
			this.drawTab(arTabs[i]);
		}
		BX.onCustomEvent(this, 'OnDesktopTabsInit');
		if (this.currentTab == '')
		{
			if (arTabs[0].id == 'exit')
			{
				if (typeof(arTabs[1]) != 'undefined')
				{
					this.changeTab(arTabs[1].id);
				}
			}
			else
			{
				this.changeTab(arTabs[0].id);
			}
		}

		if (BX.desktop && BX.desktop.apiReady)
		{
			BX.desktop.updateTabBadge();
		}

		return true;
	}

	MessengerWindow.prototype.drawTab = function (params)
	{
		if (params.type == 'separator')
		{
			this.contentTab.appendChild(
				BX.create('div', { attrs : { 'data-id' : params.id, id: 'bx-desktop-sep-'+params.id}, props : { className : "bx-desktop-separator"}})
			);
		}
		else
		{
			this.contentTab.appendChild(
				BX.create('div', { attrs : { 'data-id' : params.id, id: 'bx-desktop-tab-'+params.id, title: params.title}, props : { className : "bx-desktop-tab bx-desktop-tab-"+params.id+(this.currentTab == params.id? ' bx-desktop-tab-active': '')+(params.hide? ' bx-desktop-tab-hide': '') }, children: [
					BX.create('span', { props : { className : "bx-desktop-tab-counter" }, html: params.badge > 0? '<span class="bx-desktop-tab-counter-digit">'+(params.badge > 50? '50+': params.badge)+'</span>': ''}),
					BX.create('div', { props : { className : "bx-desktop-tab-icon bx-desktop-tab-icon-"+params.id }})
				]})
			);

			if (!BX('bx-desktop-tab-content-'+params.id) && params.id == params.target)
			{
				this.contentTabContent.appendChild(
					BX.create('div', { attrs : { 'data-id': params.id, id: 'bx-desktop-tab-content-'+params.id}, props : { className : "bx-desktop-tab-content bx-desktop-tab-content-"+params.id+(this.currentTab == params.id? ' bx-desktop-tab-content-active': '') }, children: params.initContent? [params.initContent]: []})
				);
				params.events.init();
			}
		}
		return true;
	}

	MessengerWindow.prototype.drawAppearance = function ()
	{
		if (!this.content)
			return false;

		this.content.innerHTML = '';
		this.content.appendChild(
			this.contentBox = BX.create("div", { props : { className : 'bx-desktop-appearance'}, style: {minHeight: this.minHeight+'px'}, children: [
				this.contentMenu = BX.create("div", { props : { className : 'bx-desktop-appearance-menu'}, children: [
					this.contentAvatar = BX.create("div", { props : { className : 'bx-desktop-appearance-avatar'}}),
					this.contentTab = BX.create("div", { props : { className : 'bx-desktop-appearance-tab'}})
				]}),
				this.contentTabContent = BX.create("div", { props : { className : 'bx-desktop-appearance-content'}})
			]})
		);

		BX.bindDelegate(this.contentTab, "click", {className: 'bx-desktop-tab'}, BX.delegate(function(event){
			this.changeTab(event, false);
			BX.PreventDefault(event);
		}, this));
		this.adjustSize();

		BX.onCustomEvent(window, 'onMessengerWindowInit', [this, this.BXIM]);

		return true;
	}

	MessengerWindow.prototype.changeTab = function (tabId, force)
	{
		force = typeof(force) == 'undefined'? true: force;

		if (typeof(tabId) == 'object')
		{
			if (!BX.proxy_context)
			{
				return false;
			}
			tabId = BX.proxy_context.getAttribute('data-id');
		}

		if (!this.tabItems[tabId])
			return false;

		if (this.tabItems[tabId].target)
		{
			var fireEvent = false;
			if (!force || this.currentTab != tabId)
			{
				this.lastTab = this.currentTab;
				this.lastTabTarget = this.currentTabTarget;
				this.currentTab = this.tabItems[tabId].id;
				this.currentTabTarget = this.tabItems[tabId].target;

				fireEvent = true;
			}

			if (BX('bx-desktop-tab-'+this.lastTab))
				BX.removeClass(BX('bx-desktop-tab-'+this.lastTab), 'bx-desktop-tab-active');

			if (BX('bx-desktop-tab-'+tabId))
				BX.addClass(BX('bx-desktop-tab-'+tabId), 'bx-desktop-tab-active');

			if (BX('bx-desktop-tab-content-'+this.lastTab))
			{
				BX.removeClass(BX('bx-desktop-tab-content-'+this.lastTab), 'bx-desktop-tab-content-active');
			}
			else if (BX('bx-desktop-tab-content-'+this.lastTabTarget))
			{
				BX.removeClass(BX('bx-desktop-tab-content-'+this.lastTabTarget), 'bx-desktop-tab-content-active');
			}

			if (BX('bx-desktop-tab-content-'+this.currentTab))
			{
				BX.addClass(BX('bx-desktop-tab-content-'+this.currentTab), 'bx-desktop-tab-content-active');
			}
			else if (BX('bx-desktop-tab-content-'+this.currentTabTarget))
			{
				BX.addClass(BX('bx-desktop-tab-content-'+this.currentTabTarget), 'bx-desktop-tab-content-active');
			}

			if (fireEvent)
			{
				if (this.tabItems[this.lastTab])
				{
					this.tabItems[this.lastTab].events.close();
				}

				if (this.tabItems[this.currentTab])
				{
					BX.onCustomEvent(this, 'OnDesktopTabChange', [this.currentTab, this.lastTab]);
					this.tabItems[this.currentTab].events.open();
				}

			}
		}
		else
		{
			this.tabItems[tabId].events.open();
		}

		return true;
	}

	MessengerWindow.prototype.closeTab = function (tabId)
	{
		tabId = tabId || this.getCurrentTab();

		if (!this.tabItems[tabId] || this.getCurrentTab() != tabId)
			return false;

		if (this.tabItems[tabId].target != this.currentTabTarget)
		{
			this.changeTab(tabId, false);
		}
		else
		{
			if (BX('bx-desktop-tab-'+this.currentTab))
				BX.removeClass(BX('bx-desktop-tab-'+this.currentTab), 'bx-desktop-tab-active');

			if (BX('bx-desktop-tab-'+this.lastTab))
				BX.addClass(BX('bx-desktop-tab-'+this.lastTab), 'bx-desktop-tab-active');

			var lastTab = this.lastTab;
			this.lastTab = this.currentTab;
			this.currentTab = lastTab;
		}
	}

	MessengerWindow.prototype.setTabBadge = function (tabId, value)
	{
		if (!this.tabItems[tabId])
			return false;

		value = parseInt(value);
		this.tabItems[tabId].badge = value>0? value: 0;

		if (value > 50)
			value = '50+';

		if (BX('bx-desktop-tab-'+tabId))
		{
			var counter = BX.findChild(BX('bx-desktop-tab-'+tabId), {className : "bx-desktop-tab-counter"}, true);
			if (counter)
				counter.innerHTML = value? '<span class="bx-desktop-tab-counter-digit">'+value+'</span>': '';
		}

		if (BX.desktop && BX.desktop.apiReady)
		{
			BX.desktop.updateTabBadge();
		}
	}

	MessengerWindow.prototype.setTabContent = function (tabId, content)
	{
		if (!this.tabItems[tabId])
			return false;

		if (BX('bx-desktop-tab-content-'+tabId))
		{
			if (BX.type.isDomNode(content))
			{
				BX('bx-desktop-tab-content-'+tabId).innerHTML = '';
				BX('bx-desktop-tab-content-'+tabId).appendChild(content);
			}
			else
			{
				BX('bx-desktop-tab-content-'+tabId).innerHTML = content;
			}
		}
		else
		{
			this.tabItems[tabId].initContent = content;
		}

		return true;
	}

	MessengerWindow.prototype.getCurrentTab = function ()
	{
		return this.currentTab;
	}

	MessengerWindow.prototype.getCurrentTabTarget = function ()
	{
		return this.currentTabTarget;
	}

	MessengerWindow.prototype.setUserInfo = function (params)
	{
		if (!this.userInfo)
		{
			if (!params || !params.id || !params.name)
				return false;
		}

		if (params)
		{
			if (!params.gender)
				params.gender = 'M';

			if (!params.avatar || !params.profile)
				params.avatar = '';

			this.userInfo = params;
		}

		if (!this.contentAvatar)
		{
			if (!this.drawAppearance())
				return false;
		}

		var events = {};

		events.click = function(e){
			BXIM.openMessenger(BXIM.userId);
			return BX.PreventDefault(e);
		};

		this.contentAvatar.innerHTML = '';
		this.contentAvatar.appendChild(
			BX.create('a', { attrs : { href : this.userInfo.profile, title : BX.util.htmlspecialcharsback(this.userInfo.name), target: "_blank" }, props : { className : "bx-desktop-avatar" }, events: events, children: [
				BX.create('img', { attrs : { src : this.userInfo.avatar, style: (BX.MessengerCommon.isBlankAvatar(this.userInfo.avatar)? 'background-color: '+this.userInfo.color: '')}, props : { className : "bx-desktop-avatar-img bx-desktop-avatar-img-default" }})
			]})
		);

		return true;
	}

	MessengerWindow.prototype.updateUserInfo = function (params)
	{
		for (var i in params)
		{
			this.userInfo[i] = params[i];
		}
		return this.setUserInfo(this.userInfo);
	}

	MessengerWindow.prototype.getUserInfo = function()
	{
		return this.userInfo;
	}

	MessengerWindow.prototype.isPopupShow = function()
	{
		if (this.context == 'DESKTOP')
			return true;
		else if (this.context == 'POPUP-FULLSCREEN' && !BX.hasClass(this.popup, 'bx-im-fullscreen-closed'))
			return true;

		return false;
	}

	MessengerWindow.prototype.backgroundChange = function()
	{
		var backgroundImage = this.backgroundSelector.value;
		if (backgroundImage == 'transparent')
		{
			BX.removeClass(this.popupBackground, 'bx-im-fullscreen-popup-bitrix24');
			BX.addClass(this.popupBackground, 'bx-im-fullscreen-popup-transparent');
			BX.style(this.popupBackground, 'background', '');
			BX.style(this.popupBackground, 'backgroundSize', '');
		}
		else if (backgroundImage > 0)
		{
			BX.removeClass(this.popupBackground, 'bx-im-fullscreen-popup-bitrix24');
			BX.removeClass(this.popupBackground, 'bx-im-fullscreen-popup-transparent');
			BX.style(this.popupBackground, 'background', 'url(/bitrix/js/im/images/bg-image-'+backgroundImage+'.jpg) #ccc');
			BX.style(this.popupBackground, 'backgroundSize', 'cover');
		}
		else
		{
			BX.removeClass(this.popupBackground, 'bx-im-fullscreen-popup-transparent');
			BX.addClass(this.popupBackground, 'bx-im-fullscreen-popup-bitrix24');
			BX.style(this.popupBackground, 'background', '');
			BX.style(this.popupBackground, 'backgroundSize', '');
		}
	}

	MessengerWindow.prototype.showPopup = function(dialogId)
	{
		if (this.isPopupShow())
			return false;

		this.popupTimestart = +new Date();
		clearTimeout(this.popupTimeout);

		var scrollSize = window.innerWidth - document.documentElement.clientWidth;
		BX.onCustomEvent(window, 'onMessengerWindowBodyOverflow', [this, scrollSize]);
		BX.addClass(document.body, 'bx-im-fullscreen-block-scroll');

		BX.addClass(this.popup, 'bx-im-fullscreen-opening');
		BX.removeClass(this.popup, 'bx-im-fullscreen-closing');
		BX.removeClass(this.popup, 'bx-im-fullscreen-closed');
		this.adjustSize();
		this.BXIM.desktop.initHeight = BX.MessengerWindow.content.offsetHeight;

		this.popupTimeout = setTimeout(BX.delegate(function(){
			BX.removeClass(this.popup, 'bx-im-fullscreen-opening');
			BX.addClass(this.popup, 'bx-im-fullscreen-open');
			if (this.BXIM.webrtc.callOverlay)
			{
				BX.style(this.BXIM.webrtc.callOverlay, 'height', (this.BXIM.messenger.popupMessengerFullHeight-1)+'px');
			}
		}, this), 400);

		BX.onCustomEvent(this, 'OnMessengerWindowShowPopup', [dialogId]);
		return true;
	}

	MessengerWindow.prototype.closePopup = function()
	{
		if (!this.isPopupShow() || this.BXIM.webrtc.callInit)
			return false;

		if (this.popupTimestart+400 > (+new Date()))
			return false;

		clearTimeout(this.popupTimeout);
		BX.removeClass(document.body, 'bx-im-fullscreen-block-scroll');
		BX.onCustomEvent(this, 'OnMessengerWindowClosePopup', []);
		BX.onCustomEvent(window, 'onMessengerWindowBodyOverflow', [this, 0]);

		BX.addClass(this.popup, 'bx-im-fullscreen-open');
		BX.addClass(this.popup, 'bx-im-fullscreen-closing');
		BX.removeClass(this.popup, 'bx-im-fullscreen-opening');
		this.popupTimeout = setTimeout(BX.delegate(function(){
			BX.removeClass(this.popup, 'bx-im-fullscreen-closing');
			BX.removeClass(this.popup, 'bx-im-fullscreen-open');
			BX.addClass(this.popup, 'bx-im-fullscreen-closed');

		}, this), 400);

		return true;
	}

	MessengerWindow.prototype.storageSet = function(params)
	{
		if (params.key == 'imFullscreenBackground')
		{
			this.backgroundSelector.value = params.value;
			this.backgroundChange();
		}
	};

	BX.MessengerWindow = new MessengerWindow();
})(window);
