/**
 * Class for Desktop App
 * @param params
 * @constructor
 */

;(function (window)
{
	if (window.BX.desktop) return;

	var BX = window.BX;

	var Desktop = function ()
	{
		this.apiReady = typeof(BXDesktopSystem) != "undefined" || typeof(BXDesktopWindow) != "undefined";
		this.clientVersion = 0;

		this.disableLogin = false;

		this.autorun = null;
		this.telemetry = null;
		this.lastSetIcon = null;
		this.currentIcon = null;
		this.showNotifyId = {};
		this.htmlWrapperHead = null;

		this.topmostWindow = null;
		this.topmostWindowTimeout = null;

		this.path = {};
		this.path.mainUserOptions = '/desktop_app/options.ajax.php';
		this.path.pathToAjax = '/desktop_app/im.ajax.php';
		this.path.pathToPull = '/desktop_app/pull.ajax.php';

		this.tabItems = {};
		this.tabRedrawTimeout = null;

		this.syncStatus = null;
		this.syncPauseBlock = false;

		this.inited = false;
		this.sizeInited = false;

		BXDesktopSystem.LogInfo = function()
		{
			for (var _len = arguments.length, params = new Array(_len), _key = 0; _key < _len; _key++) {
				params[_key] = arguments[_key];
			}

			var context = BX.Messenger.Lib.Logger;
			context.desktop.apply(context, params);
		};

		/* sizes */

		this.minWidth = 515;
		this.minHeight = 384;

		this.timeoutDelayOfLogout = null;

		this.eventHandlers = {};

		this.addCustomEvent("bxImLogoutInit", BX.delegate(function(terminate, reason) {
			this.logout(terminate, reason, true);
		}, this));

		// desktop hotkeys
		BX.bind(window, "keydown", BX.delegate(function(e) {
			// CMD+R / CTRL+R
			if (e.keyCode == 82 && (e.ctrlKey == true || e.metaKey == true))
			{
				if (typeof(BXIM) === 'undefined' || !BXIM.callController.hasActiveCall())
				{
					console.log('NOTICE: User use /windowReload');
					this.windowReload();
				}
			}
			// CMD+SHIFT+D / CTRL+SHIFT+D
			else if (e.keyCode == 68 && (e.ctrlKey == true || e.metaKey == true) && e.shiftKey == true)
			{
				this.openDeveloperTools();
				console.log('NOTICE: User use /openDeveloperTools');
			}
			// CMD+SHIFT+L / CTRL+SHIFT+L
			else if (e.keyCode == 76 && (e.ctrlKey == true || e.metaKey == true) && e.shiftKey == true)
			{
				this.openLogsFolder();
				console.log('NOTICE: User use /openLogsFolder');
			}
		}, this));
	};

	Desktop.prototype.init = function (params)
	{
		params = params || {};
		if (this.inited)
		{
			return true;
		}
		this.inited = true;

		if (this.ready())
		{
			console.log(BX.message('BXD_DEFAULT_TITLE').replace('#VERSION#', this.getApiVersion(true)));
		}

		if (this.enableInVersion(45))
		{
			BX.debugEnable(true);
		}
		else
		{
			if (!BX.browser.IsMac() && document.head)
				document.head.insertBefore(BX.create("style", {attrs: {type: 'text/css'}, html: "@font-face { font-family: 'helvetica neue'; src: local('Arial'); } @font-face { font-family: 'Helvetica'; src: local('Arial'); }"}), document.head.firstChild);

			if (this.ready())
			{
				BX.ready(function(){
					BX.addClass(document.body, 'bx-desktop');
				});
			}
			else
			{
				BX.ready(function(){
					BX.addClass(document.body, 'im-desktop-content');
				});
			}

			this.setWindowResizable(true);
			this.setWindowMinSize({ Width: BX.MessengerWindow.minWidth, Height: BX.MessengerWindow.minHeight });
		}

		BX.addCustomEvent("onMessengerWindowInit", BX.delegate(function() {
			this.userInfo = BX.MessengerWindow.getUserInfo();
			this.contentMenu = BX.MessengerWindow.contentMenu;
			this.content = BX.MessengerWindow.content;
			if (!this.enableInVersion(45))
			{
				BX.onCustomEvent(window, 'onDesktopInit', [this]);
				BX.desktop.onCustomEvent("onDesktopInit", [this]);
			}
		}, this));
		BX.addCustomEvent("onPullRevisionUp", function(newRevision, oldRevision) {
			BX.PULL.closeConfirm();
			console.log('NOTICE: Window reload, becouse PULL REVISION UP ('+oldRevision+' -> '+newRevision+')');
			BX.onCustomEvent(window, 'onDesktopReload', [this]);
			location.reload();
		});
		BX.addCustomEvent("onPullError", BX.delegate(function(error, code) {
			if (error == 'AUTHORIZE_ERROR')
			{
				this.setIconStatus('offline');
				this.login(function(){
					console.log('DESKTOP LOGIN: success after PullError');
				});
			}
			else if (error == 'RECONNECT')
			{
				this.setIconStatus('offline');
			}
		}, this));

		BX.addCustomEvent("onImError", BX.delegate(function(error, sendErrorCode) {
			if (error == 'AUTHORIZE_ERROR' || error == 'SEND_ERROR' && sendErrorCode == 'AUTHORIZE_ERROR')
			{
				this.setIconStatus('offline');
				this.login(BX.delegate(function(){
					this.setIconStatus('online');

					var textError = 'DESKTOP LOGIN: success after ImError';
					console.log(textError);

					if (typeof(BXIM) != 'undefined')
					{
						BX.desktop.log('phone.'+BXIM.userEmail+'.log', textError);
						BXIM.messenger.connectionStatus('online', false);
					}
				},this));
			}
			else if (error == 'CONNECT_ERROR')
			{
				this.setIconStatus('offline');
			}
		}, this));

		this.addCustomEvent("BXChangeTab", BX.delegate(function(tabId) {
			this.changeTab(tabId)
		}, this));

		this.addCustomEvent("BXTrayConstructMenu", BX.delegate(function() {
			this.onCustomEvent('main','BXTrayMenu', [])
			setTimeout(function(){
				BX.desktop.finalizeTrayMenu();
			});
		}, this));

		this.addCustomEvent("BXFileStorageSyncPauseChanged", BX.delegate(this.onSyncStatusChanged, this));

		if (this.ready())
		{
			if (!this.enableInVersion(45))
			{
				BX.userOptions.setAjaxPath(this.path.mainUserOptions);
			}

			BX.addCustomEvent("onPullStatus", BX.delegate(function(status){
				if (status == 'offline')
					this.setIconStatus('offline');
				else
					this.setIconStatus(BXIM && BXIM.settings? BXIM.settings.status: 'online');
			}, this));

			BX.bind(window, "online", BX.delegate(function(){
				this.setIconStatus(BXIM && BXIM.settings? BXIM.settings.status: 'online');
			}, this));

			BX.bind(window, "offline", BX.delegate(function(){
				this.setIconStatus('offline');
			}, this));

			this.addCustomEvent("BXWakeAction", BX.delegate(function(){
				this.setIconStatus(BXIM && BXIM.settings? BXIM.settings.status: 'online');
			}, this));

			this.addCustomEvent("BXSleepAction", BX.delegate(function(){
				this.setIconStatus('offline');
			}, this));

			this.addCustomEvent("BXExitApplication", BX.delegate(function() {
				this.preventShutdown();
				this.logout(true, 'exit_event');
			}, this));

			if (this.enableInVersion(45))
			{
				BX.onCustomEvent(window, 'onDesktopInit', [this]);
				BX.desktop.onCustomEvent("onDesktopInit", [this]);
			}
		}
	}

	Desktop.prototype.notSupported = function ()
	{
		this.setWindowMinSize({ Width: 864, Height: 493 });
		this.setWindowSize({ Width: 864, Height: 493 });
		this.setWindowTitle(BX.message('BXD_DEFAULT_TITLE').replace('#VERSION#', this.getApiVersion(true)));

		var updateContent = BX.create("div", { props : { className : "bx-desktop-update-box" }, children : [
			BX.create("div", { props : { className : "bx-desktop-update-box-text" }, html: BX.message('BXD_NEED_UPDATE')}),
			BX.create("div", { props : { className : "bx-desktop-update-box-btn" }, events : { click :  BX.delegate(function(){this.checkUpdate(true)}, this)}, html: BX.message('BXD_NEED_UPDATE_BTN')})
		]});

		BX.ready(function(){
			document.body.innerHTML = '';
			document.body.appendChild(updateContent);
			BX.onCustomEvent(window, 'onDesktopOutdated', [this]);
		});
	}

	Desktop.prototype.withoutPushServer = function ()
	{
		this.setWindowMinSize({ Width: 864, Height: 493 });
		this.setWindowSize({ Width: 864, Height: 493 });
		this.setWindowTitle(BX.message('BXD_DEFAULT_TITLE').replace('#VERSION#', this.getApiVersion(true)));

		var updateContent = BX.create("div", { props : { className : "bx-desktop-update-box" }, children : [
			BX.create("div", { props : { className : "bx-desktop-update-box-text" }, html: BX.message('IM_M_PP_SERVER_ERROR')}),
			BX.create("div", { props : { className : "bx-desktop-update-box-btn" }, events : { click :  BX.delegate(function(){
				if (BXIM.bitrixIntranet)
				{
					BX.Helper.show("redirect=detail&code=12715116");
				}
				else
				{
					BX.MessengerCommon.openLink(BX.message('IM_M_PP_SERVER_ERROR_BUS_LINK'));
				}
			}, this)
			}, html: BX.message('IM_M_PP_SERVER_ERROR_MORE')})
		]});

		BX.ready(function(){
			document.body.innerHTML = '';
			document.body.appendChild(updateContent);
			BX.onCustomEvent(window, 'onDesktopOutdated', [this]);
		});
	}

	Desktop.prototype.hideLoader = function()
	{
		BX.remove(BX('bx-desktop-loader'));
	}

	Desktop.prototype.isFeatureEnabled = function(code)
	{
		if (!this.ready())
		{
			return false;
		}

		if (typeof BXDesktopSystem.FeatureEnabled !== 'function')
		{
			return false;
		}

		return !!BXDesktopSystem.FeatureEnabled(code);
	}

	Desktop.prototype.getBackgroundImage = function()
	{
		if (!this.apiReady)
		{
			return {id: 'none', source: ''};
		}

		var id = BXDesktopSystem.QuerySettings("bxd_camera_background_id") || 'none';

		return {id: id};
	}

	Desktop.prototype.setCallBackground = function(id, source)
	{
		if (source === 'none' || source === '')
		{
			source = '';
		}
		else if (source === 'blur')
		{
		}
		else if (source === 'gaussianBlur')
		{
			source = 'GaussianBlur';
		}
		else
		{
			source = this.prepareResourcePath(source);
		}

		var promise = new BX.Promise();

		setTimeout(() => {
			this.setCallMask(false);
			BXDesktopSystem.StoreSettings("bxd_camera_background_id", id);
			BXDesktopSystem.StoreSettings("bxd_camera_background", source);

			promise.resolve();
		}, 100);

		return promise;
	}

	Desktop.prototype.setCallMaskLoadHandlers = function(callback)
	{
		this.addCustomEvent("BX3dAvatarReady", callback);
		this.addCustomEvent("BX3dAvatarError", callback);
	}

	Desktop.prototype.setCallMask = function(id, maskUrl, backgroundUrl)
	{
		if (!this.enableInVersion(72))
		{
			return false;
		}

		if (!id)
		{
			BXDesktopSystem.Set3dAvatar("", "");
			BXDesktopSystem.StoreSettings("bxd_camera_3dbackground_id", '');
			return true;
		}

		maskUrl = this.prepareResourcePath(maskUrl);
		backgroundUrl = this.prepareResourcePath(backgroundUrl);

		BXDesktopSystem.Set3dAvatar(maskUrl, backgroundUrl);
		BXDesktopSystem.StoreSettings("bxd_camera_3dbackground_id", id);
	}

	Desktop.prototype.getMask = function()
	{
		if (!this.apiReady)
		{
			return {id: ''};
		}

		return {
			id: BXDesktopSystem.QuerySettings("bxd_camera_3dbackground_id") || ''
		};
	}

	Desktop.prototype.prepareResourcePath = function(source)
	{
		try
		{
			const url = new URL(source, location.origin);
			source = url.href;
		}
		catch(e)
		{
			source = '';
		}

		return source;
	}

	Desktop.prototype.getCurrentUrl = function ()
	{
		return document.location.protocol+'//'+document.location.hostname+(document.location.port == ''?'':':'+document.location.port)
	}

	Desktop.prototype.ready = function ()
	{
		return this.apiReady;
	}

	Desktop.prototype.diskReady = function ()
	{
		return this.apiReady && typeof(BXFileStorage) != 'undefined';
	}

	Desktop.prototype.login = function (callback)
	{
		if (this.disableLogin)
		{
			console.log('DESKTOP LOGIN: command was disabled');
			return false;
		}

		var textError = 'DESKTOP LOGIN: try to login';
		console.log(textError);

		if (typeof(BXIM) != 'undefined')
		{
			BX.desktop.log('phone.'+BXIM.userEmail+'.log', textError);
		}

		if (!this.ready())
		{
			this.windowReload();
			return false;
		}

		if (this.currentIcon === 'offline')
		{
			this.setIconStatus(this.lastSetIcon);
		}

		var params = {};

		if (typeof(callback)=='function')
		{
			params.success = BX.delegate(function(sessid) {
				if (typeof(sessid) == "string")
				{
					BX.message({'bitrix_sessid': sessid});
				}
				callback(sessid);
				this.onCustomEvent('main','BXLoginSuccess', [sessid]);
			}, this);
		}
		else
		{
			params.success = BX.delegate(this.loginSuccessCallback, this);
		}

		BXDesktopSystem.Login(params);

		return true;
	}

	Desktop.prototype.loginSuccessCallback = function (sessid)
	{
		if (typeof(sessid) == "string")
		{
			BX.message({'bitrix_sessid': sessid});
		}

		if (!this.ready()) return false;

		//this.windowReload()

		return true;
	}

	Desktop.prototype.showLoginForm = function ()
	{
		BXDesktopSystem.Logout(1, 'login_form');
	}

	Desktop.prototype.windowReload = function ()
	{
		BX.onCustomEvent(window, 'onDesktopReload', [this]);
		location.reload();
	}

	Desktop.prototype.logout = function (terminate, reason, skipCheck)
	{
		terminate = terminate == true;

		this.apiReady = false;

		BX.ajax({
			url: this.path.pathToAjax+'?DESKTOP_LOGOUT',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_DESKTOP_LOGOUT' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function()
			{
				if (reason)
					console.log('Logout reason: '+reason);

				if (terminate)
					BXDesktopSystem.Shutdown();
				else
					BXDesktopSystem.Logout(2);
			}, this),
			onfailure: BX.delegate(function()
			{
				if (reason)
					console.log('Logout reason (fail): '+reason);

				if (terminate)
					BXDesktopSystem.Shutdown();
				else
					BXDesktopSystem.Logout(3);
			}, this)
		});

		return true;
	}

	Desktop.prototype.checkUpdate = function (openBrowser)
	{
		if (typeof(BXDesktopSystem) == 'undefined')
			return false;

		openBrowser = typeof(openBrowser) != 'boolean'? false: openBrowser;
		if (!openBrowser && this.enableInVersion(16))
			BXDesktopSystem.ExecuteCommand("update.check", { NotifyNoUpdates: true, ShowNotifications: true});
		else
			this.browse(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp");

		return true;
	}

	Desktop.prototype.getApiVersion = function (full)
	{
		if (typeof(BXDesktopSystem) == 'undefined')
			return 0;

		if (!this.clientVersion)
			this.clientVersion = BXDesktopSystem.GetProperty('versionParts');

		return full? this.clientVersion.join('.'): this.clientVersion[3];
	}

	Desktop.prototype.enableInVersion = function (version)
	{
		if (typeof(BXDesktopSystem) == 'undefined')
			return false;

		return this.getApiVersion() >= parseInt(version);
	}

	Desktop.prototype.addCustomEvent = function(eventName, eventHandler)
	{
		if (!this.ready()) return false;
		var realHandler = function (e)
		{
			var arEventParams = [];
			for(var i in e.detail)
				arEventParams.push(e.detail[i]);

			eventHandler.apply(window, arEventParams);
		};

		if(!this.eventHandlers[eventName])
			this.eventHandlers[eventName] = [];

		this.eventHandlers[eventName].push(realHandler);
		window.addEventListener(eventName, realHandler);

		return true;
	}

	Desktop.prototype.removeCustomEvents = function(eventName)
	{
		if(!this.eventHandlers[eventName])
			return false;

		this.eventHandlers[eventName].forEach(function(eventHandler)
		{
			window.removeEventListener(eventName, eventHandler);
		});
		this.eventHandlers[eventName] = [];
	}

	Desktop.prototype.onCustomEvent = function(windowTarget, eventName, arEventParams)
	{
		if (!this.ready()) return false;

		if (arguments.length == 2)
		{
			arEventParams = eventName
			eventName = windowTarget;
			windowTarget = 'all';
		}
		else if (arguments.length < 2)
		{
			return false;
		}

		var objEventParams = {};
		for (var i = 0; i < arEventParams.length; i++)
		{
			objEventParams[i] = arEventParams[i];
		}

		if (windowTarget == 'all')
		{
			try
			{
				var mainWindow = opener? opener: top;
				for (var i = 0; i < mainWindow.BXWindows.length; i++)
				{
					if (
						mainWindow.BXWindows[i]
						&& mainWindow.BXWindows[i].name != ''
						&& mainWindow.BXWindows[i].BXDesktopWindow
						&& mainWindow.BXWindows[i].BXDesktopWindow.DispatchCustomEvent
					)
					{
						mainWindow.BXWindows[i].BXDesktopWindow.DispatchCustomEvent(eventName, objEventParams);
					}
				}
			}
			catch (e)
			{
				console.warn(e);
			}

			try
			{
				mainWindow.BXDesktopWindow.DispatchCustomEvent(eventName, objEventParams);
			}
			catch (e)
			{
				console.warn(e);
			}
		}

		try
		{
			if(typeof(windowTarget) === "object" && windowTarget.hasOwnProperty("BXDesktopWindow"))
			{
				windowTarget.BXDesktopWindow.DispatchCustomEvent(eventName, objEventParams);
			}
			else
			{
				if (windowTarget = this.findWindow(windowTarget))
					windowTarget.BXDesktopWindow.DispatchCustomEvent(eventName, objEventParams);
			}
		}
		catch (e)
		{
			console.warn(e);
		}

		return true;
	}

	Desktop.prototype.findWindow = function (name)
	{
		if (!this.ready()) return null;

		if (typeof(name) == 'undefined')
			name = 'main';

		var mainWindow = opener? opener: top;
		if (name == 'main')
		{
			return mainWindow;
		}
		else
		{
			for (var i = 0; i < mainWindow.BXWindows.length; i++)
			{
				if (mainWindow.BXWindows[i] && mainWindow.BXWindows[i].name === name)
					return mainWindow.BXWindows[i];
			}
		}
		return null;
	}

	Desktop.prototype.windowIsFocused = function ()
	{
		if (!this.ready()) return false;

		return BXDesktopWindow.GetProperty("isForeground");
	}

	Desktop.prototype.openNextTab = function ()
	{
		if (!this.ready()) return false;

		return BXDesktopSystem.NextTab();
	}

	Desktop.prototype.setIconStatus = function (status)
	{
		if (!this.ready()) return false;

		if (this.currentIcon === status)
			return false;

		this.lastSetIcon = !this.lastSetIcon? 'online': this.currentIcon;
		this.currentIcon = status;

		BXDesktopSystem.SetIconStatus(status);

		return true;
	}

	Desktop.prototype.setIconBadge = function (count, important)
	{
		if (!this.ready()) return false;

		important = important === true;

		BXDesktopSystem.SetIconBadge(count + '', important);
		BXDesktopSystem.SetTabBadge(this.getContextWindow(), count + '');

		return true;
	}

	Desktop.prototype.setIconTooltip = function (iconTitle)
	{
		if (!this.ready()) return false;

		return BXDesktopSystem.ExecuteCommand('tooltip.change', iconTitle);
	}

	Desktop.prototype.setWindowResizable = function (enabled)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.SetProperty("resizable", enabled !== false);

		return false;
	}

	Desktop.prototype.setWindowClosable = function (enabled)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.SetProperty("closable", enabled !== false);

		return false;
	}

	Desktop.prototype.flashIcon = function (voiced)
	{
		if (!this.ready()) return false;

		BXDesktopSystem.FlashIcon(voiced == true);

		return true;
	}

	Desktop.prototype.getWorkArea = function ()
	{
		if (!this.ready())
			return false;

		var coordinates = BXDesktopSystem.GetWorkArea();

		return {top: coordinates[0], left: coordinates[1], right: coordinates[2], bottom: coordinates[3]}
	}

	Desktop.prototype.showNotification = function (notifyId, content, js)
	{
		if (!this.ready() || content == "")
			return false;

		if (this.showNotifyId[notifyId])
			return false;

		this.showNotifyId[notifyId] = true;

		BXDesktopSystem.ExecuteCommand('notification.show.html', this.getHtmlPage(content, js, 'desktop-notify-popup'));

		return true;
	}

	Desktop.prototype.adjustSize = function (width, height)
	{
		return BX.MessengerWindow.adjustSize(width, height);
	}

	Desktop.prototype.resize = function ()
	{
		if (!this.ready()) return false;

		if (!BXIM.init)
		{
			BXDesktopWindow.SetProperty("clientSize", { Width: document.body.offsetWidth, Height: document.body.offsetHeight});
		}

		return true;
	}

	Desktop.prototype.syncPause = function (status, immediate)
	{
		if (!this.diskReady()) return false;

		if (immediate)
		{
			this.syncPauseBlock = status;
		}

		if (!this.syncPauseBlock || this.syncPauseBlock && immediate)
		{
			this.syncStatus = !status;

			BXFileStorage.SyncPause(!this.syncStatus);
			BX.onCustomEvent(window, 'onDesktopSyncPause', [this.syncStatus]);
		}

		return true;
	};

	Desktop.prototype.onSyncStatusChanged = function (status)
	{
		this.syncPause(status, true);
	};

	Desktop.prototype.getSyncStatus = function ()
	{
		return this.syncStatus;
	};

	Desktop.prototype.windowCommand = function (windowTarget, command)
	{
		if (!this.ready()) return false;

		if (arguments.length == 1)
		{
			command = windowTarget;
			windowTarget = window;
		}

		if (command == "show" && windowTarget == window)
		{
			BX.desktop.setActiveWindow();
		}

		try
		{
			if (command == "show" || command == "hide" || command == "freeze" || command == "unfreeze")
			{
				windowTarget.BXDesktopWindow.ExecuteCommand(command);
			}
			else if (command == "focus")
			{
				windowTarget.BXDesktopWindow.ExecuteCommand('show.active');
			}
			else if (command == "close")
			{
				if (windowTarget.opener)
				{
					if (windowTarget.name.indexOf('topmost')>=0 || windowTarget.name.indexOf('notif')>=0)
					{
						windowTarget.BXDesktopWindow.ExecuteCommand("close");
					}
					else
					{
						windowTarget.close();
					}
				}
				else
				{
					windowTarget.BXDesktopWindow.ExecuteCommand("hide");
				}
			}
		}
		catch(e)
		{
			console.log('ExecuteCommand Error', command, windowTarget, e);
			console.trace();
		}

		return true;
	};

	Desktop.prototype.openTopmostWindow = function(html, js, bodyClass)
	{
		if (!this.ready())
			return false;

		this.closeTopmostWindow();
		this.topmostWindow = BXDesktopSystem.ExecuteCommand('topmost.show.html', this.getHtmlPage(html, js, bodyClass));

		return true;
	};

	Desktop.prototype.closeTopmostWindow = function()
	{
		if (this.topmostWindow)
		{
			this.windowCommand(this.topmostWindow, "close");
			this.topmostWindow = null;
		}
		return true;
	}

	Desktop.prototype.isPopupPageLoaded = function()
	{
		if (!this.enableInVersion(45))
			return false;

		if (("BXIM" in window) && !window.BXIM.isUtfMode)
			return false;

		if (!BXInternals)
			return false;

		if (!BXInternals.PopupTemplate)
			return false;

		if (BXInternals.PopupTemplate === '#PLACEHOLDER#')
			return false;

		return true;
	}

	Desktop.prototype.getHtmlPage = function(content, jsContent, initImJs, bodyClass)
	{
		if (!this.ready()) return;

		if (("BXIM" in window))
		{
			return window.BXIM.desktop.getHtmlPage(content, jsContent, initImJs, bodyClass);
		}

		content = content || '';
		jsContent = jsContent || '';
		bodyClass = bodyClass || '';


		if (content != '' && BX.type.isDomNode(content))
		{
			content = content.outerHTML;
		}

		if (jsContent != '' && BX.type.isDomNode(jsContent))
		{
			jsContent = jsContent.outerHTML;
		}

		if (jsContent != '')
		{
			jsContent = '<script type="text/javascript">BX.ready(function(){'+jsContent+'});</script>';
		}

		if (this.isPopupPageLoaded())
		{
			return '<div class="im-desktop im-desktop-popup '+bodyClass+'">'+content+jsContent+'</div>';
		}
		else
		{
			if (this.htmlWrapperHead == null)
			{
				this.htmlWrapperHead = document.head.outerHTML.replace(/BX\.PULL\.start\([^)]*\);/g, '');
			}

			return '<!DOCTYPE html><html>'+this.htmlWrapperHead+'<body class="im-desktop im-desktop-popup '+bodyClass+'">'+content+jsContent+'</body></html>';
		}
	};

	Desktop.prototype.openDeveloperTools = function()
	{
		if (typeof(BXDesktopWindow) == 'undefined')
			return false;

		BXDesktopWindow.OpenDeveloperTools();

		return true;
	};

	Desktop.prototype.openLogsFolder = function()
	{
		if (!this.ready()) return false;

		BXDesktopSystem.OpenLogsFolder();

		return true;
	};

	Desktop.prototype.browse = function (url)
	{
		if (typeof(BXDesktopSystem) == 'undefined')
			return false;

		BXDesktopSystem.ExecuteCommand('browse', url);

		return true;
	}

	Desktop.prototype.autorunStatus = function(value)
	{
		if (!this.ready()) return false;

		if (typeof(value) !='boolean')
		{
			if (this.autorun == null)
			{
				this.autorun = BXDesktopSystem.GetProperty("autostart");
			}
		}
		else
		{
			this.autorun = value;
			BXDesktopSystem.SetProperty("autostart", this.autorun);
		}
		return this.autorun;
	};

	Desktop.prototype.telemetryStatus = function(value)
	{
		if (!this.ready()) return false;

		if (typeof(value) !='boolean')
		{
			return BXDesktopSystem.QuerySettings("bxd_telemetry", "1") === "1";
		}
		else
		{
			BXDesktopSystem.StoreSettings("bxd_telemetry", value? "1": "0");
		}

		return true;
	};

	Desktop.prototype.cameraSmoothingStatus = function(value)
	{
		if (!this.ready()) return false;

		if (typeof(value) !='boolean')
		{
			return BXDesktopSystem.QuerySettings("bxd_camera_smoothing", "0") === "1";
		}
		else
		{
			BXDesktopSystem.StoreSettings("bxd_camera_smoothing", value? "1": "0");
		}

		return true;
	};

	Desktop.prototype.cameraSmoothingLambda = function(value)
	{
		if (!this.ready()) return false;

		if (typeof(value) === 'undefined')
		{
			return BXDesktopSystem.QuerySettings("bxd_camera_smoothing_lambda", "36");
		}

		BXDesktopSystem.StoreSettings("bxd_camera_smoothing_lambda", value.toString());

		return true;
	};

	Desktop.prototype.diskAttachStatus = function()
	{
		if (!this.ready()) return false;

		return BitrixDisk? BitrixDisk.enabled: false;
	};

	Desktop.prototype.clipboardSelected = function (element)
	{
		expandToWholeWord = false;

		var resultText = "";
		var selectionStart = 0;
		var selectionEnd = 0;

		if (typeof(element) == 'object' && (element.tagName == 'TEXTAREA' || element.tagName == 'INPUT' || !element.tagName))
		{
			selectionStart = element.selectionStart;
			selectionEnd = element.selectionEnd;
			resultText = element.value.substring(selectionStart, selectionEnd);

			if (selectionStart == selectionEnd)
			{
				if (!(resultText && resultText.indexOf(" ") > -1))
				{
					var wordStartPosition = element.value.substr(0, selectionStart).search(/([-'`~!@#$%^&*()_|+=?;:'",.<>\{\}\[\]\\\/\x20])(?!.*[-'`~!@#$%^&*()_|+=?;:'",.<>\{\}\[\]\\\/\x20])/)+1;
					var wordEndPosition = element.value.substr(wordStartPosition).search(/[-'`~!@#$%^&*()_|+=?;:'",.<>\{\}\[\]\\\/\x20]/);
					wordEndPosition = (wordEndPosition > 0? wordEndPosition: element.value.length);

					resultText = element.value.substr(wordStartPosition, wordEndPosition);

					selectionStart = wordStartPosition;
					selectionEnd = wordStartPosition+wordEndPosition;
				}
			}
		}
		else
		{
			if (!expandToWholeWord && window.getSelection().toString().length > 0)
			{
				var range = window.getSelection().getRangeAt(0).cloneContents();
				var div = document.createElement("div");
				div.appendChild(range);

				var messages = div.getElementsByClassName('bx-messenger-message');
				if (messages.length > 0)
				{
					var resultMessage = [];
					for (var index in messages)
					{
						if (messages.hasOwnProperty(index))
						{
							resultMessage.push(messages[index].innerHTML);
						}
					}
					resultText = resultMessage.join('\n');
				}
				else
				{
					resultText = div.innerHTML;
				}
			}
		}

		if (resultText.length > 0)
		{
			resultText = BX.util.htmlspecialcharsback(resultText);
			resultText = resultText.split('&nbsp;&nbsp;&nbsp;&nbsp;').join("\t");
			resultText = resultText.replace(/<img.*?data-code="([^"]*)".*?>/gi, '$1');
			resultText = resultText.replace(/&nbsp;/gi, ' ').replace(/&copy;/, '(c)');
			resultText = resultText.replace(/<div class=\"bx-messenger-hr\"><\/div>/gi, '\n');
			resultText = resultText.replace(/<span class=\"bx-messenger-clear\"><\/span>/gi, '\n');
			resultText = resultText.replace(/<s>([^"]*)<\/s>/gi, '');
			resultText = resultText.replace(/<(\/*)([buis]+)>/gi, '[$1$2]');
			resultText = resultText.replace(/<a.*?href="([^"]*)".*?>.*?<\/a>/gi, '$1');
			resultText = resultText.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "["+BX.message("BXD_QUOTE_BLOCK")+"]");
			resultText = resultText.replace(/<br( \/)?>/gi, '\n').replace(/<\/?[^>]+>/gi, '');
		}
		return {text: resultText, selectionStart: selectionStart, selectionEnd: selectionEnd};
	}

	Desktop.prototype.clipboardCopy = function(callback, cut)
	{
		return BX.MessengerCommon.clipboardCopy(callback, cut);
	}

	Desktop.prototype.clipboardCut = function ()
	{
		return BX.MessengerCommon.clipboardCut();
	}

	Desktop.prototype.clipboardPaste = function ()
	{
		if (!this.ready()) return false;

		document.execCommand("paste");

		return true;
	}

	Desktop.prototype.clipboardDelete = function ()
	{
		if (!this.ready()) return false;

		document.execCommand("delete");

		return true;
	}

	Desktop.prototype.clipboardUndo = function ()
	{
		if (!this.ready()) return false;

		document.execCommand("undo");

		return true;
	}

	Desktop.prototype.clipboardRedo = function ()
	{
		if (!this.ready()) return false;

		document.execCommand("redo");

		return true;
	}

	Desktop.prototype.clipboardReplaceText = function (element, positionStart, positionEnd, text)
	{
		if (!this.ready()) return false;

		element.focus();
  		element.selectionStart = positionStart;
  		element.selectionEnd = positionEnd;

		if (positionEnd - positionStart < text.length)
		{
			positionEnd = positionStart+text.length;
		}

		document.execCommand("insertText", false, text);

		element.selectionStart = positionEnd;
  		element.selectionEnd = positionEnd;

		return true;
	}

	Desktop.prototype.selectAll = function (element)
	{
		if (!this.ready()) return false;

		element.selectionStart = 0;

		return true;
	}

	Desktop.prototype.getLocalConfig = function(name, def)
	{
		def = typeof(def) == 'undefined'? null: def;

		if (!this.ready()) return def;

		var querySetting = BXDesktopSystem.QuerySettings(name, def+'');

		var result = def;
		if (typeof(querySetting) == 'string' && querySetting.length > 0)
		{
			try {
				result = JSON.parse(querySetting);
			}
			catch(e) { result = querySetting; }
		}

		return result;
	};

	Desktop.prototype.setLocalConfig = function(name, value)
	{
		if (!this.ready()) return false;

		if (typeof(value) == 'object')
			value = JSON.stringify(value);
		else if (typeof(value) == 'boolean')
			value = value? 'true': 'false';
		else if (typeof(value) == 'undefined')
			value = '';
		else if (typeof(value) != 'string')
			value = value+'';

		BXDesktopSystem.StoreSettings(name, value);

		return true;
	};

	Desktop.prototype.removeLocalConfig = function(name)
	{
		if (!this.ready()) return false;

		BXDesktopSystem.StoreSettings(name, null);

		return true;
	};

	Desktop.prototype.log = function (filename, text)
	{
		if (!this.ready()) return false;

		BXDesktopSystem.Log(filename, text);

		return true;
	}

	Desktop.prototype.createWindow = function (name, callback, reuse)
	{
		reuse = typeof reuse === "boolean"? reuse: false;

		if (reuse)
		{
			var popup = BX.desktop.findWindow(name);
			if (popup)
			{
				BX.desktop.windowCommand(popup, 'show');
				return true;
			}
		}

		BXDesktopSystem.GetWindow(name, callback);
	}

	Desktop.prototype.closeWindow = function (names)
	{
		if (!Array.isArray(names))
		{
			names = [names];
		}

		names.forEach(function(name) {
			var popup = BX.desktop.findWindow(name);
			if (!popup)
			{
				return true;
			}

			BX.desktop.windowCommand(popup, 'close');
		});

		return true;
	}

	Desktop.prototype.getWindowTitle = function (title)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.GetProperty("title");

		return true;
	}

	Desktop.prototype.setWindowTitle = function (title)
	{
		if (!this.ready()) return false;

		if (typeof(title) == 'undefined')
			return false;

		title = BX.util.trim(title);
		if (title.length <= 0)
			return false;

		BXDesktopWindow.SetProperty("title", title);

		return true;
	}

	Desktop.prototype.setWindowPosition = function (params)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.SetProperty("position", params);

		return true;
	}

	Desktop.prototype.setWindowName = function (name)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.SetProperty("windowName", name.toString());

		return true;
	}

	Desktop.prototype.setWindowSize = function (params)
	{
		if (!this.ready()) return false;

		//BXDesktopWindow.SetProperty("clientSize", params);
		if (params.Width && params.Height)
			BX.MessengerWindow.adjustSize(params.Width, params.Height);

		return true;
	}

	Desktop.prototype.setWindowMinSize = function (params)
	{
		if (!this.ready())
			return false;

		if (!params.Width || !params.Height)
			return false;

		BX.MessengerWindow.minWidth = params.Width;
		BX.MessengerWindow.minHeight = params.Height;

		BXDesktopWindow.SetProperty("minClientSize", params);

		return true;
	}

	Desktop.prototype.addTrayMenuItem = function (params)
	{
		if (!this.ready()) return false;

		BXDesktopWindow.AddTrayMenuItem(params)

		return true;
	}

	Desktop.prototype.finalizeTrayMenu = function ()
	{
		if (!this.ready()) return false;

		BXDesktopWindow.EndTrayMenuItem();

		return true;
	}

	Desktop.prototype.preventShutdown = function ()
	{
		if (!this.ready()) return false;

		BXDesktopSystem.PreventShutdown();

		return true;
	}

	Desktop.prototype.diskReportStorageNotification = function (command, params)
	{
		if (!this.ready()) return false;

		BXDesktopSystem.ReportStorageNotification(command, params);

		return true;
	}

	Desktop.prototype.diskOpenFolder = function ()
	{
		if (!this.ready()) return false;

		BXFileStorage.OpenFolder();

		return true;
	}

	/* Interface */
	Desktop.prototype.addSeparator = function (params)
	{
		return BX.MessengerWindow.addSeparator(params);
	}

	Desktop.prototype.addTab = function (params)
	{
		return BX.MessengerWindow.addTab(params);
	}

	Desktop.prototype.changeTab = function (tabId, force)
	{
		return BX.MessengerWindow.changeTab(tabId, force);
	}

	Desktop.prototype.closeTab = function (tabId)
	{
		return BX.MessengerWindow.closeTab(tabId);
	}

	Desktop.prototype.setTabBadge = function (tabId, value)
	{
		return BX.MessengerWindow.setTabBadge(tabId, value);
	}

	Desktop.prototype.updateTabBadge = function ()
	{
		if (!this.ready())
			return false;

		var value = 0;
		for (var tabId in BX.MessengerWindow.tabItems)
		{
			if (BX.MessengerWindow.tabItems[tabId].badge)
				value += BX.MessengerWindow.tabItems[tabId].badge;
		}

		if (value <= 0)
			value = '';
		else if (value > 50)
			value = '50+';

		BXDesktopSystem.SetTabBadge(this.getContextWindow(), value+'');
	}

	Desktop.prototype.setTabContent = function (tabId, content)
	{
		return BX.MessengerWindow.setTabContent(tabId, content);
	}

	Desktop.prototype.isActiveWindow = function ()
	{
		if (!this.ready())
			return false;

		return BXDesktopSystem.IsActiveTab();
	}

	Desktop.prototype.getActiveWindow = function ()
	{
		if (!this.ready())
			return 1;

		return BXDesktopSystem.ActiveTab();
	}

	Desktop.prototype.getContextWindow = function ()
	{
		if (!this.ready())
			return 1;

		if(this.isActiveWindow())
		{
			return this.getActiveWindow();
		}
		else
		{
			if(this.getActiveWindow() == TAB_CP)
			{
				return TAB_B24NET;
			}
			else
			{
				return TAB_CP;
			}
		}
	}

	Desktop.prototype.setActiveWindow = function (windowId)
	{
		if (!this.ready())
			return false;

		if (typeof(windowId) != 'undefined')
		{
			if (windowId == TAB_B24NET || windowId == TAB_CP)
			{
				BXDesktopSystem.SetActiveTabI(windowId);
			}
		}
		else
		{
			BXDesktopSystem.SetActiveTab();
		}
	}

	Desktop.prototype.getUserInfo = function()
	{
		return BX.MessengerWindow.getUserInfo();
	}

	BX.desktop = new Desktop();
})(window);
