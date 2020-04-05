(function() {

if (BX.IM)
	return;

BX.IM = function(domNode, params)
{
	if(typeof(BX.message("USER_TZ_AUTO")) == 'undefined' || BX.message("USER_TZ_AUTO") == 'Y')
		BX.message({"USER_TZ_OFFSET": -(new Date).getTimezoneOffset()*60-parseInt(BX.message("SERVER_TZ_OFFSET"))});

	if (typeof(BX.MessengerCommon) != 'undefined')
		BX.MessengerCommon.setBxIm(this);

	this.mobileVersion = false;
	this.mobileAction = 'none';

	this.revision = 120; // api version - im/lib/revision.php
	this.ieVersion = BX.browser.DetectIeVersion();
	this.errorMessage = '';
	this.animationSupport = true;
	this.context = params.context;
	this.design = params.design;
	this.bitrixNetwork = params.bitrixNetwork;
	this.bitrixNetwork2 = params.bitrixNetwork2;
	this.bitrixOpenLines = params.bitrixOpenLines;
	this.bitrix24 = params.bitrix24;
	this.isAdmin = params.isAdmin;
	this.bitrixIntranet = params.bitrixIntranet;
	this.bitrix24net = params.bitrix24net;
	this.bitrixXmpp = params.bitrixXmpp;
	this.bitrixMobile = params.bitrixMobile;
	this.colors = params.colors;
	this.colorsHex = params.colorsHex;
	this.ppStatus = params.ppStatus;
	this.ppServerStatus = this.ppStatus? params.ppServerStatus: false;
	this.updateStateInterval = params.updateStateInterval;
	this.desktopStatus = params.desktopStatus || false;
	this.desktopVersion = params.desktopVersion;
	this.desktopProtocolVersion = 2;
	this.xmppStatus = params.xmppStatus;
	this.lastRecordId = 0;
	this.userId = params.userId;
	this.userEmail = params.userEmail;
	this.userColor = params.userColor;
	this.userGender = params.userGender;
	this.userExtranet = params.userExtranet;
	this.options = params.options || {};
	this.path = params.path;
	this.language = params.language || 'en';
	this.init = typeof(params.init) != 'undefined'? params.init: true;
	this.windowFocus = true;
	this.windowFocusTimeout = null;
	this.extraBind = null;
	this.extraOpen = false;
	this.dialogOpen = false;
	this.notifyOpen = false;
	this.adjustSizeTimeout = null;
	this.tryConnect = true;
	this.openSettingsFlag =  typeof(params.openSettings) != 'undefined'? params.openSettings: false;
	this.popupConfirm = null;

	this.settings = params.settings;
	this.settingsDisabled = {};
	this.settingsView = params.settingsView || {common:{}, notify:{}, privacy:{}};
	this.settingsNotifyBlocked = params.settingsNotifyBlocked || {};
	this.settingsTableConfig = {};
	this.settingsSaveCallback = {};
	this.settingsCameraTestMediaStream = null;
	this.micTestMediaStream = null;
	this.settingsLevelMeter = null;
	this.saveSettingsTimeout = {};
	this.popupSettings = null;
	if (params.users && params.users[this.userId])
		params.users[this.userId].status = this.settings.status;

	this.pathToAjax = params.path.im? params.path.im: '/bitrix/components/bitrix/im.messenger/im.ajax.php';
	this.pathToCallAjax = params.path.call? params.path.call: '/bitrix/components/bitrix/im.messenger/call.ajax.php';
	this.pathToFileAjax = params.path.file? params.path.file: '/bitrix/components/bitrix/im.messenger/file.ajax.php';
	this.pathToBlankImage = '/bitrix/js/im/images/blank.gif';

	this.audio = {};
	this.audio.reminder = null;
	this.audio.newMessage1 = null;
	this.audio.newMessage2 = null;
	this.audio.send = null;
	this.audio.dialtone = null;
	this.audio.ringtone = null;
	this.audio.start = null;
	this.audio.stop = null;
	this.audio.current = null;
	this.audio.timeout = {};

	this.mailCount = params.mailCount;
	this.notifyCount = params.notifyCount || 0;
	this.messageCount = params.messageCount || 0;
	this.linesCount = params.linesCount || 0;

	this.quirksMode = (BX.browser.IsIE() && !BX.browser.IsDoctype() && (/MSIE 8/.test(navigator.userAgent) || /MSIE 9/.test(navigator.userAgent)));
	this.platformName = BX.browser.IsMac()? 'OS X': (/windows/.test(navigator.userAgent.toLowerCase())? 'Windows': '');

	if (BX.browser.IsIE() && !BX.browser.IsIE9() && (/MSIE 7/i.test(navigator.userAgent)))
		this.errorMessage = BX.message('IM_M_OLD_BROWSER');

	if (this.context == 'POPUP-FULLSCREEN' && BX.browser.IsMobile())
	{
		this.design = 'POPUP';
	}

	if (this.context == 'DESKTOP' || this.context == 'FULLSCREEN'  || this.context == 'PAGE' || this.context == 'DIALOG' || this.context == 'LINES' || this.context == 'POPUP-FULLSCREEN')
	{
		if (this.context == 'DESKTOP')
		{
			BX.desktop.init({context: this.context, design: this.design, bxim: this});
		}
		if (BX.MessengerCommon.isPage())
		{
			BX.MessengerWindow.init({context: this.context, design: this.design, bxim: this});
		}
	}

	this.desktop = new BX.IM.Desktop(this, {
		'desktop': params.desktop
	});

	this.webrtc = new BX.IM.WebRTC(this, {
		'desktopClass': this.desktop,
		'callServerEnabled': params.webrtc && params.webrtc.callServerEnabled == 'Y' || false,
		'phoneEnabled': params.webrtc && params.webrtc.phoneEnabled || false,
		'phoneCanPerformCalls': params.webrtc && params.webrtc.phoneCanPerformCalls == 'Y' || false,
		'phoneCanCallUserNumber': params.webrtc && params.webrtc.phoneCanCallUserNumber || false,
		'phoneSipAvailable': params.webrtc && params.webrtc.phoneSipAvailable || 0,
		'phoneDeviceActive': params.webrtc && params.webrtc.phoneDeviceActive || 'N',
		'phoneDeviceCall': params.webrtc && params.webrtc.phoneDeviceCall || 'Y',
		'phoneCrm': params.phoneCrm && params.phoneCrm || {},
		'phoneLines': params.webrtc && params.webrtc.phoneLines || {},
		'phoneDefaultLineId': params.webrtc && params.webrtc.phoneDefaultLineId || '',
		'phoneAvailableLines': params.webrtc && params.webrtc.availableLines || [],
		'turnServer': params.webrtc && params.webrtc.turnServer || '',
		'turnServerFirefox': params.webrtc && params.webrtc.turnServerFirefox || '',
		'turnServerLogin': params.webrtc && params.webrtc.turnServerLogin || '',
		'turnServerPassword': params.webrtc && params.webrtc.turnServerPassword || '',
		'panel': domNode != null? domNode: BX.create('div')
	});

	BX.PhoneCallView.setDefaults({
		restApps: params.webrtc && params.webrtc.phoneCallCardRestApps || [],
		callInterceptAllowed: params.webrtc && params.webrtc.phoneCanInterceptCall || false
	});

	this.desktop.webrtc = this.webrtc;

	if (this.init)
	{
		if (BX.MessengerCommon.isDesktop())
		{
			this.windowTitle = this.bitrixIntranet? (!BX.browser.IsMac()? BX.message('IM_DESKTOP_B24_TITLE'): BX.message('IM_DESKTOP_B24_OSX_TITLE')): BX.message('IM_WM');
			BX.desktop.setWindowTitle(this.windowTitle);
		}
		else
		{
			this.windowTitle = document.title;
		}
	}

	for (var i in params.notify)
	{
		params.notify[i].date = new Date(params.notify[i].date);
		if (parseInt(i) > this.lastRecordId)
			this.lastRecordId = parseInt(i);
	}
	for (var i in params.message)
	{
		params.message[i].date = new Date(params.message[i].date);
		if (parseInt(i) > this.lastRecordId)
			this.lastRecordId = parseInt(i);
	}
	for (var i in params.recent)
	{
		params.recent[i].date = new Date(params.recent[i].date);
	}
	if (BX.browser.SupportLocalStorage())
	{
		BX.addCustomEvent(window, "onLocalStorageSet", BX.proxy(this.storageSet, this));

		var lri = BX.localStorage.get('lri');
		if (parseInt(lri) > this.lastRecordId)
			this.lastRecordId = parseInt(lri);

		BX.garbage(function(){
			BX.localStorage.set('lri', this.lastRecordId, 60);
		}, this);
	}

	this.notifyManager = new BX.IM.NotifyManager(this, {});
	this.notify = new BX.Notify(this, {
		'desktopClass': this.desktop,
		'webrtcClass': this.webrtc,
		'domNode': domNode,
		'counters': params.counters || {},
		'mailCount': params.mailCount || 0,
		'notify': params.notify || {},
		'unreadNotify' : params.unreadNotify || {},
		'flashNotify' : params.flashNotify || {},
		'countNotify' : params.countNotify || 0,
		'loadNotify' : params.loadNotify
	});
	this.webrtc.notify = this.notify;
	this.desktop.notify = this.notify;

	this.disk = new BX.IM.DiskManager(this, {
		notifyClass: this.notify,
		desktopClass: this.desktop,
		files: params.files || {},
		enable: params.disk && params.disk.enable,
		enableExternal: params.disk && params.disk.external
	});
	this.notify.disk = this.disk;
	this.webrtc.disk = this.disk;
	this.desktop.disk = this.disk;

	this.messenger = new BX.Messenger(this, {
		'openChatEnable': params.openChatEnable,
		'updateStateInterval': params.updateStateInterval,
		'notifyClass': this.notify,
		'webrtcClass': this.webrtc,
		'desktopClass': this.desktop,
		'diskClass': this.disk,
		'externalRecentList': params.externalRecentList,
		'recent': params.recent,
		'users': params.users || {},
		'businessUsers': params.businessUsers || false,
		'openlines': params.openlines || false,
		'groups': params.groups || {},
		'userChatBlockStatus': params.userChatBlockStatus || {},
		'userChatOptions': params.userChatOptions || {},
		'userInGroup': params.userInGroup || {},
		'currentTab' : params.currentTab || 0,
		'generalChatId' : params.generalChatId || 0,
		'canSendMessageGeneralChat' : params.canSendMessageGeneralChat || false,
		'chat' : params.chat || {},
		'userInChat' : params.userInChat || {},
		'userChat' : params.userChat || {},
		'hrphoto' : params.hrphoto || {},
		'message' : params.message || {},
		'showMessage' : params.showMessage || {},
		'unreadMessage' : params.unreadMessage || {},
		'flashMessage' : params.flashMessage || {},
		'countMessage' : params.countMessage || 0,
		'tooltipShowed' : params.tooltipShowed || {},
		'bot' : params.bot || {},
		'command' : params.command || [],
		'textareaIcon' : params.textareaIcon || [],
		'smile' : params.smile || false,
		'smileSet' : params.smileSet || false,
		'history' : params.history || {},
		'openMessenger' : typeof(params.openMessenger) != 'undefined'? params.openMessenger: false,
		'openHistory' : typeof(params.openHistory) != 'undefined'? params.openHistory: false,
		'openNotify' : typeof(params.openNotify) != 'undefined'? params.openNotify: false
	});
	this.webrtc.messenger = this.messenger;
	this.notify.messenger = this.messenger;
	this.desktop.messenger = this.messenger;
	this.disk.messenger = this.messenger;

	if (this.init)
	{
		BX.addCustomEvent(window, "onImUpdateCounterNotify", BX.proxy(this.updateCounter, this));
		BX.addCustomEvent(window, "onImUpdateCounterMessage", BX.proxy(this.updateCounter, this));
		BX.addCustomEvent(window, "onImUpdateCounterMail", BX.proxy(this.updateCounter, this));
		BX.addCustomEvent(window, "onImUpdateCounter", BX.proxy(this.updateCounter, this));

		BX.bind(window, "blur", BX.delegate(function(){ this.changeFocus(false);}, this));
		BX.bind(window, "focus", this.setFocusFunction = BX.delegate(function(){
			if (this.windowFocus)
				return false;

			if (BX.MessengerCommon.isDesktop() && !BX.desktop.isActiveWindow())
				return false;

			this.changeFocus(true);
			if (this.isFocus() && this.messenger.unreadMessage[this.messenger.currentTab] && this.messenger.unreadMessage[this.messenger.currentTab].length>0)
				BX.MessengerCommon.readMessage(this.messenger.currentTab);

			if (this.isFocus('notify'))
			{
				if (this.notify.unreadNotifyLoad)
					this.notify.loadNotify();
				else if (this.notify.notifyUpdateCount > 0)
					this.notify.viewNotifyAll();
			}
		}, this));

		if (BX.MessengerCommon.isDesktop())
			BX.bind(window, "click", this.setFocusFunction);

		BX.addCustomEvent("onPullEvent-xmpp", BX.delegate(function(command, params)
		{
			if (command == 'lastActivityDate')
			{
				this.xmppStatus = params.timestamp > 0;
			}
		}, this));

		this.updateCounter();
		BX.onCustomEvent(window, 'onImInit', [this]);
	}

	if (this.openSettingsFlag !== false)
		this.openSettings(this.openSettingsFlag == 'true'? {}: {'onlyPanel': this.openSettingsFlag.toString().toLowerCase()});
};

BX.IM.prototype.isFocus = function(context)
{
	context = typeof(context) == 'undefined'? 'dialog': context;
	if (!BX.MessengerCommon.isPage() && (this.messenger == null || this.messenger.popupMessenger == null))
		return false;

	if (context == 'dialog')
	{
		if (BX.MessengerCommon.isPage() && BX.MessengerWindow.getCurrentTab() != 'im' && BX.MessengerWindow.getCurrentTab() != 'im-phone' && BX.MessengerWindow.getCurrentTab() != 'im-ol')
			return false;
		if (this.messenger && !BX.MessengerCommon.isScrollMax(this.messenger.popupMessengerBody, 200))
			return false;
		if (this.dialogOpen == false)
			return false;
	}
	else if (context == 'notify')
	{
		if (BX.MessengerCommon.isPage() && BX.MessengerWindow.getCurrentTab() != 'notify' && BX.MessengerWindow.getCurrentTab() != 'im-phone')
			return false;
		if (this.notifyOpen == false)
			return false;
	}

	if (this.quirksMode || (BX.browser.IsIE() && !BX.browser.IsIE9()))
		return true;

	return this.windowFocus;
};

BX.IM.prototype.changeFocus = function (focus)
{
	this.windowFocus = typeof(focus) == "boolean"? focus: false;
	return this.windowFocus;
};

BX.IM.prototype.playSound = function(sound, force)
{
	force = force? true: false;
	if (!force && (!this.init || this.webrtc.callActive))
		return false;

	var whiteList = {'start': true, 'dialtone': true, 'ringtone': true};
	if (!this.settings.enableSound && !whiteList[sound])
		return false;

	BX.localStorage.set('mps', true, 1);

	try{
		this.stopSound();
		this.audio.current = this.audio[sound];
		var result = this.audio[sound].play();
		if(window.Promise && result instanceof Promise)
		{
			result.catch(function(e)
			{
				BXIM.audio.current = null;
			});
		}
	}
	catch(e)
	{
		this.audio.current = null
	}

};

BX.IM.prototype.repeatSound = function(sound, time)
{
	time = parseInt(time) || 1000;
	time = time >= 1000? time: 1000;

	if (this.audio.timeout[sound])
		clearTimeout(this.audio.timeout[sound]);

	if (BX.MessengerCommon.isDesktop() || !this.desktopStatus)
		this.playSound(sound);

	this.audio.timeout[sound] = setTimeout(BX.delegate(function(){
		this.repeatSound(sound, time);
	}, this), time);
};

BX.IM.prototype.stopRepeatSound = function(sound, send)
{
	send = send != false;
	if (send)
		BX.localStorage.set('mrss', {sound: sound}, 1);

	if (this.audio.timeout[sound])
		clearTimeout(this.audio.timeout[sound]);

	if (!this.audio[sound])
		return false;

	this.audio[sound].pause();
	this.audio[sound].currentTime = 0;
};

BX.IM.prototype.stopSound = function()
{
	if (this.audio.current)
	{
		this.audio.current.pause();
		this.audio.current.currentTime = 0;
	}
};

BX.IM.prototype.autoHide = function(e)
{
	if (this.autoHideDisable)
		return true;

	e = e||window.event;
	if (e.which == 1)
	{
		if (this.popupSettings != null)
			this.popupSettings.destroy();
		else if (this.messenger.popupHistory != null)
			this.messenger.popupHistory.destroy();
		else if (BX.DiskFileDialog && BX.DiskFileDialog.popupWindow != null)
			BX.DiskFileDialog.popupWindow.destroy();
		else if (!this.webrtc.callInit && this.messenger.popupMessenger != null)
			this.messenger.popupMessenger.destroy();
	}
};

BX.IM.prototype.updateCounter = function(count, type)
{
	if (type == 'MESSAGE')
		this.messageCount = count;
	else if (type == 'NOTIFY')
		this.notifyCount = count;
	else if (type == 'MAIL')
		this.mailCount = count;

	var sumCount = 0;
	if (this.notifyCount > 0)
		sumCount += parseInt(this.notifyCount);
	if (this.messageCount > 0)
		sumCount += parseInt(this.messageCount);
	if (this.linesCount > 0)
		sumCount += parseInt(this.linesCount);

	if (BX.MessengerCommon.isPage())
	{
		var sumLabel = '';
		if (sumCount > 99)
			sumLabel = '99+';
		else if (sumCount > 0)
			sumLabel = sumCount;

		var iconTitle = BX.message('IM_DESKTOP_UNREAD_EMPTY');
		if (this.notifyCount > 0 && this.messageCount+this.linesCount > 0)
			iconTitle = BX.message('IM_DESKTOP_UNREAD_MESSAGES_NOTIFY');
		else if (this.notifyCount > 0)
			iconTitle = BX.message('IM_DESKTOP_UNREAD_NOTIFY');
		else if (this.messageCount+this.linesCount > 0)
			iconTitle = BX.message('IM_DESKTOP_UNREAD_MESSAGES');
		else if (this.notify != null && this.notify.getCounter('**') > 0)
			iconTitle = BX.message('IM_DESKTOP_UNREAD_LF');

		if (BX.MessengerCommon.isDesktop())
		{
			BX.desktop.setIconTooltip(iconTitle);
			BX.desktop.setIconBadge(sumLabel, this.messageCount+this.linesCount > 0);
		}
	}
	if (BX.MessengerCommon.isPage() && this.notify)
	{
		var lfCounter = this.notify.getCounter('**');
		BX.MessengerWindow.setTabBadge('im-lf', lfCounter);
	}
	BX.onCustomEvent(window, 'onImUpdateSumCounters', [sumCount, 'SUM']);

	if (this.settings.status != 'dnd' && !this.desktopStatus && sumCount > 0)
	{
		if (!BX.MessengerCommon.isDesktop() && document.title != '('+sumCount+') '+this.windowTitle)
			document.title = '('+sumCount+') '+this.windowTitle;

		if (this.notify.panelButtonMessage)
		{
			if (this.messageCount > 0)
				BX.addClass(this.notify.panelButtonMessage, 'bx-notifier-message-new');
			else
				BX.removeClass(this.notify.panelButtonMessage, 'bx-notifier-message-new');
		}
	}
	else
	{
		if (!BX.MessengerCommon.isDesktop() && document.title != this.windowTitle)
			document.title = this.windowTitle;

		if (this.notify.panelButtonMessage)
		{
			if (this.messageCount <= 0 || this.settings.status == 'dnd' || this.desktopStatus)
			{
				BX.removeClass(this.notify.panelButtonMessage, 'bx-notifier-message-new');
			}
		}
	}
};

BX.IM.prototype.openNotify = function(params)
{
	force = params && params.force == true;

	if (!this.settings.openDesktopFromPanel)
	{
		BX.defer(function() {
			if (BX.MessengerCommon.isPage())
			{
				if (BX.MessengerWindow.currentTab != 'notify')
				{
					BX.MessengerWindow.changeTab('im', true);
				}
				this.notify.openNotify(false, true);
				setTimeout(function(){
					BX.MessengerWindow.changeTab('notify', true);
				},100)
			}
			else
			{
				this.notify.openNotify(false, true);
			}
		}, this)();

		return false;
	}

	BX.desktopUtils.runningCheck(function() {
		BX.desktopUtils.goToBx("bx://notify");
	}, BX.defer(function() {
		if (BX.MessengerCommon.isPage())
		{
			if (BX.MessengerWindow.currentTab != 'notify')
			{
				BX.MessengerWindow.changeTab('im', true);
			}
			this.notify.openNotify(false, true);

			setTimeout(function(){
				BX.MessengerWindow.changeTab('notify', true);
			},100)
		}
		else
		{
			this.notify.openNotify(false, true);
		}
	}, this));
};

BX.IM.prototype.closeNotify = function()
{
	BX.onCustomEvent(window, 'onImNotifyWindowClose', []);
	if (this.messenger.popupMessenger != null && !this.webrtc.callInit)
		this.messenger.popupMessenger.destroy();
};

BX.IM.prototype.toggleNotify = function()
{
	if (this.isOpenNotify())
		this.closeNotify();
	else
		this.openNotify();
};

BX.IM.prototype.isOpenNotify = function()
{
	return this.notifyOpen;
};

BX.IM.prototype.callTo = function(userId, video)
{
	video = !(typeof(video) != 'undefined' && !video);

	BX.desktopUtils.runningCheck(function(){
		BX.desktopUtils.goToBx("bx://callto/"+(video? 'video': 'audio')+"/"+userId+(BXIM.bitrix24net? '/bitrix24net/Y':''));
	}, BX.delegate(function(){
		this.webrtc.callInvite(userId, video);
	}, this));
};

BX.IM.prototype.sendMessage = function(dialogId, message)
{
	if (!message && !dialogId)
		return false;

	if (!message)
	{
		message = dialogId;
		dialogId = this.messenger.currentTab;
	}

	var previousMessage = this.messenger.popupMessengerTextarea.value;
	this.messenger.popupMessengerTextarea.value = message;
	this.messenger.sendMessage(dialogId);

	setTimeout(BX.delegate(function(){
		this.messenger.popupMessengerTextarea.value = previousMessage;
		this.messenger.textareaCheckText();
	}, this), 10);

	return true;
}

BX.IM.prototype.putMessage = function(message)
{
	BX.addClass(this.messenger.popupMessengerTextarea.parentNode, 'bx-messenger-textarea-focus');

	this.messenger.popupMessengerTextarea.focus();
	this.messenger.insertTextareaText(this.messenger.popupMessengerTextarea, message+' ', false);
	this.messenger.textareaHistory[this.messenger.currentTab] = message+' ';

	return true;
}

BX.IM.prototype.phoneTo = function(number, params)
{
	params = params? params: {};
	var lineId = params['LINE_ID'] ? params['LINE_ID'] : this.webrtc.phoneDefaultLineId;
	if (typeof(params) != 'object')
	{
		try { params = JSON.parse(params); } catch(e) { params = {} }
	}

	if(this.webrtc.isRestLine(lineId))
	{
		BX.MessengerCommon.phoneStartCallViaRestApp(number, lineId, params);
		return true;
	}

	if (!BX.MessengerCommon.isDesktop() && this.desktopStatus && this.desktopVersion >= 18)
	{
		var stringParams = '';
		if (params)
		{
			for (var i in params)
			{
				stringParams = stringParams+'!!'+i+'!!'+params[i];
			}
			stringParams = '/params/'+stringParams.substr(2);
		}
		if (this.webrtc.popupKeyPad)
			this.webrtc.popupKeyPad.close();

		BX.desktopUtils.runningCheck(function(){
			BX.desktopUtils.goToBx("bx://callto/phone/"+escape(number)+stringParams)
		}, BX.delegate(function(){
			this.webrtc.phoneCall(number, params);
		}, this));
	}
	else
	{
		this.webrtc.phoneCall(number, params);
	}
	return true;
};

/**
 * Starts Call List mode
 * @param callListId int
 * @param params object: {webformId (int) }
 * @returns {boolean}
 */
BX.IM.prototype.startCallList = function(callListId, params)
{
	params = params? params: {};
	callListId = parseInt(callListId);
	if(callListId == 0)
		return;

	if (!this.desktop.ready() && this.desktopStatus && this.desktopVersion >= 18)
	{
		BX.desktopUtils.runningCheck(
			function()
			{
				BX.desktopUtils.goToBx("bx://calllist/id/"+callListId+/params/+BX.desktopUtils.encodeParams(params))
			},
			BX.delegate(function()
			{
				this.webrtc.startCallList(callListId, params);
			}, this)
		);
	}
	else
	{
		this.webrtc.startCallList(callListId, params);
	}
	return true;

};

BX.IM.prototype.checkCallSupport = function()
{
	return this.webrtc.callSupport();
};

BX.IM.prototype.addPopupMenuModifier = function(func)
{
	this.messenger.popupPopupMenuModifyFunction.push(func);
	return true;
}

BX.IM.prototype.openMessengerSlider = function(dialogId, params)
{
	params = params || {};
	params.SLIDER = 'Y';

	BX.defer(function() {
		if (dialogId && dialogId.toString().substr(0,4) == 'imol')
		{
			this.messenger.linesOpenMessenger(dialogId.toString().substr(5), params);
		}
		else
		{
			this.messenger.openMessengerSlider(dialogId, params);
		}
	}, this)();
};

BX.IM.prototype.openMessenger = function(userId, tab, openThis)
{
	userId = userId === false? false: userId;
	openThis = openThis? true: false;

	if (!this.settings.openDesktopFromPanel || openThis)
	{
		BX.defer(function() {
			if (userId && userId.toString().substr(0,4) == 'imol')
			{
				this.messenger.linesOpenMessenger(userId.toString().substr(5));
			}
			else
			{
				this.messenger.openMessenger(userId);
				if (tab)
				{
					BX.MessengerWindow.changeTab(tab, true);
				}
			}
		}, this)();

		return false;
	}

	BX.desktopUtils.runningCheck(function() {
		BX.desktopUtils.goToBx(userId === false? "bx://messenger": "bx://messenger/dialog/"+encodeURIComponent(userId)+"/tab/"+tab);
	}, BX.defer(function() {
		if (userId && userId.toString().substr(0,4) == 'imol')
		{
			this.messenger.linesOpenMessenger(userId.toString().substr(5));
		}
		else
		{
			this.messenger.openMessenger(userId);
			if (tab)
			{
				BX.MessengerWindow.changeTab(tab, true);
			}
		}
	}, this));

	return false;
};

BX.IM.prototype.closeMessenger = function()
{
	this.messenger.popupMessenger.close();
};

BX.IM.prototype.isOpenMessenger = function()
{
	return this.dialogOpen;
};

BX.IM.prototype.toggleMessenger = function()
{
	if (this.isOpenMessenger())
		this.closeMessenger();
	else if (this.extraOpen && !this.isOpenNotify())
		this.closeMessenger();
	else
		this.openMessenger(this.messenger.currentTab);
};

BX.IM.prototype.openHistory = function(userId)
{
	if (userId && userId.toString().substr(0,4) == 'imol')
	{
		setTimeout(BX.delegate(function(){
			this.messenger.linesOpenHistory(userId.toString().substr(5));
		},this), 300);
	}
	else
	{
		setTimeout(BX.delegate(function(){
			this.messenger.openHistory(userId)
		},this), 10);
	}
};

BX.IM.prototype.openContactList = function()
{
	this.messenger.openMessenger(false);
	setTimeout(BX.delegate(function(){
		this.messenger.popupContactListSearchInput.focus();
	},this), 200);
	return false;
};

BX.IM.prototype.closeContactList = function()
{
	return false;
};

BX.IM.prototype.isOpenContactList = function()
{
	return false;
};

BX.IM.prototype.checkRevision = function(revision)
{
	if (typeof(revision) == "number" && this.revision < revision)
	{
		if (BX.MessengerCommon.isDesktop() || this.context == 'PAGE')
		{
			console.log('NOTICE: Window reload, because REVISION UP ('+this.revision+' -> '+revision+')');
			BX.MessengerWindow.windowReload();
		}
		else
		{
			if (this.isOpenMessenger())
			{
				this.closeMessenger();
				this.openMessenger();
			}
			this.errorMessage = BX.message('IM_M_OLD_REVISION').replace('#WM_NAME#', BX.message('IM_WM'));
			this.tryConnect = false;
		}
		return false;
	}
	return true;
};

BX.IM.prototype.openSettings = function(params)
{
	if (this.messenger && this.messenger.popupMessengerConnectionStatusState != 'online')
		return false;

	params = typeof(params) == 'object'? params: {};
	if (this.popupSettings != null || !this.messenger)
		return false;

	if (!BX.MessengerCommon.isPage())
		this.messenger.setClosingByEsc(false);

	this.settingsSaveCallback = {};
	this.settingsTableConfig = {};

	var colors = [];
	if (this.colors)
	{
		for (var color in this.colors)
		{
			colors.push({'title': this.colors[color], 'value': color});
		}
	}

	var linesTabEnable = BX.MessengerCommon.isPage() && this.bitrixOpenLines? true: false;

	this.settingsView.common = {
		'title' : BX.message('IM_SETTINGS_COMMON'),
		'settings': [
			{'title': BX.message('IM_M_VIEW_LAST_MESSAGE_OFF'), 'type': 'checkbox', 'name':'viewLastMessage',  'checked': !this.settings.viewLastMessage, 'saveCallback': BX.delegate(function(element) { BX.MessengerCommon.recentListRedraw(); return !element.checked; }, this)},
			{'title': BX.message('IM_M_VIEW_OFFLINE_OFF'), 'type': 'checkbox', 'name':'viewOffline',  'checked': !this.settings.viewOffline, 'saveCallback': BX.delegate(function(element) { return !element.checked; }, this)},
			{'type': 'space'},
			linesTabEnable? {'title': BX.message('IM_M_VIEW_OL_LIST'), 'type': 'checkbox', 'name':'linesTabEnable',  'checked': this.settings.linesTabEnable, 'saveCallback': BX.delegate(function(element) { this.messenger.toggleLinesTab(element.checked); return element.checked; }, this)}: null,
			linesTabEnable? {'title': BX.message('IM_M_VIEW_OL_NEW'), 'type': 'checkbox', 'name':'linesNewGroupEnable',  'checked': this.settings.linesNewGroupEnable, 'saveCallback': BX.delegate(function(element) { this.messenger.toggleLinesNewGroup(element.checked); return element.checked; }, this)}: null,
			linesTabEnable? {'type': 'space'}: null,
			{'title': BX.message('IM_M_LLM'), 'type': 'checkbox', 'name':'loadLastMessage', 'checked': this.settings.loadLastMessage},
			{'title': BX.message('IM_M_LLN'), 'type': 'checkbox', 'name':'loadLastNotify', 'checked': this.settings.loadLastNotify},
			{'title': BX.message('IM_M_NAR'), 'type': 'checkbox', 'name':'notifyAutoRead', 'checked': this.settings.notifyAutoRead},
			{'type': 'space'},
			{'title': BX.message('IM_M_DESKTOP_BIG_SMILE_ON'), 'type': 'checkbox', 'name':'enableBigSmile', 'checked': this.settings.enableBigSmile},
			{'title': BX.message('IM_M_RICH_LINK_ON'), 'type': 'checkbox', 'name':'enableRichLink', 'checked': this.settings.enableRichLink},
			{'title': BX.message('IM_M_ENABLE_SOUND'), 'type': 'checkbox', 'name':'enableSound', 'checked': this.settings.enableSound},
			BX.MessengerCommon.isDesktop()? {'title': BX.message('IM_M_ENABLE_BIRTHDAY'), 'type': 'checkbox', 'checked': this.desktop.birthdayStatus(), 'callback': BX.delegate(function(){ this.desktop.birthdayStatus(!this.desktop.birthdayStatus()); }, this)}: null,
			{'title': BX.message('IM_M_KEY_SEND'), 'type': 'select', 'name':'sendByEnter', 'value': this.settings.sendByEnter?'Y':'N', items: [{title: (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter"), value: 'N'}, {title: 'Enter', value: 'Y'}], 'saveCallback': BX.delegate(function(element) { return element[element.selectedIndex].value == 'Y'; }, this)},
			//this.language=='ru' && BX.correctText? {'title': BX.message('IM_M_AUTO_CORRECT'), 'type': 'checkbox', 'name':'correctText', 'checked': this.settings.correctText }: null,
			{'type': 'space'},
			this.colors? {'title': BX.message('IM_M_USER_COLOR'), 'name': 'userColor', 'type': 'select', 'value': this.userColor, items: colors, skipSave: 'Y', 'saveCallback': BX.delegate(function(element){ BX.MessengerCommon.setColor(element.options[element.selectedIndex].value) }, this)}: null,
			this.desktopVersion? {'title': BX.message('IM_M_OPEN_DESKTOP_FROM_PANEL'), 'type': 'checkbox', 'name':'openDesktopFromPanel',  'checked': this.settings.openDesktopFromPanel}: null,
			BX.MessengerCommon.isDesktop()? {'title': BX.message('IM_M_DESKTOP_AUTORUN_ON'), 'type': 'checkbox', 'checked': BX.desktop.autorunStatus(), 'callback': BX.delegate(function(){ BX.desktop.autorunStatus(!BX.desktop.autorunStatus()); }, this)}: null
		]
	};
	this.settingsView.notify = {
		'title' : BX.message('IM_SETTINGS_NOTIFY'),
		'settings': [
			{'type': 'notifyControl'},
			{'type': 'table', name: 'notify', show: this.settings.notifyScheme == 'expert'},
			{'type': 'table', name: 'simpleNotify', show: this.settings.notifyScheme == 'simple'}
		]
	};

	this.settingsTableConfig['notify'] = {
		'condition': BX.delegate(function(){ return this.settingsTableConfig['notify'].rows.length > 0 }, this),
		'headers' : [
			'',
			BX.message('IM_SETTINGS_NOTIFY_SITE'),
			this.bitrixXmpp? BX.message('IM_SETTINGS_NOTIFY_XMPP'): false,
			BX.message('IM_SETTINGS_NOTIFY_EMAIL'),
			this.bitrixMobile? BX.message('IM_SETTINGS_NOTIFY_PUSH'): false
		],
		'rows' : [],
		'error_rows': BX.create("div", {children: [
			BX.create("div", {props: {className: "bx-messenger-content-item-progress"}}),
			BX.create("span", {props: {className: "bx-messenger-content-item-progress-with-text"}, html: BX.message('IM_SETTINGS_LOAD')}),
		]})
	};

	this.settingsTableConfig['simpleNotify'] = {
		'condition': BX.delegate(function(){  return this.settingsTableConfig['simpleNotify'].rows.length > 0 }, this),
		'headers' : [BX.message('IM_SETTINGS_SNOTIFY'), ''],
		'rows' : []
	};

	this.settingsView.privacy = {
		'title' : BX.message('IM_SETTINGS_PRIVACY'),
		'condition': BX.delegate(function(){ return !this.bitrixIntranet}, this),
		'settings': [
			{'title': BX.message('IM_SETTINGS_PRIVACY_MESS'), name: 'privacyMessage', 'type': 'select', items: [{title: BX.message('IM_SETTINGS_SELECT_1'), value: 'all'}, {title: BX.message('IM_SETTINGS_SELECT_2'), value: 'contact'}], 'value': this.settings.privacyMessage},
			{'title': BX.message('IM_SETTINGS_PRIVACY_CALL'), name: 'privacyCall', 'type': 'select', items: [{title: BX.message('IM_SETTINGS_SELECT_1'), value: 'all'}, {title: BX.message('IM_SETTINGS_SELECT_2'), value: 'contact'}], 'value': this.settings.privacyCall},
			{'title': BX.message('IM_SETTINGS_PRIVACY_CHAT'), name: 'privacyChat', 'type': 'select', items: [{title: BX.message('IM_SETTINGS_SELECT_1_2'), value: 'all'}, {title: BX.message('IM_SETTINGS_SELECT_2_2'), value: 'contact'}], 'value': this.settings.privacyChat},
			{'title': BX.message('IM_SETTINGS_PRIVACY_SEARCH'), name: 'privacySearch', 'type': 'select', items: [{title: BX.message('IM_SETTINGS_SELECT_1_3'), value: 'all'}, {title: BX.message('IM_SETTINGS_SELECT_2_3'), value: 'contact'}], 'value': this.settings.privacySearch},
			this.bitrix24net? {'title': BX.message('IM_SETTINGS_PRIVACY_PROFILE'), name: 'privacyProfile', 'type': 'select', items: [{title: BX.message('IM_SETTINGS_SELECT_1_3'), value: 'all'}, {title: BX.message('IM_SETTINGS_SELECT_2_3'), value: 'contact'}, {title: BX.message('IM_SETTINGS_SELECT_3_3'), value: 'nobody'}], 'value': this.settings.privacyProfile}: null
		]
	};
	this.settingsView.hardware = {
		'title' : BX.message('IM_SETTINGS_HARDWARE'),
		'settings': [
			{'title': BX.message('IM_SETTINGS_HARDWARE_MICROPHONE'), 'type': 'select', 'name':'defaultMicrophone', 'items': {}, 'callback': this.changeHardwareSettings.bind(this), 'saveCallback': function(e){if(!localStorage) return e.value; localStorage.setItem('bx-im-settings-default-microphone', e.value);} },
			{'type': 'space'},
			{'title': 'microphoneLevel', 'type': 'html', 'value': '<div id="bx-messenger-settings-hardware-microphone-level" class="bx-messenger-settings-level-meter-container"></div>'},
			{'title': BX.message('IM_SETTINGS_HARDWARE_AUTO_PARAMETERS_MICROPHONE'), 'type': 'checkbox', 'name': 'enableMicAutoParameters', 'checked': this.webrtc.enableMicAutoParameters, 'saveCallback': function(e){if(!localStorage) return e.checked; localStorage.setItem('bx-im-settings-enable-mic-auto-parameters', (e.checked ? 'Y' : 'N'));} },
			{'type': 'space'},
			{'title': BX.message('IM_SETTINGS_HARDWARE_SPEAKER'), 'type': 'select', 'name':'defaultSpeaker', 'items': {}, 'saveCallback': function(e){if(!localStorage) return e.value; localStorage.setItem('bx-im-settings-default-speaker', e.value);} },
			{'type': 'space'},
			{'title': BX.message('IM_SETTINGS_HARDWARE_CAMERA'), 'type': 'select', 'name':'defaultCamera', 'items': {}, 'callback': this.changeHardwareSettings.bind(this), 'saveCallback': function(e){if(!localStorage) return e.value; localStorage.setItem('bx-im-settings-default-camera', e.value);} },
			{'type': 'space'},
			{'title': 'cameraImage', 'type': 'html', 'value': '<div id="bx-messenger-settings-hardware-camera-image"></div>'}
		],
		'click': BX.delegate(this.showHardwareSettings, this)
	};

	BX.onCustomEvent(this, "prepareSettingsView", []);

	if (params.onlyPanel && !this.settingsView[params.onlyPanel])
		return false;

	this.popupSettingsButtonSave = new BX.PopupWindowButton({
		text : BX.message('IM_SETTINGS_SAVE'),
		className : "popup-window-button-accept",
		events : { click : BX.delegate(function() {
			this.popupSettingsButtonSave.setClassName('popup-window-button');
			this.popupSettingsButtonSave.setName(BX.message('IM_SETTINGS_WAIT'));
			BX.hide(this.popupSettingsButtonClose.buttonNode);
			this.saveFormSettings();
			this.closeHardwareSettings();
		}, this) }
	});
	this.popupSettingsButtonClose = new BX.PopupWindowButton({
		text : BX.message('IM_SETTINGS_CLOSE'),
		className : "popup-window-button-close",
		events : { click : BX.delegate(function() { this.popupSettings.close(); BX.hide(this.popupSettingsButtonSave.buttonNode); BX.hide(this.popupSettingsButtonClose.buttonNode); this.closeHardwareSettings();}, this) }
	});
	this.popupSettingsBody = BX.create("div", { props : { className : "bx-messenger-settings"+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: this.prepareSettings({onlyPanel: params.onlyPanel? params.onlyPanel: false, active: params.active? params.active: false})});

	if (BX.MessengerCommon.isDesktop())
	{
		if (this.init)
		{
			this.desktop.openSettings(this.popupSettingsBody, "BXIM.openSettings("+JSON.stringify(params)+"); BX.desktop.resize(); ", params);
			return false;
		}
		else
		{
			this.popupSettings = new BX.PopupWindowDesktop();
			BX.addClass(this.popupSettingsBody, "bx-messenger-mark");
			this.desktop.drawOnPlaceholder(this.popupSettingsBody);
		}
	}
	else
	{
		this.popupSettings = new BX.PopupWindow('bx-messenger-popup-settings', null, {
			//parentPopup: this.messenger.popupMessenger,
			autoHide: false,
			zIndex: 200,
			overlay: {opacity: 50, backgroundColor: "#000000"},
			buttons: [this.popupSettingsButtonSave, this.popupSettingsButtonClose],
			draggable: {restrict: true},
			closeByEsc: true,
			events : {
				onPopupClose : function() { this.destroy(); },
				onPopupDestroy : BX.delegate(function() {
					this.popupSettings = null;
					if (!BX.MessengerCommon.isPage() && this.messenger.popupMesseger == null)
						BX.bind(document, "click", BX.proxy(this.autoHide, this));

					this.closeHardwareSettings();
					this.messenger.setClosingByEsc(true)
				}, this)
			},
			//titleBar: {content: BX.create('span', {props : { className : "bx-messenger-title" }, html: params.onlyPanel? this.settingsView[params.onlyPanel].title: BX.message('IM_SETTINGS')})},
			titleBar: params.onlyPanel? this.settingsView[params.onlyPanel].title: BX.message('IM_SETTINGS'),
			closeIcon : true,
			contentNoPaddings : true,
			contentColor : "white",
			content : this.popupSettingsBody
		});
		this.popupSettings.show();
		BX.addClass(this.popupSettings.popupContainer, "bx-messenger-mark");
		BX.bind(this.popupSettings.popupContainer, "click", BX.MessengerCommon.preventDefault);
	}

	BX.bindDelegate(this.popupSettingsBody, 'click', {className: 'bx-messenger-settings-tab'}, BX.delegate(function() {
		BX.onCustomEvent(window, 'onImSettingsTabShow', [BX.proxy_context.getAttribute('data-name')]);

		var elements = BX.findChildrenByClassName(BX.proxy_context.parentNode, "bx-messenger-settings-tab", false);
		for (var i = 0; i < elements.length; i++)
			BX.removeClass(elements[i], 'bx-messenger-settings-tab-active');
		BX.addClass(BX.proxy_context, 'bx-messenger-settings-tab-active');

		var elements = BX.findChildrenByClassName(BX.proxy_context.parentNode.nextSibling, "bx-messenger-settings-content", false);
		for (var i = 0; i < elements.length; i++)
		{
			if (parseInt(BX.proxy_context.getAttribute('data-id')) == i)
				BX.addClass(elements[i], 'bx-messenger-settings-content-active');
			else
				BX.removeClass(elements[i], 'bx-messenger-settings-content-active');
		}
		if (BX.MessengerCommon.isDesktop())
			this.desktop.autoResize();

	}, this));

	if (this.settings.notifyScheme == 'simple')
		this.GetSimpleNotifySettings();
	else
		this.GetNotifySettings();

	if (!BX.MessengerCommon.isDesktop())
		BX.bind(document, "click", BX.proxy(this.autoHide, this));
};

BX.IM.prototype.prepareSettings = function(params)
{
	params = typeof(params) == "object"? params: {};

	var items = [];

	var tabs = [];
	var tabActive = true;
	var i = 0;

	for (var tab in this.settingsView)
	{
		if (this.settingsView[tab].condition && !this.settingsView[tab].condition())
			continue;
		var events = {};
		if (this.settingsView[tab].click)
			events = {click: BX.delegate(this.settingsView[tab].click, this)};

		if (params.active && this.settingsView[params.active])
		{
			if (params.active == tab)
				tabActive = true;
			else
				tabActive = false;
		}
		if (tabActive)
		{
			BX.onCustomEvent(window, 'onImSettingsTabShow', [tab]);
		}

		tabs.push(BX.create('div', {attrs: {'data-id': i+"", 'data-name': tab}, props : { className : "bx-messenger-settings-tab"+(tabActive ? " bx-messenger-settings-tab-active": "") }, html: this.settingsView[tab].title, events: events}));
		tabActive = false;
		i++;
	}
	items.push(BX.create("div", {style: {display: !params.onlyPanel? 'block': 'none' }, props : { className: "bx-messenger-settings-tabs"}, children : tabs}));

	var tabs = [];
	var tabActive = true;
	for (var tab in this.settingsView)
	{
		if (this.settingsView[tab].condition && !this.settingsView[tab].condition())
			continue;

		if (params.active && this.settingsView[params.active])
		{
			if (params.active == tab)
				tabActive = true;
			else
				tabActive = false;
		}

		var table = [];
		if (this.settingsView[tab].settings)
		{
			var tableItems = [];
			for (var item = 0; item < this.settingsView[tab].settings.length; item++)
			{
				if (typeof(this.settingsView[tab].settings[item]) != 'object' || this.settingsView[tab].settings[item] === null)
					continue;

				if (this.settingsView[tab].settings[item].condition && !this.settingsView[tab].settings[item].condition())
					continue;

				if (this.settingsView[tab].settings[item].type == 'notifyControl' || this.settingsView[tab].settings[item].type == 'table' || this.settingsView[tab].settings[item].type == 'space')
				{
					tableItems.push(BX.create("tr", {children : [
						BX.create("td", {attrs: {'colspan': 2}, children: this.prepareSettingsItem(this.settingsView[tab].settings[item])})
					]}));
				}
				else if(this.settingsView[tab].settings[item].type === 'html')
				{
					tableItems.push(BX.create("tr", {children : [
						BX.create("td", {attrs: {colspan: 2}, children: this.prepareSettingsItem(this.settingsView[tab].settings[item])})
					]}));
				}
				else
				{
					tableItems.push(BX.create("tr", {children : [
						BX.create("td", {attrs: {'width': '55%'}, html: this.settingsView[tab].settings[item].title}),
						BX.create("td", {attrs: {'width': '45%'}, children: this.prepareSettingsItem(this.settingsView[tab].settings[item])})
					]}));
				}
			}
			if (tableItems.length > 0)
				table.push(BX.create("table", {attrs : {'cellpadding': '0', 'cellspacing': '0', 'border': '0', 'width': '100%'}, props : { className: "bx-messenger-settings-table bx-messenger-settings-table-style-"+tab}, children: tableItems}));
		}

		tabs.push(BX.create("div", {style: {display: params.onlyPanel? (params.onlyPanel == tab? 'block': 'none'): '' }, props : { id: 'bx-messenger-settings-content-'+tab, className: "bx-messenger-settings-content"+(tabActive? " bx-messenger-settings-content-active": "")}, children: table}));
		tabActive = false;
	}
	items.push(BX.create("div", {props : { className: "bx-messenger-settings-contents"}, children : tabs}));
	if (BX.MessengerCommon.isDesktop())
	{
		items.push(BX.create("div", {props : { className: "popup-window-buttons"}, children : [this.popupSettingsButtonSave.buttonNode, this.popupSettingsButtonClose.buttonNode]}));
	}

	return items;
};

BX.IM.prototype.prepareSettingsTable = function(tab)
{
	var config = this.settingsTableConfig[tab];

	if (!config.error_rows && config.condition && !BX.delegate(config.condition, this)())
		return null;

	var tableNotify = [];
	var tableHeaders = [];
	for (var item = 0; item < config.headers.length; item++)
	{
		if (typeof(config.headers[item]) == 'boolean')
			continue;
		tableHeaders.push(BX.create("th", {html: config.headers[item]}));
	}

	if (tableHeaders.length > 0)
		tableNotify.push(BX.create("tr", {children : tableHeaders}));

	if (config.error_rows && config.condition && !config.condition())
	{
		tableNotify.push(BX.create("tr", {children: [
			BX.create("td", {attrs: {'colspan': config.headers.length}, style: {textAlign: 'center'}, children: [config.error_rows]})
		]}));
		config.rows = [];
	}

	for (var item = 0; item < config.rows.length; item++)
	{
		var tableRows = [];
		for (var column = 0; column < config.rows[item].length; column++)
		{
			if (typeof(config.rows[item][column]) != 'object' || config.rows[item][column] === null)
				continue;

			var attrs = {};
			var props = {};
			if (config.rows[item][column].type == 'separator')
			{
				attrs = {'colspan': config.headers.length};
				props = {className: "bx-messenger-settings-table-sep"};
			}
			else if (config.rows[item][column].type == 'error')
			{
				attrs = {'colspan': config.headers.length};
				props = {className: "bx-messenger-settings-table-error"};
			}
			if (typeof(this.settingsDisabled[config.rows[item][column].name]) != 'undefined')
			{
				config.rows[item][column].disabled = this.settingsDisabled[config.rows[item][column].name];
			}
			tableRows.push(BX.create("td", {attrs: attrs, props:props, children: this.prepareSettingsItem(config.rows[item][column])}));
		}
		if (tableRows.length > 0)
			tableNotify.push(BX.create("tr", {children : tableRows}));
	}
	var currentTable = null;
	if (tableNotify.length > 0)
		currentTable = BX.create("table", {attrs : {'cellpadding': '0', 'cellspacing': '0', 'border': '0'}, props : { className: "bx-messenger-settings-table-extra bx-messenger-settings-table-extra-"+tab}, children: tableNotify});

	return currentTable;
};

BX.IM.prototype.prepareSettingsItem = function(params)
{
	var items = [];
	var config = BX.clone(params);

	var tooltipNode = null;
	if (config.tooltip)
	{
		tooltipNode = BX.create("span", {props: {className: "bx-messenger-settings-tooltip"}, attrs: {'data-tooltip': config.tooltip}, html: 'i', events: {
			'click': BX.delegate(function(e){
				this.messenger.tooltip(BX.proxy_context, BX.proxy_context.getAttribute('data-tooltip'), {angle: false, width: 300});
				BX.PreventDefault(e);
			}, this)
		}});
	}

	if (config.type == 'space')
	{
		items.push(BX.create("span", {props: {className: "bx-messenger-settings-space"}}));
	}
	if (config.type == 'text' || config.type == 'separator' || config.type == 'error')
	{
		items.push(BX.create("span", {html: config.title }))
	}
	if (config.type == 'html')
	{
		items.push(BX.create("div", { html: config.value }));
	}
	if (config.type == 'link')
	{
		if (config.callback)
			var events = { click: config.callback };

		items.push(BX.create("span", {props: {className: "bx-messenger-settings-link"}, attrs: config.attrs, html: config.title, events: events }))
	}
	if (config.type == 'checkbox')
	{
		if (config.callback)
			var events = { change: config.callback };

		if (typeof(config.checked) == 'undefined')
			config.checked = this.settings[config.name] != false;

		var attrs = { type: "checkbox", name: config.name? config.name: false, id: config.id? config.id: '', checked: config.checked == true? "true": false, disabled: config.disabled == true? "true": false};
		if (!config.skipSave && config.name)
			attrs['data-save'] = 1;

		var element = BX.create("input", {attrs: attrs, events: events });
		items.push(BX.create("div", {style: {whiteSpace: 'nowrap'}, children: [element, tooltipNode]}));

		if (config.saveCallback)
			this.settingsSaveCallback[config.name] = config.saveCallback;
	}
	else if (config.type == 'select')
	{
		if (config.callback)
			var events = { change: config.callback };

		var options = [];
		for (var i = 0; i < config.items.length; i++)
		{
			options.push(BX.create("option", {attrs : { value: config.items[i].value, selected: config.value == config.items[i].value? "true": false}, html: config.items[i].title}));
		}
		var attrs = { name: config.name};
		if (config.name)
			attrs['data-save'] = 1;
		var element = BX.create("select", {attrs : attrs, events: events, children: options});
		items.push(BX.create("div", {style: {whiteSpace: 'nowrap'}, children: [element, tooltipNode]}));

		if (config.saveCallback)
			this.settingsSaveCallback[config.name] = config.saveCallback;
	}
	else if (config.type == 'table')
	{
		items.push(BX.create("div", {attrs: {id: 'bx-messenger-settings-table-'+config.name, className: 'bx-messenger-settings-table-'+config.name}, style: {'display': config.show? 'block':'none'}, children: [this.prepareSettingsTable(config.name)]}));
	}
	else if (config.type == 'notifyControl')
	{
		var onChangeNotifyScheme = BX.delegate(function(){
			if (BX.proxy_context.value == 'simple')
			{
				BX.hide(BX('bx-messenger-settings-table-notify'));
				BX.show(BX('bx-messenger-settings-table-simpleNotify'));
				BX.show(BX('bx-messenger-settings-notify-clients'));

				this.GetSimpleNotifySettings();
			}
			else
			{
				BX.show(BX('bx-messenger-settings-table-notify'));
				BX.hide(BX('bx-messenger-settings-table-simpleNotify'));
				BX.hide(BX('bx-messenger-settings-notify-clients'));

				this.GetNotifySettings();
			}
		}, this);
		items.push(BX.create("div", {props : { className: "bx-messenger-settings-notify-type"}, children : [
			BX.create("input", {attrs : { id: 'notifySchemeSimpleValue', 'data-save': 1,  type: "radio", name: "notifyScheme", value: 'simple', checked: this.settings.notifyScheme == 'simple'}, events: {change: onChangeNotifyScheme}}),
			BX.create("label", {attrs : { 'for': 'notifySchemeSimpleValue'}, html: ' '+BX.message('IM_SETTINGS_NS_1')+' '}),
			BX.create("input", {attrs : { id: 'notifySchemeExpertValue', 'data-save': 1,  type: "radio", name: "notifyScheme", value: 'expert', checked: this.settings.notifyScheme == 'expert'}, events: {change: onChangeNotifyScheme}}),
			BX.create("label", {attrs : { 'for': 'notifySchemeExpertValue'}, html: ' '+BX.message('IM_SETTINGS_NS_2')+' '})
		]}));
		/*
		items.push(BX.create("div", {attrs: {id: "bx-messenger-settings-notify-important"}, style : {display: this.settings.notifyScheme == 'simple'? 'block':'none'}, props : { className: "bx-messenger-settings-notify-important"}, children : [
			BX.create("input", {attrs : { id: 'notifySchemeLevelImportantValue', 'data-save': 1,  type: "radio", name: "notifySchemeLevel", value: 'important', checked: this.settings.notifySchemeLevel == 'important'}}),
			BX.create("label", {attrs : { 'for': 'notifySchemeLevelImportantValue'}, html: ' '+BX.message('IM_SETTINGS_NSL_1')+' '}),
			BX.create("input", {attrs : { id: 'notifySchemeLevelNormalValue', 'data-save': 1,  type: "radio", name: "notifySchemeLevel", value: 'normal', checked: this.settings.notifySchemeLevel == 'normal'}}),
			BX.create("label", {attrs : { 'for': 'notifySchemeLevelNormalValue'}, html: ' '+BX.message('IM_SETTINGS_NSL_2')+' '})
		]}));
		*/
		items.push(BX.create("div", {attrs: {id: "bx-messenger-settings-notify-clients"}, style : {display: this.settings.notifyScheme == 'simple'? 'block':'none'}, props : { className: "bx-messenger-settings-notify-clients"}, children : [
			BX.create("div", {props: {className: 'bx-messenger-settings-notify-clients-title'}, html: BX.message('IM_SETTINGS_NC_1_NEW')}),
			BX.create("div", {props: {className: 'bx-messenger-settings-notify-clients-item'}, children: [
				BX.create("input", {
					attrs : { 'data-save': 1,  type: "checkbox", id: "notifySchemeSendSite", name: "notifySchemeSendSite", value: 'Y', checked: this.settings.notifySchemeSendSite},
					events : { change : function(e) {if (!this.checked) {BX('notifySchemeSendEmail').checked = false;} else {BX('notifySchemeSendEmail').checked = true;}}}
				}),
				BX.create("label", {attrs : {'for': "notifySchemeSendSite"}, html: ' '+BX.message('IM_SETTINGS_NC_2')+'<br />'})
			]}),
			this.bitrixXmpp? BX.create("div", {props: {className: 'bx-messenger-settings-notify-clients-item'}, children: [
				BX.create("input", {attrs : { 'data-save': 1,  type: "checkbox", id: "notifySchemeSendXmpp", name: "notifySchemeSendXmpp", value: 'Y', checked: this.settings.notifySchemeSendXmpp}}),
				BX.create("label", {attrs : {'for': "notifySchemeSendXmpp"}, html: ' '+BX.message('IM_SETTINGS_NC_3')+'<br />'})
			]}): null,
			BX.create("div", {props: {className: 'bx-messenger-settings-notify-clients-item'}, children: [
				BX.create("input", {
					attrs : { 'data-save': 1,  type: "checkbox", id: "notifySchemeSendEmail", name: "notifySchemeSendEmail", value: 'Y', checked: this.settings.notifySchemeSendEmail},
					events : { change : function(e) {if (this.checked) {BX('notifySchemeSendSite').checked = true;}}}
				}),
				BX.create("label", {attrs : {'for': "notifySchemeSendEmail"}, html: ' '+BX.message('IM_SETTINGS_NC_4').replace('#MAIL#', this.userEmail)+''})
			]}),
			this.bitrixMobile? BX.create("div", {props: {className: 'bx-messenger-settings-notify-clients-item'}, children: [
				BX.create("input", {attrs : { 'data-save': 1,  type: "checkbox", id: "notifySchemeSendPush", name: "notifySchemeSendPush", value: 'Y', checked: this.settings.notifySchemeSendPush}}),
				BX.create("label", {attrs : {'for': "notifySchemeSendPush"}, html: ' '+BX.message('IM_SETTINGS_NC_5')+'<br />'})
			]}): null
		]}));
	}

	return items;
};

BX.IM.prototype.showHardwareSettings = function()
{
	var self = this;

	var elements = {
		micSelect: document.querySelector('[name=defaultMicrophone]'),
		camSelect: document.querySelector('[name=defaultCamera]'),
		speakerSelect: document.querySelector('[name=defaultSpeaker]'),
		audioLevel: BX('bx-messenger-settings-hardware-microphone-level'),
		cameraImage: BX('bx-messenger-settings-hardware-camera-image'),
		video: BX('bx-messenger-settings-hardware-camera-image-video')
	};

	if(this.settingsCameraTestMediaStream)
	{
		return;
	}

	if(!elements.micSelect || !elements.camSelect)
	{
		return;
	}

	elements.micSelect.style.minWidth = '200px';
	elements.camSelect.style.minWidth = '200px';

	if(!elements.video)
	{
		elements.video = BX.create('video', {attrs: {id: 'bx-messenger-settings-hardware-camera-image-video'}});
		elements.cameraImage.appendChild(elements.video);
		elements.video.addEventListener('loadedmetadata', function()
		{
			if(BX.MessengerCommon.isDesktop())
			{
				BX.desktop.resize();
			}
		});
	}

	if(!this.webrtc.enabled)
	{
		console.log('webrtc is not supported');
		return;
	}

	var constraints = {
		audio: false,
		video: false
	};

	var supportedDevices = {
		audioInput: false,
		videoInput: false,
		audioOutput: false
	};

	var foundDevices = {
		audioInput: false,
		videoInput: false,
		audioOutput: false
	};

	navigator.mediaDevices.enumerateDevices().then(function(devices)
	{
		devices.forEach(function(mediaDevice)
		{
			if(mediaDevice.kind == 'audioinput')
			{
				supportedDevices.audioInput = true;
				if(self.webrtc.defaultMicrophone === mediaDevice.deviceId)
				{
					foundDevices.audioInput = true;
				}
			}
			else if(mediaDevice.kind == 'videoinput')
			{
				supportedDevices.videoInput = true;
				if(self.webrtc.defaultCamera === mediaDevice.deviceId)
				{
					foundDevices.videoInput = true;
				}
			}
			else if(mediaDevice.kind == 'audiooutput')
			{
				supportedDevices.audioOutput = true;
				if(self.webrtc.defaultSpeaker === mediaDevice.deviceId)
				{
					foundDevices.audioOutput = true;
				}
			}
		});

		if(!foundDevices.audioInput)
		{
			window.localStorage.removeItem('bx-im-settings-default-microphone');
			self.webrtc.defaultMicrophone = '';
		}
		if(!foundDevices.videoInput)
		{
			window.localStorage.removeItem('bx-im-settings-default-camera');
			self.webrtc.defaultCamera = '';
		}
		if(!foundDevices.audioOutput)
		{
			window.localStorage.removeItem('bx-im-settings-default-speaker');
			self.webrtc.defaultSpeaker = '';
		}

		if(supportedDevices.audioInput)
		{
			if(self.webrtc.defaultMicrophone)
				constraints.audio = {deviceId: {exact: self.webrtc.defaultMicrophone}};
			else
				constraints.audio = true;
		}
		if(supportedDevices.videoInput)
		{
			if(self.webrtc.defaultCamera)
				constraints.video = {deviceId: {exact: self.webrtc.defaultCamera}};
			else
				constraints.video = true;
		}

		return navigator.mediaDevices.getUserMedia(constraints);
	}).then(function(mediaStream)
	{
		self.settingsCameraTestMediaStream = mediaStream;
		self.settingsLevelMeter = new BX.IM.LevelMeter(elements.audioLevel);
		if(self.settingsLevelMeter.supported)
			self.settingsLevelMeter.attachMediaStream(mediaStream);

		elements.video.src = window.URL.createObjectURL(mediaStream);
		elements.video.play();
		elements.video.muted = true;
		if(BX.MessengerCommon.isDesktop())
		{
			BX.desktop.resize();
		}
		return navigator.mediaDevices.enumerateDevices();
	}).then(function(devices)
	{
		var videoTrackLabel = (function()
		{
			var videoTracks = self.settingsCameraTestMediaStream.getVideoTracks();
			if(videoTracks.length > 0 && videoTracks[0].label)
				return videoTracks[0].label;
			else
				return '';
		})();
		var audioTrackLabel = (function()
		{
			var audioTracks = self.settingsCameraTestMediaStream.getAudioTracks();
			if(audioTracks.length > 0 && audioTracks[0].label)
				return audioTracks[0].label;
			else
				return '';
		})();

		return new Promise(function(resolve, reject)
		{
			if(devices && elements.micSelect.options.length == 0 && elements.camSelect.options.length == 0)
			{
				devices.forEach(function(device)
				{
					var option;
					var hasAudioInputDevices = false;
					var hasAudioOutputDevices = false;
					var hasVideoInputDevices = false;

					var deviceLabel = device.label == '' ? BX.message('IM_SETTINGS_HARDWARE_DEFAULT_MICROPHONE') : device.label;

					if(device.kind == 'audioinput')
					{
						hasAudioInputDevices = true;
						option = BX.create('option', {text: deviceLabel, attrs:{value: device.deviceId}});

						if(device.label === audioTrackLabel || device.deviceId === self.webrtc.defaultMicrophone)
						{
							option.selected = true;
						}
						elements.micSelect.options.add(option);
					}
					else if(device.kind == 'videoinput')
					{
						hasVideoInputDevices = true;
						option = BX.create('option', {text: deviceLabel, attrs:{value: device.deviceId}});
						if(device.label === videoTrackLabel || device.deviceId === self.webrtc.defaultCamera)
						{
							option.selected = true;
						}
						elements.camSelect.options.add(option);
					}
					else if(device.kind == 'audiooutput')
					{
						hasAudioOutputDevices = true;
						option = BX.create('option', {text: deviceLabel, attrs:{value: device.deviceId}});
						if(device.deviceId === self.webrtc.defaultSpeaker)
						{
							option.selected = true;
						}
						elements.speakerSelect.options.add(option);
					}
				});
				resolve();
			}
			else
			{
				reject();
			}
		});
	}).catch(function(e)
	{
		console.log('could not access user hardware. constraints were: ', constraints);
		console.log(e);
	});
};

BX.IM.prototype.changeHardwareSettings = function()
{
	var self = this;
	var elements = {
		micSelect: document.querySelector('[name=defaultMicrophone]'),
		camSelect: document.querySelector('[name=defaultCamera]'),
		audioLevel: BX('bx-messenger-settings-hardware-microphone-level'),
		cameraImage: BX('bx-messenger-settings-hardware-camera-image'),
		video: BX('bx-messenger-settings-hardware-camera-image-video')
	};

	if(this.settingsCameraTestMediaStream)
	{
		BX.webrtc.stopMediaStream(this.settingsCameraTestMediaStream);
		this.settingsCameraTestMediaStream = null;
	}

	if(this.settingsLevelMeter)
	{
		this.settingsLevelMeter.stop();
	}

	var constraints = {
		audio: {
			deviceId: elements.micSelect.value ? {exact: elements.micSelect.value} : undefined
		},
		video: {
			deviceId: elements.camSelect.value ? {exact: elements.camSelect.value} : undefined
		}
	};

	navigator.mediaDevices.getUserMedia(constraints).then(function(mediaStream)
	{
		self.settingsCameraTestMediaStream = mediaStream;
		if(self.settingsLevelMeter.supported)
			self.settingsLevelMeter.attachMediaStream(mediaStream);

		elements.video.src = window.URL.createObjectURL(mediaStream);
		elements.video.play();
		if(BX.MessengerCommon.isDesktop())
		{
			BX.desktop.resize();
		}
	}).catch(function(e)
	{
		console.log('could not access user hardware', e);
	});
};

BX.IM.prototype.closeHardwareSettings = function()
{
	if(this.settingsCameraTestMediaStream)
		BX.webrtc.stopMediaStream(this.settingsCameraTestMediaStream);

	if(this.settingsLevelMeter)
		this.settingsLevelMeter.stop();

	this.settingsCameraTestMediaStream = null;
	this.webrtc.readDefaults();
};

BX.IM.prototype.saveSetting = function(name, value)
{
	this.settings[name] = value;

	var settings = {};
	settings[name] = value;

	this.saveSettings(settings);

	return true;
}

BX.IM.prototype.saveSettings = function(settings)
{
	var timeoutKey = '';
	for (var config in settings)
	{
		this.settings[config] = settings[config];
		timeoutKey = timeoutKey+config;
	}
	BX.localStorage.set('ims', JSON.stringify(this.settings), 5);

	if (this.saveSettingsTimeout[timeoutKey])
		clearTimeout(this.saveSettingsTimeout[timeoutKey]);

	this.saveSettingsTimeout[timeoutKey] = setTimeout(BX.delegate(function(){
		BX.ajax({
			url: this.pathToAjax+'?SETTINGS_SAVE&V='+this.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_SETTING_SAVE' : 'Y', 'IM_AJAX_CALL' : 'Y', SETTINGS: JSON.stringify(settings), 'sessid': BX.bitrix_sessid()}
		});
		delete this.saveSettingsTimeout[timeoutKey];
	}, this), 700);
};

BX.IM.prototype.saveFormSettings = function()
{
	var inputs = BX.findChildren(this.popupSettingsBody, {attribute : "data-save"}, true);
	for (var i = 0; i < inputs.length; i++)
	{
		if (inputs[i].tagName == 'INPUT' && inputs[i].type == 'checkbox')
		{
			if (typeof(this.settingsSaveCallback[inputs[i].name]) == 'function')
				this.settings[inputs[i].name] = this.settingsSaveCallback[inputs[i].name](inputs[i]);
			else
				this.settings[inputs[i].name] = inputs[i].checked;
		}
		else if (inputs[i].tagName == 'INPUT' && inputs[i].type == 'radio' && inputs[i].checked)
		{
			if (typeof(this.settingsSaveCallback[inputs[i].name]) == 'function')
				this.settings[inputs[i].name] = this.settingsSaveCallback[inputs[i].name](inputs[i]);
			else
				this.settings[inputs[i].name] = inputs[i].value;
		}
		else if (inputs[i].tagName == 'SELECT')
		{
			if (typeof(this.settingsSaveCallback[inputs[i].name]) == 'function')
				this.settings[inputs[i].name] = this.settingsSaveCallback[inputs[i].name](inputs[i]);
			else
				this.settings[inputs[i].name] = inputs[i][inputs[i].selectedIndex].value;
		}
	}

	var values = this.settings['notifyScheme'] == 'simple'? {}: {notify: {}};
	for (var config in this.settings)
	{
		if (config.substr(0,7) == 'notify|')
		{
			if (this.settingsDisabled[config])
				continue;
			if (values['notify'])
				values['notify'][config.substr(7)] = this.settings[config];
		}
		else
		{
			values[config] = this.settings[config];
		}
	}

	if (BX.MessengerCommon.isDesktop())
	{
		BX.desktop.onCustomEvent("bxSaveSettings", [this.settings]);
	}
	else
	{
		BX.localStorage.set('ims', JSON.stringify(this.settings), 5);
	}

	if (this.messenger != null)
	{
		BX.MessengerCommon.userListRedraw(true);
		if (this.messenger.popupMessengerTextareaSendType)
			this.messenger.popupMessengerTextareaSendType.innerHTML = this.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");
	}

	BX.ajax({
		url: this.pathToAjax+'?SETTINGS_FORM_SAVE&V='+this.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SETTINGS_SAVE' : 'Y', 'IM_AJAX_CALL' : 'Y', SETTINGS: JSON.stringify(values), 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function() {
			BX.MessengerCommon.drawTab(this.messenger.currentTab, true);
			this.popupSettings.close();
		}, this),
		onfailure: BX.delegate(function() {
			this.popupSettingsButtonSave.setClassName('popup-window-button popup-window-button-accept');
			this.popupSettingsButtonSave.setName(BX.message('IM_SETTINGS_SAVE'));
			BX.show(this.popupSettingsButtonClose.buttonNode);
		}, this)
	});
};

BX.IM.prototype.GetNotifySettings = function()
{
	BX.ajax({
		url: this.pathToAjax+'?SETTINGS_NOTIFY_LOAD&V='+this.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SETTINGS_NOTIFY_LOAD' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			if (data.ERROR == "")
			{
				if (this.settings.notifyScheme == 'simple')
				{
					for (var configName in data.VALUES)
					{
						if (configName.substr(0,10) == 'important|')
						{
							continue;
						}
						if (configName.substr(0,9) == 'disabled|')
						{
							this.settingsDisabled['notify|'+configName.substr(9)] = data.VALUES[configName];
							continue;
						}

						if (!BX('notifySchemeSendSite').checked && configName.substr(0,5) == 'site|')
							data.VALUES[configName] = false;
						else if (this.bitrixXmpp && !BX('notifySchemeSendXmpp').checked && configName.substr(0,5) == 'xmpp|')
							data.VALUES[configName] = false;
						else if (!BX('notifySchemeSendEmail').checked && configName.substr(0,6) == 'email|')
							data.VALUES[configName] = false;
						else if (this.bitrixMobile && !BX('notifySchemeSendPush').checked && configName.substr(0,5) == 'push|')
							data.VALUES[configName] = false;

						this.settings['notify|'+configName] = data.VALUES[configName];
					}
				}
				else
				{
					for (var configName in data.VALUES)
					{
						if (configName.substr(0,10) == 'important|')
						{
							continue;
						}
						if (configName.substr(0,9) == 'disabled|')
						{
							this.settingsDisabled['notify|'+configName.substr(9)] = data.VALUES[configName];
							continue;
						}
						this.settings['notify|'+configName] = data.VALUES[configName];
					}
				}

				var rows = [];
				if (data.NAMES['im'])
				{
					rows.push([{'type': 'separator', title: data.NAMES['im'].NAME}]);
					for (var notifyId in data.NAMES['im']['NOTIFY'])
					{
						var notifyName = data.NAMES['im']['NOTIFY'][notifyId];
						rows.push([
							{'type': 'text', title: notifyName},
							{'type': 'checkbox', id: 'notifyId|site|im|'+notifyId, name: 'notify|site|im|'+notifyId, callback : function(e) {if (BX(this.id.replace('|site|', '|email|')).disabled) { return true; } if (!this.checked) { BX(this.id.replace('|site|', '|email|')).checked = false;} else {BX(this.id.replace('|site|', '|email|')).checked = true;}}},
							this.bitrixXmpp? {'type': 'checkbox', name: 'notify|xmpp|im|'+notifyId}: false,
							{'type': 'checkbox', id: 'notifyId|email|im|'+notifyId, name: 'notify|email|im|'+notifyId, callback : function(e) {if (BX(this.id.replace('|email|', '|site|')).disabled) { return true; } if (this.checked) { BX(this.id.replace('|email|', '|site|')).checked = true;}}},
							this.bitrixMobile? {'type': 'checkbox', name: 'notify|push|im|'+notifyId}: false
						]);
					}
				}

				for (var moduleId in data.NAMES)
				{
					if (moduleId == 'im')
						continue;

					rows.push([{'type': 'separator', title: data.NAMES[moduleId].NAME}]);
					for (var notifyId in data.NAMES[moduleId]['NOTIFY'])
					{
						var notifyName = data.NAMES[moduleId]['NOTIFY'][notifyId];
						rows.push([
							{'type': 'text', title: notifyName},
							{'type': 'checkbox',  id: 'notifyId|site|'+moduleId+'|'+notifyId, name: 'notify|site|'+moduleId+'|'+notifyId, callback : function(e) {if (BX(this.id.replace('|site|', '|email|')).disabled) { return true; } if (!this.checked) {BX(this.id.replace('|site|', '|email|')).checked = false;} else {BX(this.id.replace('|site|', '|email|')).checked = true;}}},
							this.bitrixXmpp? {'type': 'checkbox', name: 'notify|xmpp|'+moduleId+'|'+notifyId}: false,
							{'type': 'checkbox', id: 'notifyId|email|'+moduleId+'|'+notifyId, name: 'notify|email|'+moduleId+'|'+notifyId, callback : function(e) {if (BX(this.id.replace('|email|', '|site|')).disabled) { return true; } if (this.checked) {BX(this.id.replace('|email|', '|site|')).checked = true;}}},
							this.bitrixMobile? {'type': 'checkbox', name: 'notify|push|'+moduleId+'|'+notifyId}: false
						]);
					}
				}

				this.settingsTableConfig['notify'].rows = rows;
			}
			else
			{
				this.settingsTableConfig['notify'].rows = [
					[{'type': 'error', title: BX.message('IM_M_ERROR')}]
				];
			}
			BX('bx-messenger-settings-table-notify').innerHTML = '';
			BX.adjust(BX('bx-messenger-settings-table-notify'), {children: [this.prepareSettingsTable('notify')]});
			if (data.ERROR != "")
				this.settingsTableConfig['notify'].rows = [];
			if (BX.MessengerCommon.isDesktop())
				this.desktop.autoResize();
		}, this),
		onfailure: BX.delegate(function() {
			this.settingsTableConfig['notify'].rows = [
				[{'type': 'error', title: BX.message('IM_M_ERROR')}]
			];
			BX('bx-messenger-settings-table-notify').innerHTML = '';
			BX.adjust(BX('bx-messenger-settings-table-notify'), {children: [this.prepareSettingsTable('notify')]});
			this.settingsTableConfig['notify'].rows = [];
			if (BX.MessengerCommon.isDesktop())
				this.desktop.autoResize()
		}, this)
	});
};

BX.IM.prototype.GetSimpleNotifySettings = function()
{
	BX.ajax({
		url: this.pathToAjax+'?SETTINGS_SIMPLE_NOTIFY_LOAD&V='+this.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SETTINGS_SIMPLE_NOTIFY_LOAD' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			if (data.ERROR == "")
			{
				var rows = [];
				for (var moduleId in data.VALUES)
				{
					rows.push([{'type': 'separator', title: data.NAMES[moduleId]? data.NAMES[moduleId].NAME: '-'}]);
					for (var notifyId in data.VALUES[moduleId])
					{
						var notifyName = data.NAMES[moduleId]? data.NAMES[moduleId]['NOTIFY'][notifyId]: '-';
						rows.push([
							{'type': 'text', title: notifyName},
							{'type': 'link', title: BX.message('IM_SETTINGS_SNOTIFY_ENABLE'), attrs: { 'data-settingName': moduleId+'|'+notifyId}, callback: BX.delegate(function(){ this.removeSimpleNotify(BX.proxy_context)}, this)}
						]);
						this.settingsNotifyBlocked[moduleId+"|"+notifyId] = true;
					}
				}
				this.settingsTableConfig['simpleNotify'].rows = rows;
			}
			else
			{
				this.settingsTableConfig['simpleNotify'].rows = [
					[{'type': 'error', title: BX.message('IM_M_ERROR')}]
				];
			}
			BX('bx-messenger-settings-table-simpleNotify').innerHTML = '';
			BX.adjust(BX('bx-messenger-settings-table-simpleNotify'), {children: [this.prepareSettingsTable('simpleNotify')]});
			if (data.ERROR != "")
				this.settingsTableConfig['simpleNotify'].rows = [];
			if (BX.MessengerCommon.isDesktop())
				this.desktop.autoResize();
		}, this),
		onfailure: BX.delegate(function() {
			this.settingsTableConfig['simpleNotify'].rows = [
				[{'type': 'error', title: BX.message('IM_M_ERROR')}]
			];
			if (BX('bx-messenger-settings-table-simpleNotify'))
			{
				BX('bx-messenger-settings-table-simpleNotify').innerHTML = '';
				BX.adjust(BX('bx-messenger-settings-table-simpleNotify'), {children: [this.prepareSettingsTable('simpleNotify')]});
			}
			this.settingsTableConfig['simpleNotify'].rows = [];
			if (BX.MessengerCommon.isDesktop())
				this.desktop.autoResize();
		}, this)
	});
};

BX.IM.prototype.removeSimpleNotify = function(element)
{
	var table = element.parentNode.parentNode.parentNode;
	if (!element.parentNode.parentNode.nextSibling && element.parentNode.parentNode.previousSibling.childNodes[0].className != "bx-messenger-settings-table-sep")
	{
		BX.remove(element.parentNode.parentNode);
	}
	else if (element.parentNode.parentNode.previousSibling && element.parentNode.parentNode.previousSibling.childNodes[0].className != "bx-messenger-settings-table-sep")
	{
		BX.remove(element.parentNode.parentNode);
	}
	else if (element.parentNode.parentNode.nextSibling && element.parentNode.parentNode.nextSibling.childNodes[0].className != "bx-messenger-settings-table-sep")
	{
		BX.remove(element.parentNode.parentNode);
	}
	else if (element.parentNode.parentNode.previousSibling.childNodes[0].className == "bx-messenger-settings-table-sep" && !element.parentNode.parentNode.nextSibling)
	{
		BX.remove(element.parentNode.parentNode.previousSibling);
		BX.remove(element.parentNode.parentNode);
	}
	else if (element.parentNode.parentNode.previousSibling.childNodes[0].className == "bx-messenger-settings-table-sep" && element.parentNode.parentNode.nextSibling.childNodes[0].className == "bx-messenger-settings-table-sep")
	{
		BX.remove(element.parentNode.parentNode.previousSibling);
		BX.remove(element.parentNode.parentNode);
	}
	if (table.childNodes.length <= 1)
		BX.remove(table);

	this.notify.blockNotifyType(element.getAttribute('data-settingName'));

	if (BX.MessengerCommon.isDesktop())
		this.desktop.autoResize();
};

BX.IM.prototype.openConfirm = function(text, buttons, modal)
{
	if (this.popupConfirm != null)
		this.popupConfirm.destroy();

	if (typeof(text) == "object")
		text = '<div class="bx-messenger-confirm-title">'+text.title+'</div>'+text.message;

	modal = modal !== false;
	var autohide = (buttons === false);
	if (typeof(buttons) == "undefined" || typeof(buttons) == "object" && buttons.length <= 0 || buttons === false)
	{
		buttons = [new BX.PopupWindowButton({
			text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
			className : "popup-window-button-decline",
			events : { click : function(e) { this.popupWindow.close(); BX.PreventDefault(e) } }
		})];
	}
	this.popupConfirm = new BX.PopupWindow('bx-notifier-popup-confirm', null, {
		//parentPopup: this.messenger.popupMessenger,
		zIndex: 15000,
		autoHide: buttons === false,
		buttons : buttons,
		closeByEsc: buttons === false,
		overlay : modal,
		events : { onPopupClose : function() { this.destroy() }, onPopupDestroy : BX.delegate(function() { this.popupConfirm = null }, this)},
		content : BX.create("div", { props : { className : (buttons === false? " bx-messenger-confirm-without-buttons": "bx-messenger-confirm") }, html: text})
	});
	BX.addClass(this.popupConfirm.popupContainer, "bx-messenger-mark");
	this.popupConfirm.show();
	BX.bind(this.popupConfirm.popupContainer, "click", BX.MessengerCommon.preventDefault);
	BX.bind(this.popupConfirm.contentContainer, "click", BX.PreventDefault);
	BX.bind(this.popupConfirm.overlay.element, "click", BX.PreventDefault);
	if(autohide === true)
	{
		setTimeout(BX.delegate(function()
		{
			this.close();
		}, this.popupConfirm), 2000);
	}
};

BX.IM.prototype.setBackground = function(value)
{
	var classNode = null;
	var mainNode = null;

	if (BX.MessengerCommon.isPage())
	{
		mainNode = BX.MessengerWindow.contentBox;
	}
	else
	{
		mainNode = this.messenger.popupMessengerContent;
	}

	var isChanged = false;
	if (typeof(value) == 'undefined')
	{
		value = this.settings.backgroundImage;
	}
	else
	{
		if (value == "on")
		{
			value = true;
		}
		else if (value == "off")
		{
			value = false;
		}
		else if (this.colorsHex[value.toString().toUpperCase()])
		{
			value = this.colorsHex[value.toString().toUpperCase()];
		}
		else
		{
			var colors = {};
			for (var color in this.colors)
			{
				colors[this.colors[color].toUpperCase()] = color;
			}
			if (colors[value.toString().toUpperCase()])
			{
				var color = colors[value.toString().toUpperCase()];
				if (this.colorsHex[color])
				{
					value = this.colorsHex[color];
				}
			}
		}

		isChanged = this.settings.backgroundImage != value;
	}

	if (value === false)
	{
		BX.removeClass(mainNode, "bx-messenger-image");
		BX.removeClass(mainNode, "bx-messenger-image-link");
		BX.style(mainNode, "background-image", "");
		BX.style(mainNode, "background-color", "");
	}
	else if (value === true)
	{
		BX.addClass(mainNode, "bx-messenger-image");
		BX.removeClass(mainNode, "bx-messenger-image-link");
		BX.style(mainNode, "background-image", "");
		BX.style(mainNode, "background-color", "");
	}
	else if (value.toString().length > 0)
	{
		BX.addClass(mainNode, "bx-messenger-image");
		if (value.toString().substr(0,1) == '#')
		{
			BX.style(mainNode, "background-color", value);
			BX.style(mainNode, "background-image", "");
		}
		else if (value.toString().substr(0,4) == 'http')
		{
			BX.addClass(mainNode, "bx-messenger-image-link");
			BX.style(mainNode, "background-image", "url("+value+")");
			BX.style(mainNode, "background-color", "");
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}

	if (isChanged)
	{
		this.saveSettings({'backgroundImage': value});
	}
}

BX.IM.getSelectionText = function()
{
	var selected = '';

	if (window.getSelection)
	{
		selected = window.getSelection().toString();
	}
	else
	{
		selected = document.selection.createRange().text;
	}

	return selected;
}

BX.IM.prototype.getLocalConfig = function(name, def)
{
	if (BX.MessengerCommon.isDesktop())
	{
		return BX.desktop.getLocalConfig(name, def);
	}

	def = typeof(def) == 'undefined'? null: def;

	if (!BX.browser.SupportLocalStorage())
	{
		return def;
	}

	if (BX.MessengerCommon.isPage() && !BX.MessengerCommon.isDesktop())
		name = 'full-'+name;

	var result = BX.localStorage.get(name);
	if (result == null)
	{
		return def;
	}

	if (typeof(result) == 'string' && result.length > 0)
	{
		try {
			result = JSON.parse(result);
		}
		catch(e) { result = def; }
	}

	return result;
};

BX.IM.prototype.setLocalConfig = function(name, value, ttl)
{
	if (BX.MessengerCommon.isDesktop())
	{
		return BX.desktop.setLocalConfig(name, value);
	}

	ttl = ttl || 86400;

	if (typeof(value) == 'object')
		value = JSON.stringify(value);
	else if (typeof(value) == 'boolean')
		value = value? 'true': 'false';
	else if (typeof(value) == 'undefined')
		value = '';
	else if (typeof(value) != 'string')
		value = value+'';

	if (!BX.browser.SupportLocalStorage())
		return false;

	if (BX.MessengerCommon.isPage() && !BX.MessengerCommon.isDesktop())
		name = 'full-'+name;

	BX.localStorage.set(name, value, ttl);

	return true;
};

BX.IM.prototype.removeLocalConfig = function(name)
{
	if (BX.MessengerCommon.isDesktop())
	{
		return BX.desktop.removeLocalConfig(name);
	}

	if (!BX.browser.SupportLocalStorage())
		return false;

	if (BX.MessengerCommon.isPage() && !BX.MessengerCommon.isDesktop())
		name = 'full-'+name;

	BX.localStorage.remove(name);

	return true;
};

BX.IM.prototype.storageSet = function(params)
{
	if (params.key == 'mps')
	{
		this.stopSound();
	}
	else if (params.key == 'mrss')
	{
		this.stopRepeatSound(params.value.sound, false);
	}
};
})();


/* IM notify class */

(function() {

if (BX.Notify)
	return;

BX.Notify = function(BXIM, params)
{
	this.BXIM = BXIM;
	this.settings = {};
	this.params = params || {};
	this.windowInnerSize = {};
	this.windowScrollPos = {};
	this.sendAjaxTry = 0;

	this.webrtc = params.webrtcClass;
	this.desktop = params.desktopClass;

	this.notifyCount = params.countNotify;
	this.notifyUpdateCount = params.countNotify;
	this.counters = params.counters;
	this.mailCount = params.mailCount;

	this.notifyAnswerBlock = {};
	this.notifyAnswerText = {};

	this.notifyHistoryPage = 0;
	this.notifyHistoryLoad = false;

	this.notifyBody = null;
	this.notify = params.notify;
	for (var notifyId in this.notify)
	{
		this.notify[notifyId].date = new Date(this.notify[notifyId].date);
	}

	this.notifyLoad = false;
	this.unreadNotify = params.unreadNotify;
	this.unreadNotifyLoad = params.loadNotify;
	this.flashNotify = params.flashNotify;
	this.initNotifyCount = params.countNotify;
	this.confirmDisabledButtons = false;

	if (this.unreadNotifyLoad)
	{
		for (var i in this.notify)
			this.initNotifyCount--;
	}

	if (params.domNode)
	{
		this.panel = params.domNode;
		this.panelEnabled = true;
		BX.bind(this.panel, 'click', BX.PreventDefault);
	}
	else
	{
		this.panel = BX.create('span', {props: { className: "bx-messenger-hide"}});
		this.panelEnabled = false;
	}

	if (this.panelEnabled)
	{
		if (BX.browser.IsDoctype())
			BX.addClass(this.panel, 'bx-notifier-panel-doc');
		else
			BX.addClass(document.body, 'bx-no-doctype');

		this.panelButtonCall = BX.findChildByClassName(this.panel, "bx-notifier-call");
		if (!this.webrtc.phoneEnabled || !this.webrtc.phoneCanPerformCalls)
		{
			BX.style(this.panelButtonCall, 'display', 'none');
		}

		this.panelButtonNetwork = BX.findChildByClassName(this.panel, "bx-notifier-network");
		if (this.panelButtonNetwork)
		{
			this.panelButtonNetworkCount = BX.findChildByClassName(this.panelButtonNetwork, "bx-notifier-indicator-count");
			if (this.BXIM.bitrixNetwork)
			{
				this.panelButtonNetwork.href = "https://www.bitrix24.net/";
				this.panelButtonNetwork.setAttribute('target', '_blank');
				if (this.panelButtonNetworkCount != null)
					this.panelButtonNetworkCount.innerHTML = '';
			}
			else
			{
				BX.style(this.panelButtonNetwork, 'display', 'none');
				this.panelButtonNetworkCount.innerHTML = '';
			}
		}

		this.panelButtonNotify = BX.findChildByClassName(this.panel, "bx-notifier-notify");
		if (this.panelButtonNotify)
		{
			this.panelButtonNotifyCount = BX.findChildByClassName(this.panelButtonNotify, "bx-notifier-indicator-count");
			if (this.panelButtonNotifyCount)
				this.panelButtonNotifyCount.innerHTML = '';
		}

		this.panelButtonMessage = BX.findChildByClassName(this.panel, "bx-notifier-message");
		if (this.panelButtonMessage)
		{
			this.panelButtonMessageCount = BX.findChildByClassName(this.panelButtonMessage, "bx-notifier-indicator-count");
			if (this.panelButtonMessageCount)
				this.panelButtonMessageCount.innerHTML = '';
		}

		this.panelButtonMail = BX.findChildByClassName(this.panel, "bx-notifier-mail");
		if (this.panelButtonMail)
		{
			this.panelButtonMailCount = BX.findChildByClassName(this.panelButtonMail, "bx-notifier-indicator-count");
			if (this.panelButtonMailCount)
			{
				this.panelButtonMail.href = this.BXIM.path.mail;
				this.panelButtonMail.setAttribute('target', '_blank');
				if (this.panelButtonMailCount != null)
					this.panelButtonMailCount.innerHTML = '';
			}
		}
		this.panelDragLabel = BX.findChildByClassName(this.panel, "bx-notifier-drag");
		if (this.panelDragLabel)
		{
			BX.bind(this.panelDragLabel, "mousedown", BX.delegate(this._startDrag, this));
			BX.bind(this.panelDragLabel, "dobleclick", BX.delegate(this._stopDrag, this));
		}
	}

	if (BX.browser.IsAndroid() || BX.browser.IsIOS())
		BX.addClass(document.body, 'bx-im-mobile');

	this.messenger = null;
	this.messengerNotifyButton = null;
	this.messengerNotifyButtonCount = null;

	/* full window notify */
	this.popupNotifyItem = null;
	this.popupNotifySize = 387;
	this.popupNotifySizeMin = 317;

	this.popupNotifyButtonFilter = null;
	this.popupNotifyButtonFilterBox = null;
	this.popupHistoryFilterVisible = false;
	/* more users from notify */
	this.popupNotifyMore = null;

	this.dragged = false;
	this.dragPageX = 0;
	this.dragPageY = 0;

	if (this.BXIM.init)
	{
		if (BX.MessengerCommon.isPage())
		{
			BX.MessengerWindow.addTab({
				id: 'notify',
				title: BX.message('IM_SETTINGS_NOTIFY'),
				order: 110,
				target: 'im',
				events: {
					open: BX.delegate(function(){
						this.openNotify(false, true)
					}, this)
				}
			});
		}

		this.panel.appendChild(this.BXIM.audio.reminder = BX.create("audio", { props : { className : "bx-notify-audio" }, children : [
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/reminder.ogg", type : "audio/ogg; codecs=vorbis" }}),
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/reminder.mp3", type : "audio/mpeg" }})
		]}));
		if (typeof(this.BXIM.audio.reminder.play) == 'undefined')
		{
			this.BXIM.settings.enableSound = false;
		}

		if (BX.browser.SupportLocalStorage())
		{
			BX.addCustomEvent(window, "onLocalStorageSet", BX.proxy(this.storageSet, this));
			var panelPosition = BX.localStorage.get('npp');
			this.BXIM.settings.panelPositionHorizontal = !!panelPosition? panelPosition.h: this.BXIM.settings.panelPositionHorizontal;
			this.BXIM.settings.panelPositionVertical = !!panelPosition? panelPosition.v: this.BXIM.settings.panelPositionVertical;

			var mfn = BX.localStorage.get('mfn');
			if (mfn)
			{
				for (var i in this.flashNotify)
					if (this.flashNotify[i] != mfn[i] && mfn[i] == false)
						this.flashNotify[i] = false;
			}

			BX.garbage(function(){
				BX.localStorage.set('mfn', this.flashNotify, 15);
			}, this);
		}

		if (this.panelButtonNotify)
		{
			BX.bind(this.panelButtonNotify, "click", BX.proxy(function(){
				this.toggleNotify()
			}, this.BXIM));
		}

		if (this.webrtc.phoneEnabled && this.webrtc.phoneCanPerformCalls)
		{
			if (this.panelButtonCall)
			{
				BX.bind(this.panelButtonCall, "click", BX.delegate(this.webrtc.openKeyPad, this.webrtc));
			}
			BX.bind(window, 'scroll', BX.delegate(function(){
				if (this.webrtc.popupKeyPad)
					this.webrtc.popupKeyPad.close();
			}, this));
		}

		if (this.panelDragLabel)
		{
			BX.bind(this.panelDragLabel, "mousedown", BX.proxy(this._startDrag, this));
			BX.bind(this.panelDragLabel, "dobleclick", BX.proxy(this._stopDrag, this));
		}

		this.updateNotifyMailCount();

		if (!BX.MessengerCommon.isPage())
		{
			this.adjustPosition({resize: true});
			BX.bind(window, "resize", BX.proxy(function(){
				this.closePopup();
				this.adjustPosition({resize: true});
			}, this));
			if (!BX.browser.IsDoctype())
				BX.bind(window, "scroll", BX.proxy(function(){ this.adjustPosition({scroll: true});}, this));
		}

		setTimeout(BX.delegate(function(){
			this.newNotify();
			this.updateNotifyCounters();
			this.updateNotifyCount();
		}, this), 500);
	}

	BX.addCustomEvent(window, "onSonetLogCounterClear", BX.proxy(function(counter){
		var sendObject = {};
		sendObject[counter] = 0;
		this.updateNotifyCounters(sendObject);
	}, this));
};

BX.Notify.prototype.getCounter = function(type)
{
	if (typeof(type) != 'string')
		return false;

	type = type.toString();

	if (type == 'im_notify')
		return this.notifyCount;
	if (type == 'im_message')
		return this.BXIM.messageCount;

	return this.counters[type]? this.counters[type]: 0;
};

BX.Notify.prototype.updateNotifyCounters = function(arCounter, send)
{
	send = send != false;
	if (typeof(arCounter) == "object")
	{
		for (var i in arCounter)
			this.counters[i] = arCounter[i];
	}
	BX.onCustomEvent(window, 'onImUpdateCounter', [this.counters]);
	if (send)
		BX.localStorage.set('nuc', this.counters, 5);
};

BX.Notify.prototype.updateNotifyMailCount = function(count, send)
{
	send = send != false;

	if (typeof(count) != "undefined" || parseInt(count)>0)
		this.mailCount = parseInt(count);

	var mailCountLabel = '';
	if (this.mailCount > 99)
		mailCountLabel = '99+';
	else if (this.mailCount > 0)
		mailCountLabel = this.mailCount;

	if (this.panelButtonMail)
	{
		if (this.mailCount > 0)
			BX.removeClass(this.panelButtonMail, 'bx-notifier-hide');
		else
			BX.addClass(this.panelButtonMail, 'bx-notifier-hide');

		if (this.panelButtonMailCount != null)
		{
			this.panelButtonMailCount.innerHTML = mailCountLabel;
			this.adjustPosition({"resize": true, "timeout": 500});
		}
	}

	BX.onCustomEvent(window, 'onImUpdateCounterMail', [this.mailCount, 'MAIL']);

	if (send)
		BX.localStorage.set('numc', this.mailCount, 5);
};

BX.Notify.prototype.updateNotifyCount = function(send)
{
	send = send != false;

	var count = 0;
	var updateCount = 0;

	if (this.unreadNotifyLoad)
		count = this.initNotifyCount;

	for (var i in this.unreadNotify)
	{
		if (this.unreadNotify[i] == null)
			continue;

		var notify = this.notify[this.unreadNotify[i]];
		if (!notify)
			continue;

		if (notify.type != 1)
			updateCount++;

		count++;
	}

	var notifyCountLabel = '';
	if (count > 99)
		notifyCountLabel = '99+';
	else if (count > 0)
		notifyCountLabel = count;

	if (this.panelButtonNotifyCount)
	{
		this.panelButtonNotifyCount.innerHTML = notifyCountLabel;
		this.adjustPosition({"resize": true, "timeout": 500});
	}

	if (this.messengerNotifyButtonCount)
		this.messengerNotifyButtonCount.innerHTML = parseInt(notifyCountLabel)>0? '<span class="bx-messenger-cl-count-digit">'+notifyCountLabel+'</span>':'';

	if (BX.MessengerCommon.isPage())
	{
		BX.MessengerWindow.setTabBadge('notify', count)
	}

	this.notifyCount = parseInt(count);
	this.notifyUpdateCount = parseInt(updateCount);

	BX.onCustomEvent(window, 'onImUpdateCounterNotify', [this.notifyCount, 'NOTIFY']);

	if (send)
		BX.localStorage.set('nunc', {'unread': this.unreadNotify, 'flash': this.flashNotify}, 5);
};

BX.Notify.prototype.changeUnreadNotify = function(unreadNotify, send)
{
	send = send != false;
	var redraw = false;
	for (var i in unreadNotify)
	{
		if (!this.BXIM.xmppStatus && this.BXIM.settings.status != 'dnd')
			this.flashNotify[unreadNotify[i]] = true;
		else
			this.flashNotify[unreadNotify[i]] = false;

		this.unreadNotify[unreadNotify[i]] = unreadNotify[i];
		redraw = true;
	}
	this.newNotify(send);

	if (redraw && this.BXIM.notifyOpen)
		this.openNotify(true);

	this.updateNotifyCount(send);
};

BX.Notify.prototype.viewNotify = function(id, read, send)
{
	if (parseInt(id) <= 0)
		return false;

	read = read === false? false: true;
	send = send === false? false: true;

	var notify = this.notify[id];
	if (notify && notify.type != 1)
	{
		if (read)
		{
			delete this.unreadNotify[id];
		}
		else
		{
			this.unreadNotify[id] = id;
		}
	}

	delete this.flashNotify[id];

	BX.localStorage.set('mfn', this.flashNotify, 80);

	if (send)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?NOTIFY_VIEW&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_NOTIFY_VIEW' : 'Y', 'ID' : parseInt(id), 'READ': (read? 'Y':'N'), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});
	}

	if (this.BXIM.notifyOpen)
	{
		var notify = BX.findChildByClassName(document.body, "bx-notifier-item-"+id);
		if (read)
		{
			BX.removeClass(notify, 'bx-notifier-item-new');
		}
		else
		{
			BX.addClass(notify, 'bx-notifier-item-new');
		}
	}

	this.updateNotifyCount(false);

	return true;
};

BX.Notify.prototype.viewNotifyMarkupUpdate = function()
{
	if (this.BXIM.notifyOpen)
	{
		var elements = BX.findChildrenByClassName(this.popupNotifyItem, "bx-notifier-item-new", false);
		if (elements != null)
		{
			for (var i = 0; i < elements.length; i++)
			{
				if (elements[i].getAttribute('data-notifyType') == 1)
				{
					continue;
				}
				if (!this.unreadNotify[elements[i].getAttribute('data-notifyId')])
				{
					BX.removeClass(elements[i], 'bx-notifier-item-new');
				}
			}
		}
		for (var i in this.unreadNotify)
		{
			var element = BX.findChildByClassName(this.popupNotifyItem, "bx-notifier-item-"+i, false);
			if (element != null)
			{
				BX.addClass(element, 'bx-notifier-item-new');
			}
		}
	}
}

BX.Notify.prototype.viewNotifyAll = function(send)
{
	send = send !== false;
	if (this.BXIM.settings.notifyAutoRead)
	{
		var id = null;
		for (var i in this.unreadNotify)
		{
			if (this.notify[i] && this.notify[i].type != 1)
			{
				delete this.unreadNotify[i];
				if (id === null || id > i)
				{
					id = i;
				}
			}

			delete this.flashNotify[i];
		}
		if (!id)
		{
			return false;
		}

		if (send)
		{
			BX.ajax({
				url: this.BXIM.pathToAjax+'?NOTIFY_READ&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: {'IM_NOTIFY_READ' : 'Y', 'ID' : id, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}

		setTimeout(this.viewNotifyMarkupUpdate, 500);

		this.updateNotifyCount(false);
	}
	else
	{
		for (var i in this.unreadNotify)
		{
			delete this.flashNotify[i];
		}
	}

	BX.localStorage.set('mfn', this.flashNotify, 80);

	return true;
};

BX.Notify.prototype.newNotify = function(send)
{
	send = send != false;

	var arNotify = [];
	var arNotifyText = [];
	var arNotifySort = [];
	for (var i in this.flashNotify)
	{
		if (this.flashNotify[i] === true)
		{
			arNotifySort.push(parseInt(i));
			this.flashNotify[i] = false;
		}
	}
	var flashNames = {};
	arNotifySort.sort(BX.delegate(function(a, b) {if (!this.notify[a] || !this.notify[b]){return 0;}var i1 = this.notify[a].date.getTime(); var i2 = this.notify[b].date.getTime();var t1 = parseInt(this.notify[a].type); var t2 = parseInt(this.notify[b].type);if (t1 == 1 && t2 != 1) { return -1;}else if (t2 == 1 && t1 != 1) { return 1;}else if (i2 > i1) { return 1; }else if (i2 < i1) { return -1;}else{ return 0;}}, this));
	for (var i = 0; i < arNotifySort.length; i++)
	{
		var notify = BX.clone(this.notify[arNotifySort[i]]);
		if (notify && notify.userId && notify.userName)
			flashNames[notify.userId] = notify.userName;

		notify.text = notify.text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function (whole, imol, chatId, text)
		{
			return text;
		});

		notify = this.createNotify(notify, true);
		if (notify !== false)
		{
			arNotify.push(notify);

			notify = this.notify[arNotifySort[i]];
			arNotifyText.push({
				'title':  notify.userName? BX.util.htmlspecialcharsback(notify.userName): BX.message('IM_NOTIFY_WINDOW_NEW_TITLE'),
				'text':  BX.util.htmlspecialcharsback(notify.text).split('<br />').join("\n").replace(/<\/?[^>]+>/gi, ''),
				'icon':  notify.userAvatar? notify.userAvatar: '',
				'tag':  'im-notify-'+notify.tag
			});
		}
	}
	if (arNotify.length > 5)
	{
		var names = '';
		for (var i in flashNames)
			names += ', <i>'+flashNames[i]+'</i>';

		var notify = {
			id: 0, type: 4,date: new Date(), tag: '', originalTag: '',
			title: BX.message('IM_NM_NOTIFY_1').replace('#COUNT#', arNotify.length),
			text: names.length>0? BX.message('IM_NM_NOTIFY_2').replace('#USERS#', names.substr(2)): BX.message('IM_NM_NOTIFY_3')
		};
		notify = this.createNotify(notify, true);
		BX.style(notify, 'cursor', 'pointer');
		arNotify = [notify];

		arNotifyText = [{
			'id': '',
			'title':  BX.message('IM_NM_NOTIFY_1').replace('#COUNT#', arNotify.length),
			'text': names.length>0? BX.message('IM_NM_NOTIFY_2').replace('#USERS#', BX.util.htmlspecialcharsback(names.substr(2))).replace(/<\/?[^>]+>/gi, ''): BX.message('IM_NM_NOTIFY_3')
		}];
	}
	if (arNotify.length == 0)
		return false;

	if (BX.MessengerCommon.isDesktop())
		BX.desktop.flashIcon(false);

	this.closePopup();

	if (this.BXIM.context == "LINES" || this.BXIM.context == "DIALOG")
	{
		return false;
	}

	if (this.BXIM.settings.status == 'dnd' || !BX.MessengerCommon.isDesktop() && this.BXIM.desktopStatus)
		return false;

	if (send && !this.BXIM.xmppStatus)
		this.BXIM.playSound("reminder");

	if (send && BX.MessengerCommon.isDesktop())
	{
		for (var i = 0; i < arNotify.length; i++)
		{
			var dataNotifyId = arNotify[i].getAttribute("data-notifyId");
			var messsageJs =
				'var notify = BX.findChildByClassName(document.body, "bx-notifier-item");'+
				'BX.bind(BX.findChildByClassName(notify, "bx-notifier-item-delete"), "click", function(event){ if (this.getAttribute("data-notifyType") != 1) { BX.desktop.onCustomEvent("main", "bxImClickCloseNotify", [this.getAttribute("data-notifyId")]); } BX.desktop.windowCommand("close"); BX.MessengerCommon.preventDefault(event); });'+
				(arNotify[i].id>0? '': 'BX.bind(notify, "click", function(event){ BX.desktop.onCustomEvent("main", "bxImClickNotify", []); BX.desktop.windowCommand("close"); BX.MessengerCommon.preventDefault(event); });')+
				'BX.bindDelegate(notify, "click", {className: "bx-notifier-item-button"}, BX.delegate(function(){ '+
					'BX.desktop.windowCommand("freeze");'+
					'notifyId = BX.proxy_context.getAttribute("data-id");'+
					'BXIM.notify.confirmRequest({'+
						'"notifyId": notifyId,'+
						'"notifyValue": BX.proxy_context.getAttribute("data-value"),'+
						'"notifyURL": BX.proxy_context.getAttribute("data-url"),'+
						'"notifyTag": BXIM.notify.notify[notifyId] && BXIM.notify.notify[notifyId].tag? BXIM.notify.notify[notifyId].tag: null,'+
						'"groupDelete": BX.proxy_context.getAttribute("data-group") == null? false: true,'+
					'}, true);'+
					'BX.desktop.onCustomEvent("main", "bxImClickConfirmNotify", [notifyId]); '+
				'}, BXIM.notify));'+
				'BX.bind(notify, "contextmenu", function(){ BX.desktop.windowCommand("close")});';
			this.desktop.openNewNotify(dataNotifyId, arNotify[i], messsageJs);
		}
	}
	else if(send && !this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
	{
		for (var i = 0; i < arNotifyText.length; i++)
		{
			var notify = arNotifyText[i];
			notify.onshow = function() {
				var notify = this;
				setTimeout(function(){
					notify.close();
				}, 5000)
			}
			notify.onclick = function() {
				window.focus();
				top.BXIM.openNotify();
				this.close();
			}
			this.BXIM.notifyManager.nativeNotify(notify)
		}
	}
	else
	{
		if (this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
		{
			BX.localStorage.set('mnnb', true, 1);
		}
		for (var i = 0; i < arNotify.length; i++)
		{
			this.BXIM.notifyManager.add({
				'html': arNotify[i],
				'tag': arNotify[i].id>0? 'im-notify-'+this.notify[arNotify[i].getAttribute("data-notifyId")].tag:'',
				'originalTag': arNotify[i].id>0? this.notify[arNotify[i].getAttribute("data-notifyId")].originalTag:'',
				'notifyId': arNotify[i].getAttribute("data-notifyId"),
				'notifyType': arNotify[i].getAttribute("data-notifyType"),
				'click': arNotify[i].id > 0? null: BX.delegate(function(popup) {
					this.BXIM.openNotify();
					popup.close();
				}, this),
				'close': BX.delegate(function(popup) {
					if (popup.notifyParams.notifyType != 1 && popup.notifyParams.notifyId)
						this.viewNotify(popup.notifyParams.notifyId);
				}, this)
			});
		}
	}
	return true;
};

BX.Notify.prototype.confirmRequest = function(params, popup)
{
	if (this.confirmDisabledButtons)
		return false;

	popup = popup == true;

	params.notifyOriginTag = this.notify[params.notifyId]? this.notify[params.notifyId].originalTag: '';

	if (BX.MessengerCommon.isMobile())
	{
		if (params.groupDelete && params.notifyTag != null)
		{
			for (var i in this.notify)
			{
				if (this.notify[i].tag == params.notifyTag)
					delete this.notify[i];
			}
		}
		else
		{
			delete this.notify[params.notifyId];
		}
	}
	this.updateNotifyCount();

	if (popup && BX.MessengerCommon.isDesktop())
		BX.desktop.windowCommand("freeze");
	else
		BX.hide(BX.proxy_context.parentNode.parentNode.parentNode);

	BX.ajax({
		url: this.BXIM.pathToAjax+'?NOTIFY_CONFIRM&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_NOTIFY_CONFIRM' : 'Y', 'NOTIFY_ID' : params.notifyId, 'NOTIFY_VALUE' : params.notifyValue, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			if (params.notifyURL != null)
			{
				if (popup && BX.MessengerCommon.isDesktop())
					BX.desktop.browse(params.notifyURL);
				else
					location.href = params.notifyURL;

				this.confirmDisabledButtons = true;
			}
			if (!BX.MessengerCommon.isMobile() && this.notify[params.notifyId] && data.MESSAGES)
			{
				this.notify[params.notifyId].confirmMessages = data.MESSAGES;
			}
			BX.onCustomEvent(window, 'onImConfirmNotify', [{'NOTIFY_ID' : params.notifyId, 'NOTIFY_TAG' : params.notifyOriginTag, 'NOTIFY_VALUE' : params.notifyValue, 'NOTIFY_MESSAGES': data.MESSAGES}]);
			if (popup && BX.MessengerCommon.isDesktop())
				BX.desktop.windowCommand("close");
		}, this),
		onfailure: BX.delegate(function() {
			if (popup && BX.MessengerCommon.isDesktop())
				BX.desktop.windowCommand("close");
		}, this)
	});

	if (params.groupDelete)
		BX.localStorage.set('nrgn', params.notifyTag, 5);
	else
		BX.localStorage.set('nrn', params.notifyId, 5);

	return false;
};

BX.Notify.prototype.drawNotify = function(arItemsNotify, loadMore)
{
	loadMore = loadMore == true;
	var itemsNotify = typeof(arItemsNotify) == 'object'? arItemsNotify: BX.clone(this.notify);

	var arGroupedNotify = {};
	var arGroupedNotifyByUser = {};
	for (var i in itemsNotify)
	{
		if (
			itemsNotify[i].tag != '' &&
			(!itemsNotify[i].params || itemsNotify[i].params.CAN_ANSWER != 'Y')
		)
		{
			if (!arGroupedNotifyByUser[itemsNotify[i].tag] || !arGroupedNotifyByUser[itemsNotify[i].tag][itemsNotify[i].userId])
			{
				if (arGroupedNotifyByUser[itemsNotify[i].tag])
				{
					if (!arGroupedNotifyByUser[itemsNotify[i].tag][itemsNotify[i].userId])
						arGroupedNotifyByUser[itemsNotify[i].tag][itemsNotify[i].userId] = itemsNotify[i].id;

					if (parseInt(arGroupedNotify[itemsNotify[i].tag].date) <= parseInt(itemsNotify[i].date))
					{
						itemsNotify[i].groupped = true;
						delete itemsNotify[arGroupedNotify[itemsNotify[i].tag].id];
						arGroupedNotify[itemsNotify[i].tag] = itemsNotify[i];
					}
					else
					{
						itemsNotify[arGroupedNotify[itemsNotify[i].tag].id].groupped = true;
						delete itemsNotify[i];
					}
				}
				else
				{
					arGroupedNotifyByUser[itemsNotify[i].tag] = {};
					arGroupedNotifyByUser[itemsNotify[i].tag][itemsNotify[i].userId] = itemsNotify[i].id;
					arGroupedNotify[itemsNotify[i].tag] = itemsNotify[i];
				}
			}
			else
			{
				if (parseInt(arGroupedNotify[itemsNotify[i].tag].date) <= parseInt(itemsNotify[i].date))
				{
					itemsNotify[i].groupped = true;
					delete itemsNotify[arGroupedNotify[itemsNotify[i].tag].id];
					arGroupedNotify[itemsNotify[i].tag] = itemsNotify[i];
				}
				else
				{
					itemsNotify[arGroupedNotify[itemsNotify[i].tag].id].groupped = true;
					delete itemsNotify[i];
				}
			}
		}
	}

	var arNotify = [];
	var arNotifySort = [];
	for (var i in itemsNotify)
	{
		arNotifySort.push(parseInt(i));
	}

	arNotifySort.sort(function(a, b) {
		if (!itemsNotify[a] || !itemsNotify[b]){return 0;}
		var i1 = itemsNotify[a].date.getTime();
		var i2 = itemsNotify[b].date.getTime();
		var t1 = typeof(itemsNotify[a].confirmMessages) == 'undefined'? parseInt(itemsNotify[a].type): 2;
		var t2 = typeof(itemsNotify[b].confirmMessages) == 'undefined'? parseInt(itemsNotify[b].type): 2;
		if (t1 == 1 && t2 != 1) { return -1;}
		else if (t2 == 1 && t1 != 1) { return 1;}
		else if (i2 > i1) { return 1; }
		else if (i2 < i1) { return -1;}
		else{ return 0;}
	});
	for (var i = 0; i < arNotifySort.length; i++)
	{
		var notify = itemsNotify[arNotifySort[i]];
		if (notify.groupped)
		{
			notify.otherCount = 0;
			if (this.notify[notify.id])
			{
				this.notify[notify.id].otherItems = [];
				for (var userId in arGroupedNotifyByUser[notify.tag])
				{
					if (this.notify[notify.id].userId != userId)
						this.notify[notify.id].otherItems.push(arGroupedNotifyByUser[notify.tag][userId]);
				}
				notify.otherCount = this.notify[notify.id].otherItems.length;
			}
			if (notify.otherCount > 0 && notify.type == 2)
				notify.type = 3;
		}
		notify = this.createNotify(notify);
		if (notify !== false)
			arNotify.push(notify);
	}

	if (arNotify.length == 0)
	{
		if (this.messenger.popupMessengerConnectionStatusState != 'online')
		{
			arNotify.push(BX.create("div", { attrs : { style : "padding-top: 231px; margin-bottom: 45px;"}, props : { className : "bx-messenger-box-empty bx-notifier-content-empty", id : "bx-notifier-content-empty"}, html: BX.message('IM_NOTIFY_ERROR')}));
			arNotify.push(
				BX.create('a', { attrs : { href : "#notifyHistory", id : "bx-notifier-content-link-history"}, props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
					BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_NOTIFY_HISTORY_2')})
				]})
			);
			this.notifyLoad = false;
		}
		else if (this.BXIM.settings.loadLastNotify && !this.notifyLoad || this.unreadNotifyLoad)
		{
			arNotify.push(BX.create("div", { attrs : { style : "padding-top: 162px;"}, props : { className: "bx-notifier-content-load", id : "bx-notifier-content-load"}, children : [
				BX.create("div", {props : { className: "bx-notifier-content-load-block bx-notifier-item"}, children : [
					BX.create('span', { props : { className : "bx-notifier-content-load-block-img" }}),
					BX.create('span', {props : { className : "bx-notifier-content-load-block-text"}, html: BX.message('IM_NOTIFY_LOAD_NOTIFY')})
				]})
			]}));
		}
		else if (!loadMore && !this.BXIM.settings.loadLastNotify)
		{
			arNotify.push(BX.create("div", { attrs : { style : "padding-top: 231px; margin-bottom: 45px;"}, props : { className : "bx-messenger-box-empty bx-notifier-content-empty", id : "bx-notifier-content-empty"}, html: BX.message('IM_NOTIFY_EMPTY_2')}));
			arNotify.push(
				BX.create('a', { attrs : { href : "#notifyHistory", id : "bx-notifier-content-link-history"}, props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
					BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_NOTIFY_HISTORY')})
				]})
			);
		}
		else if (!loadMore)
		{
			arNotify.push(BX.create("div", { attrs : { style : "padding-top: 231px; margin-bottom: 45px;"}, props : { className : "bx-messenger-box-empty bx-notifier-content-empty", id : "bx-notifier-content-empty"}, html: BX.message('IM_NOTIFY_EMPTY_3')}));
			arNotify.push(BX.create('a', { attrs : { href : "#notifyHistory", id : "bx-notifier-content-link-history"}, props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
				BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_NOTIFY_HISTORY_LATE')})
			]}));
		}
		if (this.BXIM.settings.loadLastNotify)
			return arNotify;
	}
	else if (!loadMore)
	{
		arNotify.push(
			BX.create('a', { attrs : { href : "#notifyHistory", id : "bx-notifier-content-link-history"}, props : { className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty" }, children: [
				BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-white" }, html: BX.message('IM_NOTIFY_HISTORY_LATE')})
			]})
		);
	}

	return arNotify;
};

BX.Notify.prototype.openNotify = function(reOpen, force)
{
	reOpen = reOpen == true;
	force = force == true;

	if (this.messenger.popupMessenger == null)
	{
		this.messenger.openMessenger(false);
	}

	if (this.BXIM.notifyOpen && !force)
	{
		if (!reOpen)
		{
			this.messenger.extraClose(true);
			return false;
		}
	}
	else
	{
		this.BXIM.dialogOpen = false;
		this.BXIM.notifyOpen = true;
		if (!BX.MessengerCommon.isPage())
		{
			this.messengerNotifyButton.className = "bx-messenger-cl-notify-button bx-messenger-cl-notify-button-active";
		}
	}

	this.messenger.closeMenuPopup();

	this.webrtc.callOverlayToggleSize(true);

	var arNotify = this.drawNotify();
	this.notifyBody = BX.create("div", { props : { className : "bx-notifier-wrap" }, children : [
		BX.create("div", { props : { className : "bx-messenger-panel" }, children : [
			BX.create('span', { props : { className : "bx-messenger-panel-avatar bx-messenger-avatar-notify"}}),
			BX.create("span", { props : { className : "bx-messenger-panel-title bx-messenger-panel-title-middle"}, html: BX.message('IM_NOTIFY_WINDOW_TITLE')})
		]}),
		this.popupNotifyButtonFilterBox = BX.create("div", { props : { className : "bx-messenger-panel-filter-box" }, style : {display: 'none'}, children : [
			BX.create('div', {props : { className : "bx-messenger-filter-name" }, html: BX.message('IM_PANEL_FILTER_NAME')}),
			this.popupHistorySearchDateWrap = BX.create('div', {props : { className : "bx-messenger-filter-date bx-messenger-input-wrap bx-messenger-filter-date-notify" }, html: '<span class="bx-messenger-input-date"></span><a class="bx-messenger-input-close" href="#close"></a><input type="text" class="bx-messenger-input" value="" tabindex="1002" placeholder="'+BX.message('IM_PANEL_FILTER_DATE')+'" />'})
		]}),
		this.popupNotifyItem = BX.create("div", { props : { className : "bx-notifier-item-wrap" }, style : {height: this.popupNotifySize+'px'}, children : arNotify})
	]});
	this.messenger.extraOpen(this.notifyBody);

	clearTimeout(this.popupMessengerTopLineTimeout);
	this.popupMessengerTopLineTimeout = setTimeout(BX.delegate(function(){
		this.BXIM.notifyManager.nativeNotifyAccessForm();
	}, this), 10000);

	if (this.unreadNotifyLoad)
		this.loadNotify();
	else if (!this.notifyLoad && this.BXIM.settings.loadLastNotify)
		this.notifyHistory();

	if (!reOpen && this.BXIM.isFocus('notify') && this.notifyUpdateCount > 0)
		this.viewNotifyAll();

	BX.bind(this.popupNotifyItem, "scroll", BX.delegate(function() {
		if (this.messenger.popupPopupMenu != null)
		{
			if (BX.util.in_array(this.messenger.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-', ''), ["copypaste", "copylink", "notifyDelete", "notify", "external-data"]))
			{
				this.messenger.popupPopupMenu.close();
			}
		}
	}, this));

	BX.bind(BX('bx-notifier-content-link-history'), "click", BX.delegate(this.notifyHistory, this));

	BX.bind(this.popupNotifyItem, "click", BX.delegate(this.closePopup, this));

	BX.bind(this.notifyBody, "click",  BX.delegate(function(e){
		BX.MessengerCommon.contactListSearchClear(e);
	}, BX.MessengerCommon));

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-messenger-ajax'}, BX.delegate(function() {
		if (BX.proxy_context.getAttribute('data-entity') == 'user')
		{
			this.messenger.openPopupExternalData(BX.proxy_context, 'user', true, {'ID': BX.proxy_context.getAttribute('data-userId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'chat')
		{
			this.messenger.openPopupExternalData(BX.proxy_context, 'chat', true, {'ID': BX.proxy_context.getAttribute('data-chatId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'openlines')
		{
			this.messenger.linesOpenHistory(BX.proxy_context.getAttribute('data-sessionId'));
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'network')
		{
			this.messenger.openMessenger('network'+BX.proxy_context.getAttribute('data-networkId'))
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'date')
		{
			this.messenger.openPopupMenu(BX.proxy_context, 'shareMenu');
		}
	}, this));

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-item-help'}, BX.proxy(function(e) {
		if (this.popupNotifyMore != null)
			this.popupNotifyMore.destroy();
		else
		{
			var notifyHelp = this.notify[BX.proxy_context.getAttribute('data-help')];
			if (!notifyHelp.otherItems)
				return false;

			var htmlElement = '<span class="bx-notifier-item-help-popup">';
				for (var i = 0; i < notifyHelp.otherItems.length; i++)
				{
					var avatarColor = BX.MessengerCommon.isBlankAvatar(this.notify[notifyHelp.otherItems[i]].userAvatar)? 'style="background-color: '+this.notify[notifyHelp.otherItems[i]].userColor+'"': '';
					var user = BX.MessengerCommon.getUserParam(this.notify[notifyHelp.otherItems[i]].userId);
					htmlElement += '<a class="bx-notifier-item-help-popup-img" href="'+this.notify[notifyHelp.otherItems[i]].userLink+'"  onclick="BXIM.openMessenger('+this.notify[notifyHelp.otherItems[i]].userId+'); return false;" target="_blank">' +
						'<span class="bx-notifier-popup-avatar bx-notifier-popup-avatar-status-'+user.status+'">' +
							'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(this.notify[notifyHelp.otherItems[i]].userAvatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+this.notify[notifyHelp.otherItems[i]].userAvatar+'" '+avatarColor+'>' +
						'</span>' +
						'<span class="bx-notifier-item-help-popup-name '+(user.extranet? ' bx-notifier-popup-avatar-extranet':'')+'">'+BX.MessengerCommon.prepareText(this.notify[notifyHelp.otherItems[i]].userName)+'</span>' +
					'</a>';
				}
			htmlElement += '</span>';

			this.popupNotifyMore = new BX.PopupWindow('bx-notifier-other-window', BX.proxy_context, {
				//parentPopup: this.messenger.popupMessenger,
				zIndex: 200,
				lightShadow : true,
				offsetTop: -2,
				offsetLeft: 3,
				autoHide: true,
				closeByEsc: true,
				bindOptions: {position: "top"},
				events : {
					onPopupClose : function() { this.destroy() },
					onPopupDestroy : BX.proxy(function() { this.popupNotifyMore = null; }, this)
				},
				content : BX.create("div", { props : { className : "bx-messenger-popup-menu" }, html: htmlElement})
			});
			this.popupNotifyMore.setAngle({});
			this.popupNotifyMore.show();
			BX.bind(this.popupNotifyMore.popupContainer, "click", BX.MessengerCommon.preventDefault);
		}

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-answer-reply'}, BX.proxy(function(e) {
		if (!BX.proxy_context) return;

		if (!this.toggleNotifyAnswer(BX.proxy_context.parentNode))
			return true;

		return BX.PreventDefault(e);
	}, this));

	var item = BX.findChildByClassName(this.popupNotifyItem, "bx-notifier-answer-box-open");
	if (item)
	{
		var itemInput = item.firstChild.nextSibling.firstChild;
		itemInput.focus();
		itemInput.selectionStart = itemInput.value.length+1;
		itemInput.selectionEnd = itemInput.value.length+1;
	}

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-answer-button'}, BX.proxy(function(e) {
		if (!BX.proxy_context) return;

		this.sendNotifyAnswer(BX.proxy_context.parentNode);

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-item-delete'}, BX.proxy(function(e) {
		if (!BX.proxy_context) return;

		BX.proxy_context.setAttribute('id', 'bx-notifier-item-delete-'+BX.proxy_context.getAttribute('data-notifyId'));
		this.deleteNotify(BX.proxy_context.getAttribute('data-notifyId'));

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-item-button-confirm'}, BX.proxy(function(e) {
		if (this.messenger.popupMessengerConnectionStatusState != 'online')
			return false;

		var notifyId = BX.proxy_context.getAttribute('data-id');
		this.confirmRequest({
			'notifyId': notifyId,
			'notifyValue': BX.proxy_context.getAttribute('data-value'),
			'notifyURL': BX.proxy_context.getAttribute('data-url'),
			'notifyTag': this.notify[notifyId] && this.notify[notifyId].tag? this.notify[notifyId].tag: null,
			'groupDelete': BX.proxy_context.getAttribute('data-group') != null
		});
		this.openNotify(true);

		if (BX.MessengerCommon.isMobile())
		{
			if (BX.proxy_context.parentNode.parentNode.parentNode.previousSibling == null && BX.proxy_context.parentNode.parentNode.parentNode.nextSibling == null)
				this.openNotify(true);
			else if (BX.proxy_context.parentNode.parentNode.parentNode.previousSibling == null && BX.proxy_context.parentNode.parentNode.parentNode.nextSibling.tagName.toUpperCase() == 'A')
				this.openNotify(true);
			else
				BX.remove(BX.proxy_context.parentNode.parentNode.parentNode);
		}

		return BX.PreventDefault(e);
	}, this));

	if (BX.MessengerCommon.isDesktop())
	{
		BX.bindDelegate(this.popupNotifyItem, 'contextmenu', {className: 'bx-notifier-item-content'}, BX.delegate(function(e) {
			if (!BX.proxy_context) return;

			BX.proxy_context.parentNode.setAttribute('id', 'bx-notifier-item-delete-'+BX.proxy_context.parentNode.getAttribute('data-notifyId'));
			this.messenger.openPopupMenu(e, 'notify', false);

			return BX.PreventDefault(e);
		}, this));
	}
	else
	{
		BX.bindDelegate(this.popupNotifyItem, 'contextmenu', {className: 'bx-notifier-item-delete'}, BX.proxy(function(e) {
			if (!BX.proxy_context) return;

			BX.proxy_context.setAttribute('id', 'bx-notifier-item-delete-'+BX.proxy_context.getAttribute('data-notifyId'));
			this.messenger.openPopupMenu(BX.proxy_context, 'notifyDelete');

			return BX.PreventDefault(e);
		}, this));
	}

	BX.bindDelegate(this.popupNotifyItem, 'dblclick', {className: 'bx-notifier-item'}, BX.delegate(function(e) {
		if (!BX.proxy_context) return;

		var notifyId = BX.proxy_context.getAttribute('data-notifyId');
		if (this.unreadNotify[notifyId])
		{
			this.viewNotify(notifyId, true);
		}
		else
		{
			this.viewNotify(notifyId, false);
		}

		return BX.PreventDefault(e);
	}, this));

	if (false && !this.BXIM.settings.notifyAutoRead) // TODO read after click
	{
		BX.bindDelegate(this.popupNotifyItem, 'click', {className: 'bx-notifier-item-text-link'}, BX.delegate(function(e) {
			var notifyId = BX.proxy_context.parentNode.parentNode.parentNode.getAttribute('data-notifyId');
			if (this.unreadNotify[notifyId])
			{
				this.viewNotify(notifyId, true);
			}
		}, this));
	}

	return false;
};


BX.Notify.prototype.deleteNotify = function(notifyId)
{
	var notifyDiv = BX('bx-notifier-item-delete-'+notifyId);
	var sendRequest = false;

	if (this.notify[notifyId])
	{
		sendRequest = true;
		var notifyTag = null;
		if (this.notify[notifyId].tag)
		{
			notifyTag = this.notify[notifyId].tag;
		}

		if (this.notify[notifyId].type == 1)
		{
			sendRequest = false;
		}

		var groupDelete = !(!notifyDiv || notifyDiv.getAttribute('data-group') == null || notifyTag == null);
		if (groupDelete)
		{
			for (var i in this.notify)
			{
				if (this.notify[i].tag == notifyTag)
					delete this.notify[i];
			}
		}
		else
		{
			delete this.notify[notifyId];
		}
	}
	this.updateNotifyCount();

	if (sendRequest)
	{
		this.skipMassDelete = true;
		var DATA = {};
		if (groupDelete)
			DATA = {'IM_NOTIFY_GROUP_REMOVE' : 'Y', 'NOTIFY_ID' : notifyId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
		else
			DATA = {'IM_NOTIFY_REMOVE' : 'Y', 'NOTIFY_ID' : notifyId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};

		BX.ajax({
			url: this.BXIM.pathToAjax+'?NOTIFY_REMOVE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: DATA,
			onsuccess: BX.delegate(function(data) {
				setTimeout(BX.delegate(function() {
					this.skipMassDelete = false;
				}, this), 2000);
			}, this)
		});

		if (groupDelete)
			BX.localStorage.set('nrgn', notifyTag, 5);
		else
			BX.localStorage.set('nrn', notifyId, 5);
	}

	if (notifyDiv.parentNode.parentNode.previousSibling == null && notifyDiv.parentNode.parentNode.nextSibling == null)
	{
		this.openNotify(true);
	}
	else if (notifyDiv.parentNode.parentNode.previousSibling == null && notifyDiv.parentNode.parentNode.nextSibling.tagName.toUpperCase() == 'A')
	{
		this.notifyLoad = false;
		this.notifyHistoryPage = 0;
		this.openNotify(true);
	}
	else
	{
		BX.remove(notifyDiv.parentNode.parentNode);
	}

	return true;
};

BX.Notify.prototype.blockNotifyType = function(settingName)
{
	var blockResult = typeof(this.BXIM.settingsNotifyBlocked[settingName]) == 'undefined';
	BX.ajax({
		url: this.BXIM.pathToAjax+'?NOTIFY_BLOCK_TYPE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_NOTIFY_BLOCK_TYPE' : 'Y', 'BLOCK_TYPE' : settingName, 'BLOCK_RESULT' : (blockResult? 'Y': 'N'), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});

	if (blockResult)
	{
		this.BXIM.settingsNotifyBlocked[settingName] = true;
		this.BXIM.settings['site|'.settingName] = false;
		this.BXIM.settings['xmpp|'.settingName] = false;
		this.BXIM.settings['email|'.settingName] = false;
	}
	else
	{
		delete this.BXIM.settingsNotifyBlocked[settingName];
		this.BXIM.settings['site|'.settingName] = true;
		this.BXIM.settings['xmpp|'.settingName] = true;
		this.BXIM.settings['email|'.settingName] = true;
	}

	return true;
};

BX.Notify.prototype.closeNotify = function()
{
	if (!BX.MessengerCommon.isPage())
	{
		this.messengerNotifyButton.className = "bx-messenger-cl-notify-button";
	}

	this.BXIM.notifyOpen = false;
	this.popupNotifyItem = null;
	BX.unbindAll(this.popupNotifyButtonFilter);
	BX.unbindAll(this.popupNotifyItem);
};

BX.Notify.prototype.loadNotify = function(send)
{
	if (this.loadNotityBlock)
		return false;

	send = send != false;
	this.loadNotityBlock = true;
	BX.ajax({
		url: this.BXIM.pathToAjax+'?NOTIFY_LOAD&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		lsId: 'IM_NOTIFY_LOAD',
		lsTimeout: 5,
		timeout: 30,
		data: {'IM_NOTIFY_LOAD' : 'Y', 'IM_AUTO_READ' : (this.BXIM.settings.notifyAutoRead? 'Y': 'N'), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			this.loadNotityBlock = false;
			this.unreadNotifyLoad = false;
			this.notifyLoad = true;
			var arNotify = {};

			if (typeof(data.NOTIFY) == 'object')
			{
				for (var i in data.NOTIFY)
				{
					data.NOTIFY[i].date = new Date(data.NOTIFY[i].date);
					arNotify[i] = this.notify[i] = data.NOTIFY[i];
					this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;

					if (this.BXIM.settings.notifyAutoRead)
					{
						if (data.NOTIFY[i].type != '1')
						{
							delete this.unreadNotify[i];
						}
						else
						{
							this.unreadNotify[i] = i
						}
					}
					else
					{
						this.unreadNotify[i] = i;
					}
				}
			}
			if (send)
			{
				this.openNotify(true);
				if (this.BXIM.settings.loadLastNotify)
					this.notifyHistory();

				BX.localStorage.set('nln', true, 5);
			}

			this.updateNotifyCount();

		}, this),
		onfailure: BX.delegate(function() {
			this.loadNotityBlock = false;
		}, this)
	});
};

BX.Notify.prototype.notifyHistory = function(event)
{
	event = event || window.event;
	if (this.notifyHistoryLoad)
		return false;

	if (this.messenger && this.messenger.popupMessengerConnectionStatusState != 'online')
		return false;

	if (BX('bx-notifier-content-link-history'))
	{
		BX('bx-notifier-content-link-history').innerHTML = '<span class="bx-notifier-item-button bx-notifier-item-button-white">'+BX.message('IM_NOTIFY_LOAD_NOTIFY')+'...'+'</span>';
	}

	this.notifyHistoryLoad = true;
	BX.ajax({
		url: this.BXIM.pathToAjax+'?NOTIFY_HISTORY_LOAD_MORE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_NOTIFY_HISTORY_LOAD_MORE' : 'Y', 'PAGE' : !this.BXIM.settings.loadLastNotify && this.notifyHistoryPage == 0? 1: this.notifyHistoryPage, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}
			if (data.ERROR == '')
			{
				this.notifyLoad = true;
				BX.remove(BX('bx-notifier-content-load'));

				this.sendAjaxTry = 0;
				var arNotify = {};
				var count = 0;
				if (typeof(data.NOTIFY) == 'object')
				{
					for (var i in data.NOTIFY)
					{
						data.NOTIFY[i].date = new Date(data.NOTIFY[i].date);
						if (!this.notify[i])
							arNotify[i] = data.NOTIFY[i];

						if (!this.notify[i])
						{
							this.notify[i] = BX.clone(data.NOTIFY[i]);
						}
						count++;
					}
				}
				if (this.popupNotifyItem)
				{
					if (BX('bx-notifier-content-link-history'))
						BX.remove(BX('bx-notifier-content-link-history'));

					if (count > 0)
					{
						if (BX('bx-notifier-content-empty'))
							BX.remove(BX('bx-notifier-content-empty'));

						var arNotify = this.drawNotify(arNotify, true);
						for (var i = 0; i < arNotify.length; i++)
						{
							this.popupNotifyItem.appendChild(arNotify[i]);
						}
						if (count < 20 && this.notifyHistoryPage > 0)
						{
							BX.remove(BX('bx-notifier-content-link-history'));
						}
						else
						{
							this.popupNotifyItem.appendChild(
								BX.create('a', {
									attrs : {href : "#notifyHistory", id : "bx-notifier-content-link-history"},
									events : {'click' : BX.delegate(this.notifyHistory, this)},
									props : {className : "bx-notifier-content-link-history"},
									children : [
										BX.create('span', {
											props : {className : "bx-notifier-item-button bx-notifier-item-button-white"},
											html : BX.message('IM_NOTIFY_HISTORY_LATE')
										})
									]
								})
							);
							if (count >= 20 && this.notifyHistoryPage == 0)
								this.notifyHistoryPage = 1;
						}
					}
					else if (count <= 0 && this.notifyHistoryPage == 0)
					{
						if (BX('bx-notifier-content-link-history'))
							BX.remove(BX('bx-notifier-content-link-history'));
						this.popupNotifyItem.innerHTML = '';
						this.popupNotifyItem.appendChild(BX.create("div", {
							attrs : {style : "padding-top: 210px; margin-bottom: 20px;"},
							props : {
								className : "bx-messenger-box-empty bx-notifier-content-empty",
								id : "bx-notifier-content-empty"
							},
							html : BX.message('IM_NOTIFY_EMPTY_3')
						}));
						this.popupNotifyItem.appendChild(
							BX.create('a', {
								attrs : {href : "#notifyHistory", id : "bx-notifier-content-link-history"},
								events : {'click' : BX.delegate(this.notifyHistory, this)},
								props : {className : "bx-notifier-content-link-history bx-notifier-content-link-history-empty"},
								children : [
									BX.create('span', {
										props : {className : "bx-notifier-item-button bx-notifier-item-button-white"},
										html : BX.message('IM_NOTIFY_HISTORY_LATE')
									})
								]
							})
						);
					}
					else
					{
						if (this.popupNotifyItem.innerHTML == '')
						{
							this.popupNotifyItem.appendChild(BX.create("div", {
								attrs : {style : "padding-top: 210px; margin-bottom: 20px;"},
								props : {
									className : "bx-messenger-box-empty bx-notifier-content-empty",
									id : "bx-notifier-content-empty"
								},
								html : BX.message('IM_NOTIFY_EMPTY_3')
							}));
						}
					}
				}
				this.notifyHistoryLoad = false;
				this.notifyHistoryPage++;
			}
			else
			{
				if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry < 2)
				{
					this.sendAjaxTry++;
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					setTimeout(BX.delegate(function(){
						this.notifyHistoryLoad = false;
						this.notifyHistory();
					}, this), 2000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR')
				{
					this.sendAjaxTry++;
					if (BX.MessengerCommon.isDesktop())
					{
						setTimeout(BX.delegate(function (){
							this.notifyHistoryLoad = false;
							this.notifyHistory();
						}, this), 10000);
					}
					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
			}
		}, this),
		onfailure: BX.delegate(function(){
			this.notifyHistoryLoad = false;
			this.sendAjaxTry = 0;
		}, this)
	});

	if (event)
		return BX.PreventDefault(event);
	else
		return true;
};

BX.Notify.prototype.adjustPosition = function(params)
{
	if (BX.MessengerCommon.isDesktop())
		return false;

	params = params || {};
	params.timeout = typeof(params.timeout) == "number"? parseInt(params.timeout): 0;

	clearTimeout(this.adjustPositionTimeout);
	this.adjustPositionTimeout = setTimeout(BX.delegate(function(){
		params.scroll = params.scroll || !BX.browser.IsDoctype();
		params.resize = params.resize || false;

		if (!this.windowScrollPos.scrollLeft)
			this.windowScrollPos = {scrollLeft : 0, scrollTop : 0};
		if (params.scroll)
			this.windowScrollPos = BX.GetWindowScrollPos();

		if (params.resize || !this.windowInnerSize.innerWidth)
		{
			this.windowInnerSize = BX.GetWindowInnerSize();

			if (this.BXIM.settings.panelPositionVertical == 'bottom' && typeof(window.scroll) == 'function' && !(BX.browser.IsAndroid() || BX.browser.IsIOS()))
			{
				if (typeof(window.scrollX) != 'undefined' && typeof(window.scrollY) != 'undefined')
				{
					var originalScrollLeft = window.scrollX;
					window.scroll(1, window.scrollY);
					this.windowInnerSize.innerHeight += window.scrollX == 1? -16: 0;
					window.scroll(originalScrollLeft, window.scrollY);
				}
				else
				{
					var scrollX = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft;
					var scrollY = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
					var originalScrollLeft = scrollX;
					window.scroll(1, scrollY);
					scrollX = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft;
					this.windowInnerSize.innerHeight += scrollX == 1? -16: 0;
					window.scroll(originalScrollLeft, scrollY);
				}
			}
		}

		if (params.scroll || params.resize)
		{
			if (this.BXIM.settings.panelPositionHorizontal == 'left')
				this.panel.style.left = (this.windowScrollPos.scrollLeft+25)+'px';
			else if (this.BXIM.settings.panelPositionHorizontal == 'center')
				this.panel.style.left = (this.windowScrollPos.scrollLeft+this.windowInnerSize.innerWidth-this.panel.offsetWidth)/2+'px';
			else if (this.BXIM.settings.panelPositionHorizontal == 'right')
				this.panel.style.left = (this.windowScrollPos.scrollLeft+this.windowInnerSize.innerWidth-this.panel.offsetWidth-35)+'px';

			if (this.BXIM.settings.panelPositionVertical == 'top')
			{
				this.panel.style.top = (this.windowScrollPos.scrollTop)+'px';
				if (BX.hasClass(this.panel, 'bx-notifier-panel-doc'))
					this.panel.className = 'bx-notifier-panel bx-notifier-panel-top bx-notifier-panel-doc';
				else
					this.panel.className = 'bx-notifier-panel bx-notifier-panel-top';
			}
			else if (this.BXIM.settings.panelPositionVertical == 'bottom')
			{
				if (BX.hasClass(this.panel, 'bx-notifier-panel-doc'))
					this.panel.className = 'bx-notifier-panel bx-notifier-panel-bottom bx-notifier-panel-doc';
				else
					this.panel.className = 'bx-notifier-panel bx-notifier-panel-bottom';

				this.panel.style.top = (this.windowScrollPos.scrollTop+this.windowInnerSize.innerHeight-this.panel.offsetHeight)+'px';
			}
		}
	},this), params.timeout);
};

BX.Notify.prototype.move = function(offsetX, offsetY)
{
	var left = parseInt(this.panel.style.left) + offsetX;
	var top = parseInt(this.panel.style.top) + offsetY;

	if (left < 0)
		left = 0;

	var scrollSize = BX.GetWindowScrollSize();
	var floatWidth = this.panel.offsetWidth;
	var floatHeight = this.panel.offsetHeight;

	if (left > (scrollSize.scrollWidth - floatWidth))
		left = scrollSize.scrollWidth - floatWidth;

	if (top > (scrollSize.scrollHeight - floatHeight))
		top = scrollSize.scrollHeight - floatHeight;

	if (top < 0)
		top = 0;

	this.panel.style.left = left + "px";
	this.panel.style.top = top + "px";
};

BX.Notify.prototype._startDrag = function(event)
{
	event = event || window.event;
	BX.fixEventPageXY(event);

	this.dragPageX = event.pageX;
	this.dragPageY = event.pageY;
	this.dragged = false;

	this.closePopup();

	BX.bind(document, "mousemove", BX.proxy(this._moveDrag, this));
	BX.bind(document, "mouseup", BX.proxy(this._stopDrag, this));

	if (document.body.setCapture)
		document.body.setCapture();

	document.body.ondrag = BX.False;
	document.body.onselectstart = BX.False;
	document.body.style.cursor = "move";
	document.body.style.MozUserSelect = "none";
	this.panel.style.MozUserSelect = "none";
	BX.addClass(this.panel, "bx-notifier-panel-drag-"+(this.BXIM.settings.panelPositionVertical == 'top'? 'top': 'bottom'));

	return BX.PreventDefault(event);
};

BX.Notify.prototype._moveDrag = function(event)
{
	event = event || window.event;
	BX.fixEventPageXY(event);

	if(this.dragPageX == event.pageX && this.dragPageY == event.pageY)
		return;

	this.move((event.pageX - this.dragPageX), (event.pageY - this.dragPageY));
	this.dragPageX = event.pageX;
	this.dragPageY = event.pageY;

	if (!this.dragged)
	{
		BX.onCustomEvent(this, "onPopupDragStart");
		this.dragged = true;
	}

	BX.onCustomEvent(this, "onPopupDrag");
};

BX.Notify.prototype._stopDrag = function(event)
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this._moveDrag, this));
	BX.unbind(document, "mouseup", BX.proxy(this._stopDrag, this));

	document.body.ondrag = null;
	document.body.onselectstart = null;
	document.body.style.cursor = "";
	document.body.style.MozUserSelect = "";
	this.panel.style.MozUserSelect = "";
	BX.removeClass(this.panel, "bx-notifier-panel-drag-"+(this.BXIM.settings.panelPositionVertical == 'top'? 'top': 'bottom'));
	BX.onCustomEvent(this, "onPopupDragEnd");

	var windowScrollPos = BX.GetWindowScrollPos();
	this.BXIM.settings.panelPositionVertical = (this.windowInnerSize.innerHeight/2 > (event.pageY - windowScrollPos.scrollTop||event.y))? 'top' : 'bottom';
	if (this.windowInnerSize.innerWidth/3 > (event.pageX- windowScrollPos.scrollLeft||event.x))
		this.BXIM.settings.panelPositionHorizontal = 'left';
	else if (this.windowInnerSize.innerWidth/3*2 < (event.pageX - windowScrollPos.scrollLeft||event.x))
		this.BXIM.settings.panelPositionHorizontal = 'right';
	else
		this.BXIM.settings.panelPositionHorizontal = 'center';

	this.BXIM.saveSettings({'panelPositionVertical': this.BXIM.settings.panelPositionVertical, 'panelPositionHorizontal': this.BXIM.settings.panelPositionHorizontal});

	BX.localStorage.set('npp', {v: this.BXIM.settings.panelPositionVertical, h: this.BXIM.settings.panelPositionHorizontal});

	this.adjustPosition({resize: true});

	this.dragged = false;

	return BX.PreventDefault(event);
};

BX.Notify.prototype.closePopup = function()
{
	if (this.popupNotifyMore != null)
		this.popupNotifyMore.destroy();
	if (this.messenger != null && this.messenger.popupPopupMenu != null)
		this.messenger.popupPopupMenu.destroy();
};

BX.Notify.prototype.createNotify = function(notify, popup)
{
	var element = false;
	if (!notify)
		return false;

	popup = popup == true;

	notify.text = notify.text.replace(/\[like\]/ig, '<span class="bx-smile bx-im-smile-like" title="'+BX.message('IM_MESSAGE_LIKE')+'"></span>');
	notify.text = notify.text.replace(/\[dislike\]/ig, '<span class="bx-smile bx-im-smile-dislike" title="'+BX.message('IM_MESSAGE_DISLIKE')+'"></span>');

	notify.text = notify.text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, function(whole, userId, text)
	{
		var html = '';

		userId = parseInt(userId);
		if (userId > 0 && typeof(BXIM) != 'undefined')
			html = '<span class="bx-messenger-ajax '+(userId == BXIM.userId? 'bx-messenger-ajax-self': '')+'" data-entity="user" data-userId="'+userId+'">'+text+'</span>';
		else
			html = text;

		return html;
	});

	notify.text = notify.text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function(whole, openlines, chatId, text)
	{
		var html = '';

		chatId = parseInt(chatId);

		if (chatId > 0)
		{
			if (openlines)
			{
				html = '<span class="bx-messenger-ajax" data-entity="openlines" data-sessionId="'+chatId+'">'+text+'</span>';
			}
			else
			{
				html = '<span class="bx-messenger-ajax" data-entity="chat" data-chatId="'+chatId+'">'+text+'</span>';
			}
		}
		else
		{
			html = text;
		}

		return html;
	});

	notify.text = notify.text.replace(/\[RATING\=([1-5]{1})\]/ig, BX.delegate(function(whole, rating)
	{
		return BX.MessengerCommon.linesVoteHeadNodes(0, rating, false).outerHTML;
	}, this));

	if (BX.MessengerCommon.isDesktop() || this.BXIM.context == "FULLSCREEN" || this.BXIM.context == "PAGE")
	{
		notify.text = notify.text.replace(/<a(.*?)>(.*?)<\/a>/ig, BX.delegate(function(whole, aInner, text)
		{
			return '<a'+aInner.replace('target="_self"', 'target="_blank"')+' class="bx-notifier-item-text-link">'+text+'</a>';
		}, this));
	}

	var itemNew = (this.unreadNotify[notify.id] && !popup? " bx-notifier-item-new": "");
	notify.userAvatar = notify.userAvatar? notify.userAvatar: this.BXIM.pathToBlankImage;

	var attachNode = notify.params && notify.params.ATTACH? BX.MessengerCommon.drawAttach(0, 0, notify.params.ATTACH): [];
	if (attachNode.length > 0)
	{
		attachNode = BX.create("div", { props : { className : "bx-messenger-attach-box" }, children: attachNode});
	}
	else
	{
		attachNode = null;
	}

	if (notify.type == 1 && typeof(notify.buttons) != "undefined" && notify.buttons.length > 0)
	{
		var arButtons = [];
		var canConfirmDelete = false;
		if (typeof(notify.confirmMessages) != 'undefined')
		{
			canConfirmDelete = true;
			for (var i = 0; i < notify.confirmMessages.length; i++)
			{
				arButtons.push(BX.create('div', {props : { className : "bx-notifier-item-confirm-message"}, html: notify.confirmMessages[i]}));
			}
		}
		else
		{
			for (var i = 0; i < notify.buttons.length; i++)
			{
				var type = notify.buttons[i].TYPE == 'accept'? 'accept': (notify.buttons[i].TYPE == 'cancel'? 'cancel': 'default');
				var arAttr = { 'data-id' : notify.id, 'data-value' : notify.buttons[i].VALUE};
				if (notify.grouped)
					arAttr['data-group'] = 'Y';

				if (notify.buttons[i].URL)
					arAttr['data-url'] = notify.buttons[i].URL;

				arButtons.push(BX.create('span', {props : { className : "bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-"+type }, attrs : arAttr, html: notify.buttons[i].TITLE}));
			}
		}
		element = BX.create("div", {attrs : {'data-notifyId' : notify.id, 'data-notifyType' : notify.type}, props : { className: "bx-notifier-item bx-notifier-item-"+notify.id+" "+itemNew}, children : [
			BX.create('span', {props : { className : "bx-notifier-item-content" }, children : [
				BX.create('span', {props : { className : "bx-notifier-item-avatar" }, children : [
					BX.create('img', {props : { className : "bx-notifier-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? " bx-notifier-item-avatar-img-default": "") }, attrs : {src : notify.userAvatar, style: (BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? 'background-color: '+notify.userColor: '')}})
				]}),
				!canConfirmDelete? BX.create("span", {props : { className: "bx-notifier-item-delete bx-notifier-item-delete-fake"}}): BX.create("a", {attrs : {href : '#', 'data-notifyId' : notify.id, 'data-notifyType' : notify.type, title: BX.message('IM_NOTIFY_DELETE_1')}, props : { className: "bx-notifier-item-delete"}}),
				BX.create('span', {props : { className : "bx-notifier-item-date" }, html: BX.MessengerCommon.formatDate(notify.date)}),
				notify.userName? BX.create('span', {props : { className : "bx-notifier-item-name" }, html: '<a href="'+notify.userLink+'" onclick="if (BXIM.init) { BXIM.openMessenger('+notify.userId+'); return false; } ">'+BX.MessengerCommon.prepareText(notify.userName)+'</a>'}): null,
				BX.create('span', {props : { className : "bx-notifier-item-text" }, html: notify.text}),
				attachNode,
				BX.create('span', {props : { className : "bx-notifier-item-button-wrap" }, children : arButtons})
			]})
		]});
	}
	else if (notify.type == 2 || (notify.type == 1 && typeof(notify.buttons) != "undefined" && notify.buttons.length <= 0))
	{
		element = BX.create("div", {attrs : {'data-notifyId' : notify.id, 'data-notifyType' : notify.type}, props : { className: "bx-notifier-item bx-notifier-item-"+notify.id+" "+itemNew}, children : [
			BX.create('span', {props : { className : "bx-notifier-item-content" }, children : [
				BX.create('span', {props : { className : "bx-notifier-item-avatar" }, children : [
					BX.create('img', {props : { className : "bx-notifier-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? " bx-notifier-item-avatar-img-default": "") },attrs : {src : notify.userAvatar, style: (BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? 'background-color: '+notify.userColor: '')}})
				]}),
				BX.create("a", {attrs : {href : '#', 'data-notifyId' : notify.id, 'data-notifyType' : notify.type, title: BX.message('IM_NOTIFY_DELETE_1')}, props : { className: "bx-notifier-item-delete"}}),
				BX.create('span', {props : { className : "bx-notifier-item-date" }, html: BX.MessengerCommon.formatDate(notify.date)}),
				BX.create('span', {props : { className : "bx-notifier-item-name" }, html: '<a href="'+notify.userLink+'" onclick="if (BXIM.init) { BXIM.openMessenger('+notify.userId+'); return false; } ">'+BX.MessengerCommon.prepareText(notify.userName)+'</a>'}),
				BX.create('span', {props : { className : "bx-notifier-item-text" }, html: notify.text}),
				attachNode,
				this.drawNotifyAnswer(notify)
			]})
		]});
	}
	else if (notify.type == 3)
	{
		element = BX.create("div", {attrs : {'data-notifyId' : notify.id, 'data-notifyType' : notify.type}, props : { className: "bx-notifier-item bx-notifier-item-"+notify.id+" "+itemNew}, children : [
			BX.create('span', {props : { className : "bx-notifier-item-content" }, children : [
				BX.create('span', {props : { className : "bx-notifier-item-avatar-group" }, children : [
					BX.create('span', {props : { className : "bx-notifier-item-avatar" }, children : [
						BX.create('img', {props : { className : "bx-notifier-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? " bx-notifier-item-avatar-img-default": "") },attrs : {src : notify.userAvatar, style: (BX.MessengerCommon.isBlankAvatar(notify.userAvatar)? 'background-color: '+notify.userColor: '')}})
					]})
				]}),
				BX.create("a", {attrs : {href : '#', 'data-notifyId' : notify.id, 'data-group' : 'Y', 'data-notifyType' : notify.type, title: BX.message('IM_NOTIFY_DELETE_1')}, props : { className: "bx-notifier-item-delete"}}),
				BX.create('span', {props : { className : "bx-notifier-item-date" }, html: BX.MessengerCommon.formatDate(notify.date)}),
				BX.create('span', {props : { className : "bx-notifier-item-name" }, html: BX.message('IM_NOTIFY_GROUP_NOTIFY').replace('#USER_NAME#', '<a href="'+notify.userLink+'" onclick="if (BXIM.init) { BXIM.openMessenger('+notify.userId+'); return false;} ">'+BX.MessengerCommon.prepareText(notify.userName)+'</a>').replace('#U_START#', '<span class="bx-notifier-item-help" data-help="'+notify.id+'">').replace('#U_END#', '</span>').replace('#COUNT#', notify.otherCount)}),
				BX.create('span', {props : { className : "bx-notifier-item-text" }, html: notify.text}),
				attachNode,
				this.drawNotifyAnswer(notify)
			]})
		]});
	}
	else
	{
		element = BX.create("div", {attrs : {'data-notifyId' : notify.id, 'data-notifyType' : notify.type}, props : { className: "bx-notifier-item bx-notifier-item-"+notify.id+" "+itemNew}, children : [
			BX.create('span', {props : { className : "bx-notifier-item-content" }, children : [
				BX.create('span', {props : { className : "bx-notifier-item-avatar" }, children : [
					BX.create('img', {props : { className : "bx-notifier-item-avatar-img bx-notifier-item-avatar-img-default-2" },attrs : {src : notify.userAvatar}})
				]}),
				BX.create("a", {attrs : {href : '#', 'data-notifyId' : notify.id, 'data-notifyType' : notify.type, title: BX.message('IM_NOTIFY_DELETE_1')}, props : { className: "bx-notifier-item-delete"}}),
				BX.create('span', {props : { className : "bx-notifier-item-date" }, html: BX.MessengerCommon.formatDate(notify.date)}),
				notify.title && notify.title.length>0? BX.create('span', {props : { className : "bx-notifier-item-name" }, html: BX.MessengerCommon.prepareText(notify.title)}): null,
				BX.create('span', {props : { className : "bx-notifier-item-text" }, html: notify.text}),
				attachNode,
				this.drawNotifyAnswer(notify)
			]})
		]});
	}
	return element;
};

BX.Notify.prototype.drawNotifyAnswer = function(notify)
{
	var node = null;
	if (typeof(notify.params) == 'object' && notify.params.CAN_ANSWER != 'Y')
		return node;

	value = this.notifyAnswerText[notify.id]? this.notifyAnswerText[notify.id]: "";

	node = BX.create('div', {props : { className : "bx-notifier-item-text" }, children : [
		BX.create('div', {props : { className : "bx-notifier-answer-link" }, children : [
			BX.create("span", {props : { className : "bx-notifier-answer-reply bx-messenger-ajax" }, html: BX.message('IM_N_REPLY')})
		]}),
		BX.create('div', {attrs: {'data-id': notify.id}, props : { className : "bx-notifier-answer-box"+(value? ' bx-notifier-answer-box-open': '') }, children : [
			BX.create("span", {props : { className : "bx-notifier-answer-progress" }}),
			BX.create('span', {props : { className : "bx-notifier-answer-input" }, children : [
				BX.create("input", {attrs: {type: "text", value: value, 'data-id': notify.id}, events: { 'keydown': BX.delegate(function(event){
					if (event.keyCode == 13)
					{
						this.sendNotifyAnswer(BX.proxy_context.parentNode.parentNode);
					}
					else if (event.keyCode == 27)
					{
						if (BX.proxy_context.value != "")
						{
							BX.proxy_context.value = "";
							this.notifyAnswerText[BX.proxy_context.getAttribute('data-id')] = "";
						}
						else
						{
							this.toggleNotifyAnswer(BX.proxy_context.parentNode.parentNode.previousSibling);
						}
						return BX.MessengerCommon.preventDefault(event);
					}
				}, this), 'keyup': BX.delegate(function(event){
					this.notifyAnswerText[BX.proxy_context.getAttribute('data-id')] = BX.proxy_context.value;
				}, this)}, props : { className : "bx-messenger-input" }})
			]}),
			BX.create("a", {attrs: {href: "#send"}, props : { className : "bx-notifier-answer-button" }})
		]}),
		BX.create('div', {props : { className : "bx-notifier-answer-text" }, html: BX.message('IM_N_REPLY_TEXT')})
	]});

	return node;
}
BX.Notify.prototype.toggleNotifyAnswer = function(notifyAnswer)
{
	var id = notifyAnswer.nextSibling.getAttribute('data-id');
	if (this.notifyAnswerBlock[id])
		return false;

	BX.toggleClass(notifyAnswer.nextSibling, 'bx-notifier-answer-box-open');
	BX.removeClass(notifyAnswer.nextSibling.nextSibling, 'bx-notifier-answer-text-show');

	var item = BX.findChildByClassName(notifyAnswer.nextSibling, "bx-messenger-input");
	if (item)
	{
		item.focus();
	}

	return true;
}
BX.Notify.prototype.sendNotifyAnswer = function(notifyAnswer, popup)
{
	var id = notifyAnswer.getAttribute('data-id');
	if (this.notifyAnswerBlock[id])
		return true;

	var input = BX.findChildByClassName(notifyAnswer, "bx-messenger-input");
	if (!input)
		return false;

	input.value = BX.util.trim(input.value);
	if (input.value == "")
	{
		return true;
	}

	if (!this.BXIM.init && BX.MessengerCommon.isDesktop())
		BX.desktop.windowCommand("freeze");

	this.notifyAnswerBlock[id] = true;
	this.notifyAnswerText[id] = input.value;

	input.disabled = true;

	BX.addClass(notifyAnswer, 'bx-notifier-answer-box-send');

	BX.ajax({
		url: this.BXIM.pathToAjax+'?NOTIFY_ANSWER&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_NOTIFY_ANSWER' : 'Y', 'NOTIFY_ID' : id, 'NOTIFY_ANSWER' : input.value, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			BX.removeClass(notifyAnswer, 'bx-notifier-answer-box-error');
			BX.removeClass(notifyAnswer, 'bx-notifier-answer-box-send');
			this.notifyAnswerBlock[id] = false;
			this.notifyAnswerText[id] = "";
			var input = BX.findChildByClassName(notifyAnswer, "bx-messenger-input");
			if (input)
			{
				input.disabled = false;
			}

			if (data.ERROR == "")
			{
				BX.removeClass(notifyAnswer, 'bx-notifier-answer-box-open');
				BX.addClass(notifyAnswer.nextSibling, 'bx-notifier-answer-text-show');

				if (data.MESSAGES && data.MESSAGES.length > 0)
				{
					notifyAnswer.nextSibling.innerHTML = data.MESSAGES.join("<br/>");
				}

				if (input)
				{
					input.value = "";
				}

				if (!this.BXIM.init && BX.MessengerCommon.isDesktop())
					BX.desktop.windowCommand("close");
			}
			else
			{
				BX.addClass(notifyAnswer, 'bx-notifier-answer-box-error');
			}
		}, this),
		onfailure: BX.delegate(function() {
			BX.addClass(notifyAnswer, 'bx-notifier-answer-box-error');
			BX.removeClass(notifyAnswer, 'bx-notifier-answer-box-send');
			this.notifyAnswerBlock[id] = false;

			var input = BX.findChildByClassName(notifyAnswer, "bx-messenger-input");
			if (input)
			{
				input.disabled = false;
			}
		}, this)
	});

	return true;
}


BX.Notify.prototype.storageSet = function(params)
{
	if (params.key == 'npp')
	{
		var panelPosition = BX.localStorage.get(params.key);
		this.BXIM.settings.panelPositionHorizontal = !!panelPosition? panelPosition.h: this.BXIM.settings.panelPositionHorizontal;
		this.BXIM.settings.panelPositionVertical = !!panelPosition? panelPosition.v: this.BXIM.settings.panelPositionVertical;
		this.adjustPosition({resize: true});
	}
	else if (params.key == 'nun')
	{
		this.notify = params.value;
	}
	else if (params.key == 'nrn')
	{
		delete this.notify[params.value];
		this.updateNotifyCount(false);
	}
	else if (params.key == 'nrgn')
	{
		for (var i in this.notify)
		{
			if (this.notify[i].tag == params.value)
				delete this.notify[i];
		}
		this.updateNotifyCount();
	}
	else if (params.key == 'numc')
	{
		this.updateNotifyMailCount(params.value, false);
	}
	else if (params.key == 'nuc')
	{
		this.updateNotifyCounters(params.value, false);
	}
	else if (params.key == 'nunc')
	{
		setTimeout(BX.delegate(function(){
			this.unreadNotify = params.value.unread;
			this.flashNotify = params.value.flash;

			this.updateNotifyCount(false);
		},this), 500);
	}
	else if (params.key == 'nln')
	{
		this.loadNotify(false);
	}
};

})();


/* IM messenger class */
(function() {

if (BX.Messenger)
	return;

BX.Messenger = function(BXIM, params)
{
	this.BXIM = BXIM;
	this.BXIM.messenger = this;

	this.settings = {};
	this.params = params || {};

	this.realSearchAvailable = !this.BXIM.userExtranet || !this.BXIM.bitrixIntranet && !this.BXIM.bitrix24net;
	this.realSearch = !this.BXIM.options.contactListLoad;
	this.realSearchFound = true;

	this.updateStateCount = 1;
	this.sendAjaxTry = 0;
	this.updateStateVeryFastCount = 0;
	this.updateStateFastCount = 0;
	this.updateStateStepDefault = this.BXIM.ppStatus? parseInt(params.updateStateInterval): 60;
	this.updateStateStep = this.updateStateStepDefault;
	this.updateStateTimeout = null;
	this.redrawContactListTimeout = {};
	this.redrawRecentListTimeout = null;
	this.floatDateTimeout = null;
	this.readMessageTimeout = {};
	this.readMessageTimeoutSend = null;

	this.sendFrameTokenCollection = {};
	this.sendFrameTokenTimeout = 500;

	this.webrtc = params.webrtcClass;
	this.notify = params.notifyClass;
	this.desktop = params.desktopClass;

	this.bot = params.bot;
	this.command = params.command;
	this.commandPopup = null;
	this.commandListen = false;
	this.commandList = [];
	this.commandSelect = '';
	this.commandSelectIndex = 1;
	this.textareaIcon = params.textareaIcon;

	this.smile = params.smile;
	this.smileSet = params.smileSet;
	this.smileCurrentSet = this.BXIM.getLocalConfig('smiles-current-set', 0) || [];
	this.smileRecentId = 1;
	this.getRecentSmiles();

	this.recentListIndex = [];
	if (params.recent)
	{
		this.recent = params.recent;
		this.recentListLoad = true;
	}
	else
	{
		this.recent = [];
		this.recentListLoad = false;
	}

	this.recentListExternal = null;
	if (params.externalRecentList)
	{
		this.recentListExternal = BX(params.externalRecentList);
	}

	this.popupTooltip = null;

	this.users = params.users;
	for (var userId in this.users)
	{
		this.users[userId].absent = this.users[userId].absent? new Date(this.users[userId].absent): false;
		this.users[userId].idle = this.users[userId].idle? new Date(this.users[userId].idle): false;
		this.users[userId].last_activity_date = new Date(this.users[userId].last_activity_date);
		this.users[userId].mobile_last_date = new Date(this.users[userId].mobile_last_date);
	}

	this.businessUsers = params.businessUsers;
	this.openlines = params.openlines;
	this.groups = params.groups;
	this.userInGroup = params.userInGroup;
	this.currentTab = 0;
	this.generalChatId = params.generalChatId;
	this.canSendMessageGeneralChat = params.canSendMessageGeneralChat;
	this.redrawTab = {};
	this.loadLastMessageTimeout = {};
	this.loadLastMessageClassTimeout = {};
	this.showMessage = params.showMessage;
	this.unreadMessage = params.unreadMessage;
	this.flashMessage = params.flashMessage;
	this.tooltipShowed = params.tooltipShowed || {};

	this.disk = params.diskClass;
	this.disk.messenger = this;
	this.popupMessengerFileForm = null;
	this.popupMessengerFileDropZone = null;
	this.popupMessengerFileButton = null;
	this.popupMessengerFileFormChatId = null;
	this.popupMessengerFileFormInput = null;

	this.openChatEnable = params.openChatEnable;
	this.chat = params.chat;
	for (var chatId in this.chat)
	{
		this.chat[chatId].date_create = new Date(this.chat[chatId].date_create);
	}

	this.userChat = params.userChat;
	this.userInChat = params.userInChat;
	this.userChatBlockStatus = params.userChatBlockStatus;
	this.userChatOptions = params.userChatOptions;
	this.blockJoinChat = {};
	this.hrphoto = params.hrphoto;

	this.chatPublicWatch = 0;
	this.chatPublicWatchAdd = false;

	this.popupIframeBind = true;
	this.popupIframeMenu = null;

	this.popupMessengerLiveChatDelayedFormMid = 0;
	this.popupMessengerLiveChatActionTimeout = null;
	this.popupMessengerLiveChatDelayedForm = null;
	this.popupMessengerLiveChatFormStage = null;

	this.phones = {};

	this.errorMessage = {};
	this.message = params.message;
	for (var messageId in this.message)
	{
		this.message[messageId].date = new Date(this.message[messageId].date);
	}

	this.messageTmpIndex = 0;
	this.history = params.history;
	this.textareaHistory = {};
	this.textareaHistoryTimeout = null;
	this.messageCount = params.countMessage;
	this.sendMessageFlag = 0;
	this.sendMessageTmp = {};
	this.sendMessageTmpTimeout = {};

	this.popupSettings = null;
	this.popupSettingsBody = null;

	this.popupChatDialog = null;
	this.popupChatDialogContactListElements = null;
	this.popupChatDialogContactListSearch = null;
	this.popupChatDialogContactListElementsType = '';
	this.popupChatDialogContactListSearchLastText = '';
	this.popupChatDialogDestElements = null;
	this.popupChatDialogUsers = {};
	this.popupChatDialogSendBlock = false;
	this.renameChatDialogFlag = false;
	this.renameChatDialogInput = null;

	this.popupHistory = null;
	this.popupHistoryElements = null;
	this.popupHistoryItems = null;
	this.popupHistoryItemsSize = 475;
	this.popupHistorySearchDateWrap = null;
	this.popupHistorySearchWrap = null;
	this.popupHistoryFilesSearchWrap = null;
	this.popupHistoryButtonDeleteAll = null;
	this.popupHistoryButtonFilter = null;
	this.popupHistoryButtonFilterBox = null;
	this.popupHistoryFilterVisible = true;
	this.popupHistoryBodyWrap = null;
	this.popupHistoryFilesItems = null;
	this.popupHistoryFilesBodyWrap = null;
	this.popupHistorySearchInput = null;
	this.historyUserId = 0;
	this.historyChatId = 0;
	this.historyDateSearch = '';
	this.historySearch = '';
	this.historyLastSearch = {};
	this.historySearchBegin = false;
	this.historySearchTimeout = null;
	this.historyFilesSearch = '';
	this.historyFilesLastSearch = {};
	this.historyFilesSearchBegin = false;
	this.historyFilesSearchTimeout = null;
	this.historyWindowBlock = false;
	this.historyMessageSplit = '------------------------------------------------------';
	this.historyOpenPage = {};
	this.historyLoadFlag = {};
	this.historyEndOfList = {};
	this.historyFilesOpenPage = {};
	this.historyFilesLoadFlag = {};
	this.historyFilesEndOfList = {};

	this.popupMessenger = null;
	this.popupMessengerWindow = {};
	this.popupMessengerExtra = null;
	this.popupMessengerTopLine = null;
	this.popupMessengerDesktopTimeout = null;
	this.popupMessengerFullWidth = 864;
	this.popupMessengerMinWidth = 864;
	this.popupMessengerFullHeight = 454;
	this.popupMessengerMinHeight = 384;
	this.popupMessengerDialog = null;
	this.popupMessengerBody = null;
	this.popupMessengerBodyDialog = null;
	this.popupMessengerBodyAnimation = null;
	this.popupMessengerBodySize = 316;
	this.popupMessengerBodySizeMin = 246;
	this.popupMessengerBodyWrap = null;

	this.popupMessengerLikeBlock = {};
	this.popupMessengerLikeBlockTimeout = {};

	this.popupMessengerSendingTimeout = {};

	this.popupMessengerConnectionStatusState = "online";
	this.popupMessengerConnectionStatusStateText = "online";
	this.popupMessengerConnectionStatus = null;
	this.popupMessengerConnectionStatusText = null;
	this.popupMessengerConnectionStatusTimeout = null;

	this.popupMessengerEditForm = null;
	this.popupMessengerEditFormTimeout = null;
	this.popupMessengerEditTextarea = null;
	this.popupMessengerEditMessageId = 0;

	this.popupMessengerPanel = null;
	this.popupMessengerPanelBotIcons = false;
	this.popupMessengerPanelAvatar = null;
	this.popupMessengerPanelButtonCall1 = null;
	this.popupMessengerPanelButtonCall2 = null;
	this.popupMessengerPanelButtonCall3 = null;
	this.popupMessengerPanelTitle = null;
	this.popupMessengerPanelStatus = null;

	this.popupMessengerPanelChat = null;
	this.popupMessengerPanelCall = null;
	this.popupMessengerPanelChatTitle = null;
	this.popupMessengerPanelUsers = null;

	this.popupMessengerTextareaPlace = null;
	this.popupMessengerTextarea = null;
	this.popupMessengerTextareaSendType = null;
	this.popupMessengerTextareaResize = {};
	this.popupMessengerTextareaSize = 30;
	this.popupMessengerLastMessage = 0;

	this.mentionList = {};
	this.mentionListen = false;
	this.mentionDelimiter = '';

	this.readedList = {};
	this.writingList = {};
	this.writingListTimeout = {};
	this.writingSendList = {};
	this.writingSendListTimeout = {};

	this.contactListPanelStatus = null;
	this.contactListSearchText = '';
	this.contactListSearchLastText = '';

	this.popupPopupMenu = null;
	this.popupPopupMenuModifyFunction = [];
	this.popupPopupMenuDateCreate = 0;

	this.popupSmileMenu = null;
	this.popupSmileMenuGallery = null;
	this.popupSmileMenuSet = null;

	this.chatList = false;
	this.recentList = true;
	this.contactList = false;
	this.contactListShowed = {};

	this.openMessengerFlag = false;
	this.openChatFlag = false;
	this.openNetworkFlag = false;
	this.openBotFlag = false;
	this.openCallFlag = false;

	this.contactListLoad = !this.BXIM.options.contactListLoad;
	this.popupContactListSize = 254;
	this.popupContactListSearchInput = null;
	this.popupContactListSearchClose = null;
	this.popupContactListWrap = null;
	this.popupContactListElements = null;
	this.popupContactListElementsSize = this.BXIM.design == 'DESKTOP'? 368: 334;
	this.popupContactListElementsSizeMin = this.BXIM.design == 'DESKTOP'? 298: 264;
	this.popupContactListElementsWrap = null;
	this.contactListPanelSettings = null;

	this.linesTransferUser = 0;
	this.linesSilentMode = {};
	this.linesLiveChatVote = false;

	this.enableGroupChat = this.BXIM.ppStatus? true: false;

	if (this.BXIM.init)
	{
		if (BX.MessengerCommon.isPage())
		{
			BX.MessengerWindow.setUserInfo(BX.MessengerCommon.getUserParam());

			BX.MessengerWindow.addTab({
				id: 'im',
				title: BX.message('IM_DESKTOP_OPEN_MESSENGER').replace('#COUNTER#', ''),
				order: 100,
				events: {
					open: BX.delegate(function(){
						if (BX.MessengerCommon.isPage() && this.BXIM.context == 'POPUP-FULLSCREEN' && !this.popupMessenger)
						{
							return false;
						}
						if (!this.BXIM.dialogOpen)
						{
							this.openMessenger(this.currentTab);
						}
					}, this)
				}
			});
			if (this.webrtc.phoneSupport() && this.webrtc.phoneCanPerformCalls)
			{
				BX.MessengerWindow.addTab({
					id: 'im-phone',
					title: BX.message('IM_PHONE_DESC'),
					order: 120,
					target: 'im',
					events: {
						open: BX.delegate(this.webrtc.openKeyPad, this.webrtc),
						close: BX.delegate(function(){
							if (this.webrtc.popupKeyPad)
								this.webrtc.popupKeyPad.close();
						}, this)
					}
				});
			}
			if (this.BXIM.settings.linesTabEnable && BX.MessengerCommon.isLinesOperator())
			{
				BX.MessengerWindow.addTab({
					id: 'im-ol',
					title: BX.message('IM_CTL_CHAT_OL'),
					order: 105,
					target: 'im',
					events: {
						open: BX.delegate(function(){
							if (BX.MessengerCommon.isPage() && this.BXIM.context == 'POPUP-FULLSCREEN' && !this.popupMessenger)
							{
								return false;
							}
							if (!this.BXIM.dialogOpen)
							{
								this.openMessenger(this.currentTab);
							}
							BX.MessengerCommon.userListRedraw();
						}, this),
						close: BX.delegate(function(){
							BX.MessengerCommon.userListRedraw();
						}, this)
					}
				});
			}
		}

		BX.addCustomEvent("onPullError", BX.delegate(function(error, code) {
			if (error == 'AUTHORIZE_ERROR')
			{
				if (BX.MessengerCommon.isDesktop())
				{
					this.connectionStatus('connecting');
				}
				else
				{
					this.connectionStatus('offline');
				}
			}
			else if (error == 'RECONNECT' && (code == 1008 || code == 1006))
			{
				this.connectionStatus('connecting');
			}
		}, this));

		BX.addCustomEvent("OnDesktopTabChange", BX.delegate(function() {
			if (this.BXIM.messenger.chatList)
			{
				BX.MessengerCommon.contactListSearchClear();
			}
			this.closeMenuPopup();
		}, this));
		BX.addCustomEvent("OnMessengerWindowShowPopup", BX.delegate(function(dialogId) {
			this.openMessenger(dialogId);
		}, this));
		BX.addCustomEvent("OnMessengerWindowClosePopup", BX.delegate(function() {
			this.closeMessenger();
		}, this));

		BX.addCustomEvent("onImError", BX.delegate(function(error, sendErrorCode) {
			if (error == 'AUTHORIZE_ERROR' || error == 'SEND_ERROR' && sendErrorCode == 'AUTHORIZE_ERROR')
			{
				if (BX.MessengerCommon.isDesktop())
				{
					this.connectionStatus('connecting');
				}
				else
				{
					this.connectionStatus('offline');
				}
			}
		}, this));

		BX.addCustomEvent("onPullStatus", BX.delegate(function(status){
			this.connectionStatus(status == 'offline'? 'offline': 'online');
		}, this));

		BX.bind(window, "online", BX.delegate(function(){
			this.connectionStatus('online');
		}, this));

		BX.bind(window, "offline", BX.delegate(function(){
			this.connectionStatus('offline')
		}, this));

		this.notify.panel.appendChild(this.BXIM.audio.newMessage1 = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-1.ogg", type : "audio/ogg; codecs=vorbis" }}),
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-1.mp3", type : "audio/mpeg" }})
		]}));
		this.notify.panel.appendChild(this.BXIM.audio.newMessage2 = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-2.ogg", type : "audio/ogg; codecs=vorbis" }}),
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-2.mp3", type : "audio/mpeg" }})
		]}));
		this.notify.panel.appendChild(this.BXIM.audio.send = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/send.ogg", type : "audio/ogg; codecs=vorbis" }}),
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/send.mp3", type : "audio/mpeg" }})
		]}));
		if (typeof(this.BXIM.audio.send.play) == 'undefined')
		{
			this.BXIM.settings.enableSound = false;
		}

		for (var i in this.unreadMessage)
		{
			if (typeof (this.flashMessage[i]) == 'undefined')
				this.flashMessage[i] = {};
			for (var k = this.unreadMessage[i].length - 1; k >= 0; k--)
			{
				BX.localStorage.set('mum', {'userId': i, 'message': this.message[this.unreadMessage[i][k]]}, 5);
			}
		}
		BX.localStorage.set('muum', this.unreadMessage, 5);

		if (this.notify.panelButtonMessage)
		{
			BX.bind(this.notify.panelButtonMessage, "click", BX.delegate(function(e){
				this.BXIM.openMessenger(true);
			}, this));
		}

		var mcesh = this.BXIM.getLocalConfig('mcesh', null);
		if (mcesh !== null)
		{
			this.BXIM.options.chatExtendShowHistory = mcesh;
		}

		var mtabs = this.BXIM.getLocalConfig('global_msz_v2', false);
		if (!mtabs && BX.MessengerCommon.isPage())
		{
			this.desktop.initHeight = BX.MessengerWindow.initHeight;

			if (BX.MessengerCommon.isDesktop())
			{
				if (!BX.browser.IsMac() && !this.desktop.enableInVersion(37))
				{
					BXDesktopWindow.SetProperty("clientSize", {Width: window.innerWidth, Height: window.innerHeight});
				}
				this.tmpTextareaResize = BX.delegate(function(){
					var textareaSize = this.BXIM.getLocalConfig('global_tas', this.popupMessengerTextareaSize);
					this.setTextareaSize(textareaSize);
					BX.unbind(window, "resize", this.tmpTextareaResize);
				}, this)
				BX.bind(window, "resize", this.tmpTextareaResize);
			}
			else
			{
				BX.addCustomEvent('onImInit', BX.delegate(function(){
					var textareaSize = this.BXIM.getLocalConfig('global_tas', this.BXIM.context == 'POPUP-FULLSCREEN'? 60: this.popupMessengerTextareaSize);
					this.setTextareaSize(textareaSize);
				}, this));
			}
		}
		else if (mtabs && (!BX.MessengerCommon.isPage() || BX.MessengerCommon.isDesktop()))
		{
			this.popupMessengerFullWidth = parseInt(mtabs.wz);
			this.popupMessengerTextareaSize = parseInt(mtabs.ta2);
			this.popupMessengerBodySize = parseInt(mtabs.b) > 0? parseInt(mtabs.b): this.popupMessengerBodySize;
			this.popupHistoryItemsSize = parseInt(mtabs.hi);
			this.popupMessengerFullHeight = parseInt(mtabs.fz);
			this.popupContactListElementsSize = parseInt(mtabs.ez);
			this.notify.popupNotifySize = parseInt(mtabs.nz);
			this.popupHistoryFilterVisible = mtabs.hf;
			if (BX.MessengerCommon.isDesktop())
			{
				BX.desktop.setWindowSize({ Width: parseInt(mtabs.dw), Height: parseInt(mtabs.dh) })
				this.desktop.initHeight = parseInt(mtabs.dh);
			}
		}
		else
		{
			if (BX.MessengerCommon.isDesktop())
			{
				BX.desktop.setWindowSize({ Width: BX.MessengerWindow.initWidth, Height: BX.MessengerWindow.initHeight });
				this.desktop.initHeight = BX.MessengerWindow.initHeight;
			}
			else if (BX.MessengerCommon.isPage())
			{
				this.desktop.initHeight = BX.MessengerWindow.initHeight;
			}
			BX.addCustomEvent('onImInit', BX.delegate(function(){
				var textareaSize = this.BXIM.getLocalConfig('global_tas', this.BXIM.context == 'POPUP-FULLSCREEN'? 60: this.popupMessengerTextareaSize);
				this.setTextareaSize(textareaSize);
			}, this));
		}
		if (BX.MessengerCommon.isPage())
		{
			this.desktop.adjustSize()
			BX.MessengerCommon.redrawDateMarks();
			BX.bind(window, "resize", BX.delegate(function(){
				this.adjustSize()
				BX.MessengerCommon.redrawDateMarks();
			}, this.desktop));
		}

		if (BX.browser.SupportLocalStorage())
		{
			var mcr = BX.localStorage.get('mcr2');
			if (mcr)
			{
				for (var i in mcr.users)
					this.users[i] = mcr.users[i];

				for (var i in mcr.hrphoto)
					this.hrphoto[i] = mcr.hrphoto[i];

				for (var i in mcr.chat)
					this.chat[i] = mcr.chat[i];

				for (var i in mcr.userInChat)
					this.userInChat[i] = mcr.userInChat[i];

				this.callInit = true;
				setTimeout(BX.delegate(function(){
					this.webrtc.callNotifyWait(mcr.callChatId, mcr.callUserId, mcr.callVideo, mcr.callToGroup);
				}, this), 500);
			}
			BX.addCustomEvent(window, "onLocalStorageSet", BX.delegate(this.storageSet, this));
			this.textareaHistory = BX.localStorage.get('mtah') || {};
			this.mentionList = BX.localStorage.get('mtam') || {};
			this.currentTab = this.currentTab || BX.localStorage.get('mct');
			this.currentTab = this.currentTab? this.currentTab: 0;

			this.messageTmpIndex = BX.localStorage.get('mti') || 0;
			var mfm = BX.localStorage.get('mfm');
			if (mfm)
			{
				for (var i in this.flashMessage)
					for (var j in this.flashMessage[i])
						if (mfm[i] && this.flashMessage[i][j] != mfm[i][j] && mfm[i][j] == false)
							this.flashMessage[i][j] = false;
			}

			BX.garbage(function(){
				BX.localStorage.set('mti', this.messageTmpIndex, 15);
				BX.localStorage.set('mtah', this.textareaHistory, 15);
				BX.localStorage.set('mtam', this.mentionList, 15);
				BX.localStorage.set('mct', this.currentTab, 15);
				BX.localStorage.set('mfm', this.flashMessage, 15);
				BX.localStorage.set('mcls', this.contactListSearchText+'', 15);

				if (BX.MessengerCommon.isDesktop() && (window.innerWidth < BX.desktop.minWidth || window.innerHeight < BX.desktop.minHeight))
					return false;

				this.BXIM.setLocalConfig('global_msz_v2', {
					'wz': this.popupMessengerFullWidth,
					'ta2': this.popupMessengerTextareaSize,
					'b': this.popupMessengerBodySize,
					'cl': this.popupContactListSize,
					'hi': this.popupHistoryItemsSize,
					'fz': this.popupMessengerFullHeight,
					'ez': this.popupContactListElementsSize,
					'nz': this.notify.popupNotifySize,
					'hf': this.popupHistoryFilterVisible,
					'dw': window.innerWidth,
					'dh': window.innerHeight,
					'place': 'garbage'
				});

			}, this);
		}
		else
		{
			var mtah = this.BXIM.getLocalConfig('mtah', false);
			if (mtah)
			{
				this.textareaHistory = mtah;
				this.BXIM.removeLocalConfig('mtah');
			}
			var mtam = this.BXIM.getLocalConfig('mtam', false);
			if (mtam)
			{
				this.textareaHistory = mtam;
				this.BXIM.removeLocalConfig('mtam');
			}
			var mct = this.BXIM.getLocalConfig('mct', false);
			if (mct)
			{
				this.currentTab = mct;
				this.BXIM.removeLocalConfig('mct');
			}

			BX.garbage(function(){
				this.BXIM.setLocalConfig('mct', this.currentTab);
				this.BXIM.setLocalConfig('mtah', this.textareaHistory);
				this.BXIM.setLocalConfig('mtam', this.mentionList);

				if (BX.MessengerCommon.isDesktop() && (window.innerWidth < BX.desktop.minWidth || window.innerHeight < BX.desktop.minHeight))
					return false;

				this.BXIM.setLocalConfig('global_msz_v2', {
					'wz': this.popupMessengerFullWidth,
					'ta2': this.popupMessengerTextareaSize,
					'b': this.popupMessengerBodySize,
					'cl': this.popupContactListSize,
					'hi': this.popupHistoryItemsSize,
					'fz': this.popupMessengerFullHeight,
					'ez': this.popupContactListElementsSize,
					'nz': this.notify.popupNotifySize,
					'hf': this.popupHistoryFilterVisible,
					'dw': window.innerWidth,
					'dh': window.innerHeight,
					'place': 'garbage'
				});
			}, this);
		}
		BX.MessengerCommon.pullEvent();

		BX.addCustomEvent("onPullError", BX.delegate(function(error) {
			if (error == 'AUTHORIZE_ERROR')
				this.sendAjaxTry++;
		}, this));

		var i = 0;
		var today = BX.date.format('d-m');
		for(var userId in this.users)
		{
			if (this.users[userId].birthday == today && userId != this.BXIM.userId)
			{
				this.message[userId+'birthday'] = {'id' : userId+'birthday', 'senderId' : 0, 'recipientId' : userId, 'date' : BX.MessengerCommon.getNowDate(true), 'text' : BX.message('IM_M_BIRTHDAY_MESSAGE').replace('#USER_NAME#', '<img src="/bitrix/js/im/images/blank.gif" class="bx-messenger-birthday-icon"><strong>'+this.users[userId].name+'</strong>') };
				if (!this.showMessage[userId])
					this.showMessage[userId] = [];
				this.showMessage[userId].push(userId+'birthday');
				this.showMessage[userId].sort(BX.delegate(function(i, ii) {if (!this.message[i] || !this.message[ii]){return 0;} var i1 = this.message[i].date.getTime(); var i2 = this.message[ii].date.getTime(); if (i1 < i2) { return -1; } else if (i1 > i2) { return 1;} else{ if (i < ii) { return -1; } else if (i > ii) { return 1;}else{ return 0;}}}, this));

				var messageLastId = this.showMessage[userId][this.showMessage[userId].length-1];
				BX.MessengerCommon.recentListAdd({
					'userId': userId,
					'userIsChat': false,
					'id': this.message[messageLastId].id,
					'date': this.message[messageLastId].date,
					'recipientId': this.message[messageLastId].recipientId,
					'senderId': this.message[messageLastId].senderId,
					'text': messageLastId == userId+'birthday'? BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT').replace('#USER_NAME#', this.users[userId].name): this.message[messageLastId].text,
					'params': {}
				}, true);
				this.recent.sort(BX.delegate(function(i, ii) {if (!this.message[i.id] || !this.message[ii.id]){return 0;} var i1 = this.message[i.id].date.getTime(); var i2 = this.message[ii.id].date.getTime(); if (i1 > i2) { return -1; } else if (i1 < i2) { return 1;} else{ if (i > ii) { return -1; } else if (i < ii) { return 1;}else{ return 0;}}}, this));

				var birthdayList = this.BXIM.getLocalConfig('birthdayPopup'+((new Date).getFullYear()), {});
				if (this.desktop.birthdayStatus() && !birthdayList[userId])
				{
					this.message[userId+'birthdayPopup'] = {'id' : userId+'birthdayPopup', 'senderId' : 0, 'recipientId' : userId, 'date' : BX.MessengerCommon.getNowDate(true), 'text' : BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT').replace('#USER_NAME#', this.users[userId].name) };
					if (BX.MessengerCommon.isDesktop())
					{
						if (!this.unreadMessage[userId])
							this.unreadMessage[userId] = [];
						this.unreadMessage[userId].push(userId+'birthdayPopup');

						if (!this.flashMessage[userId])
							this.flashMessage[userId] = {};
						this.flashMessage[userId][userId+'birthdayPopup'] = true;
					}
					birthdayList[userId] = true;
					this.BXIM.removeLocalConfig('birthdayPopup'+((new Date).getFullYear()-1));
					this.BXIM.setLocalConfig('birthdayPopup'+((new Date).getFullYear()), birthdayList);
				}
			}
			i++;
		}

		this.updateState();
		if (params.openMessenger !== false)
			this.openMessenger(params.openMessenger);
		else if (this.openMessengerFlag)
			this.openMessenger(this.currentTab);

		if (params.openHistory !== false)
		{
			this.BXIM.openHistory(params.openHistory);
		}
		if (params.openNotify !== false)
			this.BXIM.openNotify();

		if (this.BXIM.settings.status != 'dnd')
			this.newMessage();

		this.updateMessageCount();

		setInterval(BX.delegate(function(){
			BX.MessengerCommon.checkProgessMessage();
			this.expireFrameToken();
		}, this), 1000);

		BX.bind(window, 'message', BX.delegate(function(event){
			if(event && event.origin == this.openFrameDialogFrameSourceDomain)
			{
				this.openFrameDialogPostMessage(event.data);
			}
		}, this));
	}
	else
	{
		if (params.openMessenger !== false)
			this.BXIM.openMessenger(params.openMessenger);
		if (params.openHistory !== false)
			this.BXIM.openHistory(params.openHistory);
	}
};

BX.Messenger.prototype.openMessengerSlider = function(dialogId, params)
{
	params = params || {};

	requestParams = {};
	requestParams.IFRAME = 'Y';
	requestParams.IM_DIALOG = dialogId;
	requestParams.IM_RECENT = params.RECENT == 'N'? 'N': 'Y';
	requestParams.IM_MENU = params.MENU == 'N'? 'N': 'Y';

	var options = {
		cacheable: false,
		allowChangeHistory: false,
		requestMethod: "post",
		requestParams: requestParams,
	};

	if (params.RECENT == 'N' || params.MENU == 'N')
	{
		options.width = 800 + (params.RECENT == 'N'? 0: 50) + (params.MENU == 'N'? 0: 20);
	}

	BX.SidePanel.Instance.open("/desktop_app/", options);
}

BX.Messenger.prototype.openMessenger = function(userId, params)
{
	if (BX.MessengerCommon.isPage() && this.BXIM.context == 'POPUP-FULLSCREEN' && !BX.MessengerWindow.isPopupShow())
	{
		BX.MessengerWindow.showPopup(userId);
		return false;
	}

	if (this.BXIM.errorMessage != '')
	{
		this.BXIM.openConfirm(this.BXIM.errorMessage, [new BX.PopupWindowButton({
			text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
			className : "popup-window-button-decline",
			events : { click : BX.delegate(function(e) { BX.proxy_context.popupWindow.close(); if (BX.MessengerWindow){ BX.MessengerWindow.closePopup() } BX.PreventDefault(e) }, this) }
		})]);
		return false;
	}
	if (this.BXIM.popupSettings != null && !BX.MessengerCommon.isDesktop())
		this.BXIM.popupSettings.close();

	if (this.popupMessenger != null && this.dialogOpen && this.currentTab == userId && userId != 0)
		return false;

	if (userId !== false && BX.MessengerCommon.isPage() && BX.MessengerWindow.currentTab != 'im' && BX.MessengerWindow.currentTab != 'im-ol')
	{
		BX.MessengerWindow.changeTab('im', false, true);
	}

	if (this.popupMessengerEditForm)
		this.editMessageCancel();

	if (userId && userId.toString().toLowerCase() == 'general')
	{
		this.currentTab = 'chat'+this.generalChatId;
		userId = this.currentTab;
	}

	BX.localStorage.set('mcam', true, 5);
	if (typeof(userId) == "undefined" || userId == null)
	{
		userId = 0;
	}

	if (this.currentTab == null)
		this.currentTab = 0;

	this.openChatFlag = false;
	this.openNetworkFlag = false;
	this.openBotFlag = false;
	this.openLinesFlag = false;
	this.openCallFlag = false;

	if (typeof(userId) == "boolean")
	{
		userId = 0;
	}
	else if (userId == 0)
	{
		for (var i in this.unreadMessage)
		{
			userId = i;
			break;
		}
		if (userId == 0 && this.currentTab != null)
		{
			if (this.users[this.currentTab] && this.users[this.currentTab].id)
				userId = this.currentTab;
			else if (this.chat[this.getChatId()] && this.chat[this.getChatId()].id)
				userId = this.currentTab;
		}
		if (userId.toString().substr(0,4) == 'chat')
		{
			BX.MessengerCommon.getUserParam(userId);
			this.openChatFlag = true;
			if (this.chat[userId.toString().substr(4)].type == 'call')
				this.openCallFlag = true;
			else if (this.chat[userId.toString().substr(4)].type == 'lines')
				this.openLinesFlag = true;
		}
		else
		{
			userId = parseInt(userId);
		}
	}
	else if (userId.toString().substr(0,4) == 'chat' || userId.toString().substr(0,2) == 'sg')
	{
		BX.MessengerCommon.getUserParam(userId);
		this.openChatFlag = true;

		if (userId.toString().substr(0,4) == 'chat')
		{
			if (this.chat[userId.toString().substr(4)].type == 'call')
				this.openCallFlag = true;
			else if (this.chat[userId.toString().substr(4)].type == 'lines')
				this.openLinesFlag = true;
		}
	}
	else if (userId.toString().substr(0,7) == 'network')
	{
		BX.MessengerCommon.getUserParam(userId);
		this.openNetworkFlag = true;
	}
	else if (this.users[userId] && this.users[userId].id)
	{
		userId = parseInt(userId);
	}
	else
	{
		userId = parseInt(userId);
		if (isNaN(userId))
		{
			userId = 0;
		}
		else
		{
			BX.MessengerCommon.getUserParam(userId);
		}
	}
	if (this.openNetworkFlag)
	{}
	else if (!this.openChatFlag && typeof(userId) != 'number')
	{
		userId = 0;
	}
	if (this.openChatFlag || userId > 0)
	{
		this.currentTab = userId;
		this.BXIM.notifyManager.closeByTag('im-message-'+userId);
		BX.localStorage.set('mct', this.currentTab, 15);

		if (!this.openChatFlag && this.users[userId] && this.users[userId].bot)
		{
			this.openBotFlag = true;
		}
	}

	if (this.popupMessenger != null)
	{
		BX.MessengerCommon.openDialog(userId, this.BXIM.dialogOpen? false: true);
		if (!(BX.browser.IsAndroid() || BX.browser.IsIOS() || window != window.top))
		{
			if (this.popupMessengerTextarea)
				this.popupMessengerTextarea.focus();
		}
		return false;
	}

	// TODO remove this
	var styleOfContent = {};
	if (!BX.browser.IsMobile() && BX.MessengerCommon.isPage())
	{
		var newHeight = BX.MessengerWindow.content.offsetHeight - this.popupMessengerFullHeight;
		this.popupContactListElementsSize = this.popupContactListElementsSize + newHeight;
		this.popupMessengerBodySize = this.popupMessengerBodySize + newHeight;
		this.popupMessengerFullHeight = this.popupMessengerFullHeight + newHeight;
		this.notify.popupNotifySize = this.notify.popupNotifySize + newHeight;
	}
	else
	{
		styleOfContent = {width: this.popupMessengerFullWidth+'px'};
	}

	if (BX.MessengerWindow && BX.MessengerWindow.contentMenu)
	{
		if (this.BXIM.options.showMenu)
		{
			BX.removeClass(BX.MessengerWindow.contentBox, 'bx-desktop-appearance-hide-menu');
		}
		else
		{
			BX.addClass(BX.MessengerWindow.contentBox, 'bx-desktop-appearance-hide-menu');
		}
	}

	var userStatus = BX.MessengerCommon.getUserStatus(this.users[this.BXIM.userId]);

	this.popupMessengerContent = BX.create("div", { props : { className : "bx-messenger-box bx-messenger-mark bx-messenger-global-context-"+this.BXIM.context.toLowerCase()+" "+(this.webrtc.callInit? ' bx-messenger-call'+(this.callOverlayMinimize? '': ' bx-messenger-call-maxi'): '')+(BX.MessengerCommon.isPage()? ' bx-messenger-box-desktop': '')+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll')+(this.BXIM.options.showRecent? '': ' bx-messenger-hide-recent') }, style: styleOfContent, children : [
		/* CL */
		this.popupContactListWrap = BX.create("div", { props : { className : "bx-messenger-box-contact bx-messenger-box-contact-normal" }, style : {width: this.popupContactListSize+'px'},  children : [
			BX.create("div", { props : { className : "bx-messenger-cl-search" }, children : [
				this.popupContactListCreateChat = BX.create("span", {props : { className : "bx-messenger-input-search-create" }}),
				BX.create("div", { props : { className : "bx-messenger-input-wrap bx-messenger-cl-search-wrap" }, children : [
					this.popupContactListSearchClose = BX.create("a", {attrs: {href: "#close"}, props : { className : "bx-messenger-input-close" }}),
					this.popupContactListSearchInput = BX.create("input", {attrs: {type: "text", placeholder: BX.message('IM_M_SEARCH'), value: this.contactListSearchText}, props : { className : "bx-messenger-input" }})
				]})
			]}),
			this.popupContactListElements = BX.create("div", { props : { className : "bx-messenger-cl" }, style : {height: this.popupContactListElementsSize+'px'}, children : [
				this.popupContactListElementsWrap = BX.create("div", { props : { className : "bx-messenger-cl-wrap bx-messenger-recent-wrap" }})
			]}),
			this.BXIM.design == 'DESKTOP'? null: BX.create('div', {props : { className : "bx-messenger-cl-notify-wrap" }, children : [
				this.notify.messengerNotifyButton = BX.create("div", { props : { className : "bx-messenger-cl-notify-button"}, events : { click : BX.delegate(this.notify.openNotify, this.notify)}, children : [
					BX.create('span', {props : { className : "bx-messenger-cl-notify-text"}, html: BX.message('IM_NOTIFY_BUTTON_TITLE')}),
					this.notify.messengerNotifyButtonCount = BX.create('span', { props : { className : "bx-messenger-cl-count" }, html: parseInt(this.notify.notifyCount)>0? '<span class="bx-messenger-cl-count-digit">'+this.notify.notifyCount+'</span>':''})
				]}),
				this.popupContactListSearchCall = !this.webrtc.phoneSupport() || !this.webrtc.phoneCanPerformCalls? null: BX.create("div", { props : { className : "bx-messenger-cl-phone-button"}, children : [
					BX.create('span', {props : { className : "bx-messenger-cl-phone-text"}, html: BX.message('IM_PHONE_BUTTON_TITLE')}),
				]})
			]}),
			BX.create('div', {props : { className : "bx-messenger-cl-panel" }, children : [
				BX.create('div', {props : { className : "bx-messenger-cl-panel-wrap" }, children : [
					this.contactListPanelStatus = BX.create("span", { props : { className : "bx-messenger-cl-panel-status-wrap bx-messenger-cl-panel-status-"+BX.MessengerCommon.getUserStatus(this.users[this.BXIM.userId]) }, html: '<span class="bx-messenger-cl-panel-status"></span><span class="bx-messenger-cl-panel-status-text">'+BX.message("IM_STATUS_"+(userStatus == 'birthday'? 'online': userStatus ).toUpperCase())+'</span><span class="bx-messenger-cl-panel-status-arrow"></span>'}),
					BX.create('span', {props : { className : "bx-messenger-cl-panel-right-wrap" }, children : [
						//this.contactListPanelFull = BX.MessengerCommon.isPage()? null: BX.create("span", { props : { title : BX.message("IM_FULLSCREEN"), className : "bx-messenger-cl-panel-fullscreen-wrap"}}),
						this.contactListPanelSettings = this.BXIM.design == 'DESKTOP'? null: BX.create("span", { props : { title : BX.message("IM_SETTINGS"), className : "bx-messenger-cl-panel-settings-wrap"}})
					]})
				]})
			]})
		]}),
		/* DIALOG */
		this.popupMessengerDialog = BX.create("div", { props : { className : "bx-messenger-box-dialog"+(this.BXIM.isAdmin? ' bx-messenger-user-admin': '') }, style : {marginLeft: this.popupContactListSize+'px'},  children : [
			this.popupMessengerPanel = BX.create("div", { props : { className : "bx-messenger-panel bx-messenger-context-user "+(this.openChatFlag? ' bx-messenger-hide': '') }, children : [
				BX.create('a', { attrs : { href : this.users[this.currentTab]? this.users[this.currentTab].profile: BX.MessengerCommon.getUserParam().profile}, props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-status-"+BX.MessengerCommon.getUserStatus(this.users[this.currentTab]) }, children: [
					this.popupMessengerPanelAvatar = BX.create('img', { attrs : { src : this.BXIM.pathToBlankImage }, props : { className : "bx-messenger-panel-avatar-img bx-messenger-panel-avatar-img-default" }}),
					BX.create('span', {  props : { className : "bx-messenger-panel-avatar-status" }})
				], events : {
					mouseover: BX.delegate(function(e){
						if (this.users[this.currentTab])
						{
							BX.proxy_context.title = BX.MessengerCommon.getUserStatus(this.users[this.currentTab], true);
						}
					}, this)
				}}),
				BX.create("a", {attrs: {href: "#history", title: BX.message("IM_M_OPEN_HISTORY_2")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-history"}, events : { click: BX.delegate(function(e){ this.openHistory(this.currentTab); BX.PreventDefault(e)}, this)}}),
				this.popupMessengerPanelMute = BX.create("a", {attrs: {href: "#block", title: this.muteButtonStatus(this.currentTab)? BX.message("IM_M_USER_BLOCK_ON"): BX.message("IM_M_USER_BLOCK_OFF")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-mute"}, events : { click: BX.delegate(function(e){BX.MessengerCommon.muteMessageChat(this.currentTab);BX.PreventDefault(e);}, this)}}),
				this.enableGroupChat? BX.create("a", {attrs: {href: "#chat", title: BX.message("IM_M_CHAT_TITLE")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-chat"}, html: BX.message("IM_M_CHAT_BTN_JOIN"), events : { click: BX.delegate(function(e){ this.openChatDialog({'type': 'CHAT_ADD', 'bind': BX.proxy_context}); BX.PreventDefault(e)}, this)}}): null,
				this.popupMessengerPanelButtonCall1 = this.callButton(),
				BX.create("span", { props : { className : "bx-messenger-panel-title"}, children: [
					this.popupMessengerPanelTitle = BX.create('a', { props : { className : "bx-messenger-panel-title-link"+(this.users[this.currentTab] && this.users[this.currentTab].extranet? " bx-messenger-user-extranet": (this.users[this.currentTab] && this.users[this.currentTab].bot? (this.bot[this.currentTab] && this.bot[this.currentTab].type == 'network'? " bx-messenger-user-network": " bx-messenger-user-bot"): ""))}, attrs : { href : this.users[this.currentTab]? this.users[this.currentTab].profile: BX.MessengerCommon.getUserParam().profile}, html: this.users[this.currentTab]? this.users[this.currentTab].name: ''}),
					this.popupMessengerPanelLastDate = BX.create("span", { props : { className : "bx-messenger-panel-title-position"}, html: ''})
				]}),
				this.popupMessengerPanelStatus = BX.create("span", { props : { className : "bx-messenger-panel-desc"}, html: BX.MessengerCommon.getUserPosition(this.users[this.currentTab], false, true)})
			]}),
			this.popupMessengerPanelChat = BX.create("div", { props : { className : "bx-messenger-panel bx-messenger-context-chat "+(this.openChatFlag && !this.openCallFlag? '': ' bx-messenger-hide') }, children : [
				this.popupMessengerPanelAvatarForm2 = BX.create('form', { attrs : { action : this.BXIM.pathToFileAjax}, props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-chat" }, children: [
					BX.create('div', { props : { className : "bx-messenger-panel-avatar-progress"}, html: '<div class="bx-messenger-panel-avatar-progress-image"></div>'}),
					BX.create('input', { attrs : { type : 'hidden', name: 'IM_AVATAR_UPDATE', value: 'Y'}}),
					this.popupMessengerPanelAvatarId2 = BX.create('input', { attrs : { type : 'hidden', name: 'CHAT_ID', value: this.getChatId()}}),
					BX.create('input', { attrs : { type : 'hidden', name: 'IM_AJAX_CALL', value: 'Y'}}),
					this.popupMessengerPanelAvatarUpload2 = this.disk.lightVersion || !this.BXIM.ppServerStatus? null: BX.create('input', { attrs : { type : 'file', title: BX.message('IM_M_AVATAR_UPLOAD')}, props : { className : "bx-messenger-panel-avatar-upload"}}),
					this.popupMessengerPanelAvatar2 = BX.create('img', { attrs : { src : this.BXIM.pathToBlankImage}, props : { className : "bx-messenger-panel-avatar-img bx-messenger-panel-avatar-img-default" }}),
					this.popupMessengerPanelCrm = BX.create('span', {  props : { className : "bx-messenger-panel-avatar-crm" }}),
					this.popupMessengerPanelStatus2 = BX.create('span', {  props : { className : "bx-messenger-panel-avatar-status" }})
					/*this.popupMessengerPanelLoader = BX.create('span', {  props : { className : "bx-messenger-loader" }, children: [
						BX.create('span', {  props : { className : "bx-messenger-loader-default bx-messenger-loader-first" }}),
						BX.create('span', {  props : { className : "bx-messenger-loader-default bx-messenger-loader-second" }}),
						BX.create('span', {  props : { className : "bx-messenger-loader-mask" }})
					]})*/
				]}),
				BX.create("span", {attrs: {title: BX.message('IM_P_MENU')}, props : { className : "bx-messenger-panel-button bx-messenger-panel-menu"}, events : { click: BX.delegate(function(e){ this.openPopupMenu(BX.proxy_context, this.chat[this.getChatId()].entity_type == "LINES"? 'openLinesMenu': 'pathMenu'); BX.PreventDefault(e); }, this)}}),
				BX.create("a", {attrs: {href: "#history", title: BX.message("IM_M_OPEN_HISTORY_2")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-history"}, events : { click: BX.delegate(function(e){ this.openHistory(this.currentTab); BX.PreventDefault(e)}, this)}}),
				this.popupMessengerPanelMute2 = BX.create("a", {attrs: {href: "#mute", title: this.muteButtonStatus(this.currentTab)? BX.message("IM_M_CHAT_MUTE_ON_2"): BX.message("IM_M_CHAT_MUTE_OFF_2")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-mute "+(this.muteButtonStatus(this.currentTab)? ' bx-messenger-panel-unmute': '')}, events : { click: BX.delegate(function(e){BX.MessengerCommon.muteMessageChat(this.currentTab);BX.PreventDefault(e);}, this)}}),
				this.popupOpenLinesSpam = BX.create("span", {attrs: {title: BX.message('IM_M_OL_FORCE_CLOSE')? BX.message('IM_M_OL_FORCE_CLOSE').replace('<br>', ''): BX.message('IM_M_OL_SPAM')}, props : { className : "bx-messenger-panel-button bx-messenger-panel-spam"}, events : { click: BX.delegate(function(e){ this.linesMarkAsSpam(); BX.PreventDefault(e); }, this)}}),
				this.popupOpenLinesClose = BX.create("span", {attrs: {title: BX.message('IM_M_OL_CLOSE')}, props : { className : "bx-messenger-panel-button bx-messenger-panel-close"}, events : { click: BX.delegate(function(e){ this.linesCloseDialog(); BX.PreventDefault(e); }, this)}}),
				this.popupOpenLinesTransfer = BX.create("span", {attrs: {title: BX.message('IM_P_TRANSFER')}, props : { className : "bx-messenger-panel-button bx-messenger-panel-transfer"}, events : { click: BX.delegate(function(e){ this.linesOpenTransferDialog({'bind': BX.proxy_context}); BX.PreventDefault(e); }, this)}}),
				this.popupMessengerPanelButtonExtend = this.enableGroupChat? BX.create("a", {attrs: {href: "#chat", title: BX.message("IM_M_CHAT_TITLE")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-chat"}, html: BX.message("IM_M_CHAT_BTN_JOIN"), events : { click: BX.delegate(function(e){ this.openChatDialog({'chatId': this.getChatId(),'type': 'CHAT_EXTEND', 'bind': BX.proxy_context}); BX.PreventDefault(e)}, this)}}): null,
				this.popupMessengerPanelButtonCall2 = this.callButton(),
				BX.create("span", { props : { className : "bx-messenger-panel-title bx-messenger-panel-title-chat"}, children: [
					this.popupMessengerPanelChatTitle = BX.create('span', { props : { className : ""}, html: this.chat[this.getChatId()]? this.chat[this.getChatId()].name: BX.message('IM_CL_LOAD')})
				]}),
				BX.create("span", { props : { className : "bx-messenger-panel-desc"}, children : [
					this.popupMessengerPanelUsers = BX.create('div', { props : { className : "bx-messenger-panel-chat-users"}, html: BX.message('IM_CL_LOAD')})
				]})
			]}),
			this.popupMessengerPanelCall = BX.create("div", { props : { className : "bx-messenger-panel bx-messenger-context-call "+(this.openChatFlag && this.openCallFlag? '': ' bx-messenger-hide') }, children : [
				this.popupMessengerPanelAvatarForm3 = BX.create('form', { attrs : { action : this.BXIM.pathToFileAjax}, props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-call" }, children: [
					BX.create('div', { props : { className : "bx-messenger-panel-avatar-progress"}, html: '<div class="bx-messenger-panel-avatar-progress-image"></div>'}),
					BX.create('input', { attrs : { type : 'hidden', name: 'IM_AVATAR_UPDATE', value: 'Y'}}),
					this.popupMessengerPanelAvatarId3 = BX.create('input', { attrs : { type : 'hidden', name: 'CHAT_ID', value: this.getChatId()}}),
					BX.create('input', { attrs : { type : 'hidden', name: 'IM_AJAX_CALL', value: 'Y'}}),
					this.popupMessengerPanelAvatarUpload3 = this.disk.lightVersion || !this.BXIM.ppServerStatus? null: BX.create('input', { attrs : { type : 'file', title: BX.message('IM_M_AVATAR_UPLOAD_2')}, props : { className : "bx-messenger-panel-avatar-upload"}}),
					this.popupMessengerPanelAvatar3 = BX.create('img', { attrs : { src : this.BXIM.pathToBlankImage}, props : { className : "bx-messenger-panel-avatar-img bx-messenger-panel-avatar-img-default" }}),
					this.popupMessengerPanelStatus3 = BX.create('span', {  props : { className : "bx-messenger-panel-avatar-status bx-messenger-panel-avatar-status-chat" }})
				]}),
				BX.create("span", {attrs: {title: BX.message('IM_P_MENU')}, props : { className : "bx-messenger-panel-button bx-messenger-panel-menu"}, events : { click: BX.delegate(function(e){ this.openPopupMenu(BX.proxy_context, 'callContextMenu'); BX.PreventDefault(e); }, this)}}),
				BX.create("a", {attrs: {href: "#history", title: BX.message("IM_M_OPEN_HISTORY_2")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-history"}, events : { click: BX.delegate(function(e){ this.openHistory(this.currentTab); BX.PreventDefault(e)}, this)}}),
				this.popupMessengerPanelMute3 = BX.create("a", {attrs: {href: "#mute", title: this.muteButtonStatus(this.currentTab)? BX.message("IM_M_CHAT_MUTE_ON_2"): BX.message("IM_M_CHAT_MUTE_OFF_2")}, props : { className : "bx-messenger-panel-button bx-messenger-panel-mute "+(this.muteButtonStatus(this.currentTab)? ' bx-messenger-panel-unmute': '')}, events : { click: BX.delegate(function(e){ BX.MessengerCommon.muteMessageChat(this.currentTab); BX.PreventDefault(e)}, this)}}),
				this.popupMessengerPanelButtonCall3 = this.callButton('call'),
				this.popupMessengerPanelCallTitle = BX.create("span", { props : { className : "bx-messenger-panel-title"}, html: this.chat[this.getChatId()]? this.chat[this.getChatId()].name: BX.message('IM_CL_LOAD')}),
				this.popupMessengerPanelCallDescription = BX.create("span", { props : { className : "bx-messenger-panel-desc"}, text: this.chat[this.getChatId()] && this.chat[this.getChatId()].entity_data_1 && this.chat[this.getChatId()].entity_data_1.toString().charAt(0) === "Y" ? this.chat[this.getChatId()].call_number : BX.message('IM_PHONE_DESC')})
			]}),
			this.popupMessengerConnectionStatus = BX.create("div", { props : { className : "bx-messenger-connection-status "+(this.popupMessengerConnectionStatusState == 'online'? "bx-messenger-connection-status-hide": "bx-messenger-connection-status-show bx-messenger-connection-status-"+this.popupMessengerConnectionStatusState) }, children : [
				BX.create("div", { props : { className : "bx-messenger-connection-status-wrap" }, children : [
					this.popupMessengerConnectionStatusText = BX.create("span", { props : { className : "bx-messenger-connection-status-text"}, html: this.popupMessengerConnectionStatusStateText}),
					BX.create("span", { props : { className : "bx-messenger-connection-status-text-reload"}, children : [
						BX.create("span", { props : { className : "bx-messenger-connection-status-text-reload-title"}, html: BX.message('IM_CS_RELOAD')}),
						BX.create("span", { props : { className : "bx-messenger-connection-status-text-reload-hotkey"}, html: (BX.browser.IsMac()? "&#8984;+R": "Ctrl+R")})
					], events: {
						'click': function(){ location.reload() }
					}})
				]})
			]}),
			this.popupMessengerEditForm = BX.create("div", { props : { className : "bx-messenger-editform bx-messenger-editform-disable" }, children : [
				BX.create("div", { props : { className : "bx-messenger-editform-wrap" }, children : [
					BX.create("div", { props : { className : "bx-messenger-editform-textarea" }, children : [
						this.popupMessengerEditTextarea = BX.create("textarea", { props : { value: '', className : "bx-messenger-editform-textarea-input" }, style : {height: '70px'}})
					]}),
					BX.create("div", { props : { className : "bx-messenger-editform-buttons" }, children : [
						BX.create("span", { props : { className : "popup-window-button popup-window-button-accept" }, children : [
							BX.create("span", { props : { className : "popup-window-button-left"}}),
							BX.create("span", { props : { className : "popup-window-button-text"}, html: BX.message('IM_M_CHAT_BTN_EDIT')}),
							BX.create("span", { props : { className : "popup-window-button-right"}})
						], events : {
							click: BX.delegate(function(e){
								var editedMessageId = this.popupMessengerEditMessageId;
								BX.MessengerCommon.editMessageAjax(this.popupMessengerEditMessageId, this.popupMessengerEditTextarea.value);
								if(this.message[editedMessageId].quick_saved)
								{
									BX.MessengerCommon.linesSaveToQuickAnswers(editedMessageId, true);
								}
							}, this)
						}}),
						BX.create("span", { props : { className : "popup-window-button" }, children : [
							BX.create("span", { props : { className : "popup-window-button-left"}}),
							BX.create("span", { props : { className : "popup-window-button-text"}, html: BX.message('IM_M_CHAT_BTN_CANCEL')}),
							BX.create("span", { props : { className : "popup-window-button-right"}})
						], events : {
							click: BX.delegate(function(e){
								this.editMessageCancel();
							}, this)
						}}),
						BX.create("span", { props : { className : "bx-messenger-editform-progress"}, html: BX.message('IM_MESSAGE_EDIT_TEXT') })
					]})
				]})
			]}),
			this.popupMessengerBodyDialog = BX.create("div", { props : { className : "bx-messenger-body-dialog bxu-file-input-over" }, children: [
				this.popupMessengerFileDropZone = !this.disk.enable? null: BX.create("div", { props : { className : "bx-messenger-file-dropzone" }, children : [
					BX.create("div", { props : { className : "bx-messenger-file-dropzone-wrap" }, children: [
						BX.create("div", { props : { className : "bx-messenger-file-dropzone-icon" }}),
						BX.create("div", { props : { className : "bx-messenger-file-dropzone-text" }, html: BX.message('IM_F_DND_TEXT')}),
					]})
				]}),
				this.popupMessengerBodyPanel = BX.create("div", { props : { className : "bx-messenger-body-panel" }, style : {height: this.popupMessengerBodySize+'px'}, children: [
					BX.create("div", { props : { className : "bx-messenger-body-panel-title" }, children: [
						this.popupMessengerBodyPanelTitleName = BX.create("div", { props : { className : "bx-messenger-body-panel-title-name" }}),
						this.popupMessengerBodyPanelTitleDesc = BX.create("div", { props : { className : "bx-messenger-body-panel-title-desc" }}),
						BX.create("div", { props : { className : "bx-messenger-body-panel-title-close" }, events: {click: BX.delegate(function(){
							this.closeMessengerPanel();
						}, this)}})
					]}),
					this.popupMessengerBodyPanelWrap = BX.create("div", { props : { className : "bx-messenger-body-panel-wrap" }})
				]}),
				this.popupMessengerBody = BX.create("div", { props : { className : "bx-messenger-body" }, style : {height: this.popupMessengerBodySize+'px'}, children: [
					BX.create("div", { props : { className : "bx-messenger-body-bg" }, children: [
						this.popupMessengerBodyWrap = BX.create("div", { props : { className : "bx-messenger-body-wrap" }})
					]}),
				]}),
				this.popupMessengerBodyLiveChatForm = BX.create("div", { props : { className : "bx-messenger-livechat-form" }}),
				this.popupMessengerTextareaPlace = BX.create("div", { props : { className : "bx-messenger-textarea-place"}, children : [
					BX.create("div", { props : { className : "bx-messenger-textarea-open-lines" }, children : [
						BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
							BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
								this.popupMessengerTextareaOpenLinesText = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text" }, html: BX.message('IM_OL_INVITE_TEXT')})
							]})
						]}),
						BX.create("div", { props: { className : "bx-messenger-textarea-open-invite-join-box"}, children: [
							this.popupMessengerTextareaOpenLinesAnswer = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-answer bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_OL_INVITE_ANSWER')}),
							this.popupMessengerTextareaOpenLinesSkip = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-skip bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-cancel" }, html: BX.message('IM_OL_INVITE_SKIP')}),
							this.popupMessengerTextareaOpenLinesTransfer = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-transfer bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-transfer" }, html: BX.message('IM_OL_INVITE_TRANSFER'), events : { click: BX.delegate(function(e){ this.linesOpenTransferDialog({'bind': BX.proxy_context}); BX.PreventDefault(e); }, this)}})
						]})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-open-invite" }, children : [
						BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
							BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
								this.popupMessengerTextareaOpenText = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text" }, html: BX.message(this.BXIM.bitrixIntranet? 'IM_O_INVITE_TEXT': 'IM_O_INVITE_TEXT_SITE')})
							]})
						]}),
						this.popupMessengerTextareaOpenJoin = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-join bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_O_INVITE_JOIN')})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-general-invite" }, children : [
						BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box" }, children: [
							BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text-box-element" }, children: [
								this.popupMessengerTextareaGeneralText = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-text" }})
							]})
						]}),
						this.popupMessengerTextareaGeneralJoin = BX.create("div", { props : { className : "bx-messenger-textarea-open-invite-join bx-notifier-item-button bx-notifier-item-button-confirm bx-notifier-item-button-accept" }, html: BX.message('IM_G_JOIN_'+this.BXIM.userGender)})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-resize" }, events : { mousedown : BX.delegate(this.resizeTextareaStart, this)}}),
					BX.create("div", { props : { className : "bx-messenger-textarea-send" }, children : [
						BX.create("a", {attrs: {href: "#send"}, props : { className : "bx-messenger-textarea-send-button" }, events : { click : BX.delegate(this.sendMessage, this)}}),
						this.popupMessengerTextareaSendType = BX.browser.IsMobile()? BX.create("span"): BX.create("span", {attrs : {title : BX.message('IM_M_SEND_TYPE_TITLE')}, props : { className : "bx-messenger-textarea-cntr-enter"}, html: this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter"), events: {
							click: BX.delegate(function() {
								if (this.popupMessengerTextareaPlace && this.popupMessengerTextareaPlace.className.indexOf('bx-messenger-textarea-with-text') == -1)
								{
									return false;
								}

								this.BXIM.settings.sendByEnter = this.BXIM.settings.sendByEnter? false: true;
								this.BXIM.saveSettings({'sendByEnter': this.BXIM.settings.sendByEnter});
								BX.proxy_context.innerHTML = this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");
							}, this)
						}})
					]}),
					this.popupMessengerTextareaIcons = BX.create("div", {props : { className : "bx-messenger-textarea-icons" }, children: [
						this.popupMessengerFileButton = this.disk.getFileMenuIcon(),
						this.BXIM.context == "LINES"? null: BX.create("div", {attrs : { title: BX.message('IM_MENTION_MENU')},  props : { className : "bx-messenger-textarea-mention" }, events : { click : BX.delegate(function(e){ this.openMentionDialog({delay: 0}); return BX.PreventDefault(e);}, this)}}),
						this.BXIM.context == "LINES"? null: BX.create("div", {attrs : { title: BX.message('IM_COMMAND_MENU')},  props : { className : "bx-messenger-textarea-command" }, events : { click : BX.delegate(function(e){ this.openCommandDialog(); return BX.PreventDefault(e);}, this)}}),
						this.popupMessengerSmileButton = BX.create("div", {attrs : { title: BX.message('IM_SMILE_MENU')},  props : { className : "bx-messenger-textarea-smile" }, events : { click : BX.delegate(function(e){this.openSmileMenu(); return BX.PreventDefault(e);}, this)}}),
						this.BXIM.context == "LINES"? null: BX.create("div", {attrs : { title: BX.message('IM_FORMS_MENU')},  props : { className : "bx-messenger-textarea-forms" }, events : { click : BX.delegate(function(e){this.openFormsMenu(); return BX.PreventDefault(e);}, this)}}),
						this.BXIM.context == "LINES"? null: BX.create("div", {attrs : { title: BX.message('IM_ANSWERS_MENU')},  props : { className : "bx-messenger-textarea-answers" }, events : { click : BX.delegate(function(e){this.openAnswersMenu(); return BX.PreventDefault(e);}, this)}}),
						this.popupMessengerHiddenModeButton = BX.create("div", {attrs : { title: BX.message('IM_HIDDEN_MODE_MENU')},  props : { className : "bx-messenger-textarea-hidden" }, events : { click : BX.delegate(function(e){ this.linesToggleSilentMode(); return BX.PreventDefault(e);}, this)}}),
						this.popupMessengerTextareaIconBox = BX.create("div", { props : { className : "bx-messenger-textarea-icon-box" }})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea" }, children : [
						this.popupMessengerTextarea = BX.create("textarea", { props : { value: (this.textareaHistory[userId]? this.textareaHistory[userId]: ''), className : "bx-messenger-textarea-input"}, style : {height: this.popupMessengerTextareaSize+'px'}}),
						this.popupMessengerTextareaPlaceholder = BX.create("div", { props : {className : "bx-messenger-textarea-placeholder"}, html : BX.message('IM_M_TA_TEXT')})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-clear" }}),
					BX.MessengerCommon.isPage() && !BX.MessengerCommon.isDesktop()? null: BX.create("span", { props : { className : "bx-messenger-resize" }, events : BX.MessengerCommon.isPage()? {}: { mousedown : BX.delegate(this.resizeWindowStart, this)}})
				]})
			]})
		]}),
		/* EXTRA PANEL */
		this.popupMessengerExtra = BX.create("div", { props : { className : "bx-messenger-box-extra"}, style : {marginLeft: this.popupContactListSize+'px', height: this.popupMessengerFullHeight+'px'}})
	]});
	this.textareaCheckText();

	this.BXIM.dialogOpen = true;
	if (BX.MessengerCommon.isPage())
	{
		this.popupMessenger = new BX.PopupWindowDesktop(this.BXIM);
		BX.MessengerWindow.setTabContent('im', this.popupMessengerContent);
		BX.bind(this.popupMessengerContent, 'click', BX.delegate(this.closePopupFileMenu, this));
		this.disk.chatDialogInit();
		this.disk.chatAvatarInit();
	}
	else
	{
		this.popupMessenger = new BX.PopupWindow('bx-messenger-popup-messenger', null, {
			lightShadow : true,
			autoHide: false,
			closeByEsc: true,
			overlay: {opacity: 50, backgroundColor: "#000000"},
			draggable: {restrict: true},
			events : {
				onPopupShow : BX.delegate(function() {
					this.disk.chatDialogInit();
					this.disk.chatAvatarInit();
				}, this),
				onPopupClose : function() { this.destroy(); },
				onPopupDestroy : BX.delegate(function() {
					this.closeMessenger();
				}, this)
			},
			titleBar: {content: BX.create('div')},
			closeIcon : {'top': '10px', 'right': '13px'},
			content : this.popupMessengerContent,
			noAllPaddings : true,
			contentColor : "white"
		});
		this.popupMessenger.show();

		BX.bind(this.popupMessenger.popupContainer, "click", BX.MessengerCommon.preventDefault);
		if (this.webrtc.ready())
		{
			BX.addCustomEvent(this.popupMessenger, "onPopupDragStart", BX.delegate(function(){
				if (this.webrtc.callDialogAllow != null)
					this.webrtc.callDialogAllow.destroy();
			}, this));
		}
		BX.bind(document, "click", BX.proxy(this.BXIM.autoHide, this.BXIM));
		BX.bind(window, "keydown", BX.proxy(this.closePopupFileMenuKeydown, this));

		BX.addCustomEvent(this.popupMessenger, "onPopupFullscreenEnter", BX.delegate(function(){
			BX.addClass(this.popupMessengerContent, 'bx-messenger-fullscreen');

			this.messengerFullscreenStatus = true;
			this.resizeMainWindow();
			if (BX.browser.IsChrome())
			{
				setTimeout(BX.delegate(function(){
					this.resizeMainWindow();
				}, this), 100);
			}

			this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight - this.popupMessengerBody.offsetHeight;

		}, this));

		BX.addCustomEvent(this.popupMessenger, "onPopupFullscreenLeave", BX.delegate(function(){

			BX.removeClass(this.popupMessengerContent, 'bx-messenger-fullscreen');
			if (BX.browser.IsChrome())
			{
				BX.addClass(this.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
				setTimeout(BX.delegate(function(){
					BX.removeClass(this.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
				}, this), 100);
			}
			this.resizeMainWindow();

			this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight - this.popupMessengerBody.offsetHeight;
		}, this));
	}

	this.BXIM.setBackground();

	this.popupMessengerTopLine = BX.create("div", { props : { className : "bx-messenger-box-topline"}});
	this.popupMessengerContent.insertBefore(this.popupMessengerTopLine, this.popupMessengerContent.firstChild);

	clearTimeout(this.popupMessengerTopLineTimeout);
	this.popupMessengerTopLineTimeout = setTimeout(BX.delegate(function(){
		this.BXIM.notifyManager.nativeNotifyAccessForm();
	}, this), 10000);

	if (!BX.MessengerCommon.isDesktop() && this.BXIM.bitrixIntranet && this.BXIM.platformName != '' && this.BXIM.settings.bxdNotify)
	{
		clearTimeout(this.popupMessengerDesktopTimeout);
		this.popupMessengerDesktopTimeout = setTimeout(BX.delegate(function(){
			var acceptButton = BX.delegate(function(){
				window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp");
				this.BXIM.settings.bxdNotify = false;
				this.BXIM.saveSettings({'bxdNotify': this.BXIM.settings.bxdNotify});
				this.hideTopLine();
			}, this);
			var declineButton = BX.delegate(function(){
				this.BXIM.settings.bxdNotify = false;
				this.BXIM.saveSettings({'bxdNotify': this.BXIM.settings.bxdNotify});
				this.hideTopLine();
			}, this);
			this.showTopLine(BX.message('IM_DESKTOP_INSTALL').replace('#WM_NAME#', BX.message('IM_WM')).replace('#OS#', this.BXIM.platformName), [
				{title: BX.message('IM_DESKTOP_INSTALL_Y'), callback: acceptButton},
				{title: BX.message('IM_DESKTOP_INSTALL_N'), callback: declineButton}
			], false);
		}, this), 15000);
	}

	if (this.webrtc.callNotify != null)
	{
		if (this.webrtc.ready())
		{
			this.setClosingByEsc(false);
			BX.addClass(BX('bx-messenger-popup-messenger'), 'bx-messenger-popup-messenger-dont-close');
			BX.removeClass(this.webrtc.callNotify.contentContainer.children[0], 'bx-messenger-call-overlay-float');
			this.popupMessengerContent.insertBefore(this.webrtc.callNotify.contentContainer.children[0], this.popupMessengerContent.firstChild);
			this.webrtc.callNotify.close();

			BX.style(this.webrtc.callOverlay, 'width', (this.popupMessengerExtra.style.display == "block"? this.popupMessengerExtra.offsetWidth+1: this.popupMessengerDialog.offsetWidth+1)+'px');
		}
		else
		{
			this.webrtc.callOverlayClose(false);
		}
	}

	this.textareaIconPrepare();

	BX.MessengerCommon.userListRedraw();
	if (this.BXIM.quirksMode)
	{
		this.popupContactListWrap.style.position = "absolute";
		this.popupContactListWrap.style.display = "block";
	}
	this.setUpdateStateStep();
	if (!(BX.browser.IsAndroid() || BX.browser.IsIOS() || window != window.top) && this.popupMessenger != null)
	{
		setTimeout(BX.delegate(function(){
			this.popupMessengerTextarea.focus();
		}, this), 50);
	}

	/* CL */
	if (this.webrtc.phoneEnabled && this.BXIM.design != 'DESKTOP')
	{
		BX.bind(this.popupContactListSearchCall, "click", BX.delegate(this.webrtc.openKeyPad, this.webrtc));
	}
	BX.bind(this.popupContactListWrap, "mouseover", BX.delegate(function(e) {
		if (this.popupContactListHovered || this.popupContactListActive)
			return false;

		clearTimeout(this.popupContactListWrapAnimation);
		this.popupContactListWrapAnimation = setTimeout(BX.delegate(function(){
			BX.addClass(this.popupContactListWrap, 'bx-messenger-box-contact-hover');
			clearTimeout(this.popupContactListWrapAnimation);
			this.popupContactListWrapAnimation = setTimeout(BX.delegate(function(){
				BX.removeClass(this.popupContactListWrap, 'bx-messenger-box-contact-normal');
			}, this), 100);
		}, this), 2000);

		this.popupContactListHovered = true;
	}, this));

	BX.bind(this.popupContactListWrap, "mouseout", BX.delegate(function(e) {
		if (!this.popupContactListHovered || this.popupContactListActive)
			return false;

		clearTimeout(this.popupContactListWrapAnimation);
		this.popupContactListWrapAnimation = setTimeout(BX.delegate(function(){
			BX.addClass(this.popupContactListWrap, 'bx-messenger-box-contact-normal');
			clearTimeout(this.popupContactListWrapAnimation);
			this.popupContactListWrapAnimation = setTimeout(BX.delegate(function(){
				BX.removeClass(this.popupContactListWrap, 'bx-messenger-box-contact-hover');
			}, this), 50);
		}, this), 400);

		this.popupContactListHovered = false;

	}, this));

	BX.bind(this.popupContactListCreateChat, "click",  BX.delegate(function(e) {
		if (!this.recentList)
		{
			this.recentList = true;
			BX.MessengerCommon.recentListRedraw();
		}
		this.openPopupMenu(e.currentTarget, 'createChat');
		return BX.PreventDefault(e);
	}, this));
	BX.bind(this.popupContactListSearchClose.parentNode, "click",  BX.delegate(function(){
		this.popupContactListSearchInput.focus();
	}, this));
	BX.bind(this.popupMessengerDialog, "click",  BX.delegate(function(e){
		if (this.recentList && !this.chatList && !this.contactList)
		{
			return false;
		}
		BX.MessengerCommon.contactListSearchClear(e);
	}, this));
	BX.bind(this.popupContactListSearchClose, "click",  BX.delegate(function(e){
		BX.MessengerCommon.contactListSearchClear(e);
		return BX.PreventDefault(e);
	}, BX.MessengerCommon));
	/*
	BX.bind(this.popupContactListSearchInput, "click", BX.delegate(function(e) {
		if (this.contactListSearchText.length == 0 && !this.contactList && e.altKey == true)
		{
			clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
			BX.MessengerCommon.contactListPrepareOld();
		}
	}, this));
	*/
	BX.bind(this.popupContactListSearchInput, "focus", BX.delegate(function(e) {
		clearTimeout(this.BXIM.messenger.redrawChatListTimeout);
		this.BXIM.messenger.redrawChatListTimeout = setTimeout(BX.delegate(function(){
			if (this.contactListSearchText.length == 0 && !this.chatList && !this.contactList)
			{
				BX.MessengerCommon.chatListRedraw();
			}
		}, this), 100);
		this.setClosingByEsc(false);
	}, this));
	BX.bind(this.popupContactListSearchInput, "blur", BX.delegate(function(){
		if (this.contactListSearchText.length == 0 && !this.popupContactListHovered && !this.recentList)
		{
			this.setClosingByEsc(true);
		}
	}, this));
	if (BX.MessengerCommon.isDesktop())
	{
		BX.bind(this.popupContactListSearchInput, "contextmenu", BX.delegate(function(e) {
			this.openPopupMenu(e, 'copypaste', false, {'spell': true});
			return BX.PreventDefault(e);
		}, this));
	}
	BX.bind(this.popupContactListSearchInput, "keyup", BX.delegate(BX.MessengerCommon.contactListSearch, BX.MessengerCommon));

	BX.bind(this.popupMessengerPanelChatTitle, "click",  BX.delegate(this.renameChatDialog, this));

	BX.bindDelegate(this.popupMessengerPanelUsers, "click", {className: 'bx-messenger-panel-chat-user'}, BX.delegate(function(e){this.openPopupMenu(BX.proxy_context, 'chatUser'); return BX.PreventDefault(e);}, this));

	BX.bindDelegate(this.popupMessengerPanelUsers, "click", {className: 'bx-notifier-popup-user-more'}, BX.delegate(function(e) {
		if (this.popupChatUsers != null)
		{
			this.popupChatUsers.destroy();
			return false;
		}

		var currentTab = this.getChatId();
		var htmlElement = '<span class="bx-notifier-item-help-popup">';
		for (var i = parseInt(BX.proxy_context.getAttribute('data-last-item')); i < this.userInChat[currentTab].length; i++)
		{
			if (!this.userInChat[currentTab][i] || !this.users[this.userInChat[currentTab][i]].active)
				continue;

			var avatarColor = BX.MessengerCommon.isBlankAvatar(this.users[this.userInChat[currentTab][i]].avatar)? 'style="background-color: '+this.users[this.userInChat[currentTab][i]].color+'"': '';
			htmlElement += '<span class="bx-notifier-item-help-popup-img bx-messenger-panel-chat-user" data-userId="'+this.userInChat[currentTab][i]+'">' +
				'<span class="bx-notifier-popup-avatar  bx-notifier-popup-avatar-status-'+BX.MessengerCommon.getUserStatus(this.users[this.userInChat[currentTab][i]])+'">' +
					'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(this.users[this.userInChat[currentTab][i]].avatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+this.users[this.userInChat[currentTab][i]].avatar+'" '+avatarColor+'>' +
				'</span>' +
				'<span class="bx-notifier-item-help-popup-name  '+(this.users[this.userInChat[currentTab][i]].extranet? ' bx-notifier-popup-avatar-extranet':'')+'">'+this.users[this.userInChat[currentTab][i]].name+'</span>' +
			'</span>';
		}
		htmlElement += '</span>';

		this.popupChatUsers = new BX.PopupWindow('bx-messenger-popup-chat-users', BX.proxy_context, {
			//parentPopup: this.popupMessenger,
			zIndex: 200,
			lightShadow : true,
			offsetTop: -2,
			offsetLeft: 3,
			autoHide: true,
			closeByEsc: true,
			events : {
				onPopupClose : function() { this.destroy() },
				onPopupDestroy : BX.proxy(function() { this.popupChatUsers = null; }, this)
			},
			content : BX.create("div", { props : { className : "bx-messenger-popup-menu"+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, html: htmlElement})
		});
		this.popupChatUsers.setAngle({offset: BX.proxy_context.offsetWidth});
		this.popupChatUsers.show();

		BX.bindDelegate(this.popupChatUsers.popupContainer, "click", {className: 'bx-messenger-panel-chat-user'}, BX.delegate(function(e){this.openPopupMenu(BX.proxy_context, 'chatUser'); return BX.PreventDefault(e);}, this));

		return BX.PreventDefault(e);
	}, this));
	BX.bindDelegate(this.popupContactListElements, "contextmenu", {className: 'bx-messenger-cl-item'}, BX.delegate(function(e) {
		this.openPopupMenu(BX.proxy_context, 'contactList');
		return BX.PreventDefault(e);
	}, this));
	BX.bindDelegate(this.popupContactListElements, "click", {className: 'bx-messenger-cl-item'}, BX.delegate(BX.MessengerCommon.contactListClickItem, BX.MessengerCommon));
	BX.bindDelegate(this.popupContactListElements, "click", {className: 'bx-messenger-chatlist-group-add'}, BX.delegate(function(e){
		if (!this.recentList)
		{
			this.recentList = true;
			BX.MessengerCommon.recentListRedraw();
		}
		this.openChatCreateForm(BX.proxy_context.getAttribute('data-type'));
	}, this));

	BX.bindDelegate(this.popupContactListElements, "click", {className: 'bx-messenger-chatlist-more'}, BX.delegate(this.toggleChatListGroup, this));
	BX.bindDelegate(this.popupContactListElements, "click", {className: 'bx-messenger-chatlist-search-button'}, BX.delegate(function(){
		this.BXIM.messenger.chatListSearchAction(BX.proxy_context.parentNode);
	}, this));

	BX.bind(this.popupContactListElements, "scroll", BX.delegate(function() {
		if (this.popupPopupMenu != null && this.popupPopupMenuDateCreate+500 < (+new Date()) && this.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-','') == 'contactList')
		{
			this.popupPopupMenu.close();
		}
	}, this));

	BX.bind(this.contactListPanelStatus, "click", BX.delegate(function(e){this.openPopupMenu(this.contactListPanelStatus, 'status');  return BX.PreventDefault(e);}, this));
	if (this.contactListPanelSettings)
	{
		BX.bind(this.contactListPanelSettings, "click", BX.delegate(function(e){this.BXIM.openSettings(); BX.PreventDefault(e)}, this));
	}
	if (this.contactListPanelFull)
	{
		BX.bind(this.contactListPanelFull, "click", BX.delegate(function(e){
			this.popupMessenger.enterFullScreen(); BX.PreventDefault(e)
		}, this));
	}

	/* EDIT FORM */
	BX.bind(this.popupMessengerEditTextarea, "focus", BX.delegate(function() {
		this.setClosingByEsc(false);
	}, this));
	BX.bind(this.popupMessengerEditTextarea, "blur", BX.delegate(function() {
		this.setClosingByEsc(true);
	}, this));
	BX.bind(this.popupMessengerEditTextarea, "keydown", BX.delegate(function(event){
		this.textareaPrepareText(BX.proxy_context, event, BX.delegate(function(){
			BX.MessengerCommon.editMessageAjax(this.popupMessengerEditMessageId, this.popupMessengerEditTextarea.value);
		}, this), BX.delegate(function(){
			this.editMessageCancel();
		}, this));
	}, this));

	if (BX.MessengerCommon.isDesktop())
	{
		BX.bind(this.popupMessengerEditTextarea, "contextmenu", BX.delegate(function(e) {
			this.openPopupMenu(e, 'copypaste', false, {'spell': true});
			return BX.PreventDefault(e);
		}, this));
		BX.bind(this.popupMessengerTextarea, "contextmenu", BX.delegate(function(e) {
			this.openPopupMenu(e, 'copypaste', false, {'spell': true});
			return BX.PreventDefault(e);
		}, this));
		BX.bind(this.popupMessengerEditTextarea, "click", BX.delegate(function(e) {
			if (!(e.metaKey || e.ctrlKey) || !this.desktop.enableInVersion(34))
				return false;

			var selectedText = BX.desktop.clipboardSelected(this.popupMessengerEditTextarea, true);
			if (!selectedText.text)
				return false;

			BXDesktopSystem.SpellCheckWord(selectedText.text, BX.delegate(function(isCorrect, suggest){
				if (isCorrect || suggest.length <= 0)
					return false;

				var selectedText = BX.desktop.clipboardSelected(this.popupMessengerEditTextarea, true);
				BX.desktop.clipboardReplaceText(this.popupMessengerEditTextarea, selectedText.selectionStart, selectedText.selectionEnd, suggest[0]);
			}, this));
		}, this));
		BX.bind(this.popupMessengerTextarea, "click", BX.delegate(function(e) {
			if (!(e.metaKey || e.ctrlKey) || !this.desktop.enableInVersion(34))
				return false;

			var selectedText = BX.desktop.clipboardSelected(this.popupMessengerTextarea, true);
			if (!selectedText.text)
				return false;

			BXDesktopSystem.SpellCheckWord(selectedText.text, BX.delegate(function(isCorrect, suggest){
				if (isCorrect || suggest.length <= 0)
					return false
				var selectedText = BX.desktop.clipboardSelected(this.popupMessengerTextarea, true);
				BX.desktop.clipboardReplaceText(this.popupMessengerTextarea, selectedText.selectionStart, selectedText.selectionEnd, suggest[0]);
			}, this));
		}, this));
	}
	BX.bind(this.popupMessengerTextarea, "paste", BX.delegate(this.onPaste, this));
	BX.bind(this.popupMessengerTextarea, "focus", BX.delegate(function() {
		this.textareaCheckText();
		this.setClosingByEsc(false);
		BX.addClass(this.popupMessengerTextarea.parentNode, 'bx-messenger-textarea-focus');
		BX.onCustomEvent(window, 'onImTextareaFocus', [true]);
	}, this));
	BX.bind(this.popupMessengerTextarea, "blur", BX.delegate(function() {
		this.textareaCheckText();
		this.setClosingByEsc(true);
		BX.removeClass(this.popupMessengerTextarea.parentNode, 'bx-messenger-textarea-focus');
		BX.onCustomEvent(window, 'onImTextareaFocus', [false]);
	}, this));

	BX.bind(this.popupMessengerTextarea, "keydown", BX.delegate(function(event){
		this.textareaPrepareText(BX.proxy_context, event, BX.delegate(this.sendMessage, this), BX.delegate(function(){
			if (BX.util.trim(this.popupMessengerEditTextarea.value).length <= 0)
			{
				this.popupMessengerEditTextarea.value = "";
				if (this.popupMessenger && !this.webrtc.callInit && this.popupMessengerEditTextarea.value.length <= 0)
					this.popupMessenger.destroy();
			}
			else
			{
				this.popupMessengerEditTextarea.value = "";
			}
		},this));
	}, this));

	BX.bind(this.popupMessengerTextarea, "keyup", BX.delegate(this.textareaCheckText, this));

	if (BX.MessengerCommon.isDesktop())
	{
		BX.bindDelegate(this.popupMessengerBodyWrap, "contextmenu", {className: 'bx-messenger-content-item-content'}, BX.delegate(function(e) {
			this.openPopupMenu(e, 'dialogContext', false);
			return BX.PreventDefault(e);
		}, this));
	}

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-avatar-button'}, BX.delegate(function(e)
	{
		var userId = BX.proxy_context.parentNode.parentNode.getAttribute('data-senderId');
		if (!this.users[userId] || this.users[userId].fake)
			return false;

		var userName =  BX.util.htmlspecialcharsback(this.users[userId].name);
		if (e.metaKey || e.ctrlKey)
		{
			userName = '[USER='+userId+']'+userName+'[/USER]';
		}
		else
		{
			BX.MessengerCommon.addMentionList(this.currentTab, userName, userId);
		}

		this.insertTextareaText(this.popupMessengerTextarea, ' '+userName+' ', false);
		this.popupMessengerTextarea.focus();

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-attach-block-spoiler'}, BX.delegate(function(e) {
		var item = BX.findChildByClassName(BX.proxy_context, "bx-messenger-attach-block-value");
		if (BX.hasClass(BX.proxy_context, 'bx-messenger-attach-block-spoiler-show'))
		{
			height = item.getAttribute('data-min-height');
			BX.removeClass(BX.proxy_context, 'bx-messenger-attach-block-spoiler-show');
		}
		else
		{
			BX.addClass(BX.proxy_context, 'bx-messenger-attach-block-spoiler-show');
			height = item.getAttribute('data-max-height');
		}

		item.style.maxHeight = height+'px';
	}, this));


	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-menu'}, BX.delegate(function(e) {
		if (e.metaKey || e.ctrlKey)
		{
			var messageId = BX.proxy_context.parentNode.parentNode.getAttribute('data-blockmessageid');
			if (this.message[messageId] && this.users[this.message[messageId].senderId].name)
			{
				var arQuote = [];

				if (this.message[messageId].text)
				{
					arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
				}
				if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
				{
					for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
					{
						var fileId = this.message[messageId].params.FILE_ID[j];
						var chatId = this.message[messageId].chatId;
						if (this.disk.files[chatId][fileId])
						{
							arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
						}
					}
				}

				if (arQuote.length > 0)
				{
					this.insertQuoteText(this.users[this.message[messageId].senderId].name, this.message[messageId].date, arQuote.join("\n"));
				}
			}
		}
		else
		{
			this.openPopupMenu(BX.proxy_context, 'dialogMenu');
		}
		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-like-digit'}, BX.delegate(function(e)
	{
		BX.localStorage.set('implc', true, 1);

		var messageId = BX.proxy_context.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('data-blockmessageid');
		if (messageId.substr(0,4) == 'temp' || !this.message[messageId].params || !this.message[messageId].params['LIKE'] || this.message[messageId].params['LIKE'].length <= 0)
			return false;

		if (this.popupChatUsers != null)
		{
			this.popupChatUsers.destroy();
			return false;
		}

		var htmlElement = '<span class="bx-notifier-item-help-popup">';
		for (var i = 0; i < this.message[messageId].params['LIKE'].length; i++)
		{
			if (this.users[this.message[messageId].params['LIKE'][i]])
			{
				var avatarColor = BX.MessengerCommon.isBlankAvatar(this.users[this.message[messageId].params['LIKE'][i]].avatar)? 'style="background-color: '+this.users[this.message[messageId].params['LIKE'][i]].color+'"': '';
				htmlElement += '<span class="bx-notifier-item-help-popup-img bx-messenger-panel-chat-user" data-userId="'+this.message[messageId].params['LIKE'][i]+'">' +
					'<span class="bx-notifier-popup-avatar  bx-notifier-popup-avatar-status-'+BX.MessengerCommon.getUserStatus(this.users[this.message[messageId].params['LIKE'][i]])+'">' +
						'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(this.users[this.message[messageId].params['LIKE'][i]].avatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+this.users[this.message[messageId].params['LIKE'][i]].avatar+'" '+avatarColor+'>' +
					'</span>' +
					'<span class="bx-notifier-item-help-popup-name  '+(this.users[this.message[messageId].params['LIKE'][i]].extranet? ' bx-notifier-popup-avatar-extranet':'')+'">'+this.users[this.message[messageId].params['LIKE'][i]].name+'</span>' +
				'</span>';
			}
		}
		htmlElement += '</span>';

		this.popupChatUsers = new BX.PopupWindow('bx-messenger-popup-like-users', BX.proxy_context, {
			//parentPopup: this.popupMessenger,
			zIndex: 200,
			lightShadow : true,
			offsetTop: 5,
			offsetLeft: 12,
			autoHide: true,
			closeByEsc: true,
			bindOptions: {position: "top"},
			events : {
				onPopupClose : function() { this.destroy() },
				onPopupDestroy : BX.proxy(function() { this.popupChatUsers = null; }, this)
			},
			content : BX.create("div", { props : { className : "bx-messenger-popup-menu"+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, html: htmlElement})
		});
		this.popupChatUsers.setAngle({offset: BX.proxy_context.offsetWidth});
		this.popupChatUsers.show();

		BX.bindDelegate(this.popupChatUsers.popupContainer, "click", {className: 'bx-messenger-panel-chat-user'}, BX.delegate(function(e){this.openPopupMenu(BX.proxy_context, 'chatUser'); return BX.PreventDefault(e);}, this));

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-keyboard-button-text'}, BX.delegate(BX.MessengerCommon.clickButtonKeyboard, BX.MessengerCommon));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-like-button'}, BX.delegate(function(e) {
		var chatId = this.getChatId();
		if (this.openChatFlag && !BX.MessengerCommon.userInChat(chatId))
		{
			return false;
		}
		if (BX.localStorage.get('implc', true, 1))
		{
			return false;
		}
		var messageId = BX.proxy_context.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute('data-blockmessageid');
		BX.MessengerCommon.messageLike(messageId);

		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-attach-delete'}, BX.delegate(function(e) {
		var messageId = BX.proxy_context.getAttribute('data-messageId');
		var attachId = BX.proxy_context.getAttribute('data-attachId');
		var action = BX.proxy_context.getAttribute('data-action');

		if (action == 'url')
		{
			BX.MessengerCommon.messageUrlAttachDelete(messageId, attachId);
		}

		return BX.PreventDefault(e);
	}, this));

	BX.bind(this.popupMessengerTextareaOpenJoin, 'click', BX.delegate(function() {
		if (this.currentTab.substr(0, 4) != 'chat')
			return false;

		if (this.BXIM.messenger.popupMessengerDialog && BX.hasClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message"))
		{
			return false;
		}

		var chatId = this.currentTab.substr(4);
		BX.MessengerCommon.joinToChat(chatId);

		return true;
	}, this));

	BX.bind(this.popupMessengerTextareaGeneralJoin, 'click', BX.delegate(function() {
		if (this.BXIM.messenger.popupMessengerDialog && BX.hasClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message"))
		{
			return false;
		}

		this.BXIM.settings.generalNotify = false;

		this.BXIM.saveSettings({'generalNotify': this.BXIM.settings.generalNotify});
		this.redrawChatHeader({userRedraw: false});

		this.popupMessengerTextarea.focus();

		return true;
	}, this));

	BX.bind(this.popupMessengerTextareaOpenLinesAnswer, 'click', BX.delegate(function() {
		if (this.currentTab.substr(0, 4) != 'chat')
			return false;

		if (this.BXIM.messenger.popupMessengerDialog && BX.hasClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message"))
		{
			return false;
		}

		var chatId = this.currentTab.substr(4);
		if (!BX.MessengerCommon.userInChat(chatId))
		{
			var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);
			if (parseInt(session.id) <= 0)
			{
				BX.MessengerCommon.linesStartSession(chatId);
			}
			else if (parseInt(this.chat[chatId].owner) == 0)
			{
				BX.MessengerCommon.linesAnswer(chatId);
			}
			else
			{
				BX.MessengerCommon.linesJoinSession(chatId);
			}
		}
		else
		{
			BX.MessengerCommon.linesAnswer(chatId);
		}

		return true;
	}, this));

	BX.bind(this.popupMessengerTextareaOpenLinesSkip, 'click', BX.delegate(function() {
		if (this.currentTab.substr(0, 4) != 'chat')
			return false;

		if (this.BXIM.messenger.popupMessengerDialog && BX.hasClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message"))
		{
			return false;
		}

		var chatId = this.currentTab.substr(4);
		if (!BX.MessengerCommon.userInChat(chatId))
			BX.MessengerCommon.dialogCloseCurrent(true);
		else
			BX.MessengerCommon.linesSkip(chatId);

		return true;
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-ajax'}, BX.delegate(function() {
		if (BX.proxy_context.getAttribute('data-entity') == 'readedList')
		{
			this.openPopupExternalData(BX.proxy_context, 'readedList', true, {'TAB': this.BXIM.messenger.currentTab})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'user')
		{
			this.openPopupExternalData(BX.proxy_context, 'user', true, {'ID': BX.proxy_context.getAttribute('data-userId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'openlines')
		{
			this.linesOpenHistory(BX.proxy_context.getAttribute('data-sessionId'));
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'chat')
		{
			this.openPopupExternalData(BX.proxy_context, 'chat', true, {'ID': BX.proxy_context.getAttribute('data-chatId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'network')
		{
			this.openMessenger('network'+BX.proxy_context.getAttribute('data-networkId'))
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'date')
		{
			this.openPopupMenu(BX.proxy_context, 'shareMenu');
		}
		else if (this.webrtc.phoneSupport() && BX.proxy_context.getAttribute('data-entity') == 'phoneCallHistory')
		{
			this.openPopupExternalData(BX.proxy_context, 'phoneCallHistory', true, {'ID': BX.proxy_context.getAttribute('data-historyID')})
		}
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-command'}, BX.delegate(function() {
		if (BX.proxy_context.getAttribute('data-entity') == 'send')
		{
			this.BXIM.sendMessage(this.currentTab, BX.proxy_context.nextSibling.innerHTML);
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'put')
		{
			this.BXIM.putMessage(BX.proxy_context.nextSibling.innerHTML);
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'call')
		{
			this.BXIM.phoneTo(BX.proxy_context.getAttribute('data-command'));
		}
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-date'}, BX.delegate(function(e) {
		if (this.openLinesFlag)
		{
			this.tooltip(BX.proxy_context, BX.message('IM_TIP_OL_SYSTEM'), {offsetLeft: 48});
		}
		BX.PreventDefault(e);
	}, this));

	BX.bind(this.popupMessengerBody, "scroll", BX.delegate(function(e) {
		if (this.unreadMessage[this.currentTab] && this.unreadMessage[this.currentTab].length > 0 && BX.MessengerCommon.isScrollMax(this.popupMessengerBody, 200) && this.BXIM.isFocus())
		{
			clearTimeout(this.readMessageTimeout);
			this.readMessageTimeout = setTimeout(BX.delegate(function ()
			{
				BX.MessengerCommon.readMessage(this.currentTab);
			}, this), 100);
		}

		BX.MessengerCommon.redrawDateMarks();
		BX.MessengerCommon.loadHistory(this.currentTab, false);

		if (this.popupPopupMenu != null)
		{
			if (this.popupPopupMenuDateCreate+500 < (+new Date()) && BX.util.in_array(this.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-',''), ["copypaste", "copylink", "dialogContext", "dialogMenu", "external-data"]))
			{
				this.popupPopupMenu.close();
			}
			else if (false && BX.util.in_array(this.popupPopupMenu.uniquePopupId.replace('bx-messenger-popup-',''), ["dialogMenu", "external-data"]))
			{
				this.popupPopupMenu.adjustPosition();
			}
		}
		if (this.popupChatUsers != null && this.popupChatUsers.uniquePopupId.replace('bx-messenger-popup-','') == 'like-users')
		{
			this.popupChatUsers.close();
		}
		if (this.popupTooltip != null)
		{
			this.popupTooltip.close();
		}
	}, this));

	BX.bindDelegate(this.popupMessengerBodyWrap, 'click', {className: 'bx-messenger-content-item-error'}, BX.delegate(BX.MessengerCommon.sendMessageRetry, BX.MessengerCommon));

	if (userId == 0)
	{
		this.extraOpen(
			BX.create("div", { props : { className : "bx-messenger-box-hello-wrap" }, children: [
				BX.create("div", { props : { className : "bx-messenger-box-hello" }, html: BX.message('IM_M_EMPTY')})
			]})
		);
	}
	else
	{
		BX.MessengerCommon.openDialog(userId);
	}
};

BX.Messenger.prototype.closeMessenger = function()
{
	if (!this.popupMessenger || this.webrtc.callInit)
		return false;

	if (this.BXIM.popupSettings != null)
		this.BXIM.popupSettings.close();

	if (this.webrtc.callInit)
	{
		this.webrtc.callCommand(this.webrtc.callChatId, 'decline', {'ACTIVE': this.callActive? 'Y': 'N', 'INITIATOR': this.initiator? 'Y': 'N'});
		this.webrtc.callAbort();
	}
	this.closeMenuPopup();

	this.popupMessenger = null;
	BX.remove(this.popupMessengerContent);
	this.popupMessengerContent = null;
	this.mentionListen = false;
	this.mentionDelimiter = '';
	this.BXIM.extraOpen = false;
	this.BXIM.dialogOpen = false;
	this.BXIM.notifyOpen = false;

	clearTimeout(this.popupMessengerDesktopTimeout);

	this.setUpdateStateStep();
	BX.unbind(document, "click", BX.proxy(this.BXIM.autoHide, this.BXIM));
	BX.unbind(window, "keydown", BX.proxy(this.closePopupFileMenuKeydown, this));
	this.webrtc.callOverlayClose();

	return true;
}

BX.Messenger.prototype.openMessengerPanel = function()
{
	if (!this.popupMessengerBodyPanel)
		return false;

	this.popupMessengerPanelOpen = true;

	this.popupMessengerBody.style.width = "calc(100% - 400px)";
	this.popupMessengerTextareaPlace.style.width = "calc(100% - 400px)";
	this.popupMessengerBodyPanel.style.height = this.popupMessengerBodyDialog.offsetHeight+'px';
	this.popupMessengerBodyPanel.style.right = "0";

	return true;
}

BX.Messenger.prototype.closeMessengerPanel = function()
{
	if (!this.popupMessengerBodyPanel)
		return false;

	this.popupMessengerPanelOpen = false;

	this.popupMessengerBody.style.removeProperty('width');
	this.popupMessengerTextareaPlace.style.removeProperty('width');
	this.popupMessengerBodyPanel.style.removeProperty('right');

	return true;
}

BX.Messenger.prototype.enterFullScreen = function()
{
  if (this.messengerFullscreenStatus)
  {
	  if (document.cancelFullScreen)
		  document.cancelFullScreen();
	  else if (document.mozCancelFullScreen)
		  document.mozCancelFullScreen();
	  else if (document.webkitCancelFullScreen)
		  document.webkitCancelFullScreen();
  }
  else
   {
	  if (BX.browser.IsChrome() || BX.browser.IsSafari())
	  {
		  this.popupMessengerContent.webkitRequestFullScreen(this.popupMessengerContent.ALLOW_KEYBOARD_INPUT);
		  BX.bind(window, "webkitfullscreenchange", this.messengerFullscreenBind = BX.proxy(this.eventFullScreen, this));
	  }
	  else if (BX.browser.IsFirefox())
	  {
		  this.popupMessengerContent.mozRequestFullScreen(this.popupMessengerContent.ALLOW_KEYBOARD_INPUT);
		  BX.bind(window, "mozfullscreenchange", this.messengerFullscreenBind = BX.proxy(this.eventFullScreen, this));
	  }
  }
};

BX.Messenger.prototype.eventFullScreen = function(event)
{
	if (this.messengerFullscreenStatus)
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
			BX.unbind(window, "webkitfullscreenchange", this.messengerFullscreenBind);
		else if (BX.browser.IsFirefox())
			BX.unbind(window, "mozfullscreenchange", this.messengerFullscreenBind);

		BX.removeClass(this.popupMessengerContent, 'bx-messenger-fullscreen');
		if (BX.browser.IsChrome())
		{
			BX.addClass(this.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
			setTimeout(BX.delegate(function(){
				BX.removeClass(this.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
			}, this), 100);
		}
		this.messengerFullscreenStatus = false;
		this.resizeMainWindow();
		this.popupMessenger.adjustPosition();
	}
	else
	{
		BX.addClass(this.popupMessengerContent, 'bx-messenger-fullscreen');
		this.messengerFullscreenStatus = true;
		this.resizeMainWindow();
		if (BX.browser.IsChrome())
		{
			setTimeout(BX.delegate(function(){
				  this.resizeMainWindow();
			}, this), 100);
		}
	}
	this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight - this.popupMessengerBody.offsetHeight;
};

BX.Messenger.prototype.tooltip = function(bind, text, params)
{
	if (this.tooltipIsOpen())
		this.popupTooltip.close();

	params = params || {};

	params.offsetLeft = params.offsetLeft || 0;
	params.offsetTop = params.offsetTop || BX.MessengerCommon.isDesktop()? 0: -10;
	params.width = params.width || 0;
	params.angle = typeof(params.angle) == 'undefined'? true: params.angle;
	params.showOnce = typeof(params.showOnce) == 'undefined'? false: params.showOnce;
	params.bindOptions = typeof(params.bindOptions) == 'undefined'? {position: "top"}: params.bindOptions;
	if (params.showOnce)
	{
		if (this.tooltipShowed[params.showOnce])
		{
			return true;
		}
		else
		{
			BX.userOptions.save('im', 'tooltipShowed', params.showOnce, 1);
			this.tooltipShowed[params.showOnce] = 1;
		}
	}

	var content = null;

	if (typeof(text) == 'object')
	{
		content = BX.create("div", { props : { className: "bx-messenger-tooltip", style : "padding-right: 5px;"+(params.width>0? "width: "+params.width+"px;": '') }, children: [text]})
	}
	else
	{
		content = BX.create("div", { props : { className: "bx-messenger-tooltip", style : "padding-right: 5px;"+(params.width>0? "width: "+params.width+"px;": '') }, html: text})
	}

	this.popupTooltip = new BX.PopupWindow('bx-messenger-tooltip', bind, {
		//parentPopup: this.popupMessenger,
		lightShadow: true,
		autoHide: true,
		darkMode: true,
		offsetLeft: params.offsetLeft,
		offsetTop: params.offsetTop,
		closeIcon : {},
		bindOptions: params.bindOptions,
		events : {
			onPopupClose : function() {this.destroy(); },
			onPopupDestroy : BX.delegate(function() { this.popupTooltip = null; }, this)
		},
		zIndex: 2000,
		content: content
	});
	if (params.angle)
	{
		this.popupTooltip.setAngle({offset:23, position: params.bindOptions.position == 'top'? 'bottom': 'top'});
	}
	this.popupTooltip.show();


	return true;
};
BX.Messenger.prototype.tooltipIsOpen = function()
{
	return this.popupTooltip != null;
}
BX.Messenger.prototype.tooltipClose = function()
{
	if (this.tooltipIsOpen())
		this.popupTooltip.close();
}

BX.Messenger.prototype.dialogStatusRedraw = function(params)
{
	if (this.popupMessenger == null)
		return false;

	params = params || {};

	this.popupMessengerPanelButtonCall1.className = this.callButtonStatus(this.currentTab);
	this.popupMessengerPanelButtonCall2.className = this.callButtonStatus(this.currentTab);
	this.popupMessengerPanelButtonCall3.className = this.phoneButtonStatus();

	if (this.popupMessengerFileButton)
		BX.show(this.popupMessengerFileButton);

	this.popupMessengerPanel.className = this.openChatFlag? 'bx-messenger-panel bx-messenger-context-user bx-messenger-hide': 'bx-messenger-panel bx-messenger-context-user';

	clearInterval(this.popupMessengerPanelLastDateInterval);

	if (this.openChatFlag)
	{
		this.textareaIconToggle();
		this.redrawChatHeader(params);
	}
	else if (this.users[this.currentTab])
	{
		BX.style(this.popupOpenLinesSpam, 'display', '');

		if (this.popupMessengerFileFormChatId)
		{
			this.popupMessengerFileFormChatId.value = this.userChat[this.currentTab]? this.userChat[this.currentTab]: 0;
			if (!this.disk.enableExternal && (this.users[this.currentTab].bot || this.users[this.currentTab].network))
			{
				this.popupMessengerFileFormInput.setAttribute('disabled', true);
			}
			else
			{
				if (parseInt(this.popupMessengerFileFormChatId.value) > 0)
				{
					this.popupMessengerFileFormInput.removeAttribute('disabled');
				}
				else
				{
					this.popupMessengerFileFormInput.setAttribute('disabled', true);
				}
			}
		}

		if (this.openChatFlag)
		{
			this.popupMessengerPanelMute.title = this.muteButtonStatus(this.currentTab)? BX.message("IM_M_CHAT_MUTE_ON_2"): BX.message("IM_M_CHAT_MUTE_OFF_2");
		}
		else
		{
			this.popupMessengerPanelMute.title = this.muteButtonStatus(this.currentTab)? BX.message("IM_M_USER_BLOCK_OFF"): BX.message("IM_M_USER_BLOCK_ON");
		}
		this.popupMessengerPanelMute.className = "bx-messenger-panel-button bx-messenger-panel-mute "+(this.muteButtonStatus(this.currentTab)? ' bx-messenger-panel-unmute': '');

		this.popupMessengerPanelAvatar.parentNode.href = this.users[this.currentTab].profile;
		this.popupMessengerPanelAvatar.parentNode.className = 'bx-messenger-panel-avatar bx-messenger-panel-avatar-status-'+BX.MessengerCommon.getUserStatus(this.users[this.currentTab]);
		this.popupMessengerPanelAvatar.parentNode.title = (BX.MessengerCommon.getUserStatus(this.users[this.currentTab], false)).title;
		this.popupMessengerPanelAvatar.src = this.users[this.currentTab].avatar? this.users[this.currentTab].avatar: this.BXIM.pathToBlankImage;
		this.popupMessengerPanelAvatar.className = "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar.src)? " bx-messenger-panel-avatar-img-default": "");
		BX.style(this.popupMessengerPanelAvatar, "background-color", (BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar.src) && this.users[this.currentTab].color? this.users[this.currentTab].color: ""));

		this.popupMessengerPanelTitle.href = this.users[this.currentTab].profile;
		this.popupMessengerPanelTitle.innerHTML = this.users[this.currentTab].name;
		if (this.BXIM.userId == this.currentTab)
		{
			this.popupMessengerPanelTitle.innerHTML = this.popupMessengerPanelTitle.innerHTML+' (<b><i>'+BX.message('IM_YOU')+'</i></b>)';
		}

		var funcUpdateLastDate = BX.delegate(function()
		{
			if (!this.popupMessengerPanelLastDate || this.currentTab && this.currentTab.toString().substr(0, 4) == 'chat')
				return false;

			var titleLastDate = BX.MessengerCommon.getUserLastDate(this.users[this.currentTab]);
			this.popupMessengerPanelLastDate.innerHTML = titleLastDate? '. '+titleLastDate: '';

			return true;
		}, this);
		funcUpdateLastDate();

		this.popupMessengerPanelLastDateInterval = setInterval(funcUpdateLastDate, 60000);

		this.popupMessengerPanelStatus.innerHTML = BX.MessengerCommon.getUserPosition(this.users[this.currentTab], false);

		var removeClass = [];
		if (this.users[this.currentTab].extranet)
		{
			BX.addClass(this.popupMessengerPanelTitle, 'bx-messenger-user-extranet');
			BX.addClass(this.popupMessengerDialog, 'bx-messenger-dialog-extranet');
			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-bot');
			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-network');
			removeClass.push('bx-messenger-chat-livechat');
			removeClass.push('bx-messenger-chat-lines');
			removeClass.push('bx-messenger-dialog-bot');
			removeClass.push('bx-messenger-dialog-network');
			removeClass.push('bx-messenger-dialog-self');
		}
		else if (this.users[this.currentTab].bot)
		{
			if (this.bot[this.currentTab] && this.bot[this.currentTab].type == 'network')
			{
				BX.addClass(this.popupMessengerPanelTitle, 'bx-messenger-user-network');
				BX.addClass(this.popupMessengerDialog, 'bx-messenger-dialog-network');
				BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-bot');
				BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-bot');
			}
			else
			{
				BX.addClass(this.popupMessengerPanelTitle, 'bx-messenger-user-bot');
				BX.addClass(this.popupMessengerDialog, 'bx-messenger-dialog-bot');
				BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-network');
				BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-network');
			}

			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-extranet');
			removeClass.push('bx-messenger-chat-livechat');
			removeClass.push('bx-messenger-chat-lines');
			removeClass.push('bx-messenger-dialog-extranet');
			removeClass.push('bx-messenger-dialog-self');

			this.popupMessengerPanelBotIcons = true;
		}
		else
		{
			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-extranet');
			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-bot');
			BX.removeClass(this.popupMessengerPanelTitle, 'bx-messenger-user-network');
			removeClass.push('bx-messenger-dialog-bot')
			removeClass.push('bx-messenger-dialog-network')
			removeClass.push('bx-messenger-chat-livechat');
			removeClass.push('bx-messenger-chat-lines');
			removeClass.push('bx-messenger-dialog-extranet');

			if (this.BXIM.userId == this.currentTab)
			{
				BX.addClass(this.popupMessengerDialog, 'bx-messenger-dialog-self');
			}
			else
			{
				removeClass.push('bx-messenger-dialog-self');
			}
		}
		this.popupMessengerTextarea.disabled = false;

		this.textareaIconToggle();

		removeClass.push('bx-messenger-chat-guest');
		removeClass.push('bx-messenger-chat-open');
		removeClass.push('bx-messenger-chat-chat');
		removeClass.push('bx-messenger-chat-call');
		removeClass.push('bx-messenger-chat-general');
		removeClass.push('bx-messenger-chat-general-first-open');
		removeClass.push('bx-messenger-chat-general-access');

		BX.removeClass(this.popupMessengerDialog, removeClass.join(" "));
	}

	return true;
};

BX.Messenger.prototype.muteButtonStatus = function(dialogId)
{
	var chatId = 0;
	if (dialogId.toString().substr(0,4) == 'chat')
	{
		chatId = dialogId.toString().substr(4);
	}
	else
	{
		chatId = this.userChat[dialogId];
	}
	return this.userChatBlockStatus[chatId] && this.userChatBlockStatus[chatId][this.BXIM.userId];
}

BX.Messenger.prototype.callButton = function(type)
{
	var button = null;
	if (type == 'call')
	{
		button = BX.create("span", {props : {className : this.phoneButtonStatus()}, children: [
			BX.create("a", {
				attrs: { href: "#call", title: BX.message("IM_PHONE_CALL") },
				props : { className : 'bx-messenger-panel-button bx-messenger-panel-call-audio' },
				events : {
					click: BX.delegate(function(e){
						if (this.webrtc.callInit)
							return false;

						var currentChat = this.chat[this.getChatId()];
						if (currentChat.call_number)
						{
							this.BXIM.phoneTo(currentChat.call_number);
						}
						else
						{
							this.webrtc.openKeyPad();
						}

						BX.PreventDefault(e);
					}, this)
				},
				html: BX.message("IM_PHONE_CALL")
			})
		]});
	}
	else
	{
		button = BX.create("span", {props : {className : this.callButtonStatus(this.currentTab)}, children: [
			BX.create("a", {
				attrs: { href: "#call", title: BX.message("IM_M_CALL_VIDEO") },
				props : { className : 'bx-messenger-panel-button bx-messenger-panel-call-video' },
				events : {
					click: BX.delegate(function(e){
						if (!this.webrtc.callInit)
							this.BXIM.callTo(this.currentTab, true);
						BX.PreventDefault(e);
					}, this)
				},
				html: BX.message("IM_M_CALL_VIDEO")
			}),
			BX.create("a", {
				attrs: { href: "#callMenu" },
				props : { className : 'bx-messenger-panel-call-menu' },
				events : {
					click: BX.delegate(function(e){
						if (!this.webrtc.callInit)
							this.openPopupMenu(BX.proxy_context, 'callMenu');
						BX.PreventDefault(e);
					}, this)
				}
			})
		]});
	}
	return button;
};

BX.Messenger.prototype.callButtonStatus = function(dialogId)
{
	dialogId = dialogId.toString();
	var elementClassName = 'bx-messenger-panel-button-box bx-messenger-panel-call-hide';
	if (this.openChatFlag && this.chat[dialogId.substr(4)] && (this.chat[dialogId.substr(4)].type == 'lines' || this.chat[dialogId.substr(4)].type == 'livechat'))
	{

	}
	else if (this.BXIM.ppServerStatus && (!this.users[dialogId] || !this.users[dialogId].network))
	{
		elementClassName = (!this.webrtc.callSupport(dialogId, this) || this.webrtc.callInit)? 'bx-messenger-panel-button-box bx-messenger-panel-call-disabled': 'bx-messenger-panel-button-box bx-messenger-panel-call-enabled';
	}

	return elementClassName;
};

BX.Messenger.prototype.phoneButtonStatus = function()
{
	var elementClassName = 'bx-messenger-panel-call-hide';
	if (this.BXIM.ppServerStatus)
		elementClassName = (this.webrtc.phoneSupport() && this.webrtc.phoneCanPerformCalls ? 'bx-messenger-panel-call-enabled': 'bx-messenger-panel-call-disabled');

	return 'bx-messenger-panel-call-phone '+elementClassName;
};

/* CHAT */
BX.Messenger.prototype.chatListSearchAction = function(element)
{
	this.realSearch = true;

	this.popupContactListElementsWrap.appendChild(BX.create("div", {
		props : { className: "bx-messenger-cl-item-search"},
		html : BX.message('IM_M_CL_SEARCH')
	}));
	BX.remove(element);

	BX.MessengerCommon.contactListRealSearch(this.contactListSearchText);
}
BX.Messenger.prototype.toggleChatListGroup = function()
{
	if (BX.hasClass(BX.proxy_context.parentNode.parentNode, 'bx-messenger-chatlist-show-all'))
	{
		this.contactListShowed[BX.proxy_context.getAttribute('data-id')] = false;
		BX.proxy_context.innerHTML = BX.proxy_context.getAttribute('data-text');
		BX.removeClass(BX.proxy_context.parentNode.parentNode, 'bx-messenger-chatlist-show-all');
		if(this.popupContactListElements)
		{
			var pos = BX.pos(BX.proxy_context, true);
			this.popupContactListElements.scrollTop = pos.top-100;
		}
	}
	else
	{
		this.contactListShowed[BX.proxy_context.getAttribute('data-id')] = true;
		BX.proxy_context.innerHTML = BX.message('IM_CL_HIDE');
		BX.addClass(BX.proxy_context.parentNode.parentNode, 'bx-messenger-chatlist-show-all');
	}
}

BX.Messenger.prototype.openChatCreateForm = function(type)
{
	this.currentTab = 'create';

	var descriptionNodes = []
	var avatarColor = "";
	var placeholder = "";
	if (type == 'chat')
	{
		avatarColor = "#49afdf";
		descriptionNodes = [
			BX.create("div", { props : { className : "bx-messenger-box-create-icon bx-messenger-box-create-icon-"+type}, children: [
				BX.create("div", { props : { className : "bx-messenger-box-create-icon-image"}})
			]}),
			BX.create("div", { props : { className : "bx-messenger-box-create-title"}, html: BX.message('IM_CL_CHAT_2')}),
			BX.create("div", { props : { className : "bx-messenger-box-create-text"}, html: BX.message(this.BXIM.bitrixIntranet? 'IM_C_ABOUT_CHAT': 'IM_C_ABOUT_CHAT_CHAT').split('#BR#').join("<br />").replace('#PROFILE_END#', '</a>').replace('#PROFILE_START#', '<a href="'+BXIM.path.profile+'edit/" target="_blank">')})
		];
	}
	else if (type == 'open' && (!this.BXIM.userExtranet || this.openChatEnable))
	{
		avatarColor = "#a7c131";

		descriptionNodes = [
			BX.create("div", { props : { className : "bx-messenger-box-create-icon bx-messenger-box-create-icon-"+type}, children: [
				BX.create("div", { props : { className : "bx-messenger-box-create-icon-image"}})
			]}),
			BX.create("div", { props : { className : "bx-messenger-box-create-title"}, html: BX.message('IM_CL_OPEN_CHAT')}),
			BX.create("div", { props : { className : "bx-messenger-box-create-text"}, html: BX.message(this.BXIM.bitrixIntranet? 'IM_C_ABOUT_OPEN': 'IM_C_ABOUT_OPEN_SITE').split('#BR#').join("<br />").replace('#PROFILE_END#', '</a>').replace('#PROFILE_START#', '<a href="'+BXIM.path.profile+'edit/" target="_blank">').replace('#CHAT_END#', '</b>').replace('#CHAT_START#', '<b>')})
		];
	}
	else
	{
		type = 'private';
		avatarColor = this.users[this.BXIM.userId].color;

		descriptionNodes = [
			BX.create("div", { props : { className : "bx-messenger-box-create-icon bx-messenger-box-create-icon-"+type}, children: [
				BX.create("div", { props : { className : "bx-messenger-box-create-icon-image"}})
			]}),
			BX.create("div", { props : { className : "bx-messenger-box-create-title"}, html: BX.message('IM_CL_PRIVATE_CHAT')}),
			BX.create("div", { props : { className : "bx-messenger-box-create-text"}, html: BX.message(this.BXIM.bitrixIntranet? 'IM_C_ABOUT_PRIVATE': 'IM_C_ABOUT_PRIVATE_SITE').split('#BR#').join("<br />").replace('#PROFILE_END#', '</a>').replace('#PROFILE_START#', '<a href="'+BXIM.path.profile+'edit/" target="_blank">')})
		];
	}

	if (this.chatCreateForm && !BX.browser.IsIE11())
	{
		this.extraOpen(this.chatCreateForm);

		if (this.chatCreateFormAvatar.parentNode)
		{
			this.chatCreateFormAvatar.parentNode.className = "bx-messenger-panel-avatar bx-messenger-panel-avatar-"+type;
		}
		BX.style(this.chatCreateFormAvatar, 'background-color', avatarColor);

		this.chatCreateType = type;
		this.chatCreateUsers = {};

		this.chatCreateFormDescription.innerHTML = '';
		BX.adjust(this.chatCreateFormDescription, {children: descriptionNodes});

		BX.MessengerCommon.clearMentionList('create');

		this.chatCreateFormChatTitle.value = '';
		this.chatCreateFormUsersInput.value = '';
		this.chatCreateFormUsersDest.innerHTML = '';

		this.popupCreateChatTextarea.value = '';
		this.textareaCheckText({'textarea': 'createChat'});

		BX.style(this.chatCreateFormBody, 'height', this.popupMessengerBodySize+'px');
		BX.style(this.popupCreateChatTextarea, 'height', this.popupMessengerTextareaSize+'px');

		if (type == 'open')
		{
			BX.addClass(this.chatCreateFormUsersInput.parentNode.parentNode, 'bx-messenger-hide');
			BX.removeClass(this.chatCreateFormChatTitle.parentNode.parentNode, 'bx-messenger-hide');
		}
		else
		{
			BX.addClass(this.chatCreateFormChatTitle.parentNode.parentNode, 'bx-messenger-hide');
			BX.removeClass(this.chatCreateFormUsersInput.parentNode.parentNode, 'bx-messenger-hide');
			BX.removeClass(this.chatCreateFormUsersInput, 'bx-messenger-hide');
			BX.addClass(this.chatCreateFormUsersInput, "bx-messenger-input-dest-empty")
		}

		if (this.chatCreateUsers.length > 0 && this.popupCreateChatTextarea.value.length > 0) // TODO length
		{
			this.popupCreateChatTextarea.focus();
		}
		else
		{
			if (type == 'open')
			{
				this.chatCreateFormChatTitle.focus();
			}
			else
			{
				this.chatCreateFormUsersInput.focus();
			}
		}
	}
	else
	{
		this.chatCreateType = type;
		this.chatCreateUsers = {};
		BX.MessengerCommon.clearMentionList('create');
		this.chatCreateForm = BX.create("div", { props : { className : "bx-messenger-box-create" },  children : [
			BX.create("div", { props : { className : "bx-messenger-panel" }, children : [
				BX.create("div", { props : { className : "bx-messenger-panel-wrap" }, children : [
					BX.create('div', { props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-"+type }, children: [
						this.chatCreateFormAvatar = BX.create('img', { attrs : { src : this.BXIM.pathToBlankImage, style: 'background-color: '+avatarColor}, props : { className : "bx-messenger-panel-avatar-img bx-messenger-panel-avatar-img-default" }})
					]}),
					BX.create("span", { props : { className : "bx-messenger-panel-title bx-messenger-panel-create-chat "+(type == 'open'? 'bx-messenger-hide':'') }, children: [
						BX.create("div", { props : { className : "bx-messenger-input-wrap bx-messenger-panel-create-input" }, children : [
							this.chatCreateFormUsersDest = BX.create("span", { props : { className : "bx-messenger-dest-items"}}),
							this.chatCreateFormUsersInput = BX.create("input", {props : { className : "bx-messenger-input bx-messenger-input-dest-empty" }, attrs: {type: "text", value: '', placeholder: BX.message('IM_C_PRIVATE_TITLE')}})
						]})
					]}),
					BX.create("span", { props : { className : "bx-messenger-panel-title bx-messenger-panel-create-chat "+(type != 'open'? 'bx-messenger-hide':'')}, children: [
						BX.create("div", { props : { className : "bx-messenger-input-wrap bx-messenger-panel-create-input" }, children : [
							this.chatCreateFormChatTitle = BX.create("input", {props : { className : "bx-messenger-input bx-messenger-input-dest-empty" }, attrs: {type: "text", value: '', placeholder: BX.message('IM_C_CHAT_TITLE')}})
						]})
					]})
				]})
			]}),
			BX.create("div", { props : { className : "bx-messenger-body-dialog" }, children: [
				this.chatCreateFormBody = BX.create("div", { props : { className : "bx-messenger-body" }, style : {height: this.popupMessengerBodySize+'px'}, children: [
					BX.create("div", { props : { className : "bx-messenger-box-create-desc"}, children: [
						this.chatCreateFormDescription = BX.create("div", { props : { className : "bx-messenger-box-create-desc-wrap"}, children: descriptionNodes})
					]})
				]}),
				BX.create("div", { props : { className : "bx-messenger-textarea-place"}, children : [
					BX.create("div", { props : { className : "bx-messenger-textarea-resize" }}),
					BX.create("div", { props : { className : "bx-messenger-textarea-send" }, children : [
						BX.create("a", {attrs: {href: "#send"}, props : { className : "bx-messenger-textarea-send-button" }, events : { click : BX.delegate(function(){
							this.createChat(this.chatCreateType, this.chatCreateUsers, this.popupCreateChatTextarea.value);
						}, this)}}),
						BX.create("span", {attrs : {title : BX.message('IM_M_SEND_TYPE_TITLE')}, props : { className : "bx-messenger-textarea-cntr-enter"}, html: this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter"), events: {
							click: BX.delegate(function() {
								this.BXIM.settings.sendByEnter = this.BXIM.settings.sendByEnter? false: true;
								this.BXIM.saveSettings({'sendByEnter': this.BXIM.settings.sendByEnter});

								BX.proxy_context.innerHTML = this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");
								this.popupMessengerTextareaSendType.innerHTML = BX.proxy_context.innerHTML;
							}, this)
						}})
					]}),
					BX.create("div", {props : { className : "bx-messenger-textarea-icons" }, children: [
						BX.create("div", {attrs : { title: BX.message('IM_SMILE_MENU')},  props : { className : "bx-messenger-textarea-smile" }, events : { click : BX.delegate(function(e){this.openSmileMenu({textarea: 'createChat', bind: e.currentTarget}); return BX.PreventDefault(e);}, this)}}),
						BX.create("div", {attrs : { title: BX.message('IM_MENTION_MENU')},  props : { className : "bx-messenger-textarea-mention" }, events : { click : BX.delegate(function(e){this.openMentionDialog({delay: 0, textarea: 'createChat'}); return BX.PreventDefault(e);}, this)}}),
						!this.disk.enable? null: BX.create("div", {attrs : { title: BX.message('IM_F_UPLOAD_MENU')}, props : { className : "bx-messenger-textarea-file" }, events: {click: BX.delegate(function(e){
							this.BXIM.openConfirm(BX.message('IM_F_ERR_NC'));
						}, this)}})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea" }, children : [
						this.popupCreateChatTextarea = BX.create("textarea", { props : { value: '', className : "bx-messenger-textarea-input"}, style : {height: this.popupMessengerTextareaSize+'px'}}),
						BX.create("div", { props : {className : "bx-messenger-textarea-placeholder"}, html : BX.message('IM_M_TA_TEXT')})
					]}),
					BX.create("div", { props : { className : "bx-messenger-textarea-clear" }})
				]})
			]})
		]});
		if (BX.MessengerCommon.isDesktop())
		{
			BX.bind(this.popupCreateChatTextarea, "contextmenu", BX.delegate(function(e) {
				this.openPopupMenu(e, 'copypaste', false, {'spell': true});
				return BX.PreventDefault(e);
			}, this));
		}
		BX.bind(this.popupCreateChatTextarea, "focus", BX.delegate(function() {
			this.textareaCheckText({'textarea': 'createChat'});
			this.setClosingByEsc(false);
			BX.addClass(this.popupCreateChatTextarea.parentNode, 'bx-messenger-textarea-focus');
		}, this));
		BX.bind(this.popupCreateChatTextarea, "blur", BX.delegate(function() {
			this.textareaCheckText({'textarea': 'createChat'});
			this.setClosingByEsc(true);
			BX.removeClass(this.popupCreateChatTextarea.parentNode, 'bx-messenger-textarea-focus');
		}, this));

		BX.bind(this.chatCreateFormChatTitle, "keydown", BX.delegate(function(event){
			this.textareaPrepareText(BX.proxy_context, event, BX.delegate(function(){
				this.createChat(this.chatCreateType, this.chatCreateUsers, this.popupCreateChatTextarea.value);
			}, this), function(){

			});
		}, this));

		BX.bind(this.chatCreateFormChatTitle, "keydown", BX.delegate(function(e) {
			if (e.keyCode == 9 || e.keyCode == 13)
			{
				this.popupCreateChatTextarea.focus();
				return BX.PreventDefault(e);
			}
		}, this));

		BX.bind(this.popupCreateChatTextarea, "keydown", BX.delegate(function(event){
			this.textareaPrepareText(BX.proxy_context, event, BX.delegate(function(){
				this.createChat(this.chatCreateType, this.chatCreateUsers, this.popupCreateChatTextarea.value);
			}, this), function(){

			});
		}, this));

		BX.bind(this.popupCreateChatTextarea, "keyup", BX.delegate(function(){
			this.textareaCheckText({'textarea': 'createChat'});
		}, this));

		if (BX.MessengerCommon.isDesktop())
		{
			BX.bindDelegate(this.popupMessengerBodyWrap, "contextmenu", {className: 'bx-messenger-content-item-content'}, BX.delegate(function(e) {
				this.openPopupMenu(e, 'dialogContext', false);
				return BX.PreventDefault(e);
			}, this));
		}
		this.extraOpen(this.chatCreateForm);

		if (type == 'open')
		{
			this.chatCreateFormChatTitle.focus();
		}
		else
		{
			this.chatCreateFormUsersInput.focus();
			BX.bind(this.chatCreateFormUsersInput, "keyup", BX.delegate(function(event){
				if (!this.popupChatDialog && this.chatCreateFormUsersInput.value.length > 0)
				{
					this.openChatDialog({
						'type': 'CHAT_CREATE',
						'bind': this.chatCreateFormUsersInput,
						'bindResult': this.chatCreateFormUsersDest,
						'bindSearch': this.chatCreateFormUsersInput,
						'bindUsersList': this.chatCreateUsers,
						'skipBind': this.chatCreateFormSkipDialogBind
					});
					this.chatCreateFormSkipDialogBind = true;
				}
			}, this))
		}
	}

}

BX.Messenger.prototype.getChatId = function()
{
	return this.currentTab.toString().substr(4);
}

BX.Messenger.prototype.createChat = function(type, users, message)
{
	if (this.BXIM.popupConfirm != null)
	{
		this.BXIM.popupConfirm.destroy();
		return false;
	}

	if (type == 'private')
	{
		var userId = 0;
		for (var i in users)
		{
			userId = users[i].id;
		}
		if (userId)
		{
			this.openMessenger(userId);

			this.popupMessengerTextarea.value = BX.MessengerCommon.prepareMention('create', message);
			this.sendMessage(userId);
		}
		else
		{
			this.chatCreateFormUsersInput.focus();
			return false;
		}
	}
	else
	{
		if (type == 'open')
		{
			if (BX.util.trim(this.chatCreateFormChatTitle.value) == '')
			{
				this.chatCreateFormChatTitle.focus();
				return false;
			}

			this.sendRequestChatDialog({
				'action' : 'CHAT_CREATE',
				'type' : 'open',
				'title' : this.chatCreateFormChatTitle.value,
				'message' : BX.MessengerCommon.prepareMention('create', message)
			});
		}
		else
		{
			if (BX.MessengerCommon.countObject(users) <= 0)
			{
				this.chatCreateFormUsersInput.focus();
				return false;
			}

			var arUsers = [];
			for (var i in users)
				arUsers.push(i);

			this.sendRequestChatDialog({
				'action' : 'CHAT_CREATE',
				'type' : 'chat',
				'users' : arUsers,
				'message' : BX.MessengerCommon.prepareMention('create', message)
			});
		}
	}

	return false;
}

BX.Messenger.prototype.kickFromChat = function(chatId, userId)
{
	if (!this.chat[chatId] && this.chat[chatId].owner != this.BXIM.userId && !this.userId[userId])
		return false;

	BX.ajax({
		url: this.BXIM.pathToAjax+'?CHAT_LEAVE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'IM_CHAT_LEAVE' : 'Y', 'CHAT_ID' : chatId, 'USER_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data){
			if (data.ERROR == '')
			{
				for (var i = 0; i < this.userInChat[data.CHAT_ID].length; i++)
					if (this.userInChat[data.CHAT_ID][i] == userId)
						delete this.userInChat[data.CHAT_ID][i];

				if (this.popupMessenger != null)
					BX.MessengerCommon.userListRedraw();

				if (!this.BXIM.ppServerStatus)
					BX.PULL.updateState(true);

				BX.localStorage.set('mclk', {'chatId': data.CHAT_ID, 'userId': data.USER_ID}, 5);
			}
		}, this)
	});
};

BX.Messenger.prototype.redrawChatHeader = function(params)
{
	if (!this.openChatFlag)
		return false;

	var chatId = this.getChatId();
	if (!this.chat[chatId])
		return false;

	params = params || {};
	params.userRedraw = params.userRedraw || true;

	if (this.popupMessengerFileFormChatId)
	{
		this.popupMessengerFileFormChatId.value = chatId;
		if (parseInt(this.popupMessengerFileFormChatId.value) > 0)
		{
			this.popupMessengerFileFormInput.removeAttribute('disabled');
		}
		else
		{
			this.popupMessengerFileFormInput.setAttribute('disabled', true);
		}
	}
	if (this.popupMessengerFileFormChatId)
	{
		this.popupMessengerFileFormChatId.value = chatId;
		if (this.chat[chatId] && !this.disk.enableExternal && this.chat[chatId].type == 'lines' && this.chat[chatId].entity_id.substr(0,8) != 'livechat')
		{
			this.popupMessengerFileFormInput.setAttribute('disabled', true);
		}
		else
		{
			if (parseInt(this.popupMessengerFileFormChatId.value) > 0)
			{
				this.popupMessengerFileFormInput.removeAttribute('disabled');
			}
			else
			{
				this.popupMessengerFileFormInput.setAttribute('disabled', true);
			}
		}
	}

	this.renameChatDialogFlag = false;

	BX.style(this.popupOpenLinesSpam, 'display', '');
	BX.style(this.popupOpenLinesClose, 'display', 'none');

	var removeClass = [];
	var addClass = [];
	if (this.chat[chatId].type == 'call')
	{
		this.popupMessengerPanelAvatar3.src = this.chat[chatId].avatar? this.chat[chatId].avatar: this.BXIM.pathToBlankImage;
		this.popupMessengerPanelAvatar3.className = "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar3.src)? " bx-messenger-panel-avatar-img-default": "");
		BX.style(this.popupMessengerPanelAvatar3, "background-color", (BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar3.src) && this.chat[chatId].color? this.chat[chatId].color: ""));

		if (this.popupMessengerPanelCallTitle)
			this.popupMessengerPanelCallTitle.innerHTML = this.chat[chatId].name;
		if (this.popupMessengerPanelCallDescription)
			this.popupMessengerPanelCallDescription.innerText = this.chat[chatId] && this.chat[chatId].entity_data_1 && this.chat[chatId].entity_data_1.toString().charAt(0) === "Y" ? this.chat[chatId].call_number : BX.message('IM_PHONE_DESC');
		this.popupMessengerPanelAvatarId3.value = chatId;
		this.disk.avatarFormIsBlocked(chatId, 'popupMessengerPanelAvatarUpload3', this.popupMessengerPanelAvatarForm3);

		this.popupMessengerPanelMute3.title = this.muteButtonStatus(this.currentTab)? BX.message("IM_M_CHAT_MUTE_ON_2"): BX.message("IM_M_CHAT_MUTE_OFF_2");
		this.popupMessengerPanelMute3.className = "bx-messenger-panel-button bx-messenger-panel-mute "+(this.muteButtonStatus(this.currentTab)? ' bx-messenger-panel-unmute': '');

		removeClass.push('bx-messenger-chat-guest');
		removeClass.push('bx-messenger-chat-open');
		removeClass.push('bx-messenger-chat-lines');
		removeClass.push('bx-messenger-chat-general');
		removeClass.push('bx-messenger-chat-general-first-open');
		removeClass.push('bx-messenger-chat-general-access');
		BX.style(this.popupOpenLinesTransfer, 'display', 'none');

		BX.addClass(this.popupMessengerDialog, 'bx-messenger-chat-call');
		BX.removeClass(this.popupMessengerDialog, removeClass.join(" "));

		this.popupMessengerPanelChat.className = 'bx-messenger-panel bx-messenger-context-chat bx-messenger-hide';
		this.popupMessengerPanelCall.className = 'bx-messenger-panel bx-messenger-context-call';
	}
	else
	{
		this.popupMessengerPanelMute2.title = this.muteButtonStatus(this.currentTab)? BX.message("IM_M_CHAT_MUTE_ON_2"): BX.message("IM_M_CHAT_MUTE_OFF_2");
		this.popupMessengerPanelMute2.className = "bx-messenger-panel-button bx-messenger-panel-mute "+(this.muteButtonStatus(this.currentTab)? ' bx-messenger-panel-unmute': '');

		var isDefaultImage = BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar2.src);
		this.popupMessengerPanelAvatar2.src = this.chat[chatId].avatar? this.chat[chatId].avatar: this.BXIM.pathToBlankImage;
		this.popupMessengerPanelAvatar2.className = "bx-messenger-panel-avatar-img"+(isDefaultImage? " bx-messenger-panel-avatar-img-default": "");
		BX.style(this.popupMessengerPanelAvatar2, "background-color", (BX.MessengerCommon.isBlankAvatar(this.popupMessengerPanelAvatar2.src) && this.chat[chatId].color? this.chat[chatId].color: ""));

		if (this.popupMessengerPanelChatTitle.className.indexOf('bx-messenger-chat-edit') == -1)
		{
			this.popupMessengerPanelChatTitle.innerHTML = this.chat[chatId].name;
		}

		this.popupMessengerPanelAvatarId2.value = chatId;
		this.disk.avatarFormIsBlocked(chatId, 'popupMessengerPanelAvatarUpload2', this.popupMessengerPanelAvatarForm2);

		this.popupMessengerPanelAvatarForm2.className = "bx-messenger-panel-avatar";
		if (this.chat[chatId].type == 'lines' || this.chat[chatId].type == 'livechat' || this.chat[chatId].type == 'chat')
		{
			var textareaDisabled = false;
			if (this.chat[chatId].type == 'livechat')
			{
				var session = BX.MessengerCommon.livechatGetSession(chatId);

				BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-lines');
				BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-livechat');
				addClass.push('bx-messenger-chat-livechat');
				removeClass.push('bx-messenger-chat-chat');
				removeClass.push('bx-messenger-chat-lines');
				BX.style(this.popupOpenLinesTransfer, 'display', 'none');
			}
			else if (this.chat[chatId].type == 'lines')
			{
				this.openLinesFlag = true;
				var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);

				BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-lines');
				BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-'+BX.MessengerCommon.linesGetSource(this.chat[chatId]));
				addClass.push('bx-messenger-chat-lines');
				removeClass.push('bx-messenger-chat-chat');
				removeClass.push('bx-messenger-chat-livechat');

				if (!BX.MessengerCommon.userInChat(chatId))
				{
					textareaDisabled = true;
					BX.style(this.popupOpenLinesTransfer, 'display', 'none');

					BX.style(this.popupMessengerTextareaOpenLinesTransfer, 'display', session.id? 'inline-block': 'none');
					this.popupMessengerTextareaOpenLinesAnswer.innerHTML = session.id? BX.message('IM_OL_INVITE_JOIN_2'): BX.message('IM_OL_INVITE_JOIN');
					this.popupMessengerTextareaOpenLinesSkip.innerHTML = BX.message('IM_OL_INVITE_CLOSE');
					this.popupMessengerTextareaOpenLinesText.innerHTML = session.id? BX.message('IM_OL_INVITE_TEXT_JOIN'): BX.message('IM_OL_INVITE_TEXT_OPEN');
				}
				else if (this.chat[chatId].owner == 0)
				{
					textareaDisabled = true;
					BX.style(this.popupOpenLinesTransfer, 'display', 'none');
					BX.style(this.popupOpenLinesClose, 'display', 'none');

					BX.style(this.popupMessengerTextareaOpenLinesTransfer, 'display', session.id? 'inline-block': 'none');
					this.popupMessengerTextareaOpenLinesAnswer.innerHTML = session.id? BX.message('IM_OL_INVITE_ANSWER'): BX.message('IM_OL_INVITE_JOIN');
					this.popupMessengerTextareaOpenLinesSkip.innerHTML = session.id? BX.message('IM_OL_INVITE_SKIP'): BX.message('IM_OL_INVITE_CLOSE');
					this.popupMessengerTextareaOpenLinesText.innerHTML = session.id? BX.message('IM_OL_INVITE_TEXT'): BX.message('IM_OL_INVITE_TEXT_OPEN');
				}
				else
				{
					if (this.chat[chatId].owner == this.BXIM.userId)
					{
						BX.style(this.popupOpenLinesTransfer, 'display', 'block');
						BX.style(this.popupOpenLinesClose, 'display', 'block');
					}
					else
					{
						BX.style(this.popupOpenLinesTransfer, 'display', 'none');
						BX.style(this.popupOpenLinesClose, 'display', 'none');
					}
				}

				if (session.id && parseInt(this.chat[chatId].owner) > 0)
				{
					BX.style(this.popupOpenLinesSpam, 'display', '');
				}
				else
				{
					BX.style(this.popupOpenLinesSpam, 'display', 'block');
				}

				if (this.linesSilentMode[chatId])
				{
					BX.addClass(this.popupMessengerHiddenModeButton, 'bx-messenger-textarea-hidden-active');
				}
				else
				{
					BX.removeClass(this.popupMessengerHiddenModeButton, 'bx-messenger-textarea-hidden-active');
				}
			}
			else
			{
				this.openLinesFlag = false;
				BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-chat');
				BX.style(this.popupOpenLinesTransfer, 'display', 'none');
				addClass.push('bx-messenger-chat-chat');
				removeClass.push('bx-messenger-chat-livechat');
				removeClass.push('bx-messenger-chat-lines');
			}

			if (textareaDisabled)
			{
				addClass.push('bx-messenger-chat-guest');
			}
			else
			{
				removeClass.push('bx-messenger-chat-guest');
			}

			this.popupMessengerTextarea.disabled = textareaDisabled;

			removeClass.push('bx-messenger-chat-open');
			removeClass.push('bx-messenger-chat-general');
			removeClass.push('bx-messenger-chat-general-first-open');
			removeClass.push('bx-messenger-chat-general-access');
		}
		else
		{
			BX.addClass(this.popupMessengerPanelAvatarForm2, 'bx-messenger-panel-avatar-open');
			BX.style(this.popupOpenLinesTransfer, 'display', 'none');

			addClass.push('bx-messenger-chat-open');
			removeClass.push('bx-messenger-chat-chat');
			removeClass.push('bx-messenger-chat-livechat');
			removeClass.push('bx-messenger-chat-lines');

			var textareaDisabled = false;
			if (chatId == this.generalChatId)
			{
				addClass.push('bx-messenger-chat-general');
				if (!this.canSendMessageGeneralChat)
				{
					addClass.push('bx-messenger-chat-general-access');
					this.popupMessengerTextareaGeneralText.innerHTML = BX.message('IM_G_ACCESS');
					textareaDisabled = true;
				}
				else if (this.BXIM.settings.generalNotify)
				{
					addClass.push('bx-messenger-chat-general-first-open');
					// onclick="BX.Helper.show(\"redirect=detail&HD_ID='+BX.message('IM_G_JOIN_HELPDESK_ID')+'\");"
					this.popupMessengerTextareaGeneralText.innerHTML = BX.message('IM_G_JOIN').replace('#LINK_START#', '<a href="'+BX.message('IM_G_JOIN_LINK')+'" target="_blank" onclick="BXIM.closeMessenger()" style="margin-left: 10px; text-decoration: underline;">').replace('#LINK_END#', '</a>').replace('#ICON#', '<span class="bx-messenger-icon-notify-mute" onclick="BX.MessengerCommon.muteMessageChat(\'chat'+this.generalChatId+'\');"></span>');
					textareaDisabled = true;
				}
				else
				{
					removeClass.push('bx-messenger-chat-general-first-open');
					removeClass.push('bx-messenger-chat-general-access');
				}
			}
			else
			{
				removeClass.push('bx-messenger-chat-general');
				removeClass.push('bx-messenger-chat-general-first-open');
				removeClass.push('bx-messenger-chat-general-access');
			}

			if (textareaDisabled)
			{
				this.popupMessengerTextarea.disabled = true;
			}
			else if (BX.MessengerCommon.userInChat(chatId))
			{
				this.popupMessengerTextarea.disabled = false;
				removeClass.push('bx-messenger-chat-guest');
			}
			else
			{
				this.popupMessengerTextarea.disabled = true;
				addClass.push('bx-messenger-chat-guest');
			}
		}
		removeClass.push('bx-messenger-chat-call');

		BX.addClass(this.popupMessengerDialog, addClass.join(" "));
		BX.removeClass(this.popupMessengerDialog, removeClass.join(" "));

		if (isDefaultImage)
			BX.addClass(this.popupMessengerPanelStatus2, 'bx-messenger-panel-avatar-status-hide');
		else
			BX.removeClass(this.popupMessengerPanelStatus2, 'bx-messenger-panel-avatar-status-hide');

		if (this.chat[chatId].entity_type != "" && BX.MessengerCommon.getEntityTypePath(chatId))
		{
			this.popupMessengerPanelChat.className = 'bx-messenger-panel bx-messenger-context-chat bx-messenger-panel-with-menu';
		}
		else
		{
			this.popupMessengerPanelChat.className = 'bx-messenger-panel bx-messenger-context-chat';
		}

		if (this.chat[chatId].entity_type != "" && BX.MessengerCommon.checkRestriction(chatId, 'EXTEND'))
		{
			BX.style(this.popupMessengerPanelButtonExtend, 'display', 'none');
		}
		else
		{
			BX.style(this.popupMessengerPanelButtonExtend, 'display', 'block');
		}

		this.popupMessengerPanelCall.className = 'bx-messenger-panel bx-messenger-context-call bx-messenger-hide';
	}

	this.popupMessengerPanel.className = 'bx-messenger-panel bx-messenger-context-user bx-messenger-hide';

	if (!this.userInChat[chatId])
	{
		this.popupMessengerPanelUsers.innerHTML = this.chat[chatId].fake? BX.message('IM_CL_LOAD'): BX.message('IM_C_EMPTY');
		return false;
	}

	if (params.userRedraw)
	{
		var showUser = false;
		this.popupMessengerPanelUsers.innerHTML = '';

		if (this.userInChat[chatId])
		{
			this.userInChat[chatId].sort(BX.delegate(function(a, b) {
				if (!this.users[a] || !this.users[b]) return 0;
				i = 0;
				if (this.users[a].status != 'offline') { i += 20; }
				if (this.chat[chatId].owner == a) { i += 10 }
				if (this.users[a].status == 'online') { i += 5; }
				if (this.users[a].status == 'mobile') { i += 3; }
				if (this.users[a].avatar != "/bitrix/js/im/images/blank.gif") { i += 5 }
				if (a < b) { i += 1 }
				ii = 0;
				if (this.users[b].status != 'offline') { ii += 20; }
				if (this.chat[chatId].owner == b) { ii += 10 }
				if (this.users[b].status == 'online') { ii += 5; }
				if (this.users[b].status == 'mobile') { ii += 3; }
				if (this.users[b].avatar != "/bitrix/js/im/images/blank.gif") { ii += 5 }
				if (b < a) { ii += 1 }
				if (i < ii) { return 1; } else if (i > ii) { return -1;}else{ return 0;}
			}, this));
		}

		var extranetInChat = this.chat[chatId].extranet;
		if (this.chat[chatId].extranet == "")
		{
			extranetInChat = false;
			for (var i = 0; i < this.userInChat[chatId].length; i++)
			{
				extranetInChat = this.users[this.userInChat[chatId][i]] && this.users[this.userInChat[chatId][i]].extranet;
			}
		}
		if (this.chat[chatId].type == 'livechat')
		{
			BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-extranet');
			BX.removeClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-extranet');
			BX.addClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-title-lines');
		}
		else if (this.chat[chatId].type == 'lines')
		{
			BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-extranet');
			BX.removeClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-extranet');
			BX.addClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-title-lines');

			if (session.crm == 'Y')
			{
				BX.style(this.popupMessengerPanelCrm, 'display', 'inline-block');
			}
			else
			{
				BX.style(this.popupMessengerPanelCrm, 'display', 'none');
			}
		}
		else if (this.chat[chatId].extranet)
		{
			BX.addClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-extranet');
			BX.addClass(this.popupMessengerDialog, 'bx-messenger-dialog-extranet');
			BX.style(this.popupMessengerPanelCrm, 'display', 'none');
		}
		else
		{
			BX.removeClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-extranet')
			BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-extranet')
			BX.removeClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-title-lines');
			BX.style(this.popupMessengerPanelCrm, 'display', 'none');
		}
		BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-bot');
		BX.removeClass(this.popupMessengerDialog, 'bx-messenger-dialog-network');

		var maxCount = Math.floor((this.popupMessengerPanelUsers.offsetWidth)/135);
		if (maxCount >= this.userInChat[chatId].length)
		{
			for (var i = 0; i < this.userInChat[chatId].length && i < maxCount; i++)
			{
				var user = this.users[this.userInChat[chatId][i]];
				if (!user || !user.active)
				{
					continue;
				}

				if (this.chat[chatId].entity_type == "LINES" && this.chat[chatId].owner == 0 && user.id != this.BXIM.userId && !(user.bot || user.connector))
				{
					continue;
				}

				var avatarColor = BX.MessengerCommon.isBlankAvatar(user.avatar)? 'style="background-color: '+user.color+'"': '';
				this.popupMessengerPanelUsers.innerHTML += '<span class="bx-messenger-panel-chat-user" data-userId="'+user.id+'">' +
					'<span class="bx-notifier-popup-avatar bx-notifier-popup-avatar-status-'+BX.MessengerCommon.getUserStatus(user)+(this.chat[chatId].owner == user.id? ' bx-notifier-popup-avatar-owner': '')+(user.extranet && !user.connector? ' bx-notifier-popup-avatar-extranet':'')+'">' +
						'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(user.avatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+user.avatar+'" title="'+user.name+'" '+avatarColor+'>' +
						'<span class="bx-notifier-popup-avatar-status-icon" title="'+user.name+'"></span>'+
					'</span>' +
					'<span class="bx-notifier-popup-user-name'+(user.extranet && !user.connector? ' bx-messenger-panel-chat-user-name-extranet':'')+(user.connector? ' bx-messenger-panel-chat-user-name-lines':'')+(user.bot? ' bx-messenger-panel-chat-user-name-bot':'')+'">'+user.name+'</span>' +
				'</span>';

				showUser = true;
			}
		}
		else
		{
			maxCount = Math.floor((this.popupMessengerPanelUsers.offsetWidth-10)/32);
			for (var i = 0; i < this.userInChat[chatId].length && i < maxCount; i++)
			{
				var user = this.users[this.userInChat[chatId][i]];
				if (user && user.active)
				{
					var avatarColor = BX.MessengerCommon.isBlankAvatar(user.avatar)? 'style="background-color: '+user.color+'"': '';
					this.popupMessengerPanelUsers.innerHTML += '<span class="bx-messenger-panel-chat-user" data-userId="'+user.id+'">' +
						'<span class="bx-notifier-popup-avatar bx-notifier-popup-avatar-status-'+BX.MessengerCommon.getUserStatus(user)+(this.chat[chatId].owner == user.id? ' bx-notifier-popup-avatar-owner': '')+(user.extranet? ' bx-notifier-popup-avatar-extranet':'')+'">' +
							'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(user.avatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+user.avatar+'" title="'+user.name+'" '+avatarColor+'>' +
						'<span class="bx-notifier-popup-avatar-status-icon" title="'+user.name+'"></span>'+
						'</span>' +
					'</span>';
					showUser = true;
				}
			}
			if (showUser && this.userInChat[chatId].length > maxCount)
				this.popupMessengerPanelUsers.innerHTML += '<span class="bx-notifier-popup-user-more" data-last-item="'+i+'">'+BX.message('IM_M_CHAT_MORE_USER').replace('#USER_COUNT#', (this.userInChat[chatId].length-maxCount))+'</span>';
		}

		if (!showUser)
		{
			this.popupMessengerPanelUsers.innerHTML = BX.message('IM_CL_LOAD');
		}
	}
};

BX.Messenger.prototype.updateChatAvatar = function(chatId, chatAvatar)
{
	if (this.chat[chatId] && chatAvatar && chatAvatar.length > 0)
	{
		this.chat[chatId].avatar = chatAvatar;

		this.dialogStatusRedraw();
		BX.MessengerCommon.userListRedraw();
	}
	return true;
}
BX.Messenger.prototype.renameChatDialog = function()
{
	var chatId = this.getChatId();
	if (this.renameChatDialogFlag || !BX.MessengerCommon.userInChat(chatId) || BX.MessengerCommon.checkRestriction(chatId, 'RENAME'))
		return false;

	this.renameChatDialogFlag = true;

	BX.addClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-edit');

	this.popupMessengerPanelChatTitle.innerHTML = '';
	BX.adjust(this.popupMessengerPanelChatTitle, {children: [
		BX.create("div", { props : { className : "bx-messenger-input-wrap bx-messenger-panel-title-chat-input" }, children : [
			this.renameChatDialogInput = BX.create("input", {props : { className : "bx-messenger-input" }, attrs: {type: "text", value: BX.util.htmlspecialcharsback(this.chat[chatId].name)}})
		]})
	]});
	this.renameChatDialogInput.focus();
	BX.bind(this.renameChatDialogInput, "blur", BX.delegate(function(){
		BX.removeClass(this.popupMessengerPanelChatTitle, 'bx-messenger-chat-edit');

		BX.MessengerCommon.renameChat(chatId, this.renameChatDialogInput.value);

		BX.remove(this.renameChatDialogInput);
		this.renameChatDialogInput = null;

		this.popupMessengerPanelChatTitle.innerHTML = this.chat[chatId].name;
		this.renameChatDialogFlag = false;
	}, this));

	BX.bind(this.renameChatDialogInput, "keydown", BX.delegate(function(e) {
		if (e.keyCode == 27 && !BX.MessengerCommon.isDesktop())
		{
			this.renameChatDialogInput.value = BX.util.htmlspecialcharsback(this.chat[chatId].name);
			this.popupMessengerTextarea.focus();
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 9 || e.keyCode == 13)
		{
			this.popupMessengerTextarea.focus();
			return BX.PreventDefault(e);
		}
	}, this));
};

BX.Messenger.prototype.openMentionDialog = function(params)
{
	if (this.popupSmileMenu != null)
	{
		this.popupSmileMenu.destroy();
	}

	if (this.popupChatDialog != null)
	{
		this.popupChatDialog.close();
		return false;
	}

	params = params || {};
	params.delay = params.delay || 300;
	params.textarea = params.textarea || 'default';

	var textarea = params.textarea == 'createChat'? this.popupCreateChatTextarea: this.popupMessengerTextarea;

	textarea.focus();
	if (textarea.value.substr(-1) != "@")
	{
		this.insertTextareaText(textarea, "@");
	}

	this.mentionListen = true;
	this.mentionDelimiter = "@";
	this.openChatDialog({'type': 'MENTION', 'bind': textarea, 'focus': false, 'delimiter': this.mentionDelimiter, 'delay': params.delay})

	this.setClosingByEsc(false);
}

BX.Messenger.prototype.openChatDialog = function(params)
{
	if (!this.enableGroupChat)
		return false;

	if (this.popupChatDialog != null)
	{
		this.popupChatDialog.close();
		return false;
	}
	if (this.popupTransferDialog != null)
	{
		this.popupTransferDialog.close();
		return false;
	}

	BX.MessengerCommon.contactListSearchClear();
	this.closePopupFileMenu();

	if (this.popupPopupMenu != null)
		this.popupPopupMenu.destroy();

	if (this.popupSmileMenu != null)
	{
		this.popupSmileMenu.destroy();
	}
	if (this.commandPopup != null)
	{
		this.commandPopup.destroy();
	}
	if (this.popupIframeMenu != null && this.popupIframeBind)
	{
		this.popupIframeMenu.destroy();
	}
	if (params.type == 'CHAT_EXTEND' && this.popupMessengerTextarea.disabled)
	{
		return false
	}

	var type = null;
	if (params.type == 'CHAT_ADD' || params.type == 'CHAT_EXTEND' || params.type == 'CALL_INVITE_USER' || params.type == 'MENTION' || params.type == 'CHAT_CREATE')
		this.popupChatDialogDestType = params.type;
	else
		return false;


	var offsetTop = 5;
	var angleOffset = {offset: BX.MessengerCommon.isPage()? 39: 210};
	var offsetLeft = BX.MessengerCommon.isPage()? this.webrtc.callActive? 5: 0: this.webrtc.callActive? -162: -170;

	this.popupChatDialogEmptyCallback = function(){}

	this.popupChatDialogExceptUsers = [];
	if (typeof(params.chatId) != 'undefined' && this.userInChat[params.chatId])
	{
		this.popupChatDialogExceptUsers = this.userInChat[params.chatId];
	}

	if (params.type == 'MENTION')
	{
		params.maxUsers = 1;
		offsetTop = BX.MessengerCommon.isPage()? 15: 10;
		offsetLeft = -10;
		angleOffset = {offset: 39};
	}
	else if (params.type == 'CHAT_CREATE')
	{
		if (this.chatCreateType == 'private')
		{
			params.maxUsers = 1;
		}

		this.popupChatDialogDestElements = params.bindResult;
		this.popupChatDialogContactListSearch = params.bindSearch;
		this.popupChatDialogUsers = params.bindUsersList;

		for (var i in this.popupChatDialogUsers)
		{
			this.popupChatDialogExceptUsers.push(this.popupChatDialogUsers[i].id);
		}

		this.popupChatDialogEmptyCallback = BX.delegate(function(){
			if (this.popupChatDialog)
				this.popupChatDialog.close();
		},this);
	}

	this.popupChatDialogMaxChatUsers = typeof(params.maxUsers) == 'undefined'? 1000000: parseInt(params.maxUsers);

	if (typeof(params.chatId) != 'undefined' && this.userInChat[params.chatId])
	{
		this.popupChatDialogMaxChatUsers = this.popupChatDialogMaxChatUsers-this.userInChat[params.chatId].length;
	}

	params.skipBind = typeof(params.skipBind) == 'undefined'? false: params.skipBind;

	var bindElement = params.bind? params.bind: null;
	var hideHistoryCheckbox = params.type != 'CHAT_EXTEND' || this.chat[params.chatId].entity_type == 'LINES';

	this.popupChatDialog = new BX.PopupWindow('bx-messenger-popup-newchat', bindElement, {
		//parentPopup: this.popupMessenger,
		lightShadow : true,
		closeIcon : true,
		offsetTop: offsetTop,
		offsetLeft: offsetLeft,
		autoHide: true,
		bindOptions: (params.type == 'MENTION'? {position: "top"}: {}),
		buttons: params.type == 'MENTION' || params.type == 'CHAT_CREATE'? []: [
			new BX.PopupWindowButton({
				text : BX.message('IM_M_CHAT_BTN_JOIN'),
				className : "popup-window-button-accept",
				events : { click : BX.delegate(function() {
					if (this.popupChatDialogDestType == 'CHAT_ADD')
					{
						var arUsers = [this.currentTab];
						for (var i in this.popupChatDialogUsers)
							arUsers.push(i);

						this.sendRequestChatDialog({
							'action' : this.popupChatDialogDestType,
							'users' : arUsers
						})
					}
					else if (this.popupChatDialogDestType == 'CHAT_EXTEND')
					{
						var arUsers = [];
						for (var i in this.popupChatDialogUsers)
							arUsers.push(i);

						this.sendRequestChatDialog({
							'action' : this.popupChatDialogDestType,
							'chatId' : this.getChatId(),
							'users' : arUsers
						})
					}
					else if (this.popupChatDialogDestType == 'CALL_INVITE_USER')
					{
						var arUsers = [];
						for (var i in this.popupChatDialogUsers)
							arUsers.push(i);

						this.webrtc.callInviteUserToChat(arUsers);
					}
				}, this) }
			}),
			new BX.PopupWindowButton({
				text : BX.message('IM_M_CHAT_BTN_CANCEL'),
				events : { click : BX.delegate(function() { this.popupChatDialog.close(); }, this) }
			})
		],
		closeByEsc: true,
		zIndex: 200,
		events : {
			onPopupClose : function() {
				this.destroy();
			},
			onPopupDestroy : BX.delegate(function() {
				this.popupChatDialog = null;
				this.mentionListen = false;
				this.mentionDelimiter = '';
				this.popupChatDialogDestType = '';
				if (params.type != 'CHAT_CREATE')
				{
					this.popupChatDialogUsers = {};
				}
				if (params.type == 'MENTION' || params.type == 'CHAT_CREATE')
				{
					BX.proxy_context.bindElement.focus();
				}
				else
				{
					this.popupChatDialogContactListElementsType = '';
					this.popupChatDialogContactListElements = null;
				}
			}, this)
		},
		content : BX.create("div", { props : { className : "bx-messenger-popup-newchat-wrap bx-messenger-popup-newchat-wrap-style-"+params.type+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: [
			BX.create("div", { props : { className : "bx-messenger-popup-newchat-caption" }, html: (params.type == 'MENTION'? BX.message('IM_MENTION_MENU'): BX.message('IM_M_CHAT_TITLE'))}),
			params.type == 'CHAT_CREATE'? null: BX.create("div", { props : { className : "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-dest bx-messenger-popup-newchat-dest-even"+(params.type == 'MENTION'? ' bx-messenger-hide': '') }, children: [
				this.popupChatDialogDestElements = BX.create("span", { props : { className : "bx-messenger-dest-items" }}),
				this.popupChatDialogContactListSearch = BX.create("input", {props : { className : "bx-messenger-input" }, attrs: {type: "text", placeholder: BX.message(this.BXIM.bitrixIntranet? 'IM_M_SEARCH_PLACEHOLDER_CP': 'IM_M_SEARCH_PLACEHOLDER'), value: ''}})
			]}),
			this.popupChatDialogContactListElements = BX.create("div", { props : { className : "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-cl bx-messenger-recent-wrap" }, children: BX.create("div", {
				props : { className: "bx-messenger-cl-item-load"},
				html : BX.message('IM_CL_LOAD')
			})}),
			hideHistoryCheckbox ? null: BX.create("div", { props : { className : "bx-messenger-popup-newchat-checkbox" }, children: [
				this.popupChatDialogShowHistory = BX.create("input", {props : { className : "bx-messenger-checkbox" }, attrs: {id: "popupChatDialogShowHistory", type: "checkbox", checked: (this.BXIM.options.chatExtendShowHistory? "true": ""), name: "popupChatDialogShowHistory"}}),
				BX.create("label", { attrs: {"for": "popupChatDialogShowHistory"}, props : { className : "bx-messenger-checkbox-label" }, html: BX.message('IM_M_CHAT_SHOW_HISTORY')})
			]})
		]})
	});

	this.popupChatDialog.setAngle(angleOffset);
	this.popupChatDialog.show();

	BX.addClass(this.popupChatDialog.popupContainer, "bx-messenger-mark");
	//BX.bind(this.popupChatDialog.popupContainer, "click", BX.PreventDefault);
	this.popupChatDialogContactListElementsType = params.type;

	BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': (this.popupChatDialogDestType == 'MENTION'), 'exceptUsers': this.popupChatDialogExceptUsers, timeout: 0, 'callback': {'empty': this.popupChatDialogEmptyCallback}});

	BX.bindDelegate(this.popupChatDialogContactListElements, "click", {className: 'bx-messenger-chatlist-more'}, BX.delegate(this.toggleChatListGroup, this));

	if (!params.skipBind && params.type != 'MENTION')
	{
		this.popupChatDialogContactListSearch.focus();

		BX.bind(this.popupChatDialogContactListSearch, "keyup", BX.delegate(function(event){
			if (event.keyCode == 16 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 91) // 224, 17
				return false;

			if (event.keyCode == 37 || event.keyCode == 39)
				return true;

			if (this.popupChatDialogContactListSearch.value != this.popupChatDialogContactListSearchLastText || this.popupChatDialogContactListSearch.value  == '')
			{
				if (this.popupChatDialogContactListSearch.value == '' && this.popupChatDialog && this.popupChatDialogDestType == 'CHAT_CREATE')
				{
					this.popupChatDialog.close();
					return false;
				}
			}
			else if (event.keyCode == 224 || event.keyCode == 18 || event.keyCode == 17)
			{
				return true;
			}

			if (event.keyCode == 8 && this.popupChatDialogContactListSearch.value == '')
			{
				var lastId = null;
				var arMentionSort = BX.util.objectSort(this.popupChatDialogUsers, 'date', 'asc');
				for (var i = 0; i < arMentionSort.length; i++)
				{
					lastId = arMentionSort[i].id;
				}
				if (lastId)
				{
					delete this.popupChatDialogUsers[lastId];
					this.redrawChatDialogDest();
				}
			}

			if (event.keyCode == 27 && this.popupChatDialogContactListSearch.value != '')
				BX.MessengerCommon.preventDefault(event);

			if (event.keyCode == 27)
			{
				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}
				this.popupChatDialogContactListSearch.value = '';
			}

			if (event.keyCode == 38 || event.keyCode == 40)
			{
				// todo up/down select
				return true;
			}

			if (event.keyCode == 13 && this.popupChatDialogContactListSearch.value != '')
			{
				var item = BX.findChildByClassName(this.popupChatDialogContactListElements, "bx-messenger-cl-item");
				if (item)
				{
					if (this.popupChatDialogContactListSearch.value != '')
					{
						this.popupChatDialogContactListSearch.value = '';
					}
					if (this.popupChatDialogUsers[item.getAttribute('data-userId')])
						delete this.popupChatDialogUsers[item.getAttribute('data-userId')];
					else
						this.popupChatDialogUsers[item.getAttribute('data-userId')] = {'id': item.getAttribute('data-userId'), 'date': new Date()};

					this.redrawChatDialogDest();

					if (this.popupChatDialogDestType == 'CHAT_CREATE')
					{
						if (this.popupChatDialog)
							this.popupChatDialog.close();
					}
				}
				else
				{
					var item = BX.findChildByClassName(this.popupChatDialogContactListElements, "bx-messenger-chatlist-search-button");
					if (item)
					{
						this.popupChatDialogContactListElements.appendChild(BX.create("div", {
							props : { className: "bx-messenger-cl-item-search"},
							html : BX.message('IM_M_CL_SEARCH')
						}));
						BX.remove(item);

						this.BXIM.messenger.realSearch = true;

						BX.MessengerCommon.contactListRealSearch(this.popupChatDialogContactListSearch.value, BX.delegate(function(){
							BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': (this.popupChatDialogDestType == 'MENTION'), 'exceptUsers': this.popupChatDialogExceptUsers, timeout: 100, 'callback': {'empty': this.popupChatDialogEmptyCallback}});
						}, this));

						return true;
					}
				}

				if (this.BXIM.messenger.realSearch)
				{
					this.BXIM.messenger.realSearchFound = true;
				}
			}

			this.popupChatDialogContactListSearchLastText = this.popupChatDialogContactListSearch.value;

			if (this.BXIM.messenger.realSearch)
			{
				this.BXIM.messenger.realSearchFound = this.popupChatDialogContactListSearch.value.length < 3;
			}

			BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': (this.popupChatDialogDestType == 'MENTION'), 'exceptUsers': this.popupChatDialogExceptUsers, timeout: 100, 'callback': {'empty': this.popupChatDialogEmptyCallback}});
			BX.MessengerCommon.contactListRealSearch(this.popupChatDialogContactListSearch.value, BX.delegate(function(){
				BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': (this.popupChatDialogDestType == 'MENTION'), 'exceptUsers': this.popupChatDialogExceptUsers, timeout: 100, 'callback': {'empty': this.popupChatDialogEmptyCallback}});
			}, this));

			if (this.popupChatDialog)
				this.popupChatDialog.adjustPosition();
		}, this));

		BX.bindDelegate(this.popupChatDialogDestElements, "click", {className: 'bx-messenger-dest-del'}, BX.delegate(function() {
			delete this.popupChatDialogUsers[BX.proxy_context.getAttribute('data-userId')];
			if (BX.MessengerCommon.countObject(this.popupChatDialogUsers) < this.popupChatDialogMaxChatUsers)
				BX.show(this.popupChatDialogContactListSearch);
			this.redrawChatDialogDest();
		}, this));

		BX.bindDelegate(this.popupChatDialogContactListElements, "click", {className: 'bx-messenger-chatlist-search-button'}, BX.delegate(function() {
			this.popupChatDialogContactListElements.appendChild(BX.create("div", {
				props : { className: "bx-messenger-cl-item-search"},
				html : BX.message('IM_M_CL_SEARCH')
			}));
			BX.remove(BX.proxy_context.parentNode);

			this.BXIM.messenger.realSearch = true;

			BX.MessengerCommon.contactListRealSearch(this.popupChatDialogContactListSearch.value, BX.delegate(function(){
				BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': (this.popupChatDialogDestType == 'MENTION'), 'exceptUsers': this.popupChatDialogExceptUsers, timeout: 100, 'callback': {'empty': this.popupChatDialogEmptyCallback}});
			}, this));
		}, this));
	}

	BX.bindDelegate(this.popupChatDialogContactListElements, "click", {className: 'bx-messenger-cl-item'}, BX.delegate(function(e) {
		if (this.popupChatDialogContactListSearch.value != '')
		{
			this.popupChatDialogContactListSearch.value = '';
			if (this.popupChatDialogDestType != 'MENTION' && this.popupChatDialogDestType != 'CHAT_CREATE')
			{
				BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {'viewOffline': true, 'viewChat': false, 'viewOpenChat': false, 'exceptUsers': this.popupChatDialogExceptUsers});
			}
		}

		if (this.popupChatDialogUsers[BX.proxy_context.getAttribute('data-userId')])
		{
			delete this.popupChatDialogUsers[BX.proxy_context.getAttribute('data-userId')];
		}
		else
		{
			if (BX.MessengerCommon.countObject(this.popupChatDialogUsers) == this.popupChatDialogMaxChatUsers)
				return false;

			this.popupChatDialogUsers[BX.proxy_context.getAttribute('data-userId')] = {'id': BX.proxy_context.getAttribute('data-userId'), 'date': new Date()};
		}

		if (this.popupChatDialogDestType == 'MENTION')
		{
			var replaceText = bindElement.value.substr(0, bindElement.selectionEnd);
			replaceText = replaceText.substr(replaceText.lastIndexOf(params.delimiter), bindElement.selectionEnd-replaceText.lastIndexOf(params.delimiter));

			bindElement.value = bindElement.value.replace(replaceText, BX.proxy_context.getAttribute('data-name')+' ');
			BX.MessengerCommon.addMentionList(this.currentTab, BX.proxy_context.getAttribute('data-name'), BX.proxy_context.getAttribute('data-userId'));

			if (this.popupChatDialog)
				this.popupChatDialog.close();
		}
		else
		{
			this.redrawChatDialogDest();
		}

		if (this.popupChatDialogDestType == 'CHAT_CREATE')
		{
			if (this.popupChatDialog)
				this.popupChatDialog.close();
		}

		return BX.PreventDefault(e);
	}, this));
};

BX.Messenger.prototype.redrawChatDialogDest = function()
{
	var content = '';
	var count = 0;
	var userId = 0;

	var arMentionSort = BX.util.objectSort(this.popupChatDialogUsers, 'date', 'asc');
	for (var i = 0; i < arMentionSort.length; i++)
	{
		userId = arMentionSort[i].id.toString();
		var isStructure = userId.substr(0, 9) == 'structure';
		var isExtranet = false;
		var blockName = '';
		if (isStructure)
		{
			var structureId = userId.substr(9);
			blockName = this.groups[structureId].name.split(' / ')[0];
		}
		else
		{
			blockName = this.users[userId].name;
			isExtranet = this.users[userId].extranet;
		}

		count++;
		content += '<span class="bx-messenger-dest-block'+(isExtranet? ' bx-messenger-dest-block-extranet': '')+(isStructure? ' bx-messenger-dest-block-structure': '')+'">'+
						'<span class="bx-messenger-dest-text">'+blockName+'</span>'+
					'<span class="bx-messenger-dest-del" data-userId="'+userId+'"></span></span>';
	}

	this.popupChatDialogDestElements.innerHTML = content;
	this.popupChatDialogDestElements.parentNode.scrollTop = this.popupChatDialogDestElements.parentNode.offsetHeight;

	if (this.popupChatDialogDestType != 'CHAT_CREATE')
	{
		if (BX.util.even(count))
			BX.addClass(this.popupChatDialogDestElements.parentNode, 'bx-messenger-popup-newchat-dest-even');
		else
			BX.removeClass(this.popupChatDialogDestElements.parentNode, 'bx-messenger-popup-newchat-dest-even');
	}

	var currentCount = BX.MessengerCommon.countObject(this.popupChatDialogUsers);
	if (currentCount >= this.popupChatDialogMaxChatUsers)
	{
		BX.addClass(this.popupChatDialogContactListSearch, 'bx-messenger-hide');

		if (this.popupChatDialogDestType == 'CHAT_CREATE')
		{
			if (this.popupChatDialog)
				this.popupChatDialog.close();
			this.popupCreateChatTextarea.focus();
		}
	}
	else
	{
		BX.removeClass(this.popupChatDialogContactListSearch, 'bx-messenger-hide');
		if (this.popupChatDialog)
			this.popupChatDialog.adjustPosition();

		this.popupChatDialogContactListSearch.focus();
	}

	if (currentCount)
	{
		BX.removeClass(this.popupChatDialogContactListSearch, 'bx-messenger-input-dest-empty');
	}
	else
	{
		BX.addClass(this.popupChatDialogContactListSearch, 'bx-messenger-input-dest-empty');
	}
};

BX.Messenger.prototype.sendRequestChatDialog = function(params)
{
	if (this.popupChatDialogSendBlock)
		return false;

	if (typeof(params) != 'object')
		return false;

	params.type = params.type == 'open'? 'open': 'chat';
	params.users = params.users || [];
	params.message = params.message || "";
	params.title = params.title || "";

	var users = [];
	for (var i = 0; i < params.users.length; i++)
	{
		if (params.users[i].toString().substr(0, 9) == 'structure')
		{
			params.users[i] = parseInt(params.users[i].toString().substr(9));
			if (params.users[i] < 0)
				continue;

			params.users[i] = 'structure'+params.users[i];
		}
		else if (params.users[i].toString().substr(0, 7) == 'network')
		{
		}
		else
		{
			params.users[i] = parseInt(params.users[i]);
			if (params.users[i] < 0)
				continue;
		}

		if (users.indexOf && users.indexOf(params.users[i]) >= 0)
			continue;

		if (params.users[i] == this.BXIM.userId)
			continue;

		if (params.chatId && this.userInChat[params.chatId].indexOf && this.userInChat[params.chatId].indexOf(params.users[i].toString()) >= 0)
			continue;

		users.push(params.users[i]);
	}
	params.users = users;

	var error = '';
	if (params.action == 'CHAT_CREATE' && params.type == 'chat' && params.users.length < 1)
	{
		error = BX.message('IM_M_CHAT_ERROR_1');
	}
	if (params.action == 'CHAT_ADD' && params.type == 'chat' && params.users.length <= 1)
	{
		if (params.users[0] && this.users[params.users[0]])
		{
			this.openMessenger(params.users[0]);
			if (this.popupChatDialog != null)
				this.popupChatDialog.close();

			return false;
		}
		else
		{
			error = BX.message('IM_M_CHAT_ERROR_1');
		}
	}
	else if (params.action == 'CHAT_EXTEND' && params.users.length == 0)
	{
		if (this.popupChatDialog != null)
			this.popupChatDialog.close();

		return false;
	}
	if (params.action == 'CHAT_CREATE')
	{
		params.action = 'CHAT_ADD';
	}

	if (error != "")
	{
		this.BXIM.openConfirm(error);
		return false;
	}

	this.popupChatDialogSendBlock = true;
	if (this.popupChatDialog != null)
		this.popupChatDialog.buttons[0].setClassName('popup-window-button-disable');

	var data = false;
	if (params.action == 'CHAT_ADD')
	{
		data = {'IM_CHAT_ADD' : 'Y', 'TYPE' : params.type, 'TITLE' : params.title, 'MESSAGE' : params.message, 'USERS' : JSON.stringify(params.users), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
	}
	else if (params.action == 'CHAT_EXTEND')
	{
		data = {'IM_CHAT_EXTEND' : 'Y', 'CHAT_ID' : params.chatId, 'HISTORY': (this.popupChatDialogShowHistory && this.popupChatDialogShowHistory.checked? 'Y':'N'), 'USERS' : JSON.stringify(params.users), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
		this.BXIM.options.chatExtendShowHistory = this.popupChatDialogShowHistory && this.popupChatDialogShowHistory.checked;
		BXIM.setLocalConfig('mcesh', this.BXIM.options.chatExtendShowHistory);
	}

	if (!data)
		return false;

	BX.ajax({
		url: this.BXIM.pathToAjax+'?'+params.action+'&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: data,
		onsuccess: BX.delegate(function(data){
			this.popupChatDialogSendBlock = false;
			if (this.popupChatDialog != null)
				this.popupChatDialog.buttons[0].setClassName('popup-window-button-accept');
			if (data.ERROR == '')
			{
				if (!this.BXIM.ppServerStatus)
					BX.PULL.updateState(true);

				if (data.CHAT_ID)
				{
					if (this.BXIM.ppServerStatus && this.currentTab != 'chat'+data.CHAT_ID)
					{
						this.openMessenger('chat'+data.CHAT_ID);
					}
					else if (!this.BXIM.ppServerStatus && this.currentTab != 'chat'+data.CHAT_ID)
					{
						setTimeout( BX.delegate(function(){
							this.openMessenger('chat'+data.CHAT_ID);
						}, this), 500);
					}
				}
				this.popupChatDialogSendBlock = false;
				if (this.popupChatDialog != null)
					this.popupChatDialog.close();
			}
			else
			{
				this.BXIM.openConfirm(data.ERROR);
			}
		}, this)
	});
};

/* CL */
BX.Messenger.prototype.openContactList = function()
{
	return this.openMessenger();
};

BX.Messenger.prototype.openPopupMenu = function(bind, type, setAngle, params)
{
	params = params? params: {};

	var destroySmilesPopup = params.closeSmiles === false? false: true;
	if (destroySmilesPopup && this.popupSmileMenu != null)
		this.popupSmileMenu.destroy();

	this.closePopupFileMenu();

	if (this.popupPopupMenu != null)
	{
		this.popupPopupMenu.destroy();
		return false;
	}
	var offsetTop = 0;
	var offsetLeft = 13;
	var menuItems = [];
	var bindOptions = {};
	var angleOptions = {offset: 4};
	this.popupPopupMenuStyle = "";

	if (params.offsetTop)
		offsetTop = params.offsetTop;

	if (params.offsetLeft)
		offsetLeft = params.offsetLeft;

	if (params.anglePosition)
		angleOptions.position = params.anglePosition;

	if (type == 'createChat')
	{
		bindOptions = {position: "bottom"};
		if (params.openDesktop)
		{
			menuItems = [
				{icon: 'bx-messenger-cc-private', text: BX.message("IM_CL_PRIVATE_CHAT"), onclick: BX.delegate(function(){
					BX.desktopUtils.goToBx("bx://chat/create/private"); this.closeMenuPopup();
				}, this)},
				{icon: 'bx-messenger-cc-chat', text: BX.message("IM_CL_CHAT_2"), onclick: BX.delegate(function(){
					BX.desktopUtils.goToBx("bx://chat/create/chat"); this.closeMenuPopup();
				}, this)},
				this.BXIM.userExtranet || !this.openChatEnable? null: {icon: 'bx-messenger-cc-open', text: BX.message("IM_CL_OPEN_CHAT"), onclick: BX.delegate(function(){
					BX.desktopUtils.goToBx("bx://chat/create/open"); this.closeMenuPopup();
				}, this)}
			];
		}
		else if (params.openMessenger)
		{
			menuItems = [
				{icon: 'bx-messenger-cc-private', text: BX.message("IM_CL_PRIVATE_CHAT"), onclick: BX.delegate(function(){
					this.openMessenger();this.openChatCreateForm('private'); this.closeMenuPopup();
				}, this)},
				{icon: 'bx-messenger-cc-chat', text: BX.message("IM_CL_CHAT_2"), onclick: BX.delegate(function(){
					this.openMessenger();this.openChatCreateForm('chat'); this.closeMenuPopup();
				}, this)},
				this.BXIM.userExtranet || !this.openChatEnable? null: {icon: 'bx-messenger-cc-open', text: BX.message("IM_CL_OPEN_CHAT"), onclick: BX.delegate(function(){
					this.openMessenger();this.openChatCreateForm('open'); this.closeMenuPopup();
				}, this)}
			];
		}
		else
		{
			menuItems = [
				{icon: 'bx-messenger-cc-private', text: BX.message("IM_CL_PRIVATE_CHAT"), onclick: BX.delegate(function(){
					this.openChatCreateForm('private'); this.closeMenuPopup();
				}, this)},
				{icon: 'bx-messenger-cc-chat', text: BX.message("IM_CL_CHAT_2"), onclick: BX.delegate(function(){
					this.openChatCreateForm('chat'); this.closeMenuPopup();
				}, this)},
				this.BXIM.userExtranet || !this.openChatEnable? null: {icon: 'bx-messenger-cc-open', text: BX.message("IM_CL_OPEN_CHAT"), onclick: BX.delegate(function(){
					this.openChatCreateForm('open'); this.closeMenuPopup();
				}, this)}
			];
		}
	}
	else if (type == 'pathMenu')
	{
		var chatId = this.getChatId();
		var pathOptions = BX.MessengerCommon.getEntityTypePath(chatId);

		offsetTop = 5;
		offsetLeft = 14;
		menuItems = [
			{icon: 'bx-messenger-menu-crm', text: pathOptions['TITLE'], href: pathOptions['PATH'], target: '_blank', onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)},
			{icon: 'bx-messenger-menu-history-2', text: BX.message("IM_M_HISTORY"), onclick: BX.delegate(function(){ this.openHistory(this.currentTab); this.closeMenuPopup(); }, this)}
		];
	}
	else if (type == 'openLinesMenu')
	{
		var chatId = this.getChatId();
		var isOwner = this.chat[chatId].owner == this.BXIM.userId;
		var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);

		offsetTop = 5;
		offsetLeft = 14;
		menuItems = [
			isOwner? {icon: 'bx-messenger-menu-pause', text: BX.message(session.pin == "Y"? "IM_M_OL_PAUSE_OFF": "IM_M_OL_PAUSE_ON"), onclick: BX.delegate(function(){  this.linesTogglePinMode();  this.closeMenuPopup(); }, this)}: null,
			isOwner && session.crm != 'Y'? {icon: 'bx-messenger-menu-crm', text: BX.message("IM_M_OL_ADD_LEAD"), onclick: BX.delegate(function(){  this.linesCreateLead(); this.closeMenuPopup(); }, this)}: null,
			session.crmLink? {icon: 'bx-messenger-menu-crm', text: BX.message('IM_M_OL_GOTO_CRM'), href: session.crmLink, target: '_blank', onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)}: null,
			//isOwner? {icon: 'bx-messenger-menu-close', text: BX.message("IM_M_OL_CLOSE"), onclick: BX.delegate(function(){  this.linesCloseDialog();  this.closeMenuPopup(); }, this)}: null,
			{icon: 'bx-messenger-menu-history-2', text: BX.message("IM_M_HISTORY"), onclick: BX.delegate(function(){ this.openHistory(this.currentTab); this.closeMenuPopup(); }, this)},
			session.id? {separator: true}:null,
			!isOwner && session.id? {icon: 'bx-messenger-menu-intercept', text: BX.message("IM_M_OL_INTERCEPT"), onclick: BX.delegate(function(){  this.linesInterceptSession();  this.closeMenuPopup(); }, this)}: null,
			isOwner && session.id? {icon: 'bx-messenger-menu-spam', text: BX.message("IM_M_OL_FORCE_CLOSE"), onclick: BX.delegate(function(){  this.linesMarkAsSpam();  this.closeMenuPopup(); }, this)}: null
		];
	}
	else if (type == 'textareaAppsMenu')
	{
		menuItems = [];
		for (var i = 0; i < this.textareaIcon.length; i++)
		{
			if (!this.textareaIcon[i] || this.BXIM.userExtranet && !this.textareaIcon[i]['extranet'] || this.textareaIcon[i].hidden)
			{
				continue;
			}

			if (this.desktop.ready() && !this.desktop.enableInVersion(39) && this.textareaIcon[i]['iframe'])
			{
				if (BXDesktopSystem.GetProperty('versionParts').join('.') != '5.0.32.38') // TODO remove this
				{
					continue;
				}
			}

			if (!this.textareaIcon[i]['title'] && !this.textareaIcon[i]['url'])
			{
				continue;
			}

			if (this.textareaIcon[i]['url'])
			{
				continue;
			}
			var title = this.textareaIcon[i]['description']? this.textareaIcon[i]['description']: this.textareaIcon[i]['title'];

			menuItems.push({
				text: BX.util.htmlspecialchars(this.textareaIcon[i]['title']),
				onclick: BX.delegate(function(e){
					this.textareaIconClick();

					return BX.PreventDefault(e);
				}, this),
				attrs : {
					title: title,
					"data-context": this.textareaIcon[i]['context'],
					"data-code": this.textareaIcon[i]['code'],
					"data-id": this.textareaIcon[i]['id']
				},
			});
		}

		offsetTop = 5;
		offsetLeft = 14;
	}
	else if (type == 'status')
	{
		offsetLeft = 9;
		bindOptions = {position: "top"};
		menuItems = [
			{icon: 'bx-messenger-status-online', text: BX.message("IM_STATUS_ONLINE"), onclick: BX.delegate(function(){ this.setStatus('online'); this.closeMenuPopup(); }, this)},
			{icon: 'bx-messenger-status-away', text: BX.message("IM_STATUS_AWAY"), onclick: BX.delegate(function(){ this.setStatus('away'); this.closeMenuPopup(); }, this)},
			{icon: 'bx-messenger-status-dnd', text: BX.message("IM_STATUS_DND"), onclick: BX.delegate(function(){ this.setStatus('dnd'); this.closeMenuPopup(); }, this)}
		];
	}
	else if (type == 'iconMenu')
	{
		var iconId = bind.getAttribute('data-id');
		menuItems = [
			{text: BX.message("IM_MENU_DELETE"), onclick: BX.delegate(function(e){
				this.removeRecentSmile(iconId);
				BX.remove(bind);
				this.popupPopupMenu.close();
				return BX.PreventDefault(e);
			}, this)},
		];
	}
	else if (type == 'notifyDelete')
	{
		var notifyId = bind.getAttribute('data-notifyId');
		var settingName = this.notify.notify[notifyId].settingName;
		var blockNotifyText = typeof (this.BXIM.settingsNotifyBlocked[settingName]) == 'undefined'? BX.message("IM_NOTIFY_DELETE_2"): BX.message("IM_NOTIFY_DELETE_3");
		if (typeof(params.applyToDom) != 'undefined')
		{
			bind = params.applyToDom;
		}
		menuItems = [
			this.notify.unreadNotify[notifyId]? {text: BX.message("IM_MENU_READ"), onclick: BX.delegate(function(){ this.notify.viewNotify(notifyId, true); this.closeMenuPopup(); }, this)}: null,
			!this.notify.unreadNotify[notifyId]? {text: BX.message("IM_MENU_UNREAD"), onclick: BX.delegate(function(){ this.notify.viewNotify(notifyId, false); this.closeMenuPopup(); }, this)}: null,
			{text: BX.message("IM_NOTIFY_DELETE_1"), onclick: BX.delegate(function(){ this.notify.deleteNotify(notifyId); this.closeMenuPopup(); }, this)},
			{text: blockNotifyText, onclick: BX.delegate(function(){ this.notify.blockNotifyType(settingName); this.closeMenuPopup(); }, this)}
		];
	}
	else if (type == 'callMenu')
	{
		offsetTop = 2;
		offsetLeft = 20;

		menuItems = [
			{icon: 'bx-messenger-menu-call-video', text: BX.message('IM_M_CALL_VIDEO'), onclick: BX.delegate(function(){ this.webrtc.callInvite(this.currentTab, true); this.closeMenuPopup(); }, this)},
			{icon: 'bx-messenger-menu-call-voice', text: BX.message('IM_M_CALL_VOICE'), onclick: BX.delegate(function(){ this.BXIM.callTo(this.currentTab, false); this.closeMenuPopup(); }, this)},
		];

		if (this.BXIM.webrtc.callServerEnabled)
		{
			menuItems.push({separator: true});
			menuItems.push(
				{icon: 'bx-messenger-menu-call-video', text: BX.message('IM_CALL_EXPERIMENTAL_BUTTON'), onclick: BX.delegate(function(){ this.BXIM.webrtc.callInviteExperimental(this.currentTab, true); this.closeMenuPopup(); }, this)}
			);
			menuItems.push(
				{icon: 'bx-messenger-menu-call-video', text: BX.message('IM_CALL_MIC_TEST_BUTTON'), onclick: BX.delegate(function(){ this.BXIM.webrtc.startMicTest(); this.closeMenuPopup(); }, this)}
			);

		}

		if (this.BXIM.webrtc.phoneCanCallUserNumber && !this.openChatFlag && this.phones[this.currentTab])
		{
			menuItems.push({separator: true});

			if (this.phones[this.currentTab].PERSONAL_MOBILE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_MOBILE'), phone: BX.util.htmlspecialchars(this.phones[this.currentTab].PERSONAL_MOBILE), onclick: BX.delegate(function(){ this.BXIM.phoneTo(this.phones[this.currentTab].PERSONAL_MOBILE); this.closeMenuPopup(); }, this)}
				);
			}

			if (this.phones[this.currentTab].PERSONAL_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_PHONE'), phone: BX.util.htmlspecialchars(this.phones[this.currentTab].PERSONAL_PHONE), onclick: BX.delegate(function(){ this.BXIM.phoneTo(this.phones[this.currentTab].PERSONAL_PHONE); this.closeMenuPopup(); }, this)}
				);
			}

			if (this.phones[this.currentTab].WORK_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_WORK_PHONE'), phone: BX.util.htmlspecialchars(this.phones[this.currentTab].WORK_PHONE), onclick: BX.delegate(function(){ this.BXIM.phoneTo(this.phones[this.currentTab].WORK_PHONE); this.closeMenuPopup(); }, this)}
				);
			}

			if (this.phones[this.currentTab].INNER_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_INNER_PHONE'), phone: BX.util.htmlspecialchars(this.phones[this.currentTab].INNER_PHONE), onclick: BX.delegate(function(){ this.BXIM.phoneTo(this.phones[this.currentTab].INNER_PHONE); this.closeMenuPopup(); }, this)}
				);
			}
		}
	}
	else if (type == 'callPhoneMenu')
	{
		offsetTop = 2;
		offsetLeft = 25;

		menuItems = [
			{icon: 'bx-messenger-menu-call-'+(params.video? 'video': 'voice'), text: '<b>'+BX.message('IM_M_CALL_BTN_RECALL_3')+'</b>', onclick: BX.delegate(function(){ this.webrtc.callInvite(params.userId, params.video) }, this)}
		];
		menuItems.push({separator: true});
		if (this.phones[params.userId] && this.BXIM.webrtc.phoneCanCallUserNumber)
		{
			menuItems.push({separator: true});

			if (this.phones[params.userId].PERSONAL_MOBILE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_MOBILE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].PERSONAL_MOBILE), onclick: BX.delegate(function(){
						this.BXIM.phoneTo(this.phones[params.userId].PERSONAL_MOBILE);
						this.closeMenuPopup();
					}, this)}
				);
			}

			if (this.phones[params.userId].PERSONAL_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_PHONE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].PERSONAL_PHONE), onclick: BX.delegate(function(){
						this.BXIM.phoneTo(this.phones[params.userId].PERSONAL_PHONE);
						this.closeMenuPopup();
					}, this)}
				);
			}

			if (this.phones[params.userId].WORK_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_WORK_PHONE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].WORK_PHONE), onclick: BX.delegate(function(){
						this.BXIM.phoneTo(this.phones[params.userId].WORK_PHONE);
						this.closeMenuPopup();
					}, this)}
				);
			}
		}
	}
	else if (type == 'callTransferMenu')
	{
		offsetTop = 2;
		offsetLeft = 25;
		params.onSelect = BX.type.isFunction(params.onSelect) ? params.onSelect : BX.DoNothing;

		menuItems = [
			{icon: 'bx-messenger-menu-call-voice', text: BX.message('IM_PHONE_INNER_CALL'), onclick: BX.delegate(function(){
				this.closeMenuPopup();
				params.onSelect({
					type: 'user',
					userId: params.userId
				});
			}, this)}
		];

		if (this.phones[params.userId])
		{
			menuItems.push({separator: true});

			if (this.phones[params.userId].PERSONAL_MOBILE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_MOBILE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].PERSONAL_MOBILE), onclick: BX.delegate(function(){
						this.closeMenuPopup();
						params.onSelect({
							type: 'phone',
							userId: params.userId,
							phone: this.phones[params.userId].PERSONAL_MOBILE
						});
					}, this)}
				);
			}

			if (this.phones[params.userId].PERSONAL_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_PERSONAL_PHONE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].PERSONAL_PHONE), onclick: BX.delegate(function(){
						this.closeMenuPopup();
						params.onSelect({
							type: 'phone',
							userId: params.userId,
							phone: this.phones[params.userId].PERSONAL_PHONE
						});
					}, this)}
				);
			}

			if (this.phones[params.userId].WORK_PHONE)
			{
				menuItems.push(
					{type: 'call', text: BX.message('IM_PHONE_WORK_PHONE'), phone: BX.util.htmlspecialchars(this.phones[params.userId].WORK_PHONE), onclick: BX.delegate(function(){
						this.closeMenuPopup();
						params.onSelect({
							type: 'phone',
							userId: params.userId,
							phone: this.phones[params.userId].WORK_PHONE
						});
					}, this)}
				);
			}
		}
	}
	else if (type == 'callContextMenu')
	{
		var callData = BX.MessengerCommon.phoneGetCallFields(this.getChatId());
		menuItems = [
			{icon: 'bx-messenger-menu-history-2', text: BX.message("IM_M_HISTORY"), onclick: BX.delegate(function(){ this.openHistory(this.currentTab); this.closeMenuPopup(); }, this)},
			callData.crm ? {icon: 'bx-messenger-menu-crm', text: BX.message('IM_M_OL_GOTO_CRM'), href: callData.crmShowUrl, target: '_blank', onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)} : null
		];
	}
	else if (type == 'chatUser')
	{
		var userId = bind.getAttribute('data-userId');
		var chatId = this.getChatId();
		var isOwner = this.chat[chatId].owner == this.BXIM.userId;

		if (this.users[this.BXIM.userId].connector)
		{
			return false;
		}
		if (userId == this.BXIM.userId)
		{
			var hideExit = BX.MessengerCommon.checkRestriction(chatId, 'LEAVE') || this.chat[chatId].type == 'lines' && (this.chat[chatId].owner == 0 || this.chat[chatId].owner == this.BXIM.userId);
			menuItems = [
				{icon: 'bx-messenger-menu-profile', text: BX.message('IM_M_OPEN_PROFILE'), href: this.BXIM.path.profile, onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)},
				hideExit? null: {icon: 'bx-messenger-menu-chat-exit', text: BX.message('IM_M_CHAT_EXIT'), onclick: BX.delegate(function(){ BX.MessengerCommon.leaveFromChat(chatId); this.closeMenuPopup();}, this)}
			];
		}
		else if (this.chat[chatId].type == 'lines' && this.users[userId].connector)
		{
			var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);
			menuItems = [
				{icon: 'bx-messenger-menu-chat-put', text: BX.message('IM_M_CHAT_PUT'), onclick: BX.delegate(function(){ this.insertTextareaText(this.popupMessengerTextarea, ' '+BX.util.htmlspecialcharsback(this.users[userId].name)+' ', false); BX.MessengerCommon.addMentionList(this.currentTab, BX.util.htmlspecialcharsback(this.users[userId].name), userId); this.popupMessengerTextarea.focus(); this.closeMenuPopup(); }, this)},
				isOwner && session.crm != 'Y'? {icon: 'bx-messenger-menu-crm', text: BX.message("IM_M_OL_ADD_LEAD"), onclick: BX.delegate(function(){  this.linesCreateLead(); this.closeMenuPopup(); }, this)}: null,
				session.crmLink? {icon: 'bx-messenger-menu-crm', text: BX.message('IM_M_OL_GOTO_CRM'), href: session.crmLink, target: '_blank', onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)}: null,
			];
		}
		else
		{
			var canKick = !BX.MessengerCommon.checkRestriction(chatId, 'LEAVE') && this.chat[chatId].owner == this.BXIM.userId;
			var userInChat = true;
			if (chatId != this.generalChatId)
			{
				userInChat = BX.MessengerCommon.userInChat(chatId);
			}
			else if (!this.canSendMessageGeneralChat || this.BXIM.settings.generalNotify)
			{
				userInChat = false;
			}
			if (canKick && this.chat[chatId].type == 'open')
			{
				canKick = this.users[userId].extranet? true: false;
			}
			menuItems = [
				!userInChat? null: {icon: 'bx-messenger-menu-chat-put', text: BX.message('IM_M_CHAT_PUT'), onclick: BX.delegate(function(){ this.insertTextareaText(this.popupMessengerTextarea, ' '+BX.util.htmlspecialcharsback(this.users[userId].name)+' ', false); BX.MessengerCommon.addMentionList(this.currentTab, BX.util.htmlspecialcharsback(this.users[userId].name), userId); this.popupMessengerTextarea.focus(); this.closeMenuPopup(); }, this)},
				{icon: 'bx-messenger-menu-write', text: BX.message('IM_M_WRITE_MESSAGE'), onclick: BX.delegate(function(){ this.openMessenger(userId); this.closeMenuPopup(); }, this)},
				(!this.webrtc.callSupport(userId, this) || this.webrtc.callInit)? null: {icon: 'bx-messenger-menu-video', text: BX.message('IM_M_CALL_VIDEO'), onclick: BX.delegate(function(){ this.BXIM.callTo(userId, true); this.closeMenuPopup(); }, this)},
				{icon: 'bx-messenger-menu-history', text: BX.message('IM_M_OPEN_HISTORY'), onclick: BX.delegate(function(){ this.openHistory(userId); this.closeMenuPopup();}, this)},
				{icon: 'bx-messenger-menu-profile', text: BX.message('IM_M_OPEN_PROFILE'), href: this.users[userId].profile, onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)},
				canKick? {icon: 'bx-messenger-menu-chat-exit', text: BX.message('IM_M_CHAT_KICK'), onclick: BX.delegate(function(){ this.kickFromChat(chatId, userId); this.closeMenuPopup();}, this)}: {}
			];
		}
	}
	else if (type == 'contactList')
	{
		offsetTop = 2;
		offsetLeft = 25;
		var userId = bind.getAttribute('data-userId');
		var userIsChat = bind.getAttribute('data-userIsChat') === true || bind.getAttribute('data-userIsChat') == "true";
		var dialogIsPinned = bind.getAttribute('data-isPinned') === true || bind.getAttribute('data-isPinned') == "true";

		if (this.recentList || userIsChat)
		{
			var isOpenlines = this.chat[userId.toString().substr(4)] && this.chat[userId.toString().substr(4)].type == 'lines';

			var chatMuteText = BX.message('IM_M_CHAT_MUTE_OFF');
			var muteEnable = false;
			if (userIsChat)
			{
				muteEnable = true;
			}
			else if (this.users[userId].extranet)
			{
				muteEnable = true;
			}

			if (muteEnable && this.muteButtonStatus(userId))
			{
				chatMuteText = BX.message('IM_M_CHAT_MUTE_ON');
			}

			var dialogPinnedText = BX.message(!dialogIsPinned? 'IM_M_OL_PIN_ON': 'IM_M_OL_PIN_OFF');

			hideItem = !BX.MessengerCommon.userInChat(userId.toString().substr(4));
			menuItems = [
				isOpenlines? null: {icon: 'bx-messenger-menu-write', text: BX.message('IM_M_WRITE_MESSAGE'), onclick: BX.delegate(function(){ this.openMessenger(userId); this.closeMenuPopup(); }, this)},
				isOpenlines? null: {icon: 'bx-messenger-menu-pin', text: dialogPinnedText, onclick: BX.delegate(function(){ BX.MessengerCommon.pinDialog(userId, !dialogIsPinned); this.closeMenuPopup(); }, this)},
				!isOpenlines && !hideItem && muteEnable ? {icon: 'bx-messenger-menu-chat-mute', text: chatMuteText, onclick: BX.delegate(function(){ BX.MessengerCommon.muteMessageChat(userId); this.closeMenuPopup();}, this)}: {},
				isOpenlines || (!this.webrtc.callSupport(userId, this) || this.webrtc.callInit) || (userIsChat && (this.chat[userId.toString().substr(4)].type == 'call' || this.chat[userId.toString().substr(4)].type == 'lines'))? null: {icon: 'bx-messenger-menu-video', text: BX.message('IM_M_CALL_VIDEO'), onclick: BX.delegate(function(){ this.BXIM.callTo(userId, true); this.closeMenuPopup(); }, this)},
				hideItem && !userIsChat? null: {icon: 'bx-messenger-menu-history', text: BX.message('IM_M_OPEN_HISTORY'), onclick: BX.delegate(function(){ this.openHistory(userId); this.closeMenuPopup();}, this)},
				!userIsChat? {icon: 'bx-messenger-menu-profile', text: BX.message('IM_M_OPEN_PROFILE'), href: this.users[userId].profile, onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)}: {},
				!hideItem && userIsChat && this.chat[userId.toString().substr(4)].type != 'call' && !BX.MessengerCommon.checkRestriction(userId.toString().substr(4), 'RENAME') ? {icon: 'bx-messenger-menu-chat-rename', text: BX.message('IM_M_CHAT_RENAME'), onclick: BX.delegate(function(){ if (this.currentTab != userId) { this.openMessenger(userId); } else { this.renameChatDialog(); }   this.closeMenuPopup();}, this)}: {},
				isOpenlines || userIsChat && !this.recentList? null: {icon: 'bx-messenger-menu-hide-'+(userIsChat? 'chat': 'dialog'), text: BX.message('IM_M_HIDE_'+(userIsChat? (this.chat[userId.toString().substr(4)].type == 'call'? 'CALL': 'CHAT'): 'DIALOG')), onclick: BX.delegate(function(){ BX.MessengerCommon.recentListHide(userId); this.closeMenuPopup();}, this)},
				!hideItem && userIsChat && this.chat[userId.toString().substr(4)].type != 'call' && this.chat[userId.toString().substr(4)].type != 'lines' && !BX.MessengerCommon.checkRestriction(userId.toString().substr(4), 'LEAVE')? {icon: 'bx-messenger-menu-chat-exit', text: BX.message('IM_M_CHAT_EXIT'), onclick: BX.delegate(function(){ BX.MessengerCommon.leaveFromChat(userId.toString().substr(4)); this.closeMenuPopup();}, this)}: {}
			];
		}
		else
		{
			menuItems = [
				{icon: 'bx-messenger-menu-write', text: BX.message('IM_M_WRITE_MESSAGE'), onclick: BX.delegate(function(){ this.openMessenger(userId); this.closeMenuPopup(); }, this)},
				(!userIsChat && (!this.webrtc.callSupport(userId, this) || this.webrtc.callInit))? null: {icon: 'bx-messenger-menu-video', text: BX.message('IM_M_CALL_VIDEO'), onclick: BX.delegate(function(){ this.BXIM.callTo(userId, true); this.closeMenuPopup(); }, this)},
				{icon: 'bx-messenger-menu-history', text: BX.message('IM_M_OPEN_HISTORY'), onclick: BX.delegate(function(){ this.openHistory(userId); this.closeMenuPopup();}, this)},
				{icon: 'bx-messenger-menu-profile', text: BX.message('IM_M_OPEN_PROFILE'), href: this.users[userId].profile, onclick: BX.delegate(function(){ this.closeMenuPopup(); }, this)}
			];
		}
	}
	else if (type == 'dialogContext' || type == 'dialogMenu')
	{
		var messages = [];
		if (type == 'dialogMenu')
		{
			this.popupPopupMenuStyle = 'bx-messenger-content-item-menu-hover';
			angleOptions = {offset: 12};
			if (bind.parentNode.parentNode)
			{
				messages = [BX('im-message-'+bind.parentNode.parentNode.getAttribute('data-blockmessageid'))];
			}
		}
		else
		{
			var foundTarget = false;
			if (bind.target.className.indexOf("bx-messenger-file") >= 0 || bind.target.className.indexOf("bx-bxu-proper-canvas") >= 0)
			{
				var fileBox = BX.findParent(bind.target, {className : "bx-messenger-file-box"});
				if (fileBox && fileBox.previousSibling)
				{
					foundTarget = true;
					messages = [fileBox.previousSibling];
				}
			}
			if (!foundTarget)
			{
				if (BX.hasClass(bind.target,"bx-messenger-message"))
				{
					messages = [bind.target];
				}
				else if (bind.target.className.indexOf("bx-messenger-content-quote") >= 0)
				{
					messages = BX.findParent(bind.target, {className : "bx-messenger-message"});
					messages = [messages];
				}
				else
				{
					messages = BX.findChildrenByClassName(bind.target, "bx-messenger-message");
				}
				if (messages.length <= 0)
				{
					messages = BX.findParent(bind.target, {className : "bx-messenger-message"});
					if (!messages)
					{
						if (bind.target.className.substr(0, 19) == 'bx-messenger-attach')
						{
							var attach = BX.findParent(bind.target, {className : "bx-messenger-attach-box"});
							messages = attach.previousSibling;
						}
					}
					messages = [messages];
				}
			}
		}
		if (messages.length <= 0 || !messages[messages.length-1])
			return false;

		var messageName = BX.message('IM_M_SYSTEM_USER');
		var messageId = messages[messages.length-1].id.replace('im-message-','');
		if (this.message[messageId].senderId && this.users[this.message[messageId].senderId])
			messageName = this.users[this.message[messageId].senderId].name;

		if (messageId.substr(0,4) == 'temp')
			return false;

		var messageDate = this.message[messageId].date;
		var selectedText = type == 'dialogContext'? BX.desktop.clipboardSelected(): {'text': "", selectionStart: 0, selectionEnd: 0};

		var copyLink = false;
		var userName = '';
		var userId = this.message[messageId].senderId;
		var canShareToCalend = this.message[messageId].params && this.message[messageId].params.DATE_TS && this.message[messageId].params.DATE_TS.length > 0;
		if (this.openChatFlag && this.message[messageId].senderId != this.BXIM.userId && this.users[this.message[messageId].senderId])
		{
			userName = this.users[this.message[messageId].senderId].name;
		}

		var saveIconTarget = null;
		var copyLinkHref = '';
		if (type == 'dialogContext' && (
			bind.target.tagName == 'SPAN' && bind.target.parentNode.parentNode.tagName == 'A' ||
			bind.target.tagName == 'CANVAS' && bind.target.parentNode.tagName == 'A' ||
			bind.target.tagName == 'IMG' && bind.target.parentNode.tagName == 'A' ||
			bind.target.tagName == 'A'
		))
		{
			if (bind.target.tagName == 'A')
				copyLinkHref = bind.target.href;
			else if (bind.target.parentNode.tagName == 'A')
				copyLinkHref = bind.target.parentNode.href;
			else if (bind.target.parentNode.parentNode.tagName == 'A')
				copyLinkHref = bind.target.parentNode.parentNode.href;

			if (copyLinkHref.indexOf('/desktop_app/') < 0)
				copyLink = true;
		}
		else if (type == 'dialogContext' && bind.target.tagName == 'IMG' && bind.target.classList.contains('bx-icon'))
		{
			saveIconTarget = bind.target.src;
		}

		var copyFile = this.message[messageId].params && this.message[messageId].params.FILE_ID && this.message[messageId].params.FILE_ID.length > 0 && BX.clipboard.isCopySupported();

		var getClipboard = false;
		if (type == 'dialogContext' && BX.desktop)
		{
			getClipboard = true;
		}

		var canEdit = false;
		var canDelete = false;
		if (BX.MessengerCommon.checkEditMessage(messageId, 'edit'))
		{
			canEdit = true;
		}
		if (BX.MessengerCommon.checkEditMessage(messageId, 'delete'))
		{
			canDelete = true;
		}

		if (this.openChatFlag && this.message[messageId].chatId && !BX.MessengerCommon.userInChat(this.message[messageId].chatId))
		{
			return false;
		}

		var generalAccessBlock = false;
		if (this.openChatFlag && this.message[messageId].chatId && this.generalChatId == this.message[messageId].chatId)
		{
			if (this.BXIM.isAdmin && !this.message[messageId].isNowDeleted)
			{
				canDelete = true;
			}
			if (!this.canSendMessageGeneralChat)
			{
				generalAccessBlock = true;
			}
		}

		var hideBlockCreate = selectedText.text.length > 0 || this.users[this.BXIM.userId].extranet;
		var hideElementCreateOl = hideBlockCreate || !this.chat[this.message[messageId].chatId] || this.chat[this.message[messageId].chatId].entity_type != 'LINES';

		var linesQuickAnswersItem =
		{
			text: BX.message("IM_MENU_TO_OL_QA"),
			onclick: BX.delegate(function()
			{
				BX.MessengerCommon.linesSaveToQuickAnswers(messageId);
				this.closeMenuPopup();
			}, this)
		};
		if(this.message[messageId].quick_saved)
		{
			linesQuickAnswersItem.text = BX.message("IM_MENU_TO_OL_QA_ADDED");
			linesQuickAnswersItem.onclick = null;
		}

		menuItems = [
			userName.length <= 0 || generalAccessBlock? null: {text: BX.message("IM_MENU_ANSWER"), onclick: BX.delegate(function(e){ this.insertTextareaText(this.popupMessengerTextarea, ' '+BX.util.htmlspecialcharsback(userName)+' ', false); BX.MessengerCommon.addMentionList(this.currentTab, BX.util.htmlspecialcharsback(userName), userId);  setTimeout(BX.delegate(function(){ this.popupMessengerTextarea.focus(); }, this), 200);  this.closeMenuPopup(); }, this)},
			userName.length <= 0 || generalAccessBlock? null: {separator: true},
			copyLink? {text: BX.message("IM_MENU_COPY3"), onclick: BX.delegate(function()
				{
					BX.clipboard.copy(copyLinkHref);
					this.closeMenuPopup();
				}, this)
			}: null,
			copyFile? {text: BX.message("IM_MENU_COPY_FILE"), onclick: BX.delegate(function()
				{
					var text = '';
					for (var i = 0; i < this.message[messageId].params.FILE_ID.length; i++)
					{
						text = text+'[DISK='+this.message[messageId].params.FILE_ID[i]+']';
					}
					BX.clipboard.copy(text);
					this.closeMenuPopup();
				}, this)
			}: null,
			saveIconTarget? {text: BX.message("IM_SETTINGS_SAVE"), onclick: BX.delegate(function()
				{
					this.addRecentSmile(this.message[messageId].text, saveIconTarget);
					this.closeMenuPopup();
				}, this)
			}: null,
			saveIconTarget || copyFile || copyLink && this.message[messageId].text? {separator: true}: null,
			userId == this.BXIM.userId || selectedText.text.length > 0 ? null: {text: BX.message("IM_MENU_UNREAD"), onclick: BX.delegate(function(){ BX.MessengerCommon.unreadMessage(messageId); this.closeMenuPopup(); }, this)}, // TODO this
			userId == this.BXIM.userId ? null: {separator: true},
			selectedText.text.length <= 0 || generalAccessBlock? null: {text: BX.message("IM_MENU_QUOTE"), onclick: BX.delegate(function(){ var text = BX.IM.getSelectionText(); this.insertQuoteText(messageName, messageDate, text); this.closeMenuPopup(); }, this)},
			generalAccessBlock || selectedText.text.length > 0 || (!this.message[messageId].text && (!this.message[messageId].params || this.message[messageId].params.FILE_ID.length <= 0)) ? null: {text: BX.message("IM_MENU_QUOTE2"), onclick: BX.delegate(function()
				{
					var arQuote = [];
					for (var i = 0; i < messages.length; i++)
					{
						var messageId = messages[i].id.replace('im-message-','');
						if (this.message[messageId])
						{
							if (this.message[messageId].text)
							{
								arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
							}
							if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
							{
								for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
								{
									var fileId = this.message[messageId].params.FILE_ID[j];
									var chatId = this.message[messageId].chatId;
									if (this.disk.files[chatId][fileId])
									{
										arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
									}
								}
							}
						}
					}
					if (arQuote.length > 0)
					{
						this.insertQuoteText(messageName, messageDate, arQuote.join("\n"));
					}

					this.closeMenuPopup();
				}, this)
			},
			!getClipboard || selectedText.text.length <= 0? null: {text: BX.message("IM_MENU_COPY"), onclick: BX.delegate(function(){ BX.desktop.clipboardCopy(); this.closeMenuPopup(); }, this)},
			!getClipboard || !this.message[messageId].text || selectedText.text.length > 0? null: {text: BX.message("IM_MENU_COPY2"), onclick: BX.delegate(function()
				{
					var arQuote = [];
					for (var i = 0; i < messages.length; i++)
					{
						var messageId = messages[i].id.replace('im-message-','');
						if (this.message[messageId])
						{
							if (this.message[messageId].text)
							{
								arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
							}
							if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
							{
								for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
								{
									var fileId = this.message[messageId].params.FILE_ID[j];
									var chatId = this.message[messageId].chatId;
									if (this.disk.files[chatId][fileId])
									{
										arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
									}
								}
							}
						}
					}
					if (arQuote.length > 0)
					{
						BX.desktop.clipboardCopy(BX.delegate(function (value)
						{
							return this.insertQuoteText(messageName, messageDate, arQuote.join("\n"), false);
						}, this));
					}
					this.closeMenuPopup();
				}, this)
			},
			hideBlockCreate? null: {separator: true},
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_TASK"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'TASK'); this.closeMenuPopup(); }, this)},
			hideBlockCreate || !canShareToCalend? null: {text: BX.message("IM_MENU_TO_CALEND"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'CALEND'); this.closeMenuPopup(); }, this)},
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_CHAT"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'CHAT'); this.closeMenuPopup(); }, this)},
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_POST"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'POST'); this.closeMenuPopup(); }, this)},
			hideElementCreateOl? null: {separator: true},
			hideElementCreateOl? null: {text: BX.message("IM_MENU_TO_OL_START"), onclick: BX.delegate(function(){ BX.MessengerCommon.linesStartSessionByMessage(messageId); this.closeMenuPopup(); }, this)},
			hideElementCreateOl? null: linesQuickAnswersItem,
			!(!canEdit || this.message[messageId].senderId != this.BXIM.userId) || canDelete? {separator: true}: null,
			!canEdit || this.message[messageId].senderId != this.BXIM.userId? null: {text: BX.message("IM_MENU_EDIT"), onclick: BX.delegate(function() {this.editMessage(messageId);this.closeMenuPopup();}, this)},
			!canDelete? null: {text: BX.message("IM_M_HISTORY_DELETE"), onclick: BX.delegate(function() {this.deleteMessage(messageId, false);this.closeMenuPopup();}, this)}
		];
		if (this.message[messageId].params && this.message[messageId].params.MENU)
		{
			var firstPush = true;
			for (var i = 0; i < this.message[messageId].params.MENU.length; i++)
			{
				var menuItem = this.message[messageId].params.MENU[i];
				if (
					menuItem.CONTEXT &&
					(
						BX.MessengerCommon.isMobile() && menuItem.CONTEXT == 'DESKTOP' ||
						!BX.MessengerCommon.isMobile() && menuItem.CONTEXT == 'MOBILE'
					)
				)
				{
					continue;
				}
				if (firstPush)
				{
					menuItems.push({separator: true});
					firstPush = false;
				}
				var disabled = menuItem.DISABLED == 'Y';
				menuItems.push({
					text: menuItem.TEXT,
					disabled: disabled,
					icon: 'bx-messenger-menu-important',
					dataParams: menuItem,
					href: menuItem.LINK? menuItem.LINK: "",
					onclick: disabled? null: BX.delegate(function() {

						var menuItem = JSON.parse(BX.proxy_context.getAttribute('data-params'));

						if (menuItem.FUNCTION)
						{
							var userFunc = menuItem.FUNCTION.toString().replace('#MESSAGE_ID#', messageId).replace('#DIALOG_ID#', dialogId).replace('#USER_ID#', this.BXIM.userId);
							userFunc();
						}
						else if (menuItem.APP_ID)
						{
							menuItem.APP_PARAMS = menuItem.APP_PARAMS? menuItem.APP_PARAMS: '';
							this.textareaIconDialogClick(parseInt(menuItem.APP_ID), messageId, BX.util.htmlspecialchars(menuItem.APP_PARAMS));
						}

						this.closeMenuPopup();
					}, this)
				});
			}
		}
	}
	else if (type == 'shareMenu')
	{
		var messageId = bind.getAttribute('data-messageId');
		var selectedDate = bind.getAttribute('data-ts');

		var hideBlockCreate = this.users[this.BXIM.userId].extranet;
		menuItems = [
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_TASK"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'TASK', selectedDate); this.closeMenuPopup(); }, this)},
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_CALEND"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'CALEND', selectedDate); this.closeMenuPopup(); }, this)},
			hideBlockCreate? null: {text: BX.message("IM_MENU_TO_CHAT"), onclick: BX.delegate(function(){ this.shareMessage(messageId, 'CHAT', selectedDate); this.closeMenuPopup(); }, this)},
		];
	}
	else if (type == 'history')
	{
		var messages = [];
		if (bind.target.className == "bx-messenger-history-item")
		{
			messages = [bind.target];
		}
		else if (bind.target.className.indexOf("bx-messenger-content-quote") >= 0)
		{
			messages = BX.findParent(bind.target, {className : "bx-messenger-history-item"});
			messages = [messages];
		}
		else
		{
			messages = BX.findChildrenByClassName(bind.target, "bx-messenger-history-item");
		}
		if (messages.length <= 0)
		{
			messages = BX.findParent(bind.target, {className : "bx-messenger-history-item"});
			messages = [messages];
		}
		if (messages.length <= 0 || !messages[messages.length-1])
			return false;

		var messageName = BX.message('IM_M_SYSTEM_USER');
		var messageId = messages[messages.length-1].getAttribute('data-messageId');
		if (this.message[messageId].senderId && this.users[this.message[messageId].senderId])
			messageName = this.users[this.message[messageId].senderId].name;
		var messageDate = this.message[messageId].date;

		if (BX.desktop)
		{

			var selectedText = BX.desktop.clipboardSelected();

			var copyLink = false;
			var copyLinkHref = '';
			if (bind.target.tagName == 'IMG' && bind.target.parentNode.tagName == 'A' || bind.target.tagName == 'A')
			{
				if (bind.target.tagName == 'A')
					copyLinkHref = bind.target.href;
				else
					copyLinkHref = bind.target.parentNode.href;

				if (copyLinkHref.indexOf('/desktop_app/') < 0 || copyLinkHref.indexOf('/desktop_app/show.file.php') >= 0)
					copyLink = true;
			}

			var showContext = this.BXIM.messenger.historySearch? true: false;

			menuItems = [
				showContext? {text: BX.message("IM_HISTORY_RELATED"), onclick: BX.delegate(function(){
					this.showContext(messageId);
					this.closeMenuPopup();
				}, this)}: null,
				showContext? {separator: true}: null,
				copyLink? {text: BX.message("IM_MENU_COPY3"), onclick: BX.delegate(function()
					{
						BX.desktop.clipboardCopy(BX.delegate(function(){
							return copyLinkHref;
						}, this));
						this.closeMenuPopup();
					}, this)
				}: null,
				copyLink? {separator: true}: null,
				selectedText.text.length <= 0? null: {text: BX.message("IM_MENU_QUOTE"), onclick: BX.delegate(function(){ var text = BX.IM.getSelectionText();  this.insertQuoteText(messageName, messageDate, text); this.closeMenuPopup(); }, this)},
				{text: BX.message("IM_MENU_QUOTE2"), onclick: BX.delegate(function()
					{
						var arQuote = [];
						for (var i = 0; i < messages.length; i++)
						{
							var messageId = messages[i].getAttribute('data-messageId');
							if (this.message[messageId])
							{
								if (this.message[messageId].text)
								{
									arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
								}
								if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
								{
									for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
									{
										var fileId = this.message[messageId].params.FILE_ID[i];
										var chatId = this.message[messageId].chatId;
										if (this.disk.files[chatId][fileId])
										{
											arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
										}
									}
								}
							}
						}
						if (arQuote.length > 0)
						{
							this.insertQuoteText(messageName, messageDate, arQuote.join("\n"));
						}

						this.closeMenuPopup();
					}, this)
				},
				{separator: true},
				selectedText.text.length <= 0? null: {text: BX.message("IM_MENU_COPY"), onclick: BX.delegate(function(){ BX.desktop.clipboardCopy(); this.closeMenuPopup(); }, this)},
				{text: BX.message("IM_MENU_COPY2"), onclick: BX.delegate(function()
					{
						var arQuote = [];
						for (var i = 0; i < messages.length; i++)
						{
							var messageId = messages[i].getAttribute('data-messageId');
							if (this.message[messageId])
							{
								if (this.message[messageId].text)
								{
									arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
								}
								if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
								{
									for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
									{
										var fileId = this.message[messageId].params.FILE_ID[j];
										var chatId = this.message[messageId].chatId;
										if (this.disk.files[chatId][fileId])
										{
											arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
										}
									}
								}
							}
						}
						if (arQuote.length > 0)
						{
							BX.desktop.clipboardCopy(BX.delegate(function (value)
							{
								return this.insertQuoteText(messageName, messageDate, arQuote.join("\n"), false);
							}, this));
						}
						this.closeMenuPopup();
					}, this)
				}
			];
		}
		else
		{
			var showQuote = this.popupMessengerTextarea || opener;
			var showContext = this.BXIM.messenger.historySearch? true: false;
			menuItems = [
				showContext? {text: BX.message("IM_HISTORY_RELATED"), onclick: BX.delegate(function(){
					this.showContext(messageId);
					this.closeMenuPopup();
				}, this)}: null,
				/*showContext? {text: BX.message("IM_HISTORY_JUMP"), onclick: BX.delegate(function(){
					this.jumpToMessage(messageId);
					this.closeMenuPopup();
				}, this)}: null,*/
				showQuote? {separator: true}: null,
				showQuote? {text: BX.message("IM_MENU_QUOTE2"), onclick: BX.delegate(function()
					{
						var arQuote = [];
						for (var i = 0; i < messages.length; i++)
						{
							var messageId = messages[i].getAttribute('data-messageId');
							if (this.message[messageId])
							{
								if (this.message[messageId].text)
								{
									arQuote.push(BX.MessengerCommon.prepareTextBack(this.message[messageId].text));
								}
								if (this.message[messageId].params && this.message[messageId].params.FILE_ID)
								{
									for (var j = 0; j < this.message[messageId].params.FILE_ID.length; j++)
									{
										var fileId = this.message[messageId].params.FILE_ID[i];
										var chatId = this.message[messageId].chatId;
										if (this.disk.files[chatId][fileId])
										{
											arQuote.push('['+BX.message('IM_F_FILE')+': '+this.disk.files[chatId][fileId].name+']');
										}
									}
								}
							}
						}
						if (arQuote.length > 0)
						{
							this.insertQuoteText(messageName, messageDate, arQuote.join("\n"));
						}

						this.closeMenuPopup();
					}, this)
				}: null,
				!showContext && !showQuote? {text: BX.message("IM_P_CLOSE"), onclick: BX.delegate(function(){
					this.closeMenuPopup();
				}, this)}: null
			];
		}
	}
	else if (type == 'historyFileMenu')
	{
		offsetTop = 4;
		offsetLeft = 8;
		this.popupPopupMenuStyle = 'bx-messenger-file-active';

		var fileId = params.fileId;
		var chatId = params.chatId;
		var enableLink = true;
		//if (!BX.MessengerCommon.isDesktop())
		//	enableLink = false;

		if (!this.disk.files[chatId][fileId])
			return false;

		var deleteSelf = this.disk.files[chatId][fileId].authorId != this.BXIM.userId;

		menuItems = [
			enableLink? { text: BX.message("IM_F_DOWNLOAD"), href: this.disk.files[chatId][fileId].urlDownload, 'target': '_blank', onclick: BX.delegate(function(){  this.closeMenuPopup(); }, this)}: null,
			{text: BX.message("IM_F_DOWNLOAD_DISK"), onclick: BX.delegate(function(){
				this.disk.saveToDisk(chatId, fileId, {boxId: 'im-file-history-panel'});
				this.closeMenuPopup();
			}, this)},
			this.chat[chatId] && this.chat[chatId].type == 'open' && deleteSelf? null: {text: BX.message("IM_F_DELETE"), onclick: BX.delegate(function(){
				this.BXIM.openConfirm(deleteSelf? BX.message('IM_F_DELETE_SELF_CONFIRM'): BX.message('IM_F_DELETE_CONFIRM'), [
					new BX.PopupWindowButton({
						text : BX.message('IM_F_DELETE_CONFIRM_YES'),
						className : "popup-window-button-accept",
						events : { click : BX.delegate(function() {
							this.disk.deleteFile(chatId, fileId, {boxId: 'im-file-history-panel'});
							BX.proxy_context.popupWindow.close();
						}, this) }
					}),
					new BX.PopupWindowButton({
						text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
						className : "popup-window-button-decline",
						events : { click : function() { this.popupWindow.close(); } }
					})
				], true);

				this.closeMenuPopup();
			}, this)}
		];
	}
	else if (type == 'notify')
	{
		if (bind.target.className == 'bx-notifier-item-delete')
		{
			bind.target.setAttribute('id', 'bx-notifier-item-delete-'+bind.target.getAttribute('data-notifyId'));
			this.openPopupMenu(bind.target, 'notifyDelete');

			return false;
		}

		var selectedText = BX.desktop.clipboardSelected();

		var copyLink = false;

		if (bind.target.tagName == 'A' && (bind.target.href.indexOf('/desktop_app/') < 0 || copyLinkHref.indexOf('/desktop_app/show.file.php') >= 0))
		{
			copyLink = true;
			var copyLinkHref = bind.target.href;
		}
		else if (bind.target.parentNode.tagName == 'A' && (bind.target.parentNode.href.indexOf('/desktop_app/') < 0 || copyLinkHref.indexOf('/desktop_app/show.file.php') >= 0))
		{
			copyLink = true;
			var copyLinkHref = bind.target.parentNode.href;
		}

		if (!copyLink && selectedText.text.length <= 0)
		{
			var notifyId = bind.target.getAttribute('data-notifyId');
			if (!notifyId)
			{
				notifyId = bind.target.parentNode.parentNode.getAttribute('data-notifyId');
				if (!notifyId)
				{
					notifyId = bind.target.parentNode.getAttribute('data-notifyId');
				}
			}
			if (notifyId)
			{
				bind.target.setAttribute('data-notifyId', notifyId);
				this.openPopupMenu(bind.target, 'notifyDelete', false, {applyToDom: bind});
			}
			return false;
		}

		menuItems = [
			copyLink? {text: BX.message("IM_MENU_COPY3"), onclick: BX.delegate(function()
				{
					BX.desktop.clipboardCopy(BX.delegate(function(){
						return copyLinkHref;
					}, this));
					this.closeMenuPopup();
				}, this)
			}: null,
			copyLink? {separator: true}: null,
			selectedText.text.length <= 0? null: {text: BX.message("IM_MENU_COPY"), onclick: BX.delegate(function(){ BX.desktop.clipboardCopy(); this.closeMenuPopup(); }, this)}
		];

	}
	else if (type == 'copylink')
	{
		if (bind.target.tagName != 'A' || (bind.target.href.indexOf('/desktop_app/') >= 0 && bind.target.href.indexOf('/desktop_app/show.file.php') < 0 ))
			return false;

		menuItems = [
			{text: BX.message("IM_MENU_COPY3"), onclick: BX.delegate(function()
				{
					BX.desktop.clipboardCopy(BX.delegate(function(value){
						return bind.target.href;
					}, this));
					this.closeMenuPopup();
				}, this)
			}
		];
	}
	else if (type == 'copypaste')
	{
		if (params.spell && !this.desktop.enableInVersion(34))
		{
			params.spell = false;
		}

		menuItems = []
		var selectedText = BX.desktop.clipboardSelected(bind.target, params.spell);
		if (!selectedText.text)
		{
			params.spell = false;
		}

		if (params.spell)
		{
			if (params.spellReady)
			{
				for (var i = 0; i < params.suggest.length; i++)
				{
					dataParams = {'suggest': params.suggest[i], selectionStart: selectedText.selectionStart, selectionEnd: selectedText.selectionEnd};
					menuItems.push({text: params.suggest[i], slim: true, bold: true, dataParams: dataParams, onclick: BX.delegate(function(){
						var dataParams = JSON.parse(BX.proxy_context.getAttribute('data-params'));

						setTimeout(function(){
							BX.desktop.clipboardReplaceText(bind.target, dataParams.selectionStart, dataParams.selectionEnd, dataParams.suggest);
						}, 50);

						this.closeMenuPopup();
					}, this)});

					if (i == 5) break;
				}
				if (menuItems.length <= 0)
				{
					menuItems.push({text: BX.message("IM_MENU_SUGGEST_EMPTY"), bold: true, slim: true });
				}
				menuItems.push({separator: true});
			}
			else
			{
				BXDesktopSystem.SpellCheckWord(selectedText.text, BX.delegate(function(isCorrect, suggest){
					this.openPopupMenu(bind, 'copypaste', false, {'spell': !isCorrect, 'spellReady': true, 'suggest': suggest});
				}, this));
			}
		}

		if (!params.spell || params.spellReady)
		{
			if (selectedText.text.length)
			{
				menuItems.push({text: BX.message("IM_MENU_CUT"), onclick: BX.delegate(function(){ BX.desktop.clipboardCut(); this.closeMenuPopup(); }, this)}),
				menuItems.push({text: BX.message("IM_MENU_COPY"), onclick: BX.delegate(function(){ BX.desktop.clipboardCopy(); this.closeMenuPopup(); }, this)}),
				menuItems.push({text: BX.message("IM_MENU_DELETE"), onclick: BX.delegate(function(){ BX.desktop.clipboardDelete(); this.closeMenuPopup(); }, this)})
			}
			else
			{
				menuItems.push({text: BX.message("IM_MENU_PASTE"), onclick: BX.delegate(function(){ BX.desktop.clipboardPaste(); this.closeMenuPopup(); }, this)});
			}
			bindOptions = {position: "top"};
		}
	}
	else
	{
		menuItems = [];
	}

	if (menuItems.length <= 0)
	{
		return false;
	}

	var nullMenuItems = true;
	for (var i = 0; i < menuItems.length; i++)
	{
		if (menuItems[i])
		{
			nullMenuItems = false;
		}
	}
	if (nullMenuItems)
	{
		menuItems = [{text: BX.message("IM_NOTIFY_CONFIRM_CLOSE"), onclick: BX.delegate(function(){  this.closeMenuPopup(); }, this)}];
	}
	else
	{
		var firstElementSeparator = false;
		for (var i = 0; i < menuItems.length; i++)
		{
			if (menuItems[i])
			{
				if(menuItems[i].separator)
				{
					menuItems[i] = null;
				}
				else
				{
					break;
				}
			}
		}
	}

	menuItems = this.modifierPopupMenu(type, menuItems);

	this.popupPopupMenuDateCreate = +new Date();
	this.popupPopupMenu = new BX.PopupWindow('bx-messenger-popup-'+type, bind, {
		//parentPopup: this.popupMessenger,
		lightShadow : true,
		offsetTop: offsetTop,
		offsetLeft: offsetLeft,
		autoHide: true,
		closeByEsc: true,
		zIndex: params.zIndex ? params.zIndex : 3001,
		bindOptions: bindOptions,
		events : {
			onPopupClose : BX.delegate(function() {
				if (this.popupPopupMenuStyle)
				{
					if (this.popupPopupMenuStyle == 'bx-messenger-file-active')
						BX.removeClass(this.popupPopupMenu.bindElement.parentNode, this.popupPopupMenuStyle);
					else if (this.popupPopupMenuStyle == 'bx-messenger-content-item-menu-hover')
						BX.removeClass(this.popupPopupMenu.bindElement.parentNode, this.popupPopupMenuStyle);
					else
						BX.removeClass(this.popupPopupMenu.bindElement, this.popupPopupMenuStyle);
				}
				if (this.popupPopupMenuDateCreate+500 < (+new Date()))
					BX.proxy_context.destroy()
			}, this),
			onPopupDestroy : BX.delegate(function() {
				if (this.popupPopupMenuStyle)
				{
					if (this.popupPopupMenuStyle == 'bx-messenger-file-active')
						BX.removeClass(this.popupPopupMenu.bindElement.parentNode, this.popupPopupMenuStyle);
					else if (this.popupPopupMenuStyle == 'bx-messenger-content-item-menu-hover')
						BX.removeClass(this.popupPopupMenu.bindElement.parentNode, this.popupPopupMenuStyle);
					else
						BX.removeClass(this.popupPopupMenu.bindElement, this.popupPopupMenuStyle);
				}
				this.popupPopupMenu = null;
			}, this)
		},
		content : BX.create("div", { props : { className : "bx-messenger-popup-menu" }, children: [ //TODO SCROLL
			BX.create("div", { props : { className : "bx-messenger-popup-menu-items" }, children: BX.Messenger.MenuPrepareList(menuItems)})
		]})
	});
	if (setAngle !== false)
		this.popupPopupMenu.setAngle(angleOptions);

	this.popupPopupMenu.show();

	if (this.popupPopupMenuStyle)
	{
		if (this.popupPopupMenuStyle == 'bx-messenger-file-active')
			BX.addClass(bind.parentNode, this.popupPopupMenuStyle);
		else if (this.popupPopupMenuStyle == 'bx-messenger-content-item-menu-hover')
			BX.addClass(bind.parentNode, this.popupPopupMenuStyle);
		else
			BX.addClass(bind, this.popupPopupMenuStyle);
	}

	BX.bind(this.popupPopupMenu.popupContainer, "click", BX.MessengerCommon.preventDefault);

	if (type == 'dialogContext' || type == 'notify' || type == 'history' || type == 'copypaste')
	{
		BX.bind(this.popupPopupMenu.popupContainer, "mousedown", function(event){
			event.target.click();
		});
	}

	return false;
};

BX.Messenger.prototype.modifierPopupMenu = function(type, menu)
{
	var result = null;
	for (var i = 0; i < this.popupPopupMenuModifyFunction.length; i++)
	{
		result = this.popupPopupMenuModifyFunction[i](type, menu);
		if (result)
		{
			menu = result;
		}
	}

	return menu;
}

BX.Messenger.prototype.closePopupFileMenu = function()
{
	if (this.popupMessengerFileButton == null)
		return false;

	if (this.popupPopupMenuDateCreate+100 > (+new Date()))
		return false;

	if (BX.hasClass(this.popupMessengerFileButton, 'bx-messenger-textarea-file-active'))
	{
		BX.removeClass(this.popupMessengerFileButton, 'bx-messenger-textarea-file-active');
		this.setClosingByEsc(true);
	}
}

BX.Messenger.prototype.closePopupFileMenuKeydown = function(e)
{
	if (e.keyCode == 27)
	{
		setTimeout(BX.delegate(function(){
			this.closePopupFileMenu();
		}, this), 100);
	}
}

BX.Messenger.prototype.openPopupExternalData = function(bind, type, setAngle, params)
{
	if (this.popupSmileMenu != null)
		this.popupSmileMenu.destroy();

	if (this.popupPopupMenu != null)
	{
		this.popupPopupMenu.destroy();
		return false;
	}

	this.popupPopupMenuDateCreate = +new Date();
	var offsetTop = 0;
	var offsetLeft = 10;
	var bindOptions = {position: "top"};
	var sizesOptions = { width: '272px', height: '100px'};
	var ajaxData = { 'IM_GET_EXTERNAL_DATA' : 'Y', 'TYPE': type, 'TS': this.popupPopupMenuDateCreate, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()};
	var content = BX.create("div", { attrs: {'id': 'bx-messenger-external-data'}, props : { className : "bx-messenger-external-data" },  style: sizesOptions, children: [
		BX.create("div", { props : { className : "bx-messenger-external-data-load" }, html: BX.message('IM_CL_LOAD')})
	]})
	if (type == 'user')
	{
		sizesOptions = { width: '272px', height: '100px'};
		ajaxData['USER_ID'] = parseInt(params['ID']);
		if (this.users[ajaxData['USER_ID']] && !this.users[ajaxData['USER_ID']].fake)
		{
			ajaxData = false;
		}
	}
	else if (type == 'chat')
	{
		sizesOptions = { width: '272px', height: '100px'};
		ajaxData['CHAT_ID'] = parseInt(params['ID']);
		if (this.chat[ajaxData['CHAT_ID']] && !this.chat[ajaxData['CHAT_ID']].fake)
		{
			ajaxData = false;
		}
	}
	else if (type == 'phoneCallHistory')
	{
		sizesOptions = { width: '239px', height: '122px'};
		ajaxData['HISTORY_ID'] = parseInt(params['ID']);
	}
	else if (type == 'readedList')
	{
		ajaxData = false;
		var newReadedList = [];
		var firstUserId = 0;
		var firstUserDate = 0;
		for (var userId in this.BXIM.messenger.readedList[this.BXIM.messenger.currentTab])
		{
			if (userId == this.BXIM.userId)
				continue;

			if (!firstUserDate || firstUserDate > this.BXIM.messenger.readedList[this.BXIM.messenger.currentTab][userId].date)
			{
				firstUserId = userId;
				firstUserDate = this.BXIM.messenger.readedList[this.BXIM.messenger.currentTab][userId].date;
			}

			newReadedList.push({'userId': userId, 'date': this.BXIM.messenger.readedList[this.BXIM.messenger.currentTab][userId].date});
		}

		var htmlElement = '<span class="bx-notifier-item-help-popup">';
		for (var i = 0; i < newReadedList.length; i++)
		{
			if (newReadedList[i].userId == firstUserId)
				continue;

			var avatarColor = BX.MessengerCommon.isBlankAvatar(this.BXIM.messenger.users[newReadedList[i].userId].avatar)? 'style="background-color: '+this.BXIM.messenger.users[newReadedList[i].userId].color+'"': '';
			htmlElement += '<span class="bx-notifier-item-help-popup-img bx-messenger-panel-chat-user" data-userId="'+newReadedList[i].userId+'" title="'+BX.MessengerCommon.formatDate(newReadedList[i].date)+'">' +
				'<span class="bx-notifier-popup-avatar  bx-notifier-popup-avatar-status-'+BX.MessengerCommon.getUserStatus(this.users[newReadedList[i].userId])+'">' +
					'<img class="bx-notifier-popup-avatar-img'+(BX.MessengerCommon.isBlankAvatar(this.BXIM.messenger.users[newReadedList[i].userId].avatar)? " bx-notifier-popup-avatar-img-default": "")+'" src="'+this.BXIM.messenger.users[newReadedList[i].userId].avatar+'" '+avatarColor+'>' +
				'</span>' +
				'<span class="bx-notifier-item-help-popup-name  '+(this.BXIM.messenger.users[newReadedList[i].userId].extranet? ' bx-notifier-popup-avatar-extranet':'')+'">'+this.BXIM.messenger.users[newReadedList[i].userId].name+'</span>' +
			'</span>';
		}
		htmlElement += '</span>';

		content = BX.create("div", { props : { className : "bx-messenger-popup-menu" }, html: htmlElement});
	}
	else
	{
		return false;
	}

	this.popupPopupMenu = new BX.PopupWindow('bx-messenger-popup-external-data', bind, {
		//parentPopup: this.popupMessenger,
		lightShadow : true,
		offsetTop: offsetTop,
		offsetLeft: offsetLeft,
		autoHide: true,
		closeByEsc: true,
		zIndex: 200,
		bindOptions: bindOptions,
		events : {
			onPopupClose : function() { this.destroy() },
			onPopupDestroy : BX.delegate(function() { this.popupPopupMenu = null; }, this)
		},
		content : content
	});
	if (setAngle !== false)
		this.popupPopupMenu.setAngle({offset: 4});
	this.popupPopupMenu.show();

	if (ajaxData)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?GET_EXTERNAL_DATA&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: ajaxData,
			onsuccess: BX.delegate(function(data){

				if (data.ERROR)
				{
					data.TYPE = 'noAccess';
				}
				else if (data.TYPE == 'chat')
				{
					for (var i in data.CHAT)
					{
						data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
						this.chat[i] = data.CHAT[i];
					}
					for (var i in data.USER_IN_CHAT)
					{
						this.userInChat[i] = data.USER_IN_CHAT[i];
					}
					for (var i in data.USER_BLOCK_CHAT)
					{
						this.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
					}
				}
				else if (data.TYPE == 'user')
				{
					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.users[i] = data.USERS[i];
					}
					for (var i in data.PHONES)
					{
						this.phones[i] = {};
						for (var j in data.PHONES[i])
						{
							this.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
						}
					}
					for (var i in data.USER_IN_GROUP)
					{
						if (typeof(this.userInGroup[i]) == 'undefined')
						{
							this.userInGroup[i] = data.USER_IN_GROUP[i];
						}
						else
						{
							for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
								this.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

							this.userInGroup[i].users = BX.util.array_unique(this.userInGroup[i].users)
						}
					}
				}

				data.TS = parseInt(data.TS);
				if (data.TS > 0 && data.TS != this.popupPopupMenuDateCreate || !this.popupPopupMenu)
					return false;

				this.drawExternalData(data.TYPE, data);
			}, this),
			onfailure: BX.delegate(function(){
				if (this.popupPopupMenu)
					this.popupPopupMenu.destroy();
			}, this)
		});
	}
	else
	{
		if (type == 'user')
			this.drawExternalData('user', {'USER_ID': params['ID']});
		else if (type == 'chat')
			this.drawExternalData('chat', {'CHAT_ID': params['ID']});

	}

	if (this.popupPopupMenu)
		BX.bind(this.popupPopupMenu.popupContainer, "click", BX.PreventDefault);

	return false;
};

BX.Messenger.prototype.drawExternalData = function(type, params)
{
	if (!BX('bx-messenger-external-data'))
		return false;

	if (type == 'noAccess')
	{
		BX('bx-messenger-external-data').innerHTML = BX.message('IM_M_USER_NO_ACCESS');
	}
	else if (type == 'user')
	{
		if (!this.users[params['USER_ID']])
		{
			if (this.popupPopupMenu)
				this.popupPopupMenu.destroy();

			return false;
		}

		var hideButtons = false;

		BX('bx-messenger-external-data').innerHTML = '';
		BX.adjust(BX('bx-messenger-external-data'), {children: [
			BX.create('div', { props : { className : "bx-messenger-external-avatar" }, children: [
				BX.create('div', { props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-status-"+BX.MessengerCommon.getUserStatus(this.users[params['USER_ID']]) }, children: [
					BX.create('img', { attrs : { src : this.users[params['USER_ID']].avatar, style: (BX.MessengerCommon.isBlankAvatar(this.users[params['USER_ID']].avatar)? 'background-color: '+this.users[params['USER_ID']].color: '')}, props : { className : "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.users[params['USER_ID']].avatar)? " bx-messenger-panel-avatar-img-default": "") }}),
					BX.create('span', { attrs : { title : (BX.MessengerCommon.getUserStatus(this.users[params['USER_ID']], false)).title},  props : { className : "bx-messenger-panel-avatar-status" }})
				]}),
				BX.create("span", { props : { className : "bx-messenger-panel-title"}, html: (
					this.users[params['USER_ID']].extranet? '<div class="bx-messenger-user-extranet">'+this.users[params['USER_ID']].name+'</div>':
					(this.users[params['USER_ID']].bot? '<div class="'+(this.bot[params['USER_ID']].type == 'network'? 'bx-messenger-user-network': 'bx-messenger-user-bot')+'">'+this.users[params['USER_ID']].name+'</div>': this.users[params['USER_ID']].name)
				)}),
				BX.create("span", { props : { className : "bx-messenger-panel-desc"}, html: BX.MessengerCommon.getUserPosition(this.users[params['USER_ID']])})
			]}),
			hideButtons? []: BX.create('div', {props : { className : "bx-messenger-external-data-buttons"}, children: [
				BX.create('span', {
					props : { className : "bx-notifier-item-button bx-notifier-item-button-white" },
					html: BX.message('IM_M_WRITE_MESSAGE'),
					events: {click: BX.delegate(function(e){
						this.popupPopupMenu.destroy();
						this.openMessenger(params['USER_ID']);
					}, this)}
				}),
				BX.create('span', {
					props : { className : "bx-notifier-item-button bx-notifier-item-button-white" },
					html: BX.message('IM_M_CALL_BTN_HISTORY'),
					events: {click: BX.delegate(function(){
						this.popupPopupMenu.destroy();
						this.openHistory(params['USER_ID']);
					}, this)}
				})
			]})
		]});
	}
	else if (type == 'chat')
	{
		if (!this.chat[params['CHAT_ID']])
		{
			if (this.popupPopupMenu)
				this.popupPopupMenu.destroy();

			return false;
		}

		var chatTypeTitle = BX.message('IM_CL_CHAT_2');
		if (this.chat[params['CHAT_ID']].type == 'call')
		{
			chatTypeTitle = BX.message('IM_CL_PHONE');
		}
		else if (this.chat[params['CHAT_ID']].type == 'lines')
		{
			chatTypeTitle = BX.message('IM_CL_LINES');
		}
		else if (this.chat[params['CHAT_ID']].type == 'livechat')
		{
			chatTypeTitle = BX.message('IM_CL_LINES');
		}
		else if (this.chat[params['CHAT_ID']].type == 'open')
		{
			chatTypeTitle = BX.message('IM_CL_OPEN_CHAT');
		}
		BX('bx-messenger-external-data').innerHTML = '';
		BX.adjust(BX('bx-messenger-external-data'), {children: [
			BX.create('div', { props : { className : "bx-messenger-external-avatar" }, children: [
				BX.create('div', { props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-"+this.chat[params['CHAT_ID']].type }, children: [
					BX.create('img', { attrs : { src : this.chat[params['CHAT_ID']].avatar, style: (BX.MessengerCommon.isBlankAvatar(this.chat[params['CHAT_ID']].avatar)? 'background-color: '+this.chat[params['CHAT_ID']].color: '')}, props : { className : "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.chat[params['CHAT_ID']].avatar)? " bx-messenger-panel-avatar-img-default": "") }}),
				]}),
				BX.create("span", { props : { className : "bx-messenger-panel-title"}, html: (
					this.chat[params['CHAT_ID']].extranet? '<div class="bx-messenger-user-extranet">'+this.chat[params['CHAT_ID']].name+'</div>': this.chat[params['CHAT_ID']].name
				)}),
				BX.create("span", { props : { className : "bx-messenger-panel-desc"}, html: chatTypeTitle})
			]}),
			BX.create('div', {props : { className : "bx-messenger-external-data-buttons"}, children: [
				BX.create('span', {
					props : { className : "bx-notifier-item-button bx-notifier-item-button-white" },
					html: BX.message('IM_M_OPEN_CHAT'),
					events: {click: BX.delegate(function(e){
						this.popupPopupMenu.destroy();
						this.openMessenger('chat'+params['CHAT_ID']);
					}, this)}
				}),
				BX.create('span', {
					props : { className : "bx-notifier-item-button bx-notifier-item-button-white" },
					html: BX.message('IM_M_CALL_BTN_HISTORY'),
					events: {click: BX.delegate(function(){
						this.popupPopupMenu.destroy();
						this.openHistory('chat'+params['CHAT_ID']);
					}, this)}
				})
			]})
		]});
	}
	else if (type == 'phoneCallHistory')
	{
		var recordHtml = false;
		if (params['CALL_RECORD_HTML'])
		{
			var recordHtml = {
				HTML: BX.message('CALL_RECORD_ERROR'),
				SCRIPT: []
			}
			if (!BX.MessengerCommon.isDesktop())
				recordHtml = BX.processHTML(params['CALL_RECORD_HTML'], false);
		}

		BX('bx-messenger-external-data').innerHTML = '';
		BX.adjust(BX('bx-messenger-external-data'), {children: [
			BX.create('div', { props : { className : "bx-messenger-record" }, children: [
				BX.create('div', { props : { className : "bx-messenger-record-phone-box" }, children: [
					BX.create('span', { props : { className : "bx-messenger-record-icon bx-messenger-record-icon-"+params['CALL_ICON'] }, attrs: {title: params['INCOMING_TEXT']}}),
					BX.create('span', { props : { className : "bx-messenger-record-phone" }, html: (params['PHONE_NUMBER_FORMATTED'] ? params['PHONE_NUMBER_FORMATTED'] : (params['PHONE_NUMBER'] && params['PHONE_NUMBER'].toString().length >=10? '+': '')+params['PHONE_NUMBER'])})
				]}),
				BX.create("div", { props : { className : "bx-messenger-record-reason"}, html: params['CALL_FAILED_REASON']}),
				BX.create('div', { props : { className : "bx-messenger-record-stats" }, children: [
					BX.create('span', { props : { className : "bx-messenger-record-time" }, html: params['CALL_DURATION_TEXT']}),
					BX.create('span', { props : { className : "bx-messenger-record-cost" }, html: params['COST_TEXT']})
				]}),
				recordHtml? BX.create('div', { props : { className : "bx-messenger-record-box" }, children: [
					BX.create('span', { props : { className : "bx-messenger-record-player" }, html: recordHtml.HTML})
				]}): null
			]})
		]});

		if (recordHtml)
		{
			for (var i = 0; i < recordHtml.SCRIPT.length; i++)
			{
				BX.evalGlobal(recordHtml.SCRIPT[i].JS);
			}
		}
	}
}

/* HISTORY */
BX.Messenger.prototype.openHistory = function(userId)
{
	if (this.popupMessengerConnectionStatusState != 'online')
		return false;

	if (this.historyWindowBlock)
		return false;

	this.historyLastSearch[userId] = '';

	if (!this.historyEndOfList[userId])
		this.historyEndOfList[userId] = {};

	if (!this.historyLoadFlag[userId])
		this.historyLoadFlag[userId] = {};

	if (this.popupHistory != null)
		this.popupHistory.destroy();

	var chatId = 0;
	var sessionId = 0;
	var enableDisk = this.BXIM.disk.enable;
	var isChat = false;
	if (userId.toString().substr(0,4) == 'chat')
	{
		isChat = true;
		chatId = parseInt(userId.toString().substr(4));
		if (chatId <= 0)
			return false;
	}
	else
	{
		userId = parseInt(userId);
		if (userId <= 0)
			return false;

		chatId = this.userChat[userId]? this.userChat[userId]: 0;
	}

	this.historyFilesEndOfList[chatId] = false;
	this.historyFilesLoadFlag[chatId] = false;

	this.historyUserId = userId;
	this.historyChatId = chatId;

	if (!BX.MessengerCommon.isPage())
		this.setClosingByEsc(false);

	this.popupHistoryPanel = null;
	var historyPanel = this.redrawHistoryPanel(userId, chatId);

	this.popupHistoryElements = BX.create("div", { props : { className : "bx-messenger-history"+(enableDisk? ' bx-messenger-history-with-disk': '')+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: [
		this.popupHistoryPanel = BX.create("div", { props : { className : "bx-messenger-panel-wrap" }, children: historyPanel}),
		BX.create("div", { props : { className : "bx-messenger-history-types" }, children : [
			BX.create("span", { props : { className : "bx-messenger-history-type bx-messenger-history-type-message" }, children : [
				this.popupHistoryButtonFilterBox = BX.create("div", { props : { className : "bx-messenger-panel-filter-box" }, style : {display: 'block'}, children : [
					BX.create('div', {props : { className : "bx-messenger-filter-name" }, html: BX.message('IM_HISTORY_FILTER_NAME')}),
					this.popupHistorySearchDateWrap = BX.create('div', {props : { className : "bx-messenger-filter-date bx-messenger-input-wrap" }, html: '<span class="bx-messenger-input-date"></span><a class="bx-messenger-input-close" href="#close"></a><input type="text" class="bx-messenger-input" value="" tabindex="1003" placeholder="'+BX.message('IM_PANEL_FILTER_DATE')+'" />'}),
					this.popupHistorySearchWrap = BX.create('div', {props : { className : "bx-messenger-filter-text bx-messenger-history-filter-text bx-messenger-input-wrap" }, html: '<a class="bx-messenger-input-close" href="#close"></a><input type="text" class="bx-messenger-input" tabindex="1000" placeholder="'+BX.message('IM_PANEL_FILTER_TEXT')+'" value="" />'})
				]}),
				this.popupHistoryItems = BX.create("div", { props : { className : "bx-messenger-history-items" }, style : {height: this.popupHistoryItemsSize+'px'}, children : [
					this.popupHistoryBodyWrap = BX.create("div", { props : { className : "bx-messenger-history-items-wrap" }})
				]})
			]}),
			BX.create("span", { props : { className : "bx-messenger-history-type bx-messenger-history-type-disk" }, children : [
				this.popupHistoryFilesButtonFilterBox = BX.create("div", { props : { className : "bx-messenger-panel-filter-box" }, style : {display: 'block'}, children : [
					this.popupHistoryFilesSearchWrap = BX.create('div', {props : { className : "bx-messenger-filter-text bx-messenger-input-wrap" }, html: '<a class="bx-messenger-input-close" href="#close"></a><input type="text"  tabindex="1002" class="bx-messenger-input" placeholder="'+BX.message('IM_F_FILE_SEARCH')+'" value="" />'})
				]}),
				this.popupHistoryFilesItems = BX.create("div", { props : { className : "bx-messenger-history-items" }, style : {height: this.popupHistoryItemsSize+'px'}, children : [
					this.popupHistoryFilesBodyWrap = BX.create("div", { props : { className : "bx-messenger-history-items-wrap" }})
				]})
			]})
		]})
	]});

	if (this.BXIM.init && BX.MessengerCommon.isDesktop())
	{
		this.desktop.openHistory(userId, this.popupHistoryElements, "BXIM.openHistory('"+userId+"');");
		return false;
	}
	else if (BX.MessengerCommon.isDesktop())
	{
		this.popupHistory = new BX.PopupWindowDesktop();
		this.desktop.drawOnPlaceholder(this.popupHistoryElements);

		BX.bind(window, "keydown", BX.proxy(function(e) {
			if (e.keyCode == 27)
			{
				if (this.popupHistorySearchInput.value == '')
				{
					this.popupHistory.destroy();
				}
				else
				{
					this.popupHistorySearchInput.value = '';
					this.popupHistorySearchInput.focus();
				}
			}
		}, this));
	}
	else
	{
		this.popupHistory = new BX.PopupWindow('bx-messenger-popup-history', null, {
			//parentPopup: this.popupMessenger,
			//offsetTop: 0,
			autoHide: false,
			zIndex: 100,
			draggable: {restrict: true},
			closeByEsc: true,
			bindOptions: {position: "top"},
			events : {
				onPopupClose : function() { this.destroy(); },
				onPopupDestroy : BX.delegate(function() {
					this.popupHistory = null; this.historySearch = ''; this.setClosingByEsc(true);
					this.closeMenuPopup();
					var calend = BX.calendar.get()
					if (calend)
					{
						calend.Close();
					}
				}, this)
			},
			titleBar: {content: BX.create('span', {props : { className : "bx-messenger-title" }, html: BX.message('IM_M_HISTORY')})},
			closeIcon : {'right': '13px'},
			content : this.popupHistoryElements,
			contentColor : "white",
			noAllPaddings : true
		});
		this.popupHistory.show();
		BX.bind(this.popupHistory.popupContainer, "click", BX.MessengerCommon.preventDefault);
	}
	this.drawHistory(this.historyUserId);
	if (enableDisk)
	{
		this.drawHistoryFiles(this.historyChatId);
	}

	if (BX.MessengerCommon.isDesktop())
	{
		BX.bind(this.popupHistorySearchInput, "contextmenu", BX.delegate(function(e) {
			this.openPopupMenu(e, 'copypaste', false);
			return BX.PreventDefault(e);
		}, this));

		BX.bindDelegate(this.popupHistoryElements, "contextmenu", {className: 'bx-messenger-history-item'}, BX.delegate(function(e) {
			this.openPopupMenu(e, 'history', false);
			return BX.PreventDefault(e);
		}, this));
	}

	BX.bindDelegate(this.popupHistoryElements, 'click', {className: 'bx-messenger-ajax'}, BX.delegate(function() {
		if (BX.proxy_context.getAttribute('data-entity') == 'user')
		{
			this.openPopupExternalData(BX.proxy_context, 'user', true, {'ID': BX.proxy_context.getAttribute('data-userId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'chat')
		{
			this.openPopupExternalData(BX.proxy_context, 'chat', true, {'ID': BX.proxy_context.getAttribute('data-chatId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'openlines')
		{
			this.linesOpenHistory(BX.proxy_context.getAttribute('data-sessionId'));
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'network')
		{
			this.openMessenger('network'+BX.proxy_context.getAttribute('data-networkId'))
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'date')
		{
			this.openPopupMenu(BX.proxy_context, 'shareMenu');
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'phoneCallHistory')
		{
			this.openPopupExternalData(BX.proxy_context, 'phoneCallHistory', true, {'ID': BX.proxy_context.getAttribute('data-historyID')})
		}
	}, this));

	BX.bindDelegate(this.popupHistoryElements, "click", {className: 'bx-messenger-history-item-menu'}, BX.delegate(function(e) {
		this.openPopupMenu(e, 'history', false);
		return BX.PreventDefault(e);
	}, this));

	BX.bindDelegate(this.popupHistoryPanel, "click", {className: 'bx-messenger-panel-basket'},   BX.delegate(function(){
		this.BXIM.openConfirm(BX.message('IM_M_HISTORY_DELETE_ALL_CONFIRM'), [
			new BX.PopupWindowButton({
				text : BX.message('IM_M_HISTORY_DELETE_ALL'),
				className : "popup-window-button-accept",
				events : { click : BX.delegate(function() { this.deleteAllHistory(userId); BX.proxy_context.popupWindow.close(); }, this) }
			}),
			new BX.PopupWindowButton({
				text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
				className : "popup-window-button-decline",
				events : { click : function() { this.popupWindow.close(); } }
			})
		], true);
	}, this));

	this.popupHistorySearchInput = BX.findChildByClassName(this.popupHistorySearchWrap, "bx-messenger-input");
	this.popupHistorySearchInputClose = BX.findChildByClassName(this.popupHistorySearchInput.parentNode, "bx-messenger-input-close");

	this.popupHistorySearchDateInput = BX.findChildByClassName(this.popupHistorySearchDateWrap, "bx-messenger-input");
	this.popupHistorySearchDateInputClose = BX.findChildByClassName(this.popupHistorySearchDateInput.parentNode, "bx-messenger-input-close");

	BX.bind(this.popupHistorySearchDateInput, "focus",  BX.delegate(function(e){
		BX.calendar({node: BX.proxy_context, field: BX.proxy_context, bTime: false, callback_after: BX.delegate(this.newHistoryDateSearch, this)});
		return BX.PreventDefault(e);
	}, this));
	BX.bind(this.popupHistorySearchDateInput, "click",  BX.delegate(function(e){
		BX.calendar({node: BX.proxy_context, field: BX.proxy_context, bTime: false, callback_after: BX.delegate(this.newHistoryDateSearch, this)});
		return BX.PreventDefault(e);
	}, this));

	BX.bind(this.popupHistorySearchDateInputClose, "click",  BX.delegate(function(e){
		this.popupHistorySearchDateInput.value = '';
		this.historyDateSearch = "";
		this.historyLastSearch[this.historyUserId] = "";
		this.drawHistory(this.historyUserId, false, false);
	}, this));

	if (this.popupHistoryFilterVisible && !BX.browser.IsAndroid() && !BX.browser.IsIOS())
		BX.focus(this.popupHistorySearchInput);

	BX.bind(this.popupHistorySearchInputClose, "click",  BX.delegate(function(e){
		this.popupHistorySearchInput.value = '';
		this.historySearch = "";
		this.historyLastSearch[this.historyUserId] = "";
		this.drawHistory(this.historyUserId, false, false);
		return BX.PreventDefault(e);
	}, this));

	BX.bind(this.popupHistorySearchInput, "keyup", BX.delegate(this.newHistorySearch, this));

	BX.bind(this.popupHistoryItems, "scroll", BX.delegate(function(){ BX.MessengerCommon.loadHistory(userId) }, this));

	if (this.disk.enable)
	{
		BX.bindDelegate(this.popupHistoryFilesBodyWrap, "click", {className: 'bx-messenger-file-menu'}, BX.delegate(function(e) {
			var fileId = BX.proxy_context.parentNode.parentNode.getAttribute('data-fileId');
			var chatId = BX.proxy_context.parentNode.parentNode.getAttribute('data-chatId');
			this.openPopupMenu(BX.proxy_context, 'historyFileMenu', true, {fileId: fileId, chatId: chatId});
			return BX.PreventDefault(e);
		}, this));

		this.popupHistoryFilesSearchInput = BX.findChildByClassName(this.popupHistoryFilesSearchWrap, "bx-messenger-input");
		this.popupHistoryFilesSearchInputClose = BX.findChildByClassName(this.popupHistoryFilesSearchInput.parentNode, "bx-messenger-input-close");

		BX.bind(this.popupHistoryFilesSearchInputClose, "click",  BX.delegate(function(e){
			this.popupHistoryFilesSearchInput.value = '';
			this.historyFilesSearch = "";
			this.historyFilesLastSearch[this.historyChatId] = "";
			this.drawHistoryFiles(this.historyChatId, false, false);
			return BX.PreventDefault(e);
		}, this));

		BX.bind(this.popupHistoryFilesSearchInput, "keyup", BX.delegate(this.newHistoryFilesSearch, this));

		BX.bind(this.popupHistoryFilesItems, "scroll", BX.delegate(function(){ this.loadHistoryFiles(this.historyChatId) }, this));
	}
};

BX.Messenger.prototype.loadHistoryFiles = function(chatId, afterDelete)
{
	if (this.historyFilesLoadFlag[chatId])
		return;

	if (this.historyFilesSearch != "")
		return;

	if (afterDelete && this.popupHistoryFilesItems.offsetHeight > this.popupHistoryFilesBodyWrap.offsetHeight - 100)
	{
	}
	else if (!(this.popupHistoryFilesItems.scrollTop > this.popupHistoryFilesItems.scrollHeight-this.popupHistoryFilesItems.offsetHeight-100))
	{
		return;
	}

	if (!this.historyFilesEndOfList[chatId])
	{
		this.historyFilesLoadFlag[chatId] = true;

		if (this.popupHistoryFilesBodyWrap.childNodes.length > 0)
			this.historyFilesOpenPage[chatId] = Math.floor(this.popupHistoryFilesBodyWrap.childNodes.length/15)+1;
		else
			this.historyFilesOpenPage[chatId] = 1;

		var tmpLoadMoreWait = null;
		this.popupHistoryFilesBodyWrap.appendChild(tmpLoadMoreWait = BX.create("div", { props : { className : "bx-messenger-content-load-more-history" }, children : [
			BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
			BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_LOAD_FILES')})
		]}));

		BX.ajax({
			url: this.BXIM.pathToAjax+'?HISTORY_FILES_LOAD_MORE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_HISTORY_FILES_LOAD' : 'Y', 'CHAT_ID' : chatId, 'PAGE_ID' : this.historyFilesOpenPage[chatId], 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				if (tmpLoadMoreWait)
					BX.remove(tmpLoadMoreWait);
				this.historyFilesLoadFlag[data.CHAT_ID] = false;
				if (data.FILES.length == 0)
				{
					this.historyFilesEndOfList[data.CHAT_ID] = true;
					return;
				}

				var countFiles = 0;
				for (var i in data.FILES)
				{
					if (!this.disk.files[data.CHAT_ID])
						this.disk.files[data.CHAT_ID] = {};

					if (!this.disk.files[data.CHAT_ID][i])
					{
						data.FILES[i].date = new Date(data.FILES[i].date);
						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}
					countFiles++;
				}
				if (countFiles < 15)
				{
					this.historyFilesEndOfList[data.CHAT_ID] = true;
				}

				for (var i in data.FILES)
				{
					var file = this.disk.files[data.CHAT_ID][i];
					if (file && !BX('im-file-history-panel-'+file.id))
					{
						var fileNode = this.disk.drawHistoryFiles(data.CHAT_ID, file.id, {getElement: 'Y'});
						if (fileNode)
							this.popupHistoryFilesBodyWrap.appendChild(fileNode);
					}
				}
			}, this),
			onfailure: function(){
				if (tmpLoadMoreWait)
					BX.remove(tmpLoadMoreWait);
			}
		});
	}
};

BX.Messenger.prototype.showContext = function(messageId)
{
	BX.ajax({
		url: this.BXIM.pathToAjax+'?LOAD_CONTEXT_MESSAGE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		skipAuthCheck: true,
		timeout: 30,
		data: {'IM_LOAD_CONTEXT_MESSAGE' : 'Y', 'MESSAGE_ID' : messageId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}
			if (data.ERROR == '')
			{
				var dialogId = data.DIALOG_ID;

				this.showMessage[dialogId] = [];
				this.sendAjaxTry = 0;
				for (var i in data.MESSAGE)
				{
					data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
					this.message[i] = data.MESSAGE[i];
				//	if (this.BXIM.settings.loadLastMessage)
				//		this.showMessage[dialogId].push(i);
				}
				//for (var i in data.USERS_MESSAGE)
				//{
				//	if (this.history[i])
				//		this.history[i] = BX.util.array_merge(this.history[i], data.USERS_MESSAGE[i]);
				//	else
				//		this.history[i] = data.USERS_MESSAGE[i];
				//}
				for (var i in data.FILES)
				{
					if (!this.disk.files[data.CHAT_ID])
						this.disk.files[data.CHAT_ID] = {};
					if (this.disk.files[data.CHAT_ID][i])
						continue;

					data.FILES[i].date = new Date(data.FILES[i].date);
					this.disk.files[data.CHAT_ID][i] = data.FILES[i];
				}
				for (var i in data.USERS)
				{
					data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
					data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
					data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
					data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

					this.users[i] = data.USERS[i];
				}
				for (var i in data.USER_IN_GROUP)
				{
					if (typeof(this.userInGroup[i]) == 'undefined')
					{
						this.userInGroup[i] = data.USER_IN_GROUP[i];
					}
					else
					{
						for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
							this.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

						this.userInGroup[i].users = BX.util.array_unique(this.userInGroup[i].users)
					}
				}
				for (var i in data.PHONES)
				{
					this.phones[i] = {};
					for (var j in data.PHONES[i])
					{
						this.phones[i][j] = BX.util.htmlspecialcharsback(data.PHONES[i][j]);
					}
				}
				var previousSearch = this.historySearch;
				this.historySearch = '';
				this.drawHistory(data.DIALOG_ID, data.USERS_MESSAGE, false);
				this.historySearch = previousSearch;

				if (BX('im-message-history-'+messageId))
				{
					var startScroll = BX('im-message-history-'+messageId).parentNode.offsetTop;

					this.popupHistoryItems.scrollTop = startScroll-(this.popupHistoryItems.offsetHeight/2)+(BX('im-message-history-'+messageId).parentNode.offsetHeight/2)
					BX.addClass(BX('im-message-history-'+messageId).parentNode, 'bx-messenger-history-item-context');
					BX.addClass(this.popupHistoryBodyWrap, 'bx-messenger-history-items-wrap-show-context');
				}
			}
			else
			{
				if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry < 2)
				{
					this.sendAjaxTry++;
					setTimeout(BX.delegate(function(){this.showContext(messageId)}, this), 1000);
					BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR')
				{
					this.sendAjaxTry++;
					if (BX.MessengerCommon.isDesktop())
					{
						setTimeout(BX.delegate(function (){
							this.showContext(messageId)
						}, this), 10000);
					}
					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
			}
		}, this),
		onfailure: BX.delegate(function(){
			this.sendAjaxTry = 0;
		}, this)
	});
}

BX.Messenger.prototype.jumpToMessage = function(messageId)
{

}

BX.Messenger.prototype.deleteAllHistory = function(userId)
{
	BX.ajax({
		url: this.BXIM.pathToAjax+'?HISTORY_REMOVE_ALL&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_HISTORY_REMOVE_ALL' : 'Y', 'USER_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});
	BX.localStorage.set('mhra', userId, 5);

	this.history[userId] = [];
	this.showMessage[userId] = [];
	this.popupHistoryBodyWrap.innerHTML = '';
	this.popupHistoryBodyWrap.appendChild(BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
		BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_NO_MESSAGE')})
	]}));

	if (BX.MessengerCommon.isDesktop())
		BX.desktop.onCustomEvent("main", "bxImClearHistory", [userId]);
	else if (this.BXIM.init)
		BX.MessengerCommon.drawTab(userId);
};

BX.Messenger.prototype.drawMessageHistory = function(message)
{
	if (typeof(message) != 'object')
		return null;

	if (typeof(message.params) != 'object')
	{
		message.params = {};
	}

	var system = message.senderId == 0;
	if (message.system && message.system == 'Y')
	{
		system = true;
		message.senderId = 0;
	}

	var edited = message.params && message.params.IS_EDITED == 'Y';
	var deleted = message.params && message.params.IS_DELETED == 'Y';

	var messageText = message.text;

	var filesNode = BX.MessengerCommon.diskDrawFiles(message.chatId, message.params.FILE_ID, {'status': ['done', 'error'], 'boxId': 'im-file-history'});
	if (filesNode.length > 0)
	{
		filesNode = BX.create("div", { props : { className : "bx-messenger-file-box"+(message.text != ''? ' bx-messenger-file-box-with-message':'') }, children: filesNode});
	}
	else
	{
		filesNode = null;
	}

	var attachNode = null;

	var attaches = [];
	if (message.params.ATTACH)
	{
		for (var i = 0; i < message.params.ATTACH.length; i++)
		{
			attaches[i] = message.params.ATTACH[i];
		}

		var attachPattern = /\[ATTACH=([0-9]{1,})\]/gm;  var match = [];
		while ((match = attachPattern.exec(messageText)) !== null)
		{
			for (var i = 0; i < attaches.length; i++)
			{
				if (message.params.ATTACH[i].ID == match[1])
				{
					attachNode = BX.create("div", { props : { className : "bx-messenger-attach-box" }, children: BX.MessengerCommon.drawAttach(message.id, message.chatId, [attaches[i]])});
					messageText = messageText.replace('[ATTACH='+match[1]+']', attachNode.innerHTML);
					delete attaches[i];
				}
			}
		}
	}

	if (message.params.LINK_ACTIVE && message.params.LINK_ACTIVE.length > 0 && message.params.LINK_ACTIVE.indexOf(this.BXIM.userId.toString()) < 0)
	{
		messageText = messageText.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/ig, '$2');
	}

	var extraClass = "";
	if (message.params.CLASS)
	{
		extraClass = message.params.CLASS;
	}

	attachNode = BX.MessengerCommon.drawAttach(message.id, message.chatId, attaches);
	if (attachNode.length > 0)
	{
		attachNode = BX.create("div", { props : { className : "bx-messenger-attach-box" }, children: attachNode});
	}
	else
	{
		attachNode = null;
	}
	var messageUser = this.BXIM.messenger.users[message.senderId];
	if (message.params && messageUser && messageUser.id > 0 && (message.params.AVATAR || message.params.NAME || message.params.USER_ID))
	{
		messageUser = BX.clone(messageUser);
		if (message.params.AVATAR)
		{
			messageUser.avatar = message.params.AVATAR;
		}
		if (message.params.NAME)
		{
			messageUser.name = message.params.NAME;
			messageUser.first_name = message.params.NAME.split(" ")[0];
		}
		message = BX.clone(message);
		if (parseInt(message.params.USER_ID))
		{
			message.senderId = 'network'+message.params.USER_ID;
		}
	}
	var voteBlock = BX.MessengerCommon.linesVoteDraw(message.id);
	if (voteBlock)
	{
		messageText = voteBlock;
		message.system = 'Y';
	}
	else
	{
		extraClass = extraClass.replace('bx-messenger-content-item-vote', '');

		var voteResultBlock = BX.MessengerCommon.linesVoteResultDraw(message.id, messageText);
		if (voteResultBlock)
		{
			messageText = voteResultBlock;
		}
	}

	var textNode = null;
	if (typeof(messageText) == 'string')
	{
		textNode = BX.create("span", {
			props : { className : "bx-messenger-history-item-text"+(deleted?" bx-messenger-message-deleted": " ")+(deleted || edited?" bx-messenger-message-edited": "")},
			attrs: {'id' : 'im-message-history-'+message.id},
			html: BX.MessengerCommon.prepareText(messageText, false, true, true, (!this.BXIM.messenger.openChatFlag || message.senderId == this.BXIM.userId? false: (this.BXIM.messenger.users[this.BXIM.userId].name)))}
		);
	}
	else
	{
		textNode = BX.create("span", {
			props : { className : "bx-messenger-history-item-text"+(deleted?" bx-messenger-message-deleted": " ")+(deleted || edited?" bx-messenger-message-edited": "")},
			attrs: {'id' : 'im-message-history-'+message.id},
			children: [messageText]}
		);
	}

	if (filesNode == null && message.text.length <= 0)
	{
		resultNode = BX.create("div", {attrs : { 'data-messageId' : message.id}, props : { className : "bx-messenger-history-item-text bx-messenger-item-skipped"}});
	}
	else
	{
		var userAvatar = "";
		var userColor = "";
		if (message.senderId > 0 && messageUser)
		{
			userAvatar = messageUser.avatar;
			userColor = messageUser.color;
		}

		resultNode = BX.create("div", { attrs : { 'data-messageId' : message.id}, props : { className : "bx-messenger-history-item"+(message.senderId == 0? " bx-messenger-history-item-3": (message.senderId == this.BXIM.userId?"": " bx-messenger-history-item-2"))+" "+extraClass }, children : [
			BX.create("div", { props : { className : "bx-messenger-history-hide" }, html : this.historyMessageSplit}),
			BX.create("span", { props : { className : "bx-messenger-history-item-avatar"}, children : [
				BX.create('img', { props : { className : "bx-messenger-content-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(userAvatar)? " bx-messenger-content-item-avatar-img-default": "") }, attrs : {src : message.senderId>0? userAvatar: this.BXIM.pathToBlankImage, style: (message.senderId>0 && BX.MessengerCommon.isBlankAvatar(userAvatar) && userColor? 'background-color: '+userColor: '')}})
			]}),
			BX.create("div", { props : { className : "bx-messenger-history-item-name" }, html : (this.users[message.senderId]? this.users[message.senderId].name: BX.message('IM_M_SYSTEM_USER'))+' <span class="bx-messenger-history-hide">[</span><span class="bx-messenger-history-item-date">'+BX.MessengerCommon.formatDate(message.date, BX.MessengerCommon.getDateFormatType('MESSAGE'))+'</span><span class="bx-messenger-history-hide">]</span>'/*<span class="bx-messenger-history-item-delete-icon" title="'+BX.message('IM_M_HISTORY_DELETE')+'" data-messageId="'+message.id+'"></span>*/}),
			BX.create("div", { props : { className : "bx-messenger-history-item-menu" }}),
			textNode,filesNode, attachNode,
			BX.create("div", { props : { className : "bx-messenger-history-hide" }, html : '<br />'}),
			BX.create("div", { props : { className : "bx-messenger-history-hide" }, html : this.historyMessageSplit})
		]});
	}

	return resultNode;
}

BX.Messenger.prototype.drawHistory = function(userId, historyElements, loadFromServer, sort)
{
	if (this.popupHistory == null)
		return false;

	sort = typeof(sort) == 'undefined'? true: sort;
	loadFromServer = typeof(loadFromServer) == 'undefined'? true: loadFromServer;

	var userIsChat = false;
	var chatId = 0;
	if (userId.toString().substr(0,4) == 'chat')
	{
		userIsChat = true;
		chatId = userId.toString().substr(4);
	}
	var arHistory = [];
	var nodeNeedClear = false;
	BX.removeClass(this.popupHistoryBodyWrap, 'bx-messenger-history-items-wrap-show-context');
	this.popupHistoryBodyWrap.innerHTML = '';

	var activeSearch = this.historySearch.length > 0;
	var historyElements = !historyElements? this.history: historyElements;
	if (historyElements[userId] && (!userIsChat && this.users[userId] || userIsChat && this.chat[chatId]))
	{
		var arHistorySort = BX.util.array_unique(historyElements[userId]);
		var arHistoryGroup = {};
		if (sort)
		{
			arHistorySort.sort(BX.delegate(function(i, ii) {i = parseInt(i); ii = parseInt(ii); if (!this.message[i] || !this.message[ii]){return 0;} var i1 = this.message[i].date.getTime(); var i2 = this.message[ii].date.getTime(); if (i1 > i2) { return -1; } else if (i1 < i2) { return 1;} else{ if (i > ii) { return -1; } else if (i < ii) { return 1;}else{ return 0;}}}, this));
		}
		for (var i = 0; i < arHistorySort.length; i++)
		{
			if (activeSearch && this.message[historyElements[userId][i]].text.toLowerCase().indexOf((this.historySearch+'').toLowerCase()) < 0)
				continue;

			var dateGroupTitle = BX.MessengerCommon.formatDate(this.message[historyElements[userId][i]].date, BX.MessengerCommon.getDateFormatType('MESSAGE_TITLE'));
			if (!BX('bx-im-history-'+dateGroupTitle) && !arHistoryGroup[dateGroupTitle])
			{
				arHistoryGroup[dateGroupTitle] = true;
				arHistory.push(BX.create("div", {props : { className: "bx-messenger-content-group bx-messenger-content-group-history"}, children : [
					BX.create("div", {attrs: {id: 'bx-im-history-'+dateGroupTitle}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
				]}));
			}

			var message = this.drawMessageHistory(this.message[historyElements[userId][i]]);
			if (message)
				arHistory.push(message);
		}
		if (arHistory.length <= 0)
		{
			if (!this.historySearchBegin)
			{
				nodeNeedClear = true;
				arHistory = [
					BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
						BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_NO_MESSAGE')})
					]})
				];
			}
		}
	}
	else if (this.showMessage[userId] && this.showMessage[userId].length <= 0)
	{
		nodeNeedClear = true;
		arHistory = [
			BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_NO_MESSAGE')})
			]})
		];
	}

	if (arHistory.length > 0)
	{
		BX.adjust(this.popupHistoryBodyWrap, {children: arHistory});
		this.popupHistoryItems.scrollTop = 0;
	}

	if (loadFromServer && (!this.showMessage[userId] || this.showMessage[userId] && this.showMessage[userId].length < 20))
	{
		if (nodeNeedClear)
			this.popupHistoryFilesBodyWrap.innerHTML = '';

		this.popupHistoryBodyWrap.appendChild(
			BX.create("div", { props : { className : (BX.findChildrenByClassName(this.popupHistoryBodyWrap, "bx-messenger-history-item-text")).length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history" }, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
			]})
		);
		BX.ajax({
			url: this.BXIM.pathToAjax+'?HISTORY_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_HISTORY_LOAD' : 'Y', 'USER_ID' : userId, 'USER_LOAD' : userIsChat? (this.chat[userId.toString().substr(4)] && this.chat[userId.toString().substr(4)].fake? 'Y': 'N'): (this.users[userId] && this.users[userId].fake? 'Y': 'N'), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					if (!userIsChat)
					{
						if (!this.userChat[userId])
						{
							this.userChat[userId] = data.CHAT_ID;
						}
					}

					for (var i in data.FILES)
					{
						if (!this.disk.files[data.CHAT_ID])
							this.disk.files[data.CHAT_ID] = {};
						if (this.disk.files[data.CHAT_ID][i])
							continue;
						data.FILES[i].date = new Date(data.FILES[i].date);
						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					this.showMessage[userId] = [];
					this.sendAjaxTry = 0;
					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						this.message[i] = data.MESSAGE[i];
						if (this.BXIM.settings.loadLastMessage)
							this.showMessage[userId].push(i);
					}
					for (var i in data.USERS_MESSAGE)
					{
						if (this.history[i])
							this.history[i] = BX.util.array_merge(this.history[i], data.USERS_MESSAGE[i]);
						else
							this.history[i] = data.USERS_MESSAGE[i];
					}
					if ((!userIsChat && this.users[userId] && !this.users[userId].fake) ||
						(userIsChat && this.chat[data.CHAT_ID] && !this.chat[data.CHAT_ID].fake))
					{
						BX.cleanNode(this.popupHistoryBodyWrap);
						if (!data.USERS_MESSAGE[userId] || data.USERS_MESSAGE[userId].length <= 0)
						{
							this.popupHistoryBodyWrap.appendChild(
								BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
									BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_NO_MESSAGE')})
								]})
							);
						}
						else
						{
							for (var i = 0; i < data.USERS_MESSAGE[userId].length; i++)
							{
								var dateGroupTitle = BX.MessengerCommon.formatDate(this.message[data.USERS_MESSAGE[userId][i]].date, BX.MessengerCommon.getDateFormatType('MESSAGE_TITLE'));
								var dataGroupCode = typeof(BX.translit) != 'undefined'? BX.translit(dateGroupTitle): dateGroupTitle;
								if (!BX('bx-im-history-'+dataGroupCode))
								{
									this.popupHistoryBodyWrap.appendChild(BX.create("div", {props : { className: "bx-messenger-content-group bx-messenger-content-group-history"}, children : [
										BX.create("div", {attrs: {id: 'bx-im-history-'+dataGroupCode}, props : { className: "bx-messenger-content-group-title"+(this.BXIM.language == 'ru'? ' bx-messenger-lowercase': '')}, html : dateGroupTitle})
									]}));
								}

								var message = this.drawMessageHistory(this.message[data.USERS_MESSAGE[userId][i]]);
								if (message)
									this.popupHistoryBodyWrap.appendChild(message);

							}
						}
						if (this.BXIM.settings.loadLastMessage && this.currentTab == userId)
							BX.MessengerCommon.drawTab(this.currentTab, true);
					}
					else
					{
						if (userIsChat && this.chat[data.USER_ID.substr(4)].fake)
							this.chat[data.USER_ID.toString().substr(4)].name = BX.message('IM_M_USER_NO_ACCESS');

						if (!userIsChat)
						{
							BX.MessengerCommon.getUserParam(userId, true);
							this.users[userId].name = BX.message('IM_M_USER_NO_ACCESS');
						}

						for (var i in data.USERS)
						{
							data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
							data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
							data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
							data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

							this.users[i] = data.USERS[i];
						}
						for (var i in data.USER_IN_GROUP)
						{
							if (typeof(this.userInGroup[i]) == 'undefined')
							{
								this.userInGroup[i] = data.USER_IN_GROUP[i];
							}
							else
							{
								for (var j = 0; j < data.USER_IN_GROUP[i].users.length; j++)
									this.userInGroup[i].users.push(data.USER_IN_GROUP[i].users[j]);

								this.userInGroup[i].users = BX.util.array_unique(this.userInGroup[i].users)
							}
						}
						for (var i in data.CHAT)
						{
							data.CHAT[i].date_create = new Date(data.CHAT[i].date_create);
							this.chat[i] = data.CHAT[i];
						}
						for (var i in data.USER_IN_CHAT)
						{
							this.userInChat[i] = data.USER_IN_CHAT[i];
						}
						for (var i in data.USER_BLOCK_CHAT)
						{
							this.userChatBlockStatus[i] = data.USER_BLOCK_CHAT[i];
						}
						if (!userIsChat)
							BX.MessengerCommon.userListRedraw();
						this.dialogStatusRedraw();

						this.drawHistory(userId, false, false);
					}
					if (this.historyChatId == 0)
					{
						this.historyChatId = data.CHAT_ID;
						this.drawHistoryFiles(this.historyChatId);
					}
					this.redrawHistoryPanel(userId, userIsChat? data.USER_ID.substr(4): 0);
				}
				else
				{
					if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry < 2)
					{
						this.sendAjaxTry++;
						setTimeout(BX.delegate(function(){this.drawHistory(userId, historyElements, loadFromServer)}, this), 1000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.sendAjaxTry++;
						if (BX.MessengerCommon.isDesktop())
						{
							setTimeout(BX.delegate(function (){
								this.drawHistory(userId, historyElements, loadFromServer)
							}, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.sendAjaxTry = 0;
			}, this)
		});
	}
};

BX.Messenger.prototype.redrawHistoryPanel = function(userId, chatId, params)
{
	var isChat = userId.toString().substr(0,4) == 'chat'? true: false;
	var historyPanel = null;
	params = params || {};

	BX.MessengerCommon.getUserParam(userId);
	if (isChat)
	{
		historyPanel = BX.create("div", { props : { className : "bx-messenger-panel bx-messenger-panel-bg2" }, children : [
			BX.create('span', { props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-"+this.chat[chatId].type }, children:[
				BX.create('img', { attrs : { src : this.chat[chatId].avatar, style: (BX.MessengerCommon.isBlankAvatar(this.chat[chatId].avatar)? 'background-color: '+this.chat[chatId].color: '')}, props : { className : "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.chat[chatId].avatar)? " bx-messenger-panel-avatar-img-default": "") }})
			]}),
			params.drawLinesVote == 'Y'? BX.create("a", { attrs: {'data-rating': params.sessionVoteHead, title: BX.message('IM_M_HISTORY_LINES_VOTE')+(params.sessionVoteHead > 0? ' ('+params.sessionVoteHead+'/5)': '')}, props : { className : "bx-messenger-panel-history-vote"}, events: {'click': BX.delegate(function(){ this.linesVoteHeadDialog(BX.proxy_context, params.sessionId); return BX.PreventDefault(); }, this)}}): null,
			params.drawLinesJoin == 'Y'? BX.create("a", { attrs: {title: BX.message('IM_M_HISTORY_LINES_JOIN')}, props : { className : "bx-messenger-panel-history-join"}, events: {'click': BX.delegate(function(){ this.popupHistory.close(); this.linesOpenMessenger(this.chat[chatId].entity_id)}, this)}}): null,
			this.popupHistoryButtonDeleteAll = this.chat[chatId].type == 'open' || this.chat[chatId].type == 'lines'? null: BX.create("a", { attrs: {title: BX.message('IM_M_HISTORY_DELETE_ALL')}, props : { className : "bx-messenger-panel-basket"}}),
			BX.create("span", { props : { className : "bx-messenger-panel-title bx-messenger-panel-title-middle"}, html: this.chat[chatId].name})
		]});
	}
	else
	{
		historyPanel = BX.create("div", { props : { className : "bx-messenger-panel bx-messenger-panel-bg2" }, children : [
			BX.create('a', { attrs : { href : this.users[userId].profile}, props : { className : "bx-messenger-panel-avatar bx-messenger-panel-avatar-status-"+BX.MessengerCommon.getUserStatus(this.users[userId]) }, children: [
				BX.create('img', { attrs : { src : this.users[userId].avatar, style: (BX.MessengerCommon.isBlankAvatar(this.users[userId].avatar)? 'background-color: '+this.users[userId].color: '')}, props : { className : "bx-messenger-panel-avatar-img"+(BX.MessengerCommon.isBlankAvatar(this.users[userId].avatar)? " bx-messenger-panel-avatar-img-default": "") }}),
				BX.create('span', {  attrs : { title : (BX.MessengerCommon.getUserStatus(this.users[userId], false)).title},  props : { className : "bx-messenger-panel-avatar-status" }})
			]}),
			this.popupHistoryButtonDeleteAll = userId == this.BXIM.userId? null: BX.create("a", { props : { className : "bx-messenger-panel-basket"}}),
			BX.create("span", { props : { className : "bx-messenger-panel-title"}, html: (
				this.users[userId].extranet? '<div class="bx-messenger-user-extranet">'+this.users[userId].name+'</div>':
				(this.users[userId].bot && this.bot[userId]? '<div class="'+(this.bot[userId].type == 'network'? 'bx-messenger-user-network': 'bx-messenger-user-bot')+'">'+this.users[userId].name+'</div>': this.users[userId].name)
			)}),
			BX.create("span", { props : { className : "bx-messenger-panel-desc"}, html: BX.MessengerCommon.getUserPosition(this.users[userId])})
		]});
	}

	if (this.popupHistoryPanel)
	{
		this.popupHistoryPanel.innerHTML = '';
		BX.adjust(this.popupHistoryPanel, {children: [historyPanel]});
	}
	else
	{
		return [historyPanel];
	}
}

BX.Messenger.prototype.drawHistoryFiles = function(chatId, filesElements, loadFromServer)
{
	if (this.popupHistory == null)
		return false;

	loadFromServer = typeof(loadFromServer) == 'undefined'? true: loadFromServer;

	var activeSearch = this.historyFilesSearch.length > 0;
	var filesElements = !filesElements? this.disk.files[chatId]: filesElements;
	var arFiles = [];
	var nodeNeedClear = false;
	if (filesElements)
	{
		var arFilesSort = BX.util.objectSort(filesElements, 'date', 'desc');
		for (var i = 0; i < arFilesSort.length; i++)
		{
			if (activeSearch && arFilesSort[i].name.toLowerCase().indexOf((this.historyFilesSearch+'').toLowerCase()) < 0)
				continue;

			var filesNode = this.disk.drawHistoryFiles(chatId, arFilesSort[i].id, {getElement: 'Y'});
			if (filesNode)
				arFiles.push(filesNode);
		}
		if (arFiles.length <= 0)
		{
			if (!this.historyFilesSearchBegin)
			{
				nodeNeedClear = true;
				arFiles = [
					BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
						BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_NO_FILES_2')})
					]})
				];
			}
		}
		if (arFiles.length >= 15)
		{
			loadFromServer = false;
		}
	}
	else if (chatId == 0)
	{
		nodeNeedClear = true;
		arFiles = [
			BX.create("div", { props : { className : this.popupHistoryFilesBodyWrap.childNodes.length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history" }, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_LOAD_FILES')})
			]})
		];
	}
	else
	{
		nodeNeedClear = true;
		arFiles = [
			BX.create("div", { props : { className : "bx-messenger-content-history-empty" }, children : [
				BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_NO_FILES_2')})
			]})
		];
	}

	this.popupHistoryFilesBodyWrap.innerHTML = '';
	if (arFiles.length > 0)
	{
		BX.adjust(this.popupHistoryFilesBodyWrap, {children : arFiles});
		this.popupHistoryFilesItems.scrollTop = 0;
	}

	if (loadFromServer && chatId > 0)
	{
		if (nodeNeedClear)
			this.popupHistoryFilesBodyWrap.innerHTML = '';

		this.popupHistoryFilesBodyWrap.appendChild(
			BX.create("div", { props : { className : this.popupHistoryFilesBodyWrap.childNodes.length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history" }, children : [
				BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
				BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_LOAD_FILES')})
			]})
		);

		BX.ajax({
			url: this.BXIM.pathToAjax+'?HISTORY_FILES_LOAD&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			skipAuthCheck: true,
			timeout: 30,
			data: {'IM_HISTORY_FILES_LOAD' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data && data.BITRIX_SESSID)
				{
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				}
				if (data.ERROR == '')
				{
					for (var i in data.FILES)
					{
						if (!this.disk.files[data.CHAT_ID])
							this.disk.files[data.CHAT_ID] = {};

						data.FILES[i].date = new Date(data.FILES[i].date);
						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}
					this.drawHistoryFiles(data.CHAT_ID, false, false);
				}
				else
				{
					if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry < 2)
					{
						this.sendAjaxTry++;
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
						setTimeout(BX.delegate(function(){this.drawHistoryFiles(chatId, filesElements, loadFromServer)}, this), 1000);
						BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (data.ERROR == 'AUTHORIZE_ERROR')
					{
						this.sendAjaxTry++;
						if (BX.MessengerCommon.isDesktop())
						{
							setTimeout(BX.delegate(function (){
								this.drawHistoryFiles(chatId, filesElements, loadFromServer)
							}, this), 10000);
						}
						BX.onCustomEvent(window, 'onImError', [data.ERROR]);
					}
				}
			}, this),
			onfailure: BX.delegate(function(){
				this.sendAjaxTry = 0;
			}, this)
		});
	}
};

BX.Messenger.prototype.newHistorySearch = function(event)
{
	event = event||window.event;
	if (event.keyCode == 27 && this.historySearch != '')
		BX.MessengerCommon.preventDefault(event);

	if (event.keyCode == 27)
		this.popupHistorySearchInput.value = '';


	this.historySearch = this.popupHistorySearchInput.value;
	if (this.historyLastSearch[this.historyUserId] == this.historySearch)
	{
		return false;
	}
	this.historyLastSearch[this.historyUserId] = this.historySearch;

	if (this.popupHistorySearchInput.value.length <= 3)
	{
		this.historySearch = "";
		this.drawHistory(this.historyUserId, false, false);
		return false;
	}

	this.popupHistorySearchDateInput.value = '';
	this.historyDateSearch = "";

	this.historySearchBegin = true;
	this.drawHistory(this.historyUserId, false, false);

	var elEmpty = BX.findChildByClassName(this.popupHistoryBodyWrap, "bx-messenger-content-load-history");
	if (elEmpty)
		BX.remove(elEmpty);

	var elEmpty = BX.findChildByClassName(this.popupHistoryBodyWrap, "bx-messenger-content-history-empty");
	if (elEmpty)
		BX.remove(elEmpty);

	var tmpLoadMoreWait = null;
	this.popupHistoryBodyWrap.appendChild(tmpLoadMoreWait = BX.create("div", { props : { className : this.popupHistoryBodyWrap.childNodes.length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history"}, children : [
		BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
		BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
	]}));

	clearTimeout(this.historySearchTimeout);
	if (this.popupHistorySearchInput.value != '')
	{
		this.historySearchTimeout = setTimeout(BX.delegate(function(){
			BX.ajax({
				url: this.BXIM.pathToAjax+'?HISTORY_SEARCH&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_HISTORY_SEARCH' : 'Y', 'USER_ID' : this.historyUserId, 'SEARCH' : this.popupHistorySearchInput.value, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historySearchBegin = false;
					if (data.ERROR != '')
						return false;

					if (data.MESSAGE.length == 0)
					{
						var nullResult = {};
						nullResult[data.USER_ID] = [];

						this.drawHistory(data.USER_ID, nullResult, false);
						return;
					}

					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						this.message[i] = data.MESSAGE[i];
					}

					for (var i in data.FILES)
					{
						if (!this.disk.files[data.CHAT_ID])
							this.disk.files[data.CHAT_ID] = {};

						data.FILES[i].date = new Date(data.FILES[i].date);
						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					this.drawHistory(data.USER_ID, data.USERS_MESSAGE, false);
				}, this),
				onfailure: BX.delegate(function(){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historySearchBegin = false;
				}, this)
			});
		}, this), 1500);
	}

	return BX.PreventDefault(event);
};

BX.Messenger.prototype.newHistoryDateSearch = function(params)
{
	this.historyDateSearch = this.popupHistorySearchDateInput.value;
	if (this.historyLastSearch[this.historyUserId] == this.historyDateSearch)
	{
		return false;
	}
	this.historyLastSearch[this.historyUserId] = this.historyDateSearch;

	if (this.historyDateSearch.length <= 3)
	{
		this.historyDateSearch = "";
		this.drawHistory(this.historyUserId, false, false);
		return false;
	}

	this.popupHistorySearchInput.value = '';
	this.historySearch = "";

	this.historySearchBegin = true;

	var tmpLoadMoreWait = null;
	this.popupHistoryBodyWrap.innerHTML = '';
	this.popupHistoryBodyWrap.appendChild(tmpLoadMoreWait = BX.create("div", { props : { className : this.popupHistoryBodyWrap.childNodes.length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history"}, children : [
		BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
		BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_M_LOAD_MESSAGE')})
	]}));

	clearTimeout(this.historySearchTimeout);
	if (this.historyDateSearch != '')
	{
		this.historySearchTimeout = setTimeout(BX.delegate(function(){
			BX.ajax({
				url: this.BXIM.pathToAjax+'?HISTORY_DATE_SEARCH&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_HISTORY_DATE_SEARCH' : 'Y', 'USER_ID' : this.historyUserId, 'DATE' : this.historyDateSearch, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historySearchBegin = false;
					if (data.ERROR != '')
						return false;

					if (data.MESSAGE.length == 0)
					{
						var nullResult = {};
						nullResult[data.USER_ID] = [];

						this.drawHistory(data.USER_ID, nullResult, false);
						return;
					}

					for (var i in data.MESSAGE)
					{
						data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);
						this.message[i] = data.MESSAGE[i];
					}

					for (var i in data.FILES)
					{
						if (!this.disk.files[data.CHAT_ID])
							this.disk.files[data.CHAT_ID] = {};

						data.FILES[i].date = new Date(data.FILES[i].date);
						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
					}

					this.drawHistory(data.USER_ID, data.USERS_MESSAGE, false);
				}, this),
				onfailure: BX.delegate(function(){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historySearchBegin = false;
				}, this)
			});
		}, this), 1500);
	}
};

BX.Messenger.prototype.newHistoryFilesSearch = function(event)
{
	event = event||window.event;
	if (event.keyCode == 27 && this.historyFilesSearch != '')
		BX.MessengerCommon.preventDefault(event);

	if (event.keyCode == 27)
		this.popupHistoryFilesSearchInput.value = '';

	this.historyFilesSearch = this.popupHistoryFilesSearchInput.value;
	if (this.historyFilesLastSearch[this.historyChatId] == this.historyFilesSearch)
	{
		return false;
	}
	this.historyFilesLastSearch[this.historyChatId] = this.historyFilesSearch;

	if (this.popupHistoryFilesSearchInput.value.length <= 3)
	{
		this.historyFilesSearch = "";
		this.drawHistoryFiles(this.historyChatId, false, false);
		return false;
	}

	this.historyFilesSearchBegin = true;
	this.historySearch = this.popupHistorySearchInput.value;
	this.drawHistoryFiles(this.historyChatId, false, false);

	var elEmpty = BX.findChildByClassName(this.popupHistoryFilesBodyWrap, "bx-messenger-content-load-history");
	if (elEmpty)
		BX.remove(elEmpty);

	var elEmpty = BX.findChildByClassName(this.popupHistoryFilesBodyWrap, "bx-messenger-content-history-empty");
	if (elEmpty)
		BX.remove(elEmpty);

	var tmpLoadMoreWait = null;
	this.popupHistoryFilesBodyWrap.appendChild(tmpLoadMoreWait = BX.create("div", { props : { className : this.popupHistoryFilesBodyWrap.childNodes.length>0? "bx-messenger-content-load-more-history":"bx-messenger-content-load-history"}, children : [
		BX.create('span', { props : { className : "bx-messenger-content-load-img" }}),
		BX.create("span", { props : { className : "bx-messenger-content-load-text" }, html : BX.message('IM_F_LOAD_FILES')})
	]}));

	clearTimeout(this.historyFilesSearchTimeout);
	if (this.popupHistoryFilesSearchInput.value != '')
	{
		this.historyFilesSearchTimeout = setTimeout(BX.delegate(function(){
			BX.ajax({
				url: this.BXIM.pathToAjax+'?HISTORY_FILES_SEARCH&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				timeout: 30,
				data: {'IM_HISTORY_FILES_SEARCH' : 'Y', 'CHAT_ID' : this.historyChatId, 'SEARCH' : this.popupHistoryFilesSearchInput.value, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historyFilesSearchBegin = false;

					if (data.ERROR != '')
						return false;

					if (data.FILES.length == 0)
					{
						this.drawHistoryFiles(data.CHAT_ID, false, false);
						return;
					}

					var fileFound = false;
					for (var i in data.FILES)
					{
						if (!this.disk.files[data.CHAT_ID])
							this.disk.files[data.CHAT_ID] = {};

						if (!this.disk.files[data.CHAT_ID][i])
							data.FILES[i].fromSearch = true;

						data.FILES[i].date = new Date(data.FILES[i].date);

						this.disk.files[data.CHAT_ID][i] = data.FILES[i];
						fileFound = true;
					}
					this.drawHistoryFiles(data.CHAT_ID, fileFound? data.FILES: false, false);
				}, this),
				onfailure: BX.delegate(function(){
					if (tmpLoadMoreWait)
						BX.remove(tmpLoadMoreWait);
					this.historyFilesSearchBegin = false;
				}, this)
			});
		}, this), 1500);
	}

	return BX.PreventDefault(event);
};

/* GET DATA */
BX.Messenger.prototype.setUpdateStateStep = function(send)
{
	send = send != false;

	var step = this.updateStateStepDefault;
	if (!this.BXIM.ppStatus)
	{
		if (this.popupMessenger != null)
		{
			step = 20;
			if (this.updateStateVeryFastCount > 0)
			{
				step = 5;
				this.updateStateVeryFastCount--;
			}
			else if (this.updateStateFastCount > 0)
			{
				step = 10;
				this.updateStateFastCount--;
			}
		}
	}

	this.updateStateStep = parseInt(step);

	if (send)
		BX.localStorage.set('uss', this.updateStateStep, 5);

	this.updateState();
};

BX.Messenger.prototype.updateState = function(force, send, reason)
{
	if (!this.BXIM.tryConnect || this.popupMessengerConnectionStatusState == 'offline')
		return false;

	force = force == true;
	send = send != false;
	reason = reason || 'UPDATE_STATE';

	clearTimeout(this.updateStateTimeout);
	this.updateStateTimeout = setTimeout(
		BX.delegate(function(){
			if (BX.MessengerCommon.isDesktop())
			{
				var errorText = 'IM UPDATE STATE: sending ajax'+(reason == 'UPDATE_STATE'? '': ' ('+reason+')')+' ['+this.updateStateCount+']';
				BX.desktop.log('phone.'+this.BXIM.userEmail+'.log', errorText);console.log(errorText);
			}
			var _ajax = BX.ajax({
				url: this.BXIM.pathToAjax+'?'+reason+'&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				lsId: 'IM_UPDATE_STATE',
				lsTimeout: 1,
				timeout: 30,
				data: {'IM_UPDATE_STATE' : 'Y', 'OPEN_MESSENGER' : this.popupMessenger != null? 1: 0, 'TAB' : this.currentTab, 'FM' : JSON.stringify(this.flashMessage), 'FN' :  JSON.stringify(this.notify.flashNotify), 'SITE_ID': BX.message('SITE_ID'),'IM_AJAX_CALL' : 'Y', 'DESKTOP' : (BX.MessengerCommon.isDesktop()? 'Y': 'N'), 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (send)
						BX.localStorage.set('mus', true, 5);

					if (BX.MessengerCommon.isDesktop())
					{
						var errorText = '';
						if (data.ERROR == '')
						{
							errorText = 'IM UPDATE STATE: success request ['+this.updateStateCount+']';
						}
						else
						{
							errorText = 'IM UPDATE STATE: bad request ('+data.ERROR+') ['+this.updateStateCount+']';
						}
						BX.desktop.log('phone.'+this.BXIM.userEmail+'.log', errorText);console.log(errorText);
					}
					this.updateStateCount++;

					if (data && data.BITRIX_SESSID)
					{
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					}
					if (data && data.ERROR == '')
					{
						if (!this.BXIM.checkRevision(data.REVISION))
							return false;

						if(this.BXIM.desktopDisk)
						{
							this.BXIM.desktopDisk.checkRevision(data.DISK_REVISION);
						}

						BX.message({'SERVER_TIME': data.SERVER_TIME});
						this.notify.updateNotifyCounters(data.COUNTERS, send);
						this.notify.updateNotifyMailCount(data.MAIL_COUNTER, send);

						if (!this.BXIM.xmppStatus && data.XMPP_STATUS && data.XMPP_STATUS == 'Y')
							this.BXIM.xmppStatus = true;

						if (!this.BXIM.desktopStatus && data.DESKTOP_STATUS && data.DESKTOP_STATUS == 'Y')
							this.BXIM.desktopStatus = true;

						var contactListRedraw = false;
						if (!(data.ONLINE.length <= 0))
						for (var i in data.ONLINE)
						{
							if (this.users[i])
							{
								this.users[i].status = data.ONLINE[i].status;
								this.users[i].color = data.ONLINE[i].color;
								this.users[i].idle = data.ONLINE[i].idle? new Date(data.ONLINE[i].idle): false;
								this.users[i].last_activity_date = new Date(data.ONLINE[i].last_activity_date);
								this.users[i].mobile_last_date = new Date(data.ONLINE[i].mobile_last_date);
							}
						}

						this.BXIM.messenger.command = data.COMMAND? data.COMMAND: [];
						this.BXIM.messenger.textareaIcon = data.TEXTAREA_ICON? data.TEXTAREA_ICON: [];
						this.BXIM.messenger.textareaIconPrepare();

						if (typeof(data.FILES) != "undefined")
						{
							for (var chatId in data.FILES)
							{
								if (!this.disk.files[chatId])
									this.disk.files[chatId] = {};

								for (var i in data.FILES[chatId])
								{
									data.FILES[chatId][i].date = new Date(data.FILES[chatId][i].date);
									this.disk.files[chatId][i] = data.FILES[chatId][i];
								}
							}
						}

						if (typeof(data.MESSAGE) != "undefined")
							for (var i in data.MESSAGE)
								data.MESSAGE[i].date = new Date(data.MESSAGE[i].date);

						BX.MessengerCommon.updateStateVar(data, send);
						if (typeof(data.USERS_MESSAGE) != "undefined")
							contactListRedraw = true;

						this.dialogStatusRedraw();
						BX.MessengerCommon.userListRedraw();

						if (typeof(data.NOTIFY) != "undefined")
						{
							for (var i in data.NOTIFY)
							{
								data.NOTIFY[i].date = new Date(data.NOTIFY[i].date);
								this.notify.notify[i] = data.NOTIFY[i];
								this.BXIM.lastRecordId = parseInt(i) > this.BXIM.lastRecordId? parseInt(i): this.BXIM.lastRecordId;
							}

							for (var i in data.FLASH_NOTIFY)
								if (typeof(this.notify.flashNotify[i]) == 'undefined')
									this.notify.flashNotify[i] = data.FLASH_NOTIFY[i];

							this.notify.changeUnreadNotify(data.UNREAD_NOTIFY, send);
						}

						if (BX.PULL && data.PULL_CONFIG)
						{
							BX.PULL.updateChannelID(data.PULL_CONFIG);
							BX.PULL.tryConnect();
						}

						this.setUpdateStateStep(false);
					}
					else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 2)
					{
							this.sendAjaxTry++;
							setTimeout(BX.delegate(function(){
								this.updateState(true, send, reason);
							}, this), 2000);
							BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else  if (reason != 'UPDATE_STATE_RECONNECT')
					{
						if (data.ERROR == 'AUTHORIZE_ERROR')
						{
							this.sendAjaxTry++;
							if (BX.MessengerCommon.isDesktop())
							{
								setTimeout(BX.delegate(function (){
									this.updateState(true, send, reason);
								}, this), 10000);
							}
							BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						}
						else if (this.sendAjaxTry < 5)
						{
							this.sendAjaxTry++;
							if (this.sendAjaxTry >= 2 && !BX.MessengerCommon.isDesktop())
							{
								BX.onCustomEvent(window, 'onImError', [data.ERROR]);
								return false;
							}

							setTimeout(BX.delegate(function(){
								this.updateState(true, send, reason);
							}, this), 60000);
							BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						}
						else
						{

						}
					}
				}, this),
				onfailure: BX.delegate(function()
				{
					if (BX.MessengerCommon.isDesktop())
					{
						var errorText = 'IM UPDATE STATE: failure request (code: '+_ajax.status+') ['+this.updateStateCount+']';
						BX.desktop.log('phone.'+this.BXIM.userEmail+'.log', errorText); console.log(errorText);
					}
					this.updateStateCount++;

					this.sendAjaxTry = 0;
					this.setUpdateStateStep(false);
					try {
						if (typeof(_ajax) == 'object' && _ajax.status == 0 && reason != 'UPDATE_STATE_RECONNECT')
							BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
					}
					catch(e) {}
				}, this)
			});
		}, this)
	, force? 150: this.updateStateStep*1000);
};

BX.Messenger.prototype.updateStateLight = function(force, send)
{
	if (!this.BXIM.tryConnect || this.popupMessengerConnectionStatusState == 'offline')
		return false;

	force = force == true;
	send = send != false;
	clearTimeout(this.updateStateTimeout);
	this.updateStateTimeout = setTimeout(
		BX.delegate(function(){
			BX.ajax({
				url: this.BXIM.pathToAjax+'?UPDATE_STATE_LIGHT&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				lsId: 'IM_UPDATE_STATE_LIGHT',
				lsTimeout: 1,
				timeout: this.updateStateStepDefault > 10? this.updateStateStepDefault-2: 10,
				data: {'IM_UPDATE_STATE_LIGHT' : 'Y', 'SITE_ID': BX.message('SITE_ID'), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
				onsuccess: BX.delegate(function(data)
				{
					if (send)
						BX.localStorage.set('musl', true, 5);

					if (data && data.BITRIX_SESSID)
					{
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					}

					if (data && data.ERROR == '')
					{
						if (!this.BXIM.checkRevision(data.REVISION))
							return false;

						BX.message({'SERVER_TIME': data.SERVER_TIME});

						this.notify.updateNotifyCounters(data.COUNTERS, send);

						if (BX.PULL && data.PULL_CONFIG)
						{
							BX.PULL.updateChannelID(data.PULL_CONFIG);
							BX.PULL.tryConnect();
						}

						this.updateStateLight(force, send);
					}
					else
					{
						if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 2)
						{
							this.sendAjaxTry++
							setTimeout(BX.delegate(function(){
								this.updateStateLight(true, send);
							}, this), 2000);
							BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
						}
						else if (data.ERROR == 'AUTHORIZE_ERROR')
						{
							this.sendAjaxTry++;
							if (BX.MessengerCommon.isDesktop())
							{
								setTimeout(BX.delegate(function (){
									this.updateStateLight(true, send);
								}, this), 10000);
							}
							BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						}
						else if (this.sendAjaxTry < 5)
						{
							this.sendAjaxTry++;
							if (this.sendAjaxTry >= 2 && !BX.MessengerCommon.isDesktop())
							{
								BX.onCustomEvent(window, 'onImError', [data.ERROR]);
								return false;
							}

							setTimeout(BX.delegate(function(){
								this.updateStateLight(true, send);
							}, this), 60000);
							BX.onCustomEvent(window, 'onImError', [data.ERROR]);
						}
					}
				}, this),
				onfailure: BX.delegate(function() {
					this.sendAjaxTry = 0;
					this.setUpdateStateStep(false);
					try {
						if (typeof(_ajax) == 'object' && _ajax.status == 0)
							BX.onCustomEvent(window, 'onImError', ['CONNECT_ERROR']);
					}
					catch(e) {}
				}, this)
			});
		}, this)
	, force? 150: this.updateStateStepDefault*1000);
};

BX.Messenger.prototype.setClosingByEsc = function(result)
{
	if (this.popupMessenger == null)
		return false;

	if (result)
	{
		if (!this.webrtc.callInit)
		{
			this.popupMessenger.setClosingByEsc(true);
		}
	}
	else
	{
		this.popupMessenger.setClosingByEsc(false);
	}
}
/* EXTRA */
BX.Messenger.prototype.extraOpen = function(content)
{
	if (!this.popupMessengerExtra)
		return false;

	this.setClosingByEsc(false);

	if (!this.BXIM.extraBind)
	{
		BX.bind(window, "keydown", this.BXIM.extraBind = BX.proxy(function(e) {
			if (e.keyCode == 27 && this.popupMessenger)
			{
				this.popupMessenger.destroy();
				//this.extraClose(true);
			}
		}, this));
	}

	this.BXIM.extraOpen = true;
	this.BXIM.dialogOpen = false;

	BX.style(this.popupMessengerDialog, 'display', 'none');
	BX.style(this.popupMessengerExtra, 'display', 'block');

	this.popupMessengerExtra.innerHTML = '';
	BX.adjust(this.popupMessengerExtra, {children: [content]});

	this.resizeMainWindow();
};

BX.Messenger.prototype.extraClose = function(openDialog, callToggle)
{
	if (!this.popupMessengerExtra)
		return true;

	setTimeout(BX.delegate(function(){
		this.setClosingByEsc(true);
	}, this), 200);

	if (this.BXIM.extraBind)
	{
		BX.unbind(window, "keydown", this.BXIM.extraBind);
		this.BXIM.extraBind = null;
	}

	this.BXIM.extraOpen = false;
	this.BXIM.dialogOpen = true;

	openDialog = openDialog == true;
	callToggle = callToggle != false;

	if (this.BXIM.notifyOpen)
		this.notify.closeNotify();

	this.closeMenuPopup();

	if (this.currentTab == 0)
	{
		this.extraOpen(
			BX.create("div", { props : { className : "bx-messenger-box-hello-wrap" }, children: [
				BX.create("div", { props : { className : "bx-messenger-box-hello" }, html: BX.message('IM_M_EMPTY')})
			]})
		);
	}
	else
	{
		BX.style(this.popupMessengerDialog, 'display', 'block');
		BX.style(this.popupMessengerExtra, 'display', 'none');
		this.popupMessengerExtra.innerHTML = '';

		if (openDialog)
		{
			this.openChatFlag = this.currentTab.toString().substr(0,4) == 'chat';
			BX.MessengerCommon.openDialog(this.currentTab, false, callToggle);
		}
	}
	this.resizeMainWindow();
};

/* TEXTAREA */

BX.Messenger.prototype.sendMessage = function(recipientId)
{
	if (this.popupMessengerConnectionStatusState != 'online')
		return false;

	recipientId = typeof(recipientId) == 'string' || typeof(recipientId) == 'number' ? recipientId: this.currentTab;
	BX.MessengerCommon.endSendWriting(recipientId);

	this.popupMessengerTextarea.value = this.popupMessengerTextarea.value.replace('    ', "\t");
	this.popupMessengerTextarea.value = BX.util.trim(this.popupMessengerTextarea.value);
	if (this.popupMessengerTextarea.value.length == 0)
		return false;

	if (this.popupMessengerTextarea.value.length > 20006)
	{
		this.popupMessengerTextarea.value = this.popupMessengerTextarea.value.substr(0, 20000)+' (...)';
	}

	if (this.BXIM.language=='ru' && BX.correctText && this.BXIM.settings.correctText)
	{
		this.popupMessengerTextarea.value = BX.correctText(this.popupMessengerTextarea.value);
	}

	this.addRecentSmile(this.popupMessengerTextarea.value);

	this.popupMessengerTextarea.value = this.popupMessengerTextarea.value.replace(/\[icon\=(\d+)([^\]]*)\]/ig, BX.delegate(function(whole, iconId)
	{
		iconId = 'icon'+iconId;
		var iconContent = '';
		if (this.smile[iconId].WIDTH == this.smile[iconId].HEIGHT)
		{
			iconContent = iconContent+' size='+this.smile[iconId].WIDTH;
		}
		else
		{
			if (this.smile[iconId].WIDTH)
			{
				iconContent = iconContent+' width='+this.smile[iconId].WIDTH;
			}
			if (this.smile[iconId].HEIGHT)
			{
				iconContent = iconContent+' height='+this.smile[iconId].NAME;
			}
		}
		if (this.smile[iconId].NAME)
		{
			iconContent = iconContent+' title='+this.smile[iconId].NAME;
		}

		return '[icon='+this.smile[iconId].IMAGE+iconContent+']';
	}, this));

	if (this.popupMessengerTextarea.value == '/clear')
	{
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		this.textareaHistory[this.currentTab] = '';
		this.showMessage[this.currentTab] = [];
		BX.MessengerCommon.drawTab(this.currentTab, true);

		if (BX.MessengerCommon.isDesktop())
			console.log('NOTICE: User use /clear');

		return false;
	}
	else if (this.popupMessengerTextarea.value == '/webrtcDebug' || this.popupMessengerTextarea.value == '/webrtcDebug on' || this.popupMessengerTextarea.value == '/webrtcDebug off')
	{
		if (this.popupMessengerTextarea.value == '/webrtcDebug')
			this.webrtc.debug = this.webrtc.debug? false: true;
		else if (this.popupMessengerTextarea.value == '/webrtcDebug on')
			this.webrtc.debug = true;
		else if (this.popupMessengerTextarea.value == '/webrtcDebug off')
			this.webrtc.debug = false;

		if (this.webrtc.debug)
		{
			this.tooltip(this.popupMessengerTextareaSendType.previousSibling, BX.message('IM_TIP_WEBRTC_ON'));
		}
		else
		{
			this.tooltip(this.popupMessengerTextareaSendType.previousSibling, BX.message('IM_TIP_WEBRTC_OFF'));
		}
		if (BX.PULL && BX.PULL.returnPrivateVar('_revision') >= 14)
		{
			BX.PULL.capturePullEvent(this.webrtc.debug);
		}

		this.textareaHistory[this.currentTab] = '';
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		if (console && console.log)
			console.log('NOTICE: User use /webrtcDebug and TURN '+(this.webrtc.debug? 'ON': 'OFF')+' debug');

		if (BX.MessengerCommon.isDesktop() && !this.webrtc.debug)
		{
			BX.MessengerWindow.windowReload();
		}

		return false;
	}
	else if (this.popupMessengerTextarea.value == '/windowReload')
	{
		this.textareaHistory[this.currentTab] = '';
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		location.reload();

		if (BX.MessengerCommon.isDesktop())
			console.log('NOTICE: User use /windowReload');

		return false;
	}
	else if (this.popupMessengerTextarea.value == '/correctText on' || this.popupMessengerTextarea.value == '/correctText off')
	{
		if (this.popupMessengerTextarea.value == '/correctText on')
		{
			this.BXIM.settings.correctText = true;
			this.tooltip(this.popupMessengerTextareaSendType.previousSibling, BX.message('IM_TIP_AC_ON'));
		}
		else
		{
			this.BXIM.settings.correctText = false;
			this.tooltip(this.popupMessengerTextareaSendType.previousSibling, BX.message('IM_TIP_AC_OFF'));
		}
		this.BXIM.saveSettings({'correctText': this.BXIM.settings.correctText});

		console.log('NOTICE: User use /correctText');
		return false;
	}
	else if (this.popupMessengerTextarea.value == '/getChatId')
	{
		var chatId = 0;
		if (this.openChatFlag)
		{
			chatId = this.getChatId();
		}
		else
		{
			chatId = this.userChat[this.currentTab];
		}
		this.tooltip(this.popupMessengerTextareaSendType.previousSibling, BX.message('IM_CHAT_ID_IS').replace('#CHAT_ID#', '<b>'+chatId+'</b>'));
		console.log('NOTICE: User use /getChatId');

		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		return false;
	}
	else if (this.popupMessengerTextarea.value.indexOf('/background') == 0)
	{
		var color = BX.util.trim(this.popupMessengerTextarea.value).split(" ")[1];
		if (!color)
		{
			color = this.BXIM.settings.backgroundImage? false: true;
		}

		this.BXIM.setBackground(color);
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		return false;
	}
	else if (this.popupMessengerTextarea.value.indexOf('/color') == 0)
	{
		var color = this.popupMessengerTextarea.value.split(" ")[1];
		if (color && this.openChatFlag)
		{
			BX.MessengerCommon.setColor(color, this.getChatId());
		}

		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		return false;
	}
	else if (this.popupMessengerTextarea.value.indexOf('/rename') == 0)
	{
		var title = this.popupMessengerTextarea.value.substr(8);
		if (title && this.openChatFlag)
		{
			BX.MessengerCommon.renameChat(this.getChatId(), title);
		}

		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		return false;
	}

	if (BX.MessengerCommon.isDesktop())
	{
		if (this.popupMessengerTextarea.value == '/openDeveloperTools')
		{
			this.textareaHistory[this.currentTab] = '';
			this.popupMessengerTextarea.value = '';
			this.textareaCheckText();

			BX.desktop.openDeveloperTools();

			console.log('NOTICE: User use /openDeveloperTools');
			return false;
		}
		else if (this.popupMessengerTextarea.value == '/clearWindowSize')
		{
			BX.desktop.setWindowSize({ Width: BX.MessengerWindow.initWidth, Height: BX.MessengerWindow.initHeight });
			this.BXIM.setLocalConfig('global_msz_v2', false);
			BX.desktop.apiReady = false;
			location.reload();

			if (BX.MessengerCommon.isDesktop())
				console.log('NOTICE: User use /clearWindowSize');

			return false;
		}
	}
	if (this.popupMessengerTextarea.value == '/showOnlyChat')
	{
		BX.MessengerCommon.recentListRedraw({'showOnlyChat': true});
		this.textareaHistory[this.currentTab] = '';
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();

		return false;
	}

	var chatId = recipientId.toString().substr(0,4) == 'chat'? recipientId.toString().substr(4): (this.userChat[recipientId]? this.userChat[recipientId]: 0);
	if (this.errorMessage[recipientId])
	{
		BX.MessengerCommon.sendMessageRetry();
		this.errorMessage[recipientId] = false;
	}

	this.popupMessengerTextarea.value = BX.MessengerCommon.prepareMention(recipientId, this.popupMessengerTextarea.value);

	var messageTmpIndex = this.messageTmpIndex;
	this.message['temp'+messageTmpIndex] = {'id' : 'temp'+messageTmpIndex, chatId: chatId, 'senderId' : this.BXIM.userId, 'recipientId' : recipientId, 'date' : new Date(), 'text' : BX.MessengerCommon.prepareText(this.popupMessengerTextarea.value, true) };
	if (!this.showMessage[recipientId])
		this.showMessage[recipientId] = [];
	this.showMessage[recipientId].push('temp'+messageTmpIndex);

	this.messageTmpIndex++;
	BX.localStorage.set('mti', this.messageTmpIndex, 5);
	if (this.popupMessengerTextarea == null || recipientId != this.currentTab)
		return false;

	clearTimeout(this.textareaHistoryTimeout);
	if (!BX.browser.IsAndroid() && !BX.browser.IsIOS())
		BX.focus(this.popupMessengerTextarea);

	var elLoad = BX.findChildByClassName(this.popupMessengerBodyWrap, "bx-messenger-content-load");
	if (elLoad)
		BX.remove(elLoad);

	var elEmpty = BX.findChildByClassName(this.popupMessengerBodyWrap, "bx-messenger-content-empty");
	if (elEmpty)
		BX.remove(elEmpty);

	if (recipientId.toString().substr(0,4) == 'chat' && this.linesSilentMode && this.linesSilentMode[recipientId.toString().substr(4)])
	{
		if (!this.message['temp'+messageTmpIndex].params)
		{
			this.message['temp'+messageTmpIndex].params = {};
		}
		this.message['temp'+messageTmpIndex].params.CLASS = "bx-messenger-content-item-system";
	}
	BX.MessengerCommon.drawMessage(recipientId, this.message['temp'+messageTmpIndex]);

	BX.MessengerCommon.sendMessageAjax(messageTmpIndex, recipientId, this.popupMessengerTextarea.value, recipientId.toString().substr(0,4) == 'chat');

	if (this.BXIM.settings.status != 'dnd')
	{
		this.BXIM.playSound("send");
	}

	this.textareaHistory[this.currentTab] = '';
	this.popupMessengerTextarea.value = '';
	this.textareaCheckText();

	setTimeout(BX.delegate(function(){
		this.popupMessengerTextarea.value = '';
		this.textareaCheckText();
	}, this), 0);

	return true;
};

BX.Messenger.prototype.textareaCheckText = function(params)
{
	params = params || {};
	params.textarea = params.textarea || 'default';

	var textarea = params.textarea == 'createChat'? this.popupCreateChatTextarea: this.popupMessengerTextarea;

	if (textarea.value.length > 0)
	{
		if (textarea.parentNode && textarea.parentNode.parentNode && textarea.parentNode.parentNode.className.indexOf('bx-messenger-textarea-with-text') == -1)
		{
			BX.addClass(textarea.parentNode.parentNode, 'bx-messenger-textarea-with-text');
		}
	}
	else
	{
		if (textarea.parentNode && textarea.parentNode.parentNode && textarea.parentNode.parentNode.className.indexOf('bx-messenger-textarea-with-text') >= 0)
		{
			BX.removeClass(textarea.parentNode.parentNode, 'bx-messenger-textarea-with-text');
		}
	}

	/*
	TODO: textarea auto resize
	if (textarea.offsetHeight != textarea.scrollHeight)
	{
		var textareaHeight = Math.max(Math.min(-(y-this.popupMessengerTextareaResize.pos.top) + this.popupMessengerTextareaResize.textOffset, 143), 30);

		this.popupMessengerTextareaSize = textareaHeight;
		this.popupMessengerTextarea.style.height = textareaHeight + 'px';
		this.popupMessengerBodySize = this.popupMessengerTextareaResize.textOffset-textareaHeight + this.popupMessengerTextareaResize.bodyOffset;
		this.popupMessengerBody.style.height = this.popupMessengerBodySize + 'px';
		this.popupMessengerBodyPanel.style.height = this.popupMessengerBodyDialog.offsetHeight + 'px';

		console.log('more text!!!');
	}
	*/
}

BX.Messenger.prototype.openCommandDialog = function()
{
	this.closeMenuPopup();

	var textarea =  this.popupMessengerTextarea;
	if (textarea.selectionStart == 0 || textarea.value.charCodeAt(textarea.selectionStart-1) == 10 || textarea.value.charCodeAt(textarea.selectionStart-1) == 13)
	{
		if (textarea.value.substr(-1) != "/")
		{
			this.insertTextareaText(textarea, "/");
		}
	}
	else
	{
		if (textarea.value.substr(-1) != "/")
		{
			this.insertTextareaText(textarea, "\n");
			this.insertTextareaText(textarea, "/");
		}
	}
	textarea.focus();

	this.textareaCommandListUpdate("");
}

BX.Messenger.prototype.textareaCommandListUpdate = function(command)
{
	if (this.currentTab == this.BXIM.userId)
	{
		return false;
	}

	if (command === false)
	{
		this.commandListen = false;
		this.commandSelect = '';
		this.commandSelectIndex = 1;
		if (this.commandPopup)
			this.commandPopup.close();
	}
	else
	{
		this.commandListen = true;
		this.commandList = BX.MessengerCommon.prepareCommandList(command);
		if (this.commandList.length > 0)
		{
			this.commandSelectIndex = 1;
			this.commandSelect = this.commandList[this.commandSelectIndex].command == '>>'? this.commandList[this.commandSelectIndex].command: this.commandList[this.commandSelectIndex].command.substr(1);

			var fistShow = false;
			if (!this.commandPopup)
			{
				this.commandPopup = new BX.PopupWindow('bx-messenger-command', this.popupMessengerTextareaPlace, {
					lightShadow : true,
					autoHide: true,
					offsetLeft: 5,
					bindOptions: {position: "top"},
					zIndex: 200,
					events : {
						onPopupClose : function() { this.destroy() },
						onPopupDestroy : BX.delegate(function() {
							if (this.commandPopup)
							{
								this.commandPopup = null;
								this.textareaCommandListUpdate(false);
							}
						}, this)
					},
					content: BX.create("div", { props : { className : "bx-messenger-command-popup "+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: [
						BX.create("div", { props : { className : "bx-messenger-command-popup-header"}, children: [
							BX.create("span", { props : { className : "bx-messenger-command-popup-title"}, html: BX.message('IM_COMMAND_TITLE')}),
							BX.create("span", { props : { className : "bx-messenger-command-popup-help"}, children: [
								BX.create("span", { props : { className : "bx-messenger-command-popup-help-item"}, html: BX.message('IM_COMMAND_H_1')}),
								BX.create("span", { props : { className : "bx-messenger-command-popup-help-item"}, html: BX.message('IM_COMMAND_H_2')}),
								BX.create("span", { props : { className : "bx-messenger-command-popup-help-item"}, html: BX.message('IM_COMMAND_H_3')})
							]})
						]}),
						this.commandPopupList = BX.create("div", { props : { className : "bx-messenger-command-popup-list"}, html: this.textareaCommandListItems()})
					]})
				});
				this.commandPopup.setAngle({offset: 5});
				fistShow = true;
			}
			if (fistShow)
			{
				this.commandPopup.show();
				BX.bindDelegate(this.commandPopupList, "click", {className: 'bx-messenger-command-popup-item'}, BX.delegate(function(){
					var id = BX.proxy_context.getAttribute('data-id');
					var command = '';
					for (var i = 0; i < this.command.length; i++)
					{
						if (this.command[i].id == id)
						{
							command = this.command[i].command.substr(1);
						}
					}
					this.commandSelect = command;
					this.textareaCommandClick()
				}, this));
				BX.bindDelegate(this.commandPopupList, "mouseover", {className: 'bx-messenger-command-popup-item'}, BX.delegate(function(){
					var id = BX.proxy_context.getAttribute('data-id');
					if (!id)
					{
						return true;
					}
					var command = '';
					for (var i = 0; i < this.command.length; i++)
					{
						if (this.command[i].id == id)
						{
							command = this.command[i].command.substr(1);
						}
					}
					this.commandSelectIndex = parseInt(BX.proxy_context.getAttribute('data-index'));
					this.commandSelect = command;

					var item = BX.findChildByClassName(this.commandPopupList, "bx-messenger-command-popup-item-selected");
					if (item)
					{
						BX.removeClass(item, "bx-messenger-command-popup-item-selected");
					}
					BX.addClass(BX.proxy_context, "bx-messenger-command-popup-item-selected");

					command = '/'+this.commandSelect;

					var textarea =  this.popupMessengerTextarea;
					var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/");
					var endOftextarea = textarea.value.substr(textarea.selectionStart);
					var startOftextarea = textarea.value.substr(0, selectionStart);
					textarea.value = startOftextarea+command+""+endOftextarea;
					textarea.selectionStart = selectionStart+command.length;
					textarea.selectionEnd = textarea.selectionStart;
				}, this));
			}
			else if (this.commandList.length > 0)
			{
				this.commandPopupList.innerHTML = this.textareaCommandListItems();
				this.commandPopup.adjustPosition({"forceBindPosition": true, position: "top"});
			}
		}
		else
		{
			this.commandSelectIndex = 0;
			this.commandSelect = command;

			if (this.commandPopup)
			{
				var commandPopup = this.commandPopup;
				this.commandPopup = null;
				commandPopup.close();
			}
		}
	}
}

BX.Messenger.prototype.textareaCommandListItems = function()
{
	var html = '';
	var firstSelected = false;
	for (var i = 0; i < this.commandList.length; i++)
	{
		if (this.commandList[i].type == 'category')
		{
			html += '<div class="bx-messenger-command-popup-item-category">'+this.commandList[i].title+'</div>';
		}
		else
		{
			html += '<div class="bx-messenger-command-popup-item bx-messenger-command-popup-item-'+i+' '+(this.commandSelectIndex == i? 'bx-messenger-command-popup-item-selected': '')+'" data-id="'+this.commandList[i].id+'" data-index="'+i+'">'+
						'<span class="bx-messenger-command-popup-item-text">'+
							'<span class="bx-messenger-command-popup-item-command">'+
								this.commandList[i].command+
							'</span>'+
							'<span class="bx-messenger-command-popup-item-params">'+
								this.commandList[i].params+
							'</span>'+
						'</span>'+
						'<span class="bx-messenger-command-popup-item-title">'+
							this.commandList[i].title+
						'</span>'+
					'</div>';
		}
	}
	return html;
}

BX.Messenger.prototype.textareaCommandClick = function()
{
	var command = '';
	if (this.commandSelect)
	{
		command = this.commandSelect == ">>"? ">> ": '/'+this.commandSelect+" ";
	}
	var textarea =  this.popupMessengerTextarea;
	var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/");
	var endOftextarea = textarea.value.substr(textarea.selectionStart);
	var startOftextarea = textarea.value.substr(0, selectionStart);
	textarea.value = startOftextarea+command+endOftextarea;

	textarea.selectionStart = selectionStart+command.length;
	textarea.selectionEnd = textarea.selectionStart;

	this.textareaCommandListUpdate(false);

	textarea.focus();
}

BX.Messenger.prototype.textareaCommandSelect = function(action)
{
	if (this.commandList.length <= 0 || this.commandList.length == 2)
	{
		return this.commandSelect;
	}

	if (action == 'up')
	{
		if (this.commandSelectIndex == 1)
		{
			this.commandSelectIndex = this.commandList.length-1;
		}
		else
		{
			this.commandSelectIndex -= 1;
			if (this.commandList[this.commandSelectIndex].type == 'category')
			{
				this.commandSelectIndex -= 1;
			}
		}
	}
	else
	{
		if (this.commandSelectIndex == this.commandList.length-1)
		{
			this.commandSelectIndex = 1;
		}
		else
		{
			this.commandSelectIndex += 1;
			if (this.commandList[this.commandSelectIndex].type == 'category')
			{
				this.commandSelectIndex += 1;
			}
		}
	}
	this.commandSelect = this.commandList[this.commandSelectIndex].command == '>>'? this.commandList[this.commandSelectIndex].command: this.commandList[this.commandSelectIndex].command.substr(1);

	var item = BX.findChildByClassName(this.commandPopupList, "bx-messenger-command-popup-item-selected");
	if (item)
	{
		BX.removeClass(item, "bx-messenger-command-popup-item-selected");
	}
	item = BX.findChildByClassName(this.commandPopupList, "bx-messenger-command-popup-item-"+this.commandSelectIndex);
	if (item)
	{
		BX.addClass(item, "bx-messenger-command-popup-item-selected");
		var itemVisible = BX.MessengerCommon.isElementVisibleOnScreen(item, this.commandPopupList, true);
		if (!itemVisible.top || !itemVisible.bottom)
		{
			var finish = 0;
			if (this.commandSelectIndex == this.commandList.length-1)
			{
				finish = this.commandPopupList.scrollHeight;
			}
			else if (this.commandSelectIndex > 1)
			{
				if (action == 'up')
				{
					finish = this.commandPopupList.scrollTop - (itemVisible.coords.top * -1);
				}
				else
				{
					finish = this.commandPopupList.scrollTop + itemVisible.coords.top - this.commandPopupList.offsetHeight + item.offsetHeight;
				}
			}

			if (this.commandPopupListAnimation != null)
			{
				this.commandPopupListAnimation.stop();
			}
			(this.commandPopupListAnimation = new BX.easing({
				duration : 400,
				start : { scroll : this.commandPopupList.scrollTop },
				finish : { scroll : finish},
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
				step : BX.delegate(function(state){
					this.commandPopupList.scrollTop = state.scroll;
				}, this)
			})).animate();
		}
	}

	return this.commandSelect;
}

BX.Messenger.prototype.textareaPrepareText = function(textarea, e, sendCommand, closeCommand)
{
	var result = true;

	if (this.commandListen)
	{
		if (e.altKey == true || e.ctrlKey == true || e.metaKey == true)
		{
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 8)
		{
			var previousText = textarea.value.substr(textarea.selectionStart-1, 1);
			if (previousText == '/')
			{
				this.textareaCommandListUpdate(false)
			}
			else
			{
				setTimeout(BX.delegate(function(){
					var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/")+1;
					var command = textarea.value.substr(
						selectionStart,
						textarea.selectionStart-selectionStart
					);
					this.textareaCommandListUpdate(command);
				},this), 10);
			}
		}
		else if (e.keyCode == 27)
		{
			this.commandListen = false;

			var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/");

			var endOftextarea = textarea.value.substr(textarea.selectionStart);
			var startOftextarea = textarea.value.substr(0, selectionStart+1);
			textarea.value = startOftextarea+endOftextarea;

			textarea.selectionStart = selectionStart+1;
			textarea.selectionEnd = textarea.selectionStart;

			this.textareaCommandListUpdate(false);
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 9)
		{
			this.textareaCommandSelect('down');

			command = '/'+this.commandSelect;

			var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/");

			var endOftextarea = textarea.value.substr(textarea.selectionStart);
			var startOftextarea = textarea.value.substr(0, selectionStart);
			textarea.value = startOftextarea+command+""+endOftextarea;

			textarea.selectionStart = selectionStart+command.length;
			textarea.selectionEnd = textarea.selectionStart;

			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 39 || e.keyCode == 37)
		{
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 38 || e.keyCode == 40)
		{
			if (e.keyCode == 38)
			{
				this.textareaCommandSelect('up');
			}
			else if (e.keyCode == 40)
			{
				this.textareaCommandSelect('down');
			}

			command = '/'+this.commandSelect;

			var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/");


			var endOftextarea = textarea.value.substr(textarea.selectionStart);
			var startOftextarea = textarea.value.substr(0, selectionStart);
			textarea.value = startOftextarea+command+endOftextarea;

			textarea.selectionStart = selectionStart+command.length;
			textarea.selectionEnd = textarea.selectionStart;

			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 13 || e.keyCode == 32)
		{
			this.textareaCommandClick();
			return BX.PreventDefault(e);
		}
		else
		{
			setTimeout(BX.delegate(function(){
				var selectionStart = textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/")+1;
				var command = textarea.value.substr(
					textarea.value.substr(0, textarea.selectionStart).lastIndexOf("/")+1,
					textarea.selectionStart-selectionStart
				);
				this.textareaCommandListUpdate(command);
			},this), 10);
		}
	}
	else if (this.mentionListen)
	{
		if (e.keyCode == 27)
		{
			this.mentionListen = false;
			this.mentionDelimiter = '';
			return BX.PreventDefault(e);
		}
		else if (e.keyCode == 13)
		{
			this.popupContactListSearchInput.value = '';
			var item = BX.findChildByClassName(this.popupChatDialogContactListElements, "bx-messenger-cl-item");
			if (item)
			{
				item.getAttribute('data-userId')
				var replaceText = textarea.value.substr(0, textarea.selectionEnd);
				replaceText = replaceText.substr(replaceText.lastIndexOf(this.mentionDelimiter), textarea.selectionEnd-replaceText.lastIndexOf(this.mentionDelimiter));

				textarea.value = textarea.value.replace(replaceText, item.getAttribute('data-name')+' ');
				BX.MessengerCommon.addMentionList(this.currentTab, item.getAttribute('data-name'), item.getAttribute('data-userId'));

				this.popupChatDialog.close();
			}

			return BX.PreventDefault(e);
		}
		else
		{
			setTimeout(BX.delegate(function(){
				var replaceText = textarea.value.substr(0, textarea.selectionEnd);

				var firstIndex = replaceText.lastIndexOf(this.mentionDelimiter);
				var lastIndex = textarea.selectionEnd-replaceText.lastIndexOf(this.mentionDelimiter);
				replaceText = replaceText.substr(firstIndex, lastIndex);
				if (replaceText.length <= 0 || firstIndex < 0)
				{
					if (this.popupChatDialog)
						this.popupChatDialog.close();
					return false;
				}
				replaceText = replaceText.substr(1);
				if (replaceText.substr(0, 1) == ' ')
				{
					if (this.popupChatDialog)
						this.popupChatDialog.close();
					return false;
				}
				else if (replaceText.length <= 3 && replaceText.substr(0, 1).substr(0,1).match(/\d$/))
				{
					if (this.popupChatDialog)
						this.popupChatDialog.close();
					return false;
				}

				this.popupChatDialogContactListSearch.value = replaceText;
				BX.MessengerCommon.contactListPrepareSearch('popupChatDialogContactListElements', this.popupChatDialogContactListElements, this.popupChatDialogContactListSearch.value, {
					'viewOffline': true,
					'viewChat': false,
					'viewOpenChat': true,
					'exceptUsers': [],
					'timeout': 100,
					'callback': {
						'empty': BX.delegate(function(){
							this.popupChatDialog.close();
							return false;
						}, this)
					}
				});
			},this), 10)
		}
	}
	else if (e.altKey == true && e.ctrlKey == true)
	{
	}
	else if ((e.shiftKey == true  && (e.keyCode == 61 || e.keyCode == 50 || e.keyCode == 187 || e.keyCode == 187)) || e.keyCode == 107)
	{
		var blocked = (this.BXIM.messenger.openChatFlag && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)] && this.BXIM.messenger.chat[this.BXIM.messenger.currentTab.toString().substr(4)].type == "livechat");
		if (!this.mentionListen && !blocked)
		{
			setTimeout(BX.delegate(function(){
				var delimiter = textarea.value.substr(textarea.selectionEnd-1, 1);
				if (!(delimiter == "@" || delimiter == "+"))
					return false;

				this.mentionListen = true;
				this.mentionDelimiter = delimiter;
				this.openChatDialog({'type': 'MENTION', 'bind': textarea, 'focus': false, 'delimiter': delimiter})

				this.setClosingByEsc(false);
			},this), 300)
		}
	}
	else if (e.metaKey == true || e.ctrlKey == true)
	{
		var tagReplace = {66: 'b', 83: 's', 73: 'i', 85: 'u'};
		if (tagReplace[e.keyCode] || e.keyCode == 84 || !BX.MessengerCommon.isDesktop() && BX.browser.IsChrome() && e.keyCode == 69)
		{
			var selectionStart = textarea.selectionStart;
			var selectionEnd = textarea.selectionEnd;

			resultText = textarea.value.substring(selectionStart, selectionEnd);
			if (e.keyCode == 84 || !BX.MessengerCommon.isDesktop() && BX.browser.IsChrome() && e.keyCode == 69)
			{
				if (selectionStart == selectionEnd)
				{
					selectionStart = 0;
					selectionEnd = textarea.value.length;
					resultText = textarea.value;
				}
				textarea.value = textarea.value.substring(0, selectionStart)+BX.correctText(resultText, {replace_way: 'AUTO', mixed:true})+textarea.value.substring(selectionEnd, textarea.value.length);
				textarea.selectionStart = selectionStart;
				textarea.selectionEnd = selectionEnd;
			}
			else
			{
				if (selectionStart == selectionEnd)
				{
					return BX.PreventDefault(e);
				}
				resultTagStart = textarea.value.substring(selectionStart, selectionStart+3);
				resultTagEnd = textarea.value.substring(selectionEnd-4, selectionEnd);

				if (resultTagStart.toLowerCase() == '['+tagReplace[e.keyCode]+']' && resultTagEnd.toLowerCase() == '[/'+tagReplace[e.keyCode]+']')
				{
					textarea.value = textarea.value.substring(0, selectionStart)+textarea.value.substring(selectionStart+3, selectionEnd-4)+textarea.value.substring(selectionEnd, textarea.value.length)
					textarea.selectionStart = selectionStart;
					textarea.selectionEnd = selectionEnd-7;
				}
				else
				{
					textarea.value = textarea.value.substring(0, selectionStart)+'['+tagReplace[e.keyCode]+']'+resultText+'[/'+tagReplace[e.keyCode]+']'+textarea.value.substring(selectionEnd, textarea.value.length);
					textarea.selectionStart = selectionStart;
					textarea.selectionEnd = selectionEnd+7;
				}
			}
			return BX.PreventDefault(e);
		}
	}
	else if ((e.keyCode == 191 || e.keyCode == 111 || e.keyCode == 220) && textarea == this.popupMessengerTextarea)
	{
		if (textarea.selectionStart == 0 || textarea.value.charCodeAt(textarea.selectionStart-1) == 10 || textarea.value.charCodeAt(textarea.selectionStart-1) == 13)
		{
			setTimeout(BX.delegate(function(){
				var delimiter = textarea.value.substr(textarea.selectionEnd-1, 1);
				if (delimiter == '/')
				{
					this.textareaCommandListUpdate("");
				}
			},this), 300)
		}
	}
	if (e.keyCode == 9)
	{
		this.insertTextareaText(textarea, "\t");
		return BX.PreventDefault(e);
	}
	if (e.keyCode == 27 && !BX.MessengerCommon.isDesktop())
	{
		if (e.shiftKey)
		{
			closeCommand();
		}
		else if (textarea == this.popupCreateChatTextarea)
		{
			if (this.popupCreateChatTextarea.value == "")
			{
				closeCommand();
			}
			else
			{
				return BX.PreventDefault(e);
			}
		}
		else if (textarea != this.popupMessengerTextarea || this.popupMessengerTextarea.value == "")
		{
			closeCommand();
		}
	}
	else if (e.keyCode == 38 && this.popupMessengerLastMessage > 0 && BX.util.trim(textarea.value).length <= 0)
	{
		this.editMessage(this.popupMessengerLastMessage);
	}
	else if (this.BXIM.settings.sendByEnter == true && (e.ctrlKey == true || e.altKey == true) && e.keyCode == 13)
		this.insertTextareaText(textarea, "\n");
	else if (this.BXIM.settings.sendByEnter == true && e.shiftKey == false && e.keyCode == 13)
		result = sendCommand();
	else if (this.BXIM.settings.sendByEnter == false && e.ctrlKey == true && e.keyCode == 13)
		result = sendCommand();
	else if (this.BXIM.settings.sendByEnter == false && (e.metaKey == true || e.altKey == true) && e.keyCode == 13 && BX.browser.IsMac())
		result = sendCommand();

	clearTimeout(this.textareaHistoryTimeout);
	this.textareaHistoryTimeout = setTimeout(BX.delegate(function(){
		this.textareaHistory[this.currentTab] = this.popupMessengerTextarea.value;
	}, this), 200);

	if (BX.util.trim(textarea.value).length > 2)
		BX.MessengerCommon.sendWriting(this.currentTab);

	if (!result)
		return BX.PreventDefault(e);
}

BX.Messenger.prototype.openAnswersMenu = function(params)
{
	this.BXIM.openConfirm(BX.message('IM_OL_ANSWERS_SOON'), [
		new BX.PopupWindowButton({
			text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
			className : "popup-window-button-decline",
			events : { click : function() { this.popupWindow.close(); } }
		})
	], true);
}
BX.Messenger.prototype.openFormsMenu = function(params)
{
	this.BXIM.openConfirm(BX.message('IM_OL_FORMS_SOON'), [
		new BX.PopupWindowButton({
			text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
			className : "popup-window-button-decline",
			events : { click : function() { this.popupWindow.close(); } }
		})
	], true);
}

BX.Messenger.prototype.addRecentSmile = function(text, icon)
{
	icon = icon || '';
	if (BX.MessengerCommon.isDesktop() && BX.browser.IsMac() && !this.desktop.enableInVersion(36))
		return false;

	var foundIcons = text.match(/\[icon\=([^\]]*)\]/ig);
	var saveNew = false;
	if (foundIcons && foundIcons.length)
	{
		var currentRecent = [];
		var smilesRecent = this.BXIM.getLocalConfig('smiles-recent', []) || [];
		for (var i = 0; i < smilesRecent.length; i++)
		{
			currentRecent.push(smilesRecent[i].IMAGE);
		}
		for (var i = 0; i < foundIcons.length; i++)
		{
			var whole = foundIcons[i];
			var url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);
			if (url && url[1])
			{
				url = url[1];
				if (currentRecent && currentRecent.indexOf(url) > -1 || url.match(/^(\d+)$/))
				{
					continue;
				}
			}
			else
			{
				continue;
			}

			if (icon && icon.indexOf(url) < 0)
			{
				continue;
			}

			saveNew = true;

			var attrs = {'IMAGE': url, 'HEIGHT': 20, 'WIDTH': 20, 'NAME': ''};

			var size = whole.match(/size\=(\d+)/i);
			if (size && size[1])
			{
				attrs['WIDTH'] = size[1];
				attrs['HEIGHT'] = size[1];
			}
			else
			{
				var width = whole.match(/width\=(\d+)/i);
				if (width && width[1])
				{
					attrs['WIDTH'] = width[1];
				}

				var height = whole.match(/height\=(\d+)/i);
				if (height && height[1])
				{
					attrs['HEIGHT'] = height[1];
				}

				if (attrs['WIDTH'] && !attrs['HEIGHT'])
				{
					attrs['HEIGHT'] = attrs['WIDTH'];
				}
				else if (attrs['HEIGHT'] && !attrs['WIDTH'])
				{
					attrs['WIDTH'] = attrs['HEIGHT'];
				}
				else
				{
					attrs['WIDTH'] = 20;
					attrs['HEIGHT'] = 20;
				}
			}

			var title = whole.match(/title\=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];
				if (title.indexOf('width=') > -1)
				{
					title = title.substr(0, title.indexOf('width='))
				}
				if (title.indexOf('height=') > -1)
				{
					title = title.substr(0, title.indexOf('height='))
				}
				if (title.indexOf('size=') > -1)
				{
					title = title.substr(0, title.indexOf('size='))
				}
				if (title)
				{
					title = BX.util.trim(title);
					attrs['NAME'] = title;
				}
			}
			smilesRecent.push(attrs);
			this.injectRecentSmile(attrs);
		}
		if (saveNew)
		{
			this.BXIM.setLocalConfig('smiles-recent', smilesRecent, 2600000);
		}
	}

	return foundIcons? foundIcons.length: 0;
}

BX.Messenger.prototype.removeRecentSmile = function(id)
{
	if (BX.MessengerCommon.isDesktop() && BX.browser.IsMac() && !this.desktop.enableInVersion(36))
		return false;

	var deleteImage = '';
	if (this.smile[id])
	{
		deleteImage = this.smile[id].IMAGE;
	}

	if (deleteImage)
	{
		var currentRecent = [];
		var smilesRecent = this.BXIM.getLocalConfig('smiles-recent', []) || [];
		for (var i = 0; i < smilesRecent.length; i++)
		{
			if (deleteImage != smilesRecent[i].IMAGE)
			{
				currentRecent.push(smilesRecent[i]);
			}
		}
		this.BXIM.setLocalConfig('smiles-recent', currentRecent, 2600000);

		delete this.smile[id];
	}

	return true
}

BX.Messenger.prototype.getRecentSmiles = function()
{
	if (BX.MessengerCommon.isDesktop() && BX.browser.IsMac() && !this.desktop.enableInVersion(36))
		return false;

	if (!this.smileSet)
		return false;

	this.smileSet.push({
		'ID': 'icons',
		'NAME': BX.message('IM_ICON_SET'),
		'PARENT_ID': 0,
		'TYPE': 'G'
	});

	var smilesRecent = this.BXIM.getLocalConfig('smiles-recent', []) || [];
	if (smilesRecent.length <= 0)
	{
		return true;
	}

	this.smileRecentId = smilesRecent.length+1;
	for (var i = 0; i < smilesRecent.length; i++)
	{
		this.injectRecentSmile(smilesRecent[i]);
	}
}
BX.Messenger.prototype.injectRecentSmile = function(params)
{
	var smile = BX.clone(params);
	if (typeof(smile) != 'object')
		return false;

	smile.TITLE = smile.NAME;
	if (!smile.TITLE)
	{
		smile.TITLE = smile.IMAGE.substring(smile.IMAGE.lastIndexOf('/')+1);
		smile.TITLE = smile.TITLE.substring(0, smile.TITLE.lastIndexOf('.'));
	}
	this.smile['icon'+this.smileRecentId] = {
		'NAME': smile.NAME,
		'HEIGHT': smile.HEIGHT>100? 100: smile.HEIGHT,
		'WIDTH': smile.WIDTH>100? 100: smile.WIDTH,
		'IMAGE': smile.IMAGE,
		'TYPING': '[icon='+this.smileRecentId+' title='+smile.TITLE+']',
		'SET_ID': 'icons'
	};
	this.smileRecentId++;
}


BX.Messenger.prototype.openSmileMenu = function(params)
{
	params = params || {};
	params.textarea = params.textarea || 'default';
	params.bind = params.bind || this.popupMessengerSmileButton;

	this.closePopupFileMenu();

	if (this.popupPopupMenu != null)
		this.popupPopupMenu.destroy();

	if (this.popupChatDialog != null)
	{
		this.popupChatDialog.destroy();
	}
	if (this.popupSmileMenu != null)
	{
		this.popupSmileMenu.destroy();
	}
	if (this.commandPopup != null)
	{
		this.commandPopup.destroy();
	}
	if (this.popupIframeMenu != null && this.popupIframeBind)
	{
		this.popupIframeMenu.destroy();
	}

	if (this.smile == false)
	{
		this.tooltip(this.popupMessengerSmileButton, BX.message('IM_SMILE_NA'), {offsetLeft: -20});
		return false;
	}

	var arGalleryItem = {};
	for (var id in this.smile)
	{
		if (!arGalleryItem[this.smile[id].SET_ID])
			arGalleryItem[this.smile[id].SET_ID] = [];

		var typing = BX.util.htmlspecialcharsback(this.smile[id].TYPING);

		arGalleryItem[this.smile[id].SET_ID].push(
			BX.create("img", { props : { className : 'bx-messenger-smile-gallery-image'}, attrs : { 'data-id': id, 'data-code': typing, 'data-textarea': params.textarea,  style: "width: "+this.smile[id].WIDTH+"px; height: "+this.smile[id].HEIGHT+"px", src : this.smile[id].IMAGE, alt : this.smile[id].TYPING, title : BX.util.htmlspecialcharsback(this.smile[id].NAME)}})
		);
	}

	var setCount = 0;
	var arGallery = [];
	var arSet = [
		BX.create("span", { props : { className : "bx-messenger-smile-nav-name" }, html: BX.message('IM_SMILE_SET')})
	];

	if (!this.smileSet[this.smileCurrentSet] || typeof(arGalleryItem[this.smileSet[this.smileCurrentSet]['ID']]) == 'undefined')
	{
		this.smileCurrentSet = 0;
	}

	var id = 0;
	var name = '';
	for (var i = 0; i < this.smileSet.length; i++)
	{
		if (typeof(arGalleryItem[this.smileSet[i]['ID']]) == 'undefined')
			continue;

		id = this.smileSet[i]['ID'];
		name = this.smileSet[i]['NAME'];

		arGallery.push(
			BX.create("span", { attrs : { 'data-set-id': id }, props : { className : "bx-messenger-smile-gallery-set"+(setCount != this.smileCurrentSet? ' bx-messenger-smile-gallery-set-hide': '') }, children: arGalleryItem[id]})
		);
		arSet.push(
			BX.create("span", { attrs : { 'data-set-id': id, title : BX.util.htmlspecialcharsback(name) }, props : { className : "bx-messenger-smile-nav-item"+(setCount == this.smileCurrentSet? ' bx-messenger-smile-nav-item-active': '')}})
		);
		setCount++;
	}

	this.popupSmileMenu = new BX.PopupWindow('bx-messenger-popup-smile', params.bind, {
		//parentPopup: this.popupMessenger,
		lightShadow : false,
		offsetTop: 0,
		offsetLeft: -38,
		autoHide: true,
		closeByEsc: true,
		bindOptions: {position: "top"},
		zIndex: 200,
		events : {
			onPopupClose : function() { this.destroy() },
			onPopupDestroy : BX.delegate(function() { this.popupSmileMenu = null; }, this)
		},
		content : BX.create("div", { props : { className : "bx-messenger-smile"+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: [
			this.popupSmileMenuGallery = BX.create("div", { props : { className : "bx-messenger-smile-gallery" }, children: arGallery}),
			this.popupSmileMenuSet = BX.create("div", { props : { className : "bx-messenger-smile-nav"+(setCount <= 1? " bx-messenger-smile-nav-disabled": "")}, children: arSet})
		]})
	});
	this.popupSmileMenu.setAngle({offset: 74});
	this.popupSmileMenu.show();

	BX.bindDelegate(this.popupSmileMenuGallery, "click", {className: 'bx-messenger-smile-gallery-image'}, BX.delegate(function(){
		var textarea = BX.proxy_context.getAttribute('data-textarea') == 'createChat'? this.popupCreateChatTextarea: this.popupMessengerTextarea;
		this.insertTextareaText(textarea, ' '+BX.proxy_context.getAttribute('data-code')+' ', false);
		this.popupSmileMenu.close();
		textarea.focus();
	}, this));

	BX.bindDelegate(this.popupSmileMenuGallery, "contextmenu", {className: 'bx-messenger-smile-gallery-image'}, BX.delegate(function(e){
		var foundIcons = BX.proxy_context.getAttribute('data-code').match(/\[icon\=([^\]]*)\]/ig);
		if (foundIcons)
		{
			this.openPopupMenu(BX.proxy_context, 'iconMenu', true, {closeSmiles: false});
			return BX.PreventDefault(e);
		}
	}, this));

	BX.bindDelegate(this.popupSmileMenuSet, "click", {className: 'bx-messenger-smile-nav-item'}, BX.delegate(function(){
		if (BX.hasClass(BX.proxy_context, 'bx-messenger-smile-nav-item-active'))
			return false;

		var nodesGallery = BX.findChildrenByClassName(this.popupSmileMenuGallery, "bx-messenger-smile-gallery-set", false);
		var nodesSet = BX.findChildrenByClassName(this.popupSmileMenuSet, "bx-messenger-smile-nav-item", false);
		for (var i = 0; i < nodesSet.length; i++)
		{
			if (BX.proxy_context == nodesSet[i])
			{
				BX.removeClass(nodesGallery[i], 'bx-messenger-smile-gallery-set-hide');
				BX.addClass(nodesSet[i], 'bx-messenger-smile-nav-item-active');
				this.smileCurrentSet = i;
				this.BXIM.setLocalConfig('smiles-current-set', i);
			}
			else
			{
				BX.addClass(nodesGallery[i], 'bx-messenger-smile-gallery-set-hide');
				BX.removeClass(nodesSet[i], 'bx-messenger-smile-nav-item-active');
			}
		}
	}, this));

	BX.onCustomEvent('onImOpenSmileMenu', []);

	return false;
};

BX.Messenger.prototype.textareaIconToggle = function()
{
	if (!this.popupMessengerPanelBotIcons)
	{
		return true;
	}

	var elements = BX.findChildrenByClassName(this.popupMessengerTextareaIconBox, "bx-messenger-textarea-icon-bot", true);
	if (!elements)
	{
		this.popupMessengerPanelBotIcons = false;
		return false;
	}

	for (var i = 0; i < elements.length; i++)
	{
		BX.removeClass(elements[i], 'bx-messenger-textarea-icon-bot-show');
	}

	this.popupMessengerPanelBotIcons = false;

	if (this.openBotFlag)
	{
		var elements = BX.findChildrenByClassName(this.popupMessengerTextareaIconBox, "bx-messenger-textarea-icon-bot-"+this.currentTab, true);
		if (elements)
		{
			for (var i = 0; i < elements.length; i++)
			{
				BX.addClass(elements[i], 'bx-messenger-textarea-icon-bot-show');
			}
			this.popupMessengerPanelBotIcons = true;
		}
	}

	return true;
}

BX.Messenger.prototype.textareaIconCheckContext = function(context)
{
	// context: all, chat, bot, lines, user, call ( postfix - admin)
	var isAdmin = context.substr(-6) == '-admin';
	if (isAdmin && !this.BXIM.isAdmin)
	{
		return false;
	}
	if (isAdmin)
	{
		context = context.substr(0, context.length-6);
	}

	if (context == 'chat')
	{
		if (!this.openChatFlag)
		{
			return false;
		}
	}
	else if (context == 'bot')
	{
		if (!this.openBotFlag)
		{
			return false;
		}
	}
	else if (context == 'lines')
	{
		if (!this.openLinesFlag)
		{
			return false;
		}
	}
	else if (context == 'call')
	{
		if (!this.openCallFlag)
		{
			return false;
		}
	}
	else if (context == 'user')
	{
		if (this.openCallFlag || this.openChatFlag || this.openLinesFlag)
		{
			return false;
		}
	}

	return true;
}

BX.Messenger.prototype.textareaIconPrepare = function()
{
	if (!this.popupMessengerTextareaIconBox)
		return false;

	this.popupMessengerTextareaIconBox.innerHTML = '';

	if (!this.textareaIcon.length)
	{
		return false;
	}

	var textareaIcon = null;

	var textareaApps = [];
	var textareaAppsClass = [];
	for (var i = 0; i < this.textareaIcon.length; i++)
	{
		if (!this.textareaIcon[i] || this.textareaIcon[i].hidden)
		{
			continue;
		}

		if (this.desktop.ready() && !this.desktop.enableInVersion(39) && this.textareaIcon[i]['iframe'])
		{
			if (BXDesktopSystem.GetProperty('versionParts').join('.') != '5.0.32.38') // TODO remove this
			{
				continue;
			}
		}

		var title = this.textareaIcon[i]['description']? this.textareaIcon[i]['description']: this.textareaIcon[i]['title'];

		if (!this.textareaIcon[i]['title'] && !this.textareaIcon[i]['url'])
		{
			continue;
		}

		var textareaIconClass = "bx-messenger-textarea-icon-marketplace-"+this.textareaIcon[i]['id']+" bx-messenger-textarea-icon-context-"+this.textareaIcon[i]['context']+(this.textareaIcon[i]['context'] == 'bot' || this.textareaIcon[i]['context'] == 'bot-admin'? ' bx-messenger-textarea-icon-bot bx-messenger-textarea-icon-bot-'+this.textareaIcon[i]['botId']: '');

		if (!this.textareaIcon[i]['url'])
		{
			textareaApps.push(this.textareaIcon[i]);
			textareaAppsClass.push(textareaIconClass);
			continue;
		}

		textareaIcon = BX.create("div", {
			props : { className : "bx-messenger-textarea-icon-marketplace "+textareaIconClass},
			attrs : { title: title, style: "background-image: url('"+this.textareaIcon[i]['url']+"')", "data-context": this.textareaIcon[i]['context'], "data-code": this.textareaIcon[i]['code'], "data-id": this.textareaIcon[i]['id'] },
			events : { click : BX.delegate(this.textareaIconClick, this)}
		});
		this.popupMessengerTextareaIconBox.appendChild(textareaIcon);
	}
	if (textareaApps.length)
	{
		this.popupMessengerTextareaIconApps = BX.create("div", {
			props : { className : "bx-messenger-textarea-icon-marketplace bx-messenger-textarea-icon-marketplace-default "+textareaAppsClass.join(" ")},
			attrs : { title: BX.message('IM_APPS_LIST') },
			events : { click : BX.delegate(function(e){
				this.openPopupMenu(BX.proxy_context, 'textareaAppsMenu');
			}, this)}
		});
		this.popupMessengerTextareaIconBox.appendChild(this.popupMessengerTextareaIconApps);
	}

	return true;
}

BX.Messenger.prototype.textareaIconDialogClick = function(id, messageId, params)
{
	params = params || {};

	var icon = null;
	for (var i = 0; i < this.textareaIcon.length; i++)
	{
		if (!this.textareaIcon[i] || this.textareaIcon[i].id != id)
		{
			continue;
		}

		icon = this.textareaIcon[i];
		break;
	}

	if (!icon && !params.___ajaxSkip)
	{
		BX.ajax({
			url: this.BXIM.pathToAjax+'?GET_TEXTAREA_ICONS&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 60,
			data: {'IM_GET_TEXTAREA_ICONS': 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data){
				this.textareaIcon = data.TEXTAREA_ICON? data.TEXTAREA_ICON: [];
				this.textareaIconPrepare();

				params.___ajaxSkip = true;
				this.textareaIconDialogClick(id, messageId, params)
			}, this)
		});

		return false;
	}

	delete params.___ajaxSkip;

	if (this.textareaIconCheckContext(icon.context))
	{
		if (icon.iframe)
		{
			var dialogContext = 'user';
			var dialogEntityId = '';
			var dialogEntityData1 = '';
			if (this.currentTab.toString().substr(0,4) == 'chat')
			{
				dialogContext = this.chat[this.currentTab.substr(4)].entity_type.toLowerCase();
				dialogEntityId = this.chat[this.currentTab.substr(4)].entity_id;
				dialogEntityData1 = this.chat[this.currentTab.substr(4)].entity_data_1;
			}
			this.openFrameDialog({
				'bind': null,
				'title': icon.title,
				'copyright': icon.copyright,
				'iframe': {src: icon.iframe, width: icon.iframeWidth, height: icon.iframeHeight, popup: true},
				'params': {
					BOT_ID: icon.botId,
					BOT_CODE: icon.botCode,
					APP_ID: icon.id,
					APP_CODE: icon.code,
					DOMAIN: location.origin,
					DOMAIN_HASH: icon.domainHash,
					USER_ID: this.BXIM.userId,
					USER_HASH: icon.userHash,
					DIALOG_ID: this.currentTab,
					DIALOG_CONTEXT: dialogContext,
					DIALOG_ENTITY_ID: dialogEntityId,
					DIALOG_ENTITY_DATA_1: dialogEntityData1,
					LANG: BX.message.LANGUAGE_ID,
					IS_CHROME: BX.browser.IsChrome()? 'Y': 'N',
					CONTEXT: 'button',
					MESSAGE_ID: messageId,
					BUTTON_PARAMS: params
				}
			});
		}
		else if (icon.js)
		{
			var button = BX.proxy_context;
			eval(icon.js);
		}
	}
}

BX.Messenger.prototype.textareaIconClick = function(event)
{
	if (this.popupPopupMenu != null)
	{
		this.popupPopupMenu.destroy();
	}

	var icon = null;
	for (var i = 0; i < this.textareaIcon.length; i++)
	{
		if (!this.textareaIcon[i] || this.textareaIcon[i].id != BX.proxy_context.getAttribute('data-id') || this.textareaIcon[i].hidden)
		{
			continue;
		}

		icon = this.textareaIcon[i];
		break;
	}
	if (!icon)
	{
		return false;
	}

	if (this.textareaIconCheckContext(icon.context))
	{
		if (icon.iframe)
		{
			var dialogContext = 'user';
			var dialogEntityId = '';
			var dialogEntityData1 = '';
			if (this.currentTab.toString().substr(0,4) == 'chat')
			{
				dialogContext = this.chat[this.currentTab.substr(4)].entity_type.toLowerCase();
				dialogEntityId = this.chat[this.currentTab.substr(4)].entity_id;
				dialogEntityData1 = this.chat[this.currentTab.substr(4)].entity_data_1;;
			}

			this.openFrameDialog({
				'bind': event? BX.proxy_context: this.popupMessengerTextareaIconApps,
				'title': icon.title,
				'copyright': icon.copyright,
				'iframe': {src: icon.iframe, width: icon.iframeWidth, height: icon.iframeHeight, popup: icon.iframePopup},
				'params': {
					BOT_ID: icon.botId,
					BOT_CODE: icon.botCode,
					APP_ID: icon.id,
					APP_CODE: icon.code,
					DOMAIN: location.origin,
					DOMAIN_HASH: icon.domainHash,
					USER_ID: this.BXIM.userId,
					USER_HASH: icon.userHash,
					DIALOG_ID: this.currentTab,
					DIALOG_CONTEXT: dialogContext,
					DIALOG_ENTITY_ID: dialogEntityId,
					DIALOG_ENTITY_DATA_1: dialogEntityData1,
					LANG: BX.message.LANGUAGE_ID,
					IS_CHROME: BX.browser.IsChrome()? 'Y': 'N',
					CONTEXT: 'textarea'
				}
			});
		}
		else if (icon.js)
		{
			var button = BX.proxy_context;
			eval(icon.js);
		}
	}

	return event? BX.PreventDefault(event): true;
}

BX.Messenger.prototype.openFrameDialog = function(params)
{
	params = params || {};

	if (params.iframe && params.iframe.popup)
	{
		params.bind = null;
	}
	else
	{
		params.bind = params.bind || null;
	}

	this.closePopupFileMenu();

	if (this.popupPopupMenu != null)
	{
		this.popupPopupMenu.destroy();
	}
	if (this.popupChatDialog != null)
	{
		this.popupChatDialog.destroy();
	}
	if (this.popupSmileMenu != null)
	{
		this.popupSmileMenu.destroy();
	}
	if (this.commandPopup != null)
	{
		this.commandPopup.destroy();
	}
	if (this.popupIframeMenu != null)
	{
		this.popupIframeMenu.destroy();
	}

	this.openFrameDialogBid = params.params.BOT_ID;
	this.openFrameDialogDid = this.currentTab;

	if (this.sendFrameTokenCollection[this.openFrameDialogBid])
	{
		if (this.sendFrameTokenCollection[this.openFrameDialogBid]+(this.sendFrameTokenTimeout*1000) < +new Date())
		{
			this.sendFrameToken(this.openFrameDialogBid, this.openFrameDialogDid);
		}
	}
	else
	{
		this.sendFrameToken(this.openFrameDialogBid, this.openFrameDialogDid);
	}

	var iframeUrl = '';
	for (var i in params.params)
	{
		iframeUrl = iframeUrl+i+'='+encodeURIComponent(params.params[i])+'&'
	}
	iframeUrl = params.iframe.src+iframeUrl;

	params.iframe.height = parseInt(params.iframe.height);
	if (params.iframe.height > this.popupMessengerBody.offsetHeight)
	{
		params.iframe.height = this.popupMessengerBody.offsetHeight;
	}

	this.popupIframeBind = !!params.bind;

	this.popupIframeMenu = new BX.PopupWindow('bx-messenger-iframe', params.bind, {
		//parentPopup: this.popupMessenger,
		lightShadow : false,
		offsetTop: 0,
		offsetLeft: -38,
		autoHide: this.popupIframeBind,
		closeByEsc: true,
		bindOptions: {position: "top"},
		closeIcon : params.bind? null: {'right': '13px'},
		draggable : params.bind? null: {'restrict': true},
		zIndex: 200,
		events : {
			onPopupClose : function() { this.destroy() },
			onPopupDestroy : BX.delegate(function() {
				this.openFrameDialogBid = null;
				this.openFrameDialogDid = null;
				this.popupIframeMenu = null;
				this.popupIframeBind =  true;
				this.openFrameDialogFrame = null;
				this.openFrameDialogFrameSourceDomain = null;
			}, this)
		},
		content: BX.create("div", { props : { className : "bx-messenger-iframe-title-box"}, children: [
			this.openFrameDialogTitle = BX.create("div", { props : { className : "bx-messenger-command-popup-header"}, children: [
				BX.create("span", { props : { className : "bx-messenger-command-popup-title"}, text: params.title}),
				BX.create("span", { props : { className : "bx-messenger-command-popup-help"}, children: [
					BX.create("span", { props : { className : "bx-messenger-command-popup-help-item"}, text: params.copyright})
				]})
			]}),
			this.openFrameDialogFrame = BX.create("iframe", {
				attrs : { frameborder: 0, src: iframeUrl, style: 'min-width: '+parseInt(params.iframe.width)+'px; min-height: '+parseInt(params.iframe.height)+'px; max-height: 100%; max-width: 100%;', sandbox: "allow-same-origin allow-forms allow-scripts allow-popups"},
				props : { className : "bx-messenger-iframe-element"+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll')}
			})
		]})
	});
	if (params.bind)
	{
		this.popupIframeMenu.setAngle({offset: 74});
	}
	else
	{
		this.openFrameDialogTitle.style.cursor = "move";
		BX.bind(this.openFrameDialogTitle, "mousedown", BX.proxy(this.popupIframeMenu.onTitleMouseDown, this.popupIframeMenu));
	}
	this.popupIframeMenu.show();

	BX.bind(this.openFrameDialogFrame, 'load', BX.delegate(this.openFrameDialogLoad, this));

	if (iframeUrl.indexOf('http') === 0)
	{
		var sourceHref = document.createElement('a');
		sourceHref.href = iframeUrl;

		this.openFrameDialogFrameSourceDomain = sourceHref.protocol+'//'+sourceHref.hostname+(sourceHref.port && sourceHref.port != '80' && sourceHref.port != '443'? ":"+sourceHref.port: "");
	}
	else
	{
		this.openFrameDialogFrameSourceDomain = location.protocol+'//'+location.hostname+(location.port && location.port != '80' && location.port != '443'? ":"+location.port: "");
	}

	BX.onCustomEvent('onImOpenFrameDialog', []);

	return false;
};

BX.Messenger.prototype.openFrameDialogLoad = function(params)
{
	var ie = 0 /*@cc_on + @_jscript_version @*/;
	if(typeof window.postMessage === 'function' && !ie)
	{
		this.openFrameDialogFrameUid = Math.random().toString().substr(2);
		this.openFrameDialogFrame.contentWindow.postMessage(JSON.stringify({
			'action': 'init',
			'domain': location.origin,
			'uniqueLoadId': this.openFrameDialogFrameUid
		}), this.openFrameDialogFrameSourceDomain);
	}
}

BX.Messenger.prototype.openFrameDialogPostMessage = function(params)
{
	var data = {};
	try { data = JSON.parse(params); } catch (err){}
	if(!data.action) return;

	if (this.openFrameDialogFrameUid != data.uniqueLoadId) return;

	if (data.action == 'send')
	{
		this.BXIM.sendMessage(data.message);
	}
	else if (data.action == 'put')
	{
		this.BXIM.putMessage(data.message);
		this.BXIM.messenger.textareaCheckText();
	}
	else if (data.action == 'call')
	{
		this.BXIM.phoneTo(data.number);
	}
	else if (data.action == 'support')
	{
		this.BXIM.openMessenger("networkLines"+data.code, null, true);
	}
	else if (data.action == 'close')
	{
		if (this.popupIframeMenu != null)
		{
			this.popupIframeMenu.destroy();
		}
	}

	return true;
};

BX.Messenger.prototype.expireFrameToken = function()
{
	if (!this.openFrameDialogBid)
	{
		return false;
	}

	for (var botId in this.sendFrameTokenCollection)
	{
		if (this.sendFrameTokenCollection[botId]+(this.sendFrameTokenTimeout*1000) < +new Date())
		{
			delete this.sendFrameTokenCollection[botId];

			if (this.openFrameDialogBid)
			{
				this.sendFrameToken(this.openFrameDialogBid, this.openFrameDialogDid);
			}
		}
	}

	return true;
}

BX.Messenger.prototype.sendFrameToken = function(botId, dialogId)
{
	this.sendFrameTokenCollection[botId] = +new Date();

	BX.ajax({
		url: this.BXIM.pathToAjax+'?SEND_FRAME_TOKEN&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'IM_OPEN_REST_TOKEN': 'Y', 'BOT_ID' : botId, 'DIALOG_ID' : dialogId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
	});
}

BX.Messenger.prototype.connectionStatus = function(status, send)
{
	send = typeof(send) == 'undefined'? true: send;

	if (!(status == 'online' || status == 'connecting' || status == 'offline'))
		return false;

	if (this.popupMessengerConnectionStatusState == status)
		return false;

	this.popupMessengerConnectionStatusState = status;

	var statusClass = '';

	if (status == 'offline')
	{
		this.popupMessengerConnectionStatusStateText = BX.message('IM_CS_OFFLINE');
		statusClass = 'bx-messenger-connection-status-offline';
	}
	else if (status == 'connecting')
	{
		this.popupMessengerConnectionStatusStateText = BX.message('IM_CS_CONNECTING');
		statusClass = 'bx-messenger-connection-status-connecting';
	}
	else if (status == 'online')
	{
		this.popupMessengerConnectionStatusStateText = BX.message('IM_CS_ONLINE');
		statusClass = 'bx-messenger-connection-status-online';
	}

	clearTimeout(this.popupMessengerConnectionStatusTimeout);

	if (!this.popupMessengerConnectionStatus)
		return false;

	if (status == 'online')
	{
		if (send)
		{
			if(this.redrawTab[this.currentTab])
			{
				BX.MessengerCommon.openDialog(this.currentTab);
			}
			else
			{
				this.updateState(true, false, 'UPDATE_STATE_RECONNECT');
			}
		}

		clearTimeout(this.popupMessengerConnectionStatusTimeout);
		this.popupMessengerConnectionStatusTimeout = setTimeout(BX.delegate(function(){
			BX.removeClass(this.popupMessengerConnectionStatus, "bx-messenger-connection-status-show");
			BX.addClass(this.popupMessengerConnectionStatus, "bx-messenger-connection-status-hide");
		}, this), 4000);
	}

	this.popupMessengerConnectionStatus.className = "bx-messenger-connection-status bx-messenger-connection-status-show "+statusClass;
	this.popupMessengerConnectionStatusText.innerHTML = this.popupMessengerConnectionStatusStateText;

	return true;
}

BX.Messenger.prototype.editMessage = function(messageId)
{
	if (!BX.MessengerCommon.checkEditMessage(messageId, 'edit'))
		return false;

	BX.removeClass(this.popupMessengerEditForm, 'bx-messenger-editform-disable');
	BX.removeClass(this.popupMessengerEditForm, 'bx-messenger-editform-hide');
	BX.addClass(this.popupMessengerEditForm, 'bx-messenger-editform-show');

	this.popupMessengerEditMessageId = messageId;

	if (this.popupMessengerEditTextarea.value.length > 20006)
	{
		this.popupMessengerEditTextarea.value = this.popupMessengerEditTextarea.value.substr(0, 20000)+' (...)';
	}

	this.popupMessengerEditTextarea.value = BX.MessengerCommon.prepareTextBack(this.message[messageId].text, true);

	this.popupMessengerEditTextarea.value = this.popupMessengerEditTextarea.value.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, BX.delegate(function(whole, userId, text)
	{
		BX.MessengerCommon.addMentionList(this.currentTab, text, parseInt(userId));
		return text;
	}, this));

	this.popupMessengerEditTextarea.value = this.popupMessengerEditTextarea.value.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, BX.delegate(function(whole, imol, chatId, text)
	{
		BX.MessengerCommon.addMentionList(this.currentTab, text, 'chat'+parseInt(chatId));
		return text;
	}, this));

	clearTimeout(this.popupMessengerEditFormTimeout);
	this.popupMessengerEditFormTimeout = setTimeout(BX.delegate(function(){
		if (!this.popupMessengerEditTextarea)
			return false;

		this.popupMessengerEditTextarea.focus();
		this.popupMessengerEditTextarea.selectionStart = this.popupMessengerEditTextarea.value.length;
		this.popupMessengerEditTextarea.selectionEnd = this.popupMessengerEditTextarea.value.length;
	}, this), 200);
}

BX.Messenger.prototype.editMessageCancel = function()
{
	this.popupMessengerEditTextarea.value = '';

	if (BX.hasClass(this.popupMessengerEditForm, 'bx-messenger-editform-disable'))
		return false;

	this.popupMessengerEditMessageId = 0;

	BX.removeClass(this.popupMessengerEditForm, 'bx-messenger-editform-show');
	BX.addClass(this.popupMessengerEditForm, 'bx-messenger-editform-hide');

	clearTimeout(this.popupMessengerEditFormTimeout);
	this.popupMessengerEditFormTimeout = setTimeout(BX.delegate(function(){
		BX.removeClass(this.popupMessengerEditForm, 'bx-messenger-editform-hide');
		BX.addClass(this.popupMessengerEditForm, 'bx-messenger-editform-disable');
	}, this), 500);

	this.popupMessengerTextarea.focus();
	this.popupMessengerTextarea.selectionStart = this.popupMessengerTextarea.value.length;
	this.popupMessengerTextarea.selectionEnd = this.popupMessengerTextarea.value.length;
}

BX.Messenger.prototype.deleteMessage = function(messageId, check)
{
	if (check !== false && !BX.MessengerCommon.checkEditMessage(messageId, 'delete'))
		return false;

	if (check !== false)
	{
		this.BXIM.openConfirm(BX.message('IM_M_HISTORY_DELETE_CONFIRM'), [
			new BX.PopupWindowButton({
				text : BX.message('IM_M_HISTORY_DELETE'),
				className : "popup-window-button-accept",
				events : { click : BX.delegate(function() { this.deleteMessage(messageId, false); BX.proxy_context.popupWindow.close(); }, this) }
			}),
			new BX.PopupWindowButton({
				text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
				className : "popup-window-button-decline",
				events : { click : function() { this.popupWindow.close(); } }
			})
		], true);
	}
	else
	{
		BX.MessengerCommon.deleteMessageAjax(messageId);
	}
}

BX.Messenger.prototype.shareMessage = function(messageId, type, date)
{
	BX.MessengerCommon.shareMessageAjax(messageId, type, date);
}

BX.Messenger.prototype.toggleLinesTab = function(checked)
{
	if (typeof(checked) == 'undefined')
	{
		checked = this.BXIM.settings.linesTabEnable;
	}
	else
	{
		this.BXIM.settings.linesTabEnable = checked;
	}

	if (checked)
	{
		if (BX.MessengerWindow.existsTab('im-ol'))
		{
			BX.MessengerWindow.showTab('im-ol');
		}
		else if (BX.MessengerCommon.isLinesOperator())
		{
			BX.MessengerWindow.addTab({
				id: 'im-ol',
				title: BX.message('IM_CTL_CHAT_OL'),
				order: 105,
				target: 'im',
				events: {
					open: BX.delegate(function(){
						if (BX.MessengerCommon.isPage() && this.BXIM.context == 'POPUP-FULLSCREEN' && !this.popupMessenger)
						{
							return false;
						}
						if (!this.BXIM.dialogOpen)
						{
							this.openMessenger(this.currentTab);
						}
						BX.MessengerCommon.userListRedraw();
					}, this),
					close: BX.delegate(function(){
						BX.MessengerCommon.userListRedraw();
					}, this)
				}
			});
		}
	}
	else
	{
		BX.MessengerWindow.hideTab('im-ol');
	}

	if (BX.MessengerWindow.currentTab == 'im-ol')
	{
		BX.MessengerWindow.changeTab('im', true);
	}
	BX.MessengerCommon.userListRedraw();
	this.updateMessageCount();

	return true;
}

BX.Messenger.prototype.toggleLinesNewGroup = function(active)
{
	if (typeof(active) == 'undefined')
	{
		active = this.BXIM.settings.linesNewGroupEnable;
	}
	else
	{
		this.BXIM.settings.linesNewGroupEnable = active;
	}

	BX.MessengerCommon.userListRedraw();

	return active;
}

BX.Messenger.prototype.onPaste = function(event)
{
	if (!event.clipboardData)
	{
		return true;
	}

	var text = event.clipboardData.getData("Text");
	if (text && !text.match(/\.(jpg|jpeg|png|gif)$/i))
	{
		return true;
	}

	this.imageUploaderFiles = [];

	var hasUploadFile = false;
	var uploadFilesLeft = event.clipboardData.files.length;
	for (var i=0; i < event.clipboardData.files.length; ++i)
	{
		var file = event.clipboardData.files[i];
		if (!file || !file.type.match(/(jpg|jpeg|png|gif)/i))
		{
			continue;
		}

		hasUploadFile = true;

		if (BX.browser.IsSafari())
		{
			fileName = file.name;
		}
		else
		{
			var convertType = file.name.replace(/^(.*)\.(jpg|jpeg|png|gif)$/im, function(whole, name, type){ return type; });
			var fileName = text? text.replace(/^(.*)\.(jpg|jpeg|png|gif)$/im, function(whole, name){return name+'.'+convertType;}): 'image_'+BX.date.format("Y-m-d_H:i:s")+'.'+convertType;
		}

		if (file.size > 1*1024*1024)
		{
			this.imageUploader();
		}

		var fileReader = new FileReader();

		fileReader.onerror = function (error)
		{
			console.error('BX.Messenger.onPaste -> fileReader.onerror:', error);

			if (this.popupImageUploader)
			{
				this.popupImageUploader.close();
			}
		}.bind(this)

		fileReader.onabort = function (error)
		{
			console.error('BX.Messenger.onPaste -> fileReader.onabort:', error);

			if (this.popupImageUploader)
			{
				this.popupImageUploader.close();
			}
		}.bind(this);

		fileReader.onloadend = function (result)
		{
			this.imageUploaderFiles.push({
				'name': fileName,
				'source': result.target.result,
			})

			if (uploadFilesLeft == 1)
			{
				if (this.popupImageUploader)
				{
					this.imageUploaderUpdateImage();
				}
				else
				{
					this.imageUploader();
				}
			}
			else
			{
				uploadFilesLeft--;
			}
		}.bind(this)

		fileReader.readAsDataURL(file);
	}

	if (hasUploadFile)
	{
		event.preventDefault();
		event.stopPropagation();
	}

	return true;
}

BX.Messenger.prototype.imageUploader = function()
{
	if (this.popupImageUploader)
		this.popupImageUploader.close();

	var titleBar = BX.message('IM_UPLOAD_IMAGE_TITLE');
	if (this.imageUploaderFiles.length > 1 && BX.message('IM_UPLOAD_IMAGE_TITLE_2'))
	{
		titleBar = BX.message('IM_UPLOAD_IMAGE_TITLE_2').replace('#NUMBER#', this.imageUploaderFiles.length);
	}

	this.popupImageUploader = new BX.PopupWindow('bx-messenger-image-uploader', null, {
		lightShadow: true,
		closeByEsc: true,
		closeIcon : {},
		contentNoPaddings : true,
		contentColor : "white",
		events : {
			onPopupClose : function() {this.destroy(); },
			onPopupDestroy : function() {
				this.popupImageUploader = null;
				this.imageUploaderTextarea = null;
				this.imageUploaderFiles = [];
			}.bind(this)
		},
		buttons: [
			new BX.PopupWindowButton({
				text : BX.message('IM_UPLOAD_IMAGE_BUTTON_UPLOAD'),
				className : "popup-window-button-accept",
				events : { click : BX.delegate(function() {
					this.disk.uploadFromClipboard(this.imageUploaderFiles, this.imageUploaderTextarea.value);
					BX.proxy_context.popupWindow.close();
				}, this) }
			}),
			new BX.PopupWindowButton({
				text : BX.message('IM_UPLOAD_IMAGE_BUTTON_CLOSE'),
				className : "popup-window-button-decline",
				events : { click : function() { this.popupWindow.close(); } }
			})
		],
		zIndex: 2000,
		titleBar: titleBar,
		content: '<div class="im-messenger-image-uploader">'+
			'<div class="im-messenger-image-uploader-preview bx-messenger-custom-scroll">'+
				this.imageUploaderPreperaImageNode()+
			'</div>'+
			'<div class="im-messenger-image-uploader-textarea">'+
				'<textarea class="im-messenger-image-uploader-textarea-input" placeholder="'+BX.message('IM_UPLOAD_IMAGE_COMMENT')+'"></textarea>'+
			'</div>'+
		'</div>'
	});
	this.popupImageUploader.show();

	BX.addClass(this.popupImageUploader.popupContainer, "bx-messenger-mark");

	this.imageUploaderButtonUpload = BX.findChildByClassName(this.popupImageUploader.buttonsContainer, 'popup-window-button-accept');
	if (this.imageUploaderButtonUpload && this.imageUploaderButtonUpload.innerHTML == BX.message('IM_UPLOAD_IMAGE_BUTTON_UPLOAD'))
	{
		this.imageUploaderButtonUpload.innerHTML = BX.message('IM_UPLOAD_IMAGE_BUTTON_UPLOAD')+' ('+(BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter")+')';
	}
	this.imageUploaderTextarea = BX.findChildByClassName(this.popupImageUploader.contentContainer, 'im-messenger-image-uploader-textarea-input');
	this.imageUploaderTextarea.focus();

	BX.bind(this.imageUploaderTextarea, 'keydown', function(event)
	{
		if (
			(event.metaKey == true || event.ctrlKey == true)
			&& (event.keyCode == 13 || event.keyCode == 32)
		)
		{
			this.disk.uploadFromClipboard(this.imageUploaderFiles, this.imageUploaderTextarea.value);
			this.popupImageUploader.close();
		}
	}.bind(this));

	if (this.imageUploaderFiles.length <= 0)
	{
		var previewNode = BX.findChildByClassName(this.popupImageUploader.contentContainer, 'im-messenger-image-uploader-preview');

		this.imageUploaderLoader = new BX.Loader({size: 42});
		this.imageUploaderLoader.show(previewNode);
	}

	return true;
};

BX.Messenger.prototype.imageUploaderPreperaImageNode = function()
{
	if (this.imageUploaderFiles.length <= 0)
	{
		return '';
	}

	var className = '';
	if (this.imageUploaderFiles.length == 1)
	{
		return '<div class="im-messenger-image-uploader-preview-box">' +
					'<img src="'+this.imageUploaderFiles[0].source+'" class="im-messenger-image-uploader-preview-image">' +
				'</div>';
	}
	else if (this.imageUploaderFiles.length == 2)
	{
		className = 'im-messenger-image-uploader-preview-group-box-twin';
	}
	else if (this.imageUploaderFiles.length == 3)
	{
		className = 'im-messenger-image-uploader-preview-group-box-one-line';
	}
	else if (this.imageUploaderFiles.length <= 6)
	{
		className = 'im-messenger-image-uploader-preview-group-box-two-line';
	}
	else
	{
		className = 'im-messenger-image-uploader-preview-group-box';
	}

	var result = '';
	this.imageUploaderFiles.forEach(function(item) {
		result += '<div class="im-messenger-image-uploader-preview-group-image">' +
					'<img src="'+item.source+'" class="im-messenger-image-uploader-preview-group-image-source">' +
				'</div>';
	});

	return '<div class="im-messenger-image-uploader-preview-box '+className+'">'+result+'</div>';
}

BX.Messenger.prototype.imageUploaderUpdateImage = function()
{
	if (!this.popupImageUploader || this.imageUploaderFiles.length <= 0)
	{
		return false;
	}

	var previewNode = BX.findChildByClassName(this.popupImageUploader.contentContainer, 'im-messenger-image-uploader-preview');
	if (!previewNode)
	{
		return false;
	}

	if (this.imageUploaderLoader)
	{
		this.imageUploaderLoader.destroy();
		this.imageUploaderLoader = null;
	}

	previewNode.innerHTML = this.imageUploaderPreperaImageNode();

	return true;
}

BX.Messenger.prototype.insertQuoteMessage = function(node)
{
	var arQuote = [];
	var firstMessage = true;
	var messageName = '';
	var messageDate = '';

	var stackMessages = BX.findChildren(node.parentNode.nextSibling.firstChild, {tagName : "span"}, false);
	for (var i = 0; i < stackMessages.length; i++) {
		var messageId = stackMessages[i].id.replace('im-message-','');
		if (this.message[messageId])
		{
			if (firstMessage)
			{
				if (this.users[this.message[messageId].senderId])
				{
					messageName = this.users[this.message[messageId].senderId].name;
					messageDate = this.message[messageId].date;
				}
				firstMessage = false;
			}

			var messageText = this.message[messageId].text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, BX.delegate(function(whole, userId, text){return text;}, this));
			messageText = messageText.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, BX.delegate(function(whole, imol, chatId, text) {return text;}, this));
			messageText = messageText.replace(/\[RATING\=([1-5]{1})\]/ig, BX.delegate(function(whole, rating) {return '['+BX.message('IM_F_RATING')+'] ';}, this));
			messageText = messageText.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
			messageText = messageText.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
			messageText = messageText.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
			messageText = messageText.replace(/\[ATTACH=([0-9]{1,})\]/ig, BX.delegate(function(whole, command, text) {return command == 10000? '': '['+BX.message('IM_F_ATTACH')+'] ';}, this));

			arQuote.push(BX.MessengerCommon.prepareTextBack(messageText));
		}
	}
	this.insertQuoteText(messageName, messageDate, arQuote.join("\n"));
}

BX.Messenger.prototype.insertQuoteText = function(name, date, text, insertInTextarea)
{
	text = text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, BX.delegate(function(whole, userId, text) {return text;}, this));
	text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, BX.delegate(function(whole, imol, chatId, text) {return text;}, this));
	text = text.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
	text = text.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
	text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, BX.delegate(function(whole, command, text) {return text? text: command;}, this));
	text = text.replace(/\[ATTACH=([0-9]{1,})\]/ig, BX.delegate(function(whole, command, text) {return command == 10000? '': '['+BX.message('IM_F_ATTACH')+'] ';}, this));
	text = text.replace(/\[RATING\=([1-5]{1})\]/ig, BX.delegate(function(whole, rating) {return '['+BX.message('IM_F_RATING')+'] ';}, this));
	text = text.replace(/&nbsp;/ig, " ");

	var arQuote = [];
	arQuote.push((this.popupMessengerTextarea && this.popupMessengerTextarea.value.length>0?"\n":'')+this.historyMessageSplit);
	arQuote.push(BX.util.htmlspecialcharsback(name)+' ['+BX.MessengerCommon.formatDate(date)+']');
	arQuote.push(text);
	arQuote.push(this.historyMessageSplit+"\n");

	if (insertInTextarea !== false)
	{
		this.insertTextareaText(this.popupMessengerTextarea, arQuote.join("\n"), false);

		setTimeout(BX.delegate(function(){
			this.popupMessengerTextarea.scrollTop = this.popupMessengerTextarea.scrollHeight;
			this.popupMessengerTextarea.focus();
		}, this), 100);
	}
	else
	{
		return arQuote.join("\n");
	}
}

BX.Messenger.prototype.insertTextareaText = function(textarea, text, returnBack)
{
	if (!textarea && opener.BXIM.messenger.popupMessengerTextarea)
		textarea = opener.BXIM.messenger.popupMessengerTextarea;

	if (textarea.selectionStart || textarea.selectionStart == '0')
	{
		var selectionStart = textarea.selectionStart;
		var selectionEnd = textarea.selectionEnd;
		textarea.value = textarea.value.substring(0,selectionStart)+text+textarea.value.substring(selectionEnd, textarea.value.length);

		returnBack = returnBack != false;
		if (returnBack)
		{
			textarea.selectionStart = selectionStart+1;
			textarea.selectionEnd = selectionStart+1;
		}
		else if (BX.browser.IsChrome() || BX.browser.IsSafari() || BX.MessengerCommon.isDesktop())
		{
			textarea.selectionStart = textarea.value.length+1;
			textarea.selectionEnd = textarea.value.length+1;
		}
	}
	if (document.selection && document.documentMode && document.documentMode <= 8)
	{
		textarea.focus();
		var select=document.selection.createRange();
		select.text = text;
	}
};

BX.Messenger.prototype.resizeTextareaStart = function(e)
{
	if (this.webrtc.callOverlayFullScreen) return false;

	if(!e) e = window.event;

	this.popupMessengerTextareaResize.wndSize = BX.GetWindowScrollPos();
	this.popupMessengerTextareaResize.pos = BX.pos(this.popupMessengerTextarea);
	this.popupMessengerTextareaResize.y = e.clientY + this.popupMessengerTextareaResize.wndSize.scrollTop;
	this.popupMessengerTextareaResize.textOffset = this.popupMessengerTextarea.offsetHeight;
	this.popupMessengerTextareaResize.bodyOffset = this.popupMessengerBody.offsetHeight;

	BX.bind(document, "mousemove", BX.proxy(this.resizeTextareaMove, this));
	BX.bind(document, "mouseup", BX.proxy(this.resizeTextareaStop, this));

	if(document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = 'none';
	b.style.cursor = 'move';

	BX.onCustomEvent('onImResizeTextarea', []);

	this.closeMenuPopup();
};
BX.Messenger.prototype.resizeTextareaMove = function(e)
{
	if(!e) e = window.event;

	var windowScroll = BX.GetWindowScrollPos();
	var x = e.clientX + windowScroll.scrollLeft;
	var y = e.clientY + windowScroll.scrollTop;
	if(this.popupMessengerTextareaResize.y == y)
		return;

	var textareaHeight = Math.max(Math.min(-(y-this.popupMessengerTextareaResize.pos.top) + this.popupMessengerTextareaResize.textOffset, 143), 30);

	this.popupMessengerTextareaSize = textareaHeight;
	this.popupMessengerTextarea.style.height = textareaHeight + 'px';
	this.popupMessengerBodySize = this.popupMessengerTextareaResize.textOffset-textareaHeight + this.popupMessengerTextareaResize.bodyOffset;
	this.popupMessengerBody.style.height = this.popupMessengerBodySize + 'px';
	this.popupMessengerBodyPanel.style.height = this.popupMessengerBodyDialog.offsetHeight + 'px';
	this.resizeMainWindow();

	this.popupMessengerTextareaResize.x = x;
	this.popupMessengerTextareaResize.y = y;
};

BX.Messenger.prototype.resizeTextareaStop = function()
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.resizeTextareaMove, this));
	BX.unbind(document, "mouseup", BX.proxy(this.resizeTextareaStop, this));

	document.onmousedown = null;

	this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight - this.popupMessengerBody.offsetHeight;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = '';
	b.style.cursor = '';

	clearTimeout(this.BXIM.adjustSizeTimeout);
	this.BXIM.adjustSizeTimeout = setTimeout(BX.delegate(function(){
		this.BXIM.setLocalConfig('global_tas', this.popupMessengerTextareaSize);
		this.BXIM.setLocalConfig('global_msz_v2', {
			'wz': this.popupMessengerFullWidth,
			'ta2': this.popupMessengerTextareaSize,
			'b': this.popupMessengerBodySize,
			'cl': this.popupContactListSize,
			'hi': this.popupHistoryItemsSize,
			'fz': this.popupMessengerFullHeight,
			'ez': this.popupContactListElementsSize,
			'nz': this.notify.popupNotifySize,
			'hf': this.popupHistoryFilterVisible,
			'dw': window.innerWidth,
			'dh': window.innerHeight,
			'place': 'taMove'
		});
	}, this), 500);
};

BX.Messenger.prototype.setTextareaSize = function(size)
{
	size = Math.max(Math.min(size, 143), 30);
	if (this.popupMessengerTextareaSize == size)
		return true;

	var difference = size-this.popupMessengerTextareaSize;

	this.popupMessengerBodySize = this.popupMessengerBodySize+(difference*-1);
	if (this.popupMessengerBody)
	{
		this.popupMessengerBody.style.height = this.popupMessengerBodySize + 'px';
		this.popupMessengerBodyPanel.style.height = this.popupMessengerBodyDialog.offsetHeight + 'px';
	}

	this.popupMessengerTextareaSize = size;
	if (this.popupMessengerTextarea)
	{
		this.popupMessengerTextarea.style.height = size + 'px';
	}

	return true;
}

BX.Messenger.prototype.resizeWindowStart = function()
{
	if (this.webrtc.callOverlayFullScreen) return false;
	if (this.popupMessengerTopLine)
		BX.remove(this.popupMessengerTopLine);

	this.popupMessengerWindow.pos = BX.pos(this.popupMessengerContent);
	this.popupMessengerWindow.mb = this.popupMessengerBodySize;
	this.popupMessengerWindow.nb = this.notify.popupNotifySize;

	BX.bind(document, "mousemove", BX.proxy(this.resizeWindowMove, this));
	BX.bind(document, "mouseup", BX.proxy(this.resizeWindowStop, this));

	if (document.body.setCapture)
		document.body.setCapture();

	document.onmousedown = BX.False;

	var b = document.body;
	b.ondrag = b.onselectstart = BX.False;
	b.style.MozUserSelect = 'none';
	b.style.cursor = 'move';

	this.closeMenuPopup();
	this.BXIM.autoHideDisable = true;
};
BX.Messenger.prototype.resizeWindowMove = function(e)
{
	if(!e) e = window.event;

	var windowScroll = BX.GetWindowScrollPos();
	var x = e.clientX + windowScroll.scrollLeft;
	var y = e.clientY + windowScroll.scrollTop;

	this.popupMessengerFullHeight = Math.max(Math.min(y-this.popupMessengerWindow.pos.top, 1000), this.popupMessengerMinHeight);
	this.popupMessengerFullWidth = Math.max(Math.min(x-this.popupMessengerWindow.pos.left, 1200), this.popupMessengerMinWidth);

	this.popupMessengerContent.style.height = this.popupMessengerFullHeight+'px';
	this.popupMessengerContent.style.width = this.popupMessengerFullWidth+'px';

	var changeHeight = this.popupMessengerFullHeight-Math.max(Math.min(this.popupMessengerWindow.pos.height, 1000), this.popupMessengerMinHeight);

	this.popupMessengerBodySize = this.popupMessengerWindow.mb+changeHeight;
	if (this.popupMessengerBody != null)
		this.popupMessengerBody.style.height = this.popupMessengerBodySize + 'px';
	if (this.popupMessengerBodyPanel != null)
		this.xx.style.height = this.popupMessengerBodyDialog.offsetHeight + 'px';
	if (this.popupMessengerExtra != null)
		this.popupMessengerExtra.style.height = this.popupMessengerFullHeight+'px';

	this.notify.popupNotifySize = Math.max(this.popupMessengerWindow.nb+(this.popupMessengerBodySize - this.popupMessengerWindow.mb), this.notify.popupNotifySizeMin);
	if (this.notify.popupNotifyItem != null)
		this.notify.popupNotifyItem.style.height = this.notify.popupNotifySize+'px';

	if (this.webrtc.callOverlay)
	{
		BX.style(this.webrtc.callOverlay, 'transition', 'none');
		BX.style(this.webrtc.callOverlay, 'width', (this.popupMessengerExtra.style.display == "block"? this.popupMessengerExtra.offsetWidth-1: this.popupMessengerDialog.offsetWidth-1)+'px');
		BX.style(this.webrtc.callOverlay, 'height', (this.popupMessengerFullHeight-1)+'px');
	}

	this.BXIM.messenger.redrawChatHeader();
	this.resizeMainWindow();
};

BX.Messenger.prototype.resizeWindowStop = function()
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	BX.unbind(document, "mousemove", BX.proxy(this.resizeWindowMove, this));
	BX.unbind(document, "mouseup", BX.proxy(this.resizeWindowStop, this));

	document.onmousedown = null;

	this.popupMessengerBody.scrollTop = this.popupMessengerBody.scrollHeight - this.popupMessengerBody.offsetHeight;

	var b = document.body;
	b.ondrag = b.onselectstart = null;
	b.style.MozUserSelect = '';
	b.style.cursor = '';

	if (this.webrtc.callOverlay)
		BX.style(this.webrtc.callOverlay, 'transition', '');

	clearTimeout(this.BXIM.adjustSizeTimeout);
	this.BXIM.adjustSizeTimeout = setTimeout(BX.delegate(function(){
		this.BXIM.setLocalConfig('global_msz_v2', {
			'wz': this.popupMessengerFullWidth,
			'ta2': this.popupMessengerTextareaSize,
			'b': this.popupMessengerBodySize,
			'cl': this.popupContactListSize,
			'hi': this.popupHistoryItemsSize,
			'fz': this.popupMessengerFullHeight,
			'ez': this.popupContactListElementsSize,
			'nz': this.notify.popupNotifySize,
			'hf': this.popupHistoryFilterVisible,
			'dw': window.innerWidth,
			'dh': window.innerHeight,
			'place': 'winMove'
		});
		this.BXIM.autoHideDisable = false;
	}, this), 500);
};

/* COMMON */

BX.Messenger.prototype.newMessage = function(send)
{
	send = send != false;

	var arNewMessage = [];
	var arNewMessageText = [];
	var flashCount = 0;
	var flashNames = {};
	var enableSound = 0;
	for (var i in this.flashMessage)
	{
		var skip = false;
		var skipBlock = false;

		if (this.BXIM.isFocus() && this.popupMessenger != null && i == this.currentTab)
		{
			skip = true;
			enableSound++;
		}
		else if (i.toString().substr(0,4) == 'chat' || this.users[i] && this.users[i].extranet)
		{
			if (this.muteButtonStatus(i))
			{
				skipBlock = true;
			}
		}

		if (skip || skipBlock)
		{
			for (var k in this.flashMessage[i])
			{
				if (this.flashMessage[i][k] !== false)
				{
					this.flashMessage[i][k] = false;
					flashCount++;
				}
			}
			continue;
		}

		var flashedDialogId = {};
		for (var k in this.flashMessage[i])
		{
			if (this.flashMessage[i][k] === false || flashedDialogId[i])
			{
				this.flashMessage[i][k] = false;
				continue;
			}

			flashedDialogId[i] = true;

			var isChat = this.message[k].recipientId.toString().substr(0,4) == 'chat';
			var recipientId = this.message[k].recipientId;
			var senderId = !isChat && this.message[k].senderId == 0? i: this.message[k].senderId;

			if (
				isChat && !this.chat[recipientId.substr(4)]
				|| !isChat && !this.users[senderId]
			)
			{
				continue;
			}

			var isCall = isChat && this.chat[recipientId.substr(4)].type == 'call';
			var isLines = isChat && this.chat[recipientId.substr(4)].type == 'lines';
			var isSystem = this.message[k].system == 'Y';

			var messageText = BX.MessengerCommon.purifyText(this.message[k].text, this.message[k].params);

			if (i != this.BXIM.userId)
			{
				flashNames[i] = (isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name);
			}

			if (messageText.length > 150)
			{
				messageText = messageText.substr(0, 150);
				var lastSpace = messageText.lastIndexOf(' ');
				if (lastSpace < 140)
					messageText = messageText.substr(0, lastSpace)+'...';
				else
					messageText = messageText.substr(0, 140)+'...';
			}

			if (messageText == '')
			{
				if (this.message[k].params['FILE_ID'] && this.message[k].params['FILE_ID'].length > 0)
					messageText = '['+BX.message('IM_F_FILE')+']';
				else if (this.message[k].params['ATTACH'] && this.message[k].params['ATTACH'].length > 0)
					messageText = '['+BX.message('IM_F_ATTACH')+']';
			}

			if (isChat)
			{
				var chatId = recipientId.substr(4);
				var avatarStyle = BX.MessengerCommon.isBlankAvatar(this.chat[chatId].avatar)? 'background-color: '+this.chat[chatId].color: '';

				var avatarType = 3;
				if (isCall)
				{
					avatarType = 4;
				}
				else if(isLines)
				{
					avatarType = 7;
				}
				else if(this.generalChatId == chatId)
				{
					avatarType = 6;
				}
				else if (this.chat[recipientId.substr(4)].type == 'open')
				{
					avatarType = 5;
				}
			}
			else
			{
				var avatarStyle = BX.MessengerCommon.isBlankAvatar(this.users[senderId].avatar)? 'background-color: '+this.users[senderId].color: '';
			}
			var element = BX.create("div", {attrs : { 'data-userId' : isChat? recipientId: senderId, 'data-messageId' : k}, props : { className: "bx-notifier-item bx-notifier-item-"+k+" "}, children : [
				BX.create('span', {props : { className : "bx-notifier-item-content" }, children : [
					BX.create('span', {props : { className : "bx-notifier-item-avatar"}, children : [
						BX.create('img', {props : {className : "bx-notifier-item-avatar-img"+(BX.MessengerCommon.isBlankAvatar(isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar)? (isChat? " bx-notifier-item-avatar-img-default-"+avatarType: " bx-notifier-item-avatar-img-default"): "")}, attrs : {src : isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar, style: avatarStyle}})
					]}),
					BX.create("a", {attrs : {href : '#', 'data-messageId' : k}, props : { className: "bx-notifier-item-delete"}}),
					BX.create('span', {props : { className : "bx-notifier-item-date" }, html: BX.MessengerCommon.formatDate(this.message[k].date)}),
					BX.create('span', {props : { className : "bx-notifier-item-name" }, html: isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name}),
					BX.create('span', {props : { className : "bx-notifier-item-text" }, html: (isChat && senderId>0?'<i>'+this.users[senderId].name+'</i>: ':'')+BX.MessengerCommon.prepareText(messageText, false, true)})
				]})
			]});
			if (!this.BXIM.xmppStatus || this.BXIM.xmppStatus && isChat)
			{
				arNewMessage.push(element);

				messageText = BX.util.htmlspecialcharsback(messageText);
				messageText = messageText.split('<br />').join("\n");
				messageText = messageText.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, function(whole, userId, text) {return text;});
				messageText = messageText.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function(whole, imol, chatId, text) {return text;});
				messageText = messageText.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, function(whole, historyId, text) {return text;});
				messageText = messageText.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, function(whole, command, text) {return text? text: command;});
				messageText = messageText.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, function(whole, command, text) {return text? text: command;});
				messageText = messageText.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, function(whole, command, text) {return text? text: command;});
				messageText = messageText.replace(/\[ATTACH=([0-9]{1,})\]/ig, function(whole, historyId, text) {return '';});

				arNewMessageText.push({
					'id':  isChat? recipientId: senderId,
					'title':  BX.util.htmlspecialcharsback(isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name),
					'text':  (isChat && senderId>0?this.users[senderId].name+': ':'')+messageText,
					'icon':  isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar,
					'tag':  'im-messenger-'+(isChat? recipientId: senderId)
				});
			}
			this.flashMessage[i][k] = false;
		}
	}

	if (this.BXIM.context == "LINES" || this.BXIM.context == "DIALOG")
	{
		return false;
	}

	if (!BX.MessengerCommon.isDesktop() && this.BXIM.desktopStatus)
		return false;

	if (arNewMessage.length > 5)
	{
		var names = '';
		for (var i in flashNames)
			names += ', <i>'+flashNames[i]+'</i>';

		var notify = {
			id: 0, type: 4, date: new Date(),
			title: BX.message('IM_NM_MESSAGE_1').replace('#COUNT#', arNewMessage.length),
			text: BX.message('IM_NM_MESSAGE_2').replace('#USERS#', names.substr(2))
		};
		arNewMessage = [];
		arNewMessage.push(this.notify.createNotify(notify, true))

		arNewMessageText = []
		arNewMessageText.push({
			'id': '',
			'title':  BX.message('IM_NM_MESSAGE_1').replace('#COUNT#', arNewMessage.length),
			'text':  BX.message('IM_NM_MESSAGE_2').replace('#USERS#', BX.util.htmlspecialcharsback(names.substr(2))).replace(/<\/?[^>]+>/gi, '')
		})
	}
	else if (arNewMessage.length == 0)
	{
		if (enableSound > 0 && BX.MessengerCommon.isDesktop())
			BX.desktop.flashIcon();

		if (send && enableSound > 0 && this.BXIM.settings.status != 'dnd')
		{
			this.BXIM.playSound("newMessage2");
		}

		return false;
	}

	if (BX.MessengerCommon.isDesktop())
		BX.desktop.flashIcon();

	//if (this.BXIM.settings.status == 'dnd')
	//	return false;

	if (BX.MessengerCommon.isDesktop())
	{
		for (var i = 0; i < arNewMessage.length; i++)
		{
			var dataMessageId = arNewMessage[i].getAttribute("data-messageId");
			var messsageJs =
				'var notify = BX.findChildByClassName(document.body, "bx-notifier-item");'+
				'notify.style.cursor = "pointer";'+
				'BX.bind(notify, "click", function(){BX.desktop.onCustomEvent("main", "bxImClickNewMessage", [notify.getAttribute("data-userId")]); BX.desktop.windowCommand("close")});'+
				'BX.bind(BX.findChildByClassName(notify, "bx-notifier-item-delete"), "click", function(event){ BX.desktop.onCustomEvent("main", "bxImClickCloseMessage", [notify.getAttribute("data-userId")]); BX.desktop.windowCommand("close"); BX.MessengerCommon.preventDefault(event); });'+
				'BX.bind(notify, "contextmenu", function(){ BX.desktop.windowCommand("close")});';
			this.desktop.openNewMessage(dataMessageId, arNewMessage[i], messsageJs);
		}
	}
	else if(send && !this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
	{
		for (var i = 0; i < arNewMessageText.length; i++)
		{
			var notify = arNewMessageText[i];
			notify.onshow = function() {
				var notify = this;
				setTimeout(function(){
					notify.close();
				}, 5000)
			}
			notify.onclick = function() {
				window.focus();
				top.BXIM.openMessenger(notify.id);
				this.close();
			}
			this.BXIM.notifyManager.nativeNotify(notify)
		}
	}
	else
	{
		if (this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
		{
			BX.localStorage.set('mnnb', true, 1);
		}
		for (var i = 0; i < arNewMessage.length; i++)
		{
			this.BXIM.notifyManager.add({
				'html': arNewMessage[i],
				'tag': 'im-message-'+arNewMessage[i].getAttribute('data-userId'),
				'userId': arNewMessage[i].getAttribute('data-userId'),
				'click': BX.delegate(function(popup) {
					this.openMessenger(popup.notifyParams.userId);
					popup.close();
				}, this),
				'close': BX.delegate(function(popup) {
					BX.MessengerCommon.readMessage(popup.notifyParams.userId);
				}, this)
			});
		}
	}

	if (BX.MessengerCommon.isDesktop())
		BX.desktop.flashIcon();

	if (send)
	{
		this.BXIM.playSound("newMessage1");
	}
};

BX.Messenger.prototype.showNotifyBlock = function(messageParams)
{
	var isChat = messageParams.recipientId.toString().substr(0, 4) == 'chat';
	var recipientId = messageParams.recipientId;
	var isCall = isChat && this.chat[recipientId.substr(4)] && this.chat[recipientId.substr(4)].type == 'call';
	var isLines = isChat && this.chat[recipientId.substr(4)] && this.chat[recipientId.substr(4)].type == 'lines';
	var senderId = !isChat && messageParams.senderId == 0? i: messageParams.senderId;
	var messageText = messageParams.text_mobile? messageParams.text_mobile: messageParams.text;

	if (!messageParams.id)
		messageParams.id = "custom-"+(+new Date);

	if (messageParams.date)
		messageParams.date = new Date();

	messageText = messageText.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "[" + BX.message("IM_M_QUOTE_BLOCK") + "]");
	if (messageText.length > 150)
	{
		messageText = messageText.substr(0, 150);
		var lastSpace = messageText.lastIndexOf(' ');
		if (lastSpace < 140)
			messageText = messageText.substr(0, lastSpace) + '...';
		else
			messageText = messageText.substr(0, 140) + '...';
	}


	if (messageText == '' && messageParams.params['FILE_ID'].length > 0)
	{
		messageText = '[' + BX.message('IM_F_FILE') + ']';
	}

	if (isChat)
	{
		var chatId = recipientId.substr(4);
		var avatarStyle = BX.MessengerCommon.isBlankAvatar(this.chat[chatId].avatar)? 'background-color: '+this.chat[chatId].color: '';

		var avatarType = 3;
		if (isCall)
		{
			avatarType = 4;
		}
		else if(isLines)
		{
			avatarType = 7;
		}
		else if(this.generalChatId == chatId)
		{
			avatarType = 6;
		}
		else if (this.chat[recipientId.substr(4)].type == 'open')
		{
			avatarType = 5;
		}
	}
	else
	{
		var avatarStyle = BX.MessengerCommon.isBlankAvatar(this.users[senderId].avatar)? 'background-color: '+this.users[senderId].color: '';
	}

	var notifyHtmlNode = BX.create("div", {
		attrs : {'data-userId' : isChat? recipientId: senderId, 'data-messageId' : messageParams.id},
		props : {className : "bx-notifier-item bx-notifier-item-"+messageParams.id+" "},
		children : [
			BX.create('span', {
				props : {className : "bx-notifier-item-content"}, children : [
					BX.create('span', {
						props : {className : "bx-notifier-item-avatar"}, children : [
							BX.create('img', {
								props : {className : "bx-notifier-item-avatar-img" + (BX.MessengerCommon.isBlankAvatar(isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar)? (isChat? " bx-notifier-item-avatar-img-default-" + avatarType: " bx-notifier-item-avatar-img-default"): "")},
								attrs : {src : isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar, style: avatarStyle}
							})
						]
					}),
					BX.create("a", {
						attrs : {href : '#', 'data-messageId' : messageParams.id},
						props : {className : "bx-notifier-item-delete"}
					}),
					messageParams.date? BX.create('span', {
						props : {className : "bx-notifier-item-date"},
						html : BX.MessengerCommon.formatDate(messageParams.date)
					}): BX.create('span'),
					BX.create('span', {
						props : {className : "bx-notifier-item-name"},
						html : isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name
					}),
					BX.create('span', {
						props : {className : "bx-notifier-item-text"},
						html : (isChat && senderId > 0? '<i>' + this.users[senderId].name + '</i>: ': '') + BX.MessengerCommon.prepareText(messageText, false, true)
					})
				]
			})
		]
	});

	if (!this.BXIM.xmppStatus || this.BXIM.xmppStatus && isChat)
	{
		messageText = BX.util.htmlspecialcharsback(messageText);
		messageText = messageText.split('<br />').join("\n");
		messageText = messageText.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, function (whole, userId, text) {return text;});
		messageText = messageText.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/ig, function (whole, imol, chatId, text) {return text;});
		messageText = messageText.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, function (whole, historyId, text) {return text;});
		messageText = messageText.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/ig, function (whole, command, text) {return text? text: command;});
		messageText = messageText.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/ig, function (whole, command, text) {return text? text: command;});
		messageText = messageText.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/ig, function (whole, command, text) {return text? text: command;});
		messageText = messageText.replace(/\[ATTACH=([0-9]{1,})\]/ig, function (whole, command, text) {return '';});

		notifyTextObject = {
			'id' : isChat? recipientId: senderId,
			'title' : BX.util.htmlspecialcharsback(isChat? this.chat[recipientId.substr(4)].name: this.users[senderId].name),
			'text' : (isChat && senderId > 0? this.users[senderId].name + ': ': '') + messageText,
			'icon' : isChat? this.chat[recipientId.substr(4)].avatar: this.users[senderId].avatar,
			'tag' : 'im-messenger-' + (isChat? recipientId: senderId)
		};
	}
	else
	{
		return false;
	}

	if (!(!BX.MessengerCommon.isDesktop() && BX.MessengerCommon.isPage()) && !BX.MessengerCommon.isDesktop() && this.BXIM.desktopStatus)
		return false;

	if (BX.MessengerCommon.isDesktop())
	{
		var messsageJs =
			'var notify = BX.findChildByClassName(document.body, "bx-notifier-item");'+
			'notify.style.cursor = "pointer";'+
			'BX.bind(notify, "click", function(){BX.desktop.onCustomEvent("main", "bxImClickNewMessage", [notify.getAttribute("data-userId")]); BX.desktop.windowCommand("close")});'+
			'BX.bind(BX.findChildByClassName(notify, "bx-notifier-item-delete"), "click", function(event){ BX.desktop.onCustomEvent("main", "bxImClickCloseMessage", [notify.getAttribute("data-userId")]); BX.desktop.windowCommand("close"); BX.MessengerCommon.preventDefault(event); });'+
			'BX.bind(notify, "contextmenu", function(){ BX.desktop.windowCommand("close")});';
		this.desktop.openNewMessage(
			notifyHtmlNode.getAttribute("data-messageId"),
			notifyHtmlNode,
			messsageJs
		);
	}
	else if(!this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
	{
		var notify = notifyTextObject;
		notify.onshow = function() {
			var notify = this;
			setTimeout(function(){
				notify.close();
			}, 5000)
		}
		notify.onclick = function() {
			window.focus();
			top.BXIM.openMessenger(notify.id);
			this.close();
		}
		this.BXIM.notifyManager.nativeNotify(notify);
	}
	else
	{
		this.BXIM.notifyManager.add({
			'html': notifyHtmlNode,
			'tag': 'im-message-'+notifyHtmlNode.getAttribute('data-userId'),
			'userId': notifyHtmlNode.getAttribute('data-userId'),
			'click': BX.delegate(function(popup) {
				this.openMessenger(popup.notifyParams.userId);
				popup.close();
			}, this),
			'close': BX.delegate(function(popup) {
				BX.MessengerCommon.readMessage(popup.notifyParams.userId);
			}, this)
		});
	}

	return true;
}

BX.Messenger.prototype.updateMessageCount = function(send)
{
	send = send != false;
	var count = 0;
	var chatId = 0;
	var countLines = 0;
	for (var i in this.unreadMessage)
	{
		if (!this.unreadMessage[i])
			continue;

		if (i.toString().substr(0,4) == 'chat')
		{
			chatId = i.toString().substr(4);
			if (this.chat[chatId] && this.chat[chatId].entity_type == 'LINES' && this.BXIM.settings.linesTabEnable && BX.MessengerCommon.isLinesOperator())
			{
				countLines = countLines+this.unreadMessage[i].length;
			}
			else if (!this.userChatBlockStatus[chatId] || !this.userChatBlockStatus[chatId][this.BXIM.userId])
			{
				count = count+this.unreadMessage[i].length;
			}
		}
		else
		{
			count = count+this.unreadMessage[i].length;
		}
	}

	if (send)
		BX.localStorage.set('mumc', {'unread':this.unreadMessage, 'flash':this.flashMessage}, 5);

	if (this.messageCount != count)
		BX.onCustomEvent(window, 'onImUpdateCounterMessage', [count, 'MESSAGE']);

	this.messageCount = count;

	if (this.BXIM.linesCount != countLines)
		BX.onCustomEvent(window, 'onImUpdateCounterMessage', [countLines, 'LINES']);

	this.BXIM.linesCount = countLines;

	var messageCountLabel = '';
	if (this.messageCount > 99)
		messageCountLabel = '99+';
	else if (this.messageCount > 0)
		messageCountLabel = this.messageCount;

	if (this.notify.panelButtonMessageCount)
	{
		this.notify.panelButtonMessageCount.innerHTML = messageCountLabel;
		this.notify.adjustPosition({"resize": true, "timeout": 500});
	}

	if (BX.MessengerCommon.isPage())
	{
		BX.MessengerWindow.setTabBadge('im', count);
		BX.MessengerWindow.setTabBadge('im-ol', countLines);
	}

	this.BXIM.messageCount = this.messageCount;

	return this.messageCount;
};

BX.Messenger.prototype.setStatus = function(status, send)
{
	send = send != false;

	//if (this.users[this.BXIM.userId].status == status)
	//	return false;

	if (!status)
		return false;

	status = status.toLowerCase();

	this.users[this.BXIM.userId].status = status;
	this.BXIM.updateCounter(); // for redraw digits on new color

	if (this.contactListPanelStatus != null && !BX.hasClass(this.contactListPanelStatus, 'bx-messenger-cl-panel-status-'+status))
	{
		this.contactListPanelStatus.className = 'bx-messenger-cl-panel-status-wrap bx-messenger-cl-panel-status-'+status;

		var statusText = BX.findChildByClassName(this.contactListPanelStatus, "bx-messenger-cl-panel-status-text");
		status = status == 'birthday'? 'online': status;
		statusText.innerHTML = BX.message("IM_STATUS_"+status.toUpperCase());

		if (send)
		{
			this.BXIM.saveSettings({'status': status});
			BX.onCustomEvent(this, 'onStatusChange', [status]);
			BX.localStorage.set('mms', status, 5);
		}
	}
	if (BX.MessengerCommon.isDesktop())
		BX.desktop.setIconStatus(status);
};

BX.Messenger.prototype.resizeMainWindow = function()
{
	if (BX.MessengerCommon.isPage())
		return false;

	if (this.popupMessengerExtra.style.display == "block")
		this.popupContactListElementsSize = this.popupMessengerExtra.offsetHeight-120;
	else
		this.popupContactListElementsSize = this.popupMessengerDialog.offsetHeight-120;

	this.popupContactListElements.style.height = this.popupContactListElementsSize+'px';
};

BX.Messenger.prototype.showTopLine = function(text, buttons, closeFunction)
{
	if (typeof (text) != 'string')
		return false;

	if (typeof(closeFunction) != "function")
	{
		closeFunction = BX.delegate(function(){this.hideTopLine();}, this);
	}
	var arElements = [];
	arElements.push(BX.create('span', { props : { className : "bx-messenger-box-topline-close" }, events: {click: closeFunction}}));

	if (typeof (buttons) == 'object')
	{
		var arButtons = [];
		for (var i = 0; i < buttons.length; i++)
		{
			arButtons.push(BX.create('span', { props : { className : "bx-messenger-box-topline-button" }, html: buttons[i].title, events: {click: buttons[i].callback}}));
		}
		arElements.push(BX.create('span', { props : { className : "bx-messenger-box-topline-buttons" }, children: arButtons}));
	}

	arElements.push(BX.create('span', { props : { className : "bx-messenger-box-topline-text" }, children: [
		BX.create('span', { props : { className : "bx-messenger-box-topline-text-inner"}, html: text})
	]}));

	this.popupMessengerTopLine.innerHTML = '';
	BX.adjust(this.popupMessengerTopLine, {children: arElements});
	BX.addClass(this.popupMessengerTopLine, "bx-messenger-box-topline-show");

	return true;
};

BX.Messenger.prototype.hideTopLine = function(send)
{
	BX.removeClass(this.popupMessengerTopLine, "bx-messenger-box-topline-show");

	if (send !== false);
	{
		BX.localStorage.set('mhtl', true, 1);
	}
};

BX.Messenger.prototype.closeMenuPopup = function()
{
	if (this.popupPopupMenu != null && this.popupPopupMenuDateCreate+100 < (+new Date()))
		this.popupPopupMenu.close();
	if (this.popupSmileMenu != null)
		this.popupSmileMenu.close();
	if (this.notify.popupNotifyMore != null)
		this.notify.popupNotifyMore.destroy();
	if (this.popupChatUsers != null)
		this.popupChatUsers.close();
	if (this.webrtc.popupKeyPad != null)
		this.webrtc.popupKeyPad.destroy();
	if (this.popupChatDialog != null)
		this.popupChatDialog.destroy();
	if (this.popupTransferDialog != null)
		this.popupTransferDialog.destroy();
	if (this.popupTooltip != null)
		this.popupTooltip.destroy();
	if (this.commandPopup != null)
		this.commandPopup.close();
	if (this.popupIframeMenu != null && this.popupIframeBind)
		this.popupIframeMenu.destroy();

	if (window.obCrm && window.obCrm.olCrmSelector && window.obCrm.olCrmSelector.popup)
		window.obCrm.olCrmSelector.popup.close();

	this.closePopupFileMenu();
};

BX.Messenger.MenuPrepareList = function(menuItems)
{
	var items = [];
	for (var i = 0; i < menuItems.length; i++)
	{
		var item = menuItems[i];
		if (item == null)
			continue;

		if (!item.separator && (!item.text || !BX.type.isNotEmptyString(item.text)))
			continue;

		if (item.separator)
		{
			items.push(BX.create("div", { props : { className : "bx-messenger-menu-hr" }}));
		}
		else if (item.type == 'call')
		{
			var a = BX.create("a", {
				props : { className: "bx-messenger-popup-menu-item"},
				attrs : { title : item.title ? item.title : "",  href : item.href ? item.href : "", target: item.target ? item.target : "_blank", 'data-params': item.dataParams? JSON.stringify(item.dataParams): ""},
				events : item.onclick && BX.type.isFunction(item.onclick) ? { click : item.onclick } : null,
				html :  '<div class="bx-messenger-popup-menu-item-call"><span class="bx-messenger-popup-menu-item-left"></span><span class="bx-messenger-popup-menu-item-title">' + item.text + '</span><span class="bx-messenger-popup-menu-right"></span></div>'+
						'<div><span class="bx-messenger-popup-menu-item-left"></span><span class="bx-messenger-popup-menu-item-text">' + item.phone + '</span><span class="bx-messenger-popup-menu-right"></span></div>'
			});

			if (item.href)
				a.href = item.href;
			items.push(a);
		}
		else
		{
			var attrs = item.attrs? item.attrs: {};
			attrs['title'] = item.title ? item.title : "";
			attrs['href'] = item.href ? item.href : "";
			attrs['target'] = item.target ? item.target : "_blank";
			attrs['data-params'] = item.dataParams? JSON.stringify(item.dataParams): "";

			var a = BX.create("a", {
				props : { className: "bx-messenger-popup-menu-item"+(item.bold? " bx-messenger-popup-menu-item-bold":"")+(item.slim? " bx-messenger-popup-menu-item-slim":"")+(item.disabled? " bx-messenger-popup-menu-item-disabled":"")+(BX.type.isNotEmptyString(item.className) ? " " + item.className : "")},
				attrs : attrs,
				events : item.onclick && BX.type.isFunction(item.onclick) ? { click : item.onclick } : null,
				html :  '<span class="bx-messenger-popup-menu-item-left"></span>'+(item.icon? '<span class="bx-messenger-popup-menu-item-icon '+item.icon+'"></span>':'')+'<span class="bx-messenger-popup-menu-item-text">' + item.text + '</span><span class="bx-messenger-popup-menu-right"></span>'
			});

			if (item.href)
				a.href = item.href;
			items.push(a);
		}
	}
	return items;
};

BX.Messenger.prototype.storageSet = function(params)
{
	if (params.key == 'ims')
	{
		if (this.BXIM.settings.viewOffline != params.value.viewOffline || this.BXIM.settings.viewGroup != params.value.viewGroup)
			BX.MessengerCommon.userListRedraw(true);

		if (this.BXIM.settings.sendByEnter != params.value.sendByEnter && this.popupMessengerTextareaSendType)
			this.popupMessengerTextareaSendType.innerHTML = this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");

		if (this.BXIM.settings.sendByEnter != params.value.sendByEnter && this.popupMessengerTextareaSendType)
			this.popupMessengerTextareaSendType.innerHTML = this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");

		BX.MessengerCommon.drawTab(this.currentTab, true);

		this.BXIM.settings = params.value;
	}
	else if (params.key == 'mus')
	{
		this.updateState(true, false);
	}
	else if (params.key == 'musl')
	{
		this.updateStateLight(true, false);
	}
	else if (params.key == 'mms')
	{
		this.setStatus(params.value, false);
	}
	else if (params.key == 'mhtl')
	{
		this.hideTopLine(false);
	}
	else if (params.key == 'mct')
	{
	}
	else if (params.key == 'mrlr')
	{
		BX.MessengerCommon.recentListHide(params.value.userId, false);
	}
	else if (params.key == 'mrd')
	{
		this.BXIM.settings.viewGroup = params.value.viewGroup;
		this.BXIM.settings.viewOffline = params.value.viewOffline;

		BX.MessengerCommon.userListRedraw();
	}
	else if (params.key == 'mrm')
	{
		BX.MessengerCommon.readMessage(params.value, false, false);
	}
	else if (params.key == 'mcl')
	{
		BX.MessengerCommon.leaveFromChat(params.value, false);
	}
	else if (params.key == 'mclk')
	{
		this.kickFromChat(params.value.chatId, params.value.userId);
	}
	else if (params.key == 'mes')
	{
		this.BXIM.settings.enableSound = params.value;
	}
	else if (params.key == 'mti')
	{
		if (params.value > this.messageTmpIndex)
			this.messageTmpIndex = params.value;
	}
	else if (params.key == 'mns')
	{
		if (this.popupContactListSearchInput != null)
			this.popupContactListSearchInput.value = params.value != null? params.value+'': '';

		this.contactListSearchText = params.value != null? params.value+'': '';
	}
	else if (params.key == 'msm')
	{
		if (this.message[params.value.id])
			return;

		params.value.date = new Date(params.value.date);
		this.message[params.value.id] = params.value;

		if (this.history[params.value.recipientId])
			this.history[params.value.recipientId].push(params.value.id);
		else
			this.history[params.value.recipientId] = [params.value.id];

		if (this.showMessage[params.value.recipientId])
			this.showMessage[params.value.recipientId].push(params.value.id);
		else
			this.showMessage[params.value.recipientId] = [params.value.id];

		BX.MessengerCommon.updateStateVar(params.value, false, false);

		BX.MessengerCommon.drawTab(params.value.recipientId, true);
	}
	else if (params.key == 'uss')
	{
		this.updateStateStep = parseInt(params.value);
	}
	else if (params.key == 'mumc')
	{
		setTimeout(BX.delegate(function(){
			var send = false;
			if (this.popupMessenger != null && this.BXIM.isFocus())
			{
				delete params.value.unread[this.currentTab];
				send = true;
			}

			this.unreadMessage = params.value.unread;
			this.flashMessage = params.value.flash;

			this.updateMessageCount(send);
		}, this), 500);
	}
	else if (params.key == 'mum')
	{
		params.value.message.date = new Date(params.value.message.date);
		this.message[params.value.message.id] = params.value.message;

		if (this.showMessage[params.value.userId])
		{
			this.showMessage[params.value.userId].push(params.value.message.id);
			this.showMessage[params.value.userId] = BX.util.array_unique(this.showMessage[params.value.userId]);
		}
		else
			this.showMessage[params.value.userId] = [params.value.message.id];

		BX.MessengerCommon.drawMessage(params.value.userId, params.value.message, this.currentTab == params.value.userId);
	}
	else if (params.key == 'muum')
	{
		BX.MessengerCommon.changeUnreadMessage(params.value, false);
	}
	else if (params.key == 'mcam' && !this.BXIM.ppServerStatus)
	{
		if (this.popupMessenger != null && !this.webrtc.callInit)
			this.popupMessenger.close();
	}
};

/* OPEN LINES */
BX.Messenger.prototype.linesVoteHeadDialog = function(bindElement, sessionId, inline)
{
	inline = inline || false;

	var rating = bindElement.getAttribute('data-rating') || 0;

	var ratingNode = BX.MessengerCommon.linesVoteHeadNodes(sessionId, rating, true, inline? null: bindElement);

	if (inline)
		return ratingNode;

	this.tooltip(bindElement, ratingNode, {offsetTop: 10, offsetLeft: 12, bindOptions: {position: "bottom"}});

	return true;
}

BX.Messenger.prototype.linesOpenHistory = function(sessionId)
{
	BX.MessengerCommon.linesGetSessionHistory(sessionId);
}

BX.Messenger.prototype.linesShowHistory = function(chatId, data)
{
	if (this.popupMessengerConnectionStatusState != 'online')
		return false;

	if (this.historyWindowBlock)
		return false;

	if (this.popupHistory != null)
		this.popupHistory.destroy();

	if (!chatId)
		return false;

	var enableDisk = this.BXIM.disk.enable;

	enableDisk = false; // TODO files for session not work
	this.popupHistoryPanel = null;
	var historyPanel = this.redrawHistoryPanel('chat'+chatId, chatId, {'drawLinesJoin': data.CAN_JOIN, 'drawLinesVote': data.CAN_VOTE_HEAD, 'sessionVoteHead': data.SESSION_VOTE_HEAD, 'sessionId': data.SESSION_ID});

	this.popupHistoryElements = BX.create("div", { props : { className : "bx-messenger-history"+(enableDisk? ' bx-messenger-history-with-disk': '')+(BX.browser.IsMac()? '': ' bx-messenger-custom-scroll') }, children: [
		this.popupHistoryPanel = BX.create("div", { props : { className : "bx-messenger-panel-wrap" }, children: historyPanel}),
		BX.create("div", { props : { className : "bx-messenger-history-types" }, children : [
			BX.create("span", { props : { className : "bx-messenger-history-type bx-messenger-history-type-message" }, children : [
				this.popupHistoryItems = BX.create("div", { props : { className : "bx-messenger-history-items" }, style : {height: this.popupHistoryItemsSize+'px'}, children : [
					this.popupHistoryBodyWrap = BX.create("div", { props : { className : "bx-messenger-history-items-wrap" }})
				]})
			]}),
			BX.create("span", { props : { className : "bx-messenger-history-type bx-messenger-history-type-disk" }, children : [
				this.popupHistoryFilesItems = BX.create("div", { props : { className : "bx-messenger-history-items" }, style : {height: this.popupHistoryItemsSize+'px'}, children : [
					this.popupHistoryFilesBodyWrap = BX.create("div", { props : { className : "bx-messenger-history-items-wrap" }})
				]})
			]})
		]})
	]});

	this.popupHistory = new BX.PopupWindow('bx-messenger-popup-history', null, {
		//parentPopup: this.popupMessenger,
		autoHide: false,
		zIndex: 100,
		draggable: {restrict: true},
		closeByEsc: true,
		events : {
			onPopupClose : function() { this.destroy(); },
			onPopupDestroy : BX.delegate(function() {
				this.popupHistory = null; this.historySearch = ''; this.setClosingByEsc(true);
				this.closeMenuPopup();
				var calend = BX.calendar.get()
				if (calend)
				{
					calend.Close();
				}
			}, this)
		},
		titleBar: {content: BX.create('span', {props : { className : "bx-messenger-title" }, html: BX.message('IM_M_HISTORY')})},
		closeIcon : {'right': '13px'},
		content : this.popupHistoryElements,
		contentColor : "white",
		noAllPaddings : true
	});
	this.popupHistory.show();
	BX.bind(this.popupHistory.popupContainer, "click", BX.MessengerCommon.preventDefault);

	if (data.HISTORY['chat'+chatId])
	{
		data.HISTORY['chat'+chatId].sort(BX.delegate(function (i, ii)
		{
			i = parseInt(i);
			ii = parseInt(ii);

			if (i > ii)
			{
				return 1;
			}
			else if (i < ii)
			{
				return -1;
			}
			else
			{
				return 0;
			}
		}, this));
	}


	this.drawHistory('chat'+chatId, data.HISTORY, false, false);
	if (enableDisk)
	{
		this.drawHistoryFiles(chatId, data.FILES, false);
	}

	BX.bindDelegate(this.popupHistoryElements, 'click', {className: 'bx-messenger-ajax'}, BX.delegate(function() {
		if (BX.proxy_context.getAttribute('data-entity') == 'user')
		{
			this.openPopupExternalData(BX.proxy_context, 'user', true, {'ID': BX.proxy_context.getAttribute('data-userId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'chat')
		{
			this.openPopupExternalData(BX.proxy_context, 'chat', true, {'ID': BX.proxy_context.getAttribute('data-chatId')})
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'openlines')
		{
			this.linesOpenHistory(BX.proxy_context.getAttribute('data-sessionId'));
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'network')
		{
			this.openMessenger('network'+BX.proxy_context.getAttribute('data-networkId'))
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'date')
		{
			this.openPopupMenu(BX.proxy_context, 'shareMenu');
		}
		else if (BX.proxy_context.getAttribute('data-entity') == 'phoneCallHistory')
		{
			this.openPopupExternalData(BX.proxy_context, 'phoneCallHistory', true, {'ID': BX.proxy_context.getAttribute('data-historyID')})
		}
	}, this));

	if (this.disk.enable)
	{
		BX.bindDelegate(this.popupHistoryFilesBodyWrap, "click", {className: 'bx-messenger-file-menu'}, BX.delegate(function(e) {
			var fileId = BX.proxy_context.parentNode.parentNode.getAttribute('data-fileId');
			var chatId = BX.proxy_context.parentNode.parentNode.getAttribute('data-chatId');
			this.openPopupMenu(BX.proxy_context, 'historyFileMenu', true, {fileId: fileId, chatId: chatId});
			return BX.PreventDefault(e);
		}, this));
	}
};

BX.Messenger.prototype.linesLivechatFormShow = function(type, stage, params)
{
	return false;
}
BX.Messenger.prototype.linesLivechatFormHide = function()
{
	return this.linesLivechatFormShow();
}

BX.Messenger.prototype.linesOpenMessenger = function(userCode, params)
{
	params = params || {};
	BX.MessengerCommon.linesOpenSession(userCode, params);
}

BX.Messenger.prototype.linesCreateLead = function()
{
	var chatId = this.getChatId();
	var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);
	if (session.crm == 'N')
	{
		BX.MessengerCommon.linesCreateLead(chatId);
	}
}
BX.Messenger.prototype.linesCloseDialog = function()
{
	var chatId = this.getChatId();

	BX.MessengerCommon.linesCloseDialog(chatId);
}
BX.Messenger.prototype.linesMarkAsSpam = function()
{
	var chatId = this.getChatId();

	BX.MessengerCommon.linesMarkAsSpam(chatId);
}
BX.Messenger.prototype.linesInterceptSession = function()
{
	var chatId = this.getChatId();

	BX.MessengerCommon.linesInterceptSession(chatId);
}
BX.Messenger.prototype.linesTogglePinMode = function()
{
	var chatId = this.getChatId();
	var flag;

	var session = BX.MessengerCommon.linesGetSession(this.chat[chatId]);
	if (session.pin == 'Y')
	{
		flag = 'N';
	}
	else
	{
		flag = 'Y';
	}

	BX.MessengerCommon.linesActivatePinMode(chatId, flag);
}
BX.Messenger.prototype.linesToggleSilentMode = function()
{
	var chatId = this.getChatId();
	var flag;

	if (this.linesSilentMode[chatId])
	{
		BX.removeClass(this.popupMessengerHiddenModeButton, 'bx-messenger-textarea-hidden-active');
		flag = 'N';
	}
	else
	{
		BX.addClass(this.popupMessengerHiddenModeButton, 'bx-messenger-textarea-hidden-active');
		flag = 'Y';
	}

	this.linesSilentMode[chatId] = flag == 'Y';

	this.tooltip(this.popupMessengerHiddenModeButton, BX.message(flag == 'Y'? 'IM_OL_CHAT_STEALTH_ON': 'IM_OL_CHAT_STEALTH_OFF'), {offsetLeft: 15, showOnce: flag == 'Y'? 'OL_STEALTH_ON': 'OL_STEALTH_OFF'});
	//BX.MessengerCommon.linesActivateSilentMode(chatId, flag);
}

BX.Messenger.prototype.linesOpenTransferDialog = function(params)
{
	if (this.BXIM.messenger.popupMessengerDialog && BX.hasClass(this.BXIM.messenger.popupMessengerDialog, "bx-messenger-chat-load-last-message"))
	{
		return false;
	}
	if (this.popupTransferDialog != null)
	{
		this.popupTransferDialog.close();
		return false;
	}
	if (this.popupChatDialog != null)
	{
		this.popupChatDialog.close();
		return false;
	}

	BX.MessengerCommon.contactListSearchClear();

	this.linesTransferUser = 0;
	var bindElement = params.bind? params.bind: null;
	params.maxUsers = 1;

	this.popupTransferDialog = new BX.PopupWindow('bx-messenger-popup-transfer', bindElement, {
		//parentPopup: this.popupMessenger,
		lightShadow : true,
		offsetTop: 5,
		offsetLeft: BX.MessengerCommon.isPage()? 5: -162,
		autoHide: true,
		buttons: [
			new BX.PopupWindowButton({
				text : BX.message('IM_OL_INVITE_TRANSFER'),
				className : "popup-window-button-accept",
				events : { click : BX.delegate(function() {
					var chatId = this.getChatId();
					this.linesSendTransfer(chatId);
				}, this) }
			}),
			new BX.PopupWindowButton({
				text : BX.message('IM_M_CHAT_BTN_CANCEL'),
				events : { click : BX.delegate(function() { this.popupTransferDialog.close(); }, this) }
			})
		],
		closeByEsc: true,
		zIndex: 200,
		events : {
			onPopupClose : function() { this.destroy() },
			onPopupDestroy : BX.delegate(function() { this.popupTransferDialog = null; this.popupTransferDialogContactListElements = null; }, this)
		},
		content : BX.create("div", { props : { className : "bx-messenger-popup-newchat-wrap" }, children: [
			BX.create("div", { props : { className : "bx-messenger-popup-newchat-caption" }, html: BX.message('IM_OL_TRANSFER_TEXT')}),
			BX.create("div", { props : { className : "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-dest bx-messenger-popup-newchat-dest-even" }, children: [
				this.popupTransferDialogDestElements = BX.create("span", { props : { className : "bx-messenger-dest-items" }}),
				this.popupTransferDialogContactListSearch = BX.create("input", {props : { className : "bx-messenger-input" }, attrs: {type: "text", placeholder: BX.message(this.BXIM.bitrixIntranet? 'IM_M_SEARCH_PLACEHOLDER_CP': 'IM_M_SEARCH_PLACEHOLDER'), value: ''}})
			]}),
			this.popupTransferDialogContactListElements = BX.create("div", { props : { className : "bx-messenger-popup-newchat-box bx-messenger-popup-newchat-cl bx-messenger-recent-wrap" }, children: []})
		]})
	});

	BX.MessengerCommon.contactListPrepareSearch('popupTransferDialogContactListElements', this.popupTransferDialogContactListElements, this.popupTransferDialogContactListSearch.value, {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewBot': false, 'viewTransferOlQueue': true, 'viewOnlyIntranet': true, 'viewOfflineWithPhones': false});

	BX.bindDelegate(this.popupTransferDialogContactListElements, "click", {className: 'bx-messenger-chatlist-more'}, BX.delegate(this.toggleChatListGroup, this));

	this.popupTransferDialog.setAngle({offset: BX.MessengerCommon.isPage()? 32: 198});
	this.popupTransferDialog.show();
	this.popupTransferDialogContactListSearch.focus();
	BX.addClass(this.popupTransferDialog.popupContainer, "bx-messenger-mark");
	BX.bind(this.popupTransferDialog.popupContainer, "click", BX.PreventDefault);

	BX.bind(this.popupTransferDialogContactListSearch, "keyup", BX.delegate(function(event){
		if (event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18 || event.keyCode == 20 || event.keyCode == 244 || event.keyCode == 224 || event.keyCode == 91)
			return false;

		if (event.keyCode == 27 && this.popupTransferDialogContactListSearch.value != '')
			BX.MessengerCommon.preventDefault(event);

		if (event.keyCode == 27)
		{
			this.popupTransferDialogContactListSearch.value = '';
		}

		if (event.keyCode == 8)
		{
			var lastId = null;
			var arMentionSort = BX.util.objectSort(this.popupChatDialogUsers, 'date', 'asc');
			for (var i = 0; i < arMentionSort.length; i++)
			{
				lastId = arMentionSort[i].id;
			}
			if (lastId)
			{
				delete this.popupChatDialogUsers[lastId];
				this.linesRedrawTransferDialogDest();
			}
		}

		if (event.keyCode == 13)
		{
			this.popupTransferDialogContactListSearch.value = '';
			var item = BX.findChildByClassName(this.popupTransferDialogContactListElements, "bx-messenger-cl-item");
			if (item)
			{
				if (this.popupTransferDialogContactListSearch.value != '')
				{
					this.popupTransferDialogContactListSearch.value = '';
				}
				if (this.linesTransferUser > 0)
				{
					params.maxUsers = params.maxUsers+1;
					if (params.maxUsers > 0)
						BX.show(this.popupTransferDialogContactListSearch);
					this.linesTransferUser = 0;
				}
				else
				{
					if (params.maxUsers > 0)
					{
						params.maxUsers = params.maxUsers-1;
						if (params.maxUsers <= 0)
							BX.hide(this.popupTransferDialogContactListSearch);

						this.linesTransferUser = item.getAttribute('data-userId');
					}
				}
				this.linesRedrawTransferDialogDest();
			}
		}

		BX.MessengerCommon.contactListPrepareSearch('popupTransferDialogContactListElements', this.popupTransferDialogContactListElements, this.popupTransferDialogContactListSearch.value, {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewBot': false, 'viewTransferOlQueue': true, 'viewOnlyIntranet': true, 'viewOfflineWithPhones': false, timeout: 100});
	}, this));
	BX.bindDelegate(this.popupTransferDialogDestElements, "click", {className: 'bx-messenger-dest-del'}, BX.delegate(function() {
		this.linesTransferUser = 0;
		params.maxUsers = params.maxUsers+1;
		if (params.maxUsers > 0)
			BX.show(this.popupTransferDialogContactListSearch);
		this.linesRedrawTransferDialogDest();
	}, this));
	BX.bindDelegate(this.popupTransferDialogContactListElements, "click", {className: 'bx-messenger-cl-item'}, BX.delegate(function(e) {
		if (this.popupTransferDialogContactListSearch.value != '')
		{
			this.popupTransferDialogContactListSearch.value = '';
			BX.MessengerCommon.contactListPrepareSearch('popupTransferDialogContactListElements', this.popupTransferDialogContactListElements, '', {'viewChat': false, 'viewOpenChat': false, 'viewOffline': false, 'viewBot': false, 'viewTransferOlQueue': true, 'viewOnlyIntranet': true, 'viewOfflineWithPhones': false});
		}
		if (this.linesTransferUser)
		{
			params.maxUsers = params.maxUsers+1;
			this.linesTransferUser = 0;
		}
		else
		{
			if (params.maxUsers <= 0)
				return false;
			params.maxUsers = params.maxUsers-1;
			this.linesTransferUser = BX.proxy_context.getAttribute('data-userId');
		}

		if (params.maxUsers <= 0)
			BX.hide(this.popupTransferDialogContactListSearch);
		else
			BX.show(this.popupTransferDialogContactListSearch);

		this.linesRedrawTransferDialogDest();

		return BX.PreventDefault(e);
	}, this));
};

BX.Messenger.prototype.linesRedrawTransferDialogDest = function()
{
	var content = '';
	var count = 0;

	var isQueue = this.linesTransferUser.toString().substr(0, 5) == 'queue';
	var queueId = isQueue? this.linesTransferUser.toString().substr(5): 0;

	if (isQueue)
	{
		var queueName = this.linesTransferUser;
		for (var i = 0; i < this.openlines.queue.length; i++)
		{
			if (this.openlines.queue[i].id == queueId)
			{
				queueName = this.openlines.queue[i].name;
				break;
			}
		}

		count++;
		content += '<span class="bx-messenger-dest-block bx-messenger-dest-block-queue">'+
						'<span class="bx-messenger-dest-text">'+queueName+'</span>'+
					'<span class="bx-messenger-dest-del" data-userId="'+this.linesTransferUser+'"></span></span>';
	}
	else if (this.linesTransferUser > 0)
	{
		count++;
		content += '<span class="bx-messenger-dest-block'+(this.users[this.linesTransferUser].extranet? ' bx-messenger-dest-block-extranet': '')+'">'+
						'<span class="bx-messenger-dest-text">'+(this.users[this.linesTransferUser].name)+'</span>'+
					'<span class="bx-messenger-dest-del" data-userId="'+this.linesTransferUser+'"></span></span>';
	}

	this.popupTransferDialogDestElements.innerHTML = content;
	this.popupTransferDialogDestElements.parentNode.scrollTop = this.popupTransferDialogDestElements.parentNode.offsetHeight;

	if (BX.util.even(count))
		BX.addClass(this.popupTransferDialogDestElements.parentNode, 'bx-messenger-popup-newchat-dest-even');
	else
		BX.removeClass(this.popupTransferDialogDestElements.parentNode, 'bx-messenger-popup-newchat-dest-even');

	this.popupTransferDialogContactListSearch.focus();
};

BX.Messenger.prototype.linesSendTransfer = function(chatId)
{
	if (this.BXIM.messenger.blockJoinChat[chatId])
		return false;

	if (this.chat[chatId] && this.chat[chatId].entity_type != 'LINES')
		return false;

	if (this.linesTransferUser <= 0)
		return false;

	if (this.popupTransferDialog)
		this.popupTransferDialog.close();

	this.BXIM.messenger.blockJoinChat[chatId] = true;

	if(!BX.MessengerCommon.userInChat(chatId))
		BX.MessengerCommon.dialogCloseCurrent(true);
	else
		BX.MessengerCommon.dialogCloseCurrent(false);

	if (this.linesTransferUser.toString().substr(0, 5) == 'queue')
	{
		var transferQueueId = this.linesTransferUser.substr(5);
		for (var i = 0; i < this.BXIM.messenger.openlines.queue.length; i++)
		{
			if (this.BXIM.messenger.openlines.queue[i].id == transferQueueId)
			{
				this.BXIM.messenger.openlines.queue[i].transfer_count = parseInt(this.BXIM.messenger.openlines.queue[i].transfer_count)+1;
			}
		}
	}

	BX.ajax({
		url: this.BXIM.pathToAjax+'?LINES_TRANSFER&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'COMMAND': 'transfer', 'CHAT_ID' : chatId, 'TRANSFER_ID': this.linesTransferUser, 'IM_OPEN_LINES' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(){
			this.BXIM.messenger.blockJoinChat[chatId] = false;
		}, this),
		onfailure: BX.delegate(function(){
			this.BXIM.messenger.blockJoinChat[chatId] = false;
		}, this)
	});
};


BX.IM.Desktop = function(BXIM, params)
{
	this.BXIM = BXIM;

	this.initDate = new Date();

	this.clientVersion = false;
	this.markup = BX('placeholder-messanger');
	this.htmlWrapperHead = null;
	this.showNotifyId = {};
	this.showMessageId = {};
	this.lastSetIcon = null;

	this.topmostWindow = null;
	this.topmostWindowTimeout = null;
	this.topmostWindowCloseTimeout = null;

	this.minCallVideoWidth = 320;
	this.minCallVideoHeight = 180;
	this.minCallWidth = 320;
	this.minCallHeight = 35;
	this.minHistoryWidth = 608;
	this.minHistoryDiskWidth = 780;
	this.minHistoryHeight = 593;
	this.minSettingsWidth = 620;
	this.startSettingsHeight = BX.browser.IsMac()? 448: 357;
	this.minSettingsHeight = 137;

	if (this.BXIM.init && BX.MessengerCommon.isPage())
	{
		BX.MessengerWindow.addTab({
			id: 'config',
			title: BX.message('IM_SETTINGS'),
			order: 150,
			target: false,
			events: {
				open: BX.delegate(function(e){
					this.BXIM.openSettings({'active': BX.MessengerWindow.getCurrentTab()});
				}, this)
			}
		});

		BX.MessengerWindow.addSeparator({
			order: 500
		});

		if (!this.BXIM.bitrix24net)
		{
			BX.MessengerWindow.addTab({
				id: 'im-lf',
				title: BX.message('IM_DESKTOP_GO_SITE').replace('#COUNTER#', ''),
				order: 550,
				target: false,
				events: {
					open: BX.delegate(function(){
						BX.MessengerWindow.browse(BX.MessengerWindow.getCurrentUrl()+this.BXIM.path.lf);
					}, this)
				}
			});
		}

		if (this.BXIM.animationSupport && /Microsoft Windows NT 5/i.test(navigator.userAgent))
			this.BXIM.animationSupport = false;

		if (BX.MessengerCommon.isDesktop())
			this.BXIM.changeFocus(BX.desktop.windowIsFocused());

		if (this.BXIM.context == 'DESKTOP' || this.BXIM.context == 'POPUP-FULLSCREEN')
		{
			BX.bind(window, "keydown", BX.delegate(function(e) {
				if (!BX.MessengerWindow.isPopupShow())
					return false;

				if (!(BX.MessengerWindow.getCurrentTab() == 'im' || BX.MessengerWindow.getCurrentTab() == 'notify' || BX.MessengerWindow.getCurrentTab() == 'im-phone' || BX.MessengerWindow.getCurrentTab() == 'im-ol'))
					return false;

				if (e.keyCode == 27) // TODO check
				{
					if (this.messenger.popupSmileMenu)
					{
						this.messenger.popupSmileMenu.destroy();
					}
					else if (this.messenger.popupMessengerFileButton != null && BX.hasClass(this.messenger.popupMessengerFileButton, 'bx-messenger-textarea-file-active'))
					{
						this.messenger.closePopupFileMenu();
					}
					else if (this.messenger.popupPopupMenu)
					{
						this.messenger.popupPopupMenu.destroy();
					}
					else if (this.messenger.popupChatDialog && this.messenger.popupChatDialogContactListSearch.value.length >= 0)
					{
						this.messenger.popupChatDialogContactListSearch.value = '';
					}
					else if (this.BXIM.extraOpen)
					{
						//BX.MessengerWindow.changeTab('im');
						//this.messenger.extraClose(true);
					}
					else if (this.messenger.renameChatDialogInput && this.messenger.renameChatDialogInput.value.length > 0)
					{
						this.messenger.renameChatDialogInput.value = BX.util.htmlspecialcharsback(this.messenger.chat[this.messenger.currentTab.toString().substr(4)].name);
						this.messenger.popupMessengerTextarea.focus();
					}
					else if (this.messenger.popupContactListSearchInput && (this.messenger.popupContactListSearchInput.value.length > 0 || this.messenger.chatList))
					{
						BX.MessengerCommon.contactListSearch({'keyCode': 27});
						this.messenger.popupMessengerTextarea.focus();
					}
					else
					{
						if (BX.util.trim(this.messenger.popupMessengerEditTextarea.value).length > 0)
						{
							this.messenger.editMessageCancel();
						}
						else if (BX.util.trim(this.messenger.popupMessengerTextarea.value).length <= 0 && !this.webrtc.callInit)
						{
							this.messenger.textareaHistory[this.messenger.currentTab] = '';
							this.messenger.popupMessengerTextarea.value = "";
							if (BX.MessengerCommon.isDesktop())
							{
								BX.desktop.windowCommand('hide');
							}
							else if (this.messenger.popupMessenger)
							{
								this.messenger.popupMessenger.destroy();
							}
						}
						else if (e.shiftKey)
						{
							this.messenger.textareaHistory[this.messenger.currentTab] = '';
							this.messenger.popupMessengerTextarea.value = "";
						}
					}
				}
				else if (e.altKey == true)
				{
					if (e.keyCode == 49 || e.keyCode == 50 || e.keyCode == 51
						|| e.keyCode == 52 || e.keyCode == 53 || e.keyCode == 54
						|| e.keyCode == 55 || e.keyCode == 56 || e.keyCode == 57)
					{
						this.messenger.openMessenger(this.messenger.recentListIndex[parseInt(e.keyCode)-49]);
						BX.PreventDefault(e);
					}
					else if (e.keyCode == 48)
					{
						this.messenger.popupContactListSearchInput.focus();
						BX.PreventDefault(e);
					}
				}
			}, this));
		}

		if (BX.MessengerCommon.isDesktop())
		{
			BX.desktop.syncPause(false);

			BX.desktop.addCustomEvent("bxImClickNewMessage", BX.delegate(function(userId) {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('im');
				this.BXIM.openMessenger(userId);
			}, this));
			BX.desktop.addCustomEvent("bxImClickCloseMessage", BX.delegate(function(userId) {
				BX.MessengerCommon.readMessage(userId);
			}, this));
			BX.desktop.addCustomEvent("bxImClickCloseNotify", BX.delegate(function(notifyId) {
				this.BXIM.notify.viewNotify(notifyId);
			}, this));
			BX.desktop.addCustomEvent("bxImClickNotify", BX.delegate(function() {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('notify');
			}, this));
			BX.desktop.addCustomEvent("bxCallDecline", BX.delegate(function() {
				var callVideo = this.webrtc.callVideo;
				this.webrtc.callSelfDisabled = true;
				this.webrtc.callCommand(this.webrtc.callChatId, 'decline', {'ACTIVE': this.webrtc.callActive? 'Y': 'N', 'INITIATOR': this.webrtc.initiator? 'Y': 'N'});
				this.BXIM.playSound('stop');
				if (callVideo && this.webrtc.callStreamSelf != null)
					this.webrtc.callOverlayVideoClose();
				else
					this.webrtc.callOverlayClose();
			}, this));
			BX.desktop.addCustomEvent("bxPhoneAnswer", BX.delegate(function() {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('im');

				this.BXIM.stopRepeatSound('ringtone');
				this.webrtc.phoneIncomingAnswer();

				this.closeTopmostWindow();
			}, this));
			BX.desktop.addCustomEvent("bxPhoneSkip", BX.delegate(function() {
				this.webrtc.phoneCallFinish();
				this.webrtc.callAbort();
				this.webrtc.callOverlayClose();
			}, this));
			BX.desktop.addCustomEvent("bxCallOpenDialog", BX.delegate(function() {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('im');
				if (this.BXIM.dialogOpen)
				{
					if (this.webrtc.callOverlayUserId > 0)
					{
						this.messenger.openChatFlag = false;
						BX.MessengerCommon.openDialog(this.webrtc.callOverlayUserId, false, false);
					}
					else
					{
						this.messenger.openChatFlag = true;
						BX.MessengerCommon.openDialog('chat'+this.webrtc.callOverlayChatId, false, false);
					}
				}
				else
				{
					if (this.webrtc.callOverlayUserId > 0)
					{
						this.messenger.openChatFlag = false;
						this.messenger.currentTab = this.webrtc.callOverlayUserId;
					}
					else
					{
						this.messenger.openChatFlag = true;
						this.messenger.currentTab = 'chat'+this.webrtc.callOverlayChatId;
					}
					this.messenger.extraClose(true, false);
				}
				this.webrtc.callOverlayToggleSize(false);
			}, this));
			BX.desktop.addCustomEvent("bxCallMuteMic", BX.delegate(function() {
				if (this.webrtc.phoneCurrentCall)
					this.webrtc.phoneToggleAudio();
				else
					this.webrtc.toggleAudio();

				var icon = BX.findChildByClassName(BX('bx-messenger-call-overlay-button-mic'), "bx-messenger-call-overlay-button-mic");
				if (icon)
					BX.toggleClass(icon, 'bx-messenger-call-overlay-button-mic-off');
			}, this));
			BX.desktop.addCustomEvent("bxCallAnswer", BX.delegate(function(chatId, userId, video, callToGroup) {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('im');
				this.webrtc.callActive = true;

				BX.ajax({
					url: this.BXIM.pathToCallAjax+'?CALL_ANSWER&V='+this.BXIM.revision,
					method: 'POST',
					dataType: 'json',
					timeout: 30,
					data: {'IM_CALL' : 'Y', 'COMMAND': 'answer', 'CHAT_ID': chatId, 'CALL_TO_GROUP': callToGroup? 'Y': 'N', 'RECIPIENT_ID' : this.callUserId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
					onsuccess: BX.delegate(function(){
						this.webrtc.callDialog();
					}, this)
				});
			}, this));
			BX.desktop.addCustomEvent("bxCallJoin", BX.delegate(function(chatId, userId, video, callToGroup) {
				BX.desktop.windowCommand("show");
				BX.desktop.changeTab('im');
				this.webrtc.callAbort();
				this.webrtc.callOverlayClose(false);
				this.webrtc.callInvite(callToGroup? 'chat'+chatId: userId, video);
			}, this));

			BX.desktop.addCustomEvent("bxImClearHistory", BX.delegate(function(userId) {
				this.messenger.history[userId] = [];
				this.messenger.showMessage[userId] = [];

				if (this.BXIM.init)
					BX.MessengerCommon.drawTab(userId);
			}, this));
			BX.desktop.addCustomEvent("bxSaveSettings", BX.delegate(function(settings) {
				this.BXIM.settings = settings;
				if (this.BXIM.messenger != null)
				{
					var changeTab = BX.MessengerWindow.currentTab == 'im-ol' || BX.MessengerWindow.currentTab == 'im';
					BX.MessengerCommon.drawTab(this.messenger.currentTab, true, 0, changeTab);
					BX.MessengerCommon.userListRedraw(true);
					if (this.BXIM.messenger.popupMessengerTextareaSendType)
						this.BXIM.messenger.popupMessengerTextareaSendType.innerHTML = this.BXIM.settings.sendByEnter? 'Enter': (BX.browser.IsMac()? "&#8984;+Enter": "Ctrl+Enter");

					if (BX.MessengerCommon.isPage() && this.BXIM.bitrixOpenLines)
					{
						this.messenger.toggleLinesTab();
						this.messenger.toggleLinesNewGroup();
					}
				}
				if(this.BXIM.webrtc != null)
				{
					this.BXIM.webrtc.readDefaults();
				}
			}, this));
			BX.desktop.addCustomEvent("bxSaveColor", BX.delegate(function(params) {
				BX.MessengerCommon.setColor(params.color, params.chatId);
			}, this));
			BX.desktop.addCustomEvent("bxImClickConfirmNotify", BX.delegate(function(notifyId) {
				delete this.BXIM.notify.notify[notifyId];
				delete this.BXIM.notify.unreadNotify[notifyId];
				delete this.BXIM.notify.flashNotify[notifyId];
				this.BXIM.notify.updateNotifyCount(false);
				if (this.BXIM.openNotify)
					this.BXIM.notify.openNotify(true, true);
			}, this));

			BX.desktop.addCustomEvent("BXUserAway", BX.delegate(this.onAwayAction, this));

			BX.desktop.addCustomEvent("BXTrayAction", BX.delegate(this.onTrayAction, this));

			BX.desktop.addCustomEvent("BXWakeAction", BX.delegate(this.onWakeAction, this));

			BX.desktop.addCustomEvent("BXForegroundChanged", BX.delegate(function(focus)
			{
				clearTimeout(this.BXIM.windowFocusTimeout);
				this.BXIM.windowFocusTimeout = setTimeout(BX.delegate(function(){
					this.BXIM.changeFocus(focus);
					if (this.BXIM.isFocus() && this.messenger && this.messenger.unreadMessage[this.messenger.currentTab] && this.messenger.unreadMessage[this.messenger.currentTab].length>0)
						BX.MessengerCommon.readMessage(this.messenger.currentTab);

					if (this.BXIM.isFocus('notify') && this.notify)
					{
						if (this.notify.unreadNotifyLoad)
							this.notify.loadNotify();
						else if (this.notify.notifyUpdateCount > 0)
							this.notify.viewNotifyAll();
					}
					if (focus)
					{
						this.closeCallFloatDialog();
					}
					else
					{
						this.openCallFloatDialog();
					}
				}, this), focus? 500: 0);
			}, this));

			BX.desktop.addCustomEvent("BXTopmostMoved", BX.delegate(function(x, y)
			{
				x = parseInt(x);
				y = parseInt(y);
				if (x >= 0 && y >= 0)
				{
					BXDesktopSystem.StoreSettings('global_topmost_x', ''+x);
					BXDesktopSystem.StoreSettings('global_topmost_y', ''+y);
				}
			}, this));

			BX.bind(window, "blur", BX.delegate(function(){
				this.openCallFloatDialog();
			}, this));
			BX.bind(window, "focus", BX.delegate(function(){
				this.closeCallFloatDialog();
			}, this));

			BX.desktop.addCustomEvent("BXTrayMenu", BX.delegate(function (){
				var lFcounter = BXIM.notify.getCounter('**');
				var notifyCounter = BXIM.notify.getCounter('im_notify');
				var messengerCounter = BXIM.notify.getCounter('im_message');

				BX.desktop.addTrayMenuItem({Id: "messenger", Order: 100,Title: (BX.message('IM_DESKTOP_OPEN_MESSENGER') || '').replace('#COUNTER#', (messengerCounter>0? '('+messengerCounter+')':'')), Callback: function(){
					BX.desktop.windowCommand("show");
					BX.desktop.changeTab('im');
					BXIM.messenger.openMessenger(BXIM.messenger.currentTab);
				},Default: true	});

				BX.desktop.addTrayMenuItem({Id: "notify",Order: 120,Title: (BX.message('IM_DESKTOP_OPEN_NOTIFY') || '').replace('#COUNTER#', (notifyCounter>0? '('+notifyCounter+')':'')), Callback: function(){
					BX.desktop.windowCommand("show");
					BX.desktop.changeTab('notify');
					BXIM.notify.openNotify(false, true);
				}});
				BX.desktop.addTrayMenuItem({Id: "bdisk",Order: 130, Title: BX.message('IM_DESKTOP_BDISK'), Callback: function(){
					if (BX.desktop.diskAttachStatus())
					{
						BX.desktop.diskOpenFolder();
					}
					else
					{
						BX.desktop.windowCommand("show");
						BX.desktop.changeTab('disk');
					}
				}});
				BX.desktop.addTrayMenuItem({Id: "site",Order: 140, Title: (BX.message('IM_DESKTOP_GO_SITE') || '').replace('#COUNTER#', (lFcounter>0? '('+lFcounter+')':'')), Callback: function(){
					BX.desktop.browse(BX.desktop.getCurrentUrl());
				}});
				BX.desktop.addTrayMenuItem({Id: "separator1",IsSeparator: true, Order: 150});
				BX.desktop.addTrayMenuItem({Id: "settings",Order: 160, Title: BX.message('IM_DESKTOP_SETTINGS'), Callback: function(){
					BXIM.openSettings();
				}});
				BX.desktop.addTrayMenuItem({Id: "separator2",IsSeparator: true,Order: 1000});
				BX.desktop.addTrayMenuItem({Id: "logout",Order: 1010, Title: BX.message('IM_DESKTOP_LOGOUT'),Callback: function(){ BX.desktop.logout(false, 'tray_menu') }});
			}, this));
			BX.desktop.addCustomEvent("BXProtocolUrl", BX.delegate(function(command, params) {
				console.log('BXProtocolUrl', command, params? JSON.stringify(params): "");
				params = params? params: {}
				if (params.bitrix24net && params.bitrix24net == 'Y' && !this.BXIM.bitrix24net)
					return false;

				for (var i in params)
				{
					params[i] = decodeURIComponent(params[i]);
				}

				if (command == 'messenger')
				{
					if (params.dialog)
					{
						this.BXIM.openMessenger(params.dialog);
					}
					else if (params.chat)
					{
						this.BXIM.openMessenger('chat'+params.chat);
					}
					else
					{
						this.BXIM.openMessenger();
					}
					if (params.tab)
					{
						BX.MessengerWindow.changeTab(params.tab, true);
					}
					BX.desktop.setActiveWindow();
					BX.desktop.windowCommand("show");
				}
				else if (command == 'chat' && params.id)
				{
					this.BXIM.openMessenger('chat'+params.id);
					BX.desktop.setActiveWindow();
					BX.desktop.windowCommand("show");
				}
				else if (command == 'chat' && params.create)
				{
					this.BXIM.openMessenger();
					this.BXIM.messenger.openChatCreateForm(params.create);
					BX.desktop.setActiveWindow();
					BX.desktop.windowCommand("show");
				}
				else if (command == 'notify')
				{
					this.BXIM.openNotify({'force': true});
					BX.desktop.setActiveWindow();
					BX.desktop.windowCommand("show");
				}
				else if (command == 'history' && params.user)
				{
					if (params.dialog)
					{
						this.BXIM.openHistory(params.dialog);
					}
					else if (params.chat)
					{
						this.BXIM.openHistory('chat'+params.chat);
					}
					BX.desktop.setActiveWindow();
					BX.desktop.windowCommand("show");
				}
				else if (command == 'callto')
				{
					if (params.video)
					{
						this.BXIM.callTo(params.video, true);
						BX.desktop.setActiveWindow();
						BX.desktop.windowCommand("show");
					}
					else if (params.audio)
					{
						this.BXIM.callTo(params.audio, false);
						BX.desktop.setActiveWindow();
						BX.desktop.windowCommand("show");
					}
					else if (params.phone)
					{
						if (params.params)
						{
							this.webrtc.phoneCall(unescape(params.phone), BX.desktopUtils.decodeParams(params.params));
						}
						else
						{
							this.BXIM.phoneTo(unescape(params.phone));
						}
					}

				}
				else if (command == 'calllist')
				{
					if(!params.id)
						return;

					this.BXIM.startCallList(params.id, BX.desktopUtils.decodeParams(params.params));
				}
			}, this));

			BX.addCustomEvent("onPullEvent-webdav", function(command,params)
			{
				BX.desktop.diskReportStorageNotification(command, params);
			});
		}

		BX.addCustomEvent("onPullEvent-main", BX.delegate(function(command,params)
		{
			if (command == 'user_counter' && params[BX.message('SITE_ID')] && params[BX.message('SITE_ID')].hasOwnProperty('**'))
			{
				var lfCounter = parseInt(params[BX.message('SITE_ID')]['**']);
				this.notify.updateNotifyCounters({'**':lfCounter});
			}
		}, this));
	}
};

BX.IM.Desktop.prototype.run = function()
{
	return BX.MessengerCommon.isPage();
};

BX.IM.Desktop.prototype.ready = function()
{
	return BX.MessengerCommon.isDesktop();
};

BX.IM.Desktop.prototype.getCurrentUrl = function()
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	return BX.desktop.getCurrentUrl();
}

BX.IM.Desktop.prototype.enableInVersion = function(version)
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	return BX.desktop.enableInVersion(version);
}

BX.IM.Desktop.prototype.addCustomEvent = function(eventName, eventHandler)
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	BX.desktop.addCustomEvent(eventName, eventHandler);
}

BX.IM.Desktop.prototype.onCustomEvent = function(windowTarget, eventName, arEventParams)
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	BX.desktop.onCustomEvent(windowTarget, eventName, arEventParams);
};

BX.IM.Desktop.prototype.windowCommand = function(command, currentWindow)
{
	if (!BX.MessengerCommon.isDesktop()) return false;

	if (typeof(currentWindow) == "undefined")
		BX.desktop.windowCommand(command)
	else
		BX.desktop.windowCommand(currentWindow, command)
};

BX.IM.Desktop.prototype.browse = function(url)
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	BX.desktop.browse(url);
};

BX.IM.Desktop.prototype.drawOnPlaceholder = function(content)
{
	if (this.markup == null || !BX.type.isDomNode(content)) return false;

	this.markup.innerHTML = '';
	this.markup.appendChild(content);
};

BX.IM.Desktop.prototype.openNewNotify = function(notifyId, content, js)
{
	if (!BX.MessengerCommon.isDesktop()) return;
	if (content == "") return false;

	if (this.showNotifyId[notifyId])
		return false;

	this.showNotifyId[notifyId] = true;

	var sendNotify = {};
	sendNotify[notifyId] = this.BXIM.notify.notify[notifyId];

	BXDesktopSystem.ExecuteCommand('notification.show.html', this.getHtmlPage(content, js, {'notify' : sendNotify}, 'im-notify-popup'));
};

BX.IM.Desktop.prototype.openNewMessage = function(messageId, content, js)
{
	if (!BX.MessengerCommon.isDesktop()) return;
	if (content == "") return false;

	if (this.showMessageId[messageId])
		return false;

	this.showMessageId[messageId] = true;

	BXDesktopSystem.ExecuteCommand('notification.show.html', this.getHtmlPage(content, js, true, 'im-notify-popup'));
};

BX.IM.Desktop.prototype.adjustSize = function()
{
	documentOffsetHeight = document.body.offsetHeight;
	if (BX.MessengerCommon.isPage() && !BX.MessengerCommon.isDesktop())
	{
		if (this.BXIM.context == 'POPUP-FULLSCREEN' && BX.hasClass(BX.MessengerWindow.popup, 'bx-im-fullscreen-closed'))
		{
			return false;
		}
		if (this.BXIM.context == "LINES")
		{
			if (window.innerHeight < BX.MessengerWindow.minHeight)
			{
				return false;
			}
		}
		else if (BX.MessengerWindow.content)
		{
			documentOffsetHeight = BX.MessengerWindow.content.offsetHeight;
		}

		var newHeight = documentOffsetHeight-this.initHeight;
		this.initHeight = documentOffsetHeight;
	}
	else if (!BX.MessengerCommon.isDesktop() || !this.BXIM.init  || !this.BXIM.messenger || !this.BXIM.notify)
	{
		return false;
	}
	else
	{
		if (window.innerHeight < BX.MessengerWindow.minHeight)
			return false;
		var newHeight = documentOffsetHeight-this.initHeight;
		this.initHeight = documentOffsetHeight;
	}
	this.BXIM.messenger.popupMessengerBodySize = Math.max(this.BXIM.messenger.popupMessengerBodySize+newHeight, this.BXIM.messenger.popupMessengerBodySizeMin-(this.BXIM.messenger.popupMessengerTextareaSize-30));
	if (this.BXIM.messenger.popupMessengerBody != null)
	{
		this.BXIM.messenger.popupMessengerBody.style.height = this.BXIM.messenger.popupMessengerBodySize+'px';
		this.BXIM.messenger.popupMessengerBodyPanel.style.height = this.BXIM.messenger.popupMessengerBodyDialog.offsetHeight+'px';
		this.BXIM.messenger.redrawChatHeader();
	}

	this.BXIM.messenger.popupContactListElementsSize = Math.max(this.BXIM.messenger.popupContactListElementsSize+newHeight, this.BXIM.messenger.popupContactListElementsSizeMin);
	if (this.BXIM.messenger.popupContactListElements != null)
		this.BXIM.messenger.popupContactListElements.style.height = this.BXIM.messenger.popupContactListElementsSize+'px';

	this.BXIM.messenger.popupMessengerFullHeight = documentOffsetHeight;
	if (this.BXIM.messenger.popupMessengerExtra != null)
		this.BXIM.messenger.popupMessengerExtra.style.height = this.BXIM.messenger.popupMessengerFullHeight+'px';

	this.BXIM.notify.popupNotifySize = Math.max(this.BXIM.notify.popupNotifySize+newHeight, this.BXIM.notify.popupNotifySizeMin);
	if (this.BXIM.notify.popupNotifyItem != null)
		this.BXIM.notify.popupNotifyItem.style.height = this.BXIM.notify.popupNotifySize+'px';

	if (this.BXIM.webrtc.callOverlay)
	{
		this.BXIM.webrtc.callOverlay.style.transition = 'none';
		this.BXIM.webrtc.callOverlay.style.width = (this.BXIM.messenger.popupMessengerExtra.style.display == "block"? this.BXIM.messenger.popupMessengerExtra.offsetWidth-1: this.BXIM.messenger.popupMessengerDialog.offsetWidth-1)+'px';
		this.BXIM.webrtc.callOverlay.style.height = (this.BXIM.messenger.popupMessengerFullHeight-1)+'px';
	}

	if (this.BXIM.messenger.chatCreateFormBody)
	{
		BX.style(this.BXIM.messenger.chatCreateFormBody, 'height', this.BXIM.messenger.popupMessengerBodySize+'px');
	}
	if (this.BXIM.messenger.popupCreateChatTextarea)
	{
		BX.style(this.BXIM.messenger.popupCreateChatTextarea, 'height', this.BXIM.messenger.popupMessengerTextareaSize+'px');
	}

	this.BXIM.messenger.closeMenuPopup();

	if (BX.MessengerCommon.isDesktop())
	{
		clearTimeout(this.BXIM.adjustSizeTimeout);
		this.BXIM.adjustSizeTimeout = setTimeout(BX.delegate(function(){
			this.BXIM.setLocalConfig('global_msz_v2', {
				'wz': this.BXIM.messenger.popupMessengerFullWidth,
				'ta2': this.BXIM.messenger.popupMessengerTextareaSize,
				'b': this.BXIM.messenger.popupMessengerBodySize,
				'cl': this.BXIM.messenger.popupContactListSize,
				'hi': this.BXIM.messenger.popupHistoryItemsSize,
				'fz': this.BXIM.messenger.popupMessengerFullHeight,
				'ez': this.BXIM.messenger.popupContactListElementsSize,
				'nz': this.BXIM.notify.popupNotifySize,
				'hf': this.BXIM.messenger.popupHistoryFilterVisible,
				'dw': window.innerWidth,
				'dh': window.innerHeight,
				'place': 'desktop'
			});
			if (this.BXIM.webrtc.callOverlay)
				this.BXIM.webrtc.callOverlay.style.transition = '';
		}, this), 500);
	}

	return true;
};

BX.IM.Desktop.prototype.autoResize = function(window)
{
	if (!BX.MessengerCommon.isDesktop()) return;

	BX.desktop.resize();
};

BX.IM.Desktop.prototype.openSettings = function(content, js, params)
{
	if (!BX.MessengerCommon.isDesktop()) return false;
	params = params || {};

	if(params.minSettingsWidth)
		this.minSettingsWidth = params.minSettingsWidth;

	if(params.minSettingsHeight)
		this.minSettingsHeight = params.minSettingsHeight;

	BX.desktop.createWindow("settings", BX.delegate(function(settings) {
		settings.SetProperty("clientSize", { Width: this.minSettingsWidth, Height: this.startSettingsHeight });
		settings.SetProperty("minClientSize", { Width: this.minSettingsWidth, Height: this.minSettingsHeight });
		settings.SetProperty("resizable", false);
		settings.SetProperty("title", BX.message('IM_SETTINGS'));
		settings.ExecuteCommand("html.load", this.getHtmlPage(content, js, {}));
	},this));
};

BX.IM.Desktop.prototype.openHistory = function(userId, content, js)
{
	if (!BX.MessengerCommon.isDesktop()) return false;

	BX.desktop.createWindow("history", BX.delegate(function(history)
	{
		var data = {'chat':{}, 'users':{}, 'files':{}};

		var diskEnable = this.messenger.disk.enable;
		if (userId.toString().substr(0,4) == 'chat')
		{
			var chatId = userId.substr(4);
			data['chat'][chatId] = this.messenger.chat[chatId];
			data['files'][chatId] = this.disk.files[chatId];
			for (var i = 0; i < this.messenger.userInChat[chatId].length; i++)
				data['users'][this.messenger.userInChat[chatId][i]] = this.messenger.users[this.messenger.userInChat[chatId][i]];
		}
		else
		{
			chatId = this.messenger.userChat[userId]? this.messenger.userChat[userId]: 0;

			data['userChat'] = {}
			data['userChat'][userId] = chatId;
			data['users'][userId] = this.messenger.users[userId];
			data['users'][this.BXIM.userId] = this.messenger.users[this.BXIM.userId];
			data['files'][chatId] = this.disk.files[chatId];
		}
		history.SetProperty("clientSize", { Width: diskEnable? this.minHistoryDiskWidth: this.minHistoryWidth, Height: this.minHistoryHeight });
		history.SetProperty("minClientSize", { Width: diskEnable? this.minHistoryDiskWidth: this.minHistoryWidth, Height: this.minHistoryHeight });
		history.SetProperty("resizable", false);
		history.ExecuteCommand("html.load", this.getHtmlPage(content, js, data));
		history.SetProperty("title", BX.message('IM_M_HISTORY'));
	},this));
};

BX.IM.Desktop.prototype.openCallFloatDialog = function()
{
	if (!this.BXIM.init || !BX.MessengerCommon.isDesktop() || !this.webrtc || !this.webrtc.callActive || this.topmostWindow || this.phoneTransferEnabled)
		return false;

	if (this.webrtc.callVideo && !this.webrtc.callStreamMain)
		return false;

	if (!this.webrtc.callOverlayTitleBlock)
		return false;

	this.openTopmostWindow("callFloatDialog", 'BXIM.webrtc.callFloatDialog("'+BX.util.jsencode(this.webrtc.callOverlayTitleBlock.innerHTML.replace(/<\/?[^>]+>/gi, ' '))+'", "'+(this.webrtc.callVideo? this.webrtc.callOverlayVideoMain.src: '')+'", '+(this.webrtc.audioMuted?1:0)+')', {}, 'im-desktop-call');
};

BX.IM.Desktop.prototype.closeCallFloatDialog = function()
{
	if (!BX.MessengerCommon.isDesktop() || !this.topmostWindow)
		return false;

	if (this.webrtc.callActive)
	{
		if (this.webrtc.callOverlayUserId > 0 && this.webrtc.callOverlayUserId == this.messenger.currentTab)
		{
			this.closeTopmostWindow();
		}
		else if (this.webrtc.callOverlayChatId > 0 && this.webrtc.callOverlayChatId == this.messenger.currentTab.toString().substr(4))
		{
			this.closeTopmostWindow();
		}
	}
	else
	{
		this.closeTopmostWindow();
	}
}

BX.IM.Desktop.prototype.openTopmostWindow = function(name, js, initJs, bodyClass)
{
	if (!BX.MessengerCommon.isDesktop())
		return false;

	this.closeTopmostWindow();

	console.log('openTopmostWindow init', name, js);
	clearTimeout(this.topmostWindowTimeout);
	this.topmostWindowTimeout = setTimeout(BX.delegate(function(){
		if (this.topmostWindow)
			return false;

		console.log('openTopmostWindow show', name);
		this.topmostWindow = BXDesktopSystem.ExecuteCommand('topmost.show.html', this.getHtmlPage("", js, initJs, bodyClass));
	}, this), 500);
};

BX.IM.Desktop.prototype.closeTopmostWindow = function()
{
	clearTimeout(this.topmostWindowTimeout);
	clearTimeout(this.topmostWindowCloseTimeout);
	if (!this.topmostWindow)
		return false;

	console.log('closeTopmostWindow init');
	if (this.topmostWindow && this.topmostWindow.document)
		BX.desktop.windowCommand(this.topmostWindow, "hide");

	this.topmostWindowCloseTimeout = setTimeout(BX.delegate(function(){
		if (this.topmostWindow && this.topmostWindow.document)
		{
			/*if (this.topmostWindow.document && this.topmostWindow.document.title.length > 0)
			{*/
				console.log('closeTopmostWindow close');
				BX.desktop.windowCommand(this.topmostWindow, "close");
				this.topmostWindow = null;
			/*}
			else
			{
				this.closeTopmostWindow();
			}*/
		}
	}, this), 300);
}

BX.IM.Desktop.prototype.getHtmlPage = function(content, jsContent, initImJs, bodyClass)
{
	if (!BX.MessengerCommon.isDesktop()) return;

	content = content || '';
	jsContent = jsContent || '';
	bodyClass = bodyClass || '';

	var initImConfig = typeof(initImJs) == "undefined" || typeof(initImJs) != "object"? {}: initImJs;
	initImJs = typeof(initImJs) != "undefined";
	if (this.htmlWrapperHead == null)
		this.htmlWrapperHead = document.head.outerHTML.replace(/BX\.PULL\.start\([^)]*\);/g, '');

	if (content != '' && BX.type.isDomNode(content))
		content = content.outerHTML;

	if (jsContent != '' && BX.type.isDomNode(jsContent))
		jsContent = jsContent.outerHTML;

	if (jsContent != '')
		jsContent = '<script type="text/javascript">BX.ready(function(){'+jsContent+'});</script>';

	var initJs = '';
	if (initImJs == true)
	{
		initJs = "<script type=\"text/javascript\">"+
			"BX.ready(function() {"+
				"BXIM = new BX.IM(null, {"+
					"'init': false,"+
					"'colors' : "+(this.BXIM.colors? JSON.stringify(this.BXIM.colors): "false")+","+
					"'settings' : "+JSON.stringify(this.BXIM.settings)+","+
					"'settingsView' : "+JSON.stringify(this.BXIM.settingsView)+","+
					"'updateStateInterval': '"+this.BXIM.updateStateInterval+"',"+
					"'desktop': "+BX.MessengerCommon.isPage()+","+
					"'desktopVersion': "+this.BXIM.desktopVersion+","+
					"'ppStatus': false,"+
					"'ppServerStatus': false,"+
					"'xmppStatus': "+this.BXIM.xmppStatus+","+
					"'bitrixNetwork': "+this.BXIM.bitrixNetwork+","+
					"'bitrixNetwork2': "+this.BXIM.bitrixNetwork2+","+
					"'bitrixOpenLines': "+this.BXIM.bitrixOpenLines+","+
					"'bitrix24': "+this.BXIM.bitrix24+","+
					"'bitrixIntranet': "+this.BXIM.bitrixIntranet+","+
					"'bitrixXmpp': "+this.BXIM.bitrixXmpp+","+
					"'bitrixMobile': "+this.BXIM.bitrixMobile+","+
					"'files' : "+(initImConfig.files? JSON.stringify(initImConfig.files): '{}')+","+
					"'notify' : "+(initImConfig.notify? JSON.stringify(initImConfig.notify): '{}')+","+
					"'users' : "+(initImConfig.users? JSON.stringify(initImConfig.users): '{}')+","+
					"'chat' : "+(initImConfig.chat? JSON.stringify(initImConfig.chat): '{}')+","+
					"'userChat' : "+(initImConfig.userChat? JSON.stringify(initImConfig.userChat): '{}')+","+
					"'userInChat' : "+(initImConfig.userInChat? JSON.stringify(initImConfig.userInChat): '{}')+","+
					"'hrphoto' : "+(initImConfig.hrphoto? JSON.stringify(initImConfig.hrphoto): '{}')+","+
					"'phoneCrm' : "+(initImConfig.phoneCrm? JSON.stringify(initImConfig.phoneCrm): '{}')+","+
					"'generalChatId': "+this.BXIM.messenger.generalChatId+","+
					"'canSendMessageGeneralChat': "+this.BXIM.messenger.canSendMessageGeneralChat+","+
					"'userId': "+this.BXIM.userId+","+
					"'userEmail': '"+this.BXIM.userEmail+"',"+
					"'userColor': '"+this.BXIM.userColor+"',"+
					"'userGender': '"+this.BXIM.userGender+"',"+
					"'userExtranet': "+this.BXIM.userExtranet+","+
					"'disk': {'enable': "+(this.disk? this.disk.enable: false)+", 'external': "+(this.disk? this.disk.external: false)+"},"+
					"'path' : "+JSON.stringify(this.BXIM.path)+
				"});"+
			"});"+
		"</script>";
	}
	return '<!DOCTYPE html><html>'+this.htmlWrapperHead+'<body class="im-desktop im-desktop-popup '+bodyClass+'"><div id="placeholder-messanger">'+content+'</div>'+initJs+jsContent+'</body></html>';
};

BX.IM.Desktop.prototype.onAwayAction = function (away, manual)
{
	BX.ajax({
		url: this.BXIM.pathToAjax+'?IDLE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_IDLE' : 'Y', 'IM_AJAX_CALL' : 'Y', IDLE: away? 'Y': 'N', MANUAL: manual? 'Y': 'N', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (data && data.BITRIX_SESSID)
			{
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
			}
			if (data.ERROR == 'AUTHORIZE_ERROR' && BX.MessengerCommon.isDesktop() && this.messenger.sendAjaxTry < 3)
			{
				this.messenger.sendAjaxTry++;
				BX.onCustomEvent(window, 'onImError', [data.ERROR]);
			}
			else if (data.ERROR == 'SESSION_ERROR' && this.messenger.sendAjaxTry < 2)
			{
				this.messenger.sendAjaxTry++;
				BX.onCustomEvent(window, 'onImError', [data.ERROR, data.BITRIX_SESSID]);
			}
			else
			{
				if (data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'SESSION_ERROR')
				{
					BX.onCustomEvent(window, 'onImError', [data.ERROR]);
				}
			}
		}, this)
	});
}
BX.IM.Desktop.prototype.onWakeAction = function ()
{
	BX.desktop.setIconStatus('offline');

	BX.MessengerCommon.checkInternetConnection(function()
	{
		var initDate = BXIM.desktop.initDate;
		var curDate = new Date();
		if (
			initDate.getDate()+''+initDate.getMonth()+''+initDate.getFullYear()
			== curDate.getDate()+''+curDate.getMonth()+''+curDate.getFullYear()
		)
		{
			BX.PULL.setPrivateVar('_pullTimeConfig', 0);
			BX.PULL.expireConfig();
			BX.PULL.init();
		}
		else
		{
			BX.desktop.windowReload();
		}
	},
	BX.delegate(function()
	{
		BX.desktop.login();
	}, this), 10)
}
BX.IM.Desktop.prototype.onTrayAction = function ()
{
	BX.desktop.windowCommand("show");
	var messengerCounter = this.BXIM.notify.getCounter('im_message');
	var notifyCounter = this.BXIM.notify.getCounter('im_notify');
	if (messengerCounter > 0)
	{
		if (this.BXIM.notifyOpen == true && notifyCounter > 0)
		{
			BX.desktop.changeTab('notify');
			//this.BXIM.notify.openNotify(false, true);
		}
		else
		{
			BX.desktop.changeTab('im');
			this.BXIM.messenger.openMessenger();
		}
	}
	else if (notifyCounter > 0)
	{
		BX.desktop.changeTab('notify');
		//this.BXIM.notify.openNotify(false, true);
	}
	if (this.BXIM.messenger.popupMessengerTextarea)
	{
		this.BXIM.messenger.popupMessengerTextarea.focus();
	}
};
BX.IM.Desktop.prototype.birthdayStatus = function(value)
{
	if (!BX.MessengerCommon.isDesktop()) return false;

	if (typeof(value) !='boolean')
	{
		return this.BXIM.getLocalConfig('birthdayStatus', true);
	}
	else
	{
		this.BXIM.setLocalConfig('birthdayStatus', value);
		return value;
	}
};

BX.IM.Desktop.prototype.changeTab = function(currentTab)
{
	return false;
};

BX.PopupWindowDesktop = function()
{
	this.closeByEsc = true;
	this.setClosingByEsc = function(enable) { this.closeByEsc = enable; };
	this.close = function(){
		if (BX.MessengerCommon.isDesktop())
		{
			BX.desktop.windowCommand('close');
		}
		else if (BX.MessengerCommon.isPage())
		{
			BX.MessengerWindow.closePopup();
		}
	};
	this.destroy = function(){
		if (BX.MessengerCommon.isDesktop())
		{
			BX.desktop.windowCommand('close');
		}
		else if (BX.MessengerCommon.isPage())
		{
			BX.MessengerWindow.closePopup();
		}
	};
};

/* WebRTC */
BX.IM.WebRTC = function(BXIM, params)
{
	if (this.parent)
	{
		this.parent.constructor.apply(this, arguments);
	}
	this.BXIM = BXIM;
	this.screenSharing = new BX.IM.ScreenSharing(this, params);

	this.panel = params.panel;
	this.desktop = params.desktopClass;

	this.callToPhone = false;
	this.callOverlayFullScreen = false;

	this.callToMobile = false;

	this.callAspectCheckInterval = null;
	this.callAspectHorizontal = true;
	this.callInviteTimeout = null;
	this.callNotify = null;
	this.callAllowTimeout = null;
	this.callDialogAllow = null;
	this.callOverlay = null;
	this.callOverlayMinimize = null;
	this.callOverlayChatId = 0;
	this.callOverlayUserId = 0;
	this.callSelfDisabled = false;
	this.callOverlayPhotoSelf = null;
	this.callOverlayPhotoUsers = {};
	this.callOverlayVideoUsers = {};
	this.callOverlayVideoPhotoUsers = {};
	this.callOverlayOptions = {};
	this.callOverlayPhotoCompanion = null;
	this.callOverlayPhotoMini = null;
	this.callOverlayVideoMain = null;
	this.callOverlayVideoReserve = null;
	this.callOverlayVideoSelf = null;
	this.callOverlayProgressBlock = null;
	this.callOverlayStatusBlock = null;
	this.callOverlayButtonsBlock = null;

	this.callServerEnabled = params.callServerEnabled;

	this.phoneEnabled = params.phoneEnabled;
	this.phoneCanPerformCalls = params.phoneCanPerformCalls;
	this.phoneSipAvailable = params.phoneSipAvailable;
	this.phoneDeviceActive = params.phoneDeviceActive == 'Y';
	this.phoneCanCallUserNumber = params.phoneCanCallUserNumber == 'Y';
	this.phoneCallerID = '';
	this.phoneLogin = '';
	this.phoneServer = '';
	this.phoneCheckBalance = false;
	this.phoneCallHistory = {};
	this.phoneHistory = this.BXIM.getLocalConfig('phone-history') || [];

	this.phoneSDKinit = false;
	this.phoneMicAccess = false;
	this.phoneIncoming = false;
	this.phoneCallId = '';
	this.phoneCallTime = 0;
	this.phoneCallConfig = {};
	this.phoneCallExternal = false;
	this.phoneCallDevice = 'WEBRTC';
	this.phonePortalCall = false;
	this.phoneNumber = '';
	this.phoneFullNumber = '';
	this.phoneNumberUser = '';
	this.phoneParams = {};
	this.phoneAPI = null;
	this.phoneDisconnectAfterCallFlag = true;
	this.phoneCurrentCall = null;
	this.phoneCrm = params.phoneCrm? params.phoneCrm: {};
	this.phoneMicMuted = false;
	this.phoneHolded = false;
	this.phoneRinging = 0;
	this.phoneTransferEnabled = false;
	this.phoneTransferUser = 0;
	this.phoneTransferType = 'user'; // user|phone
	this.phoneTransferPhone = '';
	this.phoneConnectedInterval = null;
	this.phoneDeviceDelayTimeout = null;
	this.phoneLines = params.phoneLines || {};
	this.phoneDefaultLineId = params.phoneDefaultLineId || false;
	this.phoneAvailableLines = params.phoneAvailableLines || [];

	this.phoneCallView = false;
	this.foldedPhoneCallView = BX.FoldedCallView.getInstance();
	this.callListId = 0;
	this.lastCallListCallParams = null;

	this.debug = false;

	this.phoneKeypad = null;
	this.popupTransferDialog = null;
	this.popupTransferDialogDestElements = null;
	this.popupTransferDialogContactListSearch = null;
	this.popupTransferDialogContactListElements = null;

	if (this.setTurnServer)
	{
		this.setTurnServer({
			'turnServer': params.turnServer || '',
			'turnServerFirefox': params.turnServerFirefox || '',
			'turnServerLogin': params.turnServerLogin || '',
			'turnServerPassword': params.turnServerPassword || ''
		});
	}

	this.readDefaults();
	this.defineButtons();
	this.restoreFoldedCallView();
	if(BX.type.isFunction(this.logDevices))
	{
		this.logDevices();
	}

	var commonElementsInit = false;
	if (this.enabled)
	{
		commonElementsInit = true;

		BX.addCustomEvent("onPullEvent-im", BX.delegate(function(command,params)
		{
			if (command == 'call')
			{
				this.log('Incoming', params.command, params.senderId, JSON.stringify(params));

				if (params.command == 'join')
				{
					for (var i in params.users)
					{
						params.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
						params.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
						params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
						params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

						this.messenger.users[i] = params.users[i];
					}

					for (var i in params.hrphoto)
						this.messenger.hrphoto[i] = params.hrphoto[i];

					if (this.callInit || this.callActive)
					{
						setTimeout(BX.delegate(function(){
							BX.ajax({
								url: this.BXIM.pathToCallAjax+'?CALL_BUSY&V='+this.BXIM.revision,
								method: 'POST',
								dataType: 'json',
								timeout: 30,
								data: {'IM_CALL' : 'Y', 'COMMAND': 'busy', 'CHAT_ID': params.chatId, 'RECIPIENT_ID' : params.senderId, 'VIDEO': params.video? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
							});
						}, this), params.callToGroup? 1000: 0);
					}
					else
					{
						if (BX.MessengerCommon.isDesktop() || !this.BXIM.desktopStatus)
						{
							this.messenger.openMessenger('chat'+params.chatId);
							this.BXIM.repeatSound('ringtone', 5000);
							this.callNotifyWait(params.chatId, params.senderId, params.video, params.callToGroup, true);
						}

						if (BX.MessengerCommon.isDesktop() && !this.BXIM.windowFocus)
						{
							var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {}};
							if (params.callToGroup)
							{
								data['chat'][params.chatId] = this.messenger.chat[params.chatId];
								data['userInChat'][params.chatId] = this.messenger.userInChat[params.chatId];
							}

							for (var i = 0; i < this.messenger.userInChat[params.chatId].length; i++)
							{
								data['users'][this.messenger.userInChat[params.chatId][i]] = this.messenger.users[this.messenger.userInChat[params.chatId][i]];
								data['hrphoto'][this.messenger.userInChat[params.chatId][i]] = this.messenger.hrphoto[this.messenger.userInChat[params.chatId][i]];
							}

							this.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.callNotifyWaitDesktop("+params.chatId+",'"+params.senderId+"', "+(params.video?1:0)+", "+(params.callToGroup?1:0)+", true);", data, 'im-desktop-call');
						}
					}
				}
				else if (params.command == 'invite' || params.command == 'invite_join')
				{
					for (var i in params.users)
					{
						params.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
						params.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
						params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
						params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

						this.messenger.users[i] = params.users[i];
					}

					for (var i in params.hrphoto)
						this.messenger.hrphoto[i] = params.hrphoto[i];

					for (var i in params.chat)
					{
						params.chat[i].date_create = new Date(params.chat[i].date_create);
						this.messenger.chat[i] = params.chat[i];
					}

					for (var i in params.userInChat)
						this.messenger.userInChat[i] = params.userInChat[i];

					if (this.callInit || this.callActive)
					{
						if (params.command == 'invite')
						{
							if (this.callChatId == params.chatId)
							{
								this.callCommand(params.chatId, 'busy_self');
								this.callOverlayClose(false);
							}
							else
							{
								setTimeout(BX.delegate(function(){
									BX.ajax({
										url: this.BXIM.pathToCallAjax+'?CALL_BUSY&V='+this.BXIM.revision,
										method: 'POST',
										dataType: 'json',
										timeout: 30,
										data: {'IM_CALL' : 'Y', 'COMMAND': 'busy', 'CHAT_ID': params.chatId, 'RECIPIENT_ID' : params.senderId, 'VIDEO': params.video? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
									});
								}, this), params.callToGroup? 1000: 0);
							}
						}
						else if (this.initiator && this.callChatId == params.chatId)
						{
							this.initiator = false;
							this.callDialog();
							BX.ajax({
								url: this.BXIM.pathToCallAjax+'?CALL_ANSWER&V='+this.BXIM.revision,
								method: 'POST',
								dataType: 'json',
								timeout: 30,
								data: {'IM_CALL' : 'Y', 'COMMAND': 'answer', 'CHAT_ID': this.callChatId, 'CALL_TO_GROUP': this.callToGroup? 'Y': 'N',  'RECIPIENT_ID' : this.callUserId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
							});
						}
					}
					else
					{
						if (BX.MessengerCommon.isDesktop() || !this.BXIM.desktopStatus)
						{
							this.BXIM.repeatSound('ringtone', 5000);
							this.callCommand(params.chatId, 'wait');
							if (BX.MessengerCommon.isPage())
							{
								BX.MessengerWindow.changeTab('im');
							}

							this.callNotifyWait(params.chatId, params.senderId, params.video, params.callToGroup);

							if (params.isMobile)
							{
								this.callToMobile = true;
								BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-mobile');
							}
						}
						if (BX.MessengerCommon.isDesktop() && !this.BXIM.isFocus('all'))
						{
							var data = {'users' : {}, 'chat' : {}, 'userInChat' : {}, 'hrphoto' : {}};
							if (params.callToGroup)
							{
								data['chat'][params.chatId] = this.messenger.chat[params.chatId];
								data['userInChat'][params.chatId] = this.messenger.userInChat[params.chatId];
							}
							for (var i = 0; i < this.messenger.userInChat[params.chatId].length; i++)
							{
								data['users'][this.messenger.userInChat[params.chatId][i]] = this.messenger.users[this.messenger.userInChat[params.chatId][i]];
								data['hrphoto'][this.messenger.userInChat[params.chatId][i]] = this.messenger.hrphoto[this.messenger.userInChat[params.chatId][i]];
							}
							this.desktop.openTopmostWindow("callNotifyWaitDesktop", "BXIM.webrtc.callNotifyWaitDesktop("+params.chatId+",'"+params.senderId+"', "+(params.video?1:0)+", "+(params.callToGroup?1:0)+");", data, 'im-desktop-call');
						}
					}
				}
				else if (this.callInit && this.callChatId == params.lastChatId && params.command == 'invite_user')
				{
					for (var i in params.users)
					{
						params.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
						params.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
						params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
						params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

						this.messenger.users[i] = params.users[i];
					}

					for (var i in params.hrphoto)
						this.messenger.hrphoto[i] = params.hrphoto[i];

					this.callChatId = params.chatId;
					this.callGroupOverlayRedraw();
				}
				else if (!this.callActive && this.callInit && this.callChatId == params.chatId && params.command == 'wait')
				{
					if (!this.callToGroup)
					{
						clearTimeout(this.callDialtoneTimeout);
						this.callDialtoneTimeout = setTimeout(BX.delegate(function(){
							this.BXIM.repeatSound('dialtone', 5000);
						}, this), 2000);
					}

					this.callWait(params.senderId);
				}
				else if (this.initiator && this.callChatId == params.chatId && params.command == 'answer')
				{
					this.callDialog();
					if (params.isMobile)
					{
						this.callToMobile = true;
						BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-mobile');
					}
				}
				else if (params.command == 'ready')
				{
					if (this.callActive && this.callStreamSelf == null)
					{
						clearTimeout(this.callAllowTimeout);
						this.callAllowTimeout = setTimeout(BX.delegate(function(){
							this.callOverlayProgress('offline');
							this.callCommand(this.callChatId, 'errorAccess');
							this.callOverlayButtons(this.buttonsOverlayClose);
							this.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS_3'));
						}, this), 60000);
					}
					this.log('Opponent '+params.senderId+' ready!');
					this.connected[params.senderId] = true;
				}
				else if (this.callActive && this.callChatId == params.chatId &&  params.command == 'errorAccess' && (!params.callToGroup || params.closeConnect))
				{
					this.callOverlayProgress('offline');
					this.callOverlayStatus(BX.message('IM_M_CALL_ST_NO_ACCESS_2'));
					this.callOverlayButtons(this.buttonsOverlayClose);
					this.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS_2'));
				}
				else if (this.callActive && this.callChatId == params.chatId  && params.command == 'reconnect')
				{
					clearTimeout(this.pcConnectTimeout[params.senderId]);
					clearTimeout(this.initPeerConnectionTimeout[params.senderId]);

					if (this.pc[params.senderId])
						this.pc[params.senderId].close();

					delete this.pc[params.senderId];
					delete this.pcStart[params.senderId];

					if (this.callStreamMain == this.callStreamUsers[params.senderId])
						this.callStreamMain = null;
					this.callStreamUsers[params.senderId] = null;

					this.initPeerConnection(params.senderId);
				}
				else if (this.callActive && this.callChatId == params.chatId  && params.command == 'signaling')
				{
					this.signalingPeerData(params.senderId, params.peer);
				}
				else if (this.callInit && this.callChatId == params.chatId  && params.command == 'waitTimeout' && (!params.callToGroup || params.closeConnect))
				{
					this.callAbort();
					this.callOverlayClose();
				}
				else if (this.callInit && this.callChatId == params.chatId  && (params.command == 'busy_self' || params.command == 'callToPhone'))
				{
					this.callAbort();
					this.callOverlayClose();
				}
				else if (this.callInit && this.callChatId == params.chatId  && params.command == 'busy' && (!params.callToGroup || params.closeConnect))
				{
					this.callOverlayProgress('offline');
					this.callOverlayButtons([
						{
							text: BX.message('IM_M_CALL_BTN_RECALL'),
							className: 'bx-messenger-call-overlay-button-recall',
							events: {
								click : BX.delegate(function() {
									this.callInvite(params.senderId, params.video);
								}, this)
							}
						},
						{
							text: BX.message('IM_M_CALL_BTN_HISTORY'),
							title: BX.message('IM_M_CALL_BTN_HISTORY_2'),
							showInMinimize: true,
							className: 'bx-messenger-call-overlay-button-history',
							events: { click : BX.delegate(function(){
								this.messenger.openHistory(this.messenger.currentTab);
							}, this) }
						},
						{
							text: BX.message('IM_M_CALL_BTN_CLOSE'),
							className: 'bx-messenger-call-overlay-button-close',
							events: {
								click : BX.delegate(function() {
									this.callOverlayClose();
								}, this)
							}
						}
					]);
					this.callAbort(BX.message('IM_M_CALL_ST_BUSY'));
				}
				else if (this.callInit && this.callChatId == params.chatId && params.command == 'decline' && (!params.callToGroup || params.closeConnect))
				{
					if (this.callInitUserId != this.BXIM.userId || this.callActive)
					{
						var callVideo = this.callVideo;
						this.callOverlayStatus(BX.message('IM_M_CALL_ST_DECLINE'));

						this.BXIM.playSound('stop');
						if (callVideo && this.callStreamSelf != null)
							this.callOverlayVideoClose();
						else
							this.callOverlayClose();
					}
					else if (this.callInitUserId == this.BXIM.userId)
					{
						this.callOverlayProgress('offline');
						this.callOverlayButtons(this.buttonsOverlayClose);
						this.callAbort(BX.message('IM_M_CALL_ST_DECLINE'));
					}
					else
					{
						this.callAbort();
					}
				}
				else if ((params.command == 'decline_self' && this.callChatId == params.chatId || params.command == 'answer_self' && !this.callActive) && !this.callSelfDisabled)
				{
					this.BXIM.stopRepeatSound('ringtone');
					this.BXIM.stopRepeatSound('dialtone');

					this.callOverlayClose(true);
				}
				else if (this.callInit && params.callToGroup && this.callChatId == params.chatId && (params.command == 'errorAccess' || params.command == 'waitTimeout' || params.command == 'busy' || params.command == 'decline'))
				{
					var userId = this.callOverlayVideoMain.getAttribute('data-userId');
					if (userId == params.senderId)
					{
						var changeVideo = false;
						for (var i in this.callStreamUsers)
						{
							if (i == params.senderId)
								continue;

							this.callChangeMainVideo(i);
							changeVideo = true;
							break;
						}
						if (!changeVideo)
						{
							this.callStreamMain = null;
							this.callOverlayProgress('wait');
							this.callOverlayStatus(BX.message(this.callToGroup? 'IM_M_CALL_ST_WAIT_ACCESS_3':'IM_M_CALL_ST_WAIT_ACCESS_2'));
							BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call-active');
							BX.removeClass(BXIM.webrtc.callOverlay, 'bx-messenger-call-overlay-call-video');
							BX.removeClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-block-hide');
						}
					}
					BX.addClass(this.callOverlayVideoUsers[params.senderId].parentNode, 'bx-messenger-call-video-hide');
					this.connected[params.senderId] = false;
					this.callOverlayVideoUsers[params.senderId].src = '';
					this.pc[params.senderId] = null;
					delete this.pc[params.senderId];
					delete this.pcStart[params.senderId];
					if (this.callStreamUsers[params.senderId] && this.callStreamUsers[params.senderId].stop)
						this.callStreamUsers[params.senderId].stop();
					this.callStreamUsers[params.senderId] = null;
					delete this.callStreamUsers[params.senderId];
				}
				else
				{
					this.log('Command "'+params.command+'" skip (current chat: '+parseInt(this.callChatId)+'; command chat: '+parseInt(params.chatId));
				}
			}

		}, this));

		if (this.callServerEnabled)
		{
			var domNode = BX.create("link", { attrs : { href : "/bitrix/js/im/css/call.css", rel : "stylesheet", type : "text/css" }});
			document.head.insertBefore(domNode, document.head.firstChild);
			var domNode = BX.create("link", { attrs : { href : "/bitrix/css/main/font-awesome.css", rel : "stylesheet", type : "text/css" }});
			document.head.insertBefore(domNode, document.head.firstChild);
			var domNode = BX.create("script", { attrs : { src : "/bitrix/js/im/call.js", type : "text/javascript" }});
			document.head.insertBefore(domNode, document.head.firstChild);
			var domNode = BX.create("script", { attrs : { src : "/bitrix/js/main/webrtc/adapter.js", type : "text/javascript" }});
			document.head.insertBefore(domNode, document.head.firstChild);
			domNode = BX.create("script", { attrs : { src : "/bitrix/js/im/webrtc/janus.js", type : "text/javascript" }});
			document.head.insertBefore(domNode, document.head.firstChild);
		}
	}
	else
	{
		if (!this.BXIM.desktopStatus)
		{
			this.initAudio(true);
			BX.addCustomEvent("onPullEvent-im", BX.delegate(function(command,params) {
				if (params.command == 'call' && params.command == 'invite')
				{
					for (var i in params.users)
					{
						params.users[i].last_activity_date = new Date(params.users[i].last_activity_date);
						params.users[i].mobile_last_date = new Date(params.users[i].mobile_last_date);
						params.users[i].idle = params.users[i].idle? new Date(params.users[i].idle): false;
						params.users[i].absent = params.users[i].absent? new Date(params.users[i].absent): false;

						this.messenger.users[i] = params.users[i];
					}

					for (var i in params.hrphoto)
						this.messenger.hrphoto[i] = params.hrphoto[i];

					this.callOverlayShow({
						toUserId : this.BXIM.userId,
						fromUserId : params.senderId,
						callToGroup : this.callToGroup,
						video : params.video,
						progress : 'offline',
						minimize : false,
						status : BX.MessengerCommon.isDesktop()? BX.message('IM_M_CALL_ST_NO_WEBRTC_3'): BX.message('IM_M_CALL_ST_NO_WEBRTC_2'),
						buttons : [
							{
								text: BX.message('IM_M_CALL_BTN_DOWNLOAD'),
								className: 'bx-messenger-call-overlay-button-download',
								events: {
									click : BX.delegate(function() {
										window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp");
										this.callOverlayClose();
									}, this)
								},
								hide: this.BXIM.platformName == ''
							},
							{
								text: BX.message('IM_M_CALL_BTN_CLOSE'),
								className: 'bx-messenger-call-overlay-button-close',
								events: {
									click : BX.delegate(function() {
										this.callOverlayClose();
									}, this)
								}
							}
						]
					});
					this.callOverlayDeleteEvents({'closeNotify': false});
				}
			}, this));
		}

	}

	commonElementsInit = true;
	/* TODO disabled because of problems with the microphone change
	if (BX.MessengerCommon.isDesktop())
	{
		this.phoneDisconnectAfterCallFlag = false;
	}*/

	BX.MessengerCommon.pullPhoneEvent();

	if (commonElementsInit)
	{
		this.initAudio();

		if (BX.browser.SupportLocalStorage())
		{
			BX.addCustomEvent(window, "onLocalStorageSet", BX.delegate(this.storageSet, this));
		}

		BX.garbage(function(){
			if (this.callInit && !this.callActive)
			{
				if (this.initiator)
				{
					this.callCommand(this.callChatId, 'decline', {'ACTIVE': this.callActive? 'Y': 'N', 'INITIATOR': this.initiator? 'Y': 'N'}, false);
					this.callAbort();
				}
				else
				{
					var calledUsers = {};
					for (var i in this.messenger.hrphoto)
						calledUsers[i] = this.messenger.users[i];

					BX.localStorage.set('mcr2', {
						'users': calledUsers,
						'hrphoto': this.messenger.hrphoto,
						'chat': this.messenger.chat,
						'userInChat': this.messenger.userInChat,
						'callChatId': this.callChatId,
						'callUserId': this.callUserId,
						'callVideo': this.callVideo,
						'callToGroup': this.callToGroup
					}, 5);
				}
			}
			if (this.callActive)
			{
				if(this.phoneCallView && (this.phoneCallExternal || this.phoneCallDevice === 'PHONE'))
				{
					if(this.phoneCallView.canBeUnloaded())
					{
						BX.localStorage.set('bxim-folded-call-card', {
							phoneCallId: this.phoneCallId,
							phoneCrm: this.phoneCrm,
							phoneCallDevice: this.phoneCallDevice,
							phoneCallExternal: this.phoneCallExternal,
							callView: this.phoneCallView.getState()
						}, 15);
					}
				}
				else
				{
					this.callCommand(this.callChatId, 'errorAccess', {}, false);
				}
			}

			this.callOverlayClose();
		}, this);
	}
};

if (BX.inheritWebrtc)
	BX.inheritWebrtc(BX.IM.WebRTC);

BX.IM.WebRTC.prototype.ready = function()
{
	return this.enabled;
}

BX.IM.WebRTC.prototype.restoreFoldedCallView = function()
{
	var self = this;
	var callProperties = BX.localStorage.get('bxim-folded-call-card');

	if(!BX.type.isPlainObject(callProperties))
		return;

	this.callActive = true;
	this.phoneCallId = callProperties.phoneCallId;
	this.phoneCrm = callProperties.phoneCrm;
	this.phoneCallDevice = callProperties.phoneCallDevice;
	this.phoneCallExternal = callProperties.phoneCallExternal;

	var callViewProperties = callProperties.callView;
	callViewProperties.BXIM = this.BXIM;
	this.phoneCallView = new BX.PhoneCallView(callProperties.callView);
	if(this.phoneCallExternal)
	{
		this.phoneCallView.setUiState(BX.PhoneCallView.UiState.externalCard);
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connected);
		this.bindPhoneViewCallbacksExternalCall(this.phoneCallView);
	}
	else
	{
		this.bindPhoneViewCallbacks(this.phoneCallView);
	}

	if(this.phoneCallExternal)
	{
		BX.localStorage.set('viExternalCard', true, 5);
		this.phoneConnectedInterval = setInterval(function()
		{
			if(self.phoneCallExternal)
			{
				BX.localStorage.set('viExternalCard', true, 5);
			}
		}, 5000);
	}

	setTimeout(function()
	{
		BX.MessengerCommon.phoneCommand('getCall', {'CALL_ID' : self.phoneCallId}, true, function(result)
		{
			if(!result.FOUND || result.FOUND !== 'Y')
			{
				self.phoneCallId = '';
				self.callActive = false;
				self.phoneCallExternal = false;
				self.callSelfDisabled = false;
				clearInterval(self.BXIM.webrtc.phoneConnectedInterval);
				BX.localStorage.set('viExternalCard', false);
				if(self.phoneCallView)
				{
					self.phoneCallView.dispose();
					self.phoneCallView = null;
				}
			}
		});

	}.bind(this), 0);
}

BX.IM.WebRTC.prototype.readDefaults = function()
{
	if(!localStorage)
		return;

	this.defaultMicrophone = localStorage.getItem('bx-im-settings-default-microphone');
	this.defaultCamera = localStorage.getItem('bx-im-settings-default-camera');
	this.defaultSpeaker = localStorage.getItem('bx-im-settings-default-speaker');
	this.enableMicAutoParameters = (localStorage.getItem('bx-im-settings-enable-mic-auto-parameters') !== 'N');
}

BX.IM.WebRTC.prototype.defineButtons = function()
{
	this.buttonsOverlayClose = [{
		text: BX.message('IM_M_CALL_BTN_CLOSE'),
		className: 'bx-messenger-call-overlay-button-close',
		events: {
			click : BX.delegate(function() {
				this.callOverlayClose();
			}, this)
		}
	}];
}

BX.IM.WebRTC.prototype.initAudio = function(onlyError)
{
	if (onlyError === true)
	{
		this.panel.appendChild(this.BXIM.audio.error = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-error.ogg", type : "audio/ogg; codecs=vorbis" }}),
			BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-error.mp3", type : "audio/mpeg" }})
		]}));

		return false;
	}

	this.panel.appendChild(this.BXIM.audio.dialtone = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-dialtone.ogg", type : "audio/ogg; codecs=vorbis" }}),
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-dialtone.mp3", type : "audio/mpeg" }})
	]}));

	this.panel.appendChild(this.BXIM.audio.ringtone = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-ringtone.ogg", type : "audio/ogg; codecs=vorbis" }}),
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-ringtone.mp3", type : "audio/mpeg" }})
	]}));

	this.panel.appendChild(this.BXIM.audio.start = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-start.ogg", type : "audio/ogg; codecs=vorbis" }}),
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-start.mp3", type : "audio/mpeg" }})
	]}));

	this.panel.appendChild(this.BXIM.audio.stop = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-stop.ogg", type : "audio/ogg; codecs=vorbis" }}),
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-stop.mp3", type : "audio/mpeg" }})
	]}));

	this.panel.appendChild(this.BXIM.audio.error = BX.create("audio", { props : { className : "bx-messenger-audio" }, children : [
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-error.ogg", type : "audio/ogg; codecs=vorbis" }}),
		BX.create("source", { attrs : { src : "/bitrix/js/im/audio/video-error.mp3", type : "audio/mpeg" }})
	]}));

	if (typeof(this.BXIM.audio.stop.play) == 'undefined')
	{
		this.BXIM.settings.enableSound = false;
	}

};

/* WebRTC UserMedia API */
BX.IM.WebRTC.prototype.startGetUserMedia = function(video, audio)
{
	clearTimeout(this.callDialtoneTimeout);
	this.BXIM.stopRepeatSound('ringtone');
	this.BXIM.stopRepeatSound('dialtone');

	var showAllowPopup = true;

	clearTimeout(this.callInviteTimeout);
	clearTimeout(this.callDialogAllowTimeout);
	if (showAllowPopup)
	{
		this.callDialogAllowTimeout = setTimeout(BX.delegate(function(){
			this.callDialogAllowShow();
		}, this), 1500);
	}

	this.parent.startGetUserMedia.apply(this, arguments);
};

BX.IM.WebRTC.prototype.onUserMediaSuccess = function(stream)
{
	clearTimeout(this.callAllowTimeout);

	var result = this.parent.onUserMediaSuccess.apply(this, arguments);
	if (!result)
		return false;

	this.callOverlayProgress('online');
	this.callOverlayStatus(BX.message(this.callToGroup? 'IM_M_CALL_ST_WAIT_ACCESS_3':'IM_M_CALL_ST_WAIT_ACCESS_2'));
	if (this.callDialogAllow)
		this.callDialogAllow.close();

	this.attachMediaStream(this.callOverlayVideoSelf, this.callStreamSelf);
	this.callOverlayVideoSelf.muted = true;

	if (this.callToGroup && this.callVideo)
	{
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call-active');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call-video');
	}
	setTimeout(BX.delegate(function(){
		if (!this.callActive)
			return false;

		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-ready');
	}, this), 500);

	this.callCommand(this.callChatId, 'ready');

	if (BX.MessengerCommon.isDesktop() && this.BXIM.init)
	{
		BX.desktop.syncPause(true);
	}
};

BX.IM.WebRTC.prototype.onUserMediaError = function(error)
{
	clearTimeout(this.callAllowTimeout);

	var result = this.parent.onUserMediaError.apply(this, arguments);
	if (!result)
		return false;

	if (this.callDialogAllow)
		this.callDialogAllow.close();

	if (this.useFallbackConstraints === false)
	{
		this.useFallbackConstraints = true;
		this.startGetUserMedia(this.lastUserMediaParams['video'], this.lastUserMediaParams['audio']);
	}
	else
	{
		this.callOverlayProgress('offline');
		this.callCommand(this.callChatId, 'errorAccess');

		if (location.protocol.indexOf('https') === -1)
		{
			this.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS_HTTPS'));
		}
		else
		{
			this.callAbort(BX.message('IM_M_CALL_ST_NO_ACCESS'));
		}

		this.callOverlayButtons(this.buttonsOverlayClose);
	}
};

/* WebRTC PeerConnection Events */
BX.IM.WebRTC.prototype.setLocalAndSend = function(userId, desc)
{
	var result = this.parent.setLocalAndSend.apply(this, arguments);
	if (!result)
		return false;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_SIGNALING&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'signaling', 'CHAT_ID': this.callChatId,  'RECIPIENT_ID' : userId, 'PEER': JSON.stringify( desc ), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});

	return true;
};

BX.IM.WebRTC.prototype.onRemoteStreamAdded = function (userId, event, mainStream)
{
	if (mainStream)
	{
		this.attachMediaStream(this.callOverlayVideoMain, this.callStreamMain);
		if (BX.MessengerCommon.isDesktop())
			BX.desktop.onCustomEvent("bxCallChangeMainVideo", [this.callOverlayVideoMain.src]);

		if (!this.BXIM.windowFocus)
			this.desktop.openCallFloatDialog();

		this.callOverlayVideoMain.setAttribute('data-userId', userId);

		this.callOverlayVideoMain.muted = false;
		this.callOverlayVideoMain.volume = 1;

		BX('bx-messenger-call-overlay-button-plus').style.display = "inline-block";
		this.callOverlayStatus(BX.message('IM_M_CALL_ST_ONLINE'));

		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-online');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call-active');
		if (this.callVideo)
			BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call-video');

		clearInterval(this.callAspectCheckInterval);
		this.callAspectCheckInterval = setInterval(BX.delegate(function(){
			if (this.callOverlayVideoMain.offsetWidth < this.callOverlayVideoMain.offsetHeight)
			{
				if (this.callAspectHorizontal)
				{
					this.callAspectHorizontal = false;
					BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-aspect-vertical');
				}
			}
			else
			{
				if (!this.callAspectHorizontal)
				{
					this.callAspectHorizontal = true;
					BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-aspect-vertical');
				}
			}
		}, this), 500);
	}
	if (this.callToGroup)
	{
		if (!mainStream)
		{
			this.attachMediaStream(this.callOverlayVideoUsers[userId], this.callStreamUsers[userId]);
			BX.removeClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-hide');
		}
		else
		{
			BX.addClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-block-hide');
		}
	}
	if (this.initiator)
		this.callCommand(this.callChatId, 'start', {'CALL_TO_GROUP': this.callToGroup? 'Y': 'N', 'RECIPIENT_ID' : userId});
};

BX.IM.WebRTC.prototype.onRemoteStreamRemoved = function(userId, event)
{
	clearInterval(this.callAspectCheckInterval);
	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-aspect-vertical');
	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-online');
};

BX.IM.WebRTC.prototype.onIceCandidate = function (userId, candidates)
{
	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_SIGNALING&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'signaling', 'CHAT_ID': this.callChatId,  'RECIPIENT_ID' : userId, 'PEER': JSON.stringify(candidates), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});
}

BX.IM.WebRTC.prototype.peerConnectionError = function(userId, event)
{
	if (this.callDialogAllow)
		this.callDialogAllow.close();

	this.callOverlayProgress('offline');
	this.callCommand(this.callChatId, 'errorAccess');
	this.callAbort(BX.message('IM_M_CALL_ST_CON_ERROR'));

	this.callOverlayButtons(this.buttonsOverlayClose);
};

BX.IM.WebRTC.prototype.peerConnectionGetStats = function()
{
	if (this.detectedBrowser != 'chrome')
		return false;

	if (this.callUserId <= 0 || !this.pc[this.callUserId] || !this.pc[this.callUserId].getStats || this.callToGroup || this.callToPhone)
		return false;

	this.pc[this.callUserId].getStats(function(e){
		console.log(e)
	})
};

BX.IM.WebRTC.prototype.peerConnectionReconnect = function (userId)
{
	var result = this.parent.peerConnectionReconnect.apply(this, arguments);
	if (!result)
		return false;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_RECONNECT&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'reconnect', 'CHAT_ID' : this.callChatId,  'RECIPIENT_ID' : userId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(){
			this.initPeerConnection(userId, true);
		}, this)
	});

	return true;
}

/* WebRTC Signaling API  */
BX.IM.WebRTC.prototype.callSupport = function(dialogId, messengerClass)
{
	messengerClass = messengerClass? messengerClass: this.messenger;
	var userCheck = true;
	if (typeof(dialogId) != 'undefined')
	{
		if (parseInt(dialogId)>0)
		{
			userCheck = messengerClass.users[dialogId] && messengerClass.users[dialogId].status != 'guest' && !messengerClass.users[dialogId].bot && !messengerClass.users[dialogId].network;
		}
		else
		{
			if (messengerClass.chat[dialogId.toString().substr(4)] && messengerClass.chat[dialogId.toString().substr(4)].type == 'open')
			{
				userCheck = false;
			}
			else
			{
				userCheck = (messengerClass.userInChat[dialogId.toString().substr(4)] && messengerClass.userInChat[dialogId.toString().substr(4)].length <= 4);
			}
		}
	}
	return this.BXIM.ppServerStatus && this.enabled && userCheck;
};

BX.IM.WebRTC.prototype.callInvite = function(userId, video, screen)
{
	if (BX.localStorage.get('viInitedCall'))
		return false;

	if (BX.MessengerCommon.isPage() && BX.MessengerWindow.currentTab != 'im')
	{
		BX.MessengerWindow.changeTab('im');
	}

	if (!this.callSupport())
	{
		if (!BX.MessengerCommon.isDesktop())
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_NO_WEBRT'), [
				this.BXIM.platformName == ''? null: new BX.PopupWindowButton({
					text : BX.message('IM_M_CALL_BTN_DOWNLOAD'),
					className : "popup-window-button-accept",
					events : { click : BX.delegate(function() { window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp"); BX.proxy_context.popupWindow.close(); }, this) }
				}),
				new BX.PopupWindowButton({
					text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
					className : "popup-window-button-decline",
					events : { click : function() { this.popupWindow.close(); } }
				})
			]);
		}
		return false;
	}

	var callToChat = false;
	if (parseInt(userId) > 0)
	{
		if (this.messenger.users[userId] && this.messenger.users[userId].status == 'guest')
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_USER_OFFLINE'));
			return false;
		}
		else if (!this.messenger.users[userId])
		{
			BX.MessengerCommon.getUserParam(userId);
		}
		userId = parseInt(userId);
	}
	else
	{
		userId = userId.toString().substr(4);
		if (!this.messenger.userInChat[userId] || this.messenger.userInChat[userId].length <= 1)
		{
			return false;
		}
		else if (!this.messenger.userInChat[userId] || this.messenger.userInChat[userId].length > 4)
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_CHAT_LARGE'));
			return false;
		}
		callToChat = true;
	}

	video = video == true;
	screen = video === true && screen === true;

	if (!this.callActive && !this.callInit && userId > 0)
	{
		this.initiator = true;
		this.callInitUserId = this.BXIM.userId;
		this.callInit = true;
		this.callActive = false;
		this.callUserId = callToChat? 0: userId;
		this.callChatId = callToChat? userId: 0;
		this.callToGroup = callToChat;
		this.callGroupUsers = callToChat? this.messenger.userInChat[userId]: [];
		this.callVideo = video;

		this.callOverlayShow({
			toUserId : userId,
			fromUserId : this.BXIM.userId,
			callToGroup : this.callToGroup,
			video : video,
			status : BX.message('IM_M_CALL_ST_CONNECT'),
			buttons : [
				{
					text: BX.message('IM_M_CALL_BTN_HANGUP'),
					className: 'bx-messenger-call-overlay-button-hangup',
					events: {
						click : BX.delegate(function() {
							this.callSelfDisabled = true;
							this.callCommand(this.callChatId, 'decline', {'ACTIVE': this.callActive? 'Y': 'N', 'INITIATOR': this.initiator? 'Y': 'N'});
							this.callAbort();
							this.callOverlayClose();
						}, this)
					}
				},
				{
					text: BX.message('IM_M_CALL_BTN_CHAT'),
					className: 'bx-messenger-call-overlay-button-chat',
					showInMaximize: true,
					events: { click : BX.delegate(this.callOverlayToggleSize, this) }
				},
				{
					title: BX.message('IM_M_CALL_BTN_MAXI'),
					className: 'bx-messenger-call-overlay-button-maxi',
					showInMinimize: true,
					events: { click : BX.delegate(this.callOverlayToggleSize, this) }
				}
				/*{
					title: BX.message('IM_M_CALL_BTN_FULL'),
					className: 'bx-messenger-call-overlay-button-full',
					events: { click : BX.delegate(this.overlayEnterFullScreen, this) },
					hide: !this.callVideo || this.desktop.ready()
				}*/
			]
		});
		this.BXIM.playSound("start");

		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?CALL_INVITE&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'IM_CALL' : 'Y', 'COMMAND': 'invite', 'CHAT_ID' : userId, 'CHAT': (callToChat? 'Y': 'N'), 'VIDEO' : video? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR == '')
				{
					this.callChatId = data.CHAT_ID;
					for (var i in data.USERS)
					{
						data.USERS[i].last_activity_date = new Date(data.USERS[i].last_activity_date);
						data.USERS[i].mobile_last_date = new Date(data.USERS[i].mobile_last_date);
						data.USERS[i].idle = data.USERS[i].idle? new Date(data.USERS[i].idle): false;
						data.USERS[i].absent = data.USERS[i].absent? new Date(data.USERS[i].absent): false;

						this.messenger.users[i] = data.USERS[i];
					}

					for (var i in data.HR_PHOTO)
						this.messenger.hrphoto[i] = data.HR_PHOTO[i];

					if (data.CALL_ENABLED && this.callToGroup)
					{
						for (var i in data.USERS_CONNECT)
						{
							this.connected[i] = true;
						}
						this.initiator = false;
						this.callInitUserId = 0;
						this.callInit = true;
						this.callActive = false;
						this.callUserId = 0;
						this.callChatId = data.CHAT_ID;
						this.callToGroup = data.CALL_TO_GROUP;
						this.callGroupUsers = this.messenger.userInChat[data.CHAT_ID];
						this.callVideo = data.CALL_VIDEO;
						this.callDialog();
						return false;
					}

					this.callOverlayUpdatePhoto();

					var callUserId = this.callToGroup? 'chat'+this.callChatId: this.callUserId;
					var callToGroup = this.callToGroup;
					var callVideo = this.callVideo;

					this.callInviteTimeout = setTimeout(BX.delegate(function(){
						this.callOverlayProgress('offline');
						this.callOverlayButtons([
							{
								text: BX.message('IM_M_CALL_BTN_RECALL'),
								className: 'bx-messenger-call-overlay-button-recall',
								events: {
									click : BX.delegate(function(e) {
										if (this.phoneCount(this.messenger.phones[callUserId]) > 0)
										{
											this.messenger.openPopupMenu(BX.proxy_context, 'callPhoneMenu', true, {userId: callUserId, video: callVideo });
										}
										else
										{
											this.callInvite(callUserId, callVideo);
										}
										BX.PreventDefault(e);
									}, this)
								},
								hide: callToGroup
							},
							{
								text: BX.message('IM_M_CALL_BTN_CLOSE'),
								className: 'bx-messenger-call-overlay-button-close',
								events: {
									click : BX.delegate(function() {
										this.callOverlayClose();
									}, this)
								}
							}
						]);

						this.callCommand(this.callChatId, 'errorOffline');
						this.callAbort(BX.message(callToGroup? 'IM_M_CALL_ST_NO_WEBRTC_1': 'IM_M_CALL_ST_NO_WEBRTC'));

					}, this), 30000);
				}
				else
				{
					this.callOverlayProgress('offline');
					this.callCommand(this.callChatId, 'errorOffline');
					this.callOverlayButtons(this.buttonsOverlayClose);
					this.callAbort(data.ERROR);
				}
			}, this),
			onfailure: BX.delegate(function() {
				this.callAbort(BX.message('IM_M_CALL_ERR'));
				this.callOverlayClose();
			}, this)
		});
	}
};

BX.IM.WebRTC.prototype.callInviteExperimental = function(dialogId, video)
{
	var callToChat = false;
	if (parseInt(dialogId) > 0)
	{
		if (this.messenger.users[dialogId] && this.messenger.users[dialogId].status == 'guest')
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_USER_OFFLINE'));
			return false;
		}
		else if (!this.messenger.users[dialogId])
		{
			BX.MessengerCommon.getUserParam(dialogId);
		}
		dialogId = parseInt(dialogId);
	}
	else
	{
		dialogId = dialogId.toString().substr(4);
		if (!this.messenger.userInChat[dialogId] || this.messenger.userInChat[dialogId].length <= 1)
		{
			return false;
		}
		callToChat = true;
	}

	video = video == true;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_EXPERIMENTAL&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'inviteExperimental', 'CHAT_ID' : dialogId, 'CHAT': (callToChat? 'Y': 'N'), 'VIDEO' : video? 'Y': 'N', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});

	return true;
};

BX.IM.WebRTC.prototype.callWait = function()
{
	if (!this.callSupport())
		return false;

	this.callOverlayStatus(BX.message(this.callToGroup? 'IM_M_CALL_ST_WAIT_2': 'IM_M_CALL_ST_WAIT'));

	clearTimeout(this.callInviteTimeout);
	this.callInviteTimeout = setTimeout(BX.delegate(function(){
		if (!this.initiator)
		{
			this.callAbort();
			this.callOverlayClose();
			return false;
		}
		this.callOverlayProgress('offline');
		var callUserId = this.callToGroup? 'chat'+this.callChatId: this.callUserId;
		var callVideo = this.callVideo;
		var callToGroup = this.callToGroup;

		this.callOverlayButtons([
			{
				text: BX.message('IM_M_CALL_BTN_RECALL'),
				className: 'bx-messenger-call-overlay-button-recall',
				events: {
					click : BX.delegate(function(e) {
						if (this.phoneCount(this.messenger.phones[callUserId]) > 0)
						{
							this.messenger.openPopupMenu(BX.proxy_context, 'callPhoneMenu', true, {userId: callUserId, video: callVideo });
						}
						else
						{
							this.callInvite(callUserId, callVideo);
						}
						BX.PreventDefault(e);
					}, this)
				},
				hide: callToGroup
			},
			{
				text: BX.message('IM_M_CALL_BTN_CLOSE'),
				className: 'bx-messenger-call-overlay-button-close',
				events: {
					click : BX.delegate(function() {
						this.callOverlayClose();
					}, this)
				}
			}
		]);

		this.callCommand(this.callChatId, 'waitTimeout');
		this.callAbort(BX.message(this.callToGroup? 'IM_M_CALL_ST_NO_ANSWER_2': 'IM_M_CALL_ST_NO_ANSWER'));

	}, this), 20000);
};

BX.IM.WebRTC.prototype.callChangeMainVideo = function(userId)
{
	var lastUserId = this.callOverlayVideoMain.getAttribute('data-userId');
	if (lastUserId == userId || !this.callStreamUsers[userId])
		return false;

	BX.addClass(this.callOverlayVideoMain, "bx-messenger-call-video-main-block-animation");

	clearTimeout(this.callChangeMainVideoTimeout);
	this.callChangeMainVideoTimeout = setTimeout(BX.delegate(function(){
		this.callOverlayVideoMain.setAttribute('data-userId', userId);
		this.attachMediaStream(this.callOverlayVideoMain, this.callStreamUsers[userId]);

		if (BX.MessengerCommon.isDesktop())
			BX.desktop.onCustomEvent("bxCallChangeMainVideo", [this.callOverlayVideoMain.src]);

		BX.addClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-block-hide');
		BX.addClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-hide');
		this.callOverlayVideoUsers[userId].parentNode.setAttribute('title', '');

		if (this.callStreamUsers[lastUserId])
		{
			this.attachMediaStream(this.callOverlayVideoUsers[lastUserId], this.callStreamUsers[lastUserId]);
			BX.removeClass(this.callOverlayVideoUsers[lastUserId].parentNode, 'bx-messenger-call-video-hide');
		}

		this.callOverlayVideoUsers[lastUserId].parentNode.setAttribute('title', BX.message('IM_CALL_MAGNIFY'));
		BX.removeClass(this.callOverlayVideoUsers[lastUserId].parentNode, 'bx-messenger-call-video-block-hide');
		BX.removeClass(this.callOverlayVideoMain, "bx-messenger-call-video-main-block-animation");

	}, this), 400);
};

BX.IM.WebRTC.prototype.callInviteUserToChat = function(users)
{
	if (this.callChatId <= 0 || this.messenger.popupChatDialogSendBlock)
		return false;

	var error = '';
	if (users.length == 0)
	{
		if (this.messenger.popupChatDialog != null)
			this.messenger.popupChatDialog.close();
		return false;
	}
	if (error != "")
	{
		this.BXIM.openConfirm(error);
		return false;
	}

	if (this.screenSharing.callInit)
	{
		this.screenSharing.callDecline();
	}

	this.messenger.popupChatDialogSendBlock = true;
	if (this.messenger.popupChatDialog != null)
		this.messenger.popupChatDialog.buttons[0].setClassName('popup-window-button-disable');

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_INVITE_USER&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'invite_user', 'USERS': JSON.stringify(users), 'CHAT_ID': this.callChatId, 'RECIPIENT_ID' : this.callUserId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data){
			this.messenger.popupChatDialogSendBlock = false;
			if (this.messenger.popupChatDialog != null)
				this.messenger.popupChatDialog.buttons[0].setClassName('popup-window-button-accept');

			if (data.ERROR == '')
			{
				this.messenger.popupChatDialogSendBlock = false;
				if (this.messenger.popupChatDialog != null)
					this.messenger.popupChatDialog.close();
			}
			else
			{
				this.BXIM.openConfirm(data.ERROR);
			}
		}, this)
	});
};

BX.IM.WebRTC.prototype.callCommand = function(chatId, command, params, async)
{
	if (!this.callSupport())
		return false;

	chatId = parseInt(chatId);
	async = async != false;
	params = typeof(params) == 'object' ? params: {};

	if (chatId > 0)
	{
		BX.ajax({
			url: this.BXIM.pathToCallAjax+'?CALL_SHARED&V='+this.BXIM.revision,
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: async,
			data: {'IM_CALL' : 'Y', 'COMMAND': command, 'CHAT_ID': chatId, 'RECIPIENT_ID' : this.callUserId, 'PARAMS' : JSON.stringify(params), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				if (this.callDialogAllow)
					this.callDialogAllow.close();
			}, this)
		});
	}
};

/* WebRTC dialogs markup */
BX.IM.WebRTC.prototype.callDialog = function()
{
	if (!this.callSupport() && this.callOverlay == null)
		return false;

	clearTimeout(this.callInviteTimeout);
	clearTimeout(this.callDialogAllowTimeout);
	if (this.callDialogAllow)
		this.callDialogAllow.close();

	this.callActive = true;
	this.callOverlayProgress('wait');
	this.callOverlayStatus(BX.message('IM_M_CALL_ST_WAIT_ACCESS'));

	this.callOverlayButtons([
		{
			text: BX.message('IM_M_CALL_BTN_HANGUP'),
			className: 'bx-messenger-call-overlay-button-hangup',
			events: {
				click : BX.delegate(function() {
					var callVideo = this.callVideo;
					this.callSelfDisabled = true;
					this.callCommand(this.callChatId, 'decline', {'ACTIVE': this.callActive? 'Y': 'N', 'INITIATOR': this.initiator? 'Y': 'N'});
					this.BXIM.playSound('stop');
					if (callVideo && this.callStreamSelf != null)
						this.callOverlayVideoClose();
					else
						this.callOverlayClose();
				}, this)
			}
		},
		{
			title: BX.message('IM_M_CHAT_TITLE'),
			className: 'bx-messenger-call-overlay-button-plus',
			events: { click : BX.delegate(function(e){
				if (this.messenger.userInChat[this.callChatId] && this.messenger.userInChat[this.callChatId].length == 4)
				{
					this.BXIM.openConfirm(BX.message('IM_CALL_GROUP_MAX_USERS'));
					return false;
				}
				this.messenger.openChatDialog({'chatId': this.callChatId, 'type': 'CALL_INVITE_USER', 'bind': BX.proxy_context, 'maxUsers': 4});
				BX.PreventDefault(e);
			}, this)},
			hide: true
		},
		{
			title: BX.message('IM_M_CALL_BTN_MIC_TITLE'),
			id: 'bx-messenger-call-overlay-button-mic',
			className: 'bx-messenger-call-overlay-button-mic '+(this.audioMuted? ' bx-messenger-call-overlay-button-mic-off': ''),
			events: {
				click : BX.delegate(function() {
					this.toggleAudio();
					var icon = BX.findChildByClassName(BX.proxy_context, "bx-messenger-call-overlay-button-mic");
					if (icon)
						BX.toggleClass(icon, 'bx-messenger-call-overlay-button-mic-off');
				}, this)
			}
		},
		{
			title: BX.message('IM_M_CALL_BTN_SCREEN_TITLE'),
			id: 'bx-messenger-call-overlay-button-screen',
			className: 'bx-messenger-call-overlay-button-screen '+(this.screenSharing.connect? ' bx-messenger-call-overlay-button-screen-off': ''),
			events: {
				click : BX.delegate(function() {
					if (!this.desktop.enableInVersion(30))
					{
						this.BXIM.openConfirm({title: BX.message('IM_M_CALL_SCREEN'), message: BX.message('IM_M_CALL_SCREEN_ERROR')});
						return false;
					}
					this.toggleScreenSharing();
				}, this)
			}
		},
		{
			title: BX.message('IM_M_CALL_BTN_HISTORY_2'),
			className: 'bx-messenger-call-overlay-button-history2',
			events: { click : BX.delegate(function(){
				this.messenger.openHistory(this.messenger.currentTab);
			}, this) }
		},
		{
			title: BX.message('IM_M_CALL_BTN_CHAT_2'),
			className: 'bx-messenger-call-overlay-button-chat2',
			showInMaximize: true,
			events: { click : BX.delegate(this.callOverlayToggleSize, this) }
		},
		{
			title: BX.message('IM_M_CALL_BTN_MAXI'),
			className: 'bx-messenger-call-overlay-button-maxi',
			showInMinimize: true,
			events: { click : BX.delegate(this.callOverlayToggleSize, this) }
		}
	]);

	if (this.messenger.popupMessenger == null)
	{
		this.messenger.openMessenger(this.callUserId);
		this.callOverlayToggleSize(false);
	}

	BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-maxi');
	BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');
	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-mini');
	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-line');
	BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call');
	if (!this.callToGroup && this.callVideo || !this.callVideo)
	{
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call-'+(this.callVideo? 'video': 'audio'));
	}

	this.startGetUserMedia(this.callVideo);
};

BX.IM.WebRTC.prototype.toggleScreenSharing = function()
{
	if (this.screenSharing.callInit && this.screenSharing.initiator)
	{
		this.screenSharing.callDecline();
	}
	else
	{
		this.screenSharing.callInvite();
	}

	return true;
}

BX.IM.WebRTC.prototype.callOverlayShow = function(params)
{
	if (!params || !(params.toUserId || params.phoneNumber) || !(params.fromUserId || params.phoneNumber) || !params.buttons)
		return false;

	if (this.callOverlay != null)
	{
		this.callOverlayClose(false, true);
	}
	this.messenger.closeMenuPopup();

	params.video = params.video != false;
	params.callToGroup = params.callToGroup == true;
	params.callToPhone = params.callToPhone == true;
	params.minimize = typeof(params.minimize) == 'undefined'? (this.messenger.popupMessenger == null): (params.minimize == true);
	params.status = params.status? params.status: "";
	params.progress = params.progress? params.progress: "connect";

	this.callOldBeforeUnload = window.onbeforeunload;
	if (!params.prepare)
	{
		window.onbeforeunload = function(){
			if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.tryConnectDelay) == 'function') // TODO change to right code in near future (e.shelenkov)
			{
				BX.PULL.tryConnectDelay();
			}
			return BX.message('IM_M_CALL_EFP')
		};
	}

	this.callOverlayMinimize = params.prepare? true: params.minimize;

	var scrollableArea = null;
	if (this.BXIM.dialogOpen)
		scrollableArea = this.messenger.popupMessengerBody;
	else if (this.BXIM.notifyOpen)
		scrollableArea = this.messenger.popupNotifyItem;

	if (scrollableArea)
	{
		if (BX.MessengerCommon.isScrollMin(scrollableArea))
		{
			setTimeout(BX.delegate(function(){
				BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
			},this), params.minimize? 0: 400);
		}
		else
		{
			BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
			scrollableArea.scrollTop = scrollableArea.scrollTop+50;
		}
	}
	else
	{
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
	}

	if (!this.callOverlayMinimize)
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');

	var callOverlayStyle = {
		width : !this.messenger.popupMessenger? '610px': (this.messenger.popupMessengerExtra.style.display == "block"? this.messenger.popupMessengerExtra.offsetWidth+1: this.messenger.popupMessengerDialog.offsetWidth+1)+'px',
		height : (this.messenger.popupMessengerFullHeight+2)+'px',
		marginLeft : this.messenger.popupContactListSize+'px'
	};

	if (this.messenger.popupMessenger == null)
	{
		callOverlayStyle['marginTop'] = '-1px';
	}

	if (params.phoneNumber)
	{
		var callOverlayBody = this.callPhoneOverlayShow(params);
	}
	else
	{
		var callOverlayBody = params.callToGroup? this.callGroupOverlayShow(params): this.callUserOverlayShow(params);
	}

	this.callOverlay =  BX.create("div", { props : { className : 'bx-messenger-call-overlay '+(params.callToGroup? ' bx-messenger-call-overlay-group ':'')+(this.callOverlayMinimize? 'bx-messenger-call-overlay-mini': 'bx-messenger-call-overlay-maxi')}, style : callOverlayStyle, children: [
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-lvl-1'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-lvl-2'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-video-main'}, children: [
					BX.create("div", { props : { className : 'bx-messenger-call-video-main-wrap'}, children: [
						BX.create("div", { props : { className : 'bx-messenger-call-video-main-watermark'}, children: [
							BX.create("img", { props : { className : 'bx-messenger-call-video-main-watermark-img'},  attrs : {src : '/bitrix/js/im/images/watermark_'+(this.BXIM.language == 'ru'? 'ru': 'en')+'.png'}})
						]}),
						BX.create("div", { props : { className : 'bx-messenger-call-video-main-cell'}, children: [
							BX.create("div", { props : { className : 'bx-messenger-call-video-main-bg'}, children: [
								this.callOverlayVideoMain = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-call-video-main-block'}}),
								this.callOverlayVideoReserve = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-hide'}})
							]})
						]})
					]})
				]})
			]})
		]}),
		this.callOverlayBody = BX.create("div", { props : { className : 'bx-messenger-call-overlay-body'}, children: callOverlayBody})
	]});
	if (params.prepare)
	{
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-float');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-show');
	}
	else if (this.messenger.popupMessenger != null)
	{
		this.messenger.setClosingByEsc(false);
		BX.addClass(BX('bx-messenger-popup-messenger'), 'bx-messenger-popup-messenger-dont-close');
		this.messenger.popupMessengerContent.insertBefore(this.callOverlay, this.messenger.popupMessengerContent.firstChild);
	}
	else if (this.callNotify != null)
	{
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-float');
		this.callNotify.setContent(this.callOverlay);
	}
	else
	{
		this.callNotify = new BX.PopupWindow('bx-messenger-call-notify', null, {
			//parentPopup: this.popupMessenger,
			lightShadow : true,
			zIndex: 200,
			events : {
				onPopupClose : function() { this.destroy(); },
				onPopupDestroy : BX.delegate(function() {
					BX.unbind(window, "scroll", this.popupCallNotifyEvent);
					this.callNotify = null;
				}, this)},
			content : this.callOverlay
		});
		this.callNotify.show();

		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-float');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-show');
		BX.addClass(this.callNotify.popupContainer.children[0], 'bx-messenger-popup-window-transparent');
		setTimeout(BX.delegate(function(){
			if (this.callNotify)
			{
				this.callNotify.adjustPosition();
			}
		}, this), 500);
		BX.bind(window, "scroll", this.popupCallNotifyEvent = BX.proxy(function(){ this.callNotify.adjustPosition();}, this));
	}
	setTimeout(BX.delegate(function(){
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-show');
	}, this), 100);

	this.callOverlayStatus(params.status);
	this.callOverlayButtons(params.buttons);
	this.callOverlayProgress(params.progress);

	return true;
};

BX.IM.WebRTC.prototype.callGroupOverlayShow = function(params)
{
	this.callOverlayOptions = params;

	var callIncoming = params.fromUserId != this.BXIM.userId;
	var callChatId = params.fromUserId != this.BXIM.userId? params.fromUserId: params.toUserId;

	var callTitle = this.callOverlayTitle();

	this.callOverlayChatId = callChatId;

	var callOverlayPhotoUsers = [];
	var callOverlayVideoUsers = [];
	for (var i = 0; i < this.messenger.userInChat[callChatId].length; i++)
	{
		var userId = this.messenger.userInChat[callChatId][i];
		var userAvatarData = BX.MessengerCommon.getHrPhoto(userId, this.messenger.users[userId].color);
		callOverlayPhotoUsers.push(BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-left'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-block'}, children: [
				this.callOverlayPhotoUsers[userId] = BX.create("img", { props : { className : 'bx-messenger-call-overlay-photo-img'}, attrs : { 'data-userId': userId, src : userAvatarData.src, style: (userAvatarData.color? 'background-color: '+userAvatarData.color: '')}})
			]})
		]}));

		if (userId == this.BXIM.userId)
			continue;

		var userAvatarData = BX.MessengerCommon.getHrPhoto(userId, this.messenger.users[userId].color);
		callOverlayVideoUsers.push(BX.create("div", { props : { className : 'bx-messenger-call-video-mini bx-messenger-call-video-hide'}, attrs: {'data-userId': userId}, events: {click: BX.delegate(function(){ this.callChangeMainVideo(BX.proxy_context.getAttribute('data-userId')); }, this)}, children: [
			this.callOverlayVideoUsers[userId] = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-call-video-mini-block'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-video-mini-photo'}, children: [
				this.callOverlayVideoPhotoUsers[userId] = BX.create("img", { props : { className : 'bx-messenger-call-video-mini-photo-img'}, attrs : { src : userAvatarData.src, style: (userAvatarData.color? 'background-color: '+userAvatarData.color: '')}})
			]})
		]}));
	}

	var userAvatarData = BX.MessengerCommon.getHrPhoto(this.BXIM.userId, this.messenger.users[this.BXIM.userId].color);
	return [
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi'}, attrs : { title: BX.message('IM_M_CALL_BTN_RETURN')}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-video-users'}, children: callOverlayVideoUsers}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-title'}, children: [
			this.callOverlayTitleBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-title-block'}, html: callTitle})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo'}, children: callOverlayPhotoUsers}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-progress-group'}, children: [
			this.callOverlayProgressBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-progress'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-status'}, children: [
			this.callOverlayStatusBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-status-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-video-mini'}, children: [
			this.callOverlayVideoSelf = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-call-video-mini-block'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-video-mini-photo'}, children: [
				this.callOverlayPhotoMini = BX.create("img", { props : { className : 'bx-messenger-call-video-mini-photo-img'}, attrs : { src : userAvatarData.src, style: (userAvatarData.color? 'background-color: '+userAvatarData.color: '')}})
			]})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons'}, children: [
			this.callOverlayButtonsBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons-block'}})
		]})
	];
};

BX.IM.WebRTC.prototype.callUserOverlayShow = function(params)
{
	this.callOverlayOptions = params;

	var callIncoming = params.toUserId == this.BXIM.userId;
	var callUserId = callIncoming? params.fromUserId: params.toUserId;

	var callTitle = this.callOverlayTitle();

	this.callOverlayUserId = callUserId;

	var userAvatarDataCall = BX.MessengerCommon.getHrPhoto(callUserId, this.messenger.users[callUserId].color);
	var userAvatarDataSelf = BX.MessengerCommon.getHrPhoto(this.BXIM.userId, this.messenger.users[this.BXIM.userId].color);

	return [
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi'}, attrs : { title: BX.message('IM_M_CALL_BTN_RETURN')}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-title'}, children: [
			this.callOverlayTitleBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-title-block'}, html: callTitle})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-left'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-block'}, children: [
					this.callOverlayPhotoCompanion = BX.create("img", { props : { className : 'bx-messenger-call-overlay-photo-img'}, attrs : { 'data-userId': callUserId, src : userAvatarDataCall.src, style: (userAvatarDataCall.color? 'background-color: '+userAvatarDataCall.color: '')}})
				]})
			]}),
			this.callOverlayProgressBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-progress'+(callIncoming?'': ' bx-messenger-call-overlay-photo-progress-incoming')}}),
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-right'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-block'}, children: [
					this.callOverlayPhotoSelf = BX.create("img", { props : { className : 'bx-messenger-call-overlay-photo-img'}, attrs : { 'data-userId': this.BXIM.userId, src : userAvatarDataSelf.src, style: (userAvatarDataSelf.color? 'background-color: '+userAvatarDataSelf.color: '')}})
				]})
			]})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-status'}, children: [
			this.callOverlayStatusBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-status-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-video-mini'}, children: [
			this.callOverlayVideoSelf = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-call-video-mini-block'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-video-mini-photo'}, children: [
				this.callOverlayPhotoMini = BX.create("img", { props : { className : 'bx-messenger-call-video-mini-photo-img'}, attrs : { src : userAvatarDataSelf.src, style: (userAvatarDataSelf.color? 'background-color: '+userAvatarDataSelf.color: '')}})
			]})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons'}, children: [
			this.callOverlayButtonsBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons-block'}})
		]})
	];
};


BX.IM.WebRTC.prototype.callPhoneOverlayShow = function(params)
{
	this.callOverlayOptions = params;

	var callIncoming = params.toUserId == this.BXIM.userId;
	var callUserId = callIncoming? params.fromUserId: params.toUserId;

	this.callToPhone = true;
	var callTitle = '';
	if (params.phoneNumber == 'hidden')
	{
		callTitle = BX.message('IM_PHONE_HIDDEN_NUMBER');
	}
	else
	{
		if (params.callTitle)
		{
			callTitle = params.callTitle.toString();
		}
		else
		{
			callTitle = params.phoneNumber.toString();
		}

		if (callTitle.substr(0,1) == '8' || callTitle.substr(0,1) == '+')
		{
		}
		else if (!isNaN(parseInt(callTitle)) && callTitle.length >= 10)
		{
			callTitle = '+'+callTitle;
		}
	}

	if (this.phoneTransferEnabled)
	{
		callTitle = BX.message('IM_PHONE_CALL_TRANSFER').replace('#PHONE#', callTitle);
	}
	else if(params.isCallback)
	{
		callTitle = BX.message('IM_PHONE_CALLBACK_TO').replace('#PHONE#', callTitle);
	}
	else
	{
		callTitle = BX.message(callIncoming? 'IM_PHONE_CALL_VOICE_FROM': 'IM_PHONE_CALL_VOICE_TO').replace('#PHONE#', callTitle);
	}

	var companyPhoneTitle = !params.isCallback && callIncoming && params.companyPhoneNumber? '<span class="bx-messenger-call-overlay-title-company-phone">'+BX.message('IM_PHONE_CALL_TO_PHONE').replace('#PHONE#', params.companyPhoneNumber)+'</span>': '';
	this.callOverlayUserId = callUserId;

	BX.MessengerCommon.getUserParam(this.messenger.currentTab);
	BX.MessengerCommon.getUserParam(this.BXIM.userId);

	this.messenger.openChatFlag = this.messenger.currentTab.toString().substr(0,4) == 'chat';

	var userAvatarDataPhone = BX.MessengerCommon.getHrPhoto('phone', this.messenger.openChatFlag? this.messenger.chat[this.messenger.currentTab.toString().substr(4)].color: this.messenger.users[this.messenger.currentTab].color);
	var userAvatarDataSelf = BX.MessengerCommon.getHrPhoto(this.BXIM.userId, this.messenger.users[this.BXIM.userId].color);

	return [
		this.callOverlayMeterGrade = BX.create("div", { attrs: {title: BX.message('IM_PHONE_GRADE')+' '+BX.message('IM_PHONE_GRADE_4')},  props : { className : 'bx-messenger-call-overlay-meter bx-messenger-call-overlay-meter-grade-5'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-meter-grade'}}),
			this.callOverlayMeterPercent = BX.create("div", {props : { className : 'bx-messenger-call-overlay-meter-percent'}, html: 100})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi'}, attrs : { title: BX.message('IM_M_CALL_BTN_RETURN')}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-line-maxi-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-title'}, children: [
			this.callOverlayTitleBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-title-block'}, html: callTitle+companyPhoneTitle})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-left'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-block'}, children: [
					this.callOverlayPhotoCompanion = BX.create("img", { props : { className : 'bx-messenger-call-overlay-photo-img'}, attrs : { 'data-userId': 'phone', src : userAvatarDataPhone.src, style: (userAvatarDataPhone.color? 'background-color: '+userAvatarDataPhone.color: '')}})
				]})
			]}),
			this.callOverlayProgressBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-progress'+(callIncoming?'': ' bx-messenger-call-overlay-photo-progress-incoming')}}),
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-right'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-overlay-photo-block'}, children: [
					this.callOverlayPhotoSelf = BX.create("img", { props : { className : 'bx-messenger-call-overlay-photo-img'}, attrs : { 'data-userId': this.BXIM.userId, src : userAvatarDataSelf.src, style: (userAvatarDataSelf.color? 'background-color: '+userAvatarDataSelf.color: '')}})
				]})
			]})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-crm-block'}, children: [
			this.callOverlayCrmBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-crm-block-wrap'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-status'}, children: [
			this.callOverlayStatusBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-status-block'}})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-video-mini'}, children: [
			this.callOverlayVideoSelf = BX.create("video", { attrs : { autoplay : true }, props : { className : 'bx-messenger-call-video-mini-block'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-video-mini-photo'}, children: [
				this.callOverlayPhotoMini = BX.create("img", { props : { className : 'bx-messenger-call-video-mini-photo-img'}, attrs : { src : userAvatarDataSelf.src, style: (userAvatarDataSelf.color? 'background-color: '+userAvatarDataSelf.color: '')}})
			]})
		]}),
		BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons'}, children: [
			this.callOverlayButtonsBlock = BX.create("div", { props : { className : 'bx-messenger-call-overlay-buttons-block'}})
		]})
	];
};

BX.IM.WebRTC.prototype.callPhoneOverlayMeter = function(percent)
{
	if (!this.phoneCurrentCall || this.phoneCurrentCall.state() != "CONNECTED")
		return false;

	var grade = 5;
	if (100 == percent)
		grade = 5;
	else if (percent >= 99)
		grade = 4;
	else if (percent >= 97 )
		grade = 3;
	else if (percent >= 95)
		grade = 2;
	else
		grade = 1;

	this.phoneCallView.setQuality(grade);
	return grade;
}

BX.IM.WebRTC.prototype.callGroupOverlayRedraw = function()
{
	this.callToGroup = true;
	this.callGroupUsers = this.messenger.userInChat[this.callChatId];
	this.callOverlayUserId = 0;
	this.callOverlayChatId = this.callChatId;
	this.callOverlayBody.innerHTML = '';
	this.callOverlayOptions['callToGroup'] = this.callToGroup;
	this.callOverlayOptions['fromUserId'] = this.callChatId;
	BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-group');
	BX.adjust(this.callOverlayBody, {children: this.callGroupOverlayShow(this.callOverlayOptions)});
	this.callOverlayStatus(this.callOverlayOptions.status);
	this.callOverlayButtons(this.callOverlayOptions.buttons);
	this.callOverlayProgress(this.callOverlayOptions.progress);
	BX('bx-messenger-call-overlay-button-plus').style.display = "inline-block";

	this.attachMediaStream(this.callOverlayVideoSelf, this.callStreamSelf);
	this.callOverlayVideoSelf.muted = true;

	if (this.messenger.currentTab != 'chat'+this.callChatId)
	{
		this.messenger.openMessenger('chat'+this.callChatId);
		this.callOverlayToggleSize(false)
	}

	var userId = this.callOverlayVideoMain.getAttribute('data-userId');
	for (var i in this.callStreamUsers)
	{
		if (!this.callStreamUsers[i] && userId == i)
			continue;

		this.attachMediaStream(this.callOverlayVideoUsers[i], this.callStreamUsers[i]);
		BX.removeClass(this.callOverlayVideoUsers[i].parentNode, 'bx-messenger-call-video-hide');
	}
	BX.addClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-block-hide');
	BX.addClass(this.callOverlayVideoUsers[userId].parentNode, 'bx-messenger-call-video-hide');
	this.callOverlayVideoUsers[userId].parentNode.setAttribute('title', '');

	return true;
};

BX.IM.WebRTC.prototype.overlayEnterFullScreen = function()
{
	if (this.callOverlayFullScreen)
	{
		BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen');
		if (document.cancelFullScreen)
			document.cancelFullScreen();
		else if (document.mozCancelFullScreen)
			document.mozCancelFullScreen();
		else if (document.webkitCancelFullScreen)
			document.webkitCancelFullScreen();
	}
	else
	{
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen');
		if (this.detectedBrowser == 'chrome')
		{
			BX.bind(window, "webkitfullscreenchange", this.callOverlayFullScreenBind = BX.proxy(this.overlayEventFullScreen, this));
			this.messenger.popupMessengerContent.webkitRequestFullScreen(this.messenger.popupMessengerContent.ALLOW_KEYBOARD_INPUT);
		}
		else if (this.detectedBrowser == 'firefox')
		{
			BX.bind(window, "mozfullscreenchange", this.callOverlayFullScreenBind = BX.proxy(this.overlayEventFullScreen, this));
			this.messenger.popupMessengerContent.mozRequestFullScreen(this.messenger.popupMessengerContent.ALLOW_KEYBOARD_INPUT);
		}
	}
};

BX.IM.WebRTC.prototype.overlayEventFullScreen = function()
{
	if (this.callOverlayFullScreen)
	{
		if (this.detectedBrowser == 'chrome')
			BX.unbind(window, "webkitfullscreenchange", this.callOverlayFullScreenBind);
		else if (this.detectedBrowser == 'firefox')
			BX.unbind(window, "mozfullscreenchange", this.callOverlayFullScreenBind);

		BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen');
		if (BX.browser.IsChrome())
		{
			BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
			setTimeout(BX.delegate(function(){
				BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen-chrome-hack');
			}, this), 100);
		}
		this.callOverlayFullScreen = false;
		this.messenger.resizeMainWindow();
	}
	else
	{
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen');
		this.callOverlayFullScreen = true;
		this.messenger.resizeMainWindow();
	}
	this.messenger.popupMessengerBody.scrollTop = this.messenger.popupMessengerBody.scrollHeight - this.messenger.popupMessengerBody.offsetHeight;
};


BX.IM.WebRTC.prototype.callOverlayToggleSize = function(minimize)
{
	if (this.callOverlay == null)
		return false;

	if (!this.ready())
	{
		this.callOverlayClose(true);
		return false;
	}

	var resizeToMax = typeof(minimize) == 'boolean'? !minimize: this.callOverlayMinimize;

	var minimizeToLine = false;
	if (this.messenger.popupMessenger != null && !this.BXIM.dialogOpen)
		minimizeToLine = true;
	else if (this.messenger.popupMessenger != null && this.callOverlayUserId > 0 && this.callOverlayUserId != this.messenger.currentTab)
		minimizeToLine = true;
	else if (this.messenger.popupMessenger != null && this.callOverlayChatId > 0 && this.callOverlayChatId != this.messenger.currentTab.toString().substr(4))
		minimizeToLine = true;
	else if (this.messenger.popupMessenger != null && this.callOverlayUserId == 0 && this.callOverlayChatId == 0 && this.phoneNumber)
		minimizeToLine = true;

	if (resizeToMax && this.callActive)
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-call');
	else
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call');

	BX.unbindAll(this.callOverlay);
	if (resizeToMax)
	{
		this.callOverlayMinimize = false;

		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-maxi');
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-line');
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-mini');
	}
	else
	{
		this.callOverlayMinimize = true;

		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-mini');
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-maxi');
		BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');

		if (minimizeToLine)
		{
			BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-line');

			setTimeout(BX.delegate(function(){
				BX.bind(this.callOverlay, 'click', BX.delegate(function() {
					if (this.BXIM.dialogOpen)
					{
						if (this.callOverlayUserId > 0)
						{
							this.messenger.openChatFlag = false;
							BX.MessengerCommon.openDialog(this.callOverlayUserId, false, false);
						}
						else
						{
							this.messenger.openChatFlag = true;
							BX.MessengerCommon.openDialog('chat'+this.callOverlayChatId, false, false);
						}
					}
					else
					{
						if (this.callOverlayUserId > 0)
						{
							this.messenger.openChatFlag = false;
							this.messenger.currentTab = this.callOverlayUserId;
						}
						else
						{
							this.messenger.openChatFlag = true;
							this.messenger.currentTab = 'chat'+this.callOverlayChatId;
						}
						this.messenger.extraClose(true, false);
					}
					this.callOverlayToggleSize(false);
				}, this));
			}, this), 200);
		}
		else
		{
			BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-line');
		}

		if (this.BXIM.isFocus())
			BX.MessengerCommon.readMessage(this.messenger.currentTab);
		if (this.BXIM.isFocus() && this.notify.notifyUpdateCount > 0)
			this.notify.viewNotifyAll();
	}

	if (this.callOverlayUserId > 0 && this.callOverlayUserId == this.messenger.currentTab)
	{
		this.desktop.closeTopmostWindow();
	}
	else if (this.callOverlayChatId > 0 && this.callOverlayChatId == this.messenger.currentTab.toString().substr(4))
	{
		this.desktop.closeTopmostWindow();
	}
	else
	{
		this.desktop.openCallFloatDialog();
	}

	if (this.callDialogAllow != null)
	{
		if (this.callDialogAllow)
			this.callDialogAllow.close();

		setTimeout(BX.delegate(function(){
			this.callDialogAllowShow();
		}, this), 1500);
	}

	if (this.popupTransferDialog)
		this.popupTransferDialog.close();
};

BX.IM.WebRTC.prototype.callOverlayClose = function(animation, onlyMarkup)
{
	if (this.callOverlay == null)
		return false;

	this.audioMuted = true;
	this.toggleAudio(false);

	onlyMarkup = onlyMarkup == true;

	if (!onlyMarkup && this.callOverlayFullScreen)
	{
		if (this.detectedBrowser == 'firefox')
		{
			BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-fullscreen');
			BX.remove(this.messenger.popupMessengerContent);
			BX.hide(this.messenger.popupMessenger.popupContainer);
			setTimeout(BX.delegate(function(){
				this.messenger.popupMessenger.destroy();
				this.messenger.openMessenger(this.messenger.currentTab);
			}, this), 200);
		}
		else
		{
			this.overlayEnterFullScreen();
		}
	}

	if (this.messenger.popupMessenger != null)
	{
		var scrollableArea = null;
		if (this.BXIM.dialogOpen)
			scrollableArea = this.messenger.popupMessengerBody;
		else if (this.BXIM.notifyOpen)
			scrollableArea = this.messenger.popupNotifyItem;

		if (scrollableArea)
		{
			if (BX.MessengerCommon.isScrollMax(scrollableArea))
			{
				BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
			}
			else
			{
				BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
				scrollableArea.scrollTop = scrollableArea.scrollTop-50;
			}
		}
		else
		{
			BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-call');
		}
		BX.removeClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');
	}
	this.messenger.closeMenuPopup();

	animation = animation != false;
	if (animation)
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-hide');

	if (animation)
	{
		setTimeout(BX.delegate(function(){
			BX.remove(this.callOverlay);
			this.callOverlay = null;
			this.callOverlayButtonsBlock = null;
			this.callOverlayTitleBlock = null;
			this.callOverlayMeter = null;
			this.callOverlayStatusBlock = null;
			this.callOverlayProgressBlock = null;
			this.callOverlayMinimize = null;
			this.callOverlayChatId = 0;
			this.callOverlayUserId = 0;
			this.callOverlayPhotoSelf = null;
			this.callOverlayPhotoUsers = {};
			this.callOverlayVideoUsers = {};
			this.callOverlayVideoPhotoUsers = {};
			this.callOverlayOptions = {};
			this.callOverlayPhotoCompanion = null;
			this.callSelfDisabled = false;
			if (this.BXIM.isFocus())
				BX.MessengerCommon.readMessage(this.messenger.currentTab);
		}, this), 300);
	}
	else
	{
		BX.remove(this.callOverlay);
		this.callOverlay = null;
		this.callOverlayButtonsBlock = null;
		this.callOverlayStatusBlock = null;
		this.callOverlayProgressBlock = null;
		this.callOverlayMinimize = null;
		this.callOverlayChatId = 0;
		this.callOverlayUserId = 0;
		this.callOverlayPhotoSelf = null;
		this.callOverlayPhotoUsers = {};
		this.callOverlayVideoUsers = {};
		this.callOverlayVideoPhotoUsers = {};
		this.callOverlayOptions = {};
		this.callOverlayPhotoCompanion = null;
		this.callSelfDisabled = false;
		if (this.BXIM.isFocus())
			BX.MessengerCommon.readMessage(this.messenger.currentTab);
	}

	if (onlyMarkup)
	{
		window.onbeforeunload = this.callOldBeforeUnload;
		this.BXIM.stopRepeatSound('ringtone');
		this.BXIM.stopRepeatSound('dialtone');
	}
	else
	{
		this.callOverlayDeleteEvents();
	}

	this.desktop.closeTopmostWindow();
};

BX.IM.WebRTC.prototype.callOverlayVideoClose = function()
{
	this.audioMuted = true;
	this.toggleAudio(false);

	BX.style(this.callOverlayVideoMain, 'height', this.callOverlayVideoMain.parentNode.offsetHeight+'px');
	BX.addClass(this.callOverlayVideoMain.parentNode, 'bx-messenger-call-video-main-bg-start');

	setTimeout(BX.delegate(function(){
		this.callOverlayClose();
	}, this), 1700);
};

BX.IM.WebRTC.prototype.callAbort = function(reason)
{
	this.callOverlayDeleteEvents();

	if (reason && this.phoneCallView)
		this.phoneCallView.setStatusText(reason);
};

BX.IM.WebRTC.prototype.callOverlayDeleteEvents = function(params)
{
	params = params || {};

	this.desktop.closeTopmostWindow();

	window.onbeforeunload = this.callOldBeforeUnload;

	var closeNotify = params.closeNotify !== false;
	if (closeNotify && this.callNotify)
		this.callNotify.destroy();

	var callId = null;
	if (this.phoneCallId)
	{
		callId = this.phoneCallId;
	}
	else if (this.callToGroup)
	{
		callId = 'chat'+this.callChatId;
	}
	else
	{
		callId = 'user'+this.callUserId;
	}
	BX.onCustomEvent(window, 'onImCallEnd', {'CALL_ID': callId});

	clearInterval(this.callAspectCheckInterval);

	if (BX.MessengerCommon.isDesktop() && this.BXIM.init)
	{
		BX.desktop.syncPause(false);
	}

	this.deleteEvents();

	this.callToMobile = false;
	this.callToPhone = false;

	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call-audio');
	BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call-video');

	if (this.messenger.popupMessenger)
	{
		this.messenger.popupMessenger.setClosingByEsc(true);
		BX.removeClass(BX('bx-messenger-popup-messenger'), 'bx-messenger-popup-messenger-dont-close');
		this.messenger.dialogStatusRedraw();
	}

	this.phoneCallFinish();

	clearTimeout(this.callDialtoneTimeout);
	this.BXIM.stopRepeatSound('ringtone');
	this.BXIM.stopRepeatSound('dialtone');

	clearTimeout(this.callInviteTimeout);
	clearTimeout(this.callDialogAllowTimeout);
	if (this.callDialogAllow)
		this.callDialogAllow.close();
}

BX.IM.WebRTC.prototype.callOverlayProgress = function(progress)
{
	if (this.phoneCallView)
		this.phoneCallView.setProgress(progress);

	if (this.callOverlay == null)
		return false;

	if (progress != this.callOverlayOptions.progress)
	{
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-status-'+progress);
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-status-'+this.callOverlayOptions.progress);
	}

	this.callOverlayOptions.progress = progress;
	this.callOverlayProgressBlock.innerHTML = '';

	if (progress == 'connect')
	{
		this.callOverlayProgressBlock.appendChild(
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress'}, children: [
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-1'}}),
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-2'}})
			]})
		);
	}
	else if (progress == 'online')
	{
		this.callOverlayProgressBlock.appendChild(
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress bx-messenger-call-overlay-progress-online'}, children: [
				BX.create("img", { props : { className : 'bx-messenger-call-overlay-progress-status bx-messenger-call-overlay-progress-status-anim-3'}})
			]})
		);
	}
	else if (progress == 'wait' || progress == 'offline' || progress == 'error')
	{
		if (progress == 'offline')
		{
			BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-online');
			BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call');
			BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-call-active');
			this.BXIM.playSound('error');
		}
		else if (progress == 'error')
		{
			progress = 'offline';
		}
		this.callOverlayProgressBlock.appendChild(
			BX.create("div", { props : { className : 'bx-messenger-call-overlay-progress bx-messenger-call-overlay-progress-'+progress}})
		);
	}
	else
	{
		this.callOverlayOptions.progress = '';
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-status-'+progress);
		return false;
	}
};

BX.IM.WebRTC.prototype.callOverlayStatus = function(status)
{
	if (!BX.type.isNotEmptyString(status) || !this.phoneCallView)
		return false;

	this.phoneCallView.setStatusText(status.toString());
};

BX.IM.WebRTC.prototype.callOverlayTitle = function()
{
	var callTitle = '';
	var callIncoming = this.callInitUserId != this.BXIM.userId;
	if (this.callToPhone)
	{
		callTitle = this.callOverlayTitleBlock.innerHTML;
	}
	else if (this.callToGroup)
	{
		callTitle = this.messenger.chat[this.callChatId].name;
		if (callTitle.length > 85)
			callTitle = callTitle.substr(0,85)+'...';

		callTitle = BX.message('IM_CALL_GROUP_'+(this.callVideo? 'VIDEO':'VOICE')+(callIncoming? '_FROM': '_TO')).replace('#CHAT#', callTitle);
	}
	else
	{
		callTitle = BX.message('IM_M_CALL_'+(this.callVideo? 'VIDEO':'VOICE')+(callIncoming? '_FROM': '_TO')).replace('#USER#', this.messenger.users[this.callUserId].name);
	}

	return callTitle;
}

BX.IM.WebRTC.prototype.setCallOverlayTitle = function(title)
{
	if(this.phoneCallView)
	{
		this.phoneCallView.setTitle(title);
	}
}

BX.IM.WebRTC.prototype.callOverlayUpdatePhoto = function()
{
	this.callOverlayTitleBlock.innerHTML = this.callOverlayTitle();

	for (var i in this.callOverlayPhotoUsers)
	{
		if (i == 'phone')
		{
			this.callOverlayPhotoUsers[i].src = '/bitrix/js/im/images/hidef-phone-v3.png';
		}
		else if (this.messenger.hrphoto[i])
		{
			this.callOverlayPhotoUsers[i].src = this.messenger.hrphoto[i];
			if (this.messenger.hrphoto[i] != '/bitrix/js/im/images/hidef-avatar-v3.png')
			{
				this.callOverlayPhotoUsers[i].type = '';
			}
		}
		else if (this.messenger.users[i].avatar == this.BXIM.pathToBlankImage)
		{
			this.callOverlayPhotoUsers[i].src = '/bitrix/js/im/images/hidef-avatar-v3.png';
		}
		else
		{
			this.callOverlayPhotoUsers[i].src = this.messenger.users[i].avatar;
			this.callOverlayPhotoUsers[i].type = '';
		}
	}
	for (var i in this.callOverlayVideoPhotoUsers)
	{
		if (i == 'phone')
		{
			this.callOverlayVideoPhotoUsers[i].src = '/bitrix/js/im/images/hidef-phone-v3.png';
		}
		else if (this.messenger.hrphoto[i])
		{
			this.callOverlayVideoPhotoUsers[i].src = this.messenger.hrphoto[i];
			if (this.messenger.hrphoto[i] != '/bitrix/js/im/images/hidef-avatar-v3.png')
			{
				this.callOverlayVideoPhotoUsers[i].type = '';
			}
		}
		else if (this.messenger.users[i].avatar == this.BXIM.pathToBlankImage)
		{
			this.callOverlayVideoPhotoUsers[i].src = '/bitrix/js/im/images/hidef-avatar-v3.png';
		}
		else
		{
			this.callOverlayVideoPhotoUsers[i].src = this.messenger.users[i].avatar;
			this.callOverlayVideoPhotoUsers[i].type = 'background-color: '+colorId;
		}
	}
	if (this.callOverlayPhotoCompanion)
	{
		var companionUserId = this.callOverlayPhotoCompanion.getAttribute('data-userId');
		if (companionUserId == 'phone')
		{
			this.callOverlayPhotoCompanion.src = '/bitrix/js/im/images/hidef-phone-v3.png';
		}
		else if (this.messenger.hrphoto[companionUserId])
		{
			this.callOverlayPhotoCompanion.src  = this.messenger.hrphoto[companionUserId];
			if (this.messenger.hrphoto[companionUserId] != '/bitrix/js/im/images/hidef-avatar-v3.png')
			{
				this.callOverlayPhotoCompanion.type = '';
			}
		}
		else if (this.messenger.users[companionUserId] && this.messenger.users[companionUserId].avatar == this.BXIM.pathToBlankImage)
		{
			this.callOverlayPhotoCompanion.src  = '/bitrix/js/im/images/hidef-avatar-v3.png';
		}
		else if (this.messenger.users[companionUserId])
		{
			this.callOverlayPhotoCompanion.src  = this.messenger.users[companionUserId].avatar;
			this.callOverlayPhotoCompanion.type = '';
		}
	}
	if (this.callOverlayPhotoSelf)
	{
		var userAvatarDataSelf = BX.MessengerCommon.getHrPhoto(this.BXIM.userId, this.messenger.users[this.BXIM.userId].color);
		this.callOverlayPhotoSelf.src = userAvatarDataSelf.src;
		this.callOverlayPhotoSelf.type = userAvatarDataSelf.color? 'background-color: '+userAvatarDataSelf.color: '';
		this.callOverlayPhotoMini.src = this.callOverlayPhotoSelf.src;
	}
};

BX.IM.WebRTC.prototype.callOverlayTimer = function(state) // TODO not ready yet
{
	tate = typeof(state) == 'undefined'? 'start': state;

	if (state == 'start')
	{
		this.phoneCallTimeInterval = setInterval(BX.delegate(function(){
			this.phoneCallTime++;
		}, this), 1000);
	}
	else if (state == 'pause')
	{
		clearInterval(this.phoneCallTimeInterval);
	}
	else
	{
		clearInterval(this.phoneCallTimeInterval);
	}
}

BX.IM.WebRTC.prototype.callOverlayDrawCrm = function()
{
	if (!this.callOverlayCrmBlock || !this.phoneCrm.FOUND)
		return false;

	this.callOverlayCrmBlock.innerHTML = '';

	if (this.phoneCrm.FOUND == 'Y')
	{
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-mini');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-maxi');
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-crm');
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-crm-short');

		var crmContactName = this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.NAME? this.phoneCrm.CONTACT.NAME: '';
		if (this.phoneCrm.ACTIVITY_URL)
		{
			crmContactName = '<a href="'+this.phoneCrm.SHOW_URL+'" target="_blank" class="bx-messenger-call-crm-about-link">'+crmContactName+'</a>';
		}
		var crmAbout = BX.create("div", { props : { className : 'bx-messenger-call-crm-about'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block bx-messenger-call-crm-about-contact'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-header'}, html: BX.message('IM_CRM_ABOUT_CONTACT')}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-avatar'}, html: this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.PHOTO? '<img src="'+this.phoneCrm.CONTACT.PHOTO+'" class="bx-messenger-call-crm-about-block-avatar-img">': ''}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-line-1'}, html: crmContactName}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-line-2'}, html: this.phoneCrm.CONTACT && this.phoneCrm.CONTACT.POST? this.phoneCrm.CONTACT.POST: ''})
			]}),
			this.phoneCrm.COMPANY? BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block bx-messenger-call-crm-about-company'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-header'}, html: BX.message('IM_CRM_ABOUT_COMPANY')}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-line-1'}, html: this.phoneCrm.COMPANY})
			]}): null
		]});

		var crmResponsibility = BX.create("div", { props : { className : 'bx-messenger-call-crm-about'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block bx-messenger-call-crm-about-contact'}, children: (this.phoneCrm.RESPONSIBILITY && this.phoneCrm.RESPONSIBILITY.NAME? [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-header'}, html: BX.message('IM_CRM_RESPONSIBILITY')}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-avatar'}, html: this.phoneCrm.RESPONSIBILITY.PHOTO? '<img src="'+this.phoneCrm.RESPONSIBILITY.PHOTO+'" class="bx-messenger-call-crm-about-block-avatar-img">': ''}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-line-1'}, html: this.phoneCrm.RESPONSIBILITY.NAME? this.phoneCrm.RESPONSIBILITY.NAME: ''}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-about-block-line-2'}, html: this.phoneCrm.RESPONSIBILITY.POST? this.phoneCrm.RESPONSIBILITY.POST: ''})
			]: [])})
		]});

		var crmButtons = null;
		if (this.phoneCrm.ACTIVITY_URL || this.phoneCrm.INVOICE_URL || this.phoneCrm.DEAL_URL)
		{
			crmButtons = BX.create("div", { props : { className : 'bx-messenger-call-crm-buttons'}, children: [
				this.phoneCrm.ACTIVITY_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.ACTIVITY_URL},  props : { className : 'bx-messenger-call-crm-button'}, html: BX.message('IM_CRM_BTN_ACTIVITY')}): null,
				this.phoneCrm.DEAL_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.DEAL_URL},  props : { className : 'bx-messenger-call-crm-button'}, html: BX.message('IM_CRM_BTN_DEAL')}): null,
				this.phoneCrm.INVOICE_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.INVOICE_URL},  props : { className : 'bx-messenger-call-crm-button'}, html: BX.message('IM_CRM_BTN_INVOICE')}): null,
				this.phoneCrm.CURRENT_CALL_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.CURRENT_CALL_URL},  props : { className : 'bx-messenger-call-crm-link'}, html: '+ '+BX.message('IM_CRM_BTN_CURRENT_CALL')}): null
			]})
		}

		var crmActivities = null;
		if (this.phoneCrm.ACTIVITIES && this.phoneCrm.ACTIVITIES.length > 0)
		{
			crmArActivities = [];
			for (var i = 0; i < this.phoneCrm.ACTIVITIES.length; i++)
			{
				crmArActivities.push(BX.create("div", { props : { className : 'bx-messenger-call-crm-activities-item'}, children: [
					BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.ACTIVITIES[i].URL}, props : { className : 'bx-messenger-call-crm-activities-name'}, html: this.phoneCrm.ACTIVITIES[i].TITLE}),
					BX.create("div", {
						props : { className : 'bx-messenger-call-crm-activities-status'},
						html: (this.phoneCrm.ACTIVITIES[i].OVERDUE == 'Y'? '<span class="bx-messenger-call-crm-activities-dot"></span>': '')+this.phoneCrm.ACTIVITIES[i].DATE
					})
				]}));
			}
			crmActivities = BX.create("div", { props : { className : 'bx-messenger-call-crm-activities'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-activities-header'}, html: BX.message('IM_CRM_ACTIVITIES')}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-activities-items'}, children: crmArActivities})
			]});
		}

		var crmDeals = null;
		if (this.phoneCrm.DEALS && this.phoneCrm.DEALS.length > 0)
		{
			crmArDeals = [];
			for (var i = 0; i < this.phoneCrm.DEALS.length; i++)
			{
				crmArDeals.push(BX.create("div", { props : { className : 'bx-messenger-call-crm-deals-item'}, children: [
					BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.DEALS[i].URL}, props : { className : 'bx-messenger-call-crm-deals-name'}, html: this.phoneCrm.DEALS[i].TITLE}),
					BX.create("div", {
						props : { className : 'bx-messenger-call-crm-deals-status'},
						html: this.phoneCrm.DEALS[i].STAGE
					})
				]}));
			}
			crmDeals = BX.create("div", { props : { className : 'bx-messenger-call-crm-deals'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-deals-header'}, html: BX.message('IM_CRM_DEALS')}),
				BX.create("div", { props : { className : 'bx-messenger-call-crm-deals-items'}, children: crmArDeals})
			]});
		}

		var crmBlock = [];
		if (crmActivities && crmDeals)
		{
			crmBlock = [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
				crmAbout,
				crmActivities,
				crmDeals,
				BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
				crmButtons
			];
		}
		else
		{
			if (crmActivities || crmDeals)
			{
				crmBlock = [
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmAbout,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmResponsibility,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmActivities? crmActivities: crmDeals,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmButtons
				];
			}
			else if (!crmActivities && !crmDeals && crmButtons)
			{
				BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-crm-short');
				this.callOverlayCrmBlock.innerHTML = '';
				crmBlock = [
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmAbout,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmResponsibility,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmButtons
				];
			}
			else
			{
				BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-crm-short');
				this.callOverlayCrmBlock.innerHTML = '';
				crmBlock = [
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmAbout,
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					BX.create("div", { props : { className : 'bx-messenger-call-crm-space'}}),
					crmResponsibility
				];
			}
		}
	}
	else if (this.phoneCrm.LEAD_URL || this.phoneCrm.CONTACT_URL)
	{
		BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-mini');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-maxi');
		BX.addClass(this.messenger.popupMessengerContent, 'bx-messenger-call-maxi');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-crm');
		BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-crm-short');
		crmBlock = [
			BX.create("div", { props : { className : 'bx-messenger-call-crm-phone-space'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-crm-phone-icon'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-crm-phone-icon-block'}})
			]}),
			BX.create("div", { props : { className : 'bx-messenger-call-crm-phone-space'}}),
			BX.create("div", { props : { className : 'bx-messenger-call-crm-buttons bx-messenger-call-crm-buttons-center'}, children: [
				this.phoneCrm.CONTACT_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.CONTACT_URL},  props : { className : 'bx-messenger-call-crm-button'}, html: BX.message('IM_CRM_BTN_NEW_CONTACT')}): null,
				this.phoneCrm.LEAD_URL? BX.create("a", { attrs: {target: '_blank', href: this.phoneCrm.LEAD_URL},  props : { className : 'bx-messenger-call-crm-button'}, html: BX.message('IM_CRM_BTN_NEW_LEAD')}): null
			]})
		];
	}
	BX.adjust(this.callOverlayCrmBlock, {children: crmBlock});
};

BX.IM.WebRTC.prototype.callOverlayButtons = function(buttons)
{
	if (this.callOverlay == null)
		return false;

	this.callOverlayOptions.buttons = buttons;
	BX.cleanNode(this.callOverlayButtonsBlock);
	for (var i = 0; i < buttons.length; i++)
	{
		if (buttons[i] == null)
			continue;

		var button = {};
		button.title = buttons[i].title || "";
		button.text = buttons[i].text || "";
		button.subtext = buttons[i].subtext || "";
		button.className = buttons[i].className || "";
		button.id = buttons[i].id || button.className;
		button.events = buttons[i].events || {};
		button.style = {};

		var classHide = "";
		if (typeof(buttons[i].showInMinimize) == 'boolean')
			classHide = ' bx-messenger-call-overlay-button-show-'+(buttons[i].showInMinimize? 'mini': 'maxi');
		else if (typeof(buttons[i].showInMaximize) == 'boolean')
			classHide = ' bx-messenger-call-overlay-button-show-'+(buttons[i].showInMaximize? 'maxi': 'mini');
		else if (typeof(buttons[i].disabled) == 'boolean' && buttons[i].disabled)
			classHide = ' bx-messenger-call-overlay-button-disabled';
		if (typeof(buttons[i].hide) == 'boolean' && buttons[i].hide)
			button.style.display = 'none';

		this.callOverlayButtonsBlock.appendChild(
			BX.create("div", { attrs: {id: button.id, title: button.title}, style: button.style, props : { className : 'bx-messenger-call-overlay-button'+(button.subtext? ' bx-messenger-call-overlay-button-sub': '')+classHide}, events : button.events, html: '<span class="'+button.className+'"></span><span class="bx-messenger-call-overlay-button-text">'+button.text+(button.subtext? '<div class="bx-messenger-call-overlay-button-text-sub">'+button.subtext+'</div>': '')+'</span>'})
		);
	}
};

BX.IM.WebRTC.prototype.callDialogAllowShow = function(checkActive)
{
	if (BX.MessengerCommon.isDesktop())
		return false;

	if (this.phoneMicAccess)
		return false;

	checkActive = checkActive != false;
	if (!this.phoneAPI)
	{
		if (this.callStreamSelf != null)
			return false;

		if (checkActive && !this.callActive)
			return false;
	}

	if (this.callDialogAllow)
		this.callDialogAllow.close();

	this.callDialogAllow = new BX.PopupWindow('bx-messenger-call-access', this.popupMessengerDialog, {
		//parentPopup: this.popupMessenger,
		lightShadow : true,
		zIndex: 200,
		offsetTop: (this.popupMessengerDialog? (this.callOverlayMinimize? -20: -this.popupMessengerDialog.offsetHeight/2-100): -20),
		offsetLeft: (this.callOverlay? (this.callOverlay.offsetWidth/2-170): 0),
		events : {
			onPopupClose : function() { this.destroy(); },
			onPopupDestroy : BX.delegate(function() {
				this.callDialogAllow = null;
			}, this)},
		content : BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow'}, children: [
			BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-image-block'}, children: [
				BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-center'}, children: [
					BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-arrow'}})
				]}),
				BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-center'}, children: [
					BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-button'}, html: BX.message('IM_M_CALL_ALLOW_BTN')})
				]})
			]}),
			BX.create("div", { props : { className : 'bx-messenger-call-dialog-allow-text'}, html: BX.message('IM_M_CALL_ALLOW_TEXT')})
		]})
	});
	this.callDialogAllow.show();
};

BX.IM.WebRTC.prototype.callNotifyWait = function(chatId, userId, video, callToGroup, join)
{
	if (!this.callSupport())
		return false;

	join = join == true;
	video = video == true;
	callToGroup = callToGroup == true;

	this.initiator = false;
	this.callInitUserId = userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = callToGroup? 0: userId;
	this.callChatId = chatId;
	this.callToGroup = callToGroup;
	this.callGroupUsers = this.messenger.userInChat[chatId];
	this.callVideo = video;

	this.callOverlayShow({
		toUserId : this.BXIM.userId,
		fromUserId : this.callToGroup? chatId: userId,
		callToGroup : this.callToGroup,
		video : video,
		status : BX.message(this.callToGroup? 'IM_M_CALL_ST_INVITE_2': 'IM_M_CALL_ST_INVITE'),
		buttons : [
			{
				text: BX.message('IM_M_CALL_BTN_ANSWER'),
				className: 'bx-messenger-call-overlay-button-answer',
				events: {
					click : BX.delegate(function() {
						this.BXIM.stopRepeatSound('ringtone');
						if (join)
						{
							var callToGroup = this.callToGroup;
							var callChatId = this.callChatId;
							var callUserId = this.callUserId;
							var callVideo = this.callVideo;

							this.callAbort();
							this.callOverlayClose(false);
							this.callInvite(callToGroup? 'chat'+callChatId: callUserId, callVideo);
						}
						else
						{
							this.callDialog();
							BX.ajax({
								url: this.BXIM.pathToCallAjax+'?CALL_ANSWER&V='+this.BXIM.revision,
								method: 'POST',
								dataType: 'json',
								timeout: 30,
								data: {'IM_CALL' : 'Y', 'COMMAND': 'answer', 'CHAT_ID': this.callChatId, 'CALL_TO_GROUP': this.callToGroup? 'Y': 'N',  'RECIPIENT_ID' : this.callUserId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
							});
							this.desktop.closeTopmostWindow();
						}
					}, this)
				}
			},
			{
				text: BX.message('IM_M_CALL_BTN_HANGUP'),
				className: 'bx-messenger-call-overlay-button-hangup',
				events: {
					click : BX.delegate(function() {
						this.BXIM.stopRepeatSound('ringtone');
						this.callSelfDisabled = true;
						this.callCommand(this.callChatId, 'decline', {'ACTIVE': this.callActive? 'Y': 'N', 'INITIATOR': this.initiator? 'Y': 'N'});
						this.callAbort();
						this.callOverlayClose();
					}, this)
				}
			},
			{
				text: BX.message('IM_M_CALL_BTN_CHAT'),
				className: 'bx-messenger-call-overlay-button-chat',
				showInMaximize: true,
				events: { click : BX.delegate(this.callOverlayToggleSize, this) }
			},
			{
				title: BX.message('IM_M_CALL_BTN_MAXI'),
				className: 'bx-messenger-call-overlay-button-maxi',
				showInMinimize: true,
				events: { click : BX.delegate(this.callOverlayToggleSize, this) }
			}
		]
	});

	if(!this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
	{
		var notify = {
			'title':  BX.message('IM_PHONE_DESC'),
			'text':  BX.util.htmlspecialcharsback(this.callOverlayTitle()),
			'icon': this.callUserId? this.messenger.users[this.callUserId].avatar: '',
			'tag':  'im-call'
		};
		notify.onshow = function() {
			var notify = this;
			setTimeout(function(){
				notify.close();
			}, 5000)
		}
		notify.onclick = function() {
			window.focus();
			this.close();
		}
		this.BXIM.notifyManager.nativeNotify(notify)
	}

	// Debug mode
	/*
	this.callDialog();
	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_ANSWER&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_CALL' : 'Y', 'COMMAND': 'answer', 'CHAT_ID': this.callChatId, 'CALL_TO_GROUP': this.callToGroup? 'Y': 'N',  'RECIPIENT_ID' : this.callUserId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
	});
	*/
};

BX.IM.WebRTC.prototype.callNotifyWaitDesktop = function(chatId, userId, video, callToGroup, join)
{
	this.BXIM.ppServerStatus = true;
	if (!this.callSupport() || !this.desktop.ready())
		return false;

	join = join == true;
	video = video == true;
	callToGroup = callToGroup == true;

	this.initiator = false;
	this.callInitUserId = userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = callToGroup? 0: userId;
	this.callChatId = chatId;
	this.callToGroup = callToGroup;
	this.callGroupUsers = this.messenger.userInChat[chatId];
	this.callVideo = video;

	this.callOverlayShow({
		prepare : true,
		toUserId : this.BXIM.userId,
		fromUserId : this.callToGroup? chatId: userId,
		callToGroup : this.callToGroup,
		video : video,
		status : BX.message(this.callToGroup? 'IM_M_CALL_ST_INVITE_2': 'IM_M_CALL_ST_INVITE'),
		buttons : [
			{
				text: BX.message('IM_M_CALL_BTN_ANSWER'),
				className: 'bx-messenger-call-overlay-button-answer',
				events: {
					click : BX.delegate(function() {
						if (join)
							BX.desktop.onCustomEvent("main", "bxCallJoin", [chatId, userId, video, callToGroup]);
						else
							BX.desktop.onCustomEvent("main", "bxCallAnswer", [chatId, userId, video, callToGroup]);

						BX.desktop.windowCommand('close');
					}, this)
				}
			},
			{
				text: BX.message('IM_M_CALL_BTN_HANGUP'),
				className: 'bx-messenger-call-overlay-button-hangup',
				events: {
					click : BX.delegate(function() {
						BX.desktop.onCustomEvent("main", "bxCallDecline", []);
						BX.desktop.windowCommand('close');
					}, this)
				}
			}
		]
	});
	this.desktop.drawOnPlaceholder(this.callOverlay);
	BX.desktop.setWindowPosition({X:STP_CENTER, Y:STP_VCENTER, Width: 470, Height: 120});
};

BX.IM.WebRTC.prototype.callFloatDialog = function(title, stream, audioMuted)
{
	if (!BX.MessengerCommon.isDesktop())
		return false;

	this.audioMuted = audioMuted;

	var minCallWidth = stream? this.desktop.minCallVideoWidth: this.desktop.minCallWidth;
	var minCallHeight = stream? this.desktop.minCallVideoHeight: this.desktop.minCallHeight;

	var callOverlayStyle = {
		width : minCallWidth+'px',
		height : minCallHeight+'px'
	};

	this.callOverlay =  BX.create("div", { props : { className : 'bx-messenger-call-float'+(stream? '': ' bx-messenger-call-float-audio')}, style : callOverlayStyle, children: [
		this.callOverlayVideoMain = (!stream? null: BX.create("video", {
			attrs : { autoplay : true, src: stream },
			props : { className : 'bx-messenger-call-float-video'},
			events: {'click': BX.delegate(function(){
				BX.desktop.onCustomEvent("main", "bxCallOpenDialog", []);
			}, this)}
		})),
		BX.create("div", { props : { className : 'bx-messenger-call-float-buttons'}, children: [
			BX.create("div", {
				props : { className : 'bx-messenger-call-float-button bx-messenger-call-float-button-mic'+(this.audioMuted? ' bx-messenger-call-float-button-mic-disabled':'')},
				events: {'click': BX.delegate(function(e)
				{
					this.audioMuted = !this.audioMuted;
					BX.desktop.onCustomEvent("main", "bxCallMuteMic", [this.audioMuted]);

					BX.toggleClass(BX.proxy_context, 'bx-messenger-call-float-button-mic-disabled');
					var text = BX.findChildByClassName(BX.proxy_context, "bx-messenger-call-float-button-text");
					text.innerHTML = BX.message('IM_M_CALL_BTN_MIC')+' '+BX.message('IM_M_CALL_BTN_MIC_'+(this.audioMuted? 'OFF': 'ON'));

					BX.PreventDefault(e);
				}, this)},
				children: [
					BX.create("span", { props : { className : 'bx-messenger-call-float-button-icon'}}),
					BX.create("span", { props : { className : 'bx-messenger-call-float-button-text'}, html: BX.message('IM_M_CALL_BTN_MIC')+' '+BX.message('IM_M_CALL_BTN_MIC_'+(this.audioMuted? 'OFF': 'ON'))})
				]
			}),
			BX.create("div", {
				props : { className : 'bx-messenger-call-float-button bx-messenger-call-float-button-decline'},
				events: {'click': BX.delegate(function(e){
					BX.desktop.onCustomEvent("main", "bxCallDecline", []);
					BX.desktop.windowCommand('close');

					BX.PreventDefault(e);
				}, this)},
				children: [
					BX.create("span", { props : { className : 'bx-messenger-call-float-button-icon'}}),
					BX.create("span", { props : { className : 'bx-messenger-call-float-button-text'}, html: BX.message('IM_M_CALL_BTN_HANGUP')})
				]
			})
		]})
	]});

	this.desktop.drawOnPlaceholder(this.callOverlay);

	BX.desktop.setWindowMinSize({ Width: minCallWidth, Height: minCallHeight });
	BX.desktop.setWindowResizable(false);
	BX.desktop.setWindowClosable(false);
	BX.desktop.setWindowResizable(false);
	BX.desktop.setWindowTitle(BX.util.htmlspecialcharsback(BX.util.htmlspecialcharsback(title)));

	if (BXDesktopSystem.QuerySettings('global_topmost_x',null))
	{
		BX.desktop.setWindowPosition({X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)), Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)), Width: minCallWidth, Height: minCallHeight, Mode: STP_FRONT});
		if (!BX.browser.IsMac())
			BX.desktop.setWindowPosition({X: parseInt(BXDesktopSystem.QuerySettings('global_topmost_x', STP_RIGHT)), Y: parseInt(BXDesktopSystem.QuerySettings('global_topmost_y', STP_TOP)), Width: minCallWidth, Height: minCallHeight, Mode: STP_FRONT});
	}
	else
	{
		BX.desktop.setWindowPosition({X: STP_RIGHT, Y: STP_TOP, Width: minCallWidth, Height: minCallHeight, Mode: STP_FRONT});
		if (!BX.browser.IsMac())
			BX.desktop.setWindowPosition({X: STP_RIGHT, Y: STP_TOP, Width: minCallWidth, Height: minCallHeight, Mode: STP_FRONT});
	}

	if (stream)
	{
		clearInterval(this.callAspectCheckInterval);
		this.callAspectCheckInterval = setInterval(BX.delegate(function(){
			if (this.callOverlayVideoMain.offsetWidth < this.callOverlayVideoMain.offsetHeight)
			{
				if (this.callAspectHorizontal)
				{
					this.callAspectHorizontal = false;
					BX.addClass(this.callOverlay, 'bx-messenger-call-overlay-aspect-vertical');
					BX.desktop.setWindowSize({Width: this.desktop.minCallVideoHeight, Height: this.desktop.minCallVideoWidth});
				}
			}
			else
			{
				if (!this.callAspectHorizontal)
				{
					this.callAspectHorizontal = true;
					BX.removeClass(this.callOverlay, 'bx-messenger-call-overlay-aspect-vertical');
					BX.desktop.setWindowSize({Width: this.desktop.minCallVideoWidth, Height: this.desktop.minCallVideoHeight});
				}
			}
		}, this), 500);
	}

	BX.desktop.addCustomEvent("bxCallChangeMainVideo", BX.delegate(function(src) {
		this.callOverlayVideoMain.src = src;
	}, this));
};

BX.IM.WebRTC.prototype.storageSet = function(params)
{
	if (params.key == 'vite')
	{
		if(params.value === true || !this.BXIM.webrtc.callSelfDisabled)
		{
			this.phoneTransferEnabled = params.value;
		}
	}
	else if (params.key == 'viExternalCard')
	{
		if(params.value === false)
		{
			this.hideExternalCall();
		}
	}
};

/* WebRTC Cloud Phone */
BX.IM.WebRTC.prototype.phoneSupport = function()
{
	return this.phoneEnabled && (this.phoneDeviceActive || this.ready());
}

BX.IM.WebRTC.prototype.phoneMute = function()
{
	if (!this.phoneCurrentCall)
		return false;

	this.phoneMicMuted = true;
	this.phoneCurrentCall.muteMicrophone();
}

BX.IM.WebRTC.prototype.phoneUnmute = function()
{
	if (!this.phoneCurrentCall)
		return false;

	this.phoneMicMuted = false;
	this.phoneCurrentCall.unmuteMicrophone();
}

BX.IM.WebRTC.prototype.phoneToggleAudio = function()
{
	if (!this.phoneCurrentCall)
		return false;

	if (this.phoneMicMuted)
	{
		this.phoneCurrentCall.unmuteMicrophone();
		this.phoneCallView.setMuted(false);
	}
	else
	{
		this.phoneCurrentCall.muteMicrophone();
	}
	this.phoneMicMuted = !this.phoneMicMuted;
}

BX.IM.WebRTC.prototype.phoneDeviceCall = function(status)
{
	var result = true;
	if (typeof(status) == 'boolean')
	{
		this.BXIM.setLocalConfig('viDeviceCallBlock', !status);
		BX.localStorage.set('viDeviceCallBlock', !status, 86400);
		if(this.phoneCallView)
			this.phoneCallView.setDeviceCall(status);
	}
	else
	{
		var deviceCallBlock = this.BXIM.getLocalConfig('viDeviceCallBlock');
		if (!deviceCallBlock)
		{
			deviceCallBlock = BX.localStorage.get('viDeviceCallBlock');
		}
		result = this.phoneDeviceActive && deviceCallBlock != true;
	}
	return result;

}

BX.IM.WebRTC.prototype.openKeyPad = function(e)
{
	var bindElement;
	var offsetTop;
	var offsetLeft;
	var anglePosition = this.BXIM.design == 'DESKTOP' && !this.callActive? "left": "top";
	var angleOffset = this.BXIM.design == 'DESKTOP'? (this.callActive? 120: 76): 94;

	if (!this.phoneSupport() && !(this.BXIM.desktopStatus && this.BXIM.desktopVersion >= 18))
	{
		if (!BX.MessengerCommon.isDesktop())
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_NO_WEBRT'), [
				this.BXIM.platformName == ''? null: new BX.PopupWindowButton({
					text : BX.message('IM_M_CALL_BTN_DOWNLOAD'),
					className : "popup-window-button-accept",
					events : { click : BX.delegate(function() { window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp"); BX.proxy_context.popupWindow.close(); }, this) }
				}),
				new BX.PopupWindowButton({
					text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
					className : "popup-window-button-decline",
					events : { click : function() { this.popupWindow.close(); } }
				})
			]);
		}
		return false;
	}

	if(this.callInit || this.callActive || this.phoneCurrentCall || BX.localStorage.get('viInitedCall') || BX.localStorage.get('viExternalCard'))
	{
		return false;
	}

	if(this.phoneKeypad !== null)
	{
		this.phoneKeypad.close();
		return false;
	}

	if (this.messenger.popupMessenger)
	{
		if (!this.callActive)
		{
			if (this.BXIM.design == 'DESKTOP')
			{
				bindElement = BX('bx-desktop-tab-im-phone');
				offsetTop = -110;
				offsetLeft = 60;
			}
			else
			{
				BX.addClass(this.messenger.popupContactListSearchCall, 'bx-messenger-input-search-call-active');
				bindElement = this.messenger.popupContactListSearchCall;
				offsetTop = -10;
				offsetLeft = -52;
			}
		}
		else
		{
			bindElement = BX('bx-messenger-call-overlay-button-keypad');
			offsetTop = 7;
			offsetLeft = BX.MessengerCommon.isPage()? -90: -65;
			if (BX.MessengerCommon.isPage())
			{
				BX.MessengerWindow.closeTab('im-phone');
			}
		}
	}
	else
	{
		bindElement = this.notify.panelButtonCall;
		offsetTop = this.notify.panelButtonCallOffsetTop? this.notify.panelButtonCallOffsetTop: 5;
		offsetLeft = this.notify.panelButtonCallOffsetLeft? this.notify.panelButtonCallOffsetLeft: -75;
		anglePosition = this.notify.panelButtonCallAnlgePosition? this.notify.panelButtonCallAnlgePosition: anglePosition;
		angleOffset = this.notify.panelButtonCallAnlgeOffset? this.notify.panelButtonCallAnlgeOffset: angleOffset;
	}

	this.messenger.setClosingByEsc(false);
	this.phoneKeypad = new BX.PhoneKeypad({
		bindElement: bindElement,
		offsetTop: offsetTop,
		offsetLeft: offsetLeft,
		anglePosition: anglePosition,
		angleOffset: angleOffset,
		defaultLineId: this.phoneDefaultLineId,
		lines: this.phoneLines,
		availableLines: this.phoneAvailableLines,
		history: this.phoneGetHistory(),

		onDial: function(e)
		{
			var params = {};
			this.phoneKeypad.close();

			if(e.lineId)
			{
				params['LINE_ID'] = e.lineId;
			}

			this.phoneCall(e.phoneNumber, params);
		}.bind(this),
		onClose: function()
		{
			this.phoneKeypad = null;
			if (this.messenger.popupMessenger && this.BXIM.design == 'DESKTOP' && BX.MessengerCommon.isPage())
			{
				if (BX.MessengerWindow.lastTabTarget != 'im')
				{
					BX.MessengerWindow.changeTab(this.BXIM.dialogOpen? 'im': 'notify');
				}
				else
				{
					BX.MessengerWindow.closeTab('im-phone');
				}
			}

			this.messenger.setClosingByEsc(true);
			BX.removeClass(this.messenger.popupContactListSearchCall, 'bx-messenger-input-search-call-active');
		}.bind(this)
	});
	this.phoneKeypad.show();
}

BX.IM.WebRTC.prototype.phoneCount = function(numbers)
{
	var count = 0;
	if (typeof (numbers) === 'object')
	{
		if (numbers.PERSONAL_MOBILE)
			count++;
		else if (numbers.PERSONAL_PHONE)
			count++;
		else if (numbers.WORK_PHONE)
			count++;
	}

	return count;
}

BX.IM.WebRTC.prototype.phoneDisconnectAfterCall = function(value)
{
	if (BX.MessengerCommon.isDesktop())
	{
		value = false;
	}

	this.phoneDisconnectAfterCallFlag = value === false? false: true;

	return true;
}

BX.IM.WebRTC.prototype.phoneDisplayExternal = function(params)
{
	var number = params.phoneNumber;
	this.phoneLog(number, params);

	this.phoneNumberUser = BX.util.htmlspecialchars(number);

	number = BX.MessengerCommon.phoneCorrect(number);
	if (typeof(params) != 'object')
		params = {};

	if (this.callActive || this.callInit)
		return;

	if(this.phoneCallView)
		return;

	this.initiator = true;
	this.callInitUserId = this.BXIM.userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callToGroup = 0;
	this.callGroupUsers = [];
	this.phoneNumber = number;

	this.phoneCallView = new BX.PhoneCallView({
		BXIM: this.BXIM,
		callId: params.callId,
		config: params.config,
		direction: BX.PhoneCallView.Direction.outgoing,
		phoneNumber : this.phoneNumber,
		statusText : BX.message('IM_M_CALL_ST_CONNECT'),
		hasSipPhone: true,
		deviceCall: true,
		portalCall: params.portalCall,
		portalCallUserId: params.portalCallUserId,
		portalCallData: params.portalCallData,
		crm: params.showCrmCard,
		crmEntityType: params.crmEntityType,
		crmEntityId: params.crmEntityId,
		crmData: this.phoneCrm
	});
	this.bindPhoneViewCallbacks(this.phoneCallView);
	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.idle);
	this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connected);
	this.phoneCallView.show();
}

BX.IM.WebRTC.prototype.isRestLine = function(lineId)
{
	if(this.phoneLines.hasOwnProperty(lineId))
		return this.phoneLines[lineId].TYPE === 'REST';
	else
		return false;
};

BX.IM.WebRTC.prototype.setPhoneNumber = function(phoneNumber)
{
	var matches = /(\d+)([;#]*)([\d,]*)/.exec(phoneNumber);
	this.phoneFullNumber = phoneNumber;
	if(matches)
	{
		this.phoneNumber = matches[1];
	}
};

BX.IM.WebRTC.prototype.phoneCall = function(number, params)
{
	if (BX.localStorage.get('viInitedCall'))
		return false;

	if (this.phoneCallView)
		return false;

	if(this.callActive || this.callInit)
		return false;

	if (this.popupKeyPad)
		this.popupKeyPad.close();

	if (number != '')
	{
		this.phoneAddToHistory(number);
	}

	var lineId = BX.type.isPlainObject(params) && params['LINE_ID'] ? params['LINE_ID'] : this.phoneDefaultLineId;
	if(this.isRestLine(lineId))
	{
		BX.MessengerCommon.phoneStartCallViaRestApp(number, lineId, params);
		return true;
	}

	this.phoneLog(number, params);

	this.phoneNumberUser = BX.util.htmlspecialchars(number);
	numberOriginal = number;

	if (typeof(params) != 'object')
		params = {};

	var internationalNumber = BX.MessengerCommon.phoneCorrect(number);
	if(internationalNumber[0] === '+')
	{
		internationalNumber = internationalNumber.substr(1);
	}

	if (internationalNumber.length <= 0)
	{
		this.BXIM.openConfirm({title: BX.message('IM_PHONE_WRONG_NUMBER'), message: BX.message('IM_PHONE_WRONG_NUMBER_DESC')});
		return false;
	}

	this.setPhoneNumber(internationalNumber);

	if (!this.phoneSupport())
	{
		if (!BX.MessengerCommon.isDesktop())
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_NO_WEBRT'), [
				new BX.PopupWindowButton({
					text : BX.message('IM_M_CALL_BTN_DOWNLOAD'),
					className : "popup-window-button-accept",
					events : { click : BX.delegate(function() { window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp"); BX.proxy_context.popupWindow.close(); }, this) }
				}),
				new BX.PopupWindowButton({
					text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
					className : "popup-window-button-decline",
					events : { click : function() { this.popupWindow.close(); } }
				})
			]);
		}
		return false;
	}

	this.initiator = true;
	this.callInitUserId = this.BXIM.userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callToGroup = 0;
	this.phoneCallExternal = this.phoneDeviceCall();
	this.callGroupUsers = [];
	this.phoneParams = params;

	this.phoneCallView = new BX.PhoneCallView({
		phoneNumber: this.phoneFullNumber,
		callTitle: this.phoneNumberUser,
		fromUserId: this.BXIM.userId,
		direction: BX.PhoneCallView.Direction.outgoing,
		uiState: BX.PhoneCallView.UiState.connectingOutgoing,
		status: BX.message('IM_M_CALL_ST_CONNECT'),
		hasSipPhone: this.phoneDeviceActive,
		deviceCall: this.phoneCallExternal,
		BXIM: this.BXIM,
		crmData: this.phoneCrm,
		autoFold: (params['AUTO_FOLD'] === true)
	});
	this.bindPhoneViewCallbacks(this.phoneCallView);
	this.phoneCallView.show();

	this.BXIM.playSound("start");

	if (this.phoneCallExternal)
	{
		this.phoneCallDevice = 'PHONE';
		this.phoneCallView.setProgress('wait');
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_PHONE_NOTICE'));

		BX.MessengerCommon.phoneCommand(
			'deviceStartCall',
			{
				'NUMBER': numberOriginal.toString().replace(/[^0-9\*#,;]/g, ''),
				'PARAMS': params
			},
			true,
			function(response)
			{
				this.phoneCallId = response.CALL_ID;
				this.phoneCallExternal = (response.EXTERNAL == true);
				this.phoneCallConfig = response.CONFIG;
				this.phoneCallView.setProgress('wait');
				this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_WAIT_PHONE'));
				this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);
				this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);

				if (BX.MessengerCommon.isDesktop())
				{
					//todo
					BX.desktop.changeTab('im');
					BX.desktop.windowCommand("show");
					this.BXIM.desktop.closeTopmostWindow();
				}

			}.bind(this)
		);
	}
	else
	{
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_CALL_INIT'));

		if (!this.phoneLogin || !this.phoneServer)
			BX.MessengerCommon.phoneAuthorize();
		else
			this.phoneApiInit();
	}
}

BX.IM.WebRTC.prototype.phoneAddToHistory = function(phoneNumber)
{
	var oldHistory = this.phoneHistory;
	var phoneIndex = oldHistory.indexOf(phoneNumber);

	if(phoneIndex === 0)
	{
		//it's the first element already, nothing to do
	}
	else if (phoneIndex > 0)
	{
		//moving number to the top
		oldHistory.splice(phoneIndex, phoneIndex);
		this.phoneHistory = [phoneNumber].concat(oldHistory);
	}
	else
	{
		//adding as the top element of history
		this.phoneHistory = [phoneNumber].concat(oldHistory.slice(0, 4));
	}
	this.BXIM.setLocalConfig('phone-history', this.phoneHistory);
}

BX.IM.WebRTC.prototype.phoneGetHistory = function()
{
	return this.phoneHistory;
}

BX.IM.WebRTC.prototype.startCallList = function(callListId, params)
{
	callListId = parseInt(callListId);
	if(callListId == 0 || this.callActive || this.callInit || this.phoneCallView || this.isCallListMode())
		return false;

	this.callListId = callListId;
	this.phoneCallView = new BX.PhoneCallView({
		crm: true,
		callListId: callListId,
		callListStatusId: params.callListStatusId,
		callListItemIndex: params.callListItemIndex,
		direction: BX.PhoneCallView.Direction.outgoing,
		makeCall: (params.makeCall === true),
		uiState: BX.PhoneCallView.UiState.outgoing,
		BXIM: this.BXIM,
		webformId: params.webformId || 0,
		webformSecCode: params.webformSecCode || '',
		hasSipPhone: this.phoneDeviceActive,
		deviceCall: this.phoneDeviceCall(),
		crmData: this.phoneCrm
	});

	this.bindPhoneViewCallbacks(this.phoneCallView);
	this.phoneCallView.show();

	return true;
};

BX.IM.WebRTC.prototype.isCallListMode = function()
{
	return (this.callListId > 0);
};

BX.IM.WebRTC.prototype.callListMakeCall = function(e)
{
	if(this.isRestLine(this.phoneDefaultLineId))
	{
		BX.MessengerCommon.phoneStartCallViaRestApp(
			e.phoneNumber,
			this.phoneDefaultLineId,
			{
				'ENTITY_TYPE': 'CRM_' + e.crmEntityType,
				'ENTITY_ID': e.crmEntityId,
				'CALL_LIST_ID': e.callListId
			}
		);
		return true;
	}

	if (BX.localStorage.get('viInitedCall'))
		return false;

	if(this.callActive || this.callInit)
		return false;

	if(!this.phoneCallView)
		return false;

	this.lastCallListCallParams = e;

	if (typeof(params) != 'object')
		params = {};

	if (!this.phoneSupport())
	{
		this.phoneCallView.setStatusText(BX.message('IM_CALL_NO_WEBRT'));
		this.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
		return false;
	}

	var number = e.phoneNumber;
	var numberOriginal = number;
	var internationalNumber = BX.MessengerCommon.phoneCorrect(number);
	if(internationalNumber[0] === '+')
		internationalNumber = internationalNumber.substr(1);

	if (internationalNumber.length <= 0)
	{
		this.phoneCallView.setStatusText(BX.message('IM_PHONE_WRONG_NUMBER_DESC'));
		return false;
	}

	this.initiator = true;
	this.callInitUserId = this.BXIM.userId;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callToGroup = 0;
	this.phoneCallExternal = this.phoneDeviceCall();
	this.callGroupUsers = [];
	this.setPhoneNumber(internationalNumber);
	this.phoneParams = {
		'ENTITY_TYPE': 'CRM_' + e.crmEntityType,
		'ENTITY_ID': e.crmEntityId,
		'CALL_LIST_ID': e.callListId
	};

	this.BXIM.playSound("start");

	if (this.phoneCallExternal)
	{
		this.phoneCallDevice = 'PHONE';
		this.phoneCallView.setProgress('wait');
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_PHONE_NOTICE'));
		this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);
		BX.MessengerCommon.phoneCommand(
			'deviceStartCall',
			{
				'NUMBER': numberOriginal.toString().replace(/[^0-9\*#,;]/g, ''),
				'PARAMS': this.phoneParams
			},
			true,
			function(response)
			{
				this.phoneCallId = response.CALL_ID;
				this.phoneCallExternal = (params.EXTERNAL == true);
				this.phoneCallConfig = params.CONFIG;
				this.phoneCallView.setProgress('wait');
				this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_WAIT_PHONE'));
				this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);
				this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);

				if (BX.MessengerCommon.isDesktop())
				{
					//todo
					BX.desktop.changeTab('im');
					BX.desktop.windowCommand("show");
					this.BXIM.desktop.closeTopmostWindow();
				}

			}.bind(this)
		);
	}
	else
	{
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_CALL_INIT'));
		this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);

		if (!this.phoneLogin || !this.phoneServer)
			BX.MessengerCommon.phoneAuthorize();
		else
			this.phoneApiInit();
	}
}

BX.IM.WebRTC.prototype.phoneIncomingAnswer = function()
{
	this.BXIM.stopRepeatSound('ringtone');
	this.callSelfDisabled = true;
	BX.MessengerCommon.phoneCommand((this.phoneTransferEnabled? 'answerTransfer': 'answer'), {'CALL_ID' : this.phoneCallId});

	if (this.popupKeyPad)
		this.popupKeyPad.close();

	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingIncoming);
	this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);

	if (!this.phoneLogin || !this.phoneServer)
	{
		BX.MessengerCommon.phoneAuthorize();
	}
	else
	{
		this.phoneApiInit();
	}
}

BX.IM.WebRTC.prototype.phoneApiInit = function()
{
	if (!this.phoneSupport())
		return false;

	if (!this.phoneLogin || !this.phoneServer)
	{
		this.phoneCallFinish();
		this.callOverlayProgress('offline');
		this.callAbort(BX.message('IM_PHONE_ERROR'));
		this.phoneCallView.setUiState(BX.PhoneCallView.UiState.error);
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);

		return false;
	}

	if (this.phoneAPI)
	{
		if (this.phoneSDKinit)
		{
			if (this.phoneIncoming)
			{
				BX.MessengerCommon.phoneCommand((this.phoneTransferEnabled?'readyTransfer': 'ready'), {'CALL_ID': this.phoneCallId});
			}
			else if (this.callInitUserId == this.BXIM.userId)
			{
				this.phoneOnSDKReady();
			}
		}
		else
		{
			this.phoneOnSDKReady();
		}
		return true;
	}

	this.phoneAPI = VoxImplant.getInstance();
	this.phoneAPI.addEventListener(VoxImplant.Events.SDKReady, BX.delegate(this.phoneOnSDKReady, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.ConnectionEstablished, BX.delegate(this.phoneOnConnectionEstablished, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.ConnectionFailed, BX.delegate(this.phoneOnConnectionFailed, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.ConnectionClosed, BX.delegate(this.phoneOnConnectionClosed, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.IncomingCall, BX.delegate(this.phoneOnIncomingCall, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.AuthResult, BX.delegate(this.phoneOnAuthResult, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.MicAccessResult, BX.delegate(this.phoneOnMicResult, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.SourcesInfoUpdated, BX.delegate(this.phoneOnInfoUpdated, this));
	this.phoneAPI.addEventListener(VoxImplant.Events.NetStatsReceived, BX.delegate(this.phoneOnNetStatsReceived, this));

	var progressToneCountry = this.BXIM.language.toUpperCase();
	if (progressToneCountry == 'EN')
		progressToneCountry = 'US';

	var phoneApiParameters = {
		useRTCOnly: true,
		micRequired: true,
		videoSupport: false,
		progressTone: false
	};

	if(this.debug)
	{
		phoneApiParameters.showDebugInfo = true;
		phoneApiParameters.showWarnings = true;
		phoneApiParameters.prettyPrint = true;
	}

	var optionalAudioConstraints = [];

	if(this.enableMicAutoParameters === false)
	{
		phoneApiParameters.audioConstraints = {optional: [
			{echoCancellation:false},
			{googEchoCancellation:false},
			{googEchoCancellation2:false},
			{googDAEchoCancellation:false},
			{googAutoGainControl: false},
			{googAutoGainControl2: false},
			{mozAutoGainControl: false},
			{googNoiseSuppression: false},
			{googNoiseSuppression2: false},
			{googHighpassFilter: false},
			{googTypingNoiseDetection: false},
			{googAudioMirroring: false}
		]};
	}

	this.phoneAPI.init(phoneApiParameters);
	if(this.defaultMicrophone)
	{
		this.phoneAPI.useAudioSource(this.defaultMicrophone);
	}

	if(BX.MessengerCommon.isDesktop() && BX.type.isFunction(this.phoneAPI.setLoggerCallback))
	{
		this.phoneAPI.enableSilentLogging();
		this.phoneAPI.setLoggerCallback(function(e)
		{
			this.phoneLog(e.label + ": " + e.message);
		}.bind(this))
	}

	this.phoneSDKinit = true;
	return true;
}

BX.IM.WebRTC.prototype.phoneOnSDKReady = function(params)
{
	this.phoneLog('SDK ready');

	params = params || {};
	params.delay = params.delay || false;

	if (!params.delay && this.phoneDeviceActive)
	{
		if (!this.phoneIncoming && !this.phoneDeviceCall())
		{
			if (BX.MessengerCommon.isPage())
			{
				BX.MessengerWindow.changeTab('im');
			}
			if (BX.MessengerCommon.isDesktop())
			{
				BX.desktop.windowCommand("show");
				this.desktop.closeTopmostWindow();
			}
			this.callOverlayProgress('wait');
			this.callDialogAllowTimeout = setTimeout(BX.delegate(function (){

				this.phoneOnSDKReady({delay : true});
			}, this), 5000);
			return false;
		}
	}

	if (BX.MessengerCommon.isDesktop() && this.BXIM.init)
	{
		BX.desktop.syncPause(true);
	}

	if (!this.phoneAPI.connected())
	{
		this.phoneAPI.connect();

		clearTimeout(this.callDialogAllowTimeout);
		this.callDialogAllowTimeout = setTimeout(BX.delegate(function(){
			this.callDialogAllowShow();
		}, this), 1500);

		this.phoneCallView.setProgress('wait');
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_WAIT_ACCESS'));
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);
		if(this.phoneIncoming)
			this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingIncoming);
		else
			this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);
	}
	else
	{
		this.phoneLog('Connection exists');

		this.phoneCallView.setProgress('connect');
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_CONNECT'));
		this.phoneOnAuthResult({result: true});
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);
		if(this.phoneIncoming)
			this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingIncoming);
		else
			this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connectingOutgoing);

	}
}

BX.IM.WebRTC.prototype.phoneOnConnectionEstablished = function(e)
{
	BX.MessengerCommon.phoneOnConnectionEstablished(e);
	this.phoneAPI.requestOneTimeLoginKey(this.phoneLogin+"@"+this.phoneServer);
}

BX.IM.WebRTC.prototype.phoneOnConnectionFailed = function(e)
{
	BX.MessengerCommon.phoneOnConnectionFailed(e);
}

BX.IM.WebRTC.prototype.phoneOnConnectionClosed = function(e)
{
	BX.MessengerCommon.phoneOnConnectionClosed(e);
}

BX.IM.WebRTC.prototype.phoneOnIncomingCall = function(params)
{
	BX.MessengerCommon.phoneOnIncomingCall(params);
}

BX.IM.WebRTC.prototype.phoneOnAuthResult = function(e)
{
	BX.MessengerCommon.phoneOnAuthResult(e);
}

BX.IM.WebRTC.prototype.phoneOnMicResult = function(e)
{
	BX.MessengerCommon.phoneOnMicResult(e);
}

BX.IM.WebRTC.prototype.phoneOnInfoUpdated = function(e)
{
	this.phoneLog('Info updated', this.phoneAPI.audioSources(), this.phoneAPI.videoSources());
}

BX.IM.WebRTC.prototype.phoneOnCallConnected = function(e)
{
	if (BX.MessengerCommon.isDesktop() && this.BXIM.init)
	{
		BX.desktop.syncPause(true);
	}

	this.BXIM.stopRepeatSound('ringtone', 5000);
	BX.localStorage.set('viInitedCall', true, 7);

	clearInterval(this.phoneConnectedInterval);
	this.phoneConnectedInterval = setInterval(function(){
		BX.localStorage.set('viInitedCall', true, 7);
	}, 5000);

	this.desktop.closeTopmostWindow();

	this.phoneLog('Call connected', e);

	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connected);
	this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connected);
	this.phoneCallView.setProgress('online');
	this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_ONLINE'));
	this.callActive = true;
	if (!this.BXIM.windowFocus)
		this.desktop.openCallFloatDialog();
}

BX.IM.WebRTC.prototype.phoneOnCallDisconnected = function(e)
{
	BX.MessengerCommon.phoneOnCallDisconnected(e);
}

BX.IM.WebRTC.prototype.phoneOnCallFailed = function(e)
{
	BX.MessengerCommon.phoneOnCallFailed(e);
}

BX.IM.WebRTC.prototype.phoneOnProgressToneStart = function(e)
{
	BX.MessengerCommon.phoneOnProgressToneStart(e);
}

BX.IM.WebRTC.prototype.phoneOnProgressToneStop = function(e)
{
	BX.MessengerCommon.phoneOnProgressToneStop(e);
}

BX.IM.WebRTC.prototype.phoneOnNetStatsReceived = function(e)
{
	BX.MessengerCommon.phoneOnNetStatsReceived(e);
}

BX.IM.WebRTC.prototype.phoneCallFinish = function()
{
	BX.MessengerCommon.phoneCallFinish();
}

BX.IM.WebRTC.prototype.bindPhoneViewCallbacks = function(callView)
{
	if(!callView instanceof BX.PhoneCallView)
		return false;

	callView.setCallback('mute', function(){this.phoneMute();}.bind(this));
	callView.setCallback('unmute', function(){this.phoneUnmute();}.bind(this));
	callView.setCallback('hold', function(){BX.MessengerCommon.phoneHold();}.bind(this));
	callView.setCallback('unhold', function(){BX.MessengerCommon.phoneUnhold();}.bind(this));
	callView.setCallback('answer', this.phoneIncomingAnswer.bind(this));
	callView.setCallback('skip', function()
	{
		this.phoneCallFinish();
		this.callAbort();
		this.phoneCallView.close();
	}.bind(this));
	callView.setCallback('hangup', function()
	{
		this.phoneCallFinish();
		this.callAbort();
		this.BXIM.playSound('stop');
		this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_FINISHED'));
		this.phoneCallView.setCallState(BX.PhoneCallView.CallState.idle);
		if(this.isCallListMode())
		{
			this.phoneCallView.setUiState(BX.PhoneCallView.UiState.outgoing);
			if(this.phoneCallView.isFolded())
			{
				this.phoneCallView.unfold();
			}
		}
		else
		{
			this.phoneCallView.close();
		}

	}.bind(this));
	callView.setCallback('transfer', function(e)
	{
		if(e.type == 'user')
		{
			this.phoneTransferType = 'user';
			this.phoneTransferUser = e.userId;
			this.sendInviteTransfer();
		}
		else if(e.type == 'phone')
		{
			this.phoneTransferType = 'phone';
			this.phoneTransferUser = e.userId;
			this.phoneTransferPhone = e.phone;
			this.sendInviteTransfer();
		}
	}.bind(this));
	callView.setCallback('cancelTransfer', this.cancelInviteTransfer.bind(this));
	callView.setCallback('callListMakeCall', this.callListMakeCall.bind(this));
	callView.setCallback('close', function()
	{
		this.callListId = 0;
		if(this.phoneCallView)
		{
			this.phoneCallView.dispose();
			this.phoneCallView = null;
		}

		if(this.phoneCallDevice == 'PHONE')
		{
			this.phoneCallId = '';
			this.callActive = false;
			this.callInit = false;
			this.phoneCallExternal = false;
			this.callSelfDisabled = false;
			clearInterval(this.BXIM.webrtc.phoneConnectedInterval);

			BX.localStorage.set('viExternalCard', false);
		}
	}.bind(this));
	callView.setCallback('switchDevice', function(e)
	{
		var phoneNumber = e.phoneNumber;
		var lastCallListCallParams = this.lastCallListCallParams;
		this.phoneCallFinish();
		this.callAbort();
		this.phoneDeviceCall(!this.phoneDeviceCall());
		this.phoneCallView.setDeviceCall(this.phoneDeviceCall());
		if(this.isCallListMode())
		{
			this.callListMakeCall(lastCallListCallParams);
		}
		else
		{
			this.phoneCallView.close();
			this.phoneCall(phoneNumber);
		}
	}.bind(this));
	callView.setCallback('qualityGraded', function(grade)
	{
		var message = {
			COMMAND: 'gradeQuality',
			grade: grade
		};
		if(this.phoneCurrentCall)
			this.phoneCurrentCall.sendMessage(JSON.stringify(message));

	}.bind(this));
	callView.setCallback('dialpadButtonClicked', function(key)
	{
		BX.MessengerCommon.phoneSendDTMF(key);
	}.bind(this));
}

BX.IM.WebRTC.prototype.phoneIncomingWait = function(params)
{
	/*chatId, callId, callerId, lineNumber, companyPhoneNumber, isCallback*/
	params.isCallback = !!params.isCallback;
	this.phoneLog('incoming call', JSON.stringify(params));

	if (!this.phoneSupport())
	{
		if (!BX.MessengerCommon.isDesktop())
		{
			this.BXIM.openConfirm(BX.message('IM_CALL_NO_WEBRT'), [
				new BX.PopupWindowButton({
					text : BX.message('IM_M_CALL_BTN_DOWNLOAD'),
					className : "popup-window-button-accept",
					events : { click : BX.delegate(function() { window.open(BX.browser.IsMac()? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg": "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp"); BX.proxy_context.popupWindow.close(); }, this) }
				}),
				new BX.PopupWindowButton({
					text : BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
					className : "popup-window-button-decline",
					events : { click : function() { this.popupWindow.close(); } }
				})
			]);
		}
		return false;
	}

	this.phoneNumberUser = BX.util.htmlspecialchars(params.callerId);
	params.callerId = params.callerId.replace(/[^a-zA-Z0-9\.]/g, '');

	if(this.callActive || this.callInit)
		return false;

	this.initiator = true;
	this.callInitUserId = 0;
	this.callInit = true;
	this.callActive = false;
	this.callUserId = 0;
	this.callChatId = 0;
	this.callToGroup = 0;
	this.callGroupUsers = [];
	this.phoneIncoming = true;
	this.phoneCallId = params.callId;
	this.phoneNumber = params.callerId;
	this.phoneParams = {};

	var direction;

	if (this.phoneTransferEnabled)
		direction = BX.PhoneCallView.Direction.incomingTransfer;
	else if (params.isCallback)
		direction = BX.PhoneCallView.Direction.callback;
	else
		direction = BX.PhoneCallView.Direction.incoming;

	this.phoneCallView = new BX.PhoneCallView({
		BXIM: this.BXIM,
		userId : this.BXIM.userId,
		phoneNumber : this.phoneNumber,
		lineNumber : params.lineNumber,
		companyPhoneNumber : params.companyPhoneNumber,
		callTitle : this.phoneNumberUser,
		direction : direction,
		statusText : (params.isCallback ? BX.message('IM_PHONE_INVITE_CALLBACK') : BX.message('IM_PHONE_INVITE')),
		crm: params.showCrmCard,
		crmEntityType: params.crmEntityType,
		crmEntityId: params.crmEntityId,
		crmActivityId: params.crmActivityId,
		crmActivityEditUrl: params.crmActivityEditUrl,
		callId: this.phoneCallId,
		crmData: this.phoneCrm
	});
	this.bindPhoneViewCallbacks(this.phoneCallView);
	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.incoming);
	this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connecting);
	if(params.config)
	{
		this.phoneCallView.setConfig(params.config);
	}

	this.phoneCallView.show();

	if(params.portalCall)
	{
		this.phoneCallView.setPortalCall(true);
		this.phoneCallView.setPortalCallData(params.portalCallData);
		this.phoneCallView.setPortalCallUserId(params.portalCallUserId);
	}


	if(!this.BXIM.windowFocus && this.BXIM.notifyManager.nativeNotifyGranted())
	{
		var notify = {
			'title':  BX.message('IM_PHONE_DESC'),
			'text':  BX.util.htmlspecialcharsback(this.phoneCallView.getTitle()),
			'icon': this.callUserId? this.messenger.users[this.callUserId].avatar: '',
			'tag':  'im-call'
		};
		notify.onshow = function() {
			var notify = this;
			setTimeout(function(){
				notify.close();
			}, 5000)
		}
		notify.onclick = function() {
			window.focus();
			this.close();
		}
		this.BXIM.notifyManager.nativeNotify(notify)
		}
};

BX.IM.WebRTC.prototype.phoneIncomingWaitDesktop = function(chatId, callId, callerId, companyPhoneNumber, phonePortalCall)
{
	this.BXIM.ppServerStatus = true;
	if (!this.callSupport() || !BX.MessengerCommon.isDesktop())
		return false;

	if (!phonePortalCall)
	{
		this.phoneNumberUser = BX.util.htmlspecialchars(this.phoneNumberUser);
		callerId = callerId.replace(/[^a-zA-Z0-9\.]/g, '');
	}

	if (!this.callActive && !this.callInit)
	{
		this.initiator = true;
		this.callInitUserId = 0;
		this.callInit = true;
		this.callActive = false;
		this.callUserId = 0;
		this.callChatId = 0;
		this.callToGroup = 0;
		this.callGroupUsers = [];
		this.phoneIncoming = true;
		this.phoneCallId = callId;
		this.phoneNumber = callerId;
		this.phoneParams = {};

		this.callOverlayShow({
			prepare : true,
			toUserId : this.BXIM.userId,
			phoneNumber : this.phoneNumber,
			companyPhoneNumber : companyPhoneNumber,
			callTitle : this.phoneNumberUser,
			fromUserId : 0,
			callToGroup : false,
			callToPhone : true,
			video : false,
			status : BX.message('IM_PHONE_INVITE'),
			buttons : [
				{
					text: BX.message('IM_PHONE_BTN_ANSWER'),
					className: 'bx-messenger-call-overlay-button-answer',
					events: {
						click : BX.delegate(function() {
							BX.desktop.onCustomEvent("main", "bxPhoneAnswer", [chatId, callId, callerId]);
							BX.desktop.windowCommand('close');
						}, this)
					}
				},
				{
					text: BX.message('IM_PHONE_BTN_BUSY'),
					className: 'bx-messenger-call-overlay-button-hangup',
					events: {
						click : BX.delegate(function() {
							BX.desktop.onCustomEvent("main", "bxPhoneSkip", []);
							BX.desktop.windowCommand('close');
						}, this)
					}
				}
			]
		});
		this.callOverlayDrawCrm();

		this.desktop.drawOnPlaceholder(this.callOverlay);

		if (this.phoneCrm && this.phoneCrm.FOUND)
			BX.desktop.setWindowPosition({X:STP_CENTER, Y:STP_VCENTER, Width: 609, Height: 453});
		else
			BX.desktop.setWindowPosition({X:STP_CENTER, Y:STP_VCENTER, Width: 470, Height: 120});
	}
};

BX.IM.WebRTC.prototype.sendInviteTransfer = function()
{
	if (!this.phoneCurrentCall && this.phoneCallDevice == 'WEBRTC')
		return false;

	if (this.phoneTransferUser <= 0)
		return false;

	if (this.popupTransferDialog)
		this.popupTransferDialog.close();

	this.phoneTransferEnabled = true;
	BX.localStorage.set('vite', true, 1);

	this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_TRANSFER'));
	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.transferring);

	if (this.phoneCallDevice == 'WEBRTC')
	{
		this.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'hold'}));
	}
	else
	{
		BX.MessengerCommon.phoneCommand('hold', {'CALL_ID': this.phoneCallId});
	}
	BX.MessengerCommon.phoneCommand(
		'inviteTransfer',
		{
			'CALL_ID' : this.phoneCallId,
			'USER_ID': this.phoneTransferUser,
			'TRANSFER_TYPE': this.phoneTransferType,
			'TRANSFER_PHONE': this.phoneTransferPhone
		}
	);
};

BX.IM.WebRTC.prototype.cancelInviteTransfer = function()
{
	if (!this.phoneCurrentCall && this.phoneCallDevice == 'WEBRTC')
		return false;

	this.phoneTransferUser = 0;
	this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_ONLINE'));
	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.connected);

	if (this.phoneCallDevice == 'WEBRTC')
	{
		this.phoneCurrentCall.sendMessage(JSON.stringify({'COMMAND': 'unhold'}));
	}
	else
	{
		BX.MessengerCommon.phoneCommand('unhold', {'CALL_ID': this.phoneCallId});
	}

	if (this.phoneTransferEnabled)
		BX.MessengerCommon.phoneCommand('cancelTransfer', {'CALL_ID' : this.phoneCallId});

	this.phoneTransferEnabled = false;
	BX.localStorage.set('vite', false, 1);
}

BX.IM.WebRTC.prototype.errorInviteTransfer = function()
{
	if (!this.phoneTransferEnabled)
		return false;

	this.phoneCallView.setStatusText(BX.message('IM_M_CALL_ST_TRANSFER_1'));
	this.BXIM.playSound('error', true);

	this.phoneTransferUser = 0;
	this.phoneTransferEnabled = false;
	BX.localStorage.set('vite', false, 1);
}

BX.IM.WebRTC.prototype.successInviteTransfer = function()
{
	if (!this.phoneTransferEnabled)
		return false;

	this.phoneTransferUser = 0;
	this.phoneTransferEnabled = false;
	BX.localStorage.set('vite', false, 1);

	if (this.phoneCallDevice == 'PHONE')
	{
		this.callInit = false;
		this.phoneCallFinish();
		this.callOverlayDeleteEvents();
		this.BXIM.playSound('stop');
		this.phoneCallView.autoClose();
	}
}

BX.IM.WebRTC.prototype.startMicTest = function ()
{
	var buttonRecord, buttonPlay, buttonExit, statusLine, selfVideo, outputVideo;
	var recorder;
	var constraints = {audio: {deviceId: {ideal: this.defaultMicrophone}}, video: {deviceId: {ideal: this.defaultCamera}}};
	var recordBlob;
	var state = 'waiting';
	var self = this;
	var chunks = [];

	var layout = BX.create('div', {props: {className: 'bx-messenger-mic-test'}, children: [
		BX.create('div', {props: {className: 'bx-messenger-mic-test-videos'}, children: [
			BX.create('div', {props: {className: 'bx-messenger-mic-test-video-wrap'}, children: [
				selfVideo = BX.create('video', {props: {className: 'bx-messenger-mic-test-video-self'}}),
			]}),
			BX.create('div', {props: {className: 'bx-messenger-mic-test-video-wrap'}, children: [
				outputVideo = BX.create('video', {props: {className: 'bx-messenger-mic-test-video-self'}, events: {
					'ended': function()
					{
						state = 'idle';
						buttonPlay.innerText = BX.message('IM_CALL_MIC_TEST_PLAY_START');
						buttonRecord.disabled = false;
					}
				}})
			]})
		]}),
		BX.create('div', {props: {className: 'bx-messenger-mic-test-buttons'}, children: [
			buttonRecord = BX.create('button', {text: BX.message('IM_CALL_MIC_TEST_RECORD_START'), events: {
				'click': function()
				{
					if(state == 'idle')
					{
						recorder = new MediaRecorder(self.micTestVideoStream, {mimeType: 'video/webm; codecs=vp9'});
						recorder.start();
						recorder.ondataavailable = function(e)
						{
							chunks.push(e.data);
						}
						recorder.onstop = function()
						{
							recordBlob = new Blob(chunks, {'type': 'video/webm'});
							outputVideo.src = URL.createObjectURL(recordBlob);
							state = 'idle';
							buttonPlay.disabled = false;
							buttonRecord.innerText = BX.message('IM_CALL_MIC_TEST_RECORD_START')
						}
						outputVideo.src = null;
						buttonRecord.innerText = BX.message('IM_CALL_MIC_TEST_RECORD_STOP')
						buttonPlay.disabled = true;
						state = 'recording';
					}
					else if (state == 'recording')
					{
						recorder.stop();

					}
					else if (state == 'playing')
					{

					}

				}
			}}),
			buttonPlay = BX.create('button', {text: BX.message('IM_CALL_MIC_TEST_PLAY_START'), events: {
				'click': function()
				{
					if(state == 'idle')
					{
						outputVideo.play();
						state = 'playing';
						buttonPlay.innerText = BX.message('IM_CALL_MIC_TEST_PLAY_STOP');
						buttonRecord.disabled = true;
					}
					else if(state == 'playing')
					{
						outputVideo.pause();
						state = 'idle';
						buttonPlay.innerText = BX.message('IM_CALL_MIC_TEST_PLAY_START');
						buttonRecord.disabled = false;
					}
				}
			}}),
			buttonExit = BX.create('button', {text: BX.message('IM_CALL_MIC_TEST_CLOSE'), events: {
				'click': function()
				{
					BX.webrtc.stopMediaStream(self.micTestVideoStream);
					self.micTestVideoStream = null;
					BX.remove(layout);
				}
			}})
		]}),
		statusLine = BX.create('div', {props: {className: 'bx-messenger-mic-test-button-exit'}}),
	]});
	this.messenger.popupMessengerContent.insertBefore(layout, this.messenger.popupMessengerContent.firstChild);

	selfVideo.volume = 0;
	buttonRecord.disabled = true;
	buttonPlay.disabled = true;
	navigator.mediaDevices.getUserMedia(constraints).then(function(stream)
	{
		self.micTestVideoStream = stream;
		selfVideo.src = URL.createObjectURL(self.micTestVideoStream );
		selfVideo.play();
		state = 'idle';
		buttonRecord.disabled = false;
	})
}

BX.IM.WebRTC.prototype.showExternalCall = function(params)
{
	var self = this;
	var direction;
	if (this.phoneCallView)
		return;

	setTimeout(function() {
		BX.localStorage.set('viExternalCard', true, 5);
	}, 100);

	clearInterval(this.phoneConnectedInterval);
	this.phoneConnectedInterval = setInterval(function(){
		if(self.phoneCallExternal)
		{
			BX.localStorage.set('viExternalCard', true, 5);
		}
	}, 5000);

	this.phoneCallId = params.callId;
	this.callActive = true;
	this.phoneCallExternal = true;

	if(params.isCallback)
		direction = BX.PhoneCallView.Direction.callback;
	else if(params.fromUserId > 0)
		direction = BX.PhoneCallView.Direction.outgoing;
	else
		direction = BX.PhoneCallView.Direction.incoming;

	this.phoneCallView = new BX.PhoneCallView({
		BXIM: this.BXIM,
		callId: params.callId,
		direction: direction,
		phoneNumber: params.phoneNumber,
		lineNumber: params.lineNumber,
		companyPhoneNumber: params.companyPhoneNumber,
		fromUserId: params.fromUserId,
		toUserId: params.toUserId,
		crm: params.showCrmCard,
		crmEntityType: params.crmEntityType,
		crmEntityId: params.crmEntityId,
		crmActivityId: params.crmActivityId,
		crmActivityEditUrl: params.crmActivityEditUrl,
		crmData: this.phoneCrm
	});
	this.bindPhoneViewCallbacksExternalCall(this.phoneCallView);
	this.phoneCallView.setUiState(BX.PhoneCallView.UiState.externalCard);
	this.phoneCallView.setCallState(BX.PhoneCallView.CallState.connected);
	this.phoneCallView.setConfig(params.config);
	this.phoneCallView.show();

	if(params.portalCall)
	{
		this.phoneCallView.setPortalCall(true);
		this.phoneCallView.setPortalCallData(params.portalCallData);
		this.phoneCallView.setPortalCallUserId(params.portalCallUserId);
	}
};

BX.IM.WebRTC.prototype.bindPhoneViewCallbacksExternalCall = function(phoneCallView)
{
	phoneCallView.setCallback('close', function()
	{
		if(this.phoneCallView)
		{
			this.phoneCallView.dispose();
			this.phoneCallView = null;
		}

		this.phoneCallId = '';
		this.callActive = false;
		this.phoneCallExternal = false;
		this.callSelfDisabled = false;
		clearInterval(this.BXIM.webrtc.phoneConnectedInterval);
		BX.localStorage.set('viExternalCard', false);
	}.bind(this));

};

BX.IM.WebRTC.prototype.hideExternalCall = function(clearFlag)
{
	if (this.phoneCallView)
	{
		this.phoneCallView.autoClose();
	}
}

BX.IM.WebRTC.prototype.phoneLog = function()
{
	if (BX.MessengerCommon.isDesktop())
	{
		var text = '';
		for (var i = 0; i < arguments.length; i++)
		{
			if(BX.type.isPlainObject(arguments[i]))
			{
				try
				{
					text = text + ' | ' + JSON.stringify(arguments[i]);
				}
				catch (e)
				{
					text = text + ' | (circular structure)';
				}
			}
			else
			{
				text = text + ' | ' + arguments[i];
			}
		}
		BX.desktop.log('phone.'+this.BXIM.userEmail+'.log', text.substr(3));
	}
	if (this.debug)
	{
		if (console)
		{
			try
			{
				console.log('Phone Log', JSON.stringify(arguments));
			}
			catch (e)
			{
				console.log('Phone Log', arguments[0]);
			}

		}
	}
};

BX.IM.WebRTC.prototype.startCallExperimental = function(roomId)
{
	var users = [];
	if (this.BXIM.messenger.openChatFlag)
	{
		users = this.BXIM.messenger.userInChat[this.BXIM.messenger.currentTab.substr(4)];
	}
	else
	{
		users.push(this.BXIM.messenger.currentTab)
	}

	var userDetails = {};

	users.forEach(function(user)
	{
		userDetails[user] = this.BXIM.messenger.users[user];
	})

	this.callView = new CallView({
		roomId: roomId,
		userId: this.BXIM.userId,
		userDetails: userDetails,
		server: 'https://testphone.bitrix.info:8089/janus',
		apiSecret: 'eaff35df4cf027ffb36300d9b9604d8f',
		//server: 'https://cp.perevozov.bx:8089/janus',
		//apiSecret: 'bitrix'
	});
};

BX.IM.ScreenSharing = function(webrtc, params)
{
	if (this.parent)
	{
		this.parent.constructor.apply(this, arguments);
	}
	params = params || {};

	this.webrtc = webrtc;
	this.BXIM = this.webrtc.BXIM;

	this.debug = true;

	this.sdpConstraints = {'mandatory': { 'OfferToReceiveAudio':false, 'OfferToReceiveVideo': true }};

	this.oneway = true;
	this.sourceSelf = null;
	this.sourceOpponent = null;

	this.callWindowBeforeUnload = null;

	BX.addCustomEvent("onImCallEnd", BX.delegate(function(command,params)
	{
		this.callDecline(false);
	}, this));

	BX.addCustomEvent("onPullEvent-im", BX.delegate(function(command,params)
	{
		if (command == 'screenSharing')
		{
			if (params.command == 'inactive')
			{
				this.callDecline(false);
			}
			else if (!this.webrtc.callActive || this.webrtc.callUserId != params.senderId)
			{
				this.callCommand('inactive');
			}
			else
			{
				this.log('Incoming', params.command, params.senderId, JSON.stringify(params));

				if (params.command == 'invite')
				{
					if (this.callInit)
					{
						this.deleteEvents();
					}

					this.initiator = false;
					this.callVideo = true;
					this.callInit = true;
					this.callUserId = params.senderId;
					this.callInitUserId = params.senderId;
					this.callAnswer()
				}
				else if (params.command == 'answer' && this.initiator)
				{
					this.startScreenSharing();
				}
				else if (params.command == 'decline')
				{
					this.callDecline();
				}
				else if (params.command == 'ready')
				{
					this.log('Opponent '+params.senderId+' ready!');
					this.connected[params.senderId] = true;
				}
				else if (params.command == 'reconnect')
				{
					clearTimeout(this.pcConnectTimeout[params.senderId]);
					clearTimeout(this.initPeerConnectionTimeout[params.senderId]);

					if (this.pc[params.senderId])
						this.pc[params.senderId].close();

					delete this.pc[params.senderId];
					delete this.pcStart[params.senderId];

					if (this.callStreamMain == this.callStreamUsers[params.senderId])
						this.callStreamMain = null;
					this.callStreamUsers[params.senderId] = null;

					this.initPeerConnection(params.senderId);
				}
				else if (params.command == 'signaling' && this.callActive)
				{
					this.signalingPeerData(params.senderId, params.peer);
				}
				else
				{
					this.log('Command "'+params.command+'" skip');
				}
			}
		}
	}, this));

	BX.garbage(function(){
		if (this.callInit)
		{
			this.callCommand('decline', true);
		}
	}, this);
};
if (BX.inheritWebrtc)
	BX.inheritWebrtc(BX.IM.ScreenSharing);

BX.IM.ScreenSharing.prototype.startScreenSharing = function()
{
	var options = {
		mandatory:
		{
			chromeMediaSource : 'screen',
			googLeakyBucket : true,
			maxWidth : window.screen.width,
			maxHeight : window.screen.height,
			maxFrameRate : 5
		}
	};

	this.startGetUserMedia(options, false);
};

BX.IM.ScreenSharing.prototype.onUserMediaSuccess = function(stream)
{
	var result = this.parent.onUserMediaSuccess.apply(this, arguments);
	if (!result)
		return false;

	if (this.initiator)
	{
		BX.addClass(this.webrtc.callOverlay, 'bx-messenger-call-overlay-screen-sharing-self');
		this.attachMediaStream(this.webrtc.callOverlayVideoSelf, this.callStreamSelf);
	}

	this.callCommand('ready');

	return true;
};

BX.IM.ScreenSharing.prototype.onUserMediaError = function(error)
{
	var result = this.parent.onUserMediaError.apply(this, arguments);
	if (!result)
		return false;

	this.callDecline();

	return true;
}

BX.IM.ScreenSharing.prototype.setLocalAndSend = function(userId, desc)
{
	var result = this.parent.setLocalAndSend.apply(this, arguments);
	if (!result)
		return false;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_SIGNALING',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SHARING' : 'Y', 'COMMAND': 'signaling', 'USER_ID' : userId, 'PEER': JSON.stringify( desc ), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()}
	});

	return true;
}

BX.IM.ScreenSharing.prototype.onRemoteStreamAdded = function (userId, event, setMainVideo)
{
	if (!setMainVideo)
		return false;

	BX.addClass(this.webrtc.callOverlay, 'bx-messenger-call-overlay-screen-sharing');
	this.attachMediaStream(this.webrtc.callOverlayVideoReserve, this.webrtc.callStreamMain);
	this.webrtc.callOverlayVideoReserve.play();
	this.attachMediaStream(this.webrtc.callOverlayVideoMain, this.callStreamMain);
	this.webrtc.callOverlayVideoMain.play();

	return true;
}

BX.IM.ScreenSharing.prototype.onRemoteStreamRemoved = function(userId, event)
{
}

BX.IM.ScreenSharing.prototype.onIceCandidate = function (userId, candidates)
{
	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_SIGNALING',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SHARING' : 'Y', 'COMMAND': 'signaling', 'USER_ID' : userId, 'PEER': JSON.stringify(candidates), 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()}
	});
}

BX.IM.ScreenSharing.prototype.peerConnectionError = function (userId, event)
{
	this.callDecline();
}

BX.IM.ScreenSharing.prototype.peerConnectionReconnect = function (userId)
{
	var result = this.parent.peerConnectionReconnect.apply(this, arguments);
	if (!result)
		return false;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_RECONNECT',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		data: {'IM_SHARING' : 'Y', 'COMMAND': 'reconnect', 'USER_ID' : userId, 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(){
			this.initPeerConnection(userId, true);
		}, this)
	});

	return true;
}

BX.IM.ScreenSharing.prototype.deleteEvents = function ()
{
	BX.removeClass(this.webrtc.callOverlay, 'bx-messenger-call-overlay-screen-sharing-self');
	BX.removeClass(this.webrtc.callOverlay, 'bx-messenger-call-overlay-screen-sharing');
	this.webrtc.callOverlayVideoReserve.src = "";
	this.attachMediaStream(this.webrtc.callOverlayVideoSelf, this.webrtc.callStreamSelf);
	this.attachMediaStream(this.webrtc.callOverlayVideoMain, this.webrtc.callStreamMain);
	this.webrtc.callOverlayVideoMain.play();
	this.webrtc.callOverlayVideoSelf.play();

	this.parent.deleteEvents.apply(this, arguments);

	var icon = BX.findChildByClassName(BX('bx-messenger-call-overlay-button-screen'), "bx-messenger-call-overlay-button-screen");
	if (icon)
		BX.removeClass(icon, 'bx-messenger-call-overlay-button-screen-off');

	return true;
}

BX.IM.ScreenSharing.prototype.callInvite = function ()
{
	if (this.callInit)
	{
		this.deleteEvents();
	}

	this.initiator = true;
	this.callVideo = true;

	this.callInit = true;
	this.callActive = true;

	this.callUserId = this.webrtc.callUserId;
	this.callInitUserId = BXIM.userId;
	this.callCommand('invite');

	var icon = BX.findChildByClassName(BX('bx-messenger-call-overlay-button-screen'), "bx-messenger-call-overlay-button-screen");
	if (icon)
		BX.addClass(icon, 'bx-messenger-call-overlay-button-screen-off');
}

BX.IM.ScreenSharing.prototype.callAnswer = function ()
{
	this.callActive = true;
	this.startGetUserMedia();

	this.callCommand('answer');
}

BX.IM.ScreenSharing.prototype.callDecline = function (send)
{
	if (!this.callInit)
		return false;

	send = send !== false;
	if (send)
	{
		this.callCommand('decline');
	}

	this.deleteEvents();
}

BX.IM.ScreenSharing.prototype.callCommand = function(command, async)
{
	if (!this.signalingReady())
		return false;

	BX.ajax({
		url: this.BXIM.pathToCallAjax+'?CALL_COMMAND',
		method: 'POST',
		dataType: 'json',
		timeout: 30,
		async: async != false,
		data: {'IM_SHARING' : 'Y', 'COMMAND': command, 'USER_ID': this.callUserId, 'IM_AJAX_CALL': 'Y', 'sessid': BX.bitrix_sessid()}
	});
};

/* DiskManager */
BX.IM.DiskManager = function(BXIM, params)
{
	this.BXIM = BXIM;
	this.notify = params.notifyClass;
	this.desktop = params.desktopClass;

	this.enable = params.enable;
	this.enableExternal = params.enableExternal;
	this.lightVersion = BXIM.ieVersion == 8 || BXIM.ieVersion == 9;

	this.formBlocked = {};
	this.formAgents = {};

	this.files = params.files;
	for (var fileId in this.files)
	{
		this.files[fileId].date = new Date(this.files[fileId].date);
	}

	this.filesProgress = {};
	this.filesMessage = {};
	this.filesRegister = {};

	this.fileTmpId = 1;

	this.timeout = {};

	BX.garbage(function(){
		var messages = {};
		var chatId = 0;
		for (var tmpId in this.filesMessage)
		{
			messages[tmpId] = this.filesMessage[tmpId];
			if (this.messenger.message[messages[tmpId]])
			{
				chatId = this.messenger.message[messages[tmpId]].chatId;
			}
		}
		if (chatId > 0)
		{
			BX.ajax({
				url: this.BXIM.pathToFileAjax+'?FILE_TERMINATE&V='+this.BXIM.revision,
				method: 'POST',
				dataType: 'json',
				skipAuthCheck: true,
				timeout: 30,
				async: false,
				data: {'IM_FILE_UNREGISTER' : 'Y', CHAT_ID: chatId, FILES: JSON.stringify(this.filesProgress), MESSAGES: JSON.stringify(messages), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
			});
		}
	}, this);
};
BX.IM.DiskManager.prototype.getFileMenuIcon = function()
{
	if (!this.enable)
		return null;

	return BX.create("div", {attrs : { title: BX.message('IM_F_UPLOAD_MENU')}, props : { className : "bx-messenger-textarea-file"+(this.lightVersion? " bx-messenger-textarea-file-light": "") }, children : [
		BX.create("div", { attrs: {'title': this.BXIM.ieVersion > 1? BX.message('IM_F_UPLOAD_MENU'): ' '}, props : { className : "bx-messenger-textarea-file-popup"+(this.BXIM.context == "LINES"? " bx-messenger-textarea-file-popup-short": "") }, children : [
			this.messenger.popupMessengerFileForm = BX.create('form', { attrs : { action : this.BXIM.pathToFileAjax, style: this.lightVersion? "z-index: 0": ""}, props : { className : "bx-messenger-textarea-file-form" }, children: [
				BX.create('input', { attrs : { type : 'hidden', name: 'IM_FILE_UPLOAD', value: 'Y'}}),
				this.messenger.popupMessengerFileFormChatId = BX.create('input', { attrs : { type : 'hidden', name: 'CHAT_ID', value: 0}}),
				this.messenger.popupMessengerFileFormRegChatId = BX.create('input', { attrs : { type : 'hidden', name: 'REG_CHAT_ID', value: 0}}),
				this.messenger.popupMessengerFileFormRegMessageId = BX.create('input', { attrs : { type : 'hidden', name: 'REG_MESSAGE_ID', value: 0}}),
				this.messenger.popupMessengerFileFormRegParams = BX.create('input', { attrs : { type : 'hidden', name: 'REG_PARAMS', value: ''}}),
				this.messenger.popupMessengerFileFormRegMessageHidden = BX.create('input', { attrs : { type : 'hidden', name: 'REG_MESSAGE_HIDDEN', value: 'N'}}),
				BX.create('input', { attrs : { type : 'hidden', name: 'IM_AJAX_CALL', value: 'Y'}}),
				this.messenger.popupMessengerFileFormInput = BX.create('input', { attrs : { type : 'file',multiple : 'true', 'title': this.BXIM.ieVersion > 1? BX.message('IM_F_UPLOAD_MENU'): ' '}, props : { className : "bx-messenger-textarea-file-popup-input"}})
			]}),
			this.lightVersion? null: BX.create("div", { props : { className : "bx-messenger-popup-menu-item" }, html: (BX.browser.IsMobile()? BX.message('IM_F_UPLOAD_MENU_1_M'): BX.message('IM_F_UPLOAD_MENU_1'))}),
			this.lightVersion || this.BXIM.context == "LINES"? null: BX.create("div", { props : { className : "bx-messenger-menu-hr" }}),
			this.BXIM.context == "LINES"? null: BX.create("div", { props : { className : "bx-messenger-popup-menu-item" }, html: BX.message('IM_F_UPLOAD_MENU_2'), events:{
				click: BX.delegate(function(){
					this.openFileDialog();
				}, this)
			}}),
			BX.create("div", { props : { className : "bx-messenger-textarea-file-popup-arrow" }})
		]})
	], events: {
		click: BX.delegate(function(e){
			if (this.messenger.popupMessengerConnectionStatusState != 'online')
				return false;

			if (BX.hasClass(this.messenger.popupMessengerFileButton, 'bx-messenger-textarea-file-active'))
			{
				setTimeout(BX.delegate(function(){
					this.messenger.closePopupFileMenu();
				}, this), 100);
			}
			else
			{
				if (parseInt(this.messenger.popupMessengerFileFormChatId.value) <= 0)
				{
					return false;
				}

				if (this.messenger.popupMessengerFileFormInput.getAttribute('disabled'))
				{
					var showNotice = false;
					if (this.messenger.currentTab.toString().substr(0, 4) == 'chat')
					{
						var chatId = this.messenger.currentTab.toString().substr(4);
						if (this.messenger.chat[chatId] && !this.enableExternal && this.messenger.chat[chatId].type == 'lines' && this.messenger.chat[chatId].entity_id.substr(0,8) != 'livechat')
						{
							showNotice = true;
						}
					}
					else if (this.messenger.users[this.messenger.currentTab].bot || this.messenger.users[this.messenger.currentTab].network)
					{
						showNotice = true;
					}

					if (showNotice)
					{
						if (this.BXIM.isAdmin)
						{
							var enableNode = BX.create('div', {children: [
								BX.create('span', {html: BX.message('IM_D_SEND_PUBLIC_ADMIN')+'&nbsp;&nbsp;'}),
								BX.create('span', {props: {className: 'bx-messenger-ajax'}, html: BX.message('IM_D_SEND_PUBLIC_ADMIN_ON'), events: {'click': BX.delegate(function(){
									this.messenger.closeMenuPopup();

									BX.ajax({
										url: this.BXIM.pathToAjax+'?IM_DISK_ACTIVATE_PUBLIC_LINK&V='+this.BXIM.revision,
										method: 'POST',
										dataType: 'json',
										timeout: 30,
										data: {'IM_DISK_ACTIVATE_PUBLIC_LINK' : 'Y', 'STATUS' : 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
										onsuccess: BX.delegate(function(data){
											this.tooltip(this.messenger.popupMessengerFileButton, BX.message('IM_D_SEND_PUBLIC_ADMIN_READY'), {offsetLeft: 10});

											this.enableExternal = true;
											this.messenger.popupMessengerFileFormInput.removeAttribute('disabled');
										},this)
									});

								}, this)}}),
							]});

							this.messenger.tooltip(BX.proxy_context, enableNode, {offsetLeft: 10, width: 400});
						}
						else
						{
							this.messenger.tooltip(BX.proxy_context, BX.message('IM_D_SEND_PUBLIC_USER'), {offsetLeft: 10, width: 400});
						}
						return BX.PreventDefault(e);
					}
					else
					{
						return BX.PreventDefault(e);
					}
				}

				this.messenger.closeMenuPopup();
				this.messenger.popupPopupMenuDateCreate = +new Date();
				BX.addClass(this.messenger.popupMessengerFileButton, 'bx-messenger-textarea-file-active');
				if (BX.MessengerCommon.isPage())
				{
					BX.addClass(this.messenger.popupMessengerFileButton, 'bx-messenger-textarea-file-desktop');
				}
				this.messenger.setClosingByEsc(false);
			}
		}, this)
	}});
}

BX.IM.DiskManager.prototype.drawHistoryFiles = function(chatId, fileId, params)
{
	if (!this.enable)
		return [];

	if (typeof(this.files[chatId]) == 'undefined')
		return [];

	var fileIds = [];
	if (typeof(fileId) != 'object')
	{
		fileId = parseInt(fileId);
		if (typeof(this.files[chatId][fileId]) == 'undefined')
			return [];

		fileIds.push(fileId);
	}
	else
	{
		fileIds = fileId;
	}
	params = params || {};

	var enableLink = true;
	//if (!BX.MessengerCommon.isDesktop())
	//	enableLink = false;

	var nodeCollection = [];
	for (var i = 0; i < fileIds.length; i++)
	{
		var file = this.files[chatId][fileIds[i]];
		if (!file)
			continue;

		if (!(file.status == 'done' || file.status == 'error'))
			continue;

		var fileDate = BX.MessengerCommon.formatDate(file.date, [
			["tommorow", "tommorow"],
			["today", "today"],
			["yesterday", "yesterday"],
			["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"))]
		]);
		var name = BX.create("span", { props : { className: "bx-messenger-file-user"}, children: [
			BX.create("span", { props : { className: "bx-messenger-file-author"}, html: this.messenger.users[file.authorId]? this.messenger.users[file.authorId].name: file.authorName}),
			BX.create("span", { props : { className: "bx-messenger-file-date"}, html: fileDate})
		]});

		var preview = null;
		if (file.type == 'image' && (file.preview || file.urlPreview))
		{
			if (file.urlPreview)
			{
				var imageNode = BX.create("img", { attrs:{'src': file.urlPreview}, props : { className: "bx-messenger-file-image-text"}});
			}
			else if (file.preview && typeof(file.preview) != 'string')
			{
				var imageNode = file.preview;
			}
			else
			{
				var imageNode = BX.create("img", { attrs:{'src': file.preview}, props : { className: "bx-messenger-file-image-text"}});
			}

			if (enableLink && file.urlShow)
			{
				preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
					BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
						BX.create("a", {attrs: {'href': file.urlShow, 'target': '_blank'}, props : { className: "bx-messenger-file-image-src"},  children: [
							imageNode
						]})
					]}),
					BX.create("br")
				]});
			}
			else
			{
				preview = BX.create("div", {props : { className: "bx-messenger-file-preview"},  children: [
					BX.create("span", {props : { className: "bx-messenger-file-image"},  children: [
						BX.create("span", {props : { className: "bx-messenger-file-image-src"},  children: [
							imageNode
						]})
					]}),
					BX.create("br")
				]});
			}
		}
		var fileName = file.name;
		if (fileName.length > 23)
		{
			fileName = fileName.substr(0, 10)+'...'+fileName.substr(fileName.length-10, fileName.length);
		}

		var title = BX.create("span", { attrs: {'title': file.name}, props : { className: "bx-messenger-file-title"}, html: fileName});
		if (enableLink && (file.urlShow || file.urlDownload))
		{
			title = BX.create("a", { props : { className: "bx-messenger-file-title-href"}, attrs: {'href': file.urlShow? file.urlShow: file.urlDownload, 'target': '_blank'}, children: [title]});
		}
		title = BX.create("div", { props : { className: "bx-messenger-file-attrs"}, children: [
			title,
			BX.create("span", { props : { className: "bx-messenger-file-size"}, html: BX.UploaderUtils.getFormattedSize(file.size)}),
			BX.create("span", { attrs: { title: BX.message('IM_F_MENU')}, props : { className: "bx-messenger-file-menu"}})
		]});

		var status = null;
		if (file.status == 'error')
		{
			status = BX.create("span", { props : { className: "bx-messenger-file-status-error"}, html: file.errorText? file.errorText: BX.message('IM_F_ERROR')})
		}

		if (fileIds.length == 1 && params.showInner == 'Y')
		{
			nodeCollection = [name, title, preview, status];
		}
		else
		{
			nodeCollection.push(BX.create("div", {
				attrs : {id : 'im-file-history-panel-' + file.id, 'data-chatId' : file.chatId, 'data-fileId' : file.id},
				props : {className : "bx-messenger-file"},
				children : [name, title, preview, status]
			}));
		}
		if (fileIds.length == 1 && params.getElement == 'Y')
		{
			nodeCollection = nodeCollection[0];
		}
	}

	return nodeCollection
}
BX.IM.DiskManager.prototype.chatDialogInit = function()
{
	if (!this.messenger.popupMessengerFileFormInput || !BX.Uploader)
		return false;

	this.formAgents['imDialog'] = BX.Uploader.getInstance({
		id : 'imDialog',
		allowUpload : "A",
		uploadMethod : "deferred",
		showImage : true,
		filesInputMultiple: true,
		input : this.messenger.popupMessengerFileFormInput,
		dropZone : this.messenger.popupMessengerBodyDialog,
		fields: {preview: {params: {width: '500', height: '500'}}}
	});

	BX.addCustomEvent(this.formAgents['imDialog'], 'onAttachFiles', BX.delegate(function(files, nodes, agent){
		if (this.messenger.popupMessengerFileFormInput.getAttribute('disabled'))
			return false;

		var chatId = agent.form.CHAT_ID.value;
		if (this.messenger.chat[chatId] && this.messenger.chat[chatId].type == 'open' && !BX.MessengerCommon.userInChat(chatId))
		{
			while (files.length > 0)
			{
			   files.pop();
			}
		}
		else if (this.messenger.chat[chatId] && chatId == this.messenger.generalChatId && !this.messenger.canSendMessageGeneralChat)
		{
			while (files.length > 0)
			{
			   files.pop();
			}
		}
	}, this));

	BX.addCustomEvent(this.formAgents['imDialog'].dropZone, 'dragEnter', BX.delegate(function(e){
		if (this.messenger.currentTab.toString().substr(0, 4) == 'chat' && this.messenger.chat[this.BXIM.messenger.currentTab.substr(4)].type == 'open')
		{
			if (!BX.MessengerCommon.userInChat(this.messenger.currentTab.substr(4)))
				return false;
		}
		if (this.messenger.currentTab.toString().substr(0, 4) == 'chat' && this.messenger.currentTab.toString().substr(4) == this.messenger.generalChatId && !this.messenger.canSendMessageGeneralChat)
		{
			return false;
		}

		if (parseInt(this.messenger.popupMessengerFileFormChatId.value) <= 0 || this.messenger.popupMessengerFileFormInput.getAttribute('disabled'))
			return false;

		var isFileTransfer = false;

		if (e && e["dataTransfer"] && e["dataTransfer"]["types"])
		{
			for (var i in e["dataTransfer"]["types"])
			{
				if (e["dataTransfer"]["types"][i] === "Files")
				{
					isFileTransfer = true;
					break;
				}
			}
		}
		if (isFileTransfer === false)
			return false;

		BX.style(this.messenger.popupMessengerFileDropZone, 'display', 'block');
		BX.style(this.messenger.popupMessengerFileDropZone, 'width', (this.messenger.popupMessengerBodyDialog.offsetWidth-2)+'px');
		BX.style(this.messenger.popupMessengerFileDropZone, 'height', (this.messenger.popupMessengerBodyDialog.offsetHeight-2)+'px');
		clearTimeout(this.messenger.popupMessengerFileDropZoneTimeout);
		this.messenger.popupMessengerFileDropZoneTimeout = setTimeout(BX.delegate(function(){
			BX.addClass(this.messenger.popupMessengerFileDropZone, "bx-messenger-file-dropzone-active");
		},this), 10);
	}, this));

	BX.addCustomEvent(this.formAgents['imDialog'].dropZone, 'dragLeave', BX.delegate(function(){
		if (this.messenger.currentTab.toString().substr(0, 4) == 'chat' && this.messenger.chat[this.messenger.currentTab.substr(4)].type == 'open')
		{
			if (!BX.MessengerCommon.userInChat(this.messenger.currentTab.substr(4)))
				return false;
		}

		BX.removeClass(this.messenger.popupMessengerFileDropZone, "bx-messenger-file-dropzone-active");
		clearTimeout(this.messenger.popupMessengerFileDropZoneTimeout);
		this.messenger.popupMessengerFileDropZoneTimeout = setTimeout(BX.delegate(function(){
			BX.style(this.messenger.popupMessengerFileDropZone, 'display', 'none');
			BX.style(this.messenger.popupMessengerFileDropZone, 'width', 0);
			BX.style(this.messenger.popupMessengerFileDropZone, 'height', 0);
		}, this), 300);
	}, this));

	BX.addCustomEvent(this.formAgents['imDialog'], "onError", BX.delegate(BX.MessengerCommon.diskChatDialogUploadError, BX.MessengerCommon));

	BX.addCustomEvent(this.formAgents['imDialog'], "onFileinputIsReinited", BX.delegate(function(fileInput){
		if (!fileInput && !this.formAgents['imDialog'].fileInput)
			return false;

		this.messenger.popupMessengerFileFormInput = fileInput? fileInput: this.formAgents['imDialog'].fileInput;
		if (parseInt(this.messenger.popupMessengerFileFormChatId.value) <= 0)
		{
			this.messenger.popupMessengerFileFormInput.setAttribute('disabled', true);
		}
	}, this));

	BX.addCustomEvent(this.formAgents['imDialog'], "onFileIsInited", BX.delegate(function(id, file, agent){
		BX.MessengerCommon.diskChatDialogFileInited(id, file, agent);
		BX.addCustomEvent(file, 'onUploadStart', BX.delegate(BX.MessengerCommon.diskChatDialogFileStart, BX.MessengerCommon));
		BX.addCustomEvent(file, 'onUploadProgress', BX.delegate(BX.MessengerCommon.diskChatDialogFileProgress, BX.MessengerCommon));
		BX.addCustomEvent(file, 'onUploadDone', BX.delegate(BX.MessengerCommon.diskChatDialogFileDone, BX.MessengerCommon))
		BX.addCustomEvent(file, 'onUploadError', BX.delegate(BX.MessengerCommon.diskChatDialogFileError, BX.MessengerCommon));
	}, this));

	if (BX.DiskFileDialog)
	{
		if (!this.flagFileDialogInited)
		{
			BX.addCustomEvent(BX.DiskFileDialog, 'inited', BX.proxy(this.initEventFileDialog, this));
		}

		BX.addCustomEvent(BX.DiskFileDialog, 'loadItems', BX.delegate(function(link, name)
		{
			if (name != 'im-file-dialog')
				return false;

			BX.DiskFileDialog.target[name] = link.replace('/bitrix/tools/disk/uf.php', this.BXIM.pathToFileAjax);

		}, this));
	}
};

BX.IM.DiskManager.prototype.saveToDisk = function(chatId, fileId, params)
{
	if (!this.files[chatId] || !this.files[chatId][fileId])
		return false;

	if (this.files[chatId][fileId].saveToDiskBlock)
		return false;

	params = params || {};

	this.files[chatId][fileId].saveToDiskBlock = true;

	var boxId = params.boxId? params.boxId: 'im-file';

	var fileBox = BX(boxId+'-'+fileId);
	var element = BX.findChildByClassName(fileBox, "bx-messenger-file-download-disk");
	if (element)
	{
		BX.addClass(element, 'bx-messenger-file-download-block');
		element.innerHTML = BX.message('IM_SAVING');
	}
	else if (boxId == 'im-file-history-panel')
	{
		element = BX.findChildByClassName(fileBox, "bx-messenger-file-date");
		if (element)
		{
			BX.addClass(element.parentNode.parentNode, 'bx-messenger-file-download-block');
			element.setAttribute('data-date', element.innerHTML);
			element.innerHTML = BX.message('IM_SAVING');
		}
	}

	BX.ajax({
		url: this.BXIM.pathToFileAjax+'?FILE_SAVE_TO_DISK&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		skipAuthCheck: true,
		timeout: 30,
		data: {'IM_FILE_SAVE_TO_DISK' : 'Y', CHAT_ID: chatId, FILE_ID: fileId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			this.files[chatId][fileId].saveToDiskBlock = false;

			var fileBox = BX(boxId+'-'+fileId);
			var element = BX.findChildByClassName(fileBox, "bx-messenger-file-download-disk");
			if (element)
			{
				BX.removeClass(element, 'bx-messenger-file-download-block');
				element.innerHTML = BX.message('IM_F_DOWNLOAD_DISK');

			}
			else if (boxId == 'im-file-history-panel')
			{
				element = BX.findChildByClassName(fileBox, "bx-messenger-file-date");
				if (element)
				{
					BX.removeClass(element.parentNode.parentNode, 'bx-messenger-file-download-block');
					element.innerHTML = element.getAttribute('data-date');
				}
				element = BX.findChildByClassName(fileBox, "bx-messenger-file-title");
			}
			if (element && data.ERROR == '')
			{
				this.messenger.tooltip(element, BX.message('IM_F_SAVE_OK'));
			}
			else
			{
				this.messenger.tooltip(element, BX.message('IM_F_SAVE_ERR'));
			}
		}, this),
		onfailure: BX.delegate(function(){
			this.files[chatId][fileId].saveToDiskBlock = false;
			var fileBox = BX(boxId+'-'+fileId);
			var element = BX.findChildByClassName(fileBox, "bx-messenger-file-download-disk");
			if (element)
			{
				BX.removeClass(element, 'bx-messenger-file-download-block');
				element.innerHTML = BX.message('IM_F_DOWNLOAD_DISK');
				this.messenger.tooltip(element, BX.message('IM_F_SAVE_ERR'));
			}
			else if (boxId == 'im-file-history-panel')
			{
				element = BX.findChildByClassName(fileBox, "bx-messenger-file-date");
				if (element)
				{
					BX.removeClass(element.parentNode.parentNode, 'bx-messenger-file-download-block');
					element.innerHTML = element.getAttribute('data-date');
				}
			}
		}, this)
	});
}

BX.IM.DiskManager.prototype.deleteFile = function(chatId, fileId, params)
{
	if (!this.files[chatId] || !this.files[chatId][fileId])
		return false;

	if (this.files[chatId][fileId].saveToDiskBlock)
		return false;

	params = params || {};

	this.files[chatId][fileId].saveToDiskBlock = true;

	var boxId = params.boxId? params.boxId: 'im-file';

	var fileBox = BX(boxId+'-'+fileId);
	var element = BX.findChildByClassName(fileBox, "bx-messenger-file-download-disk");
	if (element)
	{
		BX.addClass(element, 'bx-messenger-file-download-block');
		element.innerHTML = BX.message('IM_DELETING');
	}
	else if (boxId == 'im-file-history-panel')
	{
		element = BX.findChildByClassName(fileBox, "bx-messenger-file-date");
		if (element)
		{
			BX.addClass(element.parentNode.parentNode, 'bx-messenger-file-download-block');
			element.setAttribute('data-date', element.innerHTML);
			element.innerHTML = BX.message('IM_DELETING');
		}
	}

	BX.ajax({
		url: this.BXIM.pathToFileAjax+'?FILE_DELETE&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		skipAuthCheck: true,
		timeout: 30,
		data: {'IM_FILE_DELETE' : 'Y', CHAT_ID: chatId, FILE_ID: fileId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			delete this.files[chatId][fileId];

			var recipientId = BX.MessengerCommon.getRecipientByChatId(chatId);

			if (BX('im-file-history-'+fileId))
			{
				this.messenger.drawHistory(recipientId);
			}
			if (BX('im-file-'+fileId))
			{
				BX.MessengerCommon.drawTab(recipientId, true);
			}
			var fileBox = BX(boxId+'-'+fileId);
			BX.style(fileBox, 'transform','scale(0, 0)');
			BX.style(fileBox, 'height', fileBox.offsetHeight+'px');
			setTimeout(function(){ BX.style(fileBox, 'height', '0px');}, 500);
			setTimeout(function(){ BX.remove(fileBox) }, 700);

			this.messenger.loadHistoryFiles(chatId, true);

		}, this),
		onfailure: BX.delegate(function(){
			this.files[chatId][fileId].saveToDiskBlock = false;
			var fileBox = BX(boxId+'-'+fileId);
			var element = BX.findChildByClassName(fileBox, "bx-messenger-file-download-disk");
			if (element)
			{
				BX.removeClass(element, 'bx-messenger-file-download-block');
				element.innerHTML = BX.message('IM_F_DOWNLOAD_DISK');
				this.messenger.tooltip(element, BX.message('IM_F_SAVE_ERR'));
			}
			else if (boxId == 'im-file-history-panel')
			{
				element = BX.findChildByClassName(fileBox, "bx-messenger-file-date");
				if (element)
				{
					BX.removeClass(element.parentNode.parentNode, 'bx-messenger-file-download-block');
					element.innerHTML = element.getAttribute('data-date');
				}
			}
		}, this)
	});
}

BX.IM.DiskManager.prototype.openFileDialog = function()
{
	this.messenger.setClosingByEsc(false);

	BX.ajax({
		url: this.BXIM.pathToFileAjax+'?action=selectFile&dialogName=im-file-dialog',
		method: 'GET',
		skipAuthCheck: true,
		timeout: 30,
		onsuccess: BX.delegate(function(data) {
			if (typeof(data) == 'object' && data.error)
			{
				this.messenger.setClosingByEsc(true);
			}
		}, this),
		onfailure: BX.delegate(function(){
			this.messenger.setClosingByEsc(true);
		}, this)
	});
}
BX.IM.DiskManager.prototype.initEventFileDialog = function(name)
{
	if (name != 'im-file-dialog' || !BX.DiskFileDialog)
		return false;

	this.flagFileDialogInited = true;

	BX.DiskFileDialog.obCallback[name] = {
		'saveButton' : BX.delegate(function(tab, path, selected){
			this.uploadFromDisk(tab, path, selected);
		}, this),
		'popupShow' : BX.delegate(function(){
			BX.bind(BX.DiskFileDialog.popupWindow.popupContainer, "click", BX.MessengerCommon.preventDefault);
			this.messenger.setClosingByEsc(false);
		}, this),
		'popupDestroy' : BX.delegate(function(){
			this.messenger.setClosingByEsc(true);
		}, this)
	};

	BX.DiskFileDialog.openDialog(name);

}
BX.IM.DiskManager.prototype.uploadFromDisk = function(tab, path, selected, text)
{
	text = text || '';
	var chatId = this.messenger.popupMessengerFileFormChatId.value;
	if (!this.files[chatId])
		this.files[chatId] = {};

	var paramsFileId = []
	for(var i in selected)
	{
		var fileId = i.replace('n', '');

		this.files[chatId]['disk'+fileId] = {
			'id': 'disk'+fileId,
			'tempId': 'disk'+fileId,
			'chatId': chatId,
			'date': new Date(selected[i].modifyDateInt*1000),
			'type': 'file',
			'preview': '',
			'name': selected[i].name,
			'size': selected[i].sizeInt,
			'status': 'upload',
			'progress': -1,
			'authorId': this.BXIM.userId,
			'authorName': this.messenger.users[this.BXIM.userId].name,
			'urlPreview': '',
			'urlShow': '',
			'urlDownload': ''
		};
		paramsFileId.push('disk'+fileId);
	}

	var recipientId = 0;
	if (this.messenger.chat[chatId])
	{
		recipientId = 'chat'+chatId;
	}
	else
	{
		for (var userId in this.messenger.userChat)
		{
			if (this.messenger.userChat[userId] == chatId)
			{
				recipientId = userId;
				break;
			}
		}
	}
	if (!recipientId)
		return false;

	var olSilentMode = 'N';
	if (recipientId.toString().substr(0,4) == 'chat' && this.BXIM.messenger.linesSilentMode && this.BXIM.messenger.linesSilentMode[chatId])
	{
		olSilentMode = 'Y';
	}

	var tmpMessageId = 'tempFile'+this.fileTmpId;
	this.messenger.message[tmpMessageId] = {
		'id': tmpMessageId,
		'chatId': chatId,
		'senderId': this.BXIM.userId,
		'recipientId': recipientId,
		'date': new Date(),
		'text': text,
		'params': {'FILE_ID': paramsFileId, 'CLASS': olSilentMode == "Y"? "bx-messenger-content-item-system": ""}
	};
	if (!this.messenger.showMessage[recipientId])
		this.messenger.showMessage[recipientId] = [];

	this.messenger.showMessage[recipientId].push(tmpMessageId);
	BX.MessengerCommon.drawMessage(recipientId, this.messenger.message[tmpMessageId]);
	BX.MessengerCommon.drawProgessMessage(tmpMessageId);

	this.messenger.sendMessageFlag++;
	this.messenger.popupMessengerFileFormInput.setAttribute('disabled', true);

	BX.ajax({
		url: this.BXIM.pathToFileAjax+'?FILE_UPLOAD_FROM_DISK&V='+this.BXIM.revision,
		method: 'POST',
		dataType: 'json',
		skipAuthCheck: true,
		timeout: 30,
		data: {'IM_FILE_UPLOAD_FROM_DISK' : 'Y', CHAT_ID: chatId, RECIPIENT_ID: recipientId, MESSAGE: text, MESSAGE_TMP_ID: tmpMessageId, 'OL_SILENT': olSilentMode, FILES: JSON.stringify(paramsFileId), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data) {
			if (data.ERROR != '')
			{
				this.messenger.sendMessageFlag--;
				delete this.messenger.message[tmpMessageId];
				BX.MessengerCommon.drawTab(recipientId);

				return false;
			}

			this.messenger.sendMessageFlag--;
			var messagefileId = [];
			var filesProgress = {};
			for(var tmpId in data.FILES)
			{
				var newFile = data.FILES[tmpId];

				if (parseInt(newFile.id) > 0)
				{
					newFile.date = new Date(newFile.date);
					this.files[data.CHAT_ID][newFile.id] = newFile;
					delete this.files[data.CHAT_ID][tmpId];

					if (BX('im-file-'+tmpId))
					{
						BX('im-file-'+tmpId).setAttribute('data-fileId', newFile.id);
						BX('im-file-'+tmpId).id = 'im-file-'+newFile.id;
						BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, newFile.id);
					}
					messagefileId.push(newFile.id);
				}
				else
				{
					this.files[data.CHAT_ID][tmpId]['status'] = 'error';
					BX.MessengerCommon.diskRedrawFile(data.CHAT_ID, tmpId);
				}
			}

			this.messenger.message[data.MESSAGE_ID] = BX.clone(this.messenger.message[data.MESSAGE_TMP_ID]);
			this.messenger.message[data.MESSAGE_ID]['id'] = data.MESSAGE_ID;
			this.messenger.message[data.MESSAGE_ID]['params']['FILE_ID'] = messagefileId;

			if (this.messenger.popupMessengerLastMessage == data.MESSAGE_TMP_ID)
				this.messenger.popupMessengerLastMessage = data.MESSAGE_ID;

			delete this.messenger.message[data.MESSAGE_TMP_ID];

			var idx = BX.util.array_search(''+data.MESSAGE_TMP_ID+'', this.messenger.showMessage[data.RECIPIENT_ID]);
			if (this.messenger.showMessage[data.RECIPIENT_ID][idx])
				this.messenger.showMessage[data.RECIPIENT_ID][idx] = ''+data.MESSAGE_ID+'';

			if (BX('im-message-'+data.MESSAGE_TMP_ID))
			{
				BX('im-message-'+data.MESSAGE_TMP_ID).id = 'im-message-'+data.MESSAGE_ID;
				var element = BX.findChild(this.messenger.popupMessengerBodyWrap, {attribute: {'data-messageid': ''+data.MESSAGE_TMP_ID}}, true);
				if (element)
				{
					element.setAttribute('data-messageid',	''+data.MESSAGE_ID+'');
					if (element.getAttribute('data-blockmessageid') == ''+data.MESSAGE_TMP_ID)
						element.setAttribute('data-blockmessageid',	''+data.MESSAGE_ID+'');
				}
				else
				{
					var element2 = BX.findChild(this.messenger.popupMessengerBodyWrap, {attribute: {'data-blockmessageid': ''+data.MESSAGE_TMP_ID}}, true);
					if (element2)
					{
						element2.setAttribute('data-blockmessageid', ''+data.MESSAGE_ID+'');
					}
				}
				var lastMessageElementDate = BX.findChildByClassName(element, "bx-messenger-content-item-date");
				if (lastMessageElementDate)
					lastMessageElementDate.innerHTML = ' &nbsp; '+BX.MessengerCommon.formatDate(this.messenger.message[data.MESSAGE_ID].date, BX.MessengerCommon.getDateFormatType('MESSAGE'));
			}
			BX.MessengerCommon.clearProgessMessage(data.MESSAGE_ID);

			if (this.messenger.history[data.RECIPIENT_ID])
				this.messenger.history[data.RECIPIENT_ID].push(data.MESSAGE_ID);
			else
				this.messenger.history[data.RECIPIENT_ID] = [data.MESSAGE_ID];

			if (BX.MessengerCommon.enableScroll(this.messenger.popupMessengerBody, 200))
			{
				if (this.BXIM.animationSupport)
				{
					if (this.messenger.popupMessengerBodyAnimation != null)
						this.messenger.popupMessengerBodyAnimation.stop();
					(this.messenger.popupMessengerBodyAnimation = new BX.easing({
						duration : 800,
						start : { scroll : this.messenger.popupMessengerBody.scrollTop },
						finish : { scroll : this.messenger.popupMessengerBody.scrollHeight - this.messenger.popupMessengerBody.offsetHeight},
						transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
						step : BX.delegate(function(state){
							this.messenger.popupMessengerBody.scrollTop = state.scroll;
						}, this)
					})).animate();
				}
				else
				{
					this.messenger.popupMessengerBody.scrollTop = this.messenger.popupMessengerBody.scrollHeight - this.messenger.popupMessengerBody.offsetHeight;
				}
			}

			this.messenger.popupMessengerFileFormInput.removeAttribute('disabled');
		}, this),
		onfailure: BX.delegate(function(){
			this.messenger.sendMessageFlag--;
			delete this.messenger.message[tmpMessageId];
			BX.MessengerCommon.drawTab(recipientId);
		}, this)
	});
	this.fileTmpId++;
}
BX.IM.DiskManager.prototype.uploadFromClipboard = function(list, text)
{
	var result = list.map(function(item) {
		var dataBlob = BX.UploaderUtils.dataURLToBlob(item.source);
		dataBlob.name = item.name;

		return dataBlob;
	});

	this.formAgents['imDialog'].messageText = text? text: '';
	this.formAgents['imDialog'].onChange(result);

	return true;
}
BX.IM.DiskManager.prototype.chatAvatarInit = function()
{
	if (!BX.Uploader)
		return false;

	if (this.messenger.popupMessengerPanelAvatarUpload2)
	{
		this.formAgents['popupMessengerPanelAvatarUpload2'] = BX.Uploader.getInstance({
			id : 'popupMessengerPanelAvatarUpload2',
			allowUpload : "I",
			uploadMethod : "immediate",
			showImage : false,
			input : this.messenger.popupMessengerPanelAvatarUpload2,
			dropZone : this.messenger.popupMessengerPanelAvatarUpload2.parentNode
		});

		BX.addCustomEvent(this.formAgents['popupMessengerPanelAvatarUpload2'], "onFileinputIsReinited", BX.delegate(function(fileInput){
			if (!fileInput && !this.formAgents['popupMessengerPanelAvatarUpload2'].fileInput)
				return false;

			this.messenger.popupMessengerPanelAvatarUpload2 = fileInput? fileInput: this.formAgents['popupMessengerPanelAvatarUpload2'].fileInput;
		}, this));

		BX.addCustomEvent(this.formAgents['popupMessengerPanelAvatarUpload2'], "onFileIsInited", BX.delegate(function(id, file, agent){
			this.chatAvatarAttached(agent);
			BX.addCustomEvent(file, 'onUploadDone', BX.delegate(this.chatAvatarDone, this));
			BX.addCustomEvent(file, 'onUploadError', BX.delegate(this.chatAvatarError, this));
		}, this));
	}

	if (this.messenger.popupMessengerPanelAvatarUpload3)
	{
		this.formAgents['popupMessengerPanelAvatarUpload3'] = BX.Uploader.getInstance({
			id : 'popupMessengerPanelAvatarUpload3',
			allowUpload : "I",
			uploadMethod : "immediate",
			showImage : false,
			input : this.messenger.popupMessengerPanelAvatarUpload3,
			dropZone : this.messenger.popupMessengerPanelAvatarUpload3.parentNode
		});

		BX.addCustomEvent(this.formAgents['popupMessengerPanelAvatarUpload3'], "onFileinputIsReinited", BX.delegate(function (fileInput)
		{
			if (!fileInput && !this.formAgents['popupMessengerPanelAvatarUpload3'].fileInput)
				return false;

			this.messenger.popupMessengerPanelAvatarUpload3 = fileInput? fileInput: this.formAgents['popupMessengerPanelAvatarUpload3'].fileInput;
		}, this));

		BX.addCustomEvent(this.formAgents['popupMessengerPanelAvatarUpload3'], "onFileIsInited", BX.delegate(function (id, file, agent)
		{
			this.chatAvatarAttached(agent);
			BX.addCustomEvent(file, 'onUploadDone', BX.delegate(this.chatAvatarDone, this));
			BX.addCustomEvent(file, 'onUploadError', BX.delegate(this.chatAvatarError, this));
		}, this));
	}
};
BX.IM.DiskManager.prototype.avatarFormIsBlocked = function(chatId, formId, form)
{

	var result = this.formBlocked[formId+'_'+chatId] || BX.MessengerCommon.checkRestriction(chatId, 'AVATAR')? true: false;

	if (this.messenger.currentTab != 'chat'+chatId)
		return result;

	var element = this.formAgents[formId] && this.formAgents[formId].fileInput? this.formAgents[formId].fileInput: null;
	if (element)
	{
		if (result)
		{
			element.title = '';
			element.disabled = true;
			element.style.cursor = "default";
		}
		else
		{
			element.title = BX.message('IM_M_AVATAR_UPLOAD');
			element.removeAttribute('disabled');
			element.style.cursor = '';
		}
	}
	if (form)
	{
		if (result)
		{
			BX.addClass(form.firstChild, 'bx-messenger-panel-avatar-progress-on');
		}
		else
		{
			BX.removeClass(form.firstChild, 'bx-messenger-panel-avatar-progress-on');
		}

		BX.removeClass(form, 'bx-messenger-panel-avatar-upload-error');
	}

	return result;
}
BX.IM.DiskManager.prototype.chatAvatarAttached = function(agent)
{
	if (!agent.form.CHAT_ID) return false;

	this.formBlocked[agent.id+'_'+agent.form.CHAT_ID.value] = true;
	this.avatarFormIsBlocked(agent.form.CHAT_ID.value, agent.id, agent.form);
}
BX.IM.DiskManager.prototype.chatAvatarDone = function(status, file, agent, pIndex)
{
	this.formBlocked[agent.id+'_'+file.file.chatId] = false;
	this.avatarFormIsBlocked(file.file.chatId, agent.id, agent.form);
	this.messenger.updateChatAvatar(file.file.chatId, file.file.chatAvatar);
}
BX.IM.DiskManager.prototype.chatAvatarError = function(status, file, agent, pIndex)
{
	var formFields = agent.streams.packages.getItem(pIndex).data

	this.formBlocked[agent.id+'_'+formFields.CHAT_ID] = false;
	this.avatarFormIsBlocked(formFields.CHAT_ID, agent.id, agent.form);
	BX.addClass(agent.form, 'bx-messenger-panel-avatar-upload-error');
	agent.fileInput.title = file.error;
}

/* NotifyManager */
BX.IM.NotifyManager = function(BXIM)
{
	this.stack = [];
	this.stackTimeout = null;
	this.stackPopup = {};
	this.stackPopupTimeout = {};
	this.stackPopupTimeout2 = {};
	this.stackPopupId = 0;
	this.stackOverflow = false;

	this.blockNativeNotify = false;
	this.blockNativeNotifyTimeout = null;

	this.notifyShow = 0;
	this.notifyHideTime = 5000;
	this.notifyHeightCurrent = 10;
	this.notifyHeightMax = 0;
	this.notifyGarbageTimeout = null;
	this.notifyAutoHide = true;
	this.notifyAutoHideTimeout = null;

	/*
	BX.bind(window, 'scroll', BX.delegate(function(events){
		if (this.notifyShow > 0)
			for (var i in this.stackPopup)
				this.stackPopup[i].close();
	}, this));
	*/

	if (BX.browser.SupportLocalStorage())
	{
		BX.addCustomEvent(window, "onLocalStorageSet", BX.proxy(this.storageSet, this));
	}

	this.BXIM = BXIM;
};

BX.IM.NotifyManager.prototype.storageSet = function(params)
{
	if (params.key == 'mnnb')
	{
		this.blockNativeNotify = true;
		clearTimeout(this.blockNativeNotifyTimeout);
		this.blockNativeNotifyTimeout = setTimeout(BX.delegate(function(){
			this.blockNativeNotify = false;
		}, this), 1000)
	}
}

BX.IM.NotifyManager.prototype.add = function(params)
{
	if (typeof(params) != "object" || !params.html)
		return false;

	if (BX.type.isDomNode(params.html))
		params.html = params.html.outerHTML;

	this.stack.push(params);

	if (!this.stackOverflow)
		this.setShowTimer(300);
};

BX.IM.NotifyManager.prototype.remove = function(stackId)
{
	delete this.stack[stackId];
};

BX.IM.NotifyManager.prototype.draw = function()
{
	this.show();
}

BX.IM.NotifyManager.prototype.show = function()
{
	this.notifyHeightMax = document.body.offsetHeight;

	var windowPos = BX.GetWindowScrollPos();
	for (var i = 0; i < this.stack.length; i++)
	{
		if (typeof(this.stack[i]) == 'undefined')
			continue;

		/* show notify to calc width & height */
		var notifyPopup = new BX.PopupWindow('bx-im-notify-flash-'+this.stackPopupId, {top: '-1000px', left: 0}, {
			//parentPopup: this.popupMessenger,
			lightShadow : true,
			zIndex: 3400,
			events : {
				onPopupClose : BX.delegate(function() {
					BX.proxy_context.popupContainer.style.opacity = 0;
					this.notifyShow--;
					this.notifyHeightCurrent -= BX.proxy_context.popupContainer.offsetHeight+10;
					this.stackOverflow = false;
					setTimeout(BX.delegate(function() {
						this.destroy();
					}, BX.proxy_context), 1500);
				}, this),
				onPopupDestroy : BX.delegate(function() {
					BX.unbindAll(BX.findChildByClassName(BX.proxy_context.popupContainer, "bx-notifier-item-delete"));
					BX.unbindAll(BX.proxy_context.popupContainer);
					delete this.stackPopup[BX.proxy_context.uniquePopupId];
					delete this.stackPopupTimeout[BX.proxy_context.uniquePopupId];
					delete this.stackPopupTimeout2[BX.proxy_context.uniquePopupId];
				}, this)
			},
			bindOnResize: false,
			content : BX.create("div", {props : { className: "bx-notifyManager-item"}, html: this.stack[i].html})
		});
		notifyPopup.notifyParams = this.stack[i];
		notifyPopup.notifyParams.id = i;
		notifyPopup.show();
		BX.onCustomEvent(window, 'onNotifyManagerShow', [this.stack[i]]);

		/* move notify out monitor */
		notifyPopup.popupContainer.style.left = document.body.offsetWidth-notifyPopup.popupContainer.offsetWidth-10+'px';
		notifyPopup.popupContainer.style.opacity = 0;

		if (this.notifyHeightMax < this.notifyHeightCurrent+notifyPopup.popupContainer.offsetHeight+10)
		{
			if (this.notifyShow > 0)
			{
				notifyPopup.destroy();
				this.stackOverflow = true;
				break;
			}
		}

		/* move notify to top-right */
		BX.addClass(notifyPopup.popupContainer, 'bx-notifyManager-animation');
		notifyPopup.popupContainer.style.opacity = 1;
		notifyPopup.popupContainer.style.top = windowPos.scrollTop+this.notifyHeightCurrent+'px';

		this.notifyHeightCurrent = this.notifyHeightCurrent+notifyPopup.popupContainer.offsetHeight+10;
		this.stackPopupId++;
		this.notifyShow++;
		this.remove(i);

		/* notify events */
		this.stackPopupTimeout[notifyPopup.uniquePopupId] = null;

		BX.bind(notifyPopup.popupContainer, "mouseover", BX.delegate(function() {
			this.clearAutoHide();
		}, this));

		BX.bind(notifyPopup.popupContainer, "mouseout", BX.delegate(function() {
			this.setAutoHide(this.notifyHideTime/2);
		}, this));

		BX.bind(notifyPopup.popupContainer, "contextmenu", BX.delegate(function(e){
			if (this.stackPopup[BX.proxy_context.id].notifyParams.tag)
				this.closeByTag(this.stackPopup[BX.proxy_context.id].notifyParams.tag);
			else
				this.stackPopup[BX.proxy_context.id].close();

			return BX.PreventDefault(e);
		}, this));

		var arLinks = BX.findChildren(notifyPopup.popupContainer, {tagName : "a"}, true);
		for (var j = 0; j < arLinks.length; j++)
		{
			if (arLinks[j].href != '#')
				arLinks[j].target = "_blank";
		}

		BX.bind(BX.findChildByClassName(notifyPopup.popupContainer, "bx-notifier-item-delete"), 'click', BX.delegate(function(e){
			var id = BX.proxy_context.parentNode.parentNode.parentNode.parentNode.id.replace('popup-window-content-', '');

			if (this.stackPopup[id].notifyParams.close)
				this.stackPopup[id].notifyParams.close(this.stackPopup[id]);

			this.stackPopup[id].close();

			if (this.notifyAutoHide == false)
			{
				this.clearAutoHide();
				this.setAutoHide(this.notifyHideTime/2);
			}
			return BX.PreventDefault(e);
		}, this));

		BX.bindDelegate(notifyPopup.popupContainer, "click", {className: "bx-notifier-item-button-confirm"}, BX.delegate(function(e){
			var id = BX.proxy_context.getAttribute('data-id');
			this.BXIM.notify.confirmRequest({
				'notifyId': id,
				'notifyValue': BX.proxy_context.getAttribute('data-value'),
				'notifyURL': BX.proxy_context.getAttribute('data-url'),
				'notifyTag': this.BXIM.notify.notify[id] && this.BXIM.notify.notify[id].tag? this.BXIM.notify.notify[id].tag: null,
				'groupDelete': BX.proxy_context.getAttribute('data-group') != null
			}, true);
			for (var i in this.stackPopup)
			{
				if (this.stackPopup[i].notifyParams.notifyId == id)
					this.stackPopup[i].close();
			}
			if (this.notifyAutoHide == false)
			{
				this.clearAutoHide();
				this.setAutoHide(this.notifyHideTime/2);
			}
			return BX.PreventDefault(e);
		}, this));

		if (notifyPopup.notifyParams.click)
		{
			notifyPopup.popupContainer.style.cursor = 'pointer';
			BX.bind(notifyPopup.popupContainer, 'click', BX.delegate(function(e){
				this.notifyParams.click(this);
				if (this.notifyParams.notifyId != 'network')
					return BX.PreventDefault(e);
			}, notifyPopup));
		}
		this.stackPopup[notifyPopup.uniquePopupId] = notifyPopup;
	}

	if (this.stack.length > 0)
	{
		this.clearAutoHide(true);
		this.setAutoHide(this.notifyHideTime);
	}
	this.garbage();
};

BX.IM.NotifyManager.prototype.closeByTag = function(tag)
{
	for (var i = 0; i < this.stack.length; i++)
	{
		if (typeof(this.stack[i]) != 'undefined' && this.stack[i].tag == tag)
		{
			delete this.stack[i];
		}
	}
	for (var i in this.stackPopup)
	{
		if (this.stackPopup[i].notifyParams.tag == tag)
			this.stackPopup[i].close()
	}
};

BX.IM.NotifyManager.prototype.setShowTimer = function(time)
{
	clearTimeout(this.stackTimeout);
	this.stackTimeout = setTimeout(BX.delegate(this.draw, this), time);
};

BX.IM.NotifyManager.prototype.setAutoHide = function(time)
{
	this.notifyAutoHide = true;
	clearTimeout(this.notifyAutoHideTimeout);
	this.notifyAutoHideTimeout = setTimeout(BX.delegate(function(){
		for (var i in this.stackPopupTimeout)
		{
			this.stackPopupTimeout[i] = setTimeout(BX.delegate(function(){
				this.close();
			}, this.stackPopup[i]), time-1000);
			this.stackPopupTimeout2[i] = setTimeout(BX.delegate(function(){
				this.setShowTimer(300);
			}, this), time-700);
		}
	}, this), 1000);
};

BX.IM.NotifyManager.prototype.clearAutoHide = function(force)
{
	clearTimeout(this.notifyGarbageTimeout);
	this.notifyAutoHide = false;
	force = force==true;
	if (force)
	{
		clearTimeout(this.stackTimeout);
		for (var i in this.stackPopupTimeout)
		{
			clearTimeout(this.stackPopupTimeout[i]);
			clearTimeout(this.stackPopupTimeout2[i]);
		}
	}
	else
	{
		clearTimeout(this.notifyAutoHideTimeout);
		this.notifyAutoHideTimeout = setTimeout(BX.delegate(function(){
			clearTimeout(this.stackTimeout);
			for (var i in this.stackPopupTimeout)
			{
				clearTimeout(this.stackPopupTimeout[i]);
				clearTimeout(this.stackPopupTimeout2[i]);
			}
		}, this), 300);
	}
};

BX.IM.NotifyManager.prototype.garbage = function()
{
	clearTimeout(this.notifyGarbageTimeout);
	this.notifyGarbageTimeout = setTimeout(BX.delegate(function(){
		var newStack = [];
		for (var i = 0; i < this.stack.length; i++)
		{
			if (typeof(this.stack[i]) != 'undefined')
				newStack.push(this.stack[i]);
		}
		this.stack = newStack;
	}, this), 10000);
};

BX.IM.NotifyManager.prototype.nativeNotify = function(params, force)
{
	if (!params.title || params.title.length <= 0)
		return false;

	if (this.blockNativeNotify)
		return false;

	if (!force)
	{
		setTimeout(BX.delegate(function(){
			if (this.blockNativeNotify)
				return false;

			this.nativeNotify(params, true);
		}, this), Math.floor(Math.random() * (151)) + 50);

		return true;
	}

	BX.localStorage.set('mnnb', true, 1);

	var notify = new Notification(params.title, {
		tag : (params.tag? params.tag: ''),
		body : (params.text? params.text: ''),
		icon : (params.icon? params.icon: '')
	});
	if (typeof(params.onshow) == 'function')
		notify.onshow = params.onshow;
	if (typeof(params.onclick) == 'function')
		notify.onclick = params.onclick;
	if (typeof(params.onclose) == 'function')
		notify.onclose = params.onclose;
	if (typeof(params.onerror) == 'function')
		notify.onerror = params.onerror;

	return true;
};

BX.IM.NotifyManager.prototype.nativeNotifyShow = function()
{
	this.show();
};

BX.IM.NotifyManager.prototype.nativeNotifyGranted = function()
{
	return (window.Notification && window.Notification.permission && window.Notification.permission.toLowerCase() == "granted");
};

BX.IM.NotifyManager.prototype.nativeNotifyAccessForm = function()
{
	clearTimeout(this.BXIM.messenger.popupMessengerTopLineTimeout);
	if (!this.BXIM.messenger.popupMessengerTopLine)
		return false;

	var nativeNotify = BX.localStorage.get('imNativeNotify');
	if (
		!this.BXIM.xmppStatus && !this.BXIM.desktopStatus && nativeNotify !== false &&
		window.Notification && window.Notification.permission && window.Notification.permission.toLowerCase() == "default"
	)
	{
		clearTimeout(this.popupMessengerDesktopTimeout);
		var acceptButton = BX.delegate(function(){
			Notification.requestPermission();
			this.BXIM.messenger.hideTopLine();
		}, this);
		var declineButton = BX.delegate(function(){
			BX.localStorage.set('imNativeNotify', false, 3000000);
			this.BXIM.saveSettings({'nativeNotify': this.BXIM.settings.nativeNotify});
			this.BXIM.messenger.hideTopLine();
		}, this);

		this.BXIM.messenger.showTopLine(BX.message("IM_WN_MAC")+"<br />"+BX.message("IM_WN_TEXT"), [
			{title: BX.message('IM_WN_ACCEPT'), callback: acceptButton},
			{title: BX.message('IM_DESKTOP_INSTALL_N'), callback: declineButton}
		], BX.delegate(function(){
			BX.localStorage.set('imNativeNotify', false, 86400);
			this.BXIM.messenger.hideTopLine()
		}, this));
	}
	else
	{
		return false;
	}

	return true;
}

BX.IM.LevelMeter = function(element)
{
	this.element = element;
	this.maximumLevel = 1;

	this.mediaStream = null;
	this.audioContext = null;
	this.mediaStreamNode = null;
	this.scriptNode = null;

	this.instant = 0.0;
	this.slow = 0.0;
	this.clip = 0.0;

	this.supported =  (window.AudioContext || window.webkitAudioContext);
	this.animationInterval = null;

	this.mask = BX.create('div', {attrs: {className: 'bx-messenger-settings-level-meter-mask'}});
	this.filler = BX.create('div', {attrs: {className: 'bx-messenger-settings-level-meter-filler'}});
	this.element.appendChild(this.mask);
	this.mask.appendChild(this.filler);
};

BX.IM.LevelMeter.prototype.render = function()
{
	var fillerWidth = Math.floor(this.slow * 100);
	this.filler.style.width = fillerWidth+'%';
};

BX.IM.LevelMeter.prototype.attachMediaStream = function(mediaStream)
{
	var self = this;

	if(!(mediaStream instanceof MediaStream))
		return;

	this.stop();

	this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
	this.scriptNode = this.audioContext.createScriptProcessor(2048, 1, 1);
	this.scriptNode.connect(this.audioContext.destination); //chrome does not start processing without this for unknown reason

	this.mediaStream = mediaStream;
	this.mediaStreamNode = this.audioContext.createMediaStreamSource(this.mediaStream);
	this.mediaStreamNode.connect(this.scriptNode);

	this.scriptNode.onaudioprocess = function(event) {
		var input = event.inputBuffer.getChannelData(0);
		var i;
		var sum = 0.0;
		var clipcount = 0;
		for (i = 0; i < input.length; ++i) {
			sum += input[i] * input[i];
			if (Math.abs(input[i]) > 0.99) {
				clipcount += 1;
			}
		}

		self.instant = Math.sqrt(sum / input.length);
		self.slow = 0.75 * self.slow + 0.25 * self.instant;
		self.clip = clipcount / input.length;
	};
	this.animationInterval = setInterval(this.render.bind(this), 200);
};

BX.IM.LevelMeter.prototype.getVolume = function()
{
	return {
		instant: this.instant,
		slow: this.slow
	}
};

BX.IM.LevelMeter.prototype.stop = function()
{
	if(this.scriptNode)
		this.scriptNode.disconnect();

	if(this.mediaStreamNode)
		this.mediaStreamNode.disconnect();

	if(this.audioContext)
		this.audioContext.close();

	if(this.animationInterval)
		clearInterval(this.animationInterval);

	this.scriptNode = null;
	this.mediaStreamNode = null;
	this.mediaStream = null;
	this.audioContext = null;
	this.animationInterval = null;
};


})();

/* Desktop utils */

(function(){

if (BX.desktopUtils)
	return;

BX.desktopUtils = function (){
	this.runningCheckTimeout = {};
	this.checkUrl = "http://127.0.0.1:20141/";
};

BX.desktopUtils.prototype.runningCheck = function(successCallback, failureCallback, successOnlyWithNewApp)
{
	if (typeof(successCallback) == 'undefined')
	{
		return false;
	}
	if (typeof(failureCallback) == 'undefined')
	{
		failureCallback = function(){};
	}

	successOnlyWithNewApp = typeof (successOnlyWithNewApp) == 'undefined' || !successOnlyWithNewApp? false: true;

	var dateCheck = (+new Date());
	if (typeof(BXIM) == 'undefined' || BX.MessengerCommon.isDesktop() || !BXIM.desktopStatus || BXIM.desktopVersion < 18)
	{
		failureCallback(false, dateCheck);
		return false;
	}
	else if (BXIM.desktopVersion < 35)
	{
		if (successOnlyWithNewApp)
		{
			failureCallback(false, dateCheck);
		}
		else
		{
			successCallback(true, dateCheck);
		}
		return true;
	}

	var checkElement = BX.create("img", {
		attrs : {
			"src" : this.checkUrl+"icon.png?"+dateCheck,
			"data-id": dateCheck
		},
		props : {className : "bx-messenger-out-of-view"},
		events : {
			"error" : function () {
				var checkId = this.getAttribute('data-id');
				failureCallback(false, checkId);
				clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
				BX.remove(this);
			},
			"load" : function () {
				var checkId = this.getAttribute('data-id');
				successCallback(true, checkId);
				clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
				BX.remove(this);
			}
		}
	});
	document.body.appendChild(checkElement);
	this.runningCheckTimeout[dateCheck] = setTimeout(function(){
		failureCallback(false, dateCheck);
		clearTimeout(BX.desktopUtils.runningCheckTimeout[dateCheck]);
		BX.remove(this);
	}, 500);

	return true;
};

BX.desktopUtils.prototype.goToBx = function (url)
{
	if (typeof(BXIM) != 'undefined' && BXIM.desktopVersion >= 36 && !url.match(/^bx:\/\/v(\d)\//))
	{
		url = url.replace('bx://', 'bx://v'+BXIM.desktopProtocolVersion+'/' + location.hostname + '/');
	}
	if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.setPrivateVar) != 'undefined')
	{
		BX.PULL.setPrivateVar('_pullTryAfterBxLink', true);
	}
	location.href = url;
};

BX.desktopUtils.prototype.isChangedLocationToBx = function ()
{
	if (typeof(BX.PULL) != 'undefined' && typeof(BX.PULL.setPrivateVar) != 'undefined')
		return BX.PULL.returnPrivateVar('_pullTryAfterBxLink');

	return false;
};

BX.desktopUtils.prototype.encodeParams = function(params)
{
	if(!BX.type.isPlainObject(params))
		return '';

	var stringParams = '';
	var first = true;
	for (var i in params)
	{
		stringParams = stringParams+(first ? '' : '!!')+i+'!!'+params[i];
		first = false;
	}
	return stringParams;
};

BX.desktopUtils.prototype.decodeParams = function(encodedParams)
{
	var result = {};
	if(!BX.type.isNotEmptyString(encodedParams))
		return result;

	var chunks = encodedParams.split('!!');
	for (var i = 0; i < chunks.length; i=i+2)
	{
		result[chunks[i]] = chunks[i+1];
	}
	return result;
};

BX.desktopUtils = new BX.desktopUtils();
})();